<?php

/**
 * Paytrace Gateway Member
 * link    http://www.open-emr.org
 * author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * Copyright (c) 2020. Sherwin Gaddis <sherwingaddis@gmail.com>
 * license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Events\PatientDocuments\PatientDocumentEvent;
use OpenEMR\Events\PatientReport\PatientReportEvent;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Services\Globals\GlobalSetting;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

function paytrace_gateway_add_menu_item(MenuEvent $event)
{
    $menu = $event->getMenu();

    $menuItem = new stdClass();
    $menuItem->requirement = 1;
    $menuItem->target = 'patimg';
    $menuItem->menu_id = 'ptg0';
    $menuItem->label = xlt("PayTrace CC On File");
    $menuItem->url = "/interface/modules/custom_modules/paytrace-gateway/cardUI.php";
    $menuItem->children = [];
    $menuItem->acl_req = ["patients", "docs"];
    //$menuItem->global_req = ["paytrace_enable"];

    foreach ($menu as $item) {
        if ($item->menu_id == 'patimg') {
            $item->children[] = $menuItem;
            break;
        }
    }

    $event->setMenu($menu);

    return $event;
}

function paytrace_fees_add_menu_item(MenuEvent $event)
{
    $menu = $event->getMenu();

    $menuItem = new stdClass();
    $menuItem->requirement = 0;
    $menuItem->target = 'feeimg';
    $menuItem->menu_id = 'ptg0';
    $menuItem->label = xlt("Batch CC Payments");
    $menuItem->url = "/interface/modules/custom_modules/paytrace-gateway/chargesUI.php";
    $menuItem->children = [];
    $menuItem->acl_req = ["patients", "docs"];
    //$menuItem->global_req = ["paytrace_enable"];

    foreach ($menu as $item) {
        if ($item->menu_id == 'feeimg') {
            $item->children[] = $menuItem;
            break;
        }
    }

    $event->setMenu($menu);

    return $event;
}

/**
 * @var EventDispatcherInterface $eventDispatcher
 * @var array                    $module
 * @global                       $eventDispatcher @see ModulesApplication::loadCustomModule
 * @global                       $module          @see ModulesApplication::loadCustomModule
 */
$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, 'paytrace_gateway_add_menu_item');
$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, 'paytrace_fees_add_menu_item');


function createPaytraceModuleGlobals(GlobalsInitializedEvent $event)
{
    $select_array = array(0 => xl('Username'), 1 => xl('Password'));
    $instruct = xl('Obtain account username and password from Paytrace.');

    $event->getGlobalsService()->createSection("Paytrace", "Report");
    $setting = new GlobalSetting(xl('Set credentials'), $select_array, 2, $instruct);
    $event->getGlobalsService()->appendToSection("Paytrace", "paytrace_info", $setting);
}

$eventDispatcher->addListener(GlobalsInitializedEvent::EVENT_HANDLE, 'createPaytraceModuleGlobals');
