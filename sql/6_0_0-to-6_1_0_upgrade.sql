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

--  #IfUpdateEditOptionsNeeded
--    desc: Change Layout edit options.
--    arguments: mode(add or remove) layout_form_id the_edit_option comma_seperated_list_of_field_ids

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
UPDATE `gacl_aro_seq` SET `id` = (SELECT max(`id`)+1);
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
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`) VALUES ('DEM','name_history', COALESCE(@group_id,@backup_group_id),'Previous Names',@seq+1,52,1,0,80,'',1,3,'','[\"EP\"]','Patient Previous Names',0);
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
#EndIf

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

#IfNotRow4D layout_options form_id HIS field_id exams titlecols 1 datacols 3
UPDATE `layout_options` SET `title` = 'Exams/Tests', `titlecols` = '1', `datacols` = '3' WHERE `layout_options`.`form_id` = 'HIS' AND `layout_options`.`field_id` = 'exams';
UPDATE `layout_options` SET `titlecols` = '1', `datacols` = '3' WHERE `layout_options`.`form_id` = 'HIS' AND `layout_options`.`field_id` = 'usertext11';
#EndIf

#IfNotRow codes code_text Ebola Zaire vaccine, live, recombinant, 1mL dose
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "Ebola Zaire vaccine, live, recombinant, 1mL dose", "Ebola Zaire vaccine, live, recombinant, 1mL dose", 204, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 (COVID-19) vaccine, Subunit, recombinant spike protein-nanoparticle+Matrix-M1 Adjuvant, preservative free, 0.5mL per dose
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 (COVID-19) vaccine, Subunit, recombinant spike protein-nanoparticle+Matrix-M1 Adjuvant, preservative free, 0.5mL per dose", "COVID-19 vaccine, Subunit, rS-nanoparticle+Matrix-M1 Adjuvant, PF, 0.5 mL", 211, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 (COVID-19) vaccine, UNSPECIFIED
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 (COVID-19) vaccine, UNSPECIFIED", "SARS-COV-2 (COVID-19) vaccine, UNSPECIFIED", 213, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text Ebola, unspecified
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "Ebola, unspecified", "Ebola, unspecified", 214, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text Pneumococcal conjugate vaccine 15-valent (PCV15), polysaccharide CRM197 conjugate, adjuvant, preservative free
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "Pneumococcal conjugate vaccine 15-valent (PCV15), polysaccharide CRM197 conjugate, adjuvant, preservative free", "Pneumococcal conjugate PCV15, polysaccharide CRM197 conjugate, adjuvant, PF", 215, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text Pneumococcal conjugate vaccine 20-valent (PCV20), polysaccharide CRM197 conjugate, adjuvant, preservative free
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "Pneumococcal conjugate vaccine 20-valent (PCV20), polysaccharide CRM197 conjugate, adjuvant, preservative free", "Pneumococcal conjugate PCV20, polysaccharide CRM197 conjugate, adjuvant, PF", 216, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 (COVID-19) vaccine, mRNA, spike protein, LNP, preservative free, 30 mcg/0.3mL dose, tris-sucrose formulation
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 (COVID-19) vaccine, mRNA, spike protein, LNP, preservative free, 30 mcg/0.3mL dose, tris-sucrose formulation", "COVID-19, mRNA, LNP-S, PF, 30 mcg/0.3 mL dose, tris-sucrose", 217, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 (COVID-19) vaccine, mRNA, spike protein, LNP, preservative free, 10 mcg/0.2mL dose, tris-sucrose formulation
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 (COVID-19) vaccine, mRNA, spike protein, LNP, preservative free, 10 mcg/0.2mL dose, tris-sucrose formulation", "COVID-19, mRNA, LNP-S, PF, 10 mcg/0.2 mL dose, tris-sucrose", 218, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 (COVID-19) vaccine, mRNA, spike protein, LNP, preservative free, 3 mcg/0.2mL dose, tris-sucrose formulation
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 (COVID-19) vaccine, mRNA, spike protein, LNP, preservative free, 3 mcg/0.2mL dose, tris-sucrose formulation", "COVID-19, mRNA, LNP-S, PF, 3 mcg/0.2 mL dose, tris-sucrose", 219, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19 Non-US Vaccine, Specific Product Unknown
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Non-US Vaccine, Specific Product Unknown", "COVID-19 Non-US Vaccine, Product Unknown", 500, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19 Inactivated Virus Non-US Vaccine Product (QAZCOVID-IN)
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Inactivated Virus Non-US Vaccine Product (QAZCOVID-IN)", "COVID-19 IV Non-US Vaccine (QAZCOVID-IN)", 501, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19 Inactivated Virus Non-US Vaccine Product (COVAXIN)
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Inactivated Virus Non-US Vaccine Product (COVAXIN)", "COVID-19 IV Non-US Vaccine (COVAXIN)", 502, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19  Live Attenuated Virus Non-US Vaccine Product (COVIVAC)
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Live Attenuated Virus Non-US Vaccine Product (COVIVAC)", "COVID-19 LAV Non-US Vaccine (COVIVAC)", 503, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19 Viral Vector Non-replicating Non-US Vaccine Product (Sputnik Light)
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Viral Vector Non-replicating Non-US Vaccine Product (Sputnik Light)", "SARS-COV-2 COVID-19 Viral Vector Non-replicating Non-US Vaccine Product (Sputnik Light)", 504, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19 Viral Vector Non-replicating Non-US Vaccine Product (Sputnik V)
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Viral Vector Non-replicating Non-US Vaccine Product (Sputnik V)", "SARS-COV-2 COVID-19 Viral Vector Non-replicating Non-US Vaccine Product (Sputnik V)", 505, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19 Viral Vector Non-replicating Non-US Vaccine Product (CanSino Biological Inc./Beijing Institute of Biotechnology)
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Viral Vector Non-replicating Non-US Vaccine Product (CanSino Biological Inc./Beijing Institute of Biotechnology)", "COVID-19 VVnr Non-US Vaccine (CanSino Biological Inc./Beijing Institute of Biotechnology)", 506, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19 Protein Subunit Non-US Vaccine Product (Anhui Zhifei Longcom Biopharmaceutical + Institute of Microbiology, Chinese Academy of Sciences)
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Protein Subunit Non-US Vaccine Product (Anhui Zhifei Longcom Biopharmaceutical + Institute of Microbiology, Chinese Academy of Sciences)", "COVID-19 PS Non-US Vaccine (Anhui Zhifei Longcom Biopharm + Inst of Micro, Chinese Acad of Sciences)", 507, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19 Protein Subunit Non-US Vaccine Product  (Jiangsu Province Centers for Disease Control and Prevention)
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Protein Subunit Non-US Vaccine Product  (Jiangsu Province Centers for Disease Control and Prevention)", "COVID-19 PS Non-US Vaccine (Jiangsu Province Centers for Disease Control and Prevention)", 508, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19 Protein Subunit Non-US Vaccine Product (EpiVacCorona)
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Protein Subunit Non-US Vaccine Product (EpiVacCorona)", "COVID-19 PS Non-US Vaccine (EpiVacCorona)", 509, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19 Inactivated Virus Non-US Vaccine Product (BIBP, Sinopharm)
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Inactivated Virus Non-US Vaccine Product (BIBP, Sinopharm)", "COVID-19 IV Non-US Vaccine (BIBP, Sinopharm)", 510, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfNotRow codes code_text SARS-COV-2 COVID-19 Inactivated Virus Non-US Vaccine Product (CoronaVac, Sinovac)
SET @codetypeid = (SELECT `ct_id` FROM `code_types` WHERE `ct_key` = 'CVX');
INSERT INTO `codes` (`id`, `code_text`, `code_text_short`, `code`, `code_type`, `modifier`, `units`, `fee`, `superbill`, `related_code`, `taxrates`, `active`)
VALUES
(NULL, "SARS-COV-2 COVID-19 Inactivated Virus Non-US Vaccine Product (CoronaVac, Sinovac)", "COVID-19 IV Non-US Vaccine (CoronaVac, Sinovac)", 511, @codetypeid, '', 0, 0, '', '', '', 1);
#EndIf

#IfMissingColumn form_observation ob_type
ALTER TABLE `form_observation` ADD `ob_code` VARCHAR(31) DEFAULT NULL;
ALTER TABLE `form_observation` ADD `ob_type` VARCHAR(31) DEFAULT NULL;
#EndIf

#IfNotRow2D list_options list_id Plan_of_Care_Type option_id intervention
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('Plan_of_Care_Type', 'intervention', 'Intervention', '11', '0', '0', '', 'RQO', '', '0', '0', '1', '', '1');
#EndIf

#IfMissingColumn layout_options codes
ALTER TABLE `layout_options` ADD `codes` varchar(255) NOT NULL DEFAULT '';
UPDATE `layout_options` SET `codes` = 'SNOMED-CT:66839005' WHERE `form_id` = 'HIS' AND `field_id` = 'history_father';
UPDATE `layout_options` SET `codes` = 'SNOMED-CT:72705000' WHERE `form_id` = 'HIS' AND `field_id` = 'history_mother';
UPDATE `layout_options` SET `codes` = 'SNOMED-CT:82101005' WHERE `form_id` = 'HIS' AND `field_id` = 'history_siblings';
UPDATE `layout_options` SET `codes` = 'SNOMED-CT:127848009' WHERE `form_id` = 'HIS' AND `field_id` = 'history_spouse';
UPDATE `layout_options` SET `codes` = 'SNOMED-CT:67822003' WHERE `form_id` = 'HIS' AND `field_id` = 'history_offspring';
#EndIf

#IfNotRow3D layout_options form_id DEM field_id pubpid max_length 255
UPDATE `layout_options` SET `max_length` = '255' WHERE `form_id` = 'DEM' AND `field_id` = 'pubpid';
#EndIf

#IfMissingColumn form_observation ob_status
ALTER TABLE `form_observation` ADD `ob_status` VARCHAR(32) NULL;
#EndIf
#IfMissingColumn form_observation result_status
ALTER TABLE `form_observation` ADD `result_status` VARCHAR(32) NULL;
#EndIf
#IfMissingColumn form_observation ob_reason_status
ALTER TABLE `form_observation` ADD `ob_reason_status` VARCHAR(32) NULL;
#EndIf
#IfMissingColumn form_observation ob_reason_code
ALTER TABLE `form_observation` ADD `ob_reason_code` VARCHAR(32) NULL;
#EndIf
#IfMissingColumn form_observation ob_reason_text
ALTER TABLE `form_observation` ADD `ob_reason_text` TEXT;
#EndIf
#IfMissingColumn form_observation ob_documentationof_table
ALTER TABLE `form_observation` ADD `ob_documentationof_table` VARCHAR(255) NULL;
#EndIf
#IfMissingColumn form_observation ob_documentationof_table_id
ALTER TABLE `form_observation` ADD `ob_documentationof_table_id` BIGINT(21) NULL;
#EndIf

#IfNotTable document_templates
CREATE TABLE `document_templates` (
  `id` bigint(21) UNSIGNED NOT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `provider` int(11) UNSIGNED DEFAULT NULL,
  `encounter` int(11) UNSIGNED DEFAULT NULL,
  `modified_date` datetime NOT NULL DEFAULT current_timestamp(),
  `profile` varchar(31) DEFAULT NULL,
  `category` varchar(63) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `template_name` varchar(255) DEFAULT NULL,
  `status` varchar(31) DEFAULT NULL,
  `exclude_portal` tinyint(1) NOT NULL DEFAULT 0,
  `exclude_dashboard` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `size` int(11) NOT NULL DEFAULT 0,
  `template_content` mediumblob DEFAULT NULL,
  `mime` varchar(31) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `location` (`pid`,`category`,`template_name`,`status`)
) ENGINE=InnoDB;
INSERT INTO `document_templates` (`id`, `pid`, `provider`, `encounter`, `modified_date`, `profile`, `category`, `location`, `template_name`, `status`, `exclude_portal`, `exclude_dashboard`, `size`, `template_content`, `mime`) VALUES(1, 0, 18, NULL, '2021-11-21 16:09:14', NULL, '', NULL, 'Help', 'New', 0, 0, 1723, 0x3c68746d6c3e0d0a3c686561643e0d0a093c7469746c653e3c2f7469746c653e0d0a3c2f686561643e0d0a3c626f64793e0d0a3c703e7b5061727365417348544d4c7d3c2f703e0d0a0d0a3c64697620636c6173733d22636f6e7461696e657220702d32206d2d312062672d7365636f6e64617279223e0d0a3c683320636c6173733d22746578742d63656e746572223e496e737472756374696f6e7320666f7220636f6d706c6574696e672050656e64696e6720466f726d733c2f68333e0d0a0d0a3c683520636c6173733d22746578742d63656e746572223e57656c636f6d65207b50617469656e744e616d657d3c2f68353e0d0a0d0a3c646c3e0d0a093c64743e46696c6c696e67204f757420466f726d733c2f64743e0d0a093c64643e2d2053656c656374206120666f726d2066726f6d20746865206c697374206f6e20746865206c65667420627920636c69636b696e672074686520617070726f70726961746520627574746f6e2e2041667465722073656c656374696f6e2c2074686520706167652077696c6c20676f20746f2066756c6c20706167652e20546f20657869742c20636c69636b2074686520416374696f6e206d656e7520686f72697a6f6e74616c2062617272656420627574746f6e20746f20746f67676c652070616765206d6f64652e3c2f64643e0d0a093c64643e2d20416e7377657220616c6c2074686520617070726f707269617465207175657269657320696e2074686520666f726d2e3c2f64643e0d0a093c64643e2d205768656e2066696e69736865642c20636c69636b206569746865722074686520262333393b53617665262333393b206f7220262333393b5375626d697420446f63756d656e74262333393b206f7074696f6e20696e20746f7020416374696f6e204d656e752e2054686520262333393b53617665262333393b20627574746f6e2077696c6c2073617665207468652063757272656e746c792065646974656420666f726d20746f20796f757220446f63756d656e7420486973746f727920616e642077696c6c207374696c6c20626520617661696c61626c6520666f722065646974696e6720756e74696c20796f752064656c6574652074686520666f726d206f722073656e6420746f20796f75722070726f7669646572207573696e672074686520262333393b5375626d697420446f63756d656e74262333393b20616374696f6e20627574746f6e2e3c2f64643e0d0a093c64743e53656e64696e6720446f63756d656e74733c2f64743e0d0a093c64643e2d20436c69636b2074686520262333393b5375626d697420446f63756d656e74262333393b20627574746f6e2066726f6d20416374696f6e204d656e752e3c2f64643e0d0a093c64643e2d204f6e63652073656e742c2074686520666f726d2077696c6c2073686f7720696e20796f757220446f63756d656e7420486973746f72792061732050656e64696e67207265766965772e20596f75206d6179207374696c6c206d616b65206368616e67657320746f2074686520666f726d20756e74696c2072657669657765642062792070726163746963652061646d696e6973747261746f72207768657265206f6e6365207468652072657669657720697320636f6d706c657465642c20446f63756d656e7420486973746f72792077696c6c2073686f772074686520666f726d206173204c6f636b656420616e64206e6f20667572746865722065646974732061726520617661696c61626c652e204174207468697320706f696e742c20796f757220636f6d706c6574656420646f63756d656e74206973207265636f7264656420696e20796f757220636861727420286d65646963616c207265636f7264292e3c2f64643e0d0a093c64743e5369676e696e6720446f63756d656e743c2f64743e0d0a093c64643e2d20437265617465206f72207265646f20796f7572206f6e2066696c65207369676e617475726520627920636c69636b696e672074686520262333393b45646974205369676e6174757265262333393b20627574746f6e20696e20746f7020416374696f6e73204d656e752e20596f75206d617920616c736f206d616e61676520796f7572207369676e61747572652066726f6d20746865204d61696e20746f70206d656e7520756e64657220262333393b4d79205369676e6174757265262333393b2e3c2f64643e0d0a093c64643e2d20546f2061646420796f7572207369676e617475726520746f206120646f63756d656e742c2073696d706c7920636c69636b2074686520617070726f707269617465207369676e206865726520262333393b58262333393b2e3c2f64643e0d0a093c64643e2d20546f2072656d6f76652061207369676e61747572652c20636c69636b20746865207369676e617475726520746f2072657475726e20746f207468652064656661756c74207369676e206865726520262333393b58262333393b2e3c2f64643e0d0a3c2f646c3e0d0a3c2f6469763e0d0a3c2f626f64793e0d0a3c2f68746d6c3e0d0a, 'text/plain');
INSERT INTO `document_templates` (`id`, `pid`, `provider`, `encounter`, `modified_date`, `profile`, `category`, `location`, `template_name`, `status`, `exclude_portal`, `exclude_dashboard`, `size`, `template_content`, `mime`) VALUES(2, -1, 18, NULL, '2021-11-21 16:08:55', NULL, '', NULL, 'Help', 'New', 0, 0, 1723, 0x3c68746d6c3e0d0a3c686561643e0d0a093c7469746c653e3c2f7469746c653e0d0a3c2f686561643e0d0a3c626f64793e0d0a3c703e7b5061727365417348544d4c7d3c2f703e0d0a0d0a3c64697620636c6173733d22636f6e7461696e657220702d32206d2d312062672d7365636f6e64617279223e0d0a3c683320636c6173733d22746578742d63656e746572223e496e737472756374696f6e7320666f7220636f6d706c6574696e672050656e64696e6720466f726d733c2f68333e0d0a0d0a3c683520636c6173733d22746578742d63656e746572223e57656c636f6d65207b50617469656e744e616d657d3c2f68353e0d0a0d0a3c646c3e0d0a093c64743e46696c6c696e67204f757420466f726d733c2f64743e0d0a093c64643e2d2053656c656374206120666f726d2066726f6d20746865206c697374206f6e20746865206c65667420627920636c69636b696e672074686520617070726f70726961746520627574746f6e2e2041667465722073656c656374696f6e2c2074686520706167652077696c6c20676f20746f2066756c6c20706167652e20546f20657869742c20636c69636b2074686520416374696f6e206d656e7520686f72697a6f6e74616c2062617272656420627574746f6e20746f20746f67676c652070616765206d6f64652e3c2f64643e0d0a093c64643e2d20416e7377657220616c6c2074686520617070726f707269617465207175657269657320696e2074686520666f726d2e3c2f64643e0d0a093c64643e2d205768656e2066696e69736865642c20636c69636b206569746865722074686520262333393b53617665262333393b206f7220262333393b5375626d697420446f63756d656e74262333393b206f7074696f6e20696e20746f7020416374696f6e204d656e752e2054686520262333393b53617665262333393b20627574746f6e2077696c6c2073617665207468652063757272656e746c792065646974656420666f726d20746f20796f757220446f63756d656e7420486973746f727920616e642077696c6c207374696c6c20626520617661696c61626c6520666f722065646974696e6720756e74696c20796f752064656c6574652074686520666f726d206f722073656e6420746f20796f75722070726f7669646572207573696e672074686520262333393b5375626d697420446f63756d656e74262333393b20616374696f6e20627574746f6e2e3c2f64643e0d0a093c64743e53656e64696e6720446f63756d656e74733c2f64743e0d0a093c64643e2d20436c69636b2074686520262333393b5375626d697420446f63756d656e74262333393b20627574746f6e2066726f6d20416374696f6e204d656e752e3c2f64643e0d0a093c64643e2d204f6e63652073656e742c2074686520666f726d2077696c6c2073686f7720696e20796f757220446f63756d656e7420486973746f72792061732050656e64696e67207265766965772e20596f75206d6179207374696c6c206d616b65206368616e67657320746f2074686520666f726d20756e74696c2072657669657765642062792070726163746963652061646d696e6973747261746f72207768657265206f6e6365207468652072657669657720697320636f6d706c657465642c20446f63756d656e7420486973746f72792077696c6c2073686f772074686520666f726d206173204c6f636b656420616e64206e6f20667572746865722065646974732061726520617661696c61626c652e204174207468697320706f696e742c20796f757220636f6d706c6574656420646f63756d656e74206973207265636f7264656420696e20796f757220636861727420286d65646963616c207265636f7264292e3c2f64643e0d0a093c64743e5369676e696e6720446f63756d656e743c2f64743e0d0a093c64643e2d20437265617465206f72207265646f20796f7572206f6e2066696c65207369676e617475726520627920636c69636b696e672074686520262333393b45646974205369676e6174757265262333393b20627574746f6e20696e20746f7020416374696f6e73204d656e752e20596f75206d617920616c736f206d616e61676520796f7572207369676e61747572652066726f6d20746865204d61696e20746f70206d656e7520756e64657220262333393b4d79205369676e6174757265262333393b2e3c2f64643e0d0a093c64643e2d20546f2061646420796f7572207369676e617475726520746f206120646f63756d656e742c2073696d706c7920636c69636b2074686520617070726f707269617465207369676e206865726520262333393b58262333393b2e3c2f64643e0d0a093c64643e2d20546f2072656d6f76652061207369676e61747572652c20636c69636b20746865207369676e617475726520746f2072657475726e20746f207468652064656661756c74207369676e206865726520262333393b58262333393b2e3c2f64643e0d0a3c2f646c3e0d0a3c2f6469763e0d0a3c2f626f64793e0d0a3c2f68746d6c3e0d0a, 'text/plain');
INSERT INTO `document_templates` (`id`, `pid`, `provider`, `encounter`, `modified_date`, `profile`, `category`, `location`, `template_name`, `status`, `exclude_portal`, `exclude_dashboard`, `size`, `template_content`, `mime`) VALUES(3, 0, 18, NULL, '2021-11-21 15:51:40', NULL, '', NULL, 'Hipaa Document', 'New', 0, 0, 3548, 0x3c68343e4849504141204465636c61726174696f6e3c2f68343e476976656e20746f6461793a207b444f537d0d0a4f70656e454d5220536f667477617265206d616b65732069742061207072696f7269747920746f206b6565702074686973207069656365206f6620736f6674776172652075706461746564207769746820746865206d6f737420726563656e7420617661696c61626c65207365637572697479206f7074696f6e732c2020736f2069742077696c6c20696e7465677261746520656173696c7920696e746f20612048495041412d636f6d706c69616e7420707261637469636520616e642077696c6c2070726f74656374206f757220637573746f6d6572732077697468206174206c6561737420746865206f6666696369616c20484950414120726567756c6174696f6e732e0d0a3c656d3e5468652050726163746963653a3c2f656d3e200d0a286129204973207265717569726564206279206665646572616c206c617720746f20206d61696e7461696e207468652070726976616379206f6620796f75722050484920616e6420746f2070726f7669646520796f75207769746820746869732050726976616379204e6f746963652064657461696c696e67207468652050726163746963652773206c6567616c2064757469657320616e642070726976616379207072616374696365732077697468207265737065637420746f20796f757220504849200d0a28622920556e6465722074686520507269766163792052756c652c206974206d6179206265207265717569726564206279206f74686572206c61777320746f206772616e74206772656174657220616363657373206f72206d61696e7461696e2067726561746572207265737472696374696f6e73206f6e2074686520757365206f662c206f722072656c65617365206f6620796f757220504849207468616e20746861742077686963682069732070726f766964656420666f7220756e646572206665646572616c204849504141206c6177732e200d0a28632920497320726571756972656420746f20616269646520627920746865207465726d73206f66207468652050726976616379204e6f74696365200d0a2864292052657365727665732074686520726967687420746f206368616e676520746865207465726d73206f6620746869732050726976616379204e6f7469636520616e64206d616b65206e65772050726976616379204e6f746963652070726f766973696f6e732065666665637469766520666f7220616c6c206f6620796f7572205048492074686174206974206d61696e7461696e73206966206e65656465640d0a2865292057696c6c206469737472696275746520616e7920726576697365642050726976616379204e6f7469636520746f20796f75207072696f7220746f20696d706c656d656e746174696f6e200d0a2866292057696c6c206e6f7420726574616c6961746520616761696e737420796f7520666f722066696c696e67206120636f6d706c61696e74200d0a3c656d3e50617469656e7420436f6d6d756e69636174696f6e733a3c2f656d3e0d0a4865616c746820496e737572616e63652050726976616379204163742031393936205553412c20726571756972657320746f20696e666f726d20796f75206f662074686520666f6c6c6f77696e6720676f7665726e6d656e742073746970756c6174696f6e7320696e206f7264657220666f722020757320746f20636f6e7461637420796f75207769746820656475636174696f6e616c20616e642070726f6d6f74696f6e616c206974656d7320696e20746865206675747572652076696120652d6d61696c2c20552e532e206d61696c2c2074656c6570686f6e652c20616e642f6f72207072657265636f72646564206d657373616765732e2057652077696c6c206e6f742073686172652c2073656c6c2c206f722075736520796f75722020706572736f6e616c20636f6e7461637420696e666f726d6174696f6e20666f72207370616d206d657373616765732e200d0a4920616d20617761726520616e64206861766520726561642074686520706f6c6963696573206f66207468697320707261637469636520746f7761726473207365637265637920616e64206469676974616c20696e666f726d6174696f6e2070726f74656374696f6e3a0d0a546865205072616374696365207365742075702074686569722055736572206163636f756e747320666f7220746865204f70656e454d52206461746162617365732c20736f20697420726571756972657320557365727320746f206c6f6720696e207769746820612070617373776f72642e200d0a5468652055736572206861766520746f2065786974206f72206c6f67206f7574206f6620616e79206d65646963616c20696e666f726d6174696f6e207768656e206e6f74207573696e67206974206f7220617320736f6f6e2061732044656661756c742074696d656f757420697320726561636865642e200d0a5768656e207573696e672074686973206d65646963616c20696e666f726d6174696f6e20726567697374726174696f6e20696e2066726f6e74206f662070617469656e74732074686520557365722073686f756c64207573652074686520225072697661637922206665617475726520746f2068696465205048492028506572736f6e616c204865616c746820496e666f726d6174696f6e2920666f72206f746865722070617469656e747320696e20746865205365617263682073637265656e2e200d0a5765206861766520646576656c6f70656420616e642077696c6c20757365207374616e64617264206f7065726174696e672070726f636564757265732028534f50732920726571756972696e6720616e7920757365206f6620746865204578706f72742050617469656e7473204d65646963616c206f72206f7468657220696e666f726d6174696f6e20746f20626520646f63756d656e7465642e200d0a557365727320617265206f6e6c7920616c6c6f77656420746f2073746f7265206120636f7079206f662020796f7572204d65646963616c20696e666f726d6174696f6e206f6e2061206c6170746f7020636f6d7075746572206f72206f7468657220706f727461626c65206d6564696120746861742069732074616b656e206f75747369646520546865205072616374696365206966207265636f7264656420696e2077726974696e672e204279207369676e696e67206f7574206f6620546865205072616374696365207769746820616e7920706f727461626c6520646576696365206f72207472616e73706f7274206d656469756d207468697320696e666f726d6174696f6e20697320746f20626520657261736564207768656e2066696e6973686564207769746820746865206e65656420746f2074616b65207468697320696e666f726d6174696f6e206f7574206f66205468652050726163746963652c20696620706f737369626c65207468697320696e666f726d6174696f6e206973206f6e6c7920746f2062652074616b656e206f7574736964652054686520507261637469636520696e20656e6372797074656420666f726d61742e200d0a4f6e6c7920737065636966696320746563686e696369616e73206d61792068617665206f63636173696f6e616c2061636365737320746f206f757220686172647761726520616e6420536f6674776172652e2054686520484950414120507269766163792052756c652072657175697265732074686174206120707261637469636520686176652061207369676e656420427573696e657373204173736f636961746520436f6e7472616374206265666f7265206772616e74696e672073756368206163636573732e2054686520546563686e696369616e732061726520747261696e6564206f6e20484950414120726567756c6174696f6e7320616e64206c696d6974207468652075736520616e6420646973636c6f73757265206f6620637573746f6d6572206461746120746f20746865206d696e696d756d206e65636573736172792e0d0a3c68723e0d0a3c656d3e3c68343e492061636b6e6f776c656467652072656365697074206f662074686973206e6f746963652c206861766520726561642074686520636f6e74656e747320616e6420756e6465727374616e642074686520636f6e74656e742e3c2f68343e3c2f656d3e0d0a50617469656e74204e616d653a20097b50617469656e744e616d657d20205365783a207b50617469656e745365787d203c656d3e686572656279207369676e7320616e6420616772656520746f20746865207465726d73206f6620746869732061677265656d656e74202e3c2f656d3e0d0a4f75722065787465726e616c2049443a7b50617469656e7449447d09090d0a426f726e3a2009097b50617469656e74444f427d20200d0a486f6d6520416464726573733a20097b416464726573737d2020200d0a5a69703a2009097b5a69707d3b20436974793a207b436974797d3b2053746174653a207b53746174657d0d0a486f6d652050686f6e653a20097b50617469656e7450686f6e657d0d0a090950617469656e74205369676e61747572653a7b50617469656e745369676e61747572657d0d0a090950617469656e743a7b50617469656e744e616d657d20446174653a207b444f537d0d0a0d0a3c656d3e4920646f206e6f7420616363657074207468657365207465726d733a203c2f656d3e7b436865636b4d61726b7d0d0a50617469656e74207265667573616c20746f207369676e2064756520746f2074686520666f6c6c6f77696e6720726561736f6e3a207b54657874496e7075747d0d0a, 'text/plain');
INSERT INTO `document_templates` (`id`, `pid`, `provider`, `encounter`, `modified_date`, `profile`, `category`, `location`, `template_name`, `status`, `exclude_portal`, `exclude_dashboard`, `size`, `template_content`, `mime`) VALUES(4, -1, 18, NULL, '2021-11-21 15:51:40', NULL, '', NULL, 'Hipaa Document', 'New', 0, 0, 3548, 0x3c68343e4849504141204465636c61726174696f6e3c2f68343e476976656e20746f6461793a207b444f537d0d0a4f70656e454d5220536f667477617265206d616b65732069742061207072696f7269747920746f206b6565702074686973207069656365206f6620736f6674776172652075706461746564207769746820746865206d6f737420726563656e7420617661696c61626c65207365637572697479206f7074696f6e732c2020736f2069742077696c6c20696e7465677261746520656173696c7920696e746f20612048495041412d636f6d706c69616e7420707261637469636520616e642077696c6c2070726f74656374206f757220637573746f6d6572732077697468206174206c6561737420746865206f6666696369616c20484950414120726567756c6174696f6e732e0d0a3c656d3e5468652050726163746963653a3c2f656d3e200d0a286129204973207265717569726564206279206665646572616c206c617720746f20206d61696e7461696e207468652070726976616379206f6620796f75722050484920616e6420746f2070726f7669646520796f75207769746820746869732050726976616379204e6f746963652064657461696c696e67207468652050726163746963652773206c6567616c2064757469657320616e642070726976616379207072616374696365732077697468207265737065637420746f20796f757220504849200d0a28622920556e6465722074686520507269766163792052756c652c206974206d6179206265207265717569726564206279206f74686572206c61777320746f206772616e74206772656174657220616363657373206f72206d61696e7461696e2067726561746572207265737472696374696f6e73206f6e2074686520757365206f662c206f722072656c65617365206f6620796f757220504849207468616e20746861742077686963682069732070726f766964656420666f7220756e646572206665646572616c204849504141206c6177732e200d0a28632920497320726571756972656420746f20616269646520627920746865207465726d73206f66207468652050726976616379204e6f74696365200d0a2864292052657365727665732074686520726967687420746f206368616e676520746865207465726d73206f6620746869732050726976616379204e6f7469636520616e64206d616b65206e65772050726976616379204e6f746963652070726f766973696f6e732065666665637469766520666f7220616c6c206f6620796f7572205048492074686174206974206d61696e7461696e73206966206e65656465640d0a2865292057696c6c206469737472696275746520616e7920726576697365642050726976616379204e6f7469636520746f20796f75207072696f7220746f20696d706c656d656e746174696f6e200d0a2866292057696c6c206e6f7420726574616c6961746520616761696e737420796f7520666f722066696c696e67206120636f6d706c61696e74200d0a3c656d3e50617469656e7420436f6d6d756e69636174696f6e733a3c2f656d3e0d0a4865616c746820496e737572616e63652050726976616379204163742031393936205553412c20726571756972657320746f20696e666f726d20796f75206f662074686520666f6c6c6f77696e6720676f7665726e6d656e742073746970756c6174696f6e7320696e206f7264657220666f722020757320746f20636f6e7461637420796f75207769746820656475636174696f6e616c20616e642070726f6d6f74696f6e616c206974656d7320696e20746865206675747572652076696120652d6d61696c2c20552e532e206d61696c2c2074656c6570686f6e652c20616e642f6f72207072657265636f72646564206d657373616765732e2057652077696c6c206e6f742073686172652c2073656c6c2c206f722075736520796f75722020706572736f6e616c20636f6e7461637420696e666f726d6174696f6e20666f72207370616d206d657373616765732e200d0a4920616d20617761726520616e64206861766520726561642074686520706f6c6963696573206f66207468697320707261637469636520746f7761726473207365637265637920616e64206469676974616c20696e666f726d6174696f6e2070726f74656374696f6e3a0d0a546865205072616374696365207365742075702074686569722055736572206163636f756e747320666f7220746865204f70656e454d52206461746162617365732c20736f20697420726571756972657320557365727320746f206c6f6720696e7769746820612070617373776f72642e200d0a5468652055736572206861766520746f2065786974206f72206c6f67206f7574206f6620616e79206d65646963616c20696e666f726d6174696f6e207768656e206e6f74207573696e67206974206f7220617320736f6f6e2061732044656661756c742074696d656f757420697320726561636865642e200d0a5768656e207573696e672074686973206d65646963616c20696e666f726d6174696f6e20726567697374726174696f6e20696e2066726f6e74206f662070617469656e74732074686520557365722073686f756c64207573652074686520225072697661637922206665617475726520746f2068696465205048492028506572736f6e616c204865616c746820496e666f726d6174696f6e2920666f72206f746865722070617469656e747320696e20746865205365617263682073637265656e2e200d0a5765206861766520646576656c6f70656420616e642077696c6c20757365207374616e64617264206f7065726174696e672070726f636564757265732028534f50732920726571756972696e6720616e7920757365206f6620746865204578706f72742050617469656e7473204d65646963616c206f72206f7468657220696e666f726d6174696f6e20746f20626520646f63756d656e7465642e200d0a557365727320617265206f6e6c7920616c6c6f77656420746f2073746f7265206120636f7079206f662020796f7572204d65646963616c20696e666f726d6174696f6e206f6e2061206c6170746f7020636f6d7075746572206f72206f7468657220706f727461626c65206d6564696120746861742069732074616b656e206f75747369646520546865205072616374696365206966207265636f7264656420696e2077726974696e672e204279207369676e696e67206f7574206f6620546865205072616374696365207769746820616e7920706f727461626c6520646576696365206f72207472616e73706f7274206d656469756d207468697320696e666f726d6174696f6e20697320746f20626520657261736564207768656e2066696e6973686564207769746820746865206e65656420746f2074616b65207468697320696e666f726d6174696f6e206f7574206f66205468652050726163746963652c20696620706f737369626c65207468697320696e666f726d6174696f6e206973206f6e6c7920746f2062652074616b656e206f7574736964652054686520507261637469636520696e20656e6372797074656420666f726d61742e200d0a4f6e6c7920737065636966696320746563686e696369616e73206d61792068617665206f63636173696f6e616c2061636365737320746f206f757220686172647761726520616e6420536f6674776172652e2054686520484950414120507269766163792052756c652072657175697265732074686174206120707261637469636520686176652061207369676e656420427573696e657373204173736f636961746520436f6e7472616374206265666f7265206772616e74696e672073756368206163636573732e2054686520546563686e696369616e732061726520747261696e6564206f6e20484950414120726567756c6174696f6e7320616e64206c696d6974207468652075736520616e6420646973636c6f73757265206f6620637573746f6d6572206461746120746f20746865206d696e696d756d206e65636573736172792e0d0a3c68723e0d0a3c656d3e3c68343e492061636b6e6f776c656467652072656365697074206f662074686973206e6f746963652c206861766520726561642074686520636f6e74656e747320616e6420756e6465727374616e642074686520636f6e74656e742e3c2f68343e3c2f656d3e0d0a50617469656e74204e616d653a20097b50617469656e744e616d657d20205365783a207b50617469656e745365787d203c656d3e686572656279207369676e7320616e6420616772656520746f20746865207465726d73206f6620746869732061677265656d656e74202e3c2f656d3e0d0a4f75722065787465726e616c2049443a7b50617469656e7449447d09090d0a426f726e3a2009097b50617469656e74444f427d20200d0a486f6d6520416464726573733a20097b416464726573737d2020200d0a5a69703a2009097b5a69707d3b20436974793a207b436974797d3b2053746174653a207b53746174657d0d0a486f6d652050686f6e653a20097b50617469656e7450686f6e657d0d0a090950617469656e74205369676e61747572653a7b50617469656e745369676e61747572657d0d0a090950617469656e743a7b50617469656e744e616d657d20446174653a207b444f537d0d0a0d0a3c656d3e4920646f206e6f7420616363657074207468657365207465726d733a203c2f656d3e7b436865636b4d61726b7d0d0a50617469656e74207265667573616c20746f207369676e2064756520746f2074686520666f6c6c6f77696e6720726561736f6e3a207b54657874496e7075747d0d0a, 'text/plain');
INSERT INTO `document_templates` (`id`, `pid`, `provider`, `encounter`, `modified_date`, `profile`, `category`, `location`, `template_name`, `status`, `exclude_portal`, `exclude_dashboard`, `size`, `template_content`, `mime`) VALUES(5, 0, 18, NULL, '2021-11-21 18:22:25', NULL, '', NULL, 'Insurance Info', 'New', 0, 0, 785, 0x3c703e7b5061727365417348544d4c7d3c2f703e0d0a3c646976207374796c653d22666f6e742d73697a653a31347078223e0d0a3c6834207374796c653d22746578742d616c69676e3a2063656e7465723b223e494e535552414e434520494e464f524d4154494f4e3c2f68343e0d0a0d0a3c703e7b436865636b4d61726b7d204d6564696361726523207b54657874496e7075747d207b436865636b4d61726b7d204d6564696361696423207b54657874496e7075747d3c2f703e0d0a0d0a3c703e7b436865636b4d61726b7d20576f726b65727320436f6d70656e736174696f6e20286a6f6220696e6a7572792920496620736f207468656e20746f2077686f6d2069732062696c6c20746f2062652073656e743f207b54657874496e7075747d3c2f703e0d0a0d0a3c703e7b436865636b4d61726b7d204f74686572204d65646963616c20496e737572616e63653a2047726f757023207b54657874496e7075747d20494423207b54657874496e7075747d3c2f703e0d0a0d0a3c703e4e616d652f4164647265737320317374206f7220326e6420496e737572616e63653a3c2f703e0d0a0d0a3c703e4e616d653a207b54657874496e7075747d2052656c6174696f6e736869703a207b54657874496e7075747d3c2f703e0d0a0d0a3c703e41646472657373207b54657874496e7075747d205374617465207b54657874496e7075747d205a6970207b54657874496e7075747d3c2f703e0d0a0d0a3c703e50686f6e653a207b54657874496e7075747d205365636f6e646172792050686f6e653a207b54657874496e7075747d3c2f703e0d0a0d0a3c6872202f3e0d0a3c703e41726520796f7520706572736f6e616c6c7920726573706f6e7369626c6520666f7220746865207061796d656e74206f6620796f757220666565733f207b796e526164696f47726f75707d3c2f703e0d0a0d0a3c703e4966206e6f742c2077686f2069733f266e6273703b3c2f703e0d0a0d0a3c703e4e616d653a207b54657874496e7075747d2052656c6174696f6e736869703a207b54657874496e7075747d20444f423a7b54657874496e7075747d3c2f703e0d0a0d0a3c703e41646472657373207b54657874496e7075747d205374617465207b54657874496e7075747d205a6970207b54657874496e7075747d3c2f703e0d0a0d0a3c703e50686f6e653a207b54657874496e7075747d205365636f6e646172792050686f6e653a207b54657874496e7075747d3c2f703e0d0a0d0a3c6872202f3e0d0a3c703e57686f20746f206e6f7469667920696e20656d657267656e637920286e6561726573742072656c6174697665206f7220667269656e64293f3c2f703e0d0a0d0a3c703e4e616d657b54657874496e7075747d2052656c6174696f6e736869707b54657874496e7075747d3c2f703e0d0a0d0a3c703e416464726573733a207b54657874496e7075747d2053746174653a207b54657874496e7075747d205a69703a207b54657874496e7075747d3c2f703e0d0a0d0a3c703e576f726b2050686f6e653a207b54657874496e7075747d20486f6d652050686f6e653a207b54657874496e7075747d3c2f703e0d0a0d0a3c6872202f3e0d0a3c703e5369676e6564206279207b50617469656e744e616d657d206f6e207b43757272656e74446174653a2671756f743b676c6f62616c2671756f743b7d207b43757272656e7454696d657d207b50617469656e745369676e61747572657d3c2f703e0d0a3c2f6469763e0d0a, 'text/plain');
INSERT INTO `document_templates` (`id`, `pid`, `provider`, `encounter`, `modified_date`, `profile`, `category`, `location`, `template_name`, `status`, `exclude_portal`, `exclude_dashboard`, `size`, `template_content`, `mime`) VALUES(6, -1, 18, NULL, '2021-11-21 18:22:52', NULL, '', NULL, 'Insurance Info', 'New', 0, 0, 785, 0x3c703e7b5061727365417348544d4c7d3c2f703e0d0a3c646976207374796c653d22666f6e742d73697a653a31347078223e0d0a3c6834207374796c653d22746578742d616c69676e3a2063656e7465723b223e494e535552414e434520494e464f524d4154494f4e3c2f68343e0d0a0d0a3c703e7b436865636b4d61726b7d204d6564696361726523207b54657874496e7075747d207b436865636b4d61726b7d204d6564696361696423207b54657874496e7075747d3c2f703e0d0a0d0a3c703e7b436865636b4d61726b7d20576f726b65727320436f6d70656e736174696f6e20286a6f6220696e6a7572792920496620736f207468656e20746f2077686f6d2069732062696c6c20746f2062652073656e743f207b54657874496e7075747d3c2f703e0d0a0d0a3c703e7b436865636b4d61726b7d204f74686572204d65646963616c20496e737572616e63653a2047726f757023207b54657874496e7075747d20494423207b54657874496e7075747d3c2f703e0d0a0d0a3c703e4e616d652f4164647265737320317374206f7220326e6420496e737572616e63653a3c2f703e0d0a0d0a3c703e4e616d653a207b54657874496e7075747d2052656c6174696f6e736869703a207b54657874496e7075747d3c2f703e0d0a0d0a3c703e41646472657373207b54657874496e7075747d205374617465207b54657874496e7075747d205a6970207b54657874496e7075747d3c2f703e0d0a0d0a3c703e50686f6e653a207b54657874496e7075747d205365636f6e646172792050686f6e653a207b54657874496e7075747d3c2f703e0d0a0d0a3c6872202f3e0d0a3c703e41726520796f7520706572736f6e616c6c7920726573706f6e7369626c6520666f7220746865207061796d656e74206f6620796f757220666565733f207b796e526164696f47726f75707d3c2f703e0d0a0d0a3c703e4966206e6f742c2077686f2069733f266e6273703b3c2f703e0d0a0d0a3c703e4e616d653a207b54657874496e7075747d2052656c6174696f6e736869703a207b54657874496e7075747d20444f423a7b54657874496e7075747d3c2f703e0d0a0d0a3c703e41646472657373207b54657874496e7075747d205374617465207b54657874496e7075747d205a6970207b54657874496e7075747d3c2f703e0d0a0d0a3c703e50686f6e653a207b54657874496e7075747d205365636f6e646172792050686f6e653a207b54657874496e7075747d3c2f703e0d0a0d0a3c6872202f3e0d0a3c703e57686f20746f206e6f7469667920696e20656d657267656e637920286e6561726573742072656c6174697665206f7220667269656e64293f3c2f703e0d0a0d0a3c703e4e616d657b54657874496e7075747d2052656c6174696f6e736869707b54657874496e7075747d3c2f703e0d0a0d0a3c703e416464726573733a207b54657874496e7075747d2053746174653a207b54657874496e7075747d205a69703a207b54657874496e7075747d3c2f703e0d0a0d0a3c703e576f726b2050686f6e653a207b54657874496e7075747d20486f6d652050686f6e653a207b54657874496e7075747d3c2f703e0d0a0d0a3c6872202f3e0d0a3c703e5369676e6564206279207b50617469656e744e616d657d206f6e207b43757272656e74446174653a2671756f743b676c6f62616c2671756f743b7d207b43757272656e7454696d657d207b50617469656e745369676e61747572657d3c2f703e0d0a3c2f6469763e0d0a, 'text/plain');
INSERT INTO `document_templates` (`id`, `pid`, `provider`, `encounter`, `modified_date`, `profile`, `category`, `location`, `template_name`, `status`, `exclude_portal`, `exclude_dashboard`, `size`, `template_content`, `mime`) VALUES(7, 0, 18, NULL, '2021-11-21 15:51:41', NULL, '', NULL, 'Medical History', 'New', 0, 0, 77, 0x7b456e636f756e746572466f726d3a4849537d0d0a0d0a3c6c6162656c3e50617469656e74205369676e61747572653a203c2f6c6162656c3e7b50617469656e745369676e61747572657d0d0a, 'text/plain');
INSERT INTO `document_templates` (`id`, `pid`, `provider`, `encounter`, `modified_date`, `profile`, `category`, `location`, `template_name`, `status`, `exclude_portal`, `exclude_dashboard`, `size`, `template_content`, `mime`) VALUES(8, -1, 18, NULL, '2021-11-21 15:51:42', NULL, '', NULL, 'Medical History', 'New', 0, 0, 77, 0x7b456e636f756e746572466f726d3a4849537d0d0a0d0a3c6c6162656c3e50617469656e74205369676e61747572653a203c2f6c6162656c3e7b50617469656e745369676e61747572657d0d0a, 'text/plain');
INSERT INTO `document_templates` (`id`, `pid`, `provider`, `encounter`, `modified_date`, `profile`, `category`, `location`, `template_name`, `status`, `exclude_portal`, `exclude_dashboard`, `size`, `template_content`, `mime`) VALUES(9, 0, 18, NULL, '2021-11-21 15:51:42', NULL, '', NULL, 'Privacy Document', 'New', 0, 0, 2536, 0x3c703e4e4f54494345204f462050524956414359205052414354494345532050415449454e542041434b4e4f574c454447454d454e5420414e4420434f4e53454e5420544f204d45444943414c2054524541544d454e540d0a50617469656e74204e616d653a203c656d3e7b50617469656e744e616d657d3c2f656d3e0d0a44617465206f662042697274683a207b50617469656e74444f427d0d0a0d0a49206861766520726563656976656420616e6420756e6465727374616e6420746869732070726163746963652773204e6f74696365206f66205072697661637920507261637469636573207772697474656e20696e20706c61696e20456e676c6973682e20546865206e6f746963652070726f766964657320696e2064657461696c20746865207573657320616e6420646973636c6f7375726573206f66206d792070726f746563746564206865616c746820696e666f726d6174696f6e2074686174206d6179206265206d61646520627920746869732070726163746963652c206d7920696e646976696475616c207269676874732c20686f772049206d61792065786572636973652074686f7365207269676874732c20616e642074686520707261637469636573206c6567616c206475746965732077697468207265737065637420746f206d7920696e666f726d6174696f6e2e0d0a0d0a4920756e6465727374616e642074686174207468652070726163746963652072657365727665732074686520726967687420746f206368616e676520746865207465726d73206f66207468652050726976616379205072616374696365732c20616e6420746f206d616b65206368616e67657320726567617264696e6720616c6c2070726f746563746564206865616c746820696e666f726d6174696f6e2e204966206368616e676573206f63637572207468656e207468652070726163746963652077696c6c2070726f76696465206d6520776974682061207265766973656420636f70792075706f6e20726571756573742e0d0a0d0a4920766f6c756e746172696c7920636f6e73656e7420746f20636172652c20696e636c7564696e672070687973696369616e206578616d696e6174696f6e20616e64207465737473207375636820617320782d7261792c206c61626f7261746f727920746573747320616e6420746f206d65646963616c2074726561746d656e74206279206d792070687973696369616e206f72206869732f68657220617373697374616e7473206f722064657369676e6565732c206173206d6179206265206e656365737361727920696e20746865206a7564676d656e74206f66206d792070687973696369616e2e204e6f2067756172616e746565732068617665206265656e206d61646520746f206d652061732074686520726573756c74206f662074726561746d656e74206f72206578616d696e6174696f6e2e0d0a0d0a417574686f72697a6174696f6e20666f723a0d0a496e20636f6e73696465726174696f6e20666f72207365727669636573207265636569766564206279203c656d3e7b526566657272696e67444f437d3c2f656d3e204920616772656520746f2070617920616e7920616e6420616c6c20636861726765732061732062696c6c65642e204920616c736f2072657175657374207468617420646972656374207061796d656e7473206265206d61646520746f203c656d3e7b526566657272696e67444f437d3c2f656d3e206f6e206d7920626568616c6620627920696e73757265727320616e64206167656e6369657320696e2074686520736574746c656d656e74206f6620616e79206f66206d7920636c61696d732e204920756e6465727374616e642074686174206d792070726f746563746564206865616c746820696e666f726d6174696f6e206d6179206e65656420746f2062652072656c656173656420666f722074686520707572706f7365206f662074726561746d656e742c207061796d656e74206f72206865616c74682063617265206f7065726174696f6e732e0d0a0d0a4d656469636172652050617469656e74733a0d0a49206365727469667920746861742074686520696e666f726d6174696f6e20676976656e206279206d6520666f72206170706c69636174696f6e20666f72207061796d656e7420756e646572207469746c65205856494949206f662074686520536f6369616c2053656375726974792041637420697320636f72726563742e204920617574686f72697a6520616e7920686f6c646572206f66206d65646963616c206f72206f746865722072656c6576616e7420696e666f726d6174696f6e2061626f7574206d652062652072656c656173656420746f2074686520536f6369616c2053656375726974792041646d696e697374726174696f6e206f72206974277320696e7465726d6564696172696573206f6620636172726965727320616e64207375636820696e666f726d6174696f6e206e656564656420746f20737570706f7274206170706c69636174696f6e20666f72207061796d656e742e20496e636c7564696e67207265636f726473207065727461696e696e6720746f2048495620737461747573206f722074726561746d656e74202841494453207265636f726473292c206472756720616e6420616c636f686f6c2074726561746d656e742c20616e64206f722070737963686961747269632074726561746d656e742e20492061737369676e20616e6420617574686f72697a65207061796d656e74206469726563746c7920746f203c656d3e7b526566657272696e67444f437d3c2f656d3e20666f722074686520756e70616964206368617267657320666f72207468652070687973696369616e27732073657276696365732e204920756e6465727374616e642074686174204920616d20726573706f6e7369626c6520666f7220616c6c20696e737572616e63652064656475637469626c657320616e6420636f696e737572616e63652e0d0a436f6d6d656e74733a207b54657874496e7075747d0d0a5369676e61747572653a207b50617469656e745369676e61747572657d0d0a446f20796f7520617574686f72697a6520656c656374726f6e6963207369676e6174757265207b436865636b4d61726b7d200d0a52656c6174696f6e7368697020746f2070617469656e7420286966207369676e6564206279206120706572736f6e616c20726570726573656e746174697665293a207b54657874496e7075747d0d0a41726520796f75205072696d61727920436172652047697665723a7b796e526164696f47726f75707d0d0a446174653a207b444f537d0d0a3c2f703e3c703e436c696e696320526570726573656e746174697665205369676e6174757265266e6273703b7b526566657272696e67444f437d205369676e65643a207b41646d696e5369676e61747572657d203c2f703e3c703e3c6272202f3e3c2f703e, 'text/plain');
INSERT INTO `document_templates` (`id`, `pid`, `provider`, `encounter`, `modified_date`, `profile`, `category`, `location`, `template_name`, `status`, `exclude_portal`, `exclude_dashboard`, `size`, `template_content`, `mime`) VALUES(10, -1, 18, NULL, '2021-11-21 15:51:42', NULL, '', NULL, 'Privacy Document', 'New', 0, 0, 2536, 0x3c703e4e4f54494345204f462050524956414359205052414354494345532050415449454e542041434b4e4f574c454447454d454e5420414e4420434f4e53454e5420544f204d45444943414c2054524541544d454e540d0a50617469656e74204e616d653a203c656d3e7b50617469656e744e616d657d3c2f656d3e0d0a44617465206f662042697274683a207b50617469656e74444f427d0d0a0d0a49206861766520726563656976656420616e6420756e6465727374616e6420746869732070726163746963652773204e6f74696365206f66205072697661637920507261637469636573207772697474656e20696e20706c61696e20456e676c6973682e20546865206e6f746963652070726f766964657320696e2064657461696c20746865207573657320616e6420646973636c6f7375726573206f66206d792070726f746563746564206865616c746820696e666f726d6174696f6e2074686174206d6179206265206d61646520627920746869732070726163746963652c206d7920696e646976696475616c207269676874732c20686f772049206d61792065786572636973652074686f7365207269676874732c20616e642074686520707261637469636573206c6567616c206475746965732077697468207265737065637420746f206d7920696e666f726d6174696f6e2e0d0a0d0a4920756e6465727374616e642074686174207468652070726163746963652072657365727665732074686520726967687420746f206368616e676520746865207465726d73206f66207468652050726976616379205072616374696365732c20616e6420746f206d616b65206368616e67657320726567617264696e6720616c6c2070726f746563746564206865616c746820696e666f726d6174696f6e2e204966206368616e676573206f63637572207468656e207468652070726163746963652077696c6c2070726f76696465206d6520776974682061207265766973656420636f70792075706f6e20726571756573742e0d0a0d0a4920766f6c756e746172696c7920636f6e73656e7420746f20636172652c20696e636c7564696e672070687973696369616e206578616d696e6174696f6e20616e64207465737473207375636820617320782d7261792c206c61626f7261746f727920746573747320616e6420746f206d65646963616c2074726561746d656e74206279206d792070687973696369616e206f72206869732f68657220617373697374616e7473206f722064657369676e6565732c206173206d6179206265206e656365737361727920696e20746865206a7564676d656e74206f66206d792070687973696369616e2e204e6f2067756172616e746565732068617665206265656e206d61646520746f206d652061732074686520726573756c74206f662074726561746d656e74206f72206578616d696e6174696f6e2e0d0a0d0a417574686f72697a6174696f6e20666f723a0d0a496e20636f6e73696465726174696f6e20666f72207365727669636573207265636569766564206279203c656d3e7b526566657272696e67444f437d3c2f656d3e204920616772656520746f2070617920616e7920616e6420616c6c20636861726765732061732062696c6c65642e204920616c736f2072657175657374207468617420646972656374207061796d656e7473206265206d61646520746f203c656d3e7b526566657272696e67444f437d3c2f656d3e206f6e206d7920626568616c6620627920696e73757265727320616e64206167656e6369657320696e2074686520736574746c656d656e74206f6620616e79206f66206d7920636c61696d732e204920756e6465727374616e642074686174206d792070726f746563746564206865616c746820696e666f726d6174696f6e206d6179206e65656420746f2062652072656c656173656420666f722074686520707572706f7365206f662074726561746d656e742c207061796d656e74206f72206865616c74682063617265206f7065726174696f6e732e0d0a0d0a4d656469636172652050617469656e74733a0d0a49206365727469667920746861742074686520696e666f726d6174696f6e20676976656e206279206d6520666f72206170706c69636174696f6e20666f72207061796d656e7420756e646572207469746c65205856494949206f662074686520536f6369616c2053656375726974792041637420697320636f72726563742e204920617574686f72697a6520616e7920686f6c646572206f66206d65646963616c206f72206f746865722072656c6576616e7420696e666f726d6174696f6e2061626f7574206d652062652072656c656173656420746f2074686520536f6369616c2053656375726974792041646d696e697374726174696f6e206f72206974277320696e7465726d6564696172696573206f6620636172726965727320616e64207375636820696e666f726d6174696f6e206e656564656420746f20737570706f7274206170706c69636174696f6e20666f72207061796d656e742e20496e636c7564696e67207265636f726473207065727461696e696e6720746f2048495620737461747573206f722074726561746d656e74202841494453207265636f726473292c206472756720616e6420616c636f686f6c2074726561746d656e742c20616e64206f722070737963686961747269632074726561746d656e742e20492061737369676e20616e6420617574686f72697a65207061796d656e74206469726563746c7920746f203c656d3e7b526566657272696e67444f437d3c2f656d3e20666f722074686520756e70616964206368617267657320666f72207468652070687973696369616e27732073657276696365732e204920756e6465727374616e642074686174204920616d20726573706f6e7369626c6520666f7220616c6c20696e737572616e63652064656475637469626c657320616e6420636f696e737572616e63652e0d0a436f6d6d656e74733a207b54657874496e7075747d0d0a5369676e61747572653a207b50617469656e745369676e61747572657d0d0a446f20796f7520617574686f72697a6520656c656374726f6e6963207369676e6174757265207b436865636b4d61726b7d200d0a52656c6174696f6e7368697020746f2070617469656e7420286966207369676e6564206279206120706572736f6e616c20726570726573656e746174697665293a207b54657874496e7075747d0d0a41726520796f75205072696d61727920436172652047697665723a7b796e526164696f47726f75707d0d0a446174653a207b444f537d0d0a3c2f703e3c703e436c696e696320526570726573656e746174697665205369676e6174757265266e6273703b7b526566657272696e67444f437d205369676e65643a207b41646d696e5369676e61747572657d203c2f703e3c703e3c6272202f3e3c2f703e, 'text/plain');
#EndIf

SET @ai_exist =(SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'document_templates' AND COLUMN_NAME = 'id' AND 'EXTRA' LIKE '%auto_increment%' LIMIT 1);
SET @query = IF (@ai_exist = 0, 'ALTER TABLE `document_templates` CHANGE `id` `id` BIGINT(21) UNSIGNED NOT NULL AUTO_INCREMENT', '');
PREPARE statement FROM @query;
EXECUTE statement;

#IfNotTable document_template_profiles
CREATE TABLE `document_template_profiles` (
  `id` bigint(21) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` bigint(21) UNSIGNED NOT NULL,
  `profile` varchar(64) DEFAULT NULL,
  `template_name` varchar(255) DEFAULT NULL,
  `category` varchar(63) DEFAULT NULL,
  `provider` int(11) UNSIGNED DEFAULT NULL,
  `modified_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `location` (`profile`,`template_name`,`template_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotRow2D list_options list_id lists option_id Document_Template_Profiles
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('lists','Document_Template_Profiles','Document Template Profiles',0,1,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Document_Template_Profiles','profile_1','Defaults',10,0,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Document_Template_Profiles','profile_2','Registration',20,0,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Document_Template_Profiles','profile_3','Mental Health',30,0,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Document_Template_Profiles','profile_4','Questionnaires',40,0,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Document_Template_Profiles','profile_5','Legal',50,0,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Document_Template_Profiles','profile_6','Acknowledgement Documents',60,0,0);
#EndIf

#IfMissingColumn patient_data street_line_2
ALTER TABLE patient_data ADD street_line_2 TINYTEXT;
#EndIf

#IfMissingColumn employer_data street_line_2
ALTER TABLE employer_data ADD street_line_2 TINYTEXT;
#EndIf

#IfMissingColumn insurance_data subscriber_street_line_2
ALTER TABLE insurance_data ADD subscriber_street_line_2 TINYTEXT;
#EndIf

#IfMissingColumn insurance_data subscriber_employer_street_line_2
ALTER TABLE insurance_data ADD subscriber_employer_street_line_2 TINYTEXT ;
#EndIf

#IfNotRow2D layout_options form_id DEM field_id street_line_2
SET @group_id = (SELECT group_id FROM layout_options WHERE field_id='street' AND form_id='DEM');
UPDATE `layout_options` SET `seq` = `seq`*10 WHERE group_id = @group_id AND form_id='DEM';
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='street' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`)
VALUES ('DEM', 'street_line_2', @group_id, 'Address Line 2', @seq_add_to+5, 2, 1, 25, 63, '', 1 , 1 , '', '[\"C\"]', 'Address Line 2', 0);
#Endif

#IfNotRow2D layout_options form_id DEM field_id em_street_line_2
SET @group_id = (SELECT group_id FROM layout_options WHERE field_id='em_street' AND form_id='DEM');
UPDATE `layout_options` SET `seq` = `seq`*10 WHERE group_id = @group_id AND form_id='DEM';
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='em_street' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`)
VALUES ('DEM', 'em_street_line_2', @group_id, 'Employer Address Line 2', @seq_add_to+5, 2, 1, 25, 63, '', 1 , 1 , '', '[\"C\"]', 'Address Line 2', 0);
#Endif

#IfNotTable payment_processing_audit
CREATE TABLE `payment_processing_audit` (
`uuid` binary(16) NOT NULL DEFAULT '',
`service` varchar(50) DEFAULT NULL,
`pid` bigint NOT NULL,
`success` tinyint DEFAULT 0,
`action_name` varchar(50) DEFAULT NULL,
`amount` varchar(20) DEFAULT NULL,
`ticket` varchar(100) DEFAULT NULL,
`transaction_id` varchar(100) DEFAULT NULL,
`audit_data` text,
`date` datetime DEFAULT NULL,
`map_uuid` binary(16) DEFAULT NULL,
`map_transaction_id` varchar(100) DEFAULT NULL,
`reverted` tinyint DEFAULT 0,
`revert_action_name` varchar(50) DEFAULT NULL,
`revert_transaction_id` varchar(100) DEFAULT NULL,
`revert_audit_data` text,
`revert_date` datetime DEFAULT NULL,
PRIMARY KEY (`uuid`),
KEY (`pid`),
KEY (`success`)
) ENGINE=InnoDB;
#EndIf

#IfNotRow2D layout_options form_id DEM field_id patient_groups
SET @group_id = (SELECT group_id FROM layout_options WHERE field_id='care_team_status' AND form_id='DEM');
UPDATE `layout_options` SET `seq` = `seq`*10 WHERE group_id = @group_id AND form_id='DEM';
SET @seq_add_to = (SELECT seq FROM layout_options WHERE group_id = @group_id AND field_id='care_team_status' AND form_id='DEM');
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`, `validation`, `codes`)
VALUES ('DEM','patient_groups',@group_id,'Patient Categories',@seq_add_to+5,36,1,0,0,'Patient_Groupings',1,1,'','[\"EP\",\"DAP\"]','Add patient to one or more category.',0,'','F','','','');

ALTER TABLE `patient_data` ADD `patient_groups` TEXT;
#Endif

#IfNotRow2D list_options list_id lists option_id Patient_Groupings
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('lists','Patient_Groupings','Patient Groupings',0,1,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Patient_Groupings','group_1','Group I',10,0,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Patient_Groupings','group_2','Group II',20,0,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Patient_Groupings','group_3','Group III',30,0,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Patient_Groupings','group_4','Group IV',40,0,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('Patient_Groupings','group_5','Group V',50,0,0);
#EndIf

#IfMissingColumn document_templates send_date
ALTER TABLE `document_templates` CHANGE `exclude_portal` `send_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, CHANGE `exclude_dashboard` `end_date` DATETIME DEFAULT NULL;
ALTER TABLE `document_templates` CHANGE `profile` `profile` VARCHAR(63) NOT NULL, CHANGE `category` `category` VARCHAR(63) NOT NULL;
ALTER TABLE `document_templates` DROP INDEX `location`, ADD UNIQUE `location` (`pid`, `profile`, `category`, `template_name`);
#EndIf

#IfMissingColumn document_template_profiles member_of
ALTER TABLE `document_template_profiles` ADD `member_of` VARCHAR(64) NOT NULL;
ALTER TABLE `document_template_profiles` ADD `active` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `document_template_profiles` ADD `recurring` TINYINT(1) NOT NULL DEFAULT '1',ADD `event_trigger` VARCHAR(31) NOT NULL, ADD `period` INT(4) NOT NULL;
ALTER TABLE `document_template_profiles` CHANGE `profile` `profile` VARCHAR(64) NOT NULL, CHANGE `category` `category` VARCHAR(64) NOT NULL, CHANGE `template_name` `template_name` VARCHAR(255) NOT NULL;
ALTER TABLE `document_template_profiles` DROP INDEX `location`, ADD UNIQUE `location` (`profile`, `template_id`, `member_of`);
#EndIf

-- Adding description as placeholder option
#IfUpdateEditOptionsNeeded Add DEM DAP fname,mname,lname,suffix,name_history,birth_fname,birth_mname,birth_lname
#EndIf

#IfNotRow3D layout_options form_id DEM field_id title datacols 3
UPDATE `layout_options` SET `datacols` = '3' WHERE `form_id` = 'DEM' AND `field_id` = 'title';
UPDATE `layout_options` SET `fld_length` = '15' WHERE `form_id` = 'DEM' AND `field_id` = 'fname';
UPDATE `layout_options` SET `fld_length` = '5' WHERE `form_id` = 'DEM' AND `field_id` = 'mname';
UPDATE `layout_options` SET `fld_length` = '20' WHERE `form_id` = 'DEM' AND `field_id` = 'lname';
UPDATE `layout_options` SET `fld_length` = '5' WHERE `form_id` = 'DEM' AND `field_id` = 'suffix';
UPDATE `layout_options` SET `datacols` = '1' WHERE `form_id` = 'DEM' AND `field_id` = 'status';
#EndIf

#IfNotRow3D layout_options form_id DEM field_id birth_fname datacols 3
UPDATE `layout_options` SET `fld_length` = '15', `datacols` = '3' WHERE `form_id` = 'DEM' AND `field_id` = 'birth_fname';
UPDATE `layout_options` SET `fld_length` = '5', `description` = 'Middle Name' WHERE `form_id` = 'DEM' AND `field_id` = 'birth_mname';
UPDATE `layout_options` SET `fld_length` = '20' WHERE `form_id` = 'DEM' AND `field_id` = 'birth_lname';
UPDATE `layout_options` SET `datacols` = '3' WHERE `form_id` = 'DEM' AND `field_id` = 'name_history';
#EndIf

-- Adding prepend row option
#IfUpdateEditOptionsNeeded Add DEM K pubpid,name_history
#EndIf

-- Adding Exclude in Portal option
#IfUpdateEditOptionsNeeded Add DEM EP care_team_provider,care_team_facility,care_team_status,regdate,referral_source,religion,ethnicity,race,ref_providerID
#EndIf

#IfNotRow2D list_options list_id lists option_id external_patient_education
INSERT INTO list_options (list_id,option_id,title,seq,is_default,option_value) VALUES ('lists', 'external_patient_education', 'External Patient Education', 0, 0, 0);
INSERT INTO list_options (list_id,option_id,title,notes,seq,is_default,activity) VALUES ('external_patient_education', 'emedicine', 'eMedicine', 'http://search.medscape.com/reference-search?newSearchHeader=1&queryText=[%]', 10, 0, 1);
INSERT INTO list_options (list_id,option_id,title,notes,seq,is_default,activity) VALUES ('external_patient_education', 'medline', 'Medline', 'http://vsearch.nlm.nih.gov/vivisimo/cgi-bin/query-meta?v%3Aproject=medlineplus&query=[%]&x=12&y=15', 20, 0, 1);
INSERT INTO list_options (list_id,option_id,title,notes,seq,is_default,activity) VALUES ('external_patient_education', 'webmd', 'WebMD', 'http://www.webmd.com/search/search_results/default.aspx?query=[%]&sourceType=undefined', 30, 0, 1);
#EndIf

#IfMissingColumn form_encounter referring_provider_id
ALTER TABLE `form_encounter` ADD `referring_provider_id` INT(11) DEFAULT '0' COMMENT 'referring provider, if any, for this visit';
#EndIf

-- drop if view was converted to a table
#IfTable onsite_activity_view
DROP TABLE IF EXISTS `onsite_activity_view`;
#EndIf

#IfNotTable verify_email
CREATE TABLE `verify_email` (
`id` bigint NOT NULL auto_increment,
`pid_holder` bigint DEFAULT NULL,
`email` varchar(255) DEFAULT NULL,
`language` varchar(100) DEFAULT NULL,
`fname` varchar(255) DEFAULT NULL,
`mname` varchar(255) DEFAULT NULL,
`lname` varchar(255) DEFAULT NULL,
`dob` date DEFAULT NULL,
`token_onetime`  VARCHAR(255) DEFAULT NULL,
`active` tinyint NOT NULL default 1,
PRIMARY KEY (`id`),
UNIQUE KEY (`email`)
) ENGINE=InnoDB;
#EndIf

