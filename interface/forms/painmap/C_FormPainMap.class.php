<?php

/**
 * C_FormPainMap.class.php, used to control a clickmap based form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright Copyright Medical Information Integration,LLC <info@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* Include the class we're extending. */
require_once($GLOBALS['fileroot'] . "/interface/clickmap/C_AbstractClickmap.php");

/* included so that we can instantiate FormPainMap in createModel, to model the data contained in this form. */
require_once("FormPainMap.php");

/**
 * @class C_FormPainMap
 *
 * @brief This class extends the C_AbstractClickmap class, to create a form useful for modelling patient pain complaints.
 */
class C_FormPainMap extends C_AbstractClickmap
{
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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @brief Called by C_AbstractClickmap's members to instantiate a Model object on demand.
     *
     * @param form_id
     *  optional id of a form in the EMR, to populate data from.
     */
    public function createModel($form_id = "")
    {
        if ($form_id != "") {
            return new FormPainMap($form_id);
        } else {
            return new FormPainMap();
        }
    }

    /**
     * @brief return the path to the backing image relative to the webroot.
     */
    function getImage()
    {
        return $GLOBALS['webroot'] . "/interface/forms/" . C_FormPainMap::$FORM_CODE . "/templates/painmap.png";
    }

    /**
     * @brief return a n arra containing the options for the dropdown box.
     */
    function getOptionList()
    {
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
    function getOptionsLabel()
    {
        return "Pain Scale";
    }
}
