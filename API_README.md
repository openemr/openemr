![img](./public/images/openemr-rest-api.png)

### Goal

This project aims to provide an easy-to-use JSON-based REST API for OpenEMR's most common functions. All code will be done in classes and separate from the view to help with codebase modernization efforts.

### Team

- [@matthewvita](https://github.com/matthewvita)
- [@sjpadgett](https://github.com/sjpadgett)
- [@juggernautsei](https://github.com/juggernautsei)
- [@kofiav](https://github.com/kofiav)
- [@bradymiller](https://github.com/bradymiller)

### Prerequsite
Enable this API service in OpenEMR menu: Administration->Globals->Connectors->"Enable OpenEMR REST API"

### Using API Internally
There are several ways to make API calls from an authorized session and maintain security:
* See the script at tests/api/InternalApiTest.php for examples of internal API use cases.

### Endpoints
Note: FHIR endpoints follow normal FHIR REST endpoints. Use `https://domain/apis/fhir as base URI.`

_Example:_ `https://domain/apis/fhir/Patient` returns a Patients bundle resource and etc..

#### POST /api/auth

Obtain an API token with your login (returns an API token). For FHIR replace Uri component 'api' with 'fhir':
Scope must match a site that has been setup in OpenEMR in the /sites/ directory.  If you haven't created additional sites
then 'default' should be the scope.

```
curl -X POST -H 'Content-Type: application/json' 'https://localhost:8300/apis/api/auth' \
-d '{
    "grant_type":"password",
    "username": "ServiceUser",
    "password": "password",
    "scope":"site id"
}'
```
Response:
```
{
"token_type":"Bearer",
"access_token":"d2870cb522230dbb8946b2f47d2c7e6664656661756c74",
"expires_in":"3600"
}
```
Each call must include the token:

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/medical_problem' \
  -H 'Authorization: Bearer d2870cb522230dbb8946b2f47d2c7e6664656661756c74'
```

#### POST /api/facility

```
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

```
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

```
curl -X GET 'http://localhost:8300/apis/api/facility'
```

#### GET /api/facility/:fid

```
curl -X GET 'http://localhost:8300/apis/api/facility/1'
```

#### GET /api/provider

```
curl -X GET 'http://localhost:8300/apis/api/provider'
```

#### GET /api/provider/:prid

```
curl -X GET 'http://localhost:8300/apis/api/provider/1'
```

#### POST /api/patient

```
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
    "dob": "1992-02-02",
    "sex": "Male",
    "race": "",
    "ethnicity": ""
}'
```

#### PUT /api/patient/:pid

```
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
    "dob": "1992-02-03",
    "sex": "Male",
    "race": "",
    "ethnicity": ""
}'
```

#### GET /api/patient

```
curl -X GET 'http://localhost:8300/apis/api/patient'
```

```
curl -X GET 'http://localhost:8300/apis/api/patient&fname=...&lname=...&dob=...'
```

#### GET /api/patient/:pid

```
curl -X GET 'http://localhost:8300/apis/api/patient/1'
```

#### GET /api/patient/:pid/encounter

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter'
```

#### GET /api/patient/:pid/encounter/:eid

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1'
```

#### POST /api/patient/:pid/encounter/:eid/vital

```
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

```
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

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/vital'
```

#### GET /api/patient/:pid/encounter/:eid/vital/:vid

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/vital/1'
```

#### POST /api/patient/:pid/encounter/:eid/soap_note

```
curl -X POST 'http://localhost:8300/apis/api/patient/1/encounter/1/soap_note' -d \
'{
    "subjective": "...",
    "objective": "...",
    "assessment": "...",
    "plan": "..."
}'
```

#### PUT /api/patient/:pid/encounter/:eid/soap_note/:sid

```
curl -X PUT 'http://localhost:8300/apis/api/patient/1/encounter/1/soap_note/1' -d \
'{
    "subjective": "...",
    "objective": "...",
    "assessment": "...",
    "plan": "..."
}'
```

#### GET /api/patient/:pid/encounter/:eid/soap_note

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/soap_note'
```

#### GET /api/patient/:pid/encounter/:eid/soap_note/:sid

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/encounter/1/soap_note/1'
```

#### POST /api/patient/:pid/medical_problem

```
curl -X POST 'http://localhost:8300/apis/api/patient/1/medical_problem' -d \
'{
    "title": "Dermatochalasis",
    "begdate": "2010-04-13",
    "enddate": null,
    "diagnosis": "ICD10:H02.839"
}'
```

#### PUT /api/patient/:pid/medical_problem/:mid

```
curl -X PUT 'http://localhost:8300/apis/api/patient/1/medical_problem/1' -d \
'{
    "title": "Dermatochalasis",
    "begdate": "2010-04-13",
    "enddate": "2018-03-12",
    "diagnosis": "ICD10:H02.839"
}'
```

#### GET /api/patient/:pid/medical_problem

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/medical_problem'
```

#### GET /api/patient/:pid/medical_problem/:mid

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/medical_problem/1'
```

#### DELETE /api/patient/:pid/medical_problem/:mid

```
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/medical_problem/1'
```

#### POST /api/patient/:pid/allergy

```
curl -X POST 'http://localhost:8300/apis/api/patient/1/allergy' -d \
'{
    "title": "Iodine",
    "begdate": "2010-10-13",
    "enddate": null
}'
```

#### PUT /api/patient/:pid/allergy/:aid

```
curl -X PUT 'http://localhost:8300/apis/api/patient/1/allergy/1' -d \
'{
    "title": "Iodine",
    "begdate": "2012-10-13",
    "enddate": null
}'
```

#### GET /api/patient/:pid/allergy

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/allergy'
```

#### GET /api/patient/:pid/allergy/:aid

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/allergy/1'
```

#### DELETE /api/patient/:pid/allergy/:aid

```
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/allergy/1'
```

#### POST /api/patient/:pid/medication

```
curl -X POST 'http://localhost:8300/apis/api/patient/1/medication' -d \
'{
    "title": "Norvasc",
    "begdate": "2013-10-13",
    "enddate": null
}'
```

#### PUT /api/patient/:pid/medication/:mid

```
curl -X PUT 'http://localhost:8300/apis/api/patient/1/medication/1' -d \
'{
    "title": "Norvasc",
    "begdate": "2013-04-13",
    "enddate": null
}'
```

#### GET /api/patient/:pid/medication

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/medication'
```

#### GET /api/patient/:pid/medication/:mid

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/medication/1'
```

#### DELETE /api/patient/:pid/medication/:mid

```
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/medication/1'
```

#### POST /api/patient/:pid/surgery

```
curl -X POST 'http://localhost:8300/apis/api/patient/1/surgery' -d \
'{
    "title": "Blepharoplasty",
    "begdate": "2013-10-13",
    "enddate": null,
    "diagnosis": "CPT4:15823-50"
}'
```

#### PUT /api/patient/:pid/surgery/:sid

```
curl -X PUT 'http://localhost:8300/apis/api/patient/1/surgery/1' -d \
'{
    "title": "Blepharoplasty",
    "begdate": "2013-10-14",
    "enddate": null,
    "diagnosis": "CPT4:15823-50"
}'
```

#### GET /api/patient/:pid/surgery

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/surgery'
```

#### GET /api/patient/:pid/surgery/:sid

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/surgery/1'
```

#### DELETE /api/patient/:pid/surgery/:sid

```
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/surgery/1'
```

#### POST /api/patient/:pid/dental_issue

```
curl -X POST 'http://localhost:8300/apis/api/patient/1/dental_issue' -d \
'{
    "title": "Halitosis",
    "begdate": "2015-03-17",
    "enddate": null
}'
```

#### PUT /api/patient/:pid/dental_issue/:did

```
curl -X PUT 'http://localhost:8300/apis/api/patient/1/dental_issue/1' -d \
'{
    "title": "Halitosis",
    "begdate": "2015-03-17",
    "enddate": "2018-03-20"
}'
```

#### GET /api/patient/:pid/dental_issue

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/dental_issue'
```

#### GET /api/patient/:pid/dental_issue/:did

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/dental_issue/1'
```

#### DELETE /api/patient/:pid/dental_issue/:did

```
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/dental_issue/1'
```

#### GET /api/patient/:pid/insurance

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/insurance'
```

#### GET /api/patient/:pid/insurance/:type

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/insurance/secondary'
```

#### POST /api/patient/:pid/insurance/:type

```
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

```
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

```
curl -X GET 'http://localhost:8300/apis/api/list/medical_problem_issue_list'
```

#### GET /api/version

```
curl -X GET 'http://localhost:8300/apis/api/version'
```

#### GET /api/product

```
curl -X GET 'http://localhost:8300/apis/api/product'
```

#### GET /api/insurance_company

```
curl -X GET 'http://localhost:8300/apis/api/insurance_company'
```

#### GET /api/insurance_type

```
curl -X GET 'http://localhost:8300/apis/api/insurance_type'
```

#### POST /api/insurance_company

```
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

```
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

```
curl -X GET 'http://localhost:8300/apis/api/appointment'
```

#### GET /api/appointment/:eid

```
curl -X GET 'http://localhost:8300/apis/api/appointment/1'
```

#### GET /api/patient/:pid/appointment

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/appointment'
```

#### GET /api/patient/:pid/appointment/:eid

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/appointment/1'
```

#### POST /api/patient/:pid/appointment

```
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

```
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/appointment/1' -d \
```

#### GET /api/patient/:pid/document

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/document&path=/eye_module/imaging-eye/drawings-eye'
```

Note: The `path` query string represents the OpenEMR documents paths with two exceptions:

- Spaces are represented with `_`
- All characters are lowercase

#### POST /api/patient/:pid/document

```
curl -X POST 'http://localhost:8300/apis/api/patient/1/document&path=/eye_module/imaging-eye/drawings-eye' \
 -F document=@/home/someone/Desktop/drawing.jpg
```

Note: The `path` query string represents the OpenEMR documents paths with two exceptions:

- Spaces are represented with `_`
- All characters are lowercase

#### GET /api/patient/:pid/document/:did

```
curl -X GET 'http://localhost:8300/apis/api/patient/1/document/1'
```

#### POST /api/patient/:pid/message

```
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

```
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

```
curl -X DELETE 'http://localhost:8300/apis/api/patient/1/message/1'
```

### Dev Notes

- For business logic, make or use the services [here](https://github.com/openemr/openemr/tree/master/services)
- For controller logic, make or use the classes [here](https://github.com/openemr/openemr/tree/master/rest_controllers)
- For routing declarations, use the class [here](https://github.com/openemr/openemr/blob/master/_rest_routes.inc.php).


### Project Management

- TODO(sherwin): Encounter POST
- TODO(?): Prevent `ListService` from using `enddate` of `0000-00-00` by default
- TODO(?): API for fee sheets
- TODO(?): API for pharmacies
- TODO(?): API for immunizations
- TODO(?): API for prescriptions
- TODO(?): Drug search API
- TODO(?): API for onotes


### What is that dog drawing?

That is Peppy, an old OpenEMR mascot. Long live Peppy!


### License

[GNU GPL](../LICENSE)
