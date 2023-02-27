<?php
// Copyright (C) 2005-2016 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows upcoming appointments with filtering and
// sorting by patient, practitioner, appointment type, and date.
// 2012-01-01 - Added display of home and cell phone and fixed header
// 2015-06-19 - brought up to security standards terry@lillysystems.com

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/appointments.inc.php";
require_once "$srcdir/clinical_rules.php";
require_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
require_once($GLOBALS['srcdir']."/wmt-v2/wmtstandard.inc");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\Smslib;

$v_js_includes = $GLOBALS['v_js_includes'];
# Clear the pidList session whenever load this page.
# This session will hold array of patients that are listed in this 
# report, which is then used by the 'Superbills' and 'Address Labels'
# features on this report.
unset($_SESSION['pidList']);
ini_set('memory_limit', -1);
set_time_limit(0);

if(!isset($_POST['form_csvexport'])) $_POST['form_csvexport'] = '';
/*
if(!isset($_POST['form_show_available'])) $_POST['form_show_available'] = false;
if(!isset($_POST['with_out_provider'])) $_POST['with_out_provider'] = false;
if(!isset($_POST['with_out_facility'])) $_POST['with_out_facility'] = false;
*/
if(!isset($_POST['unique_patients'])) $_POST['unique_patients'] = false;
if(!isset($_POST['only_with_encounters'])) $_POST['only_with_encounters'] = false;
if(!isset($_REQUEST['patient'])) $_REQUEST['patient'] = '';
if(!isset($_POST['form_provider'])) $_POST['form_provider'] = '';
if(!isset($_POST['form_facility'])) $_POST['form_facility'] = '';
if(!isset($_POST['form_template'])) $_POST['form_template'] = '';
if(!isset($_POST['form_blast'])) $_POST['form_blast'] = '';
if(!isset($_POST['form_from_hr'])) $_POST['form_from_hr'] = '';
if(!isset($_POST['form_from_mn'])) $_POST['form_from_mn'] = '';
if(!isset($_POST['form_to_hr'])) $_POST['form_to_hr'] = '';
if(!isset($_POST['form_to_mn'])) $_POST['form_to_mn'] = '';
$order_test = false;
if(!isset($_REQUEST['form_orderby'])) {
	$_REQUEST['form_orderby'] = 'date';
} else {
	$order_test = true;
}

$alertmsg = ''; // not used yet but maybe later
$patient = $_REQUEST['patient'];

if ($patient && ! $_POST['form_from_date']) {
	// If a specific patient, default to 2 years ago.
	$tmp = date('Y') - 2;
	$from_date = date("$tmp-m-d");
} else {
	$from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
	$to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}

$from_hr = $_POST['form_from_hr'];
$from_mn = $_POST['form_from_mn'];
$to_hr = $_POST['form_to_hr'];
$to_mn = $_POST['form_to_mn'];

/*
$show_available_times = false;
if ( $_POST['form_show_available'] ) {
	$show_available_times = true;
}

$chk_with_out_provider = false;
if ( $_POST['with_out_provider'] ) {
	$chk_with_out_provider = true;
}

$chk_with_out_facility = false;
if ( $_POST['with_out_facility'] ) {
	$chk_with_out_facility = true;
}

$chk_with_out_verified = false;
if ( $_POST['with_out_verified'] ) {
	$chk_with_out_verified = true;
}
*/

//$to_date   = fixDate($_POST['form_to_date'], '');
$provider  = $_POST['form_provider'];
$facility  = $_POST['form_facility'];  //(CHEMED) facility filter
$form_orderby = $_REQUEST['form_orderby'];
$sent_notice = '';
//$form_orderby = getComparisonOrder( $_REQUEST['form_orderby'] ) ?  $_REQUEST['form_orderby'] : 'date';


// LOCALLY DEFINE THESE FUNCTIONS SO THAT WE CAN GROUP AND SHRINK QUERY RESULT SIZE

function fetchBlastEvents( $from_date, $to_date, $where_param = null, $orderby_param = null, $tracker_board = false, $nextX = 0, $bind_param = null, $query_param = null, $unique = FALSE )
{
    
    $sqlBindArray = array();
    
    if($query_param) {
        
        $query = $query_param;
        
        if($bind_param) $sqlBindArray = $bind_param;
        
    } else {
        //////
        if($nextX) {
            
            $where =
            "((e.pc_endDate >= ? AND e.pc_recurrtype > '0') OR " .
            "(e.pc_eventDate >= ?))";
            
            array_push($sqlBindArray, $from_date, $from_date);
            
        } else {
            //////
            $where =
            "((e.pc_endDate >= ? AND e.pc_eventDate <= ? AND e.pc_recurrtype > '0') OR " .
            "(e.pc_eventDate >= ? AND e.pc_eventDate <= ?))";
            
            array_push($sqlBindArray, $from_date, $to_date, $from_date, $to_date);
            
        }
        
        if ( $where_param ) $where .= $where_param;
        
        $order_by = "e.pc_eventDate, e.pc_startTime";
        if ( $orderby_param ) {
            $order_by = $orderby_param;
        }
        
        // Tracker Board specific stuff
        $tracker_fields = '';
        $tracker_joins = '';
        $encounter_fields = '';
        $encounter_joins = '';
        if ($tracker_board) {
            $tracker_fields = "e.pc_room, e.pc_pid, t.id, t.date, t.apptdate, t.appttime, t.eid, t.pid, t.original_user, t.encounter, t.lastseq, t.random_drug_test, t.drug_screen_completed, " .
                "q.pt_tracker_id, q.start_datetime, q.room, q.status, q.seq, q.user, " .
                "s.toggle_setting_1, s.toggle_setting_2, s.option_id, ";
            $tracker_joins = "LEFT OUTER JOIN patient_tracker AS t ON t.pid = e.pc_pid AND t.apptdate = e.pc_eventDate AND t.appttime = e.pc_starttime AND t.eid = e.pc_eid " .
                "LEFT OUTER JOIN patient_tracker_element AS q ON q.pt_tracker_id = t.id AND q.seq = t.lastseq " .
                "LEFT OUTER JOIN list_options AS s ON s.list_id = 'apptstat' AND s.option_id = q.status AND s.activity = 1 " ;
            // WMT added to restrict who is listed on board
            $where .= ($where)? ' and ' : '';
            $where .= ' u.flowboard = 1 ';
        }
        if($_POST['only_with_encounters']) {
            $encounter_fields = "fe.encounter, ";
            $encounter_joins  = "INNER JOIN form_encounter AS fe ON (fe.pid = e.pc_pid AND DATE(fe.date) = e.pc_eventDate AND fe.provider_id = e.pc_aid) ";
        }
        
        $query = "SELECT " .
            "e.pc_eventDate, e.pc_endDate, e.pc_startTime, e.pc_endTime, e.pc_duration, e.pc_recurrtype, e.pc_recurrspec, e.pc_recurrfreq, e.pc_catid, e.pc_eid, " .
            "e.pc_title, e.pc_hometext, e.pc_apptstatus, " .
						"lo.title AS stat_desc, " . 
            "p.fname, p.mname, p.lname, p.pid, p.pubpid, p.phone_home, p.phone_cell, " .
            "u.fname AS ufname, u.mname AS umname, u.lname AS ulname, u.id AS uprovider_id, " .
            "f.name, " .
            "$tracker_fields" .
            "$encounter_fields" .
            "c.pc_catname, c.pc_catid " .
            "FROM openemr_postcalendar_events AS e " .
            "$tracker_joins" .
            "$encounter_joins" .
            "LEFT OUTER JOIN facility AS f ON e.pc_facility = f.id " .
            "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
            "LEFT OUTER JOIN users AS u ON u.id = e.pc_aid " .
            "LEFT OUTER JOIN list_options AS lo ON (lo.list_id = 'apptstat' AND lo.option_id = e.pc_apptstatus) " .
            "LEFT JOIN openemr_postcalendar_categories AS c ON c.pc_catid = e.pc_catid " .
            "WHERE $where ";
        if($unique) $query .= ' GROUP BY e.pc_pid';
        $query .= " ORDER BY $order_by";
        
        if($bind_param) $sqlBindArray = array_merge($sqlBindArray, $bind_param);
        
    }
    $res = sqlStatement($query, $sqlBindArray);
    
    $appointments = array();
    while ($event = sqlFetchArray($res)) {
        $appointments[] = $event;
    }
    return $appointments;
}

function fetchBlastAppointments( $from_date, $to_date, $patient_id = null, $provider_id = null, $facility_id = null, $pc_appstatus = null, $with_out_provider = null, $with_out_facility = null, $with_out_verified = null, $pc_catid = null, $tracker_board = false, $nextX = 0, $unique = FALSE )
{
    $sqlBindArray = array();
    
    $where = "";
    
    if ( $provider_id ) {
        $where .= " AND e.pc_aid = ?";
        array_push($sqlBindArray, $provider_id);
    }
    
    if ( $patient_id ) {
        $where .= " AND e.pc_pid = ?";
        array_push($sqlBindArray, $patient_id);
    } else {
        $where .= " AND e.pc_pid != ''";
    }
    
    if ( $facility_id ) {
        $where .= " AND e.pc_facility = ?";
        array_push($sqlBindArray, $facility_id);
    }
    
    //Appointment Status Checking
    if($pc_appstatus != ''){
        $where .= " AND e.pc_apptstatus = ?";
        array_push($sqlBindArray, $pc_appstatus);
    }
    
    if($pc_catid !=null) {
        $where .= " AND e.pc_catid = ?";
        array_push($sqlBindArray, $pc_catid);
    }
    
    //Without Provider checking
    if($with_out_provider != ''){
        $where .= " AND e.pc_aid = ''";
    }
    
    //Without Facility checking
    if($with_out_facility != ''){
        $where .= " AND e.pc_facility = 0";
    }
    
    //Without Facility checking
    if($with_out_verified != ''){
        $where .= " AND (e.pc_verified = '' OR e.pc_verified IS NULL OR e.pc_verified LIKE 'Not Checked')";
    }
    
    $appointments = fetchBlastEvents( $from_date, $to_date, $where, '', $tracker_board, $nextX, $sqlBindArray, NULL, $unique );
    return $appointments;
}


if($_POST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=appointments.csv");
  header("Content-Description: File Transfer");
	echo '"Provider",';
	echo '"Date",';
	echo '"Time",';
	echo '"Patient",';
	echo '"ID",';
	echo '"Birthdate",';
	echo '"Insurance",';
	echo '"Home",';
	echo '"Cell",';
	echo '"Allow SMS",';
	echo '"Street",';
	echo '"City",';
	echo '"State",';
	echo '"Zip",';
	echo '"Type",';
	echo '"Status",';
	echo '"Facility",';
	echo '"Comment"';
	echo "\n";
// End of CSV Export
} else {
    if($_POST['form_blast'] && $_POST['form_template']) {
        // INCLUDE THE TWILIO CLASSES AND QUEUE THE MESSAGES
        $cnt = 0;
        foreach($_POST as $k => $pid) {
            if(substr($k,0,4) == 'exp_') {
                $eid = substr($k,4);
                //$sms = new wmt\Nexmo;
                $sms = Smslib::getSmsObj();
                $content = $sms->createSMSText($eid, $pid, $_POST['form_template'], TRUE);
                if($content) {
                     $sms->queueSMS($pid, $content);
                     $cnt++;
                }
            }
        }
        $sent_notice = $cnt . ' were queued to be sent.';

    }
	// Reminders related stuff
	$incl_reminders = isset($_POST['incl_reminders']) ? 1 : 0;
	function fetch_rule_txt ($list_id, $option_id) {
	    $rs = sqlQuery('SELECT title, seq from list_options WHERE list_id = ? AND option_id = ? AND activity = 1',
	            array($list_id, $option_id));
	    $rs['title'] = xl_list_label($rs['title']);
	    return $rs;
	}
	
	function fetch_reminders($pid, $appt_date) {
	    $rems = test_rules_clinic('','passive_alert',$appt_date,'reminders-due',$pid);
	    $seq_due = array();
	    $seq_cat = array();
	    $seq_act = array();
	    foreach ($rems as $ix => $rem) {
	        $rem_out = array();
	        $rule_txt = fetch_rule_txt ('rule_reminder_due_opt', $rem['due_status']);
	        $seq_due[$ix] = $rule_txt['seq'];
	        $rem_out['due_txt'] = $rule_txt['title'];
	        $rule_txt = fetch_rule_txt ('rule_action_category', $rem['category']);
	        $seq_cat[$ix] = $rule_txt['seq'];
	        $rem_out['cat_txt'] = $rule_txt['title'];
	        $rule_txt = fetch_rule_txt ('rule_action', $rem['item']);
	        $seq_act[$ix] = $rule_txt['seq'];
	        $rem_out['act_txt'] = $rule_txt['title'];
	        $rems_out[$ix] = $rem_out;
	    }
	    array_multisort($seq_due, SORT_DESC, $seq_cat, SORT_ASC, $seq_act, SORT_ASC, $rems_out);
	    $rems = array();
	    foreach ($rems_out as $ix => $rem) {
	        $rems[$rem['due_txt']] .= (isset($rems[$rem['due_txt']]) ? ', ':'').
            $rem['act_txt'].' '.$rem['cat_txt'];
	    }
	    return $rems;
	}
?>

<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<title><?php echo xlt('SMS Blast Report'); ?></title>

<?php Header::setupHeader(['main-theme', 'textformat', 'dialog', 'jquery', 'jquery-ui']); ?>
<script type="text/javascript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

<?php include(INC_DIR_JS . 'init_ajax.inc.js'); ?>

 $(document).ready(function() {
 });

 function dosort(orderby) {
	$('#form_csvexport').attr('value','');
    var f = document.forms[0];
    f.form_orderby.value = orderby;
    f.submit();
    return false;
 }

 function oldEvt(eventid) {
    dlgopen('../../main/calendar/add_edit_event.php?eid=' + eventid, 'blank', 775, 500);
 }

 function refreshme() {
    // location.reload();
    document.forms[0].submit();
 }

 function logWmtEvent() {
	 // BUILD A DESCRIPTION OF WHAT'S HAPPENING
	var cnt = $('.form-choices:checked').length;
    var data = $('#report_parameters :input').serialize();
    data += '&form_from_date=' + document.getElementById('form_from_date').value;
    data += '&form_to_date=' + document.getElementById('form_to_date').value;
	var desc = '(' + cnt + ') patients selected and with data: [' +  data + ']';
	var output = 'error';
	$.ajax({
		type: "POST",
		url: "<?php echo AJAX_DIR_JS; ?>wmtLogEvent.ajax.php",
		datatype: "html",
		data: {
			event: 'SMS Blast Button Clicked',
			desc: desc,
			type: 'Submit'
		},
		success: function(result) {
			if(result['error']) {
				output = '';
			} else {
				output = result;
			}
		},
		async: true
	});
    return output;
 }
 
 function validateBlast() {
	var blast = document.getElementById('form_blast');
	var refresh = document.getElementById('form_refresh');

	var sel = document.getElementById('form_template');
	if(!sel.selectedIndex) {
		alert('Please Select a Template for the SMS Messages');
		return false;
	}

	if(blast.value) {
		alert('Don\'t Keep Clicking the Button!\nMessages Are Being Queued!');
		logWmtEvent();
		return false;
	}

	blast.value = 1;
	logWmtEvent();

	var cnt = $('.form-choices:checked').length;
	if(!cnt) {
		alert('No Appointments / Patients are Selected');
		blast.value = '';
		refresh.value = '';
		return false;
	}
	if(confirm('Generate [' + cnt + '] SMS Messages?')) {
    	document.getElementById('blast_span').innerHTML = 'Sending';
		document.forms[0].submit();
	} else return false;    
 }
 
 function CheckAll() {
	var i = 0;
	var l = document.forms[0].elements.length;
	for (i=0; i<l; i++) {
		if(document.forms[0].elements[i].name.indexOf('exp_') == 0) {
			if(document.forms[0].elements[i].type.indexOf('check') != -1) {
				if(!document.forms[0].elements[i].disabled)
					document.forms[0].elements[i].checked = true;
			}
		}
	}
 }

 function UncheckAll() {
	var i = 0;
	var l = document.forms[0].elements.length;
	for (i=0; i<l; i++) {
		if(document.forms[0].elements[i].name.indexOf('exp_') == 0) {
			if(document.forms[0].elements[i].type.indexOf('check') != -1) {
				document.forms[0].elements[i].checked = false;
			}
		}
	}
 }

 function updateLabel(sel) {
	 if(sel.selectedIndex > 0) {
		 document.getElementById('blast_span').innerHTML = 'Send SMS(s)';
		 $("#blast_button").unbind('click');
	     $("#blast_button").click(function(e) {
	    	 $("#form_refresh").attr("value","true");
	    	 $("#form_csvexport").attr("value","");
	    	 validateBlast();
	     });
	 } else {
		 document.getElementById('blast_span').innerHTML = 'Select a Template';
	 }
 }

 <?php if($sent_notice) { ?>
 	alert('<?php echo $sent_notice; ?>');
 <?php } ?>

//jQuery stuff to make the page a little easier to use
 $(document).ready(function(){

	var win = top.printLogSetup ? top : opener.top;
    win.printLogSetup(document.getElementById('printbutton'));
		 
    $("#blast_button").click(function(e) {
        <?php if(!$_POST['form_blast']) { ?>
        $("#form_refresh").attr("value","true");
    	$("#form_csvexport").attr("value","");
    	validateBlast();
	    <?php } else { ?>
	    alert('Those messages were just sent!');
	    <?php } ?>
     });

 });

function setButtonLabel(btn, txt) {
	 var target = document.getElementById(btn);
	 if(!target || target == null) return false;
	 target.innerHTML = txt;
 }

</script>

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
</head>

<body class="body_top" <?php echo ($_POST['form_blast']) ? 'onload="setButtonLabel(\'blast_span\',\'Queued\');"' : ''; ?> >

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('SMS Blast'); ?></span>

<div id="report_parameters_daterange"><?php echo date("d F Y", strtotime($from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($to_date)); #sets date range for calendars ?>
</div>

<form method='post' name='theform' id='theform' action='sms_blast_report.php' onsubmit='return top.restoreSession()'>

<div id="report_parameters">

<table>
	<tr>
		<td width='70%'>
		<div style='float: left'>

		<table class='text'>
			<tr>
				<td><?php echo xlt('Facility'); ?>:</td>
				<td><?php dropdown_facility($facility , 'form_facility'); ?>
				</td>
				<td><?php echo xlt('Provider'); ?>:</td>
				<td><?php

				$query = "SELECT id, lname, fname FROM users WHERE ".
				  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

				$ures = sqlStatement($query);

				echo "   <select name='form_provider' class='form-control'>\n";
				echo "    <option value=''>-- " . xlt('All') . " --\n";

				while ($urow = sqlFetchArray($ures)) {
					$provid = $urow['id'];
					echo "    <option value='" . attr($provid) . "'";
					if ($provid == $_POST['form_provider']) echo " selected";
					echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
				}

				echo "   </select>\n";
                ?>
				</td>
			</tr>
			<tr>
				<td><?php echo xlt('From'); ?>:</td>
				<td><input type='date' name='form_from_date' id="form_from_date"
					value='<?php echo attr($from_date) ?>'
					title='<?php echo xlt('Click here to choose a date'); ?>'>
				  &nbsp;&nbsp;&nbsp;<span><?php echo xlt('At'); ?>:</span>
				  <select name='form_from_hr' id='form_from_hr' style="width: 40px; height: 36px;">
				    <?php NumSel($from_hr, 0, 24, 1, 0, false, '', '0', 2); ?>
				  </select>&nbsp;:&nbsp;
					<select name='form_from_mn' id='form_from_mn' style="width: 40px; height: 36px;">
				    <?php NumSel($from_mn, 0, 55, 5, 0, false, '', '0', 2); ?>
				  </select></td>
				<td><?php echo xlt('To'); ?>:</td>
				<td><input type='date' name='form_to_date' id="form_to_date"
					value='<?php echo attr($to_date) ?>'
					title='<?php echo xlt('Click here to choose a date'); ?>'>&nbsp;
					&nbsp;&nbsp;<span><?php echo xlt('At'); ?>:</span>
					<select name='form_to_hr' id='form_to_hr' style="width: 40px; height: 36px;">
						<?php NumSel($to_hr, 0, 24, 1, 24, false, '', '0', 2); ?></select>&nbsp;:&nbsp;
					<select name='form_to_mn' id='form_to_mn' style="width: 40px; height: 36px;">
						<?php NumSel($to_mn, 0, 55, 5, 0, false, '', '0', 2); ?>
					</select></td>
			</tr>
			
			<tr>
				<td><?php echo xlt('Status'); # status code drop down creation ?>:</td>
				<td><?php generate_form_field(array('data_type'=>1,'field_id'=>'apptstatus','list_id'=>'apptstat','empty_title'=>'All'),$_POST['form_apptstatus']);?></td>
				<td><?php echo xlt('Category') #category drop down creation ?>:</td>
				<td>
                                    <select id="form_apptcat" name="form_apptcat" class="form-control">
                                        <?php
                                            $categories=fetchAppointmentCategories();
                                            echo "<option value='ALL'>".xlt("All")."</option>";
                                            while($cat=sqlFetchArray($categories))
                                            {
                                                echo "<option value='".attr($cat['id'])."'";
                                                if($cat['id']==$_POST['form_apptcat'])
                                                {
                                                    echo " selected='true' ";
                                                }
                                                echo    ">".text(xl_appt_category($cat['category']))."</option>";
                                            }
                                        ?>
                                    </select>
                                </td>
			</tr>
			<tr>
			    <td></td>
			    <td colspan=2><label><input type="checkbox" name="unique_patients" id="unique_patients" <?php if($_POST['unique_patients']) echo "checked";?>>&nbsp;<?php echo xlt('Unique Patients'); ?></label></td>
			    <td colspan=2><label><input type="checkbox" name="only_with_encounters" id="only_with_encounters" <?php if($_POST['only_with_encounters']) echo "checked";?>>&nbsp;<?php echo xlt('Only Include w/Encounters'); ?></label></td>
			    
			</tr>
			<!--  tr>
			    <td></td>
			    <td colspan=3>
			    
			    <table style='width:80%'>
				<tr>
	                <?php # these two selects will show entries that do not have a facility or a provider ?>
					<td><label><input type="checkbox" name="with_out_provider" id="with_out_provider" <?php if($chk_with_out_provider) //echo "checked";?>>&nbsp;<?php //echo xlt('Without Provider'); ?></label></td>
					<td><label><input type="checkbox" name="with_out_facility" id="with_out_facility" <?php if($chk_with_out_facility) //echo "checked";?>>&nbsp;<?php //echo xlt('Without Facility'); ?></label></td>
					<td><label><input type="checkbox" name="with_out_verified" id="with_out_verified" <?php if($chk_with_out_verified) //echo "checked";?>>&nbsp;<?php //echo xlt('Without Verification'); ?></label></td>
				</tr>
				</table>
			
			</td></tr -->
		</table>

		</div>

		</td>
		<td align='left' valign='middle' height='100%'>
				<div style='margin-left: 15px'>
                    <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value",""); $("#form_blast").attr("value",""); $("#theform").submit();'>
								<span> <?php echo xlt('Submit'); ?> </span> </a> 
<?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
        			<a href='#' class='css_button' id='printbutton'> 
                                    <span> <?php echo xlt('Print'); ?> </span> </a> 
                     <a href='#' id='blast_button' class='css_button'> 
                                    <span id='blast_span'> <?php echo ($_POST['form_template'] != '') ? xlt('Send SMS(s)') : xlt('Select A Template'); ?> </span> </a>
 					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value","true"); $("#form_blast").attr("value",""); $("#theform").submit();'>
						<span><?php echo xl('CSV Export'); ?></span></a>
<?php } ?><br><br>
		<span style="display: <?php echo ($_POST['form_refresh'] || $_POST['form_orderby']) ? 'inline-block' : 'none'; ?>" >
		<?php echo xlt('Message Templates')?>:&nbsp;&nbsp;
		<?php
		$tres = sqlStatement('SELECT * FROM `templates` WHERE 1 GROUP BY `name`');
		echo "   <select name='form_template' id='form_template' onchange='updateLabel(this);' >\n";
		echo "    <option value=''>-- " . xlt('Please Make A Selection') . " --\n";
		
		while ($trow = sqlFetchArray($tres)) {
		    $tname = $trow['name'];
		    echo "    <option value='" . attr($tname) . "'";
		    if ($tname == $_POST['form_template']) echo " selected";
		    echo ">" . text($trow['title']) . '</option>';
		}
		echo "   </select>\n";
		?></span></div>
		</td>
	</tr>
</table>

</div>
<!-- end of search parameters --> 
<?php
if ($_POST['form_refresh'] || $_POST['form_orderby']) {
	$showDate = ($from_date != $to_date) || (!$to_date);
	?>
<div id="report_results">
<table style="width:100%;" class="text table table-sm">

	<thead class="thead-light">
		<th><?php echo xlt('Include'); ?></th>
		
		<th><a href="nojs.php" onclick="return dosort('doctor')"
	<?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php echo xlt('Provider'); ?>
		</a></th>

		<th <?php echo $showDate ? '' : 'style="display:none;"' ?>><a href="nojs.php" onclick="return dosort('date')"
	<?php if ($form_orderby == "date") echo " style=\"color:#00cc00\"" ?>><?php echo xlt('Date'); ?></a>
		</th>

		<th><a href="nojs.php" onclick="return dosort('time')"
	<?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php echo xlt('Time'); ?></a>
		</th>

		<th><a href="nojs.php" onclick="return dosort('patient')"
	<?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php echo xlt('Patient'); ?></a>
		</th>

		<th><a href="nojs.php" onclick="return dosort('pubpid')"
	<?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php echo xlt('ID'); ?></a>
		</th>
		<th><?php echo xlt('Birthdate'); ?></th>
      	<th><?php echo xlt('Home'); //Sorting by phone# not really useful ?></th>

        <th><?php echo xlt('Cell'); //Sorting by phone# not really useful ?></th>
        <th><?php echo xlt('Allow SMS'); ?></th>
                
		<!--  th><?php echo xlt('Address'); ?></th -->
		<th><a href="nojs.php" onclick="return dosort('type')"
	<?php if ($form_orderby == "type") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Type'); ?></a>
		</th>
		<th><a href="nojs.php" onclick="return dosort('status')"
			<?php if ($form_orderby == "status") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Status'); ?></a>
		</th>
	</thead>
	<tbody>
		<!-- added for better print-ability -->
	<?php
	} // End of display title and order by criteria
}  // End NOT CSV Export

if ($_POST['form_refresh'] || $order_test || $_POST['form_csvexport']) {
	$lastdocname = "";
	//Appointment Status Checking
	$form_apptstatus = $_POST['form_apptstatus'];
	$form_apptcat=null;
	if(isset($_POST['form_apptcat'])) {
		if($form_apptcat!="ALL") {
			$form_apptcat=intval($_POST['form_apptcat']);
		}
	}
            
	//Without provider and facility data checking
	$with_out_provider = null;
	$with_out_facility = null;
	$with_out_verified = null;
    
	/*
	if( isset($_POST['with_out_provider']) ){
		$with_out_provider = $_POST['with_out_provider'];
	}
	
	if( isset($_POST['with_out_facility']) ){
		$with_out_facility = $_POST['with_out_facility'];
	}
	
	if( isset($_POST['with_out_verified']) ){
		$with_out_verified = $_POST['with_out_verified'];
	}
	*/
	$appointments = fetchBlastAppointments( $from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility, $with_out_verified, $form_apptcat, FALSE, 0, $_POST['unique_patients'] );
	
	$appointments = sortAppointments( $appointments, $form_orderby );
    $pid_list = array();  // Initialize list of PIDs for Superbill option
    $appt_list = array();  // INITIALIZE A LIST OF APPOINTMENT ID'S POSSIBLY FOR SMS 
    $totalAppointments = count($appointments);
    $appt_start_time = $from_hr . ':' . $from_mn . ':00';
    $appt_to_time    = $to_hr . ':' . $to_mn . ':00';
	
	$bgcolor = '';
	$cnt = 0;
	foreach ( $appointments as $appointment ) {
        if(($appointment['pc_eventDate'] == $from_date) && ($appointment['pc_startTime'] < $appt_start_time)) continue;
        if(($appointment['pc_eventDate'] == $to_date) && ($appointment['pc_startTime'] > $appt_to_time)) continue;
        if($_POST['unique_patients']) {
            if(in_array($appointment['pid'], $pid_list)) continue;
        }
        $cnt++;
 		array_push($pid_list,$appointment['pid']);
		array_push($appt_list,$appointment['pc_eid']);
		$patient_id = $appointment['pid'];
		$docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];
		
		$patient = getPatientData($patient_id,'DOB,street,city,state,postal_code,hipaa_allowsms');
		$address = $patient['street']."<br/>";
		$address .= $patient['city'].", ".$patient['state']." ".$patient['postal_code']."<br/>"; 
		
        $errmsg  = "";
		$pc_apptstatus = $appointment['pc_apptstatus'];
		
		// THIS APPOINTMENT IS ALREADY CANCELLED
		if($pc_apptstatus == 'x') continue;
		
		if(!isset($_POST['exp_' . $appointment['pc_eid']])) $_POST['exp_' . $appointment['pc_eid']] = '';
		if ($_POST['form_csvexport']) {
			// GET INSURANCE
            if($GLOBALS['wmt::link_appt_ins']) {
			    $ins = sqlQuery('SELECT ic.`name` FROM `openemr_postcalendar_events` ope LEFT JOIN `insurance_companies` ic ON ope.`pc_insurance` = ic.`id` WHERE ope.`pc_eid` = ?',array($appointment['pc_eid']));
            } else {
			    $ins = sqlQuery('SELECT ic.`name` FROM `insurance_data` ope LEFT JOIN `insurance_companies` ic ON ope.`provider` = ic.`id` WHERE ope.`pid` = ? AND ope.`type` = "primary" AND ope.`provider` IS NOT NULL AND ope.`provider` != "" AND ope.`date` != "0000-00-00" AND ope.`date` IS NOT NULL ORDER BY `date` DESC',array($appointment['pid']));
            }

			echo '"' . $docname .'",';
			echo '"' . oeFormatShortDate($appointment['pc_eventDate']) . '",';
			echo '"' . oeFormatTime($appointment['pc_startTime']) . '",';
			echo '"' . $appointment['fname'] . " " . $appointment['lname'] .'",';
			echo '"' . $appointment['pubpid'] . '",';
			echo '"' . $patient['DOB'] . '",';
			echo '"' . $ins['name'] . '",';
		    echo '"' . $appointment['phone_home'] . '",';
		    echo '"' . $appointment['phone_cell'] . '",';
		    echo '"' . $patient['hipaa_allowsms'] . '","';
		    echo '"' . $patient['street'] . '",';
		    echo '"' . $patient['city'] . '",';
		    echo '"' . $patient['state'] . '",';
		    echo '"' . $patient['postal_code'] . '",';
		    echo '"' . xl_appt_category($appointment['pc_catname']) .'",';
			echo '"' . $pc_apptstatus . '",';
			echo '"' . $appointment['name']. '",';
			echo '"' . $appointment['pc_hometext'] . '"' . "\n";
		} else { // End CSV Export
		?>

        <tr valign='top' id='p1.<?php echo attr($patient_id) ?>' bgcolor='<?php echo $bgcolor ?>'>
        	<td class="detail">&nbsp;<input class="form-choices" name="exp_<?php echo $appointment['pc_eid']; ?>" id="exp_<?php echo $appointment['pc_eid']; ?>" type="checkbox" value="<?php echo $patient_id; ?>" 
			  <?php echo $_POST['exp_' . $appointment['pc_eid']] ? 'checked="checked"' : ''; ?>
			  <?php echo $patient['hipaa_allowsms'] != 'YES' ? 'disabled="disabled"' : ''; ?>
			  </td>
        	<td class="detail">&nbsp;<?php echo ($docname == $lastdocname) ? "" : text($docname) ?></td>
			<td class="detail" <?php echo $showDate ? '' : 'style="display:none;"' ?>><?php echo text(oeFormatShortDate($appointment['pc_eventDate'])) ?></td>
			<td class="detail"><?php echo text(oeFormatTime($appointment['pc_startTime'])) ?></td>
			<td class="detail">&nbsp;<?php echo text($appointment['fname'] . " " . $appointment['lname']) ?></td>
			<td class="detail">&nbsp;<?php echo text($appointment['pubpid']) ?></td>
			<td class="detail">&nbsp;<?php echo text($patient['DOB']) ?></td>
    	<td class="detail">&nbsp;<?php echo text($appointment['phone_home']) ?></td>
	    <td class="detail">&nbsp;<?php echo text($appointment['phone_cell']) ?></td>
   	  <td class="detail">&nbsp;<?php echo text($patient['hipaa_allowsms']) ?></td>
			<!--  td class="detail">&nbsp;<?php echo $address ?></td -->
			<td class="detail">&nbsp;<?php echo text(($appointment['pc_catname'])) ?></td>
			<td class="detail">&nbsp;<?php echo text($appointment['stat_desc']); ?></td>
			<?php
				//Appointment Status
				/*
				if($pc_apptstatus != ""){
					$frow['data_type']=1;
					$frow['list_id']='apptstat';
					generate_print_field($frow, $pc_apptstatus);
				}
				*/
			?>
			</td>
		</tr>

<?php 
			if ($patient_id && $incl_reminders) {
        		// collect reminders first, so can skip it if empty
        		$rems = fetch_reminders ($patient_id, $appointment['pc_eventDate']);
    		} ?>
<?php 		if ($patient_id && (!empty($rems) || !empty($appointment['pc_hometext'])) && !TRUE) { // Not showing reminders and comments for now ?>

		<tr valign='top' id='p2.<?php echo attr($patient_id) ?>' >
			<td colspan=<?php echo $showDate ? '"4"' : '"3"' ?> class="detail" />
			<td colspan=<?php echo ($incl_reminders ? "3":"8") ?> class="detail" align='left'>
<?php
				if (trim($appointment['pc_hometext'])) {
    		        echo '<b>'.xlt('Comments') .'</b>: '.attr($appointment['pc_hometext']);
				}
				if ($incl_reminders) {
        	    	echo "<td class='detail' colspan='5' align='left'>";
        	    	$new_line = '';
        	    	foreach ($rems as $rem_due => $rem_items) {
        	        	echo "$new_line<b>$rem_due</b>: ".attr($rem_items);
        	        	$new_line = '<br>';
        	    	}
        	    	echo "</td>";
        		}
        ?>
	        </td>
		</tr>
	<?php
    		} // end available slot
		} // End NOT CSV Print
	
		$lastdocname = $docname;
	} // End of the appointment report loop
	// assign the session key with the $pid_list array - note array might be empty -- handle on the printed_fee_sheet.php page.
    $_SESSION['pidList'] = $pid_list;
	if (!$_POST['form_csvexport']) {
	?>
		<tr>
			<td colspan="12" align="left"><?php echo xlt('Total number of appointments'); ?>:&nbsp;<?php echo text($totalAppointments);?></td>
		</tr>
	</tbody>
</table>
<table><tr>
	<td><img class="selectallarrow" width="32" height="20" alt="With Selected:" src="<?php echo $GLOBALS['webroot']; ?>/images/arrow_ltr.png"></td>
	<td colspan="2"><a href="javascript:;" class="link_submit" onclick="CheckAll();"><?php xl('Check All','e'); ?></a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="javascript:;" class="link_submit" onclick="UncheckAll();"><span><?php xl('Uncheck All','e'); ?></span></a></td>
</tr></table>
</div>
<?php	
	}
} // End of report engine if the form is in refresh mode
if(!$_POST['form_csvexport']) { 
?>
<div class='text'><?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>

<input type="hidden" name="form_orderby" value="<?php echo attr($form_orderby) ?>" />
<input type="hidden" name="patient" value="<?php echo attr($patient) ?>" /> 
<input type="hidden" name="form_csvexport" id="form_csvexport" value="" />
<input type='hidden' name='form_refresh' id='form_refresh' value='' />
<input type='hidden' name='form_blast' id='form_blast' value='' />
<input type="hidden" name="export_cnt" id="export_cnt" value="<?php echo attr($cnt); ?>" />
</form>

<script type="text/javascript">
<?php
if ($alertmsg) echo " alert('$alertmsg');\n";
?>
</script>
</body>
</html>
<?php
}
?>
