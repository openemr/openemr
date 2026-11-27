<?php

/**
 * Writes an approved patient app QuestionnaireResponse to the chart, under the id the app was
 * given when it was held.
 *
 * It goes through the same parse and save as a staff FHIR create, except that the response is
 * created under the issued id and with the patient, not the approver, as the one who answered.
 *
 * A response that names one of the patient's encounters, or that staff file under one when
 * approving, is also added to that encounter as a Questionnaire Assessment form.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaireResponse;
use OpenEMR\Services\FHIR\FhirPayloadReader;
use OpenEMR\Services\FHIR\FhirRelatedPersonService;
use OpenEMR\Services\FHIR\QuestionnaireResponse\FhirQuestionnaireResponseFormService;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewRequest;

final readonly class QuestionnaireResponseWriteApplier implements PatientWriteApplierInterface
{
    public function __construct(
        private FhirQuestionnaireResponseFormService $service,
        private FhirRelatedPersonService $relatedPersons,
        private QuestionnaireResponseEncounterForm $encounterForm,
    ) {
    }

    public function type(): PatientWritableType
    {
        return PatientWritableType::QuestionnaireResponse;
    }

    /**
     * Runs the parse and the look-ups approval runs: the Questionnaire (at the version the
     * canonical pins), the patient and any encounter must be on this server. A caregiver source
     * must also be one of the patient's related persons on file, since the chart will show them
     * as the one who answered.
     */
    public function canWrite(array $resource): bool
    {
        unset($resource['resourceType']);
        try {
            $parsed = $this->service->parseFhirResource(new FHIRQuestionnaireResponse($resource));
            $this->service->checkInsertable($parsed);
        } catch (\InvalidArgumentException) {
            return false;
        }

        return $this->sourceIsOnFile($resource, $parsed);
    }

    /**
     * Parses the held response and saves it under the request uuid, then adds it to its
     * encounter, if it has one; any failure throws and nothing is written.
     *
     * @return ?string "Filed in encounter <n>" when it was added to an encounter
     */
    public function apply(ReviewRequest $request, ReviewActor $reviewer, PatientWriteApprovalOptions $options): ?string
    {
        $json = $request->payload;
        if (($json['resourceType'] ?? null) !== 'QuestionnaireResponse') {
            throw new \RuntimeException('The held request is not a QuestionnaireResponse');
        }
        unset($json['resourceType']);

        try {
            $parsed = $this->service->parseFhirResource(new FHIRQuestionnaireResponse($json));
            // Checked again here: the caregiver may have been removed from the patient's related
            // persons since the screen offered Approve.
            if (!$this->sourceIsOnFile($json, $parsed)) {
                throw new \RuntimeException('The caregiver the response names is no longer one of the patient\'s related persons');
            }
            $result = $this->service->insertOpenEMRRecordWithIssuedId(
                $parsed,
                $request->uuid,
                writtenByPatient: true,
                encounter: $options->encounter,
            );
        } catch (\InvalidArgumentException $e) {
            // e.g. the Questionnaire it answers has been removed since the write was held, or the
            // encounter staff chose is not the patient's
            throw new \RuntimeException('The held QuestionnaireResponse cannot be saved', 0, $e);
        }
        if ($result->hasErrors()) {
            throw new \RuntimeException('The held QuestionnaireResponse could not be written to the chart');
        }
        $saved = $result->getFirstDataResult();
        $encounter = is_array($saved) ? ($saved['encounter'] ?? null) : null;
        if (!is_int($encounter) || $encounter <= 0) {
            return null;
        }
        $this->encounterForm->add($request->uuid, $request->pid, $encounter, $reviewer);

        return 'Filed in encounter ' . $encounter;
    }

    /**
     * Whether the response's source is the patient, or a caregiver who is one of the patient's
     * related persons on file.
     *
     * @param array<array-key, mixed> $resource
     * @param array<array-key, mixed> $parsed from parseFhirResource()
     */
    private function sourceIsOnFile(array $resource, array $parsed): bool
    {
        $source = FhirPayloadReader::reference($resource['source'] ?? null);
        if ($source === null || !str_starts_with($source, 'RelatedPerson/')) {
            return true;
        }
        $patientUuid = $parsed['puuid'] ?? null;

        return preg_match(FhirQuestionnaireResponseFormService::CAREGIVER_SOURCE_REFERENCE, $source, $match) === 1
            && is_string($patientUuid)
            && $this->relatedPersons->getOne($match[1], $patientUuid)->hasData();
    }
}
