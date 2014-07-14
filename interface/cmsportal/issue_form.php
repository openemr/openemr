<?php
/**
 * Issue posting from the WordPress Patient Portal.
 *
 * Copyright (C) 2014 Rod Roark <rod@sunsetsystems.com>
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
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/lists.inc");
require_once("portal.inc.php");

// Consider this a step towards converting issue forms to layout-based.
// Faking it here makes things easier.
//
$issue_layout = array(
  array('field_id'     => 'type',
        'title'        => 'Type',
        'uor'          => '2',
        'data_type'    => '17',              // Issue Types
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'title',
        'title'        => 'Title',
        'uor'          => '2',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'diagnosis',
        'title'        => 'Diagnosis',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'begdate',
        'title'        => 'Start Date',
        'uor'          => '2',
        'data_type'    => '4',               // Text-date
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'enddate',
        'title'        => 'End Date',
        'uor'          => '1',
        'data_type'    => '4',               // Text-date
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'occurrence',
        'title'        => 'Occurrence',
        'uor'          => '1',
        'data_type'    => '1',               // List
        'list_id'      => 'occurrence',
        'edit_options' => '',
       ),
  array('field_id'     => 'reaction',
        'title'        => 'Reaction',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'outcome',
        'title'        => 'Outcome',
        'uor'          => '1',
        'data_type'    => '1',               // List
        'list_id'      => 'outcome',
        'edit_options' => '',
       ),
  array('field_id'     => 'destination',
        'title'        => 'Destination',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'comments',
        'title'        => 'Comments',
        'uor'          => '1',
        'data_type'    => '3',               // Textarea
        'list_id'      => '',
        'fld_length'   => '50',
        'fld_rows'     => '3',
        'edit_options' => '',
       ),
);

$postid = intval($_REQUEST['postid']);
$issueid = empty($_REQUEST['issueid']) ? 0 : intval($_REQUEST['issueid']);
$form_type = empty($_REQUEST['form_type']) ? '' : $_REQUEST['form_type'];

if ($_POST['bn_save']) {
  $ptid = intval($_POST['ptid']);
  $sets = "date = NOW()";
  foreach ($issue_layout as $frow) {
    $key = $frow['field_id'];
    $value = get_layout_form_value($frow);
    if ($frow['data_type'] == 4) {
      // Dates require some special handling.
      $value = fixDate($value, '');
      if (empty($value)) {
        $value = "NULL";
      }
      else {
        $value = "'$value'";
      }
    }
    else {
      $value = "'" . add_escape_custom($value) . "'";
    }
    $sets .= ", `$key` = $value";
  }
  if (empty($issueid)) {
    $sql = "INSERT INTO lists SET " .
      "pid = '" . add_escape_custom($ptid) . "', activity = 1, " .
      "user = '" . add_escape_custom($_SESSION['authUser']) . "', " .
      "groupname = '" . add_escape_custom($_SESSION['authProvider']) . "', $sets";
    $issueid = sqlInsert($sql);
  }
  else {
    $sql = "UPDATE lists SET $sets WHERE id = '" . add_escape_custom($issueid) . "'";
    sqlStatement($sql);
  }
  // Finally, delete the request from the portal.
  $result = cms_portal_call(array('action' => 'delpost', 'postid' => $postid));
  if ($result['errmsg']) {
    die(text($result['errmsg']));
  }
  echo "<html><body><script language='JavaScript'>\n";
  echo "if (top.restoreSession) top.restoreSession(); else opener.top.restoreSession();\n";
  echo "document.location.href = 'list_requests.php';\n";
  echo "</script></body></html>\n";
  exit();
}

// Get the portal request data.
if (!$postid) die(xlt('Request ID is missing!'));
$result = cms_portal_call(array('action' => 'getpost', 'postid' => $postid));
if ($result['errmsg']) {
  die(text($result['errmsg']));
}
// If user changed issue type, it will have submitted the form to override it.
if ($form_type) $result['fields']['type'] = $form_type;

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

// This capitalizes the first letter of each word in the passed input
// element.  It also strips out extraneous spaces.
// Copied from demographics_full.php.
function capitalizeMe(elem) {
 var a = elem.value.split(' ');
 var s = '';
 for(var i = 0; i < a.length; ++i) {
  if (a[i].length > 0) {
   if (s.length > 0) s += ' ';
   s += a[i].charAt(0).toUpperCase() + a[i].substring(1);
  }
 }
 elem.value = s;
}

function validate() {
 var f = document.forms[0];
 // TBD
 return true;
}

$(document).ready(function() {
  $("#form_type").change(function() {
    myRestoreSession();
    document.forms[0].submit();
    return true;
  });
});

</script>
</head>

<body class="body_top">
<center>

<form method='post' action='issue_form.php' onsubmit='return validate()'>

<input type='hidden' name='ptid'   value="<?php echo attr($ptid);   ?>" />
<input type='hidden' name='postid' value="<?php echo attr($postid); ?>" />

<p>
<select name='issueid' onchange='myRestoreSession();this.form.submit();'>
 <option value='0'><?php echo xlt('Add New Issue'); ?></option>
<?php
$ires = sqlStatement("SELECT id, title, begdate " .
  "FROM lists WHERE pid = ? AND type = ? AND activity > 0 " .
  "AND enddate IS NULL ORDER BY enddate, title",
  array($ptid, $result['fields']['type']));
while ($irow = sqlFetchArray($ires)) {
  echo " <option value='" . attr($irow['id']) . "'";
  if ($irow['id'] == $issueid) echo " selected";
  echo ">" . text($irow['title']);
  if (!empty($irow['begdate'])) echo " (" . text($irow['begdate']) . ")";
  echo "</option>\n";
}
?>
</select>
</p>

<table width='100%' cellpadding='1' cellspacing='2'>
 <tr class='head'>
  <th align='left'><?php echo xlt('Field'        ); ?></th>
  <th align='left'><?php echo xlt('Current Value'); ?></th>
  <th align='left'><?php echo xlt('New Value'    ); ?></th>
 </tr>

<?php
$irow = array();
if (!empty($issueid)) $irow = getListById($issueid);

foreach ($issue_layout as $lorow) {
  $data_type  = $lorow['data_type'];
  $field_id   = $lorow['field_id'];

  $list_id = $lorow['list_id'];
  $field_title = $lorow['title'];

  $currvalue  = '';
  if (isset($irow[$field_id])) $currvalue = $irow[$field_id];

  $newvalue = '';
  if (isset($result['fields'][$field_id])) $newvalue = trim($result['fields'][$field_id]);

  echo " <tr class='detail'>\n";
  echo "  <td class='bold'>" . text($field_title) . "</td>\n";
  echo "  <td>";
  echo generate_display_field($lorow, $currvalue);
  echo "</td>\n";
  echo "  <td>";
  generate_form_field($lorow, $newvalue);
  echo "</td>\n";
  echo " </tr>\n";
}
?>

</table>

<p>
<input type='submit' name='bn_save' value='<?php echo xla('Save and Delete Request'); ?>' />
&nbsp;
<input type='button' value='<?php echo xla('Back'); ?>'
 onclick="myRestoreSession();location='list_requests.php'" />
</p>

</form>

<script language="JavaScript">

// This is a by-product of generate_form_field().
<?php echo $date_init; ?>

</script>

</center>
</body>
</html>

