<?php
/**
 * Migration: Add update cache columns to medex_prefs
 *
 * Adds columns for caching update check results
 */

// Check if columns already exist
$result = sqlQuery("SHOW COLUMNS FROM medex_prefs LIKE 'module_update_cache'");

if (!$result) {
    // Add update cache columns
    $sql = "ALTER TABLE medex_prefs
        ADD COLUMN module_update_cache TEXT NULL COMMENT 'Cached update information',
        ADD COLUMN module_update_checked DATETIME NULL COMMENT 'Last update check timestamp'";

    sqlStatement($sql);

    error_log('[MedEx Migration] Added update cache columns to medex_prefs');
} else {
    error_log('[MedEx Migration] Update cache columns already exist');
}
