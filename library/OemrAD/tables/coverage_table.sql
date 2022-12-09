#IfNotTable coverage_eligibility
CREATE TABLE IF NOT EXISTS `coverage_eligibility` (
 `id` bigint(20) NOT NULL AUTO_INCREMENT,
 `pid` bigint(20) NOT NULL,
 `case_id` bigint(20) NOT NULL,
 `ins_id` bigint(20) NOT NULL,
 `cnt` int(11) NOT NULL,
 `provider_id` bigint(20) NOT NULL,
 `policy_number` varchar(255) NOT NULL,
 `group_number` varchar(255) NOT NULL,
 `effective_date` datetime NOT NULL,
 `coverage_id` varchar(100) NOT NULL,
 `raw_data` text NOT NULL,
 `plan` varchar(255) NOT NULL,
 `plan_status` varchar(255) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable coverage_eligibility_history
CREATE TABLE IF NOT EXISTS `coverage_eligibility_history` (
 `id` bigint(20) NOT NULL AUTO_INCREMENT,
 `pid` bigint(20) NOT NULL,
 `case_id` bigint(20) NOT NULL,
 `ins_id` bigint(20) NOT NULL,
 `cnt` int(11) NOT NULL,
 `provider_id` bigint(20) NOT NULL,
 `policy_number` varchar(255) NOT NULL,
 `group_number` varchar(255) NOT NULL,
 `effective_date` datetime NOT NULL,
 `coverage_id` varchar(255) NOT NULL,
 `coverage_data` text NOT NULL,
 `plan` varchar(255) NOT NULL,
 `plan_status` varchar(255) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn coverage_eligibility uid
ALTER TABLE `coverage_eligibility` ADD COLUMN `uid` varchar(255) NOT NULL default '' AFTER `pid`;
#EndIf

#IfMissingColumn coverage_eligibility_history uid
ALTER TABLE `coverage_eligibility_history` ADD COLUMN `uid` varchar(255) NOT NULL default '' AFTER `pid`;
#EndIf

#IfNotColumnType coverage_eligibility raw_data LONGTEXT
ALTER TABLE `coverage_eligibility` MODIFY `raw_data` LONGTEXT;
#EndIf

#IfNotColumnType coverage_eligibility_history coverage_data LONGTEXT
ALTER TABLE `coverage_eligibility_history` MODIFY `coverage_data` LONGTEXT;
#EndIf