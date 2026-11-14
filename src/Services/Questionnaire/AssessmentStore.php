<?php

/**
 * What the patient dashboard's Questionnaire Assessments page reads and writes.
 *
 * One seam for the page, the assessment workspace and its save, so their controllers can be
 * tested without a database. AssessmentRepository is the implementation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

interface AssessmentStore
{
    /**
     * Repository rows of the questionnaires offered in this context, in assignment order.
     *
     * @return list<array<string, mixed>>
     */
    public function availableQuestionnaires(AvailabilityContext $context): array;

    /**
     * Ids of the questionnaires offered in this context.
     *
     * @return list<int>
     */
    public function availableIds(AvailabilityContext $context): array;

    /**
     * The patient's patient-level responses (no encounter), newest first.
     *
     * @return list<array<mixed>>
     */
    public function patientHistory(int $pid): array;

    /**
     * A questionnaire_repository row, or an empty array when there is none.
     *
     * @return array<mixed>
     */
    public function questionnaire(int $id): array;

    /**
     * A questionnaire_response row by its FHIR response id, or an empty array when there is none.
     *
     * @return array<mixed>
     */
    public function response(string $responseId): array;

    /**
     * Save a patient-level response, updating $responseId when given.
     *
     * @return array{id: ?int, response_id: string, new: bool} the record id (null if unknown) and response id
     */
    public function savePatientResponse(int $pid, string $responseJson, ?string $responseId, string $questionnaireJson): array;

    /**
     * The patient's UUID as a string, created first if the row has none; empty if it cannot be.
     */
    public function patientUuid(int $pid): string;
}
