"""SQLite-backed conversation history for the resume feature.

Stores `(physician_user_id, active_patient_id) -> conversation -> messages` so
the iframe can ask "Resume previous chat?" when the same physician reopens the
same patient's dashboard.

Design choices:

- **SQLite via aiosqlite** — single-file, no extra service. Matches the MVP
  scope (one Railway replica per ARCHITECTURE §10.1). The file is mounted on a
  Railway volume so it survives container restarts.
- **PHI posture** — physician-typed questions can contain free-text PHI; agent
  prose uses pseudonyms (per §3.3). The DB file is treated as a clinical record
  with the same retention/encryption posture as OpenEMR encounter notes. It is
  *never* sent to Langfuse — see `app/observability/trace.py` which logs only
  the PHI-screened question text.
- **Pseudonym continuity** — the `PseudonymMap` snapshot is persisted alongside
  each conversation so a resumed session reuses the same `Patient-A1B2` /
  `Provider-A` tokens. Without this, replayed prior messages would reference
  pseudonyms the new session no longer knows.
- **Resume window** — a row is "resumable" if `ended_at IS NULL` and
  `last_used_at >= now - resume_window_hours`. Older = abandoned.
"""
from __future__ import annotations

import json
import logging
import os
import uuid
from dataclasses import dataclass
from datetime import datetime, timedelta, timezone
from typing import Any

import aiosqlite

logger = logging.getLogger("copilot.persistence")


_SCHEMA = """
CREATE TABLE IF NOT EXISTS conversations (
  conversation_id     TEXT PRIMARY KEY,
  physician_user_id   TEXT NOT NULL,
  active_patient_id   TEXT NOT NULL,
  patient_pseudonym   TEXT NOT NULL,
  pseudonym_map_json  TEXT NOT NULL,
  created_at          TEXT NOT NULL,
  last_used_at        TEXT NOT NULL,
  ended_at            TEXT,
  turn_count          INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_conv_phys_pat_active
  ON conversations(physician_user_id, active_patient_id, ended_at, last_used_at DESC);

CREATE TABLE IF NOT EXISTS messages (
  message_id        TEXT PRIMARY KEY,
  conversation_id   TEXT NOT NULL REFERENCES conversations(conversation_id) ON DELETE CASCADE,
  turn_index        INTEGER NOT NULL,
  role              TEXT NOT NULL CHECK (role IN ('user','assistant')),
  content           TEXT NOT NULL,
  claims_json       TEXT,
  data_gaps_json    TEXT,
  created_at        TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_msg_conv_turn ON messages(conversation_id, turn_index);
"""


def _now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


@dataclass
class RecentConversation:
    conversation_id: str
    last_used_at: str
    turn_count: int
    patient_pseudonym: str


@dataclass
class ConversationRow:
    conversation_id: str
    physician_user_id: str
    active_patient_id: str
    patient_pseudonym: str
    pseudonym_map: dict
    turn_count: int


@dataclass
class StoredMessage:
    role: str
    content: str
    claims: list[dict] | None
    data_gaps: list[str] | None


class ConversationStore:
    """Async SQLite store. Single-writer; aiosqlite serializes via its loop."""

    def __init__(self, db_path: str):
        self._db_path = db_path

    async def init(self) -> None:
        # Ensure parent directory exists (Railway volume may be empty).
        parent = os.path.dirname(self._db_path) or "."
        try:
            os.makedirs(parent, exist_ok=True)
        except OSError as e:
            logger.warning("could not create db parent dir %s: %s", parent, e)
        async with aiosqlite.connect(self._db_path) as db:
            await db.executescript(_SCHEMA)
            await db.commit()
        # Tighten file mode — clinical record posture (§5.3 split logging).
        try:
            os.chmod(self._db_path, 0o600)
        except OSError:
            pass

    async def create(
        self,
        *,
        conversation_id: str,
        physician_user_id: str,
        active_patient_id: str,
        patient_pseudonym: str,
        pseudonym_map: dict,
    ) -> None:
        now = _now_iso()
        async with aiosqlite.connect(self._db_path) as db:
            await db.execute(
                """
                INSERT INTO conversations (
                  conversation_id, physician_user_id, active_patient_id,
                  patient_pseudonym, pseudonym_map_json,
                  created_at, last_used_at, ended_at, turn_count
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NULL, 0)
                """,
                (
                    conversation_id,
                    physician_user_id,
                    active_patient_id,
                    patient_pseudonym,
                    json.dumps(pseudonym_map),
                    now,
                    now,
                ),
            )
            await db.commit()

    async def find_recent(
        self,
        *,
        physician_user_id: str,
        active_patient_id: str,
        window_hours: int,
    ) -> RecentConversation | None:
        cutoff = (
            datetime.now(timezone.utc) - timedelta(hours=window_hours)
        ).isoformat()
        async with aiosqlite.connect(self._db_path) as db:
            db.row_factory = aiosqlite.Row
            async with db.execute(
                """
                SELECT conversation_id, last_used_at, turn_count, patient_pseudonym
                FROM conversations
                WHERE physician_user_id = ?
                  AND active_patient_id = ?
                  AND ended_at IS NULL
                  AND last_used_at >= ?
                ORDER BY last_used_at DESC
                LIMIT 1
                """,
                (physician_user_id, active_patient_id, cutoff),
            ) as cur:
                row = await cur.fetchone()
        if row is None:
            return None
        return RecentConversation(
            conversation_id=row["conversation_id"],
            last_used_at=row["last_used_at"],
            turn_count=row["turn_count"],
            patient_pseudonym=row["patient_pseudonym"],
        )

    async def get(self, conversation_id: str) -> ConversationRow | None:
        async with aiosqlite.connect(self._db_path) as db:
            db.row_factory = aiosqlite.Row
            async with db.execute(
                """
                SELECT conversation_id, physician_user_id, active_patient_id,
                       patient_pseudonym, pseudonym_map_json, turn_count
                FROM conversations WHERE conversation_id = ?
                """,
                (conversation_id,),
            ) as cur:
                row = await cur.fetchone()
        if row is None:
            return None
        try:
            pmap = json.loads(row["pseudonym_map_json"]) or {}
        except json.JSONDecodeError:
            pmap = {}
        return ConversationRow(
            conversation_id=row["conversation_id"],
            physician_user_id=row["physician_user_id"],
            active_patient_id=row["active_patient_id"],
            patient_pseudonym=row["patient_pseudonym"],
            pseudonym_map=pmap,
            turn_count=row["turn_count"],
        )

    async def get_messages(self, conversation_id: str) -> list[StoredMessage]:
        async with aiosqlite.connect(self._db_path) as db:
            db.row_factory = aiosqlite.Row
            async with db.execute(
                """
                SELECT role, content, claims_json, data_gaps_json
                FROM messages
                WHERE conversation_id = ?
                ORDER BY turn_index ASC, rowid ASC
                """,
                (conversation_id,),
            ) as cur:
                rows = await cur.fetchall()
        out: list[StoredMessage] = []
        for r in rows:
            claims = None
            gaps = None
            if r["claims_json"]:
                try:
                    claims = json.loads(r["claims_json"])
                except json.JSONDecodeError:
                    claims = None
            if r["data_gaps_json"]:
                try:
                    gaps = json.loads(r["data_gaps_json"])
                except json.JSONDecodeError:
                    gaps = None
            out.append(
                StoredMessage(
                    role=r["role"],
                    content=r["content"],
                    claims=claims,
                    data_gaps=gaps,
                )
            )
        return out

    async def append_turn(
        self,
        *,
        conversation_id: str,
        question: str,
        assistant_prose: str,
        claims: list[dict] | None,
        data_gaps: list[str] | None,
        pseudonym_map: dict,
    ) -> None:
        """Append one (user, assistant) turn and refresh pseudonym snapshot.

        Same transaction so a crash never leaves a half-turn on disk.
        """
        now = _now_iso()
        async with aiosqlite.connect(self._db_path) as db:
            db.row_factory = aiosqlite.Row
            async with db.execute(
                "SELECT turn_count FROM conversations WHERE conversation_id = ?",
                (conversation_id,),
            ) as cur:
                row = await cur.fetchone()
            if row is None:
                raise KeyError(f"conversation_id {conversation_id} not found")
            next_turn = int(row["turn_count"])

            await db.execute(
                """
                INSERT INTO messages (message_id, conversation_id, turn_index, role,
                                      content, claims_json, data_gaps_json, created_at)
                VALUES (?, ?, ?, 'user', ?, NULL, NULL, ?)
                """,
                (str(uuid.uuid4()), conversation_id, next_turn, question, now),
            )
            await db.execute(
                """
                INSERT INTO messages (message_id, conversation_id, turn_index, role,
                                      content, claims_json, data_gaps_json, created_at)
                VALUES (?, ?, ?, 'assistant', ?, ?, ?, ?)
                """,
                (
                    str(uuid.uuid4()),
                    conversation_id,
                    next_turn,
                    assistant_prose,
                    json.dumps(claims) if claims else None,
                    json.dumps(data_gaps) if data_gaps else None,
                    now,
                ),
            )
            await db.execute(
                """
                UPDATE conversations
                SET turn_count = turn_count + 1,
                    last_used_at = ?,
                    pseudonym_map_json = ?
                WHERE conversation_id = ?
                """,
                (now, json.dumps(pseudonym_map), conversation_id),
            )
            await db.commit()

    async def end(self, conversation_id: str) -> None:
        async with aiosqlite.connect(self._db_path) as db:
            await db.execute(
                "UPDATE conversations SET ended_at = ? WHERE conversation_id = ? AND ended_at IS NULL",
                (_now_iso(), conversation_id),
            )
            await db.commit()

    async def touch(self, conversation_id: str) -> None:
        """Bump last_used_at without adding a turn (used on resume)."""
        async with aiosqlite.connect(self._db_path) as db:
            await db.execute(
                "UPDATE conversations SET last_used_at = ? WHERE conversation_id = ?",
                (_now_iso(), conversation_id),
            )
            await db.commit()
