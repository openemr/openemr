"""Patient Snapshot service.

The single deterministically-reconciled JSON document the agent reasons over.
Built from a parallel fan-out across OpenEMR's per-resource FHIR endpoints
(see ARCHITECTURE.md §2.1, §2.4).
"""

from .models import (
    Allergy,
    Demographics,
    DiagnosticTest,
    EncounterEntry,
    FamilyHistoryEntry,
    ImagingFinding,
    Immunization,
    LabObservation,
    Medication,
    PatientSnapshot,
    Presenting,
    Problem,
    Procedure,
    Provenance,
    QualityFlag,
    SocialHistoryEntry,
    VerificationStatus,
    VitalObservation,
)
from .reconciler import reconcile
from .service import SnapshotService, build_snapshot_from_fixture

__all__ = [
    "Allergy",
    "Demographics",
    "DiagnosticTest",
    "EncounterEntry",
    "FamilyHistoryEntry",
    "ImagingFinding",
    "Immunization",
    "LabObservation",
    "Medication",
    "PatientSnapshot",
    "Presenting",
    "Problem",
    "Procedure",
    "Provenance",
    "QualityFlag",
    "SnapshotService",
    "SocialHistoryEntry",
    "VerificationStatus",
    "VitalObservation",
    "build_snapshot_from_fixture",
    "reconcile",
]
