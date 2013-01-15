<?php
/**
* Script to pick a procedure order type from the compendium.
*
* Copyright (C) 2013 Rod Roark <rod@sunsetsystems.com>
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

$fake_register_globals = false;
$sanitize_all_escapes = true;

require_once("../globals.php");

$order =  0 + $_GET['order'];
$labid =  0 + $_GET['labid'];

//////////////////////////////////////////////////////////////////////
// The form was submitted with the selected code type.
if (isset($_GET['typeid'])) {
  $typeid = $_GET['typeid'] + 0;
  $name = '';
  if ($typeid) {
    $ptrow = sqlQuery("SELECT name FROM procedure_type WHERE " .
      "procedure_type_id = '$typeid'");
    $name = addslashes($ptrow['name']);
  }
?>
<script language="JavaScript">
if (opener.closed || !opener.set_proc_type) {
 alert('<?php xl('The destination form was closed; I cannot act on your selection.','e'); ?>');
}
else {
 opener.set_proc_type(<?php echo "$typeid, '$name'"; ?>);
<?php
// This is to generate the "Questions at Order Entry" for the Procedure Order form.
// GET parms needed for this are: formid, formseq.
if (isset($_GET['formid'])) {
  if ($typeid) {
    require_once("qoe.inc.php");
    $qoe_init_javascript = '';
    echo ' opener.set_proc_html("';
    echo generate_qoe_html($typeid, intval($_GET['formid']), 0, intval($_GET['formseq']));
    echo '", "' . $qoe_init_javascript .  '");' . "\n";
  }
  else {
    echo ' opener.set_proc_html("", "");' . "\n";
  }
}
?>
}
window.close();
</script>
<?php
  exit();
}
// End Submission.
//////////////////////////////////////////////////////////////////////

?>
<html>
<head>
<?php html_header_show(); ?>
<title><?php echo xlt('Procedure Picker'); ?></title>
<link rel="stylesheet" href='<?php echo attr($css_header) ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script language="JavaScript">

// Reload the script with the select procedure type ID.
function selcode(typeid) {
 location.href = 'find_order_popup.php<?php
echo "?order=$order&labid=$labid";
if (isset($_GET['formid' ])) echo '&formid='  . $_GET['formid'];
if (isset($_GET['formseq'])) echo '&formseq=' . $_GET['formseq'];
?>&typeid=' + typeid;
 return false;
}

</script>

</head>

<body class="body_top">

<form method='post' name='theform' action='find_order_popup.php<?php
echo "?order=$order&labid=$labid";
if (isset($_GET['formid' ])) echo '&formid='  . $_GET['formid'];
if (isset($_GET['formseq'])) echo '&formseq=' . $_GET['formseq'];
?>'>

<center>

<table border='0' cellpadding='5' cellspacing='0'>

 <tr>
  <td height="1">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td>
   <b>

 <?php echo xlt('Search for:'); ?>
   <input type='text' name='search_term' size='12' value='<?php echo attr($_REQUEST['search_term']); ?>'
    title='<?php echo xla('Any part of the desired code or its description'); ?>' />
   &nbsp;
   <input type='submit' name='bn_search' value='<?php echo xla('Search'); ?>' />
   &nbsp;&nbsp;&nbsp;
   <input type='button' value='<?php echo xla('Erase'); ?>' onclick="selcode(0)" />
   </b>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<?php if ($_REQUEST['bn_search']) { ?>

<table border='0'>
 <tr>
  <td><b><?php echo xlt('Code'); ?></b></td>
  <td><b><?php echo xlt('Description'); ?></b></td>
 </tr>
<?php
  $search_term = '%' . $_REQUEST['search_term'] . '%';

  $query = "SELECT procedure_type_id, procedure_code, name " .
    "FROM procedure_type WHERE " .
    "lab_id = ? AND " .
    "procedure_type LIKE 'ord' AND " .
    "activity = 1 AND " .
    "(procedure_code LIKE ? OR name LIKE ?) " .
    "ORDER BY seq, procedure_code";

  // echo "<!-- $query $labid $search_term -->\n"; // debugging

  $res = sqlStatement($query, array($labid, $search_term, $search_term));

  while ($row = sqlFetchArray($res)) {
    $itertypeid = $row['procedure_type_id'];
    $itercode = $row['procedure_code'];
    $itertext = trim($row['name']);
    $anchor = "<a href='' onclick='return selcode(" .
      "\"" . $itertypeid . "\")'>";
    echo " <tr>";
    echo "  <td>$anchor" . text($itercode) . "</a></td>\n";
    echo "  <td>$anchor" . text($itertext) . "</a></td>\n";
    echo " </tr>";
  }
?>
</table>

<?php } ?>

</center>
</form>
</body>
</html>
