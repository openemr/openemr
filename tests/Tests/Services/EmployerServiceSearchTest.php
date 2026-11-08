<?php

/**
 * EmployerService Search Tests
 *
 * Verifies EmployerService::search() filtering with typed search fields,
 * backing the query parameter support on GET /api/patient/:puuid/employer.
 *
 * Runs in the services test suite (requires a database connection).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\EmployerService;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EmployerServiceSearchTest extends TestCase
{
    private const NAME_PREFIX = 'test-fixture-Employer ';

    private EmployerService $service;
    private FixtureManager $fixtureManager;
    private int $patientPid;
    private string $patientUuidString;

    protected function setUp(): void
    {
        $this->service = new EmployerService();
        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();

        $patient = QueryUtils::querySingleRow(
            "SELECT `pid`, `uuid` FROM `patient_data` WHERE `pubpid` LIKE ? LIMIT 1",
            [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . "%"]
        );
        if (!is_array($patient)) {
            self::fail('patient fixture not found');
        }
        /** @var array{pid: int|numeric-string, uuid: string} $patient */
        $this->patientPid = (int) $patient['pid'];
        $this->patientUuidString = UuidRegistry::uuidToString($patient['uuid']);

        $this->insertEmployer(self::NAME_PREFIX . 'One', '2020-01-01 00:00:00', null);
        $this->insertEmployer(self::NAME_PREFIX . 'Two', '2024-06-01 00:00:00', null);
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `employer_data` WHERE `name` LIKE ?",
            [self::NAME_PREFIX . "%"]
        );
        $this->fixtureManager->removePatientFixtures();
    }

    private function insertEmployer(string $name, string $startDate, ?string $endDate): void
    {
        $uuid = (new UuidRegistry(['table_name' => 'employer_data']))->createUuid();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `employer_data` (`uuid`, `name`, `pid`, `date`, `start_date`, `end_date`)
             VALUES (?, ?, ?, NOW(), ?, ?)",
            [$uuid, $name, $this->patientPid, $startDate, $endDate]
        );
    }

    #[Test]
    public function testSearchByPatientUuid(): void
    {
        $result = $this->service->search([
            'puuid' => new TokenSearchField('puuid', $this->patientUuidString, true),
        ]);
        $this->assertTrue($result->isValid());
        /** @var list<array{puuid: string}> $data */
        $data = $result->getData();
        $this->assertCount(2, $data);
        foreach ($data as $record) {
            $this->assertEquals($this->patientUuidString, $record['puuid']);
        }
    }

    #[Test]
    public function testSearchByPatientUuidAndNameContains(): void
    {
        $result = $this->service->search([
            'puuid' => new TokenSearchField('puuid', $this->patientUuidString, true),
            'name' => new StringSearchField('name', ['Employer Two'], SearchModifier::CONTAINS),
        ]);
        $this->assertTrue($result->isValid());
        /** @var list<array{name: string}> $data */
        $data = $result->getData();
        $this->assertCount(1, $data);
        $this->assertEquals(self::NAME_PREFIX . 'Two', $data[0]['name']);
    }

    #[Test]
    public function testSearchByStartDateRange(): void
    {
        $result = $this->service->search([
            'puuid' => new TokenSearchField('puuid', $this->patientUuidString, true),
            'start_date' => new DateSearchField('start_date', ['ge2024-01-01'], DateSearchField::DATE_TYPE_DATETIME),
        ]);
        $this->assertTrue($result->isValid());
        /** @var list<array{name: string}> $data */
        $data = $result->getData();
        $this->assertCount(1, $data);
        $this->assertEquals(self::NAME_PREFIX . 'Two', $data[0]['name']);
    }

    #[Test]
    public function testSearchByOtherPatientReturnsNothing(): void
    {
        // a different fixture patient should have no employer rows
        $otherPatient = QueryUtils::querySingleRow(
            "SELECT `uuid` FROM `patient_data` WHERE `pubpid` LIKE ? AND `pid` != ? LIMIT 1",
            [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . "%", $this->patientPid]
        );
        if (!is_array($otherPatient)) {
            self::fail('second patient fixture not found');
        }
        /** @var array{uuid: string} $otherPatient */
        $result = $this->service->search([
            'puuid' => new TokenSearchField('puuid', UuidRegistry::uuidToString($otherPatient['uuid']), true),
        ]);
        $this->assertTrue($result->isValid());
        /** @var list<mixed> $data */
        $data = $result->getData();
        $this->assertCount(0, $data);
    }
}
