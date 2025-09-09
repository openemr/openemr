-- Vietnamese Medical Terminology Database
-- Enhanced bilingual medical term management for Vietnamese physiotherapy
-- Author: Dang Tran <tqvdang@msn.com>

SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci;

-- Ensure database exists with proper Vietnamese support
CREATE DATABASE IF NOT EXISTS `openemr` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_vietnamese_ci;

USE `openemr`;

-- Create Vietnamese medical terminology table
CREATE TABLE IF NOT EXISTS `vietnamese_medical_terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `english_term` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `vietnamese_term` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_vietnamese_ci DEFAULT 'physiotherapy',
  `subcategory` varchar(100) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `description_en` text COLLATE utf8mb4_vietnamese_ci,
  `description_vi` text COLLATE utf8mb4_vietnamese_ci,
  `synonyms_en` text COLLATE utf8mb4_vietnamese_ci,
  `synonyms_vi` text COLLATE utf8mb4_vietnamese_ci,
  `abbreviation` varchar(20) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_category` (`category`),
  INDEX `idx_subcategory` (`subcategory`),
  INDEX `idx_active` (`is_active`),
  INDEX `idx_english_term` (`english_term`),
  INDEX `idx_vietnamese_term` (`vietnamese_term`),
  FULLTEXT `idx_fulltext_en` (`english_term`, `description_en`, `synonyms_en`),
  FULLTEXT `idx_fulltext_vi` (`vietnamese_term`, `description_vi`, `synonyms_vi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Insert comprehensive Vietnamese PT terminology
INSERT INTO `vietnamese_medical_terms` 
(`english_term`, `vietnamese_term`, `category`, `subcategory`, `description_en`, `description_vi`, `synonyms_en`, `synonyms_vi`, `abbreviation`) VALUES

-- General Physiotherapy Terms
('Physiotherapy', 'Vật lý trị liệu', 'general', 'therapy', 'Physical therapy treatment', 'Điều trị bằng phương pháp vật lý', 'Physical Therapy, PT', 'VLTT', 'PT'),
('Physiotherapist', 'Nhà vật lý trị liệu', 'general', 'personnel', 'Licensed physical therapy professional', 'Chuyên gia vật lý trị liệu có chứng chỉ', 'Physical Therapist', 'Bác sĩ VLTT', 'PT'),
('Patient', 'Bệnh nhân', 'general', 'person', 'Person receiving medical treatment', 'Người đang được điều trị y khoa', 'Client', 'Người bệnh', 'PT'),

-- Assessment Terms
('Assessment', 'Đánh giá', 'assessment', 'evaluation', 'Clinical evaluation of patient condition', 'Đánh giá tình trạng lâm sàng của bệnh nhân', 'Evaluation, Examination', 'Thăm khám, Khám định', 'ASSESS'),
('Range of Motion', 'Phạm vi chuyển động', 'assessment', 'measurement', 'Joint movement capacity measurement', 'Đo khả năng chuyển động của khớp', 'ROM', 'Biên độ vận động', 'ROM'),
('Muscle Strength', 'Sức mạnh cơ', 'assessment', 'measurement', 'Muscular force capacity assessment', 'Đánh giá khả năng lực cơ', 'Muscle Power', 'Lực cơ', 'MS'),
('Pain Assessment', 'Đánh giá đau', 'assessment', 'pain', 'Systematic pain evaluation', 'Đánh giá đau một cách có hệ thống', 'Pain Evaluation', 'Thang điểm đau', 'PA'),
('Functional Assessment', 'Đánh giá chức năng', 'assessment', 'function', 'Evaluation of daily living activities', 'Đánh giá hoạt động sinh hoạt hàng ngày', 'ADL Assessment', 'Đánh giá sinh hoạt', 'FA'),
('Postural Assessment', 'Đánh giá tư thế', 'assessment', 'posture', 'Body posture and alignment evaluation', 'Đánh giá tư thế và thẳng hàng cơ thể', 'Posture Analysis', 'Phân tích tư thế', 'POSTURE'),

-- Treatment Terms
('Treatment', 'Điều trị', 'treatment', 'therapy', 'Medical intervention for healing', 'Can thiệp y khoa để chữa lành', 'Therapy, Intervention', 'Liệu pháp', 'TX'),
('Therapeutic Exercise', 'Bài tập trị liệu', 'treatment', 'exercise', 'Prescribed exercises for rehabilitation', 'Bài tập được kê đơn để phục hồi', 'Exercise Therapy', 'Tập luyện điều trị', 'EX'),
('Manual Therapy', 'Liệu pháp thủ công', 'treatment', 'hands-on', 'Hands-on treatment techniques', 'Kỹ thuật điều trị bằng tay', 'Hands-on Therapy', 'Điều trị bằng tay', 'MT'),
('Massage Therapy', 'Liệu pháp massage', 'treatment', 'massage', 'Therapeutic massage treatment', 'Điều trị massage có mục đích trị liệu', 'Therapeutic Massage', 'Massage điều trị', 'MASSAGE'),
('Electrotherapy', 'Điện trị liệu', 'treatment', 'modality', 'Treatment using electrical stimulation', 'Điều trị sử dụng kích thích điện', 'Electrical Stimulation', 'Kích thích điện', 'ESTIM'),
('Heat Therapy', 'Liệu pháp nhiệt', 'treatment', 'modality', 'Treatment using heat application', 'Điều trị sử dụng ứng dụng nhiệt', 'Thermotherapy', 'Nhiệt trị', 'HEAT'),
('Cold Therapy', 'Liệu pháp lạnh', 'treatment', 'modality', 'Treatment using cold application', 'Điều trị sử dụng ứng dụng lạnh', 'Cryotherapy', 'Lạnh trị', 'ICE'),

-- Conditions and Diagnoses
('Lower Back Pain', 'Đau lưng dưới', 'condition', 'spine', 'Pain in lumbar spine region', 'Đau vùng cột sống thắt lưng', 'Lumbar Pain', 'Đau thắt lưng', 'LBP'),
('Neck Pain', 'Đau cổ', 'condition', 'spine', 'Cervical spine pain', 'Đau cột sống cổ', 'Cervical Pain', 'Đau vùng cổ', 'NP'),
('Shoulder Pain', 'Đau vai', 'condition', 'joint', 'Pain in shoulder joint', 'Đau khớp vai', 'Shoulder Joint Pain', 'Đau khớp vai', 'SP'),
('Knee Pain', 'Đau gối', 'condition', 'joint', 'Pain in knee joint', 'Đau khớp gối', 'Knee Joint Pain', 'Đau khớp gối', 'KP'),
('Arthritis', 'Viêm khớp', 'condition', 'joint', 'Joint inflammation condition', 'Tình trạng viêm khớp', 'Joint Inflammation', 'Sưng khớp', 'ARTH'),
('Muscle Strain', 'Căng cơ', 'condition', 'muscle', 'Muscle overstretching injury', 'Chấn thương do căng cơ quá mức', 'Muscle Pull', 'Rách cơ', 'STRAIN'),
('Ligament Sprain', 'Bong gân', 'condition', 'ligament', 'Ligament injury from overstretching', 'Chấn thương dây chằng do căng quá mức', 'Sprain', 'Giãn dây chằng', 'SPRAIN'),

-- Anatomy Terms
('Spine', 'Cột sống', 'anatomy', 'bone', 'Vertebral column', 'Cột xương sống', 'Vertebral Column', 'Xương sống', 'SPINE'),
('Joint', 'Khớp', 'anatomy', 'joint', 'Articulation between bones', 'Nối giữa các xương', 'Articulation', 'Nối xương', 'JOINT'),
('Muscle', 'Cơ', 'anatomy', 'muscle', 'Contractile tissue', 'Mô co rút', 'Muscular Tissue', 'Mô cơ', 'MUSCLE'),
('Ligament', 'Dây chằng', 'anatomy', 'connective', 'Connects bone to bone', 'Nối xương với xương', 'Ligamentous Tissue', 'Mô dây chằng', 'LIG'),
('Tendon', 'Gân', 'anatomy', 'connective', 'Connects muscle to bone', 'Nối cơ với xương', 'Tendinous Tissue', 'Mô gân', 'TENDON'),
('Cartilage', 'Sụn', 'anatomy', 'connective', 'Joint cushioning tissue', 'Mô đệm khớp', 'Cartilaginous Tissue', 'Mô sụn', 'CART'),

-- Equipment and Tools
('Exercise Equipment', 'Thiết bị tập luyện', 'equipment', 'exercise', 'Tools for therapeutic exercise', 'Dụng cụ cho bài tập trị liệu', 'Training Equipment', 'Dụng cụ tập', 'EQUIP'),
('Walking Aid', 'Dụng cụ hỗ trợ đi lại', 'equipment', 'mobility', 'Assistive device for walking', 'Thiết bị hỗ trợ đi bộ', 'Mobility Aid', 'Dụng cụ di chuyển', 'AID'),
('Wheelchair', 'Xe lăn', 'equipment', 'mobility', 'Wheeled mobility device', 'Thiết bị di chuyển có bánh xe', 'Rolling Chair', 'Ghế bánh xe', 'WC'),
('Crutches', 'Nạng', 'equipment', 'mobility', 'Walking support device', 'Thiết bị hỗ trợ đi bộ', 'Walking Sticks', 'Gậy chống', 'CRUTCH'),

-- Insurance and Administrative
('Health Insurance', 'Bảo hiểm y tế', 'administrative', 'insurance', 'Medical coverage insurance', 'Bảo hiểm chi trả y tế', 'Medical Insurance', 'Bảo hiểm sức khỏe', 'BHYT'),
('Medical Record', 'Hồ sơ bệnh án', 'administrative', 'documentation', 'Patient medical documentation', 'Tài liệu y khoa của bệnh nhân', 'Patient Record', 'Bệnh án', 'MR'),
('Treatment Plan', 'Kế hoạch điều trị', 'administrative', 'planning', 'Structured therapy program', 'Chương trình trị liệu có cấu trúc', 'Therapy Plan', 'Phác đồ điều trị', 'PLAN'),
('Progress Note', 'Ghi chú tiến triển', 'administrative', 'documentation', 'Treatment progress documentation', 'Ghi chép tiến triển điều trị', 'Progress Report', 'Báo cáo tiến độ', 'PROGRESS');

-- Create full-text search indexes for better Vietnamese text search
ALTER TABLE `vietnamese_medical_terms` ADD FULLTEXT(`english_term`, `vietnamese_term`, `synonyms_en`, `synonyms_vi`);

-- Create procedure for bilingual term lookup
DELIMITER //
CREATE PROCEDURE GetBilingualTerm(
    IN search_term VARCHAR(255),
    IN search_language VARCHAR(2)
)
BEGIN
    IF search_language = 'vi' THEN
        SELECT * FROM vietnamese_medical_terms 
        WHERE vietnamese_term LIKE CONCAT('%', search_term, '%') 
           OR synonyms_vi LIKE CONCAT('%', search_term, '%')
           OR MATCH(vietnamese_term, synonyms_vi) AGAINST(search_term IN BOOLEAN MODE)
        ORDER BY 
            CASE WHEN vietnamese_term = search_term THEN 1
                 WHEN vietnamese_term LIKE CONCAT(search_term, '%') THEN 2
                 WHEN vietnamese_term LIKE CONCAT('%', search_term, '%') THEN 3
                 ELSE 4 END;
    ELSE
        SELECT * FROM vietnamese_medical_terms 
        WHERE english_term LIKE CONCAT('%', search_term, '%') 
           OR synonyms_en LIKE CONCAT('%', search_term, '%')
           OR MATCH(english_term, synonyms_en) AGAINST(search_term IN BOOLEAN MODE)
        ORDER BY 
            CASE WHEN english_term = search_term THEN 1
                 WHEN english_term LIKE CONCAT(search_term, '%') THEN 2
                 WHEN english_term LIKE CONCAT('%', search_term, '%') THEN 3
                 ELSE 4 END;
    END IF;
END //
DELIMITER ;

-- Log successful terminology setup
INSERT INTO `vietnamese_medical_terms` 
(`english_term`, `vietnamese_term`, `category`, `description_en`, `description_vi`) VALUES
('Database Initialization', 'Khởi tạo cơ sở dữ liệu', 'system', 
 CONCAT('Vietnamese medical terminology database initialized at ', NOW()),
 CONCAT('Cơ sở dữ liệu thuật ngữ y khoa tiếng Việt được khởi tạo lúc ', NOW()));

-- Set session variables for Vietnamese support
SET SESSION collation_connection = utf8mb4_vietnamese_ci;
SET SESSION character_set_client = utf8mb4;
SET SESSION character_set_results = utf8mb4;

-- Set session time zone to Vietnam (global requires SUPER privilege)
SET SESSION time_zone = '+07:00';