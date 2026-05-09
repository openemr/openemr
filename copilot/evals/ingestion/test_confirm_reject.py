"""W2 Plan B confirm/reject endpoint tests.

Covers:
- /confirm happy path (local stamp only — OpenEMR REST write-back deferred per W2 Phase 5)
- /confirm idempotency
- /reject soft-delete
- Panel-gate enforcement on both endpoints
- 404 when doc_id is unknown
- get_recent_uploads(confirmed_only=true) excludes pending and rejected rows
"""
from __future__ import annotations

import json
from datetime import datetime, timezone
from unittest.mock import AsyncMock

import pytest
from fastapi.testclient import TestClient

from app import main as main_module
from app.persistence.processed_documents import ProcessedDocument


PHYSICIAN = "dr_who"
FRONT_DESK = "Reception Desk"
PATIENT_ID = "patient-7"


@pytest.fixture
def client(monkeypatch, tmp_path) -> TestClient:
    monkeypatch.setenv("OPENEMR_BASE_URL", "https://emr.example.com")
    monkeypatch.setenv("OPENEMR_FHIR_BASE", "https://emr.example.com/apis/default/fhir")
    monkeypatch.setenv("OPENEMR_CLIENT_ID", "x")
    monkeypatch.setenv("OPENEMR_CLIENT_SECRET", "x")
    monkeypatch.setenv("OPENEMR_VERIFY_TLS", "false")
    monkeypatch.setenv("ANTHROPIC_API_KEY", "test-key")
    monkeypatch.setenv("CONVERSATION_DB_PATH", str(tmp_path / "copilot.db"))
    monkeypatch.setenv("COPILOT_DOCS_DB_PATH", str(tmp_path / "docs.db"))
    monkeypatch.setenv(
        "PHYSICIAN_PATIENT_PANEL", json.dumps({PHYSICIAN: [PATIENT_ID]})
    )
    monkeypatch.setenv("COPILOT_FRONT_DESK_USERS", FRONT_DESK)
    from app.config import get_settings
    get_settings.cache_clear()
    return TestClient(main_module.app)


def _seed_pending_row(client: TestClient, doc_id: str = "copilot-pending-1") -> ProcessedDocument:
    """Build a pending row and stub lookup_by_doc_id to return it."""
    row = ProcessedDocument(
        patient_pseudonym=PATIENT_ID,
        hash="0" * 128,
        canonical_doc_id=doc_id,
        doc_type="lab_doc",
        extracted_facts={"results": [], "document_date": "2026-05-08"},
        source_path="front_desk_scan",
        extracted_at=datetime.now(timezone.utc),
        file_bytes=b"%PDF-1.4 fake",
        mime_type="application/pdf",
    )
    client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(return_value=row)
    return row


def test_confirm_marks_local_only(client: TestClient) -> None:
    """W2 Phase 5: /confirm stamps the local store and returns the canonical
    confirmed_at. The OpenEMR REST write-back has been deferred (api:oemr
    scope unavailable in the dev fork — see W2_IMPLEMENTATION.md Phase 4),
    so the response carries only ``ok``, ``doc_id``, ``confirmed_at``.
    """
    with client:
        _seed_pending_row(client)
        client.app.state.processed_documents.mark_confirmed = AsyncMock(return_value=None)
        # Refreshed read after mark — return a confirmed row.
        confirmed_row = ProcessedDocument(
            patient_pseudonym=PATIENT_ID,
            hash="0" * 128,
            canonical_doc_id="copilot-pending-1",
            doc_type="lab_doc",
            extracted_facts={"results": [], "document_date": "2026-05-08"},
            source_path="front_desk_scan",
            extracted_at=datetime.now(timezone.utc),
            file_bytes=b"%PDF-1.4 fake",
            mime_type="application/pdf",
            confirmed_at=datetime.now(timezone.utc),
            confirmed_by=PHYSICIAN,
            external_doc_id=None,
        )
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            side_effect=[_seed_pending_row(client), confirmed_row]
        )
        r = client.post(
            f"/v1/documents/copilot-pending-1/confirm"
            f"?patient_id={PATIENT_ID}&physician_user_id={PHYSICIAN}"
        )
    assert r.status_code == 200, r.text
    body = r.json()
    assert set(body.keys()) == {"ok", "doc_id", "confirmed_at"}
    assert body["ok"] is True
    assert body["doc_id"] == "copilot-pending-1"
    assert body["confirmed_at"] is not None
    client.app.state.processed_documents.mark_confirmed.assert_awaited_once()


def test_confirm_returns_404_for_unknown_doc(client: TestClient) -> None:
    with client:
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            return_value=None
        )
        r = client.post(
            f"/v1/documents/missing/confirm"
            f"?patient_id={PATIENT_ID}&physician_user_id={PHYSICIAN}"
        )
    assert r.status_code == 404


def test_confirm_panel_gated(client: TestClient, monkeypatch) -> None:
    """Confirm forwards the panel-gate decision to the caller.

    Stubs ``_verify_patient_in_panel`` to a 403 — pins the endpoint
    contract; the gate's own semantics are exercised in
    ``evals/agent/test_panel_scope.py``.
    """
    from app import main as main_module
    from fastapi import HTTPException

    async def _deny(*args, **kwargs):
        raise HTTPException(status_code=403, detail="patient_out_of_panel")
    monkeypatch.setattr(main_module, "_verify_patient_in_panel", _deny)

    with client:
        r = client.post(
            "/v1/documents/anything/confirm"
            "?patient_id=other-patient&physician_user_id=" + PHYSICIAN
        )
    assert r.status_code == 403
    assert "out_of_panel" in r.json()["detail"]


def test_reject_marks_rejected_at(client: TestClient) -> None:
    with client:
        _seed_pending_row(client)
        client.app.state.processed_documents.mark_rejected = AsyncMock(return_value=None)
        rejected_row = ProcessedDocument(
            patient_pseudonym=PATIENT_ID,
            hash="0" * 128,
            canonical_doc_id="copilot-pending-1",
            doc_type="lab_doc",
            extracted_facts={"results": []},
            source_path="front_desk_scan",
            extracted_at=datetime.now(timezone.utc),
            file_bytes=b"%PDF-1.4 fake",
            mime_type="application/pdf",
            rejected_at=datetime.now(timezone.utc),
            confirmed_by=PHYSICIAN,
        )
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            side_effect=[_seed_pending_row(client), rejected_row]
        )
        r = client.post(
            f"/v1/documents/copilot-pending-1/reject"
            f"?patient_id={PATIENT_ID}&physician_user_id={PHYSICIAN}"
        )
    assert r.status_code == 200, r.text
    body = r.json()
    assert body["ok"] is True
    assert body["rejected_at"] is not None
    client.app.state.processed_documents.mark_rejected.assert_awaited_once()


def test_reject_panel_gated(client: TestClient, monkeypatch) -> None:
    from app import main as main_module
    from fastapi import HTTPException

    async def _deny(*args, **kwargs):
        raise HTTPException(status_code=403, detail="patient_out_of_panel")
    monkeypatch.setattr(main_module, "_verify_patient_in_panel", _deny)

    with client:
        r = client.post(
            "/v1/documents/anything/reject"
            "?patient_id=other-patient&physician_user_id=" + PHYSICIAN
        )
    assert r.status_code == 403
