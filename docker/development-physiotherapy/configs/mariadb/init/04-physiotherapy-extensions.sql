-- OpenEMR Physiotherapy Module Database Extensions
-- Custom tables and data for physiotherapy specialization
-- Author: Dang Tran <tqvdang@msn.com>
--
-- NOTE: Tables pt_exercise_prescriptions, pt_outcome_measures, and pt_treatment_sessions
-- are already created by 02-pt-bilingual-schema.sql. This file creates additional tables
-- and inserts sample data using the correct column names from those tables.

USE `openemr`;

-- Create physiotherapy assessment templates table (not in 02-pt-bilingual-schema.sql)
CREATE TABLE IF NOT EXISTS `pt_assessment_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `template_name_vi` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `body_region` varchar(100) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `assessment_fields` json NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_body_region` (`body_region`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Create treatment plans table (not in 02-pt-bilingual-schema.sql)
CREATE TABLE IF NOT EXISTS `pt_treatment_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  `plan_name` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `plan_name_vi` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `diagnosis_primary` text COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `diagnosis_primary_vi` text COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `diagnosis_secondary` text COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `goals_short_term` json DEFAULT NULL,
  `goals_long_term` json DEFAULT NULL,
  `treatment_frequency` varchar(100) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `estimated_duration_weeks` int(11) DEFAULT NULL,
  `treatment_approaches` json DEFAULT NULL,
  `contraindications` text COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `contraindications_vi` text COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `plan_status` enum('active','completed','on_hold','cancelled') DEFAULT 'active',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_status` (`plan_status`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Create PT configuration table for module settings
CREATE TABLE IF NOT EXISTS `pt_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `config_value` text COLLATE utf8mb4_vietnamese_ci,
  `description` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Insert configuration values
INSERT IGNORE INTO `pt_configuration` (`config_key`, `config_value`, `description`) VALUES
('installation_date', NOW(), 'Date when Vietnamese PT module was installed'),
('last_migration', '003', 'Last applied migration version'),
('default_language', 'vi', 'Default language for PT module'),
('pain_scale_max', '10', 'Maximum value for pain scale');

-- NOTE: Tables pt_exercise_prescriptions and pt_outcome_measures are already created by
-- 02-pt-bilingual-schema.sql with different column names:
-- - pt_exercise_prescriptions uses: exercise_name_en, exercise_name_vi, assessment_id, therapist_id
-- - pt_outcome_measures uses: measure_name_en, measure_name_vi, assessment_id, therapist_id, raw_score

-- Insert sample assessment templates
INSERT INTO `pt_assessment_templates` (`template_name`, `template_name_vi`, `category`, `body_region`, `assessment_fields`) VALUES
('Lower Back Pain Assessment', 'Đánh giá đau lưng dưới', 'Musculoskeletal', 'Lumbar Spine', 
 JSON_OBJECT(
   'pain_scale', 'numeric',
   'pain_location', 'text',
   'pain_duration', 'text',
   'aggravating_factors', 'text',
   'relieving_factors', 'text',
   'range_of_motion', 'object',
   'strength_testing', 'object',
   'special_tests', 'array'
 )),
('Shoulder Function Assessment', 'Đánh giá chức năng vai', 'Musculoskeletal', 'Shoulder', 
 JSON_OBJECT(
   'pain_scale', 'numeric',
   'functional_limitations', 'text',
   'range_of_motion', 'object',
   'strength_testing', 'object',
   'daily_activities_impact', 'text'
 )),
('Balance Assessment', 'Đánh giá thăng bằng', 'Neurological', 'Full Body', 
 JSON_OBJECT(
   'static_balance', 'object',
   'dynamic_balance', 'object',
   'fall_risk_factors', 'array',
   'balance_confidence', 'numeric'
 ));

-- Insert sample exercise prescriptions
-- Using correct column names from 02-pt-bilingual-schema.sql:
-- exercise_name_en (not exercise_name), assessment_id, therapist_id, reps_prescribed (int)
-- NOTE: These inserts require a patient and assessment to exist first
-- Commenting out for now to avoid FK errors on fresh install
/*
INSERT INTO `pt_exercise_prescriptions`
(`patient_id`, `assessment_id`, `therapist_id`, `exercise_name_en`, `exercise_name_vi`,
 `exercise_category`, `sets_prescribed`, `reps_prescribed`, `frequency_per_week`,
 `difficulty_level`, `instructions_en`, `instructions_vi`, `prescribed_date`) VALUES
(1, 1, 1, 'Cat-Cow Stretch', 'Động tác mèo-bò', 'stretching',
 2, 15, 7, 'beginner',
 'Start on hands and knees, alternate between arching and rounding the spine',
 'Bắt đầu ở tư thế quỳ, luân phiên giữa vòng cung và tròn lưng',
 NOW()),

(1, 1, 1, 'Wall Push-ups', 'Hít đất tường', 'strengthening',
 3, 12, 5, 'beginner',
 'Stand arms length from wall, place palms flat against wall, perform push-up motion',
 'Đứng cách tường một cánh tay, đặt lòng bàn tay vào tường, thực hiện động tác hít đất',
 NOW());
*/

-- Insert sample outcome measures
-- Using correct column names from 02-pt-bilingual-schema.sql:
-- measure_name_en (not measure_name), assessment_id, therapist_id, raw_score (not score_value)
-- NOTE: These inserts require a patient and assessment to exist first
/*
INSERT INTO `pt_outcome_measures`
(`patient_id`, `assessment_id`, `therapist_id`, `measure_name_en`, `measure_name_vi`,
 `measure_type`, `raw_score`, `percentage_score`, `interpretation_en`, `interpretation_vi`,
 `measurement_date`) VALUES
(1, 1, 1, 'Visual Analog Scale', 'Thang đo đau thị giác', 'pain',
 6.5, 65.0, 'Moderate pain level', 'Mức độ đau trung bình', NOW()),

(1, 1, 1, 'Oswestry Disability Index', 'Chỉ số khuyết tật Oswestry', 'functional',
 32.0, 32.0, 'Moderate disability', 'Khuyết tật mức độ trung bình', NOW());
*/

-- Create indexes for better performance (only for tables created in this file)
-- pt_assessment_templates and pt_treatment_plans are created here
-- pt_exercise_prescriptions and pt_outcome_measures indexes are created in 02-pt-bilingual-schema.sql
CREATE INDEX IF NOT EXISTS idx_pt_assessment_name ON `pt_assessment_templates` (`template_name`);
CREATE INDEX IF NOT EXISTS idx_pt_treatment_patient_status ON `pt_treatment_plans` (`patient_id`, `plan_status`);

-- Log successful PT extension initialization
INSERT INTO `vietnamese_test` (`vietnamese_text`) VALUES
(CONCAT('Physiotherapy extensions initialized at ', NOW(), ' - Phần mở rộng vật lý trị liệu được khởi tạo'));