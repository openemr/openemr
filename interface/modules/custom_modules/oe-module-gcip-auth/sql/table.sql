-- GCIP Authentication Module Database Tables
-- 
-- AI-Generated Content Start
-- This SQL file creates the necessary database tables and settings for the
-- GCIP Authentication module, including configuration storage, user mapping,
-- and audit logging tables.
-- AI-Generated Content End

-- Create table for GCIP user token storage (AI-Generated)
CREATE TABLE IF NOT EXISTS `module_gcip_user_tokens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `encrypted_tokens` text NOT NULL,
    `token_type` varchar(50) DEFAULT 'oauth2',
    `expires_at` datetime DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_token_type` (`user_id`, `token_type`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for GCIP authentication audit log (AI-Generated)
CREATE TABLE IF NOT EXISTS `module_gcip_audit_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `username` varchar(255) DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    `event_type` varchar(100) NOT NULL,
    `event_description` text NOT NULL,
    `event_data` json DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `success` tinyint(1) DEFAULT 1,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_username` (`username`),
    KEY `idx_email` (`email`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_success` (`success`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for GCIP user mapping (AI-Generated)
CREATE TABLE IF NOT EXISTS `module_gcip_user_mapping` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `openemr_user_id` int(11) NOT NULL,
    `gcip_user_id` varchar(255) NOT NULL,
    `gcip_email` varchar(255) NOT NULL,
    `gcip_name` varchar(255) DEFAULT NULL,
    `gcip_picture` text DEFAULT NULL,
    `last_login` datetime DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_openemr_user` (`openemr_user_id`),
    UNIQUE KEY `idx_gcip_user` (`gcip_user_id`),
    UNIQUE KEY `idx_gcip_email` (`gcip_email`),
    KEY `idx_last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default GCIP module configuration settings (AI-Generated)
INSERT IGNORE INTO `globals` (`gl_name`, `gl_index`, `gl_value`, `gl_type`, `gl_title`, `gl_active`) VALUES
('GCIP_Auth_Enabled', 0, '0', 'bool', 'Enable GCIP Authentication', 1),
('GCIP_Project_ID', 0, '', 'text', 'GCIP Project ID', 1),
('GCIP_Client_ID', 0, '', 'text', 'OAuth2 Client ID', 1),
('GCIP_Client_Secret', 0, '', 'encrypted', 'OAuth2 Client Secret (Encrypted)', 1),
('GCIP_Tenant_ID', 0, '', 'text', 'GCIP Tenant ID (Optional)', 1),
('GCIP_Redirect_URI', 0, '', 'text', 'OAuth2 Redirect URI', 1),
('GCIP_Domain_Restriction', 0, '', 'text', 'Allowed Email Domains', 1),
('GCIP_Auto_User_Creation', 0, '0', 'bool', 'Auto-create User Accounts', 1),
('GCIP_Default_Role', 0, 'Clinician', 'text', 'Default Role for New Users', 1),
('GCIP_Audit_Logging', 0, '1', 'bool', 'Enable Audit Logging', 1);

-- Add foreign key constraints (AI-Generated)
ALTER TABLE `module_gcip_user_tokens` 
ADD CONSTRAINT `fk_gcip_tokens_user` 
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `module_gcip_user_mapping` 
ADD CONSTRAINT `fk_gcip_mapping_user` 
FOREIGN KEY (`openemr_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- Create indexes for performance (AI-Generated)
CREATE INDEX `idx_gcip_audit_event_user` ON `module_gcip_audit_log` (`event_type`, `user_id`, `created_at`);
CREATE INDEX `idx_gcip_audit_email_date` ON `module_gcip_audit_log` (`email`, `created_at`);

-- Insert module information into modules table (AI-Generated)
INSERT IGNORE INTO `modules` (`mod_name`, `mod_directory`, `mod_parent`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_relative_link`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `mod_nickname`, `mod_enc_menu`) VALUES
('GCIP Authentication', 'oe-module-gcip-auth', 'default', 'custom', 1, 'GCIP Auth', 'interface/modules/custom_modules/oe-module-gcip-auth/', 0, 1, 'Google Cloud Identity Platform (GCIP) authentication integration for OpenEMR', 'gcip-auth', '');

-- Create cleanup job for expired tokens (AI-Generated)
-- This would typically be handled by a cron job or scheduled task
DELIMITER ;;
CREATE EVENT IF NOT EXISTS `gcip_token_cleanup`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Clean up expired tokens
    DELETE FROM `module_gcip_user_tokens` 
    WHERE `expires_at` IS NOT NULL 
    AND `expires_at` < NOW();
    
    -- Clean up old audit logs (older than 1 year)
    DELETE FROM `module_gcip_audit_log` 
    WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 1 YEAR);
END;;
DELIMITER ;