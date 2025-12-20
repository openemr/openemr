# Eye Mag Form - Multi-User Locking System Implementation Log

## Overview

This document describes the multi-user locking system implemented for the Eye Mag form to enable concurrent editing with proper access control. Multiple users can open the same form simultaneously, with one user having write access (ACTIVE) and others viewing read-only copies (READ-ONLY) with real-time polling updates.

## Architecture & Design

### Core Concept

The system operates on a simple but effective model:
- **One lock, one owner**: Only one user can have write access to a form at any given time
- **Lock holder**: The user with write access (ACTIVE state)
- **Lock watchers**: Other users viewing the form (READ-ONLY state)
- **Real-time sync**: READ-ONLY users poll the server every 15 seconds for field updates

### Key Design Decisions

1. **authUserID vs uniqueID**: System uses `$_SESSION['authUserID']` (actual OpenEMR user ID) for all lock comparisons, NOT random client-side IDs. This ensures server and client can reliably identify the lock owner.

2. **Persistent locks**: Locks persist in the database until explicitly released. A READ-ONLY user cannot accidentally cause the lock to transfer to another user by just viewing.

3. **Lock ownership transfer**: Only the current lock holder can release the lock. READ-ONLY users can attempt to acquire it via a confirmation dialog, which releases it from the current owner.

4. **State separation**: Chart state (ACTIVE/READ-ONLY) is separate from lock ownership. The lock owner can toggle to READ-ONLY and back without losing the lock (useful for reviewing form while allowing others to edit briefly).

## Database Layer

### Table: `form_eye_locking`

```sql
CREATE TABLE form_eye_locking (
  id INT PRIMARY KEY,
  LOCKED CHAR(1) DEFAULT '',     -- '1' = locked, '' = unlocked
  LOCKEDBY VARCHAR(255),          -- authUserID of lock owner
  LOCKEDDATE DATETIME             -- Timestamp of when lock was acquired
);
```

**Key properties:**
- `LOCKED = '1'` means someone holds write access
- `LOCKEDBY` contains the authUserID (OpenEMR user ID string)
- `LOCKEDDATE` stored in UTC, converted to user timezone on display

### Lock Lifecycle

1. **Form opened**: Server checks if locked
   - If unlocked: Current user acquires lock, sets `LOCKED='1'`, `LOCKEDBY=authUserID`, `LOCKEDDATE=NOW()`
   - If locked by someone else: User is prompted (page load) or can toggle to READ-ONLY

2. **User toggles ACTIVE → READ-ONLY**: 
   - `unlock()` called to release lock (sets `LOCKED=''`, `LOCKEDBY=''`)
   - Toggle UI to READ-ONLY (fields disabled, polling starts)
   - Other waiting users can now take the lock

3. **READ-ONLY user attempts to take over**:
   - Confirmation dialog shows lock holder's name
   - AJAX `acquire_lock` request sent
   - Server releases lock from previous owner and assigns to new user
   - New user's UI updates to ACTIVE

4. **Form closed or session ends**:
   - Lock remains in database (ownership persists)
   - Another user opening the form can take ownership if needed
   - Manual unlock may be needed for stale locks (1-hour timeout check available)

## Server-Side Implementation

### File: `interface/forms/eye_mag/view.php` (Lines 160-205)

**Lock Acquisition on Page Load:**

```php
// Check existing lock status
$lock = sqlQuery("SELECT LOCKED, LOCKEDBY, LOCKEDDATE FROM form_eye_locking WHERE id = ?", array($id));
$LOCKED = $lock['LOCKED'] ?? '';
$LOCKEDBY = $lock['LOCKEDBY'] ?? '';
$LOCKEDDATE = $lock['LOCKEDDATE'] ?? '';

// Check if current user owns the lock
$is_owner = ($LOCKEDBY === $_SESSION['authUserID']);

// If form is not locked, acquire it
if (!$LOCKED || !$LOCKEDBY) {
    $LOCKEDBY = $_SESSION['authUserID'];
    $LOCKED = '1';
    sqlQuery(
        "UPDATE form_eye_locking SET LOCKED='1', LOCKEDBY=?, LOCKEDDATE=NOW() WHERE id=?",
        array($_SESSION['authUserID'], $id)
    );
}
```

**Critical points:**
- Uses `$_SESSION['authUserID']` (actual user ID) for all comparisons
- Only acquires lock if form is completely unlocked
- Sets LOCKEDDATE to current time (stored in UTC)

**Lock Holder Display:**

```php
if ($is_owner) {
    // Current user owns the lock - show ACTIVE UI
    $warning = "nodisplay";  // Hide warning for lock owner
} else {
    // Another user owns the lock - show lock holder info
    $lock_user_query = "SELECT fname, lname FROM users WHERE id = ?";
    $lock_user = sqlQuery($lock_user_query, array($LOCKEDBY));
    $lock_user_name = ($lock_user) ? $lock_user['fname'] . ' ' . $lock_user['lname'] : 'Unknown User';
    
    // Convert LOCKEDDATE from UTC to user timezone
    $lock_time = new DateTime($LOCKEDDATE, new DateTimeZone('UTC'));
    $lock_time->setTimezone(new DateTimeZone(date_default_timezone_get()));
    $formatted_date = $lock_time->format('M d, Y @ h:i A');
    
    $warning_text = "LOCKED by $lock_user_name since $formatted_date. Enter READ-ONLY mode or take ownership.";
}
```

### File: `interface/forms/eye_mag/save.php` (Lines 430-460)

**Lock Validation on Save:**

```php
// Check if form is locked by someone else
$lock = sqlQuery("SELECT LOCKED, LOCKEDBY FROM form_eye_locking WHERE id = ?", array($form_id));

// Allow save only if current user owns the lock
if (($lock['LOCKED'] ?? '') && ($_SESSION['authUserID'] != $lock['LOCKEDBY'])) {
    // Get lock holder's name for error message
    $lock_holder = sqlQuery("SELECT fname, lname FROM users WHERE id = ?", array($lock['LOCKEDBY']));
    $lock_user_name = ($lock_holder) ? $lock_holder['fname'] . ' ' . $lock_holder['lname'] : 'Unknown User';
    
    echo "Code 400|" . $lock_user_name;  // Format: "Code 400|John Smith"
    exit;
}
```

**Lock Acquisition (when user takes over):**

```php
if ($_POST['acquire_lock'] == '1') {
    // Release lock from previous owner and assign to current user
    sqlQuery(
        "UPDATE form_eye_locking SET LOCKED='1', LOCKEDBY=?, LOCKEDDATE=NOW() WHERE id=?",
        array($_SESSION['authUserID'], $_POST['form_id'])
    );
    echo date('Y-m-d H:i:s');  // Return new LOCKEDDATE
    exit;
}
```

**Lock Release (when user toggles to READ-ONLY):**

```php
if ($_POST['unlock'] == '1') {
    sqlQuery(
        "UPDATE form_eye_locking SET LOCKED='', LOCKEDBY='', LOCKEDDATE='' WHERE id=?",
        array($_POST['form_id'])
    );
    echo "OK";
    exit;
}
```

## Client-Side Implementation

### File: `interface/forms/eye_mag/js/eye_base.php`

#### 1. Page Load Lock Check (Lines 2512-2560)

When the form loads, JavaScript checks if it's already locked by another user:

```javascript
$(function () {
    var locked = $("#LOCKED").val();           // '1' or ''
    var locked_by = $("#LOCKEDBY").val();      // authUserID of lock owner
    var authUserID = $('#authUserID').val();    // Current user's ID
    var locked_by_name = $("#LOCKEDBY_NAME").val();  // Lock holder's name

    if (locked == '1' && locked_by > '' && authUserID != locked_by) {
        // Form is locked by someone else - show popup
        var popup_msg = '\tLOCKED by ' + locked_by_name + ':\t\n\tSelect OK to take ownership or\t\n\tCANCEL to enter READ-ONLY mode.\t';
        
        if (confirm(popup_msg)) {
            // User chose to take ownership
            // AJAX acquire_lock request
            // On success: Update UI to ACTIVE, call toggle_active_flags("on")
        } else {
            // User chose READ-ONLY
            // Enter READ-ONLY mode: toggle_active_flags("off")
            // Start polling: update_READONLY() every 15 seconds
        }
    }
});
```

**Key flow:**
1. Read lock state from hidden form fields
2. If locked by another user, show popup with their name
3. Based on user choice, either take ownership or enter READ-ONLY mode

#### 2. Toggle Chart State (Lines 188-230)

Handles ACTIVE ↔ READ-ONLY transitions via toggle button:

```javascript
function toggle_chart_state() {
    var is_owner = ($("#LOCKEDBY").val() == $('#authUserID').val());
    
    if ($("#chart_status").val() == "on") {
        // Currently ACTIVE → switch to READ-ONLY
        unlock();  // Release lock from database
        toggle_active_flags("off");  // Update UI, start polling
    } else {
        // Currently READ-ONLY → try to switch to ACTIVE
        if (is_owner) {
            // You own the lock, just switch UI
            clearInterval(update_chart);
            toggle_active_flags("on");
        } else {
            // You don't own the lock, attempt to acquire it
            $.ajax({
                type: 'POST',
                url: "../../forms/eye_mag/save.php?mode=update&id=" + $("#form_id").val(),
                data: {
                    'acquire_lock': '1',
                    'uniqueID': authUserID,
                    'form_id': $("#form_id").val()
                }
            }).done(function(d) {
                $("#LOCKEDBY").val(authUserID);
                toggle_active_flags("on");
            });
        }
    }
}
```

**State transitions:**
- ACTIVE (own lock) → READ-ONLY: Release lock, stop owning it
- READ-ONLY (own lock) → ACTIVE: Keep lock, just update UI
- READ-ONLY (don't own lock) → ACTIVE: Attempt to acquire lock via AJAX

#### 3. Unlock Function (Lines 448-470)

Called when user toggles from ACTIVE to READ-ONLY:

```javascript
function unlock() {
    var formData = {
        'action': "unlock",
        'unlock': "1",
        'encounter': $('#encounter').val(),
        'pid': $('#pid').val(),
        'LOCKEDBY': $('#LOCKEDBY').val(),
        'form_id': $("#form_id").val()
    };
    
    $.ajax({
        type: 'POST',
        url: webroot + "/interface/forms/eye_mag/save.php?mode=update&id=" + $("#form_id").val(),
        data: formData,
        async: false
    }).done(function(o) {
        $('#LOCKEDBY').val('');  // Clear lock ownership
        // Don't manipulate warning here - let toggle_active_flags handle it
    });
}
```

**Important:** `unlock()` only clears the lock from the database. UI state is managed by `toggle_active_flags()`, ensuring consistent behavior.

#### 4. Toggle Active Flags (Lines 1970-2030)

Manages UI state for ACTIVE ↔ READ-ONLY modes:

```javascript
function toggle_active_flags(new_state) {
    if (new_state === "on") {
        // ACTIVE mode
        $("#chart_status").val("on");
        $("#active_flag").html(" Active Chart ");
        $("#active_icon").html("<i class='fa fa-toggle-on'></i>");
        $("#warning").addClass("nodisplay").hide();
        $("#COPY_SECTION").val("");  // Enable normal form submission
        $('input, select, textarea, a').removeAttr('disabled');
        $('input, textarea').removeAttr('readonly');
    } else {
        // READ-ONLY mode
        $("#chart_status").val("off");
        $("#active_flag").html(" READ-ONLY ");
        $("#active_icon").html("<i class='fa fa-toggle-off'></i>");
        $("#warning").removeClass("nodisplay").show();
        // Update warning message to generic READ-ONLY message
        $("#warning").find('h4').text('Warning! Form is in READ-ONLY mode.');
        $('input, select, textarea, a').attr('disabled', 'disabled');
        $('input, textarea').attr('readonly', 'readonly');
        $("#COPY_SECTION").val("READONLY-" + $("#form_id").val());  // Enable polling mode
    }
}
```

**UI effects:**
- ACTIVE: Toggle icon ON, warning hidden, fields enabled, can submit
- READ-ONLY: Toggle icon OFF, warning shown (explains field lockage), fields disabled, polling enabled

#### 5. Update READONLY (Lines 2050-2150)

Polls server every 15 seconds when form is in READ-ONLY mode:

```javascript
function update_READONLY() {
    if ($("#chart_status").val() != "off") {
        return;  // Not in READ-ONLY mode, skip
    }
    
    var formData = {
        'copy': 'ALL',           // Copy all zones
        'zone': 'ALL',
        'COPY_SECTION': 'READONLY'
    };
    
    $.ajax({
        type: 'POST',
        url: "../../forms/eye_mag/save.php?mode=update&id=" + $("#form_id").val(),
        data: formData
    }).done(function(response) {
        // Parse response and update changed fields
        // Highlight newly changed fields with purple background
        // Continue polling as long as in READ-ONLY mode
    });
}
```

**Polling behavior:**
- Only active when `chart_status === "off"`
- Fetches all form fields every 15 seconds
- Shows visual feedback (purple highlight) for changed fields
- Continues indefinitely until user switches back to ACTIVE

#### 6. Code 400 Handler (Lines 140-165)

Called when save fails due to lock held by another user:

```javascript
function code_400(lock_holder_name) {
    // Show alert with lock holder information
    alert('This form is being edited by ' + lock_holder_name + '. Please try again later.');
    
    // Enter READ-ONLY mode
    toggle_active_flags("off");
    
    // Start polling for updates
    update_chart = setInterval(function() {
        if ($("#chart_status").val() == "on") {
            clearInterval(update_chart);
        }
        update_READONLY();
    }, 15000);
}
```

**Usage:**
When form save is rejected with "Code 400|Lock Holder Name", the client parses the lock holder name and calls `code_400(name)` to inform the user and switch to READ-ONLY mode.

## Hidden Form Fields

The following hidden fields pass lock state from server to JavaScript:

```html
<!-- Lock state -->
<input type="hidden" id="LOCKED" value="1|''">
<input type="hidden" id="LOCKEDBY" value="[authUserID]">
<input type="hidden" id="LOCKEDDATE" value="[YYYY-MM-DD HH:MM:SS]">
<input type="hidden" id="LOCKEDBY_NAME" value="[First Last]">

<!-- User identification -->
<input type="hidden" id="authUserID" value="[OpenEMR user ID]">

<!-- Chart state -->
<input type="hidden" id="chart_status" value="on|off">

<!-- Polling control -->
<input type="hidden" id="COPY_SECTION" value="READONLY-[form_id]|''">
```

## Data Flow Diagrams

### Scenario 1: Browser 1 Opens Form

```
Browser 1 Load
    ↓
view.php: Check form_eye_locking
    ↓
LOCKED='' ? YES
    ↓
Acquire lock: LOCKED='1', LOCKEDBY='user1', LOCKEDDATE=NOW()
    ↓
JavaScript: locked=1, locked_by=user1, authUserID=user1 → ACTIVE ✓
    ↓
Form fully editable, can save
```

### Scenario 2: Browser 2 Opens Form (Browser 1 Still Has Lock)

```
Browser 2 Load
    ↓
view.php: Check form_eye_locking
    ↓
LOCKED='1' AND LOCKEDBY='user1' (not browser 2)
    ↓
Pass lock_user_name='User 1' to form
    ↓
JavaScript: locked=1, locked_by=user1, authUserID=user2
    ↓
Show popup: "LOCKED by User 1: OK to take or CANCEL for READ-ONLY"
    ↓
User clicks CANCEL
    ↓
toggle_active_flags("off") → READ-ONLY mode, polling starts ✓
```

### Scenario 3: Browser 2 User Takes Ownership

```
Browser 2: User clicks OK in popup
    ↓
JavaScript sends AJAX acquire_lock request
    ↓
save.php: UPDATE form_eye_locking SET LOCKED='1', LOCKEDBY='user2', LOCKEDDATE=NOW()
    ↓
Response: new LOCKEDDATE
    ↓
JavaScript: Update LOCKEDBY to user2, call toggle_active_flags("on")
    ↓
Browser 2 now ACTIVE, can save ✓
Browser 1 still has old LOCKEDBY=user1 in memory (needs refresh to see change)
```

### Scenario 4: Browser 1 User Toggles to READ-ONLY

```
Browser 1: User clicks toggle button (ACTIVE → READ-ONLY)
    ↓
toggle_chart_state() called
    ↓
AJAX unlock() request → SET LOCKED='', LOCKEDBY=''
    ↓
JavaScript: call toggle_active_flags("off")
    ↓
Browser 1 now READ-ONLY, polling starts ✓
Browser 2 (if waiting) can now acquire lock
```

## Common Issues & Solutions

### Issue 1: New Browser Auto-Acquires Lock
**Problem:** Second browser opening form immediately gets lock, even though first browser is still editing.

**Solution:** In `view.php`, only acquire lock if form is completely unlocked (`!$LOCKED || !$LOCKEDBY`). Don't overwrite existing lock just because current user isn't the owner.

### Issue 2: Third Browser Gets Lock Prematurely
**Problem:** First browser's READ-ONLY switch releases lock, third browser immediately grabs it.

**Solution:** Only release lock when explicitly requested (via `unlock()`), not when entering READ-ONLY mode. READ-ONLY users just watch; they don't touch the lock.

### Issue 3: Cannot Save After Acquiring Lock
**Problem:** Browser 2 acquires lock but save.php still rejects saves with "Code 400".

**Solution:** Clear `COPY_SECTION` when going ACTIVE. If `COPY_SECTION="READONLY-*"`, save is rejected before lock check. Line in `toggle_active_flags("on")`: `$("#COPY_SECTION").val("");`

### Issue 4: Toggle Button Fires Twice
**Problem:** Clicking toggle icon immediately reverts state change.

**Solution:** Add `e.stopPropagation()` to click handler. Both `#active_icon` and `#active_icon i` had handlers; click was bubbling to both.

### Issue 5: Stale Lock Holder Name in Banner
**Problem:** After releasing lock, warning banner still shows old lock holder.

**Solution:** In `toggle_active_flags("off")`, update warning message to generic text: `$("#warning").find('h4').text('Warning! Form is in READ-ONLY mode.');`

### Issue 6: authUserID vs uniqueID Mismatch
**Problem:** Server stores authUserID but JavaScript compares random uniqueID; locks always fail.

**Solution:** Replace ALL uniqueID with `$_SESSION['authUserID']` on server side. Pass actual authUserID to JavaScript via hidden field.

## Testing Checklist

- [ ] Browser 1 opens → acquires lock (ACTIVE)
- [ ] Browser 2 opens → sees lock holder name in popup
- [ ] Browser 2 chooses READ-ONLY → enters polling mode, fields disabled
- [ ] Browser 3 opens → sees lock holder name (still Browser 1)
- [ ] Browser 2 polls every 15s and displays updated fields
- [ ] Browser 1 makes field changes and saves successfully
- [ ] Browser 2 sees changes reflected in polled data (purple highlight)
- [ ] Browser 1 toggles to READ-ONLY → releases lock
- [ ] Browser 2 can now take ownership via toggle button
- [ ] Browser 2 acquires lock, UI updates to ACTIVE
- [ ] Browser 1 (now READ-ONLY) polls and sees Browser 2's changes
- [ ] Browser 1 toggles back to ACTIVE → attempts to reacquire lock via popup
- [ ] Lock holder name displays correctly in all popups/messages
- [ ] Timezone conversion works (lock time shows in user timezone, not UTC)

## Files Modified

1. **interface/forms/eye_mag/view.php**
   - Lines 160-205: Lock acquisition and display logic
   - Lines 402-405: Hidden fields for lock state and user name

2. **interface/forms/eye_mag/save.php**
   - Lines 430-460: Lock validation on save, acquire_lock handler, unlock handler

3. **interface/forms/eye_mag/js/eye_base.php**
   - Lines 140-165: code_400() function with lock holder name
   - Lines 188-230: toggle_chart_state() function with lock acquisition
   - Lines 448-470: unlock() function
   - Lines 1970-2030: toggle_active_flags() with warning message handling
   - Lines 2050-2150: update_READONLY() polling logic
   - Lines 2512-2560: Page load lock check with popup
   - Line 4677: Toggle button click handler with e.stopPropagation()

## Future Enhancements

1. **Stale Lock Cleanup**: Implement automatic cleanup of locks older than 1 hour (user session timeout)
2. **Lock Holder Notification**: Send message to lock holder when someone attempts takeover
3. **HPI Fields in Polling**: Add HPI_* fields to copy_forward() ALL zone so READ-ONLY users see HPI updates
4. **Visual Indicators**: Show active/inactive users list on form
5. **Conflict Resolution**: Handle simultaneous edits with merge strategy (last-write-wins or field-level tracking)

## References

- **OpenEMR API Documentation**: `library/api.inc`, `library/user.inc`
- **Database Layer**: `sqlStatement()`, `sqlQuery()`, `sqlFetchArray()` wrappers
- **Session Management**: `$_SESSION['authUserID']`, `top.restoreSession()`
- **Timezone Handling**: PHP `DateTime` class with DateTimeZone conversion
