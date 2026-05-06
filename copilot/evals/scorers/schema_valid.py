"""``schema_valid`` scorer — Pydantic validation passes on the extracted JSON.

A case YAML provides:
  - ``expected_schema_class``: ``"LabPDFExtraction"`` or ``"IntakeFormExtraction"``
  - The runner produces a ``run_output`` containing the extraction (or its
    JSON-serializable dump) under ``run_output["extraction"]``.

Pass condition: ``ExtractionClass.model_validate(extraction)`` succeeds.
"""
from __future__ import annotations

from typing import Any

from pydantic import ValidationError

from app.ingestion.schemas import IntakeFormExtraction, LabPDFExtraction
from evals.scorers._types import ScorerResult

_CLASS_MAP = {
    "LabPDFExtraction": LabPDFExtraction,
    "IntakeFormExtraction": IntakeFormExtraction,
}


def score(case: dict[str, Any], run_output: dict[str, Any]) -> ScorerResult:
    case_id = case.get("case_id", "")
    schema_name = case.get("expected_schema_class")
    if not schema_name:
        return ScorerResult(
            passed=False,
            reason="case missing expected_schema_class",
            case_id=case_id,
        )
    cls = _CLASS_MAP.get(schema_name)
    if cls is None:
        return ScorerResult(
            passed=False,
            reason=f"unknown schema class {schema_name!r}",
            case_id=case_id,
        )

    extraction = run_output.get("extraction")
    if extraction is None:
        return ScorerResult(
            passed=False,
            reason="run_output missing 'extraction' field",
            case_id=case_id,
        )

    try:
        cls.model_validate(extraction)
    except ValidationError as e:
        return ScorerResult(
            passed=False,
            reason=f"validation failed: {e.error_count()} errors",
            case_id=case_id,
        )
    return ScorerResult(passed=True, reason="schema valid", case_id=case_id)
