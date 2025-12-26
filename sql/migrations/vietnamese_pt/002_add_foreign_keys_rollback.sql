-- Rollback for Migration 002: Remove Foreign Key Constraints
-- Author: Dang Tran <tqvdang@msn.com>

SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci;
USE `openemr`;

-- Disable foreign key checks during removal
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- PT Assessments Bilingual - Remove Foreign Keys
-- ============================================================================

ALTER TABLE `pt_assessments_bilingual` DROP FOREIGN KEY IF EXISTS `fk_pt_assessment_patient`;
ALTER TABLE `pt_assessments_bilingual` DROP FOREIGN KEY IF EXISTS `fk_pt_assessment_encounter`;
ALTER TABLE `pt_assessments_bilingual` DROP FOREIGN KEY IF EXISTS `fk_pt_assessment_therapist`;
ALTER TABLE `pt_assessments_bilingual` DROP FOREIGN KEY IF EXISTS `fk_pt_assessment_created_by`;
ALTER TABLE `pt_assessments_bilingual` DROP FOREIGN KEY IF EXISTS `fk_pt_assessment_updated_by`;

-- ============================================================================
-- PT Exercise Prescriptions - Remove Foreign Keys
-- ============================================================================

ALTER TABLE `pt_exercise_prescriptions` DROP FOREIGN KEY IF EXISTS `fk_pt_exercise_patient`;
ALTER TABLE `pt_exercise_prescriptions` DROP FOREIGN KEY IF EXISTS `fk_pt_exercise_assessment`;
ALTER TABLE `pt_exercise_prescriptions` DROP FOREIGN KEY IF EXISTS `fk_pt_exercise_therapist`;

-- ============================================================================
-- PT Outcome Measures - Remove Foreign Keys
-- ============================================================================

ALTER TABLE `pt_outcome_measures` DROP FOREIGN KEY IF EXISTS `fk_pt_outcome_patient`;
ALTER TABLE `pt_outcome_measures` DROP FOREIGN KEY IF EXISTS `fk_pt_outcome_assessment`;
ALTER TABLE `pt_outcome_measures` DROP FOREIGN KEY IF EXISTS `fk_pt_outcome_therapist`;

-- ============================================================================
-- PT Treatment Plans - Remove Foreign Keys
-- ============================================================================

ALTER TABLE `pt_treatment_plans` DROP FOREIGN KEY IF EXISTS `fk_pt_plan_patient`;
ALTER TABLE `pt_treatment_plans` DROP FOREIGN KEY IF EXISTS `fk_pt_plan_created_by`;

-- ============================================================================
-- PT Treatment Sessions - Remove Foreign Keys
-- ============================================================================

ALTER TABLE `pt_treatment_sessions` DROP FOREIGN KEY IF EXISTS `fk_pt_session_patient`;
ALTER TABLE `pt_treatment_sessions` DROP FOREIGN KEY IF EXISTS `fk_pt_session_assessment`;
ALTER TABLE `pt_treatment_sessions` DROP FOREIGN KEY IF EXISTS `fk_pt_session_therapist`;

-- ============================================================================
-- PT Assessment Templates - Remove Foreign Keys
-- ============================================================================

ALTER TABLE `pt_assessment_templates` DROP FOREIGN KEY IF EXISTS `fk_pt_template_created_by`;

-- ============================================================================
-- Vietnamese Insurance Info - Remove Foreign Keys
-- ============================================================================

ALTER TABLE `vietnamese_insurance_info` DROP FOREIGN KEY IF EXISTS `fk_vn_insurance_patient`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Record rollback in tracking table
CALL RecordRollback('002_add_foreign_keys');

-- Display status
SELECT 'Vietnamese PT Foreign Keys Migration Rolled Back' AS status;

-- Verify all foreign keys removed
SELECT
    COUNT(*) AS remaining_foreign_keys
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'openemr'
AND TABLE_NAME LIKE 'pt_%'
AND REFERENCED_TABLE_NAME IS NOT NULL;
