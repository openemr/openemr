## 
## Table structure for table `batchcom`
## 

CREATE TABLE IF NOT EXISTS `batchcom` (
  `id` bigint(20) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL default '0',
  `sent_by` bigint(20) NOT NULL default '0',
  `msg_type` varchar(60) NOT NULL default '',
  `msg_subject` varchar(255) NOT NULL default '',
  `msg_text` mediumtext NOT NULL,
  `msg_date_sent` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;

ALTER TABLE billing
  MODIFY `code` varchar(9) DEFAULT NULL;
