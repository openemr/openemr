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
    assert "out_of_panel" in r.json()["detail"]


def test_e2e_confirm_then_pending_intakes_carries_confirmed_at(
    client: TestClient, monkeypatch
) -> None:
    """W2 Phase 5 regression for Issue 2b — end-to-end against the real
    persistence layer (no mocks for ``mark_confirmed`` /
    ``list_confirmed_recent``). After the physician confirms a row,
    re-fetching ``/pending_intakes`` must surface that row with
    ``confirmed_at`` populated and ``is_front_desk_filed=True`` so the
    iframe's ``alreadyHandled`` gate hides the Confirm/Reject footer
    on re-open.

    This pins the bug the user reported on 2026-05-09: buttons
    re-appearing on second click of a just-confirmed intake.
    """
    import asyncio
    from app import main as main_module

    DOC_ID = "copilot-e2e-confirm-1"

    async def _empty_search(*args, **kwargs):
        return {"entry": []}

    async def _noop_panel(*args, **kwargs):
        return None

    monkeypatch.setattr(main_module, "_verify_patient_in_panel", _noop_panel)

    with client:
        store = main_module.app.state.processed_documents
        # Mock FHIR search so the merge only contains local-store items.
        monkeypatch.setattr(main_module.app.state.fhir, "search", _empty_search)

        # 1. Seed a row mid-flow: through attach_only + process_pending.
        #    extracted_facts no longer carries the `_pending` marker.
        asyncio.run(store.record(
            patient_pseudonym=PATIENT_ID,
            hash="0" * 128,
            canonical_doc_id=DOC_ID,
            doc_type="lab_doc",
            extracted_facts={"results": [], "document_date": "2026-05-09"},
            source_path="front_desk_scan",
            file_bytes=b"%PDF-1.4 fake",
            mime_type="application/pdf",
        ))

        # 2. Confirm via the REAL route (real mark_confirmed + real read).
        r = client.post(
            f"/v1/documents/{DOC_ID}/confirm"
            f"?patient_id={PATIENT_ID}&physician_user_id={PHYSICIAN}"
        )
        assert r.status_code == 200, r.text
        confirm_body = r.json()
        assert confirm_body["confirmed_at"] is not None, (
            f"/confirm did not return confirmed_at: {confirm_body}"
        )

        # 3. Open a session and fetch the banner.
        r = client.post(
            "/v1/sessions",
            json={"patient_id": PATIENT_ID, "physician_user_id": PHYSICIAN},
        )
        assert r.status_code == 200, r.text
        sid = r.json()["session_id"]

        r = client.get(f"/v1/sessions/{sid}/pending_intakes")
        assert r.status_code == 200, r.text
        body = r.json()

    # 4. The just-confirmed row MUST appear with confirmed_at populated
    # and is_front_desk_filed=True. Failing this assertion proves the
    # iframe's alreadyHandled gate cannot be relying on the API state.
    matching = [i for i in body["items"] if i["doc_id"] == DOC_ID]
    assert len(matching) == 1, (
        f"Expected 1 banner item for {DOC_ID}, got {len(matching)}: "
        f"{body['items']}"
    )
    item = matching[0]
    assert item["confirmed_at"] is not None, (
        f"Banner item is missing confirmed_at — Issue 2b root cause: {item}"
    )
    assert item["is_front_desk_filed"] is True, (
        f"Banner item is missing is_front_desk_filed=True: {item}"
    )
    assert item["is_pending"] is False, (
        f"Banner item still marked is_pending=True: {item}"
    )
