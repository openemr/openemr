CREATE TABLE `form_review_of_systems` (
`id` int(11) NOT NULL auto_increment,
`date_tetnus_shot` varchar(12) default NULL,
`date_pneumonia_shot` varchar(12) default NULL,
`date_flu_shot` varchar(12) default NULL,
`date_pap_smear` varchar(12) default NULL,
`date_mammogram` varchar(12) default NULL,
`date_bone_density_scan` varchar(12) default NULL,
`abnormal_pap_smear` varchar(35) default NULL,
`abnormal_mammogram` varchar(35) default NULL,
`date_last_psa` varchar(35) default NULL,
`packs_per_day` varchar(35) default NULL,
`years_smoked` varchar(35) default NULL,
`alcohol_per_week` varchar(35) default NULL,
`recreational_drugs` varchar(35) default NULL,
`pid` int(11) default NULL,
`activity` tinyint(4) default NULL,
`date` datetime default NULL,
PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE `form_review_of_systems_checks` (
`id` int(11) NOT NULL auto_increment,
`foreign_id` int(11) NOT NULL default '0',
`name` varchar(100) NOT NULL default '',
PRIMARY KEY  (`id`)
) ENGINE=MyISAM;
