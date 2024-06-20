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

$data = [
    array("id" => 1, "name" => "medical_problem", "hint" => "", "data" => getMedicalProblems("medical_problem")),
    array("id" => 2, "name" => "medication", "hint" => "", "data" => getMedicalProblems("medication")),
    array("id" => 3, "name" => "vitals", "hint" => "", "data" => getVitals()),
];

function getMedicalProblems($type)
{
    $sql = "SELECT * FROM lists WHERE 'type' = '$type'";
    $result = sqlStatement($sql);

    foreach ($result as $data) {
        $datalist[] = array(
            "id" => $data['id'],
            "date" => $data["date"],
            "type" => $data["type"],
            "subtype" => $data["subtype"],
            "title" => $data["title"],
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
        );
    }

    EventAuditLogger::instance()->newEvent(
        "vehr: query lists",
        null, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );

    return $datalist;
}

function getVitals()
{
    $sql = "SELECT * FROM form_vitals";
    $result = sqlStatement($sql);

    foreach ($result as $data) {
        $datalist[] = array(
            "id" => $data['id'],
            "date" => $data["date"],
            "type" => $data["type"],
            "subtype" => $data["subtype"],
            "title" => $data["title"],
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
        );
    }

    EventAuditLogger::instance()->newEvent(
        "vehr: query form_vitals",
        null, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );

    return $datalist;
}

echo json_encode($data, JSON_PRETTY_PRINT);
