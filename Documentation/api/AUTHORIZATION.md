# Authorization & Scopes

Complete guide to OpenEMR API scopes, permissions, and access control.

## Table of Contents
- [Overview](#overview)
- [Scope Syntax](#scope-syntax)
    - [Scope Format](#scope-format)
    - [Permission Flags](#permission-flags)
    - [Context Prefixes](#context-prefixes)
    - [Granular Scopes](#granular-scopes)
    - [V1 Scope Compatibility](#v1-scope-compatibility)
- [Required Scopes](#required-scopes)
- [FHIR API Scopes (api:fhir)](#fhir-api-scopes-apifhir)
    - [Patient Context Scopes](#patient-context-scopes)
    - [User Context Scopes](#user-context-scopes)
    - [System Context Scopes](#system-context-scopes)
- [Standard API Scopes (api:oemr)](#standard-api-scopes-apioemr)
- [Patient Portal Scopes (api:port)](#patient-portal-scopes-apiport)
- [Special Scopes](#special-scopes)
- [Revoking Access](#revoking-access)
    - [Revoke Clients](#revoke-clients)
    - [Revoke Users](#revoke-users)
    - [Revoke Tokens](#revoke-tokens)
- [Best Practices](#best-practices)
- [Examples](#examples)

## Overview

OpenEMR implements **SMART on FHIR v2.2.0** scopes with granular permissions, allowing fine-grained control over API access. Scopes define:

- **What data** can be accessed (resource types)
- **What operations** can be performed (create, read, update, delete, search)
- **In what context** (patient, user, system)
- **Which subset** of data (granular filters)

### Key Features
- ✅ **Granular permissions** - Fine-grained control with `.cruds` syntax
- ✅ **Resource-level scopes** - Specify exact resource types
- ✅ **Context-aware** - Patient, user, and system contexts
- ✅ **Query filters** - Restrict access by resource categories
- ✅ **Backward compatible** - SMART on FHIR V1 `.read`/`.write` scopes supported
- ✅ **Principle of least privilege** - Request only necessary permissions

## Scope Syntax

### Scope Format

**SMART v2.2.0 Format** (Recommended):
```
<context>/<Resource>.<permissions>[?<query>]
```

**Components:**
- `<context>`: `patient`, `user`, or `system`
- `<Resource>`: FHIR resource type (e.g., `Patient`, `Observation`)
- `<permissions>`: One or more permission flags (`.cruds`)
- `<query>`: Optional query string for granular filtering

**Examples:**
```
patient/Patient.rs                  # Read and search patients
user/Observation.cruds              # Full access to observations
system/Patient.rs                   # System-level patient read/search
patient/Condition.rs?category=...   # Granular scope with filter
```

### Permission Flags

**SMART v2.2.0** introduces granular permission flags:

| Flag | Permission | Description | HTTP REST Operation |
|------|------------|-------------|---------------------|
| `c` | **Create** | Create new resources | POST                |
| `r` | **Read** | Read individual resources by ID | GET                 |
| `u` | **Update** | Update existing resources | POST,PUT,PATCH      |
| `d` | **Delete** | Delete resources | DELETE              |
| `s` | **Search** | Search for resources | GET                 |

#### Combining Permissions

Combine flags to specify multiple permissions. **Flags must be in order: `cruds`**

✅ **Valid Examples:**
```
patient/Patient.r          # Read only
patient/Patient.rs         # Read and search
patient/Patient.cr         # Create and read
patient/Patient.cru        # Create, read, update
patient/Patient.crud       # Create, read, update, delete
patient/Patient.cruds      # All permissions
patient/Observation.rs     # Read and search observations
patient/Observation.cud    # Create, update, delete (no read/search)
user/Condition.rus         # Read, update, search
```

❌ **Invalid Examples:**
```
patient/Patient.sr         # Wrong order (should be .rs)
patient/Patient.duc        # Wrong order (should be .cud)
patient/Patient.rsc        # Wrong order (should be .crs)
patient/Patient.xyz        # Invalid flags
```

#### Common Permission Combinations

| Scope | Use Case |
|-------|----------|
| `.r` | Read individual resources only (no search) |
| `.rs` | Read-only access with search |
| `.cud` | Write operations without read access |
| `.cruds` | Full access to resource type |
| `.rus` | Read, update, and search (common for updates) |
| `.crs` | Create, read, and search (common for data entry) |

### Context Prefixes

Scopes are contextualized by prefix:

#### `patient/` - Patient Context
- Access limited to **single patient's data**
- Often used in patient-facing apps, or in practitioner applications that operate on a single patient at a time (such as e-prescribe)
- Patient ID provided in token response
- Most restrictive context

**Examples:**
```
patient/Patient.rs
patient/Observation.rs
patient/MedicationRequest.rs
```

**Who uses this:**
- Patient portals
- Personal health apps
- Patient standalone apps
- Mobile health applications
- E-prescribing applications for single patient prescription flow

#### `user/` - User Context
- Access to data **user is authorized to see**
- Based on logged-in practitioner's permissions
- Access to multiple patients (per clinical role)
- Medium restriction level

**Examples:**
```
user/Patient.rs
user/Observation.cruds
user/Practitioner.rus
```

**Who uses this:**
- EHR-integrated apps
- Clinical decision support
- Provider-facing tools
- Documentation applications

#### `system/` - System Context
- **Unrestricted access** to all data
- No user or patient context required
- Used for backend services
- Highest privilege level

**Examples:**
```
system/Patient.rs
system/Patient.$export
system/Group.$export
```

**Who uses this:**
- Bulk data exports
- Analytics platforms
- Population health tools
- Backend integration services

### Granular Scopes

**New in SMART v2.2.0**: Restrict access to resource subsets using query parameters.

#### Syntax
```
<context>/<Resource>.<permissions>?<parameter>=<value>
```

#### Supported Granular Scopes

##### Condition Resource

Filter conditions by category:

| Scope | Description |
|-------|-------------|
| `patient/Condition.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category\|health-concern` | Health concerns only |
| `patient/Condition.rs?category=http://terminology.hl7.org/CodeSystem/condition-category\|encounter-diagnosis` | Encounter diagnoses only |
| `patient/Condition.rs?category=http://terminology.hl7.org/CodeSystem/condition-category\|problem-list-item` | Problem list items only |

**Example Use Cases:**
- App displays only health concerns (not full problem list)
- Clinical decision support focused on active diagnoses
- Research app analyzing specific condition types

**Request Example:**
```bash
# Register with granular Condition scope
curl -X POST https://localhost:9300/oauth2/default/registration \
  -H 'Content-Type: application/json' \
  --data '{
    "scope": "openid patient/Patient.rs patient/Condition.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern"
  }'
```

##### Observation Resource

Filter observations by category:

| Scope | Description |
|-------|-------------|
| `patient/Observation.rs?category=http://hl7.org/fhir/us/core/CodeSystem/us-core-category\|sdoh` | Social determinants of health |
| `patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category\|social-history` | Social history (smoking, etc.) |
| `patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category\|laboratory` | Lab results only |
| `patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category\|survey` | Survey/assessment results |
| `patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category\|vital-signs` | Vital signs only |

**Example Use Cases:**
- Fitness app accessing only vital signs
- Lab results viewer (labs only)
- Social needs screening app (SDOH data)
- Research study collecting specific observation types

**Request Example:**
```bash
# Vital signs tracking app
curl -X POST https://localhost:9300/oauth2/default/registration \
  -H 'Content-Type: application/json' \
  --data '{
    "scope": "openid patient/Patient.rs patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs"
  }'
```

##### DocumentReference Resource

Filter documents by category:

| Scope | Description |
|-------|-------------|
| `patient/DocumentReference.rs?category=http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category\|clinical-note` | Clinical notes only |

**Example Use Cases:**
- Clinical note viewer
- Documentation app focused on provider notes
- Patient accessing visit summaries

**Request Example:**
```bash
# Clinical notes app
curl -X POST https://localhost:9300/oauth2/default/registration \
  -H 'Content-Type: application/json' \
  --data '{
    "scope": "openid patient/Patient.rs patient/DocumentReference.rs?category=http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category|clinical-note"
  }'
```

#### Granular Scope Behavior

**Important:** Granular scopes **restrict** access. If you request:
```
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs
```

You will **only** be able to access vital signs observations. You cannot access lab results or other observation types.

**To access multiple categories**, request separate scopes:
```
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|laboratory
```

### V1 Scope Compatibility

OpenEMR maintains **backward compatibility** with SMART on FHIR v1 scopes.

#### V1 to V2 Mapping

| V1 Scope | V2 Equivalent | Permissions Granted |
|----------|---------------|---------------------|
| `patient/Patient.read` | `patient/Patient.rs` | Read + Search |
| `patient/Patient.write` | `patient/Patient.cud` | Create + Update + Delete |
| `user/Observation.read` | `user/Observation.rs` | Read + Search |
| `user/Observation.write` | `user/Observation.cud` | Create + Update + Delete |
| `system/Patient.read` | `system/Patient.rs` | Read + Search |

#### Requesting V1 Scopes

**V1 scopes are still accepted** during registration and authorization:
```json
{
  "scope": "openid patient/Patient.read patient/Observation.read"
}
```

**What you receive:**
```json
{
  "scope": "openid patient/Patient.read patient/Observation.read"
}
```

#### Migration Guidance

**For new applications:**
- ✅ Use V2 granular scopes (`.cruds`)
- ✅ Request only needed permissions
- ✅ Use granular filters where applicable

**For existing applications:**
- ⚠️ V1 scopes (`read`/`write`) continue to work
- ✅ Consider migrating to granular scopes for better security in case v1 scopes become deprecated
- ✅ Update to request specific permissions (not blanket `read`/`write`)

**Breaking changes:**
- ❌ None - V1 scopes remain functional

## Required Scopes

Certain scopes are required or strongly recommended for all applications:

### Mandatory Scopes

| Scope | Required For | Description |
|-------|-------------|-------------|
| `openid` | All OAuth2 apps | OpenID Connect authentication |
| `fhirUser` | SMART apps | Identifies the authorized user |
| `online_access` | Session-based apps | Indicates online access pattern |

### Conditional Scopes

| Scope | When Required | Description                                                   |
|-------|---------------|---------------------------------------------------------------|
| `offline_access` | Apps needing refresh tokens | Enables refresh token issuance |
| `launch` | EHR launch apps | Indicates EHR launch capability                               |
| `launch/patient` | Apps needing patient context | Receive patient ID in token                                   |

### API Type Scopes

At least one API type scope is required:

| Scope | API Access |
|-------|------------|
| `api:fhir` | FHIR API (`/fhir/` endpoints) |
| `api:oemr` | Standard API (`/api/` endpoints) |
| `api:port` | Patient Portal API (`/portal/` endpoints) |

**Example minimal scope request:**
```
openid api:fhir patient/Patient.rs
```

## FHIR API Scopes (api:fhir)

Access to FHIR R4 endpoints (`/fhir/`). Requires `api:fhir` base scope plus resource-specific scopes.

### Patient Context Scopes

Access to a single patient's data. Patient ID provided in token response.

#### Core Clinical Resources
```
patient/AllergyIntolerance.rs
patient/Appointment.rs
patient/CarePlan.rs
patient/CareTeam.rs
patient/Condition.rs
patient/Coverage.rs
patient/Device.rs
patient/DiagnosticReport.rs
patient/DocumentReference.rs
patient/Encounter.rs
patient/Goal.rs
patient/Immunization.rs
patient/MedicationRequest.rs
patient/Medication.rs
patient/Observation.rs
patient/Patient.rs
patient/Procedure.rs
patient/Provenance.rs          # Read/search only
patient/MedicationDispense.rs    # Pharmacy dispensing records
patient/RelatedPerson.rs         # Patient relationships
patient/ServiceRequest.rs        # Lab/procedure orders
patient/Specimen.rs              # Laboratory specimens
```

#### Supporting Resources
```
patient/Binary.rs                   # Binary data (read-only)
patient/Location.rs                 # Facility locations
patient/Organization.rs             # Healthcare organizations
patient/Person.rs                   # Person records
patient/Practitioner.rs             # Healthcare providers
patient/PractitionerRole.rs         # Provider roles
```

#### Special Operations
```
patient/DocumentReference.$docref   # Generate CCD documents
```

### User Context Scopes

Access to data the authenticated user is authorized to see (multiple patients).

#### Core Clinical Resources
```
user/AllergyIntolerance.rs
user/CarePlan.rs
user/CareTeam.rs
user/Condition.rs
user/Coverage.rs
user/Device.rs
user/DiagnosticReport.rs
user/DocumentReference.rs
user/Encounter.rs
user/Goal.rs
user/Immunization.rs
user/MedicationRequest.rs
user/Medication.rs
user/Observation.rs
user/Patient.rs                  # Access to multiple patients
user/Procedure.rs
user/Provenance.rs
user/MedicationDispense.rs
user/RelatedPerson.rs
user/ServiceRequest.rs
user/Specimen.rs
```

#### Supporting Resources
```
user/Binary.rs
user/Location.rs
user/Organization.rs
user/Person.rs
user/Practitioner.rs
user/PractitionerRole.rs
```

#### Special Operations
```
user/DocumentReference.$docref
```

### System Context Scopes

Unrestricted access to all data. Used for backend services and bulk exports.

#### Core Clinical Resources
```
system/AllergyIntolerance.rs
system/CarePlan.rs
system/CareTeam.rs
system/Condition.rs
system/Coverage.rs
system/Device.rs
system/DiagnosticReport.rs
system/DocumentReference.rs
system/Encounter.rs
system/Goal.rs
system/Immunization.rs
system/MedicationRequest.rs
system/Medication.rs
system/Observation.rs
system/Patient.rs
system/Procedure.rs
system/Provenance.rs
system/MedicationDispense.rs
system/RelatedPerson.rs
system/ServiceRequest.rs
system/Specimen.rs
```

#### Supporting Resources
```
system/Binary.rs
system/Group.rs
system/Location.rs
system/Organization.rs
system/Person.rs
system/Practitioner.rs
system/PractitionerRole.rs
```

#### Bulk Export Operations

**Required for bulk FHIR exports:**
```
system/Patient.$export              # Patient-level export
system/Group.$export                # Group-level export
system/*.$export                    # System-level export
system/*.$bulkdata-status           # Check export status
system/Binary.read                  # Download export files
```

**Complete bulk export scope set:**
```
system/Group.$export system/*.$bulkdata-status system/Binary.read
```

See [FHIR API - Bulk Exports](FHIR_API.md#bulk-fhir-exports) for details.

#### Special Operations
```
patient/DocumentReference.$docref
user/DocumentReference.$docref
system/DocumentReference.$docref
```

## Standard API Scopes (api:oemr)

Access to OpenEMR REST API (`/api/` endpoints). Requires `api:oemr` base scope plus resource-specific scopes.

### User Context Scopes
```
user/allergy.cruds
user/appointment.cruds
user/dental_issue.cruds
user/document.crs
user/drug.rs
user/encounter.crus
user/employer.s
user/facility.crus
user/immunization.rs
user/insurance.crus
user/insurance_company.crus
user/insurance_type.s
user/list.r
user/medical_problem.cruds
user/medication.cruds
user/message.cud
user/patient.crus
user/practitioner.crus
user/prescription.rs
user/procedure.rs
user/product.s
user/soap_note.crus
user/surgery.cruds
user/transaction.cuds
user/vital.crus
```

**Note:** Standard API uses different permission syntax than FHIR but follows the same `.cruds` pattern.

## Patient Portal Scopes (api:port)

**EXPERIMENTAL** - Access to patient portal endpoints (`/portal/`).

### Patient Context Scopes
```
patient/encounter.rs
patient/patient.s
patient/appointment.rs
```

**Enable Patient Portal API:**
Administration → Config → Connectors → "Enable OpenEMR Patient Portal REST API (EXPERIMENTAL)"

## Special Scopes

### Context Scopes

Used with EHR Launch to receive context information:
```
launch                              # EHR launch capability
launch/patient                      # When launching outside EHR, ask for a patient to be selected at launch time
```

See [AUTHENTICATION.md - EHR Launch](AUTHENTICATION.md#ehr-launch-flow) for details.

### Identity Scopes
```
openid                              # OpenID Connect (required)
fhirUser                            # Practitioner/Patient identity
profile                             # User profile information
email                               # User email address
```

### Access Pattern Scopes
```
online_access                       # Session-based access
offline_access                      # Refresh token access
```

## Revoking Access

Administrators can revoke API access at multiple levels.

### Revoke Clients

**Disable an entire client application**, preventing all tokens from working.

**Steps:**
1. Navigate to **Admin → System → API Clients**
2. Find the client to disable
3. Click **Edit**
4. Click **Disable** button
5. Confirm the action

**Effect:**
- ❌ All access tokens for this client become invalid
- ❌ All refresh tokens for this client become invalid
- ❌ Client cannot request new tokens
- ✅ Client registration remains (can be re-enabled)

**When to use:**
- Compromised client credentials
- Rogue or malicious application
- Decommissioned application
- Security incident response

### Revoke Users

**Revoke a specific user's authorization** for a client without affecting other users.

**Steps:**
1. Navigate to **Admin → System → API Clients**
2. Find and edit the client
3. Scroll to **Authenticated API Users** section
4. Find the user (use browser search if needed)
5. Click **Revoke User** button
6. Confirm the action

**Effect:**
- ❌ User's tokens for this client become invalid
- ❌ User must re-authorize the application
- ✅ Other users' access unaffected
- ✅ Client remains active

**When to use:**
- User requests data access removal
- User leaves organization
- Suspicious activity from specific user
- User's authorization expired/changed

### Revoke Tokens

**Revoke individual access or refresh tokens.**

#### Method 1: Via Client Management

**Steps:**
1. Navigate to **Admin → System → API Clients**
2. Edit the client
3. Find the **Access Tokens** or **Refresh Tokens** section
4. Locate token by identifier
5. Click **Revoke Token**
6. Confirm the action

#### Method 2: Via Token Tools

**Steps:**
1. Navigate to **Admin → System → API Clients**
2. Click **Token Tools** button
3. Paste the **full encoded token** (JWT string)
4. Click **Parse Token**
5. Review token information
6. Click **Revoke Token**
7. Confirm the action

**Verify revocation:**
1. Parse the token again
2. Status should show as "Revoked"

**Token Information Displayed:**
- Client ID and name
- Authorized user
- Scopes granted
- Expiration time
- Revocation status

**Effect:**
- ❌ Specific token becomes invalid immediately
- ✅ Other tokens for same user/client remain valid
- ✅ User can continue using other sessions

**When to use:**
- Suspected token compromise
- Token leaked in logs
- User lost device
- Terminate specific session

### Bulk Revocation

**Revoke all tokens** for security incidents:

1. **Disable the client** (revokes all tokens)

## Best Practices

### Security Best Practices

✅ **Request minimum necessary scopes**
```
# Good - specific permissions
patient/Patient.rs patient/Observation.rs

# Avoid - overly broad
patient/*.cruds
```

✅ **Use granular scopes when possible**
```
# Good - specific data subset
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs

# Less secure - all observations
patient/Observation.rs
```

✅ **Use appropriate context**
```
# Good - patient app uses patient context
patient/Patient.rs

# Wrong - patient app requesting system context
system/Patient.rs
```

### Development Best Practices

✅ **Document scope requirements**
```javascript
/**
 * Required scopes:
 * - openid
 * - offline_access
 * - patient/Patient.rs
 * - patient/Observation.rs
 * - patient/MedicationRequest.rs
 */
const requiredScopes = [
  'openid',
  'offline_access',
  'patient/Patient.rs',
  'patient/Observation.rs',
  'patient/MedicationRequest.rs'
];
```

✅ **Handle scope changes gracefully**
```javascript
// Check granted scopes
const grantedScopes = tokenResponse.scope.split(' ');

if (!grantedScopes.includes('patient/Observation.rs')) {
  console.warn('Observation access not granted');
  // Disable observation features
}
```

✅ **Request scopes progressively**
```javascript
// Start with minimal scopes
const initialScopes = ['openid', 'patient/Patient.rs'];

// Request additional scopes when needed
const additionalScopes = ['patient/Observation.rs'];
```

### Compliance Best Practices

✅ **Inform users about data access**
- Clearly explain what data will be accessed
- Display scopes in user-friendly language
- Provide opt-out mechanisms where appropriate

✅ **Follow principle of least privilege**
- Request only scopes needed for core functionality
- Don't request "nice to have" scopes
- Use granular filters to limit data access

✅ **Regular scope audits**
- Review requested scopes quarterly
- Remove unused scopes
- Update to more restrictive scopes when possible

### ONC Cures Compliance

For apps that must comply with ONC Cures Update:

✅ **Patient standalone apps**
- Use `patient/*` scopes only
- Auto-approved within 48 hours
- Cannot request `user/*` or `system/*` scopes

✅ **Provider/system apps**
- May request `user/*` or `system/*` scopes
- Subject to administrator approval
- Must justify scope requirements

## Examples

### Example 1: Patient Vital Signs Tracker

**App Description:** Mobile app for patients to track blood pressure and weight.

**Required Scopes:**
```
openid
offline_access
patient/Patient.rs
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs
```

**Scope Justification:**
- `openid`: Authentication
- `offline_access`: Refresh tokens for long-term use
- `patient/Patient.rs`: Read patient demographics
- Granular Observation scope: Only vital signs (not labs, not all observations)

**Registration:**
```bash
curl -X POST https://localhost:9300/oauth2/default/registration \
  -H 'Content-Type: application/json' \
  --data '{
    "application_type": "public",
    "client_name": "Vital Signs Tracker",
    "redirect_uris": ["com.example.vitals://callback"],
    "scope": "openid offline_access patient/Patient.rs patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs"
  }'
```

### Example 2: Clinical Documentation App

**App Description:** Provider-facing app for documenting encounters.

**Required Scopes:**
```
openid
fhirUser
launch
launch/patient
user/Patient.rs
user/Encounter.cruds
user/Condition.crs
user/Observation.crs
user/DocumentReference.crs
```

**Scope Justification:**
- `launch` + `launch/patient`: EHR launch with context
- `fhirUser`: Identify the provider
- `user/Patient.rs`: Read patient demographics
- `user/Encounter.cruds`: Full encounter management
- `user/Condition.crs`: Create, read, search conditions
- `user/Observation.crs`: Create, read, search observations
- `user/DocumentReference.crs`: Create clinical notes

**No delete permission** - documentation is preserved.

### Example 3: Lab Results Viewer

**App Description:** Patient app to view lab results only.

**Required Scopes:**
```
openid
patient/Patient.rs
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|laboratory
patient/DiagnosticReport.rs
patient/Practitioner.rs
```

**Scope Justification:**
- Granular Observation scope: Only lab results
- `DiagnosticReport.rs`: Lab report metadata
- `Practitioner.rs`: Ordering provider information
- **No write permissions** - read-only app

### Example 4: Population Health Analytics

**App Description:** Backend service for analyzing patient populations.

**Required Scopes:**
```
system/Patient.rs
system/Condition.rs
system/Observation.rs
system/MedicationRequest.rs
system/Encounter.rs
```

**Authentication:** Client credentials grant with JWKS

**Scope Justification:**
- `system/*` context: Access to all patients
- Read/search only: No modifications
- Multiple resource types: Comprehensive analysis

### Example 5: Bulk Data Export

**App Description:** Analytics platform exporting all patient data.

**Required Scopes:**
```
system/*.$export
system/*.$bulkdata-status
system/Binary.read
```

**Authentication:** Client credentials grant (required)

**Scope Justification:**
- `$export` scope: Initiate bulk export
- `$bulkdata-status`: Check export progress
- `Binary.read`: Download NDJSON files

**See:** [FHIR API - Bulk Exports](FHIR_API.md#bulk-fhir-exports)

### Example 6: Medication Management

**App Description:** Pharmacy integration for medication dispensing.

**Required Scopes:**
```
openid
fhirUser
user/Patient.rs
user/MedicationRequest.rs
user/Medication.rs
user/MedicationDispense.cruds
user/Practitioner.rs
```

**Scope Justification:**
- New `MedicationDispense` resource for recording dispensing
- `MedicationRequest.rs`: View orders
- `Medication.rs`: Medication details
- Full CRUDS on MedicationDispense: Create dispense records, update status

### Example 7: Health Concerns Tracker

**App Description:** Patient app for tracking health concerns specifically.

**Required Scopes:**
```
openid
offline_access
patient/Patient.rs
patient/Condition.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern
patient/Goal.rs
```

**Scope Justification:**
- Granular Condition scope: Only health concerns (not diagnoses)
- Focused app with minimal data access
- `Goal.rs`: Related goals for health concerns

### Example 8: Research Data Collection

**App Description:** Research study collecting survey data.

**Required Scopes:**
```
openid
patient/Patient.rs
patient/Observation.crs?category=http://terminology.hl7.org/CodeSystem/observation-category|survey
```

**Scope Justification:**
- Granular Observation: Survey responses only
- Create permission: Record survey answers

### Example 9 Backward Compatible App

**App Description:** Existing app using V1 scopes.

**Requested Scopes (V1):**
```
openid
patient/Patient.read
patient/Observation.read
patient/Condition.write
patient/Patient.rs
patient/Observation.rs
patient/Condition.cud
```

**Granted Scopes (V2):**
```
openid
patient/Patient.read
patient/Observation.read
patient/Condition.write
patient/Patient.rs
patient/Observation.rs
patient/Condition.cud
```

**Behavior:**
- V1 `.read` mapped to V2 `.rs`
- V1 `.write` mapped to V2 `.cud`
- App continues to function
- Consider migrating to granular scopes

---

**Next Steps:**
- Review [Authentication Guide](AUTHENTICATION.md) for OAuth2 flows
- See [FHIR API](FHIR_API.md) for endpoint documentation
- Learn about [SMART on FHIR](SMART_ON_FHIR.md) app integration
- Check [Developer Guide](DEVELOPER_GUIDE.md) for implementation details

**Support:**
- Community Forum: https://community.open-emr.org/
- Scope Reference: https://hl7.org/fhir/smart-app-launch/scopes-and-launch-context.html


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
