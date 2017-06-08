<?php
/**
 * Edit Layout Properties.
 *
 * Copyright (C) 2016-2017 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @link    http://www.open-emr.org
 */




require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$phpgacl_location/gacl_api.class.php");

$info_msg = "";

// Check authorization.
$thisauth = acl_check('admin', 'super');
if (!$thisauth) die(xlt('Not authorized'));

$opt_line_no = intval($_GET['lineno']);
?>
<html>
<head>
<?php html_header_show();?>
<title><?php echo xlt("Edit Layout Properties"); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="../../library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>

<script language="JavaScript">

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
var target = opener.document.forms[0]['opt[<?php echo $opt_line_no; ?>][notes]'];

$(document).ready(function () {
	var jobj = {};
	if (target.value.length) {
		try {
			jobj = JSON.parse(target.value);
			$("#fm_props").find('input,select').each(function() {
				var fm_prop = $(this).prop('name').slice(5);
				if ((typeof(jobj[fm_prop]) !== 'undefined') && (fm_prop !== '')) {
					$(this).val(jobj[fm_prop]);
				}
			});
		}
		catch (e) {
			alert('<?php echo xls('Invalid data, will be ignored and replaced.'); ?>');
		}
	}
});

// Onclick handler for Submit button.
function submitProps() {
	var jobj = {};
	$("#fm_props").find('input,select').each(function() {
		var fm_prop = $(this).prop('name').slice(5);
		if (($(this).val() !== '') && (fm_prop !== '')) {
			jobj[fm_prop] = $(this).val();
		}
	});
	target.value = ((Object.keys(jobj).length > 0) ? JSON.stringify(jobj) : '');
	window.close();
}

</script>

</head>

<body class="body_top">

<form id='fm_props' method='post'>
<center>

<table border='0' width='100%'>

 <tr>
  <td valign='top' nowrap>
   <?php echo xlt('Layout Columns'); ?>
  </td>
  <td>
   <select name='form_columns'>
<?php
  echo "<option value=''>" . xlt('Default') . " (4)</option>\n";
  for ($cols = 2; $cols <= 10; ++$cols) {
  	if ($cols != 4) {
    	echo "<option value='$cols'>$cols</option>\n";
	}
  }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap>
   <?php echo xlt('Font Size'); ?>
  </td>
  <td>
   <select name='form_size'>
<?php
  echo "<option value=''>" . xlt('Default') . "</option>\n";
  for ($size = 5; $size <= 15; ++$size) {
    echo "<option value='$size'";
    echo ">$size</option>\n";
  }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap>
   <?php echo xlt('Access Control'); ?>
  </td>
  <td>
   <select name='form_aco'>
    <option value=''></option>
    <?php echo gen_aco_html_options(); ?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap>
   <label for='form_category'><?php echo xlt('Category'); ?></label>
  </td>
  <td>
   <input type="text" id='form_category' name='form_category' size="40">
  </td>
 </tr>

</table>

<p>
<input type='button' value='<?php echo xla('Submit'); ?>' onclick='submitProps()' />

&nbsp;
<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />
</p>

</center>
</form>
<script language='JavaScript'>
<?php
if ($info_msg) {
  echo " alert('".addslashes($info_msg)."');\n";
  echo " window.close();\n";
}
?>
</script>
</body>
</html>
