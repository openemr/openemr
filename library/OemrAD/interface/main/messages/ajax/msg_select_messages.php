<?php

include_once("../../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once($GLOBALS['fileroot'].'/modules/ext_message/message/EmailMessage.php');
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Attachment;

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
//$searchValue = mysqli_real_escape_string($con,$_POST['search']['value']); // Search value

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
if(!isset($_REQUEST['assigned_to'])) $_REQUEST['assigned_to'] = '';

$pid = strip_tags($_REQUEST['pid']);
$assigned_to = strip_tags($_REQUEST['assigned_to']);

//$pid = explode(";", $pid);

## Total number of records without filtering
$records = Attachment::getRawMessageDataForSelection(array('pid' => $pid, 'user' => $assigned_to), 'count(*) as allcount');
$totalRecords = !empty($records) ? $records[0]['allcount'] : 0;

## Fetch records
//$recordsList = Attachment::fetchMessageList($pid, $assigned_to, 'pnotes.id, pnotes.user, pnotes.pid, pnotes.title, pnotes.date, pnotes.message_status, pnotes.assigned_to, list_options.option_id, IF(pnotes.user != pnotes.pid, u.fname, patient_data.fname) AS users_fname, IF(pnotes.user != pnotes.pid, u.lname, patient_data.lname) AS users_lname, IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.title, msg_to.lname), patient_data.lname) AS msg_to_lname, IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.notes, msg_to.fname), patient_data.fname) AS msg_to_fname, patient_data.fname AS patient_data_fname, patient_data.lname AS patient_data_lname, CONCAT( patient_data.lname, " ", patient_data.fname ) AS patient_fullname, CONCAT( IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.title, msg_to.lname), patient_data.lname), " ", IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.notes, msg_to.fname), patient_data.fname) ) AS msg_to, CONCAT( IF(pnotes.user != pnotes.pid, u.lname, patient_data.lname), " ", IF(pnotes.user != pnotes.pid, u.fname, patient_data.fname) ) AS user_fullname ', $columnName, $columnSortOrder, $row, $rowperpage);

$recordData = Attachment::getMessageDataForSelection(array('pid' => $pid, 'user' => $assigned_to), 'pnotes.id, pnotes.user, pnotes.pid, pnotes.title, pnotes.date, pnotes.message_status, pnotes.assigned_to, list_options.option_id, IF(pnotes.user != pnotes.pid, u.fname, patient_data.fname) AS users_fname, IF(pnotes.user != pnotes.pid, u.lname, patient_data.lname) AS users_lname, IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.title, msg_to.lname), patient_data.lname) AS msg_to_lname, IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.notes, msg_to.fname), patient_data.fname) AS msg_to_fname, patient_data.fname AS patient_data_fname, patient_data.lname AS patient_data_lname, CONCAT( patient_data.lname, " ", patient_data.fname ) AS patient_fullname, CONCAT( IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.title, msg_to.lname), patient_data.lname), " ", IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.notes, msg_to.fname), patient_data.fname) ) AS msg_to, CONCAT( IF(pnotes.user != pnotes.pid, u.lname, patient_data.lname), " ", IF(pnotes.user != pnotes.pid, u.fname, patient_data.fname) ) AS user_fullname ', $columnName, $columnSortOrder, $row, $rowperpage);
$recordsList = $recordData['items'];
$jsonData = $recordData['json_items'];

$data = array();
foreach ($recordsList as $i => $row) {
	/*$patient_id = $row['pid'];
	$patientData = getPatientData($patient_id, "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");


	$patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));

    $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);

    $row['patient_name'] = $patientName;
    $row['patient_DOB'] = $patientDOB;
    $row['pubpid'] = $patientData['pubpid'];
    $row['pid'] = $patient_id;

    $name = $row['user'];
    $name = $row['users_lname'];
    if ($row['users_fname']) {
        $name .= ", " . $row['users_fname'];
    }
    if(empty($name)) $name = $row['user'];
		        $msg_to = $row['msg_to_lname'];
    if ($row['msg_to_fname']) {
        $msg_to .= ", " . $row['msg_to_fname'];
    }

    $patient = $row['pid'];
    if ($patient > 0) {
        $patient = $row['patient_data_lname'];
        if ($row['patient_data_fname']) {
            $patient .= ", " . $row['patient_data_fname'];
        }
    } else {
        $patient = "* " . xlt('Patient must be set manually') . " *";
    }

    $row['user_fullname'] = $name;
    $row['msg_to'] = $msg_to;
    $row['patient_fullname'] = $patient;
    $row['link_title'] = '('.$row['id'].') '.$row['user_fullname'].' - '.$row['msg_to'].' - '.$row['patient_fullname'].' - '.$row['message_status'].' - '.text(oeFormatShortDate(substr($row['date'], 0, strpos($row['date'], " "))));
    $row['date'] = text(oeFormatShortDate(substr($row['date'], 0, strpos($row['date'], " "))));

    $row['row_select'] = $row['id'];
	//$row['row_select'] = '<input type="checkbox" class="checkboxes itemCheck" data-title="'.addslashes($row['link_title']).'" data-pid="'.$row['pid'].'" data-patientdob="'.$row['patient_DOB'].'" data-patientname="'.$row['patient_name'].'" data-pubpid="'.$row['pubpid'].'"  id="order_'.$row['id'].'" value="'.$row['id'].'">';
    */

    $row['row_data'] = array();
    if(isset($jsonData['message_' . $row['id']])) {
        $row['row_data'] = $jsonData['message_' . $row['id']];
    }

	$data[] = $row;
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecords,
  "iTotalDisplayRecords" => $totalRecords,
  "aaData" => $data
);

echo json_encode($response);