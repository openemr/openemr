-- Vietnamese PT Module - REST Routes and ACL Configuration
-- Run this SQL to configure access control and permissions
-- Author: Dang Tran <tqvdang@msn.com>

-- ============================================
-- PART 1: ACL Configuration
-- ============================================

-- Create Vietnamese PT ACL section
INSERT INTO `module_acl_sections` (`section_id`, `section_name`, `parent_section`, `section_identifier`, `module_id`)
VALUES
('vietnamese_pt', 'Vietnamese PT Module', '', 'vietnamese_pt', 0)
ON DUPLICATE KEY UPDATE section_name = 'Vietnamese PT Module';

-- Grant access to admin group (full access)
INSERT INTO `module_acl_group_settings` (`module_id`, `group_id`, `section_id`, `allowed`, `activities`)
VALUES
(0, 1, 'vietnamese_pt', 1, 'write')
ON DUPLICATE KEY UPDATE allowed = 1, activities = 'write';

-- Grant access to clinicians group (view only)
INSERT INTO `module_acl_group_settings` (`module_id`, `group_id`, `section_id`, `allowed`, `activities`)
VALUES
(0, 2, 'vietnamese_pt', 1, 'view')
ON DUPLICATE KEY UPDATE allowed = 1, activities = 'view';

-- Grant access to physicians group (full access)
INSERT INTO `module_acl_group_settings` (`module_id`, `group_id`, `section_id`, `allowed`, `activities`)
VALUES
(0, 3, 'vietnamese_pt', 1, 'write')
ON DUPLICATE KEY UPDATE allowed = 1, activities = 'write';

-- Grant admin user full access
INSERT INTO `module_acl_user_settings` (`module_id`, `user_id`, `section_id`, `allowed`, `activities`)
SELECT 0, `id`, 'vietnamese_pt', 1, 'write'
FROM `users`
WHERE `username` = 'admin'
ON DUPLICATE KEY UPDATE allowed = 1, activities = 'write';

-- ============================================
-- PART 2: Form Registration
-- ============================================

-- Register vietnamese_pt_assessment form
INSERT INTO `registry` (
    `name`,
    `directory`,
    `sql_run`,
    `unpackaged`,
    `state`,
    `category`,
    `nickname`,
    `patient_encounter`,
    `therapy_group_encounter`,
    `aco_spec`
)
VALUES (
    'Vietnamese PT Assessment',
    'vietnamese_pt_assessment',
    1,
    1,
    1,
    'Clinical',
    'Vietnamese PT',
    1,
    0,
    'encounters|notes'
)
ON DUPLICATE KEY UPDATE
    `name` = 'Vietnamese PT Assessment',
    `state` = 1;

-- Register vietnamese_pt_exercise form
INSERT INTO `registry` (
    `name`,
    `directory`,
    `sql_run`,
    `unpackaged`,
    `state`,
    `category`,
    `nickname`,
    `patient_encounter`,
    `therapy_group_encounter`,
    `aco_spec`
)
VALUES (
    'Vietnamese PT Exercise Prescription',
    'vietnamese_pt_exercise',
    1,
    1,
    1,
    'Clinical',
    'Vietnamese PT Exercise',
    1,
    0,
    'encounters|notes'
)
ON DUPLICATE KEY UPDATE
    `name` = 'Vietnamese PT Exercise Prescription',
    `state` = 1;

-- Register vietnamese_pt_treatment_plan form
INSERT INTO `registry` (
    `name`,
    `directory`,
    `sql_run`,
    `unpackaged`,
    `state`,
    `category`,
    `nickname`,
    `patient_encounter`,
    `therapy_group_encounter`,
    `aco_spec`
)
VALUES (
    'Vietnamese PT Treatment Plan',
    'vietnamese_pt_treatment_plan',
    1,
    1,
    1,
    'Clinical',
    'Vietnamese PT Plan',
    1,
    0,
    'encounters|notes'
)
ON DUPLICATE KEY UPDATE
    `name` = 'Vietnamese PT Treatment Plan',
    `state` = 1;

-- Register vietnamese_pt_outcome form
INSERT INTO `registry` (
    `name`,
    `directory`,
    `sql_run`,
    `unpackaged`,
    `state`,
    `category`,
    `nickname`,
    `patient_encounter`,
    `therapy_group_encounter`,
    `aco_spec`
)
VALUES (
    'Vietnamese PT Outcome Measures',
    'vietnamese_pt_outcome',
    1,
    1,
    1,
    'Clinical',
    'Vietnamese PT Outcome',
    1,
    0,
    'encounters|notes'
)
ON DUPLICATE KEY UPDATE
    `name` = 'Vietnamese PT Outcome Measures',
    `state` = 1;

-- ============================================
-- PART 3: List Options (for dropdowns)
-- ============================================

-- Add PT assessment status options
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`)
VALUES
('pt_assessment_status', 'draft', 'Draft', 10, 0, 0, 'Assessment in draft status'),
('pt_assessment_status', 'completed', 'Completed', 20, 1, 0, 'Assessment completed'),
('pt_assessment_status', 'reviewed', 'Reviewed', 30, 0, 0, 'Assessment reviewed by supervisor'),
('pt_assessment_status', 'cancelled', 'Cancelled', 40, 0, 0, 'Assessment cancelled')
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- Add exercise intensity levels
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`)
VALUES
('exercise_intensity', 'low', 'Low', 10, 0),
('exercise_intensity', 'moderate', 'Moderate', 20, 1),
('exercise_intensity', 'high', 'High', 30, 0)
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- Add treatment plan statuses
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`)
VALUES
('treatment_plan_status', 'active', 'Active', 10, 1),
('treatment_plan_status', 'completed', 'Completed', 20, 0),
('treatment_plan_status', 'on_hold', 'On Hold', 30, 0),
('treatment_plan_status', 'cancelled', 'Cancelled', 40, 0)
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- ============================================
-- PART 4: Globals Configuration
-- ============================================

-- Add Vietnamese PT globals
INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`)
VALUES
('vietnamese_pt_enabled', 0, '1'),
('vietnamese_default_language', 0, 'vi'),
('vietnamese_pt_require_bilingual', 0, '1')
ON DUPLICATE KEY UPDATE gl_value = VALUES(gl_value);

-- ============================================
-- Success Message
-- ============================================

SELECT 'Vietnamese PT ACL and Routes configured successfully!' as Status;
SELECT '✅ ACL permissions granted' as Step1;
SELECT '✅ Form registered' as Step2;
SELECT '✅ List options created' as Step3;
SELECT '✅ Globals configured' as Step4;
SELECT '' as Info;
SELECT 'Next steps:' as NextSteps;
SELECT '1. Add REST routes to _rest_routes.inc.php' as Step;
SELECT '2. Create form files in interface/forms/vietnamese_pt_assessment/' as Step;
SELECT '3. Test API endpoints with: GET /api/vietnamese-pt/assessments' as Step;