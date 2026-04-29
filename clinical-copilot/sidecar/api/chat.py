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
    text: str
    verdict: str
    candidates: list[dict] = Field(default_factory=list)
    chart_error_flags: list[dict] = Field(default_factory=list)
    annotations: list[str] = Field(default_factory=list)
    data_gaps: list[str] = Field(default_factory=list)
    dropped: list[str] = Field(default_factory=list)
    telemetry: dict = Field(default_factory=dict)


@router.post("/chat", response_model=ChatResponse)
async def chat(
    body: ChatRequest,
    authorization: str | None = Header(default=None),
) -> ChatResponse:
    """Handle one turn of the conversation.

    In production this endpoint requires a BFF-minted task token in the
    Authorization header. Demo mode accepts an empty header so the
    bundled HTML chat UI can talk to it directly.
    """
    settings = get_settings()
    snapshot = _snapshot_for(body.patient_id)
    cfg = GraphConfig(
        purpose=body.purpose,
        user_id=body.user_id,
        settings=settings,
        audit_log=_AUDIT_LOG,
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
        },
    )
    return ChatResponse(
        text=response.text,
        verdict=response.verdict,
        candidates=response.candidates,
        chart_error_flags=response.chart_error_flags,
        annotations=response.annotations,
        data_gaps=response.data_gaps,
        dropped=response.dropped,
        telemetry=response.telemetry,
    )


@router.get("/audit/head")
def audit_head() -> dict[str, str | int]:
    """Return the latest audit chain head and length.

    Used by the periodic anchoring agent (ARCHITECTURE.md §6.3).
    """
    head = _AUDIT_LOG.head_hash().hex()
    length = sum(1 for _ in _AUDIT_LOG)
    chain_ok = _AUDIT_LOG.verify_chain()
    return {"head_hash": head, "length": length, "chain_intact": chain_ok}


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


@router.get("/patients", response_model=list[str])
def list_known_patients() -> list[str]:
    """Demo helper: which fixtures are available."""
    _load_fixtures()
    return sorted(_FIXTURE_BY_PATIENT_ID.keys())
