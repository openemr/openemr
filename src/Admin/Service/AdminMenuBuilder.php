<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Service;

use OpenEMR\Admin\AdminEvents;
use OpenEMR\Admin\Event\MenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once '../../../interface/globals.php';

/**
 * Class AdminMenuBuilder.
 *
 * @package OpenEMR\Admin
 * @subpackage Service
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 */
class AdminMenuBuilder
{

    /** @var  EventDispatcher */
    public $dispatcher;

    public $event;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->dispatcher = $eventDispatcher;
        $this->event = new MenuEvent();
    }


    public function generateMainMenu()
    {
        $menu = [
            ['name' => 'Appearance', 'link' => ''],
            ['name' => 'Billing', 'link' => ''],
            ['name' => 'CDR', 'link' => ''],
            ['name' => 'Connectors', 'link' => ''],
            ['name' => 'Documents', 'link' => ''],
            ['name' => 'E-Sign', 'link' => ''],
            ['name' => 'Features', 'link' => ''],
            ['name' => 'Locale', 'link' => ''],
            ['name' => 'Logging', 'link' => ''],
            ['name' => 'Miscellaneous', 'link' => ''],
            ['name' => 'Notifications', 'link' => ''],
            ['name' => 'PDF', 'link' => ''],
            ['name' => 'Portal', 'link' => ''],
            ['name' => 'Report', 'link' => ''],
            ['name' => 'Rx', 'link' => ''],
            ['name' => 'Security', 'link' => ''],
        ];
        $event = new MenuEvent($menu);
        error_log('generateMainMenu function');
        /** @var MenuEvent $result */
        $result = $this->dispatcher->dispatch(AdminEvents::BUILD_MAIN_MENU, $event);
        error_log('and now we\'re back');
        $newMenu = $result->getMenu();
        sort($newMenu);
        var_dump($newMenu);
    }
}
