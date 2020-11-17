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

require_once(__DIR__ . "/../_rest_config.php");

$gbl = RestConfig::GetInstance();
if (empty($gbl::$SITE)) {
    http_response_code(401);
    exit;
}

// strange and frustrating that globals session isn't maintained from cross origin
//  to our endpoint thus, below.
// Will start the oauth OpenEMR session/cookie.
require_once(__DIR__ . "/../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::oauthSessionStart($gbl::$web_root);

$_GET['site'] = $gbl::$SITE;
//  No need for sessionAllowWrite since using oauth session
$ignoreAuth = true;
require_once __DIR__ . '/../interface/globals.php';

use OpenEMR\RestControllers\AuthorizationController;

// exit if api is not turned on
if (empty($GLOBALS['rest_api']) && empty($GLOBALS['rest_fhir_api']) && empty($GLOBALS['rest_portal_api']) && empty($GLOBALS['rest_portal_fhir_api'])) {
    http_response_code(404);
    exit;
}

// ensure 1) sane site 2) site from gbl and globals are the same and 3) ensure the site exists on filesystem
if (empty($gbl::$SITE) || empty($_SESSION['site_id']) || preg_match('/[^A-Za-z0-9\\-.]/', $gbl::$SITE) || ($gbl::$SITE != $_SESSION['site_id']) || !file_exists($GLOBALS['OE_SITES_BASE'] . '/' . $_SESSION['site_id'])) {
    // error collecting site
    error_log("OpenEMR error - oauth2 error since unable to properly collect site, so forced exit");
    http_response_code(400);
    exit;
}

$end_point = $gbl::getRequestEndPoint();

$authServer = new AuthorizationController();

if (false !== stripos($end_point, '/token')) {
    $authServer->oauthAuthorizeToken();
}

if (false !== stripos($end_point, '/openid-configuration')) {
    require_once("provider/.well-known/discovery.php");
}

if (false !== stripos($end_point, '/authorize')) {
    $authServer->oauthAuthorizationFlow();
}

if (!empty($GLOBALS['oauth_password_grant']) && (false !== stripos($end_point, '/password'))) {
    $authServer->oauthPasswordFlow();
}

if (false !== stripos($end_point, '/device/code')) {
    $authServer->authorizeUser();
}

if (false !== stripos($end_point, '/jwk')) {
    $oauthjwk = true;
    require_once(__DIR__ . "/provider/jwk.php");
}

if (false !== stripos($end_point, '/login')) {
    $authServer->userLogin();
}

if (false !== stripos($end_point, '/registration')) {
    $authServer->clientRegistration();
}

if (false !== stripos($end_point, '/client')) {
    $authServer->clientRegisteredDetails();
}
