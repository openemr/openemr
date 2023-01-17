<?php

/**
 * Encounter form save script.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/encounter.inc");

// OEMR - A
require_once("$srcdir/wmt-v2/case_functions.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\ListService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$facilityService = new FacilityService();

$date = isset($_POST['form_date']) ? DateTimeToYYYYMMDDHHMMSS($_POST['form_date']) : null;
$onset_date = isset($_POST['form_onset_date']) ? DateTimeToYYYYMMDDHHMMSS($_POST['form_onset_date']) : null;
$sensitivity = $_POST['form_sensitivity'] ?? null;
$pc_catid = $_POST['pc_catid'] ?? null;
$facility_id = $_POST['facility_id'] ?? null;
$billing_facility = $_POST['billing_facility'] ?? '';
$reason = $_POST['reason'] ?? null;
$mode = $_POST['mode'] ?? null;
$referral_source = $_POST['form_referral_source'] ?? null;
$class_code = $_POST['class_code'] ?? '';
$pos_code = $_POST['pos_code'] ?? null;
$parent_enc_id = $_POST['parent_enc_id'] ?? null;
$encounter_provider = $_POST['provider_id'] ?? null;
$referring_provider_id = $_POST['referring_provider_id'] ?? null;
//save therapy group if exist in external_id column
$external_id = isset($_POST['form_gid']) ? $_POST['form_gid'] : '';

$discharge_disposition = $_POST['discharge_disposition'] ?? null;
$discharge_disposition = $discharge_disposition != '_blank' ? $discharge_disposition : null;

$facilityresult = $facilityService->getById($facility_id);
$facility = $facilityresult['name'];

/* OEMR - A */
$provider_id = $_POST['provider_id'] ?? null;
$supervisor_id = $_POST['supervisor_id'] ?? null;
$case_id = $_POST['form_case'] ?? null;
if(!$case_id || $case_id == '0') {
  $case_id = mostRecentCase($pid, $_POST['force_new']);
}
/* End */

$normalurl = "patient_file/encounter/encounter_top.php";

$nexturl = $normalurl;

// OEMR - Commeted
//$provider_id = $_SESSION['authUserID'] ? $_SESSION['authUserID'] : 0;
$provider_id = $encounter_provider ? $encounter_provider : $provider_id;

$encounter_type = $_POST['encounter_type'] ?? '';
$encounter_type_code = null;
$encounter_type_description = null;
// we need to lookup the codetype and the description from this if we have one
if (!empty($encounter_type)) {
    $listService = new ListService();
    $option = $listService->getListOption('encounter-types', $encounter_type);
    $encounter_type_code = $option['codes'] ?? null;
    if (!empty($encounter_type_code)) {
        $codeService = new CodeTypesService();
        $encounter_type_description = $codeService->lookup_code_description($encounter_type_code) ?? null;
    } else {
        // we don't have any codes installed here so we will just use the encounter_type
        $encounter_type_code = $encounter_type;
        $encounter_type_description = $option['title'];
    }
}

if ($mode == 'new') {
    $encounter = generate_id();
    // OEMR - Added supervisor_id field and value
    addForm(
        $encounter,
        "New Patient Encounter",
        sqlInsert(
            "INSERT INTO form_encounter SET
                date = ?,
                onset_date = ?,
                reason = ?,
                facility = ?,
                pc_catid = ?,
                facility_id = ?,
                billing_facility = ?,
                sensitivity = ?,
                referral_source = ?,
                pid = ?,
                encounter = ?,
                pos_code = ?,
                class_code = ?,
                external_id = ?,
                parent_encounter_id = ?,
                provider_id = ?,
                discharge_disposition = ?,
                referring_provider_id = ?,
                encounter_type_code = ?,
                encounter_type_description = ?,
                supervisor_id = ?",
            [
                $date,
                $onset_date,
                $reason,
                $facility,
                $pc_catid,
                $facility_id,
                $billing_facility,
                $sensitivity,
                $referral_source,
                $pid,
                $encounter,
                $pos_code,
                $class_code,
                $external_id,
                $parent_enc_id,
                $provider_id,
                $discharge_disposition,
                $referring_provider_id,
                $encounter_type_code,
                $encounter_type_description,
                $supervisor_id
            ]
        ),
        "newpatient",
        $pid,
        $userauthorized,
        $date
    );
} elseif ($mode == 'update') {
    $id = $_POST["id"];
    $result = sqlQuery("SELECT encounter, sensitivity FROM form_encounter WHERE id = ?", array($id));
    if ($result['sensitivity'] && !AclMain::aclCheckCore('sensitivities', $result['sensitivity'])) {
        die(xlt("You are not authorized to see this encounter."));
    }

    $encounter = $result['encounter'];
    // See view.php to allow or disallow updates of the encounter date.
    $datepart = "";
    $sqlBindArray = array();
    if (AclMain::aclCheckCore('encounters', 'date_a')) {
        $datepart = "date = ?, ";
        $sqlBindArray[] = $date;
    }
    // OEMR - Added supervisor_id field and value
    array_push(
        $sqlBindArray,
        $onset_date,
        $provider_id,
        $reason,
        $facility,
        $pc_catid,
        $facility_id,
        $billing_facility,
        $sensitivity,
        $referral_source,
        $class_code,
        $pos_code,
        $discharge_disposition,
        $referring_provider_id,
        $encounter_type_code,
        $encounter_type_description,
        $supervisor_id,
        $id
    );
    sqlStatement(
        "UPDATE form_encounter SET
            $datepart
            onset_date = ?,
            provider_id = ?,
            reason = ?,
            facility = ?,
            pc_catid = ?,
            facility_id = ?,
            billing_facility = ?,
            sensitivity = ?,
            referral_source = ?,
            class_code = ?,
            pos_code = ?,
            discharge_disposition = ?,
            referring_provider_id = ?,
            encounter_type_code = ?,
            encounter_type_description = ?,
            supervisor_id = ? 
            WHERE id = ?",
        $sqlBindArray
    );
} else {
    die("Unknown mode '" . text($mode) . "'");
}

setencounter($encounter);

// Update the list of issues associated with this encounter.
if (!empty($_POST['issues']) && is_array($_POST['issues'])) {
    sqlStatement("DELETE FROM issue_encounter WHERE " .
        "pid = ? AND encounter = ?", array($pid, $encounter));
    foreach ($_POST['issues'] as $issue) {
        $query = "INSERT INTO issue_encounter ( pid, list_id, encounter ) VALUES (?,?,?)";
        sqlStatement($query, array($pid, $issue, $encounter));
    }
}

/* OEMR - A */
if($case_id) {
    $exists = sqlQuery('SELECT * FROM case_appointment_link WHERE ' .
        'enc_case = ?', array($case_id));
    if(!isset($exists{'pid'})) $exists{'pid'} = '';
    if($exists{'pid'}) {
        $msg = 'Patient Mis-Match - Case ['.$case_id.'] Is Currently Linked To PID ('.$exists{'pid'}.') And This Encounter is PID -'.$pid.'-';
        if($pid != $exists{'pid'}) die($msg);
    }
    $exists = sqlQuery('SELECT id, pid FROM form_cases WHERE ' .
        'id = ?', array($case_id));
    if(!isset($exists{'pid'})) $exists{'pid'} = '';
    if($exists{'pid'}) {
        $msg = 'Patient Mis-Match - Case ['.$case_id.'] Is Currently Attached To PID ('.$exists{'pid'}.') And This Encounter is PID -'.$pid.'-';
        if($pid != $exists{'pid'}) die($msg);
    }
    sqlInsert('INSERT INTO case_appointment_link SET enc_case = ?, '.
        'encounter = ?, pid = ? ON DUPLICATE KEY UPDATE enc_case = ?',
        array($case_id, $encounter, $pid, $case_id));
    $link = sqlQuery('SELECT * FROM case_appointment_link WHERE ' .
        'encounter = ?', array($encounter));
    if($link{'pc_eid'} && ($link{'pc_pid'} == $pid || !$link['pc_pid'])) {
        sqlStatement('UPDATE openemr_postcalendar_events SET pc_case ' .
            '= ? WHERE pc_eid = ?', array($case_id, $link{'pc_eid'}));
    }
}
/* End */

$result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe " .
    " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? order by fe.date desc", array($pid));
?>
<html>
<body>
    <script>
        EncounterDateArray = Array();
        CalendarCategoryArray = Array();
        EncounterIdArray = Array();
        Count = 0;
        <?php
        if (sqlNumRows($result4) > 0) {
            while ($rowresult4 = sqlFetchArray($result4)) {
                ?>
        EncounterIdArray[Count] =<?php echo js_escape($rowresult4['encounter']); ?>;
        EncounterDateArray[Count] =<?php echo js_escape(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date'])))); ?>;
        CalendarCategoryArray[Count] =<?php echo js_escape(xl_appt_category($rowresult4['pc_catname'])); ?>;
        Count++;
                <?php
            }
        }
        ?>

        // Get the left_nav window, and the name of its sibling (top or bottom) frame that this form is in.
        // This works no matter how deeply we are nested

        var my_left_nav = top.left_nav;
        var w = window;
        for (; w.parent != top; w = w.parent) ;
        var my_win_name = w.name;
        my_left_nav.setPatientEncounter(EncounterIdArray, EncounterDateArray, CalendarCategoryArray);
        top.restoreSession();
        <?php if ($mode == 'new') { ?>
        my_left_nav.setEncounter(<?php echo js_escape(oeFormatShortDate($date)) . ", " . js_escape($encounter) . ", window.name"; ?>);
        // Load the tab set for the new encounter, w is usually the RBot frame.
        w.location.href = '<?php echo "$rootdir/patient_file/encounter/encounter_top.php"; ?>';
        <?php } else { // not new encounter ?>
        // Always return to encounter summary page.
        window.location.href = '<?php echo "$rootdir/patient_file/encounter/forms.php"; ?>';
        <?php } // end if not new encounter ?>

    </script>
</body>
</html>
