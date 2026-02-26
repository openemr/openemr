# OpenEMR Custom Module System

## Module Location

Custom modules live in:
```
interface/modules/custom_modules/oc-<module-name>/
```

The `oc-` prefix is the convention for community/custom modules.

## Minimum Module Structure

```
interface/modules/custom_modules/oc-safety-sentinel/
├── openemr.bootstrap.php       ← Entry point — OpenEMR loads this
├── moduleConfig.php            ← Module metadata (name, version, etc.)
├── table.sql                   ← Database tables (run on install)
├── Module.php                  ← Optional Laminas module class
├── src/                        ← PSR-4 classes (OpenEMR\Modules\SafetySentinel\)
│   ├── Controller/
│   │   └── SafetyController.php
│   └── SafetySentinelSubscriber.php
├── config/
│   └── module.config.php       ← Laminas routing config (if using controllers)
├── public/
│   ├── js/
│   └── css/
└── templates/
    └── safety-panel.twig
```

## openemr.bootstrap.php (Required)

This is the entry point. OpenEMR's module manager loads this file when the module is active.

```php
<?php

/**
 * Bootstrap for Safety Sentinel module
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2026 Your Name
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel;

// Autoload module classes
// Option A: Composer autoload (if module has its own composer.json)
// require_once __DIR__ . '/vendor/autoload.php';

// Option B: Manual class loading via OpenEMR's class loader
use OpenEMR\Core\ModulesClassLoader;

$classLoader = new ModulesClassLoader($GLOBALS['fileroot']);
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\SafetySentinel\\',
    __DIR__ . '/src/'
);

// Register event subscribers
/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
$subscriber = new SafetySentinelSubscriber();
$eventDispatcher->addSubscriber($subscriber);
```

## moduleConfig.php

```php
<?php

/**
 * Module configuration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// This array is read by the module manager
return [
    'name' => 'Safety Sentinel',
    'version' => '1.0.0',
    'description' => 'AI-powered clinical safety agent for prescription verification',
    'author' => 'Your Name',
    'license' => 'GPL-3.0',
    'requires' => [
        'openemr' => '>=7.0.0',
        'php' => '>=8.2.0',
    ],
];
```

## Module Registration

Modules are registered via the OpenEMR admin UI or by SQL insert:

```sql
-- Register the module in OpenEMR's module tracking table
INSERT INTO `modules` (`mod_name`, `mod_directory`, `mod_active`, `mod_ui_active`)
VALUES ('Safety Sentinel', 'oc-safety-sentinel', 1, 1);
```

Or through the admin panel: **Administration → Modules → Custom Modules → Install**

## Adding Menu Items

Use the `menu.update` event to add navigation entries:

```php
<?php

namespace OpenEMR\Modules\SafetySentinel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SafetySentinelSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'menu.update' => ['addMenuItems', 0],
        ];
    }

    public function addMenuItems($event): void
    {
        $menu = $event->getMenu();

        // Find the appropriate parent menu and add entry
        $newItem = new \stdClass();
        $newItem->requirement = 0;
        $newItem->target = 'mod';
        $newItem->menu_id = 'mod0';
        $newItem->label = xl('Safety Sentinel');
        $newItem->url = '/interface/modules/custom_modules/oc-safety-sentinel/public/index.php';
        $newItem->children = [];
        $newItem->acl_req = ['patients', 'med'];
        $newItem->global_req = [];

        // Append to modules menu
        foreach ($menu as $menuItem) {
            if ($menuItem->menu_id === 'modimg') {
                $menuItem->children[] = $newItem;
                break;
            }
        }
    }
}
```

## Injecting JS/CSS into Pages

Use the `script.text` event to add scripts to OpenEMR pages:

```php
<?php

public function onScriptText($event): void
{
    // Only inject on encounter pages where prescribing happens
    $currentPage = $_SERVER['REQUEST_URI'] ?? '';

    if (strpos($currentPage, 'encounter') !== false) {
        $modulePath = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oc-safety-sentinel';
        $event->addText(
            '<link rel="stylesheet" href="' . attr($modulePath) . '/public/css/sidebar.css">' .
            '<script src="' . attr($modulePath) . '/public/js/sidebar.js"></script>'
        );
    }
}
```

## Accessing Patient Data from a Module

```php
<?php

namespace OpenEMR\Modules\SafetySentinel\Controller;

use OpenEMR\Services\PatientService;
use OpenEMR\Common\Acl\AclMain;

class SafetyController
{
    /**
     * Get current patient context from OpenEMR's session
     */
    public function getCurrentPatient(): ?array
    {
        // OpenEMR stores the current patient in session/globals
        $pid = $_SESSION['pid'] ?? null;

        if ($pid === null) {
            return null;
        }

        // Check ACL — does this user have access to patient data?
        if (!AclMain::aclCheckCore('patients', 'med')) {
            return null;
        }

        $patientService = new PatientService();
        return $patientService->findByPid($pid);
    }

    /**
     * Proxy a safety check request to the FastAPI backend
     */
    public function checkSafety(string $patientId, string $drugName): array
    {
        // Call the Safety Sentinel FastAPI backend
        $url = ($GLOBALS['safety_sentinel_url'] ?? 'http://localhost:8001') . '/api/v1/safety-check';

        $payload = json_encode([
            'patient_id' => $patientId,
            'message' => "Is it safe to prescribe {$drugName} for this patient?",
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            return ['error' => 'Safety check service unavailable'];
        }

        return json_decode($response, true);
    }
}
```

## Existing Module Examples

Look at these in the OpenEMR codebase for reference patterns:

```bash
# List all custom modules
ls interface/modules/custom_modules/

# Good reference modules to study
interface/modules/custom_modules/oe-module-claimrev-connect/
interface/modules/custom_modules/oe-module-faxsms/
interface/modules/custom_modules/oe-module-comlink-telehealth/
```

## Key Gotchas

- **`$GLOBALS` is used extensively** — OpenEMR passes config via globals, not DI. Accept this.
- **`xl()` for translations** — Wrap all user-facing strings in `xl('string')`.
- **`attr()` for HTML attributes** — Use `attr($value)` to escape output in HTML attributes.
- **`text()` for HTML content** — Use `text($value)` to escape output in HTML body.
- **ACL checks are required** — Always verify the user has permission via `AclMain::aclCheckCore()`.
- **Session-based patient context** — The current patient is in `$_SESSION['pid']`, not passed via URL.
- **Module assets** — Static files go in the module's `public/` directory, accessed via webroot-relative paths.