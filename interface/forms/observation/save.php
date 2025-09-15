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
require_once("$srcdir/forms.inc.php");

use Symfony\Component\HttpFoundation\Request;
use OpenEMR\Controllers\Interface\Forms\Observation\ObservationController;
use OpenEMR\Services\ObservationService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\FormService;
use OpenEMR\Common\Twig\TwigContainer;

$logger = new SystemLogger();

try {
    // Create controller and handle request
    $request = Request::createFromGlobals();
    $service = new ObservationService();
    $formService = new FormService();
    // resolves to openemer/interface/  so that templates will be found in /forms/observation/templates
    $twigContainer = new TwigContainer(__DIR__ . '/../../', $GLOBALS['kernel']);
    $controller = new ObservationController($service, $formService, $twigContainer->getTwig());
    $response = $controller->saveAction($request);
    $response->send();
} catch (Exception $e) {
    // Handle any exceptions that may occur
    $logger->errorLogCaller("Failed to create new observation form", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    echo xlt("An error occurred while trying to create a new observation form. Please try again later.");
}
