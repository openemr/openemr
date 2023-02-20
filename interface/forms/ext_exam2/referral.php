<?php
// This function is currently only called during form approval - no mode
// for displaying the referral letter from the live view is supported now.
function ext_exam2_referral( $pid, $encounter, $cols, $id, $create=false) {
  include_once("../../globals.php");
  include_once("../../../library/wmt-v2/wmtprint.inc");
  include_once("../../../library/wmt-v2/wmtstandard.inc");
  include_once("../../../library/wmt-v2/approve.inc");
  include_once("../../../library/wmt-v2/ee1form.inc");
  include_once("../../../library/wmt-v2/fyi.class.php");
  include_once("../../../library/wmt-v2/printvisit.class.php");
  include_once("../../../library/wmt-v2/printfacility.class.php");
  include_once("../../../library/wmt-v2/printpat.class.php");
	$dt=array();
	$rs=array();
	$frmdir = 'ext_exam2';
	$frmn = 'form_'.$frmdir;
	// Initialize the data array so php doesn't error everything!
	$flds = sqlListFields($frmn);
	$flds = array_slice($flds,7);
	foreach($flds as $key => $fld) { $dt[$fld] = ''; }
  $dt = sqlQuery("SELECT * FROM $frmn WHERE id=?", array($id));
	$visit = wmtPrintVisit::getEncounterByForm($id, $frmdir);
	$facility = wmtPrintFacility::getFacility($visit->facility_id);
	$agedt = substr($dt{'form_dt'},0,4).substr($dt{'form_dt'},5,2).
			substr($dt{'form_dt'},8,2);
  $patient=wmtPrintPat::getPatient($pid, $agedt);
?>

<?php if(!$create) { ?>
<html>
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header']; ?>" type="text/css">
<link rel="stylesheet" href="../../../library/wmt/wmtreport.css" type="text/css">
<?php } ?>

<body>
	<?php
	if(strtolower($dt{'form_complete'}) == 'a' && !$create) {
		echo $content;
	} else {
		include('referral_view.php');
	}
	?>

<?php if(!$create) { ?>
<br>
<span class='wmtPrnLabel'><?php echo $visit->signed_by; ?></span><br>
	<?php if($visit->approved_by) { ?>
	<span class='wmtPrnLabel'><?php echo $visit->approved_by; ?></span><br>
	<?php } ?>
<?php } ?>
</body> 
</html>

<?php
}
?>
