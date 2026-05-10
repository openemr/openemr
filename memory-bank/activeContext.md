# Active Context

**Last updated:** 2026-05-09 (memory bank refresh — reconciles to master @ `30cd84d87`. Master is **83 commits** ahead of W2 MVP `78d0672c7`; **23 commits** ahead of last documented Phase 4 tip `35b7d1d7f`. Dashboard port branch `feat/dashboard-modernize` is **52 commits** ahead of master.)

## Where we are right now

**On master (`30cd84d87`):**
- All Phases 1–4 of W2 (`copilot/W2_IMPLEMENTATION.md`).
- W2 Surprise Challenge: dashboard port itself stayed on `feat/dashboard-modernize`, but the **integration with OpenEMR's patient finder shipped on master** (commits `0a49d038d`, `4b9f181a2`, `ad40380f3`, `cbeb2f03d`, `1d71642ec`, `77e4032fc`, `a0fa9b252`, `0e29e0aac`). Pattern documented as B14 in `systemPatterns.md`. A finder click in OpenEMR now lands on the modern dashboard for the right patient, with EHR-launch silent SSO.
- W2 Final partial: Cost & Latency Report (`30cd84d87`) — `copilot/COST.md` §§8-9 backed by live Railway p50/p95 data captured via `copilot/scripts/bench_latency.py`.
- Bug-class fixes between `35b7d1d7f` and `30cd84d87`: confirm/reject closure mutation, bbox overlay tightening (multi-token snap, row expansion, OCR re-snap on cache hit), `.gitignore` for `.night-shift/` + `.claude/`. None of these introduce new architectural surface.

**On `feat/dashboard-modernize` (`2cedf50d6`):** the dashboard port itself plus 52 sync/fix commits over master (notably frontend cookie `SameSite=None`/`Secure` fixes for prod iframe-embed and CSP `frame-ancestors` hardcoded fallback for prod). **Not yet pushed/merged** — user owns this.

**Outstanding for W2 Final (Sun 2026-05-10 noon CT):**
1. **3-5 min demo video** — user owns capture.
2. **Real `POST /fhir/DocumentReference`** — R4 has no route. Plan B REST path is OAuth-scope-blocked (`api:oemr` not on the token); fail-soft makes Confirm cosmetic-only. Three fix paths in `W2_IMPLEMENTATION.md` Phase 4.
3. **Dense retrieval** — BM25 + identity-rerank shipped. Reranker scaffolding (Cohere + local cross-encoder) ready; embedding store + dense scoring is the gap. Defensible-as-shipped if confronted, but a literal PRD reading is unmet.

The PRD hard-gate (eval gate fires on regression) **continues to fire** at `f19f43514` and forward — see `W2_IMPLEMENTATION.md` Phase 3 for the canary mechanism.

---

## Prior update (2026-05-09 — dashboard-port night-shift)

**Run `2026-05-09-0213`** shipped the W2 surprise challenge onto branch `feat/dashboard-modernize` — Next.js 15 patient dashboard port, 151 unit tests across 16 files, defense doc at `PATIENT_DASHBOARD_MIGRATION.md`. See `state.key_results[]` in `.night-shift/runs/2026-05-09-0213/state.json` for the authoritative KR list. Phases: scaffold → OAuth+FHIR proxy → six clinical cards → Co-Pilot rail → CI workflow → panel-scope authorization → physician_user_id threading + logout CSRF hardening → Dockerfile + CSP/security headers → multiple Codex-review-driven accuracy passes on the defense doc + memory bank. Codex hit usage limit at ~03:50 PT during Task 4.4 round 6 then returned around 7:15 AM PT and rejected end-consensus multiple times, each time proposing concrete counter-KRs (fact-correction; stale-numbers; fact-resilient phrasing; README cleanup; memory-bank refresh; KR-table refresh) all of which were executed. All `code-review.txt` files for self-reviewed tasks are headered `CODEX UNAVAILABLE — SELF-REVIEW` at `.night-shift/runs/2026-05-09-0213/key-results/*/tasks/*/code-review.txt`. **What this update missed (closed by today's refresh): master-side cost/latency report, master-side dashboard↔finder integration, and the bug-fix sweep between `35b7d1d7f` and `30cd84d87`.** See `progress.md` Phase 5 + Phase 6 sections + W2 Surprise Challenge section for the post-port story.

**Prior update (2026-05-08 late evening):** modal viewer UX shipped at `196d75e61` — iframe rail widens to 80vw when bbox modal opens via postMessage to parent OpenEMR, plus zoom toolbar (−/+/Fit width/Fit page) in the modal header with auto-fit-width default; pure UX change, **191 tests pass**, eval-fast 15/15 100%, no regression). Also: panel-gate fix at `37331e54b` letting clinicians-in-scope see pending intakes when `Patient.generalPractitioner` is empty (Synthea/Railway reality), 191 tests / +2 panel-scope tests. Earlier today: confirm/reject UX + OpenEMR REST write-back + persistence fix shipped at `c2534e416` on master and feat/w2-early-submission, pushed to both GitHub + GitLab and Railway-deployed; 189 tests pass (was 182), eval-fast 15/15 100% across all 6 categories, no regression. On 2026-05-07: front-desk deferred-extraction upload path shipped at `5e63e5fb9` — closed the deferred Documents-tab UI item without fighting the upstream `openemr/openemr:latest` Zend module; pushed to both GitHub + GitLab; `COPILOT_FRONT_DESK_USERS=Reception Desk` set on Railway copilot service; 182 tests pass (was 174). Earlier on 2026-05-07: W2 Early-Submission scope + smoke-test polish sweep + working regression-repro shipped on `feat/w2-early-submission`, head `f19f43514`, 44 task-commits since `78d0672c7`. The base branch (through `2cb643af9`) covers Tier 1 + Tier 2-lite + 4 polish KRs from the autonomous night-shift run `2026-05-06-0104` (KR1 LangGraph state machine, KR2 50-case eval gate + pre-push hook, KR3 TurnTrace 6-field extension + Langfuse generation spans, KR4 Reranker scaffolding, KR5 pending-intake notification + ACL grant, KR6 memory-bank refresh, KR7 regression-gate meta-tests, KR8 `vlm_cost_estimate_usd` populator, KR9 README review summary), then 8 rounds of `codex review --base 56c467c70` closing 18 findings (3 P1 + 15 P2). On 2026-05-07 the live smoke test surfaced three more defects, all fixed in a four-phase polish sweep (A: informational-vs-applied + single-value prompt rules + 2 fixture cases; B: VLM bbox tighten + PDF text-snap via `&q=` URL fragment; C: per-type evidence cards for non-DocumentReference citations; D: server-side OCR-snap for image bboxes via Tesseract). Then a fifth fix (`f19f43514`) made the documented regression-repro actually fire — the runner is fixture-driven and never invoked `apply_rules`, so the README's "comment out a rule" recipe was silently green. Added `evals/scorers/rules_block_regression.py` + `evals/cases/cross/cross_layer2_regression_canary.yaml` to make a Layer-2 disable trip the gate. **174 tests pass, 53/53 eval cases at 100%, ruff clean.** Branch is in shippable shape.

---

## Current focus — Week 2 Multimodal Evidence Agent (historical context follows; current state is in the top section)

Spec read and digested: `~/Desktop/Gauntlet/Week2/Week 2 - AgentForge Clinical Co-Pilot.pdf`. Full breakdown in `assignments/week2.md`. **Key headline:** the W2 hard gate is an **eval-driven CI gate that graders will trip with a regression** — if the gate doesn't fail, the whole build doesn't pass. Everything else in W2 (vision, multi-agent, RAG) is additive; the eval gate is the deliverable that must be airtight.

> The narrative below describes the state at `f19f43514` / `c2534e416` (pre-master-merge). All of this work is now on `master`; the dashboard port + master integration + cost/latency report shipped on top per the top section. Kept verbatim as a historical record.

**Historical active branch (now merged):** `feat/w2-early-submission` (HEAD `f19f43514`). Fork point `56c467c70` (cleaned-up master tip after pre-night-shift commits). 44 commits ahead of `78d0672c7` (W2 MVP master tip).

**Confirm/Reject UX + OpenEMR write-back + persistence fix at `c2534e416` (2026-05-08 late evening):**
- **Persistence:** `processed_documents` SQLite moved from ephemeral `/srv/copilot_docs.db` to volume-mounted `/data/copilot_docs.db` (Dockerfile ENV + Railway env var). Closes the dedup-doesn't-fire bug — repeat uploads of the same file now hit `was_dedup_hit=true` across redeploys.
- **Schema (idempotent ALTER ADD COLUMN):** `confirmed_at TEXT`, `rejected_at TEXT`, `confirmed_by TEXT`, `external_doc_id TEXT` (OpenEMR-side documents.id when the REST write-back succeeded). `_SELECT_COLUMNS` constant + `_row_to_doc` helper centralize the row-inflation so future column adds touch only three places.
- **New endpoints:** `POST /v1/documents/{doc_id}/confirm` and `POST /v1/documents/{doc_id}/reject`. Both panel-gated, both idempotent. Confirm orchestrates an OpenEMR write-back via `FhirClient.post_document_via_rest_api(...)` (POST `/apis/default/api/patient/{puuid}/document` — non-FHIR REST API alongside FHIR's read-only path), captures the external doc id, then marks `confirmed_at` locally. Fail-soft: REST error → still stamps `confirmed_at`, returns `openemr_write_error` in the response.
- **Store methods:** `mark_confirmed` / `mark_rejected` (COALESCE-based for idempotency), `list_confirmed_recent(patient_pseudonym, since)`.
- **Frontend (iframe):** modal footer with `[Confirm & save to chart]` and `[Reject]` buttons (hidden until a pending intake gets extracted via the banner click); banner item state colors (orange `[needs review]`, green `[confirmed]`, grey `[rejected]`).
- **Agent "what changed?" tool path:** `get_recent_uploads` gains `confirmed_only: bool` + `since_days: int` args; prompt addition guides the agent to call it with `confirmed_only=true` for "what's new / what changed / since last visit" questions, then cross-reference confirmed extractions with prior FHIR data.
- **Tests:** 189 passing (was 182 — 7 new). `evals/ingestion/test_confirm_reject.py` (6 tests across happy + fail-soft + idempotent + panel-gate paths). `evals/tools/test_document_tool.py` extended (+1 test verifying `confirmed_only=true` excludes pending and rejected rows).

**Front-desk deferred-extraction additions at `5e63e5fb9` (2026-05-07 late evening):**
- `app/config.py` — new `copilot_front_desk_users` setting; env var `COPILOT_FRONT_DESK_USERS` (comma-separated usernames). Empty string preserves legacy single-role behavior.
- `app/ingestion/service.py` — `IngestionService.attach_only` (front-desk skip-VLM path: hash dedup, store raw bytes with `_pending` marker, no OCR/derived-FHIR writes) and `process_pending` (idempotent on-demand extraction triggered by physician click). `IngestionResult.extraction` now `Optional` with `is_pending: bool` flag.
- `app/persistence/processed_documents.py` — `list_pending_uploads(patient_pseudonym, since)` for banner merge; `replace_extraction(...)` for promoting `_pending` rows to extracted in-place.
- `app/main.py` — `/v1/documents/attach` auto-detects front-desk users (env-driven), bypasses panel gate, routes to `attach_only`. `/v1/sessions/{sid}/pending_intakes` now merges local `_pending` rows alongside FHIR DocumentReferences (last 7 days). New `POST /v1/documents/{doc_id}/process` endpoint — panel-gated, idempotent, runs VLM on demand and updates the row.
- `app/web/copilot_iframe.js` — pending-aware system message (`"Filed pending intake for X — physician will review."` instead of `"Extracted N facts"` when `is_pending=true`); banner click handler POSTs `/process` before opening the bbox modal for `is_pending` items.
- `evals/ingestion/test_attach_defer.py` (NEW, 6 tests) and `evals/agent/test_pending_intakes.py` (extended +2 tests for the local-pending merge).

**Why this approach over fixing the OpenEMR Documents-tab UI:** the upstream `openemr/openemr:latest` image's Zend Documents module renders an empty Uploader/Viewer section because the supporting CSS (`<theme>/documents.css` defining `doc-doc-ls-*` classes) and Angular controllers (`documents.js`, `documentsController.js`) are absent from our fork — verified via `grep -rln 'doc-doc-ls' interface/` returning only the `.phtml` templates. Sourcing those files from an older upstream tag was brittle; routing the front-desk arc through the iframe drop-zone with a defer flag is fully under our control and exercises a smaller, well-tested code path.

**New modules + schema additions on this branch (post-MVP polish):**
- `app/agent/evidence.py` — `extract_evidence_records()` filters tool_results down to claim-cited records and tags each by FHIR family (8 kinds + `unknown`).
- `app/agent/schemas.py` — `EvidenceKind` literal, `EvidenceRecord` model, `AgentResponse.evidence_records` map.
- `app/ingestion/ocr.py` — `ocr_items()` + `snap_bbox()` for Tesseract-based bbox snap on image extractions.
- `app/ingestion/service.py` — `_ocr_snap_extraction()` runs after VLM extraction for `image/*` mime types; mutates `source_citation.bbox` in place.
- `app/ingestion/schemas.py::encode_record_id_for_vlm` — gained optional `raw_text` kwarg appending `&q={url-encoded}` to the record_id fragment.
- `app/web/copilot_iframe.js` — modal router + per-type renderers (Observation / Medication / Allergy / Condition / Encounter / Patient / Guideline / QuestionnaireResponse / Unknown), `_snapBboxToText()` PDF text-layer helper, `evidenceCache` per-turn map.
- `app/web/copilot_iframe.{html,css}` — `<div id="bbox-modal-card">` toggle + ~10 LOC styling.
- `evals/scorers/rules_block_regression.py` + `evals/cases/cross/cross_layer2_regression_canary.yaml` — make the documented regression-repro actually fire.
- Dockerfile gains `tesseract-ocr` apt + `pytesseract>=0.3.10` + `Pillow>=10.0` deps.

---

## Where we are in the W2 sprint

| Stage | State | Notes |
|---|---|---|
| Architecture Defense (4h) | ✅ done | `W2_ARCHITECTURE.md` is design-of-record at `f5b385f97`. **Appendix C** added at `160820b57` documenting the deployed-MVP delta vs the §1–§10 design. Defense .pptx in `~/Desktop/Gauntlet/Week2/AgentForge_W2_Architecture_Defense.pptx`. |
| MVP (Tue 2026-05-05) | ✅ shipped, demoable end-to-end | All 14 tasks of `W2_IMPLEMENTATION.md` landed. Deployed at `https://copilot-production-b532.up.railway.app`. 75 tests passing (W1: 42 + W2 MVP: 33). Master tip `78d0672c7`. |
| Early Submission (Thu ≈ 2026-05-07) | ✅ shipped + polished + canary-verified | Tier 1 + Tier 2-lite + 4 polish KRs landed via night-shift `2026-05-06-0104`; 8 codex rounds closed 18 findings; 4-phase smoke-test polish sweep on 2026-05-07. Working regression-repro shipped at `f19f43514`. 174 tests / 53/53 eval cases. |
| Front-desk arc + UX (2026-05-07 → 2026-05-08) | ✅ shipped on master | Phase 4 in `W2_IMPLEMENTATION.md`. Deferred-extraction upload path (`5e63e5fb9`); confirm/reject UX + persistence fix (`c2534e416`); panel-gate empty-GP fall-through (`37331e54b`); modal viewer rail-expand + zoom (`196d75e61`); env panel demoted to advisory (`35b7d1d7f`). 192 tests. |
| Surprise Challenge (dashboard port) | ✅ shipped on `feat/dashboard-modernize` | 151 unit tests, defense doc at `PATIENT_DASHBOARD_MIGRATION.md`. Branch tip `2cedf50d6`. Not yet pushed/merged to master. |
| Surprise Challenge integration (master) | ✅ shipped 2026-05-09 | `dashboard.php` launcher + finder re-point + EHR-launch silent SSO + iframe-embed CSP. Pattern B14 in `systemPatterns.md`. Master tip `30cd84d87`. |
| Final (Sun ≈ 2026-05-10 noon) | 🟡 partial | ✅ Cost & Latency Report (`30cd84d87`); 📋 demo video; 📋 real `POST /fhir/DocumentReference` (R4 has no route — Plan B blocked on `api:oemr` scope); 📋 dense retrieval (BM25 + identity-rerank shipped). |

---

## Immediate next steps

Phases 1–5 of W2 are shipped on master (`30cd84d87`). The dashboard port is shipped on `feat/dashboard-modernize` and integrated into OpenEMR's finder via master commits (Phase 5). W2 Final is partial — Cost & Latency Report shipped; demo video, dense retrieval, and real FHIR DocumentReference write remain.

**Remaining work (in priority order, Sun 2026-05-10 noon CT deadline):**

1. **Demo video (3-5 min)** — required by PRD. User owns capture. The demo path that holds up: drop a lab PDF → "Extracted N facts" → ask "What was the LDL?" → grounded answer with bbox-modal citation → ask "what's new for this patient?" → agent uses `get_recent_uploads(confirmed_only=true)` → finder click in OpenEMR launches Modern dashboard with Co-Pilot rail.
2. **Push `feat/dashboard-modernize` to master** if user is ready for the dashboard prod URL to flip. The 52 sync/fix commits over master are mostly cookie/CSP fixes for prod iframe-embed; safe to merge.
3. **Decide Plan B REST scope fix path** — option A (add `api:oemr` to OAuth scopes; ~30 min, real risk if OAuth client isn't configured for it), option B (demote Confirm to local-only; ~10 min, zero risk), option C (cosmetic-only message reword; ~5 min). User has not chosen. Default to C for the demo, with A as follow-on if grading time allows.
4. **Optional: dense retrieval** — embedding store + dense scoring on top of the existing `Reranker` Protocol scaffolding. Defensible as-shipped but a literal PRD reading is unmet.

**Run pointers (historical):**
- Night-shift `2026-05-06-0104` (Early Submission) — `.night-shift/runs/2026-05-06-0104/`
- Night-shift `2026-05-09-0213` (Surprise Challenge) — `.night-shift/runs/2026-05-09-0213/`
- 8 rounds of codex review on Early Submission scope — `.night-shift/runs/2026-05-06-0104/external-reviews/`

---

## Operating constraints (W2-specific)

- **No raw PHI in observability** (PRD Core req #7). The 5-case `no_phi_in_logs` rubric will catch any leak — must stay at 1.0.
- **Boolean rubrics only.** No 1–10 ratings, no LLM-as-judge without a clear rubric (PRD pitfall #4).
- **Two doc types reliably > five poorly** (PRD pitfall #1). Resist the urge to add referral-fax / med-list as Core scope.
- **Supervisor is plain Python**, not an LLM (PRD pitfall #3 mitigation; `W2_ARCHITECTURE.md §4.1`). Routing decisions must be inspectable.
- **Vision goes through Anthropic.** Resolved 2026-05-05: code side via `f2d6bc972` (`get_adapter` ignores drifted `LLM_PROVIDER=openai` whenever the Anthropic key is set), Railway env flipped to `anthropic` by user same day. OpenAI key stays set as the per-turn `FallbackAdapter` safety net. Watch in Langfuse that `model=claude-...` shows up in production traces; if not, recheck `ANTHROPIC_API_KEY` on the `copilot` service.

---

## W1 → W2 carry-forwards (must not regress)

- Citation contract — `Claim.record_id` must come from a tool call this turn
- 42 W1 eval cases stay green
- PHI minimizer applied to extracted facts before logging
- `_verify_patient_in_panel` reused on every new endpoint
- FHIR-only data path; never legacy `interface/`, never direct SQL

Full details in `assignments/week1.md §6` and `progress.md` known-issues table.

---

## Branch + remote state

- `master` — W2 Phases 1–5 + Final partial (`30cd84d87`). 83 commits ahead of W2 MVP `78d0672c7`. Pushed to both GitHub `rikkiiwang/openemr` and GitLab `labs.gauntletai.com/ruijingwang/openemr` (presumed current; verify with `git status` if uncertain).
- `feat/dashboard-modernize` — Surprise Challenge port + master sync commits (`2cedf50d6`). 115 ahead of `78d0672c7`, 52 ahead of master. Includes prod-only frontend fixes (cookie `SameSite=None`/`Secure`, CSP `frame-ancestors` hardcoded fallback). **Not yet pushed/merged** — user owns this.
- `feat/w2-early-submission` — historical; merged into master. Tip was `35b7d1d7f` at last documented checkpoint.
- `w2-mvp` — fast-forward merged into master long ago and the tag was deleted.
- Remotes: `origin` (multi-push: both GitHub + GitLab). To push to one only, use the full URL: `git push https://labs.gauntletai.com/ruijingwang/openemr.git master`. Railway's source-connection is GitHub `rikkiiwang/openemr` master, so a GitLab-only push does NOT trigger a redeploy. **Dashboard service auto-deploys from GitHub `rikkiiwang/openemr` `feat/dashboard-modernize`** (or whichever branch/path the new Railway dashboard service is wired to — verify in Railway UI).

---

## W1 carry-over debts that may collide with W2 work

If any of these block W2, fix in-line; otherwise leave to a post-W2 cleanup pass.

- Langfuse SDK v3 migration (currently pinned `<3`)
- `phi/minimizer.py` defensive iteration sweep (`reaction[0].manifestation`, `referenceRange[0]`, `category[0].coding[0]`)
- ~~`LLM_PROVIDER=anthropic` flip on Railway~~ ✅ resolved 2026-05-05 — code (`f2d6bc972`) + Railway env both done
- Renal-dose + QTc Layer-2 rules (still deferred)
- `get_recent_labs` / `get_recent_vitals` SCHEMA `description` "last 90 days" drift (cosmetic)
- ~~Sensitive OpenEMR runtime artifacts (`oa*.key`, Synthea patient UUID dirs) untracked but at risk~~ ✅ resolved 2026-05-05 — `.gitignore` patterns added in `271363584`; local Synthea blob deleted
