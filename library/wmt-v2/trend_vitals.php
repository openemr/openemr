<?php
include_once('../../interface/globals.php');
include_once($GLOBALS['srcdir'].'/translation.inc.php');
$pid = $GLOBALS['pid'];
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
if(!$pid)  {
	echo "<h>No PID - No Vitals to Trend</h><br>\n";
	exit;
}
$GLOBALS['pid'] = $pid;
$href = $GLOBALS['webroot'].'/interface/patient_file/encounter/trend_form.php?formname=vitals&restore=restore';
?>
<html>
<head>
<script type="text/javascript">
window.location = '<?php echo $href; ?>';
</script>
</head>

</html>
