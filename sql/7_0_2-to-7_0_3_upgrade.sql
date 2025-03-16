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

#IfMissingColumn clinical_rules patient_sodh_usage
ALTER TABLE `clinical_rules` ADD `patient_dob_usage` TEXT
    COMMENT 'Description of how patient DOB is used by this rule';
ALTER TABLE `clinical_rules` ADD `patient_ethnicity_usage` TEXT
    COMMENT 'Description of how patient ethnicity is used by this rule';
ALTER TABLE `clinical_rules` ADD `patient_health_status_usage` TEXT
    COMMENT 'Description of how patient health status assessments are used by this rule';
ALTER TABLE `clinical_rules` ADD `patient_gender_identity_usage` TEXT
    COMMENT 'Description of how patient gender identity information is used by this rule';
ALTER TABLE `clinical_rules` ADD `patient_language_usage` TEXT
    COMMENT 'Description of how patient language information is used by this rule';
ALTER TABLE `clinical_rules` ADD `patient_race_usage` TEXT
    COMMENT 'Description of how patient race information is used by this rule';
ALTER TABLE `clinical_rules` ADD `patient_sex_usage` TEXT
    COMMENT 'Description of how patient birth sex information is used by this rule';
ALTER TABLE `clinical_rules` ADD `patient_sexual_orientation_usage` TEXT
    COMMENT 'Description of how patient sexual orientation is used by this rule';
ALTER TABLE `clinical_rules` ADD `patient_sodh_usage` TEXT
    COMMENT 'Description of how patient social determinants of health are used by this rule';
#EndIf

#IfNotRow2D list_options list_id lists option_id dsi_predictive_source_attributes
INSERT INTO `list_options` (`list_id`, `option_id`, `title`) VALUES ('lists', 'dsi_predictive_source_attributes', 'Predictive Decision Support Interventions Source Attributes');
-- Populate list with ONC default values
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_details_developer', 'Name and contact information for the intervention developer', 10);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_details_funding', 'Funding source of the technical implementation for the intervention(s) development', 20);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_details_output_value', 'Description of value that the intervention produces as an output', 30);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_details_output_type', 'Whether the intervention output is a prediction, classification, recommendation, evaluation, analysis, or other type of output', 40);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_purpose_intended_use', 'Intended use of the intervention', 50);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_purpose_patient_population', 'Intended patient population(s) for the intervention''s use', 60);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_purpose_intended_users', 'Intended user(s)', 70);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_purpose_decision_role', 'Intended decision-making role for which the intervention was designed to be used/for (e_g_, informs, augments, replaces clinical management)', 80);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_cautions_inappropriate_use', 'Description of tasks, situations, or populations where a user is cautioned against applying the intervention', 90);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_cautions_risks', 'Known risks, inappropriate settings, inappropriate uses, or known limitations of the intervention', 100);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_development_criteria', 'Exclusion and inclusion criteria that influenced the training data set', 110);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_development_variables', 'Use of variables in paragraph US § 170.315 (b)(11)(iv)(A)(5)-(13) as input features (use of race, ethnicity, language, sexual orientation, gender identity, sex, date of birth, social determinants of health, and health status assessment)', 120);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_development_demographics', 'Description of demographic representativeness according to variables in paragraph US § 170.315 (b)(11)(iv)(A)(5)-(13) including, at a minimum, those used as input features in the intervention', 130);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_development_relevance', 'Description of relevance of training data to intended deployed setting', 140);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_fairness_approach', 'Description of the approach the intervention developer has taken to ensure that the intervention''s output is fair', 150);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_fairness_bias_mitigation', 'Description of approaches to manage, reduce, or eliminate bias', 160);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_validation_data_source', 'Description of the data source, clinical setting, or environment where an intervention''s validity and fairness has been assessed, other than the source of training and testing data', 170);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_validation_tester', 'Party that conducted the external testing', 180);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_validation_demographics', 'Description of demographic representativeness of external data according to variables in paragraph US § 170.315 (b)(11)(iv)(A)(5)-(13) including, at a minimum, those used as input features in the intervention', 190);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_validation_process', 'Description of external validation process', 200);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_performance_internal_validity', 'Validity of intervention in test data derived from the same source as the initial training data', 210);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_performance_internal_fairness', 'Fairness of intervention in test data derived from the same source as the initial training data', 220);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_performance_external_validity', 'Validity of intervention in data external to or from a different source than the initial training data', 230);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_performance_external_fairness', 'Fairness of intervention in data external to or from a different source than the initial training data', 240);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_performance_outcome_evaluation', 'References to evaluation of use of the intervention on outcomes, including, bibliographic citations or hyperlinks to evaluations of how well the intervention reduced morbidity, mortality, length of stay, or other outcomes', 250);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_maintenance_validity_monitoring', 'Description of process and frequency by which the intervention''s validity is monitored over time', 260);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_maintenance_local_validity', 'Validity of intervention in local data', 270);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_maintenance_fairness_monitoring', 'Description of the process and frequency by which the intervention''s fairness is monitored over time', 280);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_maintenance_local_fairness', 'Fairness of intervention in local data', 290);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_updates_process', 'Description of process and frequency by which the intervention is updated', 300);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_predictive_source_attributes', 'predictive_updates_correction', 'Description of frequency by which the intervention''s performance is corrected when risks related to validity and fairness are identified', 310);
#EndIf

#IfNotRow2D list_options list_id lists option_id dsi_evidence_source_attributes
INSERT INTO `list_options` (`list_id`, `option_id`, `title`) VALUES ('lists', 'dsi_evidence_source_attributes', 'Evidence Based Decision Support Interventions Source Attributes');
-- Populate list with ONC default values
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_citation', 'Bibliographic citation of the intervention (clinical research or guideline)', 10);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_developer', 'Developer of the intervention (translation from clinical research or guideline)', 20);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_funding', 'Funding source of the technical implementation for the intervention(s) development', 30);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_dates', 'Release and, if applicable, revision dates of the intervention or reference source', 40);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_race', 'Intervention use of race as expressed in the standards in US § 170.213', 50);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_ethnicity', 'Intervention use of ethnicity as expressed in the standards in US § 170.213', 60);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_language', 'Intervention use of language as expressed in the standards in US § 170.213', 70);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_sexual_orientation', 'Intervention use of sexual orientation as expressed in the standards in US § 170.213', 80);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_gender_identity', 'Intervention use of gender identity as expressed in the standards in US § 170.213', 90);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_sex', 'Intervention use of sex as expressed in the standards in US § 170.213', 100);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_dob', 'Intervention use of date of birth as expressed in the standards in US § 170.213', 110);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_sdoh', 'Intervention use of social determinants of health data as expressed in the standards in US § 170.213', 120);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('dsi_evidence_source_attributes', 'evidence_based_health_status', 'Intervention use of health status assessments data as expressed in the standards in US § 170.213', 130);
#EndIf

#IfNotTable dsi_source_attributes
CREATE TABLE `dsi_source_attributes` (
     `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
     `client_id` VARCHAR(80) NOT NULL,
     `list_id` VARCHAR(100) NOT NULL,
     `option_id` VARCHAR(100) NOT NULL,
     `clinical_rule_id` VARCHAR(31) DEFAULT NULL,
     `source_value` TEXT,
     `created_by` BIGINT(20) DEFAULT NULL,
     `last_updated_by` BIGINT(20) DEFAULT NULL,
     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     `last_updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     PRIMARY KEY (`id`),
     UNIQUE (`list_id`, `option_id`, `client_id`)
) ENGINE=InnoDB COMMENT = 'Holds information about decission support intervention system source attributes';
#EndIf

#IfMissingColumn oauth_clients dsi_type
ALTER TABLE `oauth_clients` ADD `dsi_type` TINYINT UNSIGNED NOT NULL DEFAULT '1' COMMENT '0=none, 1=evidence-based,2=predictive';
#EndIf

#IfMissingColumn clinical_rules_log facility_id
ALTER TABLE `clinical_rules_log` ADD `facility_id` INT(11) DEFAULT '0' COMMENT 'facility where the rule was executed, 0 if unknown';
#EndIf

#IfRow2D list_options list_id drug_form title ml
UPDATE list_options SET title='mL' WHERE list_id='drug_form' AND title='ml';
#EndIf

#IfRow2D list_options list_id drug_route title OS
UPDATE list_options SET title='Left Eye' WHERE list_id='drug_route' AND title='OS';
#EndIf

#IfRow2D list_options list_id drug_route title OD
UPDATE list_options SET title='Right Eye' WHERE list_id='drug_route' AND title='OD';
#EndIf

#IfRow2D list_options list_id drug_route title OU
UPDATE list_options SET title='Each Eye' WHERE list_id='drug_route' AND title='OU';
#EndIf

#IfRow2D list_options list_id drug_route title SQ
UPDATE list_options SET title='Subcutaneous' WHERE list_id='drug_route' AND title='SQ';
#EndIf

#IfRow2D list_options list_id drug_interval title q.d.
UPDATE list_options SET title='Daily' WHERE list_id='drug_interval' AND title='q.d.';
#EndIf

#IfNotIndex sct2_description idx_term
ALTER TABLE sct2_description
    ADD INDEX idx_term (term),
    ADD INDEX idx_active_term (active, term),
    ADD FULLTEXT INDEX ft_term (term);
#EndIf

#IfMissingColumn onetime_auth scope
ALTER TABLE `onetime_auth` ADD `scope` tinytext COMMENT 'context scope for this token';
#EndIf

#IfMissingColumn onetime_auth profile
ALTER TABLE `onetime_auth` ADD `profile` tinytext COMMENT 'scopes profile for this token';
#EndIf

#IfMissingColumn onetime_auth onetime_actions
ALTER TABLE `onetime_auth` ADD `onetime_actions` text COMMENT 'JSON array of actions that can be performed with this token';
#EndIf
