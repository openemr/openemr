"""LangGraph state-machine assembly.

KR 1, Task 1.2 — single-node graph: ``answer_composer`` is the only node,
terminating at ``END``. The graph is functionally equivalent to calling
``run_turn`` directly. Subsequent tasks (1.3 critic, 1.5 supervisor +
workers) extend this graph additively.

The compiled graph is built once per process (in the FastAPI lifespan)
and shared across requests via ``app.state.agent_graph``.
"""
from __future__ import annotations

from langgraph.graph import END, StateGraph

from app.graph.state import AgentGraphState
from app.graph.workers.answer_composer import compose


def build_graph():
    """Construct and compile the agent graph.

    Returns a compiled LangGraph runnable. Sync-compile + async-invoke is
    the LangGraph pattern.
    """
    graph: StateGraph = StateGraph(AgentGraphState)
    graph.add_node("answer_composer", compose)
    graph.set_entry_point("answer_composer")
    graph.add_edge("answer_composer", END)
    return graph.compile()


__all__ = ["build_graph"]
