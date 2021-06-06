<?php

/**
 * Clinical Notes form save.php
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
$note_user = $_POST["user"];
$count = $_POST["count"];
$clinical_notes_type = $_POST['clinical_notes_type'];

if ($id && $id != 0) {
    sqlStatement("DELETE FROM `form_clinical_notes` WHERE id=? AND pid = ? AND encounter = ?", array($id, $_SESSION["pid"], $_SESSION["encounter"]));
    $newid = $id;
} else {
    $res2 = sqlStatement("SELECT MAX(id) as largestId FROM `form_clinical_notes`");
    $getMaxid = sqlFetchArray($res2);
    if ($getMaxid['largestId']) {
        $newid = $getMaxid['largestId'] + 1;
    } else {
        $newid = 1;
    }

    addForm($encounter, "Clinical Notes Form", $newid, "clinical_notes", $_SESSION["pid"], $userauthorized);
}

$count = array_filter($count);
if (!empty($count)) {
    foreach ($count as $key => $codeval) :
        $code_val = $code[$key] ?: 0;
        $codetext_val = $code_text[$key] ?: null;
        $description_val = $code_des[$key] ?: null;
        $clinical_notes_type_val = $clinical_notes_type[$key] ?: null;
        $note_user_val = $note_user[$key] ?: $_SESSION["authUser"];
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
            clinical_notes_type = ?";
        sqlStatement(
            "INSERT INTO form_clinical_notes SET " . $sets,
            [
                $newid,
                $_SESSION["pid"],
                $_SESSION["authProvider"],
                $note_user_val,
                $_SESSION["encounter"],
                $userauthorized,
                $code_val,
                $codetext_val,
                $description_val,
                $code_date[$key],
                $clinical_notes_type_val
            ]
        );
    endforeach;
}

formHeader("Redirecting....");
formJump();
formFooter();
