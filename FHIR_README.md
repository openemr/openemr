# OpenEMR FHIR API Documentation

## FHIR API Table of Contents
- [Overview](FHIR_README.md#overview)
- [Prerequisite](FHIR_README.md#prerequisite)
- [Using FHIR API Internally](FHIR_README.md#using-fhir-api-internally)
- [Multisite Support](FHIR_README.md#multisite-support)
- [Authorization (in API_README.md)](API_README.md#authorization)
    - [Scopes (in API_README.md)](API_README.md#scopes)
    - [Registration (in API_README.md)](API_README.md#registration)
        - [SMART on FHIR Registration (in API_README.md)](API_README.md#smart-on-fhir-registration)
    - [Authorization Code Grant (in API_README.md)](API_README.md#authorization-code-grant)
    - [Refresh Token Grant (in API_README.md)](API_README.md#refresh-token-grant)
    - [Password Grant (in API_README.md)](API_README.md#password-grant)
    - [Client Credentials Grant (in API_README.md)](API_README.md#client-credentials-grant)
    - [Logout (in API_README.md)](API_README.md#logout)
    - [More Details (in API_README.md)](API_README.md#more-details)
- [FHIR API Documentation](FHIR_README.md#fhir-api-documentation)
    - [Capability Statement](FHIR_README.md#capability-statement)
    - [Provenance](FHIR_README.md#Provenance-resources)
    - [BULK FHIR Exports](FHIR_README.md#bulk-fhir-exports)
        - [System Export](FHIR_README.md#bulk-fhir-exports)
        - [Patient Export](FHIR_README.md#bulk-fhir-exports)
        - [Group Export](FHIR_README.md#bulk-fhir-exports)
- [3rd Party SMART Apps](FHIR_README.md#3rd-party-smart-apps)
- [For Developers](FHIR_README.md#for-developers)

## Overview

Easy-to-use JSON-based REST API for OpenEMR FHIR. See standard OpenEMR API docs [here](API_README.md).  The OpenEMR FHIR API conforms to the R4 specification and the US Core 3.1 Implementation Guide (IG).

## Prerequisite

Enable the Standard FHIR service (/fhir/ endpoints) in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR Standard FHIR REST API"

## Using FHIR API Internally

There are several ways to make API calls from an authorized session and maintain security:

-   See the script at tests/api/InternalApiTest.php for examples of internal API use cases.

## Multisite Support

Multisite is supported by including the site in the endpoint. When not using multisite or using the `default` multisite site, then a typical path would look like `apis/default/fhir/patient`. If you were using multisite and using a site called `alternate`, then the path would look like `apis/alternate/fhir/patient`.

## Authorization

OpenEMR uses OIDC compliant authorization for API. SSL is required and setting baseurl at Administration->Globals->Connectors->'Site Address (required for OAuth2 and FHIR)' is required.

See [Authorization](API_README.md#authorization) for more details.

## FHIR API Documentation

The FHIR API is documented via Swagger. Can see this documentation (and can test it) by going to the `swagger` directory in your OpenEMR installation. The FHIR API is documented there in the `fhir` section. Can also see (and test) this in the online demos at https://www.open-emr.org/wiki/index.php/Development_Demo#Daily_Build_Development_Demos (clicking on the `API (Swagger) User Interface` link for the demo will take you there).

Standard FHIR endpoints Use `https://localhost:9300/apis/default/fhir as base URI.`

Note that the `default` component can be changed to the name of the site when using OpenEMR's multisite feature.

_Example:_ `https://localhost:9300/apis/default/fhir/Patient` returns a Patient's bundle resource, etc

The Bearer token is required for each OpenEMR FHIR request (except for the Capability Statement), and is conveyed using an Authorization header. Note that the Bearer token is the access_token that is obtained in the [Authorization](API_README.md#authorization) section.

When registering an API client to use with Swagger the following for the redirect url and launch url for the client.
- Redirect URL -> <base_site_address>/swagger/oauth2-redirect.html
- Launch URL -> <base_site_address>/swagger/index.html

Request:

```sh
curl -X GET 'https://localhost:9300/apis/fhir/Patient' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNnZ3eGJZYmFrOXlxUjF4U290Y1g4QVVDd3JOcG5yYXZEaFlqaHFjWXJXRGNDQUtFZmJONkh2cElTVkJiaWFobHBqOTBYZmlNRXpiY2FtU01pSHk1UzFlMmgxNmVqZEhcL1ZENlNtaVpTRFRLMmtsWDIyOFRKZzNhQmxMdUloZmNJM3FpMGFKZ003OXdtOGhYT3dpVkx5b3BFRXQ1TlNYNTE3UW5TZ0dsUVdQbG56WjVxOVYwc21tdDlSQ3RvcDV3TEkiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6ImZoaXIifQ=='
```

---

### Capability Statement

#### GET fhir/metadata

This will return the Capability Statement. Note this can be tested in the Swagger documentation linked to in the above `FHIR API Documentation`.
    ```sh
    curl -X GET 'https://localhost:9300/apis/default/fhir/metadata'
    ```

### Provenance Resources

Provenance resources are requested by including `_revinclude=Provenance:target` in the search of a resource. Is currently supported for the following resources:
  - AllergyIntolerance
      ```sh
      curl -X GET 'https://localhost:9300/apis/default/fhir/AllergyIntolerance?_revinclude=Provenance:target'
      ```

### BULK FHIR Exports
An export operation that implements the [BULK FHIR Export ONC requirements](https://hl7.org/fhir/uv/bulkdata/export/index.html) can be requested by issuing a GET request to the following endpoints:
 - System Export, requires the **system/\*.$export** scope.  Exports All supported FHIR resources
    ```sh
          curl -X GET 'https://localhost:9300/apis/default/fhir/$export'
    ```
 - Group Export, requires the **system/Group.$export** scope.  Exports all data in the [Patient Compartment](https://www.hl7.org/fhir/compartmentdefinition-patient.html) for the group.
   There is only one group defined in the system currently.  If OpenEMR defines additional patient population groups you would change the Group ID in the API call.
    ```sh
          curl -X GET 'https://localhost:9300/apis/default/fhir/Group/1/$export'
    ```
 - Patient Export, requires the **system/Group.$export** scope.  Exports all data for all patients in the [Patient Compartment](https://www.hl7.org/fhir/compartmentdefinition-patient.html).
    ```sh
          curl -X GET 'https://localhost:9300/apis/default/fhir/Patient/$export'
    ```
You will get an empty body response with a **Content-Location** header with the URL you can query for status updates on the export.

To query the status update operation you need the **system/\*.$bulkdata-status** scope.  An example query:
 - Status Query
    ```sh
          curl -X GET 'https://localhost:9300/apis/default/fhir/$bulkdata-status?job=92a94c00-77d6-4dfc-ae3b-73550742536d'
    ```

A status Query will return a result like the following:
```
{
  "transactionTime": {
    "date": "2021-02-05 20:48:38.000000",
    "timezone_type": 3,
    "timezone": "UTC"
  },
  "request": "\/apis\/default\/fhir\/Group\/1\/%24export",
  "requiresAccessToken": true,
  "output": [
    {
      "url": "https:\/\/localhost:9300\/apis\/default\/fhir\/Document\/97552\/Binary",
      "type": "Patient"
    },
    {
      "url": "https:\/\/localhost:9300\/apis\/default\/fhir\/Document\/105232\/Binary",
      "type": "Encounter"
    }
  ],
  "error": []
}
```

You can download the exported documents which are formatted in Newline Delimited JSON (NDJSON) by making a call to:
    ```sh
          curl -X GET 'https://localhost:9300/apis/default/fhir/Document/105232/Binary'
    ```

## 3rd Party SMART Apps
OpenEMR supports the ability for 3rd party apps who implement the [SMART on FHIR App Launch Implementation Guide 1.1.0](http://hl7.org/fhir/smart-app-launch/2021May/) context. 

3rd party Apps using the confidential app profile must be authorized by the OpenEMR Server Installation Administrator.  Access Tokens issued to 3rd party apps are only valid for one hour and must be renewed with a refresh token which is valid for up to three months.  Refresh tokens are only issued if the offline_access scope is authorized by the OpenEMR user authenticating with OpenEMR through their 3rd party app.

For a patient to have access to their patient data via a 3rd party app they must have api credentials generated by their clinician from the patient demographics page.  A patient must not have opted out of 3rd party API access.

OpenEMR does NOT support wildcard scopes (patient/*.* or patient/*.read).  Scopes must be requested explicitly by an app at the time of registration.  OpenEMR does not support adding scopes from the initial registration.

## For Developers

FHIR endpoints are defined in the [primary routes file](_rest_routes.inc.php). The routes file maps an external, addressable
endpoint to the OpenEMR FHIR controller which handles the request, and also handles the JSON data conversions.

```php
"GET /fhir/Patient" => function () {
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

