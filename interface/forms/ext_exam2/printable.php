<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<?php
if(isset($_GET['suppress'])) $ignoreAuth = TRUE;
include_once("../../globals.php");
$frmdir = 'ext_exam2';
$frmn = 'form_'.$frmdir;
include_once($GLOBALS['srcdir'].'/wmt-v2/ee1form.inc');
include($GLOBALS['srcdir'].'/wmt-v2/print_setup.inc.php');


if(strtolower($dt{'form_complete'}) == 'a') {
	$content = GetFormFromRepository($pid, $encounter, $id, $frmn);
}
?>

<html>
<?php include($GLOBALS['srcdir'].'/wmt-v2/printHeader.wmt.php'); ?>
<body>

<?php
include($GLOBALS['srcdir'].'/wmt-v2/print_body.inc.php');

include($GLOBALS['srcdir'].'/wmt-v2/print_form_footer.inc.php');
?>

</html>
