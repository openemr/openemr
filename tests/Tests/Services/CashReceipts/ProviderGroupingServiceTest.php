<?php

/**
 * Tests for ProviderGroupingService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\CashReceipts;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\CashReceipts\Services\ProviderGroupingService;
use OpenEMR\Reports\CashReceipts\Repository\CashReceiptsRepository;
use OpenEMR\Reports\CashReceipts\Model\Receipt;
use OpenEMR\Reports\CashReceipts\Model\ProviderSummary;

/**
 * @coversDefaultClass \OpenEMR\Reports\CashReceipts\Services\ProviderGroupingService
 */
class ProviderGroupingServiceTest extends TestCase
{
    private CashReceiptsRepository $repository;
    private ProviderGroupingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CashReceiptsRepository();
        $this->service = new ProviderGroupingService($this->repository);
    }

    /**
     * Create mock receipts for testing
     */
    private function createMockReceipts(): array
    {
        $receipts = [];

        // Provider 1 receipts
        $receipts[] = new Receipt([
            'pid' => 1,
            'encounter' => 1,
            'provider_id' => 1,
            'trans_date' => '2024-01-15',
            'amount' => 50.00,
            'type' => 'copay',
        ]);

        $receipts[] = new Receipt([
            'pid' => 2,
            'encounter' => 2,
            'provider_id' => 1,
            'trans_date' => '2024-01-20',
            'amount' => 75.00,
            'type' => 'ar_activity',
            'code' => '99213',
        ]);

        // Provider 2 receipts
        $receipts[] = new Receipt([
            'pid' => 3,
            'encounter' => 3,
            'provider_id' => 2,
            'trans_date' => '2024-01-10',
            'amount' => 100.00,
            'type' => 'copay',
        ]);

        return $receipts;
    }

    /**
     * @covers ::groupByProvider
     */
    public function testGroupByProviderCreatesProviderSummaries(): void
    {
        $receipts = $this->createMockReceipts();
        $providers = $this->service->groupByProvider($receipts);

        $this->assertIsArray($providers);
        $this->assertCount(2, $providers); // Should have 2 providers
        
        $this->assertArrayHasKey(1, $providers);
        $this->assertArrayHasKey(2, $providers);
        
        $this->assertInstanceOf(ProviderSummary::class, $providers[1]);
        $this->assertInstanceOf(ProviderSummary::class, $providers[2]);
    }

    /**
     * @covers ::groupByProvider
     */
    public function testGroupByProviderAssignsReceiptsCorrectly(): void
    {
        $receipts = $this->createMockReceipts();
        $providers = $this->service->groupByProvider($receipts);

        // Provider 1 should have 2 receipts
        $this->assertCount(2, $providers[1]->getReceipts());
        
        // Provider 2 should have 1 receipt
        $this->assertCount(1, $providers[2]->getReceipts());
    }

    /**
     * @covers ::groupByProvider
     */
    public function testGroupByProviderCalculatesTotals(): void
    {
        $receipts = $this->createMockReceipts();
        $providers = $this->service->groupByProvider($receipts);

        // Provider 1 total should be 125.00 (50 + 75)
        $this->assertEquals(125.00, $providers[1]->getGrandTotal(), '', 0.01);
        
        // Provider 2 total should be 100.00
        $this->assertEquals(100.00, $providers[2]->getGrandTotal(), '', 0.01);
    }

    /**
     * @covers ::groupByProvider
     */
    public function testGroupByProviderSortsReceipts(): void
    {
        // Create unsorted receipts
        $receipts = [];
        $receipts[] = new Receipt([
            'pid' => 2,
            'encounter' => 2,
            'provider_id' => 1,
            'trans_date' => '2024-01-20',
            'amount' => 75.00,
            'type' => 'copay',
        ]);

        $receipts[] = new Receipt([
            'pid' => 1,
            'encounter' => 1,
            'provider_id' => 1,
            'trans_date' => '2024-01-10',
            'amount' => 50.00,
            'type' => 'copay',
        ]);

        $providers = $this->service->groupByProvider($receipts);
        $providerReceipts = $providers[1]->getReceipts();

        // First receipt should be the one with earlier date
        $this->assertEquals('2024-01-10', $providerReceipts[0]->getTransactionDate());
        $this->assertEquals('2024-01-20', $providerReceipts[1]->getTransactionDate());
    }

    /**
     * @covers ::groupByProvider
     */
    public function testGroupByProviderHandlesEmptyArray(): void
    {
        $providers = $this->service->groupByProvider([]);

        $this->assertIsArray($providers);
        $this->assertEmpty($providers);
    }

    /**
     * @covers ::groupByProvider
     */
    public function testGroupByProviderHandlesSingleProvider(): void
    {
        $receipts = [
            new Receipt([
                'pid' => 1,
                'encounter' => 1,
                'provider_id' => 1,
                'trans_date' => '2024-01-15',
                'amount' => 50.00,
                'type' => 'copay',
            ])
        ];

        $providers = $this->service->groupByProvider($receipts);

        $this->assertCount(1, $providers);
        $this->assertArrayHasKey(1, $providers);
    }

    /**
     * @covers ::getSortedProviderSummaries
     */
    public function testGetSortedProviderSummariesReturnsArray(): void
    {
        $receipts = $this->createMockReceipts();
        $sortedProviders = $this->service->getSortedProviderSummaries($receipts);

        $this->assertIsArray($sortedProviders);
        $this->assertCount(2, $sortedProviders);
        
        // Should be indexed 0, 1 (not by provider ID)
        $this->assertArrayHasKey(0, $sortedProviders);
        $this->assertArrayHasKey(1, $sortedProviders);
    }

    /**
     * @covers ::getSortedProviderSummaries
     */
    public function testGetSortedProviderSummariesSortsByProviderId(): void
    {
        $receipts = [];
        
        // Add provider 3 first
        $receipts[] = new Receipt([
            'pid' => 1,
            'encounter' => 1,
            'provider_id' => 3,
            'trans_date' => '2024-01-15',
            'amount' => 50.00,
            'type' => 'copay',
        ]);

        // Add provider 1 second
        $receipts[] = new Receipt([
            'pid' => 2,
            'encounter' => 2,
            'provider_id' => 1,
            'trans_date' => '2024-01-20',
            'amount' => 75.00,
            'type' => 'copay',
        ]);

        $sortedProviders = $this->service->getSortedProviderSummaries($receipts);

        // Provider 1 should come first
        $this->assertEquals(1, $sortedProviders[0]->getProviderId());
        $this->assertEquals(3, $sortedProviders[1]->getProviderId());
    }

    /**
     * @covers ::getSortedProviderSummaries
     */
    public function testGetSortedProviderSummariesHandlesEmptyArray(): void
    {
        $sortedProviders = $this->service->getSortedProviderSummaries([]);

        $this->assertIsArray($sortedProviders);
        $this->assertEmpty($sortedProviders);
    }
}
