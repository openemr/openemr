# Week 2 — Multimodal Evidence Agent

**Spec:** `~/Desktop/Gauntlet/Week2/Week 2 - AgentForge Clinical Co-Pilot.pdf`
**Subtitle:** *Multimodal Evidence Agent — seeing clinical documents, routing work, and gating changes with evals*
**Window:** 2026-05-04 → 2026-05-10 (kickoff Mon 2026-05-04; Final Sun 2026-05-10 noon CT)
**Active branch:** `w2-mvp` (HEAD `971affe8d` as of 2026-05-05)
**Status:** ⏳ In progress — MVP-scope code largely on `w2-mvp`; LangGraph + 50-case eval still ahead

**Companion docs (already on `w2-mvp`):**
- `copilot/W2_ARCHITECTURE.md` — design-of-record (baseline `f5b385f97`)
- `copilot/W2_IMPLEMENTATION.md` — MVP-scope step-by-step plan
- (planned) `copilot/W2_EARLY_IMPLEMENTATION.md` — Early-Submission scope
- (planned) `copilot/W2_FINAL_IMPLEMENTATION.md` — Final scope

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

### ⏳ Early-Submission scope — not yet started (per `W2_EARLY_IMPLEMENTATION.md` plan)

- LangGraph supervisor + worker contracts (`app/graph/`)
- `evidence_retriever` worker with full BM25+dense+rerank pipeline
- Critic node (Layer-1 + extended Layer-2 rules: `check_extracted_fact_has_source_doc`, `check_evidence_chunk_in_corpus`)
- Dense retrieval via OpenAI embeddings stored as SQLite BLOBs
- Cohere Rerank adapter + local-cross-encoder fallback (`Reranker` protocol)
- 50-case golden set under `evals/cases/*.yaml`
- Boolean rubric scorers + per-category pass-rate writer in `evals/RESULTS.md`
- PR-blocking `pre-push` Git hook + `make eval-fast` (<2 min)
- TurnTrace 6-field extension (`routing_path`, `extraction_confidence_min`, `retrieval_hit_ids`, `rerank_scores`, `vlm_cost_estimate_usd`, `documents_attached`)
- CI workflow extension to run full 50-case suite on every PR

### 📋 Final-scope — not yet started (per `W2_FINAL_IMPLEMENTATION.md` plan)

- Real FHIR writes (replace stubs from `971affe8d`)
- Cost & latency report (extend `copilot/COST.md`)
- 3–5 min demo video
- p50/p95 latency capture + bottleneck section
- Source-grounded UI polish (click-citation → bbox modal flow)
- Final deploy verification on Railway
- Round-trip eval test: upload lab PDF → re-fetch via W1 `get_recent_labs` → must surface once with correct `derivedFrom`

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
