-- Rollback for Migration 001: Remove Performance Indexes
-- Author: Dang Tran <tqvdang@msn.com>

SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci;
USE `openemr`;

-- ============================================================================
-- PT Assessments Bilingual - Remove Indexes
-- ============================================================================

DROP INDEX IF EXISTS `idx_patient_date_composite` ON `pt_assessments_bilingual`;
DROP INDEX IF EXISTS `idx_encounter_lookup` ON `pt_assessments_bilingual`;
DROP INDEX IF EXISTS `idx_therapist_created` ON `pt_assessments_bilingual`;
DROP INDEX IF EXISTS `idx_assessment_date_range` ON `pt_assessments_bilingual`;
DROP INDEX IF EXISTS `idx_created_updated_at` ON `pt_assessments_bilingual`;
DROP INDEX IF EXISTS `idx_audit_trail` ON `pt_assessments_bilingual`;
DROP INDEX IF EXISTS `idx_language_status` ON `pt_assessments_bilingual`;
DROP INDEX IF EXISTS `idx_stats_composite` ON `pt_assessments_bilingual`;

-- ============================================================================
-- PT Exercise Prescriptions - Remove Indexes
-- ============================================================================

DROP INDEX IF EXISTS `idx_patient_status_date` ON `pt_exercise_prescriptions`;
DROP INDEX IF EXISTS `idx_assessment_exercises` ON `pt_exercise_prescriptions`;
DROP INDEX IF EXISTS `idx_therapist_prescriptions` ON `pt_exercise_prescriptions`;
DROP INDEX IF EXISTS `idx_prescription_dates` ON `pt_exercise_prescriptions`;
DROP INDEX IF EXISTS `idx_category_difficulty` ON `pt_exercise_prescriptions`;
DROP INDEX IF EXISTS `idx_compliance_tracking` ON `pt_exercise_prescriptions`;
DROP INDEX IF EXISTS `idx_recent_prescriptions` ON `pt_exercise_prescriptions`;

-- ============================================================================
-- PT Outcome Measures - Remove Indexes
-- ============================================================================

DROP INDEX IF EXISTS `idx_patient_measurements` ON `pt_outcome_measures`;
DROP INDEX IF EXISTS `idx_progress_tracking` ON `pt_outcome_measures`;
DROP INDEX IF EXISTS `idx_assessment_outcomes` ON `pt_outcome_measures`;
DROP INDEX IF EXISTS `idx_baseline_measures` ON `pt_outcome_measures`;
DROP INDEX IF EXISTS `idx_clinical_significance` ON `pt_outcome_measures`;
DROP INDEX IF EXISTS `idx_followup_tracking` ON `pt_outcome_measures`;
DROP INDEX IF EXISTS `idx_therapist_outcomes` ON `pt_outcome_measures`;

-- ============================================================================
-- PT Treatment Plans - Remove Indexes
-- ============================================================================

DROP INDEX IF EXISTS `idx_patient_plan_status` ON `pt_treatment_plans`;
DROP INDEX IF EXISTS `idx_plan_dates` ON `pt_treatment_plans`;
DROP INDEX IF EXISTS `idx_plan_status_date` ON `pt_treatment_plans`;
DROP INDEX IF EXISTS `idx_plan_creator` ON `pt_treatment_plans`;

-- ============================================================================
-- PT Treatment Sessions - Remove Indexes
-- ============================================================================

DROP INDEX IF EXISTS `idx_patient_sessions` ON `pt_treatment_sessions`;
DROP INDEX IF EXISTS `idx_assessment_sessions` ON `pt_treatment_sessions`;
DROP INDEX IF EXISTS `idx_therapist_schedule` ON `pt_treatment_sessions`;
DROP INDEX IF EXISTS `idx_session_type_date` ON `pt_treatment_sessions`;
DROP INDEX IF EXISTS `idx_session_compliance` ON `pt_treatment_sessions`;
DROP INDEX IF EXISTS `idx_pain_tracking` ON `pt_treatment_sessions`;

-- ============================================================================
-- PT Assessment Templates - Remove Indexes
-- ============================================================================

DROP INDEX IF EXISTS `idx_template_category_active` ON `pt_assessment_templates`;
DROP INDEX IF EXISTS `idx_template_body_region` ON `pt_assessment_templates`;
DROP INDEX IF EXISTS `idx_template_creator` ON `pt_assessment_templates`;

-- ============================================================================
-- Vietnamese Medical Terms - Remove Indexes
-- ============================================================================

DROP INDEX IF EXISTS `idx_category_active_term` ON `vietnamese_medical_terms`;
DROP INDEX IF EXISTS `idx_subcategory_active` ON `vietnamese_medical_terms`;
DROP INDEX IF EXISTS `idx_abbreviation_lookup` ON `vietnamese_medical_terms`;

-- ============================================================================
-- Vietnamese Insurance Info - Remove Indexes
-- ============================================================================

DROP INDEX IF EXISTS `idx_patient_insurance_active` ON `vietnamese_insurance_info`;
DROP INDEX IF EXISTS `idx_validity_range` ON `vietnamese_insurance_info`;
DROP INDEX IF EXISTS `idx_hospital_lookup` ON `vietnamese_insurance_info`;

-- Record rollback in tracking table
CALL RecordRollback('001_add_indexes');

SELECT 'Vietnamese PT Performance Indexes Migration Rolled Back' AS status;
