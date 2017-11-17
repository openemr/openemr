<?php
/**
 * Patient Tracker (Patient Flow Board)
 *
 * This program displays the information entered in the Calendar program ,
 * allowing the user to change status and view those changed here and in the Calendar
 * Will allow the collection of length of time spent in each status
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Terry Hill <terry@lilysystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
use OpenEMR\Core\Header;

require_once ("../globals.php");
require_once ("$srcdir/patient.inc");
require_once ("$srcdir/options.inc.php");
require_once ("$srcdir/patient_tracker.inc.php");
require_once ("$srcdir/user.inc");

// mdsupport - user_settings prefix
$uspfx = substr(__FILE__, strlen($webserver_root)) . '.';
$setting_new_window = prevSetting($uspfx, 'setting_new_window', 'form_new_window', ' ');

// define variables, future enhancement allow changing the to_date and from_date
// to allow picking a date to review

if (! is_null($_POST['form_provider'])) {
    $provider = $_POST['form_provider'];
} else if ($_SESSION['userauthorized']) {
    $provider = $_SESSION['authUserID'];
} else {
    $provider = null;
}

$facility = ! is_null($_POST['form_facility']) ? $_POST['form_facility'] : null;
$form_apptstatus = ! is_null($_POST['form_apptstatus']) ? $_POST['form_apptstatus'] : null;
$form_apptcat = null;
if (isset($_POST['form_apptcat'])) {
    if ($form_apptcat != "ALL") {
        $form_apptcat = intval($_POST['form_apptcat']);
    }
}

$form_patient_name = ! is_null($_POST['form_patient_name']) ? $_POST['form_patient_name'] : null;
$form_patient_id = ! is_null($_POST['form_patient_id']) ? $_POST['form_patient_id'] : null;

$appointments = array();
$from_date = date("Y-m-d");
$to_date = date("Y-m-d");
$datetime = date("Y-m-d H:i:s");

// go get the information and process it
$appointments = fetch_Patient_Tracker_Events($from_date, $to_date, $provider, $facility, $form_apptstatus, $form_apptcat, $form_patient_name, $form_patient_id);
$appointments = sortAppointments($appointments, 'time');

// grouping of the count of every status
$appointments_status = getApptStatus($appointments);

// Below are new constants for the translation pipeline
// xl('None')
// xl('Reminder done')
// xl('Chart pulled')
// xl('Canceled')
// xl('No show')
// xl('Arrived')
// xl('Arrived late')
// xl('Left w/o visit')
// xl('Ins/fin issue')
// xl('In exam room')
// xl('Checked out')
// xl('Coding done')
// xl('Canceled < 24h')
$lres = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = ? AND activity=1", array(
    'apptstat'
));
while ($lrow = sqlFetchArray($lres)) {
    // if exists, remove the legend character
    if ($lrow['title'][1] == ' ') {
        $splitTitle = explode(' ', $lrow['title']);
        array_shift($splitTitle);
        $title = implode(' ', $splitTitle);
    } else {
        $title = $lrow['title'];
    }
    
    $statuses_list[$lrow['option_id']] = $title;
}

$chk_prov = array(); // list of providers with appointments
                     
// Scan appointments for additional info
foreach ($appointments as $apt) {
    $chk_prov[$apt['uprovider_id']] = $apt['ulname'] . ', ' . $apt['ufname'] . ' ' . $apt['umname'];
}
?>
<!DOCTYPE html>
<html>
<head>
<?php Header::setupHeader();?>
<title><?php echo xlt("Flow Board") ?></title>


<script language="JavaScript">
// Refresh self
function refreshme() {
  top.restoreSession();
  document.pattrk.submit();
}
// popup for patient tracker status
function bpopup(tkid) {
 top.restoreSession()
 dlgopen('../patient_tracker/patient_tracker_status.php?tracker_id=' + tkid, '_blank', 500, 250);
 return false;
}

// popup for calendar add edit
function calendarpopup(eid,date_squash) {
 top.restoreSession()
 dlgopen('../main/calendar/add_edit_event.php?eid=' + eid + '&date=' + date_squash, '_blank', 775, 500);
 return false;
}

// auto refresh screen pat_trkr_timer is the timer variable
function refreshbegin(first){
    <?php if ($GLOBALS['pat_trkr_timer'] != '0') { ?>
    var reftime="<?php echo attr($GLOBALS['pat_trkr_timer']); ?>";
    var parsetime=reftime.split(":");
    parsetime=(parsetime[0]*60)+(parsetime[1]*1)*1000;
    if (first != '1') {
      refreshme();
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
   if (enc > 0) {
     top.RTop.location= "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + newpid + "&set_encounterid=" + enc;
   }
   else {
     top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + newpid;
   }
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
@media only screen and (max-width: 1004px) {
       [class*="col-"] {
       width: 100%;
       text-align:left!Important;
        }
    }
.table>tbody>tr>td, .table>tbody>tr>th, 
.table>tfoot>tr>td, .table>tfoot>tr>th,
.table>thead>tr>td, .table>thead>tr>th {
border: 1px solid #ddd ! Important;
}
.table{
   min-width: 1600px; !Important
   
}
a {
    color:black;
}
</style>

</head>

<?php
if ($GLOBALS['pat_trkr_timer'] == '0') {
    // if the screen is not set up for auto refresh, use standard page call
    $action_page = "patient_tracker.php";
} else {
    // if the screen is set up for auto refresh, this will allow it to be closed by auto logoff
    $action_page = "patient_tracker.php?skip_timeout_reset=1";
}

?>

<body class="body_top">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                 <div class="page-header clearfix">
                   <h2 class="clearfix"><span id='header_text'><?php echo xlt("Flow Board"); ?></span> &nbsp;<a href="#form_filter" data-toggle="collapse"><i class="fa fa-filter fa-2x small" aria-hidden="true" title="<?php echo xla('Show/Hide Filter'); ?>"></i></a><a class="pull-right" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#000000"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
                </div>
            </div>
        </div>
        <div id="form_filter" class="row collapse">
            <div class="col-xs-12">
                <form method='post' name='theform' id='theform'
                    action='<?php echo $action_page; ?>'
                    onsubmit='return top.restoreSession()'>
                    <div id="flow_board_parameters clearfix">
                        <div class="col-xs-12">
                             <div class="form-group clearfix">
                                <div class="text-left">
                                    <div class="btn-group" role="group">
                                        <button class='btn btn-default btn-filter' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'><?php echo xlt('Filter'); ?></button>
                                        <?php if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>
                                        <button  class='btn btn-default btn-print' id='printbutton'><?php echo xlt('Print'); ?></button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <fieldset>
                                <div class="row">
                                    <div class="col-xs-12  oe-custom-line" style="padding-top:10px">
                                        <div class=" col-xs-3">
                                            <label class="control-label" for="form_provider"><?php echo xlt('Provider'); ?>:</label> 
                                            <?php
                                            // Build a drop-down list of providers.
                                            $query = "SELECT id, lname, fname FROM users WHERE " . "authorized = 1  ORDER BY lname, fname"; // (CHEMED) facility filter
                                            $ures = sqlStatement($query);
                                            
                                            echo "   <select name='form_provider' id='form_provider' class='form-control'>\n";
                                            echo "    <option value='ALL'>-- " . xlt('All') . " --\n";
                                            while ($urow = sqlFetchArray($ures)) {
                                                $provid = $urow['id'];
                                                echo "    <option value='" . attr($provid) . "'";
                                                if (isset($_POST['form_provider']) && $provid == $_POST['form_provider']) {
                                                    echo " selected";
                                                } elseif (! isset($_POST['form_provider']) && $_SESSION['userauthorized'] && $provid == $_SESSION['authUserID']) {
                                                    echo " selected";
                                                }
                                                echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                                            }
                                            echo "   </select>\n";
                                            ?>
                                        </div>
                                        <div class=" col-xs-2">
                                            <label class="control-label" for="form_apptstatus"><?php echo xlt('Status'); //status code drop down creation ?>:</label> 
                                            <?php generate_form_field(array('data_type'=>1,'field_id'=>'apptstatus','list_id'=>'apptstat','empty_title'=>'All'), $_POST['form_apptstatus']);?>
                                        </div>
                                        <div class=" col-xs-2">
                                            <label class="control-label" for="form_apptcat"><?php echo xlt('Category') //category drop down creation ?>:</label>
                                            <select id="form_apptcat" name="form_apptcat" class='form-control'>
                                                <?php
                                                $categories = fetchAppointmentCategories();
                                                echo "<option value='ALL'>" . xlt("All") . "</option>";
                                                while ($cat = sqlFetchArray($categories)) {
                                                    echo "<option value='" . attr($cat['id']) . "'";
                                                    if ($cat['id'] == $_POST['form_apptcat']) {
                                                        echo " selected='true' ";
                                                    }
                                                    echo ">" . text(xl_appt_category($cat['category'])) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    
                                        <div class=" col-xs-2">
                                            <label class="control-label" for="form_patient_id"><?php echo xlt('Patient ID') ?>:</label> 
                                            <input type="text" id="patient_id" name="form_patient_id" class="form-control" value="<?php echo ($form_patient_id) ? attr($form_patient_id) : ""; ?>">
                                        </div>
                                        <div class=" col-xs-3">
                                            <label class="control-label" for="patient_name"><?php echo xlt('Patient Name') ?>:</label> 
                                            <input type="text" id="patient_name" name="form_patient_name" class="form-control" value="<?php echo ($form_patient_name) ? attr($form_patient_name) : ""; ?>">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
    </div><!--end of container div-->
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <form name='pattrk' id='pattrk' method='post'
                    action='<?php echo $action_page; ?>'
                    onsubmit='return top.restoreSession()' enctype='multipart/form-data'>

                    <div class="clearfix">
                        <?php if (count($chk_prov) == 1) {?>
                        <h3>
                            <span style='float: left'><?php echo xlt('Appointments for'). ' : '. text(reset($chk_prov)) ?></span>
                        </h3>
                        <?php } ?>
                         <div id='inanewwindow' class='inanewwindow'>
                                        <span style='float: right'> <a id='setting_cog'><i
                                                class="fa fa-cog fa-2x fa-fw">&nbsp;</i></a>
                            <?php // Note that are unable to html escape below $setting_new_window, or else will break the code, secondary to white space issues. ?>
                           <input type='hidden' name='setting_new_window'
                                            id='setting_new_window' value='<?php echo $setting_new_window ?>' />
                                            <label id='settings'><input type='checkbox' name='form_new_window'
                                                id='form_new_window' value='1' <?php echo $setting_new_window ?>>
                                <?php echo xlt('Open Patient in New Window'); ?></input></label>
                                            <a id='refreshme'><i class="fa fa-refresh fa-2x fa-fw">&nbsp;</i></a>
                                        </span>
                        </div>
                    
                    <?php if ($GLOBALS['pat_trkr_timer'] =='0') { ?>
                        <div class="col-xs-12">
                            <button  class='btn btn-default btn-refresh' onclick="document.getElementById('pattrk').submit();"><?php echo xlt('Refresh Screen'); ?></button>
                        </div>
                    <?php } ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="12"><b><small>
                                            <?php
                                            $statuses_output = xlt('Total patients') . ':' . text($appointments_status['count_all']);
                                            unset($appointments_status['count_all']);
                                            foreach ($appointments_status as $status_symbol => $count) {
                                                $statuses_output .= " | " . text(xl_list_label($statuses_list[$status_symbol])) . ":" . $count;
                                            }
                                            echo $statuses_output;
                                            ?>
                                        </small></b>
                                        </th>
                                    </tr>
                                    <tr bgcolor="#cccff">
                                        <?php if ($GLOBALS['ptkr_show_pid']) { ?>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('PID'); ?>
                                        </th>
                                        <?php } ?>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('Patient'); ?>
                                        </th>
                                        <?php if ($GLOBALS['ptkr_visit_reason']) { ?>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('Reason'); ?>
                                        </th>
                                        <?php } ?>
                                        <?php if ($GLOBALS['ptkr_show_encounter']) { ?>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('Encounter'); ?>
                                        </th>
                                        <?php } ?>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('Exam Room #'); ?>
                                        </th>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('Appt Time'); ?>
                                        </th>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('Arrive Time'); ?>
                                        </th>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('Status'); ?>
                                        </th>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('Current Status Time'); ?>
                                        </th>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('Visit Type'); ?>
                                        </th>
                                        <?php if (count($chk_prov) > 1) { ?>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('Provider'); ?>
                                        </th>
                                        <?php } ?>
                                        <th class="dehead" align="center">
                                            <?php  echo xlt('Total Time'); ?>
                                        </th>
                                        <th class="dehead" align="center">
                                        <?php  echo xlt('Check Out Time'); ?>
                                        </th>
                                        <th class="dehead" align="center">
                                            <?php  echo xlt('Updated By'); ?>
                                        </th>
                                        <?php if ($GLOBALS['drug_screen']) { ?>
                                        <th class="dehead" align="center">
                                            <?php  echo xlt('Random Drug Screen'); ?>
                                        </th>
                                        <th class="dehead" align="center">
                                            <?php  echo xlt('Drug Screen Completed'); ?>
                                        </th>
                                        <?php } ?>
                                    </tr>
                            </thead>
                            <tbody>

                                    <?php
                                    foreach ($appointments as $appointment) {
                                        // Collect appt date and set up squashed date for use below
                                        $date_appt = $appointment['pc_eventDate'];
                                        $date_squash = str_replace("-", "", $date_appt);
                                        
                                        // Collect variables and do some processing
                                        $docname = $chk_prov[$appointment['uprovider_id']];
                                        if (strlen($docname) <= 3) {
                                            continue;
                                        }
                                        
                                        $ptname = $appointment['lname'] . ', ' . $appointment['fname'] . ' ' . $appointment['mname'];
                                        $appt_enc = $appointment['encounter'];
                                        $appt_eid = (! empty($appointment['eid'])) ? $appointment['eid'] : $appointment['pc_eid'];
                                        $appt_pid = (! empty($appointment['pid'])) ? $appointment['pid'] : $appointment['pc_pid'];
                                        if ($appt_pid == 0) {
                                            continue; // skip when $appt_pid = 0, since this means it is not a patient specific appt slot
                                        }
                                        
                                        $status = (! empty($appointment['status'])) ? $appointment['status'] : $appointment['pc_apptstatus'];
                                        $appt_room = (! empty($appointment['room'])) ? $appointment['room'] : $appointment['pc_room'];
                                        $appt_time = (! empty($appointment['appttime'])) ? $appointment['appttime'] : $appointment['pc_startTime'];
                                        $tracker_id = $appointment['id'];
                                        // reason for visit
                                        if ($GLOBALS['ptkr_visit_reason']) {
                                            $reason_visit = $appointment['pc_hometext'];
                                        }
                                        
                                        $newarrive = collect_checkin($tracker_id);
                                        $newend = collect_checkout($tracker_id);
                                        $colorevents = (collectApptStatusSettings($status));
                                        $bgcolor = $colorevents['color'];
                                        $statalert = $colorevents['time_alert'];
                                        // process the time to allow items with a check out status to be displayed
                                        if (is_checkout($status) && ($GLOBALS['checkout_roll_off'] > 0)) {
                                            $to_time = strtotime($newend);
                                            $from_time = strtotime($datetime);
                                            $display_check_out = round(abs($from_time - $to_time) / 60, 0);
                                            if ($display_check_out >= $GLOBALS['checkout_roll_off']) {
                                                continue;
                                            }
                                        }
                                        ?>
                                    <tr bgcolor='<?php echo $bgcolor ?>'>
                                        <?php if ($GLOBALS['ptkr_show_pid']) { ?>
                                        <td class="detail" align="center">
                                        <?php echo text($appt_pid) ?>
                                        </td>
                                        <?php } ?>
                                        <td class="detail" align="center">
                                            <a href="#" onclick="return topatient('<?php echo attr($appt_pid);?>','<?php echo attr($appt_enc);?>')">
                                            <?php echo text($ptname); ?></a>
                                        </td>
                                                    <!-- reason -->
                                        <?php if ($GLOBALS['ptkr_visit_reason']) { ?>
                                        <td class="detail" align="center">
                                            <?php echo text($reason_visit) ?>
                                        </td>
                                        <?php } ?>
                                        <?php if ($GLOBALS['ptkr_show_encounter']) { ?>
                                        <td class="detail" align="center">
                                            <?php
                                            if ($appt_enc != 0) {
                                                    echo text($appt_enc);
                                                }
                                                ?></a>
                                        </td>
                                        <?php } ?>
                                        <td class="detail" align="center">
                                            <?php echo getListItemTitle('patient_flow_board_rooms', $appt_room);?>
                                        </td>
                                        <td class="detail" align="center">
                                            <?php echo oeFormatTime($appt_time) ?>
                                        </td>
                                        <td class="detail" align="center">
                                            <?php echo ($newarrive ? oeFormatTime($newarrive) : '&nbsp;') ?>
                                        </td>
                                        <td class="detail" align="center">
                                            <?php if (empty($tracker_id)) { #for appt not yet with tracker id and for recurring appt ?>
                                                    <a href="" onclick="return calendarpopup(<?php echo attr($appt_eid).",".attr($date_squash); # calls popup for add edit calendar event?>)">
                                                <?php } else { ?>
                                                    <a href="" onclick="return bpopup(<?php echo attr($tracker_id); # calls popup for patient tracker status?>)">
                                                <?php } ?>
                                            <?php echo text(getListItemTitle("apptstat", $status)); # drop down list for appointment status?>
                                            </a>
                                                    
                                        </td>
                                        <?php
                                        // time in current status
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
                                        
                                        $timecheck = round(abs($to_time - $from_time) / 60, 0);
                                        if ($timecheck >= $statalert && ($statalert != '0')) { // Determine if the time in status limit has been reached.
                                            echo "<td align='center' class='js-blink-infinite'>    "; // and if so blink
                                        } else {
                                            echo "<td align='center' class='detail'> "; // and if not do not blink
                                        }
                                        
                                        if (($yestime == '1') && ($timecheck >= 1) && (strtotime($newarrive) != '')) {
                                            echo text($timecheck . ' ' . ($timecheck >= 2 ? xl('minutes') : xl('minute')));
                                        }
                                        
                                        // end time in current status
                                        ?>
                                        </td>
                                        <td class="detail" align="center">
                                            <?php echo text(xl_appt_category($appointment['pc_title'])) ?>
                                        </td>
                                        <?php if (count($chk_prov) > 1) { ?>
                                            <td class="detail" align="center">
                                                <?php echo text($docname); ?>
                                            </td>
                                                <?php } ?>
                                        <td class="detail" align="center">
                                            <?php
                                            
                                            // total time in practice
                                            if (strtotime($newend) != '') {
                                                $from_time = strtotime($newarrive);
                                                $to_time = strtotime($newend);
                                            } else {
                                                $from_time = strtotime($newarrive);
                                                $to_time = strtotime(date("Y-m-d H:i:s"));
                                            }
                                            
                                            $timecheck2 = round(abs($to_time - $from_time) / 60, 0);
                                            if (strtotime($newarrive) != '' && ($timecheck2 >= 1)) {
                                                echo text($timecheck2 . ' ' . ($timecheck2 >= 2 ? xl('minutes') : xl('minute')));
                                            }
                                            
                                            // end total time in practice
                                            ?>
                                            <?php echo text($appointment['pc_time']); ?>
                                        </td>
                                        <td class="detail" align="center">
                                            <?php
                                            if (strtotime($newend) != '') {
                                                echo oeFormatTime($newend);
                                            }
                                            ?>
                                        </td>
                                        <td class="detail" align="center">
                                            <?php echo text($appointment['user']) ?>
                                        </td>
                                        <?php if ($GLOBALS['drug_screen']) { ?>
                                            <?php if (strtotime($newarrive) != '') { ?>
                                            <td class="detail" align="center">
                                                <?php
                                                if (text($appointment['random_drug_test']) == '1') {
                                                            echo xl('Yes');
                                                        } else {
                                                            echo xl('No');
                                                        }
                                                        ?>
                                                     </td>
                                            <?php
                                                    
                                            } else {
                                                echo "  <td>";
                                            }
                                            ?>
                                            <?php if (strtotime($newarrive) != '' && $appointment['random_drug_test'] == '1') { ?>
                                                <td class="detail" align="center">
                                                        <?php if (strtotime($newend) != '') { # the following block allows the check box for drug screens to be disabled once the status is check out ?>
                                                         <input type=checkbox disabled='disable'
                                                                class="drug_screen_completed"
                                                                id="<?php echo htmlspecialchars($appointment['pt_tracker_id'], ENT_NOQUOTES) ?>"
                                                                <?php
                                                            
                                                    if ($appointment['drug_screen_completed'] == "1") {
                                                                        echo "checked";
                                                                    }
                                                                    ?>>
                                                                <?php } else { ?>
                                                                 <input type=checkbox class="drug_screen_completed"
                                                                        id='<?php echo htmlspecialchars($appointment['pt_tracker_id'], ENT_NOQUOTES) ?>'
                                                                        name="drug_screen_completed"
                                                                        <?php
                                                                    
                                                    if ($appointment['drug_screen_completed'] == "1") {
                                                                        echo "checked";
                                                                    }
                                                                    ?>>
                                                                <?php } ?>
                                                </td>
                                                        <?php
                                                    
                                            } else {
                                                        echo "  <td>";
                                                    }
                                                    ?>
                                        <?php } ?>
                                    </tr>
                                            <?php
                                    } // end for
                                    ?>

                                    <?php
                                    // saving the filter for auto refresh
                                    if (! is_null($_POST['form_provider'])) {
                                        echo "<input type='hidden' name='form_provider' value='" . attr($_POST['form_provider']) . "'>";
                                    }

                                    if (! is_null($_POST['form_facility'])) {
                                        echo "<input type='hidden' name='form_facility' value='" . attr($_POST['form_facility']) . "'>";
                                    }

                                    if (! is_null($_POST['form_apptstatus'])) {
                                        echo "<input type='hidden' name='form_apptstatus' value='" . attr($_POST['form_apptstatus']) . "'>";
                                    }

                                    if (! is_null($_POST['form_apptcat'])) {
                                        echo "<input type='hidden' name='form_apptcat' value='" . attr($_POST['form_apptcat']) . "'>";
                                    }

                                    if (! is_null($_POST['form_patient_id'])) {
                                        echo "<input type='hidden' name='form_patient_id' value='" . attr($_POST['form_patient_id']) . "'>";
                                    }

                                    if (! is_null($_POST['form_patient_name'])) {
                                        echo "<input type='hidden' name='form_patient_name' value='" . attr($_POST['form_patient_name']) . "'>";
                                    }
                                    ?>
                            </tbody>
                        </table>
                    </div>
                            
                </form>
            </div>
        </div>
    </div><!--End of container-fluid div -->
     <div class="row">
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content oe-modal-content">
                    <div class="modal-header clearfix"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:#000000; font-size:1.5em;">×</span></button></div>
                    <div class="modal-body">
                        <iframe src="" id="targetiframe" style="height:650px; width:100%; overflow-x: hidden; border:none" allowtransparency="true"></iframe>  
                    </div>
                    <div class="modal-footer" style="margin-top:0px;">
                       <button class="btn btn-link btn-cancel pull-right" data-dismiss="modal" type="button"><?php echo xlt('close'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#help-href').click (function(){
                <?php
                if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443){
                    echo "alert ('". xlt('Your main page was loaded over HTTPS but you are requesting an insecure resource over HTTP. This request has been blocked, the content must be served over HTTPS.')."');";
                    echo "return;";
                } else {
                    echo "document.getElementById('targetiframe').src = 'http://www.open-emr.org/wiki/index.php/Patient_Flow_Board';";
                }
                ?>
            })
        }); 
    </script>
    <script type="text/javascript">
  $(document).ready(function() {
      $('#settings').css("display","none");
      refreshbegin('1');

    $('.js-blink-infinite').each(function() {
      // set up blinking text
      var elem = $(this);
      setInterval(function() {
        if (elem.css('visibility') == 'hidden') {
          elem.css('visibility', 'visible');
        } else {
          elem.css('visibility', 'hidden');
        }
      }, 500);
    });

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

  // mdsupport - Immediately post changes to form_new_window
  $('#form_new_window').click(function () {
    $('#setting_new_window').val(this.checked ? ' checked' : ' ');
    $.post( "<?php echo basename(__FILE__) ?>", {
      data: $('form#pattrk').serialize(),
      success: function (data) {}
    });
  });

  $('#setting_cog').click(function () {
      $(this).css("display","none");
      $('#settings').css("display","inline");
  });

  $('#refreshme').click(function () {
      refreshme();
  });
</script>
    <!-- form used to open a new top level window when a patient row is clicked -->
    <form name='fnew' method='post' target='_blank'
        action='../main/main_screen.php?auth=login&site=<?php echo attr($_SESSION['site_id']); ?>'>
        <input type='hidden' name='patientID' value='0' /> <input
            type='hidden' name='encounterID' value='0' />
    </form>
</body>
</html>
