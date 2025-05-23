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

if (!$_SESSION['encounter']) {
    $encounter = date("Ymd");
} else {
    $encounter = $_SESSION['encounter'];
}

$query = "select * from form_encounter where pid =? and encounter= ?";
$encounter_data = sqlQuery($query, array($pid,$encounter));
$encounter_date = $encounter_data['date'];

$query = "SELECT * " .
    "FROM form_encounter AS fe, forms AS f WHERE " .
    "fe.pid = ? AND fe.date = ? AND " .
    "f.formdir = ? AND f.encounter = fe.encounter AND f.encounter=? AND f.deleted = 0";
$erow = sqlQuery($query, array($pid, $encounter_date, $form_folder, $encounter));

if (!empty($erow['form_id']) && ($erow['form_id'] > '0')) {
    formHeader("Redirecting....");
    formJump('./view_form.php?formname=' . $form_folder . '&id=' . attr($erow['form_id']) . '&pid=' . attr($pid));
    formFooter();
    exit;
} else {
    $id = (!empty($erow2['count'])) ? $erow2['count']++ : null; //erow2['count'] is not defined and formSubmit doesn't use it since we are inserting...
    $providerid = findProvider(attr($pid), $encounter);
    $newid = formSubmit($table_name, $_POST, $id, $providerid);
    $tables = array('form_eye_hpi','form_eye_ros','form_eye_vitals',
        'form_eye_acuity','form_eye_refraction','form_eye_biometrics',
        'form_eye_external', 'form_eye_antseg','form_eye_postseg',
        'form_eye_neuro','form_eye_locking');
    foreach ($tables as $table) {
        $sql = "INSERT INTO " . $table . " set id=?, pid=?";
        sqlStatement($sql, array($newid, $pid));
    }
    $sql = "insert into forms (date, encounter, form_name, form_id, pid, " .
            "user, groupname, authorized, formdir) values (NOW(),?,?,?,?,?,?,?,?)";
    $answer = sqlInsert($sql, array($encounter,$form_name,$newid,$pid,$user,$group,$providerid,$form_folder));
}

    formHeader("Redirecting....");
    formJump('./view_form.php?formname=' . $form_folder . '&id=' . attr($newid) . '&pid=' . attr($pid));
    formFooter();
    exit;
