CREATE TABLE IF NOT EXISTS `form_soap` (
`id` bigint(20) NOT NULL auto_increment,
`date` datetime default NULL,
`pid` bigint(20) default 0,
`user` varchar(255) default NULL,
`groupname` varchar(255) default NULL,
`authorized` tinyint(4) default 0,
`activity` tinyint(4) default 0,
`subjective` text default NULL,
`objective` text default NULL,
`assessment` text default NULL,
`plan` text default NULL,
PRIMARY KEY (id)
) TYPE=InnoDB;
