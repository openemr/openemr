<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
// Some code was adapted from patient_select.php.
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formdata.inc.php");

$fstart = $_REQUEST['fstart'] + 0;

$searchcolor = empty($GLOBALS['layout_search_color']) ?
  '#ffff55' : $GLOBALS['layout_search_color'];
?>
<html>
<head>
<?php html_header_show();?>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<style>
form {
 padding: 0px;
 margin: 0px;
}
#searchCriteria {
 text-align: center;
 width: 100%;
 font-size: 0.8em;
 background-color: #ddddff;
 font-weight: bold;
 padding: 3px;
}
#searchResultsHeader { 
 width: 100%;
 background-color: lightgrey;
}
#searchResultsHeader table { 
 width: 96%;  /* not 100% because the 'searchResults' table has a scrollbar */
 border-collapse: collapse;
}
#searchResultsHeader th {
 font-size: 0.7em;
}
#searchResults {
 width: 100%;
 height: 80%;
 overflow: auto;
}

.srName  { width: 12%; }
.srPhone { width: 11%; }
.srSS    { width: 11%; }
.srDOB   { width:  8%; }
.srID    { width:  7%; }
.srMisc  { width: 10%; }

#searchResults table {
 width: 100%;
 border-collapse: collapse;
 background-color: white;
}
#searchResults tr {
 cursor: hand;
 cursor: pointer;
}
#searchResults td {
 font-size: 0.7em;
 border-bottom: 1px solid #eee;
}
.oneResult {
}
.topResult {
 background-color: <?php echo $searchcolor; ?>;
}
.billing {
 color: red;
 font-weight: bold;
}
.highlight { 
 background-color: #336699;
 color: white;
}
</style>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>

<script language="JavaScript">

// This is called when forward or backward paging is done.
//
function submitList(offset) {
 var f = document.forms[0];
 var i = parseInt(f.fstart.value) + offset;
 if (i < 0) i = 0;
 f.fstart.value = i;
 f.submit();
}

</script>

</head>
<body class="body_top">

<form method='post' action='new_search_popup.php' name='theform'>
<input type='hidden' name='fstart'  value='<?php echo $fstart  ?>' />

<?php
$MAXSHOW = 100; // maximum number of results to display at once

// Construct query and save search parameters as form fields.
// An interesting requirement is to sort on the number of matching fields.

$message = "";
$numfields = 0;
$relevance = "0";
$where = "1 = 0";

foreach ($_REQUEST as $key => $value) {
  if (substr($key, 0, 3) != 'mf_') continue; // "match field"
  $fldname = substr($key, 3);
  $avalue = formDataCore($value);
  $hvalue = htmlspecialchars(strip_escape_custom($value));
  // pubpid requires special treatment.  Match on that is fatal.
  if ($fldname == 'pubpid')
    $relevance .= " + 1000 * ( $fldname LIKE '$avalue' )";
  else
    $relevance .= " + ( $fldname LIKE '$avalue' )";
  $where .= " OR $fldname LIKE '$avalue'";
  echo "<input type='hidden' name='$key' value='$hvalue' />\n";
  ++$numfields;
}

$sql = "SELECT *, ( $relevance ) AS relevance, " .
  "DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS " .
  "FROM patient_data WHERE $where " .
  "ORDER BY relevance DESC, lname, fname, mname " .
  "LIMIT $fstart, $MAXSHOW";

$rez = sqlStatement($sql);
$result = array();
while ($row = sqlFetchArray($rez)) $result[] = $row;
_set_patient_inc_count($MAXSHOW, count($result), $where);
?>

</form>

<table border='0' cellpadding='5' cellspacing='0' width='100%'>
 <tr>
  <td class='text'>
   &nbsp;
  </td>
  <td class='text' align='center'>
<?php if ($message) echo "<font color='red'><b>$message</b></font>\n"; ?>
  </td>
  <td class='text' align='right'>
<?php
// Show start and end row number, and number of rows, with paging links.
$count = $GLOBALS['PATIENT_INC_COUNT'];
$fend = $fstart + $MAXSHOW;
if ($fend > $count) $fend = $count;
?>
<?php if ($fstart) { ?>
   <a href="javascript:submitList(-<?php echo $MAXSHOW ?>)">
    &lt;&lt;
   </a>
   &nbsp;&nbsp;
<?php } ?>
   <?php echo ($fstart + 1) . " - $fend of $count" ?>
<?php if ($count > $fend) { ?>
   &nbsp;&nbsp;
   <a href="javascript:submitList(<?php echo $MAXSHOW ?>)">
    &gt;&gt;
   </a>
<?php } ?>
  </td>
 </tr>
</table>

<div id="searchResultsHeader">
<table>
<tr>
<th class="srID"   ><?php xl('Hits'   ,'e');?></th>
<th class="srName" ><?php xl('Name'   ,'e');?></th>
<?php
// This gets address plus other fields that are mandatory, up to a limit of 5.
$extracols = array();
$tres = sqlStatement("SELECT field_id, title FROM layout_options " .
  "WHERE form_id = 'DEM' AND field_id != '' AND " .
  "( uor > 1 OR uor > 0 AND edit_options LIKE '%D%' ) AND " .
  "field_id NOT LIKE 'title' AND " .
  "field_id NOT LIKE '_name' " .
  "ORDER BY group_name, seq, title LIMIT 9");

while ($trow = sqlFetchArray($tres)) {
  $extracols[$trow['field_id']] = $trow['title'];
  echo "<th class='srMisc'>" . xl_layout_label($trow['title']) . "</th>\n";
}
?>

</tr>
</table>
</div>

<div id="searchResults">

<table>
<tr>
<?php
$pubpid_matched = false;
if ($result) {
  foreach ($result as $iter) {
    $relevance = $iter['relevance'];
    if ($relevance > 999) {
      $relevance -= 999;
      $pubpid_matched = true;
    }
    echo "<tr id='" . $iter['pid'] . "' class='oneresult";
    // Highlight entries where all fields matched.
    echo $numfields <= $iter['relevance'] ? " topresult" : "";
    echo "'>";
    echo  "<td class='srID'>$relevance</td>\n";
    echo  "<td class='srName'>" . $iter['lname'] . ", " . $iter['fname'] . "</td>\n";
    foreach ($extracols as $field_id => $title) {
      echo "<td class='srMisc'>" . $iter[$field_id] . "</td>\n";
    }
  }
}
?>
</table>
</div>  <!-- end searchResults DIV -->

<center>
<?php if ($pubpid_matched) { ?>
<input type='button' value='<?php echo xl('Cancel'); ?>'
 onclick='window.close();' />
<?php } else { ?>
<input type='button' value='<?php echo xl('Confirm Create New Patient'); ?>'
 onclick='opener.top.restoreSession();opener.document.forms[0].submit();window.close();' />
<?php } ?>
</center>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function() {
  $(".oneresult").mouseover(function() { $(this).addClass("highlight"); });
  $(".oneresult").mouseout(function() { $(this).removeClass("highlight"); });
  $(".oneresult").click(function() { SelectPatient(this); });
});

var SelectPatient = function (eObj) {
<?php 
// For the old layout we load a frameset that also sets up the new pid.
// The new layout loads just the demographics frame here, which in turn
// will set the pid and load all the other frames.
if ($GLOBALS['concurrent_layout']) {
  $newPage = "../patient_file/summary/demographics.php?set_pid=";
  $target = "document";
}
else {
  $newPage = "../patient_file/patient_file.php?set_pid=";
  $target = "top";
}
?>
  objID = eObj.id;
  var parts = objID.split("~");
  opener.<?php echo $target; ?>.location.href = '<?php echo $newPage; ?>' + parts[0];
  window.close();
  return true;
}

var f = opener.document.forms[0];
<?php if ($pubpid_matched) { ?>
alert('<?php xl('A patient with this ID already exists.','e'); ?>')
<?php } else { ?>
opener.force_submit = true;
f.create.value = '<?php xl('Confirm Create New Patient','e'); ?>';
<?php } ?>

<?php if (!count($result)) { ?>
if (confirm('<?php xl('No matches were found. Create the new patient now?','e'); ?>')) {
 opener.top.restoreSession();
 f.submit();
}
window.close();
<?php } ?>

</script>

</body>
</html>

