"""GET /v1/documents/{doc_id}/preview and /extractions."""
from __future__ import annotations

import json
from datetime import datetime, timezone
from unittest.mock import AsyncMock

import pytest
from fastapi.testclient import TestClient

from app import main as main_module
from app.persistence.processed_documents import ProcessedDocument


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
    # Panel: dr_who owns patient-7; dr_evil owns nobody relevant here.
    monkeypatch.setenv(
        "PHYSICIAN_PATIENT_PANEL",
        json.dumps({"dr_who": ["patient-7"]}),
    )

    from app.config import get_settings
    get_settings.cache_clear()

    return TestClient(main_module.app)


def test_preview_returns_pdf_bytes(client: TestClient) -> None:
    with client:
        # Preview now reads from the local store, not FhirClient
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            return_value=ProcessedDocument(
                patient_pseudonym="patient-7",
                hash="abc",
                canonical_doc_id="doc-1",
                doc_type="lab_doc",
                extracted_facts={"results": [], "document_date": None},
                source_path="attach_route",
                extracted_at=datetime.now(timezone.utc),
                file_bytes=b"%PDF-1.4 fake",
                mime_type="application/pdf",
            )
        )
        r = client.get(
            "/v1/documents/doc-1/preview",
            params={"physician_user_id": "dr_who", "patient_id": "patient-7"},
        )
    assert r.status_code == 200
    assert r.headers["content-type"].startswith("application/pdf")
    assert r.content == b"%PDF-1.4 fake"


def test_preview_rejects_out_of_panel_patient(client: TestClient, monkeypatch) -> None:
    """Preview forwards a panel-gate deny to the caller as 403.

    Stubs ``_verify_patient_in_panel`` to a 403 — pins the endpoint
    contract; the gate's own semantics (env-panel advisory + FHIR
    fallback) are exercised in ``evals/agent/test_panel_scope.py``.
    """
    from app import main as main_module
    from fastapi import HTTPException

    async def _deny(*args, **kwargs):
        raise HTTPException(status_code=403, detail="patient_out_of_panel")
    monkeypatch.setattr(main_module, "_verify_patient_in_panel", _deny)

    with client:
        r = client.get(
            "/v1/documents/doc-99/preview",
            params={"physician_user_id": "dr_who", "patient_id": "patient-99"},
        )
    assert r.status_code == 403
    assert "out_of_panel" in r.json()["detail"]


def test_preview_returns_404_when_not_in_store_and_fhir_404s(
    client: TestClient,
) -> None:
    """Preview falls back to FHIR DocumentReference when the doc isn't in the
    local store; if the FHIR fetch also 404s, the endpoint returns 404.
    """
    from app.fhir.client import FhirError

    with client:
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            return_value=None
        )
        client.app.state.fhir_client.get_resource = AsyncMock(
            side_effect=FhirError("not found", status=404)
        )
        r = client.get(
            "/v1/documents/doc-missing/preview",
            params={"physician_user_id": "dr_who", "patient_id": "patient-7"},
        )
    assert r.status_code == 404


def test_preview_falls_back_to_fhir_inline_attachment(client: TestClient) -> None:
    """W2 KR5 round-5 fix: when a doc isn't in the local store (front-desk
    upload via OpenEMR's stock Documents Zend module), the endpoint
    fetches the FHIR DocumentReference and returns its inline attachment.
    """
    import base64 as _b64

    payload = b"%PDF-1.4 from-fhir-inline"

    with client:
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            return_value=None
        )
        client.app.state.fhir_client.get_resource = AsyncMock(
            return_value={
                "resourceType": "DocumentReference",
                "id": "doc-front-desk",
                "subject": {"reference": "Patient/patient-7"},
                "content": [
                    {
                        "attachment": {
                            "contentType": "application/pdf",
                            "data": _b64.b64encode(payload).decode(),
                        }
                    }
                ],
            }
        )
        r = client.get(
            "/v1/documents/doc-front-desk/preview",
            params={"physician_user_id": "dr_who", "patient_id": "patient-7"},
        )
    assert r.status_code == 200
    assert r.headers["content-type"].startswith("application/pdf")
    assert r.content == payload


def test_preview_blocks_cross_patient_doc_when_subject_mismatch(
    client: TestClient,
) -> None:
    """W2 KR5 round-6 fix (codex P1, security): the panel gate only checks
    the QUERY-PARAM patient_id. Without subject validation, a caller in-
    panel for patient-7 could pass a doc_id whose DocumentReference
    actually belongs to patient-99 and the endpoint would stream patient-99's
    bytes. After the fix: 403, no bytes leaked.
    """
    import base64 as _b64

    payload = b"%PDF-1.4 patient-99-secret"

    with client:
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            return_value=None
        )
        client.app.state.fhir_client.get_resource = AsyncMock(
            return_value={
                "resourceType": "DocumentReference",
                "id": "doc-from-other-patient",
                # Subject is patient-99 — caller is in-panel for patient-7.
                "subject": {"reference": "Patient/patient-99"},
                "content": [
                    {
                        "attachment": {
                            "contentType": "application/pdf",
                            "data": _b64.b64encode(payload).decode(),
                        }
                    }
                ],
            }
        )
        r = client.get(
            "/v1/documents/doc-from-other-patient/preview",
            params={"physician_user_id": "dr_who", "patient_id": "patient-7"},
        )
    assert r.status_code == 403
    assert "subject" in r.json()["detail"]
    assert payload not in r.content  # belt-and-suspenders


def test_preview_accepts_absolute_subject_reference(client: TestClient) -> None:
    """W2 KR5 round-8 fix (codex P2): FHIR allows absolute references like
    `https://host/.../Patient/{id}`. The previous exact-match comparison
    rejected valid same-patient docs from servers that serialize absolute
    references. Now we compare the trailing `Patient/{id}` segment.
    """
    import base64 as _b64

    payload = b"%PDF-1.4 absolute-ref-ok"

    with client:
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            return_value=None
        )
        client.app.state.fhir_client.get_resource = AsyncMock(
            return_value={
                "resourceType": "DocumentReference",
                "id": "doc-with-abs-ref",
                # ABSOLUTE form — FHIR-compliant, breaks the pre-fix exact match.
                "subject": {
                    "reference": "https://emr.example.com/apis/default/fhir/Patient/patient-7"
                },
                "content": [
                    {
                        "attachment": {
                            "contentType": "application/pdf",
                            "data": _b64.b64encode(payload).decode(),
                        }
                    }
                ],
            }
        )
        r = client.get(
            "/v1/documents/doc-with-abs-ref/preview",
            params={"physician_user_id": "dr_who", "patient_id": "patient-7"},
        )
    assert r.status_code == 200
    assert r.content == payload


def test_preview_falls_back_to_fhir_binary_url(client: TestClient) -> None:
    """If the FHIR DocumentReference points to a separate Binary resource
    (the OpenEMR-default shape), the endpoint chases that reference.
    """
    import base64 as _b64

    payload = b"\x89PNG\r\n\x1a\nfake-png"

    async def _fake_get_resource(resource_type, resource_id, *, physician_user_id):
        if resource_type == "DocumentReference":
            return {
                "resourceType": "DocumentReference",
                "id": resource_id,
                "subject": {"reference": "Patient/patient-7"},
                "content": [
                    {
                        "attachment": {
                            "contentType": "image/png",
                            "url": "Binary/bin-9",
                        }
                    }
                ],
            }
        if resource_type == "Binary":
            return {
                "resourceType": "Binary",
                "id": resource_id,
                "contentType": "image/png",
                "data": _b64.b64encode(payload).decode(),
            }
        raise AssertionError(f"unexpected resource_type={resource_type}")

    with client:
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            return_value=None
        )
        client.app.state.fhir_client.get_resource = _fake_get_resource
        r = client.get(
            "/v1/documents/doc-front-desk-png/preview",
            params={"physician_user_id": "dr_who", "patient_id": "patient-7"},
        )
    assert r.status_code == 200
    assert r.headers["content-type"].startswith("image/png")
    assert r.content == payload


def test_extractions_returns_cached_extraction(client: TestClient) -> None:
    with client:
        client.app.state.fhir_client.get_resource = AsyncMock(return_value={})
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            return_value=ProcessedDocument(
                patient_pseudonym="patient-7",
                hash="abc",
                canonical_doc_id="doc-1",
                doc_type="lab_doc",
                extracted_facts={"results": [], "document_date": None},
                source_path="attach_route",
                extracted_at=datetime.now(timezone.utc),
            )
        )
        r = client.get(
            "/v1/documents/doc-1/extractions",
            params={"patient_id": "patient-7", "physician_user_id": "dr_who"},
        )
    assert r.status_code == 200
    body = r.json()
    assert body["doc_id"] == "doc-1"
    assert body["doc_type"] == "lab_doc"


def test_extractions_rejects_out_of_panel_patient(
    client: TestClient, monkeypatch
) -> None:
    """Extractions forwards a panel-gate deny to the caller as 403."""
    from app import main as main_module
    from fastapi import HTTPException

    async def _deny(*args, **kwargs):
        raise HTTPException(status_code=403, detail="patient_out_of_panel")
    monkeypatch.setattr(main_module, "_verify_patient_in_panel", _deny)

    with client:
        r = client.get(
            "/v1/documents/doc-99/extractions",
            params={"patient_id": "patient-99", "physician_user_id": "dr_who"},
        )
    assert r.status_code == 403
    assert "out_of_panel" in r.json()["detail"]
