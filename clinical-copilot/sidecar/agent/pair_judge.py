"""Pair Judge: structured-output LLM evaluation of one pair.

Two providers ship: ``OpenAIProvider`` for production (BAA + ZDR) and
``MockProvider`` for offline / CI runs. The mock is **deterministic**:
same input → same output. It encodes the seed cases from
ARCHITECTURE.md §7.1 (gout, osteoporosis-then-osteopenia, penicillin +
amoxicillin, HbA1c-without-diabetes-meds) and a small fallback ruleset.

The judge dispatches in parallel batches with a concurrency cap
(``Settings.pair_judge_max_concurrency``) per ARCHITECTURE.md §4.1.
"""

from __future__ import annotations

import asyncio
import json
import logging
import time
from abc import ABC, abstractmethod
from dataclasses import dataclass
from typing import Any, Literal

from pydantic import BaseModel, Field, ValidationError

from sidecar.config import Settings

from .pair_generator import PairA, PairB
from .prompts import (
    PROMPT_VERSION_PAIR_A,
    PROMPT_VERSION_PAIR_B,
    SYSTEM_PROMPT_PAIR_A,
    SYSTEM_PROMPT_PAIR_B,
    render_pair_a_user_prompt,
    render_pair_b_user_prompt,
)

logger = logging.getLogger(__name__)


# ─── Structured output schemas ──────────────────────────────────────────────


Likelihood = Literal["low", "moderate", "high"]
Inconsistency = Literal["none", "temporal", "biological", "pharmacological"]


class EvidenceRow(BaseModel):
    """A single chart row that supports the judgment."""

    row_id: int | str
    table: str
    quote: str = Field(default="", max_length=500)


class JudgeResultA(BaseModel):
    """Structured output for one Use Case A pair (symptom × candidate)."""

    likelihood: Likelihood
    mechanism: str = Field(max_length=500)
    supporting_chart_evidence: list[EvidenceRow] = Field(default_factory=list)
    differentiating_test: str | None = None


class JudgeResultB(BaseModel):
    """Structured output for one Use Case B pair (finding × finding)."""

    inconsistency: Inconsistency
    confidence: float = Field(ge=0.0, le=1.0)
    rule_cited: str | None = None
    evidence: list[EvidenceRow] = Field(default_factory=list)
    suggested_clarification: str | None = None


@dataclass(frozen=True)
class JudgeCallTelemetry:
    """Telemetry for one judge call. Captured per ARCHITECTURE.md §6.2."""

    prompt_tokens: int
    completion_tokens: int
    latency_ms: float
    model: str
    prompt_version: str
    dollar_cost: float


@dataclass(frozen=True)
class JudgmentA:
    pair: PairA
    result: JudgeResultA | None  # None when the call failed
    telemetry: JudgeCallTelemetry
    error: str | None = None


@dataclass(frozen=True)
class JudgmentB:
    pair: PairB
    result: JudgeResultB | None
    telemetry: JudgeCallTelemetry
    error: str | None = None


# ─── Provider interface ─────────────────────────────────────────────────────


class JudgeProvider(ABC):
    """Pluggable provider abstraction.

    Two implementations: ``OpenAIProvider`` for production (BAA + ZDR) and
    ``MockProvider`` for offline / CI runs.
    """

    model_name: str

    @abstractmethod
    async def judge_a(self, pair: PairA) -> JudgmentA: ...

    @abstractmethod
    async def judge_b(self, pair: PairB) -> JudgmentB: ...

    async def judge_many_a(
        self, pairs: list[PairA], *, max_concurrency: int
    ) -> list[JudgmentA]:
        """Dispatch many Use Case A pairs in parallel batches."""
        sem = asyncio.Semaphore(max_concurrency)

        async def one(p: PairA) -> JudgmentA:
            async with sem:
                return await self.judge_a(p)

        return await asyncio.gather(*(one(p) for p in pairs))

    async def judge_many_b(
        self, pairs: list[PairB], *, max_concurrency: int
    ) -> list[JudgmentB]:
        sem = asyncio.Semaphore(max_concurrency)

        async def one(p: PairB) -> JudgmentB:
            async with sem:
                return await self.judge_b(p)

        return await asyncio.gather(*(one(p) for p in pairs))


# ─── Mock provider (deterministic, offline) ────────────────────────────────


# Tiny knowledge base used by the mock for the seed cases. Keys are
# normalised (lowercase). Values are the predicted likelihood + mechanism.
# This is deliberately small: the mock's job is to demonstrate the engine,
# not to be a clinical knowledge base.
_SEED_PAIR_A: dict[tuple[str, str], tuple[Likelihood, str, str | None]] = {
    # (symptom_substring, candidate_label_substring): (likelihood, mechanism, diff_test)
    ("toe pain", "gout"): (
        "high",
        "Gout flares classically present as podagra (sudden monoarticular MTP-joint pain)",
        "serum uric acid; joint aspirate for monosodium urate crystals",
    ),
    ("toe pain", "diabetes"): (
        "low",
        "Diabetic peripheral neuropathy is typically symmetric and chronic, not acute monoarticular pain",
        None,
    ),
    ("body aches", "gout"): (
        "moderate",
        "Polyarticular gout flares can present with diffuse musculoskeletal pain in long-standing disease",
        "serum uric acid",
    ),
    ("body aches", "hypertension"): (
        "low",
        "Essential hypertension does not produce diffuse body aches as a primary symptom",
        None,
    ),
    ("swelling", "gout"): (
        "high",
        "Gout produces inflammatory joint swelling — warmth, redness, tenderness",
        "joint aspirate",
    ),
    ("swelling", "atrial fibrillation"): (
        "low",
        "Edema from atrial fibrillation is typically bilateral lower-extremity, not focal toe swelling",
        None,
    ),
    ("swollen toe", "gout"): (
        "high",
        "Acute monoarticular swelling at the first MTP joint is the classic gout flare presentation",
        "joint aspirate for monosodium urate crystals",
    ),
    ("toe pain", "c-reactive protein"): (
        "moderate",
        "Elevated CRP supports an active inflammatory process; with monoarticular toe pain this is consistent with crystal arthropathy (e.g. gout) or septic arthritis",
        "joint aspirate (rules in/out infection vs crystals)",
    ),
    ("swollen toe", "c-reactive protein"): (
        "moderate",
        "Elevated CRP plus acute monoarticular swelling supports an inflammatory arthritis; gout is the leading explanation when chart already documents gout",
        "joint aspirate for crystals + Gram stain",
    ),
}


_SEED_PAIR_B: dict[tuple[str, str], tuple[Inconsistency, float, str, str]] = {
    # (label_a_substring, label_b_substring): (kind, confidence, rule, suggested_clarification)
    ("osteoporosis", "osteopenia"): (
        "temporal",
        0.92,
        "osteopenia normally precedes osteoporosis on bone-density progression",
        "Confirm onset dates; if osteoporosis truly preceded osteopenia, document the intervening treatment (e.g. zoledronic acid).",
    ),
    ("penicillin allergy", "amoxicillin"): (
        "pharmacological",
        0.90,
        "documented penicillin-class allergy with subsequent amoxicillin (penicillin-class) exposure without re-exposure note",
        "Either remove the allergy if tolerated, or add a penicillin-allergy-confirmed note and stop amoxicillin.",
    ),
    ("type 2 diabetes", "hba1c"): (
        "biological",
        0.78,
        "type 2 diabetes diagnosis with sustained HbA1c < 5.7% without medication suggests prediabetes miscoded as diabetes",
        "Reclassify as prediabetes (R73.03) if the HbA1c trajectory has been < 5.7 for ≥ 6 months without medication.",
    ),
}


class MockProvider(JudgeProvider):
    """Deterministic offline provider used by tests, evals, and dev mode.

    Same input → same output. Uses substring matching against the seed
    knowledge base above. Anything not in the seed table returns
    ``low / none`` with empty evidence — the verifier then strips it.
    """

    def __init__(self, model_name: str = "mock-deterministic-v1") -> None:
        self.model_name = model_name

    @staticmethod
    def _match_a(symptom: str, candidate_label: str) -> tuple[Likelihood, str, str | None] | None:
        s = symptom.lower()
        c = candidate_label.lower()
        for (sk, ck), value in _SEED_PAIR_A.items():
            if sk in s and ck in c:
                return value
        return None

    @staticmethod
    def _match_b(
        label_a: str, label_b: str
    ) -> tuple[Inconsistency, float, str, str] | None:
        a = label_a.lower()
        b = label_b.lower()
        for (ak, bk), value in _SEED_PAIR_B.items():
            if ak in a and bk in b:
                return value
        return None

    def _telemetry(self, prompt_version: str) -> JudgeCallTelemetry:
        return JudgeCallTelemetry(
            prompt_tokens=0,
            completion_tokens=0,
            latency_ms=0.0,
            model=self.model_name,
            prompt_version=prompt_version,
            dollar_cost=0.0,
        )

    async def judge_a(self, pair: PairA) -> JudgmentA:
        match = self._match_a(pair.symptom, pair.candidate_label)
        if match is None:
            result = JudgeResultA(
                likelihood="low",
                mechanism=(
                    f"No mechanism connecting '{pair.symptom}' to "
                    f"'{pair.candidate_label}' in the seed knowledge base."
                ),
                supporting_chart_evidence=[],
                differentiating_test=None,
            )
        else:
            likelihood, mechanism, diff_test = match
            result = JudgeResultA(
                likelihood=likelihood,
                mechanism=mechanism,
                supporting_chart_evidence=[
                    EvidenceRow(
                        row_id=pair.candidate_provenance.row_id,
                        table=pair.candidate_provenance.table,
                        quote=pair.candidate_label,
                    )
                ],
                differentiating_test=diff_test,
            )
        return JudgmentA(pair=pair, result=result, telemetry=self._telemetry(PROMPT_VERSION_PAIR_A))

    async def judge_b(self, pair: PairB) -> JudgmentB:
        match = self._match_b(pair.label_a, pair.label_b)
        if match is None:
            result = JudgeResultB(
                inconsistency="none",
                confidence=0.0,
                rule_cited=None,
                evidence=[],
                suggested_clarification=None,
            )
        else:
            kind, conf, rule, clarification = match
            result = JudgeResultB(
                inconsistency=kind,
                confidence=conf,
                rule_cited=rule,
                evidence=[
                    EvidenceRow(
                        row_id=pair.provenance_a.row_id,
                        table=pair.provenance_a.table,
                        quote=pair.label_a,
                    ),
                    EvidenceRow(
                        row_id=pair.provenance_b.row_id,
                        table=pair.provenance_b.table,
                        quote=pair.label_b,
                    ),
                ],
                suggested_clarification=clarification,
            )
        return JudgmentB(pair=pair, result=result, telemetry=self._telemetry(PROMPT_VERSION_PAIR_B))


# ─── OpenAI provider (BAA + ZDR) ───────────────────────────────────────────


class OpenAIProvider(JudgeProvider):
    """OpenAI structured-output provider.

    Uses OpenAI's JSON-schema mode so a missing ``row_id`` in evidence
    becomes a discardable result (ARCHITECTURE.md §4.1). Lazily imports
    the OpenAI SDK so the package is optional in test environments.
    """

    # gpt-5 prices are placeholders; bump as soon as rate cards land.
    DOLLARS_PER_1K_PROMPT_TOKENS = 0.005
    DOLLARS_PER_1K_COMPLETION_TOKENS = 0.015

    def __init__(self, settings: Settings) -> None:
        try:
            from openai import AsyncOpenAI
        except ImportError as exc:  # pragma: no cover
            raise RuntimeError(
                "openai is required for OpenAIProvider; install with `pip install .[openai]`"
            ) from exc
        self.model_name = settings.openai_model
        self._client = AsyncOpenAI(
            api_key=settings.openai_api_key,
            base_url=settings.openai_base_url,
        )

    def _cost(self, prompt_tokens: int, completion_tokens: int) -> float:
        return (
            (prompt_tokens / 1000.0) * self.DOLLARS_PER_1K_PROMPT_TOKENS
            + (completion_tokens / 1000.0) * self.DOLLARS_PER_1K_COMPLETION_TOKENS
        )

    async def _structured_call(
        self, system: str, user: str, schema: type[BaseModel], prompt_version: str
    ) -> tuple[BaseModel | None, JudgeCallTelemetry, str | None]:
        start = time.perf_counter()
        try:
            resp = await self._client.chat.completions.create(
                model=self.model_name,
                temperature=0.0,
                response_format={
                    "type": "json_schema",
                    "json_schema": {
                        "name": schema.__name__,
                        "schema": schema.model_json_schema(),
                        "strict": True,
                    },
                },
                messages=[
                    {"role": "system", "content": system},
                    {"role": "user", "content": user},
                ],
            )
        except Exception as exc:  # noqa: BLE001
            latency = (time.perf_counter() - start) * 1000.0
            return None, JudgeCallTelemetry(
                prompt_tokens=0,
                completion_tokens=0,
                latency_ms=latency,
                model=self.model_name,
                prompt_version=prompt_version,
                dollar_cost=0.0,
            ), str(exc)

        latency = (time.perf_counter() - start) * 1000.0
        usage = resp.usage
        prompt_tokens = usage.prompt_tokens if usage else 0
        completion_tokens = usage.completion_tokens if usage else 0
        telemetry = JudgeCallTelemetry(
            prompt_tokens=prompt_tokens,
            completion_tokens=completion_tokens,
            latency_ms=latency,
            model=self.model_name,
            prompt_version=prompt_version,
            dollar_cost=self._cost(prompt_tokens, completion_tokens),
        )
        choice = resp.choices[0]
        content = choice.message.content
        if not content:
            return None, telemetry, "empty completion"
        try:
            parsed: dict[str, Any] = json.loads(content)
        except json.JSONDecodeError as exc:
            return None, telemetry, f"json decode failed: {exc}"
        try:
            return schema.model_validate(parsed), telemetry, None
        except ValidationError as exc:
            return None, telemetry, f"schema validation failed: {exc}"

    @staticmethod
    def _provenance_block_for_a(pair: PairA) -> str:
        p = pair.candidate_provenance
        return (
            f"row_id: {p.row_id}\n"
            f"table: {p.table}\n"
            f"observed_at: {p.observed_at}\n"
            f"label: {pair.candidate_label}"
        )

    @staticmethod
    def _provenance_block_for_b(label: str, prov: object) -> str:
        # `prov` is a Provenance pydantic model. Read attributes defensively.
        return (
            f"row_id: {getattr(prov, 'row_id', '?')}\n"
            f"table: {getattr(prov, 'table', '?')}\n"
            f"observed_at: {getattr(prov, 'observed_at', '?')}\n"
            f"label: {label}"
        )

    async def judge_a(self, pair: PairA) -> JudgmentA:
        user = render_pair_a_user_prompt(
            symptom=pair.symptom,
            candidate_label=pair.candidate_label,
            candidate_kind=pair.candidate_kind,
            candidate_provenance_block=self._provenance_block_for_a(pair),
        )
        result, telemetry, err = await self._structured_call(
            SYSTEM_PROMPT_PAIR_A, user, JudgeResultA, PROMPT_VERSION_PAIR_A
        )
        return JudgmentA(pair=pair, result=result, telemetry=telemetry, error=err)  # type: ignore[arg-type]

    async def judge_b(self, pair: PairB) -> JudgmentB:
        user = render_pair_b_user_prompt(
            label_a=pair.label_a,
            provenance_block_a=self._provenance_block_for_b(pair.label_a, pair.provenance_a),
            label_b=pair.label_b,
            provenance_block_b=self._provenance_block_for_b(pair.label_b, pair.provenance_b),
        )
        result, telemetry, err = await self._structured_call(
            SYSTEM_PROMPT_PAIR_B, user, JudgeResultB, PROMPT_VERSION_PAIR_B
        )
        return JudgmentB(pair=pair, result=result, telemetry=telemetry, error=err)  # type: ignore[arg-type]
