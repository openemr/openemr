# USERS.md
## Target User and Use Cases for the Clinical Co-Pilot

> **Companion documents:** `AUDIT.md` (current-state findings), `ARCHITECTURE.md` (the build that addresses these cases).
> Every capability built in `ARCHITECTURE.md` traces back to a use case in this file.

---

## 1. The User

**Dr. M., a board-certified internal medicine primary care physician (PCP) at a 4-provider independent ambulatory clinic running OpenEMR 7.x.**

She sees 18 to 22 patients a day in 20-minute slots, plus 1 to 2 same-day add-ons. Her panel is roughly 1,400 patients, skewed older (median age 62) with multiple chronic conditions: type 2 diabetes, chronic kidney disease, gout, hypertension, osteopenia and osteoporosis, atrial fibrillation, prior strokes. Many of her patients were transferred to her practice from other PCPs and carry chart histories spanning 10 to 30 years. The free-text portions of those charts are dense, copy-forwarded, and often internally contradictory.

She is not an early adopter of technology. She uses OpenEMR because her clinic chose it, not because she loves it. She trusts her own clinical judgment more than any tool. She has been burned by clinical-decision-support systems that fired alerts on every prescription and trained her to ignore them. Anything that wastes her time loses her permanently within three uses.

**What she does in the 30 seconds before the agent enters her day:** at 8:25 AM she sits down with coffee, logs in to OpenEMR, and opens her schedule for the day. She has 20 minutes of pre-clinic time (8:30 to 8:50) before the first patient. In that window she opens each chart in turn, scans the problem list and recent encounters, and tries to remember why each patient is coming in.

That 20-minute window is the moment the agent enters. It either earns its place in those 20 minutes or it does not get used.

### Why this user, not "physicians"

The audit-mandated specificity matters because every choice downstream depends on it.

A primary care physician at an independent clinic owns her own time, so improvements to **pre-visit prep** are improvements she captures personally. A salaried hospitalist or emergency-department resident has no equivalent slot. An independent clinic running OpenEMR is the realistic deployment context for this build, so the data model and integration constraints in `AUDIT.md` map to her environment. Older multi-condition patients are exactly the population where the diagnostic and chart-error use cases bite hardest; a pediatric urgent care has neither problem at the same magnitude. She has tolerance for batch processing (overnight or 8:30 AM windows) and very little tolerance for synchronous waits, which constrains the latency budget cleanly.

If the agent works for her, it generalizes. If it tries to work for everyone, it works for no one.

---

## 2. Her Workflow Today (the as-is)

A typical day:

1. **08:25** Logs in. Opens schedule.
2. **08:30 to 08:50** Pre-clinic prep. Opens each chart, eyeballs problem list, recent labs, last visit note. Mentally flags "this one looks complicated." She does not have time to actually read the longitudinal note history.
3. **09:00 to 12:00** Sees patients in 20-minute slots. Charts during the visit using OpenEMR's encounter forms. Often falls 1 or 2 patients behind.
4. **12:00 to 13:00** "Lunch." Actually finishes morning charting, returns 4 to 8 patient portal messages, signs lab results.
5. **13:00 to 17:00** Afternoon clinic.
6. **17:00 to 18:30** Closes the day's charts, signs orders, refills prescriptions, replies to messages.

**Where the system fails her today.**

She cannot reliably catch a diagnostic mismatch buried in the chart history. The Stage 0 narrative I worked from (gout already documented, doctors at three hospitals diagnosed infection) is the canonical example. The data was present. The cognitive load of reconciling it during a 20-minute visit is too high.

She inherits chart errors from prior providers (e.g. "osteoporosis" coded before "osteopenia" in time order, biologically improbable) and has no automated way to flag them. They propagate into every future encounter and bias every downstream clinician.

She has no tool that reads the **whole chart** systematically. OpenEMR's UI surfaces what is recent, not what is relevant.

---

## 3. Use Cases

Each use case below states the user's problem, when the agent enters the workflow, what the agent does, what the user does with the output, and **why a conversational agent (rather than a dashboard, sorted list, or chart-view improvement) is the right shape**.

### 3.1 Use Case A: Pre-visit Diagnostic Cross-Check

**The problem.** When a patient on Dr. M.'s schedule presents (in their portal pre-visit form, in the morning huddle, or in the previous note) with a new symptom, Dr. M. needs to know which existing items in the chart could plausibly explain that symptom. Today she does this from memory and from a 30-second eyeball of the problem list. The gout-misdiagnosed-as-infection narrative is exactly this failure mode.

**When the agent enters.** During the 8:30 to 8:50 pre-visit window, for each patient on the day's schedule who has a documented presenting concern.

**What the agent does (the systematic comparison).** This is my empirical insight from the Stage 0 experiment I ran: when a model (or a clinician) is forced to compare each presenting symptom against each candidate explanation one pair at a time, the right answer surfaces. When the model is asked to "diagnose" holistically, it anchors on the most salient feature and misses the rest.

For each presenting symptom S the agent does this:

1. Pull the patient snapshot (problems, medications, allergies, recent labs and vitals, recent free-text notes).
2. Generate the cross-product of (S, existing_problem) and (S, recent_medication_side_effect) and (S, recent_lab_abnormality).
3. For each pair, ask the model: "Could symptom S plausibly be an expression of finding F? Cite the mechanism. Score the prior likelihood low / moderate / high. Cite the chart evidence."
4. Run a verifier pass that filters out unsupported claims and ranks results.
5. Produce a short ranked report ("Top 3 candidate explanations from the existing chart, with provenance"), plus an explicit "what is missing" section ("no recent uric acid measured; would resolve gout vs. infection").

**What the user does with the output.** Dr. M. reads the ranked report in the 60 seconds before she opens the patient's door. She holds the top 1 or 2 candidates in mind during the visit. She orders the differentiating test if needed.

**Why an agent, not a dashboard.**

The output is patient-specific reasoning, not a fixed metric. A dashboard would have to anticipate every (symptom × diagnosis) pair in advance, which is intractable. Dr. M. needs to ask follow-ups in the room ("the toe is hot and swollen; what would change your ranking?"), which requires multi-turn context within the same session. The output's value is the **explanation** ("gout because the chart shows a 2019 podagra episode with elevated uric acid, which fits the current presentation"), not a number, and explanation is a chat affordance. A sorted list cannot express conditional reasoning ("if uric acid is currently elevated, gout moves to top; if not, refer back to infection").

**Why this is not "answer questions about a patient."** The agent runs a **specific named procedure** (pairwise symptom-to-finding comparison) on entering the chart, with a structured output. The chat surface is for the follow-up clarifications that the procedure cannot anticipate.

### 3.2 Use Case B: Chart-Error / Conflict Detection

**The problem.** Dr. M. inherits long charts from prior providers that contain biologically or temporally implausible sequences. My mother's chart is the canonical example I worked from: "osteoporosis" coded before "osteopenia," which is biologically backward. Other examples I've seen include an allergy listed as "penicillin" while a course of amoxicillin was prescribed and tolerated three months later, and "diabetes type 2" coexisting with a Hemoglobin A1c (HbA1c) trajectory that has been < 5.7 for 8 years. Today Dr. M. has no recourse to surface or correct these errors at scale, and they bias every future clinician's reasoning.

**When the agent enters.** As an overnight batch job that scans the next day's panel of charts, plus on demand from the chat surface ("Run a chart-conflict scan on Mr. K.").

**What the agent does (same comparison engine, different prompt).**

1. Pull the patient snapshot.
2. Generate the cross-product of (datapoint_i, datapoint_j) for the documented findings, medications, allergies, and labs.
3. For each pair, ask the model: "Comparing finding A and finding B, is there a temporal, biological, or pharmacological inconsistency that suggests one of the two is a charting error? Cite the rule. Cite the chart evidence."
4. Run a verifier pass against a curated rule store (e.g. "osteopenia normally precedes osteoporosis," "tolerated re-exposure invalidates a documented allergy," "type 2 diabetes diagnosis with sustained HbA1c < 5.7 without medication suggests prediabetes miscode").
5. Produce a chart-error report with severity levels and proposed clarifying actions, attached to the chart for clinician review.

**What the user does with the output.** During pre-visit prep Dr. M. sees a small "chart hygiene" line ("2 likely errors flagged"). She opens it for 30 seconds. If she agrees, she clicks "amend" to add a clarifying problem-list note (the agent does not edit the underlying record). If she disagrees, she dismisses with one click and the agent does not flag that pair again for 12 months.

**Why an agent, not a rule engine.**

The space of plausible chart errors is open-ended. A rule engine catches a finite list (drug-drug interactions, allergy contradictions). The agent generalizes to "improbable progression" reasoning that a rule engine cannot enumerate. The user needs explanation per flag ("osteoporosis followed by osteopenia in the chart timeline is biologically backward; osteoporosis does not regress to osteopenia"), and explanation is again a chat affordance. The follow-up question ("but what if she took zoledronic acid in between?") is the high-value back-and-forth that a static report cannot do.

### 3.3 Use Case C: Mid-Visit Clarifier (smaller, optional)

**The problem.** Mid-visit, Dr. M. asks a focused question ("when was her last colonoscopy?", "did the orthopedist's note from last August recommend physical therapy or injections?"). Today she searches OpenEMR's UI manually, breaking eye contact with the patient.

**When the agent enters.** Voice or text query during the visit, response in under 5 seconds.

**What the agent does.** Tool-use against the same patient snapshot; cites the source row and date.

**Why an agent, not a search box.** OpenEMR's search exists. The agent's value is resolving natural language to structured retrieval ("last colonoscopy" maps to the right combination of `procedure_order` rows, free-text notes, and external referral documents) and providing citations the clinician can verify in one click.

### 3.4 What this build does **not** do

- **No autonomous order entry.** The agent never writes orders, prescriptions, or chart edits. Verification plus clinician-in-the-loop is mandatory; see `ARCHITECTURE.md` Section on verification.
- **No cross-patient analytics.** "Who in my panel has the worst HbA1c?" is a population health question, a different product.
- **No patient-facing surface.** The agent talks to clinicians, not patients. A patient-facing version would have a different consent and verification model.
- **No replacement of clinical judgment.** Every output is a candidate ranked list with explicit provenance, not a recommendation.

---

## 4. Definition of Useful (the bar)

Dr. M. continues using the agent if and only if all three are true after the first month:

1. **Pre-visit prep takes less time, not more.** Median pre-visit prep on a 20-patient day drops from 20 minutes to 12 minutes.
2. **At least one diagnostic cross-check per week surfaces a finding she had not considered, and she agrees it was relevant.** This is the "would have caught the gout case" target.
3. **Chart-error flags have precision above 80% on her review.** False-positive flags are the fastest way to lose her trust.

These targets drive the eval suite in `ARCHITECTURE.md`.

---

## 5. Anti-Personas (who this is **not** for, in version 1)

- Hospital inpatient teams (different time pressure, different data sources, different EHR).
- Specialists doing single-organ-system reasoning (the cross-comparison value is highest when the chart is broad).
- Health systems with a separate clinical-decision-support stack already deployed (different integration shape).
- Patients (different consent and verification model).
