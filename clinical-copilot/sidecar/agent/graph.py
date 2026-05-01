"""LangGraph wiring for the multi-turn agent.

Pipeline: snapshot → pair gen → pair judge → aggregate → verify (verdict
only, rule-store annotations no longer surface to the user) → wrap. The
front-end receives ALL aggregated candidates with their tier so it can
render highlight/show/less-likely sections without a second round-trip.
"""

from __future__ import annotations

from dataclasses import dataclass, field
from typing import Literal

from sidecar.audit import AuditEntry, InMemoryAuditLog
from sidecar.audit.log import make_redacted_summary, now_utc
from sidecar.config import Settings
from sidecar.observability import span
from sidecar.snapshot import PatientSnapshot
from sidecar.verifier import Verifier, VerificationOutcome

from .aggregator import AggregatedResult, aggregate_pair_a, aggregate_pair_b
from .pair_generator import generate_pairs_a, generate_pairs_b
from .pair_judge import JudgeProvider, MockProvider, OpenAIProvider
from .prompts import PROMPT_VERSION_CONVERSATIONAL


@dataclass
class AgentResponse:
    """The final user-visible response."""

    text: str
    verdict: str
    candidates: list[dict[str, object]] = field(default_factory=list)
    chart_error_flags: list[dict[str, object]] = field(default_factory=list)
    data_gaps: list[str] = field(default_factory=list)
    dropped: list[str] = field(default_factory=list)
    telemetry: dict[str, object] = field(default_factory=dict)


def make_provider(settings: Settings, *, force_mock: bool = False) -> JudgeProvider:
    """Construct the provider configured by ``COPILOT_LLM_PROVIDER``.

    ``force_mock=True`` bypasses the configured provider and uses the
    deterministic mock — used by ``/chat?mock=1`` when
    ``COPILOT_ALLOW_MOCK`` is enabled.
    """
    if force_mock:
        return MockProvider()
    provider = settings.llm_provider
    if provider == "openai":
        return OpenAIProvider(settings)
    if provider == "azure":
        return OpenAIProvider(settings)
    return MockProvider()


@dataclass
class GraphConfig:
    purpose: Literal["diagnostic_cross_check", "chart_error_scan", "follow_up_question"]
    user_id: str
    settings: Settings
    audit_log: InMemoryAuditLog
    provider: JudgeProvider | None = None
    verifier: Verifier | None = None
    top_n_a: int | None = None  # None = keep all (UI partitions by tier)
    top_n_b: int | None = None


# ─── Graph nodes ───────────────────────────────────────────────────────────


async def node_pair_judge_a(snapshot: PatientSnapshot, cfg: GraphConfig) -> tuple[AggregatedResult, list]:
    pairs = generate_pairs_a(snapshot, max_pairs=cfg.settings.pair_judge_max_pairs)
    with span("pair_judge_a", pair_count=len(pairs), model=getattr(cfg.provider, "model_name", "?")):
        provider = cfg.provider or make_provider(cfg.settings)
        judgments = await provider.judge_many_a(
            pairs, max_concurrency=cfg.settings.pair_judge_max_concurrency
        )
    aggregated = aggregate_pair_a(judgments, top_n=cfg.top_n_a)
    return aggregated, judgments


async def node_pair_judge_b(snapshot: PatientSnapshot, cfg: GraphConfig) -> tuple[AggregatedResult, list]:
    pairs = generate_pairs_b(snapshot, max_pairs=cfg.settings.pair_judge_max_pairs)
    with span("pair_judge_b", pair_count=len(pairs), model=getattr(cfg.provider, "model_name", "?")):
        provider = cfg.provider or make_provider(cfg.settings)
        judgments = await provider.judge_many_b(
            pairs, max_concurrency=cfg.settings.pair_judge_max_concurrency
        )
    aggregated = aggregate_pair_b(judgments, top_n=cfg.top_n_b)
    return aggregated, judgments


def node_verify(snapshot: PatientSnapshot, aggregated: AggregatedResult, cfg: GraphConfig) -> VerificationOutcome:
    """Run the verifier for the verdict + data_gaps only.

    Per the v2 design decision (user sign-off), curated rule-store
    annotations no longer surface in the runtime response — they remain
    eval-only expectations. The verifier still computes the verdict and
    surfaces data gaps because both are derived from snapshot quality
    flags rather than authored prose.
    """
    verifier = cfg.verifier
    if verifier is None:
        from sidecar.verifier import load_default_rule_store
        verifier = Verifier(load_default_rule_store())
    candidate_provenance = [
        (c.label, c.provenance.table, str(c.provenance.row_id))
        for c in aggregated.candidates_a
    ]
    flag_provenance = [
        (f.label_a, f.provenance_a.table, str(f.provenance_a.row_id),
         f.label_b, f.provenance_b.table)
        for f in aggregated.flags_b
    ]
    with span("verifier"):
        return verifier.verify(
            snapshot,
            candidate_provenance=candidate_provenance,
            flag_provenance=flag_provenance,
        )


def node_response(aggregated: AggregatedResult, outcome: VerificationOutcome, cfg: GraphConfig) -> AgentResponse:
    """Assemble the response payload. No prose wrap — the UI renders cards."""
    return AgentResponse(
        text="",  # legacy — UI renders cards directly from candidates / flags
        verdict=outcome.verdict.value,
        candidates=[_candidate_to_dict(c) for c in aggregated.candidates_a],
        chart_error_flags=[_flag_to_dict(f) for f in aggregated.flags_b],
        data_gaps=outcome.data_gaps,
        dropped=aggregated.dropped,
        telemetry={
            "total_pair_count": aggregated.total_pair_count,
            "total_dollar_cost": aggregated.total_dollar_cost,
            "total_prompt_tokens": aggregated.total_prompt_tokens,
            "total_completion_tokens": aggregated.total_completion_tokens,
        },
    )


def _candidate_to_dict(c) -> dict[str, object]:
    return {
        "label": c.label,
        "kind": c.kind,
        "max_likelihood_pct": c.max_likelihood_pct,
        "rationale": c.rationale,
        "differentiating_test": c.differentiating_test,
        "tier": c.tier,
        "per_symptom": [
            {
                "symptom": s.symptom,
                "likelihood_pct": s.likelihood_pct,
                "rationale": s.rationale,
                "differentiating_test": s.differentiating_test,
            }
            for s in c.per_symptom
        ],
        "provenance": {
            "table": c.provenance.table,
            "row_id": str(c.provenance.row_id),
            "fhir_resource": c.provenance.fhir_resource,
        },
    }


def _flag_to_dict(f) -> dict[str, object]:
    return {
        "label_a": f.label_a,
        "kind_a": f.kind_a,
        "label_b": f.label_b,
        "kind_b": f.kind_b,
        "inconsistency_pct": f.inconsistency_pct,
        "inconsistency_kind": f.inconsistency_kind,
        "rationale": f.rationale,
        "suggested_clarification": f.suggested_clarification,
        "provenance_a": {"table": f.provenance_a.table, "row_id": str(f.provenance_a.row_id)},
        "provenance_b": {"table": f.provenance_b.table, "row_id": str(f.provenance_b.row_id)},
    }


# ─── Orchestrator ──────────────────────────────────────────────────────────


async def run_graph(snapshot: PatientSnapshot, cfg: GraphConfig) -> AgentResponse:
    """Run the full graph for one task. Writes one audit row at the end."""
    if cfg.purpose == "chart_error_scan":
        aggregated, _judgments = await node_pair_judge_b(snapshot, cfg)
    else:
        aggregated, _judgments = await node_pair_judge_a(snapshot, cfg)

    outcome = node_verify(snapshot, aggregated, cfg)
    response = node_response(aggregated, outcome, cfg)

    summary_labels = (
        [c.label for c in aggregated.candidates_a]
        if cfg.purpose != "chart_error_scan"
        else [f"{f.label_a}|{f.label_b}" for f in aggregated.flags_b]
    )
    cfg.audit_log.append(
        AuditEntry(
            occurred_at=now_utc(),
            user_id=cfg.user_id,
            patient_id=snapshot.patient_id,
            purpose_of_use=cfg.purpose,
            model_name=getattr(cfg.provider, "model_name", "unknown"),
            prompt_version=PROMPT_VERSION_CONVERSATIONAL,
            prompt_token_count=aggregated.total_prompt_tokens,
            completion_token_count=aggregated.total_completion_tokens,
            tool_calls=[
                {"tool": "snapshot.fetch", "status": "ok"},
                {"tool": "pair_judge", "status": "ok", "pair_count": aggregated.total_pair_count},
                {"tool": "verifier", "status": outcome.verdict.value},
            ],
            verifier_outcome=outcome.verdict.value,
            response_summary=make_redacted_summary(summary_labels, outcome.verdict.value),
        )
    )
    return response
