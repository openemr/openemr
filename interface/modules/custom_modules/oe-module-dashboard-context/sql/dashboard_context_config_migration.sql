-- Dashboard Context Manager - Additional Migration
-- Adds user_dashboard_context_config table to store widget configs per context per user
-- 
-- The original design stored only ONE widget_config per user in user_dashboard_context.
-- This meant switching contexts would lose the previous context's custom config.
-- 
-- This migration adds a new table that stores widget configs keyed by BOTH user_id AND context_key,
-- allowing each user to have custom configs for each context independently.

-- New table: stores widget configurations per user per context
CREATE TABLE IF NOT EXISTS `user_dashboard_context_config` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL COMMENT 'Reference to users.id',
    `context_key` VARCHAR(50) NOT NULL COMMENT 'Context key (e.g., primary_care, emergency, custom_xyz)',
    `widget_config` TEXT DEFAULT NULL COMMENT 'JSON encoded widget visibility config for this context',
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_context_unique` (`user_id`, `context_key`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_context_key` (`context_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores widget configs per user per context';

-- Migrate existing data from user_dashboard_context.widget_config to the new table
-- This preserves any existing customizations users have made
INSERT INTO `user_dashboard_context_config` (`user_id`, `context_key`, `widget_config`, `created_at`, `updated_at`)
SELECT 
    `user_id`, 
    `active_context`, 
    `widget_config`,
    `created_at`,
    `updated_at`
FROM `user_dashboard_context` 
WHERE `widget_config` IS NOT NULL AND `widget_config` != ''
ON DUPLICATE KEY UPDATE 
    `widget_config` = VALUES(`widget_config`),
    `updated_at` = VALUES(`updated_at`);

-- Optional: Remove widget_config column from user_dashboard_context since it's now in the new table
-- Uncomment the following line if you want to clean up the old column after migration:
-- ALTER TABLE `user_dashboard_context` DROP COLUMN `widget_config`;

-- Note: The user_dashboard_context table now only needs to track:
-- - user_id
-- - active_context (which context is currently selected)
-- - created_at / updated_at
-- 
-- The actual widget configurations are stored in user_dashboard_context_config
-- keyed by (user_id, context_key) so each context can have its own config.
