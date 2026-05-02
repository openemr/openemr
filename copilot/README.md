# Clinical Co-Pilot — Agent Service

[![copilot-ci](https://github.com/rikkiiwang/openemr/actions/workflows/copilot-ci.yml/badge.svg?branch=master)](https://github.com/rikkiiwang/openemr/actions/workflows/copilot-ci.yml)

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
- [`IMPLEMENTATION.md`](./IMPLEMENTATION.md) — current status, what's built, what's left
- [`../ARCHITECTURE.md`](../ARCHITECTURE.md) — full design
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

The eval suite has **17 tests** across PHI minimization, tool integration,
verification gate, and live LLM scenarios:

```bash
make test       # PHI + tool integration tests only (no live LLM)
make eval       # full suite, mocked LLM
make eval-live  # full suite, real LLM call (requires ANTHROPIC_API_KEY in env)
```

Test breakdown:
- 7 PHI minimizer tests (`evals/tools/test_phi_minimizer.py`)
- 3 tool integration tests (`evals/tools/test_tool_integration.py`)
- 4 verification gate tests (`evals/agent/test_verification.py`)
- 3 live LLM scenarios — UC1 happy path, UC1 refusal-on-empty, prompt injection
  (`evals/agent/test_scenarios.py`)

`make eval` writes `evals/RESULTS.md` with a summary of pass/fail counts.

## Layout

```
app/
  main.py              FastAPI entry, /healthz, /v1/sessions, /v1/chat, /v1/patient/{id}/raw
  config.py            Settings (env-driven)
  fhir/                OAuth2 + FHIR HTTP client
  tools/               8 FHIR-backed tools using shared 5-step pattern (_base.py + registry.py)
  agent/               Orchestration loop, provider-agnostic LLM adapter, prompt, schemas
  verification/        Layer-1 attribution + Layer-2 domain rules
  phi/                 PHI minimizer + session pseudonym map
  observability/       Langfuse trace wrapper (noop fallback when keys absent)
  acl/                 GACL mirror — defense in depth
  web/                 Standalone chat UI (with ?patient_id= auto-bind for the iframe rail)
evals/                 pytest suite — 17 tests
```

## Status

- ✅ Phase A — skeleton, OAuth2, FHIR roundtrip
- ✅ Phase B — 8 tools, PHI minimizer, ACL mirror
- ✅ Phase C — agent loop, two-layer verification gate
- ✅ Phase D — Langfuse observability, eval suite (17/17)
- ✅ Phase E — chat UI, Railway deploy, **iframe rail in OpenEMR**
- 📋 Phase F — demo video, AI cost analysis, secret rotation

See [`IMPLEMENTATION.md`](./IMPLEMENTATION.md) for the up-to-date status,
remaining checklist, and risk log.
