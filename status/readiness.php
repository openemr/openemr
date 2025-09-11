<?php

/**
 * Readiness Probe for OpenEMR
 *
 * This endpoint checks if the application is ready to serve traffic by
 * verifying database connectivity.
 *
 * @package   OpenEMR
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEmr Inc.
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Controllers\ApplicationHealthProbeController;
use Symfony\Component\HttpFoundation\Request;

// Load autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Create request from globals
$request = Request::createFromGlobals();

// Create controller and handle readiness probe
$controller = new ApplicationHealthProbeController();
$response = $controller->readiness($request);

// Send the response
$response->send();
