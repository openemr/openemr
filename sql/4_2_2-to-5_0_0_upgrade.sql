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


-- To ensure proper compatibility with MySQL/MariaDB and InnoDB, changing all *TEXT fields to
-- correctly use NULL as default.
#IfTextNullFixNeeded
#EndIf

--
-- The following 4 tables were using AUTO_INCREMENT field in the end of primary key, which needed to be
-- modified to support InnoDB,
--

-- 1. ar_activity
--
#IfTableEngine ar_activity MyISAM
ALTER TABLE `ar_activity` MODIFY `sequence_no` int UNSIGNED NOT NULL COMMENT 'Sequence_no, incremented in code';
ALTER TABLE `ar_activity` ENGINE="InnoDB";
#EndIf

-- 2. claims
--
#IfTableEngine claims MyISAM
ALTER TABLE `claims` MODIFY `version` int(10) UNSIGNED NOT NULL COMMENT 'Version, incremented in code';
ALTER TABLE `claims` ENGINE="InnoDB";
#EndIf

-- 3. procedure_answers 
--
#IfTableEngine procedure_answers MyISAM
ALTER TABLE `procedure_answers` MODIFY `answer_seq` int(11) NOT NULL COMMENT 'Supports multiple-choice questions. Answer_seq, incremented in code';
ALTER TABLE `procedure_answers` ENGINE="InnoDB";
#EndIf

-- 4. procedure_order_code 
--
#IfTableEngine procedure_order_code MyISAM
-- Modify the table for InnoDB
-- remove NOT NULL DEFAULT "" declaration from TEXT field.
-- remove AUTO_INCREMENT field declaration
ALTER TABLE `procedure_order_code` MODIFY `procedure_order_seq` int(11) NOT NULL COMMENT 'Supports multiple tests per order. Procedure_order_seq incremented in code';
ALTER TABLE `procedure_order_code` ENGINE="InnoDB";
#EndIf

--
-- Other tables do not need special treatment before conversion to InnoDB.
-- Warning: running this query can take a long time.
#IfInnoDBMigrationNeeded
-- Modifies all remaining MyISAM tables to InnoDB 
#EndIf

#IfNotTable valueset
CREATE TABLE `valueset` (
  `nqf_code` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL DEFAULT '',
  `code_system` varchar(255) NOT NULL DEFAULT '',
  `code_type` varchar(255) DEFAULT NULL,
  `valueset` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  `valueset_name` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`nqf_code`,`code`,`valueset`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn openemr_postcalendar_categories pc_active
	ALTER TABLE `openemr_postcalendar_categories` ADD `pc_active` tinyint(1) NOT NULL DEFAULT 1;
#Endif

#IfMissingColumn openemr_postcalendar_categories pc_seq
	ALTER TABLE `openemr_postcalendar_categories` ADD `pc_seq` int(11) NOT NULL DEFAULT '0';
	UPDATE `openemr_postcalendar_categories` set pc_seq = pc_catid;
#EndIf

-- Mu2 New Encounter Categories
#IfNotRow openemr_postcalendar_categories pc_catname Health and Behavioral Assessment
	SET @catid = (SELECT MAX(pc_catid) FROM  openemr_postcalendar_categories);
	INSERT INTO `openemr_postcalendar_categories` (`pc_catid`, `pc_catname`, `pc_catcolor`, `pc_catdesc`, `pc_recurrtype`, `pc_enddate`, `pc_recurrspec`, `pc_recurrfreq`, `pc_duration`, `pc_end_date_flag`, `pc_end_date_type`, `pc_end_date_freq`, `pc_end_all_day`, `pc_dailylimit`, `pc_cattype`, `pc_active`, `pc_seq`) VALUES (@catid+1, 'Health and Behavioral Assessment', '#C7C7C7', 'Health and Behavioral Assessment', 0, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 900, 0, 0, 0, 0, 0,0,1,@catid+1);
	INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_hea_and_beh', @catid+1);
#EndIf

#IfNotRow openemr_postcalendar_categories pc_catname Preventive Care Services
	SET @catid = (SELECT MAX(pc_catid) FROM  openemr_postcalendar_categories);
	INSERT INTO `openemr_postcalendar_categories` (`pc_catid`, `pc_catname`, `pc_catcolor`, `pc_catdesc`, `pc_recurrtype`, `pc_enddate`, `pc_recurrspec`, `pc_recurrfreq`, `pc_duration`, `pc_end_date_flag`, `pc_end_date_type`, `pc_end_date_freq`, `pc_end_all_day`, `pc_dailylimit`, `pc_cattype`, `pc_active`, `pc_seq`) VALUES (@catid+1, 'Preventive Care Services', '#CCCCFF', 'Preventive Care Services', 0, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 900, 0, 0, 0, 0, 0,0,1,@catid+1);
	INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_ind_counsel', @catid+1);
	INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_group_counsel', @catid+1);
	INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_other_serv', @catid+1);
	INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_ser_18_older', @catid+1);
	INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_pre_med_ser_40_older', @catid+1);
#EndIf

#IfNotRow2D list_options list_id order_type option_id enc_checkup_procedure
	SET @max_seq = (select max(seq) from list_options where list_id = 'order_type');
	INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`,`is_default`) values ('order_type','enc_checkup_procedure','Encounter Checkup Procedure',@max_seq+10,0);
#EndIf

-- updating nqf code for cqm measure blood pressure
UPDATE `clinical_rules` set `cqm_nqf_code` = '0018' where `id` = 'rule_htn_bp_measure_cqm';

--
#IfNotTable calendar_external
CREATE TABLE calendar_external (
  `id` INT NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `description` VARCHAR(45) NOT NULL,
  `source` VARCHAR(45) NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB;
#EndIf

#IfNotRow openemr_postcalendar_categories pc_catid 6
INSERT INTO `openemr_postcalendar_categories` (`pc_catid`, `pc_catname`, `pc_catcolor`, `pc_catdesc`, `pc_recurrtype`, `pc_enddate`, `pc_recurrspec`, `pc_recurrfreq`, `pc_duration`, `pc_end_date_flag`, `pc_end_date_type`, `pc_end_date_freq`, `pc_end_all_day`, `pc_dailylimit`, `pc_cattype`, `pc_active`, `pc_seq`)
VALUES (6,'Holidays','#9676DB','Clinic holiday',0,NULL,'a:5:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"4";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}',0,86400,1,3,2,0,0,2,1,6);
#EndIf

#IfNotRow openemr_postcalendar_categories pc_catid 7
INSERT INTO `openemr_postcalendar_categories` (`pc_catid`, `pc_catname`, `pc_catcolor`, `pc_catdesc`, `pc_recurrtype`, `pc_enddate`, `pc_recurrspec`, `pc_recurrfreq`, `pc_duration`, `pc_end_date_flag`, `pc_end_date_type`, `pc_end_date_freq`, `pc_end_all_day`, `pc_dailylimit`, `pc_cattype`, `pc_active`, `pc_seq`)
VALUES (7,'Closed','#2374AB','Clinic closed',0,NULL,'a:5:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"4";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}',0,86400,1,3,2,0,0,2,1,7);
#EndIf



#IfMissingColumn immunizations information_source
ALTER TABLE `immunizations` ADD COLUMN `information_source` VARCHAR(31) DEFAULT NULL;
#EndIf

#IfMissingColumn immunizations refusal_reason
ALTER TABLE `immunizations` ADD COLUMN `refusal_reason` VARCHAR(31) DEFAULT NULL;
#EndIf

#IfMissingColumn immunizations ordering_provider
ALTER TABLE `immunizations` ADD COLUMN `ordering_provider` INT(11) DEFAULT NULL;
#EndIf

#IfNotRow2D list_options list_id lists option_id immunization_registry_status
INSERT INTO list_options (list_id, option_id, title) VALUES ('lists','immunization_registry_status','Immunization Registry Status');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_registry_status','active','Active','A', '10');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_registry_status','inactive_lost_to_follow_up','Inactive - Lost to follow - up','L', '20');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_registry_status','inactive_moved_gone_elsewhere','Inactive - Moved or gone elsewhere','M', '30');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_registry_status','inactive_permanently_inactive','Inactive - Permanently inactive','P', '40');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_registry_status','inactive_unspecified','Inactive - Unspecified','I', '50');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_registry_status','unknown','Unknown','U', '60');
#EndIf

#IfNotRow2D list_options list_id lists option_id publicity_code
INSERT INTO list_options (list_id, option_id, title) VALUES ('lists','publicity_code','Publicity Code');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','no_reminder_recall','No reminder/recall','SI', '10');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','reminder_recall_any_method','Reminder/recall - any method','02', '20');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','reminder_recall_no_calls','Reminder/recall - no calls','03', '30');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','reminder_only_any_method','Reminder only - any method','04', '40');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','reminder_only_no_calls','Reminder only - no calls','05', '50');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','recall_only_any_method','Recall only - any method','06', '60');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','recall_only_no_calls','Recall only - no calls','07', '70');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','reminder_recall_to_provider','Reminder/recall - to provider','08', '80');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','reminder_to_provider','Reminder to provider','09', '90');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','reminder_to_provider_no_recall','Only reminder to provider, no recall','10', '100');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','recall_to_provider','Recall to provider','11', '110');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('publicity_code','recall_to_provider_no_reminder','Only recall to provider, no reminder','12', '120');
#EndIf

#IfNotRow2D list_options list_id lists option_id immunization_refusal_reason
INSERT INTO list_options (list_id, option_id, title) VALUES ('lists','immunization_refusal_reason','Immunization Refusal Reason');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_refusal_reason','parental_decision','Parental decision','00', '10');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_refusal_reason','religious_exemption','Religious exemption','01', '20');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_refusal_reason','other','Other','02', '30');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_refusal_reason','patient_decision','Patient decision','03', '40');
#EndIf

#IfNotRow2D list_options list_id lists option_id immunization_informationsource
INSERT INTO list_options (list_id, option_id, title) VALUES ('lists','immunization_informationsource','Immunization Information Source');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_informationsource','new_immunization_record','New Immunization Record','00', '10');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_informationsource','hist_inf_src_unspecified','Historical information -source unspecified','01', '20');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_informationsource','other_provider','Other Provider','02', '30');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_informationsource','parent_written_record','Parent Written Record','03', '40');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_informationsource','parent_recall','Parent Recall','04', '50');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_informationsource','other_registry','Other Registry','05', '60');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_informationsource','birth_certificate','Birth Certificate','06', '70');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_informationsource','school_record','School Record','07', '80');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_informationsource','public_agency','Public Agency','08', '90');
#EndIf

#IfNotRow2D list_options list_id lists option_id next_of_kin_relationship
INSERT INTO `list_options` (list_id, option_id, title) VALUES ('lists','next_of_kin_relationship','Next of Kin Relationship');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','associate','Associate','10','ASC');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) values ('next_of_kin_relationship','brother','Brother','20','BRO');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','care_giver','Care giver','30','CGV');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','child','Child','40','CHD');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','handicapped_dependent','Handicapped dependent','50','DEP');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','life_partner','Life partner','60','DOM');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','emergency_contact','Emergency contact','70','EMC');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','employee','Employee','80','EME');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','employer','Employer','90','EMR');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','extended_family','Extended family','100','EXF');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','foster_child','Foster Child','110','FCH');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','friend','Friend','120','FND');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','father','Father','130','FTH');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','grandchild','Grandchild','140','GCH');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','guardian','Guardian','150','GRD');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','grandparent','Grandparent','160','GRP');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','manager','Manager','170','MGR');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','mother','Mother','180','MTH');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','natural_child','Natural child','190','NCH');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','none','None','200','NON');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','other_adult','Other adult','210','OAD');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','other','Other','220','OTH');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','owner','Owner','230','OWN');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','parent','Parent','240','PAR');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','stepchild','Stepchild','250','SCH');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','self','Self','260','SEL');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','sibling','Sibling','270','SIB');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','sister','Sister','280','SIS');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','spouse','Spouse','290','SPO');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','trainer','Trainer','300','TRA');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','unknown','Unknown','310','UNK');
INSERT INTO `list_options` (list_id, option_id, title, seq, notes) VALUES ('next_of_kin_relationship','ward_of_court','Ward of court','320','WRD');
#EndIf

#IfNotRow2D list_options list_id lists option_id immunization_administered_site
INSERT INTO list_options (list_id, option_id, title) VALUES ('lists','immunization_administered_site','Immunization Administered Site');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','left_thigh','Left Thigh','LT', '10');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','left_arm','Left Arm','LA', '20');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','left_deltoid','Left Deltoid','LD', '30');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','left_gluteus_medius','Left Gluteus Medius','LG', '40');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','left_vastus_lateralis','Left Vastus Lateralis','LVL', '50');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','left_lower_forearm','Left Lower Forearm','LLFA', '60');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','nose','Nose','Nose', '70');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','right_arm','Right Arm','RA', '80');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','right_thigh','Right Thigh','RT', '90');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','right_vastus_lateralis','Right Vastus Lateralis','RVL', '100');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','right_gluteus_medius','Right Gluteus Medius','RG', '110');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','right_deltoid','Right Deltoid','RD', '120');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('immunization_administered_site','right_lower_forearm','Right Lower Forearm','RLFA', '130');
#EndIf

#IfNotRow2D list_options list_id lists option_id immunization_observation
INSERT INTO `list_options`(`list_id`, `option_id`, `title`) VALUES ('lists','immunization_observation','Immunization Observation Criteria');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `notes`, `codes`) VALUES ('immunization_observation','funding_program_eligibility','Vaccine funding program eligibility category','10','LN','LOINC:64994-7');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `notes`, `codes`) VALUES ('immunization_observation','vaccine_type','Vaccine Type','20','LN','LOINC:30956-7');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `notes`, `codes`) VALUES ('immunization_observation','disease_with_presumed_immunity','Disease with presumed immunity','30','LN','LOINC:59784-9');
#EndIf

#IfNotRow2D list_options list_id lists option_id imm_vac_eligibility_results
INSERT INTO `list_options`(`list_id`, `option_id`, `title`) VALUES ('lists','imm_vac_eligibility_results','Immunization Vaccine Eligibility Results');
INSERT INTO `list_options`(list_id, option_id, title, seq, notes) VALUES ('imm_vac_eligibility_results','not_vfc_eligible','Not VFC eligible','10','V01');
INSERT INTO `list_options`(list_id, option_id, title, seq, notes) VALUES ('imm_vac_eligibility_results','medicaid_managed_care','VFC eligible-Medicaid/Medicaid Managed Care','20','V02');
INSERT INTO `list_options`(list_id, option_id, title, seq, notes) VALUES ('imm_vac_eligibility_results','uninsured','VFC eligible- Uninsured','30','V03');
INSERT INTO `list_options`(list_id, option_id, title, seq, notes) VALUES ('imm_vac_eligibility_results','american_indian_alaskan_native','VFC eligible- American Indian/Alaskan Native','40','V04');
INSERT INTO `list_options`(list_id, option_id, title, seq, notes) VALUES ('imm_vac_eligibility_results','health_center_patient','VFC eligible-Federally Qualified Health Center Patient (under-insured)','50','V05');
#EndIf

#IfNotTable immunization_observation
CREATE TABLE `immunization_observation` (
  `imo_id` int(11) NOT NULL AUTO_INCREMENT,
  `imo_im_id` int(11) NOT NULL,
  `imo_pid` int(11) DEFAULT NULL,
  `imo_criteria` varchar(255) DEFAULT NULL,
  `imo_criteria_value` varchar(255) DEFAULT NULL,
  `imo_user` int(11) DEFAULT NULL,
  `imo_code` varchar(255) DEFAULT NULL,
  `imo_codetext` varchar(255) DEFAULT NULL,
  `imo_codetype` varchar(255) DEFAULT NULL,
  `imo_vis_date_published` date DEFAULT NULL,
  `imo_vis_date_presented` date DEFAULT NULL,
  `imo_date_observation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`imo_id`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn patient_data imm_reg_status
SET @group_name = (SELECT group_name FROM layout_options WHERE form_id='DEM' AND group_name LIKE '%Choices' LIMIT 1);
SET @backup_group_name = (SELECT group_name FROM layout_options WHERE field_id='hipaa_notice' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_name = IFNULL(@group_name,@backup_group_name) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES ('DEM', 'imm_reg_status', IFNULL(@group_name,@backup_group_name), 'Immunization Registry Status', @seq+1, 1, 1, 1, 0, 'immunization_registry_status', 1, 1, '', '', 'Immunization Registry Status', 0) ;
ALTER TABLE `patient_data` ADD COLUMN `imm_reg_status` TEXT;
#EndIf

#IfMissingColumn patient_data imm_reg_stat_effdate
SET @group_name = (SELECT group_name FROM layout_options WHERE form_id='DEM' AND group_name LIKE '%Choices' LIMIT 1);
SET @backup_group_name = (SELECT group_name FROM layout_options WHERE field_id='hipaa_notice' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_name = IFNULL(@group_name,@backup_group_name) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES ('DEM', 'imm_reg_stat_effdate', IFNULL(@group_name,@backup_group_name), 'Immunization Registry Status Effective Date', @seq+1, 4, 1, 10, 10, '', 1, 1, '', '', 'Immunization Registry Status Effective Date', 0) ;
ALTER TABLE `patient_data` ADD COLUMN `imm_reg_stat_effdate` TEXT;
#EndIf

#IfMissingColumn patient_data publicity_code
SET @group_name = (SELECT group_name FROM layout_options WHERE form_id='DEM' AND group_name LIKE '%Choices' LIMIT 1);
SET @backup_group_name = (SELECT group_name FROM layout_options WHERE field_id='hipaa_notice' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_name = IFNULL(@group_name,@backup_group_name) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES ('DEM', 'publicity_code', IFNULL(@group_name,@backup_group_name), 'Publicity Code', @seq+1, 1, 1, 1, 0, 'publicity_code', 1, 1, '', '', 'Publicity Code', 0) ;
ALTER TABLE `patient_data` ADD COLUMN `publicity_code` TEXT;
#EndIf

#IfMissingColumn patient_data publ_code_eff_date
SET @group_name = (SELECT group_name FROM layout_options WHERE form_id='DEM' AND group_name LIKE '%Choices' LIMIT 1);
SET @backup_group_name = (SELECT group_name FROM layout_options WHERE field_id='hipaa_notice' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_name = IFNULL(@group_name,@backup_group_name) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES ('DEM', 'publ_code_eff_date', IFNULL(@group_name,@backup_group_name), 'Publicity Code Effective Date', @seq+1, 4, 1, 10, 10, '', 1, 1, '', '', 'Publicity Code Effective Date', 0) ;
ALTER TABLE `patient_data` ADD COLUMN `publ_code_eff_date` TEXT;
#EndIf

#IfMissingColumn patient_data protect_indicator
SET @group_name = (SELECT group_name FROM layout_options WHERE form_id='DEM' AND group_name LIKE '%Choices' LIMIT 1);
SET @backup_group_name = (SELECT group_name FROM layout_options WHERE field_id='hipaa_notice' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_name = IFNULL(@group_name,@backup_group_name) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES ('DEM', 'protect_indicator', IFNULL(@group_name,@backup_group_name), 'Protection Indicator', @seq+1, 1, 1, 1, 0, 'yesno', 1, 1, '', '', 'Protection Indicator', 0) ;
ALTER TABLE `patient_data` ADD COLUMN `protect_indicator` TEXT;
#EndIf

#IfMissingColumn patient_data prot_indi_effdate
SET @group_name = (SELECT group_name FROM layout_options WHERE form_id='DEM' AND group_name LIKE '%Choices' LIMIT 1);
SET @backup_group_name = (SELECT group_name FROM layout_options WHERE field_id='hipaa_notice' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_name = IFNULL(@group_name,@backup_group_name) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES ('DEM', 'prot_indi_effdate', IFNULL(@group_name,@backup_group_name), 'Protection Indicator Effective Date', @seq+1, 4, 1, 10, 10, '', 1, 1, '', '', 'Protection Indicator Effective Date', 0) ;
ALTER TABLE `patient_data` ADD COLUMN `prot_indi_effdate` TEXT;
#EndIf

#IfRow2D layout_options form_id DEM field_id guardiansname
UPDATE `layout_options` SET group_name='8Guardian',title='Name',seq='10' WHERE form_id='DEM' AND field_id='guardiansname' AND group_name LIKE '%Contact';
#EndIf

#IfNotColumnType patient_data guardiansname TEXT
ALTER TABLE `patient_data` MODIFY `guardiansname` TEXT;
#EndIf

#IfMissingColumn patient_data guardiansname
ALTER TABLE patient_data ADD COLUMN `guardiansname` TEXT;
#EndIf

#IfMissingColumn patient_data guardianrelationship
ALTER TABLE `patient_data` ADD COLUMN `guardianrelationship` TEXT;
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'guardianrelationship'  , '8Guardian', 'Relationship'  ,20, 1, 1,0,0, 'next_of_kin_relationship', 1, 1, '', '', 'Relationship', 0);
#EndIf

#IfMissingColumn patient_data guardiansex
ALTER TABLE `patient_data` ADD COLUMN `guardiansex` TEXT;
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'guardiansex'  , '8Guardian', 'Sex'  ,30, 1, 1,0,0, 'sex', 1, 1, '', '', 'Sex', 0);
#EndIf

#IfMissingColumn patient_data guardianaddress
ALTER TABLE `patient_data` ADD COLUMN `guardianaddress` TEXT;
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'guardianaddress'  , '8Guardian', 'Address'  ,40, 2, 1,25,63, '', 1, 1, '', '', 'Address', 0);
#EndIf

#IfMissingColumn patient_data guardiancity
ALTER TABLE `patient_data` ADD COLUMN `guardiancity` TEXT;
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'guardiancity'  , '8Guardian', 'City'  ,50, 2, 1,15,63, '', 1, 1, '', '', 'City', 0);
#EndIf

#IfMissingColumn patient_data guardianstate
ALTER TABLE `patient_data` ADD COLUMN `guardianstate` TEXT;
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'guardianstate'  , '8Guardian', 'State'  ,60, 26, 1,0,0, 'state', 1, 1, '', '', 'State', 0);
#EndIf

#IfMissingColumn patient_data guardianpostalcode
ALTER TABLE `patient_data` ADD COLUMN `guardianpostalcode` TEXT;
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'guardianpostalcode'  , '8Guardian', 'Postal Code'  ,70, 2, 1,6,63, '', 1, 1, '', '', 'Postal Code', 0);
#EndIf

#IfMissingColumn patient_data guardiancountry
ALTER TABLE `patient_data` ADD COLUMN `guardiancountry` TEXT;
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'guardiancountry'  , '8Guardian', 'Country'  ,80, 26, 1,0,0, 'country', 1, 1, '', '', 'Country', 0);
#EndIf

#IfMissingColumn patient_data guardianphone
ALTER TABLE `patient_data` ADD COLUMN `guardianphone` TEXT;
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'guardianphone'  , '8Guardian', 'Phone'  ,90, 2, 1,20,63, '', 1, 1, '', '', 'Phone', 0);
#EndIf

#IfMissingColumn patient_data guardianworkphone
ALTER TABLE `patient_data` ADD COLUMN `guardianworkphone` TEXT;
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'guardianworkphone'  , '8Guardian', 'Work Phone'  ,100, 2, 1,20,63, '', 1, 1, '', '', 'Work Phone', 0);
#EndIf

#IfMissingColumn patient_data guardianemail
ALTER TABLE `patient_data` ADD COLUMN `guardianemail` TEXT;
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'guardianemail'  , '8Guardian', 'Email'  ,110, 2, 1,20,63, '', 1, 1, '', '', 'Guardian Email Address', 0);
#EndIf

#IfNotRow2D list_options list_id drug_units title mL
SET @option_id = (SELECT MAX(CAST(option_id AS UNSIGNED)) FROM list_options WHERE list_id = 'drug_units');
SET @seq = (SELECT MAX(seq) FROM list_options WHERE list_id = 'drug_units');
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('drug_units', @option_id+1, 'mL', @seq+1, 0);
#EndIf

#IfNotRow2Dx2 list_options list_id drug_route option_id intramuscular title Intramuscular
INSERT INTO list_options ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `notes`, `codes` ) VALUES ('drug_route','intramuscular','Intramuscular' ,20, 0, 'IM', 'NCI-CONCEPT-ID:C28161');
#EndIf

#IfNotRow3D list_options list_id yesno option_id NO notes N
UPDATE `list_options` SET notes='N' WHERE list_id='yesno' and option_id='NO';
#EndIf

#IfNotRow3D list_options list_id yesno option_id YES notes Y
UPDATE `list_options` SET notes='Y' WHERE list_id='yesno' and option_id='YES';
#EndIf

#IfNotRow2Dx2 list_options list_id reaction option_id shortness_of_breath title Shortness of Breath
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `codes` ) VALUES ('reaction', 'shortness_of_breath', 'Shortness of Breath', 40, 'SNOMED-CT:267036007');
#EndIf

#IfNotRow2Dx2 list_options list_id drug_route option_id inhale title Inhale
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `notes`, `codes` ) VALUES ('drug_route','inhale','Inhale' , 16, 'RESPIR', 'NCI-CONCEPT-ID:C38216');
#EndIf

#IfNotRow3D list_options list_id drug_route codes NCI-CONCEPT-ID:C38288 title Per Oris
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C38288' WHERE list_id='drug_route' and title='Per Oris';
#EndIf

#IfNotColumnType immunizations cvx_code varchar(10)
ALTER TABLE `immunizations` MODIFY `cvx_code` varchar(10) default NULL;
#EndIf

#IfMissingColumn drugs drug_code
ALTER TABLE drugs ADD COLUMN drug_code VARCHAR(25) NULL;
#EndIf

#IfNotRow code_types ct_key NCI-CONCEPT-ID
DROP TABLE IF EXISTS `temp_table_one`;
CREATE TABLE `temp_table_one` (`id` int(11) NOT NULL DEFAULT '0',`seq` int(11) NOT NULL DEFAULT '0') ENGINE=MyISAM;
INSERT INTO `temp_table_one` (`id`, `seq`) VALUES (
  IF(((SELECT MAX(`ct_id` ) FROM `code_types`) >= 100), ((SELECT MAX(`ct_id` ) FROM `code_types`) + 1), 100),
  IF(((SELECT MAX(`ct_seq`) FROM `code_types`) >= 100), ((SELECT MAX(`ct_seq`) FROM `code_types`) + 1), 100));
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag, ct_active, ct_label, ct_external, ct_claim, ct_proc, ct_term, ct_problem ) VALUES ('NCI-CONCEPT-ID', (SELECT MAX(`id`) FROM `temp_table_one`), (SELECT MAX(`seq`) FROM `temp_table_one`), 0, '', 0, 0, 1, 0, 1, 'NCI CONCEPT ID', 0, 0, 0, 0, 0);
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('suspension','C60928',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('tablet','C42998',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('capsule','C25158',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('solution','C42986',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('tsp','C48544',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('ml','C28254',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('units','C44278',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('inhalations','C42944',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('gtts(drops)','C48491',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('cream','C28944',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('ointment','C42966',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('Per Oris','C38288',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('Inhale','C38216',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('Intramuscular','C28161',(SELECT MAX(`id` ) FROM `temp_table_one`));
INSERT INTO `codes` (`code_text`,`code`,`code_type`) VALUES ('mg','C28253',(SELECT MAX(`id` ) FROM `temp_table_one`));
DROP TABLE `temp_table_one`;
#EndIf

#IfNotRow3D list_options list_id drug_units title mg codes NCI-CONCEPT-ID:C28253
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C28253' WHERE list_id='drug_units' and title='mg';
#EndIf

#IfNotRow3D list_options list_id drug_form title suspension codes NCI-CONCEPT-ID:C60928
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C60928' WHERE list_id='drug_form' and title='suspension';
#EndIf

#IfNotRow3D list_options list_id drug_form title tablet codes NCI-CONCEPT-ID:C42998
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C42998' WHERE list_id='drug_form' and title='tablet';
#EndIf

#IfNotRow3D list_options list_id drug_form title capsule codes NCI-CONCEPT-ID:C25158
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C25158' WHERE list_id='drug_form' and title='capsule';
#EndIf

#IfNotRow3D list_options list_id drug_form title solution codes NCI-CONCEPT-ID:C42986
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C42986' WHERE list_id='drug_form' and title='solution';
#EndIf

#IfNotRow3D list_options list_id drug_form title tsp codes NCI-CONCEPT-ID:C48544
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C48544' WHERE list_id='drug_form' and title='tsp';
#EndIf

#IfNotRow3D list_options list_id drug_form title ml codes NCI-CONCEPT-ID:C28254
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C28254' WHERE list_id='drug_form' and title='ml';
#EndIf

#IfNotRow3D list_options list_id drug_form title units codes NCI-CONCEPT-ID:C44278
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C44278' WHERE list_id='drug_form' and title='units';
#EndIf

#IfNotRow3D list_options list_id drug_form title inhalations codes NCI-CONCEPT-ID:C42944
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C42944' WHERE list_id='drug_form' and title='inhalations';
#EndIf

#IfNotRow3D list_options list_id drug_form title gtts(drops) codes NCI-CONCEPT-ID:C48491
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C48491' WHERE list_id='drug_form' and title='gtts(drops)';
#EndIf

#IfNotRow3D list_options list_id drug_form title cream codes NCI-CONCEPT-ID:C28944
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C28944' WHERE list_id='drug_form' and title='cream';
#EndIf

#IfNotRow3D list_options list_id drug_form title ointment codes NCI-CONCEPT-ID:C42966
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C42966' WHERE list_id='drug_form' and title='ointment';
#EndIf

#IfNotRow2D list_options list_id drug_form title puff
SET @option_id = (SELECT MAX(CAST(option_id AS UNSIGNED)) FROM list_options WHERE list_id = 'drug_form');
SET @seq = (SELECT MAX(seq) FROM list_options WHERE list_id = 'drug_form');
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `codes` ) VALUES ('drug_form', @option_id+1, 'puff', @seq+1, 0, 'NCI-CONCEPT-ID:C42944');
#EndIf

#IfNotRow3D list_options list_id drug_route title Inhale codes NCI-CONCEPT-ID:C38216
UPDATE `list_options` SET codes='NCI-CONCEPT-ID:C38216' WHERE list_id='drug_route' and title='Inhale';
#EndIf

#IfNotRow openemr_postcalendar_categories pc_catname Ophthalmological Services
SET @catid = (SELECT MAX(pc_catid) FROM  openemr_postcalendar_categories);
INSERT INTO `openemr_postcalendar_categories` (`pc_catid`, `pc_catname`, `pc_catcolor`, `pc_catdesc`, `pc_recurrtype`, `pc_enddate`, `pc_recurrspec`, `pc_recurrfreq`, `pc_duration`, `pc_end_date_flag`, `pc_end_date_type`, `pc_end_date_freq`, `pc_end_all_day`, `pc_dailylimit`, `pc_cattype`, `pc_active`, `pc_seq`) VALUES (@catid+1, 'Ophthalmological Services', '#F89219', 'Ophthalmological Services', 0, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 900, 0, 0, 0, 0, 0,0,1,@catid+1);
INSERT INTO `enc_category_map` ( `rule_enc_id`, `main_cat_id` ) VALUES ('enc_ophthal_serv', @catid+1);
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_hea_and_beh' and main_cat_id = 5;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_hea_and_beh' and main_cat_id = 9;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_hea_and_beh' and main_cat_id = 10;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_ser_18_older' and main_cat_id = 5;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_ser_18_older' and main_cat_id = 9;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_ser_18_older' and main_cat_id = 10;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_ser_40_older' and main_cat_id = 5;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_ser_40_older' and main_cat_id = 9;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_ser_40_older' and main_cat_id = 10;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_ind_counsel' and main_cat_id = 5;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_ind_counsel' and main_cat_id = 9;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_ind_counsel' and main_cat_id = 10;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_group_counsel' and main_cat_id = 5;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_group_counsel' and main_cat_id = 9;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_group_counsel' and main_cat_id = 10;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_other_serv' and main_cat_id = 5;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_other_serv' and main_cat_id = 9;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pre_med_other_serv' and main_cat_id = 10;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pregnancy' and main_cat_id = 5;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pregnancy' and main_cat_id = 9;
DELETE FROM `enc_category_map` where rule_enc_id = 'enc_pregnancy' and main_cat_id = 10;
#EndIf

#IfMissingColumn documents thumb_url
ALTER TABLE  `documents` ADD  `thumb_url` VARCHAR( 255 ) DEFAULT NULL;
#EndIf

#IfMissingColumn layout_options validation
ALTER TABLE layout_options ADD COLUMN validation varchar(100) default NULL;
#EndIf

#IfNotRow2D list_options list_id lists option_id LBF_Validations
INSERT INTO `list_options` ( list_id, option_id, title) VALUES ( 'lists','LBF_Validations','LBF_Validations');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`, `seq`) VALUES ('LBF_Validations','int1','Integers1-100','{\"numericality\": {\"onlyInteger\": true,\"greaterThanOrEqualTo\": 1,\"lessThanOrEqualTo\":100}}','10');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`, `seq`) VALUES ('LBF_Validations','names','Names','{"format\":{\"pattern\":\"[a-zA-z]+([ \'-\\\\s][a-zA-Z]+)*\"}}','20');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`, `seq`) VALUES ('LBF_Validations','past_date','Past Date','{\"pastDate\":{\"message\":\"must be past date\"}}','30');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`,`seq`) VALUES ('LBF_Validations','past_year','Past Year','{\"date\":{\"dateOnly\":true},\"pastDate\":{\"onlyYear\":true}}','35');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`,`seq`) VALUES ('LBF_Validations','email','E-Mail','{\"email\":true}','40');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`,`seq`) VALUES ('LBF_Validations','url','URL','{\"url\":true}','50');
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`notes`,`seq`) VALUES ('LBF_Validations','luhn','Luhn','{"numericality": {"onlyInteger": true}, "luhn":true}','80');
#EndIf

#IfMissingColumn facility extra_validation
ALTER TABLE facility ADD extra_validation tinyint(1) NOT NULL DEFAULT '1';
#EndIf

#IfMissingColumn drugs consumable
ALTER TABLE drugs
  ADD consumable tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = will not show on the fee sheet';
#EndIf

#IfMissingColumn billing pricelevel
ALTER TABLE `billing` ADD COLUMN `pricelevel` varchar(31) default '';
# Fill in missing price levels where possible. Specific to IPPF but will not hurt anyone else.
UPDATE billing AS b, codes AS c, prices AS p
  SET b.pricelevel = p.pr_level WHERE
  b.code_type = 'MA' AND b.activity = 1 AND b.pricelevel = '' AND b.units = 1 AND b.fee > 0.00 AND
  c.code_type = '12' AND c.code = b.code AND c.modifier = b.modifier AND
  p.pr_id = c.id AND p.pr_selector = '' AND p.pr_price = b.fee;
#EndIf

#IfMissingColumn drug_sales pricelevel
ALTER TABLE `drug_sales` ADD COLUMN `pricelevel` varchar(31) default '';
#EndIf

#IfMissingColumn drug_sales selector
ALTER TABLE `drug_sales` ADD COLUMN `selector` varchar(255) default '' comment 'references drug_templates.selector';
# Fill in missing selector values where not ambiguous.
UPDATE drug_sales AS s, drug_templates AS t
  SET s.selector = t.selector WHERE
  s.pid != 0 AND s.selector = '' AND t.drug_id = s.drug_id AND
  (SELECT COUNT(*) FROM drug_templates AS t2 WHERE t2.drug_id = s.drug_id) = 1;
# Fill in missing price levels where not ambiguous.
UPDATE drug_sales AS s, drug_templates AS t, prices AS p
  SET s.pricelevel = p.pr_level WHERE
  s.pid != 0 AND s.selector != '' AND s.pricelevel = '' AND
  t.drug_id = s.drug_id AND t.selector = s.selector AND t.quantity = s.quantity AND
  p.pr_id = s.drug_id AND p.pr_selector = s.selector AND p.pr_price = s.fee;
#EndIf

#IfMissingColumn drug_sales bill_date
ALTER TABLE `drug_sales` ADD COLUMN `bill_date` datetime default NULL;
UPDATE drug_sales AS s, billing     AS b SET s.bill_date = b.bill_date WHERE s.billed = 1 AND s.bill_date IS NULL AND b.pid = s.pid AND b.encounter = s.encounter AND b.bill_date IS NOT NULL AND b.activity = 1;
UPDATE drug_sales AS s, ar_activity AS a SET s.bill_date = a.post_time WHERE s.billed = 1 AND s.bill_date IS NULL AND a.pid = s.pid AND a.encounter = s.encounter;
UPDATE drug_sales AS s SET s.bill_date = s.sale_date WHERE s.billed = 1 AND s.bill_date IS NULL;
#EndIf

#IfNotTable voids
CREATE TABLE `voids` (
  `void_id`                bigint(20)    NOT NULL AUTO_INCREMENT,
  `patient_id`             bigint(20)    NOT NULL            COMMENT 'references patient_data.pid',
  `encounter_id`           bigint(20)    NOT NULL DEFAULT 0  COMMENT 'references form_encounter.encounter',
  `what_voided`            varchar(31)   NOT NULL            COMMENT 'checkout,receipt and maybe other options later',
  `date_original`          datetime      DEFAULT NULL        COMMENT 'time of original action that is now voided',
  `date_voided`            datetime      NOT NULL            COMMENT 'time of void action',
  `user_id`                bigint(20)    NOT NULL            COMMENT 'references users.id',
  `amount1`                decimal(12,2) NOT NULL DEFAULT 0  COMMENT 'for checkout,receipt total voided adjustments',
  `amount2`                decimal(12,2) NOT NULL DEFAULT 0  COMMENT 'for checkout,receipt total voided payments',
  `other_info`             text                              COMMENT 'for checkout,receipt the old invoice refno',
  PRIMARY KEY (`void_id`),
  KEY datevoided (date_voided),
  KEY pidenc (patient_id, encounter_id)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn drugs dispensable
UPDATE drug_sales AS s, prescriptions AS p, form_encounter AS fe
  SET s.prescription_id = p.id WHERE
  s.pid > 0 AND
  s.encounter > 0 AND
  s.prescription_id = 0 AND
  fe.pid = s.pid AND
  fe.encounter = s.encounter AND
  p.patient_id = s.pid AND
  p.drug_id = s.drug_id AND
  p.start_date = fe.date;
ALTER TABLE drugs
  ADD dispensable tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 = pharmacy elsewhere, 1 = dispensed here';
#EndIf

#IfNotRow2D list_options list_id lists option_id page_validation
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('lists', 'page_validation', 'Page Validation', 298);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'add_edit_issue#theform', '/interface/patient_file/summary/add_edit_issue.php', 10, '{form_title:{presence: true}}', 0);
#EndIf

#IfMissingColumn procedure_order history_order
ALTER TABLE procedure_order ADD COLUMN history_order enum('0','1') DEFAULT '0';
#EndIf

#IfMissingColumn amc_misc_data soc_provided
       ALTER TABLE `amc_misc_data` add column `soc_provided` DATETIME default NULL;
#EndIf

#IfNotRow clinical_rules id cpoe_med_stage1_amc_alternative
	INSERT INTO `clinical_rules`(`id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `amc_2011_flag`, `amc_2014_flag`, `amc_code_2014`, `cqm_2011_flag`, `cqm_2014_flag`, `amc_2014_stage1_flag`, `amc_2014_stage2_flag`) VALUES ('cpoe_med_stage1_amc_alternative', 0, 0, 0, 0, '', '', 1, '170.304(a)', 0, 0, 1, '170.314(g)(1)/(2)–7', 0, 0, 1, 0);
	INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`) VALUES('clinical_rules', 'cpoe_med_stage1_amc_alternative', 'Use CPOE for medication orders.(Alternative)', 48, 0, 0, '', '', '', 0, 0);	
#EndIf

#IfNotRow2D clinical_rules id cpoe_med_stage2_amc amc_2014_stage1_flag 0
	UPDATE `clinical_rules` set amc_2014_stage1_flag = 0 where id='cpoe_med_stage2_amc';
#EndIf

#IfNotRow3D list_options list_id clinical_rules option_id cpoe_med_stage2_amc title Use CPOE for medication orders.
	UPDATE list_options set title = 'Use CPOE for medication orders.' where list_id = 'clinical_rules' and option_id = 'cpoe_med_stage2_amc';
#EndIf

#IfRow2D globals gl_name css_header gl_value style_tan_no_icons.css
UPDATE `globals` SET `gl_value` = 'style_tan.css' WHERE `gl_name` = 'css_header';
#EndIf

#IfColumn users ssi_relayhealth 
ALTER TABLE `users` DROP COLUMN `ssi_relayhealth`;
#EndIf

#IfNotRow2D list_options list_id page_validation option_id common#new_encounter
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'common#new_encounter', '/interface/forms/newpatient/common.php', 50, '{pc_catid:{exclusion: ["_blank"]}}', 1);
#EndIf

#IfColumn insurance_companies freeb_type
ALTER TABLE `insurance_companies` CHANGE `freeb_type` `ins_type_code` tinyint(2) Default NULL;
#EndIf

#IfTable integration_mapping
DROP TABLE IF EXISTS `integration_mapping`;
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2016-10-01 load_filename 2017-PCS-Long-Abbrev-Titles.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2016-10-01', '2017-PCS-Long-Abbrev-Titles.zip', '4669c47f6a9ca34bf4c14d7f93b37993');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2016-10-01 load_filename 2017-GEM-DC.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2016-10-01', '2017-GEM-DC.zip', '5a0affdc77a152e6971781233ee969c1');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2016-10-01 load_filename 2017-ICD10-Code-Descriptions.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2016-10-01', '2017-ICD10-Code-Descriptions.zip', 'ed9c159cb4ac4ae4f145062e15f83291');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2016-10-01 load_filename 2017-GEM-PCS.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2016-10-01', '2017-GEM-PCS.zip', 'a4e08b08fb9a53c81385867c82aa8a9e');
#EndIf

#IfMissingColumn form_encounter pos_code
ALTER TABLE `form_encounter` ADD `pos_code` tinyint(4) default NULL;
#EndIf

#IfNotColumnType drugs size varchar(25)
ALTER TABLE `drugs` CHANGE `size` `size` varchar(25) NOT NULL default '';
#EndIf

#IfNotColumnType prescriptions size varchar(25)
ALTER TABLE `prescriptions` CHANGE `size` `size` varchar(25) default NULL;
#EndIf

#IfNotColumnType user_settings setting_label varchar(100)
ALTER TABLE `user_settings` CHANGE `setting_label` `setting_label` varchar(100) NOT NULL;
#EndIf

