<?php

/**
 * Rest Dispatch
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("./../_rest_config.php");

use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;
use Psr\Http\Message\ResponseInterface;

$gbl = RestConfig::GetInstance();
$routes = array();

// Parse needed information from Redirect or REQUEST_URI
$resource = $gbl::getRequestEndPoint();

if (!empty($_SERVER['HTTP_APICSRFTOKEN'])) {
    // Calling api from within the same session (ie. isLocalApi) since a apicsrftoken header was passed
    $isLocalApi = true;
    $gbl::setLocalCall();
    $ignoreAuth = false;
} else {
    // Calling api via rest
    // ensure token is valid
    $tokenRaw = $gbl::verifyAccessToken();
    if ($tokenRaw instanceof ResponseInterface) {
        // failed token verify
        // not a request object so send the error as response obj
        $gbl::emitResponse($tokenRaw);
        exit;
    }

    // collect token attributes
    $attributes = $tokenRaw->getAttributes();

    // collect site
    $site = '';
    $scopes = $attributes['oauth_scopes'];
    foreach ($scopes as $attr) {
        if (stripos($attr, 'site:') !== false) {
            $site = str_replace('site:', '', $attr);
            // while here parse site from endpoint
            $resource = str_replace('/' . $site, '', $resource);
        }
    }
    // ensure 1) sane site 2) site from gbl and access token are the same and 3) ensure the site exists on filesystem
    if (empty($site) || empty($gbl::$SITE) || preg_match('/[^A-Za-z0-9\\-.]/', $gbl::$SITE) || ($site !== $gbl::$SITE) || !file_exists(__DIR__ . '/../sites/' . $gbl::$SITE)) {
        error_log("OpenEMR Error - api site error, so forced exit");
        http_response_code(400);
        exit();
    }
    // set the site
    $_GET['site'] = $site;

    // collect openemr user uuid
    $userId = $attributes['oauth_user_id'];
    // collect client id (will be empty for PKCE)
    $clientId = $attributes['oauth_client_id'] ?? null;
    // collect token id
    $tokenId = $attributes['oauth_access_token_id'];
    // ensure user uuid and token id are populated
    if (empty($userId) || empty($tokenId)) {
        error_log("OpenEMR Error - userid or tokenid not available, so forced exit");
        http_response_code(400);
        exit();
    }

    // Get a site id from initial login authentication.
    $isLocalApi = false;
    $ignoreAuth = true;
}

$GLOBALS['is_local_api'] = $isLocalApi;

// Set $sessionAllowWrite to true here for following reasons:
//  1. !$isLocalApi - in this case setting sessions far downstream and no benefit to set to false since single process
//  2. $isLocalApi - in this case, basically setting this to true downstream after some session sets via session_write_close() call
$sessionAllowWrite = true;
require_once("./../interface/globals.php");

if ($isLocalApi) {
    // need to check for csrf match when using api locally
    $csrfFail = false;

    if (empty($_SERVER['HTTP_APICSRFTOKEN'])) {
        error_log("OpenEMR Error: internal api failed because csrf token not received");
        $csrfFail = true;
    }

    if ((!$csrfFail) && (!CsrfUtils::verifyCsrfToken($_SERVER['HTTP_APICSRFTOKEN'], 'api'))) {
        error_log("OpenEMR Error: internal api failed because csrf token did not match");
        $csrfFail = true;
    }

    if ($csrfFail) {
        http_response_code(401);
        exit();
    }
} else {
    // authenticate the token
    if (!$gbl->authenticateUserToken($tokenId, $userId)) {
        $gbl::destroySession();
        http_response_code(401);
        exit();
    }
    // collect user information and user role
    $uuidToUser = new UuidUserAccount($userId);
    $user = $uuidToUser->getUserAccount();
    $userRole = $uuidToUser->getUserRole();
    if (empty($user)) {
        // unable to identify the users user role
        error_log("OpenEMR Error - api user account could not be identified, so forced exit");
        $gbl::destroySession();
        http_response_code(400);
        exit();
    }
    if (empty($userRole)) {
        // unable to identify the users user role
        error_log("OpenEMR Error - api user role for user could not be identified, so forced exit");
        $gbl::destroySession();
        http_response_code(400);
        exit();
    }
    // ensure user role has access to the resource
    //  for now assuming:
    //   users has access to oemr and fhir
    //   patient has access to port and pofh
    if ($userRole == 'users' && ($gbl::is_api_request($resource) || $gbl::is_fhir_request($resource))) {
        // good to go
    } elseif ($userRole == 'patient' && ($gbl::is_portal_request($resource) || $gbl::is_portal_fhir_request($resource))) {
        // good to go
    } else {
        error_log("OpenEMR Error: api failed because user role does not have access to the resource");
        $gbl::destroySession();
        http_response_code(401);
        exit();
    }
    // set pertinent session variables
    if ($userRole == 'users') {
        $_SESSION['authUser'] = $user["username"] ?? null;
        $_SESSION['authUserID'] = $user["id"] ?? null;
        $_SESSION['authProvider'] =  sqlQueryNoLog("SELECT `name` FROM `groups` WHERE `user` = ?", [$_SESSION['authUser']])['name'] ?? null;
        if (empty($_SESSION['authUser']) || empty($_SESSION['authUserID']) || empty($_SESSION['authProvider'])) {
            // this should never happen
            error_log("OpenEMR Error: api failed because unable to set critical users session variables");
            $gbl::destroySession();
            http_response_code(401);
            exit();
        }
    } elseif ($userRole == 'patient') {
        $_SESSION['pid'] = $user['pid'] ?? null;
        $_SESSION['puuid'] = $user['uuid'] ?? null;
        if (empty($_SESSION['pid']) || empty($_SESSION['puuid'])) {
            // this should never happen
            error_log("OpenEMR Error: api failed because unable to set critical patient session variables");
            $gbl::destroySession();
            http_response_code(401);
            exit();
        }
    } else {
        // this user role is not supported
        error_log("OpenEMR Error - api user role that was provided is not supported, so forced exit");
        $gbl::destroySession();
        http_response_code(400);
        exit();
    }
}

//Extend API using RestApiCreateEvent
$restApiCreateEvent = new RestApiCreateEvent($gbl::$ROUTE_MAP, $gbl::$FHIR_ROUTE_MAP, $gbl::$PORTAL_ROUTE_MAP, $gbl::$PORTAL_FHIR_ROUTE_MAP);
$restApiCreateEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch(RestApiCreateEvent::EVENT_HANDLE, $restApiCreateEvent, 10);
$gbl::$ROUTE_MAP = $restApiCreateEvent->getRouteMap();
$gbl::$FHIR_ROUTE_MAP = $restApiCreateEvent->getFHIRRouteMap();
$gbl::$PORTAL_ROUTE_MAP = $restApiCreateEvent->getPortalRouteMap();
$gbl::$PORTAL_FHIR_ROUTE_MAP = $restApiCreateEvent->getPortalFHIRRouteMap();

// api flag must be four chars
// Pass only routes for current api.
// Also check to ensure route is turned on in globals
if ($gbl::is_fhir_request($resource)) {
    if (!$GLOBALS['rest_fhir_api'] && !$isLocalApi) {
        // if the external fhir api is turned off and this is not a local api call, then exit
        $gbl::destroySession();
        http_response_code(501);
        exit();
    }
    $_SESSION['api'] = 'fhir';
    $routes = $gbl::$FHIR_ROUTE_MAP;
} elseif ($gbl::is_portal_request($resource)) {
    if (!$GLOBALS['rest_portal_api'] && !$isLocalApi) {
        // if the external portal api is turned off and this is not a local api call, then exit
        $gbl::destroySession();
        http_response_code(501);
        exit();
    }
    $_SESSION['api'] = 'port';
    $routes = $gbl::$PORTAL_ROUTE_MAP;
} elseif ($gbl::is_portal_fhir_request($resource)) {
    if (!$GLOBALS['rest_portal_fhir_api'] && !$isLocalApi) {
        // if the external portal fhir api is turned off and this is not a local api call, then exit
        $gbl::destroySession();
        http_response_code(501);
        exit();
    }
    $_SESSION['api'] = 'pofh';
    $routes = $gbl::$PORTAL_FHIR_ROUTE_MAP;
} elseif ($gbl::is_api_request($resource)) {
    if (!$GLOBALS['rest_api'] && !$isLocalApi) {
        // if the external api is turned off and this is not a local api call, then exit
        $gbl::destroySession();
        http_response_code(501);
        exit();
    }
    $_SESSION['api'] = 'oemr';
    $routes = $gbl::$ROUTE_MAP;
} else {
    // somebody is up to no good
    if (!$isLocalApi) {
        $gbl::destroySession();
    }
    http_response_code(501);
    exit();
}

if ($isLocalApi) {
    // Ensure that a local process does not hold up other processes
    //  Note can not do this for !$isLocalApi since need to be able to set
    //  session variables and it won't help performance anyways.
    session_write_close();
}

// dispatch $routes called by ref.
$hasRoute = HttpRestRouteHandler::dispatch($routes, $resource, $_SERVER["REQUEST_METHOD"]);
// Tear down session for security.
if (!$isLocalApi) {
    $gbl::destroySession();
}
// prevent 200 if route doesn't exist
if (!$hasRoute) {
    http_response_code(404);
}
