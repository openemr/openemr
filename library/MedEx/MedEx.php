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
$_SERVER['HTTP_HOST']   = 'default'; //change for multi-site

require_once(dirname(__FILE__)."/../../interface/globals.php");
require_once(dirname(__FILE__)."/../patient.inc");
require_once(dirname(__FILE__)."/../log.inc");
require_once(dirname(__FILE__)."/API.php");
require_once(dirname(__FILE__)."/../formatting.inc.php");

$MedEx = new MedExApi\MedEx('MedExBank.com');

$logged_in = $MedEx->login($_POST['callback_key']);
if (($logged_in) && (!empty($_POST['callback_key']))) {
    $data = json_decode($_POST, true);
    $response = $MedEx->callback->receive($data);
    if (!empty($response['success'])) {
        $token      = $logged_in['token'];
        $response   = $MedEx->practice->sync($token);
        $campaigns  = $MedEx->campaign->events($token);
        $response   = $MedEx->events->generate($token, $campaigns['events']);
        echo "200";
        exit;
    }
}
echo "Not logged in: ";
echo $MedEx->getLastError();
exit;
