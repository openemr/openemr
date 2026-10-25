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

--  #IfMBOEncounterNeeded
--    desc: Add encounter to the form_misc_billing_options table
--    arguments: none

-- Fix swapped SNOMED CT codes for the administrative_sex list (see #13767).
-- 248153007 is Male (finding), 248152002 is Female (finding); the seed had them reversed.

-- FHIR Task inputs and outputs (part 3 of #14521): the system Task.code is from, what the work needs, and what it produced.
-- Codes written before the system was kept were all task-code's, except the questionnaire task's.
#IfMissingColumn task code_system
ALTER TABLE `task` MODIFY COLUMN `code` varchar(63) DEFAULT NULL COMMENT 'FHIR Task.code: the code',
  ADD COLUMN `code_system` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.code: its system, such as http://hl7.org/fhir/CodeSystem/task-code' AFTER `code`;
UPDATE `task` SET `code_system` = 'http://hl7.org/fhir/uv/sdc/CodeSystem/temp' WHERE `code` = 'complete-questionnaire';
UPDATE `task` SET `code_system` = 'http://hl7.org/fhir/CodeSystem/task-code' WHERE `code` IS NOT NULL AND `code_system` IS NULL;
#EndIf

#IfNotTable task_input
CREATE TABLE `task_input` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) NOT NULL COMMENT 'task.id',
  `seq` int(11) NOT NULL DEFAULT 0 COMMENT 'position in Task.input',
  `type_system` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.input.type: coding system',
  `type_code` varchar(63) DEFAULT NULL COMMENT 'FHIR Task.input.type: coding code',
  `type_display` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.input.type: coding display',
  `type_text` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.input.type: text',
  `value_element` varchar(31) NOT NULL COMMENT 'which value[x] it is: valueReference, valueCanonical, valueUrl, valueString, valueMarkdown or valueCodeableConcept',
  `value_type` varchar(63) DEFAULT NULL COMMENT 'valueReference: resource type',
  `value_uuid` binary(16) DEFAULT NULL COMMENT 'valueReference: resource id',
  `value_system` varchar(255) DEFAULT NULL COMMENT 'valueCodeableConcept: coding system',
  `value_code` varchar(63) DEFAULT NULL COMMENT 'valueCodeableConcept: coding code',
  `value_display` varchar(255) DEFAULT NULL COMMENT 'valueReference: its display; valueCodeableConcept: the display of its coding',
  `value_text` text COMMENT 'the canonical, url, string or markdown; valueCodeableConcept: its text',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`, `seq`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable task_output
CREATE TABLE `task_output` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) NOT NULL COMMENT 'task.id',
  `seq` int(11) NOT NULL DEFAULT 0 COMMENT 'position in Task.output',
  `type_system` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.output.type: coding system',
  `type_code` varchar(63) DEFAULT NULL COMMENT 'FHIR Task.output.type: coding code',
  `type_display` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.output.type: coding display',
  `type_text` varchar(255) DEFAULT NULL COMMENT 'FHIR Task.output.type: text',
  `value_element` varchar(31) NOT NULL COMMENT 'which value[x] it is: valueReference, valueCanonical, valueUrl, valueString, valueMarkdown or valueCodeableConcept',
  `value_type` varchar(63) DEFAULT NULL COMMENT 'valueReference: resource type',
  `value_uuid` binary(16) DEFAULT NULL COMMENT 'valueReference: resource id',
  `value_system` varchar(255) DEFAULT NULL COMMENT 'valueCodeableConcept: coding system',
  `value_code` varchar(63) DEFAULT NULL COMMENT 'valueCodeableConcept: coding code',
  `value_display` varchar(255) DEFAULT NULL COMMENT 'valueReference: its display; valueCodeableConcept: the display of its coding',
  `value_text` text COMMENT 'the canonical, url, string or markdown; valueCodeableConcept: its text',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`, `seq`)
) ENGINE=InnoDB;
#EndIf
