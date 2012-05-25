CREATE TABLE `form_well_infant` (
  `id` int(11) NOT NULL auto_increment,
  `wt` varchar(6) default NULL,
  `ht` varchar(6) default NULL,
  `hcirc` varchar(6) default NULL,
  `t` varchar(6) default NULL,
  `years` varchar(6) default NULL,
  `months` varchar(6) default NULL,
  `wt_percentile` varchar(4) default NULL,
  `ht_percentile` varchar(4) default NULL,
  `hcirc_percentile` varchar(4) NOT NULL default '',
  `head_open_cm` varchar(4) default NULL,
  `history` text,
  `feeding_oz` varchar(4) default NULL,
  `feeding_24h` varchar(4) default NULL,
  `formula_type` varchar(60) default NULL,
  `additional_findings` text,
  `assesment` text,
  `hct` varchar(10) default NULL,
  `lead` varchar(10) default NULL,
  `ppd` varchar(4) default NULL,
  `feeding` varchar(150) NOT NULL default '',
  `advice` varchar(255) default NULL,
  `rtc` varchar(150) default NULL,
  `pid` int(11) default NULL,
  `activity` tinyint(4) default NULL,
  `date` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE `form_well_infant_checks` (
  `id` int(11) NOT NULL auto_increment,
  `foreign_id` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;
