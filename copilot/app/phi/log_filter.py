"""PHI scrubbing logging.Filter for app logs and uncaught-exception tracebacks.

Closes the gap that `app/phi/minimizer.py::_scrub_text` only ran over `TurnTrace`
payloads, leaving FastAPI access logs and exception tracebacks (which can echo
request bodies) un-scrubbed.

This filter is installed on the root logger at FastAPI startup. It iterates
every active session's `name_terms` (collected from each session's
`PseudonymMap` via `app.phi.session.sessions`) and runs the existing
`_scrub_text` over `LogRecord.msg`, the formatted-args string, and `exc_text`
when present.

It is deliberately conservative:
  - String operations only — preserves log levels, loggers, and structural
    fields untouched.
  - Idempotent — running the scrubber twice yields the same output.
  - Fail-open in the scrubber: if iterating the active sessions raises (e.g.
    during shutdown teardown), the filter still emits the record so we don't
    suppress signal during incidents. The eval case
    `evals/observability/test_no_phi_in_app_logs.py` enforces the happy path.
"""
from __future__ import annotations

import logging
from typing import Iterable

from app.phi.minimizer import _scrub_text
from app.phi.session import SessionStore


class PhiLogFilter(logging.Filter):
    """Scrub LogRecord.msg / args / exc_text against active-session name terms."""

    def __init__(self, session_store: SessionStore) -> None:
        super().__init__()
        self._session_store = session_store

    def _active_name_terms(self) -> list[str]:
        terms: list[str] = []
        try:
            sessions_map = getattr(self._session_store, "_map", {})
            for session in sessions_map.values():
                terms.extend(_terms_from_pseudonym_map(session))
        except Exception:  # noqa: BLE001 — fail-open per docstring
            return []
        return terms

    def filter(self, record: logging.LogRecord) -> bool:
        terms = self._active_name_terms()
        if not terms:
            return True

        if isinstance(record.msg, str):
            scrubbed = _scrub_text(record.msg, terms)
            if scrubbed is not None:
                record.msg = scrubbed

        if record.args:
            try:
                rendered = record.msg % record.args if isinstance(record.msg, str) else None
            except (TypeError, ValueError):
                rendered = None
            if rendered is not None:
                scrubbed = _scrub_text(rendered, terms)
                if scrubbed != rendered:
                    record.msg = scrubbed
                    record.args = ()

        if record.exc_info and not record.exc_text:
            # The Formatter would otherwise populate exc_text at emit time,
            # bypassing our scrub. Materialize it now so we can sanitize it
            # before any handler sees it; the Formatter will reuse our value.
            record.exc_text = logging.Formatter().formatException(record.exc_info)
        if record.exc_text:
            scrubbed = _scrub_text(record.exc_text, terms)
            if scrubbed is not None:
                record.exc_text = scrubbed

        return True


def _terms_from_pseudonym_map(session: object) -> Iterable[str]:
    """Pull free-text PHI terms a session knows about.

    Today the only available terms are those baked into a session's
    pseudonym mappings (real ResourceType/uuid values, which already aren't
    PHI). When Week 2's intake_extractor lands and starts populating
    `extracted_facts` with real names, the session should expose a
    `phi_terms` attribute — this iterator picks it up automatically without
    a filter change.
    """
    phi_terms = getattr(session, "phi_terms", None)
    if isinstance(phi_terms, (list, tuple, set)):
        return [t for t in phi_terms if isinstance(t, str) and t]
    return []


def install(root_logger: logging.Logger, session_store: SessionStore) -> PhiLogFilter:
    """Install the filter on the root logger and uvicorn.access.

    Returns the filter instance so callers can also attach it to
    additional loggers (e.g. test fixtures).
    """
    flt = PhiLogFilter(session_store)
    root_logger.addFilter(flt)
    logging.getLogger("uvicorn.access").addFilter(flt)
    logging.getLogger("uvicorn.error").addFilter(flt)
    return flt
