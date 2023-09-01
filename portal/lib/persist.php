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

require_once(__DIR__ . '/../../src/Common/Session/SessionUtil.php');
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

$sessionAllowWrite = true;
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . '/../../interface/globals.php');
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(__DIR__ . '/../../interface/globals.php');
    if (!isset($_SESSION['authUserID'])) {
        $landingpage = 'index.php';
        header('Location: ' . $landingpage);
        exit;
    }
}

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\PatientPortalService;

$data = (array)(json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR));

if (!CsrfUtils::verifyCsrfToken($data['csrf_token_form'])) {
    CsrfUtils::csrfNotVerified();
}

if (!empty($data['where'] ?? null)) {
    $_SESSION['whereto'] = $data['where'];
}

// Set a patient setting to persist
if (!empty($data['setting_patient'] ?? null)) {
    if (!empty($data['setting_label'] ?? null)) {
        PatientPortalService::persistPatientSetting($data['setting_patient'] ?? 0, $data['setting_label'], $data['setting_value'] ?? '');
    }
}
