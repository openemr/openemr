<?php

/**
 * Care plan form save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright  Copyright (c) 2019 Brady Miller
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
    CsrfUtils::csrfNotVerified();
}

if (!$encounter) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}

$id               = (int)($_GET['id'] ?? 0);
$code             = $_POST["code"]         ?? [];
$code_text        = $_POST["codetext"]     ?? [];
$code_date        = $_POST["code_date"]    ?? [];
$end_date         = $_POST["end_date"]     ?? [];
$proposed_date     = $_POST["proposed_date"] ?? [];
$status           = $_POST["plan_status"]       ?? [];
$code_des         = $_POST["description"]  ?? [];
$count            = $_POST["count"]        ?? [];
$care_plan_type   = $_POST['care_plan_type'] ?? [];
$care_plan_user   = $_POST["user"]         ?? [];
$note_relations   = "";
$reasonCode       = $_POST['reasonCode']       ?? [];
$reasonStatusCode = $_POST['reasonCodeStatus'] ?? [];
$reasonCodeText   = $_POST['reasonCodeText']   ?? [];
$reasonDateLow    = $_POST['reasonDateLow']    ?? [];
$reasonDateHigh   = $_POST['reasonDateHigh']   ?? [];

if ($id) {
    sqlStatement(
        "DELETE FROM `form_care_plan` WHERE id=? AND pid=? AND encounter=?",
        [$id, $_SESSION["pid"], $_SESSION["encounter"]]
    );
    $newid = $id;
} else {
    $res2     = sqlStatement("SELECT MAX(id) as largestId FROM `form_care_plan`");
    $getMaxid = sqlFetchArray($res2);
    $newid    = $getMaxid['largestId'] ? ($getMaxid['largestId'] + 1) : 1;

    addForm($encounter, "Care Plan Form", $newid, "care_plan", $_SESSION["pid"], $userauthorized);
}

$count = array_filter($count);
if (!empty($count)) {
    foreach ($count as $key => $codeval) :
        $code_val           = $code[$key] ?? '';
        $codetext_val       = $code_text[$key] ?? '';
        $description_val    = $code_des[$key] ?? '';
        $care_plan_type_val = $care_plan_type[$key] ?? '';
        $care_user_val      = $care_plan_user[$key] ?: $_SESSION["authUser"];

        // Dates & status normalization
        $start_date_val = trim($code_date[$key] ?? '');
        $start_date_val = ($start_date_val !== '') ? $start_date_val : null;

        $end_date_val = trim($end_date[$key] ?? '');
        $end_date_val = ($end_date_val !== '') ? $end_date_val : null;

        $proposed_date_val = trim($proposed_date[$key] ?? '');
        $proposed_date_val = ($proposed_date_val !== '') ? $proposed_date_val : null;

        $plan_status_val = trim($status[$key] ?? '');
        $plan_status_val = ($plan_status_val !== '') ? $plan_status_val : null;

        // Reason/justification fields (kept as-is)
        $note_relations     = parse_note($description_val);
        $reason_code        = trim($reasonCode[$key] ?? '');
        $reason_status      = trim($reasonStatusCode[$key] ?? '');
        $reason_description = trim($reasonCodeText[$key] ?? '');
        $reason_low         = trim($reasonDateLow[$key] ?? '');
        $reason_high        = trim($reasonDateHigh[$key] ?? '');

        if (empty($reasonCode)) {
            // just as a failsafe we will set everything else to be empty if we don't have a reason code
            $reason_status      = '';
            $reason_description = '';
            $reason_low         = '';
            $reason_high        = '';
        }

        // Include new end_date and status columns
        $sets = "id = ?,
            pid = ?,
            groupname = ?,
            user = ?,
            encounter = ?,
            authorized = ?,
            activity = 1,
            code = ?,
            codetext = ?,
            description = ?,
            date = ?,
            date_end = ?,
            proposed_date = ?,
            plan_status = ?,
            care_plan_type = ?,
            note_related_to = ?,
            reason_code = ?,
            reason_status = ?,
            reason_description = ?,
            reason_date_low = ?,
            reason_date_high = ?";

        sqlStatement(
            "INSERT INTO form_care_plan SET " . $sets,
            [
                $newid,
                $_SESSION["pid"],
                $_SESSION["authProvider"],
                $care_user_val,
                $_SESSION["encounter"],
                $userauthorized,
                $code_val,
                $codetext_val,
                $description_val,
                $start_date_val,
                $end_date_val,
                $proposed_date_val,
                $plan_status_val,
                $care_plan_type_val,
                $note_relations,
                $reason_code,
                $reason_status,
                $reason_description,
                $reason_low,
                $reason_high
            ]
        );
    endforeach;
}

formHeader("Redirecting....");
formJump();
formFooter();

function parse_note($note)
{
    $result = preg_match_all("/\{\|([^\]]*)\|}/", (string) $note, $matches);
    return json_encode($matches[1]);
}
