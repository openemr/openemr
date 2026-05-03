"""Shared 5-step pattern that every tool follows (ARCHITECTURE §3.2).

Each tool's only responsibility is the FHIR query (step 3). Steps 1, 2, 4, 5
live here so the pattern is uniform and provably consistent across tools —
this is what makes the verification gate's record_id model work.
"""
from __future__ import annotations

import logging
import time
from dataclasses import dataclass, field
from typing import Any, Callable, Awaitable

from app.acl.check import AclResult, acl_check
from app.fhir.client import FhirClient, FhirError
from app.phi.session import PseudonymMap

logger = logging.getLogger("copilot.tools._base")


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
    # True when this result was returned from the session prewarm cache
    # (app.agent.prewarm) instead of a fresh FHIR fetch. Surfaces in
    # logs + Langfuse so first-turn cache savings are observable.
    cache_hit: bool = False

    def to_dict(self) -> dict[str, Any]:
        return {
            "tool": self.name,
            "record_type": self.record_type,
            "data": self.data,
            "record_ids": self.record_ids,
            "error": self.error,
        }


FetchFn = Callable[[FhirClient, str, str], Awaitable[list[dict[str, Any]]]]
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

    # Pre-warm short-circuit. The session may have a still-warm cached
    # result for this tool from app.agent.prewarm (fired on
    # /v1/sessions create). Cache key is just the tool name — the
    # pre-warm tools are all zero-arg and patient-scoped via
    # session.active_patient_id, so a per-session cache is correct
    # without further keying. Skipping ACL probe is safe here: the
    # cached result was produced by the same physician identity earlier
    # in the same session.
    cached = session.cache_get(name)
    if cached is not None:
        logger.info(
            "tool cache hit name=%s session=%s purpose=clinician_query",
            name, session.session_id,
        )
        cached.cache_hit = True
        return cached

    # Hard-deny if there is no authenticated physician identity. Empty/None
    # physician_user_id means the SMART launch / dev-launch path was bypassed
    # — we refuse rather than fall through to the legacy global token.
    if not user:
        denied = AclResult(
            allowed=False,
            section=section,
            action=action,
            reason="no_physician_user_id",
        )
        return ToolResult(
            name=name,
            data=[],
            record_ids=[],
            record_type=record_type,
            duration_ms=(time.perf_counter() - started) * 1000,
            acl_check=denied,
            error=f"acl_denied: {denied.reason}",
        )

    # Static GACL pre-flight (diagnostic only — informs logs but does NOT
    # block; OpenEMR is the source of truth via the runtime probe below).
    static_grant = acl_check(user, section, action)
    if not static_grant.allowed:
        logger.info(
            "static GACL pre-flight: %s lacks %s|%s — proceeding to runtime probe",
            user, section, action,
        )

    # Runtime ACL probe — cached per session. Asks OpenEMR (via the
    # physician's token) whether they can read the active patient. 401/403
    # → ACL denied; success → allowed. This replaces the hard-coded
    # PHYSICIAN_GRANTS map with whatever OpenEMR's GACL/users_facility
    # decides for this physician.
    acl = session.acl_decision  # type: ignore[assignment]
    if acl is None:
        # Runtime ACL probe — checks whether OpenEMR will let this
        # physician's token read the active patient at all (401/403).
        # The A.7 panel gate ran at /v1/sessions create time using the
        # PHYSICIAN_PATIENT_PANEL env (workaround for OpenEMR FHIR not
        # exposing Patient.generalPractitioner). We do NOT re-check the
        # panel here — it would always deny for non-admin physicians
        # because Patient.generalPractitioner is always absent in the
        # FHIR response. Trust the /v1/sessions gate.
        try:
            await fhir.get_resource(
                "Patient",
                session.active_patient_id,
                physician_user_id=user,
            )
            acl = AclResult(allowed=True, section=section, action=action)
        except FhirError as e:
            if e.status in (401, 403):
                acl = AclResult(
                    allowed=False,
                    section=section,
                    action=action,
                    reason=f"openemr_denied_patient_read:{e.status}",
                )
            else:
                # Inconclusive (timeout, 5xx, etc.) — proceed and let the
                # actual fetch surface the error in the tool result.
                acl = AclResult(
                    allowed=True,
                    section=section,
                    action=action,
                    reason="probe_inconclusive_proceeding",
                )
        except Exception as e:  # noqa: BLE001 — network/oauth issues
            acl = AclResult(
                allowed=True,
                section=section,
                action=action,
                reason=f"probe_inconclusive_proceeding:{e}",
            )
        session.acl_decision = acl

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
        raw = await fetch(fhir, session.active_patient_id, session.physician_user_id)
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
