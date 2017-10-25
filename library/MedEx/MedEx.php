<?php
/**
 * /library/MedEx/MedEx.php
 *
 * This file is the callback service for MedEx
 *
 * @package MedEx
 * @author MedEx <support@MedExBank.com>
 * @link    http://www.MedExBank.com
 * @copyright Copyright (c) 2017 MedEx <support@MedExBank.com>
 * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

$ignoreAuth=true;
$_SERVER['REQUEST_URI'] = '';
$_SERVER['SERVER_NAME'] = 'oculoplasticsllc.com'; //PUT your server name here
$_SERVER['HTTP_HOST']   = 'default'; //for multi-site i believe

require_once(dirname(__FILE__)."/../../interface/globals.php");
require_once(dirname(__FILE__)."/../patient.inc");
require_once(dirname(__FILE__)."/../log.inc");
require_once(dirname(__FILE__)."/API.php");

$MedEx = new MedExApi\MedEx('MedExBank.com');

$logged_in = $MedEx->login();
$log['Time']= date(DATE_RFC2822);
$log['action'] = "MedEx.php fired";

if ($logged_in) {
    if ((!empty($_POST['callback']))&&($_SERVER['REMOTE_ADDR']=='66.175.210.18')) {
        $data = json_decode($_POST, true);
        $response = $MedEx->callback->receive($data);
        echo $response;
        exit;
    }
    $token      = $logged_in['token'];
    $response   = $MedEx->practice->sync($token);
    $campaigns  = $MedEx->campaign->events($token);
    $response   = $MedEx->events->generate($token, $campaigns['events']);
} else {
    echo "not logged in";
    echo $MedEx->getLastError();
}
echo "200";
exit;
