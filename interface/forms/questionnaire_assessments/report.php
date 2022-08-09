<?php

/**
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS["srcdir"] . "/api.inc");

function questionnaire_assessments_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_questionnaire_assessments", $id);
    if ($data) {
    }
}
