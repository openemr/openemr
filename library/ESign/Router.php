<?php

/**
 * Allows routing to the correct controller and method using the
 * request string
 *
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Medical Information Integration, LLC
 * @link    http://www.open-emr.org
 **/

namespace ESign;

require_once $GLOBALS['srcdir'] . '/ESign/Abstract/Controller.php';

class Router
{
    public function route()
    {
        $request = new Request();
        $moduleParam = $request->getParam('module');
        $Module = ucfirst($moduleParam);
        require_once $GLOBALS['srcdir'] . '/ESign/' . $Module . '/Controller.php';
        $controllerClass = "\\ESign\\" . $Module . "_Controller";
        $controller = new $controllerClass($request);
        if ($controller instanceof Abstract_Controller) {
            $controller->run();
        }
    }
}
