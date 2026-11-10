<?php

/**
 * A patient app's FHIR writes, through the review queue: create and update are held for staff
 * review, and the app reads back what it wrote while it waits.
 *
 * FhirGenericRestController hands a patient-context write here when the route has one, and the
 * read routes ask here first. Whether the token carries the scope is the permission gates'
 * check, before this runs; whether this kind of app may write this type is the hold's, on every
 * request, so turning the practice's mode down stops tokens already issued.
 *
 * Who is writing comes from the token, never from the resource: the patient, the client and
 * whether the client is confidential.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteContext;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteHold;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritePolicy;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReadBack;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReadOutcome;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteRefusal;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteRefusalReason;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PatientReview\ReviewStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

final readonly class FhirPatientAppWriteController
{
    /**
     * Search parameters the held writes can be matched on, plus _count, which limits how many
     * are added (see the search's bundle). A search with any other parameter finds only chart
     * records, so a held write is never returned for a search it may not match.
     */
    private const HELD_SEARCH_PARAMETERS = ['_id', 'patient', 'subject', '_count'];

    /**
     * @param \Closure(string): ?PatientWriteClientType $clientType the registered client's type, or null for an unknown client
     * @param \Closure(int): ?string $portalUsername the patient's portal login, as the audit log records a patient; null when the patient has none
     */
    public function __construct(
        private PatientWritableType $type,
        private PatientWriteHold $hold,
        private PatientWriteReadBack $readBack,
        private \Closure $clientType,
        private string $fhirUrl,
        private \Closure $portalUsername,
    ) {
    }

    /**
     * Wired for a route, with the practice's current settings.
     */
    public static function forType(PatientWritableType $type, ParameterBag $globals): self
    {
        $queue = ReviewQueueFactory::create();
        $clients = new ClientRepository();
        return new self(
            $type,
            new PatientWriteHold($queue, PatientWritePolicy::fromGlobals($globals), ReviewQueueFactory::fhirWriteHandler()),
            new PatientWriteReadBack($queue),
            static function (string $clientId) use ($clients): ?PatientWriteClientType {
                $client = $clients->getClientEntity($clientId);
                if ($client === false) {
                    return null;
                }
                return in_array($client->isConfidential(), [true, 1, '1'], true)
                    ? PatientWriteClientType::Confidential
                    : PatientWriteClientType::Public;
            },
            (new ServerConfig())->getFhirUrl(),
            static function (int $pid): ?string {
                $username = QueryUtils::fetchSingleValue(
                    'SELECT `portal_username` FROM `patient_access_onsite` WHERE `pid` = ?',
                    'portal_username',
                    [$pid]
                );
                return is_string($username) && $username !== '' ? $username : null;
            },
        );
    }

    /**
     * FHIR create: 201 with Location and the resource as held.
     *
     * @param array<array-key, mixed> $resource decoded FHIR JSON, already validated
     */
    public function create(HttpRestRequest $request, array $resource): Response
    {
        try {
            $held = $this->hold->hold($this->context($request), $this->type, $resource);
        } catch (PatientWriteRefusal $refusal) {
            return self::refused($refusal->reason);
        }
        $response = new JsonResponse(PatientWriteReadBack::resource($this->type, $held), Response::HTTP_CREATED);
        $response->headers->set('Location', $this->location($held->uuid));
        return $response;
    }

    /**
     * FHIR update of the app's own write that is still held: 200 with the resource as held now.
     * An update to entered-in-error withdraws it, and is answered with the resource as sent.
     *
     * @param array<array-key, mixed> $resource decoded FHIR JSON, already validated
     */
    public function update(HttpRestRequest $request, string $id, array $resource): Response
    {
        try {
            $changed = $this->hold->replace($this->context($request), $this->type, $id, $resource);
        } catch (PatientWriteRefusal $refusal) {
            return self::refused($refusal->reason);
        }
        if ($changed->status === ReviewStatus::Cancelled) {
            return new JsonResponse($resource, Response::HTTP_OK);
        }
        return new JsonResponse(PatientWriteReadBack::resource($this->type, $changed), Response::HTTP_OK);
    }

    /**
     * FHIR read, asked before the chart: the app's own held write, 410 for its denied or
     * withdrawn one, or null to read the chart as usual.
     */
    public function read(HttpRestRequest $request, string $id): ?Response
    {
        $context = $this->readerContext($request);
        if ($context === null) {
            return null;
        }
        $read = $this->readBack->read($context, $this->type, $id);
        return match ($read->outcome) {
            PatientWriteReadOutcome::Held => new JsonResponse($read->resource, Response::HTTP_OK),
            PatientWriteReadOutcome::Gone => self::refused(PatientWriteRefusalReason::Gone),
            PatientWriteReadOutcome::NotHeld => null,
        };
    }

    /**
     * The app's held writes a FHIR search matches, to add to the chart's results. Empty for a
     * search on any parameter the held writes can't be matched on.
     *
     * @param array<array-key, mixed> $searchParams
     * @return list<array<array-key, mixed>>
     */
    public function searchHeld(HttpRestRequest $request, array $searchParams): array
    {
        $context = $this->readerContext($request);
        if ($context === null) {
            return [];
        }
        foreach ($searchParams as $name => $value) {
            if (!in_array($name, self::HELD_SEARCH_PARAMETERS, true) || !is_string($value)) {
                return [];
            }
        }
        foreach (['patient', 'subject'] as $name) {
            $value = $searchParams[$name] ?? null;
            // A uuid is the same id in either case; the context's is lower-case.
            if (is_string($value) && strtolower((string) preg_replace('#^Patient/#', '', $value)) !== $context->patientUuid) {
                return [];
            }
        }
        $ids = $searchParams['_id'] ?? null;
        if (!is_string($ids)) {
            return $this->readBack->search($context, $this->type);
        }
        // Read one by one, so that only the writes shown are recorded as read.
        $found = [];
        foreach (array_unique(explode(',', $ids)) as $id) {
            $read = $this->readBack->read($context, $this->type, trim($id));
            if ($read->outcome === PatientWriteReadOutcome::Held && $read->resource !== null) {
                $found[] = $read->resource;
            }
        }
        return $found;
    }

    /**
     * @throws PatientWriteRefusal 403 when the token doesn't name a patient and a registered client
     */
    private function context(HttpRestRequest $request): PatientWriteContext
    {
        return $this->readerContext($request) ?? throw new PatientWriteRefusal(PatientWriteRefusalReason::NotAllowed);
    }

    /**
     * Who the token is for, or null when it doesn't name a patient and a registered client.
     */
    private function readerContext(HttpRestRequest $request): ?PatientWriteContext
    {
        // Only the bearer-token check of a patient's token sets the patient and the request user.
        if (!$request->isPatientRequest()) {
            return null;
        }
        $patientUuid = $request->getPatientUUIDString();
        $clientId = $request->getClientId();
        $user = $request->getRequestUser();
        $pid = $user['pid'] ?? null;
        $pid = is_int($pid) ? $pid : (is_string($pid) && ctype_digit($pid) ? (int) $pid : 0);
        if ($patientUuid === null || $clientId === null || $clientId === '' || $pid <= 0) {
            return null;
        }
        $clientType = ($this->clientType)($clientId);
        if ($clientType === null) {
            return null;
        }
        $patientUuid = strtolower($patientUuid);
        try {
            return new PatientWriteContext(
                $patientUuid,
                $clientId,
                $clientType,
                // The audit log names a patient by portal login, as the portal does; a patient
                // without one by the uuid the token was issued for.
                ReviewActor::patient($pid, ($this->portalUsername)($pid) ?? $patientUuid),
            );
        } catch (\DomainException) {
            return null;
        }
    }

    private function location(string $id): string
    {
        return $this->fhirUrl . '/' . $this->type->fhirResourceType()->value . '/' . $id;
    }

    /**
     * The refusal as an OperationOutcome, with its status. The text names the practice
     * setting or the limit, never anything about the server.
     */
    private static function refused(PatientWriteRefusalReason $reason): Response
    {
        return RestControllerHelper::responseHandler(
            UtilsService::createOperationOutcomeResource('error', $reason->issueCode(), $reason->diagnostics()),
            null,
            $reason->httpStatus(),
        );
    }
}
