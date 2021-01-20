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
    public $id;
    public $uuid;
    public $patient_id;
    public $pharmacy_id;
    public $date_added;
    public $provider_id;
    public $drug;
    public $rxnorm_drugcode;
    public $form;
    public $quantity;
    public $substitute;
    public $refills;
    public $filled_date;
    public $note;
    public $active;
    public $user;
    public $prescriptionguid;
    private $internal_user_id;
    public $rxsynclog;
    public $messageid;
    private $method;
    private $key;
    private $enc_key;
    private $cryptoGen;
    private $iv;
    private $container;
    private $provider;

    public function __construct()
    {
        $this->container = new Container();
        $this->cryptoGen = new CryptoGen();
        $this->method = "aes-256-cbc";
        $this->rxsynclog = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/logsync.csv";
        $this->enc_key = $this->cryptoGen->decryptStandard($GLOBALS['weno_encryption_key']);
        $this->key = substr(hash('sha256', $this->enc_key, true), 0, 32);
        $this->iv = chr(0x1) . chr(0x2) . chr(0x3) . chr(0x5) . chr(0x7) . chr(0x9) . chr(0x0) . chr(0x1) . chr(0x2) . chr(0x3) . chr(0x5) . chr(0x7) . chr(0x9) . chr(0x0) . chr(0x1) . chr(0x2);
        $this->provider = $this->container->getTransmitproperties();
    }

    /**
     * @return string
     */
    public function logEcps()
    {
        $email = $this->provider->getProviderEmail();
        $prov_pass =  $this->provider->getProviderPassword();                // gets the password stored for the
        $md5 = md5($prov_pass);                       // hash the current password
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
        $email = $this->provider->getProviderEmail();
        $prov_pass =  $this->provider->getProviderPassword();                // gets the password stored for the
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

    public function insertPrescriptions(): string
    {
        $sql = 'INSERT INTO prescriptions SET '
            . 'id = ?, '
            . 'active = ?, '
            . 'date_added = ?, '
            . 'patient_id = ?, '
            . 'drug = ?, '
            . 'form = ?, '
            . 'quantity = ?, '
            . 'refills = ?, '
            . 'substitute = ?,'
            . 'note = ?, '
            . 'rxnorm_drugcode = ?, '
            . 'external_id = ?, '
            . 'indication =? ';

        $values = [
            $this->id,
            $this->active,
            $this->date_added,
            $this->patient_id,
            $this->drug,
            $this->form,
            $this->quantity,
            $this->refills,
            $this->substitute,
            $this->note,
            $this->rxnorm_drugcode,
            $this->provider_id,
            $this->prescriptionguid
        ];

        try {
            sqlInsert($sql, $values);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function doesLogFileExist()
    {
        if (file_exists($this->rxsynclog)) {
            return date("F d Y", filemtime($this->rxsynclog));
        } else {
            return false;
        }
    }

    public function checkMessageId()
    {
        $sql = "select count(*) as count from prescriptions where indication = ?";
        $entry = sqlQuery($sql, [$this->messageid]);
        return $entry['count'];
    }

    public function logSync()
    {
        $provider_info = $this->provider->getProviderEmail();

        /**
         * checks to see if the file exist and if it does was it put there today?
         * The idea behind this is to automate the log information download and import into the database.
         * This should only execute once a day no matter how many times it is called.
         * The idea was to include in the index file to be executed when the prescription called.
         */
        $today = date("F d Y");
        $filedate = $this->doesLogFileExist();
        //die if the dates match or the file does not exist
        if ($today !== $filedate) {
            $logurlparam = $this->logEcps();
            $syncLogs = "https://online.wenoexchange.com/en/EPCS/DownloadNewRxSyncDataVal?useremail=";
            if ($logurlparam == 'error') {
                echo xlt("Cipher failure check encryption key");
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
                $logstring = "prescrition log import initiated successfully";
                EventAuditLogger::instance()->newEvent("prescritions_log", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$logstring");
            } else {
                EventAuditLogger::instance()->newEvent("prescritions_log", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$statusCode");
            }

            $l = 0;
            if (file_exists($this->rxsynclog)) {
                $records = fopen($this->rxsynclog, "r");

                while (!feof($records)) {
                    $line = fgetcsv($records);

                    if ($l <= 2) {
                        $l++;
                        continue;
                    }
                    if (!isset($line[1])) {
                        continue;
                    }
                    if (isset($line[4])) {
                        $this->messageid = isset($line[4]) ? $line[4] : null;
                        $is_saved = $this->checkMessageId();
                        if ($is_saved > 0) {
                            continue;
                        }
                    }
                    if (!empty($line)) {
                        $pr = isset($line[2]) ? $line[2] : null;
                        $provider = explode(":", $pr);
                        $windate = isset($line[16]) ? $line[16] : null;
                        $idate = substr(trim($windate), 0, -5);
                        $idate = explode(" ", $idate);
                        $idate = explode("/", $idate[0]);
                        $year = isset($idate[2]) ? $idate[2] : null;
                        $month = isset($idate[0]) ? $idate[0] : null;
                        $day = isset($idate[1]) ? $idate[1] : null;
                        $idate = $year . '-' . $month . '-' . $day;
                        $ida = filter_var($idate, FILTER_SANITIZE_NUMBER_INT);
                        $p = isset($line[1]) ? $line[1] : null;
                        $pid = filter_var($p, FILTER_SANITIZE_NUMBER_INT);
                        $r = isset($line[22]) ? $line[22] : null;
                        $refills = filter_var($r, FILTER_SANITIZE_NUMBER_INT);

                        $this->id = '';
                        $this->active = 1;
                        $this->date_added = $ida;
                        $this->patient_id = $pid;
                        $this->drug = isset($line[11]) ? str_replace('"', '', $line[11]) : null;
                        $this->form = isset($line[19]) ? $line[19] : null;
                        $this->quantity = isset($line[18]) ? $line[18] : null;
                        $this->refills = $refills;
                        $this->substitute = isset($line[14]) ? $line[14] : null;
                        $this->note = isset($line[21]) ? $line[21] : null;
                        $this->rxnorm_drugcode = isset($line[12]) ? $line[12] : null;
                        $this->provider_id = $provider[0];
                        $this->prescriptionguid = isset($line[4]) ? $line[4] : null;
                        $this->insertPrescriptions();

                        ++$l;
                    }
                }
                fclose($records);
            }
        }
    }
}
