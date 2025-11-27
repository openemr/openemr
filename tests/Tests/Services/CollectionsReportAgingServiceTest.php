<?php

/**
 * Collections Report Aging Service Test
 *
 * Tests aging calculation logic for the Collections Report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\Collections\Services\AgingService;

class CollectionsReportAgingServiceTest extends TestCase
{
    private $service;
    private $baseDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AgingService();
        // Use a fixed date for predictable testing
        $this->baseDate = strtotime('2025-01-15');
    }

    /**
     * Test calculates aging days from service date
     */
    public function testCalculatesAgingDaysFromServiceDate(): void
    {
        $serviceDate = '2024-12-01'; // 45 days before baseDate
        
        $days = $this->service->calculateAgingDays($serviceDate, null, $this->baseDate);
        
        $this->assertEquals(45, $days);
    }

    /**
     * Test calculates aging days from last activity date
     */
    public function testCalculatesAgingDaysFromLastActivityDate(): void
    {
        $serviceDate = '2024-12-01'; // 45 days old
        $activityDate = '2024-12-20'; // 26 days old
        
        $days = $this->service->calculateAgingDays($serviceDate, $activityDate, $this->baseDate);
        
        $this->assertEquals(26, $days, 'Should use activity date when provided');
    }

    /**
     * Test determines correct bucket for 30-day intervals
     */
    public function testDeterminesCorrectBucketFor30DayIntervals(): void
    {
        $config = ['age_cols' => 4, 'age_inc' => 30];
        
        // 0-29 days -> bucket 0
        $this->assertEquals(0, $this->service->determineBucket(15, $config));
        $this->assertEquals(0, $this->service->determineBucket(29, $config));
        
        // 30-59 days -> bucket 1
        $this->assertEquals(1, $this->service->determineBucket(30, $config));
        $this->assertEquals(1, $this->service->determineBucket(59, $config));
        
        // 60-89 days -> bucket 2
        $this->assertEquals(2, $this->service->determineBucket(60, $config));
        $this->assertEquals(2, $this->service->determineBucket(89, $config));
        
        // 90+ days -> bucket 3
        $this->assertEquals(3, $this->service->determineBucket(90, $config));
        $this->assertEquals(3, $this->service->determineBucket(365, $config));
    }

    /**
     * Test determines correct bucket for 45-day intervals
     */
    public function testDeterminesCorrectBucketFor45DayIntervals(): void
    {
        $config = ['age_cols' => 3, 'age_inc' => 45];
        
        // 0-44 days -> bucket 0
        $this->assertEquals(0, $this->service->determineBucket(20, $config));
        $this->assertEquals(0, $this->service->determineBucket(44, $config));
        
        // 45-89 days -> bucket 1
        $this->assertEquals(1, $this->service->determineBucket(45, $config));
        $this->assertEquals(1, $this->service->determineBucket(89, $config));
        
        // 90+ days -> bucket 2
        $this->assertEquals(2, $this->service->determineBucket(90, $config));
        $this->assertEquals(2, $this->service->determineBucket(200, $config));
    }

    /**
     * Test distributes balance to single bucket
     */
    public function testDistributesBalanceToSingleBucket(): void
    {
        $config = ['age_cols' => 3, 'age_inc' => 30];
        $balance = 1500.00;
        $days = 45; // Should go in bucket 1 (30-59)
        
        $buckets = $this->service->distributeBalance($balance, $days, $config);
        
        $this->assertEquals([0.0, 1500.00, 0.0], $buckets);
    }

    /**
     * Test handles zero balance correctly
     */
    public function testHandlesZeroBalanceCorrectly(): void
    {
        $config = ['age_cols' => 3, 'age_inc' => 30];
        
        $buckets = $this->service->distributeBalance(0.0, 45, $config);
        
        $this->assertEquals([0.0, 0.0, 0.0], $buckets);
    }

    /**
     * Test handles negative balance correctly
     */
    public function testHandlesNegativeBalanceCorrectly(): void
    {
        $config = ['age_cols' => 3, 'age_inc' => 30];
        
        $buckets = $this->service->distributeBalance(-500.00, 45, $config);
        
        $this->assertEquals([0.0, -500.00, 0.0], $buckets);
    }

    /**
     * Test clamps bucket index to valid range
     */
    public function testClampsBucketIndexToValidRange(): void
    {
        $config = ['age_cols' => 3, 'age_inc' => 30];
        
        // Very old invoice should go in last bucket
        $buckets = $this->service->distributeBalance(1000.00, 500, $config);
        
        $this->assertEquals([0.0, 0.0, 1000.00], $buckets);
    }

    /**
     * Test generates bucket labels correctly
     */
    public function testGeneratesBucketLabelsCorrectly(): void
    {
        $config = ['age_cols' => 4, 'age_inc' => 30];
        
        $labels = $this->service->generateBucketLabels($config);
        
        $this->assertEquals(['0-29', '30-59', '60-89', '90+'], $labels);
    }

    /**
     * Test generates bucket labels for different intervals
     */
    public function testGeneratesBucketLabelsForDifferentIntervals(): void
    {
        $config = ['age_cols' => 3, 'age_inc' => 45];
        
        $labels = $this->service->generateBucketLabels($config);
        
        $this->assertEquals(['0-44', '45-89', '90+'], $labels);
    }

    /**
     * Test handles invalid date strings gracefully
     */
    public function testHandlesInvalidDateStringsGracefully(): void
    {
        $days = $this->service->calculateAgingDays('invalid-date', null, $this->baseDate);
        
        $this->assertEquals(0, $days);
    }

    /**
     * Test handles empty date strings gracefully
     */
    public function testHandlesEmptyDateStringsGracefully(): void
    {
        $days = $this->service->calculateAgingDays('', null, $this->baseDate);
        
        $this->assertEquals(0, $days);
    }

    /**
     * Test handles future dates correctly
     */
    public function testHandlesFutureDatesCorrectly(): void
    {
        $futureDate = '2025-12-31';
        
        $days = $this->service->calculateAgingDays($futureDate, null, $this->baseDate);
        
        // Future dates should result in 0 days or negative (which gets clamped to bucket 0)
        $this->assertLessThanOrEqual(0, $days);
    }

    /**
     * Test calculates aging buckets for multiple invoices
     */
    public function testCalculatesAgingBucketsForMultipleInvoices(): void
    {
        $config = ['age_cols' => 3, 'age_inc' => 30];
        $invoices = [
            ['balance' => 500.00, 'days' => 15],  // Bucket 0
            ['balance' => 750.00, 'days' => 45],  // Bucket 1
            ['balance' => 250.00, 'days' => 90],  // Bucket 2
        ];
        
        $totals = [0.0, 0.0, 0.0];
        foreach ($invoices as $invoice) {
            $buckets = $this->service->distributeBalance($invoice['balance'], $invoice['days'], $config);
            for ($i = 0; $i < 3; $i++) {
                $totals[$i] += $buckets[$i];
            }
        }
        
        $this->assertEquals([500.00, 750.00, 250.00], $totals);
    }
}
