--
--  Comment Meta Language Constructs:
--
--  #IfNotTable
--    argument: table_name
--    behavior: if the table_name does not exist,  the block will be executed

--  #IfTable
--    argument: table_name
--    behavior: if the table_name does exist, the block will be executed

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
--	  arguments: table_name colname value colname2 value2 colname3 value3
--	  behavior:  If the table table_name does have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

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

#IfNotIndex form_encounter encounter_date
    CREATE INDEX encounter_date on form_encounter (`date`);
#EndIf

#IfNotColumnType prescriptions size varchar(16)
ALTER TABLE `prescriptions` CHANGE `size` `size` varchar(16) DEFAULT NULL;
#EndIf

#IfNotRow globals gl_name erx_newcrop_path
UPDATE `globals` SET `gl_name` = 'erx_newcrop_path' WHERE `gl_name` = 'erx_path_production';
#EndIf

#IfNotRow globals gl_name erx_newcrop_path_soap
UPDATE `globals` SET `gl_name` = 'erx_newcrop_path_soap' WHERE `gl_name` = 'erx_path_soap_production';
#EndIf

#IfNotRow globals gl_name erx_account_partner_name
UPDATE `globals` SET `gl_name` = 'erx_account_partner_name' WHERE `gl_name` = 'partner_name_production';
#EndIf

#IfNotRow globals gl_name erx_account_name
UPDATE `globals` SET `gl_name` = 'erx_account_name' WHERE `gl_name` = 'erx_name_production';
#EndIf

#IfNotRow globals gl_name erx_account_password
UPDATE `globals` SET `gl_name` = 'erx_account_password' WHERE `gl_name` = 'erx_password_production';
#EndIf

#IfNotColumnType lang_custom constant_name mediumtext
ALTER TABLE `lang_custom` CHANGE `constant_name` `constant_name` mediumtext NOT NULL default '';
#EndIf

#IfNotTable patient_tracker
CREATE TABLE `patient_tracker` (
  `id`                     bigint(20)   NOT NULL auto_increment,
  `date`                   datetime     DEFAULT NULL,
  `apptdate`               date         DEFAULT NULL,
  `appttime`               time         DEFAULT NULL,
  `eid`                    bigint(20)   NOT NULL default '0',
  `pid`                    bigint(20)   NOT NULL default '0',
  `original_user`          varchar(255) NOT NULL default '' COMMENT 'This is the user that created the original record',
  `encounter`              bigint(20)   NOT NULL default '0',
  `lastseq`                varchar(4)   NOT NULL default '' COMMENT 'The element file should contain this number of elements',
  `random_drug_test`       TINYINT(1)   DEFAULT NULL COMMENT 'NULL if not randomized. If randomized, 0 is no, 1 is yes', 
  `drug_screen_completed`  TINYINT(1)   NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY (`eid`),
  KEY (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=1;
#EndIf

#IfNotTable patient_tracker_element
CREATE TABLE `patient_tracker_element` (
  `pt_tracker_id`      bigint(20)   NOT NULL default '0' COMMENT 'maps to id column in patient_tracker table',
  `start_datetime`     datetime     DEFAULT NULL,
  `room`               varchar(20)  NOT NULL default '',
  `status`             varchar(31)  NOT NULL default '',
  `seq`                varchar(4)   NOT NULL default '' COMMENT 'This is a numerical sequence for this pt_tracker_id events',
  `user`               varchar(255) NOT NULL default '' COMMENT 'This is the user that created this element',
  KEY  (`pt_tracker_id`,`seq`)
) ENGINE=MyISAM;
#EndIf

#IfMissingColumn openemr_postcalendar_events pc_room
ALTER TABLE `openemr_postcalendar_events` ADD `pc_room` varchar(20) NOT NULL DEFAULT '' ;
#EndIf

#IfMissingColumn list_options toggle_setting_1
ALTER TABLE `list_options` ADD COLUMN `toggle_setting_1` tinyint(1) NOT NULL default '0';
UPDATE `list_options` SET `notes`='FF2414|10' , `toggle_setting_1`='1' WHERE `option_id`='@' AND `list_id` = 'apptstat';
UPDATE `list_options` SET `notes`='FF6619|10' , `toggle_setting_1`='1' WHERE `option_id`='~' AND `list_id` = 'apptstat';
#EndIf
 
#IfMissingColumn list_options toggle_setting_2
ALTER TABLE `list_options` ADD COLUMN `toggle_setting_2` tinyint(1) NOT NULL DEFAULT '0';
UPDATE `list_options` SET `notes`='0BBA34|0' , `toggle_setting_2`='1' WHERE `option_id`='!' AND `list_id` = 'apptstat';
UPDATE `list_options` SET `notes`='FEFDCF|0' , `toggle_setting_2`='1' WHERE `option_id`='>' AND `list_id` = 'apptstat';
UPDATE `list_options` SET `notes`='FEFDCF|0' WHERE `option_id`='-' AND `list_id` = 'apptstat';
UPDATE `list_options` SET `notes`='FFC9F8|0' WHERE `option_id`='*' AND `list_id` = 'apptstat';
UPDATE `list_options` SET `notes`='87FF1F|0' WHERE `option_id`='+' AND `list_id` = 'apptstat';
UPDATE `list_options` SET `notes`='BFBFBF|0' WHERE `option_id`='x' AND `list_id` = 'apptstat';
UPDATE `list_options` SET `notes`='BFBFBF|0' WHERE `option_id`='?' AND `list_id` = 'apptstat';
UPDATE `list_options` SET `notes`='FFFF2B|0' WHERE `option_id`='#' AND `list_id` = 'apptstat';
UPDATE `list_options` SET `notes`='52D9DE|10' WHERE `option_id`='<' AND `list_id` = 'apptstat';
UPDATE `list_options` SET `notes`='C0FF96|0' WHERE `option_id`='$' AND `list_id` = 'apptstat';
UPDATE `list_options` SET `notes`='BFBFBF|0' WHERE `option_id`='%' AND `list_id` = 'apptstat';
#EndIf

#IfNotRow2D list_options list_id lists option_id patient_flow_board_rooms
INSERT INTO list_options (list_id,option_id,title) VALUES ('lists','patient_flow_board_rooms','Patient Flow Board Rooms');
INSERT INTO list_options (list_id,option_id,title,seq) VALUES ('patient_flow_board_rooms', '1', 'Room 1', 10);
INSERT INTO list_options (list_id,option_id,title,seq) VALUES ('patient_flow_board_rooms', '2', 'Room 2', 20);
INSERT INTO list_options (list_id,option_id,title,seq) VALUES ('patient_flow_board_rooms', '3', 'Room 3', 30);
#EndIf

#IfMissingColumn clinical_rules developer
ALTER TABLE  `clinical_rules` ADD  `developer` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Clinical Rule Developer';
#EndIf

#IfMissingColumn clinical_rules funding_source
ALTER TABLE  `clinical_rules` ADD  `funding_source` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Clinical Rule Funding Source';
#EndIf

#IfMissingColumn clinical_rules release_version
ALTER TABLE  `clinical_rules` ADD  `release_version` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Clinical Rule Release Version';
#EndIf

#IfNotRow2D list_options list_id proc_res_abnormal option_id vhigh
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('proc_res_abnormal', 'vhigh', 'Above upper panic limits', 50);
#EndIf

#IfNotRow2D list_options list_id proc_res_abnormal option_id vlow
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('proc_res_abnormal', 'vlow', 'Below lower panic limits', 60);
#EndIf

UPDATE `clinical_rules` SET `cqm_2014_flag` = 1 WHERE `id` = 'rule_htn_bp_measure_cqm' AND `pid` = 0;
UPDATE `clinical_rules` SET `cqm_2014_flag` = 1 WHERE `id` = 'rule_adult_wt_screen_fu_cqm' AND `pid` = 0;
UPDATE `clinical_rules` SET `cqm_2014_flag` = 1 WHERE `id` = 'rule_wt_assess_couns_child_cqm' AND `pid` = 0;
UPDATE `clinical_rules` SET `cqm_2014_flag` = 1 WHERE `id` = 'rule_pneumovacc_ge_65_cqm' AND `pid` = 0;

#IfMissingColumn clinical_rules amc_2014_stage1_flag
        ALTER TABLE `clinical_rules` ADD COLUMN `amc_2014_stage1_flag` tinyint(1) COMMENT '2014 Stage 1 - Automated Measure Calculation flag for (unable to customize per patient)';

        UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = '1', `amc_code_2014` = '170.314(g)(1)/(2)–4' WHERE `clinical_rules`.`id` = 'problem_list_amc' AND `clinical_rules`.`pid` =0;
        UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = '1', `amc_code_2014` = '170.314(g)(1)/(2)–5' WHERE `clinical_rules`.`id` = 'med_list_amc' AND `clinical_rules`.`pid` =0;
        UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = '1', `amc_code_2014` = '170.314(g)(1)/(2)–6' WHERE `clinical_rules`.`id` = 'med_allergy_list_amc' AND `clinical_rules`.`pid` =0;
        UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = '1', `amc_code_2014` = '170.314(g)(1)/(2)–7' WHERE `clinical_rules`.`id` = 'cpoe_med_amc' AND `clinical_rules`.`pid` =0;
        UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = '1', `amc_code_2014` = '170.314(g)(1)/(2)–9' WHERE `clinical_rules`.`id` = 'record_dem_amc' AND `clinical_rules`.`pid` =0;
        UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = '1', `amc_code_2014` = '170.314(g)(1)/(2)–11' WHERE `clinical_rules`.`id` = 'record_smoke_amc' AND `clinical_rules`.`pid` =0;
        UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = '1', `amc_code_2014` = '170.314(g)(1)/(2)–12' WHERE `clinical_rules`.`id` = 'lab_result_amc' AND `clinical_rules`.`pid` =0;
        UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = '1', `amc_code_2014` = '170.314(g)(1)/(2)–13' WHERE `clinical_rules`.`id` = 'send_reminder_amc' AND `clinical_rules`.`pid` =0;
        UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = '1', `amc_code_2014` = '170.314(g)(1)/(2)–15' WHERE `clinical_rules`.`id` = 'provide_sum_pat_amc' AND `clinical_rules`.`pid` =0;
        UPDATE `clinical_rules` SET `amc_2014_stage1_flag` = '1', `amc_code_2014` = '170.314(g)(1)/(2)–16' WHERE `clinical_rules`.`id` = 'patient_edu_amc' AND `clinical_rules`.`pid` =0;
#EndIf

#IfMissingColumn clinical_rules amc_2014_stage2_flag
        ALTER TABLE `clinical_rules` ADD COLUMN `amc_2014_stage2_flag` tinyint(1) COMMENT '2014 Stage 2 - Automated Measure Calculation flag for (unable to customize per patient)';

        UPDATE `clinical_rules` SET `amc_2014_stage2_flag` = '1' WHERE `clinical_rules`.`id` = 'record_smoke_amc' AND `clinical_rules`.`pid` =0;
        UPDATE `clinical_rules` SET `amc_2014_stage2_flag` = '1' WHERE `clinical_rules`.`id` = 'lab_result_amc' AND `clinical_rules`.`pid` =0;
        UPDATE `clinical_rules` SET `amc_2014_stage2_flag` = '1' WHERE `clinical_rules`.`id` = 'record_dem_amc' AND `clinical_rules`.`pid` =0;
#EndIf

#IfNotRow clinical_rules id rule_children_pharyngitis_cqm
INSERT INTO `clinical_rules` (`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`) VALUES ('rule_children_pharyngitis_cqm', 0, 0, 0, 1, '0002', '', 0, '', 0, 0, 0, '', 1, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id rule_children_pharyngitis_cqm
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES ('clinical_rules', 'rule_children_pharyngitis_cqm', 'Appropriate Testing for Children with Pharyngitis (CQM)', 502, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id rule_fall_screening_cqm
INSERT INTO `clinical_rules` (`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`) VALUES ('rule_fall_screening_cqm', 0, 0, 0, 1, '0101', '', 0, '', 0, 0, 0, '', 1, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id rule_fall_screening_cqm
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES ('clinical_rules', 'rule_fall_screening_cqm', 'Falls: Screening, Risk-Assessment, and Plan of Care to Prevent Future Falls (CQM)', 504, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id rule_pain_intensity_cqm
INSERT INTO `clinical_rules` (`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`) VALUES ('rule_pain_intensity_cqm', 0, 0, 0, 1, '0384', '', 0, '', 0, 0, 0, '', 1, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id rule_pain_intensity_cqm
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES ('clinical_rules', 'rule_pain_intensity_cqm', 'Oncology: Medical and Radiation – Pain Intensity Quantified (CQM)', 506, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id rule_child_immun_stat_2014_cqm
INSERT INTO `clinical_rules` (`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES ('rule_child_immun_stat_2014_cqm', 0, 0, 0, 1, '0038', '', 0, '', 0, 0, 0, '', 0, 1, 0, 0);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id rule_child_immun_stat_2014_cqm
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES ('clinical_rules', 'rule_child_immun_stat_2014_cqm', 'Childhood immunization Status (CQM)', 250, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id rule_tob_use_2014_cqm
INSERT INTO `clinical_rules` (`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES ('rule_tob_use_2014_cqm', 0, 0, 0, 1, '0028', '', 0, '', 0, 0, 0, '', 0, 1, 0, 0);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id rule_tob_use_2014_cqm
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES ('clinical_rules', 'rule_tob_use_2014_cqm', 'Preventive Care and Screening: Tobacco Use: Screening and Cessation Intervention (CQM)', 210, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id image_results_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('image_results_amc', 0, 0, 0, 0, '', '', 1, '', 0, 0, 1, '170.314(g)(1)/(2)–20', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id image_results_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'image_results_amc', 'Image Results', 3000, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id family_health_history_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('family_health_history_amc', 0, 0, 0, 0, '', '', 1, '', 0, 0, 1, '170.314(g)(1)/(2)–21', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id family_health_history_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'family_health_history_amc', 'Family Health History', 3100, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id electronic_notes_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('electronic_notes_amc', 0, 0, 0, 0, '', '', 1, '', 0, 0, 1, '170.314(g)(1)/(2)–22', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id electronic_notes_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'electronic_notes_amc', 'Electronic Notes', 3200, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id secure_messaging_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('secure_messaging_amc', 0, 0, 0, 0, '', '', 1, '', 0, 0, 1, '170.314(g)(1)/(2)', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id secure_messaging_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'secure_messaging_amc', 'Secure Electronic Messaging', 3400, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id view_download_transmit_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('view_download_transmit_amc', 0, 0, 0, 0, '', '', 1, '', 0, 0, 1, '170.314(g)(1)/(2)–14', 0, 0, 1, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id view_download_transmit_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'view_download_transmit_amc', 'View, Download, Transmit (VDT)  (Measure B)', 3500, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id cpoe_radiology_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('cpoe_radiology_amc', 0, 0, 0, 0, '', '', 1, '170.304(a)', 0, 0, 1, '170.314(g)(1)/(2)–7', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id cpoe_radiology_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'cpoe_radiology_amc', 'Use CPOE for radiology orders.', 46, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id cpoe_proc_orders_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('cpoe_proc_orders_amc', 0, 0, 0, 0, '', '', 1, '170.304(a)', 0, 0, 1, '170.314(g)(1)/(2)–7', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id cpoe_proc_orders_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'cpoe_proc_orders_amc', 'Use CPOE for procedure orders.', 47, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id send_reminder_stage2_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('send_reminder_stage2_amc', 0, 0, 0, 0, '', '', 1, '170.304(d)', 0, 0, 1, '170.314(g)(1)/(2)–13', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id send_reminder_stage2_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'send_reminder_stage2_amc', 'Send reminders to patients per patient preference for preventive/follow up care.', 60, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id cpoe_med_stage2_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('cpoe_med_stage2_amc', 0, 0, 0, 0, '', '', 1, '170.304(a)', 0, 0, 1, '170.314(g)(1)/(2)–7', 0, 0, 1, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id cpoe_med_stage2_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'cpoe_med_stage2_amc', 'Use CPOE for medication orders.(Alternative)', 47, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id patient_edu_stage2_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('patient_edu_stage2_amc', 0, 0, 0, 0, '', '', 1, '170.302(m)', 0, 0, 1, '170.314(g)(1)/(2)–16', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id patient_edu_stage2_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'patient_edu_stage2_amc', 'Use certified EHR technology to identify patient-specific education resources and provide those resources to the patient if appropriate(New).', 40, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id record_vitals_1_stage1_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('record_vitals_1_stage1_amc', 0, 0, 0, 0, '', '', 1, '170.302(f)', 0, 0, 1, '170.314(g)(1)/(2)–10', 0, 0, 0, 0);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id record_vitals_1_stage1_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'record_vitals_1_stage1_amc', 'Record and chart changes in vital signs (SET 1).', 20, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id record_vitals_2_stage1_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('record_vitals_2_stage1_amc', 0, 0, 0, 0, '', '', 1, '170.302(f)', 0, 0, 1, '170.314(g)(1)/(2)–10', 0, 0, 1, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id record_vitals_2_stage1_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'record_vitals_2_stage1_amc', 'Record and chart changes in vital signs (BP out of scope).', 20, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id record_vitals_3_stage1_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('record_vitals_3_stage1_amc', 0, 0, 0, 0, '', '', 1, '170.302(f)', 0, 0, 1, '170.314(g)(1)/(2)–10', 0, 0, 1, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id record_vitals_3_stage1_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'record_vitals_3_stage1_amc', 'Record and chart changes in vital signs (Height / Weight out of scope).', 20, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id record_vitals_4_stage1_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('record_vitals_4_stage1_amc', 0, 0, 0, 0, '', '', 1, '170.302(f)', 0, 0, 1, '170.314(g)(1)/(2)–10', 0, 0, 1, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id record_vitals_4_stage1_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'record_vitals_4_stage1_amc', 'Record and chart changes in vital signs ( Height / Weight / BP with in scope ).', 20, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id record_vitals_stage2_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('record_vitals_stage2_amc', 0, 0, 0, 0, '', '', 1, '170.302(f)', 0, 0, 1, '170.314(g)(1)/(2)–10', 0, 0, 0, 0);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id record_vitals_stage2_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'record_vitals_stage2_amc', 'Record and chart changes in vital signs (New).', 20, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id provide_sum_pat_stage2_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('provide_sum_pat_stage2_amc', 0, 0, 0, 0, '', '', 1, '170.304(h)', 0, 0, 1, '170.314(g)(1)/(2)–15', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id provide_sum_pat_stage2_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'provide_sum_pat_stage2_amc', 'Provide clinical summaries for patients for each office visit (New).', 75, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id vdt_stage2_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('vdt_stage2_amc', 0, 0, 0, 0, '', '', 1, '', 0, 0, 1, '170.314(g)(1)/(2)–14', 0, 0, 1, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id vdt_stage2_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'vdt_stage2_amc', 'View, Download, Transmit (VDT) (Measure A)', 3500, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id send_sum_stage1_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('send_sum_stage1_amc', 0, 0, 0, 0, '', '', 1, '170.304(i)', 0, 0, 1, '170.314(g)(1)/(2)–18', 0, 0, 1, 0);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id send_sum_stage1_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'send_sum_stage1_amc', 'The EP, eligible hospital or CAH who transitions their patient to another setting of care or provider of care or refers their patient to another provider of care should provide summary of care record for each transition of care or referral.', 80, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id send_sum_1_stage2_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('send_sum_1_stage2_amc', 0, 0, 0, 0, '', '', 1, '170.304(i)', 0, 0, 1, '170.314(g)(1)/(2)–18', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id send_sum_1_stage2_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'send_sum_1_stage2_amc', 'The EP, eligible hospital or CAH who transitions their patient to another setting of care or provider of care or refers their patient to another provider of care should provide summary of care record for each transition of care or referral (Measure A).', 80, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id send_sum_stage2_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('send_sum_stage2_amc', 0, 0, 0, 0, '', '', 1, '170.304(i)', 0, 0, 1, '170.314(g)(1)/(2)–18', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id send_sum_stage2_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'send_sum_stage2_amc', 'The EP, eligible hospital or CAH who transitions their patient to another setting of care or provider of care or refers their patient to another provider of care should provide summary of care record for each transition of care or referral (Measure B).', 80, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id e_prescribe_stage1_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('e_prescribe_stage1_amc', 0, 0, 0, 0, '', '', 1, '170.304(b)', 0, 0, 1, '170.314(g)(1)/(2)–8', 0, 0, 1, 0);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id e_prescribe_stage1_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'e_prescribe_stage1_amc', 'Generate and transmit permissible prescriptions electronically (Uncontrolled Substances).', 50, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id e_prescribe_1_stage2_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('e_prescribe_1_stage2_amc', 0, 0, 0, 0, '', '', 1, '170.304(b)', 0, 0, 1, '170.314(g)(1)/(2)–8', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id e_prescribe_1_stage2_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'e_prescribe_1_stage2_amc', 'Generate and transmit permissible prescriptions electronically (All Prescriptions).', 50, 0, 0, '', '', '', 0, 0);
#EndIf

#IfNotRow clinical_rules id e_prescribe_2_stage2_amc
	INSERT INTO `clinical_rules` 
	(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES
	('e_prescribe_2_stage2_amc', 0, 0, 0, 0, '', '', 1, '170.304(b)', 0, 0, 1, '170.314(g)(1)/(2)–8', 0, 0, 0, 1);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id e_prescribe_2_stage2_amc
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES
	('clinical_rules', 'e_prescribe_2_stage2_amc', 'Generate and transmit permissible prescriptions electronically (Uncontrolled substances with drug formulary).', 50, 0, 0, '', '', '', 0, 0);
#EndIf

#IfMissingColumn users cpoe
	ALTER TABLE `users` ADD `cpoe` tinyint(1) NULL DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_order_code procedure_order_title
	ALTER TABLE  `procedure_order_code` ADD  `procedure_order_title` varchar( 255 ) NULL DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_providers lab_director
	ALTER TABLE `procedure_providers` ADD `lab_director` bigint(20) NOT NULL DEFAULT '0';
#EndIf

#IfNotRow2D list_options list_id lists option_id order_type
	INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','order_type','Order Types', 1,0);
	INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('order_type','procedure','Procedure',10,0);
	INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('order_type','intervention','Intervention',20,0);
	INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('order_type','laboratory_test','Laboratory Test',30,0);
	INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('order_type','physical_exam','Physical Exam',40,0);
	INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('order_type','risk_category','Risk Category Assessment',50,0);
	INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('order_type','patient_characteristics','Patient Characteristics',60,0);
	INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('order_type','imaging','Imaging',70,0);
#EndIf

