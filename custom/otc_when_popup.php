<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../interface/globals.php");
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
$item = $val = $list = '';
$success = false;
if(isset($_GET['extra'])) $val = strip_tags($_GET['extra']);
if(isset($_GET['item'])) $item = strip_tags($_GET['item']);
if(isset($_GET['list'])) $list = strip_tags($_GET['list']);
$form_action = "otc_when_popup.php?extra=$extra&item=$item&list=$list";
$choices = LoadList($list);
$form_choices = array();
if($extra) $form_choices = explode('^|', $extra);
?>

<html>
<head>
<title><?php xl('Medication Time Selections','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script type="text/javascript">

function set_selection() {
	if (opener.closed || !opener.set_item) {
		alert('The destination form was closed; I cannot act on your selection.');
	} else {
		var i;
		var data = '';
		var display = '';
		var tmp = '';
		var l = document.forms[0].elements.length;		
		for(i=0; i < l; i++) {
			if(document.forms[0].elements[i].name.indexOf('chc_') != -1) {
				if(document.forms[0].elements[i].type.indexOf('check') != -1) {
					if(document.forms[0].elements[i].checked == true) {
						if(data != '') data += '^|';
						data += document.forms[0].elements[i].value;
						tmp = document.forms[0].elements[i].name;
						if(display != '') display += ', ';
						display += document.getElementById(tmp + '_lbl').innerHTML;
					}
				}
			}
		}
		
		opener.set_when('<?php echo $item; ?>', data, display);
		window.close();
		return false;
	}
}

</script>
</head>

<body class="body_top">
<form method='post' name='wot_when_form' action="<?php echo $form_action; ?>">
<center>

<table border='0' cellpadding='5' cellspacing='0'>
 <tr>
  <td height="1">
  </td>
 </tr>
 <tr>
    <td><b>Select the Desired Times and Save</b></td>
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
		<td>Time(s) Taken:</td>
	</tr>
	<tr>
		<td>
		<?php 
		foreach($choices as $chc) {
			$lbl = ucfirst(str_replace('_', ' ', $chc['option_id']));
		?>
			<input name="<?php echo 'chc_'.$chc['option_id']; ?>" id="<?php echo 'chc_'.$chc['option_id']; ?>" type="checkbox" value="<?php echo $chc['option_id']; ?>" <?php echo in_array($chc['option_id'], $form_choices) ? 'checked="checked"' : ''; ?> title="" />&nbsp;<label for="<?php echo 'chc_'.$chc['option_id']; ?>" id="<?php echo 'chc_'.$chc['option_id'].'_lbl'; ?>"><?php echo htmlspecialchars($lbl, ENT_QUOTES); ?></label><br>
		<?php } ?>
		</td>
  </tr>
</table>

<table border='0' cellpadding='4'>
	<tr>
		<td><a href="javascript: set_selection();;" class="css_button btn btn-primary"><span>Save</span></a></td>
		<td><a href="javascript:window.close();" class="css_button btn btn-primary"><span>Cancel</span></a></td>
	</tr>
</table>
</center>
</form>
</body>
</html>
