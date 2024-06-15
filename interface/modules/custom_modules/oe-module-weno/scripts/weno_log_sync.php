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
use OpenEMR\Modules\WenoModule\Services\WenoLogService;
use OpenEMR\Modules\WenoModule\Services\WenoPharmaciesJson;
use OpenEMR\Modules\WenoModule\Services\WenoValidate;

/**
 * Download Weno Pharmacy data called by background service.
 */
function downloadWenoPharmacy(): void
{
    $wenoLog = new WenoLogService();
    $wenoValidate = new WenoValidate();
    $localPharmacyJson = new WenoPharmaciesJson(new CryptoGen());

    $isKey = $wenoValidate->validateAdminCredentials(true, "pharmacy");
    if ((int)$isKey >= 998) {
        handleDownloadError("Background Initiated Pharmacy download attempt failed. Internet problem!");
    }
    if ($isKey === false) {
        requireGlobals();
    }

    $localPharmacyJson->checkBackgroundService();
    $wenoLog->insertWenoLog("pharmacy", "Download started");
    error_log('Background Initiated Pharmacy Download Started.');

    // The breadwinner!
    $status = $localPharmacyJson->storePharmacyData();

    EventAuditLogger::instance()->newEvent("pharmacy_background", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "Background Initiated Pharmacy Download Imported:" . text($status) . " Pharmacies");
    error_log('Background Initiated Weno pharmacies Updated:' . text($status) . " Pharmacies");
}

/**
 * Download Weno Prescription log.
 *
 * @throws Exception
 */
function downloadWenoPrescriptionLog(): void
{
    $wenoValidate = new WenoValidate();
    $isKey = $wenoValidate->validateAdminCredentials(true);

    if ((int)$isKey >= 998) {
        handleDownloadError("Prescription download attempt failed. Internet problem!");
    }

    if ($isKey === false) {
        requireGlobals();
    }

    $logSync = new LogProperties();
    if (!$logSync->logSync('background')) {
        error_log("Background services failed for prescription log.");
    }
}

/**
 * Handle download errors.
 *
 * @param string $errorMessage
 */
function handleDownloadError(string $errorMessage)
{
    EventAuditLogger::instance()->newEvent(
        "pharmacy_background",
        $_SESSION['authUser'],
        $_SESSION['authProvider'],
        1,
        ($errorMessage)
    );

    error_log(errorLogEscape($errorMessage));
    die;
}

/**
 * Require global variables.
 */
function requireGlobals(): void
{
    // Key has been reset, reload globals
    // This is the problem when using globals for anything in a function.
    // They need to be reloaded when dynamically changed or JIT global values.
    // TODO: We need to address this in the future.
    require_once dirname(__DIR__, 4) . "/globals.php";
}
