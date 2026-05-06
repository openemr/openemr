"""Unit tests for the two W2 Layer-2 rules added to ``apply_rules``.

- ``check_extracted_fact_has_source_doc`` — DocumentReference per-fact
  citations require the parent doc record to be in this turn's tool_results.
- ``check_evidence_chunk_in_corpus`` — Guideline citations require their
  chunk_id to be in the loaded corpus.
"""
from __future__ import annotations

from app.agent.schemas import AgentResponse, Claim
from app.verification.rules import (
    apply_rules,
    check_evidence_chunk_in_corpus,
    check_extracted_fact_has_source_doc,
)


# ─────────────────────────────────────────────────────────────────────
# check_extracted_fact_has_source_doc
# ─────────────────────────────────────────────────────────────────────


def test_extracted_fact_passes_when_parent_doc_in_tool_results() -> None:
    response = AgentResponse(
        prose="LDL 142 mg/dL.",
        claims=[
            Claim(
                text="LDL 142",
                record_id=(
                    "DocumentReference/doc-123#page=1&bbox=0.10,0.13,0.42,0.025"
                    "&field=results[ldl_cholesterol].value"
                ),
            )
        ],
    )
    tool_results = [
        {
            "tool": "attach_and_extract",
            "data": [
                # Parent doc record — emitted by the tool alongside per-fact records.
                {
                    "record_id": "DocumentReference/doc-123",
                    "resourceType": "DocumentReference",
                },
                # Per-fact record — what the claim cites.
                {
                    "record_id": (
                        "DocumentReference/doc-123#page=1&bbox=0.10,0.13,0.42,0.025"
                        "&field=results[ldl_cholesterol].value"
                    ),
                    "resourceType": "Observation",
                    "value": 142,
                    "unit": "mg/dL",
                },
            ],
        }
    ]
    rejections = check_extracted_fact_has_source_doc(response, tool_results)
    assert rejections == []


def test_extracted_fact_rejects_when_parent_doc_missing() -> None:
    response = AgentResponse(
        prose="LDL 142 mg/dL.",
        claims=[
            Claim(
                text="LDL 142",
                record_id=(
                    "DocumentReference/doc-fabricated#page=1&bbox=0,0,1,1&field=foo"
                ),
            )
        ],
    )
    # Per-fact record_id alone — no parent DocumentReference.
    tool_results = [
        {
            "tool": "attach_and_extract",
            "data": [
                {
                    "record_id": (
                        "DocumentReference/doc-fabricated"
                        "#page=1&bbox=0,0,1,1&field=foo"
                    ),
                    "resourceType": "Observation",
                }
            ],
        }
    ]
    rejections = check_extracted_fact_has_source_doc(response, tool_results)
    assert len(rejections) == 1
    assert "DocumentReference/doc-fabricated" in rejections[0]


def test_extracted_fact_rule_ignores_non_document_record_ids() -> None:
    response = AgentResponse(
        prose="LDL was 142.",
        claims=[
            Claim(text="LDL was 142", record_id="Observation/obs-7"),
            Claim(text="atorvastatin", record_id="MedicationRequest/rx-3"),
        ],
    )
    tool_results = [
        {
            "tool": "get_recent_labs",
            "data": [
                {"record_id": "Observation/obs-7"},
                {"record_id": "MedicationRequest/rx-3"},
            ],
        }
    ]
    assert check_extracted_fact_has_source_doc(response, tool_results) == []


# ─────────────────────────────────────────────────────────────────────
# check_evidence_chunk_in_corpus
# ─────────────────────────────────────────────────────────────────────


def test_evidence_chunk_passes_for_known_chunk_id() -> None:
    """The corpus' real chunk_ids contain '#' (e.g. 'uspstf-statin-2022#sec-2.1');
    the rule must NOT split on '#' — chunk_id is opaque after `Guideline/`.
    """
    response = AgentResponse(
        prose="ASCVD risk thresholds suggest statin.",
        claims=[
            Claim(
                text="statin if ≥20%",
                record_id="Guideline/uspstf-statin-2022#sec-2.1",
            )
        ],
    )
    known = frozenset(
        {"uspstf-statin-2022#sec-2.1", "ada-a1c-screening-2024#sec-2.1"}
    )
    rejections = check_evidence_chunk_in_corpus(response, known)
    assert rejections == []


def test_evidence_chunk_rejects_unknown_chunk_id() -> None:
    response = AgentResponse(
        prose="Some claim.",
        claims=[Claim(text="hallucinated", record_id="Guideline/fake-chunk-99")],
    )
    known = frozenset({"aha-cv-2024-3"})
    rejections = check_evidence_chunk_in_corpus(response, known)
    assert len(rejections) == 1
    assert "fake-chunk-99" in rejections[0]


def test_evidence_chunk_does_not_strip_hash_fragment() -> None:
    """Regression for codex round-2 P1: a chunk_id WITH '#sec-N' must NOT
    be truncated to the part before '#' before checking known_chunk_ids.
    """
    response = AgentResponse(
        prose="chunk_id has a # inside.",
        claims=[
            Claim(
                text="g",
                record_id="Guideline/uspstf-statin-2022#sec-2.1",
            )
        ],
    )
    # Known set has the SHORT form; the full form must NOT match it.
    known_short_only = frozenset({"uspstf-statin-2022"})
    rejections = check_evidence_chunk_in_corpus(response, known_short_only)
    assert len(rejections) == 1, "must reject when only the truncated form is known"

    # Now flip — known has the FULL form, claim should pass.
    known_full = frozenset({"uspstf-statin-2022#sec-2.1"})
    rejections = check_evidence_chunk_in_corpus(response, known_full)
    assert rejections == [], "must accept when the full chunk_id (incl. '#sec-N.M') is known"


def test_evidence_chunk_rule_ignores_non_guideline_record_ids() -> None:
    response = AgentResponse(
        prose="LDL 142.",
        claims=[
            Claim(text="LDL", record_id="Observation/obs-1"),
            Claim(text="lab doc", record_id="DocumentReference/d-1#page=1"),
        ],
    )
    known: frozenset[str] = frozenset()
    assert check_evidence_chunk_in_corpus(response, known) == []


# ─────────────────────────────────────────────────────────────────────
# apply_rules integration — both new rules are wired in
# ─────────────────────────────────────────────────────────────────────


def test_apply_rules_fires_extracted_fact_rule() -> None:
    response = AgentResponse(
        prose="ldl 142.",
        claims=[
            Claim(
                text="ldl 142",
                record_id=(
                    "DocumentReference/doc-X#page=1&bbox=0,0,1,1&field=ldl"
                ),
            )
        ],
    )
    # Per-fact record but no parent — rule should reject.
    tool_results = [
        {
            "tool": "attach_and_extract",
            "data": [
                {
                    "record_id": (
                        "DocumentReference/doc-X#page=1&bbox=0,0,1,1&field=ldl"
                    ),
                    "subject_pseudonym": "patient-pseudo-A",
                    "resourceType": "Observation",
                }
            ],
        }
    ]
    result = apply_rules(
        response,
        tool_results,
        active_patient_pseudonym="patient-pseudo-A",
    )
    assert result.passed is False
    assert any("source document" in r for r in result.rejection_reasons)


def test_apply_rules_fires_chunk_in_corpus_rule_when_set_provided() -> None:
    response = AgentResponse(
        prose="see guideline.",
        claims=[Claim(text="g", record_id="Guideline/unknown-chunk")],
    )
    # No tool result anchoring the claim — but cross-patient also fires
    # because the record_id isn't in any tool_result. Both rejections are
    # acceptable here; we assert the corpus rule fired.
    tool_results: list[dict] = [
        {
            "tool": "search_guidelines",
            "data": [{"record_id": "Guideline/unknown-chunk"}],
        }
    ]
    known = frozenset({"different-chunk"})
    result = apply_rules(
        response,
        tool_results,
        active_patient_pseudonym="patient-pseudo-A",
        known_chunk_ids=known,
    )
    assert result.passed is False
    assert any("chunk_id" in r and "unknown-chunk" in r for r in result.rejection_reasons)


def test_apply_rules_skips_chunk_rule_when_known_set_is_none() -> None:
    """W1 callers don't pass known_chunk_ids; the chunk rule must not fire."""
    response = AgentResponse(
        prose="ok.",
        claims=[Claim(text="ok", record_id="Observation/obs-1")],
    )
    tool_results = [
        {
            "tool": "get_recent_labs",
            "data": [
                {"record_id": "Observation/obs-1", "subject_pseudonym": "p-A"},
            ],
        }
    ]
    result = apply_rules(
        response,
        tool_results,
        active_patient_pseudonym="p-A",
        # known_chunk_ids omitted on purpose — W1 contract.
    )
    assert result.passed is True
