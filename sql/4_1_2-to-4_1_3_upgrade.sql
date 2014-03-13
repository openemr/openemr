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

