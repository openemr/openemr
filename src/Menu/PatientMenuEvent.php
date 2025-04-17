<?php

/**
 * PatientMenuEvent class. This class enables 3rd party developers to modify the tabs
 * at the top of the patient's medical record, which by default are Dashboard, History, Report,
 * Documents, etc. New items can be added, existing items can be redirected, etc.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

use Symfony\Contracts\EventDispatcher\Event;

class PatientMenuEvent extends Event
{
     /**
     * The UPDATE event occurs once the patient tabbed menu is created, so
      * that modifications can be made to the tabs at the top of the patient menu
     *
     */
    const MENU_UPDATE = 'patient.menu.update';

    /**
     * The RESTRICT event occurs once a menu has been created, updated, and now is applying security ACLs or
     * filters against the menu to decide if the menu should be shown or not.
     */
    const MENU_RESTRICT = 'patient.menu.restrict';



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
