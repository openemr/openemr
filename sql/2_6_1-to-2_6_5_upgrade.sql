ALTER TABLE `openemr_postcalendar_categories` ADD `pc_recurrtype` INT( 1 ) DEFAULT '0' NOT NULL ,
ADD `pc_recurrspec` TEXT,
ADD `pc_recurrfreq` INT( 3 ) DEFAULT '0' NOT NULL ,
ADD `pc_endDate` DATE,
ADD `pc_duration` BIGINT( 20 ) DEFAULT '0' NOT NULL ,
ADD `pc_end_date_flag` TINYINT DEFAULT '0' NOT NULL,
ADD `pc_end_date_type` INT( 2 ) ,
ADD `pc_end_date_freq` INT( 11 ) DEFAULT '0' NOT NULL,
ADD `pc_end_all_day` TINYINT(1) DEFAULT '0' NOT NULL,
ADD `pc_dailylimit` INT( 2 ) DEFAULT '0' NOT NULL ;

CREATE TABLE `openemr_postcalendar_limits` (
`pc_limitid` INT NOT NULL AUTO_INCREMENT ,
`pc_catid` INT NOT NULL ,
`pc_starttime` TIME NOT NULL ,
`pc_endtime` TIME NOT NULL ,
`pc_limit` INT DEFAULT '1' NOT NULL ,
PRIMARY KEY ( `pc_limitid` )
);
