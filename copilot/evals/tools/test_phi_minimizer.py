"""PHI minimizer unit tests — proves identifiers are stripped before LLM ingest.

Maps to AUDIT.md §1.4 (no PHI to LLM) and ARCHITECTURE.md §3.3.
"""
from __future__ import annotations

import json

from app.phi.minimizer import (
    collect_name_terms_from_patient,
    strip_allergy,
    strip_condition,
    strip_encounter,
    strip_medication_request,
    strip_observation,
    strip_patient,
)
from app.phi.session import sessions


PATIENT_RAW = {
    "resourceType": "Patient",
    "id": "abc-123",
    "active": True,
    "gender": "male",
    "birthDate": "1962-03-15",
    "name": [{"given": ["John", "Q"], "family": "Public", "text": "John Q Public"}],
    "telecom": [{"system": "phone", "value": "555-867-5309"}],
    "address": [{"line": ["742 Evergreen Tce"], "city": "Springfield"}],
    "identifier": [
        {"system": "http://hl7.org/fhir/sid/us-ssn", "value": "123-45-6789"},
        {"system": "urn:oid:1.2.3.4.5.6.7", "value": "MRN-555"},
    ],
}


def _new_session():
    return sessions.create("test-session", "demo-doc", PATIENT_RAW["id"])


def test_strip_patient_drops_identifiers():
    s = _new_session()
    out = strip_patient(PATIENT_RAW, s)
    blob = json.dumps(out)
    # No name fragments
    assert "John" not in blob and "Public" not in blob
    # No phone, address, SSN, MRN
    assert "5309" not in blob and "Evergreen" not in blob
    assert "123-45-6789" not in blob and "MRN-555" not in blob
    # Age preserved (not exact DOB)
    assert out["age"].endswith("yo")
    assert "1962" not in blob
    # Pseudonym present
    assert out["id"].startswith("Patient-")
    # Internal record_id retained for verification anchor
    assert out["record_id"] == "Patient/abc-123"


def test_strip_observation_scrubs_provider_name_in_value_string():
    s = _new_session()
    name_terms = collect_name_terms_from_patient(PATIENT_RAW)
    obs = {
        "resourceType": "Observation",
        "id": "obs-1",
        "status": "final",
        "category": [{"coding": [{"code": "laboratory"}]}],
        "code": {"coding": [{"system": "http://loinc.org", "code": "4548-4", "display": "HbA1c"}]},
        "valueQuantity": {"value": 7.4, "unit": "%"},
        "referenceRange": [{"low": {"value": 4.0, "unit": "%"}, "high": {"value": 5.6, "unit": "%"}}],
        "subject": {"reference": "Patient/abc-123"},
        "effectiveDateTime": "2026-04-12",
    }
    out = strip_observation(obs, s, name_terms)
    assert out["loinc_code"] == "4548-4"
    assert out["value"] == 7.4
    assert out["reference_range"]["high"] == 5.6
    assert out["record_id"] == "Observation/obs-1"
    # Subject reference is pseudonymized
    assert out["subject_pseudonym"].startswith("Patient-")


def test_strip_condition_keeps_icd10():
    s = _new_session()
    name_terms = collect_name_terms_from_patient(PATIENT_RAW)
    cond = {
        "resourceType": "Condition",
        "id": "cond-7",
        "clinicalStatus": {"coding": [{"code": "active"}]},
        "code": {
            "coding": [
                {"system": "http://hl7.org/fhir/sid/icd-10-cm", "code": "E11.9", "display": "Type 2 diabetes mellitus"}
            ]
        },
        "subject": {"reference": "Patient/abc-123"},
        "onsetDateTime": "2024-03-15",
    }
    out = strip_condition(cond, s, name_terms)
    assert out["icd10_code"] == "E11.9"
    assert out["record_id"] == "Condition/cond-7"


def test_strip_medication_request_keeps_rxnorm():
    s = _new_session()
    name_terms = collect_name_terms_from_patient(PATIENT_RAW)
    med = {
        "resourceType": "MedicationRequest",
        "id": "rx-22",
        "status": "active",
        "intent": "order",
        "medicationCodeableConcept": {
            "coding": [
                {"system": "http://www.nlm.nih.gov/research/umls/rxnorm", "code": "860975", "display": "metformin 500 MG"}
            ],
            "text": "metformin 500 MG tablet",
        },
        "authoredOn": "2024-03-15",
        "subject": {"reference": "Patient/abc-123"},
        "requester": {"reference": "Practitioner/prac-9"},
        "dosageInstruction": [{"text": "Take 1 tablet by mouth twice daily."}],
    }
    out = strip_medication_request(med, s, name_terms)
    assert out["rxnorm_code"] == "860975"
    assert out["record_id"] == "MedicationRequest/rx-22"
    assert out["requester"].startswith("Provider-")


def test_strip_allergy_preserves_severity():
    s = _new_session()
    name_terms = collect_name_terms_from_patient(PATIENT_RAW)
    allergy = {
        "resourceType": "AllergyIntolerance",
        "id": "all-3",
        "clinicalStatus": {"coding": [{"code": "active"}]},
        "category": ["medication"],
        "criticality": "high",
        "code": {"coding": [{"code": "7980", "display": "Penicillin"}]},
        "reaction": [
            {
                "manifestation": [{"coding": [{"display": "anaphylaxis"}]}],
                "severity": "severe",
            }
        ],
    }
    out = strip_allergy(allergy, s, name_terms)
    assert out["criticality"] == "high"
    assert out["reactions"][0]["severity"] == "severe"
    assert out["record_id"] == "AllergyIntolerance/all-3"


def test_strip_encounter_pseudonymizes_provider():
    s = _new_session()
    name_terms = collect_name_terms_from_patient(PATIENT_RAW)
    enc = {
        "resourceType": "Encounter",
        "id": "enc-44",
        "status": "finished",
        "class": {"code": "AMB"},
        "type": [{"coding": [{"display": "office visit"}]}],
        "period": {"start": "2026-04-15T09:00:00Z", "end": "2026-04-15T09:15:00Z"},
        "subject": {"reference": "Patient/abc-123"},
        "participant": [{"individual": {"reference": "Practitioner/prac-9"}}],
        "reasonCode": [{"text": "annual physical"}],
    }
    out = strip_encounter(enc, s, name_terms)
    assert out["record_id"] == "Encounter/enc-44"
    assert out["participant_pseudonyms"][0].startswith("Provider-")
    assert out["start"] == "2026-04-15T09:00:00Z"


def test_pseudonym_stable_within_session():
    s = _new_session()
    a = s.pseudo_for("Practitioner", "prac-1")
    b = s.pseudo_for("Practitioner", "prac-1")
    c = s.pseudo_for("Practitioner", "prac-2")
    assert a == b
    assert a != c
    assert s.resolve(a) == "Practitioner/prac-1"
