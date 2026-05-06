"""Verify ``answer_composer.compose`` passes through to ``run_turn``.

This is a wiring test, not a behavior test — it patches ``run_turn`` to
verify the wrapper forwards the right kwargs and merges the output back
into state. Real ``run_turn`` behavior is exercised by the existing W1
test suite.
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


@pytest.mark.asyncio
async def test_compose_forwards_kwargs_and_merges_output(monkeypatch) -> None:
    captured_kwargs: dict[str, Any] = {}

    async def fake_run_turn(**kwargs: Any) -> _FakeAgentTurnOutput:
        captured_kwargs.update(kwargs)
        return _FakeAgentTurnOutput(
            response=_FakeAgentResponse(),
            trace=_FakeTurnTrace(),
            raw_tool_results=[{"tool": "get_recent_labs", "data": [{"loinc": "13457-7"}]}],
        )

    # Patch run_turn at the import site used by answer_composer.
    monkeypatch.setattr("app.graph.workers.answer_composer.run_turn", fake_run_turn)

    from app.graph.workers.answer_composer import compose

    settings = object()
    fhir = object()
    session = object()

    state: dict[str, Any] = {
        "settings": settings,
        "fhir": fhir,
        "session": session,
        "question": "What is the LDL?",
        "proposed_drug": "atorvastatin",
        "prior_turns": None,
        "routing_path": ["supervisor"],
        "tool_results": [{"tool": "get_active_medications", "data": []}],
    }
    delta = await compose(state)  # type: ignore[arg-type]

    # Forwarded kwargs match state inputs.
    assert captured_kwargs["settings"] is settings
    assert captured_kwargs["fhir"] is fhir
    assert captured_kwargs["session"] is session
    assert captured_kwargs["question"] == "What is the LDL?"
    assert captured_kwargs["proposed_drug"] == "atorvastatin"
    assert captured_kwargs["prior_turns"] is None

    # Output merge: response + trace populated, tool_results appended (not replaced),
    # routing_path appended.
    assert isinstance(delta["response"], _FakeAgentResponse)
    assert isinstance(delta["trace"], _FakeTurnTrace)
    assert len(delta["tool_results"]) == 2
    assert delta["tool_results"][0]["tool"] == "get_active_medications"
    assert delta["tool_results"][1]["tool"] == "get_recent_labs"
    assert delta["routing_path"] == ["supervisor", "answer_composer"]


@pytest.mark.asyncio
async def test_compose_handles_empty_optional_state(monkeypatch) -> None:
    """A turn with no upstream tool_results / routing_path still composes."""
    async def fake_run_turn(**kwargs: Any) -> _FakeAgentTurnOutput:
        return _FakeAgentTurnOutput(
            response=_FakeAgentResponse(),
            trace=_FakeTurnTrace(),
            raw_tool_results=[],
        )

    monkeypatch.setattr("app.graph.workers.answer_composer.run_turn", fake_run_turn)
    from app.graph.workers.answer_composer import compose

    state: dict[str, Any] = {
        "settings": object(),
        "fhir": object(),
        "session": object(),
        "question": "anything",
    }
    delta = await compose(state)  # type: ignore[arg-type]

    assert delta["routing_path"] == ["answer_composer"]
    assert delta["tool_results"] == []
    assert isinstance(delta["response"], _FakeAgentResponse)
