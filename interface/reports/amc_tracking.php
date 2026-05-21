<?php

/**
 * AMC Tracking - Front Controller
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenEMR <dev@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc.php");
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . "/options.inc.php";
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . "/amc.php";

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Reports\AmcTracking\AmcTrackingController;

// Get OEGlobalsBag instance
$globalsBag = OEGlobalsBag::getInstance();

// Security Check: ACL
if (!AclMain::aclCheckCore('patients', 'med')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for patients/med: AMC Tracking",
        xl("Automated Measure Calculations (AMC) Tracking")
    );
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();

// Security Check: CSRF
if (!empty($_POST)) {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
}

// Initialize controller with OEGlobalsBag
$controller = new AmcTrackingController($globalsBag);

// Get form parameters
$params = !empty($_POST) ? $controller->getFormParameters() : [
    'begin_date' => '',
    'end_date' => '',
    'rule' => '',
    'provider' => ''
];

// Determine if we should show results
$showResults = !empty($_POST['form_refresh']) && !empty($params['rule']);

// Prepare data for template
$templateData = $controller->prepareTemplateData($params, $showResults, $session);

// Render template
$kernel = $globalsBag->get('kernel');
$twigContainer = new TwigContainer(null, $kernel);
$twig = $twigContainer->getTwig();

echo $twig->render('reports/amc/tracking.html.twig', $templateData);
