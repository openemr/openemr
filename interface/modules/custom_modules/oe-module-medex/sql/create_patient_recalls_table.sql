-- Create patient_recalls table for vendor-neutral recall management
-- This replaces the legacy medex_recalls table with vendor-agnostic naming

CREATE TABLE IF NOT EXISTS patient_recalls (
  r_ID bigint(20) NOT NULL AUTO_INCREMENT,
  r_pid bigint(20) NOT NULL COMMENT 'Patient ID from patient_data table',
  r_eventDate date DEFAULT NULL COMMENT 'Scheduled recall date',
  r_facility int(11) DEFAULT NULL COMMENT 'Facility ID from facility table',
  r_provider int(11) DEFAULT NULL COMMENT 'Provider ID from users table',
  r_reason varchar(255) DEFAULT NULL COMMENT 'Reason for recall (e.g. Annual exam)',
  r_created datetime DEFAULT NULL COMMENT 'When this recall was created',
  PRIMARY KEY (r_ID),
  KEY idx_pid (r_pid),
  KEY idx_eventDate (r_eventDate),
  KEY idx_facility (r_facility),
  KEY idx_provider (r_provider)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Patient recall tracking - vendor-neutral replacement for medex_recalls';
