<?php

/**
 * MainMenuRole class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2019 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

use Symfony\Component\EventDispatcher\Event;

class MenuEvent extends Event
{

     /**
     * The UPDATE event occurs once a menu has been created and had it's update
     * menu map function called.  This allows other listeners to apply additional updates
     * to the menu.
     *
     * This event allows you to change the controller that will handle the
     * request.
     *
     */
    const MENU_UPDATE = 'menu.update';

    /**
     * The RESTRICT event occurs once a menu has been created, updated, and now is applying security ACLs or
     * filters against the menu to decide if the menu should be shown or not.
     */
    const MENU_RESTRICT = 'menu.restrict';



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

    public function setMenu(array $menu)
    {
        $this->menu = $menu;
    }
}
