"""LangGraph state-machine assembly.

KR 1 progressive extensions:

  - Task 1.2: single-node graph (``answer_composer``) → END.
  - Task 1.3: adds ``critic`` node — composer → critic → END.
  - Task 1.5 (this task): adds ``supervisor`` + workers
    (``intake_extractor``, ``evidence_retriever``); supervisor uses a
    plain-Python deterministic ``decide_next`` to route.

Routing topology (W2_ARCHITECTURE.md §4):

    START → supervisor → [intake_extractor | evidence_retriever | answer_composer]
       intake_extractor → supervisor
       evidence_retriever → supervisor
       answer_composer → critic → END

The compiled graph is built once per process (in the FastAPI lifespan)
and shared across requests via ``app.state.agent_graph``.
"""
from __future__ import annotations

from langgraph.graph import END, StateGraph

from app.graph.critic import critique
from app.graph.state import AgentGraphState
from app.graph.supervisor import decide_next, supervise_tick
from app.graph.workers import evidence_retriever, intake_extractor
from app.graph.workers.answer_composer import compose


def build_graph():
    """Construct and compile the agent graph.

    Returns a compiled LangGraph runnable. Sync-compile + async-invoke is
    the LangGraph pattern.
    """
    graph: StateGraph = StateGraph(AgentGraphState)

    # Nodes
    graph.add_node("supervisor", supervise_tick)
    graph.add_node("intake_extractor", intake_extractor.run)
    graph.add_node("evidence_retriever", evidence_retriever.run)
    graph.add_node("answer_composer", compose)
    graph.add_node("critic", critique)

    # Entry: supervisor decides where to go first.
    graph.set_entry_point("supervisor")

    # Conditional routing from the supervisor — deterministic, plain-Python.
    graph.add_conditional_edges(
        "supervisor",
        decide_next,
        {
            "intake_extractor": "intake_extractor",
            "evidence_retriever": "evidence_retriever",
            "answer_composer": "answer_composer",
        },
    )

    # Workers fan back to the supervisor — it picks the next step (another
    # worker if more pending, or answer_composer when all signals consumed).
    graph.add_edge("intake_extractor", "supervisor")
    graph.add_edge("evidence_retriever", "supervisor")

    # Terminal compose → critic → END.
    graph.add_edge("answer_composer", "critic")
    graph.add_edge("critic", END)

    return graph.compile()


__all__ = ["build_graph"]
