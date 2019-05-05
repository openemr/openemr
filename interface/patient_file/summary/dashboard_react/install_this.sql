DELETE FROM `list_options` WHERE list_id='dashboard' OR option_id='dashboard';

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`, `timestamp`) VALUES
('dashboard', 'allergies', 'Allergies', 4, 0, 0, '', '{\"id\":\"right\",\"element_component\":\"MedicalProblems\",\"element\":\"Allergies\",\"element_title\":\"Allergies\"}', '', 0, 0, 1, '', 1, '2019-04-21 06:33:30'),
('dashboard', 'medical_problems', 'Medical Problems', 3, 0, 0, '', '{\"id\":\"right\",\"element_component\":\"MedicalProblems\",\"element\":\"MedicalProblems\",\"element_title\":\"Medical Problems\"}', '', 0, 0, 1, '', 1, '2019-04-21 06:33:30'),
('dashboard', 'medications', 'Medications', 5, 0, 0, '', '{\"id\":\"right\",\"element_component\":\"MedicalProblems\",\"element\":\"Medications\",\"element_title\":\"Medications\"}', '', 0, 0, 1, '', 1, '2019-04-21 06:33:30'),
('dashboard', 'menu_dashboard', 'Menu', 1, 0, 0, '', '{\"id\":\"header\",\"element_component\":\"MenuDashboard\",\"element\":\"MenuDashboard\"}', '', 0, 0, 1, '', 1, '2019-04-21 06:33:30'),
('dashboard', 'patient_details', 'Patient Details', 2, 0, 0, '', '{\"id\":\"left\",\"element_component\":\"PatientData\",\"element\":\"PatientData\"}', '', 0, 0, 1, '', 1, '2019-04-21 06:33:30'),
('lists', 'dashboard', 'dashboard', 306, 1, 0, '', NULL, '', 0, 0, 1, '', 1, '2019-04-21 06:30:49');