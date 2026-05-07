"""Build the per-turn ``evidence_records`` map for the citation modal.

The iframe needs structured content for every citation chip the user can
click. For ``DocumentReference/...`` citations the modal renders the source
document with a bbox overlay (handled in copilot_iframe.js); for everything
else (Observation, MedicationRequest, AllergyIntolerance, Condition,
Encounter, Patient, Guideline, QuestionnaireResponse) we ship the
PHI-minimized data slice keyed by record_id so the iframe can render a
typed card without a separate fetch.

This module ONLY filters + tags — the data slices come verbatim from
``tool_results[*].data[i]`` which already passed through
``app/phi/minimizer.py`` on its way to the LLM. No new PHI surface.
"""

from __future__ import annotations

from typing import Any

from app.agent.schemas import Claim, EvidenceRecord


_PREFIX_TO_KIND: dict[str, str] = {
    "DocumentReference/": "document",
    "Observation/": "observation",
    "MedicationRequest/": "medication",
    "MedicationStatement/": "medication",
    "AllergyIntolerance/": "allergy",
    "Condition/": "condition",
    "Encounter/": "encounter",
    "Patient/": "patient",
    "Guideline/": "guideline",
    "QuestionnaireResponse/": "questionnaire",
}


def _kind_for(record_id: str) -> str:
    for prefix, kind in _PREFIX_TO_KIND.items():
        if record_id.startswith(prefix):
            return kind
    return "unknown"


def extract_evidence_records(
    tool_results: list[dict[str, Any]],
    claims: list[Claim],
) -> dict[str, EvidenceRecord]:
    """Return the subset of ``tool_results[*].data[*]`` cited by ``claims``.

    Only records whose ``record_id`` is referenced by at least one claim are
    included — this avoids leaking the full tool-results bundle when only a
    handful of facts are cited. On a typical turn the cited set is 2–4 items;
    worst case ~5 KB on the wire.

    Each ``data`` slice is forwarded as-is. Layer-1 verification
    (``app/verification/attribution.py::verify``) is unchanged: it tests
    record_id-set membership against ``tool_results[*].record_ids`` and never
    looks at this map.
    """
    cited = {claim.record_id for claim in claims}
    if not cited:
        return {}

    out: dict[str, EvidenceRecord] = {}
    for result in tool_results:
        data = result.get("data") or []
        if isinstance(data, dict):
            data = [data]
        if not isinstance(data, list):
            continue
        for item in data:
            if not isinstance(item, dict):
                continue
            rid = item.get("record_id")
            if not isinstance(rid, str):
                continue
            if rid in cited and rid not in out:
                out[rid] = EvidenceRecord(kind=_kind_for(rid), data=item)
    return out
