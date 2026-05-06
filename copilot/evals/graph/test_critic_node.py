"""Verify the critic graph node — Layer-1 attribution + Layer-2 domain rules.

The critic re-runs ``verify`` and ``apply_rules`` over the answer_composer's
output as an inspectable graph node. In the production W1 path the response
is already sanitized, so the critic is a no-op; these tests construct
intentionally-bad states to verify the critic's logic in isolation.
"""
from __future__ import annotations

from dataclasses import dataclass

import pytest

from app.agent.schemas import AgentResponse, Claim
from app.graph.critic import critique


@dataclass
class _FakeSession:
    _pseudo: str = "patient-pseudo-A"

    def patient_pseudonym(self) -> str:
        return self._pseudo


@pytest.mark.asyncio
async def test_critic_passes_clean_response() -> None:
    response = AgentResponse(
        prose="LDL 142 mg/dL.",
        claims=[
            Claim(
                text="LDL 142 mg/dL",
                record_id="Observation/obs-1",
                display="LDL 142 mg/dL",
            )
        ],
    )
    tool_results = [
        {
            "tool": "get_recent_labs",
            "data": [
                {
                    "record_id": "Observation/obs-1",
                    "subject_pseudonym": "patient-pseudo-A",
                    "loinc": "13457-7",
                    "value": 142,
                    "unit": "mg/dL",
                }
            ],
        }
    ]
    state = {
        "session": _FakeSession(),
        "response": response,
        "tool_results": tool_results,
        "routing_path": ["answer_composer"],
        "rejections": [],
    }
    delta = await critique(state)  # type: ignore[arg-type]

    assert delta["routing_path"] == ["answer_composer", "critic"]
    assert delta["rejections"] == []
    # No response replacement — clean path leaves it untouched.
    assert "response" not in delta


@pytest.mark.asyncio
async def test_critic_flags_unanchored_claim_in_rejections() -> None:
    response = AgentResponse(
        prose="LDL 142 mg/dL.",
        claims=[
            Claim(
                text="LDL 142 mg/dL",
                record_id="Observation/obs-fabricated",
            )
        ],
    )
    tool_results: list[dict] = []  # no tool results — every claim is unanchored
    state = {
        "session": _FakeSession(),
        "response": response,
        "tool_results": tool_results,
        "routing_path": ["answer_composer"],
        "rejections": [],
    }
    delta = await critique(state)  # type: ignore[arg-type]

    assert "Observation/obs-fabricated" in delta["rejections"]
    # Layer-2 also fires (cross_patient_leakage triggers because the record_id
    # is not in any tool_results) — assert refusal response replaced.
    assert "response" in delta
    assert delta["response"].claims == []  # refusal has no claims


@pytest.mark.asyncio
async def test_critic_flags_layer2_allergy_contraindication() -> None:
    response = AgentResponse(
        prose="It is safe to prescribe aspirin for this patient.",
        claims=[
            Claim(
                text="aspirin verdict",
                record_id="MedicationRequest/rx-1",
            )
        ],
    )
    tool_results = [
        {
            "tool": "get_active_medications",
            "record_type": "MedicationRequest",
            "data": [
                {
                    "record_id": "MedicationRequest/rx-1",
                    "subject_pseudonym": "patient-pseudo-A",
                    "rxnorm": "1191",
                    "display": "aspirin",
                }
            ],
        },
        # Layer-2's check_allergy_contraindication requires:
        #   - record_type == "AllergyIntolerance"
        #   - clinical_status == "active"
        #   - display matches proposed_drug (substring either direction)
        {
            "tool": "get_allergies",
            "record_type": "AllergyIntolerance",
            "data": [
                {
                    "record_id": "AllergyIntolerance/al-1",
                    "subject_pseudonym": "patient-pseudo-A",
                    "display": "aspirin",
                    "category": "medication",
                    "clinical_status": "active",
                }
            ],
        },
    ]
    state = {
        "session": _FakeSession(),
        "response": response,
        "tool_results": tool_results,
        "routing_path": ["answer_composer"],
        "rejections": [],
        "proposed_drug": "aspirin",
    }
    delta = await critique(state)  # type: ignore[arg-type]

    assert any("Allergy" in r or "allergy" in r for r in delta["rejections"])
    assert "response" in delta
    assert delta["response"].claims == []  # refusal


@pytest.mark.asyncio
async def test_critic_handles_missing_response_defensively() -> None:
    state = {
        "session": _FakeSession(),
        "response": None,
        "tool_results": [],
        "routing_path": ["answer_composer"],
        "rejections": [],
    }
    delta = await critique(state)  # type: ignore[arg-type]

    assert delta["routing_path"] == ["answer_composer", "critic"]
    assert delta["rejections"] == []
    assert "response" not in delta
