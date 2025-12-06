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

error_reporting(E_ALL);
ini_set('display_errors', 'On');

use OpenEMR\Common\Logging\EventAuditLogger;

$method = $_SERVER['REQUEST_METHOD'];
if (!isset($_GET["pid"])) {
    echo json_encode([]);
    return;
}
$pid = preg_replace("#[^0-9]#", "", $_GET["pid"]);

switch ($method) {
    case 'GET':
        getAllEncounters($pid);
        break;
    default:
        # code...
        break;
}

function getAllEncounters($pid)
{
    $sql = "SELECT * FROM form_encounter WHERE pid = '$pid'";
    $result = sqlStatement($sql);

    $datalist = [];
    foreach ($result as $data) {
        $datalist[] = array(
            "id" => $data["id"],
            "date" => $data["date"],
            "reason" => $data["reason"],
            "facility" => $data["facility"],
            "facility_id" => $data["facility_id"],
            "encounter" => $data["encounter"],
            "onset_date" => $data["onset_date"],
            "billing_note" => $data["billing_note"],
            "pid" => $data["pid"],
            "provider_id" => $data["provider_id"]
        );
    }

    EventAuditLogger::instance()->newEvent(
        "vehr: query form_encounter",
        $pid, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );
    return $datalist;
}

function getLabResults($pid)
{
    $sql = "SELECT * FROM procedure_result WHERE pid = '$pid'";
}
