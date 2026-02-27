<?php

/**
 * Safety Sentinel Module Bootstrap
 *
 * Registers a new "Safety Check" tab in the patient chart navigation
 * and adds REST API routes for audit log access.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;
use OpenEMR\Menu\PatientMenuEvent;

// Register PSR-4-style autoloader for this module's src/ classes.
// Maps OpenEMR\Modules\SafetySentinel\* → {module_dir}/src/*
spl_autoload_register(function (string $class): void {
    $prefix = 'OpenEMR\\Modules\\SafetySentinel\\';
    $base_dir = __DIR__ . '/src/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $base_dir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

/**
 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
 */
function oe_module_safety_sentinel_add_tab(PatientMenuEvent $menuEvent)
{
    $existingMenu = $menuEvent->getMenu();

    $menuItem              = new stdClass();
    $menuItem->label       = xlt("Safety Check");
    $menuItem->url         = $GLOBALS['webroot']
        . "/interface/modules/custom_modules/oe-module-safety-sentinel/public/index.php";
    $menuItem->menu_id     = "mod_safety_sentinel";
    $menuItem->target      = "main";
    $menuItem->on_click    = "top.restoreSession()";
    $menuItem->pid         = "false";   // We read $_SESSION['pid'] ourselves
    $menuItem->children    = [];
    $menuItem->requirement = 0;

    $existingMenu[] = $menuItem;
    $menuEvent->setMenu($existingMenu);

    return $menuEvent;
}

$eventDispatcher->addListener(
    PatientMenuEvent::MENU_UPDATE,
    'oe_module_safety_sentinel_add_tab'
);

// Register Safety Sentinel REST routes into OpenEMR's route map.
$eventDispatcher->addListener(
    RestApiCreateEvent::EVENT_HANDLE,
    function (RestApiCreateEvent $event): void {
        $routes = require __DIR__ . '/_rest_routes.inc.php';
        foreach ($routes as $route => $action) {
            $event->addToRouteMap($route, $action);
        }
    }
);
