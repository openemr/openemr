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
 * @file C_FormAnnotate.class.php
 *
 * @brief This file contains the C_FormAnnotate class, used to control a clickmap bassed form.
 * @package OpenEMR
 * @author  Medical Information Integration,LLC <info@mi-squared.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com> 
 * @author  Terry Hill <terry@lillysystems.com>
 * @link    http://www.open-emr.org
 */

/* Include the class we're extending. */
require_once ($GLOBALS['fileroot'] . "/interface/forms/annotate_diagram/mapdiagram/C_AbstractAnnotate.php");

/* included so that we can instantiate FormAnnotate in createModel, to model the data contained in this form. */
require_once ("FormAnnotate.php");


/**
 * @class C_FormAnnotate
 *
 * @brief This class extends the C_AbstractAnnotate class, to create a form useful for modelling patient feet complaints.
 */
class C_FormAnnotate extends C_AbstractAnnotate {
    /**
     * The title of the form, used when calling addform().
     *
     * @var FORM_TITLE
     */
    static $FORM_TITLE = "Generic Graphic Diagrams";
    /**
     * The 'code' of the form, also used when calling addform().
     *
     * @var FORM_CODE
     */
    static $FORM_CODE = "annotate_diagram";

    /* initializer, just calls parent's initializer. */
    public function C_FormAnnotate() {
    	parent::C_AbstractAnnotate();
    }

    /**
     * @brief Called by C_AbstractAnnotate's members to instantiate a Model object on demand.
     *
     * @param form_id
     *  optional id of a form in the EMR, to populate data from.
     */
    public function createModel($form_id = "") {
        if ( $form_id != "" ) {
            return new FormAnnotate($form_id);
        } else {
            return new FormAnnotate();
        }
    }

    /**
     * @brief return the path to the backing image relative to the webroot.
     */

	function getImage() {
		//really now just a default image
		return ($GLOBALS['webroot'] . "/interface/forms/" . C_FormAnnotate::$FORM_CODE ."/diagram/default.png");
	}

    /**
     * @brief return a n array containing the options for the dropdown box.
     */
    function getOptionList() {
        return array(  "0" => '',
					   "1" => "None",
                       "2" => "Severity 1",
                       "3" => "Severity 2",
                       "4" => "Severity 3",
                       "5" => "Severity 4",
                       "6" => "Moderate",
                       "7" => "Severity 6",
                       "8" => "Severity 7",
                       "9" => "Severity 8",
                       "10" => "Severity 9",
                       "11" => "Worst Possible",
					   "apq2" => "Sharp",
					   "apq3" => "Dull",
					   "apq4" => "Stabbing",
					   "apq5" => "Burning",
					   "apq6" => "Constant",
					   "apq7" => "Intermettent",
					   "bpf2" => "Laceration",
					   "bpf3" => "Hemotoma",
					   "bpf4" => "Tenderness",
					   "bpf5" => "Ecchymosis",
					   "bpf6" => "Deformity",
					   "bpf7" => "Swelling",
					   "bpf8" => "Contusion",
					   "bpf9" => "Abrasion",
					   "bpf10" => "Muscle spasm",
					   "e1" => "Corneal Abrasion",
					   "e2" => "Corneal Ulceration",
                       "e3" => "Foreign Body",
                       "e4" => "Punctate Lesions",
					   "e5" => "Fluorescein Uptate",
                       "e6" => "Subconjuntival Hemporrhage",
					   "f1" => "Redness",
					   "f2" => "Normal Overall",
                       "f3" => "Callous",
                       "f4" => "Pre Ulcer",
                       "f5" => "Ulcer",
                       "f6" => "Maceration",
                       "f7" => "Dryness",
                       "f8" => "Tinea",
                       "f9" => "Can feel the 5.07 filament",
                       "f10" => "Can't feel the 5.07 filament",
                       "f11" => "Odor");
    }

    /**
     * @brief return a label for the dropdown boxes on the form, as a string.
     */
    function getOptionsLabel() {
        return "Observations List";
    }
}
?>