# Standard API (OpenEMR REST)

Complete guide to OpenEMR's Standard REST API for native OpenEMR resources.

## Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Base URL](#base-url)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
    - [Patient](#patient)
    - [Encounter](#encounter)
    - [Appointment](#appointment)
    - [Allergy](#allergy)
    - [Medical Problem](#medical-problem)
    - [Medication](#medication)
    - [Prescription](#prescription)
    - [Procedure](#procedure)
    - [Vital Signs](#vital-signs)
    - [Document](#document)
    - [Insurance](#insurance)
    - [Practitioner](#practitioner)
    - [Facility](#facility)
    - [Other Endpoints](#other-endpoints)
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
openid api:oemr user/patient.read user/encounter.read
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

### Patient

Manage patient demographics and registration.

#### Scopes
```
user/patient.cruds    # Full access
user/patient.rs       # Read and search only
```

#### Endpoints

**List Patients**
```
GET /api/patient
```

**Search Parameters:**
- `fname` - First name
- `lname` - Last name
- `dob` - Date of birth (YYYY-MM-DD)
- `ss` - Social security number
- `street` - Street address
- `postal_code` - ZIP/postal code
- `city` - City
- `state` - State
- `phone_home` - Home phone
- `phone_biz` - Business phone
- `phone_cell` - Cell phone
- `email` - Email address

**Example:**
```bash
# Search by last name
curl -X GET 'https://localhost:9300/apis/default/api/patient?lname=Smith' \
  -H 'Authorization: Bearer TOKEN'
```

**Get Patient by ID**
```
GET /api/patient/:puuid
```

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": {
    "id": "1",
    "uuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
    "title": "Mr",
    "fname": "John",
    "mname": "Q",
    "lname": "Smith",
    "DOB": "1980-01-15",
    "sex": "Male",
    "ss": "123-45-6789",
    "street": "123 Main St",
    "postal_code": "12345",
    "city": "Boston",
    "state": "MA",
    "country_code": "US",
    "phone_home": "555-1234",
    "phone_cell": "555-5678",
    "email": "john.smith@example.com"
  }
}
```

**Create Patient**
```
POST /api/patient
```

**Request Body:**
```json
{
  "title": "Mr",
  "fname": "John",
  "mname": "Q",
  "lname": "Smith",
  "DOB": "1980-01-15",
  "sex": "Male",
  "street": "123 Main St",
  "postal_code": "12345",
  "city": "Boston",
  "state": "MA",
  "country_code": "US",
  "phone_home": "555-1234",
  "email": "john.smith@example.com"
}
```

**Update Patient**
```
PUT /api/patient/:puuid
```

**Partial Update Patient**
```
PATCH /api/patient/:puuid
```

### Encounter

Manage patient encounters and visits.

#### Scopes
```
user/encounter.cruds
user/encounter.rs
```

#### Endpoints

**List Patient Encounters**
```
GET /api/patient/:puuid/encounter
```

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/encounter' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": "1",
      "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "date": "2024-01-15",
      "reason": "Annual physical",
      "facility": "Main Clinic",
      "provider": "Dr. Jane Doe",
      "pc_catid": "5",
      "facility_id": "1",
      "billing_facility": "1"
    }
  ]
}
```

**Get Encounter by ID**
```
GET /api/patient/:puuid/encounter/:euuid
```

**Create Encounter**
```
POST /api/patient/:puuid/encounter
```

**Request Body:**
```json
{
  "date": "2024-01-15",
  "onset_date": "2024-01-14",
  "reason": "Annual physical",
  "facility": "Main Clinic",
  "pc_catid": "5",
  "facility_id": "1",
  "billing_facility": "1",
  "sensitivity": "normal",
  "referral_source": "",
  "pos_code": "11"
}
```

**Update Encounter**
```
PUT /api/patient/:puuid/encounter/:euuid
```

### Appointment

Manage patient appointments.

#### Scopes
```
user/appointment.cruds
user/appointment.rs
```

#### Endpoints

**List Appointments**
```
GET /api/appointment
```

**Search Parameters:**
- `pc_catid` - Appointment category ID
- `patient_id` - Patient ID

**Get Appointment by ID**
```
GET /api/appointment/:auuid
```

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/appointment/90c196f2-807b-4c85-afc5-d56e4f5c9f3b' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": {
    "pc_eid": "1",
    "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
    "pc_catid": "5",
    "pc_title": "Office Visit",
    "pc_duration": "900",
    "pc_hometext": "Annual checkup",
    "pc_apptstatus": "SCHEDULED",
    "pc_eventDate": "2024-02-15",
    "pc_startTime": "09:00:00",
    "pc_facility": "1",
    "pc_billing_location": "1",
    "pc_aid": "1"
  }
}
```

**Get Patient Appointments**
```
GET /api/patient/:puuid/appointment
```

**Create Appointment**
```
POST /api/appointment
```

**Request Body:**
```json
{
  "pc_catid": "5",
  "pc_title": "Office Visit",
  "pc_duration": "900",
  "pc_hometext": "Annual checkup",
  "pc_apptstatus": "SCHEDULED",
  "pc_eventDate": "2024-02-15",
  "pc_startTime": "09:00:00",
  "pc_facility": "1",
  "pc_billing_location": "1",
  "pc_aid": "1",
  "pid": "1"
}
```

**Update Appointment**
```
PUT /api/appointment/:auuid
```

**Delete Appointment**
```
DELETE /api/appointment/:auuid
```

### Allergy

Manage patient allergies.

#### Scopes
```
user/allergy.cruds
user/allergy.rs
```

#### Endpoints

**List Patient Allergies**
```
GET /api/patient/:puuid/allergy
```

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/allergy' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": "1",
      "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "title": "Penicillin",
      "begdate": "2020-01-15",
      "enddate": null,
      "diagnosis": "Allergy to penicillin",
      "outcome": "1",
      "severity_al": "severe",
      "reaction": "Hives"
    }
  ]
}
```

**Get Allergy by ID**
```
GET /api/patient/:puuid/allergy/:auuid
```

**Create Allergy**
```
POST /api/patient/:puuid/allergy
```

**Request Body:**
```json
{
  "title": "Penicillin",
  "begdate": "2020-01-15",
  "diagnosis": "Allergy to penicillin",
  "severity_al": "severe",
  "reaction": "Hives"
}
```

**Update Allergy**
```
PUT /api/patient/:puuid/allergy/:auuid
```

**Delete Allergy**
```
DELETE /api/patient/:puuid/allergy/:auuid
```

### Medical Problem

Manage patient medical problems and conditions.

#### Scopes
```
user/medical_problem.cruds
user/medical_problem.rs
```

#### Endpoints

**List Patient Medical Problems**
```
GET /api/patient/:puuid/medical_problem
```

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/medical_problem' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": "1",
      "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "title": "Hypertension",
      "begdate": "2018-06-01",
      "enddate": null,
      "diagnosis": "Essential hypertension",
      "outcome": "1",
      "occurrence": "0"
    }
  ]
}
```

**Get Medical Problem by ID**
```
GET /api/patient/:puuid/medical_problem/:muuid
```

**Create Medical Problem**
```
POST /api/patient/:puuid/medical_problem
```

**Request Body:**
```json
{
  "title": "Hypertension",
  "begdate": "2018-06-01",
  "diagnosis": "Essential hypertension",
  "occurrence": "0"
}
```

**Update Medical Problem**
```
PUT /api/patient/:puuid/medical_problem/:muuid
```

**Delete Medical Problem**
```
DELETE /api/patient/:puuid/medical_problem/:muuid
```

### Medication

Manage patient medications.

#### Scopes
```
user/medication.cruds
user/medication.rs
```

#### Endpoints

**List Patient Medications**
```
GET /api/patient/:puuid/medication
```

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/medication' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": "1",
      "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "title": "Lisinopril 10mg",
      "begdate": "2023-01-15",
      "enddate": null,
      "diagnosis": "Hypertension",
      "active": "1",
      "route": "oral",
      "interval": "1",
      "form": "tablet"
    }
  ]
}
```

**Get Medication by ID**
```
GET /api/patient/:puuid/medication/:muuid
```

**Create Medication**
```
POST /api/patient/:puuid/medication
```

**Request Body:**
```json
{
  "title": "Lisinopril 10mg",
  "begdate": "2023-01-15",
  "diagnosis": "Hypertension",
  "active": "1",
  "route": "oral",
  "interval": "1",
  "form": "tablet"
}
```

**Update Medication**
```
PUT /api/patient/:puuid/medication/:muuid
```

**Delete Medication**
```
DELETE /api/patient/:puuid/medication/:muuid
```

### Prescription

Manage patient prescriptions (read-only).

#### Scopes
```
user/prescription.rs    # Read-only
```

#### Endpoints

**List Patient Prescriptions**
```
GET /api/patient/:puuid/prescription
```

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/prescription' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": "1",
      "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "drug": "Lisinopril 10mg",
      "quantity": "30",
      "refills": "3",
      "date_added": "2023-01-15",
      "active": "1",
      "dosage": "1 tablet daily",
      "provider_id": "1"
    }
  ]
}
```

**Get Prescription by ID**
```
GET /api/patient/:puuid/prescription/:pruid
```

### Procedure

Manage patient procedures (read-only).

#### Scopes
```
user/procedure.rs    # Read-only
```

#### Endpoints

**List Patient Procedures**
```
GET /api/patient/:puuid/procedure
```

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/procedure' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "procedure_order_id": "1",
      "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "date_ordered": "2023-06-15",
      "order_status": "complete",
      "order_priority": "normal",
      "order_diagnosis": "Annual screening"
    }
  ]
}
```

**Get Procedure by ID**
```
GET /api/patient/:puuid/procedure/:pouid
```

### Vital Signs

Manage patient vital signs.

#### Scopes
```
user/vital.cruds
user/vital.rs
```

#### Endpoints

**List Patient Vital Signs**
```
GET /api/patient/:puuid/vital
```

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/vital' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": "1",
      "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "date": "2024-01-15",
      "bps": "120",
      "bpd": "80",
      "weight": "180",
      "height": "70",
      "temperature": "98.6",
      "pulse": "72",
      "respiration": "16",
      "BMI": "25.8"
    }
  ]
}
```

**Get Vital by ID**
```
GET /api/patient/:puuid/vital/:vuid
```

**Create Vital**
```
POST /api/patient/:puuid/vital
```

**Request Body:**
```json
{
  "date": "2024-01-15",
  "bps": "120",
  "bpd": "80",
  "weight": "180",
  "height": "70",
  "temperature": "98.6",
  "pulse": "72",
  "respiration": "16"
}
```

**Update Vital**
```
PUT /api/patient/:puuid/vital/:vuid
```

### Document

Manage patient documents.

#### Scopes
```
user/document.cruds
user/document.rs
```

#### Endpoints

**List Patient Documents**
```
GET /api/patient/:puuid/document
```

**Search Parameters:**
- `path` - Document path/category
- `date_from` - From date (YYYY-MM-DD)
- `date_to` - To date (YYYY-MM-DD)

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/document' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": "1",
      "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "name": "Lab Results.pdf",
      "url": "/portal/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/document/90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "size": "245680",
      "mimetype": "application/pdf",
      "date": "2024-01-15",
      "category": "Lab Reports"
    }
  ]
}
```

**Get Document by ID**
```
GET /api/patient/:puuid/document/:duuid
```

**Upload Document**
```
POST /api/patient/:puuid/document
```

**Request:** Multipart form data
- `file` - Document file
- `path` - Category/folder
- `date` - Document date (optional)

**Example:**
```bash
curl -X POST 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/document' \
  -H 'Authorization: Bearer TOKEN' \
  -F 'file=@/path/to/document.pdf' \
  -F 'path=Lab Reports' \
  -F 'date=2024-01-15'
```

**Delete Document**
```
DELETE /api/patient/:puuid/document/:duuid
```

### Insurance

Manage patient insurance information.

#### Scopes
```
user/insurance.cruds
user/insurance.rs
user/insurance_company.cruds
user/insurance_company.rs
user/insurance_type.rs
```

#### Endpoints

**List Patient Insurance**
```
GET /api/patient/:puuid/insurance
```

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient/90cde167-7b9b-4ed1-bd55-533925cb2605/insurance' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": "1",
      "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "type": "primary",
      "provider": "Aetna",
      "plan_name": "PPO Plan",
      "policy_number": "ABC123456",
      "group_number": "GRP789",
      "subscriber_fname": "John",
      "subscriber_lname": "Smith",
      "subscriber_relationship": "self",
      "subscriber_ss": "123-45-6789",
      "subscriber_DOB": "1980-01-15"
    }
  ]
}
```

**Get Insurance by ID**
```
GET /api/patient/:puuid/insurance/:iuuid
```

**Create Insurance**
```
POST /api/patient/:puuid/insurance
```

**Request Body:**
```json
{
  "type": "primary",
  "provider": "1",
  "plan_name": "PPO Plan",
  "policy_number": "ABC123456",
  "group_number": "GRP789",
  "subscriber_relationship": "self"
}
```

**Update Insurance**
```
PUT /api/patient/:puuid/insurance/:iuuid
```

**List Insurance Companies**
```
GET /api/insurance_company
```

**Get Insurance Company**
```
GET /api/insurance_company/:iuid
```

**List Insurance Types**
```
GET /api/insurance_type
```

### Practitioner

Manage practitioner information.

#### Scopes
```
user/practitioner.cruds
user/practitioner.rs
```

#### Endpoints

**List Practitioners**
```
GET /api/practitioner
```

**Search Parameters:**
- `title` - Title (Dr., MD, etc.)
- `fname` - First name
- `lname` - Last name
- `specialty` - Specialty

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/practitioner?specialty=Family%20Medicine' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": "1",
      "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "title": "Dr.",
      "fname": "Jane",
      "mname": "",
      "lname": "Doe",
      "federaltaxid": "",
      "federaldrugid": "DEA123456",
      "upin": "",
      "facility_id": "1",
      "specialty": "Family Medicine",
      "npi": "1234567890",
      "email": "jane.doe@clinic.example.com",
      "active": "1"
    }
  ]
}
```

**Get Practitioner by ID**
```
GET /api/practitioner/:pruuid
```

**Create Practitioner**
```
POST /api/practitioner
```

**Request Body:**
```json
{
  "title": "Dr.",
  "fname": "Jane",
  "lname": "Doe",
  "specialty": "Family Medicine",
  "npi": "1234567890",
  "email": "jane.doe@clinic.example.com",
  "facility_id": "1"
}
```

**Update Practitioner**
```
PUT /api/practitioner/:pruuid
```

**Partial Update Practitioner**
```
PATCH /api/practitioner/:pruuid
```

### Facility

Manage facility information.

#### Scopes
```
user/facility.cruds
user/facility.rs
```

#### Endpoints

**List Facilities**
```
GET /api/facility
```

**Search Parameters:**
- `name` - Facility name

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/api/facility' \
  -H 'Authorization: Bearer TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": "1",
      "uuid": "90c196f2-807b-4c85-afc5-d56e4f5c9f3b",
      "name": "Main Clinic",
      "phone": "555-1234",
      "fax": "555-5678",
      "street": "123 Medical Drive",
      "city": "Boston",
      "state": "MA",
      "postal_code": "12345",
      "country_code": "US",
      "federal_ein": "12-3456789",
      "facility_npi": "1234567890"
    }
  ]
}
```

**Get Facility by ID**
```
GET /api/facility/:fuuid
```

**Create Facility**
```
POST /api/facility
```

**Request Body:**
```json
{
  "name": "Main Clinic",
  "phone": "555-1234",
  "street": "123 Medical Drive",
  "city": "Boston",
  "state": "MA",
  "postal_code": "12345",
  "country_code": "US",
  "facility_npi": "1234567890"
}
```

**Update Facility**
```
PUT /api/facility/:fuuid
```

**Partial Update Facility**
```
PATCH /api/facility/:fuuid
```

### Other Endpoints

#### SOAP Note

**Scopes:** `user/soap_note.cruds`

**Endpoints:**
- `GET /api/patient/:puuid/soap_note` - List SOAP notes
- `GET /api/patient/:puuid/soap_note/:souid` - Get SOAP note
- `POST /api/patient/:puuid/soap_note` - Create SOAP note
- `PUT /api/patient/:puuid/soap_note/:souid` - Update SOAP note

#### Surgery

**Scopes:** `user/surgery.cruds`

**Endpoints:**
- `GET /api/patient/:puuid/surgery` - List surgeries
- `GET /api/patient/:puuid/surgery/:suuid` - Get surgery
- `POST /api/patient/:puuid/surgery` - Create surgery
- `PUT /api/patient/:puuid/surgery/:suuid` - Update surgery
- `DELETE /api/patient/:puuid/surgery/:suuid` - Delete surgery

#### Dental Issue

**Scopes:** `user/dental_issue.cruds`

**Endpoints:**
- `GET /api/patient/:puuid/dental_issue` - List dental issues
- `GET /api/patient/:puuid/dental_issue/:duuid` - Get dental issue
- `POST /api/patient/:puuid/dental_issue` - Create dental issue
- `PUT /api/patient/:puuid/dental_issue/:duuid` - Update dental issue
- `DELETE /api/patient/:puuid/dental_issue/:duuid` - Delete dental issue

#### Immunization

**Scopes:** `user/immunization.rs` (read-only)

**Endpoints:**
- `GET /api/patient/:puuid/immunization` - List immunizations
- `GET /api/patient/:puuid/immunization/:iuuid` - Get immunization

#### Drug

**Scopes:** `user/drug.rs` (read-only)

**Endpoints:**
- `GET /api/drug` - List drugs

#### List

**Scopes:** `user/list.rs` (read-only)

**Endpoints:**
- `GET /api/list/:list_name` - Get list by name

#### Transaction

**Scopes:** `user/transaction.cruds`

**Endpoints:**
- `GET /api/transaction` - List transactions
- `POST /api/transaction` - Create transaction

#### Message

**Scopes:** `user/message.c` (create only)

**Endpoints:**
- `POST /api/patient/:puuid/message` - Create message

## Patient Portal API

**EXPERIMENTAL** - Patient-facing API endpoints.

### Enable Portal API

**Administration → Config → Connectors**
- ☑ Enable OpenEMR Patient Portal REST API (EXPERIMENTAL)

### Base URL
```
https://localhost:9300/apis/default/portal
```

### Scopes
```
api:port                    # Base portal scope
patient/encounter.rs
patient/patient.rs
patient/appointment.rs
```

### Authentication

Patients must have API credentials generated by their clinician:
1. Navigate to patient demographics
2. Click **API Credentials** button
3. Generate credentials for patient

Patients authenticate using [Password Grant](AUTHENTICATION.md#password-grant) with `user_role=patient`.

### Endpoints

**Get Patient**
```
GET /portal/patient
```

**Example:**
```bash
curl -X GET 'https://localhost:9300/apis/default/portal/patient' \
  -H 'Authorization: Bearer PATIENT_TOKEN'
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": {
    "id": "1",
    "uuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
    "fname": "John",
    "lname": "Smith",
    "DOB": "1980-01-15"
  }
}
```

**Get Patient Encounters**
```
GET /portal/patient/encounter
```

**Get Patient Appointments**
```
GET /portal/patient/appointment
```

### Limitations

⚠️ **Portal API is experimental:**
- Limited endpoint coverage
- Subject to changes
- Not recommended for production
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

### Patient Validation Rules

**Required fields:**
- `fname` - First name
- `lname` - Last name
- `DOB` - Date of birth (YYYY-MM-DD)
- `sex` - Sex (Male/Female)

**Optional but recommended:**
- `street` - Street address
- `city` - City
- `state` - State (2-letter code)
- `postal_code` - ZIP/postal code
- `phone_home` - Home phone

**Format validation:**
- `DOB` - YYYY-MM-DD format
- `ss` - XXX-XX-XXXX format (if provided)
- `email` - Valid email format
- `postal_code` - Valid ZIP format

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
