-- OpenEMR Vietnamese Physiotherapy Database Initialization
-- Sets up Vietnamese character support and basic PT customizations
-- Author: Dang Tran <tqvdang@msn.com>

-- Ensure proper character set and collation
SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci;

-- Create openemr database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `openemr` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_vietnamese_ci;

-- Grant privileges to openemr user
GRANT ALL PRIVILEGES ON `openemr`.* TO 'openemr'@'%';
GRANT ALL PRIVILEGES ON `openemr`.* TO 'openemr'@'localhost';

-- Create a test table for Vietnamese character verification
USE `openemr`;

CREATE TABLE IF NOT EXISTS `vietnamese_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vietnamese_text` text COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Insert test Vietnamese physiotherapy data
INSERT INTO `vietnamese_test` (`vietnamese_text`) VALUES 
('Vật lý trị liệu - Physiotherapy'),
('Bệnh nhân - Patient'),
('Điều trị - Treatment'),
('Tập thể dục - Exercise'),
('Phục hồi chức năng - Rehabilitation'),
('Đau cơ xương khớp - Musculoskeletal pain'),
('Liệu pháp massage - Massage therapy'),
('Kế hoạch điều trị - Treatment plan'),
('Đánh giá chức năng - Functional assessment'),
('Theo dõi tiến triển - Progress monitoring');

-- Create additional indexes for Vietnamese text search
CREATE FULLTEXT INDEX `idx_vietnamese_search` ON `vietnamese_test` (`vietnamese_text`);

-- Set session variables for Vietnamese support
SET SESSION collation_connection = utf8mb4_vietnamese_ci;
SET SESSION character_set_client = utf8mb4;
SET SESSION character_set_results = utf8mb4;

-- Flush privileges to ensure changes take effect
FLUSH PRIVILEGES;

-- Log successful initialization
INSERT INTO `vietnamese_test` (`vietnamese_text`) VALUES 
(CONCAT('Database initialized successfully at ', NOW(), ' - Khởi tạo cơ sở dữ liệu thành công'));

-- Set default time zone to Vietnam
SET GLOBAL time_zone = '+07:00';