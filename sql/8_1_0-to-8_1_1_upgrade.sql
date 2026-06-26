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

#IfNotColumnType form_eye_antseg OSCONJ text
ALTER TABLE `form_eye_antseg`
  MODIFY COLUMN OSCONJ text;
#EndIf

-- Demographics "Stats" group: rename to "Additional Details" and give its UDS/social
-- fields proper input types (dropdowns, date picker, numeric inputs). Guards are keyed
-- on the original values so this is re-run safe and skips admin-customized fields.

#IfRow3D layout_group_properties grp_form_id DEM grp_group_id 5 grp_title Stats
UPDATE layout_group_properties SET grp_title = 'Additional Details' WHERE grp_form_id = 'DEM' AND grp_group_id = '5';
#EndIf

#IfNotRow2D list_options list_id lists option_id migrantseasonal
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('lists', 'migrantseasonal', 'Migratory/Seasonal Status', 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('migrantseasonal', 'migratory', 'Migratory', 10);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('migrantseasonal', 'seasonal', 'Seasonal', 20);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('migrantseasonal', 'neither', 'Neither', 30);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('migrantseasonal', 'unknown', 'Unknown', 40);
#EndIf

#IfNotRow2D list_options list_id lists option_id housing_status
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('lists', 'housing_status', 'Housing Status', 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('housing_status', 'not_homeless', 'Not homeless', 10);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('housing_status', 'shelter', 'Shelter', 20);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('housing_status', 'transitional', 'Transitional Housing', 30);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('housing_status', 'doubled_up', 'Doubled Up', 40);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('housing_status', 'street', 'Street', 50);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('housing_status', 'permanent_supportive', 'Permanent Supportive Housing', 60);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('housing_status', 'other', 'Other', 70);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('housing_status', 'unknown', 'Unknown', 80);
#EndIf

#IfNotRow2D list_options list_id LBF_Validations option_id pos_num
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`,`seq`) VALUES ('LBF_Validations','pos_num','Non-negative Number','{"numericality": {"greaterThanOrEqualTo": 0}}','85');
#EndIf

#IfRow3D layout_options form_id DEM field_id financial_review data_type 2
UPDATE layout_options SET data_type = 4, max_length = 10, default_value = 'now', edit_options = '', description = 'Date of last financial/sliding-fee review (defaults to today on registration)' WHERE form_id = 'DEM' AND field_id = 'financial_review';
#EndIf

#IfRow3D layout_options form_id DEM field_id migrantseasonal data_type 2
UPDATE layout_options SET data_type = 1, fld_length = 0, max_length = 0, list_id = 'migrantseasonal', title = 'Migratory/Seasonal', description = 'Migratory or seasonal agricultural worker status (UDS Lines 14-15)' WHERE form_id = 'DEM' AND field_id = 'migrantseasonal';
#EndIf

#IfRow3D layout_options form_id DEM field_id homeless data_type 2
UPDATE layout_options SET data_type = 1, fld_length = 0, max_length = 0, list_id = 'housing_status', title = 'Housing Status', description = 'Patient housing status (UDS Table 4)' WHERE form_id = 'DEM' AND field_id = 'homeless';
#EndIf

#IfNotRow3D layout_options form_id DEM field_id family_size validation int1
UPDATE layout_options SET fld_length = 6, max_length = 0, validation = 'int1', description = 'Number of people in the household' WHERE form_id = 'DEM' AND field_id = 'family_size';
#EndIf

#IfNotRow3D layout_options form_id DEM field_id monthly_income validation pos_num
UPDATE layout_options SET fld_length = 10, max_length = 0, validation = 'pos_num', description = 'Household monthly income (used for sliding-fee eligibility)' WHERE form_id = 'DEM' AND field_id = 'monthly_income';
#EndIf
