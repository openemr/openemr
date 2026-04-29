# Clinical Co-Pilot Sidecar

A separate Python sidecar that integrates an AI-powered diagnostic cross-check
and chart-error scan into OpenEMR. Implements the architecture documented in
`/Users/scottlydon/Documents/Claude/Projects/Gauntlet/ARCHITECTURE.md`.

> The agent never talks directly to MySQL and never goes through `/interface/`.
> It speaks to OpenEMR over Fast Healthcare Interoperability Resources (FHIR) R4
> and a small new internal Read API. Authorization is handled at a Backend-for-
> Frontend (BFF) with OAuth2 + Proof Key for Code Exchange (PKCE), 5-minute
> downscoped tokens per `Patient/{id}` compartment.

## Layout

```
clinical-copilot/
├── sidecar/                Python LangGraph agent service (FastAPI)
│   ├── snapshot/           Parallel FHIR fan-out + deterministic reconciliation
│   ├── agent/              Pair generator, judge, aggregator, LangGraph wiring
│   ├── verifier/           Source attribution + curated rule store
│   ├── audit/              Hash-chained AI audit log (Postgres, 7-year retention)
│   ├── observability/      OpenTelemetry tracing + Prometheus metrics
│   └── api/                FastAPI HTTP routes (chat, health)
├── bff/                    Backend-for-Frontend (OAuth2 + PKCE + policy)
├── ui/                     Minimal chat surface (single-file HTML)
├── evals/                  3-layer eval suite + CI gate
│   ├── layer1_pairwise/        Unit-style golden pairwise judgments
│   ├── layer2_patient_scenarios/  Synthetic patient charts (gout, osteo, …)
│   └── layer3_adversarial/     Prompt injection, scope escalation, missing-data
├── fixtures/patients/      Synthetic seed cases referenced in ARCHITECTURE.md
└── tests/                  Pytest unit + integration tests
```

## Quick start (local, no OpenEMR required)

```bash
cd clinical-copilot
python3.12 -m venv .venv && source .venv/bin/activate
pip install -e .[dev]

# Run the eval suite against the synthetic gout / osteoporosis cases
# (uses the deterministic mock LLM provider — no OpenAI key required)
pytest evals/ -v

# Start the sidecar with the mock LLM provider
COPILOT_LLM_PROVIDER=mock python -m sidecar.main
# Health: http://127.0.0.1:8801/health
# Chat:   POST http://127.0.0.1:8801/chat
```

To run against the real OpenAI Enterprise BAA endpoint, set:

```bash
export COPILOT_LLM_PROVIDER=openai
export OPENAI_API_KEY=sk-...    # the Gauntlet billing project key
export COPILOT_OPENAI_MODEL=gpt-5
```

To run alongside a local OpenEMR docker (`openemr/docker/development-easy/`):

```bash
docker compose -f clinical-copilot/docker-compose.yml up --build
```

## Use cases this sidecar implements

Trace every capability back to `USERS.md`:

- **Use Case A — Pre-visit Diagnostic Cross-Check.** Pairwise comparison of
  presenting symptoms × candidate explanations from the patient's chart.
- **Use Case B — Chart-Error / Conflict Detection.** Same engine, different
  prompt; pairwise comparison of finding × finding for biological / temporal /
  pharmacological inconsistency.
- **Use Case C — Mid-Visit Clarifier.** Tool-use against the patient snapshot;
  cites source row and date.

## Definition of useful (the bar from `USERS.md` Section 4)

1. Pre-visit prep on a 20-patient day drops from 20 minutes to 12 minutes.
2. At least one pairwise cross-check per week surfaces a finding the clinician
   had not considered, and they agree it was relevant.
3. Chart-error flag precision above 80% on clinician review.

## Key architectural decisions (from `ARCHITECTURE.md`)

- Sidecar, not in-process. Python ecosystem; isolated blast radius. (§1.2)
- LangGraph orchestration; OpenAI `gpt-5` on Enterprise BAA + ZDR primary,
  Azure OpenAI fallback. (§1.3, §1.4)
- Deterministic pairwise comparison engine, **not** free-form holistic
  prompting. (§4.1, §USERS 3.1)
- Verification is a separate stage with source attribution + curated rule
  store, not a prompt instruction. (§5)
- Hash-chained AI audit log separate from OpenEMR's `audit_master`,
  7-year retention. (§6.3)
- Three-layer eval suite gated in CI. (§7)

## Why a sidecar does not break HIPAA

See `ARCHITECTURE.md` §1.2.1 for the full mapping from each 45 CFR 164.312
safeguard to its implementation in this build. Topology is HIPAA-neutral; the
BAA chain and the per-safeguard implementation are the variables.

## License

Same as parent OpenEMR (GNU GPL v3).
