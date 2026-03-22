-- MedEx Module Uninstallation SQL
-- This script is intentionally minimal - it does NOT drop shared tables
--
-- REASONING:
-- The tables (medex_icons, medex_outgoing, medex_prefs, medex_recalls, medex_prefs_backup)
-- are SHARED across multiple MedEx integrations:
--   - Legacy core MedEx integration
--   - oe-module-medex (this module)
--   - oe-module-medex3 (newer version)
--
-- Dropping these tables would break other MedEx modules and lose patient data.
--
-- If you want to completely remove ALL MedEx data from OpenEMR,
-- use sql/cleanup_all_medex.sql instead (WARNING: destructive operation)

-- Remove module-specific configuration
DELETE FROM globals WHERE gl_name = 'medex_enable' AND gl_index = 0;

-- Remove list options
DELETE FROM list_options WHERE list_id = 'medex_enable';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'medex_enable';

-- Service-Specific Data Cleanup (Optional)
-- Uncomment the sections below to remove data for specific services when uninstalling

-- Calendar Export Service - Calendar Feeds and Access Logs
-- DROP TABLE IF EXISTS `medex_calendar_feed_access_log`;
-- DROP TABLE IF EXISTS `medex_calendar_feeds`;
-- DROP TABLE IF EXISTS `medex_calendar_export_usage`;

-- Secure Chat Service - Chat tokens and activity logs
-- DROP TABLE IF EXISTS `medex_secure_chat_log`;
-- DROP TABLE IF EXISTS `medex_secure_chat_tokens`;

-- Session Management - API session caching
-- DROP TABLE IF EXISTS `medex_session_cache`;

-- NOTE: Patient recall data, campaign history, preferences, and icons are preserved
-- These may be used by other MedEx modules or needed for historical reference
reconnect.php