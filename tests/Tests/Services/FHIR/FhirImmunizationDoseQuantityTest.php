<?php

/**
 * Immunization.doseQuantity through FHIR, read and written: the dose whenever one is recorded,
 * its unit's name, UCUM's system and code only for a UCUM unit, and writes stored as the
 * immunization form stores them, as a Drug Units option id.
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
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunization;
use OpenEMR\Services\FHIR\FhirImmunizationService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FhirImmunizationDoseQuantityTest extends TestCase
{
    private const UCUM = 'http://unitsofmeasure.org';

    private FixtureManager $fixtureManager;
    private FhirImmunizationService $service;
    /** @var array<string, mixed> */
    private array $fixture;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $patient = $this->fixtureManager->getPatientFixtures()[0] ?? null;
        $this->assertIsArray($patient);
        $row = QueryUtils::querySingleRow('SELECT uuid FROM patient_data WHERE pubpid = ?', [$patient['pubpid'] ?? null]);
        $this->assertIsArray($row);
        $fixture = (array)$this->fixtureManager->getSingleFhirImmunizationFixture();
        $fixture['patient'] = ['reference' => 'Patient/' . UuidRegistry::uuidToString($row['uuid'])];
        // No site: the dose is sent whatever the site.
        unset($fixture['site']);
        /** @var array<string, mixed> $fixture */
        $this->fixture = $fixture;
        $this->service = new FhirImmunizationService();
        $this->service->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM immunizations WHERE note LIKE 'test-fixture%'");
        $this->fixtureManager->removePatientFixtures();
    }

    public function testAHalfMillilitreDoseRoundTrips(): void
    {
        $uuid = $this->write(['value' => 0.5, 'unit' => 'mL', 'system' => self::UCUM, 'code' => 'mL']);

        $this->assertSame(['0.5', '9'], $this->stored($uuid), 'stored as the form stores it: 0.5 and the mL option');
        $this->assertSame(['value' => 0.5, 'unit' => 'mL', 'system' => self::UCUM, 'code' => 'mL'], $this->dose($uuid));
    }

    public function testMicrogramsAreUgInUcumAndMcgInTheList(): void
    {
        $uuid = $this->write(['value' => 10, 'unit' => 'micrograms', 'system' => self::UCUM, 'code' => 'ug']);

        $this->assertSame(['10', '7'], $this->stored($uuid));
        $this->assertSame(['value' => 10, 'unit' => 'mcg', 'system' => self::UCUM, 'code' => 'ug'], $this->dose($uuid));
    }

    public function testAUnitThatIsNotUcumIsSentByNameOnly(): void
    {
        $uuid = $this->write(['value' => 5, 'unit' => 'mg', 'system' => self::UCUM, 'code' => 'mg']);
        QueryUtils::sqlStatementThrowException("UPDATE immunizations SET amount_administered_unit = '2' WHERE uuid = ?", [UuidRegistry::uuidToBytes($uuid)]);

        $this->assertSame(['value' => 5, 'unit' => 'mg/1cc'], $this->dose($uuid), 'mg/1cc goes out by name, without a UCUM system or code');
    }

    public function testAUnitStoredAsSentByAnEarlierWriteStillReads(): void
    {
        $uuid = $this->write(['value' => 0.5, 'unit' => 'mL', 'system' => self::UCUM, 'code' => 'mL']);
        QueryUtils::sqlStatementThrowException("UPDATE immunizations SET amount_administered_unit = 'mL' WHERE uuid = ?", [UuidRegistry::uuidToBytes($uuid)]);

        $this->assertSame(['value' => 0.5, 'unit' => 'mL', 'system' => self::UCUM, 'code' => 'mL'], $this->dose($uuid));
    }

    public function testAUnitTheListDoesNotHaveIsRefused(): void
    {
        $result = $this->service->insert($this->resource(['value' => 1, 'unit' => 'international units', 'system' => self::UCUM, 'code' => '[iU]']));

        $this->assertFalse($result->isValid());
        $messages = $result->getValidationMessages();
        $this->assertIsArray($messages);
        $this->assertIsString($messages['doseQuantity'] ?? null);
        $this->assertStringContainsString('"[iU]" is not in the Drug Units list', $messages['doseQuantity']);
        $this->assertEquals(0, QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM immunizations WHERE note LIKE 'test-fixture%'", 'n'));
    }

    /** @param array<string, mixed> $doseQuantity */
    private function resource(array $doseQuantity): FHIRImmunization
    {
        return new FHIRImmunization(['doseQuantity' => $doseQuantity] + $this->fixture);
    }

    /** @param array<string, mixed> $doseQuantity */
    private function write(array $doseQuantity): string
    {
        $result = $this->service->insert($this->resource($doseQuantity));
        $this->assertTrue($result->isValid(), (string)json_encode($result->getValidationMessages()));
        $data = $result->getData();
        $this->assertIsArray($data);
        $row = $data[0] ?? null;
        $this->assertIsArray($row);
        $this->assertIsString($row['uuid'] ?? null);
        return $row['uuid'];
    }

    /** @return list<string> amount_administered and amount_administered_unit as stored */
    private function stored(string $uuid): array
    {
        $row = QueryUtils::querySingleRow('SELECT amount_administered, amount_administered_unit FROM immunizations WHERE uuid = ?', [UuidRegistry::uuidToBytes($uuid)]);
        $this->assertIsArray($row);
        $this->assertIsNumeric($row['amount_administered'] ?? null);
        $this->assertIsString($row['amount_administered_unit'] ?? null);
        return [(string)(float)$row['amount_administered'], $row['amount_administered_unit']];
    }

    /** @return array<string, mixed> the doseQuantity read back, without its empty members */
    private function dose(string $uuid): array
    {
        $data = $this->service->getOne($uuid)->getData();
        $this->assertIsArray($data);
        $resource = $data[0] ?? null;
        $this->assertInstanceOf(FHIRImmunization::class, $resource);
        $json = json_decode((string)json_encode($resource), true);
        $this->assertIsArray($json);
        $this->assertIsArray($json['doseQuantity'] ?? null, 'a doseQuantity is sent');
        /** @var array<string, mixed> $dose */
        $dose = $json['doseQuantity'];
        return $dose;
    }
}
