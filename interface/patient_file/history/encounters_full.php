<?
include_once("../../globals.php");

include_once("$srcdir/forms.inc");
include_once("$srcdir/billing.inc");
include_once("$srcdir/pnotes.inc");
include_once("$srcdir/patient.inc");

?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">


</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>





<a href="patient_history.php" target="Main"><font class="title">Past Encounters</font><font class=back><?echo $tback;?></font></a><br>


<table>
<th>
<td><span class=bold>Date</span></td>
<td><span class=bold>Reason</span></td>
<td><span class=bold>Comments</span></td>
<td><span class=bold>Billing</span></td>
<td><span class=bold>Insurance</span></td>
</th>

<?
$count = 0;
if ($result = getEncounters($pid)) {
	foreach ($result as $iter ) {
		$count++;
		
		$subresult = getFormByEncounter($pid, $iter{"encounter"}, "*");
		$raw_encounter_date = date( "Y-m-d" ,strtotime($subresult[0]{"date"}));
		$encounter_date = date( "D F jS" ,strtotime($subresult[0]{"date"}));
		
		print "<tr><td></td>";
		
		print "<td valign=top width=100><a target=Main href=\"javascript:parent.Title.location.href='../encounter/encounter_title.php?set_encounter=".$iter{"encounter"}."';parent.Main.location.href='../encounter/patient_encounter.php?set_encounter=".$iter{"encounter"}."'\" class=text>" . $encounter_date . "</a></td>";
		
		///////////
		$reason_string = "";
		if ($result4 = sqlQuery("select * from form_encounter where encounter='".$iter{"encounter"}."' and pid='$pid'")) {
			
			$reason_string .= $result4{"reason"} . "<br>\n";
			
		}
		
		print "<td valign=top width=23%><a target=Main href=\"javascript:parent.Title.location.href='../encounter/encounter_title.php?set_encounter=".$iter{"encounter"}."';parent.Main.location.href='../encounter/patient_encounter.php?set_encounter=".$iter{"encounter"}."'\" class=text>" . $reason_string . "</a></td>";
		
		///////////
		$comments = "";
		if ($subresult3 = getPnotesByDate(date( "Y-m-d" ,strtotime($subresult[0]{"date"})), "all", "*", $pid, 5, 0)){
			//this is where we print out short headers for comments enterred into the patient file on the same date
			
			
			foreach ($subresult3 as $iter3) {
				$comments .= stripslashes(strterm($iter3{"body"},100)) . "<br>";
				
			}
			$comments = substr($comments,0,strlen($comments)-4);
		}
		
		print "<td valign=top width=23%><a target=Main href=\"javascript:parent.Title.location.href='../encounter/encounter_title.php?set_encounter=".$iter{"encounter"}."';parent.Main.location.href='../encounter/patient_encounter.php?set_encounter=".$iter{"encounter"}."'\" class=text>" . $comments . "</a></td>";
		
		
		///////////
		$coded = "";
		if ($subresult2 = getBillingByEncounter($pid,$iter{"encounter"})) {
			//this is where we print out the text of the billing that occurred on this encounter
			foreach ($subresult2 as $iter2) {
				$coded .= $iter2{"code_text"} . ", ";
				
				
			}
			$coded = substr($coded,0,strlen($coded)-2);
		}
		
		print "<td valign=top width=23%><a target=Main href=\"javascript:parent.Title.location.href='../encounter/encounter_title.php?set_encounter=".$iter{"encounter"}."';parent.Main.location.href='../encounter/patient_encounter.php?set_encounter=".$iter{"encounter"}."'\" class=text>" . $coded . "</a></td>";
		
		///////////
		$insured = "$raw_encounter_date";
		if ($subresult5 = getInsuranceDataByDate( $pid, $raw_encounter_date, "primary")) {
			$insured = "<span class=text>Primary: ".$subresult5{"provider"}."</span><br>\n";
		}
		if ($subresult6 = getInsuranceDataByDate( $pid, $raw_encounter_date, "secondary")) {
			$insured .= "<span class=text>Secondary: ".$subresult6{"provider"}."</span><br>\n";
		}
		if ($subresult7 = getInsuranceDataByDate( $pid, $raw_encounter_date, "tertiary")) {
			$insured .= "<span class=text>Tertiary: ".$subresult7{"provider"}."</span><br>\n";
		}
		
		
		
		print "<td valign=top width=23%><a target=Main href=\"javascript:parent.Title.location.href='../report/report_title.php?set_encounter=".$iter{"encounter"}."';parent.Main.location.href='../report/patient_report.php?set_encounter=".$iter{"encounter"}."'\" class=text>" . $insured . "</a></td>";
		
		print "</tr>\n";
		
	}
}

?>


</table>


</body>
</html>