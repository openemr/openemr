<?php

/**
 * EncounterReportDataTest.php
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Reports\Encounter;

use OpenEMR\Reports\Encounter\EncounterReportData;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EncounterReportData::class)]
#[Group('integration')]
class EncounterReportDataTest extends TestCase
{
    private EncounterReportData $reportData;

    protected function setUp(): void
    {
        $this->reportData = new EncounterReportData();
    }

    #[Test]
    public function testGetEncountersReturnsExpectedShape(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
        ];

        $result = $this->reportData->getEncounters($filters);

        $this->assertIsArray($result);
        
        // If there are results, verify the structure
        if (!empty($result)) {
            $firstRow = $result[0];
            
            $this->assertArrayHasKey('id', $firstRow);
            $this->assertArrayHasKey('date', $firstRow);
            $this->assertArrayHasKey('encounter', $firstRow);
            $this->assertArrayHasKey('pid', $firstRow);
            $this->assertArrayHasKey('provider', $firstRow);
            $this->assertArrayHasKey('patient', $firstRow);
            $this->assertArrayHasKey('category', $firstRow);
            $this->assertArrayHasKey('forms', $firstRow);
            $this->assertArrayHasKey('coding', $firstRow);
            $this->assertArrayHasKey('encounter_signer', $firstRow);
            $this->assertArrayHasKey('form_signer', $firstRow);
        }
    }

    #[Test]
    public function testGetEncountersWithEmptyFilters(): void
    {
        $result = $this->reportData->getEncounters([]);

        $this->assertIsArray($result);
        // Should return array (may be empty or have all encounters depending on implementation)
    }

    #[Test]
    public function testGetEncountersWithDateRangeOnly(): void
    {
        $filters = [
            'date_from' => '2024-06-01',
            'date_to' => '2024-06-30',
        ];

        $result = $this->reportData->getEncounters($filters);

        $this->assertIsArray($result);
        
        // Verify all returned encounters fall within the date range
        foreach ($result as $encounter) {
            $encounterDate = date('Y-m-d', strtotime($encounter['date']));
            $this->assertGreaterThanOrEqual('2024-06-01', $encounterDate);
            $this->assertLessThanOrEqual('2024-06-30', $encounterDate);
        }
    }

    #[Test]
    public function testGetEncountersWithFacilityFilter(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
            'facility' => 1,
        ];

        $result = $this->reportData->getEncounters($filters);

        $this->assertIsArray($result);
        // Result should only include encounters from facility 1
    }

    #[Test]
    public function testGetEncountersWithProviderFilter(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
            'provider' => 1,
        ];

        $result = $this->reportData->getEncounters($filters);

        $this->assertIsArray($result);
        // Result should only include encounters from provider 1
    }

    #[Test]
    public function testGetEncountersSignedOnlyTrue(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
            'signed_only' => true,
        ];

        $result = $this->reportData->getEncounters($filters);

        $this->assertIsArray($result);
        
        // Verify that returned encounters have signature information
        foreach ($result as $encounter) {
            $hasSigner = !empty($encounter['encounter_signer']) || !empty($encounter['form_signer']);
            $this->assertTrue(
                $hasSigner,
                'Signed only filter should only return encounters with signers'
            );
        }
    }

    #[Test]
    public function testGetEncountersWithCombinedFilters(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
            'facility' => 1,
            'provider' => 1,
            'signed_only' => true,
        ];

        $result = $this->reportData->getEncounters($filters);

        $this->assertIsArray($result);
        // All filters should be applied simultaneously
    }

    #[Test]
    public function testGetEncountersWithInvalidFilters(): void
    {
        $filters = [
            'date_from' => 'invalid-date',
            'date_to' => 'also-invalid',
            'facility' => 'abc',
            'provider' => -1,
        ];

        $result = $this->reportData->getEncounters($filters);

        // Should handle gracefully without throwing exceptions
        $this->assertIsArray($result);
    }

    #[Test]
    public function testGetEncounterCountWithoutFilters(): void
    {
        $result = $this->reportData->getEncounterCount([]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('encounter_count', $result);
        $this->assertIsNumeric($result['encounter_count']);
    }

    #[Test]
    public function testGetEncounterCountWithFilters(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
        ];

        $result = $this->reportData->getEncounterCount($filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('encounter_count', $result);
        $this->assertIsNumeric($result['encounter_count']);
    }

    #[Test]
    public function testGetEncounterCountWithFacilityFilter(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
            'facility' => 1,
        ];

        $result = $this->reportData->getEncounterCount($filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('encounter_count', $result);
    }

    #[Test]
    public function testGetEncounterCountWithProviderFilter(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
            'provider' => 1,
        ];

        $result = $this->reportData->getEncounterCount($filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('encounter_count', $result);
    }

    #[Test]
    public function testGetEncounterSummaryByProvider(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
            'facility' => 'all',
            'provider' => 'all',
        ];

        $result = $this->reportData->getEncounterSummary($filters);

        $this->assertIsArray($result);
        
        // Verify structure of summary data
        foreach ($result as $row) {
            $this->assertArrayHasKey('provider_id', $row);
            $this->assertArrayHasKey('provider_name', $row);
            $this->assertArrayHasKey('encounter_count', $row);
            $this->assertIsNumeric($row['encounter_count']);
        }
    }

    #[Test]
    public function testGetEncounterSummaryWithFacilityFilter(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
            'facility' => 1,
            'provider' => 'all',
        ];

        $result = $this->reportData->getEncounterSummary($filters);

        $this->assertIsArray($result);
    }

    #[Test]
    public function testGetEncounterSummaryWithProviderFilter(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
            'facility' => 'all',
            'provider' => 1,
        ];

        $result = $this->reportData->getEncounterSummary($filters);

        $this->assertIsArray($result);
        
        // With a specific provider filter, should only return that provider's data
        foreach ($result as $row) {
            $this->assertSame(1, $row['provider_id']);
        }
    }

    #[Test]
    public function testFormatDateWithVariousInputs(): void
    {
        // Test with Y-m-d format
        $date1 = '2025-01-15';
        $result1 = $this->reportData->formatDate($date1);
        $this->assertSame('20250115', $result1);

        // Test with Y-m-d H:i:s format
        $date2 = '2025-01-15 10:30:45';
        $result2 = $this->reportData->formatDate($date2);
        $this->assertSame('20250115', $result2);

        // Test with different format
        $date3 = '01/15/2025';
        $result3 = $this->reportData->formatDate($date3);
        $this->assertMatchesRegularExpression('/^\d{8}$/', $result3);
    }

    #[Test]
    public function testFormatDateReturnsCorrectFormat(): void
    {
        $date = '2025-01-15';
        $result = $this->reportData->formatDate($date);

        // Should return YYYYmmdd format
        $this->assertMatchesRegularExpression('/^\d{8}$/', $result);
        $this->assertSame(8, strlen($result));
    }

    #[Test]
    public function testGetEncountersOrderedByDateDescending(): void
    {
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
        ];

        $result = $this->reportData->getEncounters($filters);

        $this->assertIsArray($result);
        
        // Verify descending date order
        $previousDate = null;
        foreach ($result as $encounter) {
            $currentDate = strtotime($encounter['date']);
            if ($previousDate !== null) {
                $this->assertLessThanOrEqual($previousDate, $currentDate, 'Encounters should be ordered by date descending');
            }
            $previousDate = $currentDate;
        }
    }
}
