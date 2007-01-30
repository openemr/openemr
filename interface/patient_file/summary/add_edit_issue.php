<?php
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
 $thispid = $_REQUEST['thispid'] ? $_REQUEST['thispid'] : $pid;
 $info_msg = "";

 $thisauth = acl_check('patients', 'med');
 if ($issue && $thisauth != 'write') die("Edit is not authorized!");
 if ($thisauth != 'write' && $thisauth != 'addonly') die("Add is not authorized!");

 $tmp = getPatientData($thispid, "squad");
 if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
  die("Not authorized for this squad!");

 $arroccur = array(
  0   => xl('Unknown or N/A'),
  1   => xl('First'),
  2   => xl('Second'),
  3   => xl('Third'),
  4   => xl('Chronic/Recurrent'),
  5   => xl('Acute on Chronic')
 );

 function QuotedOrNull($fld) {
  if ($fld) return "'$fld'";
  return "NULL";
 }

 function rbinput($name, $value, $desc, $colname) {
  global $irow;
  $ret  = "<input type='radio' name='$name' value='$value'";
  if ($irow[$colname] == $value) $ret .= " checked";
  $ret .= " />$desc";
  return $ret;
 }

 function rbvalue($rbname) {
  $tmp = $_POST[$rbname];
  if (! $tmp) $tmp = '0';
  return "'$tmp'";
 }

?>
<html>
<head>
<title><? echo $issue ? "Edit" : "Add New" ?><?php xl('Issue','e'); ?></title>
<link rel=stylesheet href='<? echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>

<script language="JavaScript">

 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';

 var aitypes = new Array(); // issue type attributes
 var aopts   = new Array(); // Option objects
<?php
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
  echo " aitypes[$i] = " . $value[3] . ";\n";
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
 // shortcuts into the selection list of titles, and determines which
 // rows are displayed or hidden.
 function newtype(index) {
  var f = document.forms[0];
  var theopts = f.form_titles.options;
  theopts.length = 0;
  for (i = 0; i < aopts[index].length; ++i) {
   theopts[i] = aopts[index][i];
  }
  // Show or hide various rows depending on issue type, except do not
  // hide the comments or referred-by fields if they have data.
  var comdisp = (aitypes[index] == 1) ? 'none' : '';
  var revdisp = (aitypes[index] == 1) ? '' : 'none';
  document.getElementById('row_enddate'   ).style.display = comdisp;
  document.getElementById('row_active'    ).style.display = revdisp;
  document.getElementById('row_diagnosis' ).style.display = comdisp;
  document.getElementById('row_occurrence').style.display = comdisp;
  document.getElementById('row_referredby').style.display = (f.form_referredby.value) ? '' : comdisp;
  document.getElementById('row_comments'  ).style.display = (f.form_comments.value) ? '' : revdisp;
<?php if ($GLOBALS['athletic_team']) { ?>
  document.getElementById('row_returndate').style.display = comdisp;
  document.getElementById('row_missed'    ).style.display = comdisp;
<?php } ?>
 }

 // If a clickoption title is selected, copy it to the title field.
 function set_text() {
  var f = document.forms[0];
  f.form_title.value = f.form_titles.options[f.form_titles.selectedIndex].text;
  f.form_titles.selectedIndex = -1;
 }

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?issue=<?php echo $issue ?>', '_blank', 500, 450);
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
  window.close();
 }

 // Called when the Active checkbox is clicked.  For consistency we
 // use the existence of an end date to indicate inactivity, even
 // though the simple verion of the form does not show an end date.
 function activeClicked(cb) {
  var f = document.forms[0];
  if (cb.checked) {
   f.form_end.value = '';
  } else {
   var today = new Date();
   f.form_end.value = '' + (today.getYear() + 1900) + '-' +
    (today.getMonth() + 1) + '-' + today.getDate();
  }
 }

</script>

</head>

<body <?echo $top_bg_line;?>>
<?php
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

   $query = "UPDATE lists SET " .
    "type = '"        . $text_type                  . "', " .
    "title = '"       . $_POST['form_title']        . "', " .
    "comments = '"    . $_POST['form_comments']     . "', " .
    "begdate = "      . QuotedOrNull($form_begin)   . ", "  .
    "enddate = "      . QuotedOrNull($form_end)     . ", "  .
    "returndate = "   . QuotedOrNull($form_return)  . ", "  .
    "diagnosis = '"   . $_POST['form_diagnosis']    . "', " .
    "occurrence = '"  . $_POST['form_occur']        . "', " .
    "referredby = '"  . $_POST['form_referredby']   . "', " .
    "extrainfo = '"   . $_POST['form_missed']       . "', " .
    "outcome = "      . rbvalue('form_outcome')     . ", "  .
//  "destination = "  . rbvalue('form_destination') . " "   . // radio button version
    "destination = '" . $_POST['form_destination']   . "' "  .
    "WHERE id = '$issue'";
    sqlStatement($query);
    if ($text_type == "medication" && enddate != '') {
      sqlStatement('UPDATE prescriptions SET '
        . 'medication = 0 where patient_id = ' . $thispid
        . " and upper(trim(drug)) = '" . strtoupper($_POST['form_title']) . "' "
        . ' and medication = 1' );
    }

  } else {

   $issue = sqlInsert("INSERT INTO lists ( " .
    "date, pid, type, title, activity, comments, begdate, enddate, returndate, " .
    "diagnosis, occurrence, referredby, extrainfo, user, groupname, " .
    "outcome, destination " .
    ") VALUES ( " .
    "NOW(), " .
    "'$thispid', " .
    "'" . $text_type                 . "', " .
    "'" . $_POST['form_title']       . "', " .
    "1, "                            .
    "'" . $_POST['form_comments']    . "', " .
    QuotedOrNull($form_begin)        . ", "  .
    QuotedOrNull($form_end)          . ", "  .
    QuotedOrNull($form_return)       . ", "  .
    "'" . $_POST['form_diagnosis']   . "', " .
    "'" . $_POST['form_occur']       . "', " .
    "'" . $_POST['form_referredby']  . "', " .
    "'" . $_POST['form_missed']      . "', " .
    "'" . $$_SESSION['authUser']     . "', " .
    "'" . $$_SESSION['authProvider'] . "', " .
   rbvalue('form_outcome')           . ", "  .
// rbvalue('form_destination')       . " "   . // radio button version
    "'" . $_POST['form_destination'] . "' "  .
   ")");

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
   "issue_encounter.pid = '$thispid' AND " .
   "issue_encounter.list_id = '$issue' AND " .
   "billing.encounter = issue_encounter.encounter AND " .
   "( billing.code_type LIKE 'ICD%' OR " .
   "billing.code_type LIKE 'OSICS' OR " .
   "billing.code_type LIKE 'UCSMC' )"
  );
 }
?>
<form method='post' name='theform' action='add_edit_issue.php?issue=<? echo $issue ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td valign='top' width='1%' nowrap><b><? xl('Type','e'); ?>:</b></td>
  <td>
<?php
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
  <td valign='top' nowrap><b><? xl('Title','e'); ?>:</b></td>
  <td>
   <select name='form_titles' size='4' onchange='set_text()' style='width:100%'>
   </select><br>
   <input type='text' size='40' name='form_title' value='<? echo $irow['title'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Begin Date','e'); ?>:</b></td>
  <td>

   <input type='text' size='10' name='form_begin' id='form_begin'
    value='<? echo $irow['begdate'] ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='<?php xl('yyyy-mm-dd date of onset, surgery or start of medication','e'); ?>' />
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_begin' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />
  </td>
 </tr>

 <tr id='row_enddate'>
  <td valign='top' nowrap><b><? xl('End Date','e'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_end' id='form_end'
    value='<? echo $irow['enddate'] ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='<?php xl('yyyy-mm-dd date of recovery or end of medication','e'); ?>' />
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_end' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />
    &nbsp;(<? xl('leave blank if still active','e'); ?>)
  </td>
 </tr>

 <tr id='row_active'>
  <td valign='top' nowrap><b><? xl('Active','e'); ?>:</b></td>
  <td>
   <input type='checkbox' name='form_active' value='1' <? echo $irow['enddate'] ? "" : "checked"; ?>
    onclick='activeClicked(this);'
    title='<?php xl('Indicates if this issue is currently active','e'); ?>' />
  </td>
 </tr>

 <tr<?php if (! $GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_returndate'>
  <td valign='top' nowrap><b><? xl('Returned to Play','e'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_return' id='form_return'
    value='<? echo $irow['returndate'] ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='<?php xl('yyyy-mm-dd date returned to play','e'); ?>' />
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_return' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />
    &nbsp;(<? xl('leave blank if still active','e'); ?>)
  </td>
 </tr>

 <tr id='row_diagnosis'>
  <td valign='top' nowrap><b><? xl('Diagnosis','e'); ?>:</b></td>
  <td>
   <select name='form_diagnosis' title='<?php xl('Diagnosis must be coded into a linked encounter','e'); ?>'>
    <option value=""><? xl('Unknown or N/A','e'); ?></option>
<?php
 while ($brow = sqlFetchArray($bres)) {
  echo "   <option value='" . $brow['code'] . "'";
  if ($brow['code'] == $irow['diagnosis']) echo " selected";
  echo ">" . $brow['code'] . " " . substr($brow['code_text'], 0, 40) . "</option>\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr id='row_occurrence'>
  <td valign='top' nowrap><b><? xl('Occurrence','e'); ?>:</b></td>
  <td>
   <select name='form_occur'>
<?php
 foreach ($arroccur as $key => $value) {
  echo "   <option value='$key'";
  if ($key == $irow['occurrence']) echo " selected";
  echo ">$value\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr<?php if (! $GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_missed'>
  <td valign='top' nowrap><b><? xl('Missed','e'); ?>:</b></td>
  <td>
   <input type='text' size='3' name='form_missed' value='<? echo $irow['extrainfo'] ?>'
    title='<?php xl('Number of games or events missed, if any','e'); ?>' />
   &nbsp;<? xl('games/events','e'); ?>
  </td>
 </tr>

 <tr<?php if ($GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_referredby'>
  <td valign='top' nowrap><b><? xl('Referred by','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_referredby' value='<? echo $irow['referredby'] ?>'
    style='width:100%' title='<?php xl('Referring physician and practice','e'); ?>' />
  </td>
 </tr>

 <tr id='row_comments'>
  <td valign='top' nowrap><b><? xl('Comments','e'); ?>:</b></td>
  <td>
   <textarea name='form_comments' rows='4' cols='40' wrap='virtual' style='width:100%'><? echo $irow['comments'] ?></textarea>
  </td>
 </tr>

 <tr<?php if ($GLOBALS['athletic_team']) echo " style='display:none;'"; ?>>
  <td valign='top' nowrap><b><? xl('Outcome','e'); ?>:</b></td>
  <td>
   <? echo rbinput('form_outcome', '1', 'Resolved'        , 'outcome') ?>&nbsp;
   <? echo rbinput('form_outcome', '2', 'Improved'        , 'outcome') ?>&nbsp;
   <? echo rbinput('form_outcome', '3', 'Status quo'      , 'outcome') ?>&nbsp;
   <? echo rbinput('form_outcome', '4', 'Worse'           , 'outcome') ?>&nbsp;
   <? echo rbinput('form_outcome', '5', 'Pending followup', 'outcome') ?>
  </td>
 </tr>

 <tr<?php if ($GLOBALS['athletic_team']) echo " style='display:none;'"; ?>>
  <td valign='top' nowrap><b><? xl('Destination','e'); ?>:</b></td>
  <td>
<?php if (true) { ?>
   <input type='text' size='40' name='form_destination' value='<? echo $irow['destination'] ?>'
    style='width:100%' title='GP, Secondary care specialist, etc.' />
<?php } else { // leave this here for now, please -- Rod ?>
   <? echo rbinput('form_destination', '1', 'GP'                 , 'destination') ?>&nbsp;
   <? echo rbinput('form_destination', '2', 'Secondary care spec', 'destination') ?>&nbsp;
   <? echo rbinput('form_destination', '3', 'GP via physio'      , 'destination') ?>&nbsp;
   <? echo rbinput('form_destination', '4', 'GP via podiatry'    , 'destination') ?>
<?php } ?>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='<?php xl('Save','e'); ?>' />

<?php if ($issue && acl_check('admin', 'super')) { ?>
&nbsp;
<input type='button' value='<?php xl('Delete','e'); ?>' style='color:red' onclick='deleteme()' />
<?php } ?>

&nbsp;
<input type='button' value='<?php xl('Cancel','e'); ?>' onclick='window.close()' />
</p>

</center>
</form>
<script language='JavaScript'>
 newtype(<? echo $type_index ?>);
 Calendar.setup({inputField:"form_begin", ifFormat:"%Y-%m-%d", button:"img_begin"});
 Calendar.setup({inputField:"form_end", ifFormat:"%Y-%m-%d", button:"img_end"});
 Calendar.setup({inputField:"form_return", ifFormat:"%Y-%m-%d", button:"img_return"});
</script>
</body>
</html>
