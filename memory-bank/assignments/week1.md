# Week 1 — AgentForge Clinical Co-Pilot

**Spec:** `~/Desktop/Gauntlet/Week1-AgentForge/Week 1 - AgentForge.pdf`
**Window:** 2026-04-21 → 2026-05-04
**Status:** ✅ **Complete** — all four checkpoints submitted on time.

---

## 1. Original requirements (verbatim from PRD)

### Schedule (PRD p.4)

| Checkpoint | Deadline | Focus |
|---|---|---|
| Architecture Defense | 24 hours after kickoff | Architecture research and planning |
| **MVP** | Tuesday 2026-04-28 11:59 PM CT | App audit, agent plan, deployed app, demo video. AI Interview required 24h after submission. |
| **Early Submission** | Thursday 2026-04-30 11:59 PM CT | Deployed agent, eval framework, observability wired, demo video. AI Interview required 24h after. |
| **Final** | Sunday 2026-05-03 12:00 PM CT (PRD also quotes 10:59 PM CT in submission table — both treated as binding) | Production-ready agent, demo video, social media post. AI Interview required 24h after. |

### Five-stage MVP recipe (PRD p.4–6)

1. **Run It Locally** — OpenEMR running locally with sample patient data
2. **Deploy It** — publicly accessible deployment of the OpenEMR fork
3. **Audit It** — full audit (security / performance / architecture / data quality / compliance) with ~500-word summary in `AUDIT.md`
4. **Identify Users** — `USERS.md` with target user, workflow, use cases (every agent capability traceable here)
5. **Plan the Agent** — `ARCHITECTURE.md` AI integration plan with ~500-word summary

### Required agent components (PRD p.7–8)

- **Agentic chatbot** — multi-turn, tool-calling; capabilities must trace to a `USERS.md` use case
- **Verification system** — source attribution + domain constraint enforcement, with documented limits
- **Observability** — must answer: what did the agent do and in what order; how long did each step take; did any tools fail; tokens consumed at what cost
- **Evaluation** — test suite that surfaces failure modes, regression risks, edge cases (missing data, ambiguous queries, unauthorized access attempts)

### Submission deliverables (PRD p.8)

GitHub repo, `AUDIT.md`, `USERS.md` (or `USER.md` per spec), `ARCHITECTURE.md`, demo video (3–5 min), eval dataset, AI cost analysis (100/1K/10K/100K users), deployed app, social post (Final only), AI Interview booking.

### Hard gates

- Audit-first (no agent code before `AUDIT.md` exists)
- Submit deployed URL with every checkpoint
- Demo data only (Synthea synthetic patients); BAA assumed with all LLM providers per cohort guidelines

---

## 2. What was implemented

### 2a. Documents (repo root)

| Deliverable | File | Notes |
|---|---|---|
| Audit | `AUDIT.md` | 5 sections (security / perf / arch / data quality / compliance) + ~500-word executive summary. Top finding: no PHI de-identification anywhere. |
| Users | `USERS.md` | Target user (PCP), 3 use cases (UC1/UC2/UC3), explicit out-of-scope table, AUDIT trace-back matrix |
| Architecture | `ARCHITECTURE.md` | 12 sections + §12 trace-back matrix + ~500-word summary |
| Project README | `README.md` | Lists W1 deliverables + live URLs |

### 2b. OpenEMR-side integration (repo root)

| File | Responsibility |
|---|---|
| `Dockerfile` | Railway image; `awk`-injects `copilot-rail-fragment.php` into stock `demographics.php` at build; `grep -q copilot-rail` post-check fails build if injection didn't land |
| `railway-entrypoint.sh` | Wraps upstream `openemr.sh`; idempotent TLS cert generation each container boot |
| `copilot-rail-fragment.php` | ~52-line PHP+HTML fragment: `Co-Pilot ▸` toggle, iframe, inline CSS for slide-in animation. Uses only `sqlQuery()` + `\OpenEMR\Common\Uuid\UuidRegistry::uuidToString()` |
| `copilot-demographics-gate.php` | Per-patient scope check on demographics page (layer 1 of B6) |
| `copilot-finder-scope.php` | Patient finder filter by clinician panel (layer 2 of B6) |

### 2c. Co-Pilot service (`copilot/`)

Phases A → E3, plus Phase F final-submission optimization.

| Module | Files | What it does |
|---|---|---|
| Entry | `app/main.py` | FastAPI; routes `/healthz`, `/v1/sessions`, `/v1/sessions/{recent,resume,end}`, `/v1/chat`, `/v1/patient/{id}/raw` |
| Config | `app/config.py` | env-driven settings |
| FHIR | `app/fhir/{oauth.py, client.py}` | OAuth2 password grant + httpx client + TLS toggle |
| Tools | `app/tools/{_base.py, registry.py}` + 8 tool files | 5-step pattern (B3) — see systemPatterns.md for inventory |
| PHI | `app/phi/{minimizer.py, session.py, log_filter.py}` | Strip / pseudonymize / log filter |
| Verification | `app/verification/{attribution.py, rules.py}` | Layer-1 attribution + Layer-2 rules |
| Agent | `app/agent/{loop.py, llm.py, prompt.py, schemas.py, prewarm.py}` | Orchestration loop, FallbackAdapter, system prompt, schemas, pre-warm |
| ACL | `app/acl/check.py` | aclCheckCore mirror |
| Observability | `app/observability/trace.py` | Langfuse wrapper + `_NoopTracer` fallback |
| UI | `app/web/index.html` (+ `static/`) | Standalone chat UI; auto-binds to `?patient_id=` for iframe |
| Evals | `evals/{agent, tools, persistence, fixtures}` + `conftest.py` | 17 → 42 tests by Final |
| CI | `.github/workflows/copilot-ci.yml` | ruff + pytest gate on `copilot/**` |
| Cost | `copilot/COST.md` | Actual + projected spend (100 / 1K / 10K / 100K users) |

### 2d. Required-component map

| PRD requirement | Implementation |
|---|---|
| Agentic chatbot (multi-turn, tool-calling) | `app/agent/loop.py` |
| Verification — source attribution | `app/verification/attribution.py` |
| Verification — domain rules | `app/verification/rules.py` |
| Observability | Langfuse Cloud + `app/observability/trace.py` |
| Eval suite | `copilot/evals/` (42 tests at Final) |
| AI cost analysis | `copilot/COST.md` + `ARCHITECTURE.md §9` |
| Deployed application | OpenEMR + Co-Pilot on Railway (URLs in `techContext.md`) |

---

## 3. Key code changes (high-impact commits, chronological)

| Commit | What |
|---|---|
| `9291f99db` | Add Week 1 deliverables: AUDIT, USERS, ARCHITECTURE |
| `d0600aa9e` | feat(copilot): add Clinical Co-Pilot agent service (Phase A→D landed in this single commit) |
| `937f42a83` | feat(patient-summary): embed Clinical Co-Pilot iframe rail |
| `8a42c1347` | revert(deploy): drop demographics.php overlay due to fork/image version mismatch |
| `da8b10fe2` | feat(deploy): inject Co-Pilot iframe rail via awk into stock demographics.php (the fix to the previous revert) |
| `eb94bb590` | feat(copilot): A.7 panel — env-driven primary path (`PHYSICIAN_PATIENT_PANEL`) |
| `30d100af3` | feat(deploy): per-physician UI scope via finder + demographics gate |
| `34a30f6f0` | feat(copilot): resume previous chat for (physician, patient) |
| `d682c4da8` | perf(prewarm): cut first-turn latency by pre-fetching FHIR tools on session open |
| `1c8673f6e` | feat(copilot): final-submission optimization — multi-physician scope, latency, citation UX, CI/CD, COST.md |
| `50d8618b9` | test(evals): expand suite to 42 tests + add suite README |
| `f88ed610a` | chore(ci): drop deploy job; keep test gate |

---

## 4. Problems encountered & how they were resolved

| Problem | Symptom | Resolution |
|---|---|---|
| `demographics.php` full-file COPY broke after deploy | `Call to undefined method` at runtime — fork/upstream image version mismatch | Switched to `awk` injection of a self-contained 52-line fragment; only patches the line that needs patching. Build-time `grep -q` post-check guards regression. (`8a42c1347` revert → `da8b10fe2` fix) |
| Anthropic billing rejected calls demo-night | Workspace-vs-key mismatch on Anthropic side | Added `FallbackAdapter` (`app/agent/llm.py`) that swaps Anthropic→OpenAI per turn on retryable SDK errors. Demo ran on OpenAI alone via `LLM_PROVIDER=openai` |
| First-turn latency too high (~15s cold) | UC1 felt sluggish | Added `prewarm.py` that pre-fetches FHIR tools on `/v1/sessions` open. Warm = ~3s |
| Synthea FHIR import returned HTTP 500 | Resource shapes don't match OpenEMR's FHIR receiver (known upstream bug) | Switched to CCDA import path; 10 patients imported on Railway |
| Vital-signs data sparse | 90-day lookback returned empty for most Synthea patients | Widened `LOOKBACK_DAYS` to 1825 (5 years). Tool docstrings updated; SCHEMA `description` strings still drift to "last 90 days" — flagged in `progress.md` |
| OpenEMR's FHIR `dosageInstruction` returned `[[]]` (list-of-list, non-spec) | Minimizer crashed on `(resource.get("dosageInstruction") or [{}])[0].get("text")` | Replaced with defensive `next(... isinstance(x, dict) ...)`. Other minimizer fields with the same risk pattern are tracked in `progress.md` |
| Langfuse v3 SDK auto-resolved by pip | `'Langfuse' object has no attribute 'trace'` — v3 dropped `Langfuse.trace()` | Pinned `langfuse>=2.50,<3` in both `pyproject.toml` and `Dockerfile`. v3 migration to `start_as_current_span` API queued |
| CI deploy job double-fired with Railway auto-deploy | Two parallel deploys per push | Dropped CI deploy job (`f88ed610a`); kept test gate |
| `admin` literal bypass too coarse | Hardcoded string check | Replaced with env-list bypass (`f04657d65`) |
| Iframe panel had a redundant tool-layer A.7 re-check | Duplicated gating logic | Dropped tool-layer check; trust `/v1/sessions` gate alone (`1d102d0c5`) |
| `physician_user_id` propagation through iframe | Initially could not pass logged-in clinician's username to copilot reliably | Resolved per `f5b385f97`; iframe now passes the clinician's username (`efb5eb5f7`) |
| Demo-fallback session log noise on Railway | Painted as `[error]` in Railway dashboard | Demoted to INFO (`89cb9894e`) |

---

## 5. Unfinished or compromised

| Item | Status | Why deferred |
|---|---|---|
| Renal-dose Layer-2 rule | Not shipped | Out of W1 scope per `ARCHITECTURE.md §4.2` plan; `progress.md` tracks |
| QTc-prolongation Layer-2 rule | Not shipped | Same as above |
| Defensive iteration sweep across `phi/minimizer.py` | Partial | `dosageInstruction` fixed; `reaction[0].manifestation`, `referenceRange[0]`, `category[0].coding[0]` still use the brittle `[0].get(...)` pattern |
| Langfuse v3 migration | Pinned `<3` | Wrapper still on v2 API; migration to `start_as_current_span` queued |
| `LLM_PROVIDER=anthropic` flip on Railway | Live state still `openai` | `FallbackAdapter` ready; flip + redeploy queued at user's go-ahead |
| `get_recent_labs` / `get_recent_vitals` SCHEMA `description` strings | Still say "last 90 days" | Tool docstrings updated to "last 5 years"; only the SCHEMA string drifted. Cosmetic. |
| `idx_provider` on `patient_data.providerID` (audit §2.1) | Not added | Production hardening, not W1 scope |
| Slow-query log on MariaDB / MySQL (audit §2.2) | Not enabled | Production hardening |
| PHI plaintext at rest (audit §5.5) | Not encrypted | Out of W1 scope; agent does not exacerbate (no new plaintext PHI written) |
| Self-hosted Langfuse | Using Langfuse Cloud instead | BAA path adequate for current scope |
| Haiku model routing for cheap lookups | Not routed | Uniform Sonnet keeps the verification surface single |

---

## 6. Carry-forward into Week 2 (do NOT break)

- **Citation contract:** `Claim.record_id` must come from a tool call this turn
- **Layer-1 + Layer-2 verification** — extend in W2, never weaken
- **PHI minimizer** — extracted lab/intake values pass through the **same** scrubber
- **Three-layer per-physician scope** — `_verify_patient_in_panel` reused unchanged for any new endpoint
- **FHIR-only data path** — W2 may add **write** paths but stays inside FHIR
