<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use Exception;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;

class LogProperties
{
    /**
     * @var string
     */
    public $rxsynclog;
    /**
     * @var
     */
    public $messageid;
    /**
     * @var string
     */
    private $method;
    /**
     * @var false|string
     */
    private $key;
    /**
     * @var false|string
     */
    private $enc_key;
    /**
     * @var false|string
     */
    private $weno_admin_email;
    /**
     * @var false|string
     */
    private $weno_admin_password;
    /**
     * @var CryptoGen
     */
    private $cryptoGen;
    /**
     * @var string
     */
    private $iv;
    /**
     * @var Container
     */
    private $container;
    /**
     * @var TransmitProperties
     */
    private $provider;

    /**
     * LogProperties constructor.
     */
    public function __construct()
    {
        $this->cryptoGen = new CryptoGen();
        $this->method = "aes-256-cbc";
        $this->rxsynclog = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/weno/logsync.csv";
        $logDir = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/weno";
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        $this->enc_key = $this->cryptoGen->decryptStandard($GLOBALS['weno_encryption_key'] ?? '');
        $this->key = substr(hash('sha256', $this->enc_key, true), 0, 32);
        $this->iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        $this->weno_admin_email = $GLOBALS['weno_admin_username'] ?? '';
        $this->weno_admin_password = $this->cryptoGen->decryptStandard($GLOBALS['weno_admin_password'] ?? '');
    }

    public function fetchLogSyncDates(): array
    {
        $wenoLog = new WenoLogService();
        $workday = date("l");
        if ($workday == 'Monday') {
            $from = date("Y-m-d", strtotime("-2 days"));
        } else {
            $from = date("Y-m-d", strtotime("yesterday"));
        }
        // Retrieve the last sync date
        $lastSync = sqlQuery("SELECT * FROM `weno_download_log` WHERE value='Sync Report' AND status = 'Success' ORDER BY `id` DESC LIMIT 1;")['created_at'];
        $lastSync = date("Y-m-d", strtotime($lastSync));
        // Ensure `to` is today and `from` defaults to yesterday or earlier
        $to = date("Y-m-d", strtotime("tomorrow"));
        // Ensure `from` and `to` are within a 7-day range
        $sevenDaysAgo = date("Y-m-d", strtotime("$to -6 days"));
        // If last sync is before the current `from`, adjust the `from` date
        if ($lastSync < $from) {
            $from = $lastSync;
            if ($from < $sevenDaysAgo) {
                $from = $sevenDaysAgo;
                $status = xl("Syncing Report was behind. Syncing last 7 days.");
                $wenoLog->insertWenoLog("Sync Report (May be first sync)", $status);
            }
        }
        // Ensure the date range is not greater than 7 days
        if ((strtotime($to) - strtotime($from)) > (7 * 86400)) { // 86400 seconds in a day
            $to = date("Y-m-d", strtotime("$from +6 days"));
        }
        return [$from, $to];
    }

        /**
     * @return string
     */
    public function logEpcs(): string
    {
        $email['email'] = $this->weno_admin_email;
        $prov_pass = $this->weno_admin_password; // gets the password stored for the
        $md5 = md5($prov_pass); // hash the current password

        $syncDates = $this->fetchLogSyncDates();
        $from = $syncDates[0];
        $to = $syncDates[1];

        $p = [
            "UserEmail" => $email['email'],
            "MD5Password" => $md5,
            "FromDate" => $from,
            "ToDate" => $to,
            "ResponseFormat" => "CSV"
        ];
        $plaintext = json_encode($p); // json encode email and password

        if ($this->enc_key && $md5) {
            return base64_encode(openssl_encrypt($plaintext, $this->method, $this->key, OPENSSL_RAW_DATA, $this->iv));
        } else {
            return "error";
        }
    }

    /**
     * @return string
     */
    public function logReview()
    {
        $email = $this->getProviderEmail();
        $prov_pass = $this->getProviderPassword();
        $md5 = md5($prov_pass);                       // hash the current password

        $p = [
            "UserEmail" => $email['email'],
            "MD5Password" => $md5
        ];
        $plaintext = json_encode($p);                //json encode email and password
        if ($this->enc_key && $md5) {
            return base64_encode(openssl_encrypt($plaintext, $this->method, $this->key, OPENSSL_RAW_DATA, $this->iv));
        } else {
            return "error";
        }
    }

    /**
     * @throws Exception
     */
    public function logSync($tasked = 'background')
    {
        $wenoLog = new WenoLogService();
        $provider_info['email'] = $this->weno_admin_email;
        $logurlparam = $this->logEpcs();
        $syncLogs = "https://online.wenoexchange.com/en/EPCS/DownloadNewRxSyncDataVal?useremail=";
        if ($logurlparam == 'error') {
            echo TransmitProperties::styleErrors(xlt("Cipher failure check encryption key"));
            error_log("Cipher failure check encryption key", time());
            exit;
        }

        $urlOut = $syncLogs . urlencode($provider_info['email']) . "&data=" . urlencode($logurlparam);

        $wenoLog->insertWenoLog("Sync Report", "Sent a Sync Request", $urlOut);

        $ch = curl_init($urlOut);
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $rpt = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($statusCode == 200) {
            file_put_contents($this->rxsynclog, $rpt);
            $isError = $wenoLog->scrapeWenoErrorHtml($rpt);
            if ($isError['is_error']) {
                $error = $isError['messageText'];
                error_log('Prescription download failed: ' . errorLogEscape($error));
                EventAuditLogger::instance()->newEvent("prescriptions_log", $_SESSION['authUser'], $_SESSION['authProvider'], 0, ($error));
                // if background task then return false
                if ($tasked == 'background') {
                    $wenoLog->insertWenoLog("Sync Report", $error);
                    return false;
                }
                if ($tasked == 'downloadLog') {
                    $wenoLog->insertWenoLog("Sync Report", $error);
                    echo(js_escape($error));
                    exit;
                }
                $wenoLog->insertWenoLog("Sync Report", "Invalid Prescriber Credentials Hint:see previous errors.");
                echo(js_escape($error));
                return false;
            }
        } else {
            // yes record failures.
            EventAuditLogger::instance()->newEvent("prescriptions_log", $_SESSION['authUser'], $_SESSION['authProvider'], 0, ("$statusCode"));
            error_log("Prescription download failed: errorLogEscape($statusCode)");
            $wenoLog->insertWenoLog("Sync Report", "Failed http_error_$statusCode");
            return false;
        }

        if (file_exists($this->rxsynclog)) {
            $log = new LogImportBuild();
            $rtn = $log->buildPrescriptionInserts();
            if ($rtn != true) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * @return string|array
     */
    public function getProviderEmail(): string|array
    {
        if ($_SESSION['authUser']) {
            $provider_info = ['email' => $GLOBALS['weno_provider_email']];
            if (!empty($provider_info['email'])) {
                return $provider_info;
            } else {
                $error = xlt("Weno Prescriber email address is missing. Go to User settings Email to add Weno Prescriber's weno registered email address");
                error_log(errorLogEscape($error));
                TransmitProperties::echoError($error);
            }
        } elseif ($GLOBALS['weno_admin_username'] ?? false) {
            $provider_info["email"] = $GLOBALS['weno_admin_username'];
            return $provider_info;
        } else {
            $error = xlt("Weno Prescriber email address is missing. Go to User settings Weno tab to add Weno Prescriber's weno registered email address");
            error_log($error);
            echo TransmitProperties::styleErrors($error);
            exit;
        }
        return '';
    }

    /**
     * @return mixed
     */
    public function getProviderPassword(): mixed
    {
        if ($_SESSION['authUser']) {
            if (!empty($GLOBALS['weno_provider_password'])) {
                return $this->cryptoGen->decryptStandard($GLOBALS['weno_provider_password']);
            } else {
                echo xlt('Weno Prescriber Password is missing');
                error_log("Weno Prescriber Password is missing");
                die;
            }
        } elseif ($GLOBALS['weno_admin_password']) {
            return $this->cryptoGen->decryptStandard($GLOBALS['weno_admin_password']);
        } else {
            error_log("Admin password not set");
            exit;
        }
    }
}
