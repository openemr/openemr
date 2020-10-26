# OpenEMR REST API Documentation

## Overview

Easy-to-use JSON-based REST API for OpenEMR. All code is done in classes and separate from the view to help with codebase modernization efforts. FHIR is also supported, see FHIR API documentation [here](FHIR_README.md)

## Implementation

REST API endpoints are defined in the [primary routes file](_rest_routes.inc.php). The routes file maps an external, addressable
endpoint to the OpenEMR controller which handles the request, and also handles the JSON data conversions.

```php
"POST /api/patient" => function () {
    RestConfig::authorization_check("patients", "demo");
    $data = (array)(json_decode(file_get_contents("php://input")));
    return (new PatientRestController())->post($data);
}
```

At a high level, the request processing flow consists of the following steps:

```
JSON Request -> Controller Component -> Validation -> Service Component -> Database
```

The logical response flow begins with the database result:

```
Database Result -> Service Component -> Controller Component -> RequestControllerHelper -> JSON Response
```

The [RequestControllerHelper class](./src/RestControllers/RestControllerHelper.php) evaluates the Service Component's
result and maps it to a http response code and response payload. Existing APIs should be updated to utilize the
`handleProcessingResult` method as it supports the [Validator](./src/Validators/BaseValidator.php) components.

The [PatientRestController](./src/RestControllers/PatientRestController.php) may be used as a reference to see how APIs are
integrated with `RequestControllerHelper::handleProcessingResult` and the `Validator` components.

Finally, APIs which are integrated with the new `handleProcessingResult` method utilize a common response format.

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": < data payload >
}
```

-   `validationErrors` contain "client based" data validation errors
-   `internalErrors` contain server related errors
-   `data` is the response payload, represented as an object/`{}` for single results or an array/`[]` for multiple results

### Sections

-   [Standard API Endpoints](API_README.md#api-endpoints)
    -   [Facility API](API_README.md#post-apifacility)
    -   [Practitioner API](API_README.md#get-apipractitioner)
    -   [Patient API](API_README.md#post-apipatient)
    -   [Immunization API](API_README.md#get-apiimmunization)
    -   [Allergy API](API_README.md#get-apiallergy)
    -   [Procedure API](API_README.md#get-apiprocedure)
    -   [Drug API](API_README.md#get-apidrug)
    -   [Prescription API](API_README.md#get-apiprescription)
    -   [Insurance API](API_README.md#get-apipatientpidinsurance)
    -   [Appointment API](API_README.md#get-apiappointment)
    -   [Document API](API_README.md#get-apipatientpiddocument)
    -   [Message API](API_README.md#post-apipatientpidmessage)
-   [Portal API Endpoints](API_README.md#portal-Endpoints)
    -   [Patient API](API_README.md#get-portalpatient)
-   [FHIR API Endpoints](FHIR_README.md#fhir-endpoints)
    -   [FHIR Capability Statement](FHIR_README.md#capability-statement)
    -   [FHIR Authorization](FHIR_README.md#authorization)
    -   [FHIR Patient](FHIR_README.md#patient-resource)
    -   [FHIR Encounter](FHIR_README.md#encounter-resource)
    -   [FHIR Practitioner](FHIR_README.md#practitioner-resource)
    -   [FHIR PractitionerRole](FHIR_README.md#practitionerrole-resource)
    -   [FHIR Immunization](FHIR_README.md#immunization-resource)
    -   [FHIR AllergyIntolerance](FHIR_README.md#allergyintolerance-resource)
    -   [FHIR Organization](FHIR_README.md#organization-resource)
    -   [FHIR Observation](FHIR_README.md#observation-resource)
    -   [FHIR QuestionnaireResponse](FHIR_README.md#questionnaireresponse-resource)
    -   [FHIR Condition](FHIR_README.md#condition-resource)
    -   [FHIR Procedure](FHIR_README.md#procedure-resource)
    -   [FHIR MedicationRequest](FHIR_README.md#medicationrequest-resource)
    -   [FHIR Medication](FHIR_README.md#medication-resource)
    -   [FHIR Location](FHIR_README.md#location-resource)
    -   [FHIR CareTeam](FHIR_README.md#careTeam-resource)
    -   [FHIR Provenance](FHIR_README.md#Provenance-resources)
-   [Patient Portal FHIR API Endpoints](FHIR_README.md#patient-portal-fhir-endpoints)
    -   [Patient Portal FHIR Authorization](FHIR_README.md#patient-portal-authorization)
    -   [Patient Portal FHIR Patient](FHIR_README.md#patient-portal-patient-resource)
-   [Dev notes](API_README.md#dev-notes)
-   [Todos](API_README.md#project-management)

### Prerequisite

Enable the Standard API service (/api/ endpoints) in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR Standard REST API"
Enable the Patient Portal API service (/portal/ endpoints) in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR Patient Portal REST API"

### Using API Internally

There are several ways to make API calls from an authorized session and maintain security:

-   See the script at tests/api/InternalApiTest.php for examples of internal API use cases.

### /api/ Endpoints

OpenEMR standard endpoints Use `http://localhost:8300/apis/api as base URI.`

_Example:_ `http://localhost:8300/apis/api/patient` returns a resource of all Patients.

#### POST /api/auth

The OpenEMR API utilizes the OAuth2 password credential flow for authentication. To obtain an API token, submit your login credentials and requested scope. The scope must match a site that has been setup in OpenEMR, in the /sites/ directory. If additional sites have not been created, set the scope
to 'default'.

Request:

```sh
curl -X POST -H 'Content-Type: application/json' 'http://localhost:8300/apis/api/auth' \
-d '{
    "grant_type":"password",
    "username": "ServiceUser",
    "password": "password",
    "scope":"site id"
}'
```

Response:

```json
{
    "token_type": "Bearer",
    "access_token": "eyJ0b2tlbiI6IjAwNmZ4TWpsNWhsZmNPelZicXBEdEZVUlNPQUY5KzdzR1Jjejc4WGZyeGFjUjY2QlhaaEs4eThkU3cxbTd5VXFBeTVyeEZpck9mVzBQNWc5dUlidERLZ0trUElCME5wRDVtTVk5bE9WaE5DTHF5RnRnT0Q0OHVuaHRvbXZ6OTEyNmZGUmVPUllSYVJORGoyZTkzTDA5OWZSb0ZRVGViTUtWUFd4ZW5cL1piSzhIWFpJZUxsV3VNcUdjQXR5dmlLQXRXNDAiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6Im9lbXIifQ==",
    "expires_in": "3600",
    "user_data": {
        "user_id": "1"
    }
}
```

The Bearer token is required for each OpenEMR API request, and is conveyed using an Authorization header.

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/medical_problem' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNmZ4TWpsNWhsZmNPelZicXBEdEZVUlNPQUY5KzdzR1Jjejc4WGZyeGFjUjY2QlhaaEs4eThkU3cxbTd5VXFBeTVyeEZpck9mVzBQNWc5dUlidERLZ0trUElCME5wRDVtTVk5bE9WaE5DTHF5RnRnT0Q0OHVuaHRvbXZ6OTEyNmZGUmVPUllSYVJORGoyZTkzTDA5OWZSb0ZRVGViTUtWUFd4ZW5cL1piSzhIWFpJZUxsV3VNcUdjQXR5dmlLQXRXNDAiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6Im9lbXIifQ=='
```

#### POST /api/facility

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/facility' -d \
'{
    "name": "Aquaria",
    "phone": "808-606-3030",
    "fax": "808-606-3031",
    "street": "1337 Bit Shifter Ln",
    "city": "San Lorenzo",
    "state": "ZZ",
    "postal_code": "54321",
    "email": "foo@bar.com",
    "service_location": "1",
    "billing_location": "1",
    "color": "#FF69B4"
}'
```

#### PUT /api/facility/:fid

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/facility/1' -d \
'{
    "name": "Aquaria",
    "phone": "808-606-3030",
    "fax": "808-606-3031",
    "street": "1337 Bit Shifter Ln",
    "city": "San Lorenzo",
    "state": "AZ",
    "postal_code": "54321",
    "email": "foo@bar.com",
    "service_location": "1",
    "billing_location": "1",
    "color": "#FF69B4"
}'
```

#### GET /api/facility

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/facility'
```

#### GET /api/facility/:fid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/facility/1'
```

#### GET /api/practitioner

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/practitioner'
```

#### GET /api/practitioner/:uuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/practitioner/90cde167-7b9b-4ed1-bd55-533925cb2605'
```

#### POST /api/practitioner

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/practitioner' -d \
'{
    "title": "Mrs.",
    "fname": "Eduardo",
    "mname": "Kathy",
    "lname": "Perez",
    "federaltaxid": "",
    "federaldrugid": "",
    "upin": "",
    "facility_id": "3",
    "facility": "Your Clinic Name Here",
    "npi": "0123456789",
    "email": "info@pennfirm.com",
    "specialty": "",
    "billname": null,
    "url": null,
    "assistant": null,
    "organization": null,
    "valedictory": null,
    "street": "789 Third Avenue",
    "streetb": "123 Cannaut Street",
    "city": "San Diego",
    "state": "CA",
    "zip": "90210",
    "phone": "(619) 555-9827",
    "fax": null,
    "phonew1": "(619) 555-7822",
    "phonecell": "(619) 555-7821",
    "notes": null,
    "state_license_number": "123456"
}'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": {
        "id": 7,
        "uuid": "90d453fb-0248-4c0d-9575-d99d02b169f5"
    }
}
```

#### PATCH /api/practitioner/:uuid

Request:

```sh
curl -X PATCH 'http://localhost:8300/apis/api/patient/90a8923c-0b1c-4d0a-9981-994b143381a7' -d \
'{
    "title": "Mr",
    "fname": "Baz",
    "mname": "",
    "lname": "Bop",
    "street": "456 Tree Lane",
    "zip": "08642",
    "city": "FooTown",
    "state": "FL",
    "phone": "123-456-7890"
}'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": {
        "id": "7",
        "uuid": "90d453fb-0248-4c0d-9575-d99d02b169f5",
        "title": "Mr",
        "fname": "Baz",
        "lname": "Bop",
        "mname": "",
        "federaltaxid": "",
        "federaldrugid": "",
        "upin": "",
        "facility_id": "3",
        "facility": "Your Clinic Name Here",
        "npi": "0123456789",
        "email": "info@pennfirm.com",
        "active": "1",
        "specialty": "",
        "billname": "",
        "url": "",
        "assistant": "",
        "organization": "",
        "valedictory": "",
        "street": "456 Tree Lane",
        "streetb": "123 Cannaut Street",
        "city": "FooTown",
        "state": "FL",
        "zip": "08642",
        "phone": "123-456-7890",
        "fax": "",
        "phonew1": "(619) 555-7822",
        "phonecell": "(619) 555-7821",
        "notes": "",
        "state_license_number": "123456",
        "abook_title": null,
        "physician_title": null,
        "physician_code": null
    }
}
```

#### POST /api/patient

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient' -d \
'{
    "title": "Mr",
    "fname": "Foo",
    "mname": "",
    "lname": "Bar",
    "street": "456 Tree Lane",
    "postal_code": "08642",
    "city": "FooTown",
    "state": "FL",
    "country_code": "US",
    "phone_contact": "123-456-7890",
    "DOB": "1992-02-02",
    "sex": "Male",
    "race": "",
    "ethnicity": ""
}'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": {
        "pid": 1
    }
}
```

#### PUT /api/patient/:puuid

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/90a8923c-0b1c-4d0a-9981-994b143381a7' -d \
'{
    "title": "Mr",
    "fname": "Baz",
    "mname": "",
    "lname": "Bop",
    "street": "456 Tree Lane",
    "postal_code": "08642",
    "city": "FooTown",
    "state": "FL",
    "country_code": "US",
    "phone_contact": "123-456-7890",
    "DOB": "1992-02-03",
    "sex": "Male",
    "race": "",
    "ethnicity": ""
}'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": {
        "id": "193",
        "pid": "1",
        "pubpid": "",
        "title": "Mr",
        "fname": "Baz",
        "mname": "",
        "lname": "Bop",
        "ss": "",
        "street": "456 Tree Lane",
        "postal_code": "08642",
        "city": "FooTown",
        "state": "FL",
        "county": "",
        "country_code": "US",
        "drivers_license": "",
        "contact_relationship": "",
        "phone_contact": "123-456-7890",
        "phone_home": "",
        "phone_biz": "",
        "phone_cell": "",
        "email": "",
        "DOB": "1992-02-03",
        "sex": "Male",
        "race": "",
        "ethnicity": "",
        "status": ""
    }
}
```

#### GET /api/patient

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": [{ patientRecord }, { patientRecord }, etc]
}
```

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient&fname=...&lname=...&dob=...'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": [{ patientRecord }, { patientRecord }, etc]
}
```

#### GET /api/patient/:puuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/90a8923c-0b1c-4d0a-9981-994b143381a7'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": {
        "id": "193",
        "pid": "1",
        "pubpid": "",
        "title": "Mr",
        "fname": "Baz",
        "mname": "",
        "lname": "Bop",
        "ss": "",
        "street": "456 Tree Lane",
        "postal_code": "08642",
        "city": "FooTown",
        "state": "FL",
        "county": "",
        "country_code": "US",
        "drivers_license": "",
        "contact_relationship": "",
        "phone_contact": "123-456-7890",
        "phone_home": "",
        "phone_biz": "",
        "phone_cell": "",
        "email": "",
        "DOB": "1992-02-03",
        "sex": "Male",
        "race": "",
        "ethnicity": "",
        "status": ""
    }
}
```

#### GET /api/immunization

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/immunization'
```

#### GET /api/immunization/:uuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/immunization/90cde167-7b9b-4ed1-bd55-533925cb2605'
```

#### POST /api/patient/:pid/encounter

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/90a8923c-0b1c-4d0a-9981-994b143381a7/encounter' -d \
'{
    "date":"2020-11-10",
    "onset_date": "",
    "reason": "Pregnancy Test",
    "facility": "Owerri General Hospital",
    "pc_catid": "5",
    "facility_id": "3",
    "billing_facility": "3",
    "sensitivity": "normal",
    "referral_source": "",
    "pos_code": "0",
    "external_id": "",
    "provider_id": "1",
    "class_code" : "AMB"
}'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": {
        "encounter": 1,
        "uuid": "90c196f2-51cc-4655-8858-3a80aebff3ef"
    }
}
```

#### PUT /api/patient/:pid/encounter/:eid

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/90a8923c-0b1c-4d0a-9981-994b143381a7/encounter/90c196f2-51cc-4655-8858-3a80aebff3ef' -d \
'{
    "date":"2019-09-14",
    "onset_date": "2019-04-20 00:00:00",
    "reason": "Pregnancy Test",
    "pc_catid": "5",
    "facility_id": "3",
    "billing_facility": "3",
    "sensitivity": "normal",
    "referral_source": "",
    "pos_code": "0"
}'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": {
        "id": "1",
        "uuid": "90c196f2-51cc-4655-8858-3a80aebff3ef",
        "date": "2019-09-14 00:00:00",
        "reason": "Pregnancy Test",
        "facility": "Owerri General Hospital",
        "facility_id": "3",
        "pid": "1",
        "onset_date": "2019-04-20 00:00:00",
        "sensitivity": "normal",
        "billing_note": null,
        "pc_catid": "5",
        "last_level_billed": "0",
        "last_level_closed": "0",
        "last_stmt_date": null,
        "stmt_count": "0",
        "provider_id": "1",
        "supervisor_id": "0",
        "invoice_refno": "",
        "referral_source": "",
        "billing_facility": "3",
        "external_id": "",
        "pos_code": "0",
        "class_code": "AMB",
        "class_title": "ambulatory",
        "pc_catname": "Office Visit",
        "billing_facility_name": "Owerri General Hospital"
    }
}
```

#### GET /api/patient/:pid/encounter

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/90a8923c-0b1c-4d0a-9981-994b143381a7/encounter'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": [{ encounterRecord }, { encounterRecord }, etc]
}
```

#### GET /api/patient/:pid/encounter/:eid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/90a8923c-0b1c-4d0a-9981-994b143381a7/encounter/90c196f2-51cc-4655-8858-3a80aebff3ef'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": {
        "id": "1",
        "uuid": "90c196f2-51cc-4655-8858-3a80aebff3ef",
        "date": "2019-09-14 00:00:00",
        "reason": "Pregnancy Test",
        "facility": "Owerri General Hospital",
        "facility_id": "3",
        "pid": "1",
        "onset_date": "2019-04-20 00:00:00",
        "sensitivity": "normal",
        "billing_note": null,
        "pc_catid": "5",
        "last_level_billed": "0",
        "last_level_closed": "0",
        "last_stmt_date": null,
        "stmt_count": "0",
        "provider_id": "1",
        "supervisor_id": "0",
        "invoice_refno": "",
        "referral_source": "",
        "billing_facility": "3",
        "external_id": "",
        "pos_code": "0",
        "class_code": "AMB",
        "class_title": "ambulatory",
        "pc_catname": "Office Visit",
        "billing_facility_name": "Owerri General Hospital"
    }
}
```

#### POST /api/patient/:pid/encounter/:eid/vital

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/encounter/1/vital' -d \
'{
    "bps": "130",
    "bpd": "80",
    "weight": "220",
    "height": "70",
    "temperature": "98",
    "temp_method": "Oral",
    "pulse": "60",
    "respiration": "20",
    "note": "...",
    "waist_circ": "37",
    "head_circ": "22.2",
    "oxygen_saturation": "80"
}'
```

#### PUT /api/patient/:pid/encounter/:eid/vital/:vid

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/1/encounter/1/vital/1' -d \
'{
    "bps": "140",
    "bpd": "80",
    "weight": "220",
    "height": "70",
    "temperature": "98",
    "temp_method": "Oral",
    "pulse": "60",
    "respiration": "20",
    "note": "...",
    "waist_circ": "37",
    "head_circ": "22.2",
    "oxygen_saturation": "80"
}'
```

#### GET /api/patient/:pid/encounter/:eid/vital

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/vital'
```

#### GET /api/patient/:pid/encounter/:eid/vital/:vid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/vital/1'
```

#### POST /api/patient/:pid/encounter/:eid/soap_note

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/encounter/1/soap_note' -d \
'{
    "subjective": "...",
    "objective": "...",
    "assessment": "...",
    "plan": "..."
}'
```

#### PUT /api/patient/:pid/encounter/:eid/soap_note/:sid

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/1/encounter/1/soap_note/1' -d \
'{
    "subjective": "...",
    "objective": "...",
    "assessment": "...",
    "plan": "..."
}'
```

#### GET /api/patient/:pid/encounter/:eid/soap_note

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/soap_note'
```

#### GET /api/patient/:pid/encounter/:eid/soap_note/:sid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/soap_note/1'
```

#### GET /api/medical_problem

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/medical_problem'
```

#### GET /api/medical_problem/:muuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/medical_problem/9109890a-6756-44c1-a82d-bdfac91c7424'
```

#### GET /api/patient/:puuid/medical_problem

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/9101a093-da04-457f-a6a1-46ce93f0d629/medical_problem'
```

#### GET /api/patient/:puuid/medical_problem/:muuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/9101a093-da04-457f-a6a1-46ce93f0d629/medical_problem/91208832-47ab-4f65-ba44-08f57d4c028e'
```

#### POST /api/patient/:puuid/medical_problem

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/9101a093-da04-457f-a6a1-46ce93f0d629/medical_problem' -d \
'{
    "title": "Dermatochalasis",
    "begdate": "2010-04-13",
    "enddate": null,
    "diagnosis": "ICD10:H02.839"
}'
```

#### PUT /api/patient/:puuid/medical_problem/:muuid

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/9101a093-da04-457f-a6a1-46ce93f0d629/medical_problem/91208832-47ab-4f65-ba44-08f57d4c028e' -d \
'{
    "title": "Dermatochalasis",
    "begdate": "2010-04-13",
    "enddate": "2018-03-12",
    "diagnosis": "ICD10:H02.839"
}'
```

#### DELETE /api/patient/:puuid/medical_problem/:muuid

Request:

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/9101a093-da04-457f-a6a1-46ce93f0d629/medical_problem/91208832-47ab-4f65-ba44-08f57d4c028e'
```

#### GET /api/allergy

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/allergy'
```

#### GET /api/allergy/:auuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/allergy/90c196f2-51cc-4655-8858-3a80aebff3ef'
```

#### GET /api/patient/:puuid/allergy

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/90c196f2-51cc-4655-8858-3a80aebff3ef/allergy'
```

#### GET /api/patient/:puuid/allergy/:auuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/90c196f2-51cc-4655-8858-3a80aebff3ef/allergy/90c196f2-51cc-4655-8858-3a80aebff3ef'
```

#### POST /api/patient/:puuid/allergy

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/90c196f2-51cc-4655-8858-3a80aebff3ef/allergy' -d \
'{
    "title": "Iodine",
    "begdate": "2010-10-13",
    "enddate": null
}'
```

#### PUT /api/patient/:puuid/allergy/:auuid

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/90c196f2-51cc-4655-8858-3a80aebff3ef/allergy/90c196f2-51cc-4655-8858-3a80aebff3ef' -d \
'{
    "title": "Iodine",
    "begdate": "2012-10-13",
    "enddate": null
}'
```

#### DELETE /api/patient/:puuid/allergy/:auuid

Request:

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/90c196f2-51cc-4655-8858-3a80aebff3ef/allergy/90c196f2-51cc-4655-8858-3a80aebff3ef'
```

#### GET /api/procedure

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/procedure'
```

#### GET /api/procedure/:uuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/procedure/90c196f2-51cc-4655-8858-3a80aebff3ef'
```

#### GET /api/drug

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/drug'
```

#### GET /api/drug/:uuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/drug/90c196f2-51cc-4655-8858-3a80aebff3ef'
```

#### GET /api/prescription

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/prescription'
```

#### GET /api/prescription/:uuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/prescription/9128a1ec-95be-4649-8a66-d3686b7ab0ca'
```

#### POST /api/patient/:pid/medication

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/medication' -d \
'{
    "title": "Norvasc",
    "begdate": "2013-10-13",
    "enddate": null
}'
```

#### PUT /api/patient/:pid/medication/:mid

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/1/medication/1' -d \
'{
    "title": "Norvasc",
    "begdate": "2013-04-13",
    "enddate": null
}'
```

#### GET /api/patient/:pid/medication

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/medication'
```

#### GET /api/patient/:pid/medication/:mid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/medication/1'
```

#### DELETE /api/patient/:pid/medication/:mid

Request:

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/medication/1'
```

#### POST /api/patient/:pid/surgery

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/surgery' -d \
'{
    "title": "Blepharoplasty",
    "begdate": "2013-10-13",
    "enddate": null,
    "diagnosis": "CPT4:15823-50"
}'
```

#### PUT /api/patient/:pid/surgery/:sid

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/1/surgery/1' -d \
'{
    "title": "Blepharoplasty",
    "begdate": "2013-10-14",
    "enddate": null,
    "diagnosis": "CPT4:15823-50"
}'
```

#### GET /api/patient/:pid/surgery

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/surgery'
```

#### GET /api/patient/:pid/surgery/:sid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/surgery/1'
```

#### DELETE /api/patient/:pid/surgery/:sid

Request:

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/surgery/1'
```

#### POST /api/patient/:pid/dental_issue

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/dental_issue' -d \
'{
    "title": "Halitosis",
    "begdate": "2015-03-17",
    "enddate": null
}'
```

#### PUT /api/patient/:pid/dental_issue/:did

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/1/dental_issue/1' -d \
'{
    "title": "Halitosis",
    "begdate": "2015-03-17",
    "enddate": "2018-03-20"
}'
```

#### GET /api/patient/:pid/dental_issue

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/dental_issue'
```

#### GET /api/patient/:pid/dental_issue/:did

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/dental_issue/1'
```

#### DELETE /api/patient/:pid/dental_issue/:did

Request:

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/dental_issue/1'
```

#### GET /api/patient/:pid/insurance

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/insurance'
```

#### GET /api/patient/:pid/insurance/:type

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/insurance/secondary'
```

#### POST /api/patient/:pid/insurance/:type

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/10/insurance/primary' -d \
'{
    "type": "primary",
    "provider": "33",
    "plan_name": "Some Plan",
    "policy_number": "12345",
    "group_number": "252412",
    "subscriber_lname": "Tester",
    "subscriber_mname": "Xi",
    "subscriber_fname": "Foo",
    "subscriber_relationship": "other",
    "subscriber_ss": "234231234",
    "subscriber_DOB": "2018-10-03",
    "subscriber_street": "183 Cool St",
    "subscriber_postal_code": "23418",
    "subscriber_city": "Cooltown",
    "subscriber_state": "AZ",
    "subscriber_country": "USA",
    "subscriber_phone": "234-598-2123",
    "subscriber_employer": "Some Employer",
    "subscriber_employer_street": "123 Heather Lane",
    "subscriber_employer_postal_code": "23415",
    "subscriber_employer_state": "AZ",
    "subscriber_employer_country": "USA",
    "subscriber_employer_city": "Cooltown",
    "copay": "35",
    "date": "2018-10-15",
    "subscriber_sex": "Female",
    "accept_assignment": "TRUE",
    "policy_type": "a"
}'
```

Notes:

-   `provider` is the insurance company id
-   `state` can be found by querying `resource=/api/list/state`
-   `country` can be found by querying `resource=/api/list/country`

#### PUT /api/patient/:pid/insurance/:type

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/10/insurance/primary' -d \
'{
    "type": "primary",
    "provider": "33",
    "plan_name": "Some Plan",
    "policy_number": "12345",
    "group_number": "252412",
    "subscriber_lname": "Tester",
    "subscriber_mname": "Xi",
    "subscriber_fname": "Foo",
    "subscriber_relationship": "other",
    "subscriber_ss": "234231234",
    "subscriber_DOB": "2018-10-03",
    "subscriber_street": "183 Cool St",
    "subscriber_postal_code": "23418",
    "subscriber_city": "Cooltown",
    "subscriber_state": "AZ",
    "subscriber_country": "USA",
    "subscriber_phone": "234-598-2123",
    "subscriber_employer": "Some Employer",
    "subscriber_employer_street": "123 Heather Lane",
    "subscriber_employer_postal_code": "23415",
    "subscriber_employer_state": "AZ",
    "subscriber_employer_country": "USA",
    "subscriber_employer_city": "Cooltown",
    "copay": "35",
    "date": "2018-10-15",
    "subscriber_sex": "Female",
    "accept_assignment": "TRUE",
    "policy_type": "a"
}'
```

Notes:

-   `provider` is the insurance company id
-   `state` can be found by querying `resource=/api/list/state`
-   `country` can be found by querying `resource=/api/list/country`

#### GET /api/list/:list_name

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/list/medical_problem_issue_list'
```

#### GET /api/version

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/version'
```

#### GET /api/product

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/product'
```

#### GET /api/insurance_company

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/insurance_company'
```

#### GET /api/insurance_type

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/insurance_type'
```

#### POST /api/insurance_company

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/insurance_company' -d \
'{
    "name": "Cool Insurance Company",
    "attn": null,
    "cms_id": null,
    "ins_type_code": "2",
    "x12_receiver_id": null,
    "x12_default_partner_id": null,
    "alt_cms_id": "",
    "line1": "123 Cool Lane",
    "line2": "Suite 123",
    "city": "Cooltown",
    "state": "CA",
    "zip": "12245",
    "country": "USA"
}'
```

Notes: `ins_type_code` can be found by inspecting the above route (/api/insurance_type).

#### PUT /api/insurance_company/:iid

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/insurance_company/1' -d \
'{
    "name": "Super Insurance Company",
    "attn": null,
    "cms_id": null,
    "ins_type_code": "2",
    "x12_receiver_id": null,
    "x12_default_partner_id": null,
    "alt_cms_id": "",
    "line1": "123 Cool Lane",
    "line2": "Suite 123",
    "city": "Cooltown",
    "state": "CA",
    "zip": "12245",
    "country": "USA"
}'
```

Notes: `ins_type_code` can be found by inspecting the above route (/api/insurance_type).

#### GET /api/appointment

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/appointment'
```

#### GET /api/appointment/:eid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/appointment/1'
```

#### GET /api/patient/:pid/appointment

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/appointment'
```

#### GET /api/patient/:pid/appointment/:eid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/appointment/1'
```

#### POST /api/patient/:pid/appointment

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/appointment' -d \
'{
    "pc_eid":"1",
    "pc_catid": "5",
    "pc_title": "Office Visit",
    "pc_duration": "900",
    "pc_hometext": "Test",
    "pc_apptstatus": "-",
    "pc_eventDate": "2018-10-19",
    "pc_startTime": "09:00",
    "pc_facility": "9",
    "pc_billing_location": "10"
}'
```

#### DELETE /api/patient/:pid/appointment/:eid

Request:

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/appointment/1' -d \
```

#### GET /api/patient/:pid/document

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/document&path=/eye_module/imaging-eye/drawings-eye'
```

Note: The `path` query string represents the OpenEMR documents paths with two exceptions:

-   Spaces are represented with `_`
-   All characters are lowercase

#### POST /api/patient/:pid/document

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/document&path=/eye_module/imaging-eye/drawings-eye' \
 -F document=@/home/someone/Desktop/drawing.jpg
```

Note: The `path` query string represents the OpenEMR documents paths with two exceptions:

-   Spaces are represented with `_`
-   All characters are lowercase

#### GET /api/patient/:pid/document/:did

Request:

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/document/1'
```

#### POST /api/patient/:pid/message

Request:

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/message' -d \
'{
    "body": "Test 123",
    "groupname": "Default",
    "from": "admin",
    "to": "Matthew",
    "title": "Other",
    "message_status": "New"
}'
```

Notes:

-   For `title`, use `resource=/api/list/note_type`
-   For `message_type`, use `resource=/api/list/message_status`

#### PUT /api/patient/:pid/message/:mid

Request:

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/1/message/1' -d \
'{
    "body": "Test 456",
    "groupname": "Default",
    "from": "Matthew",
    "to": "admin",
    "title": "Other",
    "message_status": "New"
}'
```

Notes:

-   For `title`, use `resource=/api/list/note_type`
-   For `message_type`, use `resource=/api/list/message_status`

#### DELETE /api/patient/:pid/message/:mid

Request:

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/message/1'
```

### /portal/ Endpoints

OpenEMR patient portal endpoints Use `http://localhost:8300/apis/portal as base URI.`

_Example:_ `http://localhost:8300/apis/portal/patient` returns a resource of the patient.

#### POST /portal/auth

The OpenEMR Patient Portal API utilizes the OAuth2 password credential flow for authentication. To obtain an API token, submit your login credentials and requested scope. The scope must match a site that has been setup in OpenEMR, in the /sites/ directory. If additional sites have not been created, set the scope
to 'default'. If the patient portal is set to require email address on authenticate, then need to also include an `email` field in the request.

Request:

```sh
curl -X POST -H 'Content-Type: application/json' 'http://localhost:8300/apis/portal/auth' \
-d '{
    "grant_type":"password",
    "username": "ServiceUser",
    "password": "password",
    "scope":"site id"
}'
```

Response:

```json
{
    "token_type": "Bearer",
    "access_token": "eyJ0b2tlbiI6IjAwNmZ4TWpsNWhsZmNPelZicXBEdEZVUlNPQUY5KzdzR1Jjejc4WGZyeGFjUjY2QlhaaEs4eThkU3cxbTd5VXFBeTVyeEZpck9mVzBQNWc5dUlidERLZ0trUElCME5wRDVtTVk5bE9WaE5DTHF5RnRnT0Q0OHVuaHRvbXZ6OTEyNmZGUmVPUllSYVJORGoyZTkzTDA5OWZSb0ZRVGViTUtWUFd4ZW5cL1piSzhIWFpJZUxsV3VNcUdjQXR5dmlLQXRXNDAiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6Im9lbXIifQ==",
    "expires_in": "3600",
    "user_data": {
        "user_id": "1"
    }
}
```

The Bearer token is required for each OpenEMR Patient Portal API request, and is conveyed using an Authorization header.

Request:

```sh
curl -X GET 'http://localhost:8300/apis/portal/patient' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNmZ4TWpsNWhsZmNPelZicXBEdEZVUlNPQUY5KzdzR1Jjejc4WGZyeGFjUjY2QlhaaEs4eThkU3cxbTd5VXFBeTVyeEZpck9mVzBQNWc5dUlidERLZ0trUElCME5wRDVtTVk5bE9WaE5DTHF5RnRnT0Q0OHVuaHRvbXZ6OTEyNmZGUmVPUllSYVJORGoyZTkzTDA5OWZSb0ZRVGViTUtWUFd4ZW5cL1piSzhIWFpJZUxsV3VNcUdjQXR5dmlLQXRXNDAiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6Im9lbXIifQ=='
```

#### GET /portal/patient

Request:

```sh
curl -X GET 'http://localhost:8300/apis/portal/patient'
```

Response:

```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": {
        "id": "193",
        "pid": "1",
        "pubpid": "",
        "title": "Mr",
        "fname": "Baz",
        "mname": "",
        "lname": "Bop",
        "ss": "",
        "street": "456 Tree Lane",
        "postal_code": "08642",
        "city": "FooTown",
        "state": "FL",
        "county": "",
        "country_code": "US",
        "drivers_license": "",
        "contact_relationship": "",
        "phone_contact": "123-456-7890",
        "phone_home": "",
        "phone_biz": "",
        "phone_cell": "",
        "email": "",
        "DOB": "1992-02-03",
        "sex": "Male",
        "race": "",
        "ethnicity": "",
        "status": ""
    }
}
```

### Dev Notes

-   For business logic, make or use the services [here](src/Services)
-   For controller logic, make or use the classes [here](src/RestControllers)
-   For routing declarations, use the class [here](_rest_routes.inc.php).

### Project Management

#### General API

-   TODO(?): Prevent `ListService` from using `enddate` of `0000-00-00` by default
-   TODO(?): API for fee sheets
-   TODO(?): API for pharmacies
-   TODO(?): API for immunizations
-   TODO(?): API for prescriptions
-   TODO(?): Drug search API
-   TODO(?): API for onotes
