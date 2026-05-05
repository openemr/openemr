<?php
/**
 * Migration 004: Add provider token columns and user tracking to secure chat tables
 * 
 * Adds support for provider tokens and user identification in secure chat
 * - is_provider: tracks whether a token is for provider or patient
 * - user_initials: tracks who created the token (in both tables)
 */

namespace Modules\MedEx\Migrations;

use OpenEMR\Common\Database\QueryUtils;

return new class {
    public function up()
    {
        $changes = [];

        // Handle medex_secure_chat_tokens table
        try {
            $tableInfo = QueryUtils::fetchAll("DESCRIBE medex_secure_chat_tokens");
            $existingColumns = array_column($tableInfo, 'Field');

            // Add is_provider column if it doesn't exist
            if (!in_array('is_provider', $existingColumns)) {
                $changes[] = "ALTER TABLE medex_secure_chat_tokens ADD COLUMN is_provider tinyint(1) DEFAULT 0 COMMENT 'Whether this is a provider token (can see all messages)' AFTER method";
            }

            // Add user_initials column if it doesn't exist
            if (!in_array('user_initials', $existingColumns)) {
                $changes[] = "ALTER TABLE medex_secure_chat_tokens ADD COLUMN user_initials varchar(4) DEFAULT NULL COMMENT 'Initials of user who created token (JD, etc.)' AFTER is_provider";
            }
        } catch (\Exception $e) {
            error_log("[MedEx Migration 004] Error checking medex_secure_chat_tokens: " . $e->getMessage());
        }

        // Handle medex_secure_chat_log table
        try {
            $tableInfo = QueryUtils::fetchAll("DESCRIBE medex_secure_chat_log");
            $existingColumns = array_column($tableInfo, 'Field');

            // Add user_initials column if it doesn't exist
            if (!in_array('user_initials', $existingColumns)) {
                $changes[] = "ALTER TABLE medex_secure_chat_log ADD COLUMN user_initials varchar(4) DEFAULT NULL COMMENT 'Initials of user who initiated (JD, etc.)' AFTER created_by";
            }
        } catch (\Exception $e) {
            error_log("[MedEx Migration 004] Error checking medex_secure_chat_log: " . $e->getMessage());
        }

        // Execute changes
        if (!empty($changes)) {
            foreach ($changes as $sql) {
                QueryUtils::sqlStatement($sql);
            }
            error_log("[MedEx Migration 004] Successfully added provider token and user tracking columns");
        } else {
            error_log("[MedEx Migration 004] Columns already exist, skipping");
        }

        return true;
    }

    public function down()
    {
        $changes = [];

        // Handle medex_secure_chat_tokens table
        try {
            $tableInfo = QueryUtils::fetchAll("DESCRIBE medex_secure_chat_tokens");
            $existingColumns = array_column($tableInfo, 'Field');

            if (in_array('user_initials', $existingColumns)) {
                $changes[] = "ALTER TABLE medex_secure_chat_tokens DROP COLUMN user_initials";
            }

            if (in_array('is_provider', $existingColumns)) {
                $changes[] = "ALTER TABLE medex_secure_chat_tokens DROP COLUMN is_provider";
            }
        } catch (\Exception $e) {
            error_log("[MedEx Migration 004] Error rolling back medex_secure_chat_tokens: " . $e->getMessage());
        }

        // Handle medex_secure_chat_log table
        try {
            $tableInfo = QueryUtils::fetchAll("DESCRIBE medex_secure_chat_log");
            $existingColumns = array_column($tableInfo, 'Field');

            if (in_array('user_initials', $existingColumns)) {
                $changes[] = "ALTER TABLE medex_secure_chat_log DROP COLUMN user_initials";
            }
        } catch (\Exception $e) {
            error_log("[MedEx Migration 004] Error rolling back medex_secure_chat_log: " . $e->getMessage());
        }

        if (!empty($changes)) {
            foreach ($changes as $sql) {
                QueryUtils::sqlStatement($sql);
            }
            error_log("[MedEx Migration 004] Successfully removed provider token and user tracking columns");
        }

        return true;
    }
};
