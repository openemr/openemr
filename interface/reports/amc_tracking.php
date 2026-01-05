<?php

/**
 * AMC Tracking - Front Controller
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenEMR <dev@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/amc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Reports\AmcTracking\AmcTrackingController;

// Get OEGlobalsBag instance
$globalsBag = OEGlobalsBag::getInstance(true);

// Security Check: ACL
if (!AclMain::aclCheckCore('patients', 'med')) {
    $kernel = $globalsBag->get('kernel');
    echo (new TwigContainer(null, $kernel))->getTwig()->render(
        'core/unauthorized.html.twig',
        ['pageTitle' => xl("Automated Measure Calculations (AMC) Tracking")]
    );
    exit;
}

// Security Check: CSRF
if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
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
$templateData = $controller->prepareTemplateData($params, $showResults);

// Render template
$kernel = $globalsBag->get('kernel');
$twigContainer = new TwigContainer(null, $kernel);
$twig = $twigContainer->getTwig();

echo $twig->render('reports/amc/tracking.html.twig', $templateData);
