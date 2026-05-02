"""FastAPI entry — Clinical Co-Pilot."""
from __future__ import annotations

import logging
import uuid
from contextlib import asynccontextmanager
from pathlib import Path

from fastapi import Depends, FastAPI, HTTPException
from fastapi.responses import FileResponse, JSONResponse, RedirectResponse
from pydantic import BaseModel, Field

from app.agent.loop import run_turn
from app.config import Settings, get_settings
from app.fhir.client import FhirClient, FhirError
from app.observability.trace import get_tracer
from app.phi.session import sessions

logger = logging.getLogger("copilot.main")


@asynccontextmanager
async def lifespan(app: FastAPI):
    settings = get_settings()
    app.state.fhir = FhirClient(settings)
    app.state.tracer = get_tracer(settings)
    yield
    await app.state.fhir.aclose()


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
async def index():
    return FileResponse(WEB_DIR / "index.html")


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


async def _verify_patient_in_panel(
    fhir: FhirClient, physician: str, patient_id: str
) -> None:
    """A.7 panel gate: confirm the patient is assigned to this physician.

    Raises HTTPException(403, "patient_out_of_panel") on mismatch.
    Mechanism:
      1. Resolve the physician's Practitioner UUID from their id_token's
         `fhirUser` claim (cached on TokenSet by oauth._password_grant).
      2. Fetch the Patient resource using the physician's own token (the
         FHIR layer doesn't filter by providerID, so this returns 200
         regardless — but the response gives us `generalPractitioner`).
      3. Compare `Patient.generalPractitioner[].reference` against the
         physician's UUID; mismatch → deny.

    A physician with no resolvable Practitioner UUID (e.g. admin) is
    allowed unconditionally — admins bypass scope by design. A patient
    with no `generalPractitioner` fails closed (no physician owns it).
    """
    practitioner_uuid = await fhir._oauth.resolve_practitioner_uuid(
        fhir._http, physician
    )
    if not practitioner_uuid:
        # Admin or system caller — unconditionally allowed (matches
        # OpenEMR's UI behavior where admins see all patients).
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
            "panel deny: physician=%s practitioner=%s patient=%s owners=%s",
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
        logger.warning(
            "session %s started without physician_user_id — using demo fallback %s "
            "(SMART launch path is bypassed)",
            session_id,
            physician,
        )

    # A.7 — panel gate. Verifies Patient.generalPractitioner before
    # creating the session. Admin (no Practitioner UUID) bypasses.
    fhir: FhirClient = app.state.fhir
    await _verify_patient_in_panel(fhir, physician, body.patient_id)

    pseudo = sessions.create(session_id, physician, body.patient_id)
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
    output = await run_turn(
        settings=settings,
        fhir=fhir,
        session=session,
        question=body.question,
        proposed_drug=body.proposed_drug,
    )
    payload = {
        "response": output.response.model_dump(),
        "trace": output.trace.model_dump(),
    }
    app.state.tracer.emit(output.trace, payload["response"])
    return payload
