# OpenEMR Authorization System

This document describes the dual-layer authorization system combining OAuth2 scopes and GACL permissions.

## Overview

OpenEMR uses **two independent authorization layers** that must both pass:

| Layer         | Type              | Purpose                          | Override Possible |
|---------------|-------------------|----------------------------------|-------------------|
| OAuth2 Scopes | Token-based       | API-level granular permissions   | No                |
| GACL/ACL      | Role-based        | User role permissions            | Yes (superuser)   |

## Authorization Flow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              HTTP REQUEST                                    │
│                    Authorization: Bearer <access_token>                      │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ PHASE 1: Authentication (AuthorizationListener.onKernelRequest)             │
│ ├─ Validate JWT token (signature, expiration, issuer)                       │
│ ├─ Check token not revoked in database                                      │
│ ├─ Verify trusted user relationship exists                                   │
│ ├─ Extract user identity and role                                           │
│ ├─ Store scopes in request object                                           │
│ └─ Set session variables                                                    │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ PHASE 2: Scope Check (AuthorizationListener.onRestApiSecurityCheck)         │
│ ├─ Skip if local API (APICSRFTOKEN header)                                  │
│ ├─ Verify user role matches request context                                 │
│ │   (patient scope → patient role, user scope → user role)                  │
│ ├─ Build required scope: {context}/{resource}.{permission}                  │
│ │   Example: user/Patient.read                                              │
│ └─ Verify token contains required scope                                     │
│     └─ If missing: throw AccessDeniedException (403)                        │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ PHASE 3: ACL Check (Controller - OPTIONAL)                                  │
│ ├─ Called by controller: RestConfig::request_authorization_check()          │
│ ├─ Checks GACL: AclMain::aclCheckCore($section, $value, $user, $permission) │
│ ├─ Superuser (admin/super) bypasses all ACL checks                          │
│ └─ If denied: throw AccessDeniedException (403)                             │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                            CONTROLLER EXECUTION                              │
│                         (Both checks passed)                                 │
└─────────────────────────────────────────────────────────────────────────────┘
```

## OAuth2 Scopes

### Scope Format

```
{context}/{resource}.{permission}
```

| Component    | Values                          | Example              |
|--------------|---------------------------------|----------------------|
| `context`    | `user`, `patient`, `system`     | `user`               |
| `resource`   | FHIR or API resource name       | `Patient`            |
| `permission` | `r`, `s`, `c`, `u`, `d`, `read`, `write` | `read`      |

### Permission Flags (V2 Scopes)

| Flag | Meaning | Operations        |
|------|---------|-------------------|
| `c`  | Create  | POST              |
| `r`  | Read    | GET (single)      |
| `u`  | Update  | PUT/PATCH         |
| `d`  | Delete  | DELETE            |
| `s`  | Search  | GET (collection)  |

**Order:** Flags must be in order: `c` → `r` → `u` → `d` → `s`

### Examples

| Scope                      | Meaning                              |
|----------------------------|--------------------------------------|
| `user/Patient.read`        | User can read patient resources      |
| `patient/Observation.rs`   | Patient can read/search observations |
| `system/Patient.$export`   | System can bulk export patients      |
| `user/allergy.cruds`       | User has full CRUD on allergies      |

### Scope Checking Code

```php
// In AuthorizationListener::onRestApiSecurityCheck()
$scope = $scopeType . '/' . $resource . '.' . $permission;
$scopeEntity = ScopeEntity::createFromString($scope);

if (!$restRequest->requestHasScopeEntity($scopeEntity)) {
    throw new AccessDeniedException($scopeType, $resource, "scope not in access token");
}
```

## GACL/ACL System

### ACL Structure

ACL permissions are defined as `section/value` pairs:

| Section       | Values                                                    |
|---------------|-----------------------------------------------------------|
| `admin`       | `super`, `users`, `calendar`, `database`, `forms`, etc.   |
| `patients`    | `appt`, `demo`, `med`, `trans`, `docs`, `notes`, `rx`     |
| `encounters`  | `auth`, `coding`, `notes`, `date_a`                       |
| `acct`        | `bill`, `disc`, `eob`, `rep`                              |
| `lists`       | `default`, `state`, `country`, `language`                 |

### Return Values (Permission Levels)

| Value     | Meaning                              |
|-----------|--------------------------------------|
| `write`   | Full read/write access               |
| `view`    | Read-only access                     |
| `addonly` | Can add but not modify               |
| `wsome`   | Partial write access                 |

### ACL Checking Code

```php
// In route controller
RestConfig::request_authorization_check($request, 'patients', 'med', 'write');

// Which calls:
AclMain::aclCheckCore('patients', 'med', $username, 'write');
```

### Superuser Bypass

Users with `admin/super` ACL automatically pass ALL other ACL checks:

```php
// In AclMain::aclCheckCore()
if ($section !== 'admin' || $value !== 'super') {
    if (self::aclCheckCore('admin', 'super', $user)) {
        return true; // Superuser bypass
    }
}
```

## Scope vs ACL Comparison

| Aspect              | OAuth2 Scopes                    | GACL/ACL                        |
|---------------------|----------------------------------|----------------------------------|
| **Defined at**      | Token grant time                 | User/group assignment            |
| **Stored in**       | JWT token claims                 | Database (gacl_* tables)         |
| **Checked by**      | AuthorizationListener            | Controller (manual call)         |
| **Granularity**     | Resource + operation level       | Section + value + return level   |
| **Override**        | Cannot be elevated               | Superuser can bypass             |
| **Scope**           | Per-token                        | Per-user session                 |
| **Dynamic**         | Fixed at token creation          | Can change live                  |

## Local API Bypass

Internal API calls with `APICSRFTOKEN` header bypass scope checks:

```php
// In AuthorizationListener::onRestApiSecurityCheck()
if ($restRequest->attributes->get('skipAuthorization', false) === true) {
    return $event; // No scope validation
}
```

**Note:** ACL checks in controllers still apply to local API calls.

## Authorization Methods Reference

### RestConfig::scope_check()

```php
public static function scope_check($scopeType, $resource = null, $permission = null): void
```

Verifies OAuth2 token contains required scope.

**Parameters:**

| Parameter    | Description                           |
|--------------|---------------------------------------|
| `$scopeType` | 'user', 'patient', 'system', 'openid' |
| `$resource`  | Resource name (Patient, etc.)         |
| `$permission`| Permission (read, write, etc.)        |

**Throws:** `AccessDeniedException` if scope missing

### RestConfig::authorization_check()

```php
public static function authorization_check($section, $value, $user = '', $aclPermission = ''): void
```

Verifies user has GACL permission.

**Parameters:**

| Parameter        | Description                         |
|------------------|-------------------------------------|
| `$section`       | ACO section (admin, patients, etc.) |
| `$value`         | ACO value (super, med, etc.)        |
| `$user`          | Username (defaults to session)      |
| `$aclPermission` | Required level (view, write, etc.)  |

**Throws:** `AccessDeniedException` if ACL denied

### RestConfig::request_authorization_check()

```php
public static function request_authorization_check(
    HttpRestRequest $request,
    $section,
    $value,
    $aclPermission = ''
): void
```

Convenience wrapper extracting user from request session.

## Dual Authorization Examples

### Example 1: Both Pass

```
Token scopes: [user/Patient.write]
User ACL: patients/demo → write

Request: PUT /api/patient/123
Scope check: user/Patient.write → PASS
ACL check: patients/demo write → PASS
Result: 200 OK
```

### Example 2: Scope Fails

```
Token scopes: [user/Patient.read]
User ACL: patients/demo → write

Request: PUT /api/patient/123
Scope check: user/Patient.write → FAIL (only has .read)
Result: 403 Forbidden
```

### Example 3: ACL Fails

```
Token scopes: [user/Patient.write]
User ACL: patients/demo → view

Request: PUT /api/patient/123
Scope check: user/Patient.write → PASS
ACL check: patients/demo write → FAIL (only has view)
Result: 403 Forbidden
```

### Example 4: Superuser Override

```
Token scopes: [user/Patient.write]
User ACL: admin/super → YES (no patients/demo explicitly)

Request: PUT /api/patient/123
Scope check: user/Patient.write → PASS
ACL check: patients/demo write → PASS (superuser bypass)
Result: 200 OK
```

### Example 5: Local API

```
Headers: APICSRFTOKEN: valid-csrf-token
User ACL: patients/demo → write

Request: PUT /api/patient/123
Scope check: SKIPPED (local API)
ACL check: patients/demo write → PASS
Result: 200 OK
```

## User Roles

| Role      | Context   | Typical Scopes           | ACL Access             |
|-----------|-----------|--------------------------|------------------------|
| `users`   | `user`    | `user/Patient.*`         | Based on group         |
| `patient` | `patient` | `patient/Patient.read`   | Own data only          |
| `system`  | `system`  | `system/*`               | System user ACL        |

## Key Files Reference

| File                                              | Purpose                       |
|---------------------------------------------------|-------------------------------|
| `src/RestControllers/Subscriber/AuthorizationListener.php` | Dual-phase auth        |
| `src/RestControllers/Config/RestConfig.php`       | scope_check, authorization_check |
| `src/Common/Acl/AclMain.php`                      | GACL checking logic           |
| `src/Common/Auth/OpenIDConnect/Entities/ScopeEntity.php` | Scope parsing          |
| `src/Gacl/Gacl.php`                               | GACL query engine             |
