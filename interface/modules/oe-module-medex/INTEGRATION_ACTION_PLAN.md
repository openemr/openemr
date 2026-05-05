# MedEx Library Integration - Action Plan

## Summary

We've successfully modernized the MedEx module, but core OpenEMR files (Recall Board, Flow Board) still depend on the old `/library/MedEx/API.php` file.

---

## Current Status ✅

**Modernized Code:**
- ✅ `/interface/modules/custom_modules/oe-module-medex/src/API/` - 3,514 lines
- ✅ All services extracted and modernized
- ✅ PHPStan Level 6 compliant (0 errors)
- ✅ Backward compatible aliases in place

**Old Code Still in Use:**
- ⚠️ `/library/MedEx/API.php` - 3,620 lines (monolithic)
- ⚠️ Required by 3 core files

---

## Required Methods Found ✅

I've verified the modernized EventsService has 2 of 3 required methods:

### 1. ✅ `getAge($dob, $asof = '')` - Line 1076
**Purpose:** Calculate patient age from date of birth
**Status:** EXISTS in EventsService

### 2. ✅ `save_recall($saved)` - Line 1020
**Purpose:** Save/update patient recall
**Status:** EXISTS in EventsService

### 3. ❌ `delete_Recall()` - MISSING
**Purpose:** Delete patient recall and associated messages
**Status:** NEEDS TO BE ADDED

**Old implementation:**
```php
public function delete_Recall()
{
    $sqlQuery = "DELETE FROM medex_recalls WHERE r_pid=? OR r_ID=?";
    sqlStatement($sqlQuery, [$_POST['pid'],$_POST['r_ID']]);

    $sqlDELETE = "DELETE FROM medex_outgoing WHERE msg_pc_eid = ?";
    sqlStatement($sqlDELETE, ['recall_' . $_POST['pid']]);
}
```

---

## Integration Plan

### Step 1: Add Missing Method to EventsService ✅ REQUIRED

Add `delete_Recall()` to EventsService (modernized version):

```php
/**
 * Delete a patient recall and associated outgoing messages
 *
 * @return void
 */
public function delete_Recall(): void
{
    $pid = $_POST['pid'] ?? null;
    $r_ID = $_POST['r_ID'] ?? null;

    if (!$pid && !$r_ID) {
        return;
    }

    // Delete recall record
    $deleteRecall = "DELETE FROM medex_recalls WHERE r_pid=? OR r_ID=?";
    QueryUtils::sqlStatementThrowException($deleteRecall, [$pid, $r_ID]);

    // Delete associated outgoing messages
    if ($pid) {
        $deleteMessages = "DELETE FROM medex_outgoing WHERE msg_pc_eid = ?";
        QueryUtils::sqlStatementThrowException($deleteMessages, ['recall_' . $pid]);
    }
}
```

---

### Step 2: Create Bridge File ✅ REQUIRED

Replace `/library/MedEx/API.php` (3,620 lines) with a lightweight bridge (50 lines):

**File:** `/library/MedEx/API.php`
```php
<?php
/**
 * /library/MedEx/API.php
 * COMPATIBILITY BRIDGE
 *
 * This file maintains backward compatibility with core OpenEMR files.
 * All functionality has been modernized and moved to the oe-module-medex module.
 *
 * Files that depend on this bridge:
 * - /interface/main/messages/save.php (Recall Board backend)
 * - /interface/main/messages/messages.php (Recall Board UI)
 * - /interface/patient_tracker/patient_tracker.php (Flow Board)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 OpenEMR (Bridge implementation)
 * @license   Proprietary - All Rights Reserved
 */

namespace MedExApi;

// Determine module path relative to library directory
$module_path = __DIR__ . '/../../interface/modules/custom_modules/oe-module-medex/src/API/API.php';

if (!file_exists($module_path)) {
    // Log error
    error_log("MedEx Bridge Error: Module not found at $module_path");

    // Try alternate path (in case of different directory structure)
    $module_path = dirname(__DIR__, 2) . '/interface/modules/custom_modules/oe-module-medex/src/API/API.php';

    if (!file_exists($module_path)) {
        error_log("MedEx Bridge Error: Module not found at alternate path either");
        throw new \Exception(
            "MedEx module is required but not found. " .
            "Please ensure oe-module-medex is installed at: " .
            "/interface/modules/custom_modules/oe-module-medex/"
        );
    }
}

// Load the modernized module
require_once($module_path);

/**
 * All MedEx classes are now available via the module:
 *
 * - MedExApi\MedEx               (main coordinator class)
 * - MedExApi\Client\HttpClient   (HTTP client)
 * - MedExApi\Services\*          (all service classes)
 *
 * Backward-compatible aliases:
 * - MedExApi\CurlRequest  -> MedExApi\Client\HttpClient
 * - MedExApi\Events       -> MedExApi\Services\EventsService
 * - MedExApi\Display      -> MedExApi\Services\DisplayService
 * - MedExApi\Practice     -> MedExApi\Services\PracticeService
 * - MedExApi\Campaign     -> MedExApi\Services\CampaignService
 * - MedExApi\Callback     -> MedExApi\Services\CallbackService
 * - MedExApi\Logging      -> MedExApi\Services\LoggingService
 * - MedExApi\Setup        -> MedExApi\Services\SetupService
 *
 * These aliases ensure old code like:
 *   $MedEx = new MedExApi\MedEx(...);
 *   $MedEx->events->save_recall($data);
 *
 * Continues to work without modification.
 */
```

---

### Step 3: Backup Old File ✅ SAFETY

Before replacing, backup the old file:

```bash
cd /Users/ray/github/openemr
cp library/MedEx/API.php library/MedEx/API.php.backup_20260123
```

---

### Step 4: Test Integration ✅ CRITICAL

#### Test 1: Recall Board - Basic Functions
1. Navigate to: Messages → Recalls
2. Click "Add Recall"
3. Select a patient
4. Fill in recall details
5. Save recall
6. Verify saved successfully
7. Edit the recall
8. Delete the recall
9. Monitor logs for errors

**Expected:** All operations work without PHP errors

#### Test 2: With MedEx Disabled
1. Set `$GLOBALS['medex_enable'] = '0'` in Globals
2. Navigate to: Messages → Recalls
3. Verify Recall Board loads
4. Create/edit/delete recall
5. Verify no MedEx messaging features appear

**Expected:** Recall Board works independently

#### Test 3: With MedEx Enabled
1. Set `$GLOBALS['medex_enable'] = '1'`
2. Navigate to: Messages → Recalls
3. Verify MedEx features appear (SMS bot, campaigns, etc.)
4. Test campaign creation
5. Test message generation

**Expected:** Full MedEx functionality works

#### Test 4: Flow Board
1. Navigate to: Patient Tracker (Flow Board)
2. Verify it loads without errors
3. Check if any MedEx functions are used

**Expected:** Flow Board works (may not actually use MedEx)

---

## Files Modified Summary

### 1. Add to EventsService
**File:** `/interface/modules/custom_modules/oe-module-medex/src/API/Services/EventsService.php`
**Action:** Add `delete_Recall()` method (see code above)
**Location:** After `save_recall()` method (around line 1075)

### 2. Create Bridge
**File:** `/library/MedEx/API.php`
**Action:** Replace entire file with bridge code
**Backup:** Save old file as `API.php.backup_20260123`

### 3. Background Service Path (deprecated)
The `background_services` entry and `MedEx_background.php` are deprecated. The module manages
external synchronization outside OpenEMR; do not update `background_services` for MedEx.

---

## Monitoring During Testing

### Terminal 1: OpenEMR PHP Errors
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

**Watch for:**
- Class not found errors
- Method not found errors
- Type errors
- SQL errors

### Terminal 2: MySQL Queries
```bash
docker compose exec openemr mysql -u root -proot openemr -e "SET GLOBAL general_log = 'ON';"
docker compose exec openemr tail -f /var/lib/mysql/queries.log | grep medex
```

**Watch for:**
- DELETE FROM medex_recalls
- DELETE FROM medex_outgoing
- INSERT/UPDATE to medex_recalls

### Terminal 3: Application Log
```bash
docker compose exec openemr tail -f /var/www/localhost/htdocs/openemr/sites/default/documents/logs/log
```

**Watch for:**
- MedEx Bridge Error messages
- Module not found errors

---

## Rollback Plan

If testing fails:

### Quick Rollback
```bash
cd /Users/ray/github/openemr
cp library/MedEx/API.php.backup_20260123 library/MedEx/API.php
```

### Verify Rollback
1. Refresh browser
2. Test Recall Board
3. Should work with old code

---

## Success Criteria

✅ **Integration is successful if:**

1. Recall Board loads without errors
2. Can create new recalls
3. Can edit existing recalls
4. Can delete recalls
5. Works with MedEx disabled (`medex_enable = 0`)
6. Works with MedEx enabled (`medex_enable = 1`)
7. Flow Board loads without errors
8. No PHP fatal errors in logs
9. No SQL errors in logs
10. Bridge file successfully loads module

---

## Post-Integration

After successful integration:

### 1. Update Documentation
- Update README.md
- Document bridge architecture
- Note that old API.php is now a bridge

### 2. PHPStan Verification
```bash
vendor/bin/phpstan analyze library/MedEx/API.php --level=6
```

**Expected:** 0 errors (bridge file is simple)

### 3. Code Review
- Review bridge implementation
- Verify all backward-compatible aliases work
- Ensure error handling is robust

### 4. Performance Testing
- Test with 100+ recalls
- Verify no performance degradation
- Check memory usage

---

## Future Refactoring (Phase 2)

After bridge is stable, consider:

### Option A: Extract to Core
Move recall functions to `/library/RecallBoard/`:
- `RecallFunctions::getAge()`
- `RecallFunctions::saveRecall()`
- `RecallFunctions::deleteRecall()`

### Option B: Make MedEx Optional
- Allow Recall Board to work without MedEx module
- Use dependency injection for MedEx features
- Graceful degradation when module disabled

### Option C: Keep Current Architecture
- Bridge works well
- Minimal maintenance needed
- Clear separation maintained

---

## Next Immediate Steps

1. ✅ Add `delete_Recall()` to EventsService
2. ✅ Create bridge file for `/library/MedEx/API.php`
3. ✅ Backup old file
4. ✅ Test in Docker environment
5. ✅ Monitor all 3 terminal windows
6. ✅ Walk through all test scenarios
7. ✅ Document results

---

## Questions for You

Before we proceed:

1. **Do you want me to add `delete_Recall()` to EventsService now?**
2. **Should we create the bridge file?**
3. **Are you ready to test in Docker?**
4. **Do you want to proceed with this approach or discuss alternatives?**

Let me know and we can continue!
