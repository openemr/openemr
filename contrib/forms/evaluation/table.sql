CREATE TABLE `form_evaluation` (
  `id` int(11) NOT NULL auto_increment,
  `temp` varchar(8) default NULL,
  `p` varchar(8) default NULL,
  `r` varchar(8) default NULL,
  `bp` varchar(10) default NULL,
  `ht` varchar(6) default NULL,
  `wt` varchar(6) default NULL,
  `bmi` char(3) default NULL,
  `lmp` varchar(10) default NULL,
  `complaint` varchar(255) default NULL,
  `hpi` text,
  `eyes_od` varchar(10) default NULL,
  `eyes_os` varchar(10) default NULL,
  `eyes_ou` varchar(10) default NULL,
  `comments` text,
  `assesment` text,
  `pid` int(11) default NULL,
  `activity` tinyint(4) default NULL,
  `date` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE `form_evaluation_checks` (
  `id` int(11) NOT NULL auto_increment,
  `foreign_id` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;
