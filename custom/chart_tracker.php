<?php
/**
 * This feature requires a new list:
 * <pre>
 *   INSERT INTO list_options VALUES ('lists','chartloc','Chart Storage Locations',51,0,0);
 * </pre>
 *
 * Copyright (C) 2008-2012 Rod Roark <rod@sunsetsystems.com>
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
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../interface/globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");

$form_newid   = isset($_POST['form_newid'  ]) ? trim($_POST['form_newid'  ]) : '';
$form_curpid  = isset($_POST['form_curpid' ]) ? trim($_POST['form_curpid' ]) : '';
$form_curid   = isset($_POST['form_curid'  ]) ? trim($_POST['form_curid'  ]) : '';
$form_newloc  = isset($_POST['form_newloc' ]) ? trim($_POST['form_newloc' ]) : '';
$form_newuser = isset($_POST['form_newuser']) ? trim($_POST['form_newuser']) : '';

if ($form_newuser) $form_newloc = ''; else $form_newuser = 0;
?>
<html>

<head>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<title><?php echo xlt('Chart Tracker'); ?></title>

<script language="JavaScript">

function locationSelect() {
 var f = document.forms[0];
 var i = f.form_newloc.selectedIndex;
 if (i > 0) {
  f.form_newuser.selectedIndex = 0;
 }
}

function userSelect() {
 var f = document.forms[0];
 var i = f.form_newuser.selectedIndex;
 if (i > 0) {
  f.form_newloc.selectedIndex = 0;
 }
}

</script>

</head>

<body class="body_top">

<?php
echo "<span class='title'>" . xlt('Chart Tracker') . "</span>\n";
?>

<center>
&nbsp;<br />
<form method='post' action='chart_tracker.php' onsubmit='return top.restoreSession()'>

<?php
// This is the place for status messages.

if ($form_newloc || $form_newuser) {
  $query = "INSERT INTO chart_tracker ( " .
    "ct_pid, ct_when, ct_userid, ct_location " .
    ") VALUES ( " .
    "?, " .
    "?, " .
    "?, " .
    "? " .
    ")";
  sqlInsert($query, array($form_curpid,date('Y-m-d H:i:s'),$form_newuser,$form_newloc) );
  echo "<font color='green'>" . xlt('Save Successful for chart ID') . " " . "'" . text($form_curid) . "'.</font><br />";
}

$row = array();

if ($form_newid) {
  // Find out where the chart is now.
  $query = "SELECT pd.pid, pd.pubpid, pd.fname, pd.mname, pd.lname, " .
    "pd.ss, pd.DOB, ct.ct_userid, ct.ct_location, ct.ct_when " .
    "FROM patient_data AS pd " .
    "LEFT OUTER JOIN chart_tracker AS ct ON ct.ct_pid = pd.pid " .
    "WHERE pd.pubpid = ? " .
    "ORDER BY pd.pid ASC, ct.ct_when DESC LIMIT 1";
  $row = sqlQuery($query, array($form_newid) );
  if (empty($row)) {
    echo "<font color='red'>" . xlt('Chart ID') . " " . "'" . text($form_newid) . "' " . xlt('not found') . "!</font><br />";
  }
}
?>

<table>

<?php
if (!empty($row)) {
  $ct_userid   = $row['ct_userid'];
  $ct_location = $row['ct_location'];
  $current_location = xlt('Unassigned');
  if ($ct_userid) {
    $urow = sqlQuery("SELECT fname, mname, lname FROM users WHERE id = ?", array($ct_userid) );
    $current_location = text( $urow['lname'] . ", " . $urow['fname'] . " " . $urow['mname'] . " " . $row['ct_when'] );
  }
  else if ($ct_location) {
    $current_location = generate_display_field(array('data_type'=>'1','list_id'=>'chartloc'),$ct_location);  
  }

  echo " <tr>\n";
  echo "  <td class='bold'>" . xlt('Patient ID') . ":</td>\n";
  echo "  <td class='text'>" . text($row['pubpid']) .
       "<input type='hidden' name='form_curpid' value='" . attr($row['pid']) . "' />" .
       "<input type='hidden' name='form_curid' value='" . attr($row['pubpid']) . "' /></td>\n";
  echo " </tr>\n";

  echo " <tr>\n";
  echo "  <td class='bold'>" . xlt('Name') . ":</td>\n";
  echo "  <td class='text'>" . text( $row['lname'] . ", " . $row['fname'] . " " . $row['mname'] ) . "</td>\n";
  echo " </tr>\n";

  echo " <tr>\n";
  echo "  <td class='bold'>" . xlt('DOB') . ":</td>\n";
  echo "  <td class='text'>" . text($row['DOB']) . "</td>\n";
  echo " </tr>\n";

  echo " <tr>\n";
  echo "  <td class='bold'>" . xlt('SSN') . ":</td>\n";
  echo "  <td class='text'>" . text($row['ss']) . "</td>\n";
  echo " </tr>\n";

  echo " <tr>\n";
  echo "  <td class='bold'>" . xlt('Current Location') . ":</td>\n";
  // Note that $current_location has already been html escaped
  echo "  <td class='text'>$current_location</td>\n";
  echo " </tr>\n";

  echo " <tr>\n";
  echo "  <td class='bold'>" . xlt('Check In To') . ":</td>\n";
  echo " <td class='text'>";
  generate_form_field(array('data_type'=>1,'field_id'=>'newloc','list_id'=>'chartloc','empty_title'=>''), '');
  echo " </td>\n";
  echo " </tr>\n";

  echo " <tr>\n";
  echo "  <td class='bold'>" . xlt('Or Out To') . ":</td>\n";
  echo "  <td class='text'><select name='form_newuser' onchange='userSelect()'>\n";
  echo "   <option value=''></option>";
  $ures = sqlStatement("SELECT id, fname, mname, lname FROM users " .
    "WHERE username != '' AND active = 1 ORDER BY lname, fname, mname");
  while ($urow = sqlFetchArray($ures)) {
    echo "    <option value='" . attr($urow['id']) . "'";
    echo ">" . text($urow['lname']) . ', ' . text($urow['fname']) . ' ' . text($urow['mname']) .
      "</option>\n";
  }
  echo "  </select></td>\n";
  echo " </tr>\n";

  echo " <tr>\n";
  echo "  <td>&nbsp;</td>\n";
  echo "  <td class='text'><input type='submit' name='form_save' value='" . xlt('Save') . "' /></td>\n";
  echo " </tr>\n";

  echo " <tr>\n";
  echo "  <td class='text' colspan='2'>&nbsp;</td>\n";
  echo " </tr>\n";
}
?>

 <tr>
  <td class='bold'>
   <?php echo xlt('New Patient ID'); ?>: &nbsp;
  </td>
  <td class='text'>
   <input type='text' name='form_newid' size='10' value=''
    class='inputtext' title='<?php echo xla("Type or scan the patient identifier here") ?>' />
  </td>
 </tr>

 <tr>
  <td class='bold'>&nbsp;</td>
  <td class='text'>
   <input type='submit' class='button' name='form_lookup' value='<?php echo xla("Look Up"); ?>' />
  </td>
 </tr>

</table>

</form>
</center>
</body>
</html>
