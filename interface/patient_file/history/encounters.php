<?
include_once("../../globals.php");

include_once("$srcdir/forms.inc");
include_once("$srcdir/billing.inc");
include_once("$srcdir/pnotes.inc");
include_once("$srcdir/patient.inc");

//maximum number of encounter entries to display on this page:
$N = 10;

?>
<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<a href="encounters_full.php" target="Main"><font class="title">Past Encounters</font><font class=more><?echo $tmore;?></font></a><br>

<table width="100%">
<tr>
<td><span class=bold>Date</span></td>
<td><span class=bold>Reason</span></td>
<td><span class=bold><? echo ($GLOBALS['phone_country_code'] == '1') ? 'Billing' : 'Coding' ?></span></td>
<td><span class=bold>Insurance</span></td>
</tr>

<?
$count = 0;
if ($result = getEncounters($pid)) {
	foreach ($result as $iter ) {
		$count++;
		if ($count >= $N) {
			//we have more encounters to print, but we've reached our display maximum
			print "<tr><td colspan='4' align='center'><a target='Main' href='encounters_full.php' class='alert'>Some encounters were not displayed. Click here to view all.</a></td></tr>\n";
			break;
		}

/****
		$subresult = getFormByEncounter($pid, $iter{"encounter"}, "*", "New Patient Encounter");
		$raw_encounter_date = date( "Y-m-d" ,strtotime($subresult[0]{"date"}));
		$encounter_date = date( "D F jS" ,strtotime($subresult[0]{"date"}));
		print "<tr><td></td>";
		print "<td valign=top width=100><a target=Main href=\"javascript:parent.Title.location.href='../encounter/encounter_title.php?set_encounter=".$iter{"encounter"}."';parent.Main.location.href='../encounter/patient_encounter.php?set_encounter=".$iter{"encounter"}."'\" class=text>" . $encounter_date . "</a></td>";
****/

		$reason_string = "";
		if ($result4 = sqlQuery("select * from form_encounter where encounter='" . $iter{"encounter"} . "' and pid='$pid'")) {
			$raw_encounter_date = date("Y-m-d", strtotime($result4{"date"}));
			$encounter_date = date("D F jS", strtotime($result4{"date"}));
			$reason_string .= $result4{"reason"} . "<br>\n";
		}

		print "<tr>\n";
		print "<td valign='top'><a target='Main' href=\"javascript:parent.Title.location.href='../encounter/encounter_title.php?set_encounter=".$iter{"encounter"}."';parent.Main.location.href='../encounter/patient_encounter.php?set_encounter=".$iter{"encounter"}."'\" class='text'>" . $encounter_date . "</a></td>\n";
		print "<td valign='top'><a target='Main' href=\"javascript:parent.Title.location.href='../encounter/encounter_title.php?set_encounter=".$iter{"encounter"}."';parent.Main.location.href='../encounter/patient_encounter.php?set_encounter=".$iter{"encounter"}."'\" class='text'>" . $reason_string . "</a></td>\n";

/****
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
****/

		$coded = "";
		if ($subresult2 = getBillingByEncounter($pid,$iter{"encounter"})) {
			//this is where we print out the text of the billing that occurred on this encounter
			foreach ($subresult2 as $iter2) {
				$coded .= "<span title='" . addslashes($iter2{"code_text"}) . "'>";
				$coded .= $iter2{"code"} . "</span>, ";
			}
			$coded = substr($coded, 0, strlen($coded) - 2);
		}

		print "<td valign='top'><a target='Main' href=\"javascript:parent.Title.location.href='../encounter/encounter_title.php?set_encounter=".$iter{"encounter"}."';parent.Main.location.href='../encounter/patient_encounter.php?set_encounter=".$iter{"encounter"}."'\" class='text'>" . $coded . "</a></td>\n";

		$insured = "$raw_encounter_date";
		$subresult5 = getInsuranceDataByDate($pid, $raw_encounter_date, "primary");
		if ($subresult5 && $subresult5{"provider_name"}) {
			$insured = "<span class='text'>Primary: " . $subresult5{"provider_name"} . "</span><br>\n";
		}
		$subresult6 = getInsuranceDataByDate($pid, $raw_encounter_date, "secondary");
		if ($subresult6 && $subresult6{"provider_name"}) {
			$insured .= "<span class='text'>Secondary: ".$subresult6{"provider_name"}."</span><br>\n";
		}
		$subresult7 = getInsuranceDataByDate($pid, $raw_encounter_date, "tertiary");
		if ($subresult6 && $subresult7{"provider_name"}) {
			$insured .= "<span class='text'>Tertiary: ".$subresult7{"provider_name"}."</span><br>\n";
		}

		print "<td valign='top'><a target='Main' href=\"javascript:parent.Title.location.href='../report/report_title.php?set_encounter=".$iter{"encounter"}."';parent.Main.location.href='../report/patient_report.php?set_encounter=".$iter{"encounter"}."'\" class='text'>" . $insured . "</a></td>\n";

		print "</tr>\n";
	}
}

?>

</table>

</body>
</html>
