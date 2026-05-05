-- MedEx Schedule Templates
-- Stores reusable schedule templates for providers

CREATE TABLE IF NOT EXISTS `medex_schedule_templates` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `day_of_week` tinyint(1) NOT NULL COMMENT '0=Sunday, 6=Saturday',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `preferred_category_id` int(11) DEFAULT NULL COMMENT 'Links to openemr_postcalendar_categories.pc_catid',
  `slot_duration` int(11) NOT NULL DEFAULT 15 COMMENT 'Duration in minutes',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_applied` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`template_id`),
  KEY `provider_id` (`provider_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample template for demonstration
INSERT INTO `medex_schedule_templates`
(`provider_id`, `template_name`, `day_of_week`, `start_time`, `end_time`, `preferred_category_id`, `slot_duration`, `created_by`)
VALUES
(1, 'Monday Morning - New Patients', 1, '09:00:00', '12:00:00', 10, 30, 1),
(1, 'Monday Afternoon - Follow-ups', 1, '13:00:00', '17:00:00', 9, 15, 1),
(1, 'Friday PM - Post-Op', 5, '14:00:00', '17:00:00', 5, 10, 1);
