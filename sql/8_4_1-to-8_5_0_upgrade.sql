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
