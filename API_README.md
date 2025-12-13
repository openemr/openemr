# OpenEMR REST API Documentation

> **📚 Complete documentation has moved to [Documentation/api/](Documentation/api/README.md)**

This project provides comprehensive REST and FHIR APIs for OpenEMR, supporting:
- **FHIR R4** - Full FHIR Release 4 implementation
- **US Core 8.0** - US healthcare compliance
- **SMART on FHIR v2.2.0** - Advanced app integration
- **OAuth 2.0 / OpenID Connect** - Secure authentication
- **Bulk Data Export** - Population health analytics

## 🚀 Quick Start

### 1. Enable the API
**Administration → Config → Connectors**
- ☑ Enable OpenEMR Standard REST API
- ☑ Enable OpenEMR Standard FHIR REST API

### 2. Configure SSL
Set your base URL at:
**Administration → Config → Connectors → Site Address (required for OAuth2 and FHIR)**

### 3. Register Your Application
```bash
curl -X POST https://localhost:9300/oauth2/default/registration \
  -H 'Content-Type: application/json' \
  --data '{
    "client_name": "My App",
    "redirect_uris": ["https://myapp.example.com/callback"],
    "scope": "openid api:fhir patient/Patient.rs patient/Observation.rs"
  }'
```

### 4. Make Your First Request
```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Patient' \
  -H 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

## 📖 Documentation

### Core Documentation
- **[📘 Complete API Documentation](Documentation/api/README.md)** - Start here for overview
- **[🔐 Authentication Guide](Documentation/api/AUTHENTICATION.md)** - OAuth2, tokens, and client registration
- **[🔑 Authorization & Scopes](Documentation/api/AUTHORIZATION.md)** - Permissions and access control
- **[🏥 FHIR API Reference](Documentation/api/FHIR_API.md)** - FHIR R4 endpoints and resources
- **[⚡ SMART on FHIR](Documentation/api/SMART_ON_FHIR.md)** - App integration and launch flows
- **[🛠️ Standard API Reference](Documentation/api/STANDARD_API.md)** - OpenEMR REST endpoints
- **[👨‍💻 Developer Guide](Documentation/api/DEVELOPER_GUIDE.md)** - Internal usage and development

## 🎯 Common Tasks

### Authenticate Your Application
→ [Authorization Code Grant](Documentation/api/AUTHENTICATION.md#authorization-code-grant)

### Understand Scopes
→ [Scopes Reference](Documentation/api/AUTHORIZATION.md#scopes)

### Access Patient Data
→ [FHIR Patient Resource](Documentation/api/FHIR_API.md#patient-resources)

### Integrate a SMART App
→ [SMART Registration](Documentation/api/SMART_ON_FHIR.md#app-registration)

### Launch Apps from EHR
→ [EHR Launch Flow](Documentation/api/SMART_ON_FHIR.md#ehr-launch)

### Export Bulk Data
→ [Bulk FHIR Exports](Documentation/api/FHIR_API.md#bulk-fhir-exports)

### Generate Care Documents (CCD)
→ [DocumentReference $docref](Documentation/api/FHIR_API.md#documentreference-docref-operation)

## ✨ What's New in SMART v2.2.0

### Enhanced Security & Permissions
- ✨ **[Granular Scopes](Documentation/api/AUTHORIZATION.md#granular-scopes)** - Fine-grained permissions (`.cruds` syntax)
- ✨ **[POST-Based Authorization](Documentation/api/AUTHENTICATION.md#post-based-authorization)** - More secure auth requests
- ✨ **[Asymmetric Authentication](Documentation/api/AUTHENTICATION.md#asymmetric-client-authentication)** - JWKS support
- ✨ **[Token Introspection](Documentation/api/AUTHENTICATION.md#token-introspection)** - Validate token status

### Enhanced Context & Discovery
- ✨ **[EHR Launch with Encounter Context](Documentation/api/SMART_ON_FHIR.md#encounter-context)** - Context-aware apps
- ✨ **[SMART Configuration Endpoint](Documentation/api/SMART_ON_FHIR.md#smart-configuration)** - Dynamic capability discovery

### New FHIR Resources
- ✨ **[ServiceRequest](Documentation/api/FHIR_API.md#servicerequest-)** - Lab orders, imaging requests, referrals
- ✨ **[Specimen](Documentation/api/FHIR_API.md#specimen-)** - Laboratory specimen tracking
- ✨ **[MedicationDispense](Documentation/api/FHIR_API.md#medicationdispense-)** - Pharmacy dispensing records
- ✨ **[RelatedPerson](Documentation/api/FHIR_API.md#relatedperson-)** - Patient relationships and contacts

See complete resource list in [FHIR API Documentation](Documentation/api/FHIR_API.md#supported-resources)

## 📚 API Endpoints

### FHIR API (FHIR R4)
```
https://localhost:9300/apis/default/fhir
```
**[→ Full FHIR Documentation](Documentation/api/FHIR_API.md)**

**Key Endpoints:**
- `GET /fhir/metadata` - Capability statement (no auth required)
- `GET /fhir/Patient` - Patient search
- `GET /fhir/Observation?patient=123` - Patient observations
- `POST /fhir/DocumentReference/$docref` - Generate CCD
- `GET /fhir/$export` - Bulk data export

### Standard API (OpenEMR REST)
```
https://localhost:9300/apis/default/api
```
**[→ Full Standard API Documentation](Documentation/api/STANDARD_API.md)**

**Key Endpoints:**
- `GET /api/patient` - List patients
- `GET /api/patient/123` - Get patient details
- `GET /api/patient/123/encounter` - Patient encounters
- `POST /api/patient` - Create patient

### Patient Portal API (Experimental)
```
https://localhost:9300/apis/default/portal
```
**[→ Portal API Documentation](Documentation/api/STANDARD_API.md#patient-portal-api)**

## 🔒 Security & Compliance

### Required Security Measures
- ✅ **HTTPS/TLS Required** - All API communication must be encrypted
- ✅ **OAuth 2.0** - Industry-standard authorization
- ✅ **Granular Scopes** - Principle of least privilege
- ✅ **PKCE for Public Apps** - Enhanced security for native/browser apps
- ✅ **Token Validation** - Introspection support

### Standards Compliance
- ✅ **HIPAA** - Protected health information safeguards
- ✅ **ONC Cures Update** - Information blocking compliance
- ✅ **FHIR R4** - HL7 FHIR Release 4
- ✅ **US Core 8.0** - US healthcare requirements
- ✅ **SMART v2.2.0** - App launch framework

**[→ Security Best Practices](Documentation/api/DEVELOPER_GUIDE.md#security)**

## 🧪 Testing & Development

### Interactive Testing
Test endpoints interactively with Swagger UI:
```
https://your-openemr-install/swagger/
```

### Online Demos
Try the API on live demo instances:
- **Demo URL:** https://www.open-emr.org/wiki/index.php/Development_Demo
- **Click:** "API (Swagger) User Interface" link

### Configure Swagger OAuth
When testing with Swagger, set your client's redirect URI to:
```
<OpenEMR base URI>/swagger/oauth2-redirect.html
```

## 🌐 Multisite Support

OpenEMR supports multiple sites with site-specific endpoints:

**Default site:**
```
https://localhost:9300/apis/default/fhir
https://localhost:9300/apis/default/api
```

**Alternate site:**
```
https://localhost:9300/apis/alternate/fhir
https://localhost:9300/apis/alternate/api
```

**[→ Multisite Documentation](Documentation/api/DEVELOPER_GUIDE.md#multisite-support)**

## 📋 Scope Examples

### Patient-Facing App (Vital Signs Tracker)
```
openid
offline_access
patient/Patient.rs
patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs
```

### Provider App (Clinical Documentation)
```
openid
fhirUser
launch
launch/patient
launch/encounter
user/Patient.rs
user/Encounter.cruds
user/Observation.crs
user/DocumentReference.crs
```

### Backend Service (Analytics)
```
system/Patient.$export
system/*.$bulkdata-status
system/Binary.read
```

**[→ Complete Scope Reference](Documentation/api/AUTHORIZATION.md#fhir-api-scopes-apifhir)**

## 🆘 Support & Resources

### Documentation
- **[Complete API Docs](Documentation/api/README.md)** - All documentation
- **[Quick Start Guide](Documentation/api/README.md#quick-start)** - Get started fast
- **[FAQ & Troubleshooting](Documentation/api/SMART_ON_FHIR.md#troubleshooting)** - Common issues

### Community
- **[Community Forum](https://community.open-emr.org/)** - Ask questions, share knowledge
- **[Development Thread](https://community.open-emr.org/t/v6-authorization-and-api-changes-afoot/15450)** - API development discussion
- **[GitHub Issues](https://github.com/openemr/openemr/issues)** - Report bugs, request features

### Standards & Specifications
- **[FHIR R4 Spec](https://hl7.org/fhir/R4/)** - HL7 FHIR specification
- **[US Core 8.0 IG](https://hl7.org/fhir/us/core/STU8/)** - US Core Implementation Guide
- **[SMART App Launch](http://hl7.org/fhir/smart-app-launch/)** - SMART on FHIR specification
- **[OAuth 2.0](https://oauth.net/2/)** - OAuth 2.0 framework

## 🔄 Migration from Previous Versions

### V1 to V2 Scope Migration

**V1 Scopes (Deprecated but supported):**
```
patient/Patient.read
patient/Observation.read
```

**V2 Scopes (Recommended):**
```
patient/Patient.rs
patient/Observation.rs
```

**Mapping:**
- `.read` → `.rs` (read + search)
- `.write` → `.cud` (create + update + delete)

**[→ V1 Compatibility Guide](Documentation/api/AUTHORIZATION.md#v1-scope-compatibility)**

## 📝 Example Code

### JavaScript/Node.js
```javascript
// Fetch patient data
const response = await fetch('https://localhost:9300/apis/default/fhir/Patient/123', {
  headers: {
    'Authorization': `Bearer ${accessToken}`,
    'Accept': 'application/fhir+json'
  }
});

const patient = await response.json();
console.log(`Patient: ${patient.name[0].given[0]} ${patient.name[0].family}`);
```

### Python
```python
import requests

# Fetch observations
response = requests.get(
    'https://localhost:9300/apis/default/fhir/Observation',
    headers={
        'Authorization': f'Bearer {access_token}',
        'Accept': 'application/fhir+json'
    },
    params={'patient': '123', 'category': 'vital-signs'}
)

observations = response.json()
```

### cURL
```bash
# Get patient medications
curl -X GET 'https://localhost:9300/apis/default/fhir/MedicationRequest?patient=123' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Accept: application/fhir+json'
```

**[→ More Examples](Documentation/api/FHIR_API.md#examples)**

## 🏗️ For Developers

### Internal API Usage
- **[Internal API Guide](Documentation/api/DEVELOPER_GUIDE.md#internal-api-usage)** - Using APIs from within OpenEMR
- **[Example Code](tests/api/InternalApiTest.php)** - Internal API examples

### Extending the API
- **[Adding Endpoints](Documentation/api/DEVELOPER_GUIDE.md#adding-endpoints)** - Create new API endpoints
- **[Controllers](Documentation/api/DEVELOPER_GUIDE.md#controllers)** - Controller architecture
- **[Services](Documentation/api/DEVELOPER_GUIDE.md#services)** - Business logic layer
- **[Routing](Documentation/api/DEVELOPER_GUIDE.md#routing)** - Route definitions

### Architecture
```
Request → Authentication → Authorization → Controller → Service → Database
                                              ↓
Response ← JSON Formatting ← Validation ← Processing
```

**[→ Developer Guide](Documentation/api/DEVELOPER_GUIDE.md)**

## 📊 API Coverage

### FHIR Resources (30+)
✅ Patient, Practitioner, Organization, Location
✅ Observation, Condition, Procedure, AllergyIntolerance
✅ MedicationRequest, MedicationDispense, Immunization
✅ Encounter, Appointment, CarePlan, CareTeam
✅ DiagnosticReport, ServiceRequest, Specimen
✅ DocumentReference, Binary, Provenance
✅ Goal, Device, Coverage, RelatedPerson

**[→ Complete Resource List](Documentation/api/FHIR_API.md#supported-resources)**

### Operations
✅ Read, Search, Create, Update, Delete (per resource)
✅ Bulk Export ($export)
✅ CCD Generation ($docref)
✅ Token Introspection
✅ Capability Statement

## 🎓 Tutorials

### Getting Started
1. **[Register Your First App](Documentation/api/AUTHENTICATION.md#client-registration)**
2. **[Obtain an Access Token](Documentation/api/AUTHENTICATION.md#authorization-code-grant)**
3. **[Make Your First API Call](Documentation/api/FHIR_API.md#examples)**
4. **[Handle Token Refresh](Documentation/api/AUTHENTICATION.md#refresh-token-grant)**

### Advanced Topics
1. **[Implement EHR Launch](Documentation/api/SMART_ON_FHIR.md#ehr-launch)**
2. **[Use Granular Scopes](Documentation/api/AUTHORIZATION.md#granular-scopes)**
3. **[Export Bulk Data](Documentation/api/FHIR_API.md#bulk-fhir-exports)**
4. **[Generate Clinical Documents](Documentation/api/FHIR_API.md#documentreference-docref-operation)**

## 📜 License

OpenEMR is licensed under [GPL v3](https://www.gnu.org/licenses/gpl-3.0.en.html).

API integrations must comply with:
- HIPAA requirements
- State/federal healthcare regulations
- OpenEMR license terms

## 🔗 Quick Links

| Topic | Documentation |
|-------|---------------|
| Authentication | [AUTHENTICATION.md](Documentation/api/AUTHENTICATION.md) |
| Scopes & Permissions | [AUTHORIZATION.md](Documentation/api/AUTHORIZATION.md) |
| FHIR Endpoints | [FHIR_API.md](Documentation/api/FHIR_API.md) |
| SMART Apps | [SMART_ON_FHIR.md](Documentation/api/SMART_ON_FHIR.md) |
| Standard API | [STANDARD_API.md](Documentation/api/STANDARD_API.md) |
| Development | [DEVELOPER_GUIDE.md](Documentation/api/DEVELOPER_GUIDE.md) |

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

