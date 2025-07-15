-- GCIP Authentication Module Cleanup Script
-- 
-- AI-Generated Content Start
-- This SQL file provides cleanup operations for removing the GCIP
-- Authentication module, including table drops, configuration cleanup,
-- and data removal while preserving audit logs for compliance.
-- AI-Generated Content End

-- Remove GCIP configuration from globals table (AI-Generated)
DELETE FROM `globals` WHERE `gl_name` IN (
    'GCIP_Auth_Enabled',
    'GCIP_Project_ID', 
    'GCIP_Client_ID',
    'GCIP_Client_Secret',
    'GCIP_Tenant_ID',
    'GCIP_Redirect_URI',
    'GCIP_Domain_Restriction',
    'GCIP_Auto_User_Creation',
    'GCIP_Default_Role',
    'GCIP_Audit_Logging'
);

-- Remove module from modules table (AI-Generated)
DELETE FROM `modules` WHERE `mod_directory` = 'oe-module-gcip-auth';

-- Drop foreign key constraints first (AI-Generated)
ALTER TABLE `module_gcip_user_tokens` DROP FOREIGN KEY IF EXISTS `fk_gcip_tokens_user`;
ALTER TABLE `module_gcip_user_mapping` DROP FOREIGN KEY IF EXISTS `fk_gcip_mapping_user`;

-- Drop GCIP module tables (AI-Generated)
-- Note: Consider preserving audit logs for compliance requirements
DROP TABLE IF EXISTS `module_gcip_user_tokens`;
DROP TABLE IF EXISTS `module_gcip_user_mapping`;

-- Optional: Drop audit log table (uncomment if required)
-- DROP TABLE IF EXISTS `module_gcip_audit_log`;

-- Remove scheduled cleanup event (AI-Generated)
DROP EVENT IF EXISTS `gcip_token_cleanup`;

-- Clean up user settings related to GCIP (AI-Generated)
DELETE FROM `user_settings` WHERE `setting_label` = 'gcip_tokens';

-- Remove any ACL entries for GCIP module (AI-Generated)
DELETE FROM `gacl_aro_sections` WHERE `name` = 'GCIP Authentication';
DELETE FROM `gacl_axo_sections` WHERE `name` = 'GCIP Authentication';