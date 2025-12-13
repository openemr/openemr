<?php

namespace OpenEMR\Tests\Validators;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\PatientValidator;
use OpenEMR\Common\Uuid\UuidRegistry;

class PatientValidatorTest extends TestCase
{
    private $patientValidator;
    private $patientFixture;
    private $fixtureManager;

    protected function setUp(): void
    {
        $this->patientValidator = new PatientValidator();
        $this->fixtureManager = new FixtureManager();

        $patientFixtures = $this->fixtureManager->getPatientFixtures();
        $this->patientFixture = (array) array_pop($patientFixtures);
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
    }

    public function testValidationInvalidContext(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->patientValidator->validate($this->patientFixture, 'invalid-context');
    }

    public function testValidationInsertFailure(): void
    {
        $this->patientFixture["fname"] = "";
        $actualResult = $this->patientValidator->validate($this->patientFixture, PatientValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($actualResult->isValid());
        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertEquals(1, count($actualResult->getValidationMessages()));
    }

    public function testValidationInsertSuccess(): void
    {
        $actualResult = $this->patientValidator->validate($this->patientFixture, PatientValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(0, count($actualResult->getValidationMessages()));
    }

    public function testValidationUpdateFailure(): void
    {
        $this->patientFixture["uuid"] = $this->fixtureManager->getUnregisteredUuid();
        $this->patientFixture["fname"] = "";
        $this->patientFixture["sex"] = "M";

        $actualResult = $this->patientValidator->validate($this->patientFixture, PatientValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($actualResult->isValid());

        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("sex", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("uuid", $actualResult->getValidationMessages());
        $this->assertEquals(3, count($actualResult->getValidationMessages()));
    }

    public function testValidationUpdateSuccess(): void
    {
        $patientFixture = $this->fixtureManager->getSinglePatientFixture();
        $this->fixtureManager->installSinglePatientFixture($patientFixture);

        $fixturePid = sqlQuery(
            "SELECT pid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        )['pid'];

        $this->patientFixture['pid'] = intval($fixturePid);

        $fixtureUuid = sqlQuery(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        )['uuid'];

        $fixtureUuid = UuidRegistry::uuidToString($fixtureUuid);

        $this->patientFixture['uuid'] = $fixtureUuid;

        // updates do not require all fields
        unset($this->patientFixture["fname"]);

        $actualResult = $this->patientValidator->validate($this->patientFixture, PatientValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(0, count($actualResult->getValidationMessages()));
    }

    public function testIsExistingUuid(): void
    {
        // ensure we have an installed record/patient
        $patientFixture = $this->fixtureManager->getSinglePatientFixture();
        $this->fixtureManager->installSinglePatientFixture($patientFixture);

        $fixtureUuid = sqlQuery(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        )['uuid'];

        $fixtureUuid = UuidRegistry::uuidToString($fixtureUuid);

        $this->assertEquals(36, strlen($fixtureUuid));

        $actualResult = $this->patientValidator->isExistingUuid($fixtureUuid);
        $this->assertTrue($actualResult);

        $unregisteredUuid = $this->fixtureManager->getUnregisteredUuid();
        $actualResult = $this->patientValidator->isExistingUuid($unregisteredUuid);
        $this->assertFalse($actualResult);
    }
}
