"""F20 — Prewarm cache short-circuits run_tool on first turn.

Confirms two contracts:
  1. PseudonymMap.cache_put / cache_get round-trip with TTL expiry.
  2. run_tool() short-circuits to the cached result when present and
     marks ToolResult.cache_hit=True without touching FHIR.
"""
from __future__ import annotations

import time

import pytest

from app.acl.check import AclResult
from app.phi.session import sessions
from app.tools._base import ToolResult, run_tool


PATIENT_ID = "f47ac10b-58cc-4372-a567-0e02b2c3d479"


def test_cache_put_get_round_trip():
    sessions.end("prewarm-rt-1")
    s = sessions.create("prewarm-rt-1", "dr_alvarez", PATIENT_ID)
    payload = ToolResult(
        name="get_patient_summary",
        data=[{"k": "v"}],
        record_ids=["MedicationRequest/abc"],
        record_type="patient_summary",
    )
    s.cache_put("get_patient_summary", payload)
    fetched = s.cache_get("get_patient_summary")
    assert fetched is payload


def test_cache_get_returns_none_after_ttl():
    sessions.end("prewarm-rt-2")
    s = sessions.create("prewarm-rt-2", "dr_alvarez", PATIENT_ID)
    s.cache_put("get_patient_summary", ToolResult(name="x", data=[]))
    # Force the entry's timestamp into the past
    ts, result = s._tool_cache["get_patient_summary"]  # noqa: SLF001 — test
    s._tool_cache["get_patient_summary"] = (ts - 1000.0, result)  # noqa: SLF001
    assert s.cache_get("get_patient_summary", ttl_seconds=90.0) is None
    # Expired entry is evicted
    assert "get_patient_summary" not in s._tool_cache  # noqa: SLF001


def test_cache_get_unknown_tool_returns_none():
    sessions.end("prewarm-rt-3")
    s = sessions.create("prewarm-rt-3", "dr_alvarez", PATIENT_ID)
    assert s.cache_get("never_cached_tool") is None


@pytest.mark.asyncio
async def test_run_tool_short_circuits_on_cache_hit():
    """If a prewarm result is cached, run_tool returns it without
    invoking fetch/transform — proves the FHIR round-trip is skipped."""
    sessions.end("prewarm-rt-4")
    s = sessions.create("prewarm-rt-4", "dr_alvarez", PATIENT_ID)
    pre = ToolResult(
        name="get_patient_summary",
        data=[{"prewarmed": True}],
        record_ids=["MedicationRequest/seed"],
        record_type="patient_summary",
        acl_check=AclResult(allowed=True, section="patients", action="view"),
    )
    s.cache_put("get_patient_summary", pre)

    sentinel = "fetch should not run on cache hit"

    async def _fetch_must_not_run(*_a, **_kw):
        raise AssertionError(sentinel)

    def _transform_must_not_run(*_a, **_kw):
        raise AssertionError(sentinel)

    out = await run_tool(
        name="get_patient_summary",
        record_type="patient_summary",
        section="patients",
        action="view",
        fetch=_fetch_must_not_run,
        transform=_transform_must_not_run,
        fhir=None,  # type: ignore[arg-type] — never used on cache hit
        session=s,
    )

    assert out is pre
    assert out.cache_hit is True
    assert out.data == [{"prewarmed": True}]
