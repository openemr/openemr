<?php

/**
 * Builds the review queue with the application's shared services.
 *
 * The composition root for ReviewQueueService: callers that cannot take the service through a
 * constructor (legacy portal scripts) get it here, and nothing else in the namespace reaches
 * for a global.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FHIR\FhirRelatedPersonService;
use OpenEMR\Services\FHIR\QuestionnaireResponse\FhirQuestionnaireResponseFormService;
use OpenEMR\Services\FormService;
use OpenEMR\Services\PatientReview\FhirWrite\FhirWriteReviewHandler;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\FhirWrite\QuestionnaireResponseEncounterForm;
use OpenEMR\Services\PatientReview\FhirWrite\QuestionnaireResponseWriteApplier;
use OpenEMR\Services\PatientReview\Migration\LegacyActivityMapping;
use OpenEMR\Services\PatientReview\Migration\PortalActivityMigrator;
use OpenEMR\Services\PatientReview\Portal\PortalReviewWorkflows;
use OpenEMR\Services\Questionnaire\QuestionnaireEncounterFiling;
use OpenEMR\Services\QuestionnaireResponseService;

final class ReviewQueueFactory
{
    /**
     * @param iterable<ReviewHandlerInterface> $handlers
     */
    public static function create(iterable $handlers = []): ReviewQueueService
    {
        $group = OEGlobalsBag::getInstance()->getString('groupname');

        return new ReviewQueueService(
            new ReviewRequestRepository(new ReviewRequestMapper()),
            new EventAuditReviewTrail(EventAuditLogger::getInstance(), $group !== '' ? $group : 'none', self::clientType()),
            ServiceContainer::getClock(),
            UuidRegistry::timestampFirstCombFactory(),
            ServiceContainer::getCrypto(),
            ServiceContainer::getLogger(),
            $handlers,
        );
    }

    /**
     * Looks up whether a registered API client is confidential or public; null for a client
     * that isn't registered.
     *
     * @return \Closure(string): ?PatientWriteClientType
     */
    public static function clientType(): \Closure
    {
        $clients = new ClientRepository();

        return static function (string $clientId) use ($clients): ?PatientWriteClientType {
            $client = $clients->getClientEntity($clientId);
            if ($client === false) {
                return null;
            }
            return in_array($client->isConfidential(), [true, 1, '1'], true)
                ? PatientWriteClientType::Confidential
                : PatientWriteClientType::Public;
        };
    }

    /**
     * The queue as staff review patient app writes: approving a fhir-write request writes it to
     * the chart.
     *
     * @param ?QuestionnaireEncounterFiling $encounters the encounters approval can file in; by
     *        default those the signed-in user can open
     */
    public static function withFhirWriteHandler(?QuestionnaireEncounterFiling $encounters = null): ReviewQueueService
    {
        return self::create([self::fhirWriteHandler($encounters)]);
    }

    /**
     * The fhir-write handler with an applier for each resource type that can be approved.
     *
     * @param ?QuestionnaireEncounterFiling $encounters the encounters approval can file in; by
     *        default those the signed-in user can open
     */
    public static function fhirWriteHandler(?QuestionnaireEncounterFiling $encounters = null): FhirWriteReviewHandler
    {
        return new FhirWriteReviewHandler([
            new QuestionnaireResponseWriteApplier(
                new FhirQuestionnaireResponseFormService(),
                new FhirRelatedPersonService(),
                new QuestionnaireResponseEncounterForm(new QuestionnaireResponseService(), new FormService()),
                $encounters ?? new QuestionnaireEncounterFiling(),
            ),
        ]);
    }

    /**
     * The onsite_portal_activity migration, as the SQL upgrade runs it.
     */
    public static function portalActivityMigrator(): PortalActivityMigrator
    {
        return new PortalActivityMigrator(
            new LegacyActivityMapping(),
            ServiceContainer::getClock(),
            UuidRegistry::timestampFirstCombFactory(),
            ServiceContainer::getLogger(),
        );
    }

    /**
     * The portal's review workflows over a queue built as create() builds it.
     */
    public static function portalWorkflows(): PortalReviewWorkflows
    {
        return new PortalReviewWorkflows(self::create(), ServiceContainer::getLogger());
    }
}
