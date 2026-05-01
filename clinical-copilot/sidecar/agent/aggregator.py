"""Aggregator + ranker.

Sorts pair-judge results by likelihood_pct / inconsistency_pct, groups by
candidate (Pair A) or deduplicates the unordered pair (Pair B), and
partitions candidates into highlight (>=70), show (>=30), less-likely
(<30) tiers. ALL surviving candidates are kept in the response — the UI
decides which tier to expand.

Drops are recorded for observability (ARCHITECTURE.md §4.1) and surfaced
to the operator via the audit log.
"""

from __future__ import annotations

from collections import Counter
from collections.abc import Iterable
from dataclasses import dataclass, field

from sidecar.snapshot import Provenance

from .pair_judge import JudgmentA, JudgmentB

# When this many or more dropped pairs share the same error category, we
# prepend a single banner-style summary instead of forcing the operator to
# read 24 near-identical lines. Threshold deliberately low — two pairs
# failing the same way is already evidence the cause is global, not pair-
# specific.
_COMMON_CAUSE_THRESHOLD = 3

# Thresholds. Tunable via env later if needed.
HIGHLIGHT_PCT = 70  # red/amber accent
SHOW_PCT = 30       # full card
# Anything below SHOW_PCT goes into the collapsed less-likely tail.

# Pair B: any inconsistency_pct ≥ this is flagged.
FLAG_PCT = 50


@dataclass(frozen=True)
class PerSymptomScore:
    """One (symptom × candidate) score row used by per-symptom view."""

    symptom: str
    likelihood_pct: int
    rationale: str
    differentiating_test: str | None


@dataclass(frozen=True)
class Candidate:
    """One candidate datapoint ranked across all the patient's symptoms."""

    label: str
    kind: str
    max_likelihood_pct: int          # max across symptoms
    rationale: str                   # rationale of the max-likelihood pair
    differentiating_test: str | None
    provenance: Provenance
    per_symptom: list[PerSymptomScore]
    tier: str  # "highlight" | "show" | "less_likely"


@dataclass(frozen=True)
class ChartErrorFlag:
    """One chart-error flag."""

    label_a: str
    kind_a: str
    label_b: str
    kind_b: str
    inconsistency_pct: int
    inconsistency_kind: str  # "biological" | "temporal" | "pharmacological" | "none"
    rationale: str
    suggested_clarification: str | None
    provenance_a: Provenance
    provenance_b: Provenance


@dataclass(frozen=True)
class AggregatedResult:
    """Full ranked output. Frontend partitions by tier."""

    candidates_a: list[Candidate] = field(default_factory=list)
    flags_b: list[ChartErrorFlag] = field(default_factory=list)
    dropped: list[str] = field(default_factory=list)
    total_pair_count: int = 0
    total_dollar_cost: float = 0.0
    total_prompt_tokens: int = 0
    total_completion_tokens: int = 0


def _tier_for(pct: int) -> str:
    if pct >= HIGHLIGHT_PCT:
        return "highlight"
    if pct >= SHOW_PCT:
        return "show"
    return "less_likely"


def _common_cause_banner(judgments: Iterable[JudgmentA | JudgmentB]) -> str | None:
    """Return a single banner line if ≥ N drops share the same root cause.

    Categories come from ``error_diagnostics.diagnose_openai_error`` and
    are stable strings like ``"connect_dns_failure"`` or ``"auth_invalid"``.
    Pair-judge calls that succeeded carry ``error_category=None`` so they
    naturally drop out of the count.

    The banner pulls the hint from the first matching judgment so the
    actionable wording is consistent with the per-pair lines underneath.
    """
    counts: Counter[str] = Counter()
    hint_by_category: dict[str, str] = {}
    for j in judgments:
        cat = getattr(j, "error_category", None)
        if not cat:
            continue
        counts[cat] += 1
        if cat not in hint_by_category:
            hint = getattr(j, "error_hint", None)
            if hint:
                hint_by_category[cat] = hint
    if not counts:
        return None
    category, count = counts.most_common(1)[0]
    if count < _COMMON_CAUSE_THRESHOLD:
        return None
    hint = hint_by_category.get(category, "(no diagnosis hint available)")
    return (
        f"⚠ {count} pairs failed with the same root cause "
        f"[{category}]. Hint: {hint}"
    )


def aggregate_pair_a(judgments: list[JudgmentA], *, top_n: int | None = None) -> AggregatedResult:
    """Group Pair A judgments by candidate, keep ALL, sort by max likelihood."""
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
        by_candidate.setdefault(j.pair.candidate_label, []).append(j)

    candidates: list[Candidate] = []
    for label, group in by_candidate.items():
        best = max(group, key=lambda j: j.result.likelihood_pct)  # type: ignore[union-attr]
        assert best.result is not None
        per_symptom = []
        for jj in group:
            assert jj.result is not None
            per_symptom.append(PerSymptomScore(
                symptom=jj.pair.symptom,
                likelihood_pct=jj.result.likelihood_pct,
                rationale=jj.result.rationale,
                differentiating_test=jj.result.differentiating_test,
            ))
        per_symptom.sort(key=lambda s: s.likelihood_pct, reverse=True)
        max_pct = best.result.likelihood_pct
        candidates.append(Candidate(
            label=label,
            kind=best.pair.candidate_kind,
            max_likelihood_pct=max_pct,
            rationale=best.result.rationale,
            differentiating_test=best.result.differentiating_test,
            provenance=best.pair.candidate_provenance,
            per_symptom=per_symptom,
            tier=_tier_for(max_pct),
        ))
    candidates.sort(key=lambda c: c.max_likelihood_pct, reverse=True)
    if top_n is not None:
        candidates = candidates[:top_n]
    # Prepend a banner if N+ pairs failed with the same root cause — that
    # way the UI's "show reasons" section opens to a single actionable
    # line instead of 24 redundant ones.
    banner = _common_cause_banner(judgments)
    if banner is not None:
        dropped = [banner, *dropped]
    return AggregatedResult(
        candidates_a=candidates,
        dropped=dropped,
        total_pair_count=len(judgments),
        total_dollar_cost=cost,
        total_prompt_tokens=pt,
        total_completion_tokens=ct,
    )


def aggregate_pair_b(judgments: list[JudgmentB], *, top_n: int | None = None) -> AggregatedResult:
    """Sort Pair B judgments by inconsistency_pct, drop pct < FLAG_PCT.

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
        if j.result.inconsistency_pct < FLAG_PCT:
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
        flags.append(ChartErrorFlag(
            label_a=j.pair.label_a,
            kind_a=j.pair.kind_a,
            label_b=j.pair.label_b,
            kind_b=j.pair.kind_b,
            inconsistency_pct=j.result.inconsistency_pct,
            inconsistency_kind=j.result.kind,
            rationale=j.result.rationale,
            suggested_clarification=j.result.suggested_clarification,
            provenance_a=j.pair.provenance_a,
            provenance_b=j.pair.provenance_b,
        ))
    flags.sort(key=lambda f: f.inconsistency_pct, reverse=True)
    if top_n is not None:
        flags = flags[:top_n]
    banner = _common_cause_banner(judgments)
    if banner is not None:
        dropped = [banner, *dropped]
    return AggregatedResult(
        flags_b=flags,
        dropped=dropped,
        total_pair_count=len(judgments),
        total_dollar_cost=cost,
        total_prompt_tokens=pt,
        total_completion_tokens=ct,
    )
