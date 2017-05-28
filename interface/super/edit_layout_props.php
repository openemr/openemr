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
  var f = document.forms[0];
  var jobj = {};
  if (target.value.length) {
    try {
      jobj = JSON.parse(target.value);
    }
    catch (e) {
      alert('<?php echo xls('Invalid data, will be ignored and replaced.'); ?>');
    }
  }
  if (jobj['size'    ]) f.form_size.value     = jobj['size'];
  if (jobj['columns' ]) f.form_columns.value  = jobj['columns'];
  if (jobj['aco'     ]) f.form_aco.value      = jobj['aco'];
});

// Onclick handler for Submit button.
function submitProps() {
  var f = document.forms[0];
  var jobj = {};
  if (f.form_size.value          ) jobj['size'    ] = f.form_size.value;
  if (f.form_columns.value != '4') jobj['columns' ] = f.form_columns.value;
  if (f.form_aco.value           ) jobj['aco'     ] = f.form_aco.value;
  target.value = JSON.stringify(jobj);
  window.close();
}

</script>

</head>

<body class="body_top">

<form method='post'>
<center>

<table border='0' width='100%'>

 <tr>
  <td valign='top' nowrap>
   <?php echo xlt('Layout Columns'); ?>
  </td>
  <td>
   <select name='form_columns'>
<?php
  for ($cols = 2; $cols <= 10; ++$cols) {
    echo "<option value='$cols'";
    if ($cols == 4) echo " selected";
    echo ">$cols</option>\n";
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
