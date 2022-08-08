--
-- form_questionnaire_assessments
--
CREATE TABLE `form_questionnaire_assessments` (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT current_timestamp(),
  `last_date` datetime DEFAULT NULL,
  `pid` bigint(21) NOT NULL DEFAULT 0,
  `user` bigint(21) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) NOT NULL DEFAULT 0,
  `activity` tinyint(4) NOT NULL DEFAULT 1,
  `copyright` text,
  `form_name` varchar(255) DEFAULT NULL,
  `code` varchar(31) DEFAULT NULL,
  `code_type` varchar(31) DEFAULT "LOINC",
  `questionnaire` longtext,
  `questionnaire_response` longtext,
  `lform` longtext,
  `lform_response` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
