"""Tests for the ``intake_extractor`` and ``evidence_retriever`` worker nodes.

The workers only fire when their signal field is set; otherwise they
are no-ops that just append themselves to ``routing_path``. These tests
cover the no-op path and the active-dispatch path with patched
``dispatch`` functions to avoid hitting real FHIR / corpus.
"""
from __future__ import annotations

from typing import Any

import pytest

from app.graph.workers import evidence_retriever, intake_extractor
from app.tools._base import ToolResult


@pytest.mark.asyncio
async def test_intake_extractor_noop_when_no_pending_extraction() -> None:
    state: dict[str, Any] = {
        "fhir": object(),
        "session": object(),
        "routing_path": ["supervisor"],
        "tool_results": [],
    }
    delta = await intake_extractor.run(state)  # type: ignore[arg-type]

    assert delta["routing_path"] == ["supervisor", "intake_extractor"]
    # No tool_results modification on no-op path.
    assert "tool_results" not in delta


@pytest.mark.asyncio
async def test_intake_extractor_dispatches_when_pending_extraction_set(
    monkeypatch,
) -> None:
    captured_args: dict[str, Any] = {}

    async def fake_dispatch(name: str, args: dict[str, Any], fhir, session):
        captured_args["name"] = name
        captured_args["args"] = args
        return ToolResult(
            name="attach_and_extract",
            data=[{"record_id": "DocumentReference/d-1"}],
            record_ids=["DocumentReference/d-1"],
            record_type="DocumentReference",
        )

    monkeypatch.setattr(
        "app.graph.workers.intake_extractor.dispatch", fake_dispatch
    )
    state: dict[str, Any] = {
        "fhir": object(),
        "session": object(),
        "routing_path": ["supervisor"],
        "tool_results": [],
        "pending_extraction": {
            "file_bytes_b64": "ZmFrZQ==",
            "mime_type": "application/pdf",
            "doc_type": "lab_pdf",
        },
    }
    delta = await intake_extractor.run(state)  # type: ignore[arg-type]

    assert captured_args["name"] == "attach_and_extract"
    assert captured_args["args"]["doc_type"] == "lab_pdf"
    assert delta["routing_path"] == ["supervisor", "intake_extractor"]
    assert len(delta["tool_results"]) == 1
    assert delta["tool_results"][0]["tool"] == "attach_and_extract"
    # Signal was consumed.
    assert delta["pending_extraction"] is None


@pytest.mark.asyncio
async def test_evidence_retriever_noop_when_no_seed_query() -> None:
    state: dict[str, Any] = {
        "fhir": object(),
        "session": object(),
        "routing_path": ["supervisor"],
        "tool_results": [],
    }
    delta = await evidence_retriever.run(state)  # type: ignore[arg-type]

    assert delta["routing_path"] == ["supervisor", "evidence_retriever"]
    assert "tool_results" not in delta


@pytest.mark.asyncio
async def test_evidence_retriever_dispatches_when_seed_query_set(monkeypatch) -> None:
    captured_args: dict[str, Any] = {}

    async def fake_dispatch(name: str, args: dict[str, Any], fhir, session):
        captured_args["name"] = name
        captured_args["args"] = args
        return ToolResult(
            name="search_guidelines",
            data=[{"record_id": "Guideline/uspstf-statin-2022"}],
            record_ids=["Guideline/uspstf-statin-2022"],
            record_type="Guideline",
        )

    monkeypatch.setattr(
        "app.graph.workers.evidence_retriever.dispatch", fake_dispatch
    )
    state: dict[str, Any] = {
        "fhir": object(),
        "session": object(),
        "routing_path": ["supervisor"],
        "tool_results": [],
        "retrieval_seed_query": "USPSTF statin",
    }
    delta = await evidence_retriever.run(state)  # type: ignore[arg-type]

    assert captured_args["name"] == "search_guidelines"
    assert captured_args["args"] == {"query": "USPSTF statin"}
    assert delta["routing_path"] == ["supervisor", "evidence_retriever"]
    assert len(delta["tool_results"]) == 1
    assert delta["tool_results"][0]["tool"] == "search_guidelines"
    # Signal consumed.
    assert delta["retrieval_seed_query"] is None


@pytest.mark.asyncio
async def test_evidence_retriever_writes_reranked_order_into_tool_results(
    monkeypatch,
) -> None:
    """W2 KR1 round-2 fix (codex P2): a real reranker must reorder the
    seed evidence the LLM sees — not just the trace observability fields.
    """
    async def fake_dispatch(name: str, args: dict[str, Any], fhir, session):
        return ToolResult(
            name="search_guidelines",
            data=[
                {"record_id": "Guideline/A", "chunk_id": "A", "text": "first"},
                {"record_id": "Guideline/B", "chunk_id": "B", "text": "second"},
                {"record_id": "Guideline/C", "chunk_id": "C", "text": "third"},
            ],
            record_ids=["Guideline/A", "Guideline/B", "Guideline/C"],
            record_type="Guideline",
        )

    class _ReverseReranker:
        name = "reverse-test"

        def rerank(self, query: str, hits: list[Any], *, top_k=None):
            rev = list(reversed(hits))
            return rev, [3.0, 2.0, 1.0]

    monkeypatch.setattr(
        "app.graph.workers.evidence_retriever.dispatch", fake_dispatch
    )
    monkeypatch.setattr(
        "app.graph.workers.evidence_retriever.get_reranker",
        lambda: _ReverseReranker(),
    )
    state: dict[str, Any] = {
        "fhir": object(),
        "session": object(),
        "routing_path": ["supervisor"],
        "tool_results": [],
        "retrieval_seed_query": "anything",
    }
    delta = await evidence_retriever.run(state)  # type: ignore[arg-type]

    # The tool_result the LLM seeds from must reflect the reranker's order
    # (C, B, A) — NOT the BM25 order (A, B, C).
    appended = delta["tool_results"][0]
    assert [item["chunk_id"] for item in appended["data"]] == ["C", "B", "A"]
    # Trace fields agree.
    assert delta["retrieval_hit_ids"] == ["C", "B", "A"]
    assert delta["rerank_scores"] == [3.0, 2.0, 1.0]


@pytest.mark.asyncio
async def test_evidence_retriever_skips_seed_when_dispatch_raises(
    monkeypatch,
) -> None:
    """W2 KR1 round-6 fix (codex P2): a search_guidelines failure
    (e.g. SQLite FTS5 syntax error on hyphenated query) must NOT escape
    and 500 the chat. The pre-fetch is skipped, the supervisor consumes
    the signal, and the answer_composer can still call the tool itself
    via its tool-use loop on a sanitized query.
    """
    async def raising_dispatch(name, args, fhir, session):
        raise RuntimeError("FTS5 boom: invalid syntax 'type-2'")

    monkeypatch.setattr(
        "app.graph.workers.evidence_retriever.dispatch", raising_dispatch
    )
    state: dict[str, Any] = {
        "fhir": object(),
        "session": object(),
        "routing_path": ["supervisor"],
        "tool_results": [],
        "retrieval_seed_query": "type-2 diabetes guideline",
    }
    delta = await evidence_retriever.run(state)  # type: ignore[arg-type]

    # Routing recorded; signal consumed; no tool_result added.
    assert delta["routing_path"] == ["supervisor", "evidence_retriever"]
    assert delta["retrieval_seed_query"] is None
    assert "tool_results" not in delta or delta["tool_results"] == []
    assert "retrieval_hit_ids" not in delta


@pytest.mark.asyncio
async def test_evidence_retriever_falls_back_to_bm25_when_rerank_raises(
    monkeypatch,
) -> None:
    """If the reranker raises, the worker still emits the BM25-ordered
    seed so the LLM has SOMETHING to cite. Trace's rerank_scores stays
    empty (signaling no rerank ran).
    """
    async def fake_dispatch(name, args, fhir, session):
        return ToolResult(
            name="search_guidelines",
            data=[
                {"record_id": "Guideline/A", "chunk_id": "A"},
                {"record_id": "Guideline/B", "chunk_id": "B"},
            ],
            record_ids=["Guideline/A", "Guideline/B"],
            record_type="Guideline",
        )

    class _BrokenReranker:
        name = "broken"

        def rerank(self, query, hits, *, top_k=None):
            raise RuntimeError("reranker exploded")

    monkeypatch.setattr(
        "app.graph.workers.evidence_retriever.dispatch", fake_dispatch
    )
    monkeypatch.setattr(
        "app.graph.workers.evidence_retriever.get_reranker",
        lambda: _BrokenReranker(),
    )
    state: dict[str, Any] = {
        "fhir": object(),
        "session": object(),
        "routing_path": ["supervisor"],
        "tool_results": [],
        "retrieval_seed_query": "anything",
    }
    delta = await evidence_retriever.run(state)  # type: ignore[arg-type]

    # The seed is still emitted (BM25 order kept).
    assert len(delta["tool_results"]) == 1
    assert [item["chunk_id"] for item in delta["tool_results"][0]["data"]] == ["A", "B"]
    # rerank_scores is empty — the rerank didn't actually run.
    assert "rerank_scores" not in delta or delta["rerank_scores"] == []
