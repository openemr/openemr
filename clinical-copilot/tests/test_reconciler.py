"""Unit tests for the deterministic reconciliation pass.

These run on a hand-built FHIR Bundle dict to keep tests fast and offline.
"""

from __future__ import annotations

from sidecar.snapshot.reconciler import reconcile


def test_reconciler_collapses_duplicate_medications_and_flags_disagreement() -> None:
    """Same RxNorm in two sources with different ``active`` flag → flag raised."""
    bundles = {
        "active_problems": None,
        "encounter_diagnoses": None,
        "medications": {
            "entry": [
                {
                    "resource": {
                        "id": "9001",
                        "status": "active",
                        "medicationCodeableConcept": {
                            "text": "Metformin 500 mg",
                            "coding": [
                                {"system": "http://www.nlm.nih.gov/research/umls/rxnorm",
                                 "code": "860975"}
                            ],
                        },
                        "authoredOn": "2014-03-15",
                    },
                },
                {
                    "resource": {
                        "id": "9001-dup",
                        "status": "completed",
                        "medicationCodeableConcept": {
                            "text": "Metformin 500 mg",
                            "coding": [
                                {"system": "http://www.nlm.nih.gov/research/umls/rxnorm",
                                 "code": "860975"}
                            ],
                        },
                        "authoredOn": "2018-01-01",
                    },
                },
            ]
        },
        "allergies": None, "vitals": None, "labs": None,
    }
    snap = reconcile(patient_uuid="Patient/1", fhir_bundles=bundles)
    assert len(snap.medications) == 1
    assert snap.medications[0].sources_in_agreement is False
    flag_codes = {f.code for f in snap.quality_flags}
    assert "med_disagreement" in flag_codes


def test_reconciler_maps_free_text_to_icd10() -> None:
    bundles = {
        "active_problems": {
            "entry": [
                {
                    "resource": {
                        "id": "p1",
                        "code": {"text": "Gout", "coding": []},
                        "clinicalStatus": {"coding": [{"code": "active"}]},
                        "verificationStatus": {"coding": [{"code": "confirmed"}]},
                        "onsetDateTime": "2019-06-04",
                    }
                }
            ]
        },
        "encounter_diagnoses": None, "medications": None,
        "allergies": None, "vitals": None, "labs": None,
    }
    snap = reconcile(patient_uuid="Patient/1", fhir_bundles=bundles)
    assert len(snap.active_problems) == 1
    assert snap.active_problems[0].icd10 == "M10.9"


def test_reconciler_attaches_provenance_to_every_problem() -> None:
    bundles = {
        "active_problems": {
            "entry": [
                {
                    "resource": {
                        "id": "p1",
                        "code": {"text": "Gout", "coding": []},
                        "clinicalStatus": {"coding": [{"code": "active"}]},
                        "verificationStatus": {"coding": [{"code": "confirmed"}]},
                    }
                }
            ]
        },
        "encounter_diagnoses": None, "medications": None,
        "allergies": None, "vitals": None, "labs": None,
    }
    snap = reconcile(patient_uuid="Patient/1", fhir_bundles=bundles)
    p = snap.active_problems[0]
    assert p.provenance.table == "lists"
    assert p.provenance.row_id == "p1"
    assert p.provenance.fhir_resource == "Condition/p1"
