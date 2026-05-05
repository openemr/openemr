# MedEx Module - User Testing Guide

## Overview

This guide walks you through testing the modernized MedEx module in a live OpenEMR environment, monitoring both the OpenEMR UI and the MedExBank Docker container logs.

---

## Prerequisites

### 1. Docker Environment Running

```bash
cd docker/development-easy
docker compose up --detach --wait
```

**Verify containers are running:**
```bash
docker compose ps
```

You should see:
- `openemr` - Running on ports 8300 (HTTP) / 9300 (HTTPS)
- `medexbank` (if configured) - MedEx API simulator

### 2. OpenEMR Access

- **URL:** http://localhost:8300 or https://localhost:9300
- **Login:** `admin` / `pass`

### 3. MedEx Module Enabled

Navigate to: **Administration → Modules → Manage Modules**
- Ensure `oe-module-medex` is **Registered** and **Enabled**

---

## Test Environment Setup

### A. Create Test Patients

You'll need patients with different contact preferences to test all modalities.

**Go to:** Patient/Client → New/Search

#### Patient 1: SMS Enabled (John SMS)
```
Name: John SMS
DOB: 1980-01-01
Phone (Cell): 555-0001
Email: john.sms@example.com
HIPAA Settings:
  ☑ Allow SMS
  ☑ Allow Voice
  ☑ Allow Email
```

#### Patient 2: Email Only (Jane Email)
```
Name: Jane Email
DOB: 1980-02-02
Phone (Cell): (leave empty)
Email: jane.email@example.com
HIPAA Settings:
  ☐ Allow SMS
  ☐ Allow Voice
  ☑ Allow Email
```

#### Patient 3: Voice Only (Bob Voice)
```
Name: Bob Voice
DOB: 1980-03-03
Phone (Home): 555-0003
Phone (Cell): (leave empty)
Email: (leave empty)
HIPAA Settings:
  ☐ Allow SMS
  ☑ Allow Voice
  ☐ Allow Email
```

#### Patient 4: All Blocked (Sue Blocked)
```
Name: Sue Blocked
DOB: 1980-04-04
Phone (Cell): 555-0004
Phone (Home): 555-0005
Email: sue.blocked@example.com
HIPAA Settings:
  ☐ Allow SMS
  ☐ Allow Voice
  ☐ Allow Email
```

### B. Create Test Appointments

**Go to:** Calendar

Create appointments for each test patient:

1. **John SMS** - Tomorrow at 10:00 AM
2. **Jane Email** - Tomorrow at 11:00 AM
3. **Bob Voice** - Tomorrow at 2:00 PM
4. **Sue Blocked** - Tomorrow at 3:00 PM

---

## Test Scenarios

## Test 1: MedEx Module Access & Navigation

### Objective
Verify the modernized DisplayService navigation works correctly.

### Steps

1. **Navigate to MedEx Module**
   - Go to: **Modules → MedEx**
   - Or: **Miscellaneous → MedEx**

2. **Verify Navigation Renders**
   - You should see the MedEx navigation bar
   - Check for tabs: Dashboard, Campaigns, Recalls, etc.

### Expected Results
- ✅ Navigation displays without errors
- ✅ No PHP errors in browser console
- ✅ Navigation is responsive and clickable

### Log Monitoring

**In terminal 1 (OpenEMR logs):**
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

**Expected:** No PHP errors related to DisplayService->navigation()

**If errors appear:**
- Note the exact error message
- Check file path in error (should be Services/DisplayService.php)
- Look for type errors or missing methods

---

## Test 2: Modality Detection (DisplayService)

### Objective
Test the modernized `possibleModalities()` method with different patient HIPAA settings.

### Steps

1. **Open MedEx Dashboard**
   - Navigate to: **Modules → MedEx → Dashboard**

2. **View Patient Communication Preferences**
   - Look for a section showing available communication methods
   - Should display icons or indicators for SMS/Voice/Email

3. **For Each Test Patient, Verify:**

   **Patient: John SMS**
   - Expected: ✅ SMS, ✅ Voice, ✅ Email icons visible

   **Patient: Jane Email**
   - Expected: ❌ SMS, ❌ Voice, ✅ Email icon visible only

   **Patient: Bob Voice**
   - Expected: ❌ SMS, ✅ Voice, ❌ Email icon visible only

   **Patient: Sue Blocked**
   - Expected: ❌ SMS, ❌ Voice, ❌ Email (all blocked)

### Expected Results
- ✅ Modality icons match patient HIPAA preferences
- ✅ No modalities shown for blocked patients
- ✅ Contact info (phone/email) correctly detected

### Log Monitoring

**In terminal 1:**
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

**Expected:** No errors from DisplayService->possibleModalities()

### Debugging SQL Queries

**Enable query logging:**
```bash
docker compose exec openemr mysql -u root -proot openemr -e "SET GLOBAL general_log = 'ON';"
docker compose exec openemr tail -f /var/lib/mysql/queries.log
```

**Look for:**
- Queries to patient_data table fetching phone_cell, phone_home, email
- QueryUtils::fetchRecords() calls (should use prepared statements)

---

## Test 3: Recall Campaign Creation (EventsService)

### Objective
Test the modernized EventsService recall campaign generation.

### Steps

1. **Create a Recall Campaign**
   - Go to: **Modules → MedEx → Campaigns**
   - Click: **New Campaign**
   - Select: **Recall** type

2. **Configure Campaign**
   ```
   Campaign Name: Annual Checkup Recall Test
   Type: RECALL
   Timing: 30 days before due date
   Message: "It's time for your annual checkup. Please call to schedule."

   Enable:
   ☑ SMS
   ☑ Voice
   ☑ Email
   ```

3. **Add Patients to Recall List**
   - Go to: **Modules → MedEx → Recalls**
   - Click: **Add Recall**
   - Add all 4 test patients with due date = tomorrow

4. **Generate Events**
   - Click: **Calculate Events** or **Generate Messages**
   - This triggers EventsService->generate()

### Expected Results
- ✅ Campaign created without errors
- ✅ Events calculated for eligible patients
- ✅ Modality restrictions applied (Jane=email only, Bob=voice only, Sue=none)

### Log Monitoring

**Terminal 1 (OpenEMR PHP errors):**
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

**Watch for:**
- EventsService->processRecalls() execution
- QueryUtils::fetchRecords() calls
- Any SQL errors (should be none)

**Terminal 2 (SQL queries):**
```bash
docker compose exec openemr tail -f /var/lib/mysql/queries.log | grep -i "medex"
```

**Expected queries:**
- SELECT from medex_outgoing
- SELECT from medex_prefs
- SELECT from openemr_postcalendar_events
- SELECT from patient_data (for contact info)
- INSERT into medex_outgoing (for generated messages)

**Terminal 3 (MedExBank container - if running):**
```bash
docker compose logs -f medexbank
```

**Expected:** API calls from OpenEMR to MedExBank

---

## Test 4: Reminder Campaign (EventsService)

### Objective
Test appointment reminder generation with the modernized processReminders() method.

### Steps

1. **Create Reminder Campaign**
   - Go to: **Modules → MedEx → Campaigns**
   - Click: **New Campaign**
   - Select: **Reminder** type

2. **Configure Campaign**
   ```
   Campaign Name: 24hr Reminder Test
   Type: REMINDER
   Timing: 24 hours before appointment
   Fire Time: 09:00 AM
   Message: "Reminder: You have an appointment tomorrow at {APPT_TIME}."

   Enable:
   ☑ SMS
   ☑ Voice
   ☑ Email
   ```

3. **Calculate Events**
   - Click: **Calculate Events**
   - This triggers EventsService->calculateEvents() and processReminders()

4. **View Generated Messages**
   - Go to: **Modules → MedEx → Messages**
   - Should see 4 messages queued (one per patient)

### Expected Results

**For John SMS (all enabled):**
- ✅ SMS message queued
- ✅ Voice message queued
- ✅ Email message queued

**For Jane Email (email only):**
- ✅ Email message queued
- ❌ No SMS/Voice messages

**For Bob Voice (voice only):**
- ✅ Voice message queued
- ❌ No SMS/Email messages

**For Sue Blocked (all blocked):**
- ❌ No messages queued

### Log Monitoring

**Terminal 1:**
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

**Watch for:**
- EventsService->processReminders() entry
- QueryUtils calls for appointment data
- Message template processing

**Terminal 2 (Debug output):**
```bash
docker compose exec openemr tail -f /tmp/medex_debug.log
```

**Expected output:**
```
[2026-01-23 10:00:00] Processing REMINDER campaign: 24hr Reminder Test
[2026-01-23 10:00:00] Found 4 appointments in date range
[2026-01-23 10:00:00] Patient 1 (John SMS): SMS=YES, Voice=YES, Email=YES
[2026-01-23 10:00:00] Patient 2 (Jane Email): SMS=NO, Voice=NO, Email=YES
[2026-01-23 10:00:00] Patient 3 (Bob Voice): SMS=NO, Voice=YES, Email=NO
[2026-01-23 10:00:00] Patient 4 (Sue Blocked): SMS=NO, Voice=NO, Email=NO
[2026-01-23 10:00:00] Generated 7 messages total
```

---

## Test 5: Recall Progress Tracking (DisplayService)

### Objective
Test the modernized show_progress_recall() method.

### Steps

1. **Navigate to Recall Board**
   - Go to: **Modules → MedEx → Recalls**

2. **Select a Patient**
   - Click on one of your test patients (e.g., John SMS)
   - This triggers DisplayService->show_progress_recall()

3. **Verify Display Shows:**
   - Patient name and demographics
   - Recall reason
   - Next appointment (if scheduled)
   - Primary care provider
   - Message history
   - Status indicators

### Expected Results
- ✅ Patient data loads correctly
- ✅ Appointment data displays
- ✅ Message history shows sent/pending/failed messages
- ✅ Status correctly shows: SCHEDULED, SENT, CONFIRMED, FAILED, etc.

### Log Monitoring

**Terminal 1:**
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

**Watch for:**
- DisplayService->show_progress_recall() execution
- Multiple QueryUtils::fetchRecords() calls:
  - SELECT from users (provider lookup)
  - SELECT from openemr_postcalendar_events (appointment lookup)
  - SELECT from medex_outgoing (message history)

**Terminal 2 (SQL):**
```bash
docker compose exec openemr tail -f /var/lib/mysql/queries.log | grep "medex_outgoing\|openemr_postcalendar"
```

**Expected queries:**
```sql
SELECT * FROM users WHERE id=?
SELECT * FROM openemr_postcalendar_events WHERE pc_pid=? AND pc_eventDate >= CURDATE() ...
SELECT * FROM medex_outgoing WHERE msg_pc_eid=? AND campaign_uid=? ORDER BY msg_date DESC
```

---

## Test 6: Multiple Campaign Types (EventsService)

### Objective
Test all 6 campaign types supported by EventsService.

### Campaign Types to Test

1. ✅ **REMINDER** - Already tested above
2. ✅ **RECALL** - Already tested above
3. **ANNOUNCE** - General announcements
4. **SURVEY** - Patient surveys
5. **CLINICAL_REMINDER** - Clinical decision rules
6. **GOGREEN** - Go green campaign

### Steps for Each Campaign Type

**Create Campaign:**
```
Type: [ANNOUNCE/SURVEY/CLINICAL_REMINDER/GOGREEN]
Configure settings
Calculate Events
Monitor logs
```

### Expected Results

**For each campaign type:**
- ✅ Campaign creates without errors
- ✅ Appropriate EventsService method called:
  - processAnnouncements()
  - processSurveys()
  - processClinicalReminders()
  - processGoGreen()
- ✅ Messages generated respecting modality restrictions
- ✅ No SQL errors in logs

### Log Monitoring

**Terminal 1:**
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log | grep -i "eventsservice"
```

**Watch for method calls:**
```
EventsService->processAnnouncements()
EventsService->processSurveys()
EventsService->processClinicalReminders()
EventsService->processGoGreen()
```

---

## Test 7: Recurrent Events (EventsService)

### Objective
Test the addRecurrent() method for recurring messages.

### Steps

1. **Create Campaign with Multiple Messages**
   ```
   Type: REMINDER
   Message 1: 7 days before (First reminder)
   Message 2: 3 days before (Second reminder)
   Message 3: 1 day before (Final reminder)
   ```

2. **Calculate Events**
   - Should create 3 separate message events per patient

3. **Verify in Database**
   ```bash
   docker compose exec openemr mysql -u root -proot openemr
   ```
   ```sql
   SELECT msg_uid, msg_pc_eid, msg_date, msg_type, campaign_uid
   FROM medex_outgoing
   WHERE msg_pc_eid LIKE 'recall_%'
   ORDER BY msg_date;
   ```

### Expected Results
- ✅ 3 messages created per patient
- ✅ Each message has correct timing offset
- ✅ Messages linked to parent event (e_is_subEvent_of)

### Log Monitoring

**Terminal 1:**
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

**Watch for:**
- EventsService->addRecurrent() calls
- Multiple INSERT statements to medex_outgoing

---

## Test 8: Date Range Calculations (EventsService)

### Objective
Test the calculateEvents() method with various date ranges.

### Steps

1. **Test Different Date Ranges**
   - Go to: **Modules → MedEx → Campaigns → [Select Campaign]**
   - Click: **Calculate Events**

2. **Try These Ranges:**
   - Start: Today, End: +7 days
   - Start: Today, End: +30 days
   - Start: -7 days, End: +7 days (past and future)

3. **Verify Event Count**
   - Should show number of events calculated
   - Should match appointments in date range

### Expected Results
- ✅ Events calculated for appointments in range
- ✅ Past appointments excluded (unless explicitly included)
- ✅ Date math correct (no off-by-one errors)

### Log Monitoring

**Terminal 1:**
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

**Terminal 2 (SQL):**
```bash
docker compose exec openemr tail -f /var/lib/mysql/queries.log | grep "pc_eventDate"
```

**Expected query:**
```sql
SELECT * FROM openemr_postcalendar_events
WHERE pc_eventDate >= ? AND pc_eventDate <= ?
```

---

## Test 9: Message Sending (MedExBank Integration)

### Objective
Test actual message transmission to MedExBank API.

### Prerequisites
- MedExBank container running
- MedEx credentials configured in OpenEMR

### Steps

1. **Configure MedEx Credentials**
   - Go to: **Administration → Globals → Connectors**
   - Enter MedEx API credentials

2. **Send Test Message**
   - Go to: **Modules → MedEx → Messages**
   - Select a queued message
   - Click: **Send Now**

3. **Monitor Both Sides**

### Expected Results
- ✅ Message status changes to "SENT"
- ✅ Response logged in medex_outgoing table

### Log Monitoring

**Terminal 1 (OpenEMR):**
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

**Terminal 2 (MedExBank):**
```bash
docker compose logs -f medexbank
```

**Expected MedExBank logs:**
```
[2026-01-23 10:00:00] POST /api/messages received
[2026-01-23 10:00:00] Auth token validated
[2026-01-23 10:00:00] Message queued: SMS to 555-0001
[2026-01-23 10:00:00] Response 200: message_id=12345
```

**Terminal 3 (SQL):**
```sql
SELECT msg_uid, msg_type, msg_reply, msg_date
FROM medex_outgoing
WHERE msg_uid = '[message_id]';
```

**Expected:**
```
msg_reply = '200' or 'SENT'
```

---

## Test 10: Error Handling

### Objective
Test that modernized code handles errors gracefully.

### Scenarios to Test

1. **Invalid Patient Data**
   - Create patient with no contact info
   - Try to generate messages
   - Expected: Skipped, no error

2. **Invalid Date Range**
   - Calculate events with end_date < start_date
   - Expected: Error message, no crash

3. **Database Connection Lost**
   - Stop MySQL briefly during event generation
   - Expected: Graceful error, transaction rollback

### Log Monitoring

**Terminal 1:**
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

**Watch for:**
- Try-catch blocks catching exceptions
- QueryUtils error handling
- No uncaught exceptions

---

## Monitoring Commands Reference

### OpenEMR PHP Errors
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

### OpenEMR Application Log
```bash
docker compose exec openemr tail -f /var/www/localhost/htdocs/openemr/sites/default/documents/logs/log
```

### MySQL Query Log
```bash
# Enable
docker compose exec openemr mysql -u root -proot openemr -e "SET GLOBAL general_log = 'ON';"

# Monitor
docker compose exec openemr tail -f /var/lib/mysql/queries.log
```

### MedExBank Container
```bash
docker compose logs -f medexbank
```

### Apache Access Log
```bash
docker compose exec openemr tail -f /var/log/apache2/access.log
```

---

## Success Criteria

### ✅ All Tests Pass If:

1. **Navigation works** - No errors loading MedEx module
2. **Modalities detected correctly** - HIPAA restrictions respected
3. **Campaigns create** - All 6 types work
4. **Events generate** - Messages queued correctly
5. **Modality filtering works** - SMS/Voice/Email sent to correct patients only
6. **Recall tracking works** - Progress displays correctly
7. **Date calculations correct** - Events in proper date ranges
8. **Recurrent events work** - Multiple messages per campaign
9. **Messages send** - MedExBank receives and acknowledges
10. **Errors handled gracefully** - No crashes on invalid data

### ❌ Test Fails If:

- PHP fatal errors in logs
- SQL errors in queries
- Type errors (argument count, type mismatches)
- Missing methods
- Incorrect modality detection
- Messages sent to blocked patients
- Date math errors
- Uncaught exceptions

---

## Troubleshooting

### Common Issues

**1. "Class not found" errors**
- Check API.php facade loaded
- Verify autoloading works
- Check class_alias() statements

**2. SQL errors**
- Check QueryUtils syntax
- Verify table names correct
- Check prepared statement bindings

**3. Type errors**
- Check method signatures match
- Verify parameter types
- Check return types

**4. Messages not sending**
- Check MedEx credentials
- Verify MedExBank container running
- Check network connectivity

**5. Wrong modalities**
- Check HIPAA settings in patient_data
- Verify possibleModalities() logic
- Check phone/email fields populated

---

## Next Steps After Testing

1. **Document Results** - Note any failures or issues
2. **Review Logs** - Check for warnings or notices
3. **Performance Testing** - Test with large patient volumes
4. **Security Review** - Verify HIPAA compliance maintained
5. **Code Review** - Final review before production deployment
