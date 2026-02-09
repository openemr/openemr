# OpenEMR API Documentation Index

This index provides an overview of all API, authorization, and ACL documentation.

## Documentation Structure

```
API_DOCUMENTATION_INDEX.md     ← You are here
│
├── API_ARCHITECTURE.md        ← Request lifecycle, routing, middleware
├── API_AUTHENTICATION.md      ← OAuth2 tokens, grants, validation
├── API_AUTHORIZATION.md       ← Scopes + ACL dual-layer system
├── API_GACL_INTERNALS.md      ← GACL nested set, query algorithm
├── API_INSTALLATION.md        ← Database setup, fixtures, migrations
├── API_SCOPES.md              ← All OAuth2 scopes with ACL mappings
├── API_STANDARD_ACL.md        ← ACL API endpoints (existing + proposed)
└── API_IMPROVEMENTS.md        ← Analysis and improvement suggestions
```

## Quick Reference

### How Requests Flow

```
HTTP Request → dispatch.php → SiteSetupListener → AuthorizationListener
    → RoutesExtensionListener → HttpRestRouteHandler → Controller → Response
```

See: [API_ARCHITECTURE.md](API_ARCHITECTURE.md)

### How Authentication Works

```
Client → Authorization Code Grant → Access Token (JWT) → API Request
         ↓
    Password Grant / Client Credentials / Refresh Token (alternatives)
```

See: [API_AUTHENTICATION.md](API_AUTHENTICATION.md)

### How Authorization Works

```
Request with Token
    ↓
Phase 1: Token Validation (JWT signature, expiration, revocation)
    ↓
Phase 2: Scope Check (token contains required scope)
    ↓
Phase 3: ACL Check (user role has permission) [Optional, in controller]
    ↓
Access Granted (both must pass)
```

See: [API_AUTHORIZATION.md](API_AUTHORIZATION.md)

### How GACL Evaluates Permissions

```
acl_check(section, value, user)
    ↓
Get user's groups (nested set query)
    ↓
Find matching ACL rules (ACO + ARO/group match)
    ↓
Order by specificity (direct > group, smaller group > larger)
    ↓
Return first match (allow/deny + return_value)
```

See: [API_GACL_INTERNALS.md](API_GACL_INTERNALS.md)

### How Tables Are Initialized

```
Installation
    ↓
database.sql (schema) → Installer::install_gacl() (data)
    ↓
13 ACO sections, 76 ACOs, 7 groups, 27+ ACL rules
```

See: [API_INSTALLATION.md](API_INSTALLATION.md)

## Key Concepts

### Two-Dimensional ACL

| Dimension | Term | Meaning        | Example                    |
|-----------|------|----------------|----------------------------|
| WHO       | ARO  | Users/Groups   | `admin`, `Physicians`      |
| WHAT      | ACO  | Permissions    | `admin/super`, `patients/med` |

### Scope Format

```
{context}/{resource}.{permission}

Examples:
  user/Patient.read      ← User can read Patient
  patient/Observation.rs ← Patient can read+search Observations
  system/Patient.$export ← System can bulk export
```

### Permission Levels

| Level     | Meaning                       |
|-----------|-------------------------------|
| `write`   | Full read/write               |
| `view`    | Read-only                     |
| `addonly` | Create only, no modify        |
| `wsome`   | Partial write                 |

### Default Groups

| ID | Value   | Name             |
|----|---------|------------------|
| 10 | users   | OpenEMR Users    |
| 11 | admin   | Administrators   |
| 12 | clin    | Clinicians       |
| 13 | doc     | Physicians       |
| 14 | front   | Front Office     |
| 15 | back    | Accounting       |
| 16 | breakglass | Emergency Login |

## Common Tasks

### Check if User Has Permission

```php
// In controller
RestConfig::request_authorization_check($request, 'patients', 'med', 'write');

// Direct GACL call
AclMain::aclCheckCore('patients', 'med', $username, 'write');
```

### Add New Permission

1. Add ACO section (if new): `$gacl->add_object_section(...)`
2. Add ACO: `$gacl->add_object(...)`
3. Create ACL rule: `$gacl->add_acl(...)`
4. Add to `acl_upgrade.php` for upgrades

### Add User to Group

```php
$gacl->add_group_object($group_id, 'users', $username, 'ARO');
```

### Define New Scope

1. Add to `ServerScopeListEntity.php`
2. Add to client's allowed scopes
3. Document in `API_SCOPES.md`

### Add New API Endpoint

1. Create route in `apis/routes/standard/...`
2. Create controller method
3. Add scope check (automatic via middleware)
4. Add ACL check (manual in controller)
5. Document endpoint

## Key Files

| File                                        | Purpose                     |
|---------------------------------------------|-----------------------------|
| `apis/dispatch.php`                         | API entry point             |
| `src/RestControllers/ApiApplication.php`    | Request orchestration       |
| `src/RestControllers/Subscriber/*.php`      | Middleware listeners        |
| `src/RestControllers/Config/RestConfig.php` | Auth utilities              |
| `src/Common/Acl/AclMain.php`                | ACL checking                |
| `src/Gacl/Gacl.php`                         | GACL query engine           |
| `src/Gacl/GaclApi.php`                      | GACL management             |
| `src/Common/Auth/OpenIDConnect/*.php`       | OAuth2 implementation       |
| `library/classes/Installer.class.php`       | ACL installation            |
| `sql/database.sql`                          | Schema definitions          |

## Database Tables

### GACL Tables (13)

| Table                  | Purpose                    |
|------------------------|----------------------------|
| `gacl_acl`             | ACL rules                  |
| `gacl_aco`             | Access Control Objects     |
| `gacl_aco_sections`    | ACO categories             |
| `gacl_aro`             | Users                      |
| `gacl_aro_groups`      | User groups                |
| `gacl_aco_map`         | ACL → ACO                  |
| `gacl_aro_map`         | ACL → User                 |
| `gacl_aro_groups_map`  | ACL → Group                |
| `gacl_groups_aro_map`  | User → Group               |

### OAuth2 Tables (4)

| Table                | Purpose                     |
|----------------------|-----------------------------|
| `oauth_clients`      | Registered clients          |
| `oauth_trusted_user` | User-client trust           |
| `api_token`          | Access tokens               |
| `api_refresh_token`  | Refresh tokens              |

## API Types

| Type     | Path       | Route Finder        | Purpose              |
|----------|------------|---------------------|----------------------|
| Standard | `/api/`    | StandardRouteFinder | OpenEMR native API   |
| FHIR     | `/fhir/`   | FhirRouteFinder     | FHIR R4 US Core      |
| Portal   | `/portal/` | PortalRouteFinder   | Patient Portal       |

## See Also

- `CONTRIBUTING.md` - Development guidelines
- `API_README.md` - General API documentation
- `FHIR_README.md` - FHIR implementation details
- `tests/Tests/README.md` - Testing guide
