#IfMissingColumn users calendar
ALTER TABLE `users` 
  ADD `calendar` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = appears in calendar';
UPDATE users SET calendar = 1 WHERE authorized = 1 AND ( info IS NULL OR info NOT LIKE '%Nocalendar%' );
#EndIf

#IfNotRow2D list_options list_id lists option_id lbfnames
INSERT INTO list_options ( list_id, option_id, title, seq ) VALUES ('lists','lbfnames','Layout-Based Visit Forms',9);
#EndIf

#IfNotTable lbf_data
CREATE TABLE `lbf_data` (
  `form_id`     int(11)      NOT NULL AUTO_INCREMENT COMMENT 'references forms.form_id',
  `field_id`    varchar(31)  NOT NULL COMMENT 'references layout_options.field_id',
  `field_value` varchar(255) NOT NULL,
  PRIMARY KEY (`form_id`,`field_id`)
) ENGINE=MyISAM COMMENT='contains all data from layout-based forms';
#EndIf

#IfMissingColumn form_encounter supervisor_id
ALTER TABLE `form_encounter` 
  ADD `supervisor_id` INT(11) DEFAULT '0' COMMENT 'supervising provider, if any, for this visit';
#EndIf

#IfMissingColumn list_options mapping
ALTER TABLE `list_options` 
  ADD `mapping` varchar(15) NOT NULL DEFAULT '';
#EndIf

#IfNotTable gprelations
CREATE TABLE gprelations (
  -- Relation types are:
  --  1 documents
  --  2 form_encounter (visits)
  --  3 immunizations
  --  4 lists (issues)
  --  5 openemr_postcalendar_events (appointments)
  --  6 pnotes
  --  7 prescriptions
  --  8 transactions (e.g. referrals)
  -- By convention we require that type1 must be less than or equal to type2.
  type1 int(2)     NOT NULL,
  id1   bigint(20) NOT NULL,
  type2 int(2)     NOT NULL,
  id2   bigint(20) NOT NULL,
  PRIMARY KEY (type1,id1,type2,id2),
  KEY key2  (type2,id2)
) ENGINE=MyISAM COMMENT='general purpose relations';
#EndIf

#IfMissingColumn insurance_companies alt_cms_id
ALTER TABLE `insurance_companies` 
  ADD `alt_cms_id` varchar(15) NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn x12_partners x12_isa05
ALTER TABLE `x12_partners` 
  ADD `x12_isa05` char(2)     NOT NULL DEFAULT 'ZZ',
  ADD `x12_isa07` char(2)     NOT NULL DEFAULT 'ZZ',
  ADD `x12_isa14` char(1)     NOT NULL DEFAULT '0',
  ADD `x12_isa15` char(1)     NOT NULL DEFAULT 'P',
  ADD `x12_gs02`  varchar(15) NOT NULL DEFAULT '',
  ADD `x12_per06` varchar(80) NOT NULL DEFAULT '';
#EndIf

