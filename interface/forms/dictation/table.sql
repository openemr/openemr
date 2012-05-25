CREATE TABLE IF NOT EXISTS `form_dictation` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,
dictation longtext,
additional_notes longtext,
PRIMARY KEY (id)
) ENGINE=MyISAM;
