CREATE TABLE IF NOT EXISTS `form_rto` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `date` DATETIME DEFAULT NULL,
  `pid` BIGINT(20) DEFAULT NULL,
  `user` VARCHAR(255) DEFAULT NULL,
  `groupname` VARCHAR(255) DEFAULT NULL,
  `authorized` TINYINT(4) DEFAULT NULL,
  `activity` TINYINT(4) DEFAULT NULL,
	`rto_date` VARCHAR(16) DEFAULT NULL,
	`rto_type` VARCHAR(16) DEFAULT NULL,
  `rto_num` INT(8) DEFAULT NULL,
  `rto_frame` VARCHAR(20) DEFAULT NULL,
	`rto_target_date` VARCHAR(16) DEFAULT NULL,
  `rto_prn` TINYINT(1) DEFAULT NULL,
  `rto_notes` TEXT DEFAULT NULL,
  `rto_status` VARCHAR(2) DEFAULT NULL,
  `rto_resp_user` VARCHAR(255) DEFAULT NULL,
	`rto_action` VARCHAR(16) DEFAULT NULL,
  `rto_last_action` VARCHAR(16) DEFAULT NULL,
  `rto_last_resp_user` VARCHAR(255) DEFAULT NULL,
	`rto_extra` VARCHAR(8) DEFAULT NULL,
	`rto_last_touch` DATETIME DEFAULT NULL,
	`rto_ordered_by` VARCHAR(255) DEFAULT NULL,
	`rto_msg_trail` TEXT DEFAULT NULL,
	`rto_action_trail` TEXT DEFAULT NULL,
	`rto_repeat` TINYINT (1) DEFAULT 0,
	`rto_stop_date` VARCHAR(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS `wmt_rto_links` (
	`id` BIGINT(20) NOT NULL auto_increment,
	`form_name` VARCHAR(255) NOT NULL,
	`form_id` BIGINT(20),
	`rto_id` BIGINT(20),
	`pid` BIGINT(20),
	PRIMARY KEY (`id`),
	UNIQUE `form_and_rto` (`form_name`, `form_id`, `rto_id`)
) ENGINE=INNODB;

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `notes`) VALUES ("note_type", "Order", "Order", "100", "0", "") ON DUPLICATE KEY UPDATE `notes`= VALUES(`notes`);

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `notes`) VALUES ("lists", "RTO_Status", "RTO Status", "0", "1", "For Orders/RTO"), ("RTO_Status", "p", "Pending", "10", "0", "DO NOT DELETE"),("RTO_Status", "s", "Scheduled", "20", "0", "complete - DO NOT DELETE"), ("RTO_Status", "x", "Cancelled", "30", "0", "System Use - DO NOT DELETE"), ("RTO_Status", "c", "Complete", "40", "0", "complete - DO NOT DELETE") ON DUPLICATE KEY UPDATE `notes`= VALUES(`notes`);

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `notes`) VALUES ("lists", "RTO_Frame", "RTO Frame", "0", "1", "For Orders/RTO"), ("RTO_Frame", "d", "Day(s)", "10", "0", ""),("RTO_Frame", "w", "Week(s)", "20", "0", ""),("RTO_Frame", "m", "Month(s)", "30", "0", ""),("RTO_Frame", "y", "Year(s)", "40", "0", "") ON DUPLICATE KEY UPDATE `notes`= VALUES(`notes`);

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `notes`) VALUES ("lists", "RTO_Number", "RTO Number", "0", "1", "For Orders/RTO"), ("RTO_Number", "1", "1", "10", "0", ""),("RTO_Number", "2", "2", "20", "0", ""),("RTO_Number", "3", "3", "30", "0", ""),("RTO_Number", "4", "4", "40", "0", ""),("RTO_Number", "5", "5", "50", "0", ""),("RTO_Number", "6", "6", "60", "0", ""),("RTO_Number", "7", "7", "70", "0", ""),("RTO_Number", "8", "8", "80", "0", ""),("RTO_Number", "9", "9", "90", "0", ""),("RTO_Number", "10", "10", "100", "0", "") ON DUPLICATE KEY UPDATE `notes`= VALUES(`notes`);

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `notes`) VALUES ("lists", "RTO_Action", "RTO Action", "0", "1", "For Orders/RTO"), ("RTO_Action", "sa", "Schedule Appointment", "10", "0", "System Use - DO NOT DELETE"),("RTO_Action", "rr", "Review Results", "20", "0", "Sample List Entry For Orders/RTO"), ("RTO_Action", "ref_pend", "Referral Pending", "30", "0", "System Use - DO NOT DELETE"), ("RTO_Action", "ref_rcv", "Referral Received", "40", "0", "System Use - DO NOT DELETE") ON DUPLICATE KEY UPDATE `notes`= VALUES(`notes`);

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `notes`) VALUES 
("lists", "RTO_Link_Text", "RTO Link Text", "0", "1", "For Orders/RTO"), 
("RTO_Link_Text", "neu_psy", "Neuropsychology Referral Required", "10", "0", ""),
("RTO_Link_Text", "neuro", "Neurology Referral Required", "20", "0", ""),
("RTO_Link_Text", "pain", "Pain Clinic Referral Required", "30", "0", ""),
("RTO_Link_Text", "mri_lum", "MRI Lumbar Radiology Ordered", "40", "0", ""),
("RTO_Link_Text", "mri_thor", "MRI Thoracic Radiology Ordered", "50", "0", ""),
("RTO_Link_Text", "mri_cerv", "MRI Cervical Radiology Ordered", "60", "0", ""),
("RTO_Link_Text", "mri_brain", "MRI Brain Radiology Ordered", "70", "0", ""),
("RTO_Link_Text", "ct_lum", "CT Lumbar Radiology Ordered", "80", "0", ""),
("RTO_Link_Text", "ct_thor", "CT Thoracic Radiology Ordered", "90", "0", ""),
("RTO_Link_Text", "ct_cerv", "CT Cervical Radiology Ordered", "100", "0", ""),
("RTO_Link_Text", "ct_brain", "CT Brain Radiology Ordered", "110", "0", ""),
("RTO_Link_Text", "sc1_lab", "Patient Notified to Check-In for Lab Work", "120", "0", "") 
ON DUPLICATE KEY UPDATE `notes`= VALUES(`notes`);

INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES 
("wmt::rto_monitor_refresh", "", "120"), 
("wmt::rto_monitor_user", "", "") 
ON DUPLICATE KEY UPDATE `gl_name`= VALUES(`gl_name`);
