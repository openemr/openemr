"""High-level snapshot service.

Combines the FHIR client and the reconciler. Also exposes a fixture loader
so the eval suite can construct snapshots directly from the synthetic JSON
patients in ``fixtures/patients/`` without an OpenEMR instance.
"""

from __future__ import annotations

import json
from collections.abc import Mapping
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

from .fhir_client import FhirClient
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


class SnapshotService:
    """Builds the patient snapshot from OpenEMR's FHIR API."""

    def __init__(self, fhir_client: FhirClient) -> None:
        self._fhir = fhir_client

    async def build(
        self,
        patient_uuid: str,
        *,
        presenting: Presenting | None = None,
        demographics: Demographics | None = None,
    ) -> PatientSnapshot:
        bundles_by_name: dict[str, Mapping[str, Any] | None] = {}
        results = await self._fhir.fan_out(patient_uuid)
        for name, result in results.items():
            bundles_by_name[name] = result.bundle
        return reconcile(
            patient_uuid=patient_uuid,
            fhir_bundles=bundles_by_name,
            presenting=presenting,
            demographics=demographics,
        )


def _provenance(d: Mapping[str, Any]) -> Provenance:
    """Build a Provenance from a fixture's compact dict form."""
    return Provenance(
        table=d["table"],
        row_id=d["row_id"],
        observed_at=_parse_obs(d.get("observed_at")),
        fhir_resource=d.get("fhir_resource"),
        entered_by=d.get("entered_by"),
    )


def _parse_obs(value: Any) -> Any:
    """Permissive parser used by fixture loading. Accepts ISO date or datetime."""
    if value is None:
        return None
    s = str(value)
    try:
        return datetime.fromisoformat(s.replace("Z", "+00:00"))
    except ValueError:
        try:
            return datetime.fromisoformat(s + "T00:00:00+00:00").date()
        except ValueError:
            return None


def build_snapshot_from_fixture(path: str | Path) -> PatientSnapshot:
    """Load a hand-authored synthetic patient from JSON.

    Schema mirrors the example in ARCHITECTURE.md §2.1, with each field
    nested under top-level keys ``demographics``, ``active_problems``,
    ``medications``, ``allergies``, ``recent_vitals``, ``recent_labs``,
    ``presenting``. Each fact carries a ``provenance`` block.
    """
    raw: Mapping[str, Any] = json.loads(Path(path).read_text(encoding="utf-8"))

    demographics = Demographics(**raw.get("demographics", {}))

    problems = [
        Problem(
            id=p["id"],
            label=p["label"],
            icd10=p.get("icd10"),
            snomed=p.get("snomed"),
            onset=_parse_obs(p.get("onset")),
            enddate=_parse_obs(p.get("enddate")),
            verification=VerificationStatus(p.get("verification", "confirmed")),
            provenance=_provenance(p["provenance"]),
        )
        for p in raw.get("active_problems", [])
    ]

    meds = [
        Medication(
            id=m["id"],
            label=m["label"],
            rxnorm=m.get("rxnorm"),
            dose=m.get("dose"),
            route=m.get("route"),
            frequency=m.get("frequency"),
            started=_parse_obs(m.get("started")),
            stopped=_parse_obs(m.get("stopped")),
            active=m.get("active", True),
            sources_in_agreement=m.get("sources_in_agreement", True),
            provenance=_provenance(m["provenance"]),
        )
        for m in raw.get("medications", [])
    ]

    allergies = [
        Allergy(
            id=a["id"],
            label=a["label"],
            rxnorm=a.get("rxnorm"),
            snomed=a.get("snomed"),
            reaction=a.get("reaction"),
            severity=a.get("severity"),
            criticality=a.get("criticality"),
            onset=_parse_obs(a.get("onset")),
            provenance=_provenance(a["provenance"]),
        )
        for a in raw.get("allergies", [])
    ]

    vitals = [
        VitalObservation(
            id=v["id"],
            loinc=v["loinc"],
            label=v["label"],
            value=v.get("value"),
            unit=v.get("unit"),
            observed_at=_parse_obs(v["observed_at"]),
            provenance=_provenance(v["provenance"]),
        )
        for v in raw.get("recent_vitals", [])
    ]

    labs = [
        LabObservation(
            id=lb["id"],
            loinc=lb["loinc"],
            label=lb["label"],
            value=lb.get("value"),
            unit=lb.get("unit"),
            reference_low=lb.get("reference_low"),
            reference_high=lb.get("reference_high"),
            abnormal_flag=lb.get("abnormal_flag"),
            observed_at=_parse_obs(lb["observed_at"]),
            provenance=_provenance(lb["provenance"]),
        )
        for lb in raw.get("recent_labs", [])
    ]

    flags = [QualityFlag(**f) for f in raw.get("quality_flags", [])]
    presenting = Presenting(**raw.get("presenting", {}))

    return PatientSnapshot(
        patient_id=raw["patient_id"],
        snapshot_version=datetime.now(tz=timezone.utc),
        demographics=demographics,
        active_problems=problems,
        medications=meds,
        allergies=allergies,
        recent_vitals=vitals,
        recent_labs=labs,
        presenting=presenting,
        quality_flags=flags,
        free_text_notes_index=raw.get("free_text_notes_index"),
    )
