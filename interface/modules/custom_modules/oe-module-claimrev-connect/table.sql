-- This table definition is loaded and then executed when the OpenEMR interface's install button is clicked.
CREATE TABLE IF NOT EXISTS `mod_claimrev_eligibility`(
    `id` INT(11)  PRIMARY KEY AUTO_INCREMENT NOT NULL
    ,`pid` bigint(20)
    ,`payer_responsibility` varchar(2)
    ,`request_json` TEXT
    ,`response_json` LONGTEXT
	,`eligibility_json` LONGTEXT
	,`individual_json` LONGTEXT
    ,`response_message` TEXT
    ,`status` varchar(25)
    ,`last_checked` datetime default NULL
    ,`create_date` datetime default NULL
    ,`raw271` LONGTEXT
);

#IfNotColumnType mod_claimrev_eligibility response_json LONGTEXT
ALTER TABLE `mod_claimrev_eligibility` CHANGE `response_json` `response_json` LONGTEXT;
#EndIf

#IfNotColumnType mod_claimrev_eligibility eligibility_json LONGTEXT
ALTER TABLE `mod_claimrev_eligibility` CHANGE `eligibility_json` `eligibility_json` LONGTEXT;
#EndIf

#IfNotColumnType mod_claimrev_eligibility individual_json LONGTEXT
ALTER TABLE `mod_claimrev_eligibility` CHANGE `individual_json` `individual_json` LONGTEXT;
#EndIf

#IfNotColumnType mod_claimrev_eligibility raw271 LONGTEXT
ALTER TABLE `mod_claimrev_eligibility` CHANGE `raw271` `raw271` LONGTEXT;
#EndIf


-- Add the background service for sending claims
#IfNotRow background_services name ClaimRev_Send
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('ClaimRev_Send', 'Send Claims To ClaimRev', 1, 0, '2017-05-09 17:39:10', 1, 'start_X12_Claimrev_send_files', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Billing_Claimrev_Service.php', 100);
#Endif

#IfNotRow background_services name ClaimRev_Receive
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('ClaimRev_Receive', 'Get Reports from ClaimRev', 1, 0, '2017-05-09 17:39:10', 240, 'start_X12_Claimrev_get_reports', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Billing_Claimrev_Service.php', 100);
#Endif

#IfNotRow background_services name ClaimRev_Elig_Send_Receive
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('ClaimRev_Elig_Send_Receive', 'Send and Receive Eligibility from ClaimRev', 1, 0, '2017-05-09 17:39:10', 1, 'start_send_eligibility', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Eligibility_ClaimRev_Service.php', 100);
#Endif

CREATE TABLE IF NOT EXISTS `mod_claimrev_notifications`(
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT NOT NULL
    ,`portal_notification_id` bigint(20) NOT NULL
    ,`message_title` varchar(255)
    ,`message_body` TEXT
    ,`pnote_id` bigint(20)
    ,`created_date` datetime default NULL
    ,`processed_date` datetime default NULL
    ,UNIQUE KEY `uk_portal_notification` (`portal_notification_id`)
);

#IfNotRow background_services name ClaimRev_Notifications
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('ClaimRev_Notifications', 'ClaimRev Notification Check', 1, 0, '2017-05-09 17:39:10', 60, 'start_claimrev_notifications', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/ClaimRev_Notification_Service.php', 100);
#Endif

#IfNotRow background_services name ClaimRev_Watchdog
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('ClaimRev_Watchdog', 'ClaimRev Stuck Service Watchdog', 1, 0, '2017-05-09 17:39:10', 20, 'start_claimrev_watchdog', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/ClaimRev_Watchdog_Service.php', 50);
#Endif

#IfNotRow background_services name ClaimRev_Elig_Sweep
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('ClaimRev_Elig_Sweep', 'ClaimRev Eligibility Sweep', 1, 0, '2017-05-09 17:39:10', 1440, 'start_eligibility_sweep', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Eligibility_Sweep_Service.php', 100);
#Endif

-- Claim status tracking tables
#IfNotTable mod_claimrev_claims
CREATE TABLE `mod_claimrev_claims` (
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `pid` BIGINT(20) NOT NULL,
    `encounter` BIGINT(20) NOT NULL,
    `payer_type` TINYINT(4) NOT NULL DEFAULT 1 COMMENT '1=primary, 2=secondary, 3=tertiary',
    `claimrev_object_id` VARCHAR(64) DEFAULT NULL COMMENT 'ClaimRev claim ID for portal links',
    `claimrev_status_id` INT(11) DEFAULT NULL,
    `claimrev_status_name` VARCHAR(100) DEFAULT NULL,
    `payer_acceptance_status_id` INT(11) DEFAULT NULL,
    `payer_acceptance_status_name` VARCHAR(100) DEFAULT NULL,
    `era_classification` VARCHAR(50) DEFAULT NULL COMMENT 'Paid, Denied, PartiallyPaid, etc.',
    `payer_paid_amount` DECIMAL(12,2) DEFAULT NULL,
    `is_worked` TINYINT(1) NOT NULL DEFAULT 0,
    `ar_session_id` INT(11) DEFAULT NULL COMMENT 'FK to ar_session if payment posted',
    `last_status_check_date` DATETIME DEFAULT NULL COMMENT 'Last real-time 276/277 check',
    `last_synced` DATETIME DEFAULT NULL COMMENT 'Last pull from ClaimRev',
    `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_claim` (`pid`, `encounter`, `payer_type`),
    KEY `idx_claimrev_object_id` (`claimrev_object_id`),
    KEY `idx_status` (`claimrev_status_id`),
    KEY `idx_era` (`era_classification`),
    KEY `idx_is_worked` (`is_worked`),
    KEY `idx_last_synced` (`last_synced`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable mod_claimrev_claim_events
CREATE TABLE `mod_claimrev_claim_events` (
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `pid` BIGINT(20) NOT NULL,
    `encounter` BIGINT(20) NOT NULL,
    `payer_type` TINYINT(4) NOT NULL DEFAULT 1,
    `event_type` VARCHAR(30) NOT NULL COMMENT 'submitted, rejected, accepted, denied, status_check_276, era_received, payment_posted, requeued, corrected, manual_note, claimrev_sync',
    `status_code` VARCHAR(30) DEFAULT NULL,
    `status_description` VARCHAR(255) DEFAULT NULL,
    `detail_text` TEXT DEFAULT NULL COMMENT 'Rejection reason, denial codes, status check response, notes',
    `source` VARCHAR(20) NOT NULL DEFAULT 'system' COMMENT 'claimrev, payer_277, user, system, era',
    `amount` DECIMAL(12,2) DEFAULT NULL,
    `created_by` VARCHAR(50) NOT NULL DEFAULT 'system' COMMENT 'OpenEMR username or system',
    `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_claim_events_lookup` (`pid`, `encounter`, `payer_type`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_created_date` (`created_date`)
) ENGINE=InnoDB;
#EndIf

-- Patient statement tracking for encounters with patient responsibility
#IfNotTable mod_claimrev_patient_statements
CREATE TABLE `mod_claimrev_patient_statements` (
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `pid` BIGINT(20) NOT NULL,
    `encounter` BIGINT(20) NOT NULL,
    `statement_date` DATE NOT NULL,
    `statement_method` VARCHAR(30) NOT NULL COMMENT 'openemr_print, openemr_email, openemr_portal, claimrev',
    `amount_due` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Balance at time of statement',
    `status` VARCHAR(20) NOT NULL DEFAULT 'generated' COMMENT 'generated, sent, paid, void',
    `claimrev_statement_id` VARCHAR(64) DEFAULT NULL COMMENT 'Future ClaimRev statement integration',
    `notes` TEXT DEFAULT NULL,
    `created_by` VARCHAR(50) NOT NULL DEFAULT 'system',
    `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_patient_encounter` (`pid`, `encounter`),
    KEY `idx_statement_date` (`statement_date`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable mod_claimrev_version_check
CREATE TABLE `mod_claimrev_version_check` (
    `id` TINYINT(1) PRIMARY KEY DEFAULT 1 COMMENT 'singleton row, always 1',
    `install_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'UUIDv4 generated on first check, sent so ClaimRev can dedupe install counts without identifying who they are',
    `last_checked_at` DATETIME DEFAULT NULL,
    `current_version` VARCHAR(40) NOT NULL DEFAULT '',
    `is_current` TINYINT(1) NOT NULL DEFAULT 0,
    `is_supported` TINYINT(1) NOT NULL DEFAULT 1,
    `message` TEXT DEFAULT NULL,
    `severity` VARCHAR(10) NOT NULL DEFAULT 'info' COMMENT 'info | warning | critical',
    `download_url` VARCHAR(500) NOT NULL DEFAULT '',
    `disabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'when 1, claim send + eligibility checks no-op',
    `disable_reason` TEXT DEFAULT NULL,
    CONSTRAINT `chk_singleton` CHECK (`id` = 1)
) ENGINE=InnoDB;
#EndIf
