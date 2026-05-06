"""``supervisor`` graph node + ``decide_next`` routing function.

The supervisor is intentionally **not** an LLM — PRD pitfall #3 explicitly
warns against opaque LLM-routed orchestration. Routing decisions are
plain Python over the current state, deterministic and inspectable.

Routing rules (W2_ARCHITECTURE.md §4.1):
  - If ``pending_extraction`` is set and ``intake_extractor`` hasn't run
    this turn → route to ``intake_extractor``.
  - Else if ``retrieval_seed_query`` is set and ``evidence_retriever``
    hasn't run this turn → route to ``evidence_retriever``.
  - Else → route to ``answer_composer`` (terminal compose step).

The "hasn't run this turn" guard prevents infinite loops between
supervisor and a worker.
"""
from __future__ import annotations

from typing import Any

from app.graph.state import AgentGraphState


async def supervise_tick(state: AgentGraphState) -> dict[str, Any]:
    """Append ``"supervisor"`` to ``routing_path``.

    The actual routing decision is made by ``decide_next`` (used as a
    LangGraph conditional-edge function). This node only marks that the
    supervisor was visited so the audit trail records each tick.
    """
    routing = list(state.get("routing_path") or [])
    routing.append("supervisor")
    return {"routing_path": routing}


def decide_next(state: AgentGraphState) -> str:
    """Decide which node runs next from the supervisor.

    Returns one of ``"intake_extractor"``, ``"evidence_retriever"``,
    ``"answer_composer"``. Pure function; no side effects.
    """
    routing = state.get("routing_path") or []

    if state.get("pending_extraction") and "intake_extractor" not in routing:
        return "intake_extractor"
    if state.get("retrieval_seed_query") and "evidence_retriever" not in routing:
        return "evidence_retriever"
    return "answer_composer"


__all__ = ["supervise_tick", "decide_next"]
