"""Pre-warm: fire the high-frequency tools the moment a session opens.

The clinician opens the Co-Pilot iframe rail, then takes ~5-15s to read
the chart and type a question. We use that idle window to pre-fetch the
4 tools that 80%+ of opening questions hit (UC1/UC2/UC3 all start with
the same fan-out: summary + meds + labs + allergies). Results are cached
on the session for 90s; when the real question arrives, run_tool()
short-circuits to the cached result and saves the FHIR round-trip
(~3-4s on UC1, the largest single chunk of first-turn latency).

**Audit note.** This fires unconditionally on session-open even if the
clinician never asks a question. Logged with `purpose=prefetch` so the
audit trail can distinguish prefetch reads from clinician-driven reads.
This is a deliberate trade-off — the alternative (lazy fetch on first
question) leaves the user-visible latency unchanged.

**Concurrency.** Spawned via `asyncio.create_task()` from
`/v1/sessions` so it does NOT block the session-create response. If the
clinician types unusually fast and the real request arrives before
prewarm completes, the cache miss falls through to the normal fetch
path — no harm, just no speedup on that turn.
"""
from __future__ import annotations

import asyncio
import logging

from app.fhir.client import FhirClient
from app.phi.session import PseudonymMap
from app.tools.registry import dispatch

logger = logging.getLogger("copilot.prewarm")

# The 4 tools that 80%+ of opening questions hit. Order doesn't matter
# (asyncio.gather fires them concurrently); listing the high-value ones
# first only matters for log readability.
PREWARM_TOOLS: tuple[str, ...] = (
    "get_patient_summary",
    "get_active_medications",
    "get_recent_labs",
    "get_allergies",
)


async def prewarm(session: PseudonymMap, fhir: FhirClient) -> None:
    """Fan out the prewarm tools and cache results on the session."""

    async def _one(name: str) -> None:
        try:
            result = await dispatch(name, {}, fhir, session)
        except Exception as e:  # noqa: BLE001 — prewarm must never break session create
            logger.info(
                "prewarm miss name=%s session=%s err=%s purpose=prefetch",
                name, session.session_id, e,
            )
            return
        if result.error:
            logger.info(
                "prewarm error name=%s session=%s err=%s purpose=prefetch",
                name, session.session_id, result.error,
            )
            return
        session.cache_put(name, result)
        logger.info(
            "prewarm hit name=%s session=%s ms=%.1f records=%d purpose=prefetch",
            name, session.session_id, result.duration_ms, len(result.record_ids),
        )

    await asyncio.gather(*(_one(t) for t in PREWARM_TOOLS))
