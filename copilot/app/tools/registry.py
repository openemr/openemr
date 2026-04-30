"""Tool registry — what the agent loop dispatches against."""
from __future__ import annotations

from typing import Any, Awaitable, Callable

from app.fhir.client import FhirClient
from app.phi.session import PseudonymMap
from app.tools import (
    check_drug_interactions,
    get_active_medications,
    get_allergies,
    get_encounter_history,
    get_encounter_note,
    get_patient_summary,
    get_recent_labs,
    get_recent_vitals,
)
from app.tools._base import ToolResult


async def _no_arg(
    fn: Callable[..., Awaitable[ToolResult]],
    *,
    fhir: FhirClient,
    session: PseudonymMap,
    args: dict[str, Any],
) -> ToolResult:
    return await fn(fhir=fhir, session=session)


async def _encounter_note(
    fhir: FhirClient, session: PseudonymMap, args: dict[str, Any]
) -> ToolResult:
    return await get_encounter_note.run(
        fhir=fhir, session=session, encounter_id=args["encounter_id"]
    )


async def _drug_interactions(
    fhir: FhirClient, session: PseudonymMap, args: dict[str, Any]
) -> ToolResult:
    return await check_drug_interactions.run(
        fhir=fhir,
        session=session,
        proposed_drug=args["proposed_drug"],
        current_drug_names=args.get("current_drug_names", []),
    )


# (tool_name, dispatch_fn, schema)
TOOL_REGISTRY: dict[str, dict[str, Any]] = {
    "get_patient_summary": {
        "schema": get_patient_summary.SCHEMA,
        "run": lambda fhir, session, args: _no_arg(
            get_patient_summary.run, fhir=fhir, session=session, args=args
        ),
    },
    "get_active_medications": {
        "schema": get_active_medications.SCHEMA,
        "run": lambda fhir, session, args: _no_arg(
            get_active_medications.run, fhir=fhir, session=session, args=args
        ),
    },
    "get_recent_labs": {
        "schema": get_recent_labs.SCHEMA,
        "run": lambda fhir, session, args: _no_arg(
            get_recent_labs.run, fhir=fhir, session=session, args=args
        ),
    },
    "get_recent_vitals": {
        "schema": get_recent_vitals.SCHEMA,
        "run": lambda fhir, session, args: _no_arg(
            get_recent_vitals.run, fhir=fhir, session=session, args=args
        ),
    },
    "get_encounter_history": {
        "schema": get_encounter_history.SCHEMA,
        "run": lambda fhir, session, args: _no_arg(
            get_encounter_history.run, fhir=fhir, session=session, args=args
        ),
    },
    "get_allergies": {
        "schema": get_allergies.SCHEMA,
        "run": lambda fhir, session, args: _no_arg(
            get_allergies.run, fhir=fhir, session=session, args=args
        ),
    },
    "get_encounter_note": {
        "schema": get_encounter_note.SCHEMA,
        "run": _encounter_note,
    },
    "check_drug_interactions": {
        "schema": check_drug_interactions.SCHEMA,
        "run": _drug_interactions,
    },
}


def get_tool_definitions() -> list[dict[str, Any]]:
    """Anthropic tool-use schema list, in registration order."""
    return [v["schema"] for v in TOOL_REGISTRY.values()]


async def dispatch(
    name: str, args: dict[str, Any], fhir: FhirClient, session: PseudonymMap
) -> ToolResult:
    entry = TOOL_REGISTRY.get(name)
    if not entry:
        return ToolResult(
            name=name,
            data=[],
            record_type="",
            error=f"unknown_tool: {name}",
        )
    return await entry["run"](fhir, session, args)
