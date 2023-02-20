<?php
/** **************************************************************************
 *	QUEST/SAVE.PHP
 *
 *	Copyright (c)2014 - Williams Medical Technology, Inc.
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package laboratory
 *	@subpackage quest
 *  @version 2.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/wmt/wmt.class.php");
require_once($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');

use OpenEMR\OemrAd\Smslib;
//require_once("$srcdir/wmt-v3/wmt.globals.php");

//SMS Service Manager
//$smsManagerObj = new \extTwilio\Sms();

// set process defaults
$form_name = "quest";
$form_title = "Quest Order";

// grab important data
$authuser = $_SESSION['authUser'];	
$groupname = $_SESSION['authProvider'];
$authorized = $_SESSION['userauthorized'];

$id = $_POST["id"];
$mode = $_POST['mode'];
$process = $_POST['process'];
$print = $_POST['print'];
$lab_id = $_POST['lab_id'];
$quest_siteid = $_POST['lab_quest_siteid'];

$provider = $_POST['provider'];
$encounter = $_POST['encounter'];
$pid = $_POST['pid'];
if (! $pid) die ("Patient identifier missing!!");
if (! $encounter) die ("Encounter identifier missing!!");

// localize some data elements
$test_code = $_POST['test_code'];
$test_profile = $_POST['test_profile'];
$test_text = $_POST['test_text'];
$test_type = $_POST['test_type'];

// get the table column names
$fields = wmtOrder::listFields($form_name);

// remove control fields
$fields = array_slice($fields,7);

// retrieve/generate the object
$item_list = array();
$aoe_list = array();

// retrieve or create order
$order_data = new wmtOrder($form_name,$id);

// get provider information
$lab_data = sqlQuery("SELECT * FROM procedure_providers WHERE ppid = ?",array($_POST['lab_id']));
if ($lab_data['name']) $form_title = $lab_data['name'];

// REVIEW ONLY (when results received) !!
if (strtotime($order_data->result_datetime) !== false) { // returns false if not a valid date
	$order_data->reviewed_datetime = '';
	$order_data->reviewed_id = ($_POST['reviewed_id'] != '_blank') ? $_POST['reviewed_id'] : '';
	$order_data->notified_datetime = '';
	$order_data->notified_person = $_POST['notified_person'];
	$order_data->request_handling = $_POST['request_handling'];
	$order_data->notified_id = ($_POST['notified_id'] != '_blank') ? $_POST['notified_id'] : '';
	$order_data->review_notes = $_POST['review_notes'];
	if ($order_data->reviewed_id) $order_data->status = 'v'; // reviewed
	if ($order_data->notified_id) $order_data->status = 'n'; // notified
//	$order_data->portal_flag = $_POST['portal_flag'];
	
	$review_date = $_POST['reviewed_date'];
	if (strtotime($reviewed_date))
		$order_data->reviewed_datetime = date('Y-m-d H:i',strtotime($reviewed_date));

	$notified_date = $_POST['notified_date'];
	if (strtotime($notified_date))
		$order_data->notified_datetime = date('Y-m-d H:i',strtotime($notified_date));
	
	// AUTOMATIC PORTAL DEPLOYMENT
	$current_portal = $order_data->portal_flag;
	$order_data->portal_flag = $_POST['portal_flag'];
	if ($order_data->result_abnormal < 1 && $order_data->reviewed_id) 
		$order_data->portal_flag = 1; // normal and reviewed so auto approve
	if ($order_data->result_abnormal > 0 && $order_data->notified_id)
		$order_data->portal_flag = 1; // abnormal but notified so approve
			
	// lab notification required
	if ($GLOBALS['wmt::lab_portal_notify']) {
		// portal flag set for first time
		if ($current_portal != 1 && $order_data->portal_flag == 1) {
			//$sms = new wmt\SMS();
			//$sms = new wmt\Nexmo();
			$sms = Smslib::getSmsObj();
			$sms->labNotice($pid, 'lab_sms_notify');
		}
	}
			
	// save the new information
	$order_data->update(); 
}

// already submitted so done processing
if ($mode == 'update' && $order_data->status != 'i') {
	// redirect to landing page
	formHeader("Redirecting...");

	if ($print) {
		$reload_url = "{$GLOBALS['rootdir']}/forms/$form_name/update.php?id=$id&enc=$encounter&print=1";
		$print_url = "{$GLOBALS['rootdir']}/forms/$form_name/print.php?id=$id&pid=$pid&enc=$encounter";
	
		echo "\n<script language='Javascript'>\nif ( top.frames.length == 0 ) { // not in a frame so pop up\n";
		echo "window.location='$print_url';\n";
		echo "if (window.opener) window.opener.location.href='$reload_url';\n";
		echo "} else { \n";
		echo "top.restoreSession();window.location='$reload_url';\n";
		echo "}\n</script>\n";
	}
	else {
		$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
		if ($address == "0" || $address == '')
			$address = "{$GLOBALS['rootdir']}/patient_file/encounter/$returnurl";

		echo "\n<script language='Javascript'>\nif ( top.frames.length == 0 ) { // not in a frame so pop up\n";
		echo "\nif (window.opener && window.opener.jQuery('forms').length > 0) window.opener.document.forms[0].submit();\nwindow.close();\n";
		echo "} else { \n";
		echo "top.restoreSession();window.location='$address';\n";
		echo "}\n</script>\n";
	}
	
	formFooter();
	exit;
}

// process form
$order_data->date = date('Y-m-d H:i:s');
$order_data->pid = $pid;
$order_data->user = $authuser;
$order_data->groupname = $groupname;
$order_data->authorized = $authorized;

// retrieve the post data for the fields
foreach ($fields as $field) {
	$order_data->$field = $_POST[$field];
	if ($order_data->$field == '_blank') $order_data->$field = '';
}

// standard settings
$order_data->activity = 1;
$order_data->encounter = $encounter;
$order_data->encounter_id = $encounter;

// set default status
$order_data->status = 'i'; // incomplete
$order_data->priority = 'n'; // normal
$order_data->order_priority = 'normal';
$order_data->order_status = 'pending';

// get order datetime value
$date_ordered = $_POST['date_ordered'];
if ($date_ordered) {
	$order_data->date_ordered = date('Y-m-d H:i:s',strtotime($date_ordered));
}
else {
	$order_data->date_ordered = date('Y-m-d H:i:s');
}

// get ordering provider information
$provider_username = '';
$provider_id = $_POST['provider_id'];

if ($provider_id == '_blank') $provider_id = false;
if ($provider_id) {
	$order_data->provider_id = $provider_id;
	$provider = sqlQuery("SELECT * FROM users WHERE id = $provider_id");
}
$provider_username = ($provider['username']) ? $provider['username'] : $authuser;
if ($provider['npi']) {
	$order_data->doc_npi = $provider['npi'];
	$order_data->doc_lname = $provider['lname'];
	$order_data->doc_fname = $provider['fname'];
	$order_data->doc_mname = $provider['mname'];
}

// get diagnosis data
$diagnoses = '';
if (is_array($_POST['dx_code'])) {
	for ($d = 0; $d < count($_POST['dx_code']); $d++) {
		$dx_code = $_POST['dx_code'][$d];
		if (strpos($dx_code,":") === false) $dx_code = 'ICD9:'.$_POST['dx_code'][$d]; 
		$diagnoses .= $dx_code."^".$_POST['dx_text'][$d]."|";
	}
}
elseif ($_POST['dx_code'] != '') {
	$dx_code = $_POST['dx_code'];
	if (strpos($dx_code,":") === false) $dx_code = 'ICD9:'.$_POST['dx_code']; 
	$diagnoses = $_POST['dx_code']."^".$_POST['dx_text']."|";
}
$order_data->diagnoses = $diagnoses;


// get specimen datetime value
if (!$order_data->order_psc) {
	$sdate = $_POST['date_collected'].' '.$_POST['time_collected'];
	$collected = strtotime($order_data->date_ordered); // fallback default
	if (strtotime($sdate) !== false)
		$collected = strtotime($sdate); 	
	$order_data->date_collected = date('Y-m-d H:i:s',$collected);
}
else {
	$pdate = $_POST['date_pending'];
	if (strtotime($pdate) !== false)
		$order_data->date_pending = date('Y-m-d H:i:s',strtotime($pdate));
}

// process new form
if ($mode == 'new') {
	// add order record
	$id = wmtOrder::insert($order_data);
	$order_data = new wmtOrder($form_name,$id); // refresh object
	
	// add to forms list
	addForm($encounter, $form_title." - ".$order_data->order_number, $id, $form_name, $pid, $authorized, 'NOW()', $provider_username);
}
// process form update
else if ($mode == 'update') {
	// update the order data
	$order_data->update();
}
else {
	die ("Unknown mode '$mode'");
}

// remove old test records
if ($order_data->order_number) {
	sqlStatement("DELETE FROM procedure_order_code WHERE procedure_order_id = ?",array($order_data->order_number));
	sqlStatement("DELETE FROM procedure_answers WHERE procedure_order_id = ?",array($order_data->order_number));
}

// create test records
for ($t = 0; $t < count($test_code); $t++) {
	// create a new test record
	$seq = $t +1;
	$code = $test_code[$t];
	$text = $test_text[$t];
	$profile = $test_profile[$t];
	$order_item = new wmtOrderItem();
	
	$order_item->procedure_order_id = $order_data->order_number;
	$order_item->lab_id = $order_data->lab_id;
	$order_item->procedure_order_seq = $seq;
	$order_item->procedure_code = $code;
	$order_item->procedure_name = $text;
	$order_item->procedure_source = 1;
	$order_item->procedure_type = $profile;
	$order_item->diagnoses = $diagnoses;

	wmtOrderItem::insert($order_item);
	$item_list[] = new wmtOrderItem($order_data->order_number, $seq);
	
	$code_key = "aoe".$code."_code";
	$label_key = "aoe".$code."_label";
	$text_key = "aoe".$code."_text";
	for ($a = 0; $a < count($_POST[$code_key]); $a++) {
		$key = "aoe".$a."_code";
		$qcode = $_POST[$code_key][$a];
		$key = "aoe".$a."_label";
		$label = $_POST[$label_key][$a];
		$key = "aoe".$a."_text";
		$qtext = $_POST[$text_key][$a];
		
		// save answer record
		$qseq = $a +1;
		$params = array();
		$params[] = $order_item->procedure_order_id;
		$params[] = $order_item->procedure_order_seq;
		$params[] = $order_item->procedure_code;
		$params[] = $qcode;
		$params[] = $qtext;
		$params[] = $qseq;

		sqlInsert("INSERT INTO procedure_answers SET ".
			"procedure_order_id = ?," .
			"procedure_order_seq = ?, " .
			"procedure_code = ?, " .
			"question_code = ?, " .
			"answer = ?, " .
			"answer_seq = ? ",
		$params);
	}

}

// should we send the order
if ($process) {
	include('process.php');
}
elseif ($print) {
	// redirect to landing page
	formHeader("Redirecting...");
	
	$reload_url = "{$GLOBALS['rootdir']}/forms/$form_name/update.php?id=$id&print=1";
	$print_url = "{$GLOBALS['rootdir']}/forms/$form_name/print.php?id=$id&pid=$pid&enc=$encounter";
		
	echo "\n<script language='Javascript'>\nif ( top.frames.length == 0 ) { // not in a frame so pop up\n";
	echo "window.opener.location.href='$reload_url';\n";
	echo "window.location='$print_url';\n";
	echo "} else { \n";
	echo "top.restoreSession();window.location='$reload_url';\n";
	echo "}\n</script>\n";
		
	formFooter();
}
else {
	// redirect to landing page
	formHeader("Redirecting...");
	
	$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
	if ($address == "0" || $address == '')
		$address = "{$GLOBALS['rootdir']}/patient_file/encounter/$returnurl";
	
	echo "\n<script language='Javascript'>\nif ( top.frames.length == 0 ) { // not in a frame so pop up\n";
	echo "window.opener.document.forms[0].submit();window.close();\n";
	echo "} else { \n";
	echo "top.restoreSession();window.location='$address';\n";
	echo "}\n</script>\n";

	formFooter();
}

?>
