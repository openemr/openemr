"""``no_phi_in_logs`` scorer — TurnTrace contains no PHI fixture substrings.

A case YAML provides ``phi_substrings`` — known PHI strings that were in
the input fixture (patient name, DOB, SSN, etc.). The scorer JSON-dumps
the run's ``TurnTrace`` (and any other observability payloads in
``run_output``) and checks that NO substring appears.

This guards the "no raw PHI in observability" PRD invariant — the
single most important security property of the agent.
"""
from __future__ import annotations

import json
from typing import Any

from evals.scorers._types import ScorerResult


def _serialize(payload: Any) -> str:
    """Best-effort JSON dump of an arbitrary observability payload."""
    if hasattr(payload, "model_dump"):
        try:
            payload = payload.model_dump()
        except Exception:  # noqa: BLE001
            pass
    try:
        return json.dumps(payload, default=str, ensure_ascii=False)
    except (TypeError, ValueError):
        return str(payload)


def score(case: dict[str, Any], run_output: dict[str, Any]) -> ScorerResult:
    case_id = case.get("case_id", "")
    substrings = case.get("phi_substrings") or []
    if not substrings:
        return ScorerResult(
            passed=True,
            reason="no phi_substrings declared (vacuously clean)",
            case_id=case_id,
        )

    # Anything observability-related goes through the no-PHI scan.
    candidates: list[str] = []
    if "trace" in run_output:
        candidates.append(_serialize(run_output["trace"]))
    if "vlm_span" in run_output:
        candidates.append(_serialize(run_output["vlm_span"]))
    if "logs" in run_output:
        candidates.append(_serialize(run_output["logs"]))
    if not candidates:
        # Defensive — if no observability payload, treat as clean (nothing to leak).
        return ScorerResult(
            passed=True,
            reason="no observability payload to scan",
            case_id=case_id,
        )

    blob = "\n".join(candidates)
    leaked = [s for s in substrings if s and s in blob]
    if leaked:
        return ScorerResult(
            passed=False,
            reason=f"PHI leaked in trace: {leaked[:3]}",
            case_id=case_id,
        )
    return ScorerResult(
        passed=True,
        reason=f"none of {len(substrings)} substring(s) found in trace",
        case_id=case_id,
    )
