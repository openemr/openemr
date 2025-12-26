# Vietnamese PT Module - Production Deployment Guide

**Version:** 1.0
**Last Updated:** 2025-11-22
**Target Environment:** Linux (Ubuntu 22.04 LTS / CentOS 8+)

## Table of Contents

- [Overview](#overview)
- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [System Requirements](#system-requirements)
- [Deployment Steps](#deployment-steps)
- [Database Setup](#database-setup)
- [File Deployment](#file-deployment)
- [Configuration](#configuration)
- [Post-Deployment Verification](#post-deployment-verification)
- [Rollback Procedures](#rollback-procedures)
- [Troubleshooting](#troubleshooting)
- [Security Hardening](#security-hardening)
- [Performance Tuning](#performance-tuning)
- [Monitoring](#monitoring)

---

## Overview

This guide covers deploying the Vietnamese PT Module to a production OpenEMR instance. The deployment process follows a phased approach with comprehensive verification at each step.

### Deployment Philosophy

1. **Zero Downtime**: Use database migrations and blue-green deployment where possible
2. **Rollback Ready**: Always maintain ability to rollback
3. **Verification First**: Test each component before proceeding
4. **Security First**: Never compromise security for speed

---

## Pre-Deployment Checklist

### Planning Phase

- [ ] Review [ARCHITECTURE.md](./ARCHITECTURE.md) and [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md)
- [ ] Schedule maintenance window (recommended: 2-4 hours)
- [ ] Notify stakeholders of deployment timeline
- [ ] Prepare rollback plan
- [ ] Review change log and known issues

### Environment Verification

- [ ] Verify OpenEMR version 7.0.0+ is installed
- [ ] Verify PHP 8.2+ is installed
- [ ] Verify MariaDB 10.11+ is installed and running
- [ ] Verify sufficient disk space (minimum 5GB free)
- [ ] Verify backup system is operational
- [ ] Test restore from backup

### Backup Requirements

- [ ] Full database backup completed
- [ ] File system backup completed
- [ ] Backup verification completed
- [ ] Backup stored off-site
- [ ] Document backup timestamps and locations

### Team Readiness

- [ ] Database administrator available
- [ ] System administrator available
- [ ] PT module developer available (if needed)
- [ ] On-call support team notified

---

## System Requirements

### Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **CPU** | 4 cores | 8+ cores |
| **RAM** | 8 GB | 16+ GB |
| **Storage** | 50 GB | 100+ GB SSD |
| **Network** | 100 Mbps | 1 Gbps |

### Software Requirements

| Software | Version | Notes |
|----------|---------|-------|
| **OpenEMR** | 7.0.0+ | Must be fully functional |
| **PHP** | 8.2+ | With required extensions |
| **MariaDB** | 10.11+ | With Vietnamese collation support |
| **Web Server** | Apache 2.4+ or Nginx 1.20+ | With SSL/TLS |
| **Redis** | 6.0+ (optional) | For caching |

### PHP Extensions Required

```bash
# Verify PHP extensions
php -m | grep -E "mbstring|json|pdo|pdo_mysql|curl|zip|xml|gd|intl"

# Required extensions:
- mbstring (for Vietnamese character handling)
- json (for JSON field support)
- pdo_mysql (for database access)
- curl (for API functionality)
- intl (for internationalization)
```

### Database Configuration

```ini
# my.cnf or mariadb.conf.d/50-server.cnf

[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_vietnamese_ci
default_time_zone = '+07:00'  # Vietnam timezone

# Performance settings
innodb_buffer_pool_size = 2G  # 50-70% of available RAM
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 2
innodb_file_per_table = 1

# Full-text search
ft_min_word_len = 2
```

---

## Deployment Steps

### Phase 1: Preparation (30 minutes)

#### 1.1 Create Deployment Directory

```bash
# Create deployment workspace
sudo mkdir -p /opt/deployment/vietnamese-pt
cd /opt/deployment/vietnamese-pt

# Set proper ownership
sudo chown -R www-data:www-data /opt/deployment/vietnamese-pt
```

#### 1.2 Download Module Files

```bash
# Clone repository or download archive
git clone https://github.com/your-repo/openemr-vietnamese-pt.git
# OR
wget https://your-server/vietnamese-pt-module-v1.0.tar.gz
tar -xzf vietnamese-pt-module-v1.0.tar.gz
```

#### 1.3 Verify File Integrity

```bash
# Verify checksums
sha256sum -c checksums.txt

# Count files to deploy
find . -type f | wc -l
```

---

### Phase 2: Database Deployment (45 minutes)

#### 2.1 Backup Current Database

```bash
# Full database backup
mysqldump -u root -p \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  openemr > /backup/openemr_pre_pt_$(date +%Y%m%d_%H%M%S).sql

# Compress backup
gzip /backup/openemr_pre_pt_*.sql

# Verify backup
zcat /backup/openemr_pre_pt_*.sql.gz | head -n 20
```

#### 2.2 Test Database Connection

```bash
# Test connection
mysql -u openemr -p -e "SELECT VERSION(); SHOW VARIABLES LIKE 'character_set%';"

# Verify Vietnamese collation support
mysql -u openemr -p -e "SHOW COLLATION LIKE 'utf8mb4_vietnamese%';"
```

#### 2.3 Import PT Schema

```bash
# Import bilingual schema
mysql -u openemr -p openemr < sql/02-pt-bilingual-schema.sql

# Verify tables created
mysql -u openemr -p openemr -e "SHOW TABLES LIKE 'pt_%'; SHOW TABLES LIKE 'vietnamese_%';"

# Expected output:
# pt_assessments_bilingual
# pt_exercise_prescriptions
# pt_treatment_plans
# pt_outcome_measures
# pt_treatment_sessions
# pt_assessment_templates
# vietnamese_medical_terms
# vietnamese_insurance_info
```

#### 2.4 Import Stored Functions

```bash
# Import translation functions
mysql -u openemr -p openemr < sql/vietnamese_pt_functions.sql

# Verify functions created
mysql -u openemr -p openemr -e "SHOW FUNCTION STATUS WHERE Db = 'openemr' AND Name LIKE '%vietnamese%';"

# Test functions
mysql -u openemr -p openemr -e "SELECT get_vietnamese_term('pain') AS test;"
```

#### 2.5 Import Medical Terms

```bash
# Import medical terminology
mysql -u openemr -p openemr < sql/04-physiotherapy-extensions.sql

# Verify terms loaded
mysql -u openemr -p openemr -e "SELECT COUNT(*) FROM vietnamese_medical_terms;"

# Should return: 52+ terms
```

#### 2.6 Create Database Views

```bash
# Views should be created by schema import
# Verify view exists
mysql -u openemr -p openemr -e "SHOW CREATE VIEW pt_patient_summary_bilingual\G"
```

---

### Phase 3: Application File Deployment (30 minutes)

#### 3.1 Deploy Service Classes

```bash
# Navigate to OpenEMR root
cd /var/www/html/openemr

# Backup existing src directory
sudo cp -r src src.backup.$(date +%Y%m%d_%H%M%S)

# Deploy service classes
sudo cp -r /opt/deployment/vietnamese-pt/src/Services/VietnamesePT src/Services/
sudo cp -r /opt/deployment/vietnamese-pt/src/Validators/VietnamesePT src/Validators/
sudo cp -r /opt/deployment/vietnamese-pt/src/RestControllers/VietnamesePT src/RestControllers/

# Verify deployment
ls -la src/Services/VietnamesePT/
ls -la src/Validators/VietnamesePT/
ls -la src/RestControllers/VietnamesePT/

# Set permissions
sudo chown -R www-data:www-data src/Services/VietnamesePT
sudo chown -R www-data:www-data src/Validators/VietnamesePT
sudo chown -R www-data:www-data src/RestControllers/VietnamesePT
sudo chmod -R 755 src/Services/VietnamesePT
sudo chmod -R 755 src/Validators/VietnamesePT
sudo chmod -R 755 src/RestControllers/VietnamesePT
```

#### 3.2 Deploy Form Modules

```bash
# Deploy form directories
sudo cp -r /opt/deployment/vietnamese-pt/interface/forms/vietnamese_pt_* interface/forms/

# Verify forms
ls -la interface/forms/vietnamese_pt_*/

# Expected directories:
# vietnamese_pt_assessment
# vietnamese_pt_exercise
# vietnamese_pt_treatment_plan
# vietnamese_pt_outcome

# Set permissions
sudo chown -R www-data:www-data interface/forms/vietnamese_pt_*
sudo chmod -R 755 interface/forms/vietnamese_pt_*
```

#### 3.3 Deploy Custom Widget

```bash
# Deploy patient summary widget
sudo cp /opt/deployment/vietnamese-pt/library/custom/vietnamese_pt_widget.php library/custom/

# Set permissions
sudo chown www-data:www-data library/custom/vietnamese_pt_widget.php
sudo chmod 644 library/custom/vietnamese_pt_widget.php
```

#### 3.4 Update API Routes

```bash
# Backup existing routes file
sudo cp apis/routes/_rest_routes_standard.inc.php \
   apis/routes/_rest_routes_standard.inc.php.backup.$(date +%Y%m%d_%H%M%S)

# Deploy updated routes file
sudo cp /opt/deployment/vietnamese-pt/apis/routes/_rest_routes_standard.inc.php \
   apis/routes/_rest_routes_standard.inc.php

# Verify routes added
grep -n "vietnamese-pt" apis/routes/_rest_routes_standard.inc.php | head -5

# Set permissions
sudo chown www-data:www-data apis/routes/_rest_routes_standard.inc.php
sudo chmod 644 apis/routes/_rest_routes_standard.inc.php
```

---

### Phase 4: Composer Autoload (10 minutes)

#### 4.1 Update Composer Autoload

```bash
# Regenerate autoload files
sudo -u www-data composer dump-autoload -o

# Verify classes are autoloadable
sudo -u www-data php -r "
require 'vendor/autoload.php';
\$service = new OpenEMR\Services\VietnamesePT\PTAssessmentService();
echo 'PTAssessmentService loaded successfully\n';
"
```

---

### Phase 5: Configuration (15 minutes)

#### 5.1 Register Forms in Database

```bash
# Register PT forms
mysql -u openemr -p openemr << 'EOF'
INSERT INTO registry (
    name,
    directory,
    sql_run,
    unpackaged,
    state,
    category,
    nickname
) VALUES
('Vietnamese PT Assessment', 'vietnamese_pt_assessment', 1, 1, 1, 'Clinical', 'PT Assessment'),
('Vietnamese PT Exercise', 'vietnamese_pt_exercise', 1, 1, 1, 'Clinical', 'PT Exercise'),
('Vietnamese PT Treatment Plan', 'vietnamese_pt_treatment_plan', 1, 1, 1, 'Clinical', 'PT Plan'),
('Vietnamese PT Outcome', 'vietnamese_pt_outcome', 1, 1, 1, 'Clinical', 'PT Outcome')
ON DUPLICATE KEY UPDATE state = 1;
EOF

# Verify registration
mysql -u openemr -p openemr -e "SELECT * FROM registry WHERE directory LIKE 'vietnamese_pt%';"
```

#### 5.2 Configure ACL Permissions

```bash
# ACL should already be configured for "patients/med"
# Verify ACL configuration
mysql -u openemr -p openemr -e "
SELECT aco_section, aco_name, return_value
FROM gacl_aco
WHERE aco_section = 'patients';
"
```

#### 5.3 Clear Application Cache

```bash
# Clear PHP opcache
sudo systemctl restart php8.2-fpm

# Clear Redis cache (if using)
redis-cli FLUSHALL

# Clear OpenEMR cache
sudo rm -rf sites/default/documents/smarty/cache/*
sudo rm -rf sites/default/documents/smarty/main/*
```

---

## Post-Deployment Verification

### Verification Checklist

#### 6.1 Database Verification

```bash
# Verify all tables exist
mysql -u openemr -p openemr << 'EOF'
SELECT
    TABLE_NAME,
    TABLE_ROWS,
    TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'openemr'
  AND TABLE_NAME LIKE 'pt_%'
  OR TABLE_NAME LIKE 'vietnamese_%';
EOF

# Verify stored functions
mysql -u openemr -p openemr -e "
SELECT get_vietnamese_term('pain') AS vietnamese,
       get_english_term('đau') AS english;
"

# Expected: vietnamese = đau, english = pain
```

#### 6.2 API Endpoint Verification

```bash
# Get OAuth2 token (replace with your credentials)
TOKEN=$(curl -X POST "https://your-domain/oauth2/default/token" \
  -d "grant_type=client_credentials" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "scope=api:oemr" | jq -r '.access_token')

# Test PT assessments endpoint
curl -X GET "https://your-domain/apis/default/api/vietnamese-pt/assessments" \
  -H "Authorization: Bearer $TOKEN" | jq '.'

# Test medical terms endpoint
curl -X GET "https://your-domain/apis/default/api/vietnamese-pt/medical-terms" \
  -H "Authorization: Bearer $TOKEN" | jq '.'

# Expected: HTTP 200 with JSON response
```

#### 6.3 Form Access Verification

```bash
# Log in to OpenEMR web interface
# Navigate to: Patient → Encounter → Add Form
# Verify forms appear:
# - Vietnamese PT Assessment
# - Vietnamese PT Exercise
# - Vietnamese PT Treatment Plan
# - Vietnamese PT Outcome
```

#### 6.4 Create Test Assessment

```bash
# Create test assessment via API
curl -X POST "https://your-domain/apis/default/api/vietnamese-pt/assessments" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "patient_id": 1,
    "encounter_id": 1,
    "assessment_date": "2025-11-22 10:00:00",
    "chief_complaint_en": "Test complaint",
    "chief_complaint_vi": "Triệu chứng thử nghiệm",
    "pain_level": 5,
    "language_preference": "vi",
    "status": "draft"
  }' | jq '.'

# Verify record created
mysql -u openemr -p openemr -e "
SELECT id, patient_id, chief_complaint_vi, pain_level, status
FROM pt_assessments_bilingual
ORDER BY id DESC LIMIT 1;
"
```

#### 6.5 Performance Verification

```bash
# Test response time
time curl -X GET "https://your-domain/apis/default/api/vietnamese-pt/assessments" \
  -H "Authorization: Bearer $TOKEN" > /dev/null

# Expected: < 1 second for small datasets
```

---

## Rollback Procedures

### Emergency Rollback

If critical issues occur, follow these steps:

#### 1. Stop Web Server

```bash
sudo systemctl stop apache2
# OR
sudo systemctl stop nginx
```

#### 2. Restore Database

```bash
# Restore from backup
zcat /backup/openemr_pre_pt_*.sql.gz | mysql -u root -p openemr

# Verify restoration
mysql -u openemr -p openemr -e "SHOW TABLES LIKE 'pt_%';"
# Should show no PT tables
```

#### 3. Restore Application Files

```bash
# Remove deployed files
sudo rm -rf src/Services/VietnamesePT
sudo rm -rf src/Validators/VietnamesePT
sudo rm -rf src/RestControllers/VietnamesePT
sudo rm -rf interface/forms/vietnamese_pt_*
sudo rm -f library/custom/vietnamese_pt_widget.php

# Restore routes file
sudo cp apis/routes/_rest_routes_standard.inc.php.backup.* \
   apis/routes/_rest_routes_standard.inc.php

# Regenerate autoload
sudo -u www-data composer dump-autoload -o
```

#### 4. Clear Cache and Restart

```bash
# Clear cache
sudo rm -rf sites/default/documents/smarty/cache/*
sudo systemctl restart php8.2-fpm
redis-cli FLUSHALL

# Restart web server
sudo systemctl start apache2
```

#### 5. Verify Rollback

```bash
# Verify PT tables removed
mysql -u openemr -p openemr -e "SHOW TABLES LIKE 'pt_%';"

# Verify API returns 404
curl -X GET "https://your-domain/apis/default/api/vietnamese-pt/assessments" \
  -H "Authorization: Bearer $TOKEN"

# Expected: 404 Not Found
```

---

## Troubleshooting

### Common Issues and Solutions

#### Issue 1: Database Import Fails

**Symptom:** Error during schema import

**Solutions:**
```bash
# Check database version
mysql -V

# Verify Vietnamese collation support
mysql -u openemr -p -e "SHOW COLLATION LIKE 'utf8mb4_vietnamese%';"

# If collation missing, upgrade MariaDB to 10.11+
sudo apt update
sudo apt install mariadb-server-10.11
```

#### Issue 2: Autoload Class Not Found

**Symptom:** `Class 'OpenEMR\Services\VietnamesePT\PTAssessmentService' not found`

**Solutions:**
```bash
# Verify files exist
ls -la src/Services/VietnamesePT/PTAssessmentService.php

# Regenerate autoload
sudo -u www-data composer dump-autoload -o

# Check namespace in file
head -20 src/Services/VietnamesePT/PTAssessmentService.php
```

#### Issue 3: API Returns 403 Forbidden

**Symptom:** API endpoints return 403 even with valid token

**Solutions:**
```bash
# Check ACL permissions
mysql -u openemr -p openemr << 'EOF'
SELECT u.username, ga.aco_section, ga.aco_name
FROM users u
JOIN gacl_aro_groups_map gm ON u.id = gm.group_id
JOIN gacl_acl ga ON gm.group_id = ga.aro_group_id
WHERE u.username = 'admin' AND ga.aco_section = 'patients';
EOF

# Ensure user has "patients/med" permission
```

#### Issue 4: Vietnamese Characters Display as ???

**Symptom:** Vietnamese characters show as question marks

**Solutions:**
```bash
# Check table collation
mysql -u openemr -p openemr -e "
SHOW CREATE TABLE pt_assessments_bilingual\G
"

# Verify utf8mb4_vietnamese_ci is set
# If not, convert:
mysql -u openemr -p openemr << 'EOF'
ALTER TABLE pt_assessments_bilingual
CONVERT TO CHARACTER SET utf8mb4
COLLATE utf8mb4_vietnamese_ci;
EOF
```

#### Issue 5: Forms Don't Appear in Add Form Menu

**Symptom:** PT forms not listed in encounter forms

**Solutions:**
```bash
# Check registry table
mysql -u openemr -p openemr -e "
SELECT name, directory, state
FROM registry
WHERE directory LIKE 'vietnamese_pt%';
"

# Ensure state = 1 (active)
# If state = 0, update:
mysql -u openemr -p openemr -e "
UPDATE registry
SET state = 1
WHERE directory LIKE 'vietnamese_pt%';
"
```

---

## Security Hardening

### File Permissions

```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/html/openemr

# Set directory permissions
find /var/www/html/openemr -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/html/openemr -type f -exec chmod 644 {} \;

# Restrict sensitive directories
chmod 750 /var/www/html/openemr/sites/default/documents
chmod 640 /var/www/html/openemr/sites/default/sqlconf.php
```

### Database Security

```bash
# Create dedicated PT module database user
mysql -u root -p << 'EOF'
CREATE USER 'pt_module'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE ON openemr.pt_* TO 'pt_module'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON openemr.vietnamese_* TO 'pt_module'@'localhost';
GRANT EXECUTE ON FUNCTION openemr.get_vietnamese_term TO 'pt_module'@'localhost';
GRANT EXECUTE ON FUNCTION openemr.get_english_term TO 'pt_module'@'localhost';
FLUSH PRIVILEGES;
EOF
```

### SSL/TLS Configuration

```nginx
# Nginx SSL configuration
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /etc/ssl/certs/your-cert.crt;
    ssl_certificate_key /etc/ssl/private/your-key.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # ... rest of config
}
```

### API Rate Limiting

```nginx
# Nginx rate limiting for API
limit_req_zone $binary_remote_addr zone=api_limit:10m rate=10r/s;

location /apis/default/api/vietnamese-pt/ {
    limit_req zone=api_limit burst=20 nodelay;
    # ... rest of config
}
```

---

## Performance Tuning

### Database Optimization

```sql
-- Analyze tables
ANALYZE TABLE pt_assessments_bilingual;
ANALYZE TABLE pt_exercise_prescriptions;
ANALYZE TABLE pt_treatment_plans;
ANALYZE TABLE pt_outcome_measures;

-- Optimize tables
OPTIMIZE TABLE pt_assessments_bilingual;
OPTIMIZE TABLE pt_exercise_prescriptions;
```

### Query Performance Monitoring

```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
SET GLOBAL slow_query_log_file = '/var/log/mysql/slow-query.log';

-- Monitor slow queries
SELECT * FROM mysql.slow_log
WHERE sql_text LIKE '%pt_%'
ORDER BY query_time DESC
LIMIT 10;
```

### PHP Optimization

```ini
# php.ini optimizations
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.revalidate_freq=60

# Increase memory limit for PT module
memory_limit = 512M
```

### Caching Strategy

```bash
# Configure Redis for PT module
redis-cli CONFIG SET maxmemory 2gb
redis-cli CONFIG SET maxmemory-policy allkeys-lru

# Cache medical terms
redis-cli HSET medical_terms "pain" "đau"
```

---

## Monitoring

### Log Monitoring

```bash
# Monitor application logs
tail -f /var/log/openemr/application.log | grep -i vietnamese

# Monitor database logs
tail -f /var/log/mysql/error.log

# Monitor web server logs
tail -f /var/log/apache2/error.log
```

### Health Check Script

```bash
#!/bin/bash
# pt_module_health_check.sh

# Check database tables
TABLE_COUNT=$(mysql -u openemr -p$DB_PASSWORD openemr -sN -e "
SELECT COUNT(*) FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'openemr' AND TABLE_NAME LIKE 'pt_%';
")

if [ "$TABLE_COUNT" -ne 8 ]; then
    echo "ERROR: Expected 8 PT tables, found $TABLE_COUNT"
    exit 1
fi

# Check API endpoint
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Authorization: Bearer $TOKEN" \
    "https://your-domain/apis/default/api/vietnamese-pt/medical-terms")

if [ "$HTTP_CODE" -ne 200 ]; then
    echo "ERROR: API endpoint returned $HTTP_CODE"
    exit 1
fi

echo "Health check passed"
exit 0
```

### Performance Metrics

```bash
# Monitor API response times
ab -n 1000 -c 10 -H "Authorization: Bearer $TOKEN" \
   https://your-domain/apis/default/api/vietnamese-pt/assessments

# Monitor database performance
mysqladmin -u root -p -i 5 extended-status | grep -E "Threads_connected|Questions|Slow_queries"
```

---

## Maintenance

### Regular Maintenance Tasks

```bash
# Weekly: Analyze and optimize tables
mysql -u openemr -p openemr << 'EOF'
ANALYZE TABLE pt_assessments_bilingual, pt_exercise_prescriptions;
OPTIMIZE TABLE pt_assessments_bilingual, pt_exercise_prescriptions;
EOF

# Monthly: Review slow queries
grep "vietnamese_pt\|pt_" /var/log/mysql/slow-query.log

# Quarterly: Archive old assessments
mysql -u openemr -p openemr << 'EOF'
CREATE TABLE pt_assessments_bilingual_archive LIKE pt_assessments_bilingual;
INSERT INTO pt_assessments_bilingual_archive
SELECT * FROM pt_assessments_bilingual
WHERE assessment_date < DATE_SUB(NOW(), INTERVAL 2 YEAR);
EOF
```

---

## Related Documentation

- **[Architecture](./ARCHITECTURE.md)** - System architecture
- **[Database Schema](./DATABASE_SCHEMA.md)** - Database structure
- **[API Reference](../development/API_REFERENCE.md)** - API documentation
- **[Troubleshooting](../user-guides/TROUBLESHOOTING.md)** - User troubleshooting guide

---

**Deployment Guide Version:** 1.0
**Last Updated:** 2025-11-22
**Maintainer:** Dang Tran <tqvdang@msn.com>
