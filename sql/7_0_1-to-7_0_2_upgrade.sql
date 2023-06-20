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

#IfTable pma_bookmark
DROP TABLE IF EXISTS `pma_bookmark`;
#EndIf

#IfTable pma_column_info
DROP TABLE IF EXISTS `pma_column_info`;
#EndIf

#IfTable pma_history
DROP TABLE IF EXISTS `pma_history`;
#EndIf

#IfTable pma_pdf_pages
DROP TABLE IF EXISTS `pma_pdf_pages`;
#EndIf

#IfTable pma_relation
DROP TABLE IF EXISTS `pma_relation`;
#EndIf

#IfTable pma_table_coords
DROP TABLE IF EXISTS `pma_table_coords`;
#EndIf

#IfTable pma_table_info
DROP TABLE IF EXISTS `pma_table_info`;
#EndIf

#IfMissingColumn x12_partners x12_submitter_id
ALTER TABLE `x12_partners` ADD COLUMN `x12_submitter_id` smallint(6) DEFAULT NULL;
#EndIf

#IfNotRow2D list_options list_id abook_type option_id bill_svc
INSERT INTO list_options (list_id, option_id, title, seq, option_value) VALUES ('abook_type', 'bill_svc', 'Billing Service', 125, 3);
#EndIf

#IfMissingColumn users_secure last_login_fail
ALTER TABLE `users_secure` ADD `last_login_fail` datetime DEFAULT NULL;
#EndIf

#IfMissingColumn users_secure total_login_fail_counter
ALTER TABLE `users_secure` ADD `total_login_fail_counter` bigint DEFAULT 0;
#EndIf

#IfMissingColumn users_secure auto_block_emailed
ALTER TABLE `users_secure` ADD `auto_block_emailed` tinyint DEFAULT 0;
#EndIf

#IfNotRow globals gl_name time_reset_password_max_failed_logins
UPDATE `globals` SET `gl_value` = 20 WHERE `gl_name` = 'password_max_failed_logins' AND `gl_value` = 0;
#EndIf

#IfNotTable ip_tracking
CREATE TABLE `ip_tracking` (
`id` bigint NOT NULL auto_increment,
`ip_string` varchar(255) DEFAULT '',
`total_ip_login_fail_counter` bigint DEFAULT 0,
`ip_login_fail_counter` bigint DEFAULT 0,
`ip_last_login_fail` datetime DEFAULT NULL,
`ip_auto_block_emailed` tinyint DEFAULT 0,
`ip_force_block` tinyint DEFAULT 0,
`ip_no_prevent_timing_attack` tinyint DEFAULT 0,
PRIMARY KEY (`id`),
UNIQUE KEY `ip_string` (`ip_string`)
) ENGINE=InnoDb AUTO_INCREMENT=1;
#EndIf

#IfNotTable email_queue
CREATE TABLE `email_queue` (
`id` bigint NOT NULL auto_increment,
`sender` varchar(255) DEFAULT '',
`recipient` varchar(255) DEFAULT '',
`subject` varchar(255) DEFAULT '',
`body` text,
`datetime_queued` datetime default NULL,
`sent` tinyint DEFAULT 0,
`datetime_sent` datetime default NULL,
`error` tinyint DEFAULT 0,
`error_message` text,
`datetime_error` datetime default NULL,
PRIMARY KEY (`id`),
KEY `sent` (`sent`)
) ENGINE=InnoDb AUTO_INCREMENT=1;
#EndIf

#IfNotRow background_services name Email_Service
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('Email_Service', 'Email Service', 1, 0, '2021-01-18 11:25:10', 2, 'emailServiceRun', '/library/email_service_run.php', 100);
#EndIf


#IfNotTable patient_settings
CREATE TABLE `patient_settings` (
`setting_patient`  bigint(20)   NOT NULL DEFAULT 0,
`setting_label` varchar(100)  NOT NULL,
`setting_value` varchar(255) NOT NULL DEFAULT '',
PRIMARY KEY (`setting_patient`, `setting_label`)
) ENGINE=InnoDB;
#EndIf

#IfNotRow2D list_options list_id Document_Template_Categories option_id notification_template
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`) VALUES ('Document_Template_Categories','notification_template','Notification Template',105,0,0,'','','',0,0,1);
#EndIf
