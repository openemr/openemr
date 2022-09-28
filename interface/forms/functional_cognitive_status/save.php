<?php

/**
 * Functional cognitive status form save.php.
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

$id = (int) (isset($_GET['id']) ? $_GET['id'] : '');
$code = $_POST["code"];
$code_text = $_POST["codetext"];
$code_date = $_POST["code_date"];
$code_des = $_POST["description"];
$code_activity = $_POST["activity1"];

if ($id && $id != 0) {
    sqlStatement("DELETE FROM `form_functional_cognitive_status` WHERE id=? AND pid = ? AND encounter = ?", array($id, $_SESSION["pid"], $_SESSION["encounter"]));
    $newid = $id;
} else {
    $res2 = sqlStatement("SELECT MAX(id) as largestId FROM `form_functional_cognitive_status`");
    $getMaxid = sqlFetchArray($res2);
    if ($getMaxid['largestId']) {
        $newid = $getMaxid['largestId'] + 1;
    } else {
        $newid = 1;
    }

    addForm($encounter, "Functional and Cognitive Status Form", $newid, "functional_cognitive_status", $_SESSION["pid"], $userauthorized);
}

$code_text = array_filter($code_text);

if (!empty($code_text)) {
    foreach ($code_text as $key => $codeval) :
        $sets = "id = ?,
            pid = ?,
            groupname = ?,
            user = ?,
            encounter = ?,
            authorized = ?,
            activity = ?,
            code = ?,
            codetext = ?,
            description= ?,
            date = ?";
        sqlStatement(
            "INSERT INTO form_functional_cognitive_status SET $sets",
            [
                $newid,
                $_SESSION["pid"],
                $_SESSION["authProvider"],
                $_SESSION["authUser"],
                $_SESSION["encounter"],
                $userauthorized,
                $code_activity[$key],
                $code[$key],
                $code_text[$key],
                $code_des[$key],
                $code_date[$key]
            ]
        );
    endforeach;
}

formHeader("Redirecting....");
formJump();
formFooter();
