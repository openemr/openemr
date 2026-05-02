"""ACL — diagnostic pre-flight, with the runtime probe as source of truth.

OpenEMR enforces ACL server-side via `AclMain::aclCheckCore($section, $action, $user)`
called inside the FHIR controllers. The agent does NOT replace that.

As of A.4 (multi-physician hardening): the **runtime probe in
`app/tools/_base.py:run_tool`** is the authoritative ACL decision —
`GET /Patient/{active_patient_id}` is attempted with the physician's
own OAuth token. 401/403 → ACL denied; success → allowed. The result
is cached per session on `PseudonymMap.acl_decision`.

The static `PHYSICIAN_GRANTS` map below is kept as a non-blocking
diagnostic. If a physician's role doesn't show up here, we log it but
still let the runtime probe decide. Real multi-user deploys can extend
this map by reading GACL ARO assignments from OpenEMR's
`gacl_aro_groups_map` table via the Portal/Account API.
"""
from __future__ import annotations

from dataclasses import dataclass


# Section|Action map — mirrors the FHIR endpoints we use. Keys are the
# `(section, action)` tuples OpenEMR's `aclCheckCore` checks.
PHYSICIAN_GRANTS: set[tuple[str, str]] = {
    ("patients", "demo"),       # Patient + Condition
    ("patients", "med"),        # Observation (vitals)
    ("patients", "lab"),        # Observation (labs)
    ("patients", "rx"),         # MedicationRequest, AllergyIntolerance
    ("encounters", "notes"),    # Encounter, DocumentReference
}


@dataclass
class AclResult:
    allowed: bool
    section: str
    action: str
    reason: str = ""


def acl_check(user: str, section: str, action: str) -> AclResult:
    # Demo: any authenticated user with role=physician gets the physician grant set.
    # In production, this calls OpenEMR's permissions endpoint.
    if user and (section, action) in PHYSICIAN_GRANTS:
        return AclResult(allowed=True, section=section, action=action)
    return AclResult(
        allowed=False,
        section=section,
        action=action,
        reason=f"User '{user}' lacks {section}|{action} grant",
    )
