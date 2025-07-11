<?php

$_SESSION['site_id'] = 'default';

require_once "../../../../globals.php";

use OpenEMR\Common\Logging\EventAuditLogger;

use Ramsey\Uuid\Uuid;

$method = $_SERVER['REQUEST_METHOD'];



switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'));
        save($data);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'));
        update($data);
        break;

    default:
        # code...
        break;
}

function save($data)
{
    $bindArray = array(
        Uuid::uuid4(),
        $data->date,
        $data->type,
        $data->name_display,
        $data->start_date,
        $data->end_date,
        $data->returndate ?? "", // Optional chaining operator to assign an empty string if 'returndate' doesn't exist
        $data->occurrence,
        $data->classification ?? "", // Optional chaining operator to assign an empty string if 'classification' doesn't exist
        $data->referred_by,
        $data->extrainfo ?? "", // Optional chaining operator to assign an empty string if 'extrainfo' doesn't exist
        $data->icd_10,
        $data->activity ?? "", // Optional chaining operator to assign an empty string if 'activity' doesn't exist
        $data->comments,
        $data->pid,
        $_SESSION["authUser"],
        $data->groupname ?? "", // Optional chaining operator to assign an empty string if 'groupname' doesn't exist
        $data->outcome,
        $data->destination,
        $data->reinjury_id ?? "", // Optional chaining operator to assign an empty string if 'reinjury_id' doesn't exist
        $data->injury_part ?? "", // Optional chaining operator to assign an empty string if 'injury_part' doesn't exist
        $data->injury_type ?? "", // Optional chaining operator to assign an empty string if 'injury_type' doesn't exist
        $data->injury_grade ?? "", // Optional chaining operator to assign an empty string if 'injury_grade' doesn't exist
        $data->reaction ?? "", // Optional chaining operator to assign an empty string if 'reaction' doesn't exist
        $data->verification ?? "", // Optional chaining operator to assign an empty string if 'verification' doesn't exist
        $data->external_allergyid ?? "", // Optional chaining operator to assign an empty string if 'external_allergyid' doesn't exist
        $data->erx_source ?? "", // Optional chaining operator to assign an empty string if 'erx_source' doesn't exist
        $data->erx_uploaded ?? "", // Optional chaining operator to assign an empty string if 'erx_uploaded' doesn't exist
        $data->modifydate ?? "", // Optional chaining operator to assign an empty string if 'modifydate' doesn't exist
        $data->severity ?? "", // Optional chaining operator to assign an empty string if 'severity' doesn't exist
        $data->external_id ?? "", // Optional chaining operator to assign an empty string if 'external_id' doesn't exist
        $data->list_option_id ?? "", // Optional chaining operator to assign an empty string if 'list_option_id' doesn't exist
        $data->udi ?? "", // Optional chaining operator to assign an empty string if 'udi' doesn't exist
        $data->udi_data ?? "", // Optional chaining operator to assign an empty string if 'udi_data' doesn't exist
        // $data->color, // Remove the commented line
    );




    $sets = "uuid = ?, 
        date = ?, 
        type = ?,
        title = ?, 
        begdate = ?, 
        enddate = ?, 
        returndate = ?, 
        occurrence = ?, 
        classification = ?, 
        referredby = ?, 
        extrainfo = ?, 
        diagnosis = ?, 
        activity = ?, 
        comments = ?, 
        pid = ?, 
        user = ?, 
        groupname = ?, 
        outcome = ?, 
        destination = ?, 
        reinjury_id = ?, 
        injury_part = ?, 
        injury_type = ?, 
        injury_grade = ?, 
        reaction = ?, 
        verification = ?, 
        external_allergyid = ?, 
        erx_source = ?, 
        erx_uploaded = ?, 
        modifydate = ?, 
        severity_al = ?, 
        external_id = ?, 
        list_option_id = ?, 
        udi = ?, 
        udi_data = ?
        ";
    // color = ?

    $res  = sqlInsert("INSERT INTO lists SET $sets", $bindArray);

    EventAuditLogger::instance()->newEvent(
        "vehr: insert lists",
        $data->pid, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sets,
        1,
        'visual-ehr',
        'dashboard'
    );

    echo json_encode($res);
}

function update($data)
{
    $updated_at = date('Y-m-d');
    $bindArray = array(

        $data->uuid,
        $data->date,
        $data->type,
        $data->name_display,
        $data->start_date,
        $data->end_date,
        $data->returndate ?? "", // Optional chaining operator to assign an empty string if 'returndate' doesn't exist
        $data->occurrence,
        $data->classification ?? "", // Optional chaining operator to assign an empty string if 'classification' doesn't exist
        $data->referred_by,
        $data->extrainfo ?? "", // Optional chaining operator to assign an empty string if 'extrainfo' doesn't exist
        $data->icd_10,
        $data->activity ?? "", // Optional chaining operator to assign an empty string if 'activity' doesn't exist
        $data->comments,
        $data->pid,
        $_SESSION["authUser"],
        $data->groupname ?? "", // Optional chaining operator to assign an empty string if 'groupname' doesn't exist
        $data->outcome,
        $data->destination,
        $data->reinjury_id ?? "", // Optional chaining operator to assign an empty string if 'reinjury_id' doesn't exist
        $data->injury_part ?? "", // Optional chaining operator to assign an empty string if 'injury_part' doesn't exist
        $data->injury_type ?? "", // Optional chaining operator to assign an empty string if 'injury_type' doesn't exist
        $data->injury_grade ?? "", // Optional chaining operator to assign an empty string if 'injury_grade' doesn't exist
        $data->reaction ?? "", // Optional chaining operator to assign an empty string if 'reaction' doesn't exist
        $data->verification ?? "", // Optional chaining operator to assign an empty string if 'verification' doesn't exist
        $data->external_allergyid ?? "", // Optional chaining operator to assign an empty string if 'external_allergyid' doesn't exist
        $data->erx_source ?? "", // Optional chaining operator to assign an empty string if 'erx_source' doesn't exist
        $data->erx_uploaded ?? "", // Optional chaining operator to assign an empty string if 'erx_uploaded' doesn't exist
        $data->modifydate ?? "", // Optional chaining operator to assign an empty string if 'modifydate' doesn't exist
        $data->severity ?? "", // Optional chaining operator to assign an empty string if 'severity' doesn't exist
        $data->external_id ?? "", // Optional chaining operator to assign an empty string if 'external_id' doesn't exist
        $data->list_option_id ?? "", // Optional chaining operator to assign an empty string if 'list_option_id' doesn't exist
        $data->udi ?? "", // Optional chaining operator to assign an empty string if 'udi' doesn't exist
        $data->udi_data ?? "", // Optional chaining operator to assign an empty string if 'udi_data' doesn't exist
        // $data->color,
        $data->id
    );



    $sets = "uuid = ?, 
        date = ?, 
        type = ?,
        title = ?, 
        begdate = ?, 
        enddate = ?, 
        returndate = ?, 
        occurrence = ?, 
        classification = ?, 
        referredby = ?, 
        extrainfo = ?, 
        diagnosis = ?, 
        activity = ?, 
        comments = ?, 
        pid = ?, 
        user = ?, 
        groupname = ?, 
        outcome = ?, 
        destination = ?, 
        reinjury_id = ?, 
        injury_part = ?, 
        injury_type = ?, 
        injury_grade = ?, 
        reaction = ?, 
        verification = ?, 
        external_allergyid = ?, 
        erx_source = ?, 
        erx_uploaded = ?, 
        modifydate = ?, 
        severity_al = ?, 
        external_id = ?, 
        list_option_id = ?, 
        udi = ?, 
        udi_data = ?
        ";
    // color = ?

    $sql = "UPDATE lists SET $sets WHERE id = ?;";

    $response = sqlStatement($sql, $bindArray);

    EventAuditLogger::instance()->newEvent(
        "vehr: insert lists",
        $data->pid, //pid
        $_SESSION["authUser"], //authUser
        $_SESSION["authProvider"], //authProvider
        $sets,
        1,
        'visual-ehr',
        'dashboard'
    );

    if ($response) {
        $response = ['status' => 1, 'message' => 'Record updated successfully.'];
    } else {
        $response = ['status' => 0, 'message' => 'Failed to update record.'];
    }
    echo json_encode($response);
}
