"""check_drug_interactions — RxNav-backed interaction check.

Maps to UC3.
Required ACL: patients|rx (for the existing-meds context)

For the demo this is mock-aware: if RxNav is unreachable or the proposed med
lacks an RxCUI (AUDIT §4.2), the tool surfaces the gap in `data` and returns
an empty record_id list. The verification gate then prevents any 'safe to
prescribe' verdict that depends on an interaction-clean result it never got.
"""
from __future__ import annotations

import asyncio
from typing import Any

import httpx

from app.acl.check import acl_check
from app.fhir.client import FhirClient
from app.phi.session import PseudonymMap
from app.tools._base import ToolResult


async def _resolve_rxcui(client: httpx.AsyncClient, drug_name: str) -> str | None:
    try:
        r = await client.get(
            "https://rxnav.nlm.nih.gov/REST/rxcui.json",
            params={"name": drug_name, "search": 1},
            timeout=4.0,
        )
        if r.status_code != 200:
            return None
        ids = (((r.json() or {}).get("idGroup") or {}).get("rxnormId")) or []
        return ids[0] if ids else None
    except (httpx.HTTPError, ValueError):
        return None


async def _interactions(
    client: httpx.AsyncClient, rxcuis: list[str]
) -> list[dict[str, Any]]:
    if len(rxcuis) < 2:
        return []
    try:
        r = await client.get(
            "https://rxnav.nlm.nih.gov/REST/interaction/list.json",
            params={"rxcuis": "+".join(rxcuis)},
            timeout=5.0,
        )
        if r.status_code != 200:
            return []
        body = r.json() or {}
        results: list[dict[str, Any]] = []
        for group in (body.get("fullInteractionTypeGroup") or []):
            for it in group.get("fullInteractionType") or []:
                for pair in it.get("interactionPair") or []:
                    results.append(
                        {
                            "severity": pair.get("severity"),
                            "description": pair.get("description"),
                        }
                    )
        return results
    except (httpx.HTTPError, ValueError):
        return []


async def run(
    *,
    fhir: FhirClient,  # unused — kept for uniform signature
    session: PseudonymMap,
    proposed_drug: str,
    current_drug_names: list[str],
) -> ToolResult:
    user = session.physician_user_id
    acl = acl_check(user, "patients", "rx")
    if not acl.allowed:
        return ToolResult(
            name="check_drug_interactions",
            data=[],
            record_type="DrugInteraction",
            acl_check=acl,
            error=f"acl_denied: {acl.reason}",
        )
    async with httpx.AsyncClient() as client:
        proposed_rxcui = await _resolve_rxcui(client, proposed_drug)
        current_rxcuis = await asyncio.gather(
            *[_resolve_rxcui(client, n) for n in current_drug_names]
        )
        unresolved = [n for n, r in zip(current_drug_names, current_rxcuis) if r is None]
        all_rxcuis = [r for r in current_rxcuis if r] + (
            [proposed_rxcui] if proposed_rxcui else []
        )
        interactions = await _interactions(client, all_rxcuis)
    return ToolResult(
        name="check_drug_interactions",
        record_type="DrugInteraction",
        data={
            "proposed_drug": proposed_drug,
            "proposed_rxcui": proposed_rxcui,
            "interactions": interactions,
            "unresolved_current_meds": unresolved,
        },
        record_ids=[],  # external, no FHIR anchor
        acl_check=acl,
    )


SCHEMA = {
    "name": "check_drug_interactions",
    "description": (
        "Check for known drug-drug interactions between a proposed medication and the "
        "patient's current med list (call get_active_medications first, pass the "
        "drug_name list in). Uses RxNav (US National Library of Medicine). Note: meds "
        "without an RxNorm code (see AUDIT §4.2) are returned in unresolved_current_meds "
        "— do NOT issue a 'safe' verdict that ignores them; surface the gap explicitly."
    ),
    "input_schema": {
        "type": "object",
        "properties": {
            "proposed_drug": {
                "type": "string",
                "description": "Name of the medication the physician is considering prescribing.",
            },
            "current_drug_names": {
                "type": "array",
                "items": {"type": "string"},
                "description": "Names of the patient's current active medications.",
            },
        },
        "required": ["proposed_drug", "current_drug_names"],
    },
}
