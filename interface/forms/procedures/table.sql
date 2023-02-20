INSERT INTO `wmt_pop_forms` (`form_name`, `pop_form`, `archive_form`, `bill_form`, `screen_form`) VALUES ("procedures", "1", "0", "0", "0") ON DUPLICATE KEY UPDATE `pop_form` = VALUES(`pop_form`);

INSERT INTO `user_settings` (`setting_user`, `setting_label`, `setting_value`) VALUES 
("0", "wmt::float_menu_clear::procedures", "clearForm()"), 
("0", "wmt::float_menu_summary_link::procedures", "0"), 
("0", "wmt::auto_check_summary_amc::procedures", "0"), 
("0", "wmt::float_menu_problem_link::procedures", "0"), 
("0", "wmt::float_menu_vital_trend::procedures", "0"), 
("0", "wmt::float_menu_sugar_trend::procedures", "0"), 
("0", "wmt::float_menu_relink::procedures", "0"), 
("0", "wmt::float_menu_coding::procedures", "0"), 
("0", "wmt::float_menu_procedures::procedures", "0"), 
("0", "wmt::float_menu_procedures::dashboard", "0"), 
("0", "wmt::float_menu_save_quit::procedures", "1"), 
("0", "wmt::suppress_proc_plan", "0") 
ON DUPLICATE KEY UPDATE `setting_user`= VALUES(`setting_user`);

INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES 
("wmt::use_proc_favorites", "0", "1"), 
("wmt::include_billing_diags", "0", "1"), 
("wmt::proc_use_ajax", "0", "1") 
ON DUPLICATE KEY UPDATE `gl_index`= VALUES(`gl_index`);
