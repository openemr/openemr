<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// below brings in autoloader
require_once(__DIR__ . "/../_rest_config.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\RestControllers\AuthorizationController;

$gbl = RestConfig::GetInstance();
if (empty($gbl::$SITE)) {
    http_response_code(401);
    exit;
}

// Will start the oauth OpenEMR session/cookie.
SessionUtil::oauthSessionStart($gbl::$web_root);

$_GET['site'] = $gbl::$SITE;
//  No need for sessionAllowWrite since using oauth session
$ignoreAuth = true;
require_once __DIR__ . '/../interface/globals.php';

$logger = SystemLogger::instance();

// exit if api is not turned on
if (empty($GLOBALS['rest_api']) && empty($GLOBALS['rest_fhir_api']) && empty($GLOBALS['rest_portal_api']) && empty($GLOBALS['rest_portal_fhir_api'])) {
    $logger->debug("api disabled exiting call");
    SessionUtil::oauthSessionCookieDestroy();
    http_response_code(404);
    exit;
}

// ensure 1) sane site 2) site from gbl and globals are the same and 3) ensure the site exists on filesystem
if (empty($gbl::$SITE) || empty($_SESSION['site_id']) || preg_match('/[^A-Za-z0-9\\-.]/', $gbl::$SITE) || ($gbl::$SITE != $_SESSION['site_id']) || !file_exists($GLOBALS['OE_SITES_BASE'] . '/' . $_SESSION['site_id'])) {
    // error collecting site
    $logger->error("OpenEMR error - oauth2 error since unable to properly collect site, so forced exit");
    SessionUtil::oauthSessionCookieDestroy();
    http_response_code(400);
    exit;
}

// set up csrf
//  used to prevent csrf in the 2 different types of submissions by oauth2/provider/login.php
if (empty($_SESSION['csrf_private_key'])) {
    CsrfUtils::setupCsrfKey();
}

$end_point = $gbl::getRequestEndPoint();
$logger->debug("oauth2 request received", ["endpoint" => $end_point]);

// let's quickly be able to enable our CORS at the PHP level.
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: origin, authorization, accept, content-type, x-requested-with");
header("Access-Control-Allow-Methods: GET, HEAD, POST, PUT, DELETE, TRACE, OPTIONS");
header("Access-Control-Allow-Origin: *");

$authServer = new AuthorizationController();

if (false !== stripos($end_point, '/token')) {
    // session is destroyed within below function
    $authServer->oauthAuthorizeToken();
    exit;
}

if (false !== stripos($end_point, '/openid-configuration')) {
    $oauthdisc = true;
    $base_url = $authServer->authBaseFullUrl;
    require_once("provider/.well-known/discovery.php");
    exit;
}

if (false !== stripos($end_point, '/authorize')) {
    // session is destroyed (when throws exception) within below function
    $authServer->oauthAuthorizationFlow();
    exit;
}

if (false !== stripos($end_point, '/device/code')) {
    // session is destroyed within below function
    $authServer->authorizeUser();
    exit;
}

if (false !== stripos($end_point, '/jwk')) {
    $oauthjwk = true;
    require_once(__DIR__ . "/provider/jwk.php");
    exit;
}

if (false !== stripos($end_point, '/login')) {
    // session is maintained
    $authServer->userLogin();
    exit;
}

if (false !== stripos($end_point, '/registration')) {
    // session is destroyed within below function
    $authServer->clientRegistration();
    exit;
}

if (false !== stripos($end_point, '/client')) {
    // session is destroyed within below function
    $authServer->clientRegisteredDetails();
    exit;
}

if (false !== stripos($end_point, '/logout')) {
    // session is destroyed within below function
    $authServer->userSessionLogout();
    exit;
}

if (false !== stripos($end_point, '/introspect')) {
    // session is destroyed within below function
    $authServer->tokenIntrospection();
    exit;
}
