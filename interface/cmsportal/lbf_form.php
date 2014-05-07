<?php
/**
 * LBF form handling for the WordPress Patient Portal.
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

// Get the portal request data.
if (!$postid) die(xlt('Request ID is missing!'));
$result = cms_portal_call(array('action' => 'getpost', 'postid' => $postid));
if ($result['errmsg']) {
  die(text($result['errmsg']));
}

// Look up the patient in OpenEMR.
$ptid = lookup_openemr_patient($result['post']['user']);
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel=stylesheet href="<?php echo $css_header; ?>" type="text/css">

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<style>

tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; background-color:#ddddff; }
td input  { background-color:transparent; }

</style>

<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>

<script language="JavaScript">

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

function myRestoreSession() {
 if (top.restoreSession) top.restoreSession(); else opener.top.restoreSession();
 return true;
}

function validate() {
 var f = document.forms[0];
 // TBD
 return true;
}

function openPatient() {
 myRestoreSession();
 opener.top.RTop.document.location.href = '../patient_file/summary/demographics.php?set_pid=<?php echo attr($ptid); ?>';
}

</script>
</head>

<body class="body_top">

<?php echo "<!-- "; print_r($result); echo " -->\n"; // debugging ?>

<center>

<form method='post' action='lbf_form.php' onsubmit='return validate()'>

<table width='100%' cellpadding='1' cellspacing='2'>
 <tr class='head'>
  <th align='left'><?php echo xlt('Field'); ?></th>
  <th align='left'><?php echo xlt('Value'); ?></th>
 </tr>

<?php
foreach ($result['fields'] as $field_id => $newvalue) {
  if (is_array($newvalue)) {
    $tmp = '';
    foreach ($newvalue as $value) {
      if ($tmp !== '') $tmp .= ', ';
      $tmp .= $value;
    }
    $newvalue = $tmp;
  }
  $newvalue = trim($newvalue);
  $field_title = $result['labels'][$field_id];
  echo " <tr class='detail'>\n";
  echo "  <td class='bold'>" . text($field_title) . "</td>\n";
  echo "  <td>";
  echo text($newvalue);
  echo "</td>\n";
  echo " </tr>\n";
}
?>

</table>

<p>
<input type='button' value='<?php echo xla('Open Patient'); ?>' onclick="openPatient()" />
&nbsp;
<input type='button' value='<?php echo xla('Back'); ?>' onclick="myRestoreSession();location='list_requests.php'" />
</p>

</form>

<script language="JavaScript">
</script>

</center>
</body>
</html>

