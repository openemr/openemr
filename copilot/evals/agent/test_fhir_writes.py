"""FHIR write helpers — POST shapes match OpenEMR's R4 endpoints."""
from __future__ import annotations

import base64

import pytest
import respx
from httpx import Response

from app.config import Settings
from app.fhir.client import FhirClient


@pytest.fixture
def settings(monkeypatch: pytest.MonkeyPatch) -> Settings:
    monkeypatch.setenv("OPENEMR_BASE_URL", "https://emr.example.com")
    monkeypatch.setenv("OPENEMR_FHIR_BASE", "https://emr.example.com/apis/default/fhir")
    monkeypatch.setenv("OPENEMR_CLIENT_ID", "test-client")
    monkeypatch.setenv("OPENEMR_CLIENT_SECRET", "test-secret")
    monkeypatch.setenv("OPENEMR_VERIFY_TLS", "false")
    return Settings()


@respx.mock
async def test_create_document_reference_posts_binary(settings: Settings) -> None:
    respx.post("https://emr.example.com/oauth2/default/token").mock(
        return_value=Response(200, json={"access_token": "tok", "expires_in": 3600})
    )
    captured: dict = {}

    def _capture(request):
        captured["body"] = request.read()
        captured["url"] = str(request.url)
        return Response(
            201,
            json={"id": "doc-123", "resourceType": "DocumentReference"},
            headers={"Location": "DocumentReference/doc-123"},
        )

    respx.post("https://emr.example.com/apis/default/fhir/DocumentReference").mock(
        side_effect=_capture
    )

    client = FhirClient(settings)
    try:
        result = await client.create_document_reference(
            patient_fhir_id="patient-7",
            doc_type="lab_doc",
            mime_type="application/pdf",
            file_bytes=b"%PDF-1.4\nfake-bytes",
            sha3_hex="abc123",
            physician_user_id="dr_who",
        )
    finally:
        await client.aclose()

    assert result["id"] == "doc-123"
    body = captured["body"].decode()
    assert "DocumentReference" in body
    assert "Patient/patient-7" in body
    assert base64.b64encode(b"%PDF-1.4\nfake-bytes").decode() in body
    assert "urn:copilot:sha3-512" in body
    assert "abc123" in body
