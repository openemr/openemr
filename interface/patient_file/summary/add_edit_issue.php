<?
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../../globals.php");
 include_once("$srcdir/lists.inc");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/acl.inc");

 $issue = $_REQUEST['issue'];
 $info_msg = "";

 $thisauth = acl_check('patients', 'med');
 if ($issue && $thisauth != 'write') die("Edit is not authorized!");
 if ($thisauth != 'write' && $thisauth != 'addonly') die("Add is not authorized!");

 $tmp = getPatientData($pid, "squad");
 if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
  die("Not authorized for this squad!");

 $arroccur = array(
  0   => 'Unknown or N/A',
  1   => 'First',
  2   => 'Second',
  3   => 'Third',
  4   => 'Chronic/Recurrent',
  5   => 'Acute on Chronic'
 );

 function QuotedOrNull($fld) {
  if ($fld) return "'$fld'";
  return "NULL";
 }

?>
<html>
<head>
<title><? echo $issue ? "Edit" : "Add New" ?> Issue</title>
<link rel=stylesheet href='<? echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script type="text/javascript" src="../../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../../library/calendar.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>

<script language="JavaScript">

 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';

 var aopts = new Array();
<?
 // "Clickoptions" is a feature by Mark Leeds that provides for one-click
 // access to preselected lists of issues in each category.  Here we get
 // the issue titles from the user-customizable file and write JavaScript
 // statements that will build an array of arrays of Option objects.
 //
 $clickoptions = array();
 if (is_file("../../../custom/clickoptions.txt"))
  $clickoptions = file("../../../custom/clickoptions.txt");
 $i = 0;
 foreach ($ISSUE_TYPES as $key => $value) {
  echo " aopts[$i] = new Array();\n";
  foreach($clickoptions as $line) {
   $line = trim($line);
   if (substr($line, 0, 1) != "#") {
    if (strpos($line, $key) !== false) {
     $text = addslashes(substr($line, strpos($line, "::") + 2));
     echo " aopts[$i][aopts[$i].length] = new Option('$text', '$text', false, false);\n";
    }
   }
  }
  ++$i;
 }
?>

 // React to selection of an issue type.  This loads the associated
 // shortcuts into the selection list of titles.
 function newtype(index) {
  var theopts = document.forms[0].form_titles.options;
  theopts.length = 0;
  for (i = 0; i < aopts[index].length; ++i) {
   theopts[i] = aopts[index][i];
  }
 }

 // If a clickoption title is selected, copy it to the title field.
 function set_text() {
  var f = document.forms[0];
  f.form_title.value = f.form_titles.options[f.form_titles.selectedIndex].text;
  f.form_titles.selectedIndex = -1;
 }

</script>

</head>

<body <?echo $top_bg_line;?>>
<?
 // If we are saving, then save and close the window.
 //
 if ($_POST['form_save']) {

  $i = 0;
  $text_type = "unknown";
  foreach ($ISSUE_TYPES as $key => $value) {
   if ($i++ == $_POST['form_type']) $text_type = $key;
  }

  $form_begin = fixDate($_POST['form_begin'], '');
  $form_end   = fixDate($_POST['form_end'], '');

  if ($issue) {
   sqlStatement("UPDATE lists SET " .
    "type = '" . $text_type . "', " .
    "title = '" . $_POST['form_title'] . "', " .
    "comments = '" . $_POST['form_comments'] . "', " .
    "begdate = " . QuotedOrNull($form_begin) . ", " .
    "enddate = " . QuotedOrNull($form_end) . ", " .
    "diagnosis = '" . $_POST['form_diagnosis'] . "', " .
    "occurrence = '" . $_POST['form_occur'] . "', " .
    "referredby = '" . $_POST['form_referredby'] . "', " .
    "extrainfo = '" . $_POST['form_missed'] . "' " .
    "WHERE id = '$issue'");
  } else {
   $issue = sqlInsert("INSERT INTO lists ( " .
    "date, pid, type, title, activity, comments, begdate, enddate, " .
    "diagnosis, occurrence, referredby, extrainfo, user, groupname " .
    ") VALUES ( " .
    "NOW(), " .
    "'$pid', " .
    "'" . $text_type                 . "', " .
    "'" . $_POST['form_title']       . "', " .
    "1, "                            .
    "'" . $_POST['form_comments']    . "', " .
    QuotedOrNull($form_begin)        . ", "  .
    QuotedOrNull($form_end)          . ", "  .
    "'" . $_POST['form_diagnosis']   . "', " .
    "'" . $_POST['form_occur']       . "', " .
    "'" . $_POST['form_referredby']  . "', " .
    "'" . $_POST['form_missed']      . "', " .
    "'" . $$_SESSION['authUser']     . "', " .
    "'" . $$_SESSION['authProvider'] . "' )");
  }

  $tmp_title = $ISSUE_TYPES[$text_type][2] . ": $form_begin " .
   substr($_POST['form_title'], 0, 40);

  // Close this window and redisplay the updated list of issues.
  //
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " window.close();\n";
  // echo " opener.location.reload();\n";
  echo " if (opener.refreshIssue) opener.refreshIssue($issue,'$tmp_title');\n";
  echo "</script></body></html>\n";
  exit();
 }

 $irow = array();
 $type_index = 0;

 if ($issue) {
  $irow = sqlQuery("SELECT * FROM lists WHERE id = $issue");
  foreach ($ISSUE_TYPES as $key => $value) {
   if ($key == $irow['type']) break;
   ++$type_index;
  }
  // Get all of the eligible diagnoses.
  // We include the pid in this search for better performance,
  // because it's part of the primary key:
  $bres = sqlStatement(
   "SELECT DISTINCT billing.code, billing.code_text " .
   "FROM issue_encounter, billing WHERE " .
   "issue_encounter.pid = '$pid' AND " .
   "issue_encounter.list_id = '$issue' AND " .
   "billing.encounter = issue_encounter.encounter AND " .
   "( billing.code_type LIKE 'ICD%' OR " .
   "billing.code_type LIKE 'OSICS' OR " .
   "billing.code_type LIKE 'UCSMC' )"
  );
 }
?>
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form method='post' name='theform' action='add_edit_issue.php?issue=<? echo $issue ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td valign='top' width='1%' nowrap><b>Type:</b></td>
  <td>
<?
 $index = 0;
 foreach ($ISSUE_TYPES as $value) {
  echo "   <input type='radio' name='form_type' value='$index' onclick='newtype($index)'";
  if ($index == $type_index) echo " checked";
  echo " />" . $value[1] . "&nbsp;\n";
  ++$index;
 }
?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b>Title:</b></td>
  <td>
   <select name='form_titles' size='4' onchange='set_text()' style='width:100%'>
   </select><br>
   <input type='text' size='40' name='form_title' value='<? echo $irow['title'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b>Begin Date:</b></td>
  <td>
   <input type='text' size='10' name='form_begin' value='<? echo $irow['begdate'] ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='yyyy-mm-dd date of onset, surgery or start of medication' />
   <a href="javascript:show_calendar('theform.form_begin')"
    title="Click here to choose a date"
    ><img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b>End Date:</b></td>
  <td>
   <input type='text' size='10' name='form_end' value='<? echo $irow['enddate'] ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='yyyy-mm-dd date of recovery or end of medication' />
   <a href="javascript:show_calendar('theform.form_end')"
    title="Click here to choose a date"
    ><img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
    &nbsp;(leave blank if still active)
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b>Diagnosis:</b></td>
  <td>
   <select name='form_diagnosis' title='Diagnosis must be coded into a linked encounter'>
    <option value="">Unknown or N/A</option>
<?
 while ($brow = sqlFetchArray($bres)) {
  echo "   <option value='" . $brow['code'] . "'";
  if ($brow['code'] == $irow['diagnosis']) echo " selected";
  echo ">" . $brow['code'] . " " . substr($brow['code_text'], 0, 40) . "</option>\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b>Occurrence:</b></td>
  <td>
   <select name='form_occur'>
<?
 foreach ($arroccur as $key => $value) {
  echo "   <option value='$key'";
  if ($key == $irow['occurrence']) echo " selected";
  echo ">$value\n";
 }
?>
   </select>
  </td>
 </tr>

<? if ($GLOBALS['athletic_team']) { ?>
 <tr>
  <td valign='top' nowrap><b>Missed:</b></td>
  <td>
   <input type='text' size='3' name='form_missed' value='<? echo $irow['extrainfo'] ?>'
    title='Number of games or events missed, if any' />
   &nbsp;games/events
  </td>
 </tr>
<? } else { ?>
 <tr>
  <td valign='top' nowrap><b>Referred by:</b></td>
  <td>
   <input type='text' size='40' name='form_referredby' value='<? echo $irow['referredby'] ?>'
    style='width:100%' title='Referring physician and practice' />
  </td>
 </tr>
<? } ?>

 <tr>
  <td valign='top' nowrap><b>Comments:</b></td>
  <td>
   <textarea name='form_comments' rows='4' cols='40' wrap='virtual' style='width:100%'><? echo $irow['comments'] ?></textarea>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick='window.close()' />
</p>

</center>
</form>
<script language='JavaScript'>
 newtype(<? echo $type_index ?>);
</script>
</body>
</html>
