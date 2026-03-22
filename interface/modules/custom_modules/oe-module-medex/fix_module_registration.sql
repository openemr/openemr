-- ============================================================================
-- CRITICAL: MedEx Module Registration Fix
-- ============================================================================
--
-- ⚠️  THIS SQL MUST BE RUN AFTER EVERY FRESH INSTALLATION ⚠️
--
-- SYMPTOMS REQUIRING THIS FIX:
-- 1. Module Manager shows "Enable" button when module is already enabled
--    (should show "Disable" button)
-- 2. Module name shows as "MedEx" instead of "MedEx Communication"
-- 3. Help button in Module Manager doesn't work
-- 4. Menu items don't appear after installation
--
-- WHY THIS IS NEEDED:
-- OpenEMR's Module Manager initially registers modules using the directory
-- name as a fallback. While ModuleManagerListener.php now corrects this
-- automatically (as of 2026-02-05), existing installations and any edge
-- cases still need this SQL fix run once.
--
-- HOW TO RUN:
-- See INSTALL.md for detailed instructions
--
-- ============================================================================
-- Update module registration with proper name, version and activation status
-- NOTE: mod_directory is the actual module identifier used for loading
-- NOTE: mod_name and mod_ui_name are display names shown in Module Manager and menus
-- NOTE: mod_active = 1, mod_ui_active = 0 makes it show "Disable" button in Module Manager
UPDATE modules
SET mod_type = 'custom',
    mod_name = 'MedEx Communication Manager',
    mod_ui_name = 'MedEx Communication Manager',
    sql_version = '1.1.0',
    mod_active = 1,
    mod_ui_active = 0
WHERE mod_directory = 'oe-module-medex';

-- Ensure medex_enable global exists
INSERT INTO globals (gl_name, gl_index, gl_value)
VALUES ('medex_enable', 0, '1')
ON DUPLICATE KEY UPDATE gl_value = '1';

-- Ensure medex_api_host global exists
INSERT INTO globals (gl_name, gl_index, gl_value)
VALUES ('medex_api_host', 0, 'MedExBank.com')
ON DUPLICATE KEY UPDATE gl_value = IF(gl_value = '', 'MedExBank.com', gl_value);

-- Show results
SELECT
    mod_id,
    mod_name,
    mod_directory,
    mod_type,
    mod_active,
    mod_ui_active,
    mod_ui_name
FROM modules
WHERE mod_directory = 'oe-module-medex';

SELECT gl_name, gl_value
FROM globals
WHERE gl_name LIKE 'medex%'
ORDER BY gl_name;
