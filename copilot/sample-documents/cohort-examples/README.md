# Cohort-provided example documents

These eight documents were distributed by the AgentForge cohort as
reference fixtures for the W2 Multimodal Evidence Agent assignment.
Including them here so graders can test the deployed Co-Pilot against
the same documents they evaluate every cohort submission with — no
detour through the original distribution.

## File index

| Patient | Lab result | Intake form |
|---|---|---|
| p01 Chen | [`lab-results/p01-chen-lipid-panel.pdf`](./lab-results/p01-chen-lipid-panel.pdf) | [`intake-forms/p01-chen-intake-typed.pdf`](./intake-forms/p01-chen-intake-typed.pdf) |
| p02 Whitaker | [`lab-results/p02-whitaker-cbc.pdf`](./lab-results/p02-whitaker-cbc.pdf) | [`intake-forms/p02-whitaker-intake.pdf`](./intake-forms/p02-whitaker-intake.pdf) |
| p03 Reyes | [`lab-results/p03-reyes-hba1c.png`](./lab-results/p03-reyes-hba1c.png) *(rasterized photo)* | [`intake-forms/p03-reyes-intake.png`](./intake-forms/p03-reyes-intake.png) *(rasterized)* |
| p04 Kowalski | [`lab-results/p04-kowalski-cmp.pdf`](./lab-results/p04-kowalski-cmp.pdf) | [`intake-forms/p04-kowalski-intake.png`](./intake-forms/p04-kowalski-intake.png) *(rasterized)* |

## Coverage matrix

| Path | What it exercises |
|---|---|
| **PDF lab** (p01, p02, p04) | PDF.js text-snap layer for bbox alignment on born-digital labs |
| **PNG photo lab** (p03 hba1c) | Tesseract OCR-snap server-side path on rasterized photos |
| **PDF intake** (p01, p02) | VLM extraction of typed intake forms |
| **PNG intake** (p03, p04) | VLM extraction + OCR-snap on photographed forms (Chen "shellfish?? maybe iodine" ambiguity is a hard-problem fixture) |

## How to use

These are interchangeable with the demo-hero PDFs in the parent
`sample-documents/` folder. Drop any of them on the Co-Pilot iframe
rail of the deployed OpenEMR (any patient chart) and ask questions.
The patient *name* on the document doesn't have to match the chart
patient — the agent extracts what's on the page and reasons about it.

Note: patient names on these documents (Chen, Whitaker, Reyes,
Kowalski) do not correspond to any patient in the deployed Railway
OpenEMR's Synthea roster. For chart-name-matching demo flow, use the
parent folder's `mariela-*.pdf` and `dana-*.pdf` instead.

## Provenance

Distributed by the AgentForge cohort organizers via
`~/Desktop/Gauntlet/Week2/example-documents/` in the W2 assignment
materials. None contain real PHI — all are synthetic.

Re-distribution here is for grader convenience; original copies
remain authoritative if there's any drift.
