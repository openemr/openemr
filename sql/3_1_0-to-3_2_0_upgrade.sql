#IfMissingColumn users calendar
ALTER TABLE `users` 
  ADD `calendar` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = appears in calendar';
UPDATE users SET calendar = 1 WHERE authorized = 1 AND ( info IS NULL OR info NOT LIKE '%Nocalendar%' );
#EndIf

#IfNotRow2D list_options list_id lists option_id lbfnames
INSERT INTO list_options VALUES ('lists','lbfnames','Layout-Based Visit Forms',9,0,0);
#EndIf

#IfNotTable lbf_data
CREATE TABLE `lbf_data` (
  `form_id`     int(11)      NOT NULL AUTO_INCREMENT COMMENT 'references forms.form_id',
  `field_id`    varchar(31)  NOT NULL COMMENT 'references layout_options.field_id',
  `field_value` varchar(255) NOT NULL,
  PRIMARY KEY (`form_id`,`field_id`)
) ENGINE=MyISAM COMMENT='contains all data from layout-based forms';
#EndIf

