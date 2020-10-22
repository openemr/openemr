CREATE TABLE `form_prior_auth` (
  `id` int(11) NOT NULL auto_increment,
  `pid` int(11) default NULL,
  `activity` tinyint(4) NOT NULL default '0',
  `date` datetime default NULL,
  `prior_auth_number` varchar(35) default NULL,
  `comments` varchar(255) default NULL,
  `date_from` date default NULL,
  `date_to` date default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
