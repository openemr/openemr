#IfMissingColumn form_cases lb_date
ALTER TABLE `form_cases` ADD COLUMN `lb_date` date default NULL;
#EndIf

#IfMissingColumn form_cases lb_notes
ALTER TABLE `form_cases` ADD COLUMN `lb_notes` 	text default NULL;
#EndIf

#IfNotTable case_form_value_logs
CREATE TABLE IF NOT EXISTS `case_form_value_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `case_id` varchar(255) NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable vh_pi_case_management_details
CREATE TABLE IF NOT EXISTS `vh_pi_case_management_details` (
  `case_id` varchar(255) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_index` int(11) DEFAULT 0,
  `field_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn form_cases sc_referring_id
ALTER TABLE `form_cases` ADD COLUMN `sc_referring_id` LONGTEXT default NULL AFTER `referring_id`;;
#EndIf

#IfMissingColumn form_cases auth_req
ALTER TABLE `form_cases` ADD COLUMN `auth_req` TINYINT default 0 AFTER `lb_notes`;
#EndIf

#IfMissingColumn form_cases auth_start_date
ALTER TABLE `form_cases` ADD COLUMN `auth_start_date` varchar(16) default NULL AFTER `auth_req`;
#EndIf

#IfMissingColumn form_cases auth_end_date
ALTER TABLE `form_cases` ADD COLUMN `auth_end_date` varchar(16) default NULL AFTER `auth_start_date`;
#EndIf

#IfMissingColumn form_cases auth_num_visit
ALTER TABLE `form_cases` ADD COLUMN `auth_num_visit` varchar(100) default NULL AFTER `auth_end_date`;
#EndIf

#IfMissingColumn form_cases auth_notes
ALTER TABLE `form_cases` ADD COLUMN `auth_notes` TEXT NULL AFTER `auth_num_visit`;
#EndIf

#IfMissingColumn form_cases auth_provider
ALTER TABLE `form_cases` ADD COLUMN `auth_provider` varchar(255) default NULL AFTER `auth_num_visit`;
#EndIf

#IfMissingColumn form_cases liability_payer_exists
ALTER TABLE `form_cases` ADD COLUMN `liability_payer_exists` int(11) default 0 AFTER `auth_provider`;
#EndIf

#IfMissingColumn form_cases bc_date
ALTER TABLE `form_cases` ADD COLUMN `bc_date` varchar(16) default NULL AFTER `auth_provider`;
#EndIf

#IfMissingColumn form_cases bc_notes
ALTER TABLE `form_cases` ADD COLUMN `bc_notes` varchar(255) default NULL AFTER `bc_date`;
#EndIf

#IfMissingColumn form_cases bc_notes_dsc
ALTER TABLE `form_cases` ADD COLUMN `bc_notes_dsc` TEXT default NULL AFTER `bc_notes`;
#EndIf

#IfMissingColumn form_cases bc_stat
ALTER TABLE `form_cases` ADD COLUMN `bc_stat` TINYINT default 0 AFTER `bc_notes_dsc`;
#EndIf

#IfMissingColumn form_cases bc_created_time
ALTER TABLE `form_cases` ADD COLUMN `bc_created_time` TEXT default NULL AFTER `bc_notes_dsc`;
#EndIf

#IfMissingColumn form_cases bc_update_time
ALTER TABLE `form_cases` ADD COLUMN `bc_update_time` TEXT default NULL AFTER `bc_created_time`;
#EndIf

#IfMissingColumn users ct_communication
ALTER TABLE `users` ADD COLUMN `ct_communication` varchar(255) default NULL;
#EndIf