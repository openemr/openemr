CREATE TABLE IF NOT EXISTS `form_high_risk_screening_record` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,

record_1	longtext,
record_2	longtext,
record_3	longtext,
record_4	longtext,
record_5	longtext,
record_6	longtext,
record_7	longtext,
record_8	longtext,
record_9	longtext,
record_10	longtext,
record_11	longtext,
record_12	longtext,
record_13	longtext,


PRIMARY KEY (id)
) TYPE=MyISAM;
