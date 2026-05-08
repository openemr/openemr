# Clinical Co-Pilot — Agent Service

[![copilot-ci](https://github.com/rikkiiwang/openemr/actions/workflows/copilot-ci.yml/badge.svg?branch=master)](https://github.com/rikkiiwang/openemr/actions/workflows/copilot-ci.yml)

> **What `feat/w2-early-submission` ships** (autonomous night-shift run
> `2026-05-06-0104`, 27+ task-commits since `78d0672c7`):
>
> - **LangGraph state machine** — supervisor + `intake_extractor` +
>   `evidence_retriever` workers + `answer_composer` (W1 loop wrapped) +
>   `critic` node, with deterministic plain-Python routing
>   (`app/graph/`).
> - **PRD-mandated 50-case eval gate** with five boolean rubric scorers
>   + threshold-based regression check + `make eval-fast` (<2 s, <2 min
>   target) + pre-push hook installer + CI extension. See "Verifying the
>   W2 eval gate" below for the regression-repro recipe.
> - **6 new W2 TurnTrace fields** + Langfuse `generation()` spans per
>   LLM call so model identity surfaces in the trace UI.
> - **Reranker scaffolding** (`app/retrieval/rerank.py`) — Identity /
>   Cohere / local-cross-encoder; default Identity in CI.
> - **Tier-2 LITE front-desk role** — `GET /v1/sessions/{id}/pending_intakes`
>   + iframe banner with expandable list + ACL grant for the stock
>   `Front Office` group (acl_upgrade.php v14). See "Front-desk demo
>   prep" below.
> - **148 tests passing**, 50/50 eval cases at 100% across all six
>   PRD-named categories. The branch is **green at every commit** by
>   design (per-task structural pre-commit gate).
>
> **What stays Final-deferred:** real `POST /fhir/DocumentReference`,
> round-trip eval test, full `_verify_patient_in_facility` helper,
> dataset expansion to 18-20 patients × 2 facilities, persistent
> banner-dismiss, dense retrieval (OpenAI embeddings), cost+latency
> report, demo video. See `copilot/W2_EARLY_IMPLEMENTATION.md` for the
> tier breakdown.

---

A separate Python (FastAPI) service that surfaces inside OpenEMR as a SMART on
FHIR app and gives a primary care physician patient context in the 60–90s
window between rooms.

This directory is intentionally a sibling of OpenEMR's PHP tree, not nested
inside it. The agent never touches OpenEMR's database directly — it talks to
the existing FHIR API as an OAuth2 client.

The primary UI is an **iframe rail embedded inside OpenEMR's patient
demographics page** (a 36px collapsed `Co-Pilot ▸` tab on the right edge of
every patient chart; click to slide a 400px panel in). The standalone URL at
`/` is kept for development and for the demo video.

See:
- [`W1_IMPLEMENTATION.md`](./W1_IMPLEMENTATION.md) — Week 1 status, the agent loop, citation contract, verification gate
- [`W2_ARCHITECTURE.md`](./W2_ARCHITECTURE.md) — Week 2 design-of-record from the architecture-defense gate (§1–§10) + Appendix C documenting the deployed-MVP delta
- [`W2_IMPLEMENTATION.md`](./W2_IMPLEMENTATION.md) — Week 2 MVP plan (14 tasks, all landed)
- [`../ARCHITECTURE.md`](../ARCHITECTURE.md) — Week 1 full design
- [`../USERS.md`](../USERS.md) — target user + use cases
- [`../AUDIT.md`](../AUDIT.md) — codebase audit findings the agent must mitigate

## Quick start (Docker, local)

```bash
cp .env.example .env
# Edit .env — at minimum:
#   OPENAI_API_KEY (or ANTHROPIC_API_KEY if LLM_PROVIDER=anthropic)
#   OAUTH_CLIENT_ID, OAUTH_CLIENT_SECRET
#   OPENEMR_FHIR_BASE, OPENEMR_OAUTH_BASE

docker compose up --build
# → http://localhost:8080/healthz
# → http://localhost:8080/         (standalone chat UI)
```

For a clean clone-from-GitLab-or-GitHub setup:

```bash
git clone https://labs.gauntletai.com/ruijingwang/openemr.git
# (or)  git clone git@github.com:rikkiiwang/openemr.git
cd openemr/copilot
cp .env.example .env
# fill in secrets — see below
docker compose up --build
```

## OpenEMR OAuth2 client registration

The agent runs as a confidential SMART client. Register it once against your
OpenEMR instance (the deployed instance is at
`https://openemr-production-0c8c.up.railway.app/`):

```bash
curl -X POST 'https://<openemr-host>/oauth2/default/registration' \
  -H 'Content-Type: application/json' \
  -d '{
    "application_type": "confidential",
    "client_name": "Clinical Co-Pilot",
    "scope": "openid offline_access api:fhir user/Patient.read user/Observation.read user/MedicationRequest.read user/Condition.read user/Encounter.read user/AllergyIntolerance.read"
  }'
```

Take the returned `client_id` / `client_secret` and the system administrator
must enable the client in **OpenEMR Admin → System → API Clients**, then mark
its scopes as approved. Put the credentials in `.env` as `<OAUTH_CLIENT_ID>` /
`<OAUTH_CLIENT_SECRET>`.

The OpenEMR admin password is referenced in `.env` as
`<OPENEMR_ADMIN_PASSWORD>`. **Never commit literal secrets** — `.env` is
gitignored.

## Iframe rail integration (primary UI)

The agent is embedded into OpenEMR's patient summary via a 54-line block at
the end of `interface/patient_file/summary/demographics.php`. It looks up the
patient's FHIR UUID from `patient_data.uuid`, builds the iframe src
`https://<copilot-host>/?patient_id=<uuid>`, and renders a collapsed 36px tab
on the right edge of the page. Clicking the tab toggles `body.copilot-open`
and slides in a 400px panel.

The chat UI (`app/web/index.html`) reads `?patient_id=<uuid>` from the URL
and auto-fires `startSession()` after 50ms, so the rail opens already-scoped
to the active patient without the physician typing the FHIR id.

To change which copilot host the iframe points at, edit
`$copilotAgentUrl` near line 2073 of `interface/patient_file/summary/demographics.php`.

## Deploy (CI/CD)

Every push to `master` that touches `copilot/**` runs the `copilot-ci`
GitHub Actions workflow:

1. **Test job** — `ruff check` + `pytest evals` against the Co-Pilot tree.
   Tests run with dummy API keys; `evals/conftest.py` skips
   `@pytest.mark.live_llm` cases unless `ANTHROPIC_LIVE=1`, so CI never
   makes real LLM calls.
2. **Deploy job** — on green, deploys to Railway via the Railway CLI using
   the `RAILWAY_TOKEN` repo secret. Railway's native GitHub auto-deploy
   should be **disabled** for the `copilot` service to avoid double-fire.

Manual `railway up` is no longer required. Rolling back is `git revert` +
push, which re-runs the same workflow with the previous commit.

Workflow file: [`.github/workflows/copilot-ci.yml`](../.github/workflows/copilot-ci.yml).

## Standalone surface (dev / demo)

The standalone chat UI is served at `/` on the agent host:
- Local dev: http://localhost:8080
- Railway: https://copilot-production-b532.up.railway.app

Paste a patient FHIR UUID, click "Open chart", ask a question. Useful for the
demo video and for hitting the agent without going through OpenEMR.

## Tests

The eval suite has **75 tests** (Week 1 baseline: 42; Week 2 MVP: +33),
3 skipped (live-LLM cases gated behind `ANTHROPIC_LIVE=1`):

```bash
make test       # PHI + tool integration tests only (no live LLM)
make eval       # full suite, mocked LLM — 75 passed, 3 skipped expected
make eval-live  # full suite, real LLM call (requires ANTHROPIC_API_KEY in env)
```

Test breakdown (post-Week 2):
- Week 1: PHI minimizer, tool integration, verification gate, agent
  scenarios, panel-scope, prewarm, persistence, resume — 42 cases.
- Week 2 ingestion (`evals/ingestion/`):
  - `test_vlm.py` — Claude vision adapter content-block dispatch.
  - `test_fhir_writer.py` — derived FHIR resource builders + write seam.
  - `test_extraction_service.py` — IngestionService dedup + writes.
  - `test_attach_route.py` — POST `/v1/documents/attach` end-to-end.
  - `test_document_views.py` — `/preview` + `/extractions` panel-gated.
  - `test_pipeline_smoke.py` — fixture PDF through the full pipeline with VLM mocked.
- Week 2 retrieval (`evals/retrieval/test_corpus.py`) — BM25 corpus index + queries.
- Week 2 tools (`evals/tools/test_document_tool.py`) — `attach_and_extract`
  + `get_recent_uploads` ToolResult shape, including Layer-2 cross-patient-leakage compatibility.
- Week 2 agent (`evals/agent/test_fhir_writes.py`, `test_iframe_routes.py`) — stub-write helpers + iframe shell HTML/JS/CSS.
- Week 2 persistence (`evals/persistence/test_lifespan_wiring.py`) — app.state singletons.

`make eval` writes `evals/RESULTS.md` with a summary of pass/fail counts.

## Layout

```
app/
  main.py              FastAPI entry: /healthz, /v1/sessions, /v1/chat,
                       /v1/documents/{attach,preview,extractions} (Week 2)
  config.py            Settings (env-driven; Anthropic + OpenAI adapters)
  fhir/                OAuth2 + FHIR HTTP client. Week 2 write helpers
                       are stubbed — see W2_ARCHITECTURE.md Appendix C.2
  tools/               11 tools — 8 Week 1 FHIR readers + Week 2:
                         attach_and_extract, search_guidelines, get_recent_uploads
  agent/               Orchestration loop, provider-agnostic LLM adapter
                       (FallbackAdapter wraps Anthropic + OpenAI), prompt,
                       schemas. Week 2 prompt teaches the new tools.
  verification/        Layer-1 attribution + Layer-2 domain rules
  phi/                 PHI minimizer + session pseudonym map + log_filter
  observability/       Langfuse trace wrapper + PHI-safe vlm_span helpers
  acl/                 GACL mirror — defense in depth
  ingestion/           Week 2: schemas (Pydantic), VLM adapter,
                       service orchestration, FHIR writer (stubbed)
  retrieval/           Week 2: SQLite + FTS5 BM25 corpus reader
  persistence/         conversation store + processed_documents (Week 2:
                       sha3-512 dedup + extracted file_bytes + mime_type)
  web/                 Iframe shell (HTML + CSS + JS) for the drop-zone
                       UI; bbox modal; chat input. Light-theme pinned
                       so dark-mode hosts render correctly.
corpus/                12-chunk hand-curated guideline corpus (USPSTF/ADA/AHA)
scripts/               generate_mvp_fixtures.py — deterministic synthetic
                       lab + intake PDFs for the pipeline smoke test
evals/                 pytest suite — 75 tests (W1: 42 + W2 MVP: 33)
  agent/  ingestion/  retrieval/  tools/  persistence/
```

## Status

- ✅ Phase A — skeleton, OAuth2, FHIR roundtrip
- ✅ Phase B — 8 tools, PHI minimizer, ACL mirror
- ✅ Phase C — agent loop, two-layer verification gate
- ✅ Phase D — Langfuse observability, eval suite
- ✅ Phase E — chat UI, Railway deploy, iframe rail in OpenEMR
- ✅ **Week 2 MVP** — multimodal ingestion (lab PDF + intake form via Claude
  vision), 11-tool registry, BM25 retrieval over the seed guideline corpus,
  bbox-overlay citation contract, deployed and demoable end-to-end. See
  [`W2_ARCHITECTURE.md`](./W2_ARCHITECTURE.md) Appendix C for what shipped
  vs what's deferred to the Thursday Early plan.
- ⏭ **Week 2 Early Submission (Thursday)** — LangGraph supervisor + 2
  workers + critic node, Cohere rerank + dense retrieval, 50-case golden
  eval set + PR-blocking pre-push hook, 6 new TurnTrace fields. Tracked in
  `W2_EARLY_IMPLEMENTATION.md` (TBD).
- 📋 Week 2 Final (Sunday) — cost/latency report, demo video polish.

## Week 2 highlights

What the deployed Co-Pilot can do today (`https://copilot-production-b532.up.railway.app`):

- **Drag-and-drop a lab PDF or intake form** onto the iframe drop zone.
  Claude vision (Sonnet 4.6) extracts structured facts; every fact carries
  a normalized bounding box back to the source page.
- **Click any citation chip** in the agent's response — bbox modal opens
  with the rectangle drawn on the canvas. Per-fact citations are encoded as
  `DocumentReference/<doc_id>#page=N&bbox=...&field=results[<analyte>].value`.
- **Ask a guideline-grounded question** (e.g., "What does the guideline
  recommend for high LDL?") — `search_guidelines` runs BM25 over the
  12-chunk seed corpus; the response cites `Guideline/<chunk_id>`.
- **Combined upload + guideline question** — the agent calls
  `get_recent_uploads` for the lab value AND `search_guidelines` for the
  evidence, returning two citation chips in one response.
- **sha3-512 idempotency** — re-dropping the same PDF triggers a "deduped"
  toast; no second extraction, no duplicate observation.

See [`W2_IMPLEMENTATION.md`](./W2_IMPLEMENTATION.md) for the 14-task plan
the MVP followed, and [`W2_ARCHITECTURE.md`](./W2_ARCHITECTURE.md) Appendix C
for what's deployed vs what's deferred.

See [`W1_IMPLEMENTATION.md`](./W1_IMPLEMENTATION.md) for the Week 1 status,
the agent loop, and the citation contract that Week 2 builds on.

## Demo sample documents

Four ready-to-upload synthetic PDFs in [`sample-documents/`](./sample-documents/)
that match the deployed Railway OpenEMR's two demo-hero patients:

- **Mariela Anguiano (47F, pid 5)** — `mariela-intake.pdf` + `mariela-lipid-renal.pdf` (LDL 190, creatinine 2.72, drives UC1/UC2)
- **Dana Pollich (2y, pid 9)** — `dana-intake.pdf` + `dana-pediatric-cbc.pdf` (10 allergies including aspirin, drives UC3 hard-block)

Drop them on the Co-Pilot iframe rail (right side of any patient's
demographics page) to exercise extraction → citation → bbox modal →
agent answers. See [`sample-documents/README.md`](./sample-documents/README.md)
for usage + suggested demo questions.

## Verifying the W2 eval gate

The W2 eval gate is the PRD-mandated regression-blocking suite (53 cases —
50 baseline + 2 informational/applied citation cases + 1 Layer-2 regression
canary — across six boolean rubrics: `schema_valid`, `citation_present`,
`factually_consistent`, `safe_refusal`, `no_phi_in_logs`,
`rules_block_regression`). It runs locally via `make eval`, on every PR via
`.github/workflows/copilot-ci.yml`, and pre-push via `.git/hooks/pre-push`
(installed once by `bash copilot/scripts/install-hooks.sh`).

**Threshold:** any category dropping below 0.95 OR more than 5pp from
`evals/baseline.json` exits 1.

**How a Layer-2 disable becomes a category drop.** The 50 happy-path cases
are fixture-driven (canned response + tool_results), so they don't directly
exercise `apply_rules` at runtime. The single canary case
`cross_layer2_regression_canary` plus the `rules_block_regression` scorer
fill that gap: the canary's fixture is engineered to trigger
`check_extracted_fact_has_source_doc` (a fragment-only `DocumentReference`
citation whose parent doc is absent from `tool_results`). The scorer
asserts that `apply_rules` returns `passed=False` for this fixture. With
the rule active, it does → scorer passes. With the rule disabled, it
doesn't → scorer fails → cross category drops → runner exits 1.

**Reproducing the regression so you can confirm the gate fires:**

1. Comment out the W2 Layer-2 rule call in
   `copilot/app/verification/rules.py:198`:

   ```python
   # rejections.extend(
   #     check_extracted_fact_has_source_doc(response, tool_results)
   # )
   ```

2. Run `make eval-fast` (the pre-push subset, ~2s in Docker). Expect:

   - `14/15 cases passed`
   - `cross  66.7%  (baseline 100.0%)`
   - `FAIL: cross: 66.67% < 95% floor`
   - `make: *** [eval-fast] Error 1` (non-zero exit)

3. Try `git push` — the pre-push hook re-runs `make eval-fast` and blocks
   the push with the same exit code.

4. Revert the comment, run `make eval-fast` again — `15/15 cases passed`,
   exit 0, push succeeds.

To re-freeze the baseline (e.g., after intentional case additions):
`make eval-baseline`.

## Front-desk demo prep (W2 Tier 2 lite)

The W2 architecture's "front desk uploads intake forms before the
physician opens the iframe" flow is shipped as the **lite slice** in
this branch:

- **`acl_upgrade.php` v14** grants the stock **Front Office** group
  write access to `patients|docs`. Run the OpenEMR ACL upgrade after
  pulling this branch (Admin → Other → ACL Upgrade) so receptionists
  can upload via the Documents Zend module.
- **`GET /v1/sessions/{id}/pending_intakes`** returns processed
  documents for the active patient. Panel-gated.
- **Iframe banner** appears top-of-iframe when the endpoint returns
  ≥1 doc: *"N intake documents uploaded by front desk — review."*
  Click to expand the list; click a row to open the existing bbox
  modal pointed at the doc.
- **Per-session in-memory dismiss** — closing the iframe re-fetches
  the list. Persistent acknowledgement
  (`processed_documents.acknowledged_by_physician_at`) is **deferred
  to Final**.

**Manual demo prep** (not automatable from the agent service):

1. Run the ACL upgrade so the Front Office group has the new grant.
2. Create a Front Office user (or use an existing one) — Admin →
   Users → Add.
3. Log in as that user, open a target patient's chart, and upload one
   or two intake-form PDFs via the **Documents** tab (Zend module).
4. Log out, then log back in as the physician and open the Co-Pilot
   iframe for the same patient. The banner should appear with the
   uploaded count.

The full **facility-scope** front-desk gate (`_verify_patient_in_facility`,
facility-aware variants of `copilot-finder-scope.php` and
`copilot-demographics-gate.php`) and the dataset expansion to 18-20
patients across 2 facilities are **deferred to Final** per
`copilot/W2_EARLY_IMPLEMENTATION.md`.
