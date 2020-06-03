<?php

/**
 * Clinical instructions form save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
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
$instruction = $_POST["instruction"];

if ($id && $id != 0) {
    sqlStatement("UPDATE form_clinical_instructions SET instruction =? WHERE id = ?", array($instruction, $id));
} else {
    $newid = sqlInsert("INSERT INTO form_clinical_instructions (pid,encounter,user,instruction) VALUES (?,?,?,?)", array($pid, $encounter, $_SESSION['authUser'], $instruction));
    addForm($encounter, "Clinical Instructions", $newid, "clinical_instructions", $pid, $userauthorized);
}

formHeader("Redirecting....");
formJump();
formFooter();
