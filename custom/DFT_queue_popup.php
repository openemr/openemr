<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once('../interface/globals.php');

use OpenEMR\Core\Header;

$success = false;
if(isset($_GET['success'])) $success = strip_tags($_GET['success']);
$form_action = 'DFT_queue_popup.php?success=' . $success;

$pid = $SESSION['pid'];
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
$encounter = $SESSION['encounter'];
if(isset($_GET['enc'])) $encounter = strip_tags($_GET['enc']);

$err = '';
if(!$pid || !$encounter) $err = 'Missing either a patient or encounter....'.
	'Can NOT Process!';

$onload = '';
if(!$err) {
	$sql = 'SELECT * FROM `billing` WHERE `pid`=? AND `encounter`=?';
	$items = sqlStatement($sql, array($pid, $encounter));
	$upd = 'INSERT INTO `hl7_queue` (`hl7_msg_group`, `hl7_msg_type`, '.
		'`oemr_table`, `oemr_ref_id`, `flag`, `processed`) VALUES '.
		'("DFT", "P03", ?, ?, ?, 0) ON DUPLICATE KEY UPDATE '.
		'`processed` = 0';
	while($item = sqlFetchArray($items)) {
		sqlStatement($upd, array('billing', $item{'id'}, $pid));	
	}
	// THEN GET THE OVERALL ENCOUNTER RECORD	
	sqlStatement($upd, array('form_encounter',$encounter, $pid));	
	$onload="delayed_close();";
	$onload="dlgclose();";
} 
?>

<html>
<head>
<title><?php xl('','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<?php Header::setupHeader(['common','dialog','opener']); ?>

<style>
td { font-size:10pt; }
</style>

<script type="text/javascript">

function self_close() {
  dlgclose();
  return true;
}

function delayed_close()
{
	window.setTimeout("self_close()", 1000);
	return true;
}

</script>
</head>

<body class="body_top" onLoad="<?php echo $onload; ?>" >
<form method='post' name='addform' action="<?php echo $form_action; ?>">
<center>

<table border='0' cellpadding='5' cellspacing='0'>
 <tr><td height="3"></td></tr>
<?php
if($err) {
?>
 <tr>
    <td style="color: red;"><b><?php echo $err; ?></b></td>
 </tr>
<?php } else { ?>
 <tr>
    <td><b>Encounter (<?php echo $encounter; ?>) for PID [<?php echo $pid; ?>] Queued</b></td>
 </tr>
 </tr>
<?php } ?>
 <tr><td height="3"></td></tr>
</table>


<table border='0' cellpadding='4'>
	<tr>
		<td><a href="javascript:dlgclose();" class="css_button btn btn-primary"><span>Close</span></a></td>
	</tr>
</table>
</center>
</form>
</body>
</html>
