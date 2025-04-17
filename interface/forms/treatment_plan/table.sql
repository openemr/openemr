--
-- Table structure for table `form_treatment_plan`
--

CREATE TABLE IF NOT EXISTS `form_treatment_plan` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `client_number` bigint(20) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `admit_date` varchar(255) DEFAULT NULL,
  `presenting_issues` text,
  `patient_history` text,
  `medications` text,
  `anyother_relevant_information` text,
  `diagnosis` text,
  `treatment_received` text,
  `recommendation_for_follow_up` text,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB;
