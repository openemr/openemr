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
    private mixed $responsibleParty;

    /**
     * AdminProperties constructor.
     */
    public function __construct($returnFlag = false)
    {
        $this->errors = ['errors' => '', 'warnings' => '', 'info' => '', 'string' => ''];
        $this->csrf = js_escape(CsrfUtils::collectCsrfToken());
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
        // check if patient is under 19 years old
        $this->responsibleParty = '';
        if (self::getAge($this->patient['dob']) < 19) {
            $this->responsibleParty = $this->getResponsibleParty();
        }
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
                // Extract action from value
                $action = '';
                if (preg_match('/{([^}]*)}/', $v, $matches)) {
                    $action = $matches[1];
                    $v = str_replace('{' . $matches[1] . '}', '', $v);
                }
                // Add error to the respective error type if not already present
                if (!str_contains($error[$type], $v)) {
                    // Append error with icon and onclick event
                    $uid = attr_js($_SESSION['authUserID'] ?? 0);
                    $action = attr_js($action);
                    $error[$type] .= "<i onclick='renderDialog($action, $uid, event)' role='button' class='fas fa-pen text-warning mx-1'></i>$v<br>";
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
    public function createJsonObject(): false|string
    {
        //default is testing mode
        $testing = isset($GLOBALS['weno_rx_enable_test']);
        if ($testing) {
            $mode = 'Y';
        } else {
            $mode = 'N';
        }
        $gender = $this->patient['sex'];
        $heightDate = explode(" ", $this->vitals['date'] ?? '');
        $phonePrimary = $this->formatPhoneNumber($this->patient['phone_cell']);
        $age = self::getAge($this->patient['dob']);
        //create json array
        $wenObj = [];
        $wenObj['UserEmail'] = $this->provider_email['email'];
        $wenObj['MD5Password'] = md5($this->provider_pass);
        $wenObj['LocationID'] = $this->locid['weno_id'];
        $wenObj['TestPatient'] = $mode;
        $wenObj['PatientType'] = 'Human';
        $wenObj['OrgPatientID'] = $this->patient['pid'] . ":" . $_SESSION['authUserID'] ?? 0;
        $wenObj['LastName'] = $this->patient['lname'];

        $wenObj['FirstName'] = $this->patient['fname'];
        $wenObj['Gender'] = $gender[0];
        $wenObj['DateOfBirth'] = $this->patient['dob'];
        $wenObj['AddressLine1'] = $this->patient['street'];
        $wenObj['City'] = $this->patient['city'];
        $wenObj['State'] = $this->patient['state'];
        $wenObj['PostalCode'] = $this->patient['postal_code'];
        $wenObj['CountryCode'] = "US";
        $wenObj['PrimaryPhone'] = $phonePrimary;
        $wenObj['SupportsSMS'] = 'Y';

        if ($age < 19) {
            $wenObj['PatientHeight'] = substr($this->vitals['height'] ?? '', 0, -3);
            $wenObj['PatientWeight'] = substr($this->vitals['weight'] ?? '', 0, -3);
            $wenObj['HeightWeightObservationDate'] = $heightDate[0];
        } elseif (!empty($this->vitals['height'] ?? '') && !empty($this->vitals['weight'] ?? '')) { // may as well send
            $wenObj['PatientHeight'] = substr($this->vitals['height'] ?? '', 0, -3);
            $wenObj['PatientWeight'] = substr($this->vitals['weight'] ?? '', 0, -3);
            $wenObj['HeightWeightObservationDate'] = $heightDate[0];
        }
        $wenObj["ResponsiblePartySameAsPatient"] = $age < 19 ? 'N' : 'Y';
        if ($age < 19 && !empty($this->responsibleParty)) {
            $wenObj['ResponsiblePartyLastName'] = $this->responsibleParty['ResponsiblePartyLastName'];
            $wenObj['ResponsiblePartyFirstName'] = $this->responsibleParty['ResponsiblePartyFirstName'];
            $wenObj['ResponsiblePartyAddressLine1'] = $this->responsibleParty['ResponsiblePartyAddressLine1'];
            if (!empty(($this->responsibleParty['ResponsiblePartyAddressLine2'] ?? ''))) {
                $wenObj['ResponsiblePartyAddressLine2'] = $this->responsibleParty['ResponsiblePartyAddressLine2'];
            }
            $wenObj['ResponsiblePartyCity'] = $this->responsibleParty['ResponsiblePartyCity'];
            $wenObj['ResponsiblePartyState'] = $this->responsibleParty['ResponsiblePartyState'];
            $wenObj['ResponsiblePartyPostalCode'] = $this->responsibleParty['ResponsiblePartyPostalCode'];
            $wenObj['ResponsiblePartyCountryCode'] = 'US';
            $wenObj['ResponsiblePartyPrimaryPhone'] = self::formatPhoneNumber($this->responsibleParty['ResponsiblePartyPrimaryPhone']);
        }
        $wenObj['PatientLocation'] = "Home";

        $wenObj['PrimaryPharmacyNCPCP'] = $this->pharmacy['primary'];
        if (!empty($this->pharmacy['alternate'])) {
            $wenObj['AlternativePharmacyNCPCP'] = $this->pharmacy['alternate'];
        }

        return json_encode($wenObj);
    }

    /**
     * @return mixed
     */
    private function getResponsibleParty(): mixed
    {
        $guardian = <<<guardian
select guardiansname as ResponsiblePartyLastName, guardiansname as ResponsiblePartyFirstName, guardianaddress as ResponsiblePartyAddressLine1, guardianpostalcode as ResponsiblePartyPostalCode, guardiancity as ResponsiblePartyCity, guardianstate as ResponsiblePartyState, guardianphone as ResponsiblePartyPrimaryPhone from patient_data where pid = ?;
guardian;

        $insurance = <<<insurance
select subscriber_lname as ResponsiblePartyLastName, subscriber_fname as ResponsiblePartyFirstName, subscriber_street as ResponsiblePartyAddressLine1, subscriber_postal_code as ResponsiblePartyPostalCode, subscriber_city as ResponsiblePartyCity, subscriber_state as ResponsiblePartyState, subscriber_phone as ResponsiblePartyPrimaryPhone, subscriber_street_line_2 as ResponsiblePartyAddressLine2 from insurance_data where pid = ? and subscriber_relationship > '' and subscriber_relationship != 'self' and type = 'primary'
insurance;

        $relation = sqlQuery($guardian, [$_SESSION['pid']]);
        // if no guardian then check for primary insurance subscriber
        if (empty($relation['ResponsiblePartyLastName'])) {
            $relation = sqlQuery($insurance, [$_SESSION['pid']]);
        }
        if (empty($relation)) {
            return 'REQED:{demographics}' . xlt("Patient is under 19 years old. A Responsible Party is required. From the Patient Chart select Demographics Primary Insurance or Guardian to add a person.");
        }

        return $relation;
    }

    /**
     * @param $dob
     * @param $as_of
     * @return string
     */
    public static function getAge($dob, $as_of = ''): string
    {
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        }
        $a1 = explode('-', substr($dob, 0, 10));
        $a2 = explode('-', substr($as_of, 0, 10));
        $age = (int)$a2[0] - (int)$a1[0];
        if ($a2[1] < $a1[1] || ($a2[1] == $a1[1] && $a2[2] < $a1[2])) {
            --$age;
        }

        return (int)$age;
    }

    /**
     * @param $phone
     * @return string
     */
    public function formatPhoneNumber($phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone);
        if (strlen($phone) == 11) {
            $phone = substr($phone, 1, 10);
        }
        return $phone;
    }

    /**
     * @return array|string
     */
    public function getProviderEmail(): array|string
    {
        $provider_info = ['email' => ($GLOBALS['weno_provider_email'] ?? '')];
        if (empty($provider_info['email'])) {
            return "REQED:{user_settings}" . (xlt('Weno Prescriber Email is missing. Go to User Settings Weno Tab and enter your Weno User Email'));
        } else {
            return $provider_info;
        }
    }

    /**
     * @return array|false|null
     */
    public function getFacilityInfo(): array|null|false
    {
        // is user logged into a facility
        if (!empty($_SESSION['facilityId'])) {
            $locId = sqlQuery("select name, street, city, state, postal_code, phone, fax, weno_id from facility where id = ?", [$_SESSION['facilityId'] ?? null]);
        } else {
            // from users facility
            $facilityService = new FacilityService();
            $locId = $facilityService->getFacilityForUser($_SESSION['authUserID'] ?? '');
        }

        if (empty($locId['weno_id'] ?? '')) {
            if (!empty($locId['id'])) {
                // weno_id is not set in service so at least we have their facility id
                // so we'll look if it's set there anyway. Bottom line is get users default facility.
                $default_facility = sqlQuery("SELECT name, street, city, state, postal_code, phone, fax, weno_id from facility where `id` = ? limit 1", [$locId['id']]);
            }
            if (empty($default_facility['weno_id'] ?? '')) {
                //if no default for user then get the first facility location id as default
                $default_facility = sqlQuery("SELECT name, street, city, state, postal_code, phone, fax, weno_id from facility order by id limit 1");
            }
            if (empty($default_facility['weno_id'])) {
                // still no joy so let user know and get it set!
                $default_facility['error'] = "REQED:{weno_manage}" . xlt('Facility ID is missing. From Admin select Weno eRx Tools then Weno eRx Service Setup. Enter the Weno Location ID of your facility');
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

        $patient = sqlQuery("select title, fname, lname, mname, street, state, city, email, phone_cell, phone_home, postal_code, dob, sex, pid from patient_data where pid=?", [$_SESSION['pid']]);
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
            $patient['city'] = "REQED:{demographics}" . xlt("City Missing, From the Patient Chart select Demographics select Contact.");
        }
        if (empty($patient['state'])) {
            $patient['state'] = "REQED:{demographics}" . xlt("State Missing, From the Patient Chart select Demographics select Contact.");
        }
        if (empty($patient['phone_cell'])) {
            $patient['phone_cell'] = "REQED:{demographics}" . xlt("Cell or Home Phone Missing, From the Patient Chart select Demographics select Contact.");
            if (!empty($patient['phone_home'])) {
                $patient['phone_cell'] = $patient['phone_home'];
            }
        }
        return $patient;
    }

    /**
     * @param $error
     * @return string
     */
    public static function styleErrors($error): string
    {
        return "<div><p style='font-size: 1.0rem; color: red;'>" . text($error) . "</p></div>";
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
            $ret = $this->cryptoGen->decryptStandard($GLOBALS['weno_provider_password']);
            if (!$ret) {
                return ("REQED:{user_settings}" . xlt('Your Weno Prescriber Password fails decryption. Go to User Settings Weno Tab and reenter your Weno User Password'));
            }
            return $ret;
        } else {
            return "REQED:{user_settings}" . xlt('Your Weno Prescriber Password is missing. Go to User Settings Weno Tab and enter your Weno User Password');
        }
    }

    /**
     * @return array|null
     */
    public function getVitals(): ?array
    {
        $vitals = sqlQuery("SELECT date, height, weight FROM form_vitals WHERE pid = ? ORDER BY id DESC", [$_SESSION["pid"] ?? null]);
        // Check if vitals are empty or missing height and weight
        $patient = $this->getPatientInfo();
        if (self::getAge($patient['dob']) < 19) {
            if (empty($vitals) || ($vitals['height'] <= 0) || ($vitals['weight'] <= 0)) {
                return [
                    "REQED:{vitals}" . xlt("Vitals Height and Weight required for patient under 19 yo. Create or add Vitals in an encounter.")
                ];
            }
        } elseif (empty($vitals)) {
            $vitals = [
                "date" => date('Y-m-d H:i:s'),
                "height" => 0,
                "weight" => 0
            ];
        }
        return $vitals;
    }

    /**
     * @return mixed
     */
    private function getSubscriber(): mixed
    {
        $relation = sqlQuery("select subscriber_relationship from insurance_data where pid = ? and type = 'primary'", [$_SESSION['pid']]);
        $relation = $relation ?? ['subscriber_relationship' => ''];

        return $relation['subscriber_relationship'] ?? '';
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
            $e = 'REQED:{demographics}' . xlt("Weno Primary Pharmacy not set. From Patient's Demographics Choices assign Primary Pharmacy");
            $response['primary'] = $e;
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        $provider_info = sqlQuery("select fname, mname, lname from users where username=? ", [$_SESSION["authUser"]]);
        $provider_info = $provider_info ?? ['fname' => '', 'mname' => '', 'lname' => ''];
        return $provider_info['fname'] . " " . $provider_info['mname'] . " " . $provider_info['lname'];
    }

    /**
     * @return string
     */
    public function getPatientName(): string
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
        // get the Weno User id from the user table (weno_prov_id)
        $provider = sqlQuery("SELECT weno_prov_id FROM users WHERE id = ?", [$id]);

        if ((!empty($GLOBALS['weno_provider_uid'])) && !empty($provider['weno_prov_id'])) {
            $doIt = ($GLOBALS['weno_provider_uid']) != trim($provider['weno_prov_id']);
            if ($doIt) {
                $provider['weno_prov_id'] = $GLOBALS['weno_provider_uid'];
                $sql = "INSERT INTO `user_settings` (`setting_value`, `setting_user`, `setting_label`) 
                    VALUES (?, ?, 'global:weno_provider_uid') 
                    ON DUPLICATE KEY UPDATE `setting_value` = ?";
                sqlQuery($sql, [$provider['weno_prov_id'], $id, $provider['weno_prov_id']]);
            }
            $GLOBALS['weno_provider_uid'] = $GLOBALS['weno_prov_id'] = $provider['weno_prov_id']; // update users
            $sql = "INSERT INTO `users` (`weno_prov_id`, `id`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `weno_prov_id` = ?";
            sqlQuery($sql, [$GLOBALS['weno_provider_uid'], $id, $GLOBALS['weno_provider_uid']]);
            return $provider['weno_prov_id'];
        } elseif (!empty($provider['weno_prov_id'] ?? '') && empty($GLOBALS['weno_provider_uid'])) {
            $sql = "INSERT INTO `user_settings` (`setting_value`, `setting_user`, `setting_label`) 
                VALUES (?, ?, 'global:weno_provider_uid') 
                ON DUPLICATE KEY UPDATE `setting_value` = ?";
            sqlQuery($sql, [$provider['weno_prov_id'], $id, $provider['weno_prov_id']]);

            $GLOBALS['weno_provider_uid'] = $GLOBALS['weno_prov_id'] = $provider['weno_prov_id'];
            return $provider['weno_prov_id'];
        } elseif (empty($provider['weno_prov_id'] ?? '') && !empty($GLOBALS['weno_provider_uid'])) {
            $sql = "INSERT INTO `users` (`weno_prov_id`, `id`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `weno_prov_id` = ?";
            sqlQuery($sql, [$GLOBALS['weno_provider_uid'], $id, $GLOBALS['weno_provider_uid']]);

            $provider['weno_prov_id'] = $GLOBALS['weno_prov_id'] = $GLOBALS['weno_provider_uid'];
            return $provider['weno_prov_id'];
        } else {
            return "REQED:{users}" . xlt("Weno User Id missing. Select Admin then Users and edit the user to add Weno User Id");
        }
    }
}
