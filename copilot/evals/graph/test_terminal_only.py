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


class _FakeSession:
    """Stand-in for app.phi.session.PseudonymMap — the critic needs
    ``.patient_pseudonym()`` for Layer-2 rules.
    """

    def patient_pseudonym(self) -> str:
        return "patient-pseudo-A"


@pytest.mark.asyncio
async def test_terminal_graph_routes_through_composer_then_critic(monkeypatch) -> None:
    captured: dict[str, Any] = {}

    async def fake_run_turn(**kwargs: Any) -> _FakeAgentTurnOutput:
        captured.update(kwargs)
        # Return a response with NO claims so the critic's verify + Layer-2
        # rules pass trivially. This test verifies graph routing, not
        # verification — the critic is exercised directly in test_critic_node.py.
        from app.agent.schemas import AgentResponse, TurnTrace

        return _FakeAgentTurnOutput(
            response=AgentResponse(prose="terminal_ok", claims=[], data_gaps=[]),
            trace=TurnTrace(
                session_id="s1",
                user_id="dr_alvarez",
                patient_pseudonym="patient-pseudo-A",
                question_text="test",
                tool_call_sequence=[],
                tool_latencies_ms={},
                tool_failures={},
            ),
            raw_tool_results=[],
        )

    monkeypatch.setattr("app.graph.workers.answer_composer.run_turn", fake_run_turn)
    from app.graph.build import build_graph

    g = build_graph()
    initial: dict[str, Any] = {
        "settings": object(),
        "fhir": object(),
        "session": _FakeSession(),
        "question": "test",
        "tool_results": [],
        "routing_path": [],
        "retry_count": 0,
        "rejections": [],
    }
    final = await g.ainvoke(initial)

    # The graph routes composer → critic → END.
    assert final["routing_path"] == ["answer_composer", "critic"]
    assert final["response"].prose == "terminal_ok"
    assert final["rejections"] == []
    # run_turn received the forwarded kwargs.
    assert captured["question"] == "test"
