# MedEx Module SQL Scripts

This directory contains database installation, upgrade, and maintenance scripts for the MedEx module.

## Installation

### `table.sql`
Executed automatically when the module is installed via OpenEMR's module manager.

**What it does:**
- Creates shared MedEx tables if they don't exist
- Sets up global configuration options
- Safe to run multiple times (uses `CREATE TABLE IF NOT EXISTS`)

**Tables created:**
- `medex_icons` - Campaign status icons (SMS/EMAIL/AVM)
- `medex_outgoing` - Campaign event history
- `medex_prefs` - Practice preferences and API credentials
- `medex_prefs_backup` - Backup of preferences
- `medex_recalls` - Patient recall data

## Uninstallation

### `uninstall.sql`
**NOT automatically executed** by OpenEMR's module manager.

**What it does:**
- Removes module-specific configuration (globals, list_options)
- **Does NOT drop tables** - preserves patient data and shared resources

**Why tables are preserved:**
- Tables may be used by other MedEx modules (legacy, medex3)
- Patient recall data and campaign history are HIPAA-protected
- Practice may need historical data for compliance/auditing

## Complete Removal

### `cleanup_all_medex.sql`
⚠️ **DESTRUCTIVE - Use with extreme caution**

**Before running:**
1. Export data using `export_hipaa_data.sql` (see below)
2. Store backup according to retention policy (typically 7 years)
3. Verify backup was created successfully
4. Get approval from practice administrator/compliance officer

**What it does:**
- Drops ALL MedEx tables
- Removes ALL MedEx global settings
- Deletes ALL patient recall and campaign data

**When to use:**
- All MedEx modules have been uninstalled
- Practice is permanently discontinuing MedEx
- Data has been properly archived

### `export_hipaa_data.sql`
**REQUIRED before running cleanup_all_medex.sql**

**Usage:**
```bash
# Export data with timestamp
mysql -u[user] -p[password] [database] < sql/export_hipaa_data.sql > medex_backup_$(date +%Y-%m-%d).sql

# Example for OpenEMR Docker:
docker exec [container] mysql -uroot -proot openemr < /var/www/localhost/htdocs/openemr/interface/modules/custom_modules/oe-module-medex/sql/export_hipaa_data.sql > medex_backup_2026-01-25.sql
```

**What it exports:**
- Patient recall records (with demographics)
- Campaign communication history
- Practice preferences and configuration
- Icon definitions
- Summary statistics and metadata

**Storage requirements:**
- Store according to practice's data retention policy
- HIPAA typically requires 7 years minimum
- Encrypt if storing outside secure network
- Document export date and reason in compliance log

## Upgrades

### Version-specific upgrade scripts
Format: `X_Y_Z-to-A_B_C_upgrade.sql`

**How OpenEMR handles upgrades:**
1. Module manager detects version change
2. Automatically runs upgrade scripts in order
3. Example: v1.0.0 → v1.2.0 runs:
   - `1_0_0-to-1_1_0_upgrade.sql`
   - `1_1_0-to-1_2_0_upgrade.sql`

**Creating new upgrade scripts:**
1. Name file based on version numbers
2. Include data migration if schema changes
3. Use `ALTER TABLE IF NOT EXISTS` for safety
4. Document changes in module CHANGELOG.md
5. Test on copy of production database first

**Example upgrade script structure:**
```sql
-- Add new column
ALTER TABLE medex_outgoing
  ADD COLUMN IF NOT EXISTS new_field VARCHAR(50) DEFAULT NULL;

-- Migrate existing data
UPDATE medex_outgoing
  SET new_field = 'default_value'
  WHERE new_field IS NULL;

-- Add index for performance
CREATE INDEX IF NOT EXISTS idx_new_field
  ON medex_outgoing(new_field);
```

## Data Retention & HIPAA Compliance

### What data is HIPAA-protected:
- `medex_recalls` - Patient recall schedules
- `medex_outgoing` - Communication history (contains patient interactions)

### What data is NOT HIPAA-protected:
- `medex_icons` - Visual UI elements
- `medex_prefs` - Practice configuration (no patient info)

### Retention requirements:
- **Minimum**: 7 years from last patient contact (HIPAA standard)
- **Recommended**: Check your state regulations (may require longer)
- **Best practice**: Export before ANY cleanup operation

### Audit trail:
Every export includes:
- Export timestamp
- User who ran export
- Database version
- Record counts and date ranges

## Troubleshooting

### Module won't install - tables already exist
**Cause:** Tables from legacy MedEx or another module already present

**Solution:** This is normal and expected. The installer uses `CREATE TABLE IF NOT EXISTS`, so installation will complete successfully.

### Module won't uninstall - foreign key constraints
**Cause:** Other tables may reference MedEx tables

**Solution:** The uninstall script intentionally doesn't drop tables. This is by design to preserve data integrity.

### Need to completely remove MedEx but keep data
**Solution:**
1. Run `export_hipaa_data.sql` to backup
2. Run `uninstall.sql` to remove module registration
3. Keep tables in database (they're small and won't affect performance)
4. Document in practice notes that data is archived in-place

### Upgrading from legacy MedEx to module
**Compatibility:** Tables are identical, no migration needed

**Process:**
1. Disable legacy MedEx in globals (`medex_enable = 0`)
2. Install oe-module-medex
3. Module will use existing tables
4. Enable module (`medex_enable = 1`)
5. Test functionality
6. Remove legacy MedEx code (separate process)

## See Also
- `../moduleConfig.php` - Module version configuration
- `../openemr.bootstrap.php` - Module registration logic
- `../README.md` - Module documentation
