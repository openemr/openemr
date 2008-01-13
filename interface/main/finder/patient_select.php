<?php
include_once("../../globals.php");
include_once("$srcdir/patient.inc");

//the maximum number of patient records to display:
$M = 2000;

// this is a quick fix so it doesn't go to thousands records.
// the searching functions on patient.inc need improvement.
if ($patient=='') $patient=xl('Please enter some information','e');
?>

<html>
<head>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

</head>
<body <?php echo $top_bg_line; ?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>
<a href="./patient_select_help.php" target=_new>[Help]&nbsp</a>

<a class="title" href="../main_screen.php" target="_top" onclick="top.restoreSession()">
<?php echo xl('Select Patient') . ' ' . $patient . ' ' . xl('by') . ' ' . xl($findBy); ?></a>

<br>

<table border='0' cellpadding='5' cellspacing='0'>
<tr>
<td>
<span class='bold'><?php xl('Name','e');?></span>
</td><td>
<span class='bold'><?php xl('Phone','e');?></span>
</td><td>
<span class='bold'><?php xl('SS','e');?></span>
</td><td>
<span class='bold'><?php xl('DOB','e');?></span>
</td><td>
<span class='bold'><?php xl('ID','e');?></span>
</td><td>
<span class='bold'><?php xl('[Number Of Encounters]','e');?></span>
</td><td>
<span class='bold'><?php xl('[Days Since Last Encounter]','e');?></span>
</td><td>
<span class='bold'><?php xl('[Date of Last Encounter]','e');?></span>
</td><td>
<?
$add_days = 90;
if (preg_match('/^(\d+)\s*(.*)/',$patient,$matches) > 0) {
  $add_days = $matches[1];
  $patient = $matches[2];
}
?>
<span class=bold>[<?print $add_days?> Days From Last Encounter]</span>
</td></tr>

<?php

$total=0;

if ($findBy == "Last")
	$result = getPatientLnames("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
else if ($findBy == "ID")
	$result = getPatientId("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
else if ($findBy == "DOB")
	$result = getPatientDOB("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
else if ($findBy == "SSN")
	$result = getPatientSSN("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");

if ($result) {
	foreach ($result as $iter) {
		if ($total >= $M) break;
		// For the old layout we load a frameset that also sets up the new pid.
		// The new layout loads just the demographics frame here, which in turn
		// will set the pid and load all the other frames.
		if ($GLOBALS['concurrent_layout']) {
			$anchor = "<a href='../../patient_file/summary/demographics.php?set_pid=" .
				$iter['pid'] . "' class='text' onclick='top.restoreSession()'>";
		} else {
			$anchor = "<a class='text' target='_top' " .
				"href='../../patient_file/patient_file.php?set_pid=" .
				$iter['pid'] . "' onclick='top.restoreSession()'>";
		}
		print "<tr><td>$anchor" . $iter['lname'] . ", " . $iter['fname'] . "</a></td>\n";
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
		print "<td title='$all_other_phones'>$anchor" . $iter['phone_home']. "</a></td>\n";
		print "<td>$anchor" . $iter['ss'] . "</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td>$anchor" . $iter['DOB_TS'] . "</a></td>";
		} else {
			print "<td>$anchor&nbsp;</a></td>";
		}
		print "<td>$anchor" . $iter['pubpid'] . "</a></td>";
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
		print "<td>$encounter_count" . "</a></td>";
		print "<td>$day_diff" . "</a></td>";
		print "<td>$last_date_seen" . "</a></td>";
		print "<td>$next_appt_date" . "</a></td>";
		$total++;
	}
}
?>
</table>
</body>
</html>
