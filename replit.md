# OpenEMR

## Overview
This is OpenEMR version 7.0.4 - a Free and Open Source electronic health records and medical practice management application.

## Project Architecture

### Backend
- **Language**: PHP 8.2+
- **Framework**: Laminas MVC (formerly Zend Framework)
- **Database**: MySQL/MariaDB (required)
- **Dependencies**: Managed via Composer

### Frontend
- **Build System**: Gulp 4.x
- **Package Manager**: NPM
- **UI Libraries**: Bootstrap 4, jQuery, Angular 1.x, various medical UI components
- **Assets**: SCSS/CSS themes compiled via Gulp

## Recent Changes
- **2025-10-06**: Initial Replit import setup
  - Installed PHP dependencies via Composer
  - Installed NPM dependencies
  - Created PHP development server workflow
  - Added GitHub Actions workflow for EC2 deployment with git pull

## Development Setup

### Local Development (Replit)
The application runs on PHP's built-in development server on port 5000.

**Note**: OpenEMR requires MySQL/MariaDB. The PostgreSQL database created in Replit is not compatible with OpenEMR's database schema.

### Build Commands
```bash
# Install PHP dependencies
composer install --no-dev
composer dump-autoload -o

# Install Node dependencies  
npm install

# Build frontend assets
npm run build
```

## GitHub Actions Deployment

A GitHub Action workflow has been created at `.github/workflows/deploy-ec2.yml` for deploying to EC2.

### Required GitHub Secrets
- `AWS_ACCESS_KEY_ID`: AWS access key with SSM permissions
- `AWS_SECRET_ACCESS_KEY`: AWS secret access key
- `EC2_INSTANCE_ID`: Target EC2 instance ID (e.g., i-1234567890abcdef0)

### EC2 Instance Requirements
- **OS**: Ubuntu 24.04
- **IAM Role**: Instance must have SSM managed instance role attached
- **SSM Agent**: Must be installed and running
- **Software**: 
  - Apache2
  - PHP 8.2+ with required extensions
  - MySQL/MariaDB
  - Composer
  - Node.js 22+
  - Git
- **Git Repository**: The repository must be cloned at `/var/www/html`
- **Permissions**: Apache runs as `www-data` user

### Initial EC2 Setup
Before the first deployment, set up your EC2 instance:
```bash
# Connect via SSM
aws ssm start-session --target <INSTANCE_ID> --region us-east-1

# On the EC2 instance, clone the repository
cd /var/www
sudo rm -rf html
sudo git clone https://github.com/your-username/your-repo.git html
cd html
sudo git checkout production
```

### SSM Session Manager
To connect to your EC2 instance via SSM:
```bash
aws ssm start-session --target <INSTANCE_ID> --region us-east-1
```

### Deployment Process
1. Workflow triggers on push to `production` branch
2. SSM command connects to EC2 instance
3. Git pulls latest changes from production branch
4. Composer installs PHP dependencies
5. NPM installs dependencies and builds frontend assets
6. Apache is restarted
7. Deployment output is displayed in GitHub Actions logs

## User Preferences
None specified yet.
