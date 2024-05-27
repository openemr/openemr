<?php

/*
 * Work/School Note Form save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2004-2005 Nikolai Vitsyn
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

/*
 * name of the database table associated with this form
 */
$table_name = "form_note";

if ($encounter == "") {
    $encounter = date("Ymd");
}

$_POST['date_of_signature'] = DateToYYYYMMDD($_POST['date_of_signature']);

if ($_GET["mode"] == "new") {
    $newid = formSubmit($table_name, $_POST, $_GET["id"] ?? '', $userauthorized);
    addForm($encounter, "Work/School Note", $newid, "note", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    $success = formUpdate($table_name, $_POST, $_GET["id"], $userauthorized);
}

formHeader("Redirecting....");
formJump();
formFooter();
