<?php
/**
 * /library/MedEx/medex_background.php
 *
 * This file is executed as a background service. It synchronizes data with MedExBank.com,
 *      delivering events to be processed to MedEx AND receiving message outcomes from MedEx.
 *      MedEx.php receives data synchronously - when a response is received at MedExBank.com, the practice receives
 *      that information directly.  However not every server is always online.
 *      MedEx_background.php receives message reponses asynchronously.  It checks for all messages received since it was last
 *      executed (+ another 24 hours to be sure) and if any new ones are found,
 *      it adds them to the local database (medex_outgoing table).
 *
 * MedEx_background: manually set execution frequency in DB table "background services".
 *      Suggest running this file every 29 minutes (default) or less frequently,
 *      but at least once each morning before 8AM localtime on work days.
 *    eg. every 29 minutes ==> active=1, execute_interval=29 (default installed values)
 *        four times a day ==> active=1, execute_interval=360 (60 minutes x 6)
 *
 * @package MedEx
 * @link    http://www.MedExBank.com
 * @author  MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2017 MedEx <support@MedExBank.com>
 * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

$ignoreAuth=true;

require_once(dirname(__FILE__)."/../../interface/globals.php");
require_once(dirname(__FILE__)."/API.php");
require_once(dirname(__FILE__)."/../patient.inc");
require_once(dirname(__FILE__)."/../log.inc");

function start_MedEx()
{
    $MedEx = new MedExApi\MedEx('MedExBank.com');
    $logged_in = $MedEx->login();
    if ($logged_in) {
        $token      = $logged_in['token'];
        $MedEx->practice->sync($token);
        $campaigns  = $MedEx->campaign->events($token);
        $MedEx->events->generate($token, $campaigns['events']);
        echo "200";
    } else {
        echo $MedEx->getLastError();
    }
    echo "401";
}
