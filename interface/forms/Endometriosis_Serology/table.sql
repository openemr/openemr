CREATE TABLE IF NOT EXISTS `form_Endometriosis_Serology` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,
serum_il_1beta TEXT,
serum_il_6 TEXT,
serum_tnf_alpha TEXT,
probability_of_endometriosis TEXT,

PRIMARY KEY (id)
) ENGINE=InnoDB;
