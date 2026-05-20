-- openemr_three_scenarios_seed.sql
-- Purpose: Fake demo/sample data for exactly 3 OpenEMR AI Co-Pilot user scenarios.
-- Safe only for local Docker/dev/demo OpenEMR databases.
-- Do NOT run this on a real clinic/production database.
--
-- Demo patients:
--   DEMO-PCP-1001  Marcus Johnson   Physician summary scenario
--   DEMO-MA-1002   Angela Reed      Medical assistant rooming checklist scenario
--   DEMO-BILL-1003 Thomas Carter    Billing claim review scenario

START TRANSACTION;

-- Use an existing provider and facility so the seed fits your local OpenEMR install.
SET @provider_id := COALESCE((SELECT id FROM users WHERE authorized = 1 ORDER BY id LIMIT 1), 1);
SET @provider_username := COALESCE((SELECT username FROM users WHERE id = @provider_id LIMIT 1), 'admin');
SET @facility_id := COALESCE((SELECT id FROM facility ORDER BY id LIMIT 1), 3);
SET @billing_facility := @facility_id;

-- Pick stable new pid values above the current max. The pubpid checks keep the script idempotent.
SET @base_pid := COALESCE((SELECT MAX(pid) FROM patient_data), 0) + 1000;
SET @base_encounter := UNIX_TIMESTAMP() * 10;

-- ---------------------------------------------------------------------
-- 1) Demo patients
-- ---------------------------------------------------------------------

-- Physician scenario patient
INSERT INTO patient_data
(`title`, `language`, `financial`, `fname`, `lname`, `mname`, `DOB`, `street`, `postal_code`,
 `city`, `state`, `drivers_license`, `ss`, `occupation`, `phone_home`, `phone_biz`,
 `phone_contact`, `phone_cell`, `status`, `contact_relationship`, `date`, `sex`,
 `referrer`, `referrerID`, `providerID`, `email`, `ethnoracial`, `interpreter`,
 `migrantseasonal`, `family_size`, `monthly_income`, `homeless`, `financial_review`,
 `pubpid`, `pid`, `genericname1`, `genericval1`, `genericname2`, `genericval2`)
SELECT
 'Mr.', 'english', '', 'Marcus', 'Johnson', '', '1972-04-18',
 '101 Demo Clinic Way', '48201', 'Detroit', 'MI', '', '',
 'Delivery Supervisor', '(313) 555-1001', '', '(313) 555-1001', '(313) 555-1001',
 'married', '', NOW(), 'Male', 'Demo Seed', '', @provider_id,
 'marcus.johnson.demo@example.test', '', '', '', '', '', '', CURDATE(),
 'DEMO-PCP-1001', @base_pid + 1,
 'demo_scenario', 'physician_summary', 'demo_prompt', 'Summarize my next patient'
WHERE NOT EXISTS (SELECT 1 FROM patient_data WHERE pubpid = 'DEMO-PCP-1001' LIMIT 1);

-- Medical assistant scenario patient
INSERT INTO patient_data
(`title`, `language`, `financial`, `fname`, `lname`, `mname`, `DOB`, `street`, `postal_code`,
 `city`, `state`, `drivers_license`, `ss`, `occupation`, `phone_home`, `phone_biz`,
 `phone_contact`, `phone_cell`, `status`, `contact_relationship`, `date`, `sex`,
 `referrer`, `referrerID`, `providerID`, `email`, `ethnoracial`, `interpreter`,
 `migrantseasonal`, `family_size`, `monthly_income`, `homeless`, `financial_review`,
 `pubpid`, `pid`, `genericname1`, `genericval1`, `genericname2`, `genericval2`)
SELECT
 'Ms.', 'english', '', 'Angela', 'Reed', '', '1986-09-07',
 '202 Demo Rooming Ave', '48202', 'Detroit', 'MI', '', '',
 'School Administrator', '(313) 555-1002', '', '(313) 555-1002', '(313) 555-1002',
 'single', '', NOW(), 'Female', 'Demo Seed', '', @provider_id,
 'angela.reed.demo@example.test', '', '', '', '', '', '', CURDATE(),
 'DEMO-MA-1002', @base_pid + 2,
 'demo_scenario', 'ma_rooming_checklist', 'demo_prompt', 'What do I need to confirm?'
WHERE NOT EXISTS (SELECT 1 FROM patient_data WHERE pubpid = 'DEMO-MA-1002' LIMIT 1);

-- Billing specialist scenario patient
INSERT INTO patient_data
(`title`, `language`, `financial`, `fname`, `lname`, `mname`, `DOB`, `street`, `postal_code`,
 `city`, `state`, `drivers_license`, `ss`, `occupation`, `phone_home`, `phone_biz`,
 `phone_contact`, `phone_cell`, `status`, `contact_relationship`, `date`, `sex`,
 `referrer`, `referrerID`, `providerID`, `email`, `ethnoracial`, `interpreter`,
 `migrantseasonal`, `family_size`, `monthly_income`, `homeless`, `financial_review`,
 `pubpid`, `pid`, `genericname1`, `genericval1`, `genericname2`, `genericval2`)
SELECT
 'Mr.', 'english', '', 'Thomas', 'Carter', '', '1964-12-12',
 '303 Demo Billing Blvd', '48203', 'Detroit', 'MI', '', '',
 'Retired', '(313) 555-1003', '', '(313) 555-1003', '(313) 555-1003',
 'married', '', NOW(), 'Male', 'Demo Seed', '', @provider_id,
 'thomas.carter.demo@example.test', '', '', '', '', '', '', CURDATE(),
 'DEMO-BILL-1003', @base_pid + 3,
 'demo_scenario', 'billing_claim_review', 'demo_prompt', 'Why did this claim fail?'
WHERE NOT EXISTS (SELECT 1 FROM patient_data WHERE pubpid = 'DEMO-BILL-1003' LIMIT 1);

-- Capture pids after inserts so re-running the seed still works.
SET @pid_pcp := (SELECT pid FROM patient_data WHERE pubpid = 'DEMO-PCP-1001' LIMIT 1);
SET @pid_ma := (SELECT pid FROM patient_data WHERE pubpid = 'DEMO-MA-1002' LIMIT 1);
SET @pid_bill := (SELECT pid FROM patient_data WHERE pubpid = 'DEMO-BILL-1003' LIMIT 1);

-- ---------------------------------------------------------------------
-- 2) Upcoming appointments
-- ---------------------------------------------------------------------

INSERT INTO openemr_postcalendar_events
(`pc_catid`, `pc_multiple`, `pc_aid`, `pc_pid`, `pc_gid`, `pc_title`, `pc_time`,
 `pc_hometext`, `pc_eventDate`, `pc_endDate`, `pc_duration`, `pc_recurrtype`,
 `pc_recurrspec`, `pc_recurrfreq`, `pc_startTime`, `pc_endTime`, `pc_alldayevent`,
 `pc_location`, `pc_conttel`, `pc_contname`, `pc_contemail`, `pc_website`, `pc_fee`,
 `pc_eventstatus`, `pc_sharing`, `pc_language`, `pc_apptstatus`, `pc_prefcatid`,
 `pc_facility`, `pc_sendalertsms`, `pc_sendalertemail`, `pc_billing_location`, `pc_room`,
 `pc_informant`)
SELECT
 5, 0, CAST(@provider_id AS CHAR), CAST(@pid_pcp AS CHAR), 0, 'Office Visit', NOW(),
 'DEMO AI SCENARIO: Diabetes follow-up. Physician should ask for recent labs, medications, chronic conditions, and open follow-ups.',
 DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 1 DAY),
 1800, 0, '', 0, '09:00:00', '09:30:00', 0, 'Main Clinic',
 '(313) 555-1001', 'Marcus Johnson', 'marcus.johnson.demo@example.test', '', '',
 1, 1, 'english', '-', 0, @facility_id, 'NO', 'NO', @billing_facility, 'Exam 1',
 CAST(@provider_id AS CHAR)
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM openemr_postcalendar_events
    WHERE pc_pid = CAST(@pid_pcp AS CHAR)
      AND pc_hometext LIKE 'DEMO AI SCENARIO: Diabetes follow-up%'
    LIMIT 1
  );

INSERT INTO openemr_postcalendar_events
(`pc_catid`, `pc_multiple`, `pc_aid`, `pc_pid`, `pc_gid`, `pc_title`, `pc_time`,
 `pc_hometext`, `pc_eventDate`, `pc_endDate`, `pc_duration`, `pc_recurrtype`,
 `pc_recurrspec`, `pc_recurrfreq`, `pc_startTime`, `pc_endTime`, `pc_alldayevent`,
 `pc_location`, `pc_conttel`, `pc_contname`, `pc_contemail`, `pc_website`, `pc_fee`,
 `pc_eventstatus`, `pc_sharing`, `pc_language`, `pc_apptstatus`, `pc_prefcatid`,
 `pc_facility`, `pc_sendalertsms`, `pc_sendalertemail`, `pc_billing_location`, `pc_room`,
 `pc_informant`)
SELECT
 5, 0, CAST(@provider_id AS CHAR), CAST(@pid_ma AS CHAR), 0, 'Annual Physical', NOW(),
 'DEMO AI SCENARIO: Annual physical rooming checklist. Confirm meds, pharmacy, flu vaccine status, allergies, and last elevated BP.',
 DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 1 DAY),
 1800, 0, '', 0, '09:30:00', '10:00:00', 0, 'Main Clinic',
 '(313) 555-1002', 'Angela Reed', 'angela.reed.demo@example.test', '', '',
 1, 1, 'english', '-', 0, @facility_id, 'NO', 'NO', @billing_facility, 'Exam 2',
 CAST(@provider_id AS CHAR)
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM openemr_postcalendar_events
    WHERE pc_pid = CAST(@pid_ma AS CHAR)
      AND pc_hometext LIKE 'DEMO AI SCENARIO: Annual physical rooming checklist%'
    LIMIT 1
  );

INSERT INTO openemr_postcalendar_events
(`pc_catid`, `pc_multiple`, `pc_aid`, `pc_pid`, `pc_gid`, `pc_title`, `pc_time`,
 `pc_hometext`, `pc_eventDate`, `pc_endDate`, `pc_duration`, `pc_recurrtype`,
 `pc_recurrspec`, `pc_recurrfreq`, `pc_startTime`, `pc_endTime`, `pc_alldayevent`,
 `pc_location`, `pc_conttel`, `pc_contname`, `pc_contemail`, `pc_website`, `pc_fee`,
 `pc_eventstatus`, `pc_sharing`, `pc_language`, `pc_apptstatus`, `pc_prefcatid`,
 `pc_facility`, `pc_sendalertsms`, `pc_sendalertemail`, `pc_billing_location`, `pc_room`,
 `pc_informant`)
SELECT
 5, 0, CAST(@provider_id AS CHAR), CAST(@pid_bill AS CHAR), 0, 'Office Visit', NOW(),
 'DEMO AI SCENARIO: Billing claim review. Demo claim rejected because CPT code is missing a diagnosis link.',
 DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 2 DAY),
 1800, 0, '', 0, '10:00:00', '10:30:00', 0, 'Main Clinic',
 '(313) 555-1003', 'Thomas Carter', 'thomas.carter.demo@example.test', '', '',
 1, 1, 'english', '-', 0, @facility_id, 'NO', 'NO', @billing_facility, 'Exam 3',
 CAST(@provider_id AS CHAR)
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM openemr_postcalendar_events
    WHERE pc_pid = CAST(@pid_bill AS CHAR)
      AND pc_hometext LIKE 'DEMO AI SCENARIO: Billing claim review%'
    LIMIT 1
  );

-- ---------------------------------------------------------------------
-- 3) Recent encounters
-- ---------------------------------------------------------------------

INSERT INTO form_encounter
(`date`, `reason`, `facility`, `facility_id`, `pid`, `encounter`, `pc_catid`,
 `provider_id`, `billing_facility`, `class_code`)
SELECT
 DATE_ADD(CURDATE(), INTERVAL -14 DAY) + INTERVAL 10 HOUR,
 'DEMO AI: Diabetes follow-up - A1C increased and follow-up items open',
 'Main Clinic', @facility_id, @pid_pcp, @base_encounter + 1,
 5, @provider_id, @billing_facility, 'AMB'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM form_encounter
    WHERE pid = @pid_pcp
      AND reason = 'DEMO AI: Diabetes follow-up - A1C increased and follow-up items open'
    LIMIT 1
  );

INSERT INTO form_encounter
(`date`, `reason`, `facility`, `facility_id`, `pid`, `encounter`, `pc_catid`,
 `provider_id`, `billing_facility`, `class_code`)
SELECT
 DATE_ADD(CURDATE(), INTERVAL -30 DAY) + INTERVAL 11 HOUR,
 'DEMO AI: Prior annual/prep note - rooming gaps remain open',
 'Main Clinic', @facility_id, @pid_ma, @base_encounter + 2,
 5, @provider_id, @billing_facility, 'AMB'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM form_encounter
    WHERE pid = @pid_ma
      AND reason = 'DEMO AI: Prior annual/prep note - rooming gaps remain open'
    LIMIT 1
  );

INSERT INTO form_encounter
(`date`, `reason`, `facility`, `facility_id`, `pid`, `encounter`, `pc_catid`,
 `provider_id`, `billing_facility`, `class_code`)
SELECT
 DATE_ADD(CURDATE(), INTERVAL -21 DAY) + INTERVAL 13 HOUR,
 'DEMO AI: Office visit tied to rejected claim - missing diagnosis link',
 'Main Clinic', @facility_id, @pid_bill, @base_encounter + 3,
 5, @provider_id, @billing_facility, 'AMB'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM form_encounter
    WHERE pid = @pid_bill
      AND reason = 'DEMO AI: Office visit tied to rejected claim - missing diagnosis link'
    LIMIT 1
  );

-- ---------------------------------------------------------------------
-- 4) Notes that carry the chart/demo context for the future AI layer
-- ---------------------------------------------------------------------

INSERT INTO pnotes
(`date`, `body`, `pid`, `user`, `groupname`, `activity`, `authorized`, `title`, `assigned_to`, `message_status`)
SELECT
 NOW(),
 'DEMO AI PHYSICIAN SUMMARY SOURCE. Chronic conditions: Type 2 diabetes mellitus and hypertension. Recent labs: A1C increased from 7.4 to 8.2; LDL 116; creatinine stable. Medications: metformin 1000 mg twice daily and lisinopril 10 mg daily. Open follow-ups: overdue diabetic eye exam, repeat A1C needed, review home glucose readings. Appointment reason: diabetes follow-up. AI safety: draft summary only; physician must review chart sources.',
 @pid_pcp, @provider_username, 'Default', 1, 1,
 'DEMO AI - Physician Summary Source', @provider_username, 'New'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM pnotes
    WHERE pid = @pid_pcp
      AND title = 'DEMO AI - Physician Summary Source'
    LIMIT 1
  );

INSERT INTO pnotes
(`date`, `body`, `pid`, `user`, `groupname`, `activity`, `authorized`, `title`, `assigned_to`, `message_status`)
SELECT
 NOW(),
 'DEMO AI MEDICAL ASSISTANT ROOMING SOURCE. Visit reason: annual physical. Rooming checklist items: medication list not confirmed, pharmacy not updated, flu vaccine status unknown, allergies need confirmation, last recorded BP was elevated at 148/92. Suggested MA action: confirm only; do not change medications or diagnoses. AI safety: draft checklist only; stay within assistant permissions.',
 @pid_ma, @provider_username, 'Default', 1, 1,
 'DEMO AI - MA Rooming Source', @provider_username, 'New'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM pnotes
    WHERE pid = @pid_ma
      AND title = 'DEMO AI - MA Rooming Source'
    LIMIT 1
  );

INSERT INTO pnotes
(`date`, `body`, `pid`, `user`, `groupname`, `activity`, `authorized`, `title`, `assigned_to`, `message_status`)
SELECT
 NOW(),
 'DEMO AI BILLING CLAIM REVIEW SOURCE. Claim status: rejected demo claim. Plain-language issue: CPT 99213 office visit is not linked to a supporting diagnosis on the encounter. Insurance: Demo Medicaid/Commercial Plan. What to check first: encounter diagnosis mapping, provider documentation, payer/member information, and whether the CPT/ICD relationship is present before resubmission. AI safety: do not submit claims automatically; do not suggest upcoding; human billing review required.',
 @pid_bill, @provider_username, 'Default', 1, 1,
 'DEMO AI - Billing Claim Source', @provider_username, 'New'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM pnotes
    WHERE pid = @pid_bill
      AND title = 'DEMO AI - Billing Claim Source'
    LIMIT 1
  );

COMMIT;

-- ---------------------------------------------------------------------
-- Quick verification output after import
-- ---------------------------------------------------------------------

SELECT
  pubpid,
  fname,
  lname,
  DOB,
  genericval1 AS demo_scenario
FROM patient_data
WHERE pubpid IN ('DEMO-PCP-1001', 'DEMO-MA-1002', 'DEMO-BILL-1003')
ORDER BY pubpid;

SELECT
  p.pubpid,
  p.fname,
  p.lname,
  e.pc_eventDate,
  e.pc_startTime,
  e.pc_hometext
FROM openemr_postcalendar_events e
JOIN patient_data p ON CAST(p.pid AS CHAR) = e.pc_pid
WHERE p.pubpid IN ('DEMO-PCP-1001', 'DEMO-MA-1002', 'DEMO-BILL-1003')
ORDER BY p.pubpid, e.pc_eventDate, e.pc_startTime;

SELECT
  p.pubpid,
  n.title,
  LEFT(n.body, 180) AS note_preview
FROM pnotes n
JOIN patient_data p ON p.pid = n.pid
WHERE p.pubpid IN ('DEMO-PCP-1001', 'DEMO-MA-1002', 'DEMO-BILL-1003')
ORDER BY p.pubpid, n.date DESC;
