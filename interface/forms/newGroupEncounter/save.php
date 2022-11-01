<?php

/**
 * Encounter form save script.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/encounter.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\FacilityService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$facilityService = new FacilityService();

$group_id = $_SESSION['therapy_group'];
$provider_id = $userauthorized ? $_SESSION['authUserID'] : 0;

$date             = (isset($_POST['form_date']))            ? DateToYYYYMMDD($_POST['form_date']) : '';
$onset_date       = (isset($_POST['form_onset_date']))      ? DateToYYYYMMDD($_POST['form_onset_date']) : '';
$sensitivity      = (isset($_POST['form_sensitivity']))     ? $_POST['form_sensitivity'] : '';
$pc_catid         = (isset($_POST['pc_catid']))             ? $_POST['pc_catid'] : '';
$facility_id      = (isset($_POST['facility_id']))          ? $_POST['facility_id'] : '';
$billing_facility = (isset($_POST['billing_facility']))     ? $_POST['billing_facility'] : '';
$reason           = (isset($_POST['reason']))               ? $_POST['reason'] : '';
$mode             = (isset($_POST['mode']))                 ? $_POST['mode'] : '';
$referral_source  = (isset($_POST['form_referral_source'])) ? $_POST['form_referral_source'] : '';
$pos_code         = (isset($_POST['pos_code']))              ? $_POST['pos_code'] : '';
$counselors       = (isset($_POST['counselors']) && is_array($_POST['counselors']))  ?  implode(', ', $_POST['counselors']) : $provider_id;


$facilityresult = $facilityService->getById($facility_id);
$facility = $facilityresult['name'];

if ($mode == 'new') {
    $provider_id = $userauthorized ? $_SESSION['authUserID'] : 0;
    $encounter = generate_id();
    addForm(
        $encounter,
        "New Therapy Group Encounter",
        sqlInsert(
            "INSERT INTO form_groups_encounter SET
                date = ?,
                onset_date = ?,
                reason = ?,
                facility = ?,
                pc_catid = ?,
                facility_id = ?,
                billing_facility = ?,
                sensitivity = ?,
                referral_source = ?,
                group_id = ?,
                encounter = ?,
                pos_code = ?,
                provider_id = ?,
                counselors = ?",
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
                $group_id,
                $encounter,
                $pos_code,
                $provider_id,
                $counselors
            ]
        ),
        "newGroupEncounter",
        null,
        $userauthorized,
        $date
    );
} elseif ($mode == 'update') {
    $id = $_POST["id"];
    $result = sqlQuery("SELECT encounter, sensitivity FROM form_groups_encounter WHERE id = ?", array($id));
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
    array_push(
        $sqlBindArray,
        $onset_date,
        $reason,
        $facility,
        $pc_catid,
        $facility_id,
        $billing_facility,
        $sensitivity,
        $referral_source,
        $counselors,
        $pos_code,
        $id
    );
    sqlStatement(
        "UPDATE form_groups_encounter SET
            $datepart
            onset_date = ?,
            reason = ?,
            facility = ?,
            pc_catid = ?,
            facility_id = ?,
            billing_facility = ?,
            sensitivity = ?,
            referral_source = ?,
            counselors = ?,
            pos_code = ? WHERE id = ?",
        $sqlBindArray
    );
} else {
    die("Unknown mode '" . text($mode) . "'");
}

$normalurl = "patient_file/encounter/encounter_top.php?set_encounter=" . urlencode($encounter);

$nexturl = $normalurl;

// todo - check else solution
//setencounter($encounter);

/*// Update the list of issues associated with this encounter.
sqlStatement("DELETE FROM issue_encounter WHERE " .
  "pid = ? AND encounter = ?", array($pid,$encounter) );
if (is_array($_POST['issues'])) {
  foreach ($_POST['issues'] as $issue) {
    $query = "INSERT INTO issue_encounter ( pid, list_id, encounter ) VALUES (?,?,?)";
    sqlStatement($query, array($pid,$issue,$encounter));
  }
}*/

$result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_groups_encounter AS fe " .
    " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.group_id = ? order by fe.date desc", array($group_id));
?>
<html>
<body>
<script>
    EncounterDateArray=new Array;
    CalendarCategoryArray=new Array;
    EncounterIdArray=new Array;
    Count=0;
        <?php
        if (sqlNumRows($result4) > 0) {
            while ($rowresult4 = sqlFetchArray($result4)) {
                ?>
        EncounterIdArray[Count]=<?php echo js_escape($rowresult4['encounter']); ?>;
    EncounterDateArray[Count]=<?php echo js_escape(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date'])))); ?>;
    CalendarCategoryArray[Count]=<?php echo js_escape(xl_appt_category($rowresult4['pc_catname'])); ?>;
            Count++;
                <?php
            }
        }
        ?>
     top.window.parent.left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);
 top.restoreSession();
<?php if ($mode == 'new') { ?>
    //todo - checking necessary
    if(parent.left_nav) {
        parent.left_nav.setEncounter(<?php echo js_escape(oeFormatShortDate($date)) . ", " . js_escape($encounter) . ", window.name"; ?>);
        //console.log('new - parent.left_nav is defined');
    }
    else {
        parent.parent.frames["left_nav"].setEncounter(<?php echo js_escape(oeFormatShortDate($date)) . ", " . js_escape($encounter) . ", window.name"; ?>);
        //console.log('new - parent.left_nav is undefined');
    }
<?php } // end if new encounter ?>
    if(parent.left_nav) {
        parent.left_nav.loadFrame('enc2', window.name, <?php echo js_escape($nexturl); ?>);
        //console.log('modify - parent.left_nav is defined');
    }
    else {
        parent.parent.frames["left_nav"].loadFrame('enc2', parent.name, <?php echo js_escape($nexturl); ?>);
        //console.log('modify - parent.left_nav is undefined');
    }
</script>

</body>
</html>
