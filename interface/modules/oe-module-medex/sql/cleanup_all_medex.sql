-- Complete MedEx Removal SQL
-- ⚠️ WARNING: This script removes ALL MedEx data from OpenEMR
--
-- ⚠️⚠️⚠️ HIPAA COMPLIANCE REQUIREMENT ⚠️⚠️⚠️
-- BEFORE RUNNING THIS SCRIPT, YOU MUST:
--   1. Export patient data using: sql/export_hipaa_data.sql
--      Command: mysql -u[user] -p [database] < sql/export_hipaa_data.sql > medex_backup_$(date +%Y-%m-%d).sql
--   2. Store the backup according to your practice's data retention policy (typically 7 years)
--   3. Verify the backup file was created successfully
--
-- USE THIS ONLY IF:
--   1. You have uninstalled ALL MedEx modules (legacy, oe-module-medex, oe-module-medex3)
--   2. You have exported and securely stored all HIPAA-protected data (see above)
--   3. You want to completely remove all MedEx data from the active database
--   4. You have verified with practice administrator/compliance officer
--
-- THIS WILL DELETE:
--   - All campaign history (SMS/EMAIL/AVM events) - HIPAA protected
--   - All recall board data - HIPAA protected
--   - All MedEx preferences and API credentials
--   - All MedEx icon definitions
--   - All MedEx global settings
--   - All MedEx v3 sync tables and data
--
-- PATIENT DATA IN EXTERNAL MEDEX DATABASE IS NOT AFFECTED
-- (Contact MedEx support to remove data from their servers if required)

-- Drop all MedEx tables
DROP TABLE IF EXISTS `medex_icons`;
DROP TABLE IF EXISTS `medex_outgoing`;
DROP TABLE IF EXISTS `medex_prefs`;
DROP TABLE IF EXISTS `medex_prefs_backup`;
DROP TABLE IF EXISTS `medex_recalls`;

-- Drop Calendar Export tables (added by migrations)
DROP TABLE IF EXISTS `medex_calendar_feeds`;
DROP TABLE IF EXISTS `medex_calendar_feed_access_log`;
DROP TABLE IF EXISTS `medex_calendar_export_usage`;

-- Drop Secure Chat tables (added by migrations)
DROP TABLE IF EXISTS `medex_secure_chat_log`;
DROP TABLE IF EXISTS `medex_secure_chat_tokens`;

-- Drop Session Cache table (added by migrations)
DROP TABLE IF EXISTS `medex_session_cache`;

-- Drop AI Batches table (added by migrations)
DROP TABLE IF EXISTS `medex_ai_batches`;

-- Drop Migrations tracking table (added by migrations runner)
DROP TABLE IF EXISTS `medex_migrations`;

-- Drop MedEx v3 tables (if present)
DROP TABLE IF EXISTS `medex3_sync_status`;
DROP TABLE IF EXISTS `medex3_appointment_sync`;

-- Remove all MedEx global settings
DELETE FROM globals WHERE gl_name LIKE 'medex%';

-- Remove list options
DELETE FROM list_options WHERE list_id = 'medex_enable';
DELETE FROM list_options WHERE list_id = 'lists' AND option_id = 'medex_enable';

-- Remove module registration (if not already removed via OpenEMR UI)
DELETE FROM modules WHERE mod_directory = 'oe-module-medex';

-- ✓ Complete MedEx removal finished
-- All MedEx data has been removed from OpenEMR database
