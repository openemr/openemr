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

require_once("../../../globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/formdata.inc.php");

use OpenEMR\Core\Header;

$info_msg = "";

$form_action = $_REQUEST['page_action'];
$form_note_type = $_REQUEST['note_type'];
$form_abook_type = $_REQUEST['abook_type'];
$form_allow_multi_select = isset($_REQUEST['allow_multi_select']) ? boolval($_REQUEST['allow_multi_select']) : false;

$whereStr = ''; 
if(!empty($form_abook_type)) {
    $whereStr = " AND abook_type = '$form_abook_type'";
}

 // If we are searching, search.
 //
if ($_REQUEST['searchby'] && $_REQUEST['searchparm']) {
  $searchby = $_REQUEST['searchby'];
  $searchparm = trim($_REQUEST['searchparm']);
  
  $binds = array();
  $query = "SELECT u.* FROM `users` u ";
  $query .= "LEFT JOIN `msg_status` ms ON u.`id` = ms.`user_id` ";
  $query .= "LEFT JOIN `list_options` l ON l.`list_id` = 'msg_status' AND ms.`status` = l.`option_id` ";
  $query .= "WHERE `active` = 1 $whereStr ";
  if ($searchby == "Name") {
    $name = $searchparm.'%';
    $query .= "AND (`fname` LIKE ? OR `lname` LIKE ?) ";
    $query .= "ORDER BY `lname`, `fname`";
    $binds = array($name, $name);
  } else if ($searchby == "Email") {
    $emailparam = '%'.$searchparm.'%';
    $query .= "AND (`email` LIKE ?) ";
    $query .= "ORDER BY `email`";
    $binds = array($emailparam);
  } else if ($searchby == "Organization") {
    $emailparam = '%'.$searchparm.'%';
    $query .= "AND (`organization` LIKE ?) ";
    $query .= "ORDER BY `organization`";
    $binds = array($emailparam);
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
<?php html_header_show();?>
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
    padding: 4px 6px
}
#searchResults, #groupResults  {
    width: 100%;
    overflow: auto;
}

/* search results column widths */
.srName { width: 20%;text-align:left; }
.srOrg { width: 17%;text-align:left; }
.srEmail { width: 20%;text-align:left; }
.srFullAddress { width: 33%;text-align:left; }

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
    padding: 4px 6px;
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
 function seluid(uid, lname, fname, username, status, noteType = '', email = '') {
  if (opener.closed || ! opener.setuser)
   alert("<?php echo xlt('The destination form was closed; I cannot act on your selection.'); ?>");
  else
   opener.setuser(uid, lname, fname, username, status, noteType, email);
   dlgclose();
  return false;
 }

 function selMultiUid(userList = []) {
    if (opener.closed || ! opener.setMultiuser)
        alert("<?php echo xlt('The destination form was closed; I cannot act on your selection.'); ?>");
    else
        opener.setMultiuser(userList);
        dlgclose();
    return false;
 }

</script>

</head>

<body class="body_top">

<div id="searchCriteria">
<form method='post' name='theform' id="theform" action='find_user_popup.php?<?php if(isset($_GET['pflag'])) echo "pflag=0"; ?>'>
    <input type="hidden" name="page_action" value="<?php echo $form_action; ?>">
    <input type="hidden" name="abook_type" value="<?php echo $form_abook_type; ?>">
    <input type="hidden" name="allow_multi_select" value="<?php echo $form_allow_multi_select; ?>">
   <?php echo xlt('Search by:'); ?>
   <select name='searchby'>
    <option value="Name"><?php echo xlt('Name'); ?></option>
    <option value="Email"><?php echo xlt('Email'); ?></option>
    <option value="Organization"><?php echo xlt('Organization'); ?></option>
   </select>
 <?php echo xl('for:'); ?>:
   <input type='text' id='searchparm' name='searchparm' size='12' value='<?php echo attr($_REQUEST['searchparm']); ?>'
    title='<?php echo xla('If name, any part of first or last name'); ?>'>
   &nbsp;
   <input type='submit' id="submitbtn" value='<?php echo xla('Search'); ?>'>
   
    <?php if($form_allow_multi_select === true) { ?>
        <input type='button' id="selectsubmitbtn" value='<?php echo xla('Submit'); ?>'>
    <?php } ?>
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
  <?php if($form_allow_multi_select === true) { ?>
    <th class="selCheck"></th>
  <?php } ?>
  <th class="srName"><?php echo xlt('Name'); ?></th>
  <th class="srOrg"><?php echo xlt('Organization'); ?></th>
  <th class="srEmail"><?php echo xlt('Email'); ?></th>
  <th class="srFullAddress"><?php echo xlt('Full Address'); ?></th>
 </tr>
</table> 
</div>

<div id="searchResults">
<table> 
<?php
$jdataSet = [];
while ($iter = sqlFetchArray($result)) {
    $iteruid   = $iter['id'];
    $iterlname = $iter['lname'];
    $iterfname = $iter['fname'];
    $iteruser  = $iter['username'];
    $iteremail  = $iter['email'];
    $iterstatus  = (empty($iter['title']))? '-unknown-' : $iter['title'];
    $iterAddr = array();

    if(!empty($iter['street'])) {
        $iterAddr[] = $iter['street'];
    }

    if(!empty($iter['streetb'])) {
        $iterAddr[] = $iter['streetb'];
    }

    if(!empty($iter['city'])) {
        $iterAddr[] = $iter['city'];
    }

    if(!empty($iter['state'])) {
        $iterAddr[] = $iter['state'];
    }

    if(!empty($iter['zip'])) {
        $iterAddr[] = $iter['zip'];
    }

    if(!empty($iterAddr)) {
        $iterAddr = implode(", ", $iterAddr);
    } else {
        $iterAddr = '';
    }
    
    if($form_allow_multi_select === true) {
        if(empty(trim($iter['email']))) {
            continue;
        }
        
        $jdataSet['u_'.$iteruid] = $iter;
        echo " <tr data-id='". $iteruid ."'>";
        echo "  <td><input type='checkbox' class='sel_check' name='check_".$iteruid."' value='".$iteruid."' /></td>";
        echo "  <td class='srName'>" . text($iterfname." ".$iterlname) . "</td>\n";
        echo "  <td class='srOrg'>" . text($iter['organization']) . "</td>\n";
        echo "  <td class='srEmail'>" . text($iter['email']) . "</td>\n";
        echo "  <td class='srFullAddress'>" . text($iterAddr) . "</td>\n";
        echo " </tr>";
    } else {
        $trClass = "oneresult";
        echo " <tr class='".$trClass."' id='" .
            attr( $iteruid."~".$iterlname."~".$iterfname."~".$iteruser."~".$iterstatus."~".$iteremail."~") . "'>";
        echo "  <td class='srName'>" . text($iterfname." ".$iterlname);
        echo "</td>\n";
        echo "  <td class='srOrg'>" . text($iter['organization']) . "</td>\n";
        echo "  <td class='srEmail'>" . text($iter['email']) . "</td>\n";
        echo "  <td class='srFullAddress'>" . text($iterAddr) . "</td>\n";
        echo " </tr>";
    }
}
?>
</table>
</div>

<?php endif; ?>

<script>
// jQuery stuff to make the page a little easier to use

var jDataSet = <?php echo json_encode($jdataSet); ?>;

$(document).ready(function(){
    $("#searchparm").focus();
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").click(function() { SelectUser(this); });
    $(".noresult").click(function () { SubmitForm(this);});

    $("#theform").submit(function() { SubmitForm(this); });


    //Handle Mulituser select
    $("#selectsubmitbtn").click(function() {
        let selectedItemList = [];
        $(".sel_check:checked").each(function(){
            let uId = $(this).val();
            if(jDataSet.hasOwnProperty('u_'+uId)) {
                selectedItemList.push(jDataSet['u_'+uId]);
            }
        });

        if(selectedItemList.length <= 0) {
            alert('Please select users from the list.');
            return false;
        }

        selMultiUid(selectedItemList);
    });
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
    objID = eObj.id;
    var parts = objID.split("~");
    var name = parts[2]+" "+parts[1];
    return seluid(parts[0], name, name, parts[3], parts[4], noteType, parts[5]);
}

</script>

</body>
</html>
