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

#IfNotColumnType list_options notes TEXT
ALTER TABLE `list_options` MODIFY `notes` TEXT;
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

#IfNotRow2D list_options list_id page_validation option_id add_edit_event#theform
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'add_edit_event#theform', '/interface/main/calendar/add_edit_event.php', 60, '{form_patient:{presence: {message: "Patient Name Required"}}}', 1);
#EndIf

#IfNotRow2D list_options list_id page_validation option_id usergroup_admin_add#new_user
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'usergroup_admin_add#new_user', '/interface/usergroup/usergroup_admin_add.php', 70, '{rumple:{presence: {message:"Required field missing: Please enter the User Name"}}, stiltskin:{presence: {message:"Please enter the password"}}, fname:{presence: {message:"Required field missing: Please enter the First name"}}, lname:{presence: {message:"Required field missing: Please enter the Last name"}}}', 1);
#EndIf

#IfNotRow2D list_options list_id page_validation option_id user_admin#user_form
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'user_admin#user_form', '/interface/usergroup/user_admin.php', 80, '{fname:{presence: {message:"Required field missing: Please enter the First name"}}, lname:{presence: {message:"Required field missing: Please enter the Last name"}}}', 1);
#EndIf

#IfNotRow2D list_options list_id page_validation option_id facility_admin#facility-form
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'facility_admin#facility-form', '/interface/usergroup/facility_admin.php', 90, '{facility:{presence: true}, ncolor:{presence: true}}', 1);
#EndIf

#IfNotRow2D list_options list_id page_validation option_id facilities_add#facility-add
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'facilities_add#facility-add', '/interface/usergroup/facilities_add.php', 100, '{facility:{presence: true}, ncolor:{presence: true}}', 1);
#EndIf

#IfNotTable form_eye_mag_dispense
CREATE TABLE `form_eye_mag_dispense` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`encounter` bigint(20) NULL,
`pid` bigint(20) DEFAULT NULL,
`user` varchar(255) DEFAULT NULL,
`groupname` varchar(255) DEFAULT NULL,
`authorized` tinyint(4) DEFAULT NULL,
`activity` tinyint(4) DEFAULT NULL,
`REFDATE` DATETIME NULL DEFAULT NULL,
`REFTYPE` varchar(10) DEFAULT NULL,
`RXTYPE` varchar(20)DEFAULT NULL,
`ODSPH` varchar(10) DEFAULT NULL,
`ODCYL` varchar(10) DEFAULT NULL,
`ODAXIS` varchar(10) DEFAULT NULL,
`OSSPH` varchar(10) DEFAULT NULL,
`OSCYL` varchar(10) DEFAULT NULL,
`OSAXIS` varchar(10) DEFAULT NULL,
`ODMIDADD` varchar(10) DEFAULT NULL,
`OSMIDADD` varchar(10) DEFAULT NULL,
`ODADD` varchar(10) DEFAULT NULL,
`OSADD` varchar(10) DEFAULT NULL,
`ODHPD` varchar(20) DEFAULT NULL,
`ODHBASE` varchar(20) DEFAULT NULL,
`ODVPD` varchar(20) DEFAULT NULL,
`ODVBASE` varchar(20) DEFAULT NULL,
`ODSLABOFF` varchar(20) DEFAULT NULL,
`ODVERTEXDIST` varchar(20) DEFAULT NULL,
`OSHPD` varchar(20) DEFAULT NULL,
`OSHBASE` varchar(20) DEFAULT NULL,
`OSVPD` varchar(20) DEFAULT NULL,
`OSVBASE` varchar(20) DEFAULT NULL,
`OSSLABOFF` varchar(20) DEFAULT NULL,
`OSVERTEXDIST` varchar(20) DEFAULT NULL,
`ODMPDD` varchar(20) DEFAULT NULL,
`ODMPDN` varchar(20) DEFAULT NULL,
`OSMPDD` varchar(20) DEFAULT NULL,
`OSMPDN` varchar(20) DEFAULT NULL,
`BPDD` varchar(20) DEFAULT NULL,
`BPDN` varchar(20) DEFAULT NULL,
`LENS_MATERIAL` varchar(20) DEFAULT NULL,
`LENS_TREATMENTS` varchar(100) DEFAULT NULL,
`CTLMANUFACTUREROD` varchar(25) DEFAULT NULL,
`CTLMANUFACTUREROS` varchar(25) DEFAULT NULL,
`CTLSUPPLIEROD` varchar(25) DEFAULT NULL,
`CTLSUPPLIEROS` varchar(25) DEFAULT NULL,
`CTLBRANDOD` varchar(50) DEFAULT NULL,
`CTLBRANDOS` varchar(50) DEFAULT NULL,
`ODDIAM` varchar(50) DEFAULT NULL,
`ODBC` varchar(50) DEFAULT NULL,
`OSDIAM` varchar(50) DEFAULT NULL,
`OSBC` varchar(50) DEFAULT NULL,
`RXCOMMENTS` text,
`COMMENTS` text,
PRIMARY KEY (`id`),
UNIQUE KEY `pid` (`pid`,`encounter`,`id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable form_eye_mag
CREATE TABLE `form_eye_mag` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`date` datetime DEFAULT NULL,
`pid` bigint(20) DEFAULT NULL,
`user` varchar(255) DEFAULT NULL,
`groupname` varchar(255) DEFAULT NULL,
`authorized` tinyint(4) DEFAULT NULL,
`activity` tinyint(4) DEFAULT NULL,
`Narrative` text,
`VISITTYPE` varchar(50) DEFAULT NULL,
`CC1` text,
`HPI1` text,
`QUALITY1` text,
`TIMING1` text,
`DURATION1` text,
`CONTEXT1` text,
`SEVERITY1` text,
`MODIFY1` text,
`ASSOCIATED1` text,
`LOCATION1` text,
`CHRONIC1`  text,
`CHRONIC2`text,
`CHRONIC3`text,
`CC2` text,
`HPI2` text,
`QUALITY2` text,
`TIMING2` text,
`DURATION2` text,
`CONTEXT2` text,
`SEVERITY2` text,
`MODIFY2` text,
`ASSOCIATED2` text,
`LOCATION2` text,
`CC3` text,
`HPI3` text,
`QUALITY3` text,
`TIMING3` text,
`DURATION3` text,
`CONTEXT3` text,
`SEVERITY3` text,
`MODIFY3` text,
`ASSOCIATED3` text,
`LOCATION3` text,
`ROSGENERAL` text,
`ROSHEENT` text,
`ROSCV` text,
`ROSPULM` text,
`ROSGI` text,
`ROSGU` text,
`ROSDERM` text,
`ROSNEURO` text,
`ROSPSYCH` text,
`ROSMUSCULO` text,
`ROSIMMUNO` text,
`ROSENDOCRINE` text,
`alert` char(3) DEFAULT 'yes',
`oriented` char(3) DEFAULT 'TPP',
`confused` char(3) DEFAULT 'nml',
`SCODVA` varchar(20) DEFAULT NULL,
`SCOSVA` varchar(20) DEFAULT NULL,
`PHODVA` varchar(20) DEFAULT NULL,
`PHOSVA` varchar(20) DEFAULT NULL,
`WODVA` varchar(20) DEFAULT NULL,
`WOSVA` varchar(20) DEFAULT NULL,
`CTLODVA` varchar(20) DEFAULT NULL,
`CTLOSVA` varchar(20) DEFAULT NULL,
`MRODVA` varchar(20) DEFAULT NULL,
`MROSVA` varchar(20) DEFAULT NULL,
`SCNEARODVA` varchar(20) DEFAULT NULL,
`SCNEAROSVA` varchar(20) DEFAULT NULL,
`WNEARODVA` varchar(10) DEFAULT NULL,
`WNEAROSVA` varchar(10) DEFAULT NULL,
`MRNEARODVA` varchar(20) DEFAULT NULL,
`MRNEAROSVA` varchar(20) DEFAULT NULL,
`GLAREODVA` varchar(20) DEFAULT NULL,
`GLAREOSVA` varchar(20) DEFAULT NULL,
`GLARECOMMENTS` varchar(100) DEFAULT NULL,
`ARODVA` varchar(20) DEFAULT NULL,
`AROSVA` varchar(20) DEFAULT NULL,
`CRODVA` varchar(20) DEFAULT NULL,
`CROSVA` varchar(20) DEFAULT NULL,
`CTLODVA1` varchar(20) DEFAULT NULL,
`CTLOSVA1` varchar(20) DEFAULT NULL,
`PAMODVA` varchar(20) DEFAULT NULL,
`PAMOSVA` varchar(20) DEFAULT NULL,
`LIODVA` varchar(20) DEFAULT NULL,
`LIOSVA` varchar(20) DEFAULT NULL,
`NVOCHECKED` varchar(20) DEFAULT NULL,
`ADDCHECKED` varchar(20) DEFAULT NULL,
`MRODSPH` varchar(20) DEFAULT NULL,
`MRODCYL` varchar(20) DEFAULT NULL,
`MRODAXIS` varchar(20) DEFAULT NULL,
`MRODPRISM` varchar(20) DEFAULT NULL,
`MRODBASE` varchar(20) DEFAULT NULL,
`MRODADD` varchar(20) DEFAULT NULL,
`MROSSPH` varchar(20) DEFAULT NULL,
`MROSCYL` varchar(20) DEFAULT NULL,
`MROSAXIS` varchar(20) DEFAULT NULL,
`MROSPRISM` varchar(20) DEFAULT NULL,
`MROSBASE` varchar(20) DEFAULT NULL,
`MROSADD` varchar(20) DEFAULT NULL,
`MRODNEARSPHERE` varchar(20) DEFAULT NULL,
`MRODNEARCYL` varchar(20) DEFAULT NULL,
`MRODNEARAXIS` varchar(20) DEFAULT NULL,
`MRODPRISMNEAR` varchar(20) DEFAULT NULL,
`MRODBASENEAR` varchar(20) DEFAULT NULL,
`MROSNEARSHPERE` varchar(20) DEFAULT NULL,
`MROSNEARCYL` varchar(20) DEFAULT NULL,
`MROSNEARAXIS` varchar(20) DEFAULT NULL,
`MROSPRISMNEAR` varchar(20) DEFAULT NULL,
`MROSBASENEAR` varchar(20) DEFAULT NULL,
`CRODSPH` varchar(20) DEFAULT NULL,
`CRODCYL` varchar(20) DEFAULT NULL,
`CRODAXIS` varchar(20) DEFAULT NULL,
`CROSSPH` varchar(20) DEFAULT NULL,
`CROSCYL` varchar(20) DEFAULT NULL,
`CROSAXIS` varchar(20) DEFAULT NULL,
`CRCOMMENTS` varchar(255) DEFAULT NULL,
`BALANCED` varchar(2) DEFAULT NULL,
`DIL_RISKS` varchar(2) DEFAULT 'on',
`WETTYPE` VARCHAR(10) DEFAULT NULL,
`ATROPINE` VARCHAR(25) DEFAULT NULL,
`CYCLOMYDRIL` VARCHAR(25) DEFAULT NULL,
`TROPICAMIDE` VARCHAR(25) DEFAULT NULL,
`CYCLOGYL` VARCHAR(25) DEFAULT NULL,
`NEO25` VARCHAR(25) DEFAULT NULL,
`ARODSPH` varchar(10) DEFAULT NULL,
`ARODCYL` varchar(10) DEFAULT NULL,
`ARODAXIS` varchar(10) DEFAULT NULL,
`AROSSPH` varchar(10) DEFAULT NULL,
`AROSCYL` varchar(10) DEFAULT NULL,
`AROSAXIS` varchar(10) DEFAULT NULL,
`ARODADD` varchar(10) DEFAULT NULL,
`AROSADD` varchar(10) DEFAULT NULL,
`ARNEARODVA` varchar(10) DEFAULT NULL,
`ARNEAROSVA` varchar(10) DEFAULT NULL,
`ARODPRISM` varchar(20) DEFAULT NULL,
`AROSPRISM` varchar(20) DEFAULT NULL,
`CTLODSPH` varchar(50) DEFAULT NULL,
`CTLODCYL` varchar(50) DEFAULT NULL,
`CTLODAXIS` varchar(50) DEFAULT NULL,
`CTLODBC` varchar(50) DEFAULT NULL,
`CTLODDIAM` varchar(50) DEFAULT NULL,
`CTLOSSPH` varchar(50) DEFAULT NULL,
`CTLOSCYL` varchar(50) DEFAULT NULL,
`CTLOSAXIS` varchar(50) DEFAULT NULL,
`CTLOSBC` varchar(50) DEFAULT NULL,
`CTLOSDIAM` varchar(50) DEFAULT NULL,
`CTL_COMMENTS` text,
`CTLMANUFACTUREROD` varchar(50) DEFAULT NULL,
`CTLSUPPLIEROD` varchar(50) DEFAULT NULL,
`CTLBRANDOD` varchar(50) DEFAULT NULL,
`CTLMANUFACTUREROS` varchar(50) DEFAULT NULL,
`CTLSUPPLIEROS` varchar(50) DEFAULT NULL,
`CTLBRANDOS` varchar(50) DEFAULT NULL,
`CTLODADD` varchar(50) DEFAULT NULL,
`CTLOSADD` varchar(50) DEFAULT NULL,
`ODIOPAP` varchar(50) DEFAULT NULL,
`OSIOPAP` varchar(50) DEFAULT NULL,
`ODIOPTPN` varchar(10) DEFAULT NULL,
`OSIOPTPN` varchar(10) DEFAULT NULL,
`ODIOPFTN` varchar(10) DEFAULT NULL,
`OSIOPFTN` varchar(10) DEFAULT NULL,
`ODIOPPOST`varchar(10) DEFAULT NULL,
`OSIOPPOST` varchar(10) DEFAULT NULL,
`ODIOPTARGET`varchar(10) DEFAULT NULL,
`OSIOPTARGET` varchar(10) DEFAULT NULL,
`IOPTIME` time DEFAULT NULL,
`IOPPOSTTIME` time DEFAULT NULL,
`AMSLEROD` smallint(1) DEFAULT NULL,
`AMSLEROS` smallint(1) DEFAULT NULL,
`ODK1` varchar(50) DEFAULT NULL,
`ODK2` varchar(50) DEFAULT NULL,
`ODK2AXIS` varchar(50) DEFAULT NULL,
`OSK1` varchar(50) DEFAULT NULL,
`OSK2` varchar(50) DEFAULT NULL,
`OSK2AXIS` varchar(50) DEFAULT NULL,
`ODAXIALLENGTH` varchar(50) DEFAULT NULL,
`OSAXIALLENGTH` varchar(50) DEFAULT NULL,
`ODACD` varchar(50) DEFAULT NULL,
`OSACD` varchar(50) DEFAULT NULL,
`ODW2W` varchar(10) DEFAULT NULL,
`OSW2W` varchar(10) DEFAULT NULL,
`ODLT` varchar(20) DEFAULT NULL,
`OSLT` varchar(20) DEFAULT NULL,
`ODPDMeasured` varchar(25) DEFAULT NULL,
`OSPDMeasured` varchar(25) DEFAULT NULL,
`ACT` char(3) DEFAULT 'on',
`ACT1CCDIST` varchar(50) DEFAULT NULL,
`ACT2CCDIST` varchar(50) DEFAULT NULL,
`ACT3CCDIST` varchar(50) DEFAULT NULL,
`ACT4CCDIST` varchar(50) DEFAULT NULL,
`ACT5CCDIST` varchar(50) DEFAULT NULL,
`ACT6CCDIST` varchar(50) DEFAULT NULL,
`ACT7CCDIST` varchar(50) DEFAULT NULL,
`ACT8CCDIST` varchar(50) DEFAULT NULL,
`ACT9CCDIST` varchar(50) DEFAULT NULL,
`ACT10CCDIST` varchar(50) DEFAULT NULL,
`ACT11CCDIST` varchar(50) DEFAULT NULL,
`ACT1SCDIST` varchar(50) DEFAULT NULL,
`ACT2SCDIST` varchar(50) DEFAULT NULL,
`ACT3SCDIST` varchar(50) DEFAULT NULL,
`ACT4SCDIST` varchar(50) DEFAULT NULL,
`ACT5SCDIST` varchar(50) DEFAULT NULL,
`ACT6SCDIST` varchar(50) DEFAULT NULL,
`ACT7SCDIST` varchar(50) DEFAULT NULL,
`ACT8SCDIST` varchar(50) DEFAULT NULL,
`ACT9SCDIST` varchar(50) DEFAULT NULL,
`ACT10SCDIST` varchar(50) DEFAULT NULL,
`ACT11SCDIST` varchar(50) DEFAULT NULL,
`ACT1SCNEAR` varchar(50) DEFAULT NULL,
`ACT2SCNEAR` varchar(50) DEFAULT NULL,
`ACT3SCNEAR` varchar(50) DEFAULT NULL,
`ACT4SCNEAR` varchar(50) DEFAULT NULL,
`ACT5SCNEAR` varchar(50) DEFAULT NULL,
`ACT6SCNEAR` varchar(50) DEFAULT NULL,
`ACT7SCNEAR` varchar(50) DEFAULT NULL,
`ACT8SCNEAR` varchar(50) DEFAULT NULL,
`ACT9SCNEAR` varchar(50) DEFAULT NULL,
`ACT10SCNEAR` varchar(50) DEFAULT NULL,
`ACT11SCNEAR` varchar(50) DEFAULT NULL,
`ACT1CCNEAR` varchar(50) DEFAULT NULL,
`ACT2CCNEAR` varchar(50) DEFAULT NULL,
`ACT3CCNEAR` varchar(50) DEFAULT NULL,
`ACT4CCNEAR` varchar(50) DEFAULT NULL,
`ACT5CCNEAR` varchar(50) DEFAULT NULL,
`ACT6CCNEAR` varchar(50) DEFAULT NULL,
`ACT7CCNEAR` varchar(50) DEFAULT NULL,
`ACT8CCNEAR` varchar(50) DEFAULT NULL,
`ACT9CCNEAR` varchar(50) DEFAULT NULL,
`ACT10CCNEAR` varchar(50) DEFAULT NULL,
`ACT11CCNEAR` varchar(50) DEFAULT NULL,
`ODVF1` tinyint(1) DEFAULT NULL,
`ODVF2` tinyint(1) DEFAULT NULL,
`ODVF3` tinyint(1) DEFAULT NULL,
`ODVF4` tinyint(1) DEFAULT NULL,
`OSVF1` tinyint(1) DEFAULT NULL,
`OSVF2` tinyint(1) DEFAULT NULL,
`OSVF3` tinyint(1) DEFAULT NULL,
`OSVF4` tinyint(1) DEFAULT NULL,
`MOTILITYNORMAL` char(3) DEFAULT 'on',
`MOTILITY_RS` int(1) DEFAULT NULL,
`MOTILITY_RI` int(1) DEFAULT NULL,
`MOTILITY_RR` int(1) DEFAULT NULL,
`MOTILITY_RL` int(1) DEFAULT NULL,
`MOTILITY_LS` int(1) DEFAULT NULL,
`MOTILITY_LI` int(1) DEFAULT NULL,
`MOTILITY_LR` int(1) DEFAULT NULL,
`MOTILITY_LL` int(1) DEFAULT NULL,
`MOTILITY_RRSO` int(1) DEFAULT NULL,
`MOTILITY_RLSO` int(1) DEFAULT NULL,
`MOTILITY_RRIO` int(1) DEFAULT NULL,
`MOTILITY_RLIO` int(1) DEFAULT NULL,
`MOTILITY_LRSO` int(1) DEFAULT NULL,
`MOTILITY_LLSO` int(1) DEFAULT NULL,
`MOTILITY_LRIO` int(1) DEFAULT NULL,
`MOTILITY_LLIO` int(1) DEFAULT NULL,
`STEREOPSIS` varchar(25) DEFAULT NULL,
`ODNPA` varchar(50) DEFAULT NULL,
`OSNPA` varchar(50) DEFAULT NULL,
`VERTFUSAMPS` varchar(50) DEFAULT NULL,
`DIVERGENCEAMPS` varchar(50) DEFAULT NULL,
`NPC` varchar(10) DEFAULT NULL,
`DACCDIST` varchar(10) DEFAULT NULL,
`DACCNEAR` varchar(10) DEFAULT NULL,
`CACCDIST` varchar(10) DEFAULT NULL,
`CACCNEAR` varchar(10) DEFAULT NULL,
`ODCOLOR` varchar(5) DEFAULT NULL,
`OSCOLOR` varchar(5) DEFAULT NULL,
`ODCOINS` varchar(5) DEFAULT NULL,
`OSCOINS` varchar(5) DEFAULT NULL,
`ODREDDESAT` varchar(10) DEFAULT NULL,
`OSREDDESAT` varchar(10) DEFAULT NULL,
`NEURO_COMMENTS` text,
`RUL` text,
`LUL` text,
`RLL` text,
`LLL` text,
`RBROW` text,
`LBROW` text,
`RMCT` text,
`LMCT` text,
`RADNEXA` varchar(255) DEFAULT NULL,
`LADNEXA` varchar(255) DEFAULT NULL,
`RMRD` varchar(25) DEFAULT NULL,
`LMRD` varchar(25) DEFAULT NULL,
`RLF` varchar(50) DEFAULT NULL,
`LLF` varchar(50) DEFAULT NULL,
`RVFISSURE` varchar(10) DEFAULT NULL,
`LVFISSURE` varchar(10) DEFAULT NULL,
`ODHERTEL` varchar(10) DEFAULT NULL,
`OSHERTEL` varchar(10) DEFAULT NULL,
`HERTELBASE` varchar(10) DEFAULT NULL,
`RCAROTID` varchar(50) DEFAULT NULL,
`LCAROTID` varchar(50) DEFAULT NULL,
`RTEMPART` varchar(50) DEFAULT NULL,
`LTEMPART` varchar(50) DEFAULT NULL,
`RCNV` varchar(50) DEFAULT NULL,
`LCNV` varchar(50) DEFAULT NULL,
`RCNVII` varchar(50) DEFAULT NULL,
`LCNVII` varchar(50) DEFAULT NULL,
`EXT_COMMENTS` text,
`ODSCHIRMER1` varchar(50) DEFAULT NULL,
`OSSCHRIMER1` varchar(50) DEFAULT NULL,
`ODSCHRIMER2` varchar(50) DEFAULT NULL,
`OSSCHRIMER2` varchar(50) DEFAULT NULL,
`OSCONJ` text,
`ODCONJ` text,
`ODCORNEA` text,
`OSCORNEA` text,
`ODAC` text,
`OSAC` text,
`ODLENS` text,
`OSLENS` text,
`ODIRIS` text,
`OSIRIS` text,
`ODKTHICKNESS` varchar(20) DEFAULT NULL,
`OSKTHICKNESS` varchar(20) DEFAULT NULL,
`ODGONIO` varchar(50) DEFAULT NULL,
`OSGONIO` varchar(50) DEFAULT NULL,
`ANTSEG_COMMENTS` text,
`PUPIL_NORMAL` varchar(2) DEFAULT '1',
`ODPUPILSIZE1` varchar(20) DEFAULT NULL,
`ODPUPILSIZE2` varchar(20) DEFAULT NULL,
`ODPUPILREACTIVITY` varchar(10) DEFAULT NULL,
`ODAPD` varchar(10) DEFAULT NULL,
`OSPUPILSIZE1` varchar(20) DEFAULT NULL,
`OSPUPILSIZE2` varchar(20) DEFAULT NULL,
`OSPUPILREACTIVITY` varchar(10) DEFAULT NULL,
`OSAPD` varchar(20) DEFAULT NULL,
`DIMODPUPILSIZE1` varchar(20) DEFAULT NULL,
`DIMODPUPILSIZE2` varchar(20) DEFAULT NULL,
`DIMODPUPILREACTIVITY` varchar(10) DEFAULT NULL,
`DIMOSPUPILSIZE1` varchar(20) DEFAULT NULL,
`DIMOSPUPILSIZE2` varchar(20) DEFAULT NULL,
`DIMOSPUPILREACTIVITY` varchar(10) DEFAULT NULL,
`PUPIL_COMMENTS` text,
`ODVFCONFRONTATION1` int(1) DEFAULT NULL,
`ODVFCONFRONTATION2` int(1) DEFAULT NULL,
`ODVFCONFRONTATION3` int(1) DEFAULT NULL,
`ODVFCONFRONTATION4` int(1) DEFAULT NULL,
`ODVFCONFRONTATION5` int(1) DEFAULT NULL,
`OSVFCONFRONTATION1` int(1) DEFAULT NULL,
`OSVFCONFRONTATION2` int(1) DEFAULT NULL,
`OSVFCONFRONTATION3` int(1) DEFAULT NULL,
`OSVFCONFRONTATION4` int(1) DEFAULT NULL,
`OSVFCONFRONTATION5` int(1) DEFAULT NULL,
`ODDISC` varchar(100) DEFAULT NULL,
`OSDISC` varchar(100) DEFAULT NULL,
`ODCUP` varchar(100) DEFAULT NULL,
`OSCUP` varchar(100) DEFAULT NULL,
`ODMACULA` varchar(100) DEFAULT NULL,
`OSMACULA` varchar(100) DEFAULT NULL,
`ODVESSELS` varchar(100) DEFAULT NULL,
`OSVESSELS` varchar(100) DEFAULT NULL,
`ODPERIPH` varchar(100) DEFAULT NULL,
`OSPERIPH` varchar(100) DEFAULT NULL,
`ODCMT` varchar(50) DEFAULT NULL,
`OSCMT` varchar(50) DEFAULT NULL,
`RETINA_COMMENTS` text,
`IMP` text,
`PLAN` text,
`Technician` varchar(50) DEFAULT NULL,
`Doctor` varchar(50) DEFAULT NULL,
`Resource` varchar(50) DEFAULT NULL,
`LOCKED` VARCHAR( 3 ) NULL DEFAULT NULL,
`LOCKEDDATE` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
`LOCKEDBY` varchar(50) DEFAULT NULL,
`FINISHED` varchar(25) DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM;
INSERT INTO `registry` (`name`,`state`,`directory`,`sql_run`,`unpackaged`,`date`,`priority`,`category`,`nickname`) VALUES ('Eye Exam', 1, 'eye_mag', 1, 1, '2015-10-15 00:00:00', 0, 'Clinical', '');
#EndIf

#IfNotTable form_eye_mag_prefs
CREATE TABLE `form_eye_mag_prefs` (
  `PEZONE` varchar(25) DEFAULT NULL,
  `LOCATION` varchar(25) DEFAULT NULL,
  `LOCATION_text` varchar(25) NOT NULL,
  `id` bigint(20) DEFAULT NULL,
  `selection` varchar(255) DEFAULT NULL,
  `ZONE_ORDER` int(11) DEFAULT NULL,
  `GOVALUE` varchar(10) DEFAULT '0',
  `ordering` tinyint(4) DEFAULT NULL,
  `FILL_ACTION` varchar(10) NOT NULL DEFAULT 'ADD',
  `GORIGHT` varchar(50) NOT NULL,
  `GOLEFT` varchar(50) NOT NULL,
  `UNSPEC` varchar(50) NOT NULL,
  UNIQUE KEY `id` (`id`,`PEZONE`,`LOCATION`,`selection`)
) ENGINE=InnoDB;

INSERT INTO `form_eye_mag_prefs` (`PEZONE`, `LOCATION`, `LOCATION_text`, `id`, `selection`, `ZONE_ORDER`, `GOVALUE`, `ordering`, `FILL_ACTION`, `GORIGHT`, `GOLEFT`, `UNSPEC`) VALUES
('PREFS', 'ACT_SHOW', 'ACT Show', 2048, 'ACT_SHOW', 65, 'CCDIST', 15, 'ADD', '', '', ''),
('PREFS', 'ACT_VIEW', 'ACT View', 2048, 'ACT_VIEW', 64, '', 14, 'ADD', '', '', ''),
('PREFS', 'ADDITIONAL', 'Additional Data Points', 2048, 'ADDITIONAL', 56, '0', 6, 'ADD', '', '', ''),
('PREFS', 'ANTSEG_DRAW', 'ANTSEG DRAW', 2048, 'ANTSEG_DRAW', 73, NULL, 16, 'ADD', '', '', ''),
('PREFS', 'ANTSEG_RIGHT', 'ANTSEG DRAW', 2048, 'ANTSEG_RIGHT', 73, 'QP', 19, 'ADD', '', '', ''),
('PREFS', 'ANTSEG_VIEW', 'Anterior Segment View', 2048, 'ANTSEG_VIEW', 61, '0', 11, 'ADD', '', '', ''),
('PREFS', 'CLINICAL', 'CLINICAL', 2048, 'CLINICAL', 57, '1', 7, 'ADD', '', '', ''),
('PREFS', 'CR', 'Cycloplegic Refraction', 2048, 'CR', 54, '0', 4, 'ADD', '', '', ''),
('PREFS', 'CTL', 'Contact Lens', 2048, 'CTL', 55, '0', 5, 'ADD', '', '', ''),
('PREFS', 'CYLINDER', 'CYL', 2048, 'CYL', 59, '', 9, 'ADD', '', '', ''),
('PREFS', 'EXAM', 'EXAM', 2048, 'EXAM', 58, 'QP', 8, 'ADD', '', '', ''),
('PREFS', 'EXT_DRAW', 'EXT DRAW', 2048, 'EXT_DRAW', 72, NULL, 16, 'ADD', '', '', ''),
('PREFS', 'EXT_RIGHT', 'EXT DRAW', 2048, 'EXT_RIGHT', 72, 'QP', 18, 'ADD', '', '', ''),
('PREFS', 'EXT_VIEW', 'External View', 2048, 'EXT_VIEW', 66, '0', 16, 'ADD', '', '', ''),
('PREFS', 'HPI_DRAW', 'HPI DRAW', 2048, 'HPI_DRAW', 70, NULL, 16, 'ADD', '', '', ''),
('PREFS', 'HPI_RIGHT', 'HPI DRAW', 2048, 'HPI_RIGHT', 70, '', 16, 'ADD', '', '', ''),
('PREFS', 'HPI_VIEW', 'HPI View', 2048, 'HPI_VIEW', 60, NULL, 10, 'ADD', '', '', ''),
('PREFS', 'IMPPLAN_DRAW', 'IMPPLAN DRAW', 2048, 'IMPPLAN_DRAW', 76, NULL, 16, 'ADD', '', '', ''),
('PREFS', 'IMPPLAN_RIGHT', 'IMPPLAN DRAW', 2048, 'IMPPLAN_RIGHT', 76, NULL, 22, 'ADD', '', '', ''),
('PREFS', 'IOP', 'Intraocular Pressure', 2048, 'IOP', 67, '', 17, 'ADD', '', '', ''),
('PREFS', 'KB_VIEW', 'KeyBoard View', 2048, 'KB_VIEW', 78, '0', 24, 'ADD', '', '', ''),
('PREFS', 'MR', 'Manifest Refraction', 2048, 'MR', 53, '0', 3, 'ADD', '', '', ''),
('PREFS', 'NEURO_DRAW', 'NEURO DRAW', 2048, 'NEURO_DRAW', 75, NULL, 16, 'ADD', '', '', ''),
('PREFS', 'NEURO_RIGHT', 'NEURO DRAW', 2048, 'NEURO_RIGHT', 75, '', 21, 'ADD', '', '', ''),
('PREFS', 'NEURO_VIEW', 'Neuro View', 2048, 'NEURO_VIEW', 63, '', 13, 'ADD', '', '', ''),
('PREFS', 'PANEL_RIGHT', 'PMSFH Panel', 2048, 'PANEL_RIGHT', 77, '1', 23, 'ADD', '', '', ''),
('PREFS', 'PMH_DRAW', 'PMH DRAW', 2048, 'PMH_DRAW', 71, NULL, 16, 'ADD', '', '', ''),
('PREFS', 'PMH_RIGHT', 'PMH DRAW', 2048, 'PMH_RIGHT', 71, '', 17, 'ADD', '', '', ''),
('PREFS', 'RETINA_DRAW', 'RETINA DRAW', 2048, 'RETINA_DRAW', 74, NULL, 16, 'ADD', '', '', ''),
('PREFS', 'RETINA_RIGHT', 'RETINA DRAW', 2048, 'RETINA_RIGHT', 74, '', 20, 'ADD', '', '', ''),
('PREFS', 'RETINA_VIEW', 'Retina View', 2048, 'RETINA_VIEW', 62, '1', 12, 'ADD', '', '', ''),
('PREFS', 'VA', 'Vision', 2048, 'RS', 51, '1', 2048, 'ADD', '', '', ''),
('PREFS', 'VAX', 'Visual Acuities', 2048, 'VAX', 65, '0', 15, 'ADD', '', '', ''),
('PREFS', 'TOOLTIPS', 'Toggle Tooltips', 2048, 'TOOLTIPS', 66, 'on', NULL, 'ADD', '', '', ''),
('PREFS', 'W', 'Current Rx', 2048, 'W', 52, '1', 2, 'ADD', '', '', ''),
('PREFS', 'W_width', 'Detailed Rx', 2048, 'W_width', 80, '100', '', '', '', '', ''),
('PREFS', 'MR_width','Detailed MR', 2048, 'MR_width', 81, '110', '', '', '', '', '');
#EndIf

#IfNotTable form_eye_mag_orders
CREATE TABLE `form_eye_mag_orders` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`ORDER_PID` bigint(20) NOT NULL,
`ORDER_DETAILS` varchar(255) NOT NULL,
`ORDER_STATUS` varchar(50) DEFAULT NULL,
`ORDER_PRIORITY` varchar(50) DEFAULT NULL,
`ORDER_DATE_PLACED` date NOT NULL,
`ORDER_PLACED_BYWHOM` varchar(50) DEFAULT NULL,
`ORDER_DATE_COMPLETED` date DEFAULT NULL,
`ORDER_COMPLETED_BYWHOM` varchar(50) DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `VISIT_ID` (`ORDER_PID`,`ORDER_DETAILS`,`ORDER_DATE_PLACED`,`ORDER_PLACED_BYWHOM`,`ORDER_DATE_COMPLETED`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable form_eye_mag_impplan
CREATE TABLE `form_eye_mag_impplan` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`form_id` bigint(20) NOT NULL,
`pid` bigint(20) NOT NULL,
`title` varchar(255) NOT NULL,
`code` varchar(50) DEFAULT NULL,
`codetype` varchar(50) DEFAULT NULL,
`codedesc` varchar(255) DEFAULT NULL,
`codetext` varchar(255) DEFAULT NULL,
`plan` varchar(3000) DEFAULT NULL,
`PMSFH_link` varchar(50) DEFAULT NULL,
`IMPPLAN_order` tinyint(4) DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `second_index` (`form_id`,`pid`,`title`,`plan`(20))
) ENGINE=InnoDB;
#EndIf

#IfNotTable form_eye_mag_wearing
CREATE TABLE `form_eye_mag_wearing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ENCOUNTER` int(11) NOT NULL,
  `FORM_ID` smallint(6) NOT NULL,
  `PID` int(11) NOT NULL,
  `RX_NUMBER` int(11) NOT NULL,
  `ODSPH` varchar(10) DEFAULT NULL,
  `ODCYL` varchar(10) DEFAULT NULL,
  `ODAXIS` varchar(10) DEFAULT NULL,
  `OSSPH` varchar(10) DEFAULT NULL,
  `OSCYL` varchar(10) DEFAULT NULL,
  `OSAXIS` varchar(10) DEFAULT NULL,
  `ODMIDADD` varchar(10) DEFAULT NULL,
  `OSMIDADD` varchar(10) DEFAULT NULL,
  `ODADD` varchar(10) DEFAULT NULL,
  `OSADD` varchar(10) DEFAULT NULL,
  `ODVA` varchar(10) DEFAULT NULL,
  `OSVA` varchar(10) DEFAULT NULL,
  `ODNEARVA` varchar(10) DEFAULT NULL,
  `OSNEARVA` varchar(10) DEFAULT NULL,
  `ODHPD` varchar(20) DEFAULT NULL,
  `ODHBASE` varchar(20) DEFAULT NULL,
  `ODVPD` varchar(20) DEFAULT NULL,
  `ODVBASE` varchar(20) DEFAULT NULL,
  `ODSLABOFF` varchar(20) DEFAULT NULL,
  `ODVERTEXDIST` varchar(20) DEFAULT NULL,
  `OSHPD` varchar(20) DEFAULT NULL,
  `OSHBASE` varchar(20) DEFAULT NULL,
  `OSVPD` varchar(20) DEFAULT NULL,
  `OSVBASE` varchar(20) DEFAULT NULL,
  `OSSLABOFF` varchar(20) DEFAULT NULL,
  `OSVERTEXDIST` varchar(20) DEFAULT NULL,
  `ODMPDD` varchar(20) DEFAULT NULL,
  `ODMPDN` varchar(20) DEFAULT NULL,
  `OSMPDD` varchar(20) DEFAULT NULL,
  `OSMPDN` varchar(20) DEFAULT NULL,
  `BPDD` varchar(20) DEFAULT NULL,
  `BPDN` varchar(20) DEFAULT NULL,
  `LENS_MATERIAL` varchar(20) DEFAULT NULL,
  `LENS_TREATMENTS` varchar(100) DEFAULT NULL,
  `RX_TYPE` varchar(25) DEFAULT NULL,
  `COMMENTS` text,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `FORM_ID` (`FORM_ID`,`ENCOUNTER`,`PID`,`RX_NUMBER`)
) ENGINE=InnoDB;
#EndIf


#IfNotTable form_taskman
CREATE TABLE `form_taskman` (
    `ID` bigint(20) NOT NULL AUTO_INCREMENT,
    `REQ_DATE` datetime NOT NULL,
    `FROM_ID` bigint(20) NOT NULL,
    `TO_ID` bigint(20) NOT NULL,
    `PATIENT_ID` bigint(20) NOT NULL, `DOC_TYPE` varchar(20) DEFAULT NULL,
    `DOC_ID` bigint(20) DEFAULT NULL,
    `ENC_ID` bigint(20) DEFAULT NULL,
    `METHOD` varchar(20) NOT NULL, `COMPLETED` varchar(1) DEFAULT NULL COMMENT '1 = completed',
    `COMPLETED_DATE` datetime DEFAULT NULL,
    `COMMENT` varchar(50) DEFAULT NULL,
    `USERFIELD_1` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`ID`)
) ENGINE=INNODB;
#EndIf

#IfNotRow categories name Eye Module
INSERT INTO categories select (select MAX(id) from categories) + 1, 'Eye Module', '', 1, rght, rght + 25 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'Communication - Eye', '', (select id from categories where name = 'Eye Module'), rght + 1, rght + 2 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'Encounters - Eye', '', (select id from categories where name = 'Eye Module'), rght + 3, rght + 4 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'Imaging - Eye', '', (select id from categories where name = 'Eye Module'), rght + 5, rght + 24 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'OCT - Eye', 'POSTSEG', (select id from categories where name = 'Imaging - Eye'), rght + 6, rght + 7 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'FA/ICG - Eye', 'POSTSEG', (select id from categories where name = 'Imaging - Eye'), rght + 8, rght + 9 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'External Photos - Eye', 'EXT', (select id from categories where name = 'Imaging - Eye'), rght + 10, rght + 11 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'AntSeg Photos - Eye', 'ANTSEG', (select id from categories where name = 'Imaging - Eye'), rght + 12, rght + 13 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'Optic Disc - Eye', 'POSTSEG', (select id from categories where name = 'Imaging - Eye'), rght + 14, rght + 15 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'Fundus - Eye', 'POSTSEG', (select id from categories where name = 'Imaging - Eye'), rght + 16, rght + 17 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'Radiology - Eye', 'NEURO', (select id from categories where name = 'Imaging - Eye'), rght + 18, rght + 19 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'VF - Eye', 'NEURO', (select id from categories where name = 'Imaging - Eye'), rght + 20, rght + 21 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'Drawings - Eye', 'NEURO', (select id from categories where name = 'Imaging - Eye'), rght + 22, rght + 23 from categories where name = 'Categories';
UPDATE categories SET rght = rght + 26 WHERE name = 'Categories';
UPDATE categories_seq SET id = (select MAX(id) from categories);
#EndIf

UPDATE `list_options` SET `codes` = 'ICD10:I10' WHERE `list_id` = 'medical_problem_issue_list' AND `option_id` = 'HTN';

UPDATE `list_options` SET `codes` = 'ICD10:J45.909' WHERE `list_id` = 'medical_problem_issue_list' AND `option_id` = 'asthma';

UPDATE `list_options` SET `codes` = 'ICD10:E78.5' WHERE `list_id` = 'medical_problem_issue_list' AND `option_id` = 'hyperlipidemia';

#IfNotRow2D list_options list_id medical_problem_issue_list option_id poag
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('medical_problem_issue_list', 'poag','POAG', 10,'ICD10:H40.11X4','eye');
#EndIf

#IfNotRow2D list_options list_id medical_problem_issue_list option_id dermatochalasis
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('medical_problem_issue_list', 'dermatochalasis','Dermatochalasis', 20,'ICD10:H02.839','eye');
#EndIf

#IfNotRow2D list_options list_id medical_problem_issue_list option_id niddm_bdr
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('medical_problem_issue_list', 'niddm_bdr','NIDDM w/ BDR', 30,',ICD10:E11.319','eye');
#EndIf

#IfNotRow2D list_options list_id medical_problem_issue_list option_id ns_cataract
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('medical_problem_issue_list', 'ns_cataract','NS Cataract', 40,'ICD10:H25.10','eye');
#EndIf

#IfNotRow2D list_options list_id medical_problem_issue_list option_id BCC
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('medical_problem_issue_list', 'BCC','BCC', 50,'ICD10:C44.191','eye');
#EndIf

#IfNotRow2D list_options list_id medical_problem_issue_list option_id iddm_bdr
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('medical_problem_issue_list', 'iddm_bdr','IDDM w/ BDR', 60,'ICD10:E10.329','eye');
#EndIf

#IfNotRow2D list_options list_id medical_problem_issue_list option_id Keratoconus
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('medical_problem_issue_list', 'Keratoconus','Keratoconus', 70,'ICD10:H18.603','eye');
#EndIf

#IfNotRow2D list_options list_id medical_problem_issue_list option_id dry_eye
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('medical_problem_issue_list', 'dry_eye','Dry Eye', 80,'ICD10:H04.123','eye');
#EndIf

#IfNotRow2D list_options list_id medical_problem_issue_list option_id SCC
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('medical_problem_issue_list', 'SCC','SCC', 90,'ICD10:C44.191','eye');
#EndIf

#IfNotRow2D list_options list_id medical_problem_issue_list option_id stye
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('medical_problem_issue_list', 'stye','stye', 100,'ICD10:H00.029','eye');
#EndIf

#IfNotRow2D list_options list_id surgery_issue_list option_id Bleph_Upper
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('surgery_issue_list', 'Bleph_Upper', 'Blepharoplasty', 40, 'CPT4:15823-50', 'eye');
#EndIf

#IfNotRow2D list_options list_id surgery_issue_list option_id Phaco_IOL_OD
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('surgery_issue_list', 'Phaco_IOL_OD', 'Phaco/IOL OD', 50, 'CPT4:66984-RT', 'eye');
#EndIf

#IfNotRow2D list_options list_id surgery_issue_list option_id Phaco_IOL_OS
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('surgery_issue_list', 'Phaco_IOL_OS', 'Phaco/IOL OS', 60, 'CPT4:66984-LT', 'eye');
#EndIf

#IfNotRow2D list_options list_id surgery_issue_list option_id LPI_OD
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('surgery_issue_list', 'LPI_OD', 'LPI OD', 70, 'CPT4:66761-RT', 'eye');
#EndIf

#IfNotRow2D list_options list_id surgery_issue_list option_id LPI_OS
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('surgery_issue_list', 'LPI_OS', 'LPI OS', 80, 'CPT4:66761-LT', 'eye');
#EndIf

#IfNotRow2D list_options list_id surgery_issue_list option_id ALT_OD
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('surgery_issue_list', 'ALT_OD', 'ALT OD', 90, 'CPT4:65855-RT', 'eye');
#EndIf

#IfNotRow2D list_options list_id surgery_issue_list option_id ALT_OS
INSERT INTO list_options(list_id,option_id,title,seq,codes,subtype) VALUES ('surgery_issue_list', 'ALT_OS', 'ALT OS', 100, 'CPT4:65855-LT', 'eye');
#EndIf


#IfNotRow2D list_options list_id lists option_id CTLManufacturer
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists', 'CTLManufacturer', 'Eye Contact Lens Manufacturer list', 1, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLManufacturer', 'BNL', 'Bausch&Lomb', 10, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLManufacturer', 'CibaVision', 'Ciba Vision', 20, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLManufacturer', 'Cooper', 'CooperVision', 30, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLManufacturer', 'JNJ', 'Johnson&Johnson', 40, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id CTLSupplier
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists'    ,'CTLSupplier', 'Eye Contact Lens Supplier list', 1,0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLSupplier', 'ABB', 'ABB Optical', 10, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLSupplier', 'JNJ', 'Johnson&Johnson', 20, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLSupplier', 'LF', 'Lens Ferry', 30, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id CTLBrand
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists'   ,'CTLBrand', 'Eye Contact Lens Brand list', 1, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLBrand', 'Acuvue', 'Acuvue', 10, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLBrand', 'Acuvue2', 'Acuvue 2', 20, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLBrand', 'AcuvueOa', 'Acuvue Oasys', 30, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLBrand', 'SF66', 'SofLens Toric', 40, 0);
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('CTLBrand', 'PVMF', 'PureVision MultiFocal', 50, 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id Eye_Coding_Fields
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists' ,'Eye_Coding_Fields', 'Eye Coding Fields', 1, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_Coding_Fields', 'RUL', 'RUL', 10, 0, 0, '', 'right upper eyelid', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'RLL', 'RLL', 20, 0, 0, '', 'right lower eyelid', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'LUL', 'LUL', 30, 0, 0, '', 'left upper eyelid', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'LLL', 'LLL', 40, 0, 0, '', 'left lower eyelid', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'RBROW', 'RBROW', 50, 0, 0, '', 'forehead', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'LBROW', 'LBROW', 60, 0, 0, '', 'forehead', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'RMCT', 'RMCT', 70, 0, 0, '', 'canthus', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'LMCT', 'LMCT', 80, 0, 0, '', 'canthus', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'RBROW_unspec', 'RBROW', 90, 0, 0, '', 'unspecified', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'LBROW_unspec', 'LBROW', 100, 0, 0, '', 'unspecified', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'RADNEXA', 'RADNEXA', 110, 0, 0, '', 'unspecified', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'LADNEXA', 'LADNEXA', 120, 0, 0, '', 'unspecified', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'ODCONJ', 'ODCONJ', 130, 0, 0, '', 'right conjunctiva', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'OSCONJ', 'OSCONJ', 140, 0, 0, '', 'left conjunctiva', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'ODCORNEA', 'ODCORNEA', 150, 0, 0, '', 'right cornea', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'OSCORNEA', 'OSCORNEA', 160, 0, 0, '', 'left cornea', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'ODAC', 'ODAC', 170, 0, 0, '', 'right anterior chamber', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'OSAC', 'OSAC', 180, 0, 0, '', 'left anterior chamber', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'ODLENS', 'ODLENS', 190, 0, 0, '', 'right lens', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'OSLENS', 'OSLENS', 200, 0, 0, '', 'left lens', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'ODIRIS', 'ODIRIS', 210, 0, 0, '', 'right iris', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'OSIRIS', 'OSIRIS', 220, 0, 0, '', 'left iris', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'ODDISC', 'ODDISC', 230, 0, 0, '', 'right', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'OSDISC', 'OSDISC', 240, 0, 0, '', 'left', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'ODMAC', 'ODMACULA', 250, 0, 0, '', 'right macula', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'OSMAC', 'OSMACULA', 260, 0, 0, '', 'left macula', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'ODVESSELS', 'ODVESSELS', 270, 0, 0, '', 'right', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'OSVESSELS', 'OSVESSELS', 280, 0, 0, '', 'left', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'ODPERIPH', 'ODPERIPH', 290, 0, 0, '', 'right', '', 0, 0, 1, ''),
('Eye_Coding_Fields', 'OSPERIPH', 'OSPERIPH', 300, 0, 0, '', 'left', '', 0, 0, 1, '');
#EndIf

#IfNotRow2D list_options list_id lists option_id Eye_Coding_Terms
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists' ,'Eye_Coding_Terms', 'Eye Coding Terms', 1, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_Coding_Terms', 'dermato_RUL', 'dermatochalasis', 10, 0, 0, '', 'RUL', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'dermato_RLL', 'dermatochalasis', 20, 0, 0, '', 'RLL', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'dermato_LUL', 'dermatochalasis', 30, 0, 0, '', 'LUL', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'dermato_LLL', 'dermatochalasis', 40, 0, 0, '', 'LLL', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'ptosis_RUL', 'ptosis', 50, 0, 0, '', 'RUL', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'ptosis_LUL', 'ptosis', 60, 0, 0, '', 'LUL', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'chalazion_RUL', 'chalazion', 70, 0, 0, '', 'RUL', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'chalazion_RLL', 'chalazion', 80, 0, 0, '', 'RLL', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'chalazion_LUL', 'chalazion', 90, 0, 0, '', 'LUL', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'chalazion_LLL', 'chalazion', 100, 0, 0, '', 'LLL', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'cicectr_RUL', 'cicatricial ectropion', 110, 0, 0, '', 'RUL', 'ICD10:H02.111', 0, 0, 1, ''),
('Eye_Coding_Terms', 'cicectr_RLL', 'cicatricial ectropion', 120, 0, 0, '', 'RLL', 'ICD10:H02.112', 0, 0, 1, ''),
('Eye_Coding_Terms', 'cicectr_LUL', 'cicatricial ectropion', 130, 0, 0, '', 'LUL', 'ICD10:H02.114', 0, 0, 1, ''),
('Eye_Coding_Terms', 'cicectr_LLL', 'cicatricial ectropion', 140, 0, 0, '', 'LLL', 'ICD10:H02.115', 0, 0, 1, ''),
('Eye_Coding_Terms', 'spasectr_RUL', 'spastic ectropion', 150, 0, 0, '', 'RUL', 'ICD10:H02.141', 0, 0, 1, ''),
('Eye_Coding_Terms', 'spasentr_RUL', 'spastic entropion', 160, 0, 0, '', 'RUL', 'ICD10:H02.041', 0, 0, 1, ''),
('Eye_Coding_Terms', 'spasectr_RLL', 'spastic ectropion', 170, 0, 0, '', 'RLL', 'ICD10:H02.142', 0, 0, 1, ''),
('Eye_Coding_Terms', 'spasentr_RLL', 'spastic entropion', 180, 0, 0, '', 'RLL', 'ICD10:H02.042', 0, 0, 1, ''),
('Eye_Coding_Terms', 'spasectr_LUL', 'spastic ectropion', 190, 0, 0, '', 'LUL', 'ICD10:H02.144', 0, 0, 1, ''),
('Eye_Coding_Terms', 'spasentr_LUL', 'spastic entropion', 200, 0, 0, '', 'LUL', 'ICD10:H02.044', 0, 0, 1, ''),
('Eye_Coding_Terms', 'spasectr_LLL', 'spastic ectropion', 210, 0, 0, '', 'LLL', 'ICD10:H02.145', 0, 0, 1, ''),
('Eye_Coding_Terms', 'spasentr_LLL', 'spastic entropion', 220, 0, 0, '', 'LLL', 'ICD10:H02.045', 0, 0, 1, ''),
('Eye_Coding_Terms', 'cicentr_RUL', 'cicatricial entropion', 230, 0, 0, '', 'RUL', 'ICD10:H02.111', 0, 0, 1, ''),
('Eye_Coding_Terms', 'cicentr_RLL', 'cicatricial entropion', 240, 0, 0, '', 'RLL', 'ICD10:H02.112', 0, 0, 1, ''),
('Eye_Coding_Terms', 'cicentr_LUL', 'cicatricial entropion', 250, 0, 0, '', 'LUL', 'ICD10:H02.114', 0, 0, 1, ''),
('Eye_Coding_Terms', 'cicentr_LLL', 'cicatricial entropion', 260, 0, 0, '', 'LLL', 'ICD10:H02.115', 0, 0, 1, ''),
('Eye_Coding_Terms', 'ect_RUL', 'ectropion', 270, 0, 0, '', 'RUL', 'ICD10:H02.101', 0, 0, 1, ''),
('Eye_Coding_Terms', 'ect_RLL', 'ectropion', 280, 0, 0, '', 'RLL', 'ICD10:H02.102', 0, 0, 1, ''),
('Eye_Coding_Terms', 'ect_LUL', 'ectropion', 290, 0, 0, '', 'LUL', 'ICD10:H02.104', 0, 0, 1, ''),
('Eye_Coding_Terms', 'ect_LLL', 'ectropion', 300, 0, 0, '', 'LLL', 'ICD10:H02.105', 0, 0, 1, ''),
('Eye_Coding_Terms', 'ent_RUL', 'entropion', 310, 0, 0, '', 'RUL', 'ICD10:H02.001', 0, 0, 1, ''),
('Eye_Coding_Terms', 'ent_RLL', 'entropion', 320, 0, 0, '', 'RLL', 'ICD10:H02.002', 0, 0, 1, ''),
('Eye_Coding_Terms', 'ent_LUL', 'entropion', 330, 0, 0, '', 'LUL', 'ICD10:H02.004', 0, 0, 1, ''),
('Eye_Coding_Terms', 'ent_LLL', 'entropion', 340, 0, 0, '', 'LLL', 'ICD10:H02.005', 0, 0, 1, ''),
('Eye_Coding_Terms', 'trich_RUL', 'trichiasis', 350, 0, 0, '', 'RUL', 'ICD10:H02.051', 0, 0, 1, ''),
('Eye_Coding_Terms', 'trich_RLL', 'trichiasis', 360, 0, 0, '', 'RLL', 'ICD10:H02.052', 0, 0, 1, ''),
('Eye_Coding_Terms', 'trich_LUL', 'trichiasis', 370, 0, 0, '', 'LUL', 'ICD10:H02.054', 0, 0, 1, ''),
('Eye_Coding_Terms', 'trich_LLL', 'trichiasis', 380, 0, 0, '', 'LLL', 'ICD10:H02.055', 0, 0, 1, ''),
('Eye_Coding_Terms', 'stye_RUL', 'stye', 390, 0, 0, '', 'RUL', 'ICD10:H00.011', 0, 0, 1, ''),
('Eye_Coding_Terms', 'stye_RLL', 'stye', 400, 0, 0, '', 'RLL', 'ICD10:H00.012', 0, 0, 1, ''),
('Eye_Coding_Terms', 'stye_LUL', 'stye', 410, 0, 0, '', 'LUL', 'ICD10:H00.014', 0, 0, 1, ''),
('Eye_Coding_Terms', 'stye_LLL', 'stye', 420, 0, 0, '', 'LLL', 'ICD10:H00.015', 0, 0, 1, ''),
('Eye_Coding_Terms', 'papillae_ODCONJ', 'papilla', 430, 0, 0, '', 'ODCONJ', 'ICD10:H10.401', 0, 0, 1, ''),
('Eye_Coding_Terms', 'papillae_OSCONJ', 'papilla', 440, 0, 0, '', 'OSCONJ', 'ICD10:H10.402', 0, 0, 1, ''),
('Eye_Coding_Terms', 'folllicles_ODCONJ', 'folllicles', 450, 0, 0, '', 'ODCONJ', 'ICD10:H10.011', 0, 0, 1, ''),
('Eye_Coding_Terms', 'folllicles_OSCONJ', 'folllicles', 460, 0, 0, '', 'OSCONJ', 'ICD10:H10.012', 0, 0, 1, ''),
('Eye_Coding_Terms', 'pterygium_ODCORNEA', 'pterygium', 470, 0, 0, '', 'ODCORNEA', 'ICD10:H11.051', 0, 0, 1, ''),
('Eye_Coding_Terms', 'pterygium_ODCONJ', 'pterygium', 480, 0, 0, '', 'ODCONJ', 'ICD10:H11.811', 0, 0, 1, ''),
('Eye_Coding_Terms', 'pterygium_OSCONJ', 'pterygium', 490, 0, 0, '', 'OSCONJ', 'ICD10:H11.812', 0, 0, 1, ''),
('Eye_Coding_Terms', 'pterygium_OSCORNEA', 'pterygium', 500, 0, 0, '', 'OSCORNEA', 'ICD10:H11.052', 0, 0, 1, ''),
('Eye_Coding_Terms', 'abrasion_ODCORNEA', 'abrasion', 510, 0, 0, '', 'ODCORNEA', 'ICD10:S05.01XA', 0, 0, 1, ''),
('Eye_Coding_Terms', 'abrasion_OSCORNEA', 'abrasion', 520, 0, 0, '', 'OSCORNEA', 'ICD10:S05.02XA', 0, 0, 1, ''),
('Eye_Coding_Terms', 'FB_ODCORNEA', 'FB', 530, 0, 0, '', 'ODCORNEA', 'ICD10:T15.01XA', 0, 0, 1, ''),
('Eye_Coding_Terms', 'FB_OSCORNEA', 'FB', 540, 0, 0, '', 'OSCORNEA', 'ICD10:T15.02XA', 0, 0, 1, ''),
('Eye_Coding_Terms', 'dendrite_ODCORNEA', 'dendrite', 550, 0, 0, '', 'ODCORNEA', 'ICD10:B00.52', 0, 0, 1, ''),
('Eye_Coding_Terms', 'dendrite_OSCORNEA', 'dendrite', 560, 0, 0, '', 'OSCORNEA', 'ICD10:B00.52', 0, 0, 1, ''),
('Eye_Coding_Terms', 'MDF_ODCORNEA', 'MDF', 570, 0, 0, '', 'ODCORNEA', 'ICD10:H18.59', 0, 0, 1, ''),
('Eye_Coding_Terms', 'MDF_OSCORNEA', 'MDF', 580, 0, 0, '', 'OSCORNEA', 'ICD10:H18.59', 0, 0, 1, ''),
('Eye_Coding_Terms', 'NS_ODLENS', 'NS', 590, 0, 0, '', 'ODLENS', 'ICD10:H25.11', 0, 0, 1, ''),
('Eye_Coding_Terms', 'NS_OSLENS', 'NS', 600, 0, 0, '', 'OSLENS', 'ICD10:H25.12', 0, 0, 1, ''),
('Eye_Coding_Terms', 'PSC_ODLENS', 'PSC', 610, 0, 0, '', 'ODLENS', 'ICD10:H25.041', 0, 0, 1, ''),
('Eye_Coding_Terms', 'PSC_OSLENS', 'PSC', 620, 0, 0, '', 'OSLENS', 'ICD10:H25.042', 0, 0, 1, ''),
('Eye_Coding_Terms', 'PCIOL_ODLENS', 'PCIOL', 630, 0, 0, '', 'ODLENS', 'ICD10:Z96.1', 0, 0, 1, ''),
('Eye_Coding_Terms', 'PCIOL_OSLENS', 'PCIOL', 640, 0, 0, '', 'OSLENS', 'ICD10:Z96.1', 0, 0, 1, ''),
('Eye_Coding_Terms', 'hyphema_ODAC', 'hyphema', 650, 0, 0, '', 'ODAC', 'ICD10:H21.01', 0, 0, 1, ''),
('Eye_Coding_Terms', 'hyphema_OSAC', 'hyphema', 660, 0, 0, '', 'OSAC', 'ICD10:H21.02', 0, 0, 1, ''),
('Eye_Coding_Terms', 'horseshoe_ODPERIPHERY', 'horseshoe', 670, 0, 0, '', 'ODPERIPH', 'ICD10:H33.311', 0, 0, 1, ''),
('Eye_Coding_Terms', 'horseshoe_OSPERIPHERY', 'horseshoe', 680, 0, 0, '', 'OSPERIPH', 'ICD10:H33.312', 0, 0, 1, ''),
('Eye_Coding_Terms', 'hole_ODPERIPHERY', 'hole', 690, 0, 0, '', 'ODPERIPH', 'ICD10:H33.321', 0, 0, 1, ''),
('Eye_Coding_Terms', 'hole_OSPERIPHERY', 'hole', 700, 0, 0, '', 'OSPERIPH', 'ICD10:H33.322', 0, 0, 1, ''),
('Eye_Coding_Terms', 'CSR_ODMACULA', 'CSR', 710, 0, 0, '', 'ODMACULA', 'ICD10:H35.711', 0, 0, 1, ''),
('Eye_Coding_Terms', 'hole_ODMACULA', 'Mac hole', 720, 0, 0, '', 'ODMACULA', 'ICD10:H35.341', 0, 0, 1, ''),
('Eye_Coding_Terms', 'CSR_OSMACULA', 'CSR', 730, 0, 0, '', 'OSMACULA', 'ICD10:H35.712', 0, 0, 1, ''),
('Eye_Coding_Terms', 'hole_OSMACULA', 'Mac hole', 740, 0, 0, '', 'OSMACULA', 'ICD10:H35.342', 0, 0, 1, ''),
('Eye_Coding_Terms', 'drusen_ODMACULA', 'drusen', 750, 0, 0, '', 'ODMACULA', 'ICD10:H35.361', 0, 0, 1, ''),
('Eye_Coding_Terms', 'drusen_OSMACULA', 'drusen', 760, 0, 0, '', 'OSMACULA', 'ICD10:H35.362', 0, 0, 1, ''),
('Eye_Coding_Terms', 'drusen_ODDISC', 'drusen', 770, 0, 0, '', 'ODDISC', 'ICD10:H47.321', 0, 0, 1, ''),
('Eye_Coding_Terms', 'drusen_OSDISC', 'drusen', 780, 0, 0, '', 'ODDISC', 'ICD10:H47.322', 0, 0, 1, ''),
('Eye_Coding_Terms', 'BRVO_ODPERIPHERY', 'BRVO', 790, 0, 0, '', 'ODVESSELS', 'ICD10:H34.831', 0, 0, 1, ''),
('Eye_Coding_Terms', 'CRVO_ODPERIPHERY', 'CRVO', 800, 0, 0, '', 'ODVESSELS', 'ICD10:H34.811', 0, 0, 1, ''),
('Eye_Coding_Terms', 'lattice_ODPERIPHERY', 'lattice', 810, 0, 0, '', 'ODPERIPH', 'ICD10:H35.412', 0, 0, 1, ''),
('Eye_Coding_Terms', 'BRVO_OSPERIPHERY', 'BRVO', 820, 0, 0, '', 'OSVESSELS', 'ICD10:H34.832', 0, 0, 1, ''),
('Eye_Coding_Terms', 'CRVO_OSPERIPHERY', 'CRVO', 830, 0, 0, '', 'OSVESSELS', 'ICD10:H34.812', 0, 0, 1, ''),
('Eye_Coding_Terms', 'lattice_OSPERIPHERY', 'lattice', 840, 0, 0, '', 'OSPERIPH', 'ICD10:H35.412', 0, 0, 1, ''),
('Eye_Coding_Terms', 'NLDO_RMCT', 'NLDO', 850, 0, 0, '', 'RMCT', 'ICD10:H04.411', 0, 0, 1, ''),
('Eye_Coding_Terms', 'NLDO_LMCT', 'NLDO', 860, 0, 0, '', 'LMCT', 'ICD10:H04.412', 0, 0, 1, ''),
('Eye_Coding_Terms', 'NVD_ODDISC', 'NVD:DM', 870, 0, 0, '', 'ODDISC', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'NVD_OSDISC', 'NVD:DM', 880, 0, 0, '', 'OSDISC', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'CSME_ODMACULA', 'CSME:DM|IOL|RVO', 890, 0, 0, '', 'ODMACULA', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'CSME_OSMACULA', 'CSME:DM|IOL|RVO', 900, 0, 0, '', 'OSMACULA', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'NVE_ODVESSELS', 'NVE:DM', 910, 0, 0, '', 'ODVESSELS', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'NVE_OSVESSELS', 'NVE:DM', 920, 0, 0, '', 'OSVESSELS', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'NVE_ODPERIPHERY', 'NVE:DM', 930, 0, 0, '', 'ODPERIPH', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'NVE_OSPERIPHERY', 'NVE:DM', 940, 0, 0, '', 'OSPERIPH', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'BDR_ODMACULA', 'BDR:DM', 950, 0, 0, '', 'ODMACULA', '', 0, 0, 1, ''),
('Eye_Coding_Terms', 'BDR_OSMACULA', 'BDR:DM', 960, 0, 0, '', 'OSMACULA', '', 0, 0, 1, '');
#EndIf

#IfNotRow2D list_options list_id lists option_id Eye_QP_ANTSEG_defaults
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists' ,'Eye_QP_ANTSEG_defaults', 'Eye QP List ANTSEG for New Providers', 1, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_ANTSEG_defaults', 'ODCONJ_cl', 'c: clear field', 10, 0, 0, 'CONJ', '', '', 0, 0, 1, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCONJ_cl', 'c: clear field', 20, 0, 0, 'CONJ', '', '', 0, 0, 1, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCONJ_cl', 'c: clear field', 30, 0, 0, 'CONJ', '', '', 0, 0, 1, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCONJ_quiet', 'c: quiet', 40, 0, 0, 'CONJ', 'quiet', '', 0, 0, 1, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCONJ_quiet', 'c: quiet', 50, 0, 0, 'CONJ', 'quiet', '', 0, 0, 1, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCONJ_quiet', 'c: quiet', 60, 0, 0, 'CONJ', 'quiet', '', 0, 0, 1, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCONJ_inj', 'c: injection', 70, 0, 0, 'CONJ', 'injection', 'ICD10:H10.31', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCONJ_inj', 'c: injection', 80, 0, 0, 'CONJ', 'injection', 'ICD10:H10.32', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCONJ_inj', 'c: injection', 90, 0, 0, 'CONJ', 'injection', 'ICD10:H10.33', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCONJ_pap', 'c: papillae', 100, 0, 0, 'CONJ', 'papillae', 'ICD10:H10.31', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCONJ_pap', 'c: papillae', 110, 0, 0, 'CONJ', 'papillae', 'ICD10:H10.32', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCONJ_pap', 'c: papillae', 120, 0, 0, 'CONJ', 'papillae', 'ICD10:H10.33', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCONJ_gpap', 'c: giant pap', 130, 0, 0, 'CONJ', 'giant papillae', 'ICD10:H10.411', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCONJ_gpap', 'c: giant pap', 140, 0, 0, 'CONJ', 'giant papillae', 'ICD10:H10.412', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCONJ_gpap', 'c: giant pap', 150, 0, 0, 'CONJ', 'giant papillae', 'ICD10:H10.413', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCONJ_pinq', 'c: pinquecula', 160, 0, 0, 'CONJ', 'pinquecula', 'ICD10:H11.151', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCONJ_pinq', 'c: pinquecula', 170, 0, 0, 'CONJ', 'pinquecula', 'ICD10:H11.152', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCONJ_pinq', 'c: pinquecula', 180, 0, 0, 'CONJ', 'pinquecula', 'ICD10:H11.153', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCONJ_foll', 'c: follicles', 190, 0, 0, 'CONJ', 'follicles', 'ICD10:H10.011', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCONJ_foll', 'c: follicles', 200, 0, 0, 'CONJ', 'follicles', 'ICD10:H10.012', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCONJ_foll', 'c: follicles', 210, 0, 0, 'CONJ', 'follicles', 'ICD10:H10.013', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCONJ_mucop', 'c: mucopurulence', 220, 0, 0, 'CONJ', 'mucopurulence', 'ICD10:H10.013', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCONJ_mucop', 'c: mucopurulence', 230, 0, 0, 'CONJ', 'mucopurulence', 'ICD10:H10.013', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCONJ_mucop', 'c: mucopurulence', 240, 0, 0, 'CONJ', 'mucopurulence', 'ICD10:H10.013', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCONJ_bleb', 'c: mod bleb', 250, 0, 0, 'CONJ', 'moderate bleb', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCONJ_bleb', 'c: mod bleb', 260, 0, 0, 'CONJ', 'moderate bleb', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCONJ_bleb', 'c: mod bleb', 270, 0, 0, 'CONJ', 'moderate bleb', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCONJ_sied', 'c: siedel negative', 280, 0, 0, 'CONJ', 'siedel negative', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCONJ_sied', 'c: siedel negative', 290, 0, 0, 'CONJ', 'siedel negative', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCONJ_sied', 'c: siedel negative', 300, 0, 0, 'CONJ', 'siedel negative', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCORNEA_cl', 'k: clear field', 310, 0, 0, 'CORNEA', '', '', 0, 0, 1, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCORNEA_cl', 'k: clear field', 320, 0, 0, 'CORNEA', '', '', 0, 0, 1, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCORNEA_cl', 'k: clear field', 330, 0, 0, 'CORNEA', '', '', 0, 0, 1, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCORNEA_clear', 'k: clear', 340, 0, 0, 'CORNEA', 'clear', '', 0, 0, 1, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCORNEA_clear', 'k: clear', 350, 0, 0, 'CORNEA', 'clear', '', 0, 0, 1, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCORNEA_clear', 'k: clear', 360, 0, 0, 'CORNEA', 'clear', '', 0, 0, 1, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCORNEA_abr', 'k: abrasion', 370, 0, 0, 'CORNEA', 'abrasion', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCORNEA_abr', 'k: abrasion', 380, 0, 0, 'CORNEA', 'abrasion', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCORNEA_abr', 'k: abrasion', 390, 0, 0, 'CORNEA', 'abrasion', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCORNEA_MDF', 'k: MDFP dystrophy', 400, 0, 0, 'CORNEA', 'MDFP dystrophy', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCORNEA_MDF', 'k: MDFP dystrophy', 410, 0, 0, 'CORNEA', 'MDFP dystrophy', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCORNEA_MDF', 'k: MDFP dystrophy', 420, 0, 0, 'CORNEA', 'MDFP dystrophy', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCORNEA_FB', 'k: metallic FB', 430, 0, 0, 'CORNEA', 'metallic FB', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCORNEA_FB', 'k: metallic FB', 440, 0, 0, 'CORNEA', 'metallic FB', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCORNEA_FB', 'k: metallic FB', 450, 0, 0, 'CORNEA', 'metallic FB', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCORNEA_edema', 'k: edema', 460, 0, 0, 'CORNEA', 'edema', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCORNEA_edema', 'k: edema', 470, 0, 0, 'CORNEA', 'edema', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCORNEA_edema', 'k: edema', 480, 0, 0, 'CORNEA', 'edema', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCORNEA_HSV', 'k: dendrite', 490, 0, 0, 'CORNEA', 'dendrite', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCORNEA_HSV', 'k: dendrite', 500, 0, 0, 'CORNEA', 'dendrite', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCORNEA_HSV', 'k: dendrite', 510, 0, 0, 'CORNEA', 'dendrite', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCORNEA_scar', 'k: stromal scar', 520, 0, 0, 'CORNEA', 'stromal scar', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCORNEA_scar', 'k: stromal scar', 530, 0, 0, 'CORNEA', 'stromal scar', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCORNEA_scar', 'k: stromal scar', 540, 0, 0, 'CORNEA', 'stromal scar', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCORNEA_gut', 'k: guttatae', 550, 0, 0, 'CORNEA', 'guttatae', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCORNEA_gut', 'k: guttatae', 560, 0, 0, 'CORNEA', 'guttatae', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCORNEA_gut', 'k: guttatae', 570, 0, 0, 'CORNEA', 'guttatae', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCORNEA_fkp', 'k: fine KPs', 580, 0, 0, 'CORNEA', 'fine KPs', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCORNEA_fkp', 'k: fine KPs', 590, 0, 0, 'CORNEA', 'fine KPs', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCORNEA_fkp', 'k: fine KPs', 600, 0, 0, 'CORNEA', 'fine KPs', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODCORNEA_mkp', 'k: mutton-fat KPs', 610, 0, 0, 'CORNEA', 'mutton-fat keratic precipitates', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSCORNEA_mkp', 'k: mutton-fat KPs', 620, 0, 0, 'CORNEA', 'mutton-fat keratic precipitates', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUCORNEA_mkp', 'k: mutton-fat KPs', 630, 0, 0, 'CORNEA', 'mutton-fat keratic precipitates', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODAC_cl', 'ac: clear field', 640, 0, 0, 'AC', '', '', 0, 0, 1, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSAC_cl', 'ac: clear field', 650, 0, 0, 'AC', '', '', 0, 0, 1, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUAC_cl', 'ac: clear field', 660, 0, 0, 'AC', '', '', 0, 0, 1, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODAC_clear', 'ac: clear', 670, 0, 0, 'AC', 'clear', '', 0, 0, 1, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSAC_clear', 'ac: clear', 680, 0, 0, 'AC', 'clear', '', 0, 0, 1, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUAC_clear', 'ac: clear', 690, 0, 0, 'AC', 'clear', '', 0, 0, 1, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODAC_fc', 'ac: F/C', 700, 0, 0, 'AC', 'F/C', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSAC_fc', 'ac: F/C', 710, 0, 0, 'AC', 'F/C', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUAC_fc', 'ac: F/C', 720, 0, 0, 'AC', 'F/C', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODAC_nar', 'ac :narrow', 730, 0, 0, 'AC', 'narrow', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSAC_nar', 'ac :narrow', 740, 0, 0, 'AC', 'narrow', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUAC_nar', 'ac :narrow', 750, 0, 0, 'AC', 'narrow', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODAC_hyp', 'ac: hyphema', 760, 0, 0, 'AC', 'hyphema', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSAC_hyp', 'ac: hyphema', 770, 0, 0, 'AC', 'hyphema', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUAC_hyp', 'ac: hyphema', 780, 0, 0, 'AC', 'hyphema', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODLENS_cl', 'lens: clear field', 790, 0, 0, 'LENS', '', '', 0, 0, 1, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSLENS_cl', 'lens: clear field', 800, 0, 0, 'LENS', '', '', 0, 0, 1, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OULENS_cl', 'lens: clear field', 810, 0, 0, 'LENS', '', '', 0, 0, 1, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODLENS_pxe', 'lens: PXE', 820, 0, 0, 'LENS', 'PXE', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSLENS_pxe', 'lens: PXE', 830, 0, 0, 'LENS', 'PXE', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OULENS_pxw', 'lens: PXE', 840, 0, 0, 'LENS', 'PXE', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODLENS_psc', 'lens: PSC', 850, 0, 0, 'LENS', 'PSC', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSLENS_psc', 'lens: PSC', 860, 0, 0, 'LENS', 'PSC', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OULENS_psc', 'lens: PSC', 870, 0, 0, 'LENS', 'PSC', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODLENS_ns', 'lens: NS', 880, 0, 0, 'LENS', 'NS', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSLENS_ns', 'lens: NS', 890, 0, 0, 'LENS', 'NS', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OULENS_ns', 'lens: NS', 900, 0, 0, 'LENS', 'NS', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODLENS_cort', 'lens: cortical', 910, 0, 0, 'LENS', 'cortical', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSLENS_cort', 'lens: cortical', 920, 0, 0, 'LENS', 'cortical', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OULENS_cort', 'lens: cortical', 930, 0, 0, 'LENS', 'cortical', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODLENS_PC', 'lens: PCIOL', 940, 0, 0, 'LENS', 'PCIOL', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSLENS_PC', 'lens: PCIOL', 950, 0, 0, 'LENS', 'PCIOL', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OULENS_PC', 'lens: PCIOL', 960, 0, 0, 'LENS', 'PCIOL', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODLENS_yag', 'lens: p YAG', 970, 0, 0, 'LENS', 'PC open', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSLENS_yag', 'lens: p YAG', 980, 0, 0, 'LENS', 'PC open', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OULENS_yag', 'lens: p YAG', 990, 0, 0, 'LENS', 'PC open', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODIRIS_cl', 'iris: clear field', 1000, 0, 0, 'IRIS', '', '', 0, 0, 1, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSIRIS_cl', 'iris: clear field', 1010, 0, 0, 'IRIS', '', '', 0, 0, 1, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUIRIS_cl', 'iris: clear field', 1020, 0, 0, 'IRIS', '', '', 0, 0, 1, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODIRIS_pxe', 'iris: PXE', 1030, 0, 0, 'IRIS', 'PXE', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSIRIS_px', 'iris: PXE', 1040, 0, 0, 'IRIS', 'PXE', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUIRIS_px', 'iris: PXE', 1050, 0, 0, 'IRIS', 'PXE', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODIRIS_pi', 'iris: PI', 1060, 0, 0, 'IRIS', 'patent PI', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSIRIS_pi', 'iris: PI', 1070, 0, 0, 'IRIS', 'patent PI', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUIRIS_pi', 'iris: PI', 1080, 0, 0, 'IRIS', 'patent PI', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODIRIS_nev', 'iris: nevus', 1090, 0, 0, 'IRIS', 'nevus', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSIRIS_nev', 'iris: nevus', 1100, 0, 0, 'IRIS', 'nevus', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUIRIS_nev', 'iris: nevus', 1110, 0, 0, 'IRIS', 'nevus', '', 0, 0, 0, 'OU'),
('Eye_QP_ANTSEG_defaults', 'ODIRIS_nv', 'iris: NVI', 1120, 0, 0, 'IRIS', 'NVI', '', 0, 0, 0, 'OD'),
('Eye_QP_ANTSEG_defaults', 'OSIRIS_nv', 'iris: NVI', 1130, 0, 0, 'LENS', 'NVI', '', 0, 0, 0, 'OS'),
('Eye_QP_ANTSEG_defaults', 'OUIRIS_nv', 'iris: NVI', 1140, 0, 0, 'IRIS', 'NVI', '', 0, 0, 0, 'OU');
#EndIf

#IfNotRow2D list_options list_id lists option_id Eye_QP_EXT_defaults
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists' ,'Eye_QP_EXT_defaults', 'Eye QP List EXT for New Providers', 1, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_EXT_defaults', 'RBROW', 'BROW: clear field', 10, 0, 0, 'BROW', '', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LBROW', 'BROW: clear field', 20, 0, 0, 'BROW', '', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BBROW', 'BROW: clear field', 30, 0, 0, 'BROW', '', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RBROW_ptosis', 'BROW: ptosis', 40, 0, 0, 'BROW', 'ptosis', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LBROW_ptosis', 'BROW: ptosis', 50, 0, 0, 'BROW', 'ptosis', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BBROW_ptosis', 'BROW: ptosis', 60, 0, 0, 'BROW', 'ptosis', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RBROW_rhytids', 'BROW: rhytids', 70, 0, 0, 'BROW', 'rhytids', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LBROW_rhytids', 'BROW: rhytids', 80, 0, 0, 'BROW', 'rhytids', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BBROW_rhytids', 'BROW: rhytids', 90, 0, 0, 'BROW', 'rhytids', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RBROW_scar', 'BROW: scar', 100, 0, 0, 'BROW', 'scar', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LBROW_scar', 'BROW: scar', 110, 0, 0, 'BROW', 'scar', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BBROW_scar', 'BROW: scar', 120, 0, 0, 'BROW', 'scar', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RBROW_sebk', 'BROW: seb ker', 130, 0, 0, 'BROW', 'seb ker', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LBROW_sebk', 'BROW: seb ker', 140, 0, 0, 'BROW', 'seb ker', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BBROW_sebk', 'BROW: seb ker', 150, 0, 0, 'BROW', 'seb ker', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RBROW_AK', 'BROW: act ker', 160, 0, 0, 'BROW', 'actinic keratosis', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LBROW_AK', 'BROW: act ker', 170, 0, 0, 'BROW', 'actinic keratosis', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BBROW_AK', 'BROW: act ker', 180, 0, 0, 'BROW', 'actinic keratosis', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RBROW_bcc', 'BROW: BCC', 190, 0, 0, 'BROW', 'BCC', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LBROW_bcc', 'BROW: BCC', 200, 0, 0, 'BROW', 'BCC', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BBROW_bcc', 'BROW: BCC', 210, 0, 0, 'BROW', 'BCC', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RBROW_scc', 'BROW: SCC', 220, 0, 0, 'BROW', 'SCC', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LBROW_scc', 'BROW: SCC', 230, 0, 0, 'BROW', 'SCC', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BBROW_scc', 'BROW: SCC', 240, 0, 0, 'BROW', 'SCC', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RMRD_mrd0', 'MRD: 0', 250, 0, 0, 'MRD', '0', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LMRD_mrd0', 'MRD: 0', 260, 0, 0, 'MRD', '0', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BMRD_mrd0', 'MRD: 0', 270, 0, 0, 'MRD', '0', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RMRD_mrd1', 'MRD: 1', 280, 0, 0, 'MRD', '1', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LMRD_mrd1', 'MRD: 1', 290, 0, 0, 'MRD', '1', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BMRD_mrd1', 'MRD: 1', 300, 0, 0, 'MRD', '1', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RMRD_mrd2', 'MRD: 2', 310, 0, 0, 'MRD', '2', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LMRD_mrd2', 'MRD: 2', 320, 0, 0, 'MRD', '2', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BMRD_mrd2', 'MRD: 2', 330, 0, 0, 'MRD', '2', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RMRD_mrd3', 'MRD: 3', 340, 0, 0, 'MRD', '3', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LMRD_mrd3', 'MRD: 3', 350, 0, 0, 'MRD', '3', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BMRD_mrd3', 'MRD: 3', 360, 0, 0, 'MRD', '3', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RLF_17', 'LF: 17', 370, 0, 0, 'LF', '17', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LLF_17', 'LF: 17', 380, 0, 0, 'LF', '17', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BLF_17', 'LF: 17', 390, 0, 0, 'LF', '17', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RLF_15', 'LF: 15', 400, 0, 0, 'LF', '15', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LLF_15', 'LF: 15', 410, 0, 0, 'LF', '15', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BLF_15', 'LF: 15', 420, 0, 0, 'LF', '15', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RLF_13', 'LF: 13', 430, 0, 0, 'LF', '13', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LLF_13', 'LF: 13', 440, 0, 0, 'LF', '13', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BLF_13', 'LF: 13', 450, 0, 0, 'LF', '13', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RUL_clear', 'UL: clear field', 460, 0, 0, 'UL', '', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LUL_clear', 'UL: clear field', 470, 0, 0, 'UL', '', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BUL_clear', 'UL: clear field', 480, 0, 0, 'UL', '', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RUL_norm', 'UL: normal', 490, 0, 0, 'UL', 'normal lids and lashes', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LUL_norm', 'UL: normal', 500, 0, 0, 'UL', 'normal lids and lashes', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BUL_norm', 'UL: normal', 510, 0, 0, 'UL', 'normal lids and lashes', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RUL_der', 'UL: dermatochalasis', 520, 0, 0, 'UL', 'dermatochalasis', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LUL_der', 'UL: dermatochalasis', 530, 0, 0, 'UL', 'dermatochalasis', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BUL_der', 'UL: dermatochalasis', 540, 0, 0, 'UL', 'dermatochalasis', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RLL_der', 'LL: dermatochalasis', 550, 0, 0, 'LL', 'dermatochalasis', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LLL_der', 'LL: dermatochalasis', 560, 0, 0, 'LL', 'dermatochalasis', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BLL_der', 'LL: dermatochalasis', 570, 0, 0, 'LL', 'dermatochalasis', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RUL_pto2', 'UL: 2mm ptosis', 580, 0, 0, 'UL', '2mm ptosis', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LUL_pto2', 'UL: 2mm ptosis', 590, 0, 0, 'UL', '2mm ptosis', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BUL_pto2', 'UL: 2mm ptosis', 600, 0, 0, 'UL', '2mm ptosis', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RUL_pto3', 'UL: 3mm ptosis', 610, 0, 0, 'UL', '3mm ptosis', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LUL_pto3', 'UL: 3mm ptosis', 620, 0, 0, 'UL', '3mm ptosis', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BUL_pto3', 'UL: 3mm ptosis', 630, 0, 0, 'UL', '3mm ptosis', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RUL_lesion', 'UL: lesion', 640, 0, 0, 'UL', 'lesion', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LUL_lesion', 'UL: lesion', 650, 0, 0, 'UL', 'lesion', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BUL_lesion', 'UL: lesion', 660, 0, 0, 'UL', 'lesion', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RUL_chalazion', 'UL: chalazion', 670, 0, 0, 'UL', 'chalazion', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LUL_chalazion', 'UL: chalazion', 680, 0, 0, 'UL', 'chalazion', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BUL_chalazion', 'UL: chalazion', 690, 0, 0, 'UL', 'chalazion', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RUL_stye', 'UL: stye', 700, 0, 0, 'UL', 'stye', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LUL_stye', 'UL: stye', 710, 0, 0, 'UL', 'stye', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BUL_stye', 'UL: stye', 720, 0, 0, 'UL', 'stye', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RMCT_les', 'MCT: lesion', 730, 0, 0, 'MCT', 'lesion', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LMCT_les', 'MCT: lesion', 740, 0, 0, 'MCT', 'lesion', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BMCT_les', 'MCT: lesion', 750, 0, 0, 'MCT', 'lesion', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RMCT_NLDa', 'MCT: NLDO, acute', 760, 0, 0, 'MCT', 'NLDO, acute', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LMCT_NLDa', 'MCT: NLDO, acute', 770, 0, 0, 'MCT', 'NLDO, acute', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BMCT_NLDa', 'MCT: NLDO, acute', 780, 0, 0, 'MCT', 'NLDO, acute', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RMCT_NLDc', 'MCT: NLDO, chronic', 790, 0, 0, 'MCT', 'NLDO, chronic', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LMCT_NLDc', 'MCT: NLDO, chronic', 800, 0, 0, 'MCT', 'NLDO, chronic', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BMCT_NLDc', 'MCT: NLDO, chronic', 810, 0, 0, 'MCT', 'NLDO, chronic', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RLL_clear', 'LL: clear field', 820, 0, 0, 'LL', '', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LLL_clear', 'LL: clear field', 830, 0, 0, 'LL', '', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BLL_clear', 'LL: clear field', 840, 0, 0, 'LL', '', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RLL_norm', 'LL: good tone', 850, 0, 0, 'LL', 'good tone', '', 0, 0, 1, 'R'),
('Eye_QP_EXT_defaults', 'LLL_norm', 'LL: good tone', 860, 0, 0, 'LL', 'good tone', '', 0, 0, 1, 'L'),
('Eye_QP_EXT_defaults', 'BLL_norm', 'LL: good tone', 870, 0, 0, 'LL', 'good tone', '', 0, 0, 1, 'B'),
('Eye_QP_EXT_defaults', 'RLL_ect', 'LL: ectropion', 880, 0, 0, 'LL', 'ectropion', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LLL_ect', 'LL: ectropion', 890, 0, 0, 'LL', 'ectropion', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BLL_ect', 'LL: ectropion', 900, 0, 0, 'LL', 'ectropion', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RLL_ent', 'LL: entropion', 910, 0, 0, 'LL', 'entropion', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LLL_ent', 'LL: entropion', 920, 0, 0, 'LL', 'entropion', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BLL_ent', 'LL: entropion', 930, 0, 0, 'LL', 'entropion', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RLL_trich', 'LL: trichiasis', 940, 0, 0, 'LL', 'trichiasis', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LLL_trich', 'LL: trichiasis', 950, 0, 0, 'LL', 'trichiasis', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BLL_trich', 'LL: trichiasis', 960, 0, 0, 'LL', 'trichiasis', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RLL_lesion', 'LL: lesion', 970, 0, 0, 'LL', 'lesion', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LLL_lesion', 'LL: lesion', 980, 0, 0, 'LL', 'lesion', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BLL_lesion', 'LL: lesion', 990, 0, 0, 'LL', 'lesion', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RLL_fat', 'LL: fat prolapse', 1010, 0, 0, 'LL', 'fat prolapse', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LLL_fat', 'LL: fat prolapse', 1020, 0, 0, 'LL', 'fat prolapse', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BLL_fat', 'LL: fat prolapse', 1030, 0, 0, 'LL', 'fat prolapse', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RLL_pi', 'LL: erythema', 1040, 0, 0, 'LL', 'erythema', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LLL_pi', 'LL: erythema', 1050, 0, 0, 'LL', 'erythema', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BLL_pi', 'LL: erythema', 1060, 0, 0, 'LL', 'erythema', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RLL_nev', 'LL: ecchymosis', 1070, 0, 0, 'LL', 'ecchymosis', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LLL_nev', 'LL: ecchymosis', 1080, 0, 0, 'LL', 'ecchymosis', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BLL_nev', 'LL: ecchymosis', 1090, 0, 0, 'LL', 'ecchymosis', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RLL_chalazion', 'LL: chalazion', 1100, 0, 0, 'LL', 'chalazion', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'BLL_chalazion', 'LL: chalazion', 1110, 0, 0, 'LL', 'chalazion', '', 0, 0, 0, 'B'),
('Eye_QP_EXT_defaults', 'RLL_stye', 'LL: stye', 1120, 0, 0, 'LL', 'stye', '', 0, 0, 0, 'R'),
('Eye_QP_EXT_defaults', 'LLL_stye', 'LL: stye', 1130, 0, 0, 'LL', 'stye', '', 0, 0, 0, 'L'),
('Eye_QP_EXT_defaults', 'BLL_stye', 'LL: stye', 1140, 0, 0, 'LL', 'stye', '', 0, 0, 0, 'B');
#EndIf

#IfNotRow2D list_options list_id lists option_id Eye_QP_RETINA_defaults
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists' ,'Eye_QP_RETINA_defaults', 'Eye QP List RETINA for New Providers', 1, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'ODCUP_0', 'cup: clear field', 10, 0, 0, 'CUP', '', '', 0, 0, 1, 'OD'),
('Eye_QP_RETINA_defaults', 'OSCUP_0', 'cup: clear field', 20, 0, 0, 'CUP', '', '', 0, 0, 1, 'OS'),
('Eye_QP_RETINA_defaults', 'OUCUP_0', 'cup: clear field', 30, 0, 0, 'CUP', '', '', 0, 0, 1, 'OU'),
('Eye_QP_RETINA_defaults', 'ODCUP_1', 'cup: 0.1', 40, 0, 0, 'CUP', '0.1', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSCUP_1', 'cup: 0.1', 50, 0, 0, 'CUP', '0.1', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUCUP_1', 'cup: 0.1', 60, 0, 0, 'CUP', '0.1', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODCUP_3', 'cup: 0.3', 70, 0, 0, 'CUP', '0.3', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSCUP_3', 'cup: 0.3', 80, 0, 0, 'CUP', '0.3', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'ODCUP_5', 'cup: 0.5', 90, 0, 0, 'CUP', '0.5', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OUCUP_3', 'cup: 0.3', 100, 0, 0, 'CUP', '0.3', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'OSCUP_5', 'cup: 0.5', 110, 0, 0, 'CUP', '0.5', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUCUP_5', 'cup: 0.5', 120, 0, 0, 'CUP', '0.5', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODCUP_8', 'cup: 0.8', 130, 0, 0, 'CUP', '0.8', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSCUP_8', 'cup: 0.8', 140, 0, 0, 'CUP', '0.8', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUCUP_8', 'cup: 0.8', 150, 0, 0, 'CUP', '0.8', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODCUP_95', 'cup: 0.95', 160, 0, 0, 'CUP', '0.95', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSCUP_95', 'cup: 0.95', 170, 0, 0, 'CUP', '0.95', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUCUP_95', 'cup: 0.95', 180, 0, 0, 'CUP', '0.95', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODCUP_V', 'cup: V (vert)', 190, 0, 0, 'CUP', 'V', '', 0, 0, 2, 'OD'),
('Eye_QP_RETINA_defaults', 'OSCUP_V', 'cup: V (vert)', 200, 0, 0, 'CUP', 'V', '', 0, 0, 2, 'OS'),
('Eye_QP_RETINA_defaults', 'OUCUP_V', 'cup: V (vert)', 210, 0, 0, 'CUP', 'V', '', 0, 0, 2, 'OU'),
('Eye_QP_RETINA_defaults', 'ODCUP_x', 'cup: x (times)', 220, 0, 0, 'CUP', 'x', '', 0, 0, 2, 'OD'),
('Eye_QP_RETINA_defaults', 'OSCUP_x', 'cup: x (times)', 230, 0, 0, 'CUP', 'x', '', 0, 0, 2, 'OS'),
('Eye_QP_RETINA_defaults', 'OUCUP_x', 'cup: x (times)', 240, 0, 0, 'CUP', 'x', '', 0, 0, 2, 'OU'),
('Eye_QP_RETINA_defaults', 'ODCUP_H', 'cup: H (horiz)', 250, 0, 0, 'CUP', 'H', '', 0, 0, 2, 'OD'),
('Eye_QP_RETINA_defaults', 'OSCUP_H', 'cup: H (horiz)', 260, 0, 0, 'CUP', 'H', '', 0, 0, 2, 'OS'),
('Eye_QP_RETINA_defaults', 'OUCUP_H', 'cup: H (horiz)', 270, 0, 0, 'CUP', 'H', '', 0, 0, 2, 'OU'),
('Eye_QP_RETINA_defaults', 'ODCUP_notch', 'cup: notch', 280, 0, 0, 'CUP', 'notch', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSCUP_notch', 'cup: notch', 290, 0, 0, 'CUP', 'notch', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUCUP_notch', 'cup: notch', 300, 0, 0, 'CUP', 'notch', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODDISC_0', 'd: clear field', 310, 0, 0, 'DISC', '', '', 0, 0, 1, 'OD'),
('Eye_QP_RETINA_defaults', 'OSDISC_0', 'd: clear field', 320, 0, 0, 'DISC', '', '', 0, 0, 1, 'OS'),
('Eye_QP_RETINA_defaults', 'OUDISC_0', 'd: clear field', 330, 0, 0, 'DISC', '', '', 0, 0, 1, 'OU'),
('Eye_QP_RETINA_defaults', 'ODDISC_risk', 'd: at risk', 340, 0, 0, 'DISC', 'disc at risk', '', 0, 0, 1, 'OD'),
('Eye_QP_RETINA_defaults', 'OSDISC_risk', 'd: at risk', 350, 0, 0, 'DISC', 'disk at risk', '', 0, 0, 1, 'OS'),
('Eye_QP_RETINA_defaults', 'OUDISC_risk', 'd: at risk', 360, 0, 0, 'DISC', 'disk at risk', '', 0, 0, 1, 'OU'),
('Eye_QP_RETINA_defaults', 'ODDISC_pal', 'd: pallor', 370, 0, 0, 'DISC', 'pallor', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSCUP_pal', 'd: pallor', 380, 0, 0, 'DISC', 'pallor', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUDISC_pal', 'd: pallor', 390, 0, 0, 'DISC', 'pallor', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODDISC_NVD', 'd: NVD', 400, 0, 0, 'DISC', 'NVD', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSDISC_NVD', 'd: NVD', 410, 0, 0, 'DISC', 'NVD', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUDISC_NVD', 'd: NVD', 420, 0, 0, 'DISC', 'NVD', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODDISC_edema1', 'd: gr I edema', 430, 0, 0, 'DISC', 'Grade I papilledema', '', 0, 0, 1, 'OD'),
('Eye_QP_RETINA_defaults', 'OSDISC_edema1', 'd: gr I edema', 440, 0, 0, 'DISC', 'Grade I papilledema', '', 0, 0, 1, 'OS'),
('Eye_QP_RETINA_defaults', 'OUDISC_edema1', 'd: gr I edema', 450, 0, 0, 'DISC', 'Grade I papilledema', '', 0, 0, 1, 'OU'),
('Eye_QP_RETINA_defaults', 'ODDISC_edema2', 'd: gr III edema', 460, 0, 0, 'DISC', 'Grade III papilledema', '', 0, 0, 1, 'OD'),
('Eye_QP_RETINA_defaults', 'OSDISC_edema2', 'd: gr III edema', 470, 0, 0, 'DISC', 'Grade III papilledema', '', 0, 0, 1, 'OS'),
('Eye_QP_RETINA_defaults', 'OUDISC_edema2', 'd: gr III edema', 480, 0, 0, 'DISC', 'Grade III papilledema', '', 0, 0, 1, 'OU'),
('Eye_QP_RETINA_defaults', 'ODDISC_edemaf', 'd: gr V edema', 490, 0, 0, 'DISC', 'Grade V papilledema', '', 0, 0, 1, 'OD'),
('Eye_QP_RETINA_defaults', 'OSDISC_edemaf', 'd: gr V edema', 500, 0, 0, 'DISC', 'Grade V papilledema', '', 0, 0, 1, 'OS'),
('Eye_QP_RETINA_defaults', 'OUDISC_edemaf', 'd: gr V edema', 510, 0, 0, 'DISC', 'Grade V papilledema', '', 0, 0, 1, 'OU'),
('Eye_QP_RETINA_defaults', 'ODMAC_0', 'm: clear field', 520, 0, 0, 'MACULA', '', '', 0, 0, 1, 'OD'),
('Eye_QP_RETINA_defaults', 'OSMAC_0', 'm: clear field', 530, 0, 0, 'MACULA', '', '', 0, 0, 1, 'OS'),
('Eye_QP_RETINA_defaults', 'OUMAC_0', 'm: clear field', 540, 0, 0, 'MACULA', '', '', 0, 0, 1, 'OU'),
('Eye_QP_RETINA_defaults', 'ODMAC_hd', 'm: hard drusen', 550, 0, 0, 'MACULA', 'hard drusen', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSMAC_hd', 'm: hard drusen', 560, 0, 0, 'MACULA', 'hard drusen', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUMAC_hd', 'm: hard drusen', 570, 0, 0, 'MACULA', 'hard drusen', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODMAC_sd', 'm: soft drusen', 580, 0, 0, 'MACULA', 'soft drusen', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSMAC_sd', 'm: soft drusen', 590, 0, 0, 'MACULA', 'soft drusen', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUMAC_sd', 'm: soft drusen', 600, 0, 0, 'MACULA', 'soft drusen', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODMAC_PED', 'm: PED', 610, 0, 0, 'MACULA', 'PED', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSMAC_PED', 'm: PED', 620, 0, 0, 'MACULA', 'PED', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUMAC_PED', 'm: PED', 630, 0, 0, 'MACULA', 'PED', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODMAC_CSR', 'm: CSR', 640, 0, 0, 'MACULA', 'CSR', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSMAC_CSR', 'm: CSR', 650, 0, 0, 'MACULA', 'CSR', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUMAC_CSR', 'm: CSR', 660, 0, 0, 'MACULA', 'CSR', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODVESSELS_cl', 'v: clear field', 670, 0, 0, 'VESSELS', '', '', 0, 0, 1, 'OD'),
('Eye_QP_RETINA_defaults', 'OSVESSELS_cl', 'v: clear field', 680, 0, 0, 'VESSELS', '', '', 0, 0, 1, 'OS'),
('Eye_QP_RETINA_defaults', 'OUVESSELS_cl', 'v: clear field', 690, 0, 0, 'VESSELS', '', '', 0, 0, 1, 'OU'),
('Eye_QP_RETINA_defaults', 'ODVESSELS_12', 'v: 1:2', 700, 0, 0, 'VESSELS', '1:2', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSVESSELS_12', 'v: 1:2', 710, 0, 0, 'VESSELS', '1:2', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUVESSELS_12', 'v: 1:2', 720, 0, 0, 'VESSELS', '1:2', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODVESSELS_BDR', 'v: BDR', 730, 0, 0, 'VESSELS', 'BDR', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSVESSELS_BDR', 'v: BDR', 740, 0, 0, 'VESSELS', 'BDR', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUVESSELS_BDR', 'v: BDR', 750, 0, 0, 'VESSELS', 'BDR', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODVESSELS_PDR', 'v: PDR', 760, 0, 0, 'VESSELS', 'PDR', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSVESSELS_PDR', 'v: PDR', 770, 0, 0, 'VESSELS', 'PDR', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUVESSELS_PDR', 'v: PDR', 780, 0, 0, 'VESSELS', 'PDR', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODVESSELS_BRVO', 'v: BRVO', 790, 0, 0, 'VESSELS', 'BRVO', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSVESSELS_BRVO', 'v: BRVO', 800, 0, 0, 'VESSELS', 'BRVO', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUVESSELS_BRVO', 'v: BRVO', 810, 0, 0, 'VESSELS', 'BRVO', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODVESSELS_CRVO', 'v: CRVO', 820, 0, 0, 'VESSELS', 'CRVO', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSVESSELS_CRVO', 'v: CRVO', 830, 0, 0, 'VESSELS', 'CRVO', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUVESSELS_CRVO', 'v: CRVO', 840, 0, 0, 'VESSELS', 'CRVO', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODVESSELS_BRAO', 'v: BRAO', 850, 0, 0, 'VESSELS', 'BRAO', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSVESSELS_BRAO', 'v: BRAO', 860, 0, 0, 'VESSELS', 'BRAO', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUVESSELS_BRAO', 'v: BRAO', 870, 0, 0, 'VESSELS', 'BRAO', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODVESSELS_CRAO', 'v: CRAO', 880, 0, 0, 'VESSELS', 'CRAO', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSVESSELS_CRAO', 'v: CRAO', 890, 0, 0, 'VESSELS', 'CRAO', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUVESSELS_CRAO', 'v: CRAO', 900, 0, 0, 'VESSELS', 'CRAO', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODPERIPH_0', 'p: clear field', 910, 0, 0, 'PERIPH', '', '', 0, 0, 1, 'OD'),
('Eye_QP_RETINA_defaults', 'OSPERIPH_0', 'p: clear field', 920, 0, 0, 'PERIPH', '', '', 0, 0, 1, 'OS'),
('Eye_QP_RETINA_defaults', 'OUPERIPH_0', 'p: clear field', 930, 0, 0, 'PERIPH', '', '', 0, 0, 1, 'OU'),
('Eye_QP_RETINA_defaults', 'ODPERIPH_float', 'p: floater', 940, 0, 0, 'PERIPH', 'vitreous floater', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSPERIPH_float', 'p: floater', 950, 0, 0, 'PERIPH', 'vitreous floater', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUPERIPH_float', 'p: floater', 960, 0, 0, 'PERIPH', 'vitreous floater', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODPERIPH_pvd', 'p: PVD', 970, 0, 0, 'PERIPH', 'PVD', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSPERIPH_pvd', 'p: PVD', 980, 0, 0, 'PERIPH', 'PVD', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUPERIPH_pvd', 'p: PVD', 990, 0, 0, 'PERIPH', 'PVD', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODPERIPH_vh', 'p: vit hem', 1000, 0, 0, 'PERIPH', 'vitreous hemorrhage', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSPERIPH_vh', 'p: vit hem', 1010, 0, 0, 'PERIPH', 'vitreous hemorrhage', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUPERIPH_vh', 'p: vit hem', 1020, 0, 0, 'PERIPH', 'vitreous hemorrhage', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODPERIPH_tear', 'p: retinal tear', 1030, 0, 0, 'PERIPH', 'retinal tear', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSPERIPH_tear', 'p: retinal tear', 1040, 0, 0, 'PERIPH', 'retinal tear', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUPERIPH_tear', 'p: retinal tear', 1050, 0, 0, 'PERIPH', 'retinal tear', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODPERIPH_schisis', 'p: retinoschisis', 1060, 0, 0, 'PERIPH', 'retinoschisis', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSPERIPH_schisis', 'p: retinoschisis', 1070, 0, 0, 'PERIPH', 'retinoschisis', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUPERIPH_schisis', 'p: retinoschisis', 1080, 0, 0, 'PERIPH', 'retinoschisis', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODPERIPH_NVE', 'p: NVE', 1090, 0, 0, 'PERIPH', 'NVE', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSPERIPH_NVE', 'p: NVE', 1100, 0, 0, 'PERIPH', 'NVE', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUPERIPH_NVE', 'p: NVE', 1110, 0, 0, 'PERIPH', 'NVE', '', 0, 0, 0, 'OU'),
('Eye_QP_RETINA_defaults', 'ODPERIPH_RD', 'p: RD', 1120, 0, 0, 'PERIPH', 'RD', '', 0, 0, 0, 'OD'),
('Eye_QP_RETINA_defaults', 'OSPERIPH_RD', 'p: RD', 1130, 0, 0, 'PERIPH', 'RD', '', 0, 0, 0, 'OS'),
('Eye_QP_RETINA_defaults', 'OUPERIPH_RD', 'p: RD', 1140, 0, 0, 'PERIPH', 'RD', '', 0, 0, 0, 'OU');
#EndIf

#IfNotRow2D list_options list_id lists option_id Eye_todo_done_defaults
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists' ,'Eye_todo_done_defaults', 'Eye Orders Defaults', 1, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_todo_done_defaults', 'Ascan', 'A scan/IOL calc', 430, 0, 0, '', '', 'CPT4:76519', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'Bscan', 'B scan', 440, 0, 0, '', '', 'CPT4:76512', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'dilation', 'Dilated Exam', 90, 0, 0, '', 'Full dilated exam', '', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'ExtPhoto', 'Ext Photos', 200, 0, 0, '', '', 'CPT4:92285', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'FundusPhoto', 'Retina Photo', 210, 0, 0, '', '', 'CPT4:92250', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'Gonio', 'Gonio', 410, 0, 0, '', '', 'CPT4:92020', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'IOP', 'IOP', 80, 0, 0, '', 'Intraocular pressure check', '', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'irrigate', 'Irrigate', 450, 0, 0, '', '', 'CPT4:68840', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'ISHI', 'Color Plates', 320, 0, 0, '', '', '', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'lesion', 'Surg: office/15 min', 470, 0, 0, '', '', 'CPT4:67840', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'OCTDisc', 'OCT Disc', 300, 0, 0, '', '', 'CPT4:92133', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'OCTRetina', 'OCT Retina', 310, 0, 0, '', '', 'CPT4:92134', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'Pachy', 'Pachymetry', 420, 0, 0, '', '', 'CPT4:76514', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'refraction', 'Refraction', 460, 0, 0, '', '', 'CPT4:92015', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'RTC1month', 'RTC 1 month', 20, 0, 0, '', 'F/U 1 month', '', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'RTC1WK', 'RTC 1 week', 10, 0, 0, '', 'F/U 1 week', '', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'RTC1yr', 'RTC 1 year', 40, 0, 0, '', 'Recheck 1 year', '', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'RTC2yr', 'RTC  2 years', 50, 0, 0, '', 'Recheck 2 years', '', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'RTC3wks', 'RTC 3 weeks', 30, 0, 0, '', '', '', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'Topo', 'Corneal Topo', 400, 0, 0, '', '', 'CPT4:92025', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'VFBLEPH', 'VF BLEPH', 130, 0, 0, '', 'Taped and untaped VF, uppers only', 'CPT4:92083', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'VFC10', 'Visual Field C-10', 100, 0, 0, '', 'Central 10 red target VF', 'CPT4:92083', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'VFC24', 'VF C-24', 110, 0, 0, '', 'Central 24 VF', 'CPT4:92083', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'VFC30', 'VF C-30', 120, 0, 0, '', 'Central 30 VF', 'CPT4:92083', 0, 0, 1, ''),
('Eye_todo_done_defaults', 'Yag', 'Surg: YAG RT/LT', 480, 0, 0, '', '', 'CPT4:66821', 0, 0, 1, '');
#EndIf

#IfNotRow2D list_options list_id lists option_id Eye_Defaults_for_GENERAL
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists' ,'Eye_Defaults_for_GENERAL', 'Eye Exam Default Values for New Providers', 1, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_Defaults_for_GENERAL', 'LBROW', 'no brow ptosis', 60, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'LLF', '17', 140, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'LLL', 'good tone', 40, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'LMCT', 'no masses', 80, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'LMRD', '+3', 120, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'LUL', 'normal lids and lashes', 20, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODAC', 'deep and quiet', 190, 0, 0, '', 'ANTSEG', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODAPD', '0', 280, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODCONJ', 'quiet', 160, 0, 0, '', 'ANTSEG', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODCORNEA', 'clear', 170, 0, 0, '', 'ANTSEG', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODCUP', '0.3', 450, 0, 0, '', 'RETINA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODDISC', 'pink', 430, 0, 0, '', 'RETINA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODIOPTARGET', '21', 530, 0, 0, '', 'GLAUCOMA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODIRIS', 'round', 230, 0, 0, '', 'ANTSEG', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODLENS', 'clear', 210, 0, 0, '', 'ANTSEG', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODMACULA', 'flat', 470, 0, 0, '', 'RETINA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODPERIPH', 'flat', 510, 0, 0, '', 'RETINA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODPUPILREACTIVITY', '+2', 270, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODPUPILSIZE1', '3', 250, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODPUPILSIZE2', '2', 260, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODVESSELS', '2:3', 490, 0, 0, '', 'RETINA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODVFCONFRONTATION1', '0', 330, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODVFCONFRONTATION2', '0', 340, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODVFCONFRONTATION3', '0', 350, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODVFCONFRONTATION4', '0', 360, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'ODVFCONFRONTATION5', '0', 370, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSAC', 'deep and quiet', 200, 0, 0, '', 'ANTSEG', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSAPD', '0', 320, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSCONJ', 'quiet', 150, 0, 0, '', 'ANTSEG', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSCORNEA', 'clear', 180, 0, 0, '', 'ANTSEG', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSCUP', '0.3', 460, 0, 0, '', 'RETINA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSDISC', 'pink', 440, 0, 0, '', 'RETINA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSIOPTARGET', '21', 540, 0, 0, '', 'GLAUCOMA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSIRIS', 'round', 240, 0, 0, '', 'ANTSEG', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSLENS', 'clear', 220, 0, 0, '', 'ANTSEG', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSMACULA', 'flat', 480, 0, 0, '', 'RETINA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSPERIPH', 'flat', 520, 0, 0, '', 'RETINA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSPUPILREACTIVITY', '+2', 310, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSPUPILSIZE1', '3', 290, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSPUPILSIZE2', '2', 300, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSVESSELS', '2:3', 500, 0, 0, '', 'RETINA', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSVFCONFRONTATION1', '0', 380, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSVFCONFRONTATION2', '0', 390, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSVFCONFRONTATION3', '0', 400, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSVFCONFRONTATION4', '0', 410, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'OSVFCONFRONTATION5', '0', 420, 0, 0, '', 'NEURO', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'RADNEXA', 'normal lacrimal gland and orbit', 90, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'RBROW', 'no brow ptosis', 50, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'RLF', '17', 130, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'RLL', 'good tone', 30, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'RMCT', 'no masses', 70, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'RMRD', '+3', 110, 0, 0, '', 'EXT', '', 0, 0, 0, ''),
('Eye_Defaults_for_GENERAL', 'RUL', 'normal lids and lashes', 10, 0, 0, '', 'EXT', '', 0, 0, 0, '');
#EndIf

#IfNotRow2D list_options list_id lists option_id Eye_Lens_Material
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists' ,'Eye_Lens_Material', 'Eye Lens Material', 1, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_Lens_Material', 'LM_CG', 'Crown Glass', 10, 0, 0, '', 'Excellent optics. Low cost. Downsides: heavy, breakable. Abbe Value: 59', '', 0, 0, 1, ''),
('Eye_Lens_Material', 'LM_CR', 'CR-39', 20, 0, 0, '', 'Excellent optics. Low cost. Downside: thickness. Abbe Value: 58', '', 0, 0, 1, ''),
('Eye_Lens_Material', 'LM_HI_PLASTICS_1', 'High-index Plastics (1.6 to 1.67)', 60, 0, 0, '', 'Thin and lightweight. Block 100 percent UV. Less costly than 1.70-1.74 high-index lenses.  Abbe: 36(1.6) - 32 (1.67)', '', 0, 0, 1, ''),
('Eye_Lens_Material', 'LM_HI_PLASTICS_2', 'High-index Plastics (1.7 to 1.74)', 70, 0, 0, '', 'The thinnest lenses available. Block 100 percent UV. Lightweight.  Abbe: 36(1.7) - 33(1.74)', '', 0, 0, 1, ''),
('Eye_Lens_Material', 'LM_POLY', 'Polycarbonate', 40, 0, 0, '', 'Superior impact resistance. Blocks 100 percent UV. Lighter than high-index plastic lenses.  Abbe: 30', '', 0, 0, 1, ''),
('Eye_Lens_Material', 'LM_TRIBRID', 'Tribrid', 50, 0, 0, '', 'Thin and lightweight. Significantly more impact-resistant than CR-39 plastic and high-index plastic lenses (except polycarbonate and Trivex). Higher Abbe value than polycarbonate. Downside: Not yet available in a wide variety of lens designs.  Abbe: 41', '', 0, 0, 1, ''),
('Eye_Lens_Material', 'LM_TRIVEX', 'Trivex', 30, 0, 0, '', 'Superior impact resistance. Blocks 100 percent UV. Higher Abbe value than polycarbonate. Lightest lens material available. Abbe Value: 45', '', 0, 0, 1, '');
#EndIf

#IfNotRow2D list_options list_id lists option_id Eye_Lens_Treatments
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists' ,'Eye_Lens_Treatments', 'Eye Lens Treatments', 1, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_Lens_Treatments', 'LT_ARCOAT', 'Anti-reflective coating', 20, 0, 0, '', '', '', 0, 0, 1, ''),
('Eye_Lens_Treatments', 'LT_ASCRATCH', 'Anti-scratch coating', 10, 0, 0, '', '', '', 0, 0, 1, ''),
('Eye_Lens_Treatments', 'LT_UVBLOCK', 'UV-blocking treatment', 30, 0, 0, '', '', '', 0, 0, 1, ''),
('Eye_Lens_Treatments', 'LT_PHOTOGREY', 'Photochromic treatment', 40, 0, 0, '', '', '', 0, 0, 1, '');
#EndIf

#IfMissingColumn users suffix
ALTER TABLE `users` ADD `suffix` varchar(255) default NULL;
#EndIf

#IfNotRow2D list_options list_id page_validation option_id addrbook_edit#theform
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'addrbook_edit#theform', '/interface/usergroup/addrbook_edit.php', 110, '{}', 1);
#EndIf

#IfNotTable product_registration
CREATE TABLE `product_registration` (
  `registration_id` char(36) NOT NULL DEFAULT '',
  `email` varchar(255) NULL,
  `opt_out` TINYINT(1) NULL,
  PRIMARY KEY (`registration_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotRow2D list_options list_id Eye_Defaults_for_GENERAL option_id LADNEXA
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES ('Eye_Defaults_for_GENERAL', 'LADNEXA', 'normal lacrimal gland and orbit', 91, 0, 0, '', 'EXT', '', 0, 0, 0, '');
#EndIf

#IfMissingColumn log category
ALTER TABLE `log` ADD `category` varchar(255) default NULL;
#EndIf

#IfNotTable log_validator
CREATE TABLE `log_validator` (
  `log_id` bigint(20) NOT NULL,
  `log_checksum` longtext,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotRow2D list_options list_id note_type option_id Image Results
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`,`is_default`) VALUES ('note_type', 'Image Results', 'Image Results', 30, 0);
#EndIf

#IfMissingColumn insurance_companies inactive
ALTER TABLE `insurance_companies` ADD `inactive` INT(1) NOT NULL DEFAULT '0' ;
#EndIf

#IfNotRow2D list_options list_id lists option_id formdir_keys
INSERT INTO list_options (list_id, option_id, title,activity) VALUES ('lists','formdir_keys','Form Keys',1);
INSERT INTO list_options (list_id,option_id,title,seq,notes,activity) VALUES ('formdir_keys','newpatient','"tbl":"form_encounter"',10,'Patient encounter table has non-std name',1);
INSERT INTO list_options (list_id,option_id,title,seq,notes,activity) VALUES ('formdir_keys','procedure_order','"tbl":"procedure_order","id":"procedure_order_id"',20,'Lab order header table has non-std name and id',1);
INSERT INTO list_options (list_id,option_id,title,seq,notes,activity) VALUES ('formdir_keys','physical_exam','"id":"forms_id","limit":"*"',30,'Physical exam form table has non-std id and n records',1);
#EndIf

#IfMissingColumn form_misc_billing_options medicaid_referral_code
  ALTER TABLE form_misc_billing_options ADD COLUMN medicaid_referral_code varchar(2) default NULL;
#EndIf

#IfMissingColumn form_misc_billing_options epsdt_flag
  ALTER TABLE form_misc_billing_options ADD COLUMN epsdt_flag tinyint(1) default NULL;
#EndIf

#IfMissingColumn form_misc_billing_options provider_qualifier_code
  ALTER TABLE form_misc_billing_options ADD COLUMN provider_qualifier_code varchar(2) default NULL;
#EndIf

#IfMissingColumn form_misc_billing_options provider_id
  ALTER TABLE form_misc_billing_options ADD COLUMN provider_id int(11) default NULL;
#EndIf

#IfMissingColumn form_misc_billing_options icn_resubmission_number
  ALTER TABLE form_misc_billing_options ADD COLUMN icn_resubmission_number varchar(35) default NULL;
#EndIf

#IfNotRow2D list_options list_id lists option_id provider_qualifier_code
 INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','provider_qualifier_code','Provider Qualifier Code', 1,0);
 INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('provider_qualifier_code','dk','DK',10,0);
 INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('provider_qualifier_code','dn','DN',20,0);
#EndIf

#IfNotTable codes_history
CREATE TABLE `codes_history` (
  `log_id` bigint(20) NOT NULL auto_increment,
  `date` datetime,
  `code` varchar(25),
  `modifier` varchar(12),
  `active` tinyint(1),
  `diagnosis_reporting` tinyint(1),
  `financial_reporting` tinyint(1),
  `category` varchar(255),
  `code_type_name` varchar(255),
  `code_text` varchar(255),
  `code_text_short` varchar(24),
  `prices` text,
  `action_type` varchar(25),
  `update_by` varchar(255),
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn form_care_plan care_plan_type
ALTER TABLE form_care_plan ADD COLUMN care_plan_type VARCHAR(30) DEFAULT NULL;
#EndIf

#IfNotRow2D list_options list_id lists option_id Plan_of_Care_Type
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `activity`, `toggle_setting_1`, `toggle_setting_2`, `subtype`) VALUES('lists','Plan_of_Care_Type','Plan of Care Type','305','1','0','','','','1','0','0','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `activity`, `toggle_setting_1`, `toggle_setting_2`, `subtype`) VALUES('Plan_of_Care_Type','appointments','Appointments','4','0','0','','INT','','1','0','0','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `activity`, `toggle_setting_1`, `toggle_setting_2`, `subtype`) VALUES('Plan_of_Care_Type','instructions','Instructions','5','0','0','','INT','','1','0','0','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `activity`, `toggle_setting_1`, `toggle_setting_2`, `subtype`) VALUES('Plan_of_Care_Type','plan_of_care','Plan of Care','1','0','0','','INT','','1','0','0','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `activity`, `toggle_setting_1`, `toggle_setting_2`, `subtype`) VALUES('Plan_of_Care_Type','procedure','Procedure','3','0','0','','RQO','','1','0','0','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `activity`, `toggle_setting_1`, `toggle_setting_2`, `subtype`) VALUES('Plan_of_Care_Type','test_or_order','Test/Order','2','0','0','','RQO','','1','0','0','');
#EndIf
