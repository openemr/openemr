<?php
/**
 * Copyright Medical Information Integration,LLC info@mi-squared.com
 * 
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * Rewrite and modifications by sjpadgett@gmail.com Padgetts Consulting 2016.
 *
 * @file FormAnnotate.php
 *
 * @brief This file ontains the FormAnnotate class, used to model the data contents of a clickmap based form.
 * @package OpenEMR
 * @author  Medical Information Integration,LLC <info@mi-squared.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com> 
 * @author  Terry Hill <terry@lillysystems.com>
 * @link    http://www.open-emr.org
 */

/* include the class we are extending. */
require_once ($GLOBALS['fileroot'] . "/interface/forms/annotate_diagram/mapdiagram/AbstractAnnotateModel.php");

/**
 * @class FormAnnotate
 *
 * @brief This class extends the AbstractAnnotateModel class, to create a class for modelling the data in a pain form.
 */
class FormAnnotate extends AbstractAnnotateModel {

    /**
     * The database table to place form data in/read form data from.
     *
     * @var TABLE_NAME
     */
    static $TABLE_NAME = "form_annotate_diagram";

    /* Initializer. just alles parent's initializer. */
    function FormAnnotate($id="") {
    	parent::AbstractAnnotateModel(FormAnnotate::$TABLE_NAME, $id);
    }

    /**
     * @brief Return the Title of the form, Useful when calling addform().
     */
    public function getTitle() {
        return C_FormAnnotate::$FORM_TITLE;
    }

    /**
     * @brief Return the 'Code' of the form. Again, used when calling addform().
     */
    public function getCode() {
        return C_FormAnnotate::$FORM_CODE;
    }
}
?>