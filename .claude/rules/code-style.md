# Code Style Guidelines

## Language & Framework

- **Python 3.11+:** Use type hints throughout. Target Python 3.11 minimum.
- **FastAPI:** Use async endpoints for all API routes. Use dependency injection for shared resources (DB connections, API clients).
- **LangGraph:** Define agent graphs with explicit TypedDict state schemas. Keep nodes small and single-purpose.
- **Pydantic:** Use Pydantic v2 BaseModel for all data schemas (tool inputs, tool outputs, API requests/responses).

---

## Python

- **Type Hints:** All functions must have fully typed signatures (params and return types). Use `T | None` over `Optional[T]`.
- **Pydantic Models:** Define all data structures as Pydantic BaseModel classes in dedicated schema files. No raw dicts for structured data.
- **Constants:** Use `SCREAMING_SNAKE_CASE` for module-level constants. Group clinical constants (RxNorm codes, severity levels) in `src/tools/constants.py`.
- **No `Any`:** Use `Unknown` or define proper types. Exception: third-party library return types that genuinely lack typing.
- **Error Handling:** Use explicit try/except with specific exception types. Never use bare `except:`. Always return structured error responses from tools, never raise unhandled exceptions.

---

## Naming Conventions

- **Self-Documenting Names:** Variables and functions must be descriptive (e.g., `check_drug_allergy_conflict` not `check_allergy`, `get_patient_medications` not `get_meds`).
- **Boolean Clarity:** Prefix booleans with `is_`, `has_`, or `should_` (e.g., `is_contraindicated`, `has_allergy_conflict`, `requires_pharmacist_review`).
- **Files:** Use `snake_case.py` for all Python files.
- **Classes:** Use `PascalCase` for classes, Pydantic models, and TypedDicts.
- **Functions:** Use `snake_case` for all functions and methods.
- **Constants:** Use `SCREAMING_SNAKE_CASE` (e.g., `RXNAV_BASE_URL`, `SEVERITY_CONTRAINDICATED`).
- **Pydantic Models:** Suffix request/response models descriptively (e.g., `SafetyCheckRequest`, `InteractionResult`, `Medication`).

---

## Project Structure

- **API Routes:** FastAPI routers in `src/api/`. Keep route handlers thin — delegate to agent or tool logic.
- **Agent:** LangGraph graph definition, state schema, and node functions in `src/agent/`.
- **Tools:** One file per tool in `src/tools/`. Each tool file contains the tool function, its input/output Pydantic models, and its error handling.
- **Verification:** Domain constraint checks and hallucination detection in `src/verification/`.
- **Schemas:** Shared Pydantic models in `src/schemas/` if used across multiple modules. Tool-specific models stay in the tool file.
- **Mock Data:** Mock patient data for MVP in `src/tools/mock_data.py`. Clearly separated from real API integration code.

---

## LangGraph Conventions

- **State Schema:** Define a single `ClinicalSafetyState(TypedDict)` as the source of truth for the agent graph.
- **Node Functions:** Each node is a pure function that takes state and returns a partial state update. No side effects outside of tool calls.
- **Tool Invocation:** Always use Claude's structured `tool_use` mode. Never parse tool calls from freeform text.
- **Verification Gate:** The verification node must be purely programmatic (no LLM). Clinical constraints are enforced in code, not delegated to the model.

---

## Imports

- **Standard Library First:** Group imports as: standard library, third-party, local.
- **Explicit Imports:** Use explicit imports (`from src.tools.drug_interaction import check_drug_interaction`), not wildcard imports.
- **No Circular Imports:** Tools should not import from the agent module. Agent imports tools, not the reverse.

---

## Formatting

- **Ruff:** Use `ruff` for linting and formatting. Configure in `pyproject.toml`.
- **Line Length:** 100 characters max.
- **Quotes:** Double quotes for strings.
- **Trailing Commas:** Use trailing commas in multi-line collections and function signatures.
- **Indentation:** 4 spaces (Python standard).

---

## Comments & Documentation

- **The "Why", Not the "What":** Comments explain reasoning behind clinical rules, verification logic, or non-obvious API behavior — not what the code obviously does.
- **Docstrings:** All public functions and classes get Google-style docstrings with Args, Returns, and Raises sections.
- **Clinical Logic:** Explicitly document why specific clinical constraints exist (e.g., "Penicillin allergy + beta-lactam = block because of cross-reactivity risk, not just same-class").
- **API Sources:** Document the external API endpoint and expected response format for every external tool call (RxNav, OpenFDA).
