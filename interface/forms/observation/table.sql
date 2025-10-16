--
-- Table structure for table `form_observation`
--

CREATE TABLE `form_observation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) DEFAULT NULL,
  `form_id` bigint(20) NOT NULL COMMENT 'FK to forms.form_id',
  `date` DATETIME DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `observation` varchar(255) DEFAULT NULL,
  `ob_value` varchar(255),
  `ob_unit` varchar(255),
  `description` varchar(255),
  `code_type` varchar(255),
  `table_code` varchar(255),
  `ob_code` VARCHAR(64) DEFAULT NULL,
  `ob_type` VARCHAR(64) DEFAULT NULL,
  `ob_status` varchar(32) DEFAULT NULL,
  `result_status` varchar(32) DEFAULT NULL,
  `ob_reason_status` varchar(32) DEFAULT NULL,
  `ob_reason_code` varchar(64) DEFAULT NULL,
  `ob_reason_text` text,
  `ob_documentationof_table` varchar(255) DEFAULT NULL,
  `ob_documentationof_table_id` bigint(21) DEFAULT NULL,
  `date_end` DATETIME DEFAULT NULL,
  `parent_observation_id` bigint(20) DEFAULT NULL COMMENT 'FK to parent observation for sub-observations',
  `category` varchar(64) DEFAULT NULL COMMENT 'FK to list_options.option_id for observation category (SDOH, Functional, Cognitive, Physical, etc)',
  `questionnaire_response_id` bigint(21) DEFAULT NULL COMMENT 'FK to questionnaire_response table',
  PRIMARY KEY (`id`),
  KEY `idx_form_id` (`form_id`),
  KEY `idx_parent_observation` (`parent_observation_id`),
  KEY `idx_category` (`category`),
  KEY `idx_questionnaire_response` (`questionnaire_response_id`),
  KEY `idx_pid_encounter` (`pid`, `encounter`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB;

