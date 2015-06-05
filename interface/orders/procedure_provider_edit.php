<?php
/**
* Maintenance for the list of procedure providers.
*
* Copyright (C) 2012-2014 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
*/

$sanitize_all_escapes = true;
$fake_register_globals =false;

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/htmlspecialchars.inc.php");

// Collect user id if editing entry
$ppid = $_REQUEST['ppid'];

$info_msg = "";

function invalue($name) {
  $fld = add_escape_custom(trim($_POST[$name]));
  return "'$fld'";
}

?>
<html>
<head>
<title><?php echo $ppid ? xlt('Edit') : xlt('Add New') ?> <?php echo xlt('Procedure Provider'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<style>
td { font-size:10pt; }

.inputtext {
 padding-left:2px;
 padding-right:2px;
}

.button {
 font-family:sans-serif;
 font-size:9pt;
 font-weight:bold;
}
</style>

<script language="JavaScript">
</script>

</head>

<body class="body_top">
<?php
// If we are saving, then save and close the window.
//
if ($_POST['form_save']) {
  $sets =
    "name = "         . invalue('form_name')         . ", " .
    "npi = "          . invalue('form_npi')          . ", " .
    "send_app_id = "  . invalue('form_send_app_id')  . ", " .
    "send_fac_id = "  . invalue('form_send_fac_id')  . ", " .
    "recv_app_id = "  . invalue('form_recv_app_id')  . ", " .
    "recv_fac_id = "  . invalue('form_recv_fac_id')  . ", " .
    "DorP = "         . invalue('form_DorP')         . ", " .
    "direction = "    . invalue('form_direction')    . ", " .
    "protocol = "     . invalue('form_protocol')     . ", " .
    "remote_host = "  . invalue('form_remote_host')  . ", " .
    "login = "        . invalue('form_login')        . ", " .
    "password = "     . invalue('form_password')     . ", " .
    "orders_path = "  . invalue('form_orders_path')  . ", " .
    "results_path = " . invalue('form_results_path') . ", " .
    "notes = "        . invalue('form_notes');
  if ($ppid) {
    $query = "UPDATE procedure_providers SET $sets " .
      "WHERE ppid = '"  . add_escape_custom($ppid) . "'";
    sqlStatement($query);
  }
  else {
    $ppid = sqlInsert("INSERT INTO procedure_providers SET $sets");
  }
}
else if ($_POST['form_delete']) {
  if ($ppid) {
    sqlStatement("DELETE FROM procedure_providers WHERE ppid = ?", array($ppid));
  }
}

if ($_POST['form_save'] || $_POST['form_delete']) {
  // Close this window and redisplay the updated list.
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('" . addslashes($info_msg) . "');\n";
  echo " window.close();\n";
  echo " if (opener.refreshme) opener.refreshme();\n";
  echo "</script></body></html>\n";
  exit();
}

if ($ppid) {
  $row = sqlQuery("SELECT * FROM procedure_providers WHERE ppid = ?", array($ppid));
}
?>

<form method='post' name='theform' action='procedure_provider_edit.php?ppid=<?php echo attr($ppid) ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td nowrap><b><?php echo xlt('Name'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_name' maxlength='255'
    value='<?php echo attr($row['name']); ?>'
    style='width:100%' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('NPI'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_npi' maxlength='10'
    value='<?php echo attr($row['npi']); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Sender IDs'); ?>:</b></td>
  <td>
   <?php echo xlt('Application'); ?>:
   <input type='text' size='10' name='form_send_app_id' maxlength='100'
    value='<?php echo attr($row['send_app_id']); ?>'
    title='<?php echo xla('MSH-3.1'); ?>'
    class='inputtext' />
   &nbsp;<?php echo xlt('Facility'); ?>:
   <input type='text' size='10' name='form_send_fac_id' maxlength='100'
    value='<?php echo attr($row['send_fac_id']); ?>'
    title='<?php echo xla('MSH-4.1'); ?>'
    class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Receiver IDs'); ?>:</b></td>
  <td>
   <?php echo xlt('Application'); ?>:
   <input type='text' size='10' name='form_recv_app_id' maxlength='100'
    value='<?php echo attr($row['recv_app_id']); ?>'
    title='<?php echo xla('MSH-5.1'); ?>'
    class='inputtext' />
   &nbsp;<?php echo xlt('Facility'); ?>:
   <input type='text' size='10' name='form_recv_fac_id' maxlength='100'
    value='<?php echo attr($row['recv_fac_id']); ?>'
    title='<?php echo xla('MSH-6.1'); ?>'
    class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Usage'); ?>:</b></td>
  <td>
   <select name='form_DorP' title='<?php echo xla('MSH-11'); ?>'>
<?php
foreach(array(
  'D' => xl('Debugging'),
  'P' => xl('Production'),
  ) as $key => $value)
{
  echo "    <option value='" . attr($key) . "'";
  if ($key == $row['DorP']) echo " selected";
  echo ">" . text($value) . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Protocol'); ?>:</b></td>
  <td>
   <select name='form_protocol'>
<?php
foreach(array(
  // Add to this list as more protocols are supported.
  'DL'   => xl('Download'),
  'SFTP' => xl('SFTP'),
  'FS'   => xl('Local Filesystem'),
  ) as $key => $value)
{
  echo "    <option value='" . attr($key) . "'";
  if ($key == $row['protocol']) echo " selected";
  echo ">" . text($value) . "</option>\n";
}
?>
   </select>
   &nbsp;
   <select name='form_direction'>
<?php
foreach(array(
  'B' => xl('Bidirectional'),
  'R' => xl('Results Only'),
  ) as $key => $value)
{
  echo "    <option value='" . attr($key) . "'";
  if ($key == $row['direction']) echo " selected";
  echo ">" . text($value) . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Remote Host'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_remote_host' maxlength='255'
    value='<?php echo attr($row['remote_host']); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Login'); ?>:</b></td>
  <td>
   <input type='text' size='20' name='form_login' maxlength='255'
    value='<?php echo attr($row['login']); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Password'); ?>:</b></td>
  <td>
   <input type='text' size='20' name='form_password' maxlength='255'
    value='<?php echo attr($row['password']); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Orders Path'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_orders_path' maxlength='255'
    value='<?php echo attr($row['orders_path']); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Results Path'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_results_path' maxlength='255'
    value='<?php echo attr($row['results_path']); ?>' class='inputtext' />
  </td>
 </tr>

 <tr>
  <td nowrap><b><?php echo xlt('Notes'); ?>:</b></td>
  <td>
   <textarea rows='3' cols='40' name='form_notes' style='width:100%'
    wrap='virtual' class='inputtext' /><?php echo text($row['notes']) ?></textarea>
  </td>
 </tr>

</table>

<br />

<input type='submit' name='form_save' value='<?php echo xla('Save'); ?>' />

<?php if ($ppid) { ?>
&nbsp;
<input type='submit' name='form_delete' value='<?php echo xla('Delete'); ?>' style='color:red' />
<?php } ?>

&nbsp;
<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />
</p>

</center>
</form>
</body>
</html>
