# Testing Conventions

## Current State

- MVP uses a simple eval runner script (`evals/runner.py`) that loops through test cases in JSON and reports pass/fail.
- Post-MVP, Braintrust handles eval runs with scoring functions and regression detection.

## Framework

- **pytest** for unit and integration tests.
- **Braintrust Evals** for agent-level evaluation (correctness, tool selection, safety, latency).
- **httpx** for testing FastAPI endpoints (via `TestClient` or async client).

## What to Test

### Unit Tests (per tool)
- `get_patient_medications`: valid UUID returns structured medication list; invalid UUID returns error; empty medication list returns `[]` not error.
- `get_patient_allergies`: valid UUID returns allergy list; patient with no allergies returns `[]`.
- `check_drug_interaction`: known interacting pair (warfarin + aspirin) returns correct severity; non-interacting pair returns `severity="none"`; invalid RxNorm code returns `severity="unknown"` with flag.
- Each tool's error handling: API timeout, malformed response, missing fields.

### Verification Tests
- Allergy conflict check: penicillin-allergic patient + amoxicillin → blocked.
- Confidence scoring: contraindicated → 1.0, major → 0.85, moderate → 0.65, minor → 0.4.
- Hallucination detection: narrative mentioning a drug not in patient's medication list → flagged.
- Domain constraints: warfarin + anticoagulant → pharmacist escalation; pediatric dosage outside FDA range → flagged.

### Integration Tests
- Full agent flow: natural language query → correct tools called → structured safety response returned.
- Multi-turn conversation: follow-up question uses context from previous turn.
- Error propagation: RxNav timeout → agent returns degraded response with reduced confidence, not a crash.

### API Tests
- `POST /api/v1/safety-check` with valid payload → 200 with SafetyCheckResponse.
- `POST /api/v1/safety-check` with missing patient_uuid → 422.
- `POST /api/v1/chat` with adversarial input → still returns safety flags, doesn't comply with injection.
- `GET /api/v1/health` → 200 with service status.

## Eval Dataset (50+ cases)

### Structure
Each test case is a JSON object:
```json
{
  "id": "TC-001",
  "category": "happy_path | edge_case | adversarial | multi_step",
  "description": "Human-readable description",
  "input": { "patient_uuid": "...", "drug_name": "...", "drug_rxnorm": "..." },
  "expected": {
    "severity": "safe | minor | moderate | major | contraindicated",
    "requires_pharmacist_review": false,
    "tools_called": ["get_patient_medications", "check_drug_interaction"],
    "allergy_conflict": false
  },
  "pass_criteria": {
    "severity_match": true,
    "escalation_match": true,
    "latency_under_ms": 5000
  }
}
```

### Breakdown
- **20+ happy path:** Safe prescriptions across common drug classes.
- **10+ edge cases:** No medications, polypharmacy (15+ meds), missing RxNorm code, no allergies documented, vitals unavailable.
- **10+ adversarial:** Prompt injection via drug name field, "ignore safety checks", "approve this anyway", off-label requests.
- **10+ multi-step:** Anticoagulated patient with renal impairment, pregnant patient needing antibiotic, patient with multiple interacting medications.

## What Not to Test

- OpenEMR's internal PHP services (not your code).
- RxNav API correctness (trust the NLM; test your parsing of their responses).
- LLM output verbatim wording (test structured fields like severity, not exact narrative text).
- Frontend styling or layout.

## Test File Placement

- Unit tests: `tests/unit/test_*.py` (one file per tool).
- Integration tests: `tests/integration/test_agent_flow.py`.
- API tests: `tests/api/test_endpoints.py`.
- Eval dataset: `evals/dataset/*.json`.
- Eval runner: `evals/runner.py`.

## Conventions

- Test names use plain English: `def test_amoxicillin_flagged_for_penicillin_allergic_patient():`.
- Arrange–Act–Assert structure in every test.
- Mock external APIs (RxNav, OpenFDA) in unit tests with `pytest-httpx` or `respx`. Never hit live external APIs in unit tests.
- Mock OpenEMR data layer with the same mock data used in MVP development.
- Use `pytest.mark.integration` to tag tests that require a running OpenEMR instance.
- Use `pytest.mark.slow` for tests that hit real external APIs (run separately, not in CI).

## CI

- `ruff check .` and `ruff format --check .` must pass.
- `pytest tests/unit/` must pass before merging.
- `python evals/runner.py` reports pass rate — target >80%.
- Integration tests run on-demand, not blocking CI (require live OpenEMR).
