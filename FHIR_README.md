# OpenEMR FHIR API Documentation

### Overview

Easy-to-use JSON-based REST API for OpenEMR FHIR. All code is done in classes and separate from the view to help with codebase modernization efforts. See standard OpenEMR API docs [here](API_README.md)

### Prerequisite
Enable this FHIR service in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR REST API" 

### Using FHIR API Internally
There are several ways to make API calls from an authorized session and maintain security:
* See the script at tests/api/InternalApiTest.php for examples of internal API use cases.


### Endpoints
 FHIR endpoints Use `http://localhost:8300/apis/fhir as base URI.`

_Example:_ `http://localhost:8300/apis/fhir/Patient` returns a Patient's bundle resource, etc

#### POST /fhir/auth

The OpenEMR FHIR API utilizes the OAuth2 password credential flow for authentication. To obtain an API token, submit your login credentials and requested scope. The scope must match a site that has been setup in OpenEMR, in the /sites/ directory.  If additional sites have not been created, set the scope to 'default'.

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


```sh
curl -X GET 'http://localhost:8300/apis/fhir/Patient' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNnZ3eGJZYmFrOXlxUjF4U290Y1g4QVVDd3JOcG5yYXZEaFlqaHFjWXJXRGNDQUtFZmJONkh2cElTVkJiaWFobHBqOTBYZmlNRXpiY2FtU01pSHk1UzFlMmgxNmVqZEhcL1ZENlNtaVpTRFRLMmtsWDIyOFRKZzNhQmxMdUloZmNJM3FpMGFKZ003OXdtOGhYT3dpVkx5b3BFRXQ1TlNYNTE3UW5TZ0dsUVdQbG56WjVxOVYwc21tdDlSQ3RvcDV3TEkiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6ImZoaXIifQ=='
```

#### GET /fhir/Patient

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Patient'
```

#### GET /fhir/Patient/:pid

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Patient/1'
```

#### POST /fhir/Patient

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

#### PUT /fhir/Patient/:pid

```sh
curl -X PUT -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/fhir/Patient/1' -d \ 
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

#### PATCH /fhir/Patient/:pid

```sh
curl -X PATCH -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/fhir/Patient/1' -d \ 
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

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Encounter'
```

#### GET /fhir/Encounter/:eid

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Encounter/1'
```

#### GET /fhir/Organization

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Organization'
```

#### GET /fhir/Organization/:oid

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Organization/1'
```

#### GET /fhir/AllergyIntolerance

```sh
curl -X GET 'http://localhost:8300/apis/fhir/AllergyIntolerance'
```

#### GET /fhir/AllergyIntolerance/:id

```sh
curl -X GET 'http://localhost:8300/apis/fhir/AllergyIntolerance/1'
```

#### POST /fhir/QuestionnaireResponse

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

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Immunization'
```

#### GET /fhir/Immunization/:id

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Immunization/1'
```

#### GET /fhir/Condition

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Condition'
```

#### GET /fhir/Condition/:id

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Condition/1'
```

#### GET /fhir/Procedure

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Procedure'
```

#### GET /fhir/Procedure/:id

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Procedure/1'
```

#### GET /fhir/Medication

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Medication'
```

#### GET /fhir/Medication/:id

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Medication/1'
```

### Dev Notes

- For business logic, make or use the services [here](src/Services/FHIR)
- For controller logic, make or use the classes [here](src/RestControllers/FHIR)
- For routing declarations, use the class [here](_rest_routes.inc.php).


### Project Management

#### FHIR
- TODO(?): ?
