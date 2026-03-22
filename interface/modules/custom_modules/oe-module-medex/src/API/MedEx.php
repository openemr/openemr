<?php

    /**
     * MedEx Module Callback Service
     *
     * This file is the callback service for MedEx to update local DB with new responses in real-time.
     * It must be accessible remotely to receive data synchronously.
     *
     * ⚠️ LEGACY EXCEPTION: This is the ONE remaining endpoint that uses the legacy
     * MedExApi\MedEx class (api/login).  It exists because the callback flow is
     * bi-directional: MedEx server POSTs a callback_key here, and we echo it back
     * to api/login for verification.  api/oemr/login does not handle callback_key.
     * Once callback_key support is added to api/oemr, this should be migrated.
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
     * Uses multiple authentication steps for security:
     *  - local API_key
     *  - MedEx username
     *  - session token
     *  - MedEx generated token
     *
     * @package   OpenEMR
     * @link      https://www.open-emr.org
     * @author    MedEx <support@MedExBank.com>
     * @copyright Copyright (c) 2017-2026 MedEx <support@MedExBank.com>
     * @license   Proprietary - All Rights Reserved
     */

use OpenEMR\Core\OEGlobalsBag;

$ignoreAuth = true;
$_SERVER['HTTP_HOST'] = 'default'; //change for multi-site

require_once(__DIR__ . "/../../../../../interface/globals.php");

$globalsBag = OEGlobalsBag::getInstance();
require_once($globalsBag->get('srcdir') . "/patient.inc.php");
require_once(__DIR__ . "/API.php");

$MedEx = new MedExApi\MedEx($globalsBag->get('medex_api_host') ?? 'MedExBank.com');

if (!empty($_POST['callback_key'])) {
    $response = $MedEx->login('2');
    header('Content-type: application/json');
    echo json_encode($response);
    exit;
}

echo "Not logged in: ";
echo $MedEx->getLastError();
exit;
