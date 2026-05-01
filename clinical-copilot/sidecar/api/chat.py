"""HTTP routes: chat + health.

The sidecar speaks JSON over HTTP. The BFF (separate service in ``bff/``)
mints the bearer token and forwards. The sidecar trusts the BFF over mTLS
plus a signed JWT (ARCHITECTURE.md §1.1 topology diagram).
"""

from __future__ import annotations

import logging
from pathlib import Path
from typing import Literal

from fastapi import APIRouter, Header, HTTPException, status
from fastapi.responses import HTMLResponse
from pydantic import BaseModel, Field

from sidecar.agent.graph import GraphConfig, run_graph
from sidecar.audit import InMemoryAuditLog
from sidecar.config import get_settings
from sidecar.snapshot import PatientSnapshot, build_snapshot_from_fixture

logger = logging.getLogger(__name__)

router = APIRouter()

# Singleton audit log per process. Production swaps in PostgresAuditLog.
_AUDIT_LOG = InMemoryAuditLog()

# Map of fixture name → bundled JSON file. Used for the demo so a working
# session does not require a running OpenEMR. Production would call the
# BFF for the snapshot.
_FIXTURE_DIR = Path(__file__).resolve().parent.parent.parent / "fixtures" / "patients"
_FIXTURE_BY_PATIENT_ID: dict[str, Path] = {}


def _load_fixtures() -> None:
    if _FIXTURE_BY_PATIENT_ID:
        return
    if not _FIXTURE_DIR.exists():
        return
    for path in _FIXTURE_DIR.glob("*.json"):
        try:
            snapshot = build_snapshot_from_fixture(path)
        except Exception as exc:  # noqa: BLE001
            logger.warning("fixture_load_failed", extra={"path": str(path), "err": str(exc)})
            continue
        _FIXTURE_BY_PATIENT_ID[snapshot.patient_id] = path


def _snapshot_for(patient_id: str) -> PatientSnapshot:
    """Look up a snapshot by patient id.

    Demo path: load from ``fixtures/patients/`` if a matching fixture exists.
    Production path would call the BFF, which calls the FHIR snapshot
    service (sidecar.snapshot.SnapshotService).
    """
    _load_fixtures()
    if patient_id in _FIXTURE_BY_PATIENT_ID:
        return build_snapshot_from_fixture(_FIXTURE_BY_PATIENT_ID[patient_id])
    raise HTTPException(
        status_code=status.HTTP_404_NOT_FOUND,
        detail=f"snapshot for {patient_id!r} not available; in production this proxies to the BFF",
    )


class ChatRequest(BaseModel):
    patient_id: str = Field(description="FHIR Patient/{uuid} resource id")
    purpose: Literal["diagnostic_cross_check", "chart_error_scan", "follow_up_question"]
    user_id: str = Field(default="dr.m@example.org")
    message: str | None = Field(
        default=None, description="Optional follow-up message text. The first turn for "
        "diagnostic_cross_check or chart_error_scan does not need a message."
    )


class ChatResponse(BaseModel):
    verdict: str
    candidates: list[dict] = Field(default_factory=list)
    chart_error_flags: list[dict] = Field(default_factory=list)
    data_gaps: list[str] = Field(default_factory=list)
    dropped: list[str] = Field(default_factory=list)
    telemetry: dict = Field(default_factory=dict)


@router.post("/chat", response_model=ChatResponse)
async def chat(
    body: ChatRequest,
    authorization: str | None = Header(default=None),
    mock: int = 0,
) -> ChatResponse:
    """Handle one turn of the conversation.

    In production this endpoint requires a BFF-minted task token in the
    Authorization header. Demo mode accepts an empty header so the
    bundled HTML chat UI can talk to it directly.

    ``?mock=1`` forces the deterministic mock provider, but is rejected
    unless ``COPILOT_ALLOW_MOCK=true`` (production deployments leave it
    unset; the OpenEMR launch button never sends the flag).
    """
    from sidecar.agent.graph import make_provider
    settings = get_settings()
    snapshot = _snapshot_for(body.patient_id)
    force_mock = bool(mock) and getattr(settings, "allow_mock", False)
    if mock and not force_mock:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="?mock=1 requires COPILOT_ALLOW_MOCK=true",
        )
    cfg = GraphConfig(
        purpose=body.purpose,
        user_id=body.user_id,
        settings=settings,
        audit_log=_AUDIT_LOG,
        provider=make_provider(settings, force_mock=force_mock),
    )
    response = await run_graph(snapshot, cfg)
    logger.info(
        "chat_handled",
        extra={
            "patient_id": body.patient_id,
            "purpose": body.purpose,
            "verdict": response.verdict,
            "pair_count": response.telemetry.get("total_pair_count"),
            "auth_present": bool(authorization),
            "mock": force_mock,
        },
    )
    return ChatResponse(
        verdict=response.verdict,
        candidates=response.candidates,
        chart_error_flags=response.chart_error_flags,
        data_gaps=response.data_gaps,
        dropped=response.dropped,
        telemetry=response.telemetry,
    )


@router.get("/snapshot/{patient_uuid}")
def get_snapshot(patient_uuid: str) -> dict:
    """Return the deterministically-reconciled patient snapshot as JSON.

    Demo path: load the matching fixture. Production path: this endpoint is
    kept identical, but is fronted by the BFF which mints a 5-minute task
    token bound to ``Patient/{uuid}`` (ARCHITECTURE.md §3.2). Without a
    valid token the BFF refuses to proxy.
    """
    snapshot = _snapshot_for(f"Patient/{patient_uuid}")
    return snapshot.model_dump(mode="json")


@router.get("/audit/head")
def audit_head() -> dict[str, str | int]:
    """Return the latest audit chain head and length.

    Used by the periodic anchoring agent (ARCHITECTURE.md §6.3).
    """
    head = _AUDIT_LOG.head_hash().hex()
    length = sum(1 for _ in _AUDIT_LOG)
    chain_ok = _AUDIT_LOG.verify_chain()
    return {"head_hash": head, "length": length, "chain_intact": chain_ok}


@router.get("/audit/list")
def audit_list(limit: int = 50) -> list[dict]:
    """Return the most recent audit entries (newest first).

    Each entry is one ``/chat`` invocation with prompt fingerprint,
    redacted summary, verdict, telemetry, and the chain-of-custody hash
    that proves it hasn't been tampered with. Demo-mode visibility for
    eyeballing what the LLM saw and decided. In production this would
    require admin-tier auth and would page via a cursor.
    """
    entries = list(_AUDIT_LOG)
    entries.reverse()
    out: list[dict] = []
    for e in entries[:limit]:
        out.append({
            "ts": e.ts.isoformat() if hasattr(e.ts, "isoformat") else str(e.ts),
            "patient_id": e.patient_id,
            "user_id": e.user_id,
            "purpose": e.purpose,
            "verdict": e.verdict,
            "prompt_fingerprint": e.prompt_fingerprint,
            "summary": e.redacted_summary,
            "telemetry": e.telemetry,
            "row_hash": e.row_hash.hex() if isinstance(e.row_hash, (bytes, bytearray)) else str(e.row_hash),
            "prev_hash": e.prev_hash.hex() if isinstance(e.prev_hash, (bytes, bytearray)) else str(e.prev_hash),
        })
    return out


@router.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok"}


@router.get("/", response_class=HTMLResponse)
def root_ui() -> HTMLResponse:
    """Serve the embedded chat UI."""
    ui_path = Path(__file__).resolve().parent.parent.parent / "ui" / "chat.html"
    if not ui_path.exists():
        return HTMLResponse("<html><body><p>UI not bundled.</p></body></html>")
    return HTMLResponse(ui_path.read_text(encoding="utf-8"))


@router.get("/demo", response_class=HTMLResponse)
@router.get("/demo/", response_class=HTMLResponse)
def demo_ui() -> HTMLResponse:
    """Serve the chat UI in deterministic-mock mode for demo recordings.

    Same UI, same patient picker, but every ``/chat`` call goes through
    ``?mock=1`` so the seed-table answers fire regardless of OpenAI
    availability. Requires ``COPILOT_ALLOW_MOCK=true`` in the env;
    otherwise the inner ``/chat?mock=1`` calls 403.
    """
    settings = get_settings()
    if not getattr(settings, "allow_mock", False):
        return HTMLResponse(
            "<html><body style='font-family:system-ui;max-width:540px;margin:48px auto;'>"
            "<h2>/demo is disabled</h2>"
            "<p>Set <code>COPILOT_ALLOW_MOCK=true</code> in your <code>.env</code> "
            "and restart the sidecar to enable the demo route.</p>"
            "</body></html>",
            status_code=status.HTTP_403_FORBIDDEN,
        )
    ui_path = Path(__file__).resolve().parent.parent.parent / "ui" / "chat.html"
    if not ui_path.exists():
        return HTMLResponse("<html><body><p>UI not bundled.</p></body></html>")
    html = ui_path.read_text(encoding="utf-8")
    # Inject a flag the JS reads to route every /chat call to /chat?mock=1.
    inject = (
        "<script>window.__COPILOT_DEMO__ = true;</script>"
        "<style>header.app::after{content:'DEMO (deterministic mock data)';"
        "background:#fde68a;color:#92400e;padding:3px 10px;border-radius:99px;"
        "font-size:11px;font-weight:700;margin-left:auto;letter-spacing:.04em;}</style>"
    )
    html = html.replace("</head>", inject + "</head>", 1)
    return HTMLResponse(html)


@router.get("/patients", response_model=list[str])
def list_known_patients() -> list[str]:
    """Demo helper: which fixtures are available."""
    _load_fixtures()
    return sorted(_FIXTURE_BY_PATIENT_ID.keys())
