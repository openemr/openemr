<?php
// MUST BE FIRST. No headers before this!
require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

header('Content-Type: application/json');

try {
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

    $pid = $_POST['pid'] ?? null;
    if (empty($pid)) {
        echo json_encode(["success" => false, "error" => "No Patient ID provided."]);
        exit;
    }

    $encounter = $_POST['encounter'] ?? null;

    // 1. THE STALE ENCOUNTER CHECK
    // If the frontend gave us an encounter, verify it belongs to TODAY.
    if (!empty($encounter)) {
        $check = sqlQuery("SELECT encounter FROM form_encounter WHERE pid = ? AND encounter = ? AND DATE(date) = CURDATE()", [$pid, $encounter]);
        if (empty($check['encounter'])) {
            // It's an old encounter from a previous visit. Clear it so we create a new one.
            $encounter = null;
        }
    }

    // 2. See if there is ANY encounter for today already
    if (empty($encounter)) {
        $row = sqlQuery("SELECT encounter FROM form_encounter WHERE pid = ? AND DATE(date) = CURDATE() ORDER BY date DESC LIMIT 1", [$pid]);
        if (!empty($row['encounter'])) {
            $encounter = $row['encounter'];
        }
    }

    // 3. STILL empty? That means absolutely no visits today. Create a brand new one!
    if (empty($encounter)) {
        $enc_row = sqlQuery("SELECT MAX(encounter) AS max_enc FROM form_encounter");
        $encounter = ($enc_row['max_enc'] ?? 0) + 1;
        
        $facility = $_SESSION['login_facility'] ?? 1;
        
        sqlStatement("INSERT INTO form_encounter (pid, encounter, date, reason, facility_id, sensitivity) VALUES (?, ?, NOW(), 'Quick Vitals', ?, 'normal')", [$pid, $encounter, $facility]);
    }

    echo json_encode(["success" => true, "encounter" => (int)$encounter]);

} catch (Throwable $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
exit;