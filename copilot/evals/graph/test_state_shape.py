"""Verify ``AgentGraphState`` accepts every documented key.

This is a wiring test, not a behavior test — it locks the state shape so
later tasks (critic, workers, supervisor) cannot silently drop a key
without test churn.
"""
from __future__ import annotations

from app.graph.state import AgentGraphState


def test_agent_graph_state_accepts_all_documented_keys() -> None:
    state: AgentGraphState = {
        "settings": object(),
        "fhir": object(),
        "session": object(),
        "question": "What is the LDL?",
        "proposed_drug": None,
        "prior_turns": None,
        "routing_path": [],
        "pending_extraction": None,
        "retrieval_seed_query": None,
        "tool_results": [],
        "retry_count": 0,
        "response": None,
        "trace": None,
        "rejections": [],
    }

    # TypedDict with total=False — partial states must also be valid.
    partial: AgentGraphState = {"question": "hello"}

    assert state["question"] == "What is the LDL?"
    assert state["routing_path"] == []
    assert state["retry_count"] == 0
    assert partial["question"] == "hello"


def test_agent_graph_state_partial_state_round_trips() -> None:
    """A node may return only the keys it modified; merging must work."""
    base: AgentGraphState = {
        "question": "What is the LDL?",
        "routing_path": ["supervisor"],
        "tool_results": [],
        "retry_count": 0,
    }

    # Simulate a node that adds to routing_path and tool_results.
    delta: dict[str, object] = {
        "routing_path": [*base["routing_path"], "answer_composer"],
        "tool_results": [{"tool": "get_recent_labs", "data": []}],
    }
    merged: AgentGraphState = {**base, **delta}  # type: ignore[typeddict-item]

    assert merged["routing_path"] == ["supervisor", "answer_composer"]
    assert len(merged["tool_results"]) == 1
    assert merged["question"] == "What is the LDL?"  # untouched key survives
