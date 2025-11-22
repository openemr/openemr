# Developer Guide

Complete guide for developers working with or extending the OpenEMR API.

## Table of Contents
- [Overview](#overview)
- [Internal API Usage](#internal-api-usage)
    - [Making Internal API Calls](#making-internal-api-calls)
    - [Authentication in Internal Calls](#authentication-in-internal-calls)
    - [Example Use Cases](#example-use-cases)
- [Multisite Support](#multisite-support)
    - [Multisite Architecture](#multisite-architecture)
    - [Site-Specific Endpoints](#site-specific-endpoints)
    - [Configuration](#configuration)
- [Security Best Practices](#security-best-practices)
    - [SSL/TLS Requirements](#ssltls-requirements)
    - [Token Security](#token-security)
    - [Scope Management](#scope-management)
    - [HIPAA Compliance](#hipaa-compliance)
- [Architecture](#architecture)
    - [Request Flow](#request-flow)
    - [Response Flow](#response-flow)
    - [Component Overview](#component-overview)
- [Adding API Endpoints](#adding-api-endpoints)
    - [Standard API Endpoints](#standard-api-endpoints)
    - [FHIR API Endpoints](#fhir-api-endpoints)
- [Controllers](#controllers)
    - [REST Controllers](#rest-controllers)
    - [FHIR Controllers](#fhir-controllers)
    - [Controller Best Practices](#controller-best-practices)
- [Services](#services)
    - [Service Layer](#service-layer)
    - [FHIR Services](#fhir-services)
    - [Service Best Practices](#service-best-practices)
- [Routing](#routing)
    - [Route Definitions](#route-definitions)
    - [Route Parameters](#route-parameters)
    - [Authorization Checks](#authorization-checks)
- [Validation](#validation)
    - [Validator Components](#validator-components)
    - [Validation Rules](#validation-rules)
    - [Custom Validators](#custom-validators)
- [Testing](#testing)
    - [Unit Tests](#unit-tests)
    - [API Tests](#api-tests)
    - [Testing OAuth Flow](#testing-oauth-flow)
- [Deployment](#deployment)
    - [Production Checklist](#production-checklist)
    - [Performance Optimization](#performance-optimization)
    - [Monitoring](#monitoring)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)

## Overview

This guide is for developers who are:
- **Integrating** with OpenEMR APIs
- **Extending** OpenEMR API functionality
- **Contributing** to OpenEMR API development
- **Operating** OpenEMR in production environments

### Prerequisites

**Knowledge Requirements:**
- PHP 7.4+ (preferably 8.1+)
- RESTful API design
- OAuth 2.0 / OpenID Connect
- FHIR R4 (for FHIR development)
- SQL / Database design
- Git version control

**Development Environment:**
- OpenEMR 7.0+ installation
- PHP development environment
- MySQL/MariaDB database
- Composer (PHP dependency manager)
- Git

## Internal API Usage

OpenEMR supports making API calls from within authenticated sessions, useful for:
- Custom modules
- Internal workflows
- Administrative tools
- Data migrations

### Making Internal API Calls

**Location:** `tests/api/InternalApiTest.php`

This file provides examples of internal API usage patterns.

#### Example: Direct Service Call
```php
<?php
namespace OpenEMR\Tests\Api;

use OpenEMR\Services\PatientService;

class InternalApiExample
{
    public function getPatientData($puuid)
    {
        // Instantiate service directly
        $patientService = new PatientService();

        // Call service method
        $result = $patientService->getOne($puuid);

        // Check for errors
        if (!$result->hasData()) {
            throw new \Exception("Patient not found");
        }

        return $result->getData();
    }
}
```

#### Example: Using Controller
```php
<?php
use OpenEMR\RestControllers\PatientRestController;

// Instantiate controller
$controller = new PatientRestController();

// Call controller method (simulates REST request)
$httpRequest = new \OpenEMR\Common\Http\HttpRestRequest();
$httpRequest->setRequestUserId($userId);
$httpRequest->setRequestUserRole('users');

$response = $controller->getOne($puuid, $httpRequest);

// Process response
$data = json_decode($response->getBody(), true);
```

### Authentication in Internal Calls

**Internal calls bypass OAuth** when made from authenticated sessions.

#### Check User Permissions
```php
<?php
use OpenEMR\Common\Acl\AclMain;

// Check if user has specific permission
$hasAccess = AclMain::aclCheckCore('patients', 'demo');

if (!$hasAccess) {
    throw new \Exception("Insufficient permissions");
}
```

#### Get Current User
```php
<?php
// Get current user ID
$userId = $_SESSION['authUserID'] ?? null;

// Get current user data
$userData = sqlQuery("SELECT * FROM users WHERE id = ?", [$userId]);
```

### Example Use Cases

#### Use Case 1: Custom Patient Report
```php
<?php
namespace OpenEMR\Custom\Reports;

use OpenEMR\Services\PatientService;
use OpenEMR\Services\EncounterService;

class PatientReportGenerator
{
    private $patientService;
    private $encounterService;

    public function __construct()
    {
        $this->patientService = new PatientService();
        $this->encounterService = new EncounterService();
    }

    public function generateReport($puuid, $startDate, $endDate)
    {
        // Get patient data
        $patientResult = $this->patientService->getOne($puuid);
        $patient = $patientResult->getData();

        // Get encounters in date range
        $encountersResult = $this->encounterService->getEncountersForPatient(
            $patient['pid'],
            $startDate,
            $endDate
        );
        $encounters = $encountersResult->getData();

        // Generate report
        return [
            'patient' => $patient,
            'encounters' => $encounters,
            'summary' => $this->calculateSummary($encounters)
        ];
    }

    private function calculateSummary($encounters)
    {
        return [
            'total_encounters' => count($encounters),
            'date_range' => [
                'start' => min(array_column($encounters, 'date')),
                'end' => max(array_column($encounters, 'date'))
            ]
        ];
    }
}
```

#### Use Case 2: Bulk Data Update
```php
<?php
namespace OpenEMR\Custom\DataMigration;

use OpenEMR\Services\PatientService;

class BulkPatientUpdate
{
    private $patientService;

    public function __construct()
    {
        $this->patientService = new PatientService();
    }

    public function updatePatientAddresses($updates)
    {
        $results = [];

        foreach ($updates as $update) {
            try {
                // Prepare update data
                $data = [
                    'street' => $update['street'],
                    'city' => $update['city'],
                    'state' => $update['state'],
                    'postal_code' => $update['postal_code']
                ];

                // Update patient
                $result = $this->patientService->update(
                    $update['puuid'],
                    $data
                );

                $results[] = [
                    'puuid' => $update['puuid'],
                    'success' => true
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'puuid' => $update['puuid'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}
```

## Multisite Support

OpenEMR supports multiple independent sites within a single installation.

### Multisite Architecture

**Directory Structure:**
```
sites/
  ├── default/          # Default site
  │   ├── sqlconf.php   # Database config
  │   └── documents/    # Document storage
  ├── site2/            # Additional site
  │   ├── sqlconf.php
  │   └── documents/
  └── site3/
      ├── sqlconf.php
      └── documents/
```

**Each site has:**
- Separate database
- Separate document storage
- Independent configuration
- Isolated patient data

### Site-Specific Endpoints

API endpoints include site name:

**Standard API:**
```
https://localhost:9300/apis/{site}/api/{resource}
```

**FHIR API:**
```
https://localhost:9300/apis/{site}/fhir/{resource}
```

**OAuth2:**
```
https://localhost:9300/oauth2/{site}/{endpoint}
```

**Examples:**

Default site:
```
https://localhost:9300/apis/default/fhir/Patient
https://localhost:9300/oauth2/default/authorize
```

Alternate site:
```
https://localhost:9300/apis/alternate/fhir/Patient
https://localhost:9300/oauth2/alternate/authorize
```

### Configuration

**Enable Multisite:**

1. Edit `sites/default/sqlconf.php`
2. Set `$allow_multisite_setup = true;`
3. Create additional sites via Setup interface

**Site Selection:**

The site is determined by:
1. **URL path** - `/apis/{site}/`
2. **Default** - If not specified, uses `default`

**Site Context in Code:**
```php
<?php
// Get current site
$site = $_SESSION['site_id'] ?? 'default';

// Site-specific paths
$documentPath = $GLOBALS['OE_SITE_DIR'] . '/documents/';
$sqlConf = $GLOBALS['OE_SITE_DIR'] . '/sqlconf.php';
```

## Security Best Practices

### SSL/TLS Requirements

**Mandatory for Production:**

✅ **Use valid SSL certificates**
```
# DO NOT use self-signed certificates in production
openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -days 365
```

✅ **Configure Apache/Nginx for HTTPS**

**Apache:**
```apache
<VirtualHost *:443>
    ServerName openemr.example.com

    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem

    # Modern SSL configuration
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5
    SSLHonorCipherOrder on

    DocumentRoot /var/www/openemr
    <Directory /var/www/openemr>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:**
```nginx
server {
    listen 443 ssl http2;
    server_name openemr.example.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    # Modern SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    root /var/www/openemr;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

✅ **Redirect HTTP to HTTPS**
```apache
<VirtualHost *:80>
    ServerName openemr.example.com
    Redirect permanent / https://openemr.example.com/
</VirtualHost>
```

### Token Security

**Storage:**

❌ **Never store tokens in:**
- localStorage (XSS vulnerable)
- URL parameters
- Cookies without HttpOnly flag
- Plain text files
- Application logs

✅ **Recommended storage:**
- HttpOnly, Secure cookies (web apps)
- Platform secure storage (mobile)
- Encrypted databases (server-side)

**JavaScript Example:**
```javascript
// BAD - XSS vulnerable
localStorage.setItem('access_token', token);

// GOOD - Use HttpOnly cookie (set by server)
// Or use in-memory storage for SPA
class TokenManager {
    constructor() {
        this.token = null;
    }

    setToken(token) {
        this.token = token;
        // Token only in memory, lost on page refresh
        // Requires refresh token to restore
    }

    getToken() {
        return this.token;
    }
}
```

**Token Transmission:**

✅ **Always use Authorization header**
```http
Authorization: Bearer eyJ0eXAiOiJKV1Qi...
```

❌ **Never put tokens in URL**
```
❌ /api/patient?token=eyJ0eXAiOiJKV1Qi...
```

**Token Validation:**
```php
<?php
// Always validate tokens
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebToken;

function validateToken($bearerToken) {
    try {
        $jwt = new JsonWebToken($bearerToken);
        $jwt->validate();
        return true;
    } catch (\Exception $e) {
        error_log("Token validation failed: " . $e->getMessage());
        return false;
    }
}
```

### Scope Management

**Principle of Least Privilege:**

✅ **Request minimal scopes**
```javascript
// GOOD - Only what's needed
const scopes = [
    'openid',
    'patient/Patient.rs',
    'patient/Observation.rs?category=vital-signs'
];

// BAD - Excessive permissions
const scopes = [
    'openid',
    'patient/*.cruds',  // Too broad
    'user/*.cruds'      // Unnecessary
];
```

**Validate Scopes in Code:**
```php
<?php
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebToken;

function checkScope($token, $requiredScope) {
    $jwt = new JsonWebToken($token);
    $claims = $jwt->getClaims();
    $scopes = explode(' ', $claims['scope'] ?? '');

    return in_array($requiredScope, $scopes);
}

// Usage
if (!checkScope($token, 'patient/Patient.rs')) {
    http_response_code(403);
    echo json_encode(['error' => 'Insufficient scope']);
    exit;
}
```

### HIPAA Compliance

**Protected Health Information (PHI):**

✅ **Encryption at rest**
```sql
-- Encrypt sensitive fields
ALTER TABLE patient_data
ADD COLUMN ssn_encrypted VARBINARY(255);

-- Use AES encryption
UPDATE patient_data
SET ssn_encrypted = AES_ENCRYPT(ss, 'encryption-key');
```

✅ **Audit logging**
```php
<?php
function logApiAccess($userId, $resource, $action) {
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $userId,
        'resource' => $resource,
        'action' => $action,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];

    // Write to audit log
    sqlInsert("INSERT INTO api_audit_log SET " .
        "timestamp = ?, user_id = ?, resource = ?, action = ?, ip_address = ?",
        array_values($log)
    );
}
```

✅ **Access controls**
```php
<?php
use OpenEMR\Common\Acl\AclMain;

function checkPatientAccess($userId, $patientId) {
    // Check if user has access to patient
    $hasAccess = AclMain::aclCheckCore('patients', 'demo', $userId);

    if (!$hasAccess) {
        return false;
    }

    // Additional checks (e.g., care team membership)
    return true;
}
```

**Data Minimization:**

✅ **Return only necessary fields**
```php
<?php
// GOOD - Selective fields
$patient = [
    'uuid' => $row['uuid'],
    'fname' => $row['fname'],
    'lname' => $row['lname']
];

// BAD - All fields including sensitive data
$patient = $row;  // May include SSN, etc.
```

**Breach Notification:**

Implement breach detection:
```php
<?php
function detectPotentialBreach($userId, $accessCount, $timeWindow) {
    // Alert if excessive access
    if ($accessCount > 100 && $timeWindow < 3600) {
        // Send alert to security team
        notifySecurityTeam([
            'user_id' => $userId,
            'access_count' => $accessCount,
            'time_window' => $timeWindow,
            'severity' => 'high'
        ]);
    }
}
```

## Architecture

### Request Flow
```
HTTP Request
    ↓
Web Server (Apache/Nginx)
    ↓
index.php / _rest_routes.inc.php
    ↓
Route Matching
    ↓
Authorization Check (OAuth2)
    ↓
Controller
    ↓
Validator (if POST/PUT/PATCH)
    ↓
Service Component
    ↓
Database Query
    ↓
Data Retrieval
```

### Response Flow
```
Database Result
    ↓
Service Component
    ↓
Data Transformation
    ↓
FHIR Mapping (if FHIR endpoint)
    ↓
Controller
    ↓
RequestControllerHelper
    ↓
JSON Serialization
    ↓
HTTP Response
```

### Component Overview

**Components:**

1. **Routes** (`_rest_routes.inc.php`)
    - Define API endpoints
    - Map URLs to controllers
    - Specify HTTP methods

2. **Controllers** (`src/RestControllers/`)
    - Handle HTTP requests
    - Validate input
    - Call services
    - Format responses

3. **Services** (`src/Services/`)
    - Business logic
    - Data validation
    - Database operations
    - Data transformation

4. **Validators** (`src/Validators/`)
    - Input validation
    - Data integrity checks
    - Rule enforcement

5. **FHIR Services** (`src/Services/FHIR/`)
    - FHIR resource mapping
    - US Core compliance
    - FHIR validation

## Adding API Endpoints

### Standard API Endpoints

**Step 1: Create Service**

`src/Services/MyResourceService.php`:
```php
<?php
namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Validators\ProcessingResult;

class MyResourceService extends BaseService
{
    const TABLE_NAME = 'my_resource_table';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function getAll($search = array())
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME;
        $whereFragment = [];
        $sqlBinds = [];

        // Add search filters
        if (!empty($search['name'])) {
            $whereFragment[] = "name LIKE ?";
            $sqlBinds[] = '%' . $search['name'] . '%';
        }

        if (!empty($whereFragment)) {
            $sql .= " WHERE " . implode(" AND ", $whereFragment);
        }

        $statementResults = QueryUtils::sqlStatementThrowException(
            $sql,
            $sqlBinds
        );

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $processingResult->addData($this->createResultRecordFromDatabaseResult($row));
        }

        return $processingResult;
    }

    public function getOne($uuid)
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE uuid = ?";
        $result = QueryUtils::sqlQueryThrowException($sql, [$uuid]);

        $processingResult = new ProcessingResult();
        if (!empty($result)) {
            $processingResult->addData($this->createResultRecordFromDatabaseResult($result));
        }

        return $processingResult;
    }

    public function insert($data)
    {
        // Validation happens in controller via validator

        // Generate UUID
        $data['uuid'] = \Ramsey\Uuid\Uuid::uuid4()->toString();

        // Build insert query
        $sql = $this->buildInsertColumns($data);
        $results = sqlInsert($sql['sql'], $sql['binds']);

        $processingResult = new ProcessingResult();
        if ($results) {
            $processingResult->addData([
                'uuid' => $data['uuid'],
                'id' => $results
            ]);
        } else {
            $processingResult->addInternalError("Insert failed");
        }

        return $processingResult;
    }

    public function update($uuid, $data)
    {
        // Build update query
        $sql = $this->buildUpdateColumns($data);
        $sql['sql'] .= " WHERE uuid = ?";
        $sql['binds'][] = $uuid;

        $results = sqlStatement($sql['sql'], $sql['binds']);

        $processingResult = new ProcessingResult();
        if ($results) {
            $processingResult->addData(['uuid' => $uuid]);
        } else {
            $processingResult->addInternalError("Update failed");
        }

        return $processingResult;
    }
}
```

**Step 2: Create Controller**

`src/RestControllers/MyResourceRestController.php`:
```php
<?php
namespace OpenEMR\RestControllers;

use OpenEMR\Services\MyResourceService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Validators\MyResourceValidator;

class MyResourceRestController
{
    private $myResourceService;

    public function __construct()
    {
        $this->myResourceService = new MyResourceService();
    }

    public function getAll($search = array())
    {
        $serviceResult = $this->myResourceService->getAll($search);
        return RestControllerHelper::handleProcessingResult($serviceResult, 200);
    }

    public function getOne($uuid)
    {
        $serviceResult = $this->myResourceService->getOne($uuid);
        return RestControllerHelper::handleProcessingResult($serviceResult, 200);
    }

    public function post($data)
    {
        // Validate input
        $validator = new MyResourceValidator();
        $validationResult = $validator->validate($data);

        if (!$validationResult->isValid()) {
            return RestControllerHelper::validationErrorResponse($validationResult);
        }

        // Insert data
        $serviceResult = $this->myResourceService->insert($data);
        return RestControllerHelper::handleProcessingResult($serviceResult, 201);
    }

    public function put($uuid, $data)
    {
        // Validate input
        $validator = new MyResourceValidator();
        $validationResult = $validator->validate($data);

        if (!$validationResult->isValid()) {
            return RestControllerHelper::validationErrorResponse($validationResult);
        }

        // Update data
        $serviceResult = $this->myResourceService->update($uuid, $data);
        return RestControllerHelper::handleProcessingResult($serviceResult, 200);
    }
}
```

**Step 3: Add Routes**

`_rest_routes.inc.php`:
```php
<?php
use OpenEMR\RestControllers\MyResourceRestController;

// Add to existing routes array
"GET /api/myresource" => function () {
    RestConfig::authorization_check("admin", "users");
    $return = (new MyResourceRestController())->getAll($_GET);
    RestConfig::apiLog($return);
    return $return;
},

"GET /api/myresource/:uuid" => function ($uuid) {
    RestConfig::authorization_check("admin", "users");
    $return = (new MyResourceRestController())->getOne($uuid);
    RestConfig::apiLog($return);
    return $return;
},

"POST /api/myresource" => function () {
    RestConfig::authorization_check("admin", "users");
    $data = (array)(json_decode(file_get_contents("php://input")));
    $return = (new MyResourceRestController())->post($data);
    RestConfig::apiLog($return, $data);
    return $return;
},

"PUT /api/myresource/:uuid" => function ($uuid) {
    RestConfig::authorization_check("admin", "users");
    $data = (array)(json_decode(file_get_contents("php://input")));
    $return = (new MyResourceRestController())->put($uuid, $data);
    RestConfig::apiLog($return, $data);
    return $return;
}
```

**Step 4: Add Validator**

`src/Validators/MyResourceValidator.php`:
```php
<?php
namespace OpenEMR\Validators;

class MyResourceValidator extends BaseValidator
{
    public function validate($data)
    {
        $this->resetValidation();

        // Required fields
        $this->validateField(
            'name',
            'name',
            $data,
            true  // required
        );

        // Optional fields with format validation
        $this->validateField(
            'email',
            'email',
            $data,
            false  // not required
        );

        return $this->getValidationResult();
    }
}
```

### FHIR API Endpoints

**Step 1: Create FHIR Service**

`src/Services/FHIR/FhirMyResourceService.php`:
```php
<?php
namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\MyResourceService;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRMyResource;

class FhirMyResourceService extends FhirServiceBase
{
    private $myResourceService;

    public function __construct()
    {
        parent::__construct();
        $this->myResourceService = new MyResourceService();
    }

    public function getAll($search)
    {
        $processingResult = $this->myResourceService->getAll($search);

        if (!$processingResult->hasErrors()) {
            $results = [];
            foreach ($processingResult->getData() as $record) {
                $fhirResource = $this->parseOpenEMRRecord($record);
                $results[] = $fhirResource;
            }
            $processingResult->setData($results);
        }

        return $processingResult;
    }

    public function getOne($uuid)
    {
        $processingResult = $this->myResourceService->getOne($uuid);

        if (!$processingResult->hasErrors() && $processingResult->hasData()) {
            $record = $processingResult->getData()[0];
            $fhirResource = $this->parseOpenEMRRecord($record);
            $processingResult->setData([$fhirResource]);
        }

        return $processingResult;
    }

    public function parseOpenEMRRecord($dataRecord)
    {
        $fhirResource = new FHIRMyResource();

        // Map OpenEMR fields to FHIR resource
        $id = new \OpenEMR\FHIR\R4\FHIRElement\FHIRId();
        $id->setValue($dataRecord['uuid']);
        $fhirResource->setId($id);

        // Add other mappings...

        return $fhirResource;
    }
}
```

**Step 2: Create FHIR Controller**

`src/RestControllers/FHIR/FhirMyResourceRestController.php`:
```php
<?php
namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirMyResourceService;
use OpenEMR\RestControllers\RestControllerHelper;

class FhirMyResourceRestController
{
    private $fhirService;

    public function __construct()
    {
        $this->fhirService = new FhirMyResourceService();
    }

    public function getAll($search)
    {
        $processingResult = $this->fhirService->getAll($search);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    public function getOne($uuid)
    {
        $processingResult = $this->fhirService->getOne($uuid);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }
}
```

**Step 3: Add FHIR Routes**

`_rest_routes.inc.php`:
```php
<?php
use OpenEMR\RestControllers\FHIR\FhirMyResourceRestController;

// Add to FHIR routes
"GET /fhir/MyResource" => function (HttpRestRequest $request) {
    $return = (new FhirMyResourceRestController())->getAll($request->getQueryParams());
    RestConfig::apiLog($return);
    return $return;
},

"GET /fhir/MyResource/:id" => function ($id, HttpRestRequest $request) {
    $return = (new FhirMyResourceRestController())->getOne($id);
    RestConfig::apiLog($return);
    return $return;
}
```

## Controllers

### REST Controllers

**Location:** `src/RestControllers/`

**Purpose:**
- Handle HTTP requests
- Parse request data
- Call service layer
- Format responses

**Base Structure:**
```php
<?php
namespace OpenEMR\RestControllers;

class ExampleRestController
{
    private $service;

    public function __construct()
    {
        $this->service = new ExampleService();
    }

    public function getAll($search = [])
    {
        $result = $this->service->getAll($search);
        return RestControllerHelper::handleProcessingResult($result, 200);
    }

    public function getOne($id)
    {
        $result = $this->service->getOne($id);
        return RestControllerHelper::handleProcessingResult($result, 200);
    }

    public function post($data)
    {
        $validator = new ExampleValidator();
        $validationResult = $validator->validate($data);

        if (!$validationResult->isValid()) {
            return RestControllerHelper::validationErrorResponse($validationResult);
        }

        $result = $this->service->insert($data);
        return RestControllerHelper::handleProcessingResult($result, 201);
    }
}
```

### FHIR Controllers

**Location:** `src/RestControllers/FHIR/`

**Purpose:**
- Handle FHIR requests
- Parse FHIR resources
- Call FHIR services
- Return FHIR bundles

**Example:**
```php
<?php
namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\RestControllers\RestControllerHelper;

class FhirPatientRestController
{
    private $fhirService;

    public function __construct()
    {
        $this->fhirService = new FhirPatientService();
    }

    public function getAll($queryParams)
    {
        $processingResult = $this->fhirService->getAll($queryParams);

        // Returns FHIR Bundle
        return RestControllerHelper::handleFhirProcessingResult(
            $processingResult,
            200,
            FhirRestController::class
        );
    }
}
```

### Controller Best Practices

✅ **Keep controllers thin**
- Minimal logic
- Delegate to services
- Handle HTTP concerns only

✅ **Use RestControllerHelper**
```php
// Consistent response formatting
return RestControllerHelper::handleProcessingResult($result, 200);
```

✅ **Validate input**
```php
// Always validate before processing
$validator = new MyValidator();
$validationResult = $validator->validate($data);

if (!$validationResult->isValid()) {
    return RestControllerHelper::validationErrorResponse($validationResult);
}
```

✅ **Handle errors gracefully**
```php
try {
    $result = $this->service->process($data);
    return RestControllerHelper::handleProcessingResult($result, 200);
} catch (\Exception $e) {
    error_log("Error: " . $e->getMessage());
    return RestControllerHelper::responseHandler(null, ['error' => 'Processing failed'], 500);
}
```

## Services

### Service Layer

**Location:** `src/Services/`

**Purpose:**
- Business logic
- Data access
- Validation
- Transformation

**Base Service:**

All services extend `BaseService`:
```php
<?php
namespace OpenEMR\Services;

abstract class BaseService
{
    protected $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    protected function buildInsertColumns($data)
    {
        $columns = [];
        $binds = [];

        foreach ($data as $key => $value) {
            $columns[] = "`$key`";
            $binds[] = $value;
        }

        $sql = "INSERT INTO " . $this->table .
               " (" . implode(", ", $columns) . ") " .
               " VALUES (" . str_repeat("?, ", count($binds) - 1) . "?)";

        return ['sql' => $sql, 'binds' => $binds];
    }

    protected function buildUpdateColumns($data)
    {
        $set = [];
        $binds = [];

        foreach ($data as $key => $value) {
            $set[] = "`$key` = ?";
            $binds[] = $value;
        }

        $sql = "UPDATE " . $this->table . " SET " . implode(", ", $set);

        return ['sql' => $sql, 'binds' => $binds];
    }
}
```

### FHIR Services

**Location:** `src/Services/FHIR/`

**Purpose:**
- Map OpenEMR data to FHIR
- FHIR validation
- US Core compliance

**Example:**
```php
<?php
namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;

class FhirPatientService extends FhirServiceBase
{
    public function parseOpenEMRRecord($dataRecord)
    {
        $patient = new FHIRPatient();

        // Map ID
        $id = new \OpenEMR\FHIR\R4\FHIRElement\FHIRId();
        $id->setValue($dataRecord['uuid']);
        $patient->setId($id);

        // Map name
        $name = new FHIRHumanName();
        $name->setFamily($dataRecord['lname']);
        $name->setGiven([$dataRecord['fname']]);
        $patient->addName($name);

        // Map other fields...

        return $patient;
    }
}
```

### Service Best Practices

✅ **Return ProcessingResult**
```php
public function getOne($id)
{
    $result = new ProcessingResult();

    try {
        $data = $this->fetchData($id);
        $result->addData($data);
    } catch (\Exception $e) {
        $result->addInternalError($e->getMessage());
    }

    return $result;
}
```

✅ **Separate concerns**
- One service per resource type
- Don't mix FHIR and Standard logic
- Keep database queries in services

✅ **Use transactions for complex operations**
```php
public function updateWithRelated($id, $data, $related)
{
    sqlBeginTrans();

    try {
        $this->update($id, $data);
        $this->updateRelated($id, $related);

        sqlCommitTrans();
        return new ProcessingResult();

    } catch (\Exception $e) {
        sqlRollbackTrans();

        $result = new ProcessingResult();
        $result->addInternalError($e->getMessage());
        return $result;
    }
}
```

## Routing

### Route Definitions

**Location:** `_rest_routes.inc.php`

**Structure:**
```php
<?php
return [
    "METHOD /path" => function ($param) {
        // Authorization
        RestConfig::authorization_check("scope", "acl");

        // Controller call
        $return = (new Controller())->method($param);

        // Logging
        RestConfig::apiLog($return);

        return $return;
    }
];
```

### Route Parameters

**Named parameters:**
```php
"GET /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid) {
    // $puuid and $euuid are extracted from URL
}
```

**Query parameters:**
```php
"GET /api/patient" => function () {
    // Access via $_GET
    $search = $_GET;
}
```

### Authorization Checks

**Scope-based:**
```php
RestConfig::authorization_check("patients", "demo");
```

**Role-based:**
```php
// Check user role
if ($_SESSION['authUser'] !== 'admin') {
    http_response_code(403);
    exit;
}
```

## Validation

### Validator Components

**Location:** `src/Validators/`

**Base Validator:**
```php
<?php
namespace OpenEMR\Validators;

abstract class BaseValidator
{
    private $validationMessages = [];

    protected function resetValidation()
    {
        $this->validationMessages = [];
    }

    protected function validateField($fieldName, $fieldType, $data, $required = false)
    {
        // Check if required field is present
        if ($required && !isset($data[$fieldName])) {
            $this->validationMessages[] = "The $fieldName field is required.";
            return false;
        }

        // Skip validation if field not present and not required
        if (!isset($data[$fieldName])) {
            return true;
        }

        // Type-specific validation
        switch ($fieldType) {
            case 'email':
                if (!filter_var($data[$fieldName], FILTER_VALIDATE_EMAIL)) {
                    $this->validationMessages[] = "The $fieldName must be a valid email address.";
                    return false;
                }
                break;

            case 'date':
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data[$fieldName])) {
                    $this->validationMessages[] = "The $fieldName must be in YYYY-MM-DD format.";
                    return false;
                }
                break;

            case 'uuid':
                if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $data[$fieldName])) {
                    $this->validationMessages[] = "The $fieldName must be a valid UUID.";
                    return false;
                }
                break;
        }

        return true;
    }

    public function getValidationResult()
    {
        return new ValidationResult($this->validationMessages);
    }

    abstract public function validate($data);
}
```

### Validation Rules

**Example validator:**
```php
<?php
namespace OpenEMR\Validators;

class PatientValidator extends BaseValidator
{
    public function validate($data)
    {
        $this->resetValidation();

        // Required fields
        $this->validateField('fname', 'string', $data, true);
        $this->validateField('lname', 'string', $data, true);
        $this->validateField('DOB', 'date', $data, true);
        $this->validateField('sex', 'string', $data, true);

        // Optional fields with validation
        $this->validateField('email', 'email', $data, false);
        $this->validateField('ss', 'ssn', $data, false);

        return $this->getValidationResult();
    }
}
```

### Custom Validators

**Add custom validation:**
```php
<?php
protected function validateSSN($ssn)
{
    // Custom SSN format validation
    if (!preg_match('/^\d{3}-\d{2}-\d{4}$/', $ssn)) {
        $this->validationMessages[] = "SSN must be in XXX-XX-XXXX format.";
        return false;
    }

    return true;
}
```

## Testing

### Unit Tests

**Location:** `tests/Tests/Unit/`

**Example:**
```php
<?php
namespace OpenEMR\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Services\PatientService;

class PatientServiceTest extends TestCase
{
    private $patientService;

    protected function setUp(): void
    {
        $this->patientService = new PatientService();
    }

    public function testGetOneReturnsPatient()
    {
        $uuid = 'test-uuid-123';
        $result = $this->patientService->getOne($uuid);

        $this->assertTrue($result->hasData());
        $this->assertNotEmpty($result->getData());
    }

    public function testInsertValidatesRequiredFields()
    {
        $data = [
            'fname' => 'John'
            // Missing required fields
        ];

        $result = $this->patientService->insert($data);

        $this->assertTrue($result->hasErrors());
    }
}
```

**Run tests:**
```bash
./vendor/bin/phpunit tests/Tests/Unit/
```

### API Tests

**Location:** `tests/api/`

**Example:** `tests/api/PatientApiTest.php`
```php
<?php
namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\TestCase;

class PatientApiTest extends TestCase
{
    private $client;
    private $accessToken;

    protected function setUp(): void
    {
        // Get access token
        $this->accessToken = $this->getAccessToken();

        // Create HTTP client
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://localhost:9300/apis/default/',
            'verify' => false
        ]);
    }

    public function testGetPatientList()
    {
        $response = $this->client->get('api/patient', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data['data']);
    }

    private function getAccessToken()
    {
        // Implement OAuth flow to get token
        // ...
    }
}
```

### Testing OAuth Flow

**Integration test:**
```php
<?php
public function testOAuthFlow()
{
    // Step 1: Registration
    $registration = $this->registerClient([
        'client_name' => 'Test Client',
        'redirect_uris' => ['http://localhost/callback']
    ]);

    $clientId = $registration['client_id'];
    $clientSecret = $registration['client_secret'];

    // Step 2: Authorization
    $authCode = $this->authorizeClient($clientId);

    // Step 3: Token exchange
    $tokens = $this->exchangeCodeForToken($authCode, $clientId, $clientSecret);

    $this->assertArrayHasKey('access_token', $tokens);
    $this->assertArrayHasKey('refresh_token', $tokens);

    // Step 4: Make API call
    $response = $this->makeApiCall($tokens['access_token']);
    $this->assertEquals(200, $response->getStatusCode());
}
```

## Deployment

### Production Checklist

**Pre-Deployment:**

- [ ] SSL/TLS certificates configured
- [ ] Base URL configured correctly
- [ ] Database backed up
- [ ] OAuth2 clients registered
- [ ] Scopes configured appropriately
- [ ] ACLs configured
- [ ] Firewall rules in place
- [ ] Rate limiting configured

**Security:**

- [ ] Password grant disabled (unless absolutely necessary)
- [ ] Manual approval enabled for sensitive apps
- [ ] Audit logging enabled
- [ ] Token expiration configured appropriately
- [ ] PKCE enforced for public clients
- [ ] Certificate pinning implemented (mobile apps)

**Monitoring:**

- [ ] Error logging configured
- [ ] API access logging enabled
- [ ] Performance monitoring in place
- [ ] Alerting configured
- [ ] Backup procedures tested

### Performance Optimization

**Database:**
```sql
-- Add indexes for common queries
CREATE INDEX idx_patient_uuid ON patient_data(uuid);
CREATE INDEX idx_encounter_patient ON form_encounter(pid);
CREATE INDEX idx_encounter_date ON form_encounter(date);

-- Optimize queries
EXPLAIN SELECT * FROM patient_data WHERE uuid = ?;
```

**Caching:**
```php
<?php
// Cache frequently accessed data
use OpenEMR\Common\Cache\CacheService;

$cache = new CacheService();

$patientData = $cache->get('patient_' . $uuid);

if ($patientData === null) {
    $patientData = $this->fetchPatientFromDb($uuid);
    $cache->set('patient_' . $uuid, $patientData, 3600); // 1 hour
}
```

**Rate Limiting:**
```php
<?php
// Implement rate limiting
function checkRateLimit($clientId, $limit = 100, $window = 3600) {
    $key = 'rate_limit_' . $clientId;
    $count = apcu_fetch($key) ?: 0;

    if ($count >= $limit) {
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded']);
        exit;
    }

    apcu_store($key, $count + 1, $window);
}
```

### Monitoring

**Logging:**
```php
<?php
// Log API calls
function logApiCall($endpoint, $method, $userId, $responseCode) {
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'endpoint' => $endpoint,
        'method' => $method,
        'user_id' => $userId,
        'response_code' => $responseCode,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ];

    error_log(json_encode($log));
}
```

**Metrics:**

Track key metrics:
- API requests per minute
- Response times
- Error rates
- Token issuance rate
- Failed authentication attempts

**Alerts:**

Configure alerts for:
- High error rates (>5%)
- Slow response times (>2s)
- Failed auth attempts (>10/min)
- Disk space low
- Database connection errors

## Troubleshooting

### Common Issues

**Issue: CORS Errors**
```php
// Add CORS headers in .htaccess or PHP
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
```

**Issue: Token Validation Failures**
```php
// Check token format
var_dump(json_decode(base64_decode(explode('.', $token)[1])));

// Verify signature
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebToken;
$jwt = new JsonWebToken($token);
$jwt->validate();
```

**Issue: Database Connection Errors**
```php
// Check connection
$conn = \OpenEMR\Common\Database\Connector::Instance();
if (!$conn) {
    error_log("Database connection failed");
}
```

### Debug Mode

**Enable debug logging:**
```php
// In globals.php or config
$GLOBALS['debug_mode'] = 1;

// In code
if ($GLOBALS['debug_mode']) {
    error_log("Debug: Processing request for patient " . $puuid);
}
```

**API Request/Response Logging:**
```php
// Log full request
error_log("API Request: " . print_r([
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'headers' => getallheaders(),
    'body' => file_get_contents('php://input')
], true));
```

## Contributing

### Development Workflow

1. **Fork the repository**
```bash
   git clone https://github.com/openemr/openemr.git
   cd openemr
   git checkout -b feature/my-new-feature
```

2. **Make changes**
    - Follow coding standards (PSR-12)
    - Add tests
    - Update documentation

3. **Test changes**
```bash
   ./vendor/bin/phpunit
```

4. **Submit pull request**
    - Describe changes
    - Reference issues
    - Include screenshots if UI changes

### Coding Standards

**Follow PSR-12:**
```php
<?php
namespace OpenEMR\Services;

class ExampleService
{
    private $property;

    public function __construct()
    {
        $this->property = null;
    }

    public function methodName($parameter)
    {
        if ($parameter === null) {
            return false;
        }

        return true;
    }
}
```

**Documentation:**
```php
/**
 * Get patient by UUID
 *
 * @param string $uuid Patient UUID
 * @return ProcessingResult
 */
public function getOne($uuid)
{
    // ...
}
```

### Pull Request Template
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] API tests added/updated
- [ ] Manual testing completed

## Checklist
- [ ] Code follows PSR-12
- [ ] Documentation updated
- [ ] Tests pass
- [ ] No new warnings
```

---

**Resources:**
- **API Forum Thread:** https://community.open-emr.org/t/v6-authorization-and-api-changes-afoot/15450
- **GitHub:** https://github.com/openemr/openemr
- **Documentation:** https://www.open-emr.org/wiki/

**Support:**
- **Community Forum:** https://community.open-emr.org/
- **Chat:** https://chat.open-emr.org/
- **Issues:** https://github.com/openemr/openemr/issues

For questions about extending the API, post in the community forum or join the developer chat.

---
## Documentation Attribution

### Authorship
This documentation represents the collective knowledge and contributions of the OpenEMR open-source community. The content is based on:
- Original documentation by OpenEMR developers and contributors
- Technical specifications from the OpenEMR codebase
- Community feedback and real-world implementation experience

### AI Assistance
The organization, structure, and presentation of this documentation was enhanced using Claude AI (Anthropic) to:
- Reorganize content into a more accessible modular structure
- Add comprehensive examples and use cases
- Improve navigation and cross-referencing
- Enhance clarity and consistency across documents

All technical accuracy is maintained from the original community-authored documentation.

### Contributing
OpenEMR is an open-source project. To contribute to this documentation:
- **Report Issues:** [GitHub Issues](https://github.com/openemr/openemr/issues)
- **Discuss:** [Community Forum](https://community.open-emr.org/)
- **Submit Changes:** [Pull Requests](https://github.com/openemr/openemr/pulls)

**Last Updated:** November 2025
**License:** GPL v3

For complete documentation, see **[Documentation/api/](Documentation/api/)**
