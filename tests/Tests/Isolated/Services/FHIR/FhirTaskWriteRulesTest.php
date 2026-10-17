<?php

/**
 * What a FHIR Task create or update may say: the code sets, the reference
 * types, the parent task, the statuses a task can't leave, and If-Match.
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
        foreach (['priority', 'code', 'description', 'status_reason', 'business_status', 'focus_type', 'focus_uuid', 'owner_uuid', 'requester_uuid', 'part_of_uuid', 'authored_on'] as $column) {
            $this->assertArrayHasKey($column, $read['fields']);
            $this->assertNull($read['fields'][$column], $column);
        }
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
            'no patient' => [['for' => null], 'for'],
            'for a group' => [['for' => ['reference' => 'Group/' . self::PATIENT]], 'for'],
            'not a uuid' => [['for' => ['reference' => 'Patient/123']], 'for'],
            'focus on a patient' => [['focus' => ['reference' => 'Patient/' . self::PATIENT]], 'focus'],
            'owned by a device' => [['owner' => ['reference' => 'Device/' . self::ORG]], 'owner'],
            'requested by a related person' => [['requester' => ['reference' => 'RelatedPerson/' . self::ORG]], 'requester'],
            'two parents' => [['partOf' => [['reference' => 'Task/' . self::PARENT], ['reference' => 'Task/' . self::REQUEST]]], 'partOf'],
            'a parent that is not a task' => [['partOf' => [['reference' => 'ServiceRequest/' . self::REQUEST]]], 'partOf'],
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
                $read = FhirTaskWriteRules::read(self::task([$element => ['reference' => $type . '/' . self::ORG]]));
                $this->assertSame([], $read['errors'], "$element $type");
                $this->assertSame($type, $read['fields'][$element . '_type']);
            }
        }
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
