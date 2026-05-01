"""Layer 1 — unit-level pairwise judgments.

Range-based assertions on the new ``likelihood_pct`` schema. Each case
specifies a (low, high) bound for the expected probability so that
calibration can drift slightly without invalidating the eval.

ARCHITECTURE.md §7.1: target 95% within range; below 90% blocks deploy.
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


# (symptom, candidate, kind, expected_pct_lo, expected_pct_hi)
PAIRWISE_A_CASES = [
    ("right toe pain", "Gout, unspecified", "diagnosis", 70, 95),
    ("body aches",     "Gout, unspecified", "diagnosis", 30, 75),
    ("swollen toe",    "Gout, unspecified", "diagnosis", 75, 95),
    ("right toe pain", "Type 2 diabetes mellitus", "diagnosis", 0, 20),
    ("body aches",     "Essential hypertension",   "diagnosis", 0, 20),
]


@pytest.mark.asyncio
@pytest.mark.parametrize("symptom,candidate,kind,lo,hi", PAIRWISE_A_CASES)
async def test_pair_judge_a_likelihood(
    symptom: str, candidate: str, kind: str, lo: int, hi: int,
) -> None:
    provider = MockProvider()
    pair = PairA(
        symptom=symptom,
        since="3 days",
        candidate_label=candidate,
        candidate_kind=kind,
        candidate_provenance=_FAKE_PROV_A if "gout" in candidate.lower() else _FAKE_PROV_B,
        candidate_obj=type("Stub", (), {"label": candidate})(),
    )
    judgment = await provider.judge_a(pair)
    assert judgment.result is not None
    assert lo <= judgment.result.likelihood_pct <= hi, (
        f"{symptom!r} × {candidate!r}: pct={judgment.result.likelihood_pct} not in [{lo},{hi}]"
    )


# (label_a, prov_a, label_b, prov_b, expected_kind, expected_pct_lo, expected_pct_hi)
PAIRWISE_B_CASES = [
    ("Osteoporosis", _FAKE_PROV_OSTEO_PORO,
     "Osteopenia",   _FAKE_PROV_OSTEO_PEN,
     "temporal", 80, 100),
    ("Penicillin allergy", _FAKE_PROV_PEN_AL,
     "Amoxicillin 500 mg", _FAKE_PROV_AMOX,
     "pharmacological", 80, 100),
]


@pytest.mark.asyncio
@pytest.mark.parametrize("la,pa,lb,pb,expected_kind,lo,hi", PAIRWISE_B_CASES)
async def test_pair_judge_b_inconsistency(
    la: str, pa: Provenance, lb: str, pb: Provenance,
    expected_kind: str, lo: int, hi: int,
) -> None:
    provider = MockProvider()
    pair = PairB(
        label_a=la, provenance_a=pa, kind_a="diagnosis",
        obj_a=type("Stub", (), {"label": la})(),
        label_b=lb, provenance_b=pb, kind_b="diagnosis",
        obj_b=type("Stub", (), {"label": lb})(),
    )
    judgment = await provider.judge_b(pair)
    assert judgment.result is not None
    assert judgment.result.kind == expected_kind
    assert lo <= judgment.result.inconsistency_pct <= hi
    assert judgment.result.evidence
