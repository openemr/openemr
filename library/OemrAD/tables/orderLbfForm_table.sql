#IfNotTable form_order_layout
CREATE TABLE IF NOT EXISTS `form_order_layout` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`date` datetime DEFAULT NULL,
	`rto_id` bigint(20) DEFAULT NULL,
	`form_name` longtext DEFAULT NULL,
	`form_id` bigint(20) DEFAULT NULL,
	`pid` bigint(20) DEFAULT NULL,
	`user` varchar(255) DEFAULT NULL,
	`groupname` varchar(255) DEFAULT NULL,
	`authorized` tinyint(4) DEFAULT NULL,
	`deleted` tinyint(4) NOT NULL DEFAULT 0,
	`formdir` longtext DEFAULT NULL,
	`therapy_group_id` int(11) DEFAULT NULL,
	`issue_id` bigint(20) NOT NULL DEFAULT 0,
	`provider_id` bigint(20) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable rto_action_logs
CREATE TABLE IF NOT EXISTS `rto_action_logs` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`type` varchar(255) DEFAULT NULL,
	`rto_id` bigint(20) DEFAULT NULL,
	`foreign_id` bigint(20) DEFAULT NULL,
	`sent_to` varchar(255) DEFAULT NULL,
	`pid` bigint(20) DEFAULT NULL,
	`created_by` varchar(255) DEFAULT NULL,
	`created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (rto_id)
        REFERENCES form_rto (id)
        ON DELETE CASCADE,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn rto_action_logs operation
ALTER TABLE `rto_action_logs` ADD COLUMN `operation` varchar(255) DEFAULT NULL AFTER `pid`;
#EndIf

#IfMissingColumn form_rto rto_case
ALTER TABLE `form_rto` ADD COLUMN `rto_case` varchar(255) DEFAULT NULL AFTER `rto_ordered_by`;
#EndIf

#IfMissingColumn form_rto rto_stat
ALTER TABLE `form_rto` ADD COLUMN `rto_stat` tinyint(1) default 0 AFTER `rto_ordered_by`;
#EndIf