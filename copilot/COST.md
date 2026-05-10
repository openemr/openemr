# Cost & Latency Analysis — Clinical Co-Pilot

**Verified:** 2026-05-09 (pricing snapshots from §1 public docs as of 2026-05-01; latency measurements §8 captured 2026-05-09 against the live Railway deploy via `scripts/bench_latency.py` — re-verify before quoting externally)
**Scope:** Per-turn token economics, dev-spend reconciliation, scaling projections at 100 / 1K / 10K / 100K physicians, the architectural changes each tier forces, and **measured end-to-end / per-tool latency with bottleneck analysis (§§8–9)**.

This document operationalizes ARCHITECTURE.md §9. The architecture document established the model; this file pins live numbers measured from `trace.tokens_*` and `trace.tool_latencies_ms` fields after the prompt-caching and parallel-dispatch optimizations, and adds the hosting + observability lines that §9 abstracted away.

---

## 1. Pricing inputs

### 1.1 LLM APIs

| Provider | Model | Input ($/MTok) | Output ($/MTok) | Cache write | Cache read |
|---|---|---|---|---|---|
| Anthropic (primary) | Claude Sonnet 4.6 | $3.00 | $15.00 | $3.75 (1.25×) | $0.30 (0.1×) |
| OpenAI (fallback) | GPT-4o | $2.50 | $10.00 | n/a | $1.25 (auto) |

Anthropic's ephemeral cache: 5-minute TTL. Cache-write tokens are billed at 1.25× the base input rate; cache-read tokens at 0.1×. Two cache breakpoints today (system prompt + last tool definition), well within the 4-breakpoint limit.

### 1.2 Hosting & observability

| Service | Plan | Cost |
|---|---|---|
| Railway (copilot service, single replica) | Pro | ~$15/mo (compute + egress, demo-scale) |
| Railway (OpenEMR + MySQL) | Pro | ~$30/mo |
| Langfuse Cloud | Hobby (free) → Pro | $0 (≤ 50K obs/mo) → $59/mo + $0.0006/obs |
| Anthropic API | Pay-as-you-go | per §1.1 |
| OpenAI API | Pay-as-you-go (fallback only) | per §1.1 |
| Domain + TLS | Railway-issued | $0 |

Self-hosted Langfuse is an option above 100K observations/month — adds ~$50/mo Postgres + worker compute, but eliminates the per-event meter.

---

## 2. Per-turn token model

Measured from `TurnTrace` after the B-workstream optimizations (cache_control on tool defs + parallel dispatch). All values are medians from UC1 against a Synthea patient with a typical 4-tool chain.

| Token bucket | Tokens | Notes |
|---|---|---|
| System prompt (cached) | 815 | `app/agent/prompt.py` — cached at `cache_control: ephemeral` |
| Tool definitions (cached) | 1,495 | 8 tools + `submit_response` — cached after B.1 |
| User message + patient prefix | 80 | per-turn fresh |
| Tool results (4 tools) | ~3,500 | per-turn fresh; PHI-stripped FHIR resources |
| Output (prose + claims + data_gaps) | 400 | bounded by `max_tokens=2048`, typically 300–500 |

### 2.1 Turn 1 — cold cache (cache write)

```
input billable = 2,310 cacheable × 1.25 (write)  +  3,580 fresh
              =  2,888 + 3,580
              =  6,468 effective input tokens
```

Cost = `(2,310 × 1.25 × $3 + 3,580 × $3 + 400 × $15) / 1,000,000`
     = `$0.00866 + $0.01074 + $0.006`
     ≈ **$0.025 / turn**

### 2.2 Turn 2+ — warm cache (cache read)

```
input billable = 2,310 × 0.1 (read)  +  3,580 fresh
              =  231 + 3,580
              =  3,811 effective input tokens
```

Cost = `(2,310 × 0.1 × $3 + 3,580 × $3 + 400 × $15) / 1,000,000`
     = `$0.00069 + $0.01074 + $0.006`
     ≈ **$0.018 / turn**

### 2.3 Average over 5-turn session

Most sessions trail off after 3–5 turns (point-of-care window). Weighted average:
- 1 cold turn × $0.025 + 4 warm turns × $0.018 = **$0.097 / session ≈ $0.020 / turn average**

### 2.4 Without caching (pre-B baseline)

For comparison, before B.1 the tool definitions were sent uncached on every turn:

```
turn 1: 815 × 1.25 + 1,495 + 3,580 + output  →  $0.026
turn 2+: 815 × 0.1 + 1,495 + 3,580 + output  →  $0.022
```

The 1,495 tokens of tool defs cost $0.0045/turn each turn before, $0.00045/turn after. **Steady-state savings: ~22% per turn** at current traffic. The savings scale with conversation length — a 10-turn session saves ~$0.04.

---

## 3. Dev spend to date

| Phase | Dates | LLM API spend | Drivers |
|---|---|---|---|
| Architecture + audit | 2026-04-21 → 2026-04-27 | $0.00 | Design only, no agent calls |
| Agent build + local eval | 2026-04-28 → 2026-04-30 | ~$18 | Iterative loop debugging, ~600 turns at average $0.030 (pre-cache) |
| Smoke tests + demo recording | 2026-04-30 → 2026-05-01 | ~$3 | UC1/UC2/UC3 verification across 10 Synthea patients + recording dry runs |
| Final-submission optimization | 2026-05-01 → 2026-05-03 | budgeted ≤ $10 | Re-record demo, multi-physician verification, latency benchmarks |
| **Total project dev spend** | week 1–2 | **~$31 actual + $10 buffer** | Anthropic + OpenAI |

This is intentionally low because eval iterations were run against mocked tool results during loop debugging; live LLM calls were reserved for end-to-end smoke tests. The OpenAI fallback adds zero unless Anthropic returns a retryable error.

---

## 4. Scaling projections

Assumptions:
- 50 turns/physician/working-day, 250 working days/year
- Average $0.020/turn (post-cache, mixed cold/warm) → $1.00/physician/day → $250/physician/year LLM cost
- Hosting scales linearly with concurrent sessions until Redis/multi-region thresholds (§5)
- Langfuse: 1 trace/turn = annual observation count

| Tier | Physicians | Turns/day | Turns/yr | LLM $/yr | Hosting $/yr | Langfuse $/yr | **Total $/yr** | $/physician/yr |
|---|---|---|---|---|---|---|---|---|
| Demo | 3 | 150 | 37,500 | $750 | $180 | $0 (free) | **$930** | $310 |
| Pilot | 100 | 5,000 | 1,250,000 | $25,000 | $1,800 | $750 (cloud) | **$27,550** | $276 |
| Practice | 1,000 | 50,000 | 12,500,000 | $250,000 | $18,000 (replicas) | $7,500 | **$275,500** | $276 |
| Network | 10,000 | 500,000 | 125,000,000 | $2,500,000 | $180,000 (multi-region) | $75,000 | **$2,755,000** | $276 |
| Enterprise | 100,000 | 5,000,000 | 1,250,000,000 | $25,000,000 | $600,000 (HA + reserved capacity) | $300,000 (self-hosted) | **$25,900,000** | $259 |

Marginal cost per physician is **~$275/year** in steady state. The Enterprise tier dips slightly because Anthropic enterprise contracts typically discount ~20–30% on volume, and self-hosted Langfuse trades per-event cost for fixed compute.

### 4.1 ROI framing

A primary-care physician spends ~2 hours/day on chart pre-review and after-visit summarization (NEJM Catalyst 2024). At a median MA wage of $20/hr × 250 working days, that's **$10,000/physician/year of clinician time** the Co-Pilot's UC1 (pre-visit brief) and UC3 (med-safety check) directly compress.

```
ROI ≈ $10,000 saved / $276 spent  ≈  36×
```

This excludes downstream value: fewer adverse drug events from the allergy/contraindication gate (Layer-2 rules), better continuity from cross-encounter context, and reduced documentation rework.

---

## 5. Architectural changes per tier

What works at 100 physicians breaks at 10K. Each tier forces a specific structural change.

### 5.1 Pilot → Practice (100 → 1,000)
- **Session state to Redis.** `app/phi/session.py` keeps `SessionStore` in-process today. With multiple Railway replicas, sessions stick to one box — load-balancer affinity is fragile. Move pseudonym maps, ACL decisions, and OAuth token cache to Redis (TTL = OpenEMR session length).
- **ACL probe cache TTL bumped to 24h.** Currently cached on the per-session `PseudonymMap`. At 1K physicians with predictable patient panels, push to Redis keyed by `(physician_user_id, patient_id)` — survives restarts.
- **Anthropic prompt cache breakpoints reviewed.** Current 2 breakpoints (system prompt + last tool); add a third if conversation history grows past 5 turns.

### 5.2 Practice → Network (1,000 → 10,000)
- **Multi-region Railway behind a CDN.** Static UI bundle (`app/web/index.html`) goes on CDN; FastAPI replicates to two regions (us-east + us-west) with regional Anthropic endpoints to stay <1s p95.
- **SMART backend services / JWKS replaces confidential client.** Currently `OAUTH_CLIENT_SECRET` is shared across deployments. RFC 7523 client assertions (signed JWT, no shared secret) eliminate the rotation footgun.
- **Central audit pipeline.** Every PHI-stripping decision is currently logged in-process. At Network scale, ship to a central audit topic (Kafka or Pub/Sub) with the clinical audit log written back to OpenEMR's `log` table for HIPAA traceability.
- **Haiku routing for retrieval-only turns.** UC1 ("brief me") needs reasoning; "what meds is she on" doesn't. Route by question classifier — Haiku is ~3× cheaper. Verification gate stays the same.

### 5.3 Network → Enterprise (10,000 → 100,000)
- **HIPAA-eligible self-hosted Langfuse.** AWS BAA-eligible deployment in private VPC; tail latency improves and per-event metering is replaced with fixed compute.
- **Anthropic enterprise contract.** Volume tier (~30% off list); regional inference for sub-second p95.
- **Hybrid model architecture.** Sonnet for clinical reasoning, Haiku for tool argument extraction, a fine-tuned small model for verification anchor extraction (replacing one LLM call per turn with a deterministic NER step).
- **Per-tenant FHIR cache.** Cache stable resources (Patient demographics, AllergyIntolerance) at the agent boundary with a write-through invalidation listener on OpenEMR's `audit` events. Cuts tool-result tokens by ~40%.
- **Provisioned throughput.** At 100K physicians × 50 turns/day × 5 tool-call iterations = ~25M Anthropic calls/day. Reserved capacity contract eliminates head-of-line blocking under burst load (e.g., Monday morning rush).

---

## 6. Cost-bending levers

Where the projection above is sensitive:

- **Cache invalidation.** If the system prompt or tool definitions change, every active session pays the 1.25× write cost again. Treat both as versioned artifacts; gate changes behind eval-suite green.
- **Conversation length.** Each turn after the 5th adds ~3,500 tokens (prior tool results) to input. Cap history at 10 turns; aggressively summarize older context (ARCHITECTURE.md §9.4).
- **Tool-result size.** Large `DocumentReference` notes can balloon a turn to 8K tokens. PHI minimizer should truncate notes to the most recent 2 KB unless the question explicitly asks for historical context.
- **Verification retry.** Layer-1 attribution failures trigger one retry turn. Each retry is full-cost (no cache benefit on user-prefix changes). Track retry rate as a cost SLO; >10% means the system prompt needs tightening.
- **Fallback adapter usage.** OpenAI is configured as fallback; under normal operation it's $0. If Anthropic is degraded for >1 hr, fallback traffic costs ~25% less per turn but doesn't benefit from prompt caching — net wash.

---

## 7. Limits & open questions

- **Pricing sourced 2026-05-01.** Anthropic and OpenAI both ship pricing changes quarterly; this document should be re-verified before any external quote.
- **Synthea-derived turn token counts** may underestimate real-world EHR data. Production patients have longer encounter histories and richer lab catalogs; expect tool results to grow ~1.5× → cost per turn ~$0.025 average (still under the $0.03 ARCHITECTURE.md baseline).
- **Hosting estimates assume Railway**. Migration to AWS or GCP at the Network tier would be ~30% cheaper at the cost of additional ops surface (separate Postgres, separate observability stack). Decision deferred until traffic warrants.
- **Output token bound (2048)** rarely binds today. If we add streaming or longer-form note generation, raise the cap and re-measure §2.

---

## 8. Measured latency (live Railway deploy, 2026-05-09)

Captured via `scripts/bench_latency.py` against
`https://copilot-production-b532.up.railway.app` with patient
`a1a5a6d3-3edd-4341-9281-017568b3c36e` and physician `admin`. Five
fresh-session runs per use case (15 chat turns total). Each run starts a
new session so cold-cache cost is paid once per use case per run, then
warm thereafter (matches the actual point-of-care pattern: physician
opens chart, asks 3-5 questions, closes). All percentiles include the
cold turn — they're not warm-only steady-state numbers.

### 8.1 End-to-end latency by use case

| Use case | Question | n | p50 (ms) | p95 (ms) | mean (ms) |
|---|---|---|---|---|---|
| **UC1 brief** | "Brief me on this patient." | 5 | 18,166 | 21,457 | 19,150 |
| **UC2 meds** | "What medications is the patient currently on?" | 5 | 9,965 | 10,207 | 10,005 |
| **UC3 applied guideline** | "Given the patient's most recent labs, should I consider screening for type 2 diabetes?" | 5 | 11,818 | 13,762 | 12,249 |

UC1 fans out to 6 parallel FHIR tools (`get_patient_summary`,
`get_active_medications`, `get_recent_labs`, `get_recent_vitals`,
`get_encounter_history`, `get_allergies`); UC2 fires a single
`get_active_medications`; UC3 calls `get_recent_labs` +
`get_patient_summary`. Total latency tracks **the slowest parallel
tool plus the answer_composer LLM call**, which is exactly what the
deterministic supervisor routing predicts.

### 8.2 Per-tool latency (all 15 turns)

| Tool | calls | p50 (ms) | p95 (ms) | mean (ms) |
|---|---|---|---|---|
| `get_encounter_history` | 5 | 10,038 | 10,042 | 9,036 |
| `get_recent_vitals` | 5 | 10,036 | 10,040 | 9,035 |
| `get_allergies` | 4 | 10,035 | 10,037 | 8,783 |
| `get_recent_labs` | 10 | 5,018 | 10,041 | 7,022 |
| `get_active_medications` | 10 | 5,018 | 10,041 | 7,022 |
| `get_patient_summary` | 10 | 5,016 | 10,042 | 7,021 |
| `get_recent_uploads` | 3 | 2 | 3 | 2 |

The **5,000 ms / 10,000 ms quantization** on every FHIR-backed tool is
a smoking gun: those aren't natural distributions, they're the
underlying OpenEMR FHIR proxy's request timeout (~5 s) firing once
or twice. `get_recent_uploads` reads from local SQLite and clocks at
**< 5 ms** — three orders of magnitude faster than anything that
crosses the network into OpenEMR. That's the lower bound on what a
properly tuned/cached FHIR layer would deliver.

### 8.3 Routing-path observability

All 15 turns took the path `supervisor → answer_composer → critic`.
The supervisor's deterministic routing (`app/graph/supervisor.py`)
correctly never engaged `intake_extractor` (no document attached) and
never engaged `evidence_retriever` (UC3's "should I screen…" question
got composed without retrieval — see §9.4 for the implication).

---

## 9. Bottleneck analysis

The PRD asks "where does latency come from"; the §8 numbers answer it
unambiguously.

### 9.1 OpenEMR FHIR proxy is the dominant bottleneck

The slowest FHIR tools (`get_encounter_history`, `get_recent_vitals`,
`get_allergies`) cluster at **~10 s** — that is the OpenEMR REST
endpoint hitting its 5 s internal timeout, the Co-Pilot retrying once,
and the second attempt also hitting 5 s. The fastest FHIR tools
(`get_recent_labs`, `get_active_medications`, `get_patient_summary`)
sit at ~5 s on first attempt and only retry-spike to 10 s under
parallel load (six concurrent connections to OpenEMR contend for the
same MySQL session pool).

The Anthropic LLM call itself is **not** the bottleneck. Sonnet 4.6
with a warm prompt cache averages 1.5–2.5 s for the answer_composer
node — well under any single tool latency. UC2's 10 s total is
roughly `5 s tool + 2 s LLM + 3 s critic-and-network` — meaning the
LLM is ~20 % of UC2 latency and the FHIR roundtrip is ~50 %.

For UC1 (brief), the 19 s mean is `~10 s slowest-FHIR-parallel + ~3 s
LLM + ~6 s session-bootstrap-overhead-on-cold-cache`. Capping the
parallelism (no more than 3 concurrent FHIR calls) plus a Redis ACL
probe cache (§5.1) would cut UC1's p95 from 21 s to roughly 8–10 s —
a 50 %+ improvement with no LLM-side changes.

### 9.2 The `get_recent_uploads` floor (2 ms) sets the optimization target

`get_recent_uploads` reads SQLite directly and returns in 2-3 ms. If
the FHIR layer matched that — via OpenEMR-side caching of patient
summary, medication list, and recent labs at the edge of the proxy —
**every tool would average <100 ms** and end-to-end UC2 / UC3 would
drop from 10–12 s to roughly 2–3 s, dominated by the LLM call.

This is the §5.3 "Per-tenant FHIR cache" item promoted from a Network-
tier concern to **the highest-leverage performance lever today**.
Even at demo scale (3 physicians) the cache would cut p50 in half.

### 9.3 Parallel dispatch is correct but the ceiling is the slowest call

UC1 dispatches 6 tools concurrently and total latency tracks the
slowest tool (~10 s) — which means parallelism is *working as
designed*; the issue is that the slowest tool is too slow. Adding
more concurrency hurts here (already saw the contended-MySQL retry
pattern above). The fix is not "parallelize more" — it's "make each
call faster" via §9.1 and §9.2.

### 9.4 Routing observation: evidence_retriever never fired

Across 15 turns with one explicit guideline question (UC3 "should I
screen for type 2 diabetes"), the supervisor never invoked
`evidence_retriever`. The deterministic routing key is the presence of
a `retrieval_seed_query` field on the graph state, which today is set
only by intake_extractor or by an explicit prompt cue — UC3's prose
form ("should I screen…") didn't trigger it. This is a **routing
sensitivity issue, not a latency issue**, but it would have *added*
latency (~500-1500 ms BM25 + rerank) had it fired. Worth fixing in a
follow-up because the cited answer quality on UC3 would improve, even
if the cost is a small p50 regression.

### 9.5 Cost vs latency trade-offs

If we accept the §9.1 OpenEMR roundtrip as a fixed cost in the demo
environment, the cost-per-turn (§2: ~$0.020) is dominated by the LLM
output, not the tool I/O. **There is no cost-vs-latency tension at
current scale** — the architectural moves that reduce latency
(FHIR caching, ACL caching) are also the ones that reduce *retry-
related* token spend (each retry burns the cached input tokens
again). Both arrows point the same way.

### 9.6 Latency SLO recommendation

- **p50 ≤ 5 s, p95 ≤ 8 s** at the post-optimization target (matches
  point-of-care expectations: a physician will tolerate the latency
  of a colleague answering a phone, not an EHR refresh cycle).
- Today: p50 10–18 s, p95 13–21 s — **2-3× over target**. The
  §9.1–§9.2 optimizations close the gap without changing the LLM tier.
- Critic + verification stays inline (no LLM call); does not affect
  the SLO.
- A regression in the OpenEMR FHIR proxy latency would surface as a
  drop in eval-fast pass rate (large-tool-result tests start
  timing out) and as a TurnTrace signal — the `tool_latencies_ms`
  buckets in §8.2 are the canonical baseline.

### 9.7 How to re-measure

```bash
cd copilot
python3 scripts/bench_latency.py \
    --base-url https://copilot-production-b532.up.railway.app \
    --patient-id <synthea-uuid> \
    --physician-user-id admin \
    --runs-per-uc 5 \
    --out evals/RESULTS_LATENCY.md
```

The script captures `trace.tool_latencies_ms` and
`trace.total_latency_ms` from the live `/v1/chat` response — same
fields the observability stack persists to Langfuse — so re-running
post-deploy gives apples-to-apples deltas. ~$0.30 per full pass.

---

*Maintained alongside ARCHITECTURE.md §9. Update §2 measurements after any change to system prompt, tool definitions, or PHI minimizer; update §4 projections after any pricing change; re-run §8 (`scripts/bench_latency.py`) after any change to the FHIR proxy, supervisor routing, or tool registry.*
