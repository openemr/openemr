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
    echo json_encode(getActiveIssues($pid));
}

function getColor()
{
    $colors = ["#eb5ea8", "#9f4ed3", "#99b1fc", "#7e83d0", "#fb4e4e", "#32a852", "#fb4e4e"];
    $randColorIndex = random_int(0, count($colors) - 1);
    $generatedColor = $colors[$randColorIndex];
    return $generatedColor;
}

function getActiveIssues($pid)
{
    $sql = "SELECT * FROM lists WHERE pid = '$pid'"; //activity = 1 is for active issues/codes populate this in the ui
    $result = sqlStatement($sql);
    EventAuditLogger::instance()->newEvent(
        "vehr: query active-issues",
        $pid, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );
    $datalist = [];

    foreach ($result as $data) {
        $datalist[] = array(
            "id" => $data['id'],
            "date" => $data["date"],
            "type" => $data["type"],
            "subtype" => $data["subtype"],
            "name" => $data["title"],
            "begdate" => $data["begdate"],
            "enddate" => $data["enddate"],
            "returndate" => $data["returndate"],
            "occurrence" => $data["occurrence"],
            "classification" => $data["classification"],
            "referredby" => $data["referredby"],
            "extrainfo" => $data["extrainfo"],
            "diagnosis" => $data["diagnosis"],
            "activity" => $data["activity"],
            "comments" => $data["comments"],
            "pid" => $data["pid"],
            "user" => $data["user"],
            "groupname" => $data["groupname"],
            "outcome" => $data["outcome"],
            "destination" => $data["destination"],
            "reinjury_id" => $data["reinjury_id"],
            "injury_part" => $data["injury_part"],
            "injury_type" => $data["injury_type"],
            "injury_grade" => $data["injury_grade"],
            "reaction" => $data["reaction"],
            "verification" => $data["verification"],
            "external_allergyid" => $data["external_allergyid"],
            "erx_source" => $data["erx_source"],
            "erx_uploaded" => $data["erx_uploaded"],
            "modifydate" => $data["modifydate"],
            "severity_al" => $data["severity_al"],
            "external_id" => $data["external_id"],
            "list_option_id" => $data["list_option_id"],
            "udi" => $data["udi"],
            "udi_data" => $data["udi_data"],
            "color" => getColor()
        );
    }


    return $datalist;
}
