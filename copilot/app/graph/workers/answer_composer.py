"""``answer_composer`` worker — terminal node that wraps the W1 single-agent loop.

The W1 ``run_turn`` orchestrates the LLM tool-use loop and emits an
``AgentTurnOutput``. In the W2 graph, this becomes the terminal node:
the supervisor decides whether to fan out to ``intake_extractor`` /
``evidence_retriever`` first, then routes to ``answer_composer`` to
compose the final response. The critic node runs after this and gates
emission.

This task (KR 1, Task 1.1) introduces only the wrapper. Wiring the
graph and swapping ``/v1/chat`` is Task 1.2; lifting verification out
of ``run_turn`` into the critic node is Task 1.3.
"""
from __future__ import annotations

from typing import Any

from app.agent.loop import run_turn
from app.graph.state import AgentGraphState


async def compose(state: AgentGraphState) -> dict[str, Any]:
    """Run the W1 agent loop against the current state.

    Required state keys: ``settings``, ``fhir``, ``session``, ``question``.
    Optional keys: ``proposed_drug``, ``prior_turns``.

    Returns a partial state dict (LangGraph merges into the running
    state) with ``response``, ``trace``, and ``tool_results`` populated.
    Pre-existing ``tool_results`` from upstream workers are extended,
    not overwritten, so the answer_composer can build on
    intake_extractor / evidence_retriever output.
    """
    output = await run_turn(
        settings=state["settings"],
        fhir=state["fhir"],
        session=state["session"],
        question=state["question"],
        proposed_drug=state.get("proposed_drug"),
        prior_turns=state.get("prior_turns"),
    )

    existing_tool_results = list(state.get("tool_results") or [])
    existing_tool_results.extend(output.raw_tool_results)

    existing_routing = list(state.get("routing_path") or [])
    existing_routing.append("answer_composer")

    # W2 KR3: surface graph-level audit trail + worker-derived metrics into
    # the trace so observability + the no-PHI rubric scan it.
    if hasattr(output.trace, "routing_path"):
        output.trace.routing_path = list(existing_routing)
    if hasattr(output.trace, "documents_attached"):
        output.trace.documents_attached = sum(
            1 for tr in existing_tool_results if tr.get("tool") == "attach_and_extract"
        )
    ext_conf = state.get("extraction_confidence_min")
    if ext_conf is not None and hasattr(output.trace, "extraction_confidence_min"):
        output.trace.extraction_confidence_min = ext_conf
    hit_ids = state.get("retrieval_hit_ids") or []
    if hit_ids and hasattr(output.trace, "retrieval_hit_ids"):
        output.trace.retrieval_hit_ids = list(hit_ids)
    rerank_scores = state.get("rerank_scores") or []
    if rerank_scores and hasattr(output.trace, "rerank_scores"):
        output.trace.rerank_scores = list(rerank_scores)

    return {
        "response": output.response,
        "trace": output.trace,
        "tool_results": existing_tool_results,
        "routing_path": existing_routing,
    }


__all__ = ["compose"]
