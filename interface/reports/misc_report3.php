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
	
	$report_by = $_POST['report_by'];
	$facility_id = $_POST['form_facility'];
	$provider_id = $_POST['form_provider'];
	$txtSearch = $_POST['txtSearch'];
	
	//billing.date = date of services
	
	if ($_POST['form_refresh'])
	{
		$sql = "SELECT 
				concat(patient_data.fname, ' ', patient_data.lname) AS patient_name, 
				patient_data.id AS pid, 
				patient_data.sex, 
				patient_data.DOB, 
				patient_data.city, 
				patient_data.city as county";
		
		if($report_by == 'ICD9' || $report_by == 'CPT4')
		{
			$sql .= ",
				form_encounter.encounter AS encounter_id, 
				form_encounter.date AS date_of_visit ";
			$sql .= ", codes.code AS code, codes.code_text AS code_text, 
						concat(users.lname, ', ', users.fname) AS provider,
						concat(users.fname, ' ', users.lname) AS provider2 FROM form_encounter
				INNER JOIN patient_data ON patient_data.pid = form_encounter.pid
				INNER JOIN billing ON billing.encounter = form_encounter.encounter
				LEFT OUTER JOIN codes ON codes.code = billing.code
				LEFT OUTER JOIN users ON users.id = billing.provider_id
				LEFT OUTER JOIN facility ON facility.id = users.facility_id ";
			
			$where_str = "";
			
			if($report_by == 'ICD9')
			{
				$where_str = " WHERE billing.code_type = 'ICD9'";
			}
			else if($report_by == 'CPT4')
			{
				$where_str = " WHERE billing.code_type = 'CPT4'";
			}
			
			if(strlen($txtSearch) > 0)
			{
				$where_str .= " AND codes.code LIKE '%$txtSearch%'";
			}
			
			if(strlen($facility_id) > 0)
			{
				$where_str .= " AND facility.id = '$facility_id'";
			}
			
			if(strlen($provider_id) > 0)
			{
				$where_str .= " AND users.id = '$provider_id'";
			}
			
			$sql .= $where_str;
		}
		else if($report_by == 'Drugs')
		{
			$sql .= ", prescriptions.date_added AS date_of_visit, 
					prescriptions.drug AS drug, 
					concat(users.lname, ', ', users.fname) AS provider,
					concat(users.fname, ' ', users.lname) AS provider2
					FROM prescriptions
				INNER JOIN patient_data ON patient_data.pid = prescriptions.patient_id
				LEFT OUTER JOIN users ON users.id = prescriptions.provider_id
				LEFT OUTER JOIN facility ON facility.id = users.facility_id";
			
			$where_str = "";
			
			if(strlen($txtSearch) > 0)
			{
				$where_str = " WHERE prescriptions.drug LIKE '%$txtSearch%'";
			}
			
			if(strlen($facility_id) > 0)
			{
				if(strlen($where_str) > 0)
				{
					$where_str .= " AND facility.id = '$facility_id'";
				}
				else
				{
					$where_str = " WHERE facility.id = '$facility_id'";
				}
			}
			
			if(strlen($provider_id) > 0)
			{	
				if(strlen($where_str) > 0)
				{
					$where_str .= " AND users.id = '$provider_id'";
				}
				else
				{
					$where_str = " WHERE users.id = '$provider_id'";
				}
			}
			
			$sql .= $where_str;
		}
		else if($report_by == 'Referral')
		{
			$sql = "SELECT 
					concat(patient_data.fname, ' ', patient_data.lname) AS patient_name, 
					patient_data.id AS pid, 
					patient_data.sex, 
					patient_data.DOB, 
					patient_data.city, 
					patient_data.city as county,
					transactions.refer_related_code AS code,
					concat(users.lname, ', ', users.fname) AS provider,
					concat(users.fname, ' ', users.lname) AS provider2,
					transactions.date AS date_of_visit
					FROM transactions
					INNER JOIN patient_data ON transactions.pid = patient_data.pid
					INNER JOIN users ON transactions.user = users.username
					LEFT OUTER JOIN facility ON facility.id = users.facility_id
					WHERE transactions.title = 'Referral'";
	
			if(strlen($facility_id) > 0)
			{
				$sql .= " AND facility.id = '$facility_id'";
			}
			
			if(strlen($provider_id) > 0)
			{	
				$sql .= " AND users.id = '$provider_id'";
			}
		}
	}
	
	if ($_POST['form_refresh'] == "export")
	{
		$result = sqlStatement($sql);
		
		if($report_by == 'ICD9' || $report_by == 'CPT4')
		{
			$out = "Code, ICD9/CPT4 Description, Patient Name, Date of Birth, Patient ID, Encounter ID, Date of Visit, Provider\n";
		}
		else if($report_by == 'Drugs')
		{
			$out = "Drugs, Patient Name, Date of Birth, Patient ID, Date of Visit, Provider\n";
		}
		else if($report_by == 'Referral')
		{
			$out = "Code, ICD9/CPT4 Description, Patient Name, Date of Birth, Patient ID, Date of Visit, Provider\n";
		}
		
		while($row = sqlFetchArray($result))
		{
			if($report_by == 'Referral')
			{
				$current_code = $row['code'];
				$code_desc = '';
				
				if(strlen($current_code) > 0)
				{
					$current_code_arr = explode(":", $current_code);
					$result_search = sqlStatement("SELECT * FROM codes WHERE code = '".$current_code_arr[1]."'");
					
					if(sqlNumRows($result_search) > 0)
					{
						$row_search = sqlFetchArray($result_search);
						$code_desc = $row_search['code_text'];
					}
				}
			}
			
			if($report_by == 'ICD9' || $report_by == 'CPT4')
			{
				$out .= "{$row['code']}, {$row['code_text']}, {$row['patient_name']}, {$row['DOB']}, {$row['pid']}, {$row['encounter_id']}, {$row['date_of_visit']}, {$row['provider2']}\n";
			}
			else if($report_by == 'Drugs')
			{
				$out .= "{$row['drug']}, {$row['patient_name']}, {$row['DOB']}, {$row['pid']}, {$row['date_of_visit']}, {$row['provider2']}\n";
			}
			else if($report_by == 'Referral')
			{
				$out .= "{$row['code']}, {$code_desc}, {$row['patient_name']}, {$row['DOB']}, {$row['pid']}, {$row['date_of_visit']}, {$row['provider2']}\n";
			}
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
	<?php
	if($report_by == 'Referral')
	{
	?>
	display: none;
	<?php
	}
	?>
}
</style>
<script language="javascript" type="text/javascript">
	function checkType()
	{
		if($('#report_by').val() == 'Referral')
		{
			$('.optional_area').css("display", "none");
		}
		else
		{
			$('.optional_area').css("display", "inline");
		}
	}
</script>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<span class='title'>
<?php xl('Report - Encounters','e'); ?>
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
				<?php xl('Facility','e'); ?>:
			</td>
			<td>
				<?php
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
				 echo "    <option value='0'";
				 if ($form_facility === '0') echo " selected";
				 echo ">-- " . xl('Unspecified') . " --\n";
				 echo "   </select>\n";
				?>
			</td>
			<td class='label'>
			   <?php xl('Provider','e'); ?>:
			</td>
			<td>
				<?php

				 // Build a drop-down list of providers.
				 //

				 $query = "SELECT id, lname, fname FROM users WHERE ".
				  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

				 $ures = sqlStatement($query);

				 echo "   <select name='form_provider'>\n";
				 echo "    <option value=''>-- " . xl('All') . " --\n";

				 while ($urow = sqlFetchArray($ures)) {
				  $provid = $urow['id'];
				  echo "    <option value='$provid'";
				  if ($provid == $_POST['form_provider']) echo " selected";
				  echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
				 }

				 echo "   </select>\n";

				?>
			</td>
			<td>&nbsp;
			</td>
		</tr>
		<tr>
			<td class='label'>
			   <?php xl('Search','e'); ?>:
			</td>
			<td>
			   <select name="report_by" id="report_by" onChange="checkType();">
				<option value="ICD9" <?=($report_by == 'ICD9') ? "selected='selected'" : ''; ?>><?php xl('ICD9','e'); ?></option>
				<option value="CPT4" <?=($report_by == 'CPT4') ? "selected='selected'" : ''; ?>><?php xl('CPT4','e'); ?></option>
				<option value="Drugs" <?=($report_by == 'Drugs') ? "selected='selected'" : ''; ?>><?php xl('Drugs','e'); ?></option>
				<option value="Referral" <?=($report_by == 'Referral') ? "selected='selected'" : ''; ?>><?php xl('Referral','e'); ?></option>
			   </select>
			   <label for="txtSearch"></label>
			   <input type="text" name="txtSearch" id="txtSearch" value="<?=$txtSearch?>" class="optional_area">&nbsp;</td>
			<td class='label'>
			   
			</td>
			<td>
			   
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
						<?php xl('Submit','e'); ?>
					</span>
					</a>

					<?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php xl('Print','e'); ?>
						</span>
					</a>
					<?php } ?>
					
					<?php if ($_POST['form_refresh']) { ?>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","export"); $("#theform").submit();'>
						<span>
							<?php xl('Export','e'); ?>
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
	$result = sqlStatement($sql);
	
	if(sqlNumRows($result) > 0)
	{
?>
		<div id="report_results">
			<table>
				<thead>
					<?php
					if($report_by == 'ICD9' || $report_by == 'CPT4')
					{
						?>
						<th><?php xl('Code','e'); ?></th>
						<th><?php xl('ICD9/CPT4 Description','e'); ?></th>
						<?php
					}
					else if($report_by == 'Drugs')
					{
						?>
						<th><?php xl('Drugs','e'); ?></th>
						<?php
					}
					else if($report_by == 'Referral')
					{
						?>
						<th><?php xl('Code','e'); ?></th>
						<th><?php xl('ICD9/CPT4 Description','e'); ?></th>
						<?php
					}
					?>
					<th> <?php xl('Patient Name','e'); ?></th>
					<th> <?php xl('Date of Birth','e'); ?></th>
					<th> <?php xl('Patient ID','e'); ?></th>
					
					<?php
					if($report_by != 'Referral' && $report_by != 'Drugs' )
					{
					?>
					<th> <?php xl('Encounter ID','e'); ?></th>
					<?php
					}
					?>
					
					
					<th> <?php xl('Date of Visit','e'); ?></th>
					<th> <?php xl('Provider','e'); ?></th>
				</thead>
				<tbody>
				<?php
					while($row = sqlFetchArray($result))
					{
						if($report_by == 'Referral')
						{
							$current_code = $row['code'];
							$code_desc = '';
							
							if(strlen($current_code) > 0)
							{
								$current_code_arr = explode(":", $current_code);
								$result_search = sqlStatement("SELECT * FROM codes WHERE code = '".$current_code_arr[1]."'");
								
								if(sqlNumRows($result_search) > 0)
								{
									$row_search = sqlFetchArray($result_search);
									$code_desc = $row_search['code_text'];
								}
							}
						}
					?>
					<tr>
						<?php
						if($report_by == 'ICD9' || $report_by == 'CPT4')
						{
							?>
							<td> <?=$row['code']?>&nbsp;</td>
							<td> <?=$row['code_text']?>&nbsp;</td>
							<?php
						}
						else if($report_by == 'Drugs')
						{
							?>
							<td width="200"> <?=$row['drug']?>&nbsp;</td>
							<?php
						}
						else if($report_by == 'Referral')
						{
							?>
							<td> <?=$row['code']?>&nbsp;</td>
							<td> <?=$code_desc?>&nbsp;</td>
							<?php
						}
						?>
						<td><a target="RBot" href="../patient_file/summary/demographics2.php?pid=<?=$row['pid']?>"><?=$row['patient_name']?></a>&nbsp;</td>
						<td> <?=$row['DOB']?>&nbsp;</td>
						<td> <?=$row['pid']?>&nbsp;</td>
						
						<?php
						if($report_by != 'Referral'  && $report_by != 'Drugs' )
						{
						?>
						<td> <a target="RBot" href="../patient_file/encounter/encounter_top.php?set_encounter=<?=$row['encounter_id']?>&pid=<?=$row['pid']?>"><?=$row['encounter_id']?></a>&nbsp;</td>
						<?php
						}
						?>
						
						
						<td> <?=$row['date_of_visit']?>&nbsp;</td>
						<td> <?=$row['provider']?>&nbsp;</td>
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
	?><div class='text'> <?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?> </div><?php
}
?>
</form>
</body>


</html>
