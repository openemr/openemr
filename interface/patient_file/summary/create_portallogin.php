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
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Stephen Waite <stephen.waite@open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once('../../../library/amc.php');

use OpenEMR\Common\ {
    Csrf\CsrfUtils,
    Utils\RandomGenUtils,
};
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

$credMessage = '';
if (isset($_POST['form_save']) && $_POST['form_save'] == 'submit') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    // TODO: @adunsulag do we clear the pwd variables here?? Hard to break it out into separate functions when we do that...
    $result = $patientAccessOnSiteService->saveCredentials($pid, $_POST['pwd'], $_POST['uname'], $_POST['login_uname']);
    if (!empty($result)) {
        $emailResult = $patientAccessOnSiteService->sendCredentialsEmail($pid, $result['pwd'], $result['uname'], $result['login_uname'], $result['email_direct']);
        if ($emailResult['success']) {
            $credMessage = nl2br(displayLogin($pid, $emailResult['plainMessage'], true));
        } else {
            $credMessage = nl2br(displayLogin($pid, $emailResult['plainMessage'], false));
        }
    }
} else {
    $credMessage = '';
}
$trustedUserName = $patientAccessOnSiteService->getUniqueTrustedUsernameForPid($pid);
$trustedEmail = $patientAccessOnSiteService->getTrustedEmailForPid($pid);

echo $patientAccessOnSiteService->filterTwigTemplateData($pid, 'patient/portal_login/print.html.twig', [
        'credMessage' => $credMessage
        , 'csrfToken' => CsrfUtils::collectCsrfToken()
        , 'fname' => $credentials['fname']
        , 'portal_username' => $credentials['portal_username']
        , 'id' => $credentials['id']
        , 'uname' => $credentials['portal_username'] ? $credentials['portal_username'] : $credentials['fname'] . $credentials['id']
        , 'login_uname' => !empty($trustedUserName) ? $trustedUserName : $credentials['portal_username']
        , 'pwd' => $patientAccessOnSiteService->getRandomPortalPassword()
        , 'enforce_signin_email' => $GLOBALS['enforce_signin_email']
        , 'email_direct' => trim($trustedEmail['email_direct'])
        // if someone wants to add additional data fields they can add this in as a
        // key => [...] property where the key is the template filename
        // which must exist inside a twig directory path of 'patient/partials/' and end with the '.html.twig' extension
        // the mapped value is the data array that will be passed to the twig template.
        , 'extensionsFormFields' => []
        , 'extensionsJavascript' => []
]);
