# OpenEMR FHIR API Documentation

## Overview

Easy-to-use JSON-based REST API for OpenEMR FHIR. All code is done in classes and separate from the view to help with codebase modernization efforts. See standard OpenEMR API docs [here](API_README.md)

## Implementation

FHIR endpoints are defined in the [primary routes file](_rest_routes.inc.php). The routes file maps an external, addressable
endpoint to the OpenEMR FHIR controller which handles the request, and also handles the JSON data conversions.

```php
"GET /fhir/Patient" => function () {
    RestConfig::scope_check("user", "Patient", "read");
    RestConfig::authorization_check("patients", "demo");
    $return = (new FhirPatientRestController())->getAll($_GET);
    RestConfig::apiLog($return);
    return $return;
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
-   [Authorization](API_README.md#authorization)
    -   [Scopes](API_README.md#scopes)
    -   [Registration](API_README.md#registration)
        -   [SMART on FHIR Registration](API_README.md#smart-on-fhir-registration)
    -   [Authorization Code Grant](API_README.md#authorization-code-grant)
    -   [Refresh Token Grant](API_README.md#refresh-token-grant)
    -   [Password Grant](API_README.md#password-grant)
    -   [Logout](API_README.md#logout)
    -   [More Details](API_README.md#more-details)
-   [FHIR API Endpoints](FHIR_README.md#fhir-endpoints)
    -   [Capability Statement](FHIR_README.md#capability-statement)
    -   [Patient](FHIR_README.md#patient-resource)
    -   [Encounter](FHIR_README.md#encounter-resource)
    -   [Practitioner](FHIR_README.md#practitioner-resource)
    -   [PractitionerRole](FHIR_README.md#practitionerrole-resource)
    -   [Immunization](FHIR_README.md#immunization-resource)
    -   [AllergyIntolerance](FHIR_README.md#allergyintolerance-resource)
    -   [Organization](FHIR_README.md#organization-resource)
    -   [Observation](FHIR_README.md#observation-resource)
    -   [Condition](FHIR_README.md#condition-resource)
    -   [Procedure](FHIR_README.md#procedure-resource)
    -   [MedicationRequest](FHIR_README.md#medicationrequest-resource)
    -   [Medication](FHIR_README.md#medication-resource)
    -   [Location](FHIR_README.md#location-resource)
    -   [CareTeam](FHIR_README.md#careTeam-resource)
    -   [Provenance](FHIR_README.md#Provenance-resources)
-   [Patient Portal FHIR API Endpoints](FHIR_README.md#patient-portal-fhir-endpoints)
    -   [Patient](FHIR_README.md#patient-portal-patient-resource)

### Prerequisite

Enable the Standard FHIR service (/fhir/ endpoints) in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR Standard FHIR REST API"

### Using FHIR API Internally

There are several ways to make API calls from an authorized session and maintain security:

-   See the script at tests/api/InternalApiTest.php for examples of internal API use cases.

### Multisite Support

Multisite is supported by including the site in the endpoint. When not using multisite or using the `default` multisite site, then a typical path would look like `apis/default/fhir/patient`. If you were using multisite and using a site called `alternate`, then the path would look like `apis/alternate/fhir/patient`.

### Authorization

OpenEMR uses OIDC compliant authorization for API. SSL is required and setting baseurl at Administration->Globals->Connectors->'Site Address (required for OAuth2 and FHIR)' is required.

See [Authorization](API_README.md#authorization) for more details.

### FHIR Endpoints

Standard FHIR endpoints Use `http://localhost:8300/apis/default/fhir as base URI.`

Note that the `default` component can be changed to the name of the site when using OpenEMR's multisite feature.

_Example:_ `http://localhost:8300/apis/default/fhir/Patient` returns a Patient's bundle resource, etc

The Bearer token is required for each OpenEMR FHIR request (except for the Capability Statement), and is conveyed using an Authorization header. Note that the Bearer token is the access_token that is obtained in the [Authorization](API_README.md#authorization) section.

Request:

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Patient' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNnZ3eGJZYmFrOXlxUjF4U290Y1g4QVVDd3JOcG5yYXZEaFlqaHFjWXJXRGNDQUtFZmJONkh2cElTVkJiaWFobHBqOTBYZmlNRXpiY2FtU01pSHk1UzFlMmgxNmVqZEhcL1ZENlNtaVpTRFRLMmtsWDIyOFRKZzNhQmxMdUloZmNJM3FpMGFKZ003OXdtOGhYT3dpVkx5b3BFRXQ1TlNYNTE3UW5TZ0dsUVdQbG56WjVxOVYwc21tdDlSQ3RvcDV3TEkiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6ImZoaXIifQ=='
```

---

### Capability Statement

#### GET fhir/metadata

This will return the Capability Statement.

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/metadata'
```

---

### Patient Resource

#### GET fhir/Patient

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Patient'
```

#### GET fhir/Patient[id]

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Patient/90a8923c-0b1c-4d0a-9981-994b143381a7'
```

-   Supported Search Parameters
    -   address
    -   address-city
    -   address-postalcode
    -   address-state
    -   birthdate
    -   email
    -   family
    -   gender
    -   given
    -   name
    -   phone
    -   telecom

#### POST fhir/Patient

Request:

```sh
curl -X POST -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/default/fhir/Patient' -d \
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

#### PUT fhir/Patient/[id]

Request:

```sh
curl -X PUT -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/default/fhir/Patient/90a8923c-0b1c-4d0a-9981-994b143381a7' -d \
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

#### PUT fhir/Patient/[id]

Request:

```sh
curl -X PUT -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/default/fhir/Patient/90a8923c-0b1c-4d0a-9981-994b143381a7' -d \
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

---

### Encounter Resource

#### GET fhir/Encounter

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Encounter'
```

#### GET fhir/Encounter[id]

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Encounter/90c196f2-51cc-4655-8858-3a80aebff3ef'
```

-   Supported Search Parameters
    -   \_id
    -   patient
    -   date {gt|lt|ge|le}

---

### Practitioner Resource

#### GET fhir/Practitioner

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Practitioner'
```

#### GET fhir/Practitioner[id]

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Practitioner/90a8923c-0b1c-4d0a-9981-994b143381a7'
```

-   Supported Search Parameters
    -   address
    -   address-city
    -   address-postalcode
    -   address-state
    -   email
    -   active
    -   family
    -   given
    -   name
    -   phone
    -   telecom

#### POST fhir/Practitioner

Request:

```sh
curl -X POST -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/default/fhir/Practitioner' -d \
'{
  "resourceType": "Practitioner",
  "identifier": [ { "system": "http://hl7.org/fhir/sid/us-npi", "value": "1122334499" } ],
  "name": [ {
      "use": "official",
      "family": "Chalmers",
      "given": [ "Peter", "James" ]
  } ]
}'
```

#### PUT fhir/Practitioner/[id]

Request:

```sh
curl -X PUT -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/default/fhir/Practitioner/90a8923c-0b1c-4d0a-9981-994b143381a7' -d \
'{
  "resourceType": "Practitioner",
  "identifier": [ { "system": "http://hl7.org/fhir/sid/us-npi", "value": "1155667799" } ],
  "name": [ {
      "use": "official",
      "family": "Theil",
      "given": [ "Katy", "Wilson" ]
  } ],
  "address": [ {
      "line": [ "534 Erewhon St" ],
      "city": "PleasantVille",
      "state": "Vic",
      "postalCode": "3999"
  } ]
}'
```

---

### PractitionerRole Resource

#### GET fhir/PractitionerRole

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/PractitionerRole'
```

#### GET fhir/PractitionerRole/[id]

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/PractitionerRole/90de091a-91e9-4bbe-9a81-75ed623f65bf'
```

-   Supported Search Parameters
    -   speciality
    -   practitioner

---

### Immunization Resource

#### GET fhir/Immunization

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Immunization'
```

#### GET fhir/Immunization/[id]

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Immunization/90feaaa2-4097-4437-966e-c425d1958dd6'
```

-   Supported Search Parameters
    -   patient

---

### AllergyIntolerance Resource

#### GET fhir/AllergyIntolerance

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/AllergyIntolerance'
```

#### GET fhir/AllergyIntolerance/[id]

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/AllergyIntolerance/90feaaa2-4097-4437-966e-c425d1958dd6'
```

---

### Organization Resource

#### GET /fhir/Organization

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Organization'
```

#### GET /fhir/Organization/:id

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Organization/1'
```

---

### Observation Resource

#### GET /fhir/Observation

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Observation'
```

#### GET /fhir/Observation/:uuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Observation/9150635b-0705-4a27-8820-df8b56cf07eb'
```

---

### QuestionnaireResponse Resource

#### POST /fhir/QuestionnaireResponse

Request:

```sh
curl -X POST -H 'Content-Type: application/fhir+json' 'http://localhost:8300/apis/default/fhir/QuestionnaireResponse' -d \
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

---

### Condition Resource

#### GET fhir/Condition

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Condition'
```

#### GET fhir/Condition/[id]

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Condition/9109890a-6756-44c1-a82d-bdfac91c7424'
```

---

### Procedure Resource

#### GET /fhir/Procedure

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Procedure'
```

#### GET /fhir/Procedure/:uuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Procedure/9109890a-6756-44c1-a82d-bdfac91c7424'
```

---

### MedicationRequest Resource

#### GET /fhir/MedicationRequest

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/MedicationRequest'
```

#### GET /fhir/MedicationRequest/:uuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/MedicationRequest/9128a1ec-95be-4649-8a66-d3686b7ab0ca'
```

---

### Medication Resource

#### GET /fhir/Medication

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Medication'
```

#### GET /fhir/Medication/:uuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Medication/9109890a-6756-44c1-a82d-bdfac91c7424'
```

---

### Location Resource

#### GET /fhir/Location

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Location'
```

#### GET /fhir/Location/:uuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/Location/90f3d0e9-2a19-453b-84bd-1fa2b533f96c'
```

---

### CareTeam Resource

#### GET /fhir/CareTeam

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/CareTeam'
```

#### GET /fhir/CareTeam/:uuid

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/fhir/CareTeam/915e8fb4-86b2-4365-a420-d46fc07d5aed'
```

---

### Provenance Resources

Provenance resources are requested by including `_revinclude=Provenance:target` in the search of a resource. Is currently supported for the following resources:
  - AllergyIntolerance
      ```sh
      curl -X GET 'http://localhost:8300/apis/default/fhir/AllergyIntolerance?_revinclude=Provenance:target'
      ```

## Patient Portal FHIR Endpoints

This is under development and is considered EXPERIMENTAL.

Enable the Patient Portal FHIR service (/portalfhir/ endpoints) in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR Patient Portal FHIR REST API (EXPERIMENTAL)"

OpenEMR patient portal fhir endpoints Use `http://localhost:8300/apis/default/portalfhir as base URI.`

Note that the `default` component can be changed to the name of the site when using OpenEMR's multisite feature.

_Example:_ `http://localhost:8300/apis/default/portalfhir/Patient` returns a resource of the patient.

The Bearer token is required for each OpenEMR FHIR request (except for the Capability Statement), and is conveyed using an Authorization header. Note that the Bearer token is the access_token that is obtained in the [Authorization](API_README.md#authorization) section.

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/portalfhir/Patient' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNmZ4TWpsNWhsZmNPelZicXBEdEZVUlNPQUY5KzdzR1Jjejc4WGZyeGFjUjY2QlhaaEs4eThkU3cxbTd5VXFBeTVyeEZpck9mVzBQNWc5dUlidERLZ0trUElCME5wRDVtTVk5bE9WaE5DTHF5RnRnT0Q0OHVuaHRvbXZ6OTEyNmZGUmVPUllSYVJORGoyZTkzTDA5OWZSb0ZRVGViTUtWUFd4ZW5cL1piSzhIWFpJZUxsV3VNcUdjQXR5dmlLQXRXNDAiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6Im9lbXIifQ=='
```

---

### Patient Portal Patient Resource

#### GET /portalfhir/Patient

Request:

```sh
curl -X GET 'http://localhost:8300/apis/default/portalfhir/Patient'
```
