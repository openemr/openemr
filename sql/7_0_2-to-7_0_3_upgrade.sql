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

#IfMissingColumn form_encounter last_update
ALTER TABLE `form_encounter` ADD `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfNotRow4D supported_external_dataloads load_type CQM_VALUESET load_source NIH_VSAC load_release_date 2023-05-04 load_filename ec_only_cms_20230504.xml.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('CQM_VALUESET', 'NIH_VSAC', '2023-05-04', 'ec_only_cms_20230504.xml.zip', 'b77b3c2a88d23de0ec427c1cfc5088ce');
#EndIf

#IfMissingColumn form_encounter ordering_provider_id
ALTER TABLE `form_encounter` ADD `ordering_provider_id` INT(11) DEFAULT '0' COMMENT 'ordering provider, if any, for this visit';
#EndIf

#IfMissingColumn session_tracker number_scripts
ALTER TABLE `session_tracker` ADD `number_scripts` bigint DEFAULT 1;
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2024-10-01 load_filename icd10OrderFiles2025_0.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2024-10-01', 'icd10OrderFiles2025_0.zip', '783c2e3c92778295a76b8d7e0ebcf8fd');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2024-10-01 load_filename Zip File 3 2025 ICD-10-PCS Codes File.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2024-10-01', 'Zip File 3 2025 ICD-10-PCS Codes File.zip', 'a47ceb9a09fcc475fec19cee6526a335');
#EndIf

#IfMissingColumn users date_created
ALTER TABLE `users` ADD `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn users last_updated
ALTER TABLE `users` ADD `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn facility date_created
ALTER TABLE `facility` ADD `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn facility last_updated
ALTER TABLE `facility` ADD `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn insurance_companies date_created
ALTER TABLE `insurance_companies` ADD `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn insurance_companies last_updated
ALTER TABLE `insurance_companies` ADD `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn facility_user_ids date_created
ALTER TABLE `facility_user_ids` ADD `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn facility_user_ids last_updated
ALTER TABLE `facility_user_ids` ADD `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn openemr_postcalendar_categories pc_last_updated
ALTER TABLE `openemr_postcalendar_categories` ADD `pc_last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn list_options last_updated
ALTER TABLE `list_options` ADD `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn form_clinical_notes last_updated
ALTER TABLE `form_clinical_notes` ADD `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn form_vitals last_updated
ALTER TABLE `form_vitals` ADD `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn procedure_providers date_created
ALTER TABLE `procedure_providers` ADD `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn procedure_providers last_updated
ALTER TABLE `procedure_providers` ADD `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn drugs date_created
ALTER TABLE `drugs` ADD `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn drugs last_updated
ALTER TABLE `drugs` ADD `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn patient_data last_updated
ALTER TABLE `patient_data` ADD `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf
