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
     * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
     */

    $ignoreAuth=true;
    $_SERVER['HTTP_HOST']   = 'default'; //change for multi-site

    require_once(dirname(__FILE__)."/../../interface/globals.php");
    require_once(dirname(__FILE__)."/../patient.inc");
    require_once(dirname(__FILE__)."/../log.inc");
    require_once(dirname(__FILE__)."/API.php");

    $MedEx = new MedExApi\MedEx('MedExBank.com');
    $logged_in = $MedEx->login();
if (($logged_in) && (!empty($_POST['callback_key']))) {
    $data                   = json_decode($_POST, true);
    $token                  = $logged_in['token'];
    $response['callback']   = $MedEx->callback->receive($data);
    $response['practice']   = $MedEx->practice->sync($token);
    $response['campaigns']  = $MedEx->campaign->events($token);
    $response['generate']   = $MedEx->events->generate($token, $response['campaigns']['events']);
    $response['success']    = "200";
    header('Content-type: application/json');
    echo json_encode($response);
    exit;
}
    echo "Not logged in: ";
    echo $MedEx->getLastError();
    exit;
