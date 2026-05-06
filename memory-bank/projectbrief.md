# Project Brief — AgentForge / Clinical Co-Pilot

**Cohort:** Gauntlet AI — Austin Admission Track
**Repo:** fork of `openemr/openemr` (mirror at `labs.gauntletai.com/ruijingwang/openemr`, primary at `github.com/rikkiiwang/openemr`)
**Owner:** Rikki Wang (`wrjgouwu@gmail.com`)
**Spec:** `~/Desktop/Gauntlet/Week1-AgentForge/Week 1 - AgentForge.pdf`

---

## Overall objective

Build a **trustworthy, production-defensible AI agent embedded directly into OpenEMR** that gives a primary care physician useful patient context in the **60–90 second window between rooms**. The standard set by the spec (Final Note, p.9):

> *"The deliverable that matters is not the one that looks most impressive in a demo. It's the one you could defend in front of a hospital CTO who is deciding whether to put it in front of their physicians."*

Three immovable promises that shape every decision:

1. **Every clinical claim is traceable** to a specific record in the patient's chart. No claim leaves the agent without a `record_id` from a tool call this turn.
2. **No raw PHI crosses the LLM boundary.** Identifiers (name, SSN, DOB, address, telecom) are pseudonymized server-side; mapping happens after the LLM response.
3. **Refuse over guess.** When source data is missing, the agent says so explicitly. A wrong answer in a clinical setting is worse than no answer.

---

## Evaluation criteria (from PRD p.9 — "Interview Preparation")

The AI Interview is required within 24h of every major submission. The questions the project is graded on:

**Audit**
- Walk through the most important finding.
- What would have been missed by skipping the audit and going straight to building?
- How did the audit change the AI integration plan?

**Architecture**
- Why was the verification layer designed the way it was?
- What does the agent do when a tool fails or a record is missing?
- Where are the trust boundaries, and how are they enforced?

**Evaluation**
- What does the eval suite test that a happy-path demo would not reveal?
- What did running it surface?
- What would be added next?

**Production thinking**
- How would this scale to a 500-bed hospital with 300 concurrent clinical users?
- What would need to change before being comfortable with a real physician relying on it?
- Which failure mode worries you most, and why?

The project is also evaluated on *thoroughness, thoughtfulness, creativity, and ability to leverage technology to build something viable* (PRD p.1, "How to Use This Case Study").

---

## Final deliverables (PRD p.8 — "Submission Requirements")

Final deadline: **Sunday 2026-05-03 10:59 PM CT** (note: PRD also quotes 12:00 PM CT in the schedule table on p.4 — both windows were treated as binding).

| Deliverable | Requirement |
|---|---|
| **GitHub Repository** | Forked from OpenEMR. Includes setup guide, architecture overview, deployed link. |
| **`AUDIT.md`** | All audit findings with a 1-page (~500-word) summary detailing key findings. |
| **`USERS.md`** | Target user + list of use cases the agent addresses. (Spec calls it `USER.md` in one place; we used `USERS.md`.) |
| **`ARCHITECTURE.md`** | AI integration plan with technical detail (framework, verification, tradeoffs). Must begin with a 1-page (~500-word) summary. |
| **Demo Video (3–5 min)** | One per submission — showcases work, highlights key decisions. |
| **Eval Dataset** | Test suite with results. Structure and scope are design decisions. |
| **AI Cost Analysis** | Actual dev spend + projected production costs at 100 / 1K / 10K / 100K users. Include architectural changes needed at each level. |
| **Deployed Application** | Publicly accessible. For Early and Final, the agent must work in the live environment. |
| **Social Post** (Final only) | X or LinkedIn — describe project, show the agent, tag `@GauntletAI`. |
| **AI Interview** | Required within 24h after every major submission. |

---

## Hard gates (must pass to progress)

- **Audit-first.** No agent code may be written until `AUDIT.md` exists. Cleared.
- **Deployed URL** must be submitted with every checkpoint.
- **Citations + verification + observability** are not v2. They ship with the first deployed agent.
- **Demo data only.** Synthea synthetic patients. All LLM providers treated as having a signed BAA per cohort guidelines.

---

## Target user (single, narrow — see `USERS.md`)

**Primary care physician at a mid-size group practice** (5–15 PCPs, several thousand active patients, 20-patient day, 15-minute slots, panel scoped to their own patients). Three use cases — no others ship:

- **UC1** Pre-visit brief — synthesize who/why/what-changed in 3–5 lines
- **UC2** Multi-condition reasoning — cross-reference complaint × problem list × meds × labs
- **UC3** Medication safety — verdict (safe / caution / contraindicated) with cited evidence

Out of scope: nursing, billing, patient-portal, ED, cohort queries, order entry, specialist consult, multi-language. Each is a different product.

---

## What this brief locks in (do not relitigate without explicit user decision)

- Single-patient scope. The agent never operates across patients in a single session.
- FHIR-only data access from the agent. Never the legacy `interface/` layer, never direct SQL.
- The agent is a **separate Python service**, not PHP code in OpenEMR. It is an OAuth2 client, same as any third-party SMART app.
- Anthropic Claude Sonnet (currently 4.6) is the architectural primary LLM. OpenAI is a per-turn fallback only.
- Verification is a hard gate, not a soft suggestion in a prompt.

---

## Semester arc

| Week | Focus | Status |
|---|---|---|
| Week 1 (2026-04-21 → 2026-05-04) | Audit, design, single-agent loop, iframe rail, eval, observability, deploy | **Complete.** See `assignments/week1.md`. |
| Week 2 | TBD — assignment not yet released | Pending |
| Week 3+ | TBD per cohort | Not started |

The Week 1 verification + citation contract is **preserved unchanged** through every later week. Anything that would break it does not ship.
