<?php

/**
 * TransmitProperties class.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

use OpenEMR\Common\Crypto;

class TransmitProperties
{
    private $payload;
    private $patient;
    private $provider_email;
    private $provider_pass;
    private $locid;
    private $vitals;
    private $subscriber;
    private $pid;
    private $ncpdp;

    /**
     * AdminProperties constructor.
     */
    public function __construct()
    {
                   $this->ncpdp = $this->getPharmacy();
                $this->vitals = $this->getVitals();
               $this->patient = $this->getPatientInfo();
        $this->provider_email = $this->getProviderEmail();
         $this->provider_pass = $this->getProviderPassword();
                 $this->locid = $this->getFacilityInfo();
               $this->payload = $this->createJsonObject();
            $this->subscriber = $this->getSubscriber();
    }

    /**
     * @return false|string
     */
    public function createJsonObject()
    {
        //default is testing mode
        $testing = isset($GLOBALS['weno_rx_enable_test']);
        if ($testing) {
            $mode = 'Y';
        } else {
            $mode = 'N';
        }
        $gender = $this->patient['sex'];
        $heighDate = explode(" ", $this->vitals['date']);
        if ($this->subscriber == 'self') {
            $relationship = 'Y';
        } else {
            $relationship = 'N';
        }
        //create json array
        $wenObj = [
                            'UserEmail' => $this->provider_email['email'],
                          'MD5Password' => md5($this->provider_pass),
                           "LocationID" => $this->locid['weno_id'],
                          "TestPatient" => $mode,
                          'PatientType' => 'Human',
                         'OrgPatientID' => $this->patient['pid'],
                             'LastName' => $this->patient['lname'],
                            'FirstName' => $this->patient['fname'],
                           'MiddleName' => $this->patient['mname'],
                               'Prefix' => 'NA',
                               'Suffix' => 'NA',
                               "Gender" => $gender[0],
                          "DateOfBirth" => $this->patient['dob'],
                         "AddressLine1" => $this->patient['street'],
                         "AddressLine2" => "NA",
                                 "City" => $this->patient['city'],
                                "State" => $this->patient['state'],
                           "PostalCode" => $this->patient['postal_code'],
                          "CountryCode" => "US",
                         "PrimaryPhone" => $this->patient['phone_cell'],
                          "SupportsSMS" => "Y",
                         "PatientEmail" => $this->patient['email'],
                        "PatientHeight" => $this->vitals['height'],
                        "PatientWeight" => $this->vitals['weight'],
          "HeightWeightObservationDate" => $heighDate[0],
        "ResponsiblePartySameAsPatient" => 'Y',
                      "PatientLocation" => "Home",
                 "PrimaryPharmacyNCPCP" => $this->ncpdp,
             "AlternativePharmacyNCPCP" => $this->ncpdp
        ];

        return json_encode($wenObj);
    }

    /**
     * @return mixed
     */
    public function getProviderEmail()
    {
        $provider_info = sqlQuery("select email from users where username=? ", [$_SESSION["authUser"]]);
        if (empty($provider_info['email'])) {
            echo xlt('Provider email address is missing');
            exit;
        } else {
            return $provider_info;
        }
    }

    /**
     * @return mixed
     */
    public function getFacilityInfo()
    {
        $locid = sqlQuery("select name, street, city, state, postal_code, phone, fax, weno_id from facility where id = ?", [isset($_SESSION['facilityId'])]);

        if (empty($locid['weno_id'])) {
            //if not in an encounter then get the first facility location id as default
            $default_facility = sqlQuery("select name, street, city, state, postal_code, phone, fax, weno_id from facility order by id asc limit 1");

            if (empty($default_facility)) {
                echo xlt('Facility ID is missing');
                exit;
            } else {
                return $default_facility;
            }
        }
        return $locid;
    }

    /**
     * @return mixed
     */
    private function getPatientInfo()
    {
        //get patient data
        $patient = sqlQuery("select title, fname, lname, mname, street, state, city, email, phone_cell, postal_code, dob, sex, pid from patient_data where pid = ?", [isset($_SESSION['pid'])]);
        if (empty($patient['fname']) ||
            empty($patient['lname']) ||
            empty($patient['dob']) ||
            empty($patient['sex']) ||
            empty($patient['postal_code']) ||
            empty($patient['street']) ||
            empty($patient['email'])
        ) {
            echo xlt('Patient data is incomplete phone or ')
                . ", "
                . xlt('first last name') . ", "
                . xlt('gender email') . ", "
                . xlt('zip code') . ", "
                . xlt('date of birth or address');
            exit;
        }
        return $patient;
    }

    /**
     * @return string
     * New Rx
     */
    public function cipherpayload()
    {
        $cipher = "aes-256-cbc"; // AES 256 CBC cipher
        $cryptoGen = new Crypto\CryptoGen();
        $enc_key = $cryptoGen->decryptStandard($GLOBALS['weno_encryption_key']);
        if ($enc_key) {
            $key = substr(hash('sha256', $enc_key, true), 0, 32);
            $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
            $ciphertext = base64_encode(openssl_encrypt($this->payload, $cipher, $key, OPENSSL_RAW_DATA, $iv));
            return $ciphertext;
        } else {
            return "error";
        }
    }

    /**
     * @return mixed
     */
    public function getProviderPassword()
    {
        $cryptoGen = new Crypto\CryptoGen();
        $uid = $_SESSION['authUserID'];
        $sql = "select setting_value from user_settings where setting_user = ? and setting_label = 'global:weno_provider_password'";
        $prov_pass = sqlQuery($sql, [$uid]);
        if ($prov_pass['setting_value']) {
            return $cryptoGen->decryptStandard($prov_pass['setting_value']);
        } else {
            echo xlt('Password is missing'); die;
        }
    }

    /**
     * @return mixed
     */
    private function getVitals()
    {
        $vitals = sqlQuery("select date, height, weight from form_vitals where pid = ? ORDER BY id DESC", [isset($_SESSION["pid"])]);
        return $vitals;
    }

    private function getSubscriber()
    {
        $sql = sqlQuery("select subscriber_relationship from insurance_data where pid = ? and type = 'primary'", [$_SESSION['pid']]);
        return $sql['subscriber_relationship'];
    }

    /**
     * @return mixed
     */
    public function getPharmacy()
    {
        $sql = "SELECT p.ncpdp FROM pharmacies p JOIN patient_data pd ON p.id = pd.pharmacy_id WHERE pd.pid = ? ";
        $give = sqlQuery($sql, [$_SESSION['pid']]);
        return $give['ncpdp'];
    }
}
