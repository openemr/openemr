#IfNotTable module_lbf_statement_forms
CREATE TABLE `module_lbf_statement_forms` (
  `form_id` varchar(31) NOT NULL,
  `paragraph_field_id` varchar(31) NOT NULL,
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable module_lbf_statement_rules
CREATE TABLE `module_lbf_statement_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` varchar(31) NOT NULL,
  `source_field_id` varchar(31) NOT NULL,
  `source_field_id_2` varchar(31) DEFAULT NULL,
  `op` varchar(32) NOT NULL,
  `min_value` decimal(12,4) DEFAULT NULL,
  `max_value` decimal(12,4) DEFAULT NULL,
  `min_inclusive` tinyint(1) NOT NULL DEFAULT 1,
  `max_inclusive` tinyint(1) NOT NULL DEFAULT 1,
  `match_token` varchar(255) DEFAULT NULL,
  `statement_text` text,
  `seq` int(11) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn module_lbf_statement_rules match_token
ALTER TABLE `module_lbf_statement_rules` ADD `match_token` varchar(255) DEFAULT NULL;
#EndIf

#IfNotColumnType module_lbf_statement_rules match_token varchar(255)
ALTER TABLE `module_lbf_statement_rules` MODIFY `match_token` varchar(255) DEFAULT NULL;
#EndIf

#IfNotTable module_lbf_statement_runs
CREATE TABLE `module_lbf_statement_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` varchar(31) NOT NULL,
  `pid` bigint(20) NOT NULL,
  `instance_form_id` int(11) NOT NULL,
  `user` varchar(255) DEFAULT NULL,
  `mode` varchar(16) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pid_instance` (`pid`,`instance_form_id`)
) ENGINE=InnoDB;
#EndIf
