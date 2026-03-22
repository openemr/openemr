# MedEx Library Integration Analysis

## Current Situation

The modernized MedEx module code is in:
```
interface/modules/custom_modules/oe-module-medex/src/API/
```

But the **old monolithic code** still exists in:
```
library/MedEx/API.php (3,620 lines)
```

## Problem

Three core OpenEMR files depend on `/library/MedEx/API.php`:

### 1. `/interface/main/messages/save.php`
**Purpose:** Recall Board AJAX backend
**Line 17:** `require_once "$srcdir/MedEx/API.php";`

**MedEx Usage:**
- Line 22: `$MedEx = new MedExApi\MedEx(...)`
- Line 70: `$MedEx->login('1')` - MedEx authentication
- Line 112: `$MedEx->setup->autoReg($data)` - MedEx registration
- Line 135: `$MedEx->login('2')` - MedEx login after setup
- Line 161: `$MedEx->events->getAge($result['DOB'])` - **Used even without MedEx**
- Line 205: `$MedEx->events->save_recall($_REQUEST)` - **Core recall functionality**
- Line 211: `$MedEx->events->delete_recall()` - **Core recall functionality**

**Critical Finding:**
- Lines 161, 205, 211 are used **even when MedEx is disabled**
- These are **core Recall Board functions** needed by all users

---

### 2. `/interface/main/messages/messages.php`
**Purpose:** Message and Reminder Center UI (Recall Board main page)
**Line 27:** `require_once "$srcdir/MedEx/API.php";`

**MedEx Usage:**
- Line 39: `$MedEx = new MedExApi\MedEx(...)`
- Lines 41-50: Conditional MedEx login (only if `$GLOBALS['medex_enable'] == '1'`)
- Line 44: `$MedEx->display->SMS_bot($result)` - MedEx SMS bot UI

**Critical Finding:**
- File loads MedEx but only uses it conditionally
- Main Recall Board UI works without MedEx functionality
- BUT requires MedEx classes to exist

---

### 3. `/interface/patient_tracker/patient_tracker.php`
**Purpose:** Patient Flow Board
**Line 26:** `require_once "$srcdir/MedEx/API.php";`

**MedEx Usage:**
- Requires the file but **may not actually use it** (needs deeper inspection)

**Critical Finding:**
- Need to verify if Flow Board actually uses MedEx functions
- May be a leftover require from development

---

## Functions Used by Core OpenEMR (Non-MedEx Users)

From `save.php`, these methods are used **even without MedEx enabled**:

### 1. `$MedEx->events->getAge($dob)`
**Location:** EventsService
**Purpose:** Calculate patient age from DOB
**Used in:** New recall creation (line 161)

```php
$result['age'] = $MedEx->events->getAge($result['DOB']);
```

**Issue:** This is a utility function that shouldn't require MedEx

---

### 2. `$MedEx->events->save_recall($_REQUEST)`
**Location:** EventsService
**Purpose:** Save/update a patient recall
**Used in:** Recall creation/update (line 205)

```php
$result = $MedEx->events->save_recall($_REQUEST);
```

**Issue:** **Core Recall Board functionality** - All practices need this

---

### 3. `$MedEx->events->delete_recall()`
**Location:** EventsService
**Purpose:** Delete a patient recall
**Used in:** Recall deletion (line 211)

```php
$MedEx->events->delete_recall();
```

**Issue:** **Core Recall Board functionality** - All practices need this

---

## Architectural Problem

**Current Architecture:**
```
Core OpenEMR Files (Recall/Flow Board)
         ↓ require_once
library/MedEx/API.php (3,620 lines - old monolithic)
         ↓ creates
    MedExApi\MedEx
         ↓ contains
    ->events (EventsService)
    ->display (DisplayService)
    ->setup (SetupService)
```

**Problem:**
- Core OpenEMR features (Recall Board) depend on MedEx namespace
- Even users who **don't use MedEx messaging** need these functions
- Can't remove `/library/MedEx/API.php` without breaking Recall Board

---

## Solutions

### Option 1: Bridge/Facade in /library/MedEx/API.php ✅ RECOMMENDED

Replace `/library/MedEx/API.php` with a lightweight bridge:

```php
<?php
/**
 * /library/MedEx/API.php
 * BRIDGE FILE - Redirects to modernized module
 *
 * This file maintains backward compatibility for core OpenEMR files
 * (Recall Board, Flow Board) that depend on MedEx classes.
 */

namespace MedExApi;

// Load the modernized module
require_once(__DIR__ . '/../../interface/modules/custom_modules/oe-module-medex/src/API/API.php');

// All classes are now available via the module's API.php
// The module already provides backward-compatible class aliases:
// - MedExApi\MedEx
// - MedExApi\Events (alias to EventsService)
// - MedExApi\Display (alias to DisplayService)
// etc.
```

**Pros:**
- ✅ Minimal changes to core files
- ✅ Maintains 100% backward compatibility
- ✅ Can delete old 3,620-line monolith
- ✅ Core features still work

**Cons:**
- ⚠️ Core files still depend on module being present
- ⚠️ Module can't be disabled if Recall Board is used

---

### Option 2: Extract Core Functions to /library/

Move non-MedEx functions out of MedEx namespace:

**Create: `/library/RecallBoard/RecallFunctions.php`**
```php
<?php
namespace OpenEMR\RecallBoard;

class RecallFunctions
{
    public static function getAge(string $dob): int
    {
        // Move getAge() here
    }

    public static function saveRecall(array $data): bool
    {
        // Move save_recall() here
    }

    public static function deleteRecall(int $pid): bool
    {
        // Move delete_recall() here
    }
}
```

**Update: `/interface/main/messages/save.php`**
```php
// Old (line 17):
require_once "$srcdir/MedEx/API.php";

// New:
require_once "$srcdir/RecallBoard/RecallFunctions.php";
use OpenEMR\RecallBoard\RecallFunctions;

// Usage:
$age = RecallFunctions::getAge($result['DOB']);
RecallFunctions::saveRecall($_REQUEST);
RecallFunctions::deleteRecall();
```

**Pros:**
- ✅ Clean separation of concerns
- ✅ Recall Board independent of MedEx
- ✅ MedEx module can be disabled
- ✅ Follows OpenEMR architectural patterns

**Cons:**
- ❌ Requires changes to core files
- ❌ More refactoring work
- ❌ Need to duplicate/move code from EventsService

---

### Option 3: Hybrid Approach ✅ BEST LONG-TERM

**Phase 1: Bridge (Immediate)**
- Replace `/library/MedEx/API.php` with bridge to module
- Everything works immediately
- No core file changes needed

**Phase 2: Refactor (Future)**
- Extract core functions to `/library/RecallBoard/`
- Update core files to use new namespace
- Make MedEx module truly optional

---

## Detailed Function Analysis

### Functions in EventsService Used by Core OpenEMR

#### 1. `getAge($dob)`
**Location:** EventsService (needs to be found)
**Complexity:** Low - Simple date calculation
**Dependencies:** None
**Recommendation:** Move to utility class

#### 2. `save_recall($data)`
**Location:** EventsService (needs to be found)
**Complexity:** Medium - Database INSERT/UPDATE
**Dependencies:**
- QueryUtils
- medex_recalls table
**Recommendation:** Keep in EventsService but ensure accessible via bridge

#### 3. `delete_recall()`
**Location:** EventsService (needs to be found)
**Complexity:** Low - Database DELETE
**Dependencies:**
- QueryUtils
- medex_recalls table
**Recommendation:** Keep in EventsService but ensure accessible via bridge

---

## Impact Assessment

### Files That Must Be Updated (Option 2)

If we choose full refactoring:

1. `/interface/main/messages/save.php` (261 lines)
   - Update require_once (line 17)
   - Update 7 method calls

2. `/interface/main/messages/messages.php` (~1000 lines)
   - Update require_once (line 27)
   - Update conditional MedEx usage (lines 41-50)

3. `/interface/patient_tracker/patient_tracker.php` (~1000 lines)
   - Verify if MedEx is actually used
   - Update or remove require_once (line 26)

### Database Tables Used

These tables are used by **both** MedEx and core Recall Board:

- `medex_recalls` - Recall data (needed by all users)
- `medex_outgoing` - Message log (MedEx only)
- `medex_prefs` - MedEx preferences (MedEx only)

**No changes needed to tables.**

---

## Recommendation

### Immediate Action: **Option 1 (Bridge)**

1. **Replace `/library/MedEx/API.php`** with a lightweight bridge:

```php
<?php
/**
 * /library/MedEx/API.php
 * COMPATIBILITY BRIDGE
 *
 * This file maintains backward compatibility with core OpenEMR files.
 * All functionality has been modernized and moved to:
 * /interface/modules/custom_modules/oe-module-medex/
 */

namespace MedExApi;

// Load modernized module
$module_path = __DIR__ . '/../../interface/modules/custom_modules/oe-module-medex/src/API/API.php';

if (file_exists($module_path)) {
    require_once($module_path);
} else {
    // Fallback error
    error_log("MedEx module not found at: $module_path");
    throw new \Exception("MedEx module is required but not found. Please install oe-module-medex.");
}

// All classes now available:
// - MedExApi\MedEx (main class)
// - MedExApi\Events (backward compatible alias)
// - MedExApi\Display (backward compatible alias)
// - MedExApi\Setup (backward compatible alias)
```

2. **Verify the modernized EventsService has these methods:**
   - `getAge($dob)`
   - `save_recall($data)`
   - `delete_recall()`

3. **Test Recall Board without MedEx enabled:**
   - Create new recall
   - Update recall
   - Delete recall
   - Verify no errors

### Future Action: **Option 3 (Hybrid)**

In a future PR:
1. Extract core recall functions to `/library/RecallBoard/`
2. Update core files to use new namespace
3. Make MedEx module truly optional
4. Remove bridge file

---

## Testing Plan

### Test 1: Bridge Implementation
1. Replace `/library/MedEx/API.php` with bridge
2. Navigate to Messages → Recalls
3. Create new recall
4. Update recall
5. Delete recall
6. Monitor logs for errors

### Test 2: With MedEx Disabled
1. Set `$GLOBALS['medex_enable'] = '0'`
2. Navigate to Messages → Recalls
3. Verify Recall Board still works
4. Verify no MedEx messaging features appear

### Test 3: With MedEx Enabled
1. Set `$GLOBALS['medex_enable'] = '1'`
2. Navigate to Messages → Recalls
3. Verify MedEx messaging features work
4. Test campaign creation
5. Test message sending

### Test 4: Flow Board
1. Navigate to Patient Flow Board
2. Verify it loads without errors
3. Check if any MedEx features are actually used

---

## Next Steps

1. ✅ Verify EventsService has the 3 required methods
2. ✅ Create bridge file for `/library/MedEx/API.php`
3. ✅ Test Recall Board with bridge
4. ✅ Test with MedEx disabled
5. ✅ Test with MedEx enabled
6. ✅ Document changes
7. ⏳ Future: Refactor to remove dependency

---

## Files to Check

Need to verify these methods exist in modernized EventsService:

```bash
grep -n "function getAge" interface/modules/custom_modules/oe-module-medex/src/API/Services/EventsService.php
grep -n "function save_recall" interface/modules/custom_modules/oe-module-medex/src/API/Services/EventsService.php
grep -n "function delete_recall" interface/modules/custom_modules/oe-module-medex/src/API/Services/EventsService.php
```

If missing, need to add them before creating bridge.
