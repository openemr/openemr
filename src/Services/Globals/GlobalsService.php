<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Globals;

/**
 * Service for manipulating data structure of "globals" which affects the Admin > Globals screen
 *
 * @package OpenEMR\Services
 * @subpackage Globals
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class GlobalsService
{
    /**
     * @var array
     *
     * The entire globals structure
     */
    private $globalsMetadata = [];

    /**
     * @var array
     *
     * User-specific globals
     */
    private $userSpecificGlobals = [];

    /**
     * @var array
     *
     *  User specific tabs
     */
    private $userSpecificTabs = [];

    /**
     * GlobalsInitEvent constructor.
     * @param $userService
     * @param $GLOBALS_METADATA
     * @param $USER_SPECIFIC_GLOBALS
     * @param $USER_SPECIFIC_TABS
     */
    public function __construct($GLOBALS_METADATA, $USER_SPECIFIC_GLOBALS, $USER_SPECIFIC_TABS)
    {
        $this->globalsMetadata = $GLOBALS_METADATA;
        $this->userSpecificGlobals = $USER_SPECIFIC_GLOBALS;
        $this->userSpecificTabs = $USER_SPECIFIC_TABS;
    }

    /**
     * Save the globals data structure, does not save globals data values
     */
    public function save()
    {
        global $GLOBALS_METADATA, $USER_SPECIFIC_GLOBALS, $USER_SPECIFIC_TABS;
        $GLOBALS_METADATA = $this->globalsMetadata;
        $USER_SPECIFIC_GLOBALS = $this->userSpecificGlobals;
        $USER_SPECIFIC_TABS = $this->userSpecificTabs;
    }

    /**
     * @param $section Section name
     * @param bool $beforeSection Section name we want to insert this section before (false for at the end)
     * @throws \Exception
     *
     * Create a section, or TAB in the Admin > Globals screen
     */
    public function createSection($section, $beforeSection = false)
    {
        if (!isset($this->globalsMetadata[$section])) {
            if (
                $beforeSection !== false &&
                isset($this->globalsMetadata[$beforeSection])
            ) {
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

    /**
     * @param $section Section name
     * @param $key Global metadata key, must be unique in structure
     * @param GlobalSetting $global
     *
     * Append a global setting to the end of a section
     */
    public function appendToSection($section, $key, GlobalSetting $global)
    {
        $this->globalsMetadata[$section][$key] = $global->format();
        if ($global->isUserSetting()) {
            $this->userSpecificGlobals[] = $key;
        }
    }

    /**
     * @return array
     *
     * Get the globals metadata structure
     */
    public function getGlobalsMetadata()
    {
        return $this->globalsMetadata;
    }

    /**
     * @return array
     */
    public function getUserSpecificGlobals()
    {
        return $this->userSpecificGlobals;
    }

    /**
     * @return array
     */
    public function getUserSpecificTabs()
    {
        return $this->userSpecificTabs;
    }
}
