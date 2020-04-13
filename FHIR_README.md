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

#### POST /api/auth

Obtain an FHIR token with your login (returns an FHIR token). 
Scope must match a site that has been setup in OpenEMR in the /sites/ directory.  If you haven't created additional sites
then 'default' should be the scope.

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
For ssh calls, each call must include the token:


```sh
curl -X GET 'http://localhost:8300/apis/fhir/Patient' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNnZ3eGJZYmFrOXlxUjF4U290Y1g4QVVDd3JOcG5yYXZEaFlqaHFjWXJXRGNDQUtFZmJONkh2cElTVkJiaWFobHBqOTBYZmlNRXpiY2FtU01pSHk1UzFlMmgxNmVqZEhcL1ZENlNtaVpTRFRLMmtsWDIyOFRKZzNhQmxMdUloZmNJM3FpMGFKZ003OXdtOGhYT3dpVkx5b3BFRXQ1TlNYNTE3UW5TZ0dsUVdQbG56WjVxOVYwc21tdDlSQ3RvcDV3TEkiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6ImZoaXIifQ=='
```
While using an API client you can include the token as part of authorization 

#### GET /fhir/patient

```sh
curl -X GET 'http://localhost:8300/apis/fhir/Patient'
```


### Dev Notes

- For business logic, make or use the services [here](src/Services/FHIR)
- For controller logic, make or use the classes [here](src/RestControllers/FHIR)
- For routing declarations, use the class [here](_rest_routes.inc.php).


### Project Management

#### FHIR
- TODO(?): ?

