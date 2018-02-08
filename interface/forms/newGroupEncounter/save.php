<?php
/**
 * Encounter form save script.
 *
 * Copyright (C) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>

 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 *
 * @link    http://www.open-emr.org
 */




require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/encounter.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formdata.inc.php");

use OpenEMR\Services\FacilityService;

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
        sqlInsert("INSERT INTO form_groups_encounter SET " .
        "date = '" . add_escape_custom($date) . "', " .
        "onset_date = '" . add_escape_custom($onset_date) . "', " .
        "reason = '" . add_escape_custom($reason) . "', " .
        "facility = '" . add_escape_custom($facility) . "', " .
        "pc_catid = '" . add_escape_custom($pc_catid) . "', " .
        "facility_id = '" . add_escape_custom($facility_id) . "', " .
        "billing_facility = '" . add_escape_custom($billing_facility) . "', " .
        "sensitivity = '" . add_escape_custom($sensitivity) . "', " .
        "referral_source = '" . add_escape_custom($referral_source) . "', " .
        "group_id = '" . add_escape_custom($group_id) . "', " .
        "encounter = '" . add_escape_custom($encounter) . "', " .
        "pos_code = '" . add_escape_custom($pos_code) . "', " .
        "provider_id = '" . add_escape_custom($provider_id) . "'," .
        "counselors = '" . add_escape_custom($counselors) . "'"),
        "newGroupEncounter",
        null,
        $userauthorized,
        $date
    );
} else if ($mode == 'update') {
    $id = $_POST["id"];
    $result = sqlQuery("SELECT encounter, sensitivity FROM form_groups_encounter WHERE id = ?", array($id));
    if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
        die(xlt("You are not authorized to see this encounter."));
    }

    $encounter = $result['encounter'];
  // See view.php to allow or disallow updates of the encounter date.
    $datepart = acl_check('encounters', 'date_a') ? "date = '" . add_escape_custom($date) . "', " : "";
    sqlStatement("UPDATE form_groups_encounter SET " .
    $datepart .
    "onset_date = '" . add_escape_custom($onset_date) . "', " .
    "reason = '" . add_escape_custom($reason) . "', " .
    "facility = '" . add_escape_custom($facility) . "', " .
    "pc_catid = '" . add_escape_custom($pc_catid) . "', " .
    "facility_id = '" . add_escape_custom($facility_id) . "', " .
    "billing_facility = '" . add_escape_custom($billing_facility) . "', " .
    "sensitivity = '" . add_escape_custom($sensitivity) . "', " .
    "referral_source = '" . add_escape_custom($referral_source) . "', " .
    "counselors = '" . add_escape_custom($counselors) . "', " .
    "pos_code = '" . add_escape_custom($pos_code) . "' " .

    "WHERE id = '" . add_escape_custom($id) . "'");
} else {
    die("Unknown mode '" . text($mode) . "'");
}

$normalurl = "patient_file/encounter/encounter_top.php?set_encounter=" . $encounter;

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

$result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_groups_encounter AS fe ".
    " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.group_id = ? order by fe.date desc", array($group_id));
?>
<html>
<body>
<script language='JavaScript'>
    EncounterDateArray=new Array;
    CalendarCategoryArray=new Array;
    EncounterIdArray=new Array;
    Count=0;
        <?php
        if (sqlNumRows($result4)>0) {
            while ($rowresult4 = sqlFetchArray($result4)) {
        ?>
        EncounterIdArray[Count]='<?php echo attr($rowresult4['encounter']); ?>';
    EncounterDateArray[Count]='<?php echo attr(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date'])))); ?>';
    CalendarCategoryArray[Count]='<?php echo attr(xl_appt_category($rowresult4['pc_catname'])); ?>';
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
        parent.left_nav.setEncounter(<?php echo "'" . attr(oeFormatShortDate($date)) . "', '" . attr($encounter) . "', window.name"; ?>);
        //console.log('new - parent.left_nav is defined');
    }
    else {
        parent.parent.frames["left_nav"].setEncounter(<?php echo "'" . attr(oeFormatShortDate($date)) . "', '" . attr($encounter) . "', window.name"; ?>);
        //console.log('new - parent.left_nav is undefined');
    }
<?php } // end if new encounter ?>
    if(parent.left_nav) {
        parent.left_nav.loadFrame('enc2', window.name, '<?php echo $nexturl; ?>');
        //console.log('modify - parent.left_nav is defined');
    }
    else {
        parent.parent.frames["left_nav"].loadFrame('enc2', parent.name, '<?php echo $nexturl; ?>');
        //console.log('modify - parent.left_nav is undefined');
    }
</script>

</body>
</html>
