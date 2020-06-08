# OpenEMR FHIR API Documentation

## Overview

Easy-to-use JSON-based REST API for OpenEMR FHIR. All code is done in classes and separate from the view to help with codebase modernization efforts. See standard OpenEMR API docs [here](API_README.md)

## Implementation

FHIR endpoints are defined in the [primary routes file](_rest_routes.inc.php). The routes file maps an external, addressable
endpoint to the OpenEMR FHIR controller which handles the request, and also handles the JSON data conversions.

```php
"POST /fhir/Patient" => function () {
    RestConfig::authorization_check("patients", "demo");
    $data = (array)(json_decode(file_get_contents("php://input"), true));
    return (new FhirPatientRestController())->post($data);
}
```

At a high level, the request processing flow consists of the following steps:

```
JSON Request -> FHIR Controller Component -> FHIR Validation -> Parsing FHIR Resource -> Standard Service Component -> Validation -> Database
```

The logical response flow begins with the database result:

```
Database Result -> Service Component -> FHIR Service Component -> Parse OpenEMR Record -> FHIR Controller Component -> RequestControllerHelper -> JSON Response
```

### Sections

-   [FHIR API Endpoints](FHIR_README.md#fhir-endpoints)
    -   [FHIR Patient API](FHIR_README.md#get-fhirpatient)
    -   [FHIR Encounter API](FHIR_README.md#get-fhirencounter)
    -   [FHIR Organization API](FHIR_README.md#get-fhirorganization)
    -   [FHIR AllergyIntolerance API](FHIR_README.md#get-fhirallergyintolerance)
    -   [FHIR Observation API](FHIR_README.md#get-fhirobservation)
    -   [FHIR QuestionnaireResponse API](FHIR_README.md#get-fhirquestionnaireresponse)
    -   [FHIR Immunization API](FHIR_README.md#get-fhirimmunization)
    -   [FHIR Condition API](FHIR_README.md#get-fhircondition)
    -   [FHIR Procedure API](FHIR_README.md#get-fhirprocedure)
    -   [FHIR MedicationStatement API](FHIR_README.md#get-fhirmedicationstatement)
    -   [FHIR Medication API](FHIR_README.md#get-fhirmedication)
-   [Portal FHIR API Endpoints](FHIR_README.md#portalfhir-endpoints)
    -   [Patient API](FHIR_README.md#get-portalfhirpatient)
-   [Todos](FHIR_README.md#project-management)

### Prerequisite

Enable the Standard FHIR service (/fhir/ endpoints) in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR Standard FHIR REST API"
Enable the Patient Portal FHIR service (/portalfhir/ endpoints) in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR Patient Portal FHIR REST API"

### Using FHIR API Internally

There are several ways to make API calls from an authorized session and maintain security:

-   See the script at tests/api/InternalApiTest.php for examples of internal API use cases.

### /fhir/ Endpoints

Standard FHIR endpoints Use `http://localhost:8300/apis/fhir as base URI.`

_Example:_ `http://localhost:8300/apis/fhir/Patient` returns a Patient's bundle resource, etc

#### POST /fhir/auth

The OpenEMR FHIR API utilizes the OAuth2 password credential flow for authentication. To obtain an API token, submit your login credentials and requested scope. The scope must match a site that has been setup in OpenEMR, in the /sites/ directory. If additional sites have not been created, set the scope to 'default'.

Request:

```sh
curl -X POST -H 'Content-Type: application/json' 'http://localhost:8300/apis/fhir/auth' \
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
curl -X GET 'http://localhost:8300/apis/fhir/Patient' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNnZ3eGJZYmFrOXlxUjF4U290Y1g4QVVDd3JOcG5yYXZEaFlqaHFjWXJXRGNDQUtFZmJONkh2cElTVkJiaWFobHBqOTBYZmlNRXpiY2FtU01pSHk1UzFlMmgxNmVqZEhcL1ZENlNtaVpTRFRLMmtsWDIyOFRKZzNhQmxMdUloZmNJM3FpMGFKZ003OXdtOGhYT3dpVkx5b3BFRXQ1TlNYNTE3UW5TZ0dsUVdQbG56WjVxOVYwc21tdDlSQ3RvcDV3TEkiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6ImZoaXIifQ=='
```

#### GET /fhir/Patient

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Patient'
```

#### GET /fhir/Patient/:id

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Patient/90a8923c-0b1c-4d0a-9981-994b143381a7'
```

#### POST /fhir/Patient

Request:

```sh
curl -X POST -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/fhir/Patient' -d \
'{
  "resourceType": "Patient",
  "identifier": [ { "system": "urn:oid:1.2.36.146.595.217.0.1", "value": "12345" } ],
  "name": [ {
      "family": "Chalmers",
      "given": [ "Peter", "James" ]
  } ],
  "gender": "male",
  "birthDate": "1974-12-25"
}'
```

#### PUT /fhir/Patient/:id

Request:

```sh
curl -X PUT -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/fhir/Patient/90a8923c-0b1c-4d0a-9981-994b143381a7' -d \
'{
  "resourceType": "Patient",
  "id": "1",
  "identifier": [ { "system": "urn:oid:1.2.36.146.595.217.0.1", "value": "12345" } ],
  "name": [ {
      "family": "Chalmers",
      "given": [ "Peter", "James" ]
  } ],
  "gender": "male",
  "birthDate": "1974-01-13",
  "address": [ {
      "line": [ "534 Erewhon St" ],
      "city": "PleasantVille",
      "state": "Vic",
      "postalCode": "3999"
  } ]
}'
```

#### PATCH /fhir/Patient/:id

Request:

```sh
curl -X PATCH -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/fhir/Patient/90a8923c-0b1c-4d0a-9981-994b143381a7' -d \
'[
 {
   "op": "replace",
   "path": "/address/0/postalCode",
   "value": "M5C 2X8"
 },
 {
   "op": "replace",
   "path": "/birthDate",
   "value": "1974-02-13"
 }
]'
```

#### GET /fhir/Encounter

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Encounter'
```

#### GET /fhir/Encounter/:id

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Encounter/90c196f2-51cc-4655-8858-3a80aebff3ef'
```

#### GET /fhir/Organization

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Organization'
```

#### GET /fhir/Organization/:id

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Organization/1'
```

#### GET /fhir/AllergyIntolerance

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/AllergyIntolerance'
```

#### GET /fhir/AllergyIntolerance/:id

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/AllergyIntolerance/1'
```

#### GET /fhir/Observation

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Observation'
```

#### GET /fhir/Observation/:id

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Observation/vitals-1'
```

#### POST /fhir/QuestionnaireResponse

Request:

```sh
curl -X POST -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/fhir/QuestionnaireResponse' -d \
'{
  "resourceType": "QuestionnaireResponse",
  "id": "697485",
  "meta": {
    "versionId": "1",
    "lastUpdated": "2020-03-22T09:11:45.181+00:00",
    "source": "#L0otRLyoImuOVD2S"
  },
  "status": "completed",
  "item": [ {
    "linkId": "1",
    "text": "Do you have allergies?"
  }, {
    "linkId": "2",
    "text": "General questions",
    "item": [ {
      "linkId": "2.1",
      "text": "What is your gender?"
    }, {
      "linkId": "2.2",
      "text": "What is your date of birth?"
    }]
  }]
  } ]
}'
```

#### GET /fhir/Immunization

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Immunization'
```

#### GET /fhir/Immunization/:id

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Immunization/1'
```

#### GET /fhir/Condition

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Condition'
```

#### GET /fhir/Condition/:id

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Condition/1'
```

#### GET /fhir/Procedure

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Procedure'
```

#### GET /fhir/Procedure/:id

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Procedure/1'
```

#### GET /fhir/MedicationStatement

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/MedicationStatement'
```

#### GET /fhir/MedicationStatement/:id

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/MedicationStatement/1'
```

#### GET /fhir/Medication

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Medication'
```

#### GET /fhir/Medication/:id

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Medication/1'
```

### /portalfhir/ Endpoints

OpenEMR patient portal fhir endpoints Use `http://localhost:8300/apis/portalfhir as base URI.`

_Example:_ `http://localhost:8300/apis/portalfhir/Patient` returns a resource of the patient.

#### POST /portalfhir/auth

The OpenEMR Patient Portal FHIR service utilizes the OAuth2 password credential flow for authentication. To obtain an API token, submit your login credentials and requested scope. The scope must match a site that has been setup in OpenEMR, in the /sites/ directory. If additional sites have not been created, set the scope
to 'default'. If the patient portal is set to require email address on authenticate, then need to also include an `email` field in the request.

Request:

```sh
curl -X POST -H 'Content-Type: application/json' 'http://localhost:8300/apis/portalfhir/auth' \
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

The Bearer token is required for each OpenEMR Patient Portal FHIR service request, and is conveyed using an Authorization header.

Request:

```sh
curl -X GET 'http://localhost:8300/apis/portalfhir/Patient' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNmZ4TWpsNWhsZmNPelZicXBEdEZVUlNPQUY5KzdzR1Jjejc4WGZyeGFjUjY2QlhaaEs4eThkU3cxbTd5VXFBeTVyeEZpck9mVzBQNWc5dUlidERLZ0trUElCME5wRDVtTVk5bE9WaE5DTHF5RnRnT0Q0OHVuaHRvbXZ6OTEyNmZGUmVPUllSYVJORGoyZTkzTDA5OWZSb0ZRVGViTUtWUFd4ZW5cL1piSzhIWFpJZUxsV3VNcUdjQXR5dmlLQXRXNDAiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6Im9lbXIifQ=='
```

#### GET /portalfhir/Patient

Request:

```sh
curl -X GET 'http://localhost:8300/apis/portalfhir/Patient'
```
