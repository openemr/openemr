<?php

/**
 * class definitions for objects used in processing fee sheet related data
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * This is an encapsulation of code, code_type and description representing
 * a code
 */


require_once("$srcdir/../custom/code_types.inc.php");

class code_info
{
    function __construct($c, $ct, $desc, $selected = true)
    {
        $this->code = $c;
        $this->code_type = $ct;
        $this->description = $desc;
        $this->selected = $selected;
        // check if the code type is active and allowed to create medical problems from diagnosis elements
        $this->allowed_to_create_problem_from_diagnosis = "FALSE";
        if (check_code_set_filters($ct, array("active","problem"))) {
            $this->allowed_to_create_problem_from_diagnosis = "TRUE";
        }

        // check if the code type is active and allowed to create diagnosis elements from medical problems
        $this->allowed_to_create_diagnosis_from_problem = "FALSE";
        if (check_code_set_filters($ct, array("active","diag"))) {
            $this->allowed_to_create_diagnosis_from_problem = "TRUE";
        }
    }
    public $code;
    public $code_type;
    public $description;
    public $selected;
    public $db_id;
    public $allowed_to_create_problem_from_diagnosis;
    public $allowed_to_create_diagnosis_from_problem;
    public $create_problem;

    public function getKey()
    {
        return $this->code_type . "|" . $this->code;
    }

    public function getCode()
    {
        return $this->code;
    }
    public function getCode_type()
    {
        return $this->code_type;
    }
    public function addArrayParams(&$arr)
    {
        array_push($arr, $this->code_type, $this->code, $this->description);
    }
}

/**
 * This is an extension of code_info which supports the additional information
 * held in a procedure billing entry
 */
class procedure extends code_info
{
    function __construct($c, $ct, $desc, $fee, $justify, $modifiers, $units, $mod_size, $selected = true)
    {
        parent::__construct($c, $ct, $desc, $selected);
        $this->fee = $fee;
        $this->justify = $justify;
        $this->modifiers = $modifiers;
        $this->units = $units;
        $this->mod_size = $mod_size;
    }
    public $fee;
    public $justify;
    public $modifiers;
    public $units;

    //modifier, units, fee, justify

    public function addProcParameters(&$params)
    {
        array_push($params, $this->modifiers, $this->units, $this->fee, $this->justify);
    }
}

/**
 * This is a class which pairs an encounter's ID with the date of the encounter
 */
class encounter_info
{
    function __construct($id, $date)
    {
        $this->id = $id;
        $this->date = $date;
    }

    public $id;
    public $date;

    function getID()
    {
        return $this->id;
    }
}
