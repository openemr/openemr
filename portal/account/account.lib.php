<?php

/**
 * Ajax Library for Register
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* Library functions for register*/

use GuzzleHttp\Client;
use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\FHIR\Config\ServerConfig;

function notifyAdmin($pid, $provider): void
{

    $note = xlt("New patient registration received from patient portal. Reminder to check for possible new appointment");
    $title = xlt("New Patient");
    $user = sqlQueryNoLog("SELECT users.username FROM users WHERE authorized = 1 And id = ?", array($provider));

    if (empty($user['username'])) {
        $user['username'] = "portal-user";
    }

    addPnote($pid, $note, 1, 1, $title, $user['username'], '', 'New');
}

function processRecaptcha($gRecaptchaResponse): bool
{
    if (empty($gRecaptchaResponse)) {
        (new SystemLogger())->error("processRecaptcha function: gRecaptchaResponse is empty, so unable to verify recaptcha");
        return false;
    }
    if (empty($GLOBALS['google_recaptcha_site_key'])) {
        (new SystemLogger())->error("processRecaptcha function: google_recaptcha_site_key is empty, so unable to verify recaptcha");
        return false;
    }
    if (empty($GLOBALS['google_recaptcha_secret_key'])) {
        (new SystemLogger())->error("processRecaptcha function: google_recaptcha_secret_key is empty, so unable to verify recaptcha");
        return false;
    }
    $googleRecaptchaSecretKey = (new CryptoGen())->decryptStandard($GLOBALS['google_recaptcha_secret_key']);
    if (empty($googleRecaptchaSecretKey)) {
        (new SystemLogger())->error("processRecaptcha function: decrypted google_recaptcha_secret_key global is empty, so unable to verify recaptcha");
        return false;
    }

    $client = new Client([
        'base_uri' => 'https://www.google.com/recaptcha/api/',
        'timeout' => 2.0
    ]);
    $response = $client->request('POST', 'siteverify', [
        'query' => [
            'secret' => $googleRecaptchaSecretKey,
            'response' => $gRecaptchaResponse
        ]
    ]);
    $responseArray = json_decode($response->getBody(), true);
    (new SystemLogger())->debug("processRecaptcha function: recaptcha verification returned following", ['returnJson' => $responseArray]);
    if (empty($responseArray)) {
        (new SystemLogger())->debug("processRecaptcha function: recaptcha verification was unsuccessful since empty response from google");
        return false;
    }
    if (empty($responseArray['success'])) {
        (new SystemLogger())->debug("processRecaptcha function: recaptcha verification was unsuccessful since empty success status from google");
        return false;
    }
    if ($responseArray['success'] === true) {
        (new SystemLogger())->debug("processRecaptcha function: recaptcha verification was successful from host " . ($responseArray['hostname'] ?? ''));
        return true;
    } else {
        (new SystemLogger())->debug("processRecaptcha function: recaptcha verification was not successful from host " . ($responseArray['hostname'] ?? ''), ['errorCodes' => ($responseArray['error-codes'] ?? '')]);
        return false;
    }
}


// note function only returns false when there is an error in something and does not flag if a email exists or not
//  (this is done so a bad actor can not see if certain patients exist in the instance)
function verifyEmail(string $languageChoice, string $fname, string $mname, string $lname, string $dob, string $email): bool
{
    if (empty($languageChoice) || empty($fname) || empty($lname) || empty($dob) || empty($email)) {
        // only optional setting is the mname
        (new SystemLogger())->error("a required verifyEmail function parameter is empty");
        return false;
    }

    if (!validEmail($email)) {
        (new SystemLogger())->debug("verifyEmail function is using a email that failed validEmail test, so can not use");
        return true;
    }
    $twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
    $twig = $twigContainer->getTwig();
    $templateData = [];
    $template = 'verify-failed';
    $emailPrepSend = false;

    // check to ensure email not used
    $sql = sqlQuery(
        "SELECT `pid` FROM `patient_data` WHERE `email` = ? OR `email_direct` = ?",
        [
            $email,
            $email
        ]
    );

    if (!empty($sql['pid'])) {
        $templateData = ['email' => $email];
        (new SystemLogger())->debug("verifyEmail function: the email is already in use, so can not use");
        $emailPrepSend = true;
    } else {
        (new SystemLogger())->debug("verifyEmail function: the email will be used to register the patient");

        // create token (1 hour expiry) and ensure the token is unique
        $unique = false;
        for ($i = 1; $i <= 10; $i++) {
            $expiry = new DateTime('NOW');
            $expiry->add(new DateInterval('PT01H'));
            $token_raw = RandomGenUtils::createUniqueToken(32);
            $token_encrypt = (new CryptoGen())->encryptStandard($token_raw);
            if (empty($token_encrypt)) {
                // Serious issue if this is case, so return that something bad happened.
                (new SystemLogger())->error("OpenEMR Error : Portal email verification token encryption broken - exiting");
                return false;
            }
            $token_database = $token_raw . bin2hex($expiry->format('U'));

            $sqlVerify = sqlQueryNoLog("SELECT `id` FROM `verify_email` WHERE `token_onetime` LIKE BINARY ?", [$token_raw . '%']);
            if (empty($sqlVerify['id'])) {
                $unique = true;
                break;
            } else {
                (new SystemLogger())->error("was unable to create a unique token in verifyEmail function, which is very odd, so will try again (will try up to 10 times)");
            }
        }
        if (!$unique) {
            (new SystemLogger())->error("was unable to create a unique token in verifyEmail function, so failed");
            return false;
        }

        // place/replace database entry
        $sql = sqlQuery("SELECT `id` FROM `verify_email` WHERE `email` = ?", [$email]);
        if (empty($sql['id'])) {
            sqlStatementNoLog(
                "INSERT INTO `verify_email` (`email`, `language`, `fname`, `mname`, `lname`, `dob`, `token_onetime`, `active`, `pid_holder`) VALUES (?, ?, ?, ?, ?, ?, ?, 1, null)",
                [
                $email,
                $languageChoice,
                $fname,
                ($mname ?? ''),
                $lname,
                $dob,
                $token_database
                ]
            );
        } else {
            sqlStatementNoLog(
                "UPDATE `verify_email` SET `language` = ?, `fname` = ?, `mname` = ?, `lname` = ?, `dob` = ?, `token_onetime` = ?, `active` = 1, `pid_holder` = null WHERE `email` = ?",
                [
                    $languageChoice,
                    $fname,
                    ($mname ?? ''),
                    $lname,
                    $dob,
                    $token_database,
                    $email
                ]
            );
        }

        // create $encoded_link
        $site_addr = $GLOBALS['portal_onsite_two_address'];
        $site_id = $_SESSION['site_id'];
        if (stripos($site_addr, $site_id) === false) {
            $encoded_link = sprintf("%s?%s", attr($site_addr), http_build_query([
                'forward_email_verify' => $token_encrypt,
                'site' => $_SESSION['site_id']
            ]));
        } else {
            $encoded_link = sprintf("%s&%s", attr($site_addr), http_build_query([
                'forward_email_verify' => $token_encrypt
            ]));
        }
        $template = 'verify-success';
        $templateData['encoded_link'] = $encoded_link;
        $emailPrepSend = true;
    }

    $htmlMessage = $twig->render('emails/patient/verify_email/message-' . $template . '.html.twig', $templateData);
    $plainMessage = $twig->render('emails/patient/verify_email/message-' . $template . '.text.twig', $templateData);

    if ($emailPrepSend) {
        // send email
        $mail = new MyMailer();
        $email_sender = $GLOBALS['patient_reminder_sender_email'];
        $mail->AddReplyTo($email_sender, $email_sender);
        $mail->SetFrom($email_sender, $email_sender);
        $mail->AddAddress($email, ($fname . ' ' . $lname));
        $mail->Subject = xlt('Verify your email for patient portal registration');
        $mail->MsgHTML($htmlMessage);
        $mail->IsHTML(true);
        $mail->AltBody = $plainMessage;

        if ($mail->Send()) {
            EventAuditLogger::instance()->newEvent('patient-reg-email-verify', '', '', 1, "The patient registration verification email was successfully sent to " . $email);
            (new SystemLogger())->debug("The patient registration verification email was successfully sent to " . $email);
            return true;
        } else {
            $email_status = $mail->ErrorInfo;
            EventAuditLogger::instance()->newEvent('patient-reg-email-verify', '', '', 0, "The patient registration verification email was not successfully sent to " . $email . " because of following issue: " . $email_status);
            (new SystemLogger())->error("The patient registration verification email was not successfully sent to " . $email . " because of following issue: " . $email_status);
            return false;
        }
    }

    // should never get to below
    return true;
}

// note function only returns 0 when there is an error in something and does not flag if a patient exists or not
//  (this is done so a bad actor can not see if certain patients exist in the instance)
function resetPassword(string $dob, string $lname, string $fname, string $email): int
{
    if (empty($dob) || empty($lname) || empty($fname) || empty($email)) {
        (new SystemLogger())->error("a resetPassword function parameter is empty");
        return 0;
    }

    $sql = sqlStatement(
        "SELECT `pid` FROM `patient_data` WHERE `dob` = ? AND `lname` = ? AND `fname` = ? AND (`email` = ? OR `email_direct` = ?)",
        [
            $dob,
            $lname,
            $fname,
            $email,
            $email
        ]
    );

    if (sqlNumRows($sql) > 1) {
        EventAuditLogger::instance()->newEvent('patient-password-reset', '', '', 0, "Patient password reset failure: Multiple patients were found in patient_data for search of: " . $fname . " " . $lname . " " . $dob . " " . $email);
        (new SystemLogger())->error("resetPassword function selected more than 1 patient from patient_data, so was unable to reset the password");
        return 1;
    }
    if (!sqlNumRows($sql)) {
        EventAuditLogger::instance()->newEvent('patient-password-reset', '', '', 0, "Patient password reset failure: No patient was found in patient_data for search of: " . $fname . " " . $lname . " " . $dob . " " . $email);
        (new SystemLogger())->debug("resetPassword function found no patient in patient_data, so was unable to reset the password");
        return 1;
    }
    $row = sqlFetchArray($sql);
    if (empty($row['pid'])) {
        EventAuditLogger::instance()->newEvent('patient-password-reset', '', '', 0, "Patient password reset failure: No patient was found in patient_data for search of: " . $fname . " " . $lname . " " . $dob . " " . $email);
        (new SystemLogger())->debug("resetPassword function found no patient in patient_data, so was unable to reset the password");
        return 1;
    }
    $tempPid = $row['pid'];

    $sql = sqlStatement("SELECT `pid` FROM `patient_access_onsite` WHERE `pid`=?", [$tempPid]);
    if (sqlNumRows($sql) > 1) {
        EventAuditLogger::instance()->newEvent('patient-password-reset', '', '', 0, "Patient password reset failure: Multiple patients were found in patient_access_onsite for search of pid " . $tempPid);
        (new SystemLogger())->error("resetPassword function selected more than 1 patient from patient_access_onsite, so was unable to reset the password");
        return 1;
    }
    if (!sqlNumRows($sql)) {
        EventAuditLogger::instance()->newEvent('patient-password-reset', '', '', 0, "Patient password reset failure: No patient was found in patient_access_onsite for search of pid " . $tempPid);
        (new SystemLogger())->debug("resetPassword function found no patient in patient_access_onsite, so was unable to reset the password");
        return 1;
    }
    $row = sqlFetchArray($sql);
    if (empty($row['pid'])) {
        EventAuditLogger::instance()->newEvent('patient-password-reset', '', '', 0, "Patient password reset failure: No patient was found in patient_access_onsite for search of pid " . $tempPid);
        (new SystemLogger())->debug("resetPassword function found no patient in patient_access_onsite, so was unable to reset the password");
        return 1;
    }

    $rtn = doCredentials($row['pid'], true, $email);
    if ($rtn) {
        return 1;
    } else {
        return 0;
    }
}

function saveInsurance($pid)
{
    newInsuranceData(
        $pid = $pid,
        $type = "primary",
        $provider = "0",
        $policy_number = $_REQUEST['policy_number'],
        $group_number = $_REQUEST['group_number'],
        $plan_name = $_REQUEST['provider'] . ' ' . $_REQUEST['plan_name'],
        $subscriber_lname = "",
        $subscriber_mname = "",
        $subscriber_fname = "",
        $subscriber_relationship = "",
        $subscriber_ss = "",
        $subscriber_DOB = "",
        $subscriber_street = "",
        $subscriber_postal_code = "",
        $subscriber_city = "",
        $subscriber_state = "",
        $subscriber_country = "",
        $subscriber_phone = "",
        $subscriber_employer = "",
        $subscriber_employer_street = "",
        $subscriber_employer_city = "",
        $subscriber_employer_postal_code = "",
        $subscriber_employer_state = "",
        $subscriber_employer_country = "",
        $copay = $_REQUEST['copay'],
        $subscriber_sex = "",
        $effective_date = DateToYYYYMMDD($_REQUEST['date']),
        $accept_assignment = "TRUE",
        $policy_type = ""
    );
    newInsuranceData($pid, "secondary");
    newInsuranceData($pid, "tertiary");
}

function validEmail($email)
{
    if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $email)) {
        return true;
    }

    return false;
}

// $resetPass mode return false when something breaks (although returns true if related to a patient existing or not to prevent fishing for patients)
// !$resetPass mode return false when something breaks (no need to protect against from fishing since can't do from registration workflow)
function doCredentials($pid, $resetPass = false, $resetPassEmail = ''): bool
{
    $newpd = sqlQuery("SELECT id,fname,mname,lname,email,email_direct, providerID FROM `patient_data` WHERE `pid` = ?", array($pid));
    $user = sqlQueryNoLog("SELECT users.username FROM users WHERE authorized = 1 And id = ?", array($newpd['providerID']));

    // ensure pid exists
    if (empty($newpd)) {
        if ($resetPass) {
            (new SystemLogger())->error("doCredentials function did not find a patient from patient_data for " . $pid . " (this should never happen since checked in resetPassword function), so was unable to reset the password");
            return true;
        } else { // !$resetPass
            EventAuditLogger::instance()->newEvent('patient-registration', '', '', 0, "Patient credential creation failure: Following pid did not exist: " . $pid);
            (new SystemLogger())->error("doCredentials function did not find a patient from patient_data for " . $pid . " , so was unable to create credentials");
            return false;
        }
    }

    // ensure email is valid
    if ($resetPass) {
        if ((empty($resetPassEmail)) || ((($newpd['email'] ?? '') != $resetPassEmail) && (($newpd['email_direct'] ?? '') != $resetPassEmail))) {
            (new SystemLogger())->error("doCredentials function with empty email or unable to find correct email " . $resetPassEmail . " in patient from patient_data for pid " . $pid . " (this should never happen since checked in resetPassword function), so was unable to reset the password");
            return true;
        }
        if (!validEmail($resetPassEmail)) {
            EventAuditLogger::instance()->newEvent('patient-password-reset', '', '', 0, "Patient password reset failure: Email " . $resetPassEmail . " was not considered valid for pid: " . $pid);
            (new SystemLogger())->error("doCredentials function with email " . $resetPassEmail . " for pid " . $pid . " that was not valid per validEmail function, so was unable to reset the password");
            return false;
        }
        $newpd['email'] = $resetPassEmail;
    } else { // !$resetPass
        if (!validEmail($newpd['email'])) {
            EventAuditLogger::instance()->newEvent('patient-registration', '', '', 0, "Patient password reset failure: Email " . $newpd['email'] . " was not considered valid for pid: " . $pid);
            (new SystemLogger())->error("doCredentials function with email " . $newpd['email'] . " for pid " . $pid . " was not valid per validEmail function, so was unable to complete the registration");
            return false;
        }
    }

    // Token expiry 1 hour
    $expiry = new DateTime('NOW');
    $expiry->add(new DateInterval('PT01H'));

    $token_new = RandomGenUtils::createUniqueToken(32);
    $pin = RandomGenUtils::createUniqueToken(6);
    if (!$resetPass) {
        $clear_pass = RandomGenUtils::generatePortalPassword();
        $uname = $newpd['fname'] . $newpd['id'];
    }

    // Will send a link to user with encrypted token
    $token = (new CryptoGen())->encryptStandard($token_new);
    if (empty($token)) {
        // Serious issue if this is case, so exit.
        if ($resetPass) {
            EventAuditLogger::instance()->newEvent('patient-password-reset', '', '', 0, "Patient password reset failure secondary critical encryption error for email " . $newpd['email'] . " and pid: " . $pid);
            (new SystemLogger())->error("Error : Token encryption failed during patient password reset - exiting");
            return false;
        } else { // !$resetPass
            EventAuditLogger::instance()->newEvent('patient-registration', '', '', 0, "Patient credential creation registration failure secondary critical encryption error for email " . $newpd['email'] . " and pid: " . $pid);
            (new SystemLogger())->error("Error : Token encryption failed during patient registration - exiting");
            return false;
        }
    }
    $site_addr = $GLOBALS['portal_onsite_two_address'];
    $site_id = $_SESSION['site_id'];
    if (stripos($site_addr, $site_id) === false) {
        $encoded_link = sprintf("%s?%s", attr($site_addr), http_build_query([
            'forward' => $token,
            'site' => $_SESSION['site_id']
        ]));
    } else {
        $encoded_link = sprintf("%s&%s", attr($site_addr), http_build_query([
            'forward' => $token
        ]));
    }

    if (!$resetPass) {
        $newHash = (new AuthHash('auth'))->passwordHash($clear_pass);
        if (empty($newHash)) {
            // Serious issue if this is case, so exit.
            EventAuditLogger::instance()->newEvent('patient-registration', '', '', 0, "Patient credential creation registration failure secondary critical hashing error for email " . $newpd['email'] . " and pid: " . $pid);
            (new SystemLogger())->error("Error : Hashing failed during patient registration - exiting");
            return false;
        }
    }

    // Will store unencrypted token in database with the pin and expiration date
    $one_time = $token_new . $pin . bin2hex($expiry->format('U'));
    if ($resetPass) {
        // already confirmed there is an entry in patient_access_onsite in previously called resetPassword function
        // (note that portal_username, portal_pwd_status and portal_pwd are not touched here since password needs to remain valid until patient
        //  actually changes the password)
        $query_parameters = [$one_time, $pid];
        sqlStatementNoLog("UPDATE `patient_access_onsite` SET `portal_onetime` = ? WHERE `pid` = ?", $query_parameters);
    } else { // !$resetPass
        $query_parameters = [$uname, $one_time, $newHash, $pid];
        $res = sqlStatement("SELECT `id` FROM `patient_access_onsite` WHERE `pid` = ?", [$pid]);
        if (sqlNumRows($res)) {
            // this should never happen in current use case where these credentials are created after a new patient registers, so will return error
            EventAuditLogger::instance()->newEvent('patient-registration', '', '', 0, "Patient credential creation registration failure secondary to credentials already existing for email " . $newpd['email'] . " and pid: " . $pid);
            (new SystemLogger())->error("OpenEMR Error : doCredentials for registration - already credentials exists, so unable to create new credentials.");
            return false;
        } else {
            sqlStatementNoLog("INSERT INTO patient_access_onsite SET portal_username=?,portal_onetime=?,portal_pwd=?,portal_pwd_status=0,pid=?", $query_parameters);
        }
    }

    $twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
    $twig = $twigContainer->getTwig();
    $fhirServerConfig = new ServerConfig();

    $data = [
        'portal_onsite_two_address' => $GLOBALS['portal_onsite_two_address']
        ,'pin' => $pin
        ,'encoded_link' => $encoded_link
        ,'fhir_address' => $fhirServerConfig->getFhirUrl()
        ,'fhir_requirements_address' => $fhirServerConfig->getFhir3rdPartyAppRequirementsDocument()
    ];
    $htmlMessage = $twig->render('emails/patient/reset_credentials/message.html.twig', $data);
    $plainMessage = $twig->render('emails/patient/reset_credentials/message.text.twig', $data);

    $mail = new MyMailer();
    $pt_name = text($newpd['fname'] . ' ' . $newpd['lname']);
    $pt_email = text($newpd['email']);
    $email_subject = xlt('Access Your Patient Portal') . ' / ' . xlt('3rd Party API Access');
    $email_sender = $GLOBALS['patient_reminder_sender_email'];
    $mail->AddReplyTo($email_sender, $email_sender);
    $mail->SetFrom($email_sender, $email_sender);
    $mail->AddAddress($pt_email, $pt_name);
    $mail->Subject = $email_subject;
    $mail->MsgHTML($htmlMessage);
    $mail->IsHTML(true);
    $mail->AltBody = $plainMessage;

    if ($mail->Send()) {
        if ($resetPass) {
            EventAuditLogger::instance()->newEvent('patient-password-reset', '', '', 1, "The patient reset email was successfully sent to " . $newpd['email'] . " for pid " . $pid . ".");
            (new SystemLogger())->debug("The patient reset email was successfully sent to " . $newpd['email'] . " for pid " . $pid . ".");
        } else { // !$resetPass
            EventAuditLogger::instance()->newEvent('patient-registration', '', '', 1, "The patient registration credentials email was successfully sent to " . $newpd['email'] . " for pid " . $pid . ".");
            (new SystemLogger())->debug("The patient registration credentials email was successfully sent to " . $newpd['email'] . " for pid " . $pid . ".");
        }
        return true;
    } else {
        $email_status = $mail->ErrorInfo;
        if ($resetPass) {
            EventAuditLogger::instance()->newEvent('patient-password-reset', '', '', 0, "Patient password reset failure: The reset email to " . $newpd['email'] . " for pid " . $pid . " was not successful because of following issue: " . $email_status);
            (new SystemLogger())->error("Patient password reset failure: The reset email to " . $newpd['email'] . " for pid " . $pid . " was not successful because of following issue: " . $email_status);
        } else { // !$resetPass
            EventAuditLogger::instance()->newEvent('patient-registration', '', '', 0, "The patient registration credentials email was not successfully sent to " . $newpd['email'] . " for pid " . $pid . " because of following issue: " . $email_status);
            (new SystemLogger())->error("The patient registration credentials email was not successfully sent to " . $newpd['email'] . " for pid " . $pid . " because of following issue: " . $email_status);
            // notify admin of failure.
            $title = xlt("Failed Registration");
            $admin_msg = "\n" . xlt("A new patients credentials could not be sent after portal registration.");
            $admin_msg .= "\n" . "EMAIL ERROR: " . $email_status;
            $admin_msg .= "\n" . xlt("Please follow up.");
            // send note
            addPnote($pid, $admin_msg, 1, 1, $title, $user['username'], '', 'New');
        }
        return false;
    }
}

// the race condition can happen in registration since basically submitting patient info and insurance info at same time
//  where the pid is created and stored by the patient info. so, will sleep 1 seconds prior first attempt and then 5 seconds
//  prior second attempt to allow things to work out. Can make this mechanism more sophisticated in future if needed. In the
//  case of insurance, if it does fail getting the pid for some reason then the registration will still happen (and will
//  just not store the insurance info in worst case scenario).
function getPidHolder($preventRaceCondition = false): int
{
    if (empty($_SESSION['token_id_holder'])) {
        (new SystemLogger())->debug("getPidHolder function failed because token_id_holder session variable was not set");
        return 0;
    }
    if ($preventRaceCondition) {
        sleep(1);
    }
    $sql = sqlQueryNoLog("SELECT `pid_holder` FROM `verify_email` WHERE `id` = ?", [$_SESSION['token_id_holder']]);
    if (!empty($sql['pid_holder'])) {
        return $sql['pid_holder'];
    } else {
        if (!$preventRaceCondition) {
            return 0;
        } else { // $preventRaceCondition
            (new SystemLogger())->debug("getPidHolder function sleeping fo 5 seconds to deal with race condition");
            sleep(5);
            return getPidHolder();
        }
    }
}

function cleanupRegistrationSession()
{
    unset($_SESSION['patient_portal_onsite_two']);
    unset($_SESSION['authUser']);
    unset($_SESSION['pid']);
    unset($_SESSION['site_id']);
    unset($_SESSION['register']);
    unset($_SESSION['register_silo_ajax']);
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
}
