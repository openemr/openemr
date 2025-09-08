-- Development Sample Data for Vietnamese Physiotherapy
-- Comprehensive test data for development and testing purposes
-- Author: Dang Tran <tqvdang@msn.com>

SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci;
USE `openemr`;

-- Sample Vietnamese insurance data for development
INSERT INTO `vietnamese_insurance_info` 
(`patient_id`, `bhyt_card_number`, `insurance_provider`, `coverage_type`, `coverage_percentage`, `valid_from`, `valid_to`, `registered_hospital`, `hospital_code`, `insurance_notes`) VALUES

-- Sample patient 1 - Student insurance
(1, 'HS4401234567890', 'Bảo hiểm Xã hội Việt Nam', 'BHYT Học sinh - Sinh viên', 100.00, '2024-01-01', '2024-12-31', 'Bệnh viện Đa khoa Thành phố', 'HN001', 'Bảo hiểm học sinh, miễn phí hoàn toàn'),

-- Sample patient 2 - Family insurance  
(2, 'GD1234567890123', 'Bảo hiểm Xã hội Việt Nam', 'BHYT Gia đình', 80.00, '2024-01-01', '2024-12-31', 'Phòng khám Đa khoa Quận 1', 'HCM002', 'Bảo hiểm gia đình, đồng chi trả 20%'),

-- Sample patient 3 - Worker insurance
(3, 'NV9876543210987', 'Bảo hiểm Xã hội Việt Nam', 'BHYT Người lao động', 95.00, '2024-01-01', '2024-12-31', 'Bệnh viện Chấn thương Chỉnh hình', 'HN003', 'Bảo hiểm người lao động, đồng chi trả 5%'),

-- Sample patient 4 - Elderly insurance
(4, 'NC5555444433332', 'Bảo hiểm Xã hội Việt Nam', 'BHYT Người cao tuổi', 100.00, '2024-01-01', '2024-12-31', 'Bệnh viện Lão khoa Trung ương', 'HN004', 'Bảo hiểm người cao tuổi, miễn phí'),

-- Sample patient 5 - Children insurance
(5, 'TE1111222233334', 'Bảo hiểm Xã hội Việt Nam', 'BHYT Trẻ em dưới 6 tuổi', 100.00, '2024-01-01', '2024-12-31', 'Bệnh viện Nhi đồng Thành phố', 'HCM005', 'Bảo hiểm trẻ em, miễn phí hoàn toàn');

-- Sample PT assessment data with comprehensive bilingual content
INSERT INTO `pt_assessments_bilingual` 
(`patient_id`, `encounter_id`, `assessment_date`, `therapist_id`, `chief_complaint_en`, `chief_complaint_vi`, `pain_level`, `pain_location_en`, `pain_location_vi`, `pain_description_en`, `pain_description_vi`, `rom_measurements`, `strength_measurements`, `balance_assessment`, `functional_goals_en`, `functional_goals_vi`, `treatment_plan_en`, `treatment_plan_vi`, `language_preference`, `status`, `assessment_type`) VALUES

-- Assessment 1: Lower back pain case
(1, 1, '2024-09-01 09:00:00', 101, 
 'Lower back pain for 3 weeks after lifting heavy objects at work', 
 'Đau lưng dưới kéo dài 3 tuần sau khi nâng vật nặng tại nơi làm việc',
 7, 
 'Lower lumbar region, radiating to right leg', 
 'Vùng thắt lưng dưới, lan xuống chân phải',
 'Sharp, stabbing pain that worsens with forward bending and prolonged sitting',
 'Đau nhói, âm ỉ, tăng khi cúi về phía trước và ngồi lâu',
 JSON_OBJECT(
   'lumbar_flexion', 45,
   'lumbar_extension', 15,
   'right_hip_flexion', 85,
   'left_hip_flexion', 110,
   'right_SLR', 40,
   'left_SLR', 80
 ),
 JSON_OBJECT(
   'hip_flexors_right', '3/5',
   'hip_flexors_left', '4/5',
   'back_extensors', '3/5',
   'abdominals', '2/5',
   'gluteus_maximus', '3/5'
 ),
 JSON_OBJECT(
   'single_leg_stance_right', 15,
   'single_leg_stance_left', 28,
   'tandem_walk', 'impaired',
   'berg_balance_scale', 52
 ),
 'Return to work without pain restrictions, improve lifting mechanics, restore normal range of motion',
 'Trở lại làm việc không bị hạn chế do đau, cải thiện kỹ thuật nâng vật, khôi phục biên độ chuyển động bình thường',
 'Manual therapy for spine mobilization, therapeutic exercises for core strengthening, ergonomic training',
 'Liệu pháp thủ công để vận động cột sống, bài tập trị liệu tăng cường cơ core, đào tạo ergonomic',
 'vi', 'completed', 'initial'),

-- Assessment 2: Shoulder impingement case
(2, 2, '2024-09-02 14:00:00', 102,
 'Right shoulder pain and stiffness for 2 months, difficulty raising arm overhead',
 'Đau và cứng vai phải kéo dài 2 tháng, khó nâng tay lên trên cao',
 6,
 'Right shoulder, anterior and lateral aspects',
 'Vai phải, mặt trước và bên',
 'Dull ache with sharp pain during overhead activities, worse at night',
 'Đau âm ỉ kèm đau nhói khi hoạt động trên cao, tệ hơn vào ban đêm',
 JSON_OBJECT(
   'shoulder_flexion_right', 120,
   'shoulder_flexion_left', 170,
   'shoulder_abduction_right', 90,
   'shoulder_abduction_left', 175,
   'internal_rotation_right', 35,
   'external_rotation_right', 40
 ),
 JSON_OBJECT(
   'deltoid', '4/5',
   'rotator_cuff', '3/5',
   'rhomboids', '3/5',
   'serratus_anterior', '2/5',
   'lower_trapezius', '3/5'
 ),
 JSON_OBJECT(
   'shoulder_stability', 'impaired',
   'scapular_control', 'poor',
   'proprioception', 'decreased'
 ),
 'Pain-free overhead reaching, return to recreational tennis, improve shoulder stability',
 'Với tay lên trên không đau, trở lại chơi tennis giải trí, cải thiện độ ổn định vai',
 'Manual therapy, progressive strengthening exercises, activity modification, gradual return to sport',
 'Liệu pháp thủ công, bài tập tăng cường sức mạnh từng bước, điều chỉnh hoạt động, trở lại thể thao dần dần',
 'en', 'completed', 'initial'),

-- Assessment 3: Knee osteoarthritis case  
(3, 3, '2024-09-03 10:30:00', 101,
 'Bilateral knee pain and stiffness, worse in the morning and after prolonged activity',
 'Đau và cứng hai đầu gối, tệ hơn vào buổi sáng và sau hoạt động kéo dài',
 5,
 'Both knees, medial compartment predominantly',
 'Cả hai đầu gối, chủ yếu khoang trong',
 'Stiffness and aching pain, grinding sensation with movement',
 'Đau cứng và âm ỉ, cảm giác kêu cót két khi chuyển động',
 JSON_OBJECT(
   'knee_flexion_right', 95,
   'knee_flexion_left', 100,
   'knee_extension_right', -5,
   'knee_extension_left', -3,
   'hip_flexion_right', 100,
   'hip_flexion_left', 105
 ),
 JSON_OBJECT(
   'quadriceps_right', '3/5',
   'quadriceps_left', '4/5',
   'hamstrings', '4/5',
   'hip_abductors', '3/5',
   'calf_muscles', '4/5'
 ),
 JSON_OBJECT(
   'static_balance', 'fair',
   'dynamic_balance', 'impaired',
   'proprioception', 'decreased',
   'timed_up_and_go', 14.5
 ),
 'Reduce pain and stiffness, improve functional mobility, delay joint replacement',
 'Giảm đau và cứng khớp, cải thiện khả năng vận động chức năng, trì hoãn thay khớp',
 'Joint mobilization, strengthening exercises, aquatic therapy, pain management education',
 'Vận động khớp, bài tập tăng cường sức mạnh, liệu pháp dưới nước, giáo dục quản lý đau',
 'vi', 'completed', 'initial'),

-- Assessment 4: Post-stroke rehabilitation
(4, 4, '2024-09-04 08:00:00', 103,
 'Left-sided weakness and balance problems 6 weeks post-stroke',
 'Yếu bên trái và vấn đề thăng bằng sau đột quỵ 6 tuần',
 3,
 'Mild headache, no significant pain',
 'Nhức đầu nhẹ, không đau đáng kể',
 'Occasional headache, mainly concerned about weakness and balance',
 'Đau đầu thỉnh thoảng, chủ yếu lo lắng về tình trạng yếu và mất thăng bằng',
 JSON_OBJECT(
   'shoulder_flexion_left', 80,
   'shoulder_abduction_left', 70,
   'elbow_flexion_left', 110,
   'wrist_extension_left', 30,
   'hip_flexion_left', 85,
   'knee_flexion_left', 100,
   'ankle_dorsiflexion_left', 5
 ),
 JSON_OBJECT(
   'left_upper_extremity', '2/5',
   'left_lower_extremity', '3/5',
   'trunk_control', '3/5',
   'grip_strength_left', '1/5'
 ),
 JSON_OBJECT(
   'sitting_balance', 'good',
   'standing_balance', 'fair_with_support',
   'berg_balance_scale', 35,
   'fall_risk', 'high'
 ),
 'Improve strength and coordination, achieve independent walking, prevent falls',
 'Cải thiện sức mạnh và phối hợp, đạt được khả năng đi lại độc lập, ngăn ngừa ngã',
 'Neurodevelopmental treatment, gait training, balance exercises, family education',
 'Điều trị phát triển thần kinh, luyện tập đi bộ, bài tập thăng bằng, giáo dục gia đình',
 'vi', 'completed', 'initial'),

-- Assessment 5: Pediatric developmental delay
(5, 5, '2024-09-05 15:00:00', 104,
 '3-year-old with delayed motor development, not walking independently',
 'Trẻ 3 tuổi chậm phát triển vận động, chưa đi được độc lập',
 1,
 'No specific pain complaints',
 'Không phàn nàn đau cụ thể',
 'Child appears comfortable, no distress noted',
 'Trẻ có vẻ thoải mái, không có dấu hiệu khó chịu',
 JSON_OBJECT(
   'hip_flexion', 110,
   'hip_extension', 10,
   'knee_flexion', 135,
   'ankle_dorsiflexion', 0,
   'trunk_rotation', 30
 ),
 JSON_OBJECT(
   'head_control', 'good',
   'trunk_control', 'fair',
   'hip_abductors', '2/5',
   'quadriceps', '3/5',
   'dorsiflexors', '2/5'
 ),
 JSON_OBJECT(
   'sitting_unsupported', 'achieved',
   'standing_with_support', 'emerging',
   'cruising', 'not_achieved',
   'protective_reactions', 'delayed'
 ),
 'Achieve independent walking, improve gross motor skills, support family',
 'Đạt được khả năng đi bộ độc lập, cải thiện kỹ năng vận động thô, hỗ trợ gia đình',
 'Developmental activities, strengthening exercises, family training, equipment assessment',
 'Hoạt động phát triển, bài tập tăng cường sức mạnh, đào tạo gia đình, đánh giá thiết bị',
 'vi', 'completed', 'initial');

-- Sample exercise prescriptions with bilingual instructions
INSERT INTO `pt_exercise_prescriptions` 
(`patient_id`, `assessment_id`, `therapist_id`, `exercise_name_en`, `exercise_name_vi`, `exercise_category`, `instructions_en`, `instructions_vi`, `precautions_en`, `precautions_vi`, `sets_prescribed`, `reps_prescribed`, `frequency_per_day`, `frequency_per_week`, `duration_weeks`, `difficulty_level`, `prescribed_date`, `status`) VALUES

-- Exercises for lower back pain patient
(1, 1, 101, 'Cat-Cow Stretch', 'Duỗi mèo-bò', 'stretching',
 'Start on hands and knees. Arch your back up like a cat, then lower and lift your head like a cow. Move slowly and controlled.',
 'Bắt đầu bằng tư thế quỳ. Cong lưng lên như con mèo, sau đó hạ xuống và ngẩng đầu như con bò. Di chuyển chậm và kiểm soát.',
 'Stop if pain increases. Do not force the movement.',
 'Dừng lại nếu đau tăng. Không ép buộc chuyển động.',
 2, 10, 2, 7, 4, 'beginner', '2024-09-01 09:30:00', 'active'),

(1, 1, 101, 'Pelvic Tilt', 'Nghiêng khung chậu', 'strengthening',
 'Lie on back with knees bent. Tighten abdominal muscles and tilt pelvis to flatten lower back against floor.',
 'Nằm ngửa với đầu gối cong. Siết cơ bụng và nghiêng khung chậu để làm phẳng lưng dưới áp sát sàn.',
 'Keep breathing normally. Do not hold breath.',
 'Giữ thở bình thường. Không nín thở.',
 2, 15, 3, 7, 4, 'beginner', '2024-09-01 09:35:00', 'active'),

-- Exercises for shoulder impingement patient  
(2, 2, 102, 'Pendulum Exercise', 'Bài tập con lắc', 'mobility',
 'Lean forward and let affected arm hang down. Gently swing arm in small circles, forward/back, and side to side.',
 'Cúi người về phía trước và để cánh tay bị ảnh hưởng rủ xuống. Nhẹ nhàng xoay cánh tay theo vòng tròn nhỏ, trước/sau và sang hai bên.',
 'Use gravity to assist movement. Do not actively lift the arm.',
 'Sử dụng trọng lực để hỗ trợ chuyển động. Không chủ động nâng cánh tay.',
 2, 20, 3, 7, 3, 'beginner', '2024-09-02 14:30:00', 'active'),

(2, 2, 102, 'Wall Slides', 'Trượt tường', 'strengthening',
 'Stand with back against wall. Slide arms up and down the wall keeping contact with wall throughout movement.',
 'Đứng lưng dựa vào tường. Trượt cánh tay lên xuống trên tường giữ tiếp xúc với tường trong suốt chuyển động.',
 'Stop if pain occurs. Move only within pain-free range.',
 'Dừng lại nếu có đau. Chỉ di chuyển trong phạm vi không đau.',
 3, 12, 2, 6, 4, 'intermediate', '2024-09-02 14:35:00', 'active'),

-- Exercises for knee osteoarthritis patient
(3, 3, 101, 'Quad Sets', 'Siết cơ tứ đầu', 'strengthening',
 'Sit with leg straight. Tighten thigh muscle and press knee down into surface. Hold for 5 seconds.',
 'Ngồi với chân thẳng. Siết cơ đùi và ấn đầu gối xuống mặt đỡ. Giữ trong 5 giây.',
 'Do not hold breath. Keep ankle relaxed.',
 'Không nín thở. Giữ mắt cá chân thư giãn.',
 3, 15, 3, 7, 6, 'beginner', '2024-09-03 11:00:00', 'active'),

(3, 3, 101, 'Straight Leg Raises', 'Nâng chân thẳng', 'strengthening',
 'Lie on back with one knee bent. Lift straight leg to height of bent knee. Lower slowly.',
 'Nằm ngửa với một đầu gối cong. Nâng chân thẳng đến độ cao của đầu gối cong. Hạ chậm.',
 'Keep knee straight throughout. Stop if back pain occurs.',
 'Giữ đầu gối thẳng trong suốt bài tập. Dừng nếu đau lưng.',
 2, 12, 2, 6, 6, 'intermediate', '2024-09-03 11:05:00', 'active');

-- Sample outcome measures
INSERT INTO `pt_outcome_measures` 
(`patient_id`, `assessment_id`, `therapist_id`, `measure_name_en`, `measure_name_vi`, `measure_type`, `measurement_date`, `raw_score`, `percentage_score`, `interpretation_en`, `interpretation_vi`, `normal_range_min`, `normal_range_max`, `unit_of_measure`, `baseline_measurement`) VALUES

-- Functional outcome measures
(1, 1, 101, 'Oswestry Disability Index', 'Chỉ số Khuyết tật Oswestry', 'functional', '2024-09-01 10:00:00', 28, 56, 
 'Moderate disability. Patient has significant difficulty with daily activities.',
 'Khuyết tật vừa phải. Bệnh nhân gặp khó khăn đáng kể trong các hoạt động hàng ngày.',
 0, 20, 'percentage', 1),

(2, 2, 102, 'DASH Score', 'Điểm DASH', 'functional', '2024-09-02 14:00:00', 45, 45,
 'Moderate disability in arm, shoulder and hand function.',
 'Khuyết tật vừa phải về chức năng cánh tay, vai và bàn tay.',
 0, 30, 'score', 1),

(3, 3, 101, 'WOMAC Score', 'Điểm WOMAC', 'functional', '2024-09-03 10:30:00', 42, 42,
 'Moderate symptoms and functional limitations due to osteoarthritis.',
 'Triệu chứng vừa phải và hạn chế chức năng do viêm khớp.',
 0, 24, 'score', 1),

-- Pain measures
(1, 1, 101, 'Numeric Pain Rating Scale', 'Thang Điểm Đau Số', 'pain', '2024-09-01 09:00:00', 7, 70,
 'Severe pain significantly impacting daily activities.',
 'Đau nghiêm trọng ảnh hưởng đáng kể đến hoạt động hàng ngày.',
 0, 3, 'points', 1),

(2, 2, 102, 'Numeric Pain Rating Scale', 'Thang Điểm Đau Số', 'pain', '2024-09-02 14:00:00', 6, 60,
 'Moderate to severe pain affecting function.',
 'Đau từ vừa đến nghiêm trọng ảnh hưởng chức năng.',
 0, 3, 'points', 1);

-- Sample treatment session notes
INSERT INTO `pt_treatment_sessions`
(`patient_id`, `assessment_id`, `therapist_id`, `session_date`, `session_duration_minutes`, `treatments_provided`, `objective_findings_en`, `objective_findings_vi`, `subjective_response_en`, `subjective_response_vi`, `pain_level_pre`, `pain_level_post`, `home_exercise_compliance`, `plan_en`, `plan_vi`, `session_status`) VALUES

-- Session for lower back pain patient
(1, 1, 101, '2024-09-08 09:00:00', 60, 
 JSON_ARRAY('Manual therapy - lumbar mobilization', 'Therapeutic exercise - core strengthening', 'Patient education - lifting mechanics'),
 'Patient demonstrated improved lumbar flexion ROM from 45° to 55°. Core strength appears slightly improved.',
 'Bệnh nhân cho thấy cải thiện biên độ cúi lưng từ 45° lên 55°. Sức mạnh cơ core có vẻ cải thiện nhẹ.',
 'Patient reports feeling looser after manual therapy. Pain decreased during session.',
 'Bệnh nhân báo cáo cảm thấy thoải mái hơn sau liệu pháp thủ công. Đau giảm trong phiên điều trị.',
 7, 4, 'good',
 'Continue current exercise program. Add progressive loading exercises next session.',
 'Tiếp tục chương trình tập luyện hiện tại. Thêm bài tập tăng tải tiến bộ trong phiên tới.',
 'completed'),

-- Session for shoulder impingement patient
(2, 2, 102, '2024-09-09 14:00:00', 45,
 JSON_ARRAY('Manual therapy - shoulder mobilization', 'Therapeutic exercise - rotator cuff strengthening', 'Postural training'),
 'Shoulder flexion improved from 120° to 135°. Strength testing shows minimal improvement in external rotation.',
 'Cải thiện gập vai từ 120° lên 135°. Kiểm tra sức mạnh cho thấy cải thiện tối thiểu trong xoay ngoài.',
 'Less pain with overhead activities. Sleep quality improved.',
 'Ít đau hơn với hoạt động trên cao. Chất lượng giấc ngủ được cải thiện.',
 6, 3, 'excellent',
 'Progress to next level exercises. Consider sport-specific training.',
 'Tiến tới bài tập cấp độ tiếp theo. Xem xét luyện tập đặc thù thể thao.',
 'completed');

-- Create sample data summary
INSERT INTO `vietnamese_medical_terms` 
(`english_term`, `vietnamese_term`, `category`, `description_en`, `description_vi`) VALUES
('Sample Data Loaded', 'Dữ liệu mẫu đã tải', 'system', 
 CONCAT('Development sample data successfully loaded at ', NOW(), '. Includes 5 patients, 5 assessments, exercise prescriptions, and outcome measures.'),
 CONCAT('Dữ liệu mẫu phát triển đã được tải thành công lúc ', NOW(), '. Bao gồm 5 bệnh nhân, 5 đánh giá, đơn kê tập, và thước đo kết quả.'));

-- Log completion
SELECT 
    COUNT(*) as total_records,
    'Sample data loading completed successfully' as status,
    NOW() as timestamp
FROM (
    SELECT id FROM vietnamese_insurance_info
    UNION ALL SELECT id FROM pt_assessments_bilingual  
    UNION ALL SELECT id FROM pt_exercise_prescriptions
    UNION ALL SELECT id FROM pt_outcome_measures
    UNION ALL SELECT id FROM pt_treatment_sessions
) as all_sample_data;