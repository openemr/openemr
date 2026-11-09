<?php

/**
 * Patient App Submissions: FHIR writes from patient apps held for staff review.
 *
 * Entry point only: the URL the Miscellaneous menu loads. The page itself is
 * PatientAppSubmissionsController and templates/patient_review/patient_app_submissions.html.twig.
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
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Controllers\Interface\PatientReview\PatientAppSubmissionsController;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReviewQuery;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PatientReview\ReviewRequestMapper;

$handler = ReviewQueueFactory::fhirWriteHandler();
$controller = new PatientAppSubmissionsController(
    ReviewQueueFactory::create([$handler]),
    new PatientWriteReviewQuery(new ReviewRequestMapper(), $handler),
    ServiceContainer::getTwig(),
    SessionWrapperFactory::getInstance()->getActiveSession(),
    ServiceContainer::getLogger(),
);

$controller->dispatchAction(CurrentRequest::get())->send();
