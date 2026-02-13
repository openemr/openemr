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

--
-- Organization Type list (HL7 Value Set: OrganizationType)
-- See: https://github.com/openemr/openemr/issues/6826
--

#IfNotRow2D list_options list_id lists option_id organization-type
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('lists', 'organization-type', 'Organization Type', 1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'prov', 'Healthcare Provider', 10);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'dept', 'Hospital Department', 20);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'team', 'Organizational team', 30);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'govt', 'Government', 40);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'ins', 'Insurance Company', 50);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'pay', 'Payer', 60);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'edu', 'Educational Institute', 70);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'reli', 'Religious Institution', 80);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'crs', 'Clinical Research Sponsor', 90);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'cg', 'Community Group', 100);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'bus', 'Non-Healthcare Business or Corporation', 110);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('organization-type', 'other', 'Other', 120);
#EndIf

#IfMissingColumn facility organization_type
ALTER TABLE `facility` ADD `organization_type` VARCHAR(50) NOT NULL DEFAULT 'prov' COMMENT 'Organization type as defined by HL7 Value Set: OrganizationType';
#EndIf

--
-- Rename the misspelled list_options option_id from 'declne_to_specfy' to 'decline_to_specify',
-- and update any patient_data.race records that reference the old value.
-- See: https://github.com/openemr/openemr/issues/10385
--

#IfColumn patient_data race
UPDATE `patient_data` SET `race` = 'decline_to_specify' WHERE `race` = 'declne_to_specfy';
#EndIf

#IfRow2D list_options list_id race option_id declne_to_specfy
UPDATE `list_options` SET `option_id` = 'decline_to_specify' WHERE `list_id` = 'race' AND `option_id` = 'declne_to_specfy';
#EndIf

#IfRow2D list_options list_id language option_id declne_to_specfy
UPDATE `list_options` SET `option_id` = 'decline_to_specify' WHERE `list_id` = 'language' AND `option_id` = 'declne_to_specfy';
#EndIf

#IfRow2D list_options list_id ethrace option_id declne_to_specfy
UPDATE `list_options` SET `option_id` = 'decline_to_specify' WHERE `list_id` = 'ethrace' AND `option_id` = 'declne_to_specfy';
#EndIf
