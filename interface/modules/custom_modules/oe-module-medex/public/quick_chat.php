<?php

/**
 * Quick Secure Chat Link Sender (Patient Demographics Widget)
 * 
 * Embedded in patient demographics to quickly send secure chat link
 * Can be called via AJAX or as standalone page
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ray Magauran
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../../globals.php");
require_once($GLOBALS['srcdir'] . "/patient.inc.php");

use OpenEMR\Common\Acl\AclMain;

// Check ACL
if (!AclMain::aclCheckCore('patients', 'demo')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$pid = $_GET['pid'] ?? $_POST['pid'] ?? '';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (empty($pid)) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => 'Patient ID required']);
    } else {
        echo "Patient ID required";
    }
    exit;
}

// Get patient data
$patient = sqlQuery("SELECT pid, fname, lname, phone_cell, email, allow_patient_portal FROM patient_data WHERE pid = ?", [$pid]);

if (!$patient) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => 'Patient not found']);
    } else {
        echo "Patient not found";
    }
    exit;
}

// Check if portal enabled
if ($patient['allow_patient_portal'] !== 'YES') {
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => 'Patient portal not enabled for this patient']);
    } else {
        echo "Patient portal access not enabled. Please enable in Demographics.";
    }
    exit;
}

// If POST request, redirect to full secure chat page with patient pre-filled
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['send'])) {
    $redirectUrl = $GLOBALS['web_root'] . '/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php?pid=' . urlencode($pid);
    
    if ($isAjax) {
        echo json_encode(['success' => true, 'redirect' => $redirectUrl]);
    } else {
        header('Location: ' . $redirectUrl);
    }
    exit;
}

// Otherwise show quick info widget
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'patient' => [
        'pid' => $patient['pid'],
        'name' => $patient['fname'] . ' ' . $patient['lname'],
        'phone_cell' => $patient['phone_cell'] ?? '',
        'email' => $patient['email'] ?? '',
        'portal_enabled' => true
    ],
    'send_url' => $GLOBALS['web_root'] . '/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php?pid=' . urlencode($pid)
]);
