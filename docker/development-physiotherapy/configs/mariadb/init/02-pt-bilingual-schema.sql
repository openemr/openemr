-- PT-Specific Bilingual Database Schema
-- Comprehensive physiotherapy database structure with Vietnamese bilingual support
-- Author: Dang Tran <tqvdang@msn.com>

SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci;
USE `openemr`;

-- PT Assessment table with bilingual support
CREATE TABLE IF NOT EXISTS `pt_assessments_bilingual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `encounter_id` int(11) NOT NULL,
  `assessment_date` datetime NOT NULL,
  `therapist_id` int(11) DEFAULT NULL,
  
  -- Chief complaint in both languages
  `chief_complaint_en` text COLLATE utf8mb4_vietnamese_ci,
  `chief_complaint_vi` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Pain assessment
  `pain_level` int(2) DEFAULT NULL COMMENT 'Pain scale 0-10',
  `pain_location_en` text COLLATE utf8mb4_vietnamese_ci,
  `pain_location_vi` text COLLATE utf8mb4_vietnamese_ci,
  `pain_description_en` text COLLATE utf8mb4_vietnamese_ci,
  `pain_description_vi` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Range of Motion measurements (JSON format for flexibility)
  `rom_measurements` json COMMENT 'Range of motion measurements in degrees',
  `strength_measurements` json COMMENT 'Muscle strength measurements',
  `balance_assessment` json COMMENT 'Balance and coordination assessment',
  
  -- Functional goals in both languages
  `functional_goals_en` text COLLATE utf8mb4_vietnamese_ci,
  `functional_goals_vi` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Treatment plan
  `treatment_plan_en` text COLLATE utf8mb4_vietnamese_ci,
  `treatment_plan_vi` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Patient preferences
  `language_preference` varchar(10) DEFAULT 'en' COMMENT 'Patient preferred language',
  `communication_notes` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Assessment status
  `status` enum('draft', 'completed', 'reviewed', 'cancelled') DEFAULT 'draft',
  `assessment_type` varchar(50) DEFAULT 'initial' COMMENT 'initial, follow-up, discharge',
  
  -- Timestamps
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  INDEX `idx_patient` (`patient_id`),
  INDEX `idx_encounter` (`encounter_id`),
  INDEX `idx_therapist` (`therapist_id`),
  INDEX `idx_assessment_date` (`assessment_date`),
  INDEX `idx_status` (`status`),
  INDEX `idx_language` (`language_preference`),
  INDEX `idx_type` (`assessment_type`),
  
  -- Full-text indexes for bilingual search
  FULLTEXT `idx_complaint_search` (`chief_complaint_en`, `chief_complaint_vi`),
  FULLTEXT `idx_goals_search` (`functional_goals_en`, `functional_goals_vi`),
  FULLTEXT `idx_plan_search` (`treatment_plan_en`, `treatment_plan_vi`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Vietnamese insurance information table
CREATE TABLE IF NOT EXISTS `vietnamese_insurance_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  
  -- Vietnamese health insurance details
  `bhyt_card_number` varchar(50) COLLATE utf8mb4_vietnamese_ci COMMENT 'BHYT card number',
  `insurance_provider` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT 'Bảo hiểm Xã hội Việt Nam',
  `coverage_type` varchar(100) COLLATE utf8mb4_vietnamese_ci COMMENT 'Type of coverage',
  `coverage_percentage` decimal(5,2) DEFAULT 80.00 COMMENT 'Coverage percentage',
  
  -- Validity dates
  `valid_from` date,
  `valid_to` date,
  `issue_date` date,
  
  -- Hospital registration
  `registered_hospital` varchar(255) COLLATE utf8mb4_vietnamese_ci,
  `hospital_code` varchar(20) COLLATE utf8mb4_vietnamese_ci,
  
  -- Additional insurance details
  `insurance_notes` text COLLATE utf8mb4_vietnamese_ci,
  `is_active` tinyint(1) DEFAULT 1,
  
  -- Timestamps
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  INDEX `idx_patient` (`patient_id`),
  INDEX `idx_bhyt` (`bhyt_card_number`),
  INDEX `idx_provider` (`insurance_provider`),
  INDEX `idx_validity` (`valid_from`, `valid_to`),
  INDEX `idx_active` (`is_active`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- PT Exercise Prescription table with bilingual support
CREATE TABLE IF NOT EXISTS `pt_exercise_prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  
  -- Exercise details in both languages
  `exercise_name_en` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `exercise_name_vi` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `exercise_category` varchar(100) COLLATE utf8mb4_vietnamese_ci COMMENT 'strengthening, stretching, balance, etc.',
  
  -- Exercise instructions
  `instructions_en` text COLLATE utf8mb4_vietnamese_ci,
  `instructions_vi` text COLLATE utf8mb4_vietnamese_ci,
  `precautions_en` text COLLATE utf8mb4_vietnamese_ci,
  `precautions_vi` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Exercise parameters
  `sets_prescribed` int(3) DEFAULT NULL,
  `reps_prescribed` int(4) DEFAULT NULL,
  `hold_time_seconds` int(4) DEFAULT NULL,
  `frequency_per_day` int(2) DEFAULT NULL,
  `frequency_per_week` int(2) DEFAULT NULL,
  `duration_weeks` int(3) DEFAULT NULL,
  
  -- Progress tracking
  `difficulty_level` enum('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
  `progression_notes` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Exercise media and resources
  `image_url` varchar(500) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `handout_url` varchar(500) DEFAULT NULL,
  
  -- Status and compliance
  `status` enum('active', 'completed', 'discontinued', 'modified') DEFAULT 'active',
  `patient_compliance` enum('excellent', 'good', 'fair', 'poor', 'unknown') DEFAULT 'unknown',
  `compliance_notes` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Timestamps
  `prescribed_date` datetime NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  INDEX `idx_patient` (`patient_id`),
  INDEX `idx_assessment` (`assessment_id`),
  INDEX `idx_therapist` (`therapist_id`),
  INDEX `idx_category` (`exercise_category`),
  INDEX `idx_status` (`status`),
  INDEX `idx_difficulty` (`difficulty_level`),
  INDEX `idx_prescribed_date` (`prescribed_date`),
  
  -- Full-text search for exercise names and instructions
  FULLTEXT `idx_exercise_search` (`exercise_name_en`, `exercise_name_vi`, `instructions_en`, `instructions_vi`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- PT Outcome Measures table
CREATE TABLE IF NOT EXISTS `pt_outcome_measures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  
  -- Measurement details
  `measure_name_en` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `measure_name_vi` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `measure_type` varchar(100) COLLATE utf8mb4_vietnamese_ci COMMENT 'functional, pain, quality_of_life, etc.',
  
  -- Measurement data
  `measurement_date` datetime NOT NULL,
  `raw_score` decimal(10,2) DEFAULT NULL,
  `percentage_score` decimal(5,2) DEFAULT NULL,
  `interpretation_en` text COLLATE utf8mb4_vietnamese_ci,
  `interpretation_vi` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Reference values
  `normal_range_min` decimal(10,2) DEFAULT NULL,
  `normal_range_max` decimal(10,2) DEFAULT NULL,
  `unit_of_measure` varchar(50) COLLATE utf8mb4_vietnamese_ci,
  
  -- Clinical significance
  `clinical_significance` enum('improved', 'stable', 'declined', 'significant_improvement', 'significant_decline') DEFAULT NULL,
  `mcid_threshold` decimal(10,2) DEFAULT NULL COMMENT 'Minimal Clinically Important Difference',
  `change_from_baseline` decimal(10,2) DEFAULT NULL,
  
  -- Assessment context
  `baseline_measurement` tinyint(1) DEFAULT 0,
  `follow_up_week` int(3) DEFAULT NULL,
  `measurement_notes` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Timestamps
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  INDEX `idx_patient` (`patient_id`),
  INDEX `idx_assessment` (`assessment_id`),
  INDEX `idx_therapist` (`therapist_id`),
  INDEX `idx_measure_type` (`measure_type`),
  INDEX `idx_measurement_date` (`measurement_date`),
  INDEX `idx_baseline` (`baseline_measurement`),
  INDEX `idx_significance` (`clinical_significance`),
  
  -- Full-text search for measure names and interpretations
  FULLTEXT `idx_measure_search` (`measure_name_en`, `measure_name_vi`, `interpretation_en`, `interpretation_vi`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- PT Treatment Session Notes (bilingual)
CREATE TABLE IF NOT EXISTS `pt_treatment_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  
  -- Session details
  `session_date` datetime NOT NULL,
  `session_duration_minutes` int(4) DEFAULT NULL,
  `session_type` varchar(100) COLLATE utf8mb4_vietnamese_ci DEFAULT 'individual',
  
  -- Treatment provided
  `treatments_provided` json COMMENT 'Array of treatments provided in session',
  `objective_findings_en` text COLLATE utf8mb4_vietnamese_ci,
  `objective_findings_vi` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Patient response
  `subjective_response_en` text COLLATE utf8mb4_vietnamese_ci,
  `subjective_response_vi` text COLLATE utf8mb4_vietnamese_ci,
  `pain_level_pre` int(2) DEFAULT NULL,
  `pain_level_post` int(2) DEFAULT NULL,
  
  -- Home exercise compliance
  `home_exercise_compliance` enum('excellent', 'good', 'fair', 'poor', 'not_assessed') DEFAULT 'not_assessed',
  `compliance_notes` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Plan for next session
  `plan_en` text COLLATE utf8mb4_vietnamese_ci,
  `plan_vi` text COLLATE utf8mb4_vietnamese_ci,
  
  -- Session status
  `session_status` enum('completed', 'partial', 'cancelled', 'no_show') DEFAULT 'completed',
  `cancellation_reason` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  
  -- Timestamps
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  INDEX `idx_patient` (`patient_id`),
  INDEX `idx_assessment` (`assessment_id`),
  INDEX `idx_therapist` (`therapist_id`),
  INDEX `idx_session_date` (`session_date`),
  INDEX `idx_session_type` (`session_type`),
  INDEX `idx_status` (`session_status`),
  
  -- Full-text search for session notes
  FULLTEXT `idx_session_search` (`objective_findings_en`, `objective_findings_vi`, `subjective_response_en`, `subjective_response_vi`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Create views for easier bilingual data access
CREATE OR REPLACE VIEW `pt_patient_summary_bilingual` AS
SELECT 
  p.pid as patient_id,
  p.fname,
  p.lname,
  p.DOB,
  p.sex,
  p.language as patient_language,
  
  -- Latest assessment info
  pa.id as latest_assessment_id,
  pa.assessment_date as latest_assessment_date,
  CASE 
    WHEN p.language = 'Vietnamese' THEN pa.chief_complaint_vi
    ELSE pa.chief_complaint_en
  END as chief_complaint,
  
  -- Insurance info
  vi.bhyt_card_number,
  vi.coverage_type,
  vi.coverage_percentage,
  
  -- Latest outcome measures
  om.measure_name_en,
  om.measure_name_vi,
  om.raw_score as latest_score,
  om.measurement_date as latest_measurement_date,
  
  -- Active exercises count
  (SELECT COUNT(*) FROM pt_exercise_prescriptions pep 
   WHERE pep.patient_id = p.pid AND pep.status = 'active') as active_exercises_count

FROM patient_data p
LEFT JOIN pt_assessments_bilingual pa ON p.pid = pa.patient_id 
  AND pa.id = (SELECT MAX(id) FROM pt_assessments_bilingual WHERE patient_id = p.pid)
LEFT JOIN vietnamese_insurance_info vi ON p.pid = vi.patient_id AND vi.is_active = 1
LEFT JOIN pt_outcome_measures om ON p.pid = om.patient_id 
  AND om.id = (SELECT MAX(id) FROM pt_outcome_measures WHERE patient_id = p.pid)
WHERE p.pid IN (SELECT DISTINCT patient_id FROM pt_assessments_bilingual);

-- Create stored procedures for common bilingual operations
DELIMITER //

-- Procedure to get patient assessment in preferred language
CREATE PROCEDURE GetPatientAssessmentBilingual(
    IN p_patient_id INT,
    IN p_assessment_id INT,
    IN p_language VARCHAR(10)
)
BEGIN
    IF p_assessment_id = 0 THEN
        SELECT MAX(id) INTO p_assessment_id 
        FROM pt_assessments_bilingual 
        WHERE patient_id = p_patient_id;
    END IF;
    
    SELECT 
        id,
        patient_id,
        encounter_id,
        assessment_date,
        CASE WHEN p_language = 'vi' THEN chief_complaint_vi ELSE chief_complaint_en END as chief_complaint,
        pain_level,
        CASE WHEN p_language = 'vi' THEN pain_location_vi ELSE pain_location_en END as pain_location,
        CASE WHEN p_language = 'vi' THEN functional_goals_vi ELSE functional_goals_en END as functional_goals,
        CASE WHEN p_language = 'vi' THEN treatment_plan_vi ELSE treatment_plan_en END as treatment_plan,
        rom_measurements,
        strength_measurements,
        status,
        language_preference
    FROM pt_assessments_bilingual 
    WHERE id = p_assessment_id AND patient_id = p_patient_id;
END //

-- Procedure to get active exercise prescriptions in preferred language
CREATE PROCEDURE GetActiveExercisesBilingual(
    IN p_patient_id INT,
    IN p_language VARCHAR(10)
)
BEGIN
    SELECT 
        id,
        CASE WHEN p_language = 'vi' THEN exercise_name_vi ELSE exercise_name_en END as exercise_name,
        CASE WHEN p_language = 'vi' THEN instructions_vi ELSE instructions_en END as instructions,
        CASE WHEN p_language = 'vi' THEN precautions_vi ELSE precautions_en END as precautions,
        exercise_category,
        sets_prescribed,
        reps_prescribed,
        frequency_per_day,
        frequency_per_week,
        difficulty_level,
        prescribed_date,
        patient_compliance
    FROM pt_exercise_prescriptions 
    WHERE patient_id = p_patient_id AND status = 'active'
    ORDER BY prescribed_date DESC, exercise_category, difficulty_level;
END //

DELIMITER ;

-- Log successful schema creation
INSERT INTO `vietnamese_medical_terms` 
(`english_term`, `vietnamese_term`, `category`, `description_en`, `description_vi`) VALUES
('PT Bilingual Schema', 'Lược đồ VLTT song ngữ', 'system', 
 CONCAT('PT bilingual database schema created successfully at ', NOW()),
 CONCAT('Lược đồ cơ sở dữ liệu VLTT song ngữ được tạo thành công lúc ', NOW()));