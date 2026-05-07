# Active Context

**Last updated:** 2026-05-07 (W2 Early-Submission scope + smoke-test polish sweep + working regression-repro shipped on `feat/w2-early-submission`, head `f19f43514`, 44 task-commits since `78d0672c7`). The base branch (through `2cb643af9`) covers Tier 1 + Tier 2-lite + 4 polish KRs from the autonomous night-shift run `2026-05-06-0104` (KR1 LangGraph state machine, KR2 50-case eval gate + pre-push hook, KR3 TurnTrace 6-field extension + Langfuse generation spans, KR4 Reranker scaffolding, KR5 pending-intake notification + ACL grant, KR6 memory-bank refresh, KR7 regression-gate meta-tests, KR8 `vlm_cost_estimate_usd` populator, KR9 README review summary), then 8 rounds of `codex review --base 56c467c70` closing 18 findings (3 P1 + 15 P2). On 2026-05-07 the live smoke test surfaced three more defects, all fixed in a four-phase polish sweep (A: informational-vs-applied + single-value prompt rules + 2 fixture cases; B: VLM bbox tighten + PDF text-snap via `&q=` URL fragment; C: per-type evidence cards for non-DocumentReference citations; D: server-side OCR-snap for image bboxes via Tesseract). Then a fifth fix (`f19f43514`) made the documented regression-repro actually fire — the runner is fixture-driven and never invoked `apply_rules`, so the README's "comment out a rule" recipe was silently green. Added `evals/scorers/rules_block_regression.py` + `evals/cases/cross/cross_layer2_regression_canary.yaml` to make a Layer-2 disable trip the gate. **174 tests pass, 53/53 eval cases at 100%, ruff clean.** Branch is in shippable shape.

---

## Current focus — Week 2 Multimodal Evidence Agent

Spec read and digested: `~/Desktop/Gauntlet/Week2/Week 2 - AgentForge Clinical Co-Pilot.pdf`. Full breakdown in `assignments/week2.md`. **Key headline:** the W2 hard gate is an **eval-driven CI gate that graders will trip with a regression** — if the gate doesn't fail, the whole build doesn't pass. Everything else in W2 (vision, multi-agent, RAG) is additive; the eval gate is the deliverable that must be airtight.

**Active branch:** `feat/w2-early-submission` (HEAD `f19f43514`). Fork point `56c467c70` (cleaned-up master tip after pre-night-shift commits). 44 commits ahead of `78d0672c7` (W2 MVP master tip). NOT yet pushed to GitHub (Railway auto-deploys from GitHub master, so the deployed Co-Pilot is still the W2-MVP `78d0672c7`).

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
