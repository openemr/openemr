--
--  Comment Meta Language for sql upgrades:
--
--  Each section within an upgrade sql file is enveloped with an #If*/#EndIf block.  At first glance, these appear to be standard mysql
--  comments meant to be cryptic hints to -other developers about the sql goodness contained therein.  However, were you to rely on such basic premises,
--  you would find yourself grossly deceived.  Indeed, without the knowledge that these comments are, in fact a sneakily embedded meta language derived
--  for a purpose none-other than to aid in the protection of the database during upgrades,  you would no doubt be subject to much ridicule and public
--  beratement at the hands of the very developers who envisioned such a crafty use of comments. -jwallace
--
--  While these lines are as enigmatic as they are functional, there is a method to the madness.  Let's take a moment to briefly go over proper comment meta language use.
--
--  The #If* sections have the behavior of functions and come complete with arguments supplied command-line style
--
--  Your Comment meta language lines cannot contain any other comment styles such as the nefarious double dashes "--" lest your lines be skipped and
--  the blocks automatically executed without regard to the existing database state.
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
--    behavior:  if the colname in the table_name table does not exist,  the block will be executed

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

--  #EndIf
--    all blocks are terminated with and #EndIf statement.

#IfNotTable clinical_plans
CREATE TABLE `clinical_plans` (
  `id` varchar(31) NOT NULL DEFAULT '' COMMENT 'Unique and maps to list_options list clinical_plans',
  `pid` bigint(20) NOT NULL DEFAULT '0' COMMENT '0 is default for all patients, while > 0 is id from patient_data table',
  `normal_flag` tinyint(1) COMMENT 'Normal Activation Flag',
  `cqm_flag` tinyint(1) COMMENT 'Clinical Quality Measure flag (unable to customize per patient)',
  `cqm_measure_group` varchar(10) NOT NULL default '' COMMENT 'Clinical Quality Measure Group Identifier',
  PRIMARY KEY  (`id`,`pid`)
) ENGINE=MyISAM ;
INSERT INTO `clinical_plans` ( `id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group` ) VALUES ('dm_plan_cqm', 0, 0, 1, 'A');
INSERT INTO `clinical_plans` ( `id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group` ) VALUES ('ckd_plan_cqm', 0, 0, 1, 'C');
INSERT INTO `clinical_plans` ( `id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group` ) VALUES ('prevent_plan_cqm', 0, 0, 1, 'D');
INSERT INTO `clinical_plans` ( `id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group` ) VALUES ('periop_plan_cqm', 0, 0, 1, 'E');
INSERT INTO `clinical_plans` ( `id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group` ) VALUES ('rheum_arth_plan_cqm', 0, 0, 1, 'F');
INSERT INTO `clinical_plans` ( `id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group` ) VALUES ('back_pain_plan_cqm', 0, 0, 1, 'G');
INSERT INTO `clinical_plans` ( `id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group` ) VALUES ('cabg_plan_cqm', 0, 0, 1, 'H');
INSERT INTO `clinical_plans` ( `id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group` ) VALUES ('dm_plan', 0, 1, 0, '');
INSERT INTO `clinical_plans` ( `id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group` ) VALUES ('prevent_plan', 0, 1, 0, '');
#EndIf

#IfNotTable clinical_plans_rules
CREATE TABLE `clinical_plans_rules` (
  `plan_id` varchar(31) NOT NULL DEFAULT '' COMMENT 'Unique and maps to list_options list clinical_plans',
  `rule_id` varchar(31) NOT NULL DEFAULT '' COMMENT 'Unique and maps to list_options list clinical_rules',
  PRIMARY KEY  (`plan_id`,`rule_id`)
) ENGINE=MyISAM ;
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('dm_plan_cqm', 'rule_dm_bp_control_cqm');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('dm_plan_cqm', 'rule_dm_eye_cqm');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('dm_plan_cqm', 'rule_dm_foot_cqm');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan_cqm', 'rule_influenza_ge_50_cqm');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan_cqm', 'rule_pneumovacc_ge_65_cqm');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan_cqm', 'rule_adult_wt_screen_fu_cqm');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('dm_plan', 'rule_dm_hemo_a1c');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('dm_plan', 'rule_dm_urine_alb');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('dm_plan', 'rule_dm_eye');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('dm_plan', 'rule_dm_foot');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan', 'rule_htn_bp_measure');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan', 'rule_tob_use_assess');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan', 'rule_tob_cess_inter');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan', 'rule_adult_wt_screen_fu');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan', 'rule_wt_assess_couns_child');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan', 'rule_influenza_ge_50');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan', 'rule_pneumovacc_ge_65');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan', 'rule_cs_mammo');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan', 'rule_cs_pap');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan', 'rule_cs_colon');
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('prevent_plan', 'rule_cs_prostate');
#EndIf

#IfNotTable clinical_rules
CREATE TABLE `clinical_rules` (
  `id` varchar(31) NOT NULL DEFAULT '' COMMENT 'Unique and maps to list_options list clinical_rules',
  `pid` bigint(20) NOT NULL DEFAULT '0' COMMENT '0 is default for all patients, while > 0 is id from patient_data table',
  `active_alert_flag` tinyint(1) COMMENT 'Active Alert Widget Module flag - note not yet utilized',
  `passive_alert_flag` tinyint(1) COMMENT 'Passive Alert Widget Module flag',
  `cqm_flag` tinyint(1) COMMENT 'Clinical Quality Measure flag (unable to customize per patient)',
  `cqm_nqf_code` varchar(10) NOT NULL default '' COMMENT 'Clinical Quality Measure NQF identifier',
  `cqm_pqri_code` varchar(10) NOT NULL default '' COMMENT 'Clinical Quality Measure PQRI identifier',
  `amc_flag` tinyint(1) COMMENT 'Automated Measure Calculation flag (unable to customize per patient)',
  `amc_code` varchar(10) NOT NULL default '' COMMENT 'Automated Measure Calculation indentifier (MU rule)',
  `patient_reminder_flag` tinyint(1) COMMENT 'Clinical Reminder Module flag',
  PRIMARY KEY  (`id`,`pid`)
) ENGINE=MyISAM ;
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('problem_list_amc', 0, 0, 0, 0, '', '', 1, '170.302(c)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('med_list_amc', 0, 0, 0, 0, '', '', 1, '170.302(d)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('med_allergy_list_amc', 0, 0, 0, 0, '', '', 1, '170.302(e)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('record_vitals_amc', 0, 0, 0, 0, '', '', 1, '170.302(f)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('record_smoke_amc', 0, 0, 0, 0, '', '', 1, '170.302(g)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('lab_result_amc', 0, 0, 0, 0, '', '', 1, '170.302(h)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('med_reconc_amc', 0, 0, 0, 0, '', '', 1, '170.302(j)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('patient_edu_amc', 0, 0, 0, 0, '', '', 1, '170.302(m)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('cpoe_med_amc', 0, 0, 0, 0, '', '', 1, '170.304(a)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('e_prescribe_amc', 0, 0, 0, 0, '', '', 1, '170.304(b)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('record_dem_amc', 0, 0, 0, 0, '', '', 1, '170.304(c)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('send_reminder_amc', 0, 0, 0, 0, '', '', 1, '170.304(d)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('provide_rec_pat_amc', 0, 0, 0, 0, '', '', 1, '170.304(f)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('timely_access_amc', 0, 0, 0, 0, '', '', 1, '170.304(g)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('provide_sum_pat_amc', 0, 0, 0, 0, '', '', 1, '170.304(h)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('send_sum_amc', 0, 0, 0, 0, '', '', 1, '170.304(i)', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_htn_bp_measure_cqm', 0, 0, 0, 1, '0013', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_tob_use_assess_cqm', 0, 0, 0, 1, '0028a', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_tob_cess_inter_cqm', 0, 0, 0, 1, '0028b', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_adult_wt_screen_fu_cqm', 0, 0, 0, 1, '0421', '128', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_wt_assess_couns_child_cqm', 0, 0, 0, 1, '0024', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_influenza_ge_50_cqm', 0, 0, 0, 1, '0041', '110', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_child_immun_stat_cqm', 0, 0, 0, 1, '0038', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_pneumovacc_ge_65_cqm', 0, 0, 0, 1, '0043', '111', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_dm_eye_cqm', 0, 0, 0, 1, '0055', '117', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_dm_foot_cqm', 0, 0, 0, 1, '0056', '163', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_dm_bp_control_cqm', 0, 0, 0, 1, '0061', '3', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_htn_bp_measure', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_tob_use_assess', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_tob_cess_inter', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_adult_wt_screen_fu', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_wt_assess_couns_child', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_influenza_ge_50', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_pneumovacc_ge_65', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_dm_hemo_a1c', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_dm_urine_alb', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_dm_eye', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_dm_foot', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_cs_mammo', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_cs_pap', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_cs_colon', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_cs_prostate', 0, 0, 1, 0, '', '', 0, '', 0);
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_inr_monitor', 0, 0, 1, 0, '', '', 0, '', 0);
#EndIf

#IfNotTable enc_category_map
CREATE TABLE `enc_category_map` (
  `rule_enc_id` varchar(31) NOT NULL DEFAULT '' COMMENT 'encounter id from rule_enc_types list in list_options',
  `main_cat_id` int(11) NOT NULL DEFAULT 0 COMMENT 'category id from event category in openemr_postcalendar_categories',
  KEY  (`rule_enc_id`,`main_cat_id`)
) ENGINE=MyISAM ;
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_outpatient', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_outpatient', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_outpatient', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_nurs_fac', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_nurs_fac', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_nurs_fac', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_off_vis', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_off_vis', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_off_vis', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_hea_and_beh', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_hea_and_beh', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_hea_and_beh', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_occ_ther', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_occ_ther', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_occ_ther', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_psych_and_psych', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_psych_and_psych', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_psych_and_psych', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_ser_18_older', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_ser_18_older', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_ser_18_older', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_ind_counsel', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_ind_counsel', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_ind_counsel', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_group_counsel', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_group_counsel', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_group_counsel', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_other_serv', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_other_serv', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_other_serv', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_out_pcp_obgyn', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_out_pcp_obgyn', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_out_pcp_obgyn', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pregnancy', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pregnancy', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pregnancy', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_nurs_discharge', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_nurs_discharge', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_nurs_discharge', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_acute_inp_or_ed', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_acute_inp_or_ed', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_acute_inp_or_ed', 10);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_nonac_inp_out_or_opth', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_nonac_inp_out_or_opth', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_nonac_inp_out_or_opth', 10);
#EndIf

#IfNotTable patient_reminders
CREATE TABLE `patient_reminders` (
  `id` bigint(20) NOT NULL auto_increment,
  `active` tinyint(1) NOT NULL default 1 COMMENT '1 if active and 0 if not active',
  `date_inactivated` datetime DEFAULT NULL,
  `reason_inactivated` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to list_options list rule_reminder_inactive_opt',
  `due_status` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to list_options list rule_reminder_due_opt',
  `pid` bigint(20) NOT NULL COMMENT 'id from patient_data table',
  `category` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to the category item in the rule_action_item table',
  `item` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to the item column in the rule_action_item table',
  `date_created` datetime DEFAULT NULL,
  `date_sent` datetime DEFAULT NULL,
  `voice_status` tinyint(1) NOT NULL default 0 COMMENT '0 if not sent and 1 if sent',
  `sms_status` tinyint(1) NOT NULL default 0 COMMENT '0 if not sent and 1 if sent',
  `email_status` tinyint(1) NOT NULL default 0 COMMENT '0 if not sent and 1 if sent',
  `mail_status` tinyint(1) NOT NULL default 0 COMMENT '0 if not sent and 1 if sent',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY (`category`,`item`)
) ENGINE=MyISAM AUTO_INCREMENT=1;
#EndIf

#IfNotTable rule_action
CREATE TABLE `rule_action` (
  `id` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to the id column in the clinical_rules table',
  `group_id` bigint(20) NOT NULL DEFAULT 1 COMMENT 'Contains group id to identify collection of targets in a rule',
  `category` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to the category item in the rule_action_item table',
  `item` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to the item column in the rule_action_item table',
  KEY  (`id`)
) ENGINE=MyISAM ;
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_htn_bp_measure', 1, 'act_cat_measure', 'act_bp');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_tob_use_assess', 1, 'act_cat_assess', 'act_tobacco');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_tob_cess_inter', 1, 'act_cat_inter', 'act_tobacco');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_adult_wt_screen_fu', 1, 'act_cat_measure', 'act_wt');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_wt_assess_couns_child', 1, 'act_cat_measure', 'act_wt');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_wt_assess_couns_child', 2, 'act_cat_edu', 'act_wt');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_influenza_ge_50', 1, 'act_cat_treat', 'act_influvacc');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_pneumovacc_ge_65', 1, 'act_cat_treat', 'act_pneumovacc');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_dm_hemo_a1c', 1, 'act_cat_measure', 'act_hemo_a1c');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_dm_urine_alb', 1, 'act_cat_measure', 'act_urine_alb');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_dm_eye', 1, 'act_cat_exam', 'act_eye');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_dm_foot', 1, 'act_cat_exam', 'act_foot');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_cs_mammo', 1, 'act_cat_measure', 'act_mammo');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_cs_pap', 1, 'act_cat_exam', 'act_pap');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_cs_colon', 1, 'act_cat_assess', 'act_colon_cancer_screen');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_cs_prostate', 1, 'act_cat_assess', 'act_prostate_cancer_screen');
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_inr_monitor', 1, 'act_cat_measure', 'act_lab_inr');
#EndIf

#IfNotTable rule_action_item
CREATE TABLE `rule_action_item` (
  `category` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to list_options list rule_action_category',
  `item` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to list_options list rule_action',
  `clin_rem_link` varchar(255) NOT NULL DEFAULT '' COMMENT 'Custom html link in clinical reminder widget',
  `reminder_message` text  NOT NULL DEFAULT '' COMMENT 'Custom message in patient reminder',
  `custom_flag` tinyint(1) NOT NULL default 0 COMMENT '1 indexed to rule_patient_data, 0 indexed within main schema',
  PRIMARY KEY  (`category`,`item`)
) ENGINE=MyISAM ;
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_measure', 'act_bp', '', '', 0);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_assess', 'act_tobacco', '', '', 0);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_inter', 'act_tobacco', '', '', 1);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_measure', 'act_wt', '', '', 0);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_edu', 'act_wt', '', '', 1);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_treat', 'act_influvacc', '', '', 0);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_treat', 'act_pneumovacc', '', '', 0);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_measure', 'act_hemo_a1c', '', '', 1);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_measure', 'act_urine_alb', '', '', 1);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_exam', 'act_eye', '', '', 1);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_exam', 'act_foot', '', '', 1);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_measure', 'act_mammo', '', '', 1);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_exam', 'act_pap', '', '', 1);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_assess', 'act_colon_cancer_screen', '', '', 1);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_assess', 'act_prostate_cancer_screen', '', '', 1);
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_measure', 'act_lab_inr', '', '', 0);
#EndIf

#IfNotTable rule_filter
CREATE TABLE `rule_filter` (
  `id` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to the id column in the clinical_rules table',
  `include_flag` tinyint(1) NOT NULL default 0 COMMENT '0 is exclude and 1 is include',
  `required_flag` tinyint(1) NOT NULL default 0 COMMENT '0 is required and 1 is optional',
  `method` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to list_options list rule_filters',
  `method_detail` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to list_options lists rule__intervals',
  `value` varchar(255) NOT NULL DEFAULT '',
  KEY  (`id`)
) ENGINE=MyISAM ;
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'CUSTOM::HTN');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::401.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::401.1');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::401.9');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::402.00');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::402.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::402.10');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::402.11');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::402.90');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::402.91');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::403.00');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::403.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::403.10');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::403.11');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::403.90');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::403.91');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.00');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.10');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.11');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.12');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.13');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.90');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.91');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.92');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::404.93');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_tob_cess_inter', 1, 1, 'filt_database', '', 'LIFESTYLE::tobacco::current');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_wt_assess_couns_child', 1, 1, 'filt_age_max', 'year', '18');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_influenza_ge_50', 1, 1, 'filt_age_min', 'year', '50');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_pneumovacc_ge_65', 1, 1, 'filt_age_min', 'year', '65');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'CUSTOM::diabetes');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.00');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.10');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.11');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.12');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.13');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.20');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.21');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.22');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.23');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.30');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.31');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.32');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.33');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.4');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.40');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.41');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.42');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.43');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.50');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.51');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.52');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.53');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.60');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.61');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.62');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.63');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.7');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.70');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.71');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.72');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.73');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.80');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.81');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.82');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.83');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.9');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.90');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.91');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.92');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.93');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::357.2');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.04');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.05');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.06');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::366.41');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.00');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.04');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'CUSTOM::diabetes');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.00');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.10');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.11');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.12');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.13');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.20');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.21');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.22');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.23');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.30');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.31');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.32');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.33');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.4');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.40');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.41');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.42');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.43');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.50');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.51');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.52');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.53');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.60');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.61');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.62');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.63');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.7');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.70');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.71');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.72');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.73');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.80');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.81');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.82');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.83');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.9');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.90');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.91');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.92');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.93');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::357.2');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.04');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.05');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.06');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::366.41');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.00');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.04');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'CUSTOM::diabetes');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.00');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.10');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.11');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.12');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.13');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.20');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.21');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.22');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.23');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.30');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.31');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.32');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.33');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.4');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.40');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.41');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.42');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.43');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.50');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.51');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.52');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.53');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.60');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.61');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.62');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.63');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.7');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.70');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.71');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.72');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.73');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.80');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.81');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.82');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.83');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.9');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.90');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.91');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.92');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.93');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::357.2');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.04');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.05');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.06');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::366.41');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.00');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.04');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'CUSTOM::diabetes');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.00');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.10');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.11');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.12');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.13');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.20');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.21');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.22');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.23');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.30');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.31');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.32');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.33');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.4');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.40');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.41');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.42');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.43');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.50');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.51');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.52');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.53');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.60');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.61');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.62');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.63');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.7');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.70');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.71');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.72');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.73');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.80');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.81');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.82');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.83');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.9');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.90');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.91');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.92');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::250.93');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::357.2');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.04');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.05');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::362.06');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::366.41');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.0');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.00');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.01');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.02');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.03');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 1, 0, 'filt_lists', 'medical_problem', 'ICD9::648.04');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_mammo', 1, 1, 'filt_age_min', 'year', '40');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_mammo', 1, 1, 'filt_sex', '', 'Female');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_pap', 1, 1, 'filt_age_min', 'year', '18');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_pap', 1, 1, 'filt_sex', '', 'Female');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_colon', 1, 1, 'filt_age_min', 'year', '50');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_prostate', 1, 1, 'filt_age_min', 'year', '50');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_prostate', 1, 1, 'filt_sex', '', 'Male');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_inr_monitor', 1, 0, 'filt_lists', 'medication', 'coumadin');
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_inr_monitor', 1, 0, 'filt_lists', 'medication', 'warfarin');
#EndIf

#IfNotTable rule_patient_data
CREATE TABLE `rule_patient_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime DEFAULT NULL,
  `pid` bigint(20) NOT NULL,
  `category` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to the category item in the rule_action_item table',
  `item` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to the item column in the rule_action_item table',
  `complete` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to list_options list yesno',
  `result` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY (`pid`),
  KEY (`category`,`item`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
#EndIf

#IfNotTable rule_reminder
CREATE TABLE `rule_reminder` (
  `id` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to the id column in the clinical_rules table',
  `method` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to list_options list rule_reminder_methods',
  `method_detail` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to list_options list rule_reminder_intervals',
  `value` varchar(255) NOT NULL DEFAULT '',
  KEY  (`id`)
) ENGINE=MyISAM ;
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_htn_bp_measure', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_tob_use_assess', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_tob_use_assess', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_tob_use_assess', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_tob_use_assess', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_tob_cess_inter', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_tob_cess_inter', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_tob_cess_inter', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_tob_cess_inter', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_adult_wt_screen_fu', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_adult_wt_screen_fu', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_adult_wt_screen_fu', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_adult_wt_screen_fu', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_wt_assess_couns_child', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_wt_assess_couns_child', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_wt_assess_couns_child', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_wt_assess_couns_child', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_influenza_ge_50', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_influenza_ge_50', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_influenza_ge_50', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_influenza_ge_50', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_pneumovacc_ge_65', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_pneumovacc_ge_65', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_pneumovacc_ge_65', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_pneumovacc_ge_65', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_hemo_a1c', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_urine_alb', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_eye', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_dm_foot', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_mammo', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_mammo', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_mammo', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_mammo', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_pap', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_pap', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_pap', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_pap', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_colon', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_colon', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_colon', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_colon', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_prostate', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_prostate', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_prostate', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_cs_prostate', 'patient_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_inr_monitor', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_inr_monitor', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_inr_monitor', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_inr_monitor', 'patient_reminder_post', 'month', '1');
#EndIf

#IfNotTable rule_target
CREATE TABLE `rule_target` (
  `id` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to the id column in the clinical_rules table',
  `group_id` bigint(20) NOT NULL DEFAULT 1 COMMENT 'Contains group id to identify collection of targets in a rule',
  `include_flag` tinyint(1) NOT NULL default 0 COMMENT '0 is exclude and 1 is include',
  `required_flag` tinyint(1) NOT NULL default 0 COMMENT '0 is required and 1 is optional',
  `method` varchar(31) NOT NULL DEFAULT '' COMMENT 'Maps to list_options list rule_targets', 
  `value` varchar(255) NOT NULL DEFAULT '' COMMENT 'Data is dependent on the method',
  `interval` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Only used in interval entries', 
  KEY  (`id`)
) ENGINE=MyISAM ;
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_htn_bp_measure', 1, 1, 1, 'target_interval', 'year', 1);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_htn_bp_measure', 1, 1, 1, 'target_database', '::form_vitals::bps::::::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_htn_bp_measure', 1, 1, 1, 'target_database', '::form_vitals::bpd::::::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_tob_use_assess', 1, 1, 1, 'target_database', 'LIFESTYLE::tobacco::', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_tob_cess_inter', 1, 1, 1, 'target_interval', 'year', 1);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_tob_cess_inter', 1, 1, 1, 'target_database', 'CUSTOM::act_cat_inter::act_tobacco::YES::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_adult_wt_screen_fu', 1, 1, 1, 'target_database', '::form_vitals::weight::::::ge::2', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_wt_assess_couns_child', 1, 1, 1, 'target_database', '::form_vitals::weight::::::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_wt_assess_couns_child', 2, 1, 1, 'target_database', 'CUSTOM::act_cat_edu::act_wt::YES::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 1, 'target_interval', 'flu_season', 1);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::immunization_id::eq::30::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::immunization_id::eq::31::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_pneumovacc_ge_65', 1, 1, 0, 'target_database', '::immunizations::immunization_id::eq::19::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_pneumovacc_ge_65', 1, 1, 0, 'target_database', '::immunizations::immunization_id::eq::20::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_pneumovacc_ge_65', 1, 1, 0, 'target_database', '::immunizations::immunization_id::eq::21::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_pneumovacc_ge_65', 1, 1, 0, 'target_database', '::immunizations::immunization_id::eq::22::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_dm_hemo_a1c', 1, 1, 1, 'target_interval', 'month', 3);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_dm_hemo_a1c', 1, 1, 1, 'target_database', 'CUSTOM::act_cat_measure::act_hemo_a1c::YES::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_dm_urine_alb', 1, 1, 1, 'target_interval', 'year', 1);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_dm_urine_alb', 1, 1, 1, 'target_database', 'CUSTOM::act_cat_measure::act_urine_alb::YES::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_dm_eye', 1, 1, 1, 'target_interval', 'year', 1);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_dm_eye', 1, 1, 1, 'target_database', 'CUSTOM::act_cat_exam::act_eye::YES::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_dm_foot', 1, 1, 1, 'target_interval', 'year', 1);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_dm_foot', 1, 1, 1, 'target_database', 'CUSTOM::act_cat_exam::act_foot::YES::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_cs_mammo', 1, 1, 1, 'target_interval', 'year', 1);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_cs_mammo', 1, 1, 1, 'target_database', 'CUSTOM::act_cat_measure::act_mammo::YES::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_cs_pap', 1, 1, 1, 'target_interval', 'year', 1);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_cs_pap', 1, 1, 1, 'target_database', 'CUSTOM::act_cat_exam::act_pap::YES::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_cs_colon', 1, 1, 1, 'target_database', 'CUSTOM::act_cat_assess::act_colon_cancer_screen::YES::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_cs_prostate', 1, 1, 1, 'target_database', 'CUSTOM::act_cat_assess::act_prostate_cancer_screen::YES::ge::1', 0);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_inr_monitor', 1, 1, 1, 'target_interval', 'week', 3);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_inr_monitor', 1, 1, 1, 'target_proc', 'INR::::::::ge::1', 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id clinical_plans
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'clinical_plans','Clinical Plans', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_plans', 'dm_plan_cqm', 'Diabetes Mellitus', 5, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_plans', 'ckd_plan_cqm', 'Chronic Kidney Disease (CKD)', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_plans', 'prevent_plan_cqm', 'Preventative Care', 15, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_plans', 'periop_plan_cqm', 'Perioperative Care', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_plans', 'rheum_arth_plan_cqm', 'Rheumatoid Arthritis', 25, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_plans', 'back_pain_plan_cqm', 'Back Pain', 30, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_plans', 'cabg_plan_cqm', 'Coronary Artery Bypass Graft (CABG)', 35, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_plans', 'dm_plan', 'Diabetes Mellitus', 500, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_plans', 'prevent_plan', 'Preventative Care', 510, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id clinical_rules
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'clinical_rules','Clinical Rules', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'problem_list_amc', 'Maintain an up-to-date problem list of current and active diagnoses.', 5, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'med_list_amc', 'Maintain active medication list.', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'med_allergy_list_amc', 'Maintain active medication allergy list.', 15, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'record_vitals_amc', 'Record and chart changes in vital signs.', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'record_smoke_amc', 'Record smoking status for patients 13 years old or older.', 25, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'lab_result_amc', 'Incorporate clinical lab-test results into certified EHR technology as structured data.', 30, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'med_reconc_amc', 'The EP, eligible hospital or CAH who receives a patient from another setting of care or provider of care or believes an encounter is relevant should perform medication reconciliation.', 35, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'patient_edu_amc', 'Use certified EHR technology to identify patient-specific education resources and provide those resources to the patient if appropriate.', 40, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'cpoe_med_amc', 'Use CPOE for medication orders directly entered by any licensed healthcare professional who can enter orders into the medical record per state, local and professional guidelines.', 45, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'e_prescribe_amc', 'Generate and transmit permissible prescriptions electronically.', 50, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'record_dem_amc', 'Record demographics.', 55, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'send_reminder_amc', 'Send reminders to patients per patient preference for preventive/follow up care.', 60, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'provide_rec_pat_amc', 'Provide patients with an electronic copy of their health information (including diagnostic test results, problem list, medication lists, medication allergies), upon request.', 65, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'timely_access_amc', 'Provide patients with timely electronic access to their health information (including lab results, problem list, medication lists, medication allergies) within four business days of the information being available to the EP.', 70, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'provide_sum_pat_amc', 'Provide clinical summaries for patients for each office visit.', 75, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'send_sum_amc', 'The EP, eligible hospital or CAH who transitions their patient to another setting of care or provider of care or refers their patient to another provider of care should provide summary of care record for each transition of care or referral.', 80, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_htn_bp_measure_cqm', 'Hypertension: Blood Pressure Measurement (CQM)', 200, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_tob_use_assess_cqm', 'Tobacco Use Assessment (CQM)', 205, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_tob_cess_inter_cqm', 'Tobacco Cessation Intervention (CQM)', 210, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_adult_wt_screen_fu_cqm', 'Adult Weight Screening and Follow-Up (CQM)', 220, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_wt_assess_couns_child_cqm', 'Weight Assessment and Counseling for Children and Adolescents (CQM)', 230, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_influenza_ge_50_cqm', 'Influenza Immunization for Patients >= 50 Years Old (CQM)', 240, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_child_immun_stat_cqm', 'Childhood immunization Status (CQM)', 250, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_pneumovacc_ge_65_cqm', 'Pneumonia Vaccination Status for Older Adults (CQM)', 260, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_dm_eye_cqm', 'Diabetes: Eye Exam (CQM)', 270, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_dm_foot_cqm', 'Diabetes: Foot Exam (CQM)', 280, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_dm_bp_control_cqm', 'Diabetes: Blood Pressure Management (CQM)', 290, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_htn_bp_measure', 'Hypertension: Blood Pressure Measurement', 500, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_tob_use_assess', 'Tobacco Use Assessment', 510, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_tob_cess_inter', 'Tobacco Cessation Intervention', 520, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_adult_wt_screen_fu', 'Adult Weight Screening and Follow-Up', 530, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_wt_assess_couns_child', 'Weight Assessment and Counseling for Children and Adolescents', 540, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_influenza_ge_50', 'Influenza Immunization for Patients >= 50 Years Old', 550, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_pneumovacc_ge_65', 'Pneumonia Vaccination Status for Older Adults', 570, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_dm_hemo_a1c', 'Diabetes: Hemoglobin A1C', 570, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_dm_urine_alb', 'Diabetes: Urine Microalbumin', 590, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_dm_eye', 'Diabetes: Eye Exam', 600, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_dm_foot', 'Diabetes: Foot Exam', 610, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_cs_mammo', 'Cancer Screening: Mammogram', 620, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_cs_pap', 'Cancer Screening: Pap Smear', 630, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_cs_colon', 'Cancer Screening: Colon Cancer Screening', 640, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_cs_prostate', 'Cancer Screening: Prostate Cancer Screening', 650, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_inr_monitor', 'Coumadin Management - INR Monitoring', 1000, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_appt_reminder', 'Appointment Reminder Rule', 2000, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_targets
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_targets', 'Clinical Rule Target Methods', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_targets' ,'target_database', 'Database', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_targets' ,'target_interval', 'Interval', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_targets' ,'target_proc', 'Procedure', 20, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_target_intervals
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_target_intervals', 'Clinical Rules Target Intervals', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_target_intervals' ,'year', 'Year', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_target_intervals' ,'month', 'Month', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_target_intervals' ,'week', 'Week', 30, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_target_intervals' ,'day', 'Day', 40, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_target_intervals' ,'hour', 'Hour', 50, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_target_intervals' ,'minute', 'Minute', 60, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_target_intervals' ,'second', 'Second', 70, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_target_intervals' ,'flu_season', 'Flu Season', 80, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_comparisons
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_comparisons', 'Clinical Rules Comparisons', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_comparisons' ,'EXIST', 'Exist', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_comparisons' ,'lt', 'Less Than', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_comparisons' ,'le', 'Less Than or Equal To', 30, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_comparisons' ,'gt', 'Greater Than', 40, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_comparisons' ,'ge', 'Greater Than or Equal To', 50, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_filters
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_filters','Clinical Rule Filter Methods', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_filters', 'filt_database', 'Database', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_filters', 'filt_diagnosis', 'Diagnosis', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_filters', 'filt_sex', 'Gender', 30, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_filters', 'filt_age_max', 'Maximum Age', 40, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_filters', 'filt_age_min', 'Minimum Age', 50, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_age_intervals
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_age_intervals', 'Clinical Rules Age Intervals', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_age_intervals' ,'year', 'Year', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_age_intervals' ,'month', 'Month', 20, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_enc_types
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_enc_types', 'Clinical Rules Encounter Types', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_outpatient', 'encounter outpatient', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_nurs_fac', 'encounter nursing facility', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_off_vis', 'encounter office visit', 30, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_hea_and_beh', 'encounter health and behavior assessment', 40, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_occ_ther', 'encounter occupational therapy', 50, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_psych_and_psych', 'encounter psychiatric & psychologic', 60, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_pre_med_ser_18_older', 'encounter preventive medicine services 18 and older', 70, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_pre_ind_counsel', 'encounter preventive medicine - individual counseling', 80, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_pre_med_group_counsel', 'encounter preventive medicine group counseling', 90, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_pre_med_other_serv', 'encounter preventive medicine other services', 100, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_out_pcp_obgyn', 'encounter outpatient w/PCP & obgyn', 110, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_pregnancy', 'encounter pregnancy', 120, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_nurs_discharge', 'encounter nursing discharge', 130, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_acute_inp_or_ed', 'encounter acute inpatient or ED', 130, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_nonac_inp_out_or_opth', 'Encounter: encounter non-acute inpt, outpatient, or ophthalmology', 140, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_action_category
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_action_category', 'Clinical Rule Action Category', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action_category' ,'act_cat_assess', 'Assessment', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action_category' ,'act_cat_edu', 'Education', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action_category' ,'act_cat_exam', 'Examination', 30, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action_category' ,'act_cat_inter', 'Intervention', 40, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action_category' ,'act_cat_measure', 'Measurement', 50, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action_category' ,'act_cat_treat', 'Treatment', 60, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action_category' ,'act_cat_remind', 'Reminder', 70, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_action
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_action', 'Clinical Rule Action Item', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_bp', 'Blood Pressure', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_influvacc', 'Influenza Vaccine', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_tobacco', 'Tobacco', 30, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_wt', 'Weight', 40, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_pneumovacc', 'Pneumococcal Vaccine', 60, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_hemo_a1c', 'Hemoglobin A1C', 70, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_urine_alb', 'Urine Microalbumin', 80, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_eye', 'Opthalmic', 90, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_foot', 'Podiatric', 100, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_mammo', 'Mammogram', 110, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_pap', 'Pap Smear', 120, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_colon_cancer_screen', 'Colon Cancer Screening', 130, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_prostate_cancer_screen', 'Prostate Cancer Screening', 140, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_lab_inr', 'INR', 150, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_appointment', 'Appointment', 160, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_reminder_intervals
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_reminder_intervals', 'Clinical Rules Reminder Intervals', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_intervals' ,'month', 'Month', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_intervals' ,'week', 'Week', 20, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_reminder_methods
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_reminder_methods', 'Clinical Rules Reminder Methods', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_methods' ,'clinical_reminder_pre', 'Past Due Interval (Clinical Reminders)', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_methods' ,'patient_reminder_pre', 'Past Due Interval (Patient Reminders)', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_methods' ,'clinical_reminder_post', 'Soon Due Interval (Clinical Reminders)', 30, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_methods' ,'patient_reminder_post', 'Soon Due Interval (Patient Reminders)', 40, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_reminder_due_opt
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_reminder_due_opt', 'Clinical Rules Reminder Due Options', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_due_opt' ,'due', 'Due', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_due_opt' ,'soon_due', 'Due Soon', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_due_opt' ,'past_due', 'Past Due', 30, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_due_opt' ,'not_due', 'Not Due', 30, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id rule_reminder_inactive_opt
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('lists' ,'rule_reminder_inactive_opt', 'Clinical Rules Reminder Inactivation Options', 3, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_inactive_opt' ,'auto', 'Automatic', 10, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_inactive_opt' ,'due_status_update', 'Due Status Update', 20, 0);
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_reminder_inactive_opt' ,'manual', 'Manual', 20, 0);
#EndIf

#IfNotRow2D clinical_plans_rules plan_id dm_plan_cqm rule_id rule_dm_a1c_cqm
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('dm_plan_cqm', 'rule_dm_a1c_cqm');
#EndIf

#IfNotRow clinical_rules id rule_dm_a1c_cqm
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_dm_a1c_cqm', 0, 0, 0, 1, '0059', '1', 0, '', 0);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id rule_dm_a1c_cqm
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_dm_a1c_cqm', 'Diabetes: HbA1c Poor Control (CQM)', 285, 0);
#EndIf

#IfNotRow2D clinical_plans_rules plan_id dm_plan_cqm rule_id rule_dm_ldl_cqm
INSERT INTO `clinical_plans_rules` ( `plan_id`, `rule_id` ) VALUES ('dm_plan_cqm', 'rule_dm_ldl_cqm');
#EndIf

#IfNotRow clinical_rules id rule_dm_ldl_cqm
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_dm_ldl_cqm', 0, 0, 0, 1, '0064', '2', 0, '', 0);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id rule_dm_ldl_cqm
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_dm_ldl_cqm', 'Diabetes: LDL Management & Control (CQM)', 300, 0);
#EndIf

#IfMissingColumn form_encounter billing_facility
ALTER TABLE form_encounter ADD COLUMN billing_facility INTEGER;

update form_encounter set billing_facility = (SELECT id FROM facility ORDER BY billing_location DESC, id ASC LIMIT 1);
#EndIf

#IfMissingColumn facility color
ALTER TABLE facility ADD COLUMN color VARCHAR(7);
#EndIf

#IfMissingColumn openemr_postcalendar_events pc_billing_location
ALTER TABLE openemr_postcalendar_events ADD COLUMN pc_billing_location smallint(6);
#EndIf

#IfMissingColumn openemr_postcalendar_categories pc_cattype
ALTER TABLE `openemr_postcalendar_categories` ADD `pc_cattype` INT( 11 ) NOT NULL COMMENT 'Used in grouping categories';

UPDATE `openemr_postcalendar_categories` SET `pc_cattype`='1' WHERE `pc_catid`='4';
UPDATE `openemr_postcalendar_categories` SET `pc_cattype`='1' WHERE `pc_catid`='2';
UPDATE `openemr_postcalendar_categories` SET `pc_cattype`='1' WHERE `pc_catid`='3';
UPDATE `openemr_postcalendar_categories` SET `pc_cattype`='1' WHERE `pc_catid`='8';
UPDATE `openemr_postcalendar_categories` SET `pc_cattype`='1' WHERE `pc_catid`='11';
#EndIf

#IfMissingColumn patient_data deceased_date
ALTER TABLE `patient_data` ADD COLUMN `deceased_date` datetime default NULL;
#EndIf

#IfMissingColumn patient_data deceased_reason
ALTER TABLE `patient_data` ADD COLUMN `deceased_reason` varchar(255) NOT NULL default '';
#EndIf

#IfNotRow2D layout_options form_id DEM field_id deceased_date
UPDATE `layout_options` SET `seq` = `seq` + 2 WHERE `form_id` = 'DEM' AND `group_name` LIKE '%Misc';
INSERT INTO `layout_options` ( `form_id`, `field_id`, `group_name` , `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description` ) VALUES ('DEM', 'deceased_date', '6Misc', 'Date Deceased', 1, 4, 1, 20, 20, '', 1, 3, '', 'D', 'If person is deceased, then enter date of death.');
INSERT INTO `layout_options` ( `form_id`, `field_id`, `group_name` , `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description` ) VALUES ('DEM', 'deceased_reason', '6Misc', 'Reason Deceased', 2, 2, 1, 30, 255, '', 1, 3, '', '', 'Reason for Death.');
#EndIf

#IfNotTable lists_touch
CREATE TABLE `lists_touch` (
  `pid` bigint(20) NOT NULL default '0',
  `type` varchar(255) NOT NULL default '',
  `date` datetime default NULL,
  PRIMARY KEY  (`pid`,`type`)
) ENGINE=MyISAM ;
#EndIf

#IfNotTable amc_misc_data
CREATE TABLE `amc_misc_data` (
  `amc_id` varchar(31) NOT NULL DEFAULT '' COMMENT 'Unique and maps to list_options list clinical_rules',
  `pid` bigint(20) default NULL,
  `map_category` varchar(255) NOT NULL default '' COMMENT 'Maps to an object category (such as prescriptions etc.)',
  `map_id` bigint(20) NOT NULL default '0' COMMENT 'Maps to an object id (such as prescription id etc.)',
  `date_created` datetime default NULL,
  `date_completed` datetime default NULL,
  KEY  (`amc_id`,`pid`,`map_id`)
) ENGINE=MyISAM;
#EndIf

#IfNotRow2D layout_options form_id DEM field_id allow_patient_portal
INSERT INTO `layout_options` VALUES ('DEM', 'allow_patient_portal', '3Choices', 'Allow Patient Portal', 12, 1, 1, 0, 0, 'yesno', 1, 1, '', '', '');
#EndIf

#IfMissingColumn patient_data allow_patient_portal
ALTER TABLE `patient_data` ADD COLUMN `allow_patient_portal` varchar(31) NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn immunizations cvx_code
ALTER TABLE immunizations ADD COLUMN `cvx_code` int(11) default NULL;
#EndIf

#IfNotRow code_types ct_key CVX
DROP TABLE IF EXISTS `temp_table_one`;
CREATE TABLE `temp_table_one` (
  `id` int(11) NOT NULL DEFAULT '0',
  `seq` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM ;
INSERT INTO `temp_table_one` (`id`, `seq`) VALUES ( IF( ((SELECT MAX(`ct_id`) FROM `code_types`)>=100), ((SELECT MAX(`ct_id`) FROM `code_types`) + 1), 100 ) , IF( ((SELECT MAX(`ct_seq`) FROM `code_types`)>=100), ((SELECT MAX(`ct_seq`) FROM `code_types`) + 1), 100 )  );
INSERT INTO `code_types` (`ct_key`, `ct_id`, `ct_seq`, `ct_mod`, `ct_just`, `ct_fee`, `ct_rel`, `ct_nofs`, `ct_diag` ) VALUES ('CVX', (SELECT MAX(`id`) FROM `temp_table_one`), (SELECT MAX(`seq`) FROM `temp_table_one`), 0, ''    , 0, 0, 1, 0);
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `cyp_factor`, `active`) 
VALUES
(NULL, "diphtheria, tetanus toxoids and pertussis vaccine", "DTP", 1, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "poliovirus vaccine, live, oral", "OPV", 2, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "measles, mumps and rubella virus vaccine", "MMR", 3, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "measles and rubella virus vaccine", "M/R", 4, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "measles virus vaccine", "measles", 5, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "rubella virus vaccine", "rubella", 6, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "mumps virus vaccine", "mumps", 7, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis B vaccine, pediatric or pediatric/adolescent dosage", "Hep B, adolescent or pediatric", 8, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tetanus and diphtheria toxoids, adsorbed, for adult use", "Td (adult), adsorbed", 9, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "poliovirus vaccine, inactivated", "IPV", 10, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "pertussis vaccine", "pertussis", 11, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "diphtheria antitoxin", "diphtheria antitoxin", 12, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tetanus immune globulin", "TIG", 13, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "immune globulin, unspecified formulation", "IG, unspecified formulation", 14, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "influenza virus vaccine, split virus (incl. purified surface antigen)-retired CODE", "influenza, split (incl. purified surface antigen)", 15, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "influenza virus vaccine, whole virus", "influenza, whole", 16, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Haemophilus influenzae type b vaccine, conjugate unspecified formulation", "Hib, unspecified formulation", 17, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "rabies vaccine, for intramuscular injection", "rabies, intramuscular injection", 18, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Bacillus Calmette-Guerin vaccine", "BCG", 19, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "diphtheria, tetanus toxoids and acellular pertussis vaccine", "DTaP", 20, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "varicella virus vaccine", "varicella", 21, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "DTP-Haemophilus influenzae type b conjugate vaccine", "DTP-Hib", 22, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "plague vaccine", "plague", 23, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "anthrax vaccine", "anthrax", 24, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "typhoid vaccine, live, oral", "typhoid, oral", 25, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "cholera vaccine", "cholera", 26, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "botulinum antitoxin", "botulinum antitoxin", 27, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "diphtheria and tetanus toxoids, adsorbed for pediatric use", "DT (pediatric)", 28, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "cytomegalovirus immune globulin, intravenous", "CMVIG", 29, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis B immune globulin", "HBIG", 30, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis A vaccine, pediatric dosage, unspecified formulation", "Hep A, pediatric, unspecified formulation", 31, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "meningococcal polysaccharide vaccine (MPSV4)", "meningococcal MPSV4", 32, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "pneumococcal polysaccharide vaccine, 23 valent", "pneumococcal polysaccharide PPV23", 33, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "rabies immune globulin", "RIG", 34, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tetanus toxoid, adsorbed", "tetanus toxoid, adsorbed", 35, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "varicella zoster immune globulin", "VZIG", 36, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "yellow fever vaccine", "yellow fever", 37, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "rubella and mumps virus vaccine", "rubella/mumps", 38, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Japanese Encephalitis Vaccine SC", "Japanese encephalitis SC", 39, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "rabies vaccine, for intradermal injection", "rabies, intradermal injection", 40, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "typhoid vaccine, parenteral, other than acetone-killed, dried", "typhoid, parenteral", 41, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis B vaccine, adolescent/high risk infant dosage", "Hep B, adolescent/high risk infant", 42, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis B vaccine, adult dosage", "Hep B, adult", 43, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis B vaccine, dialysis patient dosage", "Hep B, dialysis", 44, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis B vaccine, unspecified formulation", "Hep B, unspecified formulation", 45, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Haemophilus influenzae type b vaccine, PRP-D conjugate", "Hib (PRP-D)", 46, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Haemophilus influenzae type b vaccine, HbOC conjugate", "Hib (HbOC)", 47, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Haemophilus influenzae type b vaccine, PRP-T conjugate", "Hib (PRP-T)", 48, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Haemophilus influenzae type b vaccine, PRP-OMP conjugate", "Hib (PRP-OMP)", 49, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "DTaP-Haemophilus influenzae type b conjugate vaccine", "DTaP-Hib", 50, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Haemophilus influenzae type b conjugate and Hepatitis B vaccine", "Hib-Hep B", 51, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis A vaccine, adult dosage", "Hep A, adult", 52, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "typhoid vaccine, parenteral, acetone-killed, dried (U.S. military)", "typhoid, parenteral, AKD (U.S. military)", 53, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "adenovirus vaccine, type 4, live, oral", "adenovirus, type 4", 54, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "adenovirus vaccine, type 7, live, oral", "adenovirus, type 7", 55, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "dengue fever vaccine", "dengue fever", 56, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hantavirus vaccine", "hantavirus", 57, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis C vaccine", "Hep C", 58, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis E vaccine", "Hep E", 59, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "herpes simplex virus, type 2 vaccine", "herpes simplex 2", 60, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "human immunodeficiency virus vaccine", "HIV", 61, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "human papilloma virus vaccine, quadrivalent", "HPV, quadrivalent", 62, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Junin virus vaccine", "Junin virus", 63, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "leishmaniasis vaccine", "leishmaniasis", 64, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "leprosy vaccine", "leprosy", 65, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Lyme disease vaccine", "Lyme disease", 66, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "malaria vaccine", "malaria", 67, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "melanoma vaccine", "melanoma", 68, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "parainfluenza-3 virus vaccine", "parainfluenza-3", 69, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Q fever vaccine", "Q fever", 70, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "respiratory syncytial virus immune globulin, intravenous", "RSV-IGIV", 71, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "rheumatic fever vaccine", "rheumatic fever", 72, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Rift Valley fever vaccine", "Rift Valley fever", 73, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "rotavirus, live, tetravalent vaccine", "rotavirus, tetravalent", 74, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "vaccinia (smallpox) vaccine", "vaccinia (smallpox)", 75, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Staphylococcus bacteriophage lysate", "Staphylococcus bacterio lysate", 76, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tick-borne encephalitis vaccine", "tick-borne encephalitis", 77, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tularemia vaccine", "tularemia vaccine", 78, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "vaccinia immune globulin", "vaccinia immune globulin", 79, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Venezuelan equine encephalitis, live, attenuated", "VEE, live", 80, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Venezuelan equine encephalitis, inactivated", "VEE, inactivated", 81, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "adenovirus vaccine, unspecified formulation", "adenovirus, unspecified formulation", 82, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis A vaccine, pediatric/adolescent dosage, 2 dose schedule", "Hep A, ped/adol, 2 dose", 83, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis A vaccine, pediatric/adolescent dosage, 3 dose schedule", "Hep A, ped/adol, 3 dose", 84, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis A vaccine, unspecified formulation", "Hep A, unspecified formulation", 85, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "immune globulin, intramuscular", "IG", 86, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "immune globulin, intravenous", "IGIV", 87, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "influenza virus vaccine, unspecified formulation", "influenza, unspecified formulation", 88, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "poliovirus vaccine, unspecified formulation", "polio, unspecified formulation", 89, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "rabies vaccine, unspecified formulation", "rabies, unspecified formulation", 90, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "typhoid vaccine, unspecified formulation", "typhoid, unspecified formulation", 91, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Venezuelan equine encephalitis vaccine, unspecified formulation", "VEE, unspecified formulation", 92, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "respiratory syncytial virus monoclonal antibody (palivizumab), intramuscular", "RSV-MAb", 93, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "measles, mumps, rubella, and varicella virus vaccine", "MMRV", 94, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tuberculin skin test; old tuberculin, multipuncture device", "TST-OT tine test", 95, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tuberculin skin test; purified protein derivative solution, intradermal", "TST-PPD intradermal", 96, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tuberculin skin test; purified protein derivative, multipuncture device", "TST-PPD tine test", 97, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tuberculin skin test; unspecified formulation", "TST, unspecified formulation", 98, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "RESERVED - do not use", "RESERVED - do not use", 99, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "pneumococcal conjugate vaccine, 7 valent", "pneumococcal conjugate PCV 7", 100, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "typhoid Vi capsular polysaccharide vaccine", "typhoid, ViCPs", 101, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "DTP- Haemophilus influenzae type b conjugate and hepatitis b vaccine", "DTP-Hib-Hep B", 102, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "meningococcal C conjugate vaccine", "meningococcal C conjugate", 103, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "hepatitis A and hepatitis B vaccine", "Hep A-Hep B", 104, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "vaccinia (smallpox) vaccine, diluted", "vaccinia (smallpox) diluted", 105, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "diphtheria, tetanus toxoids and acellular pertussis vaccine, 5 pertussis antigens", "DTaP, 5 pertussis antigens", 106, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "diphtheria, tetanus toxoids and acellular pertussis vaccine, unspecified formulation", "DTaP, unspecified formulation", 107, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "meningococcal vaccine, unspecified formulation", "meningococcal, unspecified formulation", 108, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "pneumococcal vaccine, unspecified formulation", "pneumococcal, unspecified formulation", 109, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "DTaP-hepatitis B and poliovirus vaccine", "DTaP-Hep B-IPV", 110, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "influenza virus vaccine, live, attenuated, for intranasal use", "influenza, live, intranasal", 111, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tetanus toxoid, unspecified formulation", "tetanus toxoid, unspecified formulation", 112, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tetanus and diphtheria toxoids, adsorbed, preservative free, for adult use", "Td (adult) preservative free", 113, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "meningococcal polysaccharide (groups A, C, Y and W-135) diphtheria toxoid conjugate vaccine (MCV4P)", "meningococcal MCV4P", 114, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tetanus toxoid, reduced diphtheria toxoid, and acellular pertussis vaccine, adsorbed", "Tdap", 115, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "rotavirus, live, pentavalent vaccine", "rotavirus, pentavalent", 116, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "varicella zoster immune globulin (Investigational New Drug)", "VZIG (IND)", 117, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "human papilloma virus vaccine, bivalent", "HPV, bivalent", 118, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "rotavirus, live, monovalent vaccine", "rotavirus, monovalent", 119, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "diphtheria, tetanus toxoids and acellular pertussis vaccine, Haemophilus influenzae type b conjugate, and poliovirus vaccine, inactivated (DTaP-Hib-IPV)", "DTaP-Hib-IPV", 120, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "zoster vaccine, live", "zoster", 121, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "rotavirus vaccine, unspecified formulation", "rotavirus, unspecified formulation", 122, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "influenza virus vaccine, H5N1, A/Vietnam/1203/2004 (national stockpile)", "influenza, H5N1-1203", 123, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Novel Influenza-H1N1-09, live virus for nasal administration", "Novel Influenza-H1N1-09, nasal", 125, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Novel influenza-H1N1-09, preservative-free, injectable", "Novel influenza-H1N1-09, preservative-free", 126, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Novel influenza-H1N1-09, injectable", "Novel influenza-H1N1-09", 127, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Novel influenza-H1N1-09, all formulations", "Novel Influenza-H1N1-09, all formulations", 128, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Japanese Encephalitis vaccine, unspecified formulation", "Japanese Encephalitis, unspecified formulation", 129, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Diphtheria, tetanus toxoids and acellular pertussis vaccine, and poliovirus vaccine, inactivated", "DTaP-IPV", 130, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Historical record of a typhus vaccination", "typhus, historical", 131, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Historical record of vaccine containing, diphtheria, tetanus toxoids and acellular pertussis, poliovirus, inactivated, Haemophilus influenzae type b conjugate, Hepatitis B (DTaP-Hib-IPV)", "DTaP-IPV-HIB-HEP B, historical", 132, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "pneumococcal conjugate vaccine, 13 valent", "Pneumococcal conjugate PCV 13", 133, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Japanese Encephalitis vaccine for intramuscular administration", "Japanese Encephalitis IM", 134, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "influenza, high dose seasonal, preservative-free", "Influenza, high dose seasonal", 135, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "meningococcal oligosaccharide (groups A, C, Y and W-135) diphtheria toxoid conjugate vaccine (MCV4O)", "Meningococcal MCV4O", 136, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "HPV, unspecified formulation", "HPV, unspecified formulation", 137, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tetanus and diphtheria toxoids, not adsorbed, for adult use", "Td (adult)", 138, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Td(adult) unspecified formulation", "Td(adult) unspecified formulation", 139, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Influenza, seasonal, injectable, preservative free", "Influenza, seasonal, injectable, preservative free", 140, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Influenza, seasonal, injectable", "Influenza, seasonal, injectable", 141, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "tetanus toxoid, not adsorbed", "tetanus toxoid, not adsorbed", 142, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "Adenovirus, type 4 and type 7, live, oral", "Adenovirus types 4 and 7", 143, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "no vaccine administered", "no vaccine administered", 998, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1),
(NULL, "unknown vaccine or immune globulin", "unknown", 999, (SELECT MAX(`id`) from `temp_table_one`), '', 0, 0, '', '', '', '', 1);
DROP TABLE `temp_table_one`;
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::15::ge::1
DELETE FROM `rule_target` WHERE `id` = 'rule_influenza_ge_50' AND `value` LIKE '::immunizations::immunization_id::%';
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::15::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::16::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::16::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::88::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::88::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::111::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::111::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::125::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::125::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::126::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::126::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::127::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::127::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::128::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::128::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::135::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::135::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::140::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::140::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::141::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::141::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_pneumovacc_ge_65 value ::immunizations::cvx_code::eq::33::ge::1
DELETE FROM `rule_target` WHERE `id` = 'rule_pneumovacc_ge_65' AND `value` LIKE '::immunizations::immunization_id::%';
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_pneumovacc_ge_65', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::33::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_pneumovacc_ge_65 value ::immunizations::cvx_code::eq::100::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_pneumovacc_ge_65', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::100::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_pneumovacc_ge_65 value ::immunizations::cvx_code::eq::109::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_pneumovacc_ge_65', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::109::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_pneumovacc_ge_65 value ::immunizations::cvx_code::eq::133::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_pneumovacc_ge_65', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::133::ge::1', 0);
#EndIf

#IfNotRow2D rule_target id rule_influenza_ge_50 value ::immunizations::cvx_code::eq::144::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_influenza_ge_50', 1, 1, 0, 'target_database', '::immunizations::cvx_code::eq::144::ge::1', 0);
#EndIf

#IfNotRow enc_category_map rule_enc_id enc_pre_med_ser_40_older
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_ser_40_older', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_ser_40_older', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_ser_40_older', 10);
#EndIf

#IfNotRow enc_category_map rule_enc_id enc_influenza
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_influenza', 5);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_influenza', 9);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_influenza', 10);
#EndIf

#IfNotRow2D list_options list_id rule_enc_types option_id enc_pre_med_ser_40_older
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_pre_med_ser_40_older', 'encounter preventive medicine 40 and older', 75, 0);
#EndIf

#IfNotRow2D list_options list_id rule_enc_types option_id enc_influenza
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_enc_types' ,'enc_influenza', 'encounter influenza', 150, 0);
#EndIf

#IfNotRow categories name CCR
INSERT INTO categories select (select MAX(id) from categories) + 1, 'CCR', '', 1, rght, rght + 1 from categories where name = 'Categories';
UPDATE categories SET rght = rght + 2 WHERE name = 'Categories';
UPDATE categories_seq SET id = (select MAX(id) from categories);
#Endif

#IfNotRow categories name CCD
INSERT INTO categories select (select MAX(id) from categories) + 1, 'CCD', '', 1, rght, rght + 1 from categories where name = 'Categories';
UPDATE categories SET rght = rght + 2 WHERE name = 'Categories';
UPDATE categories_seq SET id = (select MAX(id) from categories);
#Endif

#IfNotTable patient_access_onsite
CREATE TABLE `patient_access_onsite`(
  `id` INT NOT NULL AUTO_INCREMENT ,
  `pid` INT(11),
  `portal_username` VARCHAR(100) ,
  `portal_pwd` VARCHAR(100) ,
  `portal_pwd_status` TINYINT DEFAULT '1' COMMENT '0=>Password Created Through Demographics by The provider or staff. Patient Should Change it at first time it.1=>Pwd updated or created by patient itself',
  PRIMARY KEY (`id`)
)ENGINE=MyISAM AUTO_INCREMENT=1;
#EndIf

#IfNotTable standardized_tables_track
CREATE TABLE `standardized_tables_track` (
  `id` int(11) NOT NULL auto_increment,
  `imported_date` datetime default NULL,
  `name` varchar(255) NOT NULL default '' COMMENT 'name of standardized tables such as RXNORM',
  `revision_version` varchar(255) NOT NULL default '' COMMENT 'revision of standardized tables that were imported',
  `revision_date` datetime default NULL COMMENT 'revision of standardized tables that were imported',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
#EndIf

#IfNotRow2D list_options list_id rule_action option_id act_bmi
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_bmi', 'BMI', 43, 0);
#EndIf

#IfNotRow2D list_options list_id rule_action option_id act_nutrition
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_nutrition', 'Nutrition', 45, 0);
#EndIf

#IfNotRow2D list_options list_id rule_action option_id act_exercise
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_exercise', 'Exercise', 47, 0);
#EndIf

#IfNotRow4D rule_action id rule_wt_assess_couns_child group_id 3 category act_cat_edu item act_nutrition
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_wt_assess_couns_child', '3', 'act_cat_edu', 'act_nutrition');
#EndIf

#IfNotRow4D rule_action id rule_wt_assess_couns_child group_id 4 category act_cat_edu item act_exercise
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_wt_assess_couns_child', '4', 'act_cat_edu', 'act_exercise');
#EndIf

#IfNotRow4D rule_action id rule_wt_assess_couns_child group_id 5 category act_cat_measure item act_bmi
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_wt_assess_couns_child', '5', 'act_cat_measure', 'act_bmi');
#EndIf

#IfNotRow2D rule_action_item category act_cat_measure item act_bmi
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_measure', 'act_bmi', '', '', 0);
#EndIf

#IfNotRow2D rule_action_item category act_cat_edu item act_nutrition
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_edu', 'act_nutrition', '', '', 1);
#EndIf

#IfNotRow2D rule_action_item category act_cat_edu item act_exercise
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_edu', 'act_exercise', '', '', 1);
#EndIf

#IfNotRow2D rule_filter id rule_wt_assess_couns_child method filt_age_min
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_wt_assess_couns_child', 1, 1, 'filt_age_min', 'year', '2');
#EndIf

#IfNotRow3D rule_target id rule_wt_assess_couns_child group_id 1 method target_interval
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_wt_assess_couns_child', 1, 1, 1, 'target_interval', 'year', 3);
#EndIf

#IfNotRow3D rule_target id rule_wt_assess_couns_child group_id 2 method target_interval
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_wt_assess_couns_child', 2, 1, 1, 'target_interval', 'year', 3);
#EndIf

#IfNotRow4D rule_target id rule_wt_assess_couns_child group_id 3 method target_database value CUSTOM::act_cat_edu::act_nutrition::YES::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_wt_assess_couns_child', 3, 1, 1, 'target_database', 'CUSTOM::act_cat_edu::act_nutrition::YES::ge::1', 0);
#EndIf

#IfNotRow3D rule_target id rule_wt_assess_couns_child group_id 3 method target_interval
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_wt_assess_couns_child', 3, 1, 1, 'target_interval', 'year', 3);
#EndIf

#IfNotRow4D rule_target id rule_wt_assess_couns_child group_id 4 method target_database value CUSTOM::act_cat_edu::act_exercise::YES::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_wt_assess_couns_child', 4, 1, 1, 'target_database', 'CUSTOM::act_cat_edu::act_exercise::YES::ge::1', 0);
#EndIf

#IfNotRow3D rule_target id rule_wt_assess_couns_child group_id 4 method target_interval
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_wt_assess_couns_child', 4, 1, 1, 'target_interval', 'year', 3);
#EndIf

#IfNotRow4D rule_target id rule_wt_assess_couns_child group_id 5 method target_database value ::form_vitals::BMI::::::ge::1
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_wt_assess_couns_child', 5, 1, 1, 'target_database', '::form_vitals::BMI::::::ge::1', 0);
#EndIf

#IfNotRow3D rule_target id rule_wt_assess_couns_child group_id 5 method target_interval
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_wt_assess_couns_child', 5, 1, 1, 'target_interval', 'year', 3);
#EndIf

#IfMissingColumn prescriptions site
ALTER TABLE `prescriptions` ADD COLUMN `site` VARCHAR(50) DEFAULT NULL;
#EndIf

#IfMissingColumn prescriptions prescriptionguid
ALTER TABLE `prescriptions` ADD COLUMN `prescriptionguid` VARCHAR(50) DEFAULT NULL;
#EndIf

#IfMissingColumn prescriptions erx_source
ALTER TABLE `prescriptions` ADD COLUMN `erx_source` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0-OpenEMR 1-External';
#EndIf

#IfMissingColumn prescriptions rxnorm_drugcode
ALTER TABLE `prescriptions` ADD COLUMN `rxnorm_drugcode` INT(11) DEFAULT NULL;
#EndIf

#IfMissingColumn prescriptions datetime
ALTER TABLE `prescriptions` ADD COLUMN `datetime` DATETIME DEFAULT NULL;
#EndIf

#IfMissingColumn prescriptions user
ALTER TABLE `prescriptions` ADD COLUMN `user` VARCHAR(50) DEFAULT NULL;
#EndIf

#IfMissingColumn prescriptions erx_uploaded
ALTER TABLE `prescriptions` ADD COLUMN `erx_uploaded` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0-Pending NewCrop upload 1-Uploaded to NewCrop';
#EndIf

#IfMissingColumn lists external_allergyid
ALTER TABLE `lists` ADD COLUMN `external_allergyid` INT(11) DEFAULT NULL;
#EndIf

#IfMissingColumn lists erx_source
ALTER TABLE `lists` ADD COLUMN `erx_source` ENUM('0','1') DEFAULT '0' NOT NULL  COMMENT '0-OpenEMR 1-External';
#EndIf

#IfMissingColumn patient_data soap_import_status
ALTER TABLE `patient_data` ADD COLUMN `soap_import_status` TINYINT(4) DEFAULT NULL COMMENT '1-Prescription Press 2-Prescription Import 3-Allergy Press 4-Allergy Import';
#EndIf

#IfMissingColumn facility primary_business_entity
ALTER TABLE `facility` ADD COLUMN `primary_business_entity` INT(10) NOT NULL DEFAULT '0' COMMENT '0-Not Set as business entity 1-Set as business entity';
#EndIf

#IfMissingColumn users state_license_number
ALTER TABLE `users` ADD COLUMN `state_license_number` VARCHAR(25) DEFAULT NULL;
#EndIf

#IfMissingColumn users newcrop_user_role
ALTER TABLE `users` ADD COLUMN `newcrop_user_role` VARCHAR(30) DEFAULT NULL;
#EndIf

#IfNotRow2D list_options list_id lists option_id newcrop_erx_role
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('lists','newcrop_erx_role','NewCrop eRx Role','221','0','0','','');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('newcrop_erx_role','erxadmin','NewCrop Admin','5','0','0','','');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('newcrop_erx_role','erxdoctor','NewCrop Doctor','20','0','0','','');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('newcrop_erx_role','erxmanager','NewCrop Manager','15','0','0','','');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('newcrop_erx_role','erxmidlevelPrescriber','NewCrop Midlevel Prescriber','25','0','0','','');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('newcrop_erx_role','erxnurse','NewCrop Nurse','10','0','0','','');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('newcrop_erx_role','erxsupervisingDoctor','NewCrop Supervising Doctor','30','0','0','','');
#EndIf
#IfNotTable customlists
CREATE TABLE `customlists` (
  `cl_list_slno` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cl_list_id` int(10) unsigned NOT NULL COMMENT 'ID OF THE lIST FOR NEW TAKE SELECT MAX(cl_list_id)+1',
  `cl_list_item_id` int(10) unsigned DEFAULT NULL COMMENT 'ID OF THE lIST FOR NEW TAKE SELECT MAX(cl_list_item_id)+1',
  `cl_list_type` int(10) unsigned NOT NULL COMMENT '0=>List Name 1=>list items 2=>Context 3=>Template 4=>Sentence 5=> SavedTemplate 6=>CustomButton',
  `cl_list_item_short` varchar(10) DEFAULT NULL,
  `cl_list_item_long` text NOT NULL,
  `cl_list_item_level` int(11) DEFAULT NULL COMMENT 'Flow level for List Designation',
  `cl_order` int(11) DEFAULT NULL,
  `cl_deleted` tinyint(1) DEFAULT '0',
  `cl_creator` int(11) DEFAULT NULL,
  PRIMARY KEY (`cl_list_slno`)
) ENGINE=InnoDB AUTO_INCREMENT=1;
INSERT INTO customlists(cl_list_id,cl_list_type,cl_list_item_long) VALUES (1,2,'Subjective');
INSERT INTO customlists(cl_list_id,cl_list_type,cl_list_item_long) VALUES (2,2,'Objective');
INSERT INTO customlists(cl_list_id,cl_list_type,cl_list_item_long) VALUES (3,2,'Assessment');
INSERT INTO customlists(cl_list_id,cl_list_type,cl_list_item_long) VALUES (4,2,'Plan');
#EndIf

#IfNotTable template_users
CREATE TABLE `template_users` (
  `tu_id` int(11) NOT NULL AUTO_INCREMENT,
  `tu_user_id` int(11) DEFAULT NULL,
  `tu_facility_id` int(11) DEFAULT NULL,
  `tu_template_id` int(11) DEFAULT NULL,
  `tu_template_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`tu_id`),
  UNIQUE KEY `templateuser` (`tu_user_id`,`tu_template_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1;
#EndIf

#IfNotColumnType lbf_data field_value TEXT
ALTER TABLE `lbf_data`  CHANGE `field_value` `field_value` TEXT NOT NULL;
#EndIf

#IfNotTable automatic_notification
CREATE TABLE `automatic_notification` (
  `notification_id` int(5) NOT NULL auto_increment,
  `sms_gateway_type` varchar(255) NOT NULL,
  `next_app_date` date NOT NULL,
  `next_app_time` varchar(10) NOT NULL,
  `provider_name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `email_sender` varchar(100) NOT NULL,
  `email_subject` varchar(100) NOT NULL,
  `type` enum('SMS','Email') NOT NULL default 'SMS',
  `notification_sent_date` datetime NOT NULL,
  PRIMARY KEY  (`notification_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 ;
INSERT INTO `automatic_notification` (`notification_id`, `sms_gateway_type`, `next_app_date`, `next_app_time`, `provider_name`, `message`, `email_sender`, `email_subject`, `type`, `notification_sent_date`) VALUES (1, 'CLICKATELL', '0000-00-00', ':', 'EMR GROUP 1 .. SMS', 'Welcome to EMR GROUP 1.. SMS', '', '', 'SMS', '0000-00-00 00:00:00'),
(2, '', '2007-10-02', '05:50', 'EMR GROUP', 'Welcome to EMR GROUP . Email', 'EMR Group', 'Welcome to EMR GROUP', 'Email', '2007-09-30 00:00:00');
#EndIf

#IfNotTable notification_log
CREATE TABLE `notification_log` (
  `iLogId` int(11) NOT NULL auto_increment,
  `pid` int(7) NOT NULL,
  `pc_eid` int(11) unsigned NULL,
  `sms_gateway_type` varchar(50) NOT NULL,
  `smsgateway_info` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `email_sender` varchar(255) NOT NULL,
  `email_subject` varchar(255) NOT NULL,
  `type` enum('SMS','Email') NOT NULL,
  `patient_info` text NOT NULL,
  `pc_eventDate` date NOT NULL,
  `pc_endDate` date NOT NULL,
  `pc_startTime` time NOT NULL,
  `pc_endTime` time NOT NULL,
  `dSentDateTime` datetime NOT NULL,
  PRIMARY KEY  (`iLogId`)
) ENGINE=InnoDB AUTO_INCREMENT=5 ;
#EndIf

#IfNotTable notification_settings
CREATE TABLE `notification_settings` (
  `SettingsId` int(3) NOT NULL auto_increment,
  `Send_SMS_Before_Hours` int(3) NOT NULL,
  `Send_Email_Before_Hours` int(3) NOT NULL,
  `SMS_gateway_username` varchar(100) NOT NULL,
  `SMS_gateway_password` varchar(100) NOT NULL,
  `SMS_gateway_apikey` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  PRIMARY KEY  (`SettingsId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 ;
INSERT INTO `notification_settings` (`SettingsId`, `Send_SMS_Before_Hours`, `Send_Email_Before_Hours`, `SMS_gateway_username`, `SMS_gateway_password`, `SMS_gateway_apikey`, `type`) VALUES (1, 150, 150, 'sms username', 'sms password', 'sms api key', 'SMS/Email Settings');
#EndIf

#IfMissingColumn openemr_postcalendar_events pc_sendalertsms
ALTER TABLE openemr_postcalendar_events ADD pc_sendalertsms VARCHAR(3) NOT NULL DEFAULT 'NO';
#EndIf

#IfMissingColumn openemr_postcalendar_events pc_sendalertemail
ALTER TABLE openemr_postcalendar_events ADD pc_sendalertemail VARCHAR(3) NOT NULL DEFAULT 'NO';
#EndIf

#IfNotRow4D rule_target id rule_inr_monitor group_id 1 method target_proc value INR::CPT4:85610::::::ge::1
UPDATE `rule_target` SET `value` = 'INR::CPT4:85610::::::ge::1' WHERE `id` = 'rule_inr_monitor' AND `group_id` = 1 AND `method` = 'target_proc' AND `value` = 'INR::::::::ge::1';
#EndIf

#IfNotRow2D list_options list_id rule_targets option_id target_appt
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_targets' ,'target_appt', 'Appointment', 30, 0);
#EndIf

#IfNotRow2D list_options list_id rule_filters option_id filt_proc
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_filters', 'filt_proc', 'Procedure', 60, 0);
#EndIf

#IfNotRow2D list_options list_id rule_filters option_id filt_lists
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_filters', 'filt_lists', 'Lists', 70, 0);
#EndIf

#IfNotRow4D rule_target id rule_adult_wt_screen_fu group_id 1 method target_database value ::form_vitals::weight::::::ge::1
UPDATE `rule_target` SET `value` = '::form_vitals::weight::::::ge::1' WHERE `id` = 'rule_adult_wt_screen_fu' AND `group_id` = 1 AND `method` = 'target_database' AND `value` = '::form_vitals::weight::::::ge::2';
#EndIf

#IfNotRow2D rule_filter id rule_adult_wt_screen_fu method filt_age_min
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_adult_wt_screen_fu', 1, 1, 'filt_age_min', 'year', '18');
#EndIf

#EndIf
#IfNotTable documents_legal_detail
CREATE TABLE `documents_legal_detail` (
  `dld_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dld_pid` int(10) unsigned DEFAULT NULL,
  `dld_facility` int(10) unsigned DEFAULT NULL,
  `dld_provider` int(10) unsigned DEFAULT NULL,
  `dld_encounter` int(10) unsigned DEFAULT NULL,
  `dld_master_docid` int(10) unsigned NOT NULL,
  `dld_signed` smallint(5) unsigned NOT NULL COMMENT '0-Not Signed or Cannot Sign(Layout),1-Signed,2-Ready to sign,3-Denied(Pat Regi),10-Save(Layout)',
  `dld_signed_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dld_filepath` varchar(75) DEFAULT NULL,
  `dld_filename` varchar(45) NOT NULL,
  `dld_signing_person` varchar(50) NOT NULL,
  `dld_sign_level` int(11) NOT NULL COMMENT 'Sign flow level',
  `dld_content` varchar(50) NOT NULL COMMENT 'Layout sign position',
  `dld_file_for_pdf_generation` blob NOT NULL COMMENT 'The filled details in the fdf file is stored here.Patient Registration Screen',
  `dld_denial_reason` longtext NOT NULL,
  PRIMARY KEY (`dld_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
#EndIf

#IfNotTable documents_legal_master
CREATE TABLE `documents_legal_master` (
  `dlm_category` int(10) unsigned DEFAULT NULL,
  `dlm_subcategory` int(10) unsigned DEFAULT NULL,
  `dlm_document_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dlm_document_name` varchar(75) NOT NULL,
  `dlm_filepath` varchar(75) NOT NULL,
  `dlm_facility` int(10) unsigned DEFAULT NULL,
  `dlm_provider` int(10) unsigned DEFAULT NULL,
  `dlm_sign_height` double NOT NULL,
  `dlm_sign_width` double NOT NULL,
  `dlm_filename` varchar(45) NOT NULL,
  `dlm_effective_date` datetime NOT NULL,
  `dlm_version` int(10) unsigned NOT NULL,
  `content` varchar(255) NOT NULL,
  `dlm_savedsign` varchar(255) DEFAULT NULL COMMENT '0-Yes 1-No',
  `dlm_review` varchar(255) DEFAULT NULL COMMENT '0-Yes 1-No',
  PRIMARY KEY (`dlm_document_id`)
) ENGINE=MyISAM COMMENT='List of Master Docs to be signed' AUTO_INCREMENT=1 ;
#EndIf

#IfNotTable audit_master
CREATE TABLE `audit_master` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL COMMENT 'The Id of the user who approves or denies',
  `approval_status` tinyint(4) NOT NULL COMMENT '1-Pending,2-Approved,3-Denied,4-Appointment directly updated to calendar table,5-Cancelled appointment',
  `comments` text NOT NULL,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_time` datetime NOT NULL,
  `ip_address` varchar(100) NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '1-new patient,2-existing patient,3-change is only in the document,5-random key,10-Appointment',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1;
#EndIf

#IfNotTable audit_details
CREATE TABLE `audit_details` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `table_name` VARCHAR(100) NOT NULL COMMENT 'openemr table name',
  `field_name` VARCHAR(100) NOT NULL COMMENT 'openemr table''s field name',
  `field_value` TEXT NOT NULL COMMENT 'openemr table''s field value',
  `audit_master_id` BIGINT(20) NOT NULL COMMENT 'Id of the audit_master table',
  `entry_identification` VARCHAR(255) NOT NULL DEFAULT '1' COMMENT 'Used when multiple entry occurs from the same table.1 means no multiple entry',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1;
#EndIf

#IfNotTable documents_legal_categories
CREATE TABLE `documents_legal_categories` (
  `dlc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dlc_category_type` int(10) unsigned NOT NULL COMMENT '1 category 2 subcategory',
  `dlc_category_name` varchar(45) NOT NULL,
  `dlc_category_parent` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`dlc_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=7 ;
INSERT INTO `documents_legal_categories` (`dlc_id`, `dlc_category_type`, `dlc_category_name`, `dlc_category_parent`) VALUES
(3, 1, 'Category', NULL),
(4, 2, 'Sub Category', 1),
(5, 1, 'Layout Form', 0),
(6, 2, 'Layout Signed', 5);
#EndIf

#IfNotTable patient_access_offsite
CREATE TABLE  `patient_access_offsite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `portal_username` varchar(100) NOT NULL,
  `portal_pwd` varchar(100) NOT NULL,
  `portal_pwd_status` tinyint(4) DEFAULT '1' COMMENT '0=>Password Created Through Demographics by The provider or staff. Patient Should Change it at first time it.1=>Pwd updated or created by patient itself',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pid` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=1;
#EndIf

#IfTable globals
UPDATE globals SET gl_value = 'style_purple.css' WHERE gl_name = 'css_header' AND gl_value = 'style_tan.css';
#EndIf

#IfMissingColumn form_misc_billing_options date_initial_treatment
ALTER TABLE `form_misc_billing_options` 
  ADD date_initial_treatment date default NULL;
#EndIf

#IfMissingColumn prescriptions encounter
ALTER TABLE `prescriptions` ADD COLUMN `encounter` int(11) default NULL;
#EndIf

#IfMissingColumn lists erx_uploaded
ALTER TABLE `lists` ADD COLUMN `erx_uploaded` ENUM('0','1') DEFAULT '0' NOT NULL  COMMENT '0-Pending NewCrop upload 1-Uploaded TO NewCrop';
#EndIf

#IfMissingColumn ar_activity reason_code
ALTER TABLE ar_activity ADD COLUMN reason_code varchar(255) DEFAULT NULL COMMENT 'Use as needed to show the primary payer adjustment reason code';
#EndIf

#IfNotRow2D list_options list_id lists option_id msp_remit_codes
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('lists','msp_remit_codes','MSP Remit Codes','221','0','0','','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '1', '1', 1, 0, 0, '', 'Deductible Amount');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '2', '2', 2, 0, 0, '', 'Coinsurance Amount');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '3', '3', 3, 0, 0, '', 'Co-payment Amount');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '4', '4', 4, 0, 0, '', 'The procedure code is inconsistent with the modifier used or a required modifier is missing. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '9', '9', 9, 0, 0, '', 'The diagnosis is inconsistent with the patient''s age. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '10', '10', 10, 0, 0, '', 'The diagnosis is inconsistent with the patient''s gender. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '11', '11', 11, 0, 0, '', 'The diagnosis is inconsistent with the procedure. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '12', '12', 12, 0, 0, '', 'The diagnosis is inconsistent with the provider type. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '13', '13', 13, 0, 0, '', 'The date of death precedes the date of service.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '14', '14', 14, 0, 0, '', 'The date of birth follows the date of service.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '15', '15', 15, 0, 0, '', 'The authorization number is missing, invalid, or does not apply to the billed services or provider.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '16', '16', 16, 0, 0, '', 'Claim/service lacks information which is needed for adjudication. At least one Remark Code must be provided (may be comprised of either the NCPDP Reject Reason Code, or Remittance Advice Remark Code that is not an ALERT.)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '18', '18', 17, 0, 0, '', 'Duplicate claim/service.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '19', '19', 18, 0, 0, '', 'This is a work-related injury/illness and thus the liability of the Worker''s Compensation Carrier.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '20', '20', 19, 0, 0, '', 'This injury/illness is covered by the liability carrier.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '21', '21', 20, 0, 0, '', 'This injury/illness is the liability of the no-fault carrier.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '22', '22', 21, 0, 0, '', 'This care may be covered by another payer per coordination of benefits.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '23', '23', 22, 0, 0, '', 'The impact of prior payer(s) adjudication including payments and/or adjustments.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '24', '24', 23, 0, 0, '', 'Charges are covered under a capitation agreement/managed care plan.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '26', '26', 24, 0, 0, '', 'Expenses incurred prior to coverage.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '27', '27', 25, 0, 0, '', 'Expenses incurred after coverage terminated.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '29', '29', 26, 0, 0, '', 'The time limit for filing has expired.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '31', '31', 27, 0, 0, '', 'Patient cannot be identified as our insured.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '32', '32', 28, 0, 0, '', 'Our records indicate that this dependent is not an eligible dependent as defined.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '33', '33', 29, 0, 0, '', 'Insured has no dependent coverage.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '34', '34', 30, 0, 0, '', 'Insured has no coverage for newborns.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '35', '35', 31, 0, 0, '', 'Lifetime benefit maximum has been reached.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '38', '38', 32, 0, 0, '', 'Services not provided or authorized by designated (network/primary care) providers.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '39', '39', 33, 0, 0, '', 'Services denied at the time authorization/pre-certification was requested.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '40', '40', 34, 0, 0, '', 'Charges do not meet qualifications for emergent/urgent care. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '44', '44', 35, 0, 0, '', 'Prompt-pay discount.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '45', '45', 36, 0, 0, '', 'Charge exceeds fee schedule/maximum allowable or contracted/legislated fee arrangement. (Use Group Codes PR or CO depending upon liability).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '49', '49', 37, 0, 0, '', 'These are non-covered services because this is a routine exam or screening procedure done in conjunction with a routine exam. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '50', '50', 38, 0, 0, '', 'These are non-covered services because this is not deemed a ''medical necessity'' by the payer. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '51', '51', 39, 0, 0, '', 'These are non-covered services because this is a pre-existing condition. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '53', '53', 40, 0, 0, '', 'Services by an immediate relative or a member of the same household are not covered.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '54', '54', 41, 0, 0, '', 'Multiple physicians/assistants are not covered in this case. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '55', '55', 42, 0, 0, '', 'Procedure/treatment is deemed experimental/investigational by the payer. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '56', '56', 43, 0, 0, '', 'Procedure/treatment has not been deemed ''proven to be effective'' by the payer. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '58', '58', 44, 0, 0, '', 'Treatment was deemed by the payer to have been rendered in an inappropriate or invalid place of service. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '59', '59', 45, 0, 0, '', 'Processed based on multiple or concurrent procedure rules. (For example multiple surgery or diagnostic imaging, concurrent anesthesia.) Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '60', '60', 46, 0, 0, '', 'Charges for outpatient services are not covered when performed within a period of time prior to or after inpatient services.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '61', '61', 47, 0, 0, '', 'Penalty for failure to obtain second surgical opinion. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '66', '66', 48, 0, 0, '', 'Blood Deductible.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '69', '69', 49, 0, 0, '', 'Day outlier amount.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '70', '70', 50, 0, 0, '', 'Cost outlier - Adjustment to compensate for additional costs.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '74', '74', 51, 0, 0, '', 'Indirect Medical Education Adjustment.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '75', '75', 52, 0, 0, '', 'Direct Medical Education Adjustment.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '76', '76', 53, 0, 0, '', 'Disproportionate Share Adjustment.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '78', '78', 54, 0, 0, '', 'Non-Covered days/Room charge adjustment.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '85', '85', 55, 0, 0, '', 'Patient Interest Adjustment (Use Only Group code PR)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '87', '87', 56, 0, 0, '', 'Transfer amount.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '89', '89', 57, 0, 0, '', 'Professional fees removed from charges.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '90', '90', 58, 0, 0, '', 'Ingredient cost adjustment. Note: To be used for pharmaceuticals only.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '91', '91', 59, 0, 0, '', 'Dispensing fee adjustment.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '94', '94', 60, 0, 0, '', 'Processed in Excess of charges.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '95', '95', 61, 0, 0, '', 'Plan procedures not followed.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '96', '96', 62, 0, 0, '', 'Non-covered charge(s). At least one Remark Code must be provided (may be comprised of either the NCPDP Reject Reason Code, or Remittance Advice Remark Code that is not an ALERT.) Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 S');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '97', '97', 63, 0, 0, '', 'The benefit for this service is included in the payment/allowance for another service/procedure that has already been adjudicated. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '100', '100', 64, 0, 0, '', 'Payment made to patient/insured/responsible party/employer.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '101', '101', 65, 0, 0, '', 'Predetermination: anticipated payment upon completion of services or claim adjudication.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '102', '102', 66, 0, 0, '', 'Major Medical Adjustment.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '103', '103', 67, 0, 0, '', 'Provider promotional discount (e.g., Senior citizen discount).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '104', '104', 68, 0, 0, '', 'Managed care withholding.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '105', '105', 69, 0, 0, '', 'Tax withholding.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '106', '106', 70, 0, 0, '', 'Patient payment option/election not in effect.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '107', '107', 71, 0, 0, '', 'The related or qualifying claim/service was not identified on this claim. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '108', '108', 72, 0, 0, '', 'Rent/purchase guidelines were not met. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '109', '109', 73, 0, 0, '', 'Claim not covered by this payer/contractor. You must send the claim to the correct payer/contractor.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '110', '110', 74, 0, 0, '', 'Billing date predates service date.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '111', '111', 75, 0, 0, '', 'Not covered unless the provider accepts assignment.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '112', '112', 76, 0, 0, '', 'Service not furnished directly to the patient and/or not documented.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '114', '114', 77, 0, 0, '', 'Procedure/product not approved by the Food and Drug Administration.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '115', '115', 78, 0, 0, '', 'Procedure postponed, canceled, or delayed.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '116', '116', 79, 0, 0, '', 'The advance indemnification notice signed by the patient did not comply with requirements.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '117', '117', 80, 0, 0, '', 'Transportation is only covered to the closest facility that can provide the necessary care.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '118', '118', 81, 0, 0, '', 'ESRD network support adjustment.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '119', '119', 82, 0, 0, '', 'Benefit maximum for this time period or occurrence has been reached.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '121', '121', 83, 0, 0, '', 'Indemnification adjustment - compensation for outstanding member responsibility.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '122', '122', 84, 0, 0, '', 'Psychiatric reduction.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '125', '125', 85, 0, 0, '', 'Submission/billing error(s). At least one Remark Code must be provided (may be comprised of either the NCPDP Reject Reason Code, or Remittance Advice Remark Code that is not an ALERT.)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '128', '128', 86, 0, 0, '', 'Newborn''s services are covered in the mother''s Allowance.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '129', '129', 87, 0, 0, '', 'Prior processing information appears incorrect. At least one Remark Code must be provided (may be comprised of either the NCPDP Reject Reason Code, or Remittance Advice Remark Code that is not an ALERT.)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '130', '130', 88, 0, 0, '', 'Claim submission fee.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '131', '131', 89, 0, 0, '', 'Claim specific negotiated discount.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '132', '132', 90, 0, 0, '', 'Prearranged demonstration project adjustment.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '133', '133', 91, 0, 0, '', 'The disposition of this claim/service is pending further review.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '134', '134', 92, 0, 0, '', 'Technical fees removed from charges.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '135', '135', 93, 0, 0, '', 'Interim bills cannot be processed.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '136', '136', 94, 0, 0, '', 'Failure to follow prior payer''s coverage rules. (Use Group Code OA).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '137', '137', 95, 0, 0, '', 'Regulatory Surcharges, Assessments, Allowances or Health Related Taxes.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '138', '138', 96, 0, 0, '', 'Appeal procedures not followed or time limits not met.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '139', '139', 97, 0, 0, '', 'Contracted funding agreement - Subscriber is employed by the provider of services.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '140', '140', 98, 0, 0, '', 'Patient/Insured health identification number and name do not match.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '141', '141', 99, 0, 0, '', 'Claim spans eligible and ineligible periods of coverage.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '142', '142', 100, 0, 0, '', 'Monthly Medicaid patient liability amount.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '143', '143', 101, 0, 0, '', 'Portion of payment deferred.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '144', '144', 102, 0, 0, '', 'Incentive adjustment, e.g. preferred product/service.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '146', '146', 103, 0, 0, '', 'Diagnosis was invalid for the date(s) of service reported.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '147', '147', 104, 0, 0, '', 'Provider contracted/negotiated rate expired or not on file.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '148', '148', 105, 0, 0, '', 'Information from another provider was not provided or was insufficient/incomplete. At least one Remark Code must be provided (may be comprised of either the NCPDP Reject Reason Code, or Remittance Advice Remark Code that is not an ALERT.)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '149', '149', 106, 0, 0, '', 'Lifetime benefit maximum has been reached for this service/benefit category.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '150', '150', 107, 0, 0, '', 'Payer deems the information submitted does not support this level of service.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '151', '151', 108, 0, 0, '', 'Payment adjusted because the payer deems the information submitted does not support this many/frequency of services.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '152', '152', 109, 0, 0, '', 'Payer deems the information submitted does not support this length of service. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '153', '153', 110, 0, 0, '', 'Payer deems the information submitted does not support this dosage.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '154', '154', 111, 0, 0, '', 'Payer deems the information submitted does not support this day''s supply.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '155', '155', 112, 0, 0, '', 'Patient refused the service/procedure.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '157', '157', 113, 0, 0, '', 'Service/procedure was provided as a result of an act of war.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '158', '158', 114, 0, 0, '', 'Service/procedure was provided outside of the United States.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '159', '159', 115, 0, 0, '', 'Service/procedure was provided as a result of terrorism.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '160', '160', 116, 0, 0, '', 'Injury/illness was the result of an activity that is a benefit exclusion.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '161', '161', 117, 0, 0, '', 'Provider performance bonus');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '162', '162', 118, 0, 0, '', 'State-mandated Requirement for Property and Casualty, see Claim Payment Remarks Code for specific explanation.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '163', '163', 119, 0, 0, '', 'Attachment referenced on the claim was not received.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '164', '164', 120, 0, 0, '', 'Attachment referenced on the claim was not received in a timely fashion.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '165', '165', 121, 0, 0, '', 'Referral absent or exceeded.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '166', '166', 122, 0, 0, '', 'These services were submitted after this payers responsibility for processing claims under this plan ended.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '167', '167', 123, 0, 0, '', 'This (these) diagnosis(es) is (are) not covered. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '168', '168', 124, 0, 0, '', 'Service(s) have been considered under the patient''s medical plan. Benefits are not available under this dental plan.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '169', '169', 125, 0, 0, '', 'Alternate benefit has been provided.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '170', '170', 126, 0, 0, '', 'Payment is denied when performed/billed by this type of provider. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '171', '171', 127, 0, 0, '', 'Payment is denied when performed/billed by this type of provider in this type of facility. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '172', '172', 128, 0, 0, '', 'Payment is adjusted when performed/billed by a provider of this specialty. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '173', '173', 129, 0, 0, '', 'Service was not prescribed by a physician.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '174', '174', 130, 0, 0, '', 'Service was not prescribed prior to delivery.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '175', '175', 131, 0, 0, '', 'Prescription is incomplete.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '176', '176', 132, 0, 0, '', 'Prescription is not current.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '177', '177', 133, 0, 0, '', 'Patient has not met the required eligibility requirements.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '178', '178', 134, 0, 0, '', 'Patient has not met the required spend down requirements.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '179', '179', 135, 0, 0, '', 'Patient has not met the required waiting requirements. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '180', '180', 136, 0, 0, '', 'Patient has not met the required residency requirements.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '181', '181', 137, 0, 0, '', 'Procedure code was invalid on the date of service.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '182', '182', 138, 0, 0, '', 'Procedure modifier was invalid on the date of service.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '183', '183', 139, 0, 0, '', 'The referring provider is not eligible to refer the service billed. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '184', '184', 140, 0, 0, '', 'The prescribing/ordering provider is not eligible to prescribe/order the service billed. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '185', '185', 141, 0, 0, '', 'The rendering provider is not eligible to perform the service billed. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '186', '186', 142, 0, 0, '', 'Level of care change adjustment.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '187', '187', 143, 0, 0, '', 'Consumer Spending Account payments (includes but is not limited to Flexible Spending Account, Health Savings Account, Health Reimbursement Account, etc.)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '188', '188', 144, 0, 0, '', 'This product/procedure is only covered when used according to FDA recommendations.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '189', '189', 145, 0, 0, '', '''''Not otherwise classified'' or ''unlisted'' procedure code (CPT/HCPCS) was billed when there is a specific procedure code for this procedure/service''');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '190', '190', 146, 0, 0, '', 'Payment is included in the allowance for a Skilled Nursing Facility (SNF) qualified stay.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '191', '191', 147, 0, 0, '', 'Not a work related injury/illness and thus not the liability of the workers'' compensation carrier Note: If adjustment is at the Claim Level, the payer must send and the provider should refer to the 835 Insurance Policy Number Segment (Loop 2100 Other Clai');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '192', '192', 148, 0, 0, '', 'Non standard adjustment code from paper remittance. Note: This code is to be used by providers/payers providing Coordination of Benefits information to another payer in the 837 transaction only. This code is only used when the non-standard code cannot be ');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '193', '193', 149, 0, 0, '', 'Original payment decision is being maintained. Upon review, it was determined that this claim was processed properly.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '194', '194', 150, 0, 0, '', 'Anesthesia performed by the operating physician, the assistant surgeon or the attending physician.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '195', '195', 151, 0, 0, '', 'Refund issued to an erroneous priority payer for this claim/service.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '197', '197', 152, 0, 0, '', 'Precertification/authorization/notification absent.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '198', '198', 153, 0, 0, '', 'Precertification/authorization exceeded.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '199', '199', 154, 0, 0, '', 'Revenue code and Procedure code do not match.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '200', '200', 155, 0, 0, '', 'Expenses incurred during lapse in coverage');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '201', '201', 156, 0, 0, '', 'Workers Compensation case settled. Patient is responsible for amount of this claim/service through WC ''Medicare set aside arrangement'' or other agreement. (Use group code PR).');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '202', '202', 157, 0, 0, '', 'Non-covered personal comfort or convenience services.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '203', '203', 158, 0, 0, '', 'Discontinued or reduced service.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '204', '204', 159, 0, 0, '', 'This service/equipment/drug is not covered under the patient?s current benefit plan');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '205', '205', 160, 0, 0, '', 'Pharmacy discount card processing fee');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '206', '206', 161, 0, 0, '', 'National Provider Identifier - missing.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '207', '207', 162, 0, 0, '', 'National Provider identifier - Invalid format');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '208', '208', 163, 0, 0, '', 'National Provider Identifier - Not matched.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '209', '209', 164, 0, 0, '', 'Per regulatory or other agreement. The provider cannot collect this amount from the patient. However, this amount may be billed to subsequent payer. Refund to patient if collected. (Use Group code OA)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '210', '210', 165, 0, 0, '', 'Payment adjusted because pre-certification/authorization not received in a timely fashion');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '211', '211', 166, 0, 0, '', 'National Drug Codes (NDC) not eligible for rebate, are not covered.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '212', '212', 167, 0, 0, '', 'Administrative surcharges are not covered');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '213', '213', 168, 0, 0, '', 'Non-compliance with the physician self referral prohibition legislation or payer policy.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '214', '214', 169, 0, 0, '', 'Workers'' Compensation claim adjudicated as non-compensable. This Payer not liable for claim or service/treatment. Note: If adjustment is at the Claim Level, the payer must send and the provider should refer to the 835 Insurance Policy Number Segment (Loop');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '215', '215', 170, 0, 0, '', 'Based on subrogation of a third party settlement');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '216', '216', 171, 0, 0, '', 'Based on the findings of a review organization');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '217', '217', 172, 0, 0, '', 'Based on payer reasonable and customary fees. No maximum allowable defined by legislated fee arrangement. (Note: To be used for Workers'' Compensation only)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '218', '218', 173, 0, 0, '', 'Based on entitlement to benefits. Note: If adjustment is at the Claim Level, the payer must send and the provider should refer to the 835 Insurance Policy Number Segment (Loop 2100 Other Claim Related Information REF qualifier ''IG'') for the jurisdictional');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '219', '219', 174, 0, 0, '', 'Based on extent of injury. Note: If adjustment is at the Claim Level, the payer must send and the provider should refer to the 835 Insurance Policy Number Segment (Loop 2100 Other Claim Related Information REF qualifier ''IG'') for the jurisdictional regula');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '220', '220', 175, 0, 0, '', 'The applicable fee schedule does not contain the billed code. Please resubmit a bill with the appropriate fee schedule code(s) that best describe the service(s) provided and supporting documentation if required. (Note: To be used for Workers'' Compensation');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '221', '221', 176, 0, 0, '', 'Workers'' Compensation claim is under investigation. Note: If adjustment is at the Claim Level, the payer must send and the provider should refer to the 835 Insurance Policy Number Segment (Loop 2100 Other Claim Related Information REF qualifier ''IG'') for ');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '222', '222', 177, 0, 0, '', 'Exceeds the contracted maximum number of hours/days/units by this provider for this period. This is not patient specific. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '223', '223', 178, 0, 0, '', 'Adjustment code for mandated federal, state or local law/regulation that is not already covered by another code and is mandated before a new code can be created.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '224', '224', 179, 0, 0, '', 'Patient identification compromised by identity theft. Identity verification required for processing this and future claims.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '225', '225', 180, 0, 0, '', 'Penalty or Interest Payment by Payer (Only used for plan to plan encounter reporting within the 837)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '226', '226', 181, 0, 0, '', 'Information requested from the Billing/Rendering Provider was not provided or was insufficient/incomplete. At least one Remark Code must be provided (may be comprised of either the NCPDP Reject Reason Code, or Remittance Advice Remark Code that is not an ');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '227', '227', 182, 0, 0, '', 'Information requested from the patient/insured/responsible party was not provided or was insufficient/incomplete. At least one Remark Code must be provided (may be comprised of either the NCPDP Reject Reason Code, or Remittance Advice Remark Code that is ');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '228', '228', 183, 0, 0, '', 'Denied for failure of this provider, another provider or the subscriber to supply requested information to a previous payer for their adjudication');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '229', '229', 184, 0, 0, '', 'Partial charge amount not considered by Medicare due to the initial claim Type of Bill being 12X. Note: This code can only be used in the 837 transaction to convey Coordination of Benefits information when the secondary payer?s cost avoidance policy allow');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '230', '230', 185, 0, 0, '', 'No available or correlating CPT/HCPCS code to describe this service. Note: Used only by Property and Casualty.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '231', '231', 186, 0, 0, '', 'Mutually exclusive procedures cannot be done in the same day/setting. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '232', '232', 187, 0, 0, '', 'Institutional Transfer Amount. Note - Applies to institutional claims only and explains the DRG amount difference when the patient care crosses multiple institutions.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '233', '233', 188, 0, 0, '', 'Services/charges related to the treatment of a hospital-acquired condition or preventable medical error.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '234', '234', 189, 0, 0, '', 'This procedure is not paid separately. At least one Remark Code must be provided (may be comprised of either the NCPDP Reject Reason Code, or Remittance Advice Remark Code that is not an ALERT.)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '235', '235', 190, 0, 0, '', 'Sales Tax');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '236', '236', 191, 0, 0, '', 'This procedure or procedure/modifier combination is not compatible with another procedure or procedure/modifier combination provided on the same day according to the National Correct Coding Initiative.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', '237', '237', 192, 0, 0, '', 'Legislated/Regulatory Penalty. At least one Remark Code must be provided (may be comprised of either the NCPDP Reject Reason Code, or Remittance Advice Remark Code that is not an ALERT.)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'A0', 'A0', 193, 0, 0, '', 'Patient refund amount.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'A1', 'A1', 194, 0, 0, '', 'Claim/Service denied. At least one Remark Code must be provided (may be comprised of either the NCPDP Reject Reason Code, or Remittance Advice Remark Code that is not an ALERT.)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'A5', 'A5', 195, 0, 0, '', 'Medicare Claim PPS Capital Cost Outlier Amount.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'A6', 'A6', 196, 0, 0, '', 'Prior hospitalization or 30 day transfer requirement not met.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'A7', 'A7', 197, 0, 0, '', 'Presumptive Payment Adjustment');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'A8', 'A8', 198, 0, 0, '', 'Ungroupable DRG.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B1', 'B1', 199, 0, 0, '', 'Non-covered visits.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B10', 'B10', 200, 0, 0, '', 'Allowed amount has been reduced because a component of the basic procedure/test was paid. The beneficiary is not liable for more than the charge limit for the basic procedure/test.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B11', 'B11', 201, 0, 0, '', 'The claim/service has been transferred to the proper payer/processor for processing. Claim/service not covered by this payer/processor.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B12', 'B12', 202, 0, 0, '', 'Services not documented in patients'' medical records.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B13', 'B13', 203, 0, 0, '', 'Previously paid. Payment for this claim/service may have been provided in a previous payment.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B14', 'B14', 204, 0, 0, '', 'Only one visit or consultation per physician per day is covered.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B15', 'B15', 205, 0, 0, '', 'This service/procedure requires that a qualifying service/procedure be received and covered. The qualifying other service/procedure has not been received/adjudicated. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payme');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B16', 'B16', 206, 0, 0, '', '''''New Patient'' qualifications were not met.''');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B20', 'B20', 207, 0, 0, '', 'Procedure/service was partially or fully furnished by another provider.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B22', 'B22', 208, 0, 0, '', 'This payment is adjusted based on the diagnosis.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B23', 'B23', 209, 0, 0, '', 'Procedure billed is not authorized per your Clinical Laboratory Improvement Amendment (CLIA) proficiency test.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B4', 'B4', 210, 0, 0, '', 'Late filing penalty.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B5', 'B5', 211, 0, 0, '', 'Coverage/program guidelines were not met or were exceeded.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B7', 'B7', 212, 0, 0, '', 'This provider was not certified/eligible to be paid for this procedure/service on this date of service. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B8', 'B8', 213, 0, 0, '', 'Alternative services were available, and should have been utilized. Note: Refer to the 835 Healthcare Policy Identification Segment (loop 2110 Service Payment Information REF), if present.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'B9', 'B9', 214, 0, 0, '', 'Patient is enrolled in a Hospice.');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'D23', 'D23', 215, 0, 0, '', 'This dual eligible patient is covered by Medicare Part D per Medicare Retro-Eligibility. At least one Remark Code must be provided (may be comprised of either the NCPDP Reject Reason Code, or Remittance Advice Remark Code that is not an ALERT.)');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'W1', 'W1', 216, 0, 0, '', 'Workers'' compensation jurisdictional fee schedule adjustment. Note: If adjustment is at the Claim Level, the payer must send and the provider should refer to the 835 Class of Contract Code Identification Segment (Loop 2100 Other Claim Related Information ');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES ('msp_remit_codes', 'W2', 'W2', 217, 0, 0, '', 'Payment reduced or denied based on workers'' compensation jurisdictional regulations or payment policies, use only if no other code is applicable. Note: If adjustment is at the Claim Level, the payer must send and the provider should refer to the 835 Insur');
#EndIf

