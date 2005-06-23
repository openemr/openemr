CREATE TABLE `form_prior_auth` (
  `id` int(11) NOT NULL auto_increment,
  `pid` int(11) default NULL,
  `activity` tinyint(4) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `prior_auth_number` varchar(35) default NULL,
  `comments` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;