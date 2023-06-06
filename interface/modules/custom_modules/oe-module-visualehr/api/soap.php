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


header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_GET["pid"]) && !isset($_GET["encounter_id"])) {
    echo json_encode([]);
    return;
}

$pid = preg_replace("#[^0-9]#", "", $_GET["pid"]);
$encounter_id = preg_replace("#[^0-9]#", "", $_GET["encounter_id"]);


switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'));
        saveSoap($db, $data, $pid, $encounter_id);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'));
        updateSoap($db, $data, $pid, $encounter_id);
        break;
}

function saveSoap($db, $data, $pid, $encounter_id)
{
    $date = date('Y-m-d H:i:s');
    $sql = "INSERT INTO form_soap(id, date, pid, user, groupname, authorized, activity, subjective, objective, assessment, plan) VALUES(null, '$date', '{$data->pid}', '{$data->user}', '{$data->groupname}', '{$data->authorized}', '{$data->activity}', '{$data->subjective}', '{$data->objective}', '{$data->assessment}', '{$data->plan}')";


    $result = sqlStatement($sql);

    EventAuditLogger::instance()->newEvent(
        "vehr: insert form_soap",
        $pid, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'open-emr',
        'dashboard'
    );


    echo json_encode($result);
}

function updateSoap($db, $data)
{
    $user = empty($data->user) ? null : $data->user;
    $date = date('Y-m-d H:i:s');
    $sql = "UPDATE form_soap SET date='$date', pid='{$data->pid}', user='$user', groupname='{$data->groupname}', authorized='{$data->authorized}', activity='{$data->activity}', subjective='{$data->subjective}', objective='{$data->objective}', assessment='{$data->assessment}', plan='{$data->plan}' WHERE id='{$data->id}'";
    $res =  sqlStatement($sql);

    EventAuditLogger::instance()->newEvent(
        "vehr: update form_soap",
        null, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'open-emr',
        'dashboard'
    );

    if ($res) {
        $response = ['status' => 1, 'message' => 'Record created successfully.'];
    } else {
        $response = ['status' => 0, 'message' => 'Failed to create record.'];
    }
    echo json_encode($response);
}

function addForm($db, $pid, $encounter_id, $data, $form_id)
{
    $date               = date('Y-m-d H:i:s');
    $form_name          = "SOAP";
    $formdir            = "soap";
    $deleted            = 0;
    $issue_id           = 0;
    $therapy_group_id   = null;
    $provider_id        = 0;
    $authorized         = 1;
    $groupname          = "Default";

    $sql = "INSERT INTO forms(id, date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir, therapy_group_id, issue_id, provider_id) VALUES(null, '$date', :encounter, '$form_name', :form_id, :pid, '{$data->user}', '$groupname', $authorized, $deleted, '$formdir', $therapy_group_id, $issue_id, $provider_id)";

    $res =  sqlStatement($sql);


    EventAuditLogger::instance()->newEvent(
        "vehr: insert form",
        $pid, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sql,
        1,
        'open-emr',
        'dashboard'
    );

    if ($res) {
            $response = ['status' => 1, 'message' => 'Record created successfully.'];
    } else {
            $response = ['status' => 0, 'message' => 'Failed to create record.'];
    }
    echo json_encode($response);
}
