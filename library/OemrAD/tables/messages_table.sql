#IfNotTable message_log
CREATE TABLE IF NOT EXISTS `message_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activity` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(8) NOT NULL COMMENT 'SMS or EMAIL',
  `gateway` varchar(255) NOT NULL,
  `direction` varchar(8) DEFAULT NULL,
  `event` varchar(255) NOT NULL,
  `userid` bigint(20) DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `eid` bigint(20) DEFAULT NULL,
  `assigned` bigint(20) DEFAULT NULL,
  `assign_group` varchar(255) DEFAULT NULL,
  `msg_to` varchar(255) DEFAULT NULL,
  `msg_from` varchar(255) DEFAULT NULL,
  `receivers_name` varchar(255) NOT NULL DEFAULT '',
  `msg_convid` varchar(255) DEFAULT NULL,
  `msg_refid` varchar(255) DEFAULT NULL,
  `msg_newid` varchar(255) DEFAULT NULL,
  `msg_time` datetime DEFAULT NULL,
  `msg_status` varchar(255) DEFAULT NULL,
  `delivered_time` datetime DEFAULT NULL,
  `delivered_status` varchar(255) DEFAULT NULL,
  `message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable message_attachments
CREATE TABLE IF NOT EXISTS `message_attachments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) NOT NULL,
  `type` varchar(100) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn message_attachments doc_id
ALTER TABLE `message_attachments` ADD COLUMN `doc_id` bigint(20) default NULL AFTER `message_id`;
#EndIf

#IfMissingColumn patient_data fax_number
ALTER TABLE `patient_data` ADD COLUMN `fax_number` varchar(255) NOT NULL default '';
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'fax_number', '2', 'Fax', 15, 2, 1, 30, 95, '', 1, 1, '', '', 'Fax Number', 0);
#EndIf

#IfNotTable postal_letters
CREATE TABLE IF NOT EXISTS `postal_letters` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `letter_id` bigint(20) NOT NULL,
  `message_id` bigint(20) NOT NULL,
  `status_code` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `last_update_time` datetime NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `update_date` datetime NOT NULL DEFAULT current_timestamp(),
  `create_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable fax_messages
CREATE TABLE IF NOT EXISTS `fax_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fax_id` bigint(20) DEFAULT NULL,
  `message_id` bigint(20) NOT NULL,
  `status_code` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `update_date` datetime NOT NULL DEFAULT current_timestamp(),
  `create_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable email_verifications
CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `field_name` varchar(50) DEFAULT NULL,
  `field_value` varchar(255) DEFAULT NULL,
  `verification_status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn patient_data secondary_email
ALTER TABLE `patient_data` ADD COLUMN `secondary_email` varchar(255) NOT NULL default '';
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'secondary_email', '2', 'Secondary Email', 14, 2, 1, 30, 95, '', 1, 1, '', '', 'Secondary Email', 0);
#EndIf

#IfNotColumnType patient_data secondary_email TEXT
ALTER TABLE `patient_data` MODIFY `secondary_email` TEXT;
UPDATE `layout_options` SET `data_type`='41', `max_length` = '1000' WHERE `form_id` = "DEM" AND `field_id` = "secondary_email";
#EndIf

#IfMissingColumn fax_messages receivers_name
ALTER TABLE `fax_messages` ADD COLUMN `receivers_name` varchar(255) NOT NULL default '';
#EndIf

#IfMissingColumn patient_data secondary_phone_cell
ALTER TABLE `patient_data` ADD COLUMN `secondary_phone_cell` TEXT NOT NULL default '';
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'secondary_phone_cell', '2', 'Secondary Cell Phone', 12, 41, 1, 30, 95, '', 1, 1, '', '', 'Secondary Cell Phone', 0);
#EndIf

#IfMissingColumn message_log receivers_name
ALTER TABLE `message_log` ADD COLUMN `receivers_name` varchar(255) NOT NULL default '' AFTER `msg_from`;
#EndIf

#IfMissingColumn message_log raw_data
ALTER TABLE `message_log` ADD COLUMN `raw_data` LONGTEXT;
#EndIf

#IfMissingColumn message_log message_subject
ALTER TABLE `message_log` ADD COLUMN `message_subject` varchar(255) AFTER `delivered_status`;
#EndIf

#UPDATE `layout_options` SET `seq`='13' WHERE `form_id` = "DEM" AND `field_id` = "email_direct";
#UPDATE `layout_options` SET `seq`='14' WHERE `form_id` = "DEM" AND `field_id` = "secondary_email";

#IfNotColumnType message_log message LONGTEXT
ALTER TABLE `message_log` MODIFY `message` LONGTEXT;
#EndIf
