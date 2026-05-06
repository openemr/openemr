"""``intake_extractor`` worker — pre-extract a pending document.

When the upstream pipeline (e.g., a future inline-document /v1/chat
variant) sets ``state["pending_extraction"]`` with the doc bytes / MIME /
doc_type, this worker calls the same ``attach_and_extract`` tool the
LLM would otherwise dispatch from inside the agent loop. The result is
appended to ``state["tool_results"]`` so the answer_composer's tool-use
loop sees the extraction as if the LLM had called it.

In the current /v1/chat path this worker is a no-op (uploads go through
``POST /v1/documents/attach`` separately, and the agent reads cached
extractions via ``get_recent_uploads``). The node exists so the graph
shape matches W2_ARCHITECTURE.md §4.2 and so future inline-multimodal
flows have a place to plug in without re-shaping the graph.
"""
from __future__ import annotations

from typing import Any

from app.graph.state import AgentGraphState
from app.tools.registry import dispatch


async def run(state: AgentGraphState) -> dict[str, Any]:
    """Run the extraction if ``pending_extraction`` is set; otherwise no-op."""
    routing = list(state.get("routing_path") or [])
    routing.append("intake_extractor")

    pending = state.get("pending_extraction")
    if not pending:
        return {"routing_path": routing}

    fhir = state["fhir"]
    session = state["session"]
    result = await dispatch("attach_and_extract", dict(pending), fhir, session)

    tool_results = list(state.get("tool_results") or [])
    tool_results.append(result.to_dict())

    # W2 KR3: derive extraction_confidence_min from per-fact source_citation
    # confidences embedded in the tool_result data items. Defensive over the
    # heterogeneous result.data shape (list of items; some may not carry a
    # source_citation, e.g. parent DocumentReference rows).
    confidences: list[float] = []
    cost_estimate_usd: float | None = None
    data = result.data if isinstance(result.data, list) else [result.data]
    for item in data:
        if not isinstance(item, dict):
            continue
        sc = item.get("source_citation")
        if isinstance(sc, dict):
            c = sc.get("confidence")
            if isinstance(c, (int, float)):
                confidences.append(float(c))
        # W2 KR8: parent DocumentReference item carries the cost estimate.
        c_usd = item.get("cost_estimate_usd")
        if isinstance(c_usd, (int, float)):
            cost_estimate_usd = float(c_usd)
    extraction_confidence_min = min(confidences) if confidences else None

    delta: dict[str, Any] = {
        "routing_path": routing,
        "tool_results": tool_results,
        # Consume the signal so the supervisor doesn't re-route here.
        "pending_extraction": None,
    }
    if extraction_confidence_min is not None:
        delta["extraction_confidence_min"] = extraction_confidence_min
    if cost_estimate_usd is not None:
        delta["vlm_cost_estimate_usd"] = cost_estimate_usd
    return delta


__all__ = ["run"]
