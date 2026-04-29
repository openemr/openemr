"""Verification layer.

Two invariants enforced after every agent response and before it reaches
the clinician (ARCHITECTURE.md §5):

1. **Source attribution.** Every factual claim must be paired with a
   ``(table, row_id, observed_at)`` triple from the snapshot.
2. **Domain constraint enforcement.** The response must not violate any
   rule in the curated rule store.
"""

from .rules import Rule, RuleAction, RuleStore, load_default_rule_store
from .verifier import VerificationOutcome, Verifier

__all__ = [
    "Rule",
    "RuleAction",
    "RuleStore",
    "VerificationOutcome",
    "Verifier",
    "load_default_rule_store",
]
