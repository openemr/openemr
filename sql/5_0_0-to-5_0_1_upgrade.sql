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

#IfNotTable form_therapy_groups_attendance
CREATE TABLE `form_therapy_groups_attendance` (
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

#IfNotTable medex_icons
CREATE TABLE IF NOT EXISTS `medex_icons` (
  `i_UID` int(11) NOT NULL AUTO_INCREMENT,
  `msg_type` varchar(50) NOT NULL,
  `msg_status` varchar(10) NOT NULL,
  `i_description` varchar(255) NOT NULL,
  `i_html` text NOT NULL,
  `i_blob` longtext NOT NULL,
  PRIMARY KEY (`i_UID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;
#Endif


INSERT INTO `medex_icons` (`i_UID`, `msg_type`, `msg_status`, `i_description`, `i_html`, `i_blob`) VALUES
(1, 'SMS', 'ALLOWED', 'Message type allowed', '<i title="SMS is possible." class="fa fa-commenting-o fa-fw"></i>', ''),
(2, 'SMS', 'NotAllowed', 'Message type not allowed', '<span class="fa-stack" title="SMS not possible"><i class="fa fa-commenting-o fa-stack-1x fa-fw"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span>', ''),
(3, 'SMS', 'SCHEDULED', 'SMS: scheduled', '<span class="btn scheduled" title="SMS scheduled"><i class="fa fa-commenting-o fa-fw"></i></span>', ''),
(4, 'SMS', 'SENT', 'SMS Sent, not read - in process', '<span class="btn" title="SMS Sent - in process" style="padding:5px;background-color:yellow;color:black;"><i class="fa fa-commenting-o fa-fw"></i></span>', ''),
(5, 'SMS', 'READ', 'SMS delivered/read', '<span class="btn" title="SMS Sent and Read - waiting for response" aria-label="SMS Delivered" style="padding:5px;background-color:#146abd;"><i class="fa fa-commenting-o fa-flip-horizontal fa-fw" aria-hidden="true"></i></span>', ''),
(6, 'SMS', 'FAILURE', 'SMS delivery failed', '<span class="btn" title="SMS Failed to be delivered" style="padding:5px;background-color:red;"><i class="fa fa-commenting-o fa-inverse fa-fw"></i></span>', ''),
(7, 'SMS', 'CONFIRMED', 'Confirmed', '<span class="btn" title="Confirmed by SMS" style="padding:5px;background-color:green;"><i class="fa fa-commenting-o fa-inverse fa-fw"></i></span>', ''),
(8, 'SMS', 'CALL', 'Callback Requested by Patient', '<span class="btn btn-success" style="padding:5px;background-color: red;" title="Patient requests Office Call">\r\n<i class="fa fa-flag fa-fw"></i></span>\r\n', ''),
(9, 'SMS', 'EXTRA', 'Extra text sent along...', '<span class="fa-stack fa-lg"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-terminal fa-stack-1x fa-fw fa-inverse"></i></span>', ''),
(10, 'SMS', 'STOP', 'Optout of SMS please', '<span class="btn btn-danger" title="OptOut of SMS Messaging. Demographics updated." aria-label=''Optout SMS''><i class="fa fa-commenting" aria-hidden="true"> STOP</i></span>', ''),
(11, 'AVM', 'ALLOWED', 'Message type allowed', '<span title="Automated Voice Messages are possible" class="fa fa-phone fa-fw"></span>', ''),
(12, 'AVM', 'NotAllowed', 'Message type not allowed', '<span class="fa-stack" title="Automated Voice Messages are not allowed"><i class="fa fa-phone fa-fw fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span>', ''),
(13, 'AVM', 'SCHEDULED', 'AVM: scheduled', '<span class="btn scheduled" title="AVM scheduled"><i class="fa fa-phone fa-fw"></i></span>', ''),
(14, 'AVM', 'SENT', 'AVM in process', '<span class="btn" title="AVM in process, no response" style="padding:5px;background-color:yellow;color:black;"><i class="fa fa-volume-control-phone fa-fw"></i></span>', ''),
(15, 'AVM', 'FAILURE', 'AVM: Message failed', '<span class="btn" title="AVM: Failed.  Check patient''s phone numbers." style="padding:5px;background-color:red;"><i class="fa fa-phone fa-inverse fa-fw"></i></span>', ''),
(16, 'AVM', 'CONFIRMED', 'Confirmed', '<span class="btn" title="Confirmed by AVM" style="padding:5px;background-color:green;"><i class="fa fa-phone fa-inverse fa-fw"></i></span>', ''),
(17, 'AVM', 'CALL', 'Callback Requested by Patient', '<span class="btn btn-success" style="padding:5px;background-color: red;" title="Patient requests Office Call">\r\n<i class="fa fa-flag fa-fw"></i></span>\r\n', ''),
(18, 'AVM', 'Other', 'Extra text sent along...', '<span class="fa-stack fa-lg"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-terminal fa-fw fa-stack-1x fa-inverse"></i></span>', ''),
(19, 'AVM', 'STOP', 'Optout of AVM please', '<span class="btn btn-danger" title="OptOut of Voice Messaging. Demographics updated." aria-label=‚ÄúOptout AVM‚Äù><i class="fa fa-phone" aria-hidden="true"> STOP</i></span>', ''),
(20, 'EMAIL', 'ALLOWED', 'EMAIL:  allowed', '<span title="EMAIL is possible" class="fa fa-envelope-o fa-fw"></span>', ''),
(21, 'EMAIL', 'NotAllowed', 'EMAIL: not allowed', '<span class="fa-stack" title="EMAIL is not possible"><i class="fa fa-envelope-o fa-fw fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span>', ''),
(22, 'EMAIL', 'SCHEDULED', 'EMAIL: scheduled', '<span class=''btn scheduled'' title=''EMAIL scheduled''><i class="fa fa-envelope-o fa-fw"></i></span>', ''),
(23, 'EMAIL', 'SENT', 'EMAIL: sent', '<span class="btn" style="padding:5px;background-color:yellow;color:black;" title="EMAIL Message sent, not opened"><i class="fa fa-envelope-o fa-fw"></i></span>', ''),
(24, 'EMAIL', 'READ', 'EMAIL was opened/read', '<a class="btn" style="padding:5px;background-color:#146abd;" title="E-Mail was read/opened by patient" aria-label="Confirmed via email"><i class="fa fa-envelope-o fa-inverse fa-fw" aria-hidden="true"></i></a>', ''),
(25, 'EMAIL', 'FAILURE', 'EMAIL Message failed', '<span class="btn" title="EMAIL: Failed.  Check patient\'s email address." style="padding:5px;background-color:red;"><i class="fa fa-envelope-o fa-inverse fa-fw"></i></span>', ''),
(26, 'EMAIL', 'CONFIRMED', 'EMAIL Confirmed', '<a class="btn btn-success" style="padding:5px;background-color: green;" title="Confirmed by E-Mail" aria-label="Confirmed via email"><i class="fa fa-envelope-o fa-fw" aria-hidden="true"></i></a>', ''),
(27, 'EMAIL', 'CALL', 'Callback Requested by Patient', '<span class="btn btn-success" style="padding:5px;background-color: red;" title="Patient requests Office Call">\r\n<i class="fa fa-flag fa-fw"></i></span>\r\n', ''),
(28, 'EMAIL', 'Other', 'Extra text sent along...', '<span class="fa-stack fa-lg"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-terminal fa-fw fa-stack-1x fa-inverse fa-fw"></i></span>', ''),
(29, 'EMAIL', 'STOP', 'Optout of EMAIL please', '<span class="btn btn-danger" title="OptOut of EMAIL Messaging. Demographics updated." aria-label=‚ÄúOptout EMAIL‚Äù><i class="fa fa-envelope-o" aria-hidden="true"> STOP</i></span>', ''),
(30, 'POSTCARD', 'SENT', 'POSTCARD sent', '<span class="btn" title="Postcard Sent - in process" style="padding:5px;background-color:yellow;color:black"><i class="fa fa-image fa-fw"></i></span>', ''),
(31, 'POSTCARD', 'READ', 'e-POSTCARD delivered', '<a class="btn" style="padding:5px;background-color:#146abd;" title="e-Postcard was delivered" aria-label="Postcard Delivered"><i class="fa fa-image fa-fw" aria-hidden="true"></i></a>', ''),
(32, 'POSTCARD', 'FAILURE', 'e-POSTCARD failed', '<span class="fa-stack fa-lg" title="Delivery Failure - check Address for this patient"><i class="fa fa-image fa-fw fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span>', ''),
(33, 'POSTCARD', 'SCHEDULED', 'Postcard Campaign Event is scheduled.', '<span class="btn scheduled" title="Postcard Campaign Event is scheduled."><i class="fa fa-image fa-fw"></i></span>', ''),
(36, 'AVM', 'READ', 'AVM delivered', '<span class="btn" title="AVM completed - waiting for manual response" aria-label="AVM Delivered" style="padding:5px;background-color:#146abd;"><i class="fa fa-phone fa-fw" aria-hidden="true"></i></span>', ''),
(37, 'SMS', 'CALLED', 'Callback Completed', '<span class="btn btn-success" style="padding:5px;background-color:#146abd;" title="Patient requests Office Call: COMPLETED">\r\n<i class="fa fa-flag fa-fw"></i></span>\r\n', '');


#IfNotTable medex_outgoing
CREATE TABLE IF NOT EXISTS `medex_outgoing` (
  `msg_uid` int(11) NOT NULL AUTO_INCREMENT,
  `msg_pc_eid` varchar(11) NOT NULL,
  `campaign_uid` int(11) NOT NULL DEFAULT '0',
  `msg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `msg_type` varchar(50) NOT NULL,
  `msg_reply` varchar(50) DEFAULT NULL,
  `msg_extra_text` text,
  PRIMARY KEY (`msg_uid`),
  UNIQUE KEY `msg_eid` (`msg_uid`,`msg_pc_eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
#Endif

#IfNotTable medex_prefs
CREATE TABLE IF NOT EXISTS `medex_prefs` (
  `MedEx_id` int(11) DEFAULT '0',
  `ME_username` varchar(100) DEFAULT NULL,
  `ME_api_key` text,
  `ME_facilities` varchar(50) DEFAULT NULL,
  `ME_providers` varchar(100) DEFAULT NULL,
  `ME_hipaa_default_override` varchar(3) DEFAULT NULL,
  `PHONE_country_code` int(4) NOT NULL DEFAULT '1',
  `MSGS_default_yes` varchar(3) DEFAULT NULL,
  `POSTCARDS_local` varchar(3) DEFAULT NULL,
  `POSTCARDS_remote` varchar(3) DEFAULT NULL,
  `LABELS_local` varchar(3) DEFAULT NULL,
  `LABELS_choice` varchar(50) DEFAULT NULL,
  `combine_time` tinyint(4) DEFAULT NULL,
  `notify` varchar(100) DEFAULT NULL,
  `MedEx_lastupdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `ME_username` (`ME_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#Endif

#IfNotTable medex_recalls
CREATE TABLE IF NOT EXISTS `medex_recalls` (
  `r_ID` int(11) NOT NULL AUTO_INCREMENT,
  `r_PRACTID` int(11) NOT NULL,
  `r_pid` int(11) NOT NULL COMMENT 'PatientID from pat_data',
  `r_eventDate` date NOT NULL COMMENT 'Date of Appt or Recall',
  `r_facility` int(11) NOT NULL,
  `r_provider` int(11) NOT NULL,
  `r_reason` varchar(255) DEFAULT NULL,
  `r_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`r_ID`),
  UNIQUE KEY `r_PRACTID` (`r_PRACTID`,`r_pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=168 ;
#Endif

#IfNotRow background_services name MedEx
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('MedEx', 'MedEx Messaging Service', 29, 0, '2017-05-09 17:39:10', 60, 'start_MedEx', '/library/MedEx/medex_background.php', 100);
#Endif

#IfNotRow2D list_options list_id apptstat option_id AVM
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('apptstat', 'AVM', 'AVM Confirmed', 110, 0, 0, '', 'F0FFE8|0', '', 0, 0, 1, ''),
('apptstat', 'CALL', 'Callback requested', 130, 0, 0, '', 'FFDBE2|5', '', 0, 0, 1, ''),
('apptstat', 'SMS', 'SMS Confirmed', 90, 0, 0, '', 'F0FFE8|0', '', 0, 0, 1, ''),
('apptstat', 'EMAIL', 'EMAIL Confimed', 20, 0, 0, '', 'FFEBE3|0', '', 0, 0, 1, '');
#Endif
