<?php
/**
 * Rest Dispatch
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

include_once("./../_rest_config.php");

$gbl = RestConfig::GetInstance();
$routes = $gbl::$ROUTE_MAP;
$base_path = $gbl::$ROOT_URL;
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

// Maintain site id for multi site compatibility.
// token is a 32 character hash followed by hex encoded site id.
if ($resource === "/api/auth") {
    // Get a site id from initial log in authentication.
    $data = (array)(json_decode(file_get_contents("php://input")));
    $site = empty($data['site']) ? "default" : $data['site'];
    $_GET['site'] = $site;
} else {
    if (strlen($_SERVER["HTTP_X_API_TOKEN"]) > 32) {
        $token = str_split($_SERVER["HTTP_X_API_TOKEN"], 32);
        $_SERVER["HTTP_X_API_TOKEN"] = $token[0]; // reset hash to further the adventure.
        $_GET['site'] = hex2bin($token[1]); // site id
    } else {
        // token should always return with embedded site id
        // remove for production and comment in 401
        $_GET['site'] = "default";
        //http_response_code(401);
        //exit;
    }
}

$ignoreAuth = true;
require_once("./../interface/globals.php");
require_once("./../library/acl.inc");

use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\RestControllers\AuthRestController;

function authentication_check($resource)
{
    if ($resource !== "/api/auth") {
        $token = $_SERVER["HTTP_X_API_TOKEN"];
        $authRestController = new AuthRestController();
        if (!$authRestController->isValidToken($token)) {
            http_response_code(401);
            exit;
        } else {
            $authRestController->optionallyAddMoreTokenTime($token);
        }
    }
}

function authorization_check($section, $value)
{
    $authRestController = new AuthRestController();
    $result = $authRestController->aclCheck($_SERVER["HTTP_X_API_TOKEN"], $section, $value);

    if (!$result) {
        http_response_code(401);
        exit;
    }
}


authentication_check($resource);

HttpRestRouteHandler::dispatch($routes, $resource, $_SERVER["REQUEST_METHOD"]);
// Tear down session for security.
$gbl->destroySession();
