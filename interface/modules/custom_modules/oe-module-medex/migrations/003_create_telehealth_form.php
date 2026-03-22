<?php

/**
 * MedEx Migration: Create TeleHealth LBF Form
 *
 * Creates the TeleHealth Visit form in the Clinical dropdown
 *
 * @package   OpenEMR Modules
 * @link      https://www.open-emr.org
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

/**
 * Run this migration
 *
 * @return bool
 */
function run_migration_003(): bool
{
    try {
        // Check if form already exists
        $exists = sqlQuery("SELECT grp_form_id FROM layout_group_properties WHERE grp_form_id = 'LBFtelehealth' AND grp_group_id = ''");
        if ($exists) {
            error_log('[MedEx Migration 003] TeleHealth form already exists, skipping');
            return true;
        }

        // Run the SQL file
        $sqlFile = __DIR__ . '/../sql/telehealth_form.sql';
        if (!file_exists($sqlFile)) {
            error_log('[MedEx Migration 003] SQL file not found: ' . $sqlFile);
            return false;
        }

        $sql = file_get_contents($sqlFile);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !str_starts_with($statement, '--')) {
                sqlStatement($statement);
            }
        }

        error_log('[MedEx Migration 003] TeleHealth form created successfully');
        return true;

    } catch (Exception $e) {
        error_log('[MedEx Migration 003] Failed to create TeleHealth form: ' . $e->getMessage());
        return false;
    }
}
