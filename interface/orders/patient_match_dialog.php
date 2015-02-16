<?php
/**
 * Patient matching and selection dialog.
 *
 * Copyright (C) 2012-2015 Rod Roark <rod@sunsetsystems.com>
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
 */

$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");

$form_key   = $_REQUEST['key'];
$args = unserialize($form_key);
$form_ss    = preg_replace('/[^0-9]/', '', $args['ss']);
$form_fname = $args['fname'];
$form_lname = $args['lname'];
$form_DOB   = $args['DOB'];
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel=stylesheet href="<?php echo $css_header; ?>" type="text/css">
<style>

#searchResults {
  width: 100%;
  height: 80%;
  overflow: auto;
}
#searchResults table {
  width: 96%;
  border-collapse: collapse;
  background-color: white;
}
#searchResults th {
  background-color: lightgrey;
  font-size: 0.7em;
  text-align: left;
}
#searchResults td {
  font-size: 0.7em;
  border-bottom: 1px solid #eee;
  cursor: hand;
  cursor: pointer;
}

.highlight { 
  background-color: #336699;
  color: white;
}

.oneResult {}

</style>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>
<script language="JavaScript">

$(document).ready(function(){
  $(".oneresult").mouseover(function() {$(this).addClass("highlight");});
  $(".oneresult").mouseout(function() {$(this).removeClass("highlight");});
});

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

function myRestoreSession() {
 if (top.restoreSession) top.restoreSession(); else opener.top.restoreSession();
 return true;
}

function openPatient(ptid) {
 var f = opener.document.forms[0];
 var ename = '<?php echo addslashes("select[$form_key]"); ?>';
 if (f[ename]) {
  f[ename].value = ptid;
  window.close();
 }
 else {
  alert('<?php echo xls('Form element not found'); ?>: ' + ename);
 }
}

</script>
</head>

<body class="body_top">
<center>
<form method='post' action='patient_select.php' onsubmit='return myRestoreSession()'>

<?php
if ($form_key) {
  $clarr = array();
  $clsql = "0";
  // First name.
  if ($form_fname !== '') {
    $clsql .= " + ((fname IS NOT NULL AND fname = ?) * 5)";
    $clarr[] = $form_fname;
  }
  // Last name.
  if ($form_lname !== '') {
    $clsql .= " + ((lname IS NOT NULL AND lname = ?) * 5)";
    $clarr[] = $form_lname;
  }
  // Birth date.
  if ($form_DOB !== '') {
    $clsql .= " + ((DOB IS NOT NULL AND DOB = ?) * 5)";
    $clarr[] = $form_DOB;
  }
  // SSN match is worth a lot and we allow for matching on last 4 digits.
  if (strlen($form_ss) > 3) {
    $clsql .= " + ((ss IS NOT NULL AND ss LIKE ?) * 10)";
    $clarr[] = "%$form_ss";
  }

  $sql = "SELECT $clsql AS closeness, " .
    "pid, pubpid, fname, lname, mname, DOB, ss, postal_code, street, " .
    "phone_biz, phone_home, phone_cell, phone_contact " .
    "FROM patient_data " .
    "ORDER BY closeness DESC, lname, fname LIMIT 10";
  $res = sqlStatement($sql, $clarr);
?>

<div id="searchResults">
 <table>
  <tr>
   <th><?php echo xlt('Name' ); ?></th>
   <th><?php echo xlt('Phone'); ?></th>
   <th><?php echo xlt('SS'   ); ?></th>
   <th><?php echo xlt('DOB'  ); ?></th>
   <th><?php echo xlt('Address'); ?></th>
  </tr>
  <tr>
   <th style='font-weight:normal'><?php echo text("$form_lname, $form_fname"); ?></th>
   <th style='font-weight:normal'><?php echo '&nbsp;'; ?></th>
   <th style='font-weight:normal'><?php echo text($form_ss); ?></th>
   <th style='font-weight:normal'><?php echo text($form_DOB); ?></th>
   <th style='font-weight:normal'><?php echo '&nbsp;'; ?></th>
  </tr>

<?php
  while ($row = sqlFetchArray($res)) {
    if ($row['closeness'] == 0) continue;

    $phone = $row['phone_biz'];
    if (empty($phone)) $phone = $row['phone_home'];
    if (empty($phone)) $phone = $row['phone_cell'];
    if (empty($phone)) $phone = $row['phone_contact'];

    echo "  <tr class='oneresult'";
    echo " onclick=\"openPatient(" .
         "'" . addslashes($row['pid']) . "'"  .
         ")\">\n";
    echo "   <td>" . text($row['lname'] . ", " . $row['fname']) . "</td>\n";
    echo "   <td>" . text($phone        ) . "</td>\n";
    echo "   <td>" . text($row['ss']    ) . "</td>\n";
    echo "   <td>" . text($row['DOB']   ) . "</td>\n";
    echo "   <td>" . text($row['street'] . ' ' . $row['postal_code']) . "</td>\n";
    echo "  </tr>\n";
  }
?>
 </table>
</div>
<?php
}
?>

<p>
<input type='button' value='<?php echo xla('Add New Patient'); ?>' onclick="openPatient(0)" />
&nbsp;
<input type='button' value='<?php echo xla('Cancel'); ?>' onclick="window.close()" />
</p>

</form>
</center>
</body>
</html>
