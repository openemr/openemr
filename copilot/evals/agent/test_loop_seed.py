"""Verify ``run_turn(..., seed_tool_results=...)`` plumbs upstream worker
output into the LLM context + verify pre-conditions (W2 KR1 codex-fix).

Two tests:
  1. raw_tool_results from a turn started WITH a seed contains the seed
     entries (Layer-1 verify will accept citations against them).
  2. The conversation built for the LLM contains a "Pre-fetched evidence"
     primer when seed_tool_results is supplied — caught by patching the
     adapter to capture the conversation list it received.
"""
from __future__ import annotations

from typing import Any
from unittest.mock import AsyncMock

import pytest

from app.agent.loop import run_turn
from app.agent.schemas import AgentResponse, Claim, TurnTrace


class _FakeAdapterUsage:
    input_tokens = 0
    output_tokens = 0
    cached_tokens = 0
    cache_write_tokens = 0


class _FakeAdapter:
    """Captures the conversation passed to .call(), returns a submit_response
    payload that finishes the loop on first iteration.
    """

    def __init__(self) -> None:
        self.last_conversation: list[Any] | None = None

    async def call(self, system_prompt, tool_defs, conversation):
        self.last_conversation = list(conversation)
        # Simulate the model calling submit_response with one anchored claim.
        from app.agent.llm import ProviderResponse, ToolUseRequest, Usage

        usage = Usage()
        return ProviderResponse(
            text=None,
            tool_uses=[
                ToolUseRequest(
                    id="tu-1",
                    name="submit_response",
                    arguments={
                        "prose": "The LDL was 142.",
                        "claims": [
                            {
                                "text": "LDL 142",
                                "record_id": "Observation/seed-obs-1",
                            }
                        ],
                        "data_gaps": [],
                    },
                )
            ],
            usage=usage,
            raw_assistant_message=[],
        )

    def append_assistant(self, conversation, resp):
        conversation.append({"role": "assistant", "content": []})

    def append_tool_results(self, conversation, tool_uses, results, is_error):
        pass

    def append_user_text(self, conversation, text):
        conversation.append({"role": "user", "content": text})

    def initial_user_message(self, text):
        return {"role": "user", "content": text}

    def assistant_text_message(self, text):
        return {"role": "assistant", "content": [{"type": "text", "text": text}]}


class _FakeSession:
    session_id = "s-seed"
    physician_user_id = "dr-seed"

    def patient_pseudonym(self) -> str:
        return "patient-seed"

    def pseudo_for(self, *args, **kwargs):
        return "patient-seed"


class _FakeSettings:
    resume_replay_max_turns = 5
    agent_max_tool_iterations = 3


@pytest.mark.asyncio
async def test_run_turn_extends_raw_tool_results_with_seed(monkeypatch) -> None:
    """Layer-1 verify must see seed_tool_results record_ids as known."""
    fake_adapter = _FakeAdapter()
    monkeypatch.setattr("app.agent.loop.get_adapter", lambda settings: fake_adapter)

    seed = [
        {
            "tool": "search_guidelines",
            "data": [{"record_id": "Guideline/uspstf-statin-2022#sec-2.1"}],
        },
        {
            "tool": "get_recent_labs",
            "data": [
                {
                    "record_id": "Observation/seed-obs-1",
                    "subject_pseudonym": "patient-seed",
                }
            ],
        },
    ]

    output = await run_turn(
        settings=_FakeSettings(),
        fhir=object(),
        session=_FakeSession(),
        question="What's the LDL?",
        seed_tool_results=seed,
    )

    # raw_tool_results contains both seed entries.
    seed_tools = {tr["tool"] for tr in output.raw_tool_results}
    assert "search_guidelines" in seed_tools
    assert "get_recent_labs" in seed_tools

    # Verify accepted the claim's record_id (it was in the seed).
    assert output.response.claims  # not stripped
    assert output.trace.verification_passed is True


@pytest.mark.asyncio
async def test_run_turn_builds_seed_primer_in_conversation(monkeypatch) -> None:
    """The LLM must SEE the pre-fetched evidence as a conversation primer."""
    fake_adapter = _FakeAdapter()
    monkeypatch.setattr("app.agent.loop.get_adapter", lambda settings: fake_adapter)

    await run_turn(
        settings=_FakeSettings(),
        fhir=object(),
        session=_FakeSession(),
        question="ignored",
        seed_tool_results=[
            {"tool": "search_guidelines", "data": [{"record_id": "Guideline/x", "text": "..."}]},
        ],
    )

    convo = fake_adapter.last_conversation
    assert convo is not None
    primer_blob = "\n".join(str(m.get("content", "")) for m in convo)
    assert "Pre-fetched evidence" in primer_blob
    assert "search_guidelines" in primer_blob


@pytest.mark.asyncio
async def test_run_turn_without_seed_does_not_inject_primer(monkeypatch) -> None:
    """No seed → no primer (W1 path stays bit-identical)."""
    fake_adapter = _FakeAdapter()
    monkeypatch.setattr("app.agent.loop.get_adapter", lambda settings: fake_adapter)

    await run_turn(
        settings=_FakeSettings(),
        fhir=object(),
        session=_FakeSession(),
        question="anything",
    )

    convo = fake_adapter.last_conversation
    assert convo is not None
    # First (and only) user message is the actual question prefix, no primer.
    assert all(
        "Pre-fetched evidence" not in str(m.get("content", ""))
        for m in convo
    )
