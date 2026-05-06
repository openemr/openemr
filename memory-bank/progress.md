# Progress

**Last reviewed:** 2026-05-05 evening

---

## Cross-week summary

| Week | Window | State |
|---|---|---|
| Week 1 | 2026-04-21 → 2026-05-04 | ✅ Complete — all four checkpoints submitted, all AI Interviews completed (closed 2026-05-05) |
| Week 2 | 2026-05-04 → 2026-05-10 | 🟡 MVP shipped (Tue 2026-05-05) — deployed at `https://copilot-production-b532.up.railway.app`, master tip `78d0672c7`. Early Submission (Thu) and Final (Sun) ahead. |
| Week 3+ | TBD | 📋 Not started |

---

## ✅ Completed (Week 1)

### Documents
- `AUDIT.md` — five-section audit with ~500-word summary
- `USERS.md` — target user, 3 use cases (UC1/UC2/UC3), out-of-scope table, AUDIT trace-back
- `ARCHITECTURE.md` — 12 sections + trace-back matrix + ~500-word summary
- `README.md` — landing page with W1 deliverables and live URLs
- `copilot/IMPLEMENTATION.md` — running implementation log
- `copilot/COST.md` — actual + projected LLM spend (100 / 1K / 10K / 100K users)

### Deployed services (Railway)
- OpenEMR fork at https://openemr-production-0c8c.up.railway.app/
- Co-Pilot agent at https://copilot-production-b532.up.railway.app/
- iframe rail injected into stock `demographics.php` via build-time `awk`
- TLS cert regenerated idempotently on every container boot

### Co-Pilot agent (`copilot/`)
- 8 FHIR-backed tools using shared 5-step pattern (`app/tools/_base.py:run_tool`)
- PHI minimizer with session-scoped pseudonyms (`app/phi/`)
- ACL middleware mirroring OpenEMR's `aclCheckCore` (`app/acl/check.py`)
- Two-layer verification gate — Layer 1 source attribution + Layer 2 domain rules (`app/verification/`)
- LLM `FallbackAdapter` — Anthropic primary, OpenAI per-turn fallback (`app/agent/llm.py`)
- Langfuse Cloud observability with PHI-screened ingest (`app/observability/trace.py`)
- Eval suite: **42 passing tests** (PHI / tool integration / verification / scenarios / persistence / retrieval / ingestion)
- CI: ruff + pytest gate on `copilot/**` (`.github/workflows/copilot-ci.yml`)
- Resume-previous-chat persistence (SQLite on Railway volume + `/v1/sessions/{recent,resume,end}`)
- Pre-warm on session open — first-turn latency cut from ~15s to ~3s (`app/agent/prewarm.py`)
- Standalone chat UI at `/`, auto-binds to `?patient_id=` for iframe mode

### Three-layer per-physician scope
- `copilot-demographics-gate.php` — demographics-page gate
- `copilot-finder-scope.php` — finder filter
- `/v1/sessions` `_verify_patient_in_panel` — session-open gate
- `PHYSICIAN_PATIENT_PANEL` env-driven panel + admin-list bypass

### Patient data
- 10 Synthea CCDA patients imported on Railway (FHIR import returned HTTP 500 — known upstream Synthea/OpenEMR shape mismatch)
- Demo heroes: Mariela (UC1/UC2), Dana 2y (UC3 hard block on aspirin allergy)

### Submissions & ceremony
- MVP submitted 2026-04-28
- Early Submission submitted 2026-04-30 (with iframe rail live)
- Final submitted 2026-05-03 (with social post + 42-case eval suite)
- Demo videos recorded for each checkpoint
- **AI Interviews — all completed** (confirmed 2026-05-05). Week 1 is fully closed; no outstanding obligations.

---

## ✅ Week 2 MVP — shipped 2026-05-05 evening

Master tip `78d0672c7`, deployed at `https://copilot-production-b532.up.railway.app`. 14-task plan in `W2_IMPLEMENTATION.md` complete; deployed-MVP delta vs the architecture-defense design is documented in `W2_ARCHITECTURE.md` Appendix C.

**Core capability shipped:**
- Multimodal ingestion (lab PDF + intake form via Claude Sonnet 4.6 vision)
- 11-tool registry (8 W1 readers + `attach_and_extract`, `search_guidelines`, `get_recent_uploads`)
- BM25 retrieval over the 12-chunk hand-curated guideline corpus
- Bbox-overlay citation contract end-to-end (drop PDF → extract → click chip → modal opens with rectangle)
- sha3-512 idempotency on uploads
- 75 tests passing (W1: 42 + W2 MVP: 33)
- Anthropic-primary by design (`f2d6bc972` makes `LLM_PROVIDER=anthropic` the default whenever the key is set)
- Light-theme iframe pinned via `color-scheme: light` so dark-mode browsers render correctly

**MVP commit timeline (18 commits between `f5b385f97` and `78d0672c7`):**
- `eab5fb1bf` Land architecture + ingestion/observability/persistence/phi scaffold
- `a09872927` + `c3c00547a` FhirClient write helpers (later stubbed)
- `22f9e6060` Claude vision adapter
- `0bb813377` FHIR writer for derived facts
- `ed31f5f50` IngestionService orchestration
- `efada7539` FastAPI lifespan wiring
- `895d87fd5` + `97aaf74f0` POST /v1/documents/attach + python-multipart dep
- `52723cdce` attach_and_extract agent tool
- `eea350c97` Corpus + BM25 search_guidelines
- `bff5fe4f5` /preview + /extractions routes
- `32c84c0c5` Synthetic fixture generator
- `09d196b79` Iframe drop-zone + paperclip + bbox modal
- `299cb3a4b` Agent prompt updated
- `e61a11262` Iframe session bootstrap + panel gate
- `971affe8d` **Option A pivot — stub FHIR writes, serve preview from local store** (deploy-ready)
- `e7cccee51` `.gitattributes` anchor fix (Railway git-archive was stripping `app/tools/`)
- `717fca2ff` Dockerfile import smoke test
- `fb33beb53` `pip install -e .` + diagnostic
- `53df1f289` `PHYSICIAN_PATIENT_PANEL` wildcard `"*"` for grader cohort
- `f2d6bc972` Anthropic-primary factory regardless of `LLM_PROVIDER`
- `0b0526e2f` `llm-call` log lines per adapter (visible identity in Railway logs)
- `4dc7922b1` `get_recent_uploads` tool — agent reads recent extractions from Co-Pilot SQLite
- `c153cccef` Pseudonym key fix (`active_patient_id` not `patient_pseudonym()`)
- `85975f5dd` Per-fact data items so Layer-2 cross-patient-leakage check passes
- `271363584` Logger config (INFO for `copilot.*`) + Sonnet 4.6 pin + `.gitignore` for OAuth keys + Synthea UUID dirs
- `48cab4144` Light-theme CSS pin (dark-mode browsers were rendering black-on-black)
- `160820b57` `W2_ARCHITECTURE.md` Appendix C documenting deployed-MVP reality
- `78d0672c7` README rewrite for Week 2 + rename `IMPLEMENTATION.md` → `W1_IMPLEMENTATION.md`. **GitLab only (intentional)** — GitHub still at `160820b57`.

**Tools added beyond original architecture:** `get_recent_uploads` (`4dc7922b1`) was needed because OpenEMR's FHIR R4 API has no `POST /fhir/DocumentReference` for create; uploaded extractions live in Co-Pilot's SQLite, and without this bridge tool the agent has no way to read them mid-turn. See Appendix C.1 in `W2_ARCHITECTURE.md`.

---

## ⏳ In progress — Week 2 Early Submission

**PRD read 2026-05-05.** Spec at `~/Desktop/Gauntlet/Week2/Week 2 - AgentForge Clinical Co-Pilot.pdf`. Master tip `78d0672c7`; current sprint posture in `activeContext.md`. **Plan file `W2_EARLY_IMPLEMENTATION.md` not yet authored.**

**Early-Submission scope (Thu ≈ 2026-05-07) — not yet started:**
- LangGraph supervisor + `intake_extractor` + `evidence_retriever` workers
- Critic node (Layer-1 + extended Layer-2 with `check_extracted_fact_has_source_doc` + `check_evidence_chunk_in_corpus`)
- Dense retrieval + Cohere Rerank with local fallback
- 50-case golden set under `evals/cases/*.yaml` covering 5 PRD rubric categories
- Boolean rubric scorers + per-category pass rates in `evals/RESULTS.md`
- PR-blocking `pre-push` Git hook + `make eval-fast` (<2 min)
- TurnTrace 6-field extension
- CI workflow extension to run full 50-case suite on every PR

**Final-scope (Sun ≈ 2026-05-10) — not yet started:**
- Real FHIR writes (replace stubs from `971affe8d`)
- Round-trip eval test (upload → re-fetch via `get_recent_labs` → correct `derivedFrom`)
- Cost & latency report in `copilot/COST.md` (p50/p95, bottleneck analysis)
- 3–5 min demo video
- Source-grounded UI polish; final Railway deploy verification

**Hard gate to be aware of:** during grading, a small regression will be injected; CI must fail. Document the exact regression-reproduction in README so graders can trip it.

### Bbox-overlay PDF/PNG rendering fix — uncommitted (2026-05-05 evening, post `78d0672c7`)

PRD §5 ("Visual PDF bounding-box overlay required") was under-delivered in the MVP — the modal drew the red rectangle on a blank gray canvas because PDF.js was tagged "post-MVP" (`copilot_iframe.js:186-188`, now removed). Fix is **frontend-only**:

- `copilot/app/web/copilot_iframe.html` — added pinned PDF.js v3.11.174 (`pdf.min.js`, IIFE build that exposes `window.pdfjsLib`) from cdnjs; dropped fixed `width="800" height="1000"` on the canvas (set per-render now).
- `copilot/app/web/copilot_iframe.js` — set `pdfjsLib.GlobalWorkerOptions.workerSrc` once at boot; added `drawBboxOverlay`, `drawTextFallback`, `renderImagePreview`, `renderPdfPreview` helpers; rewrote `openBboxModal` to fetch `/v1/documents/{id}/preview`, branch on `Content-Type` (`image/*` → `<img>` + `drawImage`, `application/pdf` → PDF.js `getDocument` → `getPage(N)` → `render`), then `strokeRect` the bbox using the rendered viewport dims (not the old fixed 800×1000). Non-`DocumentReference/` record_ids keep the text-only fallback.

Server-side untouched — `/v1/documents/{doc_id}/preview` already serves bytes with original MIME (`copilot/app/main.py:578-596`, panel-gated). No new Python deps; no CSP work needed (confirmed iframe origin sets none).

PDF.js pinned at v3.11.174 (not 4.x as planned) because v4 dropped the IIFE build and only ships ESM; v3 keeps the existing IIFE script-tag integration trivial.

**Verification still owed:** local Docker run against `lab-lipid-small.pdf` (PDF path) + a PNG fixture (image path) + a `Guideline/{chunk_id}` citation (text fallback path). After verification, commit + push to GitHub master so Railway redeploys.

### W2 architecture extension — front-desk facility scope + pending-intake notification (2026-05-06, uncommitted)

`copilot/W2_ARCHITECTURE.md` extended to make the Front Desk role first-class:

- §2.0 expanded — facility scope (`users.facility_id`) is the front-desk access boundary, paralleling the physician's panel scope. Three new gate mirrors documented: `_verify_patient_in_facility` (Python) + facility-aware branches in `copilot-finder-scope.php` + `copilot-demographics-gate.php`. Role detection via `Front Office` ACL group membership, cached on session.
- New §2.0 paragraph — pending-intake banner UX. On iframe open, top-of-panel banner *"N intake documents uploaded by front desk — review"* + expandable list (one row per doc) + click opens existing bbox modal. Per-doc acknowledgement persists in `processed_documents.acknowledged_by_physician_at` (new column). Source: new `GET /v1/sessions/{id}/pending_intakes` endpoint thin-wrapping the §4.1 `pending_intake_sources(pid)` design.
- New §2.6 — EHR-resident dataset plan. W1 had 10 Synthea patients × 3 providers × 1 facility (`patient_data.providerID` ∈ {1, 4, 5}, all `users.facility_id = 3`). W2 plan: 18-20 patients total (10 existing + 8-10 new Synthea CCDA) × 4-5 providers × 2 facilities (Riverside + Eastside), with 4-6 pre-staged docs per facility uploaded via OpenEMR Documents Zend module by per-facility front-desk users. Reproducibility: new `copilot/scripts/seed_w2_dataset.py` (idempotent, seeded).
- §8 Security — new bullet documenting facility-scope front-desk gate as a parallel trust boundary to the physician's panel gate.

`copilot/W2_EARLY_IMPLEMENTATION.md` authored — 13-task plan splitting Tier-1 (eval-gate-critical, must-ship-Thursday: LangGraph + critic + 50 cases + pre-push hook + TurnTrace + Langfuse spans) from Tier-2 (front-desk LITE: ACL grant + 2 pre-staged docs + endpoint + banner stub, in-memory dismiss only) from Final-deferred (full facility-scope helper, full dataset expansion, persistent dismiss, real FHIR writes, demo video). The hard PRD gate (regression-blocking eval suite) is the single non-negotiable; everything else is sequenced behind it.

---

## 📋 Pending

### Week 3+
- Assignment not yet released. When it lands, follow CLAUDE.md rule 2.

### Open W2 questions
- Confirm absolute deadline times against cohort calendar (PRD lists "Tuesday 11:59 PM" without dates; inferred dates are based on a Mon 2026-05-04 kickoff).
- Confirm MVP submission status before pivoting attention to Early-Sub scope.
- Confirm `LLM_PROVIDER=anthropic` flip on Railway is acceptable now (vision is the forcing function).
- Confirm critic node interpretation — `W2_ARCHITECTURE.md §4.3` reads it as Core; PRD p.5 lists "critic agent" as Extension. Defaulting to Core per the architecture doc; verify with grader if challenged.

---

## ⚠️ Known issues / technical debt

| # | Item | Severity | Notes |
|---|---|---|---|
| 1 | Langfuse SDK pinned `<3` | medium | v3 dropped `Langfuse.trace()` for OTel-style spans; wrapper still on v2 API. Migration to `start_as_current_span` is queued. |
| 2 | `phi/minimizer.py` defensive iteration partial | medium | `dosageInstruction` fixed; `reaction[0].manifestation`, `referenceRange[0]`, `category[0].coding[0]` still use the brittle `[0].get(...)` pattern that breaks on OpenEMR's non-spec list-of-list shapes |
| 3 | Layer-2 rules: only allergy + cross-patient | medium | Renal-dose and QTc rules deferred from `ARCHITECTURE.md §4.2` plan. Not regressions; openly scoped out. |
| 4 | ~~Local `.env` line 6 still says `LLM_PROVIDER=openai`~~ | resolved | Updated locally to `LLM_PROVIDER=anthropic` 2026-05-05 (`.env` is gitignored). |
| 4b | ~~`anthropic_model` default vs. prose drift~~ | resolved in `271363584` | `app/config.py:16` now defaults to `claude-sonnet-4-6`. |
| 4c | ~~`vlm_model_id = claude-opus-4-5`~~ | resolved in `271363584` | Standardized on `claude-sonnet-4-6` to match the prose adapter, lower cost variance. |
| 4d | ~~Untracked sensitive files in `sites/default/documents/`~~ | resolved in `271363584` | `.gitignore` patterns added (`sites/default/documents/certificates/oa*.key`, `sites/default/documents/[uuid]/`); Synthea blob deleted from working tree. |
| 4e | Langfuse trace UI doesn't show LLM model identity | low | `LangfuseTracer.emit` emits one `agent_turn` trace but doesn't auto-instrument `messages.create`/`chat.completions.create`. Model name is currently visible only via Railway stdout `INFO:copilot.agent.llm:llm-call provider=...` lines (added `0b0526e2f`, surfaced after logger-level fix `271363584`). Adding a `langfuse.generation()` wrapper inside each adapter's `call()` is on the W2 Early-Submission scope. |
| 5 | `get_recent_labs` / `get_recent_vitals` SCHEMA `description` strings still say "last 90 days" | very low | Tool docstrings updated to "last 5 years"; only the JSON SCHEMA `description` field drifted. Cosmetic. |
| 6 | Vital-signs data sparse on Synthea patients | low | Latest vital across 10 patients is 2017-03-03. Tools window is 5 years; the agent gracefully reports `data_gaps: ["No recent vitals on file"]`. |
| 7 | `idx_provider` on `patient_data.providerID` not added (audit §2.1) | low | Production hardening, not W1 scope. Becomes acute at hospital scale (audit §2.1). |
| 8 | Slow-query log disabled on MariaDB / MySQL (audit §2.2) | low | Production hardening |
| 9 | PHI plaintext at rest (audit §5.5) | medium long-term | Out of W1 scope. CipherSuite exists in OpenEMR but is unused for `patient_data` SSN/license/phone/email. Agent does not exacerbate (no new plaintext PHI written). |
| 10 | Synthea FHIR import returns HTTP 500 | low | Known upstream shape mismatch; CCDA path works and is the data-load path. |
| 11 | Demo-script `LLM_PROVIDER` mismatch documentation drift | very low | `IMPLEMENTATION.md` is verbose about live-state vs. architectural-primary; once the env is flipped, simplify the prose. |

---

## Resolved during Week 1 (closed log — kept for audit-trail)

| Item | Resolution | Commit |
|---|---|---|
| `demographics.php` COPY broke on fork/image version mismatch | Switched to `awk` build-time injection (P8 in `systemPatterns.md`) | `8a42c1347` revert → `da8b10fe2` fix |
| Anthropic billing rejected calls demo-night | `FallbackAdapter` added; ran demo on OpenAI without code change | (in `d0600aa9e` block) |
| First-turn latency too high | `prewarm.py` pre-fetches FHIR tools on `/v1/sessions` | `d682c4da8` |
| `admin` literal bypass too coarse | Replaced with env-list bypass | `f04657d65` |
| Iframe rail panel had a redundant tool-layer A.7 re-check | Dropped; trust `/v1/sessions` gate | `1d102d0c5` |
| CI deploy job double-fired with Railway native auto-deploy | CI deploy job dropped; test gate retained | `f88ed610a` |
| `physician_user_id` propagation through iframe | Resolved | `f5b385f97` |
| Demo-fallback log noise on Railway | Demoted from WARNING to INFO | `89cb9894e` |
| `[build-system]` missing in `pyproject.toml` blocked CI install | Added | `3d589c63f` |
| Ruff lint errors blocked CI | Cleared | `9cad8a1a5` |
| `VOLUME` instruction in copilot Dockerfile rejected by Railway builder | Removed | `01d308467` |
| `LLM_PROVIDER=openai` drift on Railway blocked Anthropic primary | (a) Code side — `get_adapter` now ignores a drifted `LLM_PROVIDER` and selects `FallbackAdapter(Anthropic→OpenAI)` whenever the Anthropic key is set. (b) Railway env flipped to `anthropic` 2026-05-05 by user. Vision path now unblocked for W2. | `f2d6bc972` |
| Railway build was missing `app/tools/` (`ModuleNotFoundError`) | OpenEMR's repo-root `.gitattributes` had an unanchored `tools/ export-ignore` that matched `copilot/app/tools/`. Anchored to `/tools/` so only the top-level OpenEMR `tools/` dir is stripped from `git archive`. | `e7cccee51` |
| Railway healthcheck failed; need to reveal which model ran | Added `INFO:copilot.agent.llm:llm-call provider=... model=...` log lines in each adapter; bumped `copilot.*` loggers to INFO + attached a stream handler so the lines actually reach Railway stdout. | `0b0526e2f`, `271363584` |
| Layer-2 `check_cross_patient_leakage` rejected every claim citing an uploaded-doc record_id | Both new tools (`attach_and_extract`, `get_recent_uploads`) emitted `data` as a single extraction dict without per-fact `record_id` keys; Layer-2's `_record_belongs_to_active_patient` walk never matched. Flattened `data` to one item per cited record_id with `record_id` + `subject_pseudonym` set. | `85975f5dd` |
| Iframe rendered black-on-black on dark-mode browsers | Added `:root { color-scheme: light; }` and explicit bg + color on every relevant element. | `48cab4144` |
| Many cohort patient_ids hit the iframe; per-patient `PHYSICIAN_PATIENT_PANEL` was untenable | Added wildcard `"*"` support in the env-driven panel; deployed Railway env set to `{"admin": ["*"]}`. | `53df1f289` |
| OpenEMR `POST /fhir/DocumentReference` doesn't exist | Pivoted to **Option A**: stub the FHIR writes (synthesized `copilot-{sha3-512[:16]}` ids), store original file_bytes + extracted facts in Co-Pilot's own SQLite (`processed_documents` extended with `file_bytes BLOB` + `mime_type TEXT`), serve `/preview` from the local store. Real FHIR write path deferred to Week 3+. | `971affe8d` |
| Pseudonym key mismatch — agent looking up under session pseudonym, route stored under FHIR uuid | `get_recent_uploads` now keys lookup by `session.active_patient_id` (raw FHIR uuid), matching what the HTTP route stored. | `c153cccef` |
