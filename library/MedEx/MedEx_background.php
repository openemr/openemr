<?php
/**
 * /library/MedEx/medex_background.php
 *
 * This file is executed as a background service
 * either through ajax or cron.
 *
 * @package MedEx
 * @link    http://www.MedExBank.com
 * @author  MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2017 MedEx <support@MedExBank.com>
 * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

$ignoreAuth=true;
$_SERVER['REQUEST_URI'] = '';
$_SERVER['HTTP_HOST']   = 'default'; //for multi-site I believe

require_once(dirname(__FILE__)."/../../interface/globals.php");
require_once(dirname(__FILE__)."/API.php");
require_once(dirname(__FILE__)."/../patient.inc");
require_once(dirname(__FILE__)."/../log.inc");
require_once(dirname(__FILE__)."/../formatting.inc.php");
require_once(dirname(__FILE__) ."/../log.inc");
   
function start_MedEx()
{
    $hb = new MedExApi\MedEx('MedExBank.com');
    $logged_in = $hb->login();
    if ($logged_in) {
        $token      = $logged_in['token'];
        $response   = $hb->practice->sync($token);
        $campaigns  = $hb->campaign->events($token);
        $response   = $hb->events->generate($token, $campaigns['events']);
    } else {
        echo $hb->getLastError();
    }
}
