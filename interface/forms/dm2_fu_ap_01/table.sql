CREATE TABLE IF NOT EXISTS `form_dm2_fu_ap_01` (
`id` bigint(20) NOT NULL auto_increment,
`date` datetime default NULL,
`pid` bigint(20) default 0,
`user` varchar(255) default NULL,
`groupname` varchar(255) default NULL,
`authorized` tinyint(4) default 0,
`activity` tinyint(4) default 0,
`dm2_mgmt_status` text,
`dm2_medications` text,
`dm2_referrals` text,
`dm2_goals` text,
`dm2_labs_procedures_ordered` text,
`pt_diet_exercise` text,
`dm_complications` text,
`preventatives` text,
PRIMARY KEY (id)
) ENGINE=InnoDB;
