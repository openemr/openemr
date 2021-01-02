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
use OpenEMR\Common\Crypto;

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


    public function __construct()
    {
        $this->rxsynclog = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/logsync.csv";
    }
    
    /**
     * @return string
     */
    public function logEcps()
    {
        $provider = new TransmitProperties();
        $email = $provider->getProviderEmail();
        $cryptoGen = new Crypto\CryptoGen();
        $enc_key = $cryptoGen->decryptStandard($GLOBALS['weno_encryption_key']);  // key pulled from the globals
        $prov_pass =  $provider->getProviderPassword();                // gets the password stored for the
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
        $method = "aes-256-cbc";
        $key = substr(hash('sha256', $enc_key, true), 0, 32);
        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        if ($enc_key && $md5) {
            return base64_encode(openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv));;
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
            return date ("F d Y", filemtime($this->rxsynclog));
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
}