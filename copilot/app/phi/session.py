"""Session-scoped pseudonym map.

Maps OpenEMR's real UUIDs (Patient/abc-123, Practitioner/xyz-789) to opaque
session-scoped tokens (Patient-A1B2, Provider-A) so the LLM never sees a real
identifier. Mappings live for the duration of one chat session and are dropped
when the session ends.

The map is in-memory for v1. Multi-replica deploys would back this with Redis;
not required for the demo (one Railway replica).
"""
from __future__ import annotations

import secrets
import string
from dataclasses import dataclass, field
from datetime import datetime, timezone
from threading import RLock


def _rand_token(n: int = 4) -> str:
    alphabet = string.ascii_uppercase + string.digits
    return "".join(secrets.choice(alphabet) for _ in range(n))


@dataclass
class PseudonymMap:
    session_id: str
    physician_user_id: str
    active_patient_id: str  # the real OpenEMR Patient UUID this session is scoped to
    created_at: datetime = field(default_factory=lambda: datetime.now(timezone.utc))
    # forward and reverse maps
    _real_to_pseudo: dict[str, str] = field(default_factory=dict)
    _pseudo_to_real: dict[str, str] = field(default_factory=dict)
    _provider_letter_idx: int = 0
    _lock: RLock = field(default_factory=RLock)
    # ACL probe result, cached for the session. None until the first tool
    # call probes /Patient/{active_patient_id} with this physician's token.
    acl_decision: object | None = None

    def patient_pseudonym(self) -> str:
        return self.pseudo_for("Patient", self.active_patient_id)

    def pseudo_for(self, resource_type: str, real_id: str) -> str:
        with self._lock:
            key = f"{resource_type}/{real_id}"
            if key in self._real_to_pseudo:
                return self._real_to_pseudo[key]
            if resource_type == "Practitioner":
                token = string.ascii_uppercase[self._provider_letter_idx % 26]
                self._provider_letter_idx += 1
                pseudo = f"Provider-{token}"
            elif resource_type == "Patient":
                pseudo = f"Patient-{_rand_token()}"
            else:
                pseudo = f"{resource_type}-{_rand_token()}"
            self._real_to_pseudo[key] = pseudo
            self._pseudo_to_real[pseudo] = key
            return pseudo

    def resolve(self, pseudonym: str) -> str | None:
        """Pseudonym → real `ResourceType/uuid` (server-side only — never leaves the agent)."""
        with self._lock:
            return self._pseudo_to_real.get(pseudonym)

    def is_active_patient(self, real_id: str) -> bool:
        return real_id == self.active_patient_id

    def snapshot(self) -> dict:
        """Serialize the maps for persistence (resume feature).

        `acl_decision` is intentionally excluded — it must be re-probed on
        the next tool call, since panel/scopes can change between sessions.
        `created_at` is preserved so resume keeps the original session age.
        """
        with self._lock:
            return {
                "real_to_pseudo": dict(self._real_to_pseudo),
                "pseudo_to_real": dict(self._pseudo_to_real),
                "provider_letter_idx": self._provider_letter_idx,
                "created_at": self.created_at.isoformat(),
            }

    @classmethod
    def from_snapshot(
        cls,
        *,
        session_id: str,
        physician_user_id: str,
        active_patient_id: str,
        snapshot: dict,
    ) -> "PseudonymMap":
        created_raw = snapshot.get("created_at")
        try:
            created_at = (
                datetime.fromisoformat(created_raw)
                if created_raw
                else datetime.now(timezone.utc)
            )
        except (TypeError, ValueError):
            created_at = datetime.now(timezone.utc)
        m = cls(
            session_id=session_id,
            physician_user_id=physician_user_id,
            active_patient_id=active_patient_id,
            created_at=created_at,
        )
        m._real_to_pseudo = dict(snapshot.get("real_to_pseudo") or {})
        m._pseudo_to_real = dict(snapshot.get("pseudo_to_real") or {})
        m._provider_letter_idx = int(snapshot.get("provider_letter_idx") or 0)
        return m


class SessionStore:
    """In-memory session store keyed by session_id."""

    def __init__(self):
        self._map: dict[str, PseudonymMap] = {}
        self._lock = RLock()

    def create(self, session_id: str, physician_user_id: str, active_patient_id: str) -> PseudonymMap:
        with self._lock:
            session = PseudonymMap(
                session_id=session_id,
                physician_user_id=physician_user_id,
                active_patient_id=active_patient_id,
            )
            self._map[session_id] = session
            # Pre-allocate the patient pseudonym so it's stable across this session
            session.pseudo_for("Patient", active_patient_id)
            return session

    def get(self, session_id: str) -> PseudonymMap | None:
        with self._lock:
            return self._map.get(session_id)

    def end(self, session_id: str) -> None:
        with self._lock:
            self._map.pop(session_id, None)

    def rehydrate(
        self,
        *,
        session_id: str,
        physician_user_id: str,
        active_patient_id: str,
        snapshot: dict,
    ) -> PseudonymMap:
        """Rebuild the in-memory PseudonymMap from a persisted snapshot.

        Used by /v1/sessions/resume so prior conversation messages keep the
        same `Patient-A1B2`/`Provider-A` pseudonyms across the gap between
        sessions. Replaces any existing entry for `session_id`.
        """
        with self._lock:
            session = PseudonymMap.from_snapshot(
                session_id=session_id,
                physician_user_id=physician_user_id,
                active_patient_id=active_patient_id,
                snapshot=snapshot,
            )
            self._map[session_id] = session
            return session


sessions = SessionStore()
