<?php

/**
 * Functional cognitive status form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
/**
 * @global string $srcdir defined in globals.php
 */
global $srcdir;
require_once("$srcdir/api.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use Symfony\Component\HttpFoundation\Request;
use OpenEMR\Services\ObservationService;
use OpenEMR\Controllers\Interface\Forms\Observation\ObservationController;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Services\FormService;

$logger = new SystemLogger();

// Output the response
try {
    // Create controller and handle request
    $request = Request::createFromGlobals();
    $service = new ObservationService();
    $formService = new FormService();
    // resolves to openemer/interface/  so that templates will be found in /forms/observation/templates
    $twigContainer = new TwigContainer(__DIR__ . '/../../', $GLOBALS['kernel']);
    $controller = new ObservationController($service, $formService, $twigContainer->getTwig());
    // edit screen will start with list view... if
    if ($controller->shouldShowListView($request)) {
        $response = $controller->listAction($request);
    } else {
        $response = $controller->newAction($request);
    }
    $response->send();
} catch (Exception $e) {
    // Handle any exceptions that may occur
    $logger->errorLogCaller("Failed to create new observation form", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    echo xlt("An error occurred while trying to create a new observation form. Please try again later.");
}
