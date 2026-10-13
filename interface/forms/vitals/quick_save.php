<?php
require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

header('Content-Type: application/json');

try {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

    $pid = $_POST['pid'] ?? null;
    $encounter = $_POST['encounter'] ?? null;

    if (empty($pid) || empty($encounter)) {
        echo json_encode(["success" => false, "error" => "Missing Context"]);
        exit;
    }

    $date = date("Y-m-d H:i:s");
    
    // Parse values safely to prevent MySQL strict mode errors (numeric fields need NULL, not empty strings)
    $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
    $height = !empty($_POST['height']) ? (float)$_POST['height'] : null;
    $pulse  = !empty($_POST['pulse'])  ? (int)$_POST['pulse']   : null;
    $bps    = !empty($_POST['bps'])    ? (int)$_POST['bps']     : null;
    $bpd    = !empty($_POST['bpd'])    ? (int)$_POST['bpd']     : null;
    
    // 1. Insert into form_vitals. CRITICAL: activity MUST be 1 for the report to display it.
    $v_sql = "INSERT INTO form_vitals (date, pid, activity, weight, height, pulse, bps, bpd) 
              VALUES (?, ?, 1, ?, ?, ?, ?, ?)";
    
    $v_id = sqlInsert($v_sql, [$date, $pid, $weight, $height, $pulse, $bps, $bpd]);

    // 2. Register the form to the encounter
    $f_sql = "INSERT INTO forms (date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir) 
              VALUES (?, ?, 'Vitals', ?, ?, ?, ?, 1, 0, 'vitals')";
    
    sqlStatement($f_sql, [
        $date, 
        $encounter, 
        $v_id, 
        $pid, 
        $_SESSION['authUser'] ?? 'admin', 
        $_SESSION['authProvider'] ?? 'Default'
    ]);

    echo json_encode(["success" => true]);
} catch (Throwable $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
exit;