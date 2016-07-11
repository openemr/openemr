<?php
/*
 *  Patient Flow Board (Patient Tracker) (Report Based on the appointment report)
 *
 *  
 *
 *  This program used to select and print the information captured in the Patient Flow Board program , 
 *  allowing the user to select and print the desired information.
 * 
 * Copyright (C) 2015 Terry Hill <terry@lillysystems.com> 
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
 * @author Terry Hill <terry@lilysystems.com> 
 * @link http://www.open-emr.org 
 *
 * Please help the overall project by sending changes you make to the author and to the OpenEMR community.
 * 
 */
 
$fake_register_globals=false;
$sanitize_all_escapes=true;
 
require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/appointments.inc.php";
require_once("$srcdir/patient_tracker.inc.php");

$patient = $_REQUEST['patient'];

if ($patient && ! $_POST['form_from_date']) {
    # This sets the dates in the date select calendars
    # If a specific patient, default to 2 years ago.
    $tmp = date('Y') - 2;
    $from_date = date("$tmp-m-d");
} else {
    $from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
    $to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}

# check box information
$chk_show_details = false;
if ( $_POST['show_details'] ) {
    $chk_show_details = true;
}

$chk_show_drug_screens = false;
if ( $_POST['show_drug_screens'] ) {
    $chk_show_drug_screens = true;
}

$chk_show_completed_drug_screens = false;
if ( $_POST['show_completed_drug_screens'] ) {
    $chk_show_completed_drug_screens = true;
}

# end check box information

$provider  = $_POST['form_provider'];
$facility  = $_POST['form_facility'];  #(CHEMED) facility filter
$form_orderby = getComparisonOrder( $_REQUEST['form_orderby'] ) ?  $_REQUEST['form_orderby'] : 'date';
if ($_POST["form_patient"])
$form_patient = isset($_POST['form_patient']) ? $_POST['form_patient'] : '';
$form_pid = isset($_POST['form_pid']) ? $_POST['form_pid'] : '';
if ($form_patient == '' ) $form_pid = '';
?>

<html>

<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<title><?php echo xlt('Patient Flow Board Report'); ?></title>

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
 
// CapMinds :: invokes  find-patient popup.
 function sel_patient() {
  dlgopen('../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
 }

// CapMinds :: callback by the find-patient popup.
 function setpatient(pid, lname, fname, dob) {
  var f = document.theform;
  f.form_patient.value = lname + ', ' + fname;
  f.form_pid.value = pid;

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
<?php if ($GLOBALS['drug_screen']) { #setting the title of the page based o if drug screening is enabled ?> 
<span class='title'><?php echo xlt('Patient Flow Board'); ?> - <?php echo xlt('Drug Screen Report'); ?></span>
<?php } else { ?>
<span class='title'><?php echo xlt('Patient Flow Board Report'); ?></span>
<?php } ?>


<div id="report_parameters_daterange"><?php echo date("d F Y", strtotime($from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($to_date)); #sets date range for calendars ?>
</div>

<form method='post' name='theform' id='theform' action='patient_flow_board_report.php' onsubmit='return top.restoreSession()'>

<div id="report_parameters">

<table>
    <tr>
        <td width='650px'>
        <div style='float: left'>

        <table class='text'>
            <tr>
                <td class='label'><?php echo xlt('Facility'); ?>:</td>
                <td><?php dropdown_facility($facility, 'form_facility'); ?>
                </td>
                <td class='label'><?php echo xlt('Provider'); ?>:</td>
                <td><?php

                # Build a drop-down list of providers.
                #

                $query = "SELECT id, lname, fname FROM users WHERE ".
                  "authorized = 1  ORDER BY lname, fname"; #(CHEMED) facility filter

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

                ?></td>

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
			<td>
			&nbsp;&nbsp;<span class='text'><?php echo xlt('Patient'); ?>: </span>
			</td>
			<td>
			<input type='text' size='20' name='form_patient' style='width:100%;cursor:pointer;cursor:hand' value='<?php echo attr($form_patient) ? attr($form_patient) : xla('Click To Select'); ?>' onclick='sel_patient()' title='<?php echo xla('Click to select patient'); ?>' />
			<input type='hidden' name='form_pid' value='<?php echo attr($form_pid); ?>' />
			</td>
			
                <td colspan="2"><label><input type="checkbox" name="show_details" id="show_details" <?php if($chk_show_details) echo "checked";?>>&nbsp;<?php echo xlt('Show Details'); ?></label></td>
            </tr>  			
            <tr>
 
            </tr>
            <?php if ($GLOBALS['drug_screen']) { ?>
           	<tr>
            <?php # these two selects will are for the drug screen entries the Show Selected for Drug Screens will show all
                  # that have a yes for selected. If you just check the Show Status of Drug Screens all drug screens will be displayed
                  # if both are selected then only completed drug screens will be displayed. ?>
            <td colspan="2"><label><input type="checkbox" name="show_drug_screens" id="show_drug_screens" <?php if($chk_show_drug_screens) echo "checked";?>>&nbsp;<?php echo xlt('Show Selected for Drug Screens'); ?></label></td>
            <td colspan="2"><label><input type="checkbox" name="show_completed_drug_screens" id="show_completed_drug_screens" <?php if($chk_show_completed_drug_screens) echo "checked";?>>&nbsp;<?php echo xlt('Show Status of Drug Screens'); ?></label></td>
            </tr>
            <?php } ?>
                      
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
                                <?php } ?>
                </div>
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
    ?>
<div id="report_results">
<table>

	<thead>
    <?php if (!$chk_show_drug_screens && !$chk_show_completed_drug_screens) { # the first part of this block is for the Patient Flow Board report ?>
        <th><a href="nojs.php" onclick="return dosort('doctor')"
     <?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Provider'); ?>
        </a></th>

        <th><a href="nojs.php" onclick="return dosort('date')"
     <?php if ($form_orderby == "date") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Date'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('time')"
     <?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Time'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('patient')"
     <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>>&nbsp;&nbsp;&nbsp;<?php  echo xlt('Patient'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('pubpid')"
     <?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>>&nbsp;<?php  echo xlt('ID'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('type')"
     <?php if ($form_orderby == "type") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Type'); ?></a>
        </th>

      <?php if ($chk_show_details) { ?>
        <th><a href="nojs.php" onclick="return dosort('trackerstatus')"
     <?php if ($form_orderby == "trackerstatus") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Status'); ?></a>
        </th> 
      <?php } else { ?>
        <th><a href="nojs.php" onclick="return dosort('trackerstatus')"
     <?php if ($form_orderby == "trackerstatus") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Final Status'); ?></a>
        </th> 
     <?php } ?>
 
        
        <th><?php 
                 if ($chk_show_details) { # not sure if Sorting by Arrive Time is useful
                     echo xlt('Start Time');  
                 }
                 else
                 {
                     echo xlt('Arrive Time');                    
                 }?></th>

        <th><?php
                 if ($chk_show_details) {   # not sure if Sorting by Discharge Time is useful 
                     echo xlt('End Time');
                 }
                 else
                 {
                     echo xlt('Discharge Time');
                 }?></th>
        
        <th><?php echo xlt('Total Time'); # not adding Sorting by Total Time yet but can see that it might be useful ?></th>
    
    <?php } else { # this section is for the drug screen report ?>
    
        <th><a href="nojs.php" onclick="return dosort('doctor')"
     <?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Provider'); ?>
        </a></th>

        <th><a href="nojs.php" onclick="return dosort('date')"
     <?php if ($form_orderby == "date") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Date'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('time')"
     <?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Time'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('patient')"
     <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>>&nbsp;&nbsp;&nbsp;&nbsp;<?php  echo xlt('Patient'); ?></a>
        </th>

     <?php if (!$chk_show_completed_drug_screens) { ?>
        <th><a href="nojs.php" onclick="return dosort('pubpid')"
      <?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>>&nbsp;<?php  echo xlt('ID'); ?></a>
        </th>
      <?php } else { ?>       
        <th><a href="nojs.php" onclick="return dosort('pubpid')"
      <?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>>&nbsp;<?php  echo xlt('ID'); ?></a>
        </th>    
     <?php } ?>
    
        <th><?php echo xlt('Drug Screen'); # not sure if Sorting by Drug Screen is useful ?></th>
  
     <?php if (!$chk_show_completed_drug_screens) { ?>
         <th>&nbsp;</th>
      <?php } else { ?>
         <th><a href="nojs.php" onclick="return dosort('completed')"
      <?php if ($form_orderby == "completed") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Completed'); ?></a>
         </th>   
     <?php } ?>
  
     <th></th><th></th><th></th>

    <?php } ?>
    </thead>
    <tbody>
        <!-- added for better print-ability -->
    <?php
	
    $lastdocname = "";
    #Appointment Status Checking
        $form_apptstatus = $_POST['form_apptstatus'];
        $form_apptcat=null;
    if(isset($_POST['form_apptcat']))
        {
            if($form_apptcat!="ALL")
            {
                $form_apptcat=intval($_POST['form_apptcat']);
            }
        }
            
    #Without provider and facility data checking
    $with_out_provider = null;
    $with_out_facility = null;

    # get the appointments also set the trackerboard flag to true (last entry in the fetchAppointments call so we get the tracker stuff) 
    $appointments = fetchAppointments( $from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility,$form_apptcat,true );
    # sort the appointments by the appointment time
    $appointments = sortAppointments( $appointments, $form_orderby );
    # $j is used to count the number of patients that match the selected criteria.    
    $j=0;
    //print_r2($appointments);
    foreach ( $appointments as $appointment ) {
        $patient_id = $appointment['pid'];
        $tracker_id = $appointment['pt_tracker_id'];
        $last_seq = $appointment['lastseq'];
        $docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];
        # only get items with a tracker id.
        if ($tracker_id == '' ) continue;
        # only get the drug screens that are set to yes.
        if ($chk_show_drug_screens ==1 ) {
           if ($appointment['random_drug_test'] != '1') continue;
        }
        #if a patient id is entered just get that patient.       
        if (strlen($form_pid) !=0 ) {
          if ($appointment['pid'] != $form_pid ) continue;
        } 
        
        $errmsg  = "";
        $newarrive = '';
        $newend = '';
        $no_visit = 1;
        # getting arrive time and end time from the elements file.
        if ($tracker_id != 0) {
           $newarrive = collect_checkin($tracker_id);
           $newend = collect_checkout($tracker_id);
        }

        if ($newend != '' && $newarrive != '') {
            $no_visit = 0;
        }
        $tracker_status = $appointment['status'];
        # get the time interval for the entire visit. to display seconds add last option of true. 
        # get_Tracker_Time_Interval($newarrive, $newend, true)
        $timecheck2 = get_Tracker_Time_Interval($newarrive, $newend);        
        # Get the tracker elements.
        $tracker_elements = collect_Tracker_Elements($tracker_id);
        # $j is incremented for a patient that made it for display.
        $j=$j+1;
        ?>

    <tr bgcolor='<?php echo $bgcolor ?>'>
       <?php if (!$chk_show_drug_screens && !$chk_show_completed_drug_screens) { # the first part of this block is for the Patient Flow Board report ?>
        <td class="detail">&nbsp;<?php echo ($docname == $lastdocname) ? "" : $docname ?>
        </td>

        <td class="detail"><?php echo text(oeFormatShortDate($appointment['pc_eventDate'])) ?>
        </td>
        
        <td class="detail"><?php echo text(oeFormatTime($appointment['pc_startTime'])) ?>
        </td>

        <td class="detail">&nbsp;<?php echo text($appointment['fname'] . " " . $appointment['lname']) ?>
        </td>

        <td class="detail">&nbsp;<?php echo text($appointment['pubpid']) ?>
        </td>

        <td class="detail">&nbsp;<?php echo text(xl_appt_category($appointment['pc_catname'])) ?>
        </td>

        <td class="detail">
            <?php
                //Appointment Status
                if($chk_show_details) {
                   if($no_visit != 1) {
                    echo xlt('Complete Visit Time');
                   }
                }
                else
                {
                 if($tracker_status != ""){
                    $frow['data_type']=1;
                    $frow['list_id']='apptstat';
                    generate_print_field($frow, $tracker_status);
                 }
                }
            ?>
        </td>
        
        <td class="detail">&nbsp;<?php echo text(substr($newarrive,11)) ?>
        </td>

        <td class="detail">&nbsp;<?php echo text(substr($newend,11)) ?>
        </td>

        <?php if ($no_visit != 1) { ?>        
        <td class="detail">&nbsp;<?php echo text($timecheck2) ?></td>
        <?php } else { ?>
        <td class="detail">&nbsp;</td>
        <?php } ?>
        <?php if ($chk_show_details) { # lets show the detail lines 
              $i = '0';
              $k = '0';
              for ($x = 1; $x <= $last_seq; $x++) {   
        ?>
	    <tr valign='top' class="detail" >
	      <td colspan="6" class="detail" align='left'>
          
            <?php
                # get the verbiage for the status code            
                $track_stat = $tracker_elements[$i][status];
                # Get Interval alert time and status color.
                $colorevents = (collectApptStatusSettings($track_stat));
                $alert_time = '0';
                $alert_color = $colorevents['color'];
                $alert_time = $colorevents['time_alert'];
                if (is_checkin($track_stat) || is_checkout($track_stat)) {  #bold the check in and check out times in this block.
            ?> 
            <td class="detail"><b>
            <?php } else { ?>            
            <td class="detail">
            <?php
                }
                echo  getListItemTitle("apptstat",$track_stat);
            ?> 
            </b></td>
            <?php
               if (is_checkin($track_stat) || is_checkout($track_stat)) {  #bold the check in and check out times in this block.
            ?>             
            <td class="detail"><b>&nbsp;<?php echo text(substr($tracker_elements[$i][start_datetime],11)); ?></b></td>
            <?php } else { ?>  
            <td class="detail">&nbsp;<?php echo text(substr($tracker_elements[$i][start_datetime],11)); ?></td>
            <?php # figure out the next time of the status
               }
             $k = $i+1;
            if($k < $last_seq) {
               # get the start time of the next status to determine the total time in this status
               $start_tracker_time = $tracker_elements[$i][start_datetime]; 
               $next_tracker_time = $tracker_elements[$k][start_datetime];
             }
             else
             {
               # since this is the last status the start and end are equal
               $start_tracker_time = $tracker_elements[$i][start_datetime];
               $next_tracker_time = $tracker_elements[$i][start_datetime];
             }
               if (is_checkin($track_stat) || is_checkout($track_stat)) {  #bold the check in and check out times in this block.
            ?>             
            <td class="detail"><b>&nbsp;<?php echo text(substr($next_tracker_time,11)) ?></b></td>
            <?php } else { ?>
            <td class="detail">&nbsp;<?php echo text(substr($next_tracker_time,11)) ?></td>
            <?php # compute the total time of the status
               }
              $tracker_time = get_Tracker_Time_Interval($start_tracker_time, $next_tracker_time);
              # add code to alert if over time interval for status
              $timecheck = round(abs( strtotime($start_tracker_time) -  strtotime($next_tracker_time)) / 60,0);
              if($timecheck > $alert_time && ($alert_time != '0')) {
                 if (is_checkin($track_stat) || is_checkout($track_stat)) {  #bold the check in and check out times in this block.
            ?>             
            <td class="detail" bgcolor='<?php echo attr($alert_color) ?>'><b>&nbsp;<?php echo text($tracker_time); ?></b></td>
            <?php } else { ?>
            <td class="detail" bgcolor='<?php echo attr($alert_color) ?>'>&nbsp;<?php echo text($tracker_time); ?></td>
            <?php } ?>             
            <?php } else { if (is_checkin($track_stat) || is_checkout($track_stat)) { #bold the check in and check out times in this block. ?>
            <td class="detail"><b>&nbsp;<?php echo text($tracker_time); ?></b></td>
            <?php } else { ?>
            <td class="detail">&nbsp;<?php echo text($tracker_time); ?></td>
            <?php 
              }
              }
               $i++;
            }
          }
        ?>
        </td>
        </tr>
        
    <?php } else { # this section is for the drug screen report ?>  

        <td class="detail">&nbsp;<?php echo ($docname == $lastdocname) ? "" : $docname ?>
        </td>

        <td class="detail"><?php echo text(oeFormatShortDate($appointment['pc_eventDate'])) ?>
        </td>

        <td class="detail"><?php echo text(oeFormatTime($appointment['pc_startTime'])) ?>
        </td>

        <td class="detail">&nbsp;<?php echo text($appointment['fname'] . " " . $appointment['lname']) ?>
        </td>

        <td class="detail">&nbsp;<?php echo text($appointment['pubpid']) ?></td>

        <td class="detail" align = >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($appointment['random_drug_test'] == '1') {  echo xlt('Yes'); }  else { echo xlt('No'); }?></td>
 
        <?php if ($chk_show_completed_drug_screens) { ?>
          <td class="detail">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($appointment['drug_screen_completed'] == '1') {  echo xlt('Yes'); }  else { echo xlt('No'); }?></td>
        <?php } else { ?>
          <td class="detail">&nbsp; </td> 
        <?php } ?> 

        <?php # these last items are used to complete the screen ?>
        <td class="detail">&nbsp;</td>

        <td class="detail">&nbsp;</td>

        <td class="detail">&nbsp;</td>
    <?php } ?>      
    </tr>

    <?php
    $lastdocname = $docname;
    } # end for
    ?>
    <tr>
     <?php if (!$chk_show_drug_screens && !$chk_show_completed_drug_screens) { # is it Patient Flow Board or Drug screen ?>
        <td colspan="10" align="left"><?php echo xlt('Total number of Patient Flow Board entries'); ?>&nbsp;<?php echo text($j);?>&nbsp;<?php echo xlt('Patients'); ?></td>
     <?php } else { ?>
        <td colspan="10" align="left"><?php echo xlt('Total number of Drug Screen entries'); ?>&nbsp;<?php echo text($j);?>&nbsp;<?php echo xlt('Patients'); ?></td>
     <?php } ?> 
    </tr>
    </tbody>
</table>
</div>
<!-- end of search results --> <?php } else { ?>
<div class='text'><?php echo xlt('Please input search criteria above, and click Submit to view results.' ); ?>
</div>
    <?php } ?> <input type="hidden" name="form_orderby"
    value="<?php echo attr($form_orderby) ?>" /> <input type="hidden"
    name="patient" value="<?php echo attr($patient) ?>" /> <input type='hidden'
    name='form_refresh' id='form_refresh' value='' /></form>

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

