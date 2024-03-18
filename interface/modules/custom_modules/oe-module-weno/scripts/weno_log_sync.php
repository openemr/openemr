<?php

/**
 * @package    OpenEMR
 * @link       http://www.open-emr.org
 * @author     Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author     Jerry Padgett <sjpadgett@gmail.com>
 * @copyright  Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright  Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Modules\WenoModule\Services\LogProperties;
use OpenEMR\Modules\WenoModule\Services\WenoPharmaciesJson;
use OpenEMR\Modules\WenoModule\Services\WenoValidate;

function downloadWenoPharmacy()
{
    $cryptoGen = new CryptoGen();
    $localPharmacyJson = new WenoPharmaciesJson($cryptoGen);
    // ensure a valid key is set.
    // first check if the key is valid. If not, request a new key and set it.
    $wenoValidate = new WenoValidate();
    $isKey = $wenoValidate->validateAdminCredentials(true);
    error_log('Background Initiated Encryption Verify returned.' . text($isKey ?: 'false'));
    // Check if the background service is active. Intervals are set to once a day
    $value = $localPharmacyJson->checkBackgroundService();

    if ($value == 'active' || $value == 'live') {
        error_log('Background Initiated Pharmacy Download Started.');

        $status = $localPharmacyJson->storePharmacyDataJson();

        EventAuditLogger::instance()->newEvent(
            "pharmacy_background",
            $_SESSION['authUser'],
            $_SESSION['authProvider'],
            1,
            "Background Initiated Pharmacy Download Completed with Status:"  . text($status)
        );
        error_log('Background Initiated Weno Pharmacies download completed with status:' . text($status));
        die;
    }
}

/**
 * @throws Exception
 */
function downloadWenoPrescriptionLog(): void
{
    $logSync = new LogProperties();
    if (!$logSync->logSync()) {
        error_log("Background services failed for prescription log.");
    }
}
