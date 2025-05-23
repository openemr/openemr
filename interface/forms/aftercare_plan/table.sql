--
-- Table structure for table `form_aftercare_plan`
--

CREATE TABLE IF NOT EXISTS `form_aftercare_plan` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `admit_date` date DEFAULT NULL,
  `discharged` date DEFAULT NULL,
  `goal_a_acute_intoxication` text,
  `goal_a_acute_intoxication_I` text,
  `goal_a_acute_intoxication_II` text,
  `goal_b_emotional_behavioral_conditions` text,
  `goal_b_emotional_behavioral_conditions_I` text,
  `goal_c_relapse_potential` text,
  `goal_c_relapse_potential_I` text,
  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

