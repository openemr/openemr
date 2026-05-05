# Table Rename Plan: Remove "MedEx" from Core

## Problem

Core OpenEMR tables have "medex" in their names, even though they're used by ALL users (not just MedEx users):

- `medex_recalls` - Core recall board data
- `medex_outgoing` - Message/action tracking (postcards, labels, phone calls)

This creates architectural pollution where the core depends on MedEx naming.

---

## Solution: Rename Tables

### Table 1: `medex_recalls` → `patient_recalls`

**Purpose:** Store patient recall appointments
**Used by:** Core Recall Board (all users)
**MedEx-specific?** NO

**Current Structure:**
```sql
CREATE TABLE `medex_recalls` (
  `r_ID` int(11) NOT NULL AUTO_INCREMENT,
  `r_PRACTID` int(11) NOT NULL,
  `r_pid` int(11) NOT NULL COMMENT 'PatientID from pat_data',
  `r_eventDate` date NOT NULL COMMENT 'Date of Appt or Recall',
  `r_facility` int(11) NOT NULL,
  `r_provider` int(11) NOT NULL,
  `r_reason` varchar(255) DEFAULT NULL,
  `r_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`r_ID`),
  UNIQUE KEY `r_PRACTID` (`r_PRACTID`,`r_pid`)
) ENGINE=InnoDB;
```

**New Name:** `patient_recalls`

---

### Table 2: `medex_outgoing` → `recall_board_actions`

**Purpose:** Track recall board actions (postcards printed, labels printed, phone calls made, notes added)
**Used by:** Core Recall Board (all users)
**MedEx-specific?** Partially

**Analysis:**
- **Core fields:** `msg_pid`, `msg_pc_eid`, `msg_date`, `msg_type`, `msg_reply`, `msg_extra_text`
- **MedEx fields:** `campaign_uid`, `medex_uid`

**Current Structure:**
```sql
CREATE TABLE `medex_outgoing` (
  `msg_uid` int(11) NOT NULL AUTO_INCREMENT,
  `msg_pid` int(11) NOT NULL,
  `msg_pc_eid` varchar(11) NOT NULL,
  `campaign_uid` int(11) NOT NULL DEFAULT '0',
  `msg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `msg_type` varchar(50) NOT NULL,
  `msg_reply` varchar(50) DEFAULT NULL,
  `msg_extra_text` text,
  `medex_uid` int(11),
  PRIMARY KEY (`msg_uid`),
  UNIQUE KEY `msg_eid` (`msg_uid`,`msg_pc_eid`,`medex_uid`)
) ENGINE=InnoDB;
```

**Options:**

#### Option A: Rename to `recall_board_actions`
Keep all fields, but rename table to be neutral:
- Core uses: `msg_type` = 'postcards', 'labels', 'phone', 'notes'
- MedEx uses: `msg_type` = 'SMS', 'AVM', 'EMAIL' (with `campaign_uid` and `medex_uid`)

#### Option B: Split into two tables
- `recall_board_actions` - Core actions (postcards, labels, phone, notes)
- `medex_messages` (in module) - MedEx messages (SMS, AVM, EMAIL)

**Recommendation:** **Option A** (rename only) - simpler migration, maintains compatibility

---

### Table 3: `medex_prefs` → Keep as-is or move to module

**Purpose:** MedEx-specific preferences
**Used by:** MedEx module only
**MedEx-specific?** YES

**Options:**

#### Option A: Keep in core (with migration path)
- Rename to `medex_module_prefs` to clarify it's module-specific
- Eventually move to module-managed storage

#### Option B: Leave as `medex_prefs`
- It's module-specific, name is accurate
- When module is uninstalled, table can be dropped

**Recommendation:** **Option B** (leave as-is) - it's genuinely MedEx-specific

---

## Migration SQL

### Step 1: Rename Tables

```sql
-- Rename recalls table
RENAME TABLE `medex_recalls` TO `patient_recalls`;

-- Rename outgoing table
RENAME TABLE `medex_outgoing` TO `recall_board_actions`;
```

### Step 2: Update Code References

#### RecallService.php
```php
// BEFORE:
"SELECT * FROM medex_recalls WHERE r_pid = ?"
"DELETE FROM medex_recalls WHERE r_pid = ?"
"INSERT INTO medex_recalls ..."

// AFTER:
"SELECT * FROM patient_recalls WHERE r_pid = ?"
"DELETE FROM patient_recalls WHERE r_pid = ?"
"INSERT INTO patient_recalls ..."
```

#### save.php
```php
// BEFORE:
"SELECT * FROM medex_recalls WHERE r_pid=?"
"INSERT INTO medex_outgoing ..."

// AFTER:
"SELECT * FROM patient_recalls WHERE r_pid=?"
"INSERT INTO recall_board_actions ..."
```

### Step 3: Module Updates

MedEx module needs to use new table names:
- `patient_recalls` instead of `medex_recalls`
- `recall_board_actions` instead of `medex_outgoing`
- `medex_prefs` stays the same

---

## Backward Compatibility

### During Migration Period

Create **views** for backward compatibility:

```sql
-- Create view with old name pointing to new table
CREATE VIEW `medex_recalls` AS SELECT * FROM `patient_recalls`;
CREATE VIEW `medex_outgoing` AS SELECT * FROM `recall_board_actions`;
```

**Benefits:**
- Existing code continues to work during transition
- Module code can be updated gradually
- Can be dropped in future release

---

## PR Strategy

### Option 1: Two-Phase Approach ✅ RECOMMENDED

**Phase 1: This PR**
- Remove `/library/MedEx/` code
- Create `RecallService` and `CommunicationService`
- Update core files to use new services
- **Keep existing table names** (medex_recalls, medex_outgoing)
- Document that tables will be renamed in future

**Phase 2: Future PR**
- Rename tables
- Update all references
- Create migration script
- Update documentation

**Why?** Keeps this PR focused on code extraction, not database migration

### Option 2: Everything in One PR

**Include in this PR:**
- Code extraction (RecallService, CommunicationService)
- Table renames (medex_recalls → patient_recalls, etc.)
- Migration script
- Update all references

**Why?** Complete cleanup in one go

---

## Recommendation: Option 1 (Two-Phase)

**This PR should focus on:**
- ✅ Extract MedEx code from core
- ✅ Create RecallService and CommunicationService
- ✅ Remove `/library/MedEx/` directory
- ✅ Make module truly optional

**Future PR should handle:**
- ✅ Rename tables (medex_recalls → patient_recalls)
- ✅ Database migration
- ✅ Update all table references

**Reasoning:**
1. Smaller, focused PRs are easier to review
2. Code extraction has no data migration risk
3. Table rename is a separate database migration concern
4. This PR already delivers the main value (remove MedEx dependency)

---

## Alternative: Do It All Now

If you want to include table renames in this PR:

### Migration Script: `sql/7_0_3-to-7_0_4_upgrade.sql`

```sql
--
-- Rename MedEx tables to neutral names
--

-- Rename recalls table
RENAME TABLE `medex_recalls` TO `patient_recalls`;

-- Rename outgoing messages table
RENAME TABLE `medex_outgoing` TO `recall_board_actions`;

-- Create backward-compatible views (can be removed in future version)
CREATE OR REPLACE VIEW `medex_recalls` AS SELECT * FROM `patient_recalls`;
CREATE OR REPLACE VIEW `medex_outgoing` AS SELECT * FROM `recall_board_actions`;

-- Note: medex_prefs table keeps its name (module-specific)
```

### Update All Code

**Core files use new names:**
- `patient_recalls`
- `recall_board_actions`

**Module can use either:**
- New names (patient_recalls, recall_board_actions)
- Old names via views (during transition)

---

## Your Decision

**Which approach do you prefer?**

### A. Two-Phase (Recommended)
- **This PR:** Code extraction only, keep table names
- **Future PR:** Rename tables

### B. All-In-One
- **This PR:** Code extraction + table renames
- Includes migration script
- More comprehensive but larger PR

**For upstream OpenEMR acceptance, Option A (two-phase) is probably safer.**

Let me know which you prefer!
