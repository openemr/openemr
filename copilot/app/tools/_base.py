"""Shared 5-step pattern that every tool follows (ARCHITECTURE §3.2).

Each tool's only responsibility is the FHIR query (step 3). Steps 1, 2, 4, 5
live here so the pattern is uniform and provably consistent across tools —
this is what makes the verification gate's record_id model work.
"""
from __future__ import annotations

import time
from dataclasses import dataclass, field
from typing import Any, Callable, Awaitable

from app.acl.check import AclResult, acl_check
from app.fhir.client import FhirClient, FhirError
from app.phi.session import PseudonymMap


@dataclass
class ToolResult:
    """What every tool returns. The `record_ids` field is the verification anchor set."""

    name: str
    data: list[dict[str, Any]] | dict[str, Any]
    record_ids: list[str] = field(default_factory=list)
    record_type: str = ""
    duration_ms: float = 0.0
    acl_check: AclResult | None = None
    error: str | None = None

    def to_dict(self) -> dict[str, Any]:
        return {
            "tool": self.name,
            "record_type": self.record_type,
            "data": self.data,
            "record_ids": self.record_ids,
            "error": self.error,
        }


FetchFn = Callable[[FhirClient, str], Awaitable[list[dict[str, Any]]]]
TransformFn = Callable[[list[dict[str, Any]], PseudonymMap], list[dict[str, Any]]]


async def run_tool(
    *,
    name: str,
    record_type: str,
    section: str,
    action: str,
    fetch: FetchFn,
    transform: TransformFn,
    fhir: FhirClient,
    session: PseudonymMap,
) -> ToolResult:
    """Run a tool through the 5-step pattern.

    Steps:
      1. (caller passes pseudonym → real id is in `session.active_patient_id`)
      2. ACL check (deny early if obviously lacking scope)
      3. FHIR fetch (delegated)
      4. Strip PHI / transform (delegated)
      5. Return ToolResult with record_ids
    """
    started = time.perf_counter()
    user = session.physician_user_id
    acl = acl_check(user, section, action)
    if not acl.allowed:
        return ToolResult(
            name=name,
            data=[],
            record_ids=[],
            record_type=record_type,
            duration_ms=(time.perf_counter() - started) * 1000,
            acl_check=acl,
            error=f"acl_denied: {acl.reason}",
        )
    try:
        raw = await fetch(fhir, session.active_patient_id)
    except FhirError as e:
        return ToolResult(
            name=name,
            data=[],
            record_ids=[],
            record_type=record_type,
            duration_ms=(time.perf_counter() - started) * 1000,
            acl_check=acl,
            error=f"fhir_error: {e}",
        )
    transformed = transform(raw, session)
    record_ids = [r["record_id"] for r in transformed if "record_id" in r]
    return ToolResult(
        name=name,
        data=transformed,
        record_ids=record_ids,
        record_type=record_type,
        duration_ms=(time.perf_counter() - started) * 1000,
        acl_check=acl,
    )


def bundle_entries(bundle: dict[str, Any]) -> list[dict[str, Any]]:
    """Extract resource dicts from a FHIR searchset Bundle."""
    return [e["resource"] for e in (bundle.get("entry") or []) if "resource" in e]
