--
-- Table structure for table `form_clinical_notes`
--

CREATE TABLE IF NOT EXISTS `form_clinical_notes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `form_id` bigint(20) NOT NULL,
  `uuid` binary(16) DEFAULT NULL,
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
  `clinical_notes_type` varchar(100) DEFAULT NULL,
  `clinical_notes_category` varchar(100) DEFAULT NULL,
  `note_related_to` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB;

