"""LangGraph agent: pairwise comparison engine + verifier wiring.

The architecture's distinctive engine. Stage 0 found that holistic prompting
reproduces the same anchoring failures human clinicians make, while
structured pairwise comparison surfaces answers already in the chart.
Use Cases A (pre-visit cross-check) and B (chart-error scan) are the same
engine with different prompts.
"""

from .aggregator import AggregatedResult, Candidate, aggregate_pair_a, aggregate_pair_b
from .pair_generator import PairA, PairB, generate_pairs_a, generate_pairs_b
from .pair_judge import JudgeProvider, JudgeResultA, JudgeResultB, MockProvider, OpenAIProvider

__all__ = [
    "AggregatedResult",
    "Candidate",
    "JudgeProvider",
    "JudgeResultA",
    "JudgeResultB",
    "MockProvider",
    "OpenAIProvider",
    "PairA",
    "PairB",
    "aggregate_pair_a",
    "aggregate_pair_b",
    "generate_pairs_a",
    "generate_pairs_b",
]
