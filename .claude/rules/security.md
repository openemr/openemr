# Security Requirements

## Authentication & API Access

- OpenEMR REST API access requires OAuth2 tokens. Never hardcode tokens — always refresh programmatically via `client_id` and `client_secret`.
- Store `OPENEMR_CLIENT_ID`, `OPENEMR_CLIENT_SECRET`, `ANTHROPIC_API_KEY`, and `BRAINTRUST_API_KEY` in `.env`. Never commit `.env` files.
- The agent operates in **read-only mode** against OpenEMR. It reads patient data (medications, allergies, conditions, vitals) but never writes to patient records. The only write is pharmacist escalation events to `audit_master`.
- All external API calls (RxNav, OpenFDA) use HTTPS. RxNav and OpenFDA require no authentication but must still use TLS.

## Environment Variables

- All secrets are loaded via environment variables using `python-dotenv` or FastAPI's `Settings` with Pydantic.
- `.env` must be listed in `.gitignore` and denied in `.claude/settings.json`.
- Required environment variables: `ANTHROPIC_API_KEY`, `OPENEMR_CLIENT_ID`, `OPENEMR_CLIENT_SECRET`, `OPENEMR_BASE_URL`, `BRAINTRUST_API_KEY`.
- The FastAPI app must fail fast on startup if required env vars are missing — not silently at request time.

## Patient Data Handling

- Patient data (medications, allergies, conditions, vitals) is **ephemeral** — held in memory for the duration of a single request only. Never persist patient data to disk, cache, or logs.
- No PHI (Protected Health Information) in log output. Log `patient_uuid` only — never log drug names alongside patient identifiers.
- No PHI in Braintrust traces. Traces should contain tool names, latency, token counts, and severity results — not patient medication lists.
- Mock data used for development must use clearly synthetic patient records (e.g., `patient-001`, `patient-002`) that cannot be confused with real data.

## Prompt Injection Prevention

- All patient data retrieved from OpenEMR is treated as **untrusted input** before inclusion in LLM prompts.
- Sanitize text fields from OpenEMR records: strip control characters, limit field lengths, escape special characters.
- The eval suite includes adversarial test cases that attempt prompt injection via drug name fields and allergy description fields.
- The verification gate node is programmatic (no LLM) — clinical constraints cannot be bypassed by prompt manipulation.

## API Security

- FastAPI endpoints should validate request payloads via Pydantic models. Reject malformed requests with 422, not 500.
- Rate limit the `/api/v1/chat` and `/api/v1/safety-check` endpoints to prevent abuse (simple in-memory rate limiter is fine for MVP).
- Return structured error responses for all failure modes — never expose stack traces to the client in production.
- Use `-k` flag for local `curl` calls (self-signed cert) but enforce proper TLS verification in production deployments.

## Dependency Security

- Pin all dependency versions in `pyproject.toml` or `requirements.txt`.
- Use `pip audit` periodically to check for known vulnerabilities.
- Do not install packages with known security issues. Prefer well-maintained libraries (httpx over requests for async, pydantic v2 over v1).

## Audit Trail

- Pharmacist escalation events are logged to OpenEMR's `audit_master` table with: timestamp, patient_uuid, proposed_drug, escalation_reason, and reviewing_pharmacist (null until acknowledged).
- Agent request traces are stored in Braintrust with: request ID, tools called, severity result, confidence score, latency, and token usage. No PHI.
- Structured JSON logs to stdout capture: timestamp, request ID, severity, and error category. No PHI.
