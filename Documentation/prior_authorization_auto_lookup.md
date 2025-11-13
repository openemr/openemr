# Prior Authorization Auto-Lookup

## Overview

The Prior Authorization Auto-Lookup feature automatically populates prior authorization numbers in billing claims by matching CPT codes and service dates against a database table of stored authorizations.

## Feature Details

### Priority Order

When generating a claim, the system looks for prior authorization in this order:

1. **Manual Entry** - Explicitly entered `prior_auth_number` in `form_misc_billing_options`
2. **Auto-Lookup** - Query `module_prior_authorizations` table by CPT code and service date

### Database Schema

The feature requires the `module_prior_authorizations` table. See `sql/module_prior_authorizations_schema.sql` for the complete schema.

#### Required Columns

- `id` - Primary key
- `pid` - Patient ID (references `patient_data.pid`)
- `auth_num` - Authorization number (VARCHAR)
- `start_date` - Authorization effective date (DATE, nullable)
- `end_date` - Authorization expiration date (DATE, nullable)
- `cpt` - CPT code or comma-separated list (VARCHAR)

#### Optional Columns

- `created_date` - Record creation timestamp
- `modified_date` - Last modification timestamp
- `created_by` - User who created the record
- `notes` - Additional notes

### Recommended Indexes

For optimal query performance:

```sql
KEY idx_pid_dates (pid, start_date, end_date)
KEY idx_cpt (cpt(191))
KEY idx_auth_num (auth_num(191))
KEY idx_date_range (start_date, end_date)
```

## Usage

### Manual Table Creation

Execute the SQL schema file to create the table:

```bash
mysql -u username -p database_name < sql/module_prior_authorizations_schema.sql
```

### Adding Authorizations

```sql
INSERT INTO module_prior_authorizations (pid, auth_num, start_date, end_date, cpt, notes)
VALUES (123, 'AUTH-2025-001', '2025-01-01', '2025-12-31', '99213,99214,99215', 'Office visits');
```

### CPT Code Matching

The system supports:

- **Single CPT**: `99213`
- **Comma-separated list**: `99213,99214,99215` (spaces optional)

The query uses MySQL's `FIND_IN_SET()` function for efficient comma-separated value matching.

### Date Range Matching

- If `start_date` is NULL, authorization is effective from any past date
- If `end_date` is NULL, authorization never expires
- Service date must fall between `start_date` and `end_date` (inclusive)

## Implementation Details

### Code Location

- `src/Billing/Claim.php` - Main implementation
  - `priorAuth()` - Gets prior auth for the entire claim
  - `priorAuthForProckey($prockey)` - Gets prior auth for specific procedure line
  - `priorAuthTableExists()` - Validates table and schema (cached)
  - `priorAuthFromModuleForCpt()` - Queries authorization by CPT

### Dependencies

- `OpenEMR\Common\Database\QueryUtils` - Database operations
- `OpenEMR\Common\Logging\SystemLogger` - Error logging

### Security

- **SQL Injection Protection**: Uses parameterized queries via `QueryUtils::querySingleRow()`
- **Input Sanitization**: LIKE wildcards (% and _) are escaped in CPT codes
- **Error Handling**: Database errors are logged but don't fail claim generation

### Performance

- **Caching**: Table existence check is cached per request using static variable
- **Efficient Query**: Uses `FIND_IN_SET()` instead of multiple LIKE statements
- **Index Usage**: Compound indexes on (pid, dates) and (cpt) for fast lookups
- **LIMIT 1**: Returns most recent authorization when multiple matches exist

## Testing

### Manual Testing

1. Create the table using the schema file
2. Insert test authorization:
   ```sql
   INSERT INTO module_prior_authorizations 
   (pid, auth_num, start_date, end_date, cpt) 
   VALUES 
   (1, 'TEST-AUTH-001', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), '99213');
   ```
3. Create a claim for patient_id=1 with CPT 99213
4. Verify the authorization appears in the claim output

### Edge Cases Tested

- Missing table (feature disabled gracefully)
- Missing required columns (feature disabled with debug log)
- NULL start/end dates (matches any date)
- Multiple CPT codes in comma-separated list
- Special characters in CPT codes (escaped)
- Multiple authorizations for same CPT (returns most recent)

## Future Enhancements

- UI for managing authorizations in the admin interface
- Import/export functionality for bulk authorization management
- Authorization usage tracking and reporting
- Expiration warnings and notifications
- Integration with insurance verification systems
- Support for authorization units/visit limits

## Troubleshooting

### Authorization Not Appearing

1. **Check table exists**:
   ```sql
   SHOW TABLES LIKE 'module_prior_authorizations';
   ```

2. **Check schema**:
   ```sql
   SHOW COLUMNS FROM module_prior_authorizations;
   ```

3. **Check authorization data**:
   ```sql
   SELECT * FROM module_prior_authorizations WHERE pid = ? AND cpt LIKE '%99213%';
   ```

4. **Check system logs**:
   ```bash
   tail -f sites/default/documents/logs_and_misc/log_*
   ```
   Look for messages like "Prior auth table missing required column" or "Error fetching prior auth"

### Manual Override

If auto-lookup is not working, users can always manually enter the prior authorization in:
- **Billing** → **Misc Billing Options** → **Prior Auth Number** field

## Related Documentation

- [X12 837P Professional Claims](https://www.x12.org/codes/claim-adjustment-reason-codes/)
- [OpenEMR Database Schema](Documentation/EHI_Export/docs/tables/)
- [QueryUtils API](src/Common/Database/QueryUtils.php)
