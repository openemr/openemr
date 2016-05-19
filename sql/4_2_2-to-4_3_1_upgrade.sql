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


#IfTableEngine ar_activity MyISAM
ALTER TABLE `ar_activity` MODIFY `sequence_no` int UNSIGNED NOT NULL COMMENT 'Sequence_no, incremented in code';
ALTER TABLE `ar_activity` ENGINE="InnoDB";

--
-- Table structure for table `ar_activity_sequences`
--
CREATE TABLE IF NOT EXISTS `ar_activity_seq` (
  `pid` int(11) NOT NULL,
  `encounter` int(11) NOT NULL,
  `counter` int(11) unsigned NOT NULL,
  PRIMARY KEY (`pid` , `encounter`)
) ENGINE=InnoDB;

-- ar_activity table
INSERT INTO `ar_activity_seq`
SELECT `pid`, `encounter`, MAX(`sequence_no`) FROM `ar_activity` GROUP BY `pid`, `encounter`;

-- Trigger on delete from ar_activity table
DELIMITER $$

CREATE TRIGGER ar_activity_seq_after_delete
AFTER DELETE
   ON ar_activity FOR EACH ROW

BEGIN
  DECLARE c INT;

  SELECT COUNT(*) INTO c FROM ar_activity WHERE pid = OLD.pid AND encounter = OLD.encounter;
  IF c = 0 THEN
      DELETE FROM ar_activity_seq WHERE pid = OLD.pid AND encounter = OLD.encounter;
  END IF;

END $$
DELIMITER ;

#EndIf

#IfTableEngine claims MyISAM
ALTER TABLE `claims` MODIFY `version` int(10) UNSIGNED NOT NULL COMMENT 'Version, incremented in code';
ALTER TABLE `claims` ENGINE="InnoDB";

--
-- Table structure for table `claims_sequences`
--
CREATE TABLE IF NOT EXISTS `claims_seq` (
  `patient_id` int(11) NOT NULL,
  `encounter_id` int(11) NOT NULL,
  `counter` int(11) unsigned NOT NULL,
  PRIMARY KEY (`patient_id` , `encounter_id`)
) ENGINE=InnoDB;

-- claims table
INSERT INTO `claims_seq`
SELECT `patient_id`, `encounter_id`, MAX(`version`) FROM `claims` GROUP BY `patient_id`, `encounter_id`;

-- Trigger on delete from claims table
DELIMITER $$

CREATE TRIGGER claims_seq_after_delete
AFTER DELETE
   ON claims FOR EACH ROW

BEGIN
  DECLARE c INT;

  SELECT COUNT(*) INTO c FROM claims WHERE patient_id = OLD.patient_id AND encounter_id = OLD.encounter_id;
  IF c = 0 THEN
      DELETE FROM claims_seq WHERE patient_id = OLD.patient_id AND encounter_id = OLD.encounter_id;
  END IF;

END $$
DELIMITER ;

#EndIf

#IfTableEngine procedure_answers MyISAM
ALTER TABLE `procedure_answers` MODIFY `answer_seq` int(11) NOT NULL COMMENT 'Supports multiple-choice questions. Answer_seq, incremented in code';
ALTER TABLE `procedure_answers` ENGINE="InnoDB";

--
-- Table structure for table `procedure_answers_sequences`
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

-- Trigger on delete from procedure_answers table
DELIMITER $$

CREATE TRIGGER procedure_answers_seq_after_delete
AFTER DELETE
   ON procedure_answers FOR EACH ROW

BEGIN
  DECLARE c INT;

  SELECT COUNT(*) INTO c FROM procedure_answers WHERE procedure_order_id = OLD.procedure_order_id AND procedure_order_seq = OLD.procedure_order_seq AND question_code = OLD.question_code;
  IF c = 0 THEN
      DELETE FROM procedure_answers_seq WHERE procedure_order_id = OLD.procedure_order_id AND procedure_order_seq = OLD.procedure_order_seq AND question_code = OLD.question_code;
  END IF;

END $$
DELIMITER ;

#EndIf

#IfTableEngine procedure_order_code MyISAM
ALTER TABLE `procedure_order_code` MODIFY `procedure_order_seq` int(11) NOT NULL COMMENT 'Supports multiple tests per order. Procedure_order_seq, incremented in code';
ALTER TABLE `procedure_order_code` ENGINE="InnoDB";

--
-- Table structure for table `procedure_order_code_sequences`
--
CREATE TABLE IF NOT EXISTS `procedure_order_code_seq` (
  `procedure_order_id` bigint(20) NOT NULL,
  `counter` int(11) unsigned NOT NULL,
  PRIMARY KEY (`procedure_order_id`)
) ENGINE=InnoDB;

-- procedure_order_code table
INSERT INTO `procedure_order_code_seq`
SELECT `procedure_order_id`, MAX(`procedure_order_seq`) FROM `procedure_order_code` GROUP BY `procedure_order_id`;


-- Trigger on delete from procedure_order_code table
DELIMITER $$

CREATE TRIGGER procedure_order_code_seq_after_delete
AFTER DELETE
   ON procedure_order_code FOR EACH ROW

BEGIN
  DECLARE c INT;

  SELECT COUNT(*) INTO c FROM procedure_order_code WHERE procedure_order_id = OLD.procedure_order_id;
  IF c = 0 THEN
      DELETE FROM procedure_order_code_seq WHERE procedure_order_id = OLD.procedure_order_id;
  END IF;

END $$
DELIMITER ;

#EndIf

#IfInnoDBMigrationNeeded
#EndIf