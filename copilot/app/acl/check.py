"""ACL middleware — defense in depth on top of OpenEMR's GACL.

OpenEMR enforces ACL server-side via `AclMain::aclCheckCore($section, $action, $user)`
called inside the FHIR controllers. The agent does NOT replace that. This
middleware mirrors a small subset of the GACL section/action map so we can
*deny early* — before a FHIR call is made — when the requesting physician
clearly lacks the scope.

If this layer says "allow" but OpenEMR's server-side ACL says "deny", the FHIR
call returns 401/403 and the tool surfaces it (see ARCHITECTURE §7.1). If this
layer says "deny", we never call FHIR. The FHIR call is the source of truth;
this is the cheap pre-flight.

For v1 the role→scope map is hard-coded for the demo physician role. Real
multi-user deploys would fetch the user's GACL ARO assignments from OpenEMR
via the Portal/Account API.
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
