CREATE TABLE IF NOT EXISTS `form_vitals` (

`id` bigint(20) NOT NULL auto_increment,
`date` datetime default NULL,
`pid` bigint(20) default NULL,
`user` varchar(255) default NULL,
`groupname` varchar(255) default NULL,
`authorized` tinyint(4) default NULL,
`activity` tinyint(4) default NULL,
`bps` VARCHAR (255) default NULL,
`bpd` VARCHAR (255) default NULL,
`weight` VARCHAR (255) default NULL,
`height` VARCHAR (255) default NULL,
`temperature` VARCHAR (255) default NULL,
`temp_method` VARCHAR (255) default NULL,
`pulse` VARCHAR (255) default NULL,
`respiration` VARCHAR (255) default NULL,
`note` VARCHAR (255) default NULL,
`BMI` VARCHAR (255) default NULL,
`BMI_status` VARCHAR (255) default NULL,
`waist_circ` VARCHAR (255) default NULL,

PRIMARY KEY (id)
) TYPE=MyISAM;
