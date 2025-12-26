-- Vietnamese PT Migration Tracking Schema
-- Tracks which migrations have been applied to the database
-- Author: Dang Tran <tqvdang@msn.com>

SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci;
USE `openemr`;

-- Migration tracking table
CREATE TABLE IF NOT EXISTS `vietnamese_pt_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration_id` varchar(50) COLLATE utf8mb4_vietnamese_ci NOT NULL COMMENT 'Migration identifier (e.g., 001_add_indexes)',
  `migration_name` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL COMMENT 'Human-readable migration name',
  `description` text COLLATE utf8mb4_vietnamese_ci COMMENT 'What this migration does',
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When migration was applied',
  `applied_by` varchar(100) COLLATE utf8mb4_vietnamese_ci DEFAULT 'system' COMMENT 'Who/what applied the migration',
  `status` enum('pending', 'applied', 'rolled_back', 'failed') DEFAULT 'applied' COMMENT 'Migration status',
  `execution_time_ms` int(11) DEFAULT NULL COMMENT 'Time taken to execute migration in milliseconds',
  `error_message` text COLLATE utf8mb4_vietnamese_ci DEFAULT NULL COMMENT 'Error message if migration failed',
  `rollback_at` timestamp NULL DEFAULT NULL COMMENT 'When migration was rolled back',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_migration_id` (`migration_id`),
  KEY `idx_status` (`status`),
  KEY `idx_applied_at` (`applied_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci
COMMENT='Tracks Vietnamese PT database migrations';

-- Log the creation of migration tracking table
INSERT INTO `vietnamese_pt_migrations`
(`migration_id`, `migration_name`, `description`, `applied_by`, `status`) VALUES
('000_migration_schema', 'Migration Tracking Schema',
 'Initial migration tracking table for Vietnamese PT module',
 'system', 'applied');

-- View to show migration history
CREATE OR REPLACE VIEW `vietnamese_pt_migration_history` AS
SELECT
    migration_id,
    migration_name,
    description,
    status,
    applied_at,
    applied_by,
    CONCAT(ROUND(execution_time_ms / 1000, 2), ' seconds') AS execution_time,
    error_message,
    rollback_at
FROM vietnamese_pt_migrations
ORDER BY applied_at DESC;

-- Stored procedure to check if migration has been applied
DELIMITER //

CREATE PROCEDURE CheckMigrationStatus(
    IN p_migration_id VARCHAR(50)
)
BEGIN
    SELECT
        CASE
            WHEN COUNT(*) > 0 THEN 'applied'
            ELSE 'not_applied'
        END AS migration_status,
        MAX(applied_at) AS last_applied
    FROM vietnamese_pt_migrations
    WHERE migration_id = p_migration_id
      AND status = 'applied';
END //

-- Stored procedure to record migration application
CREATE PROCEDURE RecordMigration(
    IN p_migration_id VARCHAR(50),
    IN p_migration_name VARCHAR(255),
    IN p_description TEXT,
    IN p_execution_time_ms INT,
    IN p_applied_by VARCHAR(100)
)
BEGIN
    INSERT INTO vietnamese_pt_migrations
    (migration_id, migration_name, description, execution_time_ms, applied_by, status)
    VALUES (p_migration_id, p_migration_name, p_description, p_execution_time_ms, p_applied_by, 'applied')
    ON DUPLICATE KEY UPDATE
        applied_at = CURRENT_TIMESTAMP,
        execution_time_ms = p_execution_time_ms,
        status = 'applied',
        error_message = NULL;
END //

-- Stored procedure to record migration rollback
CREATE PROCEDURE RecordRollback(
    IN p_migration_id VARCHAR(50)
)
BEGIN
    UPDATE vietnamese_pt_migrations
    SET status = 'rolled_back',
        rollback_at = CURRENT_TIMESTAMP
    WHERE migration_id = p_migration_id;
END //

DELIMITER ;

-- Display successful initialization
SELECT 'Vietnamese PT migration tracking schema created successfully' AS status;
