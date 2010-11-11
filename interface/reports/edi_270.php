<?php
// Copyright (C) 2010 MMF Systems, Inc>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

	/** This report is the batch report required for batch eligibility verification. **/

	//SANITIZE ALL ESCAPES
	$sanitize_all_escapes=true;
	//

	//STOP FAKE REGISTER GLOBALS
	$fake_register_globals=false;
	//

	require_once("../globals.php");
	require_once("$srcdir/forms.inc");
	require_once("$srcdir/billing.inc");
	require_once("$srcdir/patient.inc");
	require_once("$srcdir/formatting.inc.php");
	require_once "$srcdir/options.inc.php";
	require_once "$srcdir/formdata.inc.php";
	include_once("$srcdir/calendar.inc");
	include_once("$srcdir/edi.inc");

	// Element data seperator		
	$eleDataSep		= "*";

	// Segment Terminator	
	$segTer			= "~"; 	

	// Component Element seperator
	$compEleSep		= "^"; 	
	
	// filter conditions for the report and batch creation 

	$from_date		= fixDate($_POST['form_from_date'], date('Y-m-d'));
	$to_date		= fixDate($_POST['form_to_date'], date('Y-m-d'));
	$form_facility	= $_POST['form_facility'] ? $_POST['form_facility'] : '';
	$form_provider	= $_POST['form_users'] ? $_POST['form_users'] : '';
	$exclude_policy = $_POST['removedrows'] ? $_POST['removedrows'] : '';
	$X12info		= $_POST['form_x12'] ? explode("|",$_POST['form_x12']) : '';

	//Set up the sql variable binding array (this prevents sql-injection attacks)
	$sqlBindArray = array();

	$where  = "e.pc_pid IS NOT NULL AND e.pc_eventDate >= ?";
	array_push($sqlBindArray, $from_date);
	
	//$where .="and e.pc_eventDate = (select max(pc_eventDate) from openemr_postcalendar_events where pc_aid = d.id)";

	if ($to_date) {
		$where .= " AND e.pc_eventDate <= ?";
		array_push($sqlBindArray, $to_date);
	}

	if($form_facility != "") {
		$where .= " AND f.id = ? ";
		array_push($sqlBindArray, $form_facility);
	}

	if($form_provider != "") {
		$where .= " AND d.id = ? ";
		array_push($sqlBindArray, $form_provider);
	}

	if($exclude_policy != ""){	$arrayExplode	=	explode(",", $exclude_policy);
								array_walk($arrayExplode, 'arrFormated');
								$exclude_policy = implode(",",$arrayExplode);
								$where .= " AND i.policy_number not in (".stripslashes($exclude_policy).")";
							}

	$where .= " AND (i.policy_number is not null and i.policy_number != '')";

	$query = sprintf("		SELECT DATE_FORMAT(e.pc_eventDate, '%%Y%%m%%d') as pc_eventDate,
								   e.pc_facility,
								   p.lname,
								   p.fname,
								   p.mname, 
								   DATE_FORMAT(p.dob, '%%Y%%m%%d') as dob,
								   p.ss,
								   p.sex,
								   p.pid,
								   p.pubpid,
								   i.policy_number,
								   i.provider as payer_id,
								   i.subscriber_relationship,
								   i.subscriber_lname,
								   i.subscriber_fname,
								   i.subscriber_mname,
								   DATE_FORMAT(i.subscriber_dob, '%%m/%%d/%%Y') as subscriber_dob,
								   i.subscriber_ss,
								   i.subscriber_sex,
								   DATE_FORMAT(i.date,'%%Y%%m%%d') as date,
								   d.lname as provider_lname,
								   d.fname as provider_fname,
								   d.npi as provider_npi,
								   d.upin as provider_pin,
								   f.federal_ein,
								   f.facility_npi,
								   f.name as facility_name,
								   c.name as payer_name
							FROM openemr_postcalendar_events AS e
							LEFT JOIN users AS d on (e.pc_aid is not null and e.pc_aid = d.id)
							LEFT JOIN facility AS f on (f.id = e.pc_facility)
							LEFT JOIN patient_data AS p ON p.pid = e.pc_pid
							LEFT JOIN insurance_data AS i ON (i.id =(
																	SELECT id
																	FROM insurance_data AS i
																	WHERE pid = p.pid AND type = 'primary'
																	ORDER BY date DESC
																	LIMIT 1
																	)
																)
							LEFT JOIN insurance_companies as c ON (c.id = i.provider)
							WHERE %s ",	$where );

	// Run the query 
	$res			= sqlStatement($query, $sqlBindArray);
	
	// Get the facilities information 
	$facilities		= getUserFacilities($_SESSION['authId']);

	// Get the Providers information 
	$providers		= getUsernames();

	//Get the x12 partners information 
	$clearinghouses	= getX12Partner();
		
		
	if (isset($_POST['form_savefile']) && !empty($_POST['form_savefile']) && $res) {
		header('Content-Type: text/plain');
		header(sprintf('Content-Disposition: attachment; filename="elig-270..%s.%s.txt"',
			$from_date,
			$to_date
		));
		print_elig($res,$X12info,$segTer,$compEleSep);
		exit; 
	}
?>

<html>

	<head>

		<?php html_header_show();?>

		<title><?php echo htmlspecialchars( xl('Eligibility 270 Inquiry Batch'), ENT_NOQUOTES); ?></title>

		<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>

		<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

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

		</style>

		<script type="text/javascript" src="../../library/textformat.js"></script>
		<script type="text/javascript" src="../../library/dialog.js"></script>
		<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
		<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
		<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
		<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

		<script type="text/javascript">

			var mypcc = "<?php echo htmlspecialchars( $GLOBALS['phone_country_code'], ENT_QUOTES); ?>";
			var stringDelete = "<?php echo htmlspecialchars( xl('Are you sure to remove this record'), ENT_QUOTES); ?>?";
			var stringBatch	 = "<?php echo htmlspecialchars( xl('Please select X12 partner, required to create the 270 batch'), ENT_QUOTES); ?>";

			// for form refresh 

			function refreshme() {
				document.forms[0].submit();
			}

			//  To delete the row from the reports section 
			function deletetherow(id){
				var suredelete = confirm(stringDelete);
				if(suredelete == true){
					document.getElementById('PR'+id).style.display="none";
					if(document.getElementById('removedrows').value == ""){
						document.getElementById('removedrows').value = "'" + id + "'"; 
					}else{
						document.getElementById('removedrows').value = document.getElementById('removedrows').value + ",'" + id + "'"; 
					
					}
				}
				
			}

			//  To validate the batch file generation - for the required field [clearing house/x12 partner] 
			function validate_batch()
			{
				if(document.getElementById('form_x12').value=='')
				{
					alert(stringBatch);
					return false;
				}
				else
				{
					document.getElementById('form_savefile').value = "true";
					document.theform.submit();
					
				}


			}

			// To Clear the hidden input field 

			function validate_policy()
			{
				document.getElementById('removedrows').value = "";
				document.getElementById('form_savefile').value = "";
				return true;
			}

			// To toggle the clearing house empty validation message 
			function toggleMessage(id,x12){
				
				var spanstyle = new String();

				spanstyle		= document.getElementById(id).style.visibility;
				selectoption	= document.getElementById(x12).value;
				
				if(selectoption != '')
				{
					document.getElementById(id).style.visibility = "hidden";
				}
				else
				{
					document.getElementById(id).style.visibility = "visible";
					document.getElementById(id).style.display = "inline";
				}
				return true;

			}

		</script>

	</head>
	<body class="body_top">

		<!-- Required for the popup date selectors -->
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

		<span class='title'><?php echo htmlspecialchars( xl('Report'), ENT_NOQUOTES); ?> - <?php echo htmlspecialchars( xl('Eligibility 270 Inquiry Batch'), ENT_NOQUOTES); ?></span>

		<div id="report_parameters_daterange">
			<?php echo htmlspecialchars( date("d F Y", strtotime($form_from_date)), ENT_NOQUOTES) .
				" &nbsp; " . htmlspecialchars( xl('to'), ENT_NOQUOTES) . 
				"&nbsp; ". htmlspecialchars( date("d F Y", strtotime($form_to_date)), ENT_NOQUOTES); ?>
		</div>

		<form method='post' name='theform' id='theform' action='edi_270.php' onsubmit="return top.restoreSession()">
			<input type="hidden" name="removedrows" id="removedrows" value="">
			<div id="report_parameters">
				<table>
					<tr>
						<td width='550px'>
							<div style='float:left'>
								<table class='text'>
									<tr>
										<td class='label'>
										   <?php xl('From','e'); ?>:
										</td>
										<td>
										   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo htmlspecialchars( $from_date, ENT_QUOTES) ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
										   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
											id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
											title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>'>
										</td>
										<td class='label'>
										   <?php echo htmlspecialchars( xl('To'), ENT_NOQUOTES); ?>:
										</td>
										<td>
										   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo htmlspecialchars( $to_date, ENT_QUOTES) ?>'
											onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
										   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
											id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
											title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>'>
										</td>
										<td>&nbsp;</td>
									</tr>
									
									<tr>
										<td class='label'>
											<?php echo htmlspecialchars( xl('Facility'), ENT_NOQUOTES); ?>:
										</td>
										<td>
											<?php dropdown_facility($form_facility,'form_facility',false);	?>
										</td>
										<td class='label'>
										   <?php echo htmlspecialchars( xl('Provider'), ENT_NOQUOTES); ?>:
										</td>
										<td>
											<select name='form_users' onchange='form.submit();'>
												<option value=''>-- <?php echo htmlspecialchars( xl('All'), ENT_NOQUOTES); ?> --</option>
												<?php foreach($providers as $user): ?>
													<option value='<?php echo htmlspecialchars( $user['id'], ENT_QUOTES); ?>'
														<?php echo $form_provider == $user['id'] ? " selected " : null; ?>
													><?php echo htmlspecialchars( $user['fname']." ".$user['lname'], ENT_NOQUOTES); ?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td>&nbsp;
										</td>
									</tr>
									
									<tr>
										<td class='label'>
											<?php echo htmlspecialchars( xl('X12 Partner'), ENT_NOQUOTES); ?>:
										</td>
										<td colspan='5'>
											<select name='form_x12' id='form_x12' onchange='return toggleMessage("emptyVald","form_x12");' >
														<option value=''>--<?php echo htmlspecialchars( xl('select'), ENT_NOQUOTES); ?>--</option>
														<?php 
															if(isset($clearinghouses) && !empty($clearinghouses))
															{
																foreach($clearinghouses as $clearinghouse): ?>
																	<option value='<?php echo htmlspecialchars( $clearinghouse['id']."|".$clearinghouse['id_number']."|".$clearinghouse['x12_sender_id']."|".$clearinghouse['x12_receiver_id']."|".$clearinghouse['x12_version']."|".$clearinghouse['processing_format'], ENT_QUOTES); ?>'
																		<?php echo $clearinghouse['id'] == $X12info[0] ? " selected " : null; ?>
																	><?php echo htmlspecialchars( $clearinghouse['name'], ENT_NOQUOTES); ?></option>
														<?php	endforeach; 
															}
															
														?>
												</select> 
												<span id='emptyVald' style='color:red;font-size:12px;'> * <?php echo htmlspecialchars( xl('Clearing house info required for EDI 270 batch creation.'), ENT_NOQUOTES); ?></span>
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
											<a href='#' class='css_button' onclick='validate_policy(); $("#theform").submit();'>
											<span>
												<?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?>
											</span>
											</a>
																						
											<a href='#' class='css_button' onclick='return validate_batch();'>
												<span>
													<?php echo htmlspecialchars( xl('Create batch'), ENT_NOQUOTES); ?>
													<input type='hidden' name='form_savefile' id='form_savefile' value=''></input>
												</span>
											</a>
											
										</div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div> 

			<div class='text'>
				<?php echo htmlspecialchars( xl('Please choose date range criteria above, and click Refresh to view results.'), ENT_NOQUOTES); ?>
			</div>

		</form>

		<?php
			if ($res){
				show_elig($res,$X12info,$segTer,$compEleSep);
			}
		?>
	</body>

	<script language='JavaScript'>
		Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
		Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
		<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>
	</script>

</html>
