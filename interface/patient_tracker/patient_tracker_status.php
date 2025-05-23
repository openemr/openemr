<?php

/**
 * Patient Tracker Status Editor
 *
 * This allows entry and editing of current status for the patient from within patient tracker and updates the status on the calendar.
 * Contains a drop down for the Room information driven by the list Patient Flow Board Rooms.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/encounter_events.inc.php");
require_once("$srcdir/patient_tracker.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

# Get the information for fields
$tracker_id = $_GET['tracker_id'];
$trow = sqlQuery("SELECT apptdate, appttime, patient_tracker_element.room AS lastroom, " .
                        "patient_tracker_element.status AS laststatus, eid, random_drug_test, encounter, pid " .
                        "FROM patient_tracker " .
                        "LEFT JOIN patient_tracker_element " .
                        "ON patient_tracker.id = patient_tracker_element.pt_tracker_id " .
                        "AND patient_tracker.lastseq = patient_tracker_element.seq " .
                        "WHERE patient_tracker.id =?", array($_GET['tracker_id']));

$tkpid = $trow['pid'];
$appttime = $trow['appttime'];
$apptdate = $trow['apptdate'];
$pceid = $trow['eid'];
$theroom = '';
?>

<html>
    <head>
        <?php Header::setupHeader(['common','opener']); ?>
    </head>

<?php
if (!empty($_POST['statustype'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $status = $_POST['statustype'];
    if (strlen($_POST['roomnum']) != 0) {
         $theroom = $_POST['roomnum'];
    }

    # Manage tracker status. Also auto create encounter, if applicable.
    if (!empty($tkpid)) {
        // if an encounter is found it is returned to be carried forward with status changes.
        // otherwise 0 which is table default.
        $is_tracker = is_tracker_encounter_exist($apptdate, $appttime, $tkpid, $pceid);
        if ($GLOBALS['auto_create_new_encounters'] && $apptdate == date('Y-m-d') && (is_checkin($status) == '1') && !$is_tracker) {
            # Gather information for encounter fields
            $genenc = sqlQuery("select pc_catid as category, pc_hometext as reason, pc_aid as provider, pc_facility as facility, pc_billing_location as billing_facility " .
                      "from openemr_postcalendar_events where pc_eid =? ", array($pceid));
            $encounter = todaysEncounterCheck($tkpid, $apptdate, $genenc['reason'], $genenc['facility'], $genenc['billing_facility'], $genenc['provider'], $genenc['category'], false);
            # Capture the appt status and room number for patient tracker. This will map the encounter to it also.
            if (!empty($pceid)) {
                manage_tracker_status($apptdate, $appttime, $pceid, $tkpid, $_SESSION["authUser"], $status, $theroom, $encounter);
            }
        } else {
            # Capture the appt status and room number for patient tracker.
            if (!empty($pceid)) {
                manage_tracker_status($apptdate, $appttime, $pceid, $tkpid, $_SESSION["authUser"], $status, $theroom, $is_tracker);
            }
        }
    }

    echo "<body>\n<script>\n";
    echo " window.opener.document.flb.submit();\n";
    echo " dlgclose();\n";
    echo "</script></body></html>\n";
    exit();
}

#get the patient name for display
$row = sqlQuery("select fname, lname " .
"from patient_data where pid =? limit 1", array($tkpid));
?>

<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Change Status for') . " " . text($row['fname']) . " " . text($row['lname']); ?></h2>
            </div>
        </div>
        <form id="form_note" method="post" action="patient_tracker_status.php?tracker_id=<?php echo attr_url($tracker_id) ?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" enctype="multipart/form-data" >
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div class="form-group">
                <label for="statustype"><?php echo xlt('Status Type'); ?></label>
                <?php echo generate_select_list('statustype', 'apptstat', $trow['laststatus'], xl('Status Type')); ?>
            </div>
            <div class="form-group">
                <label for="roomnum"><?php  echo xlt('Exam Room Number'); ?></label>
                <?php echo generate_select_list('roomnum', 'patient_flow_board_rooms', $trow['lastroom'], xl('Exam Room Number')); ?>
            </div>
            <div class="position-override">
                <div class="btn-group" role="group">
                    <button type="button" class='btn btn-primary btn-save btn-sm' onclick='document.getElementById("form_note").submit();'><?php echo xlt('Save')?></button>
                    <button type="button" class='btn btn-secondary btn-cancel btn-sm' onclick="dlgclose();" ><?php echo xlt('Cancel'); ?></button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>

