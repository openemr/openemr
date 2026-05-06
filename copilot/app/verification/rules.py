"""Layer-2 verification — domain rules.

Hard-coded rules. These are NOT prompt instructions; they are post-hoc checks
that hard-block dangerous outputs regardless of what the LLM claimed.

For v1 we ship two rules (per plan):
  - cross_patient_leakage: any cited record_id whose subject is not the active
    patient → REJECT entire response, log security incident.
  - allergy_contraindication: any "safe to prescribe" verdict for a med whose
    class matches an active AllergyIntolerance → REJECT, return contraindicated.

More rules (renal dose, QTc, sensitivity gate) are §4.1 stretch.
"""
from __future__ import annotations

import re
from dataclasses import dataclass
from typing import Any

from app.agent.schemas import AgentResponse
from app.verification.attribution import collect_known_record_ids


@dataclass
class RuleResult:
    passed: bool
    rejection_reasons: list[str]
    final: AgentResponse  # may be sanitized or replaced with refusal


def _claimed_record_ids(response: AgentResponse) -> list[str]:
    return [c.record_id for c in response.claims]


def _record_belongs_to_active_patient(
    record_id: str, tool_results: list[dict], active_patient_pseudonym: str
) -> bool:
    """Walk tool result data, find the record by id, check subject_pseudonym."""
    for tr in tool_results:
        data = tr.get("data")
        items = data if isinstance(data, list) else [data] if isinstance(data, dict) else []
        for item in items:
            if not isinstance(item, dict):
                continue
            if item.get("record_id") != record_id:
                continue
            subj = item.get("subject_pseudonym")
            # Patient resource itself: id is the pseudonym
            if item.get("resourceType") == "Patient":
                return item.get("id") == active_patient_pseudonym
            if subj is None:
                # No subject reference (e.g. AllergyIntolerance from a
                # patient-scoped query). Trust the record came from this
                # session's patient-scoped FHIR call.
                return True
            return subj == active_patient_pseudonym
    # Record not found in any tool result — should have been caught by Layer 1
    return False


def check_cross_patient_leakage(
    response: AgentResponse,
    tool_results: list[dict],
    active_patient_pseudonym: str,
) -> list[str]:
    rejections: list[str] = []
    for rid in _claimed_record_ids(response):
        if not _record_belongs_to_active_patient(
            rid, tool_results, active_patient_pseudonym
        ):
            rejections.append(
                f"Cross-patient leakage: record_id {rid} not from active patient"
            )
    return rejections


SAFE_VERDICT_PATTERNS = [
    re.compile(r"\bsafe to prescribe\b", re.IGNORECASE),
    re.compile(r"\bno (drug-)?interactions\b.*\bsafe\b", re.IGNORECASE),
    re.compile(r"\bverdict[:\s]+safe\b", re.IGNORECASE),
]


def _looks_like_safe_verdict(prose: str) -> bool:
    return any(p.search(prose) for p in SAFE_VERDICT_PATTERNS)


def _allergy_records(tool_results: list[dict]) -> list[dict[str, Any]]:
    out: list[dict[str, Any]] = []
    for tr in tool_results:
        if tr.get("record_type") != "AllergyIntolerance":
            continue
        data = tr.get("data") or []
        if isinstance(data, list):
            out.extend(d for d in data if isinstance(d, dict))
    return out


def check_allergy_contraindication(
    response: AgentResponse, tool_results: list[dict], proposed_drug: str | None
) -> list[str]:
    """If the LLM produced a 'safe' verdict but the patient has an active
    allergy whose display matches the proposed drug or its substring, reject.
    """
    if not _looks_like_safe_verdict(response.prose):
        return []
    if not proposed_drug:
        return []
    rejections: list[str] = []
    for allergy in _allergy_records(tool_results):
        if allergy.get("clinical_status") != "active":
            continue
        display = (allergy.get("display") or "").lower()
        if not display:
            continue
        if (
            display in proposed_drug.lower()
            or proposed_drug.lower() in display
        ):
            rejections.append(
                f"Allergy contraindication: 'safe' verdict but patient has active "
                f"AllergyIntolerance to '{allergy.get('display')}'"
            )
    return rejections


def check_extracted_fact_has_source_doc(
    response: AgentResponse, tool_results: list[dict]
) -> list[str]:
    """W2 Layer-2 rule.

    Reject any claim citing a ``DocumentReference/{doc_id}#…`` per-fact
    record_id whose **parent** ``DocumentReference/{doc_id}`` (sans
    fragment) does not appear in this turn's ``tool_results``. Layer-1
    ``verify`` already requires the per-fact record_id to be in
    tool_results; this rule adds the cross-check that the parent doc
    record was emitted by the extraction worker, defending against
    fragment-only fabrications.
    """
    rejections: list[str] = []
    known = collect_known_record_ids(tool_results)
    for claim in response.claims:
        rid = claim.record_id
        if not rid.startswith("DocumentReference/"):
            continue
        parent_id = rid.split("#", 1)[0]  # strip fragment if any
        if parent_id not in known:
            rejections.append(
                f"Extracted-fact citation lacks source document: claim "
                f"references {rid} but parent {parent_id} is not in this "
                f"turn's extraction output."
            )
    return rejections


def check_evidence_chunk_in_corpus(
    response: AgentResponse, known_chunk_ids: frozenset[str]
) -> list[str]:
    """W2 Layer-2 rule.

    Reject any claim citing a ``Guideline/{chunk_id}`` record_id whose
    ``chunk_id`` is not in the loaded corpus. Defends against fabricated
    guideline citations the LLM might emit even after retrieval.
    """
    rejections: list[str] = []
    for claim in response.claims:
        rid = claim.record_id
        if not rid.startswith("Guideline/"):
            continue
        # Format: Guideline/{chunk_id} — fragment is stripped if present.
        chunk_id = rid.split("/", 1)[1].split("#", 1)[0]
        if chunk_id not in known_chunk_ids:
            rejections.append(
                f"Evidence chunk not in corpus: claim cites {rid} "
                f"({chunk_id} is not a known chunk_id)."
            )
    return rejections


def apply_rules(
    response: AgentResponse,
    tool_results: list[dict],
    active_patient_pseudonym: str,
    proposed_drug: str | None = None,
    known_chunk_ids: frozenset[str] | None = None,
) -> RuleResult:
    rejections: list[str] = []
    rejections.extend(
        check_cross_patient_leakage(response, tool_results, active_patient_pseudonym)
    )
    rejections.extend(
        check_allergy_contraindication(response, tool_results, proposed_drug)
    )
    rejections.extend(
        check_extracted_fact_has_source_doc(response, tool_results)
    )
    if known_chunk_ids is not None:
        rejections.extend(
            check_evidence_chunk_in_corpus(response, known_chunk_ids)
        )
    if not rejections:
        return RuleResult(passed=True, rejection_reasons=[], final=response)
    refusal = AgentResponse(
        prose=(
            "I cannot return this response — a domain-rule check failed. "
            "Please review the chart directly. (Reason logged.)"
        ),
        claims=[],
        data_gaps=rejections,
    )
    return RuleResult(passed=False, rejection_reasons=rejections, final=refusal)
