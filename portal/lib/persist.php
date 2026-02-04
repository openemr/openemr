<?php

/**
 * persist.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
$session = SessionWrapperFactory::getInstance()->getWrapper();

$sessionAllowWrite = true;
if ($session->isSymfonySession() && !empty($session->get('pid')) && !empty($session->get('patient_portal_onsite_two'))) {
    $pid = $session->get('pid');
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . '/../../interface/globals.php');
} else {
    SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(__DIR__ . '/../../interface/globals.php');
    if (!$session->has('authUserID')) {
        $landingpage = 'index.php';
        header('Location: ' . $landingpage);
        exit;
    }
}

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\PatientPortalService;

$data = (array)(json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR));

if (!CsrfUtils::verifyCsrfToken($data['csrf_token_form'], 'default', $session->getSymfonySession())) {
    CsrfUtils::csrfNotVerified();
}

if (!empty($data['where'] ?? null)) {
    $session->set('whereto', $data['where']);
}

// Set a patient setting to persist
if (!empty($data['setting_patient'] ?? null)) {
    if (!empty($data['setting_label'] ?? null)) {
        PatientPortalService::persistPatientSetting($data['setting_patient'] ?? 0, $data['setting_label'], $data['setting_value'] ?? '');
    }
}
