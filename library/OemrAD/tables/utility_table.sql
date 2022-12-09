#IfNotTable preserve_section_values
CREATE TABLE IF NOT EXISTS `preserve_section_values` (
  `fl_name` varchar(200) NOT NULL,
  `value` LONGTEXT NOT NULL,
  PRIMARY KEY (`fl_name`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn patient_data alert_info
ALTER TABLE `patient_data` ADD COLUMN `alert_info` TEXT NOT NULL default '';
INSERT INTO `layout_options` (`form_id`,`field_id`,`group_id`,`title`,`seq`,`data_type`,`uor`,`fld_length`,`max_length`,`list_id`,`titlecols`,`datacols`,`default_value`,`edit_options`,`description`,`fld_rows`) VALUES ('DEM', 'alert_info', '6', 'Alert', 3, 2, 1, 30, 255, '', 1, 1, '', '', 'Alert', 0);
#EndIf

#IfMissingColumn preserve_section_values uid
ALTER TABLE `preserve_section_values` DROP PRIMARY KEY;
ALTER TABLE `preserve_section_values` ADD COLUMN `uid` bigint(20) default NULL FIRST;
#EndIf

#IfMissingColumn users auto_confirm_appt
ALTER TABLE `users` ADD COLUMN `auto_confirm_appt` tinyint(1) default 0 AFTER `physician_type` ;
#EndIf

#IfMissingColumn facility name1
ALTER TABLE `facility` ADD COLUMN `name1` varchar(255) default NULL AFTER `name` ;
#EndIf