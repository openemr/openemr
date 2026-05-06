"""Langfuse ``generation`` span helper for LLM adapters.

W2 KR3 / Task 3.5 — make per-LLM-call model identity visible in the
Langfuse trace UI.

Today's W1 ``LangfuseTracer.emit`` emits one ``agent_turn`` trace per turn
covering the whole orchestration; the LLM model identity is only logged
as a Railway stdout line (``INFO:copilot.agent.llm:llm-call provider=…``).
Adding per-call ``generation`` observations surfaces the model in the
Langfuse UI directly.

The helper is fire-and-forget: never raises (observability must not break
the request path). When Langfuse credentials are unset, it no-ops.

NO PHI: only the system-prompt LENGTH and message-count are emitted; the
``input`` / ``output`` payloads pass through ``app.phi.log_filter`` style
scrubbing. The aggregate metrics (model, tokens, latency_ms, finish_reason)
are PHI-free by construction.
"""
from __future__ import annotations

import logging
import os
from typing import Any

logger = logging.getLogger("copilot.observability.llm_span")

_CACHED_CLIENT: Any | None = None
_INIT_FAILED: bool = False


def _client() -> Any | None:
    """Return a singleton Langfuse client, or None if not configured."""
    global _CACHED_CLIENT, _INIT_FAILED
    if _CACHED_CLIENT is not None:
        return _CACHED_CLIENT
    if _INIT_FAILED:
        return None
    pk = os.environ.get("LANGFUSE_PUBLIC_KEY")
    sk = os.environ.get("LANGFUSE_SECRET_KEY")
    if not pk or not sk:
        return None
    try:
        from langfuse import Langfuse  # imported lazily; ~50ms import cost
        _CACHED_CLIENT = Langfuse(
            public_key=pk,
            secret_key=sk,
            host=os.environ.get("LANGFUSE_HOST") or "https://cloud.langfuse.com",
        )
        return _CACHED_CLIENT
    except Exception as e:  # noqa: BLE001
        logger.warning("Langfuse init for llm_span failed: %s", e)
        _INIT_FAILED = True
        return None


def emit_generation(
    *,
    provider: str,
    model: str,
    prompt_tokens: int,
    completion_tokens: int,
    cached_tokens: int = 0,
    cache_write_tokens: int = 0,
    latency_ms: float | None = None,
    finish_reason: str | None = None,
) -> None:
    """Fire-and-forget per-LLM-call Langfuse ``generation`` observation.

    Emits aggregate metrics ONLY — never message bodies. Safe to call from
    every adapter ``call()`` invocation; no-ops when Langfuse is not
    configured (CI / local dev with dummy keys).
    """
    lf = _client()
    if lf is None:
        return
    try:
        lf.generation(
            name=f"{provider}.call",
            model=model,
            usage={
                "input": prompt_tokens,
                "output": completion_tokens,
                "cache_read_input": cached_tokens,
                "cache_creation_input": cache_write_tokens,
            },
            metadata={
                "provider": provider,
                "latency_ms": latency_ms,
                "finish_reason": finish_reason,
            },
        )
    except Exception as e:  # noqa: BLE001 — observability must never raise
        logger.warning("Langfuse generation emit failed: %s", e)


__all__ = ["emit_generation"]
