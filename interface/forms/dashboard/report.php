<?php
function dashboard_report( $pid, $encounter, $cols, $id, $create=false) {
  include_once('../../globals.php');
	$frmdir = 'dashboard';
	$frmn = 'form_'.$frmdir;
  include($GLOBALS['srcdir'].'/wmt-v2/report_setup.inc.php');
  include($GLOBALS['srcdir'].'/wmt-v2/pap_track.inc');

	if(!$create) {
		include($GLOBALS['srcdir'].'/wmt-v2/report_header.inc.php');
	} else {
?>
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtreport.css" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtreport.bkk.css" type="text/css">
<?php } ?>

<body>
<?php
	$dt['tmp_diag_window_mode'] = 'current';
	include($GLOBALS['srcdir'].'/wmt-v2/report_body.inc.php');
	if(!$create) include($GLOBALS['srcdir'].'/wmt-v2/report_signatures.inc.php');
?>
</body> 
<?php if(!$create) echo '</html>'; ?>

<?php } ?>
