<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event object related to admin menu actions.
 *
 * @package OpenEMR\Admin
 * @subpackage Event
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 */
class MenuEvent extends Event
{

    /** @var array The menu list */
    private $menu;

    public function __construct($menu = [])
    {
        $this->menu = $menu;
    }

    /**
     * Get a list of menu items
     *
     * @return array Array of menu items
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Add a menu item.
     *
     * Used by listeners to add their menu item
     *
     * @param string $name Text displayed to user. Must already be translated
     * @param string $link Href to the view
     */
    public function addMenuItem($name, $link)
    {
        $item = ['name' => $name, 'link' => $link];
        $this->menu[] = $item;
    }
}
