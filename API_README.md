# OpenEMR REST API Documentation

## REST API Table of Contents
- [Overview](API_README.md#overview)
- [Prerequisite](API_README.md#prerequisite)
- [Using API Internally](API_README.md#using-api-internally)
- [Multisite Support](API_README.md#multisite-support)
- [Authorization](API_README.md#authorization)
    - [Scopes](API_README.md#scopes)
    - [Registration](API_README.md#registration)
        - [SMART on FHIR Registration](API_README.md#smart-on-fhir-registration)
    - [Authorization Code Grant](API_README.md#authorization-code-grant)
    - [Refresh Token Grant](API_README.md#refresh-token-grant)
    - [Password Grant](API_README.md#password-grant)
    - [Client Credentials Grant](API_README#client-credentials-grant)
    - [Logout](API_README.md#logout)
    - [OpenID Connect](API_README.md#openid-connect)
    - [More Details](API_README.md#more-details)
- [Standard API Documentation](API_README.md#standard-api-documentation)
- [Patient Portal API Documentation](API_README.md#patient-portal-api-documentation)
- [FHIR API Documentation (in FHIR_README.md)](FHIR_README.md#fhir-api-documentation)
    - [Capability Statement (in FHIR_README.md)](FHIR_README.md#capability-statement)
    - [Provenance (in FHIR_README.md)](FHIR_README.md#Provenance-resources)
    - [BULK FHIR Exports (in FHIR_README.md)](FHIR_README.md#bulk-fhir-exports)
        - [System Export (in FHIR_README.md)](FHIR_README.md#bulk-fhir-exports)
        - [Patient Export (in FHIR_README.md)](FHIR_README.md#bulk-fhir-exports)
        - [Group Export (in FHIR_README.md)](FHIR_README.md#bulk-fhir-exports)
    - [3rd Party SMART Apps (in FHIR_README.md)](FHIR_README.md#3rd-party-smart-apps)
    - [Native Applications (in FHIR_README.md)](FHIR_README.md#native-applications)
    - [Carecoordination Summary of Care (CCD) Generation (in FHIR_README.md)](FHIR_README.md#carecoordination-summary-of-care-docref-operation)
        - [Overview Docref (in FHIR_README.md)](FHIR_README.md#overview-docref)
        - [Generate CCDA (in FHIR_README.md)](FHIR_README.md#generate-ccda)
        - [Details Docref (in FHIR_README.md)](FHIR_README.md#details-docref)
- [Security Settings](API_README.md#security)
- [For Developers](API_README.md#for-developers)

## Overview

Easy-to-use JSON-based REST API for OpenEMR. FHIR is also supported, see FHIR API documentation [here](FHIR_README.md),

## Prerequisite

Enable the Standard API service (/api/ endpoints) in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR Standard REST API"

## Using API Internally

There are several ways to make API calls from an authorized session and maintain security:

-   See the script at tests/api/InternalApiTest.php for examples of internal API use cases.

## Multisite Support

Multisite is supported by including the site in the endpoint. When not using multisite or using the `default` multisite site, then a typical path would look like `apis/default/api/patient`. If you are using multisite and using a site called `alternate`, then the path would look like `apis/alternate/api/patient`.

## Authorization

OpenEMR uses OIDC compliant authorization for API. SSL is required and setting baseurl at Administration->Globals->Connectors->'Site Address (required for OAuth2 and FHIR)' is required. The listing of scopes can be found in below Scopes section.

### Scopes

This is a listing of scopes:
- `openid` (Generic mandatory scope)
- `fhirUser`
- `online_access`
- `offline_access` (Will signal server to provide a refresh token)
- `launch`
- `launch/patient`
- `api:fhir` (fhir which are the /fhir/ endpoints)
  - `patient/AllergyIntolerance.read`
  - `patient/Appointment.read`
  - `patient/Binary.read`
  - `patient/CarePlan.read`
  - `patient/CareTeam.read`
  - `patient/Condition.read`
  - `patient/Coverage.read`
  - `patient/Device.read`
  - `patient/DiagnosticReport.read`
  - `patient/DocumentReference.read`
  - `patient/DocumentReference.$docref`
  - `patient/Encounter.read`
  - `patient/Goal.read`
  - `patient/Immunization.read`
  - `patient/Location.read`
  - `patient/MedicationRequest.read`
  - `patient/Medication.read`
  - `patient/Observation.read`
  - `patient/Organization.read`
  - `patient/Patient.read`
  - `patient/Person.read`
  - `patient/Practitioner.read`
  - `patient/Procedure.read`
  - `patient/Provenance.read`
  - `system/AllergyIntolerance.read`
  - `system/Binary.read`
  - `system/CarePlan.read`
  - `system/CareTeam.read`
  - `system/Condition.read`
  - `system/Coverage.read`
  - `system/Device.read`
  - `system/DiagnosticReport.read`
  - `system/DocumentReference.read`
  - `system/DocumentReference.$docref`
  - `system/Encounter.read`
  - `system/Goal.read`
  - `system/Group.read`
  - `system/Group.$export`
  - `system/Immunization.read`
  - `system/Location.read`
  - `system/MedicationRequest.read`
  - `system/Medication.read`
  - `system/Observation.read`
  - `system/Organization.read`
  - `system/Patient.read`
  - `system/Patient.$export`
  - `system/Person.read`
  - `system/Practitioner.read`
  - `system/PractitionerRole.read`
  - `system/Procedure.read`
  - `system/Provenance.read`
  - `system/*.$bulkdata-status`
  - `system/*.$export`
  - `user/AllergyIntolerance.read`
  - `user/Binary.read`
  - `user/CarePlan.read`
  - `user/CareTeam.read`
  - `user/Condition.read`
  - `user/Coverage.read`
  - `user/Device.read`
  - `user/DiagnosticReport.read`
  - `user/DocumentReference.read`
  - `user/DocumentReference.$docref`
  - `user/Encounter.read`
  - `user/Goal.read`
  - `user/Immunization.read`
  - `user/Location.read`
  - `user/MedicationRequest.read`
  - `user/Medication.read`
  - `user/Observation.read`
  - `user/Organization.read`
  - `user/Organization.write`
  - `user/Patient.read`
  - `user/Patient.write`
  - `user/Person.read`
  - `user/Practitioner.read`
  - `user/Practitioner.write`
  - `user/PractitionerRole.read`
  - `user/Procedure.read`
  - `user/Provenance.read`
- `api:oemr` (user api which are the /api/ endpoints)
  - `user/allergy.read`
  - `user/allergy.write`
  - `user/appointment.read`
  - `user/appointment.write`
  - `user/dental_issue.read`
  - `user/dental_issue.write`
  - `user/document.read`
  - `user/document.write`
  - `user/drug.read`
  - `user/encounter.read`
  - `user/encounter.write`
  - `user/facility.read`
  - `user/facility.write`
  - `user/immunization.read`
  - `user/insurance.read`
  - `user/insurance.write`
  - `user/insurance_company.read`
  - `user/insurance_company.write`
  - `user/insurance_type.read`
  - `user/list.read`
  - `user/medical_problem.read`
  - `user/medical_problem.write`
  - `user/medication.read`
  - `user/medication.write`
  - `user/message.write`
  - `user/patient.read`
  - `user/patient.write`
  - `user/practitioner.read`
  - `user/practitioner.write`
  - `user/prescription.read`
  - `user/procedure.read`
  - `user/soap_note.read`
  - `user/soap_note.write`
  - `user/surgery.read`
  - `user/surgery.write`
  - `user/transaction.read`
  - `user/transaction.write`
  - `user/vital.read`
  - `user/vital.write`
- `api:port` (patient api which are the /portal/ endpoints) (EXPERIMENTAL)
  - `patient/encounter.read`
  - `patient/patient.read`
  - `patient/appointment.read`

### Registration

Here is an example for registering a client. A client needs to be registered before applying for grant to obtain access/refresh tokens. Note: "post_logout_redirect_uris" is optional and only used if client wants a redirect to its own confirmation workflow.

Note that all scopes are included in this example for demonstration purposes. For production purposes, should only include the necessary scopes.

```sh
curl -X POST -k -H 'Content-Type: application/json' -i https://localhost:9300/oauth2/default/registration --data '{
   "application_type": "private",
   "redirect_uris":
     ["https://client.example.org/callback"],
   "post_logout_redirect_uris":
     ["https://client.example.org/logout/callback"],
   "client_name": "A Private App",
   "token_endpoint_auth_method": "client_secret_post",
   "contacts": ["me@example.org", "them@example.org"],
   "scope": "openid offline_access api:oemr api:fhir api:port user/allergy.read user/allergy.write user/appointment.read user/appointment.write user/dental_issue.read user/dental_issue.write user/document.read user/document.write user/drug.read user/encounter.read user/encounter.write user/facility.read user/facility.write user/immunization.read user/insurance.read user/insurance.write user/insurance_company.read user/insurance_company.write user/insurance_type.read user/list.read user/medical_problem.read user/medical_problem.write user/medication.read user/medication.write user/message.write user/patient.read user/patient.write user/practitioner.read user/practitioner.write user/prescription.read user/procedure.read user/soap_note.read user/soap_note.write user/surgery.read user/surgery.write user/transaction.read user/transaction.write user/vital.read user/vital.write user/AllergyIntolerance.read user/CareTeam.read user/Condition.read user/Coverage.read user/Encounter.read user/Immunization.read user/Location.read user/Medication.read user/MedicationRequest.read user/Observation.read user/Organization.read user/Organization.write user/Patient.read user/Patient.write user/Practitioner.read user/Practitioner.write user/PractitionerRole.read user/Procedure.read patient/encounter.read patient/patient.read patient/AllergyIntolerance.read patient/CareTeam.read patient/Condition.read patient/Coverage.read patient/Encounter.read patient/Immunization.read patient/MedicationRequest.read patient/Observation.read patient/Patient.read patient/Procedure.read"
  }'
```

Response:
```sh
{
    "client_id": "LnjqojEEjFYe5j2Jp9m9UnmuxOnMg4VodEJj3yE8_OA",
    "client_secret": "j21ecvLmFi9HPc_Hv0t7Ptmf1pVcZQLtHjIdU7U9tkS9WAjFJwVMav0G8ogTJ62q4BATovC7BQ19Qagc4x9BBg",
    "registration_access_token": "uiDSXx2GNSvYy5n8eW50aGrJz0HjaGpUdrGf07Agv_Q",
    "registration_client_uri": "https:\/\/localhost:9300\/oauth2\/default\/client\/6eUVG0-qK2dYiwfYdECKIw",
    "client_id_issued_at": 1604767861,
    "client_secret_expires_at": 0,
    "contacts": ["me@example.org", "them@example.org"],
    "application_type": "private",
    "client_name": "A Private App",
    "redirect_uris": ["https:\/\/client.example.org\/callback"],
    "token_endpoint_auth_method": "client_secret_post",
    "scope": "openid offline_access api:oemr api:fhir api:port user/allergy.read user/allergy.write user/appointment.read user/appointment.write user/dental_issue.read user/dental_issue.write user/document.read user/document.write user/drug.read user/encounter.read user/encounter.write user/facility.read user/facility.write user/immunization.read user/insurance.read user/insurance.write user/insurance_company.read user/insurance_company.write user/insurance_type.read user/list.read user/medical_problem.read user/medical_problem.write user/medication.read user/medication.write user/message.write user/patient.read user/patient.write user/practitioner.read user/practitioner.write user/prescription.read user/procedure.read user/soap_note.read user/soap_note.write user/surgery.read user/surgery.write  user/transaction.read user/transaction.write user/vital.read user/vital.write user/AllergyIntolerance.read user/CareTeam.read user/Condition.read user/Coverage.read user/Encounter.read user/Immunization.read user/Location.read user/Medication.read user/MedicationRequest.read user/Observation.read user/Organization.read user/Organization.write user/Patient.read user/Patient.write user/Practitioner.read user/Practitioner.write user/PractitionerRole.read user/Procedure.read patient/encounter.read patient/patient.read patient/AllergyIntolerance.read patient/CareTeam.read patient/Condition.read patient/Coverage.read patient/Encounter.read patient/Immunization.read patient/MedicationRequest.read patient/Observation.read patient/Patient.read patient/Procedure.read"
}
```

#### SMART on FHIR Registration

SMART Enabled Apps are supported.

SMART client can be registered at <website>/interface/smart/register-app.php. For example https://localhost:9300/interface/smart/register-app.php

After registering the SMART client, can then Enable it in OpenEMR at Administration->System->API Clients

After it is enabled, the SMART App will then be available to use in the Patient Summary screen (SMART Enabled Apps widget).

See this github issue for an example of a Smart App installation: https://github.com/openemr/openemr/issues/4148

### Authorization Code Grant

This is the recommended standard mechanism to obtain access/refresh tokens. This is done by using an OAuth2 client with provider url of `oauth2/<site>`; an example full path would be `https://localhost:9300/oauth2/default`.  Standard OAUTH2 clients will retrieve the authorize URL from the FHIR /metadata endpoint, but if you are building your own client you can access the metadata or go directly to the https://localhost:9300/oauth2/default/authorize endpoint.

Note that a refresh token is only supplied if the `offline_access` scope is provided when requesting authorization grant.

You will need to pass the scopes you are requesting, the redirect_uri (must be one that was registered at the time of your client registration), and a state parameter which can be any value.  Once authorization has finished the browser will be redirected to the URL specified in redirect_uri with an encrypted code value and the state value sent in the initial authorize request.

Example GET (this must be done in a browser):
```
GET /oauth2/default/authorize?client_id=yi4mnmVadpnqnJiOigkcGshuG-Kayiq6kmLqCJsYrk4&response_type=code&scope=launch%2Fpatient%20openid%20fhirUser%20offline_access%20patient%2FAllergyIntolerance.read%20patient%2FCarePlan.read%20patient%2FCareTeam.read%20patient%2FCondition.read%20patient%2FDevice.read%20patient%2FDiagnosticReport.read%20patient%2FDocumentReference.read%20patient%2FEncounter.read%20patient%2FGoal.read%20patient%2FImmunization.read%20patient%2FLocation.read%20patient%2FMedication.read%20patient%2FMedicationRequest.read%20patient%2FObservation.read%20patient%2FOrganization.read%20patient%2FPatient.read%20patient%2FPractitioner.read%20patient%2FProcedure.read%20patient%2FProvenance.read&redirect_uri=https%3A%2F%2Fclient.example.org%2Fcallback&state=9512151b-e5ca-cb4b-1ddc-aaf4cd8c6ecc
```

The client application must then make a request for an access token by hitting the /token endpoint.  Note the redirect_uri MUST match what what was sent in /authorize endpoint.  If your application is registered as a public application you must include the client_id in the POST request.  If you are registered as a confidential app you must use HTTP Basic Authentication where the client_id is your username and the password is your client_secret.  HTTP Basic Authentication follows the algorithm of base64_encode(username:client_secret).  In PHP this would be base64_encode($client_id . ':' . $client_secret);  Note that this mechanism should ONLY be used over an encrypted protocol such as TLS to prevent leaking your client_secret.

Example Public Application POST
```
curl -X POST -k -H 'Content-Type: application/x-www-form-urlencoded'
'https://localhost:9300/oauth2/default/token'
--data 'grant_type=authorization_code&client_id=yi4mnmVadpnqnJiOigkcGshuG-Kayiq6kmLqCJsYrk4redirect_uri=https%3A%2F%2Fclient.example.org%2Fcallback&code=def50...'
```

Example Private Application POST
```
curl -X POST -k -H 'Content-Type: application/x-www-form-urlencoded' \
    -H 'Authorization: Basic c3Z2TThFX1hISEhYUmtoZzUyeWoyNjdIOEYwQnpmT09pRmE4aUZBT290WTptbzZpZEFPaEU0UVYxb0lacUR5YTFHR1JHVGU5VDQzNWpzeTlRbWYxV2NiVFQ4NXhuZW5VdUpaUFR0bUZGT1QxVkhmYjZiclVvWWZ2Znd2NTFQejFldw==' \
    'https://localhost:9300/oauth2/default/token' \
    --data 'grant_type=authorization_code&client_id=yi4mnmVadpnqnJiOigkcGshuG-Kayiq6kmLqCJsYrk4redirect_uri=https%3A%2F%2Fclient.example.org%2Fcallback&code=def50...'
```
### Refresh Token Grant

Note that a refresh token is only supplied if the `offline_access` scope is provided when requesting authorization or password grant.

Example:

```sh
curl -X POST -k -H 'Content-Type: application/x-www-form-urlencoded'
-i 'https://localhost:9300/oauth2/default/token'
--data 'grant_type=refresh_token
&client_id=LnjqojEEjFYe5j2Jp9m9UnmuxOnMg4VodEJj3yE8_OA
&refresh_token=def5020089a766d16...'
```

Response:

```json
{
  "id_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJrYn...",
  "token_type": "Bearer",
  "expires_in": 3599,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJrYnl1RkRp...",
  "refresh_token": "def5020017b484b0add020bf3491a8a537fa04eda12..."
}
```

### Password Grant

Recommend not using this mechanism unless you know what you are doing. It is considered far less secure than the standard authorization code method. Because of security implications, it is not turned on by default. It can be turned on at Administration->Globals->Connectors->'Enable OAuth2 Password Grant (Not considered secure)'.

Note that all scopes are included in these examples for demonstration purposes. For production purposes, should only include the necessary scopes.

Note that a refresh token is only supplied if the `offline_access` scope is provided when requesting password grant.

Example for `users` role:
```sh
curl -X POST -k -H 'Content-Type: application/x-www-form-urlencoded'
-i 'https://localhost:9300/oauth2/default/token'
--data 'grant_type=password
&client_id=LnjqojEEjFYe5j2Jp9m9UnmuxOnMg4VodEJj3yE8_OA
&scope=openid%20offline_access%20api%3Aoemr%20api%3Afhir%20user%2Fallergy.read%20user%2Fallergy.write%20user%2Fappointment.read%20user%2Fappointment.write%20user%2Fdental_issue.read%20user%2Fdental_issue.write%20user%2Fdocument.read%20user%2Fdocument.write%20user%2Fdrug.read%20user%2Fencounter.read%20user%2Fencounter.write%20user%2Ffacility.read%20user%2Ffacility.write%20user%2Fimmunization.read%20user%2Finsurance.read%20user%2Finsurance.write%20user%2Finsurance_company.read%20user%2Finsurance_company.write%20user%2Finsurance_type.read%20user%2Flist.read%20user%2Fmedical_problem.read%20user%2Fmedical_problem.write%20user%2Fmedication.read%20user%2Fmedication.write%20user%2Fmessage.write%20user%2Fpatient.read%20user%2Fpatient.write%20user%2Fpractitioner.read%20user%2Fpractitioner.write%20user%2Fprescription.read%20user%2Fprocedure.read%20user%2Fsoap_note.read%20user%2Fsoap_note.write%20user%2Fsurgery.read%20user%2Fsurgery.write%20user%2Ftransaction.read%20user%2Ftransaction.write%20user%2Fvital.read%20user%2Fvital.write%20user%2FAllergyIntolerance.read%20user%2FCareTeam.read%20user%2FCondition.read%20user%2FCoverage.read%20user%2FEncounter.read%20user%2FImmunization.read%20user%2FLocation.read%20user%2FMedication.read%20user%2FMedicationRequest.read%20user%2FObservation.read%20user%2FOrganization.read%20user%2FOrganization.write%20user%2FPatient.read%20user%2FPatient.write%20user%2FPractitioner.read%20user%2FPractitioner.write%20user%2FPractitionerRole.read%20user%2FProcedure.read
&user_role=users
&username=admin
&password=pass'
```

Example for `patient` role:
```sh
curl -X POST -k -H 'Content-Type: application/x-www-form-urlencoded'
-i 'https://localhost:9300/oauth2/default/token'
--data 'grant_type=password
&client_id=LnjqojEEjFYe5j2Jp9m9UnmuxOnMg4VodEJj3yE8_OA
&scope=openid%20offline_access%20api%3Aport%20api%3Afhir%20patient%2Fencounter.read%20patient%2Fpatient.read%20patient%2FAllergyIntolerance.read%20patient%2FCareTeam.read%20patient%2FCondition.read%20patient%2FCoverage.read%20patient%2FEncounter.read%20patient%2FImmunization.read%20patient%2FMedication.read%20patient%2FMedicationRequest.read%20patient%2FObservation.read%20patient%2FPatient.read%20patient%2FProcedure.read
&user_role=patient
&username=Phil1
&password=phil
&email=heya@invalid.email.com'
```

Response:

```json
{
  "id_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJrYn...",
  "token_type": "Bearer",
  "expires_in": 3599,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJrYnl1RkRp...",
  "refresh_token": "def5020017b484b0add020bf3491a8a537fa04eda12..."
}
```

### Client Credentials Grant

This is an advanced grant that uses JSON Web Key Sets(JWKS) to authenticate and identify the client.  This credential grant is
required to be used for access to any **system/\*.$export** scopes.  API clients must register either web accessible JWKS URI that hosts
a RSA384 compatible key, or provide their JWKS as part of the registration. Client Credentials Grant access tokens are short
lived and valid for only 1 minute and no refresh token is issued.  Tokens are requested at `/oauth2/default/token`
To walk you through how to do this process you can follow [this guide created by HL7](https://hl7.org/fhir/uv/bulkdata/authorization/index.html).

### Logout

A grant (both Authorization Code and Password grants) can be logged out (ie. removed) by url of `oauth2/<site>/logout?id_token_hint=<id_token>`; an example full path would be `https://localhost:9300/oauth2/default/logout?id_token_hint=<id_token>`. Optional: `post_logout_redirect_uri` and `state` parameters can also be sent; note that `post_logout_redirect_uris` also needs to be set during registration for it to work.

## OpenID Connect
- The OpenEMR OpenID Connect discover endpoint is `https://{openmr_host}/oauth2/{site}/.well-known/openid-configuration` as the base URI. An example on the OpenEMR easy-dev docker with the 'default' site installation would be: https://localhost:9300/oauth2/default/.well-known/openid-configuration
- A sample response is the following:
    ```json
    {
       "issuer": "https://localhost:9300/oauth2/default",
       "authorization_endpoint": "https://localhost:9300/oauth2/default/authorize",
       "token_endpoint": "https://localhost:9300/oauth2/default/token",
       "jwks_uri": "https://localhost:9300/oauth2/default/jwk",
       "userinfo_endpoint": "https://localhost:9300/oauth2/default/userinfo",
       "registration_endpoint": "https://localhost:9300/oauth2/default/registration",
       "end_session_endpoint": "https://localhost:9300/oauth2/default/logout",
       "introspection_endpoint": "https://localhost:9300/oauth2/default/introspect",
       "scopes_supported": [
         "openid",
         "fhirUser",
         "online_access",
         "offline_access",
         "launch",
         "launch\/patient",
         "api:oemr",
         "api:fhir",
         "api:port"
       ]
    }
   ```
- The standard site used is **default**
- OpenEMR supports token revocation.  It is recommended that clients use the OpenID Connect **introspection_endpoint** retrieved from the discovery endpoint to verify a token is active before assuming the token is active.

### More Details

The forum thread that detailed development of Authorization and where questions and issues are addressed is here: https://community.open-emr.org/t/v6-authorization-and-api-changes-afoot/15450

More specific development api topics are discussed and described on the above forum thread (such as introspection).

## Standard API Documentation

The Standard API is documented via Swagger. Can see this documentation (and can test it) by going to the `swagger` directory in your OpenEMR installation. The Standard API is documented there in the `standard` section. Can also see (and test) this in the online demos at https://www.open-emr.org/wiki/index.php/Development_Demo#Daily_Build_Development_Demos (clicking on the `API (Swagger) User Interface` link for the demo will take you there).  Make sure to set your client api registration's redirect_uris to be `<OpenEMR base URI>/swagger/oauth2-redirect.html`.

OpenEMR standard endpoints Use `https://localhost:9300/apis/default/api as base URI.`

Note that the `default` component can be changed to the name of the site when using OpenEMR's multisite feature.

_Example:_ `https://localhost:9300/apis/default/api/patient` returns a resource of all Patients.

The Bearer token is required for each OpenEMR API request, and is conveyed using an Authorization header. Note that the Bearer token is the access_token that is obtained in the above [Authorization](API_README.md#authorization) section.

Request:

```sh
curl -X GET 'https://localhost:9300/apis/default/api/patient/1/medical_problem' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNmZ4TWpsNWhsZmNPelZicXBEdEZVUlNPQUY5KzdzR1Jjejc4WGZyeGFjUjY2QlhaaEs4eThkU3cxbTd5VXFBeTVyeEZpck9mVzBQNWc5dUlidERLZ0trUElCME5wRDVtTVk5bE9WaE5DTHF5RnRnT0Q0OHVuaHRvbXZ6OTEyNmZGUmVPUllSYVJORGoyZTkzTDA5OWZSb0ZRVGViTUtWUFd4ZW5cL1piSzhIWFpJZUxsV3VNcUdjQXR5dmlLQXRXNDAiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6Im9lbXIifQ=='
```

## Patient Portal API Documentation

The Patient Portal API is documented via Swagger. Can see this documentation (and can test it) by going to the `swagger` directory in your OpenEMR installation. The Patient Portal API is documented there in the `standard-patient` section. Can also see (and test) this in the online demos at https://www.open-emr.org/wiki/index.php/Development_Demo#Daily_Build_Development_Demos (clicking on the `API (Swagger) User Interface` link for the demo will take you there). Make sure to set your client api registration's redirect_uris to be `<OpenEMR base URI>/swagger/oauth2-redirect.html`.

This is under development and is considered EXPERIMENTAL.

Enable the Patient Portal API service (/portal/ endpoints) in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR Patient Portal REST API (EXPERIMENTAL)"

OpenEMR patient portal endpoints Use `https://localhost:9300/apis/default/portal as base URI.`

Note that the `default` component can be changed to the name of the site when using OpenEMR's multisite feature.

_Example:_ `https://localhost:9300/apis/default/portal/patient` returns a resource of the patient.

The Bearer token is required for each OpenEMR API request, and is conveyed using an Authorization header. Note that the Bearer token is the access_token that is obtained in the above [Authorization](API_README.md#authorization) section.

Request:

```sh
curl -X GET 'https://localhost:9300/apis/default/portal/patient' \
  -H 'Authorization: Bearer eyJ0b2tlbiI6IjAwNmZ4TWpsNWhsZmNPelZicXBEdEZVUlNPQUY5KzdzR1Jjejc4WGZyeGFjUjY2QlhaaEs4eThkU3cxbTd5VXFBeTVyeEZpck9mVzBQNWc5dUlidERLZ0trUElCME5wRDVtTVk5bE9WaE5DTHF5RnRnT0Q0OHVuaHRvbXZ6OTEyNmZGUmVPUllSYVJORGoyZTkzTDA5OWZSb0ZRVGViTUtWUFd4ZW5cL1piSzhIWFpJZUxsV3VNcUdjQXR5dmlLQXRXNDAiLCJzaXRlX2lkIjoiZGVmYXVsdCIsImFwaSI6Im9lbXIifQ=='
```

## Security
- OpenEMR adminstrators / installers should ensure that the API is protected using an end to end encryption protocol such as TLS
- Password Grant SHOULD be turned off for any kind of production use as it has a number of security problems
- Setting the Admin -> Globals -> OAuth2 App Manual Approval Settings to be 'Manual Approval' prevents any OAuth2 application from accessing the API without manual approval from an administrator.  This is the most secure setting.  However, in the USA jurisdiction that must comply with CEHRT rules for ONC 2015 Cures Update, patient standalone apps must be approved within 48 hours of a patient requesting access in order to avoid pentalities under the Information Blocking Provisions from ONC.  EHR administrators are not allowed to vet a patient's choice of an app as long as the app complies with OpenEMR's OAuth2 security requirements.  If an app requests user/* or system/* scopes, administrators can vet an application and request additional information / security on an app by app basis.  Leaving the setting at the default will auto-approve any patient standalone app.
- Public apps (ones that can't securely store a secret) MUST implement the PKCE standard specified in [RFC 7636](https://www.rfc-editor.org/rfc/rfc7636).  Confidential apps are still highly encouraged to implement PKCE to mitigate forms of MITM attacks such as multiple native app devices registering for the same custom url scheme used as the OAUTH2 redirect_uri in the authorization_code grant.

## For Developers

-   For business logic, make or use the services [here](src/Services)
-   For controller logic, make or use the classes [here](src/RestControllers)
-   For routing declarations, use the class [here](_rest_routes.inc.php).

REST API endpoints are defined in the [primary routes file](_rest_routes.inc.php). The routes file maps an external, addressable
endpoint to the OpenEMR controller which handles the request, and also handles the JSON data conversions.

```php
"POST /api/patient" => function () {
    RestConfig::authorization_check("patients", "demo");
    $data = (array) (json_decode(file_get_contents("php://input")));
    $return = (new PatientRestController())->post($data);
    RestConfig::apiLog($return, $data);
    return $return;
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
