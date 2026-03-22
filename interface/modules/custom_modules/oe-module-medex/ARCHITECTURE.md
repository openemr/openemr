# MedEx Module: Event-Based Architecture

## Overview

This document describes the architecture for extracting all MedEx code from OpenEMR core into a self-contained custom module using an event-based injection pattern.

## Goals

1. **Clean Separation**: OpenEMR core files must be completely free of MedEx-specific code
2. **Module Encapsulation**: All MedEx functionality lives in the module directory
3. **Graceful Degradation**: Core pages work without the module installed
4. **Event-Driven Injection**: Module injects UI components and functionality via events when enabled

## Event-Based Injection Strategy

### Pattern Overview

The module uses OpenEMR's event dispatcher system to inject functionality into core pages:

```
Core Page → Dispatch Event → Module Listener → Inject UI/Logic
```

### Event Flow

1. **Page Load**: Core page (messages.php or patient_tracker.php) loads normally
2. **Event Dispatch**: Page dispatches events at specific injection points
3. **Module Listening**: If module is enabled, event listeners receive the event
4. **Content Injection**: Module injects HTML, JavaScript, or modifies page behavior
5. **Rendering**: Page renders with or without module enhancements

### Why Events?

- **Loose Coupling**: Core doesn't know about MedEx; MedEx knows about core
- **Conditional Loading**: Module only loads when enabled
- **Multiple Modules**: Other modules can use same injection points
- **Clean Upgrades**: Core updates don't break module code

## Custom Events

### 1. MessagesPageRenderEvent

**Purpose**: Inject MedEx functionality into messages.php

**Dispatch Location**: `/interface/main/messages/messages.php`

**Injection Points**:
- After head section: Navigation bar, CSS
- Before main content: MedEx-specific pages (Recalls, Preferences, SMS Bot)
- Within SMS tab: SMS patient search functionality
- In page scripts: JavaScript for SMS, recall board interactions

**Event Properties**:
```php
class MessagesPageRenderEvent extends Event
{
    private bool $medexEnabled;
    private array $request;
    private ?object $loggedInUser;
    private string $injectionPoint;
    private string $content = '';

    // Injection points: 'navigation', 'content', 'sms_tab', 'scripts'
}
```

**Example Usage**:
```php
// In messages.php
$event = new MessagesPageRenderEvent('navigation', $_REQUEST);
$event = $GLOBALS['kernel']->getEventDispatcher()
    ->dispatch($event, MessagesPageRenderEvent::EVENT_RENDER);
echo $event->getContent();
```

### 2. PatientTrackerPageRenderEvent

**Purpose**: Inject MedEx functionality into patient_tracker.php

**Dispatch Location**: `/interface/patient_tracker/patient_tracker.php`

**Injection Points**:
- After head section: Navigation bar
- In flow board: Reminder icons, patient communication status
- In table cells: MedEx-specific messaging icons and status indicators

**Event Properties**:
```php
class PatientTrackerPageRenderEvent extends Event
{
    private bool $medexEnabled;
    private array $appointment;
    private ?object $loggedInUser;
    private string $injectionPoint;
    private string $content = '';

    // Injection points: 'navigation', 'status_icons', 'modalities'
}
```

## Event Listeners in Module

### ModuleManagerListener

**File**: `/src/ModuleManagerListener.php`

**Purpose**: Handle module lifecycle events (install, enable, disable, uninstall)

**Events**:
- `MODULE_INSTALL`: Create database tables for MedEx
- `MODULE_ENABLE`: Initialize MedEx configuration
- `MODULE_DISABLE`: Disable background services
- `MODULE_UNINSTALL`: Clean up database tables

### MessagesPageListener

**File**: `/src/Listeners/MessagesPageListener.php` (NEW)

**Purpose**: Handle all message.php injection events

**Methods**:
```php
public function onNavigationRender(MessagesPageRenderEvent $event): void
{
    if (!$this->medExEnabled()) return;

    $medEx = new MedExAPI();
    $loggedIn = $medEx->login();
    $html = $medEx->renderNavigation($loggedIn);
    $event->setContent($html);
}

public function onContentRender(MessagesPageRenderEvent $event): void
{
    // Render MedEx-specific pages: Recalls, Preferences, Setup, SMS Bot
    $request = $event->getRequest();
    if ($request['go'] === 'Recalls') {
        $html = $this->renderRecallsPage($request);
        $event->setContent($html);
    }
    // ... other pages
}

public function onSMSTabRender(MessagesPageRenderEvent $event): void
{
    // Inject SMS patient search and interface
}

public function onScriptsRender(MessagesPageRenderEvent $event): void
{
    // Inject JavaScript for MedEx functionality
}
```

### PatientTrackerListener

**File**: `/src/Listeners/PatientTrackerListener.php` (NEW)

**Purpose**: Handle all patient_tracker.php injection events

**Methods**:
```php
public function onNavigationRender(PatientTrackerPageRenderEvent $event): void
{
    // Inject MedEx navigation bar
}

public function onStatusIconsRender(PatientTrackerPageRenderEvent $event): void
{
    // Inject reminder status icons in flow board
    $appointment = $event->getAppointment();
    $icons = $this->generateReminderIcons($appointment);
    $event->setContent($icons);
}

public function onModalitiesRender(PatientTrackerPageRenderEvent $event): void
{
    // Inject possible communication modalities
}
```

## Module Structure

```
oe-module-medex/
├── openemr.bootstrap.php           # Module registration & event listeners
├── ARCHITECTURE.md                 # This document
├── composer.json                   # Module dependencies
├── admin/
│   ├── settings.php               # MedEx settings page
│   └── templates/                 # Admin UI templates
├── src/
│   ├── Events/                    # Event classes
│   │   ├── MessagesPageRenderEvent.php
│   │   └── PatientTrackerPageRenderEvent.php
│   ├── Listeners/                 # Event listeners
│   │   ├── MessagesPageListener.php
│   │   └── PatientTrackerListener.php
│   ├── MedExAPI.php              # New API wrapper (reads from globals)
│   ├── MedExDisplay.php          # All UI rendering methods
│   ├── MedExSetup.php            # Setup wizard
│   ├── MedExEvents.php           # Event handling (recalls, appointments)
│   ├── CalendarSync.php          # Calendar integration
│   └── ModuleManagerListener.php  # Module lifecycle
├── templates/                     # Twig templates for UI
│   ├── navigation.html.twig
│   ├── recalls.html.twig
│   ├── preferences.html.twig
│   └── sms_bot.html.twig
└── public/
    ├── css/
    │   └── medex.css             # MedEx-specific styles
    └── js/
        └── medex.js              # MedEx JavaScript
```

## Migration Path

### Phase 1: Create Event Infrastructure (Current Phase)

1. Create event classes in module
2. Create event listeners in module
3. Keep legacy code functional during transition

**Status**: In progress

### Phase 2: Implement Event Dispatchers

1. Add event dispatch calls to messages.php
2. Add event dispatch calls to patient_tracker.php
3. Move display logic from legacy API to module listeners
4. Test with module enabled

**Files Modified**:
- `/interface/main/messages/messages.php`
- `/interface/patient_tracker/patient_tracker.php`

### Phase 3: Remove Legacy Code

1. Remove `require_once "$srcdir/MedEx/API.php"` from core files
2. Remove all MedEx method calls from core files
3. Delete `/library/MedEx/` directory
4. Test core pages work without module

**Files Modified**:
- `/interface/main/messages/messages.php`
- `/interface/main/messages/save.php`
- `/interface/patient_tracker/patient_tracker.php`

**Files Deleted**:
- `/library/MedEx/` (entire directory)

## Backward Compatibility

### Configuration Migration

**Old Location**: `library/MedEx/`
**New Location**: `oe-module-medex/src/`

**Credentials**: Continue reading from `globals` table (already implemented in MedExAPI.php)

**Database Tables**: All MedEx tables remain in core database:
- `medex_prefs`
- `medex_outgoing`
- `medex_recalls`
- `medex_icons`

### Background Services

**Old Path**: `/library/MedEx/MedEx_background.php`
**New Path**: `/interface/modules/custom_modules/oe-module-medex/src/BackgroundService.php`

**Migration**: Update `background_services` table during module enable:
```sql
UPDATE background_services
SET require_once='/interface/modules/custom_modules/oe-module-medex/src/BackgroundService.php'
WHERE name='MedEx';
```

## Testing Strategy

### Test Case 1: Module Disabled
- **Action**: Disable MedEx module
- **Expected**:
  - messages.php loads without errors
  - patient_tracker.php loads without errors
  - No MedEx UI elements visible
  - No JavaScript errors

### Test Case 2: Module Enabled
- **Action**: Enable MedEx module
- **Expected**:
  - MedEx navigation bar appears
  - Recall board accessible
  - SMS Bot functional
  - Patient tracker shows MedEx icons
  - All existing functionality works

### Test Case 3: Fresh Install
- **Action**: Install module on clean OpenEMR
- **Expected**:
  - Setup wizard appears
  - Registration completes
  - All features available

### Test Case 4: Upgrade Path
- **Action**: Upgrade from legacy MedEx to module
- **Expected**:
  - Existing credentials preserved
  - Existing recalls preserved
  - No data loss
  - Background service continues

## Security Considerations

1. **CSRF Tokens**: All AJAX requests include CSRF validation
2. **ACL Checks**: Event listeners verify user permissions before rendering
3. **Input Sanitization**: All user input sanitized before database storage
4. **API Credentials**: Stored encrypted in globals table
5. **Session Management**: Uses OpenEMR's session handling

## Performance Considerations

1. **Lazy Loading**: MedEx API only instantiated when module enabled
2. **Caching**: API responses cached to reduce external calls
3. **Conditional Queries**: Database queries only run when needed
4. **Event Propagation**: Stop propagation when event handled

## Future Enhancements

1. **REST API Integration**: Expose MedEx functionality via OpenEMR REST API
2. **WebSocket Support**: Real-time SMS notifications
3. **Mobile App**: Native mobile app using REST API
4. **Multi-tenant**: Support multiple MedEx accounts per installation

## Technical Debt

### Items to Address

1. **Legacy Database Schema**: MedEx tables use old naming conventions
2. **Direct SQL Queries**: Replace with OpenEMR service classes
3. **Global Variables**: Reduce reliance on `$GLOBALS`
4. **Error Handling**: Implement structured exception handling
5. **Unit Tests**: Add comprehensive test coverage

## Deployment Checklist

### Pre-Deployment
- [ ] All event classes created
- [ ] All event listeners implemented
- [ ] Core files updated with event dispatchers
- [ ] Module bootstrap configured
- [ ] Database migrations prepared
- [ ] Documentation complete

### Deployment
- [ ] Enable module in OpenEMR
- [ ] Run database migrations
- [ ] Test all MedEx features
- [ ] Verify background service runs
- [ ] Check log files for errors

### Post-Deployment
- [ ] Monitor error logs for 24 hours
- [ ] Verify recall processing works
- [ ] Test SMS sending/receiving
- [ ] Confirm navigation displays correctly
- [ ] Validate patient tracker icons

### Rollback Plan
If issues arise:
1. Disable module
2. Restore legacy MedEx files from backup
3. Revert core file changes
4. Restart background services
5. Test legacy functionality

## Support & Maintenance

### Common Issues

**Issue**: Navigation bar not appearing
**Solution**: Check module is enabled; verify event listeners registered

**Issue**: Background service not running
**Solution**: Check `background_services` table `require_once` path

**Issue**: API calls failing
**Solution**: Verify credentials in globals table; check network connectivity

### Debug Mode

Enable debug logging:
```php
$GLOBALS['medex_debug_log'] = true;
$GLOBALS['medex_debug_log_file'] = '/tmp/medex.log';
```

## References

- [OpenEMR Event System](https://github.com/openemr/openemr/tree/master/src/Events)
- [OpenEMR Module Development](https://github.com/openemr/openemr/blob/master/Documentation/Modules/README.md)
- [MedEx API Documentation](https://medexbank.com/api/docs)

## Version History

- **v1.0.0** (2026-01-22): Initial architecture document
  - Event-based injection pattern defined
  - Migration path established
  - Module structure outlined

## Contributors

- MedEx Development Team
- OpenEMR Core Contributors
- AI Assistant (Architecture Design)
