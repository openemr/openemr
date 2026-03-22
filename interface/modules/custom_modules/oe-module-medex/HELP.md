# MedEx Communication Platform - Help Documentation

## Overview

The MedEx Communication Platform provides HIPAA-compliant patient communication, appointment reminders, SMS messaging, and PDF form filling integrated directly into OpenEMR.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Features](#features)
3. [Menu Navigation](#menu-navigation)
4. [Configuration](#configuration)
5. [PDF Form Filler](#pdf-form-filler)
6. [Module Updates](#module-updates)
7. [Backups & Rollback](#backups--rollback)
8. [Troubleshooting](#troubleshooting)
9. [Support](#support)

---

## Getting Started

### Registration

If you're not yet registered with MedEx:

1. Go to: **Modules → MedEx Admin → Settings**
2. Click: **Register Now** button
3. Fill in your practice information
4. Submit registration
5. MedEx will contact you to activate your account

### First Time Setup

After registration:

1. Navigate to: **Modules → MedEx Admin → Settings**
2. The page will show your connection status:
   - ✅ **Online** - Connected successfully
   - ❌ **Offline** - Check settings
   - ⚠️ **Disabled** - Enable in Global Settings
3. Configure your preferences (messaging, reminders, etc.)

### Quick Access

- **Module Manager Status**: Click the ⚙️ gear icon next to MedEx in Module Manager
- **Admin Settings**: Modules → MedEx Admin → Settings
- **PDF Form Filler**: Miscellaneous → PDF Filler

---

## Features

### Patient Communication

- **SMS Messages**: Send secure text messages to patients
- **Email Notifications**: Automated email reminders and notifications
- **Voice Calls**: Automated voice message delivery
- **Two-Way Messaging**: Patients can respond to messages

### Appointment Management

- **Automated Reminders**: Send reminders before appointments
- **Confirmation Requests**: Patients can confirm appointments
- **Recall Campaigns**: Automated patient recall notifications
- **Custom Campaigns**: Create targeted communication campaigns

### PDF Form Filler

- **Template Management**: Create and manage PDF form templates (Admin)
- **Data Mapping**: Map OpenEMR fields to PDF form fields
- **Patient Forms**: Fill PDF forms with patient data
- **Document Storage**: Save completed forms to patient charts

### SMS Bot

- **Automated Responses**: Bot responds to patient SMS messages
- **Appointment Scheduling**: Patients can request appointments via SMS
- **Status Updates**: Get real-time message delivery status

### Patient Tracker

- **Flow Board**: Visual patient flow tracking
- **Wait Times**: Monitor patient wait times
- **Status Updates**: Track patient status through visit

---

## Menu Navigation

### Admin Menu (Modules → MedEx Admin)

Only visible to administrators:

1. **Settings** - Configure MedEx connection and preferences
2. **Messages** - View and manage patient messages
3. **SMS Bot** - Configure automated SMS responses
4. **Patient Tracker** - Access patient flow board
5. **PDF Filler Admin** - Manage PDF templates and data mapping
6. **Backups & Rollback** - Manage module backups and version rollback

### User Menu (Miscellaneous → PDF Filler)

Available to all authorized users:

- **PDF Filler** - Fill out PDF forms with patient data

### Module Manager

Access via **Administration → Modules → Manage Modules**:

- **Enable/Disable** - Turn module on or off
- **⚙️ Status** - View connection status and update notifications
- **Uninstall** - Remove module (not recommended)

---

## Configuration

### Connection Settings

Navigate to: **Modules → MedEx Admin → Settings**

**Server URL**:
- **Development**: `http://host.docker.internal/cart/upload`
- **Production**: `https://medexbank.com/cart/upload`
- **Custom**: Your MedEx server URL

**API Credentials**:
- Practice ID and API Token are provided after registration
- These are automatically configured during registration
- Never share your API token

**Connection Status**:
- **Practice Name**: Your registered practice name
- **Practice ID**: Your unique MedEx identifier
- **Server**: MedEx server you're connected to

### Global Settings

In OpenEMR Global Settings (Administration → Globals):

- **Enable MedEx**: Turn module functionality on/off globally
- **MedEx Practice ID**: Your practice ID (set during registration)
- **MedEx API Token**: Your API authentication token

---

## PDF Form Filler

### For Administrators (Template Management)

Navigate to: **Modules → MedEx Admin → PDF Filler Admin**

**Creating Templates**:
1. Upload a blank PDF form
2. Identify form fields (text boxes, checkboxes, etc.)
3. Map OpenEMR data fields to PDF fields
4. Save and activate template

**Data Mapping**:
- **Patient Fields**: Name, DOB, address, phone, email, etc.
- **Provider Fields**: Provider name, NPI, license, contact info
- **Facility Fields**: Facility name, address, phone, NPI
- **Encounter Fields**: Visit date, reason, diagnosis codes
- **Custom Fields**: Manual entry or calculated values

**Managing Library**:
- View all available templates
- Edit existing templates
- Deactivate unused templates
- Delete templates

### For Users (Filling Forms)

Navigate to: **Miscellaneous → PDF Filler**

**Filling a Form**:
1. Select patient from current context
2. Choose form template from list
3. Review auto-filled data
4. Fill in any manual fields
5. Preview completed form
6. Save to patient chart or download

**Auto-Fill Data**:
- Patient demographics
- Current encounter information
- Provider details
- Facility information
- Today's date and time

---

## Module Updates

### Checking for Updates

Updates are checked automatically every hour. View update status:

1. Go to: **Modules → MedEx Admin** (click ⚙️ gear icon in Module Manager)
2. If updates are available, you'll see a notification banner
3. Updates are prioritized:
   - 🚨 **CRITICAL** - Security patches (install immediately)
   - ⚠️ **SECURITY** - Security improvements (recommended within 24 hours)
   - ℹ️ **IMPORTANT** - Bug fixes (recommended soon)
   - ✨ **OPTIONAL** - New features (install at your convenience)

### Installing Updates

**Automatic Installation**:
1. Click **Install Update Now** button
2. Review changelog and any manual steps required
3. Confirm installation
4. System automatically:
   - Creates backup of current version
   - Downloads update package
   - Verifies package integrity
   - Installs new files
   - Runs database migrations
   - Clears caches

**Progress Tracking**:
- Visual progress bar shows installation progress
- Live log displays each step
- Success/error messages shown at completion

### Critical Security Patches

When MedEx Admin releases a critical security patch:

- **Full-screen notification** appears on every admin page
- **Pulsing red button** draws immediate attention
- **Security message** explains the vulnerability
- **Cannot be dismissed** - must acknowledge
- Notification persists until update is installed

**What to do**:
1. Read the security message carefully
2. Click "Install Update Now"
3. Installation completes automatically
4. Notification disappears after successful update

### Update Requirements

- **Write Permissions**: Web server must be able to write to module directory
- **Disk Space**: Ensure adequate space for backup and new version
- **Database Access**: ALTER TABLE permissions for migrations
- **Network Access**: Connection to MedEx update server

---

## Backups & Rollback

Navigate to: **Modules → MedEx Admin → Backups & Rollback**

### Automatic Backups

Backups are created automatically:
- Before every update installation
- Before every rollback operation
- When you click "Create Backup Now"

### Viewing Backups

The backup manager shows:
- **Version**: Module version in the backup
- **Date Created**: When backup was created
- **Size**: Backup file size in MB
- **Current Badge**: Highlights version matching current installation

### Rolling Back

If an update causes issues:

1. Go to **Backups & Rollback** page
2. Find the version you want to restore
3. Click **Rollback** button
4. Confirm in the modal dialog
5. System will:
   - Backup current version first
   - Extract selected backup
   - Replace all module files
   - Clear update cache
   - Show success message

**Important Notes**:
- Database changes are NOT automatically rolled back
- You can always roll forward again after rolling back
- Manual backup is recommended before major updates

### Managing Backups

**Download Backup**:
- Click **Download** to save backup ZIP locally
- Store offsite for disaster recovery

**Delete Backup**:
- Click **Delete** to remove old backups
- Frees up disk space
- Cannot be undone - use carefully

**Create Manual Backup**:
- Click **Create Backup Now**
- Useful before testing or making changes
- Stored with timestamp in filename

### Backup Storage

- **Location**: `{site}/documents/medex_backups/`
- **Format**: `medex_v{version}_{timestamp}.zip`
- **Example**: `medex_v1.0.0_2025-01-22_14-30-45.zip`
- **Contents**: Complete module directory (all files)

---

## Troubleshooting

### Connection Issues

**Problem**: "Connection Failed" or "Offline" status

**Solutions**:
1. Check MedEx server URL in settings
2. Verify API credentials are correct
3. Test network connectivity to MedEx server
4. Check firewall rules allow HTTPS to MedEx
5. Review PHP error log for details

**Testing Connection**:
```bash
# From OpenEMR server
curl -X POST "https://medexbank.com/index.php?route=api/oemr/ping" \
  -d "site=YOUR_PRACTICE_ID"
```

### Module Not Loading

**Problem**: Gear icon shows blank or 404 error

**Solutions**:
1. Clear browser cache (Ctrl+Shift+R)
2. Check module is enabled in Module Manager
3. Verify all module files exist
4. Check PHP error log: `/var/log/php_errors.log`
5. Access status page directly: `http://YOUR-OPENEMR/interface/modules/custom_modules/oe-module-medex/public/status.php?site=default`

### Registration Issues

**Problem**: "Not Registered" message persists

**Solutions**:
1. Complete registration form with all required fields
2. Check email for activation link
3. Contact MedEx support if no response
4. Verify practice information is correct
5. Check spam folder for MedEx emails

### Update Failures

**Problem**: Update installation fails

**Solutions**:
1. **Insufficient Permissions**:
   ```bash
   chown -R www-data:www-data /path/to/oe-module-medex
   chmod -R 755 /path/to/oe-module-medex
   ```

2. **Download Failed**:
   - Check network connectivity
   - Verify firewall allows HTTPS to MedEx
   - Try again later if MedEx server is busy

3. **Verification Failed**:
   - Package may be corrupt, retry download
   - Contact MedEx support if persists

4. **Migration Failed**:
   - Check database error log
   - Verify ALTER TABLE permissions
   - Rollback to previous version if needed

### PDF Form Filler Issues

**Problem**: Forms not loading or filling

**Solutions**:
1. Check if templates are activated (Admin page)
2. Verify patient context is set
3. Check encounter is selected for encounter data
4. Review browser console for JavaScript errors
5. Ensure MedEx server is accessible

**Problem**: Form data not saving to chart

**Solutions**:
1. Verify write permissions to documents directory
2. Check document categories exist
3. Review PHP error log for save errors
4. Ensure adequate disk space

### Performance Issues

**Problem**: Module seems slow

**Solutions**:
1. Check network latency to MedEx server
2. Verify database query performance
3. Clear update cache manually:
   ```sql
   UPDATE medex_prefs SET module_update_cache = NULL;
   ```
4. Review Apache/PHP error logs for warnings
5. Consider increasing PHP memory limit

---

## Support

### Documentation

- **README.md** - Module overview
- **UPDATE_SYSTEM.md** - Update and rollback system details
- **HELP.md** - This comprehensive help file

### Getting Help

1. **Check Status Page**: Modules → MedEx Admin (⚙️ gear icon)
   - Shows connection status
   - Displays any update notifications
   - Links to settings

2. **Review Logs**:
   - PHP errors: `/var/log/php_errors.log`
   - Apache errors: `/var/log/apache2/error.log`
   - OpenEMR logs: Check via Administration → Logs

3. **Test Connection**: Use status_test.php for diagnostics:
   ```
   http://YOUR-OPENEMR/interface/modules/custom_modules/oe-module-medex/public/status_test.php?site=default
   ```

4. **MedEx Support**:
   - **Email**: support@MedExBank.com
   - **Website**: https://medexbank.com
   - **Hours**: Monday-Friday, 9am-5pm EST

5. **OpenEMR Community**:
   - **Forum**: https://community.open-emr.org
   - **GitHub Issues**: https://github.com/openemr/openemr/issues
   - Include: Module version, OpenEMR version, error messages

### System Requirements

- **OpenEMR**: 7.0.0 or higher
- **PHP**: 8.2 or higher
- **MySQL**: 5.7 or higher
- **Disk Space**: 50 MB minimum for module and backups
- **Network**: HTTPS access to medexbank.com

### Version Information

- **Current Module Version**: Check Modules → MedEx Admin (⚙️ gear icon)
- **Latest Available Version**: Shown in update notifications
- **OpenEMR Version**: Administration → About

### License

**Proprietary - All Rights Reserved**

Copyright (c) 2018-2025 MedEx

This is proprietary software. All rights reserved. Unauthorized copying, modification, distribution, or use is strictly prohibited.

---

## Quick Reference

### Common Tasks

| Task | Navigation |
|------|------------|
| View connection status | Module Manager → MedEx → ⚙️ gear icon |
| Configure settings | Modules → MedEx Admin → Settings |
| Check for updates | Status page (automatic) |
| Install update | Status page → Install Update Now |
| Rollback update | Modules → MedEx Admin → Backups & Rollback |
| Fill PDF form | Miscellaneous → PDF Filler |
| Manage PDF templates | Modules → MedEx Admin → PDF Filler Admin |
| View messages | Modules → MedEx Admin → Messages |
| Create backup | Modules → MedEx Admin → Backups & Rollback → Create Backup Now |

### Keyboard Shortcuts

When viewing forms or messages:
- **Ctrl+S** - Save (where applicable)
- **Esc** - Close modal/dialog
- **Ctrl+R** - Refresh page

### Status Icons

- ✅ **Online** - Connected and working
- ❌ **Offline** - Connection failed
- ⚠️ **Disabled** - Module disabled in globals
- ⚙️ **Not Registered** - Registration required
- 🚨 **Critical Update** - Security patch available
- ⚠️ **Security Update** - Security improvement available
- ℹ️ **Important Update** - Bug fix available
- ✨ **Optional Update** - New features available

---

**Need immediate help?** Contact MedEx Support at support@MedExBank.com

**Have feedback?** We'd love to hear from you! Let us know how we can improve the MedEx Communication Platform.
