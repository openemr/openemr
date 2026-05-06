"""LangGraph state-machine assembly.

KR 1 progressive extensions:

  - Task 1.2: single-node graph (``answer_composer``) → END.
  - Task 1.3: adds ``critic`` node — composer → critic → END (this task).
  - Task 1.5: adds ``supervisor`` + ``intake_extractor`` + ``evidence_retriever``.

The compiled graph is built once per process (in the FastAPI lifespan)
and shared across requests via ``app.state.agent_graph``.
"""
from __future__ import annotations

from langgraph.graph import END, StateGraph

from app.graph.critic import critique
from app.graph.state import AgentGraphState
from app.graph.workers.answer_composer import compose


def build_graph():
    """Construct and compile the agent graph.

    Returns a compiled LangGraph runnable. Sync-compile + async-invoke is
    the LangGraph pattern.
    """
    graph: StateGraph = StateGraph(AgentGraphState)
    graph.add_node("answer_composer", compose)
    graph.add_node("critic", critique)
    graph.set_entry_point("answer_composer")
    graph.add_edge("answer_composer", "critic")
    graph.add_edge("critic", END)
    return graph.compile()


__all__ = ["build_graph"]
