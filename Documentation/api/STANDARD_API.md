# Standard API (OpenEMR REST)

Complete guide to OpenEMR's Standard REST API for native OpenEMR resources.

## Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Base URL](#base-url)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
- [Patient Portal API](#patient-portal-api)
- [Request/Response Format](#requestresponse-format)
- [Error Handling](#error-handling)
- [Validation](#validation)
- [Examples](#examples)
- [Swagger Documentation](#swagger-documentation)

## Overview

The **OpenEMR Standard REST API** provides access to OpenEMR's native data structures through RESTful endpoints. This API is designed for:

- ✅ **OpenEMR-specific operations** - Native data structures
- ✅ **Custom integrations** - Direct access to OpenEMR tables
- ✅ **Administrative functions** - User management, facilities, etc.
- ✅ **Legacy system integration** - Non-FHIR compatible systems

### When to Use Standard API vs FHIR API

| Use Standard API When | Use FHIR API When |
|----------------------|-------------------|
| Need OpenEMR-specific data structures | Need healthcare interoperability |
| Integrating with existing OpenEMR workflows | Building standards-compliant apps |
| Require direct table access | Need vendor-neutral data model |
| Administrative/operational tasks | Clinical data exchange |
| Legacy system integration | SMART on FHIR apps required |

**Recommendation:** For new healthcare applications, use the [FHIR API](FHIR_API.md) for better interoperability.

## Prerequisites

### 1. Enable Standard API

Navigate to: **Administration → Config → Connectors**

Enable: **☑ Enable OpenEMR Standard REST API**

### 2. Configure SSL/TLS

**Required:** All API endpoints require HTTPS/TLS.

Set base URL: **Administration → Config → Connectors → Site Address (required for OAuth2 and FHIR)**

### 3. Register API Client

See [Authentication Guide](AUTHENTICATION.md#client-registration) for OAuth2 client registration.

**Required scopes:** At minimum `openid` + `api:oemr` + resource-specific scopes.

Example:
```
openid api:oemr user/patient.rs user/encounter.rs
```

See [Authorization Guide](AUTHORIZATION.md#standard-api-scopes-apioemr) for complete scope listing.

## Base URL

Standard API endpoints use the following base URL pattern:
```
https://{your-openemr-host}/apis/{site}/api
```

### Default Site
```
https://localhost:9300/apis/default/api
```

### Multisite Example
```
https://localhost:9300/apis/alternate/api
```

### Endpoint Structure
```
{base}/[resource]
{base}/[resource]/[id]
{base}/[resource]/[id]/[sub-resource]
```

**Examples:**
```
GET https://localhost:9300/apis/default/api/patient
GET https://localhost:9300/apis/default/api/patient/123
GET https://localhost:9300/apis/default/api/patient/123/encounter
```

## Authentication

All Standard API requests require authentication via **Bearer token**.

### Request Format
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient' \
  -H 'Authorization: Bearer YOUR_ACCESS_TOKEN' \
  -H 'Accept: application/json'
```

### Obtaining Access Token

See [Authentication Guide](AUTHENTICATION.md) for OAuth2 flows:
- [Authorization Code Grant](AUTHENTICATION.md#authorization-code-grant) (recommended)
- [Password Grant](AUTHENTICATION.md#password-grant) (not recommended)
- [Refresh Token Grant](AUTHENTICATION.md#refresh-token-grant)

### Token in Request Header
```http
GET /apis/default/api/patient HTTP/1.1
Host: localhost:9300
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
Accept: application/json
```

## API Endpoints

### Standard API ([swagger](https://demo.openemr.io/openemr/swagger/#/standard))
| Resource                         | permissions | Description                                                                                            |
|----------------------------------|-------------|--------------------------------------------------------------------------------------------------------|
| Facility                         | crus        | Manage facility/service location information (`facility` table).                                       |
| Patient                          | crus        | Manage patient demographics and registration (`patient_data` table).                                   |
| Encounter                        | crus        | Manage patient encounters and visits. (`form_encounter` table).                                        |
| Soap Note                        | crus        | Manage Encounter Form SOAP Notes (`form_soap` table).                                                  |
| Vitals                           | crus        | Manage Patient Vitals (`form_vitals`,`form_vital_details` table).                                      |
| Practitioner                     | rus         | Manage practitioner information (`users` table).                                                       |
| Medical Problem                  | cruds       | Manage patient medical problems and conditions (`lists` table).                                        |
| Allergies                        | cruds       | Manage patient allergies (`lists` table).                                                              |
| Medications                      | cruds       | Manage patient medications (`lists` table).                                                            |
| Surgery Issues                   | cruds       | Manage patient surgical issues (`lists` table).                                                        |
| Dental Issues                    | cruds       | Manage patient dental issues (`lists` table).                                                          |
| Appointments                     | crus        | Manage patient appointments (`pc_event` table).                                                        |
| Lists                            | rs          | Read-only access to lists (`lists` table).                                                             |
| Users                            | rs          | Read-only access to users (`users` table).                                                             |
| Insurance Company                | crus        | Manage insurance companies (`insurance_data` table).                                                   |
| Patient Documents                | crs         | Manage patient documents (`documents` table).                                                          |
| Patient Employers                | rs          | Read-only access to patient employers (`employers` table).                                             |
| Patient Insurance                | crus        | Manage patient insurance information (`patient_insurance` table).                                      |
| Patient Messages                 | cud         | Create patient messages (`patient_messages` table).                                                    |
| Patient Referrals (transactions) | cruds       | Manage patient referrals (`transactions` and `lbt_data` tables). See the note below.                    |
|Patient Immunizations| rs| Read-only access to patient immunizations (`immunizations` table).                                     |
|Patient Procedures| rs| Read-only access to patient procedures (`procedure_order`,`procedure_report`,`procedure_result` table). |
|Drugs| rs| Read-only access to drugs (`drugs` table).                                                               |
|Prescriptions| rs| Read-only access to prescriptions (`prescriptions` table).                                               |

| Permissions | Description                          |
|-------------|--------------------------------------|
| c           | Create resource                      |
| r           | Read resource                        |
| u           | Update resource                      |
| d           | Delete resource                      |
| s           | Search/List resources                |

**Patient referrals.** A referral saved with `POST /api/patient/:pid/transaction` (type `LBTref`) or
`PUT /api/transaction/:tid` is the same record as one saved on the Transactions screen:

- Refer By and Refer To are found by NPI (`referByNpi`, `referToNpi`). An NPI that matches no
  user is not an error: on `PUT` the recipient is left as it was and the call still succeeds, so
  the old recipient keeps the referral and its Task. Read the referral back to check who it is
  sent to.
- With the **Track Referrals with FHIR Tasks** global on, the referral gets a FHIR Task for its
  recipient, and is readable as a FHIR ServiceRequest. No Task is made when FHIR cannot serve
  Refer To as a Practitioner or an Organization, such as a practitioner without an NPI, or when
  the referral already has a Reply Date. A `PUT` of an answered referral therefore creates none.
  See [when a Task is made](../REFERRAL_WORKFLOWS.md#setup).
- The save succeeds even if making the Task fails; the failure is only logged. Checking needs the
  FHIR API, with `ServiceRequest` and `Task` read scopes, because this API returns the
  transaction id and not the referral's FHIR id:
    1. `GET /fhir/ServiceRequest?patient=<patient-uuid>&category=3457005` and find the referral;
       its `id` is the uuid.
    2. `GET /fhir/Task?focus=ServiceRequest/<uuid>`.
    3. Keep the referral's own Tasks for the intended recipient: `code` is `fulfill`, there is
       no `partOf`, and `owner` is that recipient. A recipient's sub-Task, or a Task for an
       earlier recipient, does not count.
    4. Drop any Task OpenEMR set aside: `status` is `cancelled` or `entered-in-error` and
       `statusReason.text` is exactly one of
         - `The referral was sent to another recipient.`
         - `The referral no longer names a recipient.`
         - `The referral was deleted.`
         - `The referral's reply was cleared.`
    5. Of what is left, use the most recent Task only (the latest `authoredOn`). After a
       Re-send, an older rejected Task and the new requested Task both remain, and the new one
       is the referral's status. OpenEMR itself takes the Task it created last, which FHIR does
       not expose: if two Tasks that count share the same `authoredOn`, or one was created
       through the API with its own `authoredOn`, check the referral's status on the
       Transactions screen instead.
    6. If that Task is one the recipient itself cancelled, rejected or failed, it still counts,
       and saving the referral again will not replace it: staff use **Re-send** on the referral
       screen.

  If there is no Task that counts, save the referral again. A client without FHIR access cannot check; staff can
  see the referral's status on the Transactions screen.
- The API does not write the Reply Date, so it cannot close a referral.
- `authorization`, `visits`, `validFrom` and `validThrough` are stored, but the stock referral
  form has no fields for them.
- `PUT` only changes a field the referral already has a stored row for. A referral created
  through this API has a row for each of these four, even when it was sent empty. A referral made
  on the stock form has none, so a `PUT` cannot add them to it.
- `PUT` skips empty values, `"0"` included, so it cannot clear a field: sending an empty
  `authorization` or `visits` of `"0"` leaves the old value in place.

See [Referral Workflows](../REFERRAL_WORKFLOWS.md).

## Patient Portal API ([swagger](https://demo.openemr.io/openemr/swagger/#/standard-patient))

**EXPERIMENTAL** - Patient-facing API endpoints.

### Enable Portal API

**Administration → Config → Connectors**
- ☑ Enable OpenEMR Patient Portal REST API (EXPERIMENTAL)

### Base URL
```
https://localhost:9300/apis/default/portal
```

| Resource | permissions | Description                                    |
|----------|-------------|------------------------------------------------|
| Patient  | r           | Get logged in patient's demographics and info.             |
| Encounter| rs          | Get logged in patient's encounters and visits. |
| Appointment| rs          | Get logged in patient's appointments.          |


| Permissions | Description                          |
|-------------|--------------------------------------|
| c           | Create resource                      |
| r           | Read resource                        |
| u           | Update resource                      |
| d           | Delete resource                      |
| s           | Search/List resources                |


### Authentication

Patients must have API credentials generated by their clinician:
1. Navigate to patient demographics
2. Click **API Credentials** button
3. Generate credentials for patient

See [Authentication Guide](AUTHENTICATION.md) for OAuth2 flows:
- [Authorization Code Grant](AUTHENTICATION.md#authorization-code-grant) (recommended)
- [Password Grant](AUTHENTICATION.md#password-grant) (not recommended)
- [Refresh Token Grant](AUTHENTICATION.md#refresh-token-grant)
-
Patients authenticate using [Password Grant](AUTHENTICATION.md#password-grant) with `user_role=patient`.

### Limitations

⚠️ **Portal API is experimental:**
- Limited endpoint coverage
- Subject to changes
- Use FHIR API with `patient/*` scopes instead

## Request/Response Format

### Standard Response Format

All Standard API responses use a consistent format:
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": <result>
}
```

**Fields:**
- `validationErrors` - Array of validation errors (client-side)
- `internalErrors` - Array of server errors
- `data` - Response payload (object for single result, array for multiple)

### Success Response

**Single Resource:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": {
    "id": "1",
    "uuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
    "fname": "John",
    "lname": "Smith"
  }
}
```

**Multiple Resources:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {"id": "1", "fname": "John", "lname": "Smith"},
    {"id": "2", "fname": "Jane", "lname": "Doe"}
  ]
}
```

### Error Response

**Validation Error:**
```json
{
  "validationErrors": [
    "The fname field is required.",
    "The DOB field must be a valid date."
  ],
  "internalErrors": [],
  "data": []
}
```

**Internal Error:**
```json
{
  "validationErrors": [],
  "internalErrors": [
    "Database connection failed"
  ],
  "data": []
}
```

## Error Handling

### HTTP Status Codes

| Code | Meaning | Common Causes |
|------|---------|---------------|
| 200 | OK | Successful GET/PUT/PATCH request |
| 201 | Created | Successful POST request |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Insufficient scopes |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation error |
| 500 | Internal Server Error | Server error |

### Common Errors

**401 Unauthorized**
```json
{
  "error": "invalid_token",
  "error_description": "The access token is missing"
}
```

**Solution:** Include `Authorization: Bearer TOKEN` header

**403 Forbidden**
```json
{
  "validationErrors": [],
  "internalErrors": ["Insufficient scope for requested resource"],
  "data": []
}
```

**Solution:** Request appropriate scopes during authorization

**404 Not Found**
```json
{
  "validationErrors": [],
  "internalErrors": ["Resource not found"],
  "data": []
}
```

**Solution:** Verify resource UUID exists

**422 Validation Error**
```json
{
  "validationErrors": [
    "The fname field is required.",
    "The DOB must be a date in the format Y-m-d."
  ],
  "internalErrors": [],
  "data": []
}
```

**Solution:** Fix request data validation errors

## Validation

### UUID Format

All UUIDs must be valid UUID v4 format:
```
90cde167-7b9b-4ed1-bd55-533925cb2605
```

### Date Format

All dates use ISO 8601 format:
```
YYYY-MM-DD
```

Example: `2024-01-15`

## Examples

### Example 1: Create Patient
```bash
curl -X POST 'https://localhost:9300/apis/default/api/patient' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...' \
  -H 'Content-Type: application/json' \
  --data '{
    "title": "Mr",
    "fname": "John",
    "lname": "Smith",
    "DOB": "1980-01-15",
    "sex": "Male",
    "street": "123 Main St",
    "city": "Boston",
    "state": "MA",
    "postal_code": "12345",
    "phone_home": "555-1234",
    "email": "john.smith@example.com"
  }'
```

### Example 2: Search Patients
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient?lname=Smith&city=Boston' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...'
```

### Example 3: Get Patient Encounters
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/encounter' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...'
```

### Example 4: Create Appointment
```bash
curl -X POST 'https://localhost:9300/apis/default/api/appointment' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...' \
  -H 'Content-Type: application/json' \
  --data '{
    "pc_catid": "5",
    "pc_title": "Annual Physical",
    "pc_duration": "1800",
    "pc_eventDate": "2024-02-15",
    "pc_startTime": "09:00:00",
    "pc_facility": "1",
    "pid": "1"
  }'
```

### Example 5: Upload Document
```bash
curl -X POST 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/document' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...' \
  -F 'file=@/path/to/lab-results.pdf' \
  -F 'path=Lab Reports' \
  -F 'date=2024-01-15'
```

### Example 6: Add Allergy
```bash
curl -X POST 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/allergy' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...' \
  -H 'Content-Type: application/json' \
  --data '{
    "title": "Penicillin",
    "begdate": "2020-01-15",
    "diagnosis": "Allergy to penicillin",
    "severity_al": "severe",
    "reaction": "Hives"
  }'
```

### Example 7: Record Vitals
```bash
curl -X POST 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/vital' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1Qi...' \
  -H 'Content-Type: application/json' \
  --data '{
    "date": "2024-01-15",
    "bps": "120",
    "bpd": "80",
    "weight": "180",
    "height": "70",
    "temperature": "98.6",
    "pulse": "72",
    "respiration": "16"
  }'
```

## Swagger Documentation

### Interactive Testing

Test Standard API endpoints interactively with Swagger UI:
```
https://your-openemr-install/swagger/
```

Navigate to the **standard** section in Swagger UI.

### Online Demos

Test against live demo instances:
- **Demo Portal:** https://www.open-emr.org/wiki/index.php/Development_Demo
- **Click:** "API (Swagger) User Interface" link

### Configure Swagger OAuth

Set your client's redirect URI to:
```
<OpenEMR base URI>/swagger/oauth2-redirect.html
```

Example:
```
https://localhost:9300/swagger/oauth2-redirect.html
```

---

**Next Steps:**
- Review [Authentication](AUTHENTICATION.md) for OAuth2 setup
- See [Authorization](AUTHORIZATION.md) for scope details
- Learn about [FHIR API](FHIR_API.md) for standards-based integration
- Check [Developer Guide](DEVELOPER_GUIDE.md) for advanced topics

**Support:**
- Community Forum: https://community.open-emr.org/
- GitHub Issues: https://github.com/openemr/openemr/issues
- API Development Thread: https://community.open-emr.org/t/v6-authorization-and-api-changes-afoot/15450

**Recommendation:**
For new integrations, consider using the [FHIR API](FHIR_API.md) for better interoperability and standards compliance.

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
