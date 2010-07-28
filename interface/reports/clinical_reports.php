<?php
 // Copyright (C) 2010 OpenEMR Support LLC
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists prescriptions and their dispensations according
 // to various input selection criteria.
 //
 // Prescription report written by Rod Roark, 2010
 // Fixed drug name search to work in a broader sense - tony@mi-squared.com, 2010
 // Added five new reports as per EHR certification requirements for Patient Lists - OpenEMR Support LLC, 2010

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

	require_once("../globals.php");
	require_once("$srcdir/patient.inc");
	require_once("$srcdir/options.inc.php");
	require_once("../drugs/drugs.inc.php");
	require_once("$srcdir/formatting.inc.php");
	
	function add_date($givendate,$day=0,$mth=0,$yr=0) 
	{
		$cd = strtotime($givendate);
		$newdate = date('Y-m-d', mktime(date('h',$cd),
		date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
		date('d',$cd)+$day, date('Y',$cd)+$yr));
		return $newdate;
    }

 	$type = htmlspecialchars($_POST["type"], ENT_QUOTES);
	$facility = htmlspecialchars(isset($_POST['facility']) ? $_POST['facility'] : '', ENT_QUOTES);
	$sql_date_from = htmlspecialchars(fixDate($_POST['date_from'], date('Y-01-01')), ENT_QUOTES);
	$sql_date_to = htmlspecialchars(fixDate($_POST['date_to']  , add_date(date('Y-m-d'), -1)), ENT_QUOTES);
	$patient_id = htmlspecialchars(trim($_POST["patient_id"]), ENT_QUOTES);
	$age_from = htmlspecialchars($_POST["age_from"], ENT_QUOTES);
	$age_to = htmlspecialchars($_POST["age_to"], ENT_QUOTES);
	$sql_gender = htmlspecialchars($_POST["gender"], ENT_QUOTES);
	$sql_ethnicity = htmlspecialchars($_POST["ethnicity"], ENT_QUOTES);
	$form_lot_number = htmlspecialchars(trim($_POST['form_lot_number']), ENT_QUOTES);
	$form_drug_name = htmlspecialchars(trim($_POST["form_drug_name"]), ENT_QUOTES);
?>
<html>
<head>
<?php html_header_show();?>
<title>
<?php echo htmlspecialchars(xl('Clinical Reports'),ENT_NOQUOTES); ?>
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
	
	function submitForm()
	{
		var d_from = new String($('#date_from').val());
		var d_to = new String($('#date_to').val());
		
		var d_from_arr = d_from.split('-');
		var d_to_arr = d_to.split('-');
		
		var dt_from = new Date(d_from_arr[0], d_from_arr[1], d_from_arr[2]);
		var dt_to = new Date(d_to_arr[0], d_to_arr[1], d_to_arr[2]);
		
		var mili_from = dt_from.getTime();
		var mili_to = dt_to.getTime();
		var diff = mili_to - mili_from;
		
		$('#date_error').css("display", "none");
		
		if(diff < 0) //negative
		{
			$('#date_error').css("display", "inline");
		}
		else
		{
			$("#form_refresh").attr("value","true"); 
			$("#theform").submit();
		}
	}
	
	$(document).ready(function() {
		$(".numeric_only").keydown(function(event) {
			//alert(event.keyCode);
			// Allow only backspace and delete
			if ( event.keyCode == 46 || event.keyCode == 8 ) {
				// let it happen, don't do anything
			}
			else {
				if(!((event.keyCode >= 96 && event.keyCode <= 105) || (event.keyCode >= 48 && event.keyCode <= 57)))
				{
					event.preventDefault();	
				}
			}
		});
	});
</script>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<span class='title'>
<?php echo htmlspecialchars(xl('Report - Clinical'),ENT_NOQUOTES); ?>
</span>
<!-- Search can be done using age range, gender, and ethnicity filters.
Search options include diagnosis, procedure, prescription, medical history, and lab results.
-->
<div id="report_parameters_daterange"> <?php echo htmlspecialchars(date("d F Y", strtotime($sql_date_from)),ENT_NOQUOTES) .
      " &nbsp; to &nbsp; ". htmlspecialchars(date("d F Y", strtotime($sql_date_to)),ENT_NOQUOTES); ?> </div>
<form name='theform' id='theform' method='post' action='clinical_reports.php'>
	<div id="report_parameters">
		<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
		<table>
			<tr>
				<td width='740px'><div style='float:left'>
						<table class='text'>
							<tr>
								<td class='label'><?php echo htmlspecialchars(xl('Facility'),ENT_NOQUOTES); ?>
									: </td>
								<td>
						                   <?php dropdown_facility($facility,'facility',false); ?>
								</td>
								<td class='label'><?php echo htmlspecialchars(xl('From'),ENT_NOQUOTES); ?>
									: </td>
								<td><input type='text' name='date_from' id="date_from" size='10' value='<?php echo htmlspecialchars($sql_date_from,ENT_QUOTES) ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
									<img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php echo htmlspecialchars(xl('Click here to choose a date'),ENT_QUOTES); ?>'></td>
								<td><span id="date_error" style="color: #F00; font-size: 11px; display: none;"><?php echo htmlspecialchars(xl('From date cannot be greater than To date'),ENT_NOQUOTES); ?></span>&nbsp;</td>
							</tr>
							<tr>
								<td class='label'><?php echo htmlspecialchars(xl('Patient ID'),ENT_NOQUOTES); ?>:</td>
								<td><input name='patient_id' class="numeric_only" type='text' id="patient_id"
				title='<?php echo htmlspecialchars(xl('Optional numeric patient ID'),ENT_QUOTES); ?>' 
	                        value='<?php echo htmlspecialchars($patient_id,ENT_QUOTES); ?>' size='10' maxlength='20' /></td>
								<td class='label'><?php echo htmlspecialchars(xl('To'),ENT_NOQUOTES); ?>: </td>
								<td><input type='text' name='date_to' id="date_to" size='10' value='<?php echo htmlspecialchars($sql_date_to,ENT_QUOTES); ?>'
				onKeyUp='datekeyup(this,mypcc)' onBlur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
								<img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php echo htmlspecialchars(xl('Click here to choose a date'),ENT_QUOTES); ?>'></td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td class='label'><?php echo htmlspecialchars(xl('Age Range'),ENT_NOQUOTES); ?>
								:</td>
								<td><? echo htmlspecialchars(xl('From'),ENT_NOQUOTES); ?>
									<input name='age_from' class="numeric_only" type='text' id="age_from" value="<?php echo htmlspecialchars($age_from,ENT_QUOTES); ?>" size='3' maxlength='3' /><? echo htmlspecialchars(xl('To'),ENT_NOQUOTES); ?>
<input name='age_to' class="numeric_only" type='text' id="age_to" value="<?php echo htmlspecialchars($age_to,ENT_QUOTES); ?>" size='3' maxlength='3' /></td>
								<td class='label'><?php echo htmlspecialchars(xl('Option'),ENT_QUOTES); ?>:</td>
								<td><select name="type" id="type" onChange="checkType();">
										<option value="Diagnosis" <?php if($type == 'Diagnosis') { echo "selected"; } ?>><?php echo htmlspecialchars(xl('Diagnosis'),ENT_NOQUOTES); ?></option>
										<option value="Procedure" <?php if($type == 'Procedure') { echo "selected"; } ?>><?php echo htmlspecialchars(xl('Procedure'),ENT_NOQUOTES); ?></option>
										<?php
										if(!$GLOBALS['disable_prescriptions'])
										{
										?>
										<option value="Prescription" <?php if($type == 'Prescription') { echo "selected"; } ?>><?php echo htmlspecialchars(xl('Prescription'),ENT_NOQUOTES); ?></option>
										<?php
										}
										?>
										<option value="Medical History" <?php if($type == 'Medical History') { echo "selected"; } ?>><?php echo htmlspecialchars(xl('Medical History'),ENT_NOQUOTES); ?></option>
										<option value="Lab Results" <?php if($type == 'Lab Results') { echo "selected"; } ?>><?php echo htmlspecialchars(xl('Lab Results'),ENT_NOQUOTES); ?></option>
										<option value="Service Codes" <?php if($type == 'Service Codes') { echo "selected"; } ?>><?php echo htmlspecialchars(xl('Service Codes'),ENT_NOQUOTES); ?></option>
								</select></td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td class='label'><?php echo htmlspecialchars(xl('Gender'),ENT_NOQUOTES); ?>
								:</td>
								<td><?php echo generate_select_list('gender', 'sex', $gender, 'Select Gender', 'Unassigned', '', ''); ?></td>
								<td class='label'><span class="optional_area"><?php echo htmlspecialchars(xl('Drug'),ENT_NOQUOTES); ?>:</span>&nbsp;</td>
								<td><span class="optional_area"><input type='text' name='form_drug_name' size='10' maxlength='250' value='<?php echo htmlspecialchars($form_drug_name,ENT_QUOTES); ?>'
				title='<?php echo htmlspecialchars(xl('Optional drug name, use % as a wildcard'),ENT_QUOTES); ?>' /></span>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td class='label'><?php echo htmlspecialchars(xl('Race/Ethnicity'),ENT_NOQUOTES); ?>:</td>
								<td><?php echo generate_select_list('ethnicity', 'ethrace', $ethnicity, 'Select Ethnicity', 'Unassigned', '', ''); ?></td>
								<td class='label'><span class="optional_area">
									<?php echo htmlspecialchars(xl('Lot'),ENT_NOQUOTES); ?>
								:</span>&nbsp;</td>
								<td><span class="optional_area">
									<input type='text' name='form_lot_number' size='10' maxlength='20' value='<?php echo htmlspecialchars($form_lot_number,ENT_QUOTES); ?>'
				title='<?php echo htmlspecialchars(xl('Optional lot number, use % as a wildcard'),ENT_QUOTES); ?>' />
								</span>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
						</table>
				</div></td>
				<td height="100%" align='left' valign='middle'><table style='border-left:1px solid; width:100%; height:100%' >
						<tr>
							<td><div style='margin-left:15px'> <a href='#' class='css_button' onclick='submitForm();'> <span>
									<?php echo htmlspecialchars(xl('Submit'),ENT_NOQUOTES); ?>
									</span> </a>
									<?php if ($_POST['form_refresh']) { ?>
									<a href='#' class='css_button' onclick='window.print()'> <span>
									<?php echo htmlspecialchars(xl('Print'),ENT_NOQUOTES); ?>
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
$sqlBindArray = array();
if ($_POST['form_refresh']) 
{
	if($type == 'Prescription')
	{
		$sqlstmt = "
			SELECT 
			r.id, r.patient_id, r.date_modified AS prescriptions_date_modified, r.dosage, r.route, r.interval, r.refills, r.drug,
			d.name, d.ndc_number, d.form, d.size, d.unit, d.reactions,
			s.sale_id, s.sale_date, s.quantity,
			i.manufacturer, i.lot_number, i.expiration,
			p.pubpid, p.fname, p.lname, p.mname, 
			concat(p.fname, ' ', p.lname) AS patient_name, p.id AS patient_id, DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),p.dob)), '%Y')+0 AS patient_age, p.sex AS patient_sex, p.ethnoracial AS patient_ethnic,
			u.facility_id, concat(u.fname, ' ', u.lname)  AS users_provider
			FROM prescriptions AS r
			LEFT OUTER JOIN drugs AS d ON d.drug_id = r.drug_id
			LEFT OUTER JOIN drug_sales AS s ON s.prescription_id = r.id
			LEFT OUTER JOIN drug_inventory AS i ON i.inventory_id = s.inventory_id
			LEFT OUTER JOIN patient_data AS p ON p.pid = r.patient_id
			LEFT OUTER JOIN users AS u ON u.id = r.provider_id ";

		$where_str = 
			"
			WHERE r.date_modified >= ?
			AND r.date_modified < DATE_ADD(?, INTERVAL 1 DAY) AND r.date_modified < ?";

	        array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
	    
		if(strlen($sql_gender) > 0)
		{
			$where_str .= "AND p.sex = ? ";
		        array_push($sqlBindArray, $sql_gender);
		}

		if(strlen($sql_ethnicity) > 0)
		{
			$where_str .= "AND p.ethnoracial = ? ";
		        array_push($sqlBindArray, $sql_ethnicity);
		}

		if(strlen($age_from) > 0)
		{
			$where_str .= "AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),p.dob)), '%Y')+0 >= ? ";
		        array_push($sqlBindArray, $age_from);
		}

		if(strlen($age_to) > 0)
		{
			$where_str .= "AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),p.dob)), '%Y')+0 <= ? ";
		        array_push($sqlBindArray, $age_to);
		}

		if(strlen($patient_id) > 0)
		{
			$where_str .= "AND p.id = ? ";
		        array_push($sqlBindArray, $patient_id);
		}
		
		if(strlen($form_drug_name) > 0)
		{
			$where_str .= "AND (
			d.name LIKE ?
			OR r.drug LIKE ?
			) ";
		        array_push($sqlBindArray, $form_drug_name, $form_drug_name);
		}

		if(strlen($form_lot_number) > 0)
		{
			$where_str .= "AND i.lot_number LIKE ? ";
		        array_push($sqlBindArray, $form_lot_number);
		}
		
		if($facility != '')
		{
			$where_str = $where_str."   and u.facility_id = ? ";
		        array_push($sqlBindArray, $facility);
		}

		$sqlstmt .= $where_str . "ORDER BY p.lname, p.fname, p.pubpid, r.id, s.sale_id";
	}
	else
	{
		$sqlstmt = "select
		concat(pd.fname, ' ', pd.lname) AS patient_name,
		pd.id AS patient_id,
		DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 AS patient_age,
		pd.sex AS patient_sex,
		pd.ethnoracial AS patient_ethnic,
		concat(u.fname, ' ', u.lname)  AS users_provider, ";

		if ( $type == 'Diagnosis' )

		{
			$sqlstmt = $sqlstmt."li.date AS lists_date,
		   li.diagnosis AS lists_diagnosis,
			li.title AS lists_title ";
		}

		if ( $type == 'Procedure')
		{
			$sqlstmt = $sqlstmt."po.date_ordered AS procedure_order_date_ordered,
            pt.standard_code AS procedure_type_standard_code,
            pt.name   as procedure_name,
            po.order_priority AS procedure_order_order_priority,
            po.order_status AS procedure_order_order_status,
			po.encounter_id AS procedure_order_encounter,
            po.patient_instructions AS procedure_order_patient_instructions,
            po.activity AS procedure_order_activity,
            po.control_id AS procedure_order_control_id ";
		}
		
		if ( $type == 'Medical History')
		{
			$sqlstmt = $sqlstmt."hd.date AS history_data_date,
            hd.tobacco AS history_data_tobacco,
            hd.alcohol AS history_data_alcohol,
            hd.recreational_drugs AS history_data_recreational_drugs   ";
		}
			
		if ( $type == 'Lab Results')
		{
			$sqlstmt = $sqlstmt."pr.date AS procedure_result_date,
			   pr.facility AS procedure_result_facility,
				pr.units AS procedure_result_units,
				pr.result AS procedure_result_result,
				pr.range AS procedure_result_range,
				pr.abnormal AS procedure_result_abnormal,
				pr.comments AS procedure_result_comments,
				pr.document_id AS procedure_result_document_id ";
		}

		if($type == 'Service Codes')
		{
		 	$sqlstmt .= "pd.dob AS date_of_birth, c.code,
			c.code_text,
			fe.encounter,
			b.date,
			concat(u.fname, ' ', u.lname) AS provider_name ";
		}

		// from
		$sqlstmt = $sqlstmt."from	patient_data as pd ";
		
			// where
         $sqlstmt = $sqlstmt."left outer join users as u on u.id = pd.providerid
            left outer join facility as f on f.id = u.facility_id ";

		if($type == 'Diagnosis')
		{
			$sqlstmt = $sqlstmt." left outer join lists as li on li.pid  = pd.id ";   
		}
		
		if ( $type == 'Procedure' || $type == 'Lab Results' )
		{
         $sqlstmt = $sqlstmt."left outer join procedure_order as po on po.patient_id = pd.pid
            left outer join procedure_report as pp on pp.procedure_order_id   = po.procedure_order_id
            left outer join procedure_type as pt on pt.procedure_type_id    = po.procedure_type_id ";
		}
		
		
		if ( $type == 'Lab Results' )
		{
		   $sqlstmt = $sqlstmt."left outer join procedure_result as pr on pr.procedure_report_id  = pp.procedure_report_id
            and   pr.procedure_type_id    = po.procedure_type_id  ";
		}
		
		if ( $type == 'Medical History')
		{
         $sqlstmt = $sqlstmt."left outer join history_data as hd on hd.pid   =  pd.id 
            and (isnull(hd.tobacco)  = 0
            or isnull(hd.alcohol)  = 0
            or isnull(hd.recreational_drugs)  = 0)      ";
      }
		
      if($type == 'Service Codes')
		{
         $sqlstmt = $sqlstmt."left outer join billing as b on b.pid = pd.id
            left outer join form_encounter as fe on fe.encounter = b.encounter and   b.code_type = 'CPT4'
            left outer join codes as c on c.code = b.code ";
      }

		if ( $type == 'Diagnosis')
		{	$dt_field = 'li.date';	}
		if ( $type == 'Medical History')
		{	$dt_field = 'hd.date';	}
		if ( $type == 'Lab Results')
		{	$dt_field = 'pr.date';	}
		if ( $type == 'Procedure')
		{	$dt_field = 'po.date_ordered';	}
      if($type == 'Service Codes')
      {  $dt_field = 'b.date';   }
		
		$sqlstmt = $sqlstmt."   where $dt_field >= ? AND $dt_field < DATE_ADD(?, INTERVAL 1 DAY) AND $dt_field < ?";
	        array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
	    
		if ( strlen($patient_id) != 0)
		{
		    $sqlstmt = $sqlstmt."   and pd.id = ?";
		    array_push($sqlBindArray, $patient_id);
		}
		
		if ( strlen($age_from) != 0)
		{
		    $sqlstmt = $sqlstmt."   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 >= ?";
		    array_push($sqlBindArray, $age_from);
		}
		if ( strlen($age_to) != 0)
		{
		    $sqlstmt = $sqlstmt."   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 <= ?";
		    array_push($sqlBindArray, $age_to);
		}
	    
		if ( strlen($sql_gender) != 0)
		{
		    $sqlstmt = $sqlstmt."   and pd.sex = ?";
		    array_push($sqlBindArray, $sql_gender);
		}
		if ( strlen($sql_ethnicity) != 0)
		{
		    $sqlstmt = $sqlstmt."   and pd.ethnoracial = ?";
		    array_push($sqlBindArray, $sql_ethnicity);
		}

      if($facility != '')
		{
			$sqlstmt = $sqlstmt."   and f.id = ? ";
		        array_push($sqlBindArray, $facility);
		}
	  
	  if($type == 'Diagnosis')
		{
			$sqlstmt = $sqlstmt." union   
            select   concat(pd.fname, ' ', pd.lname),
               pd.id,
               DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0,
               pd.sex,
               pd.ethnoracial,
               concat(u.fname, ' ', u.lname),
               fe.date,
               '',
               fe.reason
            from	patient_data as pd
            left outer join users as u on u.id = pd.providerid
            left outer join facility as f on f.id = u.facility_id
            left outer join form_encounter as fe on fe.pid  = pd.id 
               and fe.date >=  ? AND fe.date <=  ?   ";
	    array_push($sqlBindArray, $sql_date_from, $sql_date_to);
         if ( strlen($patient_id) != 0)
         {
	     $sqlstmt = $sqlstmt."   where pd.id = ?";
	     array_push($sqlBindArray, $patient_id);
	 }
         if ( strlen($age_from) != 0)
         {
	     $sqlstmt = $sqlstmt."   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 >= ?";
	     array_push($sqlBindArray, $age_from);
	 }
         if ( strlen($age_to) != 0)
         {
	     $sqlstmt = $sqlstmt."   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 <= ?";
	     array_push($sqlBindArray, $age_to);
	 }
         if ( strlen($sql_gender) != 0)
         {
	     $sqlstmt = $sqlstmt."   and pd.sex = ?";
	     array_push($sqlBindArray, $sql_gender);
	 }
         if ( strlen($sql_ethnicity) != 0)
         {
	     $sqlstmt = $sqlstmt."   and pd.ethnoracial = ?";
	     array_push($sqlBindArray, $sql_ethnicity);
	 }
		 
		 if($facility != '')
		 {
		     $sqlstmt = $sqlstmt."   and f.id = ? ";
		     array_push($sqlBindArray, $facility);
		 }
		}
      
   }
   	
	//echo $sqlstmt;

	$result = sqlStatement($sqlstmt,$sqlBindArray);

	if(sqlNumRows($result) > 0)
	{
?>
		<div id="report_results">
			<table>
				<thead>
					<?php
					if($type == 'Service Codes')
					{
						?>
						<th><?php echo htmlspecialchars(xl('Code'),ENT_NOQUOTES); ?></th>
						<th> <?php echo htmlspecialchars(xl('CPT4 Description'),ENT_NOQUOTES); ?></th>
						<th> <?php echo htmlspecialchars(xl('Patient Name'),ENT_NOQUOTES); ?></th>
						<th> <?php echo htmlspecialchars(xl('Date of Birth'),ENT_NOQUOTES); ?></th>
						<th> <?php echo htmlspecialchars(xl('Patient ID'),ENT_NOQUOTES); ?></th>
						<th> <?php echo htmlspecialchars(xl('Encounter ID'),ENT_NOQUOTES); ?></th>
						<th> <?php echo htmlspecialchars(xl('Date of Visit'),ENT_NOQUOTES); ?></th>
						<th> <?php echo htmlspecialchars(xl('Provider'),ENT_NOQUOTES); ?></th>
						<?php
					}
					else
					{
					?>
					<th><?php echo htmlspecialchars(xl('Patient'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('ID'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Age'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Gender'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Race'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Provider'),ENT_NOQUOTES); ?></th>
					<?php
					}
					?>
					
					<?php
					if($type == 'Prescription')
					{
					?>
						<th> <?php echo htmlspecialchars(xl('Date'),ENT_NOQUOTES); ?> </th>
						<th> <?php echo htmlspecialchars(xl('RX'),ENT_NOQUOTES); ?> </th>
						<th> <?php echo htmlspecialchars(xl('Drug Name'),ENT_NOQUOTES); ?> </th>
						<th> <?php echo htmlspecialchars(xl('NDC'),ENT_NOQUOTES); ?> </th>
						<th> <?php echo htmlspecialchars(xl('Units'),ENT_NOQUOTES); ?> </th>
						<th> <?php echo htmlspecialchars(xl('Refills'),ENT_NOQUOTES); ?> </th>
						<th> <?php echo htmlspecialchars(xl('Instructed'),ENT_NOQUOTES); ?> </th>
						<th> <?php echo htmlspecialchars(xl('Reactions'),ENT_NOQUOTES); ?> </th>
						<th> <?php echo htmlspecialchars(xl('Dispensed'),ENT_NOQUOTES); ?> </th>
						<th> <?php echo htmlspecialchars(xl('Qty'),ENT_NOQUOTES); ?> </th>
						<th> <?php echo htmlspecialchars(xl('Manufacturer'),ENT_NOQUOTES); ?> </th>
						<th> <?php echo htmlspecialchars(xl('Lot'),ENT_NOQUOTES); ?> </th>
					<?php
					}
					?>
					
					<?php
					if($type == 'Diagnosis')
					{
					?>
					<!-- Diagnosis -->
					<th> <?php echo htmlspecialchars(xl('Date'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('DX'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Diagnosis Name'),ENT_NOQUOTES); ?></th>
					<?php
					}
					?>
					
					<?php
					if($type == 'Procedure')
					{
						
					?>
					<!-- Procedure -->
					<th> <?php echo htmlspecialchars(xl('Date'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('CPT'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Service Code'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Encounter'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Priority'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Status'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Patient Instructions'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Activity'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Control ID'),ENT_NOQUOTES); ?></th>
					<?php
					}
					?>
					
					<?php
					if($type == 'Medical History')
					{
					?>
					<!-- Medical History -->
					<th> <?php echo htmlspecialchars(xl('Date'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Smoking'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Alcohol'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Rec. Drugs'),ENT_NOQUOTES); ?></th>
					<?php
					}
					?>
					
					<?php
					if($type == 'Lab Results')
					{
					?>
					<!-- Lab Results -->
					<th> <?php echo htmlspecialchars(xl('Date'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Facility'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Units'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Result'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Range'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Abnormal'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Comments'),ENT_NOQUOTES); ?></th>
					<th> <?php echo htmlspecialchars(xl('Document ID'),ENT_NOQUOTES); ?></th>
					<?php
					}
					?>
				</thead>
				<tbody>
				
				<?php
				if($type == 'Prescription')
				{
					$last_patient_id = 0;
  					$last_prescription_id = 0;
  					while ($row = sqlFetchArray($result)) 
					{
   						$prescription_id = $row['id'];
   						$drug_name       = empty($row['name']) ? $row['drug'] : $row['name'];
   						$ndc_number      = $row['ndc_number'];
   						$drug_units      = $row['size'] . ' ' .
	               		generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['unit']);
   						$refills         = $row['refills'];
   						$reactions       = $row['reactions'];
   						$instructed      = $row['dosage'] . ' ' .
	               		
						generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'), $row['form']) . ' ' .
                       	generate_display_field(array('data_type'=>'1','list_id'=>'drug_interval'), $row['interval']);
  						
						//if ($row['patient_id'] == $last_patient_id) {
   						if (strcmp($row['pubpid'], $last_patient_id) == 0) 
						{
    						$patient_name = '&nbsp;';
    						$patient_id   = '&nbsp;';
   							
							if ($row['id'] == $last_prescription_id) 
							{
    							$prescription_id = '&nbsp;';
     							$drug_name       = '&nbsp;';
     							$ndc_number      = '&nbsp;';
     							$drug_units      = '&nbsp;';
     							$refills         = '&nbsp;';
     							$reactions       = '&nbsp;';
     							$instructed      = '&nbsp;';
   							}
   						}
						?>
							<tr>
								<td> <a target="RBot" href="../patient_file/summary/demographics.php?pid=<?php echo htmlspecialchars($row['patient_id'],ENT_QUOTES);?>"><?php echo htmlspecialchars($row['patient_name'],ENT_NOQUOTES); ?></a>&nbsp;</td>
								<td> <?php echo htmlspecialchars($row['patient_id'],ENT_NOQUOTES); ?>&nbsp;</td>
								<td> <?php echo htmlspecialchars($row['patient_age'],ENT_NOQUOTES); ?>&nbsp;</td>
								<td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'sex'), $row['patient_sex']),ENT_NOQUOTES); ?>&nbsp;</td>
								<td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'ethrace'), $row['patient_ethnic']),ENT_NOQUOTES); ?>&nbsp;</td>
								<td> <?php echo htmlspecialchars($row['users_provider'],ENT_NOQUOTES); ?>&nbsp;</td>
								<td> <?php echo htmlspecialchars(oeFormatShortDate($row['prescriptions_date_modified']),ENT_NOQUOTES); ?>&nbsp;</td>
								<td><?php echo htmlspecialchars($prescription_id,ENT_NOQUOTES); ?></td>
								<td><?php echo htmlspecialchars($drug_name,ENT_NOQUOTES); ?></td>
								<td><?php echo htmlspecialchars($ndc_number,ENT_NOQUOTES); ?></td>
								<td><?php echo htmlspecialchars($drug_units,ENT_NOQUOTES); ?></td>
								<td><?php echo htmlspecialchars($refills,ENT_NOQUOTES); ?></td>
								<td><?php echo htmlspecialchars($instructed,ENT_NOQUOTES); ?></td>
								<td><?php echo htmlspecialchars($reactions,ENT_NOQUOTES); ?></td>
								<td><a href='../drugs/dispense_drug.php?sale_id=<?php echo htmlspecialchars($row['sale_id'],ENT_QUOTES); ?>'
								style='color:#0000ff' target='_blank'>
								<?php echo htmlspecialchars(oeFormatShortDate($row['sale_date']),ENT_NOQUOTES); ?>
								</a>
								</td>
								<td><?php echo htmlspecialchars($row['quantity'],ENT_NOQUOTES); ?></td>
								<td><?php echo htmlspecialchars($row['manufacturer'],ENT_NOQUOTES); ?></td>
								<td><?php echo htmlspecialchars($row['lot_number'],ENT_NOQUOTES); ?></td>
							</tr>
						<?php
						$last_prescription_id = $row['id'];
					   	$last_patient_id = $row['pubpid'];
					}
				}
				else
				{
					while($row = sqlFetchArray($result))
					{
					?>
					<tr>
						<?php
						if($type == 'Service Codes')
						{
							?>
							<td><?php echo htmlspecialchars($row['code'],ENT_NOQUOTES); ?>&nbsp;</td>
							<td><?php echo htmlspecialchars($row['code_text'],ENT_NOQUOTES); ?>&nbsp;</td>
							<td><a target="RBot" href="../patient_file/summary/demographics.php?pid=<?php echo htmlspecialchars($row['patient_id'],ENT_QUOTES);?>"><?php echo htmlspecialchars($row['patient_name'],ENT_NOQUOTES); ?></a>&nbsp;</td>
							<td><?php echo htmlspecialchars(oeFormatShortDate($row['date_of_birth']),ENT_NOQUOTES); ?>&nbsp;</td>
							<td><?php echo htmlspecialchars($row['patient_id'],ENT_NOQUOTES); ?>&nbsp;</td>
							<td><a target="RBot" href="../patient_file/encounter/encounter_top.php?set_encounter=<?php echo htmlspecialchars($row['encounter'],ENT_QUOTES);?>&pid=<?php echo htmlspecialchars($row['patient_id'],ENT_QUOTES);?>"><?php echo htmlspecialchars($row['encounter'],ENT_NOQUOTES); ?></a>&nbsp;</td>
							<td><?php echo htmlspecialchars(oeFormatShortDate($row['date']),ENT_NOQUOTES); ?>&nbsp;</td>
							<td><?php echo htmlspecialchars($row['provider_name'],ENT_NOQUOTES); ?>&nbsp;</td>
							<?php
						}
						else
						{
							?>
							<td> <a target="RBot" href="../patient_file/summary/demographics.php?pid=<?php echo htmlspecialchars($row['patient_id'],ENT_QUOTES);?>"><?php echo htmlspecialchars($row['patient_name'],ENT_NOQUOTES); ?></a>&nbsp;</td>
							<td> <?php echo htmlspecialchars($row['patient_id'],ENT_NOQUOTES); ?>&nbsp;</td>
							<td> <?php echo htmlspecialchars($row['patient_age'],ENT_NOQUOTES); ?>&nbsp;</td>
							<td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'sex'), $row['patient_sex']),ENT_NOQUOTES); ?>&nbsp;</td>
							<td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'ethrace'), $row['patient_ethnic']),ENT_NOQUOTES); ?>&nbsp;</td>
							<td> <?php echo htmlspecialchars($row['users_provider'],ENT_NOQUOTES); ?>&nbsp;</td>
							<?php
						}
						?>
						
						<?php
						if($type == 'Diagnosis')
						{
						?>
						<!-- Diagnosis -->
						<td> <?php echo htmlspecialchars(oeFormatShortDate($row['lists_date']),ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['lists_diagnosis'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['lists_title'],ENT_NOQUOTES); ?>&nbsp;</td>
						<?php
						}
						?>
						
						<?php
						if($type == 'Procedure')
						{
							$procedure_type_standard_code_arr = explode(':', $row['procedure_type_standard_code']);
							$procedure_type_standard_code = $procedure_type_standard_code_arr[1];
						?>
						<!-- Procedure -->
						<td> <?php echo htmlspecialchars(oeFormatShortDate($row['procedure_order_date_ordered']),ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($procedure_type_standard_code,ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['procedure_name'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <a target="RBot" href="../patient_file/encounter/encounter_top.php?set_encounter=<?php echo htmlspecialchars($row['procedure_order_encounter'],ENT_QUOTES);?>&pid=<?php echo htmlspecialchars($row['patient_id'],ENT_QUOTES);?>"><?php echo htmlspecialchars($row['procedure_order_encounter'],ENT_NOQUOTES); ?></a>&nbsp;</td>
						<td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'ord_priority'),$row['procedure_order_order_priority']),ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'ord_status'),$row['procedure_order_order_status']),ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['procedure_order_patient_instructions'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['procedure_order_activity'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['procedure_order_control_id'],ENT_NOQUOTES); ?>&nbsp;</td>
						<?php
						}
						?>
						
						<?php
						if($type == 'Medical History')
						{
						?>
						<!-- Medical History -->
						<td> <?php echo htmlspecialchars(oeFormatShortDate($row['history_data_date']),ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['history_data_tobacco'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['history_data_alcohol'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['history_data_recreational_drugs'],ENT_NOQUOTES); ?>&nbsp;</td>
						<?php
						}
						?>
						
						<?php
						if($type == 'Lab Results')
						{
						?>
						<!-- Lab Results -->
						<td> <?php echo htmlspecialchars(oeFormatShortDate($row['procedure_result_date']),ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['procedure_result_facility'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'proc_unit'),$row['procedure_result_units']),ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['procedure_result_result'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['procedure_result_range'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['procedure_result_abnormal'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['procedure_result_comments'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td> <?php echo htmlspecialchars($row['procedure_result_document_id'],ENT_NOQUOTES); ?>&nbsp;</td>
						<?php
						}
						?>
					</tr>
				<?php
					}
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
	?><div class='text'> <?php echo htmlspecialchars(xl('Please input search criteria above, and click Submit to view results.'),ENT_NOQUOTES); ?> </div><?php
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
 Calendar.setup({inputField:"date_from", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"date_to", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>
</html>
