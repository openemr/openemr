<?php

/**
 * This file contains functions that manage custom user
 * settings
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../clinical_rules.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

//set the rule setting for patient (ensure all variables exist)
if ($_POST['plan'] && $_POST['type'] && $_POST['setting'] && $_POST['patient_id']) {
    set_plan_activity_patient($_POST['plan'], $_POST['type'], $_POST['setting'], $_POST['patient_id']);
}
