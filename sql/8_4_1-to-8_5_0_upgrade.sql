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

#IfNotTable content_assignments
CREATE TABLE `content_assignments` (
  `id` bigint(21) UNSIGNED NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(63) NOT NULL DEFAULT 'questionnaire' COMMENT 'questionnaire today; document_template and others later',
  `resource_id` bigint(21) UNSIGNED DEFAULT NULL COMMENT 'fk into the resource_type table, e.g. questionnaire_repository.id. NULL with a category set means every resource in that category',
  `surface` varchar(31) NOT NULL COMMENT 'dashboard, encounter, portal, smart',
  `category` varchar(64) DEFAULT NULL COMMENT 'with resource_id set, unused; with resource_id NULL, assigns every resource whose own category matches',
  `facility` int(11) DEFAULT NULL COMMENT 'fk to facility.id; NULL means any',
  `provider` int(11) UNSIGNED DEFAULT NULL COMMENT 'fk to users.id; NULL means any',
  `visit_category` varchar(64) DEFAULT NULL COMMENT 'fk to list_options.option_id WHERE list_id=visit_category; NULL means any',
  `client_id` varchar(255) DEFAULT NULL COMMENT 'oauth client for surface=smart; NULL means any',
  `acl_section` varchar(64) DEFAULT NULL COMMENT 'ACL section required; NULL falls back to the surface default',
  `acl_level` varchar(31) DEFAULT NULL COMMENT 'ACL level required; NULL falls back to the surface default',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `seq` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'fk to users.id',
  `updated_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'fk to users.id',
  PRIMARY KEY (`id`),
  KEY `lookup` (`resource_type`,`surface`,`active`),
  KEY `resource` (`resource_type`,`resource_id`)
) ENGINE=InnoDB;
#EndIf

-- Backfill from current state so an upgraded site sees exactly what it saw before.
-- Every active questionnaire was on the dashboard; every enabled registry-registered one
-- was available in every encounter. Both become unscoped assignment rows.
--
-- Idempotent per questionnaire rather than per surface: a NOT EXISTS on the individual
-- row means a re-run completes a partial backfill instead of skipping every remaining
-- questionnaire as soon as one assignment is present. DISTINCT because a questionnaire
-- can hold more than one matching registry row.
INSERT INTO `content_assignments` (`resource_type`, `resource_id`, `surface`, `active`, `seq`)
SELECT DISTINCT 'questionnaire', qr.`id`, 'dashboard', 1, 0
  FROM `questionnaire_repository` qr
 WHERE qr.`active` = 1
   AND NOT EXISTS (
       SELECT 1 FROM `content_assignments` ca
        WHERE ca.`resource_type` = 'questionnaire'
          AND ca.`resource_id` = qr.`id`
          AND ca.`surface` = 'dashboard'
   );

-- registry.state = 1 matches QuestionnaireService::fetchEncounterQuestionnaireForm, so a
-- form that was registered and later disabled does not get an active assignment.
INSERT INTO `content_assignments` (`resource_type`, `resource_id`, `surface`, `active`, `seq`)
SELECT DISTINCT 'questionnaire', r.`form_foreign_id`, 'encounter', 1, 0
  FROM `registry` r
 WHERE r.`directory` = 'questionnaire_assessments'
   AND r.`form_foreign_id` > 0
   AND r.`state` = 1
   AND NOT EXISTS (
       SELECT 1 FROM `content_assignments` ca
        WHERE ca.`resource_type` = 'questionnaire'
          AND ca.`resource_id` = r.`form_foreign_id`
          AND ca.`surface` = 'encounter'
   );
