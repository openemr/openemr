<?php
	// Copyright (C) 2010 OpenEMR Support LLC  
	// This program is free software; you can redistribute it and/or
	// modify it under the terms of the GNU General Public License
	// as published by the Free Software Foundation; either version 2
	// of the License, or (at your option) any later version.
	
	//SANITIZE ALL ESCAPES
	$sanitize_all_escapes=true;
	
	//STOP FAKE REGISTER GLOBALS
	$fake_register_globals=false;

	require_once("../globals.php");
	require_once("$srcdir/patient.inc");
	require_once("$srcdir/options.inc.php");
	require_once("../drugs/drugs.inc.php");
	
	$form_facility = htmlspecialchars(isset($_POST['form_facility']) ? $_POST['form_facility'] : '', ENT_QUOTES);
	$form_from_date = htmlspecialchars(fixDate($_POST['form_from_date'], date('Y-01-01')), ENT_QUOTES);
	$form_to_date = htmlspecialchars(fixDate($_POST['form_to_date']  , date('Y-m-d')), ENT_QUOTES);
	$form_provider = htmlspecialchars(trim($_POST["form_provider"]), ENT_QUOTES);
	
	$sqlBindArray = array();

	if ($_POST['form_refresh']) {
		
		$sql = "SELECT 
			patient_data.id,
			concat(patient_data.fname, ' ', patient_data.lname) AS patient_name,
			concat(patient_data.street, ' ', patient_data.city, ' ', patient_data.state, ' ', patient_data.postal_code) AS address, 
			patient_data.sex,
			patient_data.DOB,
			patient_data.SS,
			GROUP_CONCAT(DISTINCT billing.date SEPARATOR '<br>') AS DOS,
			GROUP_CONCAT(insurance_companies.name SEPARATOR '<br>') AS insurance_company_name,
			GROUP_CONCAT(insurance_data.policy_number SEPARATOR '<br>') AS insurance_policy,
			GROUP_CONCAT(DISTINCT billing.code SEPARATOR ', ') AS CPT4_ICD9,
			GROUP_CONCAT(DISTINCT form_soap.assessment SEPARATOR ', ') AS assessment,
			GROUP_CONCAT(DISTINCT billing.encounter SEPARATOR '<br>') AS enc
			FROM billing 
			INNER JOIN patient_data ON patient_data.id = billing.pid 
			LEFT OUTER JOIN insurance_data ON insurance_data.pid = billing.pid AND insurance_data.provider != ''
			LEFT OUTER JOIN insurance_companies ON insurance_companies.id = insurance_data.provider
			INNER JOIN form_soap ON form_soap.pid = billing.pid 
			INNER JOIN users ON users.id = billing.provider_id 
			INNER JOIN facility ON users.facility_id = facility.id
			WHERE billing.code_type != 'COPAY'
			AND billing.date  >= ?
			AND billing.date <= DATE_ADD(?, INTERVAL 1 DAY)
		";
		array_push($sqlBindArray, $form_from_date, $form_to_date);
		
		if(strlen($form_facility) > 0){
			$sql .= " AND facility.id = ?";
			array_push($sqlBindArray, $form_facility);
		}
		
		//$sql .= " GROUP BY patient_data.id";
		$sql .= " GROUP BY billing.encounter";
	}

	if ($_POST['form_refresh'] == "export"){
		$result = sqlStatement($sql, $sqlBindArray);
		
		$out = "First/Last Name, Sex, DOB, SS, DOS, Street/State/Zip, Insurance Company, Policy#, CPT Codes, ICD9 Codes, MD Assessment\n";
		
		while($row = sqlFetchArray($result))
		{
			$out .= "{$row['patient_name']}, {$row['sex']}, {$row['DOB']}, {$row['SS']}, {$row['DOS']}, {$row['address']}, {$row['patient_insurance_company']}, {$row['policy_number']}, {$row['CPT4']}, {$row['ICD9']}, {$row['assessment']}\n";
		}
		
		$dt_str = date("Ymd");
		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Length: " . strlen($out));
		header("Content-type: text/x-csv");
		header("Content-type: application/csv");
		header("Content-Disposition: attachment; filename=billing_enc_summary_".$dt_str.".csv");
		echo $out;
		exit;
	}
?>
<html>
<head>
<?php html_header_show();?>
<title>
<?php echo htmlspecialchars(xl('Clinical Reports','e'), ENT_QUOTES) ?>
</title>
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 // The OnClick handler for receipt display.
 function show_receipt(payid) {
  // dlgopen('../patient_file/front_payment.php?receipt=1&payid=' + payid, '_blank', 550, 400);
  return false;
 }

</script>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<style type="text/css">
/* specifically include & exclude from printing */
@media print {
#report_parameters {
	visibility: hidden;
	display: none;
}
#report_parameters_daterange {
	visibility: visible;
	display: inline;
}
#report_results table {
	margin-top: 0px;
}
}

/* specifically exclude some from the screen */
@media screen {
#report_parameters_daterange {
	visibility: hidden;
	display: none;
}
}

.optional_area {
	<?php
	if($type != 'Prescription' || $type == '')
	{
	?>
	display: none;
	<?php
	}
	?>
}
</style>
<script language="javascript" type="text/javascript">
	function checkType(){
		if($('#type').val() == 'Prescription'){
			$('.optional_area').css("display", "inline");
		}
		else{
			$('.optional_area').css("display", "none");
		}
	}
</script>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<span class='title'>
<?php echo htmlspecialchars(xl('Report - Billing Services/Encounter Summary'), ENT_QUOTES) ?>
</span>
<!-- Search can be done using age range, gender, and ethnicity filters.
Search options include diagnosis, procedure, prescription, medical history, and lab results.
-->
<div id="report_parameters_daterange"> <?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?> </div>

<form method='post' name='theform' id='theform' action='<?php echo $_SERVER['REQUEST_URI'] ?>'>

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

<div id="report_parameters">
<table>
 <tr>
  <td width='550px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'>
				<?php echo htmlspecialchars(xl('Facility'), ENT_QUOTES) ?>:
			</td>
			<td>
				<?php
				 // Build a drop-down list of facilities.
				 //
				 $query = "SELECT id, name FROM facility ORDER BY name";
				 $fres = sqlStatement($query);
				 echo "   <select name='form_facility'>\n";
				 echo "    <option value=''>-- " . htmlspecialchars(xl('All Facilities'), ENT_QUOTES) . " --\n";
				 while ($frow = sqlFetchArray($fres)) {
				  $facid = $frow['id'];
				  echo "    <option value='$facid'";
				  if ($facid == $form_facility) echo " selected";
				  echo ">" . htmlspecialchars($frow['name'], ENT_QUOTES) . "\n";
				 }
				 echo "    <option value='0'";
				 if ($form_facility === '0') echo " selected";
				 echo ">-- " . htmlspecialchars(xl('Unspecified'), ENT_QUOTES) . " --\n";
				 echo "   </select>\n";
				?>
			</td>
			<td class='label'>
			   <?php echo htmlspecialchars(xl('Provider'), ENT_QUOTES) ?>:
			</td>
			<td>
				<?php

				 // Build a drop-down list of providers.
				 //

				 $query = "SELECT id, lname, fname FROM users WHERE ".
				  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

				 $ures = sqlStatement($query);

				 echo "   <select name='form_provider'>\n";
				 echo "    <option value=''>-- " . htmlspecialchars(xl('All'), ENT_QUOTES) . " --\n";

				 while ($urow = sqlFetchArray($ures)) {
				  $provid = htmlspecialchars($urow['id'], ENT_QUOTES);
				  echo "    <option value='$provid'";
				  if ($provid == $_POST['form_provider']) echo " selected";
				  echo ">" . htmlspecialchars($urow['lname'] . ", " . $urow['fname'], ENT_QUOTES) . "\n";
				 }

				 echo "   </select>\n";

				?>
			</td>
			<td>&nbsp;
			</td>
		</tr>
		<tr>
			<td class='label'>
			   <?php echo htmlspecialchars(xl('From'), ENT_QUOTES) ?>:
			</td>
			<td>
			   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php echo htmlspecialchars(xl('Click here to choose a date'), ENT_QUOTES) ?>'>
			</td>
			<td class='label'>
			   <?php echo htmlspecialchars(xl('To'), ENT_QUOTES) ?>:
			</td>
			<td>
			   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php echo htmlspecialchars(xl('Click here to choose a date'), ENT_QUOTES) ?>'>
			</td>
			<td>
			   
			</td>
		</tr>
	</table>

	</div>

  </td>
  <td align='left' valign='middle' height="100%">
	<table style='border-left:1px solid; width:100%; height:100%' >
		<tr>
			<td>
				<div style='margin-left:15px'>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php echo htmlspecialchars(xl('Submit'), ENT_QUOTES) ?>
					</span>
					</a>

					<?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php echo htmlspecialchars(xl('Print'), ENT_QUOTES) ?>
						</span>
					</a>
					<?php } ?>
					
					<?php if ($_POST['form_refresh']) { ?>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","export"); $("#theform").submit();'>
						<span>
							<?php echo htmlspecialchars(xl('Export'), ENT_QUOTES) ?>
						</span>
					</a>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>

</div> <!-- end report_parameters -->

<?php

// SQL scripts for the various searches
if ($_POST['form_refresh']) 
{	
	$result = sqlStatement($sql, $sqlBindArray);

	if(sqlNumRows($result) > 0)
	{
?>
		<div id="report_results">
			<table>
				<thead>
					<th><?php echo htmlspecialchars(xl('First/Last Name'), ENT_QUOTES) ?></th>
					<th> <?php echo htmlspecialchars(xl('Sex'), ENT_QUOTES) ?></th>
					<th> <?php echo htmlspecialchars(xl('DOB'), ENT_QUOTES) ?></th>
					<th> <?php echo htmlspecialchars(xl('SS'), ENT_QUOTES) ?></th>
					<th> <?php echo htmlspecialchars(xl('DOS'), ENT_QUOTES) ?></th>
					<th> <?php echo htmlspecialchars(xl('Street/State/Zip'), ENT_QUOTES) ?></th>
					<th> <?php echo htmlspecialchars(xl('Insurance Company'), ENT_QUOTES) ?></th>
					<th> <?php echo htmlspecialchars(xl('Policy#'), ENT_QUOTES) ?></th>
					<th> <?php echo htmlspecialchars(xl('CPT4/ICD9 Codes'), ENT_QUOTES) ?></th>
					<th>&nbsp;</th>
				</thead>
				<tbody>
				<?php
					while($row = sqlFetchArray($result))
					{
					?>
					<tr>
						<td style="border: none;"> <?php echo $row['patient_name'] ?>&nbsp;</td>
						<td style="border: none;"> <?php echo $row['sex'] ?>&nbsp;</td>
						<td style="border: none;"> <?php echo $row['DOB'] ?>&nbsp;</td>
						<td style="border: none;"> <?php echo $row['SS'] ?>&nbsp;</td>
						<td style="border: none;"> <?php echo $row['DOS'] ?>&nbsp;</td>
						<td style="border: none;"> <?php echo $row['address'] ?>&nbsp;</td>
						<td style="border: none;"> <?php echo $row['insurance_company_name'] ?>&nbsp;</td>
						<td style="border: none;"> <?php echo $row['insurance_policy'] ?>&nbsp;</td>
						<td style="border: none;"> <?php echo $row['CPT4_ICD9'] ?>&nbsp;</td>
						<td style="border: none;">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="11" valign="top">
						<form>
							<strong><?php echo htmlspecialchars(xl('MD Assessment'), ENT_QUOTES) ?>:</strong><br>  <textarea name="" cols="100" rows="3" readonly="readonly"><?php echo $row['assessment']?></textarea>
						</form>
						</td>
					</tr>
				<?php
					}
				?>
				</tbody>
			</table>
		</div>
		<!-- end of results -->
	<?php
	}
	?>
<?php 
}
else
{
	?><div class='text'> <?php echo htmlspecialchars(xl('Please input search criteria above, and click Submit to view results.'), ENT_QUOTES) ?> </div><?php
}
?>
</form>
</body>

<!-- stuff for the popup calendar -->
<style type="text/css">
@import url(../../library/dynarch_calendar.css);
</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>
</html>
