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

--  #IfNotListReaction
--    Custom function for creating Reaction List

--  #IfNotListOccupation
--    Custom function for creating Occupation List

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

#IfNotRow code_types ct_key LOINC
DROP TABLE IF EXISTS `temp_table_one`;
CREATE TABLE `temp_table_one` (`id` int(11) NOT NULL DEFAULT '0',`seq` int(11) NOT NULL DEFAULT '0') ENGINE=MyISAM;
INSERT INTO `temp_table_one` (`id`, `seq`) VALUES (
  IF(((SELECT MAX(`ct_id` ) FROM `code_types`) >= 100), ((SELECT MAX(`ct_id` ) FROM `code_types`) + 1), 100),
  IF(((SELECT MAX(`ct_seq`) FROM `code_types`) >= 100), ((SELECT MAX(`ct_seq`) FROM `code_types`) + 1), 100));
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag, ct_active, ct_label, ct_external, ct_claim, ct_proc, ct_term, ct_problem ) VALUES ('LOINC', (SELECT MAX(`id`) FROM `temp_table_one`), (SELECT MAX(`seq`) FROM `temp_table_one`), 0, '', 0, 0, 1, 0, 1, 'LOINC', 0, 0, 0, 0, 0);
DROP TABLE `temp_table_one`;
#EndIf

#IfNotRow code_types ct_key PHIN Questions
DROP TABLE IF EXISTS `temp_table_one`;
CREATE TABLE `temp_table_one` (`id` int(11) NOT NULL DEFAULT '0',`seq` int(11) NOT NULL DEFAULT '0') ENGINE=MyISAM;
INSERT INTO `temp_table_one` (`id`, `seq`) VALUES (
  IF(((SELECT MAX(`ct_id` ) FROM `code_types`) >= 100), ((SELECT MAX(`ct_id` ) FROM `code_types`) + 1), 100),
  IF(((SELECT MAX(`ct_seq`) FROM `code_types`) >= 100), ((SELECT MAX(`ct_seq`) FROM `code_types`) + 1), 100));
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag, ct_active, ct_label, ct_external, ct_claim, ct_proc, ct_term, ct_problem ) VALUES ('PHIN Questions', (SELECT MAX(`id`) FROM `temp_table_one`), (SELECT MAX(`seq`) FROM `temp_table_one`), 0, '', 0, 0, 1, 0, 1, 'PHIN Questions', 0, 0, 0, 0, 0);
DROP TABLE `temp_table_one`;
#EndIf

#IfMissingColumn list_options activity
ALTER TABLE `list_options` ADD COLUMN `activity` TINYINT DEFAULT 1 NOT NULL;
#EndIf

#IfNotTable ccda_components
CREATE TABLE ccda_components (
  ccda_components_id int(11) NOT NULL AUTO_INCREMENT,
  ccda_components_field varchar(100) DEFAULT NULL,
  ccda_components_name varchar(100) DEFAULT NULL,
  PRIMARY KEY (ccda_components_id)
) ENGINE=InnoDB AUTO_INCREMENT=10 ;
insert into ccda_components (ccda_components_id, ccda_components_field, ccda_components_name) values('1','progress_note','Progress Notes');
insert into ccda_components (ccda_components_id, ccda_components_field, ccda_components_name) values('2','consultation_note','Consultation Note');
insert into ccda_components (ccda_components_id, ccda_components_field, ccda_components_name) values('3','continuity_care_document','Continuity Care Document');
insert into ccda_components (ccda_components_id, ccda_components_field, ccda_components_name) values('4','diagnostic_image_reporting','Diagnostic Image Reporting');
insert into ccda_components (ccda_components_id, ccda_components_field, ccda_components_name) values('5','discharge_summary','Discharge Summary');
insert into ccda_components (ccda_components_id, ccda_components_field, ccda_components_name) values('6','history_physical_note','History and Physical Note');
insert into ccda_components (ccda_components_id, ccda_components_field, ccda_components_name) values('7','operative_note','Operative Note');
insert into ccda_components (ccda_components_id, ccda_components_field, ccda_components_name) values('8','procedure_note','Procedure Note');
insert into ccda_components (ccda_components_id, ccda_components_field, ccda_components_name) values('9','unstructured_document','Unstructured Document');
#EndIf

#IfNotTable ccda_sections
CREATE TABLE ccda_sections (
  ccda_sections_id int(11) NOT NULL AUTO_INCREMENT,
  ccda_components_id int(11) DEFAULT NULL,
  ccda_sections_field varchar(100) DEFAULT NULL,
  ccda_sections_name varchar(100) DEFAULT NULL,
  ccda_sections_req_mapping tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (ccda_sections_id)
) ENGINE=InnoDB AUTO_INCREMENT=46;
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('1','1','assessment_plan','Assessment and Plan','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('2','2','assessment_plan','Assessment and Plan','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('3','2','history_of_present_illness','History of Present Illness','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('4','2','physical_exam','Physical Exam','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('5','2','reason_of_visit','Reason for Referral/Reason for Visit','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('6','3','allergies','Allergies','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('7','3','medications','Medications','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('8','3','problem_list','Problem List','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('9','3','procedures','Procedures','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('10','3','results','Results','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('11','4','report','Report','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('12','5','allergies','Allergies','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('13','5','hospital_course','Hospital Course','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('14','5','hospital_discharge_diagnosis','Hospital Discharge Diagnosis','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('15','5','hospital_discharge_medications','Hospital Discharge Medications','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('16','5','plan_of_care','Plan of Care','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('17','6','allergies','Allergies','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('19','6','chief_complaint','Chief Complaint / Reason for Visit','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('21','6','family_history','Family History','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('22','6','general_status','General Status','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('23','6','hpi_past_med','History of Past Illness (Past Medical History)','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('24','6','hpi','History of Present Illness','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('25','6','medications','Medications','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('26','6','physical_exam','Physical Exam','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('28','6','results','Results','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('29','6','review_of_systems','Review of Systems','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('30','6','social_history','Social History','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('31','6','vital_signs','Vital Signs','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('32','7','anesthesia','Anesthesia','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('33','7','complications','Complications','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('34','7','post_operative_diagnosis','Post Operative Diagnosis','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('35','7','pre_operative_diagnosis','Pre Operative Diagnosis','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('36','7','procedure_estimated_blood_loss','Procedure Estimated Blood Loss','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('37','7','procedure_findings','Procedure Findings','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('38','7','procedure_specimens_taken','Procedure Specimens Taken','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('39','7','procedure_description','Procedure Description','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('40','8','assessment_plan','Assessment and Plan','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('41','8','complications','Complications','1');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('42','8','postprocedure_diagnosis','Postprocedure Diagnosis','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('43','8','procedure_description','Procedure Description','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('44','8','procedure_indications','Procedure Indications','0');
insert into ccda_sections (ccda_sections_id, ccda_components_id, ccda_sections_field, ccda_sections_name, ccda_sections_req_mapping) values('45','9','unstructured_doc','Document','0');
#EndIf

#IfNotTable ccda_table_mapping
CREATE TABLE ccda_table_mapping (
  id int(11) NOT NULL AUTO_INCREMENT,
  ccda_component varchar(100) DEFAULT NULL,
  ccda_component_section varchar(100) DEFAULT NULL,
  form_dir varchar(100) DEFAULT NULL,
  form_type smallint(6) DEFAULT NULL,
  form_table varchar(100) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  deleted tinyint(4) NOT NULL DEFAULT '0',
  timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;
#EndIf

#IfNotTable ccda_field_mapping
CREATE TABLE ccda_field_mapping (
  id int(11) NOT NULL AUTO_INCREMENT,
  table_id int(11) DEFAULT NULL,
  ccda_field varchar(100) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;
#EndIf

#IfNotTable ccda
CREATE TABLE ccda (
  id INT(11) NOT NULL AUTO_INCREMENT,
  pid BIGINT(20) DEFAULT NULL,
  encounter BIGINT(20) DEFAULT NULL,
  ccda_data MEDIUMTEXT,
  time VARCHAR(50) DEFAULT NULL,
  status SMALLINT(6) DEFAULT NULL,
  updated_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  user_id VARCHAR(50) null,
  couch_docid VARCHAR(100) NULL,
  couch_revid VARCHAR(100) NULL,
  `view` tinyint(4) NOT NULL DEFAULT '0',
  `transfer` tinyint(4) NOT NULL DEFAULT '0',
  `type` VARCHAR(15),
  `emr_transfer` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY unique_key (pid,encounter,time)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;
#EndIf

#IfNotRow2D list_options list_id lists option_id religious_affiliation
INSERT INTO list_options(list_id,option_id,title) VALUES ('lists','religious_affiliation','Religious Affiliation');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','adventist','1001','Adventist','5');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','african_religions','1002','African Religions','15');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','afro-caribbean_religions','1003','Afro-Caribbean Religions','25');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','agnosticism','1004','Agnosticism','35');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','anglican','1005','Anglican','45');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','animism','1006','Animism','55');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','assembly_of_god','1061','Assembly of God','65');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','atheism','1007','Atheism','75');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','babi_bahai_faiths','1008','Babi & Baha\'I faiths','85');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','baptist','1009','Baptist','95');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','bon','1010','Bon','105');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','brethren','1062','Brethren','115');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','cao_dai','1011','Cao Dai','125');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','celticism','1012','Celticism','135');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','christiannoncatholicnonspecifc','1013','Christian (non-Catholic, non-specific)','145');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','christian_scientist','1063','Christian Scientist','155');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','church_of_christ','1064','Church of Christ','165');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','church_of_god','1065','Church of God','175');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','confucianism','1014','Confucianism','185');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','congregational','1066','Congregational','195');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','cyberculture_religions','1015','Cyberculture Religions','205');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','disciples_of_christ','1067','Disciples of Christ','215');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','divination','1016','Divination','225');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','eastern_orthodox','1068','Eastern Orthodox','235');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','episcopalian','1069','Episcopalian','245');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','evangelical_covenant','1070','Evangelical Covenant','255');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','fourth_way','1017','Fourth Way','265');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','free_daism','1018','Free Daism','275');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','friends','1071','Friends','285');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','full_gospel','1072','Full Gospel','295');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','gnosis','1019','Gnosis','305');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','hinduism','1020','Hinduism','315');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','humanism','1021','Humanism','325');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','independent','1022','Independent','335');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','islam','1023','Islam','345');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','jainism','1024','Jainism','355');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','jehovahs_witnesses','1025','Jehovah\'s Witnesses','365');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','judaism','1026','Judaism','375');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','latter_day_saints','1027','Latter Day Saints','385');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','lutheran','1028','Lutheran','395');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','mahayana','1029','Mahayana','405');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','meditation','1030','Meditation','415');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','messianic_judaism','1031','Messianic Judaism','425');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','methodist','1073','Methodist','435');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','mitraism','1032','Mitraism','445');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','native_american','1074','Native American','455');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','nazarene','1075','Nazarene','465');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','new_age','1033','New Age','475');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','non-roman_catholic','1034','non-Roman Catholic','485');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','occult','1035','Occult','495');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','orthodox','1036','Orthodox','505');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','paganism','1037','Paganism','515');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','pentecostal','1038','Pentecostal','525');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','presbyterian','1076','Presbyterian','535');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','process_the','1039','Process, The','545');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','protestant','1077','Protestant','555');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','protestant_no_denomination','1078','Protestant, No Denomination','565');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','reformed','1079','Reformed','575');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','reformed_presbyterian','1040','Reformed/Presbyterian','585');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','roman_catholic_church','1041','Roman Catholic Church','595');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','salvation_army','1080','Salvation Army','605');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','satanism','1042','Satanism','615');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','scientology','1043','Scientology','625');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','shamanism','1044','Shamanism','635');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','shiite_islam','1045','Shiite (Islam)','645');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','shinto','1046','Shinto','655');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','sikism','1047','Sikism','665');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','spiritualism','1048','Spiritualism','675');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','sunni_islam','1049','Sunni (Islam)','685');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','taoism','1050','Taoism','695');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','theravada','1051','Theravada','705');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','unitarian_universalist','1081','Unitarian Universalist','715');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','unitarian-universalism','1052','Unitarian-Universalism','725');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','united_church_of_christ','1082','United Church of Christ','735');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','universal_life_church','1053','Universal Life Church','745');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','vajrayana_tibetan','1054','Vajrayana (Tibetan)','755');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','veda','1055','Veda','765');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','voodoo','1056','Voodoo','775');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','wicca','1057','Wicca','785');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','yaohushua','1058','Yaohushua','795');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','zen_buddhism','1059','Zen Buddhism','805');
INSERT INTO list_options (list_id, option_id, notes,title, seq) VALUES ('religious_affiliation','zoroastrianism','1060','Zoroastrianism','815');

#EndIf

#IfNotRow2D list_options list_id lists option_id personal_relationship
INSERT INTO list_options(list_id,option_id,title) VALUES ('lists','personal_relationship','Relationship');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','ADOPT','Adopted Child','ADOPT','10');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','AUNT','Aunt','AUNT','20');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','CHILD','Child','CHILD','30');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','CHLDINLAW','Child in-law','CHLDINLAW','40');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','COUSN','Cousin','COUSN','50');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','DOMPART','Domestic Partner','DOMPART','60');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','FAMMEMB','Family Member','FAMMEMB','70');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','CHLDFOST','Foster Child','CHLDFOST','80');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','GRNDCHILD','Grandchild','GRNDCHILD','90');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','GPARNT','Grandparent','GPARNT','100');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','GRPRN','Grandparent','GRPRN','110');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','GGRPRN','Great Grandparent','GGRPRN','120');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','HSIB','Half-Sibling','HSIB','130');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','MAUNT','MaternalAunt','MAUNT','140');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','MCOUSN','MaternalCousin','MCOUSN','150');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','MGRPRN','MaternalGrandparent','MGRPRN','160');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','MGGRPRN','MaternalGreatgrandparent','MGGRPRN','170');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','MUNCLE','MaternalUncle','MUNCLE','180');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','NCHILD','Natural Child','NCHILD','190');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','NPRN','Natural Parent','NPRN','200');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','NSIB','Natural Sibling','NSIB','210');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','NBOR','Neighbor','NBOR','220');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','NIENEPH','Niece/Nephew','NIENEPH','230');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','PRN','Parent','PRN','240');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','PRNINLAW','parent in-law','PRNINLAW','250');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','PAUNT','PaternalAunt','PAUNT','260');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','PCOUSN','PaternalCousin','PCOUSN','270');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','PGRPRN','PaternalGrandparent','PGRPRN','280');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','PGGRPRN','PaternalGreatgrandparent','PGGRPRN','290');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','PUNCLE','PaternalUncle','PUNCLE','300');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','ROOM','Roommate','ROOM','310');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','SIB','Sibling','SIB','320');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','SIBINLAW','Sibling in-law','SIBINLAW','330');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','SIGOTHR','Significant Other','SIGOTHR','340');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','SPS','Spouse','SPS','350');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','STEP','Step Child','STEP','360');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','STPPRN','Step Parent','STPPRN','370');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','STPSIB','Step Sibling','STPSIB','380');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','UNCLE','Uncle','UNCLE','390');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('personal_relationship','FRND','Unrelated Friend','FRND','400');
#EndIf

#IfNotRow3D list_options list_id ethnicity option_id hisp_or_latin notes 2135-2
UPDATE `list_options` SET `notes` = '2135-2' WHERE `option_id` = 'hisp_or_latin' AND `list_id` = 'ethnicity';
#EndIf


#IfNotRow3D list_options list_id ethnicity option_id not_hisp_or_latin notes 2186-5
UPDATE `list_options` SET `notes` = '2186-5' WHERE `option_id` = 'not_hisp_or_latin' AND `list_id` = 'ethnicity';
#EndIf

#IfNotRow3D list_options list_id race option_id amer_ind_or_alaska_native notes 1002-5
UPDATE `list_options` SET `notes` = '1002-5' WHERE `option_id` = 'amer_ind_or_alaska_native' AND `list_id` = 'race';
#EndIf

#IfNotRow3D list_options list_id race option_id Asian notes 2028-9
UPDATE `list_options` SET `notes` = '2028-9' WHERE `option_id` = 'Asian' AND `list_id` = 'race';
#EndIf

#IfNotRow3D list_options list_id race option_id black_or_afri_amer notes 2054-5
UPDATE `list_options` SET `notes` = '2054-5' WHERE `option_id` = 'black_or_afri_amer' AND `list_id` = 'race';
#EndIf

#IfNotRow3D list_options list_id race option_id native_hawai_or_pac_island notes 2076-8
UPDATE `list_options` SET `notes` = '2076-8' WHERE `option_id` = 'native_hawai_or_pac_island' AND `list_id` = 'race';
#EndIf

#IfNotRow3D list_options list_id race option_id white notes 2106-3
UPDATE `list_options` SET `notes` = '2106-3' WHERE `option_id` = 'white' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id abenaki title Abenaki
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','abenaki','Abenaki','60', '0',' 1006-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id abenaki
UPDATE `list_options` SET `notes` = '1006-6' WHERE `option_id` = 'abenaki' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Abenaki
UPDATE `list_options` SET `notes` = '1006-6' WHERE `title` = 'Abenaki' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id absentee_shawnee title Absentee Shawnee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','absentee_shawnee','Absentee Shawnee','70', '0',' 1579-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id absentee_shawnee
UPDATE `list_options` SET `notes` = '1579-2' WHERE `option_id` = 'absentee_shawnee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Absentee Shawnee
UPDATE `list_options` SET `notes` = '1579-2' WHERE `title` = 'Absentee Shawnee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id acoma title Acoma
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','acoma','Acoma','80', '0',' 1490-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id acoma
UPDATE `list_options` SET `notes` = '1490-2' WHERE `option_id` = 'acoma' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Acoma
UPDATE `list_options` SET `notes` = '1490-2' WHERE `title` = 'Acoma' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id afghanistani title Afghanistani
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','afghanistani','Afghanistani','90', '0',' 2126-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id afghanistani
UPDATE `list_options` SET `notes` = '2126-1' WHERE `option_id` = 'afghanistani' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Afghanistani
UPDATE `list_options` SET `notes` = '2126-1' WHERE `title` = 'Afghanistani' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id african title African
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','african','African','100', '0',' 2060-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id african
UPDATE `list_options` SET `notes` = '2060-2' WHERE `option_id` = 'african' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title African
UPDATE `list_options` SET `notes` = '2060-2' WHERE `title` = 'African' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id african_american title African American
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','african_american','African American','110', '0',' 2058-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id african_american
UPDATE `list_options` SET `notes` = '2058-6' WHERE `option_id` = 'african_american' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title African American
UPDATE `list_options` SET `notes` = '2058-6' WHERE `title` = 'African American' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id agdaagux title Agdaagux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','agdaagux','Agdaagux','120', '0',' 1994-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id agdaagux
UPDATE `list_options` SET `notes` = '1994-3' WHERE `option_id` = 'agdaagux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Agdaagux
UPDATE `list_options` SET `notes` = '1994-3' WHERE `title` = 'Agdaagux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id agua_caliente title Agua Caliente
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','agua_caliente','Agua Caliente','130', '0',' 1212-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id agua_caliente
UPDATE `list_options` SET `notes` = '1212-0' WHERE `option_id` = 'agua_caliente' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Agua Caliente
UPDATE `list_options` SET `notes` = '1212-0' WHERE `title` = 'Agua Caliente' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id agua_caliente_cahuilla title Agua Caliente Cahuilla
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','agua_caliente_cahuilla','Agua Caliente Cahuilla','140', '0',' 1045-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id agua_caliente_cahuilla
UPDATE `list_options` SET `notes` = '1045-4' WHERE `option_id` = 'agua_caliente_cahuilla' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Agua Caliente Cahuilla
UPDATE `list_options` SET `notes` = '1045-4' WHERE `title` = 'Agua Caliente Cahuilla' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ahtna title Ahtna
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ahtna','Ahtna','150', '0',' 1740-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id ahtna
UPDATE `list_options` SET `notes` = '1740-0' WHERE `option_id` = 'ahtna' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ahtna
UPDATE `list_options` SET `notes` = '1740-0' WHERE `title` = 'Ahtna' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ak-chin title Ak-Chin
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ak-chin','Ak-Chin','160', '0',' 1654-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id ak-chin
UPDATE `list_options` SET `notes` = '1654-3' WHERE `option_id` = 'ak-chin' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ak-Chin
UPDATE `list_options` SET `notes` = '1654-3' WHERE `title` = 'Ak-Chin' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id akhiok title Akhiok
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','akhiok','Akhiok','170', '0',' 1993-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id akhiok
UPDATE `list_options` SET `notes` = '1993-5' WHERE `option_id` = 'akhiok' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Akhiok
UPDATE `list_options` SET `notes` = '1993-5' WHERE `title` = 'Akhiok' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id akiachak title Akiachak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','akiachak','Akiachak','180', '0',' 1897-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id akiachak
UPDATE `list_options` SET `notes` = '1897-8' WHERE `option_id` = 'akiachak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Akiachak
UPDATE `list_options` SET `notes` = '1897-8' WHERE `title` = 'Akiachak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id akiak title Akiak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','akiak','Akiak','190', '0',' 1898-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id akiak
UPDATE `list_options` SET `notes` = '1898-6' WHERE `option_id` = 'akiak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Akiak
UPDATE `list_options` SET `notes` = '1898-6' WHERE `title` = 'Akiak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id akutan title Akutan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','akutan','Akutan','200', '0',' 2007-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id akutan
UPDATE `list_options` SET `notes` = '2007-3' WHERE `option_id` = 'akutan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Akutan
UPDATE `list_options` SET `notes` = '2007-3' WHERE `title` = 'Akutan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alabama_coushatta title Alabama Coushatta
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alabama_coushatta','Alabama Coushatta','210', '0',' 1187-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id alabama_coushatta
UPDATE `list_options` SET `notes` = '1187-4' WHERE `option_id` = 'alabama_coushatta' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alabama Coushatta
UPDATE `list_options` SET `notes` = '1187-4' WHERE `title` = 'Alabama Coushatta' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alabama_creek title Alabama Creek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alabama_creek','Alabama Creek','220', '0',' 1194-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id alabama_creek
UPDATE `list_options` SET `notes` = '1194-0' WHERE `option_id` = 'alabama_creek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alabama Creek
UPDATE `list_options` SET `notes` = '1194-0' WHERE `title` = 'Alabama Creek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alabama_quassarte title Alabama Quassarte
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alabama_quassarte','Alabama Quassarte','230', '0',' 1195-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id alabama_quassarte
UPDATE `list_options` SET `notes` = '1195-7' WHERE `option_id` = 'alabama_quassarte' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alabama Quassarte
UPDATE `list_options` SET `notes` = '1195-7' WHERE `title` = 'Alabama Quassarte' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alakanuk title Alakanuk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alakanuk','Alakanuk','240', '0',' 1899-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id alakanuk
UPDATE `list_options` SET `notes` = '1899-4' WHERE `option_id` = 'alakanuk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alakanuk
UPDATE `list_options` SET `notes` = '1899-4' WHERE `title` = 'Alakanuk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alamo_navajo title Alamo Navajo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alamo_navajo','Alamo Navajo','250', '0',' 1383-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id alamo_navajo
UPDATE `list_options` SET `notes` = '1383-9' WHERE `option_id` = 'alamo_navajo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alamo Navajo
UPDATE `list_options` SET `notes` = '1383-9' WHERE `title` = 'Alamo Navajo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alanvik title Alanvik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alanvik','Alanvik','260', '0',' 1744-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id alanvik
UPDATE `list_options` SET `notes` = '1744-2' WHERE `option_id` = 'alanvik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alanvik
UPDATE `list_options` SET `notes` = '1744-2' WHERE `title` = 'Alanvik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alaska_indian title Alaska Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alaska_indian','Alaska Indian','270', '0',' 1737-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id alaska_indian
UPDATE `list_options` SET `notes` = '1737-6' WHERE `option_id` = 'alaska_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alaska Indian
UPDATE `list_options` SET `notes` = '1737-6' WHERE `title` = 'Alaska Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alaska_native title Alaska Native
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alaska_native','Alaska Native','280', '0',' 1735-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id alaska_native
UPDATE `list_options` SET `notes` = '1735-0' WHERE `option_id` = 'alaska_native' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alaska Native
UPDATE `list_options` SET `notes` = '1735-0' WHERE `title` = 'Alaska Native' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alaskan_athabascan title Alaskan Athabascan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alaskan_athabascan','Alaskan Athabascan','290', '0',' 1739-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id alaskan_athabascan
UPDATE `list_options` SET `notes` = '1739-2' WHERE `option_id` = 'alaskan_athabascan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alaskan Athabascan
UPDATE `list_options` SET `notes` = '1739-2' WHERE `title` = 'Alaskan Athabascan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alatna title Alatna
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alatna','Alatna','300', '0',' 1741-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id alatna
UPDATE `list_options` SET `notes` = '1741-8' WHERE `option_id` = 'alatna' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alatna
UPDATE `list_options` SET `notes` = '1741-8' WHERE `title` = 'Alatna' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id aleknagik title Aleknagik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','aleknagik','Aleknagik','310', '0',' 1900-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id aleknagik
UPDATE `list_options` SET `notes` = '1900-0' WHERE `option_id` = 'aleknagik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Aleknagik
UPDATE `list_options` SET `notes` = '1900-0' WHERE `title` = 'Aleknagik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id aleut title Aleut
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','aleut','Aleut','320', '0',' 1966-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id aleut
UPDATE `list_options` SET `notes` = '1966-1' WHERE `option_id` = 'aleut' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Aleut
UPDATE `list_options` SET `notes` = '1966-1' WHERE `title` = 'Aleut' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id aleut_corporation title Aleut Corporation
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','aleut_corporation','Aleut Corporation','330', '0',' 2008-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id aleut_corporation
UPDATE `list_options` SET `notes` = '2008-1' WHERE `option_id` = 'aleut_corporation' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Aleut Corporation
UPDATE `list_options` SET `notes` = '2008-1' WHERE `title` = 'Aleut Corporation' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id aleutian title Aleutian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','aleutian','Aleutian','340', '0',' 2009-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id aleutian
UPDATE `list_options` SET `notes` = '2009-9' WHERE `option_id` = 'aleutian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Aleutian
UPDATE `list_options` SET `notes` = '2009-9' WHERE `title` = 'Aleutian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id aleutian_islander title Aleutian Islander
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','aleutian_islander','Aleutian Islander','350', '0',' 2010-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id aleutian_islander
UPDATE `list_options` SET `notes` = '2010-7' WHERE `option_id` = 'aleutian_islander' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Aleutian Islander
UPDATE `list_options` SET `notes` = '2010-7' WHERE `title` = 'Aleutian Islander' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alexander title Alexander
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alexander','Alexander','360', '0',' 1742-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id alexander
UPDATE `list_options` SET `notes` = '1742-6' WHERE `option_id` = 'alexander' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alexander
UPDATE `list_options` SET `notes` = '1742-6' WHERE `title` = 'Alexander' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id algonquian title Algonquian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','algonquian','Algonquian','370', '0',' 1008-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id algonquian
UPDATE `list_options` SET `notes` = '1008-2' WHERE `option_id` = 'algonquian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Algonquian
UPDATE `list_options` SET `notes` = '1008-2' WHERE `title` = 'Algonquian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id allakaket title Allakaket
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','allakaket','Allakaket','380', '0',' 1743-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id allakaket
UPDATE `list_options` SET `notes` = '1743-4' WHERE `option_id` = 'allakaket' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Allakaket
UPDATE `list_options` SET `notes` = '1743-4' WHERE `title` = 'Allakaket' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id allen_canyon title Allen Canyon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','allen_canyon','Allen Canyon','390', '0',' 1671-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id allen_canyon
UPDATE `list_options` SET `notes` = '1671-7' WHERE `option_id` = 'allen_canyon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Allen Canyon
UPDATE `list_options` SET `notes` = '1671-7' WHERE `title` = 'Allen Canyon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alpine title Alpine
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alpine','Alpine','400', '0',' 1688-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id alpine
UPDATE `list_options` SET `notes` = '1688-1' WHERE `option_id` = 'alpine' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alpine
UPDATE `list_options` SET `notes` = '1688-1' WHERE `title` = 'Alpine' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alsea title Alsea
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alsea','Alsea','410', '0',' 1392-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id alsea
UPDATE `list_options` SET `notes` = '1392-0' WHERE `option_id` = 'alsea' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alsea
UPDATE `list_options` SET `notes` = '1392-0' WHERE `title` = 'Alsea' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id alutiiq_aleut title Alutiiq Aleut
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','alutiiq_aleut','Alutiiq Aleut','420', '0',' 1968-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id alutiiq_aleut
UPDATE `list_options` SET `notes` = '1968-7' WHERE `option_id` = 'alutiiq_aleut' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Alutiiq Aleut
UPDATE `list_options` SET `notes` = '1968-7' WHERE `title` = 'Alutiiq Aleut' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ambler title Ambler
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ambler','Ambler','430', '0',' 1845-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id ambler
UPDATE `list_options` SET `notes` = '1845-7' WHERE `option_id` = 'ambler' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ambler
UPDATE `list_options` SET `notes` = '1845-7' WHERE `title` = 'Ambler' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id american_indian title American Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','american_indian','American Indian','440', '0',' 1004-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id american_indian
UPDATE `list_options` SET `notes` = '1004-1' WHERE `option_id` = 'american_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title American Indian
UPDATE `list_options` SET `notes` = '1004-1' WHERE `title` = 'American Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id anaktuvuk title Anaktuvuk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','anaktuvuk','Anaktuvuk','460', '0',' 1846-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id anaktuvuk
UPDATE `list_options` SET `notes` = '1846-5' WHERE `option_id` = 'anaktuvuk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Anaktuvuk
UPDATE `list_options` SET `notes` = '1846-5' WHERE `title` = 'Anaktuvuk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id anaktuvuk_pass title Anaktuvuk Pass
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','anaktuvuk_pass','Anaktuvuk Pass','470', '0',' 1847-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id anaktuvuk_pass
UPDATE `list_options` SET `notes` = '1847-3' WHERE `option_id` = 'anaktuvuk_pass' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Anaktuvuk Pass
UPDATE `list_options` SET `notes` = '1847-3' WHERE `title` = 'Anaktuvuk Pass' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id andreafsky title Andreafsky
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','andreafsky','Andreafsky','480', '0',' 1901-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id andreafsky
UPDATE `list_options` SET `notes` = '1901-8' WHERE `option_id` = 'andreafsky' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Andreafsky
UPDATE `list_options` SET `notes` = '1901-8' WHERE `title` = 'Andreafsky' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id angoon title Angoon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','angoon','Angoon','490', '0',' 1814-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id angoon
UPDATE `list_options` SET `notes` = '1814-3' WHERE `option_id` = 'angoon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Angoon
UPDATE `list_options` SET `notes` = '1814-3' WHERE `title` = 'Angoon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id aniak title Aniak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','aniak','Aniak','500', '0',' 1902-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id aniak
UPDATE `list_options` SET `notes` = '1902-6' WHERE `option_id` = 'aniak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Aniak
UPDATE `list_options` SET `notes` = '1902-6' WHERE `title` = 'Aniak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id anvik title Anvik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','anvik','Anvik','510', '0',' 1745-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id anvik
UPDATE `list_options` SET `notes` = '1745-9' WHERE `option_id` = 'anvik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Anvik
UPDATE `list_options` SET `notes` = '1745-9' WHERE `title` = 'Anvik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id apache title Apache
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','apache','Apache','520', '0',' 1010-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id apache
UPDATE `list_options` SET `notes` = '1010-8' WHERE `option_id` = 'apache' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Apache
UPDATE `list_options` SET `notes` = '1010-8' WHERE `title` = 'Apache' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id arab title Arab
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','arab','Arab','530', '0',' 2129-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id arab
UPDATE `list_options` SET `notes` = '2129-5' WHERE `option_id` = 'arab' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Arab
UPDATE `list_options` SET `notes` = '2129-5' WHERE `title` = 'Arab' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id arapaho title Arapaho
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','arapaho','Arapaho','540', '0',' 1021-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id arapaho
UPDATE `list_options` SET `notes` = '1021-5' WHERE `option_id` = 'arapaho' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Arapaho
UPDATE `list_options` SET `notes` = '1021-5' WHERE `title` = 'Arapaho' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id arctic title Arctic
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','arctic','Arctic','550', '0',' 1746-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id arctic
UPDATE `list_options` SET `notes` = '1746-7' WHERE `option_id` = 'arctic' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Arctic
UPDATE `list_options` SET `notes` = '1746-7' WHERE `title` = 'Arctic' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id arctic_slope_corporation title Arctic Slope Corporation
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','arctic_slope_corporation','Arctic Slope Corporation','560', '0',' 1849-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id arctic_slope_corporation
UPDATE `list_options` SET `notes` = '1849-9' WHERE `option_id` = 'arctic_slope_corporation' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Arctic Slope Corporation
UPDATE `list_options` SET `notes` = '1849-9' WHERE `title` = 'Arctic Slope Corporation' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id arctic_slope_inupiat title Arctic Slope Inupiat
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','arctic_slope_inupiat','Arctic Slope Inupiat','570', '0',' 1848-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id arctic_slope_inupiat
UPDATE `list_options` SET `notes` = '1848-1' WHERE `option_id` = 'arctic_slope_inupiat' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Arctic Slope Inupiat
UPDATE `list_options` SET `notes` = '1848-1' WHERE `title` = 'Arctic Slope Inupiat' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id arikara title Arikara
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','arikara','Arikara','580', '0',' 1026-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id arikara
UPDATE `list_options` SET `notes` = '1026-4' WHERE `option_id` = 'arikara' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Arikara
UPDATE `list_options` SET `notes` = '1026-4' WHERE `title` = 'Arikara' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id arizona_tewa title Arizona Tewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','arizona_tewa','Arizona Tewa','590', '0',' 1491-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id arizona_tewa
UPDATE `list_options` SET `notes` = '1491-0' WHERE `option_id` = 'arizona_tewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Arizona Tewa
UPDATE `list_options` SET `notes` = '1491-0' WHERE `title` = 'Arizona Tewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id armenian title Armenian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','armenian','Armenian','600', '0',' 2109-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id armenian
UPDATE `list_options` SET `notes` = '2109-7' WHERE `option_id` = 'armenian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Armenian
UPDATE `list_options` SET `notes` = '2109-7' WHERE `title` = 'Armenian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id aroostook title Aroostook
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','aroostook','Aroostook','610', '0',' 1366-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id aroostook
UPDATE `list_options` SET `notes` = '1366-4' WHERE `option_id` = 'aroostook' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Aroostook
UPDATE `list_options` SET `notes` = '1366-4' WHERE `title` = 'Aroostook' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id asian_indian title Asian Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','asian_indian','Asian Indian','630', '0',' 2029-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id asian_indian
UPDATE `list_options` SET `notes` = '2029-7' WHERE `option_id` = 'asian_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Asian Indian
UPDATE `list_options` SET `notes` = '2029-7' WHERE `title` = 'Asian Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id assiniboine title Assiniboine
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','assiniboine','Assiniboine','640', '0',' 1028-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id assiniboine
UPDATE `list_options` SET `notes` = '1028-0' WHERE `option_id` = 'assiniboine' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Assiniboine
UPDATE `list_options` SET `notes` = '1028-0' WHERE `title` = 'Assiniboine' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id assiniboine_sioux title Assiniboine Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','assiniboine_sioux','Assiniboine Sioux','650', '0',' 1030-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id assiniboine_sioux
UPDATE `list_options` SET `notes` = '1030-6' WHERE `option_id` = 'assiniboine_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Assiniboine Sioux
UPDATE `list_options` SET `notes` = '1030-6' WHERE `title` = 'Assiniboine Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id assyrian title Assyrian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','assyrian','Assyrian','660', '0',' 2119-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id assyrian
UPDATE `list_options` SET `notes` = '2119-6' WHERE `option_id` = 'assyrian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Assyrian
UPDATE `list_options` SET `notes` = '2119-6' WHERE `title` = 'Assyrian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id atka title Atka
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','atka','Atka','670', '0',' 2011-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id atka
UPDATE `list_options` SET `notes` = '2011-5' WHERE `option_id` = 'atka' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Atka
UPDATE `list_options` SET `notes` = '2011-5' WHERE `title` = 'Atka' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id atmautluak title Atmautluak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','atmautluak','Atmautluak','680', '0',' 1903-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id atmautluak
UPDATE `list_options` SET `notes` = '1903-4' WHERE `option_id` = 'atmautluak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Atmautluak
UPDATE `list_options` SET `notes` = '1903-4' WHERE `title` = 'Atmautluak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id atqasuk title Atqasuk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','atqasuk','Atqasuk','690', '0',' 1850-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id atqasuk
UPDATE `list_options` SET `notes` = '1850-7' WHERE `option_id` = 'atqasuk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Atqasuk
UPDATE `list_options` SET `notes` = '1850-7' WHERE `title` = 'Atqasuk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id atsina title Atsina
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','atsina','Atsina','700', '0',' 1265-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id atsina
UPDATE `list_options` SET `notes` = '1265-8' WHERE `option_id` = 'atsina' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Atsina
UPDATE `list_options` SET `notes` = '1265-8' WHERE `title` = 'Atsina' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id attacapa title Attacapa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','attacapa','Attacapa','710', '0',' 1234-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id attacapa
UPDATE `list_options` SET `notes` = '1234-4' WHERE `option_id` = 'attacapa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Attacapa
UPDATE `list_options` SET `notes` = '1234-4' WHERE `title` = 'Attacapa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id augustine title Augustine
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','augustine','Augustine','720', '0',' 1046-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id augustine
UPDATE `list_options` SET `notes` = '1046-2' WHERE `option_id` = 'augustine' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Augustine
UPDATE `list_options` SET `notes` = '1046-2' WHERE `title` = 'Augustine' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bad_river title Bad River
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bad_river','Bad River','730', '0',' 1124-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id bad_river
UPDATE `list_options` SET `notes` = '1124-7' WHERE `option_id` = 'bad_river' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bad River
UPDATE `list_options` SET `notes` = '1124-7' WHERE `title` = 'Bad River' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bahamian title Bahamian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bahamian','Bahamian','740', '0',' 2067-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id bahamian
UPDATE `list_options` SET `notes` = '2067-7' WHERE `option_id` = 'bahamian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bahamian
UPDATE `list_options` SET `notes` = '2067-7' WHERE `title` = 'Bahamian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bangladeshi title Bangladeshi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bangladeshi','Bangladeshi','750', '0',' 2030-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id bangladeshi
UPDATE `list_options` SET `notes` = '2030-5' WHERE `option_id` = 'bangladeshi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bangladeshi
UPDATE `list_options` SET `notes` = '2030-5' WHERE `title` = 'Bangladeshi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bannock title Bannock
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bannock','Bannock','760', '0',' 1033-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id bannock
UPDATE `list_options` SET `notes` = '1033-0' WHERE `option_id` = 'bannock' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bannock
UPDATE `list_options` SET `notes` = '1033-0' WHERE `title` = 'Bannock' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id barbadian title Barbadian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','barbadian','Barbadian','770', '0',' 2068-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id barbadian
UPDATE `list_options` SET `notes` = '2068-5' WHERE `option_id` = 'barbadian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Barbadian
UPDATE `list_options` SET `notes` = '2068-5' WHERE `title` = 'Barbadian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id barrio_libre title Barrio Libre
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','barrio_libre','Barrio Libre','780', '0',' 1712-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id barrio_libre
UPDATE `list_options` SET `notes` = '1712-9' WHERE `option_id` = 'barrio_libre' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Barrio Libre
UPDATE `list_options` SET `notes` = '1712-9' WHERE `title` = 'Barrio Libre' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id barrow title Barrow
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','barrow','Barrow','790', '0',' 1851-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id barrow
UPDATE `list_options` SET `notes` = '1851-5' WHERE `option_id` = 'barrow' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Barrow
UPDATE `list_options` SET `notes` = '1851-5' WHERE `title` = 'Barrow' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id battle_mountain title Battle Mountain
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','battle_mountain','Battle Mountain','800', '0',' 1587-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id battle_mountain
UPDATE `list_options` SET `notes` = '1587-5' WHERE `option_id` = 'battle_mountain' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Battle Mountain
UPDATE `list_options` SET `notes` = '1587-5' WHERE `title` = 'Battle Mountain' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bay_mills_chippewa title Bay Mills Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bay_mills_chippewa','Bay Mills Chippewa','810', '0',' 1125-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id bay_mills_chippewa
UPDATE `list_options` SET `notes` = '1125-4' WHERE `option_id` = 'bay_mills_chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bay Mills Chippewa
UPDATE `list_options` SET `notes` = '1125-4' WHERE `title` = 'Bay Mills Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id beaver title Beaver
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','beaver','Beaver','820', '0',' 1747-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id beaver
UPDATE `list_options` SET `notes` = '1747-5' WHERE `option_id` = 'beaver' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Beaver
UPDATE `list_options` SET `notes` = '1747-5' WHERE `title` = 'Beaver' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id belkofski title Belkofski
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','belkofski','Belkofski','830', '0',' 2012-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id belkofski
UPDATE `list_options` SET `notes` = '2012-3' WHERE `option_id` = 'belkofski' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Belkofski
UPDATE `list_options` SET `notes` = '2012-3' WHERE `title` = 'Belkofski' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bering_straits_inupiat title Bering Straits Inupiat
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bering_straits_inupiat','Bering Straits Inupiat','840', '0',' 1852-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id bering_straits_inupiat
UPDATE `list_options` SET `notes` = '1852-3' WHERE `option_id` = 'bering_straits_inupiat' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bering Straits Inupiat
UPDATE `list_options` SET `notes` = '1852-3' WHERE `title` = 'Bering Straits Inupiat' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bethel title Bethel
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bethel','Bethel','850', '0',' 1904-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id bethel
UPDATE `list_options` SET `notes` = '1904-2' WHERE `option_id` = 'bethel' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bethel
UPDATE `list_options` SET `notes` = '1904-2' WHERE `title` = 'Bethel' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bhutanese title Bhutanese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bhutanese','Bhutanese','860', '0',' 2031-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id bhutanese
UPDATE `list_options` SET `notes` = '2031-3' WHERE `option_id` = 'bhutanese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bhutanese
UPDATE `list_options` SET `notes` = '2031-3' WHERE `title` = 'Bhutanese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id big_cypress title Big Cypress
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','big_cypress','Big Cypress','870', '0',' 1567-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id big_cypress
UPDATE `list_options` SET `notes` = '1567-7' WHERE `option_id` = 'big_cypress' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Big Cypress
UPDATE `list_options` SET `notes` = '1567-7' WHERE `title` = 'Big Cypress' AND `list_id` = 'race';
#EndIf

#IfNotRow2D list_options list_id race option_id bill_moores_slough
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bill_moores_slough',"Bill Moore's Slough",'880', '0',' 1905-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id bill_moores_slough
UPDATE `list_options` SET `notes` = '1905-9' WHERE `option_id` = 'bill_moores_slough' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id biloxi title Biloxi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','biloxi','Biloxi','890', '0',' 1235-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id biloxi
UPDATE `list_options` SET `notes` = '1235-1' WHERE `option_id` = 'biloxi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Biloxi
UPDATE `list_options` SET `notes` = '1235-1' WHERE `title` = 'Biloxi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id birch_creek title Birch Creek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','birch_creek','Birch Creek','900', '0',' 1748-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id birch_creek
UPDATE `list_options` SET `notes` = '1748-3' WHERE `option_id` = 'birch_creek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Birch Creek
UPDATE `list_options` SET `notes` = '1748-3' WHERE `title` = 'Birch Creek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bishop title Bishop
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bishop','Bishop','910', '0',' 1417-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id bishop
UPDATE `list_options` SET `notes` = '1417-5' WHERE `option_id` = 'bishop' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bishop
UPDATE `list_options` SET `notes` = '1417-5' WHERE `title` = 'Bishop' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id black title Black
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','black','Black','920', '0',' 2056-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id black
UPDATE `list_options` SET `notes` = '2056-0' WHERE `option_id` = 'black' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Black
UPDATE `list_options` SET `notes` = '2056-0' WHERE `title` = 'Black' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id blackfeet title Blackfeet
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','blackfeet','Blackfeet','940', '0',' 1035-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id blackfeet
UPDATE `list_options` SET `notes` = '1035-5' WHERE `option_id` = 'blackfeet' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Blackfeet
UPDATE `list_options` SET `notes` = '1035-5' WHERE `title` = 'Blackfeet' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id blackfoot_sioux title Blackfoot Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','blackfoot_sioux','Blackfoot Sioux','950', '0',' 1610-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id blackfoot_sioux
UPDATE `list_options` SET `notes` = '1610-5' WHERE `option_id` = 'blackfoot_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Blackfoot Sioux
UPDATE `list_options` SET `notes` = '1610-5' WHERE `title` = 'Blackfoot Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bois_forte title Bois Forte
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bois_forte','Bois Forte','960', '0',' 1126-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id bois_forte
UPDATE `list_options` SET `notes` = '1126-2' WHERE `option_id` = 'bois_forte' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bois Forte
UPDATE `list_options` SET `notes` = '1126-2' WHERE `title` = 'Bois Forte' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id botswanan title Botswanan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','botswanan','Botswanan','970', '0',' 2061-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id botswanan
UPDATE `list_options` SET `notes` = '2061-0' WHERE `option_id` = 'botswanan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Botswanan
UPDATE `list_options` SET `notes` = '2061-0' WHERE `title` = 'Botswanan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id brevig_mission title Brevig Mission
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','brevig_mission','Brevig Mission','980', '0',' 1853-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id brevig_mission
UPDATE `list_options` SET `notes` = '1853-1' WHERE `option_id` = 'brevig_mission' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Brevig Mission
UPDATE `list_options` SET `notes` = '1853-1' WHERE `title` = 'Brevig Mission' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bridgeport title Bridgeport
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bridgeport','Bridgeport','990', '0',' 1418-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id bridgeport
UPDATE `list_options` SET `notes` = '1418-3' WHERE `option_id` = 'bridgeport' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bridgeport
UPDATE `list_options` SET `notes` = '1418-3' WHERE `title` = 'Bridgeport' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id brighton title Brighton
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','brighton','Brighton','1000', '0',' 1568-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id brighton
UPDATE `list_options` SET `notes` = '1568-5' WHERE `option_id` = 'brighton' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Brighton
UPDATE `list_options` SET `notes` = '1568-5' WHERE `title` = 'Brighton' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bristol_bay_aleut title Bristol Bay Aleut
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bristol_bay_aleut','Bristol Bay Aleut','1010', '0',' 1972-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id bristol_bay_aleut
UPDATE `list_options` SET `notes` = '1972-9' WHERE `option_id` = 'bristol_bay_aleut' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bristol Bay Aleut
UPDATE `list_options` SET `notes` = '1972-9' WHERE `title` = 'Bristol Bay Aleut' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id bristol_bay_yupik title Bristol Bay Yupik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','bristol_bay_yupik','Bristol Bay Yupik','1020', '0',' 1906-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id bristol_bay_yupik
UPDATE `list_options` SET `notes` = '1906-7' WHERE `option_id` = 'bristol_bay_yupik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Bristol Bay Yupik
UPDATE `list_options` SET `notes` = '1906-7' WHERE `title` = 'Bristol Bay Yupik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id brotherton title Brotherton
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','brotherton','Brotherton','1030', '0',' 1037-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id brotherton
UPDATE `list_options` SET `notes` = '1037-1' WHERE `option_id` = 'brotherton' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Brotherton
UPDATE `list_options` SET `notes` = '1037-1' WHERE `title` = 'Brotherton' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id brule_sioux title Brule Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','brule_sioux','Brule Sioux','1040', '0',' 1611-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id brule_sioux
UPDATE `list_options` SET `notes` = '1611-3' WHERE `option_id` = 'brule_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Brule Sioux
UPDATE `list_options` SET `notes` = '1611-3' WHERE `title` = 'Brule Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id buckland title Buckland
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','buckland','Buckland','1050', '0',' 1854-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id buckland
UPDATE `list_options` SET `notes` = '1854-9' WHERE `option_id` = 'buckland' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Buckland
UPDATE `list_options` SET `notes` = '1854-9' WHERE `title` = 'Buckland' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id burmese title Burmese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','burmese','Burmese','1060', '0',' 2032-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id burmese
UPDATE `list_options` SET `notes` = '2032-1' WHERE `option_id` = 'burmese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Burmese
UPDATE `list_options` SET `notes` = '2032-1' WHERE `title` = 'Burmese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id burns_paiute title Burns Paiute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','burns_paiute','Burns Paiute','1070', '0',' 1419-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id burns_paiute
UPDATE `list_options` SET `notes` = '1419-1' WHERE `option_id` = 'burns_paiute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Burns Paiute
UPDATE `list_options` SET `notes` = '1419-1' WHERE `title` = 'Burns Paiute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id burt_lake_band title Burt Lake Band
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','burt_lake_band','Burt Lake Band','1080', '0',' 1039-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id burt_lake_band
UPDATE `list_options` SET `notes` = '1039-7' WHERE `option_id` = 'burt_lake_band' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Burt Lake Band
UPDATE `list_options` SET `notes` = '1039-7' WHERE `title` = 'Burt Lake Band' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id burt_lake_chippewa title Burt Lake Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','burt_lake_chippewa','Burt Lake Chippewa','1090', '0',' 1127-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id burt_lake_chippewa
UPDATE `list_options` SET `notes` = '1127-0' WHERE `option_id` = 'burt_lake_chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Burt Lake Chippewa
UPDATE `list_options` SET `notes` = '1127-0' WHERE `title` = 'Burt Lake Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id burt_lake_ottawa title Burt Lake Ottawa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','burt_lake_ottawa','Burt Lake Ottawa','1100', '0',' 1412-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id burt_lake_ottawa
UPDATE `list_options` SET `notes` = '1412-6' WHERE `option_id` = 'burt_lake_ottawa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Burt Lake Ottawa
UPDATE `list_options` SET `notes` = '1412-6' WHERE `title` = 'Burt Lake Ottawa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cabazon title Cabazon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cabazon','Cabazon','1110', '0',' 1047-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id cabazon
UPDATE `list_options` SET `notes` = '1047-0' WHERE `option_id` = 'cabazon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cabazon
UPDATE `list_options` SET `notes` = '1047-0' WHERE `title` = 'Cabazon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id caddo title Caddo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','caddo','Caddo','1120', '0',' 1041-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id caddo
UPDATE `list_options` SET `notes` = '1041-3' WHERE `option_id` = 'caddo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Caddo
UPDATE `list_options` SET `notes` = '1041-3' WHERE `title` = 'Caddo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cahto title Cahto
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cahto','Cahto','1130', '0',' 1054-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id cahto
UPDATE `list_options` SET `notes` = '1054-6' WHERE `option_id` = 'cahto' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cahto
UPDATE `list_options` SET `notes` = '1054-6' WHERE `title` = 'Cahto' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cahuilla title Cahuilla
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cahuilla','Cahuilla','1140', '0',' 1044-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id cahuilla
UPDATE `list_options` SET `notes` = '1044-7' WHERE `option_id` = 'cahuilla' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cahuilla
UPDATE `list_options` SET `notes` = '1044-7' WHERE `title` = 'Cahuilla' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id california_tribes title California Tribes
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','california_tribes','California Tribes','1150', '0',' 1053-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id california_tribes
UPDATE `list_options` SET `notes` = '1053-8' WHERE `option_id` = 'california_tribes' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title California Tribes
UPDATE `list_options` SET `notes` = '1053-8' WHERE `title` = 'California Tribes' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id calista_yupik title Calista Yupik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','calista_yupik','Calista Yupik','1160', '0',' 1907-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id calista_yupik
UPDATE `list_options` SET `notes` = '1907-5' WHERE `option_id` = 'calista_yupik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Calista Yupik
UPDATE `list_options` SET `notes` = '1907-5' WHERE `title` = 'Calista Yupik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cambodian title Cambodian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cambodian','Cambodian','1170', '0',' 2033-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id cambodian
UPDATE `list_options` SET `notes` = '2033-9' WHERE `option_id` = 'cambodian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cambodian
UPDATE `list_options` SET `notes` = '2033-9' WHERE `title` = 'Cambodian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id campo title Campo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','campo','Campo','1180', '0',' 1223-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id campo
UPDATE `list_options` SET `notes` = '1223-7' WHERE `option_id` = 'campo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Campo
UPDATE `list_options` SET `notes` = '1223-7' WHERE `title` = 'Campo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id canadian_latinamerican_indian title Canadian and Latin American Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','canadian_latinamerican_indian','Canadian and Latin American Indian','1190', '0',' 1068-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id canadian_latinamerican_indian
UPDATE `list_options` SET `notes` = '1068-6' WHERE `option_id` = 'canadian_latinamerican_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Canadian and Latin American Indian
UPDATE `list_options` SET `notes` = '1068-6' WHERE `title` = 'Canadian and Latin American Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id canadian_indian title Canadian Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','canadian_indian','Canadian Indian','1200', '0',' 1069-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id canadian_indian
UPDATE `list_options` SET `notes` = '1069-4' WHERE `option_id` = 'canadian_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Canadian Indian
UPDATE `list_options` SET `notes` = '1069-4' WHERE `title` = 'Canadian Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id canoncito_navajo title Canoncito Navajo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','canoncito_navajo','Canoncito Navajo','1210', '0',' 1384-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id canoncito_navajo
UPDATE `list_options` SET `notes` = '1384-7' WHERE `option_id` = 'canoncito_navajo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Canoncito Navajo
UPDATE `list_options` SET `notes` = '1384-7' WHERE `title` = 'Canoncito Navajo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cantwell title Cantwell
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cantwell','Cantwell','1220', '0',' 1749-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id cantwell
UPDATE `list_options` SET `notes` = '1749-1' WHERE `option_id` = 'cantwell' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cantwell
UPDATE `list_options` SET `notes` = '1749-1' WHERE `title` = 'Cantwell' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id capitan_grande title Capitan Grande
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','capitan_grande','Capitan Grande','1230', '0',' 1224-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id capitan_grande
UPDATE `list_options` SET `notes` = '1224-5' WHERE `option_id` = 'capitan_grande' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Capitan Grande
UPDATE `list_options` SET `notes` = '1224-5' WHERE `title` = 'Capitan Grande' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id carolinian title Carolinian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','carolinian','Carolinian','1240', '0',' 2092-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id carolinian
UPDATE `list_options` SET `notes` = '2092-5' WHERE `option_id` = 'carolinian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Carolinian
UPDATE `list_options` SET `notes` = '2092-5' WHERE `title` = 'Carolinian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id carson title Carson
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','carson','Carson','1250', '0',' 1689-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id carson
UPDATE `list_options` SET `notes` = '1689-9' WHERE `option_id` = 'carson' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Carson
UPDATE `list_options` SET `notes` = '1689-9' WHERE `title` = 'Carson' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id catawba title Catawba
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','catawba','Catawba','1260', '0',' 1076-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id catawba
UPDATE `list_options` SET `notes` = '1076-9' WHERE `option_id` = 'catawba' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Catawba
UPDATE `list_options` SET `notes` = '1076-9' WHERE `title` = 'Catawba' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cayuga title Cayuga
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cayuga','Cayuga','1270', '0',' 1286-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id cayuga
UPDATE `list_options` SET `notes` = '1286-4' WHERE `option_id` = 'cayuga' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cayuga
UPDATE `list_options` SET `notes` = '1286-4' WHERE `title` = 'Cayuga' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cayuse title Cayuse
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cayuse','Cayuse','1280', '0',' 1078-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id cayuse
UPDATE `list_options` SET `notes` = '1078-5' WHERE `option_id` = 'cayuse' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cayuse
UPDATE `list_options` SET `notes` = '1078-5' WHERE `title` = 'Cayuse' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cedarville title Cedarville
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cedarville','Cedarville','1290', '0',' 1420-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id cedarville
UPDATE `list_options` SET `notes` = '1420-9' WHERE `option_id` = 'cedarville' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cedarville
UPDATE `list_options` SET `notes` = '1420-9' WHERE `title` = 'Cedarville' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id celilo title Celilo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','celilo','Celilo','1300', '0',' 1393-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id celilo
UPDATE `list_options` SET `notes` = '1393-8' WHERE `option_id` = 'celilo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Celilo
UPDATE `list_options` SET `notes` = '1393-8' WHERE `title` = 'Celilo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id central_american_indian title Central American Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','central_american_indian','Central American Indian','1310', '0',' 1070-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id central_american_indian
UPDATE `list_options` SET `notes` = '1070-2' WHERE `option_id` = 'central_american_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Central American Indian
UPDATE `list_options` SET `notes` = '1070-2' WHERE `title` = 'Central American Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tlingit_and_haida_tribes title Central Council of Tlingit and Haida Tribes
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tlingit_and_haida_tribes','Central Council of Tlingit and Haida Tribes','1320', '0',' 1815-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id tlingit_and_haida_tribes
UPDATE `list_options` SET `notes` = '1815-0' WHERE `option_id` = 'tlingit_and_haida_tribes' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Central Council of Tlingit and Haida Tribes
UPDATE `list_options` SET `notes` = '1815-0' WHERE `title` = 'Central Council of Tlingit and Haida Tribes' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id central_pomo title Central Pomo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','central_pomo','Central Pomo','1330', '0',' 1465-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id central_pomo
UPDATE `list_options` SET `notes` = '1465-4' WHERE `option_id` = 'central_pomo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Central Pomo
UPDATE `list_options` SET `notes` = '1465-4' WHERE `title` = 'Central Pomo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chalkyitsik title Chalkyitsik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chalkyitsik','Chalkyitsik','1340', '0',' 1750-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id chalkyitsik
UPDATE `list_options` SET `notes` = '1750-9' WHERE `option_id` = 'chalkyitsik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chalkyitsik
UPDATE `list_options` SET `notes` = '1750-9' WHERE `title` = 'Chalkyitsik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chamorro title Chamorro
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chamorro','Chamorro','1350', '0',' 2088-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id chamorro
UPDATE `list_options` SET `notes` = '2088-3' WHERE `option_id` = 'chamorro' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chamorro
UPDATE `list_options` SET `notes` = '2088-3' WHERE `title` = 'Chamorro' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chefornak title Chefornak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chefornak','Chefornak','1360', '0',' 1908-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id chefornak
UPDATE `list_options` SET `notes` = '1908-3' WHERE `option_id` = 'chefornak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chefornak
UPDATE `list_options` SET `notes` = '1908-3' WHERE `title` = 'Chefornak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chehalis title Chehalis
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chehalis','Chehalis','1370', '0',' 1080-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id chehalis
UPDATE `list_options` SET `notes` = '1080-1' WHERE `option_id` = 'chehalis' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chehalis
UPDATE `list_options` SET `notes` = '1080-1' WHERE `title` = 'Chehalis' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chemakuan title Chemakuan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chemakuan','Chemakuan','1380', '0',' 1082-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id chemakuan
UPDATE `list_options` SET `notes` = '1082-7' WHERE `option_id` = 'chemakuan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chemakuan
UPDATE `list_options` SET `notes` = '1082-7' WHERE `title` = 'Chemakuan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chemehuevi title Chemehuevi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chemehuevi','Chemehuevi','1390', '0',' 1086-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id chemehuevi
UPDATE `list_options` SET `notes` = '1086-8' WHERE `option_id` = 'chemehuevi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chemehuevi
UPDATE `list_options` SET `notes` = '1086-8' WHERE `title` = 'Chemehuevi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chenega title Chenega
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chenega','Chenega','1400', '0',' 1985-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id chenega
UPDATE `list_options` SET `notes` = '1985-1' WHERE `option_id` = 'chenega' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chenega
UPDATE `list_options` SET `notes` = '1985-1' WHERE `title` = 'Chenega' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cherokee title Cherokee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cherokee','Cherokee','1410', '0',' 1088-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id cherokee
UPDATE `list_options` SET `notes` = '1088-4' WHERE `option_id` = 'cherokee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cherokee
UPDATE `list_options` SET `notes` = '1088-4' WHERE `title` = 'Cherokee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cherokee_alabama title Cherokee Alabama
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cherokee_alabama','Cherokee Alabama','1420', '0',' 1089-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id cherokee_alabama
UPDATE `list_options` SET `notes` = '1089-2' WHERE `option_id` = 'cherokee_alabama' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cherokee Alabama
UPDATE `list_options` SET `notes` = '1089-2' WHERE `title` = 'Cherokee Alabama' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cherokee_shawnee title Cherokee Shawnee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cherokee_shawnee','Cherokee Shawnee','1430', '0',' 1100-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id cherokee_shawnee
UPDATE `list_options` SET `notes` = '1100-7' WHERE `option_id` = 'cherokee_shawnee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cherokee Shawnee
UPDATE `list_options` SET `notes` = '1100-7' WHERE `title` = 'Cherokee Shawnee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cherokees_of_northeast_alabama title Cherokees of Northeast Alabama
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cherokees_of_northeast_alabama','Cherokees of Northeast Alabama','1440', '0',' 1090-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id cherokees_of_northeast_alabama
UPDATE `list_options` SET `notes` = '1090-0' WHERE `option_id` = 'cherokees_of_northeast_alabama' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cherokees of Northeast Alabama
UPDATE `list_options` SET `notes` = '1090-0' WHERE `title` = 'Cherokees of Northeast Alabama' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cherokees_of_southeast_alabama title Cherokees of Southeast Alabama
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cherokees_of_southeast_alabama','Cherokees of Southeast Alabama','1450', '0',' 1091-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id cherokees_of_southeast_alabama
UPDATE `list_options` SET `notes` = '1091-8' WHERE `option_id` = 'cherokees_of_southeast_alabama' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cherokees of Southeast Alabama
UPDATE `list_options` SET `notes` = '1091-8' WHERE `title` = 'Cherokees of Southeast Alabama' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chevak title Chevak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chevak','Chevak','1460', '0',' 1909-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id chevak
UPDATE `list_options` SET `notes` = '1909-1' WHERE `option_id` = 'chevak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chevak
UPDATE `list_options` SET `notes` = '1909-1' WHERE `title` = 'Chevak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cheyenne title Cheyenne
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cheyenne','Cheyenne','1470', '0',' 1102-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id cheyenne
UPDATE `list_options` SET `notes` = '1102-3' WHERE `option_id` = 'cheyenne' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cheyenne
UPDATE `list_options` SET `notes` = '1102-3' WHERE `title` = 'Cheyenne' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cheyenne_river_sioux title Cheyenne River Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cheyenne_river_sioux','Cheyenne River Sioux','1480', '0',' 1612-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id cheyenne_river_sioux
UPDATE `list_options` SET `notes` = '1612-1' WHERE `option_id` = 'cheyenne_river_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cheyenne River Sioux
UPDATE `list_options` SET `notes` = '1612-1' WHERE `title` = 'Cheyenne River Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cheyenne-arapaho title Cheyenne-Arapaho
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cheyenne-arapaho','Cheyenne-Arapaho','1490', '0',' 1106-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id cheyenne-arapaho
UPDATE `list_options` SET `notes` = '1106-4' WHERE `option_id` = 'cheyenne-arapaho' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cheyenne-Arapaho
UPDATE `list_options` SET `notes` = '1106-4' WHERE `title` = 'Cheyenne-Arapaho' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chickahominy title Chickahominy
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chickahominy','Chickahominy','1500', '0',' 1108-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id chickahominy
UPDATE `list_options` SET `notes` = '1108-0' WHERE `option_id` = 'chickahominy' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chickahominy
UPDATE `list_options` SET `notes` = '1108-0' WHERE `title` = 'Chickahominy' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chickaloon title Chickaloon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chickaloon','Chickaloon','1510', '0',' 1751-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id chickaloon
UPDATE `list_options` SET `notes` = '1751-7' WHERE `option_id` = 'chickaloon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chickaloon
UPDATE `list_options` SET `notes` = '1751-7' WHERE `title` = 'Chickaloon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chickasaw title Chickasaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chickasaw','Chickasaw','1520', '0',' 1112-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id chickasaw
UPDATE `list_options` SET `notes` = '1112-2' WHERE `option_id` = 'chickasaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chickasaw
UPDATE `list_options` SET `notes` = '1112-2' WHERE `title` = 'Chickasaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chignik title Chignik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chignik','Chignik','1530', '0',' 1973-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id chignik
UPDATE `list_options` SET `notes` = '1973-7' WHERE `option_id` = 'chignik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chignik
UPDATE `list_options` SET `notes` = '1973-7' WHERE `title` = 'Chignik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chignik_lagoon title Chignik Lagoon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chignik_lagoon','Chignik Lagoon','1540', '0',' 2013-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id chignik_lagoon
UPDATE `list_options` SET `notes` = '2013-1' WHERE `option_id` = 'chignik_lagoon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chignik Lagoon
UPDATE `list_options` SET `notes` = '2013-1' WHERE `title` = 'Chignik Lagoon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chignik_lake title Chignik Lake
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chignik_lake','Chignik Lake','1550', '0',' 1974-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id chignik_lake
UPDATE `list_options` SET `notes` = '1974-5' WHERE `option_id` = 'chignik_lake' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chignik Lake
UPDATE `list_options` SET `notes` = '1974-5' WHERE `title` = 'Chignik Lake' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chilkat title Chilkat
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chilkat','Chilkat','1560', '0',' 1816-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id chilkat
UPDATE `list_options` SET `notes` = '1816-8' WHERE `option_id` = 'chilkat' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chilkat
UPDATE `list_options` SET `notes` = '1816-8' WHERE `title` = 'Chilkat' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chilkoot title Chilkoot
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chilkoot','Chilkoot','1570', '0',' 1817-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id chilkoot
UPDATE `list_options` SET `notes` = '1817-6' WHERE `option_id` = 'chilkoot' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chilkoot
UPDATE `list_options` SET `notes` = '1817-6' WHERE `title` = 'Chilkoot' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chimariko title Chimariko
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chimariko','Chimariko','1580', '0',' 1055-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id chimariko
UPDATE `list_options` SET `notes` = '1055-3' WHERE `option_id` = 'chimariko' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chimariko
UPDATE `list_options` SET `notes` = '1055-3' WHERE `title` = 'Chimariko' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chinese title Chinese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chinese','Chinese','1590', '0',' 2034-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id chinese
UPDATE `list_options` SET `notes` = '2034-7' WHERE `option_id` = 'chinese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chinese
UPDATE `list_options` SET `notes` = '2034-7' WHERE `title` = 'Chinese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chinik title Chinik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chinik','Chinik','1600', '0',' 1855-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id chinik
UPDATE `list_options` SET `notes` = '1855-6' WHERE `option_id` = 'chinik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chinik
UPDATE `list_options` SET `notes` = '1855-6' WHERE `title` = 'Chinik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chinook title Chinook
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chinook','Chinook','1610', '0',' 1114-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id chinook
UPDATE `list_options` SET `notes` = '1114-8' WHERE `option_id` = 'chinook' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chinook
UPDATE `list_options` SET `notes` = '1114-8' WHERE `title` = 'Chinook' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chippewa title Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chippewa','Chippewa','1620', '0',' 1123-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id chippewa
UPDATE `list_options` SET `notes` = '1123-9' WHERE `option_id` = 'chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chippewa
UPDATE `list_options` SET `notes` = '1123-9' WHERE `title` = 'Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chippewa_cree title Chippewa Cree
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chippewa_cree','Chippewa Cree','1630', '0',' 1150-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id chippewa_cree
UPDATE `list_options` SET `notes` = '1150-2' WHERE `option_id` = 'chippewa_cree' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chippewa Cree
UPDATE `list_options` SET `notes` = '1150-2' WHERE `title` = 'Chippewa Cree' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chiricahua title Chiricahua
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chiricahua','Chiricahua','1640', '0',' 1011-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id chiricahua
UPDATE `list_options` SET `notes` = '1011-6' WHERE `option_id` = 'chiricahua' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chiricahua
UPDATE `list_options` SET `notes` = '1011-6' WHERE `title` = 'Chiricahua' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chistochina title Chistochina
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chistochina','Chistochina','1650', '0',' 1752-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id chistochina
UPDATE `list_options` SET `notes` = '1752-5' WHERE `option_id` = 'chistochina' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chistochina
UPDATE `list_options` SET `notes` = '1752-5' WHERE `title` = 'Chistochina' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chitimacha title Chitimacha
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chitimacha','Chitimacha','1660', '0',' 1153-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id chitimacha
UPDATE `list_options` SET `notes` = '1153-6' WHERE `option_id` = 'chitimacha' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chitimacha
UPDATE `list_options` SET `notes` = '1153-6' WHERE `title` = 'Chitimacha' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chitina title Chitina
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chitina','Chitina','1670', '0',' 1753-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id chitina
UPDATE `list_options` SET `notes` = '1753-3' WHERE `option_id` = 'chitina' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chitina
UPDATE `list_options` SET `notes` = '1753-3' WHERE `title` = 'Chitina' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id choctaw title Choctaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','choctaw','Choctaw','1680', '0',' 1155-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id choctaw
UPDATE `list_options` SET `notes` = '1155-1' WHERE `option_id` = 'choctaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Choctaw
UPDATE `list_options` SET `notes` = '1155-1' WHERE `title` = 'Choctaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chuathbaluk title Chuathbaluk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chuathbaluk','Chuathbaluk','1690', '0',' 1910-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id chuathbaluk
UPDATE `list_options` SET `notes` = '1910-9' WHERE `option_id` = 'chuathbaluk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chuathbaluk
UPDATE `list_options` SET `notes` = '1910-9' WHERE `title` = 'Chuathbaluk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chugach_aleut title Chugach Aleut
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chugach_aleut','Chugach Aleut','1700', '0',' 1984-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id chugach_aleut
UPDATE `list_options` SET `notes` = '1984-4' WHERE `option_id` = 'chugach_aleut' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chugach Aleut
UPDATE `list_options` SET `notes` = '1984-4' WHERE `title` = 'Chugach Aleut' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chugach_corporation title Chugach Corporation
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chugach_corporation','Chugach Corporation','1710', '0',' 1986-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id chugach_corporation
UPDATE `list_options` SET `notes` = '1986-9' WHERE `option_id` = 'chugach_corporation' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chugach Corporation
UPDATE `list_options` SET `notes` = '1986-9' WHERE `title` = 'Chugach Corporation' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chukchansi title Chukchansi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chukchansi','Chukchansi','1720', '0',' 1718-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id chukchansi
UPDATE `list_options` SET `notes` = '1718-6' WHERE `option_id` = 'chukchansi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chukchansi
UPDATE `list_options` SET `notes` = '1718-6' WHERE `title` = 'Chukchansi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chumash title Chumash
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chumash','Chumash','1730', '0',' 1162-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id chumash
UPDATE `list_options` SET `notes` = '1162-7' WHERE `option_id` = 'chumash' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chumash
UPDATE `list_options` SET `notes` = '1162-7' WHERE `title` = 'Chumash' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id chuukese title Chuukese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','chuukese','Chuukese','1740', '0',' 2097-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id chuukese
UPDATE `list_options` SET `notes` = '2097-4' WHERE `option_id` = 'chuukese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Chuukese
UPDATE `list_options` SET `notes` = '2097-4' WHERE `title` = 'Chuukese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id circle title Circle
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','circle','Circle','1750', '0',' 1754-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id circle
UPDATE `list_options` SET `notes` = '1754-1' WHERE `option_id` = 'circle' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Circle
UPDATE `list_options` SET `notes` = '1754-1' WHERE `title` = 'Circle' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id citizen_band_potawatomi title Citizen Band Potawatomi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','citizen_band_potawatomi','Citizen Band Potawatomi','1760', '0',' 1479-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id citizen_band_potawatomi
UPDATE `list_options` SET `notes` = '1479-5' WHERE `option_id` = 'citizen_band_potawatomi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Citizen Band Potawatomi
UPDATE `list_options` SET `notes` = '1479-5' WHERE `title` = 'Citizen Band Potawatomi' AND `list_id` = 'race';
#EndIf

#IfNotRow2D list_options list_id race option_id clarks_point
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','clarks_point',"Clark's Point",'1770', '0',' 1911-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id clarks_point
UPDATE `list_options` SET `notes` = '1911-7' WHERE `option_id` = 'clarks_point' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id clatsop title Clatsop
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','clatsop','Clatsop','1780', '0',' 1115-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id clatsop
UPDATE `list_options` SET `notes` = '1115-5' WHERE `option_id` = 'clatsop' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Clatsop
UPDATE `list_options` SET `notes` = '1115-5' WHERE `title` = 'Clatsop' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id clear_lake title Clear Lake
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','clear_lake','Clear Lake','1790', '0',' 1165-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id clear_lake
UPDATE `list_options` SET `notes` = '1165-0' WHERE `option_id` = 'clear_lake' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Clear Lake
UPDATE `list_options` SET `notes` = '1165-0' WHERE `title` = 'Clear Lake' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id clifton_choctaw title Clifton Choctaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','clifton_choctaw','Clifton Choctaw','1800', '0',' 1156-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id clifton_choctaw
UPDATE `list_options` SET `notes` = '1156-9' WHERE `option_id` = 'clifton_choctaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Clifton Choctaw
UPDATE `list_options` SET `notes` = '1156-9' WHERE `title` = 'Clifton Choctaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id coast_miwok title Coast Miwok
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','coast_miwok','Coast Miwok','1810', '0',' 1056-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id coast_miwok
UPDATE `list_options` SET `notes` = '1056-1' WHERE `option_id` = 'coast_miwok' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Coast Miwok
UPDATE `list_options` SET `notes` = '1056-1' WHERE `title` = 'Coast Miwok' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id coast_yurok title Coast Yurok
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','coast_yurok','Coast Yurok','1820', '0',' 1733-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id coast_yurok
UPDATE `list_options` SET `notes` = '1733-5' WHERE `option_id` = 'coast_yurok' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Coast Yurok
UPDATE `list_options` SET `notes` = '1733-5' WHERE `title` = 'Coast Yurok' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cochiti title Cochiti
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cochiti','Cochiti','1830', '0',' 1492-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id cochiti
UPDATE `list_options` SET `notes` = '1492-8' WHERE `option_id` = 'cochiti' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cochiti
UPDATE `list_options` SET `notes` = '1492-8' WHERE `title` = 'Cochiti' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cocopah title Cocopah
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cocopah','Cocopah','1840', '0',' 1725-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id cocopah
UPDATE `list_options` SET `notes` = '1725-1' WHERE `option_id` = 'cocopah' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cocopah
UPDATE `list_options` SET `notes` = '1725-1' WHERE `title` = 'Cocopah' AND `list_id` = 'race';
#EndIf

#IfNotRow2D list_options list_id race option_id coeur_dalene
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','coeur_dalene',"Coeur D'Alene",'1850', '0',' 1167-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id coeur_dalene
UPDATE `list_options` SET `notes` = '1167-6' WHERE `option_id` = 'coeur_dalene' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id coharie title Coharie
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','coharie','Coharie','1860', '0',' 1169-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id coharie
UPDATE `list_options` SET `notes` = '1169-2' WHERE `option_id` = 'coharie' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Coharie
UPDATE `list_options` SET `notes` = '1169-2' WHERE `title` = 'Coharie' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id colorado_river title Colorado River
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','colorado_river','Colorado River','1870', '0',' 1171-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id colorado_river
UPDATE `list_options` SET `notes` = '1171-8' WHERE `option_id` = 'colorado_river' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Colorado River
UPDATE `list_options` SET `notes` = '1171-8' WHERE `title` = 'Colorado River' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id columbia title Columbia
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','columbia','Columbia','1880', '0',' 1394-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id columbia
UPDATE `list_options` SET `notes` = '1394-6' WHERE `option_id` = 'columbia' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Columbia
UPDATE `list_options` SET `notes` = '1394-6' WHERE `title` = 'Columbia' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id columbia_river_chinook title Columbia River Chinook
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','columbia_river_chinook','Columbia River Chinook','1890', '0',' 1116-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id columbia_river_chinook
UPDATE `list_options` SET `notes` = '1116-3' WHERE `option_id` = 'columbia_river_chinook' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Columbia River Chinook
UPDATE `list_options` SET `notes` = '1116-3' WHERE `title` = 'Columbia River Chinook' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id colville title Colville
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','colville','Colville','1900', '0',' 1173-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id colville
UPDATE `list_options` SET `notes` = '1173-4' WHERE `option_id` = 'colville' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Colville
UPDATE `list_options` SET `notes` = '1173-4' WHERE `title` = 'Colville' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id comanche title Comanche
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','comanche','Comanche','1910', '0',' 1175-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id comanche
UPDATE `list_options` SET `notes` = '1175-9' WHERE `option_id` = 'comanche' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Comanche
UPDATE `list_options` SET `notes` = '1175-9' WHERE `title` = 'Comanche' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cook_inlet title Cook Inlet
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cook_inlet','Cook Inlet','1920', '0',' 1755-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id cook_inlet
UPDATE `list_options` SET `notes` = '1755-8' WHERE `option_id` = 'cook_inlet' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cook Inlet
UPDATE `list_options` SET `notes` = '1755-8' WHERE `title` = 'Cook Inlet' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id coos title Coos
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','coos','Coos','1930', '0',' 1180-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id coos
UPDATE `list_options` SET `notes` = '1180-9' WHERE `option_id` = 'coos' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Coos
UPDATE `list_options` SET `notes` = '1180-9' WHERE `title` = 'Coos' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id coos_lower_umpqua_siuslaw title Coos, Lower Umpqua, Siuslaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','coos_lower_umpqua_siuslaw','Coos, Lower Umpqua, Siuslaw','1940', '0',' 1178-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id coos_lower_umpqua_siuslaw
UPDATE `list_options` SET `notes` = '1178-3' WHERE `option_id` = 'coos_lower_umpqua_siuslaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Coos, Lower Umpqua, Siuslaw
UPDATE `list_options` SET `notes` = '1178-3' WHERE `title` = 'Coos, Lower Umpqua, Siuslaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id copper_center title Copper Center
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','copper_center','Copper Center','1950', '0',' 1756-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id copper_center
UPDATE `list_options` SET `notes` = '1756-6' WHERE `option_id` = 'copper_center' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Copper Center
UPDATE `list_options` SET `notes` = '1756-6' WHERE `title` = 'Copper Center' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id copper_river title Copper River
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','copper_river','Copper River','1960', '0',' 1757-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id copper_river
UPDATE `list_options` SET `notes` = '1757-4' WHERE `option_id` = 'copper_river' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Copper River
UPDATE `list_options` SET `notes` = '1757-4' WHERE `title` = 'Copper River' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id coquilles title Coquilles
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','coquilles','Coquilles','1970', '0',' 1182-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id coquilles
UPDATE `list_options` SET `notes` = '1182-5' WHERE `option_id` = 'coquilles' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Coquilles
UPDATE `list_options` SET `notes` = '1182-5' WHERE `title` = 'Coquilles' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id costanoan title Costanoan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','costanoan','Costanoan','1980', '0',' 1184-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id costanoan
UPDATE `list_options` SET `notes` = '1184-1' WHERE `option_id` = 'costanoan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Costanoan
UPDATE `list_options` SET `notes` = '1184-1' WHERE `title` = 'Costanoan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id council title Council
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','council','Council','1990', '0',' 1856-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id council
UPDATE `list_options` SET `notes` = '1856-4' WHERE `option_id` = 'council' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Council
UPDATE `list_options` SET `notes` = '1856-4' WHERE `title` = 'Council' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id coushatta title Coushatta
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','coushatta','Coushatta','2000', '0',' 1186-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id coushatta
UPDATE `list_options` SET `notes` = '1186-6' WHERE `option_id` = 'coushatta' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Coushatta
UPDATE `list_options` SET `notes` = '1186-6' WHERE `title` = 'Coushatta' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cow_creek_umpqua title Cow Creek Umpqua
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cow_creek_umpqua','Cow Creek Umpqua','2010', '0',' 1668-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id cow_creek_umpqua
UPDATE `list_options` SET `notes` = '1668-3' WHERE `option_id` = 'cow_creek_umpqua' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cow Creek Umpqua
UPDATE `list_options` SET `notes` = '1668-3' WHERE `title` = 'Cow Creek Umpqua' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cowlitz title Cowlitz
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cowlitz','Cowlitz','2020', '0',' 1189-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id cowlitz
UPDATE `list_options` SET `notes` = '1189-0' WHERE `option_id` = 'cowlitz' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cowlitz
UPDATE `list_options` SET `notes` = '1189-0' WHERE `title` = 'Cowlitz' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id craig title Craig
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','craig','Craig','2030', '0',' 1818-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id craig
UPDATE `list_options` SET `notes` = '1818-4' WHERE `option_id` = 'craig' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Craig
UPDATE `list_options` SET `notes` = '1818-4' WHERE `title` = 'Craig' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cree title Cree
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cree','Cree','2040', '0',' 1191-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id cree
UPDATE `list_options` SET `notes` = '1191-6' WHERE `option_id` = 'cree' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cree
UPDATE `list_options` SET `notes` = '1191-6' WHERE `title` = 'Cree' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id creek title Creek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','creek','Creek','2050', '0',' 1193-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id creek
UPDATE `list_options` SET `notes` = '1193-2' WHERE `option_id` = 'creek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Creek
UPDATE `list_options` SET `notes` = '1193-2' WHERE `title` = 'Creek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id croatan title Croatan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','croatan','Croatan','2060', '0',' 1207-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id croatan
UPDATE `list_options` SET `notes` = '1207-0' WHERE `option_id` = 'croatan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Croatan
UPDATE `list_options` SET `notes` = '1207-0' WHERE `title` = 'Croatan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id crooked_creek title Crooked Creek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','crooked_creek','Crooked Creek','2070', '0',' 1912-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id crooked_creek
UPDATE `list_options` SET `notes` = '1912-5' WHERE `option_id` = 'crooked_creek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Crooked Creek
UPDATE `list_options` SET `notes` = '1912-5' WHERE `title` = 'Crooked Creek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id crow title Crow
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','crow','Crow','2080', '0',' 1209-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id crow
UPDATE `list_options` SET `notes` = '1209-6' WHERE `option_id` = 'crow' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Crow
UPDATE `list_options` SET `notes` = '1209-6' WHERE `title` = 'Crow' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id crow_creek_sioux title Crow Creek Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','crow_creek_sioux','Crow Creek Sioux','2090', '0',' 1613-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id crow_creek_sioux
UPDATE `list_options` SET `notes` = '1613-9' WHERE `option_id` = 'crow_creek_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Crow Creek Sioux
UPDATE `list_options` SET `notes` = '1613-9' WHERE `title` = 'Crow Creek Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cupeno title Cupeno
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cupeno','Cupeno','2100', '0',' 1211-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id cupeno
UPDATE `list_options` SET `notes` = '1211-2' WHERE `option_id` = 'cupeno' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cupeno
UPDATE `list_options` SET `notes` = '1211-2' WHERE `title` = 'Cupeno' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id cuyapaipe title Cuyapaipe
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','cuyapaipe','Cuyapaipe','2110', '0',' 1225-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id cuyapaipe
UPDATE `list_options` SET `notes` = '1225-2' WHERE `option_id` = 'cuyapaipe' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Cuyapaipe
UPDATE `list_options` SET `notes` = '1225-2' WHERE `title` = 'Cuyapaipe' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id dakota_sioux title Dakota Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','dakota_sioux','Dakota Sioux','2120', '0',' 1614-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id dakota_sioux
UPDATE `list_options` SET `notes` = '1614-7' WHERE `option_id` = 'dakota_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Dakota Sioux
UPDATE `list_options` SET `notes` = '1614-7' WHERE `title` = 'Dakota Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id deering title Deering
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','deering','Deering','2130', '0',' 1857-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id deering
UPDATE `list_options` SET `notes` = '1857-2' WHERE `option_id` = 'deering' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Deering
UPDATE `list_options` SET `notes` = '1857-2' WHERE `title` = 'Deering' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id delaware title Delaware
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','delaware','Delaware','2140', '0',' 1214-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id delaware
UPDATE `list_options` SET `notes` = '1214-6' WHERE `option_id` = 'delaware' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Delaware
UPDATE `list_options` SET `notes` = '1214-6' WHERE `title` = 'Delaware' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id diegueno title Diegueno
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','diegueno','Diegueno','2150', '0',' 1222-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id diegueno
UPDATE `list_options` SET `notes` = '1222-9' WHERE `option_id` = 'diegueno' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Diegueno
UPDATE `list_options` SET `notes` = '1222-9' WHERE `title` = 'Diegueno' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id digger title Digger
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','digger','Digger','2160', '0',' 1057-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id digger
UPDATE `list_options` SET `notes` = '1057-9' WHERE `option_id` = 'digger' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Digger
UPDATE `list_options` SET `notes` = '1057-9' WHERE `title` = 'Digger' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id dillingham title Dillingham
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','dillingham','Dillingham','2170', '0',' 1913-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id dillingham
UPDATE `list_options` SET `notes` = '1913-3' WHERE `option_id` = 'dillingham' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Dillingham
UPDATE `list_options` SET `notes` = '1913-3' WHERE `title` = 'Dillingham' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id dominica_islander title Dominica Islander
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','dominica_islander','Dominica Islander','2180', '0',' 2070-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id dominica_islander
UPDATE `list_options` SET `notes` = '2070-1' WHERE `option_id` = 'dominica_islander' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Dominica Islander
UPDATE `list_options` SET `notes` = '2070-1' WHERE `title` = 'Dominica Islander' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id dominican title Dominican
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','dominican','Dominican','2190', '0',' 2069-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id dominican
UPDATE `list_options` SET `notes` = '2069-3' WHERE `option_id` = 'dominican' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Dominican
UPDATE `list_options` SET `notes` = '2069-3' WHERE `title` = 'Dominican' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id dot_lake title Dot Lake
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','dot_lake','Dot Lake','2200', '0',' 1758-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id dot_lake
UPDATE `list_options` SET `notes` = '1758-2' WHERE `option_id` = 'dot_lake' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Dot Lake
UPDATE `list_options` SET `notes` = '1758-2' WHERE `title` = 'Dot Lake' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id douglas title Douglas
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','douglas','Douglas','2210', '0',' 1819-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id douglas
UPDATE `list_options` SET `notes` = '1819-2' WHERE `option_id` = 'douglas' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Douglas
UPDATE `list_options` SET `notes` = '1819-2' WHERE `title` = 'Douglas' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id doyon title Doyon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','doyon','Doyon','2220', '0',' 1759-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id doyon
UPDATE `list_options` SET `notes` = '1759-0' WHERE `option_id` = 'doyon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Doyon
UPDATE `list_options` SET `notes` = '1759-0' WHERE `title` = 'Doyon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id dresslerville title Dresslerville
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','dresslerville','Dresslerville','2230', '0',' 1690-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id dresslerville
UPDATE `list_options` SET `notes` = '1690-7' WHERE `option_id` = 'dresslerville' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Dresslerville
UPDATE `list_options` SET `notes` = '1690-7' WHERE `title` = 'Dresslerville' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id dry_creek title Dry Creek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','dry_creek','Dry Creek','2240', '0',' 1466-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id dry_creek
UPDATE `list_options` SET `notes` = '1466-2' WHERE `option_id` = 'dry_creek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Dry Creek
UPDATE `list_options` SET `notes` = '1466-2' WHERE `title` = 'Dry Creek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id duck_valley title Duck Valley
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','duck_valley','Duck Valley','2250', '0',' 1603-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id duck_valley
UPDATE `list_options` SET `notes` = '1603-0' WHERE `option_id` = 'duck_valley' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Duck Valley
UPDATE `list_options` SET `notes` = '1603-0' WHERE `title` = 'Duck Valley' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id duckwater title Duckwater
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','duckwater','Duckwater','2260', '0',' 1588-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id duckwater
UPDATE `list_options` SET `notes` = '1588-3' WHERE `option_id` = 'duckwater' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Duckwater
UPDATE `list_options` SET `notes` = '1588-3' WHERE `title` = 'Duckwater' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id duwamish title Duwamish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','duwamish','Duwamish','2270', '0',' 1519-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id duwamish
UPDATE `list_options` SET `notes` = '1519-8' WHERE `option_id` = 'duwamish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Duwamish
UPDATE `list_options` SET `notes` = '1519-8' WHERE `title` = 'Duwamish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eagle title Eagle
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eagle','Eagle','2280', '0',' 1760-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id eagle
UPDATE `list_options` SET `notes` = '1760-8' WHERE `option_id` = 'eagle' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eagle
UPDATE `list_options` SET `notes` = '1760-8' WHERE `title` = 'Eagle' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eastern_cherokee title Eastern Cherokee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eastern_cherokee','Eastern Cherokee','2290', '0',' 1092-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id eastern_cherokee
UPDATE `list_options` SET `notes` = '1092-6' WHERE `option_id` = 'eastern_cherokee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eastern Cherokee
UPDATE `list_options` SET `notes` = '1092-6' WHERE `title` = 'Eastern Cherokee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eastern_chickahominy title Eastern Chickahominy
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eastern_chickahominy','Eastern Chickahominy','2300', '0',' 1109-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id eastern_chickahominy
UPDATE `list_options` SET `notes` = '1109-8' WHERE `option_id` = 'eastern_chickahominy' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eastern Chickahominy
UPDATE `list_options` SET `notes` = '1109-8' WHERE `title` = 'Eastern Chickahominy' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eastern_creek title Eastern Creek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eastern_creek','Eastern Creek','2310', '0',' 1196-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id eastern_creek
UPDATE `list_options` SET `notes` = '1196-5' WHERE `option_id` = 'eastern_creek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eastern Creek
UPDATE `list_options` SET `notes` = '1196-5' WHERE `title` = 'Eastern Creek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eastern_delaware title Eastern Delaware
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eastern_delaware','Eastern Delaware','2320', '0',' 1215-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id eastern_delaware
UPDATE `list_options` SET `notes` = '1215-3' WHERE `option_id` = 'eastern_delaware' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eastern Delaware
UPDATE `list_options` SET `notes` = '1215-3' WHERE `title` = 'Eastern Delaware' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eastern_muscogee title Eastern Muscogee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eastern_muscogee','Eastern Muscogee','2330', '0',' 1197-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id eastern_muscogee
UPDATE `list_options` SET `notes` = '1197-3' WHERE `option_id` = 'eastern_muscogee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eastern Muscogee
UPDATE `list_options` SET `notes` = '1197-3' WHERE `title` = 'Eastern Muscogee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eastern_pomo title Eastern Pomo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eastern_pomo','Eastern Pomo','2340', '0',' 1467-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id eastern_pomo
UPDATE `list_options` SET `notes` = '1467-0' WHERE `option_id` = 'eastern_pomo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eastern Pomo
UPDATE `list_options` SET `notes` = '1467-0' WHERE `title` = 'Eastern Pomo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eastern_shawnee title Eastern Shawnee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eastern_shawnee','Eastern Shawnee','2350', '0',' 1580-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id eastern_shawnee
UPDATE `list_options` SET `notes` = '1580-0' WHERE `option_id` = 'eastern_shawnee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eastern Shawnee
UPDATE `list_options` SET `notes` = '1580-0' WHERE `title` = 'Eastern Shawnee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eastern_tribes title Eastern Tribes
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eastern_tribes','Eastern Tribes','2360', '0',' 1233-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id eastern_tribes
UPDATE `list_options` SET `notes` = '1233-6' WHERE `option_id` = 'eastern_tribes' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eastern Tribes
UPDATE `list_options` SET `notes` = '1233-6' WHERE `title` = 'Eastern Tribes' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id echota_cherokee title Echota Cherokee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','echota_cherokee','Echota Cherokee','2370', '0',' 1093-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id echota_cherokee
UPDATE `list_options` SET `notes` = '1093-4' WHERE `option_id` = 'echota_cherokee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Echota Cherokee
UPDATE `list_options` SET `notes` = '1093-4' WHERE `title` = 'Echota Cherokee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eek title Eek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eek','Eek','2380', '0',' 1914-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id eek
UPDATE `list_options` SET `notes` = '1914-1' WHERE `option_id` = 'eek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eek
UPDATE `list_options` SET `notes` = '1914-1' WHERE `title` = 'Eek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id egegik title Egegik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','egegik','Egegik','2390', '0',' 1975-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id egegik
UPDATE `list_options` SET `notes` = '1975-2' WHERE `option_id` = 'egegik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Egegik
UPDATE `list_options` SET `notes` = '1975-2' WHERE `title` = 'Egegik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id egyptian title Egyptian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','egyptian','Egyptian','2400', '0',' 2120-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id egyptian
UPDATE `list_options` SET `notes` = '2120-4' WHERE `option_id` = 'egyptian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Egyptian
UPDATE `list_options` SET `notes` = '2120-4' WHERE `title` = 'Egyptian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eklutna title Eklutna
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eklutna','Eklutna','2410', '0',' 1761-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id eklutna
UPDATE `list_options` SET `notes` = '1761-6' WHERE `option_id` = 'eklutna' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eklutna
UPDATE `list_options` SET `notes` = '1761-6' WHERE `title` = 'Eklutna' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ekuk title Ekuk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ekuk','Ekuk','2420', '0',' 1915-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id ekuk
UPDATE `list_options` SET `notes` = '1915-8' WHERE `option_id` = 'ekuk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ekuk
UPDATE `list_options` SET `notes` = '1915-8' WHERE `title` = 'Ekuk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ekwok title Ekwok
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ekwok','Ekwok','2430', '0',' 1916-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id ekwok
UPDATE `list_options` SET `notes` = '1916-6' WHERE `option_id` = 'ekwok' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ekwok
UPDATE `list_options` SET `notes` = '1916-6' WHERE `title` = 'Ekwok' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id elim title Elim
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','elim','Elim','2440', '0',' 1858-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id elim
UPDATE `list_options` SET `notes` = '1858-0' WHERE `option_id` = 'elim' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Elim
UPDATE `list_options` SET `notes` = '1858-0' WHERE `title` = 'Elim' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id elko title Elko
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','elko','Elko','2450', '0',' 1589-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id elko
UPDATE `list_options` SET `notes` = '1589-1' WHERE `option_id` = 'elko' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Elko
UPDATE `list_options` SET `notes` = '1589-1' WHERE `title` = 'Elko' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ely title Ely
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ely','Ely','2460', '0',' 1590-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id ely
UPDATE `list_options` SET `notes` = '1590-9' WHERE `option_id` = 'ely' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ely
UPDATE `list_options` SET `notes` = '1590-9' WHERE `title` = 'Ely' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id emmonak title Emmonak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','emmonak','Emmonak','2470', '0',' 1917-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id emmonak
UPDATE `list_options` SET `notes` = '1917-4' WHERE `option_id` = 'emmonak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Emmonak
UPDATE `list_options` SET `notes` = '1917-4' WHERE `title` = 'Emmonak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id english title English
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','english','English','2480', '0',' 2110-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id english
UPDATE `list_options` SET `notes` = '2110-5' WHERE `option_id` = 'english' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title English
UPDATE `list_options` SET `notes` = '2110-5' WHERE `title` = 'English' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id english_bay title English Bay
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','english_bay','English Bay','2490', '0',' 1987-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id english_bay
UPDATE `list_options` SET `notes` = '1987-7' WHERE `option_id` = 'english_bay' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title English Bay
UPDATE `list_options` SET `notes` = '1987-7' WHERE `title` = 'English Bay' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eskimo title Eskimo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eskimo','Eskimo','2500', '0',' 1840-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id eskimo
UPDATE `list_options` SET `notes` = '1840-8' WHERE `option_id` = 'eskimo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eskimo
UPDATE `list_options` SET `notes` = '1840-8' WHERE `title` = 'Eskimo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id esselen title Esselen
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','esselen','Esselen','2510', '0',' 1250-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id esselen
UPDATE `list_options` SET `notes` = '1250-0' WHERE `option_id` = 'esselen' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Esselen
UPDATE `list_options` SET `notes` = '1250-0' WHERE `title` = 'Esselen' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ethiopian title Ethiopian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ethiopian','Ethiopian','2520', '0',' 2062-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id ethiopian
UPDATE `list_options` SET `notes` = '2062-8' WHERE `option_id` = 'ethiopian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ethiopian
UPDATE `list_options` SET `notes` = '2062-8' WHERE `title` = 'Ethiopian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id etowah_cherokee title Etowah Cherokee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','etowah_cherokee','Etowah Cherokee','2530', '0',' 1094-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id etowah_cherokee
UPDATE `list_options` SET `notes` = '1094-2' WHERE `option_id` = 'etowah_cherokee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Etowah Cherokee
UPDATE `list_options` SET `notes` = '1094-2' WHERE `title` = 'Etowah Cherokee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id european title European
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','european','European','2540', '0',' 2108-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id european
UPDATE `list_options` SET `notes` = '2108-9' WHERE `option_id` = 'european' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title European
UPDATE `list_options` SET `notes` = '2108-9' WHERE `title` = 'European' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id evansville title Evansville
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','evansville','Evansville','2550', '0',' 1762-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id evansville
UPDATE `list_options` SET `notes` = '1762-4' WHERE `option_id` = 'evansville' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Evansville
UPDATE `list_options` SET `notes` = '1762-4' WHERE `title` = 'Evansville' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id eyak title Eyak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','eyak','Eyak','2560', '0',' 1990-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id eyak
UPDATE `list_options` SET `notes` = '1990-1' WHERE `option_id` = 'eyak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Eyak
UPDATE `list_options` SET `notes` = '1990-1' WHERE `title` = 'Eyak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fallon title Fallon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fallon','Fallon','2570', '0',' 1604-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id fallon
UPDATE `list_options` SET `notes` = '1604-8' WHERE `option_id` = 'fallon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fallon
UPDATE `list_options` SET `notes` = '1604-8' WHERE `title` = 'Fallon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id false_pass title False Pass
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','false_pass','False Pass','2580', '0',' 2015-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id false_pass
UPDATE `list_options` SET `notes` = '2015-6' WHERE `option_id` = 'false_pass' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title False Pass
UPDATE `list_options` SET `notes` = '2015-6' WHERE `title` = 'False Pass' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fijian title Fijian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fijian','Fijian','2590', '0',' 2101-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id fijian
UPDATE `list_options` SET `notes` = '2101-4' WHERE `option_id` = 'fijian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fijian
UPDATE `list_options` SET `notes` = '2101-4' WHERE `title` = 'Fijian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id filipino title Filipino
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','filipino','Filipino','2600', '0',' 2036-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id filipino
UPDATE `list_options` SET `notes` = '2036-2' WHERE `option_id` = 'filipino' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Filipino
UPDATE `list_options` SET `notes` = '2036-2' WHERE `title` = 'Filipino' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id flandreau_santee title Flandreau Santee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','flandreau_santee','Flandreau Santee','2610', '0',' 1615-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id flandreau_santee
UPDATE `list_options` SET `notes` = '1615-4' WHERE `option_id` = 'flandreau_santee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Flandreau Santee
UPDATE `list_options` SET `notes` = '1615-4' WHERE `title` = 'Flandreau Santee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id florida_seminole title Florida Seminole
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','florida_seminole','Florida Seminole','2620', '0',' 1569-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id florida_seminole
UPDATE `list_options` SET `notes` = '1569-3' WHERE `option_id` = 'florida_seminole' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Florida Seminole
UPDATE `list_options` SET `notes` = '1569-3' WHERE `title` = 'Florida Seminole' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fond_du_lac title Fond du Lac
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fond_du_lac','Fond du Lac','2630', '0',' 1128-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id fond_du_lac
UPDATE `list_options` SET `notes` = '1128-8' WHERE `option_id` = 'fond_du_lac' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fond du Lac
UPDATE `list_options` SET `notes` = '1128-8' WHERE `title` = 'Fond du Lac' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id forest_county title Forest County
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','forest_county','Forest County','2640', '0',' 1480-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id forest_county
UPDATE `list_options` SET `notes` = '1480-3' WHERE `option_id` = 'forest_county' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Forest County
UPDATE `list_options` SET `notes` = '1480-3' WHERE `title` = 'Forest County' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fort_belknap title Fort Belknap
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fort_belknap','Fort Belknap','2650', '0',' 1252-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id fort_belknap
UPDATE `list_options` SET `notes` = '1252-6' WHERE `option_id` = 'fort_belknap' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fort Belknap
UPDATE `list_options` SET `notes` = '1252-6' WHERE `title` = 'Fort Belknap' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fort_berthold title Fort Berthold
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fort_berthold','Fort Berthold','2660', '0',' 1254-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id fort_berthold
UPDATE `list_options` SET `notes` = '1254-2' WHERE `option_id` = 'fort_berthold' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fort Berthold
UPDATE `list_options` SET `notes` = '1254-2' WHERE `title` = 'Fort Berthold' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fort_bidwell title Fort Bidwell
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fort_bidwell','Fort Bidwell','2670', '0',' 1421-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id fort_bidwell
UPDATE `list_options` SET `notes` = '1421-7' WHERE `option_id` = 'fort_bidwell' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fort Bidwell
UPDATE `list_options` SET `notes` = '1421-7' WHERE `title` = 'Fort Bidwell' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fort_hall title Fort Hall
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fort_hall','Fort Hall','2680', '0',' 1258-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id fort_hall
UPDATE `list_options` SET `notes` = '1258-3' WHERE `option_id` = 'fort_hall' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fort Hall
UPDATE `list_options` SET `notes` = '1258-3' WHERE `title` = 'Fort Hall' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fort_independence title Fort Independence
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fort_independence','Fort Independence','2690', '0',' 1422-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id fort_independence
UPDATE `list_options` SET `notes` = '1422-5' WHERE `option_id` = 'fort_independence' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fort Independence
UPDATE `list_options` SET `notes` = '1422-5' WHERE `title` = 'Fort Independence' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fort_mcdermitt title Fort McDermitt
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fort_mcdermitt','Fort McDermitt','2700', '0',' 1605-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id fort_mcdermitt
UPDATE `list_options` SET `notes` = '1605-5' WHERE `option_id` = 'fort_mcdermitt' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fort McDermitt
UPDATE `list_options` SET `notes` = '1605-5' WHERE `title` = 'Fort McDermitt' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fort_mcdowell title Fort Mcdowell
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fort_mcdowell','Fort Mcdowell','2710', '0',' 1256-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id fort_mcdowell
UPDATE `list_options` SET `notes` = '1256-7' WHERE `option_id` = 'fort_mcdowell' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fort Mcdowell
UPDATE `list_options` SET `notes` = '1256-7' WHERE `title` = 'Fort Mcdowell' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fort_peck title Fort Peck
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fort_peck','Fort Peck','2720', '0',' 1616-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id fort_peck
UPDATE `list_options` SET `notes` = '1616-2' WHERE `option_id` = 'fort_peck' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fort Peck
UPDATE `list_options` SET `notes` = '1616-2' WHERE `title` = 'Fort Peck' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fort_peck_assiniboine_sioux title Fort Peck Assiniboine Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fort_peck_assiniboine_sioux','Fort Peck Assiniboine Sioux','2730', '0',' 1031-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id fort_peck_assiniboine_sioux
UPDATE `list_options` SET `notes` = '1031-4' WHERE `option_id` = 'fort_peck_assiniboine_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fort Peck Assiniboine Sioux
UPDATE `list_options` SET `notes` = '1031-4' WHERE `title` = 'Fort Peck Assiniboine Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fort_sill_apache title Fort Sill Apache
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fort_sill_apache','Fort Sill Apache','2740', '0',' 1012-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id fort_sill_apache
UPDATE `list_options` SET `notes` = '1012-4' WHERE `option_id` = 'fort_sill_apache' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fort Sill Apache
UPDATE `list_options` SET `notes` = '1012-4' WHERE `title` = 'Fort Sill Apache' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id fort_yukon title Fort Yukon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','fort_yukon','Fort Yukon','2750', '0',' 1763-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id fort_yukon
UPDATE `list_options` SET `notes` = '1763-2' WHERE `option_id` = 'fort_yukon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Fort Yukon
UPDATE `list_options` SET `notes` = '1763-2' WHERE `title` = 'Fort Yukon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id french title French
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','french','French','2760', '0',' 2111-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id french
UPDATE `list_options` SET `notes` = '2111-3' WHERE `option_id` = 'french' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title French
UPDATE `list_options` SET `notes` = '2111-3' WHERE `title` = 'French' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id french_american_indian title French American Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','french_american_indian','French American Indian','2770', '0',' 1071-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id french_american_indian
UPDATE `list_options` SET `notes` = '1071-0' WHERE `option_id` = 'french_american_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title French American Indian
UPDATE `list_options` SET `notes` = '1071-0' WHERE `title` = 'French American Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id gabrieleno title Gabrieleno
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','gabrieleno','Gabrieleno','2780', '0',' 1260-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id gabrieleno
UPDATE `list_options` SET `notes` = '1260-9' WHERE `option_id` = 'gabrieleno' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Gabrieleno
UPDATE `list_options` SET `notes` = '1260-9' WHERE `title` = 'Gabrieleno' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id gakona title Gakona
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','gakona','Gakona','2790', '0',' 1764-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id gakona
UPDATE `list_options` SET `notes` = '1764-0' WHERE `option_id` = 'gakona' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Gakona
UPDATE `list_options` SET `notes` = '1764-0' WHERE `title` = 'Gakona' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id galena title Galena
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','galena','Galena','2800', '0',' 1765-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id galena
UPDATE `list_options` SET `notes` = '1765-7' WHERE `option_id` = 'galena' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Galena
UPDATE `list_options` SET `notes` = '1765-7' WHERE `title` = 'Galena' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id gambell title Gambell
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','gambell','Gambell','2810', '0',' 1892-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id gambell
UPDATE `list_options` SET `notes` = '1892-9' WHERE `option_id` = 'gambell' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Gambell
UPDATE `list_options` SET `notes` = '1892-9' WHERE `title` = 'Gambell' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id gay_head_wampanoag title Gay Head Wampanoag
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','gay_head_wampanoag','Gay Head Wampanoag','2820', '0',' 1680-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id gay_head_wampanoag
UPDATE `list_options` SET `notes` = '1680-8' WHERE `option_id` = 'gay_head_wampanoag' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Gay Head Wampanoag
UPDATE `list_options` SET `notes` = '1680-8' WHERE `title` = 'Gay Head Wampanoag' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id georgetown_eastern_tribes title Georgetown (Eastern Tribes)
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','georgetown_eastern_tribes','Georgetown (Eastern Tribes)','2830', '0',' 1236-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id georgetown_eastern_tribes
UPDATE `list_options` SET `notes` = '1236-9' WHERE `option_id` = 'georgetown_eastern_tribes' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Georgetown (Eastern Tribes)
UPDATE `list_options` SET `notes` = '1236-9' WHERE `title` = 'Georgetown (Eastern Tribes)' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id georgetown_yupik-eskimo title Georgetown (Yupik-Eskimo)
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','georgetown_yupik-eskimo','Georgetown (Yupik-Eskimo)','2840', '0',' 1962-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id georgetown_yupik-eskimo
UPDATE `list_options` SET `notes` = '1962-0' WHERE `option_id` = 'georgetown_yupik-eskimo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Georgetown (Yupik-Eskimo)
UPDATE `list_options` SET `notes` = '1962-0' WHERE `title` = 'Georgetown (Yupik-Eskimo)' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id german title German
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','german','German','2850', '0',' 2112-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id german
UPDATE `list_options` SET `notes` = '2112-1' WHERE `option_id` = 'german' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title German
UPDATE `list_options` SET `notes` = '2112-1' WHERE `title` = 'German' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id gila_bend title Gila Bend
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','gila_bend','Gila Bend','2860', '0',' 1655-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id gila_bend
UPDATE `list_options` SET `notes` = '1655-0' WHERE `option_id` = 'gila_bend' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Gila Bend
UPDATE `list_options` SET `notes` = '1655-0' WHERE `title` = 'Gila Bend' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id gila_river_pima-maricopa title Gila River Pima-Maricopa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','gila_river_pima-maricopa','Gila River Pima-Maricopa','2870', '0',' 1457-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id gila_river_pima-maricopa
UPDATE `list_options` SET `notes` = '1457-1' WHERE `option_id` = 'gila_river_pima-maricopa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Gila River Pima-Maricopa
UPDATE `list_options` SET `notes` = '1457-1' WHERE `title` = 'Gila River Pima-Maricopa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id golovin title Golovin
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','golovin','Golovin','2880', '0',' 1859-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id golovin
UPDATE `list_options` SET `notes` = '1859-8' WHERE `option_id` = 'golovin' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Golovin
UPDATE `list_options` SET `notes` = '1859-8' WHERE `title` = 'Golovin' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id goodnews_bay title Goodnews Bay
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','goodnews_bay','Goodnews Bay','2890', '0',' 1918-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id goodnews_bay
UPDATE `list_options` SET `notes` = '1918-2' WHERE `option_id` = 'goodnews_bay' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Goodnews Bay
UPDATE `list_options` SET `notes` = '1918-2' WHERE `title` = 'Goodnews Bay' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id goshute title Goshute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','goshute','Goshute','2900', '0',' 1591-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id goshute
UPDATE `list_options` SET `notes` = '1591-7' WHERE `option_id` = 'goshute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Goshute
UPDATE `list_options` SET `notes` = '1591-7' WHERE `title` = 'Goshute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id grand_portage title Grand Portage
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','grand_portage','Grand Portage','2910', '0',' 1129-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id grand_portage
UPDATE `list_options` SET `notes` = '1129-6' WHERE `option_id` = 'grand_portage' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Grand Portage
UPDATE `list_options` SET `notes` = '1129-6' WHERE `title` = 'Grand Portage' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id grand_ronde title Grand Ronde
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','grand_ronde','Grand Ronde','2920', '0',' 1262-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id grand_ronde
UPDATE `list_options` SET `notes` = '1262-5' WHERE `option_id` = 'grand_ronde' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Grand Ronde
UPDATE `list_options` SET `notes` = '1262-5' WHERE `title` = 'Grand Ronde' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id grand_traverse_band title Grand Traverse Band of Ottawa/Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','grand_traverse_band','Grand Traverse Band of Ottawa/Chippewa','2930', '0',' 1130-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id grand_traverse_band
UPDATE `list_options` SET `notes` = '1130-4' WHERE `option_id` = 'grand_traverse_band' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Grand Traverse Band of Ottawa/Chippewa
UPDATE `list_options` SET `notes` = '1130-4' WHERE `title` = 'Grand Traverse Band of Ottawa/Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id grayling title Grayling
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','grayling','Grayling','2940', '0',' 1766-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id grayling
UPDATE `list_options` SET `notes` = '1766-5' WHERE `option_id` = 'grayling' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Grayling
UPDATE `list_options` SET `notes` = '1766-5' WHERE `title` = 'Grayling' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id greenland_eskimo title Greenland Eskimo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','greenland_eskimo','Greenland Eskimo','2950', '0',' 1842-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id greenland_eskimo
UPDATE `list_options` SET `notes` = '1842-4' WHERE `option_id` = 'greenland_eskimo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Greenland Eskimo
UPDATE `list_options` SET `notes` = '1842-4' WHERE `title` = 'Greenland Eskimo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id gros_ventres title Gros Ventres
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','gros_ventres','Gros Ventres','2960', '0',' 1264-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id gros_ventres
UPDATE `list_options` SET `notes` = '1264-1' WHERE `option_id` = 'gros_ventres' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Gros Ventres
UPDATE `list_options` SET `notes` = '1264-1' WHERE `title` = 'Gros Ventres' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id guamanian title Guamanian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','guamanian','Guamanian','2970', '0',' 2087-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id guamanian
UPDATE `list_options` SET `notes` = '2087-5' WHERE `option_id` = 'guamanian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Guamanian
UPDATE `list_options` SET `notes` = '2087-5' WHERE `title` = 'Guamanian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id guamanian_or_chamorro title Guamanian or Chamorro
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','guamanian_or_chamorro','Guamanian or Chamorro','2980', '0',' 2086-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id guamanian_or_chamorro
UPDATE `list_options` SET `notes` = '2086-7' WHERE `option_id` = 'guamanian_or_chamorro' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Guamanian or Chamorro
UPDATE `list_options` SET `notes` = '2086-7' WHERE `title` = 'Guamanian or Chamorro' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id gulkana title Gulkana
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','gulkana','Gulkana','2990', '0',' 1767-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id gulkana
UPDATE `list_options` SET `notes` = '1767-3' WHERE `option_id` = 'gulkana' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Gulkana
UPDATE `list_options` SET `notes` = '1767-3' WHERE `title` = 'Gulkana' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id haida title Haida
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','haida','Haida','3000', '0',' 1820-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id haida
UPDATE `list_options` SET `notes` = '1820-0' WHERE `option_id` = 'haida' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Haida
UPDATE `list_options` SET `notes` = '1820-0' WHERE `title` = 'Haida' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id haitian title Haitian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','haitian','Haitian','3010', '0',' 2071-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id haitian
UPDATE `list_options` SET `notes` = '2071-9' WHERE `option_id` = 'haitian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Haitian
UPDATE `list_options` SET `notes` = '2071-9' WHERE `title` = 'Haitian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id haliwa title Haliwa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','haliwa','Haliwa','3020', '0',' 1267-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id haliwa
UPDATE `list_options` SET `notes` = '1267-4' WHERE `option_id` = 'haliwa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Haliwa
UPDATE `list_options` SET `notes` = '1267-4' WHERE `title` = 'Haliwa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hannahville title Hannahville
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hannahville','Hannahville','3030', '0',' 1481-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id hannahville
UPDATE `list_options` SET `notes` = '1481-1' WHERE `option_id` = 'hannahville' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hannahville
UPDATE `list_options` SET `notes` = '1481-1' WHERE `title` = 'Hannahville' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id havasupai title Havasupai
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','havasupai','Havasupai','3040', '0',' 1726-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id havasupai
UPDATE `list_options` SET `notes` = '1726-9' WHERE `option_id` = 'havasupai' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Havasupai
UPDATE `list_options` SET `notes` = '1726-9' WHERE `title` = 'Havasupai' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id healy_lake title Healy Lake
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','healy_lake','Healy Lake','3050', '0',' 1768-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id healy_lake
UPDATE `list_options` SET `notes` = '1768-1' WHERE `option_id` = 'healy_lake' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Healy Lake
UPDATE `list_options` SET `notes` = '1768-1' WHERE `title` = 'Healy Lake' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hidatsa title Hidatsa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hidatsa','Hidatsa','3060', '0',' 1269-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id hidatsa
UPDATE `list_options` SET `notes` = '1269-0' WHERE `option_id` = 'hidatsa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hidatsa
UPDATE `list_options` SET `notes` = '1269-0' WHERE `title` = 'Hidatsa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hmong title Hmong
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hmong','Hmong','3070', '0',' 2037-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id hmong
UPDATE `list_options` SET `notes` = '2037-0' WHERE `option_id` = 'hmong' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hmong
UPDATE `list_options` SET `notes` = '2037-0' WHERE `title` = 'Hmong' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ho-chunk title Ho-chunk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ho-chunk','Ho-chunk','3080', '0',' 1697-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id ho-chunk
UPDATE `list_options` SET `notes` = '1697-2' WHERE `option_id` = 'ho-chunk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ho-chunk
UPDATE `list_options` SET `notes` = '1697-2' WHERE `title` = 'Ho-chunk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hoh title Hoh
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hoh','Hoh','3090', '0',' 1083-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id hoh
UPDATE `list_options` SET `notes` = '1083-5' WHERE `option_id` = 'hoh' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hoh
UPDATE `list_options` SET `notes` = '1083-5' WHERE `title` = 'Hoh' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hollywood_seminole title Hollywood Seminole
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hollywood_seminole','Hollywood Seminole','3100', '0',' 1570-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id hollywood_seminole
UPDATE `list_options` SET `notes` = '1570-1' WHERE `option_id` = 'hollywood_seminole' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hollywood Seminole
UPDATE `list_options` SET `notes` = '1570-1' WHERE `title` = 'Hollywood Seminole' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id holy_cross title Holy Cross
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','holy_cross','Holy Cross','3110', '0',' 1769-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id holy_cross
UPDATE `list_options` SET `notes` = '1769-9' WHERE `option_id` = 'holy_cross' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Holy Cross
UPDATE `list_options` SET `notes` = '1769-9' WHERE `title` = 'Holy Cross' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hoonah title Hoonah
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hoonah','Hoonah','3120', '0',' 1821-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id hoonah
UPDATE `list_options` SET `notes` = '1821-8' WHERE `option_id` = 'hoonah' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hoonah
UPDATE `list_options` SET `notes` = '1821-8' WHERE `title` = 'Hoonah' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hoopa title Hoopa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hoopa','Hoopa','3130', '0',' 1271-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id hoopa
UPDATE `list_options` SET `notes` = '1271-6' WHERE `option_id` = 'hoopa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hoopa
UPDATE `list_options` SET `notes` = '1271-6' WHERE `title` = 'Hoopa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hoopa_extension title Hoopa Extension
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hoopa_extension','Hoopa Extension','3140', '0',' 1275-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id hoopa_extension
UPDATE `list_options` SET `notes` = '1275-7' WHERE `option_id` = 'hoopa_extension' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hoopa Extension
UPDATE `list_options` SET `notes` = '1275-7' WHERE `title` = 'Hoopa Extension' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hooper_bay title Hooper Bay
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hooper_bay','Hooper Bay','3150', '0',' 1919-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id hooper_bay
UPDATE `list_options` SET `notes` = '1919-0' WHERE `option_id` = 'hooper_bay' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hooper Bay
UPDATE `list_options` SET `notes` = '1919-0' WHERE `title` = 'Hooper Bay' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hopi title Hopi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hopi','Hopi','3160', '0',' 1493-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id hopi
UPDATE `list_options` SET `notes` = '1493-6' WHERE `option_id` = 'hopi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hopi
UPDATE `list_options` SET `notes` = '1493-6' WHERE `title` = 'Hopi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id houma title Houma
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','houma','Houma','3170', '0',' 1277-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id houma
UPDATE `list_options` SET `notes` = '1277-3' WHERE `option_id` = 'houma' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Houma
UPDATE `list_options` SET `notes` = '1277-3' WHERE `title` = 'Houma' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hualapai title Hualapai
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hualapai','Hualapai','3180', '0',' 1727-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id hualapai
UPDATE `list_options` SET `notes` = '1727-7' WHERE `option_id` = 'hualapai' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hualapai
UPDATE `list_options` SET `notes` = '1727-7' WHERE `title` = 'Hualapai' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hughes title Hughes
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hughes','Hughes','3190', '0',' 1770-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id hughes
UPDATE `list_options` SET `notes` = '1770-7' WHERE `option_id` = 'hughes' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hughes
UPDATE `list_options` SET `notes` = '1770-7' WHERE `title` = 'Hughes' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id huron_potawatomi title Huron Potawatomi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','huron_potawatomi','Huron Potawatomi','3200', '0',' 1482-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id huron_potawatomi
UPDATE `list_options` SET `notes` = '1482-9' WHERE `option_id` = 'huron_potawatomi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Huron Potawatomi
UPDATE `list_options` SET `notes` = '1482-9' WHERE `title` = 'Huron Potawatomi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id huslia title Huslia
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','huslia','Huslia','3210', '0',' 1771-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id huslia
UPDATE `list_options` SET `notes` = '1771-5' WHERE `option_id` = 'huslia' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Huslia
UPDATE `list_options` SET `notes` = '1771-5' WHERE `title` = 'Huslia' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id hydaburg title Hydaburg
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','hydaburg','Hydaburg','3220', '0',' 1822-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id hydaburg
UPDATE `list_options` SET `notes` = '1822-6' WHERE `option_id` = 'hydaburg' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Hydaburg
UPDATE `list_options` SET `notes` = '1822-6' WHERE `title` = 'Hydaburg' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id igiugig title Igiugig
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','igiugig','Igiugig','3230', '0',' 1976-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id igiugig
UPDATE `list_options` SET `notes` = '1976-0' WHERE `option_id` = 'igiugig' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Igiugig
UPDATE `list_options` SET `notes` = '1976-0' WHERE `title` = 'Igiugig' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id iliamna title Iliamna
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','iliamna','Iliamna','3240', '0',' 1772-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id iliamna
UPDATE `list_options` SET `notes` = '1772-3' WHERE `option_id` = 'iliamna' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Iliamna
UPDATE `list_options` SET `notes` = '1772-3' WHERE `title` = 'Iliamna' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id illinois_miami title Illinois Miami
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','illinois_miami','Illinois Miami','3250', '0',' 1359-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id illinois_miami
UPDATE `list_options` SET `notes` = '1359-9' WHERE `option_id` = 'illinois_miami' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Illinois Miami
UPDATE `list_options` SET `notes` = '1359-9' WHERE `title` = 'Illinois Miami' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id inaja-cosmit title Inaja-Cosmit
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','inaja-cosmit','Inaja-Cosmit','3260', '0',' 1279-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id inaja-cosmit
UPDATE `list_options` SET `notes` = '1279-9' WHERE `option_id` = 'inaja-cosmit' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Inaja-Cosmit
UPDATE `list_options` SET `notes` = '1279-9' WHERE `title` = 'Inaja-Cosmit' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id inalik_diomede title Inalik Diomede
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','inalik_diomede','Inalik Diomede','3270', '0',' 1860-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id inalik_diomede
UPDATE `list_options` SET `notes` = '1860-6' WHERE `option_id` = 'inalik_diomede' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Inalik Diomede
UPDATE `list_options` SET `notes` = '1860-6' WHERE `title` = 'Inalik Diomede' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id indian_township title Indian Township
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','indian_township','Indian Township','3280', '0',' 1442-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id indian_township
UPDATE `list_options` SET `notes` = '1442-3' WHERE `option_id` = 'indian_township' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Indian Township
UPDATE `list_options` SET `notes` = '1442-3' WHERE `title` = 'Indian Township' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id indiana_miami title Indiana Miami
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','indiana_miami','Indiana Miami','3290', '0',' 1360-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id indiana_miami
UPDATE `list_options` SET `notes` = '1360-7' WHERE `option_id` = 'indiana_miami' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Indiana Miami
UPDATE `list_options` SET `notes` = '1360-7' WHERE `title` = 'Indiana Miami' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id indonesian title Indonesian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','indonesian','Indonesian','3300', '0',' 2038-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id indonesian
UPDATE `list_options` SET `notes` = '2038-8' WHERE `option_id` = 'indonesian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Indonesian
UPDATE `list_options` SET `notes` = '2038-8' WHERE `title` = 'Indonesian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id inupiaq title Inupiaq
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','inupiaq','Inupiaq','3310', '0',' 1861-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id inupiaq
UPDATE `list_options` SET `notes` = '1861-4' WHERE `option_id` = 'inupiaq' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Inupiaq
UPDATE `list_options` SET `notes` = '1861-4' WHERE `title` = 'Inupiaq' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id inupiat_eskimo title Inupiat Eskimo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','inupiat_eskimo','Inupiat Eskimo','3320', '0',' 1844-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id inupiat_eskimo
UPDATE `list_options` SET `notes` = '1844-0' WHERE `option_id` = 'inupiat_eskimo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Inupiat Eskimo
UPDATE `list_options` SET `notes` = '1844-0' WHERE `title` = 'Inupiat Eskimo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id iowa title Iowa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','iowa','Iowa','3330', '0',' 1281-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id iowa
UPDATE `list_options` SET `notes` = '1281-5' WHERE `option_id` = 'iowa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Iowa
UPDATE `list_options` SET `notes` = '1281-5' WHERE `title` = 'Iowa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id iowa_of_kansas-nebraska title Iowa of Kansas-Nebraska
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','iowa_of_kansas-nebraska','Iowa of Kansas-Nebraska','3340', '0',' 1282-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id iowa_of_kansas-nebraska
UPDATE `list_options` SET `notes` = '1282-3' WHERE `option_id` = 'iowa_of_kansas-nebraska' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Iowa of Kansas-Nebraska
UPDATE `list_options` SET `notes` = '1282-3' WHERE `title` = 'Iowa of Kansas-Nebraska' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id iowa_of_oklahoma title Iowa of Oklahoma
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','iowa_of_oklahoma','Iowa of Oklahoma','3350', '0',' 1283-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id iowa_of_oklahoma
UPDATE `list_options` SET `notes` = '1283-1' WHERE `option_id` = 'iowa_of_oklahoma' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Iowa of Oklahoma
UPDATE `list_options` SET `notes` = '1283-1' WHERE `title` = 'Iowa of Oklahoma' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id iowa_sac_and_fox title Iowa Sac and Fox
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','iowa_sac_and_fox','Iowa Sac and Fox','3360', '0',' 1552-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id iowa_sac_and_fox
UPDATE `list_options` SET `notes` = '1552-9' WHERE `option_id` = 'iowa_sac_and_fox' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Iowa Sac and Fox
UPDATE `list_options` SET `notes` = '1552-9' WHERE `title` = 'Iowa Sac and Fox' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id iqurmuit_russian_mission title Iqurmuit (Russian Mission)
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','iqurmuit_russian_mission','Iqurmuit (Russian Mission)','3370', '0',' 1920-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id iqurmuit_russian_mission
UPDATE `list_options` SET `notes` = '1920-8' WHERE `option_id` = 'iqurmuit_russian_mission' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Iqurmuit (Russian Mission)
UPDATE `list_options` SET `notes` = '1920-8' WHERE `title` = 'Iqurmuit (Russian Mission)' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id iranian title Iranian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','iranian','Iranian','3380', '0',' 2121-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id iranian
UPDATE `list_options` SET `notes` = '2121-2' WHERE `option_id` = 'iranian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Iranian
UPDATE `list_options` SET `notes` = '2121-2' WHERE `title` = 'Iranian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id iraqi title Iraqi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','iraqi','Iraqi','3390', '0',' 2122-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id iraqi
UPDATE `list_options` SET `notes` = '2122-0' WHERE `option_id` = 'iraqi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Iraqi
UPDATE `list_options` SET `notes` = '2122-0' WHERE `title` = 'Iraqi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id irish title Irish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','irish','Irish','3400', '0',' 2113-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id irish
UPDATE `list_options` SET `notes` = '2113-9' WHERE `option_id` = 'irish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Irish
UPDATE `list_options` SET `notes` = '2113-9' WHERE `title` = 'Irish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id iroquois title Iroquois
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','iroquois','Iroquois','3410', '0',' 1285-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id iroquois
UPDATE `list_options` SET `notes` = '1285-6' WHERE `option_id` = 'iroquois' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Iroquois
UPDATE `list_options` SET `notes` = '1285-6' WHERE `title` = 'Iroquois' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id isleta title Isleta
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','isleta','Isleta','3420', '0',' 1494-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id isleta
UPDATE `list_options` SET `notes` = '1494-4' WHERE `option_id` = 'isleta' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Isleta
UPDATE `list_options` SET `notes` = '1494-4' WHERE `title` = 'Isleta' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id israeili title Israeili
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','israeili','Israeili','3430', '0',' 2127-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id israeili
UPDATE `list_options` SET `notes` = '2127-9' WHERE `option_id` = 'israeili' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Israeili
UPDATE `list_options` SET `notes` = '2127-9' WHERE `title` = 'Israeili' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id italian title Italian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','italian','Italian','3440', '0',' 2114-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id italian
UPDATE `list_options` SET `notes` = '2114-7' WHERE `option_id` = 'italian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Italian
UPDATE `list_options` SET `notes` = '2114-7' WHERE `title` = 'Italian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ivanof_bay title Ivanof Bay
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ivanof_bay','Ivanof Bay','3450', '0',' 1977-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id ivanof_bay
UPDATE `list_options` SET `notes` = '1977-8' WHERE `option_id` = 'ivanof_bay' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ivanof Bay
UPDATE `list_options` SET `notes` = '1977-8' WHERE `title` = 'Ivanof Bay' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id iwo_jiman title Iwo Jiman
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','iwo_jiman','Iwo Jiman','3460', '0',' 2048-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id iwo_jiman
UPDATE `list_options` SET `notes` = '2048-7' WHERE `option_id` = 'iwo_jiman' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Iwo Jiman
UPDATE `list_options` SET `notes` = '2048-7' WHERE `title` = 'Iwo Jiman' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id jamaican title Jamaican
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','jamaican','Jamaican','3470', '0',' 2072-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id jamaican
UPDATE `list_options` SET `notes` = '2072-7' WHERE `option_id` = 'jamaican' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Jamaican
UPDATE `list_options` SET `notes` = '2072-7' WHERE `title` = 'Jamaican' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id jamestown title Jamestown
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','jamestown','Jamestown','3480', '0',' 1313-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id jamestown
UPDATE `list_options` SET `notes` = '1313-6' WHERE `option_id` = 'jamestown' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Jamestown
UPDATE `list_options` SET `notes` = '1313-6' WHERE `title` = 'Jamestown' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id japanese title Japanese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','japanese','Japanese','3490', '0',' 2039-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id japanese
UPDATE `list_options` SET `notes` = '2039-6' WHERE `option_id` = 'japanese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Japanese
UPDATE `list_options` SET `notes` = '2039-6' WHERE `title` = 'Japanese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id jemez title Jemez
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','jemez','Jemez','3500', '0',' 1495-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id jemez
UPDATE `list_options` SET `notes` = '1495-1' WHERE `option_id` = 'jemez' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Jemez
UPDATE `list_options` SET `notes` = '1495-1' WHERE `title` = 'Jemez' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id jena_choctaw title Jena Choctaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','jena_choctaw','Jena Choctaw','3510', '0',' 1157-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id jena_choctaw
UPDATE `list_options` SET `notes` = '1157-7' WHERE `option_id` = 'jena_choctaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Jena Choctaw
UPDATE `list_options` SET `notes` = '1157-7' WHERE `title` = 'Jena Choctaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id jicarilla_apache title Jicarilla Apache
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','jicarilla_apache','Jicarilla Apache','3520', '0',' 1013-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id jicarilla_apache
UPDATE `list_options` SET `notes` = '1013-2' WHERE `option_id` = 'jicarilla_apache' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Jicarilla Apache
UPDATE `list_options` SET `notes` = '1013-2' WHERE `title` = 'Jicarilla Apache' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id juaneno title Juaneno
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','juaneno','Juaneno','3530', '0',' 1297-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id juaneno
UPDATE `list_options` SET `notes` = '1297-1' WHERE `option_id` = 'juaneno' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Juaneno
UPDATE `list_options` SET `notes` = '1297-1' WHERE `title` = 'Juaneno' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kaibab title Kaibab
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kaibab','Kaibab','3540', '0',' 1423-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id kaibab
UPDATE `list_options` SET `notes` = '1423-3' WHERE `option_id` = 'kaibab' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kaibab
UPDATE `list_options` SET `notes` = '1423-3' WHERE `title` = 'Kaibab' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kake title Kake
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kake','Kake','3550', '0',' 1823-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id kake
UPDATE `list_options` SET `notes` = '1823-4' WHERE `option_id` = 'kake' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kake
UPDATE `list_options` SET `notes` = '1823-4' WHERE `title` = 'Kake' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kaktovik title Kaktovik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kaktovik','Kaktovik','3560', '0',' 1862-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id kaktovik
UPDATE `list_options` SET `notes` = '1862-2' WHERE `option_id` = 'kaktovik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kaktovik
UPDATE `list_options` SET `notes` = '1862-2' WHERE `title` = 'Kaktovik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kalapuya title Kalapuya
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kalapuya','Kalapuya','3570', '0',' 1395-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id kalapuya
UPDATE `list_options` SET `notes` = '1395-3' WHERE `option_id` = 'kalapuya' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kalapuya
UPDATE `list_options` SET `notes` = '1395-3' WHERE `title` = 'Kalapuya' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kalispel title Kalispel
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kalispel','Kalispel','3580', '0',' 1299-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id kalispel
UPDATE `list_options` SET `notes` = '1299-7' WHERE `option_id` = 'kalispel' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kalispel
UPDATE `list_options` SET `notes` = '1299-7' WHERE `title` = 'Kalispel' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kalskag title Kalskag
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kalskag','Kalskag','3590', '0',' 1921-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id kalskag
UPDATE `list_options` SET `notes` = '1921-6' WHERE `option_id` = 'kalskag' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kalskag
UPDATE `list_options` SET `notes` = '1921-6' WHERE `title` = 'Kalskag' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kaltag title Kaltag
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kaltag','Kaltag','3600', '0',' 1773-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id kaltag
UPDATE `list_options` SET `notes` = '1773-1' WHERE `option_id` = 'kaltag' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kaltag
UPDATE `list_options` SET `notes` = '1773-1' WHERE `title` = 'Kaltag' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id karluk title Karluk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','karluk','Karluk','3610', '0',' 1995-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id karluk
UPDATE `list_options` SET `notes` = '1995-0' WHERE `option_id` = 'karluk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Karluk
UPDATE `list_options` SET `notes` = '1995-0' WHERE `title` = 'Karluk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id karuk title Karuk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','karuk','Karuk','3620', '0',' 1301-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id karuk
UPDATE `list_options` SET `notes` = '1301-1' WHERE `option_id` = 'karuk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Karuk
UPDATE `list_options` SET `notes` = '1301-1' WHERE `title` = 'Karuk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kasaan title Kasaan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kasaan','Kasaan','3630', '0',' 1824-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id kasaan
UPDATE `list_options` SET `notes` = '1824-2' WHERE `option_id` = 'kasaan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kasaan
UPDATE `list_options` SET `notes` = '1824-2' WHERE `title` = 'Kasaan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kashia title Kashia
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kashia','Kashia','3640', '0',' 1468-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id kashia
UPDATE `list_options` SET `notes` = '1468-8' WHERE `option_id` = 'kashia' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kashia
UPDATE `list_options` SET `notes` = '1468-8' WHERE `title` = 'Kashia' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kasigluk title Kasigluk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kasigluk','Kasigluk','3650', '0',' 1922-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id kasigluk
UPDATE `list_options` SET `notes` = '1922-4' WHERE `option_id` = 'kasigluk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kasigluk
UPDATE `list_options` SET `notes` = '1922-4' WHERE `title` = 'Kasigluk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kathlamet title Kathlamet
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kathlamet','Kathlamet','3660', '0',' 1117-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id kathlamet
UPDATE `list_options` SET `notes` = '1117-1' WHERE `option_id` = 'kathlamet' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kathlamet
UPDATE `list_options` SET `notes` = '1117-1' WHERE `title` = 'Kathlamet' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kaw title Kaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kaw','Kaw','3670', '0',' 1303-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id kaw
UPDATE `list_options` SET `notes` = '1303-7' WHERE `option_id` = 'kaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kaw
UPDATE `list_options` SET `notes` = '1303-7' WHERE `title` = 'Kaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kawaiisu title Kawaiisu
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kawaiisu','Kawaiisu','3680', '0',' 1058-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id kawaiisu
UPDATE `list_options` SET `notes` = '1058-7' WHERE `option_id` = 'kawaiisu' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kawaiisu
UPDATE `list_options` SET `notes` = '1058-7' WHERE `title` = 'Kawaiisu' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kawerak title Kawerak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kawerak','Kawerak','3690', '0',' 1863-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id kawerak
UPDATE `list_options` SET `notes` = '1863-0' WHERE `option_id` = 'kawerak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kawerak
UPDATE `list_options` SET `notes` = '1863-0' WHERE `title` = 'Kawerak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kenaitze title Kenaitze
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kenaitze','Kenaitze','3700', '0',' 1825-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id kenaitze
UPDATE `list_options` SET `notes` = '1825-9' WHERE `option_id` = 'kenaitze' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kenaitze
UPDATE `list_options` SET `notes` = '1825-9' WHERE `title` = 'Kenaitze' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id keres title Keres
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','keres','Keres','3710', '0',' 1496-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id keres
UPDATE `list_options` SET `notes` = '1496-9' WHERE `option_id` = 'keres' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Keres
UPDATE `list_options` SET `notes` = '1496-9' WHERE `title` = 'Keres' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kern_river title Kern River
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kern_river','Kern River','3720', '0',' 1059-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id kern_river
UPDATE `list_options` SET `notes` = '1059-5' WHERE `option_id` = 'kern_river' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kern River
UPDATE `list_options` SET `notes` = '1059-5' WHERE `title` = 'Kern River' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ketchikan title Ketchikan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ketchikan','Ketchikan','3730', '0',' 1826-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id ketchikan
UPDATE `list_options` SET `notes` = '1826-7' WHERE `option_id` = 'ketchikan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ketchikan
UPDATE `list_options` SET `notes` = '1826-7' WHERE `title` = 'Ketchikan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id keweenaw title Keweenaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','keweenaw','Keweenaw','3740', '0',' 1131-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id keweenaw
UPDATE `list_options` SET `notes` = '1131-2' WHERE `option_id` = 'keweenaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Keweenaw
UPDATE `list_options` SET `notes` = '1131-2' WHERE `title` = 'Keweenaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kialegee title Kialegee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kialegee','Kialegee','3750', '0',' 1198-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id kialegee
UPDATE `list_options` SET `notes` = '1198-1' WHERE `option_id` = 'kialegee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kialegee
UPDATE `list_options` SET `notes` = '1198-1' WHERE `title` = 'Kialegee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kiana title Kiana
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kiana','Kiana','3760', '0',' 1864-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id kiana
UPDATE `list_options` SET `notes` = '1864-8' WHERE `option_id` = 'kiana' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kiana
UPDATE `list_options` SET `notes` = '1864-8' WHERE `title` = 'Kiana' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kickapoo title Kickapoo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kickapoo','Kickapoo','3770', '0',' 1305-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id kickapoo
UPDATE `list_options` SET `notes` = '1305-2' WHERE `option_id` = 'kickapoo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kickapoo
UPDATE `list_options` SET `notes` = '1305-2' WHERE `title` = 'Kickapoo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kikiallus title Kikiallus
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kikiallus','Kikiallus','3780', '0',' 1520-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id kikiallus
UPDATE `list_options` SET `notes` = '1520-6' WHERE `option_id` = 'kikiallus' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kikiallus
UPDATE `list_options` SET `notes` = '1520-6' WHERE `title` = 'Kikiallus' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id king_cove title King Cove
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','king_cove','King Cove','3790', '0',' 2014-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id king_cove
UPDATE `list_options` SET `notes` = '2014-9' WHERE `option_id` = 'king_cove' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title King Cove
UPDATE `list_options` SET `notes` = '2014-9' WHERE `title` = 'King Cove' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id king_salmon title King Salmon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','king_salmon','King Salmon','3800', '0',' 1978-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id king_salmon
UPDATE `list_options` SET `notes` = '1978-6' WHERE `option_id` = 'king_salmon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title King Salmon
UPDATE `list_options` SET `notes` = '1978-6' WHERE `title` = 'King Salmon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kiowa title Kiowa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kiowa','Kiowa','3810', '0',' 1309-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id kiowa
UPDATE `list_options` SET `notes` = '1309-4' WHERE `option_id` = 'kiowa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kiowa
UPDATE `list_options` SET `notes` = '1309-4' WHERE `title` = 'Kiowa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kipnuk title Kipnuk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kipnuk','Kipnuk','3820', '0',' 1923-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id kipnuk
UPDATE `list_options` SET `notes` = '1923-2' WHERE `option_id` = 'kipnuk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kipnuk
UPDATE `list_options` SET `notes` = '1923-2' WHERE `title` = 'Kipnuk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kiribati title Kiribati
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kiribati','Kiribati','3830', '0',' 2096-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id kiribati
UPDATE `list_options` SET `notes` = '2096-6' WHERE `option_id` = 'kiribati' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kiribati
UPDATE `list_options` SET `notes` = '2096-6' WHERE `title` = 'Kiribati' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kivalina title Kivalina
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kivalina','Kivalina','3840', '0',' 1865-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id kivalina
UPDATE `list_options` SET `notes` = '1865-5' WHERE `option_id` = 'kivalina' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kivalina
UPDATE `list_options` SET `notes` = '1865-5' WHERE `title` = 'Kivalina' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id klallam title Klallam
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','klallam','Klallam','3850', '0',' 1312-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id klallam
UPDATE `list_options` SET `notes` = '1312-8' WHERE `option_id` = 'klallam' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Klallam
UPDATE `list_options` SET `notes` = '1312-8' WHERE `title` = 'Klallam' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id klamath title Klamath
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','klamath','Klamath','3860', '0',' 1317-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id klamath
UPDATE `list_options` SET `notes` = '1317-7' WHERE `option_id` = 'klamath' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Klamath
UPDATE `list_options` SET `notes` = '1317-7' WHERE `title` = 'Klamath' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id klawock title Klawock
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','klawock','Klawock','3870', '0',' 1827-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id klawock
UPDATE `list_options` SET `notes` = '1827-5' WHERE `option_id` = 'klawock' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Klawock
UPDATE `list_options` SET `notes` = '1827-5' WHERE `title` = 'Klawock' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kluti_kaah title Kluti Kaah
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kluti_kaah','Kluti Kaah','3880', '0',' 1774-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id kluti_kaah
UPDATE `list_options` SET `notes` = '1774-9' WHERE `option_id` = 'kluti_kaah' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kluti Kaah
UPDATE `list_options` SET `notes` = '1774-9' WHERE `title` = 'Kluti Kaah' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id knik title Knik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','knik','Knik','3890', '0',' 1775-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id knik
UPDATE `list_options` SET `notes` = '1775-6' WHERE `option_id` = 'knik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Knik
UPDATE `list_options` SET `notes` = '1775-6' WHERE `title` = 'Knik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kobuk title Kobuk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kobuk','Kobuk','3900', '0',' 1866-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id kobuk
UPDATE `list_options` SET `notes` = '1866-3' WHERE `option_id` = 'kobuk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kobuk
UPDATE `list_options` SET `notes` = '1866-3' WHERE `title` = 'Kobuk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kodiak title Kodiak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kodiak','Kodiak','3910', '0',' 1996-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id kodiak
UPDATE `list_options` SET `notes` = '1996-8' WHERE `option_id` = 'kodiak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kodiak
UPDATE `list_options` SET `notes` = '1996-8' WHERE `title` = 'Kodiak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kokhanok title Kokhanok
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kokhanok','Kokhanok','3920', '0',' 1979-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id kokhanok
UPDATE `list_options` SET `notes` = '1979-4' WHERE `option_id` = 'kokhanok' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kokhanok
UPDATE `list_options` SET `notes` = '1979-4' WHERE `title` = 'Kokhanok' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id koliganek title Koliganek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','koliganek','Koliganek','3930', '0',' 1924-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id koliganek
UPDATE `list_options` SET `notes` = '1924-0' WHERE `option_id` = 'koliganek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Koliganek
UPDATE `list_options` SET `notes` = '1924-0' WHERE `title` = 'Koliganek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kongiganak title Kongiganak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kongiganak','Kongiganak','3940', '0',' 1925-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id kongiganak
UPDATE `list_options` SET `notes` = '1925-7' WHERE `option_id` = 'kongiganak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kongiganak
UPDATE `list_options` SET `notes` = '1925-7' WHERE `title` = 'Kongiganak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id koniag_aleut title Koniag Aleut
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','koniag_aleut','Koniag Aleut','3950', '0',' 1992-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id koniag_aleut
UPDATE `list_options` SET `notes` = '1992-7' WHERE `option_id` = 'koniag_aleut' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Koniag Aleut
UPDATE `list_options` SET `notes` = '1992-7' WHERE `title` = 'Koniag Aleut' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id konkow title Konkow
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','konkow','Konkow','3960', '0',' 1319-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id konkow
UPDATE `list_options` SET `notes` = '1319-3' WHERE `option_id` = 'konkow' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Konkow
UPDATE `list_options` SET `notes` = '1319-3' WHERE `title` = 'Konkow' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kootenai title Kootenai
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kootenai','Kootenai','3970', '0',' 1321-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id kootenai
UPDATE `list_options` SET `notes` = '1321-9' WHERE `option_id` = 'kootenai' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kootenai
UPDATE `list_options` SET `notes` = '1321-9' WHERE `title` = 'Kootenai' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id korean title Korean
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','korean','Korean','3980', '0',' 2040-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id korean
UPDATE `list_options` SET `notes` = '2040-4' WHERE `option_id` = 'korean' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Korean
UPDATE `list_options` SET `notes` = '2040-4' WHERE `title` = 'Korean' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kosraean title Kosraean
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kosraean','Kosraean','3990', '0',' 2093-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id kosraean
UPDATE `list_options` SET `notes` = '2093-3' WHERE `option_id` = 'kosraean' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kosraean
UPDATE `list_options` SET `notes` = '2093-3' WHERE `title` = 'Kosraean' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kotlik title Kotlik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kotlik','Kotlik','4000', '0',' 1926-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id kotlik
UPDATE `list_options` SET `notes` = '1926-5' WHERE `option_id` = 'kotlik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kotlik
UPDATE `list_options` SET `notes` = '1926-5' WHERE `title` = 'Kotlik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kotzebue title Kotzebue
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kotzebue','Kotzebue','4010', '0',' 1867-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id kotzebue
UPDATE `list_options` SET `notes` = '1867-1' WHERE `option_id` = 'kotzebue' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kotzebue
UPDATE `list_options` SET `notes` = '1867-1' WHERE `title` = 'Kotzebue' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id koyuk title Koyuk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','koyuk','Koyuk','4020', '0',' 1868-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id koyuk
UPDATE `list_options` SET `notes` = '1868-9' WHERE `option_id` = 'koyuk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Koyuk
UPDATE `list_options` SET `notes` = '1868-9' WHERE `title` = 'Koyuk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id koyukuk title Koyukuk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','koyukuk','Koyukuk','4030', '0',' 1776-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id koyukuk
UPDATE `list_options` SET `notes` = '1776-4' WHERE `option_id` = 'koyukuk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Koyukuk
UPDATE `list_options` SET `notes` = '1776-4' WHERE `title` = 'Koyukuk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kwethluk title Kwethluk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kwethluk','Kwethluk','4040', '0',' 1927-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id kwethluk
UPDATE `list_options` SET `notes` = '1927-3' WHERE `option_id` = 'kwethluk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kwethluk
UPDATE `list_options` SET `notes` = '1927-3' WHERE `title` = 'Kwethluk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kwigillingok title Kwigillingok
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kwigillingok','Kwigillingok','4050', '0',' 1928-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id kwigillingok
UPDATE `list_options` SET `notes` = '1928-1' WHERE `option_id` = 'kwigillingok' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kwigillingok
UPDATE `list_options` SET `notes` = '1928-1' WHERE `title` = 'Kwigillingok' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id kwiguk title Kwiguk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','kwiguk','Kwiguk','4060', '0',' 1869-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id kwiguk
UPDATE `list_options` SET `notes` = '1869-7' WHERE `option_id` = 'kwiguk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Kwiguk
UPDATE `list_options` SET `notes` = '1869-7' WHERE `title` = 'Kwiguk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id la_jolla title La Jolla
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','la_jolla','La Jolla','4070', '0',' 1332-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id la_jolla
UPDATE `list_options` SET `notes` = '1332-6' WHERE `option_id` = 'la_jolla' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title La Jolla
UPDATE `list_options` SET `notes` = '1332-6' WHERE `title` = 'La Jolla' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id la_posta title La Posta
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','la_posta','La Posta','4080', '0',' 1226-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id la_posta
UPDATE `list_options` SET `notes` = '1226-0' WHERE `option_id` = 'la_posta' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title La Posta
UPDATE `list_options` SET `notes` = '1226-0' WHERE `title` = 'La Posta' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lac_courte_oreilles title Lac Courte Oreilles
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lac_courte_oreilles','Lac Courte Oreilles','4090', '0',' 1132-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id lac_courte_oreilles
UPDATE `list_options` SET `notes` = '1132-0' WHERE `option_id` = 'lac_courte_oreilles' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lac Courte Oreilles
UPDATE `list_options` SET `notes` = '1132-0' WHERE `title` = 'Lac Courte Oreilles' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lac_du_flambeau title Lac du Flambeau
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lac_du_flambeau','Lac du Flambeau','4100', '0',' 1133-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id lac_du_flambeau
UPDATE `list_options` SET `notes` = '1133-8' WHERE `option_id` = 'lac_du_flambeau' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lac du Flambeau
UPDATE `list_options` SET `notes` = '1133-8' WHERE `title` = 'Lac du Flambeau' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lac_vieux_desert_chippewa title Lac Vieux Desert Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lac_vieux_desert_chippewa','Lac Vieux Desert Chippewa','4110', '0',' 1134-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id lac_vieux_desert_chippewa
UPDATE `list_options` SET `notes` = '1134-6' WHERE `option_id` = 'lac_vieux_desert_chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lac Vieux Desert Chippewa
UPDATE `list_options` SET `notes` = '1134-6' WHERE `title` = 'Lac Vieux Desert Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id laguna title Laguna
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','laguna','Laguna','4120', '0',' 1497-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id laguna
UPDATE `list_options` SET `notes` = '1497-7' WHERE `option_id` = 'laguna' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Laguna
UPDATE `list_options` SET `notes` = '1497-7' WHERE `title` = 'Laguna' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lake_minchumina title Lake Minchumina
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lake_minchumina','Lake Minchumina','4130', '0',' 1777-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id lake_minchumina
UPDATE `list_options` SET `notes` = '1777-2' WHERE `option_id` = 'lake_minchumina' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lake Minchumina
UPDATE `list_options` SET `notes` = '1777-2' WHERE `title` = 'Lake Minchumina' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lake_superior title Lake Superior
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lake_superior','Lake Superior','4140', '0',' 1135-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id lake_superior
UPDATE `list_options` SET `notes` = '1135-3' WHERE `option_id` = 'lake_superior' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lake Superior
UPDATE `list_options` SET `notes` = '1135-3' WHERE `title` = 'Lake Superior' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lake_traverse_sioux title Lake Traverse Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lake_traverse_sioux','Lake Traverse Sioux','4150', '0',' 1617-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id lake_traverse_sioux
UPDATE `list_options` SET `notes` = '1617-0' WHERE `option_id` = 'lake_traverse_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lake Traverse Sioux
UPDATE `list_options` SET `notes` = '1617-0' WHERE `title` = 'Lake Traverse Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id laotian title Laotian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','laotian','Laotian','4160', '0',' 2041-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id laotian
UPDATE `list_options` SET `notes` = '2041-2' WHERE `option_id` = 'laotian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Laotian
UPDATE `list_options` SET `notes` = '2041-2' WHERE `title` = 'Laotian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id larsen_bay title Larsen Bay
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','larsen_bay','Larsen Bay','4170', '0',' 1997-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id larsen_bay
UPDATE `list_options` SET `notes` = '1997-6' WHERE `option_id` = 'larsen_bay' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Larsen Bay
UPDATE `list_options` SET `notes` = '1997-6' WHERE `title` = 'Larsen Bay' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id las_vegas title Las Vegas
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','las_vegas','Las Vegas','4180', '0',' 1424-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id las_vegas
UPDATE `list_options` SET `notes` = '1424-1' WHERE `option_id` = 'las_vegas' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Las Vegas
UPDATE `list_options` SET `notes` = '1424-1' WHERE `title` = 'Las Vegas' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lassik title Lassik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lassik','Lassik','4190', '0',' 1323-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id lassik
UPDATE `list_options` SET `notes` = '1323-5' WHERE `option_id` = 'lassik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lassik
UPDATE `list_options` SET `notes` = '1323-5' WHERE `title` = 'Lassik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lebanese title Lebanese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lebanese','Lebanese','4200', '0',' 2123-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id lebanese
UPDATE `list_options` SET `notes` = '2123-8' WHERE `option_id` = 'lebanese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lebanese
UPDATE `list_options` SET `notes` = '2123-8' WHERE `title` = 'Lebanese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id leech_lake title Leech Lake
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','leech_lake','Leech Lake','4210', '0',' 1136-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id leech_lake
UPDATE `list_options` SET `notes` = '1136-1' WHERE `option_id` = 'leech_lake' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Leech Lake
UPDATE `list_options` SET `notes` = '1136-1' WHERE `title` = 'Leech Lake' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lenni-lenape title Lenni-Lenape
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lenni-lenape','Lenni-Lenape','4220', '0',' 1216-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id lenni-lenape
UPDATE `list_options` SET `notes` = '1216-1' WHERE `option_id` = 'lenni-lenape' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lenni-Lenape
UPDATE `list_options` SET `notes` = '1216-1' WHERE `title` = 'Lenni-Lenape' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id levelock title Levelock
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','levelock','Levelock','4230', '0',' 1929-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id levelock
UPDATE `list_options` SET `notes` = '1929-9' WHERE `option_id` = 'levelock' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Levelock
UPDATE `list_options` SET `notes` = '1929-9' WHERE `title` = 'Levelock' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id liberian title Liberian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','liberian','Liberian','4240', '0',' 2063-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id liberian
UPDATE `list_options` SET `notes` = '2063-6' WHERE `option_id` = 'liberian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Liberian
UPDATE `list_options` SET `notes` = '2063-6' WHERE `title` = 'Liberian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lime title Lime
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lime','Lime','4250', '0',' 1778-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id lime
UPDATE `list_options` SET `notes` = '1778-0' WHERE `option_id` = 'lime' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lime
UPDATE `list_options` SET `notes` = '1778-0' WHERE `title` = 'Lime' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lipan_apache title Lipan Apache
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lipan_apache','Lipan Apache','4260', '0',' 1014-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id lipan_apache
UPDATE `list_options` SET `notes` = '1014-0' WHERE `option_id` = 'lipan_apache' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lipan Apache
UPDATE `list_options` SET `notes` = '1014-0' WHERE `title` = 'Lipan Apache' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id little_shell_chippewa title Little Shell Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','little_shell_chippewa','Little Shell Chippewa','4270', '0',' 1137-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id little_shell_chippewa
UPDATE `list_options` SET `notes` = '1137-9' WHERE `option_id` = 'little_shell_chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Little Shell Chippewa
UPDATE `list_options` SET `notes` = '1137-9' WHERE `title` = 'Little Shell Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lone_pine title Lone Pine
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lone_pine','Lone Pine','4280', '0',' 1425-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id lone_pine
UPDATE `list_options` SET `notes` = '1425-8' WHERE `option_id` = 'lone_pine' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lone Pine
UPDATE `list_options` SET `notes` = '1425-8' WHERE `title` = 'Lone Pine' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id long_island title Long Island
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','long_island','Long Island','4290', '0',' 1325-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id long_island
UPDATE `list_options` SET `notes` = '1325-0' WHERE `option_id` = 'long_island' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Long Island
UPDATE `list_options` SET `notes` = '1325-0' WHERE `title` = 'Long Island' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id los_coyotes title Los Coyotes
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','los_coyotes','Los Coyotes','4300', '0',' 1048-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id los_coyotes
UPDATE `list_options` SET `notes` = '1048-8' WHERE `option_id` = 'los_coyotes' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Los Coyotes
UPDATE `list_options` SET `notes` = '1048-8' WHERE `title` = 'Los Coyotes' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lovelock title Lovelock
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lovelock','Lovelock','4310', '0',' 1426-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id lovelock
UPDATE `list_options` SET `notes` = '1426-6' WHERE `option_id` = 'lovelock' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lovelock
UPDATE `list_options` SET `notes` = '1426-6' WHERE `title` = 'Lovelock' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lower_brule_sioux title Lower Brule Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lower_brule_sioux','Lower Brule Sioux','4320', '0',' 1618-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id lower_brule_sioux
UPDATE `list_options` SET `notes` = '1618-8' WHERE `option_id` = 'lower_brule_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lower Brule Sioux
UPDATE `list_options` SET `notes` = '1618-8' WHERE `title` = 'Lower Brule Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lower_elwha title Lower Elwha
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lower_elwha','Lower Elwha','4330', '0',' 1314-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id lower_elwha
UPDATE `list_options` SET `notes` = '1314-4' WHERE `option_id` = 'lower_elwha' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lower Elwha
UPDATE `list_options` SET `notes` = '1314-4' WHERE `title` = 'Lower Elwha' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lower_kalskag title Lower Kalskag
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lower_kalskag','Lower Kalskag','4340', '0',' 1930-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id lower_kalskag
UPDATE `list_options` SET `notes` = '1930-7' WHERE `option_id` = 'lower_kalskag' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lower Kalskag
UPDATE `list_options` SET `notes` = '1930-7' WHERE `title` = 'Lower Kalskag' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lower_muscogee title Lower Muscogee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lower_muscogee','Lower Muscogee','4350', '0',' 1199-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id lower_muscogee
UPDATE `list_options` SET `notes` = '1199-9' WHERE `option_id` = 'lower_muscogee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lower Muscogee
UPDATE `list_options` SET `notes` = '1199-9' WHERE `title` = 'Lower Muscogee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lower_sioux title Lower Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lower_sioux','Lower Sioux','4360', '0',' 1619-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id lower_sioux
UPDATE `list_options` SET `notes` = '1619-6' WHERE `option_id` = 'lower_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lower Sioux
UPDATE `list_options` SET `notes` = '1619-6' WHERE `title` = 'Lower Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lower_skagit title Lower Skagit
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lower_skagit','Lower Skagit','4370', '0',' 1521-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id lower_skagit
UPDATE `list_options` SET `notes` = '1521-4' WHERE `option_id` = 'lower_skagit' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lower Skagit
UPDATE `list_options` SET `notes` = '1521-4' WHERE `title` = 'Lower Skagit' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id luiseno title Luiseno
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','luiseno','Luiseno','4380', '0',' 1331-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id luiseno
UPDATE `list_options` SET `notes` = '1331-8' WHERE `option_id` = 'luiseno' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Luiseno
UPDATE `list_options` SET `notes` = '1331-8' WHERE `title` = 'Luiseno' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lumbee title Lumbee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lumbee','Lumbee','4390', '0',' 1340-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id lumbee
UPDATE `list_options` SET `notes` = '1340-9' WHERE `option_id` = 'lumbee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lumbee
UPDATE `list_options` SET `notes` = '1340-9' WHERE `title` = 'Lumbee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id lummi title Lummi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','lummi','Lummi','4400', '0',' 1342-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id lummi
UPDATE `list_options` SET `notes` = '1342-5' WHERE `option_id` = 'lummi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Lummi
UPDATE `list_options` SET `notes` = '1342-5' WHERE `title` = 'Lummi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id machis_lower_creek_indian title Machis Lower Creek Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','machis_lower_creek_indian','Machis Lower Creek Indian','4410', '0',' 1200-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id machis_lower_creek_indian
UPDATE `list_options` SET `notes` = '1200-5' WHERE `option_id` = 'machis_lower_creek_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Machis Lower Creek Indian
UPDATE `list_options` SET `notes` = '1200-5' WHERE `title` = 'Machis Lower Creek Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id madagascar title Madagascar
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','madagascar','Madagascar','4420', '0',' 2052-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id madagascar
UPDATE `list_options` SET `notes` = '2052-9' WHERE `option_id` = 'madagascar' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Madagascar
UPDATE `list_options` SET `notes` = '2052-9' WHERE `title` = 'Madagascar' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id maidu title Maidu
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','maidu','Maidu','4430', '0',' 1344-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id maidu
UPDATE `list_options` SET `notes` = '1344-1' WHERE `option_id` = 'maidu' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Maidu
UPDATE `list_options` SET `notes` = '1344-1' WHERE `title` = 'Maidu' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id makah title Makah
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','makah','Makah','4440', '0',' 1348-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id makah
UPDATE `list_options` SET `notes` = '1348-2' WHERE `option_id` = 'makah' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Makah
UPDATE `list_options` SET `notes` = '1348-2' WHERE `title` = 'Makah' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id malaysian title Malaysian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','malaysian','Malaysian','4450', '0',' 2042-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id malaysian
UPDATE `list_options` SET `notes` = '2042-0' WHERE `option_id` = 'malaysian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Malaysian
UPDATE `list_options` SET `notes` = '2042-0' WHERE `title` = 'Malaysian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id maldivian title Maldivian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','maldivian','Maldivian','4460', '0',' 2049-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id maldivian
UPDATE `list_options` SET `notes` = '2049-5' WHERE `option_id` = 'maldivian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Maldivian
UPDATE `list_options` SET `notes` = '2049-5' WHERE `title` = 'Maldivian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id malheur_paiute title Malheur Paiute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','malheur_paiute','Malheur Paiute','4470', '0',' 1427-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id malheur_paiute
UPDATE `list_options` SET `notes` = '1427-4' WHERE `option_id` = 'malheur_paiute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Malheur Paiute
UPDATE `list_options` SET `notes` = '1427-4' WHERE `title` = 'Malheur Paiute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id maliseet title Maliseet
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','maliseet','Maliseet','4480', '0',' 1350-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id maliseet
UPDATE `list_options` SET `notes` = '1350-8' WHERE `option_id` = 'maliseet' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Maliseet
UPDATE `list_options` SET `notes` = '1350-8' WHERE `title` = 'Maliseet' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mandan title Mandan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mandan','Mandan','4490', '0',' 1352-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id mandan
UPDATE `list_options` SET `notes` = '1352-4' WHERE `option_id` = 'mandan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mandan
UPDATE `list_options` SET `notes` = '1352-4' WHERE `title` = 'Mandan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id manley_hot_springs title Manley Hot Springs
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','manley_hot_springs','Manley Hot Springs','4500', '0',' 1780-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id manley_hot_springs
UPDATE `list_options` SET `notes` = '1780-6' WHERE `option_id` = 'manley_hot_springs' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Manley Hot Springs
UPDATE `list_options` SET `notes` = '1780-6' WHERE `title` = 'Manley Hot Springs' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id manokotak title Manokotak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','manokotak','Manokotak','4510', '0',' 1931-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id manokotak
UPDATE `list_options` SET `notes` = '1931-5' WHERE `option_id` = 'manokotak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Manokotak
UPDATE `list_options` SET `notes` = '1931-5' WHERE `title` = 'Manokotak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id manzanita title Manzanita
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','manzanita','Manzanita','4520', '0',' 1227-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id manzanita
UPDATE `list_options` SET `notes` = '1227-8' WHERE `option_id` = 'manzanita' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Manzanita
UPDATE `list_options` SET `notes` = '1227-8' WHERE `title` = 'Manzanita' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mariana_islander title Mariana Islander
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mariana_islander','Mariana Islander','4530', '0',' 2089-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id mariana_islander
UPDATE `list_options` SET `notes` = '2089-1' WHERE `option_id` = 'mariana_islander' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mariana Islander
UPDATE `list_options` SET `notes` = '2089-1' WHERE `title` = 'Mariana Islander' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id maricopa title Maricopa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','maricopa','Maricopa','4540', '0',' 1728-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id maricopa
UPDATE `list_options` SET `notes` = '1728-5' WHERE `option_id` = 'maricopa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Maricopa
UPDATE `list_options` SET `notes` = '1728-5' WHERE `title` = 'Maricopa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id marshall title Marshall
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','marshall','Marshall','4550', '0',' 1932-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id marshall
UPDATE `list_options` SET `notes` = '1932-3' WHERE `option_id` = 'marshall' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Marshall
UPDATE `list_options` SET `notes` = '1932-3' WHERE `title` = 'Marshall' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id marshallese title Marshallese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','marshallese','Marshallese','4560', '0',' 2090-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id marshallese
UPDATE `list_options` SET `notes` = '2090-9' WHERE `option_id` = 'marshallese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Marshallese
UPDATE `list_options` SET `notes` = '2090-9' WHERE `title` = 'Marshallese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id marshantucket_pequot title Marshantucket Pequot
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','marshantucket_pequot','Marshantucket Pequot','4570', '0',' 1454-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id marshantucket_pequot
UPDATE `list_options` SET `notes` = '1454-8' WHERE `option_id` = 'marshantucket_pequot' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Marshantucket Pequot
UPDATE `list_options` SET `notes` = '1454-8' WHERE `title` = 'Marshantucket Pequot' AND `list_id` = 'race';
#EndIf

#IfNotRow2D list_options list_id race option_id marys_igloo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','marys_igloo',"Mary's Igloo",'4580', '0',' 1889-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id marys_igloo
UPDATE `list_options` SET `notes` = '1889-5' WHERE `option_id` = 'marys_igloo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mashpee_wampanoag title Mashpee Wampanoag
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mashpee_wampanoag','Mashpee Wampanoag','4590', '0',' 1681-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id mashpee_wampanoag
UPDATE `list_options` SET `notes` = '1681-6' WHERE `option_id` = 'mashpee_wampanoag' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mashpee Wampanoag
UPDATE `list_options` SET `notes` = '1681-6' WHERE `title` = 'Mashpee Wampanoag' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id matinecock title Matinecock
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','matinecock','Matinecock','4600', '0',' 1326-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id matinecock
UPDATE `list_options` SET `notes` = '1326-8' WHERE `option_id` = 'matinecock' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Matinecock
UPDATE `list_options` SET `notes` = '1326-8' WHERE `title` = 'Matinecock' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mattaponi title Mattaponi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mattaponi','Mattaponi','4610', '0',' 1354-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id mattaponi
UPDATE `list_options` SET `notes` = '1354-0' WHERE `option_id` = 'mattaponi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mattaponi
UPDATE `list_options` SET `notes` = '1354-0' WHERE `title` = 'Mattaponi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mattole title Mattole
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mattole','Mattole','4620', '0',' 1060-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id mattole
UPDATE `list_options` SET `notes` = '1060-3' WHERE `option_id` = 'mattole' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mattole
UPDATE `list_options` SET `notes` = '1060-3' WHERE `title` = 'Mattole' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mauneluk_inupiat title Mauneluk Inupiat
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mauneluk_inupiat','Mauneluk Inupiat','4630', '0',' 1870-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id mauneluk_inupiat
UPDATE `list_options` SET `notes` = '1870-5' WHERE `option_id` = 'mauneluk_inupiat' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mauneluk Inupiat
UPDATE `list_options` SET `notes` = '1870-5' WHERE `title` = 'Mauneluk Inupiat' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mcgrath title Mcgrath
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mcgrath','Mcgrath','4640', '0',' 1779-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id mcgrath
UPDATE `list_options` SET `notes` = '1779-8' WHERE `option_id` = 'mcgrath' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mcgrath
UPDATE `list_options` SET `notes` = '1779-8' WHERE `title` = 'Mcgrath' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mdewakanton_sioux title Mdewakanton Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mdewakanton_sioux','Mdewakanton Sioux','4650', '0',' 1620-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id mdewakanton_sioux
UPDATE `list_options` SET `notes` = '1620-4' WHERE `option_id` = 'mdewakanton_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mdewakanton Sioux
UPDATE `list_options` SET `notes` = '1620-4' WHERE `title` = 'Mdewakanton Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mekoryuk title Mekoryuk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mekoryuk','Mekoryuk','4660', '0',' 1933-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id mekoryuk
UPDATE `list_options` SET `notes` = '1933-1' WHERE `option_id` = 'mekoryuk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mekoryuk
UPDATE `list_options` SET `notes` = '1933-1' WHERE `title` = 'Mekoryuk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id melanesian title Melanesian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','melanesian','Melanesian','4670', '0',' 2100-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id melanesian
UPDATE `list_options` SET `notes` = '2100-6' WHERE `option_id` = 'melanesian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Melanesian
UPDATE `list_options` SET `notes` = '2100-6' WHERE `title` = 'Melanesian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id menominee title Menominee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','menominee','Menominee','4680', '0',' 1356-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id menominee
UPDATE `list_options` SET `notes` = '1356-5' WHERE `option_id` = 'menominee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Menominee
UPDATE `list_options` SET `notes` = '1356-5' WHERE `title` = 'Menominee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mentasta_lake title Mentasta Lake
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mentasta_lake','Mentasta Lake','4690', '0',' 1781-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id mentasta_lake
UPDATE `list_options` SET `notes` = '1781-4' WHERE `option_id` = 'mentasta_lake' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mentasta Lake
UPDATE `list_options` SET `notes` = '1781-4' WHERE `title` = 'Mentasta Lake' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mesa_grande title Mesa Grande
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mesa_grande','Mesa Grande','4700', '0',' 1228-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id mesa_grande
UPDATE `list_options` SET `notes` = '1228-6' WHERE `option_id` = 'mesa_grande' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mesa Grande
UPDATE `list_options` SET `notes` = '1228-6' WHERE `title` = 'Mesa Grande' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mescalero_apache title Mescalero Apache
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mescalero_apache','Mescalero Apache','4710', '0',' 1015-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id mescalero_apache
UPDATE `list_options` SET `notes` = '1015-7' WHERE `option_id` = 'mescalero_apache' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mescalero Apache
UPDATE `list_options` SET `notes` = '1015-7' WHERE `title` = 'Mescalero Apache' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id metlakatla title Metlakatla
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','metlakatla','Metlakatla','4720', '0',' 1838-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id metlakatla
UPDATE `list_options` SET `notes` = '1838-2' WHERE `option_id` = 'metlakatla' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Metlakatla
UPDATE `list_options` SET `notes` = '1838-2' WHERE `title` = 'Metlakatla' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mexican_american_indian title Mexican American Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mexican_american_indian','Mexican American Indian','4730', '0',' 1072-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id mexican_american_indian
UPDATE `list_options` SET `notes` = '1072-8' WHERE `option_id` = 'mexican_american_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mexican American Indian
UPDATE `list_options` SET `notes` = '1072-8' WHERE `title` = 'Mexican American Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id miami title Miami
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','miami','Miami','4740', '0',' 1358-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id miami
UPDATE `list_options` SET `notes` = '1358-1' WHERE `option_id` = 'miami' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Miami
UPDATE `list_options` SET `notes` = '1358-1' WHERE `title` = 'Miami' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id miccosukee title Miccosukee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','miccosukee','Miccosukee','4750', '0',' 1363-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id miccosukee
UPDATE `list_options` SET `notes` = '1363-1' WHERE `option_id` = 'miccosukee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Miccosukee
UPDATE `list_options` SET `notes` = '1363-1' WHERE `title` = 'Miccosukee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id michigan_ottawa title Michigan Ottawa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','michigan_ottawa','Michigan Ottawa','4760', '0',' 1413-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id michigan_ottawa
UPDATE `list_options` SET `notes` = '1413-4' WHERE `option_id` = 'michigan_ottawa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Michigan Ottawa
UPDATE `list_options` SET `notes` = '1413-4' WHERE `title` = 'Michigan Ottawa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id micmac title Micmac
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','micmac','Micmac','4770', '0',' 1365-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id micmac
UPDATE `list_options` SET `notes` = '1365-6' WHERE `option_id` = 'micmac' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Micmac
UPDATE `list_options` SET `notes` = '1365-6' WHERE `title` = 'Micmac' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id micronesian title Micronesian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','micronesian','Micronesian','4780', '0',' 2085-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id micronesian
UPDATE `list_options` SET `notes` = '2085-9' WHERE `option_id` = 'micronesian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Micronesian
UPDATE `list_options` SET `notes` = '2085-9' WHERE `title` = 'Micronesian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id middle_eastern_north_african title Middle Eastern or North African
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','middle_eastern_north_african','Middle Eastern or North African','4790', '0',' 2118-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id middle_eastern_north_african
UPDATE `list_options` SET `notes` = '2118-8' WHERE `option_id` = 'middle_eastern_north_african' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Middle Eastern or North African
UPDATE `list_options` SET `notes` = '2118-8' WHERE `title` = 'Middle Eastern or North African' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mille_lacs title Mille Lacs
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mille_lacs','Mille Lacs','4800', '0',' 1138-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id mille_lacs
UPDATE `list_options` SET `notes` = '1138-7' WHERE `option_id` = 'mille_lacs' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mille Lacs
UPDATE `list_options` SET `notes` = '1138-7' WHERE `title` = 'Mille Lacs' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id miniconjou title Miniconjou
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','miniconjou','Miniconjou','4810', '0',' 1621-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id miniconjou
UPDATE `list_options` SET `notes` = '1621-2' WHERE `option_id` = 'miniconjou' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Miniconjou
UPDATE `list_options` SET `notes` = '1621-2' WHERE `title` = 'Miniconjou' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id minnesota_chippewa title Minnesota Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','minnesota_chippewa','Minnesota Chippewa','4820', '0',' 1139-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id minnesota_chippewa
UPDATE `list_options` SET `notes` = '1139-5' WHERE `option_id` = 'minnesota_chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Minnesota Chippewa
UPDATE `list_options` SET `notes` = '1139-5' WHERE `title` = 'Minnesota Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id minto title Minto
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','minto','Minto','4830', '0',' 1782-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id minto
UPDATE `list_options` SET `notes` = '1782-2' WHERE `option_id` = 'minto' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Minto
UPDATE `list_options` SET `notes` = '1782-2' WHERE `title` = 'Minto' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mission_indians title Mission Indians
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mission_indians','Mission Indians','4840', '0',' 1368-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id mission_indians
UPDATE `list_options` SET `notes` = '1368-0' WHERE `option_id` = 'mission_indians' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mission Indians
UPDATE `list_options` SET `notes` = '1368-0' WHERE `title` = 'Mission Indians' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mississippi_choctaw title Mississippi Choctaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mississippi_choctaw','Mississippi Choctaw','4850', '0',' 1158-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id mississippi_choctaw
UPDATE `list_options` SET `notes` = '1158-5' WHERE `option_id` = 'mississippi_choctaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mississippi Choctaw
UPDATE `list_options` SET `notes` = '1158-5' WHERE `title` = 'Mississippi Choctaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id missouri_sac_and_fox title Missouri Sac and Fox
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','missouri_sac_and_fox','Missouri Sac and Fox','4860', '0',' 1553-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id missouri_sac_and_fox
UPDATE `list_options` SET `notes` = '1553-7' WHERE `option_id` = 'missouri_sac_and_fox' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Missouri Sac and Fox
UPDATE `list_options` SET `notes` = '1553-7' WHERE `title` = 'Missouri Sac and Fox' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id miwok title Miwok
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','miwok','Miwok','4870', '0',' 1370-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id miwok
UPDATE `list_options` SET `notes` = '1370-6' WHERE `option_id` = 'miwok' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Miwok
UPDATE `list_options` SET `notes` = '1370-6' WHERE `title` = 'Miwok' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id moapa title Moapa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','moapa','Moapa','4880', '0',' 1428-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id moapa
UPDATE `list_options` SET `notes` = '1428-2' WHERE `option_id` = 'moapa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Moapa
UPDATE `list_options` SET `notes` = '1428-2' WHERE `title` = 'Moapa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id modoc title Modoc
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','modoc','Modoc','4890', '0',' 1372-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id modoc
UPDATE `list_options` SET `notes` = '1372-2' WHERE `option_id` = 'modoc' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Modoc
UPDATE `list_options` SET `notes` = '1372-2' WHERE `title` = 'Modoc' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mohave title Mohave
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mohave','Mohave','4900', '0',' 1729-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id mohave
UPDATE `list_options` SET `notes` = '1729-3' WHERE `option_id` = 'mohave' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mohave
UPDATE `list_options` SET `notes` = '1729-3' WHERE `title` = 'Mohave' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mohawk title Mohawk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mohawk','Mohawk','4910', '0',' 1287-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id mohawk
UPDATE `list_options` SET `notes` = '1287-2' WHERE `option_id` = 'mohawk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mohawk
UPDATE `list_options` SET `notes` = '1287-2' WHERE `title` = 'Mohawk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mohegan title Mohegan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mohegan','Mohegan','4920', '0',' 1374-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id mohegan
UPDATE `list_options` SET `notes` = '1374-8' WHERE `option_id` = 'mohegan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mohegan
UPDATE `list_options` SET `notes` = '1374-8' WHERE `title` = 'Mohegan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id molala title Molala
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','molala','Molala','4930', '0',' 1396-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id molala
UPDATE `list_options` SET `notes` = '1396-1' WHERE `option_id` = 'molala' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Molala
UPDATE `list_options` SET `notes` = '1396-1' WHERE `title` = 'Molala' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mono title Mono
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mono','Mono','4940', '0',' 1376-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id mono
UPDATE `list_options` SET `notes` = '1376-3' WHERE `option_id` = 'mono' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mono
UPDATE `list_options` SET `notes` = '1376-3' WHERE `title` = 'Mono' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id montauk title Montauk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','montauk','Montauk','4950', '0',' 1327-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id montauk
UPDATE `list_options` SET `notes` = '1327-6' WHERE `option_id` = 'montauk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Montauk
UPDATE `list_options` SET `notes` = '1327-6' WHERE `title` = 'Montauk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id moor title Moor
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','moor','Moor','4960', '0',' 1237-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id moor
UPDATE `list_options` SET `notes` = '1237-7' WHERE `option_id` = 'moor' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Moor
UPDATE `list_options` SET `notes` = '1237-7' WHERE `title` = 'Moor' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id morongo title Morongo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','morongo','Morongo','4970', '0',' 1049-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id morongo
UPDATE `list_options` SET `notes` = '1049-6' WHERE `option_id` = 'morongo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Morongo
UPDATE `list_options` SET `notes` = '1049-6' WHERE `title` = 'Morongo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mountain_maidu title Mountain Maidu
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mountain_maidu','Mountain Maidu','4980', '0',' 1345-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id mountain_maidu
UPDATE `list_options` SET `notes` = '1345-8' WHERE `option_id` = 'mountain_maidu' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mountain Maidu
UPDATE `list_options` SET `notes` = '1345-8' WHERE `title` = 'Mountain Maidu' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mountain_village title Mountain Village
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mountain_village','Mountain Village','4990', '0',' 1934-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id mountain_village
UPDATE `list_options` SET `notes` = '1934-9' WHERE `option_id` = 'mountain_village' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mountain Village
UPDATE `list_options` SET `notes` = '1934-9' WHERE `title` = 'Mountain Village' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id mowa_band_of_choctaw title Mowa Band of Choctaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','mowa_band_of_choctaw','Mowa Band of Choctaw','5000', '0',' 1159-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id mowa_band_of_choctaw
UPDATE `list_options` SET `notes` = '1159-3' WHERE `option_id` = 'mowa_band_of_choctaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Mowa Band of Choctaw
UPDATE `list_options` SET `notes` = '1159-3' WHERE `title` = 'Mowa Band of Choctaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id muckleshoot title Muckleshoot
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','muckleshoot','Muckleshoot','5010', '0',' 1522-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id muckleshoot
UPDATE `list_options` SET `notes` = '1522-2' WHERE `option_id` = 'muckleshoot' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Muckleshoot
UPDATE `list_options` SET `notes` = '1522-2' WHERE `title` = 'Muckleshoot' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id munsee title Munsee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','munsee','Munsee','5020', '0',' 1217-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id munsee
UPDATE `list_options` SET `notes` = '1217-9' WHERE `option_id` = 'munsee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Munsee
UPDATE `list_options` SET `notes` = '1217-9' WHERE `title` = 'Munsee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id naknek title Naknek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','naknek','Naknek','5030', '0',' 1935-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id naknek
UPDATE `list_options` SET `notes` = '1935-6' WHERE `option_id` = 'naknek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Naknek
UPDATE `list_options` SET `notes` = '1935-6' WHERE `title` = 'Naknek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nambe title Nambe
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nambe','Nambe','5040', '0',' 1498-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id nambe
UPDATE `list_options` SET `notes` = '1498-5' WHERE `option_id` = 'nambe' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nambe
UPDATE `list_options` SET `notes` = '1498-5' WHERE `title` = 'Nambe' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id namibian title Namibian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','namibian','Namibian','5050', '0',' 2064-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id namibian
UPDATE `list_options` SET `notes` = '2064-4' WHERE `option_id` = 'namibian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Namibian
UPDATE `list_options` SET `notes` = '2064-4' WHERE `title` = 'Namibian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nana_inupiat title Nana Inupiat
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nana_inupiat','Nana Inupiat','5060', '0',' 1871-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id nana_inupiat
UPDATE `list_options` SET `notes` = '1871-3' WHERE `option_id` = 'nana_inupiat' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nana Inupiat
UPDATE `list_options` SET `notes` = '1871-3' WHERE `title` = 'Nana Inupiat' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nansemond title Nansemond
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nansemond','Nansemond','5070', '0',' 1238-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id nansemond
UPDATE `list_options` SET `notes` = '1238-5' WHERE `option_id` = 'nansemond' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nansemond
UPDATE `list_options` SET `notes` = '1238-5' WHERE `title` = 'Nansemond' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nanticoke title Nanticoke
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nanticoke','Nanticoke','5080', '0',' 1378-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id nanticoke
UPDATE `list_options` SET `notes` = '1378-9' WHERE `option_id` = 'nanticoke' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nanticoke
UPDATE `list_options` SET `notes` = '1378-9' WHERE `title` = 'Nanticoke' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id napakiak title Napakiak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','napakiak','Napakiak','5090', '0',' 1937-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id napakiak
UPDATE `list_options` SET `notes` = '1937-2' WHERE `option_id` = 'napakiak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Napakiak
UPDATE `list_options` SET `notes` = '1937-2' WHERE `title` = 'Napakiak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id napaskiak title Napaskiak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','napaskiak','Napaskiak','5100', '0',' 1938-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id napaskiak
UPDATE `list_options` SET `notes` = '1938-0' WHERE `option_id` = 'napaskiak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Napaskiak
UPDATE `list_options` SET `notes` = '1938-0' WHERE `title` = 'Napaskiak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id napaumute title Napaumute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','napaumute','Napaumute','5110', '0',' 1936-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id napaumute
UPDATE `list_options` SET `notes` = '1936-4' WHERE `option_id` = 'napaumute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Napaumute
UPDATE `list_options` SET `notes` = '1936-4' WHERE `title` = 'Napaumute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id narragansett title Narragansett
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','narragansett','Narragansett','5120', '0',' 1380-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id narragansett
UPDATE `list_options` SET `notes` = '1380-5' WHERE `option_id` = 'narragansett' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Narragansett
UPDATE `list_options` SET `notes` = '1380-5' WHERE `title` = 'Narragansett' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id natchez title Natchez
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','natchez','Natchez','5130', '0',' 1239-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id natchez
UPDATE `list_options` SET `notes` = '1239-3' WHERE `option_id` = 'natchez' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Natchez
UPDATE `list_options` SET `notes` = '1239-3' WHERE `title` = 'Natchez' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id native_hawaiian title Native Hawaiian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','native_hawaiian','Native Hawaiian','5140', '0',' 2079-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id native_hawaiian
UPDATE `list_options` SET `notes` = '2079-2' WHERE `option_id` = 'native_hawaiian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Native Hawaiian
UPDATE `list_options` SET `notes` = '2079-2' WHERE `title` = 'Native Hawaiian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nausu_waiwash title Nausu Waiwash
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nausu_waiwash','Nausu Waiwash','5160', '0',' 1240-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id nausu_waiwash
UPDATE `list_options` SET `notes` = '1240-1' WHERE `option_id` = 'nausu_waiwash' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nausu Waiwash
UPDATE `list_options` SET `notes` = '1240-1' WHERE `title` = 'Nausu Waiwash' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id navajo title Navajo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','navajo','Navajo','5170', '0',' 1382-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id navajo
UPDATE `list_options` SET `notes` = '1382-1' WHERE `option_id` = 'navajo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Navajo
UPDATE `list_options` SET `notes` = '1382-1' WHERE `title` = 'Navajo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nebraska_ponca title Nebraska Ponca
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nebraska_ponca','Nebraska Ponca','5180', '0',' 1475-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id nebraska_ponca
UPDATE `list_options` SET `notes` = '1475-3' WHERE `option_id` = 'nebraska_ponca' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nebraska Ponca
UPDATE `list_options` SET `notes` = '1475-3' WHERE `title` = 'Nebraska Ponca' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nebraska_winnebago title Nebraska Winnebago
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nebraska_winnebago','Nebraska Winnebago','5190', '0',' 1698-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id nebraska_winnebago
UPDATE `list_options` SET `notes` = '1698-0' WHERE `option_id` = 'nebraska_winnebago' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nebraska Winnebago
UPDATE `list_options` SET `notes` = '1698-0' WHERE `title` = 'Nebraska Winnebago' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nelson_lagoon title Nelson Lagoon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nelson_lagoon','Nelson Lagoon','5200', '0',' 2016-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id nelson_lagoon
UPDATE `list_options` SET `notes` = '2016-4' WHERE `option_id` = 'nelson_lagoon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nelson Lagoon
UPDATE `list_options` SET `notes` = '2016-4' WHERE `title` = 'Nelson Lagoon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nenana title Nenana
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nenana','Nenana','5210', '0',' 1783-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id nenana
UPDATE `list_options` SET `notes` = '1783-0' WHERE `option_id` = 'nenana' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nenana
UPDATE `list_options` SET `notes` = '1783-0' WHERE `title` = 'Nenana' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nepalese title Nepalese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nepalese','Nepalese','5220', '0',' 2050-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id nepalese
UPDATE `list_options` SET `notes` = '2050-3' WHERE `option_id` = 'nepalese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nepalese
UPDATE `list_options` SET `notes` = '2050-3' WHERE `title` = 'Nepalese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id new_hebrides title New Hebrides
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','new_hebrides','New Hebrides','5230', '0',' 2104-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id new_hebrides
UPDATE `list_options` SET `notes` = '2104-8' WHERE `option_id` = 'new_hebrides' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title New Hebrides
UPDATE `list_options` SET `notes` = '2104-8' WHERE `title` = 'New Hebrides' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id new_stuyahok title New Stuyahok
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','new_stuyahok','New Stuyahok','5240', '0',' 1940-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id new_stuyahok
UPDATE `list_options` SET `notes` = '1940-6' WHERE `option_id` = 'new_stuyahok' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title New Stuyahok
UPDATE `list_options` SET `notes` = '1940-6' WHERE `title` = 'New Stuyahok' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id newhalen title Newhalen
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','newhalen','Newhalen','5250', '0',' 1939-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id newhalen
UPDATE `list_options` SET `notes` = '1939-8' WHERE `option_id` = 'newhalen' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Newhalen
UPDATE `list_options` SET `notes` = '1939-8' WHERE `title` = 'Newhalen' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id newtok title Newtok
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','newtok','Newtok','5260', '0',' 1941-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id newtok
UPDATE `list_options` SET `notes` = '1941-4' WHERE `option_id` = 'newtok' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Newtok
UPDATE `list_options` SET `notes` = '1941-4' WHERE `title` = 'Newtok' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nez_perce title Nez Perce
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nez_perce','Nez Perce','5270', '0',' 1387-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id nez_perce
UPDATE `list_options` SET `notes` = '1387-0' WHERE `option_id` = 'nez_perce' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nez Perce
UPDATE `list_options` SET `notes` = '1387-0' WHERE `title` = 'Nez Perce' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nigerian title Nigerian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nigerian','Nigerian','5280', '0',' 2065-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id nigerian
UPDATE `list_options` SET `notes` = '2065-1' WHERE `option_id` = 'nigerian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nigerian
UPDATE `list_options` SET `notes` = '2065-1' WHERE `title` = 'Nigerian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nightmute title Nightmute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nightmute','Nightmute','5290', '0',' 1942-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id nightmute
UPDATE `list_options` SET `notes` = '1942-2' WHERE `option_id` = 'nightmute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nightmute
UPDATE `list_options` SET `notes` = '1942-2' WHERE `title` = 'Nightmute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nikolai title Nikolai
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nikolai','Nikolai','5300', '0',' 1784-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id nikolai
UPDATE `list_options` SET `notes` = '1784-8' WHERE `option_id` = 'nikolai' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nikolai
UPDATE `list_options` SET `notes` = '1784-8' WHERE `title` = 'Nikolai' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nikolski title Nikolski
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nikolski','Nikolski','5310', '0',' 2017-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id nikolski
UPDATE `list_options` SET `notes` = '2017-2' WHERE `option_id` = 'nikolski' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nikolski
UPDATE `list_options` SET `notes` = '2017-2' WHERE `title` = 'Nikolski' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ninilchik title Ninilchik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ninilchik','Ninilchik','5320', '0',' 1785-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id ninilchik
UPDATE `list_options` SET `notes` = '1785-5' WHERE `option_id` = 'ninilchik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ninilchik
UPDATE `list_options` SET `notes` = '1785-5' WHERE `title` = 'Ninilchik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nipmuc title Nipmuc
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nipmuc','Nipmuc','5330', '0',' 1241-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id nipmuc
UPDATE `list_options` SET `notes` = '1241-9' WHERE `option_id` = 'nipmuc' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nipmuc
UPDATE `list_options` SET `notes` = '1241-9' WHERE `title` = 'Nipmuc' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nishinam title Nishinam
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nishinam','Nishinam','5340', '0',' 1346-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id nishinam
UPDATE `list_options` SET `notes` = '1346-6' WHERE `option_id` = 'nishinam' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nishinam
UPDATE `list_options` SET `notes` = '1346-6' WHERE `title` = 'Nishinam' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nisqually title Nisqually
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nisqually','Nisqually','5350', '0',' 1523-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id nisqually
UPDATE `list_options` SET `notes` = '1523-0' WHERE `option_id` = 'nisqually' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nisqually
UPDATE `list_options` SET `notes` = '1523-0' WHERE `title` = 'Nisqually' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id noatak title Noatak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','noatak','Noatak','5360', '0',' 1872-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id noatak
UPDATE `list_options` SET `notes` = '1872-1' WHERE `option_id` = 'noatak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Noatak
UPDATE `list_options` SET `notes` = '1872-1' WHERE `title` = 'Noatak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nomalaki title Nomalaki
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nomalaki','Nomalaki','5370', '0',' 1389-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id nomalaki
UPDATE `list_options` SET `notes` = '1389-6' WHERE `option_id` = 'nomalaki' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nomalaki
UPDATE `list_options` SET `notes` = '1389-6' WHERE `title` = 'Nomalaki' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nome title Nome
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nome','Nome','5380', '0',' 1873-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id nome
UPDATE `list_options` SET `notes` = '1873-9' WHERE `option_id` = 'nome' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nome
UPDATE `list_options` SET `notes` = '1873-9' WHERE `title` = 'Nome' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nondalton title Nondalton
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nondalton','Nondalton','5390', '0',' 1786-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id nondalton
UPDATE `list_options` SET `notes` = '1786-3' WHERE `option_id` = 'nondalton' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nondalton
UPDATE `list_options` SET `notes` = '1786-3' WHERE `title` = 'Nondalton' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nooksack title Nooksack
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nooksack','Nooksack','5400', '0',' 1524-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id nooksack
UPDATE `list_options` SET `notes` = '1524-8' WHERE `option_id` = 'nooksack' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nooksack
UPDATE `list_options` SET `notes` = '1524-8' WHERE `title` = 'Nooksack' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id noorvik title Noorvik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','noorvik','Noorvik','5410', '0',' 1874-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id noorvik
UPDATE `list_options` SET `notes` = '1874-7' WHERE `option_id` = 'noorvik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Noorvik
UPDATE `list_options` SET `notes` = '1874-7' WHERE `title` = 'Noorvik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id northern_arapaho title Northern Arapaho
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','northern_arapaho','Northern Arapaho','5420', '0',' 1022-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id northern_arapaho
UPDATE `list_options` SET `notes` = '1022-3' WHERE `option_id` = 'northern_arapaho' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Northern Arapaho
UPDATE `list_options` SET `notes` = '1022-3' WHERE `title` = 'Northern Arapaho' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id northern_cherokee title Northern Cherokee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','northern_cherokee','Northern Cherokee','5430', '0',' 1095-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id northern_cherokee
UPDATE `list_options` SET `notes` = '1095-9' WHERE `option_id` = 'northern_cherokee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Northern Cherokee
UPDATE `list_options` SET `notes` = '1095-9' WHERE `title` = 'Northern Cherokee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id northern_cheyenne title Northern Cheyenne
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','northern_cheyenne','Northern Cheyenne','5440', '0',' 1103-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id northern_cheyenne
UPDATE `list_options` SET `notes` = '1103-1' WHERE `option_id` = 'northern_cheyenne' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Northern Cheyenne
UPDATE `list_options` SET `notes` = '1103-1' WHERE `title` = 'Northern Cheyenne' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id northern_paiute title Northern Paiute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','northern_paiute','Northern Paiute','5450', '0',' 1429-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id northern_paiute
UPDATE `list_options` SET `notes` = '1429-0' WHERE `option_id` = 'northern_paiute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Northern Paiute
UPDATE `list_options` SET `notes` = '1429-0' WHERE `title` = 'Northern Paiute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id northern_pomo title Northern Pomo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','northern_pomo','Northern Pomo','5460', '0',' 1469-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id northern_pomo
UPDATE `list_options` SET `notes` = '1469-6' WHERE `option_id` = 'northern_pomo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Northern Pomo
UPDATE `list_options` SET `notes` = '1469-6' WHERE `title` = 'Northern Pomo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id northway title Northway
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','northway','Northway','5470', '0',' 1787-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id northway
UPDATE `list_options` SET `notes` = '1787-1' WHERE `option_id` = 'northway' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Northway
UPDATE `list_options` SET `notes` = '1787-1' WHERE `title` = 'Northway' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id northwest_tribes title Northwest Tribes
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','northwest_tribes','Northwest Tribes','5480', '0',' 1391-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id northwest_tribes
UPDATE `list_options` SET `notes` = '1391-2' WHERE `option_id` = 'northwest_tribes' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Northwest Tribes
UPDATE `list_options` SET `notes` = '1391-2' WHERE `title` = 'Northwest Tribes' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nuiqsut title Nuiqsut
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nuiqsut','Nuiqsut','5490', '0',' 1875-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id nuiqsut
UPDATE `list_options` SET `notes` = '1875-4' WHERE `option_id` = 'nuiqsut' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nuiqsut
UPDATE `list_options` SET `notes` = '1875-4' WHERE `title` = 'Nuiqsut' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nulato title Nulato
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nulato','Nulato','5500', '0',' 1788-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id nulato
UPDATE `list_options` SET `notes` = '1788-9' WHERE `option_id` = 'nulato' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nulato
UPDATE `list_options` SET `notes` = '1788-9' WHERE `title` = 'Nulato' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id nunapitchukv title Nunapitchukv
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','nunapitchukv','Nunapitchukv','5510', '0',' 1943-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id nunapitchukv
UPDATE `list_options` SET `notes` = '1943-0' WHERE `option_id` = 'nunapitchukv' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Nunapitchukv
UPDATE `list_options` SET `notes` = '1943-0' WHERE `title` = 'Nunapitchukv' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oglala_sioux title Oglala Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oglala_sioux','Oglala Sioux','5520', '0',' 1622-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id oglala_sioux
UPDATE `list_options` SET `notes` = '1622-0' WHERE `option_id` = 'oglala_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oglala Sioux
UPDATE `list_options` SET `notes` = '1622-0' WHERE `title` = 'Oglala Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id okinawan title Okinawan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','okinawan','Okinawan','5530', '0',' 2043-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id okinawan
UPDATE `list_options` SET `notes` = '2043-8' WHERE `option_id` = 'okinawan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Okinawan
UPDATE `list_options` SET `notes` = '2043-8' WHERE `title` = 'Okinawan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_apache title Oklahoma Apache
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_apache','Oklahoma Apache','5540', '0',' 1016-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_apache
UPDATE `list_options` SET `notes` = '1016-5' WHERE `option_id` = 'oklahoma_apache' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Apache
UPDATE `list_options` SET `notes` = '1016-5' WHERE `title` = 'Oklahoma Apache' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_cado title Oklahoma Cado
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_cado','Oklahoma Cado','5550', '0',' 1042-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_cado
UPDATE `list_options` SET `notes` = '1042-1' WHERE `option_id` = 'oklahoma_cado' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Cado
UPDATE `list_options` SET `notes` = '1042-1' WHERE `title` = 'Oklahoma Cado' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_choctaw title Oklahoma Choctaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_choctaw','Oklahoma Choctaw','5560', '0',' 1160-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_choctaw
UPDATE `list_options` SET `notes` = '1160-1' WHERE `option_id` = 'oklahoma_choctaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Choctaw
UPDATE `list_options` SET `notes` = '1160-1' WHERE `title` = 'Oklahoma Choctaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_comanche title Oklahoma Comanche
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_comanche','Oklahoma Comanche','5570', '0',' 1176-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_comanche
UPDATE `list_options` SET `notes` = '1176-7' WHERE `option_id` = 'oklahoma_comanche' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Comanche
UPDATE `list_options` SET `notes` = '1176-7' WHERE `title` = 'Oklahoma Comanche' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_delaware title Oklahoma Delaware
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_delaware','Oklahoma Delaware','5580', '0',' 1218-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_delaware
UPDATE `list_options` SET `notes` = '1218-7' WHERE `option_id` = 'oklahoma_delaware' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Delaware
UPDATE `list_options` SET `notes` = '1218-7' WHERE `title` = 'Oklahoma Delaware' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_kickapoo title Oklahoma Kickapoo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_kickapoo','Oklahoma Kickapoo','5590', '0',' 1306-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_kickapoo
UPDATE `list_options` SET `notes` = '1306-0' WHERE `option_id` = 'oklahoma_kickapoo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Kickapoo
UPDATE `list_options` SET `notes` = '1306-0' WHERE `title` = 'Oklahoma Kickapoo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_kiowa title Oklahoma Kiowa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_kiowa','Oklahoma Kiowa','5600', '0',' 1310-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_kiowa
UPDATE `list_options` SET `notes` = '1310-2' WHERE `option_id` = 'oklahoma_kiowa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Kiowa
UPDATE `list_options` SET `notes` = '1310-2' WHERE `title` = 'Oklahoma Kiowa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_miami title Oklahoma Miami
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_miami','Oklahoma Miami','5610', '0',' 1361-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_miami
UPDATE `list_options` SET `notes` = '1361-5' WHERE `option_id` = 'oklahoma_miami' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Miami
UPDATE `list_options` SET `notes` = '1361-5' WHERE `title` = 'Oklahoma Miami' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_ottawa title Oklahoma Ottawa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_ottawa','Oklahoma Ottawa','5620', '0',' 1414-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_ottawa
UPDATE `list_options` SET `notes` = '1414-2' WHERE `option_id` = 'oklahoma_ottawa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Ottawa
UPDATE `list_options` SET `notes` = '1414-2' WHERE `title` = 'Oklahoma Ottawa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_pawnee title Oklahoma Pawnee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_pawnee','Oklahoma Pawnee','5630', '0',' 1446-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_pawnee
UPDATE `list_options` SET `notes` = '1446-4' WHERE `option_id` = 'oklahoma_pawnee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Pawnee
UPDATE `list_options` SET `notes` = '1446-4' WHERE `title` = 'Oklahoma Pawnee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_peoria title Oklahoma Peoria
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_peoria','Oklahoma Peoria','5640', '0',' 1451-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_peoria
UPDATE `list_options` SET `notes` = '1451-4' WHERE `option_id` = 'oklahoma_peoria' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Peoria
UPDATE `list_options` SET `notes` = '1451-4' WHERE `title` = 'Oklahoma Peoria' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_ponca title Oklahoma Ponca
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_ponca','Oklahoma Ponca','5650', '0',' 1476-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_ponca
UPDATE `list_options` SET `notes` = '1476-1' WHERE `option_id` = 'oklahoma_ponca' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Ponca
UPDATE `list_options` SET `notes` = '1476-1' WHERE `title` = 'Oklahoma Ponca' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_sac_and_fox title Oklahoma Sac and Fox
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_sac_and_fox','Oklahoma Sac and Fox','5660', '0',' 1554-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_sac_and_fox
UPDATE `list_options` SET `notes` = '1554-5' WHERE `option_id` = 'oklahoma_sac_and_fox' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Sac and Fox
UPDATE `list_options` SET `notes` = '1554-5' WHERE `title` = 'Oklahoma Sac and Fox' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oklahoma_seminole title Oklahoma Seminole
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oklahoma_seminole','Oklahoma Seminole','5670', '0',' 1571-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id oklahoma_seminole
UPDATE `list_options` SET `notes` = '1571-9' WHERE `option_id` = 'oklahoma_seminole' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oklahoma Seminole
UPDATE `list_options` SET `notes` = '1571-9' WHERE `title` = 'Oklahoma Seminole' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id old_harbor title Old Harbor
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','old_harbor','Old Harbor','5680', '0',' 1998-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id old_harbor
UPDATE `list_options` SET `notes` = '1998-4' WHERE `option_id` = 'old_harbor' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Old Harbor
UPDATE `list_options` SET `notes` = '1998-4' WHERE `title` = 'Old Harbor' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id omaha title Omaha
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','omaha','Omaha','5690', '0',' 1403-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id omaha
UPDATE `list_options` SET `notes` = '1403-5' WHERE `option_id` = 'omaha' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Omaha
UPDATE `list_options` SET `notes` = '1403-5' WHERE `title` = 'Omaha' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oneida title Oneida
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oneida','Oneida','5700', '0',' 1288-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id oneida
UPDATE `list_options` SET `notes` = '1288-0' WHERE `option_id` = 'oneida' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oneida
UPDATE `list_options` SET `notes` = '1288-0' WHERE `title` = 'Oneida' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id onondaga title Onondaga
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','onondaga','Onondaga','5710', '0',' 1289-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id onondaga
UPDATE `list_options` SET `notes` = '1289-8' WHERE `option_id` = 'onondaga' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Onondaga
UPDATE `list_options` SET `notes` = '1289-8' WHERE `title` = 'Onondaga' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ontonagon title Ontonagon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ontonagon','Ontonagon','5720', '0',' 1140-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id ontonagon
UPDATE `list_options` SET `notes` = '1140-3' WHERE `option_id` = 'ontonagon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ontonagon
UPDATE `list_options` SET `notes` = '1140-3' WHERE `title` = 'Ontonagon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oregon_athabaskan title Oregon Athabaskan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oregon_athabaskan','Oregon Athabaskan','5730', '0',' 1405-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id oregon_athabaskan
UPDATE `list_options` SET `notes` = '1405-0' WHERE `option_id` = 'oregon_athabaskan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oregon Athabaskan
UPDATE `list_options` SET `notes` = '1405-0' WHERE `title` = 'Oregon Athabaskan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id osage title Osage
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','osage','Osage','5740', '0',' 1407-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id osage
UPDATE `list_options` SET `notes` = '1407-6' WHERE `option_id` = 'osage' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Osage
UPDATE `list_options` SET `notes` = '1407-6' WHERE `title` = 'Osage' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id oscarville title Oscarville
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','oscarville','Oscarville','5750', '0',' 1944-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id oscarville
UPDATE `list_options` SET `notes` = '1944-8' WHERE `option_id` = 'oscarville' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Oscarville
UPDATE `list_options` SET `notes` = '1944-8' WHERE `title` = 'Oscarville' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id other_pacific_islander title Other Pacific Islander
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','other_pacific_islander','Other Pacific Islander','5760', '0',' 2500-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id other_pacific_islander
UPDATE `list_options` SET `notes` = '2500-7' WHERE `option_id` = 'other_pacific_islander' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Other Pacific Islander
UPDATE `list_options` SET `notes` = '2500-7' WHERE `title` = 'Other Pacific Islander' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id other_race title Other Race
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','other_race','Other Race','5770', '0',' 2131-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id other_race
UPDATE `list_options` SET `notes` = '2131-1' WHERE `option_id` = 'other_race' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Other Race
UPDATE `list_options` SET `notes` = '2131-1' WHERE `title` = 'Other Race' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id otoe-missouria title Otoe-Missouria
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','otoe-missouria','Otoe-Missouria','5780', '0',' 1409-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id otoe-missouria
UPDATE `list_options` SET `notes` = '1409-2' WHERE `option_id` = 'otoe-missouria' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Otoe-Missouria
UPDATE `list_options` SET `notes` = '1409-2' WHERE `title` = 'Otoe-Missouria' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ottawa title Ottawa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ottawa','Ottawa','5790', '0',' 1411-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id ottawa
UPDATE `list_options` SET `notes` = '1411-8' WHERE `option_id` = 'ottawa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ottawa
UPDATE `list_options` SET `notes` = '1411-8' WHERE `title` = 'Ottawa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ouzinkie title Ouzinkie
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ouzinkie','Ouzinkie','5800', '0',' 1999-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id ouzinkie
UPDATE `list_options` SET `notes` = '1999-2' WHERE `option_id` = 'ouzinkie' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ouzinkie
UPDATE `list_options` SET `notes` = '1999-2' WHERE `title` = 'Ouzinkie' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id owens_valley title Owens Valley
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','owens_valley','Owens Valley','5810', '0',' 1430-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id owens_valley
UPDATE `list_options` SET `notes` = '1430-8' WHERE `option_id` = 'owens_valley' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Owens Valley
UPDATE `list_options` SET `notes` = '1430-8' WHERE `title` = 'Owens Valley' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id paiute title Paiute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','paiute','Paiute','5820', '0',' 1416-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id paiute
UPDATE `list_options` SET `notes` = '1416-7' WHERE `option_id` = 'paiute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Paiute
UPDATE `list_options` SET `notes` = '1416-7' WHERE `title` = 'Paiute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pakistani title Pakistani
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pakistani','Pakistani','5830', '0',' 2044-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id pakistani
UPDATE `list_options` SET `notes` = '2044-6' WHERE `option_id` = 'pakistani' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pakistani
UPDATE `list_options` SET `notes` = '2044-6' WHERE `title` = 'Pakistani' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pala title Pala
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pala','Pala','5840', '0',' 1333-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id pala
UPDATE `list_options` SET `notes` = '1333-4' WHERE `option_id` = 'pala' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pala
UPDATE `list_options` SET `notes` = '1333-4' WHERE `title` = 'Pala' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id palauan title Palauan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','palauan','Palauan','5850', '0',' 2091-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id palauan
UPDATE `list_options` SET `notes` = '2091-7' WHERE `option_id` = 'palauan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Palauan
UPDATE `list_options` SET `notes` = '2091-7' WHERE `title` = 'Palauan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id palestinian title Palestinian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','palestinian','Palestinian','5860', '0',' 2124-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id palestinian
UPDATE `list_options` SET `notes` = '2124-6' WHERE `option_id` = 'palestinian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Palestinian
UPDATE `list_options` SET `notes` = '2124-6' WHERE `title` = 'Palestinian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pamunkey title Pamunkey
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pamunkey','Pamunkey','5870', '0',' 1439-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id pamunkey
UPDATE `list_options` SET `notes` = '1439-9' WHERE `option_id` = 'pamunkey' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pamunkey
UPDATE `list_options` SET `notes` = '1439-9' WHERE `title` = 'Pamunkey' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id panamint title Panamint
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','panamint','Panamint','5880', '0',' 1592-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id panamint
UPDATE `list_options` SET `notes` = '1592-5' WHERE `option_id` = 'panamint' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Panamint
UPDATE `list_options` SET `notes` = '1592-5' WHERE `title` = 'Panamint' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id papua_new_guinean title Papua New Guinean
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','papua_new_guinean','Papua New Guinean','5890', '0',' 2102-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id papua_new_guinean
UPDATE `list_options` SET `notes` = '2102-2' WHERE `option_id` = 'papua_new_guinean' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Papua New Guinean
UPDATE `list_options` SET `notes` = '2102-2' WHERE `title` = 'Papua New Guinean' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pascua_yaqui title Pascua Yaqui
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pascua_yaqui','Pascua Yaqui','5900', '0',' 1713-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id pascua_yaqui
UPDATE `list_options` SET `notes` = '1713-7' WHERE `option_id` = 'pascua_yaqui' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pascua Yaqui
UPDATE `list_options` SET `notes` = '1713-7' WHERE `title` = 'Pascua Yaqui' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id passamaquoddy title Passamaquoddy
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','passamaquoddy','Passamaquoddy','5910', '0',' 1441-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id passamaquoddy
UPDATE `list_options` SET `notes` = '1441-5' WHERE `option_id` = 'passamaquoddy' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Passamaquoddy
UPDATE `list_options` SET `notes` = '1441-5' WHERE `title` = 'Passamaquoddy' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id paugussett title Paugussett
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','paugussett','Paugussett','5920', '0',' 1242-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id paugussett
UPDATE `list_options` SET `notes` = '1242-7' WHERE `option_id` = 'paugussett' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Paugussett
UPDATE `list_options` SET `notes` = '1242-7' WHERE `title` = 'Paugussett' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pauloff_harbor title Pauloff Harbor
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pauloff_harbor','Pauloff Harbor','5930', '0',' 2018-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id pauloff_harbor
UPDATE `list_options` SET `notes` = '2018-0' WHERE `option_id` = 'pauloff_harbor' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pauloff Harbor
UPDATE `list_options` SET `notes` = '2018-0' WHERE `title` = 'Pauloff Harbor' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pauma title Pauma
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pauma','Pauma','5940', '0',' 1334-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id pauma
UPDATE `list_options` SET `notes` = '1334-2' WHERE `option_id` = 'pauma' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pauma
UPDATE `list_options` SET `notes` = '1334-2' WHERE `title` = 'Pauma' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pawnee title Pawnee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pawnee','Pawnee','5950', '0',' 1445-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id pawnee
UPDATE `list_options` SET `notes` = '1445-6' WHERE `option_id` = 'pawnee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pawnee
UPDATE `list_options` SET `notes` = '1445-6' WHERE `title` = 'Pawnee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id payson_apache title Payson Apache
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','payson_apache','Payson Apache','5960', '0',' 1017-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id payson_apache
UPDATE `list_options` SET `notes` = '1017-3' WHERE `option_id` = 'payson_apache' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Payson Apache
UPDATE `list_options` SET `notes` = '1017-3' WHERE `title` = 'Payson Apache' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pechanga title Pechanga
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pechanga','Pechanga','5970', '0',' 1335-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id pechanga
UPDATE `list_options` SET `notes` = '1335-9' WHERE `option_id` = 'pechanga' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pechanga
UPDATE `list_options` SET `notes` = '1335-9' WHERE `title` = 'Pechanga' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pedro_bay title Pedro Bay
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pedro_bay','Pedro Bay','5980', '0',' 1789-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id pedro_bay
UPDATE `list_options` SET `notes` = '1789-7' WHERE `option_id` = 'pedro_bay' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pedro Bay
UPDATE `list_options` SET `notes` = '1789-7' WHERE `title` = 'Pedro Bay' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pelican title Pelican
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pelican','Pelican','5990', '0',' 1828-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id pelican
UPDATE `list_options` SET `notes` = '1828-3' WHERE `option_id` = 'pelican' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pelican
UPDATE `list_options` SET `notes` = '1828-3' WHERE `title` = 'Pelican' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id penobscot title Penobscot
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','penobscot','Penobscot','6000', '0',' 1448-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id penobscot
UPDATE `list_options` SET `notes` = '1448-0' WHERE `option_id` = 'penobscot' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Penobscot
UPDATE `list_options` SET `notes` = '1448-0' WHERE `title` = 'Penobscot' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id peoria title Peoria
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','peoria','Peoria','6010', '0',' 1450-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id peoria
UPDATE `list_options` SET `notes` = '1450-6' WHERE `option_id` = 'peoria' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Peoria
UPDATE `list_options` SET `notes` = '1450-6' WHERE `title` = 'Peoria' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pequot title Pequot
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pequot','Pequot','6020', '0',' 1453-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id pequot
UPDATE `list_options` SET `notes` = '1453-0' WHERE `option_id` = 'pequot' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pequot
UPDATE `list_options` SET `notes` = '1453-0' WHERE `title` = 'Pequot' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id perryville title Perryville
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','perryville','Perryville','6030', '0',' 1980-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id perryville
UPDATE `list_options` SET `notes` = '1980-2' WHERE `option_id` = 'perryville' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Perryville
UPDATE `list_options` SET `notes` = '1980-2' WHERE `title` = 'Perryville' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id petersburg title Petersburg
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','petersburg','Petersburg','6040', '0',' 1829-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id petersburg
UPDATE `list_options` SET `notes` = '1829-1' WHERE `option_id` = 'petersburg' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Petersburg
UPDATE `list_options` SET `notes` = '1829-1' WHERE `title` = 'Petersburg' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id picuris title Picuris
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','picuris','Picuris','6050', '0',' 1499-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id picuris
UPDATE `list_options` SET `notes` = '1499-3' WHERE `option_id` = 'picuris' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Picuris
UPDATE `list_options` SET `notes` = '1499-3' WHERE `title` = 'Picuris' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pilot_point title Pilot Point
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pilot_point','Pilot Point','6060', '0',' 1981-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id pilot_point
UPDATE `list_options` SET `notes` = '1981-0' WHERE `option_id` = 'pilot_point' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pilot Point
UPDATE `list_options` SET `notes` = '1981-0' WHERE `title` = 'Pilot Point' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pilot_station title Pilot Station
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pilot_station','Pilot Station','6070', '0',' 1945-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id pilot_station
UPDATE `list_options` SET `notes` = '1945-5' WHERE `option_id` = 'pilot_station' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pilot Station
UPDATE `list_options` SET `notes` = '1945-5' WHERE `title` = 'Pilot Station' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pima title Pima
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pima','Pima','6080', '0',' 1456-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id pima
UPDATE `list_options` SET `notes` = '1456-3' WHERE `option_id` = 'pima' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pima
UPDATE `list_options` SET `notes` = '1456-3' WHERE `title` = 'Pima' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pine_ridge_sioux title Pine Ridge Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pine_ridge_sioux','Pine Ridge Sioux','6090', '0',' 1623-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id pine_ridge_sioux
UPDATE `list_options` SET `notes` = '1623-8' WHERE `option_id` = 'pine_ridge_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pine Ridge Sioux
UPDATE `list_options` SET `notes` = '1623-8' WHERE `title` = 'Pine Ridge Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pipestone_sioux title Pipestone Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pipestone_sioux','Pipestone Sioux','6100', '0',' 1624-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id pipestone_sioux
UPDATE `list_options` SET `notes` = '1624-6' WHERE `option_id` = 'pipestone_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pipestone Sioux
UPDATE `list_options` SET `notes` = '1624-6' WHERE `title` = 'Pipestone Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id piro title Piro
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','piro','Piro','6110', '0',' 1500-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id piro
UPDATE `list_options` SET `notes` = '1500-8' WHERE `option_id` = 'piro' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Piro
UPDATE `list_options` SET `notes` = '1500-8' WHERE `title` = 'Piro' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id piscataway title Piscataway
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','piscataway','Piscataway','6120', '0',' 1460-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id piscataway
UPDATE `list_options` SET `notes` = '1460-5' WHERE `option_id` = 'piscataway' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Piscataway
UPDATE `list_options` SET `notes` = '1460-5' WHERE `title` = 'Piscataway' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pit_river title Pit River
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pit_river','Pit River','6130', '0',' 1462-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id pit_river
UPDATE `list_options` SET `notes` = '1462-1' WHERE `option_id` = 'pit_river' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pit River
UPDATE `list_options` SET `notes` = '1462-1' WHERE `title` = 'Pit River' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pitkas_point title Pitkas Point
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pitkas_point','Pitkas Point','6140', '0',' 1946-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id pitkas_point
UPDATE `list_options` SET `notes` = '1946-3' WHERE `option_id` = 'pitkas_point' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pitkas Point
UPDATE `list_options` SET `notes` = '1946-3' WHERE `title` = 'Pitkas Point' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id platinum title Platinum
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','platinum','Platinum','6150', '0',' 1947-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id platinum
UPDATE `list_options` SET `notes` = '1947-1' WHERE `option_id` = 'platinum' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Platinum
UPDATE `list_options` SET `notes` = '1947-1' WHERE `title` = 'Platinum' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pleasant_point_passamaquoddy title Pleasant Point Passamaquoddy
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pleasant_point_passamaquoddy','Pleasant Point Passamaquoddy','6160', '0',' 1443-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id pleasant_point_passamaquoddy
UPDATE `list_options` SET `notes` = '1443-1' WHERE `option_id` = 'pleasant_point_passamaquoddy' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pleasant Point Passamaquoddy
UPDATE `list_options` SET `notes` = '1443-1' WHERE `title` = 'Pleasant Point Passamaquoddy' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id poarch_band title Poarch Band
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','poarch_band','Poarch Band','6170', '0',' 1201-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id poarch_band
UPDATE `list_options` SET `notes` = '1201-3' WHERE `option_id` = 'poarch_band' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Poarch Band
UPDATE `list_options` SET `notes` = '1201-3' WHERE `title` = 'Poarch Band' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pocomoke_acohonock title Pocomoke Acohonock
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pocomoke_acohonock','Pocomoke Acohonock','6180', '0',' 1243-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id pocomoke_acohonock
UPDATE `list_options` SET `notes` = '1243-5' WHERE `option_id` = 'pocomoke_acohonock' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pocomoke Acohonock
UPDATE `list_options` SET `notes` = '1243-5' WHERE `title` = 'Pocomoke Acohonock' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pohnpeian title Pohnpeian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pohnpeian','Pohnpeian','6190', '0',' 2094-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id pohnpeian
UPDATE `list_options` SET `notes` = '2094-1' WHERE `option_id` = 'pohnpeian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pohnpeian
UPDATE `list_options` SET `notes` = '2094-1' WHERE `title` = 'Pohnpeian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id point_hope title Point Hope
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','point_hope','Point Hope','6200', '0',' 1876-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id point_hope
UPDATE `list_options` SET `notes` = '1876-2' WHERE `option_id` = 'point_hope' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Point Hope
UPDATE `list_options` SET `notes` = '1876-2' WHERE `title` = 'Point Hope' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id point_lay title Point Lay
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','point_lay','Point Lay','6210', '0',' 1877-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id point_lay
UPDATE `list_options` SET `notes` = '1877-0' WHERE `option_id` = 'point_lay' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Point Lay
UPDATE `list_options` SET `notes` = '1877-0' WHERE `title` = 'Point Lay' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pojoaque title Pojoaque
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pojoaque','Pojoaque','6220', '0',' 1501-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id pojoaque
UPDATE `list_options` SET `notes` = '1501-6' WHERE `option_id` = 'pojoaque' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pojoaque
UPDATE `list_options` SET `notes` = '1501-6' WHERE `title` = 'Pojoaque' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pokagon_potawatomi title Pokagon Potawatomi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pokagon_potawatomi','Pokagon Potawatomi','6230', '0',' 1483-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id pokagon_potawatomi
UPDATE `list_options` SET `notes` = '1483-7' WHERE `option_id` = 'pokagon_potawatomi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pokagon Potawatomi
UPDATE `list_options` SET `notes` = '1483-7' WHERE `title` = 'Pokagon Potawatomi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id polish title Polish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','polish','Polish','6240', '0',' 2115-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id polish
UPDATE `list_options` SET `notes` = '2115-4' WHERE `option_id` = 'polish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Polish
UPDATE `list_options` SET `notes` = '2115-4' WHERE `title` = 'Polish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id polynesian title Polynesian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','polynesian','Polynesian','6250', '0',' 2078-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id polynesian
UPDATE `list_options` SET `notes` = '2078-4' WHERE `option_id` = 'polynesian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Polynesian
UPDATE `list_options` SET `notes` = '2078-4' WHERE `title` = 'Polynesian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pomo title Pomo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pomo','Pomo','6260', '0',' 1464-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id pomo
UPDATE `list_options` SET `notes` = '1464-7' WHERE `option_id` = 'pomo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pomo
UPDATE `list_options` SET `notes` = '1464-7' WHERE `title` = 'Pomo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ponca title Ponca
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ponca','Ponca','6270', '0',' 1474-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id ponca
UPDATE `list_options` SET `notes` = '1474-6' WHERE `option_id` = 'ponca' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ponca
UPDATE `list_options` SET `notes` = '1474-6' WHERE `title` = 'Ponca' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id poospatuck title Poospatuck
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','poospatuck','Poospatuck','6280', '0',' 1328-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id poospatuck
UPDATE `list_options` SET `notes` = '1328-4' WHERE `option_id` = 'poospatuck' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Poospatuck
UPDATE `list_options` SET `notes` = '1328-4' WHERE `title` = 'Poospatuck' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id port_gamble_klallam title Port Gamble Klallam
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','port_gamble_klallam','Port Gamble Klallam','6290', '0',' 1315-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id port_gamble_klallam
UPDATE `list_options` SET `notes` = '1315-1' WHERE `option_id` = 'port_gamble_klallam' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Port Gamble Klallam
UPDATE `list_options` SET `notes` = '1315-1' WHERE `title` = 'Port Gamble Klallam' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id port_graham title Port Graham
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','port_graham','Port Graham','6300', '0',' 1988-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id port_graham
UPDATE `list_options` SET `notes` = '1988-5' WHERE `option_id` = 'port_graham' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Port Graham
UPDATE `list_options` SET `notes` = '1988-5' WHERE `title` = 'Port Graham' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id port_heiden title Port Heiden
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','port_heiden','Port Heiden','6310', '0',' 1982-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id port_heiden
UPDATE `list_options` SET `notes` = '1982-8' WHERE `option_id` = 'port_heiden' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Port Heiden
UPDATE `list_options` SET `notes` = '1982-8' WHERE `title` = 'Port Heiden' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id port_lions title Port Lions
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','port_lions','Port Lions','6320', '0',' 2000-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id port_lions
UPDATE `list_options` SET `notes` = '2000-8' WHERE `option_id` = 'port_lions' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Port Lions
UPDATE `list_options` SET `notes` = '2000-8' WHERE `title` = 'Port Lions' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id port_madison title Port Madison
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','port_madison','Port Madison','6330', '0',' 1525-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id port_madison
UPDATE `list_options` SET `notes` = '1525-5' WHERE `option_id` = 'port_madison' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Port Madison
UPDATE `list_options` SET `notes` = '1525-5' WHERE `title` = 'Port Madison' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id portage_creek title Portage Creek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','portage_creek','Portage Creek','6340', '0',' 1948-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id portage_creek
UPDATE `list_options` SET `notes` = '1948-9' WHERE `option_id` = 'portage_creek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Portage Creek
UPDATE `list_options` SET `notes` = '1948-9' WHERE `title` = 'Portage Creek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id potawatomi title Potawatomi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','potawatomi','Potawatomi','6350', '0',' 1478-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id potawatomi
UPDATE `list_options` SET `notes` = '1478-7' WHERE `option_id` = 'potawatomi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Potawatomi
UPDATE `list_options` SET `notes` = '1478-7' WHERE `title` = 'Potawatomi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id powhatan title Powhatan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','powhatan','Powhatan','6360', '0',' 1487-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id powhatan
UPDATE `list_options` SET `notes` = '1487-8' WHERE `option_id` = 'powhatan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Powhatan
UPDATE `list_options` SET `notes` = '1487-8' WHERE `title` = 'Powhatan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id prairie_band title Prairie Band
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','prairie_band','Prairie Band','6370', '0',' 1484-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id prairie_band
UPDATE `list_options` SET `notes` = '1484-5' WHERE `option_id` = 'prairie_band' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Prairie Band
UPDATE `list_options` SET `notes` = '1484-5' WHERE `title` = 'Prairie Band' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id prairie_island_sioux title Prairie Island Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','prairie_island_sioux','Prairie Island Sioux','6380', '0',' 1625-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id prairie_island_sioux
UPDATE `list_options` SET `notes` = '1625-3' WHERE `option_id` = 'prairie_island_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Prairie Island Sioux
UPDATE `list_options` SET `notes` = '1625-3' WHERE `title` = 'Prairie Island Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id principal_creek_indian_nation title Principal Creek Indian Nation
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','principal_creek_indian_nation','Principal Creek Indian Nation','6390', '0',' 1202-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id principal_creek_indian_nation
UPDATE `list_options` SET `notes` = '1202-1' WHERE `option_id` = 'principal_creek_indian_nation' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Principal Creek Indian Nation
UPDATE `list_options` SET `notes` = '1202-1' WHERE `title` = 'Principal Creek Indian Nation' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id prior_lake_sioux title Prior Lake Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','prior_lake_sioux','Prior Lake Sioux','6400', '0',' 1626-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id prior_lake_sioux
UPDATE `list_options` SET `notes` = '1626-1' WHERE `option_id` = 'prior_lake_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Prior Lake Sioux
UPDATE `list_options` SET `notes` = '1626-1' WHERE `title` = 'Prior Lake Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pueblo title Pueblo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pueblo','Pueblo','6410', '0',' 1489-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id pueblo
UPDATE `list_options` SET `notes` = '1489-4' WHERE `option_id` = 'pueblo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pueblo
UPDATE `list_options` SET `notes` = '1489-4' WHERE `title` = 'Pueblo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id puget_sound_salish title Puget Sound Salish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','puget_sound_salish','Puget Sound Salish','6420', '0',' 1518-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id puget_sound_salish
UPDATE `list_options` SET `notes` = '1518-0' WHERE `option_id` = 'puget_sound_salish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Puget Sound Salish
UPDATE `list_options` SET `notes` = '1518-0' WHERE `title` = 'Puget Sound Salish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id puyallup title Puyallup
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','puyallup','Puyallup','6430', '0',' 1526-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id puyallup
UPDATE `list_options` SET `notes` = '1526-3' WHERE `option_id` = 'puyallup' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Puyallup
UPDATE `list_options` SET `notes` = '1526-3' WHERE `title` = 'Puyallup' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id pyramid_lake title Pyramid Lake
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','pyramid_lake','Pyramid Lake','6440', '0',' 1431-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id pyramid_lake
UPDATE `list_options` SET `notes` = '1431-6' WHERE `option_id` = 'pyramid_lake' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Pyramid Lake
UPDATE `list_options` SET `notes` = '1431-6' WHERE `title` = 'Pyramid Lake' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id qagan_toyagungin title Qagan Toyagungin
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','qagan_toyagungin','Qagan Toyagungin','6450', '0',' 2019-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id qagan_toyagungin
UPDATE `list_options` SET `notes` = '2019-8' WHERE `option_id` = 'qagan_toyagungin' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Qagan Toyagungin
UPDATE `list_options` SET `notes` = '2019-8' WHERE `title` = 'Qagan Toyagungin' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id qawalangin title Qawalangin
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','qawalangin','Qawalangin','6460', '0',' 2020-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id qawalangin
UPDATE `list_options` SET `notes` = '2020-6' WHERE `option_id` = 'qawalangin' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Qawalangin
UPDATE `list_options` SET `notes` = '2020-6' WHERE `title` = 'Qawalangin' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id quapaw title Quapaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','quapaw','Quapaw','6470', '0',' 1541-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id quapaw
UPDATE `list_options` SET `notes` = '1541-2' WHERE `option_id` = 'quapaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Quapaw
UPDATE `list_options` SET `notes` = '1541-2' WHERE `title` = 'Quapaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id quechan title Quechan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','quechan','Quechan','6480', '0',' 1730-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id quechan
UPDATE `list_options` SET `notes` = '1730-1' WHERE `option_id` = 'quechan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Quechan
UPDATE `list_options` SET `notes` = '1730-1' WHERE `title` = 'Quechan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id quileute title Quileute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','quileute','Quileute','6490', '0',' 1084-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id quileute
UPDATE `list_options` SET `notes` = '1084-3' WHERE `option_id` = 'quileute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Quileute
UPDATE `list_options` SET `notes` = '1084-3' WHERE `title` = 'Quileute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id quinault title Quinault
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','quinault','Quinault','6500', '0',' 1543-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id quinault
UPDATE `list_options` SET `notes` = '1543-8' WHERE `option_id` = 'quinault' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Quinault
UPDATE `list_options` SET `notes` = '1543-8' WHERE `title` = 'Quinault' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id quinhagak title Quinhagak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','quinhagak','Quinhagak','6510', '0',' 1949-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id quinhagak
UPDATE `list_options` SET `notes` = '1949-7' WHERE `option_id` = 'quinhagak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Quinhagak
UPDATE `list_options` SET `notes` = '1949-7' WHERE `title` = 'Quinhagak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ramah_navajo title Ramah Navajo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ramah_navajo','Ramah Navajo','6520', '0',' 1385-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id ramah_navajo
UPDATE `list_options` SET `notes` = '1385-4' WHERE `option_id` = 'ramah_navajo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ramah Navajo
UPDATE `list_options` SET `notes` = '1385-4' WHERE `title` = 'Ramah Navajo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id rampart title Rampart
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','rampart','Rampart','6530', '0',' 1790-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id rampart
UPDATE `list_options` SET `notes` = '1790-5' WHERE `option_id` = 'rampart' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Rampart
UPDATE `list_options` SET `notes` = '1790-5' WHERE `title` = 'Rampart' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id rampough_mountain title Rampough Mountain
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','rampough_mountain','Rampough Mountain','6540', '0',' 1219-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id rampough_mountain
UPDATE `list_options` SET `notes` = '1219-5' WHERE `option_id` = 'rampough_mountain' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Rampough Mountain
UPDATE `list_options` SET `notes` = '1219-5' WHERE `title` = 'Rampough Mountain' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id rappahannock title Rappahannock
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','rappahannock','Rappahannock','6550', '0',' 1545-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id rappahannock
UPDATE `list_options` SET `notes` = '1545-3' WHERE `option_id` = 'rappahannock' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Rappahannock
UPDATE `list_options` SET `notes` = '1545-3' WHERE `title` = 'Rappahannock' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id red_cliff_chippewa title Red Cliff Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','red_cliff_chippewa','Red Cliff Chippewa','6560', '0',' 1141-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id red_cliff_chippewa
UPDATE `list_options` SET `notes` = '1141-1' WHERE `option_id` = 'red_cliff_chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Red Cliff Chippewa
UPDATE `list_options` SET `notes` = '1141-1' WHERE `title` = 'Red Cliff Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id red_devil title Red Devil
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','red_devil','Red Devil','6570', '0',' 1950-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id red_devil
UPDATE `list_options` SET `notes` = '1950-5' WHERE `option_id` = 'red_devil' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Red Devil
UPDATE `list_options` SET `notes` = '1950-5' WHERE `title` = 'Red Devil' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id red_lake_chippewa title Red Lake Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','red_lake_chippewa','Red Lake Chippewa','6580', '0',' 1142-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id red_lake_chippewa
UPDATE `list_options` SET `notes` = '1142-9' WHERE `option_id` = 'red_lake_chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Red Lake Chippewa
UPDATE `list_options` SET `notes` = '1142-9' WHERE `title` = 'Red Lake Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id red_wood title Red Wood
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','red_wood','Red Wood','6590', '0',' 1061-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id red_wood
UPDATE `list_options` SET `notes` = '1061-1' WHERE `option_id` = 'red_wood' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Red Wood
UPDATE `list_options` SET `notes` = '1061-1' WHERE `title` = 'Red Wood' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id reno-sparks title Reno-Sparks
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','reno-sparks','Reno-Sparks','6600', '0',' 1547-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id reno-sparks
UPDATE `list_options` SET `notes` = '1547-9' WHERE `option_id` = 'reno-sparks' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Reno-Sparks
UPDATE `list_options` SET `notes` = '1547-9' WHERE `title` = 'Reno-Sparks' AND `list_id` = 'race';
#EndIf

#IfNotRow2D list_options list_id race option_id rocky_boys_chippewa_cree
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','rocky_boys_chippewa_cree',"Rocky Boy's Chippewa Cree",'6610', '0',' 1151-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id rocky_boys_chippewa_cree
UPDATE `list_options` SET `notes` = '1151-0' WHERE `option_id` = 'rocky_boys_chippewa_cree' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id rosebud_sioux title Rosebud Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','rosebud_sioux','Rosebud Sioux','6620', '0',' 1627-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id rosebud_sioux
UPDATE `list_options` SET `notes` = '1627-9' WHERE `option_id` = 'rosebud_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Rosebud Sioux
UPDATE `list_options` SET `notes` = '1627-9' WHERE `title` = 'Rosebud Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id round_valley title Round Valley
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','round_valley','Round Valley','6630', '0',' 1549-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id round_valley
UPDATE `list_options` SET `notes` = '1549-5' WHERE `option_id` = 'round_valley' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Round Valley
UPDATE `list_options` SET `notes` = '1549-5' WHERE `title` = 'Round Valley' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ruby title Ruby
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ruby','Ruby','6640', '0',' 1791-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id ruby
UPDATE `list_options` SET `notes` = '1791-3' WHERE `option_id` = 'ruby' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ruby
UPDATE `list_options` SET `notes` = '1791-3' WHERE `title` = 'Ruby' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ruby_valley title Ruby Valley
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ruby_valley','Ruby Valley','6650', '0',' 1593-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id ruby_valley
UPDATE `list_options` SET `notes` = '1593-3' WHERE `option_id` = 'ruby_valley' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ruby Valley
UPDATE `list_options` SET `notes` = '1593-3' WHERE `title` = 'Ruby Valley' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sac_and_fox title Sac and Fox
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sac_and_fox','Sac and Fox','6660', '0',' 1551-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id sac_and_fox
UPDATE `list_options` SET `notes` = '1551-1' WHERE `option_id` = 'sac_and_fox' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sac and Fox
UPDATE `list_options` SET `notes` = '1551-1' WHERE `title` = 'Sac and Fox' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id saginaw_chippewa title Saginaw Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','saginaw_chippewa','Saginaw Chippewa','6670', '0',' 1143-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id saginaw_chippewa
UPDATE `list_options` SET `notes` = '1143-7' WHERE `option_id` = 'saginaw_chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Saginaw Chippewa
UPDATE `list_options` SET `notes` = '1143-7' WHERE `title` = 'Saginaw Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id saipanese title Saipanese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','saipanese','Saipanese','6680', '0',' 2095-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id saipanese
UPDATE `list_options` SET `notes` = '2095-8' WHERE `option_id` = 'saipanese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Saipanese
UPDATE `list_options` SET `notes` = '2095-8' WHERE `title` = 'Saipanese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id salamatof title Salamatof
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','salamatof','Salamatof','6690', '0',' 1792-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id salamatof
UPDATE `list_options` SET `notes` = '1792-1' WHERE `option_id` = 'salamatof' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Salamatof
UPDATE `list_options` SET `notes` = '1792-1' WHERE `title` = 'Salamatof' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id salinan title Salinan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','salinan','Salinan','6700', '0',' 1556-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id salinan
UPDATE `list_options` SET `notes` = '1556-0' WHERE `option_id` = 'salinan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Salinan
UPDATE `list_options` SET `notes` = '1556-0' WHERE `title` = 'Salinan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id salish title Salish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','salish','Salish','6710', '0',' 1558-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id salish
UPDATE `list_options` SET `notes` = '1558-6' WHERE `option_id` = 'salish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Salish
UPDATE `list_options` SET `notes` = '1558-6' WHERE `title` = 'Salish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id salish_and_kootenai title Salish and Kootenai
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','salish_and_kootenai','Salish and Kootenai','6720', '0',' 1560-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id salish_and_kootenai
UPDATE `list_options` SET `notes` = '1560-2' WHERE `option_id` = 'salish_and_kootenai' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Salish and Kootenai
UPDATE `list_options` SET `notes` = '1560-2' WHERE `title` = 'Salish and Kootenai' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id salt_river_pima-maricopa title Salt River Pima-Maricopa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','salt_river_pima-maricopa','Salt River Pima-Maricopa','6730', '0',' 1458-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id salt_river_pima-maricopa
UPDATE `list_options` SET `notes` = '1458-9' WHERE `option_id` = 'salt_river_pima-maricopa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Salt River Pima-Maricopa
UPDATE `list_options` SET `notes` = '1458-9' WHERE `title` = 'Salt River Pima-Maricopa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id samish title Samish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','samish','Samish','6740', '0',' 1527-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id samish
UPDATE `list_options` SET `notes` = '1527-1' WHERE `option_id` = 'samish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Samish
UPDATE `list_options` SET `notes` = '1527-1' WHERE `title` = 'Samish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id samoan title Samoan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','samoan','Samoan','6750', '0',' 2080-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id samoan
UPDATE `list_options` SET `notes` = '2080-0' WHERE `option_id` = 'samoan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Samoan
UPDATE `list_options` SET `notes` = '2080-0' WHERE `title` = 'Samoan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id san_carlos_apache title San Carlos Apache
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','san_carlos_apache','San Carlos Apache','6760', '0',' 1018-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id san_carlos_apache
UPDATE `list_options` SET `notes` = '1018-1' WHERE `option_id` = 'san_carlos_apache' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title San Carlos Apache
UPDATE `list_options` SET `notes` = '1018-1' WHERE `title` = 'San Carlos Apache' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id san_felipe title San Felipe
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','san_felipe','San Felipe','6770', '0',' 1502-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id san_felipe
UPDATE `list_options` SET `notes` = '1502-4' WHERE `option_id` = 'san_felipe' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title San Felipe
UPDATE `list_options` SET `notes` = '1502-4' WHERE `title` = 'San Felipe' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id san_ildefonso title San Ildefonso
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','san_ildefonso','San Ildefonso','6780', '0',' 1503-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id san_ildefonso
UPDATE `list_options` SET `notes` = '1503-2' WHERE `option_id` = 'san_ildefonso' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title San Ildefonso
UPDATE `list_options` SET `notes` = '1503-2' WHERE `title` = 'San Ildefonso' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id san_juan title San Juan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','san_juan','San Juan','6790', '0',' 1506-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id san_juan
UPDATE `list_options` SET `notes` = '1506-5' WHERE `option_id` = 'san_juan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title San Juan
UPDATE `list_options` SET `notes` = '1506-5' WHERE `title` = 'San Juan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id san_juan_de title San Juan De
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','san_juan_de','San Juan De','6800', '0',' 1505-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id san_juan_de
UPDATE `list_options` SET `notes` = '1505-7' WHERE `option_id` = 'san_juan_de' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title San Juan De
UPDATE `list_options` SET `notes` = '1505-7' WHERE `title` = 'San Juan De' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id san_juan_pueblo title San Juan Pueblo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','san_juan_pueblo','San Juan Pueblo','6810', '0',' 1504-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id san_juan_pueblo
UPDATE `list_options` SET `notes` = '1504-0' WHERE `option_id` = 'san_juan_pueblo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title San Juan Pueblo
UPDATE `list_options` SET `notes` = '1504-0' WHERE `title` = 'San Juan Pueblo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id san_juan_southern_paiute title San Juan Southern Paiute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','san_juan_southern_paiute','San Juan Southern Paiute','6820', '0',' 1432-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id san_juan_southern_paiute
UPDATE `list_options` SET `notes` = '1432-4' WHERE `option_id` = 'san_juan_southern_paiute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title San Juan Southern Paiute
UPDATE `list_options` SET `notes` = '1432-4' WHERE `title` = 'San Juan Southern Paiute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id san_manual title San Manual
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','san_manual','San Manual','6830', '0',' 1574-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id san_manual
UPDATE `list_options` SET `notes` = '1574-3' WHERE `option_id` = 'san_manual' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title San Manual
UPDATE `list_options` SET `notes` = '1574-3' WHERE `title` = 'San Manual' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id san_pasqual title San Pasqual
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','san_pasqual','San Pasqual','6840', '0',' 1229-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id san_pasqual
UPDATE `list_options` SET `notes` = '1229-4' WHERE `option_id` = 'san_pasqual' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title San Pasqual
UPDATE `list_options` SET `notes` = '1229-4' WHERE `title` = 'San Pasqual' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id san_xavier title San Xavier
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','san_xavier','San Xavier','6850', '0',' 1656-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id san_xavier
UPDATE `list_options` SET `notes` = '1656-8' WHERE `option_id` = 'san_xavier' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title San Xavier
UPDATE `list_options` SET `notes` = '1656-8' WHERE `title` = 'San Xavier' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sand_hill title Sand Hill
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sand_hill','Sand Hill','6860', '0',' 1220-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id sand_hill
UPDATE `list_options` SET `notes` = '1220-3' WHERE `option_id` = 'sand_hill' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sand Hill
UPDATE `list_options` SET `notes` = '1220-3' WHERE `title` = 'Sand Hill' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sand_point title Sand Point
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sand_point','Sand Point','6870', '0',' 2023-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id sand_point
UPDATE `list_options` SET `notes` = '2023-0' WHERE `option_id` = 'sand_point' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sand Point
UPDATE `list_options` SET `notes` = '2023-0' WHERE `title` = 'Sand Point' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sandia title Sandia
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sandia','Sandia','6880', '0',' 1507-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id sandia
UPDATE `list_options` SET `notes` = '1507-3' WHERE `option_id` = 'sandia' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sandia
UPDATE `list_options` SET `notes` = '1507-3' WHERE `title` = 'Sandia' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sans_arc_sioux title Sans Arc Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sans_arc_sioux','Sans Arc Sioux','6890', '0',' 1628-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id sans_arc_sioux
UPDATE `list_options` SET `notes` = '1628-7' WHERE `option_id` = 'sans_arc_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sans Arc Sioux
UPDATE `list_options` SET `notes` = '1628-7' WHERE `title` = 'Sans Arc Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id santa_ana title Santa Ana
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','santa_ana','Santa Ana','6900', '0',' 1508-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id santa_ana
UPDATE `list_options` SET `notes` = '1508-1' WHERE `option_id` = 'santa_ana' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Santa Ana
UPDATE `list_options` SET `notes` = '1508-1' WHERE `title` = 'Santa Ana' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id santa_clara title Santa Clara
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','santa_clara','Santa Clara','6910', '0',' 1509-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id santa_clara
UPDATE `list_options` SET `notes` = '1509-9' WHERE `option_id` = 'santa_clara' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Santa Clara
UPDATE `list_options` SET `notes` = '1509-9' WHERE `title` = 'Santa Clara' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id santa_rosa title Santa Rosa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','santa_rosa','Santa Rosa','6920', '0',' 1062-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id santa_rosa
UPDATE `list_options` SET `notes` = '1062-9' WHERE `option_id` = 'santa_rosa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Santa Rosa
UPDATE `list_options` SET `notes` = '1062-9' WHERE `title` = 'Santa Rosa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id santa_rosa_cahuilla title Santa Rosa Cahuilla
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','santa_rosa_cahuilla','Santa Rosa Cahuilla','6930', '0',' 1050-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id santa_rosa_cahuilla
UPDATE `list_options` SET `notes` = '1050-4' WHERE `option_id` = 'santa_rosa_cahuilla' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Santa Rosa Cahuilla
UPDATE `list_options` SET `notes` = '1050-4' WHERE `title` = 'Santa Rosa Cahuilla' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id santa_ynez title Santa Ynez
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','santa_ynez','Santa Ynez','6940', '0',' 1163-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id santa_ynez
UPDATE `list_options` SET `notes` = '1163-5' WHERE `option_id` = 'santa_ynez' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Santa Ynez
UPDATE `list_options` SET `notes` = '1163-5' WHERE `title` = 'Santa Ynez' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id santa_ysabel title Santa Ysabel
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','santa_ysabel','Santa Ysabel','6950', '0',' 1230-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id santa_ysabel
UPDATE `list_options` SET `notes` = '1230-2' WHERE `option_id` = 'santa_ysabel' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Santa Ysabel
UPDATE `list_options` SET `notes` = '1230-2' WHERE `title` = 'Santa Ysabel' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id santee_sioux title Santee Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','santee_sioux','Santee Sioux','6960', '0',' 1629-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id santee_sioux
UPDATE `list_options` SET `notes` = '1629-5' WHERE `option_id` = 'santee_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Santee Sioux
UPDATE `list_options` SET `notes` = '1629-5' WHERE `title` = 'Santee Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id santo_domingo title Santo Domingo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','santo_domingo','Santo Domingo','6970', '0',' 1510-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id santo_domingo
UPDATE `list_options` SET `notes` = '1510-7' WHERE `option_id` = 'santo_domingo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Santo Domingo
UPDATE `list_options` SET `notes` = '1510-7' WHERE `title` = 'Santo Domingo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sauk-suiattle title Sauk-Suiattle
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sauk-suiattle','Sauk-Suiattle','6980', '0',' 1528-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id sauk-suiattle
UPDATE `list_options` SET `notes` = '1528-9' WHERE `option_id` = 'sauk-suiattle' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sauk-Suiattle
UPDATE `list_options` SET `notes` = '1528-9' WHERE `title` = 'Sauk-Suiattle' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sault_ste_marie_chippewa title Sault Ste. Marie Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sault_ste_marie_chippewa','Sault Ste. Marie Chippewa','6990', '0',' 1145-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id sault_ste_marie_chippewa
UPDATE `list_options` SET `notes` = '1145-2' WHERE `option_id` = 'sault_ste_marie_chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sault Ste. Marie Chippewa
UPDATE `list_options` SET `notes` = '1145-2' WHERE `title` = 'Sault Ste. Marie Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id savoonga title Savoonga
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','savoonga','Savoonga','7000', '0',' 1893-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id savoonga
UPDATE `list_options` SET `notes` = '1893-7' WHERE `option_id` = 'savoonga' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Savoonga
UPDATE `list_options` SET `notes` = '1893-7' WHERE `title` = 'Savoonga' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id saxman title Saxman
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','saxman','Saxman','7010', '0',' 1830-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id saxman
UPDATE `list_options` SET `notes` = '1830-9' WHERE `option_id` = 'saxman' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Saxman
UPDATE `list_options` SET `notes` = '1830-9' WHERE `title` = 'Saxman' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id scammon_bay title Scammon Bay
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','scammon_bay','Scammon Bay','7020', '0',' 1952-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id scammon_bay
UPDATE `list_options` SET `notes` = '1952-1' WHERE `option_id` = 'scammon_bay' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Scammon Bay
UPDATE `list_options` SET `notes` = '1952-1' WHERE `title` = 'Scammon Bay' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id schaghticoke title Schaghticoke
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','schaghticoke','Schaghticoke','7030', '0',' 1562-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id schaghticoke
UPDATE `list_options` SET `notes` = '1562-8' WHERE `option_id` = 'schaghticoke' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Schaghticoke
UPDATE `list_options` SET `notes` = '1562-8' WHERE `title` = 'Schaghticoke' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id scott_valley title Scott Valley
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','scott_valley','Scott Valley','7040', '0',' 1564-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id scott_valley
UPDATE `list_options` SET `notes` = '1564-4' WHERE `option_id` = 'scott_valley' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Scott Valley
UPDATE `list_options` SET `notes` = '1564-4' WHERE `title` = 'Scott Valley' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id scottish title Scottish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','scottish','Scottish','7050', '0',' 2116-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id scottish
UPDATE `list_options` SET `notes` = '2116-2' WHERE `option_id` = 'scottish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Scottish
UPDATE `list_options` SET `notes` = '2116-2' WHERE `title` = 'Scottish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id scotts_valley title Scotts Valley
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','scotts_valley','Scotts Valley','7060', '0',' 1470-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id scotts_valley
UPDATE `list_options` SET `notes` = '1470-4' WHERE `option_id` = 'scotts_valley' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Scotts Valley
UPDATE `list_options` SET `notes` = '1470-4' WHERE `title` = 'Scotts Valley' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id selawik title Selawik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','selawik','Selawik','7070', '0',' 1878-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id selawik
UPDATE `list_options` SET `notes` = '1878-8' WHERE `option_id` = 'selawik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Selawik
UPDATE `list_options` SET `notes` = '1878-8' WHERE `title` = 'Selawik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id seldovia title Seldovia
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','seldovia','Seldovia','7080', '0',' 1793-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id seldovia
UPDATE `list_options` SET `notes` = '1793-9' WHERE `option_id` = 'seldovia' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Seldovia
UPDATE `list_options` SET `notes` = '1793-9' WHERE `title` = 'Seldovia' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sells title Sells
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sells','Sells','7090', '0',' 1657-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id sells
UPDATE `list_options` SET `notes` = '1657-6' WHERE `option_id` = 'sells' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sells
UPDATE `list_options` SET `notes` = '1657-6' WHERE `title` = 'Sells' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id seminole title Seminole
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','seminole','Seminole','7100', '0',' 1566-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id seminole
UPDATE `list_options` SET `notes` = '1566-9' WHERE `option_id` = 'seminole' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Seminole
UPDATE `list_options` SET `notes` = '1566-9' WHERE `title` = 'Seminole' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id seneca title Seneca
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','seneca','Seneca','7110', '0',' 1290-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id seneca
UPDATE `list_options` SET `notes` = '1290-6' WHERE `option_id` = 'seneca' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Seneca
UPDATE `list_options` SET `notes` = '1290-6' WHERE `title` = 'Seneca' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id seneca_nation title Seneca Nation
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','seneca_nation','Seneca Nation','7120', '0',' 1291-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id seneca_nation
UPDATE `list_options` SET `notes` = '1291-4' WHERE `option_id` = 'seneca_nation' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Seneca Nation
UPDATE `list_options` SET `notes` = '1291-4' WHERE `title` = 'Seneca Nation' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id seneca-cayuga title Seneca-Cayuga
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','seneca-cayuga','Seneca-Cayuga','7130', '0',' 1292-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id seneca-cayuga
UPDATE `list_options` SET `notes` = '1292-2' WHERE `option_id` = 'seneca-cayuga' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Seneca-Cayuga
UPDATE `list_options` SET `notes` = '1292-2' WHERE `title` = 'Seneca-Cayuga' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id serrano title Serrano
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','serrano','Serrano','7140', '0',' 1573-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id serrano
UPDATE `list_options` SET `notes` = '1573-5' WHERE `option_id` = 'serrano' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Serrano
UPDATE `list_options` SET `notes` = '1573-5' WHERE `title` = 'Serrano' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id setauket title Setauket
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','setauket','Setauket','7150', '0',' 1329-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id setauket
UPDATE `list_options` SET `notes` = '1329-2' WHERE `option_id` = 'setauket' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Setauket
UPDATE `list_options` SET `notes` = '1329-2' WHERE `title` = 'Setauket' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id shageluk title Shageluk
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','shageluk','Shageluk','7160', '0',' 1795-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id shageluk
UPDATE `list_options` SET `notes` = '1795-4' WHERE `option_id` = 'shageluk' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Shageluk
UPDATE `list_options` SET `notes` = '1795-4' WHERE `title` = 'Shageluk' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id shaktoolik title Shaktoolik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','shaktoolik','Shaktoolik','7170', '0',' 1879-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id shaktoolik
UPDATE `list_options` SET `notes` = '1879-6' WHERE `option_id` = 'shaktoolik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Shaktoolik
UPDATE `list_options` SET `notes` = '1879-6' WHERE `title` = 'Shaktoolik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id shasta title Shasta
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','shasta','Shasta','7180', '0',' 1576-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id shasta
UPDATE `list_options` SET `notes` = '1576-8' WHERE `option_id` = 'shasta' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Shasta
UPDATE `list_options` SET `notes` = '1576-8' WHERE `title` = 'Shasta' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id shawnee title Shawnee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','shawnee','Shawnee','7190', '0',' 1578-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id shawnee
UPDATE `list_options` SET `notes` = '1578-4' WHERE `option_id` = 'shawnee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Shawnee
UPDATE `list_options` SET `notes` = '1578-4' WHERE `title` = 'Shawnee' AND `list_id` = 'race';
#EndIf

#IfNotRow2D list_options list_id race option_id sheldons_point
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sheldons_point',"Sheldon's Point",'7200', '0',' 1953-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id sheldons_point
UPDATE `list_options` SET `notes` = '1953-9' WHERE `option_id` = 'sheldons_point' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id shinnecock title Shinnecock
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','shinnecock','Shinnecock','7210', '0',' 1582-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id shinnecock
UPDATE `list_options` SET `notes` = '1582-6' WHERE `option_id` = 'shinnecock' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Shinnecock
UPDATE `list_options` SET `notes` = '1582-6' WHERE `title` = 'Shinnecock' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id shishmaref title Shishmaref
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','shishmaref','Shishmaref','7220', '0',' 1880-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id shishmaref
UPDATE `list_options` SET `notes` = '1880-4' WHERE `option_id` = 'shishmaref' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Shishmaref
UPDATE `list_options` SET `notes` = '1880-4' WHERE `title` = 'Shishmaref' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id shoalwater_bay title Shoalwater Bay
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','shoalwater_bay','Shoalwater Bay','7230', '0',' 1584-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id shoalwater_bay
UPDATE `list_options` SET `notes` = '1584-2' WHERE `option_id` = 'shoalwater_bay' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Shoalwater Bay
UPDATE `list_options` SET `notes` = '1584-2' WHERE `title` = 'Shoalwater Bay' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id shoshone title Shoshone
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','shoshone','Shoshone','7240', '0',' 1586-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id shoshone
UPDATE `list_options` SET `notes` = '1586-7' WHERE `option_id` = 'shoshone' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Shoshone
UPDATE `list_options` SET `notes` = '1586-7' WHERE `title` = 'Shoshone' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id shoshone_paiute title Shoshone Paiute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','shoshone_paiute','Shoshone Paiute','7250', '0',' 1602-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id shoshone_paiute
UPDATE `list_options` SET `notes` = '1602-2' WHERE `option_id` = 'shoshone_paiute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Shoshone Paiute
UPDATE `list_options` SET `notes` = '1602-2' WHERE `title` = 'Shoshone Paiute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id shungnak title Shungnak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','shungnak','Shungnak','7260', '0',' 1881-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id shungnak
UPDATE `list_options` SET `notes` = '1881-2' WHERE `option_id` = 'shungnak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Shungnak
UPDATE `list_options` SET `notes` = '1881-2' WHERE `title` = 'Shungnak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id siberian_eskimo title Siberian Eskimo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','siberian_eskimo','Siberian Eskimo','7270', '0',' 1891-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id siberian_eskimo
UPDATE `list_options` SET `notes` = '1891-1' WHERE `option_id` = 'siberian_eskimo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Siberian Eskimo
UPDATE `list_options` SET `notes` = '1891-1' WHERE `title` = 'Siberian Eskimo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id siberian_yupik title Siberian Yupik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','siberian_yupik','Siberian Yupik','7280', '0',' 1894-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id siberian_yupik
UPDATE `list_options` SET `notes` = '1894-5' WHERE `option_id` = 'siberian_yupik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Siberian Yupik
UPDATE `list_options` SET `notes` = '1894-5' WHERE `title` = 'Siberian Yupik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id siletz title Siletz
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','siletz','Siletz','7290', '0',' 1607-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id siletz
UPDATE `list_options` SET `notes` = '1607-1' WHERE `option_id` = 'siletz' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Siletz
UPDATE `list_options` SET `notes` = '1607-1' WHERE `title` = 'Siletz' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id singaporean title Singaporean
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','singaporean','Singaporean','7300', '0',' 2051-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id singaporean
UPDATE `list_options` SET `notes` = '2051-1' WHERE `option_id` = 'singaporean' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Singaporean
UPDATE `list_options` SET `notes` = '2051-1' WHERE `title` = 'Singaporean' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sioux title Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sioux','Sioux','7310', '0',' 1609-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id sioux
UPDATE `list_options` SET `notes` = '1609-7' WHERE `option_id` = 'sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sioux
UPDATE `list_options` SET `notes` = '1609-7' WHERE `title` = 'Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sisseton_sioux title Sisseton Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sisseton_sioux','Sisseton Sioux','7320', '0',' 1631-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id sisseton_sioux
UPDATE `list_options` SET `notes` = '1631-1' WHERE `option_id` = 'sisseton_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sisseton Sioux
UPDATE `list_options` SET `notes` = '1631-1' WHERE `title` = 'Sisseton Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sisseton-wahpeton title Sisseton-Wahpeton
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sisseton-wahpeton','Sisseton-Wahpeton','7330', '0',' 1630-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id sisseton-wahpeton
UPDATE `list_options` SET `notes` = '1630-3' WHERE `option_id` = 'sisseton-wahpeton' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sisseton-Wahpeton
UPDATE `list_options` SET `notes` = '1630-3' WHERE `title` = 'Sisseton-Wahpeton' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sitka title Sitka
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sitka','Sitka','7340', '0',' 1831-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id sitka
UPDATE `list_options` SET `notes` = '1831-7' WHERE `option_id` = 'sitka' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sitka
UPDATE `list_options` SET `notes` = '1831-7' WHERE `title` = 'Sitka' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id siuslaw title Siuslaw
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','siuslaw','Siuslaw','7350', '0',' 1643-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id siuslaw
UPDATE `list_options` SET `notes` = '1643-6' WHERE `option_id` = 'siuslaw' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Siuslaw
UPDATE `list_options` SET `notes` = '1643-6' WHERE `title` = 'Siuslaw' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id skokomish title Skokomish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','skokomish','Skokomish','7360', '0',' 1529-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id skokomish
UPDATE `list_options` SET `notes` = '1529-7' WHERE `option_id` = 'skokomish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Skokomish
UPDATE `list_options` SET `notes` = '1529-7' WHERE `title` = 'Skokomish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id skull_valley title Skull Valley
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','skull_valley','Skull Valley','7370', '0',' 1594-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id skull_valley
UPDATE `list_options` SET `notes` = '1594-1' WHERE `option_id` = 'skull_valley' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Skull Valley
UPDATE `list_options` SET `notes` = '1594-1' WHERE `title` = 'Skull Valley' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id skykomish title Skykomish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','skykomish','Skykomish','7380', '0',' 1530-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id skykomish
UPDATE `list_options` SET `notes` = '1530-5' WHERE `option_id` = 'skykomish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Skykomish
UPDATE `list_options` SET `notes` = '1530-5' WHERE `title` = 'Skykomish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id slana title Slana
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','slana','Slana','7390', '0',' 1794-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id slana
UPDATE `list_options` SET `notes` = '1794-7' WHERE `option_id` = 'slana' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Slana
UPDATE `list_options` SET `notes` = '1794-7' WHERE `title` = 'Slana' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sleetmute title Sleetmute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sleetmute','Sleetmute','7400', '0',' 1954-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id sleetmute
UPDATE `list_options` SET `notes` = '1954-7' WHERE `option_id` = 'sleetmute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sleetmute
UPDATE `list_options` SET `notes` = '1954-7' WHERE `title` = 'Sleetmute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id snohomish title Snohomish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','snohomish','Snohomish','7410', '0',' 1531-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id snohomish
UPDATE `list_options` SET `notes` = '1531-3' WHERE `option_id` = 'snohomish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Snohomish
UPDATE `list_options` SET `notes` = '1531-3' WHERE `title` = 'Snohomish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id snoqualmie title Snoqualmie
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','snoqualmie','Snoqualmie','7420', '0',' 1532-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id snoqualmie
UPDATE `list_options` SET `notes` = '1532-1' WHERE `option_id` = 'snoqualmie' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Snoqualmie
UPDATE `list_options` SET `notes` = '1532-1' WHERE `title` = 'Snoqualmie' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id soboba title Soboba
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','soboba','Soboba','7430', '0',' 1336-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id soboba
UPDATE `list_options` SET `notes` = '1336-7' WHERE `option_id` = 'soboba' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Soboba
UPDATE `list_options` SET `notes` = '1336-7' WHERE `title` = 'Soboba' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sokoagon_chippewa title Sokoagon Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sokoagon_chippewa','Sokoagon Chippewa','7440', '0',' 1146-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id sokoagon_chippewa
UPDATE `list_options` SET `notes` = '1146-0' WHERE `option_id` = 'sokoagon_chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sokoagon Chippewa
UPDATE `list_options` SET `notes` = '1146-0' WHERE `title` = 'Sokoagon Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id solomon title Solomon
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','solomon','Solomon','7450', '0',' 1882-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id solomon
UPDATE `list_options` SET `notes` = '1882-0' WHERE `option_id` = 'solomon' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Solomon
UPDATE `list_options` SET `notes` = '1882-0' WHERE `title` = 'Solomon' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id solomon_islander title Solomon Islander
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','solomon_islander','Solomon Islander','7460', '0',' 2103-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id solomon_islander
UPDATE `list_options` SET `notes` = '2103-0' WHERE `option_id` = 'solomon_islander' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Solomon Islander
UPDATE `list_options` SET `notes` = '2103-0' WHERE `title` = 'Solomon Islander' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id south_american_indian title South American Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','south_american_indian','South American Indian','7470', '0',' 1073-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id south_american_indian
UPDATE `list_options` SET `notes` = '1073-6' WHERE `option_id` = 'south_american_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title South American Indian
UPDATE `list_options` SET `notes` = '1073-6' WHERE `title` = 'South American Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id south_fork_shoshone title South Fork Shoshone
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','south_fork_shoshone','South Fork Shoshone','7480', '0',' 1595-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id south_fork_shoshone
UPDATE `list_options` SET `notes` = '1595-8' WHERE `option_id` = 'south_fork_shoshone' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title South Fork Shoshone
UPDATE `list_options` SET `notes` = '1595-8' WHERE `title` = 'South Fork Shoshone' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id south_naknek title South Naknek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','south_naknek','South Naknek','7490', '0',' 2024-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id south_naknek
UPDATE `list_options` SET `notes` = '2024-8' WHERE `option_id` = 'south_naknek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title South Naknek
UPDATE `list_options` SET `notes` = '2024-8' WHERE `title` = 'South Naknek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id southeast_alaska title Southeast Alaska
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','southeast_alaska','Southeast Alaska','7500', '0',' 1811-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id southeast_alaska
UPDATE `list_options` SET `notes` = '1811-9' WHERE `option_id` = 'southeast_alaska' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Southeast Alaska
UPDATE `list_options` SET `notes` = '1811-9' WHERE `title` = 'Southeast Alaska' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id southeastern_indians title Southeastern Indians
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','southeastern_indians','Southeastern Indians','7510', '0',' 1244-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id southeastern_indians
UPDATE `list_options` SET `notes` = '1244-3' WHERE `option_id` = 'southeastern_indians' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Southeastern Indians
UPDATE `list_options` SET `notes` = '1244-3' WHERE `title` = 'Southeastern Indians' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id southern_arapaho title Southern Arapaho
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','southern_arapaho','Southern Arapaho','7520', '0',' 1023-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id southern_arapaho
UPDATE `list_options` SET `notes` = '1023-1' WHERE `option_id` = 'southern_arapaho' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Southern Arapaho
UPDATE `list_options` SET `notes` = '1023-1' WHERE `title` = 'Southern Arapaho' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id southern_cheyenne title Southern Cheyenne
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','southern_cheyenne','Southern Cheyenne','7530', '0',' 1104-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id southern_cheyenne
UPDATE `list_options` SET `notes` = '1104-9' WHERE `option_id` = 'southern_cheyenne' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Southern Cheyenne
UPDATE `list_options` SET `notes` = '1104-9' WHERE `title` = 'Southern Cheyenne' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id southern_paiute title Southern Paiute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','southern_paiute','Southern Paiute','7540', '0',' 1433-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id southern_paiute
UPDATE `list_options` SET `notes` = '1433-2' WHERE `option_id` = 'southern_paiute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Southern Paiute
UPDATE `list_options` SET `notes` = '1433-2' WHERE `title` = 'Southern Paiute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id spanish_american_indian title Spanish American Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','spanish_american_indian','Spanish American Indian','7550', '0',' 1074-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id spanish_american_indian
UPDATE `list_options` SET `notes` = '1074-4' WHERE `option_id` = 'spanish_american_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Spanish American Indian
UPDATE `list_options` SET `notes` = '1074-4' WHERE `title` = 'Spanish American Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id spirit_lake_sioux title Spirit Lake Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','spirit_lake_sioux','Spirit Lake Sioux','7560', '0',' 1632-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id spirit_lake_sioux
UPDATE `list_options` SET `notes` = '1632-9' WHERE `option_id` = 'spirit_lake_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Spirit Lake Sioux
UPDATE `list_options` SET `notes` = '1632-9' WHERE `title` = 'Spirit Lake Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id spokane title Spokane
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','spokane','Spokane','7570', '0',' 1645-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id spokane
UPDATE `list_options` SET `notes` = '1645-1' WHERE `option_id` = 'spokane' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Spokane
UPDATE `list_options` SET `notes` = '1645-1' WHERE `title` = 'Spokane' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id squaxin_island title Squaxin Island
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','squaxin_island','Squaxin Island','7580', '0',' 1533-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id squaxin_island
UPDATE `list_options` SET `notes` = '1533-9' WHERE `option_id` = 'squaxin_island' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Squaxin Island
UPDATE `list_options` SET `notes` = '1533-9' WHERE `title` = 'Squaxin Island' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sri_lankan title Sri Lankan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sri_lankan','Sri Lankan','7590', '0',' 2045-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id sri_lankan
UPDATE `list_options` SET `notes` = '2045-3' WHERE `option_id` = 'sri_lankan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sri Lankan
UPDATE `list_options` SET `notes` = '2045-3' WHERE `title` = 'Sri Lankan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id st_croix_chippewa title St. Croix Chippewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','st_croix_chippewa','St. Croix Chippewa','7600', '0',' 1144-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id st_croix_chippewa
UPDATE `list_options` SET `notes` = '1144-5' WHERE `option_id` = 'st_croix_chippewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title St. Croix Chippewa
UPDATE `list_options` SET `notes` = '1144-5' WHERE `title` = 'St. Croix Chippewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id st_george title St. George
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','st_george','St. George','7610', '0',' 2021-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id st_george
UPDATE `list_options` SET `notes` = '2021-4' WHERE `option_id` = 'st_george' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title St. George
UPDATE `list_options` SET `notes` = '2021-4' WHERE `title` = 'St. George' AND `list_id` = 'race';
#EndIf

#IfNotRow2D list_options list_id race option_id st_marys
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','st_marys',"St. Mary's",'7620', '0',' 1963-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id st_marys
UPDATE `list_options` SET `notes` = '1963-8' WHERE `option_id` = 'st_marys' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id st_michael title St. Michael
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','st_michael','St. Michael','7630', '0',' 1951-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id st_michael
UPDATE `list_options` SET `notes` = '1951-3' WHERE `option_id` = 'st_michael' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title St. Michael
UPDATE `list_options` SET `notes` = '1951-3' WHERE `title` = 'St. Michael' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id st_paul title St. Paul
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','st_paul','St. Paul','7640', '0',' 2022-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id st_paul
UPDATE `list_options` SET `notes` = '2022-2' WHERE `option_id` = 'st_paul' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title St. Paul
UPDATE `list_options` SET `notes` = '2022-2' WHERE `title` = 'St. Paul' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id standing_rock_sioux title Standing Rock Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','standing_rock_sioux','Standing Rock Sioux','7650', '0',' 1633-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id standing_rock_sioux
UPDATE `list_options` SET `notes` = '1633-7' WHERE `option_id` = 'standing_rock_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Standing Rock Sioux
UPDATE `list_options` SET `notes` = '1633-7' WHERE `title` = 'Standing Rock Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id star_clan_of_muscogee_creeks title Star Clan of Muscogee Creeks
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','star_clan_of_muscogee_creeks','Star Clan of Muscogee Creeks','7660', '0',' 1203-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id star_clan_of_muscogee_creeks
UPDATE `list_options` SET `notes` = '1203-9' WHERE `option_id` = 'star_clan_of_muscogee_creeks' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Star Clan of Muscogee Creeks
UPDATE `list_options` SET `notes` = '1203-9' WHERE `title` = 'Star Clan of Muscogee Creeks' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id stebbins title Stebbins
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','stebbins','Stebbins','7670', '0',' 1955-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id stebbins
UPDATE `list_options` SET `notes` = '1955-4' WHERE `option_id` = 'stebbins' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Stebbins
UPDATE `list_options` SET `notes` = '1955-4' WHERE `title` = 'Stebbins' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id steilacoom title Steilacoom
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','steilacoom','Steilacoom','7680', '0',' 1534-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id steilacoom
UPDATE `list_options` SET `notes` = '1534-7' WHERE `option_id` = 'steilacoom' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Steilacoom
UPDATE `list_options` SET `notes` = '1534-7' WHERE `title` = 'Steilacoom' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id stevens title Stevens
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','stevens','Stevens','7690', '0',' 1796-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id stevens
UPDATE `list_options` SET `notes` = '1796-2' WHERE `option_id` = 'stevens' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Stevens
UPDATE `list_options` SET `notes` = '1796-2' WHERE `title` = 'Stevens' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id stewart title Stewart
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','stewart','Stewart','7700', '0',' 1647-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id stewart
UPDATE `list_options` SET `notes` = '1647-7' WHERE `option_id` = 'stewart' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Stewart
UPDATE `list_options` SET `notes` = '1647-7' WHERE `title` = 'Stewart' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id stillaguamish title Stillaguamish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','stillaguamish','Stillaguamish','7710', '0',' 1535-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id stillaguamish
UPDATE `list_options` SET `notes` = '1535-4' WHERE `option_id` = 'stillaguamish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Stillaguamish
UPDATE `list_options` SET `notes` = '1535-4' WHERE `title` = 'Stillaguamish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id stockbridge title Stockbridge
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','stockbridge','Stockbridge','7720', '0',' 1649-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id stockbridge
UPDATE `list_options` SET `notes` = '1649-3' WHERE `option_id` = 'stockbridge' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Stockbridge
UPDATE `list_options` SET `notes` = '1649-3' WHERE `title` = 'Stockbridge' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id stony_river title Stony River
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','stony_river','Stony River','7730', '0',' 1797-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id stony_river
UPDATE `list_options` SET `notes` = '1797-0' WHERE `option_id` = 'stony_river' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Stony River
UPDATE `list_options` SET `notes` = '1797-0' WHERE `title` = 'Stony River' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id stonyford title Stonyford
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','stonyford','Stonyford','7740', '0',' 1471-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id stonyford
UPDATE `list_options` SET `notes` = '1471-2' WHERE `option_id` = 'stonyford' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Stonyford
UPDATE `list_options` SET `notes` = '1471-2' WHERE `title` = 'Stonyford' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sugpiaq title Sugpiaq
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sugpiaq','Sugpiaq','7750', '0',' 2002-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id sugpiaq
UPDATE `list_options` SET `notes` = '2002-4' WHERE `option_id` = 'sugpiaq' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sugpiaq
UPDATE `list_options` SET `notes` = '2002-4' WHERE `title` = 'Sugpiaq' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sulphur_bank title Sulphur Bank
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sulphur_bank','Sulphur Bank','7760', '0',' 1472-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id sulphur_bank
UPDATE `list_options` SET `notes` = '1472-0' WHERE `option_id` = 'sulphur_bank' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sulphur Bank
UPDATE `list_options` SET `notes` = '1472-0' WHERE `title` = 'Sulphur Bank' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id summit_lake title Summit Lake
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','summit_lake','Summit Lake','7770', '0',' 1434-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id summit_lake
UPDATE `list_options` SET `notes` = '1434-0' WHERE `option_id` = 'summit_lake' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Summit Lake
UPDATE `list_options` SET `notes` = '1434-0' WHERE `title` = 'Summit Lake' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id suqpigaq title Suqpigaq
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','suqpigaq','Suqpigaq','7780', '0',' 2004-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id suqpigaq
UPDATE `list_options` SET `notes` = '2004-0' WHERE `option_id` = 'suqpigaq' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Suqpigaq
UPDATE `list_options` SET `notes` = '2004-0' WHERE `title` = 'Suqpigaq' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id suquamish title Suquamish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','suquamish','Suquamish','7790', '0',' 1536-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id suquamish
UPDATE `list_options` SET `notes` = '1536-2' WHERE `option_id` = 'suquamish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Suquamish
UPDATE `list_options` SET `notes` = '1536-2' WHERE `title` = 'Suquamish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id susanville title Susanville
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','susanville','Susanville','7800', '0',' 1651-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id susanville
UPDATE `list_options` SET `notes` = '1651-9' WHERE `option_id` = 'susanville' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Susanville
UPDATE `list_options` SET `notes` = '1651-9' WHERE `title` = 'Susanville' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id susquehanock title Susquehanock
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','susquehanock','Susquehanock','7810', '0',' 1245-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id susquehanock
UPDATE `list_options` SET `notes` = '1245-0' WHERE `option_id` = 'susquehanock' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Susquehanock
UPDATE `list_options` SET `notes` = '1245-0' WHERE `title` = 'Susquehanock' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id swinomish title Swinomish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','swinomish','Swinomish','7820', '0',' 1537-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id swinomish
UPDATE `list_options` SET `notes` = '1537-0' WHERE `option_id` = 'swinomish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Swinomish
UPDATE `list_options` SET `notes` = '1537-0' WHERE `title` = 'Swinomish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id sycuan title Sycuan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','sycuan','Sycuan','7830', '0',' 1231-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id sycuan
UPDATE `list_options` SET `notes` = '1231-0' WHERE `option_id` = 'sycuan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Sycuan
UPDATE `list_options` SET `notes` = '1231-0' WHERE `title` = 'Sycuan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id syrian title Syrian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','syrian','Syrian','7840', '0',' 2125-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id syrian
UPDATE `list_options` SET `notes` = '2125-3' WHERE `option_id` = 'syrian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Syrian
UPDATE `list_options` SET `notes` = '2125-3' WHERE `title` = 'Syrian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id table_bluff title Table Bluff
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','table_bluff','Table Bluff','7850', '0',' 1705-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id table_bluff
UPDATE `list_options` SET `notes` = '1705-3' WHERE `option_id` = 'table_bluff' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Table Bluff
UPDATE `list_options` SET `notes` = '1705-3' WHERE `title` = 'Table Bluff' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tachi title Tachi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tachi','Tachi','7860', '0',' 1719-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id tachi
UPDATE `list_options` SET `notes` = '1719-4' WHERE `option_id` = 'tachi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tachi
UPDATE `list_options` SET `notes` = '1719-4' WHERE `title` = 'Tachi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tahitian title Tahitian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tahitian','Tahitian','7870', '0',' 2081-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id tahitian
UPDATE `list_options` SET `notes` = '2081-8' WHERE `option_id` = 'tahitian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tahitian
UPDATE `list_options` SET `notes` = '2081-8' WHERE `title` = 'Tahitian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id taiwanese title Taiwanese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','taiwanese','Taiwanese','7880', '0',' 2035-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id taiwanese
UPDATE `list_options` SET `notes` = '2035-4' WHERE `option_id` = 'taiwanese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Taiwanese
UPDATE `list_options` SET `notes` = '2035-4' WHERE `title` = 'Taiwanese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id takelma title Takelma
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','takelma','Takelma','7890', '0',' 1063-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id takelma
UPDATE `list_options` SET `notes` = '1063-7' WHERE `option_id` = 'takelma' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Takelma
UPDATE `list_options` SET `notes` = '1063-7' WHERE `title` = 'Takelma' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id takotna title Takotna
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','takotna','Takotna','7900', '0',' 1798-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id takotna
UPDATE `list_options` SET `notes` = '1798-8' WHERE `option_id` = 'takotna' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Takotna
UPDATE `list_options` SET `notes` = '1798-8' WHERE `title` = 'Takotna' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id talakamish title Talakamish
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','talakamish','Talakamish','7910', '0',' 1397-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id talakamish
UPDATE `list_options` SET `notes` = '1397-9' WHERE `option_id` = 'talakamish' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Talakamish
UPDATE `list_options` SET `notes` = '1397-9' WHERE `title` = 'Talakamish' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tanacross title Tanacross
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tanacross','Tanacross','7920', '0',' 1799-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id tanacross
UPDATE `list_options` SET `notes` = '1799-6' WHERE `option_id` = 'tanacross' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tanacross
UPDATE `list_options` SET `notes` = '1799-6' WHERE `title` = 'Tanacross' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tanaina title Tanaina
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tanaina','Tanaina','7930', '0',' 1800-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id tanaina
UPDATE `list_options` SET `notes` = '1800-2' WHERE `option_id` = 'tanaina' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tanaina
UPDATE `list_options` SET `notes` = '1800-2' WHERE `title` = 'Tanaina' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tanana title Tanana
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tanana','Tanana','7940', '0',' 1801-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id tanana
UPDATE `list_options` SET `notes` = '1801-0' WHERE `option_id` = 'tanana' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tanana
UPDATE `list_options` SET `notes` = '1801-0' WHERE `title` = 'Tanana' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tanana_chiefs title Tanana Chiefs
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tanana_chiefs','Tanana Chiefs','7950', '0',' 1802-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id tanana_chiefs
UPDATE `list_options` SET `notes` = '1802-8' WHERE `option_id` = 'tanana_chiefs' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tanana Chiefs
UPDATE `list_options` SET `notes` = '1802-8' WHERE `title` = 'Tanana Chiefs' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id taos title Taos
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','taos','Taos','7960', '0',' 1511-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id taos
UPDATE `list_options` SET `notes` = '1511-5' WHERE `option_id` = 'taos' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Taos
UPDATE `list_options` SET `notes` = '1511-5' WHERE `title` = 'Taos' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tatitlek title Tatitlek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tatitlek','Tatitlek','7970', '0',' 1969-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id tatitlek
UPDATE `list_options` SET `notes` = '1969-5' WHERE `option_id` = 'tatitlek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tatitlek
UPDATE `list_options` SET `notes` = '1969-5' WHERE `title` = 'Tatitlek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tazlina title Tazlina
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tazlina','Tazlina','7980', '0',' 1803-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id tazlina
UPDATE `list_options` SET `notes` = '1803-6' WHERE `option_id` = 'tazlina' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tazlina
UPDATE `list_options` SET `notes` = '1803-6' WHERE `title` = 'Tazlina' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id telida title Telida
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','telida','Telida','7990', '0',' 1804-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id telida
UPDATE `list_options` SET `notes` = '1804-4' WHERE `option_id` = 'telida' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Telida
UPDATE `list_options` SET `notes` = '1804-4' WHERE `title` = 'Telida' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id teller title Teller
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','teller','Teller','8000', '0',' 1883-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id teller
UPDATE `list_options` SET `notes` = '1883-8' WHERE `option_id` = 'teller' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Teller
UPDATE `list_options` SET `notes` = '1883-8' WHERE `title` = 'Teller' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id temecula title Temecula
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','temecula','Temecula','8010', '0',' 1338-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id temecula
UPDATE `list_options` SET `notes` = '1338-3' WHERE `option_id` = 'temecula' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Temecula
UPDATE `list_options` SET `notes` = '1338-3' WHERE `title` = 'Temecula' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id te-moak_western_shoshone title Te-Moak Western Shoshone
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','te-moak_western_shoshone','Te-Moak Western Shoshone','8020', '0',' 1596-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id te-moak_western_shoshone
UPDATE `list_options` SET `notes` = '1596-6' WHERE `option_id` = 'te-moak_western_shoshone' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Te-Moak Western Shoshone
UPDATE `list_options` SET `notes` = '1596-6' WHERE `title` = 'Te-Moak Western Shoshone' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tenakee_springs title Tenakee Springs
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tenakee_springs','Tenakee Springs','8030', '0',' 1832-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id tenakee_springs
UPDATE `list_options` SET `notes` = '1832-5' WHERE `option_id` = 'tenakee_springs' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tenakee Springs
UPDATE `list_options` SET `notes` = '1832-5' WHERE `title` = 'Tenakee Springs' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tenino title Tenino
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tenino','Tenino','8040', '0',' 1398-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id tenino
UPDATE `list_options` SET `notes` = '1398-7' WHERE `option_id` = 'tenino' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tenino
UPDATE `list_options` SET `notes` = '1398-7' WHERE `title` = 'Tenino' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tesuque title Tesuque
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tesuque','Tesuque','8050', '0',' 1512-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id tesuque
UPDATE `list_options` SET `notes` = '1512-3' WHERE `option_id` = 'tesuque' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tesuque
UPDATE `list_options` SET `notes` = '1512-3' WHERE `title` = 'Tesuque' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tetlin title Tetlin
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tetlin','Tetlin','8060', '0',' 1805-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id tetlin
UPDATE `list_options` SET `notes` = '1805-1' WHERE `option_id` = 'tetlin' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tetlin
UPDATE `list_options` SET `notes` = '1805-1' WHERE `title` = 'Tetlin' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id teton_sioux title Teton Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','teton_sioux','Teton Sioux','8070', '0',' 1634-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id teton_sioux
UPDATE `list_options` SET `notes` = '1634-5' WHERE `option_id` = 'teton_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Teton Sioux
UPDATE `list_options` SET `notes` = '1634-5' WHERE `title` = 'Teton Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tewa title Tewa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tewa','Tewa','8080', '0',' 1513-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id tewa
UPDATE `list_options` SET `notes` = '1513-1' WHERE `option_id` = 'tewa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tewa
UPDATE `list_options` SET `notes` = '1513-1' WHERE `title` = 'Tewa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id texas_kickapoo title Texas Kickapoo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','texas_kickapoo','Texas Kickapoo','8090', '0',' 1307-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id texas_kickapoo
UPDATE `list_options` SET `notes` = '1307-8' WHERE `option_id` = 'texas_kickapoo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Texas Kickapoo
UPDATE `list_options` SET `notes` = '1307-8' WHERE `title` = 'Texas Kickapoo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id thai title Thai
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','thai','Thai','8100', '0',' 2046-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id thai
UPDATE `list_options` SET `notes` = '2046-1' WHERE `option_id` = 'thai' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Thai
UPDATE `list_options` SET `notes` = '2046-1' WHERE `title` = 'Thai' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id thlopthlocco title Thlopthlocco
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','thlopthlocco','Thlopthlocco','8110', '0',' 1204-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id thlopthlocco
UPDATE `list_options` SET `notes` = '1204-7' WHERE `option_id` = 'thlopthlocco' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Thlopthlocco
UPDATE `list_options` SET `notes` = '1204-7' WHERE `title` = 'Thlopthlocco' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tigua title Tigua
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tigua','Tigua','8120', '0',' 1514-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id tigua
UPDATE `list_options` SET `notes` = '1514-9' WHERE `option_id` = 'tigua' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tigua
UPDATE `list_options` SET `notes` = '1514-9' WHERE `title` = 'Tigua' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tillamook title Tillamook
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tillamook','Tillamook','8130', '0',' 1399-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id tillamook
UPDATE `list_options` SET `notes` = '1399-5' WHERE `option_id` = 'tillamook' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tillamook
UPDATE `list_options` SET `notes` = '1399-5' WHERE `title` = 'Tillamook' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id timbi-sha_shoshone title Timbi-Sha Shoshone
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','timbi-sha_shoshone','Timbi-Sha Shoshone','8140', '0',' 1597-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id timbi-sha_shoshone
UPDATE `list_options` SET `notes` = '1597-4' WHERE `option_id` = 'timbi-sha_shoshone' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Timbi-Sha Shoshone
UPDATE `list_options` SET `notes` = '1597-4' WHERE `title` = 'Timbi-Sha Shoshone' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tlingit title Tlingit
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tlingit','Tlingit','8150', '0',' 1833-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id tlingit
UPDATE `list_options` SET `notes` = '1833-3' WHERE `option_id` = 'tlingit' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tlingit
UPDATE `list_options` SET `notes` = '1833-3' WHERE `title` = 'Tlingit' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tlingit-haida title Tlingit-Haida
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tlingit-haida','Tlingit-Haida','8160', '0',' 1813-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id tlingit-haida
UPDATE `list_options` SET `notes` = '1813-5' WHERE `option_id` = 'tlingit-haida' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tlingit-Haida
UPDATE `list_options` SET `notes` = '1813-5' WHERE `title` = 'Tlingit-Haida' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tobagoan title Tobagoan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tobagoan','Tobagoan','8170', '0',' 2073-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id tobagoan
UPDATE `list_options` SET `notes` = '2073-5' WHERE `option_id` = 'tobagoan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tobagoan
UPDATE `list_options` SET `notes` = '2073-5' WHERE `title` = 'Tobagoan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id togiak title Togiak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','togiak','Togiak','8180', '0',' 1956-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id togiak
UPDATE `list_options` SET `notes` = '1956-2' WHERE `option_id` = 'togiak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Togiak
UPDATE `list_options` SET `notes` = '1956-2' WHERE `title` = 'Togiak' AND `list_id` = 'race';
#EndIf

#IfNotRow2D list_options list_id race option_id tohono_oodham
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tohono_oodham',"Tohono O'Odham",'8190', '0',' 1653-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id tohono_oodham
UPDATE `list_options` SET `notes` = '1653-5' WHERE `option_id` = 'tohono_oodham' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tok title Tok
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tok','Tok','8200', '0',' 1806-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id tok
UPDATE `list_options` SET `notes` = '1806-9' WHERE `option_id` = 'tok' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tok
UPDATE `list_options` SET `notes` = '1806-9' WHERE `title` = 'Tok' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tokelauan title Tokelauan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tokelauan','Tokelauan','8210', '0',' 2083-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id tokelauan
UPDATE `list_options` SET `notes` = '2083-4' WHERE `option_id` = 'tokelauan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tokelauan
UPDATE `list_options` SET `notes` = '2083-4' WHERE `title` = 'Tokelauan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id toksook title Toksook
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','toksook','Toksook','8220', '0',' 1957-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id toksook
UPDATE `list_options` SET `notes` = '1957-0' WHERE `option_id` = 'toksook' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Toksook
UPDATE `list_options` SET `notes` = '1957-0' WHERE `title` = 'Toksook' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tolowa title Tolowa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tolowa','Tolowa','8230', '0',' 1659-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id tolowa
UPDATE `list_options` SET `notes` = '1659-2' WHERE `option_id` = 'tolowa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tolowa
UPDATE `list_options` SET `notes` = '1659-2' WHERE `title` = 'Tolowa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tonawanda_seneca title Tonawanda Seneca
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tonawanda_seneca','Tonawanda Seneca','8240', '0',' 1293-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id tonawanda_seneca
UPDATE `list_options` SET `notes` = '1293-0' WHERE `option_id` = 'tonawanda_seneca' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tonawanda Seneca
UPDATE `list_options` SET `notes` = '1293-0' WHERE `title` = 'Tonawanda Seneca' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tongan title Tongan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tongan','Tongan','8250', '0',' 2082-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id tongan
UPDATE `list_options` SET `notes` = '2082-6' WHERE `option_id` = 'tongan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tongan
UPDATE `list_options` SET `notes` = '2082-6' WHERE `title` = 'Tongan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tonkawa title Tonkawa
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tonkawa','Tonkawa','8260', '0',' 1661-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id tonkawa
UPDATE `list_options` SET `notes` = '1661-8' WHERE `option_id` = 'tonkawa' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tonkawa
UPDATE `list_options` SET `notes` = '1661-8' WHERE `title` = 'Tonkawa' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id torres-martinez title Torres-Martinez
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','torres-martinez','Torres-Martinez','8270', '0',' 1051-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id torres-martinez
UPDATE `list_options` SET `notes` = '1051-2' WHERE `option_id` = 'torres-martinez' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Torres-Martinez
UPDATE `list_options` SET `notes` = '1051-2' WHERE `title` = 'Torres-Martinez' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id trinidadian title Trinidadian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','trinidadian','Trinidadian','8280', '0',' 2074-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id trinidadian
UPDATE `list_options` SET `notes` = '2074-3' WHERE `option_id` = 'trinidadian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Trinidadian
UPDATE `list_options` SET `notes` = '2074-3' WHERE `title` = 'Trinidadian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id trinity title Trinity
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','trinity','Trinity','8290', '0',' 1272-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id trinity
UPDATE `list_options` SET `notes` = '1272-4' WHERE `option_id` = 'trinity' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Trinity
UPDATE `list_options` SET `notes` = '1272-4' WHERE `title` = 'Trinity' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tsimshian title Tsimshian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tsimshian','Tsimshian','8300', '0',' 1837-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id tsimshian
UPDATE `list_options` SET `notes` = '1837-4' WHERE `option_id` = 'tsimshian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tsimshian
UPDATE `list_options` SET `notes` = '1837-4' WHERE `title` = 'Tsimshian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tuckabachee title Tuckabachee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tuckabachee','Tuckabachee','8310', '0',' 1205-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id tuckabachee
UPDATE `list_options` SET `notes` = '1205-4' WHERE `option_id` = 'tuckabachee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tuckabachee
UPDATE `list_options` SET `notes` = '1205-4' WHERE `title` = 'Tuckabachee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tulalip title Tulalip
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tulalip','Tulalip','8320', '0',' 1538-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id tulalip
UPDATE `list_options` SET `notes` = '1538-8' WHERE `option_id` = 'tulalip' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tulalip
UPDATE `list_options` SET `notes` = '1538-8' WHERE `title` = 'Tulalip' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tule_river title Tule River
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tule_river','Tule River','8330', '0',' 1720-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id tule_river
UPDATE `list_options` SET `notes` = '1720-2' WHERE `option_id` = 'tule_river' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tule River
UPDATE `list_options` SET `notes` = '1720-2' WHERE `title` = 'Tule River' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tulukskak title Tulukskak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tulukskak','Tulukskak','8340', '0',' 1958-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id tulukskak
UPDATE `list_options` SET `notes` = '1958-8' WHERE `option_id` = 'tulukskak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tulukskak
UPDATE `list_options` SET `notes` = '1958-8' WHERE `title` = 'Tulukskak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tunica_biloxi title Tunica Biloxi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tunica_biloxi','Tunica Biloxi','8350', '0',' 1246-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id tunica_biloxi
UPDATE `list_options` SET `notes` = '1246-8' WHERE `option_id` = 'tunica_biloxi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tunica Biloxi
UPDATE `list_options` SET `notes` = '1246-8' WHERE `title` = 'Tunica Biloxi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tuntutuliak title Tuntutuliak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tuntutuliak','Tuntutuliak','8360', '0',' 1959-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id tuntutuliak
UPDATE `list_options` SET `notes` = '1959-6' WHERE `option_id` = 'tuntutuliak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tuntutuliak
UPDATE `list_options` SET `notes` = '1959-6' WHERE `title` = 'Tuntutuliak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tununak title Tununak
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tununak','Tununak','8370', '0',' 1960-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id tununak
UPDATE `list_options` SET `notes` = '1960-4' WHERE `option_id` = 'tununak' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tununak
UPDATE `list_options` SET `notes` = '1960-4' WHERE `title` = 'Tununak' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id turtle_mountain title Turtle Mountain
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','turtle_mountain','Turtle Mountain','8380', '0',' 1147-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id turtle_mountain
UPDATE `list_options` SET `notes` = '1147-8' WHERE `option_id` = 'turtle_mountain' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Turtle Mountain
UPDATE `list_options` SET `notes` = '1147-8' WHERE `title` = 'Turtle Mountain' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tuscarora title Tuscarora
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tuscarora','Tuscarora','8390', '0',' 1294-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id tuscarora
UPDATE `list_options` SET `notes` = '1294-8' WHERE `option_id` = 'tuscarora' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tuscarora
UPDATE `list_options` SET `notes` = '1294-8' WHERE `title` = 'Tuscarora' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tuscola title Tuscola
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tuscola','Tuscola','8400', '0',' 1096-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id tuscola
UPDATE `list_options` SET `notes` = '1096-7' WHERE `option_id` = 'tuscola' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tuscola
UPDATE `list_options` SET `notes` = '1096-7' WHERE `title` = 'Tuscola' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id twenty-nine_palms title Twenty-Nine Palms
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','twenty-nine_palms','Twenty-Nine Palms','8410', '0',' 1337-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id twenty-nine_palms
UPDATE `list_options` SET `notes` = '1337-5' WHERE `option_id` = 'twenty-nine_palms' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Twenty-Nine Palms
UPDATE `list_options` SET `notes` = '1337-5' WHERE `title` = 'Twenty-Nine Palms' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id twin_hills title Twin Hills
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','twin_hills','Twin Hills','8420', '0',' 1961-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id twin_hills
UPDATE `list_options` SET `notes` = '1961-2' WHERE `option_id` = 'twin_hills' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Twin Hills
UPDATE `list_options` SET `notes` = '1961-2' WHERE `title` = 'Twin Hills' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id two_kettle_sioux title Two Kettle Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','two_kettle_sioux','Two Kettle Sioux','8430', '0',' 1635-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id two_kettle_sioux
UPDATE `list_options` SET `notes` = '1635-2' WHERE `option_id` = 'two_kettle_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Two Kettle Sioux
UPDATE `list_options` SET `notes` = '1635-2' WHERE `title` = 'Two Kettle Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tygh title Tygh
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tygh','Tygh','8440', '0',' 1663-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id tygh
UPDATE `list_options` SET `notes` = '1663-4' WHERE `option_id` = 'tygh' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tygh
UPDATE `list_options` SET `notes` = '1663-4' WHERE `title` = 'Tygh' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id tyonek title Tyonek
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','tyonek','Tyonek','8450', '0',' 1807-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id tyonek
UPDATE `list_options` SET `notes` = '1807-7' WHERE `option_id` = 'tyonek' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Tyonek
UPDATE `list_options` SET `notes` = '1807-7' WHERE `title` = 'Tyonek' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ugashik title Ugashik
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ugashik','Ugashik','8460', '0',' 1970-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id ugashik
UPDATE `list_options` SET `notes` = '1970-3' WHERE `option_id` = 'ugashik' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ugashik
UPDATE `list_options` SET `notes` = '1970-3' WHERE `title` = 'Ugashik' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id uintah_ute title Uintah Ute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','uintah_ute','Uintah Ute','8470', '0',' 1672-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id uintah_ute
UPDATE `list_options` SET `notes` = '1672-5' WHERE `option_id` = 'uintah_ute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Uintah Ute
UPDATE `list_options` SET `notes` = '1672-5' WHERE `title` = 'Uintah Ute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id umatilla title Umatilla
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','umatilla','Umatilla','8480', '0',' 1665-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id umatilla
UPDATE `list_options` SET `notes` = '1665-9' WHERE `option_id` = 'umatilla' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Umatilla
UPDATE `list_options` SET `notes` = '1665-9' WHERE `title` = 'Umatilla' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id umkumiate title Umkumiate
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','umkumiate','Umkumiate','8490', '0',' 1964-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id umkumiate
UPDATE `list_options` SET `notes` = '1964-6' WHERE `option_id` = 'umkumiate' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Umkumiate
UPDATE `list_options` SET `notes` = '1964-6' WHERE `title` = 'Umkumiate' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id umpqua title Umpqua
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','umpqua','Umpqua','8500', '0',' 1667-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id umpqua
UPDATE `list_options` SET `notes` = '1667-5' WHERE `option_id` = 'umpqua' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Umpqua
UPDATE `list_options` SET `notes` = '1667-5' WHERE `title` = 'Umpqua' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id unalakleet title Unalakleet
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','unalakleet','Unalakleet','8510', '0',' 1884-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id unalakleet
UPDATE `list_options` SET `notes` = '1884-6' WHERE `option_id` = 'unalakleet' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Unalakleet
UPDATE `list_options` SET `notes` = '1884-6' WHERE `title` = 'Unalakleet' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id unalaska title Unalaska
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','unalaska','Unalaska','8520', '0',' 2025-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id unalaska
UPDATE `list_options` SET `notes` = '2025-5' WHERE `option_id` = 'unalaska' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Unalaska
UPDATE `list_options` SET `notes` = '2025-5' WHERE `title` = 'Unalaska' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id unangan_aleut title Unangan Aleut
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','unangan_aleut','Unangan Aleut','8530', '0',' 2006-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id unangan_aleut
UPDATE `list_options` SET `notes` = '2006-5' WHERE `option_id` = 'unangan_aleut' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Unangan Aleut
UPDATE `list_options` SET `notes` = '2006-5' WHERE `title` = 'Unangan Aleut' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id unga title Unga
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','unga','Unga','8540', '0',' 2026-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id unga
UPDATE `list_options` SET `notes` = '2026-3' WHERE `option_id` = 'unga' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Unga
UPDATE `list_options` SET `notes` = '2026-3' WHERE `title` = 'Unga' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id united_ketowah_band_of_cheroke title United Keetowah Band of Cherokee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','united_ketowah_band_of_cheroke','United Keetowah Band of Cherokee','8550', '0',' 1097-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id united_ketowah_band_of_cheroke
UPDATE `list_options` SET `notes` = '1097-5' WHERE `option_id` = 'united_ketowah_band_of_cheroke' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title United Keetowah Band of Cherokee
UPDATE `list_options` SET `notes` = '1097-5' WHERE `title` = 'United Keetowah Band of Cherokee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id upper_chinook title Upper Chinook
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','upper_chinook','Upper Chinook','8560', '0',' 1118-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id upper_chinook
UPDATE `list_options` SET `notes` = '1118-9' WHERE `option_id` = 'upper_chinook' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Upper Chinook
UPDATE `list_options` SET `notes` = '1118-9' WHERE `title` = 'Upper Chinook' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id upper_sioux title Upper Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','upper_sioux','Upper Sioux','8570', '0',' 1636-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id upper_sioux
UPDATE `list_options` SET `notes` = '1636-0' WHERE `option_id` = 'upper_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Upper Sioux
UPDATE `list_options` SET `notes` = '1636-0' WHERE `title` = 'Upper Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id upper_skagit title Upper Skagit
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','upper_skagit','Upper Skagit','8580', '0',' 1539-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id upper_skagit
UPDATE `list_options` SET `notes` = '1539-6' WHERE `option_id` = 'upper_skagit' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Upper Skagit
UPDATE `list_options` SET `notes` = '1539-6' WHERE `title` = 'Upper Skagit' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ute title Ute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ute','Ute','8590', '0',' 1670-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id ute
UPDATE `list_options` SET `notes` = '1670-9' WHERE `option_id` = 'ute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ute
UPDATE `list_options` SET `notes` = '1670-9' WHERE `title` = 'Ute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id ute_mountain_ute title Ute Mountain Ute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','ute_mountain_ute','Ute Mountain Ute','8600', '0',' 1673-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id ute_mountain_ute
UPDATE `list_options` SET `notes` = '1673-3' WHERE `option_id` = 'ute_mountain_ute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Ute Mountain Ute
UPDATE `list_options` SET `notes` = '1673-3' WHERE `title` = 'Ute Mountain Ute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id utu_utu_gwaitu_paiute title Utu Utu Gwaitu Paiute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','utu_utu_gwaitu_paiute','Utu Utu Gwaitu Paiute','8610', '0',' 1435-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id utu_utu_gwaitu_paiute
UPDATE `list_options` SET `notes` = '1435-7' WHERE `option_id` = 'utu_utu_gwaitu_paiute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Utu Utu Gwaitu Paiute
UPDATE `list_options` SET `notes` = '1435-7' WHERE `title` = 'Utu Utu Gwaitu Paiute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id venetie title Venetie
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','venetie','Venetie','8620', '0',' 1808-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id venetie
UPDATE `list_options` SET `notes` = '1808-5' WHERE `option_id` = 'venetie' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Venetie
UPDATE `list_options` SET `notes` = '1808-5' WHERE `title` = 'Venetie' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id vietnamese title Vietnamese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','vietnamese','Vietnamese','8630', '0',' 2047-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id vietnamese
UPDATE `list_options` SET `notes` = '2047-9' WHERE `option_id` = 'vietnamese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Vietnamese
UPDATE `list_options` SET `notes` = '2047-9' WHERE `title` = 'Vietnamese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id waccamaw-siousan title Waccamaw-Siousan
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','waccamaw-siousan','Waccamaw-Siousan','8640', '0',' 1247-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id waccamaw-siousan
UPDATE `list_options` SET `notes` = '1247-6' WHERE `option_id` = 'waccamaw-siousan' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Waccamaw-Siousan
UPDATE `list_options` SET `notes` = '1247-6' WHERE `title` = 'Waccamaw-Siousan' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wahpekute_sioux title Wahpekute Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wahpekute_sioux','Wahpekute Sioux','8650', '0',' 1637-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id wahpekute_sioux
UPDATE `list_options` SET `notes` = '1637-8' WHERE `option_id` = 'wahpekute_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wahpekute Sioux
UPDATE `list_options` SET `notes` = '1637-8' WHERE `title` = 'Wahpekute Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wahpeton_sioux title Wahpeton Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wahpeton_sioux','Wahpeton Sioux','8660', '0',' 1638-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id wahpeton_sioux
UPDATE `list_options` SET `notes` = '1638-6' WHERE `option_id` = 'wahpeton_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wahpeton Sioux
UPDATE `list_options` SET `notes` = '1638-6' WHERE `title` = 'Wahpeton Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wailaki title Wailaki
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wailaki','Wailaki','8670', '0',' 1675-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id wailaki
UPDATE `list_options` SET `notes` = '1675-8' WHERE `option_id` = 'wailaki' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wailaki
UPDATE `list_options` SET `notes` = '1675-8' WHERE `title` = 'Wailaki' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wainwright title Wainwright
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wainwright','Wainwright','8680', '0',' 1885-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id wainwright
UPDATE `list_options` SET `notes` = '1885-3' WHERE `option_id` = 'wainwright' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wainwright
UPDATE `list_options` SET `notes` = '1885-3' WHERE `title` = 'Wainwright' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wakiakum_chinook title Wakiakum Chinook
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wakiakum_chinook','Wakiakum Chinook','8690', '0',' 1119-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id wakiakum_chinook
UPDATE `list_options` SET `notes` = '1119-7' WHERE `option_id` = 'wakiakum_chinook' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wakiakum Chinook
UPDATE `list_options` SET `notes` = '1119-7' WHERE `title` = 'Wakiakum Chinook' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wales title Wales
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wales','Wales','8700', '0',' 1886-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id wales
UPDATE `list_options` SET `notes` = '1886-1' WHERE `option_id` = 'wales' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wales
UPDATE `list_options` SET `notes` = '1886-1' WHERE `title` = 'Wales' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id walker_river title Walker River
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','walker_river','Walker River','8710', '0',' 1436-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id walker_river
UPDATE `list_options` SET `notes` = '1436-5' WHERE `option_id` = 'walker_river' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Walker River
UPDATE `list_options` SET `notes` = '1436-5' WHERE `title` = 'Walker River' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id walla-walla title Walla-Walla
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','walla-walla','Walla-Walla','8720', '0',' 1677-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id walla-walla
UPDATE `list_options` SET `notes` = '1677-4' WHERE `option_id` = 'walla-walla' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Walla-Walla
UPDATE `list_options` SET `notes` = '1677-4' WHERE `title` = 'Walla-Walla' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wampanoag title Wampanoag
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wampanoag','Wampanoag','8730', '0',' 1679-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id wampanoag
UPDATE `list_options` SET `notes` = '1679-0' WHERE `option_id` = 'wampanoag' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wampanoag
UPDATE `list_options` SET `notes` = '1679-0' WHERE `title` = 'Wampanoag' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wappo title Wappo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wappo','Wappo','8740', '0',' 1064-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id wappo
UPDATE `list_options` SET `notes` = '1064-5' WHERE `option_id` = 'wappo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wappo
UPDATE `list_options` SET `notes` = '1064-5' WHERE `title` = 'Wappo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id warm_springs title Warm Springs
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','warm_springs','Warm Springs','8750', '0',' 1683-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id warm_springs
UPDATE `list_options` SET `notes` = '1683-2' WHERE `option_id` = 'warm_springs' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Warm Springs
UPDATE `list_options` SET `notes` = '1683-2' WHERE `title` = 'Warm Springs' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wascopum title Wascopum
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wascopum','Wascopum','8760', '0',' 1685-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id wascopum
UPDATE `list_options` SET `notes` = '1685-7' WHERE `option_id` = 'wascopum' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wascopum
UPDATE `list_options` SET `notes` = '1685-7' WHERE `title` = 'Wascopum' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id washakie title Washakie
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','washakie','Washakie','8770', '0',' 1598-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id washakie
UPDATE `list_options` SET `notes` = '1598-2' WHERE `option_id` = 'washakie' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Washakie
UPDATE `list_options` SET `notes` = '1598-2' WHERE `title` = 'Washakie' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id washoe title Washoe
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','washoe','Washoe','8780', '0',' 1687-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id washoe
UPDATE `list_options` SET `notes` = '1687-3' WHERE `option_id` = 'washoe' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Washoe
UPDATE `list_options` SET `notes` = '1687-3' WHERE `title` = 'Washoe' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wazhaza_sioux title Wazhaza Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wazhaza_sioux','Wazhaza Sioux','8790', '0',' 1639-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id wazhaza_sioux
UPDATE `list_options` SET `notes` = '1639-4' WHERE `option_id` = 'wazhaza_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wazhaza Sioux
UPDATE `list_options` SET `notes` = '1639-4' WHERE `title` = 'Wazhaza Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wenatchee title Wenatchee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wenatchee','Wenatchee','8800', '0',' 1400-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id wenatchee
UPDATE `list_options` SET `notes` = '1400-1' WHERE `option_id` = 'wenatchee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wenatchee
UPDATE `list_options` SET `notes` = '1400-1' WHERE `title` = 'Wenatchee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id west_indian title West Indian
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','west_indian','West Indian','8810', '0',' 2075-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id west_indian
UPDATE `list_options` SET `notes` = '2075-0' WHERE `option_id` = 'west_indian' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title West Indian
UPDATE `list_options` SET `notes` = '2075-0' WHERE `title` = 'West Indian' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id western_cherokee title Western Cherokee
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','western_cherokee','Western Cherokee','8820', '0',' 1098-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id western_cherokee
UPDATE `list_options` SET `notes` = '1098-3' WHERE `option_id` = 'western_cherokee' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Western Cherokee
UPDATE `list_options` SET `notes` = '1098-3' WHERE `title` = 'Western Cherokee' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id western_chickahominy title Western Chickahominy
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','western_chickahominy','Western Chickahominy','8830', '0',' 1110-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id western_chickahominy
UPDATE `list_options` SET `notes` = '1110-6' WHERE `option_id` = 'western_chickahominy' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Western Chickahominy
UPDATE `list_options` SET `notes` = '1110-6' WHERE `title` = 'Western Chickahominy' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id whilkut title Whilkut
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','whilkut','Whilkut','8840', '0',' 1273-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id whilkut
UPDATE `list_options` SET `notes` = '1273-2' WHERE `option_id` = 'whilkut' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Whilkut
UPDATE `list_options` SET `notes` = '1273-2' WHERE `title` = 'Whilkut' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id white_earth title White Earth
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','white_earth','White Earth','8860', '0',' 1148-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id white_earth
UPDATE `list_options` SET `notes` = '1148-6' WHERE `option_id` = 'white_earth' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title White Earth
UPDATE `list_options` SET `notes` = '1148-6' WHERE `title` = 'White Earth' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id white_mountain title White Mountain
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','white_mountain','White Mountain','8870', '0',' 1887-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id white_mountain
UPDATE `list_options` SET `notes` = '1887-9' WHERE `option_id` = 'white_mountain' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title White Mountain
UPDATE `list_options` SET `notes` = '1887-9' WHERE `title` = 'White Mountain' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id white_mountain_apache title White Mountain Apache
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','white_mountain_apache','White Mountain Apache','8880', '0',' 1019-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id white_mountain_apache
UPDATE `list_options` SET `notes` = '1019-9' WHERE `option_id` = 'white_mountain_apache' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title White Mountain Apache
UPDATE `list_options` SET `notes` = '1019-9' WHERE `title` = 'White Mountain Apache' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id white_mountain_inupiat title White Mountain Inupiat
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','white_mountain_inupiat','White Mountain Inupiat','8890', '0',' 1888-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id white_mountain_inupiat
UPDATE `list_options` SET `notes` = '1888-7' WHERE `option_id` = 'white_mountain_inupiat' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title White Mountain Inupiat
UPDATE `list_options` SET `notes` = '1888-7' WHERE `title` = 'White Mountain Inupiat' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wichita title Wichita
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wichita','Wichita','8900', '0',' 1692-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id wichita
UPDATE `list_options` SET `notes` = '1692-3' WHERE `option_id` = 'wichita' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wichita
UPDATE `list_options` SET `notes` = '1692-3' WHERE `title` = 'Wichita' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wicomico title Wicomico
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wicomico','Wicomico','8910', '0',' 1248-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id wicomico
UPDATE `list_options` SET `notes` = '1248-4' WHERE `option_id` = 'wicomico' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wicomico
UPDATE `list_options` SET `notes` = '1248-4' WHERE `title` = 'Wicomico' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id willapa_chinook title Willapa Chinook
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','willapa_chinook','Willapa Chinook','8920', '0',' 1120-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id willapa_chinook
UPDATE `list_options` SET `notes` = '1120-5' WHERE `option_id` = 'willapa_chinook' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Willapa Chinook
UPDATE `list_options` SET `notes` = '1120-5' WHERE `title` = 'Willapa Chinook' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wind_river title Wind River
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wind_river','Wind River','8930', '0',' 1694-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id wind_river
UPDATE `list_options` SET `notes` = '1694-9' WHERE `option_id` = 'wind_river' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wind River
UPDATE `list_options` SET `notes` = '1694-9' WHERE `title` = 'Wind River' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wind_river_arapaho title Wind River Arapaho
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wind_river_arapaho','Wind River Arapaho','8940', '0',' 1024-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id wind_river_arapaho
UPDATE `list_options` SET `notes` = '1024-9' WHERE `option_id` = 'wind_river_arapaho' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wind River Arapaho
UPDATE `list_options` SET `notes` = '1024-9' WHERE `title` = 'Wind River Arapaho' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wind_river_shoshone title Wind River Shoshone
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wind_river_shoshone','Wind River Shoshone','8950', '0',' 1599-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id wind_river_shoshone
UPDATE `list_options` SET `notes` = '1599-0' WHERE `option_id` = 'wind_river_shoshone' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wind River Shoshone
UPDATE `list_options` SET `notes` = '1599-0' WHERE `title` = 'Wind River Shoshone' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id winnebago title Winnebago
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','winnebago','Winnebago','8960', '0',' 1696-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id winnebago
UPDATE `list_options` SET `notes` = '1696-4' WHERE `option_id` = 'winnebago' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Winnebago
UPDATE `list_options` SET `notes` = '1696-4' WHERE `title` = 'Winnebago' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id winnemucca title Winnemucca
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','winnemucca','Winnemucca','8970', '0',' 1700-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id winnemucca
UPDATE `list_options` SET `notes` = '1700-4' WHERE `option_id` = 'winnemucca' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Winnemucca
UPDATE `list_options` SET `notes` = '1700-4' WHERE `title` = 'Winnemucca' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wintun title Wintun
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wintun','Wintun','8980', '0',' 1702-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id wintun
UPDATE `list_options` SET `notes` = '1702-0' WHERE `option_id` = 'wintun' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wintun
UPDATE `list_options` SET `notes` = '1702-0' WHERE `title` = 'Wintun' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wisconsin_potawatomi title Wisconsin Potawatomi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wisconsin_potawatomi','Wisconsin Potawatomi','8990', '0',' 1485-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id wisconsin_potawatomi
UPDATE `list_options` SET `notes` = '1485-2' WHERE `option_id` = 'wisconsin_potawatomi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wisconsin Potawatomi
UPDATE `list_options` SET `notes` = '1485-2' WHERE `title` = 'Wisconsin Potawatomi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wiseman title Wiseman
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wiseman','Wiseman','9000', '0',' 1809-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id wiseman
UPDATE `list_options` SET `notes` = '1809-3' WHERE `option_id` = 'wiseman' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wiseman
UPDATE `list_options` SET `notes` = '1809-3' WHERE `title` = 'Wiseman' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wishram title Wishram
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wishram','Wishram','9010', '0',' 1121-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id wishram
UPDATE `list_options` SET `notes` = '1121-3' WHERE `option_id` = 'wishram' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wishram
UPDATE `list_options` SET `notes` = '1121-3' WHERE `title` = 'Wishram' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wiyot title Wiyot
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wiyot','Wiyot','9020', '0',' 1704-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id wiyot
UPDATE `list_options` SET `notes` = '1704-6' WHERE `option_id` = 'wiyot' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wiyot
UPDATE `list_options` SET `notes` = '1704-6' WHERE `title` = 'Wiyot' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wrangell title Wrangell
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wrangell','Wrangell','9030', '0',' 1834-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id wrangell
UPDATE `list_options` SET `notes` = '1834-1' WHERE `option_id` = 'wrangell' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wrangell
UPDATE `list_options` SET `notes` = '1834-1' WHERE `title` = 'Wrangell' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id wyandotte title Wyandotte
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','wyandotte','Wyandotte','9040', '0',' 1295-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id wyandotte
UPDATE `list_options` SET `notes` = '1295-5' WHERE `option_id` = 'wyandotte' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Wyandotte
UPDATE `list_options` SET `notes` = '1295-5' WHERE `title` = 'Wyandotte' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yahooskin title Yahooskin
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yahooskin','Yahooskin','9050', '0',' 1401-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id yahooskin
UPDATE `list_options` SET `notes` = '1401-9' WHERE `option_id` = 'yahooskin' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yahooskin
UPDATE `list_options` SET `notes` = '1401-9' WHERE `title` = 'Yahooskin' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yakama title Yakama
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yakama','Yakama','9060', '0',' 1707-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id yakama
UPDATE `list_options` SET `notes` = '1707-9' WHERE `option_id` = 'yakama' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yakama
UPDATE `list_options` SET `notes` = '1707-9' WHERE `title` = 'Yakama' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yakama_cowlitz title Yakama Cowlitz
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yakama_cowlitz','Yakama Cowlitz','9070', '0',' 1709-5', 0);
#EndIf

#IfRow2D list_options list_id race option_id yakama_cowlitz
UPDATE `list_options` SET `notes` = '1709-5' WHERE `option_id` = 'yakama_cowlitz' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yakama Cowlitz
UPDATE `list_options` SET `notes` = '1709-5' WHERE `title` = 'Yakama Cowlitz' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yakutat title Yakutat
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yakutat','Yakutat','9080', '0',' 1835-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id yakutat
UPDATE `list_options` SET `notes` = '1835-8' WHERE `option_id` = 'yakutat' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yakutat
UPDATE `list_options` SET `notes` = '1835-8' WHERE `title` = 'Yakutat' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yana title Yana
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yana','Yana','9090', '0',' 1065-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id yana
UPDATE `list_options` SET `notes` = '1065-2' WHERE `option_id` = 'yana' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yana
UPDATE `list_options` SET `notes` = '1065-2' WHERE `title` = 'Yana' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yankton_sioux title Yankton Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yankton_sioux','Yankton Sioux','9100', '0',' 1640-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id yankton_sioux
UPDATE `list_options` SET `notes` = '1640-2' WHERE `option_id` = 'yankton_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yankton Sioux
UPDATE `list_options` SET `notes` = '1640-2' WHERE `title` = 'Yankton Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yanktonai_sioux title Yanktonai Sioux
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yanktonai_sioux','Yanktonai Sioux','9110', '0',' 1641-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id yanktonai_sioux
UPDATE `list_options` SET `notes` = '1641-0' WHERE `option_id` = 'yanktonai_sioux' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yanktonai Sioux
UPDATE `list_options` SET `notes` = '1641-0' WHERE `title` = 'Yanktonai Sioux' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yapese title Yapese
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yapese','Yapese','9120', '0',' 2098-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id yapese
UPDATE `list_options` SET `notes` = '2098-2' WHERE `option_id` = 'yapese' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yapese
UPDATE `list_options` SET `notes` = '2098-2' WHERE `title` = 'Yapese' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yaqui title Yaqui
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yaqui','Yaqui','9130', '0',' 1711-1', 0);
#EndIf

#IfRow2D list_options list_id race option_id yaqui
UPDATE `list_options` SET `notes` = '1711-1' WHERE `option_id` = 'yaqui' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yaqui
UPDATE `list_options` SET `notes` = '1711-1' WHERE `title` = 'Yaqui' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yavapai title Yavapai
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yavapai','Yavapai','9140', '0',' 1731-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id yavapai
UPDATE `list_options` SET `notes` = '1731-9' WHERE `option_id` = 'yavapai' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yavapai
UPDATE `list_options` SET `notes` = '1731-9' WHERE `title` = 'Yavapai' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yavapai_apache title Yavapai Apache
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yavapai_apache','Yavapai Apache','9150', '0',' 1715-2', 0);
#EndIf

#IfRow2D list_options list_id race option_id yavapai_apache
UPDATE `list_options` SET `notes` = '1715-2' WHERE `option_id` = 'yavapai_apache' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yavapai Apache
UPDATE `list_options` SET `notes` = '1715-2' WHERE `title` = 'Yavapai Apache' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yerington_paiute title Yerington Paiute
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yerington_paiute','Yerington Paiute','9160', '0',' 1437-3', 0);
#EndIf

#IfRow2D list_options list_id race option_id yerington_paiute
UPDATE `list_options` SET `notes` = '1437-3' WHERE `option_id` = 'yerington_paiute' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yerington Paiute
UPDATE `list_options` SET `notes` = '1437-3' WHERE `title` = 'Yerington Paiute' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yokuts title Yokuts
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yokuts','Yokuts','9170', '0',' 1717-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id yokuts
UPDATE `list_options` SET `notes` = '1717-8' WHERE `option_id` = 'yokuts' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yokuts
UPDATE `list_options` SET `notes` = '1717-8' WHERE `title` = 'Yokuts' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yomba title Yomba
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yomba','Yomba','9180', '0',' 1600-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id yomba
UPDATE `list_options` SET `notes` = '1600-6' WHERE `option_id` = 'yomba' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yomba
UPDATE `list_options` SET `notes` = '1600-6' WHERE `title` = 'Yomba' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yuchi title Yuchi
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yuchi','Yuchi','9190', '0',' 1722-8', 0);
#EndIf

#IfRow2D list_options list_id race option_id yuchi
UPDATE `list_options` SET `notes` = '1722-8' WHERE `option_id` = 'yuchi' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yuchi
UPDATE `list_options` SET `notes` = '1722-8' WHERE `title` = 'Yuchi' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yuki title Yuki
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yuki','Yuki','9200', '0',' 1066-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id yuki
UPDATE `list_options` SET `notes` = '1066-0' WHERE `option_id` = 'yuki' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yuki
UPDATE `list_options` SET `notes` = '1066-0' WHERE `title` = 'Yuki' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yuman title Yuman
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yuman','Yuman','9210', '0',' 1724-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id yuman
UPDATE `list_options` SET `notes` = '1724-4' WHERE `option_id` = 'yuman' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yuman
UPDATE `list_options` SET `notes` = '1724-4' WHERE `title` = 'Yuman' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yupik_eskimo title Yupik Eskimo
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yupik_eskimo','Yupik Eskimo','9220', '0',' 1896-0', 0);
#EndIf

#IfRow2D list_options list_id race option_id yupik_eskimo
UPDATE `list_options` SET `notes` = '1896-0' WHERE `option_id` = 'yupik_eskimo' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yupik Eskimo
UPDATE `list_options` SET `notes` = '1896-0' WHERE `title` = 'Yupik Eskimo' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id yurok title Yurok
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','yurok','Yurok','9230', '0',' 1732-7', 0);
#EndIf

#IfRow2D list_options list_id race option_id yurok
UPDATE `list_options` SET `notes` = '1732-7' WHERE `option_id` = 'yurok' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Yurok
UPDATE `list_options` SET `notes` = '1732-7' WHERE `title` = 'Yurok' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id zairean title Zairean
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','zairean','Zairean','9240', '0',' 2066-9', 0);
#EndIf

#IfRow2D list_options list_id race option_id zairean
UPDATE `list_options` SET `notes` = '2066-9' WHERE `option_id` = 'zairean' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Zairean
UPDATE `list_options` SET `notes` = '2066-9' WHERE `title` = 'Zairean' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id zia title Zia
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','zia','Zia','9250', '0',' 1515-6', 0);
#EndIf

#IfRow2D list_options list_id race option_id zia
UPDATE `list_options` SET `notes` = '1515-6' WHERE `option_id` = 'zia' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Zia
UPDATE `list_options` SET `notes` = '1515-6' WHERE `title` = 'Zia' AND `list_id` = 'race';
#EndIf

#IfNotRow2Dx2 list_options list_id race option_id zuni title Zuni
INSERT INTO list_options (list_id, option_id,  title, seq, is_default, notes, activity) VALUES ('race','zuni','Zuni','9260', '0',' 1516-4', 0);
#EndIf

#IfRow2D list_options list_id race option_id zuni
UPDATE `list_options` SET `notes` = '1516-4' WHERE `option_id` = 'zuni' AND `list_id` = 'race';
#EndIf

#IfRow2D list_options list_id race title Zuni
UPDATE `list_options` SET `notes` = '1516-4' WHERE `title` = 'Zuni' AND `list_id` = 'race';
#EndIf

#IfMissingColumn lists severity_al
ALTER TABLE lists ADD COLUMN severity_al VARCHAR(50) DEFAULT NULL;
#EndIf

#IfNotRow2D list_options list_id lists option_id severity_ccda
INSERT INTO list_options (list_id, option_id, title) VALUES ('lists','severity_ccda','Severity');
INSERT INTO list_options (list_id, option_id, title, codes, seq) values ('severity_ccda','unassigned','Unassigned','','10');
INSERT INTO list_options (list_id, option_id, title, codes, seq) values ('severity_ccda','mild','Mild','SNOMED-CT:255604002','20');
INSERT INTO list_options (list_id, option_id, title, codes, seq) values ('severity_ccda','mild_to_moderate','Mild to moderate','SNOMED-CT:371923003','30');
INSERT INTO list_options (list_id, option_id, title, codes, seq) values ('severity_ccda','moderate','Moderate','SNOMED-CT:6736007','40');
INSERT INTO list_options (list_id, option_id, title, codes, seq) values ('severity_ccda','moderate_to_severe','Moderate to severe','SNOMED-CT:371924009','50');
INSERT INTO list_options (list_id, option_id, title, codes, seq) values ('severity_ccda','severe','Severe','SNOMED-CT:24484000','60');
INSERT INTO list_options (list_id, option_id, title, codes, seq) values ('severity_ccda','life_threatening_severity','Life threatening severity','SNOMED-CT:442452003','70');
INSERT INTO list_options (list_id, option_id, title, codes, seq) values ('severity_ccda','fatal','Fatal','SNOMED-CT:399166001','80');
#EndIf

#IfNotRow3D list_options list_id drug_route notes PO title Per Oris
UPDATE list_options SET list_options.notes = 'PO' WHERE list_options.list_id = 'drug_route' AND title = 'Per Oris';
#EndIf

#IfNotRow3D list_options list_id drug_route notes OTH title Per Rectum
UPDATE list_options SET list_options.notes = 'OTH' WHERE list_options.list_id = 'drug_route' AND title = 'Per Rectum';
#EndIf

#IfNotRow3D list_options list_id drug_route notes OTH title To Skin
UPDATE list_options SET list_options.notes = 'OTH' WHERE list_options.list_id = 'drug_route' AND title = 'To Skin';
#EndIf

#IfNotRow3D list_options list_id drug_route notes OTH title To Affected Area
UPDATE list_options SET list_options.notes = 'OTH' WHERE list_options.list_id = 'drug_route' AND title = 'To Affected Area';
#EndIf

#IfNotRow3D list_options list_id drug_route notes OTH title Sublingual
UPDATE list_options SET list_options.notes = 'OTH' WHERE list_options.list_id = 'drug_route' AND title = 'Sublingual';
#EndIf

#IfNotRow3D list_options list_id drug_route notes OTH title OS
UPDATE list_options SET list_options.notes = 'OTH' WHERE list_options.list_id = 'drug_route' AND title = 'OS';
#EndIf

#IfNotRow3D list_options list_id drug_route notes OTH title OD
UPDATE list_options SET list_options.notes = 'OTH' WHERE list_options.list_id = 'drug_route' AND title = 'OD';
#EndIf

#IfNotRow3D list_options list_id drug_route notes OTH title OU
UPDATE list_options SET list_options.notes = 'OTH' WHERE list_options.list_id = 'drug_route' AND title = 'OU';
#EndIf

#IfNotRow3D list_options list_id drug_route notes OTH title SQ
UPDATE list_options SET list_options.notes = 'OTH' WHERE list_options.list_id = 'drug_route' AND title = 'SQ';
#EndIf

#IfNotRow3D list_options list_id drug_route notes IM title IM
UPDATE list_options SET list_options.notes = 'IM' WHERE list_options.list_id = 'drug_route' AND title = 'IM';
#EndIf

#IfNotRow3D list_options list_id drug_route notes IV title IV
UPDATE list_options SET list_options.notes = 'IV' WHERE list_options.list_id = 'drug_route' AND title = 'IV';
#EndIf

#IfNotRow3D list_options list_id drug_route notes NS title Per Nostril
UPDATE list_options SET list_options.notes = 'NS' WHERE list_options.list_id = 'drug_route' AND title = 'Per Nostril';
#EndIf

#IfNotRow3D list_options list_id drug_route notes OTH title Both Ears
UPDATE list_options SET list_options.notes = 'OTH' WHERE list_options.list_id = 'drug_route' AND title = 'Both Ears';
#EndIf

#IfNotRow3D list_options list_id drug_route notes OTH title Left Ear
UPDATE list_options SET list_options.notes = 'OTH' WHERE list_options.list_id = 'drug_route' AND title = 'Left Ear';
#EndIf

#IfNotRow3D list_options list_id drug_route notes OTH title Right Ear
UPDATE list_options SET list_options.notes = 'OTH' WHERE list_options.list_id = 'drug_route' AND title = 'Right Ear';
#EndIf

#IfNotRow2Dx2 list_options list_id drug_route title intradermal title Intradermal
INSERT INTO list_options ( list_id, option_id, title, seq,  notes ) VALUES ('drug_route', 'intradermal', 'Intradermal', 20, 'ID');
#EndIf

#IfNotRow2Dx2 list_options list_id drug_route title other title Other/Miscellaneous
INSERT INTO list_options ( list_id, option_id, title, seq, notes ) VALUES ('drug_route', 'other', 'Other/Miscellaneous', 30, 'OTH');
#EndIf

#IfNotRow2Dx2 list_options list_id drug_route title transdermal title Transdermal
INSERT INTO list_options ( list_id, option_id, title, seq, notes ) VALUES ('drug_route', 'transdermal', 'Transdermal', 40, 'TD');
#EndIf

#IfNotRow2D list_options list_id lists option_id physician_type
INSERT INTO list_options (list_id,option_id,title) VALUES ('lists','physician_type','Physician Type');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','attending_physician','SNOMED-CT:405279007','Attending physician', '10');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','audiological_physician','SNOMED-CT:310172001','Audiological physician', '20');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','chest_physician','SNOMED-CT:309345004','Chest physician', '30');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','community_health_physician','SNOMED-CT:23278007','Community health physician', '40');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','consultant_physician','SNOMED-CT:158967008','Consultant physician', '50');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','general_physician','SNOMED-CT:59058001','General physician', '60');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','genitourinarymedicinephysician','SNOMED-CT:309358003','Genitourinary medicine physician', '70');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','occupational_physician','SNOMED-CT:158973009','Occupational physician', '80');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','palliative_care_physician','SNOMED-CT:309359006','Palliative care physician', '90');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','physician','SNOMED-CT:309343006','Physician', '100');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','public_health_physician','SNOMED-CT:56466003','Public health physician', '110');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','rehabilitation_physician','SNOMED-CT:309360001','Rehabilitation physician', '120');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','resident_physician','SNOMED-CT:405277009','Resident physician', '130');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','specialized_physician','SNOMED-CT:69280009','Specialized physician', '140');
INSERT INTO list_options (list_id, option_id, codes,title, seq) VALUES ('physician_type','thoracic_physician','SNOMED-CT:309346003','Thoracic physician', '150');
#EndIf

#IfNotRow3D list_options list_id marital option_id married notes M
update list_options set notes = 'M' where list_id = 'marital' and option_id = 'married';
#EndIf

#IfNotRow3D list_options list_id marital option_id single notes S
update list_options set notes = 'S' where list_id = 'marital' and option_id = 'single';
#EndIf

#IfNotRow3D list_options list_id marital option_id divorced notes D
update list_options set notes = 'D' where list_id = 'marital' and option_id = 'divorced';
#EndIf

#IfNotRow3D list_options list_id marital option_id widowed notes W
update list_options set notes = 'W' where list_id = 'marital' and option_id = 'widowed';
#EndIf

#IfNotRow3D list_options list_id marital option_id separated notes L
update list_options set notes = 'L' where list_id = 'marital' and option_id = 'separated';
update list_options set notes = 'T' where list_id = 'marital' and option_id = 'domestic partner';
#EndIf

#IfMissingColumn users physician_type
ALTER TABLE users ADD COLUMN physician_type VARCHAR(50) DEFAULT NULL;
#EndIf

#IfMissingColumn facility facility_code
ALTER TABLE facility ADD COLUMN facility_code VARCHAR(31) default NULL;
#EndIf

#IfMissingColumn documents audit_master_approval_status
ALTER TABLE documents ADD COLUMN audit_master_approval_status TINYINT DEFAULT 1 NOT NULL COMMENT 'approval_status from audit_master table';
#EndIf

#IfMissingColumn documents audit_master_id
ALTER TABLE documents ADD COLUMN  audit_master_id int(11) default NULL;
#EndIf

#IfMissingColumn patient_data religion
SET @group_name = (SELECT group_name FROM layout_options WHERE field_id='ethnicity' AND form_id='DEM');
SET @backup_group_name = (SELECT group_name FROM layout_options WHERE field_id='DOB' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_name = IFNULL(@group_name,@backup_group_name) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`) VALUES ('DEM', 'religion', IFNULL(@group_name,@backup_group_name), 'Religion', @seq+1, 1, 1, 0, 0, 'religious_affiliation', 1, 3, '', '', 'Patient Religion' ) ;
ALTER TABLE patient_data ADD COLUMN religion varchar(40) NOT NULL default '';
#EndIf

#IfNotRow categories name CCDA
INSERT INTO categories (id, name, value, parent, lft, rght) select (select MAX(id) from categories) + 1, 'CCDA', '', 1, rght, rght + 1 from categories where name = 'Categories';
UPDATE categories SET rght = rght + 2 WHERE name = 'Categories';
UPDATE categories_seq SET id = (select MAX(id) from categories);
#Endif

#IfNotRow2D list_options list_id abook_type option_id ccda
INSERT INTO list_options (list_id, option_id, title, seq, option_value) VALUES ('abook_type', 'ccda', 'Care Coordination', 35, 2);
#EndIf

#IfNotRow2D list_options list_id abook_type option_id emr_direct
INSERT INTO list_options (list_id, option_id, title , seq, option_value) VALUES ('abook_type', 'emr_direct', 'EMR Direct' ,105, 4);
#EndIf

#IfNotRow2D list_options list_id abook_type option_id external_provider
INSERT INTO list_options (list_id, option_id, title , seq, option_value) VALUES ('abook_type', 'external_provider', 'External Provider' ,110, 1);
#EndIf

#IfNotRow2D list_options list_id abook_type option_id external_org
INSERT INTO list_options (list_id, option_id, title , seq, option_value) VALUES ('abook_type', 'external_org', 'External Organization' ,120, 1);
#EndIf

#IfMissingColumn immunizations external_id
ALTER TABLE `immunizations` ADD COLUMN `external_id` VARCHAR(20) DEFAULT NULL;
#EndIf

#IfMissingColumn prescriptions external_id
ALTER TABLE `prescriptions` ADD COLUMN `external_id` VARCHAR(20) DEFAULT NULL;
#EndIf

#IfMissingColumn lists external_id
ALTER TABLE `lists` ADD COLUMN `external_id` VARCHAR(20) DEFAULT NULL;
#EndIf

#IfMissingColumn form_vitals external_id
ALTER TABLE `form_vitals` ADD COLUMN `external_id` VARCHAR(20) DEFAULT NULL;
#EndIf

#IfMissingColumn form_encounter external_id
ALTER TABLE `form_encounter` ADD COLUMN `external_id` VARCHAR(20) DEFAULT NULL;
#EndIf

#IfMissingColumn billing external_id
ALTER TABLE `billing` ADD COLUMN `external_id` VARCHAR(20) DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_order external_id
ALTER TABLE `procedure_order` ADD COLUMN `external_id` VARCHAR(20) DEFAULT NULL;
#EndIf

#IfMissingColumn patient_data industry
SET @group_name = (SELECT group_name FROM layout_options WHERE field_id='occupation' AND form_id='DEM');
SET @backup_group_name = (SELECT group_name FROM layout_options WHERE field_id='DOB' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_name = IFNULL(@group_name,@backup_group_name) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`) VALUES ('DEM', 'industry', IFNULL(@group_name,@backup_group_name), 'Industry', @seq+1, 1, 1, 0, 0, 'Industry', 1, 1, '', '', 'Industry' ) ;
ALTER TABLE patient_data ADD COLUMN industry TEXT NOT NULL;
#EndIf

#IfNotRow2D list_options list_id lists option_id Industry
INSERT INTO `list_options` (`list_id`, `option_id`, `title`) VALUES('lists','Industry','Industry');
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('Industry', 'law_firm', 'Law Firm', 10);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('Industry', 'engineering_firm', 'Engineering Firm', 20);
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('Industry', 'construction_firm', 'Construction Firm', 30);
#EndIf

#IfNotListOccupation
#EndIf

#IfNotRow2D list_options list_id Occupation option_id lawyer
SET @max_list_id = (SELECT MAX(seq) FROM list_options WHERE list_id='Occupation');
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('Occupation', 'lawyer', 'Lawyer', IFNULL(@max_list_id,0) + 10);
#EndIf

#IfNotRow2D list_options list_id Occupation option_id engineer
SET @max_list_id = (SELECT MAX(seq) FROM list_options WHERE list_id='Occupation');
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('Occupation', 'engineer', 'Engineer', (@max_list_id+10));
#EndIf

#IfNotRow2D list_options list_id Occupation option_id site_worker
SET @max_list_id = (SELECT MAX(seq) FROM list_options WHERE list_id='Occupation');
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('Occupation', 'site_worker', 'Site Worker', (@max_list_id+10));
#EndIf

#IfNotRow3D layout_options field_id occupation form_id DEM data_type 26
UPDATE layout_options SET list_id='Occupation', data_type='26', fld_length='0', max_length='0', edit_options='' WHERE field_id='occupation' AND form_id='DEM';
#EndIf

#IfMissingColumn patient_access_offsite portal_relation
ALTER TABLE patient_access_offsite ADD COLUMN portal_relation VARCHAR(100) NULL;
#EndIf

#IfMissingColumn pnotes portal_relation
ALTER TABLE pnotes ADD COLUMN `portal_relation` VARCHAR(100) NULL;
#EndIf

#IfMissingColumn pnotes is_msg_encrypted
ALTER TABLE pnotes ADD is_msg_encrypted TINYINT(2) DEFAULT '0' COMMENT 'Whether messsage encrypted 0-Not encrypted, 1-Encrypted'; 
#EndIf

#IfMissingColumn log log_from
ALTER TABLE `log` ADD `log_from` VARCHAR(20) DEFAULT 'open-emr'; 
#EndIf

#IfMissingColumn log menu_item_id
ALTER TABLE `log` ADD `menu_item_id` INT(11) DEFAULT NULL;
#EndIf

#IfNotTable patient_portal_menu
CREATE TABLE `patient_portal_menu` (
  `patient_portal_menu_id` INT(11) NOT NULL AUTO_INCREMENT,
  `patient_portal_menu_group_id` INT(11) DEFAULT NULL,
  `menu_name` VARCHAR(40) DEFAULT NULL,
  `menu_order` SMALLINT(4) DEFAULT NULL,
  `menu_status` TINYINT(2) DEFAULT '1',
  PRIMARY KEY (`patient_portal_menu_id`)
) ENGINE=INNODB AUTO_INCREMENT=14;

INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (1,1,'Dashboard',3,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (2,1,'My Profile',6,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (3,1,'Appointments',9,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (4,1,'Documents',12,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (5,1,'Med Records',15,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (6,1,'My Account',18,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (7,1,'Mailbox',21,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (8,1,'Password',24,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (9,1,'View Log',27,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (10,1,'Logout',30,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (11,1,'View Health Information',33,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (12,1,'Download Health Information',36,1);
INSERT  INTO `patient_portal_menu`(`patient_portal_menu_id`,`patient_portal_menu_group_id`,`menu_name`,`menu_order`,`menu_status`) VALUES (13,1,'Transmit Health Information',39,1);

#Endif

#IfMissingColumn log ccda_doc_id
ALTER TABLE `log` ADD `ccda_doc_id` INT(11) DEFAULT NULL COMMENT 'CCDA document id from ccda';
#Endif

#IfNotListReaction
#EndIf

#IfNotRow2D list_options list_id reaction option_id unassigned
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('reaction', 'unassigned', 'Unassigned', 0);
#EndIf

#IfNotRow2D list_options list_id reaction option_id hives
SET @max_list_id = (SELECT MAX(seq) FROM list_options WHERE list_id='reaction');
INSERT INTO list_options ( list_id, option_id, title, seq, codes ) VALUES ('reaction', 'hives', 'Hives', (@max_list_id+10), 'SNOMED-CT:247472004');
#EndIf

#IfNotRow2D list_options list_id reaction option_id nausea
SET @max_list_id = (SELECT MAX(seq) FROM list_options WHERE list_id='reaction');
INSERT INTO list_options ( list_id, option_id, title, seq, codes ) VALUES ('reaction', 'nausea', 'Nausea', (@max_list_id+10), 'SNOMED-CT:422587007');
#EndIf

#IfNotRow2D list_options list_id lists option_id county
INSERT INTO list_options (list_id, option_id, title) VALUES ('lists','county','County');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('county','adair','ADAIR','001', '10');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('county','andrew','ANDREW','003', '20');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('county','atchison','ATCHISON','005', '30');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('county','audrain','AUDRAIN','007', '40');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('county','barry','BARRY','009', '50');
#EndIf

#IfMissingColumn patient_data county
SET @group_name = (SELECT group_name FROM layout_options WHERE field_id='country_code' AND form_id='DEM');
SET @backup_group_name = (SELECT group_name FROM layout_options WHERE field_id='DOB' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_name = IFNULL(@group_name,@backup_group_name) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`) VALUES ('DEM', 'county', IFNULL(@group_name,@backup_group_name), 'County', @seq+1, 26, 1, 0, 0, 'county', 1, 1, '', '', 'County' ) ;
ALTER TABLE `patient_data` ADD COLUMN `county` varchar(40) NOT NULL default '';
#EndIf 

#IfNotListImmunizationManufacturer
#EndIf

#IfNotRow2D list_options list_id lists option_id Immunization_Manufacturer
INSERT INTO list_options (list_id, option_id, title) VALUES ('lists','Immunization_Manufacturer','Immunization Manufacturer');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','AB','Abbott Laboratories','AB','10');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','ACA','Acambis, Inc','ACA','20');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','AD','Adams Laboratories, Inc.','AD','30');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','AKR','Akorn, Inc','AKR','40');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','ALP','Alpha Therapeutic Corporation','ALP','50');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','AR','Armour','AR','60');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','AVB','Aventis Behring L.L.C.','AVB','70');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','AVI','Aviron','AVI','80');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','BRR','Barr Laboratories','BRR','90');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','BAH','Baxter Healthcare Corporation','BAH','100');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','BA','Baxter Healthcare Corporation-inactive','BA','110');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','BAY','Bayer Corporation','BAY','120');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','BP','Berna Products','BP','130');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','BPC','Berna Products Corporation','BPC','140');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','BTP','Biotest Pharmaceuticals Corporation','BTP','150');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','CNJ','Cangene Corporation','CNJ','160');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','CMP','Celltech Medeva Pharmaceuticals','CMP','170');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','CEN','Centeon L.L.C.','CEN','180');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','CHI','Chiron Corporation','CHI','190');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','CON','Connaught','CON','200');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','CRU','Crucell','CRU','210');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','CSL','CSL Behring, Inc','CSL','220');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','DVC','DynPort Vaccine Company, LLC','DVC','230');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','MIP','Emergent BioDefense Operations Lansing','MIP','240');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','EVN','Evans Medical Limited','EVN','250');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','GEO','GeoVax Labs, Inc.','GEO','260');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','SKB','GlaxoSmithKline','SKB','270');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','GRE','Greer Laboratories, Inc.','GRE','280');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','GRF','Grifols','GRF','290');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','IDB','ID Biomedical','IDB','300');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','IAG','Immuno International AG','IAG','310');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','IUS','Immuno-U.S., Inc.','IUS','320');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','INT','Intercell Biomedical','INT','330');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','JNJ','Johnson and Johnson','JNJ','340');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','KGC','Korea Green Cross Corporation','KGC','350');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','LED','Lederle','LED','360');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','MBL','Massachusetts Biologic Laboratories','MBL','370');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','MA','Massachusetts Public Health Biologic Laboratories','MA','380');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','MED','MedImmune, Inc.','MED','390');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','MSD','Merck and Co., Inc.','MSD','400');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','IM','Merieux','IM','410');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','MIL','Miles','MIL','420');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','NAB','NABI','NAB','430');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','NYB','New York Blood Center','NYB','440');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','NAV','North American Vaccine, Inc.','NAV','450');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','NOV','Novartis Pharmaceutical Corporation','NOV','460');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','NVX','Novavax, Inc.','NVX','470');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','OTC','Organon Teknika Corporation','OTC','480');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','ORT','Ortho-clinical Diagnostics','ORT','490');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','OTH','Other manufacturer','OTH','500');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','PD','Parkedale Pharmaceuticals','PD','510');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','PFR','Pfizer, Inc','PFR','520');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','PWJ','PowderJect Pharmaceuticals','PWJ','530');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','PRX','Praxis Biologics','PRX','540');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','PSC','Protein Sciences','PSC','550');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','PMC','sanofi pasteur','PMC','560');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','SCL','Sclavo, Inc.','SCL','570');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','SOL','Solvay Pharmaceuticals','SOL','580');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','SI','Swiss Serum and Vaccine Inst.','SI','590');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','TAL','Talecris Biotherapeutics','TAL','600');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','JPN','The Research Foundation for Microbial Diseases of Osaka University (BIKEN)','JPN','610');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','USA','United States Army Medical Research and Material Command','USA','620');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','UNK','Unknown manufacturer','UNK','630');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','VXG','VaxGen','VXG','640');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','WAL','Wyeth','WAL','650');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','WA','Wyeth-Ayerst','WA','660');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Manufacturer','ZLB','ZLB Behring','ZLB','670');
#EndIf

#IfNotRow2D list_options list_id lists option_id Immunization_Completion_Status
INSERT INTO list_options (list_id, option_id, title) VALUES ('lists','Immunization_Completion_Status','Immunization Completion Status');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Completion_Status','Completed','completed','CP', '10');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Completion_Status','Refused','Refused','RE', '20');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Completion_Status','Not_Administered','Not Administered','NA', '30');
INSERT INTO list_options (list_id, option_id, title, notes, seq) VALUES ('Immunization_Completion_Status','Partially_Administered','Partially Administered','PA', '40');
#EndIf

#IfMissingColumn immunizations completion_status
ALTER TABLE immunizations ADD COLUMN `completion_status` VARCHAR(50) DEFAULT NULL;
#EndIf

#IfMissingColumn prescriptions indication
ALTER TABLE prescriptions ADD COLUMN `indication` text;
#EndIf

#IfMissingColumn prescriptions end_date
ALTER TABLE prescriptions ADD COLUMN `end_date` date default NULL;
#EndIf

#IfNotTable external_procedures
CREATE TABLE `external_procedures` (
  `ep_id` int(11) NOT NULL AUTO_INCREMENT,
  `ep_date` date DEFAULT NULL,
  `ep_code_type` varchar(20) DEFAULT NULL,
  `ep_code` varchar(9) DEFAULT NULL,
  `ep_pid` int(11) DEFAULT NULL,
  `ep_encounter` int(11) DEFAULT NULL,
  `ep_code_text` longtext,
  `ep_facility_id` varchar(255) DEFAULT NULL,
  `ep_external_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ep_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable external_encounters
CREATE TABLE `external_encounters` (
  `ee_id` int(11) NOT NULL AUTO_INCREMENT,
  `ee_date` date DEFAULT NULL,
  `ee_pid` int(11) DEFAULT NULL,
  `ee_provider_id` varchar(255) DEFAULT NULL,
  `ee_facility_id` varchar(255) DEFAULT NULL,
  `ee_encounter_diagnosis` varchar(255) DEFAULT NULL,
  `ee_external_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ee_id`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn prescriptions prn
ALTER TABLE prescriptions ADD COLUMN `prn` VARCHAR(30) DEFAULT NULL;
#EndIf

#IfMissingColumn patient_data care_team
SET @group_name = (SELECT group_name FROM layout_options WHERE field_id='ref_providerID' AND form_id='DEM');
SET @backup_group_name = (SELECT group_name FROM layout_options WHERE field_id='DOB' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_name = IFNULL(@group_name,@backup_group_name) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`) VALUES ('DEM', 'care_team', IFNULL(@group_name,@backup_group_name), 'Care Team', @seq+1, 11, 1, 0, 0, '', 1, 1, '', '', '' ) ;
alter table patient_data add column care_team int(11) DEFAULT NULL;
#EndIf

#IfNotTable form_observation
CREATE TABLE `form_observation` (
  `id` bigint(20) NOT NULL,
  `date` DATE DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `observation` varchar(255) DEFAULT NULL,
  `ob_value` varchar(255),
  `ob_unit` varchar(255),
  `description` varchar(255),
  `code_type` varchar(255),
  `table_code` varchar(255)
) ENGINE=InnoDB;
SET @seq = (SELECT MAX(id) FROM registry);
INSERT INTO `registry` (`name`,`state`,`directory`,`id`,`sql_run`,`unpackaged`,`date`,`priority`,`category`,`nickname`) VALUES ('Observation', 1, 'observation', @seq+1, 1, 1, '2015-09-09 00:00:00', 0, 'Clinical', '');
#EndIf

#IfNotTable form_care_plan
CREATE TABLE `form_care_plan` (
  `id` bigint(20) NOT NULL,
  `date` DATE DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `codetext` text,
  `description` text,
  `external_id` VARCHAR(30) DEFAULT NULL
) ENGINE=InnoDB;
SET @seq = (SELECT MAX(id) FROM registry);
INSERT INTO `registry` (`name`,`state`,`directory`,`id`,`sql_run`,`unpackaged`,`date`,`priority`,`category`,`nickname`) VALUES ('Care Plan', 1, 'care_plan', @seq+1, 1, 1, '2015-09-09 00:00:00', 0, 'Clinical', '');
#EndIf

#IfNotTable form_functional_cognitive_status
CREATE TABLE `form_functional_cognitive_status` (
  `id` bigint(20) NOT NULL,
  `date` DATE DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `codetext` text,
  `description` text,
  `external_id` VARCHAR(30) DEFAULT NULL
) ENGINE=InnoDB;
SET @seq = (SELECT MAX(id) FROM registry);
INSERT INTO `registry` (`name`,`state`,`directory`,`id`,`sql_run`,`unpackaged`,`date`,`priority`,`category`,`nickname`) VALUES ('Functional and Cognitive Status', 1, 'functional_cognitive_status', @seq+1, 1, 1, '2015-09-09 00:00:00', 0, 'Clinical', '');
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
	('clinical_rules', 'e_prescribe_stage1_amc', 'Generate and transmit permissible prescriptions electronically (Not including controlled substances).', 50, 0, 0, '', '', '', 0, 0);
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
	('clinical_rules', 'e_prescribe_2_stage2_amc', 'Generate and transmit permissible prescriptions electronically (Not including controlled substances).', 50, 0, 0, '', '', '', 0, 0);
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

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2015-10-01 load_filename 2016-PCS-Long-Abbrev-Titles.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2015-10-01', '2016-PCS-Long-Abbrev-Titles.zip', 'd5ea519d0257db0ed7deb0406a4d0503');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2015-10-01 load_filename 2016-General-Equivalence-Mappings.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2015-10-01', '2016-General-Equivalence-Mappings.zip', '3324a45b6040be7e48ab770a0d3ca695');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2015-10-01 load_filename 2016-Code-Descriptions-in-Tabular-Order.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2015-10-01', '2016-Code-Descriptions-in-Tabular-Order.zip', '518a47fe9e268e4fb72fecf633d15f17');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2015-10-01 load_filename 2016-ProcedureGEMs.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2015-10-01', '2016-ProcedureGEMs.zip', '45a8d9da18d8aed57f0c6ea91e3e8fe4');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2015-10-01 load_filename Reimbursement_Mapping_dx_2016.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2015-10-01', 'Reimbursement_Mapping_dx_2016.zip', '1b53b512e10c1fdf7ae4cfd1baa8dfbb');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2015-10-01 load_filename Reimbursement_Mapping_pr_2016.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2015-10-01', 'Reimbursement_Mapping_pr_2016.zip', '3c780dd103d116aa57980decfddd4f19');
#EndIf

#IfNotRow2D list_options list_id transactions option_id LBTref
UPDATE list_options SET title = 'Layout-Based Transaction Forms', seq = 9 WHERE list_id = 'lists' AND option_id = 'transactions';
UPDATE list_options SET option_id = 'LBTref'   WHERE list_id = 'transactions' AND option_id = 'Referral';
UPDATE list_options SET option_id = 'LBTptreq' WHERE list_id = 'transactions' AND option_id = 'Patient Request';
UPDATE list_options SET option_id = 'LBTphreq' WHERE list_id = 'transactions' AND option_id = 'Physician Request';
UPDATE list_options SET option_id = 'LBTlegal' WHERE list_id = 'transactions' AND option_id = 'Legal';
UPDATE list_options SET option_id = 'LBTbill'  WHERE list_id = 'transactions' AND option_id = 'Billing';
UPDATE transactions SET title     = 'LBTref'   WHERE title = 'Referral';
UPDATE transactions SET title     = 'LBTptreq' WHERE title = 'Patient Request';
UPDATE transactions SET title     = 'LBTphreq' WHERE title = 'Physician Request';
UPDATE transactions SET title     = 'LBTlegal' WHERE title = 'Legal';
UPDATE transactions SET title     = 'LBTbill'  WHERE title = 'Billing';
UPDATE layout_options SET form_id = 'LBTref'   WHERE form_id = 'REF';

INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,
  `max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`)
  VALUES ('LBTptreq','body','1','Details',10,3,2,30,0,'',1,3,'','','Content',5);

INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,
  `max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`)
  VALUES ('LBTphreq','body','1','Details',10,3,2,30,0,'',1,3,'','','Content',5);

INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,
  `max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`)
  VALUES ('LBTlegal','body','1','Details',10,3,2,30,0,'',1,3,'','','Content',5);

INSERT INTO `layout_options` (`form_id`,`field_id`,`group_name`,`title`,`seq`,`data_type`,`uor`,`fld_length`,
  `max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`)
  VALUES ('LBTbill' ,'body','1','Details',10,3,2,30,0,'',1,3,'','','Content',5);
#EndIf

#IfNotTable lbt_data
CREATE TABLE `lbt_data` (
  `form_id`     bigint(20)   NOT NULL COMMENT 'references transactions.id',
  `field_id`    varchar(31)  NOT NULL COMMENT 'references layout_options.field_id',
  `field_value` TEXT         NOT NULL,
  PRIMARY KEY (`form_id`,`field_id`)
) ENGINE=MyISAM COMMENT='contains all data from layout-based transactions';
#EndIf

#IfColumn transactions                                body
INSERT INTO lbt_data SELECT id, 'body'              , body               FROM transactions WHERE body               != '';
ALTER TABLE transactions DROP COLUMN                  body;
#EndIf
#IfColumn transactions                                refer_date
INSERT INTO lbt_data SELECT id, 'refer_date'        , refer_date         FROM transactions WHERE refer_date         IS NOT NULL;
ALTER TABLE transactions DROP COLUMN                  refer_date;
#EndIf
#IfColumn transactions                                refer_from
INSERT INTO lbt_data SELECT id, 'refer_from'        , refer_from         FROM transactions WHERE refer_from         != 0;
ALTER TABLE transactions DROP COLUMN                  refer_from;
#EndIf
#IfColumn transactions                                refer_to
INSERT INTO lbt_data SELECT id, 'refer_to'          , refer_to           FROM transactions WHERE refer_to           != 0;
ALTER TABLE transactions DROP COLUMN                  refer_to;
#EndIf
#IfColumn transactions                                refer_diag
INSERT INTO lbt_data SELECT id, 'refer_diag'        , refer_diag         FROM transactions WHERE refer_diag         != '';
ALTER TABLE transactions DROP COLUMN                  refer_diag;
#EndIf
#IfColumn transactions                                refer_risk_level
INSERT INTO lbt_data SELECT id, 'refer_risk_level'  , refer_risk_level   FROM transactions WHERE refer_risk_level   != '';
ALTER TABLE transactions DROP COLUMN                  refer_risk_level;
#EndIf
#IfColumn transactions                                refer_vitals
INSERT INTO lbt_data SELECT id, 'refer_vitals'      , refer_vitals       FROM transactions WHERE refer_vitals       != 0;
ALTER TABLE transactions DROP COLUMN                  refer_vitals;
#EndIf
#IfColumn transactions                                refer_external
INSERT INTO lbt_data SELECT id, 'refer_external'    , refer_external     FROM transactions WHERE refer_external     != 0;
ALTER TABLE transactions DROP COLUMN                  refer_external;
#EndIf
#IfColumn transactions                                refer_related_code
INSERT INTO lbt_data SELECT id, 'refer_related_code', refer_related_code FROM transactions WHERE refer_related_code != '';
ALTER TABLE transactions DROP COLUMN                  refer_related_code;
#EndIf
#IfColumn transactions                                refer_reply_date
INSERT INTO lbt_data SELECT id, 'refer_reply_date'  , refer_reply_date   FROM transactions WHERE refer_reply_date   IS NOT NULL;
ALTER TABLE transactions DROP COLUMN                  refer_reply_date;
#EndIf
#IfColumn transactions                                reply_date
INSERT INTO lbt_data SELECT id, 'reply_date'        , reply_date         FROM transactions WHERE reply_date         IS NOT NULL;
ALTER TABLE transactions DROP COLUMN                  reply_date;
#EndIf
#IfColumn transactions                                reply_from
INSERT INTO lbt_data SELECT id, 'reply_from'        , reply_from         FROM transactions WHERE reply_from         != '';
ALTER TABLE transactions DROP COLUMN                  reply_from;
#EndIf
#IfColumn transactions                                reply_init_diag
INSERT INTO lbt_data SELECT id, 'reply_init_diag'   , reply_init_diag    FROM transactions WHERE reply_init_diag    != '';
ALTER TABLE transactions DROP COLUMN                  reply_init_diag;
#EndIf
#IfColumn transactions                                reply_final_diag
INSERT INTO lbt_data SELECT id, 'reply_final_diag'  , reply_final_diag   FROM transactions WHERE reply_final_diag   != '';
ALTER TABLE transactions DROP COLUMN                  reply_final_diag;
#EndIf
#IfColumn transactions                                reply_documents
INSERT INTO lbt_data SELECT id, 'reply_documents'   , reply_documents    FROM transactions WHERE reply_documents    != '';
ALTER TABLE transactions DROP COLUMN                  reply_documents;
#EndIf
#IfColumn transactions                                reply_findings
INSERT INTO lbt_data SELECT id, 'reply_findings'    , reply_findings     FROM transactions WHERE reply_findings     != '';
ALTER TABLE transactions DROP COLUMN                  reply_findings;
#EndIf
#IfColumn transactions                                reply_services
INSERT INTO lbt_data SELECT id, 'reply_services'    , reply_services     FROM transactions WHERE reply_services     != '';
ALTER TABLE transactions DROP COLUMN                  reply_services;
#EndIf
#IfColumn transactions                                reply_recommend
INSERT INTO lbt_data SELECT id, 'reply_recommend'   , reply_recommend    FROM transactions WHERE reply_recommend    != '';
ALTER TABLE transactions DROP COLUMN                  reply_recommend;
#EndIf
#IfColumn transactions                                reply_rx_refer
INSERT INTO lbt_data SELECT id, 'reply_rx_refer'    , reply_rx_refer     FROM transactions WHERE reply_rx_refer     != '';
ALTER TABLE transactions DROP COLUMN                  reply_rx_refer;
#EndIf
#IfColumn transactions                                reply_related_code
INSERT INTO lbt_data SELECT id, 'reply_related_code', reply_related_code FROM transactions WHERE reply_related_code != '';
ALTER TABLE transactions DROP COLUMN                  reply_related_code;
#EndIf

#IfRow2D clinical_rules id secure_messaging_amc amc_code_2014 170.314(g)(1)/(2)
UPDATE `clinical_rules` SET `amc_code_2014` = '170.314(g)(1)/(2)-19' WHERE `id` = 'secure_messaging_amc' AND `amc_code_2014` = '170.314(g)(1)/(2)'; 
#EndIf

#IfMissingColumn documents documentationOf
ALTER TABLE `documents` ADD `documentationOf` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn ccda_components ccda_type
ALTER TABLE `ccda_components` ADD ccda_type int(11) NOT NULL COMMENT '0=>sections,1=>components';
#EndIf

#IfNotRow2D ccda_components ccda_components_field allergies ccda_components_name Allergies
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('allergies','Allergies',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field medications ccda_components_name Medications
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('medications','Medications',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field problems ccda_components_name Problems
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('problems','Problems',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field immunizations ccda_components_name Immunizations
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('immunizations','Immunizations',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field procedures ccda_components_name Procedures
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('procedures','Procedures',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field results ccda_components_name Results
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('results','Results',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field plan_of_care ccda_components_name Plan Of Care
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('plan_of_care','Plan Of Care',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field vitals ccda_components_name Vitals
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('vitals','Vitals',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field social_history ccda_components_name Social History
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('social_history','Social History',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field encounters ccda_components_name Encounters
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('encounters','Encounters',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field functional_status ccda_components_name Functional Status
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('functional_status','Functional Status',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field referral ccda_components_name Reason for Referral
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('referral','Reason for Referral',1);
#EndIf

#IfNotRow2D ccda_components ccda_components_field instructions ccda_components_name Instructions
INSERT INTO ccda_components (ccda_components_field, ccda_components_name, ccda_type) VALUES ('instructions','Instructions',1);
#EndIf

#IfNotTable form_clinical_instructions
CREATE TABLE `form_clinical_instructions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `instruction` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `activity` TINYINT DEFAULT 1 NULL,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB;
INSERT INTO `registry` (`name`,`state`,`directory`,`sql_run`,`unpackaged`,`date`,`priority`,`category`,`nickname`) VALUES ('Clinical Instructions', 1, 'clinical_instructions', 1, 1, '2015-09-09 00:00:00', 0, 'Clinical', '');
#EndIf

#IfMissingColumn clinical_rules web_reference
ALTER TABLE  `clinical_rules` ADD  `web_reference` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Clinical Rule Web Reference';
#EndIf

#IfNotTable clinical_rules_log
CREATE TABLE `clinical_rules_log` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime DEFAULT NULL,
  `pid` bigint(20) NOT NULL DEFAULT '0',
  `uid` bigint(20) NOT NULL DEFAULT '0',
  `category` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'An example category is clinical_reminder_widget',
  `value` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `uid` (`uid`),
  KEY `category` (`category`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
#EndIf

#IfMissingColumn clinical_rules access_control
ALTER TABLE `clinical_rules` ADD `access_control` VARCHAR(255) NOT NULL DEFAULT 'patients:med' COMMENT 'ACO link for access control';
#EndIf

#IfNotRow clinical_rules id rule_socsec_entry
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag`, `access_control` ) VALUES ('rule_socsec_entry', 0, 0, 0, 0, '', '', 0, '', 0, 'admin:practice');
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id rule_socsec_entry
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_socsec_entry', 'Data Entry - Social Security Number', 1500, 0);
#EndIf

#IfNotRow2D list_options list_id rule_action option_id act_soc_sec
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_soc_sec', 'Social Security Number', 155, 0);
#EndIf

#IfNotRow rule_action id rule_socsec_entry
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_socsec_entry', 1, 'act_cat_assess', 'act_soc_sec');
#EndIf

#IfNotRow2D rule_action_item category act_cat_assess item act_soc_sec
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_assess', 'act_soc_sec', '', '', 0);
#EndIf

#IfNotRow rule_reminder id rule_socsec_entry
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_socsec_entry', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_socsec_entry', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_socsec_entry', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_socsec_entry', 'patient_reminder_post', 'month', '1');
#EndIf

#IfNotRow rule_target id rule_socsec_entry
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_socsec_entry', 1, 1, 1, 'target_database', '::patient_data::ss::::::ge::1', 0);
#EndIf

#IfRow2D list_options list_id clinical_rules option_id e_prescribe_stage1_amc
UPDATE `list_options` SET `title` = 'Generate and transmit permissible prescriptions electronically (Not including controlled substances).'  WHERE list_id = 'clinical_rules' AND option_id = 'e_prescribe_stage1_amc';
#EndIf

#IfRow2D list_options list_id clinical_rules option_id e_prescribe_2_stage2_amc 
UPDATE `list_options` SET `title` = 'Generate and transmit permissible prescriptions electronically (Not including controlled substances).'  WHERE list_id = 'clinical_rules' AND option_id = 'e_prescribe_2_stage2_amc';
#EndIf

#IfNotRow clinical_rules id rule_penicillin_allergy
INSERT INTO `clinical_rules` ( `id`, `pid`, `active_alert_flag`, `passive_alert_flag`, `cqm_flag`, `cqm_nqf_code`, `cqm_pqri_code`, `amc_flag`, `amc_code`, `patient_reminder_flag` ) VALUES ('rule_penicillin_allergy', 0, 0, 0, 0, '', '', 0, '', 0);
#EndIf

#IfNotRow2D list_options list_id clinical_rules option_id rule_penicillin_allergy
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('clinical_rules', 'rule_penicillin_allergy', 'Assess Penicillin Allergy', 1600, 0);
#EndIf

#IfNotRow2D list_options list_id rule_action option_id act_penicillin_allergy
INSERT INTO `list_options` ( `list_id`, `option_id`, `title`, `seq`, `is_default` ) VALUES ('rule_action' ,'act_penicillin_allergy', 'Penicillin Allergy', 157, 0);
#EndIf

#IfNotRow rule_action id rule_penicillin_allergy
INSERT INTO `rule_action` ( `id`, `group_id`, `category`, `item` ) VALUES ('rule_penicillin_allergy', 1, 'act_cat_assess', 'act_penicillin_allergy');
#EndIf

#IfNotRow2D rule_action_item category act_cat_assess item act_penicillin_allergy
INSERT INTO `rule_action_item` ( `category`, `item`, `clin_rem_link`, `reminder_message`, `custom_flag` ) VALUES ('act_cat_assess', 'act_penicillin_allergy', '', '', 1);
#EndIf

#IfNotRow rule_reminder id rule_penicillin_allergy
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_penicillin_allergy', 'clinical_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_penicillin_allergy', 'clinical_reminder_post', 'month', '1');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_penicillin_allergy', 'patient_reminder_pre', 'week', '2');
INSERT INTO `rule_reminder` ( `id`, `method`, `method_detail`, `value` ) VALUES ('rule_penicillin_allergy', 'patient_reminder_post', 'month', '1');
#EndIf

#IfNotRow rule_filter id rule_penicillin_allergy
INSERT INTO `rule_filter` ( `id`, `include_flag`, `required_flag`, `method`, `method_detail`, `value` ) VALUES ('rule_penicillin_allergy', 1, 0, 'filt_lists', 'allergy', 'penicillin');
#EndIf

#IfNotRow rule_target id rule_penicillin_allergy
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_penicillin_allergy', 1, 1, 1, 'target_interval', 'year', 1);
INSERT INTO `rule_target` ( `id`, `group_id`, `include_flag`, `required_flag`, `method`, `value`, `interval` ) VALUES ('rule_penicillin_allergy', 1, 1, 1, 'target_database', 'CUSTOM::act_cat_assess::act_penicillin_allergy::YES::ge::1', 0);
#EndIf

#IfMissingColumn clinical_rules_log new_value
ALTER TABLE  `clinical_rules_log` ADD `new_value` TEXT NOT NULL;
#EndIf

#IfNotColumnType procedure_report date_report datetime
ALTER TABLE `procedure_report` CHANGE `date_report` `date_report` datetime DEFAULT NULL;
#EndIf
