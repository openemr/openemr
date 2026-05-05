-- MedEx Module Installation SQL
-- Creates shared MedEx tables used across MedEx integrations
-- Uses CREATE TABLE IF NOT EXISTS to safely handle pre-existing tables

-- Icons table - stores HTML/icons for campaign statuses (SMS/EMAIL/AVM)
CREATE TABLE IF NOT EXISTS `medex_icons` (
  `i_UID` int(11) NOT NULL AUTO_INCREMENT,
  `msg_type` varchar(50) NOT NULL,
  `msg_status` varchar(10) NOT NULL,
  `i_description` varchar(255) NOT NULL,
  `i_html` text DEFAULT NULL,
  `i_blob` longtext DEFAULT NULL,
  PRIMARY KEY (`i_UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Compatibility: migrate legacy medex_icons schema (older installs used i_type only)
#IfMissingColumn medex_icons msg_type
ALTER TABLE `medex_icons` ADD COLUMN `msg_type` varchar(50) NOT NULL DEFAULT '' AFTER `i_UID`;
#EndIf

#IfMissingColumn medex_icons msg_status
ALTER TABLE `medex_icons` ADD COLUMN `msg_status` varchar(10) NOT NULL DEFAULT '' AFTER `msg_type`;
#EndIf

#IfColumn medex_icons i_type
UPDATE `medex_icons`
SET `msg_type` = UPPER(`i_type`)
WHERE (`msg_type` IS NULL OR `msg_type` = '') AND `i_type` IS NOT NULL AND `i_type` != '';
#EndIf

UPDATE `medex_icons`
SET `msg_status` = 'LEGACY'
WHERE `msg_status` IS NULL OR `msg_status` = '';

-- Populate medex_icons table with icon HTML for all modality types and statuses
-- These icons are displayed in the Status column of the Recall Board
-- AVM Icons (Automated Voice Message)
#IfNotRow2D medex_icons msg_type AVM msg_status ALLOWED
INSERT INTO `medex_icons` (`msg_type`, `msg_status`, `i_description`, `i_html`) VALUES
('AVM', 'ALLOWED', '', '<i title="Automated Voice Messaging is possible." class="fas fa-phone fa-fw"></i>'),
('AVM', 'NotAllowed', '', '<span class="fas fa-stack" title="Automated Voice Messaging is not allowed"><i class="fas fa-phone fa-fw"></i><i class="fas fa-ban fa-stack-2x text-danger"></i></span>'),
('AVM', 'SCHEDULED', '', '<i title="Automated Voice Messaging event is scheduled." class="far fa-clock fa-fw"></i>'),
('AVM', 'SENT', '', '<span class="fas fa-stack" style="font-size:0.8em;"><i class="fas fa-phone fa-fw fa-stack-1x" style="color:green;"></i><i class="fas fa-check fa-stack-1x" style="font-size:0.6em;left:5px;bottom:4px;color:#fff;"></i></span>'),
('AVM', 'READ', '', '<span class="fas fa-stack" style="font-size:0.8em;"><i class="fas fa-phone fa-fw fa-stack-1x" style="color:green;"></i><i class="fas fa-check-double fa-stack-1x" style="font-size:0.5em;left:5px;bottom:4px;color:#fff;"></i></span>'),
('AVM', 'CONFIRMED', '', '<i title="Confirmed by the patient." class="fas fa-check-circle fa-fw" style="color:green;"></i>'),
('AVM', 'CALL', '', '<i title="Patient requests Office call" class="fas fa-flag fa-fw" style="color:red;"></i>'),
('AVM', 'CALLED', '', '<i title="You already spoke to the patient" class="fas fa-phone fa-fw" style="color:red;"></i>'),
('AVM', 'STOP', '', '<i title="Patient requests communication STOP. This is an OPT-OUT request. Check SMS Messaging opt-in status." class="fas fa-stop-circle fa-fw" style="color:red;"></i>'),
('AVM', 'FAILED', '', '<i title="Automated Voice Messaging event failed to be delivered." class="fas fa-phone fa-fw" style="color:red;"></i>'),
('AVM', 'Other', '', '<i title="There was no response from patient." class="fas fa-phone fa-fw" style="color:red;"></i>');
#EndIf

-- EMAIL Icons
#IfNotRow2D medex_icons msg_type EMAIL msg_status ALLOWED
INSERT INTO `medex_icons` (`msg_type`, `msg_status`, `i_description`, `i_html`) VALUES
('EMAIL', 'ALLOWED', '', '<i title="EMAIL is possible." class="far fa-envelope fa-fw"></i>'),
('EMAIL', 'NotAllowed', '', '<span class="fas fa-stack" title="EMAIL is not possible"><i class="far fa-envelope fa-fw"></i><i class="fas fa-ban fa-stack-2x text-danger"></i></span>'),
('EMAIL', 'SCHEDULED', '', '<i title="EMAIL event is scheduled." class="far fa-clock fa-fw"></i>'),
('EMAIL', 'SENT', '', '<span class="fas fa-stack" style="font-size:0.8em;"><i class="far fa-envelope fa-fw fa-stack-1x" style="color:green;"></i><i class="fas fa-check fa-stack-1x" style="font-size:0.6em;left:5px;bottom:4px;color:#fff;"></i></span>'),
('EMAIL', 'READ', '', '<span class="fas fa-stack" style="font-size:0.8em;"><i class="far fa-envelope-open fa-fw fa-stack-1x" style="color:green;"></i><i class="fas fa-check-double fa-stack-1x" style="font-size:0.5em;left:5px;bottom:4px;color:#000;"></i></span>'),
('EMAIL', 'CONFIRMED', '', '<i title="Confirmed by the patient." class="fas fa-check-circle fa-fw" style="color:green;"></i>'),
('EMAIL', 'CALL', '', '<i title="Patient requests Office call" class="fas fa-flag fa-fw" style="color:red;"></i>'),
('EMAIL', 'CALLED', '', '<i title="You already spoke to the patient" class="fas fa-phone fa-fw" style="color:red;"></i>'),
('EMAIL', 'STOP', '', '<i title="Patient requests communication STOP. This is an OPT-OUT request. Check SMS Messaging opt-in status." class="fas fa-stop-circle fa-fw" style="color:red;"></i>'),
('EMAIL', 'FAILED', '', '<i title="EMAIL event failed to be delivered." class="far fa-envelope fa-fw" style="color:red;"></i>'),
('EMAIL', 'Other', '', '<i title="There was no response from patient." class="far fa-envelope fa-fw" style="color:red;"></i>');
#EndIf

-- SMS Icons (Short Message Service - Text Messages)
#IfNotRow2D medex_icons msg_type SMS msg_status ALLOWED
INSERT INTO `medex_icons` (`msg_type`, `msg_status`, `i_description`, `i_html`) VALUES
('SMS', 'ALLOWED', '', '<i title="SMS is possible." class="far fa-comment-dots fa-fw"></i>'),
('SMS', 'NotAllowed', '', '<span class="fas fa-stack" title="SMS not possible"><i title="SMS is not possible." class="fas fa-comment-dots fa-fw"></i><i class="fas fa-ban fa-stack-2x text-danger"></i></span>'),
('SMS', 'SCHEDULED', '', '<i title="SMS message is scheduled." class="far fa-clock fa-fw"></i>'),
('SMS', 'SENT', '', '<span class="fas fa-stack" style="font-size:0.8em;"><i class="fas fa-comment-dots fa-fw fa-stack-1x" style="color:green;"></i><i class="fas fa-check fa-stack-1x" style="font-size:0.6em;left:5px;bottom:4px;color:#fff;"></i></span>'),
('SMS', 'READ', '', '<span class="fas fa-stack" style="font-size:0.8em;"><i class="fas fa-comment-dots fa-fw fa-stack-1x" style="color:green;"></i><i class="fas fa-check-double fa-stack-1x" style="font-size:0.5em;left:5px;bottom:4px;color:#fff;"></i></span>'),
('SMS', 'CONFIRMED', '', '<i title="Confirmed by the patient." class="fas fa-check-circle fa-fw" style="color:green;"></i>'),
('SMS', 'CALL', '', '<i title="Patient requests Office call" class="fas fa-flag fa-fw" style="color:red;"></i>'),
('SMS', 'CALLED', '', '<i title="You already spoke to the patient" class="fas fa-phone fa-fw" style="color:red;"></i>'),
('SMS', 'STOP', '', '<i title="Patient requests communication STOP. This is an OPT-OUT request. Check SMS Messaging opt-in status." class="fas fa-stop-circle fa-fw" style="color:red;"></i>'),
('SMS', 'FAILED', '', '<i title="SMS message failed to be delivered." class="fas fa-comment-dots fa-fw" style="color:red;"></i>'),
('SMS', 'EXTRA', '', '<i title="Custom SMS message sent to patient outside of automated campaigns." class="fas fa-comment-medical fa-fw" style="color:green;"></i>');
#EndIf

-- POSTCARD Icons (Physical Mail)
#IfNotRow2D medex_icons msg_type POSTCARD msg_status SCHEDULED
INSERT INTO `medex_icons` (`msg_type`, `msg_status`, `i_description`, `i_html`) VALUES
('POSTCARD', 'SCHEDULED', '', '<i title="Postcard is scheduled." class="far fa-clock fa-fw"></i>'),
('POSTCARD', 'SENT', '', '<i title="Postcard was printed/sent." class="fas fa-file-image fa-fw" style="color:green;"></i>'),
('POSTCARD', 'READ', '', '<i title="Postcard was delivered." class="fas fa-mailbox fa-fw" style="color:green;"></i>'),
('POSTCARD', 'FAILED', '', '<i title="Postcard delivery failed." class="fas fa-file-image fa-fw" style="color:red;"></i>');
#EndIf

-- Outgoing messages table - tracks all MedEx campaign events (SMS/EMAIL/AVM/etc)
CREATE TABLE IF NOT EXISTS `medex_outgoing` (
  `msg_uid` int(11) NOT NULL AUTO_INCREMENT,
  `msg_pid` int(11) NOT NULL,
  `msg_pc_eid` varchar(11) NOT NULL,
  `campaign_uid` int(11) NOT NULL DEFAULT 0,
  `msg_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `msg_type` varchar(50) NOT NULL,
  `msg_reply` varchar(50) DEFAULT NULL,
  `msg_extra_text` text DEFAULT NULL,
  `medex_uid` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`msg_uid`),
  UNIQUE KEY `msg_eid` (`msg_uid`,`msg_pc_eid`,`medex_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Preferences table - stores MedEx API credentials and settings
CREATE TABLE IF NOT EXISTS `medex_prefs` (
  `MedEx_id` int(11) DEFAULT 0,
  `ME_username` varchar(100) DEFAULT NULL,
  `ME_api_key` text DEFAULT NULL,
  `ME_facilities` varchar(50) DEFAULT NULL,
  `ME_providers` varchar(100) DEFAULT NULL,
  `ME_hipaa_default_override` varchar(3) DEFAULT NULL,
  `PHONE_country_code` int(4) NOT NULL DEFAULT 1,
  `MSGS_default_yes` varchar(3) DEFAULT NULL,
  `POSTCARDS_local` varchar(3) DEFAULT NULL,
  `POSTCARDS_remote` varchar(3) DEFAULT NULL,
  `LABELS_local` varchar(3) DEFAULT NULL,
  `LABELS_choice` varchar(50) DEFAULT NULL,
  `combine_time` tinyint(4) DEFAULT NULL,
  `postcard_top` varchar(255) DEFAULT NULL,
  `MedEx_lastupdated` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` text DEFAULT NULL,
  `bad_actor_until` timestamp NULL DEFAULT NULL,
  `sms_bot_phone_style` varchar(50) DEFAULT 'S8',
  `module_update_cache` text DEFAULT NULL COMMENT 'Cached update check JSON from MedEx server',
  `module_update_checked` datetime DEFAULT NULL COMMENT 'Timestamp of last update check',
  `session_token` varchar(255) DEFAULT NULL,
  `session_token_expiry` datetime DEFAULT NULL,
  UNIQUE KEY `ME_username` (`ME_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Compatibility: legacy medex_prefs schemas may miss newer columns used by status/licensing/update code
#IfMissingColumn medex_prefs ME_username
ALTER TABLE `medex_prefs` ADD `ME_username` varchar(100) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs ME_api_key
ALTER TABLE `medex_prefs` ADD `ME_api_key` text DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs ME_facilities
ALTER TABLE `medex_prefs` ADD `ME_facilities` varchar(50) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs ME_providers
ALTER TABLE `medex_prefs` ADD `ME_providers` varchar(100) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs ME_hipaa_default_override
ALTER TABLE `medex_prefs` ADD `ME_hipaa_default_override` varchar(3) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs PHONE_country_code
ALTER TABLE `medex_prefs` ADD `PHONE_country_code` int(4) NOT NULL DEFAULT 1;
#EndIf

#IfMissingColumn medex_prefs MSGS_default_yes
ALTER TABLE `medex_prefs` ADD `MSGS_default_yes` varchar(3) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs POSTCARDS_local
ALTER TABLE `medex_prefs` ADD `POSTCARDS_local` varchar(3) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs POSTCARDS_remote
ALTER TABLE `medex_prefs` ADD `POSTCARDS_remote` varchar(3) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs LABELS_local
ALTER TABLE `medex_prefs` ADD `LABELS_local` varchar(3) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs LABELS_choice
ALTER TABLE `medex_prefs` ADD `LABELS_choice` varchar(50) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs combine_time
ALTER TABLE `medex_prefs` ADD `combine_time` tinyint(4) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs postcard_top
ALTER TABLE `medex_prefs` ADD `postcard_top` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs status
ALTER TABLE `medex_prefs` ADD `status` text DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs bad_actor_until
ALTER TABLE `medex_prefs` ADD `bad_actor_until` timestamp NULL DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs bad_actor_message
ALTER TABLE `medex_prefs` ADD `bad_actor_message` varchar(500) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs sms_bot_phone_style
ALTER TABLE `medex_prefs` ADD `sms_bot_phone_style` varchar(50) DEFAULT 'S8';
#EndIf

#IfMissingColumn medex_prefs module_update_cache
ALTER TABLE `medex_prefs` ADD `module_update_cache` text DEFAULT NULL COMMENT 'Cached update check JSON from MedEx server';
#EndIf

#IfMissingColumn medex_prefs module_update_checked
ALTER TABLE `medex_prefs` ADD `module_update_checked` datetime DEFAULT NULL COMMENT 'Timestamp of last update check';
#EndIf

#IfMissingColumn medex_prefs session_token
ALTER TABLE `medex_prefs` ADD `session_token` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs session_token_expiry
ALTER TABLE `medex_prefs` ADD `session_token_expiry` datetime DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs ME_server_url
ALTER TABLE `medex_prefs` ADD `ME_server_url` varchar(255) DEFAULT NULL;
#EndIf

#IfColumn medex_prefs MedEx_status
UPDATE `medex_prefs` SET `status` = `MedEx_status` WHERE (`status` IS NULL OR `status` = '') AND `MedEx_status` IS NOT NULL;
#EndIf

#IfColumn medex_prefs MedEx_facilities
UPDATE `medex_prefs` SET `ME_facilities` = CAST(`MedEx_facilities` AS CHAR) WHERE (`ME_facilities` IS NULL OR `ME_facilities` = '') AND `MedEx_facilities` IS NOT NULL;
#EndIf

#IfColumn medex_prefs MedEx_providers
UPDATE `medex_prefs` SET `ME_providers` = CAST(`MedEx_providers` AS CHAR) WHERE (`ME_providers` IS NULL OR `ME_providers` = '') AND `MedEx_providers` IS NOT NULL;
#EndIf

-- Database migrations tracking table
CREATE TABLE IF NOT EXISTS `medex_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration_name` varchar(255) NOT NULL,
  `applied_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration_name` (`migration_name`),
  KEY `idx_migration_name` (`migration_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recalls table - stores patient recall data
CREATE TABLE IF NOT EXISTS `medex_recalls` (
  `r_ID` int(11) NOT NULL AUTO_INCREMENT,
  `r_PRACTID` int(11) NOT NULL,
  `r_pid` int(11) NOT NULL COMMENT 'PatientID from pat_data',
  `r_eventDate` date NOT NULL COMMENT 'Date of Appt or Recall',
  `r_facility` int(11) NOT NULL,
  `r_provider` int(11) NOT NULL,
  `r_reason` varchar(255) DEFAULT NULL,
  `r_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`r_ID`),
  UNIQUE KEY `r_PRACTID` (`r_PRACTID`,`r_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Backup preferences table (created by MedEx for safety)
CREATE TABLE IF NOT EXISTS `medex_prefs_backup` (
  `MedEx_id` int(11) DEFAULT 0,
  `ME_username` varchar(100) DEFAULT NULL,
  `ME_api_key` text DEFAULT NULL,
  `ME_facilities` varchar(50) DEFAULT NULL,
  `ME_providers` varchar(100) DEFAULT NULL,
  `ME_hipaa_default_override` varchar(3) DEFAULT NULL,
  `PHONE_country_code` int(4) NOT NULL DEFAULT 1,
  `MSGS_default_yes` varchar(3) DEFAULT NULL,
  `POSTCARDS_local` varchar(3) DEFAULT NULL,
  `POSTCARDS_remote` varchar(3) DEFAULT NULL,
  `LABELS_local` varchar(3) DEFAULT NULL,
  `LABELS_choice` varchar(50) DEFAULT NULL,
  `combine_time` tinyint(4) DEFAULT NULL,
  `postcard_top` varchar(255) DEFAULT NULL,
  `MedEx_lastupdated` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` text DEFAULT NULL,
  `bad_actor_until` timestamp NULL DEFAULT NULL,
  `sms_bot_phone_style` varchar(50) DEFAULT 'S8',
  UNIQUE KEY `ME_username` (`ME_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Global configuration settings
#IfNotRow2D list_options list_id lists option_id medex_enable
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`)
VALUES ('lists','medex_enable','MedEx Module',1,0,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`)
VALUES ('medex_enable','0','Disabled',10,1,0);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`)
VALUES ('medex_enable','1','Enabled',20,0,0);
#EndIf

-- Calendar Export Usage Tracking - for per-provider billing ($0.95/provider/month)
-- Tracks export usage to enforce subscription limits and generate billing reports
CREATE TABLE IF NOT EXISTS `medex_calendar_export_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `practice_id` varchar(50) NOT NULL,
  `provider_id` varchar(50) NOT NULL,
  `export_date` datetime NOT NULL DEFAULT current_timestamp(),
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `format` varchar(10) NOT NULL,
  `exported_by` varchar(50) NOT NULL,
  `month_year` varchar(7) NOT NULL COMMENT 'YYYY-MM format for billing aggregation',
  `export_count` int(11) NOT NULL DEFAULT 1,
  `last_export` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_provider_export` (`practice_id`,`provider_id`,`month_year`),
  KEY `idx_practice_month` (`practice_id`,`month_year`),
  KEY `idx_provider` (`provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Session token caching for MedEx API authentication
CREATE TABLE IF NOT EXISTS `medex_session_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `practice_id` varchar(50) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `session_expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_practice` (`practice_id`),
  KEY `idx_expiry` (`session_expiry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Secure Chat Tokens - stores temporary tokens for patient secure chat links
CREATE TABLE IF NOT EXISTS `medex_secure_chat_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL COMMENT 'Patient ID',
  `token` varchar(64) NOT NULL COMMENT 'Unique secure token',
  `expires_at` datetime NOT NULL COMMENT 'Token expiration time',
  `created_by` int(11) NOT NULL COMMENT 'User ID who created the token',
  `method` varchar(20) DEFAULT NULL COMMENT 'Delivery method: sms, email, copy',
  `is_provider` tinyint(1) DEFAULT 0 COMMENT 'Whether this is a provider token (can see all messages)',
  `user_initials` varchar(4) DEFAULT NULL COMMENT 'Initials of user who created token (JD, etc.)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` datetime DEFAULT NULL COMMENT 'When the token was first used',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `idx_pid` (`pid`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Secure Chat Activity Log - tracks all secure chat link activities
CREATE TABLE IF NOT EXISTS `medex_secure_chat_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL COMMENT 'Patient ID',
  `action` varchar(50) NOT NULL COMMENT 'Action: link_sent, link_copied, chat_started, message_sent, etc.',
  `method` varchar(20) DEFAULT NULL COMMENT 'Method: sms, email, manual',
  `created_by` int(11) DEFAULT NULL COMMENT 'User ID who initiated',
  `user_initials` varchar(4) DEFAULT NULL COMMENT 'Initials of user who initiated (JD, etc.)',
  `details` text DEFAULT NULL COMMENT 'JSON encoded details',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pid` (`pid`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Calendar Feeds - stores CalDAV/iCal feed configurations for Calendar Export service
-- Each feed has a unique token and can be filtered by providers and facilities
CREATE TABLE IF NOT EXISTS `medex_calendar_feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) UNIQUE NOT NULL COMMENT 'Unique secure token for feed URL',
  `name` varchar(255) NOT NULL COMMENT 'User-friendly name for this feed',
  `providers` text DEFAULT NULL COMMENT 'Pipe-separated provider IDs (e.g., "1|3|7")',
  `facilities` text DEFAULT NULL COMMENT 'Pipe-separated facility IDs (e.g., "1|2")',
  `provider_names` json DEFAULT NULL COMMENT 'JSON array of provider names for display',
  `facility_names` json DEFAULT NULL COMMENT 'JSON array of facility names for display',
  `openemr_user_id` int(11) DEFAULT NULL COMMENT 'OpenEMR user ID who created this feed',
  `openemr_username` varchar(255) DEFAULT NULL COMMENT 'OpenEMR username who created this feed',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'When this feed was created',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last modification time',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `idx_user` (`openemr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Calendar Feed Access Log - tracks all calendar feed accesses for security and usage monitoring
-- Used for: security auditing, subscription enforcement, usage analytics
CREATE TABLE IF NOT EXISTS `medex_calendar_feed_access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feed_token` varchar(64) NOT NULL COMMENT 'Token of the accessed feed',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of client (supports IPv6)',
  `user_agent` text DEFAULT NULL COMMENT 'User-Agent string from HTTP request',
  `accessed_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'When this access occurred',
  `success` tinyint(1) DEFAULT 1 COMMENT 'Whether access was successful (1) or denied (0)',
  `openemr_username` varchar(255) DEFAULT NULL COMMENT 'Optional: OpenEMR user if authenticated access',
  PRIMARY KEY (`id`),
  KEY `idx_feed_token` (`feed_token`),
  KEY `idx_accessed` (`accessed_at`),
  CONSTRAINT `fk_calendar_feed_access` FOREIGN KEY (`feed_token`) REFERENCES `medex_calendar_feeds` (`token`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
