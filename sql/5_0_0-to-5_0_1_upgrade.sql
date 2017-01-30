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

#IfNotRow2D openemr_postcalendar_categories pc_catid 1000 pc_catname Group Therapy
INSERT INTO openemr_postcalendar_categories (`pc_catid`, `pc_catname`, `pc_catcolor`, `pc_recurrspec`, `pc_duration` ,`pc_cattype` , `pc_active` , `pc_seq`)
VALUES ('1000', 'Group Therapy' , '#BFBFBF' , 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', '3600', '3', '1', '90');
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

#IfNotRow2D registry directory group_attendance id 22
INSERT INTO registry (name, state, directory, id, sql_run, unpackaged, date, priority, category, nickname, patient_encounter, therapy_group_encounter) VALUES ('Group Attendance Form', 1, 'group_attendance', 22, 1, 1, '2015-10-15 00:00:00', 0, 'Clinical', '',0,1);
#EndIf

#IfNotRow2D registry directory newGroupEncounter id 23
INSERT INTO registry (name, state, directory, id, sql_run, unpackaged, date, priority, category, nickname, patient_encounter, therapy_group_encounter) VALUES ('New Group Encounter Form', 1, 'newGroupEncounter', 23, 1, 1, '2015-10-15 00:00:00', 0, 'Clinical', '',0,1);
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
