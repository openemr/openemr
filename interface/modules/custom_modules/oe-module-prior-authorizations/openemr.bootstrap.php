<?php

/*
 *
 * @package      OpenEMR
 * @link               https://www.open-emr.org
 *
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * All Rights Reserved
 *
 */


use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Menu\PatientMenuEvent;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\Events\PatientDemographics\RenderEvent;
use OpenEMR\Common\Csrf\CsrfUtils;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

function oe_module_priorauth_add_menu_item(MenuEvent $event)
{
    $menu = $event->getMenu();

    $menuItem = new stdClass();
    $menuItem->requirement = 0;
    $menuItem->target = 'mod';
    $menuItem->menu_id = 'mod0';
    $menuItem->label = xlt("Authorizations");
    $menuItem->url = "/interface/modules/custom_modules/oe-module-prior-authorizations/public/reports/list_report.php";
    $menuItem->children = [];
    $menuItem->acl_req = ["patients", "docs"];
    $menuItem->global_req = [];

    foreach ($menu as $item) {
        if ($item->menu_id == 'repimg') {
            foreach ($item->children as $childItem) {
                if ($childItem->label == 'Insurance') {
                    $childItem->children[] = $menuItem;
                    break 2;
                }
            }
        }
    }

    $event->setMenu($menu);

    return $event;
}

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

/**
 * @var EventDispatcherInterface $eventDispatcher
 * @var array                    $module
 * @global                       $eventDispatcher @see ModulesApplication::loadCustomModule
 * @global                       $module          @see ModulesApplication::loadCustomModule
 */

$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, 'oe_module_priorauth_add_menu_item');
$eventDispatcher->addListener(PatientMenuEvent::MENU_UPDATE, 'oe_module_priorauth_patient_menu_item');
