<?php
// Version 1.0 - initial release with all views
// Version 2.0 - Updates as of 7/10/2012, new surgery window, auto-save, 
//               additional fields. Made save.php part of this file to 
//               allow periodic save and 1 line addition of surgeriesr,
//               family history, past medical history, etc.
$fake_register_globals=false;
$sanitize_all_escapes=true;
$frmdir = 'ext_exam2';
$frmn = 'form_' . $frmdir;
include_once('../../globals.php');
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once($GLOBALS['srcdir'].'/calendar.inc');
include_once($GLOBALS['srcdir'].'/lists.inc');
include_once($GLOBALS['srcdir'].'/translation.inc.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/ee1form.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/approve.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/rto.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtform.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/dashboard.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtpatient.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/lifestyle.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/fyi.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/favorites.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/billing_tools.inc');
include_once("../../forms/$frmdir/report.php");
include_once("../../forms/$frmdir/referral.php");
if(is_file($GLOBALS['srcdir'].'/patient_tracker.inc.php')) 
			include_once($GLOBALS['srcdir'].'/patient_tracker.inc.php');
// include_once("../../forms/$frmdir/convert.php");
// include_once("../../forms/$frmdir/convert2.php");
// include_once("../../forms/$frmdir/convert_table.php");
// include_once("../../forms/$frmdir/convert_table2.php");
$ftitle = getNameOrNickName($frmdir);
$id='';
$prev_enc='';
$form_focus = '';
$diag_bar_bottom=true;
$form_mode='new';
$wrap_mode='update';
$first_pass=true;
$continue=false;
$scroll_point='';
if(isset($_SESSION['encounter'])) $encounter=$_SESSION['encounter'];
if(isset($_GET['enc'])) $encounter=$_GET['enc'];
if(isset($_SESSION['pid'])) $pid=$_SESSION['pid'];
if(isset($_GET['pid'])) $pid=strip_tags($_GET['pid']);
if($pid == '' || $pid == 0) ReportMissingPID();
if(isset($_GET['id'])) $id=strip_tags($_GET['id']);
$save_style="/forms/$frmdir/new.php?mode=save&enc=$encounter&pid=$pid";
$base_action="../../../interface/forms/$frmdir/new.php?enc=$encounter&pid=$pid";
if(isset($_GET['mode'])) $form_mode=strip_tags($_GET['mode']);
if($form_mode == 'new') $wrap_mode='new';
if(isset($_GET['wrap'])) { 
	$wrap_mode=strip_tags($_GET['wrap']);
	$first_pass=false;
}
if(isset($_GET['continue'])) $continue=strip_tags($_GET['continue']);
if(isset($_GET['focusfield'])) $form_focus=strip_tags($_GET['focusfield']);
if(isset($_GET['scroll'])) $scroll_point=strip_tags($_GET['scroll']);
$no_dup = checkSettingMode('wmt::no_duplicates','',$frmdir);
$pop_form = checkSettingMode('wmt::form_popup');
$pop_type = IsFormType($frmdir, 'pop_form');
if(!$pop_type) $pop_form = false;
// Don't allow two forms under the same encounter, open existing from the tab
if($no_dup && ($form_mode == 'new') && !$continue) {
	$sql= "SELECT * FROM forms WHERE deleted=0 AND pid=? AND ".
				"encounter=? AND formdir=?";
	$parms= array($pid, $encounter, $frmdir);
	$frow= sqlQuery($sql, $parms);
	if($frow{'form_id'}) {
		$id= $frow{'form_id'};
		$form_mode= 'update';
		$wrap_mode= 'update';
	}
}

if($id != '' && $id != 0) $save_style.='&id='.$id;
$save_style.='&wrap='.$wrap_mode;
$print_href=$GLOBALS['rootdir']."/forms/$frmdir/printable.php?id=$id&pid=$pid&enc=$encounter"; 
$print_instruct_href = $GLOBALS['rootdir']."/forms/$frmdir/print_instructions.php?id=$id&pid=$pid&enc=$encounter";
$print_referral_href = $GLOBALS['rootdir']."/forms/$frmdir/print_referral.php?id=$id&pid=$pid&enc=$encounter";
$print_referral_href = $GLOBALS['rootdir']."/reports/letters/single.php?id=$id";
$print_referral_href = '';

if($id && !$continue) {
	$frow = sqlQuery("SELECT id, form_complete, form_dt FROM $frmn WHERE id=?", array($id));
}
if(!isset($frow{'form_complete'})) $frow{'form_complete'} = '';
if($no_dup && (strtolower($frow{'form_complete'}) == 'a') && ($form_mode != 'save')) {
	$print_title = 'Extended Examination';
	$print_date = $frow{'form_dt'};
	// $print_instruct_href='';
	if($print_referral_href) $print_referral_href .= '&approved=true';
	include($GLOBALS['srcdir'].'/wmt-v2/wmtarchivedisplay.php');
} else {
		// Or proceed to editing
	include("../../forms/$frmdir/common.php");
}
?>
