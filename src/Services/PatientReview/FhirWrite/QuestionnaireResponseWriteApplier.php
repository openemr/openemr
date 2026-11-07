<?php

/**
 * Writes an approved patient app QuestionnaireResponse to the chart, under the id the app was
 * given when it was held.
 *
 * It goes through the same parse and save as a staff FHIR create, except that the response is
 * created under the issued id and with the patient, not the approver, as the one who answered.
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
use OpenEMR\Services\FHIR\QuestionnaireResponse\FhirQuestionnaireResponseFormService;
use OpenEMR\Services\PatientReview\ReviewRequest;

final readonly class QuestionnaireResponseWriteApplier implements PatientWriteApplierInterface
{
    public function __construct(private FhirQuestionnaireResponseFormService $service)
    {
    }

    public function type(): PatientWritableType
    {
        return PatientWritableType::QuestionnaireResponse;
    }

    public function apply(ReviewRequest $request): void
    {
        $json = $request->payload;
        if (($json['resourceType'] ?? null) !== 'QuestionnaireResponse') {
            throw new \RuntimeException('The held request is not a QuestionnaireResponse');
        }
        unset($json['resourceType']);

        try {
            $parsed = $this->service->parseFhirResource(new FHIRQuestionnaireResponse($json));
            $result = $this->service->insertOpenEMRRecordWithIssuedId($parsed, $request->uuid);
        } catch (\InvalidArgumentException $e) {
            // e.g. the Questionnaire it answers has been removed since the write was held
            throw new \RuntimeException('The held QuestionnaireResponse cannot be saved', 0, $e);
        }
        if ($result->hasErrors()) {
            throw new \RuntimeException('The held QuestionnaireResponse could not be written to the chart');
        }
    }
}
