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

use OpenEMR\Billing\BillingProcessor\X12RemoteTracker;

/**
 * This function is called by background services,
 * reads the x12_remote_tracker table and sends
 * files to x12 partners that are in the 'waiting'
 * status.
 */
function start_X12_SFTP()
{
    if ($GLOBALS['auto_sftp_claims_to_x12_partner']) {
        X12RemoteTracker::sftpSendWaitingFiles();
    }
}
