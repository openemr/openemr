<?php
/**
 * This report lists all the demographics allergies,problems,drugs and lab results
 *
 * Copyright (C) 2014 Ensoftek, Inc
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 */

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
	require_once("$srcdir/payment_jav.inc.php");
	
	$DateFormat=DateFormatRead();
	$search_options = array("Demographics"=>xl("Demographics"),"Problems"=>xl("Problems"),"Medications"=>xl("Medications"),"Allergies"=>xl("Allergies"),"Lab results"=>xl("Lab Results"),"Communication"=>xl("Communication"));
	$comarr = array("allow_sms"=>xl("Allow SMS"),"allow_voice"=>xl("Allow Voice Message"),"allow_mail"=>xl("Allow Mail Message"),"allow_email"=>xl("Allow Email"));
	$_POST['form_details'] = true;
	function add_date($givendate,$day=0,$mth=0,$yr=0) {
		$cd = strtotime($givendate);
		$newdate = date('Y-m-d H:i:s', mktime(date('h',$cd),
		date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
		date('d',$cd)+$day, date('Y',$cd)+$yr));
		return $newdate;
        }
	if($_POST['date_from'] != "")
		$sql_date_from = $_POST['date_from'];
	else
		$sql_date_from = fixDate($_POST['date_from'], date('Y-01-01 H:i:s'));
	
	if($_POST['date_to'] != "")
		$sql_date_to = $_POST['date_to'];
	else
		$sql_date_to = fixDate($_POST['date_to']  , add_date(date('Y-m-d H:i:s')));	

	//echo "<pre>";print_r($_POST);
	$patient_id = trim($_POST["patient_id"]);
	$age_from = $_POST["age_from"];
	$age_to = $_POST["age_to"];
	$sql_gender = $_POST["gender"];
	$sql_ethnicity = $_POST["cpms_ethnicity"];
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
			<?php echo xlt('Patient List Creation'); ?>
		</title>
		<script type="text/javascript" src="../../library/overlib_mini.js"></script>
		<script type="text/javascript" src="../../library/dialog.js"></script>
		<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
		<script language="JavaScript">
		var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
		var global_date_format = '%Y-%m-%d';				
		function Form_Validate() {
			var d = document.forms[0];		 
			FromDate = d.date_from.value;
			ToDate = d.date_to.value;
			if ( (FromDate.length > 0) && (ToDate.length > 0) ) {
				if ( FromDate > ToDate ){
					alert("<?php echo xls('To date must be later than From date!'); ?>");
					return false;
				}
			}	
			$("#processing").show();
			return true;
		}
		
		</script>
		<script type="text/javascript" src="../../library/dialog.js"></script>
		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui-1.8.5.custom.css" type="text/css" />
		<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.css" media="screen" />
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.4.3.min.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui-1.8.5.custom.min.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.patched.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
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
			#report_image {
				visibility: hidden;
				display: none;
			}
			}

			/* specifically exclude some from the screen */
			@media screen {
			#report_parameters_daterange {
				visibility: hidden;
				display: none;
			}
			}
			
		</style>
		<script language="javascript" type="text/javascript">
					
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
                                        top.restoreSession(); 
					$("#theform").submit();
				}
			}
			
			//sorting changes
			function sortingCols(sort_by,sort_order)
			{
				$("#sortby").val(sort_by);
				$("#sortorder").val(sort_order);
				$("#form_refresh").attr("value","true"); 
				$("#theform").submit();
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
				<?php if($_POST['srch_option'] == "Communication"){ ?>
						$('#com_pref').show();
				<?php } ?>
			});		
			
		</script>
	</head>
	
	<body class="body_top">
		<!-- Required for the popup date selectors -->
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
		<span class='title'>
		<?php echo xlt('Report - Patient List Creation');?>
		</span>
		<!-- Search can be done using age range, gender, and ethnicity filters.
		Search options include diagnosis, procedure, prescription, medical history, and lab results.
		-->

		<div id="report_parameters_daterange"> 
			<p>
			<?php echo "<span style='margin-left:5px;'><b>".xlt('Date Range').":</b>&nbsp;".text(date($sql_date_from, strtotime($sql_date_from))) .
			  " &nbsp; to &nbsp; ". text(date($sql_date_to, strtotime($sql_date_to)))."</span>"; ?>
			<span style="margin-left:5px; " ><b><?php echo xlt('Option'); ?>:</b>&nbsp;<?php echo text($_POST['srch_option']); 
			if($_POST['srch_option'] == "Communication" && $_POST['communication'] != ""){
				if(isset($comarr[$_POST['communication']]))
				echo "(".text($comarr[$_POST['communication']]).")";
				else
				echo "(".xlt('All').")";
			}  ?></span>	
			</p>
		</div>
		<form name='theform' id='theform' method='post' action='patient_list_creation.php' onSubmit="return Form_Validate();">
			<div id="report_parameters">
				<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
				<table>
					  <tr>
					<td width='900px'><div style='float:left'>
						<table class='text'>
							<tr>
								<td class='label' ><?php echo xlt('From'); ?>: </td>
								<td><input type='text' name='date_from' id="date_from" size='18' value='<?php echo attr($sql_date_from); ?>' readonly="readonly" title='<?php echo attr($title_tooltip) ?>'> <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xla('Click here to choose a date'); ?>'></td>
								<td class='label'><?php echo xlt('To{{range}}'); ?>: </td>
								<td><input type='text' name='date_to' id="date_to" size='18' value='<?php echo attr($sql_date_to); ?>' readonly="readonly" title='<?php echo  attr($title_tooltip) ?>'>	<img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xla('Click here to choose a date'); ?>'></td>
								<td class='label'><?php echo xlt('Option'); ?>: </td>
								<td class='label'>
									<select name="srch_option" id="srch_option" onchange="javascript:$('#sortby').val('');$('#sortorder').val('');if(this.value == 'Communication'){ $('#communication').val('');$('#com_pref').show();}else{ $('#communication').val('');$('#com_pref').hide();}">
										<?php foreach($search_options as $skey => $svalue){ ?>
										<option <?php if($_POST['srch_option'] == $skey) echo 'selected'; ?> value="<?php echo attr($skey); ?>"><?php echo text($svalue); ?></option>
										<?php } ?>									
									</select>
									<?php ?>
								</td>
								
								<td > 
									<span id="com_pref" style="display:none">
									<select name="communication" id="communication" title="<?php echo xlt('Select Communication Preferences'); ?>">
										<option> <?php echo xlt('All'); ?></option>
										<option value="allow_sms" <?php if($communication == "allow_sms"){ echo "selected";}?>><?php echo xlt('Allow SMS'); ?></option>
										<option value="allow_voice" <?php if($communication == "allow_voice"){ echo "selected";}?>><?php echo xlt('Allow Voice Message'); ?></option>
										<option value="allow_mail" <?php if($communication == "allow_mail"){ echo "selected";}?>><?php echo xlt('Allow Mail Message'); ?></option>
										<option value="allow_email" <?php if($communication == "allow_email"){ echo "selected";}?>><?php echo xlt('Allow Email'); ?></option>
									</select>
									</span>
								</td>
								
							</tr>
							<tr>
								<td class='label'><?php echo xlt('Patient ID'); ?>:</td>
								<td><input name='patient_id' class="numeric_only" type='text' id="patient_id" title='<?php echo xla('Optional numeric patient ID'); ?>' value='<?php echo attr($patient_id); ?>' size='10' maxlength='20' /></td>
								<td class='label'><?php echo xlt('Age Range'); ?>:</td>
								<td><?php echo xlt('From'); ?> 
								<input name='age_from' class="numeric_only" type='text' id="age_from" value="<?php echo attr($age_from); ?>" size='3' maxlength='3' /> <?php echo xlt('To{{range}}'); ?> 
								<input name='age_to' class="numeric_only" type='text' id="age_to" value="<?php echo attr($age_to); ?>" size='3' maxlength='3' /></td>
								<td class='label'><?php echo xlt('Gender'); ?>:</td>
								<td colspan="2"><?php echo generate_select_list('gender', 'sex', $sql_gender, 'Select Gender', 'Unassigned', '', ''); ?></td>
							</tr>
							
						</table>
						
						</div></td>
						<td height="100%" valign='middle' width="175"><table style='border-left:1px solid; width:100%; height:100%'>
							<tr>
								<td width="130px"><div style='margin-left:15px'> <a href='#' class='css_button' onclick='submitForm();'> <span>
											<?php echo xlt('Submit'); ?>
											</span> </a>
									</div>
								</td>
								<td>
									<div id='processing' style='display:none;' ><img src='../pic/ajax-loader.gif'/></div>
								</td>
									
							</tr>
						</table></td>
					</tr>
				</table>
			</div>
		<!-- end of parameters -->
		<?php
		//$sql_date_from=DateTimeToYYYYMMDD($sql_date_from);
		//$sql_date_to=DateTimeToYYYYMMDD($sql_date_to);

		// SQL scripts for the various searches
		$sqlBindArray = array();
		if ($_POST['form_refresh']){
			
			$sqlstmt = "select 
						pd.date as patient_date,
						concat(pd.lname, ', ', pd.fname) AS patient_name,
						pd.pid AS patient_id,
						DATE_FORMAT(FROM_DAYS(DATEDIFF('".date('Y-m-d H:i:s')."',pd.dob)), '%Y')+0 AS patient_age,
						pd.sex AS patient_sex,
						pd.race AS patient_race,pd.ethnicity AS patient_ethinic,
						concat(u.lname, ', ', u.fname)  AS users_provider";
			
			$srch_option = $_POST['srch_option'];
			switch ($srch_option) {
				case "Medications":
				case "Allergies":
				case "Problems":
					$sqlstmt=$sqlstmt.",li.date AS lists_date,
						   li.diagnosis AS lists_diagnosis,
								li.title AS lists_title";
					break;
				case "Lab results":
					$sqlstmt = $sqlstmt.",pr.date AS procedure_result_date,
							pr.facility AS procedure_result_facility,
							pr.units AS procedure_result_units,
							pr.result AS procedure_result_result,
							pr.range AS procedure_result_range,
							pr.abnormal AS procedure_result_abnormal,
							pr.comments AS procedure_result_comments,
							pr.document_id AS procedure_result_document_id";
					break;
				case "Communication":
					$sqlstmt = $sqlstmt.",REPLACE(REPLACE(concat_ws(',',IF(pd.hipaa_allowemail = 'YES', 'Allow Email','NO'),IF(pd.hipaa_allowsms = 'YES', 'Allow SMS','NO') , IF(pd.hipaa_mail = 'YES', 'Allow Mail Message','NO') , IF(pd.hipaa_voice = 'YES', 'Allow Voice Message','NO') ), ',NO',''), 'NO,','') as communications";
					break;	
			}		
						
			//from
			$sqlstmt=$sqlstmt." from patient_data as pd left outer join users as u on u.id = pd.providerid";
			//JOINS
			switch ($srch_option) {
				case "Problems":
					$sqlstmt = $sqlstmt." left outer join lists as li on (li.pid  = pd.pid AND li.type='medical_problem')";
					break;
				case "Medications":
					$sqlstmt = $sqlstmt." left outer join lists as li on (li.pid  = pd.pid AND (li.type='medication')) ";
					break;
				case "Allergies":
					$sqlstmt = $sqlstmt." left outer join lists as li on (li.pid  = pd.pid AND (li.type='allergy')) ";
					break;
				case "Lab results":
											
					$sqlstmt = $sqlstmt." left outer join procedure_order as po on po.patient_id = pd.pid
							left outer join procedure_order_code as pc on pc.procedure_order_id = po.procedure_order_id 
							left outer join procedure_report as pp on pp.procedure_order_id = po.procedure_order_id 
							left outer join procedure_type as pt on pt.procedure_code = pc.procedure_code and pt.lab_id = po.lab_id 
							left outer join procedure_result as pr on pr.procedure_report_id = pp.procedure_report_id";
					break;			
			}		
							
			//WHERE Conditions started
			$whr_stmt="where 1=1";
			switch ($srch_option) {
				case "Medications":
				case "Allergies":
					$whr_stmt=$whr_stmt." AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND li.date <= ?";
					array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
					break;
				case "Problems":
					$whr_stmt = $whr_stmt." AND li.title != '' ";
					$whr_stmt=$whr_stmt." AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND li.date <= ?";
					array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
					break;
				case "Lab results":
					$whr_stmt=$whr_stmt." AND pr.date >= ? AND pr.date < DATE_ADD(?, INTERVAL 1 DAY) AND pr.date <= ?";
					$whr_stmt= $whr_stmt." AND (pr.result != '') ";
					array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
					break;
				case "Communication":
					$whr_stmt .= " AND (pd.hipaa_allowsms = 'YES' OR pd.hipaa_voice = 'YES' OR pd.hipaa_mail  = 'YES' OR pd.hipaa_allowemail  = 'YES') ";
					break;		
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
			
			if($srch_option == "Communication" && strlen($communication) > 0){
				if($communication == "allow_sms")  $whr_stmt .= " AND pd.hipaa_allowsms = 'YES' ";
				else if($communication == "allow_voice")  $whr_stmt .= " AND pd.hipaa_voice = 'YES' ";
				else if($communication == "allow_mail")  $whr_stmt .= " AND pd.hipaa_mail  = 'YES' ";
				else if($communication == "allow_email")  $whr_stmt .= " AND pd.hipaa_allowemail  = 'YES' ";
			}
									
			//Sorting By filter fields
			$sortby = $_REQUEST['sortby'];
			 $sortorder = $_REQUEST['sortorder'];
			 
			 // This is for sorting the records.
			 switch ($srch_option) {
				case "Medications":
				case "Allergies":
				case "Problems":
					$sort = array("lists_date","lists_diagnosis","lists_title");
					if($sortby == "")$sortby = $sort[1];
					break;
				case "Lab results":
					$sort = array("procedure_result_date","procedure_result_facility","procedure_result_units","procedure_result_result","procedure_result_range","procedure_result_abnormal");
					//$odrstmt = " procedure_result_result";
					break;	
				case "Communication":
					//$commsort = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(','))";
					$sort = array("patient_date","patient_name","patient_id","patient_age","patient_sex","users_provider", "communications");
					if($sortby == "")$sortby = $sort[6];
					//$odrstmt = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) , communications";
					break;
				case "Demographics":
					$sort = array("patient_date","patient_name","patient_id","patient_age","patient_sex","patient_race","patient_ethinic","users_provider");
					break;		
			}
				if($sortby == "") {
					$sortby = $sort[0];
				}
				if($sortorder == "") {
					$sortorder = "asc";
				}
				for($i = 0; $i < count($sort); $i++) {
					  $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols('$sort[$i]','asc');\" ><img src=\"../../images/sortdown.gif\" border=0 alt=\"".xla('Sort Up')."\"></a>";
				}
				for($i = 0; $i < count($sort); $i++) {
					if($sortby == $sort[$i]) {
						switch($sortorder) {
							case "asc"      : $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols('$sortby','desc');\" ><img src=\"../../images/sortup.gif\" border=0 alt=\"".htmlspecialchars( xl('Sort Up'), ENT_QUOTES)."\"></a>"; break;
							case "desc"     : $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols('$sortby','asc');\" onclick=\"top.restoreSession()\"><img src=\"../../images/sortdown.gif\" border=0 alt=\"".xla('Sort Down')."\"></a>"; break;
						} break;
					}
				}
			
			switch ($srch_option) {
				case "Medications":
				case "Allergies":
				case "Problems":
					$odrstmt = " ORDER BY lists_date asc";
					break;
				case "Lab results":
					$odrstmt = " ORDER BY procedure_result_date asc";
					break;	
				case "Communication":
					$odrstmt = "ORDER BY ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) asc, communications asc";
					break;
				case "Demographics":
					$odrstmt = " ORDER BY patient_date asc";
					//$odrstmt = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) , communications";
					break;		
			}
			if(!empty($_REQUEST['sortby']) && !empty($_REQUEST['sortorder'])){
				if($_REQUEST['sortby'] =="communications"){
					$odrstmt = "ORDER BY ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) ".escape_sort_order($_REQUEST['sortorder']).", communications ".escape_sort_order($_REQUEST['sortorder']);
				}else{
					$odrstmt = "ORDER BY ".escape_identifier($_REQUEST['sortby'],$sort,TRUE)." ".escape_sort_order($_REQUEST['sortorder']);
				}
			}
			
			$sqlstmt=$sqlstmt." ".$whr_stmt." ".$odrstmt;
			//echo $sqlstmt."<hr>"; 	
			$result = sqlStatement($sqlstmt,$sqlBindArray);
			//print_r($result);
			$row_id = 1.1;//given to each row to identify and toggle
			$img_id = 1.2;
			$k=1.3;

			if(sqlNumRows($result) > 0){
				$patArr = array();
						
				$patDataArr = array();
				$smoke_codes_arr = getSmokeCodes();
				while ($row = sqlFetchArray($result)) {
				
						$patArr[] = $row['patient_id'];
						$patInfoArr = array();
						$patInfoArr['patient_id'] = $row['patient_id'];
						//Diagnosis Check
						if($srch_option == "Medications" || $srch_option == "Allergies" || $srch_option == "Problems"){							
							$patInfoArr['lists_date'] = $row['lists_date'];
							$patInfoArr['lists_diagnosis'] = $row['lists_diagnosis'];
							$patInfoArr['lists_title'] = $row['lists_title'];							
							$patInfoArr['patient_name'] = $row['patient_name'];
							$patInfoArr['patient_age'] = $row['patient_age'];
							$patInfoArr['patient_sex'] = $row['patient_sex'];
							$patInfoArr['patient_race'] = $row['patient_race'];
							$patInfoArr['patient_ethinic'] = $row['patient_ethinic'];
							$patInfoArr['users_provider'] = $row['users_provider'];
						}elseif($srch_option == "Lab results"){ 
							$patInfoArr['procedure_result_date'] = $row['procedure_result_date'];
							$patInfoArr['procedure_result_facility'] = $row['procedure_result_facility'];
							$patInfoArr['procedure_result_units'] = $row['procedure_result_units'];
							$patInfoArr['procedure_result_result'] = $row['procedure_result_result'];
							$patInfoArr['procedure_result_range'] = $row['procedure_result_range'];
							$patInfoArr['procedure_result_abnormal'] = $row['procedure_result_abnormal'];
							$patInfoArr['procedure_result_comments'] = $row['procedure_result_comments'];
							$patInfoArr['procedure_result_document_id'] = $row['procedure_result_document_id'];
						}elseif($srch_option == "Communication"){
							$patInfoArr['patient_date'] = $row['patient_date'];
							$patInfoArr['patient_name'] = $row['patient_name'];
							$patInfoArr['patient_age'] = $row['patient_age'];
							$patInfoArr['patient_sex'] = $row['patient_sex'];
							$patInfoArr['users_provider'] = $row['users_provider'];
							$patInfoArr['communications'] = $row['communications'];
						}elseif($srch_option == "Demographics"){
							$patInfoArr['patient_date'] = $row['patient_date'];
							$patInfoArr['patient_name'] = $row['patient_name'];
							$patInfoArr['patient_age'] = $row['patient_age'];
							$patInfoArr['patient_sex'] = $row['patient_sex'];
							$patInfoArr['patient_race'] = $row['patient_race'];
							$patInfoArr['patient_ethinic'] = $row['patient_ethinic'];
							$patInfoArr['users_provider'] = $row['users_provider'];
						}	
									
							$patFinalDataArr[] = $patInfoArr;			
															
				}
				
                                                               
			?>
			
				<br>
				
				<input type="hidden" name="sortby" id="sortby" value="<?php echo attr($sortby); ?>" />
				<input type="hidden" name="sortorder" id="sortorder" value="<?php echo attr($sortorder); ?>" /> 
				<div id = "report_results">
					<table>
						<tr>
							<td class="text"><strong><?php echo xlt('Total Number of Patients')?>:</strong>&nbsp;<span id="total_patients"><?php echo attr(count(array_unique($patArr)));?></span></td>
						</tr>
					</table>
					
					<table width=90% align="center" cellpadding="5" cellspacing="0" style="font-family:tahoma;color:black;" border="0">
					
					<?php if($srch_option == "Medications" || $srch_option == "Allergies" || $srch_option == "Problems"){ ?>	
						<tr style="font-size:15px;"> 
							<td width="15%"><b><?php echo xlt('Diagnosis Date'); ?><?php echo $sortlink[0]; ?></b></td>
							<td width="15%"><b><?php echo xlt('Diagnosis'); ?><?php echo $sortlink[1]; ?></b></td>
							<td width="15%"><b><?php echo xlt('Diagnosis Name');?><?php echo $sortlink[2]; ?></b></td>
							<td width="15%"><b><?php echo xlt('Patient Name'); ?></b></td>
							<td width="5%"><b><?php echo xlt('PID');?></b></td>
							<td width="5%"><b><?php echo xlt('Age');?></b></td> 
							<td width="10%"><b><?php echo xlt('Gender');?></b></td>
							<td colspan=4><b><?php echo xlt('Provider');?></b></td>	
						</tr>
					<?php foreach($patFinalDataArr as $patKey => $patDetailVal){ ?>
								<tr bgcolor = "#CCCCCC" style="font-size:15px;">
									<td ><?php echo text($patDetailVal['lists_date']); ?></td>
									<td ><?php echo text($patDetailVal['lists_diagnosis']); ?></td>
									<td ><?php echo text($patDetailVal['lists_title']); ?></td>									
									<td ><?php echo text($patDetailVal['patient_name']); ?></td>
									<td ><?php echo text($patDetailVal['patient_id']); ?></td>
									<td ><?php echo text($patDetailVal['patient_age']);?></td>
									<td ><?php echo text($patDetailVal['patient_sex']);?></td> 
									<td colspan=4><?php echo text($patDetailVal['users_provider']);?></td>	
								</tr>	
					<?php	}
					}elseif($srch_option == "Lab results"){ ?>
						<tr bgcolor="#C3FDB8" align= "left" >
							<td width="15%"><b><?php echo xlt('Date'); ?><?php echo $sortlink[0]; ?></b></td>
							<td width="15%"><b><?php echo xlt('Facility');?><?php echo $sortlink[1]; ?></b></td>
							<td width="10%"><b><?php echo xlt('Unit');?></b><?php echo $sortlink[2]; ?></td>
							<td width="10%"><b><?php echo xlt('Result');?></b><?php echo $sortlink[3]; ?></td>
							<td width="10%"><b><?php echo xlt('Range');?></b><?php echo $sortlink[4]; ?></td>
							<td width="10%"><b><?php echo xlt('Abnormal');?><?php echo $sortlink[5]; ?></b></td>
							<td><b><?php echo xlt('Comments');?></b></td>
							<td width="5%"><b><?php echo xlt('Document ID');?></b></td>
							<td width="5%"><b><?php echo xlt('PID');?></b></td>
						</tr>
						<?php
							foreach($patFinalDataArr as $patKey => $labResInsideArr){?>
								<tr bgcolor = "#CCCCCC" >
									<td> <?php echo text($labResInsideArr['procedure_result_date']);?>&nbsp;</td>
									<td> <?php echo text($labResInsideArr['procedure_result_facility'],ENT_NOQUOTES); ?>&nbsp;</td>
									<td> <?php echo generate_display_field(array('data_type'=>'1','list_id'=>'proc_unit'),$labResInsideArr['procedure_result_units']); ?>&nbsp;</td>
									<td> <?php echo text($labResInsideArr['procedure_result_result']); ?>&nbsp;</td>
									<td> <?php echo text($labResInsideArr['procedure_result_range']); ?>&nbsp;</td>
									<td> <?php echo text($labResInsideArr['procedure_result_abnormal']); ?>&nbsp;</td>
									<td> <?php echo text($labResInsideArr['procedure_result_comments']); ?>&nbsp;</td>
									<td> <?php echo text($labResInsideArr['procedure_result_document_id']); ?>&nbsp;</td>
									<td colspan="3"> <?php echo text($labResInsideArr['patient_id']); ?>&nbsp;</td>
							   </tr>
						<?php
							}
					}elseif($srch_option == "Communication"){ ?> 
						<tr style="font-size:15px;">
							<td width="15%"><b><?php echo xlt('Date'); ?></b><?php echo $sortlink[0]; ?></td>
							<td width="20%"><b><?php echo xlt('Patient Name'); ?></b><?php echo $sortlink[1]; ?></td>
							<td width="5%"><b><?php echo xlt('PID');?></b><?php echo $sortlink[2]; ?></td>
							<td width="5%"><b><?php echo xlt('Age');?></b><?php echo $sortlink[3]; ?></td> 
							<td width="10%"><b><?php echo xlt('Gender');?></b><?php echo $sortlink[4]; ?></td>
							<td width="15%"><b><?php echo xlt('Provider');?></b><?php echo $sortlink[5]; ?></td>
							<td ><b><?php echo xlt('Communication');?></b><?php echo $sortlink[6]; ?></td>		
						</tr>
					<?php foreach($patFinalDataArr as $patKey => $patDetailVal){ ?>
								<tr bgcolor = "#CCCCCC" >
									<td ><?php if($patDetailVal['patient_date'] != ''){ echo text($patDetailVal['patient_date']);  }else{ echo ""; }; ?></td>
									<td ><?php echo text($patDetailVal['patient_name']); ?></td>
									<td ><?php echo text($patDetailVal['patient_id']); ?></td>
									<td ><?php echo text($patDetailVal['patient_age']);?></td>
									<td ><?php echo text($patDetailVal['patient_sex']);?></td> 
									<td ><?php echo text($patDetailVal['users_provider']);?></td>
									<td ><?php echo text($patDetailVal['communications']);?></td>
							   </tr>
						<?php
							}							
					}elseif($srch_option == "Demographics"){ ?>
						<tr style="font-size:15px;"> 
							<td width="15%"><b><?php echo xlt('Date'); ?></b><?php echo $sortlink[0]; ?></td>
							<td width="20%"><b><?php echo xlt('Patient Name'); ?></b><?php echo $sortlink[1]; ?></td>
							<td width="15%"><b><?php echo xlt('PID');?></b><?php echo $sortlink[2]; ?></td>
							<td width="5%"><b><?php echo xlt('Age');?></b><?php echo $sortlink[3]; ?></td>
							<td width="10%"><b><?php echo xlt('Gender'); ?></b><?php echo $sortlink[4]; ?></td>
							<td width="20%"><b><?php echo xlt('Race');?></b><?php echo $sortlink[5]; ?></td>
							<td colspan=5><b><?php echo xlt('Provider');?></b><?php echo $sortlink[7]; ?></td>
						</tr>
							<?php foreach($patFinalDataArr as $patKey => $patDetailVal){ ?>
								<tr bgcolor = "#CCCCCC" style="font-size:15px;">
									<td ><?php if($patDetailVal['patient_date'] != ''){ echo text($patDetailVal['patient_date']);  }else{ echo ""; };?></td>	
									<td ><?php echo text($patDetailVal['patient_name']); ?></td>
									<td ><?php echo text($patDetailVal['patient_id']); ?></td>
									<td ><?php echo text($patDetailVal['patient_age']);?></td>
									<td ><?php echo text($patDetailVal['patient_sex']);?></td>
									<td ><?php echo generate_display_field(array('data_type'=>'36','list_id'=>'race'), $patDetailVal['patient_race']); ?></td>
									<td colspan=5><?php echo text($patDetailVal['users_provider']);?></td>	
								</tr>	
						<?php	}	
					} ?>			
																		
					</table>
					 <!-- Main table ends -->
				<?php 
				}else{//End if $result?>
					<table>
						<tr>
							<td class="text">&nbsp;&nbsp;<?php echo xlt('No records found.')?></td>
						</tr>
					</table>
				<?php
				}
				?>
				</div>
				
			<?php
			}else{//End if form_refresh
				?><div class='text'> <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?> </div><?php
			}
			?>
		</form>

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
	</body>
</html>
