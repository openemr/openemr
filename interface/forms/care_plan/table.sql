--
-- Table structure for table `form_care_plan`
--

CREATE TABLE IF NOT EXISTS `form_care_plan` (
  `id` bigint(20) NOT NULL,
  `date` DATE DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `codetext` text,
  `description` text,
  `external_id` VARCHAR(30) DEFAULT NULL,
  `care_plan_type` varchar(30) DEFAULT NULL,
  `note_related_to` TEXT
) ENGINE=InnoDB;

