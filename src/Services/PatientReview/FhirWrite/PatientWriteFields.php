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

    /** A relative RelatedPerson reference, the only other source a held response keeps. */
    private const RELATED_PERSON_REFERENCE = '#^RelatedPerson/[A-Za-z0-9\-.]{1,64}$#';

    /**
     * @param array<array-key, mixed> $resource decoded FHIR JSON
     * @return array<array-key, mixed> the resource as it is held
     * @throws PatientWriteRefusal
     */
    public function force(PatientWritableType $type, array $resource, string $patientUuid): array
    {
        self::refuseUnlessOfType($type, $resource);
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
     * Whether an update marks the resource entered-in-error: the app withdrawing its write.
     * QuestionnaireResponse and Observation say so in status; AllergyIntolerance and Condition
     * in verificationStatus.
     *
     * The resourceType is checked first, as force() checks it: a withdrawal changes the held
     * write as much as a replacement does.
     *
     * @param array<array-key, mixed> $resource decoded FHIR JSON
     * @throws PatientWriteRefusal when the resource is not of the type
     */
    public function withdraws(PatientWritableType $type, array $resource): bool
    {
        self::refuseUnlessOfType($type, $resource);

        return match ($type) {
            PatientWritableType::QuestionnaireResponse,
            PatientWritableType::VitalSignObservation => ($resource['status'] ?? null) === 'entered-in-error',
            PatientWritableType::AllergyIntolerance => $this->verifiedAs($resource, self::ALLERGY_VERIFICATION_SYSTEM, 'entered-in-error'),
            PatientWritableType::Condition => $this->verifiedAs($resource, self::CONDITION_VERIFICATION_SYSTEM, 'entered-in-error'),
        };
    }

    /**
     * @param array<array-key, mixed> $resource
     * @throws PatientWriteRefusal when the resourceType is not the type's
     */
    private static function refuseUnlessOfType(PatientWritableType $type, array $resource): void
    {
        if (($resource['resourceType'] ?? null) !== $type->fhirResourceType()->value) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::WrongType);
        }
    }

    /**
     * Whether any verificationStatus coding is this code in this system.
     *
     * @param array<array-key, mixed> $resource
     */
    private function verifiedAs(array $resource, string $system, string $code): bool
    {
        $status = $resource['verificationStatus'] ?? null;
        $codings = is_array($status) ? ($status['coding'] ?? null) : null;
        if (!is_array($codings)) {
            return false;
        }
        foreach ($codings as $coding) {
            if (is_array($coding) && ($coding['system'] ?? null) === $system && ($coding['code'] ?? null) === $code) {
                return true;
            }
        }

        return false;
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
     * The patient answered, unless the source is a RelatedPerson (a caregiver), which is kept.
     * A source naming another patient is refused; any other source (a Practitioner, an
     * Organization, a display with no reference) becomes the patient, since a patient app cannot
     * vouch for anyone else. author is dropped for the same reason.
     *
     * @param array<array-key, mixed> $resource
     * @return array<array-key, mixed>
     */
    private function questionnaireResponse(array $resource, string $patientUuid): array
    {
        unset($resource['author']);
        $source = $resource['source'] ?? null;
        $reference = is_array($source) ? ($source['reference'] ?? null) : null;
        if (is_string($reference) && preg_match(self::RELATED_PERSON_REFERENCE, $reference) === 1) {
            $resource['source'] = ['reference' => $reference];
            return $resource;
        }
        $isPatient = is_string($reference) && preg_match(self::PATIENT_REFERENCE, $reference) === 1;
        $resource['source'] = $this->patientReference($isPatient ? $source : null, $patientUuid);

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

    /**
     * Whether any category coding is the observation-category vital-signs code.
     */
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
