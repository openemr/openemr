<?php

/*
 *
 * @package     OpenEMR Telehealth Module
 * @link        https://lifemesh.ai/telehealth/
 *
 * @author      Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright   Copyright (c) 2021 Lifemesh Corp <telehealth@lifemesh.ai>
 * @license     GNU General Public License 3
 *
 */

namespace OpenEMR\Modules\LifeMesh;

use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Uuid\UniqueInstallationUuid;

/**
 * Class AppDispatch
 * @package OpenEMR\Modules\LifeMesh
 */
class AppDispatch
{
    public $accountCheck;
    public $accountSummary;
    private $db;
    public $createSession;
    private $store;
    private $statusMessage;
    private $status;


    /**
     * AppDispatch constructor.
     */
    public function __construct()
    {
        $this->db = new Container();
        $this->store = $this->db->getDatabase();
    }

    /**
     * @param $username
     * @param $password
     * @param $url
     * @return string
     */
    public function apiRequest($username, $password, $url)
    {
        $data = base64_encode($username . ':' . $password);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->setUrl($url)); //dynamically set the url for the api request
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $data]);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $this->status = $status;
        curl_close($curl);

        if ($url == 'accountCheck') {
            if ($status === 200) {
                return true;
            } else {
                if ($status === 261) {
                    $statusMessage = "Please note your subscription is not active. You will not be able to schedule a Telehealth session or initiate the session from inside OpenEMR.";
                } else if ($status === 401) {
                    $statusMessage = "Please try again. Your user name or password is incorrect. You can contact Lifemesh at telehealth@lifemesh.ai for further support.";
                } else {
                    $statusMessage = "An error has occurred. Please contact Lifemesh at telehealth@lifemesh.ai for further support with a description to reproduce this error.";
                }
                $this->statusMessage = $statusMessage;
                return false;
            }
        }
        if ($url == 'accountSummary') {
            return $response;
        }
    }

    /**
     * @return mixed
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * @param $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $username
     * @param $password
     * @param $url
     * @param $callid
     * @param $eventid
     * @param $eventdatetimeutc
     * @param $eventdatetimelocal
     * @param $patientfirstname
     * @param $patientlastname
     * @param $patientemail
     * @param $patientcell
     */
    public function apiRequestSession(
        $username,
        $password,
        $url,
        $callid,
        $eventid,
        $eventdatetimeutc,
        $eventdatetimelocal,
        $patientfirstname,
        $patientlastname,
        $patientemail,
        $patientcell
    )
    {
        $data = base64_encode($username . ':' . $password);
        $header = [
            'Authorization: Basic ' . $data,
            'Content-Type: application/json'
        ];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->setUrl($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                                 "caller_id":"' . $callid . '",
                            "appointment_id":"' . $eventid . '",
                      "appointment_datetime":"' . $eventdatetimeutc . '",
                "appointment_datetime_local":"' . $eventdatetimelocal . '",
                        "patient_first_name":"' . $patientfirstname . '",
                         "patient_last_name":"' . $patientlastname . '",
                             "patient_email":"' . $patientemail . '",
                       "patient_cell_number":"' . $patientcell . '"
            }',
            CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        curl_close($curl);

        if ($status === 200) {
            $session = substr($response, 175); //remove header info
            $datatostore = json_decode($session, true);
                         $meetingid = $datatostore['MeetingID'];
                         $patient_code = $datatostore['PatientCode'];
                         $patient_uri = $datatostore['PatientURL'];
                         $provider_code = $datatostore['ProviderCode'];
                         $provider_uri = $datatostore['ProviderURL'];
                         $event_status = 'Scheduled';
                         $updatedAt = date("Y-m-d H:m:i");

            $time = explode("T", $eventdatetimelocal);

                $this->store->saveSessionData(
                    $eventid,
                    $meetingid,
                    $patient_code,
                    $patient_uri,
                    $provider_code,
                    $provider_uri,
                    $eventdatetimelocal,
                    $time[1],
                    $event_status,
                    $updatedAt
                );

        } elseif ($status === 471) {
            // Display this to the screen so that they will know that a patient can't be scheduled same day telehealth visit
            echo substr($response, 175);
            echo "<br><h4>Close this window and reopen appointment. Set a date in the future. Then save.</h4>";
            die;
        } else {
            EventAuditLogger::instance()->newEvent("lifemesh_telehealth", $_SESSION['authUser'], 'Default',0, $response);;
        }
    }

    /**
     * @param $username
     * @param $password
     * @param $callerid
     * @param $eventdatetime
     * @param $eventlocaltime
     * @param $eventid
     * @param $url
     */
    public function rescheduleSession($username, $password, $callerid, $eventdatetime,$eventlocaltime, $eventid, $url)
    {
        $data = base64_encode($username . ':' . $password);
        $header = [
            'Authorization: Basic ' . $data,
            'Content-Type: application/json'
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->setUrl($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                    "caller_id":"' . $callerid . '",
                    "appointment_id":"' . $eventid . '",
                    "new_appointment_datetime":"' . $eventdatetime . '",
                    "new_appointment_datetime_local":"' . $eventlocaltime . '"
                }',
            CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        if ($status != 200) {
            echo $response;
        }

        curl_close($curl);

    }

    /**
     * @param $encryptedaccountinfo
     * @param $eventid
     * @param $callerid
     * @param $url
     * @return bool|string
     */
    public function cancelSession($encryptedaccountinfo, $eventid, $callerid, $url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->setUrl($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "caller_id":"' . $callerid . '",
                "appointment_id":"' . $eventid . '"
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . $encryptedaccountinfo,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $this->store->cancelSessionDatabase($eventid);

        return $response;
    }

    /**
     * @param $encryptedaccountinfo
     * @param $url
     * @return bool|string
     */
    public function resetPassword($encryptedaccountinfo, $url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->setUrl($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $encryptedaccountinfo
            ),
        ));

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        if ($status == 200) {
            return 'complete';
        } else {
            return $response;
        }
    }

    /**
     * @param $encryptedaccountinfo
     * @param $url
     * @return bool|string
     */
    public function cancelSubscription($encryptedaccountinfo, $url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->setUrl($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . $encryptedaccountinfo,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        if ($status == 200) {
            return $response;
        } else {
            return $status;
        }
    }

    public function getStripeUrl($url, $email)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->setUrl($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "user_email": "' . $email . '"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    /**
     * @param $username
     * @param $password
     * @param $eventid
     */
    public function apiCheckPatientStatus($username, $password, $eventid)
    {
        $data = base64_encode($username . ':' . $password);
        $header = [
            'Authorization: Basic ' . $data,
            'Content-Type: application/json'
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->setUrl("checkPatientStatus"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "caller_id":"' . UniqueInstallationUuid::getUniqueInstallationUuid() . '",
                "appointment_id":"' . $eventid . '"
            }',
            CURLOPT_HTTPHEADER => $header
        ));
        // For debug, can send following parameter to force a "true" (note it returns strings and not booleans) :
        //  "force_true_response":"true"

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($status == 200) {
            return json_decode($response, true);
        } else {
            return false;
        }

    }

    /**
     * @param $value
     * @return string|null
     * set URL values based on the call to action
     */
    private function setUrl($value)
    {
        switch ($value) {
            case "accountCheck":
                return 'https://api.telehealth.lifemesh.ai/account_check';

            case "accountSummary":
                return 'https://api.telehealth.lifemesh.ai/account_summary';

            case "createSession":
                return 'https://api.telehealth.lifemesh.ai/create_session';

            case "rescheduleSession":
                return 'https://api.telehealth.lifemesh.ai/reschedule_session';

            case "cancelSession":
                return 'https://api.telehealth.lifemesh.ai/cancel_session';

            case "resetPassword":
                return "https://api.telehealth.lifemesh.ai/reset_password";

            case "cancelSubscription":
                return "https://api.telehealth.lifemesh.ai/cancel_subscription";

            case "createCheckoutSessionUrl":
                return "https://api.telehealth.lifemesh.ai/create_checkout_session_url";

            case "checkPatientStatus":
                return "https://api.telehealth.lifemesh.ai/check_session_patient_status";

            default:
                return NULL;
        }
    }
}
