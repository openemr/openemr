<?php

require_once("../../../globals.php");
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/calendar.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/encounter_events.inc.php');
require_once($GLOBALS['srcdir'].'/patient_tracker.inc.php');
require_once($GLOBALS['incdir']."/main/holidays/Holidays_Controller.php");
require_once($GLOBALS['srcdir'].'/group.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/case_functions.inc.php');
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\ZoomIntegration;


$_POST['form_date'] = DateToYYYYMMDD($_POST['form_date']);

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
$eid = isset($_REQUEST['eid']) ? $_REQUEST['eid'] : null;

$needToCreateEncounter = false;
$needToCreateEncounterForGroup = false;

// Save new DOB if it's there.
$patient_dob = trim($_POST['form_dob']);
$tmph = $_POST['form_hour'] + 0;
$tmpm = $_POST['form_minute'] + 0;
if ($_POST['form_ampm'] == '2' && $tmph < 12) {
    $tmph += 12;
}

$appttime = "$tmph:$tmpm:00";
$event_date = $_POST['form_date'];

if($type == "check") {
	if($_POST['form_action'] == "duplicate" || $_POST['form_action'] == "save") {
		if (!empty($_POST['form_pid'])) {
            if ($GLOBALS['auto_create_new_encounters'] && $event_date == date('Y-m-d') && (is_checkin($_POST['form_apptstatus']) == '1') && !is_tracker_encounter_exist($event_date, $appttime, $_POST['form_pid'], $eid)) {
            	$encounter = todaysEncounterIf($_POST['form_pid']);
    			if ($encounter) {
    				$needToCreateEncounter = true;
    			}
            }
        }

        if (!empty($_POST['form_gid'])) {
        	if ($GLOBALS['auto_create_new_encounters'] && $event_date == date('Y-m-d') && $_POST['form_apptstatus'] == '=') {
        		$encounter = todaysTherapyGroupEncounterIf($_POST['form_gid']);
        		if ($encounter) {
    				$needToCreateEncounterForGroup = true;
    			}
        	}
        }
	}
}

echo json_encode(array('encounter' => $needToCreateEncounter, 'encounter_group' => $needToCreateEncounterForGroup));