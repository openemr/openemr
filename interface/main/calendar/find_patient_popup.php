<?php
 // Copyright (C) 2005-2007 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 /*
  *
  * This popup is called when adding/editing a calendar event
  *
  */

 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");

 $info_msg = "";

 // If we are searching, search.
 //
 if ($_REQUEST['searchby'] && $_REQUEST['searchparm']) {
  $searchby = $_REQUEST['searchby'];
  $searchparm = $_REQUEST['searchparm'];

  if ($searchby == "Last") {
   $result = getPatientLnames("$searchparm","*");
  } elseif ($searchby == "Phone") {                  //(CHEMED) Search by phone number
   $result = getPatientPhone("$searchparm","*");
  } elseif ($searchby == "ID") {
   $result = getPatientId("$searchparm","*");
  } elseif ($searchby == "DOB") {
   $result = getPatientDOB("$searchparm","*");
  } elseif ($searchby == "SSN") {
   $result = getPatientSSN("$searchparm","*");
  }
 }
?>

<html>
<head>
<?php html_header_show();?>
<title><?php xl('Patient Finder','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

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

/* search results column widths */
.srName { width: 30%; }
.srPhone { width: 21%; }
.srSS { width: 17%; }
.srDOB { width: 17%; }
.srID { width: 15%; }

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
</style>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>

<script language="JavaScript">

 function selpid(pid, lname, fname, dob) {
  if (opener.closed || ! opener.setpatient)
   alert('The destination form was closed; I cannot act on your selection.');
  else
   opener.setpatient(pid, lname, fname, dob);
  window.close();
  return false;
 }

</script>

</head>

<body class="body_top">

<div id="searchCriteria">
<form method='post' name='theform' id="theform" action='find_patient_popup.php?'>
   <?php xl('Search by:','e'); ?>
   <select name='searchby'>
    <option value="Last"><?php xl ('Name','e'); ?></option>
    <!-- (CHEMED) Search by phone number -->
    <option value="Phone"<?php if ($searchby == 'Phone') echo ' selected' ?>><?php xl ('Phone','e'); ?></option>
    <option value="ID"<?php if ($searchby == 'ID') echo ' selected' ?>><?php xl ('ID','e'); ?></option>
    <option value="SSN"<?php if ($searchby == 'SSN') echo ' selected' ?>><?php xl ('SSN','e'); ?></option>
    <option value="DOB"<?php if ($searchby == 'DOB') echo ' selected' ?>><?php xl ('DOB','e'); ?></option>
   </select>
 <?php xl('for:','e'); ?>
   <input type='text' id='searchparm' name='searchparm' size='12' value='<?php echo $_REQUEST['searchparm']; ?>'
    title='<?php xl('If name, any part of lastname or lastname,firstname','e'); ?>'>
   &nbsp;
   <input type='submit' id="submitbtn" value='<?php xl('Search','e'); ?>'>
   <!-- &nbsp; <input type='button' value='<?php xl('Close','e'); ?>' onclick='window.close()' /> -->
   <div id="searchspinner"><img src="<?php echo $GLOBALS['webroot'] ?>/interface/pic/ajax-loader.gif"></div>
</form>
</div>


<?php if (! isset($_REQUEST['searchparm'])): ?>
<div id="searchstatus">Enter your search criteria above</div>
<?php elseif (count($result) == 0): ?>
<div id="searchstatus" class="noResults">No records found. Please expand your search criteria.</div>
<?php elseif (count($result)>=100): ?>
<div id="searchstatus" class="tooManyResults">More than 100 records found. Please narrow your search criteria.</div>
<?php elseif (count($result)<100): ?>
<div id="searchstatus" class="howManyResults"><?php echo count($result); ?> records found.</div>
<?php endif; ?>

<?php if (isset($result)): ?>

<div id="searchResultsHeader">
<table>
 <tr>
  <th class="srName"><?php xl ('Name','e'); ?></th>
  <th class="srPhone"><?php xl ('Phone','e'); ?></th> <!-- (CHEMED) Search by phone number -->
  <th class="srSS"><?php xl ('SS','e'); ?></th>
  <th class="srDOB"><?php xl ('DOB','e'); ?></th>
  <th class="srID"><?php xl ('ID','e'); ?></th>
 </tr>
</table> 
</div>

<div id="searchResults">
<table> 
<?php
foreach ($result as $iter) {
    $iterpid   = $iter['pid'];
    $iterlname = addslashes($iter['lname']);
    $iterfname = addslashes($iter['fname']);
    $itermname = addslashes($iter['mname']);
    $iterdob   = $iter['DOB'];

    // the special genericname2 of 'Billing' means something, but I'm not sure
    // what, regardless it gets special coloring and an extra line of output
    // in the 'name' column -- JRM
    $trClass = "oneresult";
    if ($iter['genericname2'] == 'Billing') { $trClass .= " billing"; }

    echo " <tr class='".$trClass."' id='".$iterpid."~".$iterlname."~".$iterfname."~".$iterdob."'>";
    echo "  <td class='srName'>$iterlname, $iterfname $itermname";
    if ($iter['genericname2'] == 'Billing') { echo "<br>".$iter['genericval2']; }
    echo "</td>\n";
    echo "  <td class='srPhone'>" . $iter['phone_home'] . "</td>\n"; //(CHEMED) Search by phone number
    echo "  <td class='srSS'>" . $iter['ss'] . "</td>\n";
    echo "  <td class='srDOB'>" . $iter['DOB'] . "</td>\n";
    echo "  <td class='srID'>" . $iter['pubpid'] . "</td>\n";
    echo " </tr>";
}
?>
</table>
</div>
<?php endif; ?>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#searchparm").focus();
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").click(function() { SelectPatient(this); });
    //$(".event").dblclick(function() { EditEvent(this); });
    $("#theform").submit(function() { SubmitForm(this); });
});

// show the 'searching...' status and submit the form
var SubmitForm = function(eObj) {
    $("#submitbtn").css("disabled", "true");
    $("#searchspinner").css("visibility", "visible");
    return true;
}


// another way to select a patient from the list of results
// parts[] ==>  0=PID, 1=LName, 2=FName, 3=DOB
var SelectPatient = function (eObj) {
    objID = eObj.id;
    var parts = objID.split("~");
    return selpid(parts[0], parts[1], parts[2], parts[3]);
}

</script>

</center>
</body>
</html>
