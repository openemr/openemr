<?php
function procedures_report( $pid, $encounter, $cols, $id, $create=false) {
  include_once('../../globals.php');
	$frmdir = 'procedures';
	$frmn = 'form_' . $frmdir;

	include($GLOBALS['srcdir'].'/wmt-v2/report_setup.inc.php');
	if(!$create) include($GLOBALS['srcdir'].'/wmt-v2/report_header.inc.php');
?>

<body>
	<?php
	if(strtolower($dt{'form_complete'}) == 'a' && !$create) {
		echo $content;
	} else {
		include("common_view.php");
	}
	if(!$create) include($GLOBALS['srcdir'].'/wmt-v2/report_signatures.inc.php');
	?>

</body>
</html>
<?php } ?>
