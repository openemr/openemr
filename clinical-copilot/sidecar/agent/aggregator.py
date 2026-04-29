"""Aggregator + ranker.

Sorts pair-judge results by likelihood / confidence, deduplicates by
candidate, and emits the top N. Drops are recorded for observability
(ARCHITECTURE.md §4.1).
"""

from __future__ import annotations

from dataclasses import dataclass, field

from sidecar.snapshot import Provenance

from .pair_judge import JudgeResultA, JudgeResultB, JudgmentA, JudgmentB

LIKELIHOOD_RANK = {"high": 3, "moderate": 2, "low": 1}


@dataclass(frozen=True)
class Candidate:
    """One candidate explanation (Use Case A) ranked for the clinician."""

    label: str
    kind: str
    likelihood: str
    likelihood_rank: int
    mechanism: str
    differentiating_test: str | None
    provenance: Provenance
    pair_count: int  # how many symptoms this candidate explained


@dataclass(frozen=True)
class ChartErrorFlag:
    """One chart-error flag (Use Case B)."""

    label_a: str
    label_b: str
    inconsistency: str
    confidence: float
    rule_cited: str | None
    suggested_clarification: str | None
    provenance_a: Provenance
    provenance_b: Provenance


@dataclass(frozen=True)
class AggregatedResult:
    """Top-ranked candidates (A) or flags (B), plus drops + telemetry."""

    candidates_a: list[Candidate] = field(default_factory=list)
    flags_b: list[ChartErrorFlag] = field(default_factory=list)
    dropped: list[str] = field(default_factory=list)
    total_pair_count: int = 0
    total_dollar_cost: float = 0.0
    total_prompt_tokens: int = 0
    total_completion_tokens: int = 0


def aggregate_pair_a(judgments: list[JudgmentA], *, top_n: int = 3) -> AggregatedResult:
    """Group Use Case A judgments by candidate, rank by likelihood."""
    by_candidate: dict[str, list[JudgmentA]] = {}
    dropped: list[str] = []
    cost = 0.0
    pt = 0
    ct = 0
    for j in judgments:
        cost += j.telemetry.dollar_cost
        pt += j.telemetry.prompt_tokens
        ct += j.telemetry.completion_tokens
        if j.result is None:
            dropped.append(
                f"({j.pair.symptom!r} × {j.pair.candidate_label!r}): {j.error or 'no result'}"
            )
            continue
        if not j.result.supporting_chart_evidence:
            dropped.append(
                f"({j.pair.symptom!r} × {j.pair.candidate_label!r}): no evidence row"
            )
            continue
        if j.result.likelihood == "low":
            continue
        by_candidate.setdefault(j.pair.candidate_label, []).append(j)

    candidates: list[Candidate] = []
    for label, group in by_candidate.items():
        # Take the highest-likelihood judgment for this candidate as the
        # rank, but keep the count of symptoms the candidate explains.
        best = max(group, key=lambda j: LIKELIHOOD_RANK[j.result.likelihood])  # type: ignore[union-attr]
        assert best.result is not None  # guarded above
        candidates.append(
            Candidate(
                label=label,
                kind=best.pair.candidate_kind,
                likelihood=best.result.likelihood,
                likelihood_rank=LIKELIHOOD_RANK[best.result.likelihood],
                mechanism=best.result.mechanism,
                differentiating_test=best.result.differentiating_test,
                provenance=best.pair.candidate_provenance,
                pair_count=len(group),
            )
        )
    candidates.sort(key=lambda c: (c.likelihood_rank, c.pair_count), reverse=True)
    return AggregatedResult(
        candidates_a=candidates[:top_n],
        dropped=dropped,
        total_pair_count=len(judgments),
        total_dollar_cost=cost,
        total_prompt_tokens=pt,
        total_completion_tokens=ct,
    )


def aggregate_pair_b(judgments: list[JudgmentB], *, top_n: int = 5) -> AggregatedResult:
    """Sort Use Case B judgments by confidence, drop ``inconsistency=none``.

    Deduplicates the unordered pair {A, B} (we generate ordered pairs but
    surface each chart error once).
    """
    seen: set[frozenset[str]] = set()
    flags: list[ChartErrorFlag] = []
    dropped: list[str] = []
    cost = 0.0
    pt = 0
    ct = 0
    for j in judgments:
        cost += j.telemetry.dollar_cost
        pt += j.telemetry.prompt_tokens
        ct += j.telemetry.completion_tokens
        if j.result is None:
            dropped.append(
                f"({j.pair.label_a!r} × {j.pair.label_b!r}): {j.error or 'no result'}"
            )
            continue
        if j.result.inconsistency == "none":
            continue
        if not j.result.evidence:
            dropped.append(
                f"({j.pair.label_a!r} × {j.pair.label_b!r}): no evidence row"
            )
            continue
        key = frozenset({j.pair.label_a.lower(), j.pair.label_b.lower()})
        if key in seen:
            continue
        seen.add(key)
        flags.append(
            ChartErrorFlag(
                label_a=j.pair.label_a,
                label_b=j.pair.label_b,
                inconsistency=j.result.inconsistency,
                confidence=j.result.confidence,
                rule_cited=j.result.rule_cited,
                suggested_clarification=j.result.suggested_clarification,
                provenance_a=j.pair.provenance_a,
                provenance_b=j.pair.provenance_b,
            )
        )
    flags.sort(key=lambda f: f.confidence, reverse=True)
    return AggregatedResult(
        flags_b=flags[:top_n],
        dropped=dropped,
        total_pair_count=len(judgments),
        total_dollar_cost=cost,
        total_prompt_tokens=pt,
        total_completion_tokens=ct,
    )
