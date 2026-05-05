<?php
/**
 * Migration: Create medex_migrations table
 *
 * Tracks which database migrations have been applied
 */

// Create migrations tracking table
$sql = "CREATE TABLE IF NOT EXISTS medex_migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration_name VARCHAR(255) NOT NULL UNIQUE,
    applied_at DATETIME NOT NULL,
    INDEX idx_migration_name (migration_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

sqlStatement($sql);

error_log('[MedEx Migration] Created medex_migrations table');
