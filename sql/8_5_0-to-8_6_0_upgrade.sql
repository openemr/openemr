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

-- The UUID service's mapping check joins on target_uuid, resource and resource_path; with only the
-- single-column target_uuid key it reads every mapping row of a record to keep one (15 per vitals form).
#IfNotIndex uuid_mapping idx_target_res_path
CREATE INDEX `idx_target_res_path` ON `uuid_mapping` (`target_uuid`, `resource`, `resource_path`);
#EndIf
