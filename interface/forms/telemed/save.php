<?php
/**
 * TeleHealth Visit save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ray Magauran <magauran@medexbank.com>
 * @copyright Copyright (c) 2020 Ray Magauran <magauran@medexbank.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
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

$sets = "date = CURDATE(),
    pid = ?,
    encounter = ? ,
    user = ?,
    groupname = ?,
    authorized = ?,
    activity = 1,
    tm_duration = ?,
    tm_subj = ?,
    tm_obj = ?,
    tm_imp = ?,
    tm_plan = ?";
/*
echo "<pre>".$userauthorized;
var_dump($_SESSION);
var_dump($_REQUEST);
die();
*/
if (empty($id)) {
    $newid = sqlInsert(
        "INSERT INTO form_telemed SET $sets",
        [
            $_SESSION["pid"],
            $encounter,
            $userauthorized,
            $_SESSION["authGroup"],
            $_SESSION["authUserID"],
            $_POST["tm_duration"],
            $_POST["tm_subj"],
            $_POST["tm_obj"],
            $_POST["tm_imp"],
            $_POST["tm_plan"]
        ]
    );
    addForm($encounter, "telemed", $newid, "telemed", $pid, $userauthorized);
} else {
    $sets = "date = CURDATE(),
    pid = ?,
    user = ?,
    groupname = ?,
    authorized = ?,
    activity = 1,
    tm_duration = ?,
    tm_subj = ?,
    tm_obj = ?,
    tm_imp = ?,
    tm_plan = ?";
    sqlStatement(
        "UPDATE form_telemed SET $sets WHERE id = ?",
        [
            $_SESSION["pid"],
            $userauthorized,
            $_SESSION["authGroup"],
            $_SESSION["authUserID"],
            $_POST["tm_duration"],
            $_POST["tm_subj"],
            $_POST["tm_obj"],
            $_POST["tm_imp"],
            $_POST["tm_plan"],
            $id
        ]
    );
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
