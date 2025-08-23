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
require_once("$srcdir/api.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\ReasonStatusCodes;
use OpenEMR\Core\Header;
use Symfony\Component\HttpFoundation\Request;
use OpenEMR\Services\ObservationService;
use OpenEMR\Controllers\Interface\Forms\Observation\ObservationController;
use OpenEMR\Common\Logging\SystemLogger;

$logger = new SystemLogger();

// Output the response
try {
    // Create controller and handle request
    $request = Request::createFromGlobals();
    $service = new ObservationService();
    $controller = new ObservationController($service);
    $response = $controller->new($request);
    $response->send();
    exit;
} catch (Exception $e) {
    // Handle any exceptions that may occur
    $logger->errorLogCaller("Failed to create new observation form", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    echo xlt("An error occurred while trying to create a new observation form. Please try again later.");
    exit;
}
