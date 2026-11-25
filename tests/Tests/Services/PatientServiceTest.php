<?php

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Patient\PatientUpdatedEvent;
use OpenEMR\Services\PatientService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Patient Service Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

class PatientServiceTest extends TestCase
{
    /**
     * @var PatientService
     */
    private $patientService;
    private $fixtureManager;

    private array $patientFixture;

    protected function setUp(): void
    {
        $this->patientService = new PatientService();
        $this->fixtureManager = new FixtureManager();
        $this->patientFixture = $this->fixtureManager->getSinglePatientFixture();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testGetFreshPid(): void
    {
        $actualValue = $this->patientService->getFreshPid();
        $this->assertGreaterThan(0, $actualValue);
    }

    #[Test]
    public function testInsertFailure(): void
    {
        $this->patientFixture["fname"] = "";
        $this->patientFixture["DOB"] = "12/27/2017";
        if (isset($this->patientFixture["sex"])) {
            unset($this->patientFixture["sex"]);
        }

        $actualResult = $this->patientService->insert($this->patientFixture);

        $this->assertFalse($actualResult->isValid());

        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("sex", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("DOB", $actualResult->getValidationMessages());
        $this->assertEquals(3, count($actualResult->getValidationMessages()));
    }

    #[Test]
    public function testInsertSuccess(): void
    {
        $actualResult = $this->patientService->insert($this->patientFixture);
        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(1, count($actualResult->getData()));

        $dataResult = $actualResult->getData()[0];
        $this->assertIsArray($dataResult);
        $this->assertArrayHasKey("pid", $dataResult);
        $this->assertGreaterThan(0, $dataResult["pid"]);
        $this->assertArrayHasKey("uuid", $dataResult);

        $this->assertEquals(0, count($actualResult->getValidationMessages()));
        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertFalse($actualResult->hasInternalErrors());
    }

    #[Test]
    public function testUpdateFailure(): void
    {
        $this->patientService->insert($this->patientFixture);

        $this->patientFixture["fname"] = "";

        $actualResult = $this->patientService->update("not-a-uuid", $this->patientFixture);

        $this->assertFalse($actualResult->isValid());

        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("uuid", $actualResult->getValidationMessages());
        $this->assertEquals(2, count($actualResult->getValidationMessages()));
    }

    #[Test]
    public function testUpdateSuccess(): void
    {
        $actualResult = $this->patientService->insert($this->patientFixture);
        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(1, count($actualResult->getData()));

        $dataResult = $actualResult->getData()[0];
        $this->assertIsArray($dataResult);
        $this->assertArrayHasKey("pid", $dataResult);
        $this->assertGreaterThan(0, $dataResult["pid"]);
        $this->assertArrayHasKey("uuid", $dataResult);

        $actualUuid = $dataResult["uuid"];
        $this->assertIsString($actualUuid);

        $this->patientFixture["phone_home"] = "555-111-4444";
        $this->patientService->update($actualUuid, $this->patientFixture);

        $sql = "SELECT `uuid`, `phone_home` FROM `patient_data` WHERE `uuid` = ?";
        $result = sqlQuery($sql, [UuidRegistry::uuidToBytes($actualUuid)]);
        $this->assertEquals($actualUuid, UuidRegistry::uuidToString($result["uuid"]));
        $this->assertEquals("555-111-4444", $result["phone_home"]);
    }

    #[Test]
    public function testUpdateBackfillsPortalLoginUsername(): void
    {
        $actualResult = $this->patientService->insert($this->patientFixture);
        $this->assertTrue($actualResult->isValid());
        $data = $actualResult->getData();
        $this->assertIsArray($data);
        $dataResult = $data[0];
        $this->assertIsArray($dataResult);
        $pid = $dataResult['pid'];

        // Create portal credentials with a username but empty login username
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_access_onsite (pid, portal_username, portal_login_username) VALUES (?, ?, '')",
            [$pid, 'testportaluser']
        );

        try {
            // Update patient with portal access enabled via databaseUpdate() —
            // this is the code path used by demographics_save.php where the fix lives
            $this->patientFixture['pid'] = $pid;
            $this->patientFixture['allow_patient_portal'] = 'YES';
            $this->patientService->databaseUpdate($this->patientFixture);

            $row = QueryUtils::querySingleRow("SELECT portal_login_username FROM patient_access_onsite WHERE pid = ?", [$pid]);
            $this->assertIsArray($row);
            $this->assertSame('testportaluser', $row['portal_login_username']);
        } finally {
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_access_onsite WHERE pid = ?", [$pid]);
        }
    }

    #[Test]
    public function testUpdateRecordsUpdatedBy(): void
    {
        [, $uuid] = $this->insertFixturePatient();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        // The session is shared across tests: restore whatever was there before.
        $hadAuthUserId = $session->has('authUserID');
        $priorAuthUserId = $session->get('authUserID');
        $session->set('authUserID', 1);

        try {
            $this->patientFixture['phone_home'] = '555-111-4444';
            $result = $this->patientService->update($uuid, $this->patientFixture);
            $this->assertTrue($result->isValid());
        } finally {
            if ($hadAuthUserId) {
                $session->set('authUserID', $priorAuthUserId);
            } else {
                $session->remove('authUserID');
            }
        }

        $updatedBy = QueryUtils::fetchSingleValue(
            'SELECT `updated_by` FROM `patient_data` WHERE `uuid` = ?',
            'updated_by',
            [UuidRegistry::uuidToBytes($uuid)]
        );
        $this->assertEquals(1, $updatedBy);
    }

    #[Test]
    public function testUpdateBackfillsPortalLoginUsernameThroughApiUpdate(): void
    {
        [$pid, $uuid] = $this->insertFixturePatient();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_access_onsite (pid, portal_username, portal_login_username) VALUES (?, ?, '')",
            [$pid, 'testportaluser']
        );

        try {
            $this->patientFixture['allow_patient_portal'] = 'YES';
            $result = $this->patientService->update($uuid, $this->patientFixture);
            $this->assertTrue($result->isValid());

            $row = QueryUtils::querySingleRow("SELECT portal_login_username FROM patient_access_onsite WHERE pid = ?", [$pid]);
            $this->assertIsArray($row);
            $this->assertSame('testportaluser', $row['portal_login_username']);
        } finally {
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_access_onsite WHERE pid = ?", [$pid]);
        }
    }

    #[Test]
    public function testUpdateSendsTheSameEventPayloadAsDatabaseUpdate(): void
    {
        [$pid, $uuid] = $this->insertFixturePatient();
        $events = [];
        $listener = function (PatientUpdatedEvent $event) use (&$events): void {
            $events[] = $event;
        };
        $dispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
        $dispatcher->addListener(PatientUpdatedEvent::EVENT_HANDLE, $listener);

        try {
            $this->patientFixture['phone_home'] = '555-222-3333';
            $this->patientService->update($uuid, $this->patientFixture);
            $this->patientFixture['pid'] = $pid;
            $this->patientFixture['phone_home'] = '555-444-5555';
            $this->patientService->databaseUpdate($this->patientFixture);
        } finally {
            $dispatcher->removeListener(PatientUpdatedEvent::EVENT_HANDLE, $listener);
        }

        $this->assertCount(2, $events);
        [$fromUpdate, $fromDatabaseUpdate] = $events;
        foreach ([$fromUpdate, $fromDatabaseUpdate] as $event) {
            $before = $event->getDataBeforeUpdate();
            $this->assertIsArray($before);
            $this->assertEquals($pid, $before['pid']);
            $this->assertSame(UuidRegistry::uuidToBytes($uuid), $before['uuid'], 'the stored row, with the binary uuid');

            $after = $event->getNewPatientData();
            $this->assertIsArray($after);
            $this->assertFalse(array_is_list($after), 'one patient record, not a list of records');
            $this->assertEquals($pid, $after['pid']);
            $this->assertArrayNotHasKey('uuid', $after);
        }
        $afterUpdate = $fromUpdate->getNewPatientData();
        $this->assertIsArray($afterUpdate);
        $this->assertSame('555-222-3333', $afterUpdate['phone_home']);
        $beforeDatabaseUpdate = $fromDatabaseUpdate->getDataBeforeUpdate();
        $this->assertIsArray($beforeDatabaseUpdate);
        $this->assertSame('555-222-3333', $beforeDatabaseUpdate['phone_home'], 'the second save starts from what update() stored');
    }

    #[Test]
    public function testUpdateIgnoresPidInTheRequestData(): void
    {
        [$pid, $uuid] = $this->insertFixturePatient();
        $otherPid = $pid + 1000000;

        $this->patientFixture['pid'] = $otherPid;
        $this->patientFixture['phone_home'] = '555-666-7777';
        $result = $this->patientService->update($uuid, $this->patientFixture);
        $this->assertTrue($result->isValid());

        $phone = QueryUtils::fetchSingleValue(
            'SELECT `phone_home` FROM `patient_data` WHERE `uuid` = ?',
            'phone_home',
            [UuidRegistry::uuidToBytes($uuid)]
        );
        $this->assertSame('555-666-7777', $phone);
        $this->assertFalse(QueryUtils::querySingleRow('SELECT `pid` FROM `patient_data` WHERE `pid` = ?', [$otherPid]));
    }

    #[Test]
    public function testFailedPortalBackfillRollsBackDatabaseUpdate(): void
    {
        [$pid] = $this->insertFixturePatient();
        $this->patientFixture['pid'] = $pid;
        $this->assertBackfillFailureRollsBack(
            $pid,
            fn(PatientService $service): array => $service->databaseUpdate($this->patientFixture)
        );
    }

    #[Test]
    public function testFailedPortalBackfillRollsBackApiUpdate(): void
    {
        [$pid, $uuid] = $this->insertFixturePatient();
        $this->assertBackfillFailureRollsBack(
            $pid,
            fn(PatientService $service): ProcessingResult => $service->update($uuid, $this->patientFixture)
        );
    }

    #[Test]
    public function testUpdatePatientDataRejectsNonArrayFields(): void
    {
        [$pid] = $this->insertFixturePatient();

        $this->expectException(\InvalidArgumentException::class);
        updatePatientData($pid, 'not an array');
    }

    /**
     * Save a new phone number with portal access on, through a service whose
     * portal backfill throws, and check nothing was saved or announced.
     *
     * @param callable(PatientService): mixed $save
     */
    private function assertBackfillFailureRollsBack(int $pid, callable $save): void
    {
        $failingService = new class extends PatientService {
            protected function backfillPortalLoginUsername(array $data): void
            {
                throw new \RuntimeException('forced portal backfill failure');
            }
        };
        $phoneBefore = QueryUtils::fetchSingleValue(
            'SELECT `phone_home` FROM `patient_data` WHERE `pid` = ?',
            'phone_home',
            [$pid]
        );
        $this->assertNotSame('555-888-9999', $phoneBefore);

        $events = [];
        $listener = function (PatientUpdatedEvent $event) use (&$events): void {
            $events[] = $event;
        };
        $dispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
        $dispatcher->addListener(PatientUpdatedEvent::EVENT_HANDLE, $listener);

        $this->patientFixture['allow_patient_portal'] = 'YES';
        $this->patientFixture['phone_home'] = '555-888-9999';
        try {
            $save($failingService);
            $this->fail('the forced backfill failure should propagate');
        } catch (\RuntimeException $e) {
            $this->assertSame('forced portal backfill failure', $e->getMessage());
        } finally {
            $dispatcher->removeListener(PatientUpdatedEvent::EVENT_HANDLE, $listener);
        }

        $row = QueryUtils::querySingleRow(
            'SELECT `phone_home`, `allow_patient_portal` FROM `patient_data` WHERE `pid` = ?',
            [$pid]
        );
        $this->assertIsArray($row);
        $this->assertSame($phoneBefore, $row['phone_home'], 'the patient row is unchanged');
        $this->assertNotSame('YES', $row['allow_patient_portal']);
        $this->assertSame([], $events, 'no PatientUpdatedEvent for a rolled-back save');
    }

    /**
     * @return array{int, string} the new patient's pid and string uuid
     */
    private function insertFixturePatient(): array
    {
        $result = $this->patientService->insert($this->patientFixture);
        $this->assertTrue($result->isValid());
        $ids = $result->getFirstDataResult();
        $this->assertIsArray($ids);
        $this->assertIsNumeric($ids['pid']);
        $this->assertIsString($ids['uuid']);
        return [(int) $ids['pid'], $ids['uuid']];
    }

    #[Test]
    public function testPatientQueries(): void
    {
        $this->fixtureManager->installPatientFixtures();

        $result = sqlQuery("SELECT `uuid` FROM `patient_data`");
        $existingUuid = UuidRegistry::uuidToString($result['uuid']);

        // getOne
        $actualResult = $this->patientService->getOne($existingUuid);
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        $this->assertEquals($existingUuid, $resultData["uuid"]);
        $this->assertArrayHasKey("fname", $resultData);
        $this->assertArrayHasKey("lname", $resultData);
        $this->assertArrayHasKey("sex", $resultData);
        $this->assertArrayHasKey("DOB", $resultData);
        $this->assertArrayHasKey("uuid", $resultData);

        // getOne - validate uuid
        $expectedUuid = $resultData["uuid"];
        $actualResult = $this->patientService->getOne($expectedUuid);
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        $this->assertEquals($expectedUuid, $resultData["uuid"]);

        // getOne - with an invalid uuid
        $actualResult = $this->patientService->getOne("not-a-uuid");
        $this->assertEquals(1, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));

        // getAll
        $actualResult = $this->patientService->getAll(["postal_code" => "90210"]);
        $this->assertNotNull($actualResult);
        $this->assertGreaterThan(1, count($actualResult->getData()));

        foreach ($actualResult->getData() as $patientRecord) {
            $this->assertArrayHasKey("fname", $resultData);
            $this->assertArrayHasKey("lname", $resultData);
            $this->assertArrayHasKey("sex", $resultData);
            $this->assertArrayHasKey("DOB", $resultData);
            $this->assertArrayHasKey("uuid", $resultData);
            $this->assertEquals("90210", $patientRecord["postal_code"]);
        }
    }

    #[Test]
    public function testGetDateColumnsClassifiesPatientDataColumns(): void
    {
        $columns = $this->patientService->getDateColumns();

        $this->assertContains('DOB', $columns['date']);
        $this->assertContains('date', $columns['datetime']);
        $this->assertNotContains('DOB', $columns['datetime']);
        $this->assertNotContains('fname', $columns['date']);
        $this->assertNotContains('fname', $columns['datetime']);
    }
}
