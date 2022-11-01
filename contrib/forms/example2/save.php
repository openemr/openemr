<?php

/*
 * This saves the submitted form
 */
/**
 * example2 save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

/** CHANGE THIS - name of the database table associated with this form **/
$table_name = "form_example";

/** CHANGE THIS name to the name of your form **/
$form_name = "My Example Form";

/** CHANGE THIS to match the folder you created for this form **/
$form_folder = "example";


if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    /* NOTE - for customization you can replace $_POST with your own array
     * of key=>value pairs where 'key' is the table field name and
     * 'value' is whatever it should be set to
     * ex)   $newrecord['parent_sig'] = $_POST['sig'];
     *       $newid = formSubmit($table_name, $newrecord, $_GET["id"], $userauthorized);
     */

    /* save the data into the form's own table */
    $newid = formSubmit($table_name, $_POST, $_GET["id"], $userauthorized);

    /* link the form to the encounter in the 'forms' table */
    addForm($encounter, $form_name, $newid, $form_folder, $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    /* update existing record */
    $success = formUpdate($table_name, $_POST, $_GET["id"], $userauthorized);
}

formHeader("Redirecting....");
formJump();
formFooter();
