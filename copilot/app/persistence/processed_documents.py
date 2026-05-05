"""SHA3-512 dedup table for ingested PDFs.

Closes the gap that `/v1/documents/attach` only dedupes within its own route
while front-desk uploads via OpenEMR's stock Documents Zend module bypass it.

Both paths converge here: the HTTP route hashes the bytes before persisting,
and the supervisor's `pending_intake_sources(pid)` (Week 2) hashes each
candidate `DocumentReference`'s binary the first time it sees the doc. On a
hash hit we skip extraction and treat the new `DocumentReference.id` as a
pointer at the canonical extraction.

SHA3-512 chosen to match the hash OpenEMR's `Document::createDocument()`
already populates in `documents.hash` (`library/classes/Document.class.php:1121`,
`hash('sha3-512', $data)`). Aligning algorithms means Co-Pilot can in
principle cross-reference OpenEMR's own column once an inspection path is
available; the table here is the system-wide source of truth in the meantime.

Patient pseudonym is the lookup key, not the real OpenEMR UUID, so the table
is consistent with the rest of the Co-Pilot's PHI posture (`app/phi/session.py`).
"""
from __future__ import annotations

import hashlib
import json
import logging
from dataclasses import dataclass
from datetime import datetime, timezone
from typing import Any

import aiosqlite

logger = logging.getLogger("copilot.persistence.processed_documents")


_SCHEMA = """
CREATE TABLE IF NOT EXISTS processed_documents (
  patient_pseudonym   TEXT NOT NULL,
  hash                TEXT NOT NULL,
  canonical_doc_id    TEXT NOT NULL,
  doc_type            TEXT NOT NULL,
  extracted_facts     TEXT NOT NULL,
  source_path         TEXT NOT NULL CHECK (source_path IN ('attach_route', 'front_desk_scan')),
  extracted_at        TEXT NOT NULL,
  file_bytes          BLOB,
  mime_type           TEXT,
  PRIMARY KEY (patient_pseudonym, hash)
);

CREATE INDEX IF NOT EXISTS idx_proc_doc_canonical
  ON processed_documents(canonical_doc_id);
"""


def hash_bytes(data: bytes) -> str:
    """SHA3-512 hex digest — matches OpenEMR's `documents.hash` algorithm."""
    return hashlib.sha3_512(data).hexdigest()


@dataclass(frozen=True)
class ProcessedDocument:
    patient_pseudonym: str
    hash: str
    canonical_doc_id: str
    doc_type: str
    extracted_facts: dict[str, Any]
    source_path: str
    extracted_at: datetime
    file_bytes: bytes | None = None
    mime_type: str | None = None


class ProcessedDocumentStore:
    """Async SQLite store for the dedup table.

    Mirrors the open/close pattern of `ConversationStore` so wiring into the
    FastAPI lifespan is mechanical.
    """

    def __init__(self, db_path: str):
        self._db_path = db_path

    async def init(self) -> None:
        async with aiosqlite.connect(self._db_path) as db:
            await db.executescript(_SCHEMA)
            # Idempotent column adds for migrating existing DBs.
            try:
                await db.execute("ALTER TABLE processed_documents ADD COLUMN file_bytes BLOB")
            except aiosqlite.OperationalError:
                pass  # column already exists
            try:
                await db.execute("ALTER TABLE processed_documents ADD COLUMN mime_type TEXT")
            except aiosqlite.OperationalError:
                pass  # column already exists
            await db.commit()

    async def lookup(
        self, *, patient_pseudonym: str, hash: str
    ) -> ProcessedDocument | None:
        async with aiosqlite.connect(self._db_path) as db:
            db.row_factory = aiosqlite.Row
            cur = await db.execute(
                """
                SELECT patient_pseudonym, hash, canonical_doc_id, doc_type,
                       extracted_facts, source_path, extracted_at,
                       file_bytes, mime_type
                  FROM processed_documents
                 WHERE patient_pseudonym = ? AND hash = ?
                """,
                (patient_pseudonym, hash),
            )
            row = await cur.fetchone()
        if row is None:
            return None
        return ProcessedDocument(
            patient_pseudonym=row["patient_pseudonym"],
            hash=row["hash"],
            canonical_doc_id=row["canonical_doc_id"],
            doc_type=row["doc_type"],
            extracted_facts=json.loads(row["extracted_facts"]),
            source_path=row["source_path"],
            extracted_at=datetime.fromisoformat(row["extracted_at"]),
            file_bytes=row["file_bytes"],
            mime_type=row["mime_type"],
        )

    async def lookup_by_doc_id(
        self, *, patient_pseudonym: str, canonical_doc_id: str
    ) -> ProcessedDocument | None:
        async with aiosqlite.connect(self._db_path) as db:
            db.row_factory = aiosqlite.Row
            cur = await db.execute(
                """
                SELECT patient_pseudonym, hash, canonical_doc_id, doc_type,
                       extracted_facts, source_path, extracted_at,
                       file_bytes, mime_type
                  FROM processed_documents
                 WHERE patient_pseudonym = ? AND canonical_doc_id = ?
                """,
                (patient_pseudonym, canonical_doc_id),
            )
            row = await cur.fetchone()
        if row is None:
            return None
        return ProcessedDocument(
            patient_pseudonym=row["patient_pseudonym"],
            hash=row["hash"],
            canonical_doc_id=row["canonical_doc_id"],
            doc_type=row["doc_type"],
            extracted_facts=json.loads(row["extracted_facts"]),
            source_path=row["source_path"],
            extracted_at=datetime.fromisoformat(row["extracted_at"]),
            file_bytes=row["file_bytes"],
            mime_type=row["mime_type"],
        )

    async def record(
        self,
        *,
        patient_pseudonym: str,
        hash: str,
        canonical_doc_id: str,
        doc_type: str,
        extracted_facts: dict[str, Any],
        source_path: str,
        file_bytes: bytes | None = None,
        mime_type: str | None = None,
    ) -> None:
        if source_path not in ("attach_route", "front_desk_scan"):
            raise ValueError(f"unknown source_path: {source_path!r}")
        async with aiosqlite.connect(self._db_path) as db:
            await db.execute(
                """
                INSERT OR IGNORE INTO processed_documents
                  (patient_pseudonym, hash, canonical_doc_id, doc_type,
                   extracted_facts, source_path, extracted_at,
                   file_bytes, mime_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                """,
                (
                    patient_pseudonym,
                    hash,
                    canonical_doc_id,
                    doc_type,
                    json.dumps(extracted_facts, sort_keys=True),
                    source_path,
                    datetime.now(timezone.utc).isoformat(),
                    file_bytes,
                    mime_type,
                ),
            )
            await db.commit()
