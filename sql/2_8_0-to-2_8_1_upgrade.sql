#IfNotTable lang_constants

## ########################################################

##
## Table structure for table `lang_constants`
##

CREATE TABLE IF NOT EXISTS `lang_constants` (
  `cons_id` int(11) NOT NULL auto_increment,
  `constant_name` varchar(255) NOT NULL default '',
  UNIQUE KEY `cons_id` (`cons_id`),
  KEY `cons_name` (`constant_name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 ;

##
## Dumping data for table `lang_constants`
##

## ########################################################

##
## Table structure for table `lang_definitions`
##

CREATE TABLE IF NOT EXISTS `lang_definitions` (
  `def_id` int(11) NOT NULL auto_increment,
  `cons_id` int(11) NOT NULL default '0',
  `lang_id` int(11) NOT NULL default '0',
  `definition` mediumtext NOT NULL,
  UNIQUE KEY `def_id` (`def_id`),
  KEY `definition` (`definition`(100))
) ENGINE=MyISAM ;

##
## Dumping data for table `lang_definitions`
##


## ########################################################

##
## Table structure for table `lang_languages`
##

CREATE TABLE IF NOT EXISTS `lang_languages` (
  `lang_id` int(11) NOT NULL auto_increment,
  `lang_code` char(2) NOT NULL default '',
  `lang_description` varchar(100) NOT NULL default '',
  UNIQUE KEY `lang_id` (`lang_id`)
) ENGINE=MyISAM ;

##
## Dumping data for table `lang_languages`
##

INSERT INTO `lang_languages` (`lang_id`, `lang_code`, `lang_description`) VALUES (1, 'en', 'English');
INSERT INTO `lang_languages` (`lang_id`, `lang_code`, `lang_description`) VALUES (2, 'se', 'Swedish');
INSERT INTO `lang_languages` (`lang_id`, `lang_code`, `lang_description`) VALUES (3, 'es', 'Spanish');

#EndIf

#IfMissingColumn lists outcome
ALTER TABLE lists
  ADD outcome     int(11) NOT NULL DEFAULT 0,
  ADD destination varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn pnotes title
ALTER TABLE pnotes
  ADD title       varchar(255) NOT NULL DEFAULT 'Unassigned',
  ADD assigned_to varchar(255) NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn users see_auth
ALTER TABLE users ADD see_auth int(11) NOT NULL DEFAULT 1;
UPDATE users SET see_auth = 3 WHERE authorized = 1;
#EndIf

#IfNotTable payments
CREATE TABLE `payments` (
  `id`          bigint(20)    NOT NULL auto_increment,
  `pid`         bigint(20)    NOT NULL DEFAULT 0,
  `dtime`       datetime      NOT NULL,
  `user`        varchar(255)  NOT NULL DEFAULT '',
  `method`      varchar(255)  NOT NULL DEFAULT '',
  `source`      varchar(255)  NOT NULL DEFAULT '',
  `amount1`     decimal(7,2)  NOT NULL DEFAULT 0,
  `amount2`     decimal(7,2)  NOT NULL DEFAULT 0,
  `posted1`     decimal(7,2)  NOT NULL DEFAULT 0,
  `posted2`     decimal(7,2)  NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
) ;
#EndIf

#IfNotColumnType form_vitals temperature float(5,2)
ALTER TABLE form_vitals
  MODIFY temperature float(5,2) default 0,
  MODIFY pulse       float(5,2) default 0,
  MODIFY respiration float(5,2) default 0,
  MODIFY waist_circ  float(5,2) default 0;
#EndIf

#IfNotColumnType form_clinical_notes followup_required int(11)
ALTER TABLE form_clinical_notes
  MODIFY followup_required int(11) NOT NULL DEFAULT 0;
#EndIf
