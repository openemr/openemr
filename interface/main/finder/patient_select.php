<?
include_once("../../globals.php");
include_once("$srcdir/patient.inc");


//the maximum number of patient records to display:
$M = 100;
?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<a class="title" href="../main_screen.php" target="_top">Select Patient: <?echo $patient;?> by <?echo $findBy;?></a>


<br>

<table border=0 cellpadding=5 cellspacing=0>
<tr>
<td>
<span class=bold>Name</span>
</td><td>
<span class=bold>SS</span>
</td><td>
<span class=bold>DOB</span>
</td><td>
<span class=bold>ID</span>
</td></tr>
<?

$count=0;
$total=0;

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




?>
</table>


</body>
</html>
