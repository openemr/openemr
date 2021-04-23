<?php

/**
 * /library/MedEx/medex_background.php
 *
 * This file is executed as a background service. It synchronizes data with MedExBank.com,
 *      delivering events to be processed to MedEx AND receiving message outcomes from MedEx.
 *      MedEx_background.php receives message responses asynchronously.
 *      Consider setting this service to run q5 minutes in background_grounds:
 *          eg. every 5 minutes ==> active=1, execute_interval=5
 *
 *      While anyone is logged into your OpenEMR instance, this will run.
 *      Consider adding a cronjob to run this file also, so messaging for upcoming events
 *      will run even if no one is logged in, eg. the office is closed/vacation etc
 *
 *      eg. to run this file every 4 hours, crontab -e
 *      0 0,4,8,12,16,20 * * * /usr/bin/env php ROOT_DIR/library/ajax/execute_background_services.php
 *      You can add " >> /tmp/medex.log " to output success/failure to a log file.
 *
 * @package MedEx
 * @link    http://www.MedExBank.com
 * @author  MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2017 MedEx <support@MedExBank.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$ignoreAuth = true;

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/API.php");
require_once(dirname(__FILE__) . "/../patient.inc.php");

function start_MedEx()
{
    $MedEx = new MedExApi\MedEx('MedExBank.com');
    $logged_in = $MedEx->login('2');
    if ($logged_in) {
        echo "Completed @ " . date("Y-m-d H:i:s") . "\n";
    } else {
        echo $MedEx->getLastError();
        echo "Failed @ " . date("Y-m-d H:i:s") . "\n";
    }
}
