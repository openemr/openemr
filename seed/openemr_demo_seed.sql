-- openemr_demo_seed.sql
-- Purpose: Fake demo/sample data for exactly 3 OpenEMR AI Co-Pilot demo scenarios.
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
SET @base_encounter := COALESCE((SELECT MAX(encounter) FROM form_encounter WHERE encounter < 2000000000), 100000) + 100;

-- ---------------------------------------------------------------------
-- 1) Demo patients
-- ---------------------------------------------------------------------

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
 'marcus.johnson.demo@example.com', '', '', '', '', '', '', CURDATE(),
 'DEMO-PCP-1001', @base_pid + 1,
 'demo_scenario', 'physician_summary', 'demo_prompt',
 'Summarize my next patient. Focus on recent labs, medications, chronic conditions, and open follow-ups.'
WHERE NOT EXISTS (SELECT 1 FROM patient_data WHERE pubpid = 'DEMO-PCP-1001' LIMIT 1);

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
 'angela.reed.demo@example.com', '', '', '', '', '', '', CURDATE(),
 'DEMO-MA-1002', @base_pid + 2,
 'demo_scenario', 'ma_rooming', 'demo_prompt',
 'What do I need to confirm before the provider sees this patient?'
WHERE NOT EXISTS (SELECT 1 FROM patient_data WHERE pubpid = 'DEMO-MA-1002' LIMIT 1);

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
 'thomas.carter.demo@example.com', '', '', '', '', '', '', CURDATE(),
 'DEMO-BILL-1003', @base_pid + 3,
 'demo_scenario', 'billing_review', 'demo_prompt',
 'Why did this claim fail, and what should I check first?'
WHERE NOT EXISTS (SELECT 1 FROM patient_data WHERE pubpid = 'DEMO-BILL-1003' LIMIT 1);

SET @pid_pcp := (SELECT pid FROM patient_data WHERE pubpid = 'DEMO-PCP-1001' LIMIT 1);
SET @pid_ma := (SELECT pid FROM patient_data WHERE pubpid = 'DEMO-MA-1002' LIMIT 1);
SET @pid_bill := (SELECT pid FROM patient_data WHERE pubpid = 'DEMO-BILL-1003' LIMIT 1);

-- ---------------------------------------------------------------------
-- 1b) Refresh demo-generated scheduling / encounter / claim rows
--     so repeated imports stay clean and demo-safe.
-- ---------------------------------------------------------------------

DELETE FROM claims
WHERE patient_id = @pid_bill;

DELETE FROM billing
WHERE pid = @pid_bill;

DELETE FROM form_encounter
WHERE pid IN (@pid_pcp, @pid_ma, @pid_bill)
  AND reason LIKE 'DEMO AI:%';

DELETE FROM openemr_postcalendar_events
WHERE pc_pid IN (CAST(@pid_pcp AS CHAR), CAST(@pid_ma AS CHAR), CAST(@pid_bill AS CHAR))
  AND (
    pc_hometext LIKE 'DEMO AI SCENARIO:%'
    OR pc_hometext LIKE 'DEMO AI FRONT DESK CONTEXT%'
  );

UPDATE patient_data
SET genericname1 = 'demo_scenario',
    genericval1 = 'physician_summary',
    genericname2 = 'demo_prompt',
    genericval2 = 'Summarize my next patient. Focus on recent labs, medications, chronic conditions, and open follow-ups.',
    email = 'marcus.johnson.demo@example.com'
WHERE pubpid = 'DEMO-PCP-1001';

UPDATE patient_data
SET genericname1 = 'demo_scenario',
    genericval1 = 'ma_rooming',
    genericname2 = 'demo_prompt',
    genericval2 = 'What do I need to confirm before the provider sees this patient?',
    email = 'angela.reed.demo@example.com'
WHERE pubpid = 'DEMO-MA-1002';

UPDATE patient_data
SET genericname1 = 'demo_scenario',
    genericval1 = 'billing_review',
    genericname2 = 'demo_prompt',
    genericval2 = 'Why did this claim fail, and what should I check first?',
    email = 'thomas.carter.demo@example.com'
WHERE pubpid = 'DEMO-BILL-1003';

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
 5, 0, CAST(@provider_id AS CHAR), CAST(@pid_pcp AS CHAR), 0, 'Diabetes Follow-up', NOW(),
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
      AND (
        pc_hometext LIKE 'DEMO AI SCENARIO:%'
        OR pc_hometext LIKE 'DEMO AI FRONT DESK CONTEXT%'
      )
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
      AND (
        pc_hometext LIKE 'DEMO AI SCENARIO:%'
        OR pc_hometext LIKE 'DEMO AI FRONT DESK CONTEXT%'
      )
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
      AND (
        pc_hometext LIKE 'DEMO AI SCENARIO:%'
        OR pc_hometext LIKE 'DEMO AI FRONT DESK CONTEXT%'
      )
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
      AND reason LIKE 'DEMO AI:%'
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
      AND reason LIKE 'DEMO AI:%'
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
      AND reason LIKE 'DEMO AI:%'
    LIMIT 1
  );

SET @enc_pcp := (
  SELECT encounter
  FROM form_encounter
  WHERE pid = @pid_pcp
    AND reason = 'DEMO AI: Diabetes follow-up - A1C increased and follow-up items open'
  ORDER BY date DESC
  LIMIT 1
);

SET @enc_ma := (
  SELECT encounter
  FROM form_encounter
  WHERE pid = @pid_ma
    AND reason = 'DEMO AI: Prior annual/prep note - rooming gaps remain open'
  ORDER BY date DESC
  LIMIT 1
);

SET @enc_bill := (
  SELECT encounter
  FROM form_encounter
  WHERE pid = @pid_bill
    AND reason = 'DEMO AI: Office visit tied to rejected claim - missing diagnosis link'
  ORDER BY date DESC
  LIMIT 1
);

SET @enc_pcp_rx := IF(@enc_pcp IS NOT NULL AND @enc_pcp <= 2147483647, @enc_pcp, NULL);
SET @enc_ma_rx := IF(@enc_ma IS NOT NULL AND @enc_ma <= 2147483647, @enc_ma, NULL);
SET @enc_bill_claim := IF(@enc_bill IS NOT NULL AND @enc_bill <= 2147483647, @enc_bill, 0);

-- ---------------------------------------------------------------------
-- 4) Structured chart data for the physician demo
-- ---------------------------------------------------------------------

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'medical_problem', 'Type 2 diabetes mellitus',
 DATE_ADD(CURDATE(), INTERVAL -400 DAY), 'ICD10:E11.9', 1,
 'Seeded demo chronic condition for AI Co-Pilot physician summary.', @pid_pcp, @provider_username, 'Default'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_pcp
      AND type = 'medical_problem'
      AND title = 'Type 2 diabetes mellitus'
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'medical_problem', 'Hypertension',
 DATE_ADD(CURDATE(), INTERVAL -360 DAY), 'ICD10:I10', 1,
 'Seeded demo chronic condition for AI Co-Pilot physician summary.', @pid_pcp, @provider_username, 'Default'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_pcp
      AND type = 'medical_problem'
      AND title = 'Hypertension'
    LIMIT 1
  );

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_pcp, DATE_ADD(CURDATE(), INTERVAL -120 DAY), DATE_ADD(CURDATE(), INTERVAL -14 DAY),
 @provider_id, @enc_pcp_rx, DATE_ADD(CURDATE(), INTERVAL -120 DAY), 'metformin', '1000 mg twice daily',
 '180 tablets', '1000', 'Continue current metformin regimen for demo patient.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -120 DAY), 'Home medication', 'Order'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_pcp
      AND drug = 'metformin'
      AND active = 1
    LIMIT 1
  );

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_pcp, DATE_ADD(CURDATE(), INTERVAL -140 DAY), DATE_ADD(CURDATE(), INTERVAL -14 DAY),
 @provider_id, @enc_pcp_rx, DATE_ADD(CURDATE(), INTERVAL -140 DAY), 'lisinopril', '10 mg daily',
 '90 tablets', '10', 'Continue lisinopril for demo hypertension management.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -140 DAY), 'Home medication', 'Order'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_pcp
      AND drug = 'lisinopril'
      AND active = 1
    LIMIT 1
  );

-- ---------------------------------------------------------------------
-- 5) Notes carrying the compact chart/demo narrative
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

-- ---------------------------------------------------------------------
-- 6) Billing / claim demo data
-- ---------------------------------------------------------------------

SET @demo_insurance_seed_id := COALESCE((SELECT MAX(id) FROM insurance_companies), 0) + 1000;

INSERT INTO insurance_companies
(`id`, `name`, `cms_id`, `ins_type_code`, `inactive`)
SELECT
 @demo_insurance_seed_id, 'Demo Medicaid Commercial Plan', 'DEMO1003', 0, 0
WHERE NOT EXISTS (
  SELECT 1 FROM insurance_companies
  WHERE name = 'Demo Medicaid Commercial Plan'
  LIMIT 1
);

SET @demo_insurance_id := (
  SELECT id
  FROM insurance_companies
  WHERE name = 'Demo Medicaid Commercial Plan'
  LIMIT 1
);

INSERT INTO insurance_data
(`type`, `provider`, `plan_name`, `policy_number`, `subscriber_lname`, `subscriber_fname`,
 `subscriber_relationship`, `date`, `pid`, `accept_assignment`, `policy_type`)
SELECT
 'primary', CAST(@demo_insurance_id AS CHAR), 'Demo Medicaid/Commercial Plan', 'DEMO-CLM-1003',
 'Carter', 'Thomas', 'self', DATE_ADD(CURDATE(), INTERVAL -180 DAY), @pid_bill, 'TRUE', 'demo'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM insurance_data
    WHERE pid = @pid_bill
      AND type = 'primary'
      AND policy_number = 'DEMO-CLM-1003'
    LIMIT 1
  );

INSERT INTO billing
(`date`, `code_type`, `code`, `pid`, `provider_id`, `user`, `groupname`, `authorized`,
 `encounter`, `code_text`, `billed`, `activity`, `payer_id`, `bill_process`, `bill_date`,
 `modifier`, `units`, `fee`, `justify`, `target`)
SELECT
 DATE_ADD(CURDATE(), INTERVAL -21 DAY) + INTERVAL 13 HOUR,
 'ICD10', 'I10', @pid_bill, @provider_id, @provider_id, 'Default', 1,
 @enc_bill_claim, 'Essential (primary) hypertension', 0, 1, @demo_insurance_id, 0, NULL,
 '', 1, 0.00, '', ''
WHERE @pid_bill IS NOT NULL
  AND @enc_bill_claim IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM billing
    WHERE pid = @pid_bill
      AND encounter = @enc_bill_claim
      AND code_type = 'ICD10'
      AND code = 'I10'
    LIMIT 1
  );

INSERT INTO billing
(`date`, `code_type`, `code`, `pid`, `provider_id`, `user`, `groupname`, `authorized`,
 `encounter`, `code_text`, `billed`, `activity`, `payer_id`, `bill_process`, `bill_date`,
 `modifier`, `units`, `fee`, `justify`, `target`)
SELECT
 DATE_ADD(CURDATE(), INTERVAL -21 DAY) + INTERVAL 13 HOUR,
 'CPT4', '99213', @pid_bill, @provider_id, @provider_id, 'Default', 1,
 @enc_bill_claim, 'Office or other outpatient visit for the evaluation and management of an established patient', 0, 1,
 @demo_insurance_id, 0, NULL, '', 1, 125.00, '', ''
WHERE @pid_bill IS NOT NULL
  AND @enc_bill_claim IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM billing
    WHERE pid = @pid_bill
      AND encounter = @enc_bill_claim
      AND code_type = 'CPT4'
      AND code = '99213'
    LIMIT 1
  );

INSERT INTO claims
(`patient_id`, `encounter_id`, `version`, `payer_id`, `status`, `payer_type`, `bill_process`,
 `bill_time`, `process_time`, `process_file`, `target`, `x12_partner_id`, `submitted_claim`)
SELECT
 @pid_bill, @enc_bill_claim, 1, @demo_insurance_id, 7, 0, 0,
 DATE_ADD(CURDATE(), INTERVAL -20 DAY) + INTERVAL 8 HOUR,
 DATE_ADD(CURDATE(), INTERVAL -20 DAY) + INTERVAL 9 HOUR,
 'demo-missing-diagnosis-link', 'paper', 0,
 'Demo rejection: missing diagnosis link for CPT 99213 office visit.'
WHERE @pid_bill IS NOT NULL
  AND @enc_bill_claim IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM claims
    WHERE patient_id = @pid_bill
      AND encounter_id = @enc_bill_claim
      AND version = 1
    LIMIT 1
  );

-- ---------------------------------------------------------------------
-- 6b) Rich clinical context for all three Medical Co-Pilot quick actions
-- ---------------------------------------------------------------------

UPDATE patient_data
SET genericname1 = 'demo_scenario',
    genericval1 = 'diabetic_foot_risk',
    genericname2 = 'demo_prompt',
    genericval2 = 'Medical Co-Pilot demo patient: diabetes foot wound with infection risk.'
WHERE pubpid = 'DEMO-PCP-1001';

UPDATE patient_data
SET genericname1 = 'demo_scenario',
    genericval1 = 'asthma_respiratory',
    genericname2 = 'demo_prompt',
    genericval2 = 'Medical Co-Pilot demo patient: asthma and respiratory symptom follow-up.'
WHERE pubpid = 'DEMO-MA-1002';

UPDATE patient_data
SET genericname1 = 'demo_scenario',
    genericval1 = 'chest_pain_risk',
    genericname2 = 'demo_prompt',
    genericval2 = 'Medical Co-Pilot demo patient: chest pressure and shortness of breath risk stratification.'
WHERE pubpid = 'DEMO-BILL-1003';

UPDATE openemr_postcalendar_events
SET pc_title = 'Wound care follow-up',
    pc_hometext = CONCAT_WS('\n',
        'DEMO AI FRONT DESK CONTEXT',
        'Appointment type: Wound care follow-up.',
        'Provider: Dr. Nina Patel.',
        'Check-in instructions: Please arrive 15 minutes early and bring photo ID, insurance card, and wound-care supplies if available.',
        'Check-in status: Not checked in.',
        'Demo scenario: right foot sore with redness and drainage in a patient with diabetes, obesity, and neuropathy.'
    ),
    pc_eventDate = '2026-05-10',
    pc_endDate = '2026-05-10',
    pc_startTime = '14:00:00',
    pc_endTime = '14:30:00',
    pc_location = 'OpenEMR Demo Clinic',
    pc_room = 'Room 118'
WHERE pc_pid = CAST(@pid_pcp AS CHAR);

UPDATE openemr_postcalendar_events
SET pc_title = 'Asthma follow-up',
    pc_hometext = CONCAT_WS('\n',
        'DEMO AI FRONT DESK CONTEXT',
        'Appointment type: Asthma follow-up.',
        'Provider: Dr. Samuel Kim.',
        'Check-in instructions: Please arrive 15 minutes early and bring photo ID, insurance card, and inhalers if available.',
        'Check-in status: Not checked in.',
        'Demo scenario: cough, wheezing, nocturnal chest tightness, and increased rescue inhaler use.'
    ),
    pc_eventDate = '2026-05-06',
    pc_endDate = '2026-05-06',
    pc_startTime = '11:15:00',
    pc_endTime = '11:45:00',
    pc_location = 'OpenEMR Demo Clinic',
    pc_room = 'Room 302'
WHERE pc_pid = CAST(@pid_ma AS CHAR);

UPDATE openemr_postcalendar_events
SET pc_title = 'Cardiology follow-up',
    pc_hometext = CONCAT_WS('\n',
        'DEMO AI FRONT DESK CONTEXT',
        'Appointment type: Cardiology follow-up.',
        'Provider: Dr. Evelyn Brooks.',
        'Check-in instructions: Please arrive 15 minutes early and bring photo ID, insurance card, and your current medication list.',
        'Check-in status: Not checked in.',
        'Demo scenario: intermittent chest pressure and exertional shortness of breath in a patient with hypertension, diabetes, and hyperlipidemia.'
    ),
    pc_eventDate = '2026-05-08',
    pc_endDate = '2026-05-08',
    pc_startTime = '09:30:00',
    pc_endTime = '10:00:00',
    pc_location = 'OpenEMR Demo Clinic',
    pc_room = 'Room 204'
WHERE pc_pid = CAST(@pid_bill AS CHAR);

UPDATE form_encounter
SET `date` = DATE_ADD(CURDATE(), INTERVAL -3 DAY) + INTERVAL 10 HOUR,
    reason = 'DEMO AI: Right foot wound with redness and drainage',
    billing_note = 'Chief complaint: right foot sore with redness, drainage, and increasing pain. History of diabetes, obesity, and peripheral neuropathy.'
WHERE encounter = @enc_pcp;

UPDATE form_encounter
SET `date` = DATE_ADD(CURDATE(), INTERVAL -4 DAY) + INTERVAL 11 HOUR,
    reason = 'DEMO AI: Cough, wheezing, and increased rescue inhaler use',
    billing_note = 'Chief complaint: cough, wheezing, chest tightness, and frequent rescue inhaler use. History of asthma and seasonal allergies.'
WHERE encounter = @enc_ma;

UPDATE form_encounter
SET `date` = DATE_ADD(CURDATE(), INTERVAL -2 DAY) + INTERVAL 14 HOUR,
    reason = 'DEMO AI: Chest pressure and shortness of breath risk assessment',
    billing_note = 'Chief complaint: intermittent exertional chest pressure with mild shortness of breath and nausea. Cardiac risk factors are present; no troponin documented yet.'
WHERE encounter = @enc_bill;

UPDATE lists
SET enddate = DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE pid = @pid_pcp
  AND type = 'medical_problem'
  AND title = 'Hypertension'
  AND (enddate IS NULL OR enddate = '0000-00-00 00:00:00');

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'medical_problem', 'Peripheral neuropathy',
 DATE_ADD(CURDATE(), INTERVAL -500 DAY), 'ICD10:G62.9', 1,
 'Seeded demo problem for diabetic foot wound infection risk scenario.', @pid_pcp, @provider_username, 'Default'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_pcp
      AND type = 'medical_problem'
      AND title = 'Peripheral neuropathy'
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'medical_problem', 'Obesity',
 DATE_ADD(CURDATE(), INTERVAL -800 DAY), 'ICD10:E66.9', 1,
 'Seeded demo problem for diabetic foot wound infection risk scenario.', @pid_pcp, @provider_username, 'Default'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_pcp
      AND type = 'medical_problem'
      AND title = 'Obesity'
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'medical_problem', 'Right foot ulcer',
 DATE_ADD(CURDATE(), INTERVAL -12 DAY), 'ICD10:L97.519', 1,
 'Seeded demo problem for foot wound infection evaluation.', @pid_pcp, @provider_username, 'Default'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_pcp
      AND type = 'medical_problem'
      AND title = 'Right foot ulcer'
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'allergy', 'Penicillin - rash',
 DATE_ADD(CURDATE(), INTERVAL -900 DAY), '', 1,
 'Seeded demo allergy for antibiotic safety review.', @pid_pcp, @provider_username, 'Default'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_pcp
      AND type = 'allergy'
      AND title = 'Penicillin - rash'
    LIMIT 1
  );

UPDATE prescriptions
SET active = 0
WHERE patient_id = @pid_pcp
  AND drug = 'lisinopril'
  AND note LIKE 'Continue lisinopril for demo hypertension management.%';

UPDATE prescriptions
SET note = 'Demo diabetes medication for diabetic foot wound scenario. Check renal function and adherence.',
    dosage = '1000 mg twice daily',
    quantity = '180 tablets',
    size = '1000',
    active = 1
WHERE patient_id = @pid_pcp
  AND drug = 'metformin';

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_pcp, DATE_ADD(CURDATE(), INTERVAL -120 DAY), DATE_ADD(CURDATE(), INTERVAL -3 DAY),
 @provider_id, @enc_pcp_rx, DATE_ADD(CURDATE(), INTERVAL -120 DAY), 'glipizide', '5 mg twice daily',
 '180 tablets', '5', 'Demo sulfonylurea for diabetic foot wound scenario. Review hypoglycemia risk and meal timing.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -120 DAY), 'Home medication', 'Order'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_pcp
      AND drug = 'glipizide'
      AND active = 1
    LIMIT 1
  );

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_pcp, DATE_ADD(CURDATE(), INTERVAL -180 DAY), DATE_ADD(CURDATE(), INTERVAL -3 DAY),
 @provider_id, @enc_pcp_rx, DATE_ADD(CURDATE(), INTERVAL -180 DAY), 'gabapentin', '300 mg at bedtime',
 '90 capsules', '300', 'Demo neuropathy medication for foot wound scenario. Review sedation and renal dosing.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -180 DAY), 'Home medication', 'Order'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_pcp
      AND drug = 'gabapentin'
      AND active = 1
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'medical_problem', 'Asthma',
 DATE_ADD(CURDATE(), INTERVAL -1200 DAY), 'ICD10:J45.909', 1,
 'Seeded demo problem for respiratory symptom scenario.', @pid_ma, @provider_username, 'Default'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_ma
      AND type = 'medical_problem'
      AND title = 'Asthma'
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'medical_problem', 'Seasonal allergic rhinitis',
 DATE_ADD(CURDATE(), INTERVAL -1600 DAY), 'ICD10:J30.2', 1,
 'Seeded demo problem for respiratory symptom scenario.', @pid_ma, @provider_username, 'Default'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_ma
      AND type = 'medical_problem'
      AND title = 'Seasonal allergic rhinitis'
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'medical_problem', 'GERD',
 DATE_ADD(CURDATE(), INTERVAL -700 DAY), 'ICD10:K21.9', 1,
 'Seeded demo problem for respiratory differential overlap.', @pid_ma, @provider_username, 'Default'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_ma
      AND type = 'medical_problem'
      AND title = 'GERD'
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'allergy', 'Azithromycin - hives',
 DATE_ADD(CURDATE(), INTERVAL -900 DAY), '', 1,
 'Seeded demo allergy for respiratory medication safety review.', @pid_ma, @provider_username, 'Default'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_ma
      AND type = 'allergy'
      AND title = 'Azithromycin - hives'
    LIMIT 1
  );

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_ma, DATE_ADD(CURDATE(), INTERVAL -365 DAY), DATE_ADD(CURDATE(), INTERVAL -4 DAY),
 @provider_id, @enc_ma_rx, DATE_ADD(CURDATE(), INTERVAL -365 DAY), 'albuterol HFA inhaler', '2 puffs every 4 to 6 hours as needed',
 '1 inhaler', '1', 'Demo rescue inhaler for asthma symptom scenario. Review overuse and inhaler technique.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -365 DAY), 'Home medication', 'Order'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_ma
      AND drug = 'albuterol HFA inhaler'
      AND active = 1
    LIMIT 1
  );

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_ma, DATE_ADD(CURDATE(), INTERVAL -220 DAY), DATE_ADD(CURDATE(), INTERVAL -4 DAY),
 @provider_id, @enc_ma_rx, DATE_ADD(CURDATE(), INTERVAL -220 DAY), 'budesonide-formoterol inhaler', '2 puffs twice daily',
 '1 inhaler', '1', 'Demo controller inhaler for asthma symptom scenario. Review adherence, rinse-after-use, and duplicate therapy.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -220 DAY), 'Home medication', 'Order'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_ma
      AND drug = 'budesonide-formoterol inhaler'
      AND active = 1
    LIMIT 1
  );

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_ma, DATE_ADD(CURDATE(), INTERVAL -300 DAY), DATE_ADD(CURDATE(), INTERVAL -4 DAY),
 @provider_id, @enc_ma_rx, DATE_ADD(CURDATE(), INTERVAL -300 DAY), 'cetirizine', '10 mg daily',
 '90 tablets', '10', 'Demo allergy medication for asthma overlap and trigger review.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -300 DAY), 'Home medication', 'Order'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_ma
      AND drug = 'cetirizine'
      AND active = 1
    LIMIT 1
  );

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_ma, DATE_ADD(CURDATE(), INTERVAL -2 DAY), DATE_ADD(CURDATE(), INTERVAL -2 DAY),
 @provider_id, @enc_ma_rx, DATE_ADD(CURDATE(), INTERVAL -2 DAY), 'prednisone', '40 mg daily for 5 days',
 '10 tablets', '20', 'Demo short steroid burst for recent asthma symptoms. Review insomnia, mood change, and glucose effects.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -2 DAY), 'Home medication', 'Order'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_ma
      AND drug = 'prednisone'
      AND active = 1
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'medical_problem', 'Hypertension',
 DATE_ADD(CURDATE(), INTERVAL -1800 DAY), 'ICD10:I10', 1,
 'Seeded demo problem for chest pressure risk stratification.', @pid_bill, @provider_username, 'Default'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_bill
      AND type = 'medical_problem'
      AND title = 'Hypertension'
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'medical_problem', 'Type 2 diabetes mellitus',
 DATE_ADD(CURDATE(), INTERVAL -1500 DAY), 'ICD10:E11.9', 1,
 'Seeded demo problem for chest pressure risk stratification.', @pid_bill, @provider_username, 'Default'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_bill
      AND type = 'medical_problem'
      AND title = 'Type 2 diabetes mellitus'
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'medical_problem', 'Hyperlipidemia',
 DATE_ADD(CURDATE(), INTERVAL -1400 DAY), 'ICD10:E78.5', 1,
 'Seeded demo problem for chest pressure risk stratification.', @pid_bill, @provider_username, 'Default'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_bill
      AND type = 'medical_problem'
      AND title = 'Hyperlipidemia'
    LIMIT 1
  );

INSERT INTO lists
(`date`, `type`, `title`, `begdate`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`)
SELECT
 NOW(), 'allergy', 'No known drug allergies (NKDA)',
 DATE_ADD(CURDATE(), INTERVAL -365 DAY), '', 1,
 'Seeded demo allergy documentation for chest pain scenario.', @pid_bill, @provider_username, 'Default'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM lists
    WHERE pid = @pid_bill
      AND type = 'allergy'
      AND title = 'No known drug allergies (NKDA)'
    LIMIT 1
  );

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_bill, DATE_ADD(CURDATE(), INTERVAL -400 DAY), DATE_ADD(CURDATE(), INTERVAL -2 DAY),
 @provider_id, @enc_bill_claim, DATE_ADD(CURDATE(), INTERVAL -400 DAY), 'metformin', '1000 mg twice daily',
 '180 tablets', '1000', 'Demo diabetes medication for chest pressure risk review. Confirm adherence and renal monitoring.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -400 DAY), 'Home medication', 'Order'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_bill
      AND drug = 'metformin'
      AND active = 1
    LIMIT 1
  );

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_bill, DATE_ADD(CURDATE(), INTERVAL -500 DAY), DATE_ADD(CURDATE(), INTERVAL -2 DAY),
 @provider_id, @enc_bill_claim, DATE_ADD(CURDATE(), INTERVAL -500 DAY), 'lisinopril', '20 mg daily',
 '90 tablets', '20', 'Demo blood pressure medication for chest pressure risk review. Monitor BP, renal function, and potassium.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -500 DAY), 'Home medication', 'Order'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_bill
      AND drug = 'lisinopril'
      AND active = 1
    LIMIT 1
  );

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_bill, DATE_ADD(CURDATE(), INTERVAL -520 DAY), DATE_ADD(CURDATE(), INTERVAL -2 DAY),
 @provider_id, @enc_bill_claim, DATE_ADD(CURDATE(), INTERVAL -520 DAY), 'atorvastatin', '40 mg nightly',
 '90 tablets', '40', 'Demo lipid-lowering medication for chest pressure risk review. Review adherence and muscle symptoms.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -520 DAY), 'Home medication', 'Order'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_bill
      AND drug = 'atorvastatin'
      AND active = 1
    LIMIT 1
  );

INSERT INTO prescriptions
(`patient_id`, `date_added`, `date_modified`, `provider_id`, `encounter`, `start_date`, `drug`, `dosage`,
 `quantity`, `size`, `note`, `active`, `user`, `txDate`, `usage_category_title`, `request_intent_title`)
SELECT
 @pid_bill, DATE_ADD(CURDATE(), INTERVAL -540 DAY), DATE_ADD(CURDATE(), INTERVAL -2 DAY),
 @provider_id, @enc_bill_claim, DATE_ADD(CURDATE(), INTERVAL -540 DAY), 'aspirin', '81 mg daily',
 '90 tablets', '81', 'Demo antiplatelet medication for chest pressure risk review. Any ongoing use should still be confirmed by the clinician.', 1, @provider_username,
 DATE_ADD(CURDATE(), INTERVAL -540 DAY), 'Home medication', 'Order'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM prescriptions
    WHERE patient_id = @pid_bill
      AND drug = 'aspirin'
      AND active = 1
    LIMIT 1
  );

INSERT INTO form_vitals
(`date`, `pid`, `user`, `groupname`, `authorized`, `activity`, `bps`, `bpd`, `weight`, `height`,
 `temperature`, `pulse`, `respiration`, `note`, `BMI`, `oxygen_saturation`)
SELECT
 DATE_ADD(CURDATE(), INTERVAL -3 DAY) + INTERVAL 10 HOUR, @pid_pcp, @provider_username, 'Default', 1, 1,
 '146', '88', 238.00, 71.00, 99.1, 96, 18,
 'DEMO AI CO-PILOT SEEDED VITALS', 33.2, 97.0
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM form_vitals
    WHERE pid = @pid_pcp
      AND note = 'DEMO AI CO-PILOT SEEDED VITALS'
    LIMIT 1
  );

UPDATE form_vitals
SET `date` = DATE_ADD(CURDATE(), INTERVAL -3 DAY) + INTERVAL 10 HOUR,
    bps = '146',
    bpd = '88',
    weight = 238.00,
    height = 71.00,
    temperature = 99.1,
    pulse = 96,
    respiration = 18,
    BMI = 33.2,
    oxygen_saturation = 97.0
WHERE pid = @pid_pcp
  AND note = 'DEMO AI CO-PILOT SEEDED VITALS';

INSERT INTO form_vitals
(`date`, `pid`, `user`, `groupname`, `authorized`, `activity`, `bps`, `bpd`, `weight`, `height`,
 `temperature`, `pulse`, `respiration`, `note`, `BMI`, `oxygen_saturation`)
SELECT
 DATE_ADD(CURDATE(), INTERVAL -4 DAY) + INTERVAL 11 HOUR, @pid_ma, @provider_username, 'Default', 1, 1,
 '132', '84', 164.00, 65.00, 98.7, 102, 20,
 'DEMO AI CO-PILOT SEEDED VITALS', 27.3, 97.0
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM form_vitals
    WHERE pid = @pid_ma
      AND note = 'DEMO AI CO-PILOT SEEDED VITALS'
    LIMIT 1
  );

UPDATE form_vitals
SET `date` = DATE_ADD(CURDATE(), INTERVAL -4 DAY) + INTERVAL 11 HOUR,
    bps = '132',
    bpd = '84',
    weight = 164.00,
    height = 65.00,
    temperature = 98.7,
    pulse = 102,
    respiration = 20,
    BMI = 27.3,
    oxygen_saturation = 97.0
WHERE pid = @pid_ma
  AND note = 'DEMO AI CO-PILOT SEEDED VITALS';

INSERT INTO form_vitals
(`date`, `pid`, `user`, `groupname`, `authorized`, `activity`, `bps`, `bpd`, `weight`, `height`,
 `temperature`, `pulse`, `respiration`, `note`, `BMI`, `oxygen_saturation`)
SELECT
 DATE_ADD(CURDATE(), INTERVAL -2 DAY) + INTERVAL 14 HOUR, @pid_bill, @provider_username, 'Default', 1, 1,
 '162', '96', 212.00, 70.00, 98.4, 104, 20,
 'DEMO AI CO-PILOT SEEDED VITALS', 30.4, 96.0
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM form_vitals
    WHERE pid = @pid_bill
      AND note = 'DEMO AI CO-PILOT SEEDED VITALS'
    LIMIT 1
  );

UPDATE form_vitals
SET `date` = DATE_ADD(CURDATE(), INTERVAL -2 DAY) + INTERVAL 14 HOUR,
    bps = '162',
    bpd = '96',
    weight = 212.00,
    height = 70.00,
    temperature = 98.4,
    pulse = 104,
    respiration = 20,
    BMI = 30.4,
    oxygen_saturation = 96.0
WHERE pid = @pid_bill
  AND note = 'DEMO AI CO-PILOT SEEDED VITALS';

INSERT INTO pnotes
(`date`, `body`, `pid`, `user`, `groupname`, `activity`, `authorized`, `title`, `assigned_to`, `message_status`)
SELECT
 NOW(),
 CONCAT_WS('\n',
 'DEMO AI CLINICAL CONTEXT',
  'Chief complaint: Right foot sore with redness, drainage, and increased pain.',
  'Visit type: established problem-focused diabetic foot visit.',
  'History of present illness: Adult male with diabetes reports a right plantar foot wound for about 1 week with new redness, serous drainage, and more pain over the last 2 days. He also notes numbness in the foot and denies documented fever.',
  'Symptoms: right foot sore, redness, drainage, numbness, increased pain, difficulty with prolonged walking.',
  'Past medical history: type 2 diabetes mellitus, obesity, peripheral neuropathy.',
  'Risk factors: poor glycemic control, neuropathy, obesity, prolonged standing at work.',
  'Allergies: Penicillin - rash.',
  'Recent labs: A1C 9.6%; random glucose 248; WBC 11.8; creatinine 1.1.',
  'Recent exam: 2 cm plantar ulcer with surrounding erythema and serous drainage; diminished protective sensation; no documented fever; pedal pulses reduced but present.',
  'Medication concerns: confirm adherence to metformin and glipizide, review hypoglycemia risk, and check renal dosing for gabapentin.',
  'Billing support: document wound size or depth if assessed, drainage or erythema severity, neuropathy impact, focused foot exam findings, diabetes management reviewed, and referral or close follow-up rationale.',
  'Follow-up considerations: wound assessment, infection severity review, imaging or labs if deep infection is suspected, offloading, and urgent precautions for spreading infection.'
 ),
 @pid_pcp, @provider_username, 'Default', 1, 1,
 'DEMO AI - Clinical Context', @provider_username, 'New'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM pnotes
    WHERE pid = @pid_pcp
      AND title = 'DEMO AI - Clinical Context'
    LIMIT 1
  );

UPDATE pnotes
SET body = CONCAT_WS('\n',
  'DEMO AI CLINICAL CONTEXT',
  'Chief complaint: Right foot sore with redness, drainage, and increased pain.',
  'Visit type: established problem-focused diabetic foot visit.',
  'History of present illness: Adult male with diabetes reports a right plantar foot wound for about 1 week with new redness, serous drainage, and more pain over the last 2 days. He also notes numbness in the foot and denies documented fever.',
  'Symptoms: right foot sore, redness, drainage, numbness, increased pain, difficulty with prolonged walking.',
  'Past medical history: type 2 diabetes mellitus, obesity, peripheral neuropathy.',
  'Risk factors: poor glycemic control, neuropathy, obesity, prolonged standing at work.',
  'Allergies: Penicillin - rash.',
  'Recent labs: A1C 9.6%; random glucose 248; WBC 11.8; creatinine 1.1.',
  'Recent exam: 2 cm plantar ulcer with surrounding erythema and serous drainage; diminished protective sensation; no documented fever; pedal pulses reduced but present.',
  'Medication concerns: confirm adherence to metformin and glipizide, review hypoglycemia risk, and check renal dosing for gabapentin.',
  'Billing support: document wound size or depth if assessed, drainage or erythema severity, neuropathy impact, focused foot exam findings, diabetes management reviewed, and referral or close follow-up rationale.',
  'Follow-up considerations: wound assessment, infection severity review, imaging or labs if deep infection is suspected, offloading, and urgent precautions for spreading infection.'
 )
WHERE pid = @pid_pcp
  AND title = 'DEMO AI - Clinical Context';

INSERT INTO pnotes
(`date`, `body`, `pid`, `user`, `groupname`, `activity`, `authorized`, `title`, `assigned_to`, `message_status`)
SELECT
 NOW(),
 CONCAT_WS('\n',
  'DEMO AI RECENT ENCOUNTER NOTE',
  'Subjective: Patient reports worsening right foot soreness with drainage and numbness. Denies documented fever or chills.',
  'Objective: Vitals show BP 146/88, pulse 96, temp 99.1 F, oxygen saturation 97%. Exam note describes plantar ulcer with surrounding erythema and drainage.',
  'Assessment: Diabetic foot ulcer with concern for cellulitis or deeper infection risk in the setting of poor glycemic control and neuropathy.',
  'Plan: Review wound severity, infection workup, glycemic control, offloading, referral needs, and ER precautions if symptoms worsen.',
  'Safety: Demo note only. No automated orders or treatment changes.'
 ),
 @pid_pcp, @provider_username, 'Default', 1, 1,
 'DEMO AI - Recent Encounter Note', @provider_username, 'New'
WHERE @pid_pcp IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM pnotes
    WHERE pid = @pid_pcp
      AND title = 'DEMO AI - Recent Encounter Note'
    LIMIT 1
  );

UPDATE pnotes
SET body = CONCAT_WS('\n',
  'DEMO AI RECENT ENCOUNTER NOTE',
  'Subjective: Patient reports worsening right foot soreness with drainage and numbness. Denies documented fever or chills.',
  'Objective: Vitals show BP 146/88, pulse 96, temp 99.1 F, oxygen saturation 97%. Exam note describes plantar ulcer with surrounding erythema and drainage.',
  'Assessment: Diabetic foot ulcer with concern for cellulitis or deeper infection risk in the setting of poor glycemic control and neuropathy.',
  'Plan: Review wound severity, infection workup, glycemic control, offloading, referral needs, and ER precautions if symptoms worsen.',
  'Safety: Demo note only. No automated orders or treatment changes.'
 )
WHERE pid = @pid_pcp
  AND title = 'DEMO AI - Recent Encounter Note';

INSERT INTO pnotes
(`date`, `body`, `pid`, `user`, `groupname`, `activity`, `authorized`, `title`, `assigned_to`, `message_status`)
SELECT
 NOW(),
 CONCAT_WS('\n',
 'DEMO AI CLINICAL CONTEXT',
  'Chief complaint: Cough, wheezing, chest tightness, and increased rescue inhaler use.',
  'Visit type: established respiratory follow-up visit.',
  'History of present illness: Adult female with asthma reports 5 days of cough and wheezing that are worse at night, with increased albuterol use and intermittent chest tightness. She denies severe distress or syncope.',
  'Symptoms: cough, wheezing, nocturnal chest tightness, mild shortness of breath, rescue inhaler use most days.',
  'Past medical history: asthma, seasonal allergic rhinitis, GERD.',
  'Risk factors: spring pollen exposure, inconsistent controller adherence, recent nighttime symptoms, anxiety when short of breath.',
  'Allergies: Azithromycin - hives.',
  'Recent labs: No CBC or chest imaging documented yet; no severe hypoxia documented.',
  'Recent exam: Expiratory wheeze documented without severe distress; speaking full sentences; oxygen saturation 97%.',
  'Medication concerns: assess rescue inhaler overuse, controller adherence, inhaler technique, and recent prednisone side effects.',
  'Billing support: document rescue inhaler frequency, nocturnal symptoms, controller adherence, respiratory findings, trigger review, and what counseling or reassessment occurred during the visit.',
  'Follow-up considerations: asthma control review, trigger review, peak flow consideration, and urgent precautions for worsening breathing symptoms.'
 ),
 @pid_ma, @provider_username, 'Default', 1, 1,
 'DEMO AI - Clinical Context', @provider_username, 'New'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM pnotes
    WHERE pid = @pid_ma
      AND title = 'DEMO AI - Clinical Context'
    LIMIT 1
  );

UPDATE pnotes
SET body = CONCAT_WS('\n',
  'DEMO AI CLINICAL CONTEXT',
  'Chief complaint: Cough, wheezing, chest tightness, and increased rescue inhaler use.',
  'Visit type: established respiratory follow-up visit.',
  'History of present illness: Adult female with asthma reports 5 days of cough and wheezing that are worse at night, with increased albuterol use and intermittent chest tightness. She denies severe distress or syncope.',
  'Symptoms: cough, wheezing, nocturnal chest tightness, mild shortness of breath, rescue inhaler use most days.',
  'Past medical history: asthma, seasonal allergic rhinitis, GERD.',
  'Risk factors: spring pollen exposure, inconsistent controller adherence, recent nighttime symptoms, anxiety when short of breath.',
  'Allergies: Azithromycin - hives.',
  'Recent labs: No CBC or chest imaging documented yet; no severe hypoxia documented.',
  'Recent exam: Expiratory wheeze documented without severe distress; speaking full sentences; oxygen saturation 97%.',
  'Medication concerns: assess rescue inhaler overuse, controller adherence, inhaler technique, and recent prednisone side effects.',
  'Billing support: document rescue inhaler frequency, nocturnal symptoms, controller adherence, respiratory findings, trigger review, and what counseling or reassessment occurred during the visit.',
  'Follow-up considerations: asthma control review, trigger review, peak flow consideration, and urgent precautions for worsening breathing symptoms.'
 )
WHERE pid = @pid_ma
  AND title = 'DEMO AI - Clinical Context';

INSERT INTO pnotes
(`date`, `body`, `pid`, `user`, `groupname`, `activity`, `authorized`, `title`, `assigned_to`, `message_status`)
SELECT
 NOW(),
 CONCAT_WS('\n',
  'DEMO AI RECENT ENCOUNTER NOTE',
  'Subjective: Patient reports cough, wheezing, and more nighttime symptoms with increased rescue inhaler use.',
  'Objective: Vitals show pulse 102, respirations 20, oxygen saturation 97%, and no severe distress. Mild expiratory wheeze is documented.',
  'Assessment: Mild to moderate asthma symptom flare with overlap from allergic triggers; pneumonia is not ruled in by current chart data.',
  'Plan: Reassess inhaler technique and controller adherence, review triggers, and escalate urgently if breathing difficulty worsens.',
  'Safety: Demo note only. No automated orders or treatment changes.'
 ),
 @pid_ma, @provider_username, 'Default', 1, 1,
 'DEMO AI - Recent Encounter Note', @provider_username, 'New'
WHERE @pid_ma IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM pnotes
    WHERE pid = @pid_ma
      AND title = 'DEMO AI - Recent Encounter Note'
    LIMIT 1
  );

UPDATE pnotes
SET body = CONCAT_WS('\n',
  'DEMO AI RECENT ENCOUNTER NOTE',
  'Subjective: Patient reports cough, wheezing, and more nighttime symptoms with increased rescue inhaler use.',
  'Objective: Vitals show pulse 102, respirations 20, oxygen saturation 97%, and no severe distress. Mild expiratory wheeze is documented.',
  'Assessment: Mild to moderate asthma symptom flare with overlap from allergic triggers; pneumonia is not ruled in by current chart data.',
  'Plan: Reassess inhaler technique and controller adherence, review triggers, and escalate urgently if breathing difficulty worsens.',
  'Safety: Demo note only. No automated orders or treatment changes.'
 )
WHERE pid = @pid_ma
  AND title = 'DEMO AI - Recent Encounter Note';

INSERT INTO pnotes
(`date`, `body`, `pid`, `user`, `groupname`, `activity`, `authorized`, `title`, `assigned_to`, `message_status`)
SELECT
 NOW(),
 CONCAT_WS('\n',
 'DEMO AI CLINICAL CONTEXT',
  'Chief complaint: Intermittent chest pressure with mild shortness of breath.',
  'Visit type: urgent outpatient chest-pain evaluation.',
  'History of present illness: Adult male with hypertension, diabetes, and hyperlipidemia reports intermittent substernal chest pressure for 2 days, worse with exertion and stairs, associated with mild shortness of breath and nausea. No syncope is documented.',
  'Symptoms: exertional chest pressure, mild shortness of breath, nausea, fatigue.',
  'Past medical history: hypertension, type 2 diabetes mellitus, hyperlipidemia.',
  'Risk factors: age over 60, diabetes, hypertension, hyperlipidemia, former smoker, family history of coronary artery disease.',
  'Allergies: No known drug allergies (NKDA).',
  'Recent labs: A1C 8.6%; LDL 148; random glucose 228; creatinine 1.0; no troponin documented yet.',
  'Recent exam: Elevated blood pressure and heart rate are documented. Patient is speaking in full sentences; no severe distress or focal neurologic deficit is documented.',
  'Medication concerns: verify adherence to metformin, lisinopril, atorvastatin, and aspirin; confirm whether any doses were missed before symptom onset.',
  'Billing support: document exertional chest-pain features, associated dyspnea or nausea, risk factors reviewed, urgency assessment, monitoring performed, and rationale for escalation or close follow-up.',
  'Follow-up considerations: urgent evaluation or escalation if symptoms are active or worsening, ECG and troponin consideration, and close blood pressure and diabetes follow-up after urgent review.'
 ),
 @pid_bill, @provider_username, 'Default', 1, 1,
 'DEMO AI - Clinical Context', @provider_username, 'New'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM pnotes
    WHERE pid = @pid_bill
      AND title = 'DEMO AI - Clinical Context'
    LIMIT 1
  );

UPDATE pnotes
SET body = CONCAT_WS('\n',
  'DEMO AI CLINICAL CONTEXT',
  'Chief complaint: Intermittent chest pressure with mild shortness of breath.',
  'Visit type: urgent outpatient chest-pain evaluation.',
  'History of present illness: Adult male with hypertension, diabetes, and hyperlipidemia reports intermittent substernal chest pressure for 2 days, worse with exertion and stairs, associated with mild shortness of breath and nausea. No syncope is documented.',
  'Symptoms: exertional chest pressure, mild shortness of breath, nausea, fatigue.',
  'Past medical history: hypertension, type 2 diabetes mellitus, hyperlipidemia.',
  'Risk factors: age over 60, diabetes, hypertension, hyperlipidemia, former smoker, family history of coronary artery disease.',
  'Allergies: No known drug allergies (NKDA).',
  'Recent labs: A1C 8.6%; LDL 148; random glucose 228; creatinine 1.0; no troponin documented yet.',
  'Recent exam: Elevated blood pressure and heart rate are documented. Patient is speaking in full sentences; no severe distress or focal neurologic deficit is documented.',
  'Medication concerns: verify adherence to metformin, lisinopril, atorvastatin, and aspirin; confirm whether any doses were missed before symptom onset.',
  'Billing support: document exertional chest-pain features, associated dyspnea or nausea, risk factors reviewed, urgency assessment, monitoring performed, and rationale for escalation or close follow-up.',
  'Follow-up considerations: urgent evaluation or escalation if symptoms are active or worsening, ECG and troponin consideration, and close blood pressure and diabetes follow-up after urgent review.'
 )
WHERE pid = @pid_bill
  AND title = 'DEMO AI - Clinical Context';

INSERT INTO pnotes
(`date`, `body`, `pid`, `user`, `groupname`, `activity`, `authorized`, `title`, `assigned_to`, `message_status`)
SELECT
 NOW(),
 CONCAT_WS('\n',
  'DEMO AI RECENT ENCOUNTER NOTE',
  'Subjective: Patient describes intermittent exertional chest pressure for 2 days with mild shortness of breath and nausea. No syncope is documented.',
  'Objective: Vitals show BP 162/96, pulse 104, respirations 20, oxygen saturation 96%. No troponin is documented yet.',
  'Assessment: Cardiac ischemia must remain on the differential given exertional symptoms and multiple risk factors, while lower-acuity gastrointestinal or musculoskeletal causes are still possible.',
  'Plan: Clarify whether symptoms are active, consider urgent escalation with ECG and troponin review, monitor vitals, and revisit chronic disease control after acute evaluation.',
  'Safety: Demo note only. No automated orders or treatment changes.'
 ),
 @pid_bill, @provider_username, 'Default', 1, 1,
 'DEMO AI - Recent Encounter Note', @provider_username, 'New'
WHERE @pid_bill IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM pnotes
    WHERE pid = @pid_bill
      AND title = 'DEMO AI - Recent Encounter Note'
    LIMIT 1
  );

UPDATE pnotes
SET body = CONCAT_WS('\n',
  'DEMO AI RECENT ENCOUNTER NOTE',
  'Subjective: Patient describes intermittent exertional chest pressure for 2 days with mild shortness of breath and nausea. No syncope is documented.',
  'Objective: Vitals show BP 162/96, pulse 104, respirations 20, oxygen saturation 96%. No troponin is documented yet.',
  'Assessment: Cardiac ischemia must remain on the differential given exertional symptoms and multiple risk factors, while lower-acuity gastrointestinal or musculoskeletal causes are still possible.',
  'Plan: Clarify whether symptoms are active, consider urgent escalation with ECG and troponin review, monitor vitals, and revisit chronic disease control after acute evaluation.',
  'Safety: Demo note only. No automated orders or treatment changes.'
 )
WHERE pid = @pid_bill
  AND title = 'DEMO AI - Recent Encounter Note';

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
  e.pc_eventDate,
  e.pc_startTime,
  e.pc_title,
  e.pc_hometext
FROM openemr_postcalendar_events AS e
JOIN patient_data AS p ON CAST(p.pid AS CHAR) = e.pc_pid
WHERE p.pubpid IN ('DEMO-PCP-1001', 'DEMO-MA-1002', 'DEMO-BILL-1003')
ORDER BY p.pubpid, e.pc_eventDate, e.pc_startTime;

SELECT
  p.pubpid,
  n.title,
  LEFT(n.body, 180) AS note_preview
FROM pnotes AS n
JOIN patient_data AS p ON p.pid = n.pid
WHERE p.pubpid IN ('DEMO-PCP-1001', 'DEMO-MA-1002', 'DEMO-BILL-1003')
ORDER BY p.pubpid, n.date DESC;

SELECT
  p.pubpid,
  b.code_type,
  b.code,
  b.justify,
  c.status AS claim_status
FROM billing AS b
JOIN patient_data AS p ON p.pid = b.pid
LEFT JOIN claims AS c ON c.patient_id = b.pid AND c.encounter_id = b.encounter
WHERE p.pubpid = 'DEMO-BILL-1003'
ORDER BY b.code_type, b.code;
