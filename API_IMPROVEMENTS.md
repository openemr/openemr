# OpenEMR API/ACL Improvement Analysis

This document analyzes the current API/ACL architecture and suggests improvements.

## Current Architecture Issues

### 1. Dual Authorization Complexity

**Problem:** Two independent authorization systems (OAuth2 scopes + GACL) with no clear mapping between them.

| Issue                                    | Impact                                    |
|------------------------------------------|-------------------------------------------|
| No automatic scope-to-ACL mapping        | Developers must manually check both       |
| Different granularity levels             | Scopes are resource-level, ACL is coarser |
| Inconsistent checking locations          | Scopes in middleware, ACL in controllers  |
| Confusing for API consumers              | Hard to understand required permissions   |

**Current State:**
```
OAuth2 Scope: user/Patient.write
ACL: patients/demo → write

These are INDEPENDENT - both must pass, but there's no explicit relationship.
```

### 2. GACL Legacy Architecture

**Problem:** GACL is a 20+ year old library with outdated patterns.

| Issue                                    | Impact                                    |
|------------------------------------------|-------------------------------------------|
| Procedural code mixed with OOP           | Hard to maintain and test                 |
| Direct SQL string building               | Potential security and performance issues |
| Global state and singletons              | Difficult to unit test                    |
| Nested set model complexity              | Tree operations are error-prone           |
| File-based caching                       | Not suitable for distributed systems      |

### 3. Inconsistent ACL Checking

**Problem:** ACL checks are optional and controller-dependent.

**Examples of inconsistency:**
```php
// Some controllers check ACL
RestConfig::request_authorization_check($request, 'patients', 'med');

// Some don't check at all (rely only on scopes)
return $controller->getAll($request);

// Some check in service layer
if (!AclMain::aclCheckCore('admin', 'super')) { ... }
```

### 4. Scope Definition Duplication

**Problem:** Scopes defined in multiple places with no single source of truth.

| Location                                | Content                      |
|-----------------------------------------|------------------------------|
| `ServerScopeListEntity.php`             | Scope string lists           |
| Route files                             | Required scopes per endpoint |
| `API_SCOPES.md`                         | Documentation                |
| Client registration                     | Allowed scopes per client    |

### 5. Module ACL Separate System

**Problem:** Module ACL uses different tables and patterns than GACL.

| System      | Tables                            | Pattern           |
|-------------|-----------------------------------|-------------------|
| GACL        | `gacl_*` (13 tables)              | Complex queries   |
| Module ACL  | `module_acl_*` (3 tables)         | Simple lookups    |

No integration between systems; modules can't use GACL groups.

### 6. Missing API Features

| Feature                      | Current State           | Need                        |
|------------------------------|-------------------------|-----------------------------|
| Rate limiting                | Not implemented         | Prevent abuse               |
| API versioning               | Implicit only           | Breaking change management  |
| Pagination standards         | Inconsistent            | FHIR vs Standard differ     |
| Bulk operations              | Limited to FHIR export  | Standard API needs bulk     |
| Webhook/events               | Not exposed via API     | Real-time integrations      |
| API key authentication       | Not available           | Simple integrations         |

---

## Improvement Suggestions

### 1. Unified Permission Model

**Goal:** Single source of truth for permissions with automatic scope-to-ACL mapping.

**Proposed Structure:**

```
Permission Definition (YAML/JSON)
    ├─ section: patients
    ├─ value: med
    ├─ fhir_resources: [Patient, Observation, Condition]
    ├─ api_resources: [patient, medical_problem, allergy]
    ├─ scopes:
    │   ├─ read: patient/Patient.read, user/patient.read
    │   └─ write: user/Patient.write, user/patient.write
    └─ levels: [view, addonly, write]
```

**Benefits:**
- Single file defines all permission relationships
- Auto-generate scope lists from definitions
- Auto-generate ACL checks from route definitions
- Self-documenting API permissions

### 2. Declarative Route Authorization

**Goal:** Define authorization requirements in route definitions, not controllers.

**Current:**
```php
'GET /api/patient/:id' => function ($id, HttpRestRequest $request) {
    RestConfig::request_authorization_check($request, 'patients', 'demo');
    return Controller::getOne($id);
}
```

**Proposed:**
```php
'GET /api/patient/:id' => [
    'controller' => [PatientController::class, 'getOne'],
    'scope' => 'user/patient.read',
    'acl' => ['patients', 'demo', 'view'],
    'params' => ['id']
]
```

**Benefits:**
- Authorization checked automatically before controller
- Route definitions are self-documenting
- Consistent enforcement across all endpoints
- Easy to audit permissions

### 3. Modern ACL Implementation

**Goal:** Replace GACL with modern RBAC/ABAC implementation.

**Proposed Architecture:**

```
┌─────────────────────────────────────────────────────────────────┐
│                      PermissionService                          │
├─────────────────────────────────────────────────────────────────┤
│ check(user, resource, action, context?): bool                   │
│ getEffectivePermissions(user): Permission[]                     │
│ grantPermission(role, permission): void                         │
│ revokePermission(role, permission): void                        │
└─────────────────────────────────────────────────────────────────┘
                              │
           ┌──────────────────┼──────────────────┐
           ▼                  ▼                  ▼
    ┌────────────┐     ┌────────────┐     ┌────────────┐
    │   Role     │     │ Permission │     │   Policy   │
    │  Service   │     │  Service   │     │   Engine   │
    └────────────┘     └────────────┘     └────────────┘
```

**Features:**
- Clean OOP with dependency injection
- Redis/database caching
- Policy-based access control (ABAC)
- Audit logging built-in
- Easy unit testing

### 4. Scope-Permission Synchronization

**Goal:** Automatically sync OAuth2 scopes with internal permissions.

**Proposed Flow:**

```
Token Request with scopes: [user/Patient.read, user/Observation.write]
    ↓
ScopeToPermissionMapper::map($scopes)
    ↓
Returns: [
    {section: 'patients', value: 'demo', level: 'view'},
    {section: 'patients', value: 'med', level: 'write'}
]
    ↓
Store in token context for request lifecycle
    ↓
PermissionService uses both user ACL AND token permissions
    ↓
Access granted only if BOTH allow
```

### 5. API Permission Registry

**Goal:** Central registry of all API permissions with metadata.

```php
class ApiPermissionRegistry
{
    public function register(string $endpoint, PermissionDefinition $def): void;
    public function getRequiredPermissions(string $endpoint): PermissionDefinition;
    public function getAllEndpoints(): array;
    public function generateOpenApiSecuritySchemes(): array;
    public function generateScopeDocumentation(): array;
}
```

**Benefits:**
- Auto-generate API documentation
- Runtime permission discovery
- Consistent permission metadata
- Easy permission auditing

### 6. Simplified ACL Tables

**Goal:** Reduce 13+ GACL tables to simpler structure.

**Current Tables (13+):**
```
gacl_acl, gacl_acl_sections, gacl_aco, gacl_aco_sections,
gacl_aro, gacl_aro_sections, gacl_aro_groups, gacl_aco_map,
gacl_aro_map, gacl_aro_groups_map, gacl_groups_aro_map, ...
```

**Proposed Tables (4):**
```sql
-- Roles (replaces gacl_aro_groups)
CREATE TABLE roles (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    parent_id INT NULL,
    description TEXT
);

-- Permissions (replaces gacl_aco + gacl_aco_sections)
CREATE TABLE permissions (
    id INT PRIMARY KEY,
    section VARCHAR(100),
    value VARCHAR(100),
    name VARCHAR(255),
    description TEXT,
    UNIQUE(section, value)
);

-- Role Permissions (replaces gacl_acl + maps)
CREATE TABLE role_permissions (
    role_id INT,
    permission_id INT,
    level ENUM('view', 'addonly', 'write'),
    PRIMARY KEY(role_id, permission_id)
);

-- User Roles (replaces gacl_groups_aro_map)
CREATE TABLE user_roles (
    user_id INT,
    role_id INT,
    PRIMARY KEY(user_id, role_id)
);
```

### 7. Caching Improvements

**Goal:** Modern distributed caching.

**Current:** File-based Cache_Lite

**Proposed:**
```php
interface PermissionCacheInterface
{
    public function get(string $key): ?array;
    public function set(string $key, array $value, int $ttl): void;
    public function invalidateUser(int $userId): void;
    public function invalidateRole(int $roleId): void;
    public function flush(): void;
}

// Implementations
class RedisPermissionCache implements PermissionCacheInterface { }
class DatabasePermissionCache implements PermissionCacheInterface { }
class InMemoryPermissionCache implements PermissionCacheInterface { }
```

### 8. API Versioning Strategy

**Goal:** Explicit API versioning for breaking changes.

**Options:**

| Strategy        | URL Example                    | Pros/Cons                    |
|-----------------|--------------------------------|------------------------------|
| URL Path        | `/api/v2/patient`              | Clear, cacheable             |
| Header          | `Accept: application/vnd.openemr.v2+json` | Clean URLs    |
| Query Param     | `/api/patient?version=2`       | Easy to test                 |

**Recommendation:** URL path versioning for clarity.

### 9. Rate Limiting

**Goal:** Protect API from abuse.

**Proposed Implementation:**

```php
class RateLimitMiddleware
{
    public function __invoke(HttpRestRequest $request, callable $next)
    {
        $key = $this->getRateLimitKey($request);
        $limit = $this->getLimit($request);

        if ($this->limiter->tooManyAttempts($key, $limit)) {
            throw new TooManyRequestsException();
        }

        $this->limiter->hit($key);
        return $next($request);
    }
}
```

**Configuration:**
```yaml
rate_limits:
    default: 1000/hour
    authenticated: 5000/hour
    admin: unlimited
    per_endpoint:
        POST /api/patient: 100/hour
        GET /api/patient: 1000/hour
```

### 10. Audit Trail Enhancement

**Goal:** Comprehensive permission audit logging.

```php
interface PermissionAuditLogger
{
    public function logCheck(
        int $userId,
        string $permission,
        bool $granted,
        string $source  // 'scope', 'acl', 'policy'
    ): void;

    public function logChange(
        int $adminUserId,
        string $action,  // 'grant', 'revoke'
        int $targetRoleOrUser,
        string $permission
    ): void;
}
```

---

## Migration Path

### Phase 1: Documentation & Mapping (No Code Changes)

1. Document all existing ACL → Scope relationships
2. Create permission registry from current state
3. Identify inconsistencies and gaps

### Phase 2: Centralized Permission Checking

1. Create PermissionService wrapper around GACL
2. Move ACL checks from controllers to middleware
3. Add permission metadata to route definitions

### Phase 3: Scope-ACL Synchronization

1. Create ScopeToPermissionMapper
2. Integrate with token validation
3. Unified permission checking in middleware

### Phase 4: Modern ACL Implementation

1. Create new simplified tables
2. Migrate data from GACL tables
3. Update PermissionService to use new tables
4. Deprecate GACL classes

### Phase 5: Advanced Features

1. Add rate limiting
2. Add API versioning
3. Add policy-based access control
4. Add comprehensive audit logging

---

## Quick Wins (Low Effort, High Value)

| Improvement                          | Effort | Impact |
|--------------------------------------|--------|--------|
| Document scope-ACL relationships     | Low    | High   |
| Move ACL checks to route definitions | Medium | High   |
| Add Redis caching for permissions    | Low    | Medium |
| Create permission audit log          | Low    | Medium |
| Standardize error responses          | Low    | Medium |

---

## Summary

The current system works but has accumulated technical debt:

| Area               | Current State          | Target State               |
|--------------------|------------------------|----------------------------|
| Authorization      | Dual, uncoordinated    | Unified, declarative       |
| ACL Implementation | Legacy GACL            | Modern RBAC/ABAC           |
| Scope Management   | Scattered definitions  | Central registry           |
| Caching            | File-based             | Redis/distributed          |
| Documentation      | Manual, outdated       | Auto-generated             |
| Testing            | Difficult              | Easy with DI               |

The improvements can be implemented incrementally while maintaining backward compatibility.
