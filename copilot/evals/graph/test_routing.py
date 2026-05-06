"""Tests for the plain-Python supervisor + ``decide_next`` routing logic.

These are pure-function tests over the routing decision; they do NOT
build the full graph (that's covered by test_terminal_only.py and the
worker tests).
"""
from __future__ import annotations

from app.graph.supervisor import decide_next


def test_decide_next_routes_to_intake_when_pending_extraction_set() -> None:
    state = {
        "pending_extraction": {"file_bytes_b64": "...", "doc_type": "lab_pdf"},
        "routing_path": ["supervisor"],
    }
    assert decide_next(state) == "intake_extractor"  # type: ignore[arg-type]


def test_decide_next_does_not_re_route_to_intake_after_it_ran() -> None:
    state = {
        "pending_extraction": {"file_bytes_b64": "...", "doc_type": "lab_pdf"},
        "routing_path": ["supervisor", "intake_extractor", "supervisor"],
    }
    # intake_extractor already in routing_path — fall through to next signal,
    # which is missing, so go to answer_composer.
    assert decide_next(state) == "answer_composer"  # type: ignore[arg-type]


def test_decide_next_routes_to_evidence_when_seed_query_set() -> None:
    state = {
        "retrieval_seed_query": "USPSTF statin recommendation",
        "routing_path": ["supervisor"],
    }
    assert decide_next(state) == "evidence_retriever"  # type: ignore[arg-type]


def test_decide_next_does_not_re_route_to_evidence_after_it_ran() -> None:
    state = {
        "retrieval_seed_query": "USPSTF statin recommendation",
        "routing_path": ["supervisor", "evidence_retriever", "supervisor"],
    }
    assert decide_next(state) == "answer_composer"  # type: ignore[arg-type]


def test_decide_next_priority_order_intake_then_retrieval_then_compose() -> None:
    """If both signals are set on the same supervisor tick, intake fires first."""
    state = {
        "pending_extraction": {"file_bytes_b64": "..."},
        "retrieval_seed_query": "guideline",
        "routing_path": ["supervisor"],
    }
    assert decide_next(state) == "intake_extractor"  # type: ignore[arg-type]


def test_decide_next_falls_through_to_compose_when_no_signals() -> None:
    state = {"routing_path": ["supervisor"]}
    assert decide_next(state) == "answer_composer"  # type: ignore[arg-type]


def test_decide_next_handles_empty_state_defensively() -> None:
    state = {}  # no routing_path key at all
    assert decide_next(state) == "answer_composer"  # type: ignore[arg-type]
