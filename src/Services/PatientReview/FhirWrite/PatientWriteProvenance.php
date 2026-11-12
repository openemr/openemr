<?php

/**
 * The Provenance of a patient app write, from its review queue row: who wrote it, which app
 * sent it, and, once approved, who verified it.
 *
 *  - author: the patient, or the RelatedPerson a QuestionnaireResponse names as its source
 *  - transmitter: the app, by its client_id (and name, when it is still registered)
 *  - verifier: the staff user who approved it, once it is in the chart
 *
 * recorded is when the app sent it. The id is derived from the request uuid, so it is stable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Services\FHIR\QuestionnaireResponse\FhirQuestionnaireResponseFormService;
use OpenEMR\Services\PatientReview\ReviewRequest;

/**
 * @phpstan-type ProvenanceAgent array{
 *     type: array{coding: list<array{system: string, code: string, display: string}>},
 *     who: array<string, mixed>
 * }
 */
final class PatientWriteProvenance
{
    public const PARTICIPANT_TYPE_SYSTEM = 'http://terminology.hl7.org/CodeSystem/provenance-participant-type';
    public const US_CORE_PARTICIPANT_TYPE_SYSTEM = 'http://hl7.org/fhir/us/core/CodeSystem/us-core-provenance-participant-type';

    /** Prefix of the Provenance id; the rest is the request uuid. */
    public const ID_PREFIX = 'patient-write-';

    /**
     * @param string $patientUuid the patient's FHIR id
     * @param ?string $appName the app's registered name; null when it is no longer registered
     * @param ?string $approverUuid the FHIR Practitioner id of the staff user who approved it;
     *                              null while it is held
     * @return array{resourceType: 'Provenance', id: string, target: list<array{reference: string}>, recorded: string, agent: list<ProvenanceAgent>}
     * @throws \DomainException when the request is not a patient app write
     */
    public static function of(ReviewRequest $request, string $patientUuid, ?string $appName, ?string $approverUuid): array
    {
        $resourceType = $request->resourceType;
        if ($resourceType === null || $request->clientId === null) {
            throw new \DomainException('Only a patient app write has a patient app Provenance');
        }

        $transmitter = ['identifier' => ['value' => $request->clientId]];
        if ($appName !== null && $appName !== '') {
            $transmitter['display'] = $appName;
        }
        $agents = [
            self::agent(self::PARTICIPANT_TYPE_SYSTEM, 'author', 'Author', ['reference' => self::author($request, $patientUuid)]),
            self::agent(self::US_CORE_PARTICIPANT_TYPE_SYSTEM, 'transmitter', 'Transmitter', $transmitter),
        ];
        if ($approverUuid !== null) {
            $agents[] = self::agent(self::PARTICIPANT_TYPE_SYSTEM, 'verifier', 'Verifier', ['reference' => 'Practitioner/' . $approverUuid]);
        }

        return [
            'resourceType' => 'Provenance',
            'id' => self::ID_PREFIX . $request->uuid,
            'target' => [['reference' => $resourceType->value . '/' . $request->uuid]],
            'recorded' => $request->createdAt->format(DATE_ATOM),
            'agent' => $agents,
        ];
    }

    /**
     * A QuestionnaireResponse answered by a caregiver keeps its RelatedPerson source; anything
     * else was written by the patient.
     */
    private static function author(ReviewRequest $request, string $patientUuid): string
    {
        $source = $request->payload['source'] ?? null;
        $reference = is_array($source) ? ($source['reference'] ?? null) : null;
        if (
            $request->resourceType?->value === 'QuestionnaireResponse'
            && is_string($reference)
            && preg_match(FhirQuestionnaireResponseFormService::CAREGIVER_SOURCE_REFERENCE, $reference) === 1
        ) {
            return $reference;
        }

        return 'Patient/' . $patientUuid;
    }

    /**
     * @param array<string, mixed> $who
     * @return ProvenanceAgent
     */
    private static function agent(string $system, string $code, string $display, array $who): array
    {
        return [
            'type' => ['coding' => [['system' => $system, 'code' => $code, 'display' => $display]]],
            'who' => $who,
        ];
    }
}
