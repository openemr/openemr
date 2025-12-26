# Vietnamese PT Database Migrations

**Author:** Dang Tran <tqvdang@msn.com>
**Version:** 1.0.0
**Last Updated:** 2025-01-22

## Overview

This directory contains database migrations for the Vietnamese Physiotherapy (PT) module. Migrations add performance indexes, foreign key constraints, and optimization features to the PT tables.

## Migration Files

### Core Migrations

| Migration | Description | Impact |
|-----------|-------------|--------|
| `000_migration_schema.sql` | Migration tracking table and procedures | Required (run first) |
| `001_add_indexes.sql` | 50+ performance indexes for PT tables | High performance gain |
| `002_add_foreign_keys.sql` | 15+ referential integrity constraints | Data integrity |

### Rollback Files

| File | Purpose |
|------|---------|
| `001_add_indexes_rollback.sql` | Remove all indexes added by 001 |
| `002_add_foreign_keys_rollback.sql` | Remove all foreign keys added by 002 |

### Documentation

| File | Content |
|------|---------|
| `README.md` | This file - migration instructions |
| `PERFORMANCE_NOTES.md` | Detailed performance analysis and query optimization guide |

## Quick Start

### Using the Migration Script (Recommended)

```bash
cd /home/dang/dev/openemr/sql/migrations/vietnamese_pt

# Apply all migrations
./apply_migrations.sh

# Apply specific migration
./apply_migrations.sh 001

# Rollback specific migration
./apply_migrations.sh rollback 001

# Check migration status
./apply_migrations.sh status
```

### Manual Application (Advanced)

```bash
# 1. Apply migration tracking schema first
mysql -u openemr -p openemr < 000_migration_schema.sql

# 2. Apply indexes
mysql -u openemr -p openemr < 001_add_indexes.sql

# 3. Apply foreign keys
mysql -u openemr -p openemr < 002_add_foreign_keys.sql

# Rollback if needed
mysql -u openemr -p openemr < 002_add_foreign_keys_rollback.sql
mysql -u openemr -p openemr < 001_add_indexes_rollback.sql
```

## Migration Details

### 000: Migration Tracking Schema

**Purpose:** Sets up migration tracking infrastructure

**What it does:**
- Creates `vietnamese_pt_migrations` table
- Adds tracking procedures (CheckMigrationStatus, RecordMigration, RecordRollback)
- Creates migration history view

**Safe to run:** Multiple times (idempotent)

**Required:** Yes (run before other migrations)

### 001: Add Performance Indexes

**Purpose:** Optimize query performance for PT tables

**What it adds:**
- 50+ indexes across 8 PT tables
- Composite indexes for complex queries
- Covering indexes for frequently-used queries

**Performance impact:**
- Query speed: 10-50x faster (see PERFORMANCE_NOTES.md)
- INSERT/UPDATE: ~1-5% slower (negligible)
- Disk space: +20-30% for index storage

**Safe to rollback:** Yes (via 001_add_indexes_rollback.sql)

**Tables affected:**
- pt_assessments_bilingual
- pt_exercise_prescriptions
- pt_outcome_measures
- pt_treatment_plans
- pt_treatment_sessions
- pt_assessment_templates
- vietnamese_medical_terms
- vietnamese_insurance_info

### 002: Add Foreign Key Constraints

**Purpose:** Enforce referential integrity between tables

**What it adds:**
- 15+ foreign key constraints
- Cascading delete rules
- Automatic cleanup of orphaned records

**Data integrity improvements:**
- Prevents orphaned exercise prescriptions
- Automatic cleanup when assessments deleted
- Preserves audit trail when users deleted

**Foreign key strategies:**
- RESTRICT: Prevents deletion of patients with PT records
- CASCADE: Auto-deletes child records (exercises, outcomes)
- SET NULL: Preserves records but clears reference (therapist deleted)

**Safe to rollback:** Yes (via 002_add_foreign_keys_rollback.sql)

**Performance impact:**
- DELETE operations: Slightly slower due to cascade checking
- SELECT operations: No impact (potential improvement from better query plans)
- INSERT/UPDATE: ~1-5% slower (constraint validation)

## Migration Status Checking

### Check if migration applied

```sql
-- Via stored procedure
CALL CheckMigrationStatus('001_add_indexes');

-- Via direct query
SELECT migration_id, status, applied_at
FROM vietnamese_pt_migrations
WHERE migration_id = '001_add_indexes';
```

### View migration history

```sql
-- All migrations
SELECT * FROM vietnamese_pt_migration_history;

-- Applied migrations only
SELECT * FROM vietnamese_pt_migrations
WHERE status = 'applied'
ORDER BY applied_at DESC;
```

## Production Deployment

### Pre-Deployment Checklist

- [ ] Backup database before applying migrations
- [ ] Test migrations on development/staging environment
- [ ] Review PERFORMANCE_NOTES.md for expected impact
- [ ] Schedule during low-traffic period (indexes take time on large tables)
- [ ] Monitor database size (indexes add 20-30% overhead)
- [ ] Verify sufficient disk space

### Deployment Steps

#### 1. Create Database Backup

```bash
# Full backup
mysqldump -u openemr -p openemr > openemr_backup_before_pt_migrations.sql

# PT tables only backup
mysqldump -u openemr -p openemr \
  pt_assessments_bilingual \
  pt_exercise_prescriptions \
  pt_outcome_measures \
  pt_treatment_plans \
  pt_treatment_sessions \
  pt_assessment_templates \
  vietnamese_medical_terms \
  vietnamese_insurance_info \
  > pt_tables_backup.sql
```

#### 2. Estimate Migration Time

**For small databases (<10K PT records):**
- 000: <1 second
- 001: 5-30 seconds
- 002: 2-10 seconds
- Total: ~1 minute

**For medium databases (10K-100K PT records):**
- 000: <1 second
- 001: 1-5 minutes
- 002: 30 seconds - 2 minutes
- Total: ~5-7 minutes

**For large databases (>100K PT records):**
- 000: <1 second
- 001: 5-30 minutes (FULLTEXT indexes are expensive)
- 002: 2-10 minutes
- Total: ~15-40 minutes

#### 3. Apply Migrations

```bash
# Using script (recommended)
cd /home/dang/dev/openemr/sql/migrations/vietnamese_pt
./apply_migrations.sh

# Manual application
mysql -u openemr -p openemr < 000_migration_schema.sql
mysql -u openemr -p openemr < 001_add_indexes.sql
mysql -u openemr -p openemr < 002_add_foreign_keys.sql
```

#### 4. Verify Application

```bash
# Check migration status
mysql -u openemr -p openemr -e "SELECT * FROM vietnamese_pt_migration_history;"

# Verify indexes created
mysql -u openemr -p openemr -e "
  SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = 'openemr'
  AND TABLE_NAME LIKE 'pt_%'
  AND INDEX_NAME NOT IN ('PRIMARY')
  ORDER BY TABLE_NAME, INDEX_NAME;"

# Verify foreign keys created
mysql -u openemr -p openemr -e "
  SELECT TABLE_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME
  FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = 'openemr'
  AND TABLE_NAME LIKE 'pt_%'
  AND REFERENCED_TABLE_NAME IS NOT NULL;"
```

#### 5. Post-Deployment Verification

```bash
# Analyze tables for accurate statistics
mysql -u openemr -p openemr -e "
  ANALYZE TABLE pt_assessments_bilingual;
  ANALYZE TABLE pt_exercise_prescriptions;
  ANALYZE TABLE pt_outcome_measures;
  ANALYZE TABLE pt_treatment_plans;
  ANALYZE TABLE pt_treatment_sessions;"

# Check index usage with EXPLAIN
mysql -u openemr -p openemr -e "
  EXPLAIN SELECT * FROM pt_assessments_bilingual
  WHERE patient_id = 1
  ORDER BY assessment_date DESC;"
```

#### 6. Monitor Performance

```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 0.1;  -- Log queries >100ms

-- Monitor for 1-2 weeks
-- Check: mysql.slow_log or slow query log file
```

## Rollback Procedures

### Rollback Single Migration

```bash
# Using script
./apply_migrations.sh rollback 002

# Manual
mysql -u openemr -p openemr < 002_add_foreign_keys_rollback.sql
```

### Rollback All Migrations

```bash
# Rollback in reverse order (important!)
mysql -u openemr -p openemr < 002_add_foreign_keys_rollback.sql
mysql -u openemr -p openemr < 001_add_indexes_rollback.sql
```

### Restore from Backup (Last Resort)

```bash
# Stop application first
# Restore full backup
mysql -u openemr -p openemr < openemr_backup_before_pt_migrations.sql

# Verify restoration
mysql -u openemr -p openemr -e "SELECT COUNT(*) FROM pt_assessments_bilingual;"
```

## Common Issues & Solutions

### Issue: Foreign Key Constraint Fails

**Symptom:**
```
ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails
```

**Cause:** Orphaned records in child tables (e.g., exercises referencing non-existent assessments)

**Solution:**
```sql
-- Find orphaned exercise prescriptions
SELECT e.id, e.patient_id, e.assessment_id
FROM pt_exercise_prescriptions e
LEFT JOIN pt_assessments_bilingual a ON e.assessment_id = a.id
WHERE e.assessment_id IS NOT NULL AND a.id IS NULL;

-- Fix: Either delete orphaned records or set assessment_id to NULL
UPDATE pt_exercise_prescriptions
SET assessment_id = NULL
WHERE assessment_id NOT IN (SELECT id FROM pt_assessments_bilingual);

-- Then retry migration
mysql -u openemr -p openemr < 002_add_foreign_keys.sql
```

### Issue: Index Creation Takes Too Long

**Symptom:** Migration 001 runs for >30 minutes on large tables

**Cause:** FULLTEXT indexes are expensive on large text columns

**Solution:**
1. Run migration during off-peak hours
2. Consider skipping FULLTEXT indexes if Vietnamese text search not critical:
   ```sql
   -- Comment out FULLTEXT index creation in 001_add_indexes.sql
   -- Lines with: FULLTEXT `idx_...`
   ```
3. Add FULLTEXT indexes later in batches

### Issue: Disk Space Full During Migration

**Symptom:** Migration fails with "disk full" error

**Cause:** Indexes require 20-30% additional space

**Solution:**
1. Check available space: `df -h`
2. Free up space or expand disk
3. Retry migration

### Issue: Migration Applied But Not Tracked

**Symptom:** Indexes exist but migration table shows no record

**Cause:** Manual application without running migration tracking schema

**Solution:**
```sql
-- Manually record migration
CALL RecordMigration(
    '001_add_indexes',
    'Add Performance Indexes',
    'Added 50+ indexes manually',
    NULL,
    'admin'
);
```

## Best Practices

### 1. Always Backup Before Migrations

```bash
# Daily backups during migration testing
mysqldump -u openemr -p openemr | gzip > backups/openemr_$(date +%Y%m%d).sql.gz
```

### 2. Test on Development First

```bash
# Copy production data to dev
mysqldump -u openemr -p openemr_prod | mysql -u openemr -p openemr_dev

# Run migrations on dev
cd /home/dang/dev/openemr/sql/migrations/vietnamese_pt
./apply_migrations.sh

# Test application functionality
# Verify performance improvements
```

### 3. Run ANALYZE After Migrations

```sql
-- Update table statistics for query optimizer
ANALYZE TABLE pt_assessments_bilingual;
ANALYZE TABLE pt_exercise_prescriptions;
ANALYZE TABLE pt_outcome_measures;
```

### 4. Monitor Query Performance

```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 0.1;

-- Review after 1 week
SELECT * FROM mysql.slow_log WHERE db = 'openemr' ORDER BY query_time DESC LIMIT 20;
```

### 5. Keep Migration History

```sql
-- Export migration history for documentation
SELECT * FROM vietnamese_pt_migration_history
INTO OUTFILE '/tmp/pt_migration_history.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

## Support & Troubleshooting

### Get Help

1. Check `PERFORMANCE_NOTES.md` for query optimization details
2. Review migration SQL comments for detailed explanations
3. Check OpenEMR logs: `/var/log/openemr/`
4. Review MySQL error log

### Report Issues

When reporting migration issues, include:
- Database version: `SELECT VERSION();`
- Table sizes: `SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA='openemr' AND TABLE_NAME LIKE 'pt_%';`
- Migration status: `SELECT * FROM vietnamese_pt_migration_history;`
- Error messages from MySQL error log

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2025-01-22 | Initial migration framework with indexes and foreign keys |

## License

GNU General Public License v3 - See OpenEMR LICENSE file
