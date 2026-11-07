<?php

/**
 * Sets the fields of a patient app write that the server decides, not the app.
 *
 *  - id and meta are dropped. The held request's uuid is the resource id; approval writes the
 *    record under it.
 *  - text is dropped. A narrative is markup the app wrote; the server builds its own where it
 *    shows one.
 *  - The patient reference is the token's patient. A resource naming another patient is
 *    refused, not corrected, because the app meant someone else.
 *  - A vital sign stays preliminary, and an allergy or condition stays unconfirmed, until a
 *    clinician says otherwise. An app cannot claim either.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

final class PatientWriteFields
{
    public const OBSERVATION_CATEGORY_SYSTEM = 'http://terminology.hl7.org/CodeSystem/observation-category';
    public const ALLERGY_VERIFICATION_SYSTEM = 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification';
    public const CONDITION_VERIFICATION_SYSTEM = 'http://terminology.hl7.org/CodeSystem/condition-ver-status';

    /** A Patient reference: relative, absolute, or versioned. Group 1 is the id. */
    private const PATIENT_REFERENCE = '#(?:^|/)Patient/([A-Za-z0-9\-.]{1,64})(?:/_history/[A-Za-z0-9\-.]{1,64})?$#';

    /**
     * @param array<array-key, mixed> $resource decoded FHIR JSON
     * @return array<array-key, mixed> the resource as it is held
     * @throws PatientWriteRefusal
     */
    public function force(PatientWritableType $type, array $resource, string $patientUuid): array
    {
        if (($resource['resourceType'] ?? null) !== $type->fhirResourceType()->value) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::WrongType);
        }
        unset($resource['id'], $resource['meta'], $resource['text']);

        $patientField = match ($type) {
            PatientWritableType::AllergyIntolerance => 'patient',
            PatientWritableType::QuestionnaireResponse,
            PatientWritableType::VitalSignObservation,
            PatientWritableType::Condition => 'subject',
        };
        $resource[$patientField] = $this->patientReference($resource[$patientField] ?? null, $patientUuid);

        return match ($type) {
            PatientWritableType::QuestionnaireResponse => $this->questionnaireResponse($resource, $patientUuid),
            PatientWritableType::VitalSignObservation => $this->vitalSign($resource),
            PatientWritableType::AllergyIntolerance => $this->withVerification($resource, self::ALLERGY_VERIFICATION_SYSTEM),
            PatientWritableType::Condition => $this->withVerification($resource, self::CONDITION_VERIFICATION_SYSTEM),
        };
    }

    /**
     * @return array{reference: string}
     * @throws PatientWriteRefusal when the reference names anyone but the token's patient
     */
    private function patientReference(mixed $given, string $patientUuid): array
    {
        if ($given !== null) {
            $reference = is_array($given) ? ($given['reference'] ?? null) : null;
            if (
                !is_string($reference)
                || preg_match(self::PATIENT_REFERENCE, $reference, $match) !== 1
                || $match[1] !== $patientUuid
            ) {
                throw new PatientWriteRefusal(PatientWriteRefusalReason::OtherPatient);
            }
        }

        return ['reference' => 'Patient/' . $patientUuid];
    }

    /**
     * The patient answered unless the app says who did. A source naming another patient is
     * refused; a RelatedPerson (a caregiver) is kept.
     *
     * @param array<array-key, mixed> $resource
     * @return array<array-key, mixed>
     */
    private function questionnaireResponse(array $resource, string $patientUuid): array
    {
        $source = $resource['source'] ?? null;
        $reference = is_array($source) ? ($source['reference'] ?? null) : null;
        if ($source === null || (is_string($reference) && preg_match(self::PATIENT_REFERENCE, $reference) === 1)) {
            $resource['source'] = $this->patientReference($source, $patientUuid);
        }

        return $resource;
    }

    /**
     * @param array<array-key, mixed> $resource
     * @return array<array-key, mixed>
     * @throws PatientWriteRefusal when the Observation is not in the vital-signs category
     */
    private function vitalSign(array $resource): array
    {
        if (!$this->hasVitalSignsCategory($resource['category'] ?? null)) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::WrongType);
        }
        $resource['status'] = 'preliminary';

        return $resource;
    }

    private function hasVitalSignsCategory(mixed $categories): bool
    {
        if (!is_array($categories)) {
            return false;
        }
        foreach ($categories as $category) {
            $codings = is_array($category) ? ($category['coding'] ?? null) : null;
            if (!is_array($codings)) {
                continue;
            }
            foreach ($codings as $coding) {
                if (
                    is_array($coding)
                    && ($coding['system'] ?? null) === self::OBSERVATION_CATEGORY_SYSTEM
                    && ($coding['code'] ?? null) === 'vital-signs'
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array<array-key, mixed> $resource
     * @return array<array-key, mixed>
     */
    private function withVerification(array $resource, string $system): array
    {
        $resource['verificationStatus'] = [
            'coding' => [['system' => $system, 'code' => 'unconfirmed', 'display' => 'Unconfirmed']],
        ];

        return $resource;
    }
}
