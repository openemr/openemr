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

