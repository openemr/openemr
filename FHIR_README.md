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
- [Native Applications](FHIR_README.md#native-applications)
- [Carecoordination Summary Of Care (CCD) Generation](FHIR_README.md#carecoordination-summary-of-care-docref-operation)
    - [Overview Docref](FHIR_README.md#overview-docref)
    - [Generate CCDA](FHIR_README.md#generate-ccda)
    - [Details Docref](FHIR_README.md#details-docref)
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
   The system automatically creates a group for every Practitioner resource in the system where the patients in the group are the individuals who have the Practitioner as their primary care provider.
    ```sh
          curl -X GET 'https://localhost:9300/apis/default/fhir/Group/1/$export'
    ```
 - Patient Export, requires the **system/Patient.$export** scope.  Exports all data for all patients in the [Patient Compartment](https://www.hl7.org/fhir/compartmentdefinition-patient.html).
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

In order to download the documents you will need the **system/Document.read** scope.

#### Bulk FHIR Scope Reference
- All System export - **system/\*.$export system\*.$bulkdata-status system/Document.read**
- Group System export - **system/Group.$export system\*.$bulkdata-status system/Document.read**
- Patient System export - **system/Patient.$export system\*.$bulkdata-status system/Document.read**

####
## 3rd Party SMART Apps
OpenEMR supports the ability for 3rd party apps who implement the [SMART on FHIR App Launch Implementation Guide 1.1.0](http://hl7.org/fhir/smart-app-launch/2021May/) context.

3rd party Apps using the confidential app profile must be authorized by the OpenEMR Server Installation Administrator.  Access Tokens issued to 3rd party apps are only valid for one hour and must be renewed with a refresh token which is valid for up to three months.  Refresh tokens are only issued if the offline_access scope is authorized by the OpenEMR user authenticating with OpenEMR through their 3rd party app.

For a patient to have access to their patient data via a 3rd party app they must have api credentials generated by their clinician from the patient demographics page.  A patient must not have opted out of 3rd party API access.

OpenEMR does NOT support wildcard scopes (patient/*.* or patient/*.read).  Scopes must be requested explicitly by an app at the time of registration.  OpenEMR does not support adding scopes from the initial registration.


## Revoking Clients, Users, Access Tokens, Refresh Tokens

## Revoking Clients
You can disable a client completely which prevents their access tokens from being used in the system from the Admin -> System -> API Clients interface.  Edit the client registered in your system you wish to disable and hit the Disable button.

## Revoking Users
If you wish to revoke a user's authorization for a particular client you will need to open up the API client from the Admin -> System -> API Clients interface.  Once you are editing the client you will need to go to the Authenticated API Users section.

From there you can find the user that is listed and hit the Revoke User button (Note this can be a lengthy list so use your browser's search text functionality to find the user).

## Revoking Access Tokens
You can revoke an access token two ways.  One from the API Client edit screen, finding the client and then the access token's identifier you wish to revoke.

The second way is if you have the fully encoded access token using the API Client Tools screen.  Go to Admin->System->API Clients and then click on the Token Tools button.  Paste in the entire encoded token and then select Parse Token.  Information about the token will be displayed including the authenticated user that authorized the token.  Now select the Revoke Token button to revoke the token.  A success message will be displayed when the revocation completes.  You can parse the token again to see that the token has been revoked.

## Native Applications
Interoperability requirements with OpenEMR for Native Applications

- Native applications wishing to use the OpenEMR FHIR API with refresh tokens MUST be capable of storing the refresh token in a secure manner similar to the requirements of storing a secret for confidential apps.
- Native applications must register their application as a confidential app
- Native applications must request the offline_scope in their initial API request in order to receive a refresh token
- Native application refresh tokens are valid for 3 months before they must be renewed.
- Native applications can only communicate with OpenEMR over a TLS secured channel in order to ensure the safe transmission of the refresh token.
- Native applications must use the Authorization Code grant flow in order to receive a refresh token.

It is recommended that native applications follow best practices for native client applications as outlined in RFC 8252 OAuth 2.0 for Native Apps.

## Carecoordination Summary of Care Docref Operation
### Overview Docref
- The $docref operation is used to request the server to generate a document based on the specified
  parameters. If no additional parameters are specified then a DocumentReference to the patient's most current Clinical
  Summary of Care Document (CCD) is returned. The document itself is retrieved using the DocumentReference.content.attachment.url
  element.  See <a href='http://hl7.org/fhir/us/core/OperationDefinition-docref.html' target='_blank'
  rel='noopener'>http://hl7.org/fhir/us/core/OperationDefinition-docref.html</a> for more details.
### Generate CCDA
- [Tutorial to Generate CCDA (with Screenshots)](https://github.com/openemr/openemr/issues/5284#issuecomment-1155678620)
### Details Docref
- Requires <context>/DocumentReference.$docref, <context>/DocumentReference.read, and <context>/Document.read scopes
- Start and end date filter encounter related events for the following sections:
    - History of Procedures
    - Relevant DX Tests / LAB Data
    - Functional Status
    - Progress Notes
    - Procedure Notes
    - Laboratory Report Narrative
    - Encounters
    - Assessments
    - Treatment Plan
    - Goals
    - Health Concerns Document
    - Reason for Referral
    - Mental Status
- The following sections have the entire medical record sent to ensure that medical professionals have medically necessary information to provide treatment of care:
    - Demographics
    - Allergies, Adverse Reactions, Alerts
    - History of Medication Use
    - Problem List
    - Immunizations
    - Social History
    - Medical Equipment
    - Vital Signs (shows the latest vitals recorded for the patient)
- CCD is generated on demand, saved off in patient's documents record under the CCDA category.
- Returns a DocumentReference search bundle per the IG spec.
- XSL to view the document can be download at /<site>/interface/modules/zend_modules/public/xsl/cda.xsl
    - Or xml file can be uploaded as a document into OpenEMR to view in a human readable format
    - Due to browser security restrictions XSL file must be in same directory as ccd document to view.
- If no start date is provided it will retrieve all records in the patient history up to the end date
- If no end date is provided it will retrieve all records connected to encounters starting from the start date
- If a records for a specific service date are desired, make the start date and end date the same day in YYYY-MM-DD format.
- Dates must be specified in least specifity to most specifity for wildcard like operations IE YYYY for the beginning of the year for start date or ending of the year for end date, YYYY-MM for the beginning of the month for start date and end of the month for end date, etc.

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

