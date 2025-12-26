-- Vietnamese PT Foreign Key Constraints Migration
-- Adds referential integrity constraints to ensure data consistency
-- Author: Dang Tran <tqvdang@msn.com>
--
-- Foreign Key Strategy:
-- - ON DELETE RESTRICT: Prevent deletion of referenced records (patient_data, users)
-- - ON DELETE CASCADE: Auto-delete child records (exercises when assessment deleted)
-- - ON DELETE SET NULL: Preserve record but clear reference (when therapist deleted)
-- - ON UPDATE CASCADE: Auto-update references when IDs change
--
-- Benefits:
-- 1. Data integrity - prevents orphaned records
-- 2. Automatic cleanup - cascading deletes
-- 3. Database-level enforcement - can't bypass in code
-- 4. Better query optimization - MySQL uses FK for joins

SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci;
USE `openemr`;

-- Disable foreign key checks temporarily for smooth application
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- PT Assessments Bilingual - Foreign Keys
-- ============================================================================

-- Patient reference (RESTRICT - can't delete patient with assessments)
ALTER TABLE `pt_assessments_bilingual`
ADD CONSTRAINT `fk_pt_assessment_patient`
  FOREIGN KEY (`patient_id`)
  REFERENCES `patient_data` (`pid`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE
  COMMENT 'Links assessment to patient - prevents patient deletion if assessments exist';

-- Encounter reference (SET NULL - preserve assessment if encounter deleted)
ALTER TABLE `pt_assessments_bilingual`
ADD CONSTRAINT `fk_pt_assessment_encounter`
  FOREIGN KEY (`encounter_id`)
  REFERENCES `form_encounter` (`encounter`)
  ON DELETE SET NULL
  ON UPDATE CASCADE
  COMMENT 'Links assessment to encounter - allows encounter deletion';

-- Therapist reference (SET NULL - preserve assessment if therapist user deleted)
ALTER TABLE `pt_assessments_bilingual`
ADD CONSTRAINT `fk_pt_assessment_therapist`
  FOREIGN KEY (`therapist_id`)
  REFERENCES `users` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE
  COMMENT 'Links assessment to therapist user - allows user deletion';

-- Created by reference (SET NULL - preserve audit trail reference)
ALTER TABLE `pt_assessments_bilingual`
ADD CONSTRAINT `fk_pt_assessment_created_by`
  FOREIGN KEY (`created_by`)
  REFERENCES `users` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE
  COMMENT 'Audit trail - user who created assessment';

-- Updated by reference (SET NULL - preserve audit trail reference)
ALTER TABLE `pt_assessments_bilingual`
ADD CONSTRAINT `fk_pt_assessment_updated_by`
  FOREIGN KEY (`updated_by`)
  REFERENCES `users` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE
  COMMENT 'Audit trail - user who last updated assessment';

-- ============================================================================
-- PT Exercise Prescriptions - Foreign Keys
-- ============================================================================

-- Patient reference (RESTRICT - can't delete patient with prescriptions)
ALTER TABLE `pt_exercise_prescriptions`
ADD CONSTRAINT `fk_pt_exercise_patient`
  FOREIGN KEY (`patient_id`)
  REFERENCES `patient_data` (`pid`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE
  COMMENT 'Links prescription to patient - prevents patient deletion';

-- Assessment reference (CASCADE - delete prescriptions when assessment deleted)
ALTER TABLE `pt_exercise_prescriptions`
ADD CONSTRAINT `fk_pt_exercise_assessment`
  FOREIGN KEY (`assessment_id`)
  REFERENCES `pt_assessments_bilingual` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE
  COMMENT 'Links prescription to assessment - cascades assessment deletion';

-- Therapist reference (SET NULL - preserve prescription if therapist deleted)
ALTER TABLE `pt_exercise_prescriptions`
ADD CONSTRAINT `fk_pt_exercise_therapist`
  FOREIGN KEY (`therapist_id`)
  REFERENCES `users` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE
  COMMENT 'Links prescription to prescribing therapist';

-- ============================================================================
-- PT Outcome Measures - Foreign Keys
-- ============================================================================

-- Patient reference (RESTRICT - can't delete patient with outcome measures)
ALTER TABLE `pt_outcome_measures`
ADD CONSTRAINT `fk_pt_outcome_patient`
  FOREIGN KEY (`patient_id`)
  REFERENCES `patient_data` (`pid`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE
  COMMENT 'Links outcome measure to patient - prevents patient deletion';

-- Assessment reference (CASCADE - delete outcomes when assessment deleted)
ALTER TABLE `pt_outcome_measures`
ADD CONSTRAINT `fk_pt_outcome_assessment`
  FOREIGN KEY (`assessment_id`)
  REFERENCES `pt_assessments_bilingual` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE
  COMMENT 'Links outcome to assessment - cascades assessment deletion';

-- Therapist reference (SET NULL - preserve outcome if therapist deleted)
ALTER TABLE `pt_outcome_measures`
ADD CONSTRAINT `fk_pt_outcome_therapist`
  FOREIGN KEY (`therapist_id`)
  REFERENCES `users` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE
  COMMENT 'Links outcome to measuring therapist';

-- ============================================================================
-- PT Treatment Plans - Foreign Keys
-- ============================================================================

-- Patient reference (RESTRICT - can't delete patient with treatment plans)
ALTER TABLE `pt_treatment_plans`
ADD CONSTRAINT `fk_pt_plan_patient`
  FOREIGN KEY (`patient_id`)
  REFERENCES `patient_data` (`pid`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE
  COMMENT 'Links treatment plan to patient - prevents patient deletion';

-- Created by reference (SET NULL - preserve plan if creator deleted)
ALTER TABLE `pt_treatment_plans`
ADD CONSTRAINT `fk_pt_plan_created_by`
  FOREIGN KEY (`created_by`)
  REFERENCES `users` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE
  COMMENT 'Audit trail - user who created treatment plan';

-- ============================================================================
-- PT Treatment Sessions - Foreign Keys
-- ============================================================================

-- Patient reference (RESTRICT - can't delete patient with session history)
ALTER TABLE `pt_treatment_sessions`
ADD CONSTRAINT `fk_pt_session_patient`
  FOREIGN KEY (`patient_id`)
  REFERENCES `patient_data` (`pid`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE
  COMMENT 'Links session to patient - prevents patient deletion';

-- Assessment reference (SET NULL - preserve session if assessment deleted)
-- Note: Using SET NULL instead of CASCADE to preserve historical session data
ALTER TABLE `pt_treatment_sessions`
ADD CONSTRAINT `fk_pt_session_assessment`
  FOREIGN KEY (`assessment_id`)
  REFERENCES `pt_assessments_bilingual` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE
  COMMENT 'Links session to assessment - preserves session history';

-- Therapist reference (SET NULL - preserve session if therapist deleted)
ALTER TABLE `pt_treatment_sessions`
ADD CONSTRAINT `fk_pt_session_therapist`
  FOREIGN KEY (`therapist_id`)
  REFERENCES `users` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE
  COMMENT 'Links session to conducting therapist';

-- ============================================================================
-- PT Assessment Templates - Foreign Keys
-- ============================================================================

-- Created by reference (SET NULL - preserve template if creator deleted)
ALTER TABLE `pt_assessment_templates`
ADD CONSTRAINT `fk_pt_template_created_by`
  FOREIGN KEY (`created_by`)
  REFERENCES `users` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE
  COMMENT 'Audit trail - user who created template';

-- ============================================================================
-- Vietnamese Insurance Info - Foreign Keys
-- ============================================================================

-- Patient reference (CASCADE - delete insurance when patient deleted)
ALTER TABLE `vietnamese_insurance_info`
ADD CONSTRAINT `fk_vn_insurance_patient`
  FOREIGN KEY (`patient_id`)
  REFERENCES `patient_data` (`pid`)
  ON DELETE CASCADE
  ON UPDATE CASCADE
  COMMENT 'Links insurance to patient - cascades patient deletion';

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- Verify Foreign Keys
-- ============================================================================

-- Record migration in tracking table
CALL RecordMigration(
    '002_add_foreign_keys',
    'Add Foreign Key Constraints',
    'Adds referential integrity constraints to Vietnamese PT tables for data consistency',
    NULL,
    'system'
);

-- Display summary of foreign keys created
SELECT
    'Vietnamese PT Foreign Keys Migration Completed' AS status,
    (SELECT COUNT(*)
     FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = 'openemr'
     AND TABLE_NAME LIKE 'pt_%'
     AND REFERENCED_TABLE_NAME IS NOT NULL) AS total_foreign_keys,
    (SELECT COUNT(DISTINCT TABLE_NAME)
     FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = 'openemr'
     AND TABLE_NAME LIKE 'pt_%'
     AND REFERENCED_TABLE_NAME IS NOT NULL) AS tables_with_fk;

-- List all foreign keys created
SELECT
    TABLE_NAME,
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'openemr'
AND TABLE_NAME LIKE 'pt_%'
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME;

-- ============================================================================
-- Important Notes
-- ============================================================================

/*
 * Foreign Key Behavior Summary:
 *
 * 1. Patient Deletion Prevention:
 *    - Cannot delete patients who have PT assessments, exercises, outcomes, plans, or sessions
 *    - Must delete/archive PT records first before patient deletion
 *
 * 2. Assessment Deletion Cascades:
 *    - Deleting an assessment auto-deletes related exercises and outcomes
 *    - Sessions are preserved with NULL assessment_id for historical tracking
 *
 * 3. User/Therapist Deletion:
 *    - Deleting a therapist user sets their ID to NULL in all PT records
 *    - Records are preserved for audit trail and historical data
 *
 * 4. Encounter Deletion:
 *    - Deleting an encounter sets encounter_id to NULL in assessments
 *    - Assessments are preserved independently
 *
 * 5. Insurance Deletion:
 *    - Patient deletion automatically deletes their insurance records
 *
 * Performance Impact:
 * - Foreign keys add minimal overhead on INSERT/UPDATE operations
 * - Provide significant benefit for DELETE operations (integrity checks)
 * - Improve query optimization through constraint metadata
 *
 * Migration Rollback:
 * - Use 002_add_foreign_keys_rollback.sql to remove all constraints
 * - Safe to rollback if referential integrity issues discovered
 */
