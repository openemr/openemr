<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Globals;

use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for creating custom global settings
 *
 * @package OpenEMR\Events
 * @subpackage Globals
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class GlobalsInitializedEvent extends Event
{
    /**
     * The global.init event occurs after globals have been set up in globals.inc.php
     */
    const EVENT_HANDLE = 'globals.initialized';

    /**
     * @var null|UserService
     *
     */
    private $userService = null;

    /**
     * @var array
     *
     *
     */
    private $globalsMetadata = [];

    /**
     * @var array
     *
     *
     */
    private $userSpecificGlobals = [];

    /**
     * @var array
     *
     *
     */
    private $userSpecificTabs = [];

    /**
     * GlobalsInitEvent constructor.
     * @param $userService
     * @param $GLOBALS_METADATA
     * @param $USER_SPECIFIC_GLOBALS
     * @param $USER_SPECIFIC_TABS
     */
    public function __construct($userService ,$GLOBALS_METADATA,$USER_SPECIFIC_GLOBALS,$USER_SPECIFIC_TABS)
    {
        $this->userService = $userService;
        $this->globalsMetadata = $GLOBALS_METADATA;
        $this->userSpecificGlobals = $USER_SPECIFIC_GLOBALS;
        $this->userSpecificTabs = $USER_SPECIFIC_TABS;
    }

    public function getUserService()
    {
        return $this->userService;
    }

    public function save()
    {
        global $GLOBALS_METADATA, $USER_SPECIFIC_GLOBALS, $USER_SPECIFIC_TABS;
        $GLOBALS_METADATA = $this->globalsMetadata;
        $USER_SPECIFIC_GLOBALS = $this->userSpecificGlobals;
        $USER_SPECIFIC_TABS = $this->userSpecificTabs;
    }

    public function createSection($section, $beforeSection = false)
    {
        if (!isset($this->globalsMetadata[$section])) {
            if ($beforeSection !== false &&
                isset($this->globalsMetadata[$beforeSection])) {

                $beforeSectionIndex = array_search($beforeSection, array_keys($this->globalsMetadata));
                $this->globalsMetadata = array_slice($this->globalsMetadata, 0, $beforeSectionIndex, true) +
                    array($section => []) +
                    array_slice($this->globalsMetadata, $beforeSectionIndex, count($this->globalsMetadata) - 1, true) ;

            } else {
                $this->globalsMetadata[$section] = [];
            }
        } else {
            throw new \Exception("Section already exists and cannot be created");
        }
    }

    public function appendToSection($section, $key, GlobalSetting $global)
    {
        $this->globalsMetadata[$section][$key] = $global->format();
        if ( $global->isUserSetting() ) {
            $this->userSpecificGlobals[]= $key;
        }
    }

    public function getGlobalsMetadata()
    {
        return $this->globalsMetadata;
    }
}
