--
-- Table structure for table `form_medical_evaluation1`
--

CREATE TABLE IF NOT EXISTS `form_medical_evaluation` (
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
  `service_date` varchar(255) DEFAULT NULL,
  `CC` text,
  `PE` text,
  `Dx` text,
  `Plan` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
