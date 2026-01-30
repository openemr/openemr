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
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");

use OpenEMR\Controllers\Interface\Forms\Observation\ObservationController;
use OpenEMR\Services\ObservationService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\FormService;
use OpenEMR\Common\Twig\TwigContainer;

function observation_report($pid, $encounter, $cols, $id): void
{
    $logger = new SystemLogger();

// Output the response
    try {
        // Create controller and handle request
        $service = new ObservationService();
        $formService = new FormService();
        // resolves to openemer/interface/  so that templates will be found in /forms/observation/templates
        $twigContainer = new TwigContainer(__DIR__ . '/../../', $GLOBALS['kernel']);
        $controller = new ObservationController($service, $formService, $twigContainer->getTwig());
        // This approach is consistent with the current design, even though the original report used session values.
        $response = $controller->reportAction($pid, $encounter, $cols, $id);
        $response->send();
    } catch (Exception $e) {
        // Handle any exceptions that may occur
        $logger->errorLogCaller("Failed to render observation form report.php", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        echo xlt("An error occurred while trying to render this form. Please try again later.");
    }
}
