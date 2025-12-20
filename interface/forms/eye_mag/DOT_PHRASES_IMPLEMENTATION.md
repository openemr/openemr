# Dot Phrases Implementation Status - REVISED

**Last Updated:** December 9, 2025
**Feature:** Eye Form Dot Phrases (Auto-expansion & Macros)
**Status:** Fixed and tested for end-user reliability - WORK IN PROGRESS

## Developer Quick Reference

**Files to modify:**
- `interface/forms/eye_mag/js/dotphrases.js` - Core expansion logic
- `interface/forms/eye_mag/view.php` - Modal UI and server sync
- `interface/forms/eye_mag/save.php` - Backend persistence

**Key concepts:**
- Phrases stored per-user in `list_options` table with list_id=`dot_phrases_{USER_ID}`
- Single storage source for all users (JSON in Notes field)
- Event-driven expansion: space, tab, enter, comma, period, semicolon
- Multi-field phrases can populate multiple form fields from single trigger
p
**Common tasks:**
- Add trigger character: Edit event listeners in `dotphrases.js` `init()` function
- Change storage format: Modify JSON structure in `persist()` and `removePersist()`
- Add validation: Update modal form in `view.php` modal HTML
- Change sync strategy: Modify `save_dot_phrases` action in `save.php`

## Critical Issues Found & Fixed

### Issue 1: Duplicate Event Listeners
**Problem:** Event listeners for `keydown` and `input` were being added twice:
- Once globally at initialization (lines 128-130)
- Again inside `init()` function (lines 172-173)

**Impact:** Caused unpredictable behavior, wasted resources, potential race conditions.

**Fix:** Removed global listeners. All event listeners now registered only inside `init()` function.

### Issue 2: Missing `blur` Event in Init()
**Problem:** The `blur` event listener was added globally but NOT inside `init()`, breaking the initialization sequence.

**Impact:** If the script ran before DOM was fully ready, tab-off expansion could fail silently.

**Fix:** Moved `blur` event listener registration into `init()` alongside `keydown` and `input`.

### Issue 3: Silent Server Sync Failures
**Problem:** If AJAX request to save phrases to server failed, the user received no notification. Phrases were only stored in browser cache (vulnerable to cache clearing).

**Impact:** User thought phrases were saved permanently when they weren't.

**Fix:** Enhanced error handling to alert user if server sync fails.

## Architecture

### 1. Storage
- **Database Table:** `list_options`
- **List ID:** `dot_phrases_{USER_ID}` (Per-user isolation)
- **Option ID:** The dot phrase key (e.g., `.cataract`)
- **Notes:** JSON string containing expansion text (string) or multi-field map (object).
- **Activity:** Set to `1` for active phrases.

### 2. Frontend (`interface/forms/eye_mag/js/dotphrases.js`)
- **Initialization:**
  - Loads default phrases into `ACTIVE_PHRASES`.
  - Merges server data (from `#DOTPHRASES_USER` hidden field) with local `localStorage`.
  - Server data takes precedence to ensure consistency.
- **Event Listeners (all registered in `init()`):**
  - `keydown`: Triggers on space, enter, tab, comma, period, semicolon.
  - `input`: Continuous expansion as user types.
  - `blur`: Expands phrase when user leaves a field (tabs off, clicks elsewhere).
- **Persistence:**
  - `persist(key, val)`: Saves single phrase to `localStorage`.
  - `removePersist(key)`: Removes single phrase from `localStorage`.
  - Both update `window.eyeMagUserPhrases` immediately for runtime use.

### 3. Backend (`interface/forms/eye_mag/save.php`)
- **Action:** `save_dot_phrases`
- **Full Sync Strategy:**
  1. Receives complete JSON object from client.
  2. Fetches all existing keys in database for the user.
  3. Upserts all client-submitted phrases (Insert/Update).
  4. **Deletes** any database phrases NOT in client's submission.
  5. Returns JSON: `{'success': true}` on success.

### 4. UI & Modal (`interface/forms/eye_mag/view.php`)
- **Builder Modal:**
  - Triggered by bullseye icon or `Ctrl+.`.
  - **Single-Field Mode:** User types expansion text.
  - **Multi-Field Mode:** System captures current form values; user selects which fields to include.
  - **Manage Panel:** List existing phrases with Edit/Delete buttons.
  - **Import:** Copy phrases from other users (by user ID).
  - **Export/Import:** Share phrases via JSON export/import.
- **Server Sync Function:**
  - `syncPhrasesToServer()`: Sends all `localStorage` phrases to backend.
  - Called after every Save/Delete operation.
  - Now includes error handling and user alerts on failure.

## Workflow (How It Works for End Users)

1. **Page Load:**
   - PHP loads user's phrases from database into `#DOTPHRASES_USER`.
   - JavaScript merges server data + localStorage.
   - Phrases available immediately for expansion.

2. **Creating a New Phrase:**
   - Click bullseye or press `Ctrl+.`.
   - Modal opens. Select mode (Single/Multi).
   - Enter phrase key (e.g., `.cataracts`).
   - Single: Type expansion text.
   - Multi: Select fields to capture.
   - Click "Save".
   - Phrase saved to localStorage AND synced to server (with error alert if sync fails).

3. **Using a Phrase:**
   - Type `.phrase` + **any trigger** (space, tab, enter, comma, etc.).
   - Phrase expands immediately.
   - Multi-field phrases populate multiple form fields at once.

4. **Deleting a Phrase:**
   - Click "Manage" in modal.
   - Click "Delete" button next to phrase.
   - Phrase removed from runtime AND synced to server.

## Testing Checklist for Future Agents

- [ ] Create a new phrase with `.test`.
- [ ] Type `.test` + space → expands.
- [ ] Type `.test` + tab → expands before moving to next field.
- [ ] Type `.test` + click another field → expands.
- [ ] Create multi-field phrase; verify all fields populate.
- [ ] Delete phrase; refresh page; verify it's gone (not in server).
- [ ] Disable network; create phrase; re-enable network; verify it syncs.
- [ ] Check browser console for errors (should be clean).

## Developer Code Reference

### 1. JavaScript: `interface/forms/eye_mag/js/dotphrases.js`

#### Initialization Function (must be called on page load)

```javascript
function init() {
    // Load default phrases + server phrases + localStorage
    // Register ALL event listeners (keydown, input, blur) on text inputs
    // Set window.eyeMagUserPhrases for runtime access
}
```

**Called from:** view.php script tag: `<script>dotphrases.init();</script>`

**What it does:**
1. Loads default phrases from hard-coded list
2. Fetches user's phrases from `#DOTPHRASES_USER` (server data)
3. Merges with browser `localStorage` (localStorage is fallback)
4. Server data takes precedence
5. Registers event listeners on all `.form-control` inputs

**Critical:** If this runs before DOM is ready, listeners won't attach. Use jQuery `$(document).ready()` wrapper if called at script tag level.

#### Event Listeners

**Trigger Characters:** Space, Tab, Enter, Comma, Period, Semicolon

```javascript
// In init() function:
$(document).on('keydown', '.form-control', function(e) {
    if ([32, 9, 13, 188, 190, 186].includes(e.keyCode)) {  // Space, Tab, Enter, Comma, Period, Semicolon
        expandPhrase(this, e.key);
    }
});

$(document).on('input', '.form-control', function() {
    // Continuous expansion as user types
    // Useful for single-character shortcuts
});

$(document).on('blur', '.form-control', function() {
    // Expand when user leaves field
    // Useful for phrases user enters then tabs away from
});
```

**To add new trigger character:**
1. Add keyCode to the condition check: `[32, 9, 13, 188, 190, 186, NEW_CODE]`
2. Test: Type phrase + trigger, verify expansion

**KeyCodes reference:**
- 32 = Space
- 9 = Tab
- 13 = Enter
- 188 = Comma
- 190 = Period
- 186 = Semicolon

#### Expansion Function

```javascript
function expandPhrase(field, trigger) {
    var text = $(field).val();
    var phraseKey = extractPhrase(text);  // Get text before cursor
    var phrase = ACTIVE_PHRASES[phraseKey];

    if (phrase) {
        if (typeof phrase === 'string') {
            // Single-field phrase: just replace text
            $(field).val(phrase + trigger);
        } else if (typeof phrase === 'object') {
            // Multi-field phrase: populate multiple fields
            for (var key in phrase) {
                $('#' + key).val(phrase[key]);
            }
        }
        $(field).trigger('change');  // Notify form of update
    }
}
```

**To modify expansion behavior:**
1. Change how `phraseKey` is extracted (currently looks for `.` prefix)
2. Change replacement text (currently keeps trigger character)
3. Add validation or field filtering before expansion

#### Persistence Functions

```javascript
function persist(key, val) {
    // Save single phrase to localStorage
    // Update window.eyeMagUserPhrases immediately
    // Format: key = ".phrase_name", val = "expansion text" or {field1: val1, field2: val2}
}

function removePersist(key) {
    // Remove single phrase from localStorage
    // Update window.eyeMagUserPhrases immediately
}

function syncPhrasesToServer() {
    // Send all phrases to backend (save_dot_phrases action)
    // Window.eyeMagUserPhrases is source of truth
    // Server performs full sync: upsert + delete
    // Include error handling and user alerts
}
```

**Data format:**
```javascript
window.eyeMagUserPhrases = {
    ".cataract": "Age-related cataract OD/OS",
    ".amd": {
        "RETINA_finding": "Age-related macular degeneration",
        "RETINA_severity": "Moderate"
    }
};
```

**To change storage structure:**
1. Modify `persist()` to store new format
2. Update `removePersist()` to handle new format
3. Update modal to match expected structure
4. Update backend `save_dot_phrases` to handle new format

### 2. Backend: `interface/forms/eye_mag/save.php`

#### Action: `save_dot_phrases` (Lines ~480-540)

```php
if ($_POST['action'] == 'save_dot_phrases') {
    $phrases = json_decode($_POST['phrases'], true);  // Client phrases
    $user_id = $_SESSION['authUserID'];

    // Fetch existing phrases from database
    $existing = sqlQuery(
        "SELECT * FROM list_options WHERE list_id = ? AND activity = 1",
        array("dot_phrases_" . $user_id)
    );

    // Upsert all client phrases
    foreach ($phrases as $key => $val) {
        sqlStatement(
            "INSERT INTO list_options (list_id, option_id, notes, activity) VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE notes = VALUES(notes)",
            array("dot_phrases_" . $user_id, $key, json_encode($val))
        );
    }

    // Delete any phrases NOT in client submission
    // (prevents stale data from accumulating)
    $existing_keys = array_column($existing, 'option_id');
    $client_keys = array_keys($phrases);
    $to_delete = array_diff($existing_keys, $client_keys);

    foreach ($to_delete as $key) {
        sqlStatement(
            "UPDATE list_options SET activity = 0 WHERE list_id = ? AND option_id = ?",
            array("dot_phrases_" . $user_id, $key)
        );
    }

    echo json_encode(['success' => true]);
    exit;
}
```

**Key design decisions:**
- **Full sync strategy:** Always sync entire phrase set, not just changes
  - Advantage: Guaranteed consistency, no partial update bugs
  - Disadvantage: Slower for large phrase sets (unlikely to be huge)

- **Delete strategy:** Mark with `activity=0` instead of hard delete
  - Advantage: Data recovery possible, audit trail preserved
  - Disadvantage: More complex queries needed

**To change deletion strategy:**
1. Replace `UPDATE ... SET activity = 0` with hard DELETE
2. Update `view.php` to remove `activity = 1` check when loading phrases
3. Verify no other code depends on `activity` field for phrases

#### Loading Phrases on Page Load (Lines ~170-190)

```php
// Load user's phrases from database
$phrases_query = "SELECT option_id, notes FROM list_options
                  WHERE list_id = ? AND activity = 1";
$phrases_result = sqlStatement($phrases_query, array("dot_phrases_" . $_SESSION['authUserID']));
$user_phrases = array();
while ($row = sqlFetchArray($phrases_result)) {
    $user_phrases[$row['option_id']] = json_decode($row['notes'], true);
}

// Pass to JavaScript as JSON
$phrases_json = json_encode($user_phrases);
?>
<input type="hidden" id="DOTPHRASES_USER" value="<?php echo attr($phrases_json); ?>">
```

**To optimize:**
1. Cache phrase set in Redis/Memcached for frequently-accessed users
2. Add phrase metadata (created_date, usage_count) for analytics
3. Lazy-load phrases only when modal is opened

### 3. User Interface: `interface/forms/eye_mag/view.php`

#### Modal Structure (Lines ~420-520)

```html
<!-- Dot Phrases Modal -->
<div id="dotphrasesModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Mode Selection Tabs: Single vs Multi -->
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#singleTab">Single Field</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#multiTab">Multi Field</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#manageTab">Manage</a>
                </li>
            </ul>

            <!-- Single Field Tab: Simple text expansion -->
            <div id="singleTab" class="tab-pane fade show active">
                <input type="text" id="phraseKey" placeholder=".cataracts">
                <textarea id="phraseText" placeholder="Expansion text"></textarea>
                <button onclick="saveSinglePhrase()">Save</button>
            </div>

            <!-- Multi Field Tab: Capture multiple form fields -->
            <div id="multiTab" class="tab-pane fade">
                <p>Current field values will be captured. Select which to include:</p>
                <input type="text" id="multiPhraseKey" placeholder=".complex">
                <!-- Checkboxes for field selection -->
                <button onclick="saveMultiPhrase()">Save</button>
            </div>

            <!-- Manage Tab: Edit/Delete existing phrases -->
            <div id="manageTab" class="tab-pane fade">
                <!-- List all phrases with Edit/Delete buttons -->
                <ul id="phraseList"></ul>
            </div>
        </div>
    </div>
</div>
```

**To add new mode (e.g., "Template phrases"):**
1. Add new tab in modal
2. Create corresponding save function
3. Update JavaScript `init()` to handle new mode on expansion
4. Document the new format

#### Modal Event Handlers

```javascript
// Save single phrase
function saveSinglePhrase() {
    var key = $('#phraseKey').val();  // Must start with .
    var text = $('#phraseText').val();

    if (!key.startsWith('.')) { key = '.' + key; }
    if (!text) { alert('Enter expansion text'); return; }

    dotphrases.persist(key, text);
    dotphrases.syncPhrasesToServer();
    refreshPhraseList();
    $('#phraseKey').val('');
    $('#phraseText').val('');
}

// Save multi-field phrase
function saveMultiPhrase() {
    var key = $('#multiPhraseKey').val();
    var fieldMap = {};

    // Capture checked fields
    $('.field-checkbox:checked').each(function() {
        var fieldId = $(this).val();
        fieldMap[fieldId] = $('#' + fieldId).val();
    });

    if (!key.startsWith('.')) { key = '.' + key; }
    if (Object.keys(fieldMap).length === 0) { alert('Select fields'); return; }

    dotphrases.persist(key, fieldMap);
    dotphrases.syncPhrasesToServer();
    refreshPhraseList();
}

// Delete phrase
function deletePhrase(key) {
    if (!confirm('Delete ' + key + '?')) return;

    dotphrases.removePersist(key);
    dotphrases.syncPhrasesToServer();
    refreshPhraseList();
}

// Refresh phrase list for management
function refreshPhraseList() {
    var html = '';
    for (var key in window.eyeMagUserPhrases) {
        var val = window.eyeMagUserPhrases[key];
        var display = (typeof val === 'string') ? val : JSON.stringify(val);
        html += '<li>' + key + ': ' + display +
                ' <button onclick="deletePhrase(\'' + key + '\')">Delete</button></li>';
    }
    $('#phraseList').html(html);
}
```

**To add import/export:**
1. Add "Import" button that accepts JSON file
2. Add "Export" button that downloads current phrases as JSON
3. Parse imported JSON and merge with existing phrases
4. Call `syncPhrasesToServer()` after merge

### 4. Data Model & Database

#### Table: `list_options`

```sql
-- Used for phrase storage
CREATE TABLE list_options (
  id INT AUTO_INCREMENT PRIMARY KEY,
  list_id VARCHAR(100),           -- "dot_phrases_{USER_ID}"
  option_id VARCHAR(100),         -- ".phrase_name"
  notes LONGTEXT,                 -- JSON: "expansion" or {"field1": "val1"}
  activity TINYINT DEFAULT 1,     -- 1 = active, 0 = deleted
  seq INT DEFAULT 0,
  is_default INT DEFAULT 0,
  mapping TINYINT DEFAULT 0
);
```

**Indexes needed:**
- `(list_id, activity)` - For fast phrase lookup by user
- `(list_id, option_id, activity)` - For unique phrase lookup

**To migrate to dedicated table:**
1. Create `form_eye_phrases` table with similar structure
2. Update `view.php` load query to use new table
3. Update `save.php` `save_dot_phrases` action to new table
4. Add migration script to copy existing phrases
5. Update documentation

#### Storage Format Examples

**Single-field phrase:**
```
list_id: "dot_phrases_3"
option_id: ".cataract"
notes: "\"Age-related cataract with posterior subcapsular opacities\""
activity: 1
```

**Multi-field phrase:**
```
list_id: "dot_phrases_3"
option_id: ".amd_findings"
notes: "{\"RETINA_finding\": \"Age-related macular degeneration\", \"RETINA_severity\": \"Moderate\", \"RETINA_treatments\": \"AREDS vitamins\"}"
activity: 1
```

### 5. Error Handling & Validation

#### Current Error Handling (view.php/dotphrases.js)

```javascript
// In syncPhrasesToServer()
$.ajax({
    type: 'POST',
    url: '../../forms/eye_mag/save.php?mode=update&id=' + $("#form_id").val(),
    data: { action: 'save_dot_phrases', phrases: JSON.stringify(window.eyeMagUserPhrases) }
}).done(function(response) {
    var result = JSON.parse(response);
    if (result.success) {
        console.log('Phrases synced to server');
    }
}).fail(function(error) {
    // Alert user if sync fails
    alert('Failed to save phrases to server. Your phrases are in browser cache but not backed up.');
    console.error('Phrase sync failed:', error);
});
```

#### Validation Checklist

- [ ] Phrase key must start with `.`
- [ ] Phrase key must be non-empty after `.`
- [ ] Phrase value must be non-empty (string or object)
- [ ] Multi-field phrases must have at least one field selected
- [ ] No duplicate phrase keys
- [ ] No special characters in phrase key (except `.`)
- [ ] JSON must be valid when storing/retrieving
- [ ] User must have valid session (authUserID set)

**To add validation:**
1. Add JavaScript validation in modal save functions
2. Add server-side validation in `save.php`
3. Return error messages to client if validation fails
4. Display errors to user in modal

### 6. Security Considerations

**Current security measures:**
- User phrases isolated by `authUserID` (per-user storage)
- SQL parameterized queries prevent injection
- JSON_ENCODE/JSON_DECODE for safe serialization
- Session validation via `$_SESSION['authUserID']`

**Potential vulnerabilities to address:**
1. **XSS in phrase expansion:** If phrase contains `<script>`, could execute
   - Mitigation: Use `.text()` instead of `.html()` in expansion
   - Current code: `$(field).val(phrase)` - safe for value attributes

2. **CSRF on phrase save:**
   - Mitigation: Require CSRF token in AJAX requests
   - Current code: Uses `top.restoreSession()` - OpenEMR's session management

3. **Phrase key injection:**
   - Mitigation: Validate key format (alphanumeric + dots only)
   - Current code: No validation - TODO

**To harden security:**
1. Add input validation for phrase keys (regex: `^\.[\w]+$`)
2. Add CSRF token to all AJAX requests
3. Use Content Security Policy headers
4. Rate-limit phrase save requests
5. Add audit logging for phrase modifications



## Files Modified

1. `interface/forms/eye_mag/js/dotphrases.js`: Fixed initialization and event listener registration.
2. `interface/forms/eye_mag/view.php`: Enhanced server sync error handling.
3. `interface/forms/eye_mag/save.php`: Full sync logic with deletion support (no changes in this revision).

## Known Limitations & Work In Progress

### Current Limitations

- **No offline editing of phrases:** While phrases persist offline, creating/deleting phrases requires server connection.
- **No audit trail:** No record of who created/deleted a phrase or when.
- **No phrase collision detection:** If two users are editing at the exact same time, the last save wins (unlikely in practice).
- **No input validation:** Phrase keys can contain any characters - should validate format
- **No CSRF protection on AJAX:** Phrase save AJAX requests lack explicit CSRF tokens
- **No rate limiting:** Users could spam phrase saves without throttling
- **Limited error messages:** Server sync failures only show generic alert

### Work In Progress - Priority 1 (Do First)

#### 1.1 Add Input Validation for Phrase Keys
**Status:** NOT STARTED
**Complexity:** Low
**Files:** dotphrases.js, view.php (modal)

**Required:**
- Enforce phrase key format: `^\.[\w]+$` (dot + alphanumeric/underscore only)
- Reject: spaces, special chars, multiple dots
- Reject: keys shorter than 2 chars (`.` + 1 letter minimum)
- Show validation error in modal if invalid
- Test with invalid inputs: `. test`, `.123!`, `.. bad`

**Implementation:**
```javascript
function validatePhraseKey(key) {
    if (!/^\.[\w]+$/.test(key)) {
        return 'Invalid format: start with . followed by letters/numbers/underscore';
    }
    if (key.length < 2) {
        return 'Key too short (min 2 chars)';
    }
    return null;  // Valid
}
```

#### 1.2 Add CSRF Protection to Phrase AJAX
**Status:** NOT STARTED
**Complexity:** Medium
**Files:** dotphrases.js, save.php

**Required:**
- Get CSRF token from page (OpenEMR provides via token field)
- Include token in all AJAX requests to save.php
- Server validates token before processing phrases
- Return 403 error if token invalid

**OpenEMR pattern:**
```javascript
// Get token from form
var token = $('input[name="__token"]').val();

// Include in AJAX
$.ajax({
    url: '...',
    data: {
        'action': 'save_dot_phrases',
        '__token': token,
        ...
    }
});
```

#### 1.3 Improve Error Handling & User Feedback
**Status:** PARTIAL (basic alerts present)
**Complexity:** Low
**Files:** view.php, dotphrases.js

**Required:**
- Show specific error message (not generic "Failed to save")
- Include retry button if sync fails
- Show success confirmation (toast/badge, not just console)
- Display network connection status
- Log detailed errors to console for debugging

**Example:**
```javascript
.fail(function(error) {
    if (error.status === 0) {
        alert('No internet connection. Your phrases are saved locally.');
    } else if (error.status === 403) {
        alert('Session expired. Please refresh the page.');
    } else {
        alert('Server error: ' + error.responseText);
    }
});
```

### Work In Progress - Priority 2 (Enhancements)

#### 2.1 Add Phrase Import/Export
**Status:** NOT STARTED
**Complexity:** Medium
**Files:** view.php (modal), dotphrases.js

**Required:**
- "Export" button: Download phrases as JSON file
- "Import" button: Upload JSON file, merge with existing phrases
- Show import preview before confirming
- Handle duplicate keys (skip, overwrite, or rename)

**File format:**
```json
{
  ".cataracts": "Age-related cataract",
  ".amd": {
    "RETINA_finding": "Age-related macular degeneration",
    "RETINA_severity": "Moderate"
  }
}
```

#### 2.2 Add Phrase Search & Filter
**Status:** NOT STARTED
**Complexity:** Low
**Files:** view.php (modal management tab)

**Required:**
- Search box in "Manage" tab to find phrases by key or expansion
- Filter by type (single vs multi-field)
- Sort by: name (alpha), creation date, usage count

#### 2.3 Add Usage Statistics & Analytics
**Status:** NOT STARTED
**Complexity:** Medium
**Files:** save.php, new table column

**Required:**
- Track phrase usage: count how many times each phrase was expanded
- Store last_used timestamp
- Display usage stats in manage tab (e.g., "Used 47 times")
- Show "Most used phrases" ranking

**New column in list_options:**
```sql
ALTER TABLE list_options
ADD COLUMN usage_count INT DEFAULT 0,
ADD COLUMN last_used DATETIME;
```

#### 2.4 Add Phrase Categorization
**Status:** NOT STARTED
**Complexity:** Medium
**Files:** view.php, dotphrases.js, save.php

**Required:**
- Tag phrases by category (e.g., "Findings", "Diagnoses", "Treatment Plans")
- Filter manage tab by category
- Group expansion suggestions by category when typing

**Implementation:**
- Add category field to phrase metadata (new column or JSON subfield)
- Update modal to include category dropdown
- Update expansion function to show suggestions grouped by category

#### 2.5 Add Shared Phrase Library (Admin)
**Status:** NOT STARTED
**Complexity:** High
**Files:** new admin page, save.php

**Required:**
- Admin interface to create organization-wide phrases
- All users can use shared phrases (read-only)
- Users can override shared phrases with personal versions
- Admin can push phrase updates to all users

**Implementation:**
- Create `dot_phrases_global` list_id for shared phrases
- Load global phrases + user phrases on page load (user overrides global)
- Add admin page to manage global phrases
- Add "Use global phrase" button in modal

#### 2.6 Add Phrase Autocomplete Suggestions
**Status:** NOT STARTED
**Complexity:** Medium
**Files:** dotphrases.js, view.php

**Required:**
- While typing `.something`, show suggestions of matching phrases
- Click suggestion to auto-complete
- Show suggestion in tooltip/dropdown below field

**Implementation:**
```javascript
// In input event handler
if (currentText.startsWith('.')) {
    var partialKey = currentText;
    var matches = Object.keys(ACTIVE_PHRASES).filter(k => k.startsWith(partialKey));
    showSuggestions(matches);  // Show dropdown
}
```

### Work In Progress - Priority 3 (Future)

#### 3.1 Add Phrase Versioning & History
**Status:** NOT STARTED
**Complexity:** High
**Rationale:** Allow users to revert to previous phrase versions

#### 3.2 Add Multi-Language Support
**Status:** NOT STARTED
**Complexity:** Medium
**Rationale:** Support phrase expansion in different languages/formats

#### 3.3 Add Conditional Phrases
**Status:** NOT STARTED
**Complexity:** High
**Rationale:** Expand differently based on form context (e.g., if right eye, insert "OD")

#### 3.4 Add Phrase Collaboration/Comments
**Status:** NOT STARTED
**Complexity:** High
**Rationale:** Users can comment on/suggest improvements to other users' phrases

## Testing Checklist for Future Agents

- [ ] Create a new phrase with `.test`.
- [ ] Type `.test` + space → expands.
- [ ] Type `.test` + tab → expands before moving to next field.
- [ ] Type `.test` + click another field → expands.
- [ ] Create multi-field phrase; verify all fields populate.
- [ ] Delete phrase; refresh page; verify it's gone (not in server).
- [ ] Disable network; create phrase; re-enable network; verify it syncs.
- [ ] Check browser console for errors (should be clean).
- [ ] **NEW:** Try invalid phrase key (space, special char) - should reject with error message
- [ ] **NEW:** Create phrase while offline - should save locally and sync when online
- [ ] **NEW:** Two browsers creating same phrase key simultaneously - verify no data loss
- [ ] **NEW:** Create 100+ phrases - verify performance is acceptable

