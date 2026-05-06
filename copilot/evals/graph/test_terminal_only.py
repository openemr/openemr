"""Verify the single-node graph (KR 1, Task 1.2) builds and invokes.

This is a wiring test for the LangGraph build itself, not a behavior
test for the agent loop. Patches the answer_composer's ``run_turn`` so
no LLM call fires; asserts the graph's ``ainvoke`` returns a final state
populated by the wrapper.
"""
from __future__ import annotations

from dataclasses import dataclass
from typing import Any

import pytest


@dataclass
class _FakeAgentResponse:
    prose: str = "ok"
    claims: tuple = ()
    data_gaps: tuple = ()


@dataclass
class _FakeTurnTrace:
    session_id: str = "s1"


@dataclass
class _FakeAgentTurnOutput:
    response: _FakeAgentResponse
    trace: _FakeTurnTrace
    raw_tool_results: list[dict]


def test_build_graph_returns_compiled_graph() -> None:
    from app.graph.build import build_graph

    g = build_graph()
    assert hasattr(g, "ainvoke"), "compiled graph should expose ainvoke"


@pytest.mark.asyncio
async def test_terminal_node_only_runs_answer_composer(monkeypatch) -> None:
    captured: dict[str, Any] = {}

    async def fake_run_turn(**kwargs: Any) -> _FakeAgentTurnOutput:
        captured.update(kwargs)
        return _FakeAgentTurnOutput(
            response=_FakeAgentResponse(prose="terminal_ok"),
            trace=_FakeTurnTrace(),
            raw_tool_results=[{"tool": "noop", "data": []}],
        )

    monkeypatch.setattr("app.graph.workers.answer_composer.run_turn", fake_run_turn)
    from app.graph.build import build_graph

    g = build_graph()
    initial: dict[str, Any] = {
        "settings": object(),
        "fhir": object(),
        "session": object(),
        "question": "test",
        "tool_results": [],
        "routing_path": [],
        "retry_count": 0,
    }
    final = await g.ainvoke(initial)

    # The graph terminates after answer_composer.
    assert final["routing_path"] == ["answer_composer"]
    assert final["response"].prose == "terminal_ok"
    assert isinstance(final["trace"], _FakeTurnTrace)
    assert final["tool_results"] == [{"tool": "noop", "data": []}]
    # run_turn received the forwarded kwargs.
    assert captured["question"] == "test"
