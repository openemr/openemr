# MedEx Module Installation Guide

## ⚠️ CRITICAL: Module Registration Fix Required

**IMPORTANT:** Due to how OpenEMR's Module Manager initially registers modules, the database registration must be fixed after EVERY fresh installation. This is a one-time SQL fix per installation.

**Symptoms of incorrect registration:**
- Module Manager shows "Enable" button even when module is already enabled (should show "Disable")
- Module name shows as "MedEx" instead of "MedEx Communication Platform"
- Help button may not work correctly

**Why this happens:**
OpenEMR's Module Manager uses the directory name for initial registration. The module's `ModuleManagerListener.php` now corrects this automatically during install (as of 2026-01-29), but existing installations need the SQL fix run once.

---

## Method 1: Via OpenEMR UI (Recommended)

1. **Navigate to Module Manager:**
   ```
   Administration → Modules → Manage Modules
   ```

2. **Install the module:**
   - Find "oe-module-medex" in the list
   - Click "Unregister" (if already registered)
   - Click "Register"
   - Click "Install"
   - Click "Enable"

3. **RUN THE SQL FIX (REQUIRED):**
   - See "Method 2: Fix Database Registration" below
   - This MUST be done even if installation appears successful
   - Verifies proper "Disable" button shows in Module Manager

4. **Verify installation:**
   - Refresh your browser (Ctrl+F5 or Cmd+Shift+R)
   - Look for "MedEx Communication Platform" in Module Manager (not just "MedEx")
   - Module Manager should show "Disable" button (not "Enable")
   - Look for "MedEx Admin" submenu under Modules menu in top navigation

## Method 2: Fix Database Registration (REQUIRED AFTER INSTALL)

**THIS STEP IS MANDATORY** - Run this SQL fix after EVERY fresh installation or if you see "Enable" when module is already enabled:

### Option A: Using Docker

```bash
# From OpenEMR root directory
cd docker/development-easy

# Run the fix script
docker compose exec openemr mysql -u root -proot openemr < \
  ../../interface/modules/custom_modules/oe-module-medex/fix_module_registration.sql
```

### Option B: Using MySQL Command Line

```bash
# From OpenEMR root directory
mysql -u openemr_user -p openemr < \
  interface/modules/custom_modules/oe-module-medex/fix_module_registration.sql
```

### Option C: Via phpMyAdmin

1. Open phpMyAdmin (usually http://localhost:8310 for Docker)
2. Select the `openemr` database
3. Go to SQL tab
4. Copy/paste the contents of `fix_module_registration.sql`
5. Click "Go"

## Method 3: Manual SQL Commands

If you prefer to run commands individually:

```sql
-- Connect to OpenEMR database
USE openemr;

-- Fix module type
UPDATE modules
SET mod_type = 'custom',
    mod_ui_name = 'MedEx Communication Platform',
    mod_ui_active = 1
WHERE mod_directory = 'oe-module-medex';

-- Enable MedEx globally
INSERT INTO globals (gl_name, gl_index, gl_value)
VALUES ('medex_enable', 0, '1')
ON DUPLICATE KEY UPDATE gl_value = '1';

-- Set API host (use 'localhost' for development)
INSERT INTO globals (gl_name, gl_index, gl_value)
VALUES ('medex_api_host', 0, 'MedExBank.com')
ON DUPLICATE KEY UPDATE gl_value = IF(gl_value = '', 'MedExBank.com', gl_value);

-- Verify
SELECT mod_name, mod_type, mod_active FROM modules WHERE mod_directory = 'oe-module-medex';
```

## Verify Installation

After running any of the above methods:

1. **Clear browser cache** (Ctrl+Shift+Del or Cmd+Shift+Del)
2. **Refresh the page** (Ctrl+F5 or Cmd+Shift+R)
3. **Check for "MedEx" menu tab** in the top navigation
4. **Click MedEx → Messages** to verify it loads

## Expected Menu Structure

When properly installed, you should see:

```
Top Menu Bar:
├── [Other tabs...]
└── MedEx
    ├── Messages
    ├── SMS Bot
    └── Patient Tracker
```

## Configuration

After installation, configure MedEx settings:

1. Go to: **Administration → Globals → Connectors**
2. Find the MedEx section:
   - `medex_enable`: Should be `1` (enabled)
   - `medex_api_host`: Set to `MedExBank.com` (production) or `localhost` (development)

## Troubleshooting

### Menu Still Not Showing

1. **Check module status in database:**
   ```sql
   SELECT mod_name, mod_directory, mod_type, mod_active, mod_ui_active
   FROM modules
   WHERE mod_directory = 'oe-module-medex';
   ```

   Should show:
   - `mod_type` = `custom`
   - `mod_active` = `1`
   - `mod_ui_active` = `1`

2. **Check PHP error log:**
   ```bash
   # Docker
   docker compose exec openemr tail -f /var/log/apache2/error.log

   # Or
   tail -f /var/log/apache2/error.log
   ```

3. **Check OpenEMR logs:**
   ```bash
   # Docker
   docker compose exec openemr /root/devtools php-log
   ```

4. **Verify bootstrap file exists:**
   ```bash
   ls -la interface/modules/custom_modules/oe-module-medex/openemr.bootstrap.php
   ```

### Module Shows but Menu Doesn't Work

Check that the following files exist:
- `interface/main/messages/messages.php`
- `interface/patient_tracker/patient_tracker.php`

These are core OpenEMR files that the menu links to.

## Development/Testing

For local development against a localhost MedEx instance:

```sql
UPDATE globals SET gl_value = 'localhost' WHERE gl_name = 'medex_api_host';
```

## Uninstallation

To completely remove the module:

1. **Via UI:**
   - Administration → Modules → Manage Modules
   - Find "oe-module-medex"
   - Click "Disable"
   - Click "Uninstall"
   - Click "Unregister"

2. **Manually remove directory:**
   ```bash
   rm -rf interface/modules/custom_modules/oe-module-medex
   ```

## Support

- **GitHub Issues**: https://github.com/openemr/openemr/issues
- **MedEx Support**: support@MedExBank.com
