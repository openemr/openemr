<?php

/**
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once dirname(__FILE__, 2) . '/interface/globals.php';

use OpenEMR\Billing\X12ClaimRepost;
use OpenEMR\Common\Crypto\CryptoGen;

function resetClaimStatus() :string
{
    $cryptgen = new CryptoGen();

    // Get user name and password
    $host = X12ClaimRepost::x12Url() ?? null;
    $user = X12ClaimRepost::x12Username() ?? null;
    $xPass = X12ClaimRepost::x12Password() ?? null;
    $pass = $cryptgen->decryptStandard($xPass['x12_sftp_pass']) ?? null;

    // Parse Host and Port

    $client = new X12ClaimRepost($host['path'], $user['x12_sftp_login'], $pass);

    if ($client == 'success') {
        //reset the batches to waiting to be sent when connection comes back online.
        X12ClaimRepost::updateStatus();
        return '<button class="btn-success">' . xlt('Connection to clearinghouse') . '</button>';
    }
        return '<button class="btn-danger">' . xlt('Failed to connect to Clearinghouse') . '</button>';
}
