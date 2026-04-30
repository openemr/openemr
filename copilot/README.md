# Clinical Co-Pilot — Agent Service

A separate Python (FastAPI) service that surfaces inside OpenEMR as a SMART on
FHIR app and gives a primary care physician patient context in the 60–90s
window between rooms.

This directory is intentionally a sibling of OpenEMR's PHP tree, not nested
inside it. The agent never touches OpenEMR's database directly — it talks to
the existing FHIR API as an OAuth2 client.

See:
- [`../ARCHITECTURE.md`](../ARCHITECTURE.md) — full design
- [`../USERS.md`](../USERS.md) — target user + use cases
- [`../AUDIT.md`](../AUDIT.md) — codebase audit findings the agent must mitigate

## Quick start (Docker)

```bash
cp .env.example .env
# Edit .env — at minimum: ANTHROPIC_API_KEY, OAUTH_CLIENT_ID, OAUTH_CLIENT_SECRET

docker compose up --build
# → http://localhost:8080/healthz
```

## OpenEMR OAuth2 client registration

The agent runs as a confidential SMART client. Register it once against your
OpenEMR instance:

```bash
curl -X POST 'https://<openemr-host>/oauth2/default/registration' \
  -H 'Content-Type: application/json' \
  -d '{
    "application_type": "confidential",
    "client_name": "Clinical Co-Pilot",
    "scope": "system/Patient.read system/Observation.read system/MedicationRequest.read system/Condition.read system/Encounter.read system/AllergyIntolerance.read"
  }'
```

Take the returned `client_id` / `client_secret` and the system administrator
must enable the client in **OpenEMR Admin → System → API Clients**, then mark
its scopes as approved. Put the credentials in `.env`.

## Layout

```
app/
  main.py              FastAPI entry, /healthz, debug FHIR roundtrip
  config.py            Settings (env-driven)
  fhir/                OAuth2 + FHIR HTTP client
  tools/               (Phase B) 8 FHIR-backed tools — see ARCHITECTURE §3.2
  agent/               (Phase C) Anthropic SDK orchestration loop
  verification/        (Phase C) Layer-1 attribution + Layer-2 domain rules
  phi/                 (Phase B) PHI minimizer + session pseudonym map
  observability/       (Phase D) Langfuse trace wrapper
  acl/                 (Phase B) GACL mirror — defense in depth
  web/                 (Phase E) Standalone chat UI
evals/                 (Phase D) pytest + promptfoo cases
```

## Status

- ✅ Phase A — skeleton, OAuth2, FHIR roundtrip
- ⬜ Phase B — tools, PHI minimizer, ACL mirror
- ⬜ Phase C — agent loop, verification gate
- ⬜ Phase D — observability, eval suite
- ⬜ Phase E — chat UI, Railway deploy
- ⬜ Phase F — demo video, cost analysis
