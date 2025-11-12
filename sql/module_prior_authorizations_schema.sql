--
-- Table structure for module_prior_authorizations
--
-- This table stores prior authorization information that can be automatically
-- linked to billing claims based on CPT codes and date ranges.
--
-- Used by: src/Billing/Claim.php
--

CREATE TABLE IF NOT EXISTS `module_prior_authorizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL COMMENT 'Patient ID from patient_data table',
  `auth_num` varchar(255) NOT NULL COMMENT 'Prior authorization number',
  `start_date` date DEFAULT NULL COMMENT 'Authorization effective start date',
  `end_date` date DEFAULT NULL COMMENT 'Authorization expiration date',
  `cpt` varchar(500) NOT NULL COMMENT 'CPT code or comma-separated list of CPT codes',
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL COMMENT 'User ID who created the record',
  `notes` text COMMENT 'Additional notes about the authorization',
  PRIMARY KEY (`id`),
  KEY `idx_pid_dates` (`pid`,`start_date`,`end_date`),
  KEY `idx_cpt` (`cpt`(191)),
  KEY `idx_auth_num` (`auth_num`(191)),
  KEY `idx_date_range` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Prior authorizations for automatic claim population';

--
-- Sample data for testing
--
-- INSERT INTO `module_prior_authorizations` (`pid`, `auth_num`, `start_date`, `end_date`, `cpt`, `notes`)
-- VALUES
-- (1, 'AUTH123456', '2025-01-01', '2025-12-31', '99213,99214', 'Office visit authorization'),
-- (1, 'AUTH789012', '2025-01-01', '2025-06-30', '80053', 'Lab work authorization');
