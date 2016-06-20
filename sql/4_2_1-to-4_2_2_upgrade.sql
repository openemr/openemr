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

#IfNotRow clinical_rules id rule_blood_pressure
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_blood_pressure', 0, 0, 0, 0, '', '', 0, '', 0);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id rule_blood_pressure
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_blood_pressure', 'Measure Blood Pressure', 1610, 0);
#EndIf

#IfNotRow rule_action id rule_blood_pressure
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_blood_pressure', 1, 'act_cat_measure', 'act_bp');
#EndIf

#IfNotRow rule_reminder id rule_blood_pressure
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_blood_pressure', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_blood_pressure', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_blood_pressure', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_blood_pressure', 'patient_reminder_post', 'month', '1');
#EndIf

#IfNotRow rule_target id rule_blood_pressure
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_blood_pressure', 1, 1, 1, 'target_database', '::form_vitals::bps::::::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_blood_pressure', 1, 1, 1, 'target_database', '::form_vitals::bpd::::::ge::1', 0);
#EndIf

#IfNotRow clinical_rules id rule_inr_measure
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_inr_measure', 0, 0, 0, 0, '', '', 0, '', 0);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id rule_inr_measure
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_inr_measure', 'Measure INR', 1620, 0);
#EndIf

#IfNotRow rule_action id rule_inr_measure
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_inr_measure', 1, 'act_cat_measure', 'act_lab_inr');
#EndIf

#IfNotRow rule_reminder id rule_inr_measure
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_inr_measure', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_inr_measure', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_inr_measure', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_inr_measure', 'patient_reminder_post', 'month', '1');
#EndIf

#IfNotRow rule_target id rule_inr_measure
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_inr_measure', 1, 1, 1, 'target_proc', 'INR::CPT4:85610::::::ge::1', 0);
#EndIf

#IfMissingColumn patient_data billing_note
SET @group_name = (SELECT group_name FROM layout_options WHERE field_id='lname' AND form_id='DEM');
SET @backup_group_name = (SELECT group_name FROM layout_options WHERE field_id='DOB' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_name = IFNULL(@group_name,@backup_group_name) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`) VALUES ('DEM', 'billing_note', IFNULL(@group_name,@backup_group_name), 'Billing Note', @seq+1, 2, 1, 60, 0, '', 1, 3, '', '', 'Patient Level Billing Note (Collections)' ) ;
ALTER TABLE patient_data ADD COLUMN billing_note text NOT NULL default '';
UPDATE `patient_data` SET `billing_note` = `genericval2` WHERE `genericname2` = 'Billing';
UPDATE `patient_data` SET `genericval2` = '', `genericname2` = '' WHERE `genericname2` = 'Billing';
#EndIf

#IfMissingColumn lang_languages lang_is_rtl
ALTER TABLE `lang_languages` ADD COLUMN `lang_is_rtl` TINYINT DEFAULT 0;
UPDATE `lang_languages` SET `lang_is_rtl`=1 WHERE `lang_code` IN ('he','ar') OR `lang_description` IN('Hebrew','Arabic');
#EndIf

#IfMissingColumn procedure_report date_collected_tz
ALTER TABLE `procedure_report` ADD COLUMN `date_collected_tz` varchar(5) DEFAULT '' COMMENT '+-hhmm offset from UTC';
#EndIf

#IfMissingColumn procedure_report date_report_tz
ALTER TABLE `procedure_report` ADD COLUMN `date_report_tz` varchar(5) DEFAULT '' COMMENT '+-hhmm offset from UTC';
#EndIf

UPDATE `clinical_rules` SET `cqm_2014_flag` = 1 WHERE `id` = 'rule_influenza_ge_50_cqm' AND `pid` = 0;
UPDATE `clinical_rules` SET `cqm_2014_flag` = 1 WHERE `id` = 'rule_dm_a1c_cqm' AND `pid` = 0;

#IfMissingColumn lists subtype
ALTER TABLE `lists` ADD COLUMN `subtype` varchar(31) NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn list_options subtype
ALTER TABLE `list_options` ADD COLUMN `subtype` varchar(31) NOT NULL DEFAULT '';
#EndIf

#IfNotRow2D list_options list_id lists option_id issue_subtypes
INSERT INTO list_options (list_id,option_id,title) VALUES ('lists','issue_subtypes','Issue Subtypes');
INSERT INTO list_options (list_id, option_id,title, seq) VALUES ('issue_subtypes', 'eye', 'Eye',10);
#EndIf

UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = 1, `amc_2014_stage2_flag` = 1 WHERE `id` = 'med_reconc_amc' AND `pid` = 0;
UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = 1, `amc_2014_stage2_flag` = 1 WHERE `id` = 'med_reconc_amc' AND `pid` = 0;
