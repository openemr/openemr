# FHIR API

Complete guide to OpenEMR's FHIR R4 API implementation.

## Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Base URL](#base-url)
- [Authentication](#authentication)
- [Capability Statement](#capability-statement)
- [Supported Resources](#supported-resources)
    - [Patient Resources](#patient-resources)
    - [Clinical Resources](#clinical-resources)
    - [Medications](#medications)
    - [Diagnostic Resources](#diagnostic-resources)
    - [Care Coordination](#care-coordination)
    - [Administrative Resources](#administrative-resources)
    - [New Resources (SMART v2.2.0)](#new-resources-smart-v220)
- [Search Parameters](#search-parameters)
- [Provenance](#provenance)
- [Bulk FHIR Exports](#bulk-fhir-exports)
    - [System Export](#system-export)
    - [Patient Export](#patient-export)
    - [Group Export](#group-export)
    - [Export Status](#export-status)
    - [Download Files](#download-files)
- [DocumentReference $docref Operation](#documentreference-docref-operation)
- [SMART Configuration](#smart-configuration)
- [Error Handling](#error-handling)
- [Examples](#examples)
- [For Developers](#for-developers)

## Overview

OpenEMR provides a comprehensive **FHIR R4** implementation compliant with:
- ✅ **FHIR R4 Specification** - HL7 FHIR Release 4
- ✅ **US Core 3.1 Implementation Guide** - US healthcare requirements
- ✅ **SMART on FHIR v2.2.0** - App launch and authorization
- ✅ **Bulk Data IG** - ONC-required bulk export operations

### Key Features
- **50+ FHIR Resources** supported
- **Granular scopes** for fine-grained access control
- **Bulk exports** for population health and analytics
- **CCD generation** via DocumentReference $docref
- **Provenance tracking** for data transparency
- **SMART app integration** with EHR and standalone launch

### Standards Compliance

| Standard | Version | Status |
|----------|---------|--------|
| FHIR | R4 (4.0.1) | ✅ Full Support |
| US Core | 3.1 | ✅ Compliant |
| SMART on FHIR | v2.2.0 | ✅ Certified |
| Bulk Data | v1.0 | ✅ Implemented |
| USCDI | v1 | ✅ Supported |

## Prerequisites

### 1. Enable FHIR API

Navigate to: **Administration → Config → Connectors**

Enable: **☑ Enable OpenEMR Standard FHIR REST API**

### 2. Configure SSL/TLS

**Required:** All FHIR endpoints require HTTPS/TLS.

Set base URL: **Administration → Config → Connectors → Site Address (required for OAuth2 and FHIR)**

Example: `https://your-openemr.example.com`

### 3. Register API Client

See [Authentication Guide](AUTHENTICATION.md#client-registration) for client registration.

**Required scopes:** At minimum `openid` + `api:fhir` + resource-specific scopes.

Example:
```
openid api:fhir patient/Patient.rs patient/Observation.rs
```

See [Authorization Guide](AUTHORIZATION.md#fhir-api-scopes-apifhir) for complete scope listing.

## Base URL

FHIR endpoints use the following base URL pattern:
```
https://{your-openemr-host}/apis/{site}/fhir
```

### Default Site
```
https://localhost:9300/apis/default/fhir
```

### Multisite Example
```
https://localhost:9300/apis/alternate/fhir
```

### Endpoint Structure
```
{base}/[resource-type]/[id]
{base}/[resource-type]?[search-parameters]
{base}/[resource-type]/[id]/[operation]
```

**Examples:**
```
GET https://localhost:9300/apis/default/fhir/Patient/123
GET https://localhost:9300/apis/default/fhir/Observation?patient=123
POST https://localhost:9300/apis/default/fhir/Patient/123/$docref
```

## Authentication

All FHIR API requests (except capability statement) require authentication via **Bearer token**.

### Request Format
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Patient' \
  -H 'Authorization: Bearer YOUR_ACCESS_TOKEN' \
  -H 'Accept: application/fhir+json'
```

### Obtaining Access Token

See [Authentication Guide](AUTHENTICATION.md) for complete OAuth2 flows:
- [Authorization Code Grant](AUTHENTICATION.md#authorization-code-grant) (recommended)
- [Client Credentials Grant](AUTHENTICATION.md#client-credentials-grant) (bulk exports)
- [EHR Launch Flow](AUTHENTICATION.md#ehr-launch-flow) (SMART apps)

### Token in Request Header
```http
GET /apis/default/fhir/Patient/123 HTTP/1.1
Host: localhost:9300
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
Accept: application/fhir+json
```

### Content Negotiation

Supported content types:

| Format | Content-Type | Accept Header |
|--------|--------------|---------------|
| JSON (default) | `application/fhir+json` | `application/fhir+json` |
| JSON | `application/json` | `application/json` |
| XML | `application/fhir+xml` | `application/fhir+xml` |

**Recommended:** Use `application/fhir+json` for FHIR-specific JSON.

## Capability Statement

The Capability Statement describes the FHIR server's capabilities.

### Endpoint
```
GET /fhir/metadata
```

**No authentication required** for capability statement.

### Request
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/metadata' \
  -H 'Accept: application/fhir+json'
```

### Response (Excerpt)
```json
{
  "resourceType": "CapabilityStatement",
  "status": "active",
  "date": "2024-01-01",
  "publisher": "OpenEMR",
  "kind": "instance",
  "software": {
    "name": "OpenEMR",
    "version": "7.0.0"
  },
  "implementation": {
    "description": "OpenEMR FHIR R4 Server",
    "url": "https://localhost:9300/apis/default/fhir"
  },
  "fhirVersion": "4.0.1",
  "format": ["application/fhir+json", "application/fhir+xml"],
  "rest": [
    {
      "mode": "server",
      "security": {
        "extension": [{
          "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/oauth-uris",
          "extension": [
            {
              "url": "authorize",
              "valueUri": "https://localhost:9300/oauth2/default/authorize"
            },
            {
              "url": "token",
              "valueUri": "https://localhost:9300/oauth2/default/token"
            }
          ]
        }],
        "service": [{
          "coding": [{
            "system": "http://terminology.hl7.org/CodeSystem/restful-security-service",
            "code": "SMART-on-FHIR"
          }]
        }]
      },
      "resource": [
        {
          "type": "Patient",
          "interaction": [
            {"code": "read"},
            {"code": "search-type"}
          ],
          "searchParam": [
            {"name": "_id", "type": "token"},
            {"name": "birthdate", "type": "date"},
            {"name": "name", "type": "string"}
          ]
        }
      ]
    }
  ]
}
```

### What's Included

The capability statement reveals:
- Supported resource types
- Supported operations (read, search, create, etc.)
- Available search parameters
- OAuth2 endpoints
- SMART capabilities
- FHIR version

### Use Cases
- Discover server capabilities programmatically
- Configure SMART apps dynamically
- Validate compatibility before integration

## Supported Resources

OpenEMR supports **50+ FHIR R4 resources** across all contexts (patient, user, system).

### Patient Resources

#### Patient
**Description:** Patient demographics and administrative information.

**Scopes:**
```
patient/Patient.cruds
user/Patient.cruds
system/Patient.rs
```

**Search Parameters:**
- `_id` - Patient ID
- `identifier` - Patient identifier (MRN, SSN)
- `name` - Patient name (family, given)
- `birthdate` - Date of birth
- `gender` - Administrative gender
- `address` - Address (city, state, postal code)
- `telecom` - Contact information

**Example Request:**
```bash
# Get patient by ID
curl -X GET 'https://localhost:9300/apis/default/fhir/Patient/123' \
  -H 'Authorization: Bearer TOKEN'

# Search by name
curl -X GET 'https://localhost:9300/apis/default/fhir/Patient?name=Smith' \
  -H 'Authorization: Bearer TOKEN'

# Search by birthdate
curl -X GET 'https://localhost:9300/apis/default/fhir/Patient?birthdate=1980-01-01' \
  -H 'Authorization: Bearer TOKEN'
```

**Example Response:**
```json
{
  "resourceType": "Patient",
  "id": "123",
  "identifier": [{
    "system": "http://example.org/mrn",
    "value": "12345"
  }],
  "name": [{
    "use": "official",
    "family": "Smith",
    "given": ["John", "Q"]
  }],
  "gender": "male",
  "birthDate": "1980-01-01",
  "address": [{
    "line": ["123 Main St"],
    "city": "Boston",
    "state": "MA",
    "postalCode": "02134"
  }]
}
```

#### Person
**Description:** Person resource for linking multiple patient records.

**Scopes:**
```
patient/Person.rs
user/Person.rs
system/Person.rs
```

#### RelatedPerson ✨ NEW
**Description:** Persons with relationships to patients (family, emergency contacts, caregivers).

**Scopes:**
```
patient/RelatedPerson.cruds
user/RelatedPerson.cruds
system/RelatedPerson.rs
```

**Search Parameters:**
- `patient` - Reference to patient
- `name` - Related person's name
- `telecom` - Contact information

**Example Request:**
```bash
# Get emergency contacts for patient
curl -X GET 'https://localhost:9300/apis/default/fhir/RelatedPerson?patient=123' \
  -H 'Authorization: Bearer TOKEN'
```

**Example Response:**
```json
{
  "resourceType": "RelatedPerson",
  "id": "456",
  "patient": {
    "reference": "Patient/123"
  },
  "relationship": [{
    "coding": [{
      "system": "http://terminology.hl7.org/CodeSystem/v3-RoleCode",
      "code": "WIFE",
      "display": "Wife"
    }]
  }],
  "name": [{
    "family": "Smith",
    "given": ["Jane"]
  }],
  "telecom": [{
    "system": "phone",
    "value": "555-1234",
    "use": "home"
  }]
}
```

### Clinical Resources

#### AllergyIntolerance
**Description:** Patient allergies and adverse reactions.

**Scopes:**
```
patient/AllergyIntolerance.cruds
user/AllergyIntolerance.cruds
system/AllergyIntolerance.rs
```

**Search Parameters:**
- `patient` - Reference to patient
- `clinical-status` - active | inactive | resolved

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/AllergyIntolerance?patient=123' \
  -H 'Authorization: Bearer TOKEN'
```

#### Condition
**Description:** Patient problems, diagnoses, and health concerns.

**Scopes:**
```
patient/Condition.cruds
user/Condition.cruds
system/Condition.rs
```

**Granular Scopes:** ✨
```
patient/Condition.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern
patient/Condition.rs?category=http://terminology.hl7.org/CodeSystem/condition-category|encounter-diagnosis
patient/Condition.rs?category=http://terminology.hl7.org/CodeSystem/condition-category|problem-list-item
```

**Search Parameters:**
- `patient` - Reference to patient
- `category` - problem-list-item | encounter-diagnosis | health-concern
- `clinical-status` - active | inactive | resolved
- `onset-date` - Date condition began

**Example:**
```bash
# Get active problems
curl -X GET 'https://localhost:9300/apis/default/fhir/Condition?patient=123&clinical-status=active' \
  -H 'Authorization: Bearer TOKEN'
```

#### Procedure
**Description:** Procedures performed on patient.

**Scopes:**
```
patient/Procedure.cruds
user/Procedure.cruds
system/Procedure.rs
```

**Search Parameters:**
- `patient` - Reference to patient
- `date` - When procedure performed
- `code` - Procedure code (CPT, SNOMED)

#### Goal
**Description:** Patient health goals.

**Scopes:**
```
patient/Goal.cruds
user/Goal.cruds
system/Goal.rs
```

#### CarePlan
**Description:** Care plans for patient treatment.

**Scopes:**
```
patient/CarePlan.cruds
user/CarePlan.cruds
system/CarePlan.rs
```

#### CareTeam
**Description:** Care team members and roles.

**Scopes:**
```
patient/CareTeam.cruds
user/CareTeam.cruds
system/CareTeam.rs
```

### Medications

#### MedicationRequest
**Description:** Medication prescriptions and orders.

**Scopes:**
```
patient/MedicationRequest.cruds
user/MedicationRequest.cruds
system/MedicationRequest.rs
```

**Search Parameters:**
- `patient` - Reference to patient
- `status` - active | on-hold | cancelled | completed
- `intent` - order | plan | proposal
- `authoredon` - When prescribed

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/MedicationRequest?patient=123&status=active' \
  -H 'Authorization: Bearer TOKEN'
```

#### Medication
**Description:** Medication definitions.

**Scopes:**
```
patient/Medication.rs
user/Medication.cruds
system/Medication.rs
```

#### MedicationDispense ✨ NEW
**Description:** Pharmacy medication dispensing records.

**Scopes:**
```
patient/MedicationDispense.cruds
user/MedicationDispense.cruds
system/MedicationDispense.rs
```

**Search Parameters:**
- `patient` - Reference to patient
- `status` - preparation | in-progress | completed
- `whenhandedover` - When medication given to patient
- `prescription` - Reference to MedicationRequest

**Example Request:**
```bash
# Get dispensing records for patient
curl -X GET 'https://localhost:9300/apis/default/fhir/MedicationDispense?patient=123' \
  -H 'Authorization: Bearer TOKEN'
```

**Example Response:**
```json
{
  "resourceType": "MedicationDispense",
  "id": "789",
  "status": "completed",
  "medicationReference": {
    "reference": "Medication/456"
  },
  "subject": {
    "reference": "Patient/123"
  },
  "authorizingPrescription": [{
    "reference": "MedicationRequest/234"
  }],
  "quantity": {
    "value": 30,
    "unit": "tablets"
  },
  "whenHandedOver": "2024-01-15T10:30:00Z",
  "dosageInstruction": [{
    "text": "Take 1 tablet daily"
  }]
}
```

**Use Cases:**
- Pharmacy integration
- Medication adherence tracking
- Dispensing history review

#### Immunization
**Description:** Vaccination records.

**Scopes:**
```
patient/Immunization.cruds
user/Immunization.cruds
system/Immunization.rs
```

**Search Parameters:**
- `patient` - Reference to patient
- `date` - Vaccination date
- `status` - completed | entered-in-error

### Diagnostic Resources

#### Observation
**Description:** Clinical observations and lab results.

**Scopes:**
```
patient/Observation.cruds
user/Observation.cruds
system/Observation.rs
```

**Granular Scopes:** ✨
```
patient/Observation.rs?category=http://hl7.org/fhir/us/core/CodeSystem/us-core-category|sdoh
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|social-history
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|laboratory
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|survey
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs
```

**Search Parameters:**
- `patient` - Reference to patient
- `category` - vital-signs | laboratory | social-history | survey | sdoh
- `code` - Observation type (LOINC)
- `date` - Observation date

**Example:**
```bash
# Get vital signs
curl -X GET 'https://localhost:9300/apis/default/fhir/Observation?patient=123&category=vital-signs' \
  -H 'Authorization: Bearer TOKEN'

# Get lab results
curl -X GET 'https://localhost:9300/apis/default/fhir/Observation?patient=123&category=laboratory' \
  -H 'Authorization: Bearer TOKEN'
```

#### DiagnosticReport
**Description:** Diagnostic study reports (labs, radiology).

**Scopes:**
```
patient/DiagnosticReport.cruds
user/DiagnosticReport.cruds
system/DiagnosticReport.rs
```

**Search Parameters:**
- `patient` - Reference to patient
- `category` - LAB | RAD | etc.
- `code` - Report type
- `date` - Report date

#### ServiceRequest ✨ NEW
**Description:** Diagnostic and procedure service requests (lab orders, imaging orders, consults).

**Scopes:**
```
patient/ServiceRequest.cruds
user/ServiceRequest.cruds
system/ServiceRequest.rs
```

**Search Parameters:**
- `patient` - Reference to patient
- `status` - draft | active | completed | cancelled
- `intent` - order | plan | proposal
- `category` - Laboratory | Imaging | Consultation
- `code` - Service being requested
- `authored` - When request created

**Example Request:**
```bash
# Get lab orders for patient
curl -X GET 'https://localhost:9300/apis/default/fhir/ServiceRequest?patient=123&category=Laboratory' \
  -H 'Authorization: Bearer TOKEN'

# Get all active orders
curl -X GET 'https://localhost:9300/apis/default/fhir/ServiceRequest?patient=123&status=active' \
  -H 'Authorization: Bearer TOKEN'
```

**Example Response:**
```json
{
  "resourceType": "ServiceRequest",
  "id": "567",
  "status": "active",
  "intent": "order",
  "category": [{
    "coding": [{
      "system": "http://snomed.info/sct",
      "code": "108252007",
      "display": "Laboratory procedure"
    }]
  }],
  "code": {
    "coding": [{
      "system": "http://loinc.org",
      "code": "2093-3",
      "display": "Cholesterol [Mass/volume] in Serum or Plasma"
    }]
  },
  "subject": {
    "reference": "Patient/123"
  },
  "authoredOn": "2024-01-15T09:00:00Z",
  "requester": {
    "reference": "Practitioner/789"
  }
}
```

**Use Cases:**
- Order management systems
- Lab information systems integration
- Referral tracking
- Care coordination

#### Specimen ✨ NEW
**Description:** Laboratory specimen information.

**Scopes:**
```
patient/Specimen.cruds
user/Specimen.cruds
system/Specimen.rs
```

**Search Parameters:**
- `patient` - Reference to patient
- `type` - Specimen type (blood, urine, etc.)
- `collected` - Collection date/time
- `status` - available | unavailable

**Example Request:**
```bash
# Get specimens for patient
curl -X GET 'https://localhost:9300/apis/default/fhir/Specimen?patient=123' \
  -H 'Authorization: Bearer TOKEN'
```

**Example Response:**
```json
{
  "resourceType": "Specimen",
  "id": "890",
  "status": "available",
  "type": {
    "coding": [{
      "system": "http://snomed.info/sct",
      "code": "119297000",
      "display": "Blood specimen"
    }]
  },
  "subject": {
    "reference": "Patient/123"
  },
  "collection": {
    "collectedDateTime": "2024-01-15T08:00:00Z",
    "quantity": {
      "value": 10,
      "unit": "mL"
    }
  },
  "request": [{
    "reference": "ServiceRequest/567"
  }]
}
```

**Use Cases:**
- Laboratory information systems
- Specimen tracking
- Chain of custody
- Test result correlation

### Care Coordination

#### Encounter
**Description:** Patient encounters (visits, admissions).

**Scopes:**
```
patient/Encounter.cruds
user/Encounter.cruds
system/Encounter.rs
```

**Search Parameters:**
- `patient` - Reference to patient
- `date` - Encounter date
- `status` - planned | in-progress | finished
- `class` - ambulatory | emergency | inpatient

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Encounter?patient=123&status=finished' \
  -H 'Authorization: Bearer TOKEN'
```

#### Appointment
**Description:** Scheduled patient appointments.

**Scopes:**
```
patient/Appointment.cruds
user/Appointment.cruds
system/Appointment.rs
```

**Search Parameters:**
- `patient` - Reference to patient
- `date` - Appointment date
- `status` - proposed | pending | booked | arrived | fulfilled | cancelled

#### DocumentReference
**Description:** Clinical documents and attachments.

**Scopes:**
```
patient/DocumentReference.cruds
user/DocumentReference.cruds
system/DocumentReference.rs
```

**Granular Scopes:** ✨
```
patient/DocumentReference.rs?category=http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category|clinical-note
```

**Search Parameters:**
- `patient` - Reference to patient
- `category` - clinical-note | imaging | laboratory
- `type` - Document type
- `date` - Document date

**Special Operation:** `$docref` - See [DocumentReference $docref](#documentreference-docref-operation)

#### Coverage
**Description:** Insurance coverage information.

**Scopes:**
```
patient/Coverage.cruds
user/Coverage.cruds
system/Coverage.rs
```

### Administrative Resources

#### Practitioner
**Description:** Healthcare provider information.

**Scopes:**
```
patient/Practitioner.rs
user/Practitioner.cruds
system/Practitioner.rs
```

**Search Parameters:**
- `name` - Provider name
- `identifier` - NPI or other identifier

#### PractitionerRole
**Description:** Provider roles and specialties.

**Scopes:**
```
patient/PractitionerRole.rs
user/PractitionerRole.cruds
system/PractitionerRole.rs
```

#### Organization
**Description:** Healthcare organizations and facilities.

**Scopes:**
```
patient/Organization.rs
user/Organization.cruds
system/Organization.rs
```

**Search Parameters:**
- `name` - Organization name
- `address` - Organization address

#### Location
**Description:** Physical locations and facilities.

**Scopes:**
```
patient/Location.rs
user/Location.cruds
system/Location.rs
```

#### Device
**Description:** Medical devices and implants.

**Scopes:**
```
patient/Device.cruds
user/Device.cruds
system/Device.rs
```

#### Group
**Description:** Groups of patients (for bulk operations).

**Scopes:**
```
system/Group.rs
system/Group.$export
```

**Note:** Group resource is system-level only.

#### Binary
**Description:** Binary data (documents, images, exports).

**Scopes:**
```
patient/Binary.rs
user/Binary.rs
system/Binary.rs
```

**Note:** Binary resources are read-only.

### Provenance
**Description:** Data provenance and attribution.

**Scopes:**
```
patient/Provenance.rs
user/Provenance.rs
system/Provenance.rs
```

**Note:** Provenance is read-only. See [Provenance section](#provenance).

### New Resources (SMART v2.2.0)

Summary of newly supported resources:

| Resource | Description | Primary Use Case |
|----------|-------------|------------------|
| **ServiceRequest** | Diagnostic/procedure orders | Lab orders, imaging requests, referrals |
| **Specimen** | Laboratory specimens | Lab tracking, chain of custody |
| **MedicationDispense** | Pharmacy dispensing | Medication adherence, pharmacy integration |
| **RelatedPerson** | Patient relationships | Emergency contacts, care coordination |

All new resources support full CRUDS operations in patient and user contexts, and read/search in system context.

## Search Parameters

### Common Search Parameters

All resources support common search parameters:

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `_id` | token | Resource ID | `?_id=123` |
| `_lastUpdated` | date | Last modified date | `?_lastUpdated=gt2024-01-01` |
| `_count` | number | Results per page | `?_count=50` |
| `_offset` | number | Result offset (deprecated) | `?_offset=100` |

### Patient Search Parameter

Most clinical resources support patient search:
```
?patient=123
?patient=Patient/123
?subject=Patient/123
```

**Examples:**
```bash
# All observations for patient
GET /fhir/Observation?patient=123

# All medications for patient
GET /fhir/MedicationRequest?patient=123

# All conditions for patient
GET /fhir/Condition?patient=123
```

### Date Range Searches

Use prefixes for date comparisons:

| Prefix | Meaning | Example |
|--------|---------|---------|
| `eq` | Equal | `?date=eq2024-01-15` |
| `ne` | Not equal | `?date=ne2024-01-15` |
| `lt` | Less than | `?date=lt2024-01-15` |
| `le` | Less than or equal | `?date=le2024-01-15` |
| `gt` | Greater than | `?date=gt2024-01-01` |
| `ge` | Greater than or equal | `?date=ge2024-01-01` |

**Examples:**
```bash
# Encounters since January 1, 2024
GET /fhir/Encounter?patient=123&date=ge2024-01-01

# Lab results in January 2024
GET /fhir/Observation?patient=123&category=laboratory&date=ge2024-01-01&date=le2024-01-31
```

### Category Searches

Filter resources by category:

**Observations:**
```bash
GET /fhir/Observation?patient=123&category=vital-signs
GET /fhir/Observation?patient=123&category=laboratory
GET /fhir/Observation?patient=123&category=social-history
```

**Conditions:**
```bash
GET /fhir/Condition?patient=123&category=problem-list-item
GET /fhir/Condition?patient=123&category=encounter-diagnosis
```

### Code Searches

Search by clinical codes (LOINC, SNOMED, CPT):
```bash
# Specific observation by LOINC code
GET /fhir/Observation?patient=123&code=http://loinc.org|2093-3

# Procedures by CPT code
GET /fhir/Procedure?patient=123&code=http://www.ama-assn.org/go/cpt|99213
```

### Status Searches

Filter by resource status:
```bash
# Active medications
GET /fhir/MedicationRequest?patient=123&status=active

# Completed encounters
GET /fhir/Encounter?patient=123&status=finished

# Active problems
GET /fhir/Condition?patient=123&clinical-status=active
```

### Pagination

Use `_count` to limit results:
```bash
# Get 20 results at a time
GET /fhir/Observation?patient=123&_count=20
```

**Response includes pagination links:**
```json
{
  "resourceType": "Bundle",
  "type": "searchset",
  "total": 150,
  "link": [
    {
      "relation": "self",
      "url": "https://localhost:9300/apis/default/fhir/Observation?patient=123&_count=20"
    },
    {
      "relation": "next",
      "url": "https://localhost:9300/apis/default/fhir/Observation?patient=123&_count=20&page=2"
    }
  ],
  "entry": [...]
}
```

### Including Related Resources

Use `_include` and `_revinclude` to fetch related resources:
```bash
# Include referenced Practitioner in MedicationRequest
GET /fhir/MedicationRequest?patient=123&_include=MedicationRequest:requester

# Include Provenance for AllergyIntolerance
GET /fhir/AllergyIntolerance?patient=123&_revinclude=Provenance:target
```

## Provenance

Provenance resources track data origin, authorship, and modifications for transparency and auditing.

### Requesting Provenance

Include `_revinclude=Provenance:target` in your search:
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/AllergyIntolerance?patient=123&_revinclude=Provenance:target' \
  -H 'Authorization: Bearer TOKEN'
```

### Supported Resources

Provenance is available for:
- AllergyIntolerance
- (Additional resources to be added)

### Example Response
```json
{
  "resourceType": "Bundle",
  "type": "searchset",
  "entry": [
    {
      "resource": {
        "resourceType": "AllergyIntolerance",
        "id": "456",
        "patient": {"reference": "Patient/123"},
        "code": {
          "coding": [{
            "system": "http://snomed.info/sct",
            "code": "387207008",
            "display": "Penicillin"
          }]
        }
      }
    },
    {
      "resource": {
        "resourceType": "Provenance",
        "id": "789",
        "target": [{"reference": "AllergyIntolerance/456"}],
        "recorded": "2024-01-15T10:00:00Z",
        "agent": [{
          "who": {"reference": "Practitioner/101"}
        }],
        "activity": {
          "coding": [{
            "system": "http://terminology.hl7.org/CodeSystem/v3-DataOperation",
            "code": "CREATE"
          }]
        }
      }
    }
  ]
}
```

### Provenance Information

Provenance reveals:
- **Who** created or modified the data
- **When** the action occurred
- **What** action was performed (CREATE, UPDATE)
- **Why** the action was taken (if recorded)

## Bulk FHIR Exports

OpenEMR implements the **FHIR Bulk Data Export specification** for large-scale data access.

### Overview

Bulk exports enable:
- ✅ Population health analytics
- ✅ Research data extraction
- ✅ Data warehouse integration
- ✅ Quality measure reporting
- ✅ ONC compliance (21st Century Cures Act)

### Requirements

**Authentication:** Client Credentials Grant with JWKS required

**Scopes:** System-level export scopes

**Format:** NDJSON (Newline Delimited JSON)

### Export Types

| Export Type | Scope | Endpoint | Data Scope |
|-------------|-------|----------|------------|
| **System Export** | `system/*.$export` | `GET /fhir/$export` | All data |
| **Patient Export** | `system/Patient.$export` | `GET /fhir/Patient/$export` | All patient compartment data |
| **Group Export** | `system/Group.$export` | `GET /fhir/Group/[id]/$export` | Group patient compartment data |

### System Export

Export **all supported resources** for all patients.

#### Required Scopes
```
system/*.$export
system/*.$bulkdata-status
system/Binary.read
```

#### Request
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/$export' \
  -H 'Authorization: Bearer TOKEN' \
  -H 'Accept: application/fhir+json' \
  -H 'Prefer: respond-async'
```

#### Response (202 Accepted)
```http
HTTP/1.1 202 Accepted
Content-Location: https://localhost:9300/apis/default/fhir/$bulkdata-status?job=92a94c00-77d6-4dfc-ae3b
```

The `Content-Location` header contains the status polling URL.

### Patient Export

Export **all patient compartment data** for all patients.

#### Required Scopes
```
system/Patient.$export
system/*.$bulkdata-status
system/Binary.read
```

#### Request
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Patient/$export' \
  -H 'Authorization: Bearer TOKEN' \
  -H 'Accept: application/fhir+json' \
  -H 'Prefer: respond-async'
```

#### Patient Compartment

Includes all resources in the [Patient Compartment](https://www.hl7.org/fhir/compartmentdefinition-patient.html):
- Patient
- Observation
- Condition
- MedicationRequest
- Procedure
- Encounter
- AllergyIntolerance
- (and more)

### Group Export

Export data for **a specific group** of patients.

#### Required Scopes
```
system/Group.$export
system/*.$bulkdata-status
system/Binary.read
```

#### Request
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Group/1/$export' \
  -H 'Authorization: Bearer TOKEN' \
  -H 'Accept: application/fhir+json' \
  -H 'Prefer: respond-async'
```

#### Group Definition

OpenEMR automatically creates groups:
- **By Practitioner:** Patients with practitioner as primary care provider
- Group ID corresponds to practitioner ID

**Example:** Group 5 contains all patients with Practitioner 5 as PCP.

### Export Status

Check export job status using the `Content-Location` URL.

#### Required Scope
```
system/*.$bulkdata-status
```

#### Request
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/$bulkdata-status?job=92a94c00-77d6-4dfc-ae3b' \
  -H 'Authorization: Bearer TOKEN'
```

#### Response (In Progress)
```http
HTTP/1.1 202 Accepted
X-Progress: 50% complete
Retry-After: 120
```

#### Response (Complete)
```json
{
  "transactionTime": "2024-01-15T14:30:00.000Z",
  "request": "/apis/default/fhir/Group/1/$export",
  "requiresAccessToken": true,
  "output": [
    {
      "type": "Patient",
      "url": "https://localhost:9300/apis/default/fhir/Binary/97552"
    },
    {
      "type": "Observation",
      "url": "https://localhost:9300/apis/default/fhir/Binary/97553"
    },
    {
      "type": "Condition",
      "url": "https://localhost:9300/apis/default/fhir/Binary/97554"
    },
    {
      "type": "MedicationRequest",
      "url": "https://localhost:9300/apis/default/fhir/Binary/97555"
    }
  ],
  "error": []
}
```

**Fields:**
- `transactionTime` - When export completed
- `request` - Original export request
- `requiresAccessToken` - If true, use Bearer token to download
- `output` - Array of output files by resource type
- `error` - Array of error files (if any)

### Download Files

Download exported NDJSON files using Binary resource URLs.

#### Required Scope
```
system/Binary.read
```

#### Request
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Binary/97552' \
  -H 'Authorization: Bearer TOKEN' \
  -o patients.ndjson
```

#### Response Format (NDJSON)
```
{"resourceType":"Patient","id":"1",...}
{"resourceType":"Patient","id":"2",...}
{"resourceType":"Patient","id":"3",...}
```

Each line is a complete JSON FHIR resource.

### Complete Export Workflow
```bash
# Step 1: Initiate export (get new token - client credentials)
TOKEN=$(curl -X POST https://localhost:9300/oauth2/default/token \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  --data-urlencode 'grant_type=client_credentials' \
  --data-urlencode 'client_assertion_type=urn:ietf:params:oauth:client-assertion-type:jwt-bearer' \
  --data-urlencode "client_assertion=$JWT" \
  --data-urlencode 'scope=system/Patient.$export system/*.$bulkdata-status system/Binary.read' \
  | jq -r '.access_token')

# Step 2: Start export
CONTENT_LOCATION=$(curl -X GET \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/fhir+json" \
  -H "Prefer: respond-async" \
  -i https://localhost:9300/apis/default/fhir/Patient/\$export \
  | grep -i 'Content-Location:' | cut -d' ' -f2 | tr -d '\r')

# Step 3: Poll for completion
while true; do
  STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Authorization: Bearer $TOKEN" \
    "$CONTENT_LOCATION")

  if [ "$STATUS" == "200" ]; then
    echo "Export complete!"
    break
  else
    echo "Export in progress... (Status: $STATUS)"
    sleep 30
  fi
done

# Step 4: Get download URLs
EXPORT_MANIFEST=$(curl -s -H "Authorization: Bearer $TOKEN" "$CONTENT_LOCATION")
echo "$EXPORT_MANIFEST" | jq -r '.output[] | "\(.type): \(.url)"'

# Step 5: Download files
echo "$EXPORT_MANIFEST" | jq -r '.output[].url' | while read URL; do
  FILENAME=$(echo "$URL" | sed 's/.*Binary\///' | sed 's/$/.ndjson/')
  curl -H "Authorization: Bearer $TOKEN" "$URL" -o "$FILENAME"
  echo "Downloaded: $FILENAME"
done
```

### Export Parameters

Customize exports with query parameters:

**Filter by resource type:**
```
GET /fhir/$export?_type=Patient,Observation,Condition
```

**Filter by date:**
```
GET /fhir/$export?_since=2024-01-01T00:00:00Z
```

**Combine filters:**
```
GET /fhir/Patient/$export?_type=Observation,Condition&_since=2024-01-01T00:00:00Z
```

## DocumentReference $docref Operation

Generate **Continuity of Care Documents (CCD)** on demand.

### Overview

The `$docref` operation creates clinical summary documents (C-CDA) for:
- Care transitions
- Referrals
- Patient requests
- External system integration

### Required Scopes
```
patient/DocumentReference.$docref
patient/DocumentReference.read
patient/Binary.read
```

Or user/system context equivalents.

### Request
```bash
curl -X POST 'https://localhost:9300/apis/default/fhir/DocumentReference/$docref' \
  -H 'Authorization: Bearer TOKEN' \
  -H 'Content-Type: application/fhir+json' \
  --data '{
    "resourceType": "Parameters",
    "parameter": [
      {
        "name": "patient",
        "valueId": "123"
      },
      {
        "name": "start",
        "valueDate": "2024-01-01"
      },
      {
        "name": "end",
        "valueDate": "2024-12-31"
      }
    ]
  }'
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `patient` | id | Yes | Patient ID |
| `start` | date | No | Start date for encounter filtering |
| `end` | date | No | End date for encounter filtering |
| `type` | code | No | Document type code |

### Date Filtering Behavior

**No dates:** Entire patient history

**Start date only:** From start date to present

**End date only:** All history up to end date

**Both dates:** Specific date range

**Same start/end:** Single day

**Precision:**
- `YYYY` - Full year
- `YYYY-MM` - Full month
- `YYYY-MM-DD` - Specific day

### Response
```json
{
  "resourceType": "Bundle",
  "type": "searchset",
  "total": 1,
  "entry": [{
    "resource": {
      "resourceType": "DocumentReference",
      "id": "ccd-123-20240115",
      "status": "current",
      "type": {
        "coding": [{
          "system": "http://loinc.org",
          "code": "34133-9",
          "display": "Summarization of Episode Note"
        }]
      },
      "category": [{
        "coding": [{
          "system": "http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category",
          "code": "clinical-note"
        }]
      }],
      "subject": {
        "reference": "Patient/123"
      },
      "date": "2024-01-15T14:30:00Z",
      "content": [{
        "attachment": {
          "contentType": "application/xml",
          "url": "https://localhost:9300/apis/default/fhir/Binary/98765"
        }
      }]
    }
  }]
}
```

### Download CCD
```bash
# Get CCD XML
curl -X GET 'https://localhost:9300/apis/default/fhir/Binary/98765' \
  -H 'Authorization: Bearer TOKEN' \
  -o patient-ccd.xml
```

### CCD Sections

Documents include:

**Date-Filtered Sections (encounter-based):**
- History of Procedures
- Relevant DX Tests / LAB Data
- Functional Status
- Progress Notes
- Procedure Notes
- Laboratory Report Narrative
- Encounters
- Assessments
- Treatment Plan
- Goals
- Health Concerns Document
- Reason for Referral
- Mental Status

**Full History Sections:**
- Demographics
- Allergies, Adverse Reactions, Alerts
- History of Medication Use
- Problem List
- Immunizations
- Social History
- Medical Equipment
- Vital Signs (most recent)

### Viewing CCDs

**Option 1: XSL Transform**

Download XSL stylesheet:
```
GET /interface/modules/zend_modules/public/xsl/cda.xsl
```

Place in same directory as CCD XML for browser rendering.

**Option 2: Upload to OpenEMR**

Upload XML to patient documents under "CCDA" category for human-readable view.

### Tutorial

Complete tutorial with screenshots:
https://github.com/openemr/openemr/issues/5284#issuecomment-1155678620

## SMART Configuration

**New in SMART v2.2.0:** Discover SMART capabilities via dedicated endpoint.

### Endpoint
```
GET /fhir/.well-known/smart-configuration
```

**No authentication required.**

### Request
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/.well-known/smart-configuration'
```

### Response
```json
{
  "issuer": "https://localhost:9300/oauth2/default",
  "authorization_endpoint": "https://localhost:9300/oauth2/default/authorize",
  "token_endpoint": "https://localhost:9300/oauth2/default/token",
  "introspection_endpoint": "https://localhost:9300/oauth2/default/introspect",
  "revocation_endpoint": "https://localhost:9300/oauth2/default/revoke",
  "token_endpoint_auth_methods_supported": [
    "client_secret_basic",
    "client_secret_post",
    "private_key_jwt"
  ],
  "registration_endpoint": "https://localhost:9300/oauth2/default/registration",
  "scopes_supported": [
    "openid",
    "fhirUser",
    "launch",
    "launch/patient",
    "launch/encounter",
    "offline_access",
    "online_access",
    "patient/*.cruds",
    "user/*.cruds",
    "system/*.rs"
  ],
  "response_types_supported": ["code"],
  "capabilities": [
    "launch-ehr",
    "launch-standalone",
    "client-public",
    "client-confidential-symmetric",
    "client-confidential-asymmetric",
    "context-banner",
    "context-style",
    "context-ehr-patient",
    "context-ehr-encounter",
    "sso-openid-connect",
    "permission-offline",
    "permission-patient",
    "permission-user"
  ],
  "code_challenge_methods_supported": ["S256"]
}
```

### SMART Capabilities

| Capability | Description |
|------------|-------------|
| `launch-ehr` | Supports EHR launch flow |
| `launch-standalone` | Supports standalone launch flow |
| `client-public` | Supports public clients |
| `client-confidential-symmetric` | Supports client secrets |
| `client-confidential-asymmetric` | Supports JWKS authentication |
| `context-ehr-patient` | Provides patient context in EHR launch |
| `context-ehr-encounter` | Provides encounter context in EHR launch |
| `sso-openid-connect` | OpenID Connect single sign-on |
| `permission-offline` | Offline access (refresh tokens) |
| `permission-patient` | Patient-level scopes |
| `permission-user` | User-level scopes |

See [SMART on FHIR Documentation](SMART_ON_FHIR.md) for details.

## Error Handling

### HTTP Status Codes

| Code | Meaning | Common Causes |
|------|---------|---------------|
| 200 | OK | Successful request |
| 201 | Created | Resource created successfully |
| 202 | Accepted | Bulk export initiated |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Insufficient scopes |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation error |
| 500 | Internal Server Error | Server error |

### Error Response Format
```json
{
  "resourceType": "OperationOutcome",
  "issue": [{
    "severity": "error",
    "code": "security",
    "diagnostics": "Insufficient scope for requested resource"
  }]
}
```

### Common Errors

**401 Unauthorized - Missing Token**
```json
{
  "error": "invalid_token",
  "error_description": "The access token is missing"
}
```

**Solution:** Include `Authorization: Bearer TOKEN` header

**403 Forbidden - Insufficient Scopes**
```json
{
  "resourceType": "OperationOutcome",
  "issue": [{
    "severity": "error",
    "code": "security",
    "diagnostics": "Insufficient scope: patient/Observation.rs required"
  }]
}
```

**Solution:** Request appropriate scopes during authorization

**404 Not Found**
```json
{
  "resourceType": "OperationOutcome",
  "issue": [{
    "severity": "error",
    "code": "not-found",
    "diagnostics": "Resource Patient/99999 not found"
  }]
}
```

**Solution:** Verify resource ID exists

**422 Validation Error**
```json
{
  "resourceType": "OperationOutcome",
  "issue": [{
    "severity": "error",
    "code": "invalid",
    "diagnostics": "Invalid date format: expected YYYY-MM-DD"
  }]
}
```

**Solution:** Fix request data format

## Examples

### Example 1: Get Patient Demographics
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Patient/123' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...' \
  -H 'Accept: application/fhir+json'
```

### Example 2: Search Active Medications
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/MedicationRequest?patient=123&status=active' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...'
```

### Example 3: Get Recent Vital Signs
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Observation?patient=123&category=vital-signs&date=ge2024-01-01&_count=10' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...'
```

### Example 4: Get Lab Results with Provenance
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Observation?patient=123&category=laboratory&_revinclude=Provenance:target' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...'
```

### Example 5: Generate CCD for Date Range
```bash
curl -X POST 'https://localhost:9300/apis/default/fhir/DocumentReference/$docref' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...' \
  -H 'Content-Type: application/fhir+json' \
  --data '{
    "resourceType": "Parameters",
    "parameter": [
      {"name": "patient", "valueId": "123"},
      {"name": "start", "valueDate": "2024-01-01"},
      {"name": "end", "valueDate": "2024-03-31"}
    ]
  }'
```

### Example 6: Search Service Requests (New)
```bash
# Get all active lab orders
curl -X GET 'https://localhost:9300/apis/default/fhir/ServiceRequest?patient=123&status=active&category=Laboratory' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...'
```

### Example 7: Get Medication Dispense History (New)
```bash
# Get dispensing records for last 6 months
curl -X GET 'https://localhost:9300/apis/default/fhir/MedicationDispense?patient=123&whenhandedover=ge2023-07-01' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...'
```

### Example 8: Get Emergency Contacts (New)
```bash
# Get related persons for patient
curl -X GET 'https://localhost:9300/apis/default/fhir/RelatedPerson?patient=123' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...'
```

### Example 9: Get Specimens for Lab Order (New)
```bash
# Get specimens collected for service request
curl -X GET 'https://localhost:9300/apis/default/fhir/Specimen?patient=123' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...'
```

## For Developers

### Internal API Usage

OpenEMR supports internal API calls from within authenticated sessions.

**Example:** See `tests/api/InternalApiTest.php`

### Request Processing Flow
```
JSON Request
  ↓
FHIR Controller
  ↓
FHIR Validation
  ↓
Parse FHIR Resource
  ↓
Service Component
  ↓
Validation
  ↓
Database
```

### Response Processing Flow
```
Database Result
  ↓
Service Component
  ↓
FHIR Service Component
  ↓
Parse OpenEMR Record
  ↓
FHIR Controller
  ↓
RequestControllerHelper
  ↓
JSON Response
```

### Adding FHIR Resources

1. **Create FHIR Service:** `src/Services/FHIR/Fhir[Resource]Service.php`
2. **Create Controller:** `src/RestControllers/FHIR/Fhir[Resource]RestController.php`
3. **Add Route:** `_rest_routes.inc.php`
4. **Add to Capability Statement**
5. **Update Documentation**

### Route Definition Example
```php
"GET /fhir/Patient/:id" => function ($id) {
    RestConfig::request_authorization_check($request, "patients", "demo");
    $return = (new FhirPatientRestController())->getOne($id);
    return $return;
}
```

### Testing

**Swagger UI:** Interactive API testing at `/swagger/`

**Online Demos:** https://www.open-emr.org/wiki/index.php/Development_Demo

**Unit Tests:** `tests/api/`

---

**Next Steps:**
- Review [Authentication](AUTHENTICATION.md) for OAuth2 setup
- See [Authorization](AUTHORIZATION.md) for scope details
- Learn about [SMART on FHIR](SMART_ON_FHIR.md) integration
- Check [Developer Guide](DEVELOPER_GUIDE.md) for advanced topics

**Support:**
- Community Forum: https://community.open-emr.org/
- FHIR Specification: https://hl7.org/fhir/R4/
- US Core IG: https://www.hl7.org/fhir/us/core/

**Swagger Documentation:**
- Production: `https://your-openemr-install/swagger/`
- Online Demo: https://www.open-emr.org/wiki/index.php/Development_Demo

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
