--
-- Dashboard Context Manager Module
-- Install and Uninstall SQL Scripts
--
-- @package   OpenEMR
-- @link      http://www.open-emr.org
-- @author    Jerry Padgett <sjpadgett@gmail.com>
-- @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
-- @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
--

-- ============================================================================
-- INSTALL SECTION
-- ============================================================================

#IfNotTable user_dashboard_context
-- User's active context tracking (which context is currently selected)
CREATE TABLE IF NOT EXISTS `user_dashboard_context` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL COMMENT 'Reference to users.id',
    `active_context` VARCHAR(50) NOT NULL DEFAULT 'primary_care' COMMENT 'Current active context key',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_id` (`user_id`),
    KEY `idx_active_context` (`active_context`)
) ENGINE=InnoDB COMMENT='Tracks active dashboard context per user';
#EndIf

#IfNotTable user_dashboard_context_config
-- Widget configurations stored per user per context
-- This allows each user to have different widget settings for each context
CREATE TABLE IF NOT EXISTS `user_dashboard_context_config` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL COMMENT 'Reference to users.id',
    `context_key` VARCHAR(50) NOT NULL COMMENT 'Context key (e.g., primary_care, emergency)',
    `widget_config` TEXT COMMENT 'JSON encoded widget visibility config',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_context` (`user_id`, `context_key`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_context_key` (`context_key`)
) ENGINE=InnoDB COMMENT='Widget configs per user per context';
#EndIf

#IfNotTable dashboard_context_definitions
-- Custom context definitions (user-created or global custom contexts)
CREATE TABLE IF NOT EXISTS `dashboard_context_definitions` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) DEFAULT NULL COMMENT 'NULL for global contexts, user ID for personal',
    `context_key` VARCHAR(50) NOT NULL COMMENT 'Unique key for the context',
    `context_name` VARCHAR(100) NOT NULL COMMENT 'Display name',
    `description` TEXT COMMENT 'Description of the context',
    `widget_config` TEXT COMMENT 'JSON encoded default widget config',
    `is_global` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 if available to all users',
    `sort_order` INT(11) DEFAULT 0 COMMENT 'Display order',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_context_key` (`context_key`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_is_global` (`is_global`)
) ENGINE=InnoDB COMMENT='Custom dashboard context definitions';
#EndIf

#IfNotTable dashboard_context_assignments
-- Admin-assigned context assignments to users
CREATE TABLE IF NOT EXISTS `dashboard_context_assignments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL COMMENT 'User being assigned',
    `context_id` INT(11) DEFAULT NULL COMMENT 'Reference to dashboard_context_definitions.id',
    `context_key` VARCHAR(50) NOT NULL COMMENT 'Context key being assigned',
    `assigned_by` INT(11) NOT NULL COMMENT 'Admin user who made assignment',
    `is_locked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 if user cannot change context',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 if this is the active assignment',
    `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `notes` TEXT COMMENT 'Optional notes about the assignment',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_context_key` (`context_key`),
    KEY `idx_assigned_by` (`assigned_by`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB COMMENT='Admin context assignments to users';
#EndIf

#IfNotTable dashboard_context_role_defaults
-- Role-based default context settings
CREATE TABLE IF NOT EXISTS `dashboard_context_role_defaults` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `role_type` VARCHAR(50) NOT NULL COMMENT 'User role/type identifier',
    `context_key` VARCHAR(50) NOT NULL COMMENT 'Default context for this role',
    `created_by` INT(11) DEFAULT NULL COMMENT 'Admin who created this default',
    `updated_by` INT(11) DEFAULT NULL COMMENT 'Admin who last updated',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_role_type` (`role_type`),
    KEY `idx_context_key` (`context_key`)
) ENGINE=InnoDB COMMENT='Default contexts per user role';
#EndIf

#IfNotTable dashboard_context_audit_log
-- Audit log for context changes
CREATE TABLE IF NOT EXISTS `dashboard_context_audit_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL COMMENT 'User whose context changed',
    `action` VARCHAR(50) NOT NULL COMMENT 'Action type: switch, assign, lock, unlock',
    `old_context` VARCHAR(50) DEFAULT NULL COMMENT 'Previous context',
    `new_context` VARCHAR(50) DEFAULT NULL COMMENT 'New context',
    `performed_by` INT(11) NOT NULL COMMENT 'User who performed action',
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_performed_by` (`performed_by`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB COMMENT='Audit log for context changes';
#EndIf

#IfNotTable dashboard_widget_order
-- Widget display order per context (and optionally per user)
CREATE TABLE IF NOT EXISTS `dashboard_widget_order` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `context_key` VARCHAR(50) NOT NULL,
    `user_id` INT(11) DEFAULT NULL COMMENT 'NULL for context-level defaults, user ID for personal overrides',
    `widget_order` TEXT NOT NULL COMMENT 'JSON array of widget IDs in display order',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_context_user` (`context_key`, `user_id`),
    KEY `idx_context_key` (`context_key`),
    KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB COMMENT='Widget display order per context';
#EndIf

#IfNotTable dashboard_widget_labels
-- Custom widget labels per context
CREATE TABLE IF NOT EXISTS `dashboard_widget_labels` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `context_key` VARCHAR(50) NOT NULL,
    `widget_id` VARCHAR(100) NOT NULL,
    `custom_label` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_context_widget` (`context_key`, `widget_id`),
    KEY `idx_context_key` (`context_key`)
) ENGINE=InnoDB COMMENT='Custom widget labels per context';
#EndIf

-- ============================================================================
-- UNINSTALL SECTION
-- ============================================================================

-- To uninstall, run the following DROP TABLE statements:
-- WARNING: This will permanently delete all dashboard context data!

-- DROP TABLE IF EXISTS `dashboard_context_audit_log`;
-- DROP TABLE IF EXISTS `dashboard_widget_labels`;
-- DROP TABLE IF EXISTS `dashboard_widget_order`;
-- DROP TABLE IF EXISTS `dashboard_context_facility_defaults`;
-- DROP TABLE IF EXISTS `dashboard_context_role_defaults`;
-- DROP TABLE IF EXISTS `dashboard_context_assignments`;
-- DROP TABLE IF EXISTS `dashboard_context_definitions`;
-- DROP TABLE IF EXISTS `user_dashboard_context_config`;
-- DROP TABLE IF EXISTS `user_dashboard_context`;
