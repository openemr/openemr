--
-- Table structure for table `form_care_plan`
--
-- Kept in sync with the authoritative definition in sql/database.sql.
--

CREATE TABLE IF NOT EXISTS `form_care_plan` (
  `id` bigint(20) NOT NULL,
  `date` datetime DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `codetext` text,
  `description` text,
  `external_id` varchar(30) DEFAULT NULL,
  `care_plan_type` varchar(30) DEFAULT NULL,
  `note_related_to` text,
  `date_end` datetime DEFAULT NULL,
  `reason_code` varchar(31) DEFAULT NULL,
  `reason_description` text,
  `reason_date_low` datetime DEFAULT NULL COMMENT 'The date the reason was recorded',
  `reason_date_high` datetime DEFAULT NULL COMMENT 'The date the explanation reason for the care plan entry value ends',
  `reason_status` varchar(31) DEFAULT NULL,
  `plan_status` varchar(32) DEFAULT NULL COMMENT 'Care Plan status (e.g., draft, active, completed, etc)',
  `proposed_date` datetime DEFAULT NULL COMMENT 'Target or Achieve-by date for the goal',
  `plan_engagement_category` varchar(100) DEFAULT '' COMMENT 'Expected engagement category with the patient based upon the care plan type',
  KEY `idx_status_date` (`plan_status`,`date`,`date_end`)
) ENGINE=InnoDB;
