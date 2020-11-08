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
// to our endpoint thus, below.
if (PHP_SESSION_ACTIVE !== \session_status()) {
    \session_id('authserver');
    \session_start();
}
$_GET['site'] = $gbl::$SITE;
//  No need for sessionAllowWrite.
$ignoreAuth = true;
require_once __DIR__ . '/../interface/globals.php';

use OpenEMR\RestControllers\AuthorizationController;

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

if (false !== stripos($end_point, '/password')) {
    $authServer->oauthPasswordFlow();
}

if (false !== stripos($end_point, '/device/code')) {
    $authServer->authorizeUser();
}

if (false !== stripos($end_point, '/jwk')) {
    require_once("./provider/jwk.php");
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
