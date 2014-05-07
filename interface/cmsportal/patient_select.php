<?php
/**
 * Patient matching and selection for the WordPress Patient Portal.
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
require_once("portal.inc.php");

$postid = intval($_REQUEST['postid']);

if ($postid) {
  $result = cms_portal_call(array('action' => 'getpost', 'postid' => $postid));
  if ($result['errmsg']) {
    die(text($result['errmsg']));
  }
}
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
 // This works whether we are a popup or in the OpenEMR frameset.
 myRestoreSession();
 document.location.href = 'patient_form.php?postid=<?php echo xls($postid); ?>&ptid=' + ptid;
}

</script>
</head>

<body class="body_top">
<center>
<form method='post' action='patient_select.php' onsubmit='return myRestoreSession()'>

<?php
// print_r($result); // debugging
if ($postid) {
  $clarr = array();
  $clsql = "0";
  // First name.
  $fname = trim($result['fields']['fname']);
  if ($fname !== '') {
    $clsql .= " + ((fname IS NOT NULL AND fname = ?) * 5)";
    $clarr[] = $fname;
  }
  // Last name.
  $lname = trim($result['fields']['lname']);
  if ($lname !== '') {
    $clsql .= " + ((lname IS NOT NULL AND lname = ?) * 5)";
    $clarr[] = $lname;
  }
  // Birth date.
  $dob = fixDate(trim($result['fields']['dob']), '');
  if ($dob !== '') {
    $clsql .= " + ((DOB IS NOT NULL AND DOB = ?) * 5)";
    $clarr[] = $dob;
  }
  // SSN match is worth a lot and we allow for matching on last 4 digits.
  $ssn = preg_replace('/[^0-9]/', '', $result['fields']['ss']);
  if (strlen($ssn) > 3) {
    $clsql .= " + ((ss IS NOT NULL AND ss LIKE ?) * 10)";
    $clarr[] = "%$ssn";
  }
  // Zip code makes it unnecessary to match on city and state.
  $zip = preg_replace('/[^0-9]/', '', $result['fields']['postal_code']);
  $zip = substr($zip, 0, 5);
  if (strlen($zip) == 5) {
    $clsql .= " + ((postal_code IS NOT NULL AND postal_code LIKE ?) * 2)";
    $clarr[] = "$zip%";
  }
  // This generates a REGEXP query that matches the first 2 words of the street address.
  if (preg_match('/^\W*(\w+)\W+(\w+)/', $result['fields']['street'], $matches)) {
    $clsql .= " + ((street IS NOT NULL AND street REGEXP '^[^[:alnum:]]*";
    $clsql .= $matches[1];
    $clsql .= "[^[:alnum:]]+";
    $clsql .= $matches[2];
    $clsql .= "[[:>:]]') * 2)";
  }

  $sql = "SELECT $clsql AS closeness, " .
    "pid, cmsportal_login, fname, lname, mname, DOB, ss, postal_code, " .
    "street, phone_biz, phone_home, phone_cell, phone_contact " .
    "FROM patient_data " .
    "ORDER BY closeness DESC, lname, fname LIMIT 10";
  $res = sqlStatement($sql, $clarr);

  // echo "<!-- $sql -->\n"; // debugging

  $phone = $result['fields']['phone_biz'];
  if (empty($phone)) $phone = $result['fields']['phone_home'];
  if (empty($phone)) $phone = $result['fields']['phone_cell'];
  if (empty($phone)) $phone = $result['fields']['phone_contact'];
?>

<div id="searchResults">
 <table>
  <tr>
   <th><?php echo xlt('Portal ID'); ?></th>
   <th><?php echo xlt('Name'     ); ?></th>
   <th><?php echo xlt('Phone'    ); ?></th>
   <th><?php echo xlt('SS'       ); ?></th>
   <th><?php echo xlt('DOB'      ); ?></th>
   <th><?php echo xlt('Address'  ); ?></th>
  </tr>
  <tr>
   <th style='font-weight:normal'><?php echo text($result['post']['user']); ?></th>
   <th style='font-weight:normal'><?php echo text("$lname, $fname"); ?></th>
   <th style='font-weight:normal'><?php echo text($phone); ?></th>
   <th style='font-weight:normal'><?php echo text($ssn  ); ?></th>
   <th style='font-weight:normal'><?php echo text($dob  ); ?></th>
   <th style='font-weight:normal'><?php echo text($result['fields']['street'] . ' ' . $zip); ?></th>
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
    echo "   <td";
    if ($row['cmsportal_login'] !== '' && $result['post']['user'] !== $row['cmsportal_login']) {
      echo " style='color:red' title='" . xla('Portal ID does not match request from portal!') . "'";
    }
    echo ">" . text($row['cmsportal_login']) . "</td>\n";
    echo "   <td>" . text($row['lname'] . ", " . $row['fname']      ) . "</td>\n";
    echo "   <td>" . text($phone                                    ) . "</td>\n";
    echo "   <td>" . text($row['ss']                                ) . "</td>\n";
    echo "   <td>" . text($row['DOB']                               ) . "</td>\n";
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
<input type='button' value='<?php echo xla('Back'); ?>' onclick="myRestoreSession();location='list_requests.php'" />
</p>

</form>
</center>
</body>
</html>

