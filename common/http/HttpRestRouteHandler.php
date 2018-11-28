<?php
/**
 * HttpResponseHelper
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
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Common\Http;

class HttpRestRouteHandler
{
    public static function dispatch(&$routes, $route, $request_method)
    {
        // Taken from https://stackoverflow.com/questions/11722711/url-routing-regex-php/11723153#11723153
        foreach($routes as $routePath => $routeCallback) {
            $routePieces = explode(" ", $routePath);
            $method = $routePieces[0];
            $path = $routePieces[1];
            $pattern = "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($path)) . "$@D";
            $matches = Array();
            if($method == $request_method && preg_match($pattern, $route, $matches)) {
                header('Content-Type: application/json');
                array_shift($matches);
                $result = call_user_func_array($routeCallback, $matches);
                echo json_encode($result);
            }
        }
    }
}
