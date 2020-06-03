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

#IfMissingColumn patient_filter_event_hook mode
ALTER TABLE `patient_filter_event_hook` ADD `mode` INT(1) NULL AFTER `description`;
#EndIf

#IfNotRow patient_filter_event_hook mode 3
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook30', '/test_hook30/', 'test hook30', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook31', '/test_hook31/', 'test hook31', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3);
#EndIf


#IfMissingColumn patient_filter_event_hook hook_type
ALTER TABLE `patient_filter_event_hook` ADD `hook_type` INT(1) NULL AFTER `mode`;
#EndIf

#IfNotRow patient_filter_event_hook hook_type 1
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`, `hook_type`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3, 1);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`, `hook_type`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3, 1);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`, `hook_type`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3, 1);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`, `hook_type`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3, 1);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`, `hook_type`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3, 1);
#EndIf

#IfMissingColumn patient_filter_event_hook hook_view
ALTER TABLE `patient_filter_event_hook` ADD `hook_view` INT(2) NULL AFTER `hook_type`;
#EndIf

#IfNotRow patient_filter_event_hook hook_view 11
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`, `hook_type`, `hook_view`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3, 1, 11);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`, `hook_type`, `hook_view`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3, 1, 11);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`, `hook_type`, `hook_view`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3, 1, 11);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`, `hook_type`, `hook_view`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3, 1, 11);
INSERT INTO `patient_filter_event_hook` (`name`, `route`, `description`, `mode`, `hook_type`, `hook_view`) VALUES ('test_hook32', '/test_hook32/', 'test hook32', 3, 1, 11);
#EndIf
