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

--  #IfNotColumnTypeDefault
--    arguments: table_name colname value value2
--    behavior:  If the table table_name does not have a column colname with a data type equal to value and a default equal to value2, then the block will be executed

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

--  #IfRow
--    arguments: table_name colname value
--    behavior:  If the table table_name does have a row where colname = value, the block will be executed.

--  #IfRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfRow3D
--        arguments: table_name colname value colname2 value2 colname3 value3
--        behavior:  If the table table_name does have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfRowIsNull
--    arguments: table_name colname
--    behavior:  If the table table_name does have a row where colname is null, the block will be executed.

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

--  #IfDocumentNamingNeeded
--    desc: populate name field with document names.
--    arguments: none

#IfMissingColumn insurance_companies uuid
ALTER TABLE `insurance_companies` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex insurance_companies uuid
CREATE UNIQUE INDEX `uuid` ON `insurance_companies` (`uuid`);
#EndIf

#IfMissingColumn insurance_data uuid
ALTER TABLE `insurance_data` ADD `uuid` binary(16) DEFAULT NULL;
#EndIf

#IfNotIndex insurance_data uuid
CREATE UNIQUE INDEX `uuid` ON `insurance_data` (`uuid`);
#EndIf

#IfMissingColumn facility weno_id
ALTER TABLE `facility` ADD `weno_id` VARCHAR(10) DEFAULT NULL;
#EndIf

#IfMissingColumn x12_partners x12_gs03
ALTER TABLE `x12_partners` ADD COLUMN `x12_gs03` varchar(15) DEFAULT NULL;
#EndIf

#IfMissingColumn x12_partners x12_submitter_name
ALTER TABLE `x12_partners` ADD COLUMN `x12_submitter_name` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn x12_partners x12_sftp_login
ALTER TABLE `x12_partners` ADD COLUMN `x12_sftp_login` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn x12_partners x12_sftp_pass
ALTER TABLE `x12_partners` ADD COLUMN `x12_sftp_pass` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn x12_partners x12_sftp_host
ALTER TABLE `x12_partners` ADD COLUMN `x12_sftp_host` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn x12_partners x12_sftp_port
ALTER TABLE `x12_partners` ADD COLUMN `x12_sftp_port` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn x12_partners x12_sftp_local_dir
ALTER TABLE `x12_partners` ADD COLUMN `x12_sftp_local_dir` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn x12_partners x12_sftp_remote_dir
ALTER TABLE `x12_partners` ADD COLUMN `x12_sftp_remote_dir` varchar(255) DEFAULT NULL;
#EndIf

#IfNotRow background_services name X12_SFTP
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('X12_SFTP', 'SFTP Claims to X12 Partner Service', 0, 0, '2021-01-18 11:25:10', 1, 'start_X12_SFTP', '/library/billing_sftp_service.php', 100);
#EndIf

#IfNotTable x12_remote_tracker
CREATE TABLE `x12_remote_tracker` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`x12_partner_id` int(11) NOT NULL,
`x12_filename` varchar(255) NOT NULL,
`status` varchar(255) NOT NULL,
`claims` text,
`messages` text,
`created_at` datetime DEFAULT NULL,
`updated_at` datetime DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB;

#IfNotRow2D list_options list_id lists option_id Procedure_Billing
INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value) VALUES ('lists','Procedure_Billing','Procedure Billing',0, 1, 0);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('Procedure_Billing','T','Third-Party',10,1,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('Procedure_Billing','P','Self Pay',20,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('Procedure_Billing','C','Bill Clinic',30,0,1);
#EndIf

#IfMissingColumn procedure_order billing_type
ALTER TABLE `procedure_order` ADD `billing_type` VARCHAR(4) DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_order specimen_fasting
ALTER TABLE `procedure_order` ADD `specimen_fasting` VARCHAR(31) DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_order order_psc
ALTER TABLE `procedure_order` ADD `order_psc` TINYINT(4) DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_order order_abn
ALTER TABLE `procedure_order` ADD `order_abn` VARCHAR(31) NOT NULL DEFAULT 'not_required';
#EndIf

#IfMissingColumn procedure_order collector_id
ALTER TABLE `procedure_order` ADD `collector_id` BIGINT(11) NOT NULL DEFAULT '0';
#EndIf

#IfMissingColumn procedure_order account
ALTER TABLE `procedure_order` ADD `account` VARCHAR(60) DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_order account_facility
ALTER TABLE `procedure_order` ADD `account_facility` int(11) DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_order provider_number
ALTER TABLE `procedure_order` ADD `provider_number` VARCHAR(30) DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_order procedure_order_type
ALTER TABLE `procedure_order` ADD `procedure_order_type` varchar(32) NOT NULL DEFAULT 'laboratory_test';
#EndIf

#IfMissingColumn procedure_order_code procedure_type
ALTER TABLE `procedure_order_code` ADD `procedure_type` VARCHAR(31) DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_order_code transport
ALTER TABLE `procedure_order_code` ADD `transport` VARCHAR(31) DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_type transport
ALTER TABLE `procedure_type` ADD `transport` VARCHAR(31) DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_providers type
ALTER TABLE `procedure_providers` ADD `type` VARCHAR(31) DEFAULT NULL;
#EndIf

#IfMissingColumn procedure_answers procedure_code
ALTER TABLE `procedure_answers` ADD `procedure_code` VARCHAR(31) DEFAULT NULL;
#EndIf

#IfNotRow users username oe-system
INSERT INTO `users`(`username`,`password`,`lname`,`authorized`,`active`) VALUES ('oe-system','NoLogin','System Operation User',0,0);
INSERT INTO `gacl_aro`(`id`, `section_value`, `value`, `order_value`, `name`, `hidden`)
    SELECT max(`id`)+1,'users','oe-system',10,'System Operation User', 0 FROM `gacl_aro`;
INSERT INTO `gacl_groups_aro_map`(`group_id`, `aro_id`)
    VALUES (
        (SELECT `id` FROM `gacl_aro_groups` WHERE parent_id=10 AND value='admin')
        ,(SELECT `id` FROM `gacl_aro` WHERE `section_value` = 'users' AND `value` = 'oe-system')
    );
#EndIf

#IfNotTable export_job
CREATE TABLE `export_job` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) DEFAULT NULL ,
  `user_id` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `status` varchar(40) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `resource_include_time` datetime DEFAULT NULL,
  `output_format` varchar(128) NOT NULL,
  `request_uri` varchar(128) NOT NULL,
  `resources` text,
  `output` text,
  `errors` text,
  `access_token_id` text,
  PRIMARY KEY  (`id`),
  UNIQUE (`uuid`)
) ENGINE=InnoDB COMMENT='fhir export jobs';
#EndIf

#IfNotRow categories name FHIR Export Document
SET @max_rght = (SELECT MAX(rght) FROM categories);
INSERT INTO categories(`id`,`name`, `value`, `parent`, `lft`, `rght`, `aco_spec`) select (select MAX(id) from categories) + 1, 'FHIR Export Document', '', 1, @max_rght, @max_rght + 1, 'admin|super' from categories where name = 'Categories';
UPDATE categories SET rght = rght + 2 WHERE name = 'Categories';
UPDATE categories_seq SET id = (select MAX(id) from categories);
#EndIf

#IfMissingColumn documents date_expires
ALTER TABLE `documents` ADD COLUMN `date_expires` DATETIME DEFAULT NULL;
#EndIf

#IfMissingColumn documents foreign_reference_id
ALTER TABLE `documents` ADD COLUMN `foreign_reference_id` bigint(20) default NULL,
                        ADD COLUMN `foreign_reference_table` VARCHAR(40) default NULL;
ALTER TABLE `documents` ADD KEY `foreign_reference` (`foreign_reference_id`, `foreign_reference_table`);

#IfNotRow background_services name WenoExchange
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('WenoExchange', 'Weno Log Sync', 0, 0, '2021-01-18 11:25:10', 0, 'start_weno', '/library/weno_log_sync.php', 100);
#EndIf

#IfNotRow2D list_options list_id Eye_Defaults_for_GENERAL option_id ODVITREOUS
INSERT INTO list_options (list_id,option_id,title,seq,is_default,option_value,mapping,notes,codes,toggle_setting_1,toggle_setting_2,activity,subtype) VALUES ('Eye_Defaults_for_GENERAL', 'ODVITREOUS', 'clear', 504, 0, 0,'', 'RETINA','', 0, 0, 1,'');
#EndIf

#IfNotRow2D list_options list_id Eye_Defaults_for_GENERAL option_id OSVITREOUS
INSERT INTO list_options (list_id,option_id,title,seq,is_default,option_value,mapping,notes,codes,toggle_setting_1,toggle_setting_2,activity,subtype) VALUES ('Eye_Defaults_for_GENERAL', 'OSVITREOUS', 'clear', 506, 0, 0,'', 'RETINA','', 0, 0, 1,'');
#EndIf

DELETE FROM medex_icons;
INSERT INTO `medex_icons` (`i_UID`, `msg_type`, `msg_status`, `i_description`, `i_html`, `i_blob`) VALUES
(1, 'SMS', 'ALLOWED', '', '<i title="SMS is possible." class="far fa-comment-dots fa-fw"></i>', ''),
(2, 'SMS', 'NotAllowed', '', '<span class="fas fa-stack" title="SMS not possible"><i title="SMS is not possible." class="fas fa-comment-dots fa-fw"></i><i class="fas fa-ban fa-stack-2x text-danger"></i></span>', ''),
(3, 'SMS', 'SCHEDULED', '', '<span class="btn scheduled" title="SMS scheduled"><i class="fas fa-comment-dots fa-fw"></i></span>', ''),
(4, 'SMS', 'SENT', '', '<span class="btn" title="SMS Sent - in process" style="background-color:yellow;"><i aria-hidden="true" class="fas fa-comment-dots fa-fw"></i></span>', ''),
(5, 'SMS', 'READ', '', '<span class="btn" title="SMS Delivered - waiting for response" aria-label="SMS Delivered" style="background-color:#146abd;"><i aria-hidden="true" class="fas fa-comment-dots fa-inverse fa-flip-horizontal fa-fw"></i></span>', ''),
(6, 'SMS', 'FAILED', '', '<span class="btn" title="SMS Failed to be delivered" style="background-color:#ffc4c4;"><i aria-hidden="true" class="fas fa-comment-dots fa-fw"></i></span>', ''),
(7, 'SMS', 'CONFIRMED', '', '<span class="btn" title="Confirmed by SMS" style="background-color:green;"><i aria-hidden="true" class="fas fa-comment-dots fa-inverse fa-flip-horizontal fa-fw"></i></span>', ''),
(8, 'SMS', 'CALL', '', '<span class="btn" style="background-color: red;" title="Patient requests Office Call"><i class="fas fa-flag fa-inverse fa-fw"></i></span>', ''),
(9, 'SMS', 'EXTRA', '', '<span class="btn" title="EXTRA" style="background-color:#000;color:#fff;"><i class="fas fa-terminal fa-fw"></i></span>', ''),
(10, 'SMS', 'STOP', '', '<span class="btn btn-danger fas fa-comment-dots" title="OptOut of SMS Messaging. Demographics updated." aria-label=\'Optout SMS\'> STOP</span>', ''),
(11, 'AVM', 'ALLOWED', '', '<span title="Automated Voice Messages are possible" class="fas fa-phone fa-fw"></span>', ''),
(12, 'AVM', 'NotAllowed', '', '<span class="fas fa-stack" title="Automated Voice Messages are not allowed"><i class="fas fa-phone fa-fw fa-stack-1x"></i><i class="fas fa-ban fa-stack-2x text-danger"></i></span>', ''),
(13, 'AVM', 'SCHEDULED', '', '<span class="btn scheduled" title="AVM scheduled"><i class="fas fa-phone fa-fw"></i></span>', ''),
(14, 'AVM', 'SENT', '', '<span class="btn" title="AVM in process, no response" style="background-color:yellow;"><i class="fas fa-phone-volume fa-reverse fa-fw"></i></span>', ''),
(15, 'AVM', 'FAILED', '', '<span class="btn" title="AVM: Failed.  Check patient\'s phone numbers." style="background-color:#ffc4c4;"><i class="fas fa-phone fa-fw"></i></span>', ''),
(16, 'AVM', 'CONFIRMED', '', '<span class="btn" title="Confirmed by AVM" style="padding:5px;background-color:green;"><i class="fas fa-phone fa-inverse fa-fw"></i></span>', ''),
(17, 'AVM', 'CALL', '', '<span class="btn" style="background-color: red;" title="Patient requests Office Call">\r\n<i class="fas fa-flag fa-inverse fa-fw"></i></span>', ''),
(18, 'AVM', 'Other', '', '<span class="fas fa-stack fa-lg"><i class="fas fa-square fa-stack-2x"></i><i class="fas fa-terminal fa-fw fa-stack-1x fa-inverse"></i></span>', ''),
(19, 'AVM', 'STOP', '', '<span class="btn btn-danger" title="OptOut of Voice Messaging. Demographics updated." aria-label="Optout AVM"><i class="fas fa-phone" aria-hidden="true"> STOP</i></span>', ''),
(20, 'EMAIL', 'ALLOWED', '', '<span title="EMAIL is possible" class="fas fa-envelope fa-fw"></span>', ''),
(21, 'EMAIL', 'NotAllowed', '', '<span class="fas fa-stack" title="EMAIL is not possible"><i class="fas fa-envelope fa-fw fa-stack-1x"></i><i class="fas fa-ban fa-stack-2x text-danger"></i></span>', ''),
(22, 'EMAIL', 'SCHEDULED', '', '<span class="btn scheduled" title="EMAIL scheduled"><i class="fas fa-envelope fa-fw"></i></span>', ''),
(23, 'EMAIL', 'SENT', '', '<span class="btn" style="background-color:yellow;" title="EMAIL Message sent, not opened"><i class="fas fa-envelope fa-fw"></i></span>', ''),
(24, 'EMAIL', 'READ', '', '<span class="btn" style="background-color:#146abd;" title="E-Mail was read/opened by patient" aria-label="Read via email"><i aria-hidden="true" class="fas fa-envelope fa-inverse fa-fw"></i></span>', ''),
(25, 'EMAIL', 'FAILED', '', '<span class="btn" title="EMAIL: Failed.  Check patient\'s email address." style="background-color:#ffc4c4;"><i class="fas fa-envelope fa-fw"></i></span>', ''),
(26, 'EMAIL', 'CONFIRMED', '', '<span class="btn" title="Confirmed by E-Mail" aria-label="Confirmed via email" style="background-color: green;"><i aria-hidden="true" class="fas fa-envelope fa-inverse fa-fw"></i></span>', ''),
(27, 'EMAIL', 'CALL', '', '<span class="btn" style="background-color: red;" title="Patient requests Office Call"><i class="fas fa-flag fa-inverse fa-fw"></i></span>', ''),
(28, 'EMAIL', 'Other', '', '<span class="fas fa-stack fa-lg"><i class="fas fa-square fa-stack-2x"></i><i class="fas fa-terminal fa-fw fa-stack-1x fa-inverse fa-fw"></i></span>', ''),
(29, 'EMAIL', 'STOP', '', '<span class="btn btn-danger" title="OptOut of EMAIL Messaging. Demographics updated." aria-label="Optout EMAIL"><i class="fas fa-envelope-o" aria-hidden="true"> STOP</i></span>', ''),
(30, 'POSTCARD', 'SENT', '', '<span class="btn" title="Postcard Sent - in process" style="padding:5px;background-color:yellow;color:black"><i class="fas fa-image fa-fw"></i></span>', ''),
(31, 'POSTCARD', 'READ', '', '<span class="btn" style="background-color:#146abd;" title="e-Postcard was delivered" aria-label="Postcard Delivered"><i class="fas fa-image fa-fw" aria-hidden="true"></i></span>', ''),
(32, 'POSTCARD', 'FAILED', '', '<span class="fas fa-stack fa-lg" title="Delivery Failure - check Address for this patient"><i class="fas fa-image fa-fw fa-stack-1x"></i><i class="fas fa-ban fa-stack-2x text-danger"></i></span>', ''),
(33, 'POSTCARD', 'SCHEDULED', '', '<span class="btn scheduled" title="Postcard Campaign Event is scheduled."><i class="fas fa-image fa-fw"></i></span>', ''),
(36, 'AVM', 'READ', '', '<span class="btn" title="AVM completed - waiting for manual response" aria-label="AVM Delivered" style="padding:5px;background-color:#146abd;"><i class="fas fa-inverse fa-phone fa-fw" aria-hidden="true"></i></span>', ''),
(37, 'SMS', 'CALLED', '', '<span class="btn" style="background-color:#146abd;" title="Patient requests Office Call: COMPLETED"><i class="fas fa-flag fa-fw"></i></span>', ''),
(38, 'AVM', 'CALLED', '', '<span class="btn" style="background-color:#146abd;" title="Patient requests Office Call: COMPLETED"><i class="fas fa-flag fa-fw"></i></span>    ', ''),
(39, 'EMAIL', 'CALLED', '', '<span class="btn" style="background-color:#146abd;" title="Patient requests Office Call: COMPLETED"><i class="fas fa-flag fa-fw"></i></span>', '');

#IfNotRow2D list_options list_id sex option_id UNK
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, codes ) VALUES ('sex', 'UNK', 'Unknown', 10, 0, 'HL7:UNK');
#EndIf

#IfNotRow3D list_options list_id sex option_id Female codes HL7:F
UPDATE `list_options` SET `codes` = 'HL7:F' WHERE `list_id` = 'sex' AND `option_id` = 'Female';
#EndIf

#IfNotRow3D list_options list_id sex option_id Male codes HL7:M
UPDATE `list_options` SET `codes` = 'HL7:M' WHERE `list_id` = 'sex' AND `option_id` = 'Male';
#EndIf

#IfMissingColumn patient_data sexual_orientation
ALTER TABLE patient_data ADD sexual_orientation TEXT;
#EndIf

#IfMissingColumn patient_data gender_identity
ALTER TABLE patient_data ADD gender_identity TEXT;
#EndIf

#IfNotRow2D list_options list_id lists option_id sexual_orientation
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`) VALUES ('lists', 'sexual_orientation', 'Sexual Orientation', '13');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('sexual_orientation','20430005','Straight or heterosexual',10,0,0,'SNOMED:20430005');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('sexual_orientation','38628009','Lesbian, gay or homosexual',20,0,0,'SNOMED:38628009');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('sexual_orientation','42035005','Bisexual',30,0,0,'SNOMED:42035005');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('sexual_orientation','comment_OTH','Something else, please describe',40,0,0,'HL7:OTH');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('sexual_orientation','UNK','Don\'t know',50,0,0,'HL7:UNK');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('sexual_orientation','ASKU','Choose not to disclose',60,0,0,'HL7:ASKU');
#EndIf

#IfNotRow2D list_options list_id lists option_id gender_identity
INSERT INTO list_options (`list_id`, `option_id`, `title`, `seq`) VALUES ('lists', 'gender_identity', 'Gender Identity', '1');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('gender_identity','446151000124109','Identifies as Male',10,0,0,'SNOMED:446151000124109');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('gender_identity','446141000124107','Identifies as Female',20,0,0,'SNOMED:446141000124107');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('gender_identity','407377005','Female-to-Male (FTM)/Transgender Male/Trans Man',30,0,0,'SNOMED:407377005');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('gender_identity','407376001','Male-to-Female (MTF)/Transgender Female/Trans Woman',40,0,0,'SNOMED:407376001');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('gender_identity','446131000124102','Genderqueer, neither exclusively male nor female',50,0,0,'SNOMED:446131000124102');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('gender_identity','comment_OTH','Additional gender category or other, please specify',60,0,0,'HL7:OTH');
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `codes`) VALUES ('gender_identity','ASKU','Choose not to disclose',70,0,0,'HL7:ASKU');
#EndIf

#IfNotRow2D layout_options form_id DEM field_id gender_identity
SET @group_id = (SELECT group_id FROM layout_options WHERE field_id='sex' AND form_id='DEM');
SET @backup_group_id = (SELECT group_id FROM layout_options WHERE field_id='DOB' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_id = IFNULL(@group_id,@backup_group_id) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'gender_identity', IFNULL(@group_id,@backup_group_id), 'Gender Identity', @seq+1, 46, 1, 0, 100, 'gender_identity' , 1 , 1 , '' , 'N' , 'Gender Identity', 0);
#EndIf

#IfNotRow2D layout_options form_id DEM field_id sexual_orientation
SET @group_id = (SELECT group_id FROM layout_options WHERE field_id='sex' AND form_id='DEM');
SET @backup_group_id = (SELECT group_id FROM layout_options WHERE field_id='DOB' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_id = IFNULL(@group_id,@backup_group_id) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'sexual_orientation', IFNULL(@group_id,@backup_group_id), 'Sexual Orientation', @seq+1, 46, 1, 0, 100, 'sexual_orientation', 1, 1, '' ,'N' ,'Sexual Orientation', 0);
#EndIf

#IfMissingColumn users google_signin_email
ALTER TABLE `users` ADD COLUMN `google_signin_email` VARCHAR(255) UNIQUE DEFAULT NULL;
#EndIf

#IfNotRow background_services name UUID_Service
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('UUID_Service', 'Automated UUID Creation Service', 1, 0, '2021-01-18 11:25:10', 240, 'autoPopulateAllMissingUuids', '/library/uuid.php', 100);
#EndIf

#IfNotRow codes code_text SARS-COV-2 (COVID-19) vaccine, vector non-replicating, recombinant spike protein-Ad26, preservative free, 0.5 mL
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 (COVID-19) vaccine, vector non-replicating, recombinant spike protein-Ad26, preservative free, 0.5 mL", "COVID-19 vaccine, vector-nr, rS-Ad26, PF, 0.5 mL", 212, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow2D list_options list_id lists option_id Document_Template_Categories
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`) VALUES ('lists','Document_Template_Categories','Document Template Categories',0,1,0,'',NULL,'',0,0,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`) VALUES ('Document_Template_Categories','repository','Repository',1,1,0,'','','',0,0,1);

#IfMissingColumn layout_group_properties grp_save_close
ALTER TABLE `layout_group_properties` ADD COLUMN `grp_save_close` tinyint(1) not null default 0;
#EndIf

#IfMissingColumn layout_group_properties grp_init_open
ALTER TABLE `layout_group_properties` ADD COLUMN `grp_init_open` tinyint(1) not null default 0;
UPDATE layout_group_properties AS p, layout_options AS o SET p.grp_init_open = 1 WHERE
  o.form_id = p.grp_form_id AND o.group_id = p.grp_group_id AND o.uor > 0 AND o.edit_options LIKE '%I%';
UPDATE layout_group_properties AS p SET p.grp_init_open = 1 WHERE p.grp_group_id = '1' AND
  (SELECT count(*) FROM layout_options AS o WHERE o.form_id = p.grp_form_id AND o.uor > 0 AND o.edit_options LIKE '%I%') = 0;
#EndIf

#IfMissingColumn layout_group_properties grp_last_update
ALTER TABLE `layout_group_properties` ADD COLUMN `grp_last_update` timestamp NULL;
#EndIf

#---------- Support for Referrals section of LBFs. ----------#
#IfMissingColumn layout_group_properties grp_referrals
ALTER TABLE `layout_group_properties` ADD COLUMN `grp_referrals` tinyint(1) not null default 0;
#EndIf

#IfMissingColumn drug_sales trans_type
ALTER TABLE drug_sales
  ADD trans_type tinyint NOT NULL DEFAULT 1 COMMENT '1=sale, 2=purchase, 3=return, 4=transfer, 5=adjustment';
UPDATE drug_sales SET trans_type = 4 WHERE pid = 0 AND xfer_inventory_id != 0;
UPDATE drug_sales SET trans_type = 5 WHERE trans_type = 1 AND pid = 0 AND fee = 0;
UPDATE drug_sales SET trans_type = 2 WHERE trans_type = 1 AND pid = 0 AND quantity >= 0;
UPDATE drug_sales SET trans_type = 3 WHERE trans_type = 1 AND pid = 0;
#EndIf

#IfMissingColumn ar_activity post_date
ALTER TABLE ar_activity
  ADD post_date date DEFAULT NULL COMMENT 'Posting date if specified at payment time';
UPDATE ar_activity SET post_date = post_time;
#EndIf

#IfMissingColumn form_encounter shift
ALTER TABLE form_encounter ADD shift varchar(31) NOT NULL DEFAULT '';
#EndIf

#IfNotRow2D list_options list_id lists option_id shift
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('lists','shift','Shifts', 1, 0);
#EndIf

#IfMissingColumn form_encounter voucher_number
ALTER TABLE form_encounter ADD voucher_number varchar(255) NOT NULL DEFAULT '' COMMENT 'also called referral number';
#EndIf

#IfMissingColumn billing chargecat
ALTER TABLE `billing` ADD COLUMN `chargecat` varchar(31) default '';
#EndIf

#IfMissingColumn drug_sales chargecat
ALTER TABLE `drug_sales` ADD COLUMN `chargecat` varchar(31) default '';

#IfMissingColumn users_facility warehouse_id
ALTER TABLE `users_facility` ADD COLUMN `warehouse_id` varchar(31) NOT NULL default '';
ALTER TABLE `users_facility` DROP PRIMARY KEY, ADD PRIMARY KEY (`tablename`,`table_id`,`facility_id`,`warehouse_id`);
#EndIf

#IfNotColumnType drugs form varchar(31)
ALTER TABLE `drugs` CHANGE `form`  `form`  varchar(31) NOT NULL default '0';
#EndIf
#IfNotColumnType drugs unit varchar(31)
ALTER TABLE `drugs` CHANGE `unit`  `unit`  varchar(31) NOT NULL default '0';
#EndIf
#IfNotColumnType drugs route varchar(31)
ALTER TABLE `drugs` CHANGE `route` `route` varchar(31) NOT NULL default '0';
#EndIf

#IfMissingColumn drug_templates pkgqty
ALTER TABLE `drug_templates` ADD COLUMN `pkgqty` float NOT NULL DEFAULT 1.0 COMMENT 'Number of product items per template item';
#EndIf

#IfMissingColumn voids reason
ALTER TABLE `voids` ADD COLUMN `reason` VARCHAR(31) default '';
#EndIf

#IfMissingColumn voids notes
ALTER TABLE `voids` ADD COLUMN `notes` VARCHAR(255) default '';
#EndIf

#IfNotRow2D list_options list_id lists option_id void_reasons
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('lists','void_reasons','Void Reasons',1,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('void_reasons','one'  ,'Reason 1',10,1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('void_reasons','two'  ,'Reason 2',20,0);
#EndIf

#IfNotIndex log patient_id
CREATE INDEX `patient_id` ON `log` (`patient_id`);
#EndIf

#IfNotRow2D list_options list_id lists option_id paymethod
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('lists','paymethod','Payment Methods', 1,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('paymethod','Cash' ,'Cash' ,10,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('paymethod','Check','Check',20,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('paymethod','MC'   ,'MC'   ,30,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('paymethod','VISA' ,'VISA' ,40,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('paymethod','AMEX' ,'AMEX' ,50,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('paymethod','DISC' ,'DISC' ,60,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`) VALUES ('paymethod','Other','Other',70,0);
#EndIf

#IfNotRow2D issue_types category default type medical_device
INSERT INTO `issue_types` (`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('35','default','medical_device','Medical Devices','Device','I','0','0');
#EndIf

#IfMissingColumn lists udi
ALTER TABLE `lists` ADD COLUMN `udi` varchar(255) default NULL;
#EndIf

#IfMissingColumn lists udi_data
ALTER TABLE `lists` ADD COLUMN `udi_data` text;
#EndIf

#IfNotRow globals gl_name gbl_fac_warehouse_restrictions
INSERT INTO `globals` (gl_name, gl_index, gl_value) SELECT 'gbl_fac_warehouse_restrictions', gl_index, gl_value
  FROM globals WHERE gl_name = 'restrict_user_facility';
#EndIf

#IfNotRow2D list_options list_id lists option_id chargecats
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`)
  VALUES ('lists','chargecats','Customers', 1,0);
#EndIf

#IfNotRow2D list_options list_id lists option_id Clinical_Note_Type
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('lists','Clinical_Note_Type','Clinical Note Type',0,1,0,'',NULL,'',0,0,1,'',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Clinical_Note_Type','progress_note','Progress Note',10,0,0,'','LOINC:11506-3','',0,0,1,'',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Clinical_Note_Type','nurse_note','Nurse Note',20,0,0,'','LOINC:34746-8','',0,0,1,'',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Clinical_Note_Type','history_physical','History & Physical',30,0,0,'','LOINC:34117-2','',0,0,1,'',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Clinical_Note_Type','general_note','General Note',40,0,0,'','LOINC:34109-9','',0,0,1,'',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Clinical_Note_Type','discharge_summary','Discharge Summary Note',50,0,0,'','LOINC:18842-5','',0,0,1,'',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Clinical_Note_Type','procedure_note','Procedure Note',60,0,0,'','LOINC:28570-0','',0,0,1,'',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Clinical_Note_Type','consultation_note','Consultation Note',70,0,0,'','LOINC:81222-2','',0,0,1,'',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Clinical_Note_Type','imaging_narrative','Imaging Narrative',80,0,0,'','LOINC:28570-0','',0,0,1,'',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Clinical_Note_Type','laboratory_report_narrative','Laboratory Report Narrative',90,0,0,'','','',0,0,1,'',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Clinical_Note_Type','pathology_report_narrative','Pathology Report Narrative',100,0,0,'','','',0,0,1,'',1);
#EndIf

#---------- Migrate old form_clinical_notes to form_clinic_note if it is installed ----------#
#IfColumn form_clinical_notes followup_timing
ALTER TABLE `form_clinical_notes` RENAME TO `form_clinic_note`;
UPDATE `forms` SET `form_name` = 'Clinic Note' WHERE `form_name` = 'Clinical Notes';
UPDATE `forms` SET `formdir` = 'clinic_note' WHERE `formdir` = 'clinical_notes';
UPDATE `registry` SET `name` = 'Clinic Note' WHERE `name` LIKE 'Clinical Notes%' AND `directory` = 'clinical_notes';
UPDATE `registry` SET `directory` = 'clinic_note' WHERE `directory` = 'clinical_notes';
#EndIf

#IfNotTable form_clinical_notes
CREATE TABLE `form_clinical_notes` (
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
    `external_id` VARCHAR(30) DEFAULT NULL,
    `clinical_notes_type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB;
INSERT INTO `registry` (`name`, `state`, `directory`, `sql_run`, `unpackaged`, `date`, `priority`, `category`, `nickname`, `patient_encounter`, `therapy_group_encounter`, `aco_spec`) VALUES ('Clinical Notes', 1, 'clinical_notes', 1, 1, '2015-09-09 00:00:00', 0, 'Clinical', '', 1, 0, 'encounters|notes');
#EndIf

#IfNotRow ccda_components ccda_components_field medical_devices
INSERT INTO `ccda_components` (`ccda_components_id`, `ccda_components_field`, `ccda_components_name`, `ccda_type`) VALUES
(23, 'medical_devices', 'Medical Devices', 1),
(24, 'goals', 'Goals', 1);
#EndIf

#IfNotRow ccda_sections ccda_sections_field medical_devices
INSERT INTO `ccda_sections` (`ccda_sections_id`, `ccda_components_id`, `ccda_sections_field`, `ccda_sections_name`, `ccda_sections_req_mapping`) VALUES
(46, 3, 'medical_devices', 'Medical Devices', 0),
(47, 3, 'goals', 'Goals', 0);
#EndIf

#IfNotRow2D list_options list_id lists option_id Care_Team_Status
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('lists', 'Care_Team_Status', 'Care Team Status', 1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Care_Team_Status', 'active', 'Active', 10, 0, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Care_Team_Status', 'inactive', 'Inactive', 20, 0, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Care_Team_Status', 'suspended', 'Suspended', 30, 0, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Care_Team_Status', 'proposed', 'Proposed', 40, 0, 0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Care_Team_Status', 'entered-in-error', 'Entered In Error', 50, 0, 0);
#EndIf

#IfMissingColumn patient_data birth_fname
ALTER TABLE `patient_data` ADD `birth_fname` TEXT;
#EndIf

#IfMissingColumn patient_data birth_lname
ALTER TABLE `patient_data` ADD `birth_lname` TEXT;
#EndIf

#IfMissingColumn patient_data birth_mname
ALTER TABLE `patient_data` ADD `birth_mname` TEXT;
#EndIf

#IfNotRow2D layout_options form_id DEM field_id birth_fname
SET @group_id = (SELECT group_id FROM layout_options WHERE field_id='fname' AND form_id='DEM');
SET @backup_group_id = (SELECT group_id FROM layout_options WHERE field_id='lname' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_id = IFNULL(@group_id,@backup_group_id) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'birth_fname', IFNULL(@group_id,@backup_group_id), 'Birth Name', @seq+1, 2, 1, 10, 63, '', 1, 1, '', 'C', 'Birth First Name', 0);
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'birth_mname', IFNULL(@group_id,@backup_group_id), '', @seq+2, 2, 1, 2, 63, '', 0, 0, '', 'C', 'Birth Middle Name', 0);
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'birth_lname', IFNULL(@group_id,@backup_group_id), '', @seq+3, 2, 1, 10, 63, '', 0, 0, '', 'C', 'Birth Last Name', 0);
#EndIf

#IfNotRow2D list_options list_id Clinical_Note_Type option_id evaluation_note
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Clinical_Note_Type','evaluation_note','Evaluation Note',5,0,0,'','LOINC:51848-0','',0,0,1,'',1);
#EndIf

#IfNotRow2D list_options list_id Plan_of_Care_Type option_id goal
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `activity`, `toggle_setting_1`, `toggle_setting_2`, `subtype`) VALUES('Plan_of_Care_Type','goal','Goal','6','0','0','','GOL','','1','0','0','');
#EndIf

#IfNotIndex audit_details audit_master_id
CREATE INDEX `audit_master_id` ON `audit_details` (`audit_master_id`);
#EndIf

#IfNotRow2D list_options list_id Plan_of_Care_Type option_id health_concern
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `activity`, `toggle_setting_1`, `toggle_setting_2`, `subtype`) VALUES('Plan_of_Care_Type','health_concern','Health Concern','7','0','0','','ACT','','1','0','0','');
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `activity`, `toggle_setting_1`, `toggle_setting_2`, `subtype`) VALUES('Plan_of_Care_Type','medication','Medication','8','0','0','','INT','','1','0','0','');
#EndIf

#IfMissingColumn form_vitals oxygen_flow_rate
ALTER TABLE `form_vitals` ADD `oxygen_flow_rate` FLOAT(5,2) NULL DEFAULT '0.00';
#EndIf

#IfMissingColumn form_clinical_notes note_related_to
ALTER TABLE `form_clinical_notes` ADD `note_related_to` TEXT COMMENT 'Reference to lists id for note relationships(json)';
#EndIf

#IfMissingColumn form_care_plan note_related_to
ALTER TABLE `form_care_plan` ADD `note_related_to` TEXT COMMENT 'Reference to lists id for note relationships(json)';
#EndIf

#IfNotTable insurance_type_codes
CREATE TABLE `insurance_type_codes` (
  `id` int(2) NOT NULL,
  `type` varchar(60) NOT NULL,
  `claim_type` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('1','Other HCFA','16');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('2','Medicare Part B','MB');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('3','Medicaid','MC');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('4','ChampUSVA','CH');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('5','ChampUS','CH');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('6','Blue Cross Blue Shield','BL');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('7','FECA','16');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('8','Self Pay','09');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('9','Central Certification','10');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('10','Other Non-Federal Programs','11');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('11','Preferred Provider Organization (PPO)','12');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('12','Point of Service (POS)','13');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('13','Exclusive Provider Organization (EPO)','14');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('14','Indemnity Insurance','15');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('15','Health Maintenance Organization (HMO) Medicare Risk','16');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('16','Automobile Medical','AM');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('17','Commercial Insurance Co.','CI');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('18','Disability','DS');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('19','Health Maintenance Organization','HM');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('20','Liability','LI');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('21','Liability Medical','LM');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('22','Other Federal Program','OF');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('23','Title V','TV');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('24','Veterans Administration Plan','VA');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('25','Workers Compensation Health Plan','WC');
INSERT INTO insurance_type_codes(`id`,`type`,`claim_type`) VALUES ('26','Mutually Defined','ZZ');
#EndIf

#IfNotColumnTypeDefault insurance_companies alt_cms_id varchar(15) NULL
ALTER TABLE `insurance_companies` MODIFY `alt_cms_id` varchar(15) NULL;
#EndIf

#IfMissingColumn form_vitals uuid
ALTER TABLE `form_vitals` ADD `uuid` binary(16) DEFAULT NULL AFTER `id`;
#EndIf

#IfNotIndex form_vitals uuid
CREATE UNIQUE INDEX `uuid` ON `form_vitals` (`uuid`);
#EndIf

#IfMissingColumn uuid_mapping resource_path
ALTER TABLE `uuid_mapping` ADD `resource_path` VARCHAR(255) DEFAULT NULL;
#EndIf

#IfMissingColumn form_vitals ped_weight_height
ALTER TABLE `form_vitals` ADD `ped_weight_height` FLOAT(4,1) DEFAULT '0.00';
#EndIf

#IfMissingColumn form_vitals ped_bmi
ALTER TABLE `form_vitals` ADD `ped_bmi` FLOAT(4,1) DEFAULT '0.00';
#EndIf

#IfMissingColumn form_vitals ped_head_circ
ALTER TABLE `form_vitals` ADD `ped_head_circ` FLOAT(4,1) DEFAULT '0.00';
#EndIf

#IfMissingColumn history_data uuid
ALTER TABLE `history_data` ADD `uuid` binary(16) DEFAULT NULL AFTER `id`;
#EndIf

#IfNotIndex history_data uuid
CREATE UNIQUE INDEX `uuid` ON `history_data` (`uuid`);
#EndIf

#IfMissingColumn form_clinical_notes form_id
ALTER TABLE `form_clinical_notes` CHANGE `id` `form_id` bigint(20) NOT NULL;
ALTER TABLE `form_clinical_notes` ADD COLUMN `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
#EndIf

#IfMissingColumn form_clinical_notes uuid
ALTER TABLE `form_clinical_notes` ADD `uuid` binary(16) DEFAULT NULL AFTER `id`;
#EndIf

#IfNotIndex form_clinical_notes uuid
CREATE UNIQUE INDEX `uuid` ON `form_clinical_notes` (`uuid`);
#EndIf

#IfMissingColumn documents uuid
ALTER TABLE `documents` ADD `uuid` binary(16) DEFAULT NULL AFTER `id`;
#EndIf

#IfNotIndex documents uuid
CREATE UNIQUE INDEX `uuid` ON `documents` (`uuid`);
#EndIf

#IfNotRow list_options list_id Clinical_Note_Category
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`
    , `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`)
VALUES
       ('lists','Clinical_Note_Category','Clinical Note Category',1,0,0,'','',0,0,0,1,'',1);
INSERT INTO `list_options`(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`
    , `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`)
VALUES
    ('Clinical_Note_Category','cardiology','Cardiology',10,0,0,'','LOINC:LP29708-2',0,0,0,1,'',1,NOW()),
    ('Clinical_Note_Category','pathology','Pathology',20,0,0,'','LOINC:LP7839-6',0,0,0,1,'',1,NOW()),
    ('Clinical_Note_Category','radiology','Radiology',30,0,0,'','LOINC:LP29684-5',0,0,0,1,'',1,NOW());
#EndIf

#IfMissingColumn form_clinical_notes clinical_notes_category
ALTER TABLE `form_clinical_notes` ADD COLUMN `clinical_notes_category` varchar(100) DEFAULT NULL;
#EndIf


#IfRow3D list_options list_id Clinical_Note_Type option_id consultation_note notes LOINC:81222-2
UPDATE `list_options` SET notes="LOINC:11488-4" WHERE list_id="Clinical_Note_Type" AND option_id="consultation_note" AND notes="LOINC:81222-2";
#EndIf

#IfMissingColumn procedure_report uuid
ALTER TABLE `procedure_report` ADD `uuid` binary(16) DEFAULT NULL AFTER `procedure_report_id`;
#EndIf

#IfNotIndex procedure_report uuid
CREATE UNIQUE INDEX `uuid` ON `procedure_report` (`uuid`);
#EndIf

#IfMissingColumn procedure_providers uuid
ALTER TABLE `procedure_providers` ADD `uuid` binary(16) DEFAULT NULL AFTER `ppid`;
#EndIf

#IfNotIndex procedure_providers uuid
CREATE UNIQUE INDEX `uuid` ON `procedure_providers` (`uuid`);
#EndIf

#IfMissingColumn patient_data dupscore
ALTER TABLE `patient_data` ADD COLUMN `dupscore` INT NOT NULL default -9;
#EndIf

#IfMissingColumn procedure_type procedure_type_name
ALTER TABLE `procedure_type` ADD `procedure_type_name` VARCHAR(64) NULL;
#EndIf

#IfNotIndex external_procedures ep_pid
CREATE INDEX `ep_pid` ON `external_procedures` (`ep_pid`);
#EndIf

#IfNotIndex users abook_type
CREATE INDEX `abook_type` ON `users` (`abook_type`);
#EndIf


#IfNotIndex procedure_type ptype_procedure_code
ALTER TABLE `procedure_type` ADD INDEX `ptype_procedure_code`(`procedure_code`);
#EndIf

#IfNotTable form_vital_details
CREATE TABLE `form_vital_details` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`form_id` bigint(20) NOT NULL COMMENT 'FK to vital_forms.id',
`vitals_column` varchar(64) NOT NULL COMMENT 'Column name from form_vitals',
`interpretation_list_id` varchar(100) DEFAULT NULL COMMENT 'FK to list_options.list_id for observation_interpretation',
`interpretation_option_id` varchar(100) DEFAULT NULL COMMENT 'FK to list_options.option_id for observation_interpretation',
`interpretation_codes` varchar(255) DEFAULT NULL COMMENT 'Archived original codes value from list_options observation_interpretation',
`interpretation_title` varchar(255) DEFAULT NULL COMMENT 'Archived original title value from list_options observation_interpretation',
PRIMARY KEY (`id`),
KEY `fk_form_id` (`form_id`),
KEY `fk_list_options_id` (`interpretation_list_id`, `interpretation_option_id`)
) ENGINE=InnoDB COMMENT='Detailed information of each vital_forms observation column';
#EndIf

#IfNotRow2D list_options list_id lists option_id vitals-interpretation
INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value) VALUES ('lists','vitals-interpretation','Observation Interpretation',0, 1, 0);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('vitals-interpretation','N','Normal',10,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('vitals-interpretation','H','High',20,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('vitals-interpretation','L','Low',30,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('vitals-interpretation','A','Abnormal',40,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('vitals-interpretation','AA','Critical abnormal',50,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('vitals-interpretation','HH','Critical high',60,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('vitals-interpretation','LL','Critical low',70,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('vitals-interpretation','HU','Significantly high',80,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('vitals-interpretation','LU','Significantly low',90,0,1);
#EndIf

#IfRow2D list_options list_id page_validation option_id messages#new_note
UPDATE `list_options` SET `notes` = '{"form_datetime":{"futureDate":{"message": "Must be future date"}}, "reply_to":{"presence": {"message": "Please choose a patient"}}, "note":{"presence": {"message": "Please enter a note"}}}' where option_id = 'messages#new_note';
#EndIf

#IfNotRow2D list_options list_id lists option_id discharge-disposition
INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value) VALUES ('lists','discharge-disposition','Discharge Disposition',0, 1, 0);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('discharge-disposition','home','Home',10,1,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('discharge-disposition','alt-home','Alternative Home',20,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('discharge-disposition','other-hcf','Other healthcare facility',30,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('discharge-disposition','hosp','Hospice',40,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('discharge-disposition','long','Long-term care',50,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('discharge-disposition','aadvice','Left against advice',60,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('discharge-disposition','exp','Expired',70,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('discharge-disposition','psy','Psychiatric hospital',80,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('discharge-disposition','rehab','Rehabilitation',90,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('discharge-disposition','snf','Skilled nursing facility',100,0,1);
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity) VALUES ('discharge-disposition','oth','Other',110,0,1);
#EndIf

#IfMissingColumn form_encounter discharge_disposition
ALTER TABLE `form_encounter` ADD COLUMN `discharge_disposition` varchar(100) NULL DEFAULT NULL;
#EndIf
#IfMissingColumn form_vitals inhaled_oxygen_concentration
ALTER TABLE `form_vitals` ADD `inhaled_oxygen_concentration` float(4,1) DEFAULT '0.00';
#EndIf

UPDATE `list_options` SET `notes` = 'LOINC:11502-2' WHERE `list_options`.`list_id` = 'Clinical_Note_Type' AND `list_options`.`option_id` = 'laboratory_report_narrative';

#IfMissingColumn patient_data care_team_status
ALTER TABLE patient_data ADD COLUMN care_team_status TEXT;
UPDATE `patient_data` SET `care_team_status` = 'active' WHERE `care_team_status` = '' OR `care_team_status` IS NULL;
#EndIf

#IfNotTable patient_history
CREATE TABLE `patient_history` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT
    , `uuid` BINARY(16) NULL
    , `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    , `care_team_provider` TEXT
    , `care_team_facility` TEXT
    , `pid` BIGINT(20) NOT NULL
    , PRIMARY KEY (`id`)
    , UNIQUE `uuid` (`uuid`)
) ENGINE = InnoDB;
#EndIf

#IfNotRow2D layout_options form_id DEM field_id care_team_status
SET @group_id = (SELECT group_id FROM layout_options WHERE field_id='care_team_provider' AND form_id='DEM');
SET @backup_group_id = (SELECT group_id FROM layout_options WHERE field_id='DOB' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_id = COALESCE(@group_id,@backup_group_id) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`)
        VALUES ('DEM', 'care_team_status', COALESCE(@group_id,@backup_group_id), 'Care Team Status', @seq+1, 1, 1, 0, 0, 'Care_Team_Status', 1, 1, '', '', 'Indicates whether the care team is current , represents future intentions or is now a historical record.', 0);
#EndIf

#IfNotRow3D list_options list_id Care_Team_Status option_id entered-in-error notes The care team should have never existed.
UPDATE `list_options` SET `notes` = 'This list originally comes from http://hl7.org/fhir/R4/valueset-care-team-status.html' WHERE `list_id` = 'lists' AND `option_id` = 'Care_Team_Status';
UPDATE `list_options` SET `seq` = 10, `notes` = 'The care team has been drafted and proposed, but not yet participating in the coordination and delivery of patient care.' WHERE `list_id` = 'Care_Team_Status' AND `option_id` = 'proposed';
UPDATE `list_options` SET `is_default` = 1, `seq` = 20, `notes` = 'The care team is currently participating in the coordination and delivery of care.' WHERE `list_id` = 'Care_Team_Status' AND `option_id` = 'active';
UPDATE `list_options` SET `seq` = 30, `notes` = 'The care team is temporarily on hold or suspended and not participating in the coordination and delivery of care.' WHERE `list_id` = 'Care_Team_Status' AND `option_id` = 'suspended';
UPDATE `list_options` SET `seq` = 40, `notes` = 'The care team was, but is no longer, participating in the coordination and delivery of care.' WHERE `list_id` = 'Care_Team_Status' AND `option_id` = 'inactive';
UPDATE `list_options` SET `seq` = 50, `notes` = 'The care team should have never existed.' WHERE `list_id` = 'Care_Team_Status' AND `option_id` = 'entered-in-error';
#EndIf

#IfMissingColumn prescriptions drug_dosage_instructions
ALTER TABLE `prescriptions` ADD COLUMN drug_dosage_instructions longtext COMMENT 'Medication dosage instructions';
#EndIf

#IfMissingColumn prescriptions usage_category
ALTER TABLE `prescriptions` ADD COLUMN `usage_category` VARCHAR(100) NULL COMMENT 'option_id in list_options.list_id=medication-usage-category';
ALTER TABLE `prescriptions` ADD COLUMN `usage_category_title` VARCHAR(255) NOT NULL COMMENT 'title in list_options.list_id=medication-usage-category';
ALTER TABLE `prescriptions` ADD COLUMN `request_intent` VARCHAR(100) NULL COMMENT 'option_id in list_options.list_id=medication-request-intent';
ALTER TABLE `prescriptions` ADD COLUMN `request_intent_title` VARCHAR(255) NOT NULL COMMENT 'title in list_options.list_id=medication-request-intent';
#EndIf

#IfNotTable lists_medication
CREATE TABLE `lists_medication` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT
    , `list_id` BIGINT(20) NULL COMMENT 'FK Reference to lists.id'
    , `drug_dosage_instructions` LONGTEXT COMMENT 'Free text dosage instructions for taking the drug'
    , `usage_category` VARCHAR(100) NULL COMMENT 'option_id in list_options.list_id=medication-usage-category'
    , `usage_category_title` VARCHAR(255) NOT NULL COMMENT 'title in list_options.list_id=medication-usage-category'
    , `request_intent` VARCHAR(100) NULL COMMENT 'option_id in list_options.list_id=medication-request-intent'
    , `request_intent_title` VARCHAR(255) NOT NULL COMMENT 'title in list_options.list_id=medication-request-intent'
    , PRIMARY KEY (`id`)
    , INDEX `lists_med_usage_category_idx`(`usage_category`)
    , INDEX `lists_med_request_intent_idx`(`request_intent`)
    , INDEX `lists_medication_list_idx` (`list_id`)
) ENGINE = InnoDB COMMENT = 'Holds additional data about patient medications.';
#EndIf

#IfNotRow2D list_options list_id lists option_id medication-usage-category
INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value, notes) VALUES ('lists','medication-usage-category','Medication Usage Category',0, 1, 0, 'Values taken from http://hl7.org/fhir/R4/valueset-medicationrequest-category.html');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-usage-category','inpatient','Inpatient',10,0,1, 'Includes requests for medications to be administered or consumed in an inpatient or acute care setting');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-usage-category','outpatient','Outpatient',20,0,1, 'Includes requests for medications to be administered or consumed in an outpatient setting (for example, Emergency Department, Outpatient Clinic, Outpatient Surgery, Doctor''s office)');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-usage-category','community','Home/Community',30,1,1, 'Includes requests for medications to be administered or consumed by the patient in their home (this would include long term care or nursing homes, hospices, etc.)');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-usage-category','discharge','Discharge',40,0,1, 'Includes requests for medications created when the patient is being released from a facility');
#EndIf

#IfNotRow2D list_options list_id lists option_id medication-request-intent
INSERT INTO list_options (list_id,option_id,title, seq, is_default, option_value, notes) VALUES ('lists','medication-request-intent','Medication Request Intent',0, 1, 0, 'Values taken from http://hl7.org/fhir/R4/valueset-medicationrequest-intent.html');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-request-intent','proposal','Proposal',10,0,1, 'The request is a suggestion made by someone/something that doesn''t have an intention to ensure it occurs and without providing an authorization to act.');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-request-intent','plan','Plan',20,0,1, 'The request represents an intention to ensure something occurs without providing an authorization for others to act.');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-request-intent','order','Order',30,1,1, 'The request represents a request/demand and authorization for action');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-request-intent','original-order','Original Order',40,0,1, 'The request represents the original authorization for the medication request.');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-request-intent','reflex-order','Reflex Order',50,0,1, 'The request represents an automatically generated supplemental authorization for action based on a parent authorization together with initial results of the action taken against that parent authorization.');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-request-intent','filler-order','Filler Order',60,0,1, 'The request represents the view of an authorization instantiated by a fulfilling system representing the details of the fulfiller''s intention to act upon a submitted order.');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-request-intent','instance-order','Instance Order',70,0,1, 'The request represents an instance for the particular order, for example a medication administration record.');
INSERT INTO list_options (list_id,option_id,title,seq,is_default,activity, notes) VALUES ('medication-request-intent','option','Option',80,0,1, 'The request represents a component or option for a RequestGroup that establishes timing, conditionality and/or other constraints among a set of requests.');
#EndIf

#IfMissingColumn api_token revoked
ALTER TABLE `api_token` ADD COLUMN `revoked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=revoked,0=not revoked';
#EndIf

#IfNotTable api_refresh_token
CREATE TABLE `api_refresh_token` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `user_id` VARCHAR(40) DEFAULT NULL,
    `client_id` VARCHAR(80) DEFAULT NULL,
    `token` VARCHAR(128) NOT NULL,
    `expiry` DATETIME DEFAULT NULL,
    `revoked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=revoked,0=not revoked',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`token`),
    INDEX `api_refresh_token_usr_client_idx` (`client_id`, `user_id`)
) ENGINE = InnoDB COMMENT = 'Holds information about api refresh tokens.';
#EndIf

#IfMissingColumn patient_history history_type_key
ALTER TABLE `patient_history` ADD `history_type_key` VARCHAR(36) NULL, ADD `previous_name_prefix` TEXT, ADD `previous_name_first` TEXT, ADD `previous_name_middle` TEXT, ADD `previous_name_last` TEXT, ADD `previous_name_suffix` TEXT, ADD `previous_name_enddate` DATE DEFAULT NULL;
#EndIf

#IfNotRow2D layout_options form_id DEM field_id name_history
SET @group_id = (SELECT group_id FROM layout_options WHERE field_id='billing_note' AND form_id='DEM');
SET @backup_group_id = (SELECT group_id FROM layout_options WHERE field_id='DOB' AND form_id='DEM');
SET @seq = (SELECT MAX(seq) FROM layout_options WHERE group_id = COALESCE(@group_id,@backup_group_id) AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES ('DEM','name_history', COALESCE(@group_id,@backup_group_id),'Previous Names',@seq+1,52,1,0,80,'',1,3,'','[\"EP\"]','Patient Previous names',0);
#EndIf

#IfNotColumnType modules mod_ui_name varchar(64)
ALTER TABLE `modules` MODIFY `mod_ui_name` VARCHAR(64) NOT NULL DEFAULT '';
UPDATE `modules` SET `mod_ui_name` = 'Syndromicsurveillance' WHERE `mod_ui_name` = 'Syndromicsurveillanc';
#EndIf

#IfNotRow modules mod_name Immunization
INSERT INTO `modules` (`mod_name`, `mod_directory`, `mod_parent`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_relative_link`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `mod_nick_name`, `mod_enc_menu`, `permissions_item_table`, `directory`, `date`, `sql_run`, `type`, `sql_version`, `acl_version`) VALUES ('Immunization', 'Immunization', '', '', 1, 'Immunization', 'public/immunization/', 0, 0, '', '', '', NULL, '', NOW(), 1, 1, '0', '');
SET @module_id = (SELECT `mod_id` FROM `modules` WHERE `mod_name` = 'Immunization' LIMIT 1);
SET @section_id = (SELECT MAX(section_id) FROM module_acl_sections);
INSERT INTO `module_acl_sections` (`section_id`, `section_name`, `parent_section`, `section_identifier`, `module_id`) VALUES (IFNULL(@section_id,0)+1, 'Immunization', 0, 'immunization', @module_id);
#EndIf

#IfNotRow modules mod_name Syndromicsurveillance
INSERT INTO `modules` (`mod_name`, `mod_directory`, `mod_parent`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_relative_link`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `mod_nick_name`, `mod_enc_menu`, `permissions_item_table`, `directory`, `date`, `sql_run`, `type`, `sql_version`, `acl_version`) VALUES ('Syndromicsurveillance', 'Syndromicsurveillance', '', '', 1, 'Syndromicsurveillance', 'public/syndromicsurveillance/', 0, 0, '', '', '', NULL, '', NOW(), 1, 1, '0', '');
SET @module_id = (SELECT `mod_id` FROM `modules` WHERE `mod_name` = 'Syndromicsurveillance' LIMIT 1);
SET @section_id = (SELECT MAX(section_id) FROM module_acl_sections);
INSERT INTO `module_acl_sections` (`section_id`, `section_name`, `parent_section`, `section_identifier`, `module_id`) VALUES (IFNULL(@section_id,0)+1, 'Syndromicsurveillance', 0, 'syndromicsurveillance', @module_id);
#EndIf

#IfNotRow modules mod_name Documents
INSERT INTO `modules` (`mod_name`, `mod_directory`, `mod_parent`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_relative_link`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `mod_nick_name`, `mod_enc_menu`, `permissions_item_table`, `directory`, `date`, `sql_run`, `type`, `sql_version`, `acl_version`) VALUES ('Documents', 'Documents', '', '', 1, 'Documents', 'public/documents/', 0, 0, '', '', '', NULL, '', NOW(), 1, 1, '0', '');
SET @module_id = (SELECT `mod_id` FROM `modules` WHERE `mod_name` = 'Documents' LIMIT 1);
SET @section_id = (SELECT MAX(section_id) FROM module_acl_sections);
INSERT INTO `module_acl_sections` (`section_id`, `section_name`, `parent_section`, `section_identifier`, `module_id`) VALUES (IFNULL(@section_id,0)+1, 'Documents', 0, 'documents', @module_id);
#EndIf

#IfNotRow modules mod_name Ccr
INSERT INTO `modules` (`mod_name`, `mod_directory`, `mod_parent`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_relative_link`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `mod_nick_name`, `mod_enc_menu`, `permissions_item_table`, `directory`, `date`, `sql_run`, `type`, `sql_version`, `acl_version`) VALUES ('Ccr', 'Ccr', '', '', 1, 'Ccr', 'public/ccr/', 0, 0, '', '', '', NULL, '', NOW(), 1, 1, '0', '');
SET @module_id = (SELECT `mod_id` FROM `modules` WHERE `mod_name` = 'Ccr' LIMIT 1);
SET @section_id = (SELECT MAX(section_id) FROM module_acl_sections);
INSERT INTO `module_acl_sections` (`section_id`, `section_name`, `parent_section`, `section_identifier`, `module_id`) VALUES (IFNULL(@section_id,0)+1, 'Ccr', 0, 'ccr', @module_id);
#EndIf

#IfNotRow modules mod_name Carecoordination
INSERT INTO `modules` (`mod_name`, `mod_directory`, `mod_parent`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_relative_link`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `mod_nick_name`, `mod_enc_menu`, `permissions_item_table`, `directory`, `date`, `sql_run`, `type`, `sql_version`, `acl_version`) VALUES ('Carecoordination', 'Carecoordination', '', '', 1, 'Carecoordination', 'public/carecoordination/', 0, 0, '', '', '', NULL, '', NOW(), 1, 1, '0', '');
SET @module_id = (SELECT `mod_id` FROM `modules` WHERE `mod_name` = 'Carecoordination' LIMIT 1);
SET @section_id = (SELECT MAX(section_id) FROM module_acl_sections);
INSERT INTO `module_acl_sections` (`section_id`, `section_name`, `parent_section`, `section_identifier`, `module_id`) VALUES (IFNULL(@section_id,0)+1, 'Carecoordination', 0, 'carecoordination', @module_id);
SET @group_id = (SELECT `id` FROM `gacl_aro_groups` WHERE `value` = 'admin' LIMIT 1);
INSERT INTO `module_acl_group_settings` (`module_id`, `group_id`, `section_id`, `allowed`) VALUES (@module_id, @group_id, @section_id+1, 1);
#EndIf

#IfRowIsNull patient_history history_type_key
UPDATE patient_history SET history_type_key = "care_team_history"
WHERE history_type_key IS NULL
        AND (care_team_provider IS NOT NULL OR care_team_facility IS NOT NULL);
#EndIf

#IfMissingColumn patient_data name_history
ALTER TABLE `patient_data` ADD COLUMN `name_history` TINYTEXT;
#EndIf

#IfMissingColumn patient_data suffix
ALTER TABLE `patient_data` ADD COLUMN `suffix` TINYTEXT;
#EndIf

#IfNotRow2D layout_options form_id DEM field_id suffix
SET @group_id = (SELECT group_id FROM layout_options WHERE field_id='lname' AND form_id='DEM');
UPDATE `layout_options` SET `seq` = `seq`*10 WHERE group_id = @group_id AND form_id='DEM';
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='lname' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'suffix', @group_id, '', @seq_add_to+5, 2, 1, 5, 63, '', 0, 0, '', '[\"EP\"]', 'Name Suffix', 0);

#IfNotTable jwt_grant_history
CREATE TABLE `jwt_grant_history` (
     `id` INT NOT NULL AUTO_INCREMENT
    , `jti` VARCHAR(100) NOT NULL COMMENT 'Unique JWT id'
    , `client_id` VARCHAR(80) NOT NULL COMMENT 'FK oauth2_clients.client_id'
    , `jti_exp` TIMESTAMP NULL DEFAULT NULL COMMENT 'jwt exp claim when the jwt expires'
    , `creation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'datetime the grant authorization was requested'
    , PRIMARY KEY (`id`)
    , KEY `jti` (`jti`)
) ENGINE = InnoDB COMMENT = 'Holds JWT authorization grant ids to prevent replay attacks';
#EndIf

#IfNotIndex sct_description idx_concept_id
ALTER TABLE sct_description ADD INDEX `idx_concept_id` (`ConceptId`);
#EndIf

#IfNotIndex sct2_description idx_concept_id
ALTER TABLE sct2_description ADD INDEX `idx_concept_id` (`conceptId`);
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2021-10-01 load_filename 2022-Code Descriptions.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2021-10-01', '2022-Code Descriptions.zip', '11d1d725c84e55d52ef6633da88aa137');
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2021-10-01 load_filename Zip File 3 2022 ICD-10-PCS Codes File.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2021-10-01', 'Zip File 3 2022 ICD-10-PCS Codes File.zip', 'a432177acbdaf9908aa528078ae72176');
#EndIf

#IfMissingColumn audit_master is_qrda_document
ALTER TABLE `audit_master` ADD `is_qrda_document` BOOLEAN NULL DEFAULT FALSE;
#EndIf

#IfNotRow4D supported_external_dataloads load_type CQM_VALUESET load_source NIH_VSAC load_release_date 2020-05-07 load_filename ep_ec_only_cms_20200507.xml.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('CQM_VALUESET', 'NIH_VSAC', '2020-05-07', 'ep_ec_only_cms_20200507.xml.zip', '02dc0b497da979e336c24b0b5c6e1ccb');
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
( 'CQM_VALUESET', 'NIH_VSAC', '2021-05-06', 'ep_ec_eh_cms_20210506.xml.zip', '6455da86e269edb6d33288e72b467373');
#EndIf

#IfMissingColumn form_encounter encounter_type_code
ALTER TABLE `form_encounter` ADD `encounter_type_code` VARCHAR(31) NULL DEFAULT NULL, ADD `encounter_type_description` TEXT;
#EndIf

#IfMissingColumn users billing_facility
ALTER TABLE `users` ADD `billing_facility` TEXT, ADD `billing_facility_id` INT(11) NOT NULL DEFAULT '0';
#EndIf

#IfNotRow code_types ct_key VALUESET
DROP TABLE IF EXISTS `temp_table_one`;
CREATE TABLE `temp_table_one` (`id` int(11) NOT NULL DEFAULT '0',`seq` int(11) NOT NULL DEFAULT '0') ENGINE=InnoDB;
INSERT INTO `temp_table_one` (`id`, `seq`) VALUES (
  IF(((SELECT MAX(`ct_id` ) FROM `code_types`) >= 100), ((SELECT MAX(`ct_id` ) FROM `code_types`) + 1), 100),
  IF(((SELECT MAX(`ct_seq`) FROM `code_types`) >= 100), ((SELECT MAX(`ct_seq`) FROM `code_types`) + 1), 100));
INSERT INTO `code_types` (`ct_key`, `ct_id`, `ct_seq`, `ct_mod`, `ct_just`, `ct_mask`, `ct_fee`, `ct_rel`, `ct_nofs`, `ct_diag`, `ct_active`, `ct_label`, `ct_external`, `ct_claim`, `ct_proc`, `ct_term`, `ct_problem`, `ct_drug`) VALUES
    ('VALUESET', (SELECT MAX(`id`) FROM `temp_table_one`), (SELECT MAX(`seq`) FROM `temp_table_one`), '0', '', '', '1', '1', '0', '1', '1', 'CQM Valueset', '13', '1', '1', '1', '1', '1');
DROP TABLE `temp_table_one`;
#EndIf
