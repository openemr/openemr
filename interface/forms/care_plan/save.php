<?php

/**
 * Care plan form save.php - thin entry point delegating to CarePlanController.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (C) 2025 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\EncounterSessionUtil;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Controllers\Interface\Forms\CarePlan\CarePlanController;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Forms\CarePlanFormService;
use OpenEMR\Services\FormService;

$globalsBag = OEGlobalsBag::getInstance();
$srcdir = $globalsBag->getSrcDir();

if (!EncounterSessionUtil::getEncounter()) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}

$formService = new FormService();
// resolves to openemr/interface/ so that templates are found in /forms/care_plan/templates
$twigContainer = new TwigContainer(__DIR__ . '/../../', $globalsBag->getKernel());

$controller = new CarePlanController(
    new CarePlanFormService($formService),
    $formService,
    $twigContainer->getTwig(),
    SessionWrapperFactory::getInstance()->getActiveSession(),
    ServiceContainer::getLogger(),
    $globalsBag->getString('rootdir'),
    $globalsBag->getWebRoot(),
    $globalsBag->getString('v_js_includes'),
);

$controller->saveAction(CurrentRequest::get(), PatientSessionUtil::getUserAuthorized())->send();

formHeader("Redirecting....");
formJump();
formFooter();
