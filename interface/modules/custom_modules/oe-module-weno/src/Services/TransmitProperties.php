<?php

/**
 * TransmitProperties class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\FacilityService;

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
    private $encounter;

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
        $this->encounter = $this->getEncounter();
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
        $wenObj['OrgPatientID'] = $this->patient['pid'] . ":" . $this->getEncounter();
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
     * @return array|void
     */
    public function getProviderEmail()
    {
        $provider_info = ['email' => $GLOBALS['weno_provider_email']];
        if (empty($provider_info['email'])) {
            echo self::styleErrors(xlt('Provider email address is missing and required. Go to User Settings select Weno Tab and enter your Weno Provider Password'));
            exit;
        } else {
            return $provider_info;
        }
    }

    /**
     * @return array|false|null
     */
    public function getFacilityInfo(): array|null|false
    {
        // is user logged into facility
        if (!empty($_SESSION['facilityId'])) {
            $locId = sqlQuery("select name, street, city, state, postal_code, phone, fax, weno_id from facility where id = ?", [$_SESSION['facilityId'] ?? null]);
        } else {
            // from users facility
            $facilityService = new FacilityService();
            $locId = $facilityService->getFacilityForUser($_SESSION['authUserID']);
        }

        if (empty($locId['weno_id'])) {
            //if not in an encounter then get the first facility location id as default
            $default_facility = sqlQuery("SELECT name, street, city, state, postal_code, phone, fax, weno_id from facility order by id limit 1");

            if (empty($default_facility['weno_id'])) {
                echo self::styleErrors(xlt('Facility ID is missing. From Admin select Other then Weno Management. Enter the Weno ID of your facility'));
                exit;
            } else {
                return $default_facility;
            }
        }
        return $locId;
    }

    /**
     * @return mixed
     */
    private function getPatientInfo()
    {
        //get patient data if in an encounter
        //Since the transmitproperties is called in the logproperties
        //need to check to see if in an encounter or not. Patient data is not required to view the Weno log
        $log = '';
        $missing = 0;
        if (empty($_SESSION['encounter'])) {
            // removed requirement sjp
        }
        $patient = sqlQuery("select title, fname, lname, mname, street, state, city, email, phone_cell, postal_code, dob, sex, pid from patient_data where pid=?", [$_SESSION['pid']]);
        if (empty($patient['fname'])) {
            $log .= xlt("First Name Missing, From the Patient Chart select Demographics select Who. Save and retry") . "<brselect";
            ++$missing;
        }
        if (empty($patient['lname'])) {
            $log .= xlt("Last Name Missing, From the Patient Chart select Demographics select Who. Save and retry") . "<br>";
            ++$missing;
        }
        if (empty($patient['dob'])) {
            $log .= xlt("Date of Birth Missing, From the Patient Chart select Demographics select Who. Save and retry") . "<br>";
            ++$missing;
        }
        if (empty($patient['sex'])) {
            $log .= xlt("Gender Missing, From the Patient Chart select Demographics select Who. Save and retry") . "<br>";
            ++$missing;
        }
        if (empty($patient['postal_code'])) {
            $log .= xlt("Zip Code Missing, From the Patient Chart select Demographics select Contact select Postal Code. Save and retry") . "<br>";
            ++$missing;
        }
        if (empty($patient['street'])) {
            $log .= xlt("Street Address incomplete Missing, From the Patient Chart select Demographics select Contact. Save and retry") . "<br>";
            ++$missing;
        }
        if ($missing > 0) {
            self::errorWithDie($log);
        }
        return $patient;
    }

    public static function styleErrors($error): string
    {
        $log = "<div><p style='font-size: 1.25rem; color: red;'>" .
            $error . "<br />" . xlt('Please address errors and try again!') .
            "<br />" . xlt("Use browser Back button or Click Patient Name from top Patient bar.") .
            "</p></div>";
        return $log;
    }

    public static function errorWithDie($error): void
    {
        $log = self::styleErrors($error);
        die($log);
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
        if (!empty($GLOBALS['weno_provider_password'])) {
            return $this->cryptoGen->decryptStandard($GLOBALS['weno_provider_password']);
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
        $data = sqlQuery("SELECT * FROM `weno_assigned_pharmacy` WHERE `pid` = ? ", [$_SESSION["pid"]]);
        if (empty($data)) {
            $log = xlt("Weno Pharmacies not set. From Patient's Demographics select Choices then select Weno Pharmacy Selector to Assign Pharmacies");
            self::errorWithDie($log);
        }
        $response = array(
            "primary" => $data['primary_ncpdp'],
            "alternate" => $data['alternate_ncpdp']
        );

        if (empty($response['primary'])) {
            $log = xlt("Weno Primary Pharmacy not set. From Patient's Demographics select Choices then select Weno Pharmacy Selector to Assign Primary Pharmacy");
            self::errorWithDie($log);
        }

        if (empty($response['alternate'])) {
            $log = xlt("Weno Alternate Pharmacy not set. From Patient's Demographics select Choices then select Weno Pharmacy Selector to Assign Primary Pharmacy");
            self::errorWithDie($log);
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
            "primary" => $data['primary_ncpdp'],
            "alternate" => $data['alternate_ncpdp']
        );
        return $response;
    }

    private function getEncounter()
    {
        return $_SESSION['encounter'] ?? 0;
    }
}
