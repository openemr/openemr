"""Pair Judge: structured-output LLM evaluation of one pair.

Two providers ship: ``OpenAIProvider`` for production (BAA + ZDR) and
``MockProvider`` for offline / CI runs. The mock is **deterministic**:
same input → same output, with plausible likelihood_pct values for the
seed cases from ARCHITECTURE.md §7.1.

The judge dispatches in parallel batches with a concurrency cap
(``Settings.pair_judge_max_concurrency``) per ARCHITECTURE.md §4.1.

Output schema is a calibrated probability (0–100), not an enum, so the
aggregator can partition into highlight / show / less-likely tiers.
"""

from __future__ import annotations

import asyncio
import json
import logging
import os
import time
from abc import ABC, abstractmethod
from dataclasses import dataclass
from typing import Any, Callable, Literal

from pydantic import BaseModel, Field, ValidationError

from sidecar.config import Settings

from .error_diagnostics import ErrorDiagnosis, diagnose_openai_error
from .pair_generator import PairA, PairB
from .prompts import (
    PROMPT_VERSION_PAIR_A,
    PROMPT_VERSION_PAIR_B,
    SYSTEM_PROMPT_PAIR_A,
    SYSTEM_PROMPT_PAIR_B,
    render_pair_a_allergy,
    render_pair_a_diagnosis,
    render_pair_a_encounter,
    render_pair_a_family_history,
    render_pair_a_imaging,
    render_pair_a_immunization,
    render_pair_a_lab,
    render_pair_a_medication,
    render_pair_a_procedure,
    render_pair_a_social_history,
    render_pair_a_test,
    render_pair_a_vital,
    render_pair_b_user_prompt,
)

logger = logging.getLogger(__name__)


# ─── Structured output schemas ──────────────────────────────────────────────


InconsistencyKind = Literal["none", "temporal", "biological", "pharmacological"]


class EvidenceRow(BaseModel):
    row_id: str
    table: str
    quote: str = Field(default="", max_length=500)


class JudgeResultA(BaseModel):
    """Structured output for one Use Case A pair (symptom × datapoint)."""

    likelihood_pct: int = Field(ge=0, le=100)
    rationale: str = Field(max_length=600)
    differentiating_test: str | None = None
    supporting_chart_evidence: list[EvidenceRow] = Field(default_factory=list)


class JudgeResultB(BaseModel):
    """Structured output for one Use Case B pair (finding × finding)."""

    inconsistency_pct: int = Field(ge=0, le=100)
    kind: InconsistencyKind = "none"
    rationale: str = Field(max_length=600)
    suggested_clarification: str | None = None
    evidence: list[EvidenceRow] = Field(default_factory=list)


@dataclass(frozen=True)
class JudgeCallTelemetry:
    """Telemetry for one judge call."""

    prompt_tokens: int
    completion_tokens: int
    latency_ms: float
    model: str
    prompt_version: str
    dollar_cost: float


@dataclass(frozen=True)
class JudgmentA:
    pair: PairA
    result: JudgeResultA | None
    telemetry: JudgeCallTelemetry
    error: str | None = None
    # ``error_category`` is a stable identifier (snake_case) for the kind of
    # failure — the aggregator uses it to collapse many identical errors
    # into a single banner. ``error_hint`` is a one-line actionable message.
    # Both stay None on success (matches the existing behaviour) and on
    # error paths that pre-date the diagnoser (verifier-style schema errors,
    # which still set ``error`` directly).
    error_category: str | None = None
    error_hint: str | None = None


@dataclass(frozen=True)
class JudgmentB:
    pair: PairB
    result: JudgeResultB | None
    telemetry: JudgeCallTelemetry
    error: str | None = None
    error_category: str | None = None
    error_hint: str | None = None


# ─── Pair A dispatch: pick the right per-kind renderer ─────────────────────


def _render_pair_a(pair: PairA) -> str:
    """Dispatch to the per-kind prompt renderer for this candidate kind."""
    obj = pair.candidate_obj
    prov = pair.candidate_provenance
    base_kwargs: dict[str, Any] = {
        "symptom": pair.symptom,
        "since": pair.since,
        "row_id": str(prov.row_id),
        "table": prov.table,
        "quote": pair.candidate_label,
    }
    kind = pair.candidate_kind

    if kind == "diagnosis":
        return render_pair_a_diagnosis(
            label=getattr(obj, "label", pair.candidate_label),
            icd10=getattr(obj, "icd10", None),
            onset=str(getattr(obj, "onset", "") or "") or None,
            verification=str(getattr(obj, "verification", "") or "") or None,
            **base_kwargs,
        )
    if kind == "medication":
        return render_pair_a_medication(
            label=getattr(obj, "label", pair.candidate_label),
            rxnorm=getattr(obj, "rxnorm", None),
            dose=getattr(obj, "dose", None),
            started=str(getattr(obj, "started", "") or "") or None,
            active=bool(getattr(obj, "active", True)),
            **base_kwargs,
        )
    if kind == "lab":
        return render_pair_a_lab(
            label=getattr(obj, "label", pair.candidate_label),
            loinc=getattr(obj, "loinc", None),
            value=getattr(obj, "value", None),
            unit=getattr(obj, "unit", None),
            ref_low=getattr(obj, "reference_low", None),
            ref_high=getattr(obj, "reference_high", None),
            abnormal_flag=getattr(obj, "abnormal_flag", None),
            observed_at=str(getattr(obj, "observed_at", "") or "") or None,
            **base_kwargs,
        )
    if kind == "vital":
        return render_pair_a_vital(
            label=getattr(obj, "label", pair.candidate_label),
            loinc=getattr(obj, "loinc", None),
            value=getattr(obj, "value", None),
            unit=getattr(obj, "unit", None),
            observed_at=str(getattr(obj, "observed_at", "") or "") or None,
            **base_kwargs,
        )
    if kind == "allergy":
        return render_pair_a_allergy(
            label=getattr(obj, "label", pair.candidate_label),
            severity=getattr(obj, "severity", None),
            reaction=getattr(obj, "reaction", None),
            recorded=str(getattr(obj, "onset", "") or "") or None,
            **base_kwargs,
        )
    if kind == "procedure":
        return render_pair_a_procedure(
            label=getattr(obj, "label", pair.candidate_label),
            cpt=getattr(obj, "cpt", None),
            performed=str(getattr(obj, "performed", "") or "") or None,
            status=getattr(obj, "status", None),
            **base_kwargs,
        )
    if kind == "immunization":
        return render_pair_a_immunization(
            label=getattr(obj, "label", pair.candidate_label),
            cvx=getattr(obj, "cvx", None),
            administered=str(getattr(obj, "administered", "") or "") or None,
            dose_number=getattr(obj, "dose_number", None),
            series_total=getattr(obj, "series_total", None),
            **base_kwargs,
        )
    if kind == "family_history":
        return render_pair_a_family_history(
            relationship=getattr(obj, "relationship", "?"),
            condition=getattr(obj, "condition", pair.candidate_label),
            relative_onset_age=getattr(obj, "relative_onset_age", None),
            status=getattr(obj, "status", None),
            patient_age=None,  # injected at runtime by caller if available
            **base_kwargs,
        )
    if kind == "social_history":
        return render_pair_a_social_history(
            category=getattr(obj, "category", "?"),
            value=getattr(obj, "value", pair.candidate_label),
            **base_kwargs,
        )
    if kind == "imaging":
        return render_pair_a_imaging(
            modality=getattr(obj, "modality", "?"),
            body_part=getattr(obj, "body_part", "?"),
            findings=getattr(obj, "findings", pair.candidate_label),
            performed=str(getattr(obj, "performed", "") or "") or None,
            **base_kwargs,
        )
    if kind == "test":
        return render_pair_a_test(
            label=getattr(obj, "label", pair.candidate_label),
            study_type=getattr(obj, "study_type", "?"),
            result=getattr(obj, "result", "?"),
            performed=str(getattr(obj, "performed", "") or "") or None,
            **base_kwargs,
        )
    if kind == "encounter":
        return render_pair_a_encounter(
            encounter_type=getattr(obj, "encounter_type", "?"),
            reason=getattr(obj, "reason", pair.candidate_label),
            start=str(getattr(obj, "start", "") or "") or None,
            end=str(getattr(obj, "end", "") or "") or None,
            **base_kwargs,
        )
    # Unknown kind — fall back to generic.
    return render_pair_a_diagnosis(
        label=pair.candidate_label, icd10=None, onset=None, verification=None,
        **base_kwargs,
    )


def _describe_b_obj(obj: object) -> str:
    """One-line descriptor used as the parenthesised hint in Pair B prompts."""
    parts: list[str] = []
    for attr in ("onset", "started", "performed", "administered", "observed_at",
                 "recorded", "value", "unit", "dose", "severity"):
        v = getattr(obj, attr, None)
        if v is not None and str(v) != "":
            parts.append(f"{attr}={v}")
    return ", ".join(parts) or "—"


def _render_pair_b(pair: PairB) -> str:
    return render_pair_b_user_prompt(
        kind_a=pair.kind_a,
        label_a=pair.label_a,
        descriptor_a=_describe_b_obj(pair.obj_a),
        row_id_a=str(pair.provenance_a.row_id),
        table_a=pair.provenance_a.table,
        quote_a=pair.label_a,
        kind_b=pair.kind_b,
        label_b=pair.label_b,
        descriptor_b=_describe_b_obj(pair.obj_b),
        row_id_b=str(pair.provenance_b.row_id),
        table_b=pair.provenance_b.table,
        quote_b=pair.label_b,
    )


# ─── Provider interface ─────────────────────────────────────────────────────


class JudgeProvider(ABC):
    model_name: str

    @abstractmethod
    async def judge_a(self, pair: PairA) -> JudgmentA: ...

    @abstractmethod
    async def judge_b(self, pair: PairB) -> JudgmentB: ...

    async def judge_many_a(self, pairs: list[PairA], *, max_concurrency: int) -> list[JudgmentA]:
        sem = asyncio.Semaphore(max_concurrency)
        async def one(p: PairA) -> JudgmentA:
            async with sem:
                return await self.judge_a(p)
        return await asyncio.gather(*(one(p) for p in pairs))

    async def judge_many_b(self, pairs: list[PairB], *, max_concurrency: int) -> list[JudgmentB]:
        sem = asyncio.Semaphore(max_concurrency)
        async def one(p: PairB) -> JudgmentB:
            async with sem:
                return await self.judge_b(p)
        return await asyncio.gather(*(one(p) for p in pairs))


# ─── Mock provider (deterministic, offline) ────────────────────────────────


# Tiny seed knowledge for offline testing. Keys are normalised (lowercase)
# substring pairs. Values are (likelihood_pct, rationale, differentiating_test).
# Anything not in the seed table returns 5–15% with empty evidence so the
# verifier strips it.
_SEED_PAIR_A: dict[tuple[str, str], tuple[int, str, str | None]] = {
    ("toe pain", "gout"): (
        82,
        "Gout flares classically present as podagra (sudden monoarticular MTP-joint pain).",
        "serum uric acid; joint aspirate for monosodium urate crystals",
    ),
    ("swollen toe", "gout"): (
        85,
        "Acute monoarticular swelling at the first MTP joint is the classic gout flare.",
        "joint aspirate for monosodium urate crystals",
    ),
    ("body aches", "gout"): (
        55,
        "Polyarticular gout flares can present with diffuse musculoskeletal pain in long-standing disease.",
        "serum uric acid",
    ),
    ("toe pain", "c-reactive protein"): (
        38,
        "Elevated CRP supports an active inflammatory process; with monoarticular toe pain consistent with crystal arthropathy or septic arthritis.",
        "joint aspirate (rules in/out infection vs crystals)",
    ),
    ("swollen toe", "c-reactive protein"): (
        42,
        "Elevated CRP plus acute monoarticular swelling supports inflammatory arthritis; gout is leading explanation when chart documents gout.",
        "joint aspirate for crystals + Gram stain",
    ),
}

_SEED_PAIR_B: dict[tuple[str, str], tuple[int, InconsistencyKind, str, str]] = {
    ("osteoporosis", "osteopenia"): (
        92, "temporal",
        "Osteopenia normally precedes osteoporosis on bone-density progression; the reverse is biologically backward.",
        "Confirm onset dates; if osteoporosis truly preceded osteopenia, document the intervening treatment (e.g. zoledronic acid).",
    ),
    ("penicillin allergy", "amoxicillin"): (
        90, "pharmacological",
        "Documented penicillin-class allergy with subsequent amoxicillin (penicillin-class) exposure without re-exposure note.",
        "Either remove the allergy if tolerated, or add a penicillin-allergy-confirmed note and stop amoxicillin.",
    ),
}


class MockProvider(JudgeProvider):
    def __init__(self, model_name: str = "mock-deterministic-v2") -> None:
        self.model_name = model_name

    @staticmethod
    def _match_a(symptom: str, candidate_label: str) -> tuple[int, str, str | None] | None:
        s = symptom.lower(); c = candidate_label.lower()
        for (sk, ck), v in _SEED_PAIR_A.items():
            if sk in s and ck in c:
                return v
        return None

    @staticmethod
    def _match_b(label_a: str, label_b: str) -> tuple[int, InconsistencyKind, str, str] | None:
        a = label_a.lower(); b = label_b.lower()
        for (ak, bk), v in _SEED_PAIR_B.items():
            if ak in a and bk in b:
                return v
        return None

    def _telem(self, prompt_version: str) -> JudgeCallTelemetry:
        return JudgeCallTelemetry(prompt_tokens=0, completion_tokens=0, latency_ms=0.0,
                                   model=self.model_name, prompt_version=prompt_version,
                                   dollar_cost=0.0)

    async def judge_a(self, pair: PairA) -> JudgmentA:
        match = self._match_a(pair.symptom, pair.candidate_label)
        if match is None:
            result = JudgeResultA(likelihood_pct=8,
                                   rationale=f"No documented mechanism connecting '{pair.symptom}' to '{pair.candidate_label}'.",
                                   differentiating_test=None,
                                   supporting_chart_evidence=[])
        else:
            pct, rationale, diff = match
            result = JudgeResultA(
                likelihood_pct=pct, rationale=rationale, differentiating_test=diff,
                supporting_chart_evidence=[EvidenceRow(
                    row_id=str(pair.candidate_provenance.row_id),
                    table=pair.candidate_provenance.table,
                    quote=pair.candidate_label,
                )],
            )
        return JudgmentA(pair=pair, result=result, telemetry=self._telem(PROMPT_VERSION_PAIR_A))

    async def judge_b(self, pair: PairB) -> JudgmentB:
        match = self._match_b(pair.label_a, pair.label_b)
        if match is None:
            result = JudgeResultB(inconsistency_pct=0, kind="none",
                                   rationale="No inconsistency between these datapoints.",
                                   suggested_clarification=None, evidence=[])
        else:
            pct, kind, rationale, clarification = match
            result = JudgeResultB(
                inconsistency_pct=pct, kind=kind, rationale=rationale,
                suggested_clarification=clarification,
                evidence=[
                    EvidenceRow(row_id=str(pair.provenance_a.row_id), table=pair.provenance_a.table, quote=pair.label_a),
                    EvidenceRow(row_id=str(pair.provenance_b.row_id), table=pair.provenance_b.table, quote=pair.label_b),
                ],
            )
        return JudgmentB(pair=pair, result=result, telemetry=self._telem(PROMPT_VERSION_PAIR_B))


# ─── OpenAI provider ───────────────────────────────────────────────────────


class OpenAIProvider(JudgeProvider):
    """Structured-output OpenAI provider.

    Targets ``gpt-4o-mini`` by default — calibration anchors in the
    prompts are tuned for it. Uses JSON-schema strict mode so a missing
    row_id becomes a discardable result (verifier strips it).
    """

    # gpt-4o-mini approximate pricing (per 1M tokens): $0.15 prompt /
    # $0.60 completion at the time of writing. Stored per-1k for the
    # legacy telemetry interface.
    DOLLARS_PER_1K_PROMPT_TOKENS = 0.00015
    DOLLARS_PER_1K_COMPLETION_TOKENS = 0.0006

    def __init__(self, settings: Settings) -> None:
        try:
            from openai import AsyncOpenAI
        except ImportError as exc:
            raise RuntimeError(
                "openai is required for OpenAIProvider; install with `pip install -e .[openai]`"
            ) from exc
        # Fail loud at startup if the key is missing — better than 24
        # APIConnectionErrors per request later. The empty-string-to-None
        # validator in Settings means we only see ``None`` here.
        if not settings.openai_api_key:
            raise RuntimeError(
                "OPENAI_API_KEY is empty or missing in .env. The sidecar is "
                "configured for live OpenAI (COPILOT_LLM_PROVIDER=openai) "
                "but no key is available. Either set the key, or switch "
                "COPILOT_LLM_PROVIDER=mock for offline runs."
            )
        self.model_name = settings.openai_model
        self._base_url = settings.openai_base_url  # remembered for diagnostics
        self._api_key_summary = (
            f"{settings.openai_api_key[:7]}…<len={len(settings.openai_api_key)}>"
        )
        # Belt + suspenders: only pass base_url if it's actually set, so an
        # empty string never reaches the SDK (which would break URL
        # building and surface as APIConnectionError).
        client_kwargs: dict[str, Any] = {"api_key": settings.openai_api_key}
        if settings.openai_base_url:
            client_kwargs["base_url"] = settings.openai_base_url
        self._client = AsyncOpenAI(**client_kwargs)
        # Surface the resolved configuration once at construction so the
        # launcher log carries the model + base_url + key prefix every run.
        # This is the breadcrumb you want when reading .launch.log after a
        # batch of pair-judge failures: it confirms which key + endpoint
        # were actually loaded vs. what's printed in .env.example.
        proxy_env = {
            k: os.environ[k]
            for k in (
                "HTTP_PROXY", "HTTPS_PROXY", "ALL_PROXY",
                "http_proxy", "https_proxy", "all_proxy",
            )
            if os.environ.get(k)
        }
        logger.info(
            "OpenAIProvider configured",
            extra={
                "model": self.model_name,
                "base_url": settings.openai_base_url or "https://api.openai.com/v1 (default)",
                "api_key": self._api_key_summary,
                "proxy_env_set": sorted(proxy_env.keys()) or None,
            },
        )
        if proxy_env:
            # Proxy env vars silently reroute outbound HTTPS — most users
            # don't realise their shell has these set. Warn loudly so a
            # later APIConnectionError isn't a mystery.
            logger.warning(
                "Outbound proxy env vars detected — they will route OpenAI "
                "traffic through a proxy. If you didn't mean to, unset them "
                "before launching the sidecar.",
                extra={"proxy_env": sorted(proxy_env.keys())},
            )

    def _cost(self, prompt_tokens: int, completion_tokens: int) -> float:
        return (
            (prompt_tokens / 1000.0) * self.DOLLARS_PER_1K_PROMPT_TOKENS
            + (completion_tokens / 1000.0) * self.DOLLARS_PER_1K_COMPLETION_TOKENS
        )

    async def _structured_call(
        self, system: str, user: str, schema: type[BaseModel], prompt_version: str,
    ) -> tuple[BaseModel | None, JudgeCallTelemetry, ErrorDiagnosis | None]:
        """One structured-output call.

        Uses the OpenAI SDK's pydantic-native ``parse()`` API which builds
        a JSON-Schema strict-mode-compatible request automatically (it
        strips constraints OpenAI rejects like ``minimum/maximum`` and
        marks every property required). Falls back to a non-strict
        ``json_object`` request if ``parse()`` is unavailable in older
        SDKs.

        On failure returns ``(None, telemetry, ErrorDiagnosis)`` — the
        diagnosis carries a one-line summary, a stable ``category``
        identifier (the aggregator uses it to dedupe), and a human-
        readable hint. The full traceback + chain is also logged at
        WARNING level via the module logger so the launcher's
        ``.launch.log`` carries everything.
        """
        start = time.perf_counter()
        try:
            parse_fn = getattr(getattr(self._client, "beta", None), "chat", None)
            if parse_fn is not None and hasattr(parse_fn.completions, "parse"):
                resp = await self._client.beta.chat.completions.parse(
                    model=self.model_name,
                    temperature=0.0,
                    response_format=schema,
                    messages=[
                        {"role": "system", "content": system},
                        {"role": "user", "content": user},
                    ],
                )
                parsed = resp.choices[0].message.parsed
            else:
                resp = await self._client.chat.completions.create(
                    model=self.model_name,
                    temperature=0.0,
                    response_format={"type": "json_object"},
                    messages=[
                        {"role": "system",
                         "content": system + "\nReturn JSON matching this schema:\n"
                                    + json.dumps(schema.model_json_schema())},
                        {"role": "user", "content": user},
                    ],
                )
                content = resp.choices[0].message.content or "{}"
                parsed = schema.model_validate_json(content)

            latency = (time.perf_counter() - start) * 1000.0
            usage = getattr(resp, "usage", None)
            pt = getattr(usage, "prompt_tokens", 0) or 0
            ct = getattr(usage, "completion_tokens", 0) or 0
            telem = JudgeCallTelemetry(
                prompt_tokens=pt, completion_tokens=ct, latency_ms=latency,
                model=self.model_name, prompt_version=prompt_version,
                dollar_cost=self._cost(pt, ct),
            )
            return parsed, telem, None
        except (ValidationError, json.JSONDecodeError) as exc:
            # Schema-side failures: the SDK gave us a response but it
            # didn't match the pydantic model. Distinct category so the
            # operator knows to look at the prompt, not the network.
            latency = (time.perf_counter() - start) * 1000.0
            telem = JudgeCallTelemetry(0, 0, latency, self.model_name, prompt_version, 0.0)
            logger.warning(
                "pair-judge schema validation failed",
                exc_info=True,
                extra={
                    "model": self.model_name,
                    "prompt_version": prompt_version,
                    "latency_ms": round(latency, 1),
                    "exception_type": type(exc).__name__,
                },
            )
            return None, telem, ErrorDiagnosis(
                summary=f"schema validation failed: {type(exc).__name__}: {exc}",
                category="schema_validation_failed",
                hint=(
                    "The model returned content the pydantic schema "
                    "rejected. Almost always a prompt or schema issue, "
                    "not a network one. Look at the rendered user prompt "
                    "for this pair and at JudgeResultA/JudgeResultB."
                ),
                log_context={
                    "exception_type": type(exc).__name__,
                    "exception_message": str(exc)[:512],
                },
            )
        except Exception as exc:  # noqa: BLE001
            latency = (time.perf_counter() - start) * 1000.0
            telem = JudgeCallTelemetry(0, 0, latency, self.model_name, prompt_version, 0.0)
            diagnosis = diagnose_openai_error(
                exc,
                base_url=self._base_url,
                api_key=self._api_key_summary,  # already redacted
                model=self.model_name,
            )
            # Log once with the full traceback + chain + category so the
            # launcher .launch.log captures everything an operator would
            # ask for. The UI gets the short summary via the return value.
            logger.warning(
                "openai call failed",
                exc_info=True,
                extra={
                    "category": diagnosis.category,
                    "summary": diagnosis.summary,
                    "hint": diagnosis.hint,
                    "latency_ms": round(latency, 1),
                    "prompt_version": prompt_version,
                    **diagnosis.log_context,
                },
            )
            return None, telem, diagnosis

    async def judge_a(self, pair: PairA) -> JudgmentA:
        user = _render_pair_a(pair)
        result, telem, diag = await self._structured_call(
            SYSTEM_PROMPT_PAIR_A, user, JudgeResultA, PROMPT_VERSION_PAIR_A,
        )
        return JudgmentA(
            pair=pair,
            result=result,  # type: ignore[arg-type]
            telemetry=telem,
            error=diag.summary if diag is not None else None,
            error_category=diag.category if diag is not None else None,
            error_hint=diag.hint if diag is not None else None,
        )

    async def judge_b(self, pair: PairB) -> JudgmentB:
        user = _render_pair_b(pair)
        result, telem, diag = await self._structured_call(
            SYSTEM_PROMPT_PAIR_B, user, JudgeResultB, PROMPT_VERSION_PAIR_B,
        )
        return JudgmentB(
            pair=pair,
            result=result,  # type: ignore[arg-type]
            telemetry=telem,
            error=diag.summary if diag is not None else None,
            error_category=diag.category if diag is not None else None,
            error_hint=diag.hint if diag is not None else None,
        )
