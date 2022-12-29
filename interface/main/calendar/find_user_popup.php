<?php
/* Copyright (C) 2005-2007 Rod Roark <rod@sunsetsystems.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

/*
 *
 * This popup is called when adding/editing a calendar event
 *
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/formdata.inc.php");

use OpenEMR\Core\Header;

$info_msg = "";

$form_action = $_REQUEST['page_action'];
$form_note_type = $_REQUEST['note_type'];

 // If we are searching, search.
 //
if ($_REQUEST['searchby'] && $_REQUEST['searchparm']) {
  $searchby = $_REQUEST['searchby'];
  $searchparm = trim($_REQUEST['searchparm']);
  
  $binds = array();
  $query = "SELECT `id`, `fname`, `lname`, `username`, `status`, l.`title` FROM `users` u ";
  $query .= "LEFT JOIN `msg_status` ms ON u.`id` = ms.`user_id` ";
  $query .= "LEFT JOIN `list_options` l ON l.`list_id` = 'msg_status' AND ms.`status` = l.`option_id` ";
  $query .= "WHERE `username` != '' AND `active` = 1 ";
  if ($searchby == "Name") {
  	$name = $searchparm.'%';
    $query .= "AND (`fname` LIKE ? OR `lname` LIKE ?) ";
    $query .= "ORDER BY `lname`, `fname`";
    $binds = array($name, $name);
  } else {
    $query .= "AND `username` = ? ";
    $query .= "ORDER BY `username`";
    $binds = array($searchparm);
  }

  $result = sqlStatement($query, $binds);
}
?>

<html>
<head>
<?php //html_header_show();?>
<title><?php echo xlt('Employee Finder'); ?></title>
<?php Header::setupHeader(['dialog', 'opener', 'main_theme', 'main-theme', 'jquery']); ?>

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
#searchResultsHeader, #groupResultsHeader { 
    width: 100%;
    background-color: lightgrey;
    text-align:left;
}
#searchResultsHeader table, #groupResultsHeader table { 
    width: 100%;  /* not 100% because the 'searchResults' table has a scrollbar */
    border-collapse: collapse;
}
#searchResultsHeader th, #groupResultsHeader th  {
    font-size: 0.7em;
}
#searchResults, #groupResults  {
    width: 100%;
    overflow: auto;
}

/* search results column widths */
.srName { width: 30%;text-align:left; }
.srUsername { width: 21%;text-align:left; }
.srStatus { width: 17%;text-align:left; }

#searchResults table, #groupResults table {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
}
#searchResults tr, #groupResults tr  {
    cursor: hand;
    cursor: pointer;
}
#searchResults td, #groupResults td {
    font-size: 0.7em;
    border-bottom: 1px solid #eee;
}
.oneResult { }
.billing { color: red; font-weight: bold; }

/* for search results or 'searching' notification */
#searchstatus {
    font-size: 0.8em;
    font-weight: bold;
    padding: 1px 1px 10px 1px;
    font-style: italic;
    color: black;
    text-align: center;
}
.noResults { background-color: #ccc; }
.tooManyResults { background-color: #fc0; }
.howManyResults { background-color: #9f6; }
#searchspinner { 
    display: inline;
    visibility: hidden;
}

/* highlight for the mouse-over */
.highlight {
    background-color: #336699;
    color: white;
}

.form_note_type_selection {
  margin-top: 25px;
  margin-bottom: 20px;
}

.form_note_type_selection select {
  height: 25px;
}
</style>

<!-- ViSolve: Verify the noresult parameter -->
<?php
if(isset($_GET["res"])){
echo '
<script language="Javascript">
			// Pass the variable to parent hidden type and submit
			// opener.document.theform.resname.value = "noresult";
			// opener.document.theform.submit();
			// Close the window
			dlg.close();
</script>';
}
?>
<!-- ViSolve: Verify the noresult parameter -->

<script language="JavaScript">
 function seluid(uid, lname, fname, username, status, noteType = '') {
  if (opener.closed || ! opener.setuser)
   alert("<?php echo xlt('The destination form was closed; I cannot act on your selection.'); ?>");
  else
   opener.setuser(uid, lname, fname, username, status, noteType);
   dlgclose();
  return false;
 }

</script>

</head>

<body class="body_top">

<div id="searchCriteria">
<form method='post' name='theform' id="theform" action='find_user_popup.php?<?php if(isset($_GET['pflag'])) echo "pflag=0"; ?>'>
    <input type="hidden" name="page_action" value="<?php echo $form_action; ?>">
   <?php echo xlt('Search by:'); ?>
   <select name='searchby'>
    <option value="Name"><?php echo xlt('Name'); ?></option>
    <option value="Username"<?php if ($searchby == 'Username') echo ' selected' ?>><?php echo xlt('Username'); ?></option>
   </select>
 <?php echo xl('for:'); ?>:
   <input type='text' id='searchparm' name='searchparm' size='12' value='<?php echo attr($_REQUEST['searchparm']); ?>'
    title='<?php echo xla('If name, any part of first or last name'); ?>'>
   &nbsp;
   <input type='submit' id="submitbtn" value='<?php echo xla('Search'); ?>'>
   <!-- &nbsp; <input type='button' value='<?php echo htmlspecialchars( xl('Close'), ENT_QUOTES); ?>' onclick='window.close()' /> -->
   <div id="searchspinner"><img src="<?php echo $GLOBALS['webroot'] ?>/interface/pic/ajax-loader.gif"></div>
</form>
</div>

<?php if (! isset($_REQUEST['searchparm'])): ?>
<div id="searchstatus"><?php echo xlt('Enter your search criteria above'); ?></div>
<?php elseif (count($result) == 0): ?>
<div id="searchstatus" class="noResults"><?php echo xlt('No records found. Please expand your search criteria or choose a group.'); ?>
</div>
<?php elseif (count($result)>=100): ?>
<div id="searchstatus" class="tooManyResults"><?php echo xlt('More than 100 records found. Please narrow your search criteria.'); ?></div>
<?php elseif (count($result)<100): ?>
<div id="searchstatus" class="howManyResults"><?php echo text(sqlNumRows($result)); ?> <?php echo xlt('records found.'); ?></div>
<?php endif; ?>

<?php if (isset($result)): ?>

<div id="searchResultsHeader">
<table>
 <tr>
  <th class="srName"><?php echo xlt('Name'); ?></th>
  <th class="srUsername"><?php echo xlt('Username'); ?></th>
  <th class="srStatus"><?php echo xlt('Status'); ?></th>
 </tr>
</table> 
</div>

<div id="searchResults">
<table> 
<?php
while ($iter = sqlFetchArray($result)) {
    $iteruid   = $iter['id'];
    $iterlname = $iter['lname'];
    $iterfname = $iter['fname'];
    $iteruser  = $iter['username'];
    $iterstatus  = (empty($iter['title']))? '-unknown-' : $iter['title'];
    
    $trClass = "oneresult";
    echo " <tr class='".$trClass."' id='" .
        attr( $iteruid."~".$iterlname."~".$iterfname."~".$iteruser."~".$iterstatus."~") . "'>";
    echo "  <td class='srName'>" . text($iterfname." ".$iterlname);
    echo "</td>\n";
    echo "  <td class='srUsername'>" . text($iter['username']) . "</td>\n";
    echo "  <td class='srStatus'>" . text($iter['title']) . "</td>\n";
    echo " </tr>";
}
?>
</table>
</div>

<?php endif; ?>

<?php if($form_action == "assign") { ?>
<div class="form_note_type_selection">
<span>Note Type: </span>
<select name="form_note_type" id="form_note_type" class="" onchange="CheckTemplate(this);">
   <?php
    $ures=sqlStatement("Select option_id, title, codes FROM list_options ".
        "WHERE list_id='note_type' ORDER BY seq");
    echo "<option value='Message Board'";
    if ($form_note_type == 'Message Board') echo " selected";
    echo ">Message Board</option>";
    while($urow=sqlFetchArray($ures)) {
      echo "<option value='" . htmlspecialchars( $urow['option_id'], ENT_QUOTES) . "'";
      if ($urow['option_id'] == $form_note_type) echo " selected";
      echo '>'. htmlspecialchars( $urow['title'], ENT_NOQUOTES);
      echo "</option>\n";
    }
   ?>
</select>
</div>
<?php } ?>

<div id="searchGroupsHeader">
<table style="margin-top:10px">
 <tr>
  <th class="srName" style='text-align:left'><?php echo xlt('Messaging Groups'); ?></th>
 </tr>
</table> 
</div>

<div id="groupResults">
<table style="width:100%"> 
<?php
$result = sqlStatement("SELECT * FROM `list_options` WHERE `list_id` = 'Messaging_Groups' ");
while ($iter = sqlFetchArray($result)) {
    $trClass = "oneresult";
    echo " <tr class='".$trClass."' id='" .
        attr("0~".$iter['title']."~~".$iter['option_id']."~~") . "'>";
    echo "  <td class='srName'>" . text($iter['title']);
    echo "</td>\n";
    echo " </tr>";
}
?>
</table>
</div>

<script>

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#searchparm").focus();
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").click(function() { SelectUser(this); });
    $(".noresult").click(function () { SubmitForm(this);});

    $("#theform").submit(function() { SubmitForm(this); });

});

// show the 'searching...' status and submit the form
var SubmitForm = function(eObj) {
    $("#submitbtn").css("disabled", "true");
    $("#searchspinner").css("visibility", "visible");
    return true;
}

var checkNoteType = function() {
    var noteType = $( "#form_note_type option:selected" ).val();
    if(noteType == "") {
      alert("Please select note type.");
    }
    return noteType;
}


// another way to select a patient from the list of results
// parts[] ==>  0=UID, 1=LName, 2=FName, 3=username 4=status
var SelectUser = function (eObj) {
    var noteType = "";

    <?php if($form_action == "assign") { ?>
      noteType = checkNoteType();
      if(noteType == "") {
        return false;
      }
    <?php } ?>

    objID = eObj.id;
    var parts = objID.split("~");
    var name = parts[2]+" "+parts[1];
    return seluid(parts[0], name, parts[3], parts[4], noteType);
}

</script>

</body>
</html>
