<?php

/**
 * dictation save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
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
    $newid = formSubmit("form_dictation", $_POST, ($_GET["id"] ?? null), $userauthorized);
    addForm($encounter, "Speech Dictation", $newid, "dictation", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement("update form_dictation set pid = ?,groupname=?,user=?,authorized=?,activity=1, date = NOW(), dictation=?, additional_notes=? where id=?", array($_SESSION["pid"],$_SESSION["authProvider"],$_SESSION["authUser"],$userauthorized,$_POST["dictation"],$_POST["additional_notes"],$_GET["id"]));
}

formHeader("Redirecting....");
formJump();
formFooter();
