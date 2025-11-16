<?php

/**
 * EncounterReportFormHandlerTest.php
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Reports\Encounter;

use OpenEMR\Reports\Encounter\EncounterReportFormHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EncounterReportFormHandler::class)]
class EncounterReportFormHandlerTest extends TestCase
{
    private EncounterReportFormHandler $handler;
    private array $originalGlobals = [];

    protected function setUp(): void
    {
        $this->handler = new EncounterReportFormHandler();
        
        // Backup $GLOBALS that we'll modify
        if (isset($GLOBALS['date_display_format'])) {
            $this->originalGlobals['date_display_format'] = $GLOBALS['date_display_format'];
        }
        
        // Set default date format for tests
        $GLOBALS['date_display_format'] = 0; // YYYY-MM-DD format
    }

    protected function tearDown(): void
    {
        // Restore original $GLOBALS
        foreach ($this->originalGlobals as $key => $value) {
            $GLOBALS[$key] = $value;
        }
    }

    #[Test]
    public function testProcessFormWithAllFieldsValid(): void
    {
        $formData = [
            'date_from' => '2025-01-01',
            'date_to' => '2025-01-31',
            'facility' => '1',
            'provider' => '5',
            'patient_id' => '100',
            'details' => '1',
            'signed_only' => '1',
        ];

        $result = $this->handler->processForm($formData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('date_from', $result);
        $this->assertArrayHasKey('date_to', $result);
        $this->assertArrayHasKey('facility', $result);
        $this->assertArrayHasKey('provider', $result);
        $this->assertArrayHasKey('patient_id', $result);
        $this->assertArrayHasKey('details', $result);
        $this->assertArrayHasKey('signed_only', $result);

        $this->assertSame('2025-01-01', $result['date_from']);
        $this->assertSame('2025-01-31', $result['date_to']);
        $this->assertSame(1, $result['facility']);
        $this->assertSame(5, $result['provider']);
        $this->assertSame(100, $result['patient_id']);
        $this->assertSame('1', $result['details']);
        $this->assertTrue($result['signed_only']);
    }

    #[Test]
    public function testProcessFormWithMissingOptionalFields(): void
    {
        $formData = [
            'date_from' => '2025-01-01',
            'date_to' => '2025-01-31',
        ];

        $result = $this->handler->processForm($formData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('date_from', $result);
        $this->assertArrayHasKey('date_to', $result);
        $this->assertArrayNotHasKey('facility', $result);
        $this->assertArrayNotHasKey('provider', $result);
        $this->assertArrayNotHasKey('patient_id', $result);
    }

    #[Test]
    public function testProcessFormWithAllValuesForFacilityAndProvider(): void
    {
        $formData = [
            'date_from' => '2025-01-01',
            'date_to' => '2025-01-31',
            'facility' => 'all',
            'provider' => 'all',
        ];

        $result = $this->handler->processForm($formData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('facility', $result);
        $this->assertArrayHasKey('provider', $result);
        $this->assertSame('all', $result['facility']);
        $this->assertSame('all', $result['provider']);
    }

    #[Test]
    public function testProcessFormFiltersOutInvalidData(): void
    {
        $formData = [
            'date_from' => '2025-01-01',
            'date_to' => '2025-01-31',
            'facility' => '1;DROP TABLE form_encounter',
            'provider' => '-999',
            'patient_id' => 'abc123',
        ];

        $result = $this->handler->processForm($formData);

        $this->assertIsArray($result);
        // Non-numeric values should not be set or should be rejected
        $this->assertArrayNotHasKey('facility', $result);
        $this->assertArrayNotHasKey('patient_id', $result);
    }

    #[Test]
    public function testDateValidationRespectsDisplayFormatZero(): void
    {
        $GLOBALS['date_display_format'] = 0; // YYYY-MM-DD

        $formData = [
            'date_from' => '2025-01-15',
            'date_to' => '2025-01-31',
        ];

        $result = $this->handler->processForm($formData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('date_from', $result);
        $this->assertArrayHasKey('date_to', $result);
        $this->assertSame('2025-01-15', $result['date_from']);
        $this->assertSame('2025-01-31', $result['date_to']);
    }

    #[Test]
    public function testDateValidationRespectsDisplayFormatOne(): void
    {
        $GLOBALS['date_display_format'] = 1; // MM/DD/YYYY

        $formData = [
            'date_from' => '01/15/2025',
            'date_to' => '01/31/2025',
        ];

        $result = $this->handler->processForm($formData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('date_from', $result);
        $this->assertArrayHasKey('date_to', $result);
    }

    #[Test]
    public function testDateValidationRespectsDisplayFormatTwo(): void
    {
        $GLOBALS['date_display_format'] = 2; // DD/MM/YYYY

        $formData = [
            'date_from' => '15/01/2025',
            'date_to' => '31/01/2025',
        ];

        $result = $this->handler->processForm($formData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('date_from', $result);
        $this->assertArrayHasKey('date_to', $result);
    }

    #[Test]
    public function testDateValidationRejectsInvalidDate(): void
    {
        $GLOBALS['date_display_format'] = 0; // YYYY-MM-DD

        $formData = [
            'date_from' => '2025-13-45', // Invalid month and day
            'date_to' => '2025-01-31',
        ];

        $result = $this->handler->processForm($formData);

        $this->assertIsArray($result);
        // Invalid date should not be included
        $this->assertArrayNotHasKey('date_from', $result);
        $this->assertArrayHasKey('date_to', $result);
    }

    #[Test]
    public function testNumericValidationForIds(): void
    {
        $formData = [
            'date_from' => '2025-01-01',
            'date_to' => '2025-01-31',
            'facility' => '123',
            'provider' => '456',
            'patient_id' => '789',
        ];

        $result = $this->handler->processForm($formData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('facility', $result);
        $this->assertArrayHasKey('provider', $result);
        $this->assertArrayHasKey('patient_id', $result);
        $this->assertSame(123, $result['facility']);
        $this->assertSame(456, $result['provider']);
        $this->assertSame(789, $result['patient_id']);
    }

    #[Test]
    public function testDefaultBehaviorWhenNoDataSubmitted(): void
    {
        $formData = [];

        $result = $this->handler->processForm($formData);

        $this->assertIsArray($result);
        // When no form data is submitted, should have details default to '1'
        $this->assertArrayHasKey('details', $result);
        $this->assertSame('1', $result['details']);
    }

    #[Test]
    public function testSignedOnlyFilterSetCorrectly(): void
    {
        $formDataWithSigned = [
            'date_from' => '2025-01-01',
            'date_to' => '2025-01-31',
            'signed_only' => '1',
        ];

        $result = $this->handler->processForm($formDataWithSigned);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('signed_only', $result);
        $this->assertTrue($result['signed_only']);

        // Test without signed_only
        $formDataWithoutSigned = [
            'date_from' => '2025-01-01',
            'date_to' => '2025-01-31',
        ];

        $result2 = $this->handler->processForm($formDataWithoutSigned);

        $this->assertIsArray($result2);
        $this->assertArrayNotHasKey('signed_only', $result2);
    }

    #[Test]
    public function testDetailsCheckboxBehavior(): void
    {
        // When details checkbox is explicitly set
        $formData1 = [
            'date_from' => '2025-01-01',
            'date_to' => '2025-01-31',
            'details' => '1',
        ];

        $result1 = $this->handler->processForm($formData1);
        $this->assertArrayHasKey('details', $result1);
        $this->assertSame('1', $result1['details']);

        // When details is not in form data but dates are present
        $formData2 = [
            'date_from' => '2025-01-01',
            'date_to' => '2025-01-31',
        ];

        $result2 = $this->handler->processForm($formData2);
        // Should not have details when dates are submitted (unchecked checkbox)
        $this->assertArrayNotHasKey('details', $result2);
    }
}
