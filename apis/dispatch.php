<?php
/**
 * Rest Dispatch
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("./../_rest_config.php");

$gbl = RestConfig::GetInstance();

// set up the session if this is a local api call
$context = $gbl->GetContext();
if ($context) {
    session_write_close();
}

$base_path = $gbl::$ROOT_URL;
$routes = array();
$resource = '';

// Parse needed information from Redirect or REQUEST_URI
if (!empty($_REQUEST['_REWRITE_COMMAND'])) {
    $resource = "/" . $_REQUEST['_REWRITE_COMMAND'];
} elseif (!empty($_SERVER['REDIRECT_QUERY_STRING'])) {
    $resource = str_replace('_REWRITE_COMMAND=', '/', $_SERVER['REDIRECT_QUERY_STRING']);
} else {
    if (!empty($_SERVER['REQUEST_URI'])) {
        if (strpos($_SERVER['REQUEST_URI'], '?') > 0) {
            $resource = strstr($_SERVER['REQUEST_URI'], '?', true);
        } else {
            $resource = str_replace("$base_path", '', $_SERVER['REQUEST_URI']);
        }
    }
}

$ignoreAuth = true;
// Maintain site id for multi site compatibility.
// token is a 32 character hash followed by hex encoded 4 char api flag and site id.
if (is_authentication($resource)) {
    // Get a site id from initial login authentication.
    $data = (array) $gbl->getPostData((file_get_contents("php://input")));
    $site = empty($data['scope']) ? "default" : $data['scope'];
    $_GET['site'] = $site;
} elseif (!$context) {
    $token = get_bearer_token();
    if (strlen($token) > 40) {
        $api_token = substr($token, 0, 32);
        $rest = hex2bin(substr($token, 32));
        $api = substr($rest, 0, 4);
        $api_site = substr($rest, 4);
        verify_api_request($resource, $api);
        $_SERVER["HTTP_X_API_TOKEN"] = $api_token; // set hash to further the adventure.
        $_GET['site'] = $api_site; // site id
    } else {
        // token should always return with embedded site id
        http_response_code(401);
        exit();
    }
} else {
    // continue already authorized session.
    // let globals verify again.
    $ignoreAuth = false;
}

require_once("./../interface/globals.php");
require_once("./../library/acl.inc");

if (!$GLOBALS['rest_api'] && !$context) {
    http_response_code(501);
    exit();
}
// api flag must be four chars
// Pass only routes for current api.
//
if (is_fhir_request($resource)) {
    $_SESSION['api'] = 'fhir';
    $routes = $gbl::$FHIR_ROUTE_MAP;
} else {
    $_SESSION['api'] = 'oemr';
    $routes = $gbl::$ROUTE_MAP;
}

use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\RestControllers\AuthRestController;

function is_authentication($resource)
{
    return ($resource === "/api/auth" || $resource === "/fhir/auth");
}

function get_bearer_token()
{
    $parse = preg_split("/[\s,]+/", $_SERVER["HTTP_AUTHORIZATION"]);
    if (strtoupper(trim($parse[0])) !== 'BEARER') {
        return false;
    }

    return trim($parse[1]);
}

function is_fhir_request($resource)
{
    return (stripos(strtolower($resource), "/fhir/") !== false) ? true : false;
}

function verify_api_request($resource, $api)
{
    $api = strtolower(trim($api));
    if (is_fhir_request($resource)) {
        if ($api !== 'fhir') {
            http_response_code(401);
            exit();
        }
    } elseif ($api !== 'oemr') {
        http_response_code(401);
        exit();
    }

    return;
}

function authentication_check($resource)
{
    if (!is_authentication($resource)) {
        $token = $_SERVER["HTTP_X_API_TOKEN"];
        $authRestController = new AuthRestController();
        if (!$authRestController->isValidToken($token)) {
            http_response_code(401);
            exit();
        } else {
            $authRestController->optionallyAddMoreTokenTime($token);
        }
    }
}

function authorization_check($section, $value)
{
    global $context;

    $authRestController = new AuthRestController();
    if ($context) {
        $result = $authRestController->aclCheckByUsername($_SESSION['authUser'], $section, $value);
    } else {
        $result = $authRestController->aclCheck($_SERVER["HTTP_X_API_TOKEN"], $section, $value);
    }
    if (!$result) {
        http_response_code(401);
        exit();
    }
}

if (!$context) {
    authentication_check($resource);
}
// dispatch $routes called by ref.
HttpRestRouteHandler::dispatch($routes, $resource, $_SERVER["REQUEST_METHOD"]);
// Tear down session for security.
if (!$context) {
    $gbl->destroySession();
}
