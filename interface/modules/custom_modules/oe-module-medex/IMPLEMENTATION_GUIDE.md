# MedEx Module: Implementation Guide for Core File Modifications

## Overview

This guide provides step-by-step instructions for modifying OpenEMR core files to dispatch events that the MedEx module listens for. This is **Phase 2** of the extraction process.

## Prerequisites

- **Phase 1 Complete**: Event classes, listeners, and module bootstrap updated
- **Backup Created**: Full backup of `/interface/main/messages/` and `/interface/patient_tracker/`
- **Module Enabled**: MedEx module is installed and enabled in OpenEMR
- **Test Environment**: Changes should be tested in non-production environment first

## Part 1: Modify messages.php

### File: `/Users/ray/github/openemr/interface/main/messages/messages.php`

### Changes Required

#### 1. Remove Legacy MedEx Require Statement

**Location**: Line 27
**Current Code**:
```php
require_once("$srcdir/MedEx/API.php");
```

**New Code**:
```php
// MedEx API no longer required - handled by module via events
// require_once("$srcdir/MedEx/API.php");
```

**Rationale**: The module will provide all MedEx functionality via event listeners.

---

#### 2. Replace MedEx Instantiation

**Location**: Lines 39-50
**Current Code**:
```php
$MedEx = new MedExApi\MedEx($GLOBALS['medex_api_host'] ?? 'MedExBank.com');

if ($GLOBALS['medex_enable'] == '1') {
    if ($_REQUEST['SMS_bot']) {
        $result = $MedEx->login('');
        $MedEx->display->SMS_bot($result);
        exit();
    }
    $logged_in = $MedEx->login();
} else {
    $logged_in = null;
}
```

**New Code**:
```php
// MedEx functionality provided by module via events
use OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent;

$medex_enabled = ($GLOBALS['medex_enable'] == '1');
$logged_in = null;

// Early exit for SMS Bot - let module handle this
if ($medex_enabled && !empty($_REQUEST['SMS_bot'])) {
    // Dispatch event for SMS Bot page
    $event = new MessagesPageRenderEvent(
        MessagesPageRenderEvent::INJECT_CONTENT,
        $_REQUEST,
        $medex_enabled,
        null
    );
    $event = $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, MessagesPageRenderEvent::EVENT_RENDER);

    if ($event->hasContent()) {
        echo $event->getContent();
        exit();
    }
}
```

---

#### 3. Inject Navigation Bar

**Location**: After line 101 (in `<head>` section, before `<style>`)
**Current Code**:
```php
<?php
if (($GLOBALS['medex_enable'] == '1') && (empty($_REQUEST['nomenu'])) && ($GLOBALS['disable_rcb'] != '1')) {
    $MedEx->display->navigation($logged_in);
    echo "<br /><br /><br />";
}
```

**New Code**:
```php
<?php
// Dispatch event for MedEx navigation
if ($medex_enabled && empty($_REQUEST['nomenu']) && ($GLOBALS['disable_rcb'] != '1')) {
    $event = new MessagesPageRenderEvent(
        MessagesPageRenderEvent::INJECT_NAVIGATION,
        $_REQUEST,
        $medex_enabled,
        $logged_in
    );
    $event = $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, MessagesPageRenderEvent::EVENT_RENDER);

    if ($event->hasContent()) {
        echo $event->getContent();
        echo "<br /><br /><br />";
    }
}
```

---

#### 4. Replace MedEx Content Pages

**Location**: Lines 108-137 (inside `if (!empty($_REQUEST['go']))` block)
**Current Code**:
```php
if (($_REQUEST['go'] == "setup") && (!$logged_in)) {
    echo "<title>" . xlt('MedEx Setup') . "</title>";
    $stage = $_REQUEST['stage'];
    if (!is_numeric($stage)) {
        echo "<br /><span class='title'>" . text($stage) . " " . xlt('Warning') . ": " . xlt('This is not a valid request') . ".</span>";
    } else {
        $MedEx->setup->MedExBank($stage);
    }
} elseif ($_REQUEST['go'] == "addRecall") {
    echo "<title>" . xlt('New Recall') . "</title>";
    $MedEx->display->display_add_recall();
} elseif ($_REQUEST['go'] == 'Recalls') {
    echo "<title>" . xlt('Recall Board') . "</title>";
    $MedEx->display->display_recalls($logged_in);
} elseif ((($_REQUEST['go'] == "setup") || ($_REQUEST['go'] == 'Preferences')) && ($logged_in)) {
    echo "<title>MedEx: " . xlt('Preferences') . "</title>";
    $MedEx->display->preferences();
} elseif ($_REQUEST['go'] == 'icons') {
    echo "<title>MedEx: " . xlt('Icons') . "&#x24B8;</title>";
    $MedEx->display->icon_template();
} elseif ($_REQUEST['go'] == 'SMS_bot') {
    echo "<title>MedEx: SMS Bot&#x24B8;</title>";
    $MedEx->display->SMS_bot($logged_in);
    exit;
} else {
    echo "<title>" . xlt('MedEx Setup') . "</title>";
    echo xlt('Warning: Navigation error. Please refresh this page.');
}
```

**New Code**:
```php
// Dispatch event for MedEx content pages
$event = new MessagesPageRenderEvent(
    MessagesPageRenderEvent::INJECT_CONTENT,
    $_REQUEST,
    $medex_enabled,
    $logged_in
);
$event = $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, MessagesPageRenderEvent::EVENT_RENDER);

if ($event->hasContent()) {
    // Set appropriate title based on page
    $go = $_REQUEST['go'] ?? '';
    $titles = [
        'setup' => xlt('MedEx Setup'),
        'addRecall' => xlt('New Recall'),
        'Recalls' => xlt('Recall Board'),
        'Preferences' => 'MedEx: ' . xlt('Preferences'),
        'icons' => 'MedEx: ' . xlt('Icons'),
        'SMS_bot' => 'MedEx: SMS Bot'
    ];
    echo "<title>" . ($titles[$go] ?? xlt('MedEx')) . "</title>";
    echo $event->getContent();
}
```

---

### Summary of messages.php Changes

1. Comment out legacy `require_once` for MedEx API
2. Replace MedEx instantiation with event dispatcher
3. Replace navigation call with event dispatch
4. Replace all MedEx content pages with event dispatch
5. Add `use` statement for `MessagesPageRenderEvent`

**Lines Modified**: 27, 39-50, 103-105, 108-137
**Lines Added**: ~30 lines of event dispatching code
**Lines Removed**: ~60 lines of legacy MedEx calls

---

## Part 2: Modify patient_tracker.php

### File: `/Users/ray/github/openemr/interface/patient_tracker/patient_tracker.php`

### Changes Required

#### 1. Remove Legacy MedEx Require Statement

**Location**: Line 26
**Current Code**:
```php
require_once "$srcdir/MedEx/API.php";
```

**New Code**:
```php
// MedEx API no longer required - handled by module via events
// require_once "$srcdir/MedEx/API.php";
```

---

#### 2. Replace MedEx Initialization

**Location**: Lines 120-134
**Current Code**:
```php
if ($GLOBALS['medex_enable'] == '1') {
    $query2 = "SELECT * FROM medex_icons";
    $iconed = sqlStatement($query2);
    while ($icon = sqlFetchArray($iconed)) {
        $icons[$icon['msg_type']][$icon['msg_status']]['html'] = $icon['i_html'];
    }
    $MedEx = new MedExApi\MedEx($GLOBALS['medex_api_host'] ?? 'MedExBank.com');
    $sql = "SELECT * FROM medex_prefs LIMIT 1";
    $preferences = sqlStatement($sql);
    $prefs = sqlFetchArray($preferences);
    $results = json_decode((string) $prefs['status'], true);
    $logged_in = $results;
    $logged_in = $results;
    $current_events = !empty($logged_in['token']) ? xlt("On-line") : xlt("Currently off-line");
}
```

**New Code**:
```php
use OpenEMR\Modules\MedEx\Events\PatientTrackerPageRenderEvent;

$medex_enabled = ($GLOBALS['medex_enable'] == '1');
$logged_in = null;
$icons = [];

if ($medex_enabled) {
    // Load MedEx icons for display
    $query2 = "SELECT * FROM medex_icons";
    $iconed = sqlStatement($query2);
    while ($icon = sqlFetchArray($iconed)) {
        $icons[$icon['msg_type']][$icon['msg_status']]['html'] = $icon['i_html'];
    }

    // Load MedEx login status
    $sql = "SELECT * FROM medex_prefs LIMIT 1";
    $preferences = sqlStatement($sql);
    $prefs = sqlFetchArray($preferences);
    $results = json_decode((string) $prefs['status'], true);
    $logged_in = $results;
}
```

---

#### 3. Inject Navigation Bar

**Location**: Lines 158-162
**Current Code**:
```php
<?php
if (($GLOBALS['medex_enable'] == '1') && (empty($_REQUEST['nomenu']))) {
    $logged_in = $MedEx->login();
    $MedEx->display->navigation($logged_in);
}
?>
```

**New Code**:
```php
<?php
// Dispatch event for MedEx navigation
if ($medex_enabled && empty($_REQUEST['nomenu'])) {
    $event = new PatientTrackerPageRenderEvent(
        PatientTrackerPageRenderEvent::INJECT_NAVIGATION,
        [],
        $medex_enabled,
        $logged_in
    );
    $event = $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, PatientTrackerPageRenderEvent::EVENT_RENDER);

    if ($event->hasContent()) {
        echo $event->getContent();
    }
}
?>
```

---

#### 4. Inject MedEx Online Status

**Location**: Lines 297-303
**Current Code**:
```php
<?php if ($GLOBALS['medex_enable'] == '1') { ?>
  <b>MedEx:</b>
        <a href="https://medexbank.com/cart/upload/index.php?route=information/campaigns&amp;g=rem"
           target="_medex">
            <?php echo $current_events; ?>
        </a>
  <?php } ?>
```

**New Code**:
```php
<?php
// Dispatch event for MedEx online status
if ($medex_enabled) {
    $event = new PatientTrackerPageRenderEvent(
        PatientTrackerPageRenderEvent::INJECT_ONLINE_STATUS,
        [],
        $medex_enabled,
        $logged_in
    );
    $event = $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, PatientTrackerPageRenderEvent::EVENT_RENDER);

    if ($event->hasContent()) {
        echo $event->getContent();
    }
}
?>
```

---

#### 5. Inject MedEx Status Icons in Flow Board

**Location**: Lines 684-689 (inside the appointment loop, in the "Current Status" column)
**Current Code**:
```php
} elseif (($icon_here ?? null) || ($icon2_here ?? null)) {
    echo "<span style='font-size:0.7rem;' onclick='return calendarpopup(" . attr_js($appt_eid) . "," . attr_js($date_squash) . ")'>" . implode('', $icon_here) . $icon2_here . "</span> " . $icon_4_CALL;
} elseif ($logged_in ?? null) {
    $pat = $MedEx->display->possibleModalities($appointment);
    echo "<span style='font-size:0.7rem;' onclick='return calendarpopup(" . attr_js($appt_eid) . "," . attr_js($date_squash) . ")'>" . $pat['SMS'] . $pat['AVM'] . $pat['EMAIL'] . "</span>";
}
```

**New Code**:
```php
} elseif ($medex_enabled) {
    // Dispatch event for MedEx status icons/modalities
    $event = new PatientTrackerPageRenderEvent(
        PatientTrackerPageRenderEvent::INJECT_STATUS_ICONS,
        $appointment,
        $medex_enabled,
        $logged_in
    );
    $event->setIcons($icons);
    $event = $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, PatientTrackerPageRenderEvent::EVENT_RENDER);

    if ($event->hasContent()) {
        echo $event->getContent();
    }
}
```

**Note**: The complex icon generation logic (lines 462-689) can remain in place temporarily, or be moved entirely to the event listener for cleaner separation.

---

### Summary of patient_tracker.php Changes

1. Comment out legacy `require_once` for MedEx API
2. Simplify MedEx initialization (keep icon/status loading, remove MedEx object)
3. Replace navigation call with event dispatch
4. Replace online status display with event dispatch
5. Replace status icons in flow board with event dispatch
6. Add `use` statement for `PatientTrackerPageRenderEvent`

**Lines Modified**: 26, 120-134, 158-162, 297-303, 684-689
**Lines Added**: ~40 lines of event dispatching code
**Lines Removed**: ~20 lines of legacy MedEx calls

---

## Part 3: Modify save.php (Optional for Now)

### File: `/Users/ray/github/openemr/interface/main/messages/save.php`

This file handles AJAX requests from messages.php. It can be updated later as part of Phase 3.

**Current Dependencies**:
- Line 17: `require_once "$srcdir/MedEx/API.php";`
- Line 22: `$MedEx = new MedExApi\MedEx(...);`

**Action**: Leave unchanged for now. The module's MedExAPI.php provides backward compatibility.

---

## Testing Procedures

### Test 1: Module Disabled

```bash
# Disable MedEx module
# Navigate to: Admin > Modules > Custom Modules
# Click "Disable" on MedEx module
```

**Expected Results**:
- messages.php loads without errors
- patient_tracker.php loads without errors
- No MedEx navigation bar visible
- No MedEx status icons in flow board
- No console errors in browser

---

### Test 2: Module Enabled - Messages Page

```bash
# Enable MedEx module
# Navigate to: Messages
```

**Test Cases**:
1. **Navigation Bar**: Should appear at top of page
2. **Recall Board**: Click "Recall Board" link - should load
3. **New Recall**: Click "New Recall" button - should load form
4. **Preferences**: Navigate to Preferences - should load
5. **SMS Bot**: Click SMS Bot tab - should load interface

**Expected Results**:
- All MedEx pages load without errors
- Navigation is functional
- No PHP errors in error_log
- Event dispatchers working correctly

---

### Test 3: Module Enabled - Patient Tracker

```bash
# Navigate to: Patient Tracker
```

**Test Cases**:
1. **Navigation Bar**: Should appear at top of page
2. **Online Status**: MedEx online/offline status should display
3. **Status Icons**: For scheduled appointments, reminder icons should appear
4. **Modalities**: For appointments without room assignment, possible communication methods should show

**Expected Results**:
- All MedEx icons display correctly
- Status colors match appointment communication state
- No PHP errors in error_log
- Clicking icons opens appropriate dialogs

---

### Test 4: Background Service (deprecated)

The legacy `background_services` entry for MedEx is deprecated.
This module no longer depends on OpenEMR's `background_services` system.
External synchronization is managed outside the OpenEMR background process.

---

## Troubleshooting

### Issue: Events Not Firing

**Symptoms**: MedEx content not appearing, no errors
**Causes**:
1. Event listeners not registered in bootstrap
2. Incorrect event class namespace
3. Event dispatcher not available

**Solutions**:
1. Check error_log for "[MedEx] ...listener registered" messages
2. Verify `use` statements in core files
3. Ensure `$GLOBALS['kernel']` is available

---

### Issue: Navigation Not Appearing

**Symptoms**: No MedEx navigation bar
**Causes**:
1. Event not dispatched at correct location
2. `$medex_enabled` is false
3. Template file missing

**Solutions**:
1. Verify event dispatch code is after `</head>` tag
2. Check `$GLOBALS['medex_enable']` == '1'
3. Verify `/src/templates/navigation.php` exists

---

### Issue: Icons Not Showing in Patient Tracker

**Symptoms**: No reminder icons in flow board
**Causes**:
1. Icons array not passed to event
2. medex_outgoing table empty
3. Event not dispatched in appointment loop

**Solutions**:
1. Add `$event->setIcons($icons)` before dispatch
2. Verify data in medex_outgoing table
3. Check event dispatch is inside foreach loop

---

## Rollback Procedure

If issues occur:

```bash
# 1. Restore backup files
cp /path/to/backup/messages.php /Users/ray/github/openemr/interface/main/messages/messages.php
cp /path/to/backup/patient_tracker.php /Users/ray/github/openemr/interface/patient_tracker/patient_tracker.php

# 2. Disable module
# Navigate to: Admin > Modules > Custom Modules
# Click "Disable" on MedEx module

# 3. Clear cache
rm -rf /tmp/openemr/*

# 4. Restart Apache
sudo systemctl restart apache2
```

---

## Success Criteria

- [ ] messages.php loads with module disabled
- [ ] patient_tracker.php loads with module disabled
- [ ] MedEx navigation appears with module enabled
- [ ] Recall Board accessible and functional
- [ ] SMS Bot accessible and functional
- [ ] Patient Tracker shows reminder icons
- [ ] Online/offline status displays correctly
- [ ] No PHP errors in error_log
- [ ] No JavaScript console errors
- [ ] Background service continues to run

---

## Next Steps (Phase 3)

After successful testing:

1. Remove all legacy MedEx code from core files
2. Delete `/library/MedEx/` directory
3. Update all references to legacy API
4. Move remaining display methods to module
5. Update background service path
6. Final testing and validation

---

## Notes

- **Backup First**: Always backup files before modification
- **Test Environment**: Test in non-production environment
- **Incremental Changes**: Make changes incrementally, test after each change
- **Version Control**: Use git to track changes
- **Documentation**: Update this guide based on learnings

---

## Support

For issues or questions:
- Review ARCHITECTURE.md for design decisions
- Check OpenEMR logs: `/var/log/apache2/error.log`
- Review MedEx logs: `/tmp/medex.log` (if debug enabled)
- Contact MedEx support: support@medexbank.com

---

## Revision History

- **2026-01-22**: Initial implementation guide created
- Phase 2 implementation instructions documented
- Testing procedures defined
