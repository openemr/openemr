<?php
include_once("../../globals.php");
include_once("$srcdir/patient.inc");

//the maximum number of patient records to display:
$M = 100;

$patient = $_REQUEST['patient'];
$findBy  = $_REQUEST['findBy'];

// this is a quick fix so it doesn't go to thousands records.
// the searching functions on patient.inc need improvement.
if ($patient=='') $patient=xl('Please enter some information','e');
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

.srName { width: 12%; }
.srPhone { width: 11%; }
.srSS { width: 11%; }
.srDOB { width: 11%; }
.srID { width: 11%; }
.srNumEnc { width: 11%; }
.srNumDays { width: 11%; }
.srDateLast { width: 11%; }
.srDateNext { width: 11%; }

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
#tooManyResults {
    font-size: 0.8em;
    font-weight: bold;
    padding: 1px 1px 10px 1px;
    font-style: italic;
    color: black;
    background-color: #fc0;
}
#howManyResults {
    font-size: 0.8em;
    font-weight: bold;
    padding: 1px 1px 10px 1px;
    font-style: italic;
    color: black;
    background-color: #9f6;
}
.highlight { 
    background-color: #336699;
    color: white;
}
</style>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>

</head>
<body class="body_top">
<a href="./patient_select_help.php" target=_new>[Help]&nbsp</a>

<!-- This link seems to have a misleading name since it just returns you to the main_screen 
Perhaps it applies to new layouts, not the old original one?
<a class="title" href="../main_screen.php" target="_top" onclick="top.restoreSession()"> -->
<?php echo xl('Select Patient') . ' ' . $patient . ' ' . xl('by') . ' ' . xl($findBy); ?>
<!-- </a> -->

<?php
if ($findBy == "Last")
    $result = getPatientLnames("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
else if ($findBy == "ID")
    $result = getPatientId("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
else if ($findBy == "DOB")
    $result = getPatientDOB("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
else if ($findBy == "SSN")
    $result = getPatientSSN("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
?>

<?php if (count($result)>=100): ?>
<span id="tooManyResults">More than 100 records found. Please narrow your search criteria.</span>
<?php else: ?>
<span id="howManyResults"><?php echo count($result); ?> records found.</span>
<?php endif; ?>

<br>

<div id="searchResultsHeader">
<table>
<tr>
<th class="srName"><?php xl('Name','e');?></th>
<th class="srPhone"><?php xl('Phone','e');?></th>
<th class="srSS"><?php xl('SS','e');?></th>
<th class="srDOB"><?php xl('DOB','e');?></th>
<th class="srID"><?php xl('ID','e');?></th>
<th class="srNumEnc"><?php xl('[Number Of Encounters]','e');?></th>
<th class="srNumDays"><?php xl('[Days Since Last Encounter]','e');?></th>
<th class="srDateLast"><?php xl('[Date of Last Encounter]','e');?></th>
<th class="srDateNext">
<?php
$add_days = 90;
if (preg_match('/^(\d+)\s*(.*)/',$patient,$matches) > 0) {
  $add_days = $matches[1];
  $patient = $matches[2];
}
?>
[<?php echo $add_days?> Days From Last Encounter]
</th>
</tr>
</table>
</div>

<div id="searchResults">

<table>
<tr>
<?php
$total=0;
if ($result) {
    foreach ($result as $iter) {
        if ($total >= $M) break;

        echo "<tr class='oneresult' id='".$iter['pid']."'>";
        echo  "<td class='srName'>" . $iter['lname'] . ", " . $iter['fname'] . "</td>\n";
        //other phone number display setup for tooltip
        $phone_biz = '';
        if ($iter{"phone_biz"} != "") {
            $phone_biz = " [business phone ".$iter{"phone_biz"}."] ";
        }
        $phone_contact = '';
        if ($iter{"phone_contact"} != "") {
            $phone_contact = " [contact phone ".$iter{"phone_contact"}."] ";
        }
        $phone_cell = '';
        if ($iter{"phone_cell"} != "") {
            $phone_cell = " [cell phone ".$iter{"phone_cell"}."] ";
        }
        $all_other_phones = $phone_biz.$phone_contact.$phone_cell;
        if ($all_other_phones == '') {$all_other_phones = 'No other phone numbers listed';}
        //end of phone number display setup, now display the phone number(s)
        echo "<td class='srPhone' title='$all_other_phones'>" . $iter['phone_home']. "</td>\n";
        echo "<td class='srSS'>" . $iter['ss'] . "</td>";
        if ($iter{"DOB"} != "0000-00-00 00:00:00") {
            echo "<td class='srDOB'>" . $iter['DOB_TS'] . "</td>";
        } else {
            echo "<td class='srDOB'>&nbsp;</td>";
        }
        echo "<td class='srID'>" . $iter['pubpid'] . "</td>";
        //setup for display of encounter date info
        $encounter_count = 0;
        $day_diff = ''; 
        $last_date_seen = ''; 
        $next_appt_date= ''; 
        $pid = '';

        //calculate date differences based on date of last cpt4 entry
        $query = "select DATE_FORMAT(date(max(form_encounter.date)),'%m/%d/%y') as mydate," .
                " (to_days(current_date())-to_days(max(form_encounter.date))) as day_diff," . 
                " DATE_FORMAT(date(max(form_encounter.date)) + interval " . $add_days . 
                " day,'%m/%d/%y') as next_appt, dayname(max(form_encounter.date) + interval " . 
                $add_days." day) as next_appt_day from form_encounter join billing on (billing.encounter = form_encounter.encounter) where billing.code_type". 
                " like 'CPT4' and form_encounter.pid=" . $iter{"pid"}; 
        $statement= sqlStatement($query);
        if ($results = mysql_fetch_array($statement, MYSQL_ASSOC)) {
            $last_date_seen = $results['mydate']; 
            $day_diff = $results['day_diff'];
            $next_appt_date= $results['next_appt_day'].', '.$results['next_appt'];
        }

        //calculate count of encounters by distinct billing dates with cpt4
        //entries
        $query = "select count(distinct date) as encounter_count " . 
                "from billing where code_type like 'CPT4' and activity=1 " . 
                "and pid=".$iter{"pid"}; 
        $statement= sqlStatement($query);
        if ($results = mysql_fetch_array($statement, MYSQL_ASSOC)) {
            $encounter_count = $results['encounter_count']; 
        }
        echo "<td class='srNumEnc'>".$encounter_count."</td>";
        echo "<td class='srNumDay'>".$day_diff."</td>";
        echo "<td class='srDateLast'>".$last_date_seen."</td>";
        echo "<td class='srDateNext'>".$next_appt_date."</td>";
        echo "\n";
        $total++;
    }
}
?>
</table>
</div>  <!-- end searchResults DIV -->

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#searchparm").focus();
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").click(function() { SelectPatient(this); });
    //$(".event").dblclick(function() { EditEvent(this); });
});

var SelectPatient = function (eObj) {
<?php 
// For the old layout we load a frameset that also sets up the new pid.
// The new layout loads just the demographics frame here, which in turn
// will set the pid and load all the other frames.
if ($GLOBALS['concurrent_layout']) {
    $newPage = "../../patient_file/summary/demographics.php?set_pid=";
    $target = "document";
} else {
    $newPage = "../../patient_file/patient_file.php?set_pid=";
    $target = "top";
}
?>
    objID = eObj.id;
    var parts = objID.split("~");
    <?php echo $target; ?>.location.href = '<?php echo $newPage; ?>' + parts[0];
    return true;
}

</script>

</body>
</html>
