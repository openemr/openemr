CREATE TABLE IF NOT EXISTS `form_Gestational_Age` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,
lmp DATE,
edc DATE,
weeks TEXT,
days TEXT,

PRIMARY KEY (id)
) ENGINE=InnoDB;
