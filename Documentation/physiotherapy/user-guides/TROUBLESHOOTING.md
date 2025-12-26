# Vietnamese PT Module - Troubleshooting Guide
# Hướng dẫn Khắc phục Sự cố - Module VLTT Việt Nam

**Version / Phiên bản:** 1.0
**Last Updated / Cập nhật:** 2025-11-22

---

## Table of Contents / Mục lục

**English:**
- [Common Issues](#common-issues)
- [Database Problems](#database-problems)
- [Form Issues](#form-issues)
- [API Errors](#api-errors)
- [Character Encoding Issues](#character-encoding-issues)
- [Permission Problems](#permission-problems)
- [Performance Issues](#performance-issues)
- [Getting Support](#getting-support)

**Tiếng Việt:**
- [Sự cố Thường gặp](#sự-cố-thường-gặp)
- [Vấn đề Cơ sở Dữ liệu](#vấn-đề-cơ-sở-dữ-liệu)
- [Vấn đề Biểu mẫu](#vấn-đề-biểu-mẫu)
- [Lỗi API](#lỗi-api)
- [Vấn đề Mã hóa Ký tự](#vấn-đề-mã-hóa-ký-tự)
- [Vấn đề Quyền truy cập](#vấn-đề-quyền-truy-cập)
- [Vấn đề Hiệu suất](#vấn-đề-hiệu-suất)
- [Nhận Hỗ trợ](#nhận-hỗ-trợ)

---

## Common Issues
## Sự cố Thường gặp

### Issue 1: Vietnamese Characters Display Incorrectly
### Vấn đề 1: Ký tự Tiếng Việt Hiển thị Sai

**English:**

**Symptoms:**
- Vietnamese text appears as question marks (???)
- Accented characters display as boxes or gibberish
- Patient names with Vietnamese characters show incorrectly

**Causes:**
- Database collation not set to `utf8mb4_vietnamese_ci`
- Browser encoding not set to UTF-8
- Form not using proper character encoding

**Solutions:**

1. **Check Database Collation:**
```sql
-- Run in MySQL/MariaDB
SHOW CREATE TABLE pt_assessments_bilingual\G

-- Should show: COLLATE=utf8mb4_vietnamese_ci
```

2. **Fix Database Collation:**
```sql
-- Convert table to correct collation
ALTER TABLE pt_assessments_bilingual
CONVERT TO CHARACTER SET utf8mb4
COLLATE utf8mb4_vietnamese_ci;
```

3. **Check Browser Encoding:**
- Open browser developer tools (F12)
- Check that page encoding is UTF-8
- If not, add to form HTML:
```html
<meta charset="UTF-8">
```

4. **Verify PHP Output:**
```php
// Add to top of PHP files
header('Content-Type: text/html; charset=UTF-8');
```

---

**Tiếng Việt:**

**Triệu chứng:**
- Văn bản tiếng Việt hiển thị dấu hỏi (???)
- Ký tự có dấu hiển thị dạng hộp hoặc ký tự lạ
- Tên bệnh nhân có ký tự tiếng Việt hiển thị sai

**Nguyên nhân:**
- Collation cơ sở dữ liệu không được đặt là `utf8mb4_vietnamese_ci`
- Trình duyệt không dùng mã hóa UTF-8
- Biểu mẫu không sử dụng mã hóa ký tự đúng

**Giải pháp:**

1. **Kiểm tra Collation Cơ sở Dữ liệu:**
```sql
-- Chạy trong MySQL/MariaDB
SHOW CREATE TABLE pt_assessments_bilingual\G

-- Phải hiển thị: COLLATE=utf8mb4_vietnamese_ci
```

2. **Sửa Collation Cơ sở Dữ liệu:**
```sql
-- Chuyển đổi bảng sang collation đúng
ALTER TABLE pt_assessments_bilingual
CONVERT TO CHARACTER SET utf8mb4
COLLATE utf8mb4_vietnamese_ci;
```

3. **Kiểm tra Mã hóa Trình duyệt:**
- Mở công cụ nhà phát triển (F12)
- Kiểm tra mã hóa trang là UTF-8
- Nếu không, thêm vào HTML form:
```html
<meta charset="UTF-8">
```

---

### Issue 2: Forms Not Appearing in Menu
### Vấn đề 2: Biểu mẫu Không Xuất hiện trong Menu

**English:**

**Symptoms:**
- Vietnamese PT forms not listed in "Add Form" menu
- Forms show in database but not in UI
- Error when trying to access form directly

**Solutions:**

1. **Check Form Registration:**
```sql
-- Verify forms are registered
SELECT name, directory, state
FROM registry
WHERE directory LIKE 'vietnamese_pt%';
```

2. **Enable Forms:**
```sql
-- Set state to 1 (active)
UPDATE registry
SET state = 1
WHERE directory LIKE 'vietnamese_pt%';
```

3. **Clear Cache:**
```bash
# Clear OpenEMR cache
sudo rm -rf sites/default/documents/smarty/cache/*
sudo rm -rf sites/default/documents/smarty/main/*

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

4. **Check User Permissions:**
- Log in as administrator
- Go to: Administration → ACL
- Verify user has "Encounters" and "Clinical Notes" permissions

---

**Tiếng Việt:**

**Triệu chứng:**
- Biểu mẫu VLTT Việt Nam không có trong menu "Thêm Biểu mẫu"
- Biểu mẫu có trong cơ sở dữ liệu nhưng không hiển thị trong UI
- Lỗi khi cố gắng truy cập biểu mẫu trực tiếp

**Giải pháp:**

1. **Kiểm tra Đăng ký Biểu mẫu:**
```sql
-- Xác minh biểu mẫu đã được đăng ký
SELECT name, directory, state
FROM registry
WHERE directory LIKE 'vietnamese_pt%';
```

2. **Kích hoạt Biểu mẫu:**
```sql
-- Đặt state thành 1 (kích hoạt)
UPDATE registry
SET state = 1
WHERE directory LIKE 'vietnamese_pt%';
```

3. **Xóa Bộ nhớ Cache:**
```bash
# Xóa cache OpenEMR
sudo rm -rf sites/default/documents/smarty/cache/*
sudo rm -rf sites/default/documents/smarty/main/*

# Khởi động lại PHP-FPM
sudo systemctl restart php8.2-fpm
```

---

### Issue 3: Cannot Save Assessment Data
### Vấn đề 3: Không thể Lưu Dữ liệu Đánh giá

**English:**

**Symptoms:**
- Form submission fails with no error message
- Data disappears after saving
- "Validation error" displayed but no details

**Solutions:**

1. **Check Required Fields:**
- Ensure all required fields are filled:
  - Patient ID
  - Encounter ID
  - Assessment Date

2. **Check Browser Console:**
- Open browser developer tools (F12)
- Go to Console tab
- Look for JavaScript errors

3. **Check PHP Error Log:**
```bash
# View PHP error log
sudo tail -f /var/log/php8.2-fpm.log

# Look for validation errors
```

4. **Verify Field Lengths:**
- Check text fields don't exceed database limits
- `chief_complaint`: TEXT (65,535 characters max)
- `pain_level`: INT (0-10 only)

5. **Test with Minimal Data:**
```json
{
  "patient_id": 1,
  "encounter_id": 1,
  "assessment_date": "2025-11-22 10:00:00",
  "language_preference": "vi",
  "status": "draft"
}
```

---

**Tiếng Việt:**

**Triệu chứng:**
- Gửi biểu mẫu thất bại không có thông báo lỗi
- Dữ liệu biến mất sau khi lưu
- Hiển thị "Lỗi xác thực" nhưng không có chi tiết

**Giải pháp:**

1. **Kiểm tra Trường Bắt buộc:**
- Đảm bảo tất cả trường bắt buộc được điền:
  - ID Bệnh nhân
  - ID Cuộc khám
  - Ngày Đánh giá

2. **Kiểm tra Console Trình duyệt:**
- Mở công cụ nhà phát triển (F12)
- Chuyển sang tab Console
- Tìm lỗi JavaScript

3. **Kiểm tra Log Lỗi PHP:**
```bash
# Xem log lỗi PHP
sudo tail -f /var/log/php8.2-fpm.log

# Tìm lỗi xác thực
```

4. **Xác minh Độ dài Trường:**
- Kiểm tra trường văn bản không vượt quá giới hạn cơ sở dữ liệu
- `chief_complaint`: TEXT (tối đa 65,535 ký tự)
- `pain_level`: INT (chỉ 0-10)

---

## Database Problems
## Vấn đề Cơ sở Dữ liệu

### Issue 4: Database Connection Failed
### Vấn đề 4: Kết nối Cơ sở Dữ liệu Thất bại

**English:**

**Symptoms:**
- Error: "Could not connect to database"
- Forms load but cannot save data
- API returns 500 Internal Server Error

**Solutions:**

1. **Test Database Connection:**
```bash
# Test connection
mysql -u openemr -p openemr -e "SELECT 1;"

# If fails, check credentials in:
cat sites/default/sqlconf.php
```

2. **Verify Database Service:**
```bash
# Check MariaDB is running
sudo systemctl status mariadb

# If stopped, start it:
sudo systemctl start mariadb
```

3. **Check Database Grants:**
```sql
-- Verify user permissions
SHOW GRANTS FOR 'openemr'@'localhost';

-- Should include:
-- GRANT ALL PRIVILEGES ON openemr.* TO 'openemr'@'localhost'
```

4. **Check Database Size:**
```sql
-- Check if database is full
SELECT
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'openemr'
GROUP BY table_schema;
```

---

**Tiếng Việt:**

**Triệu chứng:**
- Lỗi: "Không thể kết nối cơ sở dữ liệu"
- Biểu mẫu tải nhưng không thể lưu dữ liệu
- API trả về lỗi 500 Internal Server Error

**Giải pháp:**

1. **Kiểm tra Kết nối Cơ sở Dữ liệu:**
```bash
# Kiểm tra kết nối
mysql -u openemr -p openemr -e "SELECT 1;"

# Nếu thất bại, kiểm tra thông tin đăng nhập trong:
cat sites/default/sqlconf.php
```

2. **Xác minh Dịch vụ Cơ sở Dữ liệu:**
```bash
# Kiểm tra MariaDB đang chạy
sudo systemctl status mariadb

# Nếu đã dừng, khởi động:
sudo systemctl start mariadb
```

---

### Issue 5: Slow Query Performance
### Vấn đề 5: Hiệu suất Truy vấn Chậm

**English:**

**Symptoms:**
- Forms take long time to load (>5 seconds)
- API responses are slow
- Database CPU usage is high

**Solutions:**

1. **Check Indexes:**
```sql
-- Verify indexes exist
SHOW INDEX FROM pt_assessments_bilingual;

-- Should show indexes on:
-- patient_id, encounter_id, therapist_id, assessment_date
```

2. **Analyze Tables:**
```sql
-- Update table statistics
ANALYZE TABLE pt_assessments_bilingual;
ANALYZE TABLE pt_exercise_prescriptions;
ANALYZE TABLE pt_treatment_plans;
ANALYZE TABLE pt_outcome_measures;
```

3. **Optimize Tables:**
```sql
-- Defragment tables
OPTIMIZE TABLE pt_assessments_bilingual;
OPTIMIZE TABLE pt_exercise_prescriptions;
```

4. **Check Slow Queries:**
```bash
# Enable slow query log
sudo mysql -u root -p -e "SET GLOBAL slow_query_log = 'ON';"
sudo mysql -u root -p -e "SET GLOBAL long_query_time = 2;"

# View slow queries
sudo tail -f /var/log/mysql/slow-query.log
```

5. **Increase Buffer Pool:**
```ini
# Edit my.cnf or mariadb.conf.d/50-server.cnf
[mysqld]
innodb_buffer_pool_size = 2G  # 50-70% of available RAM

# Restart MariaDB
sudo systemctl restart mariadb
```

---

**Tiếng Việt:**

**Triệu chứng:**
- Biểu mẫu mất nhiều thời gian để tải (>5 giây)
- API phản hồi chậm
- Sử dụng CPU cơ sở dữ liệu cao

**Giải pháp:**

1. **Kiểm tra Chỉ mục:**
```sql
-- Xác minh chỉ mục tồn tại
SHOW INDEX FROM pt_assessments_bilingual;

-- Phải hiển thị chỉ mục trên:
-- patient_id, encounter_id, therapist_id, assessment_date
```

2. **Phân tích Bảng:**
```sql
-- Cập nhật thống kê bảng
ANALYZE TABLE pt_assessments_bilingual;
ANALYZE TABLE pt_exercise_prescriptions;
```

---

## Form Issues
## Vấn đề Biểu mẫu

### Issue 6: Form Fields Not Saving
### Vấn đề 6: Trường Biểu mẫu Không Lưu

**English:**

**Symptoms:**
- Some fields save, others don't
- JSON fields (ROM measurements) not saving
- Bilingual fields only save English version

**Solutions:**

1. **Check Field Names Match Database:**
```sql
-- View table structure
DESCRIBE pt_assessments_bilingual;

-- Verify field names match form inputs
```

2. **Validate JSON Format:**
```javascript
// Ensure JSON fields are properly formatted
const romData = {
  "lumbar_flexion": 45,
  "lumbar_extension": 20
};

// Not:
const romData = "lumbar_flexion: 45";  // Wrong!
```

3. **Check Field Length Limits:**
```sql
-- Check maximum length for TEXT fields
SELECT
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'openemr'
  AND TABLE_NAME = 'pt_assessments_bilingual';
```

4. **Debug Form Submission:**
```javascript
// Add to form JavaScript
console.log('Submitting data:', formData);

// Check browser Network tab for POST data
```

---

**Tiếng Việt:**

**Triệu chứng:**
- Một số trường lưu được, số khác không
- Trường JSON (đo ROM) không lưu
- Trường song ngữ chỉ lưu phiên bản tiếng Anh

**Giải pháp:**

1. **Kiểm tra Tên Trường Khớp với Cơ sở Dữ liệu:**
```sql
-- Xem cấu trúc bảng
DESCRIBE pt_assessments_bilingual;

-- Xác minh tên trường khớp với input form
```

2. **Xác thực Định dạng JSON:**
```javascript
// Đảm bảo trường JSON được định dạng đúng
const romData = {
  "lumbar_flexion": 45,
  "lumbar_extension": 20
};

// Không:
const romData = "lumbar_flexion: 45";  // Sai!
```

---

## API Errors
## Lỗi API

### Issue 7: 401 Unauthorized Error
### Vấn đề 7: Lỗi 401 Unauthorized

**English:**

**Symptoms:**
- API returns: `{"error": "Unauthorized"}`
- OAuth2 token rejected
- Cannot access any API endpoints

**Solutions:**

1. **Verify Token is Valid:**
```bash
# Decode JWT token
echo "YOUR_TOKEN_HERE" | cut -d'.' -f2 | base64 -d | jq '.'

# Check expiration time (exp field)
```

2. **Get New Token:**
```bash
curl -X POST "https://your-domain/oauth2/default/token" \
  -d "grant_type=client_credentials" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "scope=api:oemr"
```

3. **Check OAuth2 Client Registration:**
```sql
-- Verify client exists
SELECT client_id, client_name, is_enabled
FROM oauth_clients
WHERE client_id = 'YOUR_CLIENT_ID';

-- Ensure is_enabled = 1
```

4. **Verify Authorization Header:**
```bash
# Correct format:
Authorization: Bearer YOUR_TOKEN

# NOT:
Authorization: YOUR_TOKEN  # Missing "Bearer"
Authorization: Bearer: YOUR_TOKEN  # Extra colon
```

---

**Tiếng Việt:**

**Triệu chứng:**
- API trả về: `{"error": "Unauthorized"}`
- Token OAuth2 bị từ chối
- Không thể truy cập endpoint API nào

**Giải pháp:**

1. **Xác minh Token Hợp lệ:**
```bash
# Giải mã JWT token
echo "YOUR_TOKEN_HERE" | cut -d'.' -f2 | base64 -d | jq '.'

# Kiểm tra thời gian hết hạn (trường exp)
```

2. **Lấy Token Mới:**
```bash
curl -X POST "https://your-domain/oauth2/default/token" \
  -d "grant_type=client_credentials" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "scope=api:oemr"
```

---

### Issue 8: 403 Forbidden Error
### Vấn đề 8: Lỗi 403 Forbidden

**English:**

**Symptoms:**
- API returns: `{"error": "Forbidden"}`
- Token is valid but access denied
- Some endpoints work, others return 403

**Solutions:**

1. **Check ACL Permissions:**
```sql
-- Check user ACL settings
SELECT
    u.username,
    ga.aco_section,
    ga.aco_name,
    ga.return_value
FROM users u
JOIN gacl_aro_groups_map gm ON u.id = gm.group_id
JOIN gacl_acl ga ON gm.group_id = ga.aro_group_id
WHERE u.username = 'YOUR_USERNAME'
  AND ga.aco_section = 'patients';
```

2. **Grant Required Permissions:**
- Log in as administrator
- Go to: Administration → ACL
- Find user/role
- Ensure "Patients - Medical Records" is enabled

3. **Verify Route ACL Requirements:**
```php
// Check route definition in _rest_routes_standard.inc.php
RestConfig::request_authorization_check($request, "patients", "med");

// Requires: patients section, med level
```

---

**Tiếng Việt:**

**Triệu chứng:**
- API trả về: `{"error": "Forbidden"}`
- Token hợp lệ nhưng truy cập bị từ chối
- Một số endpoint hoạt động, số khác trả về 403

**Giải pháp:**

1. **Kiểm tra Quyền ACL:**
```sql
-- Kiểm tra cài đặt ACL người dùng
SELECT
    u.username,
    ga.aco_section,
    ga.aco_name,
    ga.return_value
FROM users u
JOIN gacl_aro_groups_map gm ON u.id = gm.group_id
JOIN gacl_acl ga ON gm.group_id = ga.aro_group_id
WHERE u.username = 'YOUR_USERNAME'
  AND ga.aco_section = 'patients';
```

2. **Cấp Quyền Yêu cầu:**
- Đăng nhập với tư cách quản trị viên
- Đi đến: Administration → ACL
- Tìm người dùng/vai trò
- Đảm bảo "Patients - Medical Records" được kích hoạt

---

## Character Encoding Issues
## Vấn đề Mã hóa Ký tự

### Issue 9: Vietnamese Tones Display Incorrectly
### Vấn đề 9: Dấu Tiếng Việt Hiển thị Sai

**English:**

**Symptoms:**
- Tones appear as separate characters (a + ́ instead of á)
- Vietnamese text displays but looks "off"
- Sorting Vietnamese names shows incorrect order

**Solutions:**

1. **Check Unicode Normalization:**
```php
// Use NFC (Canonical Decomposition followed by Canonical Composition)
$text = Normalizer::normalize($input, Normalizer::FORM_C);
```

2. **Verify Database Collation:**
```sql
-- Check column collation
SELECT
    COLUMN_NAME,
    COLLATION_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'openemr'
  AND TABLE_NAME = 'pt_assessments_bilingual'
  AND COLUMN_NAME LIKE '%_vi';

-- Should all be: utf8mb4_vietnamese_ci
```

3. **Fix Web Server Headers:**
```apache
# Apache .htaccess
AddDefaultCharset UTF-8

# Or in VirtualHost:
AddDefaultCharset UTF-8
```

```nginx
# Nginx
charset utf-8;
```

---

**Tiếng Việt:**

**Triệu chứng:**
- Dấu xuất hiện như ký tự riêng biệt (a + ́ thay vì á)
- Văn bản tiếng Việt hiển thị nhưng trông "lạ"
- Sắp xếp tên tiếng Việt hiển thị thứ tự sai

**Giải pháp:**

1. **Kiểm tra Chuẩn hóa Unicode:**
```php
// Sử dụng NFC (Phân tách Chuẩn theo sau là Kết hợp Chuẩn)
$text = Normalizer::normalize($input, Normalizer::FORM_C);
```

2. **Xác minh Collation Cơ sở Dữ liệu:**
```sql
-- Kiểm tra collation cột
SELECT
    COLUMN_NAME,
    COLLATION_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'openemr'
  AND TABLE_NAME = 'pt_assessments_bilingual'
  AND COLUMN_NAME LIKE '%_vi';

-- Tất cả phải là: utf8mb4_vietnamese_ci
```

---

## Permission Problems
## Vấn đề Quyền truy cập

### Issue 10: Cannot Access Patient Data
### Vấn đề 10: Không thể Truy cập Dữ liệu Bệnh nhân

**English:**

**Symptoms:**
- "Access Denied" when opening patient record
- Can see patient list but not details
- Forms visible but cannot edit

**Solutions:**

1. **Check User Role:**
- Log in as administrator
- Go to: Administration → Users
- Verify user role has appropriate permissions

2. **Check ACL Settings:**
```sql
-- View user's ACL permissions
SELECT
    u.username,
    ga.aco_section,
    ga.aco_name,
    ga.return_value
FROM users u
JOIN gacl_aro_groups_map gm ON u.id = gm.group_id
JOIN gacl_acl ga ON gm.group_id = ga.aro_group_id
WHERE u.username = 'YOUR_USERNAME'
ORDER BY ga.aco_section, ga.aco_name;
```

3. **Verify Facility Permissions:**
- Check user is assigned to correct facility
- Go to: Administration → Users → Edit User
- Verify "Default Facility" is set

---

**Tiếng Việt:**

**Triệu chứng:**
- "Truy cập Bị từ chối" khi mở hồ sơ bệnh nhân
- Có thể xem danh sách bệnh nhân nhưng không xem chi tiết
- Biểu mẫu hiển thị nhưng không thể chỉnh sửa

**Giải pháp:**

1. **Kiểm tra Vai trò Người dùng:**
- Đăng nhập với tư cách quản trị viên
- Đi đến: Administration → Users
- Xác minh vai trò người dùng có quyền phù hợp

2. **Kiểm tra Cài đặt ACL:**
```sql
-- Xem quyền ACL của người dùng
SELECT
    u.username,
    ga.aco_section,
    ga.aco_name,
    ga.return_value
FROM users u
JOIN gacl_aro_groups_map gm ON u.id = gm.group_id
JOIN gacl_acl ga ON gm.group_id = ga.aro_group_id
WHERE u.username = 'YOUR_USERNAME'
ORDER BY ga.aco_section, ga.aco_name;
```

---

## Performance Issues
## Vấn đề Hiệu suất

### Issue 11: Slow Form Loading
### Vấn đề 11: Biểu mẫu Tải Chậm

**English:**

**Symptoms:**
- Forms take >10 seconds to load
- Browser becomes unresponsive
- "Loading..." spinner stays visible

**Solutions:**

1. **Check Browser Console:**
- Open developer tools (F12)
- Look for JavaScript errors
- Check Network tab for slow requests

2. **Optimize Database Queries:**
```sql
-- Add missing indexes
CREATE INDEX idx_composite ON pt_assessments_bilingual(patient_id, encounter_id, assessment_date);

-- Analyze query performance
EXPLAIN SELECT * FROM pt_assessments_bilingual WHERE patient_id = 123;
```

3. **Enable PHP Opcache:**
```ini
# php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
```

4. **Clear Browser Cache:**
- Chrome: Ctrl+Shift+Delete
- Firefox: Ctrl+Shift+Delete
- Select "Cached images and files"
- Click "Clear data"

---

**Tiếng Việt:**

**Triệu chứng:**
- Biểu mẫu mất >10 giây để tải
- Trình duyệt không phản hồi
- Biểu tượng "Đang tải..." hiển thị mãi

**Giải pháp:**

1. **Kiểm tra Console Trình duyệt:**
- Mở công cụ nhà phát triển (F12)
- Tìm lỗi JavaScript
- Kiểm tra tab Network cho yêu cầu chậm

2. **Tối ưu hóa Truy vấn Cơ sở Dữ liệu:**
```sql
-- Thêm chỉ mục thiếu
CREATE INDEX idx_composite ON pt_assessments_bilingual(patient_id, encounter_id, assessment_date);

-- Phân tích hiệu suất truy vấn
EXPLAIN SELECT * FROM pt_assessments_bilingual WHERE patient_id = 123;
```

---

## Getting Support
## Nhận Hỗ trợ

### English

**Before Requesting Support:**

1. **Gather Information:**
   - OpenEMR version: `Admin → About → Version`
   - PHP version: `php -v`
   - MariaDB version: `mysql -V`
   - Error messages (copy exact text)
   - Screenshots of issue

2. **Check Log Files:**
```bash
# Application log
sudo tail -n 100 /var/log/openemr/application.log

# PHP error log
sudo tail -n 100 /var/log/php8.2-fpm.log

# Database log
sudo tail -n 100 /var/log/mysql/error.log

# Web server log
sudo tail -n 100 /var/log/apache2/error.log
```

3. **Review Documentation:**
   - [Architecture Documentation](../technical/ARCHITECTURE.md)
   - [Database Schema](../technical/DATABASE_SCHEMA.md)
   - [API Reference](../development/API_REFERENCE.md)
   - [Installation Guide](../technical/INSTALLATION.md)

**Contact Support:**

- **Developer Contact:** Dang Tran <tqvdang@msn.com>
- **GitHub Issues:** https://github.com/your-repo/openemr-vietnamese-pt/issues
- **OpenEMR Forums:** https://community.open-emr.org/

**Include in Support Request:**
- System information (versions)
- Exact error messages
- Steps to reproduce issue
- Screenshots or screen recordings
- What you've already tried

---

### Tiếng Việt

**Trước khi Yêu cầu Hỗ trợ:**

1. **Thu thập Thông tin:**
   - Phiên bản OpenEMR: `Admin → About → Version`
   - Phiên bản PHP: `php -v`
   - Phiên bản MariaDB: `mysql -V`
   - Thông báo lỗi (sao chép văn bản chính xác)
   - Ảnh chụp màn hình sự cố

2. **Kiểm tra File Log:**
```bash
# Log ứng dụng
sudo tail -n 100 /var/log/openemr/application.log

# Log lỗi PHP
sudo tail -n 100 /var/log/php8.2-fpm.log

# Log cơ sở dữ liệu
sudo tail -n 100 /var/log/mysql/error.log

# Log web server
sudo tail -n 100 /var/log/apache2/error.log
```

3. **Xem lại Tài liệu:**
   - [Tài liệu Kiến trúc](../technical/ARCHITECTURE.md)
   - [Lược đồ Cơ sở Dữ liệu](../technical/DATABASE_SCHEMA.md)
   - [Tài liệu API](../development/API_REFERENCE.md)
   - [Hướng dẫn Cài đặt](../technical/INSTALLATION.md)

**Liên hệ Hỗ trợ:**

- **Liên hệ Nhà phát triển:** Dang Tran <tqvdang@msn.com>
- **GitHub Issues:** https://github.com/your-repo/openemr-vietnamese-pt/issues
- **Diễn đàn OpenEMR:** https://community.open-emr.org/

**Bao gồm trong Yêu cầu Hỗ trợ:**
- Thông tin hệ thống (phiên bản)
- Thông báo lỗi chính xác
- Các bước để tái tạo sự cố
- Ảnh chụp màn hình hoặc video màn hình
- Những gì bạn đã thử

---

## Common Error Messages
## Thông báo Lỗi Thường gặp

| Error Code | English Message | Vietnamese Message | Solution |
|------------|-----------------|-------------------|----------|
| 400 | Bad Request - Validation failed | Yêu cầu không hợp lệ - Xác thực thất bại | Check required fields |
| 401 | Unauthorized - Invalid token | Không được phép - Token không hợp lệ | Get new OAuth2 token |
| 403 | Forbidden - Access denied | Cấm - Truy cập bị từ chối | Check ACL permissions |
| 404 | Not Found - Resource not found | Không tìm thấy - Tài nguyên không tồn tại | Verify ID exists |
| 500 | Internal Server Error | Lỗi Máy chủ Nội bộ | Check server logs |

---

**Troubleshooting Guide Version:** 1.0
**Phiên bản Hướng dẫn Khắc phục Sự cố:** 1.0
**Last Updated / Cập nhật:** 2025-11-22
**Maintainer / Người duy trì:** Dang Tran <tqvdang@msn.com>
