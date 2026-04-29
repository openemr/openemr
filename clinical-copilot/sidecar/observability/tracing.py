"""OpenTelemetry tracing helpers.

The trace exporter is configured by the ``OTEL_EXPORTER_OTLP_ENDPOINT``
environment variable. In production this points at the self-hosted
Langfuse endpoint inside the BAA boundary (ARCHITECTURE.md §6.2). In
development it can point at a local Jaeger or be unset, in which case
spans become no-ops.

The tracer is also designed to degrade gracefully when the
``opentelemetry`` extras are not installed — the eval suite must still
run in CI without those wheels.
"""

from __future__ import annotations

import contextlib
from collections.abc import Iterator
from typing import Any

import structlog

logger = structlog.get_logger(__name__)


_TRACER: Any = None  # opentelemetry.trace.Tracer when initialised
_INITIALISED = False


def init_observability(service_name: str = "clinical-copilot") -> None:
    """Configure OpenTelemetry. Safe to call multiple times.

    Falls back to no-op tracing when ``opentelemetry`` is not installed.
    """
    global _TRACER, _INITIALISED  # noqa: PLW0603
    if _INITIALISED:
        return
    try:
        from opentelemetry import trace
        from opentelemetry.exporter.otlp.proto.http.trace_exporter import OTLPSpanExporter
        from opentelemetry.sdk.resources import Resource
        from opentelemetry.sdk.trace import TracerProvider
        from opentelemetry.sdk.trace.export import BatchSpanProcessor

        provider = TracerProvider(resource=Resource.create({"service.name": service_name}))
        provider.add_span_processor(BatchSpanProcessor(OTLPSpanExporter()))
        trace.set_tracer_provider(provider)
        _TRACER = trace.get_tracer(service_name)
        _INITIALISED = True
        logger.info("observability_initialised", service_name=service_name)
    except Exception as exc:  # noqa: BLE001
        logger.warning("observability_disabled", reason=str(exc))
        _TRACER = None
        _INITIALISED = True


def get_tracer() -> Any:
    """Return the active tracer. Will be ``None`` when OTEL is unavailable."""
    return _TRACER


@contextlib.contextmanager
def span(name: str, **attributes: Any) -> Iterator[None]:
    """Context manager that opens a span if the tracer is available."""
    tracer = get_tracer()
    if tracer is None:
        yield
        return
    with tracer.start_as_current_span(name) as s:
        for k, v in attributes.items():
            try:
                s.set_attribute(k, v)
            except Exception:  # noqa: BLE001
                pass
        yield
