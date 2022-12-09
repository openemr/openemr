CREATE TABLE IF NOT EXISTS `form_cases` (
	`id` BIGINT (20) NOT NULL AUTO_INCREMENT,
	`date` DATETIME DEFAULT NULL,
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`pid` BIGINT (20) DEFAULT 0,
	`user` VARCHAR(255) DEFAULT '',
	`groupname` VARCHAR(255) DEFAULT '',
	`authorized` TINYINT(4) DEFAULT 0,
	`activity` TINYINT(4) DEFAULT 0,
	`form_complete` VARCHAR(16) DEFAULT '',
	`form_priority` VARCHAR(16) DEFAULT '',
	`form_dt` DATE DEFAULT NULL,
	`case_dt` DATE DEFAULT NULL,

	`a_collected` VARCHAR(6)  DEFAULT '',
	`a_collected_dt` DATETIME DEFAULT NULL,
	
PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `wmt_pop_forms` (`form_name`, `pop_form`, `archive_form`, `bill_form`, `screen_form`) VALUES ("case", "0", "1", "0", "0") ON DUPLICATE KEY UPDATE `pop_form` = VALUES(`pop_form`);

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `codes`, `activity`) VALUES 
("lists", "cases_modules", "Case Management Modules", "0", "1", "", "1"), 
("cases_modules", "case_header", "Case Definition", "10", "0", "", "1"),
("cases_modules", "empl_pick", "Case Related Employer", "30", "0", "", "1"), 
("cases_modules", "case_dt", "Case Dates And Accident Summary", "40", "0", "", "1"), 
("cases_modules", "notes", "Case Related Notes", "50", "0", "specified_text_box", "1"), 
("cases_modules", "diag", "Case Related Diagnoses", "60", "0", "", "1"), 
("cases_modules", "comments", "Other Case Related Comments", "70", "0", "specified_text_box", "1") 
ON DUPLICATE KEY UPDATE `is_default`= VALUES(`is_default`);

INSERT INTO `user_settings` (`setting_user`, `setting_label`, `setting_value`) VALUES 
("0", "wmt::float_menu_clear::cases", "clearForm()"), 
("0", "wmt::suppress_status::cases", "1"), 
("0", "wmt::suppress_signature::cases", "1"), 
("0", "wmt::suppress_forms_entry::cases", "1"), 
("0", "wmt::include_pat_print_info::cases", "1") 
ON DUPLICATE KEY UPDATE `setting_user`= VALUES(`setting_user`);
