<?php
 // Copyright (C) 2006, 2010 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists prescriptions and their dispensations according
 // to various input selection criteria.
 //
 // Fixed drug name search to work in a broader sense - tony@mi-squared.com 2010
 // Added several reports as per EHR certification requirements for Patient Lists - OpenEMR Support LLC, 2010

	require_once("../globals.php");
	require_once("$srcdir/patient.inc");
	require_once("$srcdir/options.inc.php");
	require_once("../drugs/drugs.inc.php");

	$form_payment_method = $_POST['form_method'];
	$form_facility = $_POST['form_facility'];
	$form_use_date = $_POST['form_use_date'];
	$form_from_date = fixDate( $_POST['form_from_date'], date('Y-01-01'));
	$form_to_date = fixDate( $_POST['form_to_date'], date('Y-m-d'));
	
	// patient_data.language = race
	// payments.dtime = DOS
	
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
   payments.method AS method
	FROM patient_data,
   payments,
   users,
   facility
	where patient_data.pid = payments.pid
	and  payments.user = users.username 	
	and  users.facility_id  = facility.id
	and  payments.dtime   >= '$form_from_date' 
	and payments.dtime <= DATE_ADD('$form_to_date', INTERVAL 1 DAY)
	and  payments.method   = '".$form_payment_method."'";

	if ( strlen($form_facility) > 0 )
	{   
		$sql .= " and facility.id = '$form_facility'";
	}
	
	if ($_POST['form_refresh'] == "export")
	{
		$result = sqlStatement($sql);
		
		$out = "First/Last Name, Sex, Age, Race, DOB, DOS, City, County, Zip, Payment Method\n";
		
		while($row = sqlFetchArray($result))
		{
			$out .= "{$row['patient_name']}, {$row['sex']}, {$row['age']}, {$row['race']}, {$row['DOB']}, {$row['DOS']}, {$row['city']}, {$row['county']}, {$row['zip']}, {$row['method']}\n";
		}
		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Length: " . strlen($out));
		header("Content-type: text/x-csv");
		header("Content-type: application/csv");
		header("Content-Disposition: attachment; filename=report_export.csv");
		echo $out;
		exit;
	}
?>
<html>
<head>
<?php html_header_show();?>
<title>
<?php xl('Clinical Reports','e'); ?>
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
	function checkType()
	{
		if($('#type').val() == 'Prescription')
		{
			$('.optional_area').css("display", "inline");
		}
		else
		{
			$('.optional_area').css("display", "none");
		}
	}
</script>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<span class='title'>
<?php xl('Report - Facility Billing','e'); ?>
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
								<td class='label'> <?php xl('Payment Method', 'e'); ?>: </td>
								<td><select name='form_method'>
										<?php
					$payment_methods = array(
					  xl('Cash'),
					  xl('Check'),
					  xl('MC'),
					  xl('VISA'),
					  xl('AMEX'),
					  xl('DISC'),
					  xl('Other'));
					  
				  foreach ($payment_methods as $value) {
					echo "    <option value='$value'";
					if ($value == $form_payment_method) echo " selected";
					echo ">$value</option>\n";
				  }
				?>
									</select></td>
								<td><?php xl('Facility','e'); ?>: </td>
								<td><?php
								// Build a drop-down list of facilities.
								//
								$query = "SELECT id, name FROM facility ORDER BY name";
								$fres = sqlStatement($query);
								echo "   <select name='form_facility'>\n";
								echo "    <option value=''>-- " . xl('All Facilities') . " --\n";
								while ($frow = sqlFetchArray($fres)) {
								  $facid = $frow['id'];
								  echo "    <option value='$facid'";
								  if ($facid == $form_facility) echo " selected";
								  echo ">" . htmlspecialchars($frow['name']) . "\n";
								}
								echo "   </select>\n";
								?>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><select name='form_use_edate'>
										<option value='0'>
										<?php xl('Payment Date','e'); ?>
										</option>
										<option value='1'<?php if ($form_use_edate) echo ' selected' ?>>
										<?php xl('Invoice Date','e'); ?>
										</option>
									</select></td>
								<td><?php xl('From', 'e'); ?>:</td>
								<td><input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
									<img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'></td>
								<td class='label'><?php xl('To','e'); ?>
									: </td>
								<td><input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
									<img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'></td>
							</tr>
						</table>
				</div></td>
				<td align='left' valign='middle' height="100%"><table style='border-left:1px solid; width:100%; height:100%' >
						<tr>
							<td><div style='margin-left:15px'> <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'> <span>
									<?php xl('Submit','e'); ?>
									</span> </a>
									<?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
									<a href='#' class='css_button' onclick='window.print()'> <span>
									<?php xl('Print','e'); ?>
									</span> </a>
									<?php } ?>
									<?php if ($_POST['form_refresh']) { ?>
									<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","export"); $("#theform").submit();'> <span>
									<?php xl('Export','e'); ?>
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
if ($_POST['form_refresh']) 
{	
	$result = sqlStatement($sql);

	if(sqlNumRows($result) > 0)
	{
?>
	<div id="report_results">
		<table>
			<thead>
			<th><?php xl('First/Last Name','e'); ?></th>
				<th> <?php xl('Sex','e'); ?></th>
				<th> <?php xl('Age','e'); ?></th>
				<th> <?php xl('Race','e'); ?></th>
				<th> <?php xl('DOB','e'); ?></th>
				<th> <?php xl('DOS','e'); ?></th>
				<th> <?php xl('City','e'); ?></th>
				<th> <?php xl('County','e'); ?></th>
				<th> <?php xl('Zip','e'); ?></th>
				<th> <?php xl('Payment Method','e'); ?></th>
					</thead>
			<tbody>
				<?php
					while($row = sqlFetchArray($result))
					{
					?>
				<tr>
					<td><?=$row['patient_name']?>
						&nbsp;</td>
					<td><?=$row['sex']?>
						&nbsp;</td>
					<td><?=$row['age']?>
						&nbsp;</td>
					<td><?=$row['race']?>
						&nbsp;</td>
					<td><?=$row['DOB']?>
						&nbsp;</td>
					<td><?=$row['DOS']?>
						&nbsp;</td>
					<td><?=$row['city']?>
						&nbsp;</td>
					<td><?=$row['county']?>
						&nbsp;</td>
					<td><?=$row['zip']?>
						&nbsp;</td>
					<td><?=$row['method']?>
						&nbsp;</td>
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
	?>
	<div class='text'> <?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?> </div>
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
