# USERS.md — Target Users for the OpenEMR AI Layer

## Purpose

This file defines the first target users for a conversational AI layer in OpenEMR.

OpenEMR is an electronic health record and practice-management system. It includes patient records, scheduling, billing, prescriptions, documents, portal workflows, REST APIs, FHIR APIs, SMART on FHIR, OAuth2/OpenID Connect, and role-based access control.

The AI layer should not replace dashboards, reports, search, or clinical review. It should help only when a user needs a fast explanation, summary, draft, or follow-up question across multiple parts of the chart.

## Rule for using conversational AI

Use conversational AI when the user needs to ask a messy, context-heavy question in plain language.

Do not use conversational AI when a normal dashboard, report, form, or search bar is enough.


## 1. Primary care physician seeing 20 patients per day

### User

A family medicine or internal medicine physician in a busy outpatient clinic.

### Use case

The physician asks:

> “Summarize my next patient. Focus on recent labs, medications, chronic conditions, and open follow-ups.”

### Why conversational AI is better than a dashboard, report, or search bar

A dashboard shows separate pieces of information. A report lists data. A search bar only works when the physician knows exactly what to search for.

Conversational AI is better here because it can combine labs, medications, notes, problems, and follow-ups into one short explanation. The physician can also ask follow-up questions like:

> “What changed since the last visit?”

### Safety limits

- The AI output is a draft summary only.
- It must cite chart sources.
- It must not diagnose or recommend treatment on its own.
- The physician must review everything.

---

## 2. Medical assistant rooming 25–35 patients per day

### User

A medical assistant who rooms patients, checks vitals, confirms medications, updates pharmacy information, and prepares the chart.

### Use case

The assistant asks:

> “What do I need to confirm before the provider sees this patient?”

### Why conversational AI is better than a dashboard, report, or search bar

A dashboard can show missing fields, but it may not explain what matters for this visit. A report is too static. A search bar is slow because the assistant may not know what is missing.

Conversational AI is better because it can create a short rooming checklist based on the appointment reason and chart context.

### Safety limits

- The AI must stay within the assistant’s permissions.
- It must not make clinical decisions.
- It must not change medications or diagnoses.
- It should only suggest what to confirm.

---

## 3. Billing specialist handling 75–150 claims per day

### User

A billing specialist who reviews rejected claims, missing documentation, coding issues, and insurance problems.

### Use case

The billing specialist asks:

> “Why did this claim fail, and what should I check first?”

### Why conversational AI is better than a dashboard, report, or search bar

A dashboard can show claim status. A report can show rejection codes. A search bar can find a claim.

Conversational AI is better because it can explain the rejection in plain language and connect it to possible missing documentation, insurance issues, or encounter problems.

### Safety limits

- The AI must not submit claims automatically.
- It must not suggest upcoding.
- It must cite the claim and encounter information.
- A human must review the final billing decision.

---

