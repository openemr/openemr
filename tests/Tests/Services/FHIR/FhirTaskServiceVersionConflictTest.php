<?php

/**
 * FhirTaskService flags a version conflict, which the controller answers with 412: a version
 * If-Match named that the task no longer has, and a conditional UPDATE that loses a race after
 * that check passed. The second can't be timed through the API, so a stand-in TaskService loses it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTask;
use OpenEMR\Services\FHIR\FhirTaskService;
use OpenEMR\Services\TaskService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;

class FhirTaskServiceVersionConflictTest extends TestCase
{
    private FixtureManager $fixtureManager;

    private string $patientUuid;

    /** @var list<string> */
    private array $tasks = [];

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $fixture = $this->fixtureManager->getPatientFixtures()[0];
        $this->assertIsArray($fixture);
        $row = QueryUtils::querySingleRow('SELECT uuid FROM patient_data WHERE pubpid = ?', [$fixture['pubpid']]);
        $this->assertIsArray($row);
        $this->assertIsString($row['uuid']);
        $this->patientUuid = UuidRegistry::uuidToString($row['uuid']);
    }

    protected function tearDown(): void
    {
        foreach ($this->tasks as $uuid) {
            $bytes = UuidRegistry::uuidToBytes($uuid);
            QueryUtils::sqlStatementThrowException('DELETE FROM task WHERE uuid = ?', [$bytes]);
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'task' AND uuid = ?", [$bytes]);
        }
        $this->fixtureManager->removePatientFixtures();
    }

    public function testAnUpdateThatFindsTheTaskAtItsVersionHasNoConflict(): void
    {
        $uuid = $this->createTask();
        $service = new FhirTaskService();
        $service->expectVersion(1);
        $result = $service->update($uuid, $this->task(['status' => 'accepted']));
        $this->assertTrue($result->isValid(), (string)json_encode($result->getValidationMessages()));
        $this->assertFalse($service->hadVersionConflict());
    }

    public function testAVersionTheTaskNoLongerHasIsAConflict(): void
    {
        $uuid = $this->createTask();
        $service = new FhirTaskService();
        $service->expectVersion(5);
        $result = $service->update($uuid, $this->task(['status' => 'accepted']));
        $this->assertFalse($result->isValid());
        $this->assertTrue($service->hadVersionConflict());
    }

    public function testAnUpdateThatLosesTheRaceIsAConflict(): void
    {
        $uuid = $this->createTask();
        $service = new FhirTaskService();
        // Someone else's update lands between the version check and this one's UPDATE.
        $service->setTaskService(new class extends TaskService {
            public function update(string $uuid, array $fields, ?int $userId, ?int $expectedVersion = null, ?array $inputs = null, ?array $outputs = null): bool
            {
                return false;
            }
        });
        $result = $service->update($uuid, $this->task(['status' => 'accepted']));
        $this->assertFalse($result->isValid());
        $this->assertTrue($service->hadVersionConflict(), 'no If-Match, but the UPDATE found the task changed');
    }

    public function testAnInvalidBodyIsNotAConflictEvenWithAStaleVersion(): void
    {
        $uuid = $this->createTask();
        $service = new FhirTaskService();
        $service->expectVersion(5);
        $result = $service->update($uuid, $this->task(['status' => 'done']));
        $this->assertFalse($result->isValid());
        $this->assertFalse($service->hadVersionConflict(), 'the body is checked before the version');
    }

    private function createTask(): string
    {
        $result = (new FhirTaskService())->insert($this->task());
        $this->assertTrue($result->isValid(), (string)json_encode($result->getValidationMessages()));
        $data = $result->getData();
        $this->assertIsArray($data);
        $task = $data[0] ?? null;
        $this->assertInstanceOf(FHIRTask::class, $task);
        $uuid = (string)$task->getId();
        $this->tasks[] = $uuid;
        return $uuid;
    }

    /**
     * @param array<string, mixed> $changes
     */
    private function task(array $changes = []): FHIRTask
    {
        return new FHIRTask($changes + [
            'resourceType' => 'Task',
            'status' => 'requested',
            'intent' => 'order',
            'for' => ['reference' => 'Patient/' . $this->patientUuid],
        ]);
    }
}
