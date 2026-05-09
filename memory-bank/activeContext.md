# Active Context

**Last updated:** 2026-05-09 (autonomous night-shift run `2026-05-09-0213` shipped the **W2 surprise challenge** — Next.js 15 patient dashboard port — onto branch `feat/dashboard-modernize`, 151 unit tests across 16 files, defense doc at `PATIENT_DASHBOARD_MIGRATION.md`. For exact commit count run `git rev-list --count master..feat/dashboard-modernize`. See `state.key_results[]` in `.night-shift/runs/2026-05-09-0213/state.json` for the authoritative KR list. Phases: scaffold → OAuth+FHIR proxy → six clinical cards → Co-Pilot rail → CI workflow → panel-scope authorization → physician_user_id threading + logout CSRF hardening → Dockerfile + CSP/security headers → multiple Codex-review-driven accuracy passes on the defense doc + memory bank. Codex hit usage limit at ~03:50 PT during Task 4.4 round 6 then returned around 7:15 AM PT and rejected end-consensus multiple times, each time proposing concrete counter-KRs (fact-correction; stale-numbers; fact-resilient phrasing; README cleanup; memory-bank refresh; KR-table refresh) all of which were executed. All `code-review.txt` files for self-reviewed tasks are headered `CODEX UNAVAILABLE — SELF-REVIEW` at `.night-shift/runs/2026-05-09-0213/key-results/*/tasks/*/code-review.txt`. Branch not yet pushed/merged; user will. See `progress.md` "W2 Surprise Challenge" section for the full KR table. Below this line is the prior W2 Early-Submission state.)

**Prior update (2026-05-08 late evening):** modal viewer UX shipped at `196d75e61` — iframe rail widens to 80vw when bbox modal opens via postMessage to parent OpenEMR, plus zoom toolbar (−/+/Fit width/Fit page) in the modal header with auto-fit-width default; pure UX change, **191 tests pass**, eval-fast 15/15 100%, no regression). Also: panel-gate fix at `37331e54b` letting clinicians-in-scope see pending intakes when `Patient.generalPractitioner` is empty (Synthea/Railway reality), 191 tests / +2 panel-scope tests. Earlier today: confirm/reject UX + OpenEMR REST write-back + persistence fix shipped at `c2534e416` on master and feat/w2-early-submission, pushed to both GitHub + GitLab and Railway-deployed; 189 tests pass (was 182), eval-fast 15/15 100% across all 6 categories, no regression. On 2026-05-07: front-desk deferred-extraction upload path shipped at `5e63e5fb9` — closed the deferred Documents-tab UI item without fighting the upstream `openemr/openemr:latest` Zend module; pushed to both GitHub + GitLab; `COPILOT_FRONT_DESK_USERS=Reception Desk` set on Railway copilot service; 182 tests pass (was 174). Earlier on 2026-05-07: W2 Early-Submission scope + smoke-test polish sweep + working regression-repro shipped on `feat/w2-early-submission`, head `f19f43514`, 44 task-commits since `78d0672c7`. The base branch (through `2cb643af9`) covers Tier 1 + Tier 2-lite + 4 polish KRs from the autonomous night-shift run `2026-05-06-0104` (KR1 LangGraph state machine, KR2 50-case eval gate + pre-push hook, KR3 TurnTrace 6-field extension + Langfuse generation spans, KR4 Reranker scaffolding, KR5 pending-intake notification + ACL grant, KR6 memory-bank refresh, KR7 regression-gate meta-tests, KR8 `vlm_cost_estimate_usd` populator, KR9 README review summary), then 8 rounds of `codex review --base 56c467c70` closing 18 findings (3 P1 + 15 P2). On 2026-05-07 the live smoke test surfaced three more defects, all fixed in a four-phase polish sweep (A: informational-vs-applied + single-value prompt rules + 2 fixture cases; B: VLM bbox tighten + PDF text-snap via `&q=` URL fragment; C: per-type evidence cards for non-DocumentReference citations; D: server-side OCR-snap for image bboxes via Tesseract). Then a fifth fix (`f19f43514`) made the documented regression-repro actually fire — the runner is fixture-driven and never invoked `apply_rules`, so the README's "comment out a rule" recipe was silently green. Added `evals/scorers/rules_block_regression.py` + `evals/cases/cross/cross_layer2_regression_canary.yaml` to make a Layer-2 disable trip the gate. **174 tests pass, 53/53 eval cases at 100%, ruff clean.** Branch is in shippable shape.

---

## Current focus — Week 2 Multimodal Evidence Agent

Spec read and digested: `~/Desktop/Gauntlet/Week2/Week 2 - AgentForge Clinical Co-Pilot.pdf`. Full breakdown in `assignments/week2.md`. **Key headline:** the W2 hard gate is an **eval-driven CI gate that graders will trip with a regression** — if the gate doesn't fail, the whole build doesn't pass. Everything else in W2 (vision, multi-agent, RAG) is additive; the eval gate is the deliverable that must be airtight.

**Active branch:** `feat/w2-early-submission` (HEAD `f19f43514`). Fork point `56c467c70` (cleaned-up master tip after pre-night-shift commits). 44 commits ahead of `78d0672c7` (W2 MVP master tip). NOT yet pushed to GitHub (Railway auto-deploys from GitHub master, so the deployed Co-Pilot is still the W2-MVP `78d0672c7`).

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
| MVP (Tue 2026-05-05) | ✅ shipped, demoable end-to-end | All 14 tasks of `W2_IMPLEMENTATION.md` landed. Deployed at `https://copilot-production-b532.up.railway.app`. Demo path verified live: drop lab PDF → "Extracted N fact(s)" → ask "What was the LDL?" → grounded answer with bbox-modal-clickable citations → ask "What guideline applies?" → guideline citation. 75 tests passing (W1: 42 + W2 MVP: 33). README rewritten for W2 in `78d0672c7`; `IMPLEMENTATION.md` renamed to `W1_IMPLEMENTATION.md`. |
| Early Submission (Thu ≈ 2026-05-07) | ✅ shipped + polished + canary-verified | Tier 1 + Tier 2-lite + 4 polish KRs landed via night-shift `2026-05-06-0104`; 8 codex rounds closed 18 findings; 4-phase smoke-test polish sweep on 2026-05-07 fixed informational-vs-applied + single-value scoping, PDF text-snap, evidence cards, OCR-snap. Working regression-repro shipped at `f19f43514` — `make eval-fast` exits non-zero when a Layer-2 rule is commented. 174 tests / 53/53 eval cases. |
| Final (Sun ≈ 2026-05-10 noon) | 📋 not started | Real FHIR writes (replacing stubs from `971affe8d`), cost/latency report, demo video, source-grounded UI polish. |

---

## Immediate next steps

W2 Early-Submission scope is **shipped + codex-hardened + smoke-tested + canary-verified** on `feat/w2-early-submission` (head `f19f43514`). 9 KRs landed via the night-shift; 8 codex rounds closed 18 findings; 4-phase polish sweep + canary fix completed 2026-05-07. The branch is shippable — the remaining work is push + open PR + grading submission.

**Pre-push validation already complete on 2026-05-07:**
- ✅ Smoke-test PDF + PNG paths against live local OpenEMR (UUID `a1a5a6d3-3edd-4341-9281-017568b3c36e`, physician `admin`). PDF text-snap hugs the value digits; PNG OCR-snap hugs glyphs after Tesseract pass; non-DocumentReference citations render formatted cards (Observation / Medication / Guideline / etc.).
- ✅ Regression-repro recipe verified end-to-end: rule active → `make eval-fast` 15/15 exit 0; rule commented → 14/15 with `cross 66.7%` and `make: *** [eval-fast] Error 1` (exit 2).
- ✅ Local OpenEMR `sqlconf.php` fix (host=mysql, config=1) — note: container-only mutation; recreated containers will revert it. Not on disk in the repo.

**Remaining steps:**

1. **Optional**: re-trigger `/security-review` over the new attack surface (FHIR preview fallback, ACL grant, banner JS, OCR pass on uploaded bytes, evidence_records map shape).
2. **Optional**: install the pre-push hook locally with `bash copilot/scripts/install-hooks.sh`.
3. **Push + open PR** — `git push origin feat/w2-early-submission`. Railway auto-deploys from GitHub master, so prod stays at `78d0672c7` until the PR merges.
4. **Submit for cohort grading.**

**Codex review trail:** every finding-and-fix cycle is captured in `.night-shift/runs/2026-05-06-0104/external-reviews/{triage.md, codex-full-diff.txt, codex-full-diff-round2..9.txt}`. Round 9 hit the daily quota cap; reset is ~4:23 PM local. Marginal value of further rounds is low — rounds 7-8 were down to UX edge cases.

**Final-deferred (next sprint, `W2_FINAL_IMPLEMENTATION.md`) — untouched:**

- Real `POST /fhir/DocumentReference` (replace `971affe8d` stub) + round-trip eval test (upload lab → re-fetch via `get_recent_labs` → correct `derivedFrom`).
- Full `_verify_patient_in_facility` Python helper + facility-aware variants of `copilot-finder-scope.php` + `copilot-demographics-gate.php`.
- `scripts/seed_w2_dataset.py` — Synthea bulk import to 18-20 patients across 2 facilities + 2 front-desk users.
- `processed_documents.acknowledged_by_physician_at` column + persistent banner-dismiss tracking across sessions.
- Dense retrieval (OpenAI embeddings + numpy cosine over BLOB).
- Cost & latency report (extend `copilot/COST.md`, p50/p95 + bottleneck analysis); 3-5 min demo video; source-grounded UI polish.

**Run pointers:**
- Night-shift state + handoff + per-KR/per-task review artifacts: `.night-shift/runs/2026-05-06-0104/`
- Codex review outputs (8 rounds + triage): `.night-shift/runs/2026-05-06-0104/external-reviews/`
- Per-task `code-review.txt` files all marked `CODEX UNAVAILABLE — SELF-REVIEW` (the night-shift's per-task gate ran without the codex CLI installed). The post-shift 8-round codex pass was the real adversarial eyes.

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

- `master` — W2 MVP shipped state (`78d0672c7`)
  - GitLab `labs.gauntletai.com/ruijingwang/openemr` — at `78d0672c7` (current — W2 MVP including README rewrite + W1_IMPLEMENTATION.md rename)
  - GitHub `rikkiiwang/openemr` — at `160820b57` (one commit behind — Architecture Appendix C). User intentionally pushed only to GitLab in the last cycle.
- `w2-mvp` branch — fast-forward merged into `master` and the `w2-mvp` tag was deleted.
- Remotes: `origin` (multi-push: both GitHub + GitLab). To push to one only, use the full URL: `git push https://labs.gauntletai.com/ruijingwang/openemr.git master`. Railway's source-connection is GitHub `rikkiiwang/openemr` master, so a GitLab-only push does NOT trigger a redeploy.

---

## W1 carry-over debts that may collide with W2 work

If any of these block W2, fix in-line; otherwise leave to a post-W2 cleanup pass.

- Langfuse SDK v3 migration (currently pinned `<3`)
- `phi/minimizer.py` defensive iteration sweep (`reaction[0].manifestation`, `referenceRange[0]`, `category[0].coding[0]`)
- ~~`LLM_PROVIDER=anthropic` flip on Railway~~ ✅ resolved 2026-05-05 — code (`f2d6bc972`) + Railway env both done
- Renal-dose + QTc Layer-2 rules (still deferred)
- `get_recent_labs` / `get_recent_vitals` SCHEMA `description` "last 90 days" drift (cosmetic)
- ~~Sensitive OpenEMR runtime artifacts (`oa*.key`, Synthea patient UUID dirs) untracked but at risk~~ ✅ resolved 2026-05-05 — `.gitignore` patterns added in `271363584`; local Synthea blob deleted
