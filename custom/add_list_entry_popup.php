<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once('../interface/globals.php');
require_once($GLOBALS['srcdir'].'/sql.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');

use OpenEMR\Core\Header;

$form_mode = 'add';
$item_field = $list = '';
$lid = $name = $err = '';
$choose_existing = $success = false;
$allow_add = true;
$prompt = 'an Issue Type';
$lbl_type = 'Issue Type';
if(!isset($_POST['item_name'])) $_POST['item_name'] = '';
if(isset($_GET['thisItem'])) $item_field = strip_tags($_GET['thisItem']);
if(isset($_GET['thisList'])) $list = strip_tags($_GET['thisList']);
if(isset($_GET['mode'])) $form_mode = strip_tags($_GET['mode']);
if(isset($_REQUEST['choose'])) $choose_existing = strip_tags($_REQUEST['choose']);
if(!isset($_GET['add'])) $_GET['add'] = '';
if($_GET['add'] == 'false') $allow_add = false;
if(isset($_GET['list_id'])) $lid = strip_tags($_GET['list_id']);
if(isset($_GET['item_name'])) {
	$name = strip_tags($_GET['item_name']);
} else $name = strip_tags($_POST['item_name']);
if(isset($_REQUEST['prompt'])) $prompt = strip_tags($_REQUEST['prompt']);
if(isset($_REQUEST['lbl_type'])) $lbl_type = strip_tags($_REQUEST['lbl_type']);
$form_action = "add_list_entry_popup.php?thisItem=$item_field&thisList=$list";
$form_action .= "&lbl_type=$lbl_type&prompt=$prompt";

$options = LoadList($list);

function list_sel($thisField, $thisList, $empty_label = '') {
  echo "<option value=''";
  if($thisField == '') echo " selected='selected'";
  echo ">$empty_label&nbsp;</option>";
  foreach ($thisList as $row) {
    echo "<option value='" . $row{'option_id'} . "'";
    if($thisField == $row{'option_id'}) {
			echo " selected='selected'";
		} else if(empty($thisField)) {
			if($row{'is_default'} == 1) echo " selected='selected'";
		}
    echo ">" . htmlspecialchars($row{'title'}, ENT_NOQUOTES);
    echo "</option>";
  }
}

if($form_mode == 'save') {
	$name = trim($name);
	if($name && $name != '') {
		$exists=false;
		// FIRST MAKE SURE THIS ISN'T IN THE LIST ALREADY
		$sql = 'SELECT * FROM list_options WHERE list_id=? AND title=?';
		$fres = sqlStatement($sql, array($list, $name));
		while($frow = sqlFetchArray($fres)) {
			if($frow{'title'} == $name) { 
				$exists = true;
				$err = 'That entry is already in this list';
			}
		} 
		if(!$exists) {
			// NOW WE HAVE TO GENERATE A CODE FOR THIS ITEM THAT IS NOT IN USE
			$tmp = explode(' ', $name);
			foreach($tmp as $key => $val) {
				$lid .= strtolower(substr($val,0,3));
			}
			$exists = GetListTitleByKey($lid, $list);
			$test = $lid;
			$cnt = 0; 
			while($exists) {
				$test = $lid . '_' . $cnt;
				$exists = GetListTitleByKey($test, $list);
				$cnt++;
			}
			$lid = $test;
			$options = array('list_id' => $list,
					'option_id' => $lid,
					'title' => $name);
			AddToList($options);
			$success = true;
			$err = 'List Addition Successful';
		 }	
	}	else {
		$err = 'No entry given, no action taken';
	}
} else if($form_mode == 'choose') {
	$success = true;
}
?>

<html>
<head>
<title>&nbsp;</title>

<?php Header::setupHeader(['opener', 'jquery', 'jquery-ui']); ?>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
div { font-size:10pt; }
</style>

<script type="text/javascript">

function set_item(newId,newItem,itemField,success) {
	if(!success) {
		// alert('In the  no success part');
<?php if($choose_existing) { ?>
		document.forms[0].elements['list_choice'].focus();
<?php } else { ?>
		document.forms[0].elements['item_name'].focus();
<?php } ?>
		return false;
	}
  if (opener.closed || ! opener.set_item) {
   alert('The destination form was closed; I cannot act on your selection.');
  } else {
   opener.set_item(newId,newItem,itemField);
	}
  window.close();
  return false;
}

function submit_form(mode) {
	var myAction = document.forms[0].action;
	myAction += '&mode='+mode
  if(mode == 'choose') {
		var s = document.getElementById('list_choice');
		var lid = s.options[s.selectedIndex].value;
		var title = s.options[s.selectedIndex].text;
		myAction += '&choose=true&list_id='+lid+'&item_name='+title;
  } else {
	}
	document.forms[0].action = myAction;
  document.forms[0].submit();
}

</script>
</head>

<body class="body_top" onLoad='set_item("<?php echo $lid;?>","<?php echo $name;?>","<?php echo $item_field;?>","<?php echo $success; ?>");' >
<form method='post' name='addform' action="<?php echo $form_action; ?>">
<center>
<?php if($choose_existing) { ?>
<table border='0' cellpadding='5' cellspacing='0'>
 <tr><td height="1"></td></tr>
 <tr>
	<td style='text-align: center;'><b>Choose <?php echo $prompt; ?></b></td>
 </tr>
 <tr>
	<td style='text-align: center;'><select name="list_choice" id="list_choice" class="form-control">
		<?php list_sel('', $options); ?>
	</select></td>
 </tr>
</table>
<div style='text-align: center;'>
<div style='display: inline-block;'><a href="javascript:submit_form('choose');" class="css_button"><span>Choose This <?php echo $lbl_type; ?></span></a></div>
<br>
	<?php if($allow_add) { ?>
	<br>
	<div style='display: inline-block;'><b> - or - </b></div>
	<?php } ?>
</div>
<?php } ?>

<?php if($allow_add) { ?>
	<table border='0' cellpadding='5' cellspacing='0'>
 	<tr><td height="1"></td></tr>
 	<tr><td><b>Add <?php echo $prompt; ?></b></td></tr>
	<?php
	if($err) {
 	echo " <tr><td height='1'></td></tr>\n";
 	echo " <tr>\n";
 	echo "    <td style='color: red'><b>$err</b></td>\n";
 	echo " </tr>\n";
 	echo " <tr><td height='1'></td></tr>\n";
	}
	?>
	</table>
	
	<table border='0' cellpadding='4'>
  	<tr>
    	<td>New <?php echo $lbl_type; ?>:</td>
    	<td><input name="item_name" id="item_name" type="text" class="form-control" value="<?php echo $name; ?>" /></td>
  	</tr>
	</table>
	
	<table border='0' cellpadding='4'>
		<tr>
			<td><a href="javascript:submit_form('save');" class="css_button btn btn-primary"><span>Save This <?php echo $lbl_type; ?></span></a></td>
			<td><a href="javascript:window.close();" class="css_button btn btn-secondary"><span>Cancel</span></a></td>
		</tr>
	</table>
<?php } ?>
</center>
</form>
</body>
</html>
