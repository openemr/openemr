--
-- SSO Module Database Schema
--
-- @package   OpenEMR
-- @link      https://www.open-emr.org
-- @author    A CTO, LLC
-- @copyright Copyright (c) 2026 A CTO, LLC
-- @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
--

-- Provider configurations
CREATE TABLE IF NOT EXISTS `sso_providers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider_type` VARCHAR(50) NOT NULL COMMENT 'entra, google, okta, generic_oidc',
    `name` VARCHAR(100) NOT NULL COMMENT 'Display name',
    `enabled` TINYINT(1) DEFAULT 0,
    `config` JSON NOT NULL COMMENT 'Provider-specific config (client_id, client_secret encrypted, etc.)',
    `auto_provision` TINYINT(1) DEFAULT 0 COMMENT 'Auto-create users on first login',
    `default_acl` VARCHAR(100) DEFAULT NULL COMMENT 'Default ACL for auto-provisioned users',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_provider_type` (`provider_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Group to ACL mappings
CREATE TABLE IF NOT EXISTS `sso_group_mappings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider_id` INT NOT NULL,
    `idp_group` VARCHAR(255) NOT NULL COMMENT 'Group ID/name from IdP',
    `openemr_acl` VARCHAR(100) NOT NULL COMMENT 'OpenEMR ACL name',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_provider_group` (`provider_id`, `idp_group`),
    FOREIGN KEY (`provider_id`) REFERENCES `sso_providers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User links between IdP and OpenEMR users
CREATE TABLE IF NOT EXISTS `sso_user_links` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT NOT NULL COMMENT 'OpenEMR users.id',
    `provider_id` INT NOT NULL,
    `provider_user_id` VARCHAR(255) NOT NULL COMMENT 'OID/sub from IdP',
    `email` VARCHAR(255) DEFAULT NULL,
    `linked_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `last_login` DATETIME DEFAULT NULL,
    UNIQUE KEY `idx_provider_user` (`provider_id`, `provider_user_id`),
    KEY `idx_user_id` (`user_id`),
    FOREIGN KEY (`provider_id`) REFERENCES `sso_providers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PKCE and state storage for auth flow
CREATE TABLE IF NOT EXISTS `sso_auth_states` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `state` VARCHAR(128) NOT NULL COMMENT 'CSRF state parameter',
    `nonce` VARCHAR(128) NOT NULL COMMENT 'Nonce for replay protection',
    `code_verifier` VARCHAR(128) NOT NULL COMMENT 'PKCE code verifier',
    `provider_id` INT NOT NULL,
    `site_id` VARCHAR(64) DEFAULT 'default' COMMENT 'OpenEMR site identifier',
    `redirect_uri` VARCHAR(512) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME NOT NULL,
    UNIQUE KEY `idx_state` (`state`),
    KEY `idx_expires` (`expires_at`),
    FOREIGN KEY (`provider_id`) REFERENCES `sso_providers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SSO audit log
CREATE TABLE IF NOT EXISTS `sso_audit_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider_id` INT DEFAULT NULL,
    `user_id` BIGINT DEFAULT NULL,
    `event_type` VARCHAR(50) NOT NULL COMMENT 'login_success, login_failure, logout, provision, link',
    `event_data` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(512) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_provider` (`provider_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
