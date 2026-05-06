"""Integration tests for /v1/sessions/{id}/pending_intakes (W2 KR5 lite)."""
from __future__ import annotations

import json
from datetime import datetime
from typing import Any

import pytest
from fastapi.testclient import TestClient

from app.persistence.processed_documents import ProcessedDocument
from app.phi.session import sessions as session_store


PATIENT_ID = "patient-uuid-pending"
PHYSICIAN = "dr_pending"


@pytest.fixture
def app_client(monkeypatch, tmp_path):
    monkeypatch.setenv("CONVERSATION_DB_PATH", str(tmp_path / "copilot.db"))
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
async def test_pending_intakes_returns_processed_doc_when_present(
    app_client,
) -> None:
    sid = _start(app_client)

    # Inject a processed document directly via the store.
    from app import main as main_module
    store = main_module.app.state.processed_documents
    await store.record(
        patient_pseudonym=PATIENT_ID,  # store keyed by raw FHIR uuid
        hash="sha3-512-fake",
        canonical_doc_id="copilot-fake-doc-1",
        doc_type="lab_doc",
        extracted_facts={"results": []},
        source_path="attach_route",  # validated enum: attach_route | front_desk_scan
        file_bytes=b"%PDF-fake",
        mime_type="application/pdf",
    )

    r = app_client.get(f"/v1/sessions/{sid}/pending_intakes")
    assert r.status_code == 200
    body = r.json()
    assert body["count"] == 1
    item = body["items"][0]
    assert item["doc_id"] == "copilot-fake-doc-1"
    assert item["doc_type"] == "lab_doc"
    assert item["mime_type"] == "application/pdf"
    assert "uploaded_at" in item


def test_pending_intakes_response_shape_is_stable(app_client) -> None:
    """The response must always have items + count keys, even when empty."""
    sid = _start(app_client)
    r = app_client.get(f"/v1/sessions/{sid}/pending_intakes")
    body = r.json()
    assert set(body.keys()) == {"items", "count"}
