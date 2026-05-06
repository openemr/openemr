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
async def test_compose_forwards_kwargs_and_seeds_run_turn(monkeypatch) -> None:
    """W2 KR1 fix: compose() passes upstream state.tool_results to run_turn
    as ``seed_tool_results``, and uses ``output.raw_tool_results`` as the
    canonical post-turn list (run_turn merges seed into it). Pre-fix
    behavior — appending state.tool_results AFTER run_turn — was broken
    because the LLM never saw the upstream worker output.
    """
    captured_kwargs: dict[str, Any] = {}

    upstream_tool_result = {"tool": "get_active_medications", "data": []}
    composer_synth_tool_result = {"tool": "get_recent_labs", "data": [{"loinc": "13457-7"}]}

    async def fake_run_turn(**kwargs: Any) -> _FakeAgentTurnOutput:
        captured_kwargs.update(kwargs)
        # The real run_turn would have prepended seed_tool_results into its
        # raw_tool_results. The fake mimics that contract so compose's
        # "use output.raw_tool_results directly" assumption works in tests.
        merged: list[dict] = list(kwargs.get("seed_tool_results") or [])
        merged.append(composer_synth_tool_result)
        return _FakeAgentTurnOutput(
            response=_FakeAgentResponse(),
            trace=_FakeTurnTrace(),
            raw_tool_results=merged,
        )

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
        "tool_results": [upstream_tool_result],
    }
    delta = await compose(state)  # type: ignore[arg-type]

    # Forwarded kwargs match state inputs.
    assert captured_kwargs["settings"] is settings
    assert captured_kwargs["fhir"] is fhir
    assert captured_kwargs["session"] is session
    assert captured_kwargs["question"] == "What is the LDL?"
    assert captured_kwargs["proposed_drug"] == "atorvastatin"
    assert captured_kwargs["prior_turns"] is None

    # The W2 KR1 fix: state.tool_results is forwarded as seed_tool_results.
    assert captured_kwargs["seed_tool_results"] == [upstream_tool_result]

    # Output merge: tool_results is exactly run_turn's output (seed + new),
    # not state.tool_results extended by raw_tool_results (the pre-fix bug).
    assert isinstance(delta["response"], _FakeAgentResponse)
    assert isinstance(delta["trace"], _FakeTurnTrace)
    assert len(delta["tool_results"]) == 2
    assert delta["tool_results"][0] is upstream_tool_result
    assert delta["tool_results"][1] is composer_synth_tool_result
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
