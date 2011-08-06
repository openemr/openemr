<?php
/**
 * Copyright Medical Information Integration,LLC info@mi-squared.com
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @file C_FormPainMap.class.php
 *
 * @brief This file contains the C_FormPainMap class, used to control a clickmap bassed form.
 */

/* Include the class we're extending. */
require_once ($GLOBALS['fileroot'] . "/interface/clickmap/C_AbstractClickmap.php");

/* included so that we can instantiate FormPainMap in createModel, to model the data contained in this form. */
require_once ("FormPainMap.php");

/**
 * @class C_FormPainMap
 *
 * @brief This class extends the C_AbstractClickmap class, to create a form useful for modelling patient pain complaints.
 */
class C_FormPainMap extends C_AbstractClickmap {
    /**
     * The title of the form, used when calling addform().
     *
     * @var FORM_TITLE
     */
    static $FORM_TITLE = "Graphical Pain Map";
    /**
     * The 'code' of the form, also used when calling addform().
     *
     * @var FORM_CODE
     */
    static $FORM_CODE = "painmap";

    /* initializer, just calls parent's initializer. */
    public function C_FormPainMap() {
    	parent::C_AbstractClickmap();
    }

    /**
     * @brief Called by C_AbstractClickmap's members to instantiate a Model object on demand.
     *
     * @param form_id
     *  optional id of a form in the EMR, to populate data from.
     */
    public function createModel($form_id = "") {
        if ( $form_id != "" ) {
            return new FormPainMap($form_id);
        } else {
            return new FormPainMap();
        }
    }

    /**
     * @brief return the path to the backing image relative to the webroot.
     */
    function getImage() {
        return $GLOBALS['webroot'] . "/interface/forms/" . C_FormPainMap::$FORM_CODE ."/templates/painmap.png";
    }

    /**
     * @brief return a n arra containing the options for the dropdown box.
     */
    function getOptionList() {
        return array(  "0" => "None",
                       "1" => "Level 1",
                       "2" => "Level 2",
                       "3" => "Level 3",
                       "4" => "Level 4",
                       "5" => "Moderate",
                       "6" => "Level 6",
                       "7" => "Level 7",
                       "8" => "Level 8",
                       "9" => "Level 9",
                       "10" => "Worst Possible" );
    }

    /**
     * @brief return a label for the dropdown boxes on the form, as a string.
     */
    function getOptionsLabel() {
        return "Pain Scale";
    }
}
?>
