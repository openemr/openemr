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


$ignoreAuth = true;

require_once("./../interface/globals.php");
include_once("./../_rest_config.php");
include_once("./../_rest_routes.inc.php");

use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\RestControllers\AuthRestController;


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

function authentication_check($resource)
{
    $authRestController = new AuthRestController();
    if ($resource !== "/api/auth") {
        if (!$authRestController->isValidToken($_SERVER["HTTP_X_API_TOKEN"])) {
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

//authentication_check($resource);

HttpRestRouteHandler::dispatch($routes, $resource, $_SERVER["REQUEST_METHOD"]);
