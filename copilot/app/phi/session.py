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


sessions = SessionStore()
