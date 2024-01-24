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

#IfMissingColumn facility inactive
ALTER TABLE `facility` ADD COLUMN `inactive` tinyint(1) NOT NULL DEFAULT '0';
#EndIf

#IfNotRow2D list_options list_id Document_Template_Categories option_id notification_template
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`) VALUES ('Document_Template_Categories','notification_template','Notification Template',105,0,0,'','','',0,0,1);
#EndIf

#IfNotRow2D list_options list_id lists option_id default_open_tabs
-- Create new list Default Open Tabs
INSERT INTO `list_options` (`list_id`, `option_id`, `title`) VALUES ('lists', 'default_open_tabs', 'Default Open Tabs');
-- Populate list with sensible defaults based on previous optiosn in globals, assume no tabs are actually active
INSERT INTO `list_options` (`list_id`, `notes`, `title`, `seq`, `option_id`, `activity`) VALUES ('default_open_tabs', 'interface/main/main_info.php', 'Calendar', 10, 'cal', '0');
INSERT INTO `list_options` (`list_id`, `notes`, `title`, `seq`, `option_id`, `activity`) VALUES ('default_open_tabs', 'interface/new/new.php', 'Patient Search / Add', 20, 'pat', '0');
INSERT INTO `list_options` (`list_id`, `notes`, `title`, `seq`, `option_id`, `activity`) VALUES ('default_open_tabs', 'interface/main/finder/dynamic_finder.php', 'Patient Finder', 30, 'fin', '0');
INSERT INTO `list_options` (`list_id`, `notes`, `title`, `seq`, `option_id`, `activity`) VALUES ('default_open_tabs', 'interface/patient_tracker/patient_tracker.php?skip_timeout_reset=1', 'Flow Board', 40, 'flb', '0');
INSERT INTO `list_options` (`list_id`, `notes`, `title`, `seq`, `option_id`, `activity`) VALUES ('default_open_tabs', 'interface/main/messages/messages.php?form_active=1', 'Message Inbox', 50, 'msg', '0');
-- Activate the 2 list options that were the previous default and second pane settings
-- note need the second line for when upgrading in a version before the default_second_tab was added, which for example will impact the official demos
UPDATE `list_options` lo INNER JOIN globals g ON lo.notes LIKE CONCAT('%', g.gl_value) AND lo.list_id = 'default_open_tabs' SET lo.activity = 1 WHERE g.gl_name = 'default_top_pane' OR g.gl_name = 'default_second_tab';
UPDATE `list_options` SET `activity` = 1 WHERE `list_id` = 'default_open_tabs' AND `option_id` = 'msg' AND NOT EXISTS (SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'default_second_tab');
#EndIf

#IfNotRow2D list_options list_id lists option_id recent_patient_columns
INSERT INTO `list_options` (`list_id`, `option_id`, `title`) VALUES ('lists', 'recent_patient_columns', 'Recent Patient Columns');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('recent_patient_columns', 'fname', 'First Name', '10');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('recent_patient_columns', 'mname', 'Middle Name', '20');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('recent_patient_columns', 'lname', 'Last Name', '30');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('recent_patient_columns', 'DOB', 'Date of Birth', '40');
#EndIf

#IfNotTable recent_patients
CREATE TABLE recent_patients (
    user_id varchar(40) NOT NULL,
    patients TEXT,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn oauth_clients skip_ehr_launch_authorization_flow
ALTER TABLE `oauth_clients` ADD COLUMN `skip_ehr_launch_authorization_flow` tinyint(1) NOT NULL DEFAULT '0';
#EndIf

#IfMissingColumn document_template_profiles notify_trigger
ALTER TABLE `document_template_profiles` ADD `notify_trigger` VARCHAR(31) NOT NULL;
ALTER TABLE `document_template_profiles` ADD `notify_period` INT(4) NOT NULL;
#EndIf

#IfNotRow2D layout_options form_id DEM field_id preferred_name
SET @group_id = (SELECT `group_id` FROM layout_options WHERE field_id='suffix' AND form_id='DEM');
SET @seq_start := 0;
UPDATE `layout_options` SET `seq` = (@seq_start := @seq_start+1)*10 WHERE group_id = @group_id AND form_id='DEM' ORDER BY `seq`;
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='suffix' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES ('DEM','preferred_name',@group_id,'Preferred Name',@seq_add_to+5,2,1,32,64,'',1,3,'','[\"J\",\"DAP\"]','Patient preferred name or name patient is commonly known.',0);
ALTER TABLE `patient_data` ADD `preferred_name` TINYTEXT;
#EndIf

#IfMissingColumn email_queue template_name
ALTER TABLE `email_queue` ADD `template_name` VARCHAR(255) DEFAULT NULL COMMENT 'The folder prefix and base filename (w/o extension) of the twig template file to use for this email';
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2023-10-01 load_filename Code Descriptions.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2023-10-01', 'Code Descriptions.zip', '15404ef88e0ffa15474e6d6076aa0a8a');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2023-10-01 load_filename Zip File 3 2024 ICD-10-PCS Codes File.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2023-10-01', 'Zip File 3 2024 ICD-10-PCS Codes File.zip', '30e096ed9971755c4dfc134b938f3c1f');
#EndIf

#IfMissingColumn onsite_documents template_data
ALTER TABLE `onsite_documents` ADD `template_data` LONGTEXT;
#EndIf

#IfNotRow2D layout_options form_id DEM field_id nationality_country
SET @group_id = (SELECT `group_id` FROM layout_options WHERE field_id='race' AND form_id='DEM');
SET @seq_start := 0;
UPDATE `layout_options` SET `seq` = (@seq_start := @seq_start+1)*10 WHERE group_id = @group_id AND form_id='DEM' ORDER BY `seq`;
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='race' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`, `validation`, `codes`) VALUES ('DEM','nationality_country',@group_id,'Nationality and Country',@seq_add_to+5,43,1,0,0,'Nationality_and_Country',1,1,'','','Patient Nationality. Type to search.',0,'','F','','','');
#EndIf

#IfMissingColumn patient_data nationality_country
ALTER TABLE `patient_data` ADD `nationality_country` TINYTEXT;
#EndIf

#IfRow2D list_options list_id lists option_id Nationality_and_Country
DELETE FROM `list_options` WHERE  `list_id` = 'lists' AND `option_id` = 'Nationality_and_Country';
#EndIf

#IfRow list_options list_id Nationality_and_Country
DELETE FROM `list_options` WHERE  `list_id` LIKE 'Nationality_and_Country';
#EndIf

#IfNotTable edi_sequences
CREATE TABLE `edi_sequences` (
    `id` int(9) unsigned NOT NULL default '0'
) ENGINE=InnoDB;
INSERT INTO `edi_sequences` VALUES (0);
#EndIf

#IfMissingColumn x12_partners x12_client_id
ALTER TABLE `x12_partners` ADD COLUMN `x12_client_id` tinytext;
#EndIf

#IfMissingColumn x12_partners x12_client_secret
ALTER TABLE `x12_partners` ADD COLUMN `x12_client_secret` tinytext;
#EndIf

#IfMissingColumn x12_partners x12_token_endpoint
ALTER TABLE `x12_partners` ADD COLUMN `x12_token_endpoint` tinytext;
#EndIf

#IfMissingColumn x12_partners x12_eligibility_endpoint
ALTER TABLE `x12_partners` ADD COLUMN `x12_eligibility_endpoint` tinytext;
#EndIf

#IfMissingColumn x12_partners x12_claim_status_endpoint
ALTER TABLE `x12_partners` ADD COLUMN `x12_claim_status_endpoint` tinytext;
#EndIf

#IfMissingColumn x12_partners x12_attachment_endpoint
ALTER TABLE `x12_partners` ADD COLUMN `x12_attachment_endpoint` tinytext;
#EndIf

#IfRow3D layout_options form_id DEM field_id nationality_country list_id Nationality_and_Country
UPDATE `layout_options` SET `title` = 'Nationality', `list_id` = 'nationality_with_country' WHERE `form_id` = 'DEM' AND `field_id` = 'nationality_country';
#EndIf

#IfNotRow2D list_options list_id lists option_id nationality_with_country
-- Combined list nationality and country --
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('lists','nationality_with_country','Nationality',1,1,0,'',NULL,'',0,0,1,'',1,'2023-09-24 18:21:13');
-- Items --
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AF', 'Afghan', '10', '0', '0', '', 'Afghanistan', 'AFG:004');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AX', 'Ålandish', '20', '0', '0', '', 'Åland Islands', 'ALA:248');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AL', 'Albanian', '30', '0', '0', '', 'Albania', 'ALB:008');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'DZ', 'Algerian', '40', '0', '0', '', 'Algeria', 'DZA:012');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'US', 'American', '50', '0', '0', '', 'United States', 'USA:840');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'UM', 'American Islander', '60', '0', '0', '', 'United States Minor Outlying Islands', 'UMI:581');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MP', 'American Mariana Islands', '70', '0', '0', '', 'Northern Mariana Islands', 'MNP:580');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AS', 'American Samoan', '80', '0', '0', '', 'American Samoa', 'ASM:016');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AD', 'Andorran', '90', '0', '0', '', 'Andorra', 'AND:020');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AO', 'Angolan', '100', '0', '0', '', 'Angola', 'AGO:024');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AI', 'Anguillian', '110', '0', '0', '', 'Anguilla', 'AIA:660');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AQ', 'Antarctican', '120', '0', '0', '', 'Antarctica', 'ATA:010');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AG', 'Antiguan, Barbudan', '130', '0', '0', '', 'Antigua and Barbuda', 'ATG:028');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AR', 'Argentine', '140', '0', '0', '', 'Argentina', 'ARG:032');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AM', 'Armenian', '150', '0', '0', '', 'Armenia', 'ARM:051');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AW', 'Aruban', '160', '0', '0', '', 'Aruba', 'ABW:533');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AU', 'Australian', '170', '0', '0', '', 'Australia', 'AUS:036');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AT', 'Austrian', '180', '0', '0', '', 'Austria', 'AUT:040');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AZ', 'Azerbaijani', '190', '0', '0', '', 'Azerbaijan', 'AZE:031');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BS', 'Bahamian', '200', '0', '0', '', 'Bahamas', 'BHS:044');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BH', 'Bahraini', '210', '0', '0', '', 'Bahrain', 'BHR:048');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BD', 'Bangladeshi', '220', '0', '0', '', 'Bangladesh', 'BGD:050');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BB', 'Barbadian', '230', '0', '0', '', 'Barbados', 'BRB:052');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BY', 'Belarusian', '240', '0', '0', '', 'Belarus', 'BLR:112');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BE', 'Belgian', '250', '0', '0', '', 'Belgium', 'BEL:056');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BZ', 'Belizean', '260', '0', '0', '', 'Belize', 'BLZ:084');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BJ', 'Beninese', '270', '0', '0', '', 'Benin', 'BEN:204');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BM', 'Bermudian', '280', '0', '0', '', 'Bermuda', 'BMU:060');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BT', 'Bhutanese', '290', '0', '0', '', 'Bhutan', 'BTN:064');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BO', 'Bolivian', '300', '0', '0', '', 'Bolivia', 'BOL:068');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BA', 'Bosnian, Herzegovinian', '310', '0', '0', '', 'Bosnia and Herzegovina', 'BIH:070');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BR', 'Brazilian', '320', '0', '0', '', 'Brazil', 'BRA:076');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GB', 'British', '330', '0', '0', '', 'United Kingdom', 'GBR:826');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'IO', 'British Indian', '1080', '0', '0', '', 'British Indian Ocean Territory', 'IOT:086');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BN', 'Bruneian', '340', '0', '0', '', 'Brunei', 'BRN:096');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BG', 'Bulgarian', '350', '0', '0', '', 'Bulgaria', 'BGR:100');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BF', 'Burkinabe', '360', '0', '0', '', 'Burkina Faso', 'BFA:854');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MM', 'Burmese', '370', '0', '0', '', 'Myanmar', 'MMR:104');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BI', 'Burundian', '380', '0', '0', '', 'Burundi', 'BDI:108');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'KH', 'Cambodian', '390', '0', '0', '', 'Cambodia', 'KHM:116');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CM', 'Cameroonian', '400', '0', '0', '', 'Cameroon', 'CMR:120');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CA', 'Canadian', '410', '0', '0', '', 'Canada', 'CAN:124');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CV', 'Cape Verdian', '420', '0', '0', '', 'Cape Verde', 'CPV:132');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'KY', 'Caymanian', '430', '0', '0', '', 'Cayman Islands', 'CYM:136');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CF', 'Central African', '440', '0', '0', '', 'Central African Republic', 'CAF:140');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TD', 'Chadian', '450', '0', '0', '', 'Chad', 'TCD:148');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GG', 'Channel Islander Guernsey', '470', '0', '0', '', 'Guernsey', 'GGY:831');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'JE', 'Channel Islander Jersey', '460', '0', '0', '', 'Jersey', 'JEY:832');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CL', 'Chilean', '480', '0', '0', '', 'Chile', 'CHL:152');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CN', 'Chinese', '490', '0', '0', '', 'China', 'CHN:156');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CX', 'Christmas Islander', '500', '0', '0', '', 'Christmas Island', 'CXR:162');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CC', 'Cocos Islander', '510', '0', '0', '', 'Cocos (Keeling) Islands', 'CCK:166');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CO', 'Colombian', '520', '0', '0', '', 'Colombia', 'COL:170');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'KM', 'Comoran', '530', '0', '0', '', 'Comoros', 'COM:174');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CG', 'Congolese', '540', '0', '0', '', 'Republic of the Congo', 'COG:178');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CK', 'Cook Islander', '550', '0', '0', '', 'Cook Islands', 'COK:184');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CR', 'Costa Rican', '560', '0', '0', '', 'Costa Rica', 'CRI:188');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'HR', 'Croatian', '570', '0', '0', '', 'Croatia', 'HRV:191');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CU', 'Cuban', '580', '0', '0', '', 'Cuba', 'CUB:192');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CW', 'Curaçaoan', '590', '0', '0', '', 'Curaçao', 'CUW:531');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CY', 'Cypriot', '600', '0', '0', '', 'Cyprus', 'CYP:196');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CZ', 'Czech', '610', '0', '0', '', 'Czechia', 'CZE:203');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'DK', 'Danish', '620', '0', '0', '', 'Denmark', 'DNK:208');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'DJ', 'Djibouti', '630', '0', '0', '', 'Djibouti', 'DJI:262');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'DO', 'Dominican', '640', '0', '0', '', 'Dominican Republic', 'DOM:214');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NL', 'Dutch', '660', '0', '0', '', 'Netherlands', 'NLD:528');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BQ', 'Dutch Caribbean ', '650', '0', '0', '', 'Caribbean Netherlands', 'BES:535');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TL', 'East Timorese', '670', '0', '0', '', 'Timor-Leste', 'TLS:626');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'EC', 'Ecuadorean', '680', '0', '0', '', 'Ecuador', 'ECU:218');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'EG', 'Egyptian', '690', '0', '0', '', 'Egypt', 'EGY:818');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'AE', 'Emirati', '700', '0', '0', '', 'United Arab Emirates', 'ARE:784');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'English', 'English', '710', '', '', '', 'England', '');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GQ', 'Equatorial Guinean', '720', '0', '0', '', 'Equatorial Guinea', 'GNQ:226');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'ER', 'Eritrean', '730', '0', '0', '', 'Eritrea', 'ERI:232');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'EE', 'Estonian', '740', '0', '0', '', 'Estonia', 'EST:233');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'ET', 'Ethiopian', '750', '0', '0', '', 'Ethiopia', 'ETH:231');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'FK', 'Falkland Islander', '760', '0', '0', '', 'Falkland Islands', 'FLK:238');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'FO', 'Faroese', '770', '0', '0', '', 'Faroe Islands', 'FRO:234');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'FJ', 'Fijian', '780', '0', '0', '', 'Fiji', 'FJI:242');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PH', 'Filipino', '790', '0', '0', '', 'Philippines', 'PHL:608');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'FI', 'Finnish', '800', '0', '0', '', 'Finland', 'FIN:246');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'FR', 'French', '820', '0', '0', '', 'France', 'FRA:250');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PF', 'French Polynesian', '830', '0', '0', '', 'French Polynesia', 'PYF:258');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TF', 'French Southern ', '810', '0', '0', '', 'French Southern and Antarctic Lands', 'ATF:260');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GA', 'Gabonese', '840', '0', '0', '', 'Gabon', 'GAB:266');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GM', 'Gambian', '850', '0', '0', '', 'Gambia', 'GMB:270');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GE', 'Georgian', '860', '0', '0', '', 'Georgia', 'GEO:268');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'DE', 'German', '870', '0', '0', '', 'Germany', 'DEU:276');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GH', 'Ghanaian', '880', '0', '0', '', 'Ghana', 'GHA:288');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GI', 'Gibraltar', '890', '0', '0', '', 'Gibraltar', 'GIB:292');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GR', 'Greek', '900', '0', '0', '', 'Greece', 'GRC:300');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GL', 'Greenlandic', '910', '0', '0', '', 'Greenland', 'GRL:304');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GD', 'Grenadian', '920', '0', '0', '', 'Grenada', 'GRD:308');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GP', 'Guadeloupian', '930', '0', '0', '', 'Guadeloupe', 'GLP:312');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GU', 'Guamanian', '940', '0', '0', '', 'Guam', 'GUM:316');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GT', 'Guatemalan', '950', '0', '0', '', 'Guatemala', 'GTM:320');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GF', 'Guianan', '960', '0', '0', '', 'French Guiana', 'GUF:254');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GW', 'Guinea-Bissauan', '970', '0', '0', '', 'Guinea-Bissau', 'GNB:624');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GN', 'Guinean', '980', '0', '0', '', 'Guinea', 'GIN:324');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GY', 'Guyanese', '990', '0', '0', '', 'Guyana', 'GUY:328');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'HT', 'Haitian', '1000', '0', '0', '', 'Haiti', 'HTI:332');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'HM', 'Heard and McDonald Islander', '1010', '0', '0', '', 'Heard Island and McDonald Islands', 'HMD:334');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'HN', 'Honduran', '1020', '0', '0', '', 'Honduras', 'HND:340');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'HK', 'Hong Konger', '1030', '0', '0', '', 'Hong Kong', 'HKG:344');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'HU', 'Hungarian', '1040', '0', '0', '', 'Hungary', 'HUN:348');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'KI', 'I-Kiribati', '1050', '0', '0', '', 'Kiribati', 'KIR:296');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'IS', 'Icelander', '1060', '0', '0', '', 'Iceland', 'ISL:352');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'IN', 'Indian', '1070', '0', '0', '', 'India', 'IND:356');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'ID', 'Indonesian', '1090', '0', '0', '', 'Indonesia', 'IDN:360');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'IR', 'Iranian', '1100', '0', '0', '', 'Iran', 'IRN:364');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'IQ', 'Iraqi', '1110', '0', '0', '', 'Iraq', 'IRQ:368');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'IE', 'Irish', '1120', '0', '0', '', 'Ireland', 'IRL:372');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'IL', 'Israeli', '1130', '0', '0', '', 'Israel', 'ISR:376');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'IT', 'Italian', '1140', '0', '0', '', 'Italy', 'ITA:380');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CI', 'Ivorian', '1150', '0', '0', '', 'Ivory Coast', 'CIV:384');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'JM', 'Jamaican', '1160', '0', '0', '', 'Jamaica', 'JAM:388');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'JP', 'Japanese', '1170', '0', '0', '', 'Japan', 'JPN:392');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'JO', 'Jordanian', '1180', '0', '0', '', 'Jordan', 'JOR:400');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'KZ', 'Kazakhstani', '1190', '0', '0', '', 'Kazakhstan', 'KAZ:398');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'KE', 'Kenyan', '1200', '0', '0', '', 'Kenya', 'KEN:404');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'KG', 'Kirghiz', '1210', '0', '0', '', 'Kyrgyzstan', 'KGZ:417');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'KN', 'Kittitian or Nevisian', '1220', '0', '0', '', 'Saint Kitts and Nevis', 'KNA:659');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'XK', 'Kosovar', '1230', '0', '0', '', 'Kosovo', 'UNK:');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'KW', 'Kuwaiti', '1240', '0', '0', '', 'Kuwait', 'KWT:414');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'LA', 'Laotian', '1250', '0', '0', '', 'Laos', 'LAO:418');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'LV', 'Latvian', '1260', '0', '0', '', 'Latvia', 'LVA:428');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'LB', 'Lebanese', '1270', '0', '0', '', 'Lebanon', 'LBN:422');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'LR', 'Liberian', '1280', '0', '0', '', 'Liberia', 'LBR:430');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'LY', 'Libyan', '1290', '0', '0', '', 'Libya', 'LBY:434');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'LI', 'Liechtensteiner', '1300', '0', '0', '', 'Liechtenstein', 'LIE:438');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'LT', 'Lithuanian', '1310', '0', '0', '', 'Lithuania', 'LTU:440');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'LU', 'Luxembourger', '1320', '0', '0', '', 'Luxembourg', 'LUX:442');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MO', 'Macanese', '1330', '0', '0', '', 'Macau', 'MAC:446');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MK', 'Macedonian', '1340', '0', '0', '', 'North Macedonia', 'MKD:807');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'YT', 'Mahoran', '1350', '0', '0', '', 'Mayotte', 'MYT:175');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MG', 'Malagasy', '1360', '0', '0', '', 'Madagascar', 'MDG:450');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MW', 'Malawian', '1370', '0', '0', '', 'Malawi', 'MWI:454');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MY', 'Malaysian', '1380', '0', '0', '', 'Malaysia', 'MYS:458');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MV', 'Maldivan', '1390', '0', '0', '', 'Maldives', 'MDV:462');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'ML', 'Malian', '1400', '0', '0', '', 'Mali', 'MLI:466');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MT', 'Maltese', '1410', '0', '0', '', 'Malta', 'MLT:470');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'IM', 'Manx', '1420', '0', '0', '', 'Isle of Man', 'IMN:833');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MH', 'Marshallese', '1430', '0', '0', '', 'Marshall Islands', 'MHL:584');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MQ', 'Martinican', '1440', '0', '0', '', 'Martinique', 'MTQ:474');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MR', 'Mauritanian', '1450', '0', '0', '', 'Mauritania', 'MRT:478');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MU', 'Mauritian', '1460', '0', '0', '', 'Mauritius', 'MUS:480');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MX', 'Mexican', '1470', '0', '0', '', 'Mexico', 'MEX:484');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'FM', 'Micronesian', '1480', '0', '0', '', 'Micronesia', 'FSM:583');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MD', 'Moldovan', '1490', '0', '0', '', 'Moldova', 'MDA:498');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MC', 'Monegasque', '1500', '0', '0', '', 'Monaco', 'MCO:492');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MN', 'Mongolian', '1510', '0', '0', '', 'Mongolia', 'MNG:496');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'ME', 'Montenegrin', '1520', '0', '0', '', 'Montenegro', 'MNE:499');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MS', 'Montserratian', '1530', '0', '0', '', 'Montserrat', 'MSR:500');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MA', 'Moroccan', '1540', '0', '0', '', 'Morocco', 'MAR:504');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'LS', 'Mosotho', '1550', '0', '0', '', 'Lesotho', 'LSO:426');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BW', 'Motswana', '1560', '0', '0', '', 'Botswana', 'BWA:072');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MZ', 'Mozambican', '1570', '0', '0', '', 'Mozambique', 'MOZ:508');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NA', 'Namibian', '1580', '0', '0', '', 'Namibia', 'NAM:516');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NR', 'Nauruan', '1590', '0', '0', '', 'Nauru', 'NRU:520');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NP', 'Nepalese', '1600', '0', '0', '', 'Nepal', 'NPL:524');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NC', 'New Caledonian', '1610', '0', '0', '', 'New Caledonia', 'NCL:540');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NZ', 'New Zealander', '1620', '0', '0', '', 'New Zealand', 'NZL:554');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'VU', 'Ni-Vanuatu', '1630', '0', '0', '', 'Vanuatu', 'VUT:548');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NI', 'Nicaraguan', '1640', '0', '0', '', 'Nicaragua', 'NIC:558');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NG', 'Nigerian', '1650', '0', '0', '', 'Nigeria', 'NGA:566');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NE', 'Nigerien', '1660', '0', '0', '', 'Niger', 'NER:562');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NU', 'Niuean', '1670', '0', '0', '', 'Niue', 'NIU:570');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NF', 'Norfolk Islander', '1680', '0', '0', '', 'Norfolk Island', 'NFK:574');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'KP', 'North Korean', '1690', '0', '0', '', 'North Korea', 'PRK:408');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'NO', 'Norwegian', '1710', '0', '0', '', 'Norway', 'NOR:578');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BV', 'Norwegian Bouvet', '1720', '0', '0', '', 'Bouvet Island', 'BVT:074');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SJ', 'Norwegian Svalbard and Jan Mayen', '1700', '0', '0', '', 'Svalbard and Jan Mayen', 'SJM:744');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'OM', 'Omani', '1730', '0', '0', '', 'Oman', 'OMN:512');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PK', 'Pakistani', '1740', '0', '0', '', 'Pakistan', 'PAK:586');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PW', 'Palauan', '1750', '0', '0', '', 'Palau', 'PLW:585');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PS', 'Palestinian', '1760', '0', '0', '', 'Palestine', 'PSE:275');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PA', 'Panamanian', '1770', '0', '0', '', 'Panama', 'PAN:591');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PG', 'Papua New Guinean', '1780', '0', '0', '', 'Papua New Guinea', 'PNG:598');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PY', 'Paraguayan', '1790', '0', '0', '', 'Paraguay', 'PRY:600');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PE', 'Peruvian', '1800', '0', '0', '', 'Peru', 'PER:604');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PN', 'Pitcairn Islander', '1810', '0', '0', '', 'Pitcairn Islands', 'PCN:612');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PL', 'Polish', '1820', '0', '0', '', 'Poland', 'POL:616');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PT', 'Portuguese', '1830', '0', '0', '', 'Portugal', 'PRT:620');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PR', 'Puerto Rican', '1840', '0', '0', '', 'Puerto Rico', 'PRI:630');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'QA', 'Qatari', '1850', '0', '0', '', 'Qatar', 'QAT:634');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'RE', 'Réunionese', '1860', '0', '0', '', 'Réunion', 'REU:638');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'RO', 'Romanian', '1870', '0', '0', '', 'Romania', 'ROU:642');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'RU', 'Russian', '1880', '0', '0', '', 'Russia', 'RUS:643');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'RW', 'Rwandan', '1890', '0', '0', '', 'Rwanda', 'RWA:646');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'EH', 'Sahrawi', '1900', '0', '0', '', 'Western Sahara', 'ESH:732');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'BL', 'Saint Barthélemy Islander', '1910', '0', '0', '', 'Saint Barthélemy', 'BLM:652');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SH', 'Saint Helenian', '1920', '0', '0', '', 'Saint Helena, Ascension and Tristan da Cunha', 'SHN:654');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'LC', 'Saint Lucian', '1930', '0', '0', '', 'Saint Lucia', 'LCA:662');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'MF', 'Saint Martin Islander', '1940', '0', '0', '', 'Saint Martin', 'MAF:663');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'VC', 'Saint Vincentian', '1950', '0', '0', '', 'Saint Vincent and the Grenadines', 'VCT:670');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'PM', 'Saint-Pierrais, Miquelonnais', '1960', '0', '0', '', 'Saint Pierre and Miquelon', 'SPM:666');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SV', 'Salvadoran', '1970', '0', '0', '', 'El Salvador', 'SLV:222');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SM', 'Sammarinese', '1980', '0', '0', '', 'San Marino', 'SMR:674');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'WS', 'Samoan', '1990', '0', '0', '', 'Samoa', 'WSM:882');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'ST', 'Sao Tomean', '2000', '0', '0', '', 'São Tomé and Príncipe', 'STP:678');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SA', 'Saudi Arabian', '2010', '0', '0', '', 'Saudi Arabia', 'SAU:682');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'Scottish', 'Scottish', '2020', '0', '0', '', 'Scotland', '');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SN', 'Senegalese', '2030', '0', '0', '', 'Senegal', 'SEN:686');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'RS', 'Serbian', '2040', '0', '0', '', 'Serbia', 'SRB:688');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SC', 'Seychellois', '2050', '0', '0', '', 'Seychelles', 'SYC:690');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SL', 'Sierra Leonean', '2060', '0', '0', '', 'Sierra Leone', 'SLE:694');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SG', 'Singaporean', '2070', '0', '0', '', 'Singapore', 'SGP:702');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SK', 'Slovak', '2080', '0', '0', '', 'Slovakia', 'SVK:703');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SI', 'Slovene', '2090', '0', '0', '', 'Slovenia', 'SVN:705');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SB', 'Solomon Islander', '2100', '0', '0', '', 'Solomon Islands', 'SLB:090');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SO', 'Somali', '2110', '0', '0', '', 'Somalia', 'SOM:706');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'ZA', 'South African', '2120', '0', '0', '', 'South Africa', 'ZAF:710');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'GS', 'South Georgian South Sandwich Islander', '2130', '0', '0', '', 'South Georgia', 'SGS:239');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'KR', 'South Korean', '2140', '0', '0', '', 'South Korea', 'KOR:410');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SS', 'South Sudanese', '2150', '0', '0', '', 'South Sudan', 'SSD:728');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'ES', 'Spanish', '2160', '0', '0', '', 'Spain', 'ESP:724');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'LK', 'Sri Lankan', '2170', '0', '0', '', 'Sri Lanka', 'LKA:144');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SX', 'St. Maartener', '2180', '0', '0', '', 'Sint Maarten', 'SXM:534');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SD', 'Sudanese', '2190', '0', '0', '', 'Sudan', 'SDN:729');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SR', 'Surinamer', '2200', '0', '0', '', 'Suriname', 'SUR:740');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SZ', 'Swazi', '2210', '0', '0', '', 'Eswatini', 'SWZ:748');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SE', 'Swedish', '2220', '0', '0', '', 'Sweden', 'SWE:752');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'CH', 'Swiss', '2230', '0', '0', '', 'Switzerland', 'CHE:756');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'SY', 'Syrian', '2240', '0', '0', '', 'Syria', 'SYR:760');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TJ', 'Tadzhik', '2250', '0', '0', '', 'Tajikistan', 'TJK:762');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TW', 'Taiwanese', '2260', '0', '0', '', 'Taiwan', 'TWN:158');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TZ', 'Tanzanian', '2270', '0', '0', '', 'Tanzania', 'TZA:834');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TH', 'Thai', '2280', '0', '0', '', 'Thailand', 'THA:764');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TG', 'Togolese', '2290', '0', '0', '', 'Togo', 'TGO:768');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TK', 'Tokelauan', '2300', '0', '0', '', 'Tokelau', 'TKL:772');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TO', 'Tongan', '2310', '0', '0', '', 'Tonga', 'TON:776');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TT', 'Trinidadian', '2320', '0', '0', '', 'Trinidad and Tobago', 'TTO:780');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TN', 'Tunisian', '2330', '0', '0', '', 'Tunisia', 'TUN:788');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TR', 'Turkish', '2340', '0', '0', '', 'Turkey', 'TUR:792');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TM', 'Turkmen', '2350', '0', '0', '', 'Turkmenistan', 'TKM:795');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TC', 'Turks and Caicos Islander', '2360', '0', '0', '', 'Turks and Caicos Islands', 'TCA:796');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'TV', 'Tuvaluan', '2370', '0', '0', '', 'Tuvalu', 'TUV:798');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'UG', 'Ugandan', '2380', '0', '0', '', 'Uganda', 'UGA:800');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'UA', 'Ukrainian', '2390', '0', '0', '', 'Ukraine', 'UKR:804');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'UY', 'Uruguayan', '2400', '0', '0', '', 'Uruguay', 'URY:858');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'UZ', 'Uzbekistani', '2410', '0', '0', '', 'Uzbekistan', 'UZB:860');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'VA', 'Vatican', '2420', '0', '0', '', 'Vatican City', 'VAT:336');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'VE', 'Venezuelan', '2430', '0', '0', '', 'Venezuela', 'VEN:862');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'VN', 'Vietnamese', '2440', '0', '0', '', 'Vietnam', 'VNM:704');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'VG', 'Virgin Islander British', '2450', '0', '0', '', 'British Virgin Islands', 'VGB:092');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'VI', 'Virgin Islander US', '2460', '0', '0', '', 'United States Virgin Islands', 'VIR:850');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'WF', 'Wallis and Futuna Islander', '2470', '0', '0', '', 'Wallis and Futuna', 'WLF:876');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'YE', 'Yemeni', '2480', '0', '0', '', 'Yemen', 'YEM:887');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'ZM', 'Zambian', '2490', '0', '0', '', 'Zambia', 'ZMB:894');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES('nationality_with_country', 'ZW', 'Zimbabwean', '2500', '0', '0', '', 'Zimbabwe', 'ZWE:716');
#EndIf

#IfNotRow categories name Invoices
INSERT INTO categories(`id`,`name`, `value`, `parent`, `lft`, `rght`, `aco_spec`) select (select MAX(id) from categories) + 1, 'Invoices', '', 1, rght, rght + 1, 'encounters|coding' from categories where name = 'Categories';
UPDATE categories SET rght = rght + 2 WHERE name = 'Categories';
UPDATE categories_seq SET id = (select MAX(id) from categories);
#EndIf

#IfNotTable fee_schedule
CREATE TABLE `fee_schedule` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `insurance_company_id` INT(11) NOT NULL DEFAULT 0,
    `plan` VARCHAR(20) DEFAULT '',
    `code` VARCHAR(10) DEFAULT '',
    `modifier` VARCHAR(2) DEFAULT '',
    `type` VARCHAR(20) DEFAULT '',
    `fee` decimal(12,2) DEFAULT NULL,
    `effective_date` date DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ins_plan_code_mod_type_date` (`insurance_company_id`, `plan`, `code`, `modifier`, `type`, `effective_date`)
) ENGINE=InnoDb AUTO_INCREMENT=1;
#EndIf
