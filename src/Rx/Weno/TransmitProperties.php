<?php

/**
 * TransmitProperties class.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
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
    private $pharmacy;

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
              $this->pharmacy = $this->getPharmacy();
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

        $wenObj['PrimaryPharmacyNCPCP'] = $this->pharmacy['primary'];
        $wenObj['AlternativePharmacyNCPCP'] = $this->pharmacy['alternate'];

        //add insurance
        return json_encode($wenObj);
    }

    /**
     * @return mixed
     */
    public function getProviderEmail()
    {
        $provider_info = sqlQuery("select email from users where username=? ", [$_SESSION["authUser"]]);
        if (empty($provider_info['email'])) {
            echo xlt('Provider email address is missing. Go to [Admin > Address Book] to add providers email address');
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
        $locid = sqlQuery("select name, street, city, state, postal_code, phone, fax, weno_id from facility where id = ?", [$_SESSION['facilityId'] ?? null]);

        if (empty($locid['weno_id'])) {
            //if not in an encounter then get the first facility location id as default
            $default_facility = sqlQuery("SELECT name, street, city, state, postal_code, phone, fax, weno_id from facility order by id asc limit 1");

            if (empty($default_facility)) {
                echo xlt('Facility ID is missing. Headover to Admin -> Other -> Weno Management. Enter the Weno ID of your facility');
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
            echo xlt("First Name Missing, headover to the Patient Chart > Demographics > Who. Save and retry") . "<br>";
            ++$missing;
        }
        if (empty($patient['lname'])) {
            echo xlt("Last Name Missing, headover to the Patient Chart > Demographics > Who. Save and retry")  . "<br>";
            ++$missing;
        }
        if (empty($patient['dob'])) {
            echo xlt("Date of Birth Missing, headover to the Patient Chart > Demographics > Who. Save and retry") . "<br>";
            ++$missing;
        }
        if (empty($patient['sex'])) {
            echo xlt("Gender Missing, headover to the Patient Chart > Demographics > Who. Save and retry") . "<br>";
            ++$missing;
        }
        if (empty($patient['postal_code'])) {
            echo xlt("Zip Code Missing, headover to the Patient Chart > Demographics > Contact > Postal Code. Save and retry") . "<br>";
            ++$missing;
        }
        if (empty($patient['street'])) {
            echo xlt("Street Address incomplete Missing, headover to the Patient Chart > Demographics > Contact. Save and retry") . "<br>";
            ++$missing;
        }
        if ($missing > 0) {
            die('Please add the missing data and try again');
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
        if (!empty($GLOBALS['weno_provider_password'])) {
            return $this->cryptoGen->decryptStandard($prov_pass['setting_value']);
        } else {
            echo xlt('Provider Password is missing');
            die;
        }
    }

    /**
     * @return mixed
     */
    public function getVitals()
    {
        $vitals = sqlQuery("select date, height, weight from form_vitals where pid = ? ORDER BY id DESC", [$_SESSION["pid"] ?? null]);
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
        $data = sqlQuery("SELECT * FROM weno_assigned_pharmacy WHERE pid = ? ", [$_SESSION["pid"]]);

        if (empty($data)) {
            die("Weno Pharmacies not set. Headover to Patient's Demographics > Choices > Weno Pharmacy Selector to Assign Pharmacies");
        }
        $response = array(
            "primary"   => $data['primary_ncpdp'],
            "alternate" => $data['alternate_ncpdp']
        );

        if (empty($response['primary'])) {
            die("Weno Primary Pharmacy not set. Headover to Patient's Demographics > Choices > Weno Pharmacy Selector to Assign Primary Pharmacy");
        }

        if (empty($response['alternate'])) {
            die("Weno Alternate Pharmacy not set. Headover to Patient's Demographics > Choices > Weno Pharmacy Selector to Assign Primary Pharmacy");
        }
        return $response;
    }

    public function wenoChr()
    {
        return
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0) .
            chr(0x0);
    }

    public function wenoMethod(): string
    {
        return "aes-256-cbc";
    }

    /**
     * @return mixed
     */
    public function getProviderName()
    {
        $provider_info = sqlQuery("select fname, mname, lname from users where username=? ", [$_SESSION["authUser"]]);

        return $provider_info['fname'] . " " . $provider_info['mname'] . " " . $provider_info['lname'];
    }

    /**
     * @return mixed
     */
    public function getPatientName()
    {
        $patient_info = sqlQuery("select fname, mname, lname from patient_data where pid=? ", [$_SESSION["pid"]]);

        return $patient_info['fname'] . " " . $patient_info['mname'] . " " . $patient_info['lname'];
    }

    /**
     * @return mixed
     */
    public function getWenoAltPharmacies()
    {
        $data = sqlQuery("SELECT * FROM weno_assigned_pharmacy WHERE pid = ? ", [$_SESSION["pid"]]);
        $response = array(
            "primary"   => $data['primary_ncpdp'],
            "alternate" => $data['alternate_ncpdp']
        );
        return $response;
    }
}
