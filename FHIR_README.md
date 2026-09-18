# OpenEMR FHIR API Documentation

> **📚 Complete FHIR documentation has moved to [Documentation/api/FHIR_API.md](Documentation/api/FHIR_API.md)**

OpenEMR provides a comprehensive **FHIR R4** implementation compliant with US Core 8.0 and SMART on FHIR v2.2.0.

## 🚀 Quick Start

### 1. Enable FHIR API
**Administration → Config → Connectors**
- ☑ Enable OpenEMR Standard FHIR REST API

### 2. FHIR Base URL
Replace `default` with your multi-site tenant if applicable see ([Multi-Tenancy Guide](Documentation/api/DEVELOPER_GUIDE.md#multisite-support)):
```
https://localhost:9300/apis/default/fhir
```

### 3. Get Capability Statement
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/metadata'
```
*No authentication required for capability statement*

### 4. Authenticate & Access Data
```bash
# Get access token (see Authentication Guide)
# Then make FHIR requests:
curl -X GET 'https://localhost:9300/apis/default/fhir/Patient' \
  -H 'Authorization: Bearer YOUR_ACCESS_TOKEN' \
  -H 'Accept: application/fhir+json'
```

## 📖 Documentation

### FHIR-Specific Documentation
- **[📘 FHIR API Complete Guide](Documentation/api/FHIR_API.md)** - Full FHIR R4 reference
- **[⚡ SMART on FHIR](Documentation/api/SMART_ON_FHIR.md)** - App integration guide
- **[🔐 Authentication](Documentation/api/AUTHENTICATION.md)** - OAuth2 for FHIR
- **[🔑 Scopes Reference](Documentation/api/AUTHORIZATION.md#fhir-api-scopes-apifhir)** - FHIR-specific scopes

### Complete API Documentation
- **[📚 Main API Documentation](Documentation/api/README.md)** - All APIs (FHIR, REST, Portal)

## ✨ FHIR Standards Compliance

| Standard | Version    | Status |
|----------|------------|--------|
| **FHIR** | R4 (4.0.1) | ✅ Full Support |
| **US Core** | 8.0        | ✅ Compliant |
| **SMART on FHIR** | v2.2.0     | ✅ Certified |
| **Bulk Data** | v1.0       | ✅ Implemented |
| **USCDI** | v1         | ✅ Supported |

## 🎯 Common FHIR Tasks

### Get Patient Demographics
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Patient/123' \
  -H 'Authorization: Bearer TOKEN'
```
**[→ Patient Resource Docs](Documentation/api/FHIR_API.md#patient)**

### Search for Observations
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Observation?patient=123&category=vital-signs' \
  -H 'Authorization: Bearer TOKEN'
```
**[→ Observation Resource Docs](Documentation/api/FHIR_API.md#observation)**

### Generate Clinical Summary (CCD)
```bash
curl -X POST 'https://localhost:9300/apis/default/fhir/DocumentReference/$docref' \
  -H 'Authorization: Bearer TOKEN' \
  -H 'Content-Type: application/fhir+json' \
  --data '{"resourceType":"Parameters","parameter":[{"name":"patient","valueId":"123"}]}'
```
**[→ $docref Documentation](Documentation/api/FHIR_API.md#documentreference-docref-operation)**

### Request Bulk Data Export
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Patient/$export' \
  -H 'Authorization: Bearer TOKEN' \
  -H 'Prefer: respond-async'
```
**[→ Bulk Export Guide](Documentation/api/FHIR_API.md#bulk-fhir-exports)**

### Register SMART App
```bash
curl -X POST 'https://localhost:9300/oauth2/default/registration' \
  -H 'Content-Type: application/json' \
  --data '{"client_name":"My SMART App","scope":"openid launch patient/Patient.rs"}'
```
**[→ SMART Registration](Documentation/api/SMART_ON_FHIR.md#app-registration)**

## 📦 Supported FHIR Resources

### Patient-Level Resources (30+)
AllergyIntolerance, Appointment, Binary, CarePlan, CareTeam, Condition, Coverage, Device, DiagnosticReport, DocumentReference, Encounter, Goal, Immunization, Location, Medication, MedicationRequest, **MedicationDispense** ✨, Observation, Organization, Patient, Person, Practitioner, PractitionerRole, Procedure, Provenance, **RelatedPerson** ✨, **ServiceRequest** ✨, **Specimen** ✨

### System-Level Resources
All patient resources plus: Group, Bulk Export operations

### ✨ New in SMART v2.2.0
- **ServiceRequest** - Lab orders, imaging requests, referrals
- **Specimen** - Laboratory specimen tracking
- **MedicationDispense** - Pharmacy dispensing records
- **RelatedPerson** - Patient relationships and emergency contacts

**[→ Complete Resource List](Documentation/api/FHIR_API.md#supported-resources)**

## 🔍 FHIR Search Examples

### Search by Patient
```bash
# All medications for a patient
GET /fhir/MedicationRequest?patient=123

# Active problems
GET /fhir/Condition?patient=123&clinical-status=active

# Recent encounters
GET /fhir/Encounter?patient=123&date=ge2024-01-01
```

### Search by Category
```bash
# Vital signs only
GET /fhir/Observation?patient=123&category=vital-signs

# Lab results only
GET /fhir/Observation?patient=123&category=laboratory

# Problem list items
GET /fhir/Condition?patient=123&category=problem-list-item
```

### Search by Date Range
```bash
# Observations since January 2024
GET /fhir/Observation?patient=123&date=ge2024-01-01

# Encounters in Q1 2024
GET /fhir/Encounter?patient=123&date=ge2024-01-01&date=le2024-03-31
```

**[→ Search Parameter Guide](Documentation/api/FHIR_API.md#search-parameters)**

## 🔐 FHIR Authentication & Scopes

### Required Scopes Format
```
<context>/<Resource>.<permissions>

Examples:
patient/Patient.rs              # Patient: read + search
user/Observation.cruds          # User: full access
system/Patient.$export          # System: bulk export
```

### Permission Flags
- `c` - Create
- `r` - Read
- `u` - Update
- `d` - Delete
- `s` - Search

### Context Types
- `patient/` - Single patient's data
- `user/` - User's authorized data (multiple patients)
- `system/` - Unrestricted access (backend services)

**[→ Complete Scope Reference](Documentation/api/AUTHORIZATION.md#fhir-api-scopes-apifhir)**

### Granular Scopes (SMART v2.2.0) ✨

Filter resources by category:
```bash
# Vital signs only
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs

# Health concerns only
patient/Condition.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern

# Clinical notes only
patient/DocumentReference.rs?category=http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category|clinical-note
```

**[→ Granular Scopes Guide](Documentation/api/AUTHORIZATION.md#granular-scopes)**

## 🔄 Bulk FHIR Exports

Export large datasets for analytics and population health:

### System Export
```bash
# Export all data
GET /fhir/$export
```

### Patient Export
```bash
# Export all patient compartment data
GET /fhir/Patient/$export
```

### Group Export
```bash
# Export data for specific patient group
GET /fhir/Group/1/$export
```

### Required Scopes
```
system/Patient.$export          # For patient export
system/*.$bulkdata-status       # Check export status
system/Binary.read              # Download files
```

### Workflow
1. Initiate export → Receive `Content-Location` URL
2. Poll status → Wait for completion
3. Download NDJSON files → Process data

**[→ Complete Bulk Export Guide](Documentation/api/FHIR_API.md#bulk-fhir-exports)**

## 📄 DocumentReference $docref (CCD Generation)

Generate Continuity of Care Documents on demand:

### Generate CCD
```bash
POST /fhir/DocumentReference/$docref
```

**Parameters:**
- `patient` - Patient ID (required)
- `start` - Start date for filtering
- `end` - End date for filtering

### Example Request
```json
{
  "resourceType": "Parameters",
  "parameter": [
    {"name": "patient", "valueId": "123"},
    {"name": "start", "valueDate": "2024-01-01"},
    {"name": "end", "valueDate": "2024-12-31"}
  ]
}
```

### Response
Returns DocumentReference with link to download CCD XML file.

**[→ Complete $docref Guide](Documentation/api/FHIR_API.md#documentreference-docref-operation)**

**[→ CCD Tutorial with Screenshots](https://github.com/openemr/openemr/issues/5284#issuecomment-1155678620)**

## ⚡ SMART on FHIR Integration

### Launch Types

**EHR Launch** - App launched from within OpenEMR:
```
1. User clicks app in OpenEMR
2. App receives launch token
3. App requests authorization with launch token
4. App receives patient + encounter context
```

**Standalone Launch** - App launched independently:
```
1. User opens app
2. App requests authorization
3. User logs in and approves
4. App receives access token
```

**[→ SMART Launch Guide](Documentation/api/SMART_ON_FHIR.md#launch-flows)**

### Launch Contexts (SMART v2.2.0) ✨

**Patient Context:**
```json
{
  "patient": "123"
}
```

**Encounter Context:** ✨ NEW
```json
{
  "patient": "123",
  "encounter": "456"
}
```

**User Context:**
```json
{
  "fhirUser": "Practitioner/789"
}
```

**[→ Launch Context Guide](Documentation/api/SMART_ON_FHIR.md#launch-contexts)**

## 🧪 Testing Your FHIR Integration

### Swagger UI
Interactive API testing:
```
https://your-openemr-install/swagger/
```

### Online Demos
Test against live OpenEMR instances:
- **Demo Portal:** https://www.open-emr.org/wiki/index.php/Development_Demo
- **Click:** "API (Swagger) User Interface"

### Capability Statement
Discover server capabilities:
```bash
curl https://localhost:9300/apis/default/fhir/metadata
```

### SMART Configuration
Discover SMART capabilities:
```bash
curl https://localhost:9300/apis/default/fhir/.well-known/smart-configuration
```

## 🆘 Support & Resources

### Documentation
- **[FHIR API Docs](Documentation/api/FHIR_API.md)** - Complete FHIR reference
- **[SMART on FHIR Docs](Documentation/api/SMART_ON_FHIR.md)** - App integration
- **[Troubleshooting](Documentation/api/SMART_ON_FHIR.md#troubleshooting)** - Common issues

### Community
- **[OpenEMR Forum](https://community.open-emr.org/)** - Ask questions
- **[GitHub Issues](https://github.com/openemr/openemr/issues)** - Report bugs

### Standards
- **[FHIR R4 Spec](https://hl7.org/fhir/R4/)** - Official specification
- **[US Core 8.0 IG](https://hl7.org/fhir/us/core/STU8/)** - US Core guide
- **[SMART Spec](http://hl7.org/fhir/smart-app-launch/)** - SMART on FHIR

## 📊 FHIR API Coverage

### Clinical Data
✅ Patients, Practitioners, Organizations
✅ Observations, Vital Signs, Lab Results
✅ Conditions, Problems, Diagnoses
✅ Medications, Prescriptions, Dispensing
✅ Procedures, Immunizations, Allergies

### Care Coordination
✅ Encounters, Appointments
✅ Care Plans, Care Teams, Goals
✅ Service Requests, Specimens
✅ Documents, CCD Generation

### Administrative
✅ Coverage, Related Persons
✅ Locations, Devices
✅ Provenance, Audit

**[→ Resource Details](Documentation/api/FHIR_API.md#supported-resources)**

## 🔒 Security & Compliance

### Security Features
✅ OAuth 2.0 / OpenID Connect
✅ Granular scope-based access control
✅ PKCE for public applications
✅ Token introspection
✅ Asymmetric client authentication (JWKS)
✅ TLS/HTTPS required

### Compliance
✅ HIPAA - Protected health information
✅ ONC Cures - Information blocking prevention
✅ US Core 8.0 - US healthcare requirements
✅ FHIR R4 - HL7 standard compliance

**[→ Security Guide](Documentation/api/DEVELOPER_GUIDE.md#security)**

## 🎓 Tutorials & Examples

### For Developers
1. **[Register a SMART App](Documentation/api/SMART_ON_FHIR.md#app-registration)**
2. **[Implement EHR Launch](Documentation/api/SMART_ON_FHIR.md#ehr-launch)**
3. **[Handle Launch Context](Documentation/api/SMART_ON_FHIR.md#launch-contexts)**
4. **[Use Granular Scopes](Documentation/api/AUTHORIZATION.md#granular-scopes)**

### For System Integrators
1. **[Bulk Data Export](Documentation/api/FHIR_API.md#bulk-fhir-exports)**
2. **[Client Credentials Auth](Documentation/api/AUTHENTICATION.md#client-credentials-grant)**
3. **[Generate CCDs](Documentation/api/FHIR_API.md#documentreference-docref-operation)**

### Code Examples
**[→ JavaScript Examples](Documentation/api/FHIR_API.md#examples)**
**[→ Python Examples](Documentation/api/FHIR_API.md#examples)**
**[→ cURL Examples](Documentation/api/FHIR_API.md#examples)**

## 🔗 Quick Reference

| Topic | Link |
|-------|------|
| **FHIR Resources** | [FHIR_API.md#supported-resources](Documentation/api/FHIR_API.md#supported-resources) |
| **Search Parameters** | [FHIR_API.md#search-parameters](Documentation/api/FHIR_API.md#search-parameters) |
| **FHIR Scopes** | [AUTHORIZATION.md#fhir-api-scopes](Documentation/api/AUTHORIZATION.md#fhir-api-scopes-apifhir) |
| **Authentication** | [AUTHENTICATION.md](Documentation/api/AUTHENTICATION.md) |
| **SMART Apps** | [SMART_ON_FHIR.md](Documentation/api/SMART_ON_FHIR.md) |
| **Bulk Export** | [FHIR_API.md#bulk-fhir-exports](Documentation/api/FHIR_API.md#bulk-fhir-exports) |
| **CCD Generation** | [FHIR_API.md#documentreference-docref-operation](Documentation/api/FHIR_API.md#documentreference-docref-operation) |

---

**FHIR Version:** R4 (4.0.1)
**SMART Version:** v2.2.0
**US Core:** 8.0

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
