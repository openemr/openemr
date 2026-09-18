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

#IfNotIndex lang_definitions lang_cons
CREATE INDEX `lang_cons` ON `lang_definitions` (`lang_id`, `cons_id`);
#EndIf

#IfMissingColumn patient_data pronoun
ALTER TABLE `patient_data` ADD COLUMN `pronoun` TEXT;
#EndIf

#IfNotRow list_options list_id pronoun
INSERT INTO list_options (list_id, option_id, title, seq, is_default, option_value, notes, activity) VALUES ('lists', 'pronoun', 'Pronouns', 0, 0, 0, '90778-2 (Personal pronouns - Reported): https://loinc.org/90778-2/', 1);

INSERT INTO list_options (list_id, option_id, title, seq, is_default, codes, activity) VALUES
   ('pronoun', 'he_him', 'he/him/his/his/himself', 10, 0, 'LOINC:LA29518-0', 1),
   ('pronoun', 'she_her', 'she/her/her/hers/herself', 20, 0, 'LOINC:LA29519-8', 1),
   ('pronoun', 'they_them', 'they/them/their/theirs/themselves', 30, 0, 'LOINC:LA29520-6', 1),
   ('pronoun', 'ask_me', 'Ask me', 40, 0, 'LOINC:LA27285-7', 1),
   ('pronoun', 'decline', 'Decline to answer', 50, 0, 'LOINC:LA30265-7', 1),
   ('pronoun', 'other', 'Other', 60, 0, 'LOINC:LA46-8', 1);
#EndIf

#IfRow3D layout_options form_id DEM field_id sex_identified conditions Sex
SET @group_id =(SELECT `group_id` FROM layout_options WHERE field_id='sex_identified' AND form_id='DEM');
SET @seq_sex_identified = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='sex_identified' AND form_id='DEM');
UPDATE `layout_options` SET `seq` = @seq_sex_identified - 5, `edit_options` = 'N', `fld_rows` = 0, `source` = 'F', `conditions` = '' WHERE `form_id` = 'DEM' AND `field_id` = 'sex_identified';
#EndIf

#IfNotRow2D layout_options form_id DEM field_id pronoun
SET @group_id = (SELECT `group_id` FROM `layout_options` WHERE `form_id`='DEM' AND `field_id`='sexual_orientation' LIMIT 1);
SET @seq = (SELECT `seq` FROM `layout_options` WHERE `form_id`='DEM' AND `field_id`='sexual_orientation' LIMIT 1) + 2;
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `description`, `edit_options`)
VALUES ('DEM', 'pronoun', @group_id, 'Pronouns', @seq, 1, 1, 0, 0, 'pronoun', 1, 1, 'Patient Pronouns', 'N');
#EndIf

#IfRow3D layout_options form_id DEM field_id sex_identified uor 2
UPDATE `layout_options` SET `uor` = 1 WHERE `form_id` = 'DEM' AND `field_id` = 'sex_identified';
#EndIf
