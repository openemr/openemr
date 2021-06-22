<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

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
    private $credentialsInformation;

    /**
     * LogProperties constructor.
     */
    public function __construct()
    {
        $this->container = new Container();
        $this->cryptoGen = new CryptoGen();
        $this->method = "aes-256-cbc";
        $this->rxsynclog = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/logsync.csv";
        $this->enc_key = $this->cryptoGen->decryptStandard($GLOBALS['weno_encryption_key']);
        $this->key = substr(hash('sha256', $this->enc_key, true), 0, 32);
        $this->iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        $this->credentialInformation = $this->providerCredentials();
    }

    private function providerCredentials()
    {
        //find the first Weno user password in the settings and get ID and password
        $sql = "select ue.id, ue.email, us.setting_value from users ue JOIN user_settings us ON us.setting_user = ue.id where us.setting_label = 'global:weno_provider_password'";
        $credentials = sqlQuery($sql);
        return $credentials;
    }

    /**
     * @return string
     */
    public function logEcps()
    {
        //$credentialInformation = $this->providerCredentials();                                                   // get the credentials to be used
        $email = $this->credentialInformation;                                                                    // an array is returned
        $pass_value = $this->credentialInformation;
        $prov_pass =  $this->cryptoGen->decryptStandard($pass_value['setting_value']);                // decrypt the password
        $md5 = md5($prov_pass);                                                                                  // hash the password
        $workday = date("l");
        //This is to cover working on Saturday but not on Sunday.
        //Checking Saturday for any prescriptions that were written.
        if ($workday == 'Monday') {
            $yesterday = date("Y-m-d", strtotime("-2 day"));
        } else {
            $yesterday = date("Y-m-d", strtotime("yesterday"));
        }

        $p = [
            "UserEmail" => $email['email'],
            "MD5Password" => $md5,
            "FromDate" => $yesterday,
            "ToDate" => $yesterday,
            "ResponseFormat" => "CSV"
        ];
        $plaintext = json_encode($p);                //json encode email and password
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
        $email = $this->credentialInformation;
        $pass_value = $this->credentialInformation;
        $prov_pass =  $this->cryptoGen->decryptStandard($pass_value['setting_value']);      // gets the password stored for the
        $md5 = md5($prov_pass);                                                             // hash the current password

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
    public function logSync()
    {
        $provider_info = $this->credentialInformation;

        $logurlparam = $this->logEcps();
        $syncLogs = "https://online.wenoexchange.com/en/EPCS/DownloadNewRxSyncDataVal?useremail=";
        if ($logurlparam == 'error') {
            echo xlt("Cipher failure check encryption key");
            error_log("Cipher failure check encryption key", time());
            exit;
        }
        //**warning** do not add urlencode to  $provider_info['email'] per Weno design
        $urlOut = $syncLogs . $provider_info['email'] . "&data=" . urlencode($logurlparam);

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
            $logstring = "prescription log import initiated successfully";
            EventAuditLogger::instance()->newEvent("prescritions_log", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$logstring");
        } else {
            EventAuditLogger::instance()->newEvent("prescritions_log", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$statusCode");
        }

        if (file_exists($this->rxsynclog)) {
            $log = new LogImportBuild();
            $log->buildInsertArray();
        }
    }
}
