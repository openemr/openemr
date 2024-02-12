<?php

/**
 * TransmitProperties class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\FacilityService;

class TransmitProperties
{
    public $errors;
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
    private mixed $wenoProviderID;
    private string|false $csrf;

    /**
     * AdminProperties constructor.
     */
    public function __construct($returnFlag = false)
    {
        $this->errors = ['errors' => '', 'warnings' => '', 'info' => '', 'string' => ''];
        $this->csrf = js_escape(CsrfUtils::collectCsrfToken());
        ;
        $this->cryptoGen = new CryptoGen();
        $this->wenoProviderID = $this->getWenoProviderID();
        $this->ncpdp = $this->getPharmacy();
        $this->vitals = $this->getVitals();
        $this->patient = $this->getPatientInfo();
        $this->provider_email = $this->getProviderEmail();
        $this->provider_pass = $this->getProviderPassword();
        $this->locid = $this->getFacilityInfo();
        $this->pharmacy = $this->getPharmacy();
        $this->subscriber = $this->getSubscriber();
        $this->encounter = $this->getEncounter();
        // check for errors
        $this->errors = $this->checkErrors($this);
        if (!empty($this->errors['errors'])) {
            // let's not create payload if there are errors.
            // nip it here so to speak.
            if ($returnFlag) {
                return;
            }
            self::echoError($this->errors);
        } elseif ($returnFlag) {
            return;
        }
        // validated so create json object
        $this->payload = $this->createJsonObject();
    }

    /**
     * @param $value
     * @return bool
     */
    public function isJson($value): bool
    {
        return is_string($value) && json_decode($value) !== null && (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @return false|string
     */
    public function getPayload(): false|string
    {
        return $this->payload;
    }

    /**
     * Generate a list of errors, warnings and info messages.
     * All messages should be escaped and translated.
     *
     * @param $obj
     * @return string[]
     */
    public function checkErrors($obj): array
    {
        // Initialize the error array
        $error = ['errors' => '', 'warnings' => '', 'info' => '', 'string' => ''];
        // Check if the input is a valid object
        if (!is_object($obj)) {
            return $error; // Return empty error array if the input is not an object
        }
        // Iterate through the object properties
        foreach ($obj as $property => $value) {
            // Skip 'errors' property and empty values
            if ($property === 'errors' || empty($value)) {
                continue;
            }
            // Extract error type and value
            $type = '';
            $v = '';
            if (is_string($value)) {
                $values = [$value];
            } elseif ($this->isJson($value)) {
                $values = json_decode($value, true);
            } elseif (is_array($value)) {
                $values = $value;
            } else {
                continue; // Skip non-array and non-string properties
            }
            // Iterate through the values
            foreach ($values as $v) {
                if (str_contains($v, "REQED")) {
                    $type = 'errors';
                } elseif (str_contains($v, "WARNS")) {
                    $type = 'warnings';
                } elseif (str_contains($v, "INFO")) {
                    $type = 'info';
                } else {
                    continue; // Skip if no error type detected
                }
                // Add error to the respective error type if not already present
                if (!str_contains($error[$type], $v)) {
                    // Extract action from value
                    $action = '';
                    if (preg_match('/{([^}]*)}/', $v, $matches)) {
                        $action = $matches[1];
                        $v = str_replace('{' . $matches[1] . '}', '', $v);
                    }
                    // Append error with icon and onclick event
                    $uid = attr_js($_SESSION['authUserID'] ?? 0);
                    $action = attr_js($action);
                    $error[$type] .= "<i onclick='renderDialog($action, $uid, event)' role='button' class='fas fa-info-circle mx-1'></i>$v<br>";
                }
            }
        }
        // Combine error messages into a single string
        $error['string'] = $error['errors'] . $error['warnings'] . $error['info'];

        return $error;
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

        //TODO add insurance
        return json_encode($wenObj);
    }

    /**
     * @return string
     */
    public function getProviderEmail(): array|string
    {
        $provider_info = ['email' => $GLOBALS['weno_provider_email']];
        if (empty($provider_info['email'])) {
            return "REQED:{user_settings}" . (xlt('Provider Email is missing. Go to User Settings Weno Tab and enter your Weno Provider Email'));
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
                $default_facility['error'] = "REQED:{weno_manage}" . xlt('Facility ID is missing. From Admin select Other then Weno Management. Enter the Weno ID of your facility');
            }
            return $default_facility;
        }
        return $locId;
    }

    /**
     * @return mixed
     */
    private function getPatientInfo(): mixed
    {
        // Get patient data if in an encounter
        // Since the transmitProperties is called in the logproperties
        // need to check to see if in an encounter or not. Patient data is not required to view the Weno log

        $patient = sqlQuery("select title, fname, lname, mname, street, state, city, email, phone_cell, postal_code, dob, sex, pid from patient_data where pid=?", [$_SESSION['pid']]);
        if (empty($patient['fname'])) {
            $patient['fname'] = "REQED:{demographics}" . xlt("First Name Missing, From the Patient Chart select Demographics select Who.");
        }
        if (empty($patient['lname'])) {
            $patient['lname'] = "REQED:{demographics}" . xlt("Last Name Missing, From the Patient Chart select Demographics select Who.");
        }
        if (empty($patient['dob'])) {
            $patient['dob'] = "REQED:{demographics}" . xlt("Date of Birth Missing, From the Patient Chart select Demographics select Who.");
        }
        if (empty($patient['sex'])) {
            $patient['sex'] = "REQED:{demographics}" . xlt("Gender Missing, From the Patient Chart select Demographics select Who.");
        }
        if (empty($patient['postal_code'])) {
            $patient['postal_code'] = "REQED:{demographics}" . xlt("Zip Code Missing, From the Patient Chart select Demographics select Contact select Postal Code.");
        }
        if (empty($patient['street'])) {
            $patient['street'] = "REQED:{demographics}" . xlt("Street Address Missing, From the Patient Chart select Demographics select Contact.");
        }
        if (empty($patient['city'])) {
            $patient['city'] = "WARNS:{demographics}" . xlt("City Missing, From the Patient Chart select Demographics select Contact.");
        }
        if (empty($patient['state'])) {
            $patient['state'] = "WARNS:{demographics}" . xlt("State Missing, From the Patient Chart select Demographics select Contact.");
        }
        if (empty($patient['phone_cell'])) {
            $patient['phone_cell'] = "WARNS:{demographics}" . xlt("Cell Phone Missing, From the Patient Chart select Demographics select Contact.");
        }
        return $patient;
    }

    public static function styleErrors($error): string
    {
        $log = "<div><p style='font-size: 1.0rem; color: red;'>" .
            $error . "<br />" . xlt('Please address errors and try again!') .
            "</p></div>";
        return $log;
    }

    /**
     * @param $errors
     * @return void
     */
    public static function echoError($errors): void
    {
        if (is_array($errors)) {
            $error = $errors['errors'] . $errors['warnings'] . $errors['info'];
        } else {
            $error = $errors;
        }
        $log = self::styleErrors($error);
        echo($log);
    }

    /**
     * @return string
     * New Rx
     */
    public function cipherPayload(): string
    {
        $cipher = "aes-256-cbc"; // AES 256 CBC cipher
        $enc_key = $this->cryptoGen->decryptStandard($GLOBALS['weno_encryption_key']);

        if (!$enc_key) {
            return "error";
        }

        $key = substr(hash('sha256', $enc_key, true), 0, 32);
        $iv = str_repeat("\0", 16); // Generate an initialization vector

        return base64_encode(openssl_encrypt($this->payload, $cipher, $key, OPENSSL_RAW_DATA, $iv));
    }


    /**
     * @return mixed
     */
    public function getProviderPassword(): mixed
    {
        if (!empty($GLOBALS['weno_provider_password'])) {
            return $this->cryptoGen->decryptStandard($GLOBALS['weno_provider_password']);
        } else {
            return "REQED:{user_settings}" . xlt('Provider Password is missing. Go to User Settings Weno Tab and enter your Weno Provider Password');
        }
    }

    /**
     * @return array|false|null
     */
    public function getVitals(): ?array
    {
        $vitals = sqlQuery("SELECT date, height, weight FROM form_vitals WHERE pid = ? ORDER BY id DESC", [$_SESSION["pid"] ?? null]);
        // Check if vitals are empty or missing height and weight
        if (empty($vitals) || !isset($vitals['height'], $vitals['weight'])) {
            return [
                "REQED:{vitals}" . xlt("A Vitals Height and Weight are required to transmit a prescription. Create or add Vitals in an encounter.")
            ];
        }
        return $vitals;
    }

    /**
     * @return mixed
     */
    private function getSubscriber(): mixed
    {
        $sql = sqlQuery("select subscriber_relationship from insurance_data where pid = ? and type = 'primary'", [$_SESSION['pid']]);
        $sql = $sql ?? ['subscriber_relationship' => ''];
        return $sql['subscriber_relationship'];
    }

    /**
     * @return string|array
     */
    public function getPharmacy(): string|array
    {
        $data = sqlQuery("SELECT * FROM `weno_assigned_pharmacy` WHERE `pid` = ? ", [$_SESSION["pid"]]);
        $response = array(
            "primary" => $data['primary_ncpdp'] ?? '',
            "alternate" => $data['alternate_ncpdp'] ?? ''
        );
        if (empty($data)) {
            $response['errors'] = true;
            // both primary and alternate are empty
        }

        if (empty($response['primary'])) {
            $response['errors'] = true;
            $e = 'REQED:demographics}' . xlt("Weno Primary Pharmacy not set. From Patient's Demographics Choices assign Primary Pharmacy");
            $response['primary'] = $e;
        }

        if (empty($response['alternate'])) {
            $response['errors'] = true;
            $e = 'WARNS:demographics ' . xlt("Weno Alternate Pharmacy not set. From Patient's Demographics Choices assign Alternate Pharmacy");
            $response['alternate'] = $e;
        }
        return $response;
    }

    /**
     * @return mixed
     */
    public function getProviderName(): mixed
    {
        $provider_info = sqlQuery("select fname, mname, lname from users where username=? ", [$_SESSION["authUser"]]);
        $provider_info = $provider_info ?? ['fname' => '', 'mname' => '', 'lname' => ''];
        return $provider_info['fname'] . " " . $provider_info['mname'] . " " . $provider_info['lname'];
    }

    /**
     * @return mixed
     */
    public function getPatientName(): mixed
    {
        $patient_info = sqlQuery("select fname, mname, lname from patient_data where pid=? ", [$_SESSION["pid"]]);
        $patient_info = $patient_info ?? ['fname' => '', 'mname' => '', 'lname' => ''];
        return $patient_info['fname'] . " " . $patient_info['mname'] . " " . $patient_info['lname'];
    }

    /**
     * @return int|mixed
     */
    private function getEncounter(): mixed
    {
        return $_SESSION['encounter'] ?? 0;
    }

    /**
     * @param $id
     * @return mixed|string
     */
    public function getWenoProviderId($id = null): mixed
    {
        if (empty($id)) {
            $id = $_SESSION['authUserID'] ?? '';
        }
        // get the weno provider id from the user table (weno_prov_id
        $provider = sqlQuery("SELECT weno_prov_id FROM users WHERE id = ?", [$id]);
        if (!empty(trim($provider['weno_prov_id']))) {
            return $provider['weno_prov_id'];
        } else {
            return "REQED:{users}" . xlt("Weno Provider Id missing. Select Admin then Users and edit the user to add Weno Provider Id");
        }
    }
}
