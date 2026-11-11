# Referral Workflows

What kinds of referral OpenEMR supports, how each one is sent, tracked and closed, and where
the limits are. Each workflow below has a worked example.

Applies to OpenEMR 8.6.0 (development). The FHIR Task resource shipped in 8.5.0; referral
tracking, recipient notices and limited API clients are new in 8.6.0.

## Contents

- [At a glance](#at-a-glance)
- [How a referral is modeled](#how-a-referral-is-modeled)
- [Setup](#setup)
- [Where a referral stands](#where-a-referral-stands)
- [Workflows](#workflows)
  - [1. Inside the practice](#1-inside-the-practice)
  - [2. Outside, by paper, fax or phone](#2-outside-by-paper-fax-or-phone)
  - [3. Outside, by Direct message](#3-outside-by-direct-message)
  - [4. Outside, recipient has only an email address](#4-outside-recipient-has-only-an-email-address)
  - [5. Outside, recipient connected through the FHIR API](#5-outside-recipient-connected-through-the-fhir-api)
  - [6. Community and social-care referrals](#6-community-and-social-care-referrals)
  - [7. Declined, re-sent or sent elsewhere](#7-declined-re-sent-or-sent-elsewhere)
  - [8. Referral created by another system](#8-referral-created-by-another-system)
  - [9. Referrals we receive](#9-referrals-we-receive)
- [Worklist and reporting](#worklist-and-reporting)
- [Not supported](#not-supported)
- [Reference](#reference)

## At a glance

| Referral | Recipient hears by | Status comes from | Closed by |
|---|---|---|---|
| Inside the practice (another provider here) | Seeing it in the chart; optional email | Staff | Reply typed in Counter-Referral |
| Outside, no connection | Printed referral, fax or phone | Staff | Reply typed in Counter-Referral |
| Outside, Direct address | Referral C-CDA by Direct | Staff; Direct reply filed with the referral | Staff confirm the Direct reply, or type the reply |
| Outside, email only | Plain email, no patient details | Staff | Reply typed in Counter-Referral |
| Outside, FHIR API client | Its own Task queue | The recipient, through `PUT /fhir/Task` | Recipient marks the Task completed, or a reply |
| Community organization (SDOH) | Any of the above | Any of the above | Any of the above, plus services listed in the Task's output |

Every row starts the same way: a referral saved on the patient's **Transactions** screen.

## How a referral is modeled

```
Transactions > Referral form (LBTref)
        |
        |  read-only
        v
FHIR ServiceRequest  (category 3457005 "Patient referral")
        ^
        |  focus
FHIR Task            (code fulfill, one per recipient, owner = Refer To)
        |
        |  output
        v
Reply: consult note (LOINC 11488-4), services delivered, results
```

- **The referral form is the record.** Refer By, Refer To, Referral Date, Reason, Referrer
  Diagnosis, Requested Service, Risk Level, and the Counter-Referral (reply) fields.
- **The ServiceRequest is the same referral, read through FHIR.** It cannot be created or
  changed through FHIR; `POST /fhir/ServiceRequest` makes a procedure order, not a referral.
- **The Task is where the referral stands.** OpenEMR makes it when the referral is saved.
  The recipient, or staff, move it along.
- **SDOH is one kind of referral, not a separate model.** A food bank and a cardiologist are
  tracked the same way.

| Form field | ServiceRequest | Task |
|---|---|---|
| Refer By | `requester` | `requester` |
| Refer To | `performer` | `owner` |
| Referral Date | `authoredOn` | |
| Reason | `note` | |
| Referrer Diagnosis | `reasonCode` (coded for entries like `ICD10:I10`, otherwise text) | |
| Requested Service | `code` (falls back to 3457005) | |
| Reply Date | `status` = completed | status = completed, reply in `output` |

## Setup

**Globals**

| Global | Where | Default | What it does |
|---|---|---|---|
| Track Referrals with FHIR Tasks (`fhir_referral_tasks`) | Config > Connectors | Off | Saving a referral makes a Task for Refer To. Needed for everything below except plain paper tracking. |
| Notify Referral Recipients (`fhir_referral_notify_recipient`) | Config > Connectors | Off | Tells the recipient about a new Task, by Direct or email. Needs the global above. |
| Referrals Overdue After (Days) (`referral_overdue_days`) | Config > Report | 14 | An open referral shows as overdue this many days after its Referral Date. 0 turns it off. |

**When a Task is made**

Saving a referral makes a Task for Refer To only when all of these are true:

- Task tracking is on.
- The referral has no Reply Date. An answered referral never gets a new Task.
- Refer To can own a Task (the table below).
- Refer To does not already have a Task for this referral that still counts, whatever its status.

A referral that missed its Task gets one the next time it is saved with all four true. For a
recipient that could not own one, fix the recipient first, then save the referral:

- A provider or address book practitioner: add the NPI.
- An External Organization: add the Organization name, or a contact name.

**Who can own a Task**

FHIR must serve Refer To as a Practitioner or an Organization:

| Refer To is | Needs | Served as |
|---|---|---|
| A provider with a login | An NPI | Practitioner |
| Address book: External Provider, Specialist, or Lab Service | An NPI | Practitioner |
| Address book: External Organization | A name (Organization, or the contact's name) | Organization |
| Anything else | | No Task. The referral is still saved and tracked by its Reply Date. |

**For notices**, the address book entry needs a Direct address (the **Trusted Email** field)
or an email address. Direct wins when it has both.

## Where a referral stands

The referral screen, the patient's Transactions list and the Referrals report all show one
status.

| Shown | Means | Counts as |
|---|---|---|
| Open | No Task (tracking off, or Refer To can't own one) and no reply | Open |
| Draft, Requested, Received, Accepted, Ready, In Progress, On Hold | The recipient's Task status | Open |
| Replied | A Reply Date is recorded | Completed |
| Completed | The Task is completed | Completed |
| Rejected, Failed, Cancelled, Entered in Error | The recipient's answer, or a correction | Closed |

- **Overdue** is added to an open referral past the overdue days.
- **A reply closes an open Task.** With tracking on, saving a Reply Date files the reply as a
  Consult note and completes the recipient's Task if it is still open. A Task the recipient
  already completed, rejected, failed or cancelled keeps its status, and no document is filed;
  the reply stays on the referral. The referral then shows Replied, or Completed if the
  recipient had completed the Task.
- **If the reply cannot be filed, the Task stays open.** That happens when the Documents
  category **Medical Record** is missing, or the document cannot be saved. The Reply Date is
  still saved, so the referral shows Replied while the recipient's Task is still open. The
  failure is in the application log. Fix the cause, then save the referral again while the Task
  is open; that save files the reply and completes the Task.
- **Only the current recipient's latest Task counts.** If Refer To changes, the earlier
  recipient's Task is cancelled and kept as a record.
- **Through FHIR**, the ServiceRequest reads `active`, `completed`, `revoked` (Task
  cancelled) or `entered-in-error`.
- **The referrer is told.** When a Task is marked accepted, rejected, completed, failed or
  cancelled through the API, Refer By gets a message in their inbox, type Referral. No message
  is sent when tracking is off, or when Refer By is inactive or has no username.

## Workflows

### 1. Inside the practice

One provider refers a patient to another provider in the same practice.

**Example.** Dr. Lee, primary care, refers a patient to Dr. Shah, the practice's
endocrinologist, for uncontrolled diabetes.

1. Open the patient, **Transactions > Add**, type **Referral**.
2. Set **External Referral** to No. Refer To then lists only the practice's providers.
3. Fill in Referral Date, Refer By (Dr. Lee), Refer To (Dr. Shah), Reason, and save.
4. With Task tracking on and an NPI on Dr. Shah's user, the referral shows **Requested**.
5. After the visit, staff open the referral, fill in the **Counter-Referral** tab (Reply
   Date, Reply From, findings, recommendations) and save.
6. The reply is filed in the patient's documents as a Consult note, the Task completes, and
   the referral shows **Completed**.

Limits:

- There is no Accept or In Progress button inside OpenEMR. An inside referral goes from
  Requested straight to Completed, unless something updates its Task through the API.
- Without an NPI, or with tracking off, it shows **Open** and then **Replied**.

### 2. Outside, by paper, fax or phone

The recipient has no electronic connection. Staff track the referral by hand.

**Example.** A patient is referred to an orthopedic group that takes referrals by fax.

1. Save the referral with External Referral set to Yes and the group as Refer To.
2. From the Transactions list, **Print** the referral and fax or hand it over. The printed
   form comes from `sites/<site>/referral_template.html`.
3. The referral sits on the worklist as open, and as overdue after 14 days.
4. When the consult letter arrives, staff record it in Counter-Referral and save. The
   referral is closed.

This works with every global off. Turn Task tracking on and the same steps also give the
referral a Task, which costs nothing and makes it ready for workflows 3 to 5 later.

### 3. Outside, by Direct message

The recipient has a Direct address. Needs Direct messaging (phiMail) set up, the Care
Coordination module, and both referral globals on.

**Example.** A patient is referred to a cardiology practice whose address book entry has a
Direct address.

1. Save the referral. A Task is made, and the recipient is sent the **Referral C-CDA** by
   Direct. It carries the referral sections only: allergies, medications, problems, plan of
   care and reason for referral. It is not the whole chart.
2. The referral screen shows how the recipient was told, and whether the Direct message was
   delivered. A failed send shows there too.
3. The cardiologist's system replies to that message. OpenEMR matches the reply by its
   `In-Reply-To` or `References` header and files it in the patient's chart with the
   referral, not in the Unassigned queue.
4. Staff open the referral, read the reply, and press **Confirm it completes the referral**.
   The reply's documents become the Task's output, the Reply Date is set, and the referral
   shows **Completed**.

Things to know:

- **A matched reply never completes the referral by itself.** It may be a question or a
  refusal. Staff confirm.
- **A reply that answers nothing we sent** stays in the Unassigned flow, as before.
- **Sending a C-CDA by hand** from the Care Coordination send form also links to a referral.
  With one patient selected, staff choose which referral; it defaults to the most recent.
- **A plain save never re-sends.** The recipient is told when the Task is made and on
  Re-send, nothing else.
- **The notice global is off by default** because a referral saved with the wrong Refer To
  sends that C-CDA to the wrong recipient.
- Not tested against a production HISP. Reply matching depends on the recipient's system
  putting our message id in its reply headers.

### 4. Outside, recipient has only an email address

Same as workflow 2, with one addition: the recipient gets a plain email. That needs **both**
globals on, Task tracking and Notify Referral Recipients, and a save that makes a Task (see
[when a Task is made](#setup)). The notice global by itself sends nothing, and a recipient that
cannot own a Task gets no email.

**Example.** A patient is referred to a physical therapy office with an email address and no
Direct address.

1. Save the referral. The office is emailed that the practice sent a referral, with the
   practice's name and phone number. **The email has no patient details.**
2. The office calls or otherwise collects the referral.
3. Staff record the reply in Counter-Referral when it arrives.

The email is sent from the **Notification Email Address** global, through the email
background service. If the entry has no address at all, the referral shows that the recipient
could not be told.

### 5. Outside, recipient connected through the FHIR API

The recipient's system reads its referrals and reports progress itself, so status no longer
travels by phone.

**The API does not tell the recipient who the patient is.** The Task and the ServiceRequest name
the patient only by a FHIR id, with no name or date of birth, and a limited client cannot read
Patient. The recipient still needs the patient's details another way: the Referral C-CDA by
Direct (workflow 3), the printed referral, or a call. Use this workflow for status and results,
alongside one of those.

**One-time setup**

1. Turn on Task tracking.
2. Make sure the recipient's address book entry can own a Task (see Setup).
3. Register an API client for the recipient with `user/Task.read`, `user/Task.write` and
   `user/ServiceRequest.read` (or the `system/` equivalents).
4. On the client's admin page (Admin > System > API Clients), set **Limit to Address Book Entry**
   to the recipient's entry.

A limited client can only do this, whatever scopes it holds:

| Call | Reaches |
|---|---|
| `GET /fhir/Task`, `GET /fhir/Task/{id}` | Tasks the entry owns. Others read as 404. |
| `PUT /fhir/Task/{id}` | The same Tasks. It cannot give one to anyone else. |
| `GET /fhir/ServiceRequest`, `GET /fhir/ServiceRequest/{id}` | Referrals it is the current recipient of and holds a Task for. No procedure orders. |
| Anything else | 403. That includes Patient, Practitioner, Organization and DocumentReference. |

**Example.** A dermatology clinic works its queue.

Find new referrals:

```bash
curl 'https://emr.example.org/apis/default/fhir/Task?status=requested' \
  -H 'Authorization: Bearer <token>'
```

Read the referral a Task points at (`Task.focus`):

```bash
curl 'https://emr.example.org/apis/default/fhir/ServiceRequest/<referral-uuid>' \
  -H 'Authorization: Bearer <token>'
```

Every update follows the same three steps: read the Task, change it, and send the whole Task
back with the version just read. This uses `jq`; `meta.versionId` is the version for `If-Match`.
Read the Task again before each update, because every update changes the version.

Set up once. The files hold patient information, so keep them in a private directory and remove
it when done:

```bash
BASE='https://emr.example.org/apis/default/fhir'
TASK="$BASE/Task/<task-uuid>"
WORK=$(mktemp -d) && cd "$WORK" && WORK=$PWD || echo 'Stop: no private directory'
pwd
```

Stop if that prints the warning, or if `pwd` is not the new directory: the commands below would
write `task.json` and `update.json` wherever you are.

Accept it:

```bash
curl -s "$TASK" -H 'Authorization: Bearer <token>' > task.json
jq '.status = "accepted"' task.json > update.json
curl -X PUT "$TASK" \
  -H 'Authorization: Bearer <token>' \
  -H 'Content-Type: application/fhir+json' \
  -H "If-Match: W/\"$(jq -r '.meta.versionId' task.json)\"" \
  --data @update.json
```

Decline it instead, with a reason. Only the `jq` line differs:

```bash
curl -s "$TASK" -H 'Authorization: Bearer <token>' > task.json
jq '.status = "rejected" | .statusReason = {text: "Not accepting new patients until January"}' \
  task.json > update.json
curl -X PUT "$TASK" \
  -H 'Authorization: Bearer <token>' \
  -H 'Content-Type: application/fhir+json' \
  -H "If-Match: W/\"$(jq -r '.meta.versionId' task.json)\"" \
  --data @update.json
```

Finish it later, with a note back. An update replaces the Task's outputs, so add the note to the
outputs just read instead of overwriting them:

```bash
curl -s "$TASK" -H 'Authorization: Bearer <token>' > task.json
jq '.status = "completed" | .output = ((.output // []) + [{
      type: {coding: [{system: "http://loinc.org", code: "11488-4", display: "Consult note"}]},
      valueString: "Seen 2026-11-20. Benign nevus, no treatment needed. Recheck in 12 months."
    }])' task.json > update.json
curl -X PUT "$TASK" \
  -H 'Authorization: Bearer <token>' \
  -H 'Content-Type: application/fhir+json' \
  -H "If-Match: W/\"$(jq -r '.meta.versionId' task.json)\"" \
  --data @update.json
```

When finished, remove the working files:

```bash
cd / && rm -rf "$WORK"
```

**A note returned as text is not shown in OpenEMR.** The referral page and the inbox message
show the status, not the Task's output. Staff see Completed and nothing else, so someone must
read the Task with a FHIR client to get the text. When staff have no such client, send the
report by Direct (workflow 3) or have staff record it in Counter-Referral.

What the referring practice sees: the status changes on the referral screen and worklist,
and Refer By gets an inbox message for accepted, rejected, completed, failed and cancelled.

Rules the API enforces:

- **Final statuses stay final.** completed, rejected, failed and cancelled can only become
  `entered-in-error`. Before that, the recipient may move between requested, received,
  accepted, in-progress and on-hold in any order.
- **`If-Match`** is optional. A stale version gets 412.
- **A referral's own Task keeps** its `for`, `focus`, `owner`, `code` and `partOf`. A change
  to any of them gets 400 naming the field.
- **`businessStatus`** holds a program's own steps, such as "Intake scheduled".
- **Output** may be a QuestionnaireResponse, Observation, Procedure or DocumentReference in
  the same patient's chart, or text, or a code.

Limit: a limited client cannot create a DocumentReference, so it returns its report as text.
A document can come back by Direct (workflow 3), or staff attach it.

### 6. Community and social-care referrals

A referral to a community organization, such as a food bank, housing agency or meals
program. It uses the same form and the same tracking. What is different is what comes back.

**Example.** Maria, 68, screens positive for food insecurity at her primary care clinic.

1. **Screening.** Maria completes a hunger screening questionnaire. Her answers are kept as
   a QuestionnaireResponse.
2. **Referral.** The clinician saves a referral to Harvest Meals (an External Organization in
   the address book), Requested Service a SNOMED CT code for meal provision, Reason naming
   the screening.
3. **Task.** Harvest Meals gets a Task, status requested.
4. **Accepted.** Harvest Meals' system, a limited client as in workflow 5, marks it accepted,
   then in-progress with `businessStatus` "Deliveries started". The clinic sees each change.
5. **Follow-up screening.** A patient Task is created through the API: code
   `complete-questionnaire`, `focus` the questionnaire, `owner` the patient, `partOf` the
   referral Task. Maria repeats the screening at 90 days.
6. **Closed.** Harvest Meals marks the Task completed with what was delivered in `output`.
   Or the clinic records a reply.

Limits:

- **Organizations without a system** work as in workflow 2, 3 or 4. Most do today.
- **Patient Tasks** can be read with `patient/Task.read`. A patient app cannot yet mark its
  own Task done; there is no `patient/Task` write scope.
- **A limited client cannot create the patient Task** in step 5. A staff-side client does.
- **There is no referral category field**, so reports cannot yet separate SDOH referrals from
  specialty ones except by Requested Service.

### 7. Declined, re-sent or sent elsewhere

**Declined, then re-sent.** The recipient rejects, fails or cancels its Task.

1. The referral shows Rejected (or Failed, Cancelled) and is closed. Refer By gets an inbox
   message with the reason.
2. Staff sort out the problem with the recipient.
3. On the referral screen, press **Re-send**. The recipient gets a new requested Task, and a
   new notice if notices are on. The old Task stays as the record. The referral is open again.

Re-send only shows when the current recipient's latest Task is one they turned down and the
referral has no Reply Date.

**Sent to someone else.** Change Refer To and save.

1. The first recipient's open Task is cancelled with the reason "The referral was sent to
   another recipient." It leaves their queue, and their limited client can no longer read the
   referral. A Task they had already completed, rejected, failed or cancelled keeps its status.
2. The new recipient gets a Task, under the conditions in [Setup](#setup). If the referral
   already has a Reply Date, or the new recipient cannot own a Task, there is none.

**Reply entered by mistake.** Clear the Reply Date and save. The Task the reply completed is
marked entered-in-error, and the recipient gets a new one. The filed reply stays in the
patient's documents. A Task the recipient finished itself through the API is not undone: a referral
the recipient completed, rejected, failed or cancelled keeps showing that status.

Task tracking must be on when the Reply Date is cleared. With it off, the Task the reply
completed is left completed and a later save does not undo it. To recover, turn tracking on,
enter the Reply Date again and save, then clear it and save.

**Referral or patient deleted.** Open Tasks are cancelled with the reason.

### 8. Referral created by another system

A referral can be saved through the standard REST API instead of the screen. It is tracked
exactly as if staff had saved it.

**Example.** A care-management tool creates the referral.

```bash
curl -X POST 'https://emr.example.org/apis/default/api/patient/<pid>/transaction' \
  -H 'Authorization: Bearer <token>' \
  -H 'Content-Type: application/json' \
  -d '{
    "type": "LBTref",
    "groupname": "Default",
    "message": "Referral",
    "referralDate": "2026-11-10",
    "referByNpi": "1234567893",
    "referToNpi": "1987654321",
    "referDiagnosis": "ICD10:E11.9",
    "riskLevel": "Low",
    "includeVitals": "1",
    "body": "Diabetic eye exam"
  }'
```

- Refer By and Refer To are found by NPI.
- `PUT /api/transaction/<tid>` updates it.
- The API never writes the Reply Date, so it cannot close a referral. The Task does that.

### 9. Referrals we receive

Support for being the **receiving** practice is thin.

| What arrives | What OpenEMR does |
|---|---|
| A Referral C-CDA, by Direct or imported by hand | Care Coordination imports it. After review, its Reason for Referral is saved as a referral on the Transactions screen, with the reason only. |
| Who referred the patient | Recorded in demographics (Referring Provider) and on the encounter (Referring Provider; Referral Source when that global is on). Claims take the referring provider from there. |
| A referral from another OpenEMR | Nothing automatic. It arrives as in the first row, or on paper. |

There is no inbound referral queue, and no way for an outside system to create a referral
here through FHIR.

## Worklist and reporting

- **Patient's Transactions list.** Each referral shows its status, and Overdue.
- **Reports > Clients > Referrals.** Filter by date, facility and **Show**: All, Open or Overdue. Open
  and Overdue ignore the From date, because an open referral may be an old one.
- **CMS50, Closing the Referral Loop.** Calculated from these referrals.
  - A referral can count only when it has a Referral Date and a Requested Service from the
    SNOMED CT code type. A billing code does not match the measure's value set.
  - Not every such referral counts. The measure takes each patient's first referral in the first
    ten months of the year, and only when its code is in the measure's Referral value set.
  - It is closed by a Reply Date, or by a document a recipient returned in its Task's output
    whose type is in the measure's Consultant Report value set. Either must come after the
    referral and inside the measurement period.
- **Summary of care (Promoting Interoperability).** The Sent Summary of Care boxes on the
  referral feed the automated measure calculations. They are separate from Task tracking.

## Not supported

- **IHE 360X.** The Direct workflow is modeled on it, but does not use XDM packaging or
  HL7 v2 status messages. Do not describe it as 360X conformant.
- **Creating or editing a referral through FHIR.** Use the screen or the standard API.
- **A patient updating their own Task.**
- **An inbound referral queue.**
- **Matching an imported Referral C-CDA to an existing referral.**
- **Authorization tracking.** The standard API accepts authorization number, visits and
  valid dates, but the stock referral form has no fields for them and nothing counts visits.
- **A referral category** (specialty, behavioral health, SDOH).
- **Risk Level as Task priority.** Risk Level is stored but not sent as `priority`.
- **A coded Referrer Diagnosis picker.** The field is free text. A typed `ICD10:I10` is read
  as a code; anything else is text.
- **FHIR Subscriptions.** A connected recipient polls for its Tasks.

## Reference

**Tracking issues**

- [#14710](https://github.com/openemr/openemr/issues/14710) Referral loop, from the referral form to a closed loop
- [#15137](https://github.com/openemr/openemr/issues/15137) Close the loop with outside recipients
- [#14521](https://github.com/openemr/openemr/issues/14521) FHIR Task resource
- [#14745](https://github.com/openemr/openemr/issues/14745) CMS50 from tracked referrals

**Where the code is**

| Piece | File |
|---|---|
| Referral form and status | `interface/patient_file/transaction/add_transaction.php`, `src/Services/ReferralStatus.php`, `src/Services/ReferralDisplay.php` |
| Reading referrals | `src/Services/ReferralService.php` |
| The referral's Task | `src/Services/ReferralTaskService.php` |
| Recipient notices | `src/Services/ReferralNoticeService.php`, `src/Events/Referral/ReferralDirectNoticeEvent.php` |
| FHIR ServiceRequest (referrals) | `src/Services/FHIR/ServiceRequest/FhirServiceRequestReferralService.php` |
| FHIR Task | `src/Services/FHIR/FhirTaskService.php`, `src/Services/FHIR/FhirTaskWriteRules.php` |
| Limited clients | `src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php` |
| Standard API | `src/Services/PatientTransactionService.php` |
| Referrals report | `interface/reports/referrals_report.php` |
| CMS50 | `src/Services/Qdm/Services/ReferralOrderService.php`, `src/Services/Qdm/Services/ConsultantReportService.php` |

**Related documentation**

- [FHIR API: referrals and Tasks](api/FHIR_API.md#referrals-and-tasks)
- [Authorization: a client limited to an address book entry](api/AUTHORIZATION.md#client-limited-to-an-address-book-entry)
- [Standard API](api/STANDARD_API.md)
- [Direct Messaging](Direct_Messaging_README.txt), section K
