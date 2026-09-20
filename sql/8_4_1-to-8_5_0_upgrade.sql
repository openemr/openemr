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
#IfRow3D list_options list_id administrative_sex option_id Male codes SNOMED-CT:248152002
UPDATE `list_options` SET `codes` = 'SNOMED-CT:248153007' WHERE `list_id` = 'administrative_sex' AND `option_id` = 'Male';
#EndIf

#IfRow3D list_options list_id administrative_sex option_id Female codes SNOMED-CT:248153007
UPDATE `list_options` SET `codes` = 'SNOMED-CT:248152002' WHERE `list_id` = 'administrative_sex' AND `option_id` = 'Female';
#EndIf

-- Payer claim control numbers (ICN/DCN) can run to the X12 REF02 maximum of 50.
#IfNotColumnType ar_activity payer_claim_number varchar(50)
ALTER TABLE `ar_activity` MODIFY `payer_claim_number` VARCHAR(50) DEFAULT NULL COMMENT 'CLP07 from the payer 835';
#EndIf

-- HCFA box 22a is not Medicaid-specific; rename for clarity and widen to REF02's maximum.
#IfColumn form_misc_billing_options medicaid_original_reference
ALTER TABLE `form_misc_billing_options` CHANGE `medicaid_original_reference` `original_reference_number` VARCHAR(50) DEFAULT NULL;
#EndIf

#IfNotColumnType form_misc_billing_options original_reference_number varchar(50)
ALTER TABLE `form_misc_billing_options` MODIFY `original_reference_number` VARCHAR(50) DEFAULT NULL;
#EndIf

-- HCFA box 22 lost its "Medicaid" prefix in the 02/12 revision of the form.
#IfColumn form_misc_billing_options medicaid_resubmission_code
ALTER TABLE `form_misc_billing_options` CHANGE `medicaid_resubmission_code` `resubmission_code` VARCHAR(10) DEFAULT NULL;
#EndIf

-- Add TOTP replay-protection column: pairs with last_challenge (existing datetime column)
-- to reject the same 6-digit code being submitted twice within its 90-second acceptance
-- window (see MfaUtils::checkTOTP).
#IfMissingColumn login_mfa_registrations last_used_token
ALTER TABLE `login_mfa_registrations` ADD COLUMN `last_used_token` varchar(16) DEFAULT NULL COMMENT 'Last 6-digit TOTP that verified successfully. Compared with incoming code within the 90s acceptance window to reject replays.';
#EndIf
