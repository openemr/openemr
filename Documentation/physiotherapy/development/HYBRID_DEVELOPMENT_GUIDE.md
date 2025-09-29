# OpenEMR Hybrid Development Environment Guide

**Vietnamese Physiotherapy Customization Setup**  
*Author: Dang Tran <tqvdang@msn.com>*

## 🎯 Overview

This guide provides comprehensive instructions for setting up and using the OpenEMR hybrid development environment, where Docker containers provide database and supporting services while your local PHP installation runs the OpenEMR application.

**Important**: All hybrid development files are located in the `docker/development-physiotherapy/` directory to maintain organization with other OpenEMR development environments.

## 🏗️ Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Local PHP     │    │  Docker Services │    │   Development   │
│   OpenEMR App   │◄───┤  - MariaDB       │◄───┤     Tools       │
│   (Port 80/443) │    │  - Redis         │    │  - phpMyAdmin   │
│                 │    │  - MailHog       │    │  - Adminer      │
└─────────────────┘    └──────────────────┘    └─────────────────┘
```

## 🚀 Quick Start

### 1. Start Docker Services

```bash
# Navigate to the physiotherapy development directory
cd docker/development-physiotherapy

# Start all containerized services
./scripts/start-dev.sh
```

### 2. Configure Local PHP

See [Local PHP Configuration](#-local-php-configuration) section below.

### 3. Access Your Application

- **OpenEMR**: http://localhost (your local web server)
- **phpMyAdmin**: http://localhost:8081
- **Adminer**: http://localhost:8082
- **MailHog**: http://localhost:8025

## 🐳 Docker Services

### MariaDB Database
- **Host**: localhost:3306
- **Database**: openemr
- **Username**: openemr
- **Password**: openemr123!
- **Character Set**: utf8mb4_vietnamese_ci

### Redis Cache
- **Host**: localhost:6379
- **Password**: redis123
- **Use for**: Sessions, caching, queue management

### MailHog (Email Testing)
- **SMTP**: localhost:1025
- **Web UI**: http://localhost:8025
- **Use for**: Development email testing

### phpMyAdmin & Adminer
- Database management interfaces
- Both connect automatically to MariaDB

## ⚙️ Local PHP Configuration

### Required PHP Extensions

Ensure these extensions are installed and enabled:

```bash
# Check PHP version (7.4+ recommended)
php --version

# Check required extensions
php -m | grep -E "(mysqli|pdo_mysql|redis|mbstring|openssl|json|curl|gd|zip|xml)"
```

### PHP Configuration Updates

Edit your `php.ini` file with these settings:

```ini
; Memory and execution limits
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

; File upload settings
file_uploads = On
upload_max_filesize = 100M
post_max_size = 100M

; Session settings for Redis
session.save_handler = redis
session.save_path = "tcp://localhost:6379?auth=redis123"
session.name = OPENEMR_PHPSESSID
session.cookie_lifetime = 7200

; Database settings
mysqli.default_host = localhost
mysqli.default_port = 3306

; Character encoding
default_charset = "UTF-8"
internal_encoding = "UTF-8"
input_encoding = "UTF-8"
output_encoding = "UTF-8"

; Vietnamese locale support
date.timezone = "Asia/Ho_Chi_Minh"

; Development settings
display_errors = On
display_startup_errors = On
log_errors = On
error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT
```

### Apache Configuration

Create or update your Apache virtual host:

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "/Users/dang/dev/openemr"
    
    <Directory "/Users/dang/dev/openemr">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        # PHP settings
        php_admin_value memory_limit 512M
        php_admin_value max_execution_time 300
        php_admin_value upload_max_filesize 100M
        php_admin_value post_max_size 100M
        
        # Character encoding
        AddDefaultCharset UTF-8
        AddCharset UTF-8 .php
    </Directory>
    
    # Logging
    ErrorLog "/var/log/apache2/openemr_error.log"
    CustomLog "/var/log/apache2/openemr_access.log" combined
    
    # Security headers
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

### Nginx Configuration

Alternative Nginx configuration:

```nginx
server {
    listen 80;
    server_name localhost;
    root /Users/dang/dev/openemr;
    index index.php index.html;
    
    # Character encoding
    charset utf-8;
    
    # PHP handling
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PHP_VALUE "
            memory_limit=512M
            max_execution_time=300
            upload_max_filesize=100M
            post_max_size=100M
            session.save_handler=redis
            session.save_path='tcp://localhost:6379?auth=redis123'
        ";
        include fastcgi_params;
    }
    
    # Security
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    
    # Logging
    error_log /var/log/nginx/openemr_error.log;
    access_log /var/log/nginx/openemr_access.log;
}
```

## 🔧 OpenEMR Configuration

### Database Connection

Update your OpenEMR `sites/default/sqlconf.php`:

```php
<?php
// Database connection settings
$host = 'localhost';
$port = '3306';
$login = 'openemr';
$pass = 'openemr123!';
$dbase = 'openemr';

// Character set
$disable_utf8_flag = false;
$sqlconf['utf8'] = 1;

// Connection options
$config = 0;
?>
```

### Redis Session Configuration

Add to your OpenEMR configuration file:

```php
// Redis session configuration
$GLOBALS['session_save_handler'] = 'redis';
$GLOBALS['session_save_path'] = 'tcp://localhost:6379?auth=redis123';

// Cache configuration
$GLOBALS['cache_driver'] = 'redis';
$GLOBALS['redis_server'] = 'localhost';
$GLOBALS['redis_port'] = 6379;
$GLOBALS['redis_password'] = 'redis123';
```

### Email Configuration

Configure SMTP to use MailHog for development:

```php
$GLOBALS['SMTP_HOST'] = 'localhost';
$GLOBALS['SMTP_PORT'] = 1025;
$GLOBALS['SMTP_USER'] = '';
$GLOBALS['SMTP_PASS'] = '';
$GLOBALS['SMTP_SECURE'] = '';
```

## 🛠️ Development Workflow

### Daily Development Routine

1. **Start Services**
   ```bash
   cd docker/development-physiotherapy && ./scripts/start-dev.sh
   ```

2. **Start Local Web Server**
   ```bash
   # Apache
   sudo service apache2 start
   
   # Or Nginx
   sudo service nginx start
   
   # Or built-in PHP server (for testing)
   php -S localhost:8000 -t /Users/dang/dev/openemr
   ```

3. **Develop and Test**
   - Edit PHP files directly
   - Changes reflect immediately
   - Use phpMyAdmin/Adminer for database work
   - Check emails in MailHog

4. **End of Day**
   ```bash
   cd docker/development-physiotherapy && ./scripts/stop-dev.sh
   ```

### Database Management

```bash
# Create backup
cd docker/development-physiotherapy && ./scripts/backup-db.sh

# Restore backup
cd docker/development-physiotherapy && ./scripts/restore-db.sh backup_file.sql.gz

# Access database directly
docker-compose exec mariadb mysql -uopenemr -p openemr
```

### Troubleshooting Commands

```bash
# Check service status
docker-compose ps

# View logs
docker-compose logs mariadb
docker-compose logs redis
docker-compose logs mailhog

# Restart specific service
docker-compose restart mariadb

# Test database connection
docker-compose exec mariadb mysql -uopenemr -p -e "SELECT 1"

# Test Redis connection
docker-compose exec redis redis-cli -a redis123 ping

# Check Vietnamese character support
docker-compose exec mariadb mysql -uopenemr -p openemr -e "SELECT * FROM vietnamese_test LIMIT 5"
```

## 🇻🇳 Vietnamese Localization

### Database Settings
- Character set: `utf8mb4`
- Collation: `utf8mb4_vietnamese_ci`
- Timezone: `Asia/Ho_Chi_Minh`

### PHP Settings
```php
// In your PHP configuration
setlocale(LC_ALL, 'vi_VN.UTF-8');
date_default_timezone_set('Asia/Ho_Chi_Minh');
mb_internal_encoding('UTF-8');
```

### Testing Vietnamese Support

1. **Database Test**
   ```sql
   INSERT INTO vietnamese_test (vietnamese_text) VALUES ('Vật lý trị liệu');
   SELECT * FROM vietnamese_test WHERE vietnamese_text LIKE '%liệu%';
   ```

2. **PHP Test**
   ```php
   <?php
   header('Content-Type: text/html; charset=UTF-8');
   echo "Hệ thống quản lý bệnh viện - Vật lý trị liệu";
   echo "<br>Encoding: " . mb_internal_encoding();
   ?>
   ```

## 🏥 Physiotherapy Features

### Custom Tables Created
- `pt_assessment_templates` - Assessment templates
- `pt_exercise_prescriptions` - Exercise prescriptions
- `pt_outcome_measures` - Outcome measurements
- `pt_treatment_plans` - Treatment plans

### Sample Data
The system includes sample Vietnamese physiotherapy data:
- Assessment templates for common conditions
- Exercise prescriptions with Vietnamese translations
- Outcome measure templates

### Accessing PT Features
1. Log into OpenEMR
2. Navigate to physiotherapy modules
3. All data supports both English and Vietnamese

## 🔧 IDE Configuration

### VS Code Settings
```json
{
    "php.validate.executablePath": "/usr/local/bin/php",
    "php.suggest.basic": true,
    "files.encoding": "utf8",
    "files.associations": {
        "*.php": "php"
    },
    "editor.insertSpaces": true,
    "editor.tabSize": 4
}
```

### Xdebug Configuration
Add to your `php.ini`:
```ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=develop,debug
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
xdebug.start_with_request=yes
```

## 🚨 Common Issues

### Database Connection Failed
```bash
# Check if MariaDB is running
docker-compose ps mariadb

# Verify connection settings
docker-compose exec mariadb mysql -uopenemr -p -e "SELECT 1"

# Check PHP mysqli extension
php -m | grep mysqli
```

### Session Issues
```bash
# Check Redis connection
docker-compose exec redis redis-cli -a redis123 ping

# Verify PHP Redis extension
php -m | grep redis

# Check session configuration in php.ini
php --ini
```

### Character Encoding Issues
```bash
# Verify database charset
docker-compose exec mariadb mysql -uopenemr -p -e "SHOW VARIABLES LIKE 'character_set%'"

# Test Vietnamese data
docker-compose exec mariadb mysql -uopenemr -p openemr -e "SELECT * FROM vietnamese_test"
```

### Performance Issues
```bash
# Check container resources
docker stats

# Increase memory limits in php.ini
memory_limit = 1G

# Optimize MariaDB
# Edit docker/configs/mariadb/custom.cnf
```

## 📚 Additional Resources

### Documentation
- [OpenEMR Official Documentation](https://www.open-emr.org/wiki/)
- [MariaDB Vietnamese Collation](https://mariadb.com/kb/en/vietnamese-collations/)
- [PHP Redis Extension](https://github.com/phpredis/phpredis)

### Development Tools
- **phpMyAdmin**: Database management
- **Adminer**: Lightweight database tool
- **MailHog**: Email testing
- **Redis CLI**: Cache management

### Backup Strategy
- Daily automated backups: `cd docker/development-physiotherapy && ./scripts/backup-db.sh`
- Version control: Git with proper `.gitignore`
- Data persistence: Docker volumes in `docker/data/`

## 🆘 Support

### Logs Location
- Docker logs: `docker-compose logs [service]`
- Apache logs: `/var/log/apache2/`
- Nginx logs: `/var/log/nginx/`
- MariaDB logs: `docker/logs/mariadb/`

### Getting Help
1. Check this guide first
2. Review Docker Compose logs
3. Verify PHP configuration
4. Test database connectivity
5. Check file permissions

### Contact
For issues specific to this setup:
- Author: Dang Tran
- Email: tqvdang@msn.com
- Repository: OpenEMR Physiotherapy Branch

---

*This hybrid development environment provides the best of both worlds: the simplicity of local PHP development with the reliability and consistency of containerized services.*