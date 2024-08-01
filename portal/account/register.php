<?php

/**
 * Portal Registration Wizard
 * twig loader for registration
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// script is brought in as require_once in index.php when applicable

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Twig\TwigContainer;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

if ($portalRegistrationAuthorization !== true) {
    (new SystemLogger())->debug("Attempted to use register.php directly, so failed");
    SessionUtil::portalSessionCookieDestroy();
    echo xlt("Not Authorized");
    header('HTTP/1.1 401 Unauthorized');
    die();
}

if (empty($GLOBALS['portal_onsite_two_register']) || empty($GLOBALS['google_recaptcha_site_key']) || empty($GLOBALS['google_recaptcha_secret_key'])) {
    (new SystemLogger())->debug("Attempted to use register.php despite register feature being turned off, so failed");
    SessionUtil::portalSessionCookieDestroy();
    echo xlt("Not Authorized");
    header('HTTP/1.1 401 Unauthorized');
    die();
}

unset($_SESSION['itsme']);
$_SESSION['authUser'] = 'portal-user';
$_SESSION['pid'] = true;
$_SESSION['register'] = true;
$_SESSION['register_silo_ajax'] = true;

$landingpage = "index.php?site=" . urlencode($_SESSION['site_id']);

// Prepare data for the template
$data = [
'global' => $GLOBALS,
'session' => $_SESSION,
'languageRegistration' => $languageRegistration ?? '',
'fnameRegistration' => $fnameRegistration ?? '',
'mnameRegistration' => $mnameRegistration ?? '',
'lnameRegistration' => $lnameRegistration ?? '',
'dobRegistration' => $dobRegistration ?? '',
'emailRegistration' => $emailRegistration ?? '',
];

// Render Register Twig template
$twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
try {
    echo $twig->render('portal/registration/portal_register.html.twig', $data);
} catch (LoaderError | SyntaxError | RuntimeError $e) {
    (new SystemLogger())->error($e->getMessage());
    echo text($e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    die();
}

exit();
