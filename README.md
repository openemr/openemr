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
GET /patient/:pid/encounter/:eid
GET /version
GET /product
```

### Next Endpoints

TODO(matthew): list out needed endpoints


### Calling the API

The API is invoked with a `resource` query string to define the path: `http://localhost:8300/rest_router.php?resource=/patient`

TODO(matthew): Implement token-based authentication.


### License

[GNU GPL](LICENSE)
