<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPerson;
use OpenEMR\Services\FHIR\FhirPersonService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR Person Service CRUD Tests
 *
 * Person writes target the same `users` table as Practitioner. The fixture cleanup
 * is shared with PractitionerFixtureManager (which deletes users WHERE fname LIKE
 * 'test-fixture%') and our fixture uses a matching test-fixture-PersonFirst name.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirPersonServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private PractitionerFixtureManager $practitionerFixtureManager;
    private FHIRPerson $fhirPersonFixture;
    private FhirPersonService $fhirPersonService;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->practitionerFixtureManager = new PractitionerFixtureManager();

        $fixture = (array) $this->fixtureManager->getSingleFhirPersonFixture();
        $this->fhirPersonFixture = new FHIRPerson($fixture);

        $this->fhirPersonService = new FhirPersonService();
        $this->fhirPersonService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        // Shared cleanup with Practitioner — writes go to the same `users` table
        $this->practitionerFixtureManager->removePractitionerFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirPersonFixture->setId(null);
        $processingResult = $this->fhirPersonService->insert($this->fhirPersonFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $processingResult->getData()[0];
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
        $this->assertGreaterThan(0, $dataResult['id']);
    }

    #[Test]
    public function testInsertWithoutNpiReturnsValidationError(): void
    {
        $this->fhirPersonFixture->setId(null);
        // Strip the NPI identifier
        $payload = $this->fhirPersonFixture->jsonSerialize();
        $payload['identifier'] = [];
        $fixture = new FHIRPerson($payload);

        $processingResult = $this->fhirPersonService->insert($fixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    #[Test]
    public function testInsertWithMissingNameFailsPractitionerValidator(): void
    {
        $this->fhirPersonFixture->setId(null);
        $payload = $this->fhirPersonFixture->jsonSerialize();
        $payload['name'] = [];
        $fixture = new FHIRPerson($payload);

        $processingResult = $this->fhirPersonService->insert($fixture);
        $this->assertFalse($processingResult->isValid());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirPersonFixture->setId(null);
        $insertResult = $this->fhirPersonService->insert($this->fhirPersonFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            "Insert should succeed: " . json_encode($insertResult->getValidationMessages())
        );

        $fhirId = $insertResult->getData()[0]['uuid'];
        $this->assertIsString($fhirId);

        $payload = $this->fhirPersonFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['telecom'] = [[
            'system' => 'phone',
            'value' => '(555) 999-9999',
            'use' => 'home',
        ]];
        $updated = new FHIRPerson($payload);

        $actualResult = $this->fhirPersonService->update($fhirId, $updated);
        $this->assertTrue(
            $actualResult->isValid(),
            "Update should succeed: " . json_encode($actualResult->getValidationMessages())
        );
        // PractitionerService::update returns getOne which depends on the search default
        // filtering for npi-bearing users; we don't assert on data shape here, just that
        // the update reported no validation/internal errors above.
        $this->assertFalse($actualResult->hasErrors());
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        $result = $this->fhirPersonService->update('bad-uuid', $this->fhirPersonFixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }
}
