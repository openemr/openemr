<?php

/**
 * HttpResponseHelper
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

class HttpRestRouteHandler
{
    public static function dispatch(&$routes, $route, $request_method, $return_method = 'standard')
    {
        // Taken from https://stackoverflow.com/questions/11722711/url-routing-regex-php/11723153#11723153
        $hasRoute = false;
        foreach ($routes as $routePath => $routeCallback) {
            $routePieces = explode(" ", $routePath);
            $method = $routePieces[0];
            $path = $routePieces[1];
            $pattern = "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_\$]+)', preg_quote($path)) . "$@D";
            $matches = array();
            if ($method == $request_method && preg_match($pattern, $route, $matches)) {
                array_shift($matches);
                $hasRoute = true;
                $result = call_user_func_array($routeCallback, $matches);
                if ($return_method == 'standard') {
                    header('Content-Type: application/json');
                    echo json_encode($result);
                } elseif ($return_method == 'direct-json') {
                    return json_encode($result);
                } else { // $return_method == 'direct'
                    return $result;
                }
            }
        }
        return $hasRoute;
    }
}
