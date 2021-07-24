CREATE TABLE `form_hp_tje_checks` (
  `id` int(11) NOT NULL auto_increment,
  `foreign_id` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;

CREATE TABLE `form_hp_tje_history` (
  `id` int(11) NOT NULL auto_increment,
  `foreign_id` int(11) NOT NULL default '0',
  `doctor` varchar(255) default NULL,
  `specialty` varchar(255) default NULL,
  `tx_rendered` varchar(255) default NULL,
  `effectiveness` varchar(255) default NULL,
  `date` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;

CREATE TABLE `form_hp_tje_previous_accidents` (
  `id` int(11) NOT NULL auto_increment,
  `foreign_id` int(11) NOT NULL default '0',
  `nature_of_accident` varchar(255) default NULL,
  `injuries` varchar(255) default NULL,
  `date` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;

CREATE TABLE `form_hp_tje_primary` (
  `id` int(11) NOT NULL auto_increment,
  `referred_by` varchar(255) default NULL,
  `complaints` varchar(255) default NULL,
  `date_of_onset` date default NULL,
  `event` int(11) default NULL,
  `event_description` varchar(255) default NULL,
  `prior_symptoms` tinyint(4) default NULL,
  `aggravated_symptoms` tinyint(4) default NULL,
  `comments` varchar(255) default NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `teeth_sore_number` varchar(5) default NULL,
  `teeth_mobile_number` varchar(5) default NULL,
  `teeth_fractured_number` varchar(5) default NULL,
  `teeth_avulsed_number` varchar(5) default NULL,
  `precipitating_factors_other_text` varchar(255) default NULL,
  `pid` int(11) default NULL,
  `activity` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;
