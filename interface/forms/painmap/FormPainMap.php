<?php

/**
 * This file contains the FormPainMap class, used to model the data contents of a clickmap based form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright Copyright Medical Information Integration,LLC <info@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* include the class we are extending. */
require_once($GLOBALS['fileroot'] . "/interface/clickmap/AbstractClickmapModel.php");

/**
 * @class FormPainMap
 *
 * @brief This class extends the AbstractClickmapModel class, to create a class for modelling the data in a pain form.
 */
class FormPainMap extends AbstractClickmapModel
{
    /**
     * The database table to place form data in/read form data from.
     *
     * @var TABLE_NAME
     */

    static $TABLE_NAME = "form_painmap";

    /* Initializer. just calls parent's initializer. */
    function __construct($id = "")
    {
        parent::__construct(FormPainMap::$TABLE_NAME, $id);
    }

    /**
     * @brief Return the Title of the form, Useful when calling addform().
     */
    public function getTitle()
    {
        return C_FormPainMap::$FORM_TITLE;
    }

    /**
     * @brief Return the 'Code' of the form. Again, used when calling addform().
     */
    public function getCode()
    {
        return C_FormPainMap::$FORM_CODE;
    }
}
