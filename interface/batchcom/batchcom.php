<?php
//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
include_once("../globals.php");
include_once("$srcdir/registry.inc");
include_once("$srcdir/sql.inc");
include_once("../../library/acl.inc");
include_once("batchcom.inc.php");

// gacl control
$thisauth = acl_check('admin', 'batchcom');

if (!$thisauth) {
  echo "<html>\n<body>\n";
  echo "<p>".xl('You are not authorized for this.','','','</p>')."\n";
  echo "</body>\n</html>\n";
  exit();
 }

// menu arrays (done this way so it's easier to validate input on validate selections)
$choices=Array (xl('CSV File'),xl('Email'),xl('Phone call list'));
$gender=Array (xl('Any'),xl('Male'),xl('Female'));
$hipaa=Array (xl('NO'),xl('YES'));
$sort_by=Array (xl('Zip Code')=>'patient_data.postal_code',xl('Last Name')=>'patient_data.lname',xl('Appointment Date')=>'last_ap' );

// process form
if ($_POST['form_action']=='Process') {
	//validation uses the functions in batchcom.inc.php
	//validate dates
	if (!check_date_format($_POST['app_s'])) $form_err.=xl('Date format for "appointment start" is not valid','','<br>');
	if (!check_date_format($_POST['app_e'])) $form_err.=xl('Date format for "appointment end" is not valid','','<br>');
	if (!check_date_format($_POST['seen_since'])) $form_err.=xl('Date format for "seen since" is not valid','','<br>');
	if (!check_date_format($_POST['not_seen_since'])) $form_err.=xl('Date format for "not seen since" is not valid','','<br>');
	// validate numbers
	if (!check_age($_POST['age_from'])) $form_err.=xl('Age format for "age from" is not valid','','<br>');
	if (!check_age($_POST['age_upto'])) $form_err.=xl('Age format for "age up to" is not valid','','<br>');
	// validate selections
	if (!check_select($_POST['gender'],$gender)) $form_err.=xl('Error in "Gender" selection','','<br>');
	if (!check_select($_POST['process_type'],$choices)) $form_err.=xl('Error in "Process" selection','','<br>');
	if (!check_select($_POST['hipaa_choice'],$hipaa)) $form_err.=xl('Error in "HIPAA" selection','','<br>');
	if (!check_select($_POST['sort_by'],$sort_by)) $form_err.=xl('Error in "Sort By" selection','','<br>');
	// validates and or
	if (!check_yes_no ($_POST['and_or_gender'])) $form_err.=xl('Error in YES or NO option','','<br>');
	if (!check_yes_no ($_POST['and_or_app_within'])) $form_err.=xl('Error in YES or NO option','','<br>');
	if (!check_yes_no ($_POST['and_or_seen_since'])) $form_err.=xl('Error in YES or NO option','','<br>');
	if (!check_yes_no ($_POST['and_or_not_seen_since'])) $form_err.=xl('Error in YES or NO option','','<br>');

	//process sql
	if (!$form_err) {

		$sql=" 
				SELECT DISTINCT patient_data.* , MAX( cal_events.pc_endDate ) AS last_ap, MAX( forms.date) AS last_visit, (DATEDIFF(CURDATE(),patient_data.DOB)/365.25) AS pat_age 
				FROM patient_data, forms  
				LEFT JOIN  openemr_postcalendar_events AS cal_events ON patient_data.pid=cal_events.pc_pid
				LEFT JOIN  forms AS forms2 ON patient_data.pid=forms2.pid
			";

		//appointment dates
		if ($_POST['app_s']!=0 AND $_POST['app_s']!='') {
			$and=where_or_and ($and);		
			$sql_where_a=" $and cal_events.pc_eventDate > '".$_POST['app_s']."'";
		} 
		if ($_POST['app_e']!=0 AND $_POST['app_e']!='') {
			$and=where_or_and ($and);
			$sql_where_a.=" $and cal_events.pc_endDate < '".$_POST['app_e']."'";
		} 
		$sql.=$sql_where_a;
		
		// encounter dates
		if ($_POST['seen_since']!=0 AND $_POST['seen_since']!='') {
			$and=where_or_and ($and);
			$sql.=" $and forms2.date > '".$_POST['seen_since']."' " ;
		} 
		if ($_POST['seen_upto']!=0 AND $_POST['not_seen_since']!='') {
			$and=where_or_and ($and);
			$sql.=" $and forms2.date > '".$_POST['seen_since']."' " ;
		}

		// age
		if ($_POST['age_from']!=0 AND $_POST['age_from']!='') {
			$and=where_or_and ($and);
			$sql.=" $and DATEDIFF( CURDATE( ), patient_data.DOB )/ 365.25 >= '".$_POST['age_from']."' ";
		} 
		if ($_POST['age_upto']!=0 AND $_POST['age_upto']!='') {
			$and=where_or_and ($and);
			$sql.=" $and DATEDIFF( CURDATE( ), patient_data.DOB )/ 365.25 <= '".$_POST['age_upto']."' ";
		}

		// gender
		if ($_POST['gender']!='Any') {
			$and=where_or_and ($and);
			$sql.=" $and patient_data.sex='".$_POST['gender']."' ";
		}

		// hipaa overwrite
		if ($_POST['hipaa_choice']!='NO') {
			$and=where_or_and ($and);
			$sql.=" $and patient_data.hipaa_mail='YES' ";
		}
		
		switch ($_POST['process_type']):
			case $choices[1]: // Email
				$and=where_or_and ($and);
				$sql.=" $and patient_data.email IS NOT NULL ";
			break;
		endswitch;

		// add to complete query sintax
		$sql.=' GROUP BY patient_data.pid';

		// sort by
		$sql.=' ORDER BY '.$_POST['sort_by'];

		// echo $sql;
		// send query for results.
		$res = sqlStatement($sql);

		// if no results.
		if (mysql_num_rows($res)==0){
			
			echo (xl('No results, please tray again.','','<br>'));
		
		//if results
		} else { 
			switch ($_POST['process_type']):
				case $choices[0]: // CSV File
					require_once ('batchCSV.php');
				break;
				case $choices[1]: // Email
					require_once ('batchEmail.php');
				break;
				case $choices[2]: // Phone list
					require_once ('batchPhoneList.php');
				break;
			endswitch;
		}
		// end results

		exit ();
	} 
}

//START OUT OUR PAGE....
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<link rel=stylesheet href="batchcom.css" type="text/css">
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>


</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<span class="title"><?xl('Batch Communication Tool','e')?></span>
<br><br>

<!-- for the popup date selector -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<FORM name="select_form" METHOD=POST ACTION="">

<div class="text">
	<div class="main_box">
		<?php
		if ($form_err) {
			echo ("The following errors occurred<br>$form_err<br><br>");
		}
		
		xl('Process:','e')?><SELECT NAME="process_type">
				<?
				foreach ($choices as $value) {
					echo ("<option>$value</option>");
				}
				?>
				</SELECT>

		<br><?xl('Overwrite HIPAA choice: ','e')?><SELECT NAME="hipaa_choice">
									<?
									foreach ($hipaa as $value) {
										echo ("<option>$value</option>");
									}
									?>
									</SELECT>
		<br>
		<?xl('Age From:','e')?><INPUT TYPE="text" size="2" NAME="age_from"> <?xl(' Up to:','e')?><INPUT TYPE="text" size="2" NAME="age_upto"> 
		<?xl('And:','e')?><INPUT TYPE="radio" NAME="and_or_gender" value="AND" checked><?xl(', Or:','e')?><INPUT TYPE="radio" NAME="and_or_gender" value="OR">
		<?xl('Gender: ','e')?><SELECT NAME="gender">
				<?
				foreach ($gender as $value) {
					echo ("<option>$value</option>");
				}
				?>
				</SELECT>
		<!-- later gator
		<br>Insurance: <SELECT multiple NAME="insurance" Rows="10" cols="20">

						</SELECT>
		-->
		<br><?xl('And:','e')?><INPUT TYPE="radio" NAME="and_or_app_within" value="AND" checked><?xl(', Or:','e')?><INPUT TYPE="radio" NAME="and_or_app_within" value="OR"><?xl(' Appointment within:','e')?><INPUT TYPE='text' size='12' NAME='app_s'> <a href="javascript:show_calendar('select_form.app_s')"
    title="<?xl('Click here to choose a date','e')?>"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0' ></a>
		
		<?xl('And: ','e')?><INPUT TYPE='text' size='12' NAME='app_e'> <a href="javascript:show_calendar('select_form.app_e')"
    title="<?xl('Click here to choose a date','e')?>"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0' ></a>

		<br><?xl('And:','e')?><INPUT TYPE="radio" NAME="and_or_seen_since" value="AND" checked><?xl(', Or:','e')?><INPUT TYPE="radio" NAME="and_or_seen_since" value="OR"><?xl(' Seen since: ','e')?><INPUT TYPE='text' size='12' NAME='seen_since'> <a href="javascript:show_calendar('select_form.seen_since')"
    title="<?xl('Click here to choose a date','e')?>"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>

		<br><?xl('And:','e')?><INPUT TYPE="radio" NAME="and_or_not_seen_since" value="AND" checked><?xl(', Or:','e')?><INPUT TYPE="radio" NAME="and_or_not_seen_since" value="OR"><?xl(' Not seen since: ','e')?><INPUT TYPE='text' size='12' NAME='not_seen_since'> <a href="javascript:show_calendar('select_form.not_seen_since')"
    title="<?xl('Click here to choose a date','e')?>"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
		<br><?xl('Sort by: ','e')?><SELECT NAME="sort_by">
				<?
				foreach ($sort_by as $key => $value) {
					echo ("<option value=".$value.">$key</option>");
				}
				?>
				</SELECT>
	<br><?xl('(Fill here only if sending email notification to patients)','e')?>
	<br><?xl('Email Sender: ','e')?><INPUT TYPE="text" NAME="email_sender" value="your@example.com">
	<br><?xl('Email Subject: ','e')?><INPUT TYPE="text" NAME="email_subject" value="From your clinic">
	<br><?xl('Email Text, Usable Tag: ***NAME*** , i.e. Dear ***NAME***','e')?>
	<br><TEXTAREA NAME="email_body" ROWS="8" COLS="35"></TEXTAREA>

	<br><INPUT TYPE="submit" name="form_action" value="Process">

	</div>
</div>
</FORM>
