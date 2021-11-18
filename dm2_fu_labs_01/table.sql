CREATE TABLE IF NOT EXISTS `form_dm2_fu_labs_01` (
`id` bigint(20) NOT NULL auto_increment,
`date` datetime default NULL,
`pid` bigint(20) default 0,
`user` varchar(255) default NULL,
`groupname` varchar(255) default NULL,
`authorized` tinyint(4) default 0,
`activity` tinyint(4) default 0,
`date_of_labs` text,
`hgb_a1c` text,
`ldl` text,
`serum_creatinine` text,
`egfr` text,
`albumin_creatinine_ratio` text,
`prev_labs_comments` text,
PRIMARY KEY (id)
) ENGINE=InnoDB;
