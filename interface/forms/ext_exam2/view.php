<?php
// Version 1.0 - initial release with all views
// Version 2.0 - Updates as of 7/10/2012, new surgery window, auto-save, 
//               additional fields. Made save.php part of this file to 
//               allow periodic save and 1 line addition of surgeriesr,
//               family history, past medical history, etc.
$fake_register_globals=false;
$sanitize_all_escapes=true;
$frmdir = 'ext_exam2';
$frmn = 'form_'.$frmdir;
include_once("../../globals.php");
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
include_once($GLOBALS['srcdir'].'/wmt-v2/billing_tools.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/favorites.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/dashboard.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printfacility.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtpatient.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/lifestyle.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/fyi.class.php');
include_once("../../forms/$frmdir/report.php");
include_once("../../forms/$frmdir/referral.php");
//include_once($GLOBALS['srcdir']."/extlib/core.php");
if(is_file($GLOBALS['srcdir'].'/patient_tracker.inc.php')) 
			include_once($GLOBALS['srcdir'].'/patient_tracker.inc.php');
$ftitle = getNameOrNickName($frmdir);
// include_once("../../forms/$frmdir/convert.php");
// include_once("../../forms/$frmdir/convert2.php");
$id='';
$prev_enc='';
$form_focus = '';
$diag_bar_bottom=true;
$form_mode='update';
$wrap_mode='update';
$first_pass=true;
$form_focus='';
$scroll_point='';
$continue = false;
if(isset($_SESSION['encounter'])) $encounter = $_SESSION['encounter'];
if(isset($_GET['enc'])) $encounter = $_GET['enc'];
if(isset($_SESSION['pid'])) $pid = $_SESSION['pid'];
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
if($pid == '' || $pid == 0) ReportMissingPID();
if(isset($_GET['id'])) $id=strip_tags($_GET['id']);
$save_style="/forms/$frmdir/new.php?mode=save&enc=$encounter&pid=$pid";
if($id != '' && $id != 0) $save_style .= '&id='.$id;
if($id == '' || $id == 0) ReportMissingID();
$base_action="../../../interface/forms/$frmdir/new.php?enc=$encounter&pid=$pid";
if(isset($_GET['mode'])) $form_mode = strip_tags($_GET['mode']);
if($form_mode == 'new') $wrap_mode = 'new';
if(isset($_GET['wrap'])) { 
	$wrap_mode = strip_tags($_GET['wrap']);
	$first_pass = false;
}
$save_style .= '&wrap='.$wrap_mode;
if(isset($_GET['continue'])) $continue = strip_tags($_GET['continue']);
if(isset($_GET['focusfield'])) $form_focus = strip_tags($_GET['focusfield']);
if(isset($_GET['scroll'])) $scroll_point = strip_tags($_GET['scroll']);

$pop_form = checkSettingMode('wmt::form_popup');
$no_dup = checkSettingMode('wmt::no_duplicates','',$frmdir);
$pop_type = IsFormType($frmdir, 'pop_form');
if(!$pop_type) $pop_form = false;
$print_href=$GLOBALS['rootdir']."/forms/$frmdir/printable.php?id=$id&pid=$pid&enc=$encounter"; 
$print_instruct_href = $GLOBALS['rootdir']."/forms/$frmdir/print_instructions.php?id=$id&pid=$pid&enc=$encounter";
$print_referral_href = '';
$print_referral_href = $GLOBALS['rootdir']."/forms/$frmdir/print_referral.php?id=$id&pid=$pid&enc=$encounter";
$print_summary_href = $GLOBALS['rootdir']."/forms/$frmdir/print_pat_summary.php?id=$id&enc=$encounter&pid=$pid&pop=pop";

$dt=sqlQuery("SELECT id, form_complete, form_dt FROM $frmn WHERE id=$id");
if(strtolower($dt{'form_complete'}) == 'a' && !$continue) {
	$print_title = 'Extended Examination';
	$print_date = $dt{'form_dt'};
	$print_css = 'wmtreport.bkk.css';
	if($print_referral_href) $print_referral_href .= '&approved=true';
	if(!FormInRepository($pid, $encounter, $id, $frmn.'_referral')) {
		$print_referral_href = '';
	}
	include($GLOBALS['srcdir'].'/wmt-v2/wmtarchivedisplay.php');
} else {
	include_once("../../forms/$frmdir/common.php");
}

?>
