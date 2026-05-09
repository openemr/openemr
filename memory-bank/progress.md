# Progress

**Last reviewed:** 2026-05-09 (night-shift dashboard-port run `2026-05-09-0213` shipped the W2 surprise challenge on `feat/dashboard-modernize`)

---

## Cross-week summary

| Week | Window | State |
|---|---|---|
| Week 1 | 2026-04-21 → 2026-05-04 | ✅ Complete — all four checkpoints submitted, all AI Interviews completed (closed 2026-05-05) |
| Week 2 | 2026-05-04 → 2026-05-10 | 🟢 Early-Submission shipped + polished + canary-verified + **front-desk deferred-extraction path** + **confirm/reject UX + OpenEMR REST write-back + persistence fix** on master at `c2534e416` (49 commits ahead of `78d0672c7`; pushed to GitHub + GitLab; Railway-deployed). **189 tests / 53/53 eval cases**. The deferred Documents-tab UI item is closed by routing the front-desk arc through the iframe drop-zone with a defer flag (`5e63e5fb9`); confirm/reject buttons + write-back to OpenEMR's MySQL `documents` table via the non-FHIR REST API shipped at `c2534e416`. **W2 surprise challenge** (port the patient dashboard to a modern framework, document defense in `PATIENT_DASHBOARD_MIGRATION.md`) **shipped 2026-05-09 on `feat/dashboard-modernize`** by night-shift run `2026-05-09-0213` — Next.js 15 + React 19 + TypeScript at `frontend/` (zero existing source-files modified per planning), server-side OAuth proxy keeps FHIR token off browser, six clinical cards + Encounter history, Co-Pilot rail embedded as sandboxed iframe, 109 unit tests, defense doc at `PATIENT_DASHBOARD_MIGRATION.md`, CI at `.github/workflows/dashboard-ci.yml`. Branch is 14 commits ahead of master tip `073e66388`; not yet pushed/merged (user will). Final (Sun) deferred items in `W2_FINAL_IMPLEMENTATION.md`. |
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

### W2 Early-Submission shipped — autonomous night-shift run 2026-05-06

Branch `feat/w2-early-submission` (head `5b9af3243`), 24 commits since `78d0672c7`. Five KRs landed:

**KR1 — LangGraph state machine** (5 tasks, commits `fa99554aa`–`33a40252e`):
- `app/graph/{state,build,critic,supervisor}.py` + `app/graph/workers/{answer_composer,intake_extractor,evidence_retriever}.py`.
- `/v1/chat` now routes through `app.state.agent_graph.ainvoke()`. The "no behavior change" checkpoint at task 1.2 held — all 75 W1+MVP tests stayed green.
- Two new W2 Layer-2 rules wired into `apply_rules`: `check_extracted_fact_has_source_doc`, `check_evidence_chunk_in_corpus` (`app/verification/rules.py`).
- Plain-Python supervisor + `decide_next` routing — PRD pitfall #3 mitigation (no LLM in the supervisor).

**KR2 — 50-case eval gate + pre-push hook** (9 tasks, commits `63ff72e8c`–`c22de6d2e`):
- 5 boolean scorers in `copilot/evals/scorers/{schema_valid,citation_present,factually_consistent,safe_refusal,no_phi_in_logs}.py`.
- Runner `copilot/evals/runner.py` with YAML loader, threshold logic (<0.95 OR >5pp drop → exit 1), `RESULTS.md` writer, baseline freeze.
- 50 YAML cases under `copilot/evals/cases/{extraction,retrieval,citation,refusal,phi,cross}/*.yaml` — 15+10+10+5+5+5.
- `make eval-fast` (12-case subset, ~2s) + `make eval-baseline`; `bash copilot/scripts/install-hooks.sh` writes `.git/hooks/pre-push`.
- README "Verifying the W2 eval gate" section with the regression-repro recipe (comment out `check_extracted_fact_has_source_doc` → cross category drops → exit 1).
- `.github/workflows/copilot-ci.yml` extended with the W2 50-case gate step.

**KR3 — TurnTrace 6-field extension + Langfuse generation spans** (6 tasks, `103bb9964`–`324ae99be`):
- TurnTrace gains `routing_path`, `extraction_confidence_min`, `retrieval_hit_ids`, `rerank_scores`, `vlm_cost_estimate_usd` (populator deferred to Final), `documents_attached`.
- Per-LLM-call `langfuse.generation()` spans in `AnthropicAdapter.call` + `OpenAIAdapter.call` via `app/observability/llm_span.py` (singleton-cached, no-ops without LANGFUSE_PUBLIC_KEY).
- PHI eval cases extended to scan all 6 new fields.

**KR4 — Reranker scaffolding** (1 task, `22e59d28d`):
- `app/retrieval/rerank.py` with `Reranker` Protocol + `IdentityReranker` (CI default), `CohereReranker` (lazy `cohere` import; `COHERE_API_KEY` gated), `LocalCrossEncoderReranker` (lazy `sentence_transformers` import).
- `get_reranker()` factory + `evidence_retriever` wires the chosen reranker → `state.rerank_scores`.
- Dense retrieval (OpenAI embeddings) explicitly Final-deferred to avoid paid-API exposure in CI.

**KR5 — Front-desk LITE (Tier 2)** (3 tasks, `10e9cd5dd`, `4387836fb`, `5b9af3243`):
- `GET /v1/sessions/{session_id}/pending_intakes` endpoint, panel-gated, reads from local `processed_documents` SQLite.
- Iframe banner top-of-rail with toggle + collapsible list; click → existing bbox modal; per-session in-memory dismiss.
- `acl_upgrade.php` v14 grants `Front Office` group write on `patients|docs`. PHP syntax verified via `php:8.2-cli` docker image.
- `copilot/README.md` "Front-desk demo prep" section with manual upload procedure.

**Test count: 140 passed, 3 skipped (live_llm). 50/50 eval cases. Both `make eval` and `make eval-fast` exit 0.**

**Next: deploy + manual smoke-test (instructions in `activeContext.md`); Final-scope items below.**

### Codex review hardening — 8 rounds, 18 findings fixed (2026-05-06 evening, post night-shift)

After the night-shift wrote per-task `code-review.txt` files marked `CODEX UNAVAILABLE — SELF-REVIEW` (codex CLI wasn't installed at run time), the user installed `@openai/codex` (v0.128.0) and ran 8 successive `codex review --base 56c467c70 -c 'model_reasoning_effort="high"'` passes against the branch. **Every reported P1 / P2 was fixed and re-verified by the next round.** Round 9 hit the daily quota cap (resets ~4:23 PM); decision was to pause iteration there since rounds 7-8 were down to UX edge cases.

**Cumulative findings across all rounds:** 3 P1 + 15 P2 = 18 distinct issues. Per-round catalog in `.night-shift/runs/2026-05-06-0104/external-reviews/triage.md`. Selected highlights:

- **F3 (P1)** — `check_evidence_chunk_in_corpus` split chunk_ids on `#`, but corpus chunk_ids ALREADY embed `#sec-N.M` (e.g. `uspstf-statin-2022#sec-2.1`). The split truncated to `uspstf-statin-2022`, failed the membership test, and the critic refused every guideline-citing answer in production.
- **F12 (P1)** — Round-5 FHIR preview fallback only verified the QUERY-PARAM `patient_id` was on the caller's panel; never checked that the FETCHED `DocumentReference.subject.reference` matched. **Cross-patient PHI leak.** Fixed: subject-reference equality check (with absolute-ref normalization in F17).
- **F5 (P1)** — CI's `ruff check .` step was failing with 24 F401 unused-import violations across the copilot tree (mix of new + pre-existing). The W2 50-case eval gate could never even start running on PRs until this cleared.
- **F1 / F2 (P2)** — LangGraph workers' tool_results were appended AFTER `run_turn` finished, so the LLM never saw them. And `/v1/chat` never seeded `retrieval_seed_query`, so the supervisor never routed through `evidence_retriever`. Both made the "fan-out" decorative. Fixed: `seed_tool_results` plumbed into `run_turn`; keyword heuristic in `/v1/chat`.
- **F4 (P2)** — Reranker reordered results into trace fields but the answer_composer kept seeding the LLM with the original BM25 order. Fixed: replace `tool_result.data` with the reordered list before append.
- **F8 / F14 / F16 / F17 (P2)** — pending_intakes endpoint successively pivoted from local-SQLite (empty in production) to FHIR DocumentReference (with `date=ge…` recency filter), gained `user/Binary.read` OAuth scope for the preview fallback, and absolute-ref normalization for FHIR servers that emit `https://host/.../Patient/{id}`.
- **F10 / F18 (P2)** — Eager `ensureSession()` could race with the first chat submit (fixed: in-flight Promise cache); also created persisted conversation rows that polluted the resume offer (fixed: `AND turn_count > 0` in `find_recent`).
- **F15 (P2)** — Pre-push hook ignored git's stdin protocol and used fragile `@{u}` / `HEAD~1..HEAD` heuristics. Fixed: read pushed refs from stdin per the git pre-push contract.
- **F13 (P2)** — Retrieval prefetch could 500 the chat on FTS5 syntax errors (e.g. hyphenated queries). Fixed: try/except around dispatch + rerank, log + skip seed on failure.
- **F6 / F7 / F9 (P2)** — pending_intakes filtered to `front_desk_scan` source_path that no production writer records → permanently empty banner; `CohereReranker` selection raised mid-request when `cohere` wasn't installed; `FAST_SUBSET` had zero `cross` cases and `pass_rates_by_category` reports empty categories as 100%, so the README repro was silently green in eval-fast.
- **F11 (P2)** — Banner surfaced FHIR DocumentReferences but the bbox modal only knew the local SQLite store → modal 404s for the very docs the banner advertised. Fixed: preview falls back to FHIR DocumentReference + Binary.

**Branch tip after the codex hardening: `2cb643af9`. 163 tests pass (was 140 at end-of-night-shift, +23 fix-related tests added). 50/50 eval cases at 100%. Ruff clean. `make eval-fast` runs in ~2s. ~37 commits since `78d0672c7`.**

### Post-MVP polish sweep — 2026-05-07

Live smoke-test of the rebuilt iframe (against local OpenEMR after fixing `sqlconf.php` to point at `mysql:3306` and flipping `$config=1`) surfaced three real defects that survived the codex hardening:

1. **Over-citation on informational guideline questions** — "what does USPSTF say about X" pulled every Observation/Patient/Med citation even when the question was purely informational.
2. **Bbox red rectangle in the wrong position on PDF/PNG previews** — VLM-emitted bboxes routinely covered the entire row instead of the value digits.
3. **Citation chips for non-`DocumentReference/` record_ids opened a "blank" modal** — the JS early-returned with tiny "(non-document citation: …)" text on a 800x1000 white canvas.

Then a fourth issue surfaced when the user re-tested:

4. **Single-value question over-share** — "What was the HbA1c?" returned HbA1c + fasting glucose + eGFR (the whole panel).

And a fifth defect when running the documented regression-repro:

5. **The "comment out a Layer-2 rule, run `make eval-fast`, see exit 1" recipe was silently green** — the runner is fixture-driven and never invokes `apply_rules`.

**Five commits closed all five:**

| Commit | What it ships |
|---|---|
| `f40cf3880` | `feat(agent,evals): scope guideline questions — informational vs applied` (Phase A) — situational paragraph in `app/agent/prompt.py` + 2 fixture cases in `evals/cases/citation/` (`informational_guideline_question.yaml`, `applied_guideline_question.yaml`). |
| `053c5451d` | `fix(ingestion,ui): tighten VLM bbox + PDF text-snap overlay` (Phase B) — `_LAB_PROMPT` and `_INTAKE_PROMPT` rewritten to bind only the printed value; `encode_record_id_for_vlm` gains optional `raw_text` kwarg appended as `&q=` URL fragment; iframe `_snapBboxToText` helper text-snaps via `pdfPage.getTextContent()`. |
| `0948b78ff` | `feat(agent,ui): per-type evidence cards for non-DocumentReference citations` (Phase C) — `EvidenceKind` / `EvidenceRecord` + `AgentResponse.evidence_records`; new `app/agent/evidence.py::extract_evidence_records()`; iframe modal router + 9 per-type renderers (Observation / Medication / Allergy / Condition / Encounter / Patient / Guideline / QuestionnaireResponse / Unknown); HTML/CSS card mode. |
| `e21497fdc` | `fix(ui,agent): smarter PDF text-snap + scope single-value questions` (Phase B+) — text-snap matcher prefers numeric tokens disambiguated by y-proximity (the loose `target.includes(s)` clause was matching first-substring like "LDL"); prompt addition for "specific value vs panel summary" pattern. |
| `b35e7bce6` | `feat(ingestion): server-side OCR-snap for image (PNG/JPG) bboxes` (Phase D) — new `app/ingestion/ocr.py` with `ocr_items()` + `snap_bbox()`; wired into `app/ingestion/service.py::_ocr_snap_extraction()` (mutates `source_citation.bbox` in place after VLM, in a worker thread); Dockerfile gains `tesseract-ocr` apt + `pytesseract>=0.3.10` + `Pillow>=10.0` deps. |
| `f19f43514` | `feat(evals): make the documented Layer-2 regression-repro actually fire` — root cause: `make eval-fast` invokes scorers (`schema_valid`, `citation_present`, …) which never call `apply_rules`. Fix: `evals/scorers/rules_block_regression.py` (calls `apply_rules` and asserts `result.passed is False` on a fixture engineered to trigger `check_extracted_fact_has_source_doc`) + `evals/cases/cross/cross_layer2_regression_canary.yaml` (the canary fixture) + `FAST_SUBSET` registration. README rewritten with the actual expected output. |

**Branch tip: `f19f43514`. 174 tests pass (was 163 + 5 evidence + 6 OCR-snap), 53/53 eval cases at 100% across all 6 PRD-named categories, ruff clean. `make eval-fast` exits 0 with rule active, exit 2 with rule commented (cross drops to 66.7%). 44 commits since `78d0672c7`.**

---

### Front-desk deferred-extraction upload path — 2026-05-07 late evening

The deferred Documents-tab UI item (per `copilot/HANDOFF.md`) was closed by re-architecting the front-desk arc instead of fighting the upstream `openemr/openemr:latest` Zend module. The native Documents tab on the deployed image renders an empty Uploader/Viewer because the supporting CSS (`<theme>/documents.css` defining `doc-doc-ls-*` classes) and Angular controllers (`documents.js`, `documentsController.js`) are absent from our fork — sourcing them from an older upstream tag would have been brittle.

**New flow** (commit `5e63e5fb9`): the front desk drops files onto the existing Co-Pilot iframe rail; the server detects the front-desk role via `COPILOT_FRONT_DESK_USERS` env, bypasses the per-physician panel gate, stores the raw file with a `{"_pending": True}` marker, and skips VLM. The physician's pending-intake banner surfaces the row with `is_pending=true`; clicking it POSTs `/v1/documents/{doc_id}/process` which runs VLM on demand (panel-gated, idempotent) and updates the row in place.

**What shipped:**

| File | Change |
|---|---|
| `app/config.py` | New `copilot_front_desk_users` setting |
| `app/ingestion/service.py` | `attach_only` (skip-VLM path) + `process_pending` (lazy on-demand extraction); `IngestionResult.extraction` now `Optional` with `is_pending: bool` flag |
| `app/persistence/processed_documents.py` | `list_pending_uploads(patient_pseudonym, since)` + `replace_extraction(...)` |
| `app/main.py` | `/v1/documents/attach` defer logic; pending-intakes banner merges local `_pending` rows; new `POST /v1/documents/{doc_id}/process` endpoint |
| `app/web/copilot_iframe.js` | Pending-aware system message; banner click handler runs `/process` before opening modal |
| `evals/ingestion/test_attach_defer.py` | NEW (6 tests) |
| `evals/agent/test_pending_intakes.py` | Extended (+2 tests for local-pending merge) |

**Branch tip: `5e63e5fb9`. 182 tests pass (was 174). `make eval-fast` 15/15 100% across all 6 categories — no regression. 47 commits since `78d0672c7`. Pushed to GitHub `rikkiiwang/openemr` and GitLab `labs.gauntletai.com/ruijingwang/openemr`. Railway env var `COPILOT_FRONT_DESK_USERS=Reception Desk` set on the copilot service; `/healthz` 200.**

---

### Confirm/Reject UX + OpenEMR REST write-back + persistence fix — 2026-05-08 late evening

Live testing of the front-desk arc surfaced two issues: (1) repeat uploads of the same file weren't deduping — root cause: `processed_documents` SQLite at `./copilot_docs.db` resolves to `/srv/copilot_docs.db` on Railway (writable layer, NOT the `/data` volume), so every redeploy wiped the table; (2) the click-to-extract flow ended with the banner item silently disappearing — no clear "saved to chart" affordance, and the agent couldn't answer "what's new for this patient?" because confirmed-vs-pending state wasn't tracked.

**Resolution at `c2534e416`** (Plan B per the 2026-05-08 plan):

| File | Change |
|---|---|
| `copilot/Dockerfile` | Add `COPILOT_DOCS_DB_PATH=/data/copilot_docs.db` to ENV — moves SQLite to volume |
| `copilot/app/persistence/processed_documents.py` | New `confirmed_at` / `rejected_at` / `confirmed_by` / `external_doc_id` columns (idempotent ALTER); new `mark_confirmed` / `mark_rejected` / `list_confirmed_recent` methods; refactored row inflation behind `_row_to_doc` helper + `_SELECT_COLUMNS` constant |
| `copilot/app/fhir/client.py` | New `post_document_via_rest_api` method — calls OpenEMR's non-FHIR REST API at `POST /apis/default/api/patient/{puuid}/document` using the same OAuth bearer the FHIR client already carries |
| `copilot/app/main.py` | New `POST /v1/documents/{doc_id}/confirm` (orchestrates OpenEMR write-back + local mark; fail-soft on REST errors) and `POST /v1/documents/{doc_id}/reject` (local-only soft-delete); pending-intakes banner merge surfaces confirmed-recent rows alongside pending ones; `PendingIntakeItem` model gains `confirmed_at` / `rejected_at` |
| `copilot/app/web/copilot_iframe.{html,js,css}` | Modal footer with `[Confirm & save to chart]` / `[Reject]` buttons; banner state coloring (`[needs review]` / `[confirmed]` / `[rejected]`); button handlers POST to the new endpoints with status feedback |
| `copilot/app/tools/document_tools.py` | `get_recent_uploads` schema gains `confirmed_only: bool` + `since_days: int` args; routes to `list_confirmed_recent` when `confirmed_only=true` |
| `copilot/app/agent/prompt.py` | New paragraph: "What changed?" / "What's new for this patient?" — guidance to call `get_recent_uploads(confirmed_only=true)` and contrast with prior FHIR data |
| `copilot/evals/ingestion/test_confirm_reject.py` | NEW — 6 tests (happy path + fail-soft + 404 + panel-gate × confirm/reject) |
| `copilot/evals/tools/test_document_tool.py` | Extended +1 test verifying `confirmed_only=true` excludes pending AND rejected rows |

**Branch tip: master at `c2534e416`. 189 tests pass (was 182 — 7 new). `make eval-fast` 15/15 100%. 49 commits since `78d0672c7`. Master + feat both at `c2534e416` on GitHub + GitLab; Railway redeployed; new code probed live (`/static/copilot_iframe.js` contains `bbox-modal-confirm`; `/v1/documents/test/confirm` returns 403 panel-gate, not 404 — endpoint is registered).**

---

### Panel-gate relax for empty `generalPractitioner` — 2026-05-08

Live testing surfaced: only `admin` could see the pending banner; clinicians who have Mariela in their OpenEMR-side panel (per `patient_data.providerID`) got 403 from `/v1/sessions/{sid}/pending_intakes` because the Co-Pilot panel gate's FHIR-derived check requires the requesting clinician's Practitioner UUID to appear in `Patient.generalPractitioner`. OpenEMR's R4 transformer never populates that field on Synthea/Railway data, so `owners=[]` and `practitioner_uuid not in []` always 403'd.

Fix at `37331e54b`: when `owners` is empty, fall through to allow with an INFO log. The OpenEMR-side awk-injected `copilot-demographics-gate.php` already enforces real per-physician scope via the OpenEMR DB. The FHIR-derived check is defense-in-depth that can't reach a defensible verdict without the upstream field. New `test_fhir_panel_allows_when_general_practitioner_empty` + `test_fhir_panel_still_denies_when_general_practitioner_lists_others` regression-pin both sides. **191 tests pass** (was 189 — 2 new). `make eval-fast` 15/15 100%.

### Modal viewer UX (rail-expand + zoom toolbar) — 2026-05-08 (later)

Live testing showed the bbox modal is unusable for reviewing PDFs/PNGs: the modal sits inside a 400px-wide iframe rail and `90vw` of that = ~360px, while a PDF page renders at scale 1.5 (~1100px wide) — physicians had to scroll horizontally and zoom every time they reviewed a pending intake.

Fix at `196d75e61`:

| File | Change |
|---|---|
| `copilot-rail-fragment.php` | New CSS rule `body.copilot-doc-open #copilot-rail { width: 80vw; }` + `<script>` listener for postMessage from the iframe (origin-guarded against non-rail sources) |
| `copilot/app/web/copilot_iframe.html` | Modal header gains zoom toolbar (`−` / `+` / `Fit width` / `Fit page`) |
| `copilot/app/web/copilot_iframe.css` | Zoom button styling |
| `copilot/app/web/copilot_iframe.js` | Send `copilot-doc-modal-open` before `modal.showModal()` and `copilot-doc-modal-close` in the close listener; refactored PDF + image renderers to cache source in `docPreviewState` and re-render at any scale via `_renderAtCurrentScale()`; default scale on open = `_fitWidthScale()` (clamped [0.4, 4]); zoom buttons re-render |

Pure UX change — no new tests required. Existing 191 tests still pass; eval-fast 15/15 100%. The rail-fragment.php change required a Railway rebuild of the **OpenEMR service** (awk-injected into demographics.php at build time); copilot service picked up the iframe JS/HTML/CSS via static-file route on its own redeploy.

**Branch tip: master at `196d75e61`. 191 tests pass. `make eval-fast` 15/15 100%. 51 commits since `78d0672c7`. Pushed to GitHub + GitLab; both Railway services redeployed; copilot service probed live (`/static/copilot_iframe.js` contains `copilot-doc-modal-open`).**

---

## ✅ W2 Surprise Challenge — Patient Dashboard Port (shipped 2026-05-09)

Branch `feat/dashboard-modernize` (14 commits ahead of master `073e66388`),
night-shift run `2026-05-09-0213`. Not yet merged/pushed.

**KRs shipped (all task acceptance green; 109/109 vitest tests):**

| KR | Title | Tasks | Total | Notes |
|---|---|---|---|---|
| KR2 | Bootstrap pinned Next.js 15 / React 19 skeleton | 3 | 52 min | KR1 codex-rejected for unpinned scaffold |
| KR4 | OAuth/PKCE login + FHIR proxy (no panel-scope) | 4 | 73 min | KR3 codex-rejected for bundled middleware |
| KR5 | Patient header + six clinical cards | 4 | 9 min | Allergies, Problems, Meds, Rx, CareTeam, Encounters |
| KR6 | Co-Pilot iframe rail component | 1 | 3 min | sandboxed; no physician_user_id (deferred) |
| KR7 | CI workflow + defense doc + memory bank | 3 | (in progress) | dashboard-ci.yml + PATIENT_DASHBOARD_MIGRATION.md |

**Stack pinned exact:** next 15.5.18 · react/react-dom 19.2.6 · typescript 5.9.3 · tailwindcss 4.3.0 · vitest 4.1.5 · jsdom 29.1.1 · @types/node 25.6.2.

**Architecture (defense in `PATIENT_DASHBOARD_MIGRATION.md`):**
- Server-side OAuth proxy at `/api/fhir/[...path]` injects bearer token; browser only holds signed httpOnly `dashboard_session` cookie.
- In-memory token store with single-flight refresh + revocation tombstone for logout/refresh race; cookie-bound `sessionExpiresAt` eviction.
- Six cards as Server Components fetching in parallel via `fhirGet()`.
- Co-Pilot rail as a sandboxed iframe (Co-Pilot service unchanged).

**Codex review state:** Rounds 1-N codex-reviewed for KR2/KR4 tasks (clean after iterations). Codex hit usage limit ~03:50 PT during Task 4.4 round 6 → all subsequent reviews use rigorous self-adversarial reviews per skill protocol (`CODEX UNAVAILABLE — SELF-REVIEW` headered files in `.night-shift/runs/2026-05-09-0213/key-results/*/tasks/*/code-review.txt`).

**Deferred (explicit out-of-scope; documented in `PATIENT_DASHBOARD_MIGRATION.md` §5):**
- Panel-scope authorization inside the proxy (was rejected from KR3 v1; needs to land before patient access goes wide).
- ID-token decode at OAuth callback to extract OpenEMR username → store in session cookie → pass to Co-Pilot iframe as `physician_user_id`.
- Patient finder / search.
- Edit forms (legacy `demographics_full.php` keeps serving these).
- Live FHIR e2e (Playwright not installed; manual smoke against deployed Railway).
- TanStack Query for action-driven refresh.

**No existing source files modified** beyond the memory bank. Verified by planning (`.gitignore`, `Dockerfile` both untouched per plan §11).

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
| 12 | Upstream `openemr/openemr:latest` Documents tab renders empty Uploader/Viewer | accepted limitation | Missing `<theme>/documents.css` + `documents.js` + `documentsController.js` in our fork (verified 2026-05-07). Routed around in `5e63e5fb9` by shipping the front-desk arc through the Co-Pilot iframe drop-zone with a defer flag. The native tab is no longer on the demo path. |
| 13 | Reception Desk can upload but cannot chat (`/v1/chat` returns 404 because `/v1/sessions` 403s on the Co-Pilot panel-gate) | accepted limitation, fix is one-line | Reception Desk is a front-desk role and shouldn't need to chat per the W2 Tier-2-LITE design. To enable chat for Reception Desk, set `PHYSICIAN_PATIENT_PANEL` Railway env var to include `"Reception Desk": ["*"]`. Not done by default — the role split is intentional. |

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
