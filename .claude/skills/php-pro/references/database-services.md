# OpenEMR Database & Service Patterns

## ADODB (OpenEMR's Database Layer)

OpenEMR uses ADODB, not Doctrine or Eloquent. The global `$GLOBALS['adodb']['db']` provides the connection, but prefer using `sqlQuery()` and `sqlStatement()` helper functions from `/library/`.

### Basic Queries

```php
<?php

// Select with parameterized query (ALWAYS use ? placeholders)
$result = sqlQuery(
    "SELECT pid, fname, lname FROM patient_data WHERE pid = ?",
    [$patientId]
);
// Returns: single row as associative array, or false

// Select multiple rows
$results = sqlStatement(
    "SELECT pid, fname, lname FROM patient_data WHERE status = ? ORDER BY lname",
    ['active']
);
while ($row = sqlFetchArray($results)) {
    // Process each row
    echo $row['fname'] . ' ' . $row['lname'];
}

// Insert
sqlStatement(
    "INSERT INTO audit_master (patient_id, event_type, event_data, created_at) VALUES (?, ?, ?, NOW())",
    [$patientId, 'safety_check', json_encode($checkResult)]
);

// Get last insert ID
$newId = sqlQuery("SELECT LAST_INSERT_ID() as id");

// Update
sqlStatement(
    "UPDATE patient_data SET status = ? WHERE pid = ?",
    ['inactive', $patientId]
);
```

### Important: SQL Injection Prevention

```php
<?php

// CORRECT — parameterized
$row = sqlQuery("SELECT * FROM patient_data WHERE pid = ?", [$pid]);

// WRONG — never concatenate user input
// $row = sqlQuery("SELECT * FROM patient_data WHERE pid = " . $pid);
// $row = sqlQuery("SELECT * FROM patient_data WHERE pid = '$pid'");
```

## BaseService Pattern

New services in `/src/Services/` should extend `BaseService`:

```php
<?php

/**
 * Service for safety check audit logging
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

class SafetyAuditService extends BaseService
{
    public const TABLE_NAME = "safety_audit_log";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * Log a safety check result
     *
     * @param string $patientId Patient PID
     * @param string $drugName Proposed drug
     * @param string $severity Check result severity
     * @param bool $blocked Whether prescription was blocked
     * @return int Insert ID
     */
    public function logCheck(
        string $patientId,
        string $drugName,
        string $severity,
        bool $blocked
    ): int {
        $result = sqlQuery(
            "INSERT INTO " . self::TABLE_NAME .
            " (patient_id, drug_name, severity, blocked, check_date) VALUES (?, ?, ?, ?, NOW())",
            [$patientId, $drugName, $severity, $blocked ? 1 : 0]
        );

        return (int) sqlQuery("SELECT LAST_INSERT_ID() as id")['id'];
    }

    /**
     * Get safety check history for a patient
     *
     * @param string $patientId Patient PID
     * @param int $limit Max results
     * @return array
     */
    public function getPatientHistory(string $patientId, int $limit = 20): array
    {
        $results = [];
        $stmt = sqlStatement(
            "SELECT * FROM " . self::TABLE_NAME .
            " WHERE patient_id = ? ORDER BY check_date DESC LIMIT ?",
            [$patientId, $limit]
        );

        while ($row = sqlFetchArray($stmt)) {
            $results[] = $row;
        }

        return $results;
    }
}
```

## table.sql (Module Database Schema)

Custom modules define their tables in `table.sql`, which runs on module install:

```sql
-- Safety Sentinel audit log table
CREATE TABLE IF NOT EXISTS `safety_audit_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `patient_id` VARCHAR(255) NOT NULL,
    `drug_name` VARCHAR(255) NOT NULL,
    `severity` ENUM('safe','minor','moderate','major','contraindicated','unknown') NOT NULL DEFAULT 'unknown',
    `blocked` TINYINT(1) NOT NULL DEFAULT 0,
    `narrative` TEXT,
    `check_date` DATETIME NOT NULL,
    `user_id` INT(11) DEFAULT NULL COMMENT 'OpenEMR user who initiated check',
    PRIMARY KEY (`id`),
    KEY `idx_patient` (`patient_id`),
    KEY `idx_severity` (`severity`),
    KEY `idx_check_date` (`check_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Using Existing OpenEMR Services

OpenEMR has built-in services you can use — don't rewrite these:

```php
<?php

use OpenEMR\Services\PatientService;
use OpenEMR\Services\PrescriptionService;
use OpenEMR\Services\AllergyIntoleranceService;

// Patient lookup
$patientService = new PatientService();
$patient = $patientService->findByPid($pid);

// Allergies
$allergyService = new AllergyIntoleranceService();
$allergies = $allergyService->getAll(['lists.pid' => $pid]);
```

## ACL (Access Control)

Always check permissions before accessing patient data:

```php
<?php

use OpenEMR\Common\Acl\AclMain;

// Check if current user can access patient medical records
if (!AclMain::aclCheckCore('patients', 'med')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Common ACL checks for a safety agent module
// 'patients', 'med'    — access to medical records
// 'patients', 'rx'     — access to prescriptions
// 'admin', 'super'     — superuser access
```

## Key Database Tables (Relevant to Safety Sentinel)

| Table | Purpose |
|-------|---------|
| `patient_data` | Demographics (fname, lname, DOB, sex, pid) |
| `lists` | Problems, allergies, medications (type column distinguishes) |
| `prescriptions` | Prescription records |
| `form_vitals` | Vital signs |
| `audit_master` | Audit trail — use for safety check logging |
| `modules` | Registered modules |
| `module_configuration` | Module settings |

## Gotchas

- **No ORM** — ADODB is a thin query wrapper. Write SQL directly.
- **`sqlQuery()` returns one row** — Use `sqlStatement()` + `sqlFetchArray()` for multiple rows.
- **Global DB connection** — You don't create connections. The connection is established at bootstrap.
- **No migrations** — Schema changes go in `table.sql` (install) or `sql/` upgrade scripts.
- **Table prefixes** — OpenEMR doesn't use table prefixes by default, but some installs may.