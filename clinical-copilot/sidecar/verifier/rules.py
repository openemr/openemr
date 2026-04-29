"""Curated rule store loader and matcher.

A small deterministic engine that runs **after** the LLM pair judgments.
It is the deterministic guard from ARCHITECTURE.md §5.3 — explicitly
**not** the place where "are these two findings inconsistent" reasoning
happens (that is the LLM in §4.1).
"""

from __future__ import annotations

from dataclasses import dataclass, field
from pathlib import Path
from typing import Any

import yaml

from sidecar._compat import StrEnum

from sidecar.snapshot import PatientSnapshot


DEFAULT_RULE_STORE_PATH = Path(__file__).resolve().parent / "rule_store.yaml"


class RuleAction(StrEnum):
    BLOCK = "block"
    WARN = "warn"


@dataclass(frozen=True)
class Rule:
    id: str
    kind: str
    action: RuleAction
    description: str
    triggers: dict[str, Any] = field(default_factory=dict)


@dataclass(frozen=True)
class RuleHit:
    """One matched rule with the snapshot evidence behind it."""

    rule: Rule
    evidence: list[str]


class RuleStore:
    def __init__(self, version: str, rules: list[Rule]) -> None:
        self.version = version
        self.rules = rules

    def evaluate_against_snapshot(self, snapshot: PatientSnapshot) -> list[RuleHit]:
        """Run every rule against the snapshot and return all matches.

        The rule engine is intentionally simple: each trigger is a
        substring/threshold match on snapshot fields. Adding new trigger
        types requires editing this file (and a test).
        """
        problem_labels = [p.label.lower() for p in snapshot.active_problems]
        med_labels = [m.label.lower() for m in snapshot.medications if m.active]
        allergy_labels = [a.label.lower() for a in snapshot.allergies]
        symptoms = [s.lower() for s in snapshot.presenting.symptoms]

        # HbA1c lookup for the diabetes rule.
        hba1c_values = [
            float(lab.value)
            for lab in snapshot.recent_labs
            if "hba1c" in lab.label.lower() and isinstance(lab.value, (int, float))
        ]

        # Trigger names whose presence + match is required for the rule to fire.
        # Any other trigger key is treated as an LLM-confirmed condition that
        # the rule store does not re-evaluate (see ARCHITECTURE.md §5.3).
        REQUIRED_LABEL_TRIGGERS = {
            "problem_label_contains",
            "medication_label_contains",
            "medication_a_contains",
            "medication_b_contains",
            "allergy_label_contains",
            "symptom_contains",
            "label_a_contains",
            "label_b_contains",
            "hba1c_below",
        }

        out: list[RuleHit] = []
        for rule in self.rules:
            t = rule.triggers
            evidence: list[str] = []
            present = REQUIRED_LABEL_TRIGGERS.intersection(t.keys())
            # A rule with no recognised label trigger is a configuration
            # error (it would always fire). Skip with no match.
            if not present:
                continue

            matched = True

            if (needles := t.get("problem_label_contains")) is not None:
                if not any(_contains_any(p, needles) for p in problem_labels):
                    matched = False
                else:
                    evidence.append(f"problem matches one of {needles}")

            if matched and (needles := t.get("medication_label_contains")) is not None:
                if not any(_contains_any(m, needles) for m in med_labels):
                    matched = False
                else:
                    evidence.append(f"medication matches one of {needles}")

            if matched and (needles := t.get("medication_a_contains")) is not None:
                if not any(_contains_any(m, needles) for m in med_labels):
                    matched = False
                else:
                    evidence.append(f"medication A matches one of {needles}")

            if matched and (needles := t.get("medication_b_contains")) is not None:
                if not any(_contains_any(m, needles) for m in med_labels):
                    matched = False
                else:
                    evidence.append(f"medication B matches one of {needles}")

            if matched and (needles := t.get("allergy_label_contains")) is not None:
                if not any(_contains_any(a, needles) for a in allergy_labels):
                    matched = False
                else:
                    evidence.append(f"allergy matches one of {needles}")

            if matched and (needles := t.get("symptom_contains")) is not None:
                if not any(_contains_any(s, needles) for s in symptoms):
                    matched = False
                else:
                    evidence.append(f"symptom matches one of {needles}")

            # `label_a_contains` / `label_b_contains` (used by the temporal
            # ``osteo_progression_backward`` rule) match across the union of
            # problems + meds + allergies (any documented finding). The
            # ordering trigger ``a_before_b`` is confirmed by the LLM in §4.1;
            # we just surface that the labels co-exist.
            all_labels = problem_labels + med_labels + allergy_labels
            if matched and (needles := t.get("label_a_contains")) is not None:
                if not any(_contains_any(lb, needles) for lb in all_labels):
                    matched = False
                else:
                    evidence.append(f"label A matches one of {needles}")
            if matched and (needles := t.get("label_b_contains")) is not None:
                if not any(_contains_any(lb, needles) for lb in all_labels):
                    matched = False
                else:
                    evidence.append(f"label B matches one of {needles}")

            if matched and (threshold := t.get("hba1c_below")) is not None:
                if not hba1c_values or min(hba1c_values) >= threshold:
                    matched = False
                else:
                    evidence.append(f"HbA1c {min(hba1c_values)} < threshold {threshold}")

            if not matched:
                continue
            out.append(RuleHit(rule=rule, evidence=evidence))
        return out


def _contains_any(haystack: str, needles: list[str]) -> bool:
    return any(n.lower() in haystack for n in needles)


def load_default_rule_store(path: str | Path | None = None) -> RuleStore:
    """Load the YAML rule store. Default path is the bundled file."""
    target = Path(path) if path else DEFAULT_RULE_STORE_PATH
    raw = yaml.safe_load(target.read_text(encoding="utf-8"))
    rules = [
        Rule(
            id=r["id"],
            kind=r["kind"],
            action=RuleAction(r["action"]),
            description=r["description"].strip(),
            triggers=r.get("triggers", {}),
        )
        for r in raw.get("rules", [])
    ]
    return RuleStore(version=raw.get("store_version", "0.0.0"), rules=rules)
