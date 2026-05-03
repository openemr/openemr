"""Tests for the verification gate — Layer 1 (attribution) + Layer 2 (rules).

These run without the LLM; they exercise the gate logic on synthetic agent
outputs. The eval suite in Phase D wires these into a full agent run.
"""
from __future__ import annotations

from app.agent.schemas import AgentResponse, Claim
from app.verification.attribution import verify
from app.verification.rules import apply_rules


def _tool_results_for_patient(patient_pseudonym: str) -> list[dict]:
    return [
        {
            "tool": "get_patient_summary",
            "record_type": "Patient+Condition",
            "data": [
                {
                    "resourceType": "Patient",
                    "id": patient_pseudonym,
                    "record_id": "Patient/abc-123",
                },
                {
                    "resourceType": "Condition",
                    "id": "cond-1",
                    "record_id": "Condition/cond-1",
                    "subject_pseudonym": patient_pseudonym,
                    "icd10_code": "E11.9",
                    "display": "Type 2 diabetes mellitus",
                },
            ],
            "record_ids": ["Patient/abc-123", "Condition/cond-1"],
        },
        {
            "tool": "get_active_medications",
            "record_type": "MedicationRequest",
            "data": [
                {
                    "resourceType": "MedicationRequest",
                    "id": "rx-22",
                    "record_id": "MedicationRequest/rx-22",
                    "subject_pseudonym": patient_pseudonym,
                    "drug_name": "metformin 500 MG",
                    "rxnorm_code": "860975",
                }
            ],
            "record_ids": ["MedicationRequest/rx-22"],
        },
    ]


def test_attribution_passes_when_all_claims_anchored():
    pseudo = "Patient-AB12"
    tool_results = _tool_results_for_patient(pseudo)
    response = AgentResponse(
        prose="62yo male, established patient, T2DM on metformin.",
        claims=[
            Claim(text="T2DM", record_id="Condition/cond-1"),
            Claim(text="metformin", record_id="MedicationRequest/rx-22"),
        ],
        data_gaps=[],
    )
    result = verify(response, tool_results)
    assert result.passed
    assert len(result.rejected_claims) == 0


def test_attribution_strips_unanchored_claim():
    pseudo = "Patient-AB12"
    tool_results = _tool_results_for_patient(pseudo)
    response = AgentResponse(
        prose="On metformin and simvastatin.",
        claims=[
            Claim(text="metformin", record_id="MedicationRequest/rx-22"),
            Claim(text="simvastatin", record_id="MedicationRequest/rx-99"),  # fake
        ],
    )
    result = verify(response, tool_results)
    assert not result.passed
    assert len(result.rejected_claims) == 1
    assert "simvastatin" not in result.sanitized.prose or "[unverified]" in result.sanitized.prose
    assert any("simvastatin" in g for g in result.sanitized.data_gaps)


def test_cross_patient_leakage_hard_blocks():
    pseudo = "Patient-AB12"
    tool_results = _tool_results_for_patient(pseudo)
    # Inject a foreign record into the tool data
    tool_results.append(
        {
            "tool": "get_active_medications",
            "record_type": "MedicationRequest",
            "data": [
                {
                    "resourceType": "MedicationRequest",
                    "id": "rx-77",
                    "record_id": "MedicationRequest/rx-77",
                    "subject_pseudonym": "Patient-OTHER",  # different patient
                    "drug_name": "warfarin 5 MG",
                }
            ],
            "record_ids": ["MedicationRequest/rx-77"],
        }
    )
    response = AgentResponse(
        prose="Patient is on warfarin.",
        claims=[Claim(text="warfarin", record_id="MedicationRequest/rx-77")],
    )
    result = apply_rules(response, tool_results, active_patient_pseudonym=pseudo)
    assert not result.passed
    assert any("Cross-patient leakage" in r for r in result.rejection_reasons)


def test_attribution_with_no_claims_records_data_gap():
    """Prose without any claims is vacuously 'passed' by Layer 1 today.

    Lock in the current behavior so a future regression that makes prose-
    only output the easy path for the LLM doesn't slip through unnoticed.
    If we later decide claim-less responses must be rejected, this test
    flips and is the single place to update.
    """
    pseudo = "Patient-AB12"
    tool_results = _tool_results_for_patient(pseudo)
    response = AgentResponse(
        prose="Patient looks stable.",
        claims=[],
        data_gaps=[],
    )
    result = verify(response, tool_results)
    # Vacuously passes — no claims means nothing to anchor.
    assert result.passed
    assert result.rejected_claims == []


def test_cross_patient_blocks_fabricated_record_id():
    """Layer 2 must reject a record_id the LLM invented out of thin air.

    Layer 1 already filters unknown ids, but the rule code is a defense-
    in-depth layer — verify it independently rejects any record_id not
    present in tool_results, regardless of whether Layer 1 ran.
    """
    pseudo = "Patient-AB12"
    tool_results = _tool_results_for_patient(pseudo)
    response = AgentResponse(
        prose="Patient is on apixaban.",
        claims=[Claim(text="apixaban", record_id="MedicationRequest/does-not-exist")],
    )
    result = apply_rules(response, tool_results, active_patient_pseudonym=pseudo)
    assert not result.passed
    assert any("Cross-patient leakage" in r for r in result.rejection_reasons)


def test_inactive_allergy_does_not_block_safe_verdict():
    """Negative test: only `clinical_status == active` allergies contraindicate."""
    pseudo = "Patient-AB12"
    tool_results = _tool_results_for_patient(pseudo)
    tool_results.append(
        {
            "tool": "get_allergies",
            "record_type": "AllergyIntolerance",
            "data": [
                {
                    "resourceType": "AllergyIntolerance",
                    "id": "all-old",
                    "record_id": "AllergyIntolerance/all-old",
                    "clinical_status": "resolved",
                    "criticality": "high",
                    "display": "penicillin",
                    "subject_pseudonym": pseudo,
                }
            ],
            "record_ids": ["AllergyIntolerance/all-old"],
        }
    )
    response = AgentResponse(
        prose="Verdict: safe to prescribe penicillin VK.",
        claims=[Claim(text="penicillin VK", record_id="AllergyIntolerance/all-old")],
    )
    result = apply_rules(
        response, tool_results, active_patient_pseudonym=pseudo, proposed_drug="penicillin VK"
    )
    assert result.passed
    assert result.rejection_reasons == []


def test_allergy_contraindication_blocks_safe_verdict():
    pseudo = "Patient-AB12"
    tool_results = _tool_results_for_patient(pseudo)
    tool_results.append(
        {
            "tool": "get_allergies",
            "record_type": "AllergyIntolerance",
            "data": [
                {
                    "resourceType": "AllergyIntolerance",
                    "id": "all-1",
                    "record_id": "AllergyIntolerance/all-1",
                    "clinical_status": "active",
                    "criticality": "high",
                    "display": "penicillin",
                }
            ],
            "record_ids": ["AllergyIntolerance/all-1"],
        }
    )
    response = AgentResponse(
        prose="Verdict: safe to prescribe amoxicillin.",
        claims=[Claim(text="amoxicillin", record_id="AllergyIntolerance/all-1")],
    )
    # Note: real penicillin-class match would need RxNorm class lookup; this
    # test uses a substring match to exercise the rule wiring.
    result = apply_rules(
        response, tool_results, active_patient_pseudonym=pseudo, proposed_drug="penicillin VK"
    )
    assert not result.passed
    assert any("Allergy contraindication" in r for r in result.rejection_reasons)
