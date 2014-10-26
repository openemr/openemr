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

	require_once("../globals.php");
	require_once("$srcdir/patient.inc");
	require_once("$srcdir/options.inc.php");
	require_once("../drugs/drugs.inc.php");
	require_once("$srcdir/formatting.inc.php");
  require_once("../../custom/code_types.inc.php");
	$comarr = array('allow_sms'=>xl('Allow SMS'),'allow_voice'=>xl('Allow Voice Message'),'allow_mail'=>xl('Allow Mail Message'),'allow_email'=>xl('Allow Email'));
	function add_date($givendate,$day=0,$mth=0,$yr=0) {
		$cd = strtotime($givendate);
		$newdate = date('Y-m-d H:i:s', mktime(date('h',$cd),
		date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
		date('d',$cd)+$day, date('Y',$cd)+$yr));
		return $newdate;
        }	
	$type = $_POST["type"];
	$facility = isset($_POST['facility']) ? $_POST['facility'] : '';
	if($_POST['date_from'] != "")
		$sql_date_from = $_POST['date_from'];
	else
		$sql_date_from = fixDate($_POST['date_from'], date('Y-01-01 H:i:s'));
	
	if($_POST['date_to'] != "")
		$sql_date_to = $_POST['date_to'];
	else
		$sql_date_to = fixDate($_POST['date_to']  , add_date(date('Y-m-d H:i:s')));
		
	
	$patient_id = trim($_POST["patient_id"]);
	$age_from = $_POST["age_from"];
	$age_to = $_POST["age_to"];
	$sql_gender = $_POST["gender"];
	$sql_ethnicity = $_POST["ethnicity"];
	$sql_race=$_POST["race"];
	$form_drug_name = trim($_POST["form_drug_name"]);
	$form_diagnosis = trim($_POST["form_diagnosis"]);
	$form_lab_results = trim($_POST["form_lab_results"]);
	$form_service_codes = trim($_POST["form_service_codes"]);
	$form_immunization = trim($_POST["form_immunization"]);
	$communication = trim($_POST["communication"]);

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

	   function toggle(id) {
                var tr = document.getElementById(id);
                if (tr==null) { return; }
                var bExpand = tr.style.display == '';
                tr.style.display = (bExpand ? 'none' : '');
            }
            function changeimage(id, sMinus, sPlus) {   
                var img = document.getElementById(id);
                if (img!=null) {
                   var bExpand = img.src.indexOf(sPlus) >= 0;
                        if (!bExpand)
                       	img.src = "../pic/blue-up-arrow.gif";	
                        else
                        img.src = "../pic/blue-down-arrow.gif";
                }
            }
	   function Toggle_trGrpHeader2(t_id,i_id) {
                var img=i_id;
                changeimage(img, 'blue-down-arrow.gif', 'blue-up-arrow.gif');
                var id1=t_id;
                toggle(id1);
             }
// This is for callback by the find-code popup.
// Appends to or erases the current list of diagnoses.
function set_related(codetype, code, selector, codedesc) {
 var f = document.forms[0][current_sel_name];
 var s = f.value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 f.value = s;
}

//This invokes the find-code popup.
function sel_diagnosis(e) {
 current_sel_name = e.name;
 dlgopen('../patient_file/encounter/find_code_popup.php?codetype=<?php echo collect_codetypes("diagnosis","csv"); ?>', '_blank', 500, 400);
}

//This invokes the find-code popup.
function sel_procedure(e) {
 current_sel_name = e.name;
 dlgopen('../patient_file/encounter/find_code_popup.php?codetype=<?php echo collect_codetypes("procedure","csv"); ?>', '_blank', 500, 400);
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
.optional_area_service_codes {
	<?php
	if($type != 'Service Codes' || $type == '')
	{
	?>
	display: none;
	<?php
	}
	?>
}
</style>
<script language="javascript" type="text/javascript">
	function checkType() {
		if($('#type').val() == 'Service Codes')
		{
			$('.optional_area_service_codes').css("display", "inline");
		}
		else
		{
			$('.optional_area_service_codes').css("display", "none");
		}
	}
	
	function submitForm() {
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
						<td class='label' width="100"><?php echo htmlspecialchars(xl('Facility'),ENT_NOQUOTES); ?>: </td>
						<td width="250"> <?php dropdown_facility($facility,'facility',false); ?> </td>
						<td class='label' width="100"><?php echo htmlspecialchars(xl('From'),ENT_NOQUOTES); ?>: </td>
						<td><input type='text' name='date_from' id="date_from" size='18' value='<?php echo htmlspecialchars($sql_date_from,ENT_QUOTES); ?>' onkeyup='datekeyup(this,mypcc,true)' onblur='dateblur(this,mypcc,true)' title='yyyy-mm-dd H:m:s'> <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo htmlspecialchars(xl('Click here to choose date time from'),ENT_QUOTES); ?>'></td>
					</tr>
					<tr>
						<td class='label'><?php echo htmlspecialchars(xl('Patient ID'),ENT_NOQUOTES); ?>:</td>
						<td><input name='patient_id' class="numeric_only" type='text' id="patient_id" title='<?php echo htmlspecialchars(xl('Optional numeric patient ID'),ENT_QUOTES); ?>' value='<?php echo htmlspecialchars($patient_id,ENT_QUOTES); ?>' size='10' maxlength='20' /></td>
						<td class='label'><?php echo htmlspecialchars(xl('To'),ENT_NOQUOTES); ?>: </td>
						<td><input type='text' name='date_to' id="date_to" size='18' value='<?php echo htmlspecialchars($sql_date_to,ENT_QUOTES); ?>' onKeyUp='datekeyup(this,mypcc,true)' onBlur='dateblur(this,mypcc,true)' title='yyyy-mm-dd H:m:s'>	<img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo htmlspecialchars(xl('Click here to choose date time to'),ENT_QUOTES); ?>'></td>
					</tr>
					<tr>
						<td class='label'><?php echo htmlspecialchars(xl('Age Range'),ENT_NOQUOTES); ?>:</td>
						<td><?php echo htmlspecialchars(xl('From'),ENT_NOQUOTES); ?> 
							<input name='age_from' class="numeric_only" type='text' id="age_from" value="<?php echo htmlspecialchars($age_from,ENT_QUOTES); ?>" size='3' maxlength='3' /> <?php echo htmlspecialchars(xl('To'),ENT_NOQUOTES); ?> 
							<input name='age_to' class="numeric_only" type='text' id="age_to" value="<?php echo htmlspecialchars($age_to,ENT_QUOTES); ?>" size='3' maxlength='3' /></td>
						<td class='label'><?php echo htmlspecialchars(xl('Problem DX'),ENT_NOQUOTES); ?>:</td>
						<td><input type='text' name='form_diagnosis' size='10' maxlength='250' value='<?php echo htmlspecialchars($form_diagnosis,ENT_QUOTES); ?>' onclick='sel_diagnosis(this)' title='<?php echo htmlspecialchars(xl('Click to select or change diagnoses'),ENT_QUOTES); ?>' readonly /></td>
                                               	<td>&nbsp;</td>
<!-- Visolve -->
					</tr>
					<tr>
						<td class='label'><?php echo htmlspecialchars(xl('Gender'),ENT_NOQUOTES); ?>:</td>
						<td><?php echo generate_select_list('gender', 'sex', $sql_gender, 'Select Gender', 'Unassigned', '', ''); ?></td>
						<td class='label'><?php echo htmlspecialchars(xl('Drug'),ENT_NOQUOTES); ?>:</td>
						<td><input type='text' name='form_drug_name' size='10' maxlength='250' value='<?php echo htmlspecialchars($form_drug_name,ENT_QUOTES); ?>' title='<?php echo htmlspecialchars(xl('Optional drug name, use % as a wildcard'),ENT_QUOTES); ?>' /></td>

					</tr>
					<tr>
						<td class='label'><?php echo htmlspecialchars(xl('Race'),ENT_NOQUOTES); ?>:</td>
						<td><?php echo generate_select_list('race', 'race', $sql_race, 'Select Race', 'Unassigned', '', ''); ?></td>
             			<td class='label'><?php echo htmlspecialchars(xl('Ethnicity'),ENT_NOQUOTES); ?>:</td>
                        <td><?php echo generate_select_list('ethnicity', 'ethnicity', $sql_ethnicity, 'Select Ethnicity', 'Unassigned', '', ''); ?></td>
						<td class='label'><?php echo htmlspecialchars(xl('Immunization'),ENT_NOQUOTES); ?>:</td>
						<td><input type='text' name='form_immunization' size='10' maxlength='250' value='<?php echo htmlspecialchars($form_immunization,ENT_QUOTES); ?>' title='<?php echo htmlspecialchars(xl('Optional immunization name or code, use % as a wildcard'),ENT_QUOTES); ?>' /></td>
					</tr>
					<tr>
						<td class='label' width='100'><?php echo htmlspecialchars(xl('Lab Result'),ENT_NOQUOTES); ?>:</td>
						<td width='100'><input type='text' name='form_lab_results' size='13' maxlength='250' value='<?php echo htmlspecialchars($form_lab_results,ENT_QUOTES); ?>' title='<?php echo htmlspecialchars(xl('Result, use % as a wildcard'),ENT_QUOTES); ?>' /></td>

						<td class='label' width='100'><?php echo htmlspecialchars(xl('Option'),ENT_NOQUOTES); ?>:</td>
						<td><select name="type" id="type" onChange="checkType();">
							<option> <?php echo htmlspecialchars(xl('Select'),ENT_NOQUOTES); ?></option>
							<option value="Procedure" <?php if($type == 'Procedure') { echo "selected"; } ?>><?php echo htmlspecialchars(xl('Procedure'),ENT_NOQUOTES); ?></option>
							<option value="Medical History" <?php if($type == 'Medical History') { echo "selected"; } ?>><?php echo htmlspecialchars(xl('Medical History'),ENT_NOQUOTES); ?></option>
							<option value="Service Codes" <?php if($type == 'Service Codes') { echo "selected"; } ?>><?php echo htmlspecialchars(xl('Service Codes'),ENT_NOQUOTES); ?></option>
						   </select>
						</td>
						<td class='label'><?php echo htmlspecialchars(xl('Communication'),ENT_NOQUOTES); ?>:</td>
                        <td>
							<select name="communication" id="communication" title="<?php echo htmlspecialchars(xl('Select Communication Preferences'),ENT_NOQUOTES); ?>">
								<option value=""> <?php echo htmlspecialchars(xl('Select'),ENT_NOQUOTES); ?></option>
								<?php foreach($comarr as $comkey => $comvalue){ ?>
								<option value="<?php echo attr($comkey); ?>" <?php if($communication == $comkey){ echo "selected";}?>><?php echo text($comvalue); ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
				</table>
				<table>
					<tr class="optional_area_service_codes">
					<td width='100'>&nbsp;</td>		
					<td width='100'>&nbsp;</td>
					<td width='195'>&nbsp;</td>
					<td class='label' width='76'><?php echo htmlspecialchars(xl('Code'),ENT_NOQUOTES); ?>:</td>
                                        <td> <input type='text' name='form_service_codes' size='10' maxlength='250' value='<?php echo htmlspecialchars($form_service_codes,ENT_QUOTES); ?>' onclick='sel_procedure(this)' title='<?php echo htmlspecialchars(xl('Click to select or change service codes'),ENT_QUOTES); ?>' readonly />&nbsp;</td>
                                        </tr>
				</table>
				<table class='text'>
					<tr>
						<!-- Sort by Start -->
                                                 <td class='label' width='63'><?php echo htmlspecialchars(xl('Sort By'),ENT_NOQUOTES); ?>:</td>
                                                 <td>
                                                   <input type='checkbox' name='form_pt_name'<?php if ($_POST['form_pt_name'] == true) echo ' checked'; ?>>
                                                   <?php echo htmlspecialchars(xl('Patient Name'),ENT_NOQUOTES); ?>&nbsp;

                                                   <input type='checkbox' name='form_pt_age'<?php if ($_POST['form_pt_age'] == true) echo ' checked'; ?>>
                                                   <?php echo htmlspecialchars(xl('Age'),ENT_NOQUOTES); ?>&nbsp;

                                                   <input type='checkbox' name='form_diagnosis_code'<?php if ($_POST['form_diagnosis_code'] == true) echo ' checked'; ?>>
                                                   <?php echo htmlspecialchars(xl('Diagnosis Code'),ENT_NOQUOTES); ?>&nbsp;

                                                   <input type='checkbox' name='form_diagnosis_tit'<?php if ($_POST['form_diagnosis_tit'] == true) echo ' checked'; ?>>
                                                   <?php echo htmlspecialchars(xl('Diagnosis Title'),ENT_NOQUOTES); ?>&nbsp;

                                                   <input type='checkbox' name='form_drug'<?php if ($_POST['form_drug'] == true) echo ' checked'; ?>>
						  <?php echo htmlspecialchars(xl('Drug'),ENT_NOQUOTES); ?>&nbsp;

                                                   <input type='checkbox' name='ndc_no'<?php if ($_POST['ndc_no'] == true) echo ' checked'; ?>>
                                                   <?php echo htmlspecialchars(xl('NDC Number'),ENT_NOQUOTES); ?>&nbsp;
                                                   <input type='checkbox' name='lab_results'<?php if ($_POST['lab_results'] == true) echo ' checked'; ?>>
                                                  <?php echo htmlspecialchars(xl('Lab Results'),ENT_NOQUOTES); ?>&nbsp;
                                               </td>
                                        </tr>
				<!-- Sort by ends -->
					</tr>
					<tr>
						<td colspan=3><span id="date_error" style="color: #F00; font-siz: 11px; display: none;"><?php echo htmlspecialchars(xl('From Date Cannot be Greater than To Date.'),ENT_NOQUOTES); ?></span>&nbsp;</td>
					</tr>
				</table>
				</div></td>
				<td height="100%" valign='middle' width="175"><table style='border-left:1px solid; width:100%; height:100%'>
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
$sqlstmt = "select
                concat(pd.fname, ' ', pd.lname) AS patient_name,
                pd.pid AS patient_id,
                DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 AS patient_age,
                pd.sex AS patient_sex,
                pd.race AS patient_race,pd.ethnicity AS patient_ethinic,
                concat(u.fname, ' ', u.lname)  AS users_provider";
	if(strlen($form_diagnosis) > 0)	{
		$sqlstmt=$sqlstmt.",li.date AS lists_date,
                   li.diagnosis AS lists_diagnosis,
                        li.title AS lists_title";
	}
	if(strlen($form_drug_name) > 0)	{

		$sqlstmt=$sqlstmt.",r.id as id, r.date_modified AS prescriptions_date_modified, r.dosage as dosage, r.route as route, r.interval as hinterval, r.refills as refills, r.drug as drug, 
		r.form as hform, r.size as size, r.unit as hunit, d.name as name, d.ndc_number as ndc_number,r.quantity as quantity";
	}

	if(strlen($form_lab_results) > 0) {
    $sqlstmt = $sqlstmt.",pr.date AS procedure_result_date,
                           pr.facility AS procedure_result_facility,
                                pr.units AS procedure_result_units,
                                pr.result AS procedure_result_result,
                                pr.range AS procedure_result_range,
                                pr.abnormal AS procedure_result_abnormal,
                                pr.comments AS procedure_result_comments,
                                pr.document_id AS procedure_result_document_id";
	}

	if ( $type == 'Procedure') {
    $sqlstmt = $sqlstmt.",po.date_ordered AS procedure_order_date_ordered,
            pt.standard_code AS procedure_type_standard_code,
            pc.procedure_name as procedure_name,
            po.order_priority AS procedure_order_order_priority,
            po.order_status AS procedure_order_order_status,
            po.encounter_id AS procedure_order_encounter,
            po.patient_instructions AS procedure_order_patient_instructions,
            po.activity AS procedure_order_activity,
            po.control_id AS procedure_order_control_id ";
  }

       if ( $type == 'Medical History') {
		$sqlstmt = $sqlstmt.",hd.date AS history_data_date,
            hd.tobacco AS history_data_tobacco,
            hd.alcohol AS history_data_alcohol,
            hd.recreational_drugs AS history_data_recreational_drugs   ";
       }
      if($type == 'Service Codes') {
              $sqlstmt .= ", c.code as code,
                        c.code_text as code_text,
                        fe.encounter as encounter,
                        b.date as date";
			$mh_stmt = $mh_stmt.",code,code_text,encounter,date";
      }
	  if (strlen($form_immunization) > 0) {
		$sqlstmt .= ", immc.code_text as imm_code, immc.code_text_short as imm_code_short, immc.id as cvx_code, imm.administered_date as imm_date, imm.amount_administered, imm.amount_administered_unit,  imm.administration_site, imm.note as notes ";
	  }
//from
	$sqlstmt=$sqlstmt." from patient_data as pd left outer join users as u on u.id = pd.providerid
            left outer join facility as f on f.id = u.facility_id";
	
	if(strlen($form_diagnosis) > 0 ){	
		$sqlstmt = $sqlstmt." left outer join lists as li on li.pid  = pd.pid ";
	}

  if ( $type == 'Procedure' ||( strlen($form_lab_results)!=0) ) {
    $sqlstmt = $sqlstmt." left outer join procedure_order as po on po.patient_id = pd.pid
    left outer join procedure_order_code as pc on pc.procedure_order_id = po.procedure_order_id
    left outer join procedure_report as pp on pp.procedure_order_id   = po.procedure_order_id
    left outer join procedure_type as pt on pt.procedure_code = pc.procedure_code and pt.lab_id = po.lab_id ";
  }

	if (strlen($form_lab_results)!=0 ) {
		$sqlstmt = $sqlstmt." left outer join procedure_result as pr on pr.procedure_report_id = pp.procedure_report_id ";
	}
	//Immunization added in clinical report
	if (strlen($form_immunization)!=0 ) {
		$sqlstmt = $sqlstmt." LEFT OUTER JOIN immunizations as imm ON imm.patient_id = pd.pid
						  LEFT OUTER JOIN codes as immc ON imm.cvx_code = immc.id ";
	}
	if(strlen($form_drug_name)!=0) {	
	       $sqlstmt=$sqlstmt." left outer join prescriptions AS r on r.patient_id=pd.pid
                        LEFT OUTER JOIN drugs AS d ON d.drug_id = r.drug_id";
	}
      if ( $type == 'Medical History') {
              $sqlstmt = $sqlstmt." left outer join history_data as hd on hd.pid   =  pd.pid 
            and (isnull(hd.tobacco)  = 0
            or isnull(hd.alcohol)  = 0
            or isnull(hd.recreational_drugs)  = 0)";
      }
      if($type == 'Service Codes') {
            $sqlstmt = $sqlstmt." left outer join billing as b on b.pid = pd.pid
            left outer join form_encounter as fe on fe.encounter = b.encounter and b.code_type = 'CPT4'
            left outer join codes as c on c.code = b.code ";
      }
//where
      $whr_stmt="where 1=1";
      if(strlen($form_diagnosis) > 0 ) {
	    $whr_stmt=$whr_stmt." AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(li.date) <= ?";
	    array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
	}
	if(strlen($form_lab_results)!=0 ) {
              $whr_stmt=$whr_stmt." AND pr.date >= ? AND pr.date < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(pr.date) <= ?";
              array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
 	}
        if(strlen($form_drug_name)!=0) {
	      $whr_stmt=$whr_stmt." AND r.date_modified >= ? AND r.date_modified < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(r.date_modified) <= ?";
              array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
	}
	if($type == 'Medical History') {
	     $whr_stmt=$whr_stmt." AND hd.date >= ? AND hd.date < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(hd.date) <= ?";
             array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
	} 
	if($type == 'Procedure') {       
	     $whr_stmt=$whr_stmt." AND po.date_ordered >= ? AND po.date_ordered < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(po.date_ordered) <= ?";
             array_push($sqlBindArray, substr($sql_date_from,0,10), substr($sql_date_to,0,10), date("Y-m-d"));
	 }
	if($type == "Service Codes") {
             $whr_stmt=$whr_stmt." AND b.date >= ? AND b.date < DATE_ADD(?, INTERVAL 1 DAY) AND DATE(b.date) <= ?";
             array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d"));
	}
        if(strlen($form_lab_results) != 0) {
            $whr_stmt= $whr_stmt." AND (pr.result LIKE ?) ";
            array_push($sqlBindArray, $form_lab_results);
        }          
	if(strlen($form_drug_name) > 0) {
            $whr_stmt .= " AND (
                        d.name LIKE ?
                        OR r.drug LIKE ?
                        ) ";
            array_push($sqlBindArray, $form_drug_name, $form_drug_name);
         }
       if($type == 'Service Codes') {
          if(strlen($form_service_codes) != 0) {
             $whr_stmt = $whr_stmt." AND (b.code = ?) ";
	     $service_code = explode(":",$form_service_codes);
             array_push($sqlBindArray, $service_code[1]);
       }
       }
      if(strlen($patient_id) != 0) {
           $whr_stmt = $whr_stmt."   and pd.pid = ?";
           array_push($sqlBindArray, $patient_id);
       }

     if(strlen($age_from) != 0) {
           $whr_stmt = $whr_stmt."   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 >= ?";
           array_push($sqlBindArray, $age_from);
     }
     if(strlen($age_to) != 0) {
           $whr_stmt = $whr_stmt."   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 <= ?";
           array_push($sqlBindArray, $age_to);
    }
    if(strlen($sql_gender) != 0) {
          $whr_stmt = $whr_stmt."   and pd.sex = ?";
          array_push($sqlBindArray, $sql_gender);
    }
   if(strlen($sql_ethnicity) != 0) {
         $whr_stmt = $whr_stmt."   and pd.ethnicity = ?";
         array_push($sqlBindArray, $sql_ethnicity);
    }
   if(strlen($sql_race) != 0) {
         $whr_stmt = $whr_stmt."   and pd.race = ?";
         array_push($sqlBindArray, $sql_race);
   }
  if($facility != '') {
        $whr_stmt = $whr_stmt."   and f.id = ? ";
        array_push($sqlBindArray, $facility);
  }
  if(strlen($form_diagnosis) > 0) {
        $whr_stmt = $whr_stmt." AND (li.diagnosis LIKE ? or li.diagnosis LIKE ? or li.diagnosis LIKE ? or li.diagnosis = ?) ";
        array_push($sqlBindArray, $form_diagnosis.";%", '%;'.$form_diagnosis.';%', '%;'.$form_diagnosis, $form_diagnosis);
  }
  //communication preferences added in clinical report
  if(strlen($communication) > 0){
	if($communication == "allow_sms")  $whr_stmt .= " AND pd.hipaa_allowsms = 'YES' ";
	else if($communication == "allow_voice")  $whr_stmt .= " AND pd.hipaa_voice = 'YES' ";
	else if($communication == "allow_mail")  $whr_stmt .= " AND pd.hipaa_mail  = 'YES' ";
	else if($communication == "allow_email")  $whr_stmt .= " AND pd.hipaa_allowemail  = 'YES' ";
  }
  
  //Immunization where condition for full text or short text
  if(strlen($form_immunization) > 0) {
	$whr_stmt .= " AND (
				immc.code_text LIKE ?
				OR immc.code_text_short LIKE ?
				) ";
	array_push($sqlBindArray, '%'.$form_immunization.'%', '%'.$form_immunization.'%');
 }
// order by
  if ($_POST['form_pt_name'] == true){
        $odrstmt=$odrstmt.",patient_name";
  }
  if ($_POST['form_pt_age'] == true) {
        $odrstmt=$odrstmt.",patient_age";
  }
  if (($_POST['form_diagnosis_code'] == true) && (strlen($form_diagnosis) > 0)){
        $odrstmt=$odrstmt.",lists_diagnosis";
  }
  if (($_POST['form_diagnosis_tit'] == true) && (strlen($form_diagnosis) > 0)){
         $odrstmt=$odrstmt.",lists_title";
  }
  if (($_POST['form_drug'] == true)&& (strlen($form_drug_name) > 0)){
        $odrstmt=$odrstmt.",r.drug";
  } 
  if (($_POST['ndc_no'] == true) && (strlen($form_drug_name) > 0)) {
         $odrstmt=$odrstmt.",d.ndc_number";
  } 
  if (($_POST['lab_results'] == true) && (strlen($form_lab_results) > 0)) {
         $odrstmt=$odrstmt.",procedure_result_result";
  }

  if($odrstmt == '') {
	$odrstmt = " ORDER BY patient_id";
  }  
  else {
	$odrstmt = " ORDER BY ".ltrim($odrstmt,",");
  }
  
  if($type == 'Medical History') {
      	$sqlstmt="select * from (".$sqlstmt." ".$whr_stmt." ".$odrstmt.",history_data_date desc) a group by patient_id";
  }
  else {
	$sqlstmt=$sqlstmt." ".$whr_stmt." ".$odrstmt;
  }

$result = sqlStatement($sqlstmt,$sqlBindArray);

$row_id = 1.1;//given to each row to identify and toggle
$img_id = 1.2;
$k=1.3;

if(sqlNumRows($result) > 0)
{
   //Added on 6-jun-2k14(regarding displaying smoking code descriptions)  
   $smoke_codes_arr = getSmokeCodes();
?>
<br>
	<div id = "report_results">
	<?php while ($row = sqlFetchArray($result)) { ?>
	<table width=90% align="center" cellpadding="5" cellspacing="0" style="font-family:tahoma;color:black;" border="0">
		<tr bgcolor = "#CCCCCC" style="font-size:15px;">
			<td><b><?php echo htmlspecialchars(xl('Summary of'),ENT_NOQUOTES); echo " "; ?> <?php echo htmlspecialchars($row['patient_name'],ENT_NOQUOTES); ?></b></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td align="center">
			<span onclick="javascript:Toggle_trGrpHeader2(<?php echo $row_id; ?>,<?php echo $img_id; ?>);"><img src="../pic/blue-down-arrow.gif" id="<?php echo $img_id; $img_id++; ?>" title="<?php echo htmlspecialchars( xl('Click here to view patient details'), ENT_QUOTES); ?>" /></span>
			</td></tr>
			<table width="100%" align="center" id = "<?php echo $row_id; $row_id++;?>" class="border1" style="display:none; font-size:13px;" cellpadding=5>
				<tr bgcolor="#C3FDB8" align="left"> 
				<td width="15%"><b><?php echo htmlspecialchars(xl('Patient Name'),ENT_NOQUOTES); ?></b></td>
				<td width="5%"><b><?php echo htmlspecialchars(xl('PID'),ENT_NOQUOTES);?></b></td>
				<td width="5%"><b><?php echo htmlspecialchars(xl('Age'),ENT_NOQUOTES);?></b></td>
				<td width="10%"><b><?php echo htmlspecialchars(xl('Gender'),ENT_NOQUOTES); ?></b></td>
				<td width="15%"><b><?php echo htmlspecialchars(xl('Race'),ENT_NOQUOTES);?></b></td>
				<td width="15%"><b><?php echo htmlspecialchars(xl('Ethnicity'),ENT_NOQUOTES);?></b></td> 
				<td width="15%" <?php if(strlen($communication) == 0){ ?> colspan=5 <?php } ?>><b><?php echo htmlspecialchars(xl('Provider'),ENT_NOQUOTES);?></b></td>
				<?php if(strlen($communication) > 0){ ?>
				<td colspan=4><b><?php echo xlt('Communication');?></b></td>
				<?php } ?>
				</tr>
				<tr bgcolor="#FFFFFF">
				<td><?php echo htmlspecialchars($row['patient_name'],ENT_NOQUOTES); ?>&nbsp;</td>
				<td> <?php echo htmlspecialchars($row['patient_id'],ENT_NOQUOTES); ?>&nbsp;</td>  
				<td> <?php echo htmlspecialchars($row['patient_age'],ENT_NOQUOTES); ?>&nbsp;</td>
                                <td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'sex'), $row['patient_sex']),ENT_NOQUOTES); ?>&nbsp;</td>
				<td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'race'), $row['patient_race']),ENT_NOQUOTES); ?>&nbsp;</td>
                               <td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'ethnicity'), $row['patient_ethinic']),ENT_NOQUOTES); ?>&nbsp;</td>
                               <td <?php if(strlen($communication) == 0){ ?> colspan=5 <?php } ?>> <?php echo htmlspecialchars($row['users_provider'],ENT_NOQUOTES); ?>&nbsp;</td>
							    <?php if(strlen($communication) > 0){ ?>
							   <td colspan=4><?php echo text($comarr["$communication"]); ?></td>
							   <?php } ?>
				</tr>
<!-- Diagnosis Report Start-->
				<?php 
				if(strlen($form_diagnosis) > 0)
			        {
				?>
	                	<tr bgcolor="#C3FDB8" align= "left">
				<td colspan=12><b><?php echo "#"; echo htmlspecialchars(xl('Diagnosis Report'),ENT_NOQUOTES);?></b></td>
				</tr>
				<tr bgcolor="#C3FDB8" align= "left">
				<td><b><?php echo htmlspecialchars(xl('Diagnosis Date'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Diagnosis'),ENT_NOQUOTES);?></b></td>
				<td colspan=9><b><?php echo htmlspecialchars(xl('Diagnosis Name'),ENT_NOQUOTES);?></b></td>
				</tr>
                        	<tr bgcolor="#FFFFFF">
				<td><?php echo htmlspecialchars($row['lists_date'],ENT_NOQUOTES); ?>&nbsp;</td> 
				<td><?php echo htmlspecialchars($row['lists_diagnosis'],ENT_NOQUOTES); ?>&nbsp;</td>
                                <td colspan=9><?php echo htmlspecialchars($row['lists_title'],ENT_NOQUOTES); ?>&nbsp;</td>
				</tr>
	<?php } ?>
<!-- Diagnosis Report End-->

<!-- Prescription Report Start-->
			       <?php
			 	if(strlen($form_drug_name) > 0)
       			 	{
				?>
                        	<tr bgcolor="#C3FDB8" align= "left">
				<td colspan=12><b><?php echo "#"; echo htmlspecialchars(xl('Prescription Report'),ENT_NOQUOTES);?><b></td></tr>
                        	<tr bgcolor="#C3FDB8" align= "left">
				<td><b><?php echo htmlspecialchars(xl('Date'),ENT_NOQUOTES); ?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Drug Name'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Route'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Dosage'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Form'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Interval'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Size'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Unit'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('ReFill'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Quantity'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('NDC'),ENT_NOQUOTES);?></b></td>
				</tr>
                        	<tr bgcolor="#FFFFFF" align="">
				<?php 
					$rx_route =  generate_display_field(array('data_type'=>'1','list_id'=>'drug_route'), $row['route']) ;
					$rx_form = generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'), $row['hform']) ;
					$rx_interval = generate_display_field(array('data_type'=>'1','list_id'=>'drug_interval'), $row['hinterval']) ;
					$rx_units =   generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['hunit']);
				?>
				 <td> <?php echo htmlspecialchars(oeFormatShortDate($row['prescriptions_date_modified']),ENT_NOQUOTES); ?>&nbsp;</td>
				<td><?php echo htmlspecialchars($row['drug'],ENT_NOQUOTES); ?></td>		
				<td><?php echo htmlspecialchars($rx_route,ENT_NOQUOTES); ?></td>
				<td><?php echo htmlspecialchars($row['dosage'],ENT_NOQUOTES); ?></td>	
				<td><?php echo htmlspecialchars($rx_form,ENT_NOQUOTES); ?></td>
				<td><?php echo htmlspecialchars($rx_interval,ENT_NOQUOTES); ?></td>
				<td><?php echo htmlspecialchars($row['size'],ENT_NOQUOTES); ?></td>
				<td><?php echo htmlspecialchars($rx_units,ENT_NOQUOTES); ?></td>
				<td><?php echo htmlspecialchars($row['refills'],ENT_NOQUOTES); ?></td>	
				<td><?php echo htmlspecialchars($row['quantity'],ENT_NOQUOTES); ?></td>
				<td><?php echo htmlspecialchars($row['ndc_number'],ENT_NOQUOTES); ?></td>
	                    	</tr>
				<?php } ?>
<!-- Prescription Report End-->

<!-- Lab Results Report Start-->
				<?php 
				if(strlen($form_lab_results) > 0)
        			{
				?>
                        	<tr bgcolor="#C3FDB8" align= "left">
				<td colspan=12><b><?php echo "#"; echo htmlspecialchars(xl('Lab Results Report'),ENT_NOQUOTES);?><b></td></tr>
                        	<tr bgcolor="#C3FDB8" align= "left">
				<td><b><?php echo htmlspecialchars(xl('Date'),ENT_NOQUOTES); ?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Facility'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Unit'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Result'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Range'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Abnormal'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Comments'),ENT_NOQUOTES);?></b></td>
				<td colspan=4><b><?php echo htmlspecialchars(xl('Document ID'),ENT_NOQUOTES);?></b></td>
				</tr>
                        	<tr bgcolor="#FFFFFF">
				<td> <?php echo htmlspecialchars(oeFormatShortDate($row['procedure_result_date']),ENT_NOQUOTES); ?>&nbsp;</td>
                                <td> <?php echo htmlspecialchars($row['procedure_result_facility'],ENT_NOQUOTES); ?>&nbsp;</td>
                                <td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'proc_unit'),$row['procedure_result_units']),ENT_NOQUOTES); ?>&nbsp;</td>
                                 <td> <?php echo htmlspecialchars($row['procedure_result_result'],ENT_NOQUOTES); ?>&nbsp;</td>
                                 <td> <?php echo htmlspecialchars($row['procedure_result_range'],ENT_NOQUOTES); ?>&nbsp;</td>
                                 <td> <?php echo htmlspecialchars($row['procedure_result_abnormal'],ENT_NOQUOTES); ?>&nbsp;</td>
                                 <td> <?php echo htmlspecialchars($row['procedure_result_comments'],ENT_NOQUOTES); ?>&nbsp;</td>
                                 <td colspan=4> <?php echo htmlspecialchars($row['procedure_result_document_id'],ENT_NOQUOTES); ?>&nbsp;</td>
                        </tr>
				<?php } ?>
<!-- Lab Results End-->

<!-- Procedures Report Start-->
				<?php
				if ( $type == 'Procedure')
                		{
				?>
                        	<tr bgcolor="#C3FDB8" align= "left">
				<td colspan=12><b><?php echo "#"; echo htmlspecialchars(xl('Procedure Report'),ENT_NOQUOTES);?><b></td></tr>
                        	<tr bgcolor="#C3FDB8" align= "left">
				<td><b><?php echo htmlspecialchars(xl('Date'),ENT_NOQUOTES); ?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Standard Name'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Procedure'),ENT_NOQUOTES); ?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Encounter'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Priority'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Status'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Instruction'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Activity'),ENT_NOQUOTES);?></b></td>
				<td colspan=3><b><?php echo htmlspecialchars(xl('Control ID'),ENT_NOQUOTES);?></b></td>
				</tr>
                        	<tr bgcolor="#FFFFFF">
				<?php
                                    $procedure_type_standard_code_arr = explode(':', $row['procedure_type_standard_code']);
                                    $procedure_type_standard_code = $procedure_type_standard_code_arr[1];
                                 ?>
                                  <!-- Procedure -->
                                  <td> <?php echo htmlspecialchars(oeFormatShortDate($row['procedure_order_date_ordered']),ENT_NOQUOTES); ?>&nbsp;</td>
                                  <td> <?php echo htmlspecialchars($procedure_type_standard_code,ENT_NOQUOTES); ?>&nbsp;</td>
                                  <td> <?php echo htmlspecialchars($row['procedure_name'],ENT_NOQUOTES); ?>&nbsp;</td>
                                  <td> <?php echo htmlspecialchars($row['procedure_order_encounter'],ENT_NOQUOTES); ?>&nbsp;</td>
                                  <td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'ord_priority'),$row['procedure_order_order_priority']),ENT_NOQUOTES); ?>&nbsp;</td>
                                  <td> <?php echo htmlspecialchars(generate_display_field(array('data_type'=>'1','list_id'=>'ord_status'),$row['procedure_order_order_status']),ENT_NOQUOTES); ?>&nbsp;</td>
                                  <td> <?php echo htmlspecialchars($row['procedure_order_patient_instructions'],ENT_NOQUOTES); ?>&nbsp;</td>
                                  <td> <?php echo htmlspecialchars($row['procedure_order_activity'],ENT_NOQUOTES); ?>&nbsp;</td>
                                  <td colspan=3> <?php echo htmlspecialchars($row['procedure_order_control_id'],ENT_NOQUOTES); ?>&nbsp;</td>

                         	  </tr>
			<?php } ?>
<!-- Procedure Report End-->

<!-- Medical History Report Start-->
				<?php
				if ( $type == 'Medical History')
                		{
				?>
                        	<tr bgcolor="#C3FDB8" align= "left">
				<td colspan=12><b><?php echo "#"; echo htmlspecialchars(xl('Medical History'),ENT_NOQUOTES);?><b></td></tr>
                        	<tr bgcolor="#C3FDB8" align= "left">
				<td><b><?php echo htmlspecialchars(xl('History Date'),ENT_NOQUOTES); ?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Tobacco'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Alcohol'),ENT_NOQUOTES);?></b></td>
				<td colspan=8><b><?php echo htmlspecialchars(xl('Recreational Drugs'),ENT_NOQUOTES);?></b></td>
				</tr>
                        	<tr bgcolor="#FFFFFF">
				<?php	
					$tmp_t = explode('|', $row['history_data_tobacco']);
					$tmp_a = explode('|', $row['history_data_alcohol']);
					$tmp_d = explode('|', $row['history_data_recreational_drugs']);
                                        $his_tobac =  generate_display_field(array('data_type'=>'1','list_id'=>'smoking_status'), $tmp_t[3]) ;
				?>
				<td> <?php echo htmlspecialchars(oeFormatShortDate($row['history_data_date']),ENT_NOQUOTES); ?>&nbsp;</td>
                                <td> <?php 
                                //Added on 6-jun-2k14(regarding displaying smoking code descriptions)
                                if(!empty($smoke_codes_arr[$tmp_t[3]])){
                                    $his_tobac.= " ( ".$smoke_codes_arr[$tmp_t[3]]." )";
                                }
                                echo htmlspecialchars($his_tobac,ENT_NOQUOTES); ?>&nbsp;</td>
				<?php 
					if ($tmp_a[1] == "currentalcohol") $res = xl('Current Alcohol');
					if ($tmp_a[1] == "quitalcohol") $res = xl('Quit Alcohol');
					if ($tmp_a[1] == "neveralcohol") $res = xl('Never Alcohol');
					if ($tmp_a[1] == "not_applicablealcohol") $res = xl('N/A');
				?>
                                 <td> <?php echo htmlspecialchars($res,ENT_NOQUOTES); ?>&nbsp;</td>
				 <?php
                                         if ($tmp_d[1] == "currentrecreational_drugs") $resd = xl('Current Recreational Drugs');
                                         if ($tmp_d[1] == "quitrecreational_drugs") $resd = xl('Quit');
                                         if ($tmp_d[1] == "neverrecreational_drugs") $resd = xl('Never');
                                         if ($tmp_d[1] == "not_applicablerecreational_drugs") $resd = xl('N/A');                                                      
                                  ?>		
                                  <td colspan=8> <?php echo htmlspecialchars($resd,ENT_NOQUOTES); ?>&nbsp;</td>
		                  </tr>
				  <?php } ?>
<!-- Medical History Report End-->

<!-- Service Codes Report Start-->
				<?php 
				if ( $type == 'Service Codes') {
				?>
                        	<tr bgcolor="#C3FDB8" align= "left">
				<td colspan=11><b><?php echo "#"; echo htmlspecialchars(xl('Service Codes'),ENT_NOQUOTES);?><b></td></tr>
                        	<tr bgcolor="#C3FDB8" align= "left">
				<td><b><?php echo htmlspecialchars(xl('Date'),ENT_NOQUOTES); ?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Code'),ENT_NOQUOTES);?></b></td>
				<td><b><?php echo htmlspecialchars(xl('Encounter ID'),ENT_NOQUOTES);?></b></td>
				<td colspan=8><b><?php echo htmlspecialchars(xl('Code Text'),ENT_NOQUOTES);?></b></td></tr>
	                        <tr bgcolor="#FFFFFF">
				<td><?php echo htmlspecialchars(oeFormatShortDate($row['date']),ENT_NOQUOTES); ?>&nbsp;</td>
			        <td><?php echo htmlspecialchars($row['code'],ENT_NOQUOTES); ?>&nbsp;</td>
                		<td><?php echo htmlspecialchars($row['encounter'],ENT_NOQUOTES); ?>&nbsp;</td>      
				<td colspan=8><?php echo htmlspecialchars($row['code_text'],ENT_NOQUOTES); ?>&nbsp;</td>
	                        </tr>
				<?php } ?>
<!-- Service Codes Report End-->

<!-- Immunization Report Start-->
				<?php 
				if(strlen($form_immunization) > 0){?>
					<tr bgcolor="#C3FDB8" align= "left">
						<td colspan=12><b><?php echo "#"; echo htmlspecialchars(xl('Immunization Report'),ENT_NOQUOTES);?></b></td>
					</tr>
					<tr bgcolor="#C3FDB8" align= "left">
						<td><b><?php echo htmlspecialchars(xl('Immunization Date'),ENT_NOQUOTES);?></b></td>
						<td><b><?php echo htmlspecialchars(xl('CVX Code'),ENT_NOQUOTES);?></b></td>
						<td><b><?php echo htmlspecialchars(xl('Vaccine'),ENT_NOQUOTES);?></b></td>
						<td><b><?php echo htmlspecialchars(xl('Amount'),ENT_NOQUOTES);?></b></td>
						<td><b><?php echo htmlspecialchars(xl('Administered Site'),ENT_NOQUOTES);?></b></td>
						<td colspan="7"><b><?php echo htmlspecialchars(xl('Notes'),ENT_NOQUOTES);?></b></td>
					</tr>
					<tr bgcolor="#FFFFFF">
						<td><?php echo htmlspecialchars($row['imm_date'],ENT_NOQUOTES); ?>&nbsp;</td> 
						<td><?php echo htmlspecialchars($row['cvx_code'],ENT_NOQUOTES); ?>&nbsp;</td>
						<td><?php echo htmlspecialchars($row['imm_code_short'],ENT_NOQUOTES)." (".htmlspecialchars($row['imm_code']).")"; ?>&nbsp;</td>
						<td>
					    <?php 
						if ($row["amount_administered"] > 0) {
							echo htmlspecialchars( $row["amount_administered"] . " " . generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['amount_administered_unit']) , ENT_NOQUOTES); 
						}else{
							echo "&nbsp;";
						}
						?>
							
					  </td>
					  
					  <td>
					   <?php echo generate_display_field(array('data_type'=>'1','list_id'=>'proc_body_site'), $row['administration_site']); ?>
					  </td>
					  
					  <td colspan="7">
					   <?php echo htmlspecialchars($row['notes']); ?>
					  </td>
					</tr>
			<?php } ?>
<!-- Immunization Report End-->
                       		 </table>
		 <?php }  //while loop end ?>
		</table> <!-- Main table ends -->
<?php 
} //End if $result
} //End if form_refresh
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
	Calendar.setup({inputField:"date_from", ifFormat:"%Y-%m-%d %H:%M:%S", button:"img_from_date", showsTime:true});
 	Calendar.setup({inputField:"date_to", ifFormat:"%Y-%m-%d %H:%M:%S", button:"img_to_date", showsTime:true});
</script>
</html>
