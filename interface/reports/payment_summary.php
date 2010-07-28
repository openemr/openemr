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

	$form_payment_method = htmlspecialchars($_POST['form_method'], ENT_QUOTES);
	$form_facility = htmlspecialchars($_POST['form_facility'], ENT_QUOTES);
	$form_use_date = htmlspecialchars($_POST['form_use_date'], ENT_QUOTES);
	$form_from_date = htmlspecialchars(fixDate( $_POST['form_from_date'], date('Y-01-01')), ENT_QUOTES);
	$form_to_date = htmlspecialchars(fixDate( $_POST['form_to_date'], date('Y-m-d')), ENT_QUOTES);
	
	// patient_data.language = race
	// payments.dtime = DOS
	
	$sqlBindArray = array();
	
	$sql = "
SELECT concat( patient_data.fname, ' ', patient_data.lname ) AS patient_name,
   patient_data.id,
   patient_data.sex,
   DATE_FORMAT( FROM_DAYS( DATEDIFF( NOW( ) , patient_data.DOB ) ) , '%Y' ) +0 AS age,
   patient_data.DOB, 
   payments.dtime AS DOS,
   patient_data.city,
   patient_data.state AS county,
   patient_data.ethnoracial AS race,
   patient_data.postal_code AS zip,
   payments.method AS method,
   payments.amount1 + payments.amount2 AS total_amount
	FROM patient_data,
   payments,
   users,
   facility
	where patient_data.pid = payments.pid
	and  payments.user = users.username 	
	and  users.facility_id  = facility.id
	and  payments.dtime   >= ? 
	and payments.dtime <= DATE_ADD(?, INTERVAL 1 DAY)";
	
	array_push($sqlBindArray, $form_from_date, $form_to_date);
	
	if($form_payment_method != '-- All --'){
		$sql .= " and  payments.method   = ?";
		array_push($sqlBindArray, $form_payment_method);
	}

	if ( strlen($form_facility) > 0 ){   
		$sql .= " and facility.id = ?";
		array_push($sqlBindArray, $form_facility);
	}
	
	//echo $sql;
	
	if ($_POST['form_refresh'] == "export"){
		$result = sqlStatement($sql, $sqlBindArray);
		
		$out = "First/Last Name, Sex, Age, Race, DOB, DOS, City, County, Zip, Payment Method\n";
		
		while($row = sqlFetchArray($result)){
			$out .= "{$row['patient_name']}, {$row['sex']}, {$row['age']}, {$row['race']}, {$row['DOB']}, {$row['DOS']}, {$row['city']}, {$row['county']}, {$row['zip']}, {$row['method']}\n";
		}
		
		$dt_str = date("Ymd");
		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Length: " . strlen($out));
		header("Content-type: text/x-csv");
		header("Content-type: application/csv");
		header("Content-Disposition: attachment; filename=payment_summary_".$dt_str.".csv");
		echo $out;
		exit;
	}
?>
<html>
<head>
<?php html_header_show();?>
<title>
<?php echo htmlspecialchars(xl('Clinical Reports'), ENT_QUOTES) ?>
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
 <?php  if($type != 'Prescription' || $type == '') {
 ?>  display: none;
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
<?php echo htmlspecialchars(xl('Report - Facility/Prov Payment Summary'), ENT_QUOTES) ?>
</span> 
<!-- Search can be done using age range, gender, and ethnicity filters.
Search options include diagnosis, procedure, prescription, medical history, and lab results.
-->
<div id="report_parameters_daterange"> <?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?> </div>
<form method='post' id='theform' action='<?php echo $_SERVER['REQUEST_URI'] ?>'>
	<div id="report_parameters">
		<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
		<table>
			<tr>
				<td width='630px'><div style='float:left'>
						<table class='text'>
							<tr>
								<td class='label'> <?php echo htmlspecialchars(xl('Payment Method'), ENT_QUOTES) ?>: </td>
								<td><select name='form_method'>
										<?php
									$payment_methods = array(
									  htmlspecialchars(xl('-- All --'), ENT_QUOTES),
									  htmlspecialchars(xl('Cash'), ENT_QUOTES),
									  htmlspecialchars(xl('Check'), ENT_QUOTES),
									  htmlspecialchars(xl('MC'), ENT_QUOTES),
									  htmlspecialchars(xl('VISA'), ENT_QUOTES),
									  htmlspecialchars(xl('AMEX'), ENT_QUOTES),
									  htmlspecialchars(xl('DISC'), ENT_QUOTES),
									  htmlspecialchars(xl('Other'), ENT_QUOTES));
									  
								  foreach ($payment_methods as $value) {
									echo "    <option value='$value'";
									if ($value == $form_payment_method) echo " selected";
									echo ">$value</option>\n";
								  }
								?>
									</select></td>
								<td><?php echo htmlspecialchars(xl('Facility'), ENT_QUOTES) ?>: </td>
								<td colspan="3"><?php
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
								echo "   </select>\n";
								?>&nbsp;</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><select name='form_use_edate'>
										<option value='0'>
										<?php echo htmlspecialchars(xl('Payment Date'), ENT_QUOTES) ?>
										</option>
										<option value='1'<?php if ($form_use_edate) echo ' selected' ?>>
										<?php echo htmlspecialchars(xl('Invoice Date'), ENT_QUOTES) ?>
										</option>
									</select></td>
								<td><?php echo htmlspecialchars(xl('From'), ENT_QUOTES) ?>:</td>
								<td>
								<table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td>
											<input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
										</td>
										<td>
											<img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo htmlspecialchars(xl('Click here to choose a date'), ENT_QUOTES) ?>'>
										</td>
									</tr>
								</table>
								</td>
								<td class='label'><?php echo htmlspecialchars(xl('To'), ENT_QUOTES) ?>: </td>
								<td>
								<table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td>
											<input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
										</td>
										<td>
											<img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo htmlspecialchars(xl('Click here to choose a date'), ENT_QUOTES); ?>'>
										</td>
									</tr>
								</table>
								</td>
							</tr>
						</table>
				</div></td>
				<td align='left' valign='middle' height="100%"><table style='border-left:1px solid; width:100%; height:100%' >
						<tr>
							<td><div style='margin-left:15px'> <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'> <span>
									<?php echo htmlspecialchars(xl('Submit'), ENT_QUOTES) ?>
									</span> </a>
									<?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
									<a href='#' class='css_button' onclick='window.print()'> <span>
									<?php echo htmlspecialchars(xl('Print'), ENT_QUOTES) ?>
									</span> </a>
									<?php } ?>
									<?php if ($_POST['form_refresh']) { ?>
									<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","export"); $("#theform").submit();'> <span>
									<?php echo htmlspecialchars(xl('Export'), ENT_QUOTES) ?>
									</span> </a>
									<?php } ?>
								</div></td>
						</tr>
					</table></td>
			</tr>
		</table>
	</div>
	<!-- end of parameters -->
	
	<?php

// SQL scripts for the various searches
if ($_POST['form_refresh']) {	
	$result = sqlStatement($sql, $sqlBindArray);

	if(sqlNumRows($result) > 0){
?>
	<div id="report_results">
		<table>
			<thead>
			<th><?php echo htmlspecialchars(xl('First/Last Name'), ENT_QUOTES) ?></th>
				<th> <?php echo htmlspecialchars(xl('Sex'), ENT_QUOTES) ?></th>
				<th> <?php echo htmlspecialchars(xl('Age'), ENT_QUOTES) ?></th>
				<th> <?php echo htmlspecialchars(xl('Race'), ENT_QUOTES) ?></th>
				<th> <?php echo htmlspecialchars(xl('DOB'), ENT_QUOTES) ?></th>
				<th> <?php echo htmlspecialchars(xl('DOS'), ENT_QUOTES) ?></th>
				<th> <?php echo htmlspecialchars(xl('City'), ENT_QUOTES) ?></th>
				<th> <?php echo htmlspecialchars(xl('County'), ENT_QUOTES) ?></th>
				<th> <?php echo htmlspecialchars(xl('Zip'), ENT_QUOTES) ?></th>
				<th> <?php echo htmlspecialchars(xl('Payment Amount'), ENT_QUOTES) ?></th>
				<th> <?php echo htmlspecialchars(xl('Payment Method'), ENT_QUOTES) ?></th>
					</thead>
			<tbody>
				<?php
					$total = 0;
					while($row = sqlFetchArray($result)){
						$total += (float)$row['total_amount'];
					?>
				<tr>
					<td><?php echo htmlspecialchars($row['patient_name'], ENT_QUOTES); ?>
						&nbsp;</td>
					<td><?php echo htmlspecialchars($row['sex'], ENT_QUOTES) ?>
						&nbsp;</td>
					<td><?php echo htmlspecialchars($row['age'], ENT_QUOTES) ?>
						&nbsp;</td>
					<td><?php echo htmlspecialchars($row['race'], ENT_QUOTES) ?>
						&nbsp;</td>
					<td><?php echo htmlspecialchars($row['DOB'], ENT_QUOTES) ?>
						&nbsp;</td>
					<td><?php echo htmlspecialchars($row['DOS'], ENT_QUOTES) ?>
						&nbsp;</td>
					<td><?php echo htmlspecialchars($row['city'], ENT_QUOTES) ?>
						&nbsp;</td>
					<td><?php echo htmlspecialchars($row['county'], ENT_QUOTES) ?>
						&nbsp;</td>
					<td><?php echo htmlspecialchars($row['zip'], ENT_QUOTES) ?>
						&nbsp;</td>
					<td><?php echo htmlspecialchars($row['total_amount'], ENT_QUOTES) ?>
						&nbsp;</td>
					<td><?php echo htmlspecialchars($row['method'], ENT_QUOTES) ?>
						&nbsp;</td>
				</tr>
				<?php
					}
				?>
				<tr style="border: 2px solid #000;">
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td><strong><?php echo htmlspecialchars(xl('Total Amount'), ENT_QUOTES) ?></strong>&nbsp;</td>
					<td><?php echo htmlspecialchars(number_format($total, 2), ENT_QUOTES) ?>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
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
	?>
	<div class='text'> <?php echo htmlspecialchars(xl('Please input search criteria above, and click Submit to view results.'), ENT_QUOTES) ?> </div>
	<?php
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
