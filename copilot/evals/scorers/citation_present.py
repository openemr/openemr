"""``citation_present`` scorer — every claim's record_id is anchored.

Wraps the W1 ``verify`` from ``app.verification.attribution``: the union of
record_ids across the turn's tool_results must include every claim's
record_id.

The case YAML may also list ``expected_record_ids`` — if provided, every
expected id must appear in the response's claims (covers the "agent
elided a required citation" case).
"""
from __future__ import annotations

from typing import Any

from app.agent.schemas import AgentResponse, Claim
from app.verification.attribution import verify
from evals.scorers._types import ScorerResult


def score(case: dict[str, Any], run_output: dict[str, Any]) -> ScorerResult:
    case_id = case.get("case_id", "")
    response_dict = run_output.get("response")
    tool_results = run_output.get("tool_results") or []

    if not response_dict:
        return ScorerResult(
            passed=False, reason="run_output missing 'response'", case_id=case_id,
        )

    # Accept either a Pydantic AgentResponse or a dict.
    if isinstance(response_dict, AgentResponse):
        response = response_dict
    else:
        response = AgentResponse(
            prose=response_dict.get("prose", ""),
            claims=[Claim(**c) for c in response_dict.get("claims", []) or []],
            data_gaps=list(response_dict.get("data_gaps", []) or []),
        )

    attr = verify(response, tool_results)
    if not attr.passed:
        return ScorerResult(
            passed=False,
            reason=f"unanchored: {','.join(attr.unknown_ids)}",
            case_id=case_id,
        )

    expected_ids = case.get("expected_record_ids")
    if expected_ids:
        actual = {c.record_id for c in response.claims}
        missing = [rid for rid in expected_ids if rid not in actual]
        if missing:
            return ScorerResult(
                passed=False,
                reason=f"missing expected record_ids: {missing}",
                case_id=case_id,
            )

    return ScorerResult(passed=True, reason="all claims anchored", case_id=case_id)
