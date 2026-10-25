-- General Dermatology form — schema upgrade script
-- Apply to any existing installation that predates table.sql changes made alongside
-- the procedure-details printable fix (2026-05).
--
-- All statements use ALTER TABLE ... ADD/MODIFY IF NOT EXISTS patterns where supported,
-- or are safe to run more than once on MariaDB 10.3+ / MySQL 8.0+ via IF NOT EXISTS.

-- 1. Add encounter column (links form row to form_encounter)
ALTER TABLE `general_dermatology`
    ADD COLUMN IF NOT EXISTS `encounter` int(10) DEFAULT NULL;

-- 2. Add Select_All column (stores 'on' when the provider selects all body-exam sites)
ALTER TABLE `general_dermatology`
    ADD COLUMN IF NOT EXISTS `Select_All` varchar(255) DEFAULT NULL;

-- 3. Widen subjective_text from varchar(255) to longtext
--    (safe: LONGTEXT is a superset; existing data is preserved)
ALTER TABLE `general_dermatology`
    MODIFY COLUMN `subjective_text` longtext DEFAULT NULL;

-- 4. Widen total_severity from varchar(255) to TEXT
--    (stores JSON severity-count objects; varchar(255) could silently truncate)
ALTER TABLE `general_dermatology`
    MODIFY COLUMN `total_severity` text DEFAULT NULL;

-- 5. Add new sites examined checkbox fields
ALTER TABLE `general_dermatology`
    ADD COLUMN IF NOT EXISTS `buttocks` tinyint(4) DEFAULT 0;

ALTER TABLE `general_dermatology`
    ADD COLUMN IF NOT EXISTS `axillae` tinyint(4) DEFAULT 0;

-- 6. Template manager metadata (Phase 1)
ALTER TABLE `general_dermatology`
    ADD COLUMN IF NOT EXISTS `template_status` varchar(20) DEFAULT 'active';

ALTER TABLE `general_dermatology`
    ADD COLUMN IF NOT EXISTS `template_updated_at` datetime DEFAULT NULL;

ALTER TABLE `general_dermatology`
    ADD COLUMN IF NOT EXISTS `template_created_by` varchar(255) DEFAULT NULL;

ALTER TABLE `general_dermatology`
    ADD COLUMN IF NOT EXISTS `template_description` varchar(500) DEFAULT NULL;

-- Backfill existing named templates as active
UPDATE `general_dermatology`
SET `template_status` = 'active'
WHERE IFNULL(`general_dermatology_form_template_name`, '') <> ''
  AND IFNULL(`template_status`, '') = '';


-- 7. Phase 2: nullable pid for templates + lookup indexes
ALTER TABLE `general_dermatology`
    MODIFY COLUMN `pid` bigint(20) NULL DEFAULT NULL;

UPDATE `general_dermatology`
SET `pid` = NULL
WHERE IFNULL(`general_dermatology_form_template_name`, '') <> ''
  AND (`pid` = 0 OR `pid` IS NULL);

ALTER TABLE `general_dermatology`
    ADD INDEX IF NOT EXISTS `idx_gd_pid_encounter` (`pid`, `encounter`);

ALTER TABLE `general_dermatology`
    ADD INDEX IF NOT EXISTS `idx_gd_template_name` (`general_dermatology_form_template_name`);

ALTER TABLE `general_dermatology`
    ADD INDEX IF NOT EXISTS `idx_gd_template_status` (`template_status`);
