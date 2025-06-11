<?php

/** @package    verysimple::Phreeze */

/**
 * import supporting libraries
 */
require_once("FieldMap.php");
require_once("KeyMap.php");

/**
 * IDaoMap2 is an interface for a mapped object that can be persisted by Phreeze
 * Version 2 includes AddMap and SetFetchingStrategy
 *
 * @package verysimple::Phreeze
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.0
 */
interface IDaoMap2
{
    /**
     * Add a new FieldMap
     *
     * @param string $property
     * @param FieldMap $map
     */
    static function AddMap($property, FieldMap $map);

    /**
     * Change the fetching strategy for a KeyMap
     *
     * @param unknown $property
     * @param int $loadType
     *          (KM_LOAD_LAZY | KM_LOAD_INNER | KM_LOAD_EAGER)
     */
    static function SetFetchingStrategy($property, $loadType);

    /**
     * Returns a singleton array of FieldMaps for a Phreezable object
     *
     * @access public
     * @return FieldMap[]
     */
    static function GetFieldMaps();

    /**
     * Returns a singleton array of KeyMaps for the Phreezable object
     *
     * @access public
     * @return KeyMap[]
     */
    static function GetKeyMaps();
}
