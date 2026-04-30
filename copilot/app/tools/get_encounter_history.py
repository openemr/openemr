"""get_encounter_history — recent Encounter list (5 most recent by default).

Maps to UC1, UC3.
Required ACL: encounters|notes
"""
from __future__ import annotations

from typing import Any

from app.fhir.client import FhirClient
from app.phi.minimizer import (
    collect_name_terms_from_patient,
    strip_encounter,
)
from app.phi.session import PseudonymMap
from app.tools._base import ToolResult, bundle_entries, run_tool


async def _fetch(fhir: FhirClient, patient_id: str) -> list[dict[str, Any]]:
    patient = await fhir.get_resource("Patient", patient_id)
    bundle = await fhir.search(
        "Encounter",
        {"patient": patient_id, "_sort": "-date", "_count": 5},
    )
    return [{"_kind": "patient_for_terms", "resource": patient}] + [
        {"_kind": "enc", "resource": e} for e in bundle_entries(bundle)
    ]


def _transform(raw: list[dict[str, Any]], session: PseudonymMap) -> list[dict[str, Any]]:
    patient_resource = next(
        (item["resource"] for item in raw if item["_kind"] == "patient_for_terms"), None
    )
    name_terms = collect_name_terms_from_patient(patient_resource or {})
    return [
        strip_encounter(item["resource"], session, name_terms)
        for item in raw
        if item["_kind"] == "enc"
    ]


async def run(*, fhir: FhirClient, session: PseudonymMap) -> ToolResult:
    return await run_tool(
        name="get_encounter_history",
        record_type="Encounter",
        section="encounters",
        action="notes",
        fetch=_fetch,
        transform=_transform,
        fhir=fhir,
        session=session,
    )


SCHEMA = {
    "name": "get_encounter_history",
    "description": (
        "Retrieve the patient's last 5 encounters (most recent first). Each includes "
        "start date, encounter type, reason text, and participant pseudonyms. Use for "
        "'what's changed since last visit' (UC1). If the most recent prior encounter is "
        ">12 months old, surface that fact rather than comparing to it as if it were "
        "current."
    ),
    "input_schema": {"type": "object", "properties": {}, "required": []},
}
