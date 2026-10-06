<?php

/**
 * Grapheus by Exetazo: adds a "Grapheus" tab to every encounter.
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

/**
 * @var Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher  (both versions)
 * @var ModulesClassLoader|null $classLoader  (provided by OpenEMR 8; created here on 7.0.x)
 */
require_once __DIR__ . '/src/Compat.php';
if (!isset($classLoader) || !($classLoader instanceof ModulesClassLoader)) {
    $classLoader = new ModulesClassLoader(Compat::fileroot());
}
$classLoader->registerNamespaceIfNotExists('Exetazo\\Grapheus\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

$eventDispatcher->addListener(EncounterMenuEvent::MENU_RENDER, function (EncounterMenuEvent $event) {
    // Only people who can write encounter notes see it.
    if (!AclMain::aclCheckCore('encounters', 'notes', '', 'write') && !AclMain::aclCheckCore('encounters', 'notes_a', '', 'write')) {
        return $event;
    }
    $menu = $event->getMenuData();
    $menu['Grapheus'] = [
        'displayText' => 'Grapheus',
        'formURL' => Compat::moduleUrl('panel.php'),
    ];
    $event->setMenuData($menu);
    return $event;
});

// A "Grapheus" item in the main menu bar: the Assistant (set up, show me how, schedule).
$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, function (MenuEvent $event) {
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

