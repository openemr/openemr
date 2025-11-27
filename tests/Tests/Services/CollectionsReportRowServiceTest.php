<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;

/**
 * CollectionsReportRowServiceTest - TDD tests for invoice row data preparation (Phase 2)
 *
 * Tests the service that prepares individual invoice row data for rendering.
 * This includes all patient demographics, financial fields, and conditional columns.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class CollectionsReportRowServiceTest extends TestCase
{
    /**
     * Test 1: Service prepares basic patient demographic fields
     */
    public function testPreparesBasicPatientDemographicFields()
    {
        // Arrange
        $rowData = [
            'pid' => '123',
            'encounter' => '456',
            'fname' => 'John',
            'lname' => 'Doe',
            'mname' => 'Q',
            'ss' => '123-45-6789',
            'DOB' => '1980-05-15',
            'pubpid' => 'PT-123',
            'phone_home' => '555-1234',
            'city' => 'Boston',
        ];

        $filterConfig = [
            'form_cb_ssn' => true,
            'form_cb_dob' => true,
            'form_cb_pubpid' => true,
            'form_cb_phone' => true,
            'form_cb_city' => true,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert
        $this->assertArrayHasKey('patient_name', $preparedRow);
        $this->assertEquals('Doe, John Q', $preparedRow['patient_name']);
        $this->assertEquals('123-45-6789', $preparedRow['ssn']);
        $this->assertEquals('05/15/1980', $preparedRow['dob']);
        $this->assertEquals('PT-123', $preparedRow['pubpid']);
        $this->assertEquals('555-1234', $preparedRow['phone']);
        $this->assertEquals('Boston', $preparedRow['city']);
    }

    /**
     * Test 2: Service prepares financial fields with correct formatting
     */
    public function testPreparesFinancialFieldsWithCorrectFormatting()
    {
        // Arrange
        $rowData = [
            'charges' => 1500.50,
            'adjustments' => -200.25,
            'paid' => 800.00,
        ];

        $filterConfig = [];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert
        $this->assertArrayHasKey('charges', $preparedRow);
        $this->assertArrayHasKey('adjustments', $preparedRow);
        $this->assertArrayHasKey('paid', $preparedRow);
        $this->assertArrayHasKey('balance', $preparedRow);
        
        // Balance should be charges + adjustments - paid
        $expectedBalance = 1500.50 + (-200.25) - 800.00;
        $this->assertEquals($expectedBalance, $preparedRow['balance']);
        $this->assertEquals(500.25, $preparedRow['balance']);
    }

    /**
     * Test 3: Service formats money values for display
     */
    public function testFormatsMoneyValuesForDisplay()
    {
        // Arrange
        $rowData = [
            'charges' => 1500.50,
            'adjustments' => -200.25,
            'paid' => 800.00,
        ];

        $filterConfig = [];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert - formatted values should have proper decimal places
        $this->assertEquals('1,500.50', $preparedRow['charges_formatted']);
        $this->assertEquals('-200.25', $preparedRow['adjustments_formatted']);
        $this->assertEquals('800.00', $preparedRow['paid_formatted']);
        $this->assertEquals('500.25', $preparedRow['balance_formatted']);
    }

    /**
     * Test 4: Service prepares invoice and encounter data
     */
    public function testPreparesInvoiceAndEncounterData()
    {
        // Arrange
        $rowData = [
            'id' => '789',
            'pid' => '123',
            'encounter' => '456',
            'invnumber' => '123.456',
            'irnumber' => 'INV-2025-001',
            'date' => '2025-01-15',
        ];

        $filterConfig = [];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert
        $this->assertEquals('789', $preparedRow['id']);
        $this->assertEquals('123', $preparedRow['pid']);
        $this->assertEquals('456', $preparedRow['encounter']);
        $this->assertEquals('INV-2025-001', $preparedRow['invoice_number']);
        $this->assertEquals('01/15/2025', $preparedRow['service_date']);
    }

    /**
     * Test 5: Service uses invoice reference number over internal number
     */
    public function testUsesInvoiceReferenceNumberOverInternalNumber()
    {
        // Arrange
        $rowData = [
            'invnumber' => '123.456',
            'irnumber' => 'INV-2025-001',
        ];

        $filterConfig = [];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert
        $this->assertEquals('INV-2025-001', $preparedRow['invoice_number']);
        
        // Test with empty reference number
        $rowData['irnumber'] = '';
        $preparedRow2 = $service->prepareRow($rowData, $filterConfig);
        $this->assertEquals('123.456', $preparedRow2['invoice_number']);
    }

    /**
     * Test 6: Service prepares insurance and policy information
     */
    public function testPreparesInsuranceAndPolicyInformation()
    {
        // Arrange
        $rowData = [
            'ins1' => 'Blue Cross Blue Shield',
            'policy' => 'BC-12345',
            'groupnumber' => 'GRP-789',
        ];

        $filterConfig = [
            'form_cb_ins1' => true,
            'form_cb_policy' => true,
            'form_cb_group_number' => true,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert
        $this->assertEquals('Blue Cross Blue Shield', $preparedRow['primary_insurance']);
        $this->assertEquals('BC-12345', $preparedRow['policy']);
        $this->assertEquals('GRP-789', $preparedRow['group_number']);
    }

    /**
     * Test 7: Service prepares provider and referrer information
     */
    public function testPreparesProviderAndReferrerInformation()
    {
        // Arrange
        $rowData = [
            'provider_id' => 'Dr. Smith, Jane',
            'referrer' => 'Dr. Johnson, Bob',
        ];

        $filterConfig = [
            'form_provider' => '5',
            'form_cb_referrer' => true,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert
        $this->assertEquals('Dr. Smith, Jane', $preparedRow['provider']);
        $this->assertEquals('Dr. Johnson, Bob', $preparedRow['referrer']);
    }

    /**
     * Test 8: Service calculates aging days correctly
     */
    public function testCalculatesAgingDaysCorrectly()
    {
        // Arrange
        $rowData = [
            'aging_date' => date('Y-m-d', strtotime('-45 days')),
            'inactive_days' => 45,
        ];

        $filterConfig = [
            'form_cb_idays' => true,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert
        $this->assertEquals(45, $preparedRow['aging_days']);
        $this->assertArrayHasKey('activity_date', $preparedRow);
    }

    /**
     * Test 9: Service determines correct aging bucket for balance
     */
    public function testDeterminesCorrectAgingBucketForBalance()
    {
        // Arrange - 45 days old, with 3 buckets at 30-day intervals
        $rowData = [
            'charges' => 1000.00,
            'adjustments' => 0,
            'paid' => 0,
            'date' => date('Y-m-d', strtotime('-45 days')), // Service date 45 days ago
        ];

        $filterConfig = [
            'form_age_cols' => 3,
            'form_age_inc' => 30,
            'form_ageby' => 'Service Date',
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert - Should be in bucket 1 (30-59 days)
        $this->assertArrayHasKey('aging_buckets', $preparedRow);
        $this->assertIsArray($preparedRow['aging_buckets']);
        $this->assertEquals(3, count($preparedRow['aging_buckets']));
        
        // Bucket 0 (0-29): 0
        $this->assertEquals(0, $preparedRow['aging_buckets'][0]);
        // Bucket 1 (30-59): 1000.00
        $this->assertEquals(1000.00, $preparedRow['aging_buckets'][1]);
        // Bucket 2 (60+): 0
        $this->assertEquals(0, $preparedRow['aging_buckets'][2]);
    }

    /**
     * Test 10: Service determines "In Collections" status
     */
    public function testDeterminesInCollectionsStatus()
    {
        // Arrange - Test with in_collection flag
        $rowData1 = [
            'in_collection' => 1,
            'billnote' => '',
        ];

        $rowData2 = [
            'in_collection' => 0,
            'billnote' => 'Patient statement: IN COLLECTIONS',
        ];

        $rowData3 = [
            'in_collection' => 0,
            'billnote' => 'Normal billing note',
        ];

        $filterConfig = [];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow1 = $service->prepareRow($rowData1, $filterConfig);
        $preparedRow2 = $service->prepareRow($rowData2, $filterConfig);
        $preparedRow3 = $service->prepareRow($rowData3, $filterConfig);

        // Assert
        $this->assertTrue($preparedRow1['is_in_collections']);
        $this->assertTrue($preparedRow2['is_in_collections']);
        $this->assertFalse($preparedRow3['is_in_collections']);
    }

    /**
     * Test 11: Service prepares duncount (statement count) correctly
     */
    public function testPreparesDuncountCorrectly()
    {
        // Arrange
        $rowData = [
            'duncount' => 3,
        ];

        $filterConfig = [];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert
        $this->assertEquals(3, $preparedRow['statement_count']);
    }

    /**
     * Test 12: Service prepares billing error messages
     */
    public function testPreparesBillingErrorMessages()
    {
        // Arrange
        $rowData = [
            'billing_errmsg' => 'Ins1 seems done',
        ];

        $filterConfig = [
            'form_cb_err' => true,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert
        $this->assertEquals('Ins1 seems done', $preparedRow['billing_error']);
    }

    /**
     * Test 13: Service handles first row vs subsequent rows for patient
     */
    public function testHandlesFirstRowVsSubsequentRowsForPatient()
    {
        // Arrange
        $rowData = [
            'pid' => '123',
            'encounter' => '456',
        ];

        $filterConfig = [];

        // Act - First row for patient
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow1 = $service->prepareRow($rowData, $filterConfig, true); // isFirstRow = true

        // Act - Subsequent row for same patient
        $preparedRow2 = $service->prepareRow($rowData, $filterConfig, false); // isFirstRow = false

        // Assert
        $this->assertTrue($preparedRow1['is_first_row']);
        $this->assertFalse($preparedRow2['is_first_row']);
    }

    /**
     * Test 14: Service handles missing optional fields gracefully
     */
    public function testHandlesMissingOptionalFieldsGracefully()
    {
        // Arrange - Minimal data
        $rowData = [
            'pid' => '123',
            'encounter' => '456',
            'fname' => 'John',
            'lname' => 'Doe',
            'charges' => 100.00,
            'paid' => 0,
        ];

        $filterConfig = [];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert - Should not throw errors, should have defaults
        $this->assertIsArray($preparedRow);
        $this->assertArrayHasKey('patient_name', $preparedRow);
        $this->assertEquals('Doe, John', $preparedRow['patient_name']); // No middle initial
    }

    /**
     * Test 15: Service prepares all conditional visibility flags
     */
    public function testPreparesAllConditionalVisibilityFlags()
    {
        // Arrange
        $rowData = [];
        $filterConfig = [
            'is_ins_summary' => false,
            'is_due_ins' => true,
            'form_cb_ssn' => true,
            'form_cb_dob' => false,
            'form_cb_adate' => true,
            'form_cb_idays' => false,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\RowService();
        $preparedRow = $service->prepareRow($rowData, $filterConfig);

        // Assert - Visibility flags should be set
        $this->assertArrayHasKey('show_ssn', $preparedRow);
        $this->assertTrue($preparedRow['show_ssn']);
        $this->assertFalse($preparedRow['show_dob']);
        $this->assertTrue($preparedRow['show_activity_date']);
        $this->assertFalse($preparedRow['show_aging_days']);
    }
}
