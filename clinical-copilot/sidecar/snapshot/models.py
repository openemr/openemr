"""Pydantic models for the Patient Snapshot.

The shape mirrors the JSON example in ARCHITECTURE.md §2.1. Every fact carries
a ``Provenance`` triple (table, row_id, observed_at) so the verifier can
strip claims that are not attributable.
"""

from __future__ import annotations

from datetime import date, datetime

from pydantic import BaseModel, ConfigDict, Field

from sidecar._compat import StrEnum


class ProblemStatus(StrEnum):
    ACTIVE = "active"
    RECURRENCE = "recurrence"
    RELAPSE = "relapse"
    INACTIVE = "inactive"
    REMISSION = "remission"
    RESOLVED = "resolved"


class VerificationStatus(StrEnum):
    UNCONFIRMED = "unconfirmed"
    PROVISIONAL = "provisional"
    DIFFERENTIAL = "differential"
    CONFIRMED = "confirmed"
    REFUTED = "refuted"
    ENTERED_IN_ERROR = "entered-in-error"


class Provenance(BaseModel):
    """Where a fact came from in OpenEMR.

    The verifier uses these triples to reject claims that cannot be traced
    back to the snapshot. ``observed_at`` is the clinically-meaningful
    timestamp (onset, recorded date), not the row's insert time.
    """

    model_config = ConfigDict(frozen=True)

    table: str
    row_id: int | str
    observed_at: datetime | date | None = None
    entered_by: int | None = None
    fhir_resource: str | None = None  # e.g. "Condition/2241"


class QualityFlag(BaseModel):
    """A self-reported data-quality issue from the reconciler.

    The agent surfaces these explicitly in its responses (ARCHITECTURE.md §2.1)
    so a clinician knows what is missing as well as what is present.
    """

    model_config = ConfigDict(frozen=True)

    code: str  # e.g. "med_disagreement", "icd10_unverified", "missing_enddate"
    description: str
    related_provenance: list[Provenance] = Field(default_factory=list)


class Demographics(BaseModel):
    age: int | None = None
    sex_at_birth: str | None = None
    weight_kg: float | None = None
    height_cm: float | None = None


class Problem(BaseModel):
    """A condition / problem-list entry."""

    model_config = ConfigDict(frozen=True)

    id: str  # FHIR id e.g. "Condition/2241"
    label: str
    icd10: str | None = None
    snomed: str | None = None
    onset: date | None = None
    enddate: date | None = None
    status: ProblemStatus = ProblemStatus.ACTIVE
    verification: VerificationStatus = VerificationStatus.CONFIRMED
    provenance: Provenance


class Medication(BaseModel):
    model_config = ConfigDict(frozen=True)

    id: str
    label: str
    rxnorm: str | None = None
    dose: str | None = None
    route: str | None = None
    frequency: str | None = None
    started: date | None = None
    stopped: date | None = None
    active: bool = True
    provenance: Provenance
    # Reconciliation tag: this medication was found in both `lists` and
    # `prescriptions` and they agreed / disagreed. See reconciler.py.
    sources_in_agreement: bool = True


class Allergy(BaseModel):
    model_config = ConfigDict(frozen=True)

    id: str
    label: str
    rxnorm: str | None = None
    snomed: str | None = None
    reaction: str | None = None
    severity: str | None = None  # mild | moderate | severe
    criticality: str | None = None  # low | high | unable-to-assess
    onset: date | None = None
    provenance: Provenance


class VitalObservation(BaseModel):
    model_config = ConfigDict(frozen=True)

    id: str
    loinc: str
    label: str
    value: float | None = None
    unit: str | None = None
    observed_at: datetime
    provenance: Provenance


class LabObservation(BaseModel):
    model_config = ConfigDict(frozen=True)

    id: str
    loinc: str
    label: str
    value: float | str | None = None
    unit: str | None = None
    reference_low: float | None = None
    reference_high: float | None = None
    abnormal_flag: str | None = None  # H, L, HH, LL, A, …
    observed_at: datetime
    provenance: Provenance

    @property
    def is_abnormal(self) -> bool:
        if self.abnormal_flag in {"H", "HH", "L", "LL", "A"}:
            return True
        if isinstance(self.value, (int, float)):
            if self.reference_high is not None and self.value > self.reference_high:
                return True
            if self.reference_low is not None and self.value < self.reference_low:
                return True
        return False


class Presenting(BaseModel):
    """The chief complaint / symptoms for the upcoming or current visit.

    Mapped from one of three FHIR locations depending on capture point
    (ARCHITECTURE.md §2.4 "Symptom-source note").
    """

    symptoms: list[str] = Field(default_factory=list)
    since: str | None = None
    source: str | None = None  # "patient portal pre-visit form" | "Encounter.reasonCode" | …


class PatientSnapshot(BaseModel):
    """The single artifact the agent reasons over."""

    model_config = ConfigDict(frozen=True)

    patient_id: str  # "Patient/87413" — FHIR resource UUID, not legacy pid
    snapshot_version: datetime
    demographics: Demographics
    active_problems: list[Problem] = Field(default_factory=list)
    medications: list[Medication] = Field(default_factory=list)
    allergies: list[Allergy] = Field(default_factory=list)
    recent_vitals: list[VitalObservation] = Field(default_factory=list)
    recent_labs: list[LabObservation] = Field(default_factory=list)
    presenting: Presenting = Field(default_factory=Presenting)
    quality_flags: list[QualityFlag] = Field(default_factory=list)
    free_text_notes_index: str | None = None  # pgvector pointer e.g. "vector://patient-87413"

    def all_findings(self) -> list[tuple[str, Provenance, str]]:
        """Flatten every documented finding to ``(label, provenance, kind)``.

        Used by the Pair Generator for Use Case B and as the candidate pool
        for Use Case A.
        """
        out: list[tuple[str, Provenance, str]] = []
        for p in self.active_problems:
            out.append((p.label, p.provenance, "problem"))
        for m in self.medications:
            if m.active:
                out.append((m.label, m.provenance, "medication"))
        for a in self.allergies:
            out.append((a.label, a.provenance, "allergy"))
        for lab in self.recent_labs:
            if lab.is_abnormal:
                out.append((f"{lab.label} = {lab.value} {lab.unit or ''}".strip(),
                            lab.provenance, "lab"))
        return out
