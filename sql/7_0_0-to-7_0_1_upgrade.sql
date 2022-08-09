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

#IfNotTable questionnaire_response
CREATE TABLE `questionnaire_response` (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) DEFAULT NULL,
  `questionnaire_foreign_id` bigint(21) DEFAULT NULL COMMENT 'questionnaire_repository id for subject questionnaire',
  `questionnaire_id` varchar(255) DEFAULT NULL,
  `questionnaire_name` varchar(255) DEFAULT NULL,
  `audit_user_id` int(11) DEFAULT NULL,
  `creator_user_id` int(11) DEFAULT NULL COMMENT 'user id if answers are provider',
  `create_time` datetime DEFAULT current_timestamp(),
  `last_updated` datetime DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
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
  KEY `questionnaire_foreign_id` (`questionnaire_foreign_id`,`questionnaire_id`,`questionnaire_name`)
) ENGINE=InnoDB;
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
