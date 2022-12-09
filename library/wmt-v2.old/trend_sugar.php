<?php
include_once('../../interface/globals.php');
include_once($GLOBALS['srcdir'].'/translation.inc.php');
if(!isset($_GET['pid'])) $_GET['pid'] = '';
$pid = $_GET['pid'];
$href = $GLOBALS['webroot'].'/interface/forms/blood_sugar/new.php?pid=' .
	$pid . '&mode=view';
?>
<html>
<head>
<script type="text/javascript">
window.location = '<?php echo $href; ?>';
</script>
</head>

</html>
