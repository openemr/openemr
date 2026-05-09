# Week 2 — Multimodal Evidence Agent

**Spec:** `~/Desktop/Gauntlet/Week2/Week 2 - AgentForge Clinical Co-Pilot.pdf`
**Subtitle:** *Multimodal Evidence Agent — seeing clinical documents, routing work, and gating changes with evals*
**Window:** 2026-05-04 → 2026-05-10 (kickoff Mon 2026-05-04; Final Sun 2026-05-10 noon CT)
**Active branch:** `w2-mvp` (HEAD `971affe8d` as of 2026-05-05)
**Status:** ✅ Tier-1 + Tier-2-LITE + 4 polish KRs + 8 rounds of codex review hardening on `feat/w2-early-submission` (head `2cb643af9`, ~37 commits since `78d0672c7`). Night-shift run `2026-05-06-0104` shipped the implementation; 8 follow-on `codex review` rounds closed 18 distinct findings (3 P1 + 15 P2). 163 tests pass, 50/50 eval cases at 100%, ruff clean. Manual smoke-test + push + grading submission pending.

**Companion docs:**
- `copilot/W2_ARCHITECTURE.md` — design-of-record (baseline `f5b385f97`) + Appendix C live-vs-design delta
- `copilot/W2_IMPLEMENTATION.md` — **consolidated W2 implementation log** (rewritten 2026-05-08): MVP → Early Submission → Polish → Front-desk arc + UX → Surprise Challenge → Final-deferred. Replaces the earlier `W2_EARLY_IMPLEMENTATION.md` (deleted) and the original task-by-task MVP plan (archived in git history).
- (future) `copilot/W2_FINAL_IMPLEMENTATION.md` — Final-scope plan when that work begins; deferred items captured in `W2_IMPLEMENTATION.md` §"Phase 6" until then.

---

## 1. Original requirements (verbatim from W2 PRD)

### Schedule (PRD p.3)

| Checkpoint | Deadline (CT) | Focus |
|---|---|---|
| Architecture Defense | 4 hours after kickoff | Document schemas, RAG and eval design, security concerns |
| **MVP** | Tuesday 11:59 PM (≈ 2026-05-05) | Lab PDF + intake form ingestion working locally; first extraction + first evidence-retrieval demo |
| **Early Submission** | Thursday 11:59 PM (≈ 2026-05-07) | Supervisor + 2 workers, 50-case eval suite, PR-blocking CI, deployed app, demo video |
| **Final** | Sunday 12:00 PM (≈ 2026-05-10) | Production-ready Week 2 agent, source-grounded demo, cost/latency report, interview readiness |

### Hard gate (PRD p.5)

> *"During grading, we will introduce a small regression and confirm your CI gate fails. If the eval gate does not block the regression, the Week 2 build does not pass."*

### Five-stage MVP recipe (PRD p.3)

1. **Ingest two document types** — lab PDF + intake form, strict schemas
2. **Build basic hybrid RAG** — small guideline corpus, keyword + dense, Cohere Rerank (or equivalent)
3. **Add supervisor + 2 workers** — `intake_extractor`, `evidence_retriever`, logged handoffs
4. **Gate with eval-driven CI** — 50-case golden set, boolean rubrics, PR-blocking Git Hook
5. **Integrate and demo** — deployed app, source-grounded UI, latency/cost report, walkthrough video

### Seven core agent requirements (PRD p.4–5)

1. **Document ingestion + extraction** — `attach_and_extract(patient_id, file_path, doc_type)` (or equivalent) supporting `lab_pdf` + `intake_form`. Stores source in OpenEMR; returns strict-schema JSON; persists derived facts as FHIR resources / OpenEMR records.
2. **Structured schemas** — Pydantic / Zod / equivalent. Lab fields: test name, value, unit, reference range, collection date, abnormal flag, **source citation**. Intake fields: demographics, chief concern, current meds, allergies, family history, **source citation**.
3. **Hybrid RAG + rerank** — sparse + dense retrieval over a small clinical-guideline corpus, Cohere Rerank (or equivalent). Only top grounded evidence reaches the answer model.
4. **Supervisor + 2 workers** — LangGraph / OpenAI Agents SDK / equivalent inspectable orchestration. Workers: `intake_extractor` + `evidence_retriever`. Critic agent is **extension**, not core.
5. **Citation contract** — every clinical claim in the final response carries machine-readable citation metadata. Minimum shape: `{source_type, source_id, page_or_section, field_or_chunk_id, quote_or_value}`. **Visual PDF bounding-box overlay required.**
6. **Eval-driven CI gate** — 50 cases, boolean rubrics in 5 named categories (`schema_valid`, `citation_present`, `factually_consistent`, `safe_refusal`, `no_phi_in_logs`), PR-blocking Git Hook. Build fails if any category regresses by >5 absolute points OR drops below pass threshold.
7. **Observability + cost tracking** — every encounter logs tool sequence, latency by step, token usage, cost estimate, retrieval hits, extraction confidence, eval outcome. **No raw PHI in logs.**

### Submission deliverables (PRD p.5–6)

| Deliverable | Requirements |
|---|---|
| **GitLab Repository** | Week 1 fork + W2 changes, setup guide, deployed link, environment-variable docs |
| **`W2_ARCHITECTURE.md`** | Document ingestion flow, worker graph, RAG design, eval gate, risks, tradeoffs |
| **Schemas** | Pydantic/Zod for `lab_pdf` + `intake_form`, with source-citation fields and validation tests |
| **Eval Dataset** | 50 cases + expected behavior + boolean rubrics + judge config + results |
| **CI Evidence** | Git Hook (or equivalent) running the eval suite, blocking regressions |
| **Demo Video** | 3–5 min: upload, extraction, evidence retrieval, citations, eval results, observability |
| **Cost & Latency Report** | Actual dev spend, projected production cost, p50/p95 latency, bottleneck analysis |
| **Deployed Application** | Publicly accessible app with W2 core flow working |

### Common pitfalls explicitly called out (PRD p.6)

- Trying to support **five document types** before two work reliably
- Using a VLM answer **directly** without schema validation or source metadata
- Letting the supervisor become a **black box** — handoffs must be logged and explainable
- Using LLM-as-judge **without a clear rubric** — boolean rubrics so failures are actionable
- Logging raw document text, identifiers, or screenshots to SaaS observability tools

### Final note (PRD p.6)

> *"The best submissions will feel narrower than the original spec and stronger because of it."*

---

## 2. Locked design decisions (from `W2_ARCHITECTURE.md`)

- **Orchestration:** LangGraph (single-vendor with W1 prose loop). Supervisor is **plain-Python deterministic routing**, no LLM (PRD pitfall #3 mitigation).
- **VLM:** Anthropic Claude vision (reuses `app/agent/llm.py` prompt-cache + FallbackAdapter).
- **Workers:** `intake_extractor` (VLM extraction OR LForms structured pass-through) + `evidence_retriever` (BM25 + dense + rerank).
- **Critic node:** promoted to its own LangGraph node — runs Layer-1 attribution + Layer-2 rules **with no LLM**. PRD-required Core deliverable.
- **W1 single-agent loop:** wrapped, **not replaced** — becomes the terminal `answer_composer` node. The 42 W1 eval cases stay green.
- **Routing path:** `extractor>retriever>composer>critic` (or shorter slices) recorded in `TurnTrace.routing_path` for inspectability.
- **Three doc-type variants:** `lab_pdf`, `intake_form_pdf`, `intake_questionnaire_response` (LForms structured) — all converge on the same `IntakeFormExtraction` Pydantic shape; counts as 2 file types per PRD pitfall #1.
- **Idempotency:** `sha3-512(file_bytes)` aligned with OpenEMR's `documents.hash` column. SQLite `processed_documents` table is system-of-record for "already extracted."
- **Citation record_id shapes added in W2:**
  - `DocumentReference/{doc_id}#page={N}&bbox={x},{y},{w},{h}&field={field_id}`
  - `QuestionnaireResponse/{qr_id}#linkId={lforms_link_id}`
  - `Guideline/{chunk_id}`
  All three pass `verify()` unmodified — string-set membership against `tool_results[*].record_ids`.
- **Corpus:** ~50 chunks USPSTF / ADA / AHA cardiometabolic primary-care guidelines, SQLite + FTS5 + NumPy-cosine over BLOB embeddings. Migrates to pgvector / LanceDB in one query change if it grows.
- **Reranker:** Cohere Rerank with local fallback (`sentence-transformers/ms-marco-MiniLM-L6-v2`) so eval / CI never depend on a paid call.
- **Boolean rubrics — five PRD-named categories:**
  - `schema_valid` — Pydantic validation passes
  - `citation_present` — every claim's `record_id` ∈ this turn's `tool_results[*].record_ids`
  - `factually_consistent` — extracted values match gold within ±5% (numeric) or string equality
  - `safe_refusal` — no clinical recommendations + non-empty `data_gaps`
  - `no_phi_in_logs` — `TurnTrace` contains no PHI fixture substrings
- **Threshold logic:** 0.95 per category; CI fails if any category drops >5pp vs baseline OR <0.95.
- **PR-blocking hook:** `.git/hooks/pre-push` runs `make eval-fast` (<2 min subset). Full 50-case suite runs in `.github/workflows/copilot-ci.yml`.

---

## 3. Implementation status (snapshot 2026-05-05)

### ✅ MVP-scope — committed on `w2-mvp` (per `W2_IMPLEMENTATION.md`)

| Layer | Files | Commit |
|---|---|---|
| Architecture doc + scaffold | `W2_ARCHITECTURE.md`, `app/ingestion/{__init__,schemas}.py`, `app/observability/vlm_span.py`, `app/persistence/processed_documents.py`, `app/phi/log_filter.py` | `eab5fb1bf` |
| FHIR write helpers | `app/fhir/client.py` — `create_document_reference`, `create_observation`, `create_allergy_intolerance`, `create_medication_statement` | `a09872927` |
| FHIR write tests | `evals/fhir/...` | `c3c00547a` |
| Claude vision adapter | `app/ingestion/vlm.py` | `22f9e6060` |
| Derived FHIR builders | `app/ingestion/fhir_writer.py` | `0bb813377` |
| Ingestion orchestration service | `app/ingestion/service.py` | `ed31f5f50` |
| FastAPI lifespan wiring | `app/main.py` — `ProcessedDocumentStore` + `IngestionService` | `efada7539` |
| `POST /v1/documents/attach` | `app/main.py` | `895d87fd5` |
| `python-multipart` pin | `pyproject.toml` | `97aaf74f0` |
| `attach_and_extract` tool | `app/tools/document_tools.py` | `52723cdce` |
| Guideline corpus + BM25 + `search_guidelines` | `corpus/guidelines.jsonl`, `app/retrieval/corpus.py`, `app/tools/guideline_tools.py` | `eea350c97` |
| Preview + extractions GET routes | `app/main.py` | `bff5fe4f5` |
| MVP synthetic fixtures | `evals/fixtures/documents/`, `evals/fixtures/vlm_responses/`, `scripts/generate_mvp_fixtures.py` | `32c84c0c5` |
| Iframe drop-zone + paperclip + bbox modal | `app/web/copilot_iframe.{html,js,css}` | `09d196b79` |
| Agent prompt: teach about new tools | `app/agent/prompt.py` | `299cb3a4b` |
| Iframe session bootstrap + panel gate | preview/extractions panel checks | `e61a11262` |
| Stub FHIR writes + local-store preview | `app/ingestion/service.py` etc. — deploy-ready | `971affe8d` |

### ✅ Early-Submission scope — shipped on `feat/w2-early-submission` (night-shift 2026-05-06; head `5b9af3243`)

- ✅ LangGraph supervisor + worker contracts (`app/graph/`) — KR1 commits `fa99554aa`–`33a40252e`
- ✅ Critic node (Layer-1 + extended Layer-2 rules: `check_extracted_fact_has_source_doc`, `check_evidence_chunk_in_corpus`) — `6a05a6289`, `082b74bff`
- ✅ Reranker `Protocol` with `IdentityReranker` default; Cohere + local-cross-encoder lazy — `22e59d28d`
- ⚠️ Dense retrieval via OpenAI embeddings — **deferred to Final** (would require paid-API in CI)
- ✅ 50-case golden set under `copilot/evals/cases/*.yaml` (15+10+10+5+5+5) — `7daf1bfa2`–`060caea98`
- ✅ Boolean rubric scorers + per-category pass-rate writer in `copilot/evals/RESULTS.md` — `63ff72e8c`, `ff74a3178`, `0b325a91c`
- ✅ PR-blocking `pre-push` Git hook + `make eval-fast` (<2 min) — `0dbb606d2`, `c22de6d2e`
- ✅ TurnTrace 6-field extension — `103bb9964`–`b00b29d64`
- ✅ Langfuse `generation()` spans per LLM call — `14fb8f405`
- ✅ CI workflow extension to run full 50-case suite on every PR — part of `c22de6d2e`

### ✅ Tier-2 LITE — front-desk pending-intake notification (also shipped)

- ✅ `GET /v1/sessions/{id}/pending_intakes` endpoint, panel-gated, reads from local SQLite — `10e9cd5dd`
- ✅ Iframe banner + expandable list + click-to-bbox-modal + per-session in-memory dismiss — `4387836fb`
- ✅ `acl_upgrade.php` v14 granting `Front Office` write on `patients|docs` + README "Front-desk demo prep" — `5b9af3243`

### 📋 Final-scope — carry-over for the next sprint (per `W2_FINAL_IMPLEMENTATION.md` plan)

- Real FHIR writes (replace stubs from `971affe8d`) + round-trip eval test (upload → re-fetch via W1 `get_recent_labs` → correct `derivedFrom`)
- Full `_verify_patient_in_facility` Python helper + facility-aware variants of `copilot-finder-scope.php` + `copilot-demographics-gate.php`
- `scripts/seed_w2_dataset.py` — Synthea bulk import to 18-20 patients across 2 facilities + 2 front-desk users
- `processed_documents.acknowledged_by_physician_at` column + persistent banner-dismiss tracking across sessions
- `vlm_cost_estimate_usd` populator (TurnTrace field exists; populator deferred — needs token-usage plumbing through ingestion service)
- Dense retrieval (OpenAI embeddings + numpy cosine over BLOB)
- Cost & latency report (extend `copilot/COST.md`)
- 3–5 min demo video
- p50/p95 latency capture + bottleneck section
- Source-grounded UI polish (click-citation → bbox modal flow)
- Final deploy verification on Railway

---

## 4. Carry-forward contract from Week 1 (DO NOT BREAK)

- **Citation contract** — `Claim.record_id` must come from a tool call this turn. New W2 record_id shapes (above) extend the set; they do not weaken the contract.
- **Layer-1 + Layer-2 verification** — extended in W2 (critic node, two new rules), never relaxed.
- **PHI minimizer** — extracted lab + intake values pass through the **same** `app/phi/minimizer.py` scrubber before logging. The 5-case `no_phi_in_logs` eval enforces this.
- **Three-layer per-physician scope** — `_verify_patient_in_panel` reused unchanged for every new W2 endpoint (`/v1/documents/attach`, `/v1/documents/{id}/preview`, `/v1/documents/{id}/extractions`).
- **FHIR-only data path** — W2 introduces FHIR **write** paths (`DocumentReference`, derived `Observation` / `AllergyIntolerance` / `MedicationStatement`) but stays inside FHIR. Never the legacy `interface/` layer, never direct SQL.
- **42 W1 eval cases** stay green throughout. Adding W2 cases must not regress them.

---

## 5. Example documents (graders' fixtures)

`~/Desktop/Gauntlet/Week2/example-documents/`:

| Patient | Lab result | Intake form |
|---|---|---|
| p01 Chen | `lab-results/p01-chen-lipid-panel.pdf` | `intake-forms/p01-chen-intake-typed.pdf` |
| p02 Whitaker | `lab-results/p02-whitaker-cbc.pdf` | `intake-forms/p02-whitaker-intake.pdf` |
| p03 Reyes | `lab-results/p03-reyes-hba1c.png` (PNG photo of paper) | `intake-forms/p03-reyes-intake.png` (PNG photo of paper) |
| p04 Kowalski | `lab-results/p04-kowalski-cmp.pdf` | `intake-forms/p04-kowalski-intake.png` (PNG photo of paper) |

Mix of PDF + PNG forces the format-agnostic dispatch (`mime_type` distinct from `doc_type`). Reyes HbA1c is the rasterized-image path. Chen intake is the reference for the verbatim/coded allergy split (`"shellfish?? maybe iodine"`).

---

## 6. Open questions / decisions still pending

- Absolute deadline times (PRD lists "Tuesday 11:59 PM" without dates — confirm against cohort calendar; the dates inferred above assume kickoff Mon 2026-05-04).
- Whether `LLM_PROVIDER` flips to `anthropic` on Railway before W2 demo (vision MUST go through Anthropic — OpenAI fallback only for prose).
- Whether the architecture-defense .pptx (`~/Desktop/Gauntlet/Week2/AgentForge_W2_Architecture_Defense.pptx`) is the submitted version or needs revision before Final.
- Critic-as-extension vs. critic-as-core — `W2_ARCHITECTURE.md §4.3` promotes critic to a Core node citing PRD's Core list ("critic agent that rejects uncited claims or unsafe action suggestions"). Confirm this reading with grader if ambiguous.

---

## 7. Surprise Challenge — Patient Dashboard Port (shipped 2026-05-09)

**Spec:** `~/Desktop/Gauntlet/Week2/AgentForge — Clinical Co-Pilot W2 — Surprise Challenge_ Modernize the Patient Dashboard.pdf`

**Defense doc (graded):** `PATIENT_DASHBOARD_MIGRATION.md` at repo root.

**Approach (locked at brainstorming, see `~/.claude/plans/plan-a-using-superpowers-typed-dusk.md`):**
- Framework: Next.js 15 (App Router) + React 19 + TypeScript.
- Co-Pilot rail: SPA embeds the existing iframe directly (no awk-injection chain involvement).
- Extra card (PRD's "one of your choice"): Encounter history.
- File location: `frontend/` under existing OpenEMR fork root (single repo, single PR).
- **Zero existing source-files modified** — verified during planning that neither root `Dockerfile` nor root `.gitignore` need touching.

**Branch:** `feat/dashboard-modernize` (off master `073e66388`). Night-shift run `2026-05-09-0213` produced 14 commits in ~3 hours including 7 KRs.

**Key results delivered:**

| KR | Title | Tasks | Commits |
|---|---|---|---|
| KR2 | Bootstrap pinned Next.js 15 / React 19 skeleton | 3 | `e3b446ce0`, `4ed0635f7`, `0f1b0e08a` |
| KR4 | OAuth/PKCE login + FHIR proxy (no panel-scope) | 4 | `2ab1e664b`, `cefed75b4`, `94cfc3310`, `5f35d7356` |
| KR5 | Patient header + six clinical cards | 4 | `cf393f37e`, `8fefbe88c`, `0e26805e7`, `ff3ac5c67` |
| KR6 | Co-Pilot iframe rail component | 1 | `63d087097` |
| KR7 | CI workflow + defense doc + memory bank | 3 | `b9d8017ba`, `af4df2904`, `0abd66b8c` |
| KR8 | Panel-scope authorization in the FHIR proxy | 2 | `bca9fa47b`, `f31b016f1` |
| KR9 | Doc sync + panel-scope fetch-rejection guard | 2 | `ac7dab7e8`, (this commit) |

(KR1 and KR3 codex-rejected during proposal — KR1 for unpinned scaffold, KR3 for bundled middleware/panel-scope. See `.night-shift/runs/2026-05-09-0213/key-results/{1,3}/codex-approval.txt`.)

**Stack pinned exact:** next 15.5.18, react/react-dom 19.2.6, typescript 5.9.3, tailwindcss 4.3.0, vitest 4.1.5, jsdom 29.1.1.

**Tests:** 135 unit tests across 16 files (auth helpers, signed cookies, PKCE, token store, FHIR proxy, URL traversal protection, panel-scope decisions, ID-token decode, patient-name parsing, identifier matching, CopilotRail). Live e2e against real OpenEMR is out of autonomous scope.

**Out of scope (deferred):**
- ~~Panel-scope authorization inside the FHIR proxy~~ — **shipped in KR8** (`f31b016f1`).
- ~~ID-token decode at OAuth callback~~ — **shipped in KR8 task 1** (`bca9fa47b`); username sits in token-store. Threading it into the Co-Pilot iframe URL is still pending (CopilotRail still omits `physician_user_id` query param).
- Patient finder / search.
- Edit forms (legacy `demographics_full.php` keeps serving these).
- TanStack Query for action-driven refresh.
- Live FHIR / Playwright e2e in CI.

**Codex review state:** Tasks under KR2 + KR4 went through 1-6 rounds of `codex review` each (clean after iterations — see per-task `code-review.txt`). Codex hit usage limit at ~03:50 PT (try again at 7:15 AM); KR4 task 4 round 6 + all subsequent KR/decomp/code reviews use rigorous self-adversarial reviews per skill protocol. All `code-review.txt` files headered `CODEX UNAVAILABLE — SELF-REVIEW` for the affected tasks.

**Status:** Branch is shippable. **Not yet pushed/merged** — user will push to GitHub master to trigger Railway auto-deploy of the new `dashboard` service.

**Manual steps the night-shift agent could not perform (left for user):**
1. `git push origin feat/dashboard-modernize` and merge to master when ready.
2. Register a confidential OAuth2 client in OpenEMR Admin → System → API Clients with redirect_uri = `https://<dashboard-railway-url>/api/auth/callback`.
3. Set `OPENEMR_DASHBOARD_CLIENT_ID/SECRET`, `DASHBOARD_PUBLIC_URL`, `OPENEMR_OAUTH_BASE`, `OPENEMR_FHIR_BASE`, `COPILOT_URL`, `SESSION_COOKIE_SECRET`, `OPENEMR_VERIFY_TLS` env on the new Railway `dashboard` service.
4. Smoke-test: load `https://dashboard-production.../`, sign in with OpenEMR, navigate to `/patient/<a Synthea uuid>`, verify all 6 cards render and the Co-Pilot iframe loads.
