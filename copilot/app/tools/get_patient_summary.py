"""get_patient_summary — Patient demographics + active problem list.

Maps to UC1 (pre-visit brief) and UC2 (multi-condition reasoning).
Required ACL: patients|demo
"""
from __future__ import annotations

from typing import Any

from app.fhir.client import FhirClient
from app.phi.minimizer import (
    collect_name_terms_from_patient,
    strip_condition,
    strip_patient,
)
from app.phi.session import PseudonymMap
from app.tools._base import ToolResult, bundle_entries, run_tool


async def _fetch(fhir: FhirClient, patient_id: str) -> list[dict[str, Any]]:
    patient = await fhir.get_resource("Patient", patient_id)
    conditions_bundle = await fhir.search(
        "Condition",
        {"patient": patient_id, "clinical-status": "active", "_count": 50},
    )
    return [{"_kind": "patient", "resource": patient}] + [
        {"_kind": "condition", "resource": c} for c in bundle_entries(conditions_bundle)
    ]


def _transform(raw: list[dict[str, Any]], session: PseudonymMap) -> list[dict[str, Any]]:
    if not raw:
        return []
    patient_resource = next(
        (item["resource"] for item in raw if item["_kind"] == "patient"), None
    )
    name_terms = collect_name_terms_from_patient(patient_resource or {})
    out: list[dict[str, Any]] = []
    if patient_resource:
        out.append(strip_patient(patient_resource, session))
    for item in raw:
        if item["_kind"] == "condition":
            out.append(strip_condition(item["resource"], session, name_terms))
    return out


async def run(*, fhir: FhirClient, session: PseudonymMap) -> ToolResult:
    return await run_tool(
        name="get_patient_summary",
        record_type="Patient+Condition",
        section="patients",
        action="demo",
        fetch=_fetch,
        transform=_transform,
        fhir=fhir,
        session=session,
    )


SCHEMA = {
    "name": "get_patient_summary",
    "description": (
        "Retrieve the patient's demographics (age, gender) and active problem list "
        "(conditions). Use this first for any pre-visit brief or 'who is this patient' "
        "question. Returns one Patient record and 0–N Condition records, each with a "
        "record_id you must cite for any claim derived from it."
    ),
    "input_schema": {
        "type": "object",
        "properties": {},  # patient is implicit from the session
        "required": [],
    },
}
