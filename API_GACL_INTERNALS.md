# OpenEMR GACL Internals

This document describes the Generic Access Control List (GACL) system internals.

## Overview

GACL is a role-based access control system using a **2-dimensional model**:

| Dimension | GACL Term | Meaning          | Tables                           |
|-----------|-----------|------------------|----------------------------------|
| WHO       | ARO       | Users/Groups     | `gacl_aro`, `gacl_aro_groups`    |
| WHAT      | ACO       | Permissions      | `gacl_aco`, `gacl_aco_sections`  |

**Note:** AXO (3rd dimension) tables exist but are NOT used in OpenEMR.

## Nested Set Model (MPTT)

User groups use Modified Preorder Tree Traversal for hierarchy:

### Structure

```
                    OpenEMR Users (lft=1, rgt=14)
                    /           |            \
        Administrators    Clinicians      Physicians
        (lft=2,rgt=3)    (lft=4,rgt=5)   (lft=6,rgt=7)
```

### Columns

| Column     | Description                              |
|------------|------------------------------------------|
| `lft`      | Left boundary of subtree                 |
| `rgt`      | Right boundary of subtree                |
| `parent_id`| Parent group ID                          |

### Ancestor Query

Find all groups a user belongs to (including ancestors):

```sql
SELECT DISTINCT g2.id
FROM gacl_aro_groups g1
JOIN gacl_aro_groups g2
  ON g2.lft <= g1.lft AND g2.rgt >= g1.rgt
WHERE g1.value = 'user_group_value'
```

### Tree Properties

- If `g2.lft <= g1.lft AND g2.rgt >= g1.rgt`, then g2 is ancestor of g1
- Smaller `(rgt - lft)` = more specific (deeper) group
- Root group has largest range

## Database Schema

### Core Tables

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   gacl_acl      │     │  gacl_aco_map   │     │    gacl_aco     │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │◄────│ acl_id (FK)     │     │ id (PK)         │
│ allow (0/1)     │     │ section_value   │────►│ section_value   │
│ enabled (0/1)   │     │ value           │     │ value           │
│ return_value    │     └─────────────────┘     │ name            │
│ updated_date    │                             └─────────────────┘
│ note            │
└─────────────────┘
        │
        │     ┌─────────────────┐     ┌─────────────────┐
        │     │  gacl_aro_map   │     │    gacl_aro     │
        │     ├─────────────────┤     ├─────────────────┤
        ├────►│ acl_id (FK)     │     │ id (PK)         │
        │     │ section_value   │────►│ section_value   │
        │     │ value           │     │ value (username)│
        │     └─────────────────┘     │ name            │
        │                             └─────────────────┘
        │
        │     ┌─────────────────────┐     ┌─────────────────┐
        │     │ gacl_aro_groups_map │     │ gacl_aro_groups │
        │     ├─────────────────────┤     ├─────────────────┤
        └────►│ acl_id (FK)         │     │ id (PK)         │
              │ group_id (FK)       │────►│ parent_id       │
              └─────────────────────┘     │ value           │
                                          │ name            │
                                          │ lft, rgt        │
                                          └─────────────────┘
```

### Table Descriptions

| Table                  | Purpose                                    |
|------------------------|--------------------------------------------|
| `gacl_acl`             | ACL rules (allow/deny with return value)   |
| `gacl_aco`             | Access Control Objects (permissions)       |
| `gacl_aco_sections`    | ACO groupings (admin, patients, etc.)      |
| `gacl_aro`             | Access Request Objects (users)             |
| `gacl_aro_groups`      | User groups with hierarchy                 |
| `gacl_aco_map`         | Links ACLs to ACOs                         |
| `gacl_aro_map`         | Links ACLs to individual users             |
| `gacl_aro_groups_map`  | Links ACLs to user groups                  |
| `gacl_groups_aro_map`  | Maps users to groups                       |

## ACL Query Algorithm

### Main Function: `acl_query()`

**Location:** `src/Gacl/Gacl.php`

### Query Flow

```
acl_query($aco_section, $aco_value, $aro_section, $aro_value, ...)
    │
    ├─ Check Cache
    │   └─ HIT: Return cached result
    │
    ├─ Get ARO Groups
    │   └─ acl_get_groups() → [group_id, parent_id, ...]
    │
    ├─ Build SQL Query
    │   ├─ SELECT a.id, a.allow, a.return_value
    │   ├─ FROM gacl_acl a
    │   ├─ LEFT JOIN gacl_aco_map (always)
    │   ├─ LEFT JOIN gacl_aro_map (if direct user match possible)
    │   ├─ LEFT JOIN gacl_aro_groups_map (if user has groups)
    │   ├─ LEFT JOIN gacl_aro_groups (for specificity ordering)
    │   └─ WHERE + ORDER BY
    │
    ├─ Execute with LIMIT 1
    │
    ├─ Cache Result
    │
    └─ Return {acl_id, allow, return_value}
```

### WHERE Clause Structure

```sql
WHERE a.enabled = 1
  -- ACO Match (always required)
  AND ac.section_value = ? AND ac.value = ?
  -- ARO Match (direct OR group)
  AND (
    (ar.section_value = ? AND ar.value = ?)
    OR rg.id IN (group_ids)
  )
```

### ORDER BY (Conflict Resolution)

```sql
ORDER BY
  -- 1. Direct user match beats group match
  (CASE WHEN ar.value IS NULL THEN 0 ELSE 1 END) DESC,
  -- 2. Smaller group (more specific) wins
  (rg.rgt - rg.lft) ASC,
  -- 3. Most recently updated wins
  a.updated_date DESC
LIMIT 1
```

## Conflict Resolution

When multiple ACLs match, priority order:

| Priority | Rule                              | SQL Expression                    |
|----------|-----------------------------------|-----------------------------------|
| 1        | Direct user match over group      | `ar.value IS NOT NULL` first      |
| 2        | More specific group wins          | `(rg.rgt - rg.lft) ASC`           |
| 3        | Most recently updated wins        | `a.updated_date DESC`             |

### Example Scenario

```
User: john
Groups: orthopedic_team (child of doctors)

Matching ACLs:
  ACL 1: Direct match for "john" → write
  ACL 2: Match via "orthopedic_team" group → write
  ACL 3: Match via "doctors" group → view

Result: ACL 1 wins (direct match)

If no direct match:
  orthopedic_team: rgt-lft = 3 (smaller, more specific)
  doctors: rgt-lft = 9 (larger, less specific)
  Result: ACL 2 wins (more specific group)
```

## Return Values

### Storage

Return values are stored in `gacl_acl.return_value` as TEXT.

### Common Values

| Value     | Meaning                              |
|-----------|--------------------------------------|
| `write`   | Full read/write access               |
| `view`    | Read-only access                     |
| `addonly` | Can add but not modify               |
| `wsome`   | Partial write access                 |

### Access Methods

```php
// Check if allowed (boolean)
$result = $gacl->acl_check($aco_section, $aco_value, $aro_section, $aro_value);
// Returns: true/false

// Get return value
$result = $gacl->acl_return_value($aco_section, $aco_value, $aro_section, $aro_value);
// Returns: ['acl_id' => 7, 'return_value' => 'write', 'allow' => true]
```

## Caching

### Configuration

| Property             | Default              | Description              |
|----------------------|----------------------|--------------------------|
| `_caching`           | FALSE                | Enable/disable caching   |
| `_cache_dir`         | `/tmp/phpgacl_cache` | Cache directory          |
| `_cache_expire_time` | 600 (10 min)         | Cache TTL in seconds     |
| `_force_cache_expire`| TRUE                 | Auto-expire on changes   |

### Cache Keys

```php
// Query cache
'acl_query_' . $aco_section . '-' . $aco_value . '-' . $aro_section . '-' . $aro_value . '...'

// Group lookup cache
'acl_get_groups_' . $section_value . '-' . $value . '-' . $root_group . '-' . $group_type
```

### Cache Operations

```php
// Check cache
$cached = $this->get_cache($cache_id);

// Store result
$this->put_cache($data, $cache_id);

// Clear all cache (on ACL modification)
$this->clear_cache();
```

## API Methods

### Checking Permissions

| Method                 | Purpose                          | Returns              |
|------------------------|----------------------------------|----------------------|
| `acl_check()`          | Check if access allowed          | boolean              |
| `acl_return_value()`   | Get permission level             | array with value     |
| `acl_query()`          | Full query with all details      | array                |
| `acl_check_array()`    | Batch check multiple AROs        | array                |

### Managing ACLs

| Method                 | Purpose                          |
|------------------------|----------------------------------|
| `add_acl()`            | Create new ACL rule              |
| `edit_acl()`           | Modify existing ACL              |
| `del_acl()`            | Delete ACL rule                  |
| `search_acl()`         | Find ACLs by criteria            |
| `get_acl()`            | Get full ACL details             |
| `append_acl()`         | Add AROs/ACOs to existing ACL    |
| `shift_acl()`          | Remove AROs/ACOs from ACL        |

### Managing Groups

| Method                 | Purpose                          |
|------------------------|----------------------------------|
| `add_group()`          | Create user group                |
| `edit_group()`         | Modify group                     |
| `del_group()`          | Delete group                     |
| `get_group_id()`       | Find group by value/name         |
| `get_group_children()` | Get child groups                 |
| `get_group_parent_id()`| Get parent group                 |
| `add_group_object()`   | Add user to group                |
| `del_group_object()`   | Remove user from group           |
| `rebuild_tree()`       | Rebuild nested set structure     |

### Managing Objects

| Method                 | Purpose                          |
|------------------------|----------------------------------|
| `add_object()`         | Create ACO/ARO                   |
| `edit_object()`        | Modify object                    |
| `del_object()`         | Delete object                    |
| `get_objects()`        | List objects in section          |
| `add_object_section()` | Create section                   |

## Performance Considerations

### Indexes Required

```sql
-- Essential for nested set queries
CREATE INDEX idx_aro_groups_lft_rgt ON gacl_aro_groups(lft, rgt);

-- ACL lookup
CREATE INDEX idx_acl_enabled ON gacl_acl(enabled);
CREATE INDEX idx_aco_map_acl ON gacl_aco_map(acl_id, section_value, value);
CREATE INDEX idx_aro_map_acl ON gacl_aro_map(acl_id, section_value, value);
```

### Query Optimization

| Optimization                   | Implementation                        |
|--------------------------------|---------------------------------------|
| Conditional JOINs              | Only join group tables if groups exist|
| Cache hit                      | Multi-tier caching at query+group     |
| LIMIT 1                        | Only fetch first matching rule        |
| Prepared statements            | Parameterized queries                 |

### Potential Bottlenecks

| Issue                          | Impact                               |
|--------------------------------|--------------------------------------|
| Deep group hierarchies         | More ancestors to query              |
| User in many groups            | Large IN clause                      |
| No indexes on lft/rgt          | Full table scans                     |
| Cache disabled                 | DB query every request               |
| Large ACL tables               | Slower query execution               |

## Data Flow Diagram

```
Request: Can "john" access "patients/med" with "write"?
    │
    ▼
AclMain::aclCheckCore('patients', 'med', 'john', 'write')
    │
    ├─ Check superuser (admin/super)
    │   └─ If yes: return TRUE (bypass)
    │
    ├─ Get Gacl instance
    │
    └─ $gacl->acl_check('patients', 'med', 'users', 'john')
        │
        └─ acl_query('patients', 'med', 'users', 'john')
            │
            ├─ Check cache (key: acl_query_patients-med-users-john-...)
            │
            ├─ acl_get_groups('users', 'john', NULL, 'ARO')
            │   └─ Returns: [5, 3, 1] (john_group, clinicians, users)
            │
            ├─ Execute SQL:
            │   SELECT a.id, a.allow, a.return_value
            │   FROM gacl_acl a
            │   LEFT JOIN gacl_aco_map ac ON ac.acl_id = a.id
            │   LEFT JOIN gacl_aro_map ar ON ar.acl_id = a.id
            │   LEFT JOIN gacl_aro_groups_map arg ON arg.acl_id = a.id
            │   LEFT JOIN gacl_aro_groups rg ON rg.id = arg.group_id
            │   WHERE a.enabled = 1
            │     AND ac.section_value = 'patients' AND ac.value = 'med'
            │     AND ((ar.section_value = 'users' AND ar.value = 'john')
            │          OR rg.id IN (5, 3, 1))
            │   ORDER BY (CASE WHEN ar.value IS NULL THEN 0 ELSE 1 END) DESC,
            │            (rg.rgt - rg.lft) ASC,
            │            a.updated_date DESC
            │   LIMIT 1
            │
            ├─ Parse result: {acl_id: 15, allow: 1, return_value: 'write'}
            │
            ├─ Cache result
            │
            └─ Return result
    │
    ▼
Compare return_value 'write' against required 'write' → MATCH
    │
    ▼
Return TRUE (access granted)
```

## Key Files Reference

| File                       | Purpose                              |
|----------------------------|--------------------------------------|
| `src/Gacl/Gacl.php`        | Core: acl_query, acl_check, caching  |
| `src/Gacl/GaclApi.php`     | Management: add_acl, add_group, etc. |
| `src/Common/Acl/AclMain.php` | High-level wrapper                 |
| `library/acl.inc.php`      | Legacy helper functions              |
