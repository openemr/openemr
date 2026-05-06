"""Shared types for the boolean rubric scorers."""
from __future__ import annotations

from dataclasses import dataclass
from typing import Any, Protocol


@dataclass(frozen=True)
class ScorerResult:
    """Verdict for one (case, scorer) pair."""

    passed: bool
    reason: str
    case_id: str = ""


class Scorer(Protocol):
    """Boolean rubric scorer — pure function, no LLM, deterministic."""

    def __call__(self, case: dict[str, Any], run_output: dict[str, Any]) -> ScorerResult: ...
