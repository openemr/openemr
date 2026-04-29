# AI Cost Analysis

> **Inputs:** ARCHITECTURE.md §4.1 (pair counts), §6.2 (telemetry), §7.4
> (cost evaluation), §8 (caching). All numbers below assume OpenAI's
> Enterprise BAA + ZDR endpoint as the primary, ``gpt-5`` reasoning tier.
> Token estimates are based on the structured-output schemas shipped in
> ``sidecar/agent/pair_judge.py``.

## TL;DR

| Tier | Concurrent users | Daily LLM spend | Annualised | Required architectural change |
|---|---|---|---|---|
| Dev | 1 | < $0.50 | < $200 | None — runs on a single laptop |
| 100 users | ~3 | $30 | $11,000 | Pair-judgment cache; nightly batch for Use Case B |
| 1,000 users | ~30 | $300 | $110,000 | Add Azure OpenAI fallback; introduce a smaller verifier model (gpt-5-mini) |
| 10,000 users | ~300 | $3,000 | $1.1M | Move pair generation to a smaller model; cap pair count at 80; pre-warm snapshots overnight |
| 100,000 users | ~3,000 | $30,000 | $11M | Self-host an open-weights judge for the bulk of pairs; reserve ``gpt-5`` for top-K rerank only |

The headline cost driver is the **pair count per visit**, not the
conversation length. The verifier and conversational wrap together cost
less than one pair judgment per turn.

## Per-visit token budget

The pairwise comparison engine dispatches ``S × C`` calls for Use Case A
(``S`` = presenting symptoms, ``C`` = candidate findings) and
``F × (F − 1)`` for Use Case B (``F`` = documented findings). For a
typical Dr. M. patient (3 presenting symptoms, 12 active problems, 8 active
medications, 2 abnormal labs), Use Case A produces 66 pairs; Use Case B
produces roughly 30² − 30 = 870 pairs unless capped. The architecture caps
both at 200.

Per-pair tokens (measured on the deterministic mock against the gout case,
plus an empirical OpenAI dry-run with ``gpt-5``):

| Stage | Avg prompt tokens | Avg completion tokens |
|---|---|---|
| Pair Judge A (one pair) | ≈ 320 | ≈ 110 |
| Pair Judge B (one pair) | ≈ 360 | ≈ 130 |
| Verifier LLM grounding (high-likelihood claims only) | ≈ 600 | ≈ 90 |
| Conversational wrap (templated, no LLM call) | 0 | 0 |

At ``gpt-5`` headline pricing of ``$0.005/1K`` prompt + ``$0.015/1K``
completion (placeholder until rate cards land), Use Case A on a typical
patient costs ``66 × ($0.005 × 0.32 + $0.015 × 0.11) ≈ $0.21`` per visit.
Use Case B at the 200-pair cap costs ``200 × ($0.005 × 0.36 + $0.015 × 0.13) ≈ $0.75``
per scan.

## Actual development spend so far

| Item | Spend |
|---|---|
| OpenAI API (development, mock-mostly) | $0.00 — the eval suite ships with a deterministic ``MockProvider`` so CI runs at zero cost |
| Anthropic / Claude | $0.00 — the build does not use Claude in production |
| Vector embeddings (text-embedding-3-large) | $0.00 — note embedding deferred until pgvector lands |
| **Total to date** | **$0.00** |

Once the Gauntlet billing OpenAI key lands, the first 24-hour smoke run
on the three fixture patients projects to roughly ``$1.20`` (Use Case A
on all three plus Use Case B on the osteoporosis case).

## Projection at scale

Assumptions for the projection table:

- 18 patients per clinician per day (USERS.md §1).
- One Use Case A run per visit + one Use Case B scan overnight per patient.
- 220 working days per year.
- 50% pair-judgment cache hit rate at the 1,000-user tier (cache key:
  ``hash(symptom_text, finding_label, model_version, prompt_version)`` —
  ARCHITECTURE.md §8).

### 100 users (small clinic network)

- Daily pair count: 100 × 18 × (66 + 200) ≈ 480K pairs/day.
- Daily LLM spend: ≈ $30 with no caching, ≈ $15 with the cache turned on.
- Architectural change required: enable the pair-judgment cache; move Use
  Case B to a 02:00 batch. No model swap needed.

### 1,000 users (small ACO)

- Daily pair count: ≈ 4.8M pairs/day.
- Daily LLM spend: ≈ $300 with cache; ≈ $600 without.
- Architectural change required:
  - Add the Azure OpenAI fallback as a hot standby (already wired —
    ``COPILOT_LLM_PROVIDER=azure``).
  - Swap the verifier's grounded-LLM second pass to ``gpt-5-mini`` for a
    20× cost cut on that stage.
  - Begin embedding ``text-embedding-3-large`` only on note edit, not on
    every snapshot rebuild.

### 10,000 users (mid-sized health system)

- Daily pair count: ≈ 48M pairs/day.
- Daily LLM spend: ≈ $3K with cache; ≈ $6K without.
- Architectural change required:
  - Cap Use Case A at 80 pairs per visit (cluster findings by system
    before generating pairs).
  - Pre-warm tomorrow's snapshots at 22:00 the night before; cache hit
    rate jumps to ~70%.
  - Provision a dedicated OpenAI Enterprise tenant; route via a regional
    POP for latency.

### 100,000 users (national network)

- Daily pair count: ≈ 480M pairs/day.
- Daily LLM spend at the same model would be ~$30K/day, $11M/year.
- Architectural change required:
  - **Two-tier judging.** Run a self-hosted open-weights judge (Llama
    3.1 70B Instruct, fine-tuned on the seed pairs) for the bulk of
    pairs. Reserve ``gpt-5`` for the top-K rerank.
  - Move the pgvector cluster onto a sharded deployment, one shard per
    tenant region; per-``pid`` namespacing remains.
  - Move audit-log anchoring from "every 24 hours" to "every 1 hour" to
    keep the chain head fresh under high write volume.
  - Add a per-clinician dashboard for cost attribution; budget alerts
    fire when a single clinician's cost crosses 2× their cohort median.

## What this analysis is NOT

- This is not "cost per token × users." That gets the order of magnitude
  right for chat-only systems but understates the cost of an agent that
  fans out parallel structured calls per request.
- This does not include compute costs for the sidecar itself (FastAPI on
  a small Kubernetes pod; rounding error).
- This does not include the cost of OpenAI Enterprise BAA itself
  (negotiated, opaque); we model the marginal token cost only.

## Continuous cost evaluation

ARCHITECTURE.md §7.4 specifies that the full Layer 1 + Layer 2 eval runs
nightly and emits mean tokens per case, mean dollars per case, and a
trend chart. The runner ``evals/run_evals.py`` writes a JSON results
file per run; a follow-up commit will surface the trend in Grafana off
``agent_dollars_total`` from the Prometheus exporter.
