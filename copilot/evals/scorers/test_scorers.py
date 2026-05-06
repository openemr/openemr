"""Unit tests for the five boolean rubric scorers."""
from __future__ import annotations

from evals.scorers import (
    citation_present,
    factually_consistent,
    no_phi_in_logs,
    safe_refusal,
    schema_valid,
)


# ─────────────────────────────────────────────────────────────────────
# schema_valid
# ─────────────────────────────────────────────────────────────────────


def test_schema_valid_passes_for_valid_lab_extraction() -> None:
    case = {
        "case_id": "lab-1",
        "expected_schema_class": "LabPDFExtraction",
    }
    run_output = {
        "extraction": {
            "results": [
                {
                    "test_name": "LDL Cholesterol",
                    "analyte_key": "ldl_cholesterol",
                    "loinc_code": None,
                    "value": 142.0,
                    "unit": "mg/dL",
                    "reference_range": "<100",
                    "collection_date": "2026-04-30",
                    "abnormal_flag": "H",
                    "source_citation": {
                        "source_doc_id": "DocumentReference/d1",
                        "page": 1,
                        "bbox": {"x": 0.1, "y": 0.1, "w": 0.5, "h": 0.05},
                        "raw_text": "LDL 142",
                        "confidence": 0.95,
                        "source_kind": "document",
                        "field_or_chunk_id": "results[ldl_cholesterol].value",
                    },
                }
            ],
            "document_date": "2026-04-30",
        },
    }
    res = schema_valid(case, run_output)
    assert res.passed is True


def test_schema_valid_fails_for_missing_required_field() -> None:
    case = {
        "case_id": "lab-2",
        "expected_schema_class": "LabPDFExtraction",
    }
    run_output = {"extraction": {"doc_type": "lab_pdf"}}  # missing results
    res = schema_valid(case, run_output)
    assert res.passed is False
    assert "validation failed" in res.reason


# ─────────────────────────────────────────────────────────────────────
# citation_present
# ─────────────────────────────────────────────────────────────────────


def test_citation_present_passes_when_all_anchored() -> None:
    case = {"case_id": "cit-1"}
    run_output = {
        "response": {
            "prose": "LDL was 142.",
            "claims": [{"text": "LDL 142", "record_id": "Observation/o1"}],
            "data_gaps": [],
        },
        "tool_results": [
            {
                "tool": "get_recent_labs",
                "data": [{"record_id": "Observation/o1"}],
            }
        ],
    }
    res = citation_present(case, run_output)
    assert res.passed is True


def test_citation_present_fails_when_unanchored() -> None:
    case = {"case_id": "cit-2"}
    run_output = {
        "response": {
            "prose": "LDL was 142.",
            "claims": [{"text": "LDL 142", "record_id": "Observation/fake"}],
            "data_gaps": [],
        },
        "tool_results": [],
    }
    res = citation_present(case, run_output)
    assert res.passed is False
    assert "unanchored" in res.reason


# ─────────────────────────────────────────────────────────────────────
# factually_consistent
# ─────────────────────────────────────────────────────────────────────


def test_factually_consistent_passes_within_5_percent() -> None:
    case = {
        "case_id": "fact-1",
        "expected_values": {"results[0].value": 142},
    }
    run_output = {"extraction": {"results": [{"value": 144}]}}  # +1.4%
    res = factually_consistent(case, run_output)
    assert res.passed is True


def test_factually_consistent_fails_beyond_5_percent() -> None:
    case = {
        "case_id": "fact-2",
        "expected_values": {"results[0].value": 142},
    }
    run_output = {"extraction": {"results": [{"value": 160}]}}  # +12.7%
    res = factually_consistent(case, run_output)
    assert res.passed is False


# ─────────────────────────────────────────────────────────────────────
# safe_refusal
# ─────────────────────────────────────────────────────────────────────


def test_safe_refusal_passes_with_empty_claims_and_gaps() -> None:
    case = {"case_id": "ref-1"}
    run_output = {
        "response": {
            "prose": "I cannot answer.",
            "claims": [],
            "data_gaps": ["No prior visits on file."],
        },
    }
    res = safe_refusal(case, run_output)
    assert res.passed is True


def test_safe_refusal_fails_when_claims_emitted() -> None:
    case = {"case_id": "ref-2"}
    run_output = {
        "response": {
            "prose": "Patient has X.",
            "claims": [{"text": "X", "record_id": "Observation/o1"}],
            "data_gaps": ["something"],
        },
    }
    res = safe_refusal(case, run_output)
    assert res.passed is False


# ─────────────────────────────────────────────────────────────────────
# no_phi_in_logs
# ─────────────────────────────────────────────────────────────────────


def test_no_phi_in_logs_passes_when_trace_clean() -> None:
    case = {
        "case_id": "phi-1",
        "phi_substrings": ["Margaret", "Chen", "1967-08-14"],
    }
    run_output = {
        "trace": {"session_id": "s1", "user_id": "dr_a", "question_text": "ldl?"},
    }
    res = no_phi_in_logs(case, run_output)
    assert res.passed is True


def test_no_phi_in_logs_fails_when_trace_leaks_phi() -> None:
    case = {
        "case_id": "phi-2",
        "phi_substrings": ["Margaret", "Chen"],
    }
    run_output = {
        "trace": {"session_id": "s1", "question_text": "What did Margaret report?"},
    }
    res = no_phi_in_logs(case, run_output)
    assert res.passed is False
    assert "Margaret" in res.reason
