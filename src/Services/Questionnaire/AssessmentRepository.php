<?php

/**
 * The database behind the Questionnaire Assessments page: availability rules, the
 * questionnaire repository, saved responses and the patient's UUID.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;

final readonly class AssessmentRepository implements AssessmentStore
{
    public function __construct(
        private QuestionnaireAvailabilityService $availability,
        private QuestionnaireService $questionnaires,
        private QuestionnaireResponseService $responses,
        private PatientService $patients,
    ) {
    }

    public function availableQuestionnaires(AvailabilityContext $context): array
    {
        return $this->availability->getAvailableQuestionnaires($context);
    }

    public function availableIds(AvailabilityContext $context): array
    {
        return $this->availability->getAvailableIds($context);
    }

    public function patientHistory(int $pid): array
    {
        return QueryUtils::fetchRecordsNoLog(
            "SELECT
                qr.id AS questionnaire_response_id,
                qr.questionnaire_foreign_id,
                qr.response_id,
                qr.questionnaire_name,
                qr.encounter,
                qr.create_time,
                qr.last_updated,
                qr.status,
                qr.version
             FROM questionnaire_response qr
             WHERE qr.patient_id = ?
                AND COALESCE(qr.encounter, 0) = 0
             ORDER BY qr.last_updated DESC, qr.id DESC",
            [$pid]
        );
    }

    public function questionnaire(int $id): array
    {
        return $this->questionnaires->fetchQuestionnaireById($id);
    }

    public function response(string $responseId): array
    {
        return $this->responses->fetchQuestionnaireResponseByResponseId($responseId);
    }

    public function savePatientResponse(int $pid, string $responseJson, ?string $responseId, string $questionnaireJson): array
    {
        return $this->responses->saveQuestionnaireResponse(
            response: $responseJson,
            pid: $pid,
            encounter: 0,
            qr_id: $responseId,
            qr_record_id: null,
            q: $questionnaireJson,
            q_id: null,
            form_response: null,
            add_report: true,
        );
    }

    /**
     * Only this patient gets a UUID if missing, rather than a backfill of the whole table on
     * every save; createMissingUuidForRow does nothing when the row already has one.
     */
    public function patientUuid(int $pid): string
    {
        $uuid = $this->patients->getUuid((string)$pid);
        if ($uuid === false) {
            UuidRegistry::createMissingUuidForRow('patient_data', 'pid', $pid);
            $uuid = $this->patients->getUuid((string)$pid);
        }

        return $uuid !== false ? UuidRegistry::uuidToString($uuid) : '';
    }
}
