<?php

/**
 * Executes the background service for billing, which sends EDI claims
 * directly to the x-12 partner (if enabled)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

set_time_limit(0);

$ignoreAuth = true;
$fake_register_globals = false;
$sanitize_all_escapes = true;

require_once __DIR__.'/../interface/globals.php';

/**
 * This function is called by background services,
 * reads the x12_remote_tracker table and sends
 * files to x12 partners that are in the 'waiting'
 * status.
 */
function start_X12_SFTP()
{
    if ($GLOBALS['auto_sftp_claims_to_x12_partner']) {
        \OpenEMR\Billing\BillingProcessor\X12RemoteTracker::sftpSendWaitingFiles();
    }
}

