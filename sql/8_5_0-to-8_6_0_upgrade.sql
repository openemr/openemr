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

-- Lookups by encounter alone, and questionnaire answers by response_id, read the whole table without these (#14863).
#IfNotIndex form_encounter encounter
CREATE INDEX `encounter` ON `form_encounter` (`encounter`);
#EndIf

#IfNotIndex issue_encounter encounter
CREATE INDEX `encounter` ON `issue_encounter` (`encounter`);
#EndIf

#IfNotIndex issue_encounter list_id
CREATE INDEX `list_id` ON `issue_encounter` (`list_id`);
#EndIf

#IfNotIndex form_questionnaire_assessments response_id
CREATE INDEX `response_id` ON `form_questionnaire_assessments` (`response_id`(64));
#EndIf

-- No desire to become pregnant was seeded with the code for Ambivalent (454391000124108).
-- 454401000124105 is No desire to become pregnant (finding).
#IfRow3D list_options list_id pregnancy_intent option_id no_desire codes SNOMED-CT:454391000124108
UPDATE `list_options` SET `codes` = 'SNOMED-CT:454401000124105' WHERE `list_id` = 'pregnancy_intent' AND `option_id` = 'no_desire';
#EndIf

-- FHIR ServiceRequest referrals (D8 of #13266): a uuid for each transaction, so the Transactions screen's referrals have a FHIR id.
#IfMissingColumn transactions uuid
ALTER TABLE `transactions` ADD `uuid` binary(16) DEFAULT NULL AFTER `id`, ADD UNIQUE KEY `uuid` (`uuid`);
#EndIf

-- FHIR Task inputs and outputs (part 3 of #14521): the system Task.code is from, what the work needs, and what it produced.
-- Codes written before the system was kept were all task-code's, except the questionnaire task's.
#IfMissingColumn task code_system
ALTER TABLE `task` MODIFY COLUMN `code` varchar(63) DEFAULT NULL COMMENT 'FHIR Task.code: the code',
  ADD COLUMN `code_system` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.code: its system, such as http://hl7.org/fhir/CodeSystem/task-code' AFTER `code`;
UPDATE `task` SET `code_system` = 'http://hl7.org/fhir/uv/sdc/CodeSystem/temp' WHERE `code` = 'complete-questionnaire';
UPDATE `task` SET `code_system` = 'http://hl7.org/fhir/CodeSystem/task-code' WHERE `code` IS NOT NULL AND `code_system` IS NULL;
#EndIf

#IfNotTable task_input
CREATE TABLE `task_input` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) NOT NULL COMMENT 'task.id',
  `seq` int(11) NOT NULL DEFAULT 0 COMMENT 'position in Task.input',
  `type_system` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.input.type: coding system',
  `type_code` varchar(63) DEFAULT NULL COMMENT 'FHIR Task.input.type: coding code',
  `type_display` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.input.type: coding display',
  `type_text` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.input.type: text',
  `value_element` varchar(31) NOT NULL COMMENT 'which value[x] it is: valueReference, valueCanonical, valueUrl, valueString, valueMarkdown or valueCodeableConcept',
  `value_type` varchar(63) DEFAULT NULL COMMENT 'valueReference: resource type',
  `value_uuid` binary(16) DEFAULT NULL COMMENT 'valueReference: resource id',
  `value_system` varchar(255) DEFAULT NULL COMMENT 'valueCodeableConcept: coding system',
  `value_code` varchar(63) DEFAULT NULL COMMENT 'valueCodeableConcept: coding code',
  `value_display` varchar(255) DEFAULT NULL COMMENT 'valueReference: its display; valueCodeableConcept: the display of its coding',
  `value_text` text COMMENT 'the canonical, url, string or markdown; valueCodeableConcept: its text',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`, `seq`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable task_output
CREATE TABLE `task_output` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) NOT NULL COMMENT 'task.id',
  `seq` int(11) NOT NULL DEFAULT 0 COMMENT 'position in Task.output',
  `type_system` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.output.type: coding system',
  `type_code` varchar(63) DEFAULT NULL COMMENT 'FHIR Task.output.type: coding code',
  `type_display` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.output.type: coding display',
  `type_text` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.output.type: text',
  `value_element` varchar(31) NOT NULL COMMENT 'which value[x] it is: valueReference, valueCanonical, valueUrl, valueString, valueMarkdown or valueCodeableConcept',
  `value_type` varchar(63) DEFAULT NULL COMMENT 'valueReference: resource type',
  `value_uuid` binary(16) DEFAULT NULL COMMENT 'valueReference: resource id',
  `value_system` varchar(255) DEFAULT NULL COMMENT 'valueCodeableConcept: coding system',
  `value_code` varchar(63) DEFAULT NULL COMMENT 'valueCodeableConcept: coding code',
  `value_display` varchar(255) DEFAULT NULL COMMENT 'valueReference: its display; valueCodeableConcept: the display of its coding',
  `value_text` text COMMENT 'the canonical, url, string or markdown; valueCodeableConcept: its text',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`, `seq`)
) ENGINE=InnoDB;
#EndIf

-- Appointments by patient compare pc_pid with text. A pc_pid of digits stored another way (05 from a
-- REST URL, or with spaces) is written as the plain number, which the text comparison matches as the
-- numeric one did. It runs on every upgrade, since a site may have added its own pc_pid index; once the
-- rows are plain it changes nothing.
#IfTable openemr_postcalendar_events
UPDATE `openemr_postcalendar_events` SET `pc_pid` = CAST(TRIM(`pc_pid`) AS UNSIGNED) WHERE TRIM(`pc_pid`) REGEXP '^[0-9]+$' AND `pc_pid` NOT REGEXP '^(0|[1-9][0-9]*)$';
#EndIf

-- Appointments by patient filter pc_pid (a varchar, compared with text); the daily summary
-- report's payments are filtered by billing.date. Both read the whole table without an index.
#IfNotIndex openemr_postcalendar_events pc_pid
CREATE INDEX `pc_pid` ON `openemr_postcalendar_events` (`pc_pid`);
#EndIf

#IfNotIndex billing date
CREATE INDEX `date` ON `billing` (`date`);
#EndIf

-- Patient review queue: patient-submitted data (portal profile changes, online payments,
-- documents, and patient-app FHIR writes) held for staff review. One row per submission, a single status column, an
-- append-only history, and sensitive data kept apart from the payload. Replaces the
-- onsite_portal_activity workflow, which is migrated onto these tables separately.
#IfNotTable patient_review_request
CREATE TABLE `patient_review_request` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) NOT NULL,
  `pid` bigint(20) NOT NULL COMMENT 'pid from patient_data table',
  `type` varchar(32) NOT NULL COMMENT 'profile, payment, document, invoice, fhir-write',
  `resource_type` varchar(64) DEFAULT NULL COMMENT 'FHIR resource type when type is fhir-write, e.g. Condition; NULL otherwise',
  `source` varchar(16) NOT NULL DEFAULT 'portal' COMMENT 'portal or api',
  `client_id` varchar(80) DEFAULT NULL COMMENT 'oauth_clients.client_id when source is api',
  `status` varchar(24) NOT NULL DEFAULT 'pending' COMMENT 'pending, awaiting_payment, approved, denied, cancelled, completed',
  `summary` varchar(255) NOT NULL DEFAULT '' COMMENT 'Staff-facing description of the request',
  `payload` longtext COMMENT 'The submitted data',
  `payload_format` varchar(16) NOT NULL DEFAULT 'json' COMMENT 'json; php-serialized or text only for rows migrated from onsite_portal_activity',
  `target_table` varchar(64) DEFAULT NULL COMMENT 'Table the request applies to, when known',
  `target_id` varchar(64) DEFAULT NULL COMMENT 'Row id or uuid in target_table',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `reviewed_by` bigint(20) DEFAULT NULL COMMENT 'users.id of the staff user who decided the request',
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `status_type` (`status`,`type`),
  KEY `pid_type_status` (`pid`,`type`,`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB COMMENT='Patient-submitted data awaiting staff review; one row per submission';
#EndIf

#IfNotTable patient_review_event
CREATE TABLE `patient_review_event` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) NOT NULL COMMENT 'patient_review_request.id',
  `from_status` varchar(24) DEFAULT NULL COMMENT 'NULL for the event that created the request',
  `to_status` varchar(24) NOT NULL,
  `actor_type` varchar(16) NOT NULL COMMENT 'patient, user or system',
  `actor_id` bigint(20) DEFAULT NULL COMMENT 'pid for a patient, users.id for a user',
  `created_at` datetime NOT NULL,
  `note` text,
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`)
) ENGINE=InnoDB COMMENT='Append-only history of patient_review_request state changes';
#EndIf

#IfNotTable patient_review_secret
CREATE TABLE `patient_review_secret` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) NOT NULL COMMENT 'patient_review_request.id',
  `kind` varchar(32) NOT NULL COMMENT 'payment-card',
  `ciphertext` longtext NOT NULL COMMENT 'Encrypted per the database_encryption setting; deleted when the request is no longer open',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_kind` (`request_id`,`kind`)
) ENGINE=InnoDB COMMENT='Sensitive data held only while a patient_review_request is open';
#EndIf

-- Copy the patient portal's review workflow out of onsite_portal_activity into the review queue.
-- legacy_activity_id records which legacy row each request came from, so the copy runs once
-- per row however often the upgrade is run. onsite_portal_activity itself is left in place and
-- dropped in a later release.
#IfMissingColumn patient_review_request legacy_activity_id
ALTER TABLE `patient_review_request`
  ADD COLUMN `legacy_activity_id` bigint(20) DEFAULT NULL COMMENT 'onsite_portal_activity.id this request was migrated from',
  ADD UNIQUE KEY `legacy_activity_id` (`legacy_activity_id`);
#EndIf

#IfPatientReviewMigrationNeeded
#EndIf

-- Phone reminders log with type 'Phone', which the enum never allowed, so MySQL stored ''.
#IfNotColumnType notification_log type enum('SMS','Email','Phone')
ALTER TABLE `notification_log` MODIFY `type` enum('SMS','Email','Phone') NOT NULL;
#EndIf

-- Relabel those rows. An empty type alone is not proof: under a binary collation the faxsms
-- writer's 'EMAIL' is stored as '' too. Email rows always carry 'sender|||subject' in
-- smsgateway_info, and phone rows carry the gateway URL, so skip rows containing '|||'.
-- Unguarded so a rerun still relabels if a previous run stopped after the ALTER.
UPDATE `notification_log` SET `type` = 'Phone' WHERE `type` = '' AND `smsgateway_info` NOT LIKE '%|||%';

-- Relabel the email rows skipped above. Before 8.6.0 the faxsms writer logged 'EMAIL', which a
-- binary collation stores as ''. Only email rows carry 'sender|||subject' in smsgateway_info.
UPDATE `notification_log` SET `type` = 'Email' WHERE `type` = '' AND `smsgateway_info` LIKE '%|||%';

-- An outside organization's or practitioner's OAuth client, limited to that address book entry's Tasks and referrals.
#IfMissingColumn oauth_clients address_book_uuid
ALTER TABLE `oauth_clients` ADD COLUMN `address_book_uuid` binary(16) DEFAULT NULL COMMENT 'users.uuid of the address book entry this client acts for; limits it to that entry''s Tasks and referrals';
#EndIf

-- The background service runner can call a class method, so the phimail service names the receiver
-- directly. library/direct_message_check.inc.php, which held phimail_check(), is deleted.
-- Unguarded so a rerun from before 7.0.1 also clears the require_once that upgrade sets back.
UPDATE `background_services` SET `function` = 'OpenEMR\\Common\\DirectMessaging\\PhiMailReceiver::runBackgroundService', `require_once` = NULL
WHERE `name` = 'phimail' AND (`function` = 'phimail_check' OR `require_once` = '/library/direct_message_check.inc.php');

-- A patient app may replace its own write while it waits for review. The request keeps its id,
-- so the version it replaced is kept on the history row that records the replacement.
#IfMissingColumn patient_review_event payload
ALTER TABLE `patient_review_event`
  ADD COLUMN `payload` longtext COMMENT 'What the request held before its submitter replaced it; NULL for a status change';
#EndIf

-- A Direct message's referral: the one a sent message carried, or the one a reply was matched to (#15137).
#IfMissingColumn direct_message_log referral_id
ALTER TABLE `direct_message_log` ADD COLUMN `referral_id` bigint(20) DEFAULT NULL COMMENT 'transactions.id of the referral a sent message carried, or a received reply was matched to', ADD KEY `referral_id` (`referral_id`);
#EndIf

-- Whether staff chose a C-CDA's referral, so only a chosen one is sent with a Direct message (#15137).
#IfMissingColumn ccda transaction_chosen
ALTER TABLE `ccda` ADD COLUMN `transaction_chosen` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 when staff chose transaction_id for this C-CDA, 0 when it is the patient''s most recent referral';
#EndIf

#IfMissingColumn direct_message_log note_id
ALTER TABLE `direct_message_log` ADD COLUMN `note_id` bigint(20) DEFAULT NULL COMMENT 'pnotes.id of the note a received message was filed as';
#EndIf

-- How a referral's recipient was told about their Task, or why they weren't (#15137).
#IfNotTable referral_notice
CREATE TABLE `referral_notice` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint(20) NOT NULL COMMENT 'transactions.id of the referral',
  `task_uuid` binary(16) NOT NULL COMMENT 'task.uuid of the Task the recipient was told about',
  `outcome` varchar(31) NOT NULL COMMENT 'ReferralNoticeOutcome: how the recipient was told, or why they were not',
  `address` varchar(255) DEFAULT NULL COMMENT 'the Direct or email address the notice went to',
  `msg_id` varchar(127) DEFAULT NULL COMMENT 'direct_message_log.msg_id of a Direct notice, which carries its delivery status',
  `detail` varchar(511) DEFAULT NULL COMMENT 'why a notice could not be sent',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'users.id of who saved the referral',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`)
) ENGINE=InnoDB;
#EndIf

-- The 7.0.4 upgrade added the DEM interpreter_needed field with its own name in edit_options.
-- The new patient duplicate check picks fields whose edit_options contain D, case-insensitively,
-- so it took that field for one of its match fields.
#IfRow3D layout_options form_id DEM field_id interpreter_needed edit_options interpreter_needed
UPDATE `layout_options` SET `edit_options` = '' WHERE `form_id` = 'DEM' AND `field_id` = 'interpreter_needed' AND `edit_options` = 'interpreter_needed';
#EndIf

-- Receive X12 files (835, 277, 999) from X12 partners over SFTP (#15227).
#IfMissingColumn x12_partners x12_sftp_receive_dir_835
ALTER TABLE `x12_partners` ADD COLUMN `x12_sftp_receive_dir_835` varchar(255) DEFAULT NULL COMMENT 'remote folder to fetch 835 remittances from';
#EndIf

#IfMissingColumn x12_partners x12_sftp_receive_dir_277
ALTER TABLE `x12_partners` ADD COLUMN `x12_sftp_receive_dir_277` varchar(255) DEFAULT NULL COMMENT 'remote folder to fetch 277 claim status files from';
#EndIf

#IfMissingColumn x12_partners x12_sftp_receive_dir_999
ALTER TABLE `x12_partners` ADD COLUMN `x12_sftp_receive_dir_999` varchar(255) DEFAULT NULL COMMENT 'remote folder to fetch 999 acknowledgments from';
#EndIf

#IfMissingColumn x12_partners x12_sftp_receive_worked_dir
ALTER TABLE `x12_partners` ADD COLUMN `x12_sftp_receive_worked_dir` varchar(255) DEFAULT NULL COMMENT 'remote folder a fetched file is moved to; empty leaves it in place';
#EndIf

#IfNotTable x12_remote_received
CREATE TABLE `x12_remote_received` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `remote_key` char(64) NOT NULL COMMENT 'sha256 of partner, folder, name, size and modified time: one row per remote file',
  `x12_partner_id` int(11) NOT NULL,
  `remote_dir` varchar(255) NOT NULL,
  `remote_name` varchar(255) NOT NULL,
  `remote_size` bigint(20) NOT NULL,
  `remote_mtime` bigint(20) NOT NULL DEFAULT 0 COMMENT 'modified time on the remote, Unix seconds',
  `file_type` varchar(8) NOT NULL DEFAULT '' COMMENT 'X12FileType: f835, f277 or f997; empty when the file was none of the kinds its folder is for',
  `status` varchar(31) NOT NULL COMMENT 'ReceivedStatus: stored, duplicate, rejected or failed',
  `stored_name` varchar(255) NOT NULL DEFAULT '' COMMENT 'file name it was stored under on the site',
  `message` varchar(255) NOT NULL DEFAULT '' COMMENT 'why it was not stored',
  `received_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `remote_key` (`remote_key`),
  KEY `partner_status` (`x12_partner_id`, `status`)
) ENGINE=InnoDB;
#EndIf

#IfNotRow background_services name X12_SFTP_Receive
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('X12_SFTP_Receive', 'Receive X12 Files from X12 Partners Service', 0, 0, '2021-01-18 11:25:10', 60, 'OpenEMR\\Billing\\X12Receive\\X12ReceiveService::runBackgroundService', NULL, 100);
#EndIf

-- ========================================================================
-- Schema hygiene: PRIMARY KEY on every core openemr table.
--
-- Adds an explicit PRIMARY KEY to the 45 openemr core tables that
-- historically lacked one. Needed for:
--   * Doctrine ORM mapping (every entity requires #[ORM\Id]) — openemr
--     is actively migrating toward Doctrine (AGENTS.md: 'Database:
--     MySQL via Doctrine DBAL 4.x (ADODB surface API for legacy
--     code)'). DBAL is in place today, ORM needs PKs on every entity.
--   * InnoDB row clustering performance — without an explicit PK,
--     InnoDB generates a hidden 6-byte row ID; secondary-index
--     lookups do an extra pointer-chase through the hidden id.
--   * Standard-compliance posture — HIPAA / NIST SP 800-53 database
--     hardening guides list 'every table must have a unique row
--     identifier' as a basic control.
--   * Galera compatibility — Galera's row-level replication cert
--     requires PRIMARY KEY on every replicated table. Not the
--     motivation, but a free bonus for openemr-devops users.
--
-- Four patterns, grouped below:
--   1. Promote existing auto_increment-backed non-PRIMARY index
--      (atomic DROP + ADD in one ALTER).
--   2. Promote SERIAL's implicit UNIQUE index.
--   3. ADD PRIMARY KEY on an existing NOT NULL non-autoinc column
--      that the application already treats as unique.
--   4. ADD a synthetic auto_increment id column (no suitable
--      existing candidate, or existing candidate is nullable or
--      the wrong type).
--
-- All blocks are idempotent via the #IfNotIndex PRIMARY / #IfMissingColumn
-- directives — safe to rerun. No data is inserted, updated, or deleted
-- in any ALTER; all changes are schema metadata plus a backfill of the
-- synthetic id values for tables in pattern 4.
--
-- A new pre-commit + CI gate (tests/schema/assert-primary-keys.sh)
-- parses sql/database.sql and contrib/util/language_translations/
-- currentLanguage_utf8.sql to prevent regressions: any new table
-- without a PRIMARY KEY fails the hook.
-- ========================================================================

-- Pattern 1: Promote existing auto_increment-backed non-PRIMARY index.

#IfNotIndex patient_data PRIMARY
ALTER TABLE `patient_data` DROP INDEX `id`, ADD PRIMARY KEY (`id`);
#EndIf

#IfNotIndex lang_constants PRIMARY
ALTER TABLE `lang_constants` DROP INDEX `cons_id`, ADD PRIMARY KEY (`cons_id`);
#EndIf

#IfNotIndex lang_definitions PRIMARY
ALTER TABLE `lang_definitions` DROP INDEX `def_id`, ADD PRIMARY KEY (`def_id`);
#EndIf

#IfNotIndex lang_languages PRIMARY
ALTER TABLE `lang_languages` DROP INDEX `lang_id`, ADD PRIMARY KEY (`lang_id`);
#EndIf

#IfNotIndex form_eye_mag_wearing PRIMARY
ALTER TABLE `form_eye_mag_wearing` DROP INDEX `id`, ADD PRIMARY KEY (`id`);
#EndIf

#IfNotIndex amendments_history PRIMARY
ALTER TABLE `amendments_history` DROP INDEX `amendment_history_id`, ADD PRIMARY KEY (`amendment_id`);
#EndIf

-- Pattern 2: Promote SERIAL's implicit UNIQUE index to PRIMARY KEY.
-- SERIAL = BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE; the UNIQUE
-- constraint creates an index auto-named after the column.

#IfNotIndex supported_external_dataloads PRIMARY
ALTER TABLE `supported_external_dataloads` DROP INDEX `load_id`, ADD PRIMARY KEY (`load_id`);
#EndIf

#IfNotIndex icd9_dx_code PRIMARY
ALTER TABLE `icd9_dx_code` DROP INDEX `dx_id`, ADD PRIMARY KEY (`dx_id`);
#EndIf

#IfNotIndex icd9_sg_code PRIMARY
ALTER TABLE `icd9_sg_code` DROP INDEX `sg_id`, ADD PRIMARY KEY (`sg_id`);
#EndIf

#IfNotIndex icd9_dx_long_code PRIMARY
ALTER TABLE `icd9_dx_long_code` DROP INDEX `dx_id`, ADD PRIMARY KEY (`dx_id`);
#EndIf

#IfNotIndex icd9_sg_long_code PRIMARY
ALTER TABLE `icd9_sg_long_code` DROP INDEX `sq_id`, ADD PRIMARY KEY (`sq_id`);
#EndIf

#IfNotIndex icd10_dx_order_code PRIMARY
ALTER TABLE `icd10_dx_order_code` DROP INDEX `dx_id`, ADD PRIMARY KEY (`dx_id`);
#EndIf

#IfNotIndex icd10_pcs_order_code PRIMARY
ALTER TABLE `icd10_pcs_order_code` DROP INDEX `pcs_id`, ADD PRIMARY KEY (`pcs_id`);
#EndIf

#IfNotIndex icd10_gem_pcs_9_10 PRIMARY
ALTER TABLE `icd10_gem_pcs_9_10` DROP INDEX `map_id`, ADD PRIMARY KEY (`map_id`);
#EndIf

#IfNotIndex icd10_gem_pcs_10_9 PRIMARY
ALTER TABLE `icd10_gem_pcs_10_9` DROP INDEX `map_id`, ADD PRIMARY KEY (`map_id`);
#EndIf

#IfNotIndex icd10_gem_dx_9_10 PRIMARY
ALTER TABLE `icd10_gem_dx_9_10` DROP INDEX `map_id`, ADD PRIMARY KEY (`map_id`);
#EndIf

#IfNotIndex icd10_gem_dx_10_9 PRIMARY
ALTER TABLE `icd10_gem_dx_10_9` DROP INDEX `map_id`, ADD PRIMARY KEY (`map_id`);
#EndIf

#IfNotIndex icd10_reimbr_dx_9_10 PRIMARY
ALTER TABLE `icd10_reimbr_dx_9_10` DROP INDEX `map_id`, ADD PRIMARY KEY (`map_id`);
#EndIf

#IfNotIndex icd10_reimbr_pcs_9_10 PRIMARY
ALTER TABLE `icd10_reimbr_pcs_9_10` DROP INDEX `map_id`, ADD PRIMARY KEY (`map_id`);
#EndIf

-- Pattern 3: ADD PRIMARY KEY on existing NOT NULL non-autoinc column.
-- Trust the application's uniqueness invariant on the chosen column.
-- Legacy sequence/counter tables are single-row by design; form tables
-- have application-assigned unique ids per encounter.

-- form_care_plan and form_functional_cognitive_status: `id` is the FORM id
-- (one form instance per encounter), but each form HOLDS multiple rows — one
-- row per care-plan item / per functional-cognitive line. CarePlanService::
-- create() inserts N rows with the same `id`; form_functional_cognitive_status
-- / save.php does the same. Promoting `id` to PRIMARY KEY would therefore
-- fail on every real install and break future multi-item inserts. Use the
-- Pattern-4 synthetic `row_id` instead; keep `id` as a non-unique KEY so
-- existing WHERE id = ? queries stay index-covered.

#IfMissingColumn form_care_plan row_id
ALTER TABLE `form_care_plan` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn form_functional_cognitive_status row_id
ALTER TABLE `form_functional_cognitive_status` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
                                                 ADD KEY `id` (`id`);
#EndIf

#IfNotIndex gacl_acl_seq PRIMARY
ALTER TABLE `gacl_acl_seq` ADD PRIMARY KEY (`id`);
#EndIf

#IfNotIndex gacl_aco_sections_seq PRIMARY
ALTER TABLE `gacl_aco_sections_seq` ADD PRIMARY KEY (`id`);
#EndIf

#IfNotIndex gacl_aco_seq PRIMARY
ALTER TABLE `gacl_aco_seq` ADD PRIMARY KEY (`id`);
#EndIf

#IfNotIndex gacl_aro_groups_id_seq PRIMARY
ALTER TABLE `gacl_aro_groups_id_seq` ADD PRIMARY KEY (`id`);
#EndIf

#IfNotIndex gacl_aro_sections_seq PRIMARY
ALTER TABLE `gacl_aro_sections_seq` ADD PRIMARY KEY (`id`);
#EndIf

#IfNotIndex gacl_aro_seq PRIMARY
ALTER TABLE `gacl_aro_seq` ADD PRIMARY KEY (`id`);
#EndIf

#IfNotIndex edi_sequences PRIMARY
ALTER TABLE `edi_sequences` ADD PRIMARY KEY (`id`);
#EndIf

#IfNotIndex sequences PRIMARY
ALTER TABLE `sequences` ADD PRIMARY KEY (`id`);
#EndIf

-- Pattern 4: ADD a synthetic auto_increment surrogate column as PRIMARY KEY.
-- Used where no existing column is a safe PK candidate (all nullable,
-- no uniqueness, or the obvious `id` column is a varchar foreign-key-like
-- reference that would clash with a new INT id). Column named `id` where
-- the table has no existing `id`, `row_id` where there's a clash. Placed
-- FIRST in column order; existing rows receive sequential ids via the
-- auto_increment backfill during the ALTER.

#IfMissingColumn amc_misc_data row_id
ALTER TABLE `amc_misc_data` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn benefit_eligibility row_id
ALTER TABLE `benefit_eligibility` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn enc_category_map row_id
ALTER TABLE `enc_category_map` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn fee_sheet_options row_id
ALTER TABLE `fee_sheet_options` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn form_eye_mag_prefs row_id
ALTER TABLE `form_eye_mag_prefs` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn lang_custom row_id
ALTER TABLE `lang_custom` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn medex_prefs row_id
ALTER TABLE `medex_prefs` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn module_acl_sections row_id
ALTER TABLE `module_acl_sections` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn modules_settings row_id
ALTER TABLE `modules_settings` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn patient_tracker_element row_id
ALTER TABLE `patient_tracker_element` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn report_itemized row_id
ALTER TABLE `report_itemized` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn rule_action row_id
ALTER TABLE `rule_action` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn rule_filter row_id
ALTER TABLE `rule_filter` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn rule_reminder row_id
ALTER TABLE `rule_reminder` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn rule_target row_id
ALTER TABLE `rule_target` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn version row_id
ALTER TABLE `version` ADD COLUMN `row_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf
