<?php
    /**
     * forms/TeleVisit/new.php
     *
     *
     * @package   OpenEMR
     * @link      https://www.open-emr.org
     * @author    Ray Magauran <rmagauran@gmail.com>
     * @copyright Copyright (c) 2020 Raymond Magauran <rmagauran@gmail.com>
     * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
     */
    
    
    require_once("../../globals.php");
    require_once("$srcdir/api.inc");
    require_once("$srcdir/forms.inc");
    
    $form_name = "TeleHealth Visit";
    $table_name = "form_telemed";
    $form_folder = "telemed";
    formHeader("Form: ".$form_name);
    $returnurl = 'encounter_top.php';
    
    $pid = $_REQUEST['pid'];
    
    if (!$pid) {
        $pid = $_SESSION['pid'];
    } else {
        $_SESSION['pid'] = $pid;
    }
    
    if (!$user) {
        $user = $_SESSION['authUser'];
    }
    
    if (!$group) {
        $group = $_SESSION['authProvider'];
    }
    
    if (!$_SESSION['encounter']) {
        $encounter = date("Ymd");
    } else {
        $encounter=$_SESSION['encounter'];
    }
//    var_dump($_SESSION);die();
    $query = "select * from form_encounter where pid =? and encounter= ?";
    $encounter_data = sqlQuery($query, array($pid,$encounter));
    $encounter_date = $encounter_data['date'];

    $query = "SELECT * " .
        "FROM form_encounter AS fe, forms AS f WHERE " .
        "fe.pid = ? AND fe.date = ? AND " .
        "f.formdir = ? AND f.encounter = fe.encounter AND f.encounter=? AND f.deleted = 0";
    $erow = sqlQuery($query, array($pid, $encounter_date, $form_folder, $encounter));
 
    if ($erow['form_id'] > '0') {
        formHeader("Redirecting....");
        formJump('./view_form.php?formname=telemed&id='.urlencode($erow['form_id']).'&pid='.urlencode($pid));
        formFooter();
        exit;
    }
    $id = '1';
    $provider_id = $encounter_data['provider_id'];
    if ($provider_id < '1') {
        $query = "select * from openemr_postcalendar_events where pc_pid=? and pc_eventDate=?";
        $appt = sqlQuery($query, array($pid, $encounter_date));
        $new_provider_id = $appt['pc_aid'];
        if (($new_provider_id < '1') || (!$new_provider_id)) {
            $new_provider_id = $_SESSION['authId'];
        }
        $provider_id = $new_provider_id;
    }
    $values = [];
    $values['encounter'] = $encounter_data['encounter'];
    $values['provider_id'] = $provider_id;
    $newid = formSubmit($table_name, $values, $id, $provider_id);
    addForm($encounter, "TeleHealth Visit", $newid, "telemed", $pid, $userauthorized);
    
    formHeader("Redirecting....");
    formJump('./view_form.php?formname='.$form_folder.'&id='.attr($newid).'&pid='.attr($pid));
    formFooter();
    exit;
