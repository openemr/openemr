"""Deterministic pair generation.

This is **code, not an LLM**. The pair generator is the most important place
to push determinism: bugs in the pair set silently bias every downstream
LLM call. Pairs are bounded (ARCHITECTURE.md §4.1, §8): cap at
``max_pairs`` per call.
"""

from __future__ import annotations

from dataclasses import dataclass

from sidecar.snapshot import PatientSnapshot, Provenance


@dataclass(frozen=True)
class PairA:
    """Use Case A pair: ``(presenting symptom, candidate explanation)``."""

    symptom: str
    candidate_label: str
    candidate_kind: str  # "problem" | "medication" | "allergy" | "lab"
    candidate_provenance: Provenance


@dataclass(frozen=True)
class PairB:
    """Use Case B pair: ``(finding A, finding B)`` over documented findings."""

    label_a: str
    provenance_a: Provenance
    kind_a: str
    label_b: str
    provenance_b: Provenance
    kind_b: str


def generate_pairs_a(snapshot: PatientSnapshot, *, max_pairs: int = 200) -> list[PairA]:
    """Generate ``(symptom, candidate)`` for Use Case A.

    Candidates are drawn from the patient's active problems, active
    medications (with their known side effects), and recent abnormal labs.
    """
    pairs: list[PairA] = []
    candidates = snapshot.all_findings()

    for symptom in snapshot.presenting.symptoms:
        for label, prov, kind in candidates:
            pairs.append(
                PairA(
                    symptom=symptom,
                    candidate_label=label,
                    candidate_kind=kind,
                    candidate_provenance=prov,
                )
            )
            if len(pairs) >= max_pairs:
                return pairs
    return pairs


def generate_pairs_b(snapshot: PatientSnapshot, *, max_pairs: int = 200) -> list[PairB]:
    """Generate ``(finding_i, finding_j)`` over documented findings.

    The order matters for some rules (e.g. "osteopenia precedes osteoporosis")
    so we emit ordered pairs, not unordered combinations.
    """
    findings = snapshot.all_findings()
    pairs: list[PairB] = []
    for i, (label_a, prov_a, kind_a) in enumerate(findings):
        for j, (label_b, prov_b, kind_b) in enumerate(findings):
            if i == j:
                continue
            pairs.append(
                PairB(
                    label_a=label_a,
                    provenance_a=prov_a,
                    kind_a=kind_a,
                    label_b=label_b,
                    provenance_b=prov_b,
                    kind_b=kind_b,
                )
            )
            if len(pairs) >= max_pairs:
                return pairs
    return pairs
