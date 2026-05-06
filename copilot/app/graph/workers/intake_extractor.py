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

    return {
        "routing_path": routing,
        "tool_results": tool_results,
        # Consume the signal so the supervisor doesn't re-route here.
        "pending_extraction": None,
    }


__all__ = ["run"]
