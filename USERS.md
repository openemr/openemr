# USERS.md — Target User & Use Cases

## Target User

**Primary Care Physician at a mid-size group practice (5–15 PCPs, several thousand active patients).**

The physician sees ~20 patients per day in 15-minute slots. The patient pool is shared across the group, but **each physician only has access to patients on their own panel** — access to other physicians' patients requires a request workflow. The patient mix is a combination of established patients (chronic disease management) and same-day acute or transferred-in visits.

### Why this user, narrowly

Three properties of the role drive every architectural decision:

1. **The 90-second window between patient rooms is the binding constraint.** There is no time before the day to read every chart and no time during a visit to scan EHR tabs. Whatever the agent surfaces has to be ingestible while the physician is walking from one room to the next.

2. **Per-physician access boundaries must be honored.** The agent must never surface data about a patient the requesting physician is not currently authorized to see — regardless of how the question is phrased.

3. **Multi-morbidity is the norm, not the exception.** Established patients commonly carry 4–6 active chronic conditions (HTN, T2DM, CKD, CHF, COPD, depression). Today's chief complaint is rarely independent of the problem list, and a new prescription rarely lacks an interaction risk. Rote chart review misses these connections; the physician needs reasoning, not retrieval.

### Workflow — the moment the agent enters the day

```
8:50 AM   Schedule loads. 11 morning patients listed.
          The physician does NOT pre-read charts — there is no time.

9:00 AM   First visit begins. Standard 15-min slot, exits at 9:14.

9:14 AM   Door closes. Walking down the hall to the next room.
          ~90-second window. The Co-Pilot panel opens for Patient #2.

9:14:05   Pre-visit brief is already streaming:
          - Who the patient is (62M, established since 2019)
          - Why they're here today (3-week cough, scheduled this morning)
          - 3 chronic conditions, 2 changed since last visit
          - 1 flagged interaction risk on current med list

9:15:30   The physician walks into the next room oriented.

9:30      Repeat for Patient #3.
```

Between visits the physician may also ask follow-ups:
- "Is this cough likely related to his ACE inhibitor?"
- "If I add azithromycin, what should I watch for?"

This is where the **conversational** shape matters. A dashboard cannot answer those questions; a search bar cannot synthesize the answer; a static chart view cannot reason across drug interactions.

**Scope clarification:** the 60–90 second pre-room window is the *binding time constraint* that makes a conversational agent (rather than a dashboard or sorted list) necessary — but the three use cases below operate across the full point-of-care context, not only the pre-visit moment. UC1 is a synthesis brief before the room; UC2 is cross-source reasoning during the visit; UC3 is a rule-checked safety review at the prescribing moment. The agent is a single-patient point-of-care assistant whose shape is determined by the time pressure that bookends the encounter, not a pre-visit summarizer.

---

## Use Cases

Every agent capability we build maps to one of these. If a feature does not trace to a use case here, we do not build it.

### Use Case 1 — Pre-Visit Brief: "Who is this patient, why are they here today?"

**Trigger:** The physician opens the Co-Pilot panel for the next patient on their schedule.

**Agent does:**
1. Pulls patient demographics, problem list, current medications, and the documented visit reason.
2. Identifies what has changed since the patient's last encounter (new diagnoses, new meds, abnormal labs, missed appointments).
3. Returns a synthesized **3–5 line brief** — not a data dump.
4. Cites every claim back to a specific record (encounter ID, lab ID, medication ID).

**Why an agent (not a dashboard):**
A dashboard would either show too much (whole chart) or too little (last visit summary). What's relevant depends on the patient: for a stable diabetic, "no changes" is the correct brief; for a new transfer, the salient facts may be three years old. Selecting *what matters today* requires reasoning over the chart, which is what an LLM with tool access does. A static layout cannot do this.

**Failure mode the agent must handle:**
The patient's most recent prior encounter is older than 12 months (or no prior encounter exists at all — new patient). Naively comparing "current state vs. prior encounter" with no time guardrail produces a misleadingly long change list, or worse, a confidently empty one. The agent must detect this case and surface it explicitly: *"Most recent prior visit is 14 months old — review for relevance"* or *"No prior encounter on file — this is a new-patient brief."* Eval suite must include both stale-prior and no-prior cases.

### Use Case 2 — Multi-Condition Reasoning: "Is today's complaint related to anything on the problem list?"

**Trigger:** The physician asks: *"He's complaining of dizziness — is this related to anything I should know about?"*

**Agent does:**
1. Cross-references the chief complaint against all active conditions in the problem list.
2. Cross-references against current medications (e.g., antihypertensives → orthostatic hypotension).
3. Pulls the most recent relevant labs/vitals (e.g., last BP reading, last A1C).
4. Returns a hypothesis-ranked answer with cited evidence.
5. Flags anything it cannot verify (e.g., "no BP recorded in last 90 days").

**Why an agent (not a dashboard):**
This is a *reasoning task across multiple data sources* with a question specific to today's visit. A dashboard cannot anticipate the question. A search bar can find data but can't connect "dizziness" to "lisinopril" to "last BP 92/58." This is exactly what an LLM with tool calls is for.

**Failure mode the agent must handle:**
A required data source is missing — for example, no BP recorded in the last 90 days, no orthostatic vitals, or the medication list is stale (last updated > 90 days ago). The agent must not produce a hypothesis that depends on missing data and present it as supported. Required behavior: *"Possible orthostatic component from lisinopril, but the most recent BP is from 8 months ago — recommend in-room measurement before attributing."* Eval suite must include cases where the connecting data point is absent and verify the agent flags the gap rather than papering over it.

### Use Case 3 — Medication Safety: "I'm about to prescribe X — is that safe given everything else?"

**Trigger:** The physician asks: *"I'm thinking azithromycin for the cough. Any concerns?"*

**Agent does:**
1. Fetches the patient's current medication list.
2. Checks each against the proposed prescription for known interactions, contraindications, and dosage flags (using a domain-rule layer — not LLM judgment alone).
3. Reviews recent labs that affect drug safety (e.g., QTc-prolonging drugs + recent EKG, renal dosing + recent creatinine).
4. Returns a clear **safe / caution / contraindicated** verdict with evidence.
5. **Refuses to give a verdict** if any required data is missing — explicitly states what's missing.

**Why an agent (not a dashboard):**
Drug-interaction checkers exist as flat tools, but they do not incorporate the patient's labs, conditions, or recent vitals into the verdict. The reasoning is: drug + drug + lab + condition + dose. That cross-source synthesis is the agent's job. The conversational shape also lets the physician ask "what about clarithromycin instead?" without re-entering context.

**Failure mode the agent must handle:**
The medication list contains entries with NULL `rxnorm_drugcode` (the audit found this is inconsistently populated — some prescriptions have only free-text drug names). Interaction lookups against canonical codes will silently miss these records. The agent must detect when an active medication lacks a structured code and surface it: *"Patient is also on 'enalapril 10mg' (no canonical code) — interaction analysis below excludes this medication. Verify manually."* Silent omission of an unmatched drug is treated as a verification failure. Eval suite must include cases with mixed coded + free-text medications.

---

## Out of Scope

Each non-goal below would require redesign of the verification layer, the prompt structure, or the data integration — they are not configuration changes. Stating them explicitly scopes the eval suite, the verification rules, and the architecture honestly.

| Out-of-scope user / use case | Why it is a different product |
|---|---|
| Nursing workflow (med administration, vitals monitoring, task lists) | Different verification requirement (eMAR cross-reference). Different time-pressure shape (sustained per-shift, not 90s windows). Different question shape (task-oriented, not diagnostic). |
| Billing / coding (ICD-10 assignment, charge capture) | Different data model (CPT, ICD-10 lookups, reimbursement rules). Different liability model (financial, not clinical). |
| Patient portal / patient-facing | Different audience (lay reader). Different consent model. Different output safety constraints. |
| Emergency department / acute care | Different time pressure (seconds, not minutes). Different data freshness requirements (live vitals streams). Different ACL model (treating relationship via active encounter, not panel assignment). |
| Population-level / cohort queries ("find me all my diabetics with elevated A1C") | Different surface area (cohort builder, not single-patient agent). Requires de-identification or population-health BAA scope. |
| Order entry (placing labs, prescriptions, referrals via the agent) | Free-form natural-language order entry is unsafe for medication dosing, lab specimen handling, and routing. Structured forms with validation are the correct shape. The agent surfaces context for orders; it does not place them. |
| Specialist consultation (cardiology, oncology, oncology workflows) | Different domain depth. Different question types (procedural, prognostic) that this agent's verification approach does not cover. |
| Multi-language clinical conversation | Translation introduces a verification surface this agent does not handle. English clinical content only for v1. |

Acknowledging these explicitly is a feature, not a hedge — it prevents the agent's eval suite from being asked to score scenarios it was never built for.

---

## What This Document Locks In

- **The agent only ever operates on a single patient at a time.** No cross-patient queries, no "find me all my diabetics with elevated A1C." Those are dashboard problems, not agent problems.
- **The agent never gives clinical recommendations beyond what is grounded in the chart.** It surfaces, cross-references, and flags. It does not decide.
- **The agent refuses confidently when data is missing.** A wrong answer is worse than no answer.
- **Access boundaries are enforced on every request** — the agent cannot surface data the requesting physician is not authorized to see. This is an architectural requirement, not a feature; details are addressed in ARCHITECTURE.md.
- **Multi-turn conversation is required.** Use Cases 2 and 3 are inherently follow-up-shaped. Single-shot Q&A would force the physician to re-explain context every turn — a non-starter in 90 seconds.
- **Tool chaining is required.** Use Case 3 chains: get-meds → check-interactions → get-labs → synthesize-verdict. This is not optional.

ARCHITECTURE.md must trace every component back to one of these three use cases. If a component cannot be traced, it does not ship.

---

## Trace-Back to AUDIT.md

Every use case above depends on findings from `AUDIT.md`. The agent's architecture must address each dependency explicitly — these are the source of truth for the next deliverable.

| Use Case | Audit Finding It Depends On | Architectural Implication |
|---|---|---|
| UC1 (pre-visit brief) | §1.3 — no patient-provider scoping at the data layer | Agent must scope patient lookups to the requesting physician's panel; cannot trust the FHIR API to do this |
| UC1 | §4.3 — `form_encounter.reason` is unstructured longtext; §4.5 — sensitivity field nullable | Agent must attribute every claim to a specific row (encounter / lab / med id); apply content-based sensitivity heuristics |
| UC2 (multi-condition reasoning) | §1.4 — no PHI de-identification before LLM | Agent must pseudonymize patient identifiers before sending clinical data to the LLM step |
| UC2 | §1.2 — ACL enforced at the call-site, not in the service layer | Agent must call `AclMain::aclCheckCore('patients', 'med'/'lab', $authUser)` for each data type before fetching |
| UC3 (medication safety) | §4.2 — `prescriptions.drug` free text, `rxnorm_drugcode` inconsistently populated | Interaction lookups must handle both coded and uncoded medications; surface uncertainty when canonical match fails |
| UC3 | §3.2 — three parallel write paths to `prescriptions` (PrescriptionService, eRxStore, drug dispensing) | Agent reads the prescription record but cannot assume any particular path created it; field completeness varies |
| All UCs | §5.5 — PHI fields (SSN, addresses, phone) stored in plaintext | Agent must minimize what enters the LLM prompt — clinical fields only, identifiers replaced with session-scoped tokens |
| All UCs | §1.3 — `sensitivities|high` gating depends on a nullable field | Agent must not assume sensitivity tagging is reliable; must apply content-based heuristics for surfacing mental health, HIV, substance abuse data |
| All UCs | §5.3 — `api_log` stores full request/response bodies including PHI | Agent's logging path must redact PHI before persistence; verification logs separate from clinical content logs |
