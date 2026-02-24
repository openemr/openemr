<?php

/**
 * forms/eye_mag/new.php
 *
 * The page shown when the user requests a new form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <magauran@MedFetch.com>
 * @copyright Copyright (c) 2016 Raymond Magauran <magauran@MedFetch.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Session\SessionUtil;

$form_name = "Eye Exam";
$table_name = "form_eye_base";
$form_folder = "eye_mag";
include_once("../../forms/" . $form_folder . "/php/" . $form_folder . "_functions.php");
formHeader("Form: " . $form_name);
$returnurl = 'encounter_top.php';

$pid = $_REQUEST['pid'] ?? null;

if (!$pid) {
    $pid = $_SESSION['pid'];
} else {
    SessionUtil::setSession('pid', $pid);
}

if (empty($user)) {
    $user = $_SESSION['authUser'];
}

if (empty($group)) {
    $group = $_SESSION['authProvider'];
}

// $encounter is already in scope from globals.php / load_form.php.
// Guard against missing encounter (the scenario that caused #10844):
if (!isset($encounter) || (int) $encounter === 0) { // @phpstan-ignore cast.int ($encounter comes from global scope)
    formHeader(xlt('Error'));
    echo '<div class="alert alert-danger">' .
        xlt('No active encounter. Please select or create an encounter first.') .
        '</div>';
    formFooter();
    exit;
}

$encounterAttr = attr($encounter); // @phpstan-ignore argument.type ($encounter validated above)

$query = "select * from form_encounter where pid =? and encounter= ?";
$encounter_data = sqlQuery($query, [$pid,$encounter]);
$encounter_date = $encounter_data['date'];

$query = "SELECT * " .
    "FROM form_encounter AS fe, forms AS f WHERE " .
    "fe.pid = ? AND fe.date = ? AND " .
    "f.formdir = ? AND f.encounter = fe.encounter AND f.encounter=? AND f.deleted = 0";
$erow = sqlQuery($query, [$pid, $encounter_date, $form_folder, $encounter]);

if (!empty($erow['form_id']) && ($erow['form_id'] > '0')) {
    formHeader("Redirecting....");
    formJump('./view_form.php?formname=' . $form_folder . '&id=' . attr($erow['form_id']) . '&pid=' . attr($pid) . '&encounter=' . $encounterAttr);
    formFooter();
    exit;
} else {
    $id = (!empty($erow2['count'])) ? $erow2['count']++ : null; //erow2['count'] is not defined and formSubmit doesn't use it since we are inserting...
    $providerid = findProvider(attr($pid), $encounter);
    $newid = formSubmit($table_name, $_POST, $id, $providerid);
    $tables = ['form_eye_hpi','form_eye_ros','form_eye_vitals',
        'form_eye_acuity','form_eye_refraction','form_eye_biometrics',
        'form_eye_external', 'form_eye_antseg','form_eye_postseg',
        'form_eye_neuro','form_eye_locking'];
    foreach ($tables as $table) {
        $sql = "INSERT INTO " . $table . " set id=?, pid=?";
        sqlStatement($sql, [$newid, $pid]);
    }
    $sql = "insert into forms (date, encounter, form_name, form_id, pid, " .
            "user, groupname, authorized, formdir) values (NOW(),?,?,?,?,?,?,?,?)";
    $answer = sqlInsert($sql, [$encounter,$form_name,$newid,$pid,$user,$group,$providerid,$form_folder]);
    // Keep the session encounter in sync with the value just written to the
    // forms table.  view.php reads $encounter exclusively from the session
    // (via globals.php) and compares it against the stored encounter for IDOR
    // protection; if they diverge the guard fires a 404 "Form not found".
    SessionUtil::setSession('encounter', $encounter);
}

    formHeader("Redirecting....");
    formJump('./view_form.php?formname=' . $form_folder . '&id=' . attr($newid) . '&pid=' . attr($pid) . '&encounter=' . $encounterAttr);
    formFooter();
    exit;
