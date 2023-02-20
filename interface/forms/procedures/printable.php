<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<?php
if(isset($_GET['suppress'])) $ignoreAuth = TRUE;
include_once('../../globals.php');
$frmdir = 'procedures';
$frmn = 'form_' . $frmdir;
include($GLOBALS['srcdir'].'/wmt-v2/print_setup.inc.php');
?>

<html>

<?php 
include($GLOBALS['srcdir'].'/wmt-v2/printHeader.bkk.php');
if(strtolower($dt{'form_complete'}) == 'a') {
	echo $content;
} else  {
	include("common_view.php");
}
include($GLOBALS['srcdir'].'/wmt-v2/print_form_footer.inc.php');
?>
</html>
