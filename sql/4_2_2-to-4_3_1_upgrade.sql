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

--  #IfTableEngine
--    desc:      Execute SQL if the table has been created with given engine specified.
--    arguments: table_name engine
--    behavior:  Use when engine conversion requires more than one ALTER TABLE

--  #IfInnoDBMigrationNeeded
--    desc: find all MyISAM tables and convert them to InnoDB.
--    arguments: none
--    behavior: can take a long time.


--
-- The following tables contains TEXT NOT NULL DEFAULT "" declaration which is not compatible with InnoDB.
-- We omit NOT NULL DEFAULT "" declaration.
--

#IfTableEngine history_data MyISAM
-- remove NOT NULL DEFAULT "" declaration from TEXT fields.
ALTER TABLE `history_data` MODIFY `exams` TEXT, MODIFY userarea11 TEXT, MODIFY userarea12 TEXT NOT NULL, ENGINE=InnoDB;
#EndIf

#IfTableEngine lang_custom MyISAM
-- remove NOT NULL DEFAULT "" declaration from MEDIUMTEXT fields.
ALTER TABLE `lang_custom` MODIFY `constant_name` mediumtext, MODIFY `definition` mediumtext, ENGINE=InnoDB;
#EndIf

#IfTableEngine layout_options MyISAM
-- remove NOT NULL DEFAULT "" declaration from TEXT field.
ALTER TABLE `layout_options` MODIFY `conditions` text COMMENT 'serialized array of skip conditions', ENGINE=InnoDB;
#EndIf

#IfTableEngine patient_data MyISAM
-- remove NOT NULL DEFAULT "" declaration from TEXT field.
ALTER TABLE `patient_data` MODIFY  `billing_note` text, ENGINE=InnoDB;
#EndIf

#IfTableEngine rule_action_item MyISAM
-- remove NOT NULL DEFAULT "" declaration from TEXT field.
ALTER TABLE `rule_action_item` MODIFY `reminder_message` text COMMENT 'Custom message in patient reminder', ENGINE=InnoDB;
#EndIf

#IfTableEngine procedure_providers MyISAM
-- remove NOT NULL DEFAULT "" declaration from TEXT field.
ALTER TABLE `procedure_providers` MODIFY `notes`, ENGINE=InnoDB;
#EndIf

#IfTableEngine procedure_questions MyISAM
-- remove NOT NULL DEFAULT "" declaration from TEXT field.
ALTER TABLE `procedure_questions` MODIFY `options` text COMMENT 'choices for fldtype S and T', ENGINE=InnoDB;
#EndIf


#IfTableEngine procedure_order MyISAM
-- remove NOT NULL DEFAULT "" declaration from TEXT field.
ALTER TABLE `procedure_order` MODIFY `patient_instructions` TEXT, ENGINE=InnoDB;
#EndIf

#IfTableEngine procedure_order_code MyISAM
-- remove NOT NULL DEFAULT "" declaration from TEXT field.
ALTER TABLE `procedure_order_code` MODIFY `diagnoses` TEXT COMMENT 'diagnoses and maybe other coding (e.g. ICD9:111.11)', ENGINE=InnoDB;
#EndIf

#IfTableEngine procedure_report MyISAM
-- remove NOT NULL DEFAULT "" declaration from TEXT field.
ALTER TABLE `procedure_report` MODIFY `report_notes` TEXT COMMENT 'notes from the lab', ENGINE=InnoDB;
#EndIf

#IfTableEngine procedure_result MyISAM
-- remove NOT NULL DEFAULT "" declaration from TEXT field.
ALTER TABLE `procedure_result` MODIFY comments TEXT COMMENT 'comments from the lab', ENGINE=InnoDB;
#EndIf


--
-- The following 4 tables were using AUTO_INCREMENT field in the end of primary key.
-- For InnoDB we replaced the field with a sequence table . 
-- Instead of ONDELETE trigger, the deletion from equence tables is done in PHP code.
--

-- 1. ar_activity
--
#IfTableEngine ar_activity MyISAM
--
-- This table holds sequence number for combination of pid and encounter
CREATE TABLE IF NOT EXISTS `ar_activity_seq` (
  `pid` int(11) NOT NULL,
  `encounter` int(11) NOT NULL,
  `counter` int(11) unsigned NOT NULL,
  PRIMARY KEY (`pid` , `encounter`)
) ENGINE=InnoDB;

-- ar_activity table
INSERT INTO `ar_activity_seq`
SELECT `pid`, `encounter`, MAX(`sequence_no`) FROM `ar_activity` GROUP BY `pid`, `encounter`;

ALTER TABLE `ar_activity` MODIFY `sequence_no` int UNSIGNED NOT NULL COMMENT 'Sequence_no, incremented in code', ENGINE="InnoDB";
#EndIf


--
-- 2. claims
--

#IfTableEngine claims MyISAM
--
-- This table will hold sequence number for each combination of patient_id and encounter_id 
--
CREATE TABLE IF NOT EXISTS `claims_seq` (
  `patient_id` int(11) NOT NULL,
  `encounter_id` int(11) NOT NULL,
  `counter` int(11) unsigned NOT NULL,
  PRIMARY KEY (`patient_id` , `encounter_id`)
) ENGINE=InnoDB;

-- populate claim sequence table 
INSERT INTO `claims_seq`
SELECT `patient_id`, `encounter_id`, MAX(`version`) FROM `claims` GROUP BY `patient_id`, `encounter_id`;

ALTER TABLE `claims` MODIFY `version` int(10) UNSIGNED NOT NULL COMMENT 'Version, incremented in code';
ALTER TABLE `claims` ENGINE="InnoDB";
#EndIf



--
-- 3. procedure_answers 
--
#IfTableEngine procedure_answers MyISAM
--
-- This table holds sequence number for combination of procedure_order_id and procedure_order_seq and question_code
--
CREATE TABLE IF NOT EXISTS `procedure_answers_seq` (
  `procedure_order_id` bigint(20) NOT NULL DEFAULT '0',
  `procedure_order_seq` int(11) NOT NULL DEFAULT '0',
  `question_code` varchar(31) NOT NULL DEFAULT '',
  `counter` int(11) unsigned NOT NULL,
  PRIMARY KEY (`procedure_order_id`, `procedure_order_seq`, `question_code`)
) ENGINE=InnoDB;

-- procedure_answers table
INSERT INTO `procedure_answers_seq`
SELECT `procedure_order_id`, `procedure_order_seq`, `question_code`, MAX(`answer_seq`) FROM `procedure_answers` GROUP BY `procedure_order_id`, `procedure_order_seq`, `question_code`;


-- Modify the table for InnoDB
ALTER TABLE `procedure_answers` MODIFY `answer_seq` int(11) NOT NULL COMMENT 'Supports multiple-choice questions. Answer_seq, incremented in code';
ALTER TABLE `procedure_answers` ENGINE="InnoDB";
#EndIf


-- 
-- 4. procedure_order_code 
--

#IfTableEngine procedure_order_code MyISAM
--
-- Table structure for table `procedure_order_code_sequences`
-- This table holds a sequence number for every unique procedure_order_id 
--
CREATE TABLE IF NOT EXISTS `procedure_order_code_seq` (
  `procedure_order_id` bigint(20) NOT NULL,
  `counter` int(11) unsigned NOT NULL,
  PRIMARY KEY (`procedure_order_id`)
) ENGINE=InnoDB;

-- initialize counter 
INSERT INTO `procedure_order_code_seq`
SELECT `procedure_order_id`, MAX(`procedure_order_seq`) FROM `procedure_order_code` GROUP BY `procedure_order_id`;


-- Modify the table for InnoDB
ALTER TABLE `procedure_order_code` MODIFY `procedure_order_seq` int(11) NOT NULL COMMENT 'Supports multiple tests per order. Procedure_order_seq incremented in code';
ALTER TABLE `procedure_order_code` ENGINE="InnoDB";
#EndIf


--
-- Other tables do not need special treatment before convertion to InnoDB.
-- Warning: running this query can take a long time.
--
#IfInnoDBMigrationNeeded
-- Modifies all MyISAM tables to InnoDB
#EndIf