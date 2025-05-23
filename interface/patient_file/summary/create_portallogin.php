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
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Stephen Waite <stephen.waite@open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Stephen Waite <stephen.waite@open-emr.org
 * @copyright Copyright (c) 2017-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once('../../../library/amc.php');

use OpenEMR\Common\{Csrf\CsrfUtils,};
use OpenEMR\Services\PatientAccessOnsiteService;

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

$patientAccessOnSiteService = new PatientAccessOnsiteService();
$credentials = $patientAccessOnSiteService->getOnsiteCredentialsForPid($pid);

$option = $GLOBALS['portal_force_credential_reset'] ?? '0';
if ($option == '2') {
    $forced_reset_disable = PatientAccessOnsiteService::fetchUserSetting('portal_login.credential_reset_disable');
} elseif ($option == '0') {
    $forced_reset_disable = 0; // sets database to force reset on login
} else {
    $forced_reset_disable = 1; // sets database to ignore force reset on login
}

$credMessage = '';
if (isset($_POST['form_save']) && $_POST['form_save'] == 'submit') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    if ($option == '2') {
        $forced_reset_disable = $_POST['forced_reset_disable'] ?? 0;
    } else {
        $forced_reset_disable = $option;
    }
    // TODO: @adunsulag do we clear the pwd variables here?? Hard to break it out into separate functions when we do that...
    $result = $patientAccessOnSiteService->saveCredentials($pid, $_POST['pwd'], $_POST['uname'], $_POST['login_uname'], $forced_reset_disable);
    if (!empty($result)) {
        $emailResult = $patientAccessOnSiteService->sendCredentialsEmail($pid, $result['pwd'], $result['uname'], $result['login_uname'], $result['email_direct']);
        if ($emailResult['success']) {
            $credMessage = nl2br(displayLogin($pid, $emailResult['plainMessage'], true));
        } else {
            $credMessage = nl2br(displayLogin($pid, $emailResult['plainMessage'], false));
        }
    }
}
$trustedUserName = $patientAccessOnSiteService->getUniqueTrustedUsernameForPid($pid);
$trustedEmail = $patientAccessOnSiteService->getTrustedEmailForPid($pid);

echo $patientAccessOnSiteService->filterTwigTemplateData($pid, 'patient/portal_login/print.html.twig', [
    'credMessage' => $credMessage
    , 'csrfToken' => CsrfUtils::collectCsrfToken()
    , 'fname' => $credentials['fname']
    , 'portal_username' => $credentials['portal_username']
    , 'id' => $credentials['id']
    , 'uname' => $credentials['portal_username'] ?: $credentials['fname'] . $credentials['lname'] . $credentials['id']
    , 'login_uname' => $credentials['portal_login_username'] ?? $trustedUserName
    , 'pwd' => $patientAccessOnSiteService->getRandomPortalPassword()
    , 'enforce_signin_email' => $GLOBALS['enforce_signin_email']
    , 'email_direct' => trim($trustedEmail['email_direct'])
    , 'forced_reset_disable' => $forced_reset_disable
    , 'forced_reset_option' => $option
    // if someone wants to add additional data fields they can add this in as a
    // key => [...] property where the key is the template filename
    // which must exist inside a twig directory path of 'patient/partials/' and end with the '.html.twig' extension
    // the mapped value is the data array that will be passed to the twig template.
    , 'extensionsFormFields' => []
    , 'extensionsJavascript' => []
]);
