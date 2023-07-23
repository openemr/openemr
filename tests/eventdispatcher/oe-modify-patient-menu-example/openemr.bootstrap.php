<?php

/**
 * Bootstrap custom Patient Menu Module
 *
 * This is the main file for the example module that demonstrates the ability
 * to modify the patient menu tabs on the demographics dashboard using a
 * module and the EventDispatcher.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2020 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Menu\PatientMenuEvent;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @param PatientMenuEvent $menuEvent
 *
 * Load our custom patient menu JSON
 */
function oe_module_custom_patient_menu(PatientMenuEvent $menuEvent)
{
    $menu = file_get_contents(__DIR__ . '/custom_patient_menu.json');
    $menu_parsed = json_decode($menu);
    $menuEvent->setMenu($menu_parsed);
    return $menuEvent;
}

// Listen for the menu update event so we can dynamically add our patient privacy menu item
$eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
$eventDispatcher->addListener(PatientMenuEvent::MENU_UPDATE, 'oe_module_custom_patient_menu');
