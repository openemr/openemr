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

// below brings in autoloader
require_once("./../_rest_config.php");

use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;
use Psr\Http\Message\ResponseInterface;

$gbl = RestConfig::GetInstance();
$restRequest = new HttpRestRequest($gbl, $_SERVER);
$routes = array();

// Parse needed information from Redirect or REQUEST_URI
$resource = $gbl::getRequestEndPoint();
$logger = new SystemLogger();
$logger->debug("dispatch.php requested", ["resource" => $resource, "method" => $_SERVER['REQUEST_METHOD']]);

$skipApiAuth = false;
if (!empty($_SERVER['HTTP_APICSRFTOKEN'])) {
    // Calling api from within the same session (ie. isLocalApi) since a apicsrftoken header was passed
    $isLocalApi = true;
    $gbl::setLocalCall();
    $skipApiAuth = false;
    $ignoreAuth = false;
} elseif ($gbl::skipApiAuth($resource)) {
    // For rest api endpoints that do not require auth, such as the capability statement
    //  note that the site is validated in the skipApiAuth() function
    // refactor resource
    $resource = str_replace('/' . $gbl::$SITE, '', $resource);
    // set site
    $_GET['site'] = $gbl::$SITE;
    $isLocalApi = false;
    $skipApiAuth = true;
    $ignoreAuth = true;
} else {
    // Calling api via rest
    // ensure token is valid
    $tokenRaw = $gbl::verifyAccessToken();
    if ($tokenRaw instanceof ResponseInterface) {
        $logger->error("dispatch.php failed token verify for resource", ["resource" => $resource]);
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
    $logger->debug("Parsed oauth_scopes in AccessToken", ["scopes" => $scopes]);
    foreach ($scopes as $attr) {
        if (stripos($attr, 'site:') !== false) {
            $site = str_replace('site:', '', $attr);
            // while here parse site from endpoint
            $resource = str_replace('/' . $site, '', $resource);
        }
    }
    // set our scopes and updated resources as needed
    $restRequest->setAccessTokenScopes($scopes);

    // ensure 1) sane site 2) site from gbl and access token are the same and 3) ensure the site exists on filesystem
    if (empty($site) || empty($gbl::$SITE) || preg_match('/[^A-Za-z0-9\\-.]/', $gbl::$SITE) || ($site !== $gbl::$SITE) || !file_exists(__DIR__ . '/../sites/' . $gbl::$SITE)) {
        $logger->error("OpenEMR Error - api site error, so forced exit");
        http_response_code(400);
        exit();
    }
    // set the site
    $_GET['site'] = $site;

    // set the scopes globals for endpoint permission checking
    $GLOBALS['oauth_scopes'] = $scopes;

    // collect openemr user uuid
    $userId = $attributes['oauth_user_id'];
    // collect client id (will be empty for PKCE)
    $clientId = $attributes['oauth_client_id'] ?? null;
    // collect token id
    $tokenId = $attributes['oauth_access_token_id'];
    // ensure user uuid and token id are populated
    if (empty($userId) || empty($tokenId)) {
        $logger->error("OpenEMR Error - userid or tokenid not available, so forced exit", ['attributes' => $attributes]);
        http_response_code(400);
        exit();
    }
    $restRequest->setClientId($clientId);
    $restRequest->setAccessTokenId($tokenId);

    // Get a site id from initial login authentication.
    $isLocalApi = false;
    $skipApiAuth = false;
    $ignoreAuth = true;
}

// set the route as well as the resource information.  Note $resource is actually the route and not the resource name.
$restRequest->setRequestPath($resource);

if (!$isLocalApi) {
    // Will start the api OpenEMR session/cookie.
    SessionUtil::apiSessionStart($gbl::$web_root);
}

$GLOBALS['is_local_api'] = $isLocalApi;
$restRequest->setIsLocalApi($isLocalApi);

// Set $sessionAllowWrite to true here for following reasons:
//  1. !$isLocalApi - not applicable since use the SessionUtil::apiSessionStart session, which was set above
//  2. $isLocalApi - in this case, basically setting this to true downstream after some session sets via session_write_close() call
$sessionAllowWrite = true;
require_once("./../interface/globals.php");

// we now can check the database to see if the token is revoked
// Note despite League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator.php:L117 already checking for revoked
// access token we have to do this logic here as we use the access token SCOPE parameter to determine our multi-site setting
// and load up the correct database, our earlier access token logic returns false for revoked as we don't have db access
//  for that reason we have this double check on validating the access token.
if (!empty($tokenId)) {
    $result = $gbl::validateAccessTokenRevoked($tokenId);
    if ($result instanceof ResponseInterface) {
        $logger->error("dispatch.php access token was revoked", ["resource" => $resource]);
        // failed token verify
        // not a request object so send the error as response obj
        $gbl::emitResponse($result);
        exit;
    }
}


// recollect this so the DEBUG global can be used if set
$logger = new SystemLogger();

$gbl::$apisBaseFullUrl = $GLOBALS['site_addr_oath'] . $GLOBALS['webroot'] . "/apis/" . $gbl::$SITE;
$restRequest->setApiBaseFullUrl($gbl::$apisBaseFullUrl);

if ($isLocalApi) {
    // need to check for csrf match when using api locally
    $csrfFail = false;

    if (empty($_SERVER['HTTP_APICSRFTOKEN'])) {
        $logger->error("OpenEMR Error: internal api failed because csrf token not received");
        $csrfFail = true;
    }

    if ((!$csrfFail) && (!CsrfUtils::verifyCsrfToken($_SERVER['HTTP_APICSRFTOKEN'], 'api'))) {
        $logger->error("OpenEMR Error: internal api failed because csrf token did not match");
        $csrfFail = true;
    }

    if ($csrfFail) {
        $logger->error("dispatch.php CSRF failed", ["resource" => $resource]);
        http_response_code(401);
        exit();
    }
} elseif ($skipApiAuth) {
    $logger->debug("dispatch.php skipping api auth");
    // For endpoints that do not require auth, such as the capability statement
} else {
    $logger->debug("dispatch.php authenticating user");
    // verify that user tokens haven't been revoked.
    // this is done by verifying the user is trusted with active auth session.
    $isTrusted = $gbl::isTrustedUser($attributes["oauth_client_id"], $attributes["oauth_user_id"]);
    if ($isTrusted instanceof ResponseInterface) {
        $logger->debug("dispatch.php oauth2 inactive user api attempt");
        // user is not logged on to server with an active session.
        // too me this is easier than revoking tokens or using phantom tokens.
        // give a 400(unsure here, could be a 401) so client can redirect to server.
        $gbl::destroySession();
        $gbl::emitResponse($isTrusted);
        exit;
    }
    // $isTrusted can be used for further validations using session_cache
    // which is a json. json_decode($isTrusted['session_cache'])

    // authenticate the token
    if (!$gbl->authenticateUserToken($tokenId, $clientId, $userId)) {
        $logger->error("dispatch.php api call with invalid token");
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
        $logger->error("OpenEMR Error - api user account could not be identified, so forced exit", [
            'userId' => $userId,
            'userRole' => $uuidToUser->getUserRole()]);
        $gbl::destroySession();
        http_response_code(400);
        exit();
    }
    if (empty($userRole)) {
        // unable to identify the users user role
        $logger->error("OpenEMR Error - api user role for user could not be identified, so forced exit");
        $gbl::destroySession();
        http_response_code(400);
        exit();
    }

    $restRequest->setAccessTokenId($tokenId);
    $restRequest->setRequestUserRole($userRole);
    $restRequest->setRequestUser($userId, $user);

    // verify that the scope covers the route
    if (
        // fhir routes are the default and can send openid/fhirUser w/ authorization_code, or no scopes at all
        // with Client Credentials, so we only reject requests for standard or portal if the correct scope is not
        // sent.
        ($gbl::is_api_request($resource) && !in_array('api:oemr', $GLOBALS['oauth_scopes'])) ||
        ($gbl::is_portal_request($resource) && !in_array('api:port', $GLOBALS['oauth_scopes']))
    ) {
        $logger->error("dispatch.php api call with token that does not cover the requested route");
        $gbl::destroySession();
        http_response_code(401);
        exit();
    }
    // ensure user role has access to the resource
    //  for now assuming:
    //   users has access to oemr and fhir
    //   patient has access to port and fhir
    if ($userRole == 'users' && ($gbl::is_api_request($resource) || $gbl::is_fhir_request($resource))) {
        $logger->debug("dispatch.php valid role and user has access to api/fhir resource", ['resource' => $resource]);
        // good to go
    } elseif ($userRole == 'patient' && ($gbl::is_portal_request($resource) || $gbl::is_fhir_request($resource))) {
        $logger->debug("dispatch.php valid role and patient has access portal resource", ['resource' => $resource]);
        // good to go
    } elseif ($userRole === 'system' && ($gbl::is_fhir_request($resource))) {
        $logger->debug("dispatch.php valid role and system has access to api/fhir resource", ['resource' => $resource]);
    } else {
        $logger->error("OpenEMR Error: api failed because user role does not have access to the resource", ['resource' => $resource, 'userRole' => $userRole]);
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
            $logger->error("OpenEMR Error: api failed because unable to set critical users session variables");
            $gbl::destroySession();
            http_response_code(401);
            exit();
        }
        if ($restRequest->requestHasScope(SmartLaunchController::CLIENT_APP_STANDALONE_LAUNCH_SCOPE)) {
            $restRequest = $gbl->populateTokenContextForRequest($restRequest);
        }
    } elseif ($userRole == 'patient') {
        $_SESSION['pid'] = $user['pid'] ?? null;
        $puuidCheck = $user['uuid'] ?? null;
        $puuidStringCheck = UuidRegistry::uuidToString($puuidCheck) ?? null;
        if (empty($_SESSION['pid']) || empty($puuidCheck) || empty($puuidStringCheck)) {
            // this should never happen
            $logger->error("OpenEMR Error: api failed because unable to set critical patient session variables");
            $gbl::destroySession();
            http_response_code(401);
            exit();
        }
        $restRequest->setPatientRequest(true);
        $restRequest->setPatientUuidString($puuidStringCheck);
    } else if ($userRole === 'system') {
        $_SESSION['authUser'] = $user["username"] ?? null;
        $_SESSION['authUserID'] = $user["id"] ?? null;
        if (
            empty($_SESSION['authUser'])
            // this should never happen as the system role depends on the system username... but we safety check it anyways
            || $_SESSION['authUser'] != \OpenEMR\Services\UserService::SYSTEM_USER_USERNAME
            || empty($_SESSION['authUserID'])
        ) {
            $logger->error("OpenEMR Error: api failed because unable to set critical users session variables");
            $gbl::destroySession();
            http_response_code(401);
            exit();
        }
    } else {
        // this user role is not supported
        $logger->error("OpenEMR Error - api user role that was provided is not supported, so forced exit");
        $gbl::destroySession();
        http_response_code(400);
        exit();
    }
}

//Extend API using RestApiCreateEvent
$restApiCreateEvent = new RestApiCreateEvent($gbl::$ROUTE_MAP, $gbl::$FHIR_ROUTE_MAP, $gbl::$PORTAL_ROUTE_MAP, $restRequest);
$restApiCreateEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch(RestApiCreateEvent::EVENT_HANDLE, $restApiCreateEvent, 10);
$gbl::$ROUTE_MAP = $restApiCreateEvent->getRouteMap();
$gbl::$FHIR_ROUTE_MAP = $restApiCreateEvent->getFHIRRouteMap();
$gbl::$PORTAL_ROUTE_MAP = $restApiCreateEvent->getPortalRouteMap();
$restRequest = $restApiCreateEvent->getRestRequest();

// api flag must be four chars
// Pass only routes for current api.
// Also check to ensure route is turned on in globals
if ($gbl::is_fhir_request($resource)) {
    if (!$GLOBALS['rest_fhir_api'] && !$isLocalApi) {
        // if the external fhir api is turned off and this is not a local api call, then exit
        $logger->error("dispatch.php attempted to access resource with FHIR api turned off ", ['resource' => $resource]);
        $gbl::destroySession();
        http_response_code(501);
        exit();
    }
    $_SESSION['api'] = 'fhir';
    $routes = $gbl::$FHIR_ROUTE_MAP;
} elseif ($gbl::is_portal_request($resource)) {
    if (!$GLOBALS['rest_portal_api'] && !$isLocalApi) {
        $logger->error("dispatch.php attempted to access resource with portal api turned off ", ['resource' => $resource]);
        // if the external portal api is turned off and this is not a local api call, then exit
        $gbl::destroySession();
        http_response_code(501);
        exit();
    }
    $_SESSION['api'] = 'port';
    $routes = $gbl::$PORTAL_ROUTE_MAP;
} elseif ($gbl::is_api_request($resource)) {
    if (!$GLOBALS['rest_api'] && !$isLocalApi) {
        $logger->error(
            "dispatch.php attempted to access resource with REST api turned off ",
            ['resource' => $resource]
        );
        // if the external api is turned off and this is not a local api call, then exit
        $gbl::destroySession();
        http_response_code(501);
        exit();
    }
    $_SESSION['api'] = 'oemr';
    $routes = $gbl::$ROUTE_MAP;
} else {
    $logger->error("dispatch.php invalid access to resource", ['resource' => $resource]);

    // somebody is up to no good
    if (!$isLocalApi) {
        $gbl::destroySession();
    }
    http_response_code(501);
    exit();
}

$restRequest->setApiType($_SESSION['api']);

if ($isLocalApi) {
    // Ensure that a local process does not hold up other processes
    //  Note can not do this for !$isLocalApi since need to be able to set
    //  session variables and it won't help performance anyways.
    session_write_close();
}

// dispatch $routes called by ref (note storing the output in a variable to allow option
//  to destroy the session/cookie before sending the output back)
ob_start();
$dispatchResult = HttpRestRouteHandler::dispatch($routes, $restRequest);
$apiCallOutput = ob_get_clean();
// Tear down session for security.
if (!$isLocalApi) {
    $gbl::destroySession();
}
// Send the output if not empty
if (!empty($apiCallOutput)) {
    echo $apiCallOutput;
} else if ($dispatchResult instanceof ResponseInterface) {
    RestConfig::emitResponse($dispatchResult);
}

// prevent 200 if route doesn't exist
if ($dispatchResult === false) {
    $logger->debug("dispatch.php no route found for resource", ['resource' => $resource]);
    http_response_code(404);
}
