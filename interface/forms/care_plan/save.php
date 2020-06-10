<?php

/**
 * Care plan form save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if (!$encounter) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}

$id = 0 + (isset($_GET['id']) ? $_GET['id'] : '');
$code = $_POST["code"];
$code_text = $_POST["codetext"];
$code_date = $_POST["code_date"];
$code_des = $_POST["description"];
$count = $_POST["count"];
$care_plan_type = $_POST['care_plan_type'];

if ($id && $id != 0) {
    sqlStatement("DELETE FROM `form_care_plan` WHERE id=? AND pid = ? AND encounter = ?", array($id, $_SESSION["pid"], $_SESSION["encounter"]));
    $newid = $id;
} else {
    $res2 = sqlStatement("SELECT MAX(id) as largestId FROM `form_care_plan`");
    $getMaxid = sqlFetchArray($res2);
    if ($getMaxid['largestId']) {
        $newid = $getMaxid['largestId'] + 1;
    } else {
        $newid = 1;
    }

    addForm($encounter, "Care Plan Form", $newid, "care_plan", $_SESSION["pid"], $userauthorized);
}

$count = array_filter($count);
if (!empty($count)) {
    foreach ($count as $key => $codeval) :
        $code_val = $code[$key] ? $code[$key] : 0;
        $codetext_val = $code_text[$key] ? $code_text[$key] : 'NULL';
        $description_val = $code_des[$key] ? $code_des[$key] : 'NULL';
        $care_plan_type_val = $care_plan_type[$key] ? $care_plan_type[$key] : 'NULL';
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
            date =  ?,
            care_plan_type = ?";
        sqlStatement(
            "INSERT INTO form_care_plan SET " . $sets,
            [
                $newid,
                $_SESSION["pid"],
                $_SESSION["authProvider"],
                $_SESSION["authUser"],
                $_SESSION["encounter"],
                $userauthorized,
                $code_val,
                $codetext_val,
                $description_val,
                $code_date[$key],
                $care_plan_type_val
            ]
        );
    endforeach;
}

formHeader("Redirecting....");
formJump();
formFooter();
