"""Integration tests for /v1/sessions/{id}/pending_intakes (W2 KR5 lite)."""
from __future__ import annotations

import json

import pytest
from fastapi.testclient import TestClient

from app.phi.session import sessions as session_store


PATIENT_ID = "patient-uuid-pending"
PHYSICIAN = "dr_pending"


@pytest.fixture
def app_client(monkeypatch, tmp_path):
    monkeypatch.setenv("CONVERSATION_DB_PATH", str(tmp_path / "copilot.db"))
    # Isolate the W2 processed_documents store too — without this, prior tests'
    # records leak into the empty-patient assertion.
    monkeypatch.setenv("COPILOT_DOCS_DB_PATH", str(tmp_path / "copilot_docs.db"))
    monkeypatch.setenv(
        "PHYSICIAN_PATIENT_PANEL", json.dumps({PHYSICIAN: [PATIENT_ID]})
    )
    from app.config import get_settings
    get_settings.cache_clear()
    session_store._map.clear()  # type: ignore[attr-defined]

    from app import main as main_module

    async def _noop_panel(*args, **kwargs):
        return None
    monkeypatch.setattr(main_module, "_verify_patient_in_panel", _noop_panel)

    with TestClient(main_module.app) as c:
        yield c


def _start(client: TestClient) -> str:
    r = client.post(
        "/v1/sessions",
        json={"patient_id": PATIENT_ID, "physician_user_id": PHYSICIAN},
    )
    assert r.status_code == 200, r.text
    return r.json()["session_id"]


def test_pending_intakes_returns_empty_for_fresh_patient(app_client) -> None:
    sid = _start(app_client)
    r = app_client.get(f"/v1/sessions/{sid}/pending_intakes")
    assert r.status_code == 200
    body = r.json()
    assert body["count"] == 0
    assert body["items"] == []


def test_pending_intakes_returns_404_for_unknown_session(app_client) -> None:
    r = app_client.get("/v1/sessions/no-such-session/pending_intakes")
    assert r.status_code == 404


@pytest.mark.asyncio
async def test_pending_intakes_returns_front_desk_uploads_only(
    app_client,
) -> None:
    """Banner copy reads 'uploaded by front desk' — we must surface only
    docs with source_path='front_desk_scan', not the physician's own
    iframe drop-zone uploads (source_path='attach_route').
    """
    sid = _start(app_client)

    # Inject one front-desk upload + one physician self-upload.
    from app import main as main_module
    store = main_module.app.state.processed_documents
    await store.record(
        patient_pseudonym=PATIENT_ID,
        hash="sha3-512-front-desk",
        canonical_doc_id="copilot-front-desk-doc",
        doc_type="lab_doc",
        extracted_facts={"results": []},
        source_path="front_desk_scan",
        file_bytes=b"%PDF-front-desk",
        mime_type="application/pdf",
    )
    await store.record(
        patient_pseudonym=PATIENT_ID,
        hash="sha3-512-physician-self",
        canonical_doc_id="copilot-physician-self",
        doc_type="lab_doc",
        extracted_facts={"results": []},
        source_path="attach_route",  # physician's own iframe upload
        file_bytes=b"%PDF-self",
        mime_type="application/pdf",
    )

    r = app_client.get(f"/v1/sessions/{sid}/pending_intakes")
    assert r.status_code == 200
    body = r.json()
    # Only the front-desk one surfaces; the physician self-upload is filtered out.
    assert body["count"] == 1
    assert body["items"][0]["doc_id"] == "copilot-front-desk-doc"


def test_pending_intakes_response_shape_is_stable(app_client) -> None:
    """The response must always have items + count keys, even when empty."""
    sid = _start(app_client)
    r = app_client.get(f"/v1/sessions/{sid}/pending_intakes")
    body = r.json()
    assert set(body.keys()) == {"items", "count"}
