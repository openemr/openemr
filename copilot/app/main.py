"""FastAPI entry — Clinical Co-Pilot."""
from __future__ import annotations

import asyncio
import base64
import json
import logging
import uuid
from contextlib import asynccontextmanager
from pathlib import Path

from fastapi import Depends, FastAPI, File, Form, HTTPException, UploadFile
from fastapi.responses import FileResponse, JSONResponse, RedirectResponse, Response
from pydantic import BaseModel, Field

from anthropic import AsyncAnthropic

from app.agent.loop import run_turn
from app.agent.prewarm import prewarm
from app.agent.schemas import PriorTurn
from app.config import Settings, get_settings
from app.fhir.client import FhirClient, FhirError
from app.ingestion.schemas import AttachDocumentRequest
from app.ingestion.service import IngestionService
from app.ingestion.vlm import VlmExtractor
from app.observability.trace import get_tracer
from app.persistence.conversations import ConversationStore
from app.persistence.processed_documents import ProcessedDocumentStore
from app.phi.log_filter import install as install_phi_log_filter
from app.phi.session import sessions
from app.retrieval.corpus import GuidelineCorpus
from app.tools.registry import set_corpus, set_ingestion_service

logger = logging.getLogger("copilot.main")


@asynccontextmanager
async def lifespan(app: FastAPI):
    settings = get_settings()
    install_phi_log_filter(logging.getLogger(), sessions)
    fhir = FhirClient(settings)
    app.state.fhir = fhir
    app.state.fhir_client = fhir
    app.state.tracer = get_tracer(settings)
    app.state.conv_store = ConversationStore(settings.conversation_db_path)
    await app.state.conv_store.init()

    # Week 2: document ingestion singletons
    docs_store = ProcessedDocumentStore(settings.copilot_docs_db_path)
    await docs_store.init()
    anthropic_client = AsyncAnthropic(api_key=settings.anthropic_api_key)
    vlm = VlmExtractor(client=anthropic_client, model_id=settings.vlm_model_id)
    ingestion = IngestionService(fhir=fhir, vlm=vlm, store=docs_store)
    app.state.processed_documents = docs_store
    app.state.ingestion_service = ingestion
    set_ingestion_service(ingestion)

    # Week 2: guideline corpus singleton
    corpus = GuidelineCorpus(
        jsonl_path="corpus/guidelines.jsonl",
        sqlite_path=settings.conversation_db_path + ".corpus",
    )
    await corpus.build()
    app.state.corpus = corpus
    set_corpus(corpus)

    yield
    await fhir.aclose()


app = FastAPI(
    title="Clinical Co-Pilot",
    version="0.1.0",
    description="AI agent for OpenEMR — point-of-care clinical context for primary care.",
    lifespan=lifespan,
)


@app.get("/healthz")
async def healthz():
    return {"status": "ok", "service": "clinical-copilot"}


WEB_DIR = Path(__file__).parent / "web"


@app.get("/")
async def get_iframe_shell():
    return FileResponse(WEB_DIR / "copilot_iframe.html", media_type="text/html")


@app.get("/static/copilot_iframe.js")
async def get_iframe_js():
    return FileResponse(WEB_DIR / "copilot_iframe.js", media_type="application/javascript")


@app.get("/static/copilot_iframe.css")
async def get_iframe_css():
    return FileResponse(WEB_DIR / "copilot_iframe.css", media_type="text/css")


@app.get("/v1/patient/{patient_id}/raw")
async def patient_raw(
    patient_id: str,
    physician_user_id: str | None = None,
    settings: Settings = Depends(get_settings),
):
    fhir: FhirClient = app.state.fhir
    physician = physician_user_id or settings.demo_physician_user_id
    try:
        resource = await fhir.get_resource(
            "Patient", patient_id, physician_user_id=physician
        )
    except FhirError as e:
        raise HTTPException(status_code=e.status or 502, detail=str(e))
    return JSONResponse(resource)


class StartSessionRequest(BaseModel):
    patient_id: str
    physician_user_id: str | None = None


class StartSessionResponse(BaseModel):
    session_id: str
    patient_pseudonym: str


def _env_panel_for(settings: Settings, physician: str) -> list[str] | None:
    """Parse PHYSICIAN_PATIENT_PANEL JSON for a physician's allowed UUIDs.

    Returns the list of patient FHIR UUIDs this physician owns, or None
    if the env is empty / the physician has no entry. Workaround for
    OpenEMR's FHIR Patient.generalPractitioner not being exposed
    server-side (verified absent on Railway 2026-05-02).
    """
    raw = (settings.physician_patient_panel or "").strip()
    if not raw or raw == "{}":
        return None
    try:
        panel_map = json.loads(raw)
    except json.JSONDecodeError:
        logger.warning("PHYSICIAN_PATIENT_PANEL is not valid JSON; ignoring")
        return None
    entry = panel_map.get(physician)
    if not entry:
        return None
    if not isinstance(entry, list):
        logger.warning(
            "PHYSICIAN_PATIENT_PANEL[%s] is not a list; ignoring", physician
        )
        return None
    return [str(p) for p in entry]


async def _verify_patient_in_panel(
    fhir: FhirClient, physician: str, patient_id: str, settings: Settings
) -> None:
    """A.7 panel gate: confirm the patient is assigned to this physician.

    Raises HTTPException(403, "patient_out_of_panel") on mismatch.

    Resolution order:
      1. **PHYSICIAN_PATIENT_PANEL env (primary).** JSON map of
         physician_user_id → list of patient FHIR uuids. Workaround for
         OpenEMR's FHIR Patient.generalPractitioner not being exposed.
         Membership in the list → allowed; outside the list → 403.
      2. **Patient.generalPractitioner (secondary).** Resolve the
         physician's Practitioner UUID via id_token, fetch the Patient
         resource, compare against generalPractitioner. Currently a
         no-op against Railway OpenEMR (the field is always absent),
         but kept as a future-proof path.
      3. **Admin bypass.** A physician with no resolvable Practitioner
         UUID (e.g. demo_physician_user_id `admin`) is allowed
         unconditionally — matches OpenEMR's UI behavior.
    """
    # Primary: env-driven panel.
    env_panel = _env_panel_for(settings, physician)
    if env_panel is not None:
        if patient_id in env_panel:
            logger.info(
                "panel allow (env): physician=%s patient=%s", physician, patient_id
            )
            return
        logger.warning(
            "panel deny (env): physician=%s patient=%s panel_size=%d",
            physician, patient_id, len(env_panel),
        )
        raise HTTPException(status_code=403, detail="patient_out_of_panel")

    # Secondary: FHIR-derived panel (currently a no-op vs Railway OpenEMR).
    practitioner_uuid = await fhir._oauth.resolve_practitioner_uuid(
        fhir._http, physician
    )
    if not practitioner_uuid:
        logger.info(
            "panel check: physician=%s has no Practitioner UUID — bypassing scope",
            physician,
        )
        return
    try:
        patient_resource = await fhir.get_resource(
            "Patient", patient_id, physician_user_id=physician
        )
    except FhirError as e:
        if e.status in (401, 403, 404):
            raise HTTPException(
                status_code=403,
                detail="patient_out_of_panel",
            ) from e
        raise HTTPException(status_code=502, detail=f"fhir_unreachable: {e}") from e

    refs = [
        (gp or {}).get("reference", "")
        for gp in (patient_resource.get("generalPractitioner") or [])
    ]
    owners = [r.rsplit("/", 1)[-1] for r in refs if r.startswith("Practitioner/")]
    if practitioner_uuid not in owners:
        logger.warning(
            "panel deny (fhir): physician=%s practitioner=%s patient=%s owners=%s",
            physician, practitioner_uuid, patient_id, owners,
        )
        raise HTTPException(status_code=403, detail="patient_out_of_panel")


@app.post("/v1/sessions", response_model=StartSessionResponse)
async def start_session(
    body: StartSessionRequest, settings: Settings = Depends(get_settings)
):
    session_id = str(uuid.uuid4())
    physician = body.physician_user_id or settings.demo_physician_user_id
    if physician == settings.demo_physician_user_id and body.physician_user_id is None:
        # INFO, not WARNING — the iframe launch is the expected path here (no
        # SMART handshake), so this fires on every session create and would
        # otherwise spam Railway's log viewer as [error]. Real misconfig (e.g.
        # rail fragment dropping authUser) is caught upstream in OpenEMR.
        logger.info(
            "session %s started without physician_user_id — using demo fallback %s "
            "(SMART launch path is bypassed)",
            session_id,
            physician,
        )

    # A.7 — panel gate. Checks PHYSICIAN_PATIENT_PANEL env first
    # (workaround), then Patient.generalPractitioner (future-proof
    # secondary). Admin (no Practitioner UUID) bypasses.
    fhir: FhirClient = app.state.fhir
    await _verify_patient_in_panel(fhir, physician, body.patient_id, settings)

    pseudo = sessions.create(session_id, physician, body.patient_id)

    # Persist the conversation skeleton so it can be looked up later by
    # (physician_user_id, patient_id) for the resume prompt. The pseudonym
    # snapshot is refreshed on every turn — see /v1/chat below.
    store: ConversationStore = app.state.conv_store
    await store.create(
        conversation_id=session_id,
        physician_user_id=physician,
        active_patient_id=body.patient_id,
        patient_pseudonym=pseudo.patient_pseudonym(),
        pseudonym_map=pseudo.snapshot(),
    )

    # Pre-warm the high-frequency tools so the first turn skips ~3-4s of
    # FHIR latency (UC1/UC2/UC3 all start with the same fan-out). Fired
    # as a background task — does NOT block the session-create response.
    # See app/agent/prewarm.py for the audit-trail trade-off.
    asyncio.create_task(prewarm(pseudo, fhir))

    return StartSessionResponse(
        session_id=session_id, patient_pseudonym=pseudo.patient_pseudonym()
    )


# ---------------- SMART app launch (auth-code + PKCE) ----------------


@app.get("/v1/oauth/launch")
async def oauth_launch(
    iss: str | None = None,
    launch: str | None = None,
    physician_user_id: str | None = None,
):
    """SMART launch endpoint.

    OpenEMR (or a direct browser hit) calls this with `iss` (the FHIR base
    of the EHR) and `launch` (an opaque launch token). We build the
    PKCE-protected /authorize URL and redirect the browser there. After the
    user signs in, OpenEMR redirects back to /v1/oauth/callback.

    For dev or non-SMART entry, omit `iss`/`launch` and pass
    `physician_user_id` to hint the identity for callback resolution.
    """
    fhir: FhirClient = app.state.fhir
    url, _state = fhir._oauth.build_authorize_url(physician_user_id, launch, iss)
    return RedirectResponse(url, status_code=302)


@app.get("/v1/oauth/callback")
async def oauth_callback(code: str, state: str):
    """OAuth redirect URI — exchanges code for tokens, redirects to /."""
    fhir: FhirClient = app.state.fhir
    try:
        ts = await fhir._oauth.exchange_code(fhir._http, code, state)
    except Exception as e:  # noqa: BLE001
        logger.exception("oauth callback failed")
        raise HTTPException(status_code=400, detail=f"oauth_callback_failed: {e}")
    return RedirectResponse(
        f"/?physician_user_id={ts.physician_user_id}", status_code=302
    )


@app.get("/v1/oauth/dev-launch")
async def oauth_dev_launch(
    physician_user_id: str, settings: Settings = Depends(get_settings)
):
    """Demo-safe direct token mint via per-physician password grant.

    Reads SMART_DEV_CREDENTIALS for the requested physician and mints a
    token. Useful when the SMART /authorize handshake from OpenEMR is
    flaky during the demo recording.
    """
    if not settings.smart_dev_launch_enabled:
        raise HTTPException(status_code=404, detail="dev_launch_disabled")
    fhir: FhirClient = app.state.fhir
    try:
        await fhir._oauth._dev_launch(fhir._http, physician_user_id)
    except Exception as e:  # noqa: BLE001
        raise HTTPException(status_code=400, detail=f"dev_launch_failed: {e}")
    return {"ok": True, "physician_user_id": physician_user_id}


class ChatRequest(BaseModel):
    session_id: str
    question: str
    proposed_drug: str | None = Field(
        default=None,
        description="UC3 helper — pass when the question is 'is X safe to add?'",
    )


@app.post("/v1/chat")
async def chat(body: ChatRequest, settings: Settings = Depends(get_settings)):
    session = sessions.get(body.session_id)
    if not session:
        raise HTTPException(status_code=404, detail="session not found or expired")
    fhir: FhirClient = app.state.fhir

    # Build prior_turns from stored messages so a resumed conversation
    # carries history into the LLM context. Pairs are (user, assistant) by
    # turn_index. Capped downstream by settings.resume_replay_max_turns.
    store: ConversationStore = app.state.conv_store
    prior_turns: list[PriorTurn] = []
    try:
        stored = await store.get_messages(body.session_id)
        pending_q: str | None = None
        for m in stored:
            if m.role == "user":
                pending_q = m.content
            elif m.role == "assistant" and pending_q is not None:
                prior_turns.append(
                    PriorTurn(question=pending_q, assistant_prose=m.content)
                )
                pending_q = None
    except Exception:  # noqa: BLE001
        logger.exception("failed to load prior turns; continuing without history")

    output = await run_turn(
        settings=settings,
        fhir=fhir,
        session=session,
        question=body.question,
        proposed_drug=body.proposed_drug,
        prior_turns=prior_turns or None,
    )
    payload = {
        "response": output.response.model_dump(),
        "trace": output.trace.model_dump(),
    }
    app.state.tracer.emit(output.trace, payload["response"])

    # Persist this turn for resume. The pseudonym snapshot is taken AFTER the
    # turn so any provider pseudonyms minted during tool calls are captured.
    # Best-effort — a DB hiccup must not fail the user's chat response.
    try:
        await store.append_turn(
            conversation_id=body.session_id,
            question=body.question,
            assistant_prose=output.response.prose,
            claims=[c.model_dump() for c in output.response.claims] or None,
            data_gaps=list(output.response.data_gaps) or None,
            pseudonym_map=session.snapshot(),
        )
    except Exception:  # noqa: BLE001
        logger.exception("failed to persist conversation turn (non-fatal)")

    return payload


# ---------------- Resume previous chat ----------------


class RecentResponse(BaseModel):
    found: bool
    conversation_id: str | None = None
    last_used_at: str | None = None
    turn_count: int | None = None
    patient_pseudonym: str | None = None


@app.get("/v1/sessions/recent", response_model=RecentResponse)
async def sessions_recent(
    physician_user_id: str,
    patient_id: str,
    settings: Settings = Depends(get_settings),
):
    """Probe for a resumable conversation for (physician, patient).

    Frontend calls this on iframe load before starting a fresh session. If
    a hit is returned, the iframe shows the "Resume previous chat?" banner.
    """
    store: ConversationStore = app.state.conv_store
    recent = await store.find_recent(
        physician_user_id=physician_user_id,
        active_patient_id=patient_id,
        window_hours=settings.resume_window_hours,
    )
    if recent is None:
        return RecentResponse(found=False)
    return RecentResponse(
        found=True,
        conversation_id=recent.conversation_id,
        last_used_at=recent.last_used_at,
        turn_count=recent.turn_count,
        patient_pseudonym=recent.patient_pseudonym,
    )


class ResumeRequest(BaseModel):
    conversation_id: str


class ResumeMessage(BaseModel):
    role: str
    content: str
    claims: list[dict] | None = None
    data_gaps: list[str] | None = None


class ResumeResponse(BaseModel):
    session_id: str
    patient_pseudonym: str
    messages: list[ResumeMessage]


@app.post("/v1/sessions/resume", response_model=ResumeResponse)
async def sessions_resume(
    body: ResumeRequest, settings: Settings = Depends(get_settings)
):
    """Rehydrate a previous conversation: same session_id, same pseudonyms.

    Re-runs the A.7 panel gate — panel membership can change between
    sessions, and a stale resumable conversation must not bypass that.
    """
    store: ConversationStore = app.state.conv_store
    row = await store.get(body.conversation_id)
    if row is None:
        raise HTTPException(status_code=404, detail="conversation not found")

    # Re-check panel membership before exposing prior messages.
    fhir: FhirClient = app.state.fhir
    await _verify_patient_in_panel(
        fhir, row.physician_user_id, row.active_patient_id, settings
    )

    # Rehydrate the in-memory PseudonymMap so the pseudonyms in stored
    # messages stay consistent with the new turn's _user_prefix.
    sessions.rehydrate(
        session_id=row.conversation_id,
        physician_user_id=row.physician_user_id,
        active_patient_id=row.active_patient_id,
        snapshot=row.pseudonym_map,
    )
    await store.touch(row.conversation_id)

    stored = await store.get_messages(row.conversation_id)
    messages = [
        ResumeMessage(
            role=m.role, content=m.content, claims=m.claims, data_gaps=m.data_gaps
        )
        for m in stored
    ]
    return ResumeResponse(
        session_id=row.conversation_id,
        patient_pseudonym=row.patient_pseudonym,
        messages=messages,
    )


@app.post("/v1/sessions/{session_id}/end")
async def sessions_end(session_id: str):
    """Mark a conversation as ended so it stops being offered for resume."""
    store: ConversationStore = app.state.conv_store
    await store.end(session_id)
    sessions.end(session_id)
    return {"ok": True}


@app.post("/v1/documents/attach")
async def attach_document(
    file: UploadFile = File(...),
    patient_id: str = Form(...),
    doc_type: str = Form(...),
    mime_type: str = Form(...),
    physician_user_id: str = Form(...),
    settings: Settings = Depends(get_settings),
):
    """Accept a multipart document upload, run panel check, then ingest via VLM."""
    # Re-validate via the same Pydantic model the architecture documents.
    try:
        AttachDocumentRequest(doc_type=doc_type, mime_type=mime_type)
    except ValueError as e:
        raise HTTPException(status_code=422, detail=str(e))

    fhir: FhirClient = app.state.fhir
    await _verify_patient_in_panel(fhir, physician_user_id, patient_id, settings)

    file_bytes = await file.read()
    if not file_bytes:
        raise HTTPException(status_code=400, detail="empty_file")

    svc = app.state.ingestion_service
    result = await svc.attach_and_extract(
        patient_fhir_id=patient_id,
        patient_pseudonym=patient_id,  # MVP: pseudonym == fhir_id; rotated in post-MVP
        doc_type=doc_type,             # type: ignore[arg-type]  validated above
        mime_type=mime_type,           # type: ignore[arg-type]
        file_bytes=file_bytes,
        physician_user_id=physician_user_id,
    )
    return {
        "doc_id": result.doc_id,
        "was_dedup_hit": result.was_dedup_hit,
        "extraction": result.extraction.model_dump(mode="json"),
        "bbox_overlay": [
            {
                "page": item.page,
                "bbox": item.bbox.model_dump(),
                "field_or_chunk_id": item.field_or_chunk_id,
                "record_id": item.record_id,
                "raw_text": item.raw_text,
            }
            for item in result.bbox_overlay
        ],
    }


@app.get("/v1/documents/{doc_id}/preview")
async def get_document_preview(doc_id: str, physician_user_id: str):
    fhir = app.state.fhir_client
    doc = await fhir.get_resource(
        "DocumentReference", doc_id, physician_user_id=physician_user_id
    )
    content = doc.get("content") or []
    if not content or "attachment" not in content[0]:
        raise HTTPException(status_code=404, detail="no_attachment")
    att = content[0]["attachment"]
    media_type = att.get("contentType", "application/octet-stream")
    raw = att.get("data")
    if not raw:
        raise HTTPException(status_code=404, detail="empty_attachment")
    return Response(content=base64.b64decode(raw), media_type=media_type)


@app.get("/v1/documents/{doc_id}/extractions")
async def get_document_extractions(doc_id: str, patient_id: str):
    store = app.state.processed_documents
    row = await store.lookup_by_doc_id(
        patient_pseudonym=patient_id, canonical_doc_id=doc_id
    )
    if row is None:
        raise HTTPException(status_code=404, detail="extraction_not_found")
    return {
        "doc_id": doc_id,
        "doc_type": row.doc_type,
        "extracted_at": row.extracted_at.isoformat(),
        "extraction": row.extracted_facts,
    }
