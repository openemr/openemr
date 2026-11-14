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

-- Patient Finder combined columns (PatientFinderBuiltInColumns). Added switched off, so a
-- site's finder only changes once an administrator enables one in Patient List Columns.
#IfNotRow2D list_options list_id ptlistcols option_id name_first_last
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `option_value`, `activity`) VALUES ('ptlistcols', 'name_first_last', 'First Last', 60, 3, 0);
#EndIf

#IfNotRow2D list_options list_id ptlistcols option_id name_first_last_middle
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `option_value`, `activity`) VALUES ('ptlistcols', 'name_first_last_middle', 'First Middle Last', 70, 3, 0);
#EndIf

#IfNotRow2D list_options list_id ptlistcols option_id city_state
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `option_value`, `activity`) VALUES ('ptlistcols', 'city_state', 'City State', 80, 3, 0);
#EndIf

#IfNotRow2D list_options list_id ptlistcols option_id address_full
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `option_value`, `activity`) VALUES ('ptlistcols', 'address_full', 'Address', 90, 3, 0);
#EndIf

#IfNotRow2D list_options list_id ptlistcols option_id address_full_country
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `option_value`, `activity`) VALUES ('ptlistcols', 'address_full_country', 'Address and Country', 100, 3, 0);
#EndIf

#IfNotRow2D list_options list_id ptlistcols option_id age
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `option_value`, `activity`) VALUES ('ptlistcols', 'age', 'Age', 110, 3, 0);
#EndIf

#IfNotRow2D list_options list_id ptlistcols option_id next_appointment
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `option_value`, `activity`) VALUES ('ptlistcols', 'next_appointment', 'Next Appt', 120, 3, 0);
#EndIf

#IfNotRow2D list_options list_id ptlistcols option_id last_appointment
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `option_value`, `activity`) VALUES ('ptlistcols', 'last_appointment', 'Last Appt', 130, 3, 0);
#EndIf

#IfNotRow2D list_options list_id ptlistcols option_id insurance_primary_provider_name
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `option_value`, `activity`) VALUES ('ptlistcols', 'insurance_primary_provider_name', 'Primary Insurance', 140, 3, 0);
#EndIf

#IfNotRow2D list_options list_id ptlistcols option_id insurance_primary_policy
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `option_value`, `activity`) VALUES ('ptlistcols', 'insurance_primary_policy', 'Primary Policy Number', 150, 3, 0);
#EndIf

-- Patient Finder Detail: the layout an expanded Patient Finder row shows. Not an LBF id, since
-- every active LBF layout is offered as an encounter form. Each part has its own guard so a
-- rerun finishes an interrupted seed; the fields go in only while the layout has none, so a
-- site that removed some of them keeps its changes.
#IfNotRow layout_group_properties grp_form_id PTFINDER
INSERT INTO `layout_group_properties` (`grp_form_id`, `grp_group_id`, `grp_title`, `grp_mapping`, `grp_columns`, `grp_size`) VALUES ('PTFINDER', '', 'Patient Finder Detail', 'Core', 8, 9);
#EndIf

#IfNotRow2D layout_group_properties grp_form_id PTFINDER grp_group_id 1
INSERT INTO `layout_group_properties` (`grp_form_id`, `grp_group_id`, `grp_title`, `grp_mapping`) VALUES ('PTFINDER', '1', 'Main', '');
#EndIf

#IfNotRow layout_options form_id PTFINDER
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES
    ('PTFINDER', 'insurance_primary_provider_name', '1', 'Insurance', 10, 2, 1, 20, 63, '', 1, 2, '', '', 'Primary insurance in effect today', 0),
    ('PTFINDER', 'sex', '1', 'Sex', 20, 1, 1, 0, 0, 'sex', 1, 2, '', '', 'Sex', 0),
    ('PTFINDER', 'insurance_primary_policy', '1', 'Insurance ID', 30, 2, 1, 20, 60, '', 1, 2, '', '["J"]', 'Policy number of the primary insurance in effect today', 0),
    ('PTFINDER', 'address_full', '1', 'Address', 40, 2, 1, 20, 200, '', 1, 7, '', '["J"]', 'Street, city, state and postal code', 0);
#EndIf

-- Patient Finder column lists: named after the finder and its tabs, so they sit together in
-- Administration > Lists. Only a name still at its default changes; a site's own name stays.
UPDATE `list_options` SET `title` = 'Patient Finder – Patient List Columns' WHERE `list_id` = 'lists' AND `option_id` = 'ptlistcols' AND `title` = 'Patient List Columns';
UPDATE `list_options` SET `title` = 'Patient Finder – Recent Patients Columns' WHERE `list_id` = 'lists' AND `option_id` = 'recent_patient_columns' AND `title` = 'Recent Patient Columns';

-- Recent Patients Use Patient List Columns: on for new installs, off here so an existing
-- site's Recent Patients tab keeps its own columns until an administrator turns it on.
-- Only a site that has been set up is changed: the installer writes every global's
-- default, so a bare database.sql (with no globals) gets the default, as a new install does.
#IfNotRow globals gl_name gbl_pt_list_recent_same_columns
INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`)
SELECT 'gbl_pt_list_recent_same_columns', 0, '0' FROM DUAL
WHERE EXISTS (SELECT 1 FROM `globals` WHERE `gl_name` = 'language_default');
#EndIf
