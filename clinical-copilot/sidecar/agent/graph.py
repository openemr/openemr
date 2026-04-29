"""LangGraph wiring for the multi-turn agent.

We avoid a hard dependency on ``langgraph`` itself: the package is
optional in CI. The graph is a simple async pipeline of pure functions
(snapshot → pair gen → pair judge → aggregate → verify → wrap), which
maps onto a LangGraph ``StateGraph`` directly when the optional extra is
installed. Multi-turn state is a plain dict here; LangGraph's checkpointer
can persist it to Postgres in production (ARCHITECTURE.md §4.2).
"""

from __future__ import annotations

from dataclasses import dataclass, field
from datetime import datetime, timezone
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
    annotations: list[str] = field(default_factory=list)
    data_gaps: list[str] = field(default_factory=list)
    dropped: list[str] = field(default_factory=list)
    telemetry: dict[str, object] = field(default_factory=dict)


def make_provider(settings: Settings) -> JudgeProvider:
    """Construct the provider configured by ``COPILOT_LLM_PROVIDER``."""
    match settings.llm_provider:
        case "openai":
            return OpenAIProvider(settings)
        case "azure":
            # Azure uses the OpenAI client with an Azure-shaped base_url.
            # See ARCHITECTURE.md §1.3 fallback row.
            return OpenAIProvider(settings)
        case "mock":
            return MockProvider()
        case _:
            return MockProvider()


@dataclass
class GraphConfig:
    purpose: Literal["diagnostic_cross_check", "chart_error_scan", "follow_up_question"]
    user_id: str
    settings: Settings
    audit_log: InMemoryAuditLog
    provider: JudgeProvider | None = None
    verifier: Verifier | None = None
    top_n_a: int = 3
    top_n_b: int = 5


# ─── Graph nodes (each one is a pure async function) ────────────────────────


async def node_pair_judge_a(
    snapshot: PatientSnapshot, cfg: GraphConfig
) -> tuple[AggregatedResult, list]:
    pairs = generate_pairs_a(snapshot, max_pairs=cfg.settings.pair_judge_max_pairs)
    with span("pair_judge_a", pair_count=len(pairs), model=getattr(cfg.provider, "model_name", "?")):
        provider = cfg.provider or make_provider(cfg.settings)
        judgments = await provider.judge_many_a(
            pairs, max_concurrency=cfg.settings.pair_judge_max_concurrency
        )
    aggregated = aggregate_pair_a(judgments, top_n=cfg.top_n_a)
    return aggregated, judgments


async def node_pair_judge_b(
    snapshot: PatientSnapshot, cfg: GraphConfig
) -> tuple[AggregatedResult, list]:
    pairs = generate_pairs_b(snapshot, max_pairs=cfg.settings.pair_judge_max_pairs)
    with span("pair_judge_b", pair_count=len(pairs), model=getattr(cfg.provider, "model_name", "?")):
        provider = cfg.provider or make_provider(cfg.settings)
        judgments = await provider.judge_many_b(
            pairs, max_concurrency=cfg.settings.pair_judge_max_concurrency
        )
    aggregated = aggregate_pair_b(judgments, top_n=cfg.top_n_b)
    return aggregated, judgments


def node_verify(
    snapshot: PatientSnapshot, aggregated: AggregatedResult, cfg: GraphConfig
) -> VerificationOutcome:
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


def node_conversational_wrap(
    aggregated: AggregatedResult, outcome: VerificationOutcome, cfg: GraphConfig
) -> AgentResponse:
    """Render the clinician-facing response.

    Deterministic templated rendering (no LLM call). The architecture
    allows an LLM wrap (§4.1) but a templated wrap is safer for a demo:
    every claim is already verified, so we just stitch them together.
    """
    lines: list[str] = []
    if cfg.purpose == "diagnostic_cross_check":
        if not aggregated.candidates_a:
            lines.append(
                "No candidate from the chart explains the presenting symptoms with sufficient evidence."
            )
        else:
            lines.append("Top candidate explanations from the chart:")
            for i, c in enumerate(aggregated.candidates_a, start=1):
                cite = f"{c.provenance.table}#{c.provenance.row_id}"
                test = (
                    f" — differentiating test: {c.differentiating_test}"
                    if c.differentiating_test
                    else ""
                )
                lines.append(
                    f"  {i}. {c.label} ({c.likelihood}) — {c.mechanism} [source: {cite}]{test}"
                )
    elif cfg.purpose == "chart_error_scan":
        if not aggregated.flags_b:
            lines.append("Chart-error scan: no inconsistencies surfaced above the confidence threshold.")
        else:
            lines.append("Likely chart errors flagged for review:")
            for i, f in enumerate(aggregated.flags_b, start=1):
                cite_a = f"{f.provenance_a.table}#{f.provenance_a.row_id}"
                cite_b = f"{f.provenance_b.table}#{f.provenance_b.row_id}"
                lines.append(
                    f"  {i}. {f.label_a} × {f.label_b} ({f.inconsistency}, "
                    f"confidence {f.confidence:.2f}) — {f.rule_cited or 'no rule cited'} "
                    f"[sources: {cite_a}, {cite_b}]"
                )
                if f.suggested_clarification:
                    lines.append(f"     Suggested clarification: {f.suggested_clarification}")
    else:
        lines.append("(follow-up handler not yet wired)")

    if outcome.data_gaps:
        lines.append("\nData gaps:")
        for g in outcome.data_gaps:
            lines.append(f"  - {g}")
    if outcome.annotations:
        lines.append("\nNotes from the rule store:")
        for a in outcome.annotations:
            lines.append(f"  - {a}")

    return AgentResponse(
        text="\n".join(lines),
        verdict=outcome.verdict.value,
        candidates=[_candidate_to_dict(c) for c in aggregated.candidates_a],
        chart_error_flags=[_flag_to_dict(f) for f in aggregated.flags_b],
        annotations=outcome.annotations,
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
        "likelihood": c.likelihood,
        "mechanism": c.mechanism,
        "differentiating_test": c.differentiating_test,
        "provenance": {
            "table": c.provenance.table,
            "row_id": str(c.provenance.row_id),
            "fhir_resource": c.provenance.fhir_resource,
        },
        "pair_count": c.pair_count,
    }


def _flag_to_dict(f) -> dict[str, object]:
    return {
        "label_a": f.label_a,
        "label_b": f.label_b,
        "inconsistency": f.inconsistency,
        "confidence": f.confidence,
        "rule_cited": f.rule_cited,
        "suggested_clarification": f.suggested_clarification,
        "provenance_a": {
            "table": f.provenance_a.table,
            "row_id": str(f.provenance_a.row_id),
        },
        "provenance_b": {
            "table": f.provenance_b.table,
            "row_id": str(f.provenance_b.row_id),
        },
    }


# ─── Orchestrator ──────────────────────────────────────────────────────────


async def run_graph(
    snapshot: PatientSnapshot, cfg: GraphConfig
) -> AgentResponse:
    """Run the full graph for one task. Writes one audit row at the end."""
    if cfg.purpose == "chart_error_scan":
        aggregated, _judgments = await node_pair_judge_b(snapshot, cfg)
    else:
        aggregated, _judgments = await node_pair_judge_a(snapshot, cfg)

    outcome = node_verify(snapshot, aggregated, cfg)
    response = node_conversational_wrap(aggregated, outcome, cfg)

    # Write the audit row (ARCHITECTURE.md §6.3).
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
                {"tool": "pair_judge", "status": "ok",
                 "pair_count": aggregated.total_pair_count},
                {"tool": "verifier", "status": outcome.verdict.value},
            ],
            verifier_outcome=outcome.verdict.value,
            response_summary=make_redacted_summary(summary_labels, outcome.verdict.value),
        )
    )
    return response
