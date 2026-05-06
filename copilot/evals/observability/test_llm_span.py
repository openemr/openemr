"""Verify the langfuse generation-span helper invokes correctly.

When credentials are unset (CI default), ``emit_generation`` no-ops and
must never raise. When the Langfuse module is patched, the helper should
call ``langfuse.generation`` with the right model + usage payload.
"""
from __future__ import annotations

from typing import Any



def test_emit_generation_noops_without_credentials(monkeypatch) -> None:
    monkeypatch.delenv("LANGFUSE_PUBLIC_KEY", raising=False)
    monkeypatch.delenv("LANGFUSE_SECRET_KEY", raising=False)
    # Reset the module-level cache.
    import app.observability.llm_span as mod
    monkeypatch.setattr(mod, "_CACHED_CLIENT", None)
    monkeypatch.setattr(mod, "_INIT_FAILED", False)

    # Should not raise.
    mod.emit_generation(
        provider="anthropic",
        model="claude-sonnet-4-6",
        prompt_tokens=100,
        completion_tokens=50,
    )


def test_emit_generation_invokes_langfuse_when_configured(monkeypatch) -> None:
    captured: dict[str, Any] = {}

    class _FakeClient:
        def generation(self, **kwargs: Any) -> None:
            captured["kwargs"] = kwargs

    import app.observability.llm_span as mod
    monkeypatch.setattr(mod, "_CACHED_CLIENT", _FakeClient())
    monkeypatch.setattr(mod, "_INIT_FAILED", False)

    mod.emit_generation(
        provider="anthropic",
        model="claude-sonnet-4-6",
        prompt_tokens=100,
        completion_tokens=50,
        cached_tokens=20,
        cache_write_tokens=5,
        latency_ms=1234.5,
        finish_reason="end_turn",
    )

    assert captured["kwargs"]["name"] == "anthropic.call"
    assert captured["kwargs"]["model"] == "claude-sonnet-4-6"
    assert captured["kwargs"]["usage"]["input"] == 100
    assert captured["kwargs"]["usage"]["output"] == 50
    assert captured["kwargs"]["usage"]["cache_read_input"] == 20
    assert captured["kwargs"]["metadata"]["provider"] == "anthropic"
    assert captured["kwargs"]["metadata"]["latency_ms"] == 1234.5
    assert captured["kwargs"]["metadata"]["finish_reason"] == "end_turn"


def test_emit_generation_swallows_langfuse_errors(monkeypatch) -> None:
    """A failing Langfuse call must NOT propagate to the request path."""
    class _ExplodingClient:
        def generation(self, **kwargs: Any) -> None:
            raise RuntimeError("network down")

    import app.observability.llm_span as mod
    monkeypatch.setattr(mod, "_CACHED_CLIENT", _ExplodingClient())
    monkeypatch.setattr(mod, "_INIT_FAILED", False)

    # Should not raise.
    mod.emit_generation(
        provider="openai",
        model="gpt-4.1-mini",
        prompt_tokens=10,
        completion_tokens=5,
    )
