<?php
/**
 *
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


function paytrace_add_menu_item(MenuEvent $event)
{
    $menu = $event->getMenu();

    $menuItem = new stdClass();
    $menuItem->requirement = 0;
    $menuItem->target = 'mod';
    $menuItem->menu_id = 'mod0';
    $menuItem->label = xlt("PayTrace");
    $menuItem->url = "/interface/modules/custom_modules/paytrace-gateway-module/chargesUI.php";
    $menuItem->children = [];
    $menuItem->acl_req = ["patients", "docs"];
    $menuItem->global_req = ["paytrace_enable"];

    foreach ($menu as $item) {
        if ($item->menu_id == 'modimg') {
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
$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, 'paytrace_add_menu_item');


