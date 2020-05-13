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

- `validationErrors` contain "client based" data validation errors
- `internalErrors` contain server related  errors
- `data` is the response payload, represented as an object/`{}` for single results or an array/`[]` for multiple results

### Sections
* [facility API](API_README.md#post-apifacility)
* [provider API](API_README.md#get-apiprovider)
* [patient API](API_README.md#post-apipatient)
* [insurance API](API_README.md#get-apipatientpidinsurance)
* [appointment API](API_README.md#get-apiappointment)
* [document API](API_README.md#get-apipatientpiddocument)
* [message API](API_README.md#post-apipatientpidmessage)
* [patient portal API](API_README.md#portal-Endpoints)
* [dev notes](API_README.md#dev-notes)
* [todos](API_README.md#project-management)

### Prerequisite
Enable the Standard API service (/api/ endpoints) in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR Standard REST API"
Enable the Patient Portal API service (/portal/ endpoints) in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR Patient Portal REST API"

### Using API Internally
There are several ways to make API calls from an authorized session and maintain security:
* See the script at tests/api/InternalApiTest.php for examples of internal API use cases.

### /api/ Endpoints
OpenEMR standard endpoints Use `http://localhost:8300/apis/api as base URI.`

_Example:_ `http://localhost:8300/apis/api/patient` returns a resource of all Patients.
#### POST /api/auth

The OpenEMR API utilizes the OAuth2 password credential flow for authentication. To obtain an API token, submit your login credentials and requested scope. The scope must match a site that has been setup in OpenEMR, in the /sites/ directory.  If additional sites have not been created, set the scope
to 'default'.

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

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/medical_problem' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNmZ4TWpsNWhsZmNPelZicXBEdEZVUlNPQUY5KzdzR1Jjejc4WGZyeGFjUjY2QlhaaEs4eThkU3cxbTd5VXFBeTVyeEZpck9mVzBQNWc5dUlidERLZ0trUElCME5wRDVtTVk5bE9WaE5DTHF5RnRnT0Q0OHVuaHRvbXZ6OTEyNmZGUmVPUllSYVJORGoyZTkzTDA5OWZSb0ZRVGViTUtWUFd4ZW5cL1piSzhIWFpJZUxsV3VNcUdjQXR5dmlLQXRXNDAiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6Im9lbXIifQ=='
```

#### POST /api/facility

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

```sh
curl -X GET 'http://localhost:8300/apis/api/facility'
```

#### GET /api/facility/:fid

```sh
curl -X GET 'http://localhost:8300/apis/api/facility/1'
```

#### GET /api/provider

```sh
curl -X GET 'http://localhost:8300/apis/api/provider'
```

#### GET /api/provider/:prid

```sh
curl -X GET 'http://localhost:8300/apis/api/provider/1'
```

#### POST /api/patient

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

Response
```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": {
        "pid": 1
    }
}
```

#### PUT /api/patient/:pid

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/1' -d \
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

Response
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

```sh
curl -X GET 'http://localhost:8300/apis/api/patient'
```

Response
```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": [
        { patientRecord },
        { patientRecord },
        etc
    ]
}
```

```sh
curl -X GET 'http://localhost:8300/apis/api/patient&fname=...&lname=...&dob=...'
```

Response
```json
{
    "validationErrors": [],
    "internalErrors": [],
    "data": [
        { patientRecord },
        { patientRecord },
        etc
    ]
}
```


#### GET /api/patient/:pid

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1'
```

Response
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


#### POST /api/patient/:pid/encounter

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/encounter' -d \
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
    "provider_id": "1"
}'
```


#### PUT /api/patient/:pid/encounter/:eid

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/encounter/1' -d \
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


#### GET /api/patient/:pid/encounter

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter'
```

#### GET /api/patient/:pid/encounter/:eid

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1'
```

#### POST /api/patient/:pid/encounter/:eid/vital

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

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/vital'
```

#### GET /api/patient/:pid/encounter/:eid/vital/:vid

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/vital/1'
```

#### POST /api/patient/:pid/encounter/:eid/soap_note

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

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/soap_note'
```

#### GET /api/patient/:pid/encounter/:eid/soap_note/:sid

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/soap_note/1'
```

#### POST /api/patient/:pid/medical_problem

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/medical_problem' -d \
'{
    "title": "Dermatochalasis",
    "begdate": "2010-04-13",
    "enddate": null,
    "diagnosis": "ICD10:H02.839"
}'
```

#### PUT /api/patient/:pid/medical_problem/:mid

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/1/medical_problem/1' -d \
'{
    "title": "Dermatochalasis",
    "begdate": "2010-04-13",
    "enddate": "2018-03-12",
    "diagnosis": "ICD10:H02.839"
}'
```

#### GET /api/patient/:pid/medical_problem

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/medical_problem'
```

#### GET /api/patient/:pid/medical_problem/:mid

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/medical_problem/1'
```

#### DELETE /api/patient/:pid/medical_problem/:mid

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/medical_problem/1'
```

#### POST /api/patient/:pid/allergy

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/allergy' -d \
'{
    "title": "Iodine",
    "begdate": "2010-10-13",
    "enddate": null
}'
```

#### PUT /api/patient/:pid/allergy/:aid

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/1/allergy/1' -d \
'{
    "title": "Iodine",
    "begdate": "2012-10-13",
    "enddate": null
}'
```

#### GET /api/patient/:pid/allergy

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/allergy'
```

#### GET /api/patient/:pid/allergy/:aid

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/allergy/1'
```

#### DELETE /api/patient/:pid/allergy/:aid

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/allergy/1'
```

#### POST /api/patient/:pid/medication

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/medication' -d \
'{
    "title": "Norvasc",
    "begdate": "2013-10-13",
    "enddate": null
}'
```

#### PUT /api/patient/:pid/medication/:mid

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/1/medication/1' -d \
'{
    "title": "Norvasc",
    "begdate": "2013-04-13",
    "enddate": null
}'
```

#### GET /api/patient/:pid/medication

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/medication'
```

#### GET /api/patient/:pid/medication/:mid

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/medication/1'
```

#### DELETE /api/patient/:pid/medication/:mid

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/medication/1'
```

#### POST /api/patient/:pid/surgery

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

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/surgery'
```

#### GET /api/patient/:pid/surgery/:sid

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/surgery/1'
```

#### DELETE /api/patient/:pid/surgery/:sid

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/surgery/1'
```

#### POST /api/patient/:pid/dental_issue

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/dental_issue' -d \
'{
    "title": "Halitosis",
    "begdate": "2015-03-17",
    "enddate": null
}'
```

#### PUT /api/patient/:pid/dental_issue/:did

```sh
curl -X PUT 'http://localhost:8300/apis/api/patient/1/dental_issue/1' -d \
'{
    "title": "Halitosis",
    "begdate": "2015-03-17",
    "enddate": "2018-03-20"
}'
```

#### GET /api/patient/:pid/dental_issue

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/dental_issue'
```

#### GET /api/patient/:pid/dental_issue/:did

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/dental_issue/1'
```

#### DELETE /api/patient/:pid/dental_issue/:did

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/dental_issue/1'
```

#### GET /api/patient/:pid/insurance

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/insurance'
```

#### GET /api/patient/:pid/insurance/:type

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/insurance/secondary'
```

#### POST /api/patient/:pid/insurance/:type

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
- `provider` is the insurance company id
- `state` can be found by querying `resource=/api/list/state`
- `country` can be found by querying `resource=/api/list/country`


#### PUT /api/patient/:pid/insurance/:type

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
- `provider` is the insurance company id
- `state` can be found by querying `resource=/api/list/state`
- `country` can be found by querying `resource=/api/list/country`

#### GET /api/list/:list_name

```sh
curl -X GET 'http://localhost:8300/apis/api/list/medical_problem_issue_list'
```

#### GET /api/version

```sh
curl -X GET 'http://localhost:8300/apis/api/version'
```

#### GET /api/product

```sh
curl -X GET 'http://localhost:8300/apis/api/product'
```

#### GET /api/insurance_company

```sh
curl -X GET 'http://localhost:8300/apis/api/insurance_company'
```

#### GET /api/insurance_type

```sh
curl -X GET 'http://localhost:8300/apis/api/insurance_type'
```

#### POST /api/insurance_company

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

```sh
curl -X GET 'http://localhost:8300/apis/api/appointment'
```

#### GET /api/appointment/:eid

```sh
curl -X GET 'http://localhost:8300/apis/api/appointment/1'
```

#### GET /api/patient/:pid/appointment

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/appointment'
```

#### GET /api/patient/:pid/appointment/:eid

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/appointment/1'
```

#### POST /api/patient/:pid/appointment

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

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/appointment/1' -d \
```

#### GET /api/patient/:pid/document

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/document&path=/eye_module/imaging-eye/drawings-eye'
```

Note: The `path` query string represents the OpenEMR documents paths with two exceptions:

- Spaces are represented with `_`
- All characters are lowercase

#### POST /api/patient/:pid/document

```sh
curl -X POST 'http://localhost:8300/apis/api/patient/1/document&path=/eye_module/imaging-eye/drawings-eye' \
 -F document=@/home/someone/Desktop/drawing.jpg
```

Note: The `path` query string represents the OpenEMR documents paths with two exceptions:

- Spaces are represented with `_`
- All characters are lowercase

#### GET /api/patient/:pid/document/:did

```sh
curl -X GET 'http://localhost:8300/apis/api/patient/1/document/1'
```

#### POST /api/patient/:pid/message

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
- For `title`, use `resource=/api/list/note_type`
- For `message_type`, use `resource=/api/list/message_status`

#### PUT /api/patient/:pid/message/:mid

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
- For `title`, use `resource=/api/list/note_type`
- For `message_type`, use `resource=/api/list/message_status`

#### DELETE /api/patient/:pid/message/:mid

```sh
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/message/1'
```

### /portal/ Endpoints
OpenEMR patient portal endpoints Use `http://localhost:8300/apis/portal as base URI.`

_Example:_ `http://localhost:8300/apis/portal/patient` returns a resource of the patient.

#### POST /portal/auth
The OpenEMR Patient Portal API utilizes the OAuth2 password credential flow for authentication. To obtain an API token, submit your login credentials and requested scope. The scope must match a site that has been setup in OpenEMR, in the /sites/ directory.  If additional sites have not been created, set the scope
to 'default'. If the patient portal is set to require email address on authenticate, then need to also include an `email` field in the request.

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

```sh
curl -X GET 'http://localhost:8300/apis/portal/patient' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNmZ4TWpsNWhsZmNPelZicXBEdEZVUlNPQUY5KzdzR1Jjejc4WGZyeGFjUjY2QlhaaEs4eThkU3cxbTd5VXFBeTVyeEZpck9mVzBQNWc5dUlidERLZ0trUElCME5wRDVtTVk5bE9WaE5DTHF5RnRnT0Q0OHVuaHRvbXZ6OTEyNmZGUmVPUllSYVJORGoyZTkzTDA5OWZSb0ZRVGViTUtWUFd4ZW5cL1piSzhIWFpJZUxsV3VNcUdjQXR5dmlLQXRXNDAiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6Im9lbXIifQ=='
```

#### GET /portal/patient

```sh
curl -X GET 'http://localhost:8300/apis/portal/patient'
```

Response
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

- For business logic, make or use the services [here](src/Services)
- For controller logic, make or use the classes [here](src/RestControllers)
- For routing declarations, use the class [here](_rest_routes.inc.php).


### Project Management

#### General API

- TODO(?): Prevent `ListService` from using `enddate` of `0000-00-00` by default
- TODO(?): API for fee sheets
- TODO(?): API for pharmacies
- TODO(?): API for immunizations
- TODO(?): API for prescriptions
- TODO(?): Drug search API
- TODO(?): API for onotes


