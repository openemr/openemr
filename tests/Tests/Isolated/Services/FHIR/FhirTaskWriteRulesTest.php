<?php

/**
 * What a FHIR Task create or update may say: the code sets, the reference
 * types, a Patient owner, the parent task, the inputs and outputs, the
 * statuses a task can't leave, and If-Match.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\RestControllers\FHIR\FhirTaskRestController;
use OpenEMR\Services\FHIR\FhirTaskService;
use OpenEMR\Services\FHIR\FhirTaskWriteRules;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class FhirTaskWriteRulesTest extends TestCase
{
    private const PATIENT = '95e8d830-3068-48cf-930a-2fefb18c2bcf';
    private const REQUEST = '95e9d3fb-fe7b-448a-aa60-d40b11b486a5';
    private const ORG = '95f0a1c2-7d3e-4b5f-8a9c-0e1d2f3a4b5c';
    private const PARENT = '9f1c2a5e-3b7d-4c8e-a1f0-2d6b8e4c7a19';
    private const RESPONSE = '96b2c3d4-e5f6-4a71-9b8c-0d1e2f3a4b5c';

    /**
     * @param array<string, mixed> $changes
     * @return array<string, mixed>
     */
    private static function task(array $changes = []): array
    {
        return array_merge([
            'resourceType' => 'Task',
            'status' => 'requested',
            'intent' => 'order',
            'priority' => 'routine',
            'code' => ['coding' => [['system' => FhirTaskService::TASK_CODE_SYSTEM, 'code' => 'fulfill']]],
            'description' => 'Medically tailored meals',
            'statusReason' => ['text' => 'Waiting on the organization'],
            'for' => ['reference' => 'Patient/' . self::PATIENT],
            'focus' => ['reference' => 'ServiceRequest/' . self::REQUEST],
            'owner' => ['reference' => 'Organization/' . self::ORG],
            'requester' => ['reference' => 'Practitioner/' . self::ORG],
            'partOf' => [['reference' => 'Task/' . self::PARENT]],
        ], $changes);
    }

    public function testAFullBodyBecomesTheTasksFields(): void
    {
        $read = FhirTaskWriteRules::read(self::task(['authoredOn' => '2026-10-17T09:30:00Z']));
        $this->assertSame([], $read['errors']);
        $this->assertSame(self::PATIENT, $read['puuid']);
        $fields = $read['fields'];
        $this->assertSame('requested', $fields['status']);
        $this->assertSame('order', $fields['intent']);
        $this->assertSame('routine', $fields['priority']);
        $this->assertSame('fulfill', $fields['code']);
        $this->assertSame(FhirTaskService::TASK_CODE_SYSTEM, $fields['code_system']);
        $this->assertSame('Medically tailored meals', $fields['description']);
        $this->assertSame('Waiting on the organization', $fields['status_reason']);
        $this->assertSame(['ServiceRequest', self::REQUEST], [$fields['focus_type'], $fields['focus_uuid']]);
        $this->assertSame(['Organization', self::ORG], [$fields['owner_type'], $fields['owner_uuid']]);
        $this->assertSame(['Practitioner', self::ORG], [$fields['requester_type'], $fields['requester_uuid']]);
        $this->assertSame(self::PARENT, $fields['part_of_uuid']);
        $this->assertMatchesRegularExpression('/^2026-10-1[67] \d\d:\d\d:\d\d$/', (string)$fields['authored_on'], 'in the server\'s time zone');
    }

    public function testLeftOutElementsAreClearedNotKept(): void
    {
        // A PUT replaces the task: an element the body leaves out becomes empty.
        $read = FhirTaskWriteRules::read(['status' => 'accepted', 'intent' => 'order', 'for' => ['reference' => 'Patient/' . self::PATIENT]]);
        $this->assertSame([], $read['errors']);
        foreach (['priority', 'code', 'code_system', 'description', 'status_reason', 'business_status', 'focus_type', 'focus_uuid', 'owner_uuid', 'requester_uuid', 'part_of_uuid', 'authored_on'] as $column) {
            $this->assertArrayHasKey($column, $read['fields']);
            $this->assertNull($read['fields'][$column], $column);
        }
        $this->assertSame([[], []], [$read['inputs'], $read['outputs']], 'and so do its inputs and outputs');
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function faults(): array
    {
        return [
            'unknown status' => [['status' => 'done'], 'status'],
            'missing intent' => [['intent' => null], 'intent'],
            'unknown priority' => [['priority' => 'whenever'], 'priority'],
            'a code from another system' => [['code' => ['coding' => [['system' => 'http://example.org', 'code' => 'fulfill']]]], 'code'],
            'a code task-code lacks' => [['code' => ['coding' => [['system' => FhirTaskService::TASK_CODE_SYSTEM, 'code' => 'complete']]]], 'code'],
            'the questionnaire code under task-code' => [['code' => ['coding' => [['system' => FhirTaskService::TASK_CODE_SYSTEM, 'code' => 'complete-questionnaire']]]], 'code'],
            'a task-code code under the SDC' => [['code' => ['coding' => [['system' => FhirTaskService::SDC_TEMP_SYSTEM, 'code' => 'fulfill']]]], 'code'],
            'no patient' => [['for' => null], 'for'],
            'for a group' => [['for' => ['reference' => 'Group/' . self::PATIENT]], 'for'],
            'not a uuid' => [['for' => ['reference' => 'Patient/123']], 'for'],
            'focus on a patient' => [['focus' => ['reference' => 'Patient/' . self::PATIENT]], 'focus'],
            'owned by a device' => [['owner' => ['reference' => 'Device/' . self::ORG]], 'owner'],
            'owned by another patient' => [['owner' => ['reference' => 'Patient/' . self::ORG]], 'owner'],
            'requested by a related person' => [['requester' => ['reference' => 'RelatedPerson/' . self::ORG]], 'requester'],
            'two parents' => [['partOf' => [['reference' => 'Task/' . self::PARENT], ['reference' => 'Task/' . self::REQUEST]]], 'partOf'],
            'a parent that is not a task' => [['partOf' => [['reference' => 'ServiceRequest/' . self::REQUEST]]], 'partOf'],
            'authoredOn not a dateTime' => [['authoredOn' => 'yesterday'], 'authoredOn'],
            'output not a list' => [['output' => ['type' => ['text' => 'Response'], 'valueReference' => ['reference' => 'QuestionnaireResponse/' . self::RESPONSE]]], 'output'],
            'an output with no type' => [['output' => [['valueReference' => ['reference' => 'QuestionnaireResponse/' . self::RESPONSE]]]], 'output[0]'],
            'an output type coding with no system' => [['output' => [['type' => ['coding' => [['code' => 'questionnaire-response']]], 'valueReference' => ['reference' => 'QuestionnaireResponse/' . self::RESPONSE]]]], 'output[0]'],
            'an output that is a Condition' => [['output' => [['type' => ['text' => 'Finding'], 'valueReference' => ['reference' => 'Condition/' . self::RESPONSE]]]], 'output[0]'],
            'an output that is a yes or no' => [['output' => [['type' => ['text' => 'Done'], 'valueBoolean' => true]]], 'output[0]'],
            'an output that is a canonical' => [['output' => [['type' => ['text' => 'Form'], 'valueCanonical' => 'https://example.org/Questionnaire/phq-9']]], 'output[0]'],
            'an output with two values' => [['output' => [['type' => ['text' => 'Contact'], 'valueString' => 'Tuesday', 'valueMarkdown' => 'Tuesday']]], 'output[0]'],
            'an output string with nothing in it' => [['output' => [['type' => ['text' => 'Contact'], 'valueString' => '  ']]], 'output[0]'],
            'an output longer than the limit' => [['output' => [['type' => ['text' => 'Notes'], 'valueMarkdown' => str_repeat('a', FhirTaskWriteRules::MAX_TEXT + 1)]]], 'output[0]'],
            'an output code with no system' => [['output' => [['type' => ['text' => 'Done'], 'valueCodeableConcept' => ['coding' => [['code' => 'food-delivered']]]]]], 'output[0]'],
            'input not a list' => [['input' => ['type' => ['text' => 'Form'], 'valueCanonical' => 'https://example.org/Questionnaire/phq-9']], 'input'],
            'an input that is a Patient' => [['input' => [['type' => ['text' => 'Contact'], 'valueReference' => ['reference' => 'Patient/' . self::PATIENT]]]], 'input[0]'],
            'an input url that is not a url' => [['input' => [['type' => ['text' => 'Form'], 'valueUrl' => 'the clinic website']]], 'input[0]'],
            'an input canonical with a space' => [['input' => [['type' => ['text' => 'Form'], 'valueCanonical' => 'https://example.org/Questionnaire/phq 9']]], 'input[0]'],
        ];
    }

    /**
     * @param array<string, mixed> $changes
     */
    #[DataProvider('faults')]
    public function testEachFaultIsNamedByItsElement(array $changes, string $element): void
    {
        $errors = FhirTaskWriteRules::read(self::task($changes))['errors'];
        $this->assertSame([$element], array_keys($errors));
    }

    public function testEveryAllowedReferenceTypeIsAccepted(): void
    {
        foreach (['focus' => FhirTaskWriteRules::FOCUS_TYPES, 'owner' => FhirTaskWriteRules::OWNER_TYPES, 'requester' => FhirTaskWriteRules::REQUESTER_TYPES] as $element => $types) {
            foreach ($types as $type) {
                // A Patient owner has to be the task's own patient.
                $uuid = $element === 'owner' && $type === 'Patient' ? self::PATIENT : self::ORG;
                $read = FhirTaskWriteRules::read(self::task([$element => ['reference' => $type . '/' . $uuid]]));
                $this->assertSame([], $read['errors'], "$element $type");
                $this->assertSame($type, $read['fields'][$element . '_type']);
            }
        }
    }

    public function testTheQuestionnaireTaskKeepsItsCodeSystemAndOutput(): void
    {
        $read = FhirTaskWriteRules::read(self::task([
            'status' => 'completed',
            'code' => ['coding' => [
                ['system' => 'http://example.org/local', 'code' => 'survey'],
                ['system' => FhirTaskService::SDC_TEMP_SYSTEM, 'code' => 'complete-questionnaire'],
            ]],
            'focus' => ['reference' => 'Questionnaire/' . self::REQUEST],
            // The patient's own task, with the uuid in capitals: the same patient.
            'owner' => ['reference' => 'Patient/' . strtoupper(self::PATIENT)],
            'output' => [[
                'type' => ['coding' => [['system' => FhirTaskService::SDC_TEMP_SYSTEM, 'code' => 'questionnaire-response']], 'text' => 'Completed form'],
                'valueReference' => ['reference' => 'QuestionnaireResponse/' . self::RESPONSE],
            ]],
        ]));
        $this->assertSame([], $read['errors']);
        $this->assertSame([FhirTaskService::SDC_TEMP_SYSTEM, 'complete-questionnaire'], [$read['fields']['code_system'], $read['fields']['code']], 'the first coding it knows');
        $this->assertSame([0 => self::row([
            'type_system' => FhirTaskService::SDC_TEMP_SYSTEM,
            'type_code' => 'questionnaire-response',
            'type_text' => 'Completed form',
            'value_element' => 'valueReference',
            'value_type' => 'QuestionnaireResponse',
            'value_uuid' => self::RESPONSE,
        ])], $read['outputs']);
    }

    public function testThePatientTasksInputsAndOutputsAsSdohClinicalCareWritesThem(): void
    {
        $sdohcc = FhirTaskService::SDOHCC_TEMP_SYSTEM;
        $read = FhirTaskWriteRules::read(self::task([
            'code' => ['coding' => [['system' => $sdohcc, 'code' => 'make-contact']]],
            'owner' => ['reference' => 'RelatedPerson/' . self::ORG],
            'input' => [
                ['type' => ['coding' => [['system' => FhirTaskService::SDC_TEMP_SYSTEM, 'code' => 'questionnaire']]], 'valueCanonical' => 'https://example.org/fhir/Questionnaire/phq-9|1.2'],
                ['type' => ['coding' => [['system' => $sdohcc, 'code' => 'questionnaire-pdf']]], 'valueReference' => ['reference' => 'DocumentReference/' . self::RESPONSE]],
                ['type' => ['coding' => [['system' => $sdohcc, 'code' => 'questionnaire-url']]], 'valueUrl' => 'https://example.org/forms/phq-9'],
                ['type' => ['coding' => [['system' => $sdohcc, 'code' => 'questionnaire-category']]], 'valueCodeableConcept' => ['coding' => [['system' => $sdohcc, 'code' => 'risk-questionnaire']]]],
                ['type' => ['coding' => [['system' => $sdohcc, 'code' => 'contact-entity']]], 'valueReference' => ['reference' => 'Organization/' . self::ORG]],
            ],
            'output' => [
                ['type' => ['coding' => [['system' => $sdohcc, 'code' => 'chosen-contact']]], 'valueMarkdown' => 'The **food pantry**, on Tuesday'],
                ['type' => ['text' => 'Answer'], 'valueString' => 'Yes'],
                ['type' => ['text' => 'What was done'], 'valueCodeableConcept' => ['coding' => [['system' => 'http://example.org/activities', 'code' => 'food-delivered']], 'text' => 'Food delivered']],
            ],
        ]));
        $this->assertSame([], $read['errors']);
        $this->assertSame([$sdohcc, 'make-contact'], [$read['fields']['code_system'], $read['fields']['code']]);
        $this->assertSame('RelatedPerson', $read['fields']['owner_type'], 'a parent can own the task');
        $this->assertSame(
            ['valueCanonical', 'valueReference', 'valueUrl', 'valueCodeableConcept', 'valueReference'],
            array_column($read['inputs'], 'value_element')
        );
        $this->assertSame('https://example.org/fhir/Questionnaire/phq-9|1.2', $read['inputs'][0]['value_text'], 'a canonical with its version');
        $this->assertSame(['DocumentReference', self::RESPONSE], [$read['inputs'][1]['value_type'], $read['inputs'][1]['value_uuid']]);
        $this->assertSame([$sdohcc, 'risk-questionnaire', null], [$read['inputs'][3]['value_system'], $read['inputs'][3]['value_code'], $read['inputs'][3]['value_text']]);
        $this->assertSame(['Organization', self::ORG], [$read['inputs'][4]['value_type'], $read['inputs'][4]['value_uuid']]);
        $this->assertSame(['valueMarkdown', 'The **food pantry**, on Tuesday'], [$read['outputs'][0]['value_element'], $read['outputs'][0]['value_text']]);
        $this->assertSame(['valueString', 'Yes'], [$read['outputs'][1]['value_element'], $read['outputs'][1]['value_text']]);
        $this->assertSame(['http://example.org/activities', 'food-delivered', 'Food delivered'], [$read['outputs'][2]['value_system'], $read['outputs'][2]['value_code'], $read['outputs'][2]['value_text']]);
    }

    public function testEveryCodeItKnowsIsAccepted(): void
    {
        foreach (FhirTaskWriteRules::CODES as $system => $codes) {
            foreach ($codes as $code) {
                $read = FhirTaskWriteRules::read(self::task(['code' => ['coding' => [['system' => $system, 'code' => $code]]]]));
                $this->assertSame([], $read['errors'], "$system $code");
                $this->assertSame([$system, $code], [$read['fields']['code_system'], $read['fields']['code']]);
            }
        }
    }

    public function testOutputsKeepTheirPlaceInTheList(): void
    {
        $response = ['type' => ['text' => 'Response'], 'valueReference' => ['reference' => 'QuestionnaireResponse/' . self::RESPONSE]];
        $read = FhirTaskWriteRules::read(self::task(['output' => [
            ['type' => ['text' => 'Response'], 'valueReference' => ['reference' => 'Goal/' . self::RESPONSE]],
            $response,
        ]]));
        $this->assertSame(['output[0]'], array_keys($read['errors']));
        $this->assertSame([1], array_keys($read['outputs']), 'the good one is still the second');
        $this->assertNull($read['outputs'][1]['type_system'], 'a type given as text alone has no coding');
    }

    public function testEveryInputAndOutputTypeIsAcceptedUpToTheLimit(): void
    {
        $outputs = array_map(
            static fn(string $type): array => ['type' => ['text' => $type], 'valueReference' => ['reference' => $type . '/' . self::RESPONSE]],
            FhirTaskWriteRules::OUTPUT_TYPES
        );
        $read = FhirTaskWriteRules::read(self::task(['output' => $outputs]));
        $this->assertSame([], $read['errors']);
        $this->assertSame(FhirTaskWriteRules::OUTPUT_TYPES, array_column($read['outputs'], 'value_type'));
        $inputs = array_map(
            static fn(string $type): array => ['type' => ['text' => $type], 'valueReference' => ['reference' => $type . '/' . self::RESPONSE]],
            FhirTaskWriteRules::INPUT_TYPES
        );
        $read = FhirTaskWriteRules::read(self::task(['input' => $inputs]));
        $this->assertSame([], $read['errors']);
        $this->assertSame(FhirTaskWriteRules::INPUT_TYPES, array_column($read['inputs'], 'value_type'));

        $many = array_fill(0, FhirTaskWriteRules::MAX_PARAMETERS, $outputs[0]);
        $this->assertSame([], FhirTaskWriteRules::read(self::task(['output' => $many]))['errors']);
        $many[] = $outputs[0];
        $this->assertSame(['output'], array_keys(FhirTaskWriteRules::read(self::task(['output' => $many]))['errors']));
    }

    /**
     * A task_input or task_output row: these columns, the rest null.
     *
     * @param array<string, string> $columns
     * @return array<string, ?string>
     */
    private static function row(array $columns): array
    {
        return array_merge(array_fill_keys(['type_system', 'type_code', 'type_text', 'value_element', 'value_type', 'value_uuid', 'value_system', 'value_code', 'value_text'], null), $columns);
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function transitions(): array
    {
        return [
            'requested to accepted' => ['requested', 'accepted', true],
            'in progress to on hold' => ['in-progress', 'on-hold', true],
            'on hold back to in progress' => ['on-hold', 'in-progress', true],
            'completed stays completed' => ['completed', 'completed', true],
            'completed to in progress' => ['completed', 'in-progress', false],
            'cancelled to requested' => ['cancelled', 'requested', false],
            'failed to completed' => ['failed', 'completed', false],
            'rejected to accepted' => ['rejected', 'accepted', false],
            'completed corrected to entered-in-error' => ['completed', 'entered-in-error', true],
            'requested corrected to entered-in-error' => ['requested', 'entered-in-error', true],
            'entered-in-error to requested' => ['entered-in-error', 'requested', false],
        ];
    }

    #[DataProvider('transitions')]
    public function testAFinalStatusCanOnlyBeCorrected(string $from, string $to, bool $allowed): void
    {
        $this->assertSame($allowed, FhirTaskWriteRules::transitionProblem($from, $to) === null);
    }

    public function testIfMatchTakesAWeakOrStrongVersion(): void
    {
        $this->assertSame(3, FhirTaskRestController::ifMatchVersion('W/"3"'));
        $this->assertSame(12, FhirTaskRestController::ifMatchVersion('"12"'));
        $this->assertNull(FhirTaskRestController::ifMatchVersion(null));
        $this->assertNull(FhirTaskRestController::ifMatchVersion('*'));
        $this->assertFalse(FhirTaskRestController::ifMatchVersion('W/3'));
        $this->assertFalse(FhirTaskRestController::ifMatchVersion('"abc"'));
    }

    public function testATasksVersionBecomesItsETag(): void
    {
        $task = FhirTaskRestController::withETag(new JsonResponse(['resourceType' => 'Task', 'id' => self::PARENT, 'meta' => ['versionId' => '4']]));
        $this->assertSame('W/"4"', $task->headers->get('ETag'));
        $outcome = FhirTaskRestController::withETag(new JsonResponse(['resourceType' => 'OperationOutcome'], 400));
        $this->assertFalse($outcome->headers->has('ETag'), 'only a Task has a version to give');
    }
}
