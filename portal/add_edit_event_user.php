<?php

/**
 *
 * Modified from interface/main/calendar/add_edit_event.php for
 * the patient portal.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (C) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../vendor/autoload.php");
$session = SessionWrapperFactory::getInstance()->getWrapper();
$globalsBag = OEGlobalsBag::getInstance();

require_once("./../library/pnotes.inc.php");

//landing page definition -- where to go if something goes wrong
$landingpage = "index.php?site=" . urlencode((string) $session->get('site_id'));
//

// kick out if patient not authenticated
if ($session->isSymfonySession() && !empty($session->get('pid')) && !empty($session->get('patient_portal_onsite_two'))) {
    $pid = $session->get('pid');
} else {
    SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit;
}

$ignoreAuth_onsite_portal = true;
global $ignoreAuth_onsite_portal;
$srcdir = $globalsBag->getString('srcdir');
require_once("../interface/globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/appointments.inc.php");

use OpenEMR\Services\AppointmentService;

// Things that might be passed by our opener.
//
$eid = intval($_GET['eid'] ?? 0);         // only for existing events
$date = $_GET['date'] ?? null;        // this and below only for new events
$userid = $_GET['userid'] ?? null;
$default_catid = ($_GET['catid'] ?? null) ? $_GET['catid'] : '5';
$patientid = $_GET['patid'] ?? null;
//

// did someone tamper with eid?
$checkEidInAppt = false;
$patient_appointments = fetchAppointments('1970-01-01', '2382-12-31', $pid);
$checkEidInAppt = array_search($eid, array_column($patient_appointments, 'pc_eid'));

if ($eid !== 0 && $checkEidInAppt === false) {
    echo xlt("Error: Invalid Event ID");
    exit();
}

if (!empty($_POST['form_pid'])) {
    if ($_POST['form_pid'] != $pid) {
        echo xlt("Error: Invalid Patient ID");
        exit();
    }
    $event_date = fixDate($_POST['form_date']);
    if (! getAvailableSlots($event_date, date('Y-m-d', strtotime("+1 year " . $event_date)), $_POST['form_provider_ae'])) {
        echo xlt("Error: No available slots for selected provider(s)");
        exit();
    }

    $appointment_service = (new AppointmentService())->getOneCalendarCategory($_POST['form_category']);
    if (($_POST['form_duration'] * 60) != ($appointment_service[0]['pc_duration'])) {
        echo xlt("Error: Invalid appointment duration for selected category");
        exit();
    }
}

$date = $date ? substr((string) $date, 0, 4) . '-' . substr((string) $date, 4, 2) . '-' . substr((string) $date, 6) : date("Y-m-d");
// internationalize the date
$date = oeFormatShortDate($date);

//
$starttimem = '00';
if (isset($_GET['starttimem'])) {
    $starttimem = substr('00' . $_GET['starttimem'], -2);
}

//
if (isset($_GET['starttimeh'])) {
    $starttimeh = $_GET['starttimeh'];
    if (isset($_GET['startampm'])) {
        if ($_GET['startampm'] == '2' && $starttimeh < 12) {
            $starttimeh += 12;
        }
    }
} else {
    $starttimeh = date("G");
}

$startampm = '';

$info_msg = "";

// EVENTS TO FACILITIES (lemonsoftware)
//(CHEMED) get facility name
// edit event case - if there is no association made, then insert one with the first facility
if ($eid !== 0) {
    $selfacil = '';
    $facility = sqlQuery("SELECT pc_facility, pc_multiple, pc_aid, facility.name
                        FROM openemr_postcalendar_events
                          LEFT JOIN facility ON (openemr_postcalendar_events.pc_facility = facility.id)
                          WHERE pc_eid = ?", [$eid]);
    if (!$facility['pc_facility']) {
        $qmin = sqlQuery("SELECT facility_id as minId, facility FROM users WHERE id = ?", [$facility['pc_aid']]);
        $min = $qmin['minId'];
        $min_name = $qmin['facility'];

        // multiple providers case
        if ($globalsBag->get('select_multi_providers')) {
            $mul = $facility['pc_multiple'];
            sqlStatement("UPDATE openemr_postcalendar_events SET pc_facility = ? WHERE pc_multiple = ?", [$min, $mul]);
        }

        // EOS multiple

        sqlStatement("UPDATE openemr_postcalendar_events SET pc_facility = ? WHERE pc_eid = ?", [$min, $eid]);
        $e2f = $min;
        $e2f_name = $min_name;
    } else {
        $e2f = $facility['pc_facility'];
        $e2f_name = $facility['name'];
    }
}

// EOS E2F
// ===========================


// If we are saving, then save and close the window.
//
if (($_POST['form_action'] ?? null) == "save") {
//print_r($_POST);
//exit();
    $event_date = fixDate($_POST['form_date']);

// Compute start and end time strings to be saved.
    if ($_POST['form_allday'] ?? null) {
        $tmph = 0;
        $tmpm = 0;
        $duration = 24 * 60;
    } else {
        $tmph = $_POST['form_hour'] + 0;
        $tmpm = $_POST['form_minute'] + 0;
        if ($_POST['form_ampm'] == '2' && $tmph < 12) {
            $tmph += 12;
        }

        $duration = $_POST['form_duration'];
    }

    $starttime = "$tmph:$tmpm:00";
//
    $tmpm += $duration;
    while ($tmpm >= 60) {
        $tmpm -= 60;
        ++$tmph;
    }

    $endtime = "$tmph:$tmpm:00";

// Useless garbage that we must save.
    $locationspec = 'a:6:{s:14:"event_location";N;s:13:"event_street1";N;' .
        's:13:"event_street2";N;s:10:"event_city";N;s:11:"event_state";N;s:12:"event_postal";N;}';

// More garbage, but this time 1 character of it is used to save the
// repeat type.
    if ($_POST['form_repeat'] ?? null) {
        $recurrspec = 'a:5:{' .
            's:17:"event_repeat_freq";s:1:"' . $_POST['form_repeat_freq'] . '";' .
            's:22:"event_repeat_freq_type";s:1:"' . $_POST['form_repeat_type'] . '";' .
            's:19:"event_repeat_on_num";s:1:"1";' .
            's:19:"event_repeat_on_day";s:1:"0";' .
            's:20:"event_repeat_on_freq";s:1:"0";}';
    } else {
        $recurrspec = 'a:5:{' .
            's:17:"event_repeat_freq";N;' .
            's:22:"event_repeat_freq_type";s:1:"0";' .
            's:19:"event_repeat_on_num";s:1:"1";' .
            's:19:"event_repeat_on_day";s:1:"0";' .
            's:20:"event_repeat_on_freq";s:1:"1";}';
    }

//The modification of the start date for events that take place on one day of the week
//for example monday, or thursday. We set the start date on the first day of the week
//that the event is scheduled. For example if you set the event to repeat on each monday
//the start date of the event will be set on the first monday after the day the event is scheduled
    if (($_POST['form_repeat_type'] ?? null) == 5) {
        $exploded_date = explode("-", (string) $event_date);
        $edate = date("D", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2], $exploded_date[0]));
        if ($edate == "Tue") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 6, $exploded_date[0]));
        } elseif ($edate == "Wed") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 5, $exploded_date[0]));
        } elseif ($edate == "Thu") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 4, $exploded_date[0]));
        } elseif ($edate == "Fri") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 3, $exploded_date[0]));
        } elseif ($edate == "Sat") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 2, $exploded_date[0]));
        } elseif ($edate == "Sun") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 1, $exploded_date[0]));
        }
    } elseif (($_POST['form_repeat_type'] ?? null) == 6) {
        $exploded_date = explode("-", (string) $event_date);
        $edate = date("D", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2], $exploded_date[0]));
        if ($edate == "Wed") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 6, $exploded_date[0]));
        } elseif ($edate == "Thu") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 5, $exploded_date[0]));
        } elseif ($edate == "Fri") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 4, $exploded_date[0]));
        } elseif ($edate == "Sat") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 3, $exploded_date[0]));
        } elseif ($edate == "Sun") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 2, $exploded_date[0]));
        } elseif ($edate == "Mon") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 1, $exploded_date[0]));
        }
    } elseif (($_POST['form_repeat_type'] ?? null) == 7) {
        $exploded_date = explode("-", (string) $event_date);
        $edate = date("D", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2], $exploded_date[0]));
        if ($edate == "Thu") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 6, $exploded_date[0]));
        } elseif ($edate == "Fri") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 5, $exploded_date[0]));
        } elseif ($edate == "Sat") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 4, $exploded_date[0]));
        } elseif ($edate == "Sun") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 3, $exploded_date[0]));
        } elseif ($edate == "Mon") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 2, $exploded_date[0]));
        } elseif ($edate == "Tue") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 1, $exploded_date[0]));
        }
    } elseif (($_POST['form_repeat_type'] ?? null) == 8) {
        $exploded_date = explode("-", (string) $event_date);
        $edate = date("D", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2], $exploded_date[0]));
        if ($edate == "Fri") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 6, $exploded_date[0]));
        } elseif ($edate == "Sat") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 5, $exploded_date[0]));
        } elseif ($edate == "Sun") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 4, $exploded_date[0]));
        } elseif ($edate == "Mon") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 3, $exploded_date[0]));
        } elseif ($edate == "Tue") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 2, $exploded_date[0]));
        } elseif ($edate == "Wed") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 1, $exploded_date[0]));
        }
    } elseif (($_POST['form_repeat_type'] ?? null) == 9) {
        $exploded_date = explode("-", (string) $event_date);
        $edate = date("D", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2], $exploded_date[0]));
        if ($edate == "Sat") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 6, $exploded_date[0]));
        } elseif ($edate == "Sun") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 5, $exploded_date[0]));
        } elseif ($edate == "Mon") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 4, $exploded_date[0]));
        } elseif ($edate == "Tue") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 3, $exploded_date[0]));
        } elseif ($edate == "Wed") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 2, $exploded_date[0]));
        } elseif ($edate == "Thu") {
            $event_date = date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2] + 1, $exploded_date[0]));
        }
    }//if end
    /* =======================================================
    //                                  UPDATE EVENTS
    ========================================================*/
    if ($eid !== 0) {
        // what is multiple key around this $eid?
        $row = sqlQuery("SELECT pc_multiple FROM openemr_postcalendar_events WHERE pc_eid = ?", [$eid]);

        if ($globalsBag->get('select_multi_providers') && $row['pc_multiple']) {
            /* ==========================================
            // multi providers BOS
            ==========================================*/

            // obtain current list of providers regarding the multiple key
            $up = sqlStatement("SELECT pc_aid FROM openemr_postcalendar_events WHERE pc_multiple = ?", [$row['pc_multiple']]);
            while ($current = sqlFetchArray($up)) {
                $providers_current[] = $current['pc_aid'];
            }

            $providers_new = $_POST['form_provider_ae'];

            // this difference means that some providers from current was UNCHECKED
            // so we must delete this event for them
            $r1 = array_diff($providers_current, $providers_new);
            if (count($r1)) {
                foreach ($r1 as $to_be_removed) {
                    sqlQuery("DELETE FROM openemr_postcalendar_events WHERE pc_aid = ? AND pc_multiple = ?", [$to_be_removed, $row['pc_multiple']]);
                }
            }

            // this difference means that some providers was added
            // so we must insert this event for them
            $r2 = array_diff($providers_new, $providers_current);
            if (count($r2)) {
                foreach ($r2 as $to_be_inserted) {
                    sqlStatement("INSERT INTO openemr_postcalendar_events ( pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing, pc_facility)
            VALUES ( " .
                        "'" . add_escape_custom($_POST['form_category']) . "', " .
                        "'" . add_escape_custom($row['pc_multiple']) . "', " .
                        "'" . add_escape_custom($to_be_inserted) . "', " .
                        "'" . add_escape_custom($pid) . "', " .
                        "'" . add_escape_custom($_POST['form_title']) . "', " .
                        "NOW(), " .
                        "'" . add_escape_custom($_POST['form_comments']) . "', " .
                        "'" . add_escape_custom($session->get('providerId')) . "', " .
                        "'" . add_escape_custom($event_date) . "', " .
                        "'" . add_escape_custom(fixDate($_POST['form_enddate'])) . "', " .
                        "'" . add_escape_custom(($duration * 60)) . "', " .
                        "'" . ($_POST['form_repeat'] ? '1' : '0') . "', " .
                        "'" . add_escape_custom($recurrspec) . "', " .
                        "'" . add_escape_custom($starttime) . "', " .
                        "'" . add_escape_custom($endtime) . "', " .
                        "'" . add_escape_custom($_POST['form_allday']) . "', " .
                        "'" . add_escape_custom($_POST['form_apptstatus']) . "', " .
                        "'" . add_escape_custom($_POST['form_prefcat']) . "', " .
                        "'" . add_escape_custom($locationspec) . "', " .
                        "1, " .
                        "1, " . (int)$_POST['facility'] . " )"); // FF stuff
                } // foreach
            } //if count


            // after the two diffs above, we must update for remaining providers
            // those who are intersected in $providers_current and $providers_new
            foreach ($_POST['form_provider_ae'] as $provider) {
                sqlStatement("UPDATE openemr_postcalendar_events SET " .
                    "pc_catid = '" . add_escape_custom($_POST['form_category']) . "', " .
                    "pc_pid = '" . add_escape_custom($pid) . "', " .
                    "pc_title = '" . add_escape_custom($_POST['form_title']) . "', " .
                    "pc_time = NOW(), " .
                    "pc_hometext = '" . add_escape_custom($_POST['form_comments']) . "', " .
                    "pc_informant = '" . add_escape_custom($session->get('providerId')) . "', " .
                    "pc_eventDate = '" . add_escape_custom($event_date) . "', " .
                    "pc_endDate = '" . add_escape_custom(fixDate($_POST['form_enddate'])) . "', " .
                    "pc_duration = '" . add_escape_custom(($duration * 60)) . "', " .
                    "pc_recurrtype = '" . ($_POST['form_repeat'] ? '1' : '0') . "', " .
                    "pc_recurrspec = '" . add_escape_custom($recurrspec) . "', " .
                    "pc_startTime = '" . add_escape_custom($starttime) . "', " .
                    "pc_endTime = '" . add_escape_custom($endtime) . "', " .
                    "pc_alldayevent = '" . add_escape_custom($_POST['form_allday']) . "', " .
                    "pc_apptstatus = '" . add_escape_custom($_POST['form_apptstatus']) . "', " .
                    "pc_prefcatid = '" . add_escape_custom($_POST['form_prefcat']) . "', " .
                    "pc_facility = '" . (int)$_POST['facility'] . "' " . // FF stuff
                    "WHERE pc_aid = '" . add_escape_custom($provider) . "' AND pc_multiple='" . add_escape_custom($row['pc_multiple']) . "'");
            } // foreach

            /* ==========================================
          // multi providers EOS
            ==========================================*/
        } elseif (!$row['pc_multiple']) {
            $prov = $globalsBag->get('select_multi_providers') ? $_POST['form_provider_ae'][0] : $_POST['form_provider_ae'];
            $insert = false;
            // simple provider case
            sqlStatement("UPDATE openemr_postcalendar_events SET " .
                "pc_catid = '" . add_escape_custom($_POST['form_category']) . "', " .
                "pc_aid = '" . add_escape_custom($prov) . "', " .
                "pc_pid = '" . add_escape_custom($pid) . "', " .
                "pc_title = '" . add_escape_custom($_POST['form_title']) . "', " .
                "pc_time = NOW(), " .
                "pc_hometext = '" . add_escape_custom($_POST['form_comments']) . "', " .
                "pc_informant = '" . add_escape_custom($session->get('providerId')) . "', " .
                "pc_eventDate = '" . add_escape_custom($event_date) . "', " .
                "pc_endDate = '" . add_escape_custom(fixDate($_POST['form_enddate'] ?? '')) . "', " .
                "pc_duration = '" . add_escape_custom(($duration * 60)) . "', " .
                "pc_recurrtype = '" . (($_POST['form_repeat'] ?? null) ? '1' : '0') . "', " .
                "pc_recurrspec = '" . add_escape_custom($recurrspec) . "', " .
                "pc_startTime = '" . add_escape_custom($starttime) . "', " .
                "pc_endTime = '" . add_escape_custom($endtime) . "', " .
                "pc_alldayevent = '" . add_escape_custom(($_POST['form_allday'] ?? '')) . "', " .
                "pc_apptstatus = '" . add_escape_custom($_POST['form_apptstatus']) . "', " .
                "pc_prefcatid = '" . add_escape_custom(($_POST['form_prefcat'] ?? '')) . "', " .
                "pc_facility = '" . (int)($_POST['facility'] ?? null) . "' " . // FF stuff
                "WHERE pc_eid = '" . add_escape_custom($eid) . "'");
        }

        // =======================================
        // EOS multi providers case
        // =======================================

        // EVENTS TO FACILITIES

        $e2f = (int)$eid;

        /* =======================================================
      //                                  INSERT EVENTS
        ========================================================*/
    } else {
        // =======================================
        // multi providers case
        // =======================================

        if (is_array($_POST['form_provider_ae'])) {
            // obtain the next available unique key to group multiple providers around some event
            $q = sqlStatement("SELECT MAX(pc_multiple) as max FROM openemr_postcalendar_events");
            $max = sqlFetchArray($q);
            $new_multiple_value = $max['max'] + 1;

            foreach ($_POST['form_provider_ae'] as $provider) {
                sqlStatement("INSERT INTO openemr_postcalendar_events ( " .
                    "pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, " .
                    "pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, " .
                    "pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, " .
                    "pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing, pc_facility " .
                    ") VALUES ( " .
                    "'" . add_escape_custom($_POST['form_category']) . "', " .
                    "'" . add_escape_custom($new_multiple_value) . "', " .
                    "'" . add_escape_custom($provider) . "', " .
                    "'" . add_escape_custom($pid) . "', " .
                    "'" . add_escape_custom($_POST['form_title']) . "', " .
                    "NOW(), " .
                    "'" . add_escape_custom($_POST['form_comments']) . "', " .
                    "'" . add_escape_custom($session->get('providerId')) . "', " .
                    "'" . add_escape_custom($event_date) . "', " .
                    "'" . add_escape_custom(fixDate($_POST['form_enddate'])) . "', " .
                    "'" . add_escape_custom(($duration * 60)) . "', " .
                    "'" . ($_POST['form_repeat'] ? '1' : '0') . "', " .
                    "'" . add_escape_custom($recurrspec) . "', " .
                    "'" . add_escape_custom($starttime) . "', " .
                    "'" . add_escape_custom($endtime) . "', " .
                    "'" . add_escape_custom($_POST['form_allday']) . "', " .
                    "'" . add_escape_custom($_POST['form_apptstatus']) . "', " .
                    "'" . add_escape_custom($_POST['form_prefcat']) . "', " .
                    "'" . add_escape_custom($locationspec) . "', " .
                    "1, " .
                    "1, " . (int)$_POST['facility'] . " )"); // FF stuff
            } // foreach
        } else {
            $_POST['form_apptstatus'] = '^';
            $insert = true;
            sqlStatement("INSERT INTO openemr_postcalendar_events ( " .
                "pc_catid, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, " .
                "pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, " .
                "pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, " .
                "pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing, pc_facility " .
                ") VALUES ( " .
                "'" . add_escape_custom($_POST['form_category']) . "', " .
                "'" . add_escape_custom($_POST['form_provider_ae']) . "', " .
                "'" . add_escape_custom($pid) . "', " .
                "'" . add_escape_custom($_POST['form_title']) . "', " .
                "NOW(), " .
                "'" . add_escape_custom($_POST['form_comments']) . "', " .
                "'" . add_escape_custom($session->get('providerId')) . "', " .
                "'" . add_escape_custom($event_date) . "', " .
                "'" . add_escape_custom(fixDate(($_POST['form_enddate'] ?? ''))) . "', " .
                "'" . add_escape_custom(($duration * 60)) . "', " .
                "'" . (($_POST['form_repeat'] ?? null) ? '1' : '0') . "', " .
                "'" . add_escape_custom($recurrspec) . "', " .
                "'" . add_escape_custom($starttime) . "', " .
                "'" . add_escape_custom($endtime) . "', " .
                "'" . add_escape_custom(($_POST['form_allday'] ?? '')) . "', " .
                "'" . add_escape_custom($_POST['form_apptstatus']) . "', " .
                "'" . add_escape_custom(($_POST['form_prefcat'] ?? null)) . "', " .
                "'" . add_escape_custom($locationspec) . "', " .
                "1, " .
                "1, " . (int)($_POST['facility'] ?? null) . ")"); // FF stuff
        } // INSERT single
    } // else - insert
} elseif (($_POST['form_action'] ?? null) == "delete") {
// =======================================
//  multi providers case
// =======================================
    if ($globalsBag->get('select_multi_providers')) {
        // what is multiple key around this $eid?
        $row = sqlQuery("SELECT pc_multiple FROM openemr_postcalendar_events WHERE pc_eid = ?", [$eid]);
        if ($row['pc_multiple']) {
            sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_multiple = ?", [$row['pc_multiple']]);
        } else {
            sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?", [$eid]);
        }

        // =======================================
        //  EOS multi providers case
        // =======================================
    } else {
        sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?", [$eid]);
    }
}

if (!empty($_POST['form_action'])) {
    // Leave
    $type = $insert ? xl("A New Appointment") : xl("An Updated Appointment");
    $note = $type . " " . xl("request was received from portal patient") . " ";
    $note .= $session->get('ptName') . " " . xl("regarding appointment dated") . " " . $event_date . " " . $starttime . ". ";
    $note .= !empty($_POST['form_comments']) ? (xl("Reason") . " " . $_POST['form_comments']) : "";
    $note .= ". " . xl("Use Portal Dashboard to confirm with patient.");
    $title = xl("Patient Reminders");
    $user = sqlQueryNoLog("SELECT users.username FROM users WHERE authorized = 1 And id = ?", [$_POST['form_provider_ae']]);
    $rtn = addPnote($pid, $note, 1, 1, $title, $user['username'], '', 'New');

    $session->set('whereto', '#appointmentcard');
    header('Location:./home.php');
    exit();
}

// If we get this far then we are displaying the form.

$statuses = [
    '-' => '',
    '*' => xl('* Reminder done'),
    '+' => xl('+ Chart pulled'),
    'x' => xl('x Cancelled'), // added Apr 2008 by JRM
    '?' => xl('? No show'),
    '@' => xl('@ Arrived'),
    '~' => xl('~ Arrived late'),
    '!' => xl('! Left w/o visit'),
    '#' => xl('# Ins/fin issue'),
    '<' => xl('< In exam room'),
    '>' => xl('> Checked out'),
    '$' => xl('$ Coding done'),
    '^' => xl('^ Pending'),
];

$repeats = 0; // if the event repeats
$repeattype = '0';
$repeatfreq = '0';
$patienttitle = "";
$hometext = "";
$row = [];

// If we are editing an existing event, then get its data.
if ($eid !== 0) {
    $row = sqlQuery("SELECT * FROM openemr_postcalendar_events WHERE pc_eid = ?", [$eid]);
    $date = oeFormatShortDate($row['pc_eventDate']);
    $userid = $row['pc_aid'];
    $patientid = $row['pc_pid'];
    $starttimeh = substr((string) $row['pc_startTime'], 0, 2) + 0;
    $starttimem = substr((string) $row['pc_startTime'], 3, 2);
    $repeats = $row['pc_recurrtype'];
    $multiple_value = $row['pc_multiple'];

    if (preg_match('/"event_repeat_freq_type";s:1:"(\d)"/', (string) $row['pc_recurrspec'], $matches)) {
        $repeattype = $matches[1];
    }

    if (preg_match('/"event_repeat_freq";s:1:"(\d)"/', (string) $row['pc_recurrspec'], $matches)) {
        $repeatfreq = $matches[1];
    }

    $hometext = $row['pc_hometext'];
    if (str_starts_with((string) $hometext, ':text:')) {
        $hometext = substr((string) $hometext, 6);
    }
} else {
    $patientid = $pid;
}

// If we have a patient ID, get the name and phone numbers to display.
if ($patientid) {
    $prow = sqlQuery("SELECT lname, fname, phone_home, phone_biz, DOB " .
        "FROM patient_data WHERE pid = ?", [$patientid]);
    $patientname = $prow['lname'] . ", " . $prow['fname'];
    if ($prow['phone_home']) {
        $patienttitle .= " H=" . $prow['phone_home'];
    }

    if ($prow['phone_biz']) {
        $patienttitle .= " W=" . $prow['phone_biz'];
    }
}

// Get the providers list.
$ures = sqlStatement("SELECT `id`, `username`, `fname`, `lname`, `mname` FROM `users` WHERE " .
    "`authorized` != 0 AND `active` = 1 AND `username` > '' ORDER BY `lname`, `fname`");

//Set default facility for a new event based on the given 'userid'
if ($userid) {
    $pref_facility = sqlFetchArray(sqlStatement("SELECT facility_id, facility FROM users WHERE id = ?", [$userid]));
    $e2f = $pref_facility['facility_id'];
    $e2f_name = $pref_facility['facility'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $eid ? xlt("Edit Event") : xlt("Add New Event"); ?></title>
    <?php // no header necessary. scope is home.php ?>
</head>
<script>
    var durations = Array();
    <?php
    // Read the event categories, generate their options list, and get
    // the default event duration from them if this is a new event.
    $cattype = 0;

    // Get event categories.
    $cres = sqlStatement("SELECT pc_catid, pc_cattype, pc_constant_id, pc_catname, " .
        "pc_recurrtype, pc_duration, pc_end_all_day " .
        "FROM openemr_postcalendar_categories where pc_active = 1 ORDER BY pc_seq");
    $catoptions = "";
    $prefcat_options = "    <option value='0'>-- " . xlt("None{{Category}}") . " --</option>\n";
    $thisduration = 0;
    if ($eid !== 0) {
        $thisduration = $row['pc_alldayevent'] ? 1440 : round($row['pc_duration'] / 60);
    }
    while ($crow = sqlFetchArray($cres)) {
        $duration = round($crow['pc_duration'] / 60);
        if ($crow['pc_end_all_day']) {
            $duration = 1440;
        }

        // This section is to build the list of preferred categories:
        if ($duration) {
            $prefcat_options .= " <option value='" . attr($crow['pc_catid']) . "'";
            if ($eid !== 0) {
                if ($crow['pc_catid'] == $row['pc_prefcatid']) {
                    $prefcat_options .= " selected";
                }
            }

            $prefcat_options .= ">" . text(xl_appt_category($crow['pc_catname'])) . "</option>\n";
        }

        if ($crow['pc_cattype'] != $cattype || $crow['pc_constant_id'] === AppointmentService::CATEGORY_CONSTANT_NO_SHOW) {
            continue;
        }

        echo " durations[" . attr($crow['pc_catid']) . "] = " . attr($duration) . ";\n";
        // echo " rectypes[" . $crow['pc_catid'] . "] = " . $crow['pc_recurrtype'] . "\n";
        $catoptions .= "    <option value='" . attr($crow['pc_catid']) . "'";
        if ($eid !== 0) {
            if ($crow['pc_catid'] == $row['pc_catid']) {
                $catoptions .= " selected";
            }
        } else {
            if ($crow['pc_catid'] == $default_catid) {
                $catoptions .= " selected";
                $thisduration = $duration;
            }
        }

        $catoptions .= ">" . text(xl_appt_category($crow['pc_catname'])) . "</option>\n";
    }
    // Fix up the time format for AM/PM.
    $startampm = '1';
    if ($starttimeh >= 12) { // p.m. starts at noon and not 12:01
        $startampm = '2';
        if ($starttimeh > 12) {
            $starttimeh -= 12;
        }
    }

    ?>
</script>
<body class="skin-blue">
    <div class="container-fluid">
        <form method='post' name='theaddform' id='theaddform' action='add_edit_event_user.php?eid=<?php echo attr_url($eid); ?>'>
            <div class="col-12">
                <input type="hidden" name="form_action" id="form_action" value="" />
                <input type='hidden' name='form_title' id='form_title' value='<?php echo ($row['pc_catid'] ?? '') ? attr($row['pc_title']) : xla("Office Visit"); ?>' />
                <input type='hidden' name='form_apptstatus' id='form_apptstatus' value='<?php echo ($row['pc_apptstatus'] ?? '') ? attr($row['pc_apptstatus'] ?? '') : "^" ?>' />
                <div class="row form-group">
                    <div class="input-group col-12 col-md-6">
                        <label class="mr-2" for="form_category"><?php echo xlt('Visit'); ?>:</label>
                        <select class="form-control mb-1" onchange='set_category()' id='form_category' name='form_category' value='<?php echo (($row['pc_catid'] ?? '') > "") ? attr($row['pc_catid']) : '5'; ?>'>
                            <?php echo $catoptions ?>
                        </select>
                    </div>
                    <div class="input-group col-12 col-md-6">
                        <label class="mr-2" for="form_date"><?php echo xlt('Date'); ?>:</label>
                        <input class="form-control mb-1" type='text' name='form_date' readonly id='form_date' value='<?php echo (isset($eid) && $eid !== 0) ? attr(oeFormatShortDate($row['pc_eventDate'])) : attr($date); ?>' />
                    </div>
                </div>
                <div class="row">
                    <div class="form-group form-inline col-12">
                        <div class="input-group mb-1">
                            <label class="mr-2"><?php echo xlt('Time'); ?>:</label>
                            <input class="form-control col-2 col-md-3" type='text' name='form_hour' size='2' value='<?php echo ((isset($eid) && $eid !== 0)) ? $starttimeh : ''; ?>' title='<?php echo xla('Event start time'); ?>' readonly />
                            <input class="form-control col-2 col-md-3" type='text' name='form_minute' size='2' value='<?php echo ((isset($eid) && $eid !== 0)) ? $starttimem : ''; ?>' title='<?php echo xla('Event start time'); ?>' readonly />
                            <select class="form-control col-3 col-md-4" name='form_ampm' title='Note: 12:00 noon is PM, not AM' readonly>
                                <option value='1'><?php echo xlt('AM'); ?></option>
                                <option value='2'<?php echo ($startampm == '2') ? " selected" : ""; ?>><?php echo xlt('PM'); ?></option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label class="mr-2" for="form_duration"><?php echo xlt('Duration'); ?></label>
                            <input class="form-control" type='text' size='1' id='form_duration' name='form_duration' value='<?php echo ($row['pc_duration'] ?? '') ? ($row['pc_duration'] * 1 / 60) : attr($thisduration) ?>' readonly />
                            <span class="input-group-append">
                            <span class="input-group-text"><?php echo "&nbsp;" . xlt('minutes'); ?></span>
                        </span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="input-group col-12 mb-1">
                        <label class="mr-2" for="form_patient"><?php echo xlt('Patient'); ?>:</label>
                        <input class="form-control" type='text' id='form_patient' name='form_patient' value='<?php echo attr($patientname); ?>' title='Patient' readonly />
                        <input type='hidden' name='form_pid' value='<?php echo attr($patientid); ?>' />
                    </div>
                </div>
                <div class="row">
                    <div class="input-group col-12 mb-1">
                        <label class="mr-2" for="form_provider_ae"><?php echo xlt('Provider'); ?>:</label>
                        <select class="form-control" name='form_provider_ae' id='form_provider_ae' onchange='change_provider();'>
                            <?php
                            // present a list of providers to choose from
                            // default to the currently logged-in user
                            while ($urow = sqlFetchArray($ures)) {
                                echo "<option value='" . attr($urow['id']) . "'";
                                if (($urow['id'] == ($_GET['userid'] ?? null)) || ($urow['id'] == $userid)) {
                                    echo " selected";
                                }
                                echo ">" . text($urow['lname']);
                                if ($urow['fname']) {
                                    echo ", " . text($urow['fname']);
                                }
                                echo "</option>\n";
                            }
                            ?>
                        </select>
                        <div class="text-right">
                            <input type='button' class='btn btn-success' value='<?php echo xla('See Availability'); ?>' onclick='find_available()' />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="input-group col-12">
                        <label class="mr-2"><?php echo xlt('Reason'); ?>:</label>
                        <input class="form-control" type='text' size='40' name='form_comments' value='<?php echo attr($hometext); ?>' title='<?php echo xla('Optional information about this event'); ?>' />
                    </div>
                </div>
                <div class="row input-group my-1">
                    <?php if (($_GET['eid'] ?? null) && $row['pc_apptstatus'] !== 'x') { ?>
                        <input type='button' id='form_cancel' class='btn btn-danger' onsubmit='return false' value='<?php echo xla('Cancel Appointment'); ?>' onclick="cancel_appointment()" />
                    <?php } ?>
                    <input type='button' name='form_save' class='btn btn-success' onsubmit='return false' value='<?php echo xla('Save'); ?>' onclick="validate()" />
                </div>
            </div>
        </form>
        <script>
            function change_provider() {
                var f = document.forms.namedItem("theaddform");
                // use today's date but reset everything else when changing providers so we can
                // search on availability. jsGlobals['date_display_format']
                f.form_date.value = window.top.oeFormatters.I18NDateFormat(new Date());
                f.form_hour.value = '';
                f.form_minute.value = '';
            }

            function set_display() {
                var f = document.forms.namedItem("theaddform");
                var si = document.getElementById('form_category');
                if (si.selectedIndex >= 0) {
                    var catid = si.options[si.selectedIndex].value;
                    //var style_apptstatus = document.getElementById('title_apptstatus').style;
                    //var style_prefcat = document.getElementById('title_prefcat').style;
                    // will keep this for future. not needed now.
                }
            }

            function cancel_appointment() {
                let f = document.forms.namedItem("theaddform");
                let msg = <?php echo xlj("Click Okay if you are sure you want to cancel this appointment?") . "\n" .
                    xlj("It is prudent to follow up with provider if not contacted.") ?>;
                let msg_reason = <?php echo xlj("You must enter a reason to cancel this appointment?") . "\n" .
                    xlj("Reason must be at least 10 characters!") ?>;
                if (f.form_comments.value.length <= 10) {
                    alert(msg_reason);
                    return false;
                }
                let yn = confirm(msg);
                if (!yn) {
                    return false;
                }
                document.getElementById('form_apptstatus').value = "x";
                validate();
            }

            // Do whatever is needed when a new event category is selected.
            // For now this means changing the event title and duration.
            function set_category() {
                var f = document.forms.namedItem("theaddform");
                var s = f.form_category;
                if (s.selectedIndex >= 0) {
                    var catid = s.options[s.selectedIndex].value;
                    f.form_title.value = s.options[s.selectedIndex].text;
                    f.form_duration.value = durations[catid] || '15';
                    set_display();
                }
            }

            // This is for callback by the find-available popup.
            function setappt(year, mon, mday, hours, minutes) {
                var f = document.forms.namedItem("theaddform");
                // note that month is 0 based, 0-11 so need to subtract 1 as the api caller is 1 based (1-12)
                let date= new Date(year, mon-1, mday, hours, minutes);
                f.form_date.value = window.top.oeFormatters.I18NDateFormat(date);
                f.form_ampm.selectedIndex = (hours > 12) ? 1 : 0;
                if (hours == 0) {
                    f.form_hour.value = 12;
                } else {
                    f.form_hour.value = (hours >= 13) ? hours - 12 : hours;
                }
                f.form_minute.value = minutes;
            }

            function get_form_category_value() {
                var catid = 0;
                var f = document.forms.namedItem("theaddform");
                var s = f.form_category;
                if (s.selectedIndex >= 0) {
                    catid = s.options[s.selectedIndex].value;
                }
                return catid;
            }

            // Invoke the find-available popup.
            function find_available() {
                // when making an appointment for a specific provider
                var catId = get_form_category_value() || 5;
                var se = document.getElementById('form_provider_ae');
                <?php if ($userid != 0) { ?>
                s = se.value;
                <?php } else {?>
                s = se.options[se.selectedIndex].value;
                <?php }?>
                const formDate = document.getElementById('form_date');
                const urlParams = new URLSearchParams({
                    bypatient: '',
                    catid: catId,
                    providerid: s,
                    startdate: formDate.value
                });
                const url = 'find_appt_popup_user.php?' + urlParams;
                const dialogParams = {
                    buttons: [
                        {text: <?php echo xlj('Cancel'); ?>, close: true, style: 'danger btn-sm'}

                    ],
                    allowResize: true,
                    dialogId: 'apptDialog',
                    type: 'iframe'
                };
                dlgopen(url, 'apptFind', 'modal-md', 300, '', 'Find Date', dialogParams);
            }

            // Check for errors when the form is submitted.
            function validate() {
                var f = document.getElementById('theaddform');
                if (!f.form_date.value || !f.form_hour.value || !f.form_minute.value) {
                    alert(<?php echo xlj('Please click on Openings to select a time.'); ?>);
                    return false;
                }

                if (f.form_patient.value == '') {
                    alert(<?php echo xlj('Your Id is missing. Cancel and try again.'); ?>);
                    return false;
                }

                var form_action = document.getElementById('form_action');
                form_action.value = "save";
                f.submit();
                return false;
            }

            <?php if ($eid !== 0) { ?>
            set_display();
            <?php } ?>
            $(function () {

            });
        </script>
    </div>
</body>
</html>
