<?php
/**
 * add or edit a medical problem.
 *
 * Copyright (C) 2005-2011 Rod Roark <rod@sunsetsystems.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/lists.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
require_once($GLOBALS['srcdir'].'/csv_like_join.php');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');

if ($ISSUE_TYPES['football_injury']) {
  // Most of the logic for the "football injury" issue type comes from this
  // included script.  We might eventually refine this approach to support
  // a plug-in architecture for custom issue types.
  require_once($GLOBALS['srcdir'].'/football_injury.inc.php');
}
if ($ISSUE_TYPES['ippf_gcac']) {
  // Similarly for IPPF issues.
  require_once($GLOBALS['srcdir'].'/ippf_issues.inc.php');
}

$issue = $_REQUEST['issue'];
$thispid = 0 + (empty($_REQUEST['thispid']) ? $pid : $_REQUEST['thispid']);
$info_msg = "";

// A nonempty thisenc means we are to link the issue to the encounter.
$thisenc = 0 + (empty($_REQUEST['thisenc']) ? 0 : $_REQUEST['thisenc']);

// A nonempty thistype is an issue type to be forced for a new issue.
$thistype = empty($_REQUEST['thistype']) ? '' : $_REQUEST['thistype'];

if ($issue && !acl_check('patients','med','','write') ) die(xlt("Edit is not authorized!"));
if ( !acl_check('patients','med','',array('write','addonly') )) die(xlt("Add is not authorized!"));

$tmp = getPatientData($thispid, "squad");
if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
  die(xlt("Not authorized for this squad!"));

function QuotedOrNull($fld) {
  if ($fld) return "'".add_escape_custom($fld)."'";
  return "NULL";
}


// Do not use this function since quotes are added in query escaping mechanism
// Only keeping since used in the football injury code football_injury.inc.php that is included.
// If start using this function, then incorporate the add_escape_custom() function into it
function rbvalue($rbname) {
  $tmp = $_POST[$rbname];
  if (! $tmp) $tmp = '0';
  return "'$tmp'";
}

function cbvalue($cbname) {
  return $_POST[$cbname] ? '1' : '0';
}

function invalue($inname) {
  return (int) trim($_POST[$inname]);
}

// Do not use this function since quotes are added in query escaping mechanism
// Only keeping since used in the football injury code football_injury.inc.php that is included.
// If start using this function, then incorporate the add_escape_custom() function into it
function txvalue($txname) {
  return "'" . trim($_POST[$txname]) . "'";
}

function rbinput($name, $value, $desc, $colname) {
  global $irow;
  $ret  = "<input type='radio' name='".attr($name)."' value='".attr($value)."'";
  if ($irow[$colname] == $value) $ret .= " checked";
  $ret .= " />".text($desc);
  return $ret;
}

function rbcell($name, $value, $desc, $colname) {
 return "<td width='25%' nowrap>" . rbinput($name, $value, $desc, $colname) . "</td>\n";
}

// Given an issue type as a string, compute its index.
function issueTypeIndex($tstr) {
  global $ISSUE_TYPES;
  $i = 0;
  foreach ($ISSUE_TYPES as $key => $value) {
    if ($key == $tstr) break;
    ++$i;
  }
  return $i;
}

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

  if ($text_type == 'football_injury') {
    $form_injury_part = $_POST['form_injury_part'];
    $form_injury_type = $_POST['form_injury_type'];
  }
  else {
    $form_injury_part = $_POST['form_medical_system'];
    $form_injury_type = $_POST['form_medical_type'];
  }

  if ($issue) {

   $query = "UPDATE lists SET " .
    "type = '"        . add_escape_custom($text_type)                  . "', " .
    "title = '"       . add_escape_custom($_POST['form_title'])        . "', " .
    "comments = '"    . add_escape_custom($_POST['form_comments'])     . "', " .
    "begdate = "      . QuotedOrNull($form_begin)   . ", "  .
    "enddate = "      . QuotedOrNull($form_end)     . ", "  .
    "returndate = "   . QuotedOrNull($form_return)  . ", "  .
    "diagnosis = '"   . add_escape_custom($_POST['form_diagnosis'])    . "', " .
    "occurrence = '"  . add_escape_custom($_POST['form_occur'])        . "', " .
    "classification = '" . add_escape_custom($_POST['form_classification']) . "', " .
    "reinjury_id = '" . add_escape_custom($_POST['form_reinjury_id'])  . "', " .
    "referredby = '"  . add_escape_custom($_POST['form_referredby'])   . "', " .
    "injury_grade = '" . add_escape_custom($_POST['form_injury_grade']) . "', " .
    "injury_part = '" . add_escape_custom($form_injury_part)           . "', " .
    "injury_type = '" . add_escape_custom($form_injury_type)           . "', " .
    "outcome = '"     . add_escape_custom($_POST['form_outcome'])      . "', " .
    "destination = '" . add_escape_custom($_POST['form_destination'])   . "', " .
    "reaction ='"     . add_escape_custom($_POST['form_reaction'])     . "', " .
    "erx_uploaded = '0' " .
    "WHERE id = '" . add_escape_custom($issue) . "'";
    sqlStatement($query);
    if ($text_type == "medication" && enddate != '') {
      sqlStatement('UPDATE prescriptions SET '
        . 'medication = 0 where patient_id = ? '
        . " and upper(trim(drug)) = ? "
        . ' and medication = 1', array($thispid,strtoupper($_POST['form_title'])) );
    }

  } else {

   $issue = sqlInsert("INSERT INTO lists ( " .
    "date, pid, type, title, activity, comments, begdate, enddate, returndate, " .
    "diagnosis, occurrence, classification, referredby, user, groupname, " .
    "outcome, destination, reinjury_id, injury_grade, injury_part, injury_type, " .
    "reaction " .
    ") VALUES ( " .
    "NOW(), " .
    "'" . add_escape_custom($thispid) . "', " .
    "'" . add_escape_custom($text_type)                 . "', " .
    "'" . add_escape_custom($_POST['form_title'])       . "', " .
    "1, "                            .
    "'" . add_escape_custom($_POST['form_comments'])    . "', " .
    QuotedOrNull($form_begin)        . ", "  .
    QuotedOrNull($form_end)        . ", "  .
    QuotedOrNull($form_return)       . ", "  .
    "'" . add_escape_custom($_POST['form_diagnosis'])   . "', " .
    "'" . add_escape_custom($_POST['form_occur'])       . "', " .
    "'" . add_escape_custom($_POST['form_classification']) . "', " .
    "'" . add_escape_custom($_POST['form_referredby'])  . "', " .
    "'" . add_escape_custom($$_SESSION['authUser'])     . "', " .
    "'" . add_escape_custom($$_SESSION['authProvider']) . "', " .
    "'" . add_escape_custom($_POST['form_outcome'])     . "', " .
    "'" . add_escape_custom($_POST['form_destination']) . "', " .
    "'" . add_escape_custom($_POST['form_reinjury_id']) . "', " .
    "'" . add_escape_custom($_POST['form_injury_grade']) . "', " .
    "'" . add_escape_custom($form_injury_part)          . "', " .
    "'" . add_escape_custom($form_injury_type)          . "', " .
    "'" . add_escape_custom($_POST['form_reaction'])         . "' " .
   ")");

  }

  // For record/reporting purposes, place entry in lists_touch table.
  setListTouch($thispid,$text_type);

  if ($text_type == 'football_injury') issue_football_injury_save($issue);
  if ($text_type == 'ippf_gcac'      ) issue_ippf_gcac_save($issue);
  if ($text_type == 'contraceptive'  ) issue_ippf_con_save($issue);

  // If requested, link the issue to a specified encounter.
  if ($thisenc) {
    $query = "INSERT INTO issue_encounter ( " .
      "pid, list_id, encounter " .
      ") VALUES ( ?,?,? )";
    sqlStatement($query, array($thispid,$issue,$thisenc));
  }

  $tmp_title = addslashes($ISSUE_TYPES[$text_type][2] . ": $form_begin " .
    substr($_POST['form_title'], 0, 40));

  // Close this window and redisplay the updated list of issues.
  //
  echo "<html><body><script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";

  echo " var myboss = opener ? opener : parent;\n";
  echo " if (myboss.refreshIssue) myboss.refreshIssue($issue,'$tmp_title');\n";
  echo " else if (myboss.reloadIssues) myboss.reloadIssues();\n";
  echo " else myboss.location.reload();\n";
  echo " if (parent.$ && parent.$.fancybox) parent.$.fancybox.close();\n";
  echo " else window.close();\n";

  echo "</script></body></html>\n";
  exit();
}

$irow = array();
if ($issue)
  $irow = sqlQuery("SELECT * FROM lists WHERE id = ?",array($issue));
else if ($thistype)
  $irow['type'] = $thistype;

$type_index = 0;

if (!empty($irow['type'])) {
  foreach ($ISSUE_TYPES as $key => $value) {
    if ($key == $irow['type']) break;
    ++$type_index;
  }
}
?>
<html>
<head>
<?php html_header_show();?>
<title><?php echo $issue ? xlt('Edit') : xlt('Add New'); ?><?php echo " ".xlt('Issue'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>

td, input, select, textarea {
 font-family: Arial, Helvetica, sans-serif;
 font-size: 10pt;
}

div.section {
 border: solid;
 border-width: 1px;
 border-color: #0000ff;
 margin: 0 0 0 10pt;
 padding: 5pt;
}

</style>

<style type="text/css">@import url(<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.js"></script>
<?php require_once($GLOBALS['srcdir'].'/dynarch_calendar_en.inc.php'); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>

<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 var aitypes = new Array(); // issue type attributes
 var aopts   = new Array(); // Option objects
<?php
 // "Clickoptions" is a feature by Mark Leeds that provides for one-click
 // access to preselected lists of issues in each category.  Here we get
 // the issue titles from the user-customizable file and write JavaScript
 // statements that will build an array of arrays of Option objects.
 //
 $clickoptions = array();
 if (is_file($GLOBALS['OE_SITE_DIR'] . "/clickoptions.txt"))
  $clickoptions = file($GLOBALS['OE_SITE_DIR'] . "/clickoptions.txt");
 $i = 0;
 foreach ($ISSUE_TYPES as $key => $value) {
  echo " aitypes[$i] = " . attr($value[3]) . ";\n";
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

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 // React to selection of an issue type.  This loads the associated
 // shortcuts into the selection list of titles, and determines which
 // rows are displayed or hidden.
 function newtype(index) {
  var f = document.forms[0];
  var theopts = f.form_titles.options;
  theopts.length = 0;
  var i = 0;
  for (i = 0; i < aopts[index].length; ++i) {
   theopts[i] = aopts[index][i];
  }
  document.getElementById('row_titles').style.display = i ? '' : 'none';
  // Show or hide various rows depending on issue type, except do not
  // hide the comments or referred-by fields if they have data.
  var comdisp = (aitypes[index] == 1) ? 'none' : '';
  var revdisp = (aitypes[index] == 1) ? '' : 'none';
  var injdisp = (aitypes[index] == 2) ? '' : 'none';
  var nordisp = (aitypes[index] == 0) ? '' : 'none';
  // reaction row should be displayed only for medication allergy.
  var alldisp =  (index == <?php echo issueTypeIndex('allergy'); ?>) ? '' : 'none';
  document.getElementById('row_enddate'       ).style.display = comdisp;
  // Note that by default all the issues will not show the active row
  //  (which is desired functionality, since then use the end date
  //   to inactivate the item.)
  document.getElementById('row_active'        ).style.display = revdisp;
  document.getElementById('row_diagnosis'     ).style.display = comdisp;
  document.getElementById('row_occurrence'    ).style.display = comdisp;
  document.getElementById('row_classification').style.display = injdisp;
  document.getElementById('row_reinjury_id'   ).style.display = injdisp;
  document.getElementById('row_reaction'      ).style.display = alldisp;
  document.getElementById('row_referredby'    ).style.display = (f.form_referredby.value) ? '' : comdisp;
  document.getElementById('row_comments'      ).style.display = (f.form_comments.value  ) ? '' : revdisp;
<?php if ($GLOBALS['athletic_team']) { ?>
  document.getElementById('row_returndate' ).style.display = comdisp;
  document.getElementById('row_injury_grade'  ).style.display = injdisp;
  document.getElementById('row_injury_part'   ).style.display = injdisp;
  document.getElementById('row_injury_type'   ).style.display = injdisp;
  document.getElementById('row_medical_system').style.display = nordisp;
  document.getElementById('row_medical_type'  ).style.display = nordisp;
  // Change label text of 'title' row depending on issue type:
  document.getElementById('title_diagnosis').innerHTML = '<b>' +
   (index == <?php echo issueTypeIndex('allergy'); ?> ?
   '<?php echo xla('Allergy') ?>' :
   (index == <?php echo issueTypeIndex('general'); ?> ?
   '<?php echo xla('Title') ?>' :
   '<?php echo xla('Text Diagnosis') ?>')) +
   ':</b>';
<?php } else { ?>
  document.getElementById('row_referredby'    ).style.display = (f.form_referredby.value) ? '' : comdisp;
<?php } ?>
<?php
  if ($ISSUE_TYPES['football_injury']) {
    // Generate more of these for football injury fields.
    issue_football_injury_newtype();
  }
  if ($ISSUE_TYPES['ippf_gcac'] && !$_POST['form_save']) {
    // Generate more of these for gcac and contraceptive fields.
    if (empty($issue) || $irow['type'] == 'ippf_gcac'    ) issue_ippf_gcac_newtype();
    if (empty($issue) || $irow['type'] == 'contraceptive') issue_ippf_con_newtype();
  }
?>
 }

 // If a clickoption title is selected, copy it to the title field.
 function set_text() {
  var f = document.forms[0];
  f.form_title.value = f.form_titles.options[f.form_titles.selectedIndex].text;
  f.form_titles.selectedIndex = -1;
 }

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?issue=<?php echo attr($issue) ?>', '_blank', 500, 450);
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
  closeme();
 }

 function closeme() {
    if (parent.$) parent.$.fancybox.close();
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

 // Called when resolved outcome is chosen and the end date is entered.
 function outcomeClicked(cb) {
  var f = document.forms[0];
  if (cb.value == '1'){
   var today = new Date();
   f.form_end.value = '' + (today.getYear() + 1900) + '-' +
    (today.getMonth() + 1) + '-' + today.getDate();
   f.form_end.focus();
  }
 }

// This is for callback by the find-code popup.
// Appends to or erases the current list of diagnoses.
function set_related(codetype, code, selector, codedesc) {
 var f = document.forms[0];
 var s = f.form_diagnosis.value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 f.form_diagnosis.value = s;
}

// This invokes the find-code popup.
function sel_diagnosis() {
 dlgopen('../encounter/find_code_popup.php?codetype=<?php echo attr(collect_codetypes("diagnosis","csv")) ?>', '_blank', 500, 400);
}

// Check for errors when the form is submitted.
function validate() {
 var f = document.forms[0];
 if (! f.form_title.value) {
  alert("<?php echo addslashes(xl('Please enter a title!')); ?>");
  return false;
 }
 top.restoreSession();
 return true;
}

// Supports customizable forms (currently just for IPPF).
function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

</script>

</head>

<body class="body_top" style="padding-right:0.5em">

<form method='post' name='theform'
 action='add_edit_issue.php?issue=<?php echo attr($issue); ?>&thispid=<?php echo attr($thispid); ?>&thisenc=<?php echo attr($thisenc); ?>'
 onsubmit='return validate()'>

<table border='0' width='100%'>

 <tr>
  <td valign='top' width='1%' nowrap><b><?php echo xlt('Type'); ?>:</b></td>
  <td>
<?php
 $index = 0;
 foreach ($ISSUE_TYPES as $value) {
  if ($issue || $thistype) {
    if ($index == $type_index) {
      echo text($value[1]);
      echo "<input type='hidden' name='form_type' value='".attr($index)."'>\n";
    }
  } else {
    echo "   <input type='radio' name='form_type' value='".attr($index)."' onclick='newtype($index)'";
    if ($index == $type_index) echo " checked";
    echo " />" . text($value[1]) . "&nbsp;\n";
  }
  ++$index;
 }
?>
  </td>
 </tr>

 <tr id='row_titles'>
  <td valign='top' nowrap>&nbsp;</td>
  <td valign='top'>
   <select name='form_titles' size='<?php echo $GLOBALS['athletic_team'] ? 10 : 4; ?>' onchange='set_text()'>
   </select> <?php echo xlt('(Select one of these, or type your own title)'); ?>
  </td>
 </tr>

 <tr>
  <td valign='top' id='title_diagnosis' nowrap><b><?php echo $GLOBALS['athletic_team'] ? xlt('Text Diagnosis') : xlt('Title'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_title' value='<?php echo attr($irow['title']) ?>' style='width:100%' />
  </td>
 </tr>

 <tr id='row_diagnosis'>
  <td valign='top' nowrap><b><?php echo xlt('Diagnosis Code'); ?>:</b></td>
  <td>
   <input type='text' size='50' name='form_diagnosis'
    value='<?php echo attr($irow['diagnosis']) ?>' onclick='sel_diagnosis()'
    title='<?php echo xla('Click to select or change diagnoses'); ?>'
    style='width:100%' readonly />
  </td>
 </tr>

 <!-- For Athletic Teams -->

 <tr<?php if (! $GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_injury_grade'>
  <td valign='top' nowrap><b><?php echo xlt('Grade of Injury'); ?>:</b></td>
  <td>
<?php
echo generate_select_list('form_injury_grade', 'injury_grade', $irow['injury_grade'], '');
?>
  </td>
 </tr>

 <tr<?php if (! $GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_injury_part'>
  <td valign='top' nowrap><b><?php echo xlt('Injured Body Part'); ?>:</b></td>
  <td>
<?php
echo generate_select_list('form_injury_part', 'injury_part', $irow['injury_part'], '');
?>
  </td>
 </tr>

 <tr<?php if (! $GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_injury_type'>
  <td valign='top' nowrap><b><?php echo xlt('Injury Type'); ?>:</b></td>
  <td>
<?php
echo generate_select_list('form_injury_type', 'injury_type', $irow['injury_type'], '');
?>
  </td>
 </tr>

 <tr<?php if (! $GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_medical_system'>
  <td valign='top' nowrap><b><?php echo xlt('Medical System'); ?>:</b></td>
  <td>
<?php
echo generate_select_list('form_medical_system', 'medical_system', $irow['injury_part'], '');
?>
  </td>
 </tr>

 <tr<?php if (! $GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_medical_type'>
  <td valign='top' nowrap><b><?php echo xlt('Medical Type'); ?>:</b></td>
  <td>
<?php
echo generate_select_list('form_medical_type', 'medical_type', $irow['injury_type'], '');
?>
  </td>
 </tr>

 <!-- End For Athletic Teams -->

 <tr>
  <td valign='top' nowrap><b><?php echo xlt('Begin Date'); ?>:</b></td>
  <td>

   <input type='text' size='10' name='form_begin' id='form_begin'
    value='<?php echo attr($irow['begdate']) ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='<?php echo xla('yyyy-mm-dd date of onset, surgery or start of medication'); ?>' />
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_begin' border='0' alt='[?]' style='cursor:pointer'
    title='<?php echo xla('Click here to choose a date'); ?>' />
  </td>
 </tr>

 <tr id='row_enddate'>
  <td valign='top' nowrap><b><?php echo xlt('End Date'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_end' id='form_end'
    value='<?php echo attr($irow['enddate']) ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='<?php echo xla('yyyy-mm-dd date of recovery or end of medication'); ?>' />
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_end' border='0' alt='[?]' style='cursor:pointer'
    title='<?php echo xla('Click here to choose a date'); ?>' />
    &nbsp;(<?php echo xlt('leave blank if still active'); ?>)
  </td>
 </tr>

 <tr id='row_active'>
  <td valign='top' nowrap><b><?php echo xlt('Active'); ?>:</b></td>
  <td>
   <input type='checkbox' name='form_active' value='1' <?php echo attr($irow['enddate']) ? "" : "checked"; ?>
    onclick='activeClicked(this);'
    title='<?php echo xla('Indicates if this issue is currently active'); ?>' />
  </td>
 </tr>

 <tr<?php if (! $GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_returndate'>
  <td valign='top' nowrap><b><?php echo xlt('Returned to Play'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_return' id='form_return'
    value='<?php echo attr($irow['returndate']) ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='<?php echo xla('yyyy-mm-dd date returned to play'); ?>' />
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_return' border='0' alt='[?]' style='cursor:pointer'
    title='<?php echo xla('Click here to choose a date'); ?>' />
    &nbsp;(<?php echo xlt('leave blank if still active'); ?>)
  </td>
 </tr>

 <tr id='row_occurrence'>
  <td valign='top' nowrap><b><?php echo xlt('Occurrence'); ?>:</b></td>
  <td>
   <?php
    // Modified 6/2009 by BM to incorporate the occurrence items into the list_options listings
    generate_form_field(array('data_type'=>1,'field_id'=>'occur','list_id'=>'occurrence','empty_title'=>'SKIP'), $irow['occurrence']);
   ?>
  </td>
 </tr>

 <tr id='row_classification'>
  <td valign='top' nowrap><b><?php echo xlt('Classification'); ?>:</b></td>
  <td>
   <select name='form_classification'>
<?php
 foreach ($ISSUE_CLASSIFICATIONS as $key => $value) {
  echo "   <option value='".attr($key)."'";
  if ($key == $irow['classification']) echo " selected";
  echo ">".text($value)."\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr<?php if (! $GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_reinjury_id'>
  <td valign='top' nowrap><b><?php echo xlt('Re-Injury?'); ?>:</b></td>
  <td>
   <select name='form_reinjury_id'>
    <option value='0'><?php echo xlt('No'); ?></option>
<?php
  $pres = sqlStatement(
   "SELECT id, begdate, title " .
   "FROM lists WHERE " .
   "pid = ? AND " .
   "type = 'football_injury' AND " .
   "activity = 1 " .
   "ORDER BY begdate DESC", array($thispid)
  );
  while ($prow = sqlFetchArray($pres)) {
    echo "   <option value='" . attr($prow['id']) . "'";
    if ($prow['id'] == $irow['reinjury_id']) echo " selected";
    echo ">" . text($prow['begdate']) . " " . text($prow['title']) . "\n";
  }
?>
   </select>
  </td>
 </tr>
 <!-- Reaction For Medication Allergy -->
  <tr id='row_reaction'>
   <td valign='top' nowrap><b><?php echo xlt('Reaction'); ?>:</b></td>
   <td>
    <input type='text' size='40' name='form_reaction' value='<?php echo attr($irow['reaction']) ?>'
     style='width:100%' title='<?php echo xla('Allergy Reaction'); ?>' />
   </td>
  </tr>
 <!-- End of reaction -->

 <tr<?php if ($GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_referredby'>
  <td valign='top' nowrap><b><?php echo xlt('Referred by'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_referredby' value='<?php echo attr($irow['referredby']) ?>'
    style='width:100%' title='<?php echo xla('Referring physician and practice'); ?>' />
  </td>
 </tr>

 <tr id='row_comments'>
  <td valign='top' nowrap><b><?php echo xlt('Comments'); ?>:</b></td>
  <td>
   <textarea name='form_comments' rows='4' cols='40' wrap='virtual' style='width:100%'><?php echo text($irow['comments']) ?></textarea>
  </td>
 </tr>

 <tr<?php if ($GLOBALS['athletic_team'] || $GLOBALS['ippf_specific']) echo " style='display:none;'"; ?>>
  <td valign='top' nowrap><b><?php echo xlt('Outcome'); ?>:</b></td>
  <td>
   <?php
    echo generate_select_list('form_outcome', 'outcome', $irow['outcome'], '', '', '', 'outcomeClicked(this);');
   ?>
  </td>
 </tr>

 <tr<?php if ($GLOBALS['athletic_team'] || $GLOBALS['ippf_specific']) echo " style='display:none;'"; ?>>
  <td valign='top' nowrap><b><?php echo xlt('Destination'); ?>:</b></td>
  <td>
<?php if (true) { ?>
   <input type='text' size='40' name='form_destination' value='<?php echo attr($irow['destination']) ?>'
    style='width:100%' title='GP, Secondary care specialist, etc.' />
<?php } else { // leave this here for now, please -- Rod ?>
   <?php echo rbinput('form_destination', '1', 'GP'                 , 'destination') ?>&nbsp;
   <?php echo rbinput('form_destination', '2', 'Secondary care spec', 'destination') ?>&nbsp;
   <?php echo rbinput('form_destination', '3', 'GP via physio'      , 'destination') ?>&nbsp;
   <?php echo rbinput('form_destination', '4', 'GP via podiatry'    , 'destination') ?>
<?php } ?>
  </td>
 </tr>

</table>

<?php
  if ($ISSUE_TYPES['football_injury']) {
    issue_football_injury_form($issue);
  }
  if ($ISSUE_TYPES['ippf_gcac']) {
    if (empty($issue) || $irow['type'] == 'ippf_gcac')
      issue_ippf_gcac_form($issue, $thispid);
    if (empty($issue) || $irow['type'] == 'contraceptive')
      issue_ippf_con_form($issue, $thispid);
  }
?>

<center>
<p>

<input type='submit' name='form_save' value='<?php echo xla('Save'); ?>' />

<?php if ($issue && acl_check('admin', 'super')) { ?>
&nbsp;
<input type='button' value='<?php echo xla('Delete'); ?>' style='color:red' onclick='deleteme()' />
<?php } ?>

&nbsp;
<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />

</p>
</center>

</form>
<script language='JavaScript'>
 newtype(<?php echo $type_index ?>);
 Calendar.setup({inputField:"form_begin", ifFormat:"%Y-%m-%d", button:"img_begin"});
 Calendar.setup({inputField:"form_end", ifFormat:"%Y-%m-%d", button:"img_end"});
 Calendar.setup({inputField:"form_return", ifFormat:"%Y-%m-%d", button:"img_return"});
</script>
</body>
</html>
