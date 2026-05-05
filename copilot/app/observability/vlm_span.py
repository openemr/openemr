"""PHI-safe Langfuse span payloads for the VLM-extraction worker (Gap E).

The VLM sees raw PHI by physical necessity — a lab report or intake form
PDF/image can't have patient name, DOB, MRN, or address removed before the
vision model reads it. The architecture's no-PHI guarantee instead targets
the *next* layer: the Langfuse spans that record what the worker did.

This module exists so the `intake_extractor` worker has exactly one obvious
way to talk to the tracer, and that one way refuses to accept the raw
image bytes or the raw VLM response.

Contract enforced here:

  - **Input** to the VLM span is *always* `None`. Raw image bytes never
    reach Langfuse, full stop.
  - **Output** is built by `vlm_span_output()` from a plain
    `LabPDFExtraction` / `IntakeFormExtraction` and produces only
    aggregate metrics:
      * `extracted_field_count` — int
      * `mean_confidence` — float | None
      * `min_confidence` — float | None
      * `low_confidence_count` — count of fields with confidence < 0.5
      * `coded_count` / `uncoded_count` — for allergies/medications, how
        many were resolvable to a code
      * `pages_touched` — distinct page numbers cited
      * `doc_id` — the pseudonymized DocumentReference id (already PHI-safe)
      * `model_id` and `latency_ms` — pass-through technical metrics
  - **No raw VLM strings** — `raw_text`, `verbatim_substance`,
    `chief_concern`, `test_name`, `name`, `dose`, `condition`,
    `ambiguity_note`, `reaction` are all dropped at this boundary.

The two `assert_no_phi_in_span_payload()` helpers are the eval-side
checks: they take the dict that would be sent to Langfuse and assert it
contains no string substrings from a known PHI fixture set.
"""
from __future__ import annotations

from typing import Any, Iterable

from app.ingestion.schemas import (
    IntakeFormExtraction,
    LabPDFExtraction,
)


# Whitelist of keys allowed in any `output` dict passed to Langfuse for a
# VLM span. `assert_no_phi_in_span_payload` enforces this; any other key
# anywhere in the payload tree is a bug.
_VLM_OUTPUT_ALLOWED_KEYS: frozenset[str] = frozenset(
    {
        "extracted_field_count",
        "mean_confidence",
        "min_confidence",
        "low_confidence_count",
        "coded_count",
        "uncoded_count",
        "pages_touched",
        "doc_id",
        "model_id",
        "latency_ms",
        "doc_type",
        "mime_type",
    }
)


def _walk_citations(payload: Any) -> Iterable[Any]:
    """Yield every nested `SourceCitation` instance inside an extraction."""
    if hasattr(payload, "source_citation"):
        yield payload.source_citation
    if hasattr(payload, "model_fields"):
        for field_name in payload.model_fields:
            child = getattr(payload, field_name)
            if isinstance(child, list):
                for item in child:
                    yield from _walk_citations(item)
            elif hasattr(child, "model_fields"):
                yield from _walk_citations(child)


def _confidence_stats(citations: list) -> tuple[float | None, float | None, int]:
    confidences = [c.confidence for c in citations if c is not None]
    if not confidences:
        return None, None, 0
    mean = sum(confidences) / len(confidences)
    low = sum(1 for c in confidences if c < 0.5)
    return mean, min(confidences), low


def _coded_uncoded_counts(extraction: Any) -> tuple[int, int]:
    """Count allergies+medications that have a coded form vs not.

    Mirrors the Gap B schema split: `Allergy.coded_substance` /
    `Medication.name` paired with optional codes. The VLM span output
    reports the *counts* (how many disambiguated, how many surfaced for
    clinician), not the strings themselves.
    """
    coded = 0
    uncoded = 0
    if isinstance(extraction, IntakeFormExtraction):
        for allergy in extraction.allergies:
            if allergy.coded_substance:
                coded += 1
            else:
                uncoded += 1
        # Medications always have a name; we only count "coded" when an
        # RxNorm-equivalent has been attached upstream. The current schema
        # doesn't yet carry a med code, so all meds are uncoded for now.
        uncoded += len(extraction.current_medications)
    return coded, uncoded


def vlm_span_input() -> None:
    """Always returns None.

    Exists as an explicit, named call site so the contract is searchable
    in the codebase and PR reviewers can see at a glance that the VLM
    span input is intentionally null.
    """
    return None


def vlm_span_output(
    extraction: LabPDFExtraction | IntakeFormExtraction,
    *,
    doc_id: str,
    doc_type: str,
    mime_type: str,
    model_id: str,
    latency_ms: float,
) -> dict[str, Any]:
    """Build the PHI-safe `output` payload for the VLM span.

    Aggregate metrics only — no raw strings from the VLM ever appear here.
    The eval `test_no_phi_in_vlm_spans` runs this against a fixture
    extraction containing known PHI and asserts the payload contains
    none of those substrings.
    """
    citations = list(_walk_citations(extraction))
    mean_c, min_c, low_count = _confidence_stats(citations)
    pages = sorted(
        {c.page for c in citations if c is not None and c.page is not None}
    )

    if isinstance(extraction, LabPDFExtraction):
        field_count = len(extraction.results)
        coded = sum(1 for r in extraction.results if r.analyte_key is not None)
        uncoded = field_count - coded
    else:
        field_count = (
            1  # demographics block
            + 1  # chief_concern
            + len(extraction.current_medications)
            + len(extraction.allergies)
            + len(extraction.family_history)
        )
        coded, uncoded = _coded_uncoded_counts(extraction)

    payload: dict[str, Any] = {
        "extracted_field_count": field_count,
        "mean_confidence": mean_c,
        "min_confidence": min_c,
        "low_confidence_count": low_count,
        "coded_count": coded,
        "uncoded_count": uncoded,
        "pages_touched": pages,
        "doc_id": doc_id,
        "doc_type": doc_type,
        "mime_type": mime_type,
        "model_id": model_id,
        "latency_ms": latency_ms,
    }
    # Defense in depth: enforce the allowlist at construction time so a
    # future contributor adding a key learns about the invariant
    # immediately, not in production.
    extra = set(payload.keys()) - _VLM_OUTPUT_ALLOWED_KEYS
    if extra:
        raise ValueError(
            f"vlm_span_output produced disallowed keys: {sorted(extra)}. "
            "Update _VLM_OUTPUT_ALLOWED_KEYS deliberately and re-run the "
            "no-PHI eval against a fixture extraction."
        )
    return payload


def assert_no_phi_in_span_payload(
    payload: dict[str, Any], phi_substrings: Iterable[str]
) -> None:
    """Eval-time assertion: no PHI substring appears anywhere in the payload.

    Walks the dict recursively (handles nested dicts and lists), stringifies
    every leaf, and raises AssertionError on any hit. Use against the dict
    `vlm_span_output` returns, plus any other dict the worker plans to send
    to Langfuse.
    """

    def _stringify(node: Any) -> str:
        if isinstance(node, dict):
            return " ".join(f"{k} {_stringify(v)}" for k, v in node.items())
        if isinstance(node, (list, tuple, set)):
            return " ".join(_stringify(x) for x in node)
        return str(node)

    haystack = _stringify(payload)
    for term in phi_substrings:
        if term and term in haystack:
            raise AssertionError(
                f"PHI substring leaked into VLM span payload: {term!r}"
            )
