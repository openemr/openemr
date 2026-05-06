"""``factually_consistent`` scorer — extracted values match gold within ±5%.

A case YAML provides ``expected_values`` as a flat dict keyed by JSON-path
strings (or top-level field names). The runner extracts the actual values
from ``run_output["extraction"]`` along the same paths.

Comparison rules:
  - Numeric (int/float): ``|actual - expected| / |expected| <= 0.05``
    (or exact equality if expected == 0).
  - String: case-insensitive equality after trimming whitespace.
  - None / NaN actual → fail.

The path syntax is dotted with bracketed list indices, e.g.
``results[0].value`` or ``demographics.age``.
"""
from __future__ import annotations

import math
import re
from typing import Any

from evals.scorers._types import ScorerResult


_INDEX_RE = re.compile(r"^([^\[]+)(?:\[(\d+)\])?$")


def _walk(obj: Any, path: str) -> Any:
    """Resolve a dotted/indexed path against a nested dict/list structure."""
    cur: Any = obj
    for part in path.split("."):
        m = _INDEX_RE.match(part)
        if not m:
            return None
        key = m.group(1)
        idx = m.group(2)
        if isinstance(cur, dict):
            cur = cur.get(key)
        else:
            return None
        if idx is not None:
            try:
                cur = cur[int(idx)]
            except (TypeError, IndexError, KeyError):
                return None
    return cur


def _is_close_enough(actual: Any, expected: Any) -> bool:
    if isinstance(expected, (int, float)) and not isinstance(expected, bool):
        if not isinstance(actual, (int, float)) or isinstance(actual, bool):
            return False
        if isinstance(actual, float) and math.isnan(actual):
            return False
        if expected == 0:
            return actual == 0
        return abs(actual - expected) / abs(expected) <= 0.05
    if isinstance(expected, str):
        if not isinstance(actual, str):
            return False
        return actual.strip().lower() == expected.strip().lower()
    return actual == expected


def score(case: dict[str, Any], run_output: dict[str, Any]) -> ScorerResult:
    case_id = case.get("case_id", "")
    expected = case.get("expected_values") or {}
    extraction = run_output.get("extraction")
    if extraction is None:
        return ScorerResult(
            passed=False,
            reason="run_output missing 'extraction'",
            case_id=case_id,
        )
    if not expected:
        return ScorerResult(
            passed=True,
            reason="no expected_values declared (vacuously consistent)",
            case_id=case_id,
        )

    mismatches: list[str] = []
    for path, want in expected.items():
        got = _walk(extraction, path)
        if not _is_close_enough(got, want):
            mismatches.append(f"{path}: got {got!r}, expected {want!r}")

    if mismatches:
        return ScorerResult(
            passed=False,
            reason="; ".join(mismatches[:3]),
            case_id=case_id,
        )
    return ScorerResult(
        passed=True,
        reason=f"all {len(expected)} fields within ±5%",
        case_id=case_id,
    )
