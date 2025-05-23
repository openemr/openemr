--
-- Table structure for table `form_transfer_summary`
--

CREATE TABLE IF NOT EXISTS `form_transfer_summary` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `transfer_to` varchar(255) DEFAULT NULL,
  `transfer_date` date DEFAULT NULL,
  `status_of_admission` text,
  `diagnosis` text,
  `intervention_provided` text,
  `overall_status_of_discharge` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

