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
  - Added GitHub Actions workflow for EC2 deployment

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
- `AWS_ACCESS_KEY_ID`: AWS access key with SSM and S3 permissions
- `AWS_SECRET_ACCESS_KEY`: AWS secret access key
- `EC2_INSTANCE_ID`: Target EC2 instance ID (e.g., i-1234567890abcdef0)
- `S3_DEPLOYMENT_BUCKET`: S3 bucket name for temporary deployment archives

### EC2 Instance Requirements
- **OS**: Ubuntu 24.04
- **IAM Role**: Instance must have SSM managed instance role attached
- **SSM Agent**: Must be installed and running
- **Software**: Apache2, PHP 8.2+, MySQL/MariaDB
- **Permissions**: Instance IAM role needs S3 read access to deployment bucket

### SSM Session Manager
To connect to your EC2 instance via SSM:
```bash
aws ssm start-session --target <INSTANCE_ID> --region us-east-1
```

### Deployment Process
1. Code is checked out and dependencies are installed
2. Frontend assets are built
3. Application is packaged (excluding git, docker, tests)
4. Archive is uploaded to S3
5. SSM command downloads and deploys to EC2
6. Apache is restarted
7. S3 archive is cleaned up

## User Preferences
None specified yet.
