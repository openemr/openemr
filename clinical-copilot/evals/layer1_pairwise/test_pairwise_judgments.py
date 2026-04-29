"""Layer 1 — unit-level pairwise judgments.

A growing corpus of ``(symptom, finding, expected_likelihood, expected_evidence_row)``
and ``(finding_a, finding_b, expected_inconsistency, expected_rule)`` tuples.
ARCHITECTURE.md §7.1: target 95% accuracy; below 90% blocks deploy.
"""

from __future__ import annotations

import pytest

from sidecar.agent.pair_generator import PairA, PairB
from sidecar.agent.pair_judge import MockProvider
from sidecar.snapshot import Provenance


_FAKE_PROV_A = Provenance(table="lists", row_id=3018, fhir_resource="Condition/3018")
_FAKE_PROV_B = Provenance(table="lists", row_id=2241, fhir_resource="Condition/2241")
_FAKE_PROV_OSTEO_PORO = Provenance(table="lists", row_id=4001)
_FAKE_PROV_OSTEO_PEN = Provenance(table="lists", row_id=4002)
_FAKE_PROV_PEN_AL = Provenance(table="lists", row_id="al01")
_FAKE_PROV_AMOX = Provenance(table="prescriptions", row_id=9201)


# ─── Pairwise A gold cases ─────────────────────────────────────────────────


PAIRWISE_A_CASES = [
    # The gout cross-check from USERS.md §3.1
    ("right toe pain", "Gout, unspecified", "high"),
    ("body aches", "Gout, unspecified", "moderate"),
    ("swelling", "Gout, unspecified", "high"),
    # Negative controls — diabetes shouldn't explain acute toe pain.
    ("right toe pain", "Type 2 diabetes mellitus", "low"),
    # Hypertension shouldn't explain body aches.
    ("body aches", "Essential hypertension", "low"),
]


@pytest.mark.asyncio
@pytest.mark.parametrize("symptom,candidate,expected", PAIRWISE_A_CASES)
async def test_pair_judge_a_likelihood(symptom: str, candidate: str, expected: str) -> None:
    provider = MockProvider()
    pair = PairA(
        symptom=symptom,
        candidate_label=candidate,
        candidate_kind="problem",
        candidate_provenance=_FAKE_PROV_A if "gout" in candidate.lower() else _FAKE_PROV_B,
    )
    judgment = await provider.judge_a(pair)
    assert judgment.result is not None, "MockProvider should never fail"
    assert judgment.result.likelihood == expected


# ─── Pairwise B gold cases ─────────────────────────────────────────────────


PAIRWISE_B_CASES = [
    # Osteoporosis recorded before osteopenia is biologically backward.
    (
        ("Osteoporosis", _FAKE_PROV_OSTEO_PORO),
        ("Osteopenia", _FAKE_PROV_OSTEO_PEN),
        "temporal",
    ),
    # Penicillin allergy with subsequent amoxicillin without re-exposure note.
    (
        ("Penicillin allergy", _FAKE_PROV_PEN_AL),
        ("Amoxicillin 500 mg", _FAKE_PROV_AMOX),
        "pharmacological",
    ),
]


@pytest.mark.asyncio
@pytest.mark.parametrize("a,b,expected", PAIRWISE_B_CASES)
async def test_pair_judge_b_inconsistency(
    a: tuple[str, Provenance], b: tuple[str, Provenance], expected: str
) -> None:
    provider = MockProvider()
    pair = PairB(
        label_a=a[0], provenance_a=a[1], kind_a="problem",
        label_b=b[0], provenance_b=b[1], kind_b="problem",
    )
    judgment = await provider.judge_b(pair)
    assert judgment.result is not None
    assert judgment.result.inconsistency == expected
    # Confidence must be above 0.5 for a real inconsistency.
    if expected != "none":
        assert judgment.result.confidence >= 0.5
        # Evidence must cite at least one row id.
        assert judgment.result.evidence
