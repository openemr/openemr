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
