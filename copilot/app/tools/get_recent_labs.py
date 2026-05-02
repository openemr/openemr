"""get_recent_labs — Observation (category=laboratory), last 5 years by default.

Maps to UC1, UC2.
Required ACL: patients|lab
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


async def _fetch(
    fhir: FhirClient, patient_id: str, physician_user_id: str
) -> list[dict[str, Any]]:
    patient = await fhir.get_resource(
        "Patient", patient_id, physician_user_id=physician_user_id
    )
    bundle = await fhir.search(
        "Observation",
        {
            "patient": patient_id,
            "category": "laboratory",
            "date": f"ge{_lookback_start()}",
            "_count": 100,
            "_sort": "-date",
        },
        physician_user_id=physician_user_id,
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
        name="get_recent_labs",
        record_type="Observation",
        section="patients",
        action="lab",
        fetch=_fetch,
        transform=_transform,
        fhir=fhir,
        session=session,
    )


SCHEMA = {
    "name": "get_recent_labs",
    "description": (
        "Retrieve laboratory observations (LOINC-coded) from the last 5 years. Each "
        "item includes value, unit, reference_range, and effective_datetime. Use this "
        "for 'what's changed since last visit' (UC1) and to ground hypotheses in "
        "multi-condition reasoning (UC2). If reference_range is null, do not assert "
        "abnormal/normal — say the range is unknown."
    ),
    "input_schema": {"type": "object", "properties": {}, "required": []},
}
