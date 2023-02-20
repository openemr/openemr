<?php
function ext_exam2_report( $pid, $encounter, $cols, $id, $create=false) {
  include_once('../../globals.php');
	$frmdir = 'ext_exam2';
	$frmn = 'form_'.$frmdir;
  include($GLOBALS['srcdir'].'/wmt-v2/report_setup.inc.php');
  include_once($GLOBALS['srcdir'].'/wmt-v2/ee1form.inc');

	if(!$create) include($GLOBALS['srcdir'].'/wmt-v2/report_header.inc.php');
?>

<body>
<?php
	include($GLOBALS['srcdir'].'/wmt-v2/report_body.inc.php');
	if(!$create) include($GLOBALS['srcdir'].'/wmt-v2/report_signatures.inc.php');
?>
</body> 
<?php if(!$create) echo '</html>'; ?>

<?php } ?>
