"""Three-layer eval suite (ARCHITECTURE.md §7).

- Layer 1: unit-level pairwise judgments scored against gold answers.
- Layer 2: patient-level scenarios with seeded findings.
- Layer 3: adversarial probes (prompt injection, scope escalation, missing-data).

The CI gate runs all three; regression below thresholds blocks deploy.
"""
