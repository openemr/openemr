"""Append-only hash-chained audit log.

Two backends ship: ``InMemoryAuditLog`` (used by tests and the eval suite)
and ``PostgresAuditLog`` (production). Both implement the same protocol.
The hash chain is SHA-256 over the canonical JSON form of each entry.

The chain head is anchored to a write-once external store (for example
AWS S3 with Object Lock) at a configurable cadence. The anchoring agent
is out of scope for this module; it reads the latest ``this_hash`` and
publishes it.
"""

from __future__ import annotations

import hashlib
import json
from abc import ABC, abstractmethod
from collections.abc import Iterator
from dataclasses import asdict, dataclass, field
from datetime import datetime, timezone
from typing import Any


@dataclass(frozen=True)
class AuditEntry:
    """One row in the AI audit log."""

    occurred_at: datetime
    user_id: str
    patient_id: str
    purpose_of_use: str
    model_name: str
    prompt_version: str
    prompt_token_count: int
    completion_token_count: int
    tool_calls: list[dict[str, Any]]
    verifier_outcome: str  # "passed" | "warned" | "blocked"
    response_summary: str  # redacted; never raw PHI

    def canonical_bytes(self) -> bytes:
        """Stable serialised form used as input to SHA-256."""
        payload = {
            "occurred_at": self.occurred_at.isoformat(),
            "user_id": self.user_id,
            "patient_id": self.patient_id,
            "purpose_of_use": self.purpose_of_use,
            "model_name": self.model_name,
            "prompt_version": self.prompt_version,
            "prompt_token_count": self.prompt_token_count,
            "completion_token_count": self.completion_token_count,
            "tool_calls": self.tool_calls,
            "verifier_outcome": self.verifier_outcome,
            "response_summary": self.response_summary,
        }
        return json.dumps(payload, sort_keys=True, separators=(",", ":")).encode("utf-8")


@dataclass
class StoredAuditEntry:
    entry: AuditEntry
    prev_hash: bytes
    this_hash: bytes
    id: int = 0


class AuditLog(ABC):
    """Storage protocol for the audit log."""

    @abstractmethod
    def append(self, entry: AuditEntry) -> StoredAuditEntry: ...

    @abstractmethod
    def head_hash(self) -> bytes: ...

    @abstractmethod
    def __iter__(self) -> Iterator[StoredAuditEntry]: ...

    @abstractmethod
    def verify_chain(self) -> bool:
        """Return True iff every stored row's prev_hash matches the previous this_hash."""


_GENESIS = b"\x00" * 32


class InMemoryAuditLog(AuditLog):
    """In-process audit log used by tests, evals, and dev mode.

    Production should use ``PostgresAuditLog`` (see schema.sql).
    """

    def __init__(self) -> None:
        self._rows: list[StoredAuditEntry] = []

    def head_hash(self) -> bytes:
        return self._rows[-1].this_hash if self._rows else _GENESIS

    def append(self, entry: AuditEntry) -> StoredAuditEntry:
        prev = self.head_hash()
        body = entry.canonical_bytes()
        this = hashlib.sha256(prev + body).digest()
        stored = StoredAuditEntry(
            entry=entry, prev_hash=prev, this_hash=this, id=len(self._rows) + 1
        )
        self._rows.append(stored)
        return stored

    def __iter__(self) -> Iterator[StoredAuditEntry]:
        return iter(self._rows)

    def verify_chain(self) -> bool:
        prev = _GENESIS
        for row in self._rows:
            if row.prev_hash != prev:
                return False
            expected = hashlib.sha256(prev + row.entry.canonical_bytes()).digest()
            if row.this_hash != expected:
                return False
            prev = row.this_hash
        return True


def make_redacted_summary(top_n_labels: list[str], verdict: str) -> str:
    """Return a non-PHI summary suitable for the audit log.

    Labels like "gout" or "type 2 diabetes" are clinical concepts, not
    PHI on their own; we keep the labels but strip any patient identifier.
    """
    bullet = ", ".join(top_n_labels[:5]) or "(no surfaced candidates)"
    return f"verdict={verdict}; top_candidates=[{bullet}]"


def now_utc() -> datetime:
    return datetime.now(tz=timezone.utc)


def entry_to_dict(stored: StoredAuditEntry) -> dict[str, Any]:
    out = asdict(stored.entry)
    out["occurred_at"] = stored.entry.occurred_at.isoformat()
    out["id"] = stored.id
    out["prev_hash"] = stored.prev_hash.hex()
    out["this_hash"] = stored.this_hash.hex()
    return out
