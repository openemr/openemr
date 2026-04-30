"""get_recent_vitals — Observation (category=vital-signs), last 5 years.

Maps to UC2.
Required ACL: patients|med
"""
from __future__ import annotations

from datetime import datetime, timedelta, timezone
from typing import Any

from app.fhir.client import FhirClient
from app.phi.minimizer import (
    collect_name_terms_from_patient,
    strip_observation,
)
from app.phi.session import PseudonymMap
from app.tools._base import ToolResult, bundle_entries, run_tool

LOOKBACK_DAYS = 1825  # 5 years — Synthea CCDA data spans patient lifetimes; a 90-day window misses everything.


def _lookback_start() -> str:
    return (datetime.now(timezone.utc) - timedelta(days=LOOKBACK_DAYS)).date().isoformat()


async def _fetch(fhir: FhirClient, patient_id: str) -> list[dict[str, Any]]:
    patient = await fhir.get_resource("Patient", patient_id)
    bundle = await fhir.search(
        "Observation",
        {
            "patient": patient_id,
            "category": "vital-signs",
            "date": f"ge{_lookback_start()}",
            "_count": 100,
            "_sort": "-date",
        },
    )
    return [{"_kind": "patient_for_terms", "resource": patient}] + [
        {"_kind": "obs", "resource": o} for o in bundle_entries(bundle)
    ]


def _transform(raw: list[dict[str, Any]], session: PseudonymMap) -> list[dict[str, Any]]:
    patient_resource = next(
        (item["resource"] for item in raw if item["_kind"] == "patient_for_terms"), None
    )
    name_terms = collect_name_terms_from_patient(patient_resource or {})
    return [
        strip_observation(item["resource"], session, name_terms)
        for item in raw
        if item["_kind"] == "obs"
    ]


async def run(*, fhir: FhirClient, session: PseudonymMap) -> ToolResult:
    return await run_tool(
        name="get_recent_vitals",
        record_type="Observation",
        section="patients",
        action="med",
        fetch=_fetch,
        transform=_transform,
        fhir=fhir,
        session=session,
    )


SCHEMA = {
    "name": "get_recent_vitals",
    "description": (
        "Retrieve vital signs (BP, HR, weight, etc.) from the last 90 days. Use for "
        "questions where the connecting evidence is a recent vital — e.g. 'is this "
        "dizziness orthostatic?' needs a recent BP. If no record exists in the window, "
        "say so explicitly; do not extrapolate from older data."
    ),
    "input_schema": {"type": "object", "properties": {}, "required": []},
}
