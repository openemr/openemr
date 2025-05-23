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
     * Uses multiple authentication steps for security:
     *  - local API_key
     *  - MedEx username
     *  - session token
     *  - MedEx generated token
     *
     * @package MedEx
     * @author MedEx <support@MedExBank.com>
     * @link    http://www.MedExBank.com
     * @copyright Copyright (c) 2017 MedEx <support@MedExBank.com>
     * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
     */

    $ignoreAuth = true;
    $_SERVER['HTTP_HOST']   = 'default'; //change for multi-site

    require_once(dirname(__FILE__) . "/../../interface/globals.php");
    require_once(dirname(__FILE__) . "/../patient.inc.php");
    require_once(dirname(__FILE__) . "/API.php");

if (!empty($_POST['callback_key'])) {
    $MedEx = new MedExApi\MedEx('MedExBank.com');
    $response = $MedEx->login('2');
    header('Content-type: application/json');
    echo json_encode($response);
    exit;
}
    echo "Not logged in: ";
    echo $MedEx->getLastError();
    exit;
