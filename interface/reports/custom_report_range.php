<?
include_once(dirname(__file__)."/../globals.php");


include_once("$srcdir/forms.inc");
include_once("$srcdir/billing.inc");
include_once("$srcdir/pnotes.inc");
include_once("$srcdir/patient.inc");
include_once("$srcdir/report.inc");
include_once("$srcdir/classes/Document.class.php");
include_once("$srcdir/classes/Note.class.php");
?>
<html>
	<head>
	
	
	<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
	
	
	</head>
	
	<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
	
	
<?

if(empty($_POST['start']) || empty($_POST['end'])){
	?>
	<form method="post" action="custom_report_range.php">
		<table>
		<tr><td><? xl('Start Date','e'); ?>:</td><td><input type="text" name="start" value="" size="10"/></td><td>YYYYMMDD</td></tr>
		<tr><td><? xl('End Date','e'); ?>:</td><td><input type="text" name="end" value="" size="10"/></td><td>YYYYMMDD</td></tr>
		<tr><td><input type="submit" value="Submit"/></td><td></td></tr>
		</table>
	</form>
	<?
}else{		
	$sql = "select * from facility where billing_location = 1";
	$db = $GLOBALS['adodb']['db'];
	$results = $db->Execute($sql);
	$facility = array();
	if (!$results->EOF) {
		$facility = $results->fields;
			?>
		<p>
		<h2><?=$facility['name']?></h2>
		<?=$facility['street']?><br>
		<?=$facility['city']?>, <?=$facility['state']?> <?=$facility['postal_code']?><br>
	
		</p>
		<? 	
	} 
	

  $res = sqlStatement("select * from forms where " .
    "form_name = 'New Patient Encounter' and " .
    "date between '$start' and '$end' " .
    "order by date DESC");
	while($result = sqlFetchArray($res)) {
		if ($result{"form_name"} == "New Patient Encounter") {
			$newpatient[] = $result{"form_id"}.":".$result{"encounter"};
			$pids[] = $result{"pid"};
		}
	}
	$N = 6;
	function postToGet($newpatient, $pids) {
		$getstring="";
		$serialnewpatient = serialize($newpatient);
		$serialpids = serialize($pids);
		$getstring = "newpatient=".urlencode($serialnewpatient)."&pids=".urlencode($serialpids);
		
		return $getstring;
	}
	
	
	$iCounter = 0;
	if(empty($newpatient)){
		$newpatient = array();
	}
	foreach($newpatient as $patient){
		/*	
		$inclookupres = sqlStatement("select distinct formdir from forms where pid='".$pids[$iCounter]."'");
		while($result = sqlFetchArray($inclookupres)) {
			include_once("{$GLOBALS['incdir']}/forms/" . $result{"formdir"} . "/report.php");
		}
		*/
		
		print "<font class=bold>".xl('Patient Data').":</font><br>";
		printRecDataOne($patient_data_array, getRecPatientData ($pids[$iCounter]), $N);
		
		print "<font class=bold>".xl('Primary Insurance Data').":</font><br>";
		printRecDataOne($insurance_data_array, getRecInsuranceData ($pids[$iCounter],"primary"), $N);		
		print "<font class=bold>".xl('Secondary Insurance Data').":</font><br>";	
		printRecDataOne($insurance_data_array, getRecInsuranceData ($pids[$iCounter],"secondary"), $N);
					
		print "<font class=bold>".xl('Tertiary Insurance Data').":</font><br>";
		printRecDataOne($insurance_data_array, getRecInsuranceData ($pids[$iCounter],"tertiary"), $N);
		
		print "<font class=bold>".xl('Billing Information').":</font><br>";
		if (count($patient) > 0) {
			$billings = array();
			echo "<table>";
			echo "<tr><td class=bold>".xl('Date')."</td><td width=\"200\" class=bold>".xl('Provider')."</td><td width=\"400\" class=bold>".xl('Code')."</td><td class=bold>".xl('Fee')."</td></tr>\n";
			$total = 0.00;
			$copays = 0.00;
			//foreach ($patient as $be) {
							
				$ta = split(":",$patient);
				$billing = getPatientBillingEncounter($pids[$iCounter],$ta[1]);
				
				$billings[] = $billing;
				foreach ($billing as $b) {
					echo "<tr>\n";
					echo "<td class=text>" . $b['date'] . "</td>";
					echo "<td class=text>" . $b['provider_name'] . "</td>";
					echo "<td class=text>";
					echo $b['code_type'] . ":\t" . $b['code'] . "&nbsp;". $b['modifier'] . "&nbsp;&nbsp;&nbsp;" . $b['code_text'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					echo "</td>\n";
					echo "<td class=text>";
					echo $b['fee'];
					echo "</td>\n";
					echo "</tr>\n";
					$total += $b['fee'];
					if ($b['code_type'] == "COPAY") {
						$copays += $b['fee'];
					}
							
				}
			//} 
			echo "<tr><td>&nbsp;</td></tr>";
			echo "<tr><td class=bold>".xl('Sub-Total')."</td><td class=text>" . sprintf("%0.2f",$total + abs($copays)) . "</td></tr>";
			echo "<tr><td class=bold>".xl('Paid')."</td><td class=text>" . sprintf("%0.2f",abs($copays)) . "</td></tr>";
			echo "<tr><td class=bold>".xl('Total')."</td><td class=text>" . sprintf("%0.2f",$total) . "</td></tr>";
			echo "</table>";
			echo "<pre>";
			//print_r($billings);
			echo "</pre>";
		}
		++$iCounter;
		print "<br/><br/>".xl('Physician Signature').":  _______________________________________________<br/><br/><br/>";
		print "<hr width=\"100%\" />";
	}
		
	
}	
	?>
	
	
	
	
	
	
	
	
	</body>
	</html>
