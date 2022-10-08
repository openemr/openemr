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
--    behavior:  if the colname in the table_name table does not exist,  the block will be executed

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

--  #IfNotIndex
--    desc:      This function will allow adding of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the index does not exist, it will be created

--  #EndIf
--    all blocks are terminated with and #EndIf statement.

#IfNotColumnType prescriptions route VARCHAR(100)
ALTER TABLE `prescriptions` CHANGE `route` `route` VARCHAR(100) NULL DEFAULT NULL Comment 'Max size 100 characters is same max as immunizations';
#EndIf

#IfNotRow2D list_options list_id drug_route option_id bymouth
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `notes`, `codes`) VALUES ('drug_route', 'bymouth', 'By Mouth', 1, 0, 'PO', 'NCI-CONCEPT-ID:C38288');
#EndIf

#IfNotTable questionnaire_repository
CREATE TABLE `questionnaire_repository` (
`id` bigint(21) UNSIGNED NOT NULL AUTO_INCREMENT,
`uuid` binary(16) DEFAULT NULL,
`questionnaire_id` varchar(255) DEFAULT NULL,
`provider` int(11) UNSIGNED DEFAULT NULL,
`version` int(11) NOT NULL DEFAULT 1,
`created_date` datetime DEFAULT current_timestamp(),
`modified_date` datetime DEFAULT current_timestamp(),
`name` varchar(255) DEFAULT NULL,
`type` varchar(63) NOT NULL DEFAULT 'Questionnaire',
`profile` varchar(255) DEFAULT NULL,
`active` tinyint(2) NOT NULL DEFAULT 1,
`status` varchar(31) DEFAULT NULL,
`source_url` text,
`code` varchar(255) DEFAULT NULL,
`code_display` text,
`questionnaire` longtext,
PRIMARY KEY (`id`),
UNIQUE KEY `uuid` (`uuid`),
KEY `search` (`name`,`questionnaire_id`)
) ENGINE=InnoDB;
#EndIf

-- At this point below table was never used. Simply recreating with additions
#IfMissingColumn questionnaire_response response_id
DROP TABLE `questionnaire_response`;
#EndIf

#IfMissingColumn questionnaire_repository lform
ALTER TABLE `questionnaire_repository` ADD `lform` LONGTEXT;
#EndIf

#IfMissingColumn registry form_foreign_id
ALTER TABLE `registry` ADD `form_foreign_id` BIGINT(21) NULL DEFAULT NULL COMMENT 'An id to a form repository. Primarily questionnaire_repository.';
#EndIf

#IfNotTable form_questionnaire_assessments
CREATE TABLE `form_questionnaire_assessments` (
`id` bigint(21) NOT NULL AUTO_INCREMENT,
`date` datetime DEFAULT current_timestamp(),
`last_date` datetime DEFAULT NULL,
`pid` bigint(21) NOT NULL DEFAULT 0,
`user` bigint(21) DEFAULT NULL,
`groupname` varchar(255) DEFAULT NULL,
`authorized` tinyint(4) NOT NULL DEFAULT 0,
`activity` tinyint(4) NOT NULL DEFAULT 1,
`copyright` text,
`form_name` varchar(255) DEFAULT NULL,
`code` varchar(31) DEFAULT NULL,
`code_type` varchar(31) DEFAULT "LOINC",
`questionnaire` longtext,
`questionnaire_response` longtext,
`lform` longtext,
`lform_response` longtext,
PRIMARY KEY (`id`)
) ENGINE=InnoDB;
INSERT INTO `registry` (`name`, `state`, `directory`, `sql_run`, `unpackaged`, `date`, `priority`, `category`, `nickname`, `patient_encounter`, `therapy_group_encounter`, `aco_spec`, `form_foreign_id`) VALUES ('New Questionnaire', 1, 'questionnaire_assessments', 1, 1, '2022-08-04 14:45:15', 0, 'Questionnaires', '', 1, 0, 'admin|forms', NULL);
#EndIf

#IfNotTable questionnaire_response
CREATE TABLE `questionnaire_response` (
`id` bigint(21) NOT NULL AUTO_INCREMENT,
`uuid` binary(16) DEFAULT NULL,
`response_id` varchar(255) DEFAULT NULL COMMENT 'A globally unique id for answer set. String version of UUID',
`questionnaire_foreign_id` bigint(21) DEFAULT NULL COMMENT 'questionnaire_repository id for subject questionnaire',
`questionnaire_id` varchar(255) DEFAULT NULL COMMENT 'Id for questionnaire content. String version of UUID',
`questionnaire_name` varchar(255) DEFAULT NULL,
`patient_id` int(11) DEFAULT NULL,
`encounter` int(11) DEFAULT NULL COMMENT 'May or may not be associated with an encounter',
`audit_user_id` int(11) DEFAULT NULL,
`creator_user_id` int(11) DEFAULT NULL COMMENT 'user id if answers are provider',
`create_time` datetime DEFAULT current_timestamp(),
`last_updated` datetime DEFAULT NULL,
`version` int(11) NOT NULL DEFAULT 1,
`status` varchar(63) DEFAULT NULL COMMENT 'form current status. completed,active,incomplete',
`questionnaire` longtext COMMENT 'the subject questionnaire json',
`questionnaire_response` longtext COMMENT 'questionnaire response json',
`form_response` longtext COMMENT 'lform answers array json',
`form_score` int(11) DEFAULT NULL COMMENT 'Arithmetic scoring of questionnaires',
`tscore` double DEFAULT NULL COMMENT 'T-Score',
`error` double DEFAULT NULL COMMENT 'Standard error for the T-Score',
PRIMARY KEY (`id`),
UNIQUE KEY `uuid` (`uuid`),
KEY `response_index` (`response_id`, `patient_id`, `questionnaire_id`, `questionnaire_name`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn form_questionnaire_assessments response_id
ALTER TABLE `form_questionnaire_assessments` CHANGE `last_date` `response_id` TEXT COMMENT 'The foreign id to the questionnaire_response repository';
ALTER TABLE `form_questionnaire_assessments` CHANGE `code` `response_meta` TEXT COMMENT 'json meta data for the response resource';
ALTER TABLE `form_questionnaire_assessments` CHANGE `code_type` `questionnaire_id` TEXT COMMENT 'The foreign id to the questionnaire_repository';
#EndIf

#IfNotRow2D list_options list_id Document_Template_Categories option_id questionnaire
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`) VALUES ('Document_Template_Categories','questionnaire','Questionnaires',10,0,0,'','','',0,0,1);
#EndIf
