<?php

/**
 * CodeInfo - encapsulation of code, code_type and description representing a code
 *
 * @package   OpenEMR
 * @link      https://open-emr.org/
 * @link      https://opencoreemr.com/
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR
 * @copyright Copyright (c) 2026 OpenCoreEmr Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Forms\FeeSheet\Review;

class CodeInfo
{
    public $db_id;
    public $allowed_to_create_problem_from_diagnosis;
    public $allowed_to_create_diagnosis_from_problem;
    public $create_problem;

    public function __construct(
        public $code,
        public $code_type,
        public $description,
        public $selected = true
    ) {
        // check if the code type is active and allowed to create medical problems from diagnosis elements
        $this->allowed_to_create_problem_from_diagnosis = "FALSE";
        if (function_exists('check_code_set_filters') && check_code_set_filters($this->code_type, ["active", "problem"])) {
            $this->allowed_to_create_problem_from_diagnosis = "TRUE";
        }

        // check if the code type is active and allowed to create diagnosis elements from medical problems
        $this->allowed_to_create_diagnosis_from_problem = "FALSE";
        if (function_exists('check_code_set_filters') && check_code_set_filters($this->code_type, ["active", "diag"])) {
            $this->allowed_to_create_diagnosis_from_problem = "TRUE";
        }
    }

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
