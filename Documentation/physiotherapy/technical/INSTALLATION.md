# OpenEMR Physiotherapy Module Installation Guide

**System Administrator Guide for Vietnamese Physiotherapy Module**  
*Technical Installation Documentation*

## 🎯 Overview

This guide provides step-by-step instructions for installing the OpenEMR Physiotherapy Module with Vietnamese bilingual support on an existing OpenEMR installation.

## 📋 Prerequisites

### System Requirements
- **OpenEMR Version**: 7.0.0 or higher
- **PHP Version**: 7.4+ (8.0+ recommended)
- **Database**: MariaDB 10.4+ or MySQL 8.0+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Operating System**: Linux (Ubuntu 20.04+ recommended), Windows, or macOS

### Required PHP Extensions
```bash
# Verify required extensions are installed
php -m | grep -E "(mysqli|pdo_mysql|redis|mbstring|openssl|json|curl|gd|zip|xml|intl)"
```

### Database Requirements
- **Character Set**: UTF-8mb4 support required
- **Collation**: utf8mb4_vietnamese_ci support
- **Storage**: Additional 50-100MB for physiotherapy data
- **Permissions**: Database user with CREATE, ALTER, INSERT, UPDATE, DELETE privileges

### Network Requirements
- **Internet Access**: Required for initial setup and updates
- **Port Access**: Standard OpenEMR ports (80, 443)
- **SSL Certificate**: Recommended for production environments

## 🚀 Installation Methods

### Method 1: Development Environment (Recommended for Testing)
Use the hybrid Docker development environment for testing and development.

### Method 2: Production Environment
Install directly on existing OpenEMR production system.

## 🐳 Development Installation (Docker)

### Step 1: Clone Repository
```bash
# Navigate to OpenEMR directory
cd /path/to/openemr

# Ensure you're on the physiotherapy branch
git checkout physio-bilingual-docker-hybrid

# Verify branch
git branch --show-current
```

### Step 2: Start Development Environment
```bash
# Navigate to development environment
cd docker/development-physiotherapy

# Start all services
./scripts/start-dev.sh
```

### Step 3: Verify Installation
```bash
# Check service status
docker-compose ps

# Verify database connection
docker-compose exec mariadb mysql -uopenemr -p -e "SHOW DATABASES;"

# Test Vietnamese character support
docker-compose exec mariadb mysql -uopenemr -p openemr -e "SELECT * FROM vietnamese_test LIMIT 5;"
```

## 🏢 Production Installation

### Step 1: Backup Existing System
```bash
# Backup OpenEMR files
tar -czf openemr-backup-$(date +%Y%m%d).tar.gz /path/to/openemr

# Backup database
mysqldump -u root -p openemr > openemr-backup-$(date +%Y%m%d).sql
```

### Step 2: Database Preparation
```sql
-- Connect to database as admin user
mysql -u root -p

-- Verify character set support
SHOW VARIABLES LIKE 'character_set%';
SHOW VARIABLES LIKE 'collation%';

-- Set Vietnamese collation (if needed)
ALTER DATABASE openemr CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci;
```

### Step 3: Install Physiotherapy Module Files
```bash
# Navigate to OpenEMR directory
cd /path/to/openemr

# Copy physiotherapy module files (assuming they're prepared)
# This would typically involve copying custom modules/interfaces/libraries

# Set proper permissions
chown -R www-data:www-data /path/to/openemr/interface/physiotherapy
chown -R www-data:www-data /path/to/openemr/library/physiotherapy
chmod -R 755 /path/to/openemr/interface/physiotherapy
```

### Step 4: Database Schema Installation
```bash
# Run physiotherapy database installation script
mysql -u openemr -p openemr < sql/physiotherapy/install.sql

# Or if using the docker initialization files:
mysql -u openemr -p openemr < docker/development-physiotherapy/configs/mariadb/init/01-vietnamese-setup.sql
mysql -u openemr -p openemr < docker/development-physiotherapy/configs/mariadb/init/02-physiotherapy-extensions.sql
```

### Step 5: OpenEMR Configuration Updates
```php
// Add to OpenEMR's globals.php or configuration file
$GLOBALS['physiotherapy_module_enabled'] = true;
$GLOBALS['vietnamese_localization'] = true;
$GLOBALS['default_language'] = 'Vietnamese';
$GLOBALS['physiotherapy_default_timezone'] = 'Asia/Ho_Chi_Minh';
```

### Step 6: Web Server Configuration

#### Apache Configuration
```apache
# Add to OpenEMR virtual host
<Directory "/path/to/openemr/interface/physiotherapy">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
    
    # Vietnamese character support
    AddDefaultCharset UTF-8
    
    # PHP settings for physiotherapy module
    php_admin_value memory_limit 256M
    php_admin_value max_execution_time 300
</Directory>
```

#### Nginx Configuration
```nginx
# Add to OpenEMR server block
location /interface/physiotherapy {
    try_files $uri $uri/ /interface/physiotherapy/index.php?$args;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Vietnamese character support
        fastcgi_param PHP_VALUE "default_charset=UTF-8";
    }
}
```

## ⚙️ Configuration

### Step 1: Database Configuration
```bash
# Verify Vietnamese support
mysql -u openemr -p openemr -e "
SELECT 
    DEFAULT_CHARACTER_SET_NAME,
    DEFAULT_COLLATION_NAME 
FROM information_schema.SCHEMATA 
WHERE SCHEMA_NAME='openemr';"
```

### Step 2: Language Configuration
```php
// In OpenEMR configuration
$GLOBALS['language_default'] = 'Vietnamese';
$GLOBALS['language_menu_showall'] = true;
$GLOBALS['translate_layout'] = true;
$GLOBALS['translate_lists'] = true;
$GLOBALS['translate_gacl_groups'] = true;
```

### Step 3: Physiotherapy Module Configuration
```php
// Physiotherapy-specific settings
$GLOBALS['pt_assessment_auto_save'] = true;
$GLOBALS['pt_exercise_library_default'] = 'vietnamese';
$GLOBALS['pt_outcome_measures_required'] = true;
$GLOBALS['pt_billing_integration'] = true;
```

### Step 4: User Permissions Setup
```sql
-- Add physiotherapy permissions to ACL
INSERT INTO gacl_aro_sections (value, name) VALUES ('physiotherapy', 'Physiotherapy');

-- Add physiotherapy ACOs
INSERT INTO gacl_aco_sections (value, name) VALUES ('physiotherapy', 'Physiotherapy');

-- Add specific permissions
INSERT INTO gacl_aco (section_value, value, name) VALUES 
('physiotherapy', 'assessments', 'Physiotherapy Assessments'),
('physiotherapy', 'exercises', 'Exercise Prescriptions'),  
('physiotherapy', 'outcomes', 'Outcome Measures'),
('physiotherapy', 'reports', 'Physiotherapy Reports');
```

## 🧪 Testing Installation

### Step 1: Basic Functionality Test
```bash
# Test database connectivity
php -r "
\$pdo = new PDO('mysql:host=localhost;dbname=openemr;charset=utf8mb4', 'openemr', 'password');
echo 'Database connection: OK\n';
"

# Test Vietnamese character support  
mysql -u openemr -p openemr -e "SELECT 'Vật lý trị liệu' as test_vietnamese;"
```

### Step 2: Web Interface Test
1. **Access OpenEMR**: Navigate to your OpenEMR installation
2. **Login**: Use administrative credentials
3. **Check Physiotherapy Menu**: Verify physiotherapy options appear
4. **Test Language Switching**: Switch between Vietnamese and English
5. **Create Test Assessment**: Create a test physiotherapy assessment

### Step 3: Feature Tests
```sql
-- Test physiotherapy tables exist
SHOW TABLES LIKE 'pt_%';

-- Test sample data
SELECT * FROM pt_assessment_templates LIMIT 5;
SELECT * FROM vietnamese_test LIMIT 5;
```

## 🔧 Post-Installation Setup

### Step 1: User Training Setup
1. **Create Training Users**: Set up test accounts for training
2. **Import Sample Data**: Load sample patients and assessments
3. **Configure Workflows**: Set up standard assessment and treatment workflows
4. **Create Templates**: Set up commonly used templates and forms

### Step 2: Integration Configuration
1. **Billing Integration**: Configure billing codes and procedures
2. **Report Templates**: Set up standard report formats
3. **Backup Procedures**: Configure automated backups
4. **Security Settings**: Review and configure security options

### Step 3: Performance Optimization
```sql
-- Add indexes for better performance
ALTER TABLE pt_assessment_templates ADD INDEX idx_category (category);
ALTER TABLE pt_exercise_prescriptions ADD INDEX idx_patient_active (patient_id, is_active);
ALTER TABLE pt_outcome_measures ADD INDEX idx_patient_date (patient_id, measurement_date);
```

## 🚨 Troubleshooting

### Common Installation Issues

#### Database Character Set Issues
```sql
-- Fix character set problems
ALTER TABLE pt_assessment_templates CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci;
ALTER TABLE pt_exercise_prescriptions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci;
```

#### Permission Issues
```bash
# Fix file permissions
chown -R www-data:www-data /path/to/openemr
chmod -R 755 /path/to/openemr/interface
chmod -R 755 /path/to/openemr/library
```

#### PHP Memory Issues
```php
// In php.ini or virtual host
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
```

### Verification Commands
```bash
# Check PHP configuration
php -i | grep -E "(memory_limit|max_execution_time|mysqli)"

# Check database connectivity
mysql -u openemr -p -e "SELECT VERSION();"

# Check file permissions
ls -la /path/to/openemr/interface/physiotherapy/

# Check web server logs
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

## 📚 Additional Resources

### Documentation
- **[Configuration Guide](./CONFIGURATION.md)** - Detailed configuration options
- **[Database Schema](../development/DATABASE_SCHEMA.md)** - Database structure documentation
- **[Troubleshooting Guide](./TROUBLESHOOTING.md)** - Common issues and solutions

### Support
- **System Requirements**: Check OpenEMR official requirements
- **Community Forums**: OpenEMR community support
- **Professional Support**: Commercial support options

## ✅ Installation Checklist

### Pre-Installation
- [ ] System requirements verified
- [ ] Backup completed
- [ ] Database permissions confirmed
- [ ] PHP extensions installed

### Installation
- [ ] Files deployed successfully
- [ ] Database schema installed
- [ ] Configuration files updated
- [ ] Web server configured

### Post-Installation
- [ ] Basic functionality tested
- [ ] Vietnamese characters verified
- [ ] User permissions configured
- [ ] Training materials prepared

### Go-Live
- [ ] User training completed
- [ ] Workflows documented
- [ ] Support procedures established
- [ ] Monitoring configured

---

**Installation complete! Ready to provide specialized physiotherapy care with Vietnamese language support.** 🏥🇻🇳