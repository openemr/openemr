<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../interface/globals.php");
require_once("../library/wmt-v2/approve.inc");
$success=false;
$usr='';
$err='';
if(isset($_GET['username'])) { $usr=$_GET['username']; }
if(isset($_GET['success'])) { $success=$_GET['success']; }
if(isset($_GET['err'])) { $err=$_GET['err']; }
$form_action="pin_check_popup.php?username=".$usr."&success=".$success;
$dispname=getApprovalUserName($usr);
$pin_check=loadUserPin($usr);

?>

<html>
<head>

<title><?php xl('','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script language="JavaScript">

function set_pin(success) {
	if(!success) {
		return false;
	}
  if (opener.closed || ! opener.set_pin)
   alert('The destination form was closed; I cannot act on your selection.');
  else
   opener.set_pin(success);
  window.close();
  return false;
}

function pin_check(test) {
	var pin = document.forms[0].elements['pin'].value;
	if(pin == '') {
		alert("Please Enter a PIN!");
		return false;
	}
	if(test == '') {
		alert("Can NOT Verify, Internal Error");
		return false;
	}
	if(test == pin) {
		set_pin(true);
		return true;
	}
	alert("Incorrect PIN...");
	return false;
}

</script>

</head>

<body class="body_top" onLoad="set_pin('<?php echo $success; ?>'); document.forms[0].elements['pin'].focus();" >
<form method='post' name='addform' action="<?php echo $form_action; ?>">
<center>

<table border='0' cellpadding='5' cellspacing='0'>
 <tr>
  <td height="1">
  </td>
 </tr>
 <tr>
    <td><b>Enter the PIN for <?php echo $dispname; ?></b></td>
 </tr>
 <tr>
  <td height="1">
  </td>
<?php
if($err) {
 echo " <tr>\n";
 echo "  <td height='1'>\n";
 echo "  </td>\n";
 echo " </tr>\n";
 echo " <tr>\n";
 echo "    <td style='color: red'><b>$err</b></td>\n";
 echo " </tr>\n";
 echo " <tr>\n";
 echo "  <td height='1'>\n";
 echo "  </td>\n";
}
?>
</table>

<table border='0' cellpadding='4'>
  <tr>
    <td>PIN:</td>
    <td><input name="pin" id="pin" type="password" class="form-control" value="" /></td>
  </tr>
</table>

<table border='0' cellpadding='4'>
	<tr>
		<td><a href="javascript:;" class="css_button btn btn-primary" onclick="pin_check('<?php echo $pin_check; ?>');"><span>Verify</span></a></td>
		<td><a href="javascript:window.close();" class="css_button btn btn-primary"><span>Cancel</span></a></td>
	</tr>
</table>
</center>
</form>
</body>
</html>
