"""Langfuse wrapper — emits one trace per agent turn.

When `LANGFUSE_PUBLIC_KEY` is unset, this becomes a no-op (logging only). The
agent doesn't gate on the trace succeeding; observability is fire-and-forget.

Per AUDIT §5.3, we never log raw PHI. Pseudonyms only. The clinical audit log
(separate write to OpenEMR's existing audit table) carries the user/patient/
time triple for HIPAA — Langfuse holds the technical trace.
"""
from __future__ import annotations

import logging
from typing import Any

from app.agent.schemas import TurnTrace
from app.config import Settings

logger = logging.getLogger("copilot.observability")


class _NoopTracer:
    def emit(self, trace: TurnTrace, response: dict[str, Any]) -> None:
        logger.info(
            "turn",
            extra={
                "session_id": trace.session_id,
                "user_id": trace.user_id,
                "patient_pseudonym": trace.patient_pseudonym,
                "tools": trace.tool_call_sequence,
                "verification_passed": trace.verification_passed,
                "rejected": trace.verification_rejections,
                "domain_rejections": trace.domain_rule_rejections,
                "tokens": {
                    "input": trace.tokens_input,
                    "output": trace.tokens_output,
                    "cached": trace.tokens_cached,
                },
                "latency_ms": trace.total_latency_ms,
            },
        )


class LangfuseTracer:
    def __init__(self, settings: Settings):
        from langfuse import Langfuse  # imported lazily so noop path stays light

        self._lf = Langfuse(
            public_key=settings.langfuse_public_key,
            secret_key=settings.langfuse_secret_key,
            host=settings.langfuse_host,
        )

    def emit(self, trace: TurnTrace, response: dict[str, Any]) -> None:
        try:
            t = self._lf.trace(
                name="agent_turn",
                user_id=trace.user_id,
                session_id=trace.session_id,
                metadata={
                    "patient_pseudonym": trace.patient_pseudonym,
                    "verification_passed": trace.verification_passed,
                    "verification_rejections": trace.verification_rejections,
                    "domain_rule_rejections": trace.domain_rule_rejections,
                    "tool_call_sequence": trace.tool_call_sequence,
                    "tool_failures": trace.tool_failures,
                    "tool_latencies_ms": trace.tool_latencies_ms,
                },
                input={"question": trace.question_text},
                output=response,
            )
            t.update(
                usage={
                    "input": trace.tokens_input,
                    "output": trace.tokens_output,
                    "cache_read_input": trace.tokens_cached,
                }
            )
            self._lf.flush()
        except Exception as e:  # noqa: BLE001 — observability must never raise
            logger.warning("langfuse emit failed: %s", e)


def get_tracer(settings: Settings) -> _NoopTracer | LangfuseTracer:
    if settings.langfuse_public_key and settings.langfuse_secret_key:
        try:
            return LangfuseTracer(settings)
        except Exception as e:  # noqa: BLE001
            logger.warning("Langfuse init failed, falling back to noop: %s", e)
    return _NoopTracer()
