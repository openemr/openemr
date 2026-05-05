"""FHIR write helpers — MVP stub behavior (no OpenEMR HTTP calls)."""
from __future__ import annotations

import pytest
import respx

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
async def test_create_document_reference_stub(settings: Settings) -> None:
    """Stub returns a copilot- prefixed id, no HTTP call made."""
    client = FhirClient(settings)
    try:
        result = await client.create_document_reference(
            patient_fhir_id="patient-7",
            doc_type="lab_doc",
            mime_type="application/pdf",
            file_bytes=b"%PDF-1.4\nfake-bytes",
            sha3_hex="abc123def456789012345678",
            physician_user_id="dr_who",
        )
    finally:
        await client.aclose()

    assert result["id"] == "copilot-abc123def4567890"
    assert result["resourceType"] == "DocumentReference"
    assert result["status"] == "current"
    assert result["subject"]["reference"] == "Patient/patient-7"
    # No HTTP requests should have been made
    assert len(respx.calls) == 0


@respx.mock
async def test_create_observation_stub(settings: Settings) -> None:
    """Stub returns a copilot-obs- prefixed id, no HTTP call made."""
    client = FhirClient(settings)
    body = {"resourceType": "Observation", "status": "final"}
    try:
        result = await client.create_observation(body=body, physician_user_id="dr_who")
    finally:
        await client.aclose()

    assert result["id"].startswith("copilot-obs-")
    assert result["resourceType"] == "Observation"
    assert len(respx.calls) == 0


@respx.mock
async def test_create_allergy_intolerance_stub(settings: Settings) -> None:
    """Stub returns a copilot-allergy- prefixed id, no HTTP call made."""
    client = FhirClient(settings)
    body = {"resourceType": "AllergyIntolerance", "clinicalStatus": {}}
    try:
        result = await client.create_allergy_intolerance(body=body, physician_user_id="dr_who")
    finally:
        await client.aclose()

    assert result["id"].startswith("copilot-allergy-")
    assert result["resourceType"] == "AllergyIntolerance"
    assert len(respx.calls) == 0


@respx.mock
async def test_create_medication_statement_stub(settings: Settings) -> None:
    """Stub returns a copilot-med- prefixed id, no HTTP call made."""
    client = FhirClient(settings)
    body = {"resourceType": "MedicationStatement", "status": "active"}
    try:
        result = await client.create_medication_statement(body=body, physician_user_id="dr_who")
    finally:
        await client.aclose()

    assert result["id"].startswith("copilot-med-")
    assert result["resourceType"] == "MedicationStatement"
    assert len(respx.calls) == 0


@respx.mock
async def test_stub_ids_are_stable(settings: Settings) -> None:
    """Same input bytes → same synthesized doc_id across calls."""
    client = FhirClient(settings)
    sha = "aabbccddeeff00112233445566778899"
    try:
        r1 = await client.create_document_reference(
            patient_fhir_id="p-1", doc_type="lab_doc", mime_type="application/pdf",
            file_bytes=b"bytes", sha3_hex=sha, physician_user_id="dr_who",
        )
        r2 = await client.create_document_reference(
            patient_fhir_id="p-1", doc_type="lab_doc", mime_type="application/pdf",
            file_bytes=b"bytes", sha3_hex=sha, physician_user_id="dr_who",
        )
    finally:
        await client.aclose()

    assert r1["id"] == r2["id"]
    assert r1["id"].startswith("copilot-")
    assert len(respx.calls) == 0
