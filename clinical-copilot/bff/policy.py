"""Second-layer policy check.

Independent of OpenEMR's ACLs (ARCHITECTURE.md §3.3, AUDIT.md §1.2).
The BFF maintains its own list of:

- Allowed ``(user, patient)`` pairs (derived nightly from the panel).
- Patient-level consent flags (``ai_allowed`` / ``ai_denied``).
- Per-purpose allow-list.

Both this check and the OpenEMR ACL must pass before any LLM call.
"""

from __future__ import annotations

from dataclasses import dataclass, field
from datetime import datetime, timezone


@dataclass(frozen=True)
class PolicyDenial:
    reason: str
    user_id: str
    patient_id: str
    purpose: str
    at: datetime


@dataclass
class PolicyStore:
    """In-memory policy store; production swaps in Postgres-backed rows.

    Default behaviour: if neither an explicit allow nor explicit deny is on
    file, the request is **denied**. The architecture (§3.3) defaults
    "AI-allowed" only for patients without ``is_sensitive`` and without
    opt-out, but for the demo we keep the safer default.
    """

    panel: dict[str, set[str]] = field(default_factory=dict)  # user_id -> set(patient_id)
    consent: dict[str, str] = field(default_factory=dict)      # patient_id -> "ai_allowed" | "ai_denied"
    allowed_purposes: set[str] = field(
        default_factory=lambda: {"diagnostic_cross_check", "chart_error_scan", "follow_up_question"}
    )

    def grant(self, *, user_id: str, patient_id: str) -> None:
        self.panel.setdefault(user_id, set()).add(patient_id)
        self.consent.setdefault(patient_id, "ai_allowed")

    def revoke(self, patient_id: str) -> None:
        self.consent[patient_id] = "ai_denied"

    def check(
        self, *, user_id: str, patient_id: str, purpose: str
    ) -> PolicyDenial | None:
        if purpose not in self.allowed_purposes:
            return PolicyDenial(
                reason=f"purpose '{purpose}' not in allow-list",
                user_id=user_id,
                patient_id=patient_id,
                purpose=purpose,
                at=datetime.now(tz=timezone.utc),
            )
        if patient_id not in self.panel.get(user_id, set()):
            return PolicyDenial(
                reason=f"user '{user_id}' is not on the panel for patient '{patient_id}'",
                user_id=user_id,
                patient_id=patient_id,
                purpose=purpose,
                at=datetime.now(tz=timezone.utc),
            )
        if self.consent.get(patient_id) != "ai_allowed":
            return PolicyDenial(
                reason=f"patient '{patient_id}' has not consented to AI use",
                user_id=user_id,
                patient_id=patient_id,
                purpose=purpose,
                at=datetime.now(tz=timezone.utc),
            )
        return None
