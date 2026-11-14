<?php

/**
 * In-memory AssessmentStore for the assessment controller tests and render fixtures.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\PatientFile\Assessment;

use OpenEMR\Controllers\Interface\PatientFile\Assessment\AssessmentValues;
use OpenEMR\Services\Questionnaire\AssessmentStore;
use OpenEMR\Services\Questionnaire\AvailabilityContext;

final class FakeAssessmentStore implements AssessmentStore
{
    public const PHQ9 = '{"resourceType":"Questionnaire","title":"PHQ-9","description":"Depression screen"}';

    /** @var list<AvailabilityContext> every availability question asked */
    public array $asked = [];

    /** @var list<array{pid: int, response: string, responseId: ?string, questionnaire: string}> */
    public array $saved = [];

    /**
     * @param list<array<string, mixed>> $questionnaires repository rows offered on the dashboard
     * @param array<string, list<int>> $smartIds questionnaire ids each SMART client id may launch
     * @param list<array<mixed>> $history response rows, newest first
     * @param array<string, array<mixed>> $responses response rows by response id
     */
    public function __construct(
        private readonly array $questionnaires = [],
        private readonly array $smartIds = [],
        private readonly array $history = [],
        private readonly array $responses = [],
        private readonly string $patientUuid = '96506861-511f-4f6d-bc97-b65a78cf1995',
    ) {
    }

    /**
     * Two categories, an in-progress PHQ-9 and a completed GAD-7, and a SMART app that may
     * launch PHQ-9. Patient 7.
     */
    public static function sample(): self
    {
        return new self(
            [
                ['id' => 1, 'name' => 'PHQ-9', 'category' => 'mental', 'category_title' => 'Mental Health', 'version' => '2', 'profile' => 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire', 'questionnaire' => self::PHQ9, 'active' => 1],
                ['id' => '2', 'name' => 'GAD-7', 'category' => 'mental', 'category_title' => 'Mental Health', 'code_display' => 'Anxiety screen', 'active' => 1],
                ['id' => 3, 'name' => '', 'category' => '', 'active' => 1],
                ['id' => 0, 'name' => 'No id'],
            ],
            ['app-1' => [1]],
            [
                ['questionnaire_response_id' => 11, 'questionnaire_foreign_id' => 1, 'response_id' => 'qr-11', 'questionnaire_name' => 'PHQ-9', 'status' => 'in-progress', 'last_updated' => '2026-11-02 10:00:00', 'version' => '2'],
                ['questionnaire_response_id' => 10, 'questionnaire_foreign_id' => 1, 'response_id' => 'qr-10', 'questionnaire_name' => 'PHQ-9', 'status' => 'completed', 'last_updated' => '2026-10-01 10:00:00'],
                ['questionnaire_response_id' => 9, 'questionnaire_foreign_id' => '2', 'response_id' => 'qr-9', 'questionnaire_name' => 'GAD-7', 'status' => 'completed', 'last_updated' => '', 'create_time' => '2026-09-01 09:00:00'],
                ['questionnaire_response_id' => 8, 'questionnaire_foreign_id' => null, 'response_id' => '', 'questionnaire_name' => 'Orphan', 'status' => ''],
            ],
            [
                'qr-11' => ['patient_id' => 7, 'encounter' => 0, 'questionnaire_foreign_id' => 1, 'questionnaire' => self::PHQ9, 'questionnaire_response' => '{"resourceType":"QuestionnaireResponse","status":"in-progress"}'],
                'qr-other' => ['patient_id' => 8, 'encounter' => 0, 'questionnaire_foreign_id' => 1, 'questionnaire' => self::PHQ9],
                'qr-encounter' => ['patient_id' => 7, 'encounter' => 5, 'questionnaire_foreign_id' => 1, 'questionnaire' => self::PHQ9],
                'qr-unlinked' => ['patient_id' => 7, 'encounter' => null, 'questionnaire_foreign_id' => 0],
                'qr-api' => ['patient_id' => '7', 'encounter' => null, 'questionnaire_foreign_id' => '1', 'questionnaire' => '', 'questionnaire_response' => ''],
                'qr-api-orphan' => ['patient_id' => 7, 'encounter' => null, 'questionnaire_foreign_id' => 404, 'questionnaire' => ''],
            ],
        );
    }

    public function availableQuestionnaires(AvailabilityContext $context): array
    {
        $this->asked[] = $context;
        return $this->questionnaires;
    }

    /**
     * The ids the asked-about SMART client may launch.
     */
    public function availableIds(AvailabilityContext $context): array
    {
        $this->asked[] = $context;
        return $this->smartIds[$context->clientId ?? ''] ?? [];
    }

    public function patientHistory(int $pid): array
    {
        return $this->history;
    }

    /**
     * A dashboard row by id, or the inactive questionnaire 4.
     */
    public function questionnaire(int $id): array
    {
        foreach ($this->questionnaires as $row) {
            if (AssessmentValues::positiveInt($row['id'] ?? null) === $id) {
                return $row;
            }
        }
        if ($id === 4) {
            return ['id' => 4, 'name' => 'Retired', 'active' => 0, 'questionnaire' => self::PHQ9];
        }

        return [];
    }

    public function response(string $responseId): array
    {
        return $this->responses[$responseId] ?? [];
    }

    /**
     * Record the save; a new response gets the id qr-new.
     */
    public function savePatientResponse(int $pid, string $responseJson, ?string $responseId, string $questionnaireJson): array
    {
        $this->saved[] = ['pid' => $pid, 'response' => $responseJson, 'responseId' => $responseId, 'questionnaire' => $questionnaireJson];

        return ['id' => 99, 'response_id' => $responseId ?? 'qr-new', 'new' => $responseId === null];
    }

    public function patientUuid(int $pid): string
    {
        return $this->patientUuid;
    }
}
