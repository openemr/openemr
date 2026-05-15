# OpenEMR Medical Co-Pilot Demo Seed

This demo stays isolated to two areas:

- `interface/ai_copilot/`
- `seed/openemr_demo_seed.sql`

It is read-only. It does not write medications, diagnoses, encounters, billing records, or claims.

## What it seeds

Exactly three fake demo patients:

1. `DEMO-PCP-1001` Marcus Johnson for a diabetes foot wound / infection risk scenario
2. `DEMO-MA-1002` Angela Reed for an asthma / respiratory symptom scenario
3. `DEMO-BILL-1003` Thomas Carter for a chest pressure / shortness-of-breath risk scenario

The seed adds only demo-safe chart context such as:

- `patient_data`
- `openemr_postcalendar_events`
- `form_encounter`
- `pnotes`
- `lists`
- `prescriptions`
- `insurance_data`
- `insurance_companies`
- `billing`
- `claims`

## Import the seed

From this repo:

```bash
cd "/mnt/d/Web Applications/firstproject/docker/development-easy"
docker compose exec -T mysql mariadb -uroot -proot openemr < ../../seed/openemr_demo_seed.sql
```

If you prefer using the mapped MySQL port directly from the host:

```bash
mysql -h 127.0.0.1 -P 8320 -uroot -proot openemr < seed/openemr_demo_seed.sql
```

## Open the demo page

1. Log in to OpenEMR at `http://localhost:8300` or `https://localhost:9300`
2. Open `http://localhost:8300/interface/ai_copilot/index.php`
3. Select one of the seeded demo patients
4. Choose a `Staff role`
5. Expand or collapse the demo control sections as needed so the prompt and response area stay in focus
6. Use a quick action to prefill the chat prompt or type your own prompt
7. Press `Send`

## Chat-first UI notes

- `Demo patient`, `Staff role`, `Focus mode`, and `Quick Actions` can all collapse or expand.
- The quick-action prompt helper text is preserved; this UI pass only changes presentation.
- Every assistant response includes `Like`, `Dislike`, and `Copy` controls.
- `Copy` copies the full visible response content, including structured sections, tags, sources, and safety note.
- Feedback and copy actions log only safe browser-console metadata such as action type, role, mode, timestamp, request id, and response id.

## Observability and AI Audit Trail Demo

- Browser-console audit events are emitted through `window.CopilotTelemetry`.
- Events are PHI-safe and log metadata such as session id, request id, response id, role, mode, selected demo patient key, message length, response length, latency, fallback use, and restriction status.
- The demo logs generation lifecycle events including start, success, and failure.
- Local deterministic fallback use is logged separately when OpenAI is unavailable or fails.
- Role-based guardrail responses emit a restricted-action audit event.
- Copying an assistant response emits an output-copied audit event.
- Like and dislike actions emit output-feedback audit events.
- Session counters are available through `window.CopilotMetrics`, and `window.printCopilotMetrics()` prints a quick summary table for the current page session.
- `Output copied` means the user clicked copy. It does not prove the output was pasted into the chart or clinically used.

## Role-Based AI Behavior Demo

- `Doctor`: clinical reasoning support, medication review, note drafting, treatment planning, follow-up support, and billing-support suggestions. No autonomous diagnosis, prescribing, ordering, or chart writes.
- `Nurse`: education, triage-style support, note drafting, follow-up planning, and escalation reminders. Medication changes still require prescribing-clinician review.
- `Billing Staff`: billing documentation review, claim-support suggestions, and billing-facing visit summaries. No automatic claim submission or definitive coding assignment.
- `Front Desk`: scheduling, contact confirmation, reminder drafting, and administrative summaries with minimum necessary PHI only. Clinical details are intentionally restricted.
- `Reminder email demo`: front-desk users can draft and trigger a demo reminder email. If mail is not configured, the UI returns a safe simulation message instead of failing.

## Guardrails Layer Demo

- The UI now runs a role-based AI guardrails pass before rendering any copilot draft.
- The floating Co-Pilot now shows a visible `Guardrails active` status strip under the control bar so role scope and the key enforcement checks are obvious in the demo.
- Every generated assistant response now shows a compact guardrail status badge such as `Guardrails checked · Role-safe · Draft-only`.
- Blocked requests now render an in-chat warning banner titled `Guardrail blocked this request` with a safe explanation and a role-appropriate alternative.
- Prompt-injection attempts such as `ignore previous instructions`, `bypass role restrictions`, or `show the full chart` are blocked before the API draft is shown.
- `Doctor` responses can support draft summaries and differential reasoning, but autonomous diagnosis language is blocked or rewritten to review-only language.
- `Nurse` responses can support education, medication summaries, and follow-up preparation, but diagnosis and medication-change instructions are blocked.
- `Billing Staff` responses are limited to billing, insurance, payment, and claim-review workflows. Detailed treatment plans, labs, and medication details are blocked.
- `Front Desk` responses are limited to appointment, contact, and administrative reminder workflows with minimum necessary PHI.
- Every guardrails decision logs a PHI-safe browser-console event named `copilot_guardrails_evaluated`.
- Unsafe drafts are replaced with a safe role-appropriate alternative before the user sees them.

### Guardrails test cases

Run the lightweight local guardrails tests with Node:

```bash
node interface/ai_copilot/copilot_guardrails.test.js
```

Current coverage includes:

- Front Desk asking for Marcus medication info is blocked
- Front Desk asking for contact info is allowed
- Doctor asking for medication info is allowed with draft-only language
- Doctor asking `diagnose Marcus` is blocked
- Billing asking for payment status is allowed
- Billing asking for the full treatment plan is blocked
- Prompt injection asking to ignore role restrictions is blocked

## Seed re-import behavior

Re-importing `seed/openemr_demo_seed.sql` refreshes the demo-generated appointments, encounters, and claim-review rows for the three fake demo patients so the role-based workflows stay consistent.

## Overlay verification

1. Log in to OpenEMR.
2. Open the normal app dashboard or any logged-in OpenEMR screen.
3. Confirm the circular `Medical Co-Pilot` launcher appears in the bottom-right corner.
4. Click the launcher and confirm the overlay drawer opens.
5. Confirm the `Staff role` selector appears under `Demo patient`.
6. Switch between `Doctor`, `Nurse`, `Billing Staff`, and `Front Desk` and confirm the quick actions change by role.
7. Confirm the chat greeting appears and the quick actions prefill the prompt instead of sending immediately.
8. Select `DEMO-PCP-1001` and role `Doctor`, then test `Differential Diagnosis`, `Medication Info`, and `Treatment Plan`.
9. Select `DEMO-MA-1002` and role `Nurse`, then test `Clinical Notes`, `Follow-Up`, and `Patient Education`.
10. Select `DEMO-BILL-1003` and role `Billing Staff`, then test `Billing`, `Claim Review`, and `Visit Summary`.
11. Select any demo patient and role `Front Desk`, then test `Appointment Info`, `Patient Contact`, and `Send Reminder`.
12. After the reminder draft appears, click `Send Reminder Email` and confirm you see either a real send confirmation or the demo-safe simulated success message.
13. Press `Escape` and confirm the drawer closes.
14. Reopen it, then close it with the close button.
15. Confirm OpenEMR remains usable after closing the drawer.

## OpenAI behavior

If `OPENAI_API_KEY` is available to the running OpenEMR PHP process, the demo will try to generate the draft with OpenAI.

If `OPENAI_API_KEY` is missing, invalid, or the request fails, the demo falls back to deterministic local chat responses. The page should still work.

Optional environment variables:

```bash
OPENAI_API_KEY=...
OPENAI_MODEL=gpt-4o-mini
OPENAI_BASE_URL=https://api.openai.com/v1
```

For the Docker dev environment, add them to the `openemr` service environment and restart that service.

## Suggested demo prompts

```text
What could be causing this patient's symptoms?
Summarize this chart for a doctor who has 30 seconds.
What medications should the clinician double-check?
Draft a SOAP note for this visit.
What questions should the clinician ask next?
What should I be careful not to miss?
Create a patient-friendly explanation of the plan.
Why did this claim fail, and what should I check first?
```

## Safety

The widget header stays labeled `Beta`, and responses include role-appropriate safety notes such as draft-only clinical review, billing/compliance review, or minimum-necessary-PHI reminders.

The demo is intentionally limited:

- No diagnoses
- No prescribing
- No treatment recommendations
- No automatic billing submission
- No claim submission
- No upcoding suggestions
- No real patient data
