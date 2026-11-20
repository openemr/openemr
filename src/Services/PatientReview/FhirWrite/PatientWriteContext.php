<?php

/**
 * Who is writing: the patient a token is for, and the app holding it.
 *
 * Built from the access token, never from the resource body, so the resource cannot choose
 * its own patient or client.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewActorType;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewType;

final readonly class PatientWriteContext
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';

    /**
     * @param string $patientUuid the token's patient, as its FHIR Patient id
     * @param string $clientId oauth_clients.client_id of the app holding the token
     * @param ReviewActor $actor the patient, as the queue records who submitted
     */
    public function __construct(
        public string $patientUuid,
        public string $clientId,
        public PatientWriteClientType $clientType,
        public ReviewActor $actor,
    ) {
        if (preg_match(self::UUID_PATTERN, $patientUuid) !== 1) {
            throw new \DomainException('The patient id must be a lower-case uuid');
        }
        if ($clientId === '') {
            throw new \DomainException('A patient app write must name its client');
        }
        if ($actor->type !== ReviewActorType::Patient || $actor->id === null) {
            throw new \DomainException('A patient app write is submitted by the patient');
        }
    }

    /**
     * The patient's pid, from the actor.
     */
    public function pid(): int
    {
        return $this->actor->id ?? throw new \LogicException('A patient actor always has a pid');
    }

    /**
     * Whether this app wrote the request, for this patient, as this type. Only then may the app
     * read it back or change it; to anyone else it does not exist.
     */
    public function wrote(ReviewRequest $request, PatientWritableType $type): bool
    {
        return $request->type === ReviewType::FhirWrite
            && $request->resourceType !== null
            && $request->resourceType->equals($type->fhirResourceType())
            && $request->pid === $this->pid()
            && $request->clientId === $this->clientId;
    }
}
