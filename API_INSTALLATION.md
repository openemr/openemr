# OpenEMR API/ACL Installation & Migration

This document describes how ACL, OAuth2, and API tables are initialized and upgraded.

## Installation Process

### Overview

```
Installation
    ├─ 1. Create database schema (database.sql)
    ├─ 2. Run Installer::install_gacl()
    ├─ 3. Seed default users
    └─ 4. Optional: Load fixtures (testing)
```

## Database Schema Creation

### Source File

`sql/database.sql`

### GACL Tables (lines 2481-2876)

| Table                  | Records Created | Purpose                    |
|------------------------|-----------------|----------------------------|
| `gacl_acl`             | Empty           | ACL rules                  |
| `gacl_acl_sections`    | 2 (system,user) | ACL section organization   |
| `gacl_acl_seq`         | 1 (value=9)     | ACL ID sequence            |
| `gacl_aco`             | Empty           | Access Control Objects     |
| `gacl_aco_sections`    | Empty           | ACO categories             |
| `gacl_aco_seq`         | 1               | ACO ID sequence            |
| `gacl_aco_map`         | Empty           | ACL to ACO mappings        |
| `gacl_aro`             | Empty           | Access Request Objects     |
| `gacl_aro_sections`    | Empty           | ARO sections               |
| `gacl_aro_groups`      | Empty           | User group hierarchy       |
| `gacl_aro_groups_id_seq`| 1              | Group ID sequence          |
| `gacl_aro_map`         | Empty           | ACL to user mappings       |
| `gacl_aro_groups_map`  | Empty           | ACL to group mappings      |
| `gacl_groups_aro_map`  | Empty           | User to group mappings     |

### OAuth2 Tables (lines 14115-14159)

| Table               | Records Created | Purpose                    |
|---------------------|-----------------|----------------------------|
| `oauth_clients`     | Empty           | Registered OAuth2 clients  |
| `oauth_trusted_user`| Empty           | User-client trust records  |
| `api_token`         | Empty           | Access tokens              |
| `api_refresh_token` | Empty           | Refresh tokens             |

### Module ACL Tables

| Table                       | Records Created | Purpose                 |
|-----------------------------|-----------------|-------------------------|
| `module_acl_sections`       | Empty           | Module ACL sections     |
| `module_acl_group_settings` | Empty           | Group permissions       |
| `module_acl_user_settings`  | Empty           | User permissions        |

## GACL Initialization

### Source File

`library/classes/Installer.class.php` → `install_gacl()`

### Step 1: Create ACO Sections

13 sections created:

| Section         | Description                |
|-----------------|----------------------------|
| `acct`          | Accounting                 |
| `admin`         | Administration             |
| `encounters`    | Encounters                 |
| `lists`         | Lists                      |
| `patients`      | Patients                   |
| `squads`        | Squads (deprecated)        |
| `sensitivities` | Sensitivity Levels         |
| `placeholder`   | Placeholder                |
| `nationnotes`   | Nation Notes               |
| `patientportal` | Patient Portal             |
| `menus`         | Menus                      |
| `groups`        | Groups                     |
| `inventory`     | Inventory                  |

### Step 2: Create ACOs

76 Access Control Objects across sections:

**admin section (14 ACOs):**
- `super` — Superuser
- `calendar` — Calendar Settings
- `database` — Database Reporting
- `forms` — Forms Administration
- `practice` — Practice Settings
- `superbill` — Superbill Codes Administration
- `users` — Users/Groups/Logs Administration
- `batchcom` — Batch Communication Tool
- `language` — Language Interface Tool
- `drugs` — Inventory Administration
- `acl` — ACL Administration
- `multipledb` — Multipledb
- `menu` — Menu
- `manage_modules` — Manage modules

**patients section (15 ACOs):**
- `appt` — Appointments (write,wsome optional)
- `demo` — Demographics (write,addonly optional)
- `med` — Medical/History (write,addonly optional)
- `trans` — Transactions (write optional)
- `docs` — Documents (write,addonly optional)
- `docs_rm` — Documents Delete
- `notes` — Patient Notes (write,addonly optional)
- `sign` — Sign Lab Results (write,addonly optional)
- `reminder` — Patient Reminders (write,addonly optional)
- `alert` — Clinical Reminders/Alerts (write,addonly optional)
- `disclosure` — Disclosures (write,addonly optional)
- `rx` — Prescriptions (write,addonly optional)
- `amendment` — Amendments (write,addonly optional)
- `lab` — Lab Results (write,addonly optional)
- `pat_rep` — Patient Report

**encounters section (8 ACOs):**
- `auth` — Authorize - my encounters
- `auth_a` — Authorize - any encounters
- `coding` — Coding - my encounters (write,wsome optional)
- `coding_a` — Coding - any encounters (write,wsome optional)
- `notes` — Notes - my encounters (write,addonly optional)
- `notes_a` — Notes - any encounters (write,addonly optional)
- `date_a` — Fix encounter dates - any encounters
- `relaxed` — Less-protected information (write,addonly optional)

**acct section (5 ACOs):**
- `bill` — Billing (write optional)
- `disc` — Price Discounting
- `eob` — EOB Data Entry
- `rep` — Financial Reporting - my encounters
- `rep_a` — Financial Reporting - anything

**groups section (5 ACOs):**
- `gadd` — View/Add/Update groups
- `gcalendar` — View/Create/Update groups appointment in calendar
- `glog` — Group encounter log
- `gdlog` — Group detailed log of appointment in patient record
- `gm` — Send message from the permanent group therapist to the personal therapist

**sensitivities section (2 ACOs):**
- `normal` — Normal
- `high` — High

### Step 3: Create ARO Groups

7 default groups (hierarchy):

```
OpenEMR Users (id: 10, root)
├── Administrators (id: 11)
├── Clinicians (id: 12)
├── Physicians (id: 13)
├── Front Office (id: 14)
├── Accounting (id: 15)
└── Emergency Login (id: 16)
```

### Step 4: Create ARO Section

One section: `users`

### Step 5: Create Admin User

```php
$gacl->add_object('users', $this->iuname, $this->iuser, 10, 0, 'ARO');
$gacl->add_group_object($admin_group_id, 'users', $this->iuser, 'ARO');
```

### Step 6: Set Permissions

ACL rules created with `add_acl()`:

| ACL | Group         | Permissions                        | Return Value |
|-----|---------------|-------------------------------------|--------------|
| 10  | Administrators| All ACOs                            | `write`      |
| 11  | Physicians    | patients/*, encounters/*            | `write`      |
| 12  | Physicians    | admin/calendar, admin/forms         | `write`      |
| 13  | Clinicians    | patients/appt,demo,med,notes        | `write`      |
| 14  | Clinicians    | encounters/notes,coding             | `write`      |
| ... | ...           | ...                                  | ...          |

## Default Users

### Source Files

- `src/Fixture/data/clean/auth/users.json`
- `src/Fixture/data/clean/auth/users_secure.json`

### Users Created

| ID | Username        | Purpose              | Active |
|----|-----------------|----------------------|--------|
| 1  | `admin`         | Administrator        | Yes    |
| 2  | `phimail-service`| phiMail Gateway     | No     |
| 3  | `portal-user`   | Patient Portal       | No     |
| 4  | `oe-system`     | System operations    | No     |

### Password Storage

`users_secure` table with bcrypt hash:
- Default password hash for `admin`
- Password history tracking (4 slots)
- Login failure counters for lockout

## Fixture System

### For Testing

**Source:** `src/Fixture/`

**Key Classes:**

| Class                     | Purpose                           |
|---------------------------|-----------------------------------|
| `AbstractFixture`         | Base fixture class                |
| `DbAwareAbstractFixture`  | Database-aware fixture            |
| `CompositeFixtureFactory` | Creates clean installation state  |

### Clean Installation Fixtures

24 tables populated:

**ACL Tables (13):**
`gacl_acl`, `gacl_acl_sections`, `gacl_aco`, `gacl_aco_sections`, `gacl_aro`, `gacl_aro_sections`, `gacl_aro_groups`, `gacl_aco_map`, `gacl_aro_map`, `gacl_aro_groups_map`, `gacl_groups_aro_map`, `gacl_aco_seq`, `gacl_aro_seq`

**Module ACL (3):**
`module_acl_sections`, `module_acl_group_settings`, `module_acl_user_settings`

**Auth (4):**
`users`, `users_secure`, `groups`, `oauth_clients`

### Fixture Data Files

| File                              | Content                      |
|-----------------------------------|------------------------------|
| `data/clean/acl/gacl_aro_groups.json` | 7 default groups         |
| `data/clean/acl/gacl_aro.json`    | 2 AROs (admin, oe-system)    |
| `data/clean/acl/gacl_aco.json`    | 76 ACOs                      |
| `data/clean/acl/gacl_acl.json`    | 27+ ACL rules                |
| `data/clean/acl/gacl_aco_map.json`| ACL to ACO mappings          |
| `data/clean/acl/gacl_aro_groups_map.json` | ACL to group mappings |
| `data/clean/auth/users.json`      | Default users                |
| `data/clean/auth/users_secure.json` | Password hashes            |

### Additional Test Data

| File                              | Content                      |
|-----------------------------------|------------------------------|
| `data/additional_users.json`      | Test users (badams, etc.)    |
| `data/additional_acl_groups.json` | Test groups (testers, etc.)  |

## Module ACL

### Sections

| ID | Identifier           | Name                 |
|----|----------------------|----------------------|
| 1  | `immunization`       | Immunization         |
| 2  | `syndromic_surveillance` | Syndromic Surv.  |
| 3  | `documents`          | Documents            |
| 4  | `ccr`                | CCR                  |
| 5  | `carecoordination`   | Care Coordination    |

### Group Settings

Grants module permissions to groups:
- Module 5 → Admin group (11) → Section 5 (allowed)

## Migration/Upgrade Scripts

### SQL Upgrade Files

Located in `sql/` directory, named `{from}_to_{to}_upgrade.sql`

**OAuth2 Introduction (5.0.2 → 6.0.0):**
```sql
CREATE TABLE `oauth_clients` (...)
CREATE TABLE `oauth_trusted_user` (...)
ALTER TABLE `api_token` ADD `scope` TEXT
```

**SMART on FHIR (7.0.1 → 7.0.2):**
```sql
ALTER TABLE `oauth_clients`
  ADD COLUMN `skip_ehr_launch_authorization_flow` tinyint(1) NOT NULL DEFAULT '0'
```

### ACL Upgrade Script

**Source:** `acl_upgrade.php`

**Process:**

1. Read current ACL version from database
2. Incrementally upgrade to each version
3. Add new ACO sections and objects
4. Create new ACL rules
5. Maintain backward compatibility

**Version-based additions:**
- Version 1: Sensitivities, Lists, Placeholder, Nation Notes, Patient Portal
- Later versions: Inventory, Group functionality, etc.

## Data Flow: Fresh Installation

```
1. Run setup.php
    ↓
2. Create database
    ↓
3. Import database.sql
    ├─ Create gacl_* tables (empty except sequences)
    ├─ Create users/users_secure tables
    └─ Create oauth_* tables
    ↓
4. Installer::install_gacl()
    ├─ Create 13 ACO sections
    ├─ Create 76 ACOs
    ├─ Create 7 ARO groups
    ├─ Create ARO section (users)
    ├─ Create admin user as ARO
    ├─ Add admin to Administrators group
    └─ Create 27+ ACL rules
    ↓
5. Create default users
    ├─ admin (active)
    ├─ phimail-service (inactive)
    ├─ portal-user (inactive)
    └─ oe-system (inactive)
    ↓
6. (Optional) Run acl_upgrade.php
    └─ Add any missing ACL components
```

## Data Flow: Test Environment

```
1. CompositePurgerFactory::createPurgeable()->purge()
    └─ Clear all 24 tables
    ↓
2. CompositeFixtureFactory::createLikeCleanInstallation()->load()
    ├─ Load ACL fixtures (13 files)
    ├─ Load auth fixtures (4 files)
    └─ Load module ACL fixtures (3 files)
    ↓
3. (Optional) Load additional fixtures
    ├─ additional_users.json
    └─ additional_acl_groups.json
```

## Key Files Reference

| File                                  | Purpose                        |
|---------------------------------------|--------------------------------|
| `sql/database.sql`                    | Complete schema                |
| `sql/*_upgrade.sql`                   | Incremental schema changes     |
| `library/classes/Installer.class.php` | Installation logic             |
| `acl_upgrade.php`                     | ACL version upgrades           |
| `src/Fixture/CompositeFixtureFactory.php` | Test fixture composition   |
| `src/Fixture/data/clean/acl/*.json`   | ACL fixture data               |
| `src/Fixture/data/clean/auth/*.json`  | Auth fixture data              |
