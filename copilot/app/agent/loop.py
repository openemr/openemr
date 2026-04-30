"""Agent orchestration loop — provider-agnostic.

Flow per ARCHITECTURE §2.1:
  1. Pick LLM adapter (Anthropic primary, OpenAI fallback) by config
  2. Build cached system prompt + tool definitions
  3. Send user question, loop on tool_use blocks
  4. The model's final action is a `submit_response` tool call (forced by prompt)
  5. Run Layer-1 attribution gate; if rejected, retry once with feedback
  6. Run Layer-2 domain rules; if rejected, return refusal
  7. Return AgentResponse (sanitized) + TurnTrace for observability

The loop has no provider-specific code — that's all behind LLMAdapter
(`app/agent/llm.py`). Verification gate, tool dispatch, and observability sit
outside.
"""
from __future__ import annotations

import time
from dataclasses import dataclass, field
from typing import Any

from app.agent.llm import LLMAdapter, ToolUseRequest, get_adapter
from app.agent.prompt import SYSTEM_PROMPT
from app.agent.schemas import AgentResponse, Claim, SUBMIT_RESPONSE_TOOL, TurnTrace
from app.config import Settings
from app.fhir.client import FhirClient
from app.phi.session import PseudonymMap
from app.tools.registry import dispatch, get_tool_definitions
from app.verification.attribution import verify
from app.verification.rules import apply_rules


@dataclass
class AgentTurnOutput:
    response: AgentResponse
    trace: TurnTrace
    raw_tool_results: list[dict] = field(default_factory=list)


def _tool_defs_for_loop() -> list[dict]:
    return get_tool_definitions() + [SUBMIT_RESPONSE_TOOL]


def _user_prefix(session: PseudonymMap, question: str) -> str:
    return (
        f"Active patient pseudonym: {session.patient_pseudonym()}\n"
        f"Physician: {session.physician_user_id}\n\n"
        f"Question:\n{question.strip()}"
    )


async def _run_one_pass(
    *,
    adapter: LLMAdapter,
    conversation: list[Any],
    tool_defs: list[dict],
    fhir: FhirClient,
    session: PseudonymMap,
    trace: TurnTrace,
    raw_tool_results: list[dict],
    max_iterations: int,
) -> AgentResponse | None:
    """One outer pass through the loop. Returns AgentResponse on submit_response,
    or None if the model never submitted (caller treats as failure).
    """
    for _iteration in range(max_iterations):
        resp = await adapter.call(SYSTEM_PROMPT, tool_defs, conversation)
        trace.tokens_input += resp.usage.input_tokens
        trace.tokens_output += resp.usage.output_tokens
        trace.tokens_cached += resp.usage.cached_tokens

        if not resp.tool_uses:
            # Provider returned plain text without calling submit_response.
            # Treat as protocol violation.
            return None

        adapter.append_assistant(conversation, resp)

        # Check for submit_response among the tool calls
        submit: ToolUseRequest | None = None
        non_submit_uses: list[ToolUseRequest] = []
        for use in resp.tool_uses:
            trace.tool_call_sequence.append(use.name)
            if use.name == "submit_response":
                submit = use
            else:
                non_submit_uses.append(use)

        if submit is not None:
            args = submit.arguments
            return AgentResponse(
                prose=args.get("prose", ""),
                claims=[Claim(**c) for c in args.get("claims") or []],
                data_gaps=list(args.get("data_gaps") or []),
            )

        # Dispatch the requested tools, attach results, loop
        tool_payloads: list[dict] = []
        is_error_flags: list[bool] = []
        for use in non_submit_uses:
            t0 = time.perf_counter()
            result = await dispatch(use.name, use.arguments, fhir, session)
            elapsed_ms = (time.perf_counter() - t0) * 1000
            trace.tool_latencies_ms[use.name] = elapsed_ms
            if result.error:
                trace.tool_failures[use.name] = result.error
            payload = result.to_dict()
            raw_tool_results.append(payload)
            tool_payloads.append(payload)
            is_error_flags.append(bool(result.error))

        adapter.append_tool_results(conversation, non_submit_uses, tool_payloads, is_error_flags)

    return None  # ran out of iterations


async def run_turn(
    *,
    settings: Settings,
    fhir: FhirClient,
    session: PseudonymMap,
    question: str,
    proposed_drug: str | None = None,
) -> AgentTurnOutput:
    started = time.perf_counter()
    adapter = get_adapter(settings)
    tool_defs = _tool_defs_for_loop()

    trace = TurnTrace(
        session_id=session.session_id,
        user_id=session.physician_user_id,
        patient_pseudonym=session.patient_pseudonym(),
        question_text=question,
        tool_call_sequence=[],
        tool_latencies_ms={},
        tool_failures={},
    )
    raw_tool_results: list[dict] = []

    conversation: list[Any] = [adapter.initial_user_message(_user_prefix(session, question))]

    # Pass 1
    response = await _run_one_pass(
        adapter=adapter,
        conversation=conversation,
        tool_defs=tool_defs,
        fhir=fhir,
        session=session,
        trace=trace,
        raw_tool_results=raw_tool_results,
        max_iterations=settings.agent_max_tool_iterations,
    )
    if response is None:
        response = AgentResponse(
            prose=(
                "I could not produce a response within the allowed tool steps. "
                "Please review the chart directly."
            ),
            claims=[],
            data_gaps=["agent_loop_did_not_submit"],
        )
        trace.verification_passed = False
        trace.verification_rejections.append("agent_did_not_submit_response")
        trace.final_response_length = len(response.prose)
        trace.total_latency_ms = (time.perf_counter() - started) * 1000
        return AgentTurnOutput(
            response=response, trace=trace, raw_tool_results=raw_tool_results
        )

    # Layer 1 — source attribution
    attr = verify(response, raw_tool_results)
    if not attr.passed:
        trace.verification_rejections.extend(attr.unknown_ids)
        retry_msg = (
            "Verification failed. The following claims were not anchored to any "
            "tool result and have been removed: "
            + ", ".join(c.text for c in attr.rejected_claims)
            + ". Please re-call submit_response with only claims you can anchor."
        )
        adapter.append_user_text(conversation, retry_msg)
        retried = await _run_one_pass(
            adapter=adapter,
            conversation=conversation,
            tool_defs=tool_defs,
            fhir=fhir,
            session=session,
            trace=trace,
            raw_tool_results=raw_tool_results,
            max_iterations=settings.agent_max_tool_iterations,
        )
        if retried is not None:
            attr = verify(retried, raw_tool_results)
            response = attr.sanitized
        else:
            response = attr.sanitized

    trace.verification_passed = attr.passed if response is attr.sanitized else True

    # Layer 2 — domain rules
    rules = apply_rules(
        response,
        raw_tool_results,
        session.patient_pseudonym(),
        proposed_drug=proposed_drug,
    )
    if not rules.passed:
        trace.domain_rule_rejections.extend(rules.rejection_reasons)
        response = rules.final

    trace.final_response_length = len(response.prose)
    trace.total_latency_ms = (time.perf_counter() - started) * 1000
    return AgentTurnOutput(
        response=response, trace=trace, raw_tool_results=raw_tool_results
    )
