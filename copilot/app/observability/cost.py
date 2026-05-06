"""Per-LLM-call USD cost helpers.

The pricing table covers the Anthropic + OpenAI models the Co-Pilot
actually uses. New models default to ``None`` (cost unknown) so the
caller's optional cost field stays None instead of fabricating a number.
Prices are USD per 1M tokens, mirroring vendor list prices as of late
2025; update when Anthropic / OpenAI publish a delta.

The helper is best-effort: it never raises, never logs PHI, and returns
None for anything it can't price.
"""
from __future__ import annotations

from dataclasses import dataclass


@dataclass(frozen=True)
class _PriceCents:
    input_per_m: float
    output_per_m: float
    cache_read_per_m: float = 0.0
    cache_write_per_m: float = 0.0


# All values in USD per 1M tokens.
_ANTHROPIC: dict[str, _PriceCents] = {
    "claude-sonnet-4-6": _PriceCents(
        input_per_m=3.00,
        output_per_m=15.00,
        cache_read_per_m=0.30,
        cache_write_per_m=3.75,
    ),
    "claude-opus-4-5": _PriceCents(
        input_per_m=15.00,
        output_per_m=75.00,
        cache_read_per_m=1.50,
        cache_write_per_m=18.75,
    ),
    "claude-haiku-4-5-20251001": _PriceCents(
        input_per_m=1.00,
        output_per_m=5.00,
        cache_read_per_m=0.10,
        cache_write_per_m=1.25,
    ),
}

_OPENAI: dict[str, _PriceCents] = {
    # Approximate placeholders — OpenAI fallback is rare in production.
    "gpt-4.1": _PriceCents(input_per_m=2.50, output_per_m=10.00),
    "gpt-4.1-mini": _PriceCents(input_per_m=0.15, output_per_m=0.60),
}


def estimate_anthropic_cost_usd(
    *,
    model_id: str,
    input_tokens: int = 0,
    output_tokens: int = 0,
    cache_read_tokens: int = 0,
    cache_write_tokens: int = 0,
) -> float | None:
    """Return USD cost estimate for an Anthropic call, or None when unknown."""
    p = _ANTHROPIC.get(model_id)
    if p is None:
        return None
    return (
        input_tokens       * p.input_per_m       / 1_000_000
        + output_tokens    * p.output_per_m      / 1_000_000
        + cache_read_tokens  * p.cache_read_per_m  / 1_000_000
        + cache_write_tokens * p.cache_write_per_m / 1_000_000
    )


def estimate_openai_cost_usd(
    *,
    model_id: str,
    input_tokens: int = 0,
    output_tokens: int = 0,
) -> float | None:
    p = _OPENAI.get(model_id)
    if p is None:
        return None
    return (
        input_tokens    * p.input_per_m  / 1_000_000
        + output_tokens * p.output_per_m / 1_000_000
    )


__all__ = ["estimate_anthropic_cost_usd", "estimate_openai_cost_usd"]
