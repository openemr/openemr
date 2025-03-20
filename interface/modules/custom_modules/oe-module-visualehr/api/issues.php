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

switch ($method) {
    case "GET":
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($_GET['type']) && !empty($_GET['type'])) {
            getIssuesByPatientId($path[4], $db);
        } else {
            fetchIssues($pid);
        }
        break;
}

function icd10($icd10s)
{
    $icd_10 = explode(";", $icd10s);
    $datalist = [];
    $datalistIds = [];
    $icd_s = [];
    foreach ($icd_10 as $icd) {
        if (!empty($icd)) {
            $data = end(explode(":", $icd));
            $icd_s[] = $data;
        }
    }
    $array_data = "'" . implode("','", $icd_s) . "'";
    $sql = "SELECT * FROM icd10_dx_order_code WHERE formatted_dx_code IN ($array_data)";
    $result = sqlStatement($sql);
    EventAuditLogger::instance()->newEvent(
        "vehr: query icd10_dx_order_code",
        null, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );

    foreach ($result as $data) {
        $datalist[] = $data['short_desc'];
        $datalistIds[] = "ICD:" . $data['formatted_dx_code'];
    }
    $data = array(implode(', ', $datalist), implode(', ', $datalistIds));
    return $data;
}

function getIssuesByPatientId($patientId, $db)
{
    $sql = "SELECT * FROM lists";
    $sql .= " WHERE pid = $patientId";
    $result = sqlStatement($sql);
    EventAuditLogger::instance()->newEvent(
        "vehr: query lists",
        $patientId, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );

    foreach ($result as $data) {
        $datalist[] = array(
            "id" => $data['id'],
            "date" => serialize_date($data["date"]),
            "type" => $data["type"],
            "subtype" => $data["subtype"],
            "title" => $data["title"],
            "begdate" => serialize_date($data["begdate"]),
            "enddate" => serialize_date($data["enddate"]),
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

    //echo json_encode();
}

function fetchIssues($pid)
{
    $datalist = [];
    $datalist[] = array(
        "timeline_id" => 1,
        "data" => getAllIssues('medical_problem', $pid)
    );

    $datalist[] = array(
        "timeline_id" => 2,
        "data" => getAllIssues('medication', $pid)
    );

    $datalist[] = array(
        "timeline_id" => 3,
        "data" => getAllIssues('allergy', $pid)
    );

    $datalist[] = array(
        "timeline_id" => 4,
        "data" => getAllIssues('surgery', $pid)
    );

    $datalist[] = array(
        "timeline_id" => 5,
        "data" => getAllIssues('dental', $pid)
    );

    $datalist[] = array(
        "timeline_id" => 6,
        "data" => getAllIssues('medical_device', $pid)
    );

    $datalist[] = array(
        "timeline_id" => 7,
        "data" => vitals($pid)
    );

    $datalist[] = array(
        "timeline_id" => 8,
        "data" => getAllLabs($pid)
    );

    $datalist[] = array(
        "timeline_id" => 9,
        "data" => soap_note($pid)
    );

    $data = array(
        "issues" => $datalist,
        "groupDate" => groupDates($pid),
        "encounters" => getAllEncounters($pid),

    );
    echo json_encode($data);
}

function getAllLabs($pid)
{
    $datalist = [];
    $sql = "SELECT * FROM procedure_order WHERE patient_id = '$pid' GROUP BY `date_ordered`";
    $result = sqlStatement($sql);
    EventAuditLogger::instance()->newEvent(
        "vehr: query procedure_order",
        $pid, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );

    foreach ($result as $data) {
        $datalist[] = array("created_at" => $data['date'], "data" =>  getLabs($data["encounter_id"], $pid));
    }
    return $datalist;
}

function vitals($pid)
{
    $datalist = [];
    $sql = "SELECT * FROM form_encounter WHERE pid = '$pid' GROUP BY `date`";
    $result = sqlStatement($sql);
    EventAuditLogger::instance()->newEvent(
        "vehr: query vitals",
        $pid, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );


    foreach ($result as $data) {
        $datalist[] = array("created_at" => $data['date'], "data" => getEncounterVitals(getEncounterForm($data["encounter"], $pid, "vitals"), $pid));
    }
    return $datalist;
}

function soap_note($pid)
{
    $datalist = [];
    $sql = "SELECT * FROM form_encounter WHERE pid = '$pid' GROUP BY `date`";
    $result = sqlStatement($sql);
    EventAuditLogger::instance()->newEvent(
        "vehr: query soap note",
        $pid, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );

    foreach ($result as $data) {
        $datalist[] = array("created_at" => $data['date'], "data" => getEncounterSoap(getEncounterForm($data["encounter"], $pid, "soap"), $pid));
    }
    return $datalist;
}

function groupDates($pid)
{
    $datalist = encounterGroupDates($pid);

    $sql = "SELECT begdate,enddate FROM lists WHERE pid = $pid AND begdate IS NOT NULL ORDER BY begdate";
    $result = sqlStatement($sql);
    EventAuditLogger::instance()->newEvent(
        "vehr: query lists",
        $pid, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'visual-ehr',
        'dashboard'
    );

    foreach ($result as $data) {
        $start_date = serialize_date($data["begdate"]);
        $end_date = serialize_date($data["enddate"]);
        if (!in_array($start_date, $datalist)) {
            $datalist[] = serialize_date($data["begdate"]);
        }
        if (!in_array($end_date, $datalist) && ($end_date != null || !empty($end_date))) {
            $datalist[] = serialize_date($data["enddate"]);
        }
    }
    usort($datalist, "cmp");
    return $datalist;
}

function encounterGroupDates($pid)
{
    $sql = "SELECT DATE_FORMAT(date, '%Y-%m-%d') as date FROM form_encounter WHERE pid = $pid AND date IS NOT NULL ORDER BY date";
    $result = sqlStatement($sql);

    $datalist = [];
    foreach ($result as $data) {
        $start_date = serialize_date($data["date"]);
        if (!in_array($start_date, $datalist)) {
            $datalist[] = serialize_date($data["date"]);
        }
    }

    return $datalist;
}

function cmp($a, $b)
{
    $a = date('Y-m-d', strtotime($a));
    $b = date('Y-m-d', strtotime($b));

    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}

function findDateRange($sDate, $eDate, $cDate)
{
    $currentDate = date('Y-m-d', strtotime($cDate));
    $startDate = date('Y-m-d', strtotime($sDate));
    $endDate = date('Y-m-d', strtotime($eDate));
    return ($currentDate >= $startDate) && ($currentDate <= $endDate);
}

function getAllIssues($subtype, $pid)
{
    $datalist = [];
    $prescriptions = [];
    $tmp_prescriptions = [];

    $sql = "SELECT * FROM lists WHERE `type` = '$subtype' AND pid = '$pid' AND begdate IS NOT NULL";
    $result = sqlStatement($sql);

    foreach ($result as $data) {
        $prescribe = array(
            "id" => $data['id'],
            "date" => serialize_date($data["date"]),
            "type" => $data["type"],
            "subtype" => $data["subtype"],
            "name" => $data["title"],
            "startDate" => serialize_date($data["begdate"]),
            "endDate" => serialize_date($data["enddate"]),
            "returndate" => $data["returndate"],
            "occurrence" => $data["occurrence"],
            "classification" => $data["classification"],
            "referredby" => $data["referredby"],
            "extrainfo" => $data["extrainfo"],
            "diagnosis_show_as" => @icd10($data["diagnosis"])[0],
            "diagnosis" => @icd10($data["diagnosis"])[1],
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
            "color" => !empty($data["color"])  ? $data["color"] : "#B2BEB5"
        );
        $prescriptions[] = $prescribe;
        $tmp_chronic_found = false;

        if (count($tmp_prescriptions)) {
            $lasttemp = end($tmp_prescriptions);
            if ($lasttemp["endDate"] != null && serialize_date($data["enddate"]) != null) {
                foreach ($tmp_prescriptions as $tmp) {
                    $prescriptions[] = $tmp;
                }
            } else {
                if ($lasttemp["endDate"] == null && serialize_date($data["enddate"]) != null) {
                    foreach ($tmp_prescriptions as $tmp) {
                        $prescriptions[] = $tmp;
                    }
                } else if ($lasttemp["endDate"] != null && serialize_date($data["enddate"]) == null) {
                    array_pop($prescriptions);
                    foreach ($tmp_prescriptions as $tmp) {
                        $prescriptions[] = $tmp;
                    }
                    $prescriptions[] = $prescribe;
                } else {
                    if ($lasttemp["endDate"] == null) {
                        $tmp_chronic_found = true;
                        $datalist[] = array("chronic" => true, "prescriptions" => $tmp_prescriptions);
                    }
                }
            }
            $tmp_prescriptions = [];
        }

        $isChronic = $prescribe["endDate"] == null;
        if (count($datalist) == 0) {
            $datalist[] = array("chronic" => $isChronic, "prescriptions" => $prescriptions);
            $prescriptions = [];
        } else {
            $fallsWithinRange = fallsWithinRange($datalist, serialize_date($data["begdate"]));

            if (isLastDiagnosisChronic($datalist)) {
                # print("tmp_chronic_found=".$tmp_chronic_found);
                $datalist[] = array("chronic" => true, "prescriptions" => $prescriptions);
                $prescriptions = [];
            } else {
                if (!$fallsWithinRange) {
                    #check if there is last tmp chronic data found
                    if (!$tmp_chronic_found) {
                        $lastdatalist = end($datalist);
                        $lastupdatedprescriptions = merge_array_data(array_merge($lastdatalist["prescriptions"], $prescriptions));
                        array_pop($datalist);
                    } else {
                        $lastupdatedprescriptions = $prescriptions;
                    }

                    $datalist[] = array("chronic" => $isChronic, "prescriptions" => $lastupdatedprescriptions);
                    if ($isChronic) {
                        $prescriptions = [];
                    }
                } else {
                    array_pop($prescriptions);
                    $tmp_prescriptions[] = $prescribe;
                }
            }
        }
    }
    EventAuditLogger::instance()->newEvent(
        "vehr: query all issues",
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

function merge_array_data($datalist)
{
    $data = [];
    foreach ($datalist as $d) {
        $filteredData = filter($data, $d["id"]);

        if (!$filteredData) {
            $data[] = $d;
        }
    }
    return $data;
}

function filter($item, $id)
{
    $datafound = false;
    foreach ($item as $tm) {
        if ($tm["id"] == $id) {
            $datafound = true;
        }
    }
    return $datafound;
}

function fallsWithinRange($datalist, $startDate)
{
    if (count($datalist)) {
        $lastdata = end($datalist);
        $lastdatalist = end($lastdata["prescriptions"]);
        return findDateRange($lastdatalist["startDate"], $lastdatalist["endDate"], $startDate);
    }
    return false;
}

function isLastDiagnosisChronic($datalist)
{
    $lastdata = end($datalist);
    $lastdatalist = end($lastdata["prescriptions"]);
    return $lastdata["chronic"];
}

function getAllEncounters($pid)
{
    $sql = "SELECT * FROM form_encounter WHERE pid = '$pid' GROUP BY `date`";
    $result = sqlStatement($sql);
    $datalist = [];

    foreach ($result as $data) {
        $datalist[] = array(
            "id" => $data["id"],
            "date" => serialize_date($data["date"]),
            "reason" => $data["reason"],
            "facility" => $data["facility"],
            "facility_id" => $data["facility_id"],
            "encounter" => $data["encounter"],
            "encounters" => getEncounters(serialize_date($data["date"]), $pid),
            "onset_date" => $data["onset_date"],
            "billing_note" => $data["billing_note"],
            "pid" => $data["pid"],
            "provider_id" => $data["provider_id"]
        );
    }

    EventAuditLogger::instance()->newEvent(
        "vehr: query all encounters",
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

function getEncounterForm($eid, $pid, $form_dir)
{
    $sql = "SELECT form_id FROM forms WHERE encounter = $eid AND formdir = '$form_dir'";
    $result = array();
    foreach (sqlStatement($sql) as $data) {
        $result[] = $data;
    }
    return $result  ? $result[0]["form_id"] : 2;
}

//Retrieve all encounters matching a specific date.
function getEncounters($date, $pid)
{
    $sql = "SELECT * FROM form_encounter WHERE date = '$date'";
    $result = sqlStatement($sql);
    $datalist = [];

    foreach ($result as $data) {
        $datalist[] = array(
            "id" => $data["id"],
            "date" => serialize_date($data["date"]),
            "reason" => $data["reason"],
            "facility" => $data["facility"],
            "facility_id" => $data["facility_id"],
            "encounter" => $data["encounter"],
            "vitals" => getEncounterVitals(getEncounterForm($data["encounter"], $pid, "vitals"), $pid),
            "labs" => getLabs($data["encounter"], $pid), //labs
            "soap_notes" => getEncounterSoap(getEncounterForm($data["encounter"], $pid, "soap"), $pid),
            "onset_date" => $data["onset_date"],
            "billing_note" => $data["billing_note"],
            "pid" => $data["pid"],
            "provider_id" => $data["provider_id"]
        );
    }

    EventAuditLogger::instance()->newEvent(
        "vehr: query get encounters",
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

function getEncounterVitals($form_id, $pid)
{
    $sql = "SELECT * FROM form_vitals WHERE id = '$form_id' AND pid = $pid";
    $result = sqlStatement($sql);

    $datalist = [];
    foreach ($result as $data) {
        $datalist[] = array(
            "id" => $data['id'],
            "pid" => $data['pid'],
            "date" => serialize_date($data["date"]),
            // "uuid" => UuidRegistry::uuidToString($data['uuid']),
            "bps" => $data["bps"],
            "bpd" => $data["bpd"],
            "weight" => $data["weight"],
            "height" => $data["height"],
            "temperature" => $data["temperature"],
            "temp_method" => $data["temp_method"],
            "pulse" => $data["pulse"],
            "respiration" => $data["respiration"],
            "note" => $data["note"],
            "BMI" => $data["BMI"],
            "BMI_status" => $data["BMI_status"],
            "waist_circ" => $data["waist_circ"],
            "head_circ" => $data["head_circ"],
            "oxygen_saturation" => $data["oxygen_saturation"],
            "external_id" => $data["external_id"],
            "oxygen_flow_rate" => $data["oxygen_flow_rate"],
            "ped_weight_height" => $data["ped_weight_height"],
            "ped_bmi" => $data["ped_bmi"],
            "ped_head_circ" => $data["ped_head_circ"],
            "inhaled_oxygen_concentration" => $data["inhaled_oxygen_concentration"],
        );
    }

    EventAuditLogger::instance()->newEvent(
        "vehr: query encounter vitals",
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

function getEncounterSoap($form_id, $pid)
{
    $sql = "SELECT * FROM form_soap WHERE id = '$form_id' AND pid = $pid";
    $result = sqlStatement($sql);

    $datalist = [];
    foreach ($result as $data) {
        $datalist[] = array(
            "id" => $data['id'],
            "pid" => $data['pid'],
            "date" => serialize_date($data["date"]),
            "user" => $data["user"],
            "groupname" => $data["groupname"],
            "authorized" => $data["authorized"],
            "activity" => $data["activity"],
            "subjective" => $data["subjective"],
            "objective" => $data["objective"],
            "assessment" => $data["assessment"],
            "plan" => $data["plan"]
        );
    }

    EventAuditLogger::instance()->newEvent(
        "vehr: query encounter soap",
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

function getLabs($eid, $pid)
{
    #procedure_order, procedure_report and procedure_result are join to fetch for lab test
    $sql = "SELECT procedure_order.patient_id,procedure_order.date_ordered,procedure_order.procedure_order_id,procedure_order.encounter_id,procedure_report.procedure_report_id,
        procedure_result.procedure_result_id,procedure_result.procedure_report_id,procedure_result.result_text,procedure_result.range,procedure_result.units,
        procedure_result.result_status,procedure_result.result
        FROM procedure_order
        JOIN procedure_report ON procedure_order.procedure_order_id = procedure_report.procedure_order_id
        JOIN procedure_result ON procedure_result.procedure_report_id = procedure_report.procedure_report_id 
        WHERE procedure_order.encounter_id=$eid AND procedure_order.patient_id=$pid";
    $result = sqlStatement($sql);

    $datalist = [];
    foreach ($result as $data) {
        $datalist[] = array(
            "created_at" => $data["date_ordered"],
            "result_text" => $data["result_text"],
            "result" => $data["result"],
            "range" => $data["range"] . ' ' . $data["units"],
            "result_status" => $data["result_status"]
        );
    }

    EventAuditLogger::instance()->newEvent(
        "vehr: query lab",
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

function getEncounterLabs($form_id, $pid)
{
    $sql = "SELECT * FROM procedure_order WHERE id = '$form_id' AND pid = $pid";
}


function generateNewColor($id)
{
    $colors = ["#eb5ea8", "#9f4ed3", "#99b1fc", "#7e83d0", "#fb4e4e", "#32a852", "#fb4e4e"];
    $randColorIndex = random_int(0, count($colors) - 1);
    $generatedColor = $colors[$randColorIndex];
    $sql = "UPDATE lists SET color = :color WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':color' => $generatedColor, ':id' => $id]);
    // EventAuditLogger::instance()->newEvent(
    //     "vehr: query generate color",
    //     null, //pid
    //     $_SESSION["authUser"], //authUser
    //     $_SESSION["authProvider"], //authProvider
    //     $sql,
    //     1,
    //     'visual-ehr',
    //     'dashboard'
    // );

    return $generatedColor;
}

function serialize_date($date)
{
    return $date;
    $data = explode(" ", $date);
    $time = explode(":", end($data));
    return (($time[1]) > 0) ? $date : $data[0];
}
