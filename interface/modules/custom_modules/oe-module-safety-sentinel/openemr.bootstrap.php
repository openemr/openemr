<?php

/**
 * Safety Sentinel Module Bootstrap
 *
 * Registers a new "Safety Check" tab in the patient chart navigation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Menu\PatientMenuEvent;

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
