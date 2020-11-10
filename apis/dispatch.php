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
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("./../_rest_config.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;
use Psr\Http\Message\ResponseInterface;

$gbl = RestConfig::GetInstance();
$base_path = $gbl::$ROOT_URL;
$routes = array();

// Parse needed information from Redirect or REQUEST_URI
$resource = $gbl::getRequestEndPoint();
// token is valid
$tokenRaw = $gbl::verifyAccessToken();
if ($tokenRaw instanceof ResponseInterface) {
    // failed token verify
    // not a request object so send the error as response obj
    $gbl::emitResponse($tokenRaw);
    exit;
}
$attributes = $tokenRaw->getAttributes();

// this confirms the access token has same scope to site from url.
$site = '';
$scopes = $attributes['oauth_scopes'];
foreach ($scopes as $attr) {
    if (stripos($attr, 'site:') !== false) {
        $site = str_replace('site:', '', $attr);
        // while here parse site from endpoint
        $resource = str_replace('/' . $site, '', $resource);
    }
}
// needs to match approved site from token
if ($site !== $gbl::$SITE) {
    http_response_code(400);
    exit();
}
// openemr user uuid
$userId = $attributes['oauth_user_id'];
// will be empty for PKCE
$clientId = $attributes['oauth_client_id'] ?? null;
// use this to lookup in api_token if want the id or refresh token.
$tokenId = $attributes['oauth_access_token_id'];

if (!empty($_SERVER['HTTP_APICSRFTOKEN'])) {
    // Calling api from within the same session (ie. isLocalApi) since a apicsrftoken header was passed
    $isLocalApi = true;
    $gbl::setLocalCall();
    $ignoreAuth = false;
} elseif (!empty($userId)) {
    // Get a site id from initial login authentication.
    $isLocalApi = false;
    $ignoreAuth = true;
    $_GET['site'] = $site;
} else {
    // something not right, so exit
    http_response_code(401);
    exit();
}

$GLOBALS['is_local_api'] = $isLocalApi;

// Set $sessionAllowWrite to true here for following reasons:
//  1. !$isLocalApi - in this case setting sessions far downstream and no benefit to set to false since single process
//  2. $isLocalApi - in this case, basically setting this to true downstream after some session sets via session_write_close() call
$sessionAllowWrite = true;
require_once("./../interface/globals.php");
// user roles for now. Patient role once we figure out how.
$user = $gbl->getUserAccount($userId, 'users');
// let acl catch in route if not valid.
$_SESSION['authUser'] = $user["username"] ?? null;
$_SESSION['authUserID'] = $user["id"] ?? null;
$_SESSION['authProvider'] = 'default';

//Extend API using RestApiCreateEvent
$restApiCreateEvent = new RestApiCreateEvent($gbl::$ROUTE_MAP, $gbl::$FHIR_ROUTE_MAP, $gbl::$PORTAL_ROUTE_MAP, $gbl::$PORTAL_FHIR_ROUTE_MAP);
$restApiCreateEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch(RestApiCreateEvent::EVENT_HANDLE, $restApiCreateEvent, 10);
$gbl::$ROUTE_MAP = $restApiCreateEvent->getRouteMap();
$gbl::$FHIR_ROUTE_MAP = $restApiCreateEvent->getFHIRRouteMap();
$gbl::$PORTAL_ROUTE_MAP = $restApiCreateEvent->getPortalRouteMap();
$gbl::$PORTAL_FHIR_ROUTE_MAP = $restApiCreateEvent->getPortalFHIRRouteMap();

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
}

// api flag must be four chars
// Pass only routes for current api.
// Also check to ensure route is turned on in globals
if ($gbl::is_fhir_request($resource)) {
    if (!$GLOBALS['rest_fhir_api'] && !$isLocalApi) {
        // if the external fhir api is turned off and this is not a local api call, then exit
        http_response_code(501);
        exit();
    }
    $_SESSION['api'] = 'fhir';
    $routes = $gbl::$FHIR_ROUTE_MAP;
} elseif ($gbl::is_portal_request($resource)) {
    if (!$GLOBALS['rest_portal_api'] && !$isLocalApi) {
        // if the external portal api is turned off and this is not a local api call, then exit
        http_response_code(501);
        exit();
    }
    $_SESSION['api'] = 'port';
    $routes = $gbl::$PORTAL_ROUTE_MAP;
} elseif ($gbl::is_portal_fhir_request($resource)) {
    if (!$GLOBALS['rest_portal_fhir_api'] && !$isLocalApi) {
        // if the external portal fhir api is turned off and this is not a local api call, then exit
        http_response_code(501);
        exit();
    }
    $_SESSION['api'] = 'pofh';
    $routes = $gbl::$PORTAL_FHIR_ROUTE_MAP;
} elseif ($gbl::is_api_request($resource)) {
    if (!$GLOBALS['rest_api'] && !$isLocalApi) {
        // if the external api is turned off and this is not a local api call, then exit
        http_response_code(501);
        exit();
    }
    $_SESSION['api'] = 'oemr';
    $routes = $gbl::$ROUTE_MAP;
} else {
    // somebody is up to no good
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
