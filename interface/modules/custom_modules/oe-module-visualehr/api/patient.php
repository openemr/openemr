<?php

/**
 * Contains all of the Visual Dashboard global settings and configuration
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2023 Visual EHR <https://visualehr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$_SESSION['site_id'] = 'default';

require_once "../../../../globals.php";


use OpenEMR\Common\Logging\EventAuditLogger;

$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_GET["pid"])) {
    echo json_encode([]);
    return;
}
$pid = preg_replace("#[^0-9]#", "", $_GET["pid"]);

if ($method == 'GET') {
    echo json_encode(getPatientDetails($pid));
}


function getPatientDetails($pid)
{
    $sql = "SELECT fname, mname, lname, dob, city, state, phone_home, email FROM patient_data WHERE pid='$pid'";
    $patient = sqlStatement($sql);

    EventAuditLogger::instance()->newEvent(
        "vehr: query patient data",
        $pid, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );

    return $patient->fields;
}
