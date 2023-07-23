<?php

/**
 * EncounterMenuEvent
 *
 * This event is used to hook into the encounter menu (in forms.php).
 *
 * @package    OpenEMR
 * @subpackage Events
 * @link       http://www.open-emr.org
 * @author     Robert Down <robertdown@live.com>
 * @copyright  Copyright (c) 2021-2023 Robert Down <robertdown@live.com>
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Encounter;

use Symfony\Contracts\EventDispatcher\Event;

class EncounterMenuEvent extends Event
{
    /**
     * This event is fired prior to rendering the encounter menu, and an assoc
     * array of menu data is passed to the event object
     */
    const MENU_RENDER = 'menu.render';

    private $menu;

    /**
     * EncounterMenuEvent constructor takes a multidimensional array
     * of menu items.
     *
     * @param array $menu
     */
    public function __construct(array $menu = [])
    {
        $this->menu = $menu;
    }

    /**
     * @return mixed
     */
    public function getMenuData()
    {
        return $this->menu;
    }

    /**
     * @param mixed $menu
     */
    public function setMenuData(array $menu): void
    {
        $this->menu = $menu;
    }
}
