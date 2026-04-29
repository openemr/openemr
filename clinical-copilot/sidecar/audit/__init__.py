"""Hash-chained AI audit log.

ARCHITECTURE.md §6.3 — separate from OpenEMR's ``audit_master``,
tamper-evident, 7-year retention.
"""

from .log import AuditEntry, AuditLog, InMemoryAuditLog

__all__ = ["AuditEntry", "AuditLog", "InMemoryAuditLog"]
