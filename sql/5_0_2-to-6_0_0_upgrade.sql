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
