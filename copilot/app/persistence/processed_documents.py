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


_SELECT_COLUMNS = (
    "patient_pseudonym, hash, canonical_doc_id, doc_type, "
    "extracted_facts, source_path, extracted_at, "
    "file_bytes, mime_type, "
    "confirmed_at, rejected_at, confirmed_by, external_doc_id"
)


def _row_to_doc(row: Any) -> "ProcessedDocument":
    """Inflate an aiosqlite.Row into a ``ProcessedDocument``.

    Centralized so adding a column only touches three places: the dataclass,
    the schema/migration in ``init()``, and ``_SELECT_COLUMNS`` above.
    """
    def _ts(key: str) -> datetime | None:
        raw = row[key]
        if not raw:
            return None
        try:
            return datetime.fromisoformat(raw)
        except (TypeError, ValueError):
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
        confirmed_at=_ts("confirmed_at"),
        rejected_at=_ts("rejected_at"),
        confirmed_by=row["confirmed_by"],
        external_doc_id=row["external_doc_id"],
    )


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
    # W2 confirm/reject UX (2026-05-08): physician explicitly accepts or
    # rejects a front-desk-uploaded intake after reviewing the bbox modal.
    # ``confirmed_at`` set ⇒ extraction is part of the patient record from
    # the agent's POV; ``rejected_at`` set ⇒ ignore in chat tool reads;
    # both null ⇒ pending review (the W2 LITE default).
    confirmed_at: datetime | None = None
    rejected_at: datetime | None = None
    confirmed_by: str | None = None
    # Plan B write-back: when the physician confirms a front-desk upload,
    # we POST the original PDF to OpenEMR's REST API and capture the
    # OpenEMR-side documents.id here. Allows future cross-reference and
    # surfaces in FHIR DocumentReference GET. NULL when the OpenEMR write
    # failed (the local confirm still applies — see
    # ``/v1/documents/{doc_id}/confirm``'s fail-soft path).
    external_doc_id: str | None = None


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
            for col_ddl in (
                "ADD COLUMN file_bytes BLOB",
                "ADD COLUMN mime_type TEXT",
                # W2 confirm/reject (2026-05-08)
                "ADD COLUMN confirmed_at TEXT",
                "ADD COLUMN rejected_at TEXT",
                "ADD COLUMN confirmed_by TEXT",
                "ADD COLUMN external_doc_id TEXT",
            ):
                try:
                    await db.execute(f"ALTER TABLE processed_documents {col_ddl}")
                except aiosqlite.OperationalError:
                    pass  # column already exists
            await db.commit()

    async def lookup(
        self, *, patient_pseudonym: str, hash: str
    ) -> ProcessedDocument | None:
        async with aiosqlite.connect(self._db_path) as db:
            db.row_factory = aiosqlite.Row
            cur = await db.execute(
                f"""
                SELECT {_SELECT_COLUMNS}
                  FROM processed_documents
                 WHERE patient_pseudonym = ? AND hash = ?
                """,
                (patient_pseudonym, hash),
            )
            row = await cur.fetchone()
        return _row_to_doc(row) if row is not None else None

    async def lookup_by_doc_id(
        self, *, patient_pseudonym: str, canonical_doc_id: str
    ) -> ProcessedDocument | None:
        async with aiosqlite.connect(self._db_path) as db:
            db.row_factory = aiosqlite.Row
            cur = await db.execute(
                f"""
                SELECT {_SELECT_COLUMNS}
                  FROM processed_documents
                 WHERE patient_pseudonym = ? AND canonical_doc_id = ?
                """,
                (patient_pseudonym, canonical_doc_id),
            )
            row = await cur.fetchone()
        return _row_to_doc(row) if row is not None else None

    async def list_recent_for_patient(
        self, *, patient_pseudonym: str, limit: int = 5
    ) -> list[ProcessedDocument]:
        """Return the patient's most recent processed documents, newest first."""
        async with aiosqlite.connect(self._db_path) as db:
            db.row_factory = aiosqlite.Row
            cur = await db.execute(
                f"""
                SELECT {_SELECT_COLUMNS}
                  FROM processed_documents
                 WHERE patient_pseudonym = ?
              ORDER BY extracted_at DESC
                 LIMIT ?
                """,
                (patient_pseudonym, limit),
            )
            rows = await cur.fetchall()
        return [_row_to_doc(r) for r in rows]

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

    async def list_pending_uploads(
        self, *, patient_pseudonym: str, since: datetime | None = None
    ) -> list[ProcessedDocument]:
        """Return rows representing front-desk uploads that haven't been
        extracted yet (W2 LITE deferred-extraction path).

        A pending row carries ``source_path = 'front_desk_scan'`` AND a
        ``"_pending": true`` marker inside ``extracted_facts``. The marker
        is dropped when the physician clicks the banner item and the
        ``/v1/documents/{doc_id}/process`` route runs VLM + writes the real
        extraction back via ``replace_extraction``.

        Optional ``since`` clamps to a recency window (matches the 7-day
        window the FHIR-side banner uses).
        """
        params: list[Any] = [patient_pseudonym]
        sql = (
            f"SELECT {_SELECT_COLUMNS} "
            "FROM processed_documents "
            "WHERE patient_pseudonym = ? AND source_path = 'front_desk_scan' "
        )
        if since is not None:
            sql += "AND extracted_at >= ? "
            params.append(since.isoformat())
        sql += "ORDER BY extracted_at DESC"

        async with aiosqlite.connect(self._db_path) as db:
            db.row_factory = aiosqlite.Row
            cur = await db.execute(sql, params)
            rows = await cur.fetchall()
        out: list[ProcessedDocument] = []
        for r in rows:
            facts = json.loads(r["extracted_facts"])
            # In-Python pending filter — keeps the SQL portable across
            # SQLite's limited JSON support.
            if not isinstance(facts, dict) or not facts.get("_pending"):
                continue
            out.append(_row_to_doc(r))
        return out

    async def replace_extraction(
        self,
        *,
        patient_pseudonym: str,
        canonical_doc_id: str,
        extracted_facts: dict[str, Any],
        doc_type: str | None = None,
    ) -> None:
        """Promote a pending row to extracted by overwriting ``extracted_facts``.

        Called by ``IngestionService.process_pending`` after VLM completes.
        ``doc_type`` updates the marker label (e.g., ``pending_intake`` →
        ``intake_form_doc``) when supplied.
        """
        async with aiosqlite.connect(self._db_path) as db:
            if doc_type is not None:
                await db.execute(
                    """
                    UPDATE processed_documents
                       SET extracted_facts = ?, doc_type = ?
                     WHERE patient_pseudonym = ? AND canonical_doc_id = ?
                    """,
                    (
                        json.dumps(extracted_facts, sort_keys=True),
                        doc_type,
                        patient_pseudonym,
                        canonical_doc_id,
                    ),
                )
            else:
                await db.execute(
                    """
                    UPDATE processed_documents
                       SET extracted_facts = ?
                     WHERE patient_pseudonym = ? AND canonical_doc_id = ?
                    """,
                    (
                        json.dumps(extracted_facts, sort_keys=True),
                        patient_pseudonym,
                        canonical_doc_id,
                    ),
                )
            await db.commit()

    async def mark_confirmed(
        self,
        *,
        patient_pseudonym: str,
        canonical_doc_id: str,
        confirmed_by: str,
        external_doc_id: str | None = None,
    ) -> None:
        """Stamp a row as physician-confirmed (W2 Plan B).

        Idempotent — re-confirming an already-confirmed row is a no-op
        on ``confirmed_at`` (we keep the original timestamp). The
        ``external_doc_id`` is updated when supplied so a successful
        retry of the OpenEMR write-back can fill it in.
        """
        now = datetime.now(timezone.utc).isoformat()
        async with aiosqlite.connect(self._db_path) as db:
            if external_doc_id is not None:
                await db.execute(
                    """
                    UPDATE processed_documents
                       SET confirmed_at = COALESCE(confirmed_at, ?),
                           confirmed_by = COALESCE(confirmed_by, ?),
                           external_doc_id = COALESCE(external_doc_id, ?)
                     WHERE patient_pseudonym = ? AND canonical_doc_id = ?
                    """,
                    (now, confirmed_by, external_doc_id,
                     patient_pseudonym, canonical_doc_id),
                )
            else:
                await db.execute(
                    """
                    UPDATE processed_documents
                       SET confirmed_at = COALESCE(confirmed_at, ?),
                           confirmed_by = COALESCE(confirmed_by, ?)
                     WHERE patient_pseudonym = ? AND canonical_doc_id = ?
                    """,
                    (now, confirmed_by, patient_pseudonym, canonical_doc_id),
                )
            await db.commit()

    async def mark_rejected(
        self,
        *,
        patient_pseudonym: str,
        canonical_doc_id: str,
        rejected_by: str,
    ) -> None:
        """Stamp a row as physician-rejected. Idempotent on ``rejected_at``."""
        now = datetime.now(timezone.utc).isoformat()
        async with aiosqlite.connect(self._db_path) as db:
            await db.execute(
                """
                UPDATE processed_documents
                   SET rejected_at = COALESCE(rejected_at, ?),
                       confirmed_by = COALESCE(confirmed_by, ?)
                 WHERE patient_pseudonym = ? AND canonical_doc_id = ?
                """,
                (now, rejected_by, patient_pseudonym, canonical_doc_id),
            )
            await db.commit()

    async def list_confirmed_recent(
        self, *, patient_pseudonym: str, since: datetime | None = None
    ) -> list[ProcessedDocument]:
        """Return rows the physician confirmed (W2 Plan B + 'what changed' tool).

        Excludes pending and rejected rows. Used by the
        ``get_recent_intake_extractions`` agent tool to answer "what's new
        for this patient" questions, and by the pending-intakes banner to
        surface a "[confirmed]" tag for recent confirmations.
        """
        params: list[Any] = [patient_pseudonym]
        sql = (
            f"SELECT {_SELECT_COLUMNS} "
            "FROM processed_documents "
            "WHERE patient_pseudonym = ? "
            "  AND confirmed_at IS NOT NULL "
            "  AND rejected_at IS NULL "
        )
        if since is not None:
            sql += "AND confirmed_at >= ? "
            params.append(since.isoformat())
        sql += "ORDER BY confirmed_at DESC"

        async with aiosqlite.connect(self._db_path) as db:
            db.row_factory = aiosqlite.Row
            cur = await db.execute(sql, params)
            rows = await cur.fetchall()
        return [_row_to_doc(r) for r in rows]
