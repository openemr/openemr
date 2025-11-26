# FHIR API

Complete guide to OpenEMR's FHIR R4 API implementation.

## Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Base URL](#base-url)
- [Authentication](#authentication)
- [Capability Statement](#capability-statement)
- [Supported Resources](#supported-resources)
    - [Administrative Resources](#administration-resources)
    - [Clinical Resources](#clinical-resources)
    - [Diagnostic Resources](#diagnostic-resources)
    - [Medication Resources](#medication-resources)
    - [Document Resources](#document-resources)
    - [Financial Resources](#financial-resources)
    - [Security and Privacy](#security-and-privacy)
    - [Terminology](#terminology)
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
- [Backwards Compatibility](#backwards-compatibility)

## Overview

OpenEMR provides a comprehensive **FHIR R4** implementation compliant with:
- ✅ **FHIR R4 Specification** - HL7 FHIR Release 4
- ✅ **US Core 8.0 Implementation Guide** - US healthcare requirements
- ✅ **SMART on FHIR v2.2.0** - App launch and authorization
- ✅ **Bulk Data IG** - ONC-required bulk export operations

### Key Features
- **30+ FHIR Resources** supported
- **Granular scopes** for fine-grained access control
- **Bulk exports** for population health and analytics
- **CCD-A generation** via DocumentReference $docref
- **Provenance tracking** for data transparency
- **SMART app integration** with EHR and standalone launch

### Standards Compliance

| Standard | Version    | Status             |
|----------|------------|--------------------|
| FHIR | R4 (4.0.1) | ✅ Baseline Support |
| US Core | 8.0        | ✅ Compliant        |
| SMART on FHIR | v2.2.0     | ✅ Certified        |
| Bulk Data | v1.0       | ✅ Implemented      |
| USCDI | v5         | ✅ Supported        |

## Prerequisites

### 1. Enable FHIR API

Navigate to: **Administration → Config → Connectors**

Enable: **☑ Enable OpenEMR Standard FHIR REST API**

### 2. Configure SSL/TLS

**Required:** All FHIR endpoints require HTTPS/TLS.

Set base URL: **Administration → Config → Connectors → Site Address (required for OAuth2 and FHIR)**

Example: `https://your-openemr.example.com` or `https://localhost:9300` for local testing.  If installed in a subdirectory, include it (e.g. `https://your-openemr.example.com/openemr`).

Note that several curl examples are given in this guide.  If your OpenEMR instance is using a self-signed certificate you will need to pass -k to curl to disable certificate verification for testing purposes.

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
{base}/[resource-type]/[operation]
```

**Examples:**
```
GET https://localhost:9300/apis/default/fhir/Patient/123
GET https://localhost:9300/apis/default/fhir/Observation?patient=123
POST https://localhost:9300/apis/default/fhir/Patient/$docref
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
- [Authorization Code Grant](AUTHENTICATION.md#authorization-code-grant) (Frontend apps with user interaction)
- [Client Credentials Grant](AUTHENTICATION.md#client-credentials-grant) (Backend or Frontend services - client apps that can manage and sign assertions with asymmetric keys)
- [EHR Launch Flow](AUTHENTICATION.md#ehr-launch-flow) (SMART apps launched from within OpenEMR)

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
    "date": "2025-11-25",
    "kind": "instance",
    "instantiates": [
        "http://hl7.org/fhir/us/core/CapabilityStatement/us-core-server",
        "http://hl7.org/fhir/uv/bulkdata/CapabilityStatement/bulk-data"
    ],
    "software": {
        "name": "OpenEMR",
        "version": "7.0.4"
    },
    "implementation": {
        "description": "OpenEMR FHIR API",
        "url": "https://localhost:9300/apis/default/fhir"
    },
    "fhirVersion": "4.0.1",
    "format": [
        "application/json"
    ],
    "rest": [
        {
            "mode": "server",
            "security": {
                "extension": [
                    {
                        "valueCode": "launch-ehr",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    },
                    {
                        "valueCode": "context-passthrough-banner",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    },
                    {
                        "valueCode": "context-ehr-patient",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    },
                    {
                        "valueCode": "context-passthrough-style",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    },
                    {
                        "valueCode": "sso-openid-connect",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    },
                    {
                        "valueCode": "client-confidential-symmetric",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    },
                    {
                        "valueCode": "permission-user",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    },
                    {
                        "valueCode": "context-standalone-patient",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    },
                    {
                        "valueCode": "launch-standalone",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    },
                    {
                        "valueCode": "permission-patient",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    },
                    {
                        "valueCode": "permission-offline",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    },
                    {
                        "valueCode": "client-public",
                        "url": "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities"
                    }
                ],
                "service": [
                    {
                        "coding": [
                            {
                                "system": "http://terminology.hl7.org/CodeSystem/restful-security-service",
                                "code": "SMART-on-FHIR",
                                "display": "SMART-on-FHIR"
                            }
                        ],
                        "text": "OAuth2 using SMART-on-FHIR profile (see http://docs.smarthealthit.org)"
                    }
                ]
            },
            "resource": [
                {
                    "type": "Patient",
                    "profile": "http://hl7.org/fhir/StructureDefinition/Patient",
                    "supportedProfile": [
                        "http://hl7.org/fhir/us/core/StructureDefinition/us-core-patient",
                        "http://hl7.org/fhir/us/core/StructureDefinition/us-core-patient|3.1.1",
                        "http://hl7.org/fhir/us/core/StructureDefinition/us-core-patient|8.0.0"
                    ],
                    "interaction": [
                        {
                            "code": "create"
                        },
                        {
                            "code": "update"
                        },
                        {
                            "code": "search-type"
                        },
                        {
                            "code": "read"
                        }
                    ],
                    "updateCreate": false,
                    "searchInclude": [
                        "*"
                    ],
                    "searchRevInclude": [
                        "Provenance:target"
                    ],
                    "searchParam": [
                        {
                            "name": "_id",
                            "type": "token"
                        },
                        {
                            "name": "identifier",
                            "type": "token"
                        },
                        {
                            "name": "name",
                            "type": "string"
                        },
                        {
                            "name": "birthdate",
                            "type": "date"
                        },
                        {
                            "name": "gender",
                            "type": "token"
                        },
                        {
                            "name": "address",
                            "type": "string"
                        },
                        {
                            "name": "address-city",
                            "type": "string"
                        },
                        {
                            "name": "address-postalcode",
                            "type": "string"
                        },
                        {
                            "name": "address-state",
                            "type": "string"
                        },
                        {
                            "name": "email",
                            "type": "token"
                        },
                        {
                            "name": "family",
                            "type": "string"
                        },
                        {
                            "name": "given",
                            "type": "string"
                        },
                        {
                            "name": "phone",
                            "type": "token"
                        },
                        {
                            "name": "telecom",
                            "type": "token"
                        },
                        {
                            "name": "_lastUpdated",
                            "type": "date"
                        },
                        {
                            "name": "generalPractitioner",
                            "type": "reference"
                        }
                    ],
                    "operation": [
                        {
                            "name": "export",
                            "definition": "http://hl7.org/fhir/uv/bulkdata/OperationDefinition/patient-export"
                        }
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

OpenEMR supports **30 FHIR R4 resources** across all contexts (patient, user, system).

The resources that OpenEMR supports is documented via Swagger. You can see this documentation (and can test it) by going to the swagger directory in your OpenEMR installation. The FHIR API is documented there in the fhir section. Can also see (and test) this in the online demos at https://www.open-emr.org/wiki/index.php/Development_Demo#Daily_Build_Development_Demos (clicking on the API (Swagger) User Interface link for the demo will take you there).

### Administration Resources
- Patient ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Patient))
- Practitioner ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Practitioner))
- PractitionerRole ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_PractitionerRole))
- CareTeam ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_CareTeam))
- Device ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Device))
- Organization ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Organization))
- Location ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Location))
- Person ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Person))
- RelatedPerson ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_RelatedPerson))
- Group ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Group))
- Encounter ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Encounter))
- Appointment ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Appointment))


### Clinical Resources
- AllergyIntolerance ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_AllergyIntolerance))
- Condition ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Condition))
- - Problem List
- - Health Concerns
- - Diagnoses
- Procedure ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Procedure))
- CarePlan ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_CarePlan))
- Goal ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Goal))
- CareTeam ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_CareTeam))
- DiagnosticReport for Clinical Notes ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_DiagnosticReport))

### Diagnostic Resources
- Observation ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Observation))
- - Laboratory
- - Vital Signs
- - Social History
- - Social Determinants of Health
- - Advance Directives
- - Care Experience Preferences
- - Occupation
- - Survey Responses
- - Treatment Intervention Preferences
- - Pregnancy Status
- - Pregnancy Intent
- - Sexual Orientation
- - Uncategorized Observations
- DiagnosticReport for Laboratory Results ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_DiagnosticReport))
- ServiceRequest([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_ServiceCategory))
- Media ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Media))
- Specimen ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Specimen))

### Medication Resources
- MedicationRequest ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_MedicationRequest))
- Medication ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Medication))
- MedicationDispense ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_MedicationDispense))
- Immunization ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Immunization))

### Document Resources
- DocumentReference ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_DocumentReference))
- Binary ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Binary))

### Financial Resources
- Coverage ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Coverage))

### Security and Privacy
- Provenance ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_Provenance))

### Terminology
- ValueSet ([swagger](https://demo.openemr.io/openemr/swagger/#/fhir/get_fhir_ValueSet))

## Bulk FHIR Exports

OpenEMR implements the **[FHIR Bulk Data Export specification](https://hl7.org/fhir/uv/bulkdata/)** for large-scale data access.

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
Replace `TOKEN` with your access token.
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
Replace `TOKEN` with your access token.
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

Export data for **a specific group** of patients.  In OpenEMR a Group is automatically created for every practitioner containing their assigned patients.  Patients are included based on their primary care provider

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
- **By Practitioner:** Patients with practitioner as primary care provider as specified in the `patient_data.providerID` field in the OpenEMR system.
- Group ID is the `uuid` column from the `uuid_mapping` table where the `resource_type` is `Group` and the `target_uuid` is the practitioner's ID converted to binary.

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

Each line is a complete JSON FHIR resource for that resource type.

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

### Testing

**Swagger UI:** Interactive API testing at `/swagger/`

**Online Demos:** https://www.open-emr.org/wiki/index.php/Development_Demo

## Backwards Compatibility

The FHIR API is designed to maintain backwards compatibility for existing integrations. New resources and operations are added in a way that does not break existing functionality.

US Core 3.1, 7.0, and 8.0 has profiles that in some resources conflict with each other.  If your specific OpenEMR implementation requires strict adherence to a specific US Core IG version, you can set the Maximum US Core IG version you support in the OpenEMR Admin->Config->Connectors->Maximum supported version for US Core FHIR Implementation Guide to be 3.1, 7.0, or 8.0.  This will ensure that the FHIR API only advertises and supports profiles up to that version.

---

**Next Steps:**
- Review [Authentication](AUTHENTICATION.md) for OAuth2 setup
- See [Authorization](AUTHORIZATION.md) for scope details
- Learn about [SMART on FHIR](SMART_ON_FHIR.md) integration
- Check [Developer Guide](DEVELOPER_GUIDE.md) for advanced topics

**Support:**
- Community Forum: https://community.open-emr.org/
- FHIR Specification: https://hl7.org/fhir/R4/
- US Core 8.0 IG: https://hl7.org/fhir/us/core/STU8/

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
