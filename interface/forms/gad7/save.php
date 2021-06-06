<?php

/**
 * Gad-7 save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ruth Moulton <moulton ruth@muswell.me.uk>
 * @copyright Copyright (c) 2021 ruth moulton <ruth@muswell.me.uk>
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_gad7", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "GAD-7 Form", $newid, "gad7", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement(
        "update form_gad7 set pid = ?,
            groupname = ?,
            user = ?,
            authorized = ?,
            activity = 1,
            nervous_score=?,
            control_worry_score=?,
            worry_score=?,
            relax_score=?,
            restless_score=?,
            irritable_score=?,
            fear_score=?,
            difficulty=?
            where id=? ",
        [
            $_SESSION["pid"],
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            $_POST["nervous_score"],
            $_POST["control_worry_score"],
            $_POST["worry_score"],
            $_POST["relax_score"],
             $_POST["restless_score"],
            $_POST["irritable_score"],
            $_POST["fear_score"],
            $_POST["difficulty"],
            $_GET["id"]
        ]
    );
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
