# MedEx Module Update System

## Overview

The MedEx module includes a comprehensive automatic update system that allows MedEx administrators to push updates (including critical security patches) directly to OpenEMR installations.

## Features

- **Automatic Version Checking**: Checks for updates every hour (configurable)
- **Priority Levels**: CRITICAL, SECURITY, IMPORTANT, OPTIONAL
- **Automatic Backups**: Creates backup before every update
- **Database Migrations**: Handles schema changes automatically
- **Push Notifications**: MedEx Admin can push critical patches that display prominently
- **File Permissions Checking**: Verifies write permissions before attempting updates
- **Rollback Support**: Can restore from backup if update fails

## Update Priority Levels

### CRITICAL
- Security vulnerabilities that require immediate patching
- Displays modal notification that cannot be dismissed
- Appears on every page load until installed
- Pulsing red button, urgent messaging
- **Recommended**: Install immediately

### SECURITY
- Security improvements and hardening
- Prominent warning notification
- Orange/warning colors
- **Recommended**: Install within 24 hours

### IMPORTANT
- Bug fixes affecting functionality
- Blue information-style notification
- **Recommended**: Install at next convenient maintenance window

### OPTIONAL
- New features and enhancements
- Green/success style notification
- Install at your discretion

## File Structure

```
oe-module-medex/
├── src/
│   ├── UpdateManager.php          # Core update logic
│   └── CriticalPatchNotifier.php  # Critical patch notifications
├── public/
│   ├── status.php                 # Shows update status
│   └── update.php                 # Update installation UI
├── migrations/                     # Database migrations
│   ├── 001_create_migrations_table.php
│   └── 002_add_update_cache_columns.php
└── UPDATE_SYSTEM.md               # This file
```

## How It Works

### 1. Version Checking

The `UpdateManager` class checks the MedEx API endpoint every hour:

```
GET https://medexbank.com/index.php?route=api/oemr/module_version
Parameters:
  - module: 'medex'
  - current_version: '1.0.0'
  - openemr_version: '7.0.2'
```

Response includes:
- `latest_version`: Available version
- `priority`: Update priority level
- `download_url`: URL to download update package
- `changelog`: What's new
- `critical_message`: Message for critical updates
- `requires_manual_steps`: Whether manual intervention needed
- `manual_steps`: Instructions for manual steps

### 2. Update Installation Process

When admin clicks "Install Update Now":

1. **Permission Check**: Verify write permissions to module directory
2. **Backup Creation**: Create ZIP backup of current version
   - Stored in: `{site}/documents/medex_backups/`
   - Named: `medex_v{version}_{timestamp}.zip`
3. **Download**: Download update package from MedEx server
4. **Verification**: Verify package integrity and required files
5. **Extraction**: Extract files to module directory (overwrites existing)
6. **Migrations**: Run any database migrations
7. **Cache Clear**: Clear update cache to reflect new version

### 3. Database Migrations

Migrations are PHP files in `/migrations/` directory:

```php
// migrations/003_example_migration.php
<?php
// Add new column to medex_prefs
sqlStatement("ALTER TABLE medex_prefs ADD COLUMN new_field VARCHAR(255) NULL");
error_log('[MedEx Migration] Added new_field to medex_prefs');
```

Migrations are:
- Run automatically during updates
- Tracked in `medex_migrations` table (prevents re-running)
- Named with numeric prefix for ordering: `001_`, `002_`, etc.
- Executed in ascending order

### 4. Critical Patch Push

MedEx Admin can mark an update as CRITICAL. When this happens:

**On Admin Page Load**:
- Check for critical update via `UpdateManager::checkCriticalUpdate()`
- If found, display full-screen modal notification
- Modal cannot be easily dismissed (must acknowledge)
- Pulsing animation draws attention

**Notification Displays**:
- Critical security message from MedEx
- Current vs. required version
- Direct link to update page
- Warning that notification will persist until updated

## API Integration (MedEx Server Side)

MedEx server should implement the version check endpoint:

### Endpoint: `/index.php?route=api/oemr/module_version`

**Request Parameters**:
- `module` (string): Module name, always 'medex'
- `current_version` (string): Currently installed version
- `openemr_version` (string): OpenEMR version for compatibility checks

**Response Format**:
```json
{
  "success": true,
  "latest_version": "1.2.0",
  "priority": "CRITICAL",
  "release_date": "2025-01-22",
  "download_url": "https://medexbank.com/downloads/oe-module-medex-1.2.0.zip",
  "changelog": "- Fixed critical SQL injection vulnerability\n- Improved PDF rendering\n- Added new template options",
  "critical_message": "This update fixes a critical SQL injection vulnerability (CVE-2025-XXXX). Please update immediately.",
  "requires_manual_steps": false,
  "manual_steps": "",
  "min_openemr_version": "7.0.0"
}
```

**Response Fields**:
- `success` (bool): Whether check was successful
- `latest_version` (string): Latest available version
- `priority` (string): One of: CRITICAL, SECURITY, IMPORTANT, OPTIONAL
- `release_date` (string): ISO date when released
- `download_url` (string): Authenticated URL to download update ZIP
- `changelog` (string): Markdown-formatted change description
- `critical_message` (string): Message to display for critical updates
- `requires_manual_steps` (bool): Whether manual intervention needed
- `manual_steps` (string): Instructions for manual steps
- `min_openemr_version` (string): Minimum OpenEMR version required

### Update Package Format

Update packages must be ZIP files with this structure:

```
oe-module-medex-1.2.0.zip
└── oe-module-medex/
    ├── openemr.bootstrap.php      # REQUIRED - contains MODULE_VERSION constant
    ├── moduleConfig.php            # REQUIRED
    ├── src/
    ├── public/
    ├── admin/
    ├── migrations/                 # Optional - new migrations
    │   └── 003_new_migration.php
    └── ... (all other module files)
```

**Critical**: The ZIP must extract to create an `oe-module-medex/` directory that can directly replace the existing module directory.

## File Permissions

For automatic updates to work, the web server must have write permissions:

```bash
# Docker environment
docker exec -it openemr chown -R www-data:www-data /var/www/localhost/htdocs/openemr/interface/modules/custom_modules/oe-module-medex

# Standard Linux
chown -R www-data:www-data /var/www/html/openemr/interface/modules/custom_modules/oe-module-medex
chmod -R 755 /var/www/html/openemr/interface/modules/custom_modules/oe-module-medex
```

If write permissions are not available:
- System will display error message
- Admin can manually download and extract update
- Or system administrator can update file permissions

## Update Workflow Examples

### Example 1: Optional Feature Release

```
MedEx Admin: Releases v1.1.0 with new PDF templates
Priority: OPTIONAL
Behavior:
  - Green notification badge in Module Manager
  - "New Version Available" message
  - Admin can install at convenience
  - No urgent messaging
```

### Example 2: Bug Fix

```
MedEx Admin: Releases v1.0.1 fixing SMS delivery bug
Priority: IMPORTANT
Behavior:
  - Blue notification banner on status page
  - "Important Update Available" message
  - Changelog describes bug fix
  - Admin should install soon
```

### Example 3: Security Improvement

```
MedEx Admin: Releases v1.0.2 with enhanced encryption
Priority: SECURITY
Behavior:
  - Orange warning banner
  - "Security Update Available" message
  - Strong recommendation to update within 24 hours
  - No blocking behavior
```

### Example 4: Critical Security Patch

```
MedEx Admin: Releases v1.0.3 fixing SQL injection
Priority: CRITICAL
Critical Message: "CVE-2025-XXXX: SQL injection vulnerability allows unauthorized database access"
Behavior:
  - Full-screen modal on every admin page load
  - Red pulsing button
  - Cannot be easily dismissed
  - Modal reappears until update installed
  - "CRITICAL SECURITY UPDATE REQUIRED" header
```

## Testing the Update System

### Test Version Check

```php
// In any admin page
require_once(__DIR__ . '/interface/modules/custom_modules/oe-module-medex/src/UpdateManager.php');
$mgr = new \OpenEMR\Modules\MedEx\UpdateManager();
$info = $mgr->checkForUpdates(true); // Force fresh check
print_r($info);
```

### Test Critical Notification

```php
// In any admin page
require_once(__DIR__ . '/interface/modules/custom_modules/oe-module-medex/src/CriticalPatchNotifier.php');
\OpenEMR\Modules\MedEx\CriticalPatchNotifier::checkAndDisplay();
```

### Test Migration

```php
// Create test migration
file_put_contents(
    '/path/to/migrations/999_test_migration.php',
    "<?php\nerror_log('[Test Migration] This is a test');\n"
);

// Run update manager migrations
$mgr->runMigrations('1.0.1');
```

## Security Considerations

1. **Download Authentication**: Update downloads should be authenticated via API token
2. **Package Verification**: Verify ZIP package contains required files before extraction
3. **Backup Before Update**: Always create backup (automatic)
4. **Admin Only**: Only admin/super users can view or install updates
5. **CSRF Protection**: Update installation requires CSRF token
6. **Secure Communication**: All API calls use HTTPS in production
7. **Rollback Support**: If update fails, automatically restore from backup

## Troubleshooting

### "Insufficient Write Permissions" Error

**Cause**: Web server cannot write to module directory

**Solutions**:
1. Fix permissions (recommended):
   ```bash
   chown -R www-data:www-data /path/to/oe-module-medex
   chmod -R 755 /path/to/oe-module-medex
   ```
2. Manual update: Download ZIP from MedEx, extract to module directory
3. Container rebuild: If using Docker, rebuild with correct permissions

### "Failed to Download Update Package"

**Cause**: Cannot reach MedEx server or invalid download URL

**Solutions**:
1. Check network connectivity to medexbank.com
2. Verify API token is valid
3. Check firewall rules allow HTTPS to MedEx server
4. Review PHP error log for cURL errors

### "Update Package Verification Failed"

**Cause**: Downloaded ZIP is corrupt or missing required files

**Solutions**:
1. Retry download (may have been interrupted)
2. Contact MedEx support if persists
3. Manually download from MedEx portal

### "Migration Failed"

**Cause**: Database migration encountered an error

**Solutions**:
1. Check MySQL error log for details
2. Verify database user has ALTER TABLE privileges
3. Restore from backup if database is corrupt
4. Contact MedEx support with migration name and error

### Update Installed But Still Shows Old Version

**Cause**: Cache not cleared or update didn't complete

**Solutions**:
1. Clear update cache:
   ```sql
   UPDATE medex_prefs SET module_update_cache = NULL, module_update_checked = NULL;
   ```
2. Hard refresh browser (Ctrl+Shift+R)
3. Check openemr.bootstrap.php has new MODULE_VERSION constant

## Rollback System

### Overview

Every update automatically creates a backup before installation. These backups can be used to rollback to previous versions if issues occur.

### Accessing Backup Manager

Navigate to: **Modules → MedEx Admin → Backups & Rollback**

### Features

1. **View All Backups**: See list of all available backups with versions and dates
2. **Rollback**: One-click rollback to any previous version
3. **Download**: Download backup ZIP files for safekeeping
4. **Delete**: Remove old backups to free up space
5. **Create Backup**: Manually create backup of current version

### Rollback Process

When you rollback:

1. **Current Version Backed Up**: System creates backup of current version first (so you can go forward again)
2. **Files Restored**: All module files replaced with selected backup version
3. **Cache Cleared**: Update cache cleared to reflect rolled-back version
4. **Confirmation**: Shows which version you rolled back to

### Important Notes

**Database Migrations**: Rolling back module files does NOT automatically rollback database changes. If a newer version added database columns or tables, they will remain after rollback. This is usually safe, but be aware of it.

**Forward Again**: You can always roll forward to a newer version after rolling back, since the system backs up before every rollback.

**Automatic Backups**: Backups are created:
- Before every update installation
- Before every rollback
- Manually via "Create Backup Now" button

### Backup Storage

Backups are stored in: `{site}/documents/medex_backups/`

Filename format: `medex_v{version}_{timestamp}.zip`

Example: `medex_v1.0.0_2025-01-22_14-30-45.zip`

### Rollback Scenarios

**Scenario 1: Update Causes Issues**
```
1. Update to v1.2.0 (backup v1.1.0 created automatically)
2. Discover PDF rendering broken in v1.2.0
3. Go to Modules → MedEx Admin → Backups & Rollback
4. Click "Rollback" on v1.1.0 backup
5. System backs up v1.2.0, then restores v1.1.0
6. Module now running v1.1.0 again
7. Wait for v1.2.1 bug fix release
8. Update to v1.2.1 when ready
```

**Scenario 2: Testing New Features**
```
1. Create manual backup of v1.0.0 (production)
2. Update to v1.1.0 (testing new features)
3. Test features in development environment
4. If issues found, rollback to v1.0.0
5. Report issues to MedEx support
6. Wait for v1.1.1 with fixes
```

**Scenario 3: Accidental Update**
```
1. Accidentally installed v2.0.0 (major version)
2. Not ready for breaking changes
3. Rollback to v1.5.0 immediately
4. Plan migration to v2.0.0 properly
5. Update when ready
```

## Future Enhancements

Potential additions to the update system:

1. **Scheduled Updates**: Allow admins to schedule updates for maintenance windows
2. **Multi-Site Updates**: Update all sites in a multi-site installation at once
3. **Delta Updates**: Download only changed files instead of full ZIP
4. **Update History**: Track all updates applied with timestamps
5. **Pre-Update Checks**: Verify system requirements before downloading
6. **Email Notifications**: Alert admins when critical updates are available
7. **Auto-Update**: Automatically install PATCH version updates (opt-in)
8. **Database Migration Rollback**: Track and rollback database changes (complex)

## Support

For questions or issues with the update system:
- **Email**: support@medexbank.com
- **Documentation**: https://medexbank.com/docs/openemr-module-updates
- **GitHub Issues**: (if applicable)
