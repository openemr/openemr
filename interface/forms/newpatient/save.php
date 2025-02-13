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
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/encounter.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\ListService;
use OpenEMR\Services\PatientService;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientIssuesService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$facilityService = new FacilityService();
$encounterService = new EncounterService();
$patientService = new PatientService();

// Get patient UUID
/**
 * @global $pid
 */
$patient = $patientService->findByPid($pid);
if (empty($patient)) {
    (new \OpenEMR\Common\Logging\SystemLogger())->errorLogCaller("Patient not found, this should never happen");
    die("Patient not found");
}
$puuid = UuidRegistry::uuidToString($patient['uuid']);

if ($_POST['mode'] == 'new' && ($GLOBALS['enc_service_date'] == 'hide_both' || $GLOBALS['enc_service_date'] == 'show_edit')) {
    $date = (new DateTime())->format('Y-m-d H:i:s');
} elseif ($_POST['mode'] == 'update' && ($GLOBALS['enc_service_date'] == 'hide_both' || $GLOBALS['enc_service_date'] == 'show_new')) {
    $enc_from_id = sqlQuery("SELECT `encounter` FROM `form_encounter` WHERE `id` = ?", [intval($_POST['id'])]);
    $enc = $encounterService->getEncounterById($enc_from_id['encounter']);
    $enc_data = $enc->getData();
    $date = $enc_data[0]['date'];
} else {
    $date = isset($_POST['form_date']) ? DateTimeToYYYYMMDDHHMMSS($_POST['form_date']) : null;
}
$defaultPosCode = $encounterService->getPosCode($_POST['facility_id']);
$onset_date = isset($_POST['form_onset_date']) ? DateTimeToYYYYMMDDHHMMSS($_POST['form_onset_date']) : null;
$sensitivity = $_POST['form_sensitivity'] ?? null;
$pc_catid = $_POST['pc_catid'] ?? '';
$facility_id = $_POST['facility_id'] ?? null;
$billing_facility = $_POST['billing_facility'] ?? '';
$reason = $_POST['reason'] ?? null;
$mode = $_POST['mode'] ?? null;
$referral_source = $_POST['form_referral_source'] ?? null;
$class_code = $_POST['class_code'] ?? '';
$pos_code = (empty($_POST['pos_code'])) ? $defaultPosCode : $_POST['pos_code'];
$in_collection = $_POST['in_collection'] ?? null;
$parent_enc_id = $_POST['parent_enc_id'] ?? null;
$encounter_provider = $_POST['provider_id'] ?? null;
$referring_provider_id = $_POST['referring_provider_id'] ?? null;
//save therapy group if exist in external_id column
$external_id = isset($_POST['form_gid']) ? $_POST['form_gid'] : '';
$ordering_provider_id = $_POST['ordering_provider_id'] ?? null;

$discharge_disposition = $_POST['discharge_disposition'] ?? null;
$discharge_disposition = $discharge_disposition != '_blank' ? $discharge_disposition : null;

$facilityresult = $facilityService->getById($facility_id);
$facility = $facilityresult['name'];

$normalurl = "patient_file/encounter/encounter_top.php";

$nexturl = $normalurl;

$provider_id = $_SESSION['authUserID'] ? $_SESSION['authUserID'] : 0;
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
// Prepare encounter data
$encounterData = [
    'date' => $date,
    'onset_date' => $onset_date,
    'sensitivity' => $sensitivity,
    'pc_catid' => $pc_catid,
    'facility_id' => $facility_id,
    'facility' => $facility,
    'billing_facility' => $billing_facility,
    'reason' => $reason,
    'referral_source' => $referral_source,
    'class_code' => $class_code,
    'pos_code' => $pos_code,
    'in_collection' => $in_collection,
    'provider_id' => $provider_id,
    'referring_provider_id' => $referring_provider_id,
    'ordering_provider_id' => $ordering_provider_id,
    'discharge_disposition' => $discharge_disposition,
    'external_id' => $external_id,
    'encounter_type_code' => $encounter_type_code,
    'encounter_type_description' => $encounter_type_description,
    'pid' => $pid,
    'parent_encounter_id' => $parent_enc_id,
    'user' => $_SESSION['authUser'],
    'group' => $_SESSION['authProvider'],
];

if ($mode == 'new') {
    $result = $encounterService->insertEncounter($puuid, $encounterData);
    if (!$result->isValid()) {
        // Handle errors
        die("Error creating encounter: " . var_export($result->getValidationMessages(), true));
    }
    $encounter = $result->getData()[0]['eid'];
} elseif ($mode == 'update') {
    $id = $_POST["id"];
    // Get encounter UUID
    $encResult = sqlQuery("SELECT uuid FROM form_encounter WHERE id = ?", array($id));
    if (empty($encResult)) {
        die("Encounter not found");
    }
    $euuid = UuidRegistry::uuidToString($encResult['uuid']);

    $result = $encounterService->updateEncounter($puuid, $euuid, $encounterData);
    if (!$result->isValid()) {
        // Handle errors
        die("Error updating encounter: " . var_export($result->getValidationMessages(), true));
    }
    $encounter = $result->getData()[0]['eid'];
} else {
    die("Unknown mode '" . text($mode) . "'");
}

setencounter($encounter);

// Update the list of issues associated with this encounter.
// always delete the issues for this encounter
$patientIssueService = new PatientIssuesService();
$patientIssueService->replaceIssuesForEncounter($pid, $encounter, $_POST['issues'] ?? []);

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
