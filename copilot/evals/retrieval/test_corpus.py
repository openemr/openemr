"""BM25 corpus index — build, query, return well-formed chunks."""
from __future__ import annotations

import os
import tempfile

import pytest

from app.retrieval.corpus import GuidelineCorpus


@pytest.fixture
def corpus_path() -> str:
    here = os.path.dirname(os.path.dirname(os.path.dirname(__file__)))
    return os.path.join(here, "corpus", "guidelines.jsonl")


@pytest.fixture
async def corpus(corpus_path: str) -> GuidelineCorpus:
    fd, db = tempfile.mkstemp(suffix=".db")
    os.close(fd)
    c = GuidelineCorpus(jsonl_path=corpus_path, sqlite_path=db)
    await c.build()
    yield c
    os.unlink(db)


async def test_bm25_finds_statin_chunk_for_high_ldl_question(corpus: GuidelineCorpus):
    hits = await corpus.search("when should I start a statin for high LDL", top_k=5)
    chunk_ids = [h.chunk_id for h in hits]
    assert any("statin" in cid for cid in chunk_ids)


async def test_bm25_finds_a1c_threshold_for_diabetes_diagnosis(corpus: GuidelineCorpus):
    hits = await corpus.search("HbA1c threshold to diagnose diabetes", top_k=5)
    chunk_ids = [h.chunk_id for h in hits]
    assert any("a1c" in cid for cid in chunk_ids)


async def test_returned_chunk_has_url_and_section(corpus: GuidelineCorpus):
    hits = await corpus.search("blood pressure target", top_k=3)
    assert hits, "expected at least one hit for BP query"
    h = hits[0]
    assert h.url
    assert h.section
    assert h.text
