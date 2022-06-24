<?php

/**
 * create_portallogin.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Paul Simon <paul@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once('../../../library/amc.php');

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\FHIR\Config\ServerConfig;
use Twig\Environment;

$twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
$twig = $twigContainer->getTwig();

$trustedEmail = sqlQueryNoLog("SELECT email_direct, email FROM `patient_data` WHERE `pid`=?", array($pid));
$row = sqlQuery("SELECT pd.*,pao.portal_username, pao.portal_login_username,pao.portal_pwd,pao.portal_pwd_status FROM patient_data AS pd LEFT OUTER JOIN patient_access_onsite AS pao ON pd.pid=pao.pid WHERE pd.pid=?", array($pid));

$trustedEmail['email_direct'] = !empty(trim($trustedEmail['email_direct'])) ? text(trim($trustedEmail['email_direct'])) : text(trim($trustedEmail['email']));
$trustedUserName = $trustedEmail['email_direct'];
// check for duplicate username
$dup_check = sqlQueryNoLog("SELECT * FROM patient_access_onsite WHERE pid != ? AND portal_login_username = ?", array($pid, $trustedUserName));
// make unique if needed
if (!empty($dup_check)) {
    if (strpos($trustedUserName, '@')) {
        $trustedUserName = str_replace("@", "$pid@", $trustedUserName);
    } else {
        // account name will be used and is unique
        $trustedUserName = '';
    }
}

function validEmail($email)
{
    if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $email)) {
        return true;
    }

    return false;
}

function emailLogin($patient_id, $htmlMsg, $plainMsg, Environment $twig)
{
    $patientData = sqlQuery("SELECT * FROM `patient_data` WHERE `pid`=?", array($patient_id));
    if ($patientData['hipaa_allowemail'] != "YES" || empty($patientData['email']) || empty($GLOBALS['patient_reminder_sender_email'])) {
        return false;
    }

    if (!(validEmail($patientData['email']))) {
        return false;
    }

    if (!(validEmail($GLOBALS['patient_reminder_sender_email']))) {
        return false;
    }
    $mail = new MyMailer();
    $pt_name = $patientData['fname'] . ' ' . $patientData['lname'];
    $pt_email = $patientData['email'];
    $email_subject = xl('Access Your Patient Portal') . ' / ' . xl('3rd Party API Access');
    $email_sender = $GLOBALS['patient_reminder_sender_email'];
    $mail->AddReplyTo($email_sender, $email_sender);
    $mail->SetFrom($email_sender, $email_sender);
    $mail->AddAddress($pt_email, $pt_name);
    $mail->Subject = $email_subject;
    $mail->MsgHTML($htmlMsg);
    $mail->AltBody = $plainMsg;
    $mail->IsHTML(true);

    if ($mail->Send()) {
        return true;
    } else {
        $email_status = $mail->ErrorInfo;
        error_log("EMAIL ERROR: " . errorLogEscape($email_status), 0);
        return false;
    }
}

function displayLogin($patient_id, $message, $emailFlag)
{
    $patientData = sqlQuery("SELECT * FROM `patient_data` WHERE `pid`=?", array($patient_id));
    if ($emailFlag) {
        $message = xlt("Email was sent to following address") . ": " .
            text($patientData['email']) . "\n\n" .
            $message;
    } else {
        $message = "<div class='text-danger'>" . xlt("Email was not sent to the following address") . ": " .
            text($patientData['email']) . "</div>" . "\n\n" .
            $message;
    }

    return $message;
}

if (isset($_POST['form_save']) && $_POST['form_save'] == 'submit') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $clear_pass = $_POST['pwd'];

    $res = sqlStatement("SELECT * FROM patient_access_onsite WHERE pid=?", array($pid));
    $query_parameters = array($_POST['uname'],$_POST['login_uname']);
    $hash = (new AuthHash('auth'))->passwordHash($clear_pass);
    if (empty($hash)) {
        // Something is seriously wrong
        error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
        die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
    }
    array_push($query_parameters, $hash);

    array_push($query_parameters, $pid);
    if (sqlNumRows($res)) {
        // TODO: @adunsulag is there a reason why we don't audit the fact that the patient credentials were created and updated?
        // That seems like a pretty important security event we want to be aware of.
        sqlStatementNoLog("UPDATE patient_access_onsite SET portal_username=?,portal_login_username=?,portal_pwd=?,portal_pwd_status=0 WHERE pid=?", $query_parameters);
    } else {
        sqlStatementNoLog("INSERT INTO patient_access_onsite SET portal_username=?,portal_login_username=?,portal_pwd=?,portal_pwd_status=0,pid=?", $query_parameters);
    }

    // Create the message
    $fhirServerConfig = new ServerConfig();
    $data = [
        'portal_onsite_two_address' => $GLOBALS['portal_onsite_two_address']
        ,'enforce_signin_email' => $GLOBALS['enforce_signin_email']
        ,'uname' => $_POST['uname']
        ,'login_uname' => $_POST['login_uname']
        ,'pwd' => $clear_pass
        ,'email_direct' => trim($trustedEmail['email_direct'])
        ,'fhir_address' => $fhirServerConfig->getFhirUrl()
        ,'fhir_requirements_address' => $fhirServerConfig->getFhir3rdPartyAppRequirementsDocument()
    ];

    $htmlMessage = $twig->render('emails/patient/portal_login/message.html.twig', $data);
    $plainMessage = $twig->render('emails/patient/portal_login/message.text.twig', $data);

    // Email and display/print the message
    if (emailLogin($pid, $htmlMessage, $plainMessage, $twig)) {
        // email was sent
        $credMessage = nl2br(displayLogin($pid, $plainMessage, true));
    } else {
        // email wasn't sent
        $credMessage = nl2br(displayLogin($pid, $plainMessage, false));
    }
    // need to track that credentials were created
} else {
    $credMessage = '';
}

echo $twig->render('patient/portal_login/print.html.twig', [
        'credMessage' => $credMessage
        , 'csrfToken' => CsrfUtils::collectCsrfToken()
        , 'fname' => $row['fname']
        , 'portal_username' => $row['portal_username']
        , 'id' => $row['id']
        , 'uname' => $row['portal_username'] ? $row['portal_username'] : $row['fname'] . $row['id']
        , 'login_uname' => !empty($trustedUserName) ? $trustedUserName : $row['portal_username']
        , 'pwd' => RandomGenUtils::generatePortalPassword()
        , 'enforce_signin_email' => $GLOBALS['enforce_signin_email']
        , 'email_direct' => trim($trustedEmail['email_direct'])
]);
