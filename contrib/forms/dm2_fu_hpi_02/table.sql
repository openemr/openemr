CREATE TABLE IF NOT EXISTS `form_dm2_fu_hpi_01` (
`id` bigint(20) NOT NULL auto_increment,
`date` datetime default NULL,
`pid` bigint(20) default 0,
`user` varchar(255) default NULL,
`groupname` varchar(255) default NULL,
`authorized` tinyint(4) default 0,
`activity` tinyint(4) default 0,
`date_of_original_dm2_diagnosis` text,
`date_last_dm2_visit` text,
`dm2_mgmt_last_visit` text,
`intv_hx_chngs_bs_er_hosp` text,
`severity_significance` text,
`pt_diet_exercise` text,
`dm_complications` text,
`preventatives` text,
PRIMARY KEY (id)
) ENGINE=InnoDB;
