"""Unit tests for the per-LLM-call USD cost helper."""
from __future__ import annotations

from app.observability.cost import estimate_anthropic_cost_usd, estimate_openai_cost_usd


def test_anthropic_sonnet_cost_with_known_token_counts() -> None:
    # Sonnet 4.6 pricing: input $3/M, output $15/M.
    # 1M input + 1M output = 3 + 15 = $18.
    cost = estimate_anthropic_cost_usd(
        model_id="claude-sonnet-4-6",
        input_tokens=1_000_000,
        output_tokens=1_000_000,
    )
    assert cost is not None
    assert abs(cost - 18.0) < 1e-6


def test_anthropic_cache_hit_pricing_is_cheaper_than_input() -> None:
    """A cache_read_token must cost less than a fresh input token."""
    fresh = estimate_anthropic_cost_usd(
        model_id="claude-sonnet-4-6",
        input_tokens=1_000_000,
    )
    cached = estimate_anthropic_cost_usd(
        model_id="claude-sonnet-4-6",
        cache_read_tokens=1_000_000,
    )
    assert fresh is not None and cached is not None
    assert cached < fresh


def test_unknown_model_returns_none() -> None:
    assert estimate_anthropic_cost_usd(
        model_id="claude-not-a-real-model",
        input_tokens=100_000,
    ) is None


def test_openai_pricing_is_distinct_from_anthropic() -> None:
    cost = estimate_openai_cost_usd(model_id="gpt-4.1-mini", input_tokens=1_000_000)
    assert cost is not None
    # gpt-4.1-mini is configured as 0.15/M input.
    assert abs(cost - 0.15) < 1e-6


def test_zero_tokens_returns_zero_not_none_for_known_model() -> None:
    cost = estimate_anthropic_cost_usd(
        model_id="claude-sonnet-4-6",
        input_tokens=0,
        output_tokens=0,
    )
    assert cost == 0.0
