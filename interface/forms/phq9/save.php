<?php

/**
 * PHQ-9 save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ruth Moulton <moulton ruth@muswell.me.uk>
 * @copyright Copyright (c) 2021 ruth moulton <ruth@muswell.me.uk>
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
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
    $newid = formSubmit("form_phq9", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "PHQ-9 Form", $newid, "phq9", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement(
        "update form_phq9 set pid = ?,
            groupname = ?,
            user = ?,
            authorized = ?,
            activity = 1,
            interest_score=?,
            hopeless_score=?,
            sleep_score=?,
            fatigue_score=?,
            appetite_score=?,
            failure_score=?,
            focus_score=?,
	    psychomotor_score=?,
	    suicide_score=?,
            difficulty=?
            where id=? ",
        [
            $_SESSION["pid"],
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            $_POST["interest_score"],
            $_POST["hopeless_score"],
            $_POST["sleep_score"],
            $_POST["fatigue_score"],
            $_POST["appetite_score"],
            $_POST["failure_score"],
            $_POST["focus_score"],
        $_POST["psychomotor_score"],
        $_POST["suicide_score"],
            $_POST["difficulty"],
            $_GET["id"]
        ]
    );
}

formHeader("Redirecting....");
formJump();
formFooter();
