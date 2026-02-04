<?php

/**
 * Allows routing to the correct controller and method using the
 * request string
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace ESign;

require_once $GLOBALS['srcdir'] . '/ESign/Abstract/Controller.php';

class Router
{
    public function route()
    {
        $request = new Request();
        $moduleParam = $request->getParam('module');
        $Module = ucfirst((string) $moduleParam);
        require_once $GLOBALS['srcdir'] . '/ESign/' . $Module . '/Controller.php';
        $controllerClass = "\\ESign\\" . $Module . "_Controller";
        $controller = new $controllerClass($request);
        if ($controller instanceof Abstract_Controller) {
            $controller->run();
        }
    }
}
