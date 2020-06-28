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

#IfMissingColumn users uuid
ALTER TABLE `users` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfUuidNeedUpdate users
#EndIf

#IfNotIndex users uuid
CREATE UNIQUE INDEX `uuid` ON `users` (`uuid`);
#EndIf

#IfNotRow codes code 145 code_type 100
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "respiratory syncytial virus monoclonal antibody (motavizumab), intramuscular", "RSV-MAb (new)", 145, 100, '', 0, 0, '', '', '', 1),
(NULL, "Diphtheria and Tetanus Toxoids and Acellular Pertussis Adsorbed, Inactivated Poliovirus, Haemophilus b Conjugate (Meningococcal Protein Conjugate), and Hepatitis B (Recombinant) Vaccine.", "DTaP,IPV,Hib,HepB", 146, 100, '', 0, 0, '', '', '', 1),
(NULL, "Meningococcal, MCV4, unspecified conjugate formulation(groups A, C, Y and W-135)", "meningococcal MCV4, unspecified formulation", 147, 100, '', 0, 0, '', '', '', 1),
(NULL, "Meningococcal Groups C and Y and Haemophilus b Tetanus Toxoid Conjugate Vaccine", "Meningococcal C/Y-HIB PRP", 148, 100, '', 0, 0, '', '', '', 1),
(NULL, "influenza, live, intranasal, quadrivalent", "influenza, live, intranasal, quadrivalent", 149, 100, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, injectable, quadrivalent, preservative free", "influenza, injectable, quadrivalent, preservative free", 150, 100, '', 0, 0, '', '', '', 1),
(NULL, "influenza nasal, unspecified formulation", "influenza nasal, unspecified formulation", 151, 100, '', 0, 0, '', '', '', 1),
(NULL, "Pneumococcal Conjugate, unspecified formulation", "Pneumococcal Conjugate, unspecified formulation", 152, 100, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, injectable, Madin Darby Canine Kidney, preservative free", "Influenza, injectable, MDCK, preservative free", 153, 100, '', 0, 0, '', '', '', 1),
(NULL, "Hepatitis A immune globulin", "Hep A, IG", 154, 100, '', 0, 0, '', '', '', 1),
(NULL, "Seasonal, trivalent, recombinant, injectable influenza vaccine, preservative free", "influenza, recombinant, injectable, preservative free", 155, 100, '', 0, 0, '', '', '', 1),
(NULL, "Rho(D) Immune globulin- IV or IM", "Rho(D)-IG", 156, 100, '', 0, 0, '', '', '', 1),
(NULL, "Rho(D) Immune globulin - IM", "Rho(D) -IG IM", 157, 100, '', 0, 0, '', '', '', 1),
(NULL, "influenza, injectable, quadrivalent, contains preservative", "influenza, injectable, quadrivalent", 158, 100, '', 0, 0, '', '', '', 1),
(NULL, "Rho(D) Unspecified formulation", "Rho(D) - Unspecified formulation", 159, 100, '', 0, 0, '', '', '', 1),
(NULL, "Influenza A monovalent (H5N1), adjuvanted, National stockpile 2013", "Influenza A monovalent (H5N1), ADJUVANTED-2013", 160, 100, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, injectable,quadrivalent, preservative free, pediatric", "Influenza, injectable,quadrivalent, preservative free, pediatric", 161, 100, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal B vaccine, fully recombinant", "meningococcal B, recombinant", 162, 100, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal B vaccine, recombinant, OMV, adjuvanted", "meningococcal B, OMV", 163, 100, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal B, unspecified formulation", "meningococcal B, unspecified", 164, 100, '', 0, 0, '', '', '', 1),
(NULL, "Human Papillomavirus 9-valent vaccine", "HPV9", 165, 100, '', 0, 0, '', '', '', 1),
(NULL, "influenza, intradermal, quadrivalent, preservative free, injectable", "influenza, intradermal, quadrivalent, preservative free", 166, 100, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal vaccine of unknown formulation and unknown serogroups", "meningococcal, unknown serogroups", 167, 100, '', 0, 0, '', '', '', 1),
(NULL, "Seasonal trivalent influenza vaccine, adjuvanted, preservative free", "influenza, trivalent, adjuvanted", 168, 100, '', 0, 0, '', '', '', 1),
(NULL, "Hep A, live attenuated-IM", "Hep A, live attenuated", 169, 100, '', 0, 0, '', '', '', 1),
(NULL, "non-US diphtheria, tetanus toxoids and acellular pertussis vaccine, Haemophilus influenzae type b conjugate, and poliovirus vaccine, inactivated (DTaP-Hib-IPV)", "DTAP/IPV/HIB - non-US", 170, 100, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, injectable, Madin Darby Canine Kidney, preservative free, quadrivalent", "Influenza, injectable, MDCK, preservative free, quadrivalent", 171, 100, '', 0, 0, '', '', '', 1),
(NULL, "cholera, WC-rBS", "cholera, WC-rBS", 172, 100, '', 0, 0, '', '', '', 1),
(NULL, "cholera, BivWC", "cholera, BivWC", 173, 100, '', 0, 0, '', '', '', 1),
(NULL, "cholera, live attenuated", "cholera, live attenuated", 174, 100, '', 0, 0, '', '', '', 1),
(NULL, "Human Rabies vaccine from human diploid cell culture", "Rabies - IM Diploid cell culture", 175, 100, '', 0, 0, '', '', '', 1),
(NULL, "Human rabies vaccine from Chicken fibroblast culture", "Rabies - IM fibroblast culture", 176, 100, '', 0, 0, '', '', '', 1),
(NULL, "pneumococcal conjugate vaccine, 10 valent", "PCV10", 177, 100, '', 0, 0, '', '', '', 1),
(NULL, "Non-US bivalent oral polio vaccine (types 1 and 3)", "OPV bivalent", 178, 100, '', 0, 0, '', '', '', 1),
(NULL, "Non-US monovalent oral polio vaccine, unspecified formulation", "OPV ,monovalent, unspecified", 179, 100, '', 0, 0, '', '', '', 1),
(NULL, "tetanus immune globulin", "tetanus immune globulin", 180, 100, '', 0, 0, '', '', '', 1),
(NULL, "anthrax immune globulin", "anthrax immune globulin", 181, 100, '', 0, 0, '', '', '', 1),
(NULL, "Oral Polio Vaccine, Unspecified formulation", "OPV, Unspecified", 182, 100, '', 0, 0, '', '', '', 1),
(NULL, "Yellow fever vaccine alternative formulation", "Yellow fever vaccine - alt", 183, 100, '', 0, 0, '', '', '', 1),
(NULL, "Yellow fever vaccine, unspecified formulation", "Yellow fever, unspecified formulation", 184, 100, '', 0, 0, '', '', '', 1),
(NULL, "Seasonal, quadrivalent, recombinant, injectable influenza vaccine, preservative free", "influenza, recombinant, quadrivalent,injectable, preservative free", 185, 100, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, injectable, Madin Darby Canine Kidney, quadrivalent with preservative", "Influenza, injectable, MDCK, quadrivalent, preservative", 186, 100, '', 0, 0, '', '', '', 1),
(NULL, "zoster vaccine recombinant", "zoster recombinant", 187, 100, '', 0, 0, '', '', '', 1),
(NULL, "zoster vaccine, unspecified formulation", "zoster, unspecified formulation", 188, 100, '', 0, 0, '', '', '', 1),
(NULL, "Hepatitis B vaccine (recombinant), CpG adjuvanted", "HepB-CpG", 189, 100, '', 0, 0, '', '', '', 1),
(NULL, "Typhoid conjugate vaccine (non-US)", "Typhoid conjugate vaccine (TCV)", 190, 100, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal A polysaccharide vaccine (non-US)", "meningococcal A polysaccharide (non-US)", 191, 100, '', 0, 0, '', '', '', 1),
(NULL, "meningococcal AC polysaccharide vaccine (non-US)", "meningococcal AC polysaccharide (non-US)", 192, 100, '', 0, 0, '', '', '', 1),
(NULL, "hepatitis A and hepatitis B vaccine, pediatric/adolescent (non-US)", "Hep A-Hep B, pediatric/adolescent", 193, 100, '', 0, 0, '', '', '', 1),
(NULL, "Influenza, Southern Hemisphere, unspecified formulation (Non-US)", "Influenza, Southern Hemisphere", 194, 100, '', 0, 0, '', '', '', 1),
(NULL, "Diphtheria, Tetanus, Poliomyelitis adsorbed", "DT, IPV adsorbed", 195, 100, '', 0, 0, '', '', '', 1),
(NULL, "tetanus and diphtheria toxoids, adsorbed, preservative free, for adult use, Lf unspecified", "Td, adsorbed, preservative free, adult use, Lf unspecified", 196, 100, '', 0, 0, '', '', '', 1),
(NULL, "influenza, high-dose seasonal, quadrivalent, preservative free", "influenza, high-dose, quadrivalent", 197, 100, '', 0, 0, '', '', '', 1),
(NULL, "Diphtheria, pertussis, tetanus, hepatitis B, Haemophilus Influenza Type b, (Pentavalent)", "DTP-hepB-Hib Pentavalent Non-US", 198, 100, '', 0, 0, '', '', '', 1),
(NULL, "influenza, seasonal, Southern Hemisphere, quadrivalent, pediatric 0.25mL dose, preservative free", "influenza, Southern Hemisphere, pediatric, preservative free", 200, 100, '', 0, 0, '', '', '', 1),
(NULL, "influenza, seasonal, Southern Hemisphere, quadrivalent, 0.5mL dose, no preservative", "influenza, Southern Hemisphere, preservative free", 201, 100, '', 0, 0, '', '', '', 1),
(NULL, "influenza, seasonal, Southern Hemisphere, quadrivalent, 0.5mL dose, with preservative", "influenza, Southern Hemisphere, quadrivalent, with preservative", 202, 100, '', 0, 0, '', '', '', 1),
(NULL, "AS03 Adjuvant", "AS03 Adjuvant", 801, 100, '', 0, 0, '', '', '', 1);
UPDATE `codes` SET `code_text` = "trivalent poliovirus vaccine, live, oral" WHERE `code` = '2';
UPDATE `codes` SET `code_text` = "tetanus and diphtheria toxoids, adsorbed, preservative free, for adult use (2 Lf of tetanus toxoid and 2 Lf of diphtheria toxoid)"  WHERE `code` = '9';
UPDATE `codes` SET `code_text_short` = "Td (adult), 2 Lf tetanus toxoid, preservative free, adsorbed" WHERE `code` = '9';
UPDATE `codes` SET `code_text` = "rabies vaccine, for intramuscular injection RETIRED CODE" WHERE `code` = '18';
UPDATE `codes` SET `code_text` = "cholera vaccine, unspecified formulation" WHERE `code` = '26';
UPDATE `codes` SET `code_text_short` = "cholera, unspecified formulation" WHERE `code` = '26';
UPDATE `codes` SET `code_text` = "meningococcal ACWY vaccine, unspecified formulation" WHERE `code` = '108';
UPDATE `codes` SET `code_text_short` = "meningococcal ACWY, unspecified formulation" WHERE `code` = '108';
UPDATE `codes` SET `code_text` = "tetanus and diphtheria toxoids, adsorbed, preservative free, for adult use (5 Lf of tetanus toxoid and 2 Lf of diphtheria toxoid)" WHERE `code` = '113';
UPDATE `codes` SET `code_text_short` = "Td (adult), 5 Lf tetanus toxoid, preservative free, adsorbed" WHERE `code` = '113';
UPDATE `codes` SET `code_text_short` = "zoster live" WHERE `code` = '121';
UPDATE `codes` SET `code_text` = "Historical diphtheria and tetanus toxoids and acellular pertussis, poliovirus, Haemophilus b conjugate and hepatitis B (recombinant) vaccine." WHERE `code` = '132';
UPDATE `codes` SET `code_text_short` = "DTaP-IPV-HIB-HEP B, historical" WHERE `code` = '132';
#EndIf


