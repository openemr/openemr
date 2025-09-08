# OpenEMR Physiotherapy Hybrid Development Environment

**Vietnamese Bilingual Physiotherapy Customization**  
*Author: Dang Tran <tqvdang@msn.com>*

## 🎯 Overview

This development environment provides Docker services for database and supporting tools while allowing local PHP development for the OpenEMR application. It's specifically configured for Vietnamese physiotherapy customizations.

## 🏗️ Architecture

```
┌─────────────────┐    ┌──────────────────┐
│   Local PHP     │    │  Docker Services │
│   OpenEMR App   │◄───┤  - MariaDB       │
│   (Port 80/443) │    │  - Redis         │
│                 │    │  - MailHog       │
│                 │    │  - phpMyAdmin    │
│                 │    │  - Adminer       │
└─────────────────┘    └──────────────────┘
```

## 🚀 Quick Start

### 1. Navigate to Directory
```bash
cd docker/development-physiotherapy
```

### 2. Start Services
```bash
./scripts/start-dev.sh
```

### 3. Configure Local PHP
Follow the configuration guide in the main documentation.

## 📁 Directory Structure

```
docker/development-physiotherapy/
├── .env                           # Environment configuration
├── docker-compose.yml             # Docker services definition
├── README.md                      # This file
├── configs/                       # Service configurations
│   ├── mariadb/
│   │   ├── custom.cnf            # MariaDB Vietnamese configuration
│   │   └── init/                 # Database initialization scripts
│   │       ├── 01-vietnamese-setup.sql
│   │       └── 02-physiotherapy-extensions.sql
│   ├── phpmyadmin/
│   │   └── config.user.inc.php   # phpMyAdmin Vietnamese config
│   └── redis/
│       └── redis.conf            # Redis configuration
├── data/                         # Persistent data (created automatically)
│   ├── mariadb/                  # MariaDB data files
│   ├── redis/                    # Redis data files
│   └── mailhog/                  # MailHog emails
├── logs/                         # Service logs
│   └── mariadb/                  # MariaDB logs
├── backups/                      # Database backups
└── scripts/                      # Management scripts
    ├── start-dev.sh              # Start services
    ├── stop-dev.sh               # Stop services
    ├── backup-db.sh              # Backup database
    └── restore-db.sh             # Restore database
```

## 🐳 Services

| Service | Port | URL | Credentials |
|---------|------|-----|-------------|
| **MariaDB** | 3306 | localhost:3306 | openemr/openemr123! |
| **phpMyAdmin** | 8081 | http://localhost:8081 | openemr/openemr123! |
| **Adminer** | 8082 | http://localhost:8082 | openemr/openemr123! |
| **Redis** | 6379 | localhost:6379 | Password: redis123 |
| **MailHog Web** | 8025 | http://localhost:8025 | No auth |
| **MailHog SMTP** | 1025 | localhost:1025 | No auth |

## 🛠️ Management Commands

### Start Development Environment
```bash
./scripts/start-dev.sh
```

### Stop Development Environment
```bash
./scripts/stop-dev.sh
```

### Database Management
```bash
# Create backup
./scripts/backup-db.sh

# Restore from backup
./scripts/restore-db.sh [backup-file.sql.gz]

# Direct database access
docker-compose exec mariadb mysql -uopenemr -p openemr
```

### View Service Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f mariadb
docker-compose logs -f redis
```

## 🇻🇳 Vietnamese Features

### Database Configuration
- **Character Set**: utf8mb4
- **Collation**: utf8mb4_vietnamese_ci
- **Timezone**: Asia/Ho_Chi_Minh

### Physiotherapy Extensions
The system automatically creates Vietnamese physiotherapy-specific tables:
- `pt_assessment_templates` - Assessment templates
- `pt_exercise_prescriptions` - Exercise prescriptions  
- `pt_outcome_measures` - Outcome measurements
- `pt_treatment_plans` - Treatment plans

### Sample Data
Includes Vietnamese sample data for:
- Common physiotherapy assessments
- Exercise prescriptions with Vietnamese translations
- Outcome measure templates

## ⚙️ Configuration

### Environment Variables (.env)
Key configuration options:
```bash
# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=openemr
DB_USER=openemr
DB_PASSWORD=openemr123!

# Redis
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=redis123

# Web interfaces
PMA_PORT=8081
ADMINER_PORT=8082
MAILHOG_HTTP_PORT=8025
```

### Local PHP Requirements
Your local PHP needs these extensions:
- mysqli or pdo_mysql
- redis
- mbstring
- openssl
- json
- curl
- gd
- zip
- xml

## 🔧 Troubleshooting

### Services Won't Start
```bash
# Check Docker is running
docker info

# Check ports aren't in use
lsof -i :3306
lsof -i :6379

# View detailed logs
docker-compose logs mariadb
```

### Database Connection Issues
```bash
# Test database connection
docker-compose exec mariadb mysql -uopenemr -p -e "SELECT 1"

# Check Vietnamese character support
docker-compose exec mariadb mysql -uopenemr -p openemr -e "SELECT * FROM vietnamese_test LIMIT 5"
```

### Performance Issues
```bash
# Check container resources
docker stats

# Optimize MariaDB (edit configs/mariadb/custom.cnf)
# Increase PHP memory_limit in local php.ini
```

## 🔄 Development Workflow

1. **Start Services**: `./scripts/start-dev.sh`
2. **Configure Local PHP**: Connect to localhost:3306
3. **Start Local Web Server**: Apache/Nginx/PHP built-in
4. **Develop**: Edit files locally, changes reflect immediately
5. **Test**: Use phpMyAdmin/Adminer for database, MailHog for emails
6. **Backup**: Regular backups with `./scripts/backup-db.sh`
7. **Stop**: `./scripts/stop-dev.sh` when done

## 📚 Additional Resources

### Physiotherapy Documentation
- **Main Documentation Hub**: `../../Documentation/physiotherapy/README.md`
- **Development Guide**: `../../Documentation/physiotherapy/development/HYBRID_DEVELOPMENT_GUIDE.md`
- **User Guides**: `../../Documentation/physiotherapy/user-guides/`
- **Technical Documentation**: `../../Documentation/physiotherapy/technical/`

### External Resources
- **OpenEMR Docs**: https://www.open-emr.org/wiki/
- **MariaDB Vietnamese**: https://mariadb.com/kb/en/vietnamese-collations/
- **Docker Compose**: https://docs.docker.com/compose/

## 🆘 Support

### Common Issues
- **Port conflicts**: Change ports in .env file
- **Permissions**: Ensure scripts are executable (`chmod +x scripts/*.sh`)
- **Data persistence**: Data is stored in `data/` directory
- **Character encoding**: All services configured for UTF-8mb4

### Getting Help
1. Check service logs: `docker-compose logs [service]`
2. Verify configuration: Check .env and docker-compose.yml
3. Test connectivity: Use provided test commands
4. Review documentation: See main development guide

---

**Ready for Vietnamese physiotherapy development!** 🏥🇻🇳