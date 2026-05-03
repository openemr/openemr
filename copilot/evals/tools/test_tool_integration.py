"""End-to-end tool tests using respx-mocked FHIR.

Proves the 5-step pattern (resolve → ACL → fetch → strip → return record_ids)
works for the four UC1 tools without requiring a live OpenEMR.
"""
from __future__ import annotations

import pytest
import respx
from httpx import Response

from app.config import get_settings
from app.fhir.client import FhirClient
from app.phi.session import sessions
from app.tools.registry import dispatch


PATIENT_ID = "f47ac10b-58cc-4372-a567-0e02b2c3d479"


def _patient_resource():
    return {
        "resourceType": "Patient",
        "id": PATIENT_ID,
        "active": True,
        "gender": "male",
        "birthDate": "1962-03-15",
        "name": [{"given": ["John", "Q"], "family": "Public"}],
    }


def _condition_bundle():
    return {
        "resourceType": "Bundle",
        "type": "searchset",
        "entry": [
            {
                "resource": {
                    "resourceType": "Condition",
                    "id": "cond-1",
                    "clinicalStatus": {"coding": [{"code": "active"}]},
                    "code": {
                        "coding": [
                            {
                                "system": "http://hl7.org/fhir/sid/icd-10-cm",
                                "code": "E11.9",
                                "display": "Type 2 diabetes mellitus",
                            }
                        ]
                    },
                    "subject": {"reference": f"Patient/{PATIENT_ID}"},
                }
            }
        ],
    }


def _med_bundle():
    return {
        "resourceType": "Bundle",
        "type": "searchset",
        "entry": [
            {
                "resource": {
                    "resourceType": "MedicationRequest",
                    "id": "rx-1",
                    "status": "active",
                    "medicationCodeableConcept": {
                        "coding": [
                            {
                                "system": "http://www.nlm.nih.gov/research/umls/rxnorm",
                                "code": "860975",
                                "display": "metformin 500 MG",
                            }
                        ],
                        "text": "metformin 500 MG tablet",
                    },
                    "authoredOn": "2024-03-15",
                    "subject": {"reference": f"Patient/{PATIENT_ID}"},
                    "dosageInstruction": [{"text": "1 tablet PO BID"}],
                }
            }
        ],
    }


@pytest.fixture
def session():
    sessions.end("integration-session")
    return sessions.create("integration-session", "demo-doc", PATIENT_ID)


@pytest.fixture
def fhir():
    return FhirClient(get_settings())


@respx.mock
async def test_get_patient_summary_returns_record_ids(session, fhir):
    base = get_settings().openemr_fhir_base
    respx.post(get_settings().openemr_oauth_base + "/token").mock(
        return_value=Response(200, json={"access_token": "test", "expires_in": 300})
    )
    respx.get(f"{base}/Patient/{PATIENT_ID}").mock(
        return_value=Response(200, json=_patient_resource())
    )
    respx.get(f"{base}/Condition").mock(
        return_value=Response(200, json=_condition_bundle())
    )

    result = await dispatch("get_patient_summary", {}, fhir, session)

    assert result.error is None
    assert result.acl_check.allowed
    # Patient + 1 Condition
    assert len(result.data) == 2
    assert f"Patient/{PATIENT_ID}" in result.record_ids
    assert "Condition/cond-1" in result.record_ids
    # PHI is gone
    import json as _json
    blob = _json.dumps(result.data)
    assert "John" not in blob and "Public" not in blob and "1962" not in blob


@respx.mock
async def test_get_active_medications_records_rxnorm(session, fhir):
    base = get_settings().openemr_fhir_base
    respx.post(get_settings().openemr_oauth_base + "/token").mock(
        return_value=Response(200, json={"access_token": "test", "expires_in": 300})
    )
    respx.get(f"{base}/Patient/{PATIENT_ID}").mock(
        return_value=Response(200, json=_patient_resource())
    )
    respx.get(f"{base}/MedicationRequest").mock(
        return_value=Response(200, json=_med_bundle())
    )

    result = await dispatch("get_active_medications", {}, fhir, session)

    assert result.error is None
    assert "MedicationRequest/rx-1" in result.record_ids
    assert result.data[0]["rxnorm_code"] == "860975"


async def test_acl_denies_unknown_role(session, fhir, monkeypatch):
    # A user not in the demo physician set
    session.physician_user_id = ""
    result = await dispatch("get_patient_summary", {}, fhir, session)
    assert result.acl_check.allowed is False
    assert result.error and result.error.startswith("acl_denied")
    assert result.record_ids == []


@respx.mock
async def test_tool_returns_acl_denied_on_fhir_401(session, fhir):
    """If OpenEMR rejects the patient read with 401, the runtime ACL probe
    must convert that into `acl_denied` — not a crash, not a partial
    result, and not a leak of error details into the tool data.
    """
    base = get_settings().openemr_fhir_base
    respx.post(get_settings().openemr_oauth_base + "/token").mock(
        return_value=Response(200, json={"access_token": "test", "expires_in": 300})
    )
    respx.get(f"{base}/Patient/{PATIENT_ID}").mock(
        return_value=Response(401, json={"error": "unauthorized"})
    )

    result = await dispatch("get_patient_summary", {}, fhir, session)
    assert result.acl_check.allowed is False
    assert result.error and result.error.startswith("acl_denied")
    assert "openemr_denied_patient_read:401" in (result.acl_check.reason or "")
    assert result.data == []
    assert result.record_ids == []
