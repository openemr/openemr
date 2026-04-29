"""Unit tests for the rule store + verifier."""

from __future__ import annotations

from datetime import datetime, timezone

from sidecar.snapshot import (
    Allergy,
    Demographics,
    Medication,
    PatientSnapshot,
    Presenting,
    Problem,
    Provenance,
)
from sidecar.verifier import Verifier, load_default_rule_store


def _provenance(table: str, row_id: str | int = 1) -> Provenance:
    return Provenance(table=table, row_id=row_id)


def test_rule_store_loads_default_yaml() -> None:
    store = load_default_rule_store()
    rule_ids = {r.id for r in store.rules}
    assert "osteo_progression_backward" in rule_ids
    assert "penicillin_allergy_amoxicillin" in rule_ids
    assert "gout_present_toe_pain_anchor" in rule_ids


def test_verifier_warns_when_pen_allergy_meets_amoxicillin() -> None:
    snap = PatientSnapshot(
        patient_id="Patient/1",
        snapshot_version=datetime.now(tz=timezone.utc),
        demographics=Demographics(),
        active_problems=[],
        medications=[
            Medication(id="MR/1", label="Amoxicillin 500 mg", active=True,
                       provenance=_provenance("prescriptions"))
        ],
        allergies=[
            Allergy(id="AI/1", label="Penicillin allergy",
                    provenance=_provenance("lists"))
        ],
        recent_vitals=[], recent_labs=[],
        presenting=Presenting(),
    )
    v = Verifier(load_default_rule_store())
    outcome = v.verify(snap)
    assert outcome.verdict.value == "warned"
    assert any(h.rule.id == "penicillin_allergy_amoxicillin" for h in outcome.rule_hits)


def test_verifier_passes_clean_chart() -> None:
    snap = PatientSnapshot(
        patient_id="Patient/1",
        snapshot_version=datetime.now(tz=timezone.utc),
        demographics=Demographics(age=30),
        active_problems=[
            Problem(id="C/1", label="Asthma", icd10="J45.909",
                    provenance=_provenance("lists"))
        ],
        medications=[
            Medication(id="MR/1", label="Albuterol",
                       provenance=_provenance("prescriptions"))
        ],
        allergies=[], recent_vitals=[], recent_labs=[],
        presenting=Presenting(),
    )
    outcome = Verifier(load_default_rule_store()).verify(snap)
    assert outcome.verdict.value == "passed"
    assert outcome.rule_hits == []
