<?php

/**
 * ankleinjury report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2004 Nikolai Vitsyn
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_ankleinjury", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Ankle Evaluation Form", $newid, "ankleinjury", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement(
        "update form_ankleinjury set pid = ?,
            groupname = ?,
            user = ?,
            authorized = ?,
            activity = 1,
            date = NOW(),
            ankle_date_of_injuary = ?,
            ankle_work_related = ?,
            ankle_foot = ?,
            ankle_severity_of_pain = ?,
            ankle_significant_swelling = ?,
            ankle_onset_of_swelling = ?,
            ankle_how_did_injury_occur = ?,
            ankle_ottawa_bone_tenderness = ?,
            ankle_able_to_bear_weight_steps = ?,
            ankle_x_ray_interpretation = ?,
            ankle_additional_x_ray_notes = ?,
            ankle_diagnosis1 = ?,
            ankle_diagnosis2 = ?,
            ankle_diagnosis3 = ?,
            ankle_diagnosis4 = ?,
            ankle_plan = ?,
            ankle_additional_diagnisis = ? where id = ?",
        [
            $_SESSION["pid"],
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            $_POST["ankle_date_of_injuary"],
            $_POST["ankle_work_related"],
            $_POST["ankle_foot"],
            $_POST["ankle_severity_of_pain"],
            $_POST["ankle_significant_swelling"],
            $_POST["ankle_onset_of_swelling"],
            $_POST["ankle_how_did_injury_occur"],
            $_POST["ankle_ottawa_bone_tenderness"],
            $_POST["ankle_able_to_bear_weight_steps"],
            $_POST["ankle_x_ray_interpretation"],
            $_POST["ankle_additional_x_ray_notes"],
            $_POST["ankle_diagnosis1"],
            $_POST["ankle_diagnosis2"],
            $_POST["ankle_diagnosis3"],
            $_POST["ankle_diagnosis4"],
            $_POST["ankle_plan"],
            $_POST["ankle_additional_diagnisis"],
            $_GET["id"]
        ]
    );
}

formHeader("Redirecting....");
formJump();
formFooter();
