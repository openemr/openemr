<?php
/** 
 *  Patient Tracker (Patient Flow Board)
 *
 *  This program displays the information entered in the Calendar program , 
 *  allowing the user to change status and view those changed here and in the Calendar
 *  Will allow the collection of length of time spent in each status
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
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient_tracker.inc.php");

?>
<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/common.js"></script>
<script type="text/javascript" src="../../library/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="../../library/js/blink/jquery.modern-blink.js"></script>

<script language="JavaScript">
$(document).ready(function(){
  refreshbegin('1');
  $('.js-blink-infinite').modernBlink();
});
 
// popup for patient tracker status 
function bpopup(tkid) {
 top.restoreSession();
 window.open('../patient_tracker/patient_tracker_status.php?tracker_id=' + tkid ,'_blank', 'width=500,height=250,resizable=1');
 return false;
}

// popup for calendar add edit 
function calendarpopup(eid,date_squash) {
 top.restoreSession()   
 window.open('../main/calendar/add_edit_event.php?eid=' + eid + '&date=' + date_squash,'_blank', 'width=550,height=400,resizable=1');
 return false;
}

// auto refresh screen pat_trkr_timer is the timer variable
function refreshbegin(first){
  <?php if ($GLOBALS['pat_trkr_timer'] != '0') { ?>
    var reftime="<?php echo attr($GLOBALS['pat_trkr_timer']); ?>";
    var parsetime=reftime.split(":");
    parsetime=(parsetime[0]*60)+(parsetime[1]*1)*1000;
    if (first != '1') {
      top.restoreSession();
      document.pattrk.submit();
    }
    setTimeout("refreshbegin('0')",parsetime);
  <?php } else { ?>
    return;
 <?php } ?>
} 

// used to display the patient demographic and encounter screens
function topatient(newpid, enc) {
 if (document.pattrk.form_new_window.checked) {
   openNewTopWindow(newpid,enc);
 }
 else {
   top.restoreSession();
   <?php if ($GLOBALS['concurrent_layout']) { ?>
     if (enc > 0) {
       document.location.href = "../patient_file/summary/demographics.php?set_pid=" + newpid + "&set_encounterid=" + enc;
     }
     else {
       document.location.href = "../patient_file/summary/demographics.php?set_pid=" + newpid; 
     }
   <?php } else { ?>
     top.location.href = "../patient_file/patient_file.php?set_pid=" + newpid;
   <?php } ?>
 }
}

// opens the demographic and encounter screens in a new window
function openNewTopWindow(newpid,newencounterid) {
 document.fnew.patientID.value = newpid;
 document.fnew.encounterID.value = newencounterid;
 top.restoreSession();
 document.fnew.submit();
 }
 
</script>
<style>
  table tr td, table tr th{
    font-size: 12px !important;
    text-align: left;
    padding:15px 10px;
    border-bottom: 1px solid #efefef;
  }
  table {
    margin-top:25px;
  }
</style>
</head>

<body class="body_top">
<?php $action_suffix = ($GLOBALS['pat_trkr_timer'] == '0') ? '' : '?skip_timeout_reset=1'; ?>
<form name='pattrk' id='pattrk' method='post' action='patient_tracker.php<?php echo $action_suffix;?>' onsubmit='return top.restoreSession()' enctype='multipart/form-data'>

<?php
if (isset($_POST['setting_new_window'])) {
  $new_window_checked = (isset($_POST['form_new_window'])) ? " checked" : '';
} else {
  $new_window_checked = ($GLOBALS['ptkr_pt_list_new_window']) ? " checked" : '';
}
?>
<input type='hidden' name='setting_new_window' value='1' />
<input type='checkbox' name='form_new_window' id="form_new_window" value='1'<?php echo $new_window_checked; ?> />
<label for="form_new_window"><?php echo xlt('Open Patient in New Window'); ?></label>
<?php if ($GLOBALS['pat_trkr_timer'] =='0') { ?>
<a href='javascript:;' class='css_button_small' align='center' style='color:gray' onclick="document.getElementById('pattrk').submit();"><span><?php echo xlt('Refresh Screen'); ?></span></a>
<?php } ?>
<div class="clearfix"></div>
<table style="width:100%" cellpadding="0" cellspacing="0">
  <thead>
    <tr>
      <th><?php echo xlt('PID'); ?></th>
      <th><?php echo xlt('Patient'); ?></th>
      <th><?php echo xlt('Encounter'); ?></th>
      <th><?php echo xlt('Exam Room #'); ?></th><th><?php echo xlt('Appt Time'); ?></th>
      <th><?php echo xlt('Arrive Time'); ?></th>
      <th><?php echo xlt('Status'); ?></th>
      <th><?php echo xlt('Current Status Time'); ?></th>
      <th><?php echo xlt('Visit Type'); ?></th>
      <th><?php echo xlt('Provider'); ?></th>
      <th><?php echo xlt('Total Time'); ?></th>
      <th><?php echo xlt('Check Out Time'); ?></th>
      <th><?php echo xlt('Updated By'); ?></th>
      <?php if ($GLOBALS['drug_screen']) : ?>
      <th><?php echo xlt('Random Drug Screen'); ?></th>
      <th><?php echo xlt('Drug Screen Completed'); ?></th>
      <?php endif; // end if drug_screen ?>
    </tr>
  </thead>

<?php
#define variables, future enhancement allow changing the to_date and from_date 
#to allow picking a date to review
 
$appointments = array();
$from_date = date("Y-m-d");
$to_date = date("Y-m-d");
$datetime = date("Y-m-d H:i:s");

# go get the information and process it
$appointments = fetch_Patient_Tracker_Events($from_date, $to_date);
$appointments = sortAppointments( $appointments, 'time' );

foreach ( $appointments as $appointment ) {
  # Collect appt date and set up squashed date for use below
  $date_appt = $appointment['pc_eventDate'];
  $date_squash = str_replace("-","",$date_appt);

  if ($appointment['pc_recurrtype'] != 0) {
    # TODO: Note this block of code can likely be removed when the appointment recursion bug has been fixed.
    # don't show if date has been excluded
    # example of pc_recurrspec having "exdate" of "20150527,20150528,";
    $recurrent_info = unserialize($appointment['pc_recurrspec']);
    if (preg_match("/$date_squash/",$recurrent_info['exdate'])) {
            continue;
    }
  }

  # Collect variables and do some processing
  $docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];
  if (strlen($docname)<= 3 ) continue;
  $ptname = $appointment['lname'] . ', ' . $appointment['fname'] . ' ' . $appointment['mname'];
  $appt_enc = $appointment['encounter'];
  $appt_eid = (!empty($appointment['eid'])) ? $appointment['eid'] : $appointment['pc_eid'];
  $appt_pid = (!empty($appointment['pid'])) ? $appointment['pid'] : $appointment['pc_pid'];
  $status = (!empty($appointment['status'])) ? $appointment['status'] : $appointment['pc_apptstatus'];
  $appt_room = (!empty($appointment['room'])) ? $appointment['room'] : $appointment['pc_room'];
  $appt_time = (!empty($appointment['appttime'])) ? $appointment['appttime'] : $appointment['pc_startTime'];
  $tracker_id = $appointment['id'];
  $newarrive = collect_checkin($tracker_id);
  $newend = collect_checkout($tracker_id);
  $colorevents = (collectApptStatusSettings($status));
  $bgcolor = $colorevents['color'];
  $statalert = $colorevents['time_alert'];
  # process the time to allow items with a check out status to be displayed
  if ( is_checkout($status) && ($GLOBALS['checkout_roll_off'] > 0) ) {
    $to_time = strtotime($newend);
    $from_time = strtotime($datetime);
    $display_check_out = round(abs($from_time - $to_time) / 60,0);
    if ( $display_check_out >= $GLOBALS['checkout_roll_off'] ) continue;
  }
?>
  <tr bgcolor='<?php echo $bgcolor ?>'>
    <td><?php echo text($appt_pid) ?></td>
    <td>
      <a href="#" onclick="return topatient('<?php echo attr($appt_pid);?>','<?php echo attr($appt_enc);?>')" >
        <?php echo text($ptname); ?>
      </a>
    </td>
    <td><?php if($appt_enc != 0) echo text($appt_enc); ?></a></td>
    <td><?php echo getListItemTitle('patient_flow_board_rooms', $appt_room);?></td>
    <td><?php echo text($appt_time) ?></td>
    <td><?php echo text(substr($newarrive,11)); ?></td>
    <td>
      <?php if (empty($tracker_id)) { #for appt not yet with tracker id and for recurring appt ?>
      <a href=""  onclick="return calendarpopup(<?php echo attr($appt_eid).",".attr($date_squash); # calls popup for add edit calendar event?>)">
      <?php } else { ?>
      <a href=""  onclick="return bpopup(<?php echo attr($tracker_id); # calls popup for patient tracker status?>)">
      <?php } ?>
        <?php echo text(getListItemTitle("apptstat",$status)); # drop down list for appointment status?>
      </a>
    </td>
    <?php
    #time in current status
    $to_time = strtotime(date("Y-m-d H:i:s"));
    $yestime = '0';
    if (strtotime($newend) != '') {
      $from_time = strtotime($newarrive);
      $to_time = strtotime($newend);
      $yestime = '0';
    } else {
      $from_time = strtotime($appointment['start_datetime']);
      $yestime = '1';
    }

    $timecheck = round(abs($to_time - $from_time) / 60,0);
    if ($timecheck >= $statalert && ($statalert != '0')) { # Determine if the time in status limit has been reached.
      echo "<td align='center' class='js-blink-infinite'>    "; # and if so blink
    } else {
      echo "<td align='center' class='detail'> "; # and if not do not blink
    }

    if (($yestime == '1') && ($timecheck >=1) && (strtotime($newarrive)!= '')) {
      echo text($timecheck . ' ' .($timecheck >=2 ? xl('minutes'): xl('minute')));
    }
    #end time in current status
    ?>
    </td>
    <td><?php echo text(xl_appt_category($appointment['pc_title'])) ?></td>
    <td><?php echo text($docname); ?></td>
    <td>
    <?php
    # total time in practice
    if (strtotime($newend) != '') {
      $from_time = strtotime($newarrive);
      $to_time = strtotime($newend);
    } else {
      $from_time = strtotime($newarrive);
      $to_time = strtotime(date("Y-m-d H:i:s"));
    }
    $timecheck2 = round(abs($to_time - $from_time) / 60,0);
    if (strtotime($newarrive) != '' && ($timecheck2 >=1)) {
      echo text($timecheck2 . ' ' .($timecheck2 >=2 ? xl('minutes'): xl('minute')));
    }
    # end total time in practice
    echo text($appointment['pc_time']); ?>
    </td>
    <td>
    <?php
    if (strtotime($newend) != '') {
      echo text(substr($newend,11)) ;
    }
    ?>
    </td>
    <td>
    <?php echo text($appointment['user']) ?>
    </td>
    <?php if ($GLOBALS['drug_screen']) { ?>
    <?php if (strtotime($newarrive) != '') { ?>
    <td>
    <?php if (text($appointment['random_drug_test']) == '1') {  echo xl('Yes'); }  else { echo xl('No'); }?>
    </td>
    <?php } else {  echo "  <td>"; }?>
    <?php if (strtotime($newarrive) != '' && $appointment['random_drug_test'] == '1') { ?>
    <td>
    <?php if (strtotime($newend) != '') { # the following block allows the check box for drug screens to be disabled once the status is check out ?>
       <input type=checkbox  disabled='disable' class="drug_screen_completed" id="<?php echo htmlspecialchars($appointment['pt_tracker_id'], ENT_NOQUOTES) ?>"  <?php if ($appointment['drug_screen_completed'] == "1") echo "checked";?>>
    <?php } else { ?>
       <input type=checkbox  class="drug_screen_completed" id='<?php echo htmlspecialchars($appointment['pt_tracker_id'], ENT_NOQUOTES) ?>' name="drug_screen_completed" <?php if ($appointment['drug_screen_completed'] == "1") echo "checked";?>>
    <?php } ?>
    </td>
    <?php } else {  echo "  <td>"; }?>
    <?php } ?>
    </tr>
    <?php
} //end for
?>
</table>
</form>

<script type="text/javascript">
  $(document).ready(function() { 
  // toggle of the check box status for drug screen completed and ajax call to update the database
 $(".drug_screen_completed").change(function() {
      top.restoreSession();
    if (this.checked) {
      testcomplete_toggle="true";
    } else {
      testcomplete_toggle="false";
    }
      $.post( "../../library/ajax/drug_screen_completed.php", {
        trackerid: this.id,
        testcomplete: testcomplete_toggle
      });
    });
  });
</script>
<!-- form used to open a new top level window when a patient row is clicked -->
<form name='fnew' method='post' target='_blank' action='../main/main_screen.php?auth=login&site=<?php echo attr($_SESSION['site_id']); ?>'>
<input type='hidden' name='patientID'      value='0' />
<input type='hidden' name='encounterID'    value='0' />
</form>
</body>
</html>
