"""get_active_medications — current MedicationRequest list.

Maps to UC1, UC2, UC3.
Required ACL: patients|rx
"""
from __future__ import annotations

from typing import Any

from app.fhir.client import FhirClient
from app.phi.minimizer import (
    collect_name_terms_from_patient,
    strip_medication_request,
)
from app.phi.session import PseudonymMap
from app.tools._base import ToolResult, bundle_entries, run_tool


async def _fetch(
    fhir: FhirClient, patient_id: str, physician_user_id: str
) -> list[dict[str, Any]]:
    patient = await fhir.get_resource(
        "Patient", patient_id, physician_user_id=physician_user_id
    )
    bundle = await fhir.search(
        "MedicationRequest",
        {"patient": patient_id, "status": "active", "_count": 100},
        physician_user_id=physician_user_id,
    )
    return [{"_kind": "patient_for_terms", "resource": patient}] + [
        {"_kind": "medreq", "resource": m} for m in bundle_entries(bundle)
    ]


def _transform(raw: list[dict[str, Any]], session: PseudonymMap) -> list[dict[str, Any]]:
    patient_resource = next(
        (item["resource"] for item in raw if item["_kind"] == "patient_for_terms"), None
    )
    name_terms = collect_name_terms_from_patient(patient_resource or {})
    return [
        strip_medication_request(item["resource"], session, name_terms)
        for item in raw
        if item["_kind"] == "medreq"
    ]


async def run(*, fhir: FhirClient, session: PseudonymMap) -> ToolResult:
    return await run_tool(
        name="get_active_medications",
        record_type="MedicationRequest",
        section="patients",
        action="rx",
        fetch=_fetch,
        transform=_transform,
        fhir=fhir,
        session=session,
    )


SCHEMA = {
    "name": "get_active_medications",
    "description": (
        "Retrieve the patient's currently active medications (MedicationRequest with "
        "status=active). Each item includes drug_name, RxNorm code (when available), "
        "dose instructions, and authored_on date. NULL rxnorm_code is a known data "
        "quality issue (see AUDIT §4.2) — surface this caveat if drug-interaction logic "
        "depends on canonical codes."
    ),
    "input_schema": {"type": "object", "properties": {}, "required": []},
}
