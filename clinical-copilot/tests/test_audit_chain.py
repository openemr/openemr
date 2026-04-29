"""Unit tests for the hash-chained AI audit log."""

from __future__ import annotations

from sidecar.audit.log import AuditEntry, InMemoryAuditLog, now_utc


def _entry(summary: str = "x") -> AuditEntry:
    return AuditEntry(
        occurred_at=now_utc(),
        user_id="u",
        patient_id="Patient/1",
        purpose_of_use="diagnostic_cross_check",
        model_name="mock",
        prompt_version="v1",
        prompt_token_count=0,
        completion_token_count=0,
        tool_calls=[],
        verifier_outcome="passed",
        response_summary=summary,
    )


def test_first_row_uses_zero_prev_hash() -> None:
    log = InMemoryAuditLog()
    stored = log.append(_entry())
    assert stored.prev_hash == b"\x00" * 32


def test_chain_links_correctly() -> None:
    log = InMemoryAuditLog()
    a = log.append(_entry("first"))
    b = log.append(_entry("second"))
    assert b.prev_hash == a.this_hash
    assert log.verify_chain()


def test_long_chain_verifies() -> None:
    log = InMemoryAuditLog()
    for i in range(50):
        log.append(_entry(f"row-{i}"))
    assert log.verify_chain()
