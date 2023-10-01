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
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('recent_patient_columns', 'dob', 'Date of Birth', '40');
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
ALTER TABLE `patient_data` ADD `nationality_country` TINYTEXT;
#EndIf


#IfNotRow2D list_options list_id lists option_id Nationality_and_Country
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('lists','Nationality_and_Country','Nationality and Country',307,1,0,'',NULL,'',0,0,1,'',1,'2023-09-24 18:21:13');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AF','Afghan : Afghanistan',10,0,0,'','Afghanistan','4:AFG',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AL','Albanian : Albania',20,0,0,'','Albania','8:ALB',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AQ','Antarctic : Antarctica',30,0,0,'','Antarctica','10:ATA',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','DZ','Algerian : Algeria',40,0,0,'','Algeria','12:DZA',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AS','American Samoan : American Samoa',50,0,0,'','American Samoa','16:ASM',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AD','Andorran : Andorra',60,0,0,'','Andorra','20:AND',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AO','Angolan : Angola',70,0,0,'','Angola','24:AGO',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AG','Antiguan or Barbudan : Antigua and Barbuda',80,0,0,'','Antigua and Barbuda','28:ATG',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AZ','Azerbaijani, Azeri : Azerbaijan',90,0,0,'','Azerbaijan','31:AZE',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AR','Argentine : Argentina',100,0,0,'','Argentina','32:ARG',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AU','Australian : Australia',110,0,0,'','Australia','36:AUS',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AT','Austrian : Austria',120,0,0,'','Austria','40:AUT',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BS','Bahamian : Bahamas',130,0,0,'','Bahamas','44:BHS',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BH','Bahraini : Bahrain',140,0,0,'','Bahrain','48:BHR',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BD','Bangladeshi : Bangladesh',150,0,0,'','Bangladesh','50:BGD',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AM','Armenian : Armenia',160,0,0,'','Armenia','51:ARM',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BB','Barbadian : Barbados',170,0,0,'','Barbados','52:BRB',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BE','Belgian : Belgium',180,0,0,'','Belgium','56:BEL',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BM','Bermudian, Bermudan : Bermuda',190,0,0,'','Bermuda','60:BMU',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BT','Bhutanese : Bhutan',200,0,0,'','Bhutan','64:BTN',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BO','Bolivian : Bolivia (Plurinational State of)',210,0,0,'','Bolivia (Plurinational State of)','68:BOL',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BA','Bosnian or Herzegovinian : Bosnia and Herzegovina',220,0,0,'','Bosnia and Herzegovina','70:BIH',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BW','Motswana, Botswanan : Botswana',230,0,0,'','Botswana','72:BWA',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BV','Bouvet Island : Bouvet Island',240,0,0,'','Bouvet Island','74:BVT',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BR','Brazilian : Brazil',250,0,0,'','Brazil','76:BRA',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BZ','Belizean : Belize',260,0,0,'','Belize','84:BLZ',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','IO','BIOT : British Indian Ocean Territory',270,0,0,'','British Indian Ocean Territory','86:IOT',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SB','Solomon Island : Solomon Islands',280,0,0,'','Solomon Islands','90:SLB',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','VG','British Virgin Island : Virgin Islands (British)',290,0,0,'','Virgin Islands (British)','92:VGB',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BN','Bruneian : Brunei Darussalam',300,0,0,'','Brunei Darussalam','96:BRN',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BG','Bulgarian : Bulgaria',310,0,0,'','Bulgaria','100:BGR',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MM','Burmese : Myanmar',320,0,0,'','Myanmar','104:MMR',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BI','Burundian : Burundi',330,0,0,'','Burundi','108:BDI',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BY','Belarusian : Belarus',340,0,0,'','Belarus','112:BLR',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','KH','Cambodian : Cambodia',350,0,0,'','Cambodia','116:KHM',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CM','Cameroonian : Cameroon',360,0,0,'','Cameroon','120:CMR',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CA','Canadian : Canada',370,0,0,'','Canada','124:CAN',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CV','Cabo Verdean : Cabo Verde',380,0,0,'','Cabo Verde','132:CPV',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','KY','Caymanian : Cayman Islands',390,0,0,'','Cayman Islands','136:CYM',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CF','Central African : Central African Republic',400,0,0,'','Central African Republic','140:CAF',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','LK','Sri Lankan : Sri Lanka',410,0,0,'','Sri Lanka','144:LKA',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TD','Chadian : Chad',420,0,0,'','Chad','148:TCD',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CL','Chilean : Chile',430,0,0,'','Chile','152:CHL',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CN','Chinese : China',440,0,0,'','China','156:CHN',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TW','Chinese, Taiwanese : Taiwan, Province of China',450,0,0,'','Taiwan, Province of China','158:TWN',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CX','Christmas Island : Christmas Island',460,0,0,'','Christmas Island','162:CXR',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CC','Cocos Island : Cocos (Keeling) Islands',470,0,0,'','Cocos (Keeling) Islands','166:CCK',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CO','Colombian : Colombia',480,0,0,'','Colombia','170:COL',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','KM','Comoran, Comorian : Comoros',490,0,0,'','Comoros','174:COM',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','YT','Mahoran : Mayotte',500,0,0,'','Mayotte','175:MYT',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CG','Congolese : Congo (Republic of the)',510,0,0,'','Congo (Republic of the)','178:COG',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CD','Congolese : Congo (Democratic Republic of the)',520,0,0,'','Congo (Democratic Republic of the)','180:COD',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CK','Cook Island : Cook Islands',530,0,0,'','Cook Islands','184:COK',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CR','Costa Rican : Costa Rica',540,0,0,'','Costa Rica','188:CRI',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','HR','Croatian : Croatia',550,0,0,'','Croatia','191:HRV',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CU','Cuban : Cuba',560,0,0,'','Cuba','192:CUB',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CY','Cypriot : Cyprus',570,0,0,'','Cyprus','196:CYP',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CZ','Czech : Czech Republic',580,0,0,'','Czech Republic','203:CZE',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BJ','Beninese, Beninois : Benin',590,0,0,'','Benin','204:BEN',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','DK','Danish : Denmark',600,0,0,'','Denmark','208:DNK',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','DM','Dominican : Dominica',610,0,0,'','Dominica','212:DMA',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','DO','Dominican : Dominican Republic',620,0,0,'','Dominican Republic','214:DOM',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','EC','Ecuadorian : Ecuador',630,0,0,'','Ecuador','218:ECU',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SV','Salvadoran : El Salvador',640,0,0,'','El Salvador','222:SLV',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GQ','Equatorial Guinean, Equatoguinean : Equatorial Guinea',650,0,0,'','Equatorial Guinea','226:GNQ',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','ET','Ethiopian : Ethiopia',660,0,0,'','Ethiopia','231:ETH',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','ER','Eritrean : Eritrea',670,0,0,'','Eritrea','232:ERI',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','EE','Estonian : Estonia',680,0,0,'','Estonia','233:EST',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','FO','Faroese : Faroe Islands',690,0,0,'','Faroe Islands','234:FRO',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','FK','Falkland Island : Falkland Islands (Malvinas)',700,0,0,'','Falkland Islands (Malvinas)','238:FLK',0,0,1,'',1,'2023-09-24 21:07:24');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GS','South Georgia or South Sandwich Islands : South Georgia and the South Sandwich Islands',710,0,0,'','South Georgia and the South Sandwich Islands','239:SGS',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','FJ','Fijian : Fiji',720,0,0,'','Fiji','242:FJI',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','FI','Finnish : Finland',730,0,0,'','Finland','246:FIN',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AX','Åland Island : Åland Islands',740,0,0,'','Åland Islands','248:ALA',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','FR','French : France',750,0,0,'','France','250:FRA',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GF','French Guianese : French Guiana',760,0,0,'','French Guiana','254:GUF',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PF','French Polynesian : French Polynesia',770,0,0,'','French Polynesia','258:PYF',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TF','French Southern Territories : French Southern Territories',780,0,0,'','French Southern Territories','260:ATF',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','DJ','Djiboutian : Djibouti',790,0,0,'','Djibouti','262:DJI',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GA','Gabonese : Gabon',800,0,0,'','Gabon','266:GAB',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GE','Georgian : Georgia',810,0,0,'','Georgia','268:GEO',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GM','Gambian : Gambia',820,0,0,'','Gambia','270:GMB',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PS','Palestinian : Palestine, State of',830,0,0,'','Palestine, State of','275:PSE',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','DE','German : Germany',840,0,0,'','Germany','276:DEU',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GH','Ghanaian : Ghana',850,0,0,'','Ghana','288:GHA',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GI','Gibraltar : Gibraltar',860,0,0,'','Gibraltar','292:GIB',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','KI','I-Kiribati : Kiribati',870,0,0,'','Kiribati','296:KIR',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GR','Greek, Hellenic : Greece',880,0,0,'','Greece','300:GRC',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GL','Greenlandic : Greenland',890,0,0,'','Greenland','304:GRL',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GD','Grenadian : Grenada',900,0,0,'','Grenada','308:GRD',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GP','Guadeloupe : Guadeloupe',910,0,0,'','Guadeloupe','312:GLP',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GU','Guamanian, Guambat : Guam',920,0,0,'','Guam','316:GUM',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GT','Guatemalan : Guatemala',930,0,0,'','Guatemala','320:GTM',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GN','Guinean : Guinea',940,0,0,'','Guinea','324:GIN',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GY','Guyanese : Guyana',950,0,0,'','Guyana','328:GUY',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','HT','Haitian : Haiti',960,0,0,'','Haiti','332:HTI',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','HM','Heard Island or McDonald Islands : Heard Island and McDonald Islands',970,0,0,'','Heard Island and McDonald Islands','334:HMD',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','VA','Vatican : Vatican City State',980,0,0,'','Vatican City State','336:VAT',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','HN','Honduran : Honduras',990,0,0,'','Honduras','340:HND',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','HK','Hong Kong, Hong Kongese : Hong Kong',1000,0,0,'','Hong Kong','344:HKG',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','HU','Hungarian, Magyar : Hungary',1010,0,0,'','Hungary','348:HUN',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','IS','Icelandic : Iceland',1020,0,0,'','Iceland','352:ISL',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','IN','Indian : India',1030,0,0,'','India','356:IND',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','ID','Indonesian : Indonesia',1040,0,0,'','Indonesia','360:IDN',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','IR','Iranian, Persian : Iran',1050,0,0,'','Iran','364:IRN',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','IQ','Iraqi : Iraq',1060,0,0,'','Iraq','368:IRQ',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','IE','Irish : Ireland',1070,0,0,'','Ireland','372:IRL',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','IL','Israeli : Israel',1080,0,0,'','Israel','376:ISR',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','IT','Italian : Italy',1090,0,0,'','Italy','380:ITA',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CI','Ivorian : Côte d\'Ivoire',1100,0,0,'','Côte d\'Ivoire','384:CIV',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','JM','Jamaican : Jamaica',1110,0,0,'','Jamaica','388:JAM',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','JP','Japanese : Japan',1120,0,0,'','Japan','392:JPN',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','KZ','Kazakhstani, Kazakh : Kazakhstan',1130,0,0,'','Kazakhstan','398:KAZ',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','JO','Jordanian : Jordan',1140,0,0,'','Jordan','400:JOR',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','KE','Kenyan : Kenya',1150,0,0,'','Kenya','404:KEN',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','KP','North Korean : Korea (Democratic People\'s Republic of)',1160,0,0,'','Korea (Democratic People\'s Republic of)','408:PRK',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','KR','South Korean : Korea (Republic of)',1170,0,0,'','Korea (Republic of)','410:KOR',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','KW','Kuwaiti : Kuwait',1180,0,0,'','Kuwait','414:KWT',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','KG','Kyrgyzstani, Kyrgyz, Kirgiz, Kirghiz : Kyrgyzstan',1190,0,0,'','Kyrgyzstan','417:KGZ',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','LA','Lao, Laotian : Lao People\'s Democratic Republic',1200,0,0,'','Lao People\'s Democratic Republic','418:LAO',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','LB','Lebanese : Lebanon',1210,0,0,'','Lebanon','422:LBN',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','LS','Basotho : Lesotho',1220,0,0,'','Lesotho','426:LSO',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','LV','Latvian : Latvia',1230,0,0,'','Latvia','428:LVA',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','LR','Liberian : Liberia',1240,0,0,'','Liberia','430:LBR',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','LY','Libyan : Libya',1250,0,0,'','Libya','434:LBY',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','LI','Liechtenstein : Liechtenstein',1260,0,0,'','Liechtenstein','438:LIE',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','LT','Lithuanian : Lithuania',1270,0,0,'','Lithuania','440:LTU',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','LU','Luxembourg, Luxembourgish : Luxembourg',1280,0,0,'','Luxembourg','442:LUX',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MO','Macanese, Chinese : Macao',1290,0,0,'','Macao','446:MAC',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MG','Malagasy : Madagascar',1300,0,0,'','Madagascar','450:MDG',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MW','Malawian : Malawi',1310,0,0,'','Malawi','454:MWI',0,0,1,'',1,'2023-09-24 21:07:25');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MY','Malaysian : Malaysia',1320,0,0,'','Malaysia','458:MYS',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MV','Maldivian : Maldives',1330,0,0,'','Maldives','462:MDV',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','ML','Malian, Malinese : Mali',1340,0,0,'','Mali','466:MLI',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MT','Maltese : Malta',1350,0,0,'','Malta','470:MLT',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MQ','Martiniquais, Martinican : Martinique',1360,0,0,'','Martinique','474:MTQ',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MR','Mauritanian : Mauritania',1370,0,0,'','Mauritania','478:MRT',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MU','Mauritian : Mauritius',1380,0,0,'','Mauritius','480:MUS',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MX','Mexican : Mexico',1390,0,0,'','Mexico','484:MEX',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MC','Monégasque, Monacan : Monaco',1400,0,0,'','Monaco','492:MCO',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MN','Mongolian : Mongolia',1410,0,0,'','Mongolia','496:MNG',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MD','Moldovan : Moldova (Republic of)',1420,0,0,'','Moldova (Republic of)','498:MDA',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','ME','Montenegrin : Montenegro',1430,0,0,'','Montenegro','499:MNE',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MS','Montserratian : Montserrat',1440,0,0,'','Montserrat','500:MSR',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MA','Moroccan : Morocco',1450,0,0,'','Morocco','504:MAR',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MZ','Mozambican : Mozambique',1460,0,0,'','Mozambique','508:MOZ',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','OM','Omani : Oman',1470,0,0,'','Oman','512:OMN',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NA','Namibian : Namibia',1480,0,0,'','Namibia','516:NAM',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NR','Nauruan : Nauru',1490,0,0,'','Nauru','520:NRU',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NP','Nepali, Nepalese : Nepal',1500,0,0,'','Nepal','524:NPL',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NL','Dutch, Netherlandic : Netherlands',1510,0,0,'','Netherlands','528:NLD',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CW','Curaçaoan : Curaçao',1520,0,0,'','Curaçao','531:CUW',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AW','Aruban : Aruba',1530,0,0,'','Aruba','533:ABW',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SX','Sint Maarten : Sint Maarten (Dutch part)',1540,0,0,'','Sint Maarten (Dutch part)','534:SXM',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BQ','Bonaire : Bonaire, Sint Eustatius and Saba',1550,0,0,'','Bonaire, Sint Eustatius and Saba','535:BES',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NC','New Caledonian : New Caledonia',1560,0,0,'','New Caledonia','540:NCL',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','VU','Ni-Vanuatu, Vanuatuan : Vanuatu',1570,0,0,'','Vanuatu','548:VUT',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NZ','New Zealand, NZ : New Zealand',1580,0,0,'','New Zealand','554:NZL',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NI','Nicaraguan : Nicaragua',1590,0,0,'','Nicaragua','558:NIC',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NE','Nigerien : Niger',1600,0,0,'','Niger','562:NER',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NG','Nigerian : Nigeria',1610,0,0,'','Nigeria','566:NGA',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NU','Niuean : Niue',1620,0,0,'','Niue','570:NIU',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NF','Norfolk Island : Norfolk Island',1630,0,0,'','Norfolk Island','574:NFK',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','NO','Norwegian : Norway',1640,0,0,'','Norway','578:NOR',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MP','Northern Marianan : Northern Mariana Islands',1650,0,0,'','Northern Mariana Islands','580:MNP',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','UM','American : United States Minor Outlying Islands',1660,0,0,'','United States Minor Outlying Islands','581:UMI',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','FM','Micronesian : Micronesia (Federated States of)',1670,0,0,'','Micronesia (Federated States of)','583:FSM',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MH','Marshallese : Marshall Islands',1680,0,0,'','Marshall Islands','584:MHL',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PW','Palauan : Palau',1690,0,0,'','Palau','585:PLW',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PK','Pakistani : Pakistan',1700,0,0,'','Pakistan','586:PAK',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PA','Panamanian : Panama',1710,0,0,'','Panama','591:PAN',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PG','Papua New Guinean, Papuan : Papua New Guinea',1720,0,0,'','Papua New Guinea','598:PNG',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PY','Paraguayan : Paraguay',1730,0,0,'','Paraguay','600:PRY',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PE','Peruvian : Peru',1740,0,0,'','Peru','604:PER',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PH','Philippine, Filipino : Philippines',1750,0,0,'','Philippines','608:PHL',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PN','Pitcairn Island : Pitcairn',1760,0,0,'','Pitcairn','612:PCN',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PL','Polish : Poland',1770,0,0,'','Poland','616:POL',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PT','Portuguese : Portugal',1780,0,0,'','Portugal','620:PRT',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GW','Bissau-Guinean : Guinea-Bissau',1790,0,0,'','Guinea-Bissau','624:GNB',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TL','Timorese : Timor-Leste',1800,0,0,'','Timor-Leste','626:TLS',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PR','Puerto Rican : Puerto Rico',1810,0,0,'','Puerto Rico','630:PRI',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','QA','Qatari : Qatar',1820,0,0,'','Qatar','634:QAT',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','RE','Réunionese, Réunionnais : Réunion',1830,0,0,'','Réunion','638:REU',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','RO','Romanian : Romania',1840,0,0,'','Romania','642:ROU',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','RU','Russian : Russian Federation',1850,0,0,'','Russian Federation','643:RUS',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','RW','Rwandan : Rwanda',1860,0,0,'','Rwanda','646:RWA',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BL','Barthélemois : Saint Barthélemy',1870,0,0,'','Saint Barthélemy','652:BLM',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SH','Saint Helenian : Saint Helena, Ascension and Tristan da Cunha',1880,0,0,'','Saint Helena, Ascension and Tristan da Cunha','654:SHN',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','KN','Kittitian or Nevisian : Saint Kitts and Nevis',1890,0,0,'','Saint Kitts and Nevis','659:KNA',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AI','Anguillan : Anguilla',1900,0,0,'','Anguilla','660:AIA',0,0,1,'',1,'2023-09-24 21:07:26');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','LC','Saint Lucian : Saint Lucia',1910,0,0,'','Saint Lucia','662:LCA',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MF','Saint-Martinoise : Saint Martin (French part)',1920,0,0,'','Saint Martin (French part)','663:MAF',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','PM','Saint-Pierrais or Miquelonnais : Saint Pierre and Miquelon',1930,0,0,'','Saint Pierre and Miquelon','666:SPM',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','VC','Saint Vincentian, Vincentian : Saint Vincent and the Grenadines',1940,0,0,'','Saint Vincent and the Grenadines','670:VCT',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SM','Sammarinese : San Marino',1950,0,0,'','San Marino','674:SMR',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','ST','São Toméan : Sao Tome and Principe',1960,0,0,'','Sao Tome and Principe','678:STP',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SA','Saudi, Saudi Arabian : Saudi Arabia',1970,0,0,'','Saudi Arabia','682:SAU',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SN','Senegalese : Senegal',1980,0,0,'','Senegal','686:SEN',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','RS','Serbian : Serbia',1990,0,0,'','Serbia','688:SRB',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SC','Seychellois : Seychelles',2000,0,0,'','Seychelles','690:SYC',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SL','Sierra Leonean : Sierra Leone',2010,0,0,'','Sierra Leone','694:SLE',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SG','Singaporean : Singapore',2020,0,0,'','Singapore','702:SGP',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SK','Slovak : Slovakia',2030,0,0,'','Slovakia','703:SVK',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','VN','Vietnamese : Vietnam',2040,0,0,'','Vietnam','704:VNM',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SI','Slovenian, Slovene : Slovenia',2050,0,0,'','Slovenia','705:SVN',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SO','Somali, Somalian : Somalia',2060,0,0,'','Somalia','706:SOM',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','ZA','South African : South Africa',2070,0,0,'','South Africa','710:ZAF',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','ZW','Zimbabwean : Zimbabwe',2080,0,0,'','Zimbabwe','716:ZWE',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','ES','Spanish : Spain',2090,0,0,'','Spain','724:ESP',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SS','South Sudanese : South Sudan',2100,0,0,'','South Sudan','728:SSD',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SD','Sudanese : Sudan',2110,0,0,'','Sudan','729:SDN',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','EH','Sahrawi, Sahrawian, Sahraouian : Western Sahara',2120,0,0,'','Western Sahara','732:ESH',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SR','Surinamese : Suriname',2130,0,0,'','Suriname','740:SUR',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SJ','Svalbard : Svalbard and Jan Mayen',2140,0,0,'','Svalbard and Jan Mayen','744:SJM',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SZ','Swazi : Swaziland',2150,0,0,'','Swaziland','748:SWZ',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SE','Swedish : Sweden',2160,0,0,'','Sweden','752:SWE',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','CH','Swiss : Switzerland',2170,0,0,'','Switzerland','756:CHE',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','SY','Syrian : Syrian Arab Republic',2180,0,0,'','Syrian Arab Republic','760:SYR',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TJ','Tajikistani : Tajikistan',2190,0,0,'','Tajikistan','762:TJK',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TH','Thai : Thailand',2200,0,0,'','Thailand','764:THA',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TG','Togolese : Togo',2210,0,0,'','Togo','768:TGO',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TK','Tokelauan : Tokelau',2220,0,0,'','Tokelau','772:TKL',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TO','Tongan : Tonga',2230,0,0,'','Tonga','776:TON',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TT','Trinidadian or Tobagonian : Trinidad and Tobago',2240,0,0,'','Trinidad and Tobago','780:TTO',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','AE','Emirati, Emirian, Emiri : United Arab Emirates',2250,0,0,'','United Arab Emirates','784:ARE',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TN','Tunisian : Tunisia',2260,0,0,'','Tunisia','788:TUN',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TR','Turkish : Turkey',2270,0,0,'','Turkey','792:TUR',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TM','Turkmen : Turkmenistan',2280,0,0,'','Turkmenistan','795:TKM',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TC','Turks and Caicos Island : Turks and Caicos Islands',2290,0,0,'','Turks and Caicos Islands','796:TCA',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TV','Tuvaluan : Tuvalu',2300,0,0,'','Tuvalu','798:TUV',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','UG','Ugandan : Uganda',2310,0,0,'','Uganda','800:UGA',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','UA','Ukrainian : Ukraine',2320,0,0,'','Ukraine','804:UKR',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','MK','Macedonian : Macedonia (the former Yugoslav Republic of)',2330,0,0,'','Macedonia (the former Yugoslav Republic of)','807:MKD',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','EG','Egyptian : Egypt',2340,0,0,'','Egypt','818:EGY',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GB','British, UK : United Kingdom of Great Britain and Northern Ireland',2350,0,0,'','United Kingdom of Great Britain and Northern Ireland','826:GBR',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','GG','Channel Island : Guernsey',2360,0,0,'','Guernsey','831:GGY',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','JE','Channel Island : Jersey',2370,0,0,'','Jersey','832:JEY',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','IM','Manx : Isle of Man',2380,0,0,'','Isle of Man','833:IMN',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','TZ','Tanzanian : Tanzania, United Republic of',2390,0,0,'','Tanzania, United Republic of','834:TZA',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','US','American : United States of America',2400,0,0,'','United States of America','840:USA',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','VI','U.S. Virgin Island : Virgin Islands (U.S.)',2410,0,0,'','Virgin Islands (U.S.)','850:VIR',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','BF','Burkinabé : Burkina Faso',2420,0,0,'','Burkina Faso','854:BFA',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','UY','Uruguayan : Uruguay',2430,0,0,'','Uruguay','858:URY',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','UZ','Uzbekistani, Uzbek : Uzbekistan',2440,0,0,'','Uzbekistan','860:UZB',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','VE','Venezuelan : Venezuela (Bolivarian Republic of)',2450,0,0,'','Venezuela (Bolivarian Republic of)','862:VEN',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','WF','Wallis and Futuna, Wallisian or Futunan : Wallis and Futuna',2460,0,0,'','Wallis and Futuna','876:WLF',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','WS','Samoan : Samoa',2470,0,0,'','Samoa','882:WSM',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','YE','Yemeni : Yemen',2480,0,0,'','Yemen','887:YEM',0,0,1,'',1,'2023-09-24 21:07:27');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES ('Nationality_and_Country','ZM','Zambian : Zambia',2490,0,0,'','Zambia','894:ZMB',0,0,1,'',1,'2023-09-24 21:07:27');
#EndIf

#IfNotTable edi_sequences
CREATE TABLE `edi_sequences` (
    `id` int(9) unsigned NOT NULL default '0'
) ENGINE=InnoDB;
#EndIf