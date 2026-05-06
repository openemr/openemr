"""``evidence_retriever`` worker — pre-fetch guideline evidence.

When the supervisor sets ``state["retrieval_seed_query"]`` (typically
when the question pattern-matches a guideline-flavored intent —
"what guideline applies?", "USPSTF…", "ADA recommendation"), this
worker dispatches ``search_guidelines`` and seeds ``state["tool_results"]``
so the answer_composer doesn't need to spend a tool-use round on it.

Like ``intake_extractor``, this node is currently a structural
placeholder: today's LLM dispatches ``search_guidelines`` itself when
needed. Pre-seeding is a token / latency optimization that this graph
shape enables; KR 4 (dense + rerank) will exercise it.
"""
from __future__ import annotations

from typing import Any

from app.graph.state import AgentGraphState
from app.retrieval.rerank import get_reranker
from app.tools.registry import dispatch


async def run(state: AgentGraphState) -> dict[str, Any]:
    """Run a guideline search if ``retrieval_seed_query`` is set; else no-op."""
    routing = list(state.get("routing_path") or [])
    routing.append("evidence_retriever")

    query = state.get("retrieval_seed_query")
    if not query:
        return {"routing_path": routing}

    fhir = state["fhir"]
    session = state["session"]
    result = await dispatch("search_guidelines", {"query": query}, fhir, session)

    tool_results = list(state.get("tool_results") or [])
    tool_results.append(result.to_dict())

    # W2 KR3: derive retrieval_hit_ids (chunk_ids in score order) from the
    # tool result data so answer_composer can surface them into TurnTrace.
    data = result.data if isinstance(result.data, list) else [result.data]
    hit_dicts: list[dict[str, Any]] = [d for d in data if isinstance(d, dict)]

    # W2 KR4: rerank step. The default IdentityReranker preserves BM25 order
    # and emits 1.0 scores; Cohere / local cross-encoder are gated by env.
    reranker = get_reranker()
    reordered, scores = reranker.rerank(query, hit_dicts)

    hit_ids: list[str] = []
    for item in reordered:
        chunk_id = item.get("chunk_id") if isinstance(item, dict) else None
        if isinstance(chunk_id, str):
            hit_ids.append(chunk_id)

    delta: dict[str, Any] = {
        "routing_path": routing,
        "tool_results": tool_results,
        # Consume the signal so the supervisor doesn't re-route here.
        "retrieval_seed_query": None,
    }
    if hit_ids:
        delta["retrieval_hit_ids"] = hit_ids
    if scores:
        delta["rerank_scores"] = scores
    return delta


__all__ = ["run"]
