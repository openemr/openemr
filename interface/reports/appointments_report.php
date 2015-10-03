<?php
// Copyright (C) 2005-2010 Rod Roark <rod@sunsetsystems.com>
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

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/appointments.inc.php";
require_once "$srcdir/clinical_rules.php";

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

//$to_date   = fixDate($_POST['form_to_date'], '');
$provider  = $_POST['form_provider'];
$facility  = $_POST['form_facility'];  //(CHEMED) facility filter
$form_orderby = getComparisonOrder( $_REQUEST['form_orderby'] ) ?  $_REQUEST['form_orderby'] : 'date';

// Reminders related stuff
$incl_reminders = isset($_POST['incl_reminders']) ? 1 : 0;
function fetch_rule_txt ($list_id, $option_id) {
    $rs = sqlQuery('SELECT title, seq from list_options WHERE list_id=? AND option_id=?',
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

<title><?php echo xlt('Appointments Report'); ?></title>

<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script type="text/javascript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 $(document).ready(function() {
  var win = top.printLogSetup ? top : opener.top;
  win.printLogSetup(document.getElementById('printbutton'));
 });

 function dosort(orderby) {
    var f = document.forms[0];
    f.form_orderby.value = orderby;
    f.submit();
    return false;
 }

 function oldEvt(eventid) {
    dlgopen('../main/calendar/add_edit_event.php?eid=' + eventid, 'blank', 550, 270);
 }

 function refreshme() {
    // location.reload();
    document.forms[0].submit();
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

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv"
	style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Appointments'); ?></span>

<div id="report_parameters_daterange"><?php echo date("d F Y", strtotime($from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($to_date)); #sets date range for calendars ?>
</div>

<form method='post' name='theform' id='theform' action='appointments_report.php' onsubmit='return top.restoreSession()'>

<div id="report_parameters">

<table>
	<tr>
		<td width='650px'>
		<div style='float: left'>

		<table class='text'>
			<tr>
				<td class='label'><?php echo xlt('Facility'); ?>:</td>
				<td><?php dropdown_facility($facility , 'form_facility'); ?>
				</td>
				<td class='label'><?php echo xlt('Provider'); ?>:</td>
				<td><?php

				// Build a drop-down list of providers.
				//

				$query = "SELECT id, lname, fname FROM users WHERE ".
				  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

				$ures = sqlStatement($query);

				echo "   <select name='form_provider'>\n";
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
				<td class='label'><?php echo xlt('From'); ?>:</td>
				<td><input type='text' name='form_from_date' id="form_from_date"
					size='10' value='<?php echo attr($from_date) ?>'
					onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
					title='yyyy-mm-dd'> <img src='../pic/show_calendar.gif'
					align='absbottom' width='24' height='22' id='img_from_date'
					border='0' alt='[?]' style='cursor: pointer'
					title='<?php echo xlt('Click here to choose a date'); ?>'></td>
				<td class='label'><?php echo xlt('To'); ?>:</td>
				<td><input type='text' name='form_to_date' id="form_to_date"
					size='10' value='<?php echo attr($to_date) ?>'
					onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
					title='yyyy-mm-dd'> <img src='../pic/show_calendar.gif'
					align='absbottom' width='24' height='22' id='img_to_date'
					border='0' alt='[?]' style='cursor: pointer'
					title='<?php echo xlt('Click here to choose a date'); ?>'></td>
			</tr>
			
			<tr>
				<td class='label'><?php echo xlt('Status'); # status code drop down creation ?>:</td>
				<td><?php generate_form_field(array('data_type'=>1,'field_id'=>'apptstatus','list_id'=>'apptstat','empty_title'=>'All'),$_POST['form_apptstatus']);?></td>
				<td><?php echo xlt('Category') #category drop down creation ?>:</td>
				<td>
                                    <select id="form_apptcat" name="form_apptcat">
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
				<td><label><input type='checkbox' name='form_show_available'
					<?php  if ( $show_available_times ) echo ' checked'; ?>> <?php  echo xlt('Show Available Times'); # check this to show available times on the report ?>
				</label></td>
			    <td></td>
                <td><label><input type="checkbox" name="incl_reminders" id="incl_reminders" 
                    <?php echo ($incl_reminders ? ' checked':''); # This will include the reminder for the patients on the report ?>>
                    <?php echo xlt('Show Reminders'); ?></label></td>
			
			<tr>
			    <td></td>
                <?php # these two selects will show entries that do not have a facility or a provider ?>
				<td><label><input type="checkbox" name="with_out_provider" id="with_out_provider" <?php if($chk_with_out_provider) echo "checked";?>>&nbsp;<?php echo xlt('Without Provider'); ?></label></td>
			    <td></td>
				<td><label><input type="checkbox" name="with_out_facility" id="with_out_facility" <?php if($chk_with_out_facility) echo "checked";?>>&nbsp;<?php echo xlt('Without Facility'); ?></label></td>
			</tr>
			
		</table>

		</div>

		</td>
		<td align='left' valign='middle' height="100%">
		<table style='border-left: 1px solid; width: 100%; height: 100%'>
			<tr>
				<td>
				<div style='margin-left: 15px'>
                                <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
				<span> <?php echo xlt('Submit'); ?> </span> </a> 
                                <?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
        <a href='#' class='css_button' id='printbutton'> 
                                    <span> <?php echo xlt('Print'); ?> </span> </a> 
                                <a href='#' class='css_button' onclick='window.open("../patient_file/printed_fee_sheet.php?fill=2","_blank")' onsubmit='return top.restoreSession()'> 
                                    <span> <?php echo xlt('Superbills'); ?> </span> </a> 
                                <?php } ?></div>
				</td>
			</tr>
                        <tr>&nbsp;&nbsp;<?php echo xlt('Most column headers can be clicked to change sort order') ?></tr>
		</table>
		</td>
	</tr>
</table>

</div>
<!-- end of search parameters --> <?php
if ($_POST['form_refresh'] || $_POST['form_orderby']) {
	$showDate = ($from_date != $to_date) || (!$to_date);
	?>
<div id="report_results">
<table>

	<thead>
		<th><a href="nojs.php" onclick="return dosort('doctor')"
	<?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Provider'); ?>
		</a></th>

		<th <?php echo $showDate ? '' : 'style="display:none;"' ?>><a href="nojs.php" onclick="return dosort('date')"
	<?php if ($form_orderby == "date") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Date'); ?></a>
		</th>

		<th><a href="nojs.php" onclick="return dosort('time')"
	<?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Time'); ?></a>
		</th>

		<th><a href="nojs.php" onclick="return dosort('patient')"
	<?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Patient'); ?></a>
		</th>

		<th><a href="nojs.php" onclick="return dosort('pubpid')"
	<?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('ID'); ?></a>
		</th>

         	<th><?php echo xlt('Home'); //Sorting by phone# not really useful ?></th>

                <th><?php echo xlt('Cell'); //Sorting by phone# not really useful ?></th>
                
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
	
	$lastdocname = "";
	//Appointment Status Checking
        $form_apptstatus = $_POST['form_apptstatus'];
        $form_apptcat=null;
	if(isset($_POST['form_apptcat']))
        {
            if($form_apptcat!="ALL")
            {
                $form_apptcat=intval($_POST['form_apptcat']);
            }
        }
            
	//Without provider and facility data checking
	$with_out_provider = null;
	$with_out_facility = null;

	if( isset($_POST['with_out_provider']) ){
		$with_out_provider = $_POST['with_out_provider'];
	}
	
	if( isset($_POST['with_out_facility']) ){
		$with_out_facility = $_POST['with_out_facility'];
	}
	$appointments = fetchAppointments( $from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility,$form_apptcat );
	
	if ( $show_available_times ) {
		$availableSlots = getAvailableSlots( $from_date, $to_date, $provider, $facility );
		$appointments = array_merge( $appointments, $availableSlots );
	}

	$appointments = sortAppointments( $appointments, $form_orderby );
    $pid_list = array();  // Initialize list of PIDs for Superbill option
    $totalAppontments = count($appointments);   
	
	foreach ( $appointments as $appointment ) {
                array_push($pid_list,$appointment['pid']);
		$patient_id = $appointment['pid'];
		$docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];
                
        $errmsg  = "";
		$pc_apptstatus = $appointment['pc_apptstatus'];

		?>

        <tr valign='top' id='p1.<?php echo attr($patient_id) ?>' bgcolor='<?php echo $bgcolor ?>'>
        <td class="detail">&nbsp;<?php echo ($docname == $lastdocname) ? "" : text($docname) ?>
		</td>

		<td class="detail" <?php echo $showDate ? '' : 'style="display:none;"' ?>><?php echo text(oeFormatShortDate($appointment['pc_eventDate'])) ?>
		</td>

		<td class="detail"><?php echo text(oeFormatTime($appointment['pc_startTime'])) ?>
		</td>

		<td class="detail">&nbsp;<?php echo text($appointment['fname'] . " " . $appointment['lname']) ?>
		</td>

		<td class="detail">&nbsp;<?php echo text($appointment['pubpid']) ?></td>

        <td class="detail">&nbsp;<?php echo text($appointment['phone_home']) ?></td>

        <td class="detail">&nbsp;<?php echo text($appointment['phone_cell']) ?></td>

		<td class="detail">&nbsp;<?php echo text(xl_appt_category($appointment['pc_catname'])) ?></td>
		
		<td class="detail">&nbsp;
			<?php
				//Appointment Status
				if($pc_apptstatus != ""){
					$frow['data_type']=1;
					$frow['list_id']='apptstat';
					generate_print_field($frow, $pc_apptstatus);
				}
			?>
		</td>
	</tr>

    <?php if ($patient_id && $incl_reminders) {
        // collect reminders first, so can skip it if empty
        $rems = fetch_reminders ($patient_id, $appointment['pc_eventDate']);
    } ?>
    <?php if ($patient_id && (!empty($rems) || !empty($appointment['pc_hometext']))) { // Not display of available slot or not showing reminders and comments empty ?>
	<tr valign='top' id='p2.<?php echo attr($patient_id) ?>' >
	   <td colspan=<?php echo $showDate ? '"3"' : '"2"' ?> class="detail" />
	   <td colspan=<?php echo ($incl_reminders ? "3":"6") ?> class="detail" align='left'>
		<?php
		if (trim($appointment['pc_hometext'])) {
            echo '<b>'.xlt('Comments') .'</b>: '.attr($appointment['pc_hometext']);
		}
		if ($incl_reminders) {
            echo "<td class='detail' colspan='3' align='left'>";
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
    } // End of row 2 display
    
	$lastdocname = $docname;
	}
	// assign the session key with the $pid_list array - note array might be empty -- handle on the printed_fee_sheet.php page.
        $_SESSION['pidList'] = $pid_list;
	?>
	<tr>
		<td colspan="10" align="left"><?php echo xlt('Total number of appointments'); ?>:&nbsp;<?php echo text($totalAppontments);?></td>
	</tr>
	</tbody>
</table>
</div>
<!-- end of search results --> <?php } else { ?>
<div class='text'><?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
	<?php } ?> <input type="hidden" name="form_orderby"
	value="<?php echo attr($form_orderby) ?>" /> <input type="hidden"
	name="patient" value="<?php echo attr($patient) ?>" /> <input type='hidden'
	name='form_refresh' id='form_refresh' value='' /></form>

<script type="text/javascript">

<?php
if ($alertmsg) { echo " alert('$alertmsg');\n"; }
?>

</script>

</body>

<!-- stuff for the popup calendar -->
<style type="text/css">
    @import url(../../library/dynarch_calendar.css);
</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript"
	src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
