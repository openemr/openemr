<?php

/**
 * Drug Screen Complete Update Database
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$drugval = '0';
if ($_POST['testcomplete'] == 'true') {
    $drugval = '1';
}

$tracker_id = $_POST['trackerid'];
if ($tracker_id != 0) {
       sqlStatement("UPDATE patient_tracker SET " .
           "drug_screen_completed = ? " .
           "WHERE id =? ", array($drugval,$tracker_id));
}
