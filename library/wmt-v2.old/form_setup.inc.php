<?php
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once($GLOBALS['srcdir'].'/calendar.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/options.inc.php');
include_once($GLOBALS['srcdir'].'/translation.inc.php');
include_once($GLOBALS['srcdir'].'/formatting.inc.php');
include_once($GLOBALS['srcdir'].'/pnotes.inc');
include_once($GLOBALS['srcdir'].'/billing.inc');
include_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/billing_tools.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/approve.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtform.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtpatient.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/dashboard.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/fyi.class.php');
include_once("../../forms/$frmdir/report.php");

$ftitle = xl(getNameOrNickName($frmdir),'r');
if(!isset($id)) $id = '';
$first_pass = true;
$continue = false;
$skip_approve = false;
$form_focus = '';
$scroll_point = '';
$no_dup = checkSettingMode('wmt::no_duplicates','',$frmdir);
$wrap_mode = $form_mode;

$pop_form = checkSettingMode('wmt::form_popup');
$pop_type = IsFormType($frmdir, 'pop_form');
$bill_form = IsFormType($frmdir, 'bill_form');
if(!$pop_type) $pop_form = false;
if(isset($_GET['popup'])) {
	$pop_form = true;
	if($_GET['popup'] == 'false' || $_GET['popup'] == 'no') $pop_form = false;
}

$form_event_logging = checkSettingMode('wmt::form_logging','',$frmdir);
if(isset($_GET['wrap'])) { 
	$wrap_mode = strip_tags($_GET['wrap']);
	$first_pass = false;
}

// THIS IS ONLY FOR FORMS THAT CAN BE CALLED FROM THE LEFT NAV, WE MUST BE 
// ABLE TO CREATE OR GET THE ENCOUNTER FROM TODAY IF IT EXISTS ALREADY
// NOTE - POSSIBLE FIX!! - THERE COULD BE MULCIPLE ENCOUNTERS FOR THE DAY?
$auto_encounter = false;
if(isset($_GET['autoloaded'])) 
		$auto_encounter = strip_tags($_GET['autoloaded']);
if($auto_encounter) {
	$pop_form = false;
	$test = $_SESSION['pid'];
	if(!$test) {
		echo "<h>You MUST Select A Patient First</h>\n";
		echo "<html>\n";
		echo "<head>\n";
		echo "<title>Redirecting.....</title>\n";
		if($pop_form) {
			echo "\n<script type='text/javascript'>";
			echo "alert('A Patient Must Be Chosen...Closing');\n";
			echo "window.close();</script>\n";
			echo "</head>\n";
			echo "</html>\n";
			exit;
		} else {
			echo "</head>\n";
			echo "</html>\n";
			echo "\n<script type='text/javascript'>";
			$address = $GLOBALS['rootdir'].'/new/new.php';
			echo "alert('A Patient Must Be Chosen...Closing');\n";
			echo "window.location='$address';\n";
			echo "</script>\n";
		}
	}

	if($first_pass && $form_mode == 'new' && !$encounter) {
		include_once($GLOBALS['srcdir'].'/encounter_events.inc.php');
		$today = date('Y-m-d');
		$thisProvider = '';
		$thisReason = checkSettingMode('wmt::auto_enc_reason', '', $frmdir);
		$thisCategory = checkSettingMode('wmt::auto_enc_category', '', $frmdir);
		if(!$thisReason) $thisReason = 'Office Visit';
		if(IsDoctor()) $thisProvider = $_SESSION['authUserID'];
		$encounter = todaysEncounterCheck($pid, $today, 
							$thisReason, '', '', $thisProvider, $thisCategory);
		$_SESSION['encounter'] = $encounter;
	}
}
// END OF THE AUTO-ENCOUNTER LOGIC

if(isset($_SESSION['encounter'])) $encounter = $_SESSION['encounter'];
if(isset($_GET['enc'])) $encounter = strip_tags($_GET['enc']);
if(isset($_SESSION['pid'])) $pid = $_SESSION['pid'];
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
if($pid == '' || $pid == 0) ReportMissingPID();
if(isset($_GET['id'])) $id = strip_tags($_GET['id']);
if(isset($_GET['mode'])) $form_mode = strip_tags($_GET['mode']);
if(isset($_GET['continue'])) $continue = strip_tags($_GET['continue']);
if(isset($_GET['focusfield'])) $form_focus = strip_tags($_GET['focusfield']);
if($form_mode == 'new') $wrap_mode = 'new';
$save_style = "/forms/$frmdir/new.php?mode=save&enc=$encounter&pid=$pid";
$base_action = $GLOBALS['webroot'];
$base_action .= "/interface/forms/$frmdir/new.php?enc=$encounter&pid=$pid";
if($auto_encounter) {
	$save_style .= '&autoloaded=' . $auto_encounter;
	$base_action .= '&autoloaded=' . $auto_encounter;
}
if(isset($_GET['popup'])) {
	$save_style .= '&popup=' . strip_tags($_GET['popup']);
	$base_action .= '&popup=' . strip_tags($_GET['popup']);
}

// THIS FLAG DOESN'T ALLOW TWO OF THE SAME FORMS ON THE SAME ENCOUNTER
if($no_dup && ($form_mode == 'new')) {
	$sql= 'SELECT * FROM forms WHERE deleted=0 AND pid=? AND '.
				'encounter = ? AND formdir = ?';
	$parms = array($pid, $encounter, $frmdir);
	$frow = sqlQuery($sql, $parms);
	if($frow{'form_id'}) {
		$id = $frow{'form_id'};
		$form_mode = 'update';
		$wrap_mode = 'update';
	}
}
if($id != '' && $id != 0) $save_style .= '&id='.$id;
$save_style .= '&wrap='.$wrap_mode;

$print_href = $GLOBALS['rootdir']."/forms/$frmdir/printable.php?id=$id".
		"&pid=$pid&enc=$encounter"; 
$print_instruct_href = '';

// CHECK IF ALREADY APPROVED
if($id && !$continue) {
	$frow = sqlQuery("SELECT id, form_complete, form_dt FROM $frmn WHERE id=?", array($id));
}
if(!isset($frow{'form_complete'})) $frow{'form_complete'} = '';
if(!isset($frow{'form_dt'})) $frow{'form_dt'} = '';
if(strtolower($frow{'form_complete'}) == 'a' && 
		($form_mode == 'new' || $form_mode == 'update')) {
	$print_date = $frow{'form_dt'} ? $frow{'form_dt'} : date('Y-m-d');
	include($GLOBALS['srcdir'].'/wmt-v2/wmtarchivedisplay.php');
	exit;
}

$warn_popup = checkSettingMode('wmt::cancel_warning_on','',$frmdir);
$approve_popup = checkSettingMode('wmt::approve_warning_on','',$frmdir);
$use_tasks=checkSettingMode('wmt::use_tasks','',$frmdir);
if($use_tasks) include_once($GLOBALS['srcdir'].'/wmt-v2/rto.class.php');
$hpi_override = checkSettingMode('wmt::hpi_clear_ros','',$frmdir);
$include_pat_summary = checkSettingMode('wmt::include_pat_summary','',$frmdir);
$noload = checkSettingMode('wmt::noload_form_history','',$frmdir);
$use_charges = checkSettingMode('wmt::use_charges','',$frmdir);
$auto_post_pqrs = checkSettingMode('wmt::auto_post_pqrs','',$frmdir);
$allergy_add_allowed = checkSettingMode('wmt::db_allergy_add');
$med_add_allowed = checkSettingMode('wmt::db_meds_add');
$use_meds_not_rx = checkSettingMode('wmt::use_meds_not_rx');
$use_diag_link_checkbox = checkSettingMode('wmt::diag_use_checkbox','',$frmdir);
$med_list_type = ($use_meds_not_rx) ? 'medication' : 'prescriptions';
$unlink_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'med');
$delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
$unlink_all_rx_history = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'med');
$unlink_all_allergies = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'med');
$unlink_all_meds = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'med');
$use_snippets = checkSettingMode('wmt::use_snippets','',$frmdir);
$approval = canApproveForms();
$is_billed = isEncounterBilled($pid, $encounter);

$patient = wmtPatData::getPidPatient($pid);
$pat_sex = strtolower(substr($patient->sex,0,1));
$visit = wmtPrintVisit::getEncounter($encounter);
$dt['db_pat_blood_type'] = $patient->blood_type;
$dt['db_pat_rh_factor'] = $patient->rh_factor;
$dashboard = wmtDashboard::getPidDisplayDashboard($pid);
foreach($dashboard as $key => $val) {
	$dt[$key] = $val;
}
$fyi = wmtFYI::getPidDisplayFYI($pid);
foreach($fyi as $key => $val) {
	if(substr($key,0,3) != 'fyi') continue;
	$dt[$key] = $val;
}
$approval = canApproveForms();

// SUPPORT FOR A FULLY FUNCTIONAL PATIENT PORTAL

$cancel_warning = xl("Are you sure you wish to exit and discard your changes? Click 'OK' to continue to exit, or 'Cancel' to save or continue working on this form.");
$cancel_field = 'form_complete';
$cancel_compare = 'a';
if($bill_form) {
	$cancel_field = 'form_priority';
	$cancel_compare = 'u';
}

$modules = LoadList($frmdir . '_modules', 'active');

global $ros_options, $wmt_ros, $rs;

// READ THE MODULES HERE AND SET THE ROS
$ros_module = '';
foreach($modules as $module) {
	if(strpos('ros', $module['option_id']) !== FALSE) 
		$ros_module = $module['option_id'];
}
if($ros_module) {
	include_once($GLOBALS['srcdir'].'/wmt-v2/ros_functions.inc');
	$ros_options = LoadList($frmdir.'_ROS_Keys', 'active');
	$ros_unused = LoadList($frmdir.'_ROS_Keys', 'inactive');
	foreach($ros_options as $o) {
		$rs[$o['option_id']] = '';
		$rs[$o['option_id'].'_nt'] = '';
	}
	$flds = sqlListFields('form_wmt_ros');
	$flds = array_slice($flds,7);
	foreach($flds as $key => $fld) { 
		$wmt_ros[$fld]='';
		$ros_parent[$fld]='';
	}
}
?>
