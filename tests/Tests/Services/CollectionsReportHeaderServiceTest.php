<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;

/**
 * CollectionsReportHeaderServiceTest - TDD tests for table header generation (Phase 1)
 *
 * Tests the service that prepares header configuration data for the Collections Report
 * results table. This is Phase 1 of the TDD migration plan.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class CollectionsReportHeaderServiceTest extends TestCase
{
    /**
     * Test 1: Service generates correct header columns for default configuration
     */
    public function testGeneratesDefaultHeaderColumns()
    {
        // Arrange
        $filterConfig = [
            'is_due_ins' => false,
            'is_ins_summary' => false,
            'form_cb_ssn' => false,
            'form_cb_dob' => true,
            'form_cb_pubpid' => false,
            'form_cb_policy' => true,
            'form_cb_group_number' => false,
            'form_cb_phone' => true,
            'form_cb_city' => false,
            'form_cb_ins1' => true,
            'form_cb_referrer' => false,
            'form_cb_adate' => false,
            'form_cb_idays' => true,
            'form_cb_err' => false,
            'form_provider' => null,
            'form_payer_id' => null,
            'form_age_cols' => 0, // No aging columns
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\HeaderService();
        $headers = $service->generateHeaders($filterConfig);

        // Assert
        $this->assertIsArray($headers);
        $this->assertNotEmpty($headers);
        
        // Should have: Name, DOB, Policy, Phone, Primary Ins, Invoice, Svc Date, Charge, Adjust, Paid, Balance, Aging Days, Prv, Sel
        $expectedColumns = ['Name', 'DOB', 'Policy', 'Phone', 'Primary Ins', 'Invoice', 'Svc Date', 'Charge', 'Adjust', 'Paid', 'Balance', 'Aging Days', 'Prv', 'Sel'];
        
        $this->assertCount(14, $headers, 'Should have 14 columns for default config');
        
        // Verify specific columns exist
        $columnLabels = array_column($headers, 'label');
        $this->assertContains('Name', $columnLabels);
        $this->assertContains('DOB', $columnLabels);
        $this->assertContains('Policy', $columnLabels);
        $this->assertContains('Balance', $columnLabels);
    }

    /**
     * Test 2: Insurance column appears when is_due_ins is true
     */
    public function testInsuranceColumnAppearsWhenDueIns()
    {
        // Arrange
        $filterConfig = [
            'is_due_ins' => true,
            'is_ins_summary' => false,
            'form_cb_ssn' => false,
            'form_cb_dob' => false,
            'form_cb_pubpid' => false,
            'form_cb_policy' => false,
            'form_cb_group_number' => false,
            'form_cb_phone' => false,
            'form_cb_city' => false,
            'form_cb_ins1' => false,
            'form_cb_referrer' => false,
            'form_cb_adate' => false,
            'form_cb_idays' => false,
            'form_cb_err' => false,
            'form_provider' => null,
            'form_payer_id' => null,
            'form_age_cols' => 0,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\HeaderService();
        $headers = $service->generateHeaders($filterConfig);

        // Assert
        $columnLabels = array_column($headers, 'label');
        $this->assertContains('Insurance', $columnLabels, 'Insurance column should appear when is_due_ins is true');
        
        // Insurance should be first column
        $this->assertEquals('Insurance', $headers[0]['label']);
    }

    /**
     * Test 3: Optional columns show/hide based on checkbox filters
     */
    public function testOptionalColumnsShowHideCorrectly()
    {
        // Arrange - All optional columns enabled
        $filterConfig = [
            'is_due_ins' => false,
            'is_ins_summary' => false,
            'form_cb_ssn' => true,
            'form_cb_dob' => true,
            'form_cb_pubpid' => true,
            'form_cb_policy' => true,
            'form_cb_group_number' => true,
            'form_cb_phone' => true,
            'form_cb_city' => true,
            'form_cb_ins1' => true,
            'form_cb_referrer' => true,
            'form_cb_adate' => true,
            'form_cb_idays' => true,
            'form_cb_err' => true,
            'form_provider' => null,
            'form_payer_id' => null,
            'form_age_cols' => 0,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\HeaderService();
        $headers = $service->generateHeaders($filterConfig);

        // Assert - All optional columns should be present
        $columnLabels = array_column($headers, 'label');
        $this->assertContains('SSN', $columnLabels);
        $this->assertContains('DOB', $columnLabels);
        $this->assertContains('ID', $columnLabels); // pubpid
        $this->assertContains('Policy', $columnLabels);
        $this->assertContains('Group Number', $columnLabels);
        $this->assertContains('Phone', $columnLabels);
        $this->assertContains('City', $columnLabels);
        $this->assertContains('Primary Ins', $columnLabels);
        $this->assertContains('Referrer', $columnLabels);
        $this->assertContains('Act Date', $columnLabels);
        $this->assertContains('Aging Days', $columnLabels);
        $this->assertContains('Error', $columnLabels);
    }

    /**
     * Test 4: Aging columns generate with correct labels
     */
    public function testAgingColumnsGenerateWithCorrectLabels()
    {
        // Arrange - 3 aging columns at 30-day intervals
        $filterConfig = [
            'is_due_ins' => false,
            'is_ins_summary' => false,
            'form_cb_ssn' => false,
            'form_cb_dob' => false,
            'form_cb_pubpid' => false,
            'form_cb_policy' => false,
            'form_cb_group_number' => false,
            'form_cb_phone' => false,
            'form_cb_city' => false,
            'form_cb_ins1' => false,
            'form_cb_referrer' => false,
            'form_cb_adate' => false,
            'form_cb_idays' => false,
            'form_cb_err' => false,
            'form_provider' => null,
            'form_payer_id' => null,
            'form_age_cols' => 3,
            'form_age_inc' => 30,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\HeaderService();
        $headers = $service->generateHeaders($filterConfig);

        // Assert - Should have aging column headers: "0-29", "30-59", "60+"
        $columnLabels = array_column($headers, 'label');
        $this->assertContains('0-29', $columnLabels, 'First aging column should be 0-29');
        $this->assertContains('30-59', $columnLabels, 'Second aging column should be 30-59');
        $this->assertContains('60+', $columnLabels, 'Third aging column should be 60+');
        
        // Balance column should NOT exist when aging columns are used
        $this->assertNotContains('Balance', $columnLabels, 'Balance column should not exist when aging columns are enabled');
    }

    /**
     * Test 5: Different aging configurations generate correctly
     */
    public function testDifferentAgingConfigurationsGenerateCorrectly()
    {
        // Arrange - 4 aging columns at 45-day intervals
        $filterConfig = [
            'is_due_ins' => false,
            'is_ins_summary' => false,
            'form_cb_ssn' => false,
            'form_cb_dob' => false,
            'form_cb_pubpid' => false,
            'form_cb_policy' => false,
            'form_cb_group_number' => false,
            'form_cb_phone' => false,
            'form_cb_city' => false,
            'form_cb_ins1' => false,
            'form_cb_referrer' => false,
            'form_cb_adate' => false,
            'form_cb_idays' => false,
            'form_cb_err' => false,
            'form_provider' => null,
            'form_payer_id' => null,
            'form_age_cols' => 4,
            'form_age_inc' => 45,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\HeaderService();
        $headers = $service->generateHeaders($filterConfig);

        // Assert - Should have: "0-44", "45-89", "90-134", "135+"
        $columnLabels = array_column($headers, 'label');
        $this->assertContains('0-44', $columnLabels);
        $this->assertContains('45-89', $columnLabels);
        $this->assertContains('90-134', $columnLabels);
        $this->assertContains('135+', $columnLabels);
    }

    /**
     * Test 6: Headers have correct CSS classes for Bootstrap
     */
    public function testHeadersHaveCorrectCssClasses()
    {
        // Arrange
        $filterConfig = [
            'is_due_ins' => false,
            'is_ins_summary' => false,
            'form_cb_ssn' => false,
            'form_cb_dob' => true,
            'form_cb_pubpid' => false,
            'form_cb_policy' => false,
            'form_cb_group_number' => false,
            'form_cb_phone' => false,
            'form_cb_city' => false,
            'form_cb_ins1' => false,
            'form_cb_referrer' => false,
            'form_cb_adate' => false,
            'form_cb_idays' => false,
            'form_cb_err' => false,
            'form_provider' => null,
            'form_payer_id' => null,
            'form_age_cols' => 0,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\HeaderService();
        $headers = $service->generateHeaders($filterConfig);

        // Assert - Each header should have css_classes array
        foreach ($headers as $header) {
            $this->assertArrayHasKey('css_classes', $header);
            $this->assertIsArray($header['css_classes']);
        }
        
        // Financial columns should have text-right alignment
        $chargeColumn = array_filter($headers, fn($h) => $h['label'] === 'Charge');
        $this->assertNotEmpty($chargeColumn);
        $chargeColumn = reset($chargeColumn);
        $this->assertContains('text-right', $chargeColumn['css_classes']);
    }

    /**
     * Test 7: Insurance summary mode hides patient-specific columns
     */
    public function testInsuranceSummaryModeHidesPatientColumns()
    {
        // Arrange
        $filterConfig = [
            'is_due_ins' => false,
            'is_ins_summary' => true,
            'form_cb_ssn' => true, // Should be ignored in insurance summary mode
            'form_cb_dob' => true,
            'form_cb_pubpid' => true,
            'form_cb_policy' => true,
            'form_cb_group_number' => true,
            'form_cb_phone' => true,
            'form_cb_city' => true,
            'form_cb_ins1' => true,
            'form_cb_referrer' => true,
            'form_cb_adate' => true,
            'form_cb_idays' => true,
            'form_cb_err' => true,
            'form_provider' => null,
            'form_payer_id' => null,
            'form_age_cols' => 0,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\HeaderService();
        $headers = $service->generateHeaders($filterConfig);

        // Assert - Patient-specific columns should NOT appear
        $columnLabels = array_column($headers, 'label');
        $this->assertNotContains('Name', $columnLabels, 'Name column should not appear in insurance summary');
        $this->assertNotContains('Invoice', $columnLabels, 'Invoice column should not appear in insurance summary');
        $this->assertNotContains('Svc Date', $columnLabels, 'Service Date column should not appear in insurance summary');
        $this->assertNotContains('Prv', $columnLabels, 'Prv column should not appear in insurance summary');
        $this->assertNotContains('Sel', $columnLabels, 'Sel column should not appear in insurance summary');
    }

    /**
     * Test 8: Provider column appears when provider filter is set
     */
    public function testProviderColumnAppearsWhenProviderFilterSet()
    {
        // Arrange
        $filterConfig = [
            'is_due_ins' => false,
            'is_ins_summary' => false,
            'form_cb_ssn' => false,
            'form_cb_dob' => false,
            'form_cb_pubpid' => false,
            'form_cb_policy' => false,
            'form_cb_group_number' => false,
            'form_cb_phone' => false,
            'form_cb_city' => false,
            'form_cb_ins1' => false,
            'form_cb_referrer' => false,
            'form_cb_adate' => false,
            'form_cb_idays' => false,
            'form_cb_err' => false,
            'form_provider' => '5', // Provider ID is set
            'form_payer_id' => null,
            'form_age_cols' => 0,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\HeaderService();
        $headers = $service->generateHeaders($filterConfig);

        // Assert
        $columnLabels = array_column($headers, 'label');
        $this->assertContains('Provider', $columnLabels, 'Provider column should appear when provider filter is set');
    }

    /**
     * Test 9: Column order is correct
     */
    public function testColumnOrderIsCorrect()
    {
        // Arrange - Enable all columns to verify order
        $filterConfig = [
            'is_due_ins' => true,
            'is_ins_summary' => false,
            'form_cb_ssn' => true,
            'form_cb_dob' => true,
            'form_cb_pubpid' => true,
            'form_cb_policy' => true,
            'form_cb_group_number' => true,
            'form_cb_phone' => true,
            'form_cb_city' => true,
            'form_cb_ins1' => true,
            'form_cb_referrer' => true,
            'form_cb_adate' => true,
            'form_cb_idays' => true,
            'form_cb_err' => true,
            'form_provider' => '5',
            'form_payer_id' => '101',
            'form_age_cols' => 0,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\HeaderService();
        $headers = $service->generateHeaders($filterConfig);

        // Assert - Verify expected order (based on lines 780-854 of original PHP)
        $columnLabels = array_column($headers, 'label');
        
        // Insurance should be first (when is_due_ins)
        $this->assertEquals('Insurance', $columnLabels[0]);
        
        // Name should come after insurance
        $this->assertEquals('Name', $columnLabels[1]);
        
        // Financial columns (Charge, Adjust, Paid) should come after demographic columns
        $chargeIndex = array_search('Charge', $columnLabels);
        $nameIndex = array_search('Name', $columnLabels);
        $this->assertGreaterThan($nameIndex, $chargeIndex, 'Charge should come after Name');
    }

    /**
     * Test 10: Header data includes alignment information
     */
    public function testHeaderDataIncludesAlignmentInformation()
    {
        // Arrange
        $filterConfig = [
            'is_due_ins' => false,
            'is_ins_summary' => false,
            'form_cb_ssn' => false,
            'form_cb_dob' => true,
            'form_cb_pubpid' => false,
            'form_cb_policy' => false,
            'form_cb_group_number' => false,
            'form_cb_phone' => false,
            'form_cb_city' => false,
            'form_cb_ins1' => false,
            'form_cb_referrer' => false,
            'form_cb_adate' => false,
            'form_cb_idays' => false,
            'form_cb_err' => false,
            'form_provider' => null,
            'form_payer_id' => null,
            'form_age_cols' => 0,
        ];

        // Act
        $service = new \OpenEMR\Reports\Collections\Services\HeaderService();
        $headers = $service->generateHeaders($filterConfig);

        // Assert - Financial columns should have right alignment
        foreach ($headers as $header) {
            $this->assertArrayHasKey('align', $header);
            
            if (in_array($header['label'], ['Charge', 'Adjust', 'Paid', 'Balance', 'Aging Days'])) {
                $this->assertEquals('right', $header['align'], "{$header['label']} should be right-aligned");
            }
        }
    }
}
