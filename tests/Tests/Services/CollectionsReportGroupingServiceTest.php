<?php

/**
 * Collections Report Grouping Service Test
 *
 * Tests patient/insurance grouping logic for the Collections Report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\Collections\Services\GroupingService;

class CollectionsReportGroupingServiceTest extends TestCase
{
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GroupingService();
    }

    /**
     * Test groups single patient with multiple invoices correctly
     */
    public function testGroupsSinglePatientWithMultipleInvoices(): void
    {
        $rows = [
            ['pid' => 100, 'invnumber' => '100.1', 'charges' => 500, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 100, 'invnumber' => '100.2', 'charges' => 300, 'adjustments' => -50, 'paid' => 100],
            ['pid' => 100, 'invnumber' => '100.3', 'charges' => 200, 'adjustments' => 0, 'paid' => 0],
        ];

        $result = $this->service->groupByPatient($rows, []);

        $this->assertCount(1, $result, 'Should have 1 patient group');
        $this->assertCount(3, $result[0]['invoices'], 'Patient should have 3 invoices');
        $this->assertEquals(100, $result[0]['pid']);
    }

    /**
     * Test groups multiple patients correctly
     */
    public function testGroupsMultiplePatientsCorrectly(): void
    {
        $rows = [
            ['pid' => 100, 'invnumber' => '100.1', 'charges' => 500, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 100, 'invnumber' => '100.2', 'charges' => 300, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 200, 'invnumber' => '200.1', 'charges' => 750, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 300, 'invnumber' => '300.1', 'charges' => 1000, 'adjustments' => 0, 'paid' => 500],
        ];

        $result = $this->service->groupByPatient($rows, []);

        $this->assertCount(3, $result, 'Should have 3 patient groups');
        $this->assertEquals(100, $result[0]['pid']);
        $this->assertEquals(200, $result[1]['pid']);
        $this->assertEquals(300, $result[2]['pid']);
        $this->assertCount(2, $result[0]['invoices']);
        $this->assertCount(1, $result[1]['invoices']);
        $this->assertCount(1, $result[2]['invoices']);
    }

    /**
     * Test calculates patient totals correctly
     */
    public function testCalculatesPatientTotalsCorrectly(): void
    {
        $rows = [
            ['pid' => 100, 'invnumber' => '100.1', 'charges' => 500, 'adjustments' => -50, 'paid' => 200],
            ['pid' => 100, 'invnumber' => '100.2', 'charges' => 300, 'adjustments' => -30, 'paid' => 100],
        ];

        $result = $this->service->groupByPatient($rows, []);

        $totals = $result[0]['totals'];
        $this->assertEquals(800, $totals['charges'], 'Total charges should be 800');
        $this->assertEquals(-80, $totals['adjustments'], 'Total adjustments should be -80');
        $this->assertEquals(300, $totals['paid'], 'Total paid should be 300');
        $this->assertEquals(420, $totals['balance'], 'Total balance should be 420 (800 - 80 - 300)');
    }

    /**
     * Test calculates rowspan for patient name cell
     */
    public function testCalculatesRowspanForPatientNameCell(): void
    {
        $rows = [
            ['pid' => 100, 'invnumber' => '100.1', 'charges' => 500, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 100, 'invnumber' => '100.2', 'charges' => 300, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 100, 'invnumber' => '100.3', 'charges' => 200, 'adjustments' => 0, 'paid' => 0],
        ];

        $result = $this->service->groupByPatient($rows, []);

        $this->assertEquals(3, $result[0]['rowspan'], 'Rowspan should equal number of invoices');
        $this->assertTrue($result[0]['invoices'][0]['is_first_row']);
        $this->assertFalse($result[0]['invoices'][1]['is_first_row']);
        $this->assertFalse($result[0]['invoices'][2]['is_first_row']);
    }

    /**
     * Test groups by insurance in insurance summary mode
     */
    public function testGroupsByInsuranceInInsuranceSummaryMode(): void
    {
        $rows = [
            ['pid' => 100, 'ins1' => 'Blue Cross', 'invnumber' => '100.1', 'charges' => 500, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 200, 'ins1' => 'Blue Cross', 'invnumber' => '200.1', 'charges' => 300, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 300, 'ins1' => 'Aetna', 'invnumber' => '300.1', 'charges' => 750, 'adjustments' => 0, 'paid' => 0],
        ];

        $config = ['is_insurance_summary' => true];
        $result = $this->service->groupByInsurance($rows, $config);

        $this->assertCount(2, $result, 'Should have 2 insurance groups');
        $this->assertEquals('Blue Cross', $result[0]['insurance_name']);
        $this->assertEquals('Aetna', $result[1]['insurance_name']);
        $this->assertEquals(800, $result[0]['totals']['charges'], 'Blue Cross total charges');
        $this->assertEquals(750, $result[1]['totals']['charges'], 'Aetna total charges');
    }

    /**
     * Test calculates aging totals per patient
     */
    public function testCalculatesAgingTotalsPerPatient(): void
    {
        $rows = [
            ['pid' => 100, 'invnumber' => '100.1', 'charges' => 500, 'adjustments' => 0, 'paid' => 0, 
             'date' => '2024-10-01', 'aging_date' => '2024-10-01'],
            ['pid' => 100, 'invnumber' => '100.2', 'charges' => 300, 'adjustments' => 0, 'paid' => 0,
             'date' => '2024-09-01', 'aging_date' => '2024-09-01'],
        ];

        $config = ['form_age_cols' => 3, 'form_age_inc' => 30];
        $result = $this->service->groupByPatient($rows, $config);

        $this->assertArrayHasKey('aging_buckets', $result[0]['totals']);
        $this->assertCount(3, $result[0]['totals']['aging_buckets']);
        $this->assertIsArray($result[0]['totals']['aging_buckets']);
    }

    /**
     * Test marks first row correctly for each patient
     */
    public function testMarksFirstRowCorrectlyForEachPatient(): void
    {
        $rows = [
            ['pid' => 100, 'invnumber' => '100.1', 'charges' => 500, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 100, 'invnumber' => '100.2', 'charges' => 300, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 200, 'invnumber' => '200.1', 'charges' => 750, 'adjustments' => 0, 'paid' => 0],
        ];

        $result = $this->service->groupByPatient($rows, []);

        // Patient 100's first invoice
        $this->assertTrue($result[0]['invoices'][0]['is_first_row']);
        $this->assertFalse($result[0]['invoices'][1]['is_first_row']);
        
        // Patient 200's first invoice
        $this->assertTrue($result[1]['invoices'][0]['is_first_row']);
    }

    /**
     * Test handles empty input gracefully
     */
    public function testHandlesEmptyInputGracefully(): void
    {
        $result = $this->service->groupByPatient([], []);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /**
     * Test preserves all row data in grouped results
     */
    public function testPreservesAllRowDataInGroupedResults(): void
    {
        $rows = [
            [
                'pid' => 100,
                'invnumber' => '100.1',
                'charges' => 500,
                'adjustments' => 0,
                'paid' => 0,
                'lname' => 'Smith',
                'fname' => 'John',
                'ss' => '123-45-6789',
            ],
        ];

        $result = $this->service->groupByPatient($rows, []);

        $this->assertEquals('Smith', $result[0]['invoices'][0]['lname']);
        $this->assertEquals('John', $result[0]['invoices'][0]['fname']);
        $this->assertEquals('123-45-6789', $result[0]['invoices'][0]['ss']);
    }

    /**
     * Test assigns zebra stripe colors (alternating row colors)
     */
    public function testAssignsZebraStripeColors(): void
    {
        $rows = [
            ['pid' => 100, 'invnumber' => '100.1', 'charges' => 500, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 200, 'invnumber' => '200.1', 'charges' => 300, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 300, 'invnumber' => '300.1', 'charges' => 750, 'adjustments' => 0, 'paid' => 0],
        ];

        $result = $this->service->groupByPatient($rows, []);

        $this->assertEquals(0, $result[0]['row_index'] % 2, 'First patient should have even index');
        $this->assertEquals(1, $result[1]['row_index'] % 2, 'Second patient should have odd index');
        $this->assertEquals(0, $result[2]['row_index'] % 2, 'Third patient should have even index');
    }

    /**
     * Test counts invoices per patient
     */
    public function testCountsInvoicesPerPatient(): void
    {
        $rows = [
            ['pid' => 100, 'invnumber' => '100.1', 'charges' => 500, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 100, 'invnumber' => '100.2', 'charges' => 300, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 100, 'invnumber' => '100.3', 'charges' => 200, 'adjustments' => 0, 'paid' => 0],
        ];

        $result = $this->service->groupByPatient($rows, []);

        $this->assertEquals(3, $result[0]['invoice_count']);
    }

    /**
     * Test shows patient total row only when invoice count > 1
     */
    public function testShowsPatientTotalRowOnlyWhenMultipleInvoices(): void
    {
        $singleInvoice = [
            ['pid' => 100, 'invnumber' => '100.1', 'charges' => 500, 'adjustments' => 0, 'paid' => 0],
        ];

        $multipleInvoices = [
            ['pid' => 200, 'invnumber' => '200.1', 'charges' => 500, 'adjustments' => 0, 'paid' => 0],
            ['pid' => 200, 'invnumber' => '200.2', 'charges' => 300, 'adjustments' => 0, 'paid' => 0],
        ];

        $result1 = $this->service->groupByPatient($singleInvoice, []);
        $result2 = $this->service->groupByPatient($multipleInvoices, []);

        $this->assertFalse($result1[0]['show_total_row'], 'Should not show total for single invoice');
        $this->assertTrue($result2[0]['show_total_row'], 'Should show total for multiple invoices');
    }
}
