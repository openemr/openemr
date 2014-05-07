--
--  Comment Meta Language Constructs:
--
--  #IfNotTable
--    argument: table_name
--    behavior: if the table_name does not exist,  the block will be executed

--  #IfTable
--    argument: table_name
--    behavior: if the table_name does exist, the block will be executed

--  #IfMissingColumn
--    arguments: table_name colname
--    behavior:  if the table exists but the column does not,  the block will be executed

--  #IfNotColumnType
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a column colname with a data type equal to value, then the block will be executed

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

--  #IfRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfRow3D
--	  arguments: table_name colname value colname2 value2 colname3 value3
--	  behavior:  If the table table_name does have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

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

#IfNotRow4D supported_external_dataloads load_type ICD9 load_source CMS load_release_date 2013-10-01 load_filename cmsv31-master-descriptions.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD9', 'CMS', '2013-10-01', 'cmsv31-master-descriptions.zip', 'fe0d7f9a5338f5ff187683b4737ad2b7');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2012-10-01 load_filename 2013_PCS_long_and_abbreviated_titles.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2012-10-01', '2013_PCS_long_and_abbreviated_titles.zip', '04458ed0631c2c122624ee0a4ca1c475');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2012-10-01 load_filename 2013-DiagnosisGEMs.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2012-10-01', '2013-DiagnosisGEMs.zip', '773aac2a675d6aefd1d7dd149883be51');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2012-10-01 load_filename ICD10CMOrderFiles_2013.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2012-10-01', 'ICD10CMOrderFiles_2013.zip', '1c175a858f833485ef8f9d3e66b4d8bd');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2012-10-01 load_filename ProcedureGEMs_2013.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2012-10-01', 'ProcedureGEMs_2013.zip', '92aa7640e5ce29b9629728f7d4fc81db');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2012-10-01 load_filename 2013-ReimbursementMapping_dx.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2012-10-01', '2013-ReimbursementMapping_dx.zip', '0d5d36e3f4519bbba08a9508576787fb');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2012-10-01 load_filename ReimbursementMapping_pr_2013.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2012-10-01', 'ReimbursementMapping_pr_2013.zip', '4c3920fedbcd9f6af54a1dc9069a11ca');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2013-10-01 load_filename 2014-PCS-long-and-abbreviated-titles.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2013-10-01', '2014-PCS-long-and-abbreviated-titles.zip', '2d03514a0c66d92cf022a0bc28c83d38');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2013-10-01 load_filename DiagnosisGEMs-2014.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2013-10-01', 'DiagnosisGEMs-2014.zip', '3ed7b7c5a11c766102b12d97d777a11b');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2013-10-01 load_filename 2014-ICD10-Code-Descriptions.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2013-10-01', '2014-ICD10-Code-Descriptions.zip', '5458b95f6f37228b5cdfa03aefc6c8bb');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2013-10-01 load_filename ProcedureGEMs-2014.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2013-10-01', 'ProcedureGEMs-2014.zip', 'be46de29f4f40f97315d04821273acf9');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2013-10-01 load_filename 2014-Reimbursement-Mappings-DX.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2013-10-01', '2014-Reimbursement-Mappings-DX.zip', '614b3957304208e3ef7d3ba8b3618888');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2013-10-01 load_filename 2014-Reimbursement-Mappings-PR.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2013-10-01', '2014-Reimbursement-Mappings-PR.zip', 'f306a0e8c9edb34d28fd6ce8af82b646');
#EndIf

#IfMissingColumn patient_data email_direct
ALTER TABLE `patient_data` ADD COLUMN `email_direct` varchar(255) NOT NULL default '';
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES('DEM', 'email_direct', '2Contact', 'Trusted Email', 14, 2, 1, 30, 95, '', 1, 1, '', '', 'Trusted (Direct) Email Address', 0);
#EndIf

#IfMissingColumn users email_direct
ALTER TABLE `users` ADD COLUMN `email_direct` varchar(255) NOT NULL default '';
#EndIf

#IfNotTable erx_ttl_touch
CREATE TABLE `erx_ttl_touch` (
  `patient_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Patient record Id', 
  `process` ENUM('allergies','medications') NOT NULL COMMENT 'NewCrop eRx SOAP process',
  `updated` DATETIME NOT NULL COMMENT 'Date and time of last process update for patient', 
  PRIMARY KEY (`patient_id`, `process`) ) 
ENGINE = InnoDB COMMENT = 'Store records last update per patient data process';
#EndIf

#IfMissingColumn form_misc_billing_options box_14_date_qual
ALTER TABLE `form_misc_billing_options` 
ADD COLUMN `box_14_date_qual` CHAR(3) NULL DEFAULT NULL;
#EndIf

#IfMissingColumn form_misc_billing_options box_15_date_qual
ALTER TABLE `form_misc_billing_options` 
ADD COLUMN `box_15_date_qual` CHAR(3) NULL DEFAULT NULL;
#EndIf

#IfNotTable esign_signatures
CREATE TABLE `esign_signatures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL COMMENT 'Table row ID for signature',
  `table` varchar(255) NOT NULL COMMENT 'table name for the signature',
  `uid` int(11) NOT NULL COMMENT 'user id for the signing user',
  `datetime` datetime NOT NULL COMMENT 'datetime of the signature action',
  `is_lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'sig, lock or amendment',
  `amendment` text COMMENT 'amendment text, if any',
  `hash` varchar(255) NOT NULL COMMENT 'hash of signed data',
  `signature_hash` varchar(255) NOT NULL COMMENT 'hash of signature itself',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `table` (`table`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;
#EndIf

#IfMissingColumn layout_options list_backup_id
ALTER TABLE `layout_options` ADD COLUMN `list_backup_id` VARCHAR(31) NOT NULL DEFAULT '';
UPDATE `layout_options` SET `list_backup_id` = 'ethrace' WHERE `layout_options`.`form_id` = 'DEM' AND `layout_options`.`field_id` = 'ethnicity';
UPDATE `layout_options` SET `list_backup_id` = 'ethrace' WHERE `layout_options`.`form_id` = 'DEM' AND `layout_options`.`field_id` = 'race';
#EndIf

UPDATE `layout_options` SET `data_type` = '36' WHERE `layout_options`.`form_id` = 'DEM' AND `layout_options`.`field_id` = 'race';
UPDATE `layout_options` SET `data_type` = '1', `datacols` = '3' WHERE `layout_options`.`form_id` = 'DEM' AND `layout_options`.`field_id` = 'language';

#IfNotTable modules
CREATE TABLE `modules` (
  `mod_id` INT(11) NOT NULL AUTO_INCREMENT,
  `mod_name` VARCHAR(64) NOT NULL DEFAULT '0',
  `mod_directory` VARCHAR(64) NOT NULL DEFAULT '',
  `mod_parent` VARCHAR(64) NOT NULL DEFAULT '',
  `mod_type` VARCHAR(64) NOT NULL DEFAULT '',
  `mod_active` INT(1) UNSIGNED NOT NULL DEFAULT '0',
  `mod_ui_name` VARCHAR(20) NOT NULL DEFAULT '''',
  `mod_relative_link` VARCHAR(64) NOT NULL DEFAULT '',
  `mod_ui_order` TINYINT(3) NOT NULL DEFAULT '0',
  `mod_ui_active` INT(1) UNSIGNED NOT NULL DEFAULT '0',
  `mod_description` VARCHAR(255) NOT NULL DEFAULT '',
  `mod_nick_name` VARCHAR(25) NOT NULL DEFAULT '',
  `mod_enc_menu` VARCHAR(10) NOT NULL DEFAULT 'no',
  `permissions_item_table` CHAR(100) DEFAULT NULL,
  `directory` VARCHAR(255) NOT NULL,
  `date` DATETIME NOT NULL,
  `sql_run` TINYINT(4) DEFAULT '0',
  `type` TINYINT(4) DEFAULT '0',
  PRIMARY KEY (`mod_id`,`mod_directory`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable module_acl_group_settings
CREATE TABLE `module_acl_group_settings` (
  `module_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `allowed` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`module_id`,`group_id`,`section_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable module_acl_sections
CREATE TABLE `module_acl_sections` (
  `section_id` int(11) DEFAULT NULL,
  `section_name` varchar(255) DEFAULT NULL,
  `parent_section` int(11) DEFAULT NULL,
  `section_identifier` varchar(50) DEFAULT NULL,
  `module_id` int(11) DEFAULT NULL
) ENGINE=InnoDB;
#EndIf

#IfNotTable module_acl_user_settings
CREATE TABLE `module_acl_user_settings` (
  `module_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `allowed` int(1) DEFAULT NULL,
  PRIMARY KEY (`module_id`,`user_id`,`section_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable module_configuration
CREATE TABLE `module_configuration` (
  `module_config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int(10) unsigned NOT NULL,
  `field_name` varchar(45) NOT NULL,
  `field_value` varchar(255) NOT NULL,
  PRIMARY KEY (`module_config_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable modules_hooks_settings
CREATE TABLE `modules_hooks_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mod_id` int(11) DEFAULT NULL,
  `enabled_hooks` varchar(255) DEFAULT NULL,
  `attached_to` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable modules_settings
CREATE TABLE `modules_settings` (
  `mod_id` INT(11) DEFAULT NULL,
  `fld_type` SMALLINT(6) DEFAULT NULL COMMENT '1=>ACL,2=>preferences,3=>hooks',
  `obj_name` VARCHAR(255) DEFAULT NULL,
  `menu_name` VARCHAR(255) DEFAULT NULL,
  `path` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;
#EndIf

#IfNotRow2D list_options list_id lists option_id insurance_types
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('lists','insurance_types','Insurance Types',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('insurance_types','primary'  ,'Primary'  ,10);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('insurance_types','secondary','Secondary',20);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('insurance_types','tertiary' ,'Tertiary' ,30);
#EndIf

#IfMissingColumn patient_data cmsportal_login
ALTER TABLE `patient_data` ADD COLUMN `cmsportal_login` varchar(60) NOT NULL default '';
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES
  ('DEM', 'cmsportal_login', '3Choices', 'CMS Portal Login', 15, 2, 1, 30, 60, '', 1, 1, '', '', 'Login ID for the CMS Patient Portal', 0);
#EndIf

#IfNotColumnType procedure_order control_id varchar(255)
ALTER TABLE `procedure_order` CHANGE `control_id`
  `control_id` varchar(255) NOT NULL DEFAULT '' COMMENT 'This is the CONTROL ID that is sent back from lab';
#EndIf

#IfMissingColumn procedure_providers direction
ALTER TABLE `procedure_providers`
ADD COLUMN `direction` char(1) NOT NULL DEFAULT 'B' COMMENT 'Bidirectional or Results-only';
#EndIf

#IfNotColumnType billing units int(11)
  ALTER TABLE `billing` CHANGE `units` `units` int(11) DEFAULT NULL;
#EndIf

#IfNotColumnType codes units int(11)
  ALTER TABLE `codes`   CHANGE `units` `units` int(11) DEFAULT NULL;
#EndIf

#IfNotRow2D list_options list_id language option_id declne_to_specfy
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'declne_to_specfy', 'Declined To Specify', 0, 0, 0);
#EndIf

#IfNotRow2D list_options list_id ethrace option_id declne_to_specfy
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('ethrace', 'declne_to_specfy', 'Declined To Specify', 0, 0, 0);
#EndIf

#IfNotRow2D list_options list_id race option_id declne_to_specfy
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('race', 'declne_to_specfy', 'Declined To Specify', 0, 0, 0);
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id abkhazian title Abkhazian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'abkhazian', 'Abkhazian', 10, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id abkhazian
UPDATE `list_options` SET `notes` = 'abk' WHERE `option_id` = 'abkhazian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Abkhazian
UPDATE `list_options` SET `notes` = 'abk' WHERE `title` = 'Abkhazian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id afar title Afar
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'afar', 'Afar', 20, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id afar
UPDATE `list_options` SET `notes` = 'aar' WHERE `option_id` = 'afar' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Afar
UPDATE `list_options` SET `notes` = 'aar' WHERE `title` = 'Afar' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id afrikaans title Afrikaans
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'afrikaans', 'Afrikaans', 30, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id afrikaans
UPDATE `list_options` SET `notes` = 'afr' WHERE `option_id` = 'afrikaans' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Afrikaans
UPDATE `list_options` SET `notes` = 'afr' WHERE `title` = 'Afrikaans' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id akan title Akan
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'akan', 'Akan', 40, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id akan
UPDATE `list_options` SET `notes` = 'aka' WHERE `option_id` = 'akan' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Akan
UPDATE `list_options` SET `notes` = 'aka' WHERE `title` = 'Akan' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id albanian title Albanian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'albanian', 'Albanian', 50, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id albanian
UPDATE `list_options` SET `notes` = 'alb(B)|sqi(T)' WHERE `option_id` = 'albanian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Albanian
UPDATE `list_options` SET `notes` = 'alb(B)|sqi(T)' WHERE `title` = 'Albanian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id amharic title Amharic
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'amharic', 'Amharic', 60, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id amharic
UPDATE `list_options` SET `notes` = 'amh' WHERE `option_id` = 'amharic' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Amharic
UPDATE `list_options` SET `notes` = 'amh' WHERE `title` = 'Amharic' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id arabic title Arabic
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'arabic', 'Arabic', 70, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id arabic
UPDATE `list_options` SET `notes` = 'ara' WHERE `option_id` = 'arabic' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Arabic
UPDATE `list_options` SET `notes` = 'ara' WHERE `title` = 'Arabic' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id aragonese title Aragonese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'aragonese', 'Aragonese', 80, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id aragonese
UPDATE `list_options` SET `notes` = 'arg' WHERE `option_id` = 'aragonese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Aragonese
UPDATE `list_options` SET `notes` = 'arg' WHERE `title` = 'Aragonese' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id armenian title Armenian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'armenian', 'Armenian', 90, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id armenian
UPDATE `list_options` SET `notes` = 'arm(B)|hye(T)' WHERE `option_id` = 'armenian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Armenian
UPDATE `list_options` SET `notes` = 'arm(B)|hye(T)' WHERE `title` = 'Armenian' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 10 option_id armenian
UPDATE `list_options` SET `seq` = 90 WHERE `option_id` = 'armenian' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 10 title Armenian
UPDATE `list_options` SET `seq` = 90 WHERE `title` = 'Armenian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id assamese title Assamese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'assamese', 'Assamese', 100, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id assamese
UPDATE `list_options` SET `notes` = 'asm' WHERE `option_id` = 'assamese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Assamese
UPDATE `list_options` SET `notes` = 'asm' WHERE `title` = 'Assamese' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id avaric title Avaric
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'avaric', 'Avaric', 110, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id avaric
UPDATE `list_options` SET `notes` = 'ava' WHERE `option_id` = 'avaric' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Avaric
UPDATE `list_options` SET `notes` = 'ava' WHERE `title` = 'Avaric' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id avestan title Avestan
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'avestan', 'Avestan', 120, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id avestan
UPDATE `list_options` SET `notes` = 'ave' WHERE `option_id` = 'avestan' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Avestan
UPDATE `list_options` SET `notes` = 'ave' WHERE `title` = 'Avestan' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id aymara title Aymara
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'aymara', 'Aymara', 130, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id aymara
UPDATE `list_options` SET `notes` = 'aym' WHERE `option_id` = 'aymara' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Aymara
UPDATE `list_options` SET `notes` = 'aym' WHERE `title` = 'Aymara' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id azerbaijani title Azerbaijani
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'azerbaijani', 'Azerbaijani', 140, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id azerbaijani
UPDATE `list_options` SET `notes` = 'aze' WHERE `option_id` = 'azerbaijani' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Azerbaijani
UPDATE `list_options` SET `notes` = 'aze' WHERE `title` = 'Azerbaijani' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id bambara title Bambara
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'bambara', 'Bambara', 150, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id bambara
UPDATE `list_options` SET `notes` = 'bam' WHERE `option_id` = 'bambara' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Bambara
UPDATE `list_options` SET `notes` = 'bam' WHERE `title` = 'Bambara' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id bashkir title Bashkir
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'bashkir', 'Bashkir', 160, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id bashkir
UPDATE `list_options` SET `notes` = 'bak' WHERE `option_id` = 'bashkir' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Bashkir
UPDATE `list_options` SET `notes` = 'bak' WHERE `title` = 'Bashkir' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id basque title Basque
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'basque', 'Basque', 170, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id basque
UPDATE `list_options` SET `notes` = 'baq(B)|eus(T)' WHERE `option_id` = 'basque' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Basque
UPDATE `list_options` SET `notes` = 'baq(B)|eus(T)' WHERE `title` = 'Basque' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id belarusian title Belarusian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'belarusian', 'Belarusian', 180, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id belarusian
UPDATE `list_options` SET `notes` = 'bel' WHERE `option_id` = 'belarusian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Belarusian
UPDATE `list_options` SET `notes` = 'bel' WHERE `title` = 'Belarusian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id bengali title Bengali
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'bengali', 'Bengali', 190, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id bengali
UPDATE `list_options` SET `notes` = 'ben' WHERE `option_id` = 'bengali' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Bengali
UPDATE `list_options` SET `notes` = 'ben' WHERE `title` = 'Bengali' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id bihari_languages title Bihari languages
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'bihari_languages', 'Bihari languages', 200, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id bihari_languages
UPDATE `list_options` SET `notes` = 'bih' WHERE `option_id` = 'bihari_languages' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Bihari languages
UPDATE `list_options` SET `notes` = 'bih' WHERE `title` = 'Bihari languages' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id bislama title Bislama
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'bislama', 'Bislama', 210, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id bislama
UPDATE `list_options` SET `notes` = 'bis' WHERE `option_id` = 'bislama' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Bislama
UPDATE `list_options` SET `notes` = 'bis' WHERE `title` = 'Bislama' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id bokmal_norwegian_norwegian_bok title Bokmål, Norwegian; Norwegian Bokmål
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'bokmal_norwegian_norwegian_bok', 'Bokmål, Norwegian; Norwegian Bokmål', 220, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id bokmal_norwegian_norwegian_bok
UPDATE `list_options` SET `notes` = 'nob' WHERE `option_id` = 'bokmal_norwegian_norwegian_bok' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Bokmål, Norwegian; Norwegian Bokmål
UPDATE `list_options` SET `notes` = 'nob' WHERE `title` = 'Bokmål, Norwegian; Norwegian Bokmål' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id bosnian title Bosnian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'bosnian', 'Bosnian', 230, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id bosnian
UPDATE `list_options` SET `notes` = 'bos' WHERE `option_id` = 'bosnian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Bosnian
UPDATE `list_options` SET `notes` = 'bos' WHERE `title` = 'Bosnian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id breton title Breton
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'breton', 'Breton', 240, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id breton
UPDATE `list_options` SET `notes` = 'bre' WHERE `option_id` = 'breton' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Breton
UPDATE `list_options` SET `notes` = 'bre' WHERE `title` = 'Breton' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id bulgarian title Bulgarian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'bulgarian', 'Bulgarian', 250, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id bulgarian
UPDATE `list_options` SET `notes` = 'bul' WHERE `option_id` = 'bulgarian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Bulgarian
UPDATE `list_options` SET `notes` = 'bul' WHERE `title` = 'Bulgarian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id burmese title Burmese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'burmese', 'Burmese', 260, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id burmese
UPDATE `list_options` SET `notes` = 'bur(B)|mya(T)' WHERE `option_id` = 'burmese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Burmese
UPDATE `list_options` SET `notes` = 'bur(B)|mya(T)' WHERE `title` = 'Burmese' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id catalan_valencian title Catalan; Valencian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, option_value ) VALUES ('language', 'catalan_valencian', 'Catalan; Valencian', 270, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id catalan_valencian
UPDATE `list_options` SET `notes` = 'cat' WHERE `option_id` = 'catalan_valencian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Catalan; Valencian
UPDATE `list_options` SET `notes` = 'cat' WHERE `title` = 'Catalan; Valencian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id central_khmer title Central Khmer
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'central_khmer', 'Central Khmer', 280, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id central_khmer
UPDATE `list_options` SET `notes` = 'khm' WHERE `option_id` = 'central_khmer' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Central Khmer
UPDATE `list_options` SET `notes` = 'khm' WHERE `title` = 'Central Khmer' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id chamorro title Chamorro
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'chamorro', 'Chamorro', 290, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id chamorro
UPDATE `list_options` SET `notes` = 'cha' WHERE `option_id` = 'chamorro' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Chamorro
UPDATE `list_options` SET `notes` = 'cha' WHERE `title` = 'Chamorro' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id chechen title Chechen
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'chechen', 'Chechen', 300, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id chechen
UPDATE `list_options` SET `notes` = 'che' WHERE `option_id` = 'chechen' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Chechen
UPDATE `list_options` SET `notes` = 'che' WHERE `title` = 'Chechen' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id chichewa_chewa_nyanja title Chichewa; Chewa; Nyanja
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'chichewa_chewa_nyanja', 'Chichewa; Chewa; Nyanja', 310, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id chichewa_chewa_nyanja
UPDATE `list_options` SET `notes` = 'nya' WHERE `option_id` = 'chichewa_chewa_nyanja' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Chichewa; Chewa; Nyanja
UPDATE `list_options` SET `notes` = 'nya' WHERE `title` = 'Chichewa; Chewa; Nyanja' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id chinese title Chinese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'chinese', 'Chinese', 320, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id chinese
UPDATE `list_options` SET `notes` = 'chi(B)|zho(T)' WHERE `option_id` = 'chinese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Chinese
UPDATE `list_options` SET `notes` = 'chi(B)|zho(T)' WHERE `title` = 'Chinese' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 20 option_id chinese
UPDATE `list_options` SET `seq` = 320 WHERE `option_id` = 'chinese' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 20  title Chinese
UPDATE `list_options` SET `seq` = 320 WHERE `title` = 'Chinese' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id church_slavic_old_slavonic_chu title Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'church_slavic_old_slavonic_chu', 'Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic', 330, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id church_slavic_old_slavonic_chu
UPDATE `list_options` SET `notes` = 'chu' WHERE `option_id` = 'church_slavic_old_slavonic_chu' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic
UPDATE `list_options` SET `notes` = 'chu' WHERE `title` = 'Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id chuvash title Chuvash
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'chuvash', 'Chuvash', 340, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id chuvash
UPDATE `list_options` SET `notes` = 'chv' WHERE `option_id` = 'chuvash' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Chuvash
UPDATE `list_options` SET `notes` = 'chv' WHERE `title` = 'Chuvash' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id cornish title Cornish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'cornish', 'Cornish', 350, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id cornish
UPDATE `list_options` SET `notes` = 'cor' WHERE `option_id` = 'cornish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Cornish
UPDATE `list_options` SET `notes` = 'cor' WHERE `title` = 'Cornish' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id corsican title Corsican
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'corsican', 'Corsican', 360, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id corsican
UPDATE `list_options` SET `notes` = 'cos' WHERE `option_id` = 'corsican' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Corsican
UPDATE `list_options` SET `notes` = 'cos' WHERE `title` = 'Corsican' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id cree title Cree
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'cree', 'Cree', 370, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id cree
UPDATE `list_options` SET `notes` = 'cre' WHERE `option_id` = 'cree' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Cree
UPDATE `list_options` SET `notes` = 'cre' WHERE `title` = 'Cree' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id croatian title Croatian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'croatian', 'Croatian', 380, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id croatian
UPDATE `list_options` SET `notes` = 'hrv' WHERE `option_id` = 'croatian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Croatian
UPDATE `list_options` SET `notes` = 'hrv' WHERE `title` = 'Croatian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id czech title Czech
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'czech', 'Czech', 390, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id czech
UPDATE `list_options` SET `notes` = 'cze(B)|ces(T)' WHERE `option_id` = 'czech' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Czech
UPDATE `list_options` SET `notes` = 'cze(B)|ces(T)' WHERE `title` = 'Czech' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id danish title Danish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'danish', 'Danish', 400, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id danish
UPDATE `list_options` SET `notes` = 'dan' WHERE `option_id` = 'danish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Danish
UPDATE `list_options` SET `notes` = 'dan' WHERE `title` = 'Danish' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 30 option_id danish
UPDATE `list_options` SET `seq` = 400 WHERE `option_id` = 'danish' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 30 title Danish
UPDATE `list_options` SET `seq` = 400 WHERE `title` = 'Danish' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 40 option_id deaf
UPDATE `list_options` SET `seq` = 405 WHERE `option_id` = 'deaf' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 40 title Deaf
UPDATE `list_options` SET `seq` = 405 WHERE `title` = 'Deaf' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id divehi_dhivehi_maldivian title Divehi; Dhivehi; Maldivian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'divehi_dhivehi_maldivian', 'Divehi; Dhivehi; Maldivian', 410, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id divehi_dhivehi_maldivian
UPDATE `list_options` SET `notes` = 'div' WHERE `option_id` = 'divehi_dhivehi_maldivian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Divehi; Dhivehi; Maldivian
UPDATE `list_options` SET `notes` = 'div' WHERE `title` = 'Divehi; Dhivehi; Maldivian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id dutch_flemish title Dutch; Flemish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'dutch_flemish', 'Dutch; Flemish', 420, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id dutch_flemish
UPDATE `list_options` SET `notes` = 'dut(B)|nld(T)' WHERE `option_id` = 'dutch_flemish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Dutch; Flemish
UPDATE `list_options` SET `notes` = 'dut(B)|nld(T)' WHERE `title` = 'Dutch; Flemish' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id dzongkha title Dzongkha
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'dzongkha', 'Dzongkha', 430, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id dzongkha
UPDATE `list_options` SET `notes` = 'dzo' WHERE `option_id` = 'dzongkha' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Dzongkha
UPDATE `list_options` SET `notes` = 'dzo' WHERE `title` = 'Dzongkha' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id English title English
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'English', 'English', 440, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id English
UPDATE `list_options` SET `notes` = 'eng' WHERE `option_id` = 'English' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title English
UPDATE `list_options` SET `notes` = 'eng' WHERE `title` = 'English' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 50 option_id English
UPDATE `list_options` SET `seq` = 440 WHERE `option_id` = 'English' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 50 title English
UPDATE `list_options` SET `seq` = 440 WHERE `title` = 'English' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id esperanto title Esperanto
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'esperanto', 'Esperanto', 450, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id esperanto
UPDATE `list_options` SET `notes` = 'epo' WHERE `option_id` = 'esperanto' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Esperanto
UPDATE `list_options` SET `notes` = 'epo' WHERE `title` = 'Esperanto' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id estonian title Estonian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'estonian', 'Estonian', 460, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id estonian
UPDATE `list_options` SET `notes` = 'est' WHERE `option_id` = 'estonian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Estonian
UPDATE `list_options` SET `notes` = 'est' WHERE `title` = 'Estonian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id ewe title Ewe
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'ewe', 'Ewe', 470, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id ewe
UPDATE `list_options` SET `notes` = 'ewe' WHERE `option_id` = 'ewe' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Ewe
UPDATE `list_options` SET `notes` = 'ewe' WHERE `title` = 'Ewe' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id faroese title Faroese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'faroese', 'Faroese', 480, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id faroese
UPDATE `list_options` SET `notes` = 'fao' WHERE `option_id` = 'faroese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Faroese
UPDATE `list_options` SET `notes` = 'fao' WHERE `title` = 'Faroese' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 60 option_id farsi
UPDATE `list_options` SET `seq` = 485 WHERE `option_id` = 'farsi' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 60 title Farsi
UPDATE `list_options` SET `seq` = 485 WHERE `title` = 'Farsi' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id fijian title Fijian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'fijian', 'Fijian', 490, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id fijian
UPDATE `list_options` SET `notes` = 'fij' WHERE `option_id` = 'fijian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Fijian
UPDATE `list_options` SET `notes` = 'fij' WHERE `title` = 'Fijian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id finnish title Finnish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'finnish', 'Finnish', 500, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id finnish
UPDATE `list_options` SET `notes` = 'fin' WHERE `option_id` = 'finnish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Finnish
UPDATE `list_options` SET `notes` = 'fin' WHERE `title` = 'Finnish' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id french title French
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'french', 'French', 510, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id french
UPDATE `list_options` SET `notes` = 'fre(B)|fra(T)' WHERE `option_id` = 'french' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title French
UPDATE `list_options` SET `notes` = 'fre(B)|fra(T)' WHERE `title` = 'French' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 70 option_id french
UPDATE `list_options` SET `seq` = 510 WHERE `option_id` = 'french' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 70 title French
UPDATE `list_options` SET `seq` = 510 WHERE `title` = 'French' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id fulah title Fulah
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'fulah', 'Fulah', 520, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id fulah
UPDATE `list_options` SET `notes` = 'ful' WHERE `option_id` = 'fulah' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Fulah
UPDATE `list_options` SET `notes` = 'ful' WHERE `title` = 'Fulah' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id gaelic_scottish_gaelic title Gaelic; Scottish Gaelic
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'gaelic_scottish_gaelic', 'Gaelic; Scottish Gaelic', 530, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id gaelic_scottish_gaelic
UPDATE `list_options` SET `notes` = 'gla' WHERE `option_id` = 'gaelic_scottish_gaelic' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Gaelic; Scottish Gaelic
UPDATE `list_options` SET `notes` = 'gla' WHERE `title` = 'Gaelic; Scottish Gaelic' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id galician title Galician
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'galician', 'Galician', 540, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id galician
UPDATE `list_options` SET `notes` = 'glg' WHERE `option_id` = 'galician' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Galician
UPDATE `list_options` SET `notes` = 'glg' WHERE `title` = 'Galician' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id ganda title Ganda
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'ganda', 'Ganda', 550, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id ganda
UPDATE `list_options` SET `notes` = 'lug' WHERE `option_id` = 'ganda' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Ganda
UPDATE `list_options` SET `notes` = 'lug' WHERE `title` = 'Ganda' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id georgian title Georgian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'georgian', 'Georgian', 560, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id georgian
UPDATE `list_options` SET `notes` = 'geo(B)|kat(T)' WHERE `option_id` = 'georgian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Georgian
UPDATE `list_options` SET `notes` = 'geo(B)|kat(T)' WHERE `title` = 'Georgian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id german title German
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'german', 'German', 570, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id german
UPDATE `list_options` SET `notes` = 'ger(B)|deu(T)' WHERE `option_id` = 'german' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title German
UPDATE `list_options` SET `notes` = 'ger(B)|deu(T)' WHERE `title` = 'German' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 80 option_id german
UPDATE `list_options` SET `seq` = 570 WHERE `option_id` = 'german' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 80 title German
UPDATE `list_options` SET `seq` = 570 WHERE `title` = 'German' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id greek title Greek, Modern (1453-)
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'greek', 'Greek, Modern (1453-)', 580, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id greek
UPDATE `list_options` SET `notes` = 'gre(B)|ell(T)' WHERE `option_id` = 'greek' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Greek, Modern (1453-)
UPDATE `list_options` SET `notes` = 'gre(B)|ell(T)' WHERE `title` = 'Greek, Modern (1453-)' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Greek
UPDATE `list_options` SET `notes` = 'gre(B)|ell(T)' WHERE `title` = 'Greek' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 90 option_id greek
UPDATE `list_options` SET `seq` = 580 WHERE `option_id` = 'greek' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 90 title Greek, Modern (1453-)
UPDATE `list_options` SET `seq` = 580 WHERE `title` = 'Greek, Modern (1453-)' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 90 title Greek
UPDATE `list_options` SET `seq` = 580 WHERE `title` = 'Greek' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id guarani title Guarani
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'guarani', 'Guarani', 590, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id guarani
UPDATE `list_options` SET `notes` = 'grn' WHERE `option_id` = 'guarani' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Guarani
UPDATE `list_options` SET `notes` = 'grn' WHERE `title` = 'Guarani' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id gujarati title Gujarati
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'gujarati', 'Gujarati', 600, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id gujarati
UPDATE `list_options` SET `notes` = 'guj' WHERE `option_id` = 'gujarati' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Gujarati
UPDATE `list_options` SET `notes` = 'guj' WHERE `title` = 'Gujarati' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id haitian_haitian_creole title Haitian; Haitian Creole
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'haitian_haitian_creole', 'Haitian; Haitian Creole', 610, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id haitian_haitian_creole
UPDATE `list_options` SET `notes` = 'hat' WHERE `option_id` = 'haitian_haitian_creole' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Haitian; Haitian Creole
UPDATE `list_options` SET `notes` = 'hat' WHERE `title` = 'Haitian; Haitian Creole' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id hausa title Hausa
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'hausa', 'Hausa', 620, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id hausa
UPDATE `list_options` SET `notes` = 'hau' WHERE `option_id` = 'hausa' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Hausa
UPDATE `list_options` SET `notes` = 'hau' WHERE `title` = 'Hausa' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id hebrew title Hebrew
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'hebrew', 'Hebrew', 630, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id hebrew
UPDATE `list_options` SET `notes` = 'heb' WHERE `option_id` = 'hebrew' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Hebrew
UPDATE `list_options` SET `notes` = 'heb' WHERE `title` = 'Hebrew' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id herero title Herero
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'herero', 'Herero', 640, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id herero
UPDATE `list_options` SET `notes` = 'her' WHERE `option_id` = 'herero' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Herero
UPDATE `list_options` SET `notes` = 'her' WHERE `title` = 'Herero' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id hindi title Hindi
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'hindi', 'Hindi', 650, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id hindi
UPDATE `list_options` SET `notes` = 'hin' WHERE `option_id` = 'hindi' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Hindi
UPDATE `list_options` SET `notes` = 'hin' WHERE `title` = 'Hindi' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id hiri_motu title Hiri Motu
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'hiri_motu', 'Hiri Motu', 660, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id hiri_motu
UPDATE `list_options` SET `notes` = 'hmo' WHERE `option_id` = 'hiri_motu' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Hiri Motu
UPDATE `list_options` SET `notes` = 'hmo' WHERE `title` = 'Hiri Motu' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language option_id hmong
UPDATE `list_options` SET `notes` = 'hmn' WHERE `option_id` = 'hmong' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Hmong
UPDATE `list_options` SET `notes` = 'hmn' WHERE `title` = 'Hmong' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 100 option_id hmong
UPDATE `list_options` SET `seq` = 665 WHERE `option_id` = 'hmong' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 100 title Hmong
UPDATE `list_options` SET `seq` = 665 WHERE `title` = 'Hmong' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id hungarian title Hungarian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'hungarian', 'Hungarian', 670, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id hungarian
UPDATE `list_options` SET `notes` = 'hun' WHERE `option_id` = 'hungarian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Hungarian
UPDATE `list_options` SET `notes` = 'hun' WHERE `title` = 'Hungarian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id icelandic title Icelandic
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'icelandic', 'Icelandic', 680, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id icelandic
UPDATE `list_options` SET `notes` = 'ice(B)|isl(T)' WHERE `option_id` = 'icelandic' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Icelandic
UPDATE `list_options` SET `notes` = 'ice(B)|isl(T)' WHERE `title` = 'Icelandic' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id ido title Ido
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES (
'language', 'ido', 'Ido', 690, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id ido
UPDATE `list_options` SET `notes` = 'ido' WHERE `option_id` = 'ido' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Ido
UPDATE `list_options` SET `notes` = 'ido' WHERE `title` = 'Ido' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id igbo title Igbo
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'igbo', 'Igbo', 700, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id igbo
UPDATE `list_options` SET `notes` = 'ibo' WHERE `option_id` = 'igbo' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Igbo
UPDATE `list_options` SET `notes` = 'ibo' WHERE `title` = 'Igbo' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id indonesian title Indonesian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'indonesian', 'Indonesian', 710, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id indonesian
UPDATE `list_options` SET `notes` = 'ind' WHERE `option_id` = 'indonesian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Indonesian
UPDATE `list_options` SET `notes` = 'ind' WHERE `title` = 'Indonesian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id interlingua_international_auxi title Interlingua (International Auxiliary Language Association)
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'interlingua_international_auxi', 'Interlingua (International Auxiliary Language Association)', 720, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id interlingua_international_auxi
UPDATE `list_options` SET `notes` = 'ina' WHERE `option_id` = 'interlingua_international_auxi' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Interlingua (International Auxiliary Language Association)
UPDATE `list_options` SET `notes` = 'ina' WHERE `title` = 'Interlingua (International Auxiliary Language Association)' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id interlingue_occidental title Interlingue; Occidental
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'interlingue_occidental', 'Interlingue; Occidental', 730, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id interlingue_occidental
UPDATE `list_options` SET `notes` = 'ile' WHERE `option_id` = 'interlingue_occidental' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Interlingue; Occidental
UPDATE `list_options` SET `notes` = 'ile' WHERE `title` = 'Interlingue; Occidental' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id inuktitut title Inuktitut
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'inuktitut', 'Inuktitut', 740, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id inuktitut
UPDATE `list_options` SET `notes` = 'iku' WHERE `option_id` = 'inuktitut' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Inuktitut
UPDATE `list_options` SET `notes` = 'iku' WHERE `title` = 'Inuktitut' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id inupiaq title Inupiaq
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'inupiaq', 'Inupiaq', 750, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id inupiaq
UPDATE `list_options` SET `notes` = 'ipk' WHERE `option_id` = 'inupiaq' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Inupiaq
UPDATE `list_options` SET `notes` = 'ipk' WHERE `title` = 'Inupiaq' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id irish title Irish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'irish', 'Irish', 760, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id irish
UPDATE `list_options` SET `notes` = 'gle' WHERE `option_id` = 'irish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Irish
UPDATE `list_options` SET `notes` = 'gle' WHERE `title` = 'Irish' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id italian title Italian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'italian', 'Italian', 770, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id italian
UPDATE `list_options` SET `notes` = 'ita' WHERE `option_id` = 'italian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Italian
UPDATE `list_options` SET `notes` = 'ita' WHERE `title` = 'Italian' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 110 option_id italian
UPDATE `list_options` SET `seq` = 770 WHERE `option_id` = 'italian' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 110 title Italian
UPDATE `list_options` SET `seq` = 770 WHERE `title` = 'Italian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id japanese title Japanese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'japanese', 'Japanese', 780, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id japanese
UPDATE `list_options` SET `notes` = 'jpn' WHERE `option_id` = 'japanese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Japanese
UPDATE `list_options` SET `notes` = 'jpn' WHERE `title` = 'Japanese' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 120 option_id japanese
UPDATE `list_options` SET `seq` = 780 WHERE `option_id` = 'japanese' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 120 title Japanese
UPDATE `list_options` SET `seq` = 780 WHERE `title` = 'Japanese' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id javanese title Javanese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'javanese', 'Javanese', 790, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id javanese
UPDATE `list_options` SET `notes` = 'jav' WHERE `option_id` = 'javanese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Javanese
UPDATE `list_options` SET `notes` = 'jav' WHERE `title` = 'Javanese' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id kalaallisut_greenlandic title Kalaallisut; Greenlandic
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'kalaallisut_greenlandic', 'Kalaallisut; Greenlandic', 800, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id kalaallisut_greenlandic
UPDATE `list_options` SET `notes` = 'kal' WHERE `option_id` = 'kalaallisut_greenlandic' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Kalaallisut; Greenlandic
UPDATE `list_options` SET `notes` = 'kal' WHERE `title` = 'Kalaallisut; Greenlandic' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id kannada title Kannada
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'kannada', 'Kannada', 810, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id kannada
UPDATE `list_options` SET `notes` = 'kan' WHERE `option_id` = 'kannada' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Kannada
UPDATE `list_options` SET `notes` = 'kan' WHERE `title` = 'Kannada' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id kanuri title Kanuri
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'kanuri', 'Kanuri', 820, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id kanuri
UPDATE `list_options` SET `notes` = 'kau' WHERE `option_id` = 'kanuri' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Kanuri
UPDATE `list_options` SET `notes` = 'kau' WHERE `title` = 'Kanuri' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id kashmiri title Kashmiri
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'kashmiri', 'Kashmiri', 830, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id kashmiri
UPDATE `list_options` SET `notes` = 'kas' WHERE `option_id` = 'kashmiri' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Kashmiri
UPDATE `list_options` SET `notes` = 'kas' WHERE `title` = 'Kashmiri' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id kazakh title Kazakh
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'kazakh', 'Kazakh', 840, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id kazakh
UPDATE `list_options` SET `notes` = 'kaz' WHERE `option_id` = 'kazakh' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Kazakh
UPDATE `list_options` SET `notes` = 'kaz' WHERE `title` = 'Kazakh' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id kikuyu_gikuyu title Kikuyu; Gikuyu
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'kikuyu_gikuyu', 'Kikuyu; Gikuyu', 850, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id kikuyu_gikuyu
UPDATE `list_options` SET `notes` = 'kik' WHERE `option_id` = 'kikuyu_gikuyu' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Kikuyu; Gikuyu
UPDATE `list_options` SET `notes` = 'kik' WHERE `title` = 'Kikuyu; Gikuyu' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id kinyarwanda title Kinyarwanda
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'kinyarwanda', 'Kinyarwanda', 860, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id kinyarwanda
UPDATE `list_options` SET `notes` = 'kin' WHERE `option_id` = 'kinyarwanda' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Kinyarwanda
UPDATE `list_options` SET `notes` = 'kin' WHERE `title` = 'Kinyarwanda' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id kirghiz_kyrgyz title Kirghiz; Kyrgyz
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'kirghiz_kyrgyz', 'Kirghiz; Kyrgyz', 870, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id kirghiz_kyrgyz
UPDATE `list_options` SET `notes` = 'kir' WHERE `option_id` = 'kirghiz_kyrgyz' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Kirghiz; Kyrgyz
UPDATE `list_options` SET `notes` = 'kir' WHERE `title` = 'Kirghiz; Kyrgyz' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id komi title Komi
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'komi', 'Komi', 880, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id komi
UPDATE `list_options` SET `notes` = 'kom' WHERE `option_id` = 'komi' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Komi
UPDATE `list_options` SET `notes` = 'kom' WHERE `title` = 'Komi' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id kongo title Kongo
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'kongo', 'Kongo', 890, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id kongo
UPDATE `list_options` SET `notes` = 'kon' WHERE `option_id` = 'kongo' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Kongo
UPDATE `list_options` SET `notes` = 'kon' WHERE `title` = 'Kongo' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id korean title Korean
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'korean', 'Korean', 900, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id korean
UPDATE `list_options` SET `notes` = 'kor' WHERE `option_id` = 'korean' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Korean
UPDATE `list_options` SET `notes` = 'kor' WHERE `title` = 'Korean' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 130 option_id korean
UPDATE `list_options` SET `seq` = 900 WHERE `option_id` = 'korean' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 130 title Korean
UPDATE `list_options` SET `seq` = 900 WHERE `title` = 'Korean' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id kuanyama_kwanyama title Kuanyama; Kwanyama
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'kuanyama_kwanyama', 'Kuanyama; Kwanyama', 910, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id kuanyama_kwanyama
UPDATE `list_options` SET `notes` = 'kua' WHERE `option_id` = 'kuanyama_kwanyama' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Kuanyama; Kwanyama
UPDATE `list_options` SET `notes` = 'kua' WHERE `title` = 'Kuanyama; Kwanyama' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id kurdish title Kurdish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'kurdish', 'Kurdish', 920, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id kurdish
UPDATE `list_options` SET `notes` = 'kur' WHERE `option_id` = 'kurdish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Kurdish
UPDATE `list_options` SET `notes` = 'kur' WHERE `title` = 'Kurdish' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id laotian title Lao
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'laotian', 'Lao', 930, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id laotian
UPDATE `list_options` SET `notes` = 'lao' WHERE `option_id` = 'laotian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Lao
UPDATE `list_options` SET `notes` = 'lao' WHERE `title` = 'Lao' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Laotian
UPDATE `list_options` SET `notes` = 'lao' WHERE `title` = 'Laotian' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 140 option_id laotian
UPDATE `list_options` SET `seq` = 930 WHERE `option_id` = 'laotian' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 140 title Lao
UPDATE `list_options` SET `seq` = 930 WHERE `title` = 'Lao' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 140 title Laotian
UPDATE `list_options` SET `seq` = 930 WHERE `title` = 'Laotian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id latin title Latin
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'latin', 'Latin', 940, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id latin
UPDATE `list_options` SET `notes` = 'lat' WHERE `option_id` = 'latin' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Latin
UPDATE `list_options` SET `notes` = 'lat' WHERE `title` = 'Latin' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id latvian title Latvian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'latvian', 'Latvian', 950, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id latvian
UPDATE `list_options` SET `notes` = 'lav' WHERE `option_id` = 'latvian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Latvian
UPDATE `list_options` SET `notes` = 'lav' WHERE `title` = 'Latvian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id limburgan_limburger_limburgish title Limburgan; Limburger; Limburgish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'limburgan_limburger_limburgish', 'Limburgan; Limburger; Limburgish', 960, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id limburgan_limburger_limburgish
UPDATE `list_options` SET `notes` = 'lim' WHERE `option_id` = 'limburgan_limburger_limburgish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Limburgan; Limburger; Limburgish
UPDATE `list_options` SET `notes` = 'lim' WHERE `title` = 'Limburgan; Limburger; Limburgish' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id lingala title Lingala
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'lingala', 'Lingala', 970, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id lingala
UPDATE `list_options` SET `notes` = 'lin' WHERE `option_id` = 'lingala' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Lingala
UPDATE `list_options` SET `notes` = 'lin' WHERE `title` = 'Lingala' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id lithuanian title Lithuanian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'lithuanian', 'Lithuanian', 980, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id lithuanian
UPDATE `list_options` SET `notes` = 'lit' WHERE `option_id` = 'lithuanian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Lithuanian
UPDATE `list_options` SET `notes` = 'lit' WHERE `title` = 'Lithuanian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id luba-katanga title Luba-Katanga
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'luba-katanga', 'Luba-Katanga', 990, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id luba-katanga
UPDATE `list_options` SET `notes` = 'lub' WHERE `option_id` = 'luba-katanga' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Luba-Katanga
UPDATE `list_options` SET `notes` = 'lub' WHERE `title` = 'Luba-Katanga' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id luxembourgish_letzeburgesch title Luxembourgish; Letzeburgesch
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'luxembourgish_letzeburgesch', 'Luxembourgish; Letzeburgesch', 1000, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id luxembourgish_letzeburgesch
UPDATE `list_options` SET `notes` = 'ltz' WHERE `option_id` = 'luxembourgish_letzeburgesch' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Luxembourgish; Letzeburgesch
UPDATE `list_options` SET `notes` = 'ltz' WHERE `title` = 'Luxembourgish; Letzeburgesch' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id macedonian title Macedonian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'macedonian', 'Macedonian', 1010, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id macedonian
UPDATE `list_options` SET `notes` = 'mac(B)|mkd(T)' WHERE `option_id` = 'macedonian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Macedonian
UPDATE `list_options` SET `notes` = 'mac(B)|mkd(T)' WHERE `title` = 'Macedonian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id malagasy title Malagasy
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'malagasy', 'Malagasy', 1020, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id malagasy
UPDATE `list_options` SET `notes` = 'mlg' WHERE `option_id` = 'malagasy' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Malagasy
UPDATE `list_options` SET `notes` = 'mlg' WHERE `title` = 'Malagasy' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id malay title Malay
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'malay', 'Malay', 1030, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id malay
UPDATE `list_options` SET `notes` = 'may(B)|msa(T)' WHERE `option_id` = 'malay' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Malay
UPDATE `list_options` SET `notes` = 'may(B)|msa(T)' WHERE `title` = 'Malay' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id malayalam title Malayalam
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'malayalam', 'Malayalam', 1040, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id malayalam
UPDATE `list_options` SET `notes` = 'mal' WHERE `option_id` = 'malayalam' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Malayalam
UPDATE `list_options` SET `notes` = 'mal' WHERE `title` = 'Malayalam' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id maltese title Maltese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'maltese', 'Maltese', 1050, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id maltese
UPDATE `list_options` SET `notes` = 'mlt' WHERE `option_id` = 'maltese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Maltese
UPDATE `list_options` SET `notes` = 'mlt' WHERE `title` = 'Maltese' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id manx title Manx
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'manx', 'Manx', 1060, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id manx
UPDATE `list_options` SET `notes` = 'glv' WHERE `option_id` = 'manx' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Manx
UPDATE `list_options` SET `notes` = 'glv' WHERE `title` = 'Manx' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id maori title Maori
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'maori', 'Maori', 1070, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id maori
UPDATE `list_options` SET `notes` = 'mao(B)|mri(T)' WHERE `option_id` = 'maori' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Maori
UPDATE `list_options` SET `notes` = 'mao(B)|mri(T)' WHERE `title` = 'Maori' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id marathi title Marathi
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'marathi', 'Marathi', 1080, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id marathi
UPDATE `list_options` SET `notes` = 'mar' WHERE `option_id` = 'marathi' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Marathi
UPDATE `list_options` SET `notes` = 'mar' WHERE `title` = 'Marathi' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id marshallese title Marshallese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'marshallese', 'Marshallese', 1090, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id marshallese
UPDATE `list_options` SET `notes` = 'mah' WHERE `option_id` = 'marshallese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Marshallese
UPDATE `list_options` SET `notes` = 'mah' WHERE `title` = 'Marshallese' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 150 option_id mien
UPDATE `list_options` SET `seq` = 1095 WHERE `option_id` = 'mien' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 150 title Mien
UPDATE `list_options` SET `seq` = 1095 WHERE `title` = 'Mien' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id mongolian title Mongolian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'mongolian', 'Mongolian', 1100, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id mongolian
UPDATE `list_options` SET `notes` = 'mon' WHERE `option_id` = 'mongolian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Mongolian
UPDATE `list_options` SET `notes` = 'mon' WHERE `title` = 'Mongolian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id nauru title Nauru
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'nauru', 'Nauru', 1110, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id nauru
UPDATE `list_options` SET `notes` = 'nau' WHERE `option_id` = 'nauru' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Nauru
UPDATE `list_options` SET `notes` = 'nau' WHERE `title` = 'Nauru' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id navajo_navaho title Navajo; Navaho
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'navajo_navaho', 'Navajo; Navaho', 1120, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id navajo_navaho
UPDATE `list_options` SET `notes` = 'nav' WHERE `option_id` = 'navajo_navaho' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Navajo; Navaho
UPDATE `list_options` SET `notes` = 'nav' WHERE `title` = 'Navajo; Navaho' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id ndebele_north_north_ndebele title Ndebele, North; North Ndebele
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'ndebele_north_north_ndebele', 'Ndebele, North; North Ndebele', 1130, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id ndebele_north_north_ndebele
UPDATE `list_options` SET `notes` = 'nde' WHERE `option_id` = 'ndebele_north_north_ndebele' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Ndebele, North; North Ndebele
UPDATE `list_options` SET `notes` = 'nde' WHERE `title` = 'Ndebele, North; North Ndebele' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id ndebele_south_south_ndebele title Ndebele, South; South Ndebele
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'ndebele_south_south_ndebele', 'Ndebele, South; South Ndebele', 1140, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id ndebele_south_south_ndebele
UPDATE `list_options` SET `notes` = 'nbl' WHERE `option_id` = 'ndebele_south_south_ndebele' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Ndebele, South; South Ndebele
UPDATE `list_options` SET `notes` = 'nbl' WHERE `title` = 'Ndebele, South; South Ndebele' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id ndonga title Ndonga
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'ndonga', 'Ndonga', 1150, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id ndonga
UPDATE `list_options` SET `notes` = 'ndo' WHERE `option_id` = 'ndonga' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Ndonga
UPDATE `list_options` SET `notes` = 'ndo' WHERE `title` = 'Ndonga' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id nepali title Nepali
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'nepali', 'Nepali', 1160, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id nepali
UPDATE `list_options` SET `notes` = 'nep' WHERE `option_id` = 'nepali' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Nepali
UPDATE `list_options` SET `notes` = 'nep' WHERE `title` = 'Nepali' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id northern_sami title Northern Sami
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'northern_sami', 'Northern Sami', 1170, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id northern_sami
UPDATE `list_options` SET `notes` = 'sme' WHERE `option_id` = 'northern_sami' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Northern Sami
UPDATE `list_options` SET `notes` = 'sme' WHERE `title` = 'Northern Sami' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id norwegian title Norwegian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'norwegian', 'Norwegian', 1180, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id norwegian
UPDATE `list_options` SET `notes` = 'nor' WHERE `option_id` = 'norwegian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Norwegian
UPDATE `list_options` SET `notes` = 'nor' WHERE `title` = 'Norwegian' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 160 option_id norwegian
UPDATE `list_options` SET `seq` = 1180 WHERE `option_id` = 'norwegian' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 160 title Norwegian
UPDATE `list_options` SET `seq` = 1180 WHERE `title` = 'Norwegian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id norwegian_nynorsk_nynorsk_norw title Norwegian Nynorsk; Nynorsk, Norwegian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'norwegian_nynorsk_nynorsk_norw', 'Norwegian Nynorsk; Nynorsk, Norwegian', 1190, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id norwegian_nynorsk_nynorsk_norw
UPDATE `list_options` SET `notes` = 'nno' WHERE `option_id` = 'norwegian_nynorsk_nynorsk_norw' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Norwegian Nynorsk; Nynorsk, Norwegian
UPDATE `list_options` SET `notes` = 'nno' WHERE `title` = 'Norwegian Nynorsk; Nynorsk, Norwegian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id occitan_post_1500 title Occitan (post 1500)
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'occitan_post_1500', 'Occitan (post 1500)', 1200, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id occitan_post_1500
UPDATE `list_options` SET `notes` = 'oci' WHERE `option_id` = 'occitan_post_1500' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Occitan (post 1500)
UPDATE `list_options` SET `notes` = 'oci' WHERE `title` = 'Occitan (post 1500)' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id ojibwa title Ojibwa
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'ojibwa', 'Ojibwa', 1210, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id ojibwa
UPDATE `list_options` SET `notes` = 'oji' WHERE `option_id` = 'ojibwa' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Ojibwa
UPDATE `list_options` SET `notes` = 'oji' WHERE `title` = 'Ojibwa' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id oriya title Oriya
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'oriya', 'Oriya', 1220, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id oriya
UPDATE `list_options` SET `notes` = 'ori' WHERE `option_id` = 'oriya' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Oriya
UPDATE `list_options` SET `notes` = 'ori' WHERE `title` = 'Oriya' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id oromo title Oromo
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'oromo', 'Oromo', 1230, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id oromo
UPDATE `list_options` SET `notes` = 'orm' WHERE `option_id` = 'oromo' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Oromo
UPDATE `list_options` SET `notes` = 'orm' WHERE `title` = 'Oromo' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id ossetian_ossetic title Ossetian; Ossetic
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'ossetian_ossetic', 'Ossetian; Ossetic', 1240, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id ossetian_ossetic
UPDATE `list_options` SET `notes` = 'oss' WHERE `option_id` = 'ossetian_ossetic' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Ossetian; Ossetic
UPDATE `list_options` SET `notes` = 'oss' WHERE `title` = 'Ossetian; Ossetic' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 170 option_id othrs
UPDATE `list_options` SET `seq` = 1245 WHERE `option_id` = 'othrs' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 170 title Others
UPDATE `list_options` SET `seq` = 1245 WHERE `title` = 'Others' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id pali title Pali
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'pali', 'Pali', 1250, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id pali
UPDATE `list_options` SET `notes` = 'pli' WHERE `option_id` = 'pali' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Pali
UPDATE `list_options` SET `notes` = 'pli' WHERE `title` = 'Pali' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id persian title Persian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'persian', 'Persian', 1260, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id persian
UPDATE `list_options` SET `notes` = 'per(B)|fas(T)' WHERE `option_id` = 'persian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Persian
UPDATE `list_options` SET `notes` = 'per(B)|fas(T)' WHERE `title` = 'Persian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id polish title Polish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'polish', 'Polish', 1270, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id polish
UPDATE `list_options` SET `notes` = 'pol' WHERE `option_id` = 'polish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Polish
UPDATE `list_options` SET `notes` = 'pol' WHERE `title` = 'Polish' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id portuguese title Portuguese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'portuguese', 'Portuguese', 1280, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id portuguese
UPDATE `list_options` SET `notes` = 'por' WHERE `option_id` = 'portuguese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Portuguese
UPDATE `list_options` SET `notes` = 'por' WHERE `title` = 'Portuguese' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 180 option_id portuguese
UPDATE `list_options` SET `seq` = 1280 WHERE `option_id` = 'portuguese' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 180 title Portuguese
UPDATE `list_options` SET `seq` = 1280 WHERE `title` = 'Portuguese' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id punjabi title Punjabi
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'punjabi', 'Punjabi', 1290, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id portuguese
UPDATE `list_options` SET `notes` = 'pan' WHERE `option_id` = 'punjabi' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Punjabi
UPDATE `list_options` SET `notes` = 'pan' WHERE `title` = 'Punjabi' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 190 option_id punjabi
UPDATE `list_options` SET `seq` = 1290 WHERE `option_id` = 'punjabi' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 190 title Punjabi
UPDATE `list_options` SET `seq` = 1290 WHERE `title` = 'Punjabi' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id pushto_pashto title Pushto; Pashto
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'pushto_pashto', 'Pushto; Pashto', 1300, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id pushto_pashto
UPDATE `list_options` SET `notes` = 'pus' WHERE `option_id` = 'pushto_pashto' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Pushto; Pashto
UPDATE `list_options` SET `notes` = 'pus' WHERE `title` = 'Pushto; Pashto' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id quechua title Quechua
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'quechua', 'Quechua', 1310, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id quechua
UPDATE `list_options` SET `notes` = 'que' WHERE `option_id` = 'quechua' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Quechua
UPDATE `list_options` SET `notes` = 'que' WHERE `title` = 'Quechua' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id romanian_moldavian_moldovan title Romanian; Moldavian; Moldovan
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'romanian_moldavian_moldovan', 'Romanian; Moldavian; Moldovan', 1320, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id romanian_moldavian_moldovan
UPDATE `list_options` SET `notes` = 'rum(B)|ron(T)' WHERE `option_id` = 'romanian_moldavian_moldovan' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Romanian; Moldavian; Moldovan
UPDATE `list_options` SET `notes` = 'rum(B)|ron(T)' WHERE `title` = 'Romanian; Moldavian; Moldovan' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id romansh title Romansh
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'romansh', 'Romansh', 1330, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id romansh
UPDATE `list_options` SET `notes` = 'roh' WHERE `option_id` = 'romansh' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Romansh
UPDATE `list_options` SET `notes` = 'roh' WHERE `title` = 'Romansh' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id rundi title Rundi
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'rundi', 'Rundi', 1340, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id rundi
UPDATE `list_options` SET `notes` = 'run' WHERE `option_id` = 'rundi' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Rundi
UPDATE `list_options` SET `notes` = 'run' WHERE `title` = 'Rundi' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id russian title Russian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'russian', 'Russian', 1350, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id russian
UPDATE `list_options` SET `notes` = 'rus' WHERE `option_id` = 'russian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Russian
UPDATE `list_options` SET `notes` = 'rus' WHERE `title` = 'Russian' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 200 option_id russian
UPDATE `list_options` SET `seq` = 1350 WHERE `option_id` = 'russian' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 200 title Russian
UPDATE `list_options` SET `seq` = 1350 WHERE `title` = 'Russian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id samoan title Samoan
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'samoan', 'Samoan', 1360, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id samoan
UPDATE `list_options` SET `notes` = 'smo' WHERE `option_id` = 'samoan' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Samoan
UPDATE `list_options` SET `notes` = 'smo' WHERE `title` = 'Samoan' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id sango title Sango
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'sango', 'Sango', 1370, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id sango
UPDATE `list_options` SET `notes` = 'sag' WHERE `option_id` = 'sango' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Sango
UPDATE `list_options` SET `notes` = 'sag' WHERE `title` = 'Sango' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id sanskrit title Sanskrit
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'sanskrit', 'Sanskrit', 1380, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id sanskrit
UPDATE `list_options` SET `notes` = 'san' WHERE `option_id` = 'sanskrit' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Sanskrit
UPDATE `list_options` SET `notes` = 'san' WHERE `title` = 'Sanskrit' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id sardinian title Sardinian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'sardinian', 'Sardinian', 1390, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id sardinian
UPDATE `list_options` SET `notes` = 'srd' WHERE `option_id` = 'sardinian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Sardinian
UPDATE `list_options` SET `notes` = 'srd' WHERE `title` = 'Sardinian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id serbian title Serbian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'serbian', 'Serbian', 1400, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id serbian
UPDATE `list_options` SET `notes` = 'srp' WHERE `option_id` = 'serbian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Serbian
UPDATE `list_options` SET `notes` = 'srp' WHERE `title` = 'Serbian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id shona title Shona
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'shona', 'Shona', 1410, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id shona
UPDATE `list_options` SET `notes` = 'sna' WHERE `option_id` = 'shona' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Shona
UPDATE `list_options` SET `notes` = 'sna' WHERE `title` = 'Shona' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id sichuan_yi_nuosu title Sichuan Yi; Nuosu
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'sichuan_yi_nuosu', 'Sichuan Yi; Nuosu', 1420, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id sichuan_yi_nuosu
UPDATE `list_options` SET `notes` = 'iii' WHERE `option_id` = 'sichuan_yi_nuosu' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Sichuan Yi; Nuosu
UPDATE `list_options` SET `notes` = 'iii' WHERE `title` = 'Sichuan Yi; Nuosu' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id sindhi title Sindhi
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'sindhi', 'Sindhi', 1430, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id sindhi
UPDATE `list_options` SET `notes` = 'snd' WHERE `option_id` = 'sindhi' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Sindhi
UPDATE `list_options` SET `notes` = 'snd' WHERE `title` = 'Sindhi' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id sinhala_sinhalese title Sinhala; Sinhalese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'sinhala_sinhalese', 'Sinhala; Sinhalese', 1440, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id sinhala_sinhalese
UPDATE `list_options` SET `notes` = 'sin' WHERE `option_id` = 'sinhala_sinhalese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Sinhala; Sinhalese
UPDATE `list_options` SET `notes` = 'sin' WHERE `title` = 'Sinhala; Sinhalese' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id slovak title Slovak
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'slovak', 'Slovak', 1450, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id slovak
UPDATE `list_options` SET `notes` = 'slo(B)|slk(T)' WHERE `option_id` = 'slovak' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Slovak
UPDATE `list_options` SET `notes` = 'slo(B)|slk(T)' WHERE `title` = 'Slovak' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id slovenian title Slovenian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'slovenian', 'Slovenian', 1460, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id slovenian
UPDATE `list_options` SET `notes` = 'slv' WHERE `option_id` = 'slovenian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Slovenian
UPDATE `list_options` SET `notes` = 'slv' WHERE `title` = 'Slovenian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id somali title Somali
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'somali', 'Somali', 1470, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id somali
UPDATE `list_options` SET `notes` = 'som' WHERE `option_id` = 'somali' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Somali
UPDATE `list_options` SET `notes` = 'som' WHERE `title` = 'Somali' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id sotho_southern title Sotho, Southern
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'sotho_southern', 'Sotho, Southern', 1480, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id sotho_southern
UPDATE `list_options` SET `notes` = 'sot' WHERE `option_id` = 'sotho_southern' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Sotho, Southern
UPDATE `list_options` SET `notes` = 'sot' WHERE `title` = 'Sotho, Southern' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id Spanish title Spanish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'Spanish', 'Spanish', 1490, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id Spanish
UPDATE `list_options` SET `notes` = 'spa' WHERE `option_id` = 'Spanish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Spanish
UPDATE `list_options` SET `notes` = 'spa' WHERE `title` = 'Spanish' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 210 option_id Spanish
UPDATE `list_options` SET `seq` = 1490 WHERE `option_id` = 'Spanish' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 210 title Spanish
UPDATE `list_options` SET `seq` = 1490 WHERE `title` = 'Spanish' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id sundanese title Sundanese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'sundanese', 'Sundanese', 1500, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id sundanese
UPDATE `list_options` SET `notes` = 'sun' WHERE `option_id` = 'sundanese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Sundanese
UPDATE `list_options` SET `notes` = 'sun' WHERE `title` = 'Sundanese' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id swahili title Swahili
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'swahili', 'Swahili', 1510, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id swahili
UPDATE `list_options` SET `notes` = 'swa' WHERE `option_id` = 'swahili' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Swahili
UPDATE `list_options` SET `notes` = 'swa' WHERE `title` = 'Swahili' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id swati title Swati
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'swati', 'Swati', 1520, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id swati
UPDATE `list_options` SET `notes` = 'ssw' WHERE `option_id` = 'swati' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Swati
UPDATE `list_options` SET `notes` = 'ssw' WHERE `title` = 'Swati' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id swedish title Swedish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'swedish', 'Swedish', 1530, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id swedish
UPDATE `list_options` SET `notes` = 'swe' WHERE `option_id` = 'swedish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Swedish
UPDATE `list_options` SET `notes` = 'swe' WHERE `title` = 'Swedish' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id tagalog title Tagalog
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'tagalog', 'Tagalog', 1540, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id tagalog
UPDATE `list_options` SET `notes` = 'tgl' WHERE `option_id` = 'tagalog' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Tagalog
UPDATE `list_options` SET `notes` = 'tgl' WHERE `title` = 'Tagalog' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 220 option_id tagalog
UPDATE `list_options` SET `seq` = 1540 WHERE `option_id` = 'tagalog' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 220 title Tagalog
UPDATE `list_options` SET `seq` = 1540 WHERE `title` = 'Tagalog' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id tahitian title Tahitian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'tahitian', 'Tahitian', 1550, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id tahitian
UPDATE `list_options` SET `notes` = 'tah' WHERE `option_id` = 'tahitian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Tahitian
UPDATE `list_options` SET `notes` = 'tah' WHERE `title` = 'Tahitian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id tajik title Tajik
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'tajik', 'Tajik', 1560, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id tajik
UPDATE `list_options` SET `notes` = 'tgk' WHERE `option_id` = 'tajik' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Tajik
UPDATE `list_options` SET `notes` = 'tgk' WHERE `title` = 'Tajik' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id tamil title Tamil
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'tamil', 'Tamil', 1570, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id tamil
UPDATE `list_options` SET `notes` = 'tam' WHERE `option_id` = 'tamil' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Tamil
UPDATE `list_options` SET `notes` = 'tam' WHERE `title` = 'Tamil' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id tatar title Tatar
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'tatar', 'Tatar', 1580, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id tatar
UPDATE `list_options` SET `notes` = 'tat' WHERE `option_id` = 'tatar' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Tatar
UPDATE `list_options` SET `notes` = 'tat' WHERE `title` = 'Tatar' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id telugu title Telugu
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'telugu', 'Telugu', 1590, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id telugu
UPDATE `list_options` SET `notes` = 'tel' WHERE `option_id` = 'telugu' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Telugu
UPDATE `list_options` SET `notes` = 'tel' WHERE `title` = 'Telugu' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id thai title Thai
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'thai', 'Thai', 1600, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id thai
UPDATE `list_options` SET `notes` = 'tha' WHERE `option_id` = 'thai' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Thai
UPDATE `list_options` SET `notes` = 'tha' WHERE `title` = 'Thai' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id tibetan title Tibetan
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'tibetan', 'Tibetan', 1610, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id tibetan
UPDATE `list_options` SET `notes` = 'tib(B)|bod(T)' WHERE `option_id` = 'tibetan' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Tibetan
UPDATE `list_options` SET `notes` = 'tib(B)|bod(T)' WHERE `title` = 'Tibetan' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id tigrinya title Tigrinya
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'tigrinya', 'Tigrinya', 1620, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id tigrinya
UPDATE `list_options` SET `notes` = 'tir' WHERE `option_id` = 'tigrinya' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Tigrinya
UPDATE `list_options` SET `notes` = 'tir' WHERE `title` = 'Tigrinya' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id tonga_tonga_islands title Tonga (Tonga Islands)
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'tonga_tonga_islands', 'Tonga (Tonga Islands)', 1630, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id tonga_tonga_islands
UPDATE `list_options` SET `notes` = 'ton' WHERE `option_id` = 'tonga_tonga_islands' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Tonga (Tonga Islands)
UPDATE `list_options` SET `notes` = 'ton' WHERE `title` = 'Tonga (Tonga Islands)' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id tsonga title Tsonga
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'tsonga', 'Tsonga', 1640, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id tsonga
UPDATE `list_options` SET `notes` = 'tso' WHERE `option_id` = 'tsonga' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Tsonga
UPDATE `list_options` SET `notes` = 'tso' WHERE `title` = 'Tsonga' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id tswana title Tswana
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'tswana', 'Tswana', 1650, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id tswana
UPDATE `list_options` SET `notes` = 'tsn' WHERE `option_id` = 'tswana' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Tswana
UPDATE `list_options` SET `notes` = 'tsn' WHERE `title` = 'Tswana' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id turkish title Turkish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'turkish', 'Turkish', 1660, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id turkish
UPDATE `list_options` SET `notes` = 'tur' WHERE `option_id` = 'turkish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Turkish
UPDATE `list_options` SET `notes` = 'tur' WHERE `title` = 'Turkish' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 230 option_id turkish
UPDATE `list_options` SET `seq` = 1660 WHERE `option_id` = 'turkish' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 230 title Turkish
UPDATE `list_options` SET `seq` = 1660 WHERE `title` = 'Turkish' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id turkmen title Turkmen
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'turkmen', 'Turkmen', 1670, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id turkmen
UPDATE `list_options` SET `notes` = 'tuk' WHERE `option_id` = 'turkmen' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Turkmen
UPDATE `list_options` SET `notes` = 'tuk' WHERE `title` = 'Turkmen' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id twi title Twi
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'twi', 'Twi', 1680, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id twi
UPDATE `list_options` SET `notes` = 'twi' WHERE `option_id` = 'twi' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Twi
UPDATE `list_options` SET `notes` = 'twi' WHERE `title` = 'Twi' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id uighur_uyghur title Uighur; Uyghur
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'uighur_uyghur', 'Uighur; Uyghur', 1690, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id uighur_uyghur
UPDATE `list_options` SET `notes` = 'uig' WHERE `option_id` = 'uighur_uyghur' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Uighur; Uyghur
UPDATE `list_options` SET `notes` = 'uig' WHERE `title` = 'Uighur; Uyghur' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id ukrainian title Ukrainian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'ukrainian', 'Ukrainian', 1700, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id ukrainian
UPDATE `list_options` SET `notes` = 'ukr' WHERE `option_id` = 'ukrainian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Ukrainian
UPDATE `list_options` SET `notes` = 'ukr' WHERE `title` = 'Ukrainian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id urdu title Urdu
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'urdu', 'Urdu', 1710, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id urdu
UPDATE `list_options` SET `notes` = 'urd' WHERE `option_id` = 'urdu' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Urdu
UPDATE `list_options` SET `notes` = 'urd' WHERE `title` = 'Urdu' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id uzbek title Uzbek
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'uzbek', 'Uzbek', 1720, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id uzbek
UPDATE `list_options` SET `notes` = 'uzb' WHERE `option_id` = 'uzbek' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Uzbek
UPDATE `list_options` SET `notes` = 'uzb' WHERE `title` = 'Uzbek' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id venda title Venda
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'venda', 'Venda', 1730, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id venda
UPDATE `list_options` SET `notes` = 'ven' WHERE `option_id` = 'venda' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Venda
UPDATE `list_options` SET `notes` = 'ven' WHERE `title` = 'Venda' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id vietnamese title Vietnamese
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'vietnamese', 'Vietnamese', 1740, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id vietnamese
UPDATE `list_options` SET `notes` = 'vie' WHERE `option_id` = 'vietnamese' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Vietnamese
UPDATE `list_options` SET `notes` = 'vie' WHERE `title` = 'Vietnamese' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 240 option_id vietnamese
UPDATE `list_options` SET `seq` = 1740 WHERE `option_id` = 'vietnamese' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 240 title Vietnamese
UPDATE `list_options` SET `seq` = 1740 WHERE `title` = 'Vietnamese' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id volapuk title Volapük
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'volapuk', 'Volapük', 1750, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id volapuk
UPDATE `list_options` SET `notes` = 'vol' WHERE `option_id` = 'volapuk' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Volapük
UPDATE `list_options` SET `notes` = 'vol' WHERE `title` = 'Volapük' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id walloon title Walloon
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'walloon', 'Walloon', 1760, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id walloon
UPDATE `list_options` SET `notes` = 'wln' WHERE `option_id` = 'walloon' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Walloon
UPDATE `list_options` SET `notes` = 'wln' WHERE `title` = 'Walloon' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id welsh title Welsh
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'welsh', 'Welsh', 1770, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id welsh
UPDATE `list_options` SET `notes` = 'wel(B)|cym(T)' WHERE `option_id` = 'welsh' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Welsh
UPDATE `list_options` SET `notes` = 'wel(B)|cym(T)' WHERE `title` = 'Welsh' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id western_frisian title Western Frisian
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'western_frisian', 'Western Frisian', 1780, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id western_frisian
UPDATE `list_options` SET `notes` = 'fry' WHERE `option_id` = 'western_frisian' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Western Frisian
UPDATE `list_options` SET `notes` = 'fry' WHERE `title` = 'Western Frisian' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id wolof title Wolof
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'wolof', 'Wolof', 1790, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id wolof
UPDATE `list_options` SET `notes` = 'wol' WHERE `option_id` = 'wolof' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Wolof
UPDATE `list_options` SET `notes` = 'wol' WHERE `title` = 'Wolof' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id xhosa title Xhosa
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'xhosa', 'Xhosa', 1800, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id xhosa
UPDATE `list_options` SET `notes` = 'xho' WHERE `option_id` = 'xhosa' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Xhosa
UPDATE `list_options` SET `notes` = 'xho' WHERE `title` = 'Xhosa' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id yiddish title Yiddish
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'yiddish', 'Yiddish', 1810, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id yiddish
UPDATE `list_options` SET `notes` = 'yid' WHERE `option_id` = 'yiddish' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Yiddish
UPDATE `list_options` SET `notes` = 'yid' WHERE `title` = 'Yiddish' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 250 option_id yiddish
UPDATE `list_options` SET `seq` = 1810 WHERE `option_id` = 'yiddish' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 250 title Yiddish
UPDATE `list_options` SET `seq` = 1810 WHERE `title` = 'Yiddish' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id yoruba title Yoruba
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'yoruba', 'Yoruba', 1820, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id yoruba
UPDATE `list_options` SET `notes` = 'yor' WHERE `option_id` = 'yoruba' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Yoruba
UPDATE `list_options` SET `notes` = 'yor' WHERE `title` = 'Yoruba' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id zhuang_chuang title Zhuang; Chuang
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'zhuang_chuang', 'Zhuang; Chuang', 1830, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id zhuang_chuang
UPDATE `list_options` SET `notes` = 'zha' WHERE `option_id` = 'zhuang_chuang' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Zhuang; Chuang
UPDATE `list_options` SET `notes` = 'zha' WHERE `title` = 'Zhuang; Chuang' AND `list_id` = 'language';
#EndIf

#IfNotRow2Dx2 list_options list_id language option_id zulu title Zulu
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value` ) VALUES ('language', 'zulu', 'Zulu', 1840, 0, 0);
#EndIf

#IfRow2D list_options list_id language option_id zulu
UPDATE `list_options` SET `notes` = 'zul' WHERE `option_id` = 'zulu' AND `list_id` = 'language';
#EndIf

#IfRow2D list_options list_id language title Zulu
UPDATE `list_options` SET `notes` = 'zul' WHERE `title` = 'Zulu' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 260 option_id zulu
UPDATE `list_options` SET `seq` = 1840 WHERE `option_id` = 'zulu' AND `list_id` = 'language';
#EndIf

#IfRow3D list_options list_id language seq 260 title Zulu
UPDATE `list_options` SET `seq` = 1840 WHERE `title` = 'Zulu' AND `list_id` = 'language';
#EndIf

