-- Migrate out_date → date_end (upstream column) for legacy installations
-- Safe to run multiple times; only updates rows where date_end is not yet set.

SET @col_out := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'form_encounter'
      AND COLUMN_NAME  = 'out_date'
);

SET @sync_end := IF(
    @col_out > 0,
    "UPDATE form_encounter SET date_end = out_date WHERE out_date IS NOT NULL AND date_end IS NULL",
    "SELECT 1"
);
PREPARE stmt FROM @sync_end; EXECUTE stmt; DEALLOCATE PREPARE stmt;
