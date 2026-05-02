# Cost Analysis — Clinical Co-Pilot

**Verified:** 2026-05-01 (pricing snapshots from public docs as of this date — re-verify before quoting externally)
**Scope:** Per-turn token economics, dev-spend reconciliation, scaling projections at 100 / 1K / 10K / 100K physicians, and the architectural changes each tier forces.

This document operationalizes ARCHITECTURE.md §9. The architecture document established the model; this file pins live numbers measured from `trace.tokens_*` fields after the prompt-caching and parallel-dispatch optimizations, and adds the hosting + observability lines that §9 abstracted away.

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

*Maintained alongside ARCHITECTURE.md §9. Update §2 measurements after any change to system prompt, tool definitions, or PHI minimizer; update §4 projections after any pricing change.*
