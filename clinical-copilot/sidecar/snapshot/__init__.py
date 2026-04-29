"""Patient Snapshot service.

The single deterministically-reconciled JSON document the agent reasons over.
Built from a parallel fan-out across OpenEMR's per-resource FHIR endpoints
(see ARCHITECTURE.md §2.1, §2.4).
"""

from .models import (
    Allergy,
    Demographics,
    LabObservation,
    Medication,
    PatientSnapshot,
    Presenting,
    Problem,
    Provenance,
    QualityFlag,
    VerificationStatus,
    VitalObservation,
)
from .reconciler import reconcile
from .service import SnapshotService, build_snapshot_from_fixture

__all__ = [
    "Allergy",
    "Demographics",
    "LabObservation",
    "Medication",
    "PatientSnapshot",
    "Presenting",
    "Problem",
    "Provenance",
    "QualityFlag",
    "SnapshotService",
    "VerificationStatus",
    "VitalObservation",
    "build_snapshot_from_fixture",
    "reconcile",
]
