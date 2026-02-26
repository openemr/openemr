# Symfony Patterns in OpenEMR

OpenEMR uses **Symfony EventDispatcher** as its event system — it does NOT use the full Symfony framework. No Symfony routing, no Symfony DI container, no Symfony controllers. Only the EventDispatcher component.

## Event Dispatcher (How OpenEMR Uses It)

OpenEMR fires events at key points in clinical workflows. Custom modules subscribe to these events to add behavior without modifying core code.

```php
<?php

/**
 * Listening to OpenEMR events in a custom module
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SafetySentinelSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Subscribe to OpenEMR's built-in events
        return [
            // Menu event — add items to the main navigation
            'menu.update' => ['onMenuUpdate', 0],

            // Script event — inject JS/CSS into pages
            'script.text' => ['onScriptText', 0],
        ];
    }

    public function onMenuUpdate($event): void
    {
        // Add a menu entry for the safety sentinel panel
        $menu = $event->getMenu();
        // ... modify menu structure
    }

    public function onScriptText($event): void
    {
        // Inject a script tag into OpenEMR pages
        $event->addText('<script src="/modules/custom_modules/oc-safety-sentinel/public/js/sidebar.js"></script>');
    }
}
```

## Registering Event Subscribers in a Custom Module

In OpenEMR, event subscribers are registered in the module's bootstrap, not via YAML config.

```php
<?php

/**
 * Module bootstrap — registers event subscribers
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel;

use OpenEMR\Core\ModulesClassLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

// The module manager calls this file on module load
// $eventDispatcher is provided by OpenEMR's kernel

/** @var EventDispatcherInterface $eventDispatcher */
$subscriber = new SafetySentinelSubscriber();
$eventDispatcher->addSubscriber($subscriber);
```

## Common OpenEMR Events

These are events that OpenEMR dispatches that custom modules can hook into:

| Event | When It Fires | Use Case |
|-------|--------------|----------|
| `menu.update` | Main menu renders | Add navigation items |
| `script.text` | Page head renders | Inject JS/CSS |
| `patient.edit.update.pre` | Before patient save | Validate patient data |
| `patient.edit.update.post` | After patient save | Trigger downstream actions |

**Important:** OpenEMR's event names and signatures may vary by version. Always check the current codebase by searching for `$eventDispatcher->dispatch(` to find available events.

## Finding Events in the Codebase

```bash
# Find all event dispatches in OpenEMR
grep -rn "eventDispatcher->dispatch" src/ interface/ --include="*.php"

# Find all event subscriber registrations
grep -rn "addSubscriber\|addListener" src/ interface/ --include="*.php"

# Find existing custom modules to use as templates
ls interface/modules/custom_modules/
```

## Key Differences from Pure Symfony

| Aspect | Pure Symfony | OpenEMR |
|--------|-------------|---------|
| DI Container | Full Symfony DI | Not used — manual instantiation or `$GLOBALS` |
| Event registration | `services.yaml` with tags | PHP bootstrap file in module |
| Controllers | Symfony `AbstractController` | Plain PHP files in `/interface/` or Laminas controllers |
| Routing | Symfony Router with attributes | Laminas routing or direct file includes |
| Config | YAML/XML/PHP config | `$GLOBALS`, `.env`, `sqlconf.php` |

## Do NOT Use These Symfony Patterns in OpenEMR

- `#[Route()]` attributes — OpenEMR doesn't use Symfony routing
- `#[AsMessageHandler]` — OpenEMR doesn't use Symfony Messenger
- `AbstractController` — OpenEMR doesn't extend Symfony controllers
- `#[MapRequestPayload]` — OpenEMR doesn't use Symfony's request mapping
- `services.yaml` autowiring — OpenEMR doesn't use Symfony's DI container
- Voters (`Voter` class) — OpenEMR has its own ACL system