<?php
/**
 * Rest Dispatch
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
 * Copyright (C) 2018 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
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
if ($resource === "/api/auth" || $resource === "/fhir/auth") {
    // Get a site id from initial login authentication.
    $data = (array)(json_decode(file_get_contents("php://input")));
    $site = empty($data['client_id']) ? "default" : $data['client_id'];
    $_GET['site'] = $site;
} else {
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
}

$ignoreAuth = true;
require_once("./../interface/globals.php");
require_once("./../library/acl.inc");

if (!$GLOBALS['rest_api']) {
    http_response_code(501);
    exit();
}
// api flag must be four chars
//
if (is_fhir_request($resource))
    $_SESSION['api'] = 'fhir';
else
    $_SESSION['api'] = 'oemr';

use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\RestControllers\AuthRestController;

function get_bearer_token()
{
    $parse = preg_split("/[\s,]+/", $_SERVER["HTTP_AUTHORIZATION"]);
    if (strtoupper(trim($parse[0])) !== 'BEARER') return false;

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
    if ($resource !== "/api/auth" && $resource !== "/fhir/auth") {
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
// dispatch $routes called by ref.
HttpRestRouteHandler::dispatch($routes, $resource, $_SERVER["REQUEST_METHOD"]);
// Tear down session for security.
$gbl->destroySession();
