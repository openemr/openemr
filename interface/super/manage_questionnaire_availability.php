<?php

/**
 * Questionnaire availability management.
 *
 * Entry point only: the URL the Availability tab under Questionnaires loads. The page itself
 * is QuestionnaireAvailabilityController and templates/super/questionnaire_availability.html.twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once('../globals.php');

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailabilityController;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityLookups;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityService;

$controller = new QuestionnaireAvailabilityController(
    new QuestionnaireAvailabilityService(),
    new QuestionnaireAvailabilityLookups(new ClientRepository()),
    ServiceContainer::getTwig(),
    SessionWrapperFactory::getInstance()->getActiveSession(),
    ServiceContainer::getLogger(),
);

$controller->dispatchAction(CurrentRequest::get())->send();
