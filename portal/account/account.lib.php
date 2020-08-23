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

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Utils\RandomGenUtils;

function notifyAdmin($pid, $provider)
{

    $note = xlt("New patient registration received from patient portal. Reminder to check for possible new appointment");
    $title = xlt("New Patient");
    $user = sqlQueryNoLog("SELECT users.username FROM users WHERE authorized = 1 And id = ?", array($provider));

    if (empty($user['username'])) {
        $user['username'] = "portal-user";
    }

    $rtn = addPnote($pid, $note, 1, 1, $title, $user['username'], '', 'New');

    return $rtn;
}

function isNew($dob = '', $lname = '', $fname = '', $email = '')
{
    // no sense doing a weighted search because we want specific criteria
    // for new patients. Mainly catch those trying to just get a new password.
    // or change email.
    $last = trim(urldecode($lname));
    $first = trim(urldecode($fname));
    $dob = trim(urldecode($dob));
    $semail = trim(urldecode($email));
    // first check email both contact and secure
    if ($email) {
        $sql = "select pid from patient_data " .
            "Where (patient_data.email LIKE ? OR patient_data.email_direct LIKE ?) " .
            "And patient_data.DOB LIKE ? order by date limit 0,1";
        $data = array(
            $semail,
            $semail,
            $dob
        );
        $tier1 = sqlQuery($sql, $data);
        if (!empty($tier1['pid'])) {
            // email with this dob already on file so, skedaddle ...
            return $tier1['pid'];
        }
    }
    // fully matched for our purposes
    $sql = "select pid from patient_data Where patient_data.lname LIKE ? And patient_data.fname LIKE ? And patient_data.DOB LIKE ? And (patient_data.email LIKE ? OR patient_data.email_direct LIKE ?) order by date limit 0,1";
    $data = array(
        $last,
        $first,
        $dob,
        $semail,
        $semail
    );
    $tier2 = sqlQuery($sql, $data);
    if (!empty($tier2['pid'])) {
        return $tier2['pid'];
    }
    // name and dob match. Most likely trying to change email!
    // too much of a coincidence...
    $sql = "select pid from patient_data Where patient_data.lname LIKE ? And patient_data.fname LIKE ? And patient_data.DOB LIKE ? order by date limit 0,1";
    $data = array(
        $last,
        $first,
        $dob
    );
    $tier3 = sqlQuery($sql, $data);

    return $tier3['pid'] ? $tier3['pid'] : 0;
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

function getNewPid()
{
    $result = sqlQuery("select max(pid)+1 as pid from patient_data");
    $newpid = 1;
    if ($result['pid'] > 1) {
        $newpid = $result['pid'];
    }
    if ($newpid == null) {
        $newpid = 0;
    }
    return $newpid;
}

function validEmail($email)
{
    if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $email)) {
        return true;
    }

    return false;
}

function messageCreate($uname, $pass, $encoded_link = '')
{
    $message = '<p>' . xlt("We received a credentials reset request. The link to reset your credentials is below.") . '</p>';
    $message .= '<p>' . xlt("Please ignore this email if you did not make this request") . '</p>';
    $message .= '<p><strong>' . xlt("Credentials Reset. Below link is only valid for one hour.") . ": </strong></p>";
    $message .= sprintf('<a href="%s">%s</a>', attr($encoded_link), text($encoded_link));
    $message .= "<p><strong>" . xlt("One Time verification PIN") . ": </strong>" . text($pass) . "</p>";
    $message .= "<p><strong>" . xlt("Your Portal Login Web Address. Bookmark for future logins.") . ": </strong></p>";
    $message .= '<a href=' . attr($GLOBALS['portal_onsite_two_address']) . '>' . text($GLOBALS['portal_onsite_two_address']) . "</a><br />";
    $message .= "<p>" . xlt("Thank You.") . "</p>";

    return $message;
}

function doCredentials($pid)
{
    global $srcdir;

    $newpd = sqlQuery("SELECT id,fname,mname,lname,email,email_direct, providerID FROM `patient_data` WHERE `pid`=?", array($pid));
    $user = sqlQueryNoLog("SELECT users.username FROM users WHERE authorized = 1 And id = ?", array($newpd['providerID']));


    $crypto = new CryptoGen();
    $uname = $newpd['fname'] . $newpd['id'];
    // Token expiry 1 hour
    $expiry = new DateTime('NOW');
    $expiry->add(new DateInterval('PT01H'));

    $clear_pass = RandomGenUtils::generatePortalPassword();
    $token_new = RandomGenUtils::createUniqueToken(32);
    $pin = RandomGenUtils::createUniqueToken(6);

    // Will send a link to user with encrypted token
    $token = $crypto->encryptStandard($token_new);
    if (empty($token)) {
        // Serious issue if this is case, so die.
        error_log('OpenEMR Error : Portal token encryption broken - exiting');
        die();
    }
    $encoded_link = sprintf("%s?%s", attr($GLOBALS['portal_onsite_two_address']), http_build_query([
        'forward' => $token,
        'site' => $_SESSION['site_id']
    ]));

    // Will store unencrypted token in database with the pin and expiration date
    $one_time = $token_new . $pin . bin2hex($expiry->format('U'));
    $res = sqlStatement("SELECT * FROM patient_access_onsite WHERE pid=?", array($pid));
    $query_parameters = array($uname, $one_time);
    $newHash = (new AuthHash('auth'))->passwordHash($clear_pass);
    if (empty($newHash)) {
        // Something is seriously wrong
        error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
        die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
    }
    array_push($query_parameters, $newHash);
    array_push($query_parameters, $pid);
    if (sqlNumRows($res)) {
        sqlStatementNoLog("UPDATE patient_access_onsite SET portal_username=?,portal_onetime=?,portal_pwd=?,portal_pwd_status=0 WHERE pid=?", $query_parameters);
    } else {
        sqlStatementNoLog("INSERT INTO patient_access_onsite SET portal_username=?,portal_onetime=?,portal_pwd=?,portal_pwd_status=0,pid=?", $query_parameters);
    }

    if (!validEmail($newpd['email_direct'])) {
        if (validEmail($newpd['email'])) {
            $newpd['email_direct'] = $newpd['email'];
        }
    }

    $message = messageCreate($uname, $pin, $encoded_link);

    $mail = new MyMailer();
    $pt_name = text($newpd['fname'] . ' ' . $newpd['lname']);
    $pt_email = text($newpd['email_direct']);
    $email_subject = xlt('Access Your Patient Portal');
    $email_sender = $GLOBALS['patient_reminder_sender_email'];
    $mail->AddReplyTo($email_sender, $email_sender);
    $mail->SetFrom($email_sender, $email_sender);
    $mail->AddAddress($pt_email, $pt_name);
    $mail->Subject = $email_subject;
    $mail->MsgHTML("<html><body><div class='wrapper'>" . $message . "</div></body></html>");
    $mail->IsHTML(true);
    $mail->AltBody = $message;

    if ($mail->Send()) {
        $sent = 1;
    } else {
        $email_status = $mail->ErrorInfo;
        $errorMsg = "EMAIL ERROR: " . errorLogEscape($email_status) . '<br />';
        if ($newpd['id']) {
            $errorMsg .= xlt("Your account has been successfully created however, we were unable to send the account information.");
            $errorMsg .= "<br />" . xlt("Please contact your providers office with the following account information") . ":<br />";
            $errorMsg1 = xlt("Account Id") . ": " . $uname . " " . xlt("MRN Reference") . ": " . $pid;
            $errorMsg .= $errorMsg1;
            $errorMsg .= "<br /><br />" . xlt("The providers office has been notified. Thank you.") . "<br />";
            // notify admin of failure.
            $title = xlt("Failed Registration");
            $admin_msg = "\n" . xlt("A new patients credentials could not be sent after portal registration.");
            $admin_msg .= "\n" . $errorMsg1;
            $admin_msg .= "\n" . xlt("Please follow up.");
            // send note
            addPnote($pid, $admin_msg, 1, 1, $title, $user['username'], '', 'New');
        } else {
            $errorMsg .= "<br />" . xlt("We were unable to create an account.") . "<br />";
            $errorMsg .= xlt("Please try again or contact the providers office for further assistance.");
        }
        error_log("Portal Registration error: " . errorLogEscape($errorMsg), 0);

        return $errorMsg;
    }

    return $sent;
}
