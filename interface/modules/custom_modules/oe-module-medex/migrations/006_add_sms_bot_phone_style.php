<?php
/**
 * Migration 006: Add sms_bot_phone_style column to medex_prefs
 *
 * This column was present in table.sql from the start but was never added to
 * existing installations via migration, causing "Unknown column" errors when
 * saving MedEx Messaging preferences.
 */

namespace Modules\MedEx\Migrations;

use OpenEMR\Common\Database\QueryUtils;

return new class {
    public function up()
    {
        try {
            $tableInfo = QueryUtils::fetchAll("DESCRIBE medex_prefs");
            $existingColumns = array_column($tableInfo, 'Field');

            if (!in_array('sms_bot_phone_style', $existingColumns)) {
                QueryUtils::sqlStatementThrowException(
                    "ALTER TABLE medex_prefs ADD COLUMN sms_bot_phone_style varchar(50) DEFAULT 'S8' AFTER PHONE_country_code"
                );
                error_log("[MedEx Migration 006] Added sms_bot_phone_style column to medex_prefs");
            }
        } catch (\Exception $e) {
            error_log("[MedEx Migration 006] Error: " . $e->getMessage());
            throw $e;
        }

        return true;
    }

    public function down()
    {
        try {
            $tableInfo = QueryUtils::fetchAll("DESCRIBE medex_prefs");
            $existingColumns = array_column($tableInfo, 'Field');

            if (in_array('sms_bot_phone_style', $existingColumns)) {
                QueryUtils::sqlStatementThrowException(
                    "ALTER TABLE medex_prefs DROP COLUMN sms_bot_phone_style"
                );
            }
        } catch (\Exception $e) {
            error_log("[MedEx Migration 006] Rollback error: " . $e->getMessage());
        }

        return true;
    }
};
