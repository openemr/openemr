--
--  Comment Meta Language Constructs:
--
--  #IfNotTable
--    argument: table_name
--    behavior: if the table_name does not exist,  the block will be executed

--  #IfTable
--    argument: table_name
--    behavior: if the table_name does exist, the block will be executed

--  #IfColumn
--    arguments: table_name colname
--    behavior:  if the table and column exist,  the block will be executed

--  #IfMissingColumn
--    arguments: table_name colname
--    behavior:  if the table exists but the column does not,  the block will be executed

--  #IfNotColumnType
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a column colname with a data type equal to value, then the block will be executed

--  #IfNotColumnTypeDefault
--    arguments: table_name colname value value2
--    behavior:  If the table table_name does not have a column colname with a data type equal to value and a default equal to value2, then the block will be executed

--  #IfNotRow
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a row where colname = value, the block will be executed.

--  #IfNotRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfNotRow3D
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfNotRow4D
--    arguments: table_name colname value colname2 value2 colname3 value3 colname4 value4
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3 AND colname4 = value4, the block will be executed.

--  #IfNotRow2Dx2
--    desc:      This is a very specialized function to allow adding items to the list_options table to avoid both redundant option_id and title in each element.
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  The block will be executed if both statements below are true:
--               1) The table table_name does not have a row where colname = value AND colname2 = value2.
--               2) The table table_name does not have a row where colname = value AND colname3 = value3.

--  #IfRow
--    arguments: table_name colname value
--    behavior:  If the table table_name does have a row where colname = value, the block will be executed.

--  #IfRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfRow3D
--        arguments: table_name colname value colname2 value2 colname3 value3
--        behavior:  If the table table_name does have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfRowIsNull
--    arguments: table_name colname
--    behavior:  If the table table_name does have a row where colname is null, the block will be executed.

--  #IfIndex
--    desc:      This function is most often used for dropping of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the table and index exist the relevant statements are executed, otherwise not.

--  #IfNotIndex
--    desc:      This function will allow adding of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the index does not exist, it will be created

--  #EndIf
--    all blocks are terminated with a #EndIf statement.

--  #IfNotListReaction
--    Custom function for creating Reaction List

--  #IfNotListOccupation
--    Custom function for creating Occupation List

--  #IfTextNullFixNeeded
--    desc: convert all text fields without default null to have default null.
--    arguments: none

--  #IfTableEngine
--    desc:      Execute SQL if the table has been created with given engine specified.
--    arguments: table_name engine
--    behavior:  Use when engine conversion requires more than one ALTER TABLE

--  #IfInnoDBMigrationNeeded
--    desc: find all MyISAM tables and convert them to InnoDB.
--    arguments: none
--    behavior: can take a long time.

--  #IfDocumentNamingNeeded
--    desc: populate name field with document names.
--    arguments: none

--  #IfUpdateEditOptionsNeeded
--    desc: Change Layout edit options.
--    arguments: mode(add or remove) layout_form_id the_edit_option comma_separated_list_of_field_ids

--  #IfVitalsDatesNeeded
--    desc: Change date from zeroes to date of vitals form creation.
--    arguments: none

--  #IfMBOEncounterNeeded
--    desc: Add encounter to the form_misc_billing_options table
--    arguments: none

-- Fix swapped SNOMED CT codes for the administrative_sex list (see #13767).
-- 248153007 is Male (finding), 248152002 is Female (finding); the seed had them reversed.
#IfRow3D list_options list_id administrative_sex option_id Male codes SNOMED-CT:248152002
UPDATE `list_options` SET `codes` = 'SNOMED-CT:248153007' WHERE `list_id` = 'administrative_sex' AND `option_id` = 'Male';
#EndIf

#IfRow3D list_options list_id administrative_sex option_id Female codes SNOMED-CT:248153007
UPDATE `list_options` SET `codes` = 'SNOMED-CT:248152002' WHERE `list_id` = 'administrative_sex' AND `option_id` = 'Female';
#EndIf

-- Payer claim control numbers (ICN/DCN) can run to the X12 REF02 maximum of 50.
#IfNotColumnType ar_activity payer_claim_number varchar(50)
ALTER TABLE `ar_activity` MODIFY `payer_claim_number` VARCHAR(50) DEFAULT NULL COMMENT 'CLP07 from the payer 835';
#EndIf

-- HCFA box 22a is not Medicaid-specific; rename for clarity and widen to REF02's maximum.
#IfColumn form_misc_billing_options medicaid_original_reference
ALTER TABLE `form_misc_billing_options` CHANGE `medicaid_original_reference` `original_reference_number` VARCHAR(50) DEFAULT NULL;
#EndIf

#IfNotColumnType form_misc_billing_options original_reference_number varchar(50)
ALTER TABLE `form_misc_billing_options` MODIFY `original_reference_number` VARCHAR(50) DEFAULT NULL;
#EndIf

-- HCFA box 22 lost its "Medicaid" prefix in the 02/12 revision of the form.
#IfColumn form_misc_billing_options medicaid_resubmission_code
ALTER TABLE `form_misc_billing_options` CHANGE `medicaid_resubmission_code` `resubmission_code` VARCHAR(10) DEFAULT NULL;
#EndIf

-- ICD-10-CM and ICD-10-PCS FY 2027 code sets, effective 2026-10-01.
#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2026-10-01 load_filename 2027-code-descriptions-in-tabular-order.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2026-10-01', '2027-code-descriptions-in-tabular-order.zip', 'd71d4467481e3396991576a02e030213');
#EndIf
#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2026-10-01 load_filename zip-file-3-2027-icd-10-pcs-codes-file.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2026-10-01', 'zip-file-3-2027-icd-10-pcs-codes-file.zip', 'ca7dd9e61622a3b9faf766ac6b1cd15d');
#EndIf

-- ICD-10-CM and ICD-10-PCS April 1, 2026 mid-year updates. CMS reuses the October
-- 2025 PCS file name, so the checksum tells the two releases apart.
#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2026-04-01 load_filename april-1-2026-code-descriptions-in-tabular-order.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2026-04-01', 'april-1-2026-code-descriptions-in-tabular-order.zip', '22700f631c4e0194467b96d0c1f83e67');
#EndIf
#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2026-04-01 load_filename zip-file-3-2026-icd-10-pcs-codes-file.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2026-04-01', 'zip-file-3-2026-icd-10-pcs-codes-file.zip', '3521b090d9ca58af9c8d73bbf2b3110a');
#EndIf

-- Add TOTP replay-protection column: records the RFC 6238 time slice
-- (floor(unix_ts/period)) of the last successfully consumed code so
-- MfaUtils::checkTOTP can atomically reject any subsequent code whose
-- slice is not strictly greater. Guards against A-B-A replay across
-- two adjacent valid codes within the 90-second acceptance window.
#IfMissingColumn login_mfa_registrations last_used_step
ALTER TABLE `login_mfa_registrations` ADD COLUMN `last_used_step` bigint DEFAULT NULL COMMENT 'TOTP time slice (RFC 6238) of the last consumed code. Incoming codes must land on a strictly greater slice; guards against A-B-A replay across two adjacent valid codes within the 90s acceptance window.';
#EndIf

-- Add per-user + per-IP MFA challenge failure counters. Kept
-- independent of login_fail_counter / ip_login_fail_counter so an
-- in-progress MFA brute force is not zeroed out by the
-- password-verify-success reset that happens on every attempt.
#IfMissingColumn users_secure mfa_fail_counter
ALTER TABLE `users_secure` ADD COLUMN `mfa_fail_counter` bigint DEFAULT 0 COMMENT 'Per-user MFA challenge failure counter. Independent of login_fail_counter so an in-progress MFA brute force does not get zeroed out by the password verify success that happens on every attempt.';
#EndIf
#IfMissingColumn users_secure mfa_last_fail
ALTER TABLE `users_secure` ADD COLUMN `mfa_last_fail` datetime DEFAULT NULL COMMENT 'Timestamp of the last MFA challenge failure. Used for time-based counter reset.';
#EndIf
#IfMissingColumn ip_tracking mfa_login_fail_counter
ALTER TABLE `ip_tracking` ADD COLUMN `mfa_login_fail_counter` bigint DEFAULT 0 COMMENT 'Per-IP MFA challenge failure counter. Independent of ip_login_fail_counter so an in-progress MFA brute force is not zeroed out by the password verify success on each attempt.';
#EndIf
#IfMissingColumn ip_tracking mfa_last_login_fail
ALTER TABLE `ip_tracking` ADD COLUMN `mfa_last_login_fail` datetime DEFAULT NULL COMMENT 'Timestamp of the last MFA challenge failure from this IP. Used for time-based counter reset.';
#EndIf

-- Add per-portal-account failure counter. Portal auth has no per-user
-- counter equivalent to users_secure.login_fail_counter — only the
-- shared IP counter. An attacker holding valid credentials for one
-- portal account could otherwise burn (threshold - 1) guesses against
-- account B, log into A to zero the shared IP counter, and repeat
-- indefinitely. Per-account counter is cleared only on success for
-- that specific account, so blocks accumulate per victim account.
#IfMissingColumn patient_access_onsite portal_fail_counter
ALTER TABLE `patient_access_onsite` ADD COLUMN `portal_fail_counter` bigint DEFAULT 0 COMMENT 'Per-portal-account failure counter. Independent of ip_login_fail_counter so a valid login on account A cannot clear an in-progress brute force against account B.';
#EndIf
#IfMissingColumn patient_access_onsite portal_last_fail
ALTER TABLE `patient_access_onsite` ADD COLUMN `portal_last_fail` datetime DEFAULT NULL COMMENT 'Timestamp of the last portal login failure for this account. Used for time-based counter reset.';
#EndIf

#IfMissingColumn drugs billing_units
ALTER TABLE `drugs` ADD COLUMN `billing_units` int(11) DEFAULT NULL COMMENT 'default units when the related HCPCS code is added to a fee sheet' AFTER `related_code`;
#EndIf

#IfMissingColumn drugs ndc_uom
ALTER TABLE `drugs` ADD COLUMN `ndc_uom` varchar(2) NOT NULL DEFAULT '' COMMENT 'NDC unit of measure for the related HCPCS service line' AFTER `billing_units`;
#EndIf

#IfMissingColumn drugs ndc_quantity
ALTER TABLE `drugs` ADD COLUMN `ndc_quantity` decimal(10,3) DEFAULT NULL COMMENT 'NDC quantity for the related HCPCS service line' AFTER `ndc_uom`;
#EndIf

-- OAuth2 clients may now use only the grant types they registered for
-- (oauth_clients.grant_types was stored but never enforced). Backfill it so
-- existing clients keep working after the upgrade:
--   * no recorded grant types: the RFC 7591 default, authorization_code
--   * confidential backend-services clients (system/ scopes plus a JWKS):
--     client_credentials, which the registration UI never recorded. A JWKS is
--     required because this grant only ever authenticated with a signed JWT
--     assertion; a client holding just a secret could not use it before either.
--   * clients that have already used the password grant, per
--     oauth_trusted_user.grant_type: password
-- A client that named grant types when it registered keeps them as recorded.
-- A client that never named the password grant and has not used it yet is not
-- given it here; an administrator allows it under Admin > System > API Clients.
-- Each statement is idempotent.
#IfColumn oauth_clients grant_types
UPDATE `oauth_clients` SET `grant_types` = 'authorization_code' WHERE `grant_types` IS NULL OR `grant_types` = '';
UPDATE `oauth_clients` SET `grant_types` = CONCAT(`grant_types`, '|client_credentials') WHERE `is_confidential` = 1 AND `scope` LIKE '%system/%' AND ((`jwks` IS NOT NULL AND `jwks` <> '') OR (`jwks_uri` IS NOT NULL AND `jwks_uri` <> '')) AND CONCAT('|', `grant_types`, '|') NOT LIKE '%|client_credentials|%';
UPDATE `oauth_clients` SET `grant_types` = CONCAT(`grant_types`, '|password') WHERE `client_id` IN (SELECT `client_id` FROM `oauth_trusted_user` WHERE `grant_type` = 'password') AND CONCAT('|', `grant_types`, '|') NOT LIKE '%|password|%';
#EndIf

-- eCQM 2026 Performance Period measures (built with ecqm-bundle-builder)
#IfNotRow list_options list_id ecqm_2026_reporting
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`) VALUES ('lists','ecqm_2026_reporting','eCQM 2026 Performance Period',0,1,0,'');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS2v15','Preventive Care and Screening: Screening for Depression and Follow-Up Plan',10,0,'Percentage of patients aged 12 years and older screened for depression on the date of the encounter or up to 14 days prior to the date of the encounter using an age-appropriate standardized depression screening tool AND if positive a follow-up plan is documented on the date of or up to two days after the date of the qualifying encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS22v14','Preventive Care and Screening Screening for High Blood Pressure and Follow Up Documented',20,1,'Percentage of patient visits for patients aged 18 years and older seen during the measurement period who were screened for high blood pressure AND a recommended follow-up plan is documented, as indicated, if blood pressure is elevated or hypertensive');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS50v14','Closing the Referral Loop: Receipt of Specialist Report',30,0,'Percentage of patients with referrals, regardless of age, for which the referring clinician receives a report from the clinician to whom the patient was referred');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS56v14','Functional Status Assessment for Total Hip Replacement',40,0,'Percentage of patients 19 years of age and older who received an elective primary total hip arthroplasty (THA) and completed a functional status assessment within 90 days prior to the surgery and in the 300 - 425 days after the surgery');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS68v15','Documentation of Current Medications in the Medical Record',50,1,'Percentage of visits for which the eligible clinician attests to documenting a list of current medications using all immediate resources available on the date of the encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS69v14','Preventive Care and Screening Body Mass Index (BMI) Screening and Follow Up Plan',60,1,'Percentage of patients aged 18 years and older with a BMI documented during the current encounter or during the measurement period AND who had a follow-up plan documented if BMI was outside of normal parameters');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS71v15','Anticoagulation Therapy for Atrial Fibrillation/Flutter',70,0,'Ischemic stroke patients with atrial fibrillation/flutter who are prescribed or continuing to take anticoagulation therapy at hospital discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS72v14','Antithrombotic Therapy by End of Hospital Day 2',80,0,'Ischemic stroke patients administered antithrombotic therapy by the end of hospital day 2');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS74v15','Primary Caries Prevention Intervention as Offered by Dentists',90,0,'Percentage of children, 1-20 years of age, who received two fluoride varnish applications during the measurement period as determined by a dentist');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS75v14','Children Who Have Dental Decay or Cavities',100,0,'Percentage of children, 1-20 years of age at the start of the measurement period, who have had dental decay or cavities during the measurement period as determined by a dentist');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS90v15','Functional Status Assessments for Heart Failure',110,0,'Percentage of patients 18 years of age and older with heart failure who completed initial and follow-up patient-reported functional status assessments');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS104v14','Discharged on Antithrombotic Therapy',120,0,'Ischemic stroke patients prescribed or continuing to take antithrombotic therapy at hospital discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS108v14','Venous Thromboembolism Prophylaxis',130,0,'This measure assesses the number of patients who received Venous Thromboembolism (VTE) prophylaxis or have documentation why no VTE prophylaxis was given between the day of arrival to the day after hospital admission or surgery end date for surgeries that end the day after hospital admission');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS117v14','Childhood Immunization Status',140,0,'Percentage of children 2 years of age who had four diphtheria, tetanus and acellular pertussis (DTaP); three polio (IPV), one measles, mumps and rubella (MMR); three or four H influenza type B (HiB); three hepatitis B (HepB); one chicken pox (VZV); four pneumococcal conjugate (PCV); one hepatitis A (HepA); two or three rotavirus (RV); and two influenza (flu) vaccines by their second birthday');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS122v14','Diabetes: Glycemic Status Assessment Greater Than 9%',150,1,'Percentage of patients 18-75 years of age with diabetes who had a glycemic status assessment (hemoglobin A1c [HbA1c] or glucose management indicator [GMI]) &gt; 9.0% during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS124v14','Cervical Cancer Screening',160,1,'Percentage of women 21-64 years of age who were screened for cervical cancer using either of the following criteria: - Women age 21-64 who had cervical cytology performed within the last 3 years - Women age 30-64 who had cervical human papillomavirus (HPV) testing performed within the last 5 years');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS125v14','Breast Cancer Screening',170,1,'Percentage of women 40-74 years of age who had a mammogram to screen for breast cancer in the 27 months prior to the end of the Measurement Period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS128v14','Antidepressant Medication Management',180,0,'Percentage of patients 18 years of age and older who were treated with antidepressant medication, had a diagnosis of major depression, and who remained on an antidepressant medication treatment. Two rates are reported. a. Percentage of patients who remained on an antidepressant medication for at least 84 days (12 weeks). b. Percentage of patients who remained on an antidepressant medication for at least 180 days (6 months).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS129v15','Prostate Cancer Avoidance of Overuse of Bone Scan for Staging Low Risk Prostate Cancer Patients',190,0,'Percentage of patients, regardless of age, with a diagnosis of prostate cancer at low (or very low) risk of recurrence receiving interstitial prostate brachytherapy, OR external beam radiotherapy to the prostate, OR radical prostatectomy who did not have a bone scan performed at any time since diagnosis of prostate cancer');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS130v14','Colorectal Cancer Screening',200,1,'Percentage of adults 45-75 years of age who had appropriate screening for colorectal cancer');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS131v14','Diabetes: Eye Exam',210,0,'Percentage of patients 18-75 years of age with diabetes and an active diagnosis of retinopathy in any part of the measurement period who had a retinal or dilated eye exam by an eye care professional during the measurement period or diabetics with no diagnosis of retinopathy in any part of the measurement period who had a retinal or dilated eye exam by an eye care professional during the measurement period or in the 12 months prior to the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS133v14','Cataracts: 20/40 or Better Visual Acuity within 90 Days Following Cataract Surgery',220,0,'Percentage of cataract surgeries for patients aged 18 and older with a diagnosis of uncomplicated cataract and no significant ocular conditions impacting the visual outcome of surgery and had best-corrected visual acuity of 20/40 or better (distance or near) achieved in the operative eye within 90 days following the cataract surgery');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS135v14','Heart Failure (HF): Angiotensin-Converting Enzyme (ACE) Inhibitor or Angiotensin Receptor Blocker (ARB) or Angiotensin Receptor-Neprilysin Inhibitor (ARNI) Therapy for Left Ventricular Systolic Dysfunction (LVSD)',230,0,'Percentage of patients aged 18 years and older with a diagnosis of heart failure (HF) with a current or prior left ventricular ejection fraction (LVEF) &lt;=40% who were prescribed or already taking ACE inhibitor or ARB or ARNI therapy during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS136v15','Follow-Up Care for Children Prescribed ADHD Medication',240,0,'Percentage of children 6-12 years of age and newly prescribed a medication for attention-deficit/hyperactivity disorder (ADHD) who had appropriate follow-up care. Two rates are reported. a. Percentage of children who had one follow-up visit with a practitioner with prescribing authority during the 30-Day Initiation Phase. b. Percentage of children who remained on ADHD medication for at least 210 treatment days and who, in addition to the visit in the Initiation Phase, had at least two additional follow-up visits with a practitioner within 270 days (9 months) after the Initiation Phase ended.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS137v14','Initiation and Engagement of Substance Use Disorder Treatment',250,0,'Percentage of patients 13 years of age and older with a new substance use disorder (SUD) episode who received the following (Two rates are reported): a. Percentage of patients who initiated treatment, including either an intervention or medication for the treatment of SUD, within 14 days of the new SUD episode. b. Percentage of patients who engaged in ongoing treatment, including two additional interventions or medication treatment events for SUD, or one long-acting medication event for the treatment of SUD, within 34 days of the initiation.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS138v14','Preventive Care and Screening: Tobacco Use: Screening and Cessation Intervention',260,1,'Percentage of patients aged 12 years and older who were screened for tobacco use one or more times during the measurement period AND who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period if identified as a tobacco user. Three rates are reported: a. Percentage of patients aged 12 years and older who were screened for tobacco use one or more times during the measurement period b. Percentage of patients aged 12 years and older who were identified as a tobacco user during the measurement period who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period c. Percentage of patients aged 12 years and older who were screened for tobacco use one or more times during the measurement period AND who received tobacco cessation intervention during the measurement period or in the six months prior to the measurement period if identified as a tobacco user');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS139v14','Falls: Screening for Future Fall Risk',270,0,'Percentage of patients 65 years of age and older who were screened for future fall risk during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS142v14','Diabetic Retinopathy: Communication with the Physician Managing Ongoing Diabetes Care',280,0,'Percentage of patients aged 18 years and older with a diagnosis of diabetic retinopathy who had a dilated macular or fundus exam performed with documented communication to the physician who manages the ongoing care of the patient with diabetes mellitus regarding the findings of the macular or fundus exam at least once during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS143v14','Primary Open-Angle Glaucoma (POAG): Optic Nerve Evaluation',290,0,'Percentage of patients aged 18 years and older with a diagnosis of primary open-angle glaucoma (POAG) who have an optic nerve head evaluation during one or more visits during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS144v14','Heart Failure (HF): Beta-Blocker Therapy for Left Ventricular Systolic Dysfunction (LVSD)',300,0,'Percentage of patients aged 18 years and older with a diagnosis of heart failure (HF) with a current or prior left ventricular ejection fraction (LVEF) &lt;= 40% who were prescribed or already taking beta-blocker therapy during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS145v14','Coronary Artery Disease (CAD): Beta-Blocker Therapy-Prior Myocardial Infarction (MI) or Left Ventricular Systolic Dysfunction (LVEF less than or equal to 40%)',310,0,'Percentage of patients aged 18 years and older with a diagnosis of coronary artery disease seen within a 12-month period who also have a prior MI or a current or prior left ventricular ejection fraction (LVEF) &lt;=40% who were prescribed beta-blocker therapy');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS146v14','Appropriate Testing for Pharyngitis',320,0,'The percentage of episodes for patients 3 years and older with a diagnosis of pharyngitis that resulted in an antibiotic order on or three days after the episode date and a group A streptococcus (strep) test in the seven-day period from three days prior to the episode date through three days after the episode date');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS149v14','Dementia: Cognitive Assessment',330,0,'Percentage of patients, regardless of age, with a diagnosis of dementia for whom an assessment of cognition is performed and the results reviewed at least once within the 12 months preceding a dementia encounter during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS153v14','Chlamydia Screening in Women',340,0,'Percentage of women 16-24 years of age who were identified as sexually active at any time during the measurement period and who had at least one test for chlamydia during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS154v14','Appropriate Treatment for Upper Respiratory Infection (URI)',350,0,'Percentage of episodes for patients 3 months of age and older with a diagnosis of upper respiratory infection (URI) that did not result in an antibiotic order');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS155v14','Weight Assessment and Counseling for Nutrition and Physical Activity for Children/Adolescents',360,0,'Percentage of patients 3-17 years of age who had an outpatient visit with a primary care physician (PCP) or obstetrician/gynecologist (OB/GYN) and who had evidence of the following during the measurement period. - Percentage of patients with height, weight, and body mass index (BMI) percentile documentation - Percentage of patients with counseling for nutrition - Percentage of patients with counseling for physical activity');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS156v14','Use of High-Risk Medications in Older Adults',370,0,'Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class. Three rates are reported. 1. Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class. 2. Percentage of patients 65 years of age and older who were ordered at least two high-risk medications from the same drug class, except for appropriate diagnoses. 3. Total rate (the sum of the two numerators divided by the denominator, deduplicating for patients in both numerators).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS157v14','Oncology: Medical and Radiation - Pain Intensity Quantified',380,0,'Percentage of patient visits, regardless of patient age, with a diagnosis of cancer currently receiving chemotherapy or radiation therapy in which pain intensity is quantified');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS159v14','Depression Remission at Twelve Months',390,0,'The percentage of adolescent patients 12 to 17 years of age and adult patients 18 years of age or older with major depression or dysthymia who reached remission 12 months (+/- 60 days) after an index event');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS165v14','Controlling High Blood Pressure',400,1,'Percentage of patients 18-85 years of age who had a diagnosis of essential hypertension starting before and continuing into, or starting during the first six months of the measurement period, and whose most recent blood pressure was adequately controlled (&lt;140/90 mmHg) during the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS177v14','Child and Adolescent Major Depressive Disorder (MDD): Suicide Risk Assessment',410,0,'Percentage of patient visits for those patients aged 6 through 16 at the start of the measurement period with a diagnosis of major depressive disorder (MDD) with an assessment for suicide risk');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS190v14','Intensive Care Unit Venous Thromboembolism Prophylaxis',420,0,'This measure assesses the number of patients who received Venous Thromboembolism (VTE) prophylaxis on the day of or the day after the initial admission (or transfer) to the Intensive Care Unit (ICU) or on the day of or the day after surgery end date for surgeries that end the day after ICU admission (or transfer) or have documentation why no VTE prophylaxis was given between the day of arrival and the day after ICU admission (for patients directly admitted as inpatients to the ICU) or on the day of or the day after surgery end date for surgeries that end the day after ICU admission (or transfer)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS314v3','HIV Viral Suppression',430,0,'Percentage of patients, regardless of age, diagnosed with Human Immunodeficiency Virus (HIV) prior to or during the first 90 days of the measurement period, with an eligible encounter in the first 240 days of the measurement period, whose last HIV viral load test result was less than 200 copies/mL during the measurement period.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS334v7','Cesarean Birth',440,0,'Nulliparous patients with a term, singleton baby in a vertex position delivered by cesarean birth');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS347v9','Statin Therapy for the Prevention and Treatment of Cardiovascular Disease',450,0,'Percentage of the following patients - all considered at high risk of cardiovascular events - who were prescribed or were on statin therapy during the measurement period: - All patients who were previously diagnosed with or currently have a diagnosis of clinical atherosclerotic cardiovascular disease (ASCVD), including an ASCVD procedure; OR - Patients aged 20 to 75 years who have ever had a low-density lipoprotein cholesterol (LDL-C) level &gt;= 190 mg/dL or were previously diagnosed with or currently have an active diagnosis of familial hypercholesterolemia; OR - Patients aged 40-75 years with a diagnosis of diabetes; OR - Patients aged 40 to 75 with a 10-year ASCVD risk score of &gt;= 20 percent.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS349v8','HIV Screening',460,0,'Percentage of patients aged 15-65 at the start of the measurement period who were between 15-65 years old when tested for human immunodeficiency virus (HIV)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS506v8','Safe Use of Opioids - Concurrent Prescribing',470,0,'Proportion of inpatient hospitalizations for patients 18 years of age and older prescribed, or continued on, two or more opioids or an opioid and benzodiazepine concurrently at discharge');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS529v6','Core Clinical Data Elements for the Hybrid Hospital-Wide Readmission Measure with Claims and Electronic Health Record Data - HWR',480,0,'This logic is intended to extract electronic clinical data. This is not an electronic clinical quality measure and this logic will not produce measure results. Instead, it will produce a file containing the data that CMS will link with administrative claims to risk adjust the Hybrid HWR outcome measure. It is designed to extract the first resulted set of vital signs and basic laboratory results obtained from hospitalizations for adult Medicare Fee-For-Service (FFS) and Medicare Advantage (MA) patients admitted to acute care hospitals.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS645v9','Bone Density Evaluation for Patients with Prostate Cancer and Receiving Androgen Deprivation Therapy',490,0,'Percentage of patients determined as having prostate cancer who are currently starting or undergoing androgen deprivation therapy (ADT), for an anticipated period of 12 months or greater and who receive an initial bone density evaluation. The bone density evaluation must be prior to the start of ADT or within 3 months of the start of ADT.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS646v6','Intravesical Bacillus-Calmette-Guerin for Non-Muscle Invasive Bladder Cancer',500,0,'Percentage of patients initially diagnosed with non-muscle invasive bladder cancer and who received intravesical Bacillus-Calmette-Guerin (BCG) within 6 months of bladder cancer staging');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS771v7','Urinary Symptom Score Change 6-12 Months After Diagnosis of Benign Prostatic Hyperplasia',510,0,'Percentage of patients with an office visit within the measurement period and with a new diagnosis of clinically significant Benign Prostatic Hyperplasia who have International Prostate Symptoms Score (IPSS) or American Urological Association (AUA) Symptom Index (SI) documented at time of diagnosis and again 6-12 months later with an improvement of 3 points');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS816v5','Hospital Harm - Severe Hypoglycemia',520,0,'The measure assesses the number of inpatient hospitalizations for patients age 18 and older who were administered at least one hypoglycemic medication during the encounter and who suffer the harm of a severe hypoglycemic event during the encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS819v4','Hospital Harm - Opioid-Related Adverse Events',530,0,'This measure assesses the number of inpatient hospitalizations for patients age 18 and older who have been administered an opioid medication outside of the operating room and are subsequently administered a non-enteral opioid antagonist outside of the operating room within 12 hours, an indication of an opioid-related adverse event');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS826v3','Hospital Harm - Pressure Injury',540,0,'The measure assesses the number of inpatient hospitalizations for patients aged 18 and older who suffer the harm of developing a new stage 2, stage 3, stage 4, deep tissue, or unstageable pressure injury');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS832v3','Hospital Harm - Acute Kidney Injury',550,0,'The measure assesses the number of inpatient hospitalizations for patients age 18 and older who have an acute kidney injury (AKI) stage 2 or greater that occurred during the encounter. AKI stage 2 or greater is defined as a substantial increase in serum creatinine value, or by the initiation of kidney dialysis (continuous renal replacement therapy (CRRT), hemodialysis, or peritoneal dialysis).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS844v6','Core Clinical Data Elements for the Hybrid Hospital-Wide All-Condition All-Procedure Risk-Standardized Mortality Measure - HWM',560,0,'This logic is intended to extract electronic clinical data. This is not an electronic clinical quality measure and this logic will not produce measure results. Instead, it will produce a file containing the data that CMS will link with administrative claims to risk adjust the Hybrid HWM outcome measure. It is designed to extract the first resulted set of vital signs and basic laboratory results obtained from hospitalizations for adult Medicare Fee-For-Service (FFS) and Medicare Advantage (MA) patients admitted to acute care hospitals.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS871v5','Hospital Harm - Severe Hyperglycemia',570,0,'This measure assesses the number of inpatient hospital days for patients age 18 and older with a hyperglycemic event (harm) per the total qualifying inpatient hospital days for that encounter');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS951v4','Kidney Health Evaluation',580,0,'Percentage of patients aged 18-85 years with a diagnosis of diabetes who received a kidney health evaluation defined by an Estimated Glomerular Filtration Rate (eGFR) AND Urine Albumin-Creatinine Ratio (uACR) within the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS986v5','Malnutrition Care Score',590,0,'This measure assesses the percentage of eligible encounters of adults aged 18 years and older at the start of the eligible encounter during the measurement period, with a length of stay equal to or greater than 24 hours, who received optimal malnutrition care where care performed was appropriate to the patient&#39;s level of malnutrition risk and severity. Malnutrition care best practices recommend that for each eligible encounter, adult inpatients are (1) screened for malnutrition risk or for a dietitian referral order to be placed, (2) assessed by a registered dietitian (RD) or registered dietitian nutritionist (RDN) to confirm findings of malnutrition risk, and if identified with a &quot;moderate&quot; or &quot;severe&quot; malnutrition status in the current performed nutrition assessment, (3) receive a &quot;moderate&quot; or &quot;severe&quot; malnutrition diagnosis by a physician or eligible clinician as defined by the Centers for Medicare &amp; Medicaid Services (CMS), and (4) have a current nutrition care plan performed by an RD/RDN.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS996v6','Appropriate Treatment for ST-Segment Elevation Myocardial Infarction (STEMI) Patients in the Emergency Department (ED)',600,0,'Percentage of emergency department (ED) encounters for patients 18 years and older with a diagnosis of ST-segment elevation myocardial infarction (STEMI) that received appropriate treatment, defined as fibrinolytic therapy within 30 minutes of ED arrival, percutaneous coronary intervention (PCI) within 90 minutes of ED arrival, or discharge to an acute care facility within 45 minutes of ED arrival');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS1017v2','Hospital Harm - Falls with Injury',610,0,'This ratio measure assesses the number of inpatient hospitalizations where at least one fall with a major or moderate injury occurs among the total qualifying inpatient hospital days for patients age 18 years and older');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS1028v4','Severe Obstetric Complications',620,0,'Patients with severe obstetric complications that occur during the inpatient delivery hospitalization');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS1056v3','Excessive Radiation Dose or Inadequate Image Quality for Diagnostic Computed Tomography (CT) in Adults (Clinician Level)',630,1,'This measure provides a standardized method for monitoring the performance of diagnostic CT to discourage unnecessarily high radiation doses, a risk factor for cancer, while preserving image quality. It is expressed as a percentage of patients with CT exams that are out-of-range based on having either excessive radiation dose or inadequate image quality relative to evidence-based thresholds based on the clinical indication for the exam. All diagnostic CT exams of specified anatomic sites performed in inpatient, outpatient and ambulatory care settings are eligible. This measure is not telehealth eligible. This eCQM requires the use of additional software to access primary data elements stored within radiology electronic health records and translate them into data elements that can be ingested by this eCQM. Additional details are included in the Guidance field.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS1074v3','Excessive Radiation Dose or Inadequate Image Quality for Diagnostic Computed Tomography (CT) in Adults (Facility IQR)',640,0,'This measure provides a standardized method for monitoring the performance of diagnostic CT to discourage unnecessarily high radiation doses, a risk factor for cancer, while preserving image quality. This measure is expressed as a percentage of CT exams that are out-of-range based on having either excessive radiation dose or inadequate image quality relative to evidence-based thresholds based on the clinical indication for the exam. All diagnostic CT exams of specified anatomic sites performed in hospital inpatient care settings are eligible. This eCQM requires the use of additional software to access primary data elements stored within radiology electronic health records and translate them into data elements that can be ingested by this eCQM. Additional details are included in the Guidance field.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS1154v1','Screening for Abnormal Glucose Metabolism in Patients at Risk of Developing Diabetes',650,0,'Percentage of adult patients with risk factors for type 2 diabetes who are due for glycemic screening for whom the screening process was completed during the measurement period.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS1157v2','HIV Annual Retention in Care',660,0,'Percentage of patients, regardless of age, with a diagnosis of Human Immunodeficiency Virus (HIV) during the first 240 days of the measurement period or before the measurement period who had at least two eligible encounters or at least one eligible encounter and one HIV viral load test that were at least 90 days apart within the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS1173v1','Diagnostic Delay of Venous Thromboembolism in Primary Care',670,0,'Percentage of episodes for patients 18 years of age and older with documented Venous Thromboembolism (VTE) symptoms in the primary care setting and who had a diagnosis of VTE that occurs &gt; 24 hours and within 30 days following the index primary care visit where symptoms for the VTE were first present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS1188v3','Sexually Transmitted Infection (STI) Testing for People with HIV',680,0,'Percentage of patients 13 years of age and older with a diagnosis of Human Immunodeficiency Virus (HIV) who had tests for syphilis, gonorrhea, and chlamydia performed within the measurement period');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS1206v3','Excessive Radiation Dose or Inadequate Image Quality for Diagnostic Computed Tomography (CT) in Adults (Facility OQR)',690,0,'This measure is an episode of care measure that provides a standardized method for monitoring the performance of diagnostic CT to discourage unnecessarily high radiation doses, a risk factor for cancer, while preserving image quality. This measure is expressed as a percentage of CT exams that are out-of-range based on having either excessive radiation dose or inadequate image quality relative to evidence-based thresholds based on the clinical indication for the exam. All diagnostic CT exams of specified anatomic sites performed in hospital non-inpatient care settings (including emergency settings) are eligible. This eCQM requires the use of additional software to access primary data elements stored within radiology electronic health records and translate them into data elements that can be ingested by this eCQM. Additional details are included in the Guidance field.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ('ecqm_2026_reporting','CMS1218v2','Hospital Harm - Postoperative Respiratory Failure',700,0,'This measure assesses the number of elective inpatient hospitalizations for patients aged 18 years and older without an obstetrical condition who have a procedure resulting in postoperative respiratory failure (PRF)');
#EndIf

-- AuthCodeRepository's revokeAuthCode / isAuthCodeRevoked methods now
-- persist their state here so an already-exchanged authorization code
-- is rejected on subsequent presentation. Rows age out naturally with
-- the authorization-code TTL -- a stale row keeps rejecting a code
-- that would already be rejected on expiry, so cleanup is optional.
#IfNotTable oauth_auth_code_revocations
CREATE TABLE `oauth_auth_code_revocations` (
    `code_id` varchar(80) NOT NULL,
    `client_id` varchar(80) DEFAULT NULL COMMENT 'client the code was issued to, kept for operator traceability',
    `revoked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`code_id`),
    KEY `idx_revoked_at` (`revoked_at`)
) ENGINE=InnoDB;
#EndIf

-- FHIR Task (#14521, D9 of #13266): its own table, as QuestionnaireResponse has.
#IfNotTable task
CREATE TABLE `task` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) DEFAULT NULL,
  `status` varchar(31) NOT NULL DEFAULT 'requested' COMMENT 'FHIR Task.status code',
  `status_reason` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.statusReason, as text',
  `business_status` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.businessStatus, as text',
  `intent` varchar(31) NOT NULL DEFAULT 'order' COMMENT 'FHIR Task.intent code',
  `priority` varchar(31) DEFAULT NULL COMMENT 'FHIR Task.priority code',
  `code` varchar(63) DEFAULT NULL COMMENT 'FHIR Task.code, from http://hl7.org/fhir/CodeSystem/task-code',
  `description` text,
  `patient_id` bigint(20) DEFAULT NULL COMMENT 'FHIR Task.for: patient_data.pid',
  `focus_type` varchar(63) DEFAULT NULL COMMENT 'FHIR Task.focus: resource type',
  `focus_uuid` binary(16) DEFAULT NULL COMMENT 'FHIR Task.focus: resource id',
  `owner_type` varchar(63) DEFAULT NULL COMMENT 'FHIR Task.owner: resource type',
  `owner_uuid` binary(16) DEFAULT NULL COMMENT 'FHIR Task.owner: resource id',
  `requester_type` varchar(63) DEFAULT NULL COMMENT 'FHIR Task.requester: resource type',
  `requester_uuid` binary(16) DEFAULT NULL COMMENT 'FHIR Task.requester: resource id',
  `part_of_uuid` binary(16) DEFAULT NULL COMMENT 'FHIR Task.partOf: the parent task.uuid',
  `authored_on` datetime DEFAULT NULL,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` bigint(20) DEFAULT NULL COMMENT 'users.id',
  `updated_by` bigint(20) DEFAULT NULL COMMENT 'users.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `patient_id` (`patient_id`),
  KEY `status` (`status`),
  KEY `focus_uuid` (`focus_uuid`),
  KEY `owner_uuid` (`owner_uuid`),
  KEY `part_of_uuid` (`part_of_uuid`)
) ENGINE=InnoDB;
#EndIf

-- FHIR Task writes (part 2 of #14521): meta.versionId, raised on each update, for If-Match.
#IfMissingColumn task version
ALTER TABLE `task` ADD COLUMN `version` int(11) NOT NULL DEFAULT 1 COMMENT 'FHIR meta.versionId: 1 on create, raised by each update (If-Match)';
#EndIf

#IfNotTable module_lbf_statement_forms
CREATE TABLE `module_lbf_statement_forms` (
  `form_id` varchar(31) NOT NULL,
  `paragraph_field_id` varchar(31) NOT NULL,
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable module_lbf_statement_rules
CREATE TABLE `module_lbf_statement_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` varchar(31) NOT NULL,
  `source_field_id` varchar(31) NOT NULL,
  `source_field_id_2` varchar(31) DEFAULT NULL,
  `op` varchar(32) NOT NULL,
  `min_value` decimal(12,4) DEFAULT NULL,
  `max_value` decimal(12,4) DEFAULT NULL,
  `min_inclusive` tinyint(1) NOT NULL DEFAULT 1,
  `max_inclusive` tinyint(1) NOT NULL DEFAULT 1,
  `match_token` varchar(255) DEFAULT NULL,
  `statement_text` text,
  `seq` int(11) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable module_lbf_statement_runs
CREATE TABLE `module_lbf_statement_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` varchar(31) NOT NULL,
  `pid` bigint(20) NOT NULL,
  `instance_form_id` int(11) NOT NULL,
  `user` varchar(255) DEFAULT NULL,
  `mode` varchar(16) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pid_instance` (`pid`,`instance_form_id`)
) ENGINE=InnoDB;
#EndIf
