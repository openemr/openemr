-- Anatomical Selections Schema for Vietnamese PT Module
-- Stores drill-down anatomical selections from interactive body diagrams
--
-- @package   OpenEMR
-- @link      http://www.open-emr.org
-- @author    Dang Tran <tqvdang@msn.com>
-- @copyright Copyright (c) 2025 Dang Tran
-- @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3

USE `openemr`;

-- Anatomical regions hierarchy table
-- Defines the drill-down structure: Body -> Region -> Sub-region -> Structure
CREATE TABLE IF NOT EXISTS `anatomy_regions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `parent_id` INT(11) DEFAULT NULL,
  `code` VARCHAR(50) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `name_en` VARCHAR(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `name_vi` VARCHAR(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `level` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=body, 2=region, 3=sub-region, 4=structure',
  `structure_type` ENUM('region','muscle','bone','joint','nerve','vessel','ligament','tendon','organ') DEFAULT 'region',
  `svg_file` VARCHAR(255) DEFAULT NULL COMMENT 'SVG file for this level',
  `svg_element_id` VARCHAR(100) DEFAULT NULL COMMENT 'Element ID within parent SVG',
  `fma_id` VARCHAR(50) DEFAULT NULL COMMENT 'Foundational Model of Anatomy ID',
  `display_order` INT(11) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_level` (`level`),
  KEY `idx_type` (`structure_type`),
  KEY `idx_active` (`is_active`),
  FULLTEXT KEY `idx_name_search` (`name_en`, `name_vi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Patient anatomical selections from assessments
CREATE TABLE IF NOT EXISTS `pt_anatomical_selections` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `assessment_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `encounter_id` INT(11) DEFAULT NULL,

  -- Selected anatomy region (references anatomy_regions)
  `region_id` INT(11) NOT NULL,
  `region_code` VARCHAR(50) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `region_path` VARCHAR(500) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL COMMENT 'Full path: body>shoulder>rotator_cuff>supraspinatus',

  -- Laterality
  `laterality` ENUM('left','right','bilateral','midline','not_applicable') DEFAULT 'not_applicable',

  -- Clinical findings
  `finding_type` VARCHAR(100) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL COMMENT 'tenderness, weakness, ROM_limitation, swelling, etc.',
  `severity_level` TINYINT(2) DEFAULT NULL COMMENT '0-10 scale',
  `pain_level` TINYINT(2) DEFAULT NULL COMMENT '0-10 scale',

  -- Notes in both languages
  `notes_en` TEXT COLLATE utf8mb4_vietnamese_ci,
  `notes_vi` TEXT COLLATE utf8mb4_vietnamese_ci,

  -- Visual state for restoring exact view
  `view_state` JSON DEFAULT NULL COMMENT 'Current drill-down path and view settings',

  -- Timestamps
  `selected_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_assessment` (`assessment_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_encounter` (`encounter_id`),
  KEY `idx_region` (`region_id`),
  KEY `idx_region_code` (`region_code`),
  KEY `idx_laterality` (`laterality`),
  KEY `idx_finding_type` (`finding_type`),
  KEY `idx_severity` (`severity_level`),
  FULLTEXT KEY `idx_notes_search` (`notes_en`, `notes_vi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Insert base anatomical regions hierarchy
-- Level 1: Body views
INSERT INTO `anatomy_regions` (`code`, `name_en`, `name_vi`, `level`, `structure_type`, `svg_file`, `display_order`) VALUES
('body_front', 'Body (Front View)', 'Cơ thể (Mặt trước)', 1, 'region', 'body-full-front.svg', 1),
('body_back', 'Body (Back View)', 'Cơ thể (Mặt sau)', 1, 'region', 'body-full-back.svg', 2);

-- Level 2: Major body regions (Front)
INSERT INTO `anatomy_regions` (`parent_id`, `code`, `name_en`, `name_vi`, `level`, `structure_type`, `svg_file`, `svg_element_id`, `display_order`) VALUES
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'head_front', 'Head', 'Đầu', 2, 'region', 'regions/head.svg', 'head', 1),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'neck_front', 'Neck', 'Cổ', 2, 'region', 'regions/neck.svg', 'neck', 2),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'shoulder_right', 'Right Shoulder', 'Vai phải', 2, 'region', 'regions/shoulder.svg', 'shoulder_r', 3),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'shoulder_left', 'Left Shoulder', 'Vai trái', 2, 'region', 'regions/shoulder.svg', 'shoulder_l', 4),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'chest', 'Chest', 'Ngực', 2, 'region', 'regions/chest.svg', 'chest', 5),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'upper_arm_right', 'Right Upper Arm', 'Cánh tay phải', 2, 'region', 'regions/upper-arm.svg', 'upper_arm_r', 6),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'upper_arm_left', 'Left Upper Arm', 'Cánh tay trái', 2, 'region', 'regions/upper-arm.svg', 'upper_arm_l', 7),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'elbow_right', 'Right Elbow', 'Khuỷu tay phải', 2, 'region', 'regions/elbow.svg', 'elbow_r', 8),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'elbow_left', 'Left Elbow', 'Khuỷu tay trái', 2, 'region', 'regions/elbow.svg', 'elbow_l', 9),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'forearm_right', 'Right Forearm', 'Cẳng tay phải', 2, 'region', 'regions/forearm.svg', 'forearm_r', 10),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'forearm_left', 'Left Forearm', 'Cẳng tay trái', 2, 'region', 'regions/forearm.svg', 'forearm_l', 11),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'wrist_right', 'Right Wrist', 'Cổ tay phải', 2, 'region', 'regions/wrist.svg', 'wrist_r', 12),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'wrist_left', 'Left Wrist', 'Cổ tay trái', 2, 'region', 'regions/wrist.svg', 'wrist_l', 13),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'hand_right', 'Right Hand', 'Bàn tay phải', 2, 'region', 'regions/hand.svg', 'hand_r', 14),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'hand_left', 'Left Hand', 'Bàn tay trái', 2, 'region', 'regions/hand.svg', 'hand_l', 15),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'abdomen', 'Abdomen', 'Bụng', 2, 'region', 'regions/abdomen.svg', 'abdomen', 16),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'hip_right', 'Right Hip', 'Hông phải', 2, 'region', 'regions/hip.svg', 'hip_r', 17),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'hip_left', 'Left Hip', 'Hông trái', 2, 'region', 'regions/hip.svg', 'hip_l', 18),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'thigh_right', 'Right Thigh', 'Đùi phải', 2, 'region', 'regions/thigh.svg', 'thigh_r', 19),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'thigh_left', 'Left Thigh', 'Đùi trái', 2, 'region', 'regions/thigh.svg', 'thigh_l', 20),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'knee_right', 'Right Knee', 'Đầu gối phải', 2, 'region', 'regions/knee.svg', 'knee_r', 21),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'knee_left', 'Left Knee', 'Đầu gối trái', 2, 'region', 'regions/knee.svg', 'knee_l', 22),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'lower_leg_right', 'Right Lower Leg', 'Cẳng chân phải', 2, 'region', 'regions/lower-leg.svg', 'lower_leg_r', 23),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'lower_leg_left', 'Left Lower Leg', 'Cẳng chân trái', 2, 'region', 'regions/lower-leg.svg', 'lower_leg_l', 24),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'ankle_right', 'Right Ankle', 'Mắt cá phải', 2, 'region', 'regions/ankle.svg', 'ankle_r', 25),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'ankle_left', 'Left Ankle', 'Mắt cá trái', 2, 'region', 'regions/ankle.svg', 'ankle_l', 26),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'foot_right', 'Right Foot', 'Bàn chân phải', 2, 'region', 'regions/foot.svg', 'foot_r', 27),
((SELECT id FROM anatomy_regions WHERE code = 'body_front' LIMIT 1), 'foot_left', 'Left Foot', 'Bàn chân trái', 2, 'region', 'regions/foot.svg', 'foot_l', 28);

-- Level 2: Major body regions (Back)
INSERT INTO `anatomy_regions` (`parent_id`, `code`, `name_en`, `name_vi`, `level`, `structure_type`, `svg_file`, `svg_element_id`, `display_order`) VALUES
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'cervical_spine', 'Cervical Spine', 'Cột sống cổ', 2, 'region', 'regions/spine-cervical.svg', 'c_spine', 1),
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'thoracic_spine', 'Thoracic Spine', 'Cột sống ngực', 2, 'region', 'regions/spine-thoracic.svg', 't_spine', 2),
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'lumbar_spine', 'Lumbar Spine', 'Cột sống thắt lưng', 2, 'region', 'regions/spine-lumbar.svg', 'l_spine', 3),
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'sacrum', 'Sacrum', 'Xương cùng', 2, 'region', 'regions/sacrum.svg', 'sacrum', 4),
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'upper_back', 'Upper Back', 'Lưng trên', 2, 'region', 'regions/upper-back.svg', 'upper_back', 5),
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'lower_back', 'Lower Back', 'Lưng dưới', 2, 'region', 'regions/lower-back.svg', 'lower_back', 6),
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'gluteal_right', 'Right Gluteal', 'Mông phải', 2, 'region', 'regions/gluteal.svg', 'gluteal_r', 7),
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'gluteal_left', 'Left Gluteal', 'Mông trái', 2, 'region', 'regions/gluteal.svg', 'gluteal_l', 8),
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'hamstring_right', 'Right Hamstring', 'Gân kheo phải', 2, 'region', 'regions/hamstring.svg', 'hamstring_r', 9),
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'hamstring_left', 'Left Hamstring', 'Gân kheo trái', 2, 'region', 'regions/hamstring.svg', 'hamstring_l', 10),
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'calf_right', 'Right Calf', 'Bắp chân phải', 2, 'region', 'regions/calf.svg', 'calf_r', 11),
((SELECT id FROM anatomy_regions WHERE code = 'body_back' LIMIT 1), 'calf_left', 'Left Calf', 'Bắp chân trái', 2, 'region', 'regions/calf.svg', 'calf_l', 12);

-- Level 3: Shoulder structures (example drill-down)
INSERT INTO `anatomy_regions` (`parent_id`, `code`, `name_en`, `name_vi`, `level`, `structure_type`, `svg_file`, `svg_element_id`, `fma_id`, `display_order`) VALUES
-- Shoulder Muscles
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'deltoid_right', 'Deltoid', 'Cơ delta', 3, 'muscle', 'structures/shoulder-muscles.svg', 'deltoid', 'FMA32521', 1),
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'supraspinatus_right', 'Supraspinatus', 'Cơ trên gai', 3, 'muscle', 'structures/shoulder-muscles.svg', 'supraspinatus', 'FMA9629', 2),
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'infraspinatus_right', 'Infraspinatus', 'Cơ dưới gai', 3, 'muscle', 'structures/shoulder-muscles.svg', 'infraspinatus', 'FMA32546', 3),
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'subscapularis_right', 'Subscapularis', 'Cơ dưới vai', 3, 'muscle', 'structures/shoulder-muscles.svg', 'subscapularis', 'FMA13357', 4),
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'teres_minor_right', 'Teres Minor', 'Cơ tròn nhỏ', 3, 'muscle', 'structures/shoulder-muscles.svg', 'teres_minor', 'FMA32550', 5),
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'teres_major_right', 'Teres Major', 'Cơ tròn lớn', 3, 'muscle', 'structures/shoulder-muscles.svg', 'teres_major', 'FMA32554', 6),
-- Shoulder Bones
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'humerus_head_right', 'Humeral Head', 'Chỏm xương cánh tay', 3, 'bone', 'structures/shoulder-bones.svg', 'humerus_head', 'FMA23363', 7),
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'scapula_right', 'Scapula', 'Xương bả vai', 3, 'bone', 'structures/shoulder-bones.svg', 'scapula', 'FMA13394', 8),
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'clavicle_right', 'Clavicle', 'Xương đòn', 3, 'bone', 'structures/shoulder-bones.svg', 'clavicle', 'FMA13321', 9),
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'acromion_right', 'Acromion', 'Mỏm cùng vai', 3, 'bone', 'structures/shoulder-bones.svg', 'acromion', 'FMA23260', 10),
-- Shoulder Joints
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'glenohumeral_right', 'Glenohumeral Joint', 'Khớp ổ chảo-cánh tay', 3, 'joint', 'structures/shoulder-joints.svg', 'glenohumeral', 'FMA25912', 11),
((SELECT id FROM anatomy_regions WHERE code = 'shoulder_right' LIMIT 1), 'acromioclavicular_right', 'AC Joint', 'Khớp cùng vai-đòn', 3, 'joint', 'structures/shoulder-joints.svg', 'ac_joint', 'FMA25898', 12);

-- Level 3: Knee structures (example drill-down)
INSERT INTO `anatomy_regions` (`parent_id`, `code`, `name_en`, `name_vi`, `level`, `structure_type`, `svg_file`, `svg_element_id`, `fma_id`, `display_order`) VALUES
-- Knee Muscles
((SELECT id FROM anatomy_regions WHERE code = 'knee_right' LIMIT 1), 'quadriceps_right', 'Quadriceps', 'Cơ tứ đầu đùi', 3, 'muscle', 'structures/knee-muscles.svg', 'quadriceps', 'FMA22428', 1),
((SELECT id FROM anatomy_regions WHERE code = 'knee_right' LIMIT 1), 'patellar_tendon_right', 'Patellar Tendon', 'Gân bánh chè', 3, 'tendon', 'structures/knee-muscles.svg', 'patellar_tendon', 'FMA44581', 2),
-- Knee Bones
((SELECT id FROM anatomy_regions WHERE code = 'knee_right' LIMIT 1), 'patella_right', 'Patella', 'Xương bánh chè', 3, 'bone', 'structures/knee-bones.svg', 'patella', 'FMA24485', 3),
((SELECT id FROM anatomy_regions WHERE code = 'knee_right' LIMIT 1), 'femur_distal_right', 'Distal Femur', 'Đầu xa xương đùi', 3, 'bone', 'structures/knee-bones.svg', 'femur_distal', 'FMA32839', 4),
((SELECT id FROM anatomy_regions WHERE code = 'knee_right' LIMIT 1), 'tibia_proximal_right', 'Proximal Tibia', 'Đầu gần xương chày', 3, 'bone', 'structures/knee-bones.svg', 'tibia_proximal', 'FMA33140', 5),
-- Knee Ligaments
((SELECT id FROM anatomy_regions WHERE code = 'knee_right' LIMIT 1), 'acl_right', 'ACL', 'Dây chằng chéo trước', 3, 'ligament', 'structures/knee-ligaments.svg', 'acl', 'FMA44614', 6),
((SELECT id FROM anatomy_regions WHERE code = 'knee_right' LIMIT 1), 'pcl_right', 'PCL', 'Dây chằng chéo sau', 3, 'ligament', 'structures/knee-ligaments.svg', 'pcl', 'FMA44615', 7),
((SELECT id FROM anatomy_regions WHERE code = 'knee_right' LIMIT 1), 'mcl_right', 'MCL', 'Dây chằng bên trong', 3, 'ligament', 'structures/knee-ligaments.svg', 'mcl', 'FMA44612', 8),
((SELECT id FROM anatomy_regions WHERE code = 'knee_right' LIMIT 1), 'lcl_right', 'LCL', 'Dây chằng bên ngoài', 3, 'ligament', 'structures/knee-ligaments.svg', 'lcl', 'FMA44613', 9),
-- Knee Menisci
((SELECT id FROM anatomy_regions WHERE code = 'knee_right' LIMIT 1), 'medial_meniscus_right', 'Medial Meniscus', 'Sụn chêm trong', 3, 'joint', 'structures/knee-menisci.svg', 'medial_meniscus', 'FMA76690', 10),
((SELECT id FROM anatomy_regions WHERE code = 'knee_right' LIMIT 1), 'lateral_meniscus_right', 'Lateral Meniscus', 'Sụn chêm ngoài', 3, 'joint', 'structures/knee-menisci.svg', 'lateral_meniscus', 'FMA76691', 11);

-- Level 3: Lumbar spine structures
INSERT INTO `anatomy_regions` (`parent_id`, `code`, `name_en`, `name_vi`, `level`, `structure_type`, `svg_file`, `svg_element_id`, `fma_id`, `display_order`) VALUES
-- Lumbar Vertebrae
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'l1_vertebra', 'L1 Vertebra', 'Đốt sống L1', 3, 'bone', 'structures/lumbar-vertebrae.svg', 'l1', 'FMA13072', 1),
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'l2_vertebra', 'L2 Vertebra', 'Đốt sống L2', 3, 'bone', 'structures/lumbar-vertebrae.svg', 'l2', 'FMA13073', 2),
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'l3_vertebra', 'L3 Vertebra', 'Đốt sống L3', 3, 'bone', 'structures/lumbar-vertebrae.svg', 'l3', 'FMA13074', 3),
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'l4_vertebra', 'L4 Vertebra', 'Đốt sống L4', 3, 'bone', 'structures/lumbar-vertebrae.svg', 'l4', 'FMA13075', 4),
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'l5_vertebra', 'L5 Vertebra', 'Đốt sống L5', 3, 'bone', 'structures/lumbar-vertebrae.svg', 'l5', 'FMA13076', 5),
-- Lumbar Discs
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'l1_l2_disc', 'L1-L2 Disc', 'Đĩa đệm L1-L2', 3, 'joint', 'structures/lumbar-discs.svg', 'l1l2_disc', 'FMA10506', 6),
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'l2_l3_disc', 'L2-L3 Disc', 'Đĩa đệm L2-L3', 3, 'joint', 'structures/lumbar-discs.svg', 'l2l3_disc', 'FMA10507', 7),
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'l3_l4_disc', 'L3-L4 Disc', 'Đĩa đệm L3-L4', 3, 'joint', 'structures/lumbar-discs.svg', 'l3l4_disc', 'FMA10508', 8),
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'l4_l5_disc', 'L4-L5 Disc', 'Đĩa đệm L4-L5', 3, 'joint', 'structures/lumbar-discs.svg', 'l4l5_disc', 'FMA10509', 9),
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'l5_s1_disc', 'L5-S1 Disc', 'Đĩa đệm L5-S1', 3, 'joint', 'structures/lumbar-discs.svg', 'l5s1_disc', 'FMA10510', 10),
-- Lumbar Muscles
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'erector_spinae', 'Erector Spinae', 'Cơ dựng sống', 3, 'muscle', 'structures/lumbar-muscles.svg', 'erector_spinae', 'FMA71302', 11),
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'multifidus', 'Multifidus', 'Cơ nhiều chẽ', 3, 'muscle', 'structures/lumbar-muscles.svg', 'multifidus', 'FMA22813', 12),
((SELECT id FROM anatomy_regions WHERE code = 'lumbar_spine' LIMIT 1), 'quadratus_lumborum', 'Quadratus Lumborum', 'Cơ vuông thắt lưng', 3, 'muscle', 'structures/lumbar-muscles.svg', 'ql', 'FMA22762', 13);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_anatomy_parent_level ON `anatomy_regions` (`parent_id`, `level`);
CREATE INDEX IF NOT EXISTS idx_selection_patient_assessment ON `pt_anatomical_selections` (`patient_id`, `assessment_id`);

-- Log successful creation
INSERT INTO `vietnamese_test` (`vietnamese_text`) VALUES
(CONCAT('Anatomy visualization schema created at ', NOW(), ' - Lược đồ hiển thị giải phẫu được tạo'));
