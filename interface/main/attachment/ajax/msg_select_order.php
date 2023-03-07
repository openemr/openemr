<?php

include_once("../../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
require_once("{$GLOBALS['srcdir']}/options.inc.php");
require_once("{$GLOBALS['srcdir']}/api.inc");
require_once("{$GLOBALS['srcdir']}/calendar.inc");
require_once("{$GLOBALS['srcdir']}/pnotes.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtstandard.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtpatient.class.php");
include_once($GLOBALS['srcdir']."/wmt-v2/rto.inc");
include_once($GLOBALS['srcdir']."/wmt-v2/rto.class.php");
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
$pid = strip_tags($_REQUEST['pid']);

## Total number of records without filtering
$records = Attachment::getRawOrderDataForSelection(array('pid' => $pid), 'count(*) as allcount');
$totalRecords = !empty($records) ? $records[0]['allcount'] : 0;

## Fetch records
//$recordsList = MessagesLib::getOrderList($pid, 'fr.*', $columnName, $columnSortOrder, $row, $rowperpage);
$recordData = Attachment::getOrderDataForSelection(array('pid' => $pid), 'fr.*', $columnName, $columnSortOrder, $row, $rowperpage);
$recordsList = $recordData['items'];
$jsonData = $recordData['json_items'];



$data = array();
foreach ($recordsList as $i => $ritem) {
  /*$itemTitle = ListLook($ritem['rto_action'],'RTO_Action');

	if(!empty($ritem['rto_status'])) {
		$itemTitle .= ' - '.ListLook($ritem['rto_status'],'RTO_Status');
	}

  
	$patient_id = $ritem['pid'];
	$patientData = getPatientData($patient_id, "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
	$patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));
  $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);

  $ritem['patient_name'] = $patientName;
  $ritem['patient_DOB'] = $patientDOB;
  $ritem['pubpid'] = $patientData['pubpid'];

  $ritem['title'] = $itemTitle;		
	$ritem['rto_action'] = ListLook($ritem['rto_action'],'RTO_Action');
	$ritem['rto_ordered_by'] = UserNameFromName($ritem['rto_ordered_by']);
	$ritem['rto_status'] = ListLook($ritem['rto_status'],'RTO_Status');
	$ritem['rto_resp_user'] = !empty($ritem['rto_resp_user']) ? UserNameFromName($ritem['rto_resp_user']) : '';

	$ritem['row_select'] = $ritem['id'];
	//$ritem['row_select'] = '<input type="checkbox" class="checkboxes itemCheck" data-title="'.addslashes($itemTitle).'" id="order_'.$ritem['id'].'" data-pid="'.$ritem['pid'].'" data-patientdob="'.$ritem['patient_DOB'].'" data-patientname="'.$ritem['patient_name'].'" data-pubpid="'.$ritem['pubpid'].'" value="'.$ritem['id'].'">';
	*/

	$ritem['row_data'] = array();
	if(isset($jsonData['order_' . $ritem['id']])) {
		$ritem['row_data'] = $jsonData['order_' . $ritem['id']];
	}

	$data[] = $ritem;
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecords,
  "iTotalDisplayRecords" => $totalRecords,
  "aaData" => $data
);

echo json_encode($response);