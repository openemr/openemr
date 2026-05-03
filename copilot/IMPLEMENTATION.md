# Implementation Status — Clinical Co-Pilot

**Last updated:** 2026-05-03 (resume-previous-chat live on Railway: SQLite-backed conversation history on volume, /v1/sessions/recent + /v1/sessions/resume + /v1/sessions/{id}/end, "Resume previous chat?" banner in iframe verified working; demo-fallback session log demoted from WARNING to INFO so Railway stops painting it as `[error]`)
**Submission targets:**
- ✅ MVP — Tuesday 2026-04-28 11:59 PM CT (audit + users + architecture docs + deployed OpenEMR)
- ✅ **Early Submission — Thursday 2026-04-30 11:59 PM CT** — agent deployed, iframe rail live in OpenEMR, eval+observability wired, demo video recorded. Submission form + AI Interview booking are the only steps left tonight.
- 📋 Final — Sunday 2026-05-03 12:00 PM CT (production-ready, AI cost analysis, social post, secret rotation)

This document tracks what's built, what's blocked, and what's left before each deadline.

---

## 1. Original Plan vs. Reality

### What ARCHITECTURE.md said we'd build

A separate Python (FastAPI) service deployed alongside OpenEMR, integrated as a SMART on FHIR app. The agent uses Anthropic's Claude Sonnet 4.6 via the official SDK, exposes 8 FHIR-backed tools, runs every response through a two-layer verification gate (source attribution + domain rules), and emits Langfuse traces for observability. PHI is pseudonymized before any LLM call.

### What's running right now

```
┌──────────────────────────────────────────────────────────────────┐
│  PHYSICIAN'S BROWSER                                             │
│  https://openemr-production-0c8c.up.railway.app/                 │
│  → click patient → demographics page → Co-Pilot ▸ tab on right   │
│  → 400px iframe rail → https://copilot-production-b532...        │
└──────────────────────────────────────────────────────────────────┘
           │  HTTPS, session token (auto-bound to ?patient_id=<uuid>)
           ▼
┌──────────────────────────────────────────────────────────────────┐
│  COPILOT SERVICE (Railway, this repo's /copilot dir)             │
│   • FastAPI 0.136 / Python 3.11                                  │
│   • OAuth2 password grant against OpenEMR                        │
│   • 8 FHIR-backed tools (5-step pattern)                         │
│   • PHI minimizer (session-scoped pseudonyms)                    │
│   • Two-layer verification gate                                  │
│   • Langfuse wrapper (noop fallback when keys absent)            │
│   • LLM adapter — Anthropic Sonnet 4.6 primary + OpenAI fallback │
│     (FallbackAdapter wraps both; per-turn swap on retryable err) │
└──────────────────────────────────────────────────────────────────┘
           │                        │
           │ FHIR R4 over OAuth2    │ chat.completions.create
           ▼                        ▼
┌─────────────────────────┐  ┌────────────────────────────────────┐
│ OPENEMR (Railway)       │  │ OPENAI gpt-4o                      │
│ openemr-production-...  │  │ (primary; Anthropic adapter ready  │
│ MySQL backing service   │  │  for switch via LLM_PROVIDER env)  │
│ ✅ 10 SYNTHEA PATIENTS   │  └────────────────────────────────────┘
└─────────────────────────┘
```

The agent matches ARCHITECTURE.md, with **Anthropic Claude Sonnet 4.6 as the architectural primary and OpenAI gpt-4o as an automatic per-turn fallback**. Last night Anthropic's billing was rejecting calls (workspace-vs-key mismatch), so we ran the demo on OpenAI alone via `LLM_PROVIDER=openai`. The Anthropic key is working again now, and `app/agent/llm.py` has a new `FallbackAdapter` that wraps both adapters: tries Anthropic at the start of every turn; on a retryable SDK error (`APIStatusError` / `APIConnectionError` / `APITimeoutError`) before any conversation has been built, it transparently swaps to OpenAI for that turn and resets back to Anthropic at the next turn. **Live state on Railway is still `LLM_PROVIDER=openai` (env flip + deploy queued for the user's go-ahead);** once flipped to `anthropic` with the OpenAI key still configured, the wrapper activates and traffic moves to Sonnet 4.6 with OpenAI as the safety net.

The agent is now **embedded inside OpenEMR** via an iframe rail injected into `interface/patient_file/summary/demographics.php`, so the physician never has to leave the chart. The dedicated `https://copilot-production-b532.up.railway.app` URL still works as a standalone surface (useful for the demo video / debugging), but the iframe rail is the production UX and the surface that satisfies the PRD's "AI agent embedded directly into OpenEMR" requirement.

### Architecture trace-back

| ARCHITECTURE.md §  | Component                | Where it lives                                     | Status |
|--------------------|--------------------------|----------------------------------------------------|--------|
| §2.1 Framework     | Custom orchestration     | `app/agent/loop.py`                                | ✅      |
| §2.2 LLM           | Adapters + FallbackAdapter (Anthropic primary → OpenAI fallback) | `app/agent/llm.py`                                 | ✅      |
| §3.1 Tools         | 8 tools, FHIR-backed     | `app/tools/` (8 files + `_base.py` + `registry.py`)| ✅      |
| §3.2 5-step pattern| Shared helper            | `app/tools/_base.py:run_tool`                      | ✅      |
| §3.3 PHI minimizer | Pseudonym + scrub        | `app/phi/minimizer.py`, `app/phi/session.py`       | ✅      |
| §3.4 Trust boundaries| OAuth2 + ACL + verify  | `app/fhir/oauth.py`, `app/acl/check.py`            | ✅      |
| §4.1 Layer-1 verify| Source attribution       | `app/verification/attribution.py`                  | ✅      |
| §4.1 Layer-2 verify| Domain rules             | `app/verification/rules.py`                        | ✅ (2 of 4 rules) |
| §5 Observability   | Langfuse wrapper         | `app/observability/trace.py`                       | ✅ (cloud traces flowing) |
| §6 Evaluation      | pytest harness           | `evals/`                                           | ✅      |
| §10 Deployment     | Railway service `copilot`| `copilot/Dockerfile`, `copilot/railway.toml`       | ✅      |
| §10 Embedding      | iframe rail in OpenEMR   | injected into stock `demographics.php` via awk in repo-root `Dockerfile` (fragment in `copilot-rail-fragment.php`) | ✅ (NEW today) |
| §10 OpenEMR cert   | TLS cert idempotent gen  | `railway-entrypoint.sh` (wraps upstream `./openemr.sh`) | ✅ (NEW today) |

---

## 2. Synthea Integration

We use [github.com/synthetichealth/synthea](https://github.com/synthetichealth/synthea) (MITRE) — the de-facto open-source synthetic-patient generator. Synthea outputs full clinical lifetimes (demographics, encounters, conditions, meds, labs, vitals, immunizations, procedures) in FHIR R4 / CCDA / CSV formats. Synthetic data only — no PHI, suitable for the demo per the PRD's "demo data only" constraint.

### How we run Synthea

We don't compile from source — we use the upstream pre-built JAR + an OpenJDK Docker image so no local Java install is needed:

```bash
# One-time download (~190 MB)
curl -L -o /tmp/synthea-with-dependencies.jar \
  https://github.com/synthetichealth/synthea/releases/latest/download/synthea-with-dependencies.jar

# Generate N patients in CCDA format
docker run --rm \
  -v /tmp/synthea-with-dependencies.jar:/synthea-with-dependencies.jar \
  -v /tmp/synthea-ccda:/output \
  eclipse-temurin:17-jre \
  java -jar /synthea-with-dependencies.jar \
    --exporter.ccda.export=true \
    --exporter.fhir.export=false \
    --exporter.csv.export=false \
    --exporter.baseDirectory=/output \
    -p 10 Massachusetts
# → /tmp/synthea-ccda/ccda/*.xml (one CCDA per generated patient)
```

### Synthea output formats — what we tried and why

| Format | Size/patient | Where we tried | Result |
|---|---|---|---|
| FHIR R4 bundles | ~200 KB | POST to OpenEMR FHIR API | ❌ HTTP 500 — Synthea's resource shapes don't match OpenEMR's FHIR receiver. Known upstream issue. |
| CCDA XML | 100 KB – 7 MB | OpenEMR Carecoordination UI | ✅ Imports successfully on Railway after the volume-perms fix in §4. |
| CSV | ~varies | (not attempted) | Future option for direct-DB load |

**10 alive Synthea patients are imported on Railway OpenEMR** (verified via `GET /apis/default/fhir/Patient?_count=20` returning `total: 10` with name + birthDate + gender populated).

### Patient roster on Railway (post-import, 2026-04-30)

| Name | Encounters | Last encounter | Last lab | Allergies | Notes |
|---|---|---|---|---|---|
| Mariela | 33 | 2026-04-24 | 2024-04-12 | 5 | **Best UC1/UC2 demo** — 47F, LDL 190, chlorpheniramine, creatinine 2.72 |
| Kacie | 39 | 2026-04-15 | 2024-06-12 | 0 | |
| Dana (2y) | 17 | 2026-04-11 | 2022-12-23 | 10 (incl Aspirin) | **UC3 hard-block demo** — Layer-2 fires cleanly on aspirin |
| Kiera | 30 | 2026-04-05 | 2020-03-15 | 0 | |
| Un | 39 | 2026-03-10 | 2019-10-27 | 0 | |
| Qiana | 50 | 2025-09-03 | 2025-08-27 | 0 | |
| Chris | 38 | 2025-06-25 | 2023-06-05 | 0 | |
| Guillermo | 12 | 2024-07-25 | 2024-07-25 | 0 | |
| Clair | 50 | 1999-06-09 | 1998-05-20 | 0 | |
| Tracey | 7 | 1960-11-02 | 1960-11-02 | 0 | Deceased per Synthea |

Vital-signs data is sparse — last vital across all 10 patients is 2017-03-03 (Mariela). Even with the 5-year tool window, most patients won't have current vitals; the agent gracefully reports `data_gaps: ["No recent vitals on file"]` and recommends in-room measurement.

### Eval suite uses synthetic fixtures (not Synthea)

The agent's `evals/agent/test_scenarios.py` uses hand-crafted FHIR fixtures (smaller and more targeted than Synthea output) to exercise the verification gate against specific failure modes. Synthea is the *demo* data source, not the *eval* data source — the architecture document calls this out at §6.2 ("hand-crafted synthetic patients designed to stress specific failure modes").

---

## 3. What's Finished

### Phase A — Skeleton + FHIR plumbing ✅

- FastAPI app with `/healthz`, `/`, `/v1/sessions`, `/v1/chat`, `/v1/patient/{id}/raw`
- httpx-based FHIR client with TLS-verify toggle (`OPENEMR_VERIFY_TLS`) for local self-signed cert
- OAuth2 password grant against OpenEMR (user-scoped client, not system/JWKS — simpler for demo)
- Dockerfile (`python:3.11-slim`, multi-stage), `railway.toml`, local `docker-compose.yml`

### Phase B — Tools, PHI minimizer, ACL ✅

- 8 tools, all using shared 5-step pattern in `app/tools/_base.py`:
  - `get_patient_summary`, `get_active_medications`, `get_recent_labs`, `get_recent_vitals`,
    `get_encounter_history`, `get_allergies`, `get_encounter_note`, `check_drug_interactions`
- PHI minimizer strips name, birthDate→age, address, telecom, SSN, MRN; replaces with session pseudonym `Patient-XXXX` / `Provider-A`
- ACL middleware mirrors OpenEMR's `aclCheckCore` — denies *before* hitting FHIR for missing scopes
- Session pseudonym map in-memory (Redis-ready interface for multi-replica scaling)
- **`get_recent_labs` + `get_recent_vitals` lookback window: 5 years (`LOOKBACK_DAYS = 1825`)**, not 90 days. Synthea generates patients with lifetime timelines; a 90-day window returned empty for every imported patient and made UC2 useless. Tool docstrings reflect "last 5 years"; the SCHEMA.description string still says "last 90 days" in both files — flagged for §6 cleanup but not blocking.
- **Defensive `dosageInstruction` handling in `app/phi/minimizer.py`**: OpenEMR's FHIR returns `dosageInstruction = [[]]` (a list-of-list, non-spec) for some MedicationRequests, which broke the agent on the original `(resource.get("dosageInstruction") or [{}])[0].get("text")` access. Replaced with a `next(... isinstance(x, dict) ...)` defensive iteration. Other minimizer functions still use the old `[0].get(...)` pattern on similar fields (`reaction[0].manifestation`, `referenceRange[0]`, `category[0].coding[0]`); a defensive sweep across them is queued for Final Sunday in §6.

### Phase C — Agent loop + verification ✅

- Provider-agnostic loop (`app/agent/loop.py`) — works with both Anthropic and OpenAI adapters
- Static system prompt with cache_control on Anthropic (cache hits ~30% on repeat sessions in local testing)
- `submit_response` tool forces structured JSON output: `{prose, claims[], data_gaps[]}`
- Layer-1 source attribution: walks every `claim`, verifies its `record_id` is in the union of tool-result IDs; strips unanchored claims and retries once with feedback
- Layer-2 domain rules: cross-patient leakage hard-block, allergy contraindication hard-block (renal-dose and QTc rules deferred to Final Sunday)

### Phase D — Observability + eval ✅

- Langfuse wrapper (`app/observability/trace.py`) — emits one trace per turn including tool sequence, latencies, token counts, verification verdict, ACL checks. Falls back to stdout-structured logging when keys unset.
- **Langfuse cloud now active** — `LANGFUSE_PUBLIC_KEY` + `LANGFUSE_SECRET_KEY` set on Railway copilot service; traces flowing to `https://cloud.langfuse.com` under the *AgentForge Co-Pilot* project. Each agent turn lands as one trace with the tool-call sequence as nested spans, LLM input/output, token counts, and verification metadata. **Important caveat from tonight**: the deps had `langfuse>=2.50` with no upper bound, so pip resolved to v3.x — the v3 SDK dropped the `Langfuse.trace()` method in favor of OpenTelemetry-style spans, which raised `'Langfuse' object has no attribute 'trace'` on every emit. Pinned to `langfuse>=2.50,<3` in both `pyproject.toml` and `Dockerfile` to match the v2 API the wrapper was written against. Updating the wrapper to v3's `start_as_current_span` API is queued for §6.
- Eval harness — pytest with auto-generated `evals/RESULTS.md` summary, `make eval` and `make eval-live` targets
- **17 tests passing**: 7 PHI minimizer, 3 tool integration, 4 verification gate, 3 live LLM scenarios (UC1 happy path, UC1 refusal-on-empty, prompt injection)

### Phase E1 — Local validation ✅

- Local OpenEMR (Docker dev-easy stack) registered as SMART client with user/* read scopes
- Real UC1 question against local OpenEMR returned a verified, cited response (Phil Belford patient): 4 tools chained, every claim with `record_id`, latency 8.9s, `verification_passed: true`
- All three use cases manually tested in local chat UI

### Phase E2 — Railway deployment ✅

- `copilot` service deployed in `refreshing-empathy` project alongside `MySQL` and `openemr`
- Public URL: **https://copilot-production-b532.up.railway.app**
- All env vars set including OAuth client (`<OAUTH_CLIENT_ID>` / `<OAUTH_CLIENT_SECRET>`) registered against Railway OpenEMR
- `/healthz` returns 200; chat UI loads at root
- Deploys triggered via `railway up` from laptop (planned switch to GitHub auto-deploy on Final Sunday — see §6)

### Phase E3 — Iframe rail integration in OpenEMR ✅ (NEW today)

The iframe rail is **surgically injected into the stock OpenEMR image's `demographics.php` at Docker build time** — not by file-overlay. Architecture:

- **Fragment** at `copilot-rail-fragment.php` (repo root): ~52 lines of self-contained PHP+HTML — the `Co-Pilot ▸` toggle button, the `<iframe>`, and inline CSS for the slide-in animation. Uses only `sqlQuery()` + `\OpenEMR\Common\Uuid\UuidRegistry::uuidToString()`, both present in the upstream image.
- **Build step** in `/Dockerfile` (repo root): `awk '/<\/body>/ && !done {while ((getline line < "/tmp/copilot-rail-fragment.php") > 0) print line; done=1} {print}' "$DEMO" > "${DEMO}.new"` — inserts the fragment immediately before `</body>`, sets `apache:apache` ownership and mode 444, then renames over the original. A `grep -q copilot-rail` post-check fails the build if injection didn't land.
- **Patient context flow**: the fragment looks up `patient_data.uuid` for the current `$pid`, formats it, and constructs iframe src `https://copilot-production-b532.up.railway.app/?patient_id=<uuid>`. The copilot's `app/web/index.html` auto-fills the input from `?patient_id=` and fires `startSession()` after 50ms, so the rail opens pre-scoped to the patient. The UUID input + Open chart button are hidden in iframe mode (visible in standalone).
- **Default UI state**: collapsed 36px-wide blue `Co-Pilot ▸` tab on the right edge. Click toggles `body.copilot-open` → 400px iframe slides in, body padding-right animates from 36px to 400px.
- This satisfies PRD page 2's "AI agent embedded directly into OpenEMR".

#### Why awk-injection, not full-file overlay (the version-mismatch detour)

The first attempt was to COPY our fork's complete `interface/patient_file/summary/demographics.php` into the image. It crashed at runtime with:

```
PHP Fatal error: Uncaught Error: Call to undefined method
  OpenEMR\Common\Session\SessionWrapperFactory::getActiveSession()
  in demographics.php:67
```

Our fork's `SessionWrapperFactory.php` adds `getActiveSession()` (line 64); the version baked into `openemr/openemr:latest` doesn't have that method. Overlaying our PHP onto an image with an older PHP class library guarantees breakage everywhere our fork has diverged. The surgical awk-injection sidesteps this entirely: the upstream's demographics.php (compatible with the image's class library) is preserved; only our 52-line iframe fragment is appended. Deferred to §6: pin a specific upstream image tag that matches our fork's PHP, OR move the iframe injection to an event-listener hook so we never touch demographics.php directly.

### Phase E4 — Smoke tests against deployed agent ✅ (NEW today)

| Use Case | Patient | Result |
|---|---|---|
| **UC1** | Mariela | 5 claims all `record_id`-anchored, `verification_passed: true`, PHI pseudonym `Patient-Z3DU` substituted, 12-15s latency. Identifies LDL 190 + chlorpheniramine + missing RxNorm code. |
| **UC2** | Mariela | 2 claims, identifies chlorpheniramine + creatinine 2.72 mg/dL → renal-aware reasoning. Honestly flags "no recent vitals on file — measure in-room". 8s. |
| **UC3** | Dana + aspirin | 3 claims (now also surfacing other meds — cetirizine, epinephrine auto-injector). Layer-2 hard-block fires cleanly. 5-7s. |

### Phase E5 — Railway deploy infrastructure ✅ (NEW today)

Two pieces added at repo root to make OpenEMR redeploys reliable on Railway:

- **`/Dockerfile`** — wraps `openemr/openemr:latest`. Adds the awk-injection step for the Co-Pilot iframe (§E3) and installs the cert-bootstrap entrypoint.
- **`/railway-entrypoint.sh`** — idempotent self-signed TLS cert generator. Runs on every container boot before exec'ing the upstream `./openemr.sh`. Generates `/etc/ssl/certs/webserver.cert.pem` + `/etc/ssl/private/webserver.key.pem` only if missing. Why this exists: Railway redeploys mount the persistent OpenEMR volume on a fresh container instance; the upstream image generates the Apache TLS cert only on first-ever boot, so without this wrapper every redeploy hits `AH00526: SSLCertificateFile … does not exist or is empty` and crashes Apache in a tight restart loop. Discovered tonight when a routine push triggered a CRASHED deployment that wouldn't recover.

### Phase E6 — Langfuse cloud ✅ (NEW today)

Keys provisioned and set as Railway env vars on the copilot service. After the wrapper init, the noop fallback is no longer used; every chat turn lands as one trace under the *AgentForge Co-Pilot* project at `https://cloud.langfuse.com`. End-to-end verified with a UC1 probe against Mariela. The langfuse SDK is pinned to `>=2.50,<3` (v3 dropped the `.trace()` method).

### Phase E7 — `FallbackAdapter` for LLM provider resilience ✅ code only — deploy queued

`app/agent/llm.py` now ships a `FallbackAdapter` class wrapping a primary + secondary `LLMAdapter`. Behavior:

- **Per-turn fallback.** On the first `call()` of every turn (detected by an empty `conversation` list) it tries the primary; on a retryable SDK error (`anthropic.APIStatusError`, `APIConnectionError`, `APITimeoutError`) it logs a warning and swaps to the secondary for the rest of that turn. The next turn (next empty-conversation `call()`) resets back to the primary.
- **Format-safe.** Anthropic and OpenAI use different conversation message shapes (content blocks vs flat tool-call dicts). After the first successful `call()`, the conversation list is in the active provider's format, so the wrapper *never* swaps mid-turn — a retryable error after that point propagates instead of corrupting the message history.
- **Conversation-mutation routing.** `append_assistant`, `append_tool_results`, `append_user_text`, `initial_user_message` all delegate to whichever adapter served the most recent successful `call()` — keeps the message list self-consistent.
- **Factory wiring.** `get_adapter()` returns `FallbackAdapter(AnthropicAdapter, OpenAIAdapter)` when `LLM_PROVIDER=anthropic` *and* both keys are set; otherwise falls through to a single adapter as before. Single-key configs are unchanged.
- **Verified locally.** A 7-case unit test against mocked adapters covered: happy path (primary alone), turn-start fallback, post-fallback method delegation to secondary, turn-boundary reset to primary, mid-turn-error propagation, secondary failure propagation, and non-retryable error propagation. All passed; the test file was deleted after the run since this isn't a regression-eligible behavior we expect to keep re-testing.

**Live state**: Railway copilot still has `LLM_PROVIDER=openai` and `ANTHROPIC_MODEL=claude-sonnet-4-5-20250929`. Activating the new FallbackAdapter is two env-var changes (`LLM_PROVIDER=anthropic`, `ANTHROPIC_MODEL=claude-sonnet-4-6`) plus a `railway up` to ship the code. Queued for the user's explicit go-ahead — see §6 F5.

### Phase F — Final Submission Optimization Sprint ✅ (2026-05-01 evening)

Five workstreams shipped to local Docker (Railway redeploy queued for Saturday after manual setup §6.5):

#### F1 — Latency: cache tool defs + parallelize dispatch + cache write/read trace

- **B.1** `cache_control: {"type": "ephemeral"}` added to the LAST tool definition in `app/agent/llm.py:86-100` (`AnthropicAdapter.call`). System prompt was already cached; this caches the ~1,495 tokens of tool definitions too. 2nd of Anthropic's 4 allowed cache breakpoints.
- **B.2** `app/agent/loop.py:100-130` — sequential `for use in non_submit_uses: await dispatch(...)` replaced with `asyncio.gather(*[_one(u) for u in non_submit_uses])`. `_one` wraps dispatch with timing + per-tool exception catching; exceptions become `is_error=True` payloads.
- **B.3** `tokens_cache_write` field added to `Usage` (`llm.py:33`) and `TurnTrace` (`schemas.py:33`); populated from `usage.cache_creation_input_tokens` in AnthropicAdapter; surfaced in Langfuse via `app/observability/trace.py:75` as `cache_creation_input` alongside `cache_read_input`.
- **Live verification (2026-05-01 evening, local stack):** turn 2 of a same-session conversation shows `tokens_cached=4224` (warm hit). All 3 tools dispatched concurrently — total_latency_ms 7996 vs sum-of-tool-latencies 2974, confirming overlap.

#### F2 — Citation UX: human-readable `display` field

- New optional `display: str | None` on `Claim` (`schemas.py:7-19`) and on `SUBMIT_RESPONSE_TOOL.input_schema` (`schemas.py:55-92`). Required fields stay `["text", "record_id"]`.
- `app/agent/prompt.py` updated with new instruction + 6 examples for the `display` format ("Med: Lisinopril 10mg daily (2024-12-01)", "Lab: LDL 190 mg/dL (2024-04-12)", "Allergy: Aspirin (severe)", "Cond: Type 2 diabetes (active since 2019)", "Enc: Office visit 2026-04-15 — diabetes f/u", "Vital: BP 158/94 mmHg (2025-08-27)").
- `app/web/index.html:222-237` renders `c.display || c.text || c.record_id` as the bold `.claim-label`, with the raw `record_id` shown small + dim in `.claim-rid` below.
- `app/verification/attribution.py` docstring explicitly notes `display` is presentation-only and is NOT validated by the gate — the audit anchor is `record_id`.
- **Live verification:** claims now render as `"Patient: 54yo male"` / `"Enc: Check-up - Sad (2014-02-01)"` / `"Med: Norvasc (no canonical code)"` instead of raw `MedicationRequest/a1ab628e-...`.

#### F3 — CI/CD: GitHub Actions + Railway auto-deploy

- New file `.github/workflows/copilot-ci.yml` (75 lines):
  - **`test` job** (every PR + master push, path-filter `copilot/**`): Python 3.11, `pip install -e ".[dev]"`, `ruff check .`, `pytest evals -v` with dummy API keys (`evals/conftest.py:21` skips `live_llm` marker by default).
  - **`deploy` job** (only on master push, only if `test` passes, with `concurrency: copilot-deploy`): installs Railway CLI via `npm i -g @railway/cli`, runs `railway up --service copilot --detach` with `RAILWAY_TOKEN` from GitHub Secrets.
- `copilot/README.md` adds CI badge + new "Deploy (CI/CD)" section explaining the flow.
- Manual setup (Saturday): add `RAILWAY_TOKEN` GitHub Secret, disable Railway native auto-deploy on the copilot service.

#### F4 — COST.md (PRD page 8 deliverable)

- New file `copilot/COST.md` (179 lines, verified date 2026-05-01).
- Pricing inputs: Sonnet 4.6 $3/$15 per MTok, ephemeral cache write 1.25×, read 0.1×; GPT-4o $2.50/$10 (fallback); Langfuse Hobby/Pro; Railway ~$15/mo per replica.
- Per-turn token model measured from `TurnTrace`: turn 1 (cache write) ~$0.025; turn 2+ (cache read) ~$0.018; avg ~$0.020/turn over a 5-turn session.
- Projection table at 100 / 1k / 10k / 100k physicians (50 turns/day × 250 days), with $/physician/yr ≈ $275 in steady state. ROI framing: $275/physician/yr vs ~$10K/physician/yr saved on chart pre-review = 36×.
- Architectural changes per tier (Pilot → Practice [Redis session store] → Network [SMART backend services / JWKS, multi-region, Haiku routing] → Enterprise [self-hosted Langfuse, Anthropic enterprise contract]).
- Cost-bending levers (cache invalidation, conversation length, tool-result size, verification retry rate, fallback adapter usage).

#### F5 — Multi-physician with SMART auth-code + PKCE (A.1–A.5)

- **A.1** `app/fhir/oauth.py` rewritten. `FhirOAuthClient._tokens: dict[physician_user_id, TokenSet]` replaces the global `_cache`. New methods: `build_authorize_url` (PKCE S256 + state cache), `exchange_code`, `get_token` (with refresh + `_dev_launch` + `_legacy_password_grant` fallback chain), `_password_grant`, `resolve_practitioner_uuid`. New `TokenSet.practitioner_uuid` populated from id_token's `fhirUser` claim.
- New `Settings` fields in `app/config.py`: `oauth_redirect_uri`, `oauth_authorize_path`, `oauth_token_path`, `oauth_refresh_skew_seconds`, `smart_dev_launch_enabled`, `smart_dev_credentials` (JSON map of physician → `{username, password}`).
- **A.2** `app/fhir/client.py` — `get_resource` and `search` take required `physician_user_id` kwarg. Propagated through 5-step pattern in `_base.py` and 7 of 8 tools (check_drug_interactions doesn't touch FHIR). Also fixed §6 F3 in passing — SCHEMA descriptions in `get_recent_labs.py` and `get_recent_vitals.py` now say "last 5 years" not "last 90 days".
- **A.3** `app/main.py` adds three SMART routes:
  - `GET /v1/oauth/launch?iss=&launch=&physician_user_id=` — builds /authorize URL + 302 redirect
  - `GET /v1/oauth/callback?code=&state=` — exchanges code, redirects to `/?physician_user_id=<resolved>`
  - `GET /v1/oauth/dev-launch?physician_user_id=` — demo-safe direct token mint when `SMART_DEV_LAUNCH_ENABLED=true`
- **A.4** `app/acl/check.py` — static `PHYSICIAN_GRANTS` map demoted to diagnostic-only (logged, not blocking). `_base.py:run_tool` runs a runtime probe: `GET /Patient/{active_patient_id}` with the physician's token. 401/403 → ACL denied; success → allowed. Cached on `PseudonymMap.acl_decision` (new field in `phi/session.py`). New: empty `physician_user_id` is hard-denied without probing (`no_physician_user_id` reason).
- **A.5** `FallbackAdapter` docstring at `app/agent/llm.py:268` documents the per-turn concurrency contract — `get_adapter(settings)` is called per turn at `loop.py:126`, so each turn gets a fresh adapter and `_active` is naturally per-request. Do NOT cache adapters at module level (would require `contextvars.ContextVar`).

#### F6 — Per-physician PATIENT scope enforcement (A.7, NEW)

The first cut shipped here was Layer 3 of what eventually became a three-layer defense. The original `Patient.generalPractitioner` approach was **abandoned** mid-execution — OpenEMR's FHIR endpoint never actually exposes that field on Railway (verified live 2026-05-02). The Layer-3 gate that runs in production reads `PHYSICIAN_PATIENT_PANEL` env JSON instead. The two OpenEMR-side gates (Layers 1 + 2) were added 2026-05-02 evening after live-demo testing showed dr_alvarez could still browse out-of-panel patients via the OpenEMR UI even though the agent itself was correctly refusing.

**See §F18 for the full three-layer story.** This section is preserved for historical context only — F18 is the authoritative description of what runs in production.

#### F7 — Test suite expansion

- All 14 prior mockable tests still pass.
- 3 NEW tests in `evals/agent/test_panel_scope.py`:
  - `test_panel_allows_when_general_practitioner_matches` — Patient owned by physician → allowed
  - `test_panel_denies_when_general_practitioner_mismatches` — Patient owned by another physician → `acl_denied: patient_out_of_panel`
  - `test_panel_bypassed_when_practitioner_uuid_absent` — admin (no fhirUser claim) → bypasses
- Total: **17 passed / 3 skipped (live_llm)** in 0.19s.

#### F18 — Three-Layer Per-Physician Scope Defense ✅ (2026-05-02 evening, commit `30d100af3`)

The single most architecturally interesting piece of the project. Per-physician scope ended up being a defense-in-depth design with three independent layers, each catching a different failure mode of the layer below it. None of the layers trusts the others — pulling any one out still leaves the other two intact.

**Why three layers, not one.** The original PRD only required Layer 3 (the AI agent itself refusing to load PHI for out-of-panel patients). Live demo testing on 2026-05-02 surfaced two production gaps that Layer 3 alone couldn't close:

1. OpenEMR's FHIR layer applies **zero** user-based filtering server-side (`src/Services/FHIR/FhirPatientService.php:943`). Any token with `user/Patient.read` returns every patient.
2. OpenEMR's modern patient finder bypasses the legacy `pt_restrict_field` mechanism — `interface/main/finder/dynamic_finder_ajax.php:258` runs raw `SELECT $sellist FROM patient_data WHERE $where` with no provider check. So even with `pt_restrict_field=usertext6` set globally, dr_alvarez could see every patient in the search list and click into Chen's chart directly.

The fix was to extend the design beyond the agent: gate the OpenEMR UI itself, then defend the agent independently.

**The three layers**

| # | Where it lives | Files | What it prevents | Bypass |
|---|---|---|---|---|
| **L1 — UI patient finder scope** | OpenEMR-side, awk-injected at image build | `copilot-finder-scope.php` → `interface/main/finder/dynamic_finder_ajax.php` (line 230 marker). Mutates `$customWhere` to append `AND providerID = <users.id>` before COUNT + SELECT queries. | A logged-in physician can't even **see** out-of-panel patients in the search/finder list. | `admin` username bypasses (no filter applied). |
| **L2 — Demographics chart gate** | OpenEMR-side, awk-injected at image build | `copilot-demographics-gate.php` → `interface/patient_file/summary/demographics.php` (immediately after `require_once globals.php`). Looks up `patient_data.providerID` for the requested `pid`, compares to the logged-in user's `users.id`; mismatch → styled `403` page. | Even if a physician URL-pokes a patient ID directly (or has a stale bookmark), the chart **won't render**. | `admin` bypasses. Patients with NULL providerID fall through (preserves stock behavior for legacy data). |
| **L3 — Co-Pilot agent panel gate** | Co-Pilot-side, runtime | `_verify_patient_in_panel()` in `app/main.py` runs at `/v1/sessions` create, consulting `PHYSICIAN_PATIENT_PANEL` env JSON (`{"dr_alvarez":["uuid_1",...]}`). Mismatch → `HTTPException(403, "patient_out_of_panel")`. | Even if Layers 1 + 2 are bypassed (e.g. the Co-Pilot's standalone URL `?patient_id=<other_pat>`), the **AI agent itself** refuses to load any PHI — no FHIR fetches, no LLM reasoning, no tokens spent. | `admin` (no Practitioner UUID) bypasses. |

**Why awk-injection over forking OpenEMR.** The first attempt (mid-April) was to overlay a forked `demographics.php` into the image, which crashed at runtime because the fork's PHP class library expected methods newer than what `openemr/openemr:latest` ships. The lesson: forking a complex upstream PHP app to add ~25 lines of behavior is the wrong trade — every upstream release has to be re-merged forever. The awk-injection at Docker build time touches only the specific lines we need to change, leaves the rest of the upstream class library intact, and reverts cleanly by removing the awk lines from the Dockerfile. ~25 lines of shell beat ~10k lines of PHP fork.

**Operational model — adding a new physician requires 3 SQL writes + 1 env update.**

1. Create the user via OpenEMR Admin UI (must have non-null `users.npi` for FHIR Practitioner exposure).
2. SQL: `INSERT INTO users_facility (tablename, table_id, facility_id) VALUES ('users', <new_user_id>, <facility_id>);`
3. SQL: `UPDATE patient_data SET providerID=<new_user_id> WHERE pid IN (...)` for their panel.
4. Railway: append the physician's panel UUIDs to `PHYSICIAN_PATIENT_PANEL` env JSON.

The two-write requirement (SQL + env) is the one operational smell. The single most obvious follow-up is a `/admin/sync-panel` endpoint in the Co-Pilot that reads `patient_data.providerID + uuid` directly via FHIR and rebuilds the env at runtime — eliminates the manual env step. Out of scope for this submission; flagged in §6 backlog.

**Verification (post-deploy, against `https://openemr-production-0c8c.up.railway.app`):**

```bash
# Layer 1 — patient finder scope
# Log in as dr_alvarez → click patient finder → list shows only her panel patients.
# Log in as admin → list shows all 10 patients.

# Layer 2 — demographics chart gate
# Logged in as dr_alvarez, paste:
#   /interface/patient_file/summary/demographics.php?set_pid=<chen_only_pid>
# → styled 403 "Patient not in your panel" page.

# Layer 3 — Co-Pilot agent panel gate
curl -i -X POST https://copilot-production-b532.up.railway.app/v1/sessions \
  -H 'content-type: application/json' \
  -d '{"patient_id":"<chen-only-uuid>","physician_user_id":"dr_alvarez"}'
# → HTTP/1.1 403 Forbidden, body {"detail":"patient_out_of_panel"}
```

#### F19 — Resume Previous Chat ✅ (2026-05-03, commits `34a30f6f0` + Dockerfile fix `01d308467` + log demotion `89cb9894e`)

Conversations are now persistent and resumable. When a physician reopens a patient's dashboard within 24h of a prior chat, the iframe asks **"Resume previous chat?"** — Yes rehydrates the prior session (same pseudonyms, prior Q&A re-rendered + replayed into the LLM context), No ends the prior conversation and starts fresh.

**Why it exists.** Physicians naturally interrupt encounters — phone, EHR navigation, cross-coverage. Before this, closing the iframe dropped everything: a fresh `session_id` + empty `conversation: list` per page load. The prior brief had to be rebuilt from scratch on every return. F19 closes that gap without forcing the physician to retype context.

**Persistence model.** Tiny SQLite store (`aiosqlite`) at `/data/copilot.db` on a Railway volume. Two tables:

- `conversations(conversation_id, physician_user_id, active_patient_id, patient_pseudonym, pseudonym_map_json, created_at, last_used_at, ended_at, turn_count)` indexed on `(physician_user_id, active_patient_id, ended_at, last_used_at DESC)`.
- `messages(message_id, conversation_id, turn_index, role, content, claims_json, data_gaps_json, created_at)` ordered by `(turn_index, rowid)` so user→assistant pairs replay deterministically.

`POST /v1/chat` appends each turn (user question + agent prose) and refreshes the `pseudonym_map_json` snapshot. The pseudonym snapshot is the load-bearing piece: without it, a resumed conversation would mint a new `Patient-X9YZ` while stored messages still reference `Patient-A1B2` and the LLM would lose anchoring.

**New endpoints (`app/main.py`):**

| Method | Path | Purpose |
|---|---|---|
| `GET` | `/v1/sessions/recent?physician_user_id=&patient_id=` | Probe for a resumable conversation. Returns `{found, conversation_id, last_used_at, turn_count, patient_pseudonym}`. Filter: `ended_at IS NULL` AND `last_used_at >= now - resume_window_hours` (default 24). |
| `POST` | `/v1/sessions/resume` | Body `{conversation_id}`. Re-runs the A.7 panel gate (panel membership can change between sessions), rehydrates the in-memory `PseudonymMap` from `pseudonym_map_json`, bumps `last_used_at`, returns ordered prior messages with claims + data_gaps preserved. |
| `POST` | `/v1/sessions/{session_id}/end` | Marks `ended_at` so the conversation stops being offered for resume. Called by the "No, start new" path. |

**LLM history replay (compact, not full trace).** `run_turn(...)` accepts `prior_turns: list[PriorTurn]`. When provided, replays each prior turn as `(user_prefix(question) → assistant_text(prose))` pairs (capped at `settings.resume_replay_max_turns = 10` per ARCHITECTURE §9.4). We deliberately do **not** replay the tool_use loop — the verification gate must anchor THIS turn's claims to THIS turn's tool results, never inherit prior `record_id`s. Replayed prose is conversational context, not a citation source.

**Frontend (`app/web/index.html`).** On iframe load with `patient_id` + `physician_user_id` query params, the page calls `probeRecent()` before auto-starting a session. If a hit is returned, `renderResumeBanner()` shows `[Yes, resume] [No, start new]`. Yes → `POST /v1/sessions/resume` then `replayHistory(messages)` re-renders prior turns via the existing `appendUser` / `renderAssistant` helpers (with a `verified ✓` pill but no latency/token metrics — those belong to the original Langfuse trace). No → `POST /v1/sessions/{old}/end` then the existing fresh-session path.

**PHI / audit posture (ARCHITECTURE §3.3, §5.3, §8.1).** Conversation rows can contain physician-typed PHI in the question text (agent prose already uses pseudonyms). This is a new plaintext-at-rest surface, treated as a clinical record:

- SQLite file on Railway volume (encrypted at rest by Railway), file mode 0600 on a 0700 directory, set during `ConversationStore.init()`.
- Conversation content is **never** sent to Langfuse. `app/observability/trace.py:69` continues to log the per-turn question only (PHI-screened). The SQLite store is the resume substrate; Langfuse is the technical trace; OpenEMR's `api_log` remains the HIPAA audit trail.

**Tests** (`evals/persistence/`, 12 new — full suite 28 passed / 3 skipped):

- `test_persistence.py`: round-trip three turns (ordered user→assistant via `rowid`), `find_recent` returns latest unended, respects 24h window cutoff, skips ended conversations, isolated by `(physician, patient)` key, pseudonym snapshot/restore preserves patient + provider tokens, `SessionStore.rehydrate` replaces existing in-memory entry.
- `test_resume_flow.py`: full E2E with FastAPI `TestClient` and a stubbed `run_turn` (so no live LLM cost). Confirms: start → chat → recent (`found:true`) → resume (same pseudonym, ordered messages) → next chat receives `prior_turns`. Plus `end` hides from recent, per-patient isolation, first-turn has no prior_turns, 404 on unknown conversation.

**Infra changes.** `aiosqlite>=0.20` added to `pyproject.toml` and the builder pip line in `Dockerfile`. `/data` mounted via `railway.toml` `[[deploy.volumes]]` + `docker-compose.yml` named volume `copilot-data`. The Dockerfile originally had a `VOLUME ["/data"]` instruction; **Railway rejects `VOLUME` at build time** (`01d308467`) — the volume is wired via service config only, the Dockerfile just ensures the directory exists with `0700`.

**Out of scope (explicit).**
- Multi-replica deployment (one Railway replica per ARCHITECTURE §10.1; in-memory `SessionStore` + per-replica SQLite is fine for v1; Redis-backed sharding is post-MVP).
- Cross-physician resume (keyed strictly on `(physician_user_id, patient_id)` — no shared resume across users).
- Editing or branching prior conversations (read-then-extend only).
- Application-layer encryption of `messages.content` (relies on Railway volume encryption for v1; flagged as a follow-up under ARCHITECTURE §8.1 row §5.5).

**Live verification (post-deploy, 2026-05-03):**

```bash
curl -sS https://copilot-production-b532.up.railway.app/healthz
# → {"status":"ok","service":"clinical-copilot"}

curl -sS "https://copilot-production-b532.up.railway.app/v1/sessions/recent?physician_user_id=x&patient_id=y"
# → {"found":false,"conversation_id":null,"last_used_at":null,"turn_count":null,"patient_pseudonym":null}
```

To trigger the banner end-to-end inside OpenEMR: open a patient's chart, ask one question, close the chart, reopen the same patient's chart with the same physician → "Resume previous chat?" appears (hard-refresh the iframe once if the browser cached the old `index.html`).

**Live status (2026-05-03 post-deploy).** The banner appears and resume works end-to-end against Railway prod — confirmed against multiple patient chart reopens. Conversations persist across iframe reloads and rehydrate with the same patient pseudonym.

**Known issue tracked alongside, not blocking F19.** The OpenEMR-side iframe URL is currently emitted without the `&physician_user_id=…` segment (verified via `GET /?patient_id=…` in `copilot` Railway logs — the param is absent, not just empty). Net effect: every iframe-launched session falls back to `demo_physician_user_id` (`EPU-admin-46`), so:

- F19 conversations are bucketed under `EPU-admin-46` for now, not the actual clinician. Resume works *within* that bucket (which is why the feature looks correct in single-tester demos), but per-physician keying is not yet in effect on the iframe path.
- F18 L3 is similarly neutered on the iframe path — admin bypasses panel enforcement, so L3's gate is effectively only enforcing for direct API hits, not the iframe.

The fix is on the OpenEMR PHP side (`copilot-rail-fragment.php` already passes `&physician_user_id=' . urlencode($_SESSION['authUser'] ?? '')` since commit `efb5eb5f7`, 2026-05-02). Most likely cause: the deployed `openemr` Railway image predates that commit. Diagnostic = view-source the iframe in a logged-in OpenEMR session and inspect `<iframe id="copilot-rail" src="…">`. Fix = `railway up --service openemr --detach`. Tracked as a separate item, owned by the OpenEMR-PHP workstream — not a Co-Pilot bug.

**Log noise fix (`89cb9894e`).** While the iframe-URL issue persists, every session create logs `session … started without physician_user_id — using demo fallback EPU-admin-46`. Railway's log viewer paints WARN as `[error]`, which made the steady-state demo path look like a fault. Demoted to `logger.info` in `app/main.py:start_session` so it's still visible at INFO+ but stops triggering the red `[error]` paint. Behavior unchanged — only the level changed.

---

## 4. Patient Data on Railway OpenEMR — RESOLVED ✅

**The agent is deployed, the iframe rail is live, and the Railway OpenEMR has 10 Synthea patients with full clinical histories.** This unblocks the demo video and the Early Submission.

### Root cause of the original block

The Synthea CCDA upload via Carecoordination was returning HTTP 200 in the browser but logging `mkdir(): Permission denied` server-side. Diagnosis via `railway ssh --service openemr`:

- `sites/default/` and `sites/default/documents/` had mode `dr-x------` (read+execute only, no write bit).
- The Apache process (which runs as user `apache` inside the container) **owned** the directory but had no write bit set.
- This is why Carecoordination's `mkdir()` for the per-patient subfolder under `documents/` failed silently — Apache could `stat` the parent but not create children.

### Fix applied (Option E from previous draft of this doc)

```bash
railway ssh --service openemr
cd /var/www/localhost/htdocs/openemr
chmod u+w sites/default
chmod -R u+rwX,g+rX sites/default/documents
# Sanity-checked with a write probe — returned OK_DOCS_WRITABLE.
```

Idempotent and reversible — only flipped write bits on the documents subtree.

### Import flow that worked

1. User logged in to Carecoordination on `https://openemr-production-0c8c.up.railway.app/`.
2. Uploaded each of the 10 alive Synthea CCDA files from `/tmp/synthea-ccda/ccda/` → Pending Documents.
3. Clicked "Approve as new patient" on each one.
4. Verified via FHIR:
   ```bash
   curl -s -H "Authorization: Bearer $TOK" \
     'https://openemr-production-0c8c.up.railway.app/apis/default/fhir/Patient?_count=20'
   # → total: 10, each with name + birthDate + gender populated
   ```

### Option G — direct MySQL bulk-load (documented but did NOT need to run)

The previous draft of this doc planned a fallback "Option G": dump the 50 already-imported Synthea patients out of local MariaDB and restore them into Railway MySQL via the public proxy. The perm fix made that unnecessary. Keeping the steps below as a reference in case future patient-import work needs them.

```bash
# Dump from local MariaDB (limited to pids 4-53)
docker exec development-easy-mysql-1 mariadb-dump \
  -uopenemr -popenemr openemr \
  --no-create-info --single-transaction --skip-add-locks --skip-comments \
  --where="pid >= 4 AND pid <= 53" \
  patient_data > /tmp/patient_data.sql

# Restore into Railway via public proxy URL pulled from Railway dashboard
mysql --host=shortline.proxy.rlwy.net --port=<PORT> \
      --user=root --password=<MYSQL_PASSWORD> railway \
      < /tmp/patient_data.sql
```

Risks if ever revived: local schema is MariaDB 11.8.6, Railway is MySQL 8.x — column type compatibility is 99%, not 100% (TIMESTAMP precision, ENUM coercion). Foreign-key chain across `users`, `facility`, `lists_categories` may not match between environments. Mitigation: dump+restore `patient_data` first, verify FHIR can find one patient before the dependent tables.

---

## 5. What's Left — Pre-Early-Submission (Tonight)

Submission requires three artifacts together: **GitLab repo link + deployed URL + demo video.** Two of three are already in place.

| # | Track | Task | Owner | Time | Status |
|---|---|---|---|---|---|
| 1 | Data | Synthea data import via Carecoordination perm fix | Auto+User | 30 min | ✅ done — 10 patients |
| 2 | Data | FHIR verification: `Patient?_count=20` returns 10 with names + birthDates | Auto | 5 min | ✅ done — `total: 10` |
| 3 | Code | `dosageInstruction` defensive handling in `phi/minimizer.py` | Auto | 10 min | ✅ deployed in `d0600aa9e` |
| 4 | Code | Lookback window 90d → 5y in `get_recent_labs` + `get_recent_vitals` | Auto | 5 min | ✅ deployed in `d0600aa9e` |
| 5 | Code | iframe rail injection in `interface/patient_file/summary/demographics.php` | Auto | 30 min | ✅ deployed |
| 6 | Code | Auto-bind `?patient_id=<uuid>` in `app/web/index.html` | Auto | 5 min | ✅ deployed |
| 7 | Smoke | UC1 against Mariela on deployed agent | Auto | 5 min | ✅ 5 cited claims, verified |
| 8 | Smoke | UC2 against Mariela on deployed agent | Auto | 5 min | ✅ 2 claims, renal-aware reasoning |
| 9 | Smoke | UC3 against Dana + aspirin on deployed agent | Auto | 5 min | ✅ Layer-2 hard-block fires |
| 10 | Git | Squash commit `feat(copilot): add Clinical Co-Pilot agent service` | Auto | 5 min | ✅ `d0600aa9e` |
| 11 | Git | Push to GitHub master | Auto | 2 min | ✅ |
| 12 | Git | Push to GitLab master (via dual-push origin remote) | Auto | 2 min | ✅ |
| 13 | Git | Verify both repos render `copilot/` + this file in their web UIs | User | 3 min | ✅ both at `da8b10fe2` |
| 14 | Live | End-to-end click test: open OpenEMR → patient → demographics → Co-Pilot tab → expanded → ask UC1 | User | 5 min | ✅ confirmed working in browser |
| 15 | Demo | Decide whether to switch the demo physician from `admin` to a non-admin doctor user (defense-in-depth: ACL middleware should be exercised by a non-superuser; admin bypasses ACL) | User | 10 min | 📋 deferred to §6 — out of tonight's scope |
| 16 | Demo | Record demo video (3–5 min) — see script below | User | 30 min | ✅ recorded |
| 17 | Submit | Fill the Early Submission form (GitLab link + deployed URL + video link) | User | 5 min | 📋 **next** |
| 18 | Interview | Schedule the AI Interview within 24h of submission (PRD page 4 hard gate) | User | 2 min | 📋 **after submit** |

### Demo video script (~3-5 min)

- Open https://openemr-production-0c8c.up.railway.app/, login.
- Click into Mariela's chart (the 47F with 33 encounters). Demographics page opens; the Co-Pilot tab is visible at the right edge.
- Click `Co-Pilot ▸` — rail slides in pre-scoped to Mariela's UUID. Show the `Patient-XXXX` pseudonym in the session bar.
- **UC1**: "Brief me on this patient — who they are, why they're here, what's changed." Point at the cited-claim pills under the response and the "verified ✓" badge.
- **Hallucination demo (optional, 30s)**: ask "Is she also on simvastatin?" — agent should refuse / mark `[unverified]` because no MedicationRequest record_id supports that claim.
- **UC2**: "She's complaining of dizziness — anything I should know?" — agent chains tools and surfaces creatinine 2.72 (renal-aware) plus the `data_gaps: ["No recent vitals on file"]` honesty.
- **UC3**: switch to Dana's chart, ask "I'm thinking about adding aspirin for her — any concerns?" with `proposed_drug=aspirin` — Layer-2 allergy rule must hard-block.
- Click the "full trace" expander on any response — show tool sequence + per-tool latencies + token counts + verification verdict.
- Closing 30 seconds: PHI minimization (pseudonyms in trace, no SSN/name to LLM), source-attribution gate (deterministic, not LLM self-grading), Anthropic primary / OpenAI fallback adapter.

---

## 6. What's Left — Pre-Final-Submission (Sunday)

Final adds production polish + AI cost analysis + social post + secret rotation + minor cleanup.

| # | Task | Effort | Notes |
|---|---|---|---|
| F1 | Layer-2 domain rules: renal-dose check + QTc check | 60 min | ARCHITECTURE.md §4.1 says 4 rules; we have 2 |
| F2 | Defensive sweep across other minimizer functions (`reaction[0]`, `referenceRange[0]`, `category[0].coding[0]` etc.) — same `next(... isinstance(x, dict) ...)` pattern as `dosageInstruction` got today | 30 min | Belt-and-braces against more `[[]]` non-spec FHIR shapes from OpenEMR |
| ~~F3~~ | ~~Align SCHEMA.description strings in `get_recent_labs.py` + `get_recent_vitals.py` to "last 5 years"~~ | ~~5 min~~ | ✅ DONE 2026-05-01 (in passing during A.2 propagation pass) |
| ~~F4~~ | ~~SMART on FHIR app launch handshake (replace password-grant with auth-code + PKCE)~~ | ~~90 min~~ | ✅ DONE 2026-05-01 — Phase F5 §A.1–A.3. Code lives in `app/fhir/oauth.py` + `app/main.py` (`/v1/oauth/launch`, `/callback`, `/dev-launch`). Awaits Saturday OpenEMR-side SMART client registration §6.5. |
| F5 | Activate the `FallbackAdapter` on Railway: set `LLM_PROVIDER=anthropic` + `ANTHROPIC_MODEL=claude-sonnet-4-6`, then `railway up` to ship the code from `app/agent/llm.py` | 5 min | Anthropic billing unblocked. Code merged (Phase E7); env flip + deploy queued for Saturday §6.5. |
| F6 | **Rotate all credentials that touched chat history tonight** — OAuth client secret, OpenEMR admin password (`EPU-admin-46`), and the Langfuse public + secret keys. Re-issue, update `copilot/.env` and Railway env vars, redeploy. | 25 min | New: includes Langfuse keys (added when wiring cloud traces tonight) |
| F7 | Switch the demo physician from `admin` to a non-admin doctor user so the ACL middleware is exercised by a non-superuser (admin bypasses ACL) | 10 min | Deferred from §5 row 15 — covered by the 3 doctor users in §6.5. |
| F8 | Pin `openemr/openemr` image to a specific tag matching this fork's PHP class library — OR refactor iframe injection to an OpenEMR event-listener hook so we never touch `demographics.php` directly | 60 min | Removes the awk-injection workaround; root-cause fix for the version-mismatch failure mode |
| F9 | Update `app/observability/trace.py` to Langfuse v3 SDK (`start_as_current_span` + `update_trace`) and bump pin to `>=3` | 30 min | Currently pinned to v2 because v3 dropped `Langfuse.trace()` |
| ~~F10~~ | ~~Write `COST.md`~~ | ~~30 min~~ | ✅ DONE 2026-05-01 — Phase F4. 179 lines at `copilot/COST.md`. |
| ~~F11~~ | ~~Connect Railway `copilot` service to GitHub auto-deploy~~ | ~~15 min~~ | ✅ DONE 2026-05-01 — Phase F3. `.github/workflows/copilot-ci.yml` with test + deploy jobs. Awaits Saturday `RAILWAY_TOKEN` GitHub Secret + Railway native auto-deploy disable §6.5. |
| F12 | Re-record demo video against the production-quality stack | 30 min | Replaces the Thursday recording |
| F13 | Social media post on X/LinkedIn tagging @GauntletAI | 15 min | PRD page 8 final-only deliverable |
| F14 | Schedule the second AI Interview within 24h of Final submission | 2 min | |
| **F15** | Per-physician PATIENT scope enforcement (Layer 3, Co-Pilot agent gate) | ~~90 min~~ | ✅ DONE 2026-05-01 — Phase F6. Pivoted from `Patient.generalPractitioner` (FHIR doesn't expose it) to `PHYSICIAN_PATIENT_PANEL` env JSON. See §F18 for full three-layer story. |
| **F16** | Latency: cache tool defs + parallelize dispatch | ~~3h~~ | ✅ DONE 2026-05-01 — Phase F1. ~60-80% input cost reduction on turn 2+; tool dispatch sum→max. |
| **F17** | Citation UX: human-readable `display` field on claims | ~~1.5h~~ | ✅ DONE 2026-05-01 — Phase F2. Claims now show "Med: Lisinopril 10mg daily" instead of raw FHIR ids. |
| **F18** | OpenEMR-side scope gates (Layers 1 + 2): UI patient finder + demographics chart 403 | ~~75 min~~ | ✅ DONE 2026-05-02 evening — Phase F18, commit `30d100af3`. Two awk-injected PHP fragments in repo-root Dockerfile; closes the UI gap that Layer 3 alone couldn't reach. |

---

## 6.5 Manual Setup for Saturday 2026-05-02 (~1.5h)

The Phase F code is shipped to local Docker; six manual steps remain before the Railway redeploy can verify multi-physician end-to-end.

1. **Create 3 doctor users in Railway OpenEMR** via Admin → Users → New (`/interface/usergroup/user_admin.php?id=new`): `dr_alvarez`, `dr_chen`, `dr_kumar`. Each must have non-null `users.npi` so OpenEMR exposes them as `Practitioner` in FHIR (required for A.7 panel resolution). Suggested password: `Pilot-2026!`.

   **Verify:** `SELECT id, username, npi FROM users WHERE username IN ('dr_alvarez','dr_chen','dr_kumar');` returns 3 rows, no NULL `npi`.

2. **Set `users_facility` scope** (legacy UI scope only — FHIR ignores this, but it keeps the OpenEMR chart picker clean per doctor):
   ```sql
   INSERT INTO users_facility (tablename, table_id, facility_id)
   VALUES ('users', <alvarez_id>, <facility_id>),
          ('users', <chen_id>,    <facility_id>),
          ('users', <kumar_id>,   <facility_id>);
   ```

3. **Reassign Synthea patients** — the **single source of truth** for the A.7 panel gate:
   ```sql
   UPDATE patient_data SET providerID = <alvarez_id> WHERE pid IN (1,2,3);  -- include Mariela
   UPDATE patient_data SET providerID = <chen_id>    WHERE pid IN (4,5,6);  -- include Dana
   UPDATE patient_data SET providerID = <kumar_id>   WHERE pid IN (7,8,9,10);
   ```
   Verify: `SELECT providerID, COUNT(*) FROM patient_data GROUP BY providerID;` shows 3 doctors with ~3-4 patients each. Future transfers are one UPDATE; Co-Pilot picks up the change on next session-create.

4. **Register a SMART confidential client** with the new redirect URI and broader scopes (the existing client only allows password grant):
   ```bash
   curl -X POST https://openemr-production-0c8c.up.railway.app/oauth2/default/registration \
     -H "Content-Type: application/json" \
     -d '{
       "application_type": "private",
       "client_name": "Clinical Co-Pilot (SMART)",
       "redirect_uris": ["https://copilot-production-b532.up.railway.app/v1/oauth/callback"],
       "token_endpoint_auth_method": "client_secret_post",
       "grant_types": ["authorization_code","refresh_token","password"],
       "response_types": ["code"],
       "scope": "launch openid fhirUser offline_access user/Patient.read user/Observation.read user/MedicationRequest.read user/Condition.read user/Encounter.read user/AllergyIntolerance.read user/DocumentReference.read user/Practitioner.read"
     }'
   ```
   Save returned `client_id` + `client_secret`. Then OpenEMR Admin → System → API Clients → enable + approve all scopes.

5. **Set Railway env vars on the `copilot` service** (Variables tab → Raw Editor, or `railway variables --set ...`):
   ```
   LLM_PROVIDER=anthropic
   ANTHROPIC_MODEL=claude-sonnet-4-5-20250929   # confirm against app/agent/llm.py default
   OAUTH_CLIENT_ID=<new client_id from step 4>
   OAUTH_CLIENT_SECRET=<new client_secret from step 4>
   OAUTH_REDIRECT_URI=https://copilot-production-b532.up.railway.app/v1/oauth/callback
   SMART_DEV_LAUNCH_ENABLED=true
   SMART_DEV_CREDENTIALS={"dr_alvarez":{"username":"dr_alvarez","password":"Pilot-2026!"},"dr_chen":{"username":"dr_chen","password":"Pilot-2026!"},"dr_kumar":{"username":"dr_kumar","password":"Pilot-2026!"}}
   ```
   Railway auto-redeploys (or `railway up` if native auto-deploy is already disabled per step 6).

6. **CI/CD plumbing** (Phase F3):
   - Mint Railway token at https://railway.app/account/tokens (scope to the `openemr` project).
   - Add as `RAILWAY_TOKEN` in https://github.com/rikkiiwang/openemr/settings/secrets/actions/new.
   - Disable Railway's native GitHub auto-deploy on the `copilot` service (Settings → Source → Disconnect, or toggle Auto Deploy off). The CI workflow now owns deploys.

**Sunday verification commands** are in the plan file at `/Users/rikki/.claude/plans/read-copilot-folder-has-witty-shamir.md` §Verification. Key beats:
- Two physicians, concurrent UC1 sessions
- Cross-panel denial: `dr_alvarez` requesting `dr_chen`'s patient → `HTTP 403 patient_out_of_panel`
- Patient transfer: one SQL UPDATE swaps ownership; next session-create reflects the change immediately (no Co-Pilot redeploy)
- Cache hit visible: turn 2 shows `tokens_cached > 0`, `tokens_cache_write` populated on cold cache
- Citations: every claim has non-null `display`

---

## 7. Risk Log

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Anthropic billing not resolved by Final | Medium | Low | OpenAI adapter is shipped, defends as architecture-aware fallback |
| Railway free tier rate limits during demo recording | Low | Medium | Record demo while everything is warmed up; have a backup recording |
| Iframe rail blocked by browser due to mixed-content / X-Frame-Options on Railway | Low | High | Both OpenEMR and Copilot serve HTTPS; Copilot does not set X-Frame-Options DENY. Verified locally; verify on Railway during step 14. |
| Demo recorded as `admin` user, ACL middleware never exercised in the video | Medium | Low | F-tier item is to switch to a doctor user; if not done, mention in voiceover that admin bypasses ACL by design and the ACL gate is exercised in eval `test_tool_integration.py` |
| AI Interview not booked in 24h window | Low | High | Schedule immediately after submission, set a calendar reminder |
| OAuth client secret + admin password were in chat history before redaction | Low | Medium | Rotate Sunday (F6); GitLab/GitHub repos never received literal secrets — `.env` is gitignored |

---

## 8. File Map (where everything lives)

```
/Users/rikki/Desktop/Doc/OOD/openemr/
├── AUDIT.md, USERS.md, ARCHITECTURE.md, AGENTFORGE.md  ← MVP deliverables (locked)
├── README.md (upstream OpenEMR — unchanged)
├── .github/workflows/copilot-ci.yml        ← NEW (Phase F3): test + Railway deploy
├── interface/patient_file/summary/demographics.php    ← +54 lines: Co-Pilot iframe rail
└── copilot/                                ← THE AGENT (this directory)
    ├── IMPLEMENTATION.md                   ← THIS FILE
    ├── README.md                           ← Setup quickstart (with CI badge)
    ├── COST.md                             ← NEW (Phase F4): scaling cost analysis
    ├── pyproject.toml, Dockerfile, railway.toml, docker-compose.yml, Makefile
    ├── .env, .env.example                  ← .env contains live secrets, gitignored
    ├── app/
    │   ├── main.py, config.py              (main.py: +/v1/oauth/{launch,callback,dev-launch}, +panel gate)
    │   ├── fhir/        client.py, oauth.py (oauth.py: per-physician token store, PKCE, dev-launch)
    │   ├── tools/       8 tool files + _base.py + registry.py (_base.py: runtime ACL probe + panel re-check)
    │   ├── agent/       loop.py, llm.py, prompt.py, schemas.py (loop: parallel dispatch; llm: cache_control on tools; schemas: display field)
    │   ├── verification/ attribution.py, rules.py (attribution.py: display is presentation-only docstring)
    │   ├── phi/         minimizer.py, session.py (session.py: +acl_decision cache)
    │   ├── acl/         check.py (static grants demoted to diagnostic-only)
    │   ├── observability/ trace.py (cache_creation_input alongside cache_read_input)
    │   └── web/         index.html (chat UI + ?patient_id auto-bind + display rendering)
    └── evals/
        ├── conftest.py, RESULTS.md         ← auto-generated test summary
        ├── tools/      test_phi_minimizer.py, test_tool_integration.py
        └── agent/      test_verification.py, test_scenarios.py, test_panel_scope.py  ← NEW (Phase F6)
```

### Test Suite Status (post-Phase F)

```
17 passed, 3 skipped (live_llm)  in 0.19s

evals/agent/test_panel_scope.py        ← NEW: 3 tests (panel allow / deny / admin bypass)
evals/agent/test_verification.py       4 tests (attribution + Layer-2 rules)
evals/agent/test_scenarios.py          3 tests (live_llm — skipped unless ANTHROPIC_LIVE=1)
evals/tools/test_phi_minimizer.py      7 tests (PHI strip per resource type)
evals/tools/test_tool_integration.py   3 tests (tool fan-out + ACL deny)
```

---

## 9. Quick Reference — Where Things Are Deployed

| Service | URL | Status |
|---|---|---|
| Co-Pilot agent (Railway) | https://copilot-production-b532.up.railway.app | ✅ live |
| OpenEMR (Railway) | https://openemr-production-0c8c.up.railway.app | ✅ live, 10 Synthea patients |
| MySQL (Railway, internal) | `mysql.railway.internal` | ✅ |
| Local agent (dev) | http://localhost:8080 | ✅ |
| Local OpenEMR (dev) | https://localhost:9300 | ✅ |
| GitHub repo | https://github.com/rikkiiwang/openemr | ✅ master @ d0600aa9e |
| GitLab repo (submission link) | https://labs.gauntletai.com/ruijingwang/openemr | ✅ master @ d0600aa9e |

OAuth clients registered:

| Client | Where | client_id | Purpose |
|---|---|---|---|
| Clinical Co-Pilot (local) | Local OpenEMR | `<OAUTH_CLIENT_ID>` | Local agent → local OpenEMR |
| Clinical Co-Pilot (Railway prod) | Railway OpenEMR | `<OAUTH_CLIENT_ID>` | Deployed agent → Railway OpenEMR |

OpenEMR admin password: `<OPENEMR_ADMIN_PASSWORD>` (rotate Sunday — see §6 F6). OAuth client secret: `<OAUTH_CLIENT_SECRET>` (same — rotate Sunday). Both live in `copilot/.env` and Railway service env vars; never committed.

---

*This document is the source of truth for the Co-Pilot's status. Update it after every milestone completion or new blocker.*
