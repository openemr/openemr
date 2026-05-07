"""``rules_block_regression`` scorer — Layer-2 gate must reject this fixture.

This scorer is the W2 hard-gate canary: it pairs with a fixture that is
intentionally engineered to trigger ``apply_rules`` (e.g. a fragment-only
``DocumentReference`` citation whose parent doc is absent from
tool_results, which ``check_extracted_fact_has_source_doc`` blocks). The
scorer asserts that ``apply_rules`` returns ``passed=False`` for the
fixture.

  - With the Layer-2 rule active: ``apply_rules`` rejects → result.passed
    is False → scorer PASSES (the gate did its job).
  - With the rule disabled (someone's regression): ``apply_rules`` returns
    passed=True → scorer FAILS → category drops → runner exits 1.

This is what makes the README's "comment out a Layer-2 rule, run
``make eval-fast``, see exit 1" recipe actually work end-to-end. The
rest of the eval suite is fixture-pass-through and would not catch a
silent rule disable.

The fixture-level ``active_patient_pseudonym`` (default ``p-A``) is
threaded into ``apply_rules`` so cross-patient checks don't accidentally
short-circuit the assertion.
"""
from __future__ import annotations

from typing import Any

from app.agent.schemas import AgentResponse, Claim
from app.verification.rules import apply_rules
from evals.scorers._types import ScorerResult


def score(case: dict[str, Any], run_output: dict[str, Any]) -> ScorerResult:
    case_id = case.get("case_id", "")
    response_dict = run_output.get("response")
    tool_results = run_output.get("tool_results") or []
    if not response_dict:
        return ScorerResult(
            passed=False, reason="run_output missing 'response'", case_id=case_id,
        )

    if isinstance(response_dict, AgentResponse):
        response = response_dict
    else:
        response = AgentResponse(
            prose=response_dict.get("prose", ""),
            claims=[Claim(**c) for c in response_dict.get("claims", []) or []],
            data_gaps=list(response_dict.get("data_gaps", []) or []),
        )

    pseudo = (
        case.get("active_patient_pseudonym")
        or run_output.get("active_patient_pseudonym")
        or "p-A"
    )

    result = apply_rules(
        response,
        tool_results,
        active_patient_pseudonym=pseudo,
    )

    if result.passed:
        return ScorerResult(
            passed=False,
            reason=(
                "Layer-2 gate did NOT reject this regression-canary fixture "
                "— apply_rules returned passed=True. Either a rule was "
                "disabled or the canary fixture has been weakened."
            ),
            case_id=case_id,
        )

    return ScorerResult(
        passed=True,
        reason=(
            f"Layer-2 gate rejected as expected ({len(result.rejection_reasons)} "
            f"reason(s))."
        ),
        case_id=case_id,
    )
