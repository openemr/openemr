# OpenEMR Physiotherapy Module Documentation

**Vietnamese Bilingual Physiotherapy Customization for OpenEMR**  
*Author: Dang Tran <tqvdang@msn.com>*

## 📚 Documentation Overview

This documentation provides comprehensive guides for the OpenEMR Physiotherapy module, specifically customized for Vietnamese healthcare environments with bilingual support.

## 🗂️ Documentation Structure

### 📖 [Development](./development/)
Technical documentation for developers working on the physiotherapy module:
- **[Hybrid Development Guide](./development/HYBRID_DEVELOPMENT_GUIDE.md)** - Complete setup guide for local development environment
- **[Database Schema](./development/DATABASE_SCHEMA.md)** - Physiotherapy-specific database tables and structures
- **[API Documentation](./development/API_DOCUMENTATION.md)** - REST API endpoints for physiotherapy features
- **[Vietnamese Localization](./development/VIETNAMESE_LOCALIZATION.md)** - Implementation details for Vietnamese language support

### 👥 [User Guides](./user-guides/)
End-user documentation for healthcare professionals:
- **[Getting Started](./user-guides/GETTING_STARTED.md)** - Basic introduction to physiotherapy features
- **[Patient Assessment](./user-guides/PATIENT_ASSESSMENT.md)** - How to conduct and document assessments
- **[Exercise Prescription](./user-guides/EXERCISE_PRESCRIPTION.md)** - Creating and managing exercise programs
- **[Outcome Measures](./user-guides/OUTCOME_MEASURES.md)** - Recording and tracking patient outcomes
- **[Reporting](./user-guides/REPORTING.md)** - Generating physiotherapy reports

### 🔧 [Technical](./technical/)
System administration and technical configuration:
- **[Installation Guide](./technical/INSTALLATION.md)** - Step-by-step installation instructions
- **[Configuration](./technical/CONFIGURATION.md)** - System configuration options
- **[Database Maintenance](./technical/DATABASE_MAINTENANCE.md)** - Backup, restore, and maintenance procedures
- **[Troubleshooting](./technical/TROUBLESHOOTING.md)** - Common issues and solutions
- **[Security](./technical/SECURITY.md)** - Security considerations and best practices

### 🖼️ [Images](./images/)
Screenshots, diagrams, and visual documentation assets

## 🚀 Quick Start Links

### For Developers
- **[Development Environment Setup](./development/HYBRID_DEVELOPMENT_GUIDE.md)** - Start here to set up your development environment
- **[Docker Physiotherapy Environment](../../docker/development-physiotherapy/README.md)** - Quick reference for the Docker setup

### For System Administrators
- **[Installation Guide](./technical/INSTALLATION.md)** - Install the physiotherapy module
- **[Configuration Guide](./technical/CONFIGURATION.md)** - Configure the system

### For End Users
- **[Getting Started](./user-guides/GETTING_STARTED.md)** - Learn how to use the physiotherapy features
- **[Patient Workflow](./user-guides/PATIENT_WORKFLOW.md)** - Typical patient care workflow

## 🇻🇳 Vietnamese Localization Features

- **Bilingual Interface**: Complete Vietnamese translation with fallback to English
- **Cultural Adaptation**: Terminology adapted for Vietnamese healthcare practices  
- **Character Support**: Full UTF-8mb4 support for Vietnamese characters
- **Date/Time Formatting**: Vietnamese locale-aware formatting
- **Report Templates**: Vietnamese-language report templates

## 🏥 Physiotherapy Features

### Core Modules
- **Patient Assessment Templates**: Standardized assessment forms
- **Exercise Prescription System**: Comprehensive exercise library with Vietnamese translations
- **Outcome Measures Tracking**: Progress monitoring and measurement tools
- **Treatment Planning**: Structured treatment plan creation and management
- **Progress Documentation**: Session notes and progress tracking

### Integration Features
- **OpenEMR Core Integration**: Seamless integration with existing OpenEMR workflows
- **Billing Integration**: Integration with OpenEMR billing system
- **Report Generation**: Custom physiotherapy reports
- **User Permissions**: Role-based access control for physiotherapy features

## 📋 Documentation Standards

### Writing Guidelines
- Use clear, concise language appropriate for the target audience
- Include screenshots and examples where helpful
- Maintain consistency in terminology between English and Vietnamese
- Follow OpenEMR documentation standards

### File Organization
- Keep related documentation in appropriate subdirectories
- Use descriptive filenames in UPPERCASE with underscores
- Include cross-references between related documents
- Maintain a table of contents for longer documents

## 🔗 Related Resources

### OpenEMR Official Documentation
- [OpenEMR Wiki](https://www.open-emr.org/wiki/)
- [OpenEMR Developer Documentation](https://www.open-emr.org/wiki/index.php/Developers_Guide)

### Vietnamese Healthcare Standards
- Vietnamese Ministry of Health Guidelines
- Vietnamese Physiotherapy Association Standards

### Development Resources
- [Git Repository](https://github.com/openemr/openemr)
- [Docker Documentation](https://docs.docker.com/)
- [MariaDB Vietnamese Collations](https://mariadb.com/kb/en/vietnamese-collations/)

## 📞 Support and Contact

### For Technical Issues
- Check the [Troubleshooting Guide](./technical/TROUBLESHOOTING.md)
- Review system logs and error messages
- Consult the [FAQ section](./technical/FAQ.md)

### For Feature Requests
- Submit issues through the appropriate channels
- Follow the contribution guidelines
- Provide detailed use cases and requirements

### Development Team Contact
- **Lead Developer**: Dang Tran <tqvdang@msn.com>
- **Repository**: OpenEMR Physiotherapy Branch
- **Documentation**: This documentation set

---

**Last Updated**: September 2025  
**Version**: 1.0  
**Compatible with**: OpenEMR 7.0.0+