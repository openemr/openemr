<?php

/**
 * Isolated tests for the clinicalStatus FHIR Condition derives from a problem's
 * outcome and occurrence lists.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Condition;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\Services\FHIR\Condition\FhirConditionEncounterDiagnosisService;
use OpenEMR\Services\FHIR\Condition\FhirConditionProblemListItemService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FhirConditionClinicalStatusTest extends TestCase
{
    /**
     * Occurrence list: 0 Unknown or N/A, 1 First, 4-8 the recurrence kinds.
     * Outcome list: 0 Unassigned, 1 Resolved.
     *
     * @return array<string, array{string, int, int, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function statusProvider(): array
    {
        $cases = [
            'occurrence unknown' => [0, 0, 'active'],
            'occurrence First' => [1, 0, 'active'],
            'occurrence Early Recurrence' => [6, 0, 'recurrence'],
            'occurrence Chronic/Recurrent' => [4, 0, 'recurrence'],
            'outcome Resolved' => [0, 1, 'resolved'],
            'outcome Resolved, occurrence First' => [1, 1, 'resolved'],
            'outcome Resolved, occurrence Early Recurrence' => [6, 1, 'resolved'],
        ];
        $data = [];
        foreach (['problem list item' => 'problem', 'encounter diagnosis' => 'encounter'] as $label => $service) {
            foreach ($cases as $case => [$occurrence, $outcome, $status]) {
                $data["$label, $case"] = [$service, $occurrence, $outcome, $status];
            }
        }
        return $data;
    }

    #[DataProvider('statusProvider')]
    public function testClinicalStatusFromOutcomeAndOccurrence(string $service, int $occurrence, int $outcome, string $expected): void
    {
        $record = [
            'uuid' => 'condition-uuid-1',
            'condition_uuid' => 'condition-uuid-1',
            'date' => '2025-01-01 01:00:00',
            'type' => 'medical_problem',
            'title' => 'Test Condition',
            'begdate' => '2025-01-01 02:00:00',
            'enddate' => null,
            'diagnosis' => [
                'A01.1' => [
                    'code' => 'A01.1',
                    'system' => FhirCodeSystemConstants::HL7_ICD10,
                    'description' => 'Typhoid meningitis',
                ],
            ],
            'puuid' => 'patient-uuid-1',
            'encounter_uuid' => 'encounter-uuid-1',
            'last_updated_time' => '2025-01-02 12:00:00',
            'verification' => '',
            'verification_title' => '',
            'resolved' => 0,
            'occurrence' => $occurrence,
            'outcome' => $outcome,
        ];
        // The problem list service's constructor makes a database-backed ConditionService,
        // which parseOpenEMRRecord() doesn't use.
        $parser = $service === 'problem'
            ? (new \ReflectionClass(FhirConditionProblemListItemService::class))->newInstanceWithoutConstructor()
            : new FhirConditionEncounterDiagnosisService();

        $condition = $parser->parseOpenEMRRecord($record);

        $this->assertInstanceOf(FHIRCondition::class, $condition);
        $this->assertSame($expected, (string) $condition->getClinicalStatus()->getCoding()[0]->getCode());
    }
}
