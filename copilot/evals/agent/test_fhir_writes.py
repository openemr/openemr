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
    # OPENEMR_FHIR_BASE deliberately omitted — exercises the model_validator
    # that derives openemr_fhir_base and openemr_oauth_base from OPENEMR_BASE_URL.
    monkeypatch.setenv("OAUTH_CLIENT_ID", "test-client")
    monkeypatch.setenv("OAUTH_CLIENT_SECRET", "test-secret")
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


@pytest.mark.parametrize(
    "method_name,resource_type",
    [
        ("create_observation", "Observation"),
        ("create_allergy_intolerance", "AllergyIntolerance"),
        ("create_medication_statement", "MedicationStatement"),
    ],
)
@respx.mock
async def test_thin_post_wrappers(
    settings: Settings, method_name: str, resource_type: str
) -> None:
    respx.post("https://emr.example.com/oauth2/default/token").mock(
        return_value=Response(200, json={"access_token": "tok", "expires_in": 3600})
    )
    expected = {"id": "res-1", "resourceType": resource_type}
    respx.post(
        f"https://emr.example.com/apis/default/fhir/{resource_type}"
    ).mock(return_value=Response(201, json=expected))

    client = FhirClient(settings)
    try:
        result = await getattr(client, method_name)(
            body={"resourceType": resource_type},
            physician_user_id="dr_test",
        )
    finally:
        await client.aclose()

    last_req = respx.calls.last.request
    assert str(last_req.url).endswith(f"/{resource_type}")
    assert result == expected
