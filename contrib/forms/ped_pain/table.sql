CREATE TABLE IF NOT EXISTS `form_ped_pain` (

`id` bigint(20) NOT NULL auto_increment,
`date` datetime default NULL,
`pid` bigint(20) default NULL,
`user` varchar(255) default NULL,
`groupname` varchar(255) default NULL,
`authorized` tinyint(4) default NULL,
`activity` tinyint(4) default NULL,

`location` VARCHAR (255) default NULL,
`duration` VARCHAR (255) default NULL,
`severity` VARCHAR (255) default NULL,
`fever` VARCHAR (255) default NULL,
`lethargy` VARCHAR (255) default NULL,
`vomiting` VARCHAR (255) default NULL,
`oral_hydration_capable` VARCHAR (255) default NULL,
`urine_output_last_6_hours` VARCHAR (255) default NULL,
`pain_with_urination` VARCHAR (255) default NULL,
`cough_or_breathing_difficulty` VARCHAR (255) default NULL,
`able_to_sleep` VARCHAR (255) default NULL,
`nasal_discharge` VARCHAR (255) default NULL,
`previous_hospitalization` VARCHAR (255) default NULL,
`siblings_affected` VARCHAR (255) default NULL,
`immunization_up_to_date` VARCHAR (255) default NULL,
`notes` VARCHAR (255) default NULL,
PRIMARY KEY (id)
) ENGINE=MyISAM;
