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
ALTER TABLE `patient_access_onsite`  ADD `portal_login_username` VARCHAR(100) DEFAULT NULL COMMENT 'User entered username', ADD `portal_onetime` VARCHAR(255) DEFAULT NULL;
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

#IfNotColumnType codes code_text_short varchar(255)
ALTER TABLE `codes` MODIFY `code_text_short` varchar(255) NOT NULL default '';
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

#IfNotTable api_forced_id
CREATE TABLE `api_forced_id` (
    `pid` bigint(20) NOT NULL,
    `forced_id` varchar(100) NOT NULL,
    `resource_pid` bigint(20) NOT NULL,
    `resource_type` varchar(100) DEFAULT NULL,
    PRIMARY KEY (`pid`),
    UNIQUE KEY `idx_forcedid_resid` (`resource_pid`),
    UNIQUE KEY `idx_forcedid_type_resid` (`resource_type`,`resource_pid`),
    KEY `idx_forcedid_type_forcedid` (`resource_type`,`forced_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable api_resource
CREATE TABLE `api_resource` (
     `res_id` bigint(20) NOT NULL,
     `res_deleted_at` datetime DEFAULT NULL,
     `res_version` varchar(7) DEFAULT NULL,
     `has_tags` bit(1) NOT NULL,
     `res_published` datetime DEFAULT NULL,
     `res_updated` datetime DEFAULT NULL,
     `reviewed_date` datetime DEFAULT NULL,
     `hash_sha256` varchar(64) DEFAULT NULL,
     `res_language` varchar(20) DEFAULT NULL,
     `res_profile` varchar(200) DEFAULT NULL,
     `res_type` varchar(30) DEFAULT NULL,
     `res_ver` bigint(20) DEFAULT NULL,
     `forced_id_pid` bigint(20) DEFAULT NULL,
     PRIMARY KEY (`res_id`),
     KEY `idx_res_date` (`res_updated`),
     KEY `idx_res_lang` (`res_type`,`res_language`),
     KEY `idx_res_profile` (`res_profile`),
     KEY `idx_res_type` (`res_type`),
     KEY `idx_reviewed_date` (`reviewed_date`),
     KEY `fk_resource_forcedid` (`forced_id_pid`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable api_res_ver
CREATE TABLE `api_res_ver` (
    `pid` bigint(20) NOT NULL,
    `res_deleted_at` datetime DEFAULT NULL,
    `res_version` varchar(7) DEFAULT NULL,
    `has_tags` bit(1) NOT NULL,
    `res_published` datetime DEFAULT NULL,
    `res_updated` datetime DEFAULT NULL,
    `res_encoding` varchar(5) NOT NULL,
    `res_text` longblob,
    `res_id` bigint(20) DEFAULT NULL,
    `res_type` varchar(30) NOT NULL,
    `res_ver` bigint(20) NOT NULL,
    `forced_id_pid` bigint(20) DEFAULT NULL,
    PRIMARY KEY (`pid`),
    UNIQUE KEY `idx_resver_id_ver` (`res_id`,`res_ver`),
    KEY `idx_resver_type_date` (`res_type`,`res_updated`),
    KEY `idx_resver_id_date` (`res_id`,`res_updated`),
    KEY `idx_resver_date` (`res_updated`),
    KEY `fk_resver_forcedid` (`forced_id_pid`)
) ENGINE=InnoDB;

ALTER TABLE `api_forced_id`
    ADD CONSTRAINT `fk_forcedid_resource` FOREIGN KEY (`resource_pid`) REFERENCES `api_resource` (`res_id`);

ALTER TABLE `api_resource`
    ADD CONSTRAINT `fk_resource_forcedid` FOREIGN KEY (`forced_id_pid`) REFERENCES `api_forced_id` (`pid`);

ALTER TABLE `api_res_ver`
    ADD CONSTRAINT `fk_resver_forcedid` FOREIGN KEY (`forced_id_pid`) REFERENCES `api_forced_id` (`pid`);
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

#IfMissingColumn patient_data uuid
ALTER TABLE `patient_data` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfUuidNeedUpdate patient_data
#EndIf

#IfNotIndex patient_data uuid
CREATE UNIQUE INDEX `uuid` ON `patient_data` (`uuid`);
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

#IfUuidNeedUpdate form_encounter
#EndIf

#IfNotIndex form_encounter uuid
CREATE UNIQUE INDEX `uuid` ON `form_encounter` (`uuid`);
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

#IfUuidNeedUpdate users
#EndIf

#IfNotIndex users uuid
CREATE UNIQUE INDEX `uuid` ON `users` (`uuid`);
#EndIf

#IfNotRow2D layout_options form_id FACUSR field_id role_code
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('FACUSR', 'role_code', '1', 'Provider Role', 1, 2, 1, 15, 63, '', 1, 1, '', '', 'Provider Role at Specified Facility', 0);
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
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '376K00000X', 'Nurse\'s Aide', 2340);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '385H00000X', 'Respite Care', 2350);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '390200000X', 'Student in an Organized Health Care Education/Training Program', 2360);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '405300000X', 'Prevention Professional', 2370);
INSERT INTO list_options(list_id,option_id,title,seq) VALUES ('us-core-provider-role', '101Y00000X', 'Counselor', 2380);
#EndIf
