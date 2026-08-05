<?php

/**
 * library/MedEx/MedEx_background.php
 *
 * This file is executed as a background service. It synchronizes data with MedExBank.com,
 *      delivering events to be processed to MedEx AND receiving message outcomes from MedEx.
 *      MedEx_background.php receives message responses asynchronously.
 *      Consider setting this service to run every 5 minutes in background_services:
 *          e.g. every 5 minutes ==> active=1, execute_interval=5
 *
 *      While anyone is logged into your OpenEMR instance, this will run.
 *      Consider adding a cronjob to run this file also, so messaging for upcoming events
 *      will run even if no one is logged in, e.g. the office is closed/vacation etc.
 *
 *      e.g. to run this service every 4 hours via cron:
 *          0 0,4,8,12,16,20 * * * /usr/bin/env php ROOT_DIR/bin/console background:services run --name=MedEx
 *      For the full set of active services, generate a ready-to-install crontab with:
 *          php bin/console background:services crontab
 *      You can append " >> /tmp/medex.log " to output success/failure to a log file.
 *
 * @package   OpenEMR
 * @subpackage MedEx
 *
 * @link      https://www.open-emr.org
 * @link      https://www.MedExBank.com
 * @author    MedEx <support@MedExBank.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017 MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$ignoreAuth = true;

require_once(__DIR__ . "/../../interface/globals.php");
require_once(__DIR__ . "/API.php");
require_once(__DIR__ . "/../patient.inc.php");

function start_MedEx(): void
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
