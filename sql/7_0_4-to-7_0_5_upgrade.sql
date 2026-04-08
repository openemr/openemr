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
--    behavior:  if the table exists but the column does not have the specified type,  the block will be executed

--  #IfNotColumnTypeDefault
--    arguments: table_name colname value value2
--    behavior:  if the table exists but the column does not have the specified type or default,  the block will be executed

--  #IfNotRow
--    arguments: table_name colname value
--    behavior:  if the table exists but the column does not have the specified value,  the block will be executed
--      (useful for insertions)

--  #IfNotRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  if the table exists but the column does not have the specified value AND the column2 does not have the specified value2,  the block will be executed
--      (useful for insertions)

--  #IfNotRow3D
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  if the table exists but the column does not have the specified value AND the column2 does not have the specified value2 AND the column3 does not have the specified value3,  the block will be executed
--      (useful for insertions)

--  #IfNotRow4D
--    arguments: table_name colname value colname2 value2 colname3 value3 colname4 value4
--    behavior:  if the table exists but the column does not have the specified value AND the column2 does not have the specified value2 AND the column3 does not have the specified value3 AND the column4 does not have the specified value4,  the block will be executed
--      (useful for insertions)

--  #IfNotIndex
--    arguments: table_name colname
--    behavior:  if the table exists but the index does not,  the block will be executed
--      (useful for adding indexes)

--  #IfNotMigrateClickOptions
--    behavior: if the clickoptions table does not exist, then that indicate user settings need to be migrated from globals.php to the tables

--  #EndIf
--    all blocks are terminated with and #EndIf

--  #IfNotListOccupation
-- Custom function for creating Occupation List

--  #IfNotListReaction
-- Custom function for creating Reaction List

--  #IfNotWenoRx
-- Custom function for importing new Weno formulary

--  #IfTextNullFixNeeded
--    Only include this block if text datatypes are null.

--  #IfTableEngine
--    argument: table_name engine
--    behavior: if the table_name have current engine,  the block will be executed

--  #IfInnoDBMigrationNeeded
--    behavior: if there are MyISAM tables needing to be migrated, the block will be executed

-- Create patient relationships table for contact tracing
#IfNotTable patient_relationships
CREATE TABLE `patient_relationships` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) DEFAULT NULL,
  `patient_id` bigint(20) NOT NULL COMMENT 'References patient_data.id',
  `related_patient_id` bigint(20) NOT NULL COMMENT 'References patient_data.id',
  `relationship_type` varchar(50) NOT NULL COMMENT 'Maps to list_options',
  `notes` text,
  `created_by` int(11) NOT NULL COMMENT 'References users.id',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `unique_relationship` (`patient_id`, `related_patient_id`, `relationship_type`, `active`),
  KEY `patient_id` (`patient_id`),
  KEY `related_patient_id` (`related_patient_id`),
  KEY `relationship_type` (`relationship_type`),
  FOREIGN KEY (`patient_id`) REFERENCES `patient_data` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`related_patient_id`) REFERENCES `patient_data` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
#EndIf

-- Create the parent list for patient relationship types
#IfNotRow2D list_options list_id lists option_id patient_relationship_types
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('lists', 'patient_relationship_types', 'Patient Relationship Types', 1, 0, 0, '', 'Types of relationships between patients for contact tracing', '', 0, 0, 1, '', 1);
#EndIf

-- Add list options for patient relationship types
#IfNotRow2D list_options list_id patient_relationship_types option_id lives_with
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('patient_relationship_types', 'lives_with', 'Lives With', 10, 0, 0, '', '', '', 0, 0, 1, '', 1);
#EndIf

#IfNotRow2D list_options list_id patient_relationship_types option_id works_with
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('patient_relationship_types', 'works_with', 'Works With', 20, 0, 0, '', '', '', 0, 0, 1, '', 1);
#EndIf

#IfNotRow2D list_options list_id patient_relationship_types option_id family_member
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('patient_relationship_types', 'family_member', 'Family Member', 30, 0, 0, '', '', '', 0, 0, 1, '', 1);
#EndIf

#IfNotRow2D list_options list_id patient_relationship_types option_id close_contact
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('patient_relationship_types', 'close_contact', 'Close Contact', 40, 0, 0, '', '', '', 0, 0, 1, '', 1);
#EndIf

#IfNotRow2D list_options list_id patient_relationship_types option_id household_member
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('patient_relationship_types', 'household_member', 'Household Member', 50, 0, 0, '', '', '', 0, 0, 1, '', 1);
#EndIf

#IfNotRow2D list_options list_id patient_relationship_types option_id caregiver
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('patient_relationship_types', 'caregiver', 'Caregiver', 60, 0, 0, '', '', '', 0, 0, 1, '', 1);
#EndIf

#IfNotRow2D list_options list_id patient_relationship_types option_id healthcare_worker
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('patient_relationship_types', 'healthcare_worker', 'Healthcare Worker', 70, 0, 0, '', '', '', 0, 0, 1, '', 1);
#EndIf

#IfNotRow2D list_options list_id patient_relationship_types option_id travel_companion
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('patient_relationship_types', 'travel_companion', 'Travel Companion', 80, 0, 0, '', '', '', 0, 0, 1, '', 1);
#EndIf
