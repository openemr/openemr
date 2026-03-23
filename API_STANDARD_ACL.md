# OpenEMR Standard ACL API

This document describes the REST API endpoints for managing Access Control Lists (ACL) in OpenEMR.

## Overview

OpenEMR uses GACL (Generic Access Control List) with a **2-dimensional model**:

| Dimension | GACL Term                    | Meaning          | DB Table                         | Example                          |
|-----------|------------------------------|------------------|----------------------------------|----------------------------------|
| WHO       | ARO (Access Request Object)  | Users and Groups | `gacl_aro`, `gacl_aro_groups`    | `admin`, `Physicians`            |
| WHAT      | ACO (Access Control Object)  | Permissions      | `gacl_aco`, `gacl_aco_sections`  | `admin/super`, `patients/med`    |

**Note:** AXO (Access eXtension Object) - the 3rd dimension - is NOT used in OpenEMR.

### ACO Structure

An ACO (permission) consists of two parts:
- **section** - the grouping/category (e.g., `admin`, `patients`, `acct`) - stored in `gacl_aco_sections.value`
- **value** - the specific permission (e.g., `super`, `med`, `bill`) - stored in `gacl_aco.value`

Written as: `section/value` (e.g., `admin/super`, `patients/med`)

### Permission Levels (Return Values)

| Level     | Description                                    |
|-----------|------------------------------------------------|
| `write`   | Full read/write access                         |
| `view`    | Read-only access                               |
| `addonly` | Can add new records, but not modify existing   |
| `wsome`   | Partial write access                           |

---

## Existing Endpoints

### ACL Groups (User Groups)

Manage user groups for role-based access control.

| Method | Path                     | Description          | DB Tables          |
|--------|--------------------------|----------------------|--------------------|
| GET    | `/api/admin/acl/group`     | List all ACL groups  | `gacl_aro_groups`  |
| GET    | `/api/admin/acl/group/:id` | Get group by ID      | `gacl_aro_groups`  |
| POST   | `/api/admin/acl/group`     | Create new group     | `gacl_aro_groups`  |
| DELETE | `/api/admin/acl/group/:id` | Delete group         | `gacl_aro_groups`  |

**Request Body (POST):**
```json
{
  "parent_id": 10,
  "name": "Nurses",
  "value": "nurses"
}
```

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": {
    "id": 15,
    "parent_id": 10,
    "name": "Nurses",
    "value": "nurses"
  }
}
```

### ACL Group Members

Manage which users belong to which groups.

| Method | Path                                         | Description              | DB Tables                      |
|--------|----------------------------------------------|--------------------------|--------------------------------|
| GET    | `/api/admin/acl/group/:groupId/member`       | List members of a group  | `gacl_groups_aro_map` + `users`|
| POST   | `/api/admin/acl/group/:groupId/member/:uuid` | Add user to group        | `gacl_groups_aro_map`          |
| DELETE | `/api/admin/acl/group/:groupId/member/:uuid` | Remove user from group   | `gacl_groups_aro_map`          |

**Request Body (POST) - Optional:**
```json
{
  "order": 0,
  "hidden": false
}
```

**Response (GET):**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": 1,
      "uuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
      "fname": "Administrator",
      "mname": "",
      "lname": "Administrator",
      "email": "admin@example.com",
      "username": "admin"
    }
  ]
}
```

### ACL Sections (Module-based)

List available ACL sections (used for module-based permissions, not GACL ACO sections).

| Method | Path                     | Description                   | DB Tables              |
|--------|--------------------------|-------------------------------|------------------------|
| GET    | `/api/admin/acl/section` | List all module ACL sections  | `module_acl_sections`  |

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "parent_section": 0,
      "section_id": 1,
      "section_identifier": "immunization",
      "section_name": "Immunization",
      "module_id": 0
    }
  ]
}
```

### ACL Group Settings (Module-based)

View module ACL settings configured at the group level.

| Method | Path                                   | Description                    | DB Tables                    |
|--------|----------------------------------------|--------------------------------|------------------------------|
| GET    | `/api/admin/acl/group/setting`           | List all group ACL settings    | `module_acl_group_settings`  |
| GET    | `/api/admin/acl/group/setting/:sectionId`| Get group settings by section  | `module_acl_group_settings`  |

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "group_id": 11,
      "section_id": 1,
      "allowed": 1
    }
  ]
}
```

### ACL User Settings (Module-based)

View module ACL settings configured at the user level.

| Method | Path                                   | Description                   | DB Tables                   |
|--------|----------------------------------------|-------------------------------|-----------------------------|
| GET    | `/api/admin/acl/user/setting`            | List all user ACL settings    | `module_acl_user_settings`  |
| GET    | `/api/admin/acl/user/setting/:sectionId` | Get user settings by section  | `module_acl_user_settings`  |

**Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "user_id": 1,
      "section_id": 1,
      "allowed": 1
    }
  ]
}
```

---

## Proposed New Endpoints

### ACO Catalog (Available Permissions)

List available ACO sections and values that can be granted.

| Method | Path                                         | Description                        | DB Tables                          | GACL Concept    |
|--------|----------------------------------------------|------------------------------------|------------------------------------|-----------------|
| GET    | `/api/admin/acl/aco`                         | List all ACOs (grouped by section) | `gacl_aco_sections` + `gacl_aco`   | All ACOs        |
| GET    | `/api/admin/acl/aco/section`                 | List all ACO sections              | `gacl_aco_sections`                | ACO Sections    |
| GET    | `/api/admin/acl/aco/section/:section/values` | List ACO values in a section       | `gacl_aco`                         | ACOs by section |

**Response (GET /api/admin/acl/aco):**
```json
{
  "data": {
    "admin": [
      { "value": "super", "name": "Superuser" },
      { "value": "users", "name": "Users/Groups/Logs Administration" }
    ],
    "patients": [
      { "value": "appt", "name": "Appointments" },
      { "value": "demo", "name": "Demographics" },
      { "value": "med", "name": "Medical Records" }
    ]
  }
}
```

**Response (GET /api/admin/acl/aco/section):**
```json
{
  "data": [
    { "value": "admin", "name": "Administration", "order": 10 },
    { "value": "patients", "name": "Patient Information", "order": 20 },
    { "value": "encounters", "name": "Encounter Information", "order": 30 },
    { "value": "acct", "name": "Accounting", "order": 40 },
    { "value": "inventory", "name": "Inventory", "order": 50 }
  ]
}
```

**Response (GET /api/admin/acl/aco/section/admin/values):**
```json
{
  "data": [
    { "section": "admin", "value": "super", "name": "Superuser", "order": 10 },
    { "section": "admin", "value": "users", "name": "Users/Groups/Logs Administration", "order": 20 },
    { "section": "admin", "value": "calendar", "name": "Calendar Settings", "order": 30 },
    { "section": "admin", "value": "database", "name": "Database Administration", "order": 40 }
  ]
}
```

### Group Permission Management

Manage GACL permissions for groups.

| Method | Path                                | Description                          | DB Tables                                            | GACL Concept            |
|--------|-------------------------------------|--------------------------------------|------------------------------------------------------|-------------------------|
| GET    | `/api/admin/acl/group/:id/permission` | Get all permissions for a group      | `gacl_acl` + `gacl_aco_map` + `gacl_aro_groups_map`  | ACL rules for ARO group |
| PUT    | `/api/admin/acl/group/:id/permission` | Replace all permissions for a group  | `gacl_acl` + `gacl_aco_map` + `gacl_aro_groups_map`  | Recreate ACL rules      |
| PATCH  | `/api/admin/acl/group/:id/permission` | Update specific permissions          | `gacl_acl` + `gacl_aco_map`                          | Modify ACL rules        |

**Response (GET /api/admin/acl/group/11/permission):**
```json
{
  "data": {
    "groupId": 11,
    "groupName": "Administrators",
    "groupValue": "admin",
    "permissions": [
      { "section": "admin", "value": "super", "returnValue": "write" },
      { "section": "patients", "value": "appt", "returnValue": "write" },
      { "section": "patients", "value": "demo", "returnValue": "write" },
      { "section": "patients", "value": "med", "returnValue": "write" },
      { "section": "acct", "value": "bill", "returnValue": "view" }
    ]
  }
}
```

**Request Body (PUT /api/admin/acl/group/:id/permission):**
```json
{
  "permissions": [
    { "section": "admin", "value": "calendar", "returnValue": "write" },
    { "section": "patients", "value": "appt", "returnValue": "write" },
    { "section": "patients", "value": "demo", "returnValue": "view" }
  ]
}
```

**Request Body (PATCH /api/admin/acl/group/:id/permission):**
```json
{
  "grant": [
    { "section": "patients", "value": "med", "returnValue": "write" }
  ],
  "revoke": [
    { "section": "acct", "value": "bill" }
  ]
}
```

### User Permission Management

Manage and view permissions for individual users.

| Method | Path                                  | Description                                        | DB Tables                                       | GACL Concept             |
|--------|---------------------------------------|----------------------------------------------------|-------------------------------------------------|--------------------------|
| GET    | `/api/admin/acl/user/:uuid/permission`  | Get user's effective permissions (inherited+direct)| `gacl_acl` + `gacl_aro` + `gacl_groups_aro_map` | Resolved ARO permissions |
| PUT    | `/api/admin/acl/user/:uuid/permission`  | Set user's direct permissions                      | `gacl_acl` + `gacl_aro_map`                     | ACL rules for ARO        |

**Response (GET /api/admin/acl/user/:uuid/permission):**
```json
{
  "data": {
    "userUuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
    "username": "admin",
    "groups": ["Administrators", "Physicians"],
    "permissions": [
      { "section": "admin", "value": "super", "returnValue": "write", "source": "group:Administrators" },
      { "section": "patients", "value": "med", "returnValue": "write", "source": "group:Physicians" },
      { "section": "patients", "value": "notes", "returnValue": "view", "source": "direct" }
    ]
  }
}
```

### Permission Check (Diagnostic)

Check if a user or group has a specific permission. Useful for debugging and validation.

| Method | Path                                        | Description                           | DB Tables           | GACL Concept   |
|--------|---------------------------------------------|---------------------------------------|---------------------|----------------|
| POST   | `/api/admin/acl/user/:uuid/permission/check`  | Check if user has specific permission | `gacl_acl` (query)  | `acl_check()`  |
| POST   | `/api/admin/acl/group/:id/permission/check`   | Check if group has specific permission| `gacl_acl` (query)  | `acl_check()`  |

**Request Body:**
```json
{
  "section": "patients",
  "value": "med",
  "returnValue": "write"
}
```

**Response:**
```json
{
  "data": {
    "allowed": true,
    "effectiveReturnValue": "write",
    "source": "group:Administrators"
  }
}
```

---

## Database Tables Reference

### GACL Core Tables

| Table               | Description                                                      |
|---------------------|------------------------------------------------------------------|
| `gacl_acl`          | ACL rules (id, section_value, allow, enabled, return_value, note)|
| `gacl_acl_sections` | ACL section organization                                         |

### ARO Tables (Users/Groups - WHO)

| Table                | Description                                                  |
|----------------------|--------------------------------------------------------------|
| `gacl_aro`           | Individual users (id, section_value, value, name)            |
| `gacl_aro_sections`  | ARO sections (typically just "users")                        |
| `gacl_aro_groups`    | User groups with hierarchy (id, parent_id, value, name, lft, rgt) |
| `gacl_aro_map`       | Links users directly to ACL rules                            |
| `gacl_aro_groups_map`| Links user groups to ACL rules                               |
| `gacl_groups_aro_map`| Maps users to groups                                         |

### ACO Tables (Permissions - WHAT)

| Table               | Description                                        |
|---------------------|----------------------------------------------------|
| `gacl_aco`          | Individual permissions (id, section_value, value, name) |
| `gacl_aco_sections` | Permission sections (admin, patients, acct, etc.)  |
| `gacl_aco_map`      | Links permissions to ACL rules                     |

### Module ACL Tables

| Table                       | Description                        |
|-----------------------------|------------------------------------|
| `module_acl_sections`       | Module-based ACL sections          |
| `module_acl_group_settings` | Group permissions for modules      |
| `module_acl_user_settings`  | User permissions for modules       |

---

## Standard ACO Sections

| Section (`gacl_aco_sections.value`) | Description           | Values (`gacl_aco.value`)                                    |
|-------------------------------------|-----------------------|--------------------------------------------------------------|
| `admin`                             | Administration        | `super`, `users`, `calendar`, `database`, `forms`, `practice`|
| `acct`                              | Accounting            | `bill`, `disc`, `eob`, `rep`, `rep_a`                        |
| `patients`                          | Patient Information   | `appt`, `demo`, `med`, `trans`, `docs`, `notes`, `rx`        |
| `encounters`                        | Encounter Information | `auth`, `auth_a`, `coding`, `coding_a`, `notes`, `notes_a`   |
| `lists`                             | Lists                 | `default`, `state`, `country`, `language`                    |
| `sensitivities`                     | Sensitivity Levels    | `normal`, `high`                                             |
| `groups`                            | Group Management      | `gadd`, `gcalendar`, `glog`, `gdlog`, `gm`                   |
| `inventory`                         | Inventory             | `lots`, `sales`, `purchases`, `transfers`, `adjustments`     |

---

## Authorization

All ACL endpoints require:
- Valid OAuth2 token or session authentication
- ACL permission: `admin/users` or `admin/groups` depending on endpoint

```
Authorization: Bearer <access_token>
```

---

## Error Responses

All endpoints return errors in standard format:

```json
{
  "validationErrors": [
    { "field": "name", "message": "Name is required" }
  ],
  "internalErrors": [
    { "message": "Database connection failed" }
  ],
  "data": []
}
```

| HTTP Status | Description                          |
|-------------|--------------------------------------|
| 200         | Success                              |
| 201         | Created (POST)                       |
| 400         | Bad Request (validation errors)      |
| 401         | Unauthorized                         |
| 403         | Forbidden (insufficient permissions) |
| 404         | Not Found                            |
| 500         | Internal Server Error                |
