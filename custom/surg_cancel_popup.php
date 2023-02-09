<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../interface/globals.php");
require_once("../library/api.inc");
include_once("../library/translation.inc.php");
include_once("../library/wmt/wmtstandard.inc");
include_once("../library/wmt/rto.class.php");
$id = $pid = $thisform = '';
if(isset($_SESSION['pid'])) { $pid=$_SESSION['pid']; }
if(isset($_SESSION['encounter'])) { $encounter=$_SESSION['encounter']; }
// echo "After Session Set: $pid - $encounter<br/>\n";
if(isset($_GET['patient_id'])) { $pid = $_GET['patient_id']; }
if(isset($_GET['form'])) { $thisform = 'form_'.$_GET['form']; }
if(isset($_GET['fid'])) { $id = $_GET['fid']; }
// echo "After GET Set: $pid - $encounter<br/>\n";
$success=true;
$err='';
$form_action="surg_cancel_popup.php?&success=".$success;
// echo "Form Directory: $thisform<br>\n";
?>

<html>
<head>
<title><?php xl('Canceling Linked Orders','e'); ?></title>
<link rel="stylesheet" href='<?php echo $GLOBALS['css_header'] ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script type="text/javascript">

function close_refresh(success) {
	// window.opener.location.reload(false);
  window.close();
  return true;
}

</script>

</head>

<form method='post' name='addform' action="<?php echo $form_action; ?>">
<center>
</table>

<table border='0' cellpadding='4'>
  <tr>
    <td><?php xl('Canceling Surgical Case','e'); ?>: <?php xl('Form ID','e'); ?> [<?php echo $id; ?>]  <?php xl('PID','e'); ?> (<?php echo $pid; ?>)</td>
  </tr>
	<?php
	$sql = "SELECT * FROM wmt_rto_links WHERE form_name=? AND form_id=? AND ".
		"pid=?";
	$lres = sqlStatement($sql, array($thisform, $fid, $pid));
	while($lrow = sqlFetchArray($lres)) {
		$rto = wmtRTOData::getRTObyID($lrow{'rto_id'});
		$rto->rto_status = 'x';
		echo "<tr><td>",xl('Canceling RTO ID','e')," [$rto->id] ->$rto->rto_action<td><tr>\n";
		$rto->update();
	}
	$sql = "SELECT id, sc1_case FROM $thisform WHERE id=?";
	$test = sqlStatement($sql, array($fid));
	$data = sqlFetchArray($test);
	if($data{'id'} == $fid) {
		$sql = "UPDATE $thisform SET form_complete='x' WHERE id=?";
		$test = sqlStatement($sql, array($fid));
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "<tr><td>",xl('Canceling Surgical Case','e')," [",$data{'sc1_case'},"] ",xl('For PID','e'),": $pid<td><tr>\n";
	} else {
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "<tr><td>",xl('** WARNING** Could NOT Cancel Surgical Form','e')," [$fid] ",xl('For PID','e'),": $pid<td><tr>\n";
	}
	?>
</table>
<body class="body_top" onLoad='close_refresh("<?php echo $success; ?>");' >
<br/><b><?php xl('Cancel Complete','e'); ?>.....<?php xl('Window Closing','e'); ?></b><br/>
</center>
</form>
</body>
</html>
