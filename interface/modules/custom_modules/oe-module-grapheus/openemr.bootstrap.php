<?php

/**
 * Grapheus by Exetazo: a "Grapheus" tab in every encounter (the AI scribe)
 * and a "Grapheus" item in the main menu (the Assistant).
 *
 * @package   Grapheus
 * @link      https://scribe.exetazohealth.com
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use Exetazo\Grapheus\Compat;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Events\Encounter\EncounterMenuEvent;
use OpenEMR\Menu\MenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @var EventDispatcherInterface $eventDispatcher  provided by the module loader
 * @var ModulesClassLoader       $classLoader      provided by the module loader
 */
$classLoader->registerNamespaceIfNotExists('Exetazo\\Grapheus\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

$eventDispatcher->addListener(EncounterMenuEvent::MENU_RENDER, static function (EncounterMenuEvent $event): EncounterMenuEvent {
    // Only people who can write encounter notes see the scribe.
    if (!AclMain::aclCheckCore('encounters', 'notes', '', 'write') && !AclMain::aclCheckCore('encounters', 'notes_a', '', 'write')) {
        return $event;
    }
    $menu = $event->getMenuData();
    $menu['Grapheus'] = ['displayText' => 'Grapheus', 'formURL' => Compat::moduleUrl('panel.php')];
    $event->setMenuData($menu);
    return $event;
});

$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, static function (MenuEvent $event): MenuEvent {
    $menu = $event->getMenu();
    $item = new stdClass();
    $item->requirement = 0;
    $item->target = 'gra';
    $item->menu_id = 'grapheus0';
    $item->label = xlt('Grapheus');
    $item->url = '/interface/modules/custom_modules/oe-module-grapheus/public/assistant.php';
    $item->children = [];
    $item->acl_req = [];
    $item->global_req = [];
    $menu[] = $item;
    $event->setMenu($menu);
    return $event;
});
