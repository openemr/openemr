![img](./openemr-rest-api.png)

_(Project is in-flight - do not use in production)._

### Goal

This project aims to provide an easy-to-use JSON-based REST API for OpenEMR's most common functions. All code will be done in classes and separate from the view to help with codebase modernization efforts.


### Team

- [@juggernautsei](https://github.com/juggernautsei)
- [@matthewvi](https://github.com/matthewvi)
- ?
- ?
- ?

### Endpoints

#### POST /api/auth

Obtain an API token with your login (returns an API token):

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/api/auth' \
-d '{
    "username": "ServiceUser",
    "password": "password"
}'
```

Each call must include the token:

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/medical_problem' \
  -H 'x-api-token: b0583518bce37774f5ea627f7190d228'
```

#### POST /api/facility

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/api/facility' -d \
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
curl -X PUT 'http://localhost:8300/rest_router.php?resource=/api/facility/1' -d \
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
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/facility'
```

#### GET /api/facility/:fid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/facility/1'
```

#### GET /api/provider

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/provider'
```

#### GET /api/provider/:prid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/provider/1'
```

#### POST /api/patient

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/api/patient' -d \
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

#### GET /api/patient

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient'
```

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient&fname=...&lname=...&dob=...'
```

#### GET /api/patient/:pid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1'
```

#### GET /api/patient/:pid/encounter

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/encounter'
```

#### GET /api/patient/:pid/encounter/:eid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/encounter/1'
```

#### POST /api/patient/:pid/encounter/:eid/vital

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/api/patient/1/encounter/1/vital' -d \
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
curl -X PUT 'http://localhost:8300/rest_router.php?resource=/api/patient/1/encounter/1/vital/1' -d \
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
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/encounter/1/vital'
```

#### GET /api/patient/:pid/encounter/:eid/vital/:vid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/encounter/1/vital/1'
```

#### POST /api/patient/:pid/encounter/:eid/soap_note

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/api/patient/1/encounter/1/soap_note' -d \
'{
    "subjective": "...",
    "objective": "...",
    "assessment": "...",
    "plan": "..."
}'
```

#### PUT /api/patient/:pid/encounter/:eid/soap_note/:sid

```
curl -X PUT 'http://localhost:8300/rest_router.php?resource=/api/patient/1/encounter/1/soap_note/:sid' -d \
'{
    "subjective": "...",
    "objective": "...",
    "assessment": "...",
    "plan": "..."
}'
```

#### GET /api/patient/:pid/encounter/:eid/soap_note

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/encounter/1/soap_note'
```

#### GET /api/patient/:pid/encounter/:eid/soap_note/:sid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/encounter/1/soap_note/1'
```

#### POST /api/patient/:pid/medical_problem

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/api/patient/1/medical_problem' -d \
'{
    "title": "Dermatochalasis",
    "begdate": "2010-04-13",
    "enddate": null,
    "diagnosis": "ICD10:H02.839"
}'
```

#### PUT /api/patient/:pid/medical_problem/:mid

```
curl -X PUT 'http://localhost:8300/rest_router.php?resource=/api/patient/1/medical_problem/1' -d \
'{
    "title": "Dermatochalasis",
    "begdate": "2010-04-13",
    "enddate": "2018-03-12",
    "diagnosis": "ICD10:H02.839"
}'
```

#### GET /api/patient/:pid/medical_problem

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/medical_problem'
```

#### GET /api/patient/:pid/medical_problem/:mid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/medical_problem/1'
```

#### POST /api/patient/:pid/allergy

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/api/patient/1/allergy' -d \
'{
    "title": "Iodine",
    "begdate": "2010-10-13",
    "enddate": null
}'
```

#### PUT /api/patient/:pid/allergy/:aid

```
curl -X PUT 'http://localhost:8300/rest_router.php?resource=/api/patient/1/allergy/1' -d \
'{
    "title": "Iodine",
    "begdate": "2012-10-13",
    "enddate": null
}'
```

#### GET /api/patient/:pid/allergy

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/allergy'
```

#### GET /api/patient/:pid/allergy/:aid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/allergy/1'
```

#### POST /api/patient/:pid/medication

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/api/patient/1/medication' -d \
'{
    "title": "Norvasc",
    "begdate": "2013-10-13",
    "enddate": null
}'
```

#### PUT /api/patient/:pid/medication/:mid

```
curl -X PUT 'http://localhost:8300/rest_router.php?resource=/api/patient/1/medication/1' -d \
'{
    "title": "Norvasc",
    "begdate": "2013-04-13",
    "enddate": null
}'
```

#### GET /api/patient/:pid/medication

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/medication'
```

#### GET /api/patient/:pid/medication/:mid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/medication/1'
```

#### POST /api/patient/:pid/surgery

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/api/patient/1/surgery' -d \
'{
    "title": "Blepharoplasty",
    "begdate": "2013-10-13",
    "enddate": null,
    "diagnosis": "CPT4:15823-50"
}'
```

#### PUT /api/patient/:pid/surgery/:sid

```
curl -X PUT 'http://localhost:8300/rest_router.php?resource=/api/patient/1/surgery/1' -d \
'{
    "title": "Blepharoplasty",
    "begdate": "2013-10-14",
    "enddate": null,
    "diagnosis": "CPT4:15823-50"
}'
```

#### GET /api/patient/:pid/surgery

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/surgery'
```

#### GET /api/patient/:pid/surgery/:sid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/surgery/1'
```

#### POST /api/patient/:pid/dental_issue

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/api/patient/1/dental_issue' -d \
'{
    "title": "Halitosis",
    "begdate": "2015-03-17",
    "enddate": null
}'
```

#### PUT /api/patient/:pid/dental_issue/:did

```
curl -X PUT 'http://localhost:8300/rest_router.php?resource=/api/patient/1/dental_issue/1' -d \
'{
    "title": "Halitosis",
    "begdate": "2015-03-17",
    "enddate": "2018-03-20"
}'
```

#### GET /api/patient/:pid/dental_issue

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/dental_issue'
```

#### GET /api/patient/:pid/dental_issue/:did

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/dental_issue/1'
```

#### GET /api/list/:list_name

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/list/medical_problem_issue_list'
```

#### GET /api/version

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/version'
```

#### GET /api/product

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/product'
```

#### GET /api/insurance_company

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/insurance_company'
```

#### GET /api/appointment

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/appointment'
```

#### GET /api/appointment/:eid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/appointment/1'
```

#### GET /api/patient/:pid/appointment

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/appointment'
```

#### GET /api/patient/:pid/appointment/:eid

```
curl -X GET 'http://localhost:8300/rest_router.php?resource=/api/patient/1/appointment/1'
```

#### POST /api/patient/:pid/appointment

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/api/patient/1/appointment' -d \
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
    "pc_billing_facility": "10"
}'
```

#### DELETE /api/patient/:pid/appointment/:eid

```
curl -X DELETE 'http://localhost:8300/rest_router.php?resource=/api/patient/1/appointment/1' -d \
```


### Dev Notes

- For business logic, make or use the services [here](https://github.com/GoTeamEpsilon/openemr-rest-api/tree/master/services)
- For controller logic, make or use the classes [here](https://github.com/GoTeamEpsilon/openemr-rest-api/tree/master/rest_controllers)
- For routing declarations, use the class [here](https://github.com/GoTeamEpsilon/openemr-rest-api/blob/master/rest_router.php).


### Project Management

- TODO(team): Consider using Symfony's router
- TODO(sherwin): Encounter POST
- TODO(matthew): Validation for SOAP and vitals
- TODO(matthew): Fix authorization piece
- TODO(matthew): Implement Particle's `optional` validation logic for all current validators
- TODO(matthew): "Delete" functions for medical problems, allergies, etc
- TODO(?): API for patient documents
- TODO(?): API for onotes
- TODO(?): Prevent `ListService` from using `enddate` of `0000-00-00` by default
- TODO(?): `PatientService`'s `insert` doesn't handle `dob` correctly
- TODO(?): Patient PUT
- TODO(?): insurance company PUT/POST
- TODO(?): API for pharmacies
- TODO(?): API for fee sheets
- TODO(?): API for prescriptions
- TODO(?): API for messages


### What is that dog drawing?

That is Peppy, an old OpenEMR mascot. Long live Peppy!


### License

[GNU GPL](LICENSE)