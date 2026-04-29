"""Observability: OpenTelemetry traces, structured logs, Prometheus metrics."""

from .tracing import get_tracer, init_observability, span

__all__ = ["get_tracer", "init_observability", "span"]
