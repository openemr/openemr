CREATE TABLE IF NOT EXISTS `wmt_diag_fav` (
	`id` BIGINT(20) NOT NULL auto_increment,
	`date` DATETIME DEFAULT NULL,
	`user` VARCHAR(255) DEFAULT NULL,
	`code_type` VARCHAR(15) DEFAULT NULL,
	`code` VARCHAR(15) DEFAULT NULL,
	`seq` VARCHAR(8) DEFAULT NULL,
	`title` VARCHAR(255) DEFAULT NULL,
	`list_user` VARCHAR(255) DEFAULT NULL,
	`global_list` TINYINT(1) DEFAULT 0,
	`grp` VARCHAR(32) DEFAULT NULL,
	`modifier` VARCHAR(8) DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE `grp_idx` (`list_user`, `code_type`, `grp`, `code`, `modifier`)
) ENGINE=InnoDB;

INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES 
("wmt::use_diag_favorites", "0", "1"), 
("wmt::use_cpt_favorites", "0", "1"), 
("wmt::default_diag_type", "0", "ICD10") 
ON DUPLICATE KEY UPDATE `gl_value`= VALUES(`gl_value`);

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `notes`) VALUES ("lists", "Diagnosis_Categories", "Diagnosis Categories", "0", "1", "Multi-Use List"), ("Diagnosis_Categories", "general", "General", "10", "0", "") ON DUPLICATE KEY UPDATE `notes`= VALUES(`notes`);

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `notes`) VALUES ("lists", "Procedure_Categories", "Procedure Categories", "0", "1", "Multi-Use List"), ("Procedure_Categories", "general", "General", "10", "0", "") ON DUPLICATE KEY UPDATE `notes`= VALUES(`notes`);
