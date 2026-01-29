<?php

/**
 * /library/MedEx/MedEx.php
 *
 * This file is the callback service for MedEx to update local DB with new responses in real-time.
 * It must be accessible remotely to receive data synchronously.
 *
 * It is not required if a practice is happy syncing using background services only.
 *
 * The advantages using this file are:
 *  1.  Real time updates of patient responses == synchronous receiver
 *  2.  Reduced need to run the MedEx_background service
 *          - MedEx_background syncs DB responses asynchronously, ie only when run (default = every 29 minutes)
 *          - It consumes resources and may affecting performance of the server if run too often.
 *              (see MedEx_background.php for configuration examples)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017 MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;

$ignoreAuth = true;
$_SERVER['HTTP_HOST'] = 'default'; //change for multi-site

require_once(__DIR__ . "/../../interface/globals.php");
require_once(__DIR__ . "/../patient.inc.php");
require_once(__DIR__ . "/API.php");

header('Content-type: application/json');

$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Verify MedEx is enabled - return 404 to hide endpoint existence
if (OEGlobalsBag::getInstance()->get('medex_enable') !== '1') {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}

// Validate callback_key is present
$callbackKey = $_POST['callback_key'] ?? '';
if ($callbackKey === '') {
    EventAuditLogger::getInstance()->newEvent('medex-webhook', '', '', 0, "Missing callback key from $remoteAddr");
    http_response_code(400);
    echo json_encode(['error' => 'Missing callback key']);
    exit;
}

// Process the sync - MedEx validates the callback_key server-side
$MedEx = new MedExApi\MedEx('MedExBank.com');
$response = $MedEx->login('2');

// Return only success/failure status, not sensitive tokens
if (isset($response['success']) && $response['success']) {
    EventAuditLogger::getInstance()->newEvent('medex-webhook', '', '', 1, "Sync successful from $remoteAddr");
    echo json_encode(['success' => true]);
} else {
    $error = $MedEx->getLastError() ?: 'Sync failed';
    EventAuditLogger::getInstance()->newEvent('medex-webhook', '', '', 0, "Sync failed from $remoteAddr: $error");
    echo json_encode(['error' => $error]);
}
exit;
