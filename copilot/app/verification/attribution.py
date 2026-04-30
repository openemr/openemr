"""Layer-1 verification — source attribution.

Every claim in the LLM's `submit_response` output must cite a record_id that
was returned by a tool call in this conversation. Claims with unknown
record_ids are stripped from the prose; the prose is reconstructed without
them. If anything was stripped, the gate flags `verification_passed=False` so
the orchestrator can retry once with feedback.

This is purely deterministic — no LLM is involved in the check itself.
"""
from __future__ import annotations

from dataclasses import dataclass

from app.agent.schemas import AgentResponse, Claim


@dataclass
class AttributionResult:
    passed: bool
    sanitized: AgentResponse
    rejected_claims: list[Claim]
    unknown_ids: list[str]


def collect_known_record_ids(tool_results: list[dict]) -> set[str]:
    """Union of record_ids seen across all tool results in this turn."""
    known: set[str] = set()
    for tr in tool_results:
        for rid in tr.get("record_ids") or []:
            known.add(rid)
        # Also accept ids inside data items (defense in depth)
        data = tr.get("data")
        if isinstance(data, list):
            for item in data:
                rid = (item or {}).get("record_id")
                if rid:
                    known.add(rid)
        elif isinstance(data, dict):
            rid = data.get("record_id")
            if rid:
                known.add(rid)
    return known


def verify(
    response: AgentResponse, tool_results: list[dict]
) -> AttributionResult:
    known = collect_known_record_ids(tool_results)
    rejected: list[Claim] = []
    accepted: list[Claim] = []
    for claim in response.claims:
        if claim.record_id in known:
            accepted.append(claim)
        else:
            rejected.append(claim)

    if not rejected:
        return AttributionResult(
            passed=True,
            sanitized=response,
            rejected_claims=[],
            unknown_ids=[],
        )

    # Reconstruct prose: drop sentences referencing rejected claims' text where
    # possible. Conservative: replace any rejected claim text with [unverified].
    sanitized_prose = response.prose
    for claim in rejected:
        if claim.text and claim.text in sanitized_prose:
            sanitized_prose = sanitized_prose.replace(claim.text, "[unverified]")

    sanitized = AgentResponse(
        prose=sanitized_prose,
        claims=accepted,
        data_gaps=response.data_gaps
        + [f"Unverified claim removed: {c.text}" for c in rejected],
    )
    return AttributionResult(
        passed=False,
        sanitized=sanitized,
        rejected_claims=rejected,
        unknown_ids=[c.record_id for c in rejected],
    )
