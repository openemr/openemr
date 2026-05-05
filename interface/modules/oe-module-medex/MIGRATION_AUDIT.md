# MedEx API.php Migration Audit

## Goal
Move all MedEx-dependent functionality from `library/MedEx/API.php` into the `oe-module-medex` custom module, using event listeners to inject functionality into Flow Board and Messages/Recalls pages.

## Classes in library/MedEx/API.php

### 1. CurlRequest
**Purpose**: HTTP request handler with session/cookie management
**Methods**:
- `__construct($sessionFile)`
- `makeRequest()`
- `setUrl($url)`
- `setData($postData)`
- `getResponse()`
- `getRawResponse()`
- Private: `restoreSession()`, `getCookies()`, `saveSession()`

**Migration**: ✅ Already replaced by module's `MedExAPI::makeRequest()` using curl directly

---

### 2. Base
**Purpose**: Base class for all MedEx classes
**Methods**: `__construct($MedEx)`

**Migration**: Create `src/Base.php` in module with similar pattern

---

### 3. Practice
**Purpose**: Sync practice data from MedEx server
**Methods**: `sync($token)`

**Migration**: Create `src/Services/PracticeService.php` in module
- Called during login to sync facilities, providers, preferences

---

### 4. Campaign
**Purpose**: Get campaign/event types from MedEx
**Methods**: `events($token)`

**Migration**: Create `src/Services/CampaignService.php` in module
- Returns available reminder/recall campaign types

---

### 5. Events
**Purpose**: Generate appointment reminder events
**Methods**:
- `generate($token, $events)` - Main event generation
- `calculateEvents($event, $start_date, $stop_date)` - Calculate recurring events
- `save_recall($saved)` - Save recall to database
- `delete_Recall()` - Delete recall
- `getAge($dob, $asof)` - Calculate patient age
- Private: `addRecurrent()`, `recursive_array_search()`, `process_deletes()`, `process()`, `getDatesInRecurring()`, `__increment()`

**Migration**: Create `src/Services/EventService.php` in module
- Core functionality for generating appointment reminders
- Handles recurring appointments
- Manages recall scheduling

---

### 6. Callback
**Purpose**: Handle incoming callbacks from MedEx server
**Methods**: `receive($data)`

**Migration**: ✅ Already implemented in `public/callback.php` and `src/CallbackHandlers/`

---

### 7. Logging
**Purpose**: Debug logging to file
**Methods**: `log_this($data, $label)`

**Migration**: Use OpenEMR's error_log() or create `src/Services/LoggingService.php` if needed

---

### 8. Display (LARGEST CLASS - ~1500 lines)
**Purpose**: Render all UI components for MedEx
**Methods**:
- `navigation($logged_in)` - Top navigation menu
- `preferences($prefs)` - Preferences/settings page (~230 lines)
- `display_recalls($logged_in)` - Recall board UI
- `get_recalls($from_date, $to_date)` - Fetch recalls from DB
- `show_progress_recall($recall, $events)` - Recall progress display
- `display_add_recall($pid)` - Add/edit recall form
- `icon_template()` - Icon customization UI
- `SMS_bot($logged_in)` - SMS conversation interface
- `TM_bot($logged_in, $data)` - Telephone bot interface
- `syncPat($pid, $logged_in)` - Sync patient to MedEx
- `possibleModalities($appt)` - Get available contact methods for patient
- Private: `recall_board_process()`, `get_icon()`, `recall_board_top()`, `recall_board_bot()`

**Migration**: Break into multiple classes:
- `admin/preferences.php` - Settings page (merge with existing setup.php)
- `src/Display/Navigation.php` - Menu/navigation
- `src/Display/RecallBoard.php` - Recall board UI
- `src/Display/IconManager.php` - Icon templates
- `src/Display/SMSBot.php` - SMS interface
- `src/Services/PatientSync.php` - Patient sync logic

---

### 9. Setup
**Purpose**: Initial MedEx setup/registration wizard
**Methods**:
- `MedExBank($stage)` - Multi-stage setup wizard
- `autoReg($data)` - Automated registration

**Migration**: ✅ Already implemented in `admin/register.php` (2-stage wizard)
- Can deprecate Setup class entirely

---

### 10. MedEx (Main Class)
**Purpose**: Main API coordinator class
**Methods**:
- `__construct($url, $sessionFile)`
- `getCookie()`
- `getLastError()`
- `login($force)` - Authenticate with MedEx
- `getPreferences()` - Load medex_prefs from DB
- `getUrl($method)` - Construct API URLs
- `checkModality($event, $appt, $icon)` - Check if event can be sent via modality
- Private: `just_login($info)` - Perform actual login request

**Migration**: ✅ Partially migrated to `src/MedExAPI.php`
- Need to add: `checkModality()`, improve `login()` to handle sync/generate

---

## Database Tables Used

1. **medex_prefs** - Practice preferences and cached session
2. **medex_outgoing** - Outgoing messages/events
3. **medex_recalls** - Patient recalls
4. **medex_icons** - Custom icons for message types
5. **openemr_postcalendar_events** - Appointments
6. **patient_data** - Patient demographics and HIPAA preferences
7. **background_services** - MedEx background service config

---

## Pages Using library/MedEx/API.php

1. **interface/patient_tracker/patient_tracker.php** (Flow Board)
   - Uses: `$MedEx->login()`, `$MedEx->display->navigation()`, `$MedEx->display->possibleModalities()`

2. **interface/main/messages/messages.php** (Messages/Recalls)
   - Uses: `$MedEx->login()`, `$MedEx->display->navigation()`, `$MedEx->display->*` (all display methods)

3. **interface/main/messages/save.php** (Save operations)
   - Uses: `$MedEx->login()`

---

## Migration Strategy

### Phase 1: Core Services (Priority)
1. ✅ `src/MedExAPI.php` - Already exists, enhance with sync/generate
2. Create `src/Services/PracticeService.php` - Practice sync
3. Create `src/Services/CampaignService.php` - Campaign/event types
4. Create `src/Services/EventService.php` - Event generation & recalls
5. Create `src/Services/PatientSync.php` - Patient sync logic

### Phase 2: Display Components
1. Create `admin/preferences.php` - Unified preferences (merge all settings)
2. Create `src/Display/Navigation.php` - Menu/navigation
3. Create `src/Display/RecallBoard.php` - Recall board UI
4. Create `src/Display/IconManager.php` - Icon customization
5. Create `src/Display/SMSBot.php` - SMS conversation UI

### Phase 3: Integration via Events
1. Create event listener for Flow Board to inject MedEx UI elements
2. Create event listener for Messages page to inject MedEx UI elements
3. Update Flow Board to check for module event instead of calling API.php directly
4. Update Messages page to check for module event instead of calling API.php directly

### Phase 4: Deprecation
1. Add deprecation notices to library/MedEx/API.php classes
2. Update all direct API.php usages to use module
3. Eventually remove library/MedEx/ entirely (long-term goal)

---

## Key Decision: Event-Driven Architecture

Instead of Flow Board/Messages directly calling the module, use OpenEMR's event system:

```php
// In Flow Board (patient_tracker.php)
$medexData = $GLOBALS['kernel']->getEventDispatcher()->dispatch(
    new GenericEvent('medex.flow_board.get_data', ['appointment' => $appt])
);

// In module Bootstrap
$eventDispatcher->addListener('medex.flow_board.get_data', function($event) {
    $appt = $event->getArgument('appointment');
    // Return MedEx status icons, possible modalities, etc.
    $event->setArgument('medex_icons', $icons);
    $event->setArgument('medex_modalities', $modalities);
});
```

This keeps Flow Board and Messages pages MedEx-agnostic while allowing the module to inject functionality.

---

## Next Steps

1. Start with Phase 1: Core Services
2. Build unified Preferences page
3. Create event listeners for Flow Board integration
4. Test thoroughly before deprecating API.php
