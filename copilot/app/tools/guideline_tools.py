"""search_guidelines — BM25 retrieval over the seed corpus, MVP scope."""
from __future__ import annotations

from typing import Any

from app.ingestion.schemas import encode_record_id_for_guideline
from app.retrieval.corpus import GuidelineCorpus
from app.tools._base import ToolResult


SCHEMA: dict[str, Any] = {
    "name": "search_guidelines",
    "description": (
        "Search the clinical guideline corpus for evidence relevant to a "
        "specific clinical question. Returns up to 5 chunks; cite "
        "Guideline/{chunk_id} in any recommendation derived from the result."
    ),
    "input_schema": {
        "type": "object",
        "properties": {
            "query": {"type": "string"},
            "top_k": {"type": "integer", "minimum": 1, "maximum": 10, "default": 5},
        },
        "required": ["query"],
    },
}


async def run_search_guidelines(
    *, corpus: GuidelineCorpus, args: dict[str, Any]
) -> ToolResult:
    query = args["query"]
    top_k = int(args.get("top_k") or 5)
    hits = await corpus.search(query, top_k=top_k)
    record_ids = [encode_record_id_for_guideline(chunk_id=h.chunk_id) for h in hits]
    payload = [
        {
            "chunk_id": h.chunk_id,
            "source": h.source,
            "section": h.section,
            "text": h.text,
            "url": h.url,
            "last_updated": h.last_updated.isoformat() if h.last_updated else None,
            "record_id": encode_record_id_for_guideline(chunk_id=h.chunk_id),
            "score": h.score,
        }
        for h in hits
    ]
    return ToolResult(
        name="search_guidelines",
        record_type="Guideline",
        data=payload,
        record_ids=record_ids,
    )
