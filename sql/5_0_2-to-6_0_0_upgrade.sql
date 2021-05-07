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

--  #IfIndex
--    desc:      This function is most often used for dropping of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the table and index exist the relevant statements are executed, otherwise not.

--  #IfNotIndex
--    desc:      This function will allow adding of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the index does not exist, it will be created

--  #IfUuidNeedUpdate
--    argument: table_name
--    behavior: this will add and populate a uuid column into table

--  #IfUuidNeedUpdateId
--    argument: table_name primary_id
--    behavior: this will add and populate a uuid column into table

--  #IfUuidNeedUpdateVertical
--    argument: table_name table_columns
--    behavior: this will add and populate a uuid column into vertical table for combinations of table_columns given

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


#IfMissingColumn facility iban
ALTER TABLE `facility` ADD `iban` varchar(50) default NULL;
#EndIf

#IfNotRow2D list_options list_id apps option_id oeSignerRemote
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('apps','oeSignerRemote','./../portal/sign/assets/signit.php',30,0,0);
#EndIf

#IfNotColumnType form_eye_neuro ACT5CCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT5CCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT1CCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT1CCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT2CCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT2CCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT3CCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT3CCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT4CCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT4CCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT6CCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT6CCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT7CCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT7CCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT8CCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT8CCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT9CCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT9CCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT10CCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT10CCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT11CCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT11CCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT1SCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT1SCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT2SCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT2SCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT3SCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT3SCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT4SCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT4SCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT5SCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT5SCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT6SCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT6SCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT7SCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT7SCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT8SCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT8SCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT9SCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT9SCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT10SCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT10SCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT11SCDIST text
ALTER TABLE `form_eye_neuro` MODIFY `ACT11SCDIST` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT1SCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT1SCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT2SCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT2SCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT3SCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT3SCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT4SCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT4SCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT5CCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT5CCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT6CCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT6CCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT7CCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT7CCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT8CCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT8CCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT9CCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT9CCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT10CCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT10CCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT11CCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT11CCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT5SCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT5SCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT6SCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT6SCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT7SCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT7SCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT8SCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT8SCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT9SCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT9SCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT10SCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT10SCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT11SCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT11SCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT1CCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT1CCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT2CCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT2CCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT3CCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT3CCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ACT4CCNEAR text
ALTER TABLE `form_eye_neuro` MODIFY `ACT4CCNEAR` text;
#EndIf

#IfNotColumnType form_eye_neuro ODNPA text
ALTER TABLE `form_eye_neuro` MODIFY `ODNPA` text;
#EndIf

#IfNotColumnType form_eye_neuro OSNPA text
ALTER TABLE `form_eye_neuro` MODIFY `OSNPA` text;
#EndIf

#IfNotColumnType form_eye_neuro VERTFUSAMPS text
ALTER TABLE `form_eye_neuro` MODIFY `VERTFUSAMPS` text;
#EndIf

#IfNotColumnType form_eye_neuro DIVERGENCEAMPS text
ALTER TABLE `form_eye_neuro` MODIFY `DIVERGENCEAMPS` text;
#EndIf

#IfNotColumnType form_eye_neuro ODCOLOR text
ALTER TABLE `form_eye_neuro` MODIFY `ODCOLOR` text;
#EndIf

#IfNotColumnType form_eye_neuro OSCOLOR text
ALTER TABLE `form_eye_neuro` MODIFY `OSCOLOR` text;
#EndIf

#IfNotColumnType form_eye_neuro ODCOINS text
ALTER TABLE `form_eye_neuro` MODIFY `ODCOINS` text;
#EndIf

#IfNotColumnType form_eye_neuro OSCOINS text
ALTER TABLE `form_eye_neuro` MODIFY `OSCOINS` text;
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2019-10-01 load_filename 2020-ICD-10-CM-Codes.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2019-10-01', '2020-ICD-10-CM-Codes.zip', '745546b3c94af3401e84003e1b143b9b');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2019-10-01 load_filename 2020-ICD-10-PCS-Order.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2019-10-01', '2020-ICD-10-PCS-Order.zip', '8dc136d780ec60916e9e1fc999837bc8');
#EndIf

#IfMissingColumn patient_access_onsite portal_login_username
ALTER TABLE `patient_access_onsite` ADD `portal_login_username` VARCHAR(100) DEFAULT NULL COMMENT 'User entered username', ADD `portal_onetime` VARCHAR(255) DEFAULT NULL;
UPDATE `patient_access_onsite` SET `portal_pwd_status` = '0', `portal_login_username` = `portal_username`;
#EndIf

#IfMissingColumn api_token token_auth_salt
ALTER TABLE `api_token` ADD `token_auth_salt` varchar(255);
#EndIf

#IfMissingColumn api_token token_auth
ALTER TABLE `api_token` ADD `token_auth` varchar(255);
#EndIf

#IfMissingColumn facility info
ALTER TABLE `facility` ADD `info` TEXT;
#EndIf

#IfNotColumnType patient_access_onsite portal_pwd varchar(255)
ALTER TABLE `patient_access_onsite` MODIFY `portal_pwd` varchar(255);
#EndIf

#IfColumn patient_access_onsite portal_salt
ALTER TABLE `patient_access_onsite` DROP COLUMN `portal_salt`;
#EndIf

#IfNotColumnType patient_access_offsite portal_pwd varchar(255)
ALTER TABLE `patient_access_offsite` MODIFY `portal_pwd` varchar(255) NOT NULL;
#EndIf

#IfColumn users pwd_expiration_date
ALTER TABLE users DROP COLUMN `pwd_expiration_date`;
#EndIf

#IfColumn users pwd_history1
ALTER TABLE users DROP COLUMN `pwd_history1`;
#EndIf

#IfColumn users pwd_history2
ALTER TABLE users DROP COLUMN `pwd_history2`;
#EndIf

#IfMissingColumn users_secure last_update_password
ALTER TABLE `users_secure` ADD `last_update_password` datetime DEFAULT NULL;
UPDATE `users_secure` SET `last_update_password` = NOW();
#EndIf

#IfColumn users_secure salt
ALTER TABLE `users_secure` DROP COLUMN `salt`;
#EndIf

#IfColumn users_secure salt_history1
ALTER TABLE `users_secure` DROP COLUMN `salt_history1`;
#EndIf

#IfColumn users_secure salt_history2
ALTER TABLE `users_secure` DROP COLUMN `salt_history2`;
#EndIf

#IfColumn api_token token_auth_salt
ALTER TABLE `api_token` DROP COLUMN `token_auth_salt`;
#EndIf

#IfMissingColumn users_secure password_history3
ALTER TABLE `users_secure` ADD `password_history3` varchar(255);
#EndIf

#IfMissingColumn users_secure password_history4
ALTER TABLE `users_secure` ADD `password_history4` varchar(255);
#EndIf

UPDATE `globals` SET `gl_value`=3 WHERE `gl_name`='password_history' AND `gl_value`=1;

#IfNotRow4D supported_external_dataloads load_type CQM_VALUESET load_source NIH_VSAC load_release_date 2018-09-17 load_filename ep_ec_eh_cms_20180917.xml.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('CQM_VALUESET', 'NIH_VSAC', '2018-09-17','ep_ec_eh_cms_20180917.xml.zip','a1e584714b080aced6ca73b4b7b076a1');
#EndIf

#IfMissingColumn form_encounter parent_encounter_id
ALTER TABLE `form_encounter` ADD `parent_encounter_id` BIGINT(20) NULL DEFAULT NULL;
#EndIf

#IfMissingColumn modules sql_version
ALTER TABLE `modules` ADD `sql_version` VARCHAR(150) NOT NULL;
#EndIf

#IfMissingColumn modules acl_version
ALTER TABLE `modules` ADD `acl_version` VARCHAR(150) NOT NULL;
#EndIf

#IfNotTable pro_assessments
CREATE TABLE `pro_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_oid` varchar(255) NOT NULL COMMENT 'unique id for specific instrument, pulled from assessment center API',
  `form_name` varchar (255) NOT NULL COMMENT 'pulled from assessment center API',
  `user_id` int(11) NOT NULL COMMENT 'ID for user that orders the form',
  `deadline` datetime NOT NULL COMMENT 'deadline to complete the form, will be used when sending notification and reminders',
  `patient_id` int(11) NOT NULL COMMENT 'ID for patient to order the form for',
  `assessment_oid` varchar(255) NOT NULL COMMENT 'unique id for this specific assessment, pulled from assessment center API',
  `status` varchar(255) NOT NULL COMMENT 'ordered or completed',
  `score` double NOT NULL COMMENT 'T-Score for the assessment',
  `error` double NOT NULL COMMENT 'Standard error for the score',
  `created_at` datetime NOT NULL COMMENT 'timestamp recording the creation time of this assessment',
  `updated_at` datetime NOT NULL COMMENT 'this field indicates the completion time when the status is completed',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1;
#EndIf

#IfNotRow2D list_options list_id LBF_Validations option_id future_date
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`, `seq`) VALUES ('LBF_Validations','future_date','Future Date','{\"futureDate\":{\"message\":\"must be future date\"}}','32');

#IfNotRow2D list_options list_id lists option_id Sort_Direction
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `activity`) VALUES ('lists', 'Sort_Direction', 'Sort Direction', 1, 0, 1);
#EndIf

#IfNotRow2D list_options list_id Sort_Direction option_id 0
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `activity`) VALUES ('Sort_Direction', '0', 'asc', 10, 1, 1);
#EndIf

#IfNotRow2D list_options list_id Sort_Direction option_id 1
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `activity`) VALUES ('Sort_Direction', '1', 'desc', 20, 0, 1);
#EndIf

#IfNotColumnType form_eye_mag_prefs ordering smallint(6)
ALTER TABLE `form_eye_mag_prefs` MODIFY `ordering` smallint(6) DEFAULT NULL;
#EndIf

#IfNotColumnType codes code_text_short text
ALTER TABLE `codes` MODIFY `code_text_short` text;
#EndIf

#IfNotColumnTypeDefault amendments created_time timestamp NULL
ALTER TABLE `amendments` MODIFY `created_time` timestamp NULL COMMENT 'created time';
SET @currentSQLMode = (SELECT @@sql_mode);
SET sql_mode = '';
UPDATE `amendments` SET `created_time` = NULL WHERE `created_time` = '0000-00-00 00:00:00';
SET sql_mode = @currentSQLMode;
#EndIf

#IfNotColumnTypeDefault amendments_history created_time timestamp NULL
ALTER TABLE `amendments_history` MODIFY `created_time` timestamp NULL COMMENT 'created time';
SET @currentSQLMode = (SELECT @@sql_mode);
SET sql_mode = '';
UPDATE `amendments_history` SET `created_time` = NULL WHERE `created_time` = '0000-00-00 00:00:00';
SET sql_mode = @currentSQLMode;
#EndIf

#IfNotColumnTypeDefault batchcom msg_date_sent datetime NULL
ALTER TABLE `batchcom` MODIFY `msg_date_sent` datetime NULL;
SET @currentSQLMode = (SELECT @@sql_mode);
SET sql_mode = '';
UPDATE `batchcom` SET `msg_date_sent` = NULL WHERE `msg_date_sent` = '0000-00-00 00:00:00';
SET sql_mode = @currentSQLMode;
#EndIf

#IfNotColumnTypeDefault drug_inventory last_notify date NULL
ALTER TABLE `drug_inventory` MODIFY `last_notify` date NULL;
SET @currentSQLMode = (SELECT @@sql_mode);
SET sql_mode = '';
UPDATE `drug_inventory` SET `last_notify` = NULL WHERE `last_notify` = '0000-00-00';
SET sql_mode = @currentSQLMode;
#EndIf

#IfNotColumnTypeDefault drugs last_notify date NULL
ALTER TABLE `drugs` MODIFY `last_notify` date NULL;
SET @currentSQLMode = (SELECT @@sql_mode);
SET sql_mode = '';
UPDATE `drugs` SET `last_notify` = NULL WHERE `last_notify` = '0000-00-00';
SET sql_mode = @currentSQLMode;
#EndIf

#IfNotColumnTypeDefault insurance_data date date NULL
ALTER TABLE `insurance_data` MODIFY `date` date NULL;
SET @currentSQLMode = (SELECT @@sql_mode);
SET sql_mode = '';
UPDATE `insurance_data` SET `date` = NULL WHERE `date` = '0000-00-00';
SET sql_mode = @currentSQLMode;
#EndIf

#IfNotColumnTypeDefault onsite_documents patient_signed_time datetime NULL
ALTER TABLE `onsite_documents` MODIFY `patient_signed_time` datetime NULL;
SET @currentSQLMode = (SELECT @@sql_mode);
SET sql_mode = '';
UPDATE `onsite_documents` SET `patient_signed_time` = NULL WHERE `patient_signed_time` = '0000-00-00 00:00:00';
SET sql_mode = @currentSQLMode;
#EndIf

#IfNotColumnTypeDefault onsite_documents review_date datetime NULL
ALTER TABLE `onsite_documents` MODIFY `review_date` datetime NULL;
SET @currentSQLMode = (SELECT @@sql_mode);
SET sql_mode = '';
UPDATE `onsite_documents` SET `review_date` = NULL WHERE `review_date` = '0000-00-00 00:00:00';
SET sql_mode = @currentSQLMode;
#EndIf

#IfMissingColumn api_token token_api
ALTER TABLE `api_token` ADD `token_api` varchar(40);
#EndIf

#IfMissingColumn api_token patient_id
ALTER TABLE `api_token` ADD `patient_id` bigint(20) NOT NULL;
#EndIf

-- Note the below block will also be skipped if the uuid_registry table does not yet exist
#IfNotColumnType uuid_registry uuid binary(16)
DROP TABLE `uuid_registry`;
ALTER TABLE `patient_data` DROP `uuid`;
#EndIf

-- Note the below block will also be skipped if the patient_data uuid does not yet exist
#IfNotColumnTypeDefault patient_data uuid binary(16) NULL
ALTER TABLE `patient_data` DROP `uuid`;
#EndIf

#IfNotTable uuid_registry
CREATE TABLE `uuid_registry` (
  `uuid` binary(16) NOT NULL DEFAULT '',
  `table_name` varchar(255) NOT NULL DEFAULT '',
  `created` timestamp NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn uuid_registry table_id
ALTER TABLE `uuid_registry` ADD `table_id` varchar(255) NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn uuid_registry couchdb
ALTER TABLE `uuid_registry` ADD `couchdb` varchar(255) NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn uuid_registry mapped
ALTER TABLE `uuid_registry` ADD `mapped` tinyint(4) NOT NULL DEFAULT '0';
#EndIf

#IfMissingColumn uuid_registry document_drive
ALTER TABLE `uuid_registry` ADD `document_drive` tinyint(4) NOT NULL DEFAULT '0';
#EndIf

#IfMissingColumn patient_data uuid
ALTER TABLE `patient_data` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex patient_data uuid
CREATE UNIQUE INDEX `uuid` ON `patient_data` (`uuid`);
#EndIf

#IfUuidNeedUpdate patient_data
#EndIf

#IfNotColumnTypeDefault insurance_data subscriber_DOB date NULL
ALTER TABLE `insurance_data` MODIFY `subscriber_DOB` date NULL;
SET @currentSQLMode = (SELECT @@sql_mode);
SET sql_mode = '';
UPDATE `insurance_data` SET `subscriber_DOB` = NULL WHERE `subscriber_DOB` = '0000-00-00';
SET sql_mode = @currentSQLMode;
#EndIf

SET @currentSQLMode = (SELECT @@sql_mode);
SET sql_mode = '';
#IfRow insurance_data date 0000-00-00
UPDATE `insurance_data` SET `date` = NULL WHERE `date` = '0000-00-00';
#EndIf
SET sql_mode = @currentSQLMode;

#IfNotColumnType api_token token_api varchar(4)
ALTER TABLE `api_token` MODIFY `token_api` varchar(4);
#EndIf

-- Note removing all data from api_token table in case legacy stuff gets in way
--  and to ensure will not break when add the unique index below
#IfNotColumnType api_token token varchar(40)
TRUNCATE TABLE api_token;
ALTER TABLE `api_token` MODIFY `token` varchar(40) DEFAULT NULL;
#EndIf

#IfNotIndex api_token token
CREATE UNIQUE INDEX `token` ON `api_token` (`token`);
#EndIf

#IfNotIndex patient_access_onsite pid
CREATE UNIQUE INDEX `pid` ON `patient_access_onsite` (`pid`);
#EndIf

#IfMissingColumn form_encounter uuid
ALTER TABLE `form_encounter` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex form_encounter uuid
CREATE UNIQUE INDEX `uuid` ON `form_encounter` (`uuid`);
#EndIf

#IfUuidNeedUpdate form_encounter
#EndIf

#IfMissingColumn form_encounter class_code
ALTER TABLE `form_encounter` ADD `class_code` VARCHAR(10) NOT NULL  DEFAULT "AMB";
#EndIf

#IfNotRow2D list_options list_id lists option_id _ActEncounterCode
INSERT INTO list_options (list_id, option_id, title, seq) VALUES ('lists', '_ActEncounterCode', 'Value Set ActEncounterCode', 1);
#EndIf

#IfNotRow list_options list_id _ActEncounterCode
INSERT INTO list_options(list_id,option_id,title,notes,seq,is_default) VALUES ('_ActEncounterCode', 'AMB', 'Outpatient', 'ambulatory', 10, 1);
INSERT INTO list_options(list_id,option_id,title,notes,seq) VALUES ('_ActEncounterCode','EMER','Emergency Dept','emergency',20);
INSERT INTO list_options(list_id,option_id,title,notes,seq) VALUES ('_ActEncounterCode','FLD','Out in Field','field',30);
INSERT INTO list_options(list_id,option_id,title,notes,seq) VALUES ('_ActEncounterCode','HH','Home Health','home health',40);
INSERT INTO list_options(list_id,option_id,title,notes,seq) VALUES ('_ActEncounterCode','IMP','Inpatient Encounter','inpatient encounter',50);
INSERT INTO list_options(list_id,option_id,title,notes,seq) VALUES ('_ActEncounterCode','ACUTE','Inpatient Acute','inpatient acute',60);
INSERT INTO list_options(list_id,option_id,title,notes,seq) VALUES ('_ActEncounterCode','NONAC','Inpatient Non-Acute','inpatient non-acute',70);
INSERT INTO list_options(list_id,option_id,title,notes,seq) VALUES ('_ActEncounterCode','OBSENC','Observation Encounter','observation encounter',80);
INSERT INTO list_options(list_id,option_id,title,notes,seq) VALUES ('_ActEncounterCode','PRENC','Pre-Admission','pre-admission',90);
INSERT INTO list_options(list_id,option_id,title,notes,seq) VALUES ('_ActEncounterCode','SS','Short Stay','short stay',100);
INSERT INTO list_options(list_id,option_id,title,notes,seq) VALUES ('_ActEncounterCode','VR','Virtual Encounter','virtual',110);
#EndIf

#IfTable patient_access_offsite
DROP TABLE `patient_access_offsite`;
#EndIf

#IfNotTable session_tracker
CREATE TABLE `session_tracker` (
  `uuid` binary(16) NOT NULL DEFAULT '',
  `created` timestamp NULL,
  `last_updated` timestamp NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB;
#EndIf

#IfColumn product_registration registration_id
ALTER TABLE `product_registration` DROP COLUMN `registration_id`;
#EndIf

#IfMissingColumn product_registration id
ALTER TABLE `product_registration` ADD `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT;
#EndIf

#IfMissingColumn users portal_user
ALTER TABLE `users` ADD `portal_user` TINYINT(1) NOT NULL DEFAULT '0';
#EndIf

#IfMissingColumn users supervisor_id
ALTER TABLE `users` ADD `supervisor_id` INT(11) NOT NULL DEFAULT '0';
#EndIf

#IfMissingColumn users uuid
ALTER TABLE `users` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex users uuid
CREATE UNIQUE INDEX `uuid` ON `users` (`uuid`);
#EndIf

#IfUuidNeedUpdate users
#EndIf

#IfMissingColumn uuid_registry table_vertical
ALTER TABLE `uuid_registry` ADD `table_vertical` varchar(255) NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn facility_user_ids uuid
ALTER TABLE `facility_user_ids` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex facility_user_ids uuid
CREATE INDEX `uuid` ON `facility_user_ids` (`uuid`);
#EndIf

#IfUuidNeedUpdateVertical facility_user_ids uid:facility_id
#EndIf

#IfMissingColumn facility uuid
ALTER TABLE `facility` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex facility uuid
CREATE UNIQUE INDEX `uuid` ON `facility` (`uuid`);
#EndIf

#IfUuidNeedUpdate facility
#EndIf

#IfNotRow codes code_text respiratory syncytial virus monoclonal antibody (motavizumab), intramuscular
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "seasonal influenza, intradermal, preservative free", "influenza, seasonal, intradermal, preservative free", 144, @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "respiratory syncytial virus monoclonal antibody (motavizumab), intramuscular", "RSV-MAb (new)", 145,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Diphtheria and Tetanus Toxoids and Acellular Pertussis Adsorbed, Inactivated Poliovirus, Haemophilus b Conjugate (Meningococcal Protein Conjugate), and Hepatitis B (Recombinant) Vaccine.", "DTaP,IPV,Hib,HepB", 146,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Meningococcal, MCV4, unspecified conjugate formulation(groups A, C, Y and W-135)", "meningococcal MCV4, unspecified formulation", 147,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Meningococcal Groups C and Y and Haemophilus b Tetanus Toxoid Conjugate Vaccine", "Meningococcal C/Y-HIB PRP", 148,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "influenza, live, intranasal, quadrivalent", "influenza, live, intranasal, quadrivalent", 149,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, injectable, quadrivalent, preservative free", "influenza, injectable, quadrivalent, preservative free", 150,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "influenza nasal, unspecified formulation", "influenza nasal, unspecified formulation", 151,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Pneumococcal Conjugate, unspecified formulation", "Pneumococcal Conjugate, unspecified formulation", 152,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, injectable, Madin Darby Canine Kidney, preservative free", "Influenza, injectable, MDCK, preservative free", 153,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Hepatitis A immune globulin", "Hep A, IG", 154,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Seasonal, trivalent, recombinant, injectable influenza vaccine, preservative free", "influenza, recombinant, injectable, preservative free", 155,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Rho(D) Immune globulin- IV or IM", "Rho(D)-IG", 156,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Rho(D) Immune globulin - IM", "Rho(D) -IG IM", 157,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "influenza, injectable, quadrivalent, contains preservative", "influenza, injectable, quadrivalent", 158,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Rho(D) Unspecified formulation", "Rho(D) - Unspecified formulation", 159,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Influenza A monovalent (H5N1), adjuvanted, National stockpile 2013", "Influenza A monovalent (H5N1), ADJUVANTED-2013", 160,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, injectable,quadrivalent, preservative free, pediatric", "Influenza, injectable,quadrivalent, preservative free, pediatric", 161,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal B vaccine, fully recombinant", "meningococcal B, recombinant", 162,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal B vaccine, recombinant, OMV, adjuvanted", "meningococcal B, OMV", 163,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal B, unspecified formulation", "meningococcal B, unspecified", 164,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Human Papillomavirus 9-valent vaccine", "HPV9", 165,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "influenza, intradermal, quadrivalent, preservative free, injectable", "influenza, intradermal, quadrivalent, preservative free", 166,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal vaccine of unknown formulation and unknown serogroups", "meningococcal, unknown serogroups", 167,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Seasonal trivalent influenza vaccine, adjuvanted, preservative free", "influenza, trivalent, adjuvanted", 168,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Hep A, live attenuated-IM", "Hep A, live attenuated", 169,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "non-US diphtheria, tetanus toxoids and acellular pertussis vaccine, Haemophilus influenzae type b conjugate, and poliovirus vaccine, inactivated (DTaP-Hib-IPV)", "DTAP/IPV/HIB - non-US", 170,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, injectable, Madin Darby Canine Kidney, preservative free, quadrivalent", "Influenza, injectable, MDCK, preservative free, quadrivalent", 171,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "cholera, WC-rBS", "cholera, WC-rBS", 172,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "cholera, BivWC", "cholera, BivWC", 173,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "cholera, live attenuated", "cholera, live attenuated", 174,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Human Rabies vaccine from human diploid cell culture", "Rabies - IM Diploid cell culture", 175,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Human rabies vaccine from Chicken fibroblast culture", "Rabies - IM fibroblast culture", 176,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "pneumococcal conjugate vaccine, 10 valent", "PCV10", 177,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Non-US bivalent oral polio vaccine (types 1 and 3)", "OPV bivalent", 178,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Non-US monovalent oral polio vaccine, unspecified formulation", "OPV ,monovalent, unspecified", 179,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "tetanus immune globulin", "tetanus immune globulin", 180,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "anthrax immune globulin", "anthrax immune globulin", 181,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Oral Polio Vaccine, Unspecified formulation", "OPV, Unspecified", 182,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Yellow fever vaccine alternative formulation", "Yellow fever vaccine - alt", 183,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Yellow fever vaccine, unspecified formulation", "Yellow fever, unspecified formulation", 184,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Seasonal, quadrivalent, recombinant, injectable influenza vaccine, preservative free", "influenza, recombinant, quadrivalent,injectable, preservative free", 185,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, injectable, Madin Darby Canine Kidney, quadrivalent with preservative", "Influenza, injectable, MDCK, quadrivalent, preservative", 186,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "zoster vaccine recombinant", "zoster recombinant", 187,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "zoster vaccine, unspecified formulation", "zoster, unspecified formulation", 188,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Hepatitis B vaccine (recombinant), CpG adjuvanted", "HepB-CpG", 189,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Typhoid conjugate vaccine (non-US)", "Typhoid conjugate vaccine (TCV)", 190,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal A polysaccharide vaccine (non-US)", "meningococcal A polysaccharide (non-US)", 191,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal AC polysaccharide vaccine (non-US)", "meningococcal AC polysaccharide (non-US)", 192,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "hepatitis A and hepatitis B vaccine, pediatric/adolescent (non-US)", "Hep A-Hep B, pediatric/adolescent", 193,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, Southern Hemisphere, unspecified formulation (Non-US)", "Influenza, Southern Hemisphere", 194,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Diphtheria, Tetanus, Poliomyelitis adsorbed", "DT, IPV adsorbed", 195,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "tetanus and diphtheria toxoids, adsorbed, preservative free, for adult use, Lf unspecified", "Td, adsorbed, preservative free, adult use, Lf unspecified", 196,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "influenza, high-dose seasonal, quadrivalent, preservative free", "influenza, high-dose, quadrivalent", 197,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "Diphtheria, pertussis, tetanus, hepatitis B, Haemophilus Influenza Type b, (Pentavalent)", "DTP-hepB-Hib Pentavalent Non-US", 198,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "influenza, seasonal, Southern Hemisphere, quadrivalent, pediatric 0.25mL dose, preservative free", "influenza, Southern Hemisphere, pediatric, preservative free", 200,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "influenza, seasonal, Southern Hemisphere, quadrivalent, 0.5mL dose, no preservative", "influenza, Southern Hemisphere, preservative free", 201,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "influenza, seasonal, Southern Hemisphere, quadrivalent, 0.5mL dose, with preservative", "influenza, Southern Hemisphere, quadrivalent, with preservative", 202,  @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "AS03 Adjuvant", "AS03 Adjuvant", 801,  @codetypeid, '', 0, 0, '', '', '', 1);
UPDATE `codes` SET `code_text` = "trivalent poliovirus vaccine, live, oral" WHERE `code` = '2' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text` = "tetanus and diphtheria toxoids, adsorbed, preservative free, for adult use (2 Lf of tetanus toxoid and 2 Lf of diphtheria toxoid)"  WHERE `code` = '9' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text_short` = "Td (adult), 2 Lf tetanus toxoid, preservative free, adsorbed" WHERE `code` = '9' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text` = "rabies vaccine, for intramuscular injection RETIRED CODE" WHERE `code` = '18' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text` = "cholera vaccine, unspecified formulation" WHERE `code` = '26' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text_short` = "cholera, unspecified formulation" WHERE `code` = '26' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text` = "meningococcal ACWY vaccine, unspecified formulation" WHERE `code` = '108' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text_short` = "meningococcal ACWY, unspecified formulation" WHERE `code` = '108' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text` = "tetanus and diphtheria toxoids, adsorbed, preservative free, for adult use (5 Lf of tetanus toxoid and 2 Lf of diphtheria toxoid)" WHERE `code` = '113' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text_short` = "Td (adult), 5 Lf tetanus toxoid, preservative free, adsorbed" WHERE `code` = '113' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text_short` = "zoster live" WHERE `code` = '121' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text` = "Historical diphtheria and tetanus toxoids and acellular pertussis, poliovirus, Haemophilus b conjugate and hepatitis B (recombinant) vaccine." WHERE `code` = '132' AND `code_type` = @codetypeid;
UPDATE `codes` SET `code_text_short` = "DTaP-IPV-HIB-HEP B, historical" WHERE `code` = '132' AND `code_type` = @codetypeid;
#EndIf

#IfNotRow2D layout_options form_id FACUSR field_id role_code
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('FACUSR', 'role_code', '1', 'Provider Role', 2, 43, 1, 0, 0, 'us-core-provider-role', 1, 1, '', '', 'Provider Role at Specified Facility', 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id us-core-provider-role
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq` ) VALUES ('lists' ,'us-core-provider-role', 'US Core Provider Role', 1);
#EndIf

#IfNotRow list_options list_id us-core-provider-role
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '102L00000X', 'Psychoanalyst', 20);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '102X00000X', 'Poetry Therapist', 30);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '103G00000X', 'Clinical Neuropsychologist', 40);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '103K00000X', 'Behavior Analyst', 50);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '103T00000X', 'Psychologist', 60);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '104100000X', 'Social Worker', 70);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '106E00000X', 'Assistant Behavior Analyst', 80);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '106H00000X', 'Marriage & Family Therapist', 90);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '106S00000X', 'Behavior Technician', 100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '111N00000X', 'Chiropractor', 110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '122300000X', 'Dentist', 120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '122400000X', 'Denturist', 130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '124Q00000X', 'Dental Hygienist', 140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '125J00000X', 'Dental Therapist', 150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '125K00000X', 'Advanced Practice Dental Therapist', 160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '125Q00000X', 'Oral Medicinist', 170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '126800000X', 'Dental Assistant', 180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '126900000X', 'Dental Laboratory Technician', 190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '132700000X', 'Dietary Manager', 200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '133N00000X', 'Nutritionist', 210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '133V00000X', 'Dietitian, Registered', 220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '136A00000X', 'Dietetic Technician, Registered', 230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '146D00000X', 'Personal Emergency Response Attendant', 240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '146L00000X', 'Emergency Medical Technician, Paramedic', 250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '146M00000X', 'Emergency Medical Technician, Intermediate', 260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '146N00000X', 'Emergency Medical Technician, Basic', 270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '152W00000X', 'Optometrist', 280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '156F00000X', 'Technician/Technologist', 290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '163W00000X', 'Registered Nurse', 300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '164W00000X', 'Licensed Practical Nurse', 310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '164X00000X', 'Licensed Vocational Nurse', 320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '167G00000X', 'Licensed Psychiatric Technician', 330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '170100000X', 'Medical Genetics, Ph.D. Medical Genetics', 340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '170300000X', 'Genetic Counselor, MS', 350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '171000000X', 'Military Health Care Provider', 360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '171100000X', 'Acupuncturist', 370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '171M00000X', 'Case Manager/Care Coordinator', 380);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '171R00000X', 'Interpreter', 390);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '171W00000X', 'Contractor', 400);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '172A00000X', 'Driver', 410);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '172M00000X', 'Mechanotherapist', 420);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '172P00000X', 'Naprapath', 430);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '172V00000X', 'Community Health Worker', 440);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '173000000X', 'Legal Medicine', 450);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '173C00000X', 'Reflexologist', 460);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '173F00000X', 'Sleep Specialist, PhD', 470);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '174200000X', 'Meals', 480);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '174400000X', 'Specialist', 490);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '174H00000X', 'Health Educator', 500);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '174M00000X', 'Veterinarian', 510);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '174N00000X', 'Lactation Consultant, Non-RN', 520);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '174V00000X', 'Clinical Ethicist', 530);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '175F00000X', 'Naturopath', 540);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '175L00000X', 'Homeopath', 550);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '175M00000X', 'Midwife, Lay', 560);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '175T00000X', 'Peer Specialist', 570);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '176B00000X', 'Midwife', 580);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '176P00000X', 'Funeral Director', 590);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '177F00000X', 'Lodging', 600);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '183500000X', 'Pharmacist', 610);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '183700000X', 'Pharmacy Technician', 620);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '193200000X', 'Multi-Specialty', 630);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '193400000X', 'Single Specialty', 640);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '202C00000X', 'Independent Medical Examiner', 650);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '202K00000X', 'Phlebology', 660);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '204C00000X', 'Neuromusculoskeletal Medicine, Sports Medicine', 670);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '204D00000X', 'Neuromusculoskeletal Medicine & OMM', 680);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '204E00000X', 'Oral & Maxillofacial Surgery', 690);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '204F00000X', 'Transplant Surgery', 700);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '204R00000X', 'Electrodiagnostic Medicine', 710);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207K00000X', 'Allergy & Immunology', 720);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207L00000X', 'Anesthesiology', 730);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207N00000X', 'Dermatology', 740);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207P00000X', 'Emergency Medicine', 750);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207Q00000X', 'Family Medicine', 760);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207R00000X', 'Internal Medicine', 770);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207T00000X', 'Neurological Surgery', 780);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207U00000X', 'Nuclear Medicine', 790);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207V00000X', 'Obstetrics & Gynecology', 800);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207W00000X', 'Ophthalmology', 810);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207X00000X', 'Orthopaedic Surgery', 820);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '207Y00000X', 'Otolaryngology', 830);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '208000000X', 'Pediatrics', 840);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '208100000X', 'Physical Medicine & Rehabilitation', 850);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '208200000X', 'Plastic Surgery', 860);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '208600000X', 'Surgery', 870);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '208800000X', 'Urology', 880);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '208C00000X', 'Colon & Rectal Surgery', 890);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '208D00000X', 'General Practice', 900);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '208G00000X', 'Thoracic Surgery (Cardiothoracic Vascular Surgery)', 910);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '208M00000X', 'Hospitalist', 920);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '208U00000X', 'Clinical Pharmacology', 930);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '209800000X', 'Legal Medicine', 940);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '211D00000X', 'Assistant, Podiatric', 950);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '213E00000X', 'Podiatrist', 960);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '221700000X', 'Art Therapist', 970);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '222Q00000X', 'Developmental Therapist', 980);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '222Z00000X', 'Orthotist', 990);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '224900000X', 'Mastectomy Fitter', 1000);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '224L00000X', 'Pedorthist', 1010);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '224P00000X', 'Prosthetist', 1020);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '224Y00000X', 'Clinical Exercise Physiologist', 1030);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '224Z00000X', 'Occupational Therapy Assistant', 1040);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225000000X', 'Orthotic Fitter', 1050);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225100000X', 'Physical Therapist', 1060);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225200000X', 'Physical Therapy Assistant', 1070);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225400000X', 'Rehabilitation Practitioner', 1080);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225500000X', 'Specialist/Technologist', 1090);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225600000X', 'Dance Therapist', 1100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225700000X', 'Massage Therapist', 1110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225800000X', 'Recreation Therapist', 1120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225A00000X', 'Music Therapist', 1130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225B00000X', 'Pulmonary Function Technologist', 1140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225C00000X', 'Rehabilitation Counselor', 1150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '225X00000X', 'Occupational Therapist', 1160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '226000000X', 'Recreational Therapist Assistant', 1170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '226300000X', 'Kinesiotherapist', 1180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '227800000X', 'Respiratory Therapist, Certified', 1190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '227900000X', 'Respiratory Therapist, Registered', 1200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '229N00000X', 'Anaplastologist', 1210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '231H00000X', 'Audiologist', 1220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '235500000X', 'Specialist/Technologist', 1230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '235Z00000X', 'Speech-Language Pathologist', 1240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '237600000X', 'Audiologist-Hearing Aid Fitter', 1250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '237700000X', 'Hearing Instrument Specialist', 1260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '242T00000X', 'Perfusionist', 1270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '243U00000X', 'Radiology Practitioner Assistant', 1280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '246Q00000X', 'Specialist/Technologist, Pathology', 1290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '246R00000X', 'Technician, Pathology', 1300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '246W00000X', 'Technician, Cardiology', 1310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '246X00000X', 'Specialist/Technologist Cardiovascular', 1320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '246Y00000X', 'Specialist/Technologist, Health Information', 1330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '246Z00000X', 'Specialist/Technologist, Other', 1340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '247000000X', 'Technician, Health Information', 1350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '247100000X', 'Radiologic Technologist', 1360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '247200000X', 'Technician, Other', 1370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251300000X', 'Local Education Agency (LEA)', 1380);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251B00000X', 'Case Management', 1390);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251C00000X', 'Day Training, Developmentally Disabled Services', 1400);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251E00000X', 'Home Health', 1410);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251F00000X', 'Home Infusion', 1420);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251G00000X', 'Hospice Care, Community Based', 1430);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251J00000X', 'Nursing Care', 1440);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251K00000X', 'Public Health or Welfare', 1450);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251S00000X', 'Community/Behavioral Health', 1460);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251T00000X', 'Program of All-Inclusive Care for the Elderly (PACE) Provider Organization', 1470);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251V00000X', 'Voluntary or Charitable', 1480);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '251X00000X', 'Supports Brokerage', 1490);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '252Y00000X', 'Early Intervention Provider Agency', 1500);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '253J00000X', 'Foster Care Agency', 1510);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '253Z00000X', 'In Home Supportive Care', 1520);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '261Q00000X', 'Clinic/Center', 1530);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '273100000X', 'Epilepsy Unit', 1540);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '273R00000X', 'Psychiatric Unit', 1550);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '273Y00000X', 'Rehabilitation Unit', 1560);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '275N00000X', 'Medicare Defined Swing Bed Unit', 1570);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '276400000X', 'Rehabilitation, Substance Use Disorder Unit', 1580);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '281P00000X', 'Chronic Disease Hospital', 1590);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '282E00000X', 'Long Term Care Hospital', 1600);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '282J00000X', 'Religious Nonmedical Health Care Institution', 1610);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '282N00000X', 'General Acute Care Hospital', 1620);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '283Q00000X', 'Psychiatric Hospital', 1630);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '283X00000X', 'Rehabilitation Hospital', 1640);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '284300000X', 'Special Hospital', 1650);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '286500000X', 'Military Hospital', 1660);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '287300000X', 'Christian Science Sanitorium', 1670);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '291900000X', 'Military Clinical Medical Laboratory', 1680);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '291U00000X', 'Clinical Medical Laboratory', 1690);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '292200000X', 'Dental Laboratory', 1700);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '293D00000X', 'Physiological Laboratory', 1710);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '302F00000X', 'Exclusive Provider Organization', 1720);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '302R00000X', 'Health Maintenance Organization', 1730);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '305R00000X', 'Preferred Provider Organization', 1740);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '305S00000X', 'Point of Service', 1750);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '310400000X', 'Assisted Living Facility', 1760);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '310500000X', 'Intermediate Care Facility, Mental Illness', 1770);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '311500000X', 'Alzheimer Center (Dementia Center)', 1780);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '311Z00000X', 'Custodial Care Facility', 1790);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '313M00000X', 'Nursing Facility/Intermediate Care Facility', 1800);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '314000000X', 'Skilled Nursing Facility', 1810);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '315D00000X', 'Hospice, Inpatient', 1820);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '315P00000X', 'Intermediate Care Facility, Mentally Retarded', 1830);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '317400000X', 'Christian Science Facility', 1840);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '320600000X', 'Residential Treatment Facility, Mental Retardation and/or Developmental Disabilities', 1850);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '320700000X', 'Residential Treatment Facility, Physical Disabilities', 1860);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '320800000X', 'Community Based Residential Treatment Facility, Mental Illness', 1870);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '320900000X', 'Community Based Residential Treatment Facility, Mental Retardation and/or Developmental Disabilities', 1880);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '322D00000X', 'Residential Treatment Facility, Emotionally Disturbed Children', 1890);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '323P00000X', 'Psychiatric Residential Treatment Facility', 1900);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '324500000X', 'Substance Abuse Rehabilitation Facility', 1910);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '331L00000X', 'Blood Bank', 1920);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '332000000X', 'Military/U.S. Coast Guard Pharmacy', 1930);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '332100000X', 'Department of Veterans Affairs (VA) Pharmacy', 1940);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '332800000X', 'Indian Health Service/Tribal/Urban Indian Health (I/T/U) Pharmacy', 1950);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '332900000X', 'Non-Pharmacy Dispensing Site', 1960);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '332B00000X', 'Durable Medical Equipment & Medical Supplies', 1970);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '332G00000X', 'Eye Bank', 1980);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '332H00000X', 'Eyewear Supplier', 1990);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '332S00000X', 'Hearing Aid Equipment', 2000);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '332U00000X', 'Home Delivered Meals', 2010);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '333300000X', 'Emergency Response System Companies', 2020);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '333600000X', 'Pharmacy', 2030);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '335E00000X', 'Prosthetic/Orthotic Supplier', 2040);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '335G00000X', 'Medical Foods Supplier', 2050);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '335U00000X', 'Organ Procurement Organization', 2060);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '335V00000X', 'Portable X-ray and/or Other Portable Diagnostic Imaging Supplier', 2070);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '341600000X', 'Ambulance', 2080);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '341800000X', 'Military/U.S. Coast Guard Transport', 2090);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '343800000X', 'Secured Medical Transport (VAN)', 2100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '343900000X', 'Non-emergency Medical Transport (VAN)', 2110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '344600000X', 'Taxi', 2120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '344800000X', 'Air Carrier', 2130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '347B00000X', 'Bus', 2140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '347C00000X', 'Private Vehicle', 2150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '347D00000X', 'Train', 2160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '347E00000X', 'Transportation Broker', 2170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '363A00000X', 'Physician Assistant', 2180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '363L00000X', 'Nurse Practitioner', 2190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '364S00000X', 'Clinical Nurse Specialist', 2200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '367500000X', 'Nurse Anesthetist, Certified Registered', 2210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '367A00000X', 'Advanced Practice Midwife', 2220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '367H00000X', 'Anesthesiologist Assistant', 2230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '372500000X', 'Chore Provider', 2240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '372600000X', 'Adult Companion', 2250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '373H00000X', 'Day Training/Habilitation Specialist', 2260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '374700000X', 'Technician', 2270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '374J00000X', 'Doula', 2280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '374K00000X', 'Religious Nonmedical Practitioner', 2290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '374T00000X', 'Religious Nonmedical Nursing Personnel', 2300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '374U00000X', 'Home Health Aide', 2310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '376G00000X', 'Nursing Home Administrator', 2320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '376J00000X', 'Homemaker', 2330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '376K00000X', "Nurse's Aide", 2340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '385H00000X', 'Respite Care', 2350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '390200000X', 'Student in an Organized Health Care Education/Training Program', 2360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '405300000X', 'Prevention Professional', 2370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '101Y00000X', 'Counselor', 2380);
#EndIf

#IfNotRow2D layout_options form_id FACUSR field_id specialty_code
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('FACUSR', 'specialty_code', '1', 'Provider Specialty', 3, 43, 1, 0, 0, 'us-core-provider-specialty', 1, 1, '', '', 'Provider Specialty at Specified Facility', 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id us-core-provider-specialty
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq` ) VALUES ('lists' ,'us-core-provider-specialty', 'US Core Provider Specialty', 1);
#EndIf

#IfNotRow list_options list_id us-core-provider-specialty
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "101Y00000X", "Counselor", 10);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "101YA0400X", "Addiction (Substance Use Disorder)", 20);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "101YM0800X", "Mental Health", 30);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "101YP1600X", "Pastoral", 40);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "101YP2500X", "Professional", 50);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "101YS0200X", "School", 60);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "102L00000X", "Psychoanalyst", 70);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "102X00000X", "Poetry Therapist", 80);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103G00000X", "Clinical Neuropsychologist", 90);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103GC0700X", "Clinical", 100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103K00000X", "Behavioral Analyst", 110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103T00000X", "Psychologist", 120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TA0400X", "Addiction (Substance Use Disorder)", 130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TA0700X", "Adult Development & Aging", 140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TB0200X", "Cognitive & Behavioral", 150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TC0700X", "Clinical", 160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TC1900X", "Counseling", 170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TC2200X", "Clinical Child & Adolescent", 180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TE1000X", "Educational", 190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TE1100X", "Exercise & Sports", 200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TF0000X", "Family", 210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TF0200X", "Forensic", 220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TH0004X", "Health", 230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TH0100X", "Health Service", 240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TM1700X", "Men & Masculinity", 250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TM1800X", "Mental Retardation & Developmental Disabilities", 260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TP0016X", "Prescribing (Medical)", 270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TP0814X", "Psychoanalysis", 280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TP2700X", "Psychotherapy", 290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TP2701X", "Group Psychotherapy", 300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TR0400X", "Rehabilitation", 310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TS0200X", "School", 320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "103TW0100X", "Women", 330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "104100000X", "Social Worker", 340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1041C0700X", "Clinical", 350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1041S0200X", "School", 360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "106E00000X", "Assistant Behavior Analyst", 370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "106H00000X", "Marriage & Family Therapist", 380);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "106S00000X", "Behavior Technician", 390);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111N00000X", "Chiropractor", 400);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111NI0013X", "Independent Medical Examiner", 410);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111NI0900X", "Internist", 420);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111NN0400X", "Neurology", 430);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111NN1001X", "Nutrition", 440);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111NP0017X", "Pediatric Chiropractor", 450);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111NR0200X", "Radiology", 460);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111NR0400X", "Rehabilitation", 470);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111NS0005X", "Sports Physician", 480);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111NT0100X", "Thermography", 490);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111NX0100X", "Occupational Health", 500);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "111NX0800X", "Orthopedic", 510);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "122300000X", "Dentist", 520);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1223D0001X", "Dental Public Health", 530);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1223D0004X", "Dentist Anesthesiologist", 540);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1223E0200X", "Endodontics", 550);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1223G0001X", "General Practice", 560);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1223P0106X", "Oral and Maxillofacial Pathology", 570);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1223P0221X", "Pediatric Dentistry", 580);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1223P0300X", "Periodontics", 590);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1223P0700X", "Prosthodontics", 600);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1223S0112X", "Oral and Maxillofacial Surgery", 610);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1223X0008X", "Oral and Maxillofacial Radiology", 620);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1223X0400X", "Orthodontics and Dentofacial Orthopedics", 630);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "122400000X", "Denturist", 640);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "124Q00000X", "Dental Hygienist", 650);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "125J00000X", "Dental Therapist", 660);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "125K00000X", "Advanced Practice Dental Therapist", 670);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "125Q00000X", "Oral Medicinist", 680);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "126800000X", "Dental Assistant", 690);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "126900000X", "Dental Laboratory Technician", 700);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "132700000X", "Dietary Manager", 710);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "133N00000X", "Nutritionist", 720);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "133NN1002X", "Nutrition, Education", 730);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "133V00000X", "Dietitian, Registered", 740);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "133VN1004X", "Nutrition, Pediatric", 750);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "133VN1005X", "Nutrition, Renal", 760);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "133VN1006X", "Nutrition, Metabolic", 770);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "136A00000X", "Dietetic Technician, Registered", 780);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "146D00000X", "Personal Emergency Response Attendant", 790);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "146L00000X", "Emergency Medical Technician, Paramedic", 800);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "146M00000X", "Emergency Medical Technician, Intermediate", 810);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "146N00000X", "Emergency Medical Technician, Basic", 820);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "152W00000X", "Optometrist", 830);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "152WC0802X", "Corneal and Contact Management", 840);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "152WL0500X", "Low Vision Rehabilitation", 850);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "152WP0200X", "Pediatrics", 860);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "152WS0006X", "Sports Vision", 870);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "152WV0400X", "Vision Therapy", 880);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "152WX0102X", "Occupational Vision", 890);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "156F00000X", "Technician/Technologist", 900);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "156FC0800X", "Contact Lens", 910);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "156FC0801X", "Contact Lens Fitter", 920);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "156FX1100X", "Ophthalmic", 930);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "156FX1101X", "Ophthalmic Assistant", 940);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "156FX1201X", "Optometric Assistant", 950);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "156FX1202X", "Optometric Technician", 960);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "156FX1700X", "Ocularist", 970);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "156FX1800X", "Optician", 980);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "156FX1900X", "Orthoptist", 990);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163W00000X", "Registered Nurse", 1000);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WA0400X", "Addiction (Substance Use Disorder)", 1010);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WA2000X", "Administrator", 1020);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WC0200X", "Critical Care Medicine", 1030);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WC0400X", "Case Management", 1040);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WC1400X", "College Health", 1050);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WC1500X", "Community Health", 1060);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WC1600X", "Continuing Education/Staff Development", 1070);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WC2100X", "Continence Care", 1080);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WC3500X", "Cardiac Rehabilitation", 1090);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WD0400X", "Diabetes Educator", 1100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WD1100X", "Dialysis, Peritoneal", 1110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WE0003X", "Emergency", 1120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WE0900X", "Enterostomal Therapy", 1130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WF0300X", "Flight", 1140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WG0000X", "General Practice", 1150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WG0100X", "Gastroenterology", 1160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WG0600X", "Gerontology", 1170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WH0200X", "Home Health", 1180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WH0500X", "Hemodialysis", 1190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WH1000X", "Hospice", 1200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WI0500X", "Infusion Therapy", 1210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WI0600X", "Infection Control", 1220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WL0100X", "Lactation Consultant", 1230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WM0102X", "Maternal Newborn", 1240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WM0705X", "Medical-Surgical", 1250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WM1400X", "Nurse Massage Therapist (NMT)", 1260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WN0002X", "Neonatal Intensive Care", 1270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WN0003X", "Neonatal, Low-Risk", 1280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WN0300X", "Nephrology", 1290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WN0800X", "Neuroscience", 1300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WN1003X", "Nutrition Support", 1310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WP0000X", "Pain Management", 1320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WP0200X", "Pediatrics", 1330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WP0218X", "Pediatric Oncology", 1340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WP0807X", "Psych/Mental Health, Child & Adolescent", 1350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WP0808X", "Psych/Mental Health", 1360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WP0809X", "Psych/Mental Health, Adult", 1370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WP1700X", "Perinatal", 1380);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WP2201X", "Ambulatory Care", 1390);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WR0006X", "Registered Nurse First Assistant", 1400);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WR0400X", "Rehabilitation", 1410);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WR1000X", "Reproductive Endocrinology/Infertility", 1420);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WS0121X", "Plastic Surgery", 1430);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WS0200X", "School", 1440);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WU0100X", "Urology", 1450);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WW0000X", "Wound Care", 1460);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WW0101X", "Women's Health Care, Ambulatory", 1470);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WX0002X", "Obstetric, High-Risk", 1480);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WX0003X", "Obstetric, Inpatient", 1490);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WX0106X", "Occupational Health", 1500);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WX0200X", "Oncology", 1510);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WX0601X", "Otorhinolaryngology & Head-Neck", 1520);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WX0800X", "Orthopedic", 1530);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WX1100X", "Ophthalmic", 1540);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "163WX1500X", "Ostomy Care", 1550);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "164W00000X", "Licensed Practical Nurse", 1560);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "164X00000X", "Licensed Vocational Nurse", 1570);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "167G00000X", "Licensed Psychiatric Technician", 1580);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "170100000X", "Medical Genetics, Ph.D. Medical Genetics", 1590);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "170300000X", "Genetic Counselor, MS", 1600);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "171000000X", "Military Health Care Provider", 1610);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1710I1002X", "Independent Duty Corpsman", 1620);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1710I1003X", "Independent Duty Medical Technicians", 1630);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "171100000X", "Acupuncturist", 1640);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "171M00000X", "Case Manager/Care Coordinator", 1650);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "171R00000X", "Interpreter", 1660);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "171W00000X", "Contractor", 1670);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "171WH0202X", "Home Modifications", 1680);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "171WV0202X", "Vehicle Modifications", 1690);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "172A00000X", "Driver", 1700);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "172M00000X", "Mechanotherapist", 1710);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "172P00000X", "Naprapath", 1720);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "172V00000X", "Community Health Worker", 1730);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "173000000X", "Legal Medicine", 1740);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "173C00000X", "Reflexologist", 1750);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "173F00000X", "Sleep Specialist, PhD", 1760);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "174200000X", "Meals", 1770);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "174400000X", "Specialist", 1780);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1744G0900X", "Graphics Designer", 1790);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1744P3200X", "Prosthetics Case Management", 1800);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1744R1102X", "Research Study", 1810);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1744R1103X", "Research Data Abstracter/Coder", 1820);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "174H00000X", "Health Educator", 1830);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "174M00000X", "Veterinarian", 1840);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "174MM1900X", "Medical Research", 1850);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "174N00000X", "Lactation Consultant, Non-RN", 1860);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "174V00000X", "Clinical Ethicist", 1870);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "175F00000X", "Naturopath", 1880);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "175L00000X", "Homeopath", 1890);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "175M00000X", "Midwife, Lay", 1900);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "175T00000X", "Peer Specialist", 1910);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "176B00000X", "Midwife", 1920);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "176P00000X", "Funeral Director", 1930);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "177F00000X", "Lodging", 1940);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "183500000X", "Pharmacist", 1950);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1835C0205X", "Critical Care", 1960);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1835G0000X", "General Practice", 1970);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1835G0303X", "Geriatric", 1980);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1835N0905X", "Nuclear", 1990);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1835N1003X", "Nutrition Support", 2000);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1835P0018X", "Pharmacist Clinician (PhC)/ Clinical Pharmacy Specialist", 2010);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1835P0200X", "Pediatrics", 2020);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1835P1200X", "Pharmacotherapy", 2030);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1835P1300X", "Psychiatric", 2040);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1835P2201X", "Ambulatory Care", 2050);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "1835X0200X", "Oncology", 2060);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "183700000X", "Pharmacy Technician", 2070);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "193200000X", "Multi-Specialty", 2080);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "193400000X", "Single Specialty", 2090);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "202C00000X", "Independent Medical Examiner", 2100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "202K00000X", "Phlebology", 2110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "204C00000X", "Neuromusculoskeletal Medicine, Sports Medicine", 2120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "204D00000X", "Neuromusculoskeletal Medicine & OMM", 2130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "204E00000X", "Oral & Maxillofacial Surgery", 2140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "204F00000X", "Transplant Surgery", 2150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "204R00000X", "Electrodiagnostic Medicine", 2160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207K00000X", "Allergy & Immunology", 2170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207KA0200X", "Allergy", 2180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207KI0005X", "Clinical & Laboratory Immunology", 2190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207L00000X", "Anesthesiology", 2200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207LA0401X", "Addiction Medicine", 2210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207LC0200X", "Critical Care Medicine", 2220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207LH0002X", "Hospice and Palliative Medicine", 2230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207LP2900X", "Pain Medicine", 2240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207LP3000X", "Pediatric Anesthesiology", 2250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207N00000X", "Dermatology", 2260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ND0101X", "MOHS-Micrographic Surgery", 2270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ND0900X", "Dermatopathology", 2280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207NI0002X", "Clinical & Laboratory Dermatological Immunology", 2290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207NP0225X", "Pediatric Dermatology", 2300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207NS0135X", "Procedural Dermatology", 2310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207P00000X", "Emergency Medicine", 2320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207PE0004X", "Emergency Medical Services", 2330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207PE0005X", "Undersea and Hyperbaric Medicine", 2340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207PH0002X", "Hospice and Palliative Medicine", 2350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207PP0204X", "Pediatric Emergency Medicine", 2360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207PS0010X", "Sports Medicine", 2370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207PT0002X", "Medical Toxicology", 2380);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207Q00000X", "Family Medicine", 2390);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207QA0000X", "Adolescent Medicine", 2400);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207QA0401X", "Addiction Medicine", 2410);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207QA0505X", "Adult Medicine", 2420);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207QB0002X", "Obesity Medicine", 2430);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207QG0300X", "Geriatric Medicine", 2440);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207QH0002X", "Hospice and Palliative Medicine", 2450);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207QS0010X", "Sports Medicine", 2460);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207QS1201X", "Sleep Medicine", 2470);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207R00000X", "Internal Medicine", 2480);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RA0000X", "Adolescent Medicine", 2490);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RA0001X", "Advanced Heart Failure and Transplant Cardiology", 2500);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RA0201X", "Allergy & Immunology", 2510);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RA0401X", "Addiction Medicine", 2520);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RB0002X", "Obesity Medicine", 2530);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RC0000X", "Cardiovascular Disease", 2540);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RC0001X", "Clinical Cardiac Electrophysiology", 2550);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RC0200X", "Critical Care Medicine", 2560);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RE0101X", "Endocrinology, Diabetes & Metabolism", 2570);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RG0100X", "Gastroenterology", 2580);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RG0300X", "Geriatric Medicine", 2590);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RH0000X", "Hematology", 2600);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RH0002X", "Hospice and Palliative Medicine", 2610);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RH0003X", "Hematology & Oncology", 2620);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RH0005X", "Hypertension Specialist", 2630);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RI0001X", "Clinical & Laboratory Immunology", 2640);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RI0008X", "Hepatology", 2650);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RI0011X", "Interventional Cardiology", 2660);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RI0200X", "Infectious Disease", 2670);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RM1200X", "Magnetic Resonance Imaging (MRI)", 2680);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RN0300X", "Nephrology", 2690);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RP1001X", "Pulmonary Disease", 2700);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RR0500X", "Rheumatology", 2710);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RS0010X", "Sports Medicine", 2720);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RS0012X", "Sleep Medicine", 2730);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RT0003X", "Transplant Hepatology", 2740);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207RX0202X", "Medical Oncology", 2750);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207SC0300X", "Medical Genetics", 2760);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207SG0201X", "Clinical Genetics (M.D.)", 2770);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207SG0202X", "Clinical Biochemical Genetics", 2780);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207SG0203X", "Clinical Molecular Genetics", 2790);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207SG0205X", "Ph.D. Medical Genetics", 2800);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207SM0001X", "Molecular Genetic Pathology", 2810);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207T00000X", "Neurological Surgery", 2820);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207U00000X", "Nuclear Medicine", 2830);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207UN0901X", "Nuclear Cardiology", 2840);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207UN0902X", "Nuclear Imaging & Therapy", 2850);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207UN0903X", "In Vivo & In Vitro Nuclear Medicine", 2860);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207V00000X", "Obstetrics & Gynecology", 2870);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207VB0002X", "Obesity Medicine", 2880);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207VC0200X", "Critical Care Medicine", 2890);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207VE0102X", "Reproductive Endocrinology", 2900);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207VF0040X", "Female Pelvic Medicine and Reconstructive Surgery", 2910);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207VG0400X", "Gynecology", 2920);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207VH0002X", "Hospice and Palliative Medicine", 2930);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207VM0101X", "Maternal & Fetal Medicine", 2940);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207VX0000X", "Obstetrics", 2950);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207VX0201X", "Gynecologic Oncology", 2960);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207W00000X", "Ophthalmology", 2970);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207WX0200X", "Ophthalmic Plastic and Reconstructive Surgery", 2980);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207X00000X", "Orthopaedic Surgery", 2990);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207XP3100X", "Pediatric Orthopaedic Surgery", 3000);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207XS0106X", "Hand Surgery", 3010);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207XS0114X", "Adult Reconstructive Orthopaedic Surgery", 3020);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207XS0117X", "Orthopaedic Surgery of the Spine", 3030);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207XX0004X", "Foot and Ankle Surgery", 3040);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207XX0005X", "Sports Medicine", 3050);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207XX0801X", "Orthopaedic Trauma", 3060);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207Y00000X", "Otolaryngology", 3070);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207YP0228X", "Pediatric Otolaryngology", 3080);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207YS0012X", "Sleep Medicine", 3090);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207YS0123X", "Facial Plastic Surgery", 3100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207YX0007X", "Plastic Surgery within the Head & Neck", 3110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207YX0602X", "Otolaryngic Allergy", 3120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207YX0901X", "Otology & Neurotology", 3130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207YX0905X", "Otolaryngology/Facial Plastic Surgery", 3140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZB0001X", "Pathology", 3150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZC0006X", "Clinical Pathology", 3160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZC0008X", "Clinical Informatics", 3170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZC0500X", "Cytopathology", 3180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZD0900X", "Dermatopathology", 3190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZF0201X", "Forensic Pathology", 3200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZH0000X", "Hematology", 3210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZI0100X", "Immunopathology", 3220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZM0300X", "Medical Microbiology", 3230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZN0500X", "Neuropathology", 3240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZP0007X", "Molecular Genetic Pathology", 3250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZP0101X", "Anatomic Pathology", 3260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZP0102X", "Anatomic Pathology & Clinical Pathology", 3270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZP0104X", "Chemical Pathology", 3280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZP0105X", "Clinical Pathology/Laboratory Medicine", 3290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "207ZP0213X", "Pediatric Pathology", 3300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208000000X", "Pediatrics", 3310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080A0000X", "Adolescent Medicine", 3320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080B0002X", "Obesity Medicine", 3330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080C0008X", "Child Abuse Pediatrics", 3340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080H0002X", "Hospice and Palliative Medicine", 3350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080I0007X", "Clinical & Laboratory Immunology", 3360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080N0001X", "Neonatal-Perinatal Medicine", 3370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0006X", "Developmental � Behavioral Pediatrics", 3380);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0008X", "Neurodevelopmental Disabilities", 3390);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0201X", "Pediatric Allergy/Immunology", 3400);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0202X", "Pediatric Cardiology", 3410);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0203X", "Pediatric Critical Care Medicine", 3420);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0204X", "Pediatric Emergency Medicine", 3430);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0205X", "Pediatric Endocrinology", 3440);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0206X", "Pediatric Gastroenterology", 3450);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0207X", "Pediatric Hematology-Oncology", 3460);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0208X", "Pediatric Infectious Diseases", 3470);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0210X", "Pediatric Nephrology", 3480);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0214X", "Pediatric Pulmonology", 3490);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080P0216X", "Pediatric Rheumatology", 3500);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080S0010X", "Sports Medicine", 3510);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080S0012X", "Sleep Medicine", 3520);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080T0002X", "Medical Toxicology", 3530);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2080T0004X", "Pediatric Transplant Hepatology", 3540);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208100000X", "Physical Medicine & Rehabilitation", 3550);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2081H0002X", "Hospice and Palliative Medicine", 3560);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2081N0008X", "Neuromuscular Medicine", 3570);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2081P0004X", "Spinal Cord Injury Medicine", 3580);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2081P0010X", "Pediatric Rehabilitation Medicine", 3590);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2081P0301X", "Brain Injury Medicine", 3600);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2081P2900X", "Pain Medicine", 3610);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2081S0010X", "Sports Medicine", 3620);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208200000X", "Plastic Surgery", 3630);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2082S0099X", "Plastic Surgery Within the Head and Neck", 3640);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2082S0105X", "Surgery of the Hand", 3650);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2083A0100X", "Preventive Medicine", 3660);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2083B0002X", "Obesity Medicine", 3670);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2083C0008X", "Clinical Informatics", 3680);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2083P0011X", "Undersea and Hyperbaric Medicine", 3690);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2083P0500X", "Preventive Medicine/Occupational Environmental Medicine", 3700);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2083P0901X", "Public Health & General Preventive Medicine", 3710);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2083S0010X", "Sports Medicine", 3720);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2083T0002X", "Medical Toxicology", 3730);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2083X0100X", "Occupational Medicine", 3740);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084A0401X", "Psychiatry & Neurology", 3750);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084A2900X", "Neurocritical Care", 3760);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084B0002X", "Obesity Medicine", 3770);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084B0040X", "Behavioral Neurology & Neuropsychiatry", 3780);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084D0003X", "Diagnostic Neuroimaging", 3790);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084F0202X", "Forensic Psychiatry", 3800);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084H0002X", "Hospice and Palliative Medicine", 3810);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084N0008X", "Neuromuscular Medicine", 3820);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084N0400X", "Neurology", 3830);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084N0402X", "Neurology with Special Qualifications in Child Neurology", 3840);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084N0600X", "Clinical Neurophysiology", 3850);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084P0005X", "Neurodevelopmental Disabilities", 3860);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084P0015X", "Psychosomatic Medicine", 3870);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084P0301X", "Brain Injury Medicine", 3880);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084P0800X", "Psychiatry", 3890);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084P0802X", "Addiction Psychiatry", 3900);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084P0804X", "Child & Adolescent Psychiatry", 3910);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084P0805X", "Geriatric Psychiatry", 3920);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084P2900X", "Pain Medicine", 3930);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084S0010X", "Sports Medicine", 3940);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084S0012X", "Sleep Medicine", 3950);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2084V0102X", "Vascular Neurology", 3960);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085B0100X", "Radiology", 3970);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085D0003X", "Diagnostic Neuroimaging", 3980);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085H0002X", "Hospice and Palliative Medicine", 3990);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085N0700X", "Neuroradiology", 4000);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085N0904X", "Nuclear Radiology", 4010);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085P0229X", "Pediatric Radiology", 4020);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085R0001X", "Radiation Oncology", 4030);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085R0202X", "Diagnostic Radiology", 4040);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085R0203X", "Therapeutic Radiology", 4050);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085R0204X", "Vascular & Interventional Radiology", 4060);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085R0205X", "Radiological Physics", 4070);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2085U0001X", "Diagnostic Ultrasound", 4080);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208600000X", "Surgery", 4090);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2086H0002X", "Hospice and Palliative Medicine", 4100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2086S0102X", "Surgical Critical Care", 4110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2086S0105X", "Surgery of the Hand", 4120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2086S0120X", "Pediatric Surgery", 4130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2086S0122X", "Plastic and Reconstructive Surgery", 4140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2086S0127X", "Trauma Surgery", 4150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2086S0129X", "Vascular Surgery", 4160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2086X0206X", "Surgical Oncology", 4170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208800000X", "Urology", 4180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2088F0040X", "Female Pelvic Medicine and Reconstructive Surgery", 4190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2088P0231X", "Pediatric Urology", 4200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208C00000X", "Colon & Rectal Surgery", 4210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208D00000X", "General Practice", 4220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208G00000X", "Thoracic Surgery (Cardiothoracic Vascular Surgery)", 4230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208M00000X", "Hospitalist", 4240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208U00000X", "Clinical Pharmacology", 4250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208VP0000X", "Pain Medicine", 4260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "208VP0014X", "Interventional Pain Medicine", 4270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "209800000X", "Legal Medicine", 4280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "211D00000X", "Assistant, Podiatric", 4290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "213E00000X", "Podiatrist", 4300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "213EG0000X", "General Practice", 4310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "213EP0504X", "Public Medicine", 4320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "213EP1101X", "Primary Podiatric Medicine", 4330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "213ER0200X", "Radiology", 4340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "213ES0000X", "Sports Medicine", 4350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "213ES0103X", "Foot & Ankle Surgery", 4360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "213ES0131X", "Foot Surgery", 4370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "221700000X", "Art Therapist", 4380);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "222Q00000X", "Developmental Therapist", 4390);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "222Z00000X", "Orthotist", 4400);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "224900000X", "Mastectomy Fitter", 4410);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "224L00000X", "Pedorthist", 4420);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "224P00000X", "Prosthetist", 4430);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "224Y00000X", "Clinical Exercise Physiologist", 4440);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "224Z00000X", "Occupational Therapy Assistant", 4450);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "224ZE0001X", "Environmental Modification", 4460);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "224ZF0002X", "Feeding, Eating & Swallowing", 4470);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "224ZL0004X", "Low Vision", 4480);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "224ZR0403X", "Driving and Community Mobility", 4490);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225000000X", "Orthotic Fitter", 4500);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225100000X", "Physical Therapist", 4510);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2251C2600X", "Cardiopulmonary", 4520);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2251E1200X", "Ergonomics", 4530);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2251E1300X", "Electrophysiology, Clinical", 4540);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2251G0304X", "Geriatrics", 4550);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2251H1200X", "Hand", 4560);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2251H1300X", "Human Factors", 4570);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2251N0400X", "Neurology", 4580);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2251P0200X", "Pediatrics", 4590);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2251S0007X", "Sports", 4600);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2251X0800X", "Orthopedic", 4610);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225200000X", "Physical Therapy Assistant", 4620);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225400000X", "Rehabilitation Practitioner", 4630);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225500000X", "Specialist/Technologist", 4640);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2255A2300X", "Athletic Trainer", 4650);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2255R0406X", "Rehabilitation, Blind", 4660);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225600000X", "Dance Therapist", 4670);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225700000X", "Massage Therapist", 4680);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225800000X", "Recreation Therapist", 4690);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225A00000X", "Music Therapist", 4700);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225B00000X", "Pulmonary Function Technologist", 4710);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225C00000X", "Rehabilitation Counselor", 4720);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225CA2400X", "Assistive Technology Practitioner", 4730);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225CA2500X", "Assistive Technology Supplier", 4740);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225CX0006X", "Orientation and Mobility Training Provider", 4750);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225X00000X", "Occupational Therapist", 4760);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XE0001X", "Environmental Modification", 4770);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XE1200X", "Ergonomics", 4780);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XF0002X", "Feeding, Eating & Swallowing", 4790);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XG0600X", "Gerontology", 4800);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XH1200X", "Hand", 4810);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XH1300X", "Human Factors", 4820);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XL0004X", "Low Vision", 4830);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XM0800X", "Mental Health", 4840);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XN1300X", "Neurorehabilitation", 4850);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XP0019X", "Physical Rehabilitation", 4860);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XP0200X", "Pediatrics", 4870);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "225XR0403X", "Driving and Community Mobility", 4880);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "226000000X", "Recreational Therapist Assistant", 4890);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "226300000X", "Kinesiotherapist", 4900);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "227800000X", "Respiratory Therapist, Certified", 4910);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278C0205X", "Critical Care", 4920);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278E0002X", "Emergency Care", 4930);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278E1000X", "Educational", 4940);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278G0305X", "Geriatric Care", 4950);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278G1100X", "General Care", 4960);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278H0200X", "Home Health", 4970);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278P1004X", "Pulmonary Diagnostics", 4980);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278P1005X", "Pulmonary Rehabilitation", 4990);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278P1006X", "Pulmonary Function Technologist", 5000);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278P3800X", "Palliative/Hospice", 5010);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278P3900X", "Neonatal/Pediatrics", 5020);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278P4000X", "Patient Transport", 5030);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2278S1500X", "SNF/Subacute Care", 5040);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "227900000X", "Respiratory Therapist, Registered", 5050);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279C0205X", "Critical Care", 5060);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279E0002X", "Emergency Care", 5070);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279E1000X", "Educational", 5080);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279G0305X", "Geriatric Care", 5090);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279G1100X", "General Care", 5100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279H0200X", "Home Health", 5110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279P1004X", "Pulmonary Diagnostics", 5120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279P1005X", "Pulmonary Rehabilitation", 5130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279P1006X", "Pulmonary Function Technologist", 5140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279P3800X", "Palliative/Hospice", 5150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279P3900X", "Neonatal/Pediatrics", 5160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279P4000X", "Patient Transport", 5170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2279S1500X", "SNF/Subacute Care", 5180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "229N00000X", "Anaplastologist", 5190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "231H00000X", "Audiologist", 5200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "231HA2400X", "Assistive Technology Practitioner", 5210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "231HA2500X", "Assistive Technology Supplier", 5220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "235500000X", "Specialist/Technologist", 5230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2355A2700X", "Audiology Assistant", 5240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2355S0801X", "Speech-Language Assistant", 5250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "235Z00000X", "Speech-Language Pathologist", 5260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "237600000X", "Audiologist-Hearing Aid Fitter", 5270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "237700000X", "Hearing Instrument Specialist", 5280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "242T00000X", "Perfusionist", 5290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "243U00000X", "Radiology Practitioner Assistant", 5300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246Q00000X", "Spec/Tech, Pathology", 5310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246QB0000X", "Blood Banking", 5320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246QC1000X", "Chemistry", 5330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246QC2700X", "Cytotechnology", 5340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246QH0000X", "Hematology", 5350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246QH0401X", "Hemapheresis Practitioner", 5360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246QH0600X", "Histology", 5370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246QI0000X", "Immunology", 5380);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246QL0900X", "Laboratory Management", 5390);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246QL0901X", "Laboratory Management, Diplomate", 5400);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246QM0706X", "Medical Technologist", 5410);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246QM0900X", "Microbiology", 5420);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246R00000X", "Technician, Pathology", 5430);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246RH0600X", "Histology", 5440);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246RM2200X", "Medical Laboratory", 5450);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246RP1900X", "Phlebotomy", 5460);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246W00000X", "Technician, Cardiology", 5470);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246X00000X", "Spec/Tech, Cardiovascular", 5480);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246XC2901X", "Cardiovascular Invasive Specialist", 5490);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246XC2903X", "Vascular Specialist", 5500);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246XS1301X", "Sonography", 5510);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246Y00000X", "Spec/Tech, Health Info", 5520);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246YC3301X", "Coding Specialist, Hospital Based", 5530);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246YC3302X", "Coding Specialist, Physician Office Based", 5540);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246YR1600X", "Registered Record Administrator", 5550);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246Z00000X", "Specialist/Technologist, Other", 5560);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZA2600X", "Art, Medical", 5570);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZB0301X", "Biomedical Engineering", 5580);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZB0302X", "Biomedical Photographer", 5590);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZB0500X", "Biochemist", 5600);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZB0600X", "Biostatistician", 5610);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZC0007X", "Surgical Assistant", 5620);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZE0500X", "EEG", 5630);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZE0600X", "Electroneurodiagnostic", 5640);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZG0701X", "Graphics Methods", 5650);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZG1000X", "Geneticist, Medical (PhD)", 5660);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZI1000X", "Illustration, Medical", 5670);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZN0300X", "Nephrology", 5680);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZS0410X", "Surgical Technologist", 5690);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "246ZX2200X", "Orthopedic Assistant", 5700);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "247000000X", "Technician, Health Information", 5710);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2470A2800X", "Assistant Record Technician", 5720);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "247100000X", "Radiologic Technologist", 5730);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471B0102X", "Bone Densitometry", 5740);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471C1101X", "Cardiovascular-Interventional Technology", 5750);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471C1106X", "Cardiac-Interventional Technology", 5760);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471C3401X", "Computed Tomography", 5770);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471C3402X", "Radiography", 5780);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471M1202X", "Magnetic Resonance Imaging", 5790);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471M2300X", "Mammography", 5800);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471N0900X", "Nuclear Medicine Technology", 5810);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471Q0001X", "Quality Management", 5820);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471R0002X", "Radiation Therapy", 5830);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471S1302X", "Sonography", 5840);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471V0105X", "Vascular Sonography", 5850);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2471V0106X", "Vascular-Interventional Technology", 5860);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "247200000X", "Technician, Other", 5870);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2472B0301X", "Biomedical Engineering", 5880);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2472D0500X", "Darkroom", 5890);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2472E0500X", "EEG", 5900);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2472R0900X", "Renal Dialysis", 5910);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2472V0600X", "Veterinary", 5920);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "247ZC0005X", "Pathology", 5930);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251300000X", "Local Education Agency (LEA)", 5940);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251B00000X", "Case Management", 5950);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251C00000X", "Day Training, Developmentally Disabled Services", 5960);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251E00000X", "Home Health", 5970);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251F00000X", "Home Infusion", 5980);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251G00000X", "Hospice Care, Community Based", 5990);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251J00000X", "Nursing Care", 6000);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251K00000X", "Public Health or Welfare", 6010);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251S00000X", "Community/Behavioral Health", 6020);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251T00000X", "PACE Provider Organization", 6030);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251V00000X", "Voluntary or Charitable", 6040);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "251X00000X", "Supports Brokerage", 6050);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "252Y00000X", "Early Intervention Provider Agency", 6060);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "253J00000X", "Foster Care Agency", 6070);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "253Z00000X", "In Home Supportive Care", 6080);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261Q00000X", "Clinic/Center", 6090);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QA0005X", "Ambulatory Family Planning Facility", 6100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QA0006X", "Ambulatory Fertility Facility", 6110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QA0600X", "Adult Day Care", 6120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QA0900X", "Amputee", 6130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QA1903X", "Ambulatory Surgical", 6140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QA3000X", "Augmentative Communication", 6150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QB0400X", "Birthing", 6160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QC0050X", "Critical Access Hospital", 6170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QC1500X", "Community Health", 6180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QC1800X", "Corporate Health", 6190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QD0000X", "Dental", 6200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QD1600X", "Developmental Disabilities", 6210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QE0002X", "Emergency Care", 6220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QE0700X", "End-Stage Renal Disease (ESRD) Treatment", 6230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QE0800X", "Endoscopy", 6240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QF0050X", "Family Planning, Non-Surgical", 6250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QF0400X", "Federally Qualified Health Center (FQHC)", 6260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QG0250X", "Genetics", 6270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QH0100X", "Health Service", 6280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QH0700X", "Hearing and Speech", 6290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QI0500X", "Infusion Therapy", 6300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QL0400X", "Lithotripsy", 6310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM0801X", "Mental Health (Including Community Mental Health Center)", 6320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM0850X", "Adult Mental Health", 6330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM0855X", "Adolescent and Children Mental Health", 6340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM1000X", "Migrant Health", 6350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM1100X", "Military/U.S. Coast Guard Outpatient", 6360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM1101X", "Military and U.S. Coast Guard Ambulatory Procedure", 6370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM1102X", "Military Outpatient Operational (Transportable) Component", 6380);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM1103X", "Military Ambulatory Procedure Visits Operational (Transportable)", 6390);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM1200X", "Magnetic Resonance Imaging (MRI)", 6400);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM1300X", "Multi-Specialty", 6410);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM2500X", "Medical Specialty", 6420);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM2800X", "Methadone Clinic", 6430);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QM3000X", "Medically Fragile Intants and Children Day Care", 6440);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QP0904X", "Public Health, Federal", 6450);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QP0905X", "Public Health, State or Local", 6460);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QP1100X", "Podiatric", 6470);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QP2000X", "Physical Therapy", 6480);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QP2300X", "Primary Care", 6490);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QP2400X", "Prison Health", 6500);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QP3300X", "Pain", 6510);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QR0200X", "Radiology", 6520);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QR0206X", "Radiology, Mammography", 6530);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QR0207X", "Radiology, Mobile Mammography", 6540);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QR0208X", "Radiology, Mobile", 6550);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QR0400X", "Rehabilitation", 6560);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QR0401X", "Rehabilitation, Comprehensive Outpatient Rehabilitation Facility (CORF)", 6570);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QR0404X", "Rehabilitation, Cardiac Facilities", 6580);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QR0405X", "Rehabilitation, Substance Use Disorder", 6590);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QR0800X", "Recovery Care", 6600);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QR1100X", "Research", 6610);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QR1300X", "Rural Health", 6620);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QS0112X", "Oral and Maxillofacial Surgery", 6630);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QS0132X", "Ophthalmologic Surgery", 6640);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QS1000X", "Student Health", 6650);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QS1200X", "Sleep Disorder Diagnostic", 6660);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QU0200X", "Urgent Care", 6670);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QV0200X", "VA", 6680);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QX0100X", "Occupational Medicine", 6690);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QX0200X", "Oncology", 6700);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "261QX0203X", "Oncology, Radiation", 6710);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "273100000X", "Epilepsy Unit", 6720);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "273R00000X", "Psychiatric Unit", 6730);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "273Y00000X", "Rehabilitation Unit", 6740);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "275N00000X", "Medicare Defined Swing Bed Unit", 6750);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "276400000X", "Rehabilitation, Substance Use Disorder Unit", 6760);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "281P00000X", "Chronic Disease Hospital", 6770);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "281PC2000X", "Children", 6780);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "282E00000X", "Long Term Care Hospital", 6790);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "282J00000X", "Religious Nonmedical Health Care Institution", 6800);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "282N00000X", "General Acute Care Hospital", 6810);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "282NC0060X", "Critical Access", 6820);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "282NC2000X", "Children", 6830);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "282NR1301X", "Rural", 6840);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "282NW0100X", "Women", 6850);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "283Q00000X", "Psychiatric Hospital", 6860);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "283X00000X", "Rehabilitation Hospital", 6870);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "283XC2000X", "Children", 6880);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "284300000X", "Special Hospital", 6890);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "286500000X", "Military Hospital", 6900);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2865C1500X", "Community Health", 6910);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2865M2000X", "Military General Acute Care Hospital", 6920);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "2865X1600X", "Military General Acute Care Hospital. Operational (Transportable)", 6930);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "287300000X", "Christian Science Sanitorium", 6940);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "291900000X", "Military Clinical Medical Laboratory", 6950);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "291U00000X", "Clinical Medical Laboratory", 6960);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "292200000X", "Dental Laboratory", 6970);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "293D00000X", "Physiological Laboratory", 6980);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "302F00000X", "Exclusive Provider Organization", 6990);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "302R00000X", "Health Maintenance Organization", 7000);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "305R00000X", "Preferred Provider Organization", 7010);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "305S00000X", "Point of Service", 7020);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "310400000X", "Assisted Living Facility", 7030);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3104A0625X", "Assisted Living, Mental Illness", 7040);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3104A0630X", "Assisted Living, Behavioral Disturbances", 7050);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "310500000X", "Intermediate Care Facility, Mental Illness", 7060);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "311500000X", "Alzheimer Center (Dementia Center)", 7070);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "311Z00000X", "Custodial Care Facility", 7080);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "311ZA0620X", "Adult Care Home", 7090);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "313M00000X", "Nursing Facility/Intermediate Care Facility", 7100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "314000000X", "Skilled Nursing Facility", 7110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3140N1450X", "Nursing Care, Pediatric", 7120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "315D00000X", "Hospice, Inpatient", 7130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "315P00000X", "Intermediate Care Facility, Mentally Retarded", 7140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "317400000X", "Christian Science Facility", 7150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "320600000X", "Residential Treatment Facility, Mental Retardation and/or Developmental Disabilities", 7160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "320700000X", "Residential Treatment Facility, Physical Disabilities", 7170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "320800000X", "Community Based Residential Treatment Facility, Mental Illness", 7180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "320900000X", "Community Based Residential Treatment, Mental Retardation and/or Developmental Disabilities", 7190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "322D00000X", "Residential Treatment Facility, Emotionally Disturbed Children", 7200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "323P00000X", "Psychiatric Residential Treatment Facility", 7210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "324500000X", "Substance Abuse Rehabilitation Facility", 7220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3245S0500X", "Substance Abuse Treatment, Children", 7230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "331L00000X", "Blood Bank", 7240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332000000X", "Military/U.S. Coast Guard Pharmacy", 7250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332100000X", "Department of Veterans Affairs (VA) Pharmacy", 7260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332800000X", "Indian Health Service/Tribal/Urban Indian Health (I/T/U) Pharmacy", 7270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332900000X", "Non-Pharmacy Dispensing Site", 7280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332B00000X", "Durable Medical Equipment & Medical Supplies", 7290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332BC3200X", "Customized Equipment", 7300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332BD1200X", "Dialysis Equipment & Supplies", 7310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332BN1400X", "Nursing Facility Supplies", 7320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332BP3500X", "Parenteral & Enteral Nutrition", 7330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332BX2000X", "Oxygen Equipment & Supplies", 7340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332G00000X", "Eye Bank", 7350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332H00000X", "Eyewear Supplier (Equipment, not the service)", 7360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332S00000X", "Hearing Aid Equipment", 7370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "332U00000X", "Home Delivered Meals", 7380);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "333300000X", "Emergency Response System Companies", 7390);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "333600000X", "Pharmacy", 7400);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3336C0002X", "Clinic Pharmacy", 7410);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3336C0003X", "Community/Retail Pharmacy", 7420);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3336C0004X", "Compounding Pharmacy", 7430);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3336H0001X", "Home Infusion Therapy Pharmacy", 7440);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3336I0012X", "Institutional Pharmacy", 7450);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3336L0003X", "Long Term Care Pharmacy", 7460);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3336M0002X", "Mail Order Pharmacy", 7470);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3336M0003X", "Managed Care Organization Pharmacy", 7480);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3336N0007X", "Nuclear Pharmacy", 7490);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3336S0011X", "Specialty Pharmacy", 7500);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "335E00000X", "Prosthetic/Orthotic Supplier", 7510);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "335G00000X", "Medical Foods Supplier", 7520);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "335U00000X", "Organ Procurement Organization", 7530);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "335V00000X", "Portable X-ray and/or Other Portable Diagnostic Imaging Supplier", 7540);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "341600000X", "Ambulance", 7550);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3416A0800X", "Air Transport", 7560);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3416L0300X", "Land Transport", 7570);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3416S0300X", "Water Transport", 7580);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "341800000X", "Military/U.S. Coast Guard Transport", 7590);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3418M1110X", "Military or U.S. Coast Guard Ambulance, Ground Transport", 7600);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3418M1120X", "Military or U.S. Coast Guard Ambulance, Air Transport", 7610);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3418M1130X", "Military or U.S. Coast Guard Ambulance, Water Transport", 7620);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "343800000X", "Secured Medical Transport (VAN)", 7630);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "343900000X", "Non-emergency Medical Transport (VAN)", 7640);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "344600000X", "Taxi", 7650);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "344800000X", "Air Carrier", 7660);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "347B00000X", "Bus", 7670);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "347C00000X", "Private Vehicle", 7680);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "347D00000X", "Train", 7690);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "347E00000X", "Transportation Broker", 7700);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363A00000X", "Physician Assistant", 7710);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363AM0700X", "Medical", 7720);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363AS0400X", "Surgical Technologist", 7730);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363L00000X", "Nurse Practitioner", 7740);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LA2100X", "Acute Care", 7750);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LA2200X", "Adult Health", 7760);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LC0200X", "Critical Care Medicine", 7770);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LC1500X", "Community Health", 7780);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LF0000X", "Family", 7790);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LG0600X", "Gerontology", 7800);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LN0000X", "Neonatal", 7810);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LN0005X", "Neonatal, Critical Care", 7820);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LP0200X", "Pediatrics", 7830);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LP0222X", "Pediatrics, Critical Care", 7840);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LP0808X", "Psych/Mental Health", 7850);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LP1700X", "Perinatal", 7860);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LP2300X", "Primary Care", 7870);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LS0200X", "School", 7880);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LW0102X", "Women's Health", 7890);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LX0001X", "Obstetrics & Gynecology", 7900);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "363LX0106X", "Occupational Health", 7910);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364S00000X", "Clinical Nurse Specialist", 7920);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SA2100X", "Acute Care", 7930);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SA2200X", "Adult Health", 7940);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SC0200X", "Critical Care Medicine", 7950);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SC1501X", "Community Health/Public Health", 7960);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SC2300X", "Chronic Care", 7970);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SE0003X", "Emergency", 7980);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SE1400X", "Ethics", 7990);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SF0001X", "Family Health", 8000);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SG0600X", "Gerontology", 8010);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SH0200X", "Home Health", 8020);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SH1100X", "Holistic", 8030);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SI0800X", "Informatics", 8040);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SL0600X", "Long-Term Care", 8050);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SM0705X", "Medical-Surgical", 8060);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SN0000X", "Neonatal", 8070);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SN0800X", "Neuroscience", 8080);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SP0200X", "Pediatrics", 8090);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SP0807X", "Psych/Mental Health, Child & Adolescent", 8100);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SP0808X", "Psych/Mental Health", 8110);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SP0809X", "Psych/Mental Health, Adult", 8120);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SP0810X", "Psych/Mental Health, Child & Family", 8130);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SP0811X", "Psych/Mental Health, Chronically Ill", 8140);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SP0812X", "Psych/Mental Health, Community", 8150);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SP0813X", "Psych/Mental Health, Geropsychiatric", 8160);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SP1700X", "Perinatal", 8170);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SP2800X", "Perioperative", 8180);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SR0400X", "Rehabilitation", 8190);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SS0200X", "School", 8200);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364ST0500X", "Transplantation", 8210);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SW0102X", "Women's Health", 8220);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SX0106X", "Occupational Health", 8230);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SX0200X", "Oncology", 8240);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "364SX0204X", "Oncology, Pediatrics", 8250);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "367500000X", "Nurse Anesthetist, Certified Registered", 8260);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "367A00000X", "Advanced Practice Midwife", 8270);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "367H00000X", "Anesthesiologist Assistant", 8280);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "372500000X", "Chore Provider", 8290);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "372600000X", "Adult Companion", 8300);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "373H00000X", "Day Training/Habilitation Specialist", 8310);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "374700000X", "Technician", 8320);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3747A0650X", "Attendant Care Provider", 8330);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "3747P1801X", "Personal Care Attendant", 8340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "374J00000X", "Doula", 8350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "374K00000X", "Religious Nonmedical Practitioner", 8360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "374T00000X", "Religious Nonmedical Nursing Personnel", 8370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "374U00000X", "Home Health Aide", 8380);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "376G00000X", "Nursing Home Administrator", 8390);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "376J00000X", "Homemaker", 8400);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "376K00000X", "Nurse's Aide", 8410);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "385H00000X", "Respite Care", 8420);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "385HR2050X", "Respite Care Camp", 8430);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "385HR2055X", "Respite Care, Mental Illness, Child", 8440);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "385HR2060X", "Respite Care, Mental Retardation and/or Developmental Disabilities, Child", 8450);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "385HR2065X", "Respite Care, Physical Disabilities, Child", 8460);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "390200000X", "Student in an Organized Health Care Education/Training Program", 8470);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ("us-core-provider-specialty", "405300000X", "Prevention Professional", 8480);
#EndIf

#IfMissingColumn documents document_data
ALTER TABLE `documents` ADD `document_data` MEDIUMTEXT;
#EndIf

#IfMissingColumn immunizations uuid
ALTER TABLE `immunizations` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex immunizations uuid
CREATE UNIQUE INDEX `uuid` ON `immunizations` (`uuid`);
#EndIf

#IfUuidNeedUpdate immunizations
#EndIf

#IfMissingColumn lists uuid
ALTER TABLE `lists` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex lists uuid
CREATE UNIQUE INDEX `uuid` ON `lists` (`uuid`);
#EndIf

#IfUuidNeedUpdate lists
#EndIf

#IfMissingColumn lists verification
ALTER TABLE `lists` ADD `verification` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Reference to list_options option_id = allergyintolerance-verification';
#EndIf

#IfNotRow2D list_options list_id lists option_id allergyintolerance-verification
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq` ) VALUES ('lists' ,'allergyintolerance-verification', 'AllergyIntolerance Verification Status Codes', 1);
#EndIf

#IfNotRow list_options list_id allergyintolerance-verification
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('allergyintolerance-verification', 'unconfirmed', 'Unconfirmed', 10);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('allergyintolerance-verification', 'confirmed', 'Confirmed', 20);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('allergyintolerance-verification', 'refuted', 'Refuted', 30);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('allergyintolerance-verification', 'entered-in-error', 'Entered in Error', 40);
#EndIf

#IfMissingColumn ar_activity deleted
ALTER TABLE `ar_activity` ADD `deleted` datetime DEFAULT NULL COMMENT 'NULL if active, otherwise when voided';
#EndIf

#IfNotRow2D list_options list_id lists option_id condition-verification
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq` ) VALUES ('lists' ,'condition-verification', 'Condition Verification Status Codes', 1);
#EndIf

#IfNotRow list_options list_id condition-verification
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('condition-verification', 'confirmed', 'Confirmed', 10);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('condition-verification', 'unconfirmed', 'Unconfirmed', 20);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('condition-verification', 'refuted', 'Refuted', 30);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('condition-verification', 'entered-in-error', 'Entered in Error', 40);
#EndIf

#IfMissingColumn procedure_order uuid
ALTER TABLE `procedure_order` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex procedure_order uuid
CREATE UNIQUE INDEX `uuid` ON `procedure_order` (`uuid`);
#EndIf

#IfUuidNeedUpdateId procedure_order procedure_order_id
#EndIf

UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#dee2e6' WHERE `pc_constant_id`='no_show' AND `pc_catcolor`='#DDDDDD';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#cce5ff' WHERE `pc_constant_id`='in_office' AND `pc_catcolor`='#99CCFF';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#fdb172' WHERE `pc_constant_id`='out_of_office' AND `pc_catcolor`='#99FFFF';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#e9ecef' WHERE `pc_constant_id`='vacation' AND `pc_catcolor`='#EFEFEF';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#ffecb4' WHERE `pc_constant_id`='office_visit' AND `pc_catcolor`='#FFFFCC';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#8663ba' WHERE `pc_constant_id`='holidays' AND `pc_catcolor`='#9676DB';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#2374ab' WHERE `pc_constant_id`='closed' AND `pc_catcolor`='#2374AB';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#ffd351' WHERE `pc_constant_id`='lunch' AND `pc_catcolor`='#FFFF33';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#93d3a2' WHERE `pc_constant_id`='established_patient' AND `pc_catcolor`='#CCFF33';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#a2d9e2' WHERE `pc_constant_id`='new_patient' AND `pc_catcolor`='#CCFFFF';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#b02a37' WHERE `pc_constant_id`='reserved' AND `pc_catcolor`='#FF7777';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#ced4da' WHERE `pc_constant_id`='health_and_behavioral_assessment' AND `pc_catcolor`='#C7C7C7';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#d3c6ec' WHERE `pc_constant_id`='preventive_care_services' AND `pc_catcolor`='#CCCCFF';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#febe89' WHERE `pc_constant_id`='ophthalmological_services' AND `pc_catcolor`='#F89219';
UPDATE `openemr_postcalendar_categories` SET `pc_catcolor`='#adb5bd' WHERE `pc_constant_id`='group_therapy' AND `pc_catcolor`='#BFBFBF';

#IfMissingColumn drugs uuid
ALTER TABLE `drugs` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex drugs uuid
CREATE UNIQUE INDEX `uuid` ON `drugs` (`uuid`);
#EndIf

#IfUuidNeedUpdateId drugs drug_id
#EndIf

#IfMissingColumn prescriptions uuid
ALTER TABLE `prescriptions` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex prescriptions uuid
CREATE UNIQUE INDEX `uuid` ON `prescriptions` (`uuid`);
#EndIf

#IfUuidNeedUpdate prescriptions
#EndIf

#IfNotColumnType prescriptions rxnorm_drugcode varchar(25)
ALTER TABLE `prescriptions` MODIFY `rxnorm_drugcode` varchar(25) DEFAULT NULL;
#EndIf

#IfMissingColumn ccda encrypted
ALTER TABLE `ccda` ADD `encrypted` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0->No,1->Yes';
#EndIf

#IfNotTable uuid_mapping
CREATE TABLE `uuid_mapping` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) NOT NULL DEFAULT '',
  `resource` varchar(255) NOT NULL DEFAULT '',
  `table` varchar(255) NOT NULL DEFAULT '',
  `target_uuid` binary(16) NOT NULL DEFAULT '',
  `created` timestamp NULL,
  PRIMARY KEY (`id`),
  KEY `uuid` (`uuid`),
  KEY `resource` (`resource`),
  KEY `table` (`table`),
  KEY `target_uuid` (`target_uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=1;
#EndIf

#IfColumn automatic_notification next_app_date
ALTER TABLE `automatic_notification` DROP COLUMN `next_app_date`;
#EndIf

#IfColumn automatic_notification next_app_time
ALTER TABLE `automatic_notification` DROP COLUMN `next_app_time`;
#EndIf

#IfColumn automatic_notification notification_sent_date
ALTER TABLE `automatic_notification` DROP COLUMN `notification_sent_date`;
#EndIf

#IfMissingColumn procedure_result uuid
ALTER TABLE `procedure_result` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex procedure_result uuid
CREATE UNIQUE INDEX `uuid` ON `procedure_result` (`uuid`);
#EndIf

#IfUuidNeedUpdateId procedure_result procedure_result_id
#EndIf

#IfNotColumnType form_bronchitis user varchar(50)
ALTER TABLE `form_bronchitis` MODIFY `user` varchar(50) default NULL;
#EndIf

#IfNotColumnType form_bronchitis groupname varchar(50)
ALTER TABLE `form_bronchitis` MODIFY `groupname` varchar(50) default NULL;
#EndIf

#IfNotColumnType form_bronchitis bronchitis_ops_fever text
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_ops_fever` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_ops_cough` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_ops_dizziness` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_ops_chest_pain` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_ops_dyspnea` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_ops_sweating` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_ops_wheezing` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_ops_malaise` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_ops_sputum` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_ops_all_reviewed` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_review_of_pmh` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_review_of_allergies` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_review_of_sh` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_review_of_fh` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_normal_right` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_normal_left` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_nares_normal_right` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_nares_normal_left` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_thickened_right` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_thickened_left` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_af_level_right` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_af_level_left` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_nares_swelling_right` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_nares_swelling_left` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_retracted_right` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_retracted_left` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_nares_discharge_right` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_nares_discharge_left` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_bulging_right` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_bulging_left` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_perforated_right` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_perforated_left` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_tms_nares_not_examined` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_no_sinus_tenderness` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_oropharynx_normal` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_sinus_tenderness_frontal_right` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_sinus_tenderness_frontal_left` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_oropharynx_erythema` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_oropharynx_exudate` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_oropharynx_abcess` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_oropharynx_ulcers` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_sinus_tenderness_maxillary_right` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_sinus_tenderness_maxillary_left` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_sinus_tenderness_not_examined` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_oropharynx_not_examined` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_heart_pmi` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_heart_s3` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_heart_s4` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_heart_click` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_heart_rub` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_heart_normal` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_heart_not_examined` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_bs_normal` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_bs_reduced` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_bs_increased` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_crackles_lll` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_crackles_rll` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_crackles_bll` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_rubs_lll` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_rubs_rll` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_rubs_bll` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_wheezes_lll` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_wheezes_rll` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_wheezes_bll` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_wheezes_dll` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_normal_exam` text;
ALTER TABLE `form_bronchitis` MODIFY `bronchitis_lungs_not_examined` text;
ALTER TABLE `form_bronchitis` MODIFY `diagnosis1_bronchitis_form` text;
ALTER TABLE `form_bronchitis` MODIFY `diagnosis2_bronchitis_form` text;
ALTER TABLE `form_bronchitis` MODIFY `diagnosis3_bronchitis_form` text;
ALTER TABLE `form_bronchitis` MODIFY `diagnosis4_bronchitis_form` text;
#EndIf

DELETE FROM `globals` WHERE `gl_name`='font-size';
DELETE FROM `globals` WHERE `gl_name`='font-family';

#IfMissingColumn documents name
ALTER TABLE `documents` ADD `name` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn documents drive_uuid
ALTER TABLE `documents` ADD `drive_uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex documents drive_uuid
CREATE UNIQUE INDEX `drive_uuid` ON `documents` (`drive_uuid`);
#EndIf

#IfDocumentNamingNeeded
#EndIf

#IfNotTable api_log
CREATE TABLE `api_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `patient_id` bigint(20) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `method` varchar(20) NOT NULL,
  `request` varchar(255) NOT NULL,
  `request_url` text,
  `request_body` longtext,
  `response` longtext,
  `encrypted` tinyint(1) NOT NULL,
  `created_time` timestamp NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;
#EndIf

#IfMissingColumn api_log log_id
ALTER TABLE `api_log` ADD `log_id` int(11) NOT NULL;
#EndIf

#IfColumn api_log encrypted
ALTER TABLE `api_log` DROP COLUMN `encrypted`;
#EndIf

#IfColumn patient_data care_team
ALTER TABLE `patient_data` CHANGE `care_team` `care_team_provider` text;
#EndIf

#IfMissingColumn patient_data care_team_facility
ALTER TABLE `patient_data` ADD `care_team_facility` text;
#EndIf

#IfRow2D layout_options form_id DEM field_id care_team
SET @group_id = (SELECT group_id FROM layout_options WHERE field_id='care_team' AND form_id='DEM');
SET @backup_group_id = (SELECT group_id FROM layout_options WHERE field_id='DOB' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_id = IFNULL(@group_id,@backup_group_id) AND form_id='DEM');
UPDATE `layout_options` SET field_id='care_team_provider', group_id = IFNULL(@group_id,@backup_group_id), seq=@seq+1, title='Care Team (Provider)', data_type=45 WHERE form_id='DEM' AND field_id='care_team';
#EndIf

#IfNotRow2D layout_options form_id DEM field_id care_team_facility
SET @group_id = (SELECT group_id FROM layout_options WHERE field_id='care_team_provider' AND form_id='DEM');
SET @backup_group_id = (SELECT group_id FROM layout_options WHERE field_id='DOB' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_id = IFNULL(@group_id,@backup_group_id) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'care_team_facility', IFNULL(@group_id,@backup_group_id), 'Care Team (Facility)', @seq+1, 44, 1, 0, 0, '', 1, 1, '', '', '', 0);
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2020-10-01 load_filename Code-Descriptions.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2020-10-01', 'Code-Descriptions.zip', 'f22e7201fa662689d85b926a32359701');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2020-10-01 load_filename Zip File 5 2021 ICD-10-PCS Order File (Long and Abbreviated Titles).zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2020-10-01', 'Zip File 5 2021 ICD-10-PCS Order File (Long and Abbreviated Titles).zip', '6a61cee7a8f774e23412ca1330980bbb');
#EndIf

#IfNotColumnType documents hash varchar(255)
ALTER TABLE `documents` MODIFY `hash` varchar(255) DEFAULT NULL;
#EndIf

#IfTable log_validator
DROP TABLE `log_validator`;
#EndIf

#IfMissingColumn log_comment_encrypt checksum_api
ALTER TABLE `log_comment_encrypt` ADD `checksum_api` longtext;
#EndIf

#IfNotColumnType onsite_signatures sig_hash varchar(255)
ALTER TABLE `onsite_signatures` MODIFY `sig_hash` varchar(255) NOT NULL;
#EndIf

#IfMissingColumn ccda hash
ALTER TABLE `ccda` ADD `hash` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn ccda uuid
ALTER TABLE `ccda` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex ccda uuid
CREATE UNIQUE INDEX `uuid` ON `ccda` (`uuid`);
#EndIf

#IfUuidNeedUpdate ccda
#EndIf

#IfNotColumnTypeDefault form_prior_auth date datetime NULL
ALTER TABLE `form_prior_auth` MODIFY `date` datetime NULL;
SET @currentSQLMode = (SELECT @@sql_mode);
SET sql_mode = '';
UPDATE `form_prior_auth` SET `date` = NULL WHERE `date` = '0000-00-00 00:00:00';
SET sql_mode = @currentSQLMode;
#EndIf

#IfMissingColumn form_prior_auth date_from
ALTER TABLE `form_prior_auth` ADD `date_from` date DEFAULT NULL;
#EndIf

#IfMissingColumn form_prior_auth date_to
ALTER TABLE `form_prior_auth` ADD `date_to` date DEFAULT NULL;
#EndIf

#IfMissingColumn documents deleted
ALTER TABLE `documents` ADD `deleted` tinyint(1) NOT NULL DEFAULT '0';
#EndIf

#IfMissingColumn procedure_providers active
ALTER TABLE `procedure_providers` ADD `active` tinyint(1) NOT NULL DEFAULT '1';
#EndIf

#IfNotColumnType api_token token varchar(128)
ALTER TABLE `api_token` CHANGE `token` `token` VARCHAR(128) DEFAULT NULL;
#EndIf

#IfNotColumnType api_token token_auth text
ALTER TABLE `api_token` CHANGE `token_auth` `token_auth` TEXT;
#EndIf

#IfNotColumnType api_token user_id varchar(40)
ALTER TABLE `api_token` CHANGE `user_id` `user_id` VARCHAR(40) DEFAULT NULL;
#EndIf

#IfMissingColumn api_token client_id
ALTER TABLE `api_token` ADD `client_id` VARCHAR(80) DEFAULT NULL;
#EndIf

#IfMissingColumn api_token auth_user_id
ALTER TABLE `api_token` ADD `auth_user_id` VARCHAR(80) DEFAULT NULL;
#EndIf

#IfMissingColumn api_token scope
ALTER TABLE `api_token` ADD `scope` TEXT COMMENT 'json encoded';
#EndIf

#IfNotTable oauth_clients
CREATE TABLE `oauth_clients` (
`client_id` varchar(80) NOT NULL,
`client_role` varchar(20) DEFAULT NULL,
`client_name` varchar(80) NOT NULL,
`client_secret` text,
`registration_token` varchar(80) DEFAULT NULL,
`registration_uri_path` varchar(40) DEFAULT NULL,
`register_date` datetime DEFAULT NULL,
`revoke_date` datetime DEFAULT NULL,
`contacts` text,
`redirect_uri` text,
`grant_types` varchar(80) DEFAULT NULL,
`scope` text,
`user_id` varchar(40) DEFAULT NULL,
`site_id` varchar(64) DEFAULT NULL,
`is_confidential` tinyint(1) NOT NULL DEFAULT '1',
PRIMARY KEY (`client_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable oauth_trusted_user
CREATE TABLE `oauth_trusted_user` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`user_id` varchar(80) DEFAULT NULL,
`client_id` varchar(80) DEFAULT NULL,
`scope` text,
`persist_login` tinyint(1) DEFAULT '0',
`time` timestamp NULL DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `accounts_id` (`user_id`),
KEY `clients_id` (`client_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotRow2D icd10_dx_order_code dx_code U072 active 1
INSERT INTO `icd10_dx_order_code`
(`dx_code`, `formatted_dx_code`, `valid_for_coding`, `short_desc`, `long_desc`, `active`, `revision`)
VALUES ('U072', 'U07.2', '1', 'COVID-19, virus not identified', 'COVID-19, virus not identified', '1', '1');
#EndIf

#IfRow2D icd10_dx_order_code dx_code U072 active 1
set @newMax = (SELECT MAX(revision) from icd10_dx_order_code);
UPDATE `icd10_dx_order_code` SET `revision` = @newMax WHERE `dx_code` = 'U072';
#EndIf

#IfNotColumnType oauth_clients client_id varchar(80)
ALTER TABLE `oauth_clients` CHANGE `client_id` `client_id` varchar(80) NOT NULL;
#EndIf

#IfNotColumnType oauth_clients client_secret text
ALTER TABLE `oauth_clients` CHANGE `client_secret` `client_secret` text;
#EndIf

#IfNotColumnType oauth_clients registration_token varchar(80)
ALTER TABLE `oauth_clients` CHANGE `registration_token` `registration_token` varchar(80) DEFAULT NULL;
#EndIf

#IfColumn api_token token_auth
ALTER TABLE `api_token` DROP COLUMN `token_auth`;
#EndIf

#IfColumn api_token token_api
ALTER TABLE `api_token` DROP COLUMN `token_api`;
#EndIf

#IfColumn api_token patient_id
ALTER TABLE `api_token` DROP COLUMN `patient_id`;
#EndIf

#IfColumn api_token auth_user_id
ALTER TABLE `api_token` DROP COLUMN `auth_user_id`;
#EndIf

#IfMissingColumn oauth_trusted_user code
ALTER TABLE `oauth_trusted_user` ADD `code` text;
#EndIf

#IfMissingColumn oauth_trusted_user session_cache
ALTER TABLE `oauth_trusted_user` ADD `session_cache` text;
#EndIf

#IfNotColumnType codes code_text text
ALTER TABLE `codes` MODIFY `code_text` text;
#EndIf

#IfNotColumnType codes_history code_text text
ALTER TABLE `codes_history` MODIFY `code_text` text;
#EndIf

#IfNotColumnType codes_history code_text_short text
ALTER TABLE `codes_history` MODIFY `code_text_short` text;
#EndIf

#IfNotColumnType icd10_dx_order_code long_desc text
ALTER TABLE `icd10_dx_order_code` MODIFY `long_desc` text;
#EndIf

#IfNotColumnType icd10_pcs_order_code long_desc text
ALTER TABLE `icd10_pcs_order_code` MODIFY `long_desc` text;
#EndIf

#IfColumn api_token user_role
ALTER TABLE `api_token` DROP COLUMN `user_role`;
#EndIf

#IfColumn oauth_trusted_user user_role
ALTER TABLE `oauth_trusted_user` DROP COLUMN `user_role`;
#EndIf

#IfMissingColumn oauth_clients logout_redirect_uris
ALTER TABLE `oauth_clients` ADD `logout_redirect_uris` text;
#EndIf

#IfMissingColumn oauth_trusted_user grant_type
ALTER TABLE `oauth_trusted_user` ADD COLUMN `grant_type` varchar(32) DEFAULT NULL;
#EndIf

#IfNotColumnType layout_options title text
ALTER TABLE `layout_options` CHANGE `title` `title` TEXT;
#EndIf

#IfMissingColumn oauth_clients jwks_uri
ALTER TABLE `oauth_clients` ADD `jwks_uri` TEXT;
ALTER TABLE `oauth_clients` ADD `jwks` TEXT;
ALTER TABLE `oauth_clients` ADD `initiate_login_uri` TEXT;
#EndIf

#IfMissingColumn oauth_clients endorsements
ALTER TABLE `oauth_clients` ADD `endorsements` TEXT;
ALTER TABLE `oauth_clients` ADD `policy_uri` TEXT;
ALTER TABLE `oauth_clients` ADD `tos_uri` TEXT;
#EndIf

#IfMissingColumn oauth_clients is_enabled
ALTER TABLE `oauth_clients` ADD `is_enabled` tinyint(1) NOT NULL DEFAULT '0';
#EndIf

#IfNotRow codes code_text meningococcal polysaccharide (groups A, C, Y, W-135) tetanus toxoid conjugate vaccine .5mL dose, preservative free
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "meningococcal polysaccharide (groups A, C, Y, W-135) tetanus toxoid conjugate vaccine .5mL dose, preservative free", "meningococcal polysaccharide (groups A, C, Y, W-135) TT conjugate", 203, @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "influenza, seasonal vaccine, quadrivalent, adjuvanted, .5mL dose, preservative free", "Influenza vaccine, quadrivalent, adjuvanted", 205, @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "smallpox monkeypox vaccine, live attenuated, preservative free (National Stockpile)", "Smallpox monkeypox vaccine (National Stockpile)", 206, @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "SARS-COV-2 (COVID-19) vaccine, mRNA, spike protein, LNP, preservative free, 100 mcg/0.5mL dose", "COVID-19, mRNA, LNP-S, PF, 100 mcg/0.5 mL dose", 207, @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "SARS-COV-2 (COVID-19) vaccine, mRNA, spike protein, LNP, preservative free, 30 mcg/0.3mL dose", "COVID-19, mRNA, LNP-S, PF, 30 mcg/0.3 mL dose", 208, @codetypeid, '', 0, 0, '', '', '', 1),
(NULL, "SARS-COV-2 (COVID-19) vaccine, vector non-replicating, recombinant spike protein-ChAdOx1, preservative free, 0.5 mL ", "COVID-19 vaccine, vector-nr, rS-ChAdOx1, PF, 0.5 mL", 210, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

