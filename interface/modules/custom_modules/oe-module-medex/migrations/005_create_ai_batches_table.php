<?php
/**
 * Migration 005: Create medex_ai_batches table
 *
 * Tracks AI-generated calendar events by batch for undo/rollback support.
 * Each row maps a batch_id to a pc_eid in openemr_postcalendar_events.
 */

namespace Modules\MedEx\Migrations;

use OpenEMR\Common\Database\QueryUtils;

return new class {
    public function up()
    {
        // Check if table already exists
        try {
            $check = QueryUtils::fetchAll("SHOW TABLES LIKE 'medex_ai_batches'");
            if (!empty($check)) {
                error_log("[MedEx Migration 005] medex_ai_batches table already exists, skipping");
                return true;
            }
        } catch (\Exception $e) {
            // Table doesn't exist, proceed with creation
        }

        $sql = "CREATE TABLE `medex_ai_batches` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `batch_id` varchar(50) NOT NULL COMMENT 'Unique batch identifier (AI_BATCH_xxx)',
            `pc_eid` int(11) NOT NULL COMMENT 'FK to openemr_postcalendar_events.pc_eid',
            `provider_id` int(11) DEFAULT NULL COMMENT 'Provider ID for this event',
            `facility_id` int(11) DEFAULT NULL COMMENT 'Facility ID for this event',
            `event_date` date DEFAULT NULL COMMENT 'Date of the created event',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `created_by` int(11) DEFAULT NULL COMMENT 'authUserID of creator',
            `undone_at` datetime DEFAULT NULL COMMENT 'When batch was undone (NULL=active)',
            `undone_by` int(11) DEFAULT NULL COMMENT 'authUserID who undid the batch',
            PRIMARY KEY (`id`),
            INDEX `idx_batch_id` (`batch_id`),
            INDEX `idx_pc_eid` (`pc_eid`),
            INDEX `idx_undone` (`undone_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        QueryUtils::sqlStatement($sql);
        error_log("[MedEx Migration 005] Created medex_ai_batches table");

        return true;
    }

    public function down()
    {
        QueryUtils::sqlStatement("DROP TABLE IF EXISTS `medex_ai_batches`");
        error_log("[MedEx Migration 005] Dropped medex_ai_batches table");
        return true;
    }
};
