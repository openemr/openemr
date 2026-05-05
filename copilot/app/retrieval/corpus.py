"""SQLite + FTS5 corpus reader — BM25 only for the MVP."""
from __future__ import annotations

import json
from dataclasses import dataclass
from datetime import date
from pathlib import Path

import aiosqlite


@dataclass(frozen=True)
class GuidelineHit:
    chunk_id: str
    source: str
    section: str
    text: str
    url: str
    last_updated: date | None
    score: float


_SCHEMA = """
CREATE VIRTUAL TABLE IF NOT EXISTS guidelines USING fts5(
  chunk_id UNINDEXED,
  source UNINDEXED,
  section,
  text,
  url UNINDEXED,
  last_updated UNINDEXED,
  tokenize = 'porter unicode61'
);
"""


class GuidelineCorpus:
    def __init__(self, *, jsonl_path: str | Path, sqlite_path: str) -> None:
        self._jsonl = Path(jsonl_path)
        self._db = sqlite_path

    async def build(self) -> None:
        async with aiosqlite.connect(self._db) as db:
            await db.executescript(_SCHEMA)
            await db.execute("DELETE FROM guidelines")  # idempotent rebuilds
            for line in self._jsonl.read_text().splitlines():
                if not line.strip():
                    continue
                row = json.loads(line)
                await db.execute(
                    """
                    INSERT INTO guidelines
                      (chunk_id, source, section, text, url, last_updated)
                    VALUES (?, ?, ?, ?, ?, ?)
                    """,
                    (
                        row["chunk_id"],
                        row["source"],
                        row["section"],
                        row["text"],
                        row["url"],
                        row.get("last_updated", ""),
                    ),
                )
            await db.commit()

    async def search(self, query: str, *, top_k: int = 5) -> list[GuidelineHit]:
        # FTS5 BM25: lower score = better. We negate so higher = better.
        sql = """
            SELECT chunk_id, source, section, text, url, last_updated,
                   bm25(guidelines) AS score
              FROM guidelines
             WHERE guidelines MATCH ?
          ORDER BY score
             LIMIT ?
        """
        async with aiosqlite.connect(self._db) as db:
            db.row_factory = aiosqlite.Row
            cur = await db.execute(sql, (_fts_query(query), top_k))
            rows = await cur.fetchall()

        hits: list[GuidelineHit] = []
        for r in rows:
            try:
                lu = date.fromisoformat(r["last_updated"]) if r["last_updated"] else None
            except ValueError:
                lu = None
            hits.append(
                GuidelineHit(
                    chunk_id=r["chunk_id"],
                    source=r["source"],
                    section=r["section"],
                    text=r["text"],
                    url=r["url"],
                    last_updated=lu,
                    score=-r["score"],
                )
            )
        return hits


def _fts_query(query: str) -> str:
    """Sanitize free-text into an FTS5 MATCH expression — strip punctuation,
    OR-join the surviving tokens. Keeps recall up on short clinical queries."""
    keep = []
    for tok in query.split():
        cleaned = "".join(ch for ch in tok if ch.isalnum() or ch == "-")
        if cleaned and len(cleaned) > 1:
            keep.append(cleaned)
    if not keep:
        return query.strip() or '""'
    return " OR ".join(keep)
