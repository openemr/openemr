<?php

/**
 * Instantiate a router and route the interface request to the appropriate
 * controller and method in the ESign/ library directory.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use ESign\Router;

require_once "../globals.php";
require_once $GLOBALS['srcdir'] . "/ESign/Router.php";
$router = new Router();
$router->route();
