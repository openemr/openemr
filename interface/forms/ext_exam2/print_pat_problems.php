<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<?php
include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/sql.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtprint.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/printpat.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');

$frmdir = 'ext_exam2';
$frmn = 'form_'.$frmdir;
$ftitle = 'Patient Problems';
$id = strip_tags($_GET['id']);
$pid = strip_tags($_GET['pid']);
$pop = false;
$encounter = '';
if(isset($GLOBALS['encounter'])) $encounter = $GLOBALS['encounter'];
if(isset($_GET['enc'])) $encounter = strip_tags($_GET['enc']);
if(isset($_GET['pop'])) $pop = strip_tags($_GET['pop']);
$patient = wmtPrintPat::getPatient($pid);
$visit = wmtPrintVisit::getEncounterByForm($id, $frmdir);
if(isset($GLOBALS['wmt::client_id'])) {
	$client_id = $GLOBALS['wmt::client_id'];
} else $client_id = $GLOBALS['wmt::client_id'];
$diag = GetProblemsWithDiags($pid, 'encounter', $encounter);
$include_plans = true;
?>

<html>
<?php include($GLOBALS['srcdir'].'/wmt-v2/printHeader.bkk.php'); ?>
<body>

<?php include($GLOBALS['srcdir'].'/wmt-v2/diagnosis.plain.print.php'); ?>

</body>
</html>
<script type="text/javascript">window.print();</script>
