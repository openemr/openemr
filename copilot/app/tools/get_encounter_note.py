"""get_encounter_note — DocumentReference attached to a specific encounter.

Maps to UC3 (find the source note for a medication's indication).
Required ACL: encounters|notes
"""
from __future__ import annotations

from typing import Any

from app.fhir.client import FhirClient
from app.phi.minimizer import (
    _scrub_text,
    collect_name_terms_from_patient,
    strip_encounter,
)
from app.phi.session import PseudonymMap
from app.tools._base import ToolResult, bundle_entries, run_tool


# Tool input is { "encounter_id": "..." }. We pass it via session metadata so the
# 5-step pattern remains uniform; see registry.py dispatch.


async def _build_fetch(encounter_id: str):
    async def _fetch(
        fhir: FhirClient, patient_id: str, physician_user_id: str
    ) -> list[dict[str, Any]]:
        patient = await fhir.get_resource(
            "Patient", patient_id, physician_user_id=physician_user_id
        )
        encounter = await fhir.get_resource(
            "Encounter", encounter_id, physician_user_id=physician_user_id
        )
        # DocumentReference linked to the encounter
        docs_bundle = await fhir.search(
            "DocumentReference",
            {"encounter": encounter_id, "_count": 10},
            physician_user_id=physician_user_id,
        )
        return [
            {"_kind": "patient_for_terms", "resource": patient},
            {"_kind": "encounter", "resource": encounter},
        ] + [{"_kind": "doc", "resource": d} for d in bundle_entries(docs_bundle)]

    return _fetch


def _transform(raw: list[dict[str, Any]], session: PseudonymMap) -> list[dict[str, Any]]:
    patient_resource = next(
        (item["resource"] for item in raw if item["_kind"] == "patient_for_terms"), None
    )
    name_terms = collect_name_terms_from_patient(patient_resource or {})
    out: list[dict[str, Any]] = []
    for item in raw:
        if item["_kind"] == "encounter":
            out.append(strip_encounter(item["resource"], session, name_terms))
        elif item["_kind"] == "doc":
            d = item["resource"]
            real_id = d.get("id", "")
            content_attachments = [
                c.get("attachment") or {} for c in (d.get("content") or [])
            ]
            text = _scrub_text(
                "\n".join(
                    a.get("title") or a.get("data") or "" for a in content_attachments
                ),
                name_terms,
            )
            out.append(
                {
                    "resourceType": "DocumentReference",
                    "id": real_id,
                    "record_id": f"DocumentReference/{real_id}",
                    "type": ((d.get("type") or {}).get("coding") or [{}])[0].get("display"),
                    "date": d.get("date"),
                    "text": text,
                }
            )
    return out


async def run(
    *, fhir: FhirClient, session: PseudonymMap, encounter_id: str
) -> ToolResult:
    fetch = await _build_fetch(encounter_id)
    return await run_tool(
        name="get_encounter_note",
        record_type="Encounter+DocumentReference",
        section="encounters",
        action="notes",
        fetch=fetch,
        transform=_transform,
        fhir=fhir,
        session=session,
    )


SCHEMA = {
    "name": "get_encounter_note",
    "description": (
        "Retrieve the documented note(s) attached to a specific Encounter. Use to "
        "answer 'why was [med X] started?' (UC3) — first call get_active_medications "
        "to find the prescribing encounter id, then call this with that encounter_id."
    ),
    "input_schema": {
        "type": "object",
        "properties": {
            "encounter_id": {
                "type": "string",
                "description": "The Encounter resource id (the bare id, not 'Encounter/id').",
            }
        },
        "required": ["encounter_id"],
    },
}
