<?php

namespace OpenEMR\Tests\Validators;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\PatientValidator;

/**
 * @coversDefaultClass OpenEMR\Validators\PatientValidator
 */
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

    /**
     * @covers ::validate when an invalid context is used
     */
    public function testValidationInvalidContext()
    {
        $this->expectException(\RuntimeException::class);        
        $this->patientValidator->validate($this->patientFixture, 'invalid-context');
    }

    /**
     * @covers ::validate for an insert context with an invalid record
     */
    public function testValidationInsertFailure()
    {
        $this->patientFixture["fname"] = "";
        $actualResult = $this->patientValidator->validate($this->patientFixture, PatientValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($actualResult->isValid());
        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertEquals(1, count($actualResult->getValidationMessages()));
    }

    /**
     * @covers ::validate for an insert context with a valid record
     */
    public function testValidationInsertSuccess()
    {
        $actualResult = $this->patientValidator->validate($this->patientFixture, PatientValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(0, count($actualResult->getValidationMessages()));
    }

    /**
     * @covers ::validate for an update context with an invalid record
     */
    public function testValidationUpdateFailure()
    {
        $this->patientFixture["fname"] = "A";
        $this->patientFixture["sex"] = "M";

        $actualResult = $this->patientValidator->validate($this->patientFixture, PatientValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($actualResult->isValid());
        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("sex", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("pid", $actualResult->getValidationMessages());
        $this->assertEquals(3, count($actualResult->getValidationMessages()));
    }

    /**
     * @covers ::validate for an update context with a valid record
     */
    public function testValidationUpdateSuccess()
    {
        $patientFixture = $this->fixtureManager->getPatientFixture();
        $this->fixtureManager->installPatientFixture($patientFixture);

        $fixturePid = sqlQuery(
            "SELECT pid FROM patient_data WHERE pubpid = ?",
            array($patientFixture->pubpid)
        )['pid'];

        $this->patientFixture['pid'] = intval($fixturePid);
        // updates do not require all fields
        unset($this->patientFixture["fname"]);

        $actualResult = $this->patientValidator->validate($this->patientFixture, PatientValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(0, count($actualResult->getValidationMessages()));
    }
    
    /**
     * @covers ::isExistingPid for success and failure use-cases
     */
    public function testIsExistingPid()
    {
        // ensure we have an installed record/patient
        $patientFixture = $this->fixtureManager->getPatientFixture();
        $this->fixtureManager->installPatientFixture($patientFixture);

        $fixturePid = sqlQuery(
            "SELECT pid FROM patient_data WHERE pubpid = ?",
            array($patientFixture->pubpid)
        )['pid'];   
        $fixturePid = intval($fixturePid);

        $this->assertGreaterThan(0, $fixturePid);

        $actualResult = $this->patientValidator->isExistingPid($fixturePid);
        $this->assertTrue($actualResult);

        $actualResult = $this->patientValidator->isExistingPid(0);
        $this->assertFalse($actualResult);
    }    
}
