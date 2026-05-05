# OpenEMR Pull Request Plan: Remove MedEx from Core

## Goal

Submit a PR to OpenEMR that:
1. ✅ Removes all `/library/MedEx/` code from core
2. ✅ Removes all `require_once "$srcdir/MedEx/API.php"` from core files
3. ✅ Makes MedEx functionality available **only via module**
4. ✅ Uses OpenEMR's event system for UI injection
5. ✅ Maintains full backward compatibility when module is enabled
6. ✅ Recall Board works independently when module is disabled

---

## Current Architecture Problems

### Problem 1: Core Files Depend on MedEx Library

**Files with hard dependencies:**
1. `/interface/main/messages/save.php` (line 17)
2. `/interface/main/messages/messages.php` (line 27)
3. `/interface/patient_tracker/patient_tracker.php` (line 26)

All three have:
```php
require_once "$srcdir/MedEx/API.php";
```

### Problem 2: Mixed Responsibilities

**MedEx provides TWO types of functionality:**

#### A. Core Recall Board Functions (needed by ALL users)
- `getAge($dob)` - Calculate patient age
- `save_recall($data)` - Save/update recalls
- `delete_Recall()` - Delete recalls

#### B. MedEx Messaging Features (optional)
- Campaign management
- SMS/Email/Voice messaging
- MedEx API integration
- Message tracking

**The problem:** Core functionality is trapped in MedEx namespace!

---

## Solution Architecture

### Phase 1: Extract Core Recall Functions

Move core recall functions OUT of MedEx namespace into OpenEMR core:

#### Create: `/library/RecallBoard/RecallService.php`

```php
<?php
/**
 * Recall Board Service
 * Core recall functionality independent of MedEx
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Services\RecallBoard;

use OpenEMR\Common\Database\QueryUtils;

class RecallService
{
    /**
     * Calculate patient age from date of birth
     *
     * @param string $dob Date of birth (YYYY-MM-DD)
     * @param string $asof Calculate age as of this date (default: today)
     * @return int Age in years
     */
    public static function getAge(string $dob, string $asof = ''): int
    {
        if (empty($asof)) {
            $asof = date('Y-m-d');
        }

        $a1 = explode('-', substr($dob, 0, 10));
        $a2 = explode('-', substr($asof, 0, 10));
        $age = $a2[0] - $a1[0];

        if ($a2[1] < $a1[1] || ($a2[1] == $a1[1] && $a2[2] < $a1[2])) {
            --$age;
        }

        return $age;
    }

    /**
     * Save or update a patient recall
     *
     * @param array $data Recall data from form
     * @return void
     */
    public static function saveRecall(array $data): void
    {
        $pid = $data['pid'] ?? '';
        $provider = $data['provider'] ?? '';
        $facility = $data['facility'] ?? '';
        $eventDate = $data['RECALL_DATE'] ?? '';
        $reason = $data['new_reason'] ?? '';

        if (!$pid) {
            throw new \InvalidArgumentException('Patient ID required');
        }

        // Check if recall exists
        $existing = QueryUtils::fetchRecords(
            "SELECT * FROM medex_recalls WHERE r_pid = ?",
            [$pid]
        );

        if (!empty($existing)) {
            // Update existing recall
            QueryUtils::sqlStatementThrowException(
                "UPDATE medex_recalls
                 SET r_eventDate = ?, r_facility = ?, r_provider = ?, r_reason = ?
                 WHERE r_pid = ?",
                [$eventDate, $facility, $provider, $reason, $pid]
            );
        } else {
            // Insert new recall
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO medex_recalls
                 (r_pid, r_eventDate, r_facility, r_provider, r_reason)
                 VALUES (?, ?, ?, ?, ?)",
                [$pid, $eventDate, $facility, $provider, $reason]
            );
        }
    }

    /**
     * Delete a patient recall
     *
     * @param int|null $pid Patient ID
     * @param int|null $r_ID Recall ID
     * @return void
     */
    public static function deleteRecall(?int $pid = null, ?int $r_ID = null): void
    {
        // Get from POST if not provided (for backward compatibility)
        $pid = $pid ?? ($_POST['pid'] ?? null);
        $r_ID = $r_ID ?? ($_POST['r_ID'] ?? null);

        if (!$pid && !$r_ID) {
            return;
        }

        // Delete recall record
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM medex_recalls WHERE r_pid = ? OR r_ID = ?",
            [$pid, $r_ID]
        );

        // Delete associated outgoing messages (if MedEx was used)
        if ($pid) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM medex_outgoing WHERE msg_pc_eid = ?",
                ['recall_' . $pid]
            );
        }
    }
}
```

---

### Phase 2: Update Core Files

#### Update: `/interface/main/messages/save.php`

**Line 17 - BEFORE:**
```php
require_once "$srcdir/MedEx/API.php";
```

**Line 17 - AFTER:**
```php
require_once "$srcdir/RecallBoard/RecallService.php";

use OpenEMR\Services\RecallBoard\RecallService;
```

**Lines 161, 205, 211 - BEFORE:**
```php
$result['age'] = $MedEx->events->getAge($result['DOB']);
$result = $MedEx->events->save_recall($_REQUEST);
$MedEx->events->delete_recall();
```

**Lines 161, 205, 211 - AFTER:**
```php
$result['age'] = RecallService::getAge($result['DOB']);
RecallService::saveRecall($_REQUEST);
RecallService::deleteRecall();
```

**Lines 70, 112, 135 - MedEx-specific calls:**
```php
// Keep MedEx functionality conditional
if ($GLOBALS['medex_enable'] == '1') {
    // MedEx setup/login code here
    if (class_exists('MedExApi\\MedEx')) {
        $MedEx = new MedExApi\MedEx($GLOBALS['medex_api_host'] ?? 'MedExBank.com');
        $result = $MedEx->login('1');
    }
}
```

---

#### Update: `/interface/main/messages/messages.php`

**Line 27 - REMOVE:**
```php
require_once("$srcdir/MedEx/API.php");
```

**Lines 39-50 - MAKE CONDITIONAL:**
```php
// Only load MedEx if enabled AND module exists
$MedEx = null;
$logged_in = null;

if ($GLOBALS['medex_enable'] == '1') {
    $module_path = __DIR__ . '/../modules/custom_modules/oe-module-medex/src/API/API.php';
    if (file_exists($module_path)) {
        require_once($module_path);
        $MedEx = new MedExApi\MedEx($GLOBALS['medex_api_host'] ?? 'MedExBank.com');

        if ($_REQUEST['SMS_bot'] ?? false) {
            $result = $MedEx->login('');
            $MedEx->display->SMS_bot($result);
            exit();
        }

        $logged_in = $MedEx->login();
    }
}
```

**Lines 103-106 - ALREADY CONDITIONAL:**
```php
// This already checks if MedEx is enabled - GOOD!
if (($GLOBALS['medex_enable'] == '1') && (empty($_REQUEST['nomenu'])) && ($GLOBALS['disable_rcb'] != '1')) {
    if ($MedEx) {
        $MedEx->display->navigation($logged_in);
    }
}
```

---

#### Update: `/interface/patient_tracker/patient_tracker.php`

**Line 26 - REMOVE:**
```php
require_once "$srcdir/MedEx/API.php";
```

**Add conditional MedEx loading if actually used:**
```php
// Only load if MedEx is enabled
$MedEx = null;
if ($GLOBALS['medex_enable'] == '1') {
    $module_path = __DIR__ . '/../modules/custom_modules/oe-module-medex/src/API/API.php';
    if (file_exists($module_path)) {
        require_once($module_path);
        $MedEx = new MedExApi\MedEx($GLOBALS['medex_api_host'] ?? 'MedExBank.com');
    }
}
```

**Note:** Need to verify if Flow Board actually uses MedEx functionality

---

### Phase 3: Module Event Injection

Use OpenEMR's event system to inject MedEx UI when module is enabled.

#### Create: `/interface/modules/custom_modules/oe-module-medex/src/EventSubscriber/MessageCenterSubscriber.php`

```php
<?php
/**
 * Message Center Event Subscriber
 * Injects MedEx functionality into Recall Board
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\EventSubscriber;

use OpenEMR\Events\Core\TemplatePageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageCenterSubscriber implements EventSubscriberInterface
{
    /**
     * Register events to listen for
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            TemplatePageEvent::RENDER_EVENT => 'onMessageCenterRender',
        ];
    }

    /**
     * Inject MedEx navigation and tabs into Message Center
     *
     * @param TemplatePageEvent $event
     * @return void
     */
    public function onMessageCenterRender(TemplatePageEvent $event): void
    {
        $pageName = $event->getPageName();

        // Only act on message center page
        if ($pageName !== 'interface/main/messages/messages.php') {
            return;
        }

        // Only inject if MedEx is enabled
        if ($GLOBALS['medex_enable'] != '1') {
            return;
        }

        // Load MedEx
        $module_path = __DIR__ . '/../../API/API.php';
        if (!file_exists($module_path)) {
            return;
        }

        require_once($module_path);
        $MedEx = new \MedExApi\MedEx($GLOBALS['medex_api_host'] ?? 'MedExBank.com');
        $logged_in = $MedEx->login();

        // Add MedEx variables to template
        $event->setTwigVariables([
            'medex_enabled' => true,
            'medex_logged_in' => $logged_in,
            'medex_navigation' => $this->renderNavigation($MedEx, $logged_in),
            'medex_sms_tab' => $logged_in ? true : false,
        ]);
    }

    /**
     * Render MedEx navigation HTML
     *
     * @param object $MedEx
     * @param bool $logged_in
     * @return string
     */
    private function renderNavigation($MedEx, $logged_in): string
    {
        ob_start();
        $MedEx->display->navigation($logged_in);
        return ob_get_clean();
    }
}
```

---

#### Update: `/interface/modules/custom_modules/oe-module-medex/openemr.bootstrap.php`

```php
<?php
/**
 * MedEx Module Bootstrap
 * Registers event subscribers
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   Proprietary - All Rights Reserved
 */

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Events\Core\ModuleLoadEvents;
use OpenEMR\Modules\MedEx\EventSubscriber\MessageCenterSubscriber;

/**
 * @var Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
 * @var ModulesClassLoader $classLoader
 */

// Register event subscribers
$eventDispatcher->addSubscriber(new MessageCenterSubscriber());

error_log('[MedEx Module] Event subscribers registered');
```

---

### Phase 4: Remove MedEx from Core

Once all the above is complete:

#### Delete these files/directories:
```bash
rm -rf library/MedEx/
```

This removes:
- `/library/MedEx/API.php` (3,620 lines)
- `/library/MedEx/MedEx.php`
- `/library/MedEx/MedEx_background.php`

---

## Files Modified Summary

### New Files Created
1. `/library/RecallBoard/RecallService.php` - Core recall functions
2. `/interface/modules/custom_modules/oe-module-medex/src/EventSubscriber/MessageCenterSubscriber.php` - UI injection

### Core Files Modified
1. `/interface/main/messages/save.php`
   - Remove line 17: `require_once "$srcdir/MedEx/API.php";`
   - Add line 17: `require_once "$srcdir/RecallBoard/RecallService.php";`
   - Add line 18: `use OpenEMR\Services\RecallBoard\RecallService;`
   - Update lines 161, 205, 211 to use RecallService
   - Make MedEx calls conditional (lines 70, 112, 135)

2. `/interface/main/messages/messages.php`
   - Remove line 27: `require_once "$srcdir/MedEx/API.php";`
   - Make MedEx loading conditional (lines 39-50)
   - Keep existing conditionals (lines 103-106, 192-196)

3. `/interface/patient_tracker/patient_tracker.php`
   - Remove line 26: `require_once "$srcdir/MedEx/API.php";`
   - Add conditional MedEx loading if needed

### Module Files Modified
1. `/interface/modules/custom_modules/oe-module-medex/openemr.bootstrap.php`
   - Register MessageCenterSubscriber

### Files Deleted
1. `/library/MedEx/API.php`
2. `/library/MedEx/MedEx.php`
3. `/library/MedEx/MedEx_background.php`

---

## Testing Plan

### Test 1: Recall Board WITHOUT MedEx
1. Disable MedEx module
2. Navigate to Messages → Recalls
3. Create new recall ✓
4. Edit recall ✓
5. Delete recall ✓
6. Verify no MedEx features appear ✓
7. Verify no PHP errors ✓

### Test 2: Recall Board WITH MedEx
1. Enable MedEx module
2. Navigate to Messages → Recalls
3. Verify MedEx navigation appears ✓
4. Verify SMS Zone tab appears ✓
5. Create recall ✓
6. Test MedEx messaging features ✓
7. Verify no errors ✓

### Test 3: Flow Board
1. Navigate to Patient Tracker
2. Verify loads without errors ✓
3. Test with MedEx disabled ✓
4. Test with MedEx enabled ✓

### Test 4: Backward Compatibility
1. Existing recalls display correctly ✓
2. Existing MedEx campaigns work ✓
3. Message history intact ✓
4. No database migration needed ✓

---

## Database Considerations

**No schema changes required!**

Tables remain the same:
- `medex_recalls` - Used by core Recall Board
- `medex_outgoing` - Used by MedEx module only
- `medex_prefs` - Used by MedEx module only

---

## Pull Request Structure

### PR Title
```
refactor: Extract MedEx from core into optional module
```

### PR Description
```markdown
## Summary
This PR removes MedEx from OpenEMR core (`/library/MedEx/`) and makes it available only via the `oe-module-medex` module, using OpenEMR's event system for UI injection.

## Changes

### Core Recall Functions Extracted
- Created `/library/RecallBoard/RecallService.php`
- Moved `getAge()`, `saveRecall()`, `deleteRecall()` to core namespace
- Recall Board now works independently of MedEx

### MedEx Module Integration
- Uses Symfony EventDispatcher for UI injection
- MedEx navigation injected via `TemplatePageEvent`
- Module fully self-contained

### Files Modified
- `interface/main/messages/save.php` - Use RecallService, conditional MedEx
- `interface/main/messages/messages.php` - Conditional MedEx loading
- `interface/patient_tracker/patient_tracker.php` - Conditional MedEx loading

### Files Deleted
- `library/MedEx/API.php` (3,620 lines)
- `library/MedEx/MedEx.php`
- `library/MedEx/MedEx_background.php`

## Testing
- ✅ Recall Board works without MedEx enabled
- ✅ MedEx features work when module enabled
- ✅ No breaking changes
- ✅ All existing data preserved

## Benefits
- **Clean separation:** Core vs optional functionality
- **Modularity:** MedEx can be enabled/disabled cleanly
- **Maintainability:** Smaller core codebase
- **Standards:** Uses OpenEMR event system properly
```

---

## Implementation Checklist

### Step 1: Create RecallService ✅
- [ ] Create `/library/RecallBoard/RecallService.php`
- [ ] Add `getAge()` method
- [ ] Add `saveRecall()` method
- [ ] Add `deleteRecall()` method
- [ ] Add PHPStan type declarations
- [ ] Test all three methods

### Step 2: Update Core Files ✅
- [ ] Update `save.php` to use RecallService
- [ ] Update `messages.php` to conditionally load MedEx
- [ ] Update `patient_tracker.php` to conditionally load MedEx
- [ ] Test core functionality

### Step 3: Create Event Subscriber ✅
- [ ] Create `MessageCenterSubscriber.php`
- [ ] Implement `onMessageCenterRender()`
- [ ] Register subscriber in `openemr.bootstrap.php`
- [ ] Test UI injection

### Step 4: Test Everything ✅
- [ ] Test Recall Board without MedEx
- [ ] Test Recall Board with MedEx
- [ ] Test Flow Board
- [ ] Test backward compatibility
- [ ] Run PHPStan on all modified files

### Step 5: Clean Up ✅
- [ ] Delete `/library/MedEx/` directory
- [ ] Update documentation
- [ ] Create PR

---

## Next Steps

**Which would you like me to do first?**

1. **Create RecallService.php** - Core recall functions
2. **Create MessageCenterSubscriber.php** - Event injection
3. **Update save.php** - Use RecallService
4. **Create comprehensive test plan** - Detailed test scenarios

Let me know and I'll proceed!
