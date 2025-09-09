-- OpenEMR Physiotherapy Module Database Extensions
-- Custom tables and data for physiotherapy specialization
-- Author: Dang Tran <tqvdang@msn.com>

USE `openemr`;

-- Create physiotherapy assessment templates table
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

-- Create exercise prescription table
CREATE TABLE IF NOT EXISTS `pt_exercise_prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  `exercise_name` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `exercise_name_vi` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_vietnamese_ci,
  `description_vi` text COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `sets_prescribed` int(11) DEFAULT NULL,
  `reps_prescribed` varchar(50) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `frequency_per_week` int(11) DEFAULT NULL,
  `intensity_level` enum('low','moderate','high') DEFAULT 'moderate',
  `instructions` text COLLATE utf8mb4_vietnamese_ci,
  `instructions_vi` text COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `equipment_needed` varchar(500) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `precautions` text COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `precautions_vi` text COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `prescribed_by` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_encounter` (`encounter_id`),
  KEY `idx_prescribed_by` (`prescribed_by`),
  KEY `idx_active` (`is_active`),
  KEY `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Create outcome measures table
CREATE TABLE IF NOT EXISTS `pt_outcome_measures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  `measure_type` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `measure_name` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `measure_name_vi` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `score_value` decimal(10,2) DEFAULT NULL,
  `max_score` decimal(10,2) DEFAULT NULL,
  `interpretation` text COLLATE utf8mb4_vietnamese_ci,
  `interpretation_vi` text COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `measurement_date` date NOT NULL,
  `reassessment_due` date DEFAULT NULL,
  `measured_by` int(11) NOT NULL,
  `notes` text COLLATE utf8mb4_vietnamese_ci,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_encounter` (`encounter_id`),
  KEY `idx_measure_type` (`measure_type`),
  KEY `idx_measurement_date` (`measurement_date`),
  KEY `idx_measured_by` (`measured_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Create treatment plans table
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
INSERT INTO `pt_exercise_prescriptions` 
(`patient_id`, `exercise_name`, `exercise_name_vi`, `description`, `description_vi`, 
 `sets_prescribed`, `reps_prescribed`, `duration_minutes`, `frequency_per_week`, 
 `intensity_level`, `instructions`, `instructions_vi`, `equipment_needed`, 
 `start_date`, `prescribed_by`) VALUES
(1, 'Cat-Cow Stretch', 'Động tác mèo-bò', 
 'Spinal mobility exercise for lower back flexibility', 
 'Bài tập linh hoạt cột sống để tăng độ dẻo dai lưng dưới',
 2, '10-15 reps', 10, 7, 'low',
 'Start on hands and knees, alternate between arching and rounding the spine',
 'Bắt đầu ở tư thế quỳ, luân phiên giữa vòng cung và tròn lưng',
 'Exercise mat', CURDATE(), 1),

(1, 'Wall Push-ups', 'Hít đất tường', 
 'Upper body strengthening exercise', 
 'Bài tập tăng cường sức mạnh phần thân trên',
 3, '8-12 reps', 15, 5, 'moderate',
 'Stand arms length from wall, place palms flat against wall, perform push-up motion',
 'Đứng cách tường một cánh tay, đặt lòng bàn tay vào tường, thực hiện động tác hít đất',
 'Wall space', CURDATE(), 1);

-- Insert sample outcome measures
INSERT INTO `pt_outcome_measures` 
(`patient_id`, `measure_type`, `measure_name`, `measure_name_vi`, 
 `score_value`, `max_score`, `interpretation`, `interpretation_vi`,
 `measurement_date`, `measured_by`) VALUES
(1, 'Pain Scale', 'Visual Analog Scale', 'Thang đo đau thị giác',
 6.5, 10.0, 'Moderate pain level', 'Mức độ đau trung bình',
 CURDATE(), 1),

(1, 'Functional', 'Oswestry Disability Index', 'Chỉ số khuyết tật Oswestry',
 32.0, 100.0, 'Moderate disability', 'Khuyết tật mức độ trung bình',
 CURDATE(), 1);

-- Create indexes for better performance
CREATE INDEX idx_pt_assessment_name ON `pt_assessment_templates` (`template_name`);
CREATE INDEX idx_pt_exercise_patient_date ON `pt_exercise_prescriptions` (`patient_id`, `start_date`);
CREATE INDEX idx_pt_outcome_patient_type ON `pt_outcome_measures` (`patient_id`, `measure_type`);
CREATE INDEX idx_pt_treatment_patient_status ON `pt_treatment_plans` (`patient_id`, `plan_status`);

-- Log successful PT extension initialization
INSERT INTO `vietnamese_test` (`vietnamese_text`) VALUES 
(CONCAT('Physiotherapy extensions initialized at ', NOW(), ' - Phần mở rộng vật lý trị liệu được khởi tạo'));