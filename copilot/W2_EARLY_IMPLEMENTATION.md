# W2 Early-Submission Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Pass the Week 2 Early-Submission grading bar by **Thursday 2026-05-07 11:59 PM CT**. Two deliverables matter:

1. **The eval gate** — a 50-case golden set with five named boolean rubrics, run by a PR-blocking pre-push Git hook. PRD §p.5 hard gate: *"During grading, we will introduce a small regression and confirm your CI gate fails. If the eval gate does not block the regression, the Week 2 build does not pass."* This is the single non-negotiable.
2. **The supervisor + 2-worker LangGraph** with a critic node that enforces Layer-1 attribution + extended Layer-2 rules with no LLM, plus a 6-field extension to `TurnTrace` and Langfuse generation spans so the model identity is visible in the trace UI.

**Stretch (lands if time permits, otherwise carries to Final):**
- A "front-desk lite" slice — grant `patients|docs` write to OpenEMR's existing `Front Office` ACL group, pre-stage 2 intake fixtures via the Documents Zend module as a synthetic front-desk user, render a stub banner in the iframe driven by a new `GET /v1/sessions/{id}/pending_intakes` endpoint. The full facility-scope helper, dataset expansion to 18–20 patients × 2 facilities, and per-doc-acknowledgement persistence are explicitly **deferred to `W2_FINAL_IMPLEMENTATION.md`**.

**Architecture:** Wrap the W1 single-agent loop as the terminal `answer_composer` node of a LangGraph state machine. A plain-Python deterministic supervisor routes between `intake_extractor`, `evidence_retriever`, and `answer_composer`, with the new `critic` node running Layer-1 attribution + Layer-2 domain rules with **no LLM**. The supervisor is intentionally not an LLM (PRD pitfall #3). Routing decisions and per-step latencies land in the extended `TurnTrace`, which is what the eval suite scores and what Langfuse renders.

**Tech Stack delta over W2 MVP:**
- `langgraph` (Python) — `^0.2`. Pinned in `pyproject.toml`.
- `cohere` — optional dependency, lazy-imported. CI/eval works without `COHERE_API_KEY` via local fallback.
- `sentence-transformers` (`ms-marco-MiniLM-L6-v2`) — local cross-encoder reranker; optional; cached on disk.
- `pyyaml` — already a transitive dep, made explicit for `evals/cases/*.yaml` loader.

**Scope boundary — what this plan does NOT cover (deferred to `W2_FINAL_IMPLEMENTATION.md`):**
- Replacing the stub FHIR writes from `971affe8d` with real `POST /fhir/DocumentReference` (upstream OpenEMR endpoint missing).
- Round-trip eval (upload lab → re-fetch via `get_recent_labs` → correct `derivedFrom`).
- Full **facility-scope** front-desk role (`_verify_patient_in_facility` Python helper + facility-aware `copilot-finder-scope.php` / `copilot-demographics-gate.php` branches + Front Office ACL group enforcement on all front-desk endpoints).
- Full **dataset expansion** to 18–20 patients across 2 facilities (`scripts/seed_w2_dataset.py`, Synthea CCDA imports, provider/facility assignments, `users` rows for two front-desk staff).
- Full **pending-intake banner** with per-doc acknowledgement persisted in `processed_documents.acknowledged_by_physician_at`, expandable list with thumbnails, dismiss tracking across sessions.
- Cost & latency report (p50/p95, bottleneck section).
- 3–5 min demo video.
- Source-grounded UI polish.

**Baseline verified:** Master tip `78d0672c7`. W2 MVP shipped to Railway 2026-05-05 evening. The bbox-overlay PDF/PNG rendering fix (uncommitted as of 2026-05-06 morning, see `progress.md`) is rolling in as part of the Early submission once verified locally.

---

## File Map

**Already exists, used unchanged:**
- `copilot/app/agent/{loop,llm,prompt,schemas,prewarm}.py` — W1 loop becomes the terminal `answer_composer` body.
- `copilot/app/verification/{attribution,rules}.py` — Layer-1 + Layer-2; pulled into the new critic node.
- `copilot/app/observability/{trace,vlm_span}.py` — `TurnTrace` is extended (additive); `vlm_span` untouched.
- `copilot/app/tools/{_base,registry}.py` and the 11 tool modules — no changes.
- `copilot/app/retrieval/corpus.py` — BM25 + corpus loader; gets a sibling `dense.py` and `rerank.py`.
- `copilot/evals/conftest.py` — `@pytest.mark.live_llm` skip-by-default; reused for the 50-case live-LLM cases.

**Will be modified:**
- `copilot/app/main.py` — add `/v1/sessions/{id}/pending_intakes` (front-desk lite); change `/v1/chat` to dispatch through the LangGraph state machine instead of calling `run_turn` directly.
- `copilot/app/observability/trace.py` — extend `TurnTrace` with the 6 new fields.
- `copilot/app/agent/llm.py` — wrap each adapter's `call()` in a `langfuse.generation()` span so the model identity surfaces in the Langfuse UI.
- `copilot/app/retrieval/corpus.py` — extract a `Reranker` Protocol; add `rerank()` step after BM25.
- `copilot/Makefile` — add `eval-fast` target (subset of 50 cases, <2 min).
- `copilot/pyproject.toml` — add `langgraph`, `pyyaml` (explicit), optional `[rerank]` extra for `cohere` + `sentence-transformers`.
- `copilot/evals/RESULTS.md` — auto-rewritten by the rubric runner with per-category pass-rate table.
- `copilot/app/web/copilot_iframe.{html,js,css}` — pending-intake banner stub (front-desk lite).
- `copilot/README.md` — document the regression-reproduction recipe so graders can trip the gate.
- `acl_upgrade.php` — one-line addition to grant `patients|docs` write to the `Front Office` group (front-desk lite).

**Will be created:**
- `copilot/app/graph/__init__.py`
- `copilot/app/graph/state.py` — `AgentGraphState` TypedDict (or Pydantic).
- `copilot/app/graph/supervisor.py` — plain-Python routing function `decide_next(state) -> Literal[...]`.
- `copilot/app/graph/workers/__init__.py`
- `copilot/app/graph/workers/intake_extractor.py` — wraps `attach_and_extract` tool + per-fact bbox emission.
- `copilot/app/graph/workers/evidence_retriever.py` — wraps `search_guidelines` tool + dense + rerank.
- `copilot/app/graph/workers/answer_composer.py` — wraps W1 `run_turn` as a node.
- `copilot/app/graph/critic.py` — Layer-1 attribution + Layer-2 rules (`check_extracted_fact_has_source_doc`, `check_evidence_chunk_in_corpus`) — no LLM.
- `copilot/app/graph/build.py` — assembles the LangGraph and returns a compiled graph instance.
- `copilot/app/retrieval/dense.py` — OpenAI embeddings → SQLite BLOBs → numpy cosine.
- `copilot/app/retrieval/rerank.py` — `Reranker` Protocol + `CohereReranker` + `LocalCrossEncoderReranker` + `get_reranker()` factory.
- `copilot/evals/cases/extraction/*.yaml` — 15 cases.
- `copilot/evals/cases/retrieval/*.yaml` — 10 cases.
- `copilot/evals/cases/citation/*.yaml` — 10 cases.
- `copilot/evals/cases/refusal/*.yaml` — 5 cases.
- `copilot/evals/cases/phi/*.yaml` — 5 cases.
- `copilot/evals/cases/cross/*.yaml` — 5 cases.
- `copilot/evals/scorers/{schema_valid,citation_present,factually_consistent,safe_refusal,no_phi_in_logs}.py` — five boolean scorers.
- `copilot/evals/runner.py` — loads cases, executes, scores, writes per-category table to `RESULTS.md`.
- `copilot/scripts/install-hooks.sh` — writes `.git/hooks/pre-push` running `make eval-fast`.

---

## Tasks

### Tier 1 — Eval gate critical path (must ship Thursday)

#### 1. Author this plan + lock scope

- [ ] Confirm with user that the W2 MVP is in a known-good state (`78d0672c7` + uncommitted bbox-overlay PDF/PNG fix verified locally).
- [ ] Confirm the front-desk feature is split: lite slice in this plan, full feature in `W2_FINAL_IMPLEMENTATION.md`.
- [ ] Commit the bbox-overlay fix (`copilot/app/web/copilot_iframe.{html,js}`) so master is clean before LangGraph work starts.

#### 2. LangGraph skeleton — terminal-node refactor (no behavior change)

- [ ] Add `langgraph` to `pyproject.toml`; `pip install -e .` in the Dockerfile pass.
- [ ] Create `app/graph/state.py` — `AgentGraphState` TypedDict with `{question, patient_id, session_id, tool_results, claims, routing_path, extraction, retrieval}`.
- [ ] Create `app/graph/workers/answer_composer.py` — calls today's `run_turn(question, session)` exactly as `/v1/chat` does, returns `{response, claims, tool_results}` into state.
- [ ] Create `app/graph/build.py` — single-node graph (just `answer_composer`); compile and expose `build_graph()`.
- [ ] Change `/v1/chat` in `app/main.py` to invoke the compiled graph instead of `run_turn` directly. **Behavior must be identical** — all 75 existing tests stay green. This is the refactor checkpoint.
- [ ] Run `pytest -q` — confirm green.

#### 3. Worker nodes + plain-Python supervisor

- [ ] Create `app/graph/workers/intake_extractor.py` — wraps `attach_and_extract` invocation when `state["pending_extraction"]` is non-empty; emits per-fact `record_id`s into `state["tool_results"]`.
- [ ] Create `app/graph/workers/evidence_retriever.py` — wraps `search_guidelines`; pushes results into `state["retrieval"]` and `state["tool_results"]`.
- [ ] Create `app/graph/supervisor.py` — `decide_next(state) -> Literal["intake_extractor", "evidence_retriever", "answer_composer", "END"]`. Routing rules per `W2_ARCHITECTURE.md §4.1` (deterministic, not LLM-routed). Append the chosen node name to `state["routing_path"]`.
- [ ] Update `app/graph/build.py` — wire conditional edges from supervisor to each worker, fan back to supervisor, terminate at `answer_composer`.
- [ ] Add `tests/graph/test_routing.py` — 6 cases covering each route + the empty-state fallthrough.

#### 4. Critic node + extended Layer-2 rules

- [ ] Extract Layer-1 (`app/verification/attribution.py`) and Layer-2 (`app/verification/rules.py`) calls out of the W1 loop and into `app/graph/critic.py` as a single deterministic step.
- [ ] Add `check_extracted_fact_has_source_doc(state)` — every extraction-derived claim's `record_id` must resolve to a `DocumentReference/...` in `state["tool_results"]` from this turn.
- [ ] Add `check_evidence_chunk_in_corpus(state)` — every retrieval-derived claim's `Guideline/{chunk_id}` must be present in the corpus loader's known chunk set (no fabricated chunks).
- [ ] Wire critic as the post-`answer_composer` node — on rejection, return to `answer_composer` for one retry with the failure reason in state; second failure becomes a "cannot verify" refusal.
- [ ] `pytest evals/agent/` — confirm green; the existing 42 W1 cases must still pass through the new critic node unmodified.

#### 5. 50-case golden set

- [ ] Create `evals/cases/` directory tree (6 subdirs per `File Map`).
- [ ] Author 15 extraction cases — 8 `lab_pdf`, 4 `intake_form_pdf`, 3 `intake_questionnaire_response`. Each YAML lists the fixture path, expected schema fields, expected bbox count, expected per-field confidence floor.
- [ ] Author 10 retrieval cases — query → expected top-1 `chunk_id` in the 12-chunk MVP corpus (later 50-chunk corpus).
- [ ] Author 10 citation cases — turns whose answer must cite specific record_ids; tests Layer-1 attribution.
- [ ] Author 5 refusal cases — questions whose answer must be empty + `data_gaps` non-empty.
- [ ] Author 5 PHI cases — fixtures injecting known PHI substrings (`Margaret`, `Chen`, `1967-08-14`, `shellfish`, `iodine`); `TurnTrace` must contain none.
- [ ] Author 5 cross-functional cases — end-to-end flows touching at least 2 of the above categories.

#### 6. Boolean rubric scorers + RESULTS.md writer

- [ ] Implement five scorers in `evals/scorers/*.py`. Each: `def score(case, run_output) -> tuple[bool, str]`. Pure functions, no LLM.
- [ ] Implement `evals/runner.py` — loads YAML cases, calls the graph, runs scorers, writes per-category pass rates to `evals/RESULTS.md` with a markdown table.
- [ ] Threshold logic: any category drops >5pp vs baseline (stored in `evals/baseline.json`) OR <0.95 → exit code 1.

#### 7. PR-blocking pre-push Git hook

- [ ] Add `make eval-fast` to `copilot/Makefile` — runs a 12-case subset (2 from each category + cross) under 2 minutes, mocked LLM only.
- [ ] Write `scripts/install-hooks.sh` — copies a `pre-push` hook into `.git/hooks/`. Hook runs `make -C copilot eval-fast` and blocks the push on non-zero exit.
- [ ] Document the regression-reproduction recipe in `copilot/README.md`: a one-paragraph "How to verify the eval gate works" with a deliberate-regression patch graders can apply to confirm CI fails. Example: comment out the `check_extracted_fact_has_source_doc` rule → eval suite must fail the citation category.

#### 8. TurnTrace 6-field extension

- [ ] Extend `TurnTrace` (`app/observability/trace.py`) with: `routing_path: list[str]`, `extraction_confidence_min: float | None`, `retrieval_hit_ids: list[str]`, `rerank_scores: list[float]`, `vlm_cost_estimate_usd: float | None`, `documents_attached: int`. All Optional; backwards-compatible with W1 traces.
- [ ] Populate from supervisor (routing_path), workers (extraction/retrieval), reranker (rerank_scores), ingestion service (vlm_cost_estimate, documents_attached).
- [ ] Update the no-PHI eval (`test_no_phi_in_vlm_spans`) to also scan the new fields.

#### 9. Langfuse generation spans

- [ ] Wrap `AnthropicAdapter.call` and `OpenAIAdapter.call` in a `langfuse.generation()` span with `model`, `prompt_tokens`, `completion_tokens`, `latency_ms`. The model name then surfaces in the Langfuse UI directly, not just Railway stdout.
- [ ] Verify in Langfuse Cloud: a fresh chat turn shows a parent `agent_turn` trace with child `generation` spans labeled `claude-sonnet-4-6` (or `gpt-5.1`) per LLM call.

#### 10. Dense + rerank (lower priority — ships if Tier 1 finishes Thursday afternoon)

- [ ] Create `app/retrieval/dense.py` — OpenAI `text-embedding-3-small` over the 12 corpus chunks; store as SQLite BLOBs; numpy cosine similarity for query-time scoring.
- [ ] Create `app/retrieval/rerank.py` — `Reranker` Protocol; `CohereReranker` (lazy-imports `cohere`); `LocalCrossEncoderReranker` (lazy-imports `sentence_transformers`); `get_reranker()` factory that picks based on `COHERE_API_KEY` presence.
- [ ] Update `evidence_retriever` to call `bm25.search() ⊕ dense.search() → rerank()` and cap at top-3.
- [ ] Add 5 retrieval cases comparing pre- and post-rerank ordering.
- [ ] If the 12-chunk corpus is too small for rerank to demonstrate value, expand to ~50 chunks per `W2_ARCHITECTURE.md §5.1` plan.

### Tier 2 — Front-desk LITE (only if Tier 1 is solid by Thursday morning)

#### 11. ACL grant + 1-2 pre-staged docs

- [ ] In `acl_upgrade.php`, grant `patients|docs` write to the `Front Office` group. One-line ACL change per `W2_ARCHITECTURE.md §2.0`.
- [ ] Create one synthetic front-desk user (`front-rfm`) in OpenEMR, member of the `Front Office` group, assigned `users.facility_id = 3` (default).
- [ ] As that user, upload **2 pre-staged intake fixtures** (one PDF, one PNG) via OpenEMR's stock Documents Zend module to two existing W1 patients (e.g., Mariela + Dana). The resulting `DocumentReference.author` will be `Practitioner/{front-rfm-id}` — real, auditable.

#### 12. Pending-intake endpoint (panel-gated, no facility scope yet)

- [ ] Add `GET /v1/sessions/{session_id}/pending_intakes` to `app/main.py`. Body: list of `{doc_id, filename, uploaded_at, uploaded_by_role, doc_type}` for the session's `patient_id`.
- [ ] Source: FHIR `DocumentReference?patient=...` filtered to those NOT in `processed_documents` (or in `processed_documents` but with `acknowledged_by_physician_at IS NULL` once that column is added in Final).
- [ ] Re-uses `_verify_patient_in_panel` — no new gate. The full `_verify_patient_in_facility` helper is **explicitly Final-scope**.
- [ ] Add `evals/api/test_pending_intakes.py` — verifies endpoint returns the 2 pre-staged fixtures for Mariela's session, returns empty for Dana's.

#### 13. Iframe banner stub

- [ ] After session-bootstrap in `copilot/app/web/copilot_iframe.js`, fetch `/v1/sessions/{id}/pending_intakes`. If the list is non-empty, render a top-of-iframe banner: *"N intake documents uploaded by front desk — review."*
- [ ] Click expands an inline list (one row per doc); click row opens existing `<dialog id="bbox-modal">` with the source rendered (re-uses the bbox-overlay PDF/PNG fix from this morning).
- [ ] **Acknowledgement is in-memory only** for the lite slice — closing the modal removes the row from the visible list, but reopening the iframe re-fetches and re-renders all pending docs. Persistent acknowledgement is Final-scope.
- [ ] CSS: banner styles in `copilot_iframe.css` matching the existing light-theme palette.

---

## Eval gate setup (specific to Task 5–7)

### `eval-fast` subset (the pre-push hook)

12 cases, ~2 min total runtime, all mocked-LLM:

- 2 extraction (1 lab_pdf + 1 intake_pdf, fixture-driven, mocked VLM response)
- 2 retrieval (BM25-only, no live embedding call)
- 2 citation (mocked LLM response with known record_ids)
- 2 refusal (mocked LLM response)
- 2 PHI (PHI-injection fixtures, scan TurnTrace)
- 2 cross-functional (mock-stitched)

### Full 50-case suite (CI on every PR + Thursday eval submission)

Same 50 cases, mocked LLM. The `@pytest.mark.live_llm` cases (≤5) only run when `ANTHROPIC_LIVE=1` is set — same pattern as W1.

### Threshold logic (the regression gate)

```
if any category pass_rate < baseline - 0.05 or any category < 0.95:
    sys.exit(1)
```

Baseline lives in `evals/baseline.json`, regenerated only by an explicit `make eval-baseline` (intentional human review step). Graders' regression injection lands in some category; the threshold logic triggers; `pre-push` blocks; CI fails on the PR; Week 2 hard gate satisfied.

---

## Verification (end-to-end)

1. **All 42 W1 + 33 W2-MVP tests still pass** after the LangGraph refactor (Task 2 checkpoint). Regression here = STOP.

2. **`make eval` (full 50-case suite)** writes `RESULTS.md` showing per-category pass rates ≥ 0.95.

3. **Regression reproduction** (the documented recipe in `README.md`):
   - Apply a 1-line patch that breaks one rubric (e.g., comment out `check_extracted_fact_has_source_doc`).
   - Run `make eval` — exit code 1, `RESULTS.md` shows the broken category dropping below 0.95.
   - Run `git push` — pre-push hook blocks with the same exit code.
   - Revert patch, push succeeds.

4. **Langfuse trace UI** — open a fresh trace from the deployed Co-Pilot; confirm the parent `agent_turn` trace contains child `generation` spans with the model name visible (`claude-sonnet-4-6`).

5. **TurnTrace 6 fields** — `pytest tests/observability/test_turn_trace.py` confirms a turn populates all six fields with non-default values when the routing path covers all 3 workers.

6. **Front-desk lite (only if shipped)** — open the iframe for Mariela; banner shows *"2 intake documents uploaded by front desk — review"*; click expands list; click row opens bbox modal with the pre-staged PDF rendered. Open iframe for a patient with no pre-staged docs — banner does not render.

7. **Live deploy on Railway** — push to `master`; Railway auto-deploys; iframe at `https://openemr-production-0c8c.up.railway.app/` exercises the full path against Mariela's lipid panel + Dana's allergy contraindication. Eval suite has been run on master HEAD before deploy (CI gate).

---

## Risk register

| Risk | Likelihood | Mitigation |
|---|---|---|
| LangGraph refactor breaks the 75 existing tests | Medium | Task 2 is "no behavior change" — keep `run_turn` semantics identical. Run the full suite *before* moving to Task 3. |
| 50 cases is unrealistic for one person in 36 hours | High | Cases are mostly YAML-only authoring (no new code per case). Extraction cases re-use the synthetic fixtures already on master. Realistic ceiling: 12 cases authored deeply + 38 thinner cases referencing one fixture each. |
| Cohere rerank requires a paid API key in CI | Low | Local cross-encoder fallback is the default in CI. `COHERE_API_KEY` only set on the Railway service for prod traces. |
| Front-desk lite slips into Tier 1 by accident | Medium | This plan explicitly puts Front-desk under "Tier 2 — only if Tier 1 is solid by Thursday morning." Don't touch `acl_upgrade.php` or the iframe banner before the eval suite is green on master. |
| The graders' regression patch lands in a place no rubric covers | Low | All five PRD rubrics are covered with ≥5 cases each. Distribution across categories matches PRD's named scorers exactly. |
| Langfuse generation spans break the existing PHI-no-leak invariant | Low | `langfuse.generation()` only logs `model`, `prompt_tokens`, `completion_tokens`, `latency` — no message content. The no-PHI eval scans new fields too (Task 8). |
| pre-push hook annoys local development | Low | Hook only fires on `git push`, not on every commit. Bypass is possible (`--no-verify`) for emergencies but documented as a gate-violation. |

---

## Out of scope — deferred to `W2_FINAL_IMPLEMENTATION.md`

Captured here so they don't accidentally creep in:

- Real `POST /fhir/DocumentReference` (replacing the `971affe8d` stub). Round-trip eval test.
- `_verify_patient_in_facility` Python helper + facility-aware variants of `copilot-finder-scope.php` + `copilot-demographics-gate.php`.
- `scripts/seed_w2_dataset.py` — Synthea CCDA bulk import + provider/facility assignment + 2 front-desk users.
- `processed_documents.acknowledged_by_physician_at` column + per-doc dismiss tracking across sessions.
- Pending-intake banner expandable list with thumbnails + dismiss UX polish.
- Cost & latency report (`COST.md` extension).
- 3–5 min demo video.
- Source-grounded UI polish.
