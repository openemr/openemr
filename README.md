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

### Current Endpoints

```
GET /facility
GET /facility/:fid
POST /facility
PUT /facility/:fid
GET /provider
GET /provider/:prid
POST /provider
GET /patient
POST /patient
GET /patient/:pid
GET /patient/:pid/encounter
GET /patient/:pid/medical_problem
GET /patient/:pid/medical_problem/:mid
GET /patient/:pid/allergy
GET /patient/:pid/allergy/:aid
GET /patient/:pid/medication
GET /patient/:pid/medication/:mid
GET /patient/:pid/surgery
GET /patient/:pid/surgery/:sid
GET /patient/:pid/dental_issue
GET /patient/:pid/dental_issue/:did
GET /patient/:pid/encounter/:eid
GET /version
GET /product
```

### Calling the API

The API is invoked with a `resource` query string to define the path: `http://localhost:8300/rest_router.php?resource=/patient`. For example:

```
curl -X POST 'http://localhost:8300/rest_router.php?resource=/facility' -d \
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

### Dev Notes

- Use cURL or [Postman](https://www.getpostman.com/)
- For business logic, make or use the services [here](https://github.com/GoTeamEpsilon/openemr-rest-api/tree/master/services)
- For controller logic, make or use the classes [here](https://github.com/GoTeamEpsilon/openemr-rest-api/tree/master/rest_controllers)
- For routing declarations, use the class [here](https://github.com/GoTeamEpsilon/openemr-rest-api/blob/master/rest_router.php).

### Project Management

- TODO(team): Consider using Symfony's router
- TODO(matthew): Implement Particle's `optional` validation logic for all current validators
- TODO(matthew): API for medications
- TODO(matthew): API for problem list
- TODO(matthew): API for allergies
- TODO(matthew): API for surgeries
- TODO(matthew): API for dental issues
- TODO(?): API for onotes
- TODO(?): API for SOAP notes
- TODO(?): API for vitals
- TODO(?): API for appointments
- TODO(?): API for insurance companies
- TODO(?): API for fee sheets
- TODO(?): API for patient documents
- TODO(?): API for prescriptions
- TODO(?): API for messages
- TODO(?): Implement token-based authentication

### What is that dog drawing?

That is Peppy, an old OpenEMR mascot. Long live Peppy!

### License

[GNU GPL](LICENSE)