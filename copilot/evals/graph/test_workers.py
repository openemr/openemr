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
