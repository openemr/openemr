<?php

/**
 * CodeInfo - encapsulation of code, code_type and description representing a code
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR <https://www.open-emr.org/wiki/index.php/OEMR_wiki_page>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Forms\FeeSheet\Review;

class CodeInfo
{
    public ?string $db_id = null;
    public string $allowed_to_create_problem_from_diagnosis;
    public string $allowed_to_create_diagnosis_from_problem;
    public ?bool $create_problem = null;

    public function __construct(
        public string $code,
        public string $code_type,
        public string $description,
        public bool $selected = true
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

    public function getKey(): string
    {
        return $this->code_type . "|" . $this->code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCode_type(): string
    {
        return $this->code_type;
    }

    /**
     * @param array<mixed> $arr
     */
    public function addArrayParams(array &$arr): void
    {
        array_push($arr, $this->code_type, $this->code, $this->description);
    }
}
