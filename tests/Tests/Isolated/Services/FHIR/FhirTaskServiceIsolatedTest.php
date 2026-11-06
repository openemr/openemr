<?php

/**
 * Isolated tests for FhirTaskService: the patient compartment, the search
 * parameters, and how a task record becomes a FHIR Task.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTask;
use OpenEMR\Services\FHIR\FhirTaskService;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class FhirTaskServiceIsolatedTest extends TestCase
{
    private const TASK = '9f1c2a5e-3b7d-4c8e-a1f0-2d6b8e4c7a19';
    private const PATIENT = '95e8d830-3068-48cf-930a-2fefb18c2bcf';
    private const SERVICE_REQUEST = '95e9d3fb-fe7b-448a-aa60-d40b11b486a5';
    private const PRACTITIONER = '9b6a5d3f-2c1e-4f8a-b7d0-1e3c5a7f9b2d';
    private const ORGANIZATION = '95f0a1c2-7d3e-4b5f-8a9c-0e1d2f3a4b5c';
    private const PARENT = '7a0b1c2d-3e4f-4a5b-8c6d-7e8f9a0b1c2d';

    public function testThePatientCompartmentBindsToThePatientUuid(): void
    {
        $interfaces = class_implements(FhirTaskService::class);
        $this->assertIsArray($interfaces);
        $this->assertContains(IPatientCompartmentResourceService::class, $interfaces);
        $field = (new FhirTaskService())->getPatientContextSearchField();
        $this->assertSame('patient', $field->getName());
        $mapped = $field->getMappedFields();
        $this->assertIsArray($mapped);
        $this->assertSame('puuid', $mapped[0]->getField());
    }

    public function testItSearchesByTheFirstVersionsFields(): void
    {
        $params = (new FhirTaskService())->getSearchParams();
        $this->assertIsArray($params);
        $this->assertSame(
            ['_id', 'patient', 'subject', 'status', 'code', 'focus', 'owner', 'requester', 'part-of', 'authored-on', '_lastUpdated'],
            array_keys($params)
        );
    }

    public function testAFullRecordBecomesAFhirTask(): void
    {
        $task = (new FhirTaskService())->parseOpenEMRRecord([
            'uuid' => self::TASK,
            'status' => 'in-progress',
            'status_reason' => 'Waiting for the pantry to call back',
            'business_status' => 'Contacted',
            'intent' => 'order',
            'priority' => 'urgent',
            'code' => 'fulfill',
            'code_system' => FhirTaskService::TASK_CODE_SYSTEM,
            'description' => 'Referral to a food pantry',
            'puuid' => self::PATIENT,
            'focus_type' => 'ServiceRequest',
            'focus_uuid' => self::SERVICE_REQUEST,
            'owner_type' => 'Organization',
            'owner_uuid' => self::ORGANIZATION,
            'requester_type' => 'Practitioner',
            'requester_uuid' => self::PRACTITIONER,
            'part_of_uuid' => self::PARENT,
            'authored_on' => '2026-10-15 09:00:00',
            'last_modified' => '2026-10-15 10:30:00',
        ]);
        $json = $this->toArray($task);

        $this->assertSame('Task', $json['resourceType']);
        $this->assertSame(self::TASK, $json['id']);
        $this->assertSame('in-progress', $json['status']);
        $this->assertSame(['text' => 'Waiting for the pantry to call back'], $json['statusReason']);
        $this->assertSame(['text' => 'Contacted'], $json['businessStatus']);
        $this->assertSame('order', $json['intent']);
        $this->assertSame('urgent', $json['priority']);
        $this->assertSame(
            [['system' => FhirTaskService::TASK_CODE_SYSTEM, 'code' => 'fulfill', 'display' => 'fulfill']],
            self::at($json, 'code', 'coding')
        );
        $this->assertSame('Referral to a food pantry', $json['description']);
        $this->assertSame('Patient/' . self::PATIENT, self::at($json, 'for', 'reference'));
        $this->assertSame('ServiceRequest/' . self::SERVICE_REQUEST, self::at($json, 'focus', 'reference'));
        $this->assertSame('Organization/' . self::ORGANIZATION, self::at($json, 'owner', 'reference'));
        $this->assertSame('Practitioner/' . self::PRACTITIONER, self::at($json, 'requester', 'reference'));
        $this->assertSame('Task/' . self::PARENT, self::at($json, 'partOf', 0, 'reference'));
        $this->assertStringStartsWith('2026-10-15T09:00:00', (string)self::text(self::at($json, 'authoredOn')));
        $this->assertSame($json['lastModified'], self::at($json, 'meta', 'lastUpdated'));
    }

    /**
     * A write keeps a code with its system, and the upgrade gave every earlier code one, so a code
     * without a system isn't rendered under a guessed one: code search wouldn't find it there.
     */
    public function testACodeWithoutItsSystemIsLeftOut(): void
    {
        $json = $this->toArray((new FhirTaskService())->parseOpenEMRRecord([
            'uuid' => self::TASK,
            'status' => 'requested',
            'intent' => 'order',
            'code' => 'fulfill',
            'puuid' => self::PATIENT,
        ]));
        $this->assertArrayNotHasKey('code', $json);
    }

    public function testASparseRecordLeavesTheOptionalElementsOut(): void
    {
        $json = $this->toArray((new FhirTaskService())->parseOpenEMRRecord([
            'uuid' => self::TASK,
            'status' => 'requested',
            'intent' => 'order',
            'status_reason' => '',
            'focus_type' => 'ServiceRequest',
            'focus_uuid' => null,
            'owner_type' => null,
            'owner_uuid' => self::ORGANIZATION,
        ]));

        $this->assertSame('requested', $json['status']);
        $this->assertSame('order', $json['intent']);
        foreach (['statusReason', 'businessStatus', 'priority', 'code', 'description', 'for', 'focus', 'owner', 'requester', 'partOf', 'authoredOn'] as $element) {
            $this->assertArrayNotHasKey($element, $json, "$element should be left out");
        }
        $this->assertArrayHasKey('lastModified', $json);
    }

    /**
     * @return array<mixed>
     */
    private function toArray(FHIRTask $task): array
    {
        $decoded = json_decode((string)json_encode($task), true);
        $this->assertIsArray($decoded);
        return $decoded;
    }

    /** A value deep in decoded JSON, or null when any step is missing. */
    private static function at(mixed $value, string|int ...$path): mixed
    {
        foreach ($path as $step) {
            if (!is_array($value) || !array_key_exists($step, $value)) {
                return null;
            }
            $value = $value[$step];
        }
        return $value;
    }

    private static function text(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
