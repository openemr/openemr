-- FQHC module — install SQL
--
-- Additive side tables only. No certified table is modified.
--
-- @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3

-- Versioned Federal Poverty Level guidelines (income / FPL band calculation).
-- Stored as data because the figures change yearly and differ by region.
CREATE TABLE IF NOT EXISTS `fqhc_fpl_guideline` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `guideline_year` INT NOT NULL,
    `region` VARCHAR(20) NOT NULL COMMENT 'contiguous | alaska | hawaii',
    `base_annual` DECIMAL(12,2) NOT NULL COMMENT '100%-FPL annual threshold for a 1-person household',
    `per_person_annual` DECIMAL(12,2) NOT NULL COMMENT 'added per additional household member',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_year_region` (`guideline_year`, `region`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2025 HHS Poverty Guidelines (source: ASPE; Federal Register 2025-01377,
-- published 2025-01-17). Verify/extend for each reporting year.
INSERT INTO `fqhc_fpl_guideline` (`guideline_year`, `region`, `base_annual`, `per_person_annual`) VALUES
    (2025, 'contiguous', 15650.00, 5500.00),
    (2025, 'alaska',     19550.00, 6880.00),
    (2025, 'hawaii',     17990.00, 6330.00)
ON DUPLICATE KEY UPDATE
    `base_annual` = VALUES(`base_annual`),
    `per_person_annual` = VALUES(`per_person_annual`);

-- Per-patient income determination (feeds UDS Table 4 income lines and the
-- sliding-fee tier). One current row per patient; history is a later addition.
CREATE TABLE IF NOT EXISTS `fqhc_patient_income` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `pid` BIGINT NOT NULL,
    `household_size` INT DEFAULT NULL,
    `annual_income` DECIMAL(12,2) DEFAULT NULL,
    `income_unknown` TINYINT(1) NOT NULL DEFAULT 0,
    `effective_date` DATE DEFAULT NULL,
    `recorded_by` BIGINT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
