"""Deterministic pair generation.

This is **code, not an LLM**. The pair generator is the most important
place to push determinism: bugs in the pair set silently bias every
downstream LLM call. Pairs are bounded (ARCHITECTURE.md §4.1, §8): cap at
``max_pairs`` per call.

Each pair carries the source datapoint object so the per-kind prompt
renderer can pull kind-specific fields (icd10 for a diagnosis, dose for a
medication, severity for an allergy, etc.) without round-tripping through
the snapshot.
"""

from __future__ import annotations

from dataclasses import dataclass

from sidecar.snapshot import PatientSnapshot, Provenance


@dataclass(frozen=True)
class PairA:
    """Use Case A pair: ``(presenting symptom, candidate datapoint)``."""

    symptom: str
    since: str | None
    candidate_label: str
    candidate_kind: str  # "diagnosis" | "medication" | "lab" | "vital" |
                         # "allergy" | "procedure" | "immunization" |
                         # "family_history" | "social_history" | "imaging" |
                         # "test" | "encounter"
    candidate_provenance: Provenance
    candidate_obj: object  # source pydantic model — used by the renderer


@dataclass(frozen=True)
class PairB:
    """Use Case B pair: ``(finding A, finding B)`` over documented findings."""

    label_a: str
    provenance_a: Provenance
    kind_a: str
    obj_a: object
    label_b: str
    provenance_b: Provenance
    kind_b: str
    obj_b: object


def generate_pairs_a(snapshot: PatientSnapshot, *, max_pairs: int = 200) -> list[PairA]:
    """Generate ``(symptom, candidate)`` for Use Case A.

    Candidates are drawn from every documented kind in the snapshot —
    problems, meds, abnormal labs, vitals, allergies, procedures,
    immunizations, family history, social history, imaging, tests, and
    recent encounters.
    """
    pairs: list[PairA] = []
    candidates = snapshot.all_findings()
    since = snapshot.presenting.since

    for symptom in snapshot.presenting.symptoms:
        for label, prov, kind, obj in candidates:
            pairs.append(
                PairA(
                    symptom=symptom,
                    since=since,
                    candidate_label=label,
                    candidate_kind=kind,
                    candidate_provenance=prov,
                    candidate_obj=obj,
                )
            )
            if len(pairs) >= max_pairs:
                return pairs
    return pairs


def generate_pairs_b(snapshot: PatientSnapshot, *, max_pairs: int = 200) -> list[PairB]:
    """Generate ``(finding_i, finding_j)`` over documented findings.

    The order matters for some checks (e.g. "osteopenia precedes
    osteoporosis") so we emit ordered pairs, not unordered combinations.
    """
    findings = snapshot.all_findings()
    pairs: list[PairB] = []
    for i, (label_a, prov_a, kind_a, obj_a) in enumerate(findings):
        for j, (label_b, prov_b, kind_b, obj_b) in enumerate(findings):
            if i == j:
                continue
            pairs.append(
                PairB(
                    label_a=label_a,
                    provenance_a=prov_a,
                    kind_a=kind_a,
                    obj_a=obj_a,
                    label_b=label_b,
                    provenance_b=prov_b,
                    kind_b=kind_b,
                    obj_b=obj_b,
                )
            )
            if len(pairs) >= max_pairs:
                return pairs
    return pairs
