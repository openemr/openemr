"""LLM provider adapters — Anthropic (primary) and OpenAI (fallback).

Both adapters implement the same `LLMAdapter` Protocol so the agent loop can
swap between them by config (`LLM_PROVIDER` env var). The agent's verification
gate, tool dispatch, and observability sit *outside* this module — they only
care about (text response, tool_uses, usage), not which vendor produced them.

Design notes:
  - Anthropic is the architectural primary (BAA, prompt caching, clinical
    reasoning per ARCHITECTURE §2.2). OpenAI is the runtime fallback.
  - Tool-use schema differs between providers; the adapter normalizes it into
    `ToolUseRequest` so loop.py never sees vendor-specific shapes.
  - Each adapter owns its own message-list shape because OpenAI uses
    `role=tool` follow-ups while Anthropic uses `tool_result` content blocks.
"""
from __future__ import annotations

import json
import logging
from dataclasses import dataclass
from typing import Any, Protocol

logger = logging.getLogger("copilot.agent.llm")


@dataclass
class ToolUseRequest:
    id: str
    name: str
    arguments: dict[str, Any]


@dataclass
class Usage:
    input_tokens: int = 0
    output_tokens: int = 0
    cached_tokens: int = 0          # cache_read_input_tokens (warm cache)
    cache_write_tokens: int = 0     # cache_creation_input_tokens (cold cache)


@dataclass
class ProviderResponse:
    """Vendor-neutral shape returned by every adapter."""
    text: str | None  # final assistant text, None if the model only made tool calls
    tool_uses: list[ToolUseRequest]
    usage: Usage
    raw_assistant_message: Any  # provider-native object to echo back into next turn


class LLMAdapter(Protocol):
    name: str

    async def call(
        self, system_prompt: str, tool_defs: list[dict], conversation: list[Any]
    ) -> ProviderResponse: ...

    def append_assistant(self, conversation: list[Any], resp: ProviderResponse) -> None: ...

    def append_tool_results(
        self,
        conversation: list[Any],
        tool_uses: list[ToolUseRequest],
        results: list[dict[str, Any]],
        is_error: list[bool],
    ) -> None: ...

    def append_user_text(self, conversation: list[Any], text: str) -> None: ...

    def initial_user_message(self, text: str) -> Any: ...


# ---------------------- Anthropic ----------------------


class AnthropicAdapter:
    name = "anthropic"

    def __init__(self, api_key: str, model: str):
        import anthropic
        self._anthropic = anthropic
        self._client = anthropic.AsyncAnthropic(api_key=api_key)
        self._model = model

    async def call(
        self, system_prompt: str, tool_defs: list[dict], conversation: list[Any]
    ) -> ProviderResponse:
        # Cache the entire tool block by attaching cache_control to the LAST tool.
        # Anthropic's ephemeral cache covers everything up to (and including) the
        # breakpoint. Combined with the system-prompt breakpoint below, we have 2
        # of the 4 allowed breakpoints — caches ~2.3K tokens of stable content.
        tools_with_cache = list(tool_defs)
        if tools_with_cache:
            last = dict(tools_with_cache[-1])
            last["cache_control"] = {"type": "ephemeral"}
            tools_with_cache[-1] = last
        resp = await self._client.messages.create(
            model=self._model,
            max_tokens=2048,
            system=[
                {"type": "text", "text": system_prompt, "cache_control": {"type": "ephemeral"}}
            ],
            tools=tools_with_cache,
            messages=conversation,
        )
        usage = getattr(resp, "usage", None)
        u = Usage()
        if usage:
            u.input_tokens = getattr(usage, "input_tokens", 0) or 0
            u.output_tokens = getattr(usage, "output_tokens", 0) or 0
            u.cached_tokens = getattr(usage, "cache_read_input_tokens", 0) or 0
            u.cache_write_tokens = getattr(usage, "cache_creation_input_tokens", 0) or 0

        text_chunks: list[str] = []
        tool_uses: list[ToolUseRequest] = []
        for block in resp.content:
            if block.type == "text":
                text_chunks.append(block.text)
            elif block.type == "tool_use":
                tool_uses.append(
                    ToolUseRequest(id=block.id, name=block.name, arguments=block.input or {})
                )
        return ProviderResponse(
            text="\n".join(text_chunks) if text_chunks else None,
            tool_uses=tool_uses,
            usage=u,
            raw_assistant_message=resp.content,
        )

    def append_assistant(self, conversation: list[Any], resp: ProviderResponse) -> None:
        conversation.append({"role": "assistant", "content": resp.raw_assistant_message})

    def append_tool_results(
        self,
        conversation: list[Any],
        tool_uses: list[ToolUseRequest],
        results: list[dict[str, Any]],
        is_error: list[bool],
    ) -> None:
        blocks = [
            {
                "type": "tool_result",
                "tool_use_id": tu.id,
                "content": json.dumps(r, default=str),
                "is_error": err,
            }
            for tu, r, err in zip(tool_uses, results, is_error)
        ]
        conversation.append({"role": "user", "content": blocks})

    def append_user_text(self, conversation: list[Any], text: str) -> None:
        conversation.append({"role": "user", "content": text})

    def initial_user_message(self, text: str) -> Any:
        return {"role": "user", "content": text}


# ---------------------- OpenAI ----------------------


class OpenAIAdapter:
    name = "openai"

    def __init__(self, api_key: str, model: str):
        from openai import AsyncOpenAI
        self._client = AsyncOpenAI(api_key=api_key)
        self._model = model

    @staticmethod
    def _to_openai_tools(tool_defs: list[dict]) -> list[dict]:
        out = []
        for t in tool_defs:
            out.append(
                {
                    "type": "function",
                    "function": {
                        "name": t["name"],
                        "description": t["description"],
                        "parameters": t["input_schema"],
                    },
                }
            )
        return out

    async def call(
        self, system_prompt: str, tool_defs: list[dict], conversation: list[Any]
    ) -> ProviderResponse:
        messages = [{"role": "system", "content": system_prompt}, *conversation]
        resp = await self._client.chat.completions.create(
            model=self._model,
            messages=messages,
            tools=self._to_openai_tools(tool_defs),
            tool_choice="auto",
            max_tokens=2048,
        )
        msg = resp.choices[0].message
        usage = getattr(resp, "usage", None)
        u = Usage()
        if usage:
            u.input_tokens = getattr(usage, "prompt_tokens", 0) or 0
            u.output_tokens = getattr(usage, "completion_tokens", 0) or 0
            details = getattr(usage, "prompt_tokens_details", None)
            if details is not None:
                u.cached_tokens = getattr(details, "cached_tokens", 0) or 0

        tool_uses: list[ToolUseRequest] = []
        for tc in (msg.tool_calls or []):
            try:
                args = json.loads(tc.function.arguments or "{}")
            except json.JSONDecodeError:
                args = {}
            tool_uses.append(
                ToolUseRequest(id=tc.id, name=tc.function.name, arguments=args)
            )
        return ProviderResponse(
            text=msg.content,
            tool_uses=tool_uses,
            usage=u,
            raw_assistant_message=msg,
        )

    def append_assistant(self, conversation: list[Any], resp: ProviderResponse) -> None:
        msg = resp.raw_assistant_message
        entry: dict[str, Any] = {"role": "assistant", "content": msg.content or ""}
        if msg.tool_calls:
            entry["tool_calls"] = [
                {
                    "id": tc.id,
                    "type": "function",
                    "function": {"name": tc.function.name, "arguments": tc.function.arguments},
                }
                for tc in msg.tool_calls
            ]
        conversation.append(entry)

    def append_tool_results(
        self,
        conversation: list[Any],
        tool_uses: list[ToolUseRequest],
        results: list[dict[str, Any]],
        is_error: list[bool],
    ) -> None:
        for tu, r in zip(tool_uses, results):
            conversation.append(
                {
                    "role": "tool",
                    "tool_call_id": tu.id,
                    "content": json.dumps(r, default=str),
                }
            )

    def append_user_text(self, conversation: list[Any], text: str) -> None:
        conversation.append({"role": "user", "content": text})

    def initial_user_message(self, text: str) -> Any:
        return {"role": "user", "content": text}


# ---------------------- fallback wrapper ----------------------


class FallbackAdapter:
    """Per-turn fallback wrapper.

    Tries the primary adapter on the first call() of every turn; on a retryable
    failure it swaps to the secondary adapter for the rest of that turn. At the
    start of the next turn (detected by an empty `conversation` list) it resets
    to primary.

    Why per-turn and not mid-turn: Anthropic and OpenAI use different
    conversation shapes (Anthropic = content-block lists; OpenAI = flat
    messages with `tool_call` dicts). After the first successful call() the
    `conversation` list has been mutated by `append_assistant` /
    `append_tool_results` into one provider's format — feeding that to the
    other provider would 400. So fallback is only safe before any mutation
    has occurred (i.e. when `conversation` is empty). This still covers the
    failure mode this wrapper was added for: a billing/auth rejection from
    Anthropic that happens immediately on the first call of every turn.

    Concurrency contract (A.5):
      - `self._active` is per-INSTANCE state. The adapter must be constructed
        per turn, NOT cached at module level.
      - `app/agent/loop.py:run_turn` calls `get_adapter(settings)` on every
        invocation, which builds a fresh FallbackAdapter for that request.
        Two concurrent /v1/chat requests therefore have independent
        `_active` state and cannot race.
      - If you ever cache adapters at module/app level (e.g. into
        `app.state.adapter` for performance), swap `self._active` for a
        `contextvars.ContextVar` keyed per request. Otherwise physician A's
        Anthropic→OpenAI fallback decision will corrupt physician B's mid-turn
        provider, and the message-format invariant breaks.
    """

    def __init__(self, primary: LLMAdapter, secondary: LLMAdapter):
        import anthropic
        self._retryable = (
            anthropic.APIStatusError,
            anthropic.APIConnectionError,
            anthropic.APITimeoutError,
        )
        self._primary = primary
        self._secondary = secondary
        self._active = primary
        self.name = f"{primary.name}->{secondary.name}"

    async def call(
        self, system_prompt: str, tool_defs: list[dict], conversation: list[Any]
    ) -> ProviderResponse:
        if not conversation:
            self._active = self._primary  # new turn — try primary again
        try:
            return await self._active.call(system_prompt, tool_defs, conversation)
        except self._retryable as e:
            if self._active is self._secondary or conversation:
                # Already on secondary, OR mid-turn (format-incompatible swap).
                raise
            logger.warning(
                "LLM primary (%s) failed at turn start, falling back to %s: %s",
                self._primary.name,
                self._secondary.name,
                e,
            )
            self._active = self._secondary
            return await self._active.call(system_prompt, tool_defs, conversation)

    # Conversation-mutation methods delegate to whichever adapter served the
    # most recent successful call() for this turn, so the message list stays
    # in a self-consistent format.
    def append_assistant(self, conversation: list[Any], resp: ProviderResponse) -> None:
        return self._active.append_assistant(conversation, resp)

    def append_tool_results(
        self,
        conversation: list[Any],
        tool_uses: list[ToolUseRequest],
        results: list[dict[str, Any]],
        is_error: list[bool],
    ) -> None:
        return self._active.append_tool_results(conversation, tool_uses, results, is_error)

    def append_user_text(self, conversation: list[Any], text: str) -> None:
        return self._active.append_user_text(conversation, text)

    def initial_user_message(self, text: str) -> Any:
        return self._active.initial_user_message(text)


# ---------------------- factory ----------------------


def get_adapter(settings) -> LLMAdapter:
    if settings.llm_provider == "openai":
        if not settings.openai_api_key:
            raise RuntimeError("LLM_PROVIDER=openai but OPENAI_API_KEY is unset")
        return OpenAIAdapter(settings.openai_api_key, settings.openai_model)
    if not settings.anthropic_api_key:
        raise RuntimeError("LLM_PROVIDER=anthropic but ANTHROPIC_API_KEY is unset")
    primary = AnthropicAdapter(settings.anthropic_api_key, settings.anthropic_model)
    if settings.openai_api_key:
        secondary = OpenAIAdapter(settings.openai_api_key, settings.openai_model)
        return FallbackAdapter(primary, secondary)
    return primary
