<?php

/**
 * FQHC module bootstrap class.
 *
 * Wires the module's event subscribers. For Step 1 this only adds a top-level
 * "FQHC" navigation item (additively, via the menu event) that opens the host
 * page. Nothing here modifies a certified code path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\Fqhc;

use OpenEMR\Menu\MenuEvent;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    public const MODULE_INSTALLATION_PATH = '/interface/modules/custom_modules/oe-module-fqhc';
    private const MENU_ID = 'fqhc0';

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function subscribeToEvents(): void
    {
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, $this->addMenuItem(...));
    }

    /**
     * Append a top-level "FQHC" menu item that opens the module host page.
     * Guarded so repeated dispatches never duplicate the entry.
     */
    public function addMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        foreach ($menu as $item) {
            if (($item->menu_id ?? null) === self::MENU_ID) {
                return $event;
            }
        }

        $fqhc = new stdClass();
        $fqhc->requirement = 0;
        $fqhc->target = 'fqhc';
        $fqhc->menu_id = self::MENU_ID;
        $fqhc->label = xlt('FQHC');
        $fqhc->url = self::MODULE_INSTALLATION_PATH . '/public/index.php';
        $fqhc->children = [];
        $fqhc->acl_req = ['patients', 'demo'];
        $fqhc->global_req = [];

        $menu[] = $fqhc;
        $event->setMenu($menu);

        return $event;
    }
}
