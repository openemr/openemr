<?php

/**
 * Holds library functions used by events
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Paul Simon K <paul@zhservices.com>
 * @author    Ian Jardine ( github.com/epsdky ) ( Modified calendar_arrived )
 * @copyright Copyright (c) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

require_once(__DIR__ . '/calendar.inc');
require_once(__DIR__ . '/patient_tracker.inc.php');

//===============================================================================
//This section handles the events of payment screen.
//===============================================================================
define('REPEAT_EVERY_DAY', 0);
define('REPEAT_EVERY_WEEK', 1);
define('REPEAT_EVERY_MONTH', 2);
define('REPEAT_EVERY_YEAR', 3);
define('REPEAT_EVERY_WORK_DAY', 4);
define('REPEAT_DAYS_EVERY_WEEK', 6);
//===============================================================================
$today = date('Y-m-d');
//===============================================================================
// If unique current date appointment found update status to arrived and create
// encounter
//
function calendar_arrived($form_pid)
{
    $appts = array();
    $today = date('Y-m-d');
    $appts = fetchAppointments($today, $today, $form_pid);
    $appt_count = count($appts); //
    if ($appt_count == 0) {
        echo "<br /><br /><br /><h2 class='text-center'>" . htmlspecialchars(xl('Sorry No Appointment is Fixed'), ENT_QUOTES) . ". " . htmlspecialchars(xl('No Encounter could be created'), ENT_QUOTES) . ".</h2>";
        exit;
    } elseif ($appt_count == 1) {
        $enc = todaysEncounterCheck($form_pid);
        if ($appts[0]['pc_recurrtype'] == 0) {
            sqlStatement("UPDATE openemr_postcalendar_events SET pc_apptstatus = '@' WHERE pc_eid = ?", array($appts[0]['pc_eid']));
        } else {
            update_event($appts[0]['pc_eid']);
        }
    } elseif ($appt_count > 1) {
        echo "<br /><br /><br /><h2 class='text-center'>" . htmlspecialchars(xl('More than one appointment was found'), ENT_QUOTES) . ". " . htmlspecialchars(xl('No Encounter could be created'), ENT_QUOTES) . ".</h2>";
        exit;
    }
    return $enc;
}
//
//===============================================================================
// Checks for the patient's encounter ID for today, creating it if there is none.
//
function todaysEncounterCheck($patient_id, $enc_date = '', $reason = '', $fac_id = '', $billing_fac = '', $provider = '', $cat = '', $return_existing = true)
{
    global $today;
    $encounter = todaysEncounterIf($patient_id);
    if ($encounter && (int)$GLOBALS['auto_create_new_encounters'] !== 2) {
        if ($return_existing) {
            return $encounter;
        } else {
            return 0;
        }
    }

    if (is_array($provider)) {
        $visit_provider = (int)$provider[0];
    } elseif ($provider) {
        $visit_provider = (int)$provider;
    } else {
        $visit_provider = '(NULL)';
    }

    $dos = $enc_date ? $enc_date : $today;
    $visit_reason = $reason ? $reason : xl('Please indicate visit reason');
    $tmprow = sqlQuery("SELECT username, facility, facility_id FROM users WHERE id = ?", array($_SESSION["authUserID"]));
    $username = $tmprow['username'];
    $facility = $tmprow['facility'];
    $facility_id = $fac_id ? (int)$fac_id : $tmprow['facility_id'];
    $billing_facility = $billing_fac ? (int)$billing_fac : $tmprow['facility_id'];
    $pos_code = sqlQuery("SELECT pos_code FROM facility WHERE id = ?", array($facility_id))['pos_code'];
    $visit_cat = $cat ? $cat : '(NULL)';
    $conn = $GLOBALS['adodb']['db'];
    $encounter = $conn->GenID("sequences");
    addForm(
        $encounter,
        "New Patient Encounter",
        sqlInsert(
            "INSERT INTO form_encounter SET " .
            "date = ?, " .
            "reason = ?, " .
            "facility = ?, " .
            "facility_id = ?, " .
            "billing_facility = ?, " .
            "provider_id = ?, " .
            "pid = ?, " .
            "encounter = ?," .
            "pc_catid = ?," .
            "pos_code = ?",
            array($dos,$visit_reason,$facility,$facility_id,$billing_facility,$visit_provider,$patient_id,$encounter,$visit_cat, $pos_code)
        ),
        "newpatient",
        $patient_id,
        "1",
        "NOW()",
        $username
    );
    return $encounter;
}

    //===============================================================================
    // Checks for the group's encounter ID for today, creating it if there is none.
    //
function todaysTherapyGroupEncounterCheck($group_id, $enc_date = '', $reason = '', $fac_id = '', $billing_fac = '', $provider = '', $cat = '', $return_existing = true, $eid = null)
{
    global $today;
    $encounter = todaysTherapyGroupEncounterIf($group_id);
    if ($encounter) {
        if ($return_existing) {
            return $encounter;
        } else {
            return 0;
        }
    }

    if (is_array($provider)) {
        $visit_provider = (int)$provider[0];
        $counselors = implode(',', $provider);
    } elseif ($provider) {
        $visit_provider = $counselors = (int)$provider;
    } else {
        $visit_provider = $counselors = null;
    }

    $dos = $enc_date ? $enc_date : $today;
    $visit_reason = $reason ? $reason : xl('Please indicate visit reason');
    $tmprow = sqlQuery("SELECT username, facility, facility_id FROM users WHERE id = ?", array($_SESSION["authUserID"]));
    $username = $tmprow['username'];
    $facility = $tmprow['facility'];
    $facility_id = $fac_id ? (int)$fac_id : $tmprow['facility_id'];
    $billing_facility = $billing_fac ? (int)$billing_fac : $tmprow['facility_id'];
    $visit_cat = $cat ? $cat : '(NULL)';
    $conn = $GLOBALS['adodb']['db'];
    $encounter = $conn->GenID("sequences");
    addForm(
        $encounter,
        "New Therapy Group Encounter",
        sqlInsert(
            "INSERT INTO form_groups_encounter SET " .
            "date = ?, " .
            "reason = ?, " .
            "facility = ?, " .
            "facility_id = ?, " .
            "billing_facility = ?, " .
            "provider_id = ?, " .
            "group_id = ?, " .
            "encounter = ?," .
            "pc_catid = ? ," .
            "appt_id = ? ," .
            "counselors = ? ",
            array($dos,$visit_reason,$facility,$facility_id,$billing_facility,$visit_provider,$group_id,$encounter,$visit_cat, $eid, $counselors)
        ),
        "newGroupEncounter",
        null,
        "1",
        "NOW()",
        $username,
        "",
        $group_id
    );
    return $encounter;
}
//===============================================================================
// Get the patient's encounter ID for today, if it exists.
// In the case of more than one encounter today, pick the last one.
//
function todaysEncounterIf($patient_id)
{
    global $today;
    $tmprow = sqlQuery("SELECT encounter FROM form_encounter WHERE " .
    "pid = ? AND date = ? " .
    "ORDER BY encounter DESC LIMIT 1", array($patient_id,"$today 00:00:00"));
    return empty($tmprow['encounter']) ? 0 : $tmprow['encounter'];
}
//===============================================================================
// Get the group's encounter ID for today, if it exists.
// In the case of more than one encounter today, pick the last one.
//
function todaysTherapyGroupEncounterIf($group_id)
{
    global $today;
    $tmprow = sqlQuery("SELECT encounter FROM form_groups_encounter WHERE " .
        "group_id = ? AND date = ? " .
        "ORDER BY encounter DESC LIMIT 1", array($group_id,"$today 00:00:00"));
    return empty($tmprow['encounter']) ? 0 : $tmprow['encounter'];
}
//===============================================================================

// Get the patient's encounter ID for today, creating it if there is none.
//
function todaysEncounter($patient_id, $reason = '')
{
    global $today, $userauthorized;

    if (empty($reason)) {
        $reason = xl('Please indicate visit reason');
    }

  // Was going to use the existing encounter for today if there is one, but
  // decided it's right to always create a new one.  Leaving the code here
  // (and corresponding function above) in case it is ever wanted later.
  /*******************************************************************
  $encounter = todaysEncounterIf($patient_id);
  if ($encounter) return $encounter;
  *******************************************************************/

    $tmprow = sqlQuery("SELECT username, facility, facility_id FROM users " .
    "WHERE id = ?", array($_SESSION["authUserID"]));
    $username = $tmprow['username'];
    $facility = $tmprow['facility'];
    $facility_id = $tmprow['facility_id'];
    $conn = $GLOBALS['adodb']['db'];
    $encounter = $conn->GenID("sequences");
    $provider_id = $userauthorized ? $_SESSION['authUserID'] : 0;
    addForm(
        $encounter,
        "New Patient Encounter",
        sqlInsert(
            "INSERT INTO form_encounter SET date = ?, onset_date = ?, "  .
            "reason = ?, facility = ?, facility_id = ?, pid = ?, encounter = ?, " .
            "provider_id = ?",
            array($today, $today, $reason, $facility, $facility_id, $patient_id,
            $encounter, $provider_id)
        ),
        "newpatient",
        $patient_id,
        $userauthorized,
        "NOW()",
        $username
    );
    return $encounter;
}
//===============================================================================
// get the original event's repeat specs
function update_event($eid)
{
    $origEventRes = sqlStatement("SELECT * FROM openemr_postcalendar_events WHERE pc_eid = ?", array($eid));
    $origEvent = sqlFetchArray($origEventRes);
    $oldRecurrspec = unserialize($origEvent['pc_recurrspec'], ['allowed_classes' => false]);
    $duration = $origEvent['pc_duration'];
    $starttime = $origEvent['pc_startTime'];
    $endtime = $origEvent['pc_endTime'];
    $selected_date = date("Ymd");
    if ($oldRecurrspec['exdate'] != "") {
        $oldRecurrspec['exdate'] .= "," . $selected_date;
    } else {
        $oldRecurrspec['exdate'] .= $selected_date;
    }

    // mod original event recur specs to exclude this date
        sqlStatement("UPDATE openemr_postcalendar_events SET pc_recurrspec = ? WHERE pc_eid = ?", array(serialize($oldRecurrspec),$eid));
    // specify some special variables needed for the INSERT
  // no recurr specs, this is used for adding a new non-recurring event
        $noRecurrspec = array("event_repeat_freq" => "",
                        "event_repeat_freq_type" => "",
                        "event_repeat_on_num" => "1",
                        "event_repeat_on_day" => "0",
                        "event_repeat_on_freq" => "0",
                        "exdate" => ""
                    );
    // Useless garbage that we must save.
        $locationspecs = array("event_location" => "",
                            "event_street1" => "",
                            "event_street2" => "",
                            "event_city" => "",
                            "event_state" => "",
                            "event_postal" => ""
                        );
        $locationspec = serialize($locationspecs);
        $args['event_date'] = date('Y-m-d');
        $args['duration'] = $duration;
    // this event is forced to NOT REPEAT
        $args['form_repeat'] = "0";
        $args['recurrspec'] = $noRecurrspec;
        $args['form_enddate'] = "0000-00-00";
        $args['starttime'] = $starttime;
        $args['endtime'] = $endtime;
        $args['locationspec'] = $locationspec;
        $args['form_category'] = $origEvent['pc_catid'];
        $args['new_multiple_value'] = $origEvent['pc_multiple'];
        $args['form_provider'] = $origEvent['pc_aid'];
        $args['form_pid'] = $origEvent['pc_pid'];
        $args['form_title'] = $origEvent['pc_title'];
        $args['form_allday'] = $origEvent['pc_alldayevent'];
        $args['form_apptstatus'] = '@';
        $args['form_prefcat'] = $origEvent['pc_prefcatid'];
        $args['facility'] = $origEvent['pc_facility'];
        $args['billing_facility'] = $origEvent['pc_billing_location'];
        InsertEvent($args, 'payment');
}
//===============================================================================
// check if event exists
function check_event_exist($eid)
{
    $origEventRes = sqlStatement("SELECT * FROM openemr_postcalendar_events WHERE pc_eid = ?", array($eid));
    $origEvent = sqlFetchArray($origEventRes);
    $pc_catid = $origEvent['pc_catid'];
    $pc_aid = $origEvent['pc_aid'];
    $pc_pid = $origEvent['pc_pid'];
    $pc_eventDate = date('Y-m-d');
    $pc_startTime = $origEvent['pc_startTime'];
    $pc_endTime = $origEvent['pc_endTime'];
    $pc_facility = $origEvent['pc_facility'];
    $pc_billing_location = $origEvent['pc_billing_location'];
    $pc_recurrspec_array = unserialize($origEvent['pc_recurrspec'], ['allowed_classes' => false]);
    $origEvent = sqlStatement(
        "SELECT * FROM openemr_postcalendar_events WHERE pc_eid != ? and pc_catid=? and pc_aid=? " .
        "and pc_pid=? and pc_eventDate=? and pc_startTime=? and pc_endTime=? and pc_facility=? and pc_billing_location=?",
        array($eid,$pc_catid,$pc_aid,$pc_pid,$pc_eventDate,$pc_startTime,$pc_endTime,$pc_facility,$pc_billing_location)
    );
    if (sqlNumRows($origEvent) > 0) {
        $origEventRow = sqlFetchArray($origEvent);
        return $origEventRow['pc_eid'];
    } else {
        if (strpos($pc_recurrspec_array['exdate'], date('Ymd')) === false) {//;'20110228'
            return false;
        } else {//this happens in delete case
            return true;
        }
    }
}
//===============================================================================
// insert an event
// $args is mainly filled with content from the POST http var
function InsertEvent($args, $from = 'general')
{
    $pc_recurrtype = '0';
    if (!empty($args['form_repeat']) || !empty($args['days_every_week'])) {
        if ($args['recurrspec']['event_repeat_freq_type'] == "6") {
            $pc_recurrtype = 3;
        } else {
            $pc_recurrtype = $args['recurrspec']['event_repeat_on_freq'] ? '2' : '1';
        }
    }

    $form_pid = empty($args['form_pid']) ? '' : $args['form_pid'];
    $form_room = empty($args['form_room']) ? '' : $args['form_room'];
    $form_gid = empty($args['form_gid']) ? '' : $args['form_gid'];
    ;
    if ($from == 'general') {
        $pc_eid = sqlInsert(
            "INSERT INTO openemr_postcalendar_events ( " .
            "pc_catid, pc_multiple, pc_aid, pc_pid, pc_gid, pc_title, pc_time, pc_hometext, " .
            "pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, " .
            "pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, " .
            "pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing, pc_facility,pc_billing_location,pc_room " .
            ") VALUES (?,?,?,?,?,?,NOW(),?,?,?,?,?,?,?,?,?,?,?,?,?,1,1,?,?,?)",
            array($args['form_category'],(isset($args['new_multiple_value']) ? $args['new_multiple_value'] : ''),$args['form_provider'],$form_pid,$form_gid,
            $args['form_title'],$args['form_comments'],$_SESSION['authUserID'],$args['event_date'],
            fixDate($args['form_enddate']),$args['duration'],$pc_recurrtype,serialize($args['recurrspec']),
            $args['starttime'],$args['endtime'],$args['form_allday'],$args['form_apptstatus'],$args['form_prefcat'],
            $args['locationspec'],(int)$args['facility'],(int)$args['billing_facility'],$form_room)
        );

            //Manage tracker status.
        if (!empty($form_pid)) {
            manage_tracker_status($args['event_date'], $args['starttime'], $pc_eid, $form_pid, $_SESSION['authUser'], $args['form_apptstatus'], $args['form_room']);
        }

            $GLOBALS['temporary-eid-for-manage-tracker'] = $pc_eid; //used by manage tracker module to set correct encounter in tracker when check in

            return $pc_eid;
    } elseif ($from == 'payment') {
        sqlStatement(
            "INSERT INTO openemr_postcalendar_events ( " .
            "pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, " .
            "pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, " .
            "pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, " .
            "pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing, pc_facility,pc_billing_location " .
            ") VALUES (?,?,?,?,?,NOW(),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            array($args['form_category'],$args['new_multiple_value'],$args['form_provider'],$form_pid,$args['form_title'],
                $args['event_date'],$args['form_enddate'],$args['duration'],$pc_recurrtype,serialize($args['recurrspec']),
                $args['starttime'],$args['endtime'],$args['form_allday'],$args['form_apptstatus'],$args['form_prefcat'], $args['locationspec'],
            1,
            1,
            (int)$args['facility'],
            (int)$args['billing_facility'])
        );
    }
}
//================================================================================================================
/**
 *  __increment()
 *  returns the next valid date for an event based on the
 *  current day,month,year,freq and type
 *  @private
 *  @returns string YYYY-MM-DD
 */
function &__increment($d, $m, $y, $f, $t)
{
    if ($t == REPEAT_DAYS_EVERY_WEEK) {
        $old_appointment_date = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
        $next_appointment_date = getTheNextAppointment($old_appointment_date, $f);
        return $next_appointment_date;
    }

    if ($t == REPEAT_EVERY_DAY) {
        $d = $d + $f;
    } elseif ($t == REPEAT_EVERY_WORK_DAY) {
        // a workday is defined as Mon,Tue,Wed,Thu,Fri
        // repeating on every or Nth work day means to not include
        // weekends (Sat/Sun) in the increment... tricky

        // ugh, a day-by-day loop seems necessary here, something where
        // we can check to see if the day is a Sat/Sun and increment
        // the frequency count so as to ignore the weekend. hmmmm....
        $orig_freq = $f;
        for ($daycount = 1; $daycount <= $orig_freq; $daycount++) {
            $nextWorkDOW = date('w', mktime(0, 0, 0, $m, ($d + $daycount), $y));
            if (is_weekend_day($nextWorkDOW)) {
                $f++;
            }
        }

        // and finally make sure we haven't landed on a end week days
        // adjust as necessary
        $nextWorkDOW = date('w', mktime(0, 0, 0, $m, ($d + $f), $y));
        if (count($GLOBALS['weekend_days']) === 2) {
            if ($nextWorkDOW == $GLOBALS['weekend_days'][0]) {
                $f += 2;
            } elseif ($nextWorkDOW == $GLOBALS['weekend_days'][1]) {
                $f++;
            }
        } elseif (count($GLOBALS['weekend_days']) === 1 && $nextWorkDOW === $GLOBALS['weekend_days'][0]) {
            $f++;
        }

        $d = $d + $f;
    } elseif ($t == REPEAT_EVERY_WEEK) {
        $d = $d + (7 * $f);
    } elseif ($t == REPEAT_EVERY_MONTH) {
        $m = $m + $f;
    } elseif ($t == REPEAT_EVERY_YEAR) {
        $y = $y + $f;
    }

    $dtYMD = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
    return $dtYMD;
}

function getTheNextAppointment($appointment_date, $freq)
{
    $day_arr = explode(",", $freq);
    $date_arr = array();
    foreach ($day_arr as $day) {
        $day = getDayName($day);
        $date = date('Y-m-d', strtotime("next " . $day, strtotime($appointment_date)));
        array_push($date_arr, $date);
    }

    $next_appointment = getEarliestDate($date_arr);
    return $next_appointment;
}

function getDayName($day_num)
{
    if ($day_num == "1") {
        return "sunday";
    }

    if ($day_num == "2") {
        return "monday";
    }

    if ($day_num == "3") {
        return "tuesday";
    }

    if ($day_num == "4") {
        return "wednesday";
    }

    if ($day_num == "5") {
        return "thursday";
    }

    if ($day_num == "6") {
        return "friday";
    }

    if ($day_num == "7") {
        return "saturday";
    }
}


function getEarliestDate($date_arr)
{
    $earliest = ($date_arr[0]);
    foreach ($date_arr as $date) {
        if (strtotime($date) < strtotime($earliest)) {
            $earliest = $date;
        }
    }

    return $earliest;
}
//================================================================================================================
