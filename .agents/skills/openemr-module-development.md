# OpenEMR Custom Module Development — Definitive Reference

Reverse-engineered from 8 production custom modules plus core loader code.
All code examples cite file and line numbers. Nothing is invented.

---

## Section 1: Module File Structure

### Required Files

| File | Purpose |
|------|---------|
| `openemr.bootstrap.php` | **The only required file.** Run on every page load for enabled modules. Registers namespaces, hooks events. |
| `info.txt` | One-line human-readable name (e.g., `Weno EZ Integration eRx Module`). Used in Module Manager UI listing. |

### Common Optional Files

| File | Purpose |
|------|---------|
| `moduleConfig.php` | Config page rendered in an iframe by Module Manager when the user clicks "Configure". Include globals.php and render your setup UI. |
| `ModuleManagerListener.php` | Hooks into Module Manager lifecycle events (install, enable, disable, unregister). Contains a class named `ModuleManagerListener` extending `AbstractModuleActionListener`. **Must NOT declare a namespace.** |
| `table.sql` | Database DDL for module tables, run by Module Manager on install. Uses OpenEMR's conditional SQL syntax (see Section 7). |
| `cleanup.sql` | SQL run when the module is unregistered/uninstalled. |
| `src/Bootstrap.php` | Recommended pattern: put subscription logic in a class here instead of directly in `openemr.bootstrap.php`. |
| `src/` | PSR-4 source directory. Register with `$classLoader->registerNamespaceIfNotExists()`. |
| `templates/` | Twig or PHP templates. |
| `public/` | Static assets (JS, CSS, images). Accessible via web at `/interface/modules/custom_modules/<module-dir>/public/`. |
| `composer.json` | If the module has its own Composer dependencies. |
| `version.php` | Optional version tracking. |

### Directory Layout (Recommended)

```
interface/modules/custom_modules/
└── oe-module-my-module/          ← directory name = mod_directory in DB
    ├── openemr.bootstrap.php     ← REQUIRED: entry point, injected by loader
    ├── info.txt                  ← REQUIRED: one-line display name
    ├── moduleConfig.php          ← optional: config page (iframe in Module Manager)
    ├── ModuleManagerListener.php ← optional: lifecycle hooks (no namespace)
    ├── table.sql                 ← optional: DDL, run at install time
    ├── cleanup.sql               ← optional: DDL, run at uninstall
    ├── src/
    │   ├── Bootstrap.php         ← recommended: event subscriptions
    │   └── Services/
    ├── templates/
    ├── public/
    │   ├── js/
    │   └── css/
    └── README.md
```

### Naming Conventions

- All shipped custom modules use the `oe-module-` prefix (e.g., `oe-module-faxsms`, `oe-module-weno`).
- The `oe-` prefix is conventional, **not enforced** by the loader. The loader uses whatever `mod_directory` is registered in the `modules` table.
- Third-party vendors use a different prefix if they choose (no rule enforced).

---

## Section 2: Module Lifecycle

### Discovery and Registration

OpenEMR does **not** auto-discover modules from the filesystem at runtime. A module must be **registered in the `modules` database table** first.

The `modules` table key columns:
```sql
mod_name       VARCHAR(64)   -- display name
mod_directory  VARCHAR(64)   -- directory name under custom_modules/
mod_active     TINYINT(1)    -- 1 = enabled, 0 = disabled
mod_ui_active  TINYINT(1)    -- 1 = show Configure button even when disabled
type           TINYINT(4)    -- 0 = custom module, 1 = Laminas module
```

Registration is done via the **Module Manager UI** at `/interface/modules/zend_modules/public/index.php/Installer/index` (Admin → Modules → Manage Modules → Add New). It scans the filesystem for directories in `custom_modules/` that are not yet registered.

### What Happens on Bootstrap

**File:** `src/Core/ModulesApplication.php`, method `bootstrapCustomModules()` (line 132)

On every page load (after session/globals are initialized):

1. Query `modules` table for all records with `mod_active = 1 AND type != 1`, ordered by `mod_ui_order, date`.
2. For each enabled module, check that `<custom_module_path>/<mod_directory>/openemr.bootstrap.php` is readable (retries 3 times).
3. Modules whose bootstrap is missing after 3 retries are **force-disabled** (`mod_active = 0`).
4. Call `loadCustomModule()` for each available module.
5. After all modules load, fire `ModuleLoadEvents::MODULES_LOADED` event.

**File:** `src/Core/ModulesApplication.php`, method `loadCustomModule()` (line 179):
```php
include $module['path'] . '/' . attr(self::CUSTOM_MODULE_BOOSTRAP_NAME);
```
The file is included in method scope, so **these variables are available in your bootstrap**:

| Variable | Type | Source |
|----------|------|--------|
| `$classLoader` | `OpenEMR\Core\ModulesClassLoader` | Injected by loader |
| `$eventDispatcher` | `Symfony\Contracts\EventDispatcher\EventDispatcherInterface` | Injected by loader |
| `$module` | `array` with keys `name`, `directory`, `path`, `available`, `error` | Injected by loader |
| `$GLOBALS` | `array` | PHP global (all OpenEMR globals available) |
| `$_SESSION` | `array` | PHP global (auth info, patient context) |

Source: `src/Core/ModulesApplication.php:179-192`

### Enable / Disable Mechanism

- **Disable**: Module Manager sets `mod_active = 0`. Bootstrap no longer included on page load.
- **Enable**: Module Manager sets `mod_active = 1`. Bootstrap runs on next page load.
- **`mod_ui_active = 1`**: Allows the Configure button to appear even while `mod_active = 0`. Useful for requiring configuration before enabling (see Weno `ModuleManagerListener`).
- The `ModuleManagerListener::setModuleActiveState($modId, $flag, $flag_ui)` method (in `AbstractModuleActionListener`) updates both flags in one SQL call.

### Security: Script Path Enforcement

`ModulesApplication::checkModuleScriptPathForEnabledModule()` is called before bootstrap runs. If the current HTTP request's `$_SERVER['SCRIPT_NAME']` points into a module directory, it verifies that module is actually enabled. This prevents direct URL access to disabled module scripts.

---

## Section 3: Event System — Complete Reference

### How to Subscribe

In `openemr.bootstrap.php` (or your Bootstrap class):
```php
// Simple closure
$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, function(MenuEvent $event): MenuEvent {
    // ...
    return $event;
});

// Named function (used in simpler modules without Bootstrap class)
$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, 'my_module_add_menu_item');

// Method on object (used in Bootstrap class pattern)
$this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, $this->addMenuItems(...));

// With priority (higher number = runs first)
$this->eventDispatcher->addListener(AppointmentSetEvent::EVENT_HANDLE, $this->createSessionRecord(...), 10);
```

Source: `interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php:227`
Source: `interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php:199`

All events extend `Symfony\Contracts\EventDispatcher\Event` (Symfony 5+).

### Complete Event Reference

#### Menu Events
| Event Class | Constant | String Value | Data |
|-------------|----------|--------------|------|
| `OpenEMR\Menu\MenuEvent` | `MENU_UPDATE` | `'menu.update'` | `getMenu()` / `setMenu()` — array of stdClass menu items |
| `OpenEMR\Menu\MenuEvent` | `MENU_RESTRICT` | `'menu.restrict'` | Same |
| `OpenEMR\Menu\PatientMenuEvent` | `MENU_UPDATE` | `'patient.menu.update'` | Patient tabs (Dashboard, History, Report, etc.) |
| `OpenEMR\Menu\PatientMenuEvent` | `MENU_RESTRICT` | `'patient.menu.restrict'` | Same |

Source: `src/Menu/MenuEvent.php:28-34`, `src/Menu/PatientMenuEvent.php:26-32`

#### Script / Style Injection
| Event Class | Constant | String Value | Data |
|-------------|----------|--------------|------|
| `OpenEMR\Events\Core\ScriptFilterEvent` | `EVENT_NAME` | `'html.head.script.filter'` | `getScripts()` / `setScripts()` — array of JS URLs |
| `OpenEMR\Events\Core\StyleFilterEvent` | `EVENT_NAME` | `'html.head.style.filter'` | `getStyles()` / `setStyles()` — array of CSS URLs |

Both events also expose `getPageName()` (basename of the script) and `getContextArgument(ScriptFilterEvent::CONTEXT_ARGUMENT_SCRIPT_NAME)` (full script path).

Source: `src/Events/Core/ScriptFilterEvent.php:21`, `src/Events/Core/StyleFilterEvent.php:22`
Fired from: `src/Core/Header.php:100-106`

#### Patient Demographics
| Event Class | Constant | String Value | Description |
|-------------|----------|--------------|-------------|
| `OpenEMR\Events\PatientDemographics\RenderEvent` | `EVENT_SECTION_LIST_RENDER_TOP` | `'patientDemographics.render.section.top'` | Before demographics sections |
| `OpenEMR\Events\PatientDemographics\RenderEvent` | `EVENT_SECTION_LIST_RENDER_BEFORE` | `'patientDemographics.render.section.before'` | Before section list |
| `OpenEMR\Events\PatientDemographics\RenderEvent` | `EVENT_SECTION_LIST_RENDER_AFTER` | `'patientDemographics.render.section.after'` | After section list |
| `OpenEMR\Events\PatientDemographics\RenderEvent` | `EVENT_RENDER_POST_PAGELOAD` | `'patientDemographics.render.post_page_load'` | After page JS ready |
| `OpenEMR\Events\PatientDemographics\RenderPharmacySectionEvent` | `RENDER_JAVASCRIPT` | `'patientDemographics.render.javascript'` | Inject JS into demographics |
| `OpenEMR\Events\PatientDemographics\RenderPharmacySectionEvent` | `RENDER_AFTER_PHARMACY_SECTION` | `'patientDemographics.render.section.after.pharmacy'` | After default pharmacy selector |
| `OpenEMR\Events\PatientDemographics\RenderPharmacySectionEvent` | `RENDER_AFTER_SELECTED_PHARMACY_SECTION` | `'patientDemographics.render.after.selected.pharmacy'` | After selected pharmacy display |

`RenderEvent` carries `getPid()` (patient ID).
Source: `src/Events/PatientDemographics/RenderEvent.php`, `src/Events/PatientDemographics/RenderPharmacySectionEvent.php`

#### Patient CRUD
| Event Class | Constant | Description |
|-------------|----------|-------------|
| `OpenEMR\Events\Patient\BeforePatientCreatedEvent` | — | Before patient created |
| `OpenEMR\Events\Patient\PatientCreatedEvent` | — | After patient created |
| `OpenEMR\Events\Patient\PatientBeforeCreatedAuxEvent` | `EVENT_HANDLE` | Auxiliary pre-create (used by Weno for pharmacy persistence) |
| `OpenEMR\Events\Patient\BeforePatientUpdatedEvent` | — | Before patient updated |
| `OpenEMR\Events\Patient\PatientUpdatedEvent` | — | After patient updated |
| `OpenEMR\Events\Patient\PatientUpdatedEventAux` | `EVENT_HANDLE` | Auxiliary post-update |

#### Service Events (Generic)
| Event Class | Constant | String Value | Description |
|-------------|----------|--------------|-------------|
| `OpenEMR\Events\Services\ServiceSaveEvent` | `EVENT_PRE_SAVE` | `'service.save.pre'` | Before any service saves (can mutate data) |
| `OpenEMR\Events\Services\ServiceSaveEvent` | `EVENT_POST_SAVE` | `'service.save.post'` | After any service saves |
| `OpenEMR\Events\Services\ServiceDeleteEvent` | `EVENT_PRE_DELETE` | `'service.delete.pre'` | Before any service deletes |
| `OpenEMR\Events\Services\ServiceDeleteEvent` | `EVENT_POST_DELETE` | `'service.delete.post'` | After any service deletes |

`ServiceSaveEvent` carries `getService()` (the `BaseService` instance) and `getSaveData()` / `setSaveData()`. Services that dispatch these include `AppointmentService`, `VitalsService`, `EncounterService`, `InsuranceService`, `SocialHistoryService`, and others.

Source: `src/Events/Services/ServiceSaveEvent.php:24-30`

#### REST API Extension
| Event Class | Constant | String Value | Description |
|-------------|----------|--------------|-------------|
| `OpenEMR\Events\RestApiExtend\RestApiCreateEvent` | `EVENT_HANDLE` | `'restConfig.route_map.create'` | Add custom REST/FHIR/portal routes |
| `OpenEMR\Events\RestApiExtend\RestApiScopeEvent` | `EVENT_TYPE_GET_SUPPORTED_SCOPES` | `'api.scope.get-supported-scopes'` | Add OAuth2 scopes |
| `OpenEMR\Events\RestApiExtend\RestApiResourceServiceEvent` | — | — | Add custom API resource services |
| `OpenEMR\Events\RestApiExtend\RestApiSecurityCheckEvent` | — | — | Customize security checks |

`RestApiCreateEvent` provides `addToRouteMap($route, $action)`, `addToFHIRRouteMap()`, `addToPortalRouteMap()`.
Source: `src/Events/RestApiExtend/RestApiCreateEvent.php:11-72`

#### Appointments
| Event Class | Constant | String Value | Description |
|-------------|----------|--------------|-------------|
| `OpenEMR\Events\Appointments\AppointmentSetEvent` | `EVENT_HANDLE` | `'appointment.set'` | After appointment is scheduled. Carries `givenAppointmentData()` (POST data). |
| `OpenEMR\Events\Appointments\AppointmentsFilterEvent` | — | — | Filter appointment list queries |
| `OpenEMR\Events\Appointments\AppointmentRenderEvent` | — | — | Render appointment HTML |
| `OpenEMR\Events\Appointments\CalendarFilterEvent` | — | — | Filter calendar events |
| `OpenEMR\Events\Appointments\CalendarUserGetEventsFilter` | — | — | Filter per-user calendar events |
| `OpenEMR\Events\Appointments\AppointmentDialogCloseEvent` | — | — | After appointment dialog closes |

#### Main Tabs (Page Shell)
| Event Class | Constant | String Value | Description |
|-------------|----------|--------------|-------------|
| `OpenEMR\Events\Main\Tabs\RenderEvent` | `EVENT_BODY_RENDER_PRE` | `'main.body.render.pre'` | Before main body renders |
| `OpenEMR\Events\Main\Tabs\RenderEvent` | `EVENT_BODY_RENDER_NAV` | `'main.body.render.nav'` | Nav bar render point |
| `OpenEMR\Events\Main\Tabs\RenderEvent` | `EVENT_BODY_RENDER_POST` | `'main.body.render.post'` | After main body renders. Used by Comlink to inject telehealth scripts. |

Source: `src/Events/Main/Tabs/RenderEvent.php`

#### Globals / Configuration
| Event Class | Constant | String Value | Description |
|-------------|----------|--------------|-------------|
| `OpenEMR\Events\Globals\GlobalsInitializedEvent` | `EVENT_HANDLE` | `'globals.initialized'` | After globals initialized. Use to add module settings to Admin → Globals. |

Carries `getGlobalsService()` — call `$service->appendToSection($sectionName, $key, new GlobalSetting(...))`.
Source: `src/Events/Globals/GlobalsInitializedEvent.php:28`

#### Patient Documents
| Event Class | Constant | String Value | Description |
|-------------|----------|--------------|-------------|
| `OpenEMR\Events\PatientDocuments\PatientDocumentEvent` | `ACTIONS_RENDER_FAX_ANCHOR` | `'documents.actions.render.fax.anchor'` | Inject anchor buttons in document list |
| `OpenEMR\Events\PatientDocuments\PatientDocumentEvent` | `JAVASCRIPT_READY_FAX_DIALOG` | `'documents.javascript.fax.dialog'` | Inject JS for fax dialog |
| `OpenEMR\Events\PatientDocuments\PatientDocumentCreateCCDAEvent` | — | — | During CCDA creation |
| `OpenEMR\Events\PatientDocuments\PatientDocumentStoreOffsite` | — | — | Store document offsite |
| `OpenEMR\Events\PatientDocuments\PatientDocumentViewCCDAEvent` | — | — | View CCDA event |
| `OpenEMR\Events\PatientDocuments\PatientDocumentTreeViewFilterEvent` | — | — | Filter document tree |
| `OpenEMR\Events\PatientDocuments\PatientRetrieveOffsiteDocument` | — | — | Retrieve offsite document |

Source: `src/Events/PatientDocuments/PatientDocumentEvent.php`

#### Patient Report
| Event Class | Constant | String Value | Description |
|-------------|----------|--------------|-------------|
| `OpenEMR\Events\PatientReport\PatientReportEvent` | `ACTIONS_RENDER_POST` | `'patientReport.actions.render.post'` | Inject action buttons after patient report renders |
| `OpenEMR\Events\PatientReport\PatientReportEvent` | `JAVASCRIPT_READY_POST` | `'patientReport.javascript.load.post'` | Inject JS in patient report document.ready |
| `OpenEMR\Events\PatientReport\PatientReportFilterEvent` | — | — | Filter patient report data |

Source: `src/Events/PatientReport/PatientReportEvent.php`

#### Patient Summary Cards
| Event Class | Constant | String Value | Description |
|-------------|----------|--------------|-------------|
| `OpenEMR\Events\Patient\Summary\Card\SectionEvent` | `EVENT_HANDLE` | `'section.render'` | Add cards to patient summary sections |
| `OpenEMR\Events\Patient\Summary\Card\RenderEvent` | — | — | Render individual patient summary card |

`SectionEvent` carries `getSection()` and `addCard(CardInterface $card, $position)`.
Source: `src/Events/Patient/Summary/Card/SectionEvent.php`

#### Messaging
| Event Class | Constant | String Value | Description |
|-------------|----------|--------------|-------------|
| `OpenEMR\Events\Messaging\SendSmsEvent` | `ACTIONS_RENDER_SMS_POST` | `'sendSMS.actions.render.post'` | Inject SMS send button. Carries `getPid()`, `getRecipientPhone()`. |
| `OpenEMR\Events\Messaging\SendSmsEvent` | `JAVASCRIPT_READY_SMS_POST` | `'sendSMS.javascript.load.post'` | Inject SMS dialog JS |
| `OpenEMR\Events\Messaging\SendNotificationEvent` | — | — | General notification event |

Source: `src/Events/Messaging/SendSmsEvent.php`

#### Encounter
| Event Class | Constant | Description |
|-------------|----------|-------------|
| `OpenEMR\Events\Encounter\EncounterButtonEvent` | — | Add buttons to encounter |
| `OpenEMR\Events\Encounter\EncounterMenuEvent` | — | Modify encounter menu |
| `OpenEMR\Events\Encounter\EncounterFormsListRenderEvent` | — | Modify encounter forms list |
| `OpenEMR\Events\Encounter\LoadEncounterFormFilterEvent` | — | Filter which encounter forms load |

#### User Events
| Event Class | Constant | Description |
|-------------|----------|-------------|
| `OpenEMR\Events\User\UserCreatedEvent` | — | After user created |
| `OpenEMR\Events\User\UserUpdatedEvent` | — | After user updated |
| `OpenEMR\Events\User\UserEditRenderEvent` | — | Inject HTML into user edit form |

#### Core / System
| Event Class | Constant | String Value | Description |
|-------------|----------|--------------|-------------|
| `OpenEMR\Events\Core\ModuleLoadEvents` | `MODULES_LOADED` | `'modules.loaded'` | After all modules bootstrapped. Carries load status array. |
| `OpenEMR\Events\Core\TwigEnvironmentEvent` | `EVENT_CREATED` | — | After Twig environment created. Used to add template path overrides. |
| `OpenEMR\Events\Core\TemplatePageEvent` | `RENDER_EVENT` | `'events.core.page'` | General page render event |
| `OpenEMR\Events\Core\SQLUpgradeEvent` | — | — | During SQL upgrade process |

#### Other Events
| Category | Events |
|----------|--------|
| Billing | `Billing\Payments\DeletePayment`, `Billing\Payments\PostFrontPayment` |
| CDA | `CDA\CDAPreParseEvent`, `CDA\CDAPostParseEvent` |
| Facilities | `Facility\FacilityCreatedEvent`, `Facility\FacilityUpdatedEvent` |
| Patient Portal | `PatientPortal\RenderEvent`, `PatientPortal\AppointmentFilterEvent` |
| Patient Finder | `PatientFinder\PatientFinderFilterEvent`, `PatientFinder\ColumnFilter` |
| Patient Select | `PatientSelect\PatientSelectFilterEvent` |
| Services | `Services\LogoFilterEvent`, `Services\DornLabEvent`, `Services\QuestLabTransmitEvent` |
| Codes | `Codes\CodeTypeInstalledEvent`, `Codes\ExternalCodesCreatedEvent` |

### Prescription / Medication — Important Note

**There are NO dedicated prescription or medication events** in the codebase as of the current version. The `PrescriptionService` and related medication services do **not** dispatch `ServiceSaveEvent`. The Weno eRx module works by:
1. Adding pharmacy selection UI via `RenderPharmacySectionEvent` hooks in patient demographics
2. Persisting pharmacy preferences via `PatientBeforeCreatedAuxEvent` / `PatientUpdatedEventAux`
3. Adding menu items to the Reports section

If you need to intercept prescription saves, you must either extend the prescription UI directly (direct PHP file access) or use `ServiceSaveEvent` if fired by your target service. **Needs verification:** which prescription-related services fire `ServiceSaveEvent` — check `src/Services/Traits/ServiceEventTrait.php` for the full list.

---

## Section 4: Menu Integration

### Main Navigation Menu

Subscribe to `MenuEvent::MENU_UPDATE` (`'menu.update'`). The event provides `getMenu()` (array of top-level `stdClass` items) and `setMenu()`.

**Menu item stdClass structure** (from actual modules):
```php
$menuItem = new stdClass();
$menuItem->requirement = 0;          // int, always 0 in all observed modules
$menuItem->target = 'mod';           // string: frame target identifier
$menuItem->menu_id = 'mod0';         // string: unique ID for this item
$menuItem->label = xlt("My Label");  // string: translated display name
$menuItem->url = "/interface/modules/custom_modules/oe-module-mine/public/index.php";
$menuItem->children = [];            // array: child menu items (same structure)
$menuItem->acl_req = ["patients", "rx"];  // array: [section, aco] ACL pair
$menuItem->global_req = ["my_global_flag"]; // array: $GLOBALS keys that must be truthy
$menuItem->icon = "fa-caret-right";  // string: FontAwesome icon (optional)
```

Source: `interface/modules/custom_modules/oe-module-weno/src/Bootstrap.php:241-255`

**Known top-level `menu_id` values** (used to locate insertion points):
- `admimg` — Administration section
- `modimg` — Modules section
- `repimg` — Reports section
- `service` — Services section (added by faxsms module, not built-in)

**ACL sections and ACOs** follow the OpenEMR ACL scheme. Common pairs:
- `["patients", "rx"]` — Prescriptions
- `["patients", "docs"]` — Patient documents
- `["admin", "super"]` — Admin only
- `["admin", "docs"]` — Admin documents
- `["patients", "demo"]` — Patient demographics

**Complete example from oe-module-weno** (`src/Bootstrap.php:238-320`):
```php
public function addCustomMenuItem(MenuEvent $event): MenuEvent
{
    $menu = $event->getMenu();

    $menuItem = new \stdClass();
    $menuItem->requirement = 0;
    $menuItem->target = 'rep';
    $menuItem->menu_id = 'rep0';
    $menuItem->label = xlt("Weno Prescription Log");
    $menuItem->url = "/interface/modules/custom_modules/oe-module-weno/templates/rxlogmanager.php";
    $menuItem->children = [];
    $menuItem->acl_req = ["patients", "rx"];
    $menuItem->global_req = ["weno_rx_enable"];

    foreach ($menu as $item) {
        if ($item->menu_id == 'repimg') {
            foreach ($item->children as $clientReport) {
                if ($clientReport->label == 'Clients') {
                    $clientReport->children[] = $menuItem;
                    break 2;
                }
            }
        }
    }

    $event->setMenu($menu);
    return $event;
}
```

Register: `$this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, $this->addCustomMenuItem(...));`

### Patient Tab Menu

Subscribe to `PatientMenuEvent::MENU_UPDATE` (`'patient.menu.update'`). Adds or modifies tabs at the top of the patient record (Dashboard, History, Report, Documents, etc.).

**Example from oe-module-prior-authorizations** (`openemr.bootstrap.php:65-80`):
```php
function oe_module_priorauth_patient_menu_item(PatientMenuEvent $menuEvent)
{
    $existingMenu = $menuEvent->getMenu();

    $menuItem = new stdClass();
    $menuItem->label = "Auths";
    $menuItem->url = $GLOBALS['webroot'] . "/interface/modules/custom_modules/oe-module-prior-authorizations/public/index.php";
    $menuItem->menu_id = "mod_pa";
    $menuItem->target = "mod";

    $existingMenu[] = $menuItem;
    $menuEvent->setMenu($existingMenu);
    return $menuEvent;
}
$eventDispatcher->addListener(PatientMenuEvent::MENU_UPDATE, 'oe_module_priorauth_patient_menu_item');
```

---

## Section 5: Script / Asset Injection

### How It Works

Every time `Header::setupHeader()` is called (in `<head>` of each page), it fires two events:
1. `ScriptFilterEvent` (`'html.head.script.filter'`) — collect `<script src="">` tags
2. `StyleFilterEvent` (`'html.head.style.filter'`) — collect `<link rel="stylesheet">` tags

**Security restriction**: Scripts and styles are filtered by `ModulesApplication::filterSafeLocalModuleFiles()`. Only files whose real path starts with `interface/modules/` are allowed. This prevents modules from injecting external scripts.

Source: `src/Core/Header.php:97-130`, `src/Core/ModulesApplication.php:231-259`

### Injecting JS/CSS Conditionally

```php
use OpenEMR\Events\Core\ScriptFilterEvent;
use OpenEMR\Events\Core\StyleFilterEvent;

// In bootstrap or Bootstrap class:
$eventDispatcher->addListener(ScriptFilterEvent::EVENT_NAME, function(ScriptFilterEvent $event): void {
    $pageName = $event->getPageName(); // e.g. 'pnuserapi.php'
    $fullPath = $event->getContextArgument(ScriptFilterEvent::CONTEXT_ARGUMENT_SCRIPT_NAME);

    if ($pageName === 'pnuserapi.php') {
        $scripts = $event->getScripts();
        $scripts[] = $GLOBALS['web_root'] . '/interface/modules/custom_modules/oe-module-mine/public/js/my-script.js';
        $event->setScripts($scripts);
    }
});

$eventDispatcher->addListener(StyleFilterEvent::EVENT_NAME, function(StyleFilterEvent $event): void {
    if ($event->getPageName() === 'pnuserapi.php') {
        $styles = $event->getStyles();
        $styles[] = $GLOBALS['web_root'] . '/interface/modules/custom_modules/oe-module-mine/public/css/my-style.css';
        $event->setStyles($styles);
    }
});
```

**Concrete example from oe-module-comlink-telehealth** (`src/Controller/TeleHealthCalendarController.php:136-194`):
```php
public function addCalendarStylesheet(StyleFilterEvent $event)
{
    if ($this->isCalendarPageInclude($event->getPageName())) {
        $styles = $event->getStyles();
        $styles[] = $this->getAssetPath() . CacheUtils::addAssetCacheParamToPath("css/telehealth.css");
        $event->setStyles($styles);
    }
}

public function addCalendarJavascript(ScriptFilterEvent $event)
{
    $pageName = $event->getPageName();
    if ($this->isCalendarPageInclude($pageName)) {
        $scripts = $event->getScripts();
        $scripts[] = $this->getAssetPath() . "js/telehealth-calendar.js";
        $event->setScripts($scripts);
    }
}

// Register both in subscribeToEvents():
$eventDispatcher->addListener(ScriptFilterEvent::EVENT_NAME, $this->addCalendarJavascript(...));
$eventDispatcher->addListener(StyleFilterEvent::EVENT_NAME, $this->addCalendarStylesheet(...));
```

### Injecting Inline HTML / JS into Pages

For inline HTML output (buttons, forms, dialogs), use pattern-specific events like `PatientReportEvent::ACTIONS_RENDER_POST` and echo directly from the listener. The listener function echoes PHP/HTML:

```php
function oe_module_faxsms_patient_report_render_action_buttons(Event $event): void
{
    ?>
    <button type="button" class="genfax btn btn-success btn-sm">
        <?php echo xlt('Send Fax'); ?>
    </button>
    <?php
}
$eventDispatcher->addListener(PatientReportEvent::ACTIONS_RENDER_POST, 'oe_module_faxsms_patient_report_render_action_buttons');
```

Source: `interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php:232-237`

---

## Section 6: Laminas Routing

### Custom Modules Do NOT Use Laminas Routing

None of the 8 custom modules surveyed have a `config/module.config.php`. Custom modules serve pages via **direct PHP file access** at their URL paths:

```
/interface/modules/custom_modules/oe-module-faxsms/messageUI.php?type=sms
/interface/modules/custom_modules/oe-module-prior-authorizations/public/index.php
/interface/modules/custom_modules/oe-module-weno/templates/rxlogmanager.php
```

These files include `globals.php` directly:
```php
require_once(__DIR__ . "/../../../globals.php");
// or:
require_once dirname(__FILE__, 4) . '/globals.php';
```

### Laminas Routing Is for Core Modules Only

Laminas MVC routing (`config/module.config.php`) is used by the **core Laminas modules** in `interface/modules/zend_modules/module/` (Installer, Documents, FHIR, etc.). The format is:

```php
// interface/modules/zend_modules/module/Installer/config/module.config.php
return [
    'controllers' => [
        'factories' => [
            Installer\Controller\InstallerController::class => function (ContainerInterface $container, $requestedName) {
                // dependency injection
                return new Installer\Controller\InstallerController($InstModuleTable);
            },
        ]
    ],
    'router' => [
        'routes' => [
            'Installer' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/Installer[/:action][/:id]',
                    'defaults' => [
                        'controller' => Installer\Controller\InstallerController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    // ...
];
```

Source: `interface/modules/zend_modules/module/Installer/config/module.config.php`

**For new custom modules: use direct PHP file access, not Laminas routing.**

---

## Section 7: Database Access from Modules

### Defining Tables (table.sql)

The `table.sql` file uses OpenEMR's conditional SQL extension syntax. These are parsed by `SQLUpgradeService`, not raw MySQL:

```sql
-- Only create if table doesn't exist
#IfNotTable my_module_records
CREATE TABLE `my_module_records` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pid` BIGINT(20) NOT NULL,
    `data` TEXT,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `pid` (`pid`)
) ENGINE=InnoDB;
#EndIf

-- Only add column if missing
#IfMissingColumn my_module_records status
ALTER TABLE `my_module_records` ADD `status` VARCHAR(50) DEFAULT 'pending';
#EndIf

-- Only insert if row doesn't exist
#IfNotRow globals gl_name my_module_setting
INSERT INTO `globals` (`gl_name`, `gl_value`) VALUES ('my_module_setting', '0');
#EndIf

-- Only run if row exists
#IfRow globals gl_name old_setting_name
UPDATE `globals` SET gl_name='new_setting_name' WHERE gl_name='old_setting_name';
#EndIf

-- Only add index if not present
#IfNotIndex my_module_records my_index_name
ALTER TABLE `my_module_records` ADD INDEX `my_index_name` (`pid`, `status`);
#EndIf
```

Source: `interface/modules/custom_modules/oe-module-weno/table.sql`, `interface/modules/custom_modules/oe-module-faxsms/table.sql`

### Querying Data

Modules use OpenEMR's procedural ADODB wrapper functions directly (no ORM):

```php
// Single row — returns associative array or false
$row = sqlQuery("SELECT * FROM my_module_records WHERE pid = ? LIMIT 1", [$pid]);
$value = $row['data'] ?? null;

// Multi-row result set
$resultSet = sqlStatement("SELECT * FROM my_module_records WHERE status = ?", ['pending']);
while ($row = sqlFetchArray($resultSet)) {
    // process $row
}

// Insert / Update / Delete (no log)
sqlStatementNoLog("UPDATE modules SET mod_active = 0 WHERE mod_directory = ?", [$directory]);

// Weno module example (src/Bootstrap.php:401):
$provider = sqlQuery("SELECT weno_prov_id FROM users WHERE id = ?", [$id]);
return $provider['weno_prov_id'] ?? '';
```

### Using OpenEMR Services

Modules can instantiate any OpenEMR service directly:

```php
use OpenEMR\Services\PatientService;
use OpenEMR\Services\PrescriptionService;

$patientService = new PatientService();
$patient = $patientService->getOne($puuid);
```

### Registering Background Services

Add to `table.sql`:
```sql
#IfNotRow background_services name MyModuleTask
INSERT INTO `background_services`
    (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`)
VALUES
    ('MyModuleTask', 'My Module Background Task', '0', '0', current_timestamp(), '30',
     'myModuleFunction',
     '/interface/modules/custom_modules/oe-module-mine/scripts/background_task.php',
     '100');
#EndIf
```

Source: `interface/modules/custom_modules/oe-module-weno/table.sql:73-79`

---

## Section 8: Coding Conventions (Observed from Real Modules)

### No `declare(strict_types=1)`

Zero modules use `declare(strict_types=1)`. This is consistent with the rest of OpenEMR. Do not add it.

### Namespace Conventions

- OpenEMR-maintained modules: `OpenEMR\Modules\ModuleName\` (e.g., `OpenEMR\Modules\WenoModule\`, `OpenEMR\Modules\EhiExporter\`)
- Third-party vendor modules: `VendorName\OpenEMR\Modules\ModuleName\` (e.g., `Comlink\OpenEMR\Modules\TeleHealthModule\`, `Juggernaut\OpenEMR\Modules\PriorAuthModule\`)
- The `ModuleManagerListener.php` file **must NOT declare a namespace**. The loader instantiates it as a classname-only reference.

### File Header Format

```php
<?php

/**
 * Brief description of file purpose.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Author Name <author@example.com>
 * @copyright Copyright (c) 2024 Author Name
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
```

Source: Every file in the codebase.

### Type Hints

Mixed usage. Newer modules (Comlink, EhiExporter) use typed properties, readonly, first-class callables (`$this->method(...)`). Older modules (FaxSMS, PriorAuth) use less typing. Match the style of the file you're editing.

```php
// Modern style (PHP 8.1+) — seen in Comlink, EhiExporter, Weno
public function __construct(
    private readonly EventDispatcherInterface $eventDispatcher,
    ?Kernel $kernel = null
) {}

// Older style — seen in FaxSMS, PriorAuth
public function __construct()
{
    parent::__construct();
    $this->service = new BootstrapService();
}
```

### Bootstrap File Pattern: Two Approaches

**Approach 1: Flat bootstrap (simpler modules)**
Everything in `openemr.bootstrap.php` — define functions globally, add listeners directly:
```php
// oe-module-prior-authorizations/openemr.bootstrap.php
$classLoader->registerNamespaceIfNotExists('Juggernaut\\OpenEMR\\Modules\\PriorAuthModule\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

function oe_module_priorauth_add_menu_item(MenuEvent $event) { ... }
$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, 'oe_module_priorauth_add_menu_item');
```

**Approach 2: Bootstrap class (complex modules)**
Delegate to a `Bootstrap` class in `src/Bootstrap.php`:
```php
// oe-module-weno/openemr.bootstrap.php
namespace OpenEMR\Modules\WenoModule;
$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\WenoModule\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');
$bootstrap = new Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();
```

### Error Handling

- Use `error_log(errorLogEscape($message))` for logging.
- In `ModuleManagerListener`, return a string error message from action methods — Module Manager displays it to the user.
- In Bootstrap classes, wrap risky operations in `try/catch`: `ModulesApplication::loadCustomModule()` catches `\Throwable` at the top level, so a bootstrap exception will just log and skip the module.

### Translating Strings

Use standard OpenEMR translation functions:
- `xlt("string")` — translate (for output in HTML)
- `xl("string")` — translate (raw, no escaping)
- `xlj("string")` — translate for JavaScript context

---

## Section 9: Complete Module Skeleton

A minimal module that adds a menu item and injects a script. Copy-paste ready and follows all observed conventions.

### Directory: `interface/modules/custom_modules/oe-module-safety-sentinel/`

---

**`info.txt`**
```
Safety Sentinel Module
```

---

**`openemr.bootstrap.php`**
```php
<?php

/**
 * Bootstrap for Safety Sentinel module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2024 Your Name
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel;

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Core\OEGlobalsBag;

/**
 * @global ModulesClassLoader $classLoader
 */
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\SafetySentinel\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src'
);

/**
 * @global \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
 */
$bootstrap = new Bootstrap($eventDispatcher, OEGlobalsBag::getInstance()->getKernel());
$bootstrap->subscribeToEvents();
```

---

**`src/Bootstrap.php`**
```php
<?php

/**
 * Bootstrap class for Safety Sentinel module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2024 Your Name
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel;

use OpenEMR\Core\Kernel;
use OpenEMR\Events\Core\ScriptFilterEvent;
use OpenEMR\Events\Core\StyleFilterEvent;
use OpenEMR\Menu\MenuEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    const MODULE_INSTALLATION_PATH = '/interface/modules/custom_modules/oe-module-safety-sentinel';

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ?Kernel $kernel = null
    ) {}

    public function subscribeToEvents(): void
    {
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, $this->addMenuItems(...));
        $this->eventDispatcher->addListener(ScriptFilterEvent::EVENT_NAME, $this->addScripts(...));
        $this->eventDispatcher->addListener(StyleFilterEvent::EVENT_NAME, $this->addStyles(...));
    }

    public function addMenuItems(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'mod';
        $menuItem->menu_id = 'safety_sentinel';
        $menuItem->label = xlt('Safety Sentinel');
        $menuItem->url = $GLOBALS['webroot'] . self::MODULE_INSTALLATION_PATH . '/public/index.php';
        $menuItem->children = [];
        $menuItem->acl_req = ['patients', 'rx'];
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if ($item->menu_id == 'repimg') {
                $item->children[] = $menuItem;
                break;
            }
        }

        $event->setMenu($menu);
        return $event;
    }

    public function addScripts(ScriptFilterEvent $event): void
    {
        // Inject on all pages — filter by pagename if needed
        // if ($event->getPageName() === 'specific_page.php') { ... }
        $scripts = $event->getScripts();
        $scripts[] = $GLOBALS['web_root'] . self::MODULE_INSTALLATION_PATH . '/public/js/safety-sentinel.js';
        $event->setScripts($scripts);
    }

    public function addStyles(StyleFilterEvent $event): void
    {
        $styles = $event->getStyles();
        $styles[] = $GLOBALS['web_root'] . self::MODULE_INSTALLATION_PATH . '/public/css/safety-sentinel.css';
        $event->setStyles($styles);
    }
}
```

---

**`moduleConfig.php`** (optional — config page rendered by Module Manager)
```php
<?php

/**
 * Module configuration page for Safety Sentinel.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2024 Your Name
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\ModulesClassLoader;

require_once dirname(__FILE__, 4) . '/globals.php';

$classLoader = new ModulesClassLoader($GLOBALS['fileroot']);
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\SafetySentinel\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src'
);

$module_config = 1;
require_once __DIR__ . '/templates/config.php';
exit;
```

---

**`ModuleManagerListener.php`** (no namespace — required by Laminas loader)
```php
<?php

/**
 * ModuleManagerListener for Safety Sentinel.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2024 Your Name
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// DO NOT declare a namespace here.

use OpenEMR\Core\AbstractModuleActionListener;

class ModuleManagerListener extends AbstractModuleActionListener
{
    public function __construct()
    {
        parent::__construct();
    }

    public function moduleManagerAction($methodName, $modId, string $currentActionStatus = 'Success'): string
    {
        if (method_exists(self::class, $methodName)) {
            return self::$methodName($modId, $currentActionStatus);
        }
        return $currentActionStatus;
    }

    public static function getModuleNamespace(): string
    {
        return 'OpenEMR\\Modules\\SafetySentinel\\';
    }

    public static function initListenerSelf(): ModuleManagerListener
    {
        return new self();
    }

    private function install($modId, $currentActionStatus): mixed
    {
        return $currentActionStatus;
    }

    private function enable($modId, $currentActionStatus): mixed
    {
        return $currentActionStatus;
    }

    private function disable($modId, $currentActionStatus): mixed
    {
        return $currentActionStatus;
    }

    private function unregister($modId, $currentActionStatus): mixed
    {
        // Clean up tables if needed
        return $currentActionStatus;
    }

    private function install_sql($modId, $currentActionStatus): mixed
    {
        return $currentActionStatus;
    }

    private function upgrade_sql($modId, $currentActionStatus): mixed
    {
        return $currentActionStatus;
    }
}
```

---

**`table.sql`** (DDL for module tables)
```sql
#IfNotTable safety_sentinel_checks
CREATE TABLE `safety_sentinel_checks` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pid` BIGINT(20) NOT NULL,
    `drug_name` VARCHAR(255) NOT NULL,
    `drug_rxnorm` VARCHAR(64) DEFAULT NULL,
    `severity` VARCHAR(32) NOT NULL DEFAULT 'unknown',
    `result_json` LONGTEXT,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `pid` (`pid`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB COMMENT='Safety Sentinel check results';
#EndIf
```

---

**`public/index.php`** (module UI page)
```php
<?php

/**
 * Safety Sentinel main UI page.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2024 Your Name
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../../../../globals.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'rx')) {
    die(xlt('Not Authorized'));
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('Safety Sentinel'); ?></title>
</head>
<body>
    <div class="container-fluid">
        <h2><?php echo xlt('Safety Sentinel'); ?></h2>
        <!-- Your UI here -->
    </div>
</body>
</html>
```

---

## Quick Reference: Variables Available in Bootstrap

```php
// openemr.bootstrap.php — these are in scope:
$classLoader      // OpenEMR\Core\ModulesClassLoader — register namespace
$eventDispatcher  // Symfony EventDispatcherInterface — add listeners
$module           // array: ['name', 'directory', 'path', 'available', 'error']
$GLOBALS          // all OpenEMR globals (webroot, fileroot, etc.)
$_SESSION         // authUserID, authUser, pid, site_id, etc.
```

## Quick Reference: Event Strings Cheatsheet

```php
// Menu
'menu.update'                                    // MenuEvent::MENU_UPDATE
'patient.menu.update'                            // PatientMenuEvent::MENU_UPDATE

// Scripts / Styles
'html.head.script.filter'                        // ScriptFilterEvent::EVENT_NAME
'html.head.style.filter'                         // StyleFilterEvent::EVENT_NAME

// Patient demographics
'patientDemographics.render.section.before'      // RenderEvent::EVENT_SECTION_LIST_RENDER_BEFORE
'patientDemographics.render.section.after'       // RenderEvent::EVENT_SECTION_LIST_RENDER_AFTER
'patientDemographics.render.after.selected.pharmacy' // RenderPharmacySectionEvent

// Globals settings
'globals.initialized'                            // GlobalsInitializedEvent::EVENT_HANDLE

// REST API
'restConfig.route_map.create'                    // RestApiCreateEvent::EVENT_HANDLE

// Services (generic)
'service.save.pre'                               // ServiceSaveEvent::EVENT_PRE_SAVE
'service.save.post'                              // ServiceSaveEvent::EVENT_POST_SAVE

// Appointments
'appointment.set'                                // AppointmentSetEvent::EVENT_HANDLE

// Main tab page shell
'main.body.render.post'                          // RenderEvent::EVENT_BODY_RENDER_POST

// Module lifecycle
'modules.loaded'                                 // ModuleLoadEvents::MODULES_LOADED
```
