# Active Context

**Last updated:** 2026-05-06 (Week 2 MVP shipped to Railway 2026-05-05; `master` at `78d0672c7`). PDF.js bbox-overlay fix applied to `app/web/copilot_iframe.{html,js}` after `78d0672c7` — uncommitted. Architecture extended 2026-05-06 to make Front Desk role first-class with **facility scope** + **pending-intake banner notification** + **EHR dataset expansion plan** (new §2.6); `W2_EARLY_IMPLEMENTATION.md` authored with the 13-task Tier-1/Tier-2 plan.

---

## Current focus — Week 2 Multimodal Evidence Agent

Spec read and digested: `~/Desktop/Gauntlet/Week2/Week 2 - AgentForge Clinical Co-Pilot.pdf`. Full breakdown in `assignments/week2.md`. **Key headline:** the W2 hard gate is an **eval-driven CI gate that graders will trip with a regression** — if the gate doesn't fail, the whole build doesn't pass. Everything else in W2 (vision, multi-agent, RAG) is additive; the eval gate is the deliverable that must be airtight.

**Active branch:** `master` (HEAD `78d0672c7`). The `w2-mvp` working branch was fast-forward merged; the `w2-mvp` tag was deleted. Code is real and deployed to Railway.

---

## Where we are in the W2 sprint

| Stage | State | Notes |
|---|---|---|
| Architecture Defense (4h) | ✅ done | `W2_ARCHITECTURE.md` is design-of-record at `f5b385f97`. **Appendix C** added at `160820b57` documenting the deployed-MVP delta vs the §1–§10 design. Defense .pptx in `~/Desktop/Gauntlet/Week2/AgentForge_W2_Architecture_Defense.pptx`. |
| MVP (Tue 2026-05-05) | ✅ shipped, demoable end-to-end | All 14 tasks of `W2_IMPLEMENTATION.md` landed. Deployed at `https://copilot-production-b532.up.railway.app`. Demo path verified live: drop lab PDF → "Extracted N fact(s)" → ask "What was the LDL?" → grounded answer with bbox-modal-clickable citations → ask "What guideline applies?" → guideline citation. 75 tests passing (W1: 42 + W2 MVP: 33). README rewritten for W2 in `78d0672c7`; `IMPLEMENTATION.md` renamed to `W1_IMPLEMENTATION.md`. |
| Early Submission (Thu ≈ 2026-05-07) | ⏳ next | LangGraph supervisor + workers, critic node, 50-case eval, PR-blocking pre-push hook, full Cohere/dense rerank, TurnTrace 6-field extension, Langfuse per-LLM-call generation spans — none yet on branch. Plan file `W2_EARLY_IMPLEMENTATION.md` not yet authored. |
| Final (Sun ≈ 2026-05-10 noon) | 📋 not started | Real FHIR writes (replacing stubs from `971affe8d`), cost/latency report, demo video, source-grounded UI polish. |

---

## Immediate next steps

MVP shipped; pivot to Early Submission. Plan now lives in `copilot/W2_EARLY_IMPLEMENTATION.md` (authored 2026-05-06). Tier-1 (eval gate critical, Thursday) + Tier-2 (front-desk lite, optional) + Final-deferred. The 13 tasks in priority order:

**Tier 1 — eval gate critical (Thursday 2026-05-07):**
1. Lock scope; commit the uncommitted bbox-overlay PDF/PNG fix.
2. LangGraph skeleton — terminal-node refactor, **no behavior change** checkpoint (all 75 tests stay green).
3. Worker nodes + plain-Python supervisor (`intake_extractor`, `evidence_retriever`, `answer_composer`, `decide_next`).
4. Critic node + extended Layer-2 rules (`check_extracted_fact_has_source_doc`, `check_evidence_chunk_in_corpus`).
5. 50-case golden set under `evals/cases/*.yaml` (15 extraction / 10 retrieval / 10 citation / 5 refusal / 5 PHI / 5 cross).
6. Five boolean rubric scorers + `evals/RESULTS.md` writer + threshold logic (any category <0.95 OR >5pp drop → exit 1).
7. PR-blocking `pre-push` Git hook + `make eval-fast` <2 min + regression-repro recipe in README.
8. TurnTrace 6-field extension (`routing_path`, `extraction_confidence_min`, `retrieval_hit_ids`, `rerank_scores`, `vlm_cost_estimate_usd`, `documents_attached`).
9. Langfuse generation spans in `AnthropicAdapter.call` + `OpenAIAdapter.call` so model identity shows in trace UI.
10. Dense + Cohere rerank — only if Tier 1 is done by Thursday afternoon.

**Tier 2 — front-desk LITE (only if Tier 1 is solid by Thursday morning):**
11. ACL grant `patients|docs` write to `Front Office` group + 2 pre-staged intake fixtures uploaded as `front-rfm` user via OpenEMR Documents Zend module.
12. `GET /v1/sessions/{id}/pending_intakes` endpoint, panel-gated (re-uses `_verify_patient_in_panel` — no new gate yet).
13. Iframe banner stub fetching the endpoint + click-to-modal (re-uses bbox-overlay fix). In-memory dismiss only.

**Final-deferred (`W2_FINAL_IMPLEMENTATION.md`, Sunday 2026-05-10):**
- Real `POST /fhir/DocumentReference` (replace `971affe8d` stub) + round-trip eval.
- Full `_verify_patient_in_facility` Python helper + facility-aware variants of `copilot-finder-scope.php` + `copilot-demographics-gate.php`.
- `scripts/seed_w2_dataset.py` — Synthea bulk import to 18-20 patients across 2 facilities + 2 front-desk users.
- `processed_documents.acknowledged_by_physician_at` + persistent dismiss tracking.
- Cost & latency report; demo video; source-grounded UI polish.

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
