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

use OpenEMR\Common\Crypto\CryptoGen;

class TransmitProperties
{
    private $payload;
    private $patient;
    private $provider_email;
    private $provider_pass;
    private $locid;
    private $vitals;
    private $subscriber;
    private $ncpdp;
    private $cryptoGen;

    /**
     * AdminProperties constructor.
     */
    public function __construct()
    {
             $this->cryptoGen = new CryptoGen();
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
        $phoneprimary = preg_replace('/\D+/', '', $this->patient['phone_cell']);
        //create json array
        $wenObj = [];
        $wenObj['UserEmail'] = $this->provider_email['email'];
        $wenObj['MD5Password'] = md5($this->provider_pass);
        $wenObj['LocationID'] = $this->locid['weno_id'];
        $wenObj['TestPatient'] = $mode;
        $wenObj['PatientType'] = 'Human';
        $wenObj['OrgPatientID'] = $this->patient['pid'];
        $wenObj['LastName'] = $this->patient['lname'];

        $wenObj['FirstName'] = $this->patient['fname'];
        $wenObj['Gender'] = $gender[0];
        $wenObj['DateOfBirth'] = $this->patient['dob'];
        $wenObj['AddressLine1'] = $this->patient['street'];
        $wenObj['City'] = $this->patient['city'];
        $wenObj['State'] = $this->patient['state'];
        $wenObj['PostalCode'] = $this->patient['postal_code'];
        $wenObj['CountryCode'] = "US";
        $wenObj['PrimaryPhone'] = $phoneprimary;
        $wenObj['SupportsSMS'] = 'Y';

        $wenObj['PatientHeight'] = substr($this->vitals['height'], 0, -3);
        $wenObj['PatientWeight'] = substr($this->vitals['weight'], 0, -3);
        $wenObj['HeightWeightObservationDate'] = $heighDate[0];
        $wenObj["ResponsiblePartySameAsPatient"] = 'Y';
        $wenObj['PatientLocation'] = "Home";
        return json_encode($wenObj);
    }

    /**
     * @return mixed
     */
    public function getProviderEmail()
    {
        $provider_info = sqlQuery("select email from users where username=? ", [$_SESSION["authUser"]]);
        if (empty($provider_info['email'])) {
            echo xlt('Provider email address is missing. Go to address book to add providers email address');
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
        $locid = sqlQuery("select name, street, city, state, postal_code, phone, fax, weno_id from facility where id = ?", [$_SESSION['facilityId']]);

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
        //get patient data if in an encounter
        //Since the transmitproperties is called in the logproperties
        //need to check to see if in an encounter or not. Patient data is not required to view the Weno log
        if (empty($_SESSION['encounter'])) {
            die("please select an encounter");
        }
        $missing = 0;
        $patient = sqlQuery("select title, fname, lname, mname, street, state, city, email, phone_cell, postal_code, dob, sex, pid from patient_data where pid=?", [$_SESSION['pid']]);
        if (empty($patient['fname'])) {
            echo xlt("First Name Missing") . "<br>";
            ++$missing;
        }
        if (empty($patient['lname'])) {
            echo xlt("Last Name Missing")  . "<br>";
            ++$missing;
        }
        if (empty($patient['dob'])) {
            echo xlt("Date of Birth Missing") . "<br>";
            ++$missing;
        }
        if (empty($patient['sex'])) {
            echo xlt("Gender Missing") . "<br>";
            ++$missing;
        }
        if (empty($patient['postal_code'])) {
            echo xlt("Zip Code Missing") . "<br>";
            ++$missing;
        }
        if (empty($patient['street'])) {
            echo xlt("Street Address incomplete Missing") . "<br>";
            ++$missing;
        }
        if ($missing > 0) {
            die('Pleasae add the missing data and try again');
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
        $enc_key = $this->cryptoGen->decryptStandard($GLOBALS['weno_encryption_key']);
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
        $uid = $_SESSION['authUserID'];
        $sql = "select setting_value from user_settings where setting_user = ? and setting_label = 'global:weno_provider_password'";
        $prov_pass = sqlQuery($sql, [$uid]);
        if (!empty($prov_pass['setting_value'])) {
            return $this->cryptoGen->decryptStandard($prov_pass['setting_value']);
        } else {
            echo xlt('Provider Password is missing');
            die;
        }
    }

    /**
     * @return mixed
     */
    private function getVitals()
    {
        $vitals = sqlQuery("select date, height, weight from form_vitals where pid = ? ORDER BY id DESC", [$_SESSION["pid"]]);
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
