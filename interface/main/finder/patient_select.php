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

<a class="title" href="../main_screen.php" target="_top"><?php echo xl('Select Patient') . ' ' . $patient . ' ' . xl('by') . ' ' . xl($findBy); ?></a>

<br>

<table border='0' cellpadding='5' cellspacing='0'>
<tr>
<td>
<span class='bold'><?php xl('Name','e');?></span>
</td><td>
<span class='bold'><?php xl('SS','e');?></span>
</td><td>
<span class='bold'><?php xl('DOB','e');?></span>
</td><td>
<span class='bold'><?php xl('ID','e');?></span>
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
				$iter['pid'] . "' class='text'>";
		} else {
			$anchor = "<a class='text' target='_top' " .
				"href='../../patient_file/patient_file.php?set_pid=" .
				$iter['pid'] . "'>";
		}
		print "<tr><td>$anchor" . $iter['lname'] . ", " . $iter['fname'] . "</a></td>\n";
		print "<td>$anchor" . $iter['ss'] . "</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td>$anchor" . $iter['DOB_TS'] . "</a></td>";
		} else {
			print "<td>$anchor&nbsp;</a></td>";
		}
		print "<td>$anchor" . $iter['pubpid'] . "</a></td>";
		$total++;
	}
}


/****
if ($findBy == "Last" && $result = getPatientLnames("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {
		
		if ($total >= $M) {
			break;
		}
		print "<tr><td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"lname"}.", ".$iter{"fname"}."</td></a>\n";
		print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"ss"}."</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter["DOB_TS"]."</a></td>";
		} else {
			print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>&nbsp;</a></td>";
		}
		print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"pubpid"}."</a></td>";
		
		$total++;
	}
}

if ($findBy == "ID" && $result = getPatientId("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {
		
		if ($total >= $M) {
			break;
		}
		print "<tr><td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"lname"}.", ".$iter{"fname"}."</td></a>\n";
		print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"ss"}."</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter["DOB_TS"]."</a></td>";
		} else {
			print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>&nbsp;</a></td>";
		}
		print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"pubpid"}."</a></td>";
		
		$total++;
	}
}

if ($findBy == "DOB" && $result = getPatientDOB("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {
		
		if ($total >= $M) {
			break;
		}
		print "<tr><td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"lname"}.", ".$iter{"fname"}."</td></a>\n";
		print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"ss"}."</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter["DOB_TS"]."</a></td>";
		} else {
			print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>&nbsp;</a></td>";
		}
		print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"pubpid"}."</a></td>";
		
		$total++;
	}
}

if ($findBy == "SSN" && $result = getPatientSSN("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {
		
		if ($total >= $M) {
			break;
		}
		print "<tr><td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"lname"}.", ".$iter{"fname"}."</td></a>\n";
		print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"ss"}."</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter["DOB_TS"]."</a></td>";
		} else {
			print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>&nbsp;</a></td>";
		}
		print "<td><a class=text target=_top href='../../patient_file/patient_file.php?set_pid=".$iter{"pid"}."'>".$iter{"pubpid"}."</a></td>";
		
		$total++;
	}
}

****/


?>
</table>

</body>
</html>
