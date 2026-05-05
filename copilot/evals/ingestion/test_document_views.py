"""GET /v1/documents/{doc_id}/preview and /extractions."""
from __future__ import annotations

import base64
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
        # Mock the FhirClient.get_resource for DocumentReference fetch
        client.app.state.fhir_client.get_resource = AsyncMock(
            return_value={
                "resourceType": "DocumentReference",
                "id": "doc-1",
                "subject": {"reference": "Patient/patient-7"},
                "content": [
                    {
                        "attachment": {
                            "contentType": "application/pdf",
                            "data": base64.b64encode(b"%PDF-1.4 fake").decode("ascii"),
                        }
                    }
                ],
            }
        )
        r = client.get(
            "/v1/documents/doc-1/preview",
            params={"physician_user_id": "dr_who"},
        )
    assert r.status_code == 200
    assert r.headers["content-type"].startswith("application/pdf")
    assert r.content == b"%PDF-1.4 fake"


def test_preview_rejects_out_of_panel_patient(client: TestClient) -> None:
    """Preview must return 403 when the doc's patient is outside the physician's panel."""
    with client:
        client.app.state.fhir_client.get_resource = AsyncMock(
            return_value={
                "resourceType": "DocumentReference",
                "id": "doc-99",
                "subject": {"reference": "Patient/patient-99"},  # not in dr_who's panel
                "content": [
                    {
                        "attachment": {
                            "contentType": "application/pdf",
                            "data": base64.b64encode(b"%PDF-1.4 fake").decode("ascii"),
                        }
                    }
                ],
            }
        )
        r = client.get(
            "/v1/documents/doc-99/preview",
            params={"physician_user_id": "dr_who"},
        )
    assert r.status_code == 403
    assert "out_of_panel" in r.json()["detail"]


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


def test_extractions_rejects_out_of_panel_patient(client: TestClient) -> None:
    """Extractions must return 403 when the patient is outside the physician's panel."""
    with client:
        client.app.state.fhir_client.get_resource = AsyncMock(return_value={})
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            return_value=ProcessedDocument(
                patient_pseudonym="patient-99",
                hash="abc",
                canonical_doc_id="doc-99",
                doc_type="lab_doc",
                extracted_facts={"results": [], "document_date": None},
                source_path="attach_route",
                extracted_at=datetime.now(timezone.utc),
            )
        )
        r = client.get(
            "/v1/documents/doc-99/extractions",
            params={"patient_id": "patient-99", "physician_user_id": "dr_who"},
        )
    assert r.status_code == 403
    assert "out_of_panel" in r.json()["detail"]
