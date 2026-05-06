"""Integration tests for /v1/sessions/{id}/pending_intakes (W2 KR5 lite).

Round-4 codex fix: endpoint now reads FHIR DocumentReference, not the
local processed_documents SQLite. Tests mock fhir.search accordingly.
"""
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
        yield c, main_module


def _start(client: TestClient) -> str:
    r = client.post(
        "/v1/sessions",
        json={"patient_id": PATIENT_ID, "physician_user_id": PHYSICIAN},
    )
    assert r.status_code == 200, r.text
    return r.json()["session_id"]


def _patch_fhir_search(main_module, monkeypatch, bundle: dict) -> None:
    captured: dict = {}

    async def _fake_search(resource_type, params, *, physician_user_id):
        assert resource_type == "DocumentReference"
        assert params.get("patient") == PATIENT_ID
        captured["params"] = params
        return bundle

    monkeypatch.setattr(main_module.app.state.fhir, "search", _fake_search)
    return captured


def test_pending_intakes_returns_empty_when_fhir_returns_empty_bundle(
    app_client, monkeypatch
) -> None:
    client, main_module = app_client
    _patch_fhir_search(main_module, monkeypatch, {"entry": []})
    sid = _start(client)

    r = client.get(f"/v1/sessions/{sid}/pending_intakes")
    assert r.status_code == 200
    body = r.json()
    assert body["count"] == 0
    assert body["items"] == []


def test_pending_intakes_returns_404_for_unknown_session(app_client) -> None:
    client, _ = app_client
    r = client.get("/v1/sessions/no-such-session/pending_intakes")
    assert r.status_code == 404


def test_pending_intakes_surfaces_fhir_documentreferences(
    app_client, monkeypatch
) -> None:
    """The endpoint reads OpenEMR's FHIR DocumentReference for the patient
    so front-desk uploads done via the stock Documents Zend module surface
    in the iframe banner.
    """
    client, main_module = app_client
    bundle = {
        "resourceType": "Bundle",
        "type": "searchset",
        "entry": [
            {
                "resource": {
                    "resourceType": "DocumentReference",
                    "id": "doc-front-desk-1",
                    "type": {
                        "coding": [
                            {
                                "system": "http://loinc.org",
                                "code": "11502-2",
                                "display": "Laboratory report",
                            }
                        ],
                        "text": "Lab Report",
                    },
                    "date": "2026-05-06T08:30:00Z",
                    "content": [
                        {
                            "attachment": {
                                "contentType": "application/pdf",
                                "url": "/Binary/doc-front-desk-1",
                            }
                        }
                    ],
                }
            },
            {
                "resource": {
                    "resourceType": "DocumentReference",
                    "id": "doc-front-desk-2",
                    "type": {"text": "Intake Form"},
                    "date": "2026-05-05T14:15:00Z",
                    "content": [
                        {"attachment": {"contentType": "image/png"}}
                    ],
                }
            },
        ],
    }
    _patch_fhir_search(main_module, monkeypatch, bundle)
    sid = _start(client)

    r = client.get(f"/v1/sessions/{sid}/pending_intakes")
    assert r.status_code == 200
    body = r.json()
    assert body["count"] == 2
    items_by_id = {i["doc_id"]: i for i in body["items"]}
    assert items_by_id["doc-front-desk-1"]["doc_type"] == "Laboratory report"
    assert items_by_id["doc-front-desk-1"]["mime_type"] == "application/pdf"
    assert items_by_id["doc-front-desk-2"]["doc_type"] == "Intake Form"
    assert items_by_id["doc-front-desk-2"]["mime_type"] == "image/png"


def test_pending_intakes_handles_fhir_error_gracefully(
    app_client, monkeypatch
) -> None:
    """Transient FHIR errors must NOT 500 the iframe load — banner stays empty."""
    client, main_module = app_client
    from app.fhir.client import FhirError

    async def _failing_search(*args, **kwargs):
        raise FhirError("upstream timeout")

    monkeypatch.setattr(main_module.app.state.fhir, "search", _failing_search)
    sid = _start(client)

    r = client.get(f"/v1/sessions/{sid}/pending_intakes")
    assert r.status_code == 200
    assert r.json() == {"items": [], "count": 0}


def test_pending_intakes_response_shape_is_stable(app_client, monkeypatch) -> None:
    """The response must always have items + count keys, even when empty."""
    client, main_module = app_client
    _patch_fhir_search(main_module, monkeypatch, {})
    sid = _start(client)
    r = client.get(f"/v1/sessions/{sid}/pending_intakes")
    body = r.json()
    assert set(body.keys()) == {"items", "count"}


def test_pending_intakes_constrains_to_recent_uploads(
    app_client, monkeypatch
) -> None:
    """W2 KR5 round-7 fix (codex P2): without a date filter the endpoint
    returns every DocumentReference on the chart, which is wrong for the
    "uploaded by front desk — review" banner copy. The lite implementation
    asks FHIR for `date=geYYYY-MM-DD` (last 7 days) so the banner stays
    focused on recent activity.
    """
    client, main_module = app_client
    captured = _patch_fhir_search(main_module, monkeypatch, {"entry": []})
    sid = _start(client)

    r = client.get(f"/v1/sessions/{sid}/pending_intakes")
    assert r.status_code == 200

    date_param = captured["params"].get("date")
    assert isinstance(date_param, str)
    assert date_param.startswith("ge"), "must be a `geYYYY-MM-DD` lower bound"
    # The cutoff is computed at request time; sanity check the iso-date suffix.
    assert len(date_param) == len("geYYYY-MM-DD")
