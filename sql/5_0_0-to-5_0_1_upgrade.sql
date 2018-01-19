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

#IfMissingColumn list_options edit_options
  ALTER TABLE `list_options` ADD `edit_options` TINYINT(1) NOT NULL DEFAULT '1';
#Endif

#IfMissingColumn list_options timestamp
ALTER TABLE `list_options` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ;
#Endif

#IfNotTable multiple_db
  CREATE TABLE `multiple_db` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `namespace` varchar(255) NOT NULL,
    `username` varchar(255) NOT NULL,
    `password` text,
    `dbname` varchar(255) NOT NULL,
    `host` varchar(255) NOT NULL DEFAULT 'localhost',
    `port` smallint(4) NOT NULL DEFAULT '3306',
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     UNIQUE KEY `namespace` (namespace),
     PRIMARY KEY (id)
  ) ENGINE=InnoDB;
#EndIf

#IfNotRow2D list_options list_id page_validation option_id therapy_groups_add#addGroup
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'therapy_groups_add#addGroup', '/interface/therapy_groups/index.php?method=addGroup', 120, '{group_name:{presence: true}}', 1);
#EndIf

#IfNotRow2D list_options list_id page_validation option_id therapy_groups_edit#editGroup
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'therapy_groups_edit#editGroup', '/interface/therapy_groups/index.php?method=groupDetails', 125, '{group_name:{presence: true}}', 1);
#EndIf

#IfNotRow2D list_options list_id page_validation option_id tg_add#add-participant-form
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'tg_add#add-participant-form', '/interface/therapy_groups/index.php?method=groupParticipants', 130, '{participant_name:{presence: true}, group_patient_start:{presence: true}}', 1);
#EndIf

#IfNotRow2D list_options list_id page_validation option_id add_edit_event#theform_groups
INSERT INTO `list_options` (`list_id`,`option_id`,`title`,`seq`,`is_default`,`option_value`,`mapping`,`notes`,`codes`,`toggle_setting_1`,`toggle_setting_2`,`activity`,`subtype`)
VALUES ('page_validation','add_edit_event#theform_groups','/interface/main/calendar/add_edit_event.php?group=true',150,0,0,'','{form_group:{presence: true}}','',0,0,1,'');
#EndIf

#IfNotRow2D list_options list_id page_validation option_id common#new-encounter-form
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'common#new-encounter-form', '/interface/forms/newGroupEncounter/common.php', 160, '{pc_catid:{exclusion: ["_blank"]}}', 1);
#EndIf


#IfNotTable therapy_groups
CREATE TABLE `therapy_groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `group_name` varchar(255) NOT NULL ,
  `group_start_date` date NOT NULL ,
  `group_end_date` date,
  `group_type` tinyint NOT NULL,
  `group_participation` tinyint NOT NULL,
  `group_status` int(11) NOT NULL,
  `group_notes` text,
  `group_guest_counselors` varchar(255),
  PRIMARY KEY  (`group_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable therapy_groups_participants
CREATE TABLE `therapy_groups_participants` (
  `group_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL ,
  `group_patient_status` int(11) NOT NULL,
  `group_patient_start` date NOT NULL ,
  `group_patient_end` date,
  `group_patient_comment` text,
  PRIMARY KEY (`group_id`,`pid`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable therapy_groups_participant_attendance
CREATE TABLE `therapy_groups_participant_attendance` (
  `form_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL ,
  `meeting_patient_comment` text ,
  `meeting_patient_status` varchar(15),
  PRIMARY KEY (`form_id`,`pid`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable therapy_groups_counselors
CREATE TABLE `therapy_groups_counselors`(
	`group_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn openemr_postcalendar_events pc_gid
ALTER TABLE openemr_postcalendar_events ADD pc_gid int(11) DEFAULT 0;
#EndIf

#IfNotRow2D list_options list_id lists option_id groupstat
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('lists', 'groupstat', 'Group Statuses', '13', '0', '0');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`) VALUES ('groupstat', '-', '- None', '10', '0', '0', 'FEFDCF|0');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`) VALUES ('groupstat', '=', '= Took Place', '20', '0', '0', 'FF2414|0');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`) VALUES ('groupstat', '>', '> Did Not Take Place', '30', '0', '0', 'BFBFBF|0');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`) VALUES ('groupstat', '<', '< Not Reported', '40', '0', '0', 'FEFDCF|0');
#EndIf

#IfNotRow openemr_postcalendar_categories pc_catname Group Therapy
INSERT INTO openemr_postcalendar_categories (`pc_catname`, `pc_catcolor`, `pc_recurrspec`, `pc_duration` ,`pc_cattype` , `pc_active` , `pc_seq`)
VALUES ('Group Therapy' , '#BFBFBF' , 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', '3600', '3', '1', '90');
#EndIf


#IfNotTable form_groups_encounter
CREATE TABLE `form_groups_encounter` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime default NULL,
  `reason` longtext,
  `facility` longtext,
  `facility_id` int(11) NOT NULL default '0',
  `group_id` bigint(20) default NULL,
  `encounter` bigint(20) default NULL,
  `onset_date` datetime default NULL,
  `sensitivity` varchar(30) default NULL,
  `billing_note` text,
  `pc_catid` int(11) NOT NULL default '5' COMMENT 'event category from openemr_postcalendar_categories',
  `last_level_billed` int  NOT NULL DEFAULT 0 COMMENT '0=none, 1=ins1, 2=ins2, etc',
  `last_level_closed` int  NOT NULL DEFAULT 0 COMMENT '0=none, 1=ins1, 2=ins2, etc',
  `last_stmt_date`    date DEFAULT NULL,
  `stmt_count`        int  NOT NULL DEFAULT 0,
  `provider_id` INT(11) DEFAULT '0' COMMENT 'default and main provider for this visit',
  `supervisor_id` INT(11) DEFAULT '0' COMMENT 'supervising provider, if any, for this visit',
  `invoice_refno` varchar(31) NOT NULL DEFAULT '',
  `referral_source` varchar(31) NOT NULL DEFAULT '',
  `billing_facility` INT(11) NOT NULL DEFAULT 0,
  `external_id` VARCHAR(20) DEFAULT NULL,
  `pos_code` tinyint(4) default NULL,
  `counselors` VARCHAR (255),
  `appt_id` INT(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `pid_encounter` (`group_id`, `encounter`),
  KEY `encounter_date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;
#EndIf

#IfMissingColumn forms therapy_group_id
ALTER TABLE  `forms` ADD  `therapy_group_id` INT(11) DEFAULT NULL;
#EndIf

#IfMissingColumn registry patient_encounter
ALTER TABLE `registry` ADD `patient_encounter` TINYINT NOT NULL DEFAULT '1';
#EndIf

#IfMissingColumn registry therapy_group_encounter
ALTER TABLE `registry` ADD `therapy_group_encounter` TINYINT NOT NULL DEFAULT '0';
#EndIf


#IfNotRow2D list_options list_id lists option_id attendstat
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('lists', 'attendstat', 'Group Attendance Statuses', '15', '0', '0');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`, `toggle_setting_1`) VALUES ('attendstat', '-', '- Not Reported', '10', '0', '0', 'FEFDCF|0', '0');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`, `toggle_setting_1`) VALUES ('attendstat', '@', '@ Attended', '20', '0', '0', 'FF2414|0', '1');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`, `toggle_setting_1`) VALUES ('attendstat', '?', '? Did Not Attend', '30', '0', '0', 'BFBFBF|0', '1');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`, `toggle_setting_1`) VALUES ('attendstat', '~', '~ Late Arrival', '40', '0', '0', 'BFBFBF|0', '1');
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`, `toggle_setting_1`) VALUES ('attendstat', 'x', 'x Cancelled', '50', '0', '0', 'FEFDCF|0', '0');
#EndIf

#IfNotRow registry directory group_attendance
INSERT INTO registry (name, state, directory, sql_run, unpackaged, date, priority, category, nickname, patient_encounter, therapy_group_encounter) VALUES ('Group Attendance Form', 1, 'group_attendance', 1, 1, '2015-10-15 00:00:00', 0, 'Clinical', '',0,1);
#EndIf

#IfNotRow registry directory newGroupEncounter
INSERT INTO registry (name, state, directory, sql_run, unpackaged, date, priority, category, nickname, patient_encounter, therapy_group_encounter) VALUES ('New Group Encounter Form', 1, 'newGroupEncounter', 1, 1, '2015-10-15 00:00:00', 0, 'Clinical', '',0,1);
#EndIf

#IfTable form_therapy_groups_attendance
RENAME TABLE form_therapy_groups_attendance TO form_group_attendance;
#EndIf

#IfNotTable form_group_attendance
CREATE TABLE `form_group_attendance` (
  id	bigint(20) auto_increment,
  date	date,
  group_id	int(11),
  user	varchar(255),
  groupname	varchar(255),
  authorized	tinyint(4),
  encounter_id	int(11),
  activity	tinyint(4),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;
#EndIf

#IfNotRow2D list_options list_id lists option_id files_white_list
INSERT INTO list_options (`list_id`, `option_id`, `title`) VALUES ('lists', 'files_white_list', 'Files type white list');
#EndIf

#IfNotTable onsite_documents
CREATE TABLE `onsite_documents` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int(10) UNSIGNED DEFAULT NULL,
  `facility` int(10) UNSIGNED DEFAULT NULL,
  `provider` int(10) UNSIGNED DEFAULT NULL,
  `encounter` int(10) UNSIGNED DEFAULT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `doc_type` varchar(255) NOT NULL,
  `patient_signed_status` smallint(5) UNSIGNED NOT NULL,
  `patient_signed_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `authorize_signed_time` datetime DEFAULT NULL,
  `accept_signed_status` smallint(5) NOT NULL,
  `authorizing_signator` varchar(50) NOT NULL,
  `review_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `denial_reason` varchar(255) NOT NULL,
  `authorized_signature` text,
  `patient_signature` text,
  `full_document` blob,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;
#EndIf

#IfNotTable onsite_mail
CREATE TABLE `onsite_mail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `owner` bigint(20) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `header` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` longtext,
  `recipient_id` varchar(128) DEFAULT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `sender_id` varchar(128) DEFAULT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `assigned_to` varchar(255) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0' COMMENT 'flag indicates note is deleted',
  `delete_date` datetime DEFAULT NULL,
  `mtype` varchar(128) DEFAULT NULL,
  `message_status` varchar(20) NOT NULL DEFAULT 'New',
  `mail_chain` int(11) DEFAULT NULL,
  `reply_mail_chain` int(11) DEFAULT NULL,
  `is_msg_encrypted` tinyint(2) DEFAULT '0' COMMENT 'Whether messsage encrypted 0-Not encrypted, 1-Encrypted',
  PRIMARY KEY (`id`),
  KEY `pid` (`owner`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;
#EndIf

#IfNotTable onsite_messages
CREATE TABLE `onsite_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `message` longtext,
  `ip` varchar(15) NOT NULL,
  `date` datetime NOT NULL,
  `sender_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'who sent id',
 `recip_id` varchar(255) NOT NULL COMMENT 'who to id array',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='Portal messages' AUTO_INCREMENT=1 ;
#EndIf

#IfNotTable onsite_online
CREATE TABLE `onsite_online` (
  `hash` varchar(32) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `last_update` datetime NOT NULL,
  `username` varchar(64) NOT NULL,
  `userid` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable onsite_portal_activity
CREATE TABLE `onsite_portal_activity` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `patient_id` bigint(20) DEFAULT NULL,
  `activity` varchar(255) DEFAULT NULL,
  `require_audit` tinyint(1) DEFAULT '1',
  `pending_action` varchar(255) DEFAULT NULL,
  `action_taken` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `narrative` longtext,
  `table_action` longtext,
  `table_args` longtext,
  `action_user` int(11) DEFAULT NULL,
  `action_taken_time` datetime DEFAULT NULL,
  `checksum` longtext,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;
#EndIf

#IfNotTable onsite_signatures
CREATE TABLE `onsite_signatures` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `status` varchar(128) NOT NULL DEFAULT 'waiting',
  `type` varchar(128) NOT NULL,
  `created` int(11) NOT NULL,
  `lastmod` datetime NOT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` int(11) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `activity` tinyint(4) NOT NULL DEFAULT '0',
  `authorized` tinyint(4) DEFAULT NULL,
  `signator` varchar(255) NOT NULL,
  `sig_image` text,
  `signature` text,
  `sig_hash` varchar(128) NOT NULL,
  `ip` varchar(46) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pid` (`pid`,`user`),
  KEY `encounter` (`encounter`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;
#EndIf

#IfNotRow categories name Onsite Portal
INSERT INTO categories select (select MAX(id) from categories) + 1, 'Onsite Portal', '', 1, rght, rght + 5 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'Patient', '', (select id from categories where name = 'Onsite Portal'), rght + 1, rght + 2 from categories where name = 'Categories';
INSERT INTO categories select (select MAX(id) from categories) + 1, 'Reviewed', '', (select id from categories where name = 'Onsite Portal'), rght + 3, rght + 4 from categories where name = 'Categories';
UPDATE categories SET rght = rght + 6 WHERE name = 'Categories';
UPDATE categories_seq SET id = (select MAX(id) from categories);
#EndIf

#IfNotRow2D list_options list_id apptstat option_id ^
INSERT INTO list_options ( `list_id`, `option_id`, `title`, `seq`, `is_default`, `notes` ) VALUES ('apptstat','^','^ Pending',70,0,'FEFDCF|0');
#EndIf

#IfMissingColumn registry aco_spec
ALTER TABLE `registry` ADD `aco_spec` varchar(63) NOT NULL default 'encounters|notes';
UPDATE `registry` SET `aco_spec` = 'patients|appt'     WHERE directory = 'newpatient';
UPDATE `registry` SET `aco_spec` = 'patients|appt'     WHERE directory = 'newGroupEncounter';
UPDATE `registry` SET `aco_spec` = 'encounters|coding' WHERE directory = 'fee_sheet';
UPDATE `registry` SET `aco_spec` = 'encounters|coding' WHERE directory = 'misc_billing_options';
UPDATE `registry` SET `aco_spec` = 'patients|lab'      WHERE directory = 'procedure_order';
#EndIf

#IfNotColumnType lbf_data field_value longtext
ALTER TABLE `lbf_data` CHANGE `field_value` `field_value` longtext;
#EndIf

#IfMissingColumn issue_types aco_spec
ALTER TABLE `issue_types` ADD `aco_spec` varchar(63) NOT NULL default 'patients|med';
#EndIf

#IfMissingColumn categories aco_spec
ALTER TABLE `categories` ADD `aco_spec` varchar(63) NOT NULL default 'patients|docs';
#EndIf

#IfNotRow background_services name ccdaservice
INSERT INTO `background_services` (`name`, `title`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES ('ccdaservice', 'C-CDA Node Service', 1, 'runCheck', '/ccdaservice/ssmanager.php', 95);
ALTER TABLE `background_services` CHANGE `running` `running` TINYINT(1) NOT NULL DEFAULT '-1' COMMENT 'True indicates managed service is busy. Skip this interval.';
#EndIf

#IfNotColumnType onsite_mail owner varchar(128)
ALTER TABLE `onsite_mail` CHANGE `owner` `owner` varchar(128) DEFAULT NULL;
#Endif

#IfNotColumnType openemr_postcalendar_events pc_facility int(11)
ALTER TABLE `openemr_postcalendar_events` CHANGE `pc_facility` `pc_facility` int(11) NOT NULL DEFAULT '0' COMMENT 'facility id for this event';
#Endif

#IfMissingColumn form_misc_billing_options onset_date
ALTER TABLE `form_misc_billing_options` ADD `onset_date` date default NULL;
UPDATE `list_options` SET `option_id` = 'DK', `title` = 'Ordering Provider' WHERE `list_id` = 'provider_qualifier_code' AND `option_id` = 'dk';
UPDATE `list_options` SET `option_id` = 'DN', `title` = 'Referring Provider', `is_default` = '1' WHERE `list_id` = 'provider_qualifier_code' AND `option_id` = 'dn';
#EndIF

#IfNotRow2D list_options list_id provider_qualifier_code option_id DQ
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('provider_qualifier_code', 'DQ', 'Supervising Provider', '30', '0');
#EndIf

#IfMissingColumn users main_menu_role
ALTER TABLE `users` ADD `main_menu_role` VARCHAR(50) NOT NULL DEFAULT 'standard';
#EndIf

#IfMissingColumn openemr_postcalendar_categories aco_spec
ALTER TABLE `openemr_postcalendar_categories` ADD COLUMN `aco_spec` VARCHAR(63) NOT NULL DEFAULT 'encounters|notes';
#EndIf
#IfNotRow2D list_options list_id lists option_id apps
INSERT INTO list_options (list_id,option_id,title) VALUES ('lists','apps','Apps');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('apps','*OpenEMR','main/main_screen.php',10,1,0);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('apps','Calendar','main/calendar/index.php',20,0,0);
#EndIf

#IfNotColumnType list_options list_id varchar(100)
ALTER TABLE `list_options` CHANGE `list_id` `list_id` VARCHAR(100) NOT NULL DEFAULT '';
#EndIf

#IfNotColumnType list_options option_id varchar(100)
ALTER TABLE `list_options` CHANGE `option_id` `option_id` VARCHAR(100) NOT NULL DEFAULT '';
#EndIf

#IfNotColumnType layout_options list_id varchar(100)
ALTER TABLE `layout_options` CHANGE `list_id` `list_id` VARCHAR(100) NOT NULL DEFAULT '';
#EndIf

#IfNotColumnType layout_options list_backup_id varchar(100)
ALTER TABLE `layout_options` CHANGE `list_backup_id` `list_backup_id` VARCHAR(100) NOT NULL DEFAULT '';
#EndIf

#IfNotTable patient_birthday_alert
CREATE TABLE `patient_birthday_alert` (
  `pid` bigint(20) NOT NULL DEFAULT 0,
  `user_id` bigint(20) NOT NULL DEFAULT 0,
  `turned_off_on` date NOT NULL,
  PRIMARY KEY  (`pid`,`user_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2017-10-01 load_filename 2018-ICD-10-PCS-Order-File.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2017-10-01', '2018-ICD-10-PCS-Order-File.zip', '264b342310236f2b3927062d2c72cfe3');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2017-10-01 load_filename 2018-ICD-10-CM-General-Equivalence-Mappings.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2017-10-01', '2018-ICD-10-CM-General-Equivalence-Mappings.zip', '787a025fdcf6e1da1a85be779004f670');
#EndIf

UPDATE `supported_external_dataloads` SET `load_filename`='2018-ICD-10-Code-Descriptions.zip' WHERE `load_filename`='2018-ICD-10-Code-Dedcriptions.zip' AND `load_release_date`='2017-10-01';
#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2017-10-01 load_filename 2018-ICD-10-Code-Descriptions.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2017-10-01', '2018-ICD-10-Code-Descriptions.zip', '6f9c77440132e30f565222ca9bb6599c');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2017-10-01 load_filename 2018-ICD-10-PCS-General-Equivalence-Mappings.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2017-10-01', '2018-ICD-10-PCS-General-Equivalence-Mappings.zip', 'bb73c80e272da28712887d7979b1cebf');
#EndIf

#IfColumn x12_partners x12_version
ALTER TABLE `x12_partners` DROP COLUMN `x12_version`;
#EndIf

#IfNotRow2D list_options list_id page_validation option_id add_edit_event#theform_prov
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES
('page_validation', 'add_edit_event#theform_prov', '/interface/main/calendar/add_edit_event.php?prov=true', 170, '{}', 1);
#EndIf

#IfMissingColumn claims submitted_claim
ALTER TABLE `claims` ADD COLUMN `submitted_claim` TEXT COMMENT 'This claims form claim data';
#EndIf

#IfMissingColumn billing revenue_code
ALTER TABLE `billing` ADD COLUMN `revenue_code` varchar(6) NOT NULL DEFAULT "" COMMENT 'Item revenue code';
#EndIf

#IfMissingColumn codes revenue_code
ALTER TABLE `codes` ADD COLUMN `revenue_code` varchar(6) NOT NULL DEFAULT "" COMMENT 'Item revenue code';
#EndIf

#IfMissingColumn users weno_prov_id
ALTER TABLE `users` ADD `weno_prov_id` VARCHAR(15) DEFAULT NULL;
#EndIf

#IfMissingColumn prescriptions ntx
ALTER TABLE `prescriptions` ADD `ntx` INT(2) DEFAULT NULL;
#EndIf

#IfMissingColumn prescriptions rtx
ALTER TABLE `prescriptions` ADD `rtx` INT(2) DEFAULT NULL;
#EndIf

#IfMissingColumn prescriptions txDate
ALTER TABLE `prescriptions` ADD `txDate` DATE NOT NULL;
#EndIf

#IfMissingColumn pharmacies ncpdp
ALTER TABLE `pharmacies` ADD `ncpdp` INT(12) DEFAULT NULL;
#EndIf

#IfMissingColumn pharmacies npi
ALTER TABLE `pharmacies` ADD `npi` INT(12) DEFAULT NULL;
#EndIf

#IfNotRow2Dx2 list_options list_id state option_id PR title Puerto Rico
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('state','PR','Puerto Rico',39,0);
#EndIf

#IfNotTable erx_drug_paid
CREATE TABLE `erx_drug_paid` (
  `drugid` int(11) NOT NULL AUTO_INCREMENT,
  `drug_label_name` varchar(45) NOT NULL,
  `ahfs_descr` varchar(45) NOT NULL,
  `ndc` bigint(12) NOT NULL,
  `price_per_unit` decimal(5,2) NOT NULL,
  `avg_price` decimal(6,2) NOT NULL,
  `avg_price_paid` int(6) NOT NULL,
  `avg_savings` decimal(6,2) NOT NULL,
  `avg_percent` decimal(6,2) NOT NULL,
   PRIMARY KEY (`drugid`)
   ) ENGINE=InnoDB;
#EndIf

#IfNotTable erx_rx_log
CREATE TABLE `erx_rx_log` (
 `id` int(20) NOT NULL AUTO_INCREMENT,
 `prescription_id` int(6) NOT NULL,
 `date` varchar(25) NOT NULL,
 `time` varchar(15) NOT NULL,
 `code` int(6) NOT NULL,
 `status` text,
 `message_id` varchar(100) DEFAULT NULL,
 `read` int(1) DEFAULT NULL,
 PRIMARY KEY (`id`)
  ) ENGINE=InnoDB;
#EndIf

#IfNotTable erx_narcotics
CREATE TABLE `erx_narcotics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `drug` varchar(255) NOT NULL,
  `dea_number` varchar(5) NOT NULL,
  `csa_sch` varchar(2) NOT NULL,
  `narc` varchar(2) NOT NULL,
  `other_names` varchar(255) NOT NULL,
   PRIMARY KEY (`id`)
  ) ENGINE=InnoDB;
#EndIf

UPDATE `globals` SET `gl_value`='style_red.css' WHERE `gl_name`='css_header' AND `gl_value`='style_flat_red.css';
UPDATE `globals` SET `gl_value`='style_manila.css' WHERE `gl_name`='css_header' AND `gl_value`='style_tan.css';
UPDATE `globals` SET `gl_value`='style_light.css' WHERE `gl_name`='css_header' AND (`gl_value`='style_babyblu.css'
 OR `gl_value`='style_metal.css'
 OR `gl_value`='style_oemr.css'
 OR `gl_value`='style_purple.css'
 OR `gl_value`='style_radiant.css'
 OR `gl_value`='style_sky_blue.css');

UPDATE `user_settings` SET `setting_value`='style_red.css' WHERE `setting_label`='global:css_header' AND `setting_value`='style_flat_red.css';
UPDATE `user_settings` SET `setting_value`='style_manila.css' WHERE `setting_label`='global:css_header' AND `setting_value`='style_tan.css';
UPDATE `user_settings` SET `setting_value`='style_light.css' WHERE `setting_label`='global:css_header' AND (`setting_value`='style_babyblu.css'
 OR `setting_value`='style_metal.css'
 OR `setting_value`='style_oemr.css'
 OR `setting_value`='style_purple.css'
 OR `setting_value`='style_radiant.css'
 OR `setting_value`='style_sky_blue.css');

#IfNotColumnType facility country_code varchar(30)
ALTER TABLE `facility` CHANGE `country_code` `country_code` varchar(30) NOT NULL default '';
#EndIf

#IfNotColumnType layout_options group_name varchar(255)
ALTER TABLE `layout_options` CHANGE `group_name` `group_name` varchar(255) NOT NULL default '';
#EndIf

#IfMissingColumn forms issue_id
ALTER TABLE `forms` ADD COLUMN `issue_id` bigint(20) NOT NULL default 0 COMMENT 'references lists.id to identify a case';
#EndIf

#IfMissingColumn forms provider_id
ALTER TABLE `forms` ADD COLUMN `provider_id` bigint(20) NOT NULL default 0 COMMENT 'references users.id to identify a provider';
#EndIf

#IfNotTable layout_group_properties
CREATE TABLE `layout_group_properties` (
  grp_form_id     varchar(31)    not null,
  grp_group_id    varchar(31)    not null default '' comment 'empty when representing the whole form',
  grp_title       varchar(63)    not null default '' comment 'descriptive name of the form or group',
  grp_subtitle    varchar(63)    not null default '' comment 'for display under the title',
  grp_mapping     varchar(31)    not null default '' comment 'the form category',
  grp_seq         int(11)        not null default 0  comment 'optional order within mapping',
  grp_activity    tinyint(1)     not null default 1,
  grp_repeats     int(11)        not null default 0,
  grp_columns     int(11)        not null default 0,
  grp_size        int(11)        not null default 0,
  grp_issue_type  varchar(75)    not null default '',
  grp_aco_spec    varchar(63)    not null default '',
  grp_services    varchar(4095)  not null default '',
  grp_products    varchar(4095)  not null default '',
  grp_diags       varchar(4095)  not null default '',
  PRIMARY KEY (grp_form_id, grp_group_id)
) ENGINE=InnoDB;
ALTER TABLE layout_options ADD COLUMN group_id VARCHAR(31) NOT NULL default '' AFTER group_name;
#ConvertLayoutProperties
ALTER TABLE layout_options DROP COLUMN group_name;
DELETE FROM list_options WHERE list_id = 'lbfnames';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'lbfnames';
DELETE FROM list_options WHERE list_id = 'transactions';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'transactions';
#EndIf

#IfMissingColumn openemr_postcalendar_categories pc_constant_id
ALTER TABLE `openemr_postcalendar_categories` ADD `pc_constant_id` VARCHAR (255) default NULL;
UPDATE `openemr_postcalendar_categories` SET pc_constant_id = LOWER(REPLACE (pc_catname,' ', '_'));
#EndIf

#IfMissingColumn facility facility_taxonomy
ALTER TABLE facility ADD facility_taxonomy VARCHAR(15) DEFAULT NULL;
#EndIf

