"""get_allergies — AllergyIntolerance list.

Maps to UC2, UC3.
Required ACL: patients|rx
"""
from __future__ import annotations

from typing import Any

from app.fhir.client import FhirClient
from app.phi.minimizer import (
    collect_name_terms_from_patient,
    strip_allergy,
)
from app.phi.session import PseudonymMap
from app.tools._base import ToolResult, bundle_entries, run_tool


async def _fetch(fhir: FhirClient, patient_id: str) -> list[dict[str, Any]]:
    patient = await fhir.get_resource("Patient", patient_id)
    bundle = await fhir.search(
        "AllergyIntolerance", {"patient": patient_id, "_count": 50}
    )
    return [{"_kind": "patient_for_terms", "resource": patient}] + [
        {"_kind": "allergy", "resource": a} for a in bundle_entries(bundle)
    ]


def _transform(raw: list[dict[str, Any]], session: PseudonymMap) -> list[dict[str, Any]]:
    patient_resource = next(
        (item["resource"] for item in raw if item["_kind"] == "patient_for_terms"), None
    )
    name_terms = collect_name_terms_from_patient(patient_resource or {})
    return [
        strip_allergy(item["resource"], session, name_terms)
        for item in raw
        if item["_kind"] == "allergy"
    ]


async def run(*, fhir: FhirClient, session: PseudonymMap) -> ToolResult:
    return await run_tool(
        name="get_allergies",
        record_type="AllergyIntolerance",
        section="patients",
        action="rx",
        fetch=_fetch,
        transform=_transform,
        fhir=fhir,
        session=session,
    )


SCHEMA = {
    "name": "get_allergies",
    "description": (
        "Retrieve the patient's documented allergies (drug, food, environmental). "
        "Required input to UC3 (medication-safety check) — the verification gate "
        "hard-blocks any 'safe to prescribe' verdict that conflicts with this list."
    ),
    "input_schema": {"type": "object", "properties": {}, "required": []},
}
