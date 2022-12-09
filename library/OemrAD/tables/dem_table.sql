#IfNotTable form_value_logs
CREATE TABLE IF NOT EXISTS `form_value_logs` (
  `field_id` varchar(255) NOT NULL,
  `form_name` varchar(255) NOT NULL,
  `new_value` text DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn form_value_logs id
ALTER TABLE `form_value_logs` ADD COLUMN `id` bigint(20) PRIMARY KEY AUTO_INCREMENT FIRST;
#EndIf

#IfMissingColumn form_value_logs form_id
ALTER TABLE `form_value_logs` ADD COLUMN `form_id` bigint(20) default NULL;
#EndIf

#IfMissingColumn form_value_logs username
ALTER TABLE `form_value_logs` ADD COLUMN `username` varchar(255) default NULL;
#EndIf